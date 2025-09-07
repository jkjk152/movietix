<?php
function ip_bin()
{
  $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
  return inet_pton($ip);
}
function rate_limit(PDO $pdo, string $bucket, int $max, int $minutes)
{
  $pdo->prepare('INSERT INTO rate_events(ip,bucket) VALUES(?,?)')->execute([ip_bin(), $bucket]);
  $stmt = $pdo->prepare('SELECT COUNT(*) FROM rate_events WHERE bucket=? AND created_at>= NOW() - INTERVAL ? MINUTE AND ip=?');
  $stmt->execute([$bucket, $minutes, ip_bin()]);
  $cnt = (int) $stmt->fetchColumn();
  if ($cnt > $max) {
    http_response_code(429);
    exit('Too many requests. Try later.');
  }
}