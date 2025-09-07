<?php
require __DIR__ . '/db.php';
$id = (int) ($_GET['booking_id'] ?? 0);
if (!$id) {
    echo 'Missing booking id';
    exit;
}
$stmt = $pdo->prepare('SELECT b.*, s.show_date, s.show_time, s.hall, s.price,
m.title
 FROM bookings b
 JOIN showtimes s ON s.id = b.showtime_id
 JOIN movies m ON m.id = s.movie_id
 WHERE b.id = ?');
$stmt->execute([$id]);
$booking = $stmt->fetch();
if (!$booking) {
    echo 'Booking not found';
    exit;
}
$seatsStmt = $pdo->prepare('SELECT seat_label FROM booking_seats WHERE
booking_id = ? ORDER BY seat_label');
$seatsStmt->execute([$id]);
$seats = $seatsStmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Success</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>

<body>
    <main class="container card pad">
        <h1> Booking Confirmed</h1>
        <p>Thank you, <strong><?= h($booking['customer_name']) ?></strong>!</p>
        <ul class="receipt">
            <li><strong>Movie:</strong> <?= h($booking['title']) ?></li>
            <li><strong>Date:</strong> <?= h(date(
                'D, d M Y',
                strtotime($booking['show_date'])
            )) ?></li>
            <li><strong>Time:</strong> <?= h(substr($booking['show_time'], 0, 5)) ?></ li>
            <li><strong>Hall:</strong> <?= h($booking['hall']) ?></li>
            <li><strong>Seats:</strong> <?= h(implode(', ', $seats)) ?></li>
            <li><strong>Total:</strong> RM <?= number_format((float) 
                $booking['total_amount'], 2) ?></li>
            <li><strong>Booking #:</strong> <?= (int) $booking['id'] ?></li>
        </ul>
        <div class="print">
            <button class="btn" onclick="window.print()"> Print</button>
            <a class="btn outline" href="index.php">Back to Home</a>
        </div>
    </main>
</body>

</html>