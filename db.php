<?php
session_start();
$dsn = 'mysql:host=localhost;dbname=movietix;charset=utf8mb4';
$user = 'root';
$pass = '';
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC];
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die('DB fail: ' . $e->getMessage());
}
$env = file_exists(__DIR__ . '/.env.php') ? require __DIR__ . '/.env.php' : [];
function h($s)
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
function user()
{
    return $_SESSION['user'] ?? null;
}
function isAdmin()
{
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
}
function requireLogin()
{
    if (!user()) {
        header('Location: /movietix/auth/login.php');
        exit;
    }
}