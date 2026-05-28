<?php
// config/conexion.php

$host = $_ENV['MYSQLHOST'] ?? 'bxcfmutsnd6r952jjuzb-mysql.services.clever-cloud.com';
$db   = $_ENV['MYSQLDATABASE'] ?? 'bxcfmutsnd6r952jjuzb';
$user = $_ENV['MYSQLUSER'] ?? 'uaax0kegurbcatz1';
$pass = $_ENV['MYSQLPASSWORD'] ?? 'Qk2v0FWf7POXwlOkczHP';
$port = $_ENV['MYSQLPORT'] ?? '3306';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;port=$port;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
     // Conexión exitosa
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>
