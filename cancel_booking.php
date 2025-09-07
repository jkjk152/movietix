<?php
require __DIR__ . '/db.php';
requireLogin();
$booking_id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT b.*, s.show_date, s.show_time FROM bookings b JOIN showtimes s ON s.id=b.showtime_id WHERE b.id=? AND (b.user_id=? OR isnull(b.user_id))');
$stmt->execute([$booking_id, user()['id'] ?? 0]);
$b = $stmt->fetch();
if (!$b)
    die('Not found');
$deadline = strtotime($b['show_date'] . ' ' . $b['show_time'] . ' -2 hours');
if (time() > $deadline)
    die('Cancellation window closed');

$pdo->beginTransaction();
$pdo->prepare('DELETE FROM booking_seats WHERE booking_id=?')->execute([$booking_id]);
$pdo->prepare('UPDATE bookings SET payment_status="refunded" WHERE id=?')->execute([$booking_id]);
$pdo->prepare('DELETE FROM bookings WHERE id=?')->execute([$booking_id]);
$pdo->commit();

echo 'Booking cancelled.';