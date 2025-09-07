<?php
require __DIR__ . '/db.php';
$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM movies WHERE id = ?');
$stmt->execute([$id]);
$movie = $stmt->fetch();
if (!$movie) {
  http_response_code(404);
  echo 'Movie not found';
  exit;
}

$showtimes = $pdo->prepare('SELECT s.*, h.name as hall FROM showtimes s LEFT JOIN halls h ON h.id=s.hall_id WHERE s.movie_id = ? AND s.show_date >= CURDATE() ORDER BY s.show_date, s.show_time');
$showtimes->execute([$id]);
$showtimes = $showtimes->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= h($movie['title']) ?> — Showtimes</title>
  <link rel="stylesheet" href="assets/styles.css">
</head>

<body>
  <header class="container">
    <h1><?= h($movie['title']) ?></h1>
    <a href="index.php" class="link">← Back to all movies</a>
  </header>
  <main class="container">
    <div class="detail">
      <img class="poster-lg" src="<?= h($movie['poster_url']) ?>" alt="Poster of <?= h($movie['title']) ?>">
      <div>
        <p class="muted">Duration: <?= (int) $movie['duration_min'] ?> min · Rating: <?= h($movie['rating']) ?></p>
        <p><?= nl2br(h($movie['description'])) ?></p>
        <h2>Upcoming Showtimes</h2>
        <?php if ($showtimes): ?>
          <table class="table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Hall</th>
                <th>Price</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($showtimes as $s): ?>
                <tr>
                  <td><?= h(date('D, d M Y', strtotime($s['show_date']))) ?></td>
                  <td><?= h(substr($s['show_time'], 0, 5)) ?></td>
                  <td><?= h($s['hall'] ?: 'N/A') ?></td>
                  <td>RM <?= number_format((float) $s['price'], 2) ?></td>
                  <td><a class="btn" href="book.php?showtime_id=<?= (int) $s['id'] ?>">Book</a></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p>No upcoming showtimes.</p>
        <?php endif; ?>
      </div>
    </div>
  </main>
</body>

</html>