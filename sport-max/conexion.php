<?php
// conexion.php

$host = 'localhost';
$db   = 'sport_maxx'; // Nombre de tu base de datos según phpMyAdmin
$user = 'root';       // Usuario por defecto en XAMPP
$pass = '';           // Contraseña por defecto en XAMPP (vacía)
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $conexion = new PDO($dsn, $user, $pass, $options);
    // Compatibilidad con bases creadas antes de agregar las fotos de perfil.
    $fotoPerfil = $conexion->query("SHOW COLUMNS FROM usuarios LIKE 'foto_perfil'")->fetch();
    if (!$fotoPerfil) {
        $conexion->exec('ALTER TABLE usuarios ADD COLUMN foto_perfil VARCHAR(255) NULL AFTER rol');
    }
} catch (\PDOException $e) {
    die("No se pudo preparar la base de datos de Sport Max. Verifica que exista la tabla usuarios y que MySQL permita actualizar su estructura. Detalle: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}
?>
