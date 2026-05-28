<?php
// config/conexion.php

$host = 'bxcfmutsnd6r952jjuzb-mysql.services.clever-cloud.com';
$db   = 'bxcfmutsnd6r952jjuzb';
$user = 'uaax0kegurbcatzl';
$pass = 'Qk2v0FWf7POXwlOkczHP'; // Asegúrate que sea la O mayúscula aquí
$port = '3306';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;port=$port;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMPLATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // Conexión exitosa
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>
