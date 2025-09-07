<?php
require __DIR__ . '/db.php';
require __DIR__ . '/middleware/rate_limit.php';
requireLogin(); // must be logged in to book
rate_limit($pdo, 'booking', 20, 5);


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: index.php');
  exit;
}


$showtime_id = (int) ($_POST['showtime_id'] ?? 0);
$promoCode = strtoupper(trim($_POST['promo_code'] ?? ''));
$seats = $_POST['seats'] ?? [];


// Pull name/email from the session (registered user), not from the form
$name = user()['name'] ?? '';
$email = user()['email'] ?? '';


if (!$showtime_id || !$name || !filter_var($email, FILTER_VALIDATE_EMAIL) || !is_array($seats) || count($seats) === 0) {
  die('Invalid input.');
}


$showStmt = $pdo->prepare('SELECT price FROM showtimes WHERE id = ?');
$showStmt->execute([$showtime_id]);
$show = $showStmt->fetch();
if (!$show)
  die('Showtime not found.');
$price = (float) $show['price'];


$seats = array_values(array_unique(array_map(function ($s) {
  return preg_replace('/[^A-Z0-9]/', '', strtoupper($s)); }, $seats)));


// Promo lookup
$promo_id = null;
$discountPct = 0;
if ($promoCode) {
  $p = $pdo->prepare('SELECT * FROM promo_codes WHERE code=? AND active=1 AND (valid_until IS NULL OR valid_until>=CURDATE())');
  $p->execute([$promoCode]);
  $promo = $p->fetch();
  if ($promo) {
    $promo_id = (int) $promo['id'];
    $discountPct = (int) $promo['discount_percent'];
  }
}


try {
  $pdo->beginTransaction();


  $placeholders = implode(',', array_fill(0, count($seats), '?'));
  $check = $pdo->prepare("SELECT seat_label FROM booking_seats WHERE showtime_id = ? AND seat_label IN ($placeholders) FOR UPDATE");
  $check->execute(array_merge([$showtime_id], $seats));
  $conflicts = $check->fetchAll(PDO::FETCH_COLUMN);
  if ($conflicts) {
    $pdo->rollBack();
    echo 'Seats taken: ' . h(implode(', ', $conflicts));
    exit;
  }


  $subtotal = $price * count($seats);
  $discount = $discountPct ? round($subtotal * $discountPct / 100, 2) : 0;
  $total = max(0, $subtotal - $discount);


  $ins = $pdo->prepare('INSERT INTO bookings (showtime_id, customer_name, customer_email, user_id, total_amount, payment_status, promo_id) VALUES (?,?,?,?,? ,"pending", ?)');
  $ins->execute([$showtime_id, $name, $email, user()['id'] ?? null, $total, $promo_id]);
  $booking_id = (int) $pdo->lastInsertId();


  $insSeat = $pdo->prepare('INSERT INTO booking_seats (booking_id, showtime_id, seat_label) VALUES (?,?,?)');
  foreach ($seats as $label) {
    $insSeat->execute([$booking_id, $showtime_id, $label]);
  }


  $pdo->commit();
  header('Location: /movietix/payments/checkout.php?booking_id=' . $booking_id);
  exit;
} catch (PDOException $e) {
  if ($pdo->inTransaction())
    $pdo->rollBack();
  echo 'Error: ' . h($e->getMessage());
}