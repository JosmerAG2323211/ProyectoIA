<?php
// config/conexion.php

// 1. Configuración Dinámica (Detecta automáticamente si está en Railway o en Local)
$host    = isset($_ENV['MYSQLHOST'])     ? $_ENV['MYSQLHOST']     : 'bxcfmutsnd6r952jjuzb-mysql.services.clever-cloud.com';
$db      = isset($_ENV['MYSQLDATABASE']) ? $_ENV['MYSQLDATABASE'] : 'bxcfmutsnd6r952jjuzb';
$user    = isset($_ENV['MYSQLUSER'])     ? $_ENV['MYSQLUSER']     : 'uaax0kegurbcatz1';
$pass    = isset($_ENV['MYSQLPASSWORD']) ? $_ENV['MYSQLPASSWORD'] : 'Qk2v0FWf7POXwlOkczHP';
$port    = isset($_ENV['MYSQLPORT'])     ? $_ENV['MYSQLPORT']     : '3306';
$charset = 'utf8mb4';

// 2. Armamos el DSN incluyendo el puerto (Crucial para que Railway conecte sin problemas)
$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
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
