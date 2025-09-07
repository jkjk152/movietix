<?php
require __DIR__ . '/../db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $pass = $_POST['password'] ?? '';
  $pass2 = $_POST['password2'] ?? '';
  if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 8 || $pass !== $pass2) {
    $err = 'Invalid input';
  }
  if (!isset($err)) {
    $hash = password_hash($pass, PASSWORD_BCRYPT);
    try {
      $stmt = $pdo->prepare('INSERT INTO users(name,email,password_hash) VALUES (?,?,?)');
      $stmt->execute([$name, $email, $hash]);
      $_SESSION['user'] = ['id' => $pdo->lastInsertId(), 'name' => $name, 'email' => $email, 'role' => 'user'];
      header('Location: /movietix/index.php');
      exit;
    } catch (PDOException $e) {
      $err = str_contains($e->getMessage(), 'Duplicate') ? 'Email already used' : 'Error';
    }
  }
}
?>
<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <link rel="stylesheet" href="../assets/styles.css">
  <title>Register</title>
</head>

<body class="container">
  <h1>Create account</h1>
  <?php if (!empty($err)): ?>
    <p class="muted"><?= h($err) ?></p><?php endif; ?>
  <form method="post" class="card pad" autocomplete="off">
    <label>Name<br><input type="text" name="name" required></label>
    <label>Email<br><input type="email" name="email" required></label>
    <label>Password (min 8)<br><input type="password" name="password" required></label>
    <label>Confirm Password<br><input type="password" name="password2" required></label>
    <button class="btn" type="submit">Register</button>
    <p>Have an account? <a class="link" href="/movietix/auth/login.php">Login</a></p>
  </form>
</body>

</html>