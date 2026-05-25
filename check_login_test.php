<?php
require_once __DIR__ . '/config/database.php';
$stmt = $pdo->prepare('SELECT username, role, password FROM users WHERE username IN (?, ?)');
$stmt->execute(['admin','user']);
foreach ($stmt->fetchAll() as $u) {
    $test = $u['username'] === 'admin' ? 'admin123' : 'user123';
    echo $u['username'] . ' / ' . $u['role'] . ' => ' . (password_verify($test, $u['password']) ? 'OK' : 'ҚАТЕ') . '<br>';
}
