<?php
require __DIR__ . '/_guard.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if ($_POST['op'] === 'create') {
    $stmt = $pdo->prepare('INSERT INTO movies(title,description,duration_min,rating,poster_url) VALUES (?,?,?,?,?)');
    $stmt->execute([$_POST['title'], $_POST['description'], (int) $_POST['duration_min'], $_POST['rating'], $_POST['poster_url']]);
  }
  if ($_POST['op'] === 'update') {
    $stmt = $pdo->prepare('UPDATE movies SET title=?,description=?,duration_min=?,rating=?,poster_url=? WHERE id=?');
    $stmt->execute([$_POST['title'], $_POST['description'], (int) $_POST['duration_min'], $_POST['rating'], $_POST['poster_url'], (int) $_POST['id']]);
  }
  if ($_POST['op'] === 'delete') {
    $pdo->prepare('DELETE FROM movies WHERE id=?')->execute([(int) $_POST['id']]);
  }
  header('Location: movies.php');
  exit;
}

$movies = $pdo->query('SELECT * FROM movies ORDER BY created_at DESC')->fetchAll();
?>
<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <link rel="stylesheet" href="../assets/styles.css">
  <title>Admin · Movies</title>
  <style>
    /* Modal styling */
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.6);
      justify-content: center;
      align-items: center;
      z-index: 999;
    }

    .modal-content {
      background: #0f1220;
      padding: 2rem;
      border-radius: 8px;
      width: 400px;
      max-height: 90%;
      overflow-y: auto;
      position: relative;
      color: #fff;
    }

    .modal-content h2 {
      margin-top: 0;
    }

    .modal .close {
      position: absolute;
      top: 10px;
      right: 15px;
      font-size: 1.5rem;
      cursor: pointer;
    }

    #poster-preview {
      max-width: 100%;
      margin-top: 10px;
      border-radius: 6px;
      display: none;
    }
  </style>
</head>

<body class="container">
  <h1>Admin · Movies</h1>
  <a class="link" href="/movietix/index.php">← Site</a>

  <h2>Add Movie</h2>
  <form method="post" class="card pad">
    <input type="hidden" name="op" value="create">
    <label>Title<input type="text" name="title" required></label>
    <label>Description<textarea name="description" rows="3"></textarea></label>
    <label>Duration (min)<input type="number" name="duration_min" min="1" required></label>
    <label>Rating<input type="text" name="rating"></label>
    <label>Poster URL<input type="text" name="poster_url"></label><br>
    <button class="btn">Create</button>
  </form>

  <h2>All Movies</h2>
  <table class="table">
    <thead>
      <tr>
        <th>Poster</th>
        <th>Title</th>
        <th>Duration</th>
        <th>Rating</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($movies as $m): ?>
        <tr>
          <td><img src="../<?= h($m['poster_url']) ?>" style="width:60px;height:80px;object-fit:cover"></td>
          <td><?= h($m['title']) ?></td>
          <td><?= (int) $m['duration_min'] ?></td>
          <td><?= h($m['rating']) ?></td>
          <td>
            <button type="button" class="btn" onclick='openEditModal(
              <?= (int) $m['id'] ?>,
              <?= json_encode($m['title']) ?>,
              <?= json_encode($m['description']) ?>,
              <?= (int) $m['duration_min'] ?>,
              <?= json_encode($m['rating']) ?>,
              <?= json_encode($m['poster_url']) ?>
            )'>Edit</button>

            <form method="post" style="display:inline" onsubmit="return confirm('Delete this movie?')">
              <input type="hidden" name="op" value="delete">
              <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
              <button class="btn btn-danger">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- Edit Modal -->
  <div id="editModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeEditModal()">&times;</span>
      <h2>Edit Movie</h2>
      <form method="post">
        <input type="hidden" name="op" value="update">
        <input type="hidden" name="id" id="edit-id">

        <label>Title<input type="text" name="title" id="edit-title" required></label>
        <label>Description<textarea name="description" rows="3" id="edit-description"></textarea></label>
        <label>Duration (min)<input type="number" name="duration_min" min="1" id="edit-duration" required></label>
        <label>Rating<input type="text" name="rating" id="edit-rating"></label>
        <label>Poster URL<input type="text" name="poster_url" id="edit-poster"
            oninput="updatePosterPreview(this.value)"></label>
        <img id="poster-preview" src="" alt="Poster preview">
        <button class="btn">Save</button>
      </form>
    </div>
  </div>

  <script>
    function openEditModal(id, title, desc, duration, rating, poster) {
      document.getElementById('edit-id').value = id;
      document.getElementById('edit-title').value = title;
      document.getElementById('edit-description').value = desc;
      document.getElementById('edit-duration').value = duration;
      document.getElementById('edit-rating').value = rating;
      document.getElementById('edit-poster').value = poster;

      updatePosterPreview(poster);

      document.getElementById('editModal').style.display = 'flex';
    }
    function closeEditModal() {
      document.getElementById('editModal').style.display = 'none';
    }
    window.onclick = function (e) {
      if (e.target == document.getElementById('editModal')) {
        closeEditModal();
      }
    }

    function updatePosterPreview(url) {
      const preview = document.getElementById('poster-preview');
      if (url) {
        preview.src = "../" + url;
        preview.style.display = "block";
      } else {
        preview.style.display = "none";
      }
    }
  </script>
</body>

</html>