<?php
require __DIR__ . '/_guard.php';

$movies = $pdo->query('SELECT id,title,poster_url FROM movies ORDER BY title')->fetchAll();
$halls = $pdo->query('SELECT id,name FROM halls ORDER BY name')->fetchAll();

// handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if ($_POST['op'] === 'create') {
    $stmt = $pdo->prepare('INSERT INTO showtimes(movie_id,show_date,show_time,hall_id,price) VALUES (?,?,?,?,?)');
    $stmt->execute([(int) $_POST['movie_id'], $_POST['show_date'], $_POST['show_time'], (int) $_POST['hall_id'], (float) $_POST['price']]);
  }
  if ($_POST['op'] === 'update') {
    $stmt = $pdo->prepare('UPDATE showtimes SET movie_id=?,show_date=?,show_time=?,hall_id=?,price=? WHERE id=?');
    $stmt->execute([(int) $_POST['movie_id'], $_POST['show_date'], $_POST['show_time'], (int) $_POST['hall_id'], (float) $_POST['price'], (int) $_POST['id']]);
  }
  if ($_POST['op'] === 'delete') {
    $pdo->prepare('DELETE FROM showtimes WHERE id=?')->execute([(int) $_POST['id']]);
  }
  header('Location: showtimes.php?movie_id=' . (int) ($_POST['movie_id'] ?? 0));
  exit;
}

// detect selected movie
$selectedMovie = isset($_GET['movie_id']) ? (int) $_GET['movie_id'] : 0;

$rows = [];
$selectedPoster = '';
$selectedTitle = '';
if ($selectedMovie) {
  // fetch showtimes
  $stmt = $pdo->prepare('SELECT s.*, m.title AS movie, h.name AS hall, m.poster_url 
    FROM showtimes s 
    LEFT JOIN movies m ON m.id=s.movie_id 
    LEFT JOIN halls h ON h.id=s.hall_id
    WHERE s.movie_id=?
    ORDER BY s.show_date,s.show_time');
  $stmt->execute([$selectedMovie]);
  $rows = $stmt->fetchAll();

  // fetch movie info
  foreach ($movies as $m) {
    if ($m['id'] == $selectedMovie) {
      $selectedTitle = $m['title'];
      $selectedPoster = $m['poster_url'];
      break;
    }
  }
}
?>
<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <link rel="stylesheet" href="../assets/styles.css">
  <title>Admin · Showtimes</title>
  <style>
    .poster-preview {
      margin: 1rem 0;
    }

    .poster-preview img {
      max-width: 160px;
      border-radius: 6px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, .4);
    }
  </style>
</head>

<body class="container">
  <h1>Admin · Showtimes</h1>
  <a class="link" href="/movietix/index.php">← Site</a>

  <h2>Select Movie</h2>
  <form method="get">
    <select name="movie_id" onchange="this.form.submit()" required>
      <option value="">-- Choose a movie --</option>
      <?php foreach ($movies as $m): ?>
        <option value="<?= (int) $m['id'] ?>" <?= $m['id'] === $selectedMovie ? 'selected' : '' ?>>
          <?= h($m['title']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </form>

  <?php if ($selectedMovie): ?>
    <div class="poster-preview">
      <h3><?= h($selectedTitle) ?></h3>
      <?php if ($selectedPoster): ?>
        <img src="../<?= h($selectedPoster) ?>" alt="Poster of <?= h($selectedTitle) ?>">
      <?php else: ?>
        <p><em>No poster available.</em></p>
      <?php endif; ?>
    </div>

    <h2>Add Showtime</h2>
    <form method="post" class="card pad">
      <input type="hidden" name="op" value="create">
      <input type="hidden" name="movie_id" value="<?= $selectedMovie ?>">
      <label>Date<input type="date" name="show_date" required></label>
      <label>Time<input type="time" name="show_time" required></label>
      <label>Hall
        <select name="hall_id" required>
          <?php foreach ($halls as $h): ?>
            <option value="<?= (int) $h['id'] ?>"><?= h($h['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>Price (RM)<input type="number" name="price" min="0" step="0.01" required></label>
      <button class="btn">Create</button>
    </form>

    <h2>Showtimes for <?= h($selectedTitle) ?></h2>
    <?php if ($rows): ?>
      <table class="table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Time</th>
            <th>Hall</th>
            <th>Price</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><?= h($r['show_date']) ?></td>
              <td><?= h(substr($r['show_time'], 0, 5)) ?></td>
              <td><?= h($r['hall']) ?></td>
              <td><?= number_format((float) $r['price'], 2) ?></td>
              <td>
                <details>
                  <summary>Edit</summary>
                  <form method="post" class="pad">
                    <input type="hidden" name="op" value="update">
                    <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                    <input type="hidden" name="movie_id" value="<?= $selectedMovie ?>">
                    <label>Date<input type="date" name="show_date" value="<?= h($r['show_date']) ?>" required></label>
                    <label>Time<input type="time" name="show_time" value="<?= h($r['show_time']) ?>" required></label>
                    <label>Hall
                      <select name="hall_id">
                        <?php foreach ($halls as $h): ?>
                          <option value="<?= (int) $h['id'] ?>" <?= $h['id'] == $r['hall_id'] ? 'selected' : '' ?>>
                            <?= h($h['name']) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </label>
                    <label>Price<input type="number" name="price" step="0.01" value="<?= (float) $r['price'] ?>"
                        required></label>
                    <button class="btn">Save</button>
                    <button class="btn btn-danger">Delete</button>
                  </form>
                  <form method="post" onsubmit="return confirm('Delete this showtime?')">
                    <input type="hidden" name="op" value="delete">
                    <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                    <input type="hidden" name="movie_id" value="<?= $selectedMovie ?>">
                  </form>
                </details>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>No showtimes for this movie yet.</p>
    <?php endif; ?>
  <?php endif; ?>
</body>

</html>