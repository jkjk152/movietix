<?php
require __DIR__ . '/../db.php';
//require __DIR__ . '/../middleware/rate_limit.php';
//rate_limit($pdo, 'login', 5, 15); // 5 attempts / 15 min per IP

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $pass = $_POST['password'] ?? '';
  $stmt = $pdo->prepare('SELECT * FROM users WHERE email=?');
  $stmt->execute([$email]);
  $u = $stmt->fetch();
  if ($u && password_verify($pass, $u['password_hash'])) {
    $_SESSION['user'] = ['id' => $u['id'], 'name' => $u['name'], 'email' => $u['email'], 'role' => $u['role']];
    header('Location: /movietix/index.php');
    exit;
  } else {
    $err = 'Invalid credentials';
  }
}
?>
<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <link rel="stylesheet" href="../assets/styles.css">
  <title>Login</title>
</head>

<body class="container">
  <h1>Login</h1>
  <?php if (!empty($err)): ?>
    <p class="muted"><?= h($err) ?></p><?php endif; ?>
  <form method="post" class="card pad" autocomplete="off">
    <label>Email<br><input type="email" name="email" required></label><br>
    <label>Password<br><input type="password" name="password" required></label><br>
    <button class="btn" type="submit">Login</button>
    <p>No account? <a class="link" href="/movietix/auth/register.php">Register</a></p>
  </form>
</body>

</html>