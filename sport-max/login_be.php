<?php
session_start();
require_once __DIR__ . '/conexion.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php'); exit; }
$correo = strtolower(trim($_POST['correo_electronico'] ?? $_POST['correo'] ?? ''));
$password = $_POST['contrasena'] ?? $_POST['password'] ?? '';
if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || $password === '') { header('Location: index.php?error=login#login'); exit; }
$stmt = $conexion->prepare('SELECT id, nombre_completo, correo_electronico, contrasena, rol, foto_perfil FROM usuarios WHERE correo_electronico = ? LIMIT 1');
$stmt->execute([$correo]);
$user = $stmt->fetch();
if (!$user || !password_verify($password, $user['contrasena'])) { header('Location: index.php?error=login#login'); exit; }
session_regenerate_id(true);
$_SESSION['usuario_id'] = $_SESSION['id_usuario'] = (int)$user['id'];
$_SESSION['usuario'] = $user['correo_electronico'];
$_SESSION['usuario_nombre'] = $_SESSION['nombre_usuario'] = $user['nombre_completo'];
$_SESSION['usuario_rol'] = $user['rol'] ?? 'cliente';
$_SESSION['foto_perfil'] = $user['foto_perfil'] ?? null;
header('Location: ' . ($_SESSION['usuario_rol'] === 'admin' ? 'admin/dashboard.php' : 'index.php?bienvenido=1'));
exit;
