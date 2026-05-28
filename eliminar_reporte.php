<?php
// eliminar_reporte.php
require_once 'config/conexion.php';

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_reporte = intval($_GET['id']);

    try {
        // Sentencia preparada para prevenir vulnerabilidades de inyección SQL
        $sql = "DELETE FROM reportes_fallas WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$id_reporte])) {
            header("Location: index.php?status=deleted");
            exit();
        } else {
            header("Location: index.php?status=error");
            exit();
        }
    } catch (PDOException $e) {
        header("Location: index.php?status=error");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}