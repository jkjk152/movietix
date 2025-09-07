<?php
require __DIR__ . '/db.php';
requireLogin(); // must be logged in
$showtime_id = (int) ($_GET['showtime_id'] ?? 0);

$stmt = $pdo->prepare('SELECT s.*, m.title, m.poster_url, h.layout AS hall_layout, h.name AS hall_name 
  FROM showtimes s 
  JOIN movies m ON m.id = s.movie_id 
  LEFT JOIN halls h ON h.id = s.hall_id 
  WHERE s.id = ?');
$stmt->execute([$showtime_id]);
$show = $stmt->fetch();
if (!$show) {
  echo 'Showtime not found';
  exit;
}

$bookedStmt = $pdo->prepare('SELECT seat_label FROM booking_seats WHERE showtime_id = ?');
$bookedStmt->execute([$showtime_id]);
$bookedSet = array_fill_keys(array_column($bookedStmt->fetchAll(), 'seat_label'), true);

$layout = $show['hall_layout'] ? json_decode($show['hall_layout'], true) : ['rows' => 8, 'cols' => 12];
$rowsCount = max(1, (int) ($layout['rows'] ?? 8));
$colsCount = max(1, (int) ($layout['cols'] ?? 12));

$rows = [];
for ($i = 0; $i < $rowsCount; $i++) {
  $rows[] = chr(ord('A') + $i);
}
$cols = range(1, $colsCount);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Book — <?= h($show['title']) ?></title>
  <link rel="stylesheet" href="assets/styles.css">
  <style>
    .hall-grid {
      display: grid;
      grid-template-columns: 28px repeat(<?= $colsCount ?>, 40px);
      gap: .4rem;
      justify-content: center;
    }
  </style>
</head>

<body>
  <header class="container">
    <h1>Book Seats — <?= h($show['title']) ?></h1>
    <p class="muted">Date: <?= h(date('D, d M Y', strtotime($show['show_date']))) ?> · Time:
      <?= h(substr($show['show_time'], 0, 5)) ?> · Hall: <?= h($show['hall_name'] ?: 'N/A') ?> · Price: RM
      <?= number_format((float) $show['price'], 2) ?>
    </p>
  </header>
  <main class="container">
    <form method="post" action="process_booking.php" id="bookingForm" class="card pad">
      <input type="hidden" name="showtime_id" value="<?= (int) $showtime_id ?>">

      <!-- Big screen bar -->
      <div class="screen">SCREEN</div>

      <!-- Seat rows -->
      <div class="hall-grid">
        <?php foreach ($rows as $r): ?>
          <div class="row-label"><?= $r ?></div>
          <?php foreach ($cols as $c):
            $label = $r . $c;
            $isBooked = isset($bookedSet[$label]);
            ?>
            <label class="seat <?= $isBooked ? 'booked' : '' ?>">
              <input type="checkbox" name="seats[]" value="<?= $label ?>" <?= $isBooked ? 'disabled' : '' ?>>
              <span><?= $label ?></span>
            </label>
          <?php endforeach; ?>
        <?php endforeach; ?>
      </div>

      <label>Promo code (optional)<br>
        <input type="text" name="promo_code" placeholder="e.g. MOVIE10">
      </label>

      <div class="summary">
        <strong>Selected seats:</strong> <span id="seatList">None</span><br>
        <strong>Total:</strong> RM <span id="total">0.00</span>
      </div>

      <button class="btn" type="submit">Confirm Booking</button>
    </form>
  </main>

  <script>
    const price = <?= json_encode((float) $show['price']) ?>;
    const form = document.getElementById('bookingForm');
    const seatListEl = document.getElementById('seatList');
    const totalEl = document.getElementById('total');

    function updateSummary() {
      const checked = Array.from(form.querySelectorAll('input[name="seats[]"]:checked')).map(i => i.value);
      seatListEl.textContent = checked.length ? checked.join(', ') : 'None';
      totalEl.textContent = (checked.length * price).toFixed(2);
    }

    form.addEventListener('change', e => {
      if (e.target.name === 'seats[]') updateSummary();
    });
    updateSummary();
  </script>
</body>

</html>