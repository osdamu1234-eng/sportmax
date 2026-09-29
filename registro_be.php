<?php
session_start();
require_once __DIR__ . '/conexion.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php'); exit; }
$nombre = trim($_POST['nombre_completo'] ?? $_POST['nombre'] ?? '');
$correo = strtolower(trim($_POST['correo_electronico'] ?? $_POST['correo'] ?? ''));
$password = $_POST['contrasena'] ?? $_POST['password'] ?? '';
if ($nombre === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) { header('Location: index.php?error=registro#registro'); exit; }
$stmt = $conexion->prepare('SELECT id FROM usuarios WHERE correo_electronico = ? LIMIT 1');
$stmt->execute([$correo]);
if ($stmt->fetch()) { header('Location: index.php?error=correo#registro'); exit; }
$stmt = $conexion->prepare("INSERT INTO usuarios (nombre_completo, correo_electronico, contrasena, rol) VALUES (?, ?, ?, 'cliente')");
$stmt->execute([$nombre, $correo, password_hash($password, PASSWORD_DEFAULT)]);
$id = (int)$conexion->lastInsertId();
session_regenerate_id(true);
$_SESSION['usuario_id'] = $_SESSION['id_usuario'] = $id;
$_SESSION['usuario'] = $correo;
$_SESSION['usuario_nombre'] = $_SESSION['nombre_usuario'] = $nombre;
$_SESSION['usuario_rol'] = 'cliente';
$_SESSION['foto_perfil'] = null;
header('Location: index.php?registro=ok');
exit;
