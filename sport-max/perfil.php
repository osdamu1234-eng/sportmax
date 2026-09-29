<?php
session_start();
require_once __DIR__ . '/conexion.php';
if (empty($_SESSION['usuario_id'])) { header('Location: index.php?error=sesion#login'); exit; }
$id = (int)$_SESSION['usuario_id'];
$stmt = $conexion->prepare('SELECT id,nombre_completo,correo_electronico,rol,foto_perfil FROM usuarios WHERE id=?');
$stmt->execute([$id]); $usuario = $stmt->fetch();
if (!$usuario) { header('Location: logout.php'); exit; }
$error = ''; $avatarNuevo = null; $rutaNueva = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre_completo'] ?? ''); $correo = strtolower(trim($_POST['correo_electronico'] ?? '')); $password = $_POST['contrasena'] ?? '';
    if ($nombre === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) $error = 'Revisa el nombre y el correo.';
    elseif ($password !== '' && strlen($password) < 8) $error = 'La contraseña debe tener al menos 8 caracteres.';
    if ($error === '' && isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['foto_perfil'];
        if ($file['error'] !== UPLOAD_ERR_OK) $error = 'No se pudo recibir la imagen. Inténtalo de nuevo.';
        elseif ($file['size'] > 3 * 1024 * 1024) $error = 'La imagen debe pesar máximo 3 MB.';
        else {
            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
            $extensions = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
            if (!isset($extensions[$mime]) || @getimagesize($file['tmp_name']) === false) $error = 'Usa una imagen JPG, PNG o WEBP válida.';
            else {
                $directory = __DIR__ . '/uploads/avatars';
                if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) $error = 'No se pudo preparar el almacenamiento de fotos.';
                else {
                    $filename = bin2hex(random_bytes(18)) . '.' . $extensions[$mime];
                    if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $filename)) $error = 'No se pudo guardar la imagen.';
                    else { $avatarNuevo = 'uploads/avatars/' . $filename; $rutaNueva = $directory . '/' . $filename; }
                }
            }
        }
    }
    if ($error === '') {
        $check = $conexion->prepare('SELECT id FROM usuarios WHERE correo_electronico=? AND id<>?'); $check->execute([$correo,$id]);
        if ($check->fetch()) $error = 'Ese correo ya está en uso.';
        else {
            $fotoFinal = $avatarNuevo ?? $usuario['foto_perfil'];
            if ($password !== '') { $save=$conexion->prepare('UPDATE usuarios SET nombre_completo=?,correo_electronico=?,contrasena=?,foto_perfil=? WHERE id=?');$save->execute([$nombre,$correo,password_hash($password,PASSWORD_DEFAULT),$fotoFinal,$id]); }
            else { $save=$conexion->prepare('UPDATE usuarios SET nombre_completo=?,correo_electronico=?,foto_perfil=? WHERE id=?');$save->execute([$nombre,$correo,$fotoFinal,$id]); }
            if ($avatarNuevo && !empty($usuario['foto_perfil']) && strpos($usuario['foto_perfil'],'uploads/avatars/')===0) { $old=__DIR__.'/'.$usuario['foto_perfil'];if(is_file($old)) @unlink($old); }
            $_SESSION['usuario_nombre']=$_SESSION['nombre_usuario']=$nombre;$_SESSION['usuario']=$correo;$_SESSION['foto_perfil']=$fotoFinal;
            header('Location: perfil.php?guardado=1');exit;
        }
    }
    if ($rutaNueva && is_file($rutaNueva)) @unlink($rutaNueva);
    $usuario['nombre_completo']=$nombre;$usuario['correo_electronico']=$correo;
}
function h($value){return htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8');}
$avatar=!empty($usuario['foto_perfil'])?$usuario['foto_perfil']:'https://ui-avatars.com/api/?name='.rawurlencode($usuario['nombre_completo']).'&background=0876ea&color=fff&size=240';
$isAdmin=($_SESSION['usuario_rol']??'')==='admin';
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Mi perfil | Sport Max</title><link rel="stylesheet" href="admin/admin.css"></head><body class="admin-body">
<aside class="admin-sidebar"><a class="admin-brand" href="index.php">SPORT<span>MAX</span></a><p class="admin-label">Mi cuenta</p><nav class="admin-nav"><?php if($isAdmin): ?><a href="admin/dashboard.php"><span class="ico">◈</span>Resumen</a><a href="admin/productos.php"><span class="ico">▣</span>Productos</a><a href="admin/usuarios.php"><span class="ico">♙</span>Usuarios</a><a href="admin/mensajes.php"><span class="ico">✉</span>Mensajes</a><?php else: ?><a href="index.php"><span class="ico">⌂</span>Volver a la tienda</a><?php endif; ?><a class="active" href="perfil.php"><span class="ico">◉</span>Mi perfil</a></nav><div class="sidebar-account"><img src="<?= h($avatar) ?>" alt=""><div><strong><?= h($usuario['nombre_completo']) ?></strong><small><?= h(ucfirst($usuario['rol']??'cliente')) ?></small></div><a href="logout.php">↗</a></div></aside>
<main class="admin-main"><header class="admin-topbar"><span class="crumb">Sport Max / Mi cuenta</span><div class="top-links"><a href="index.php">↗ Ver tienda</a><a href="logout.php">Cerrar sesión</a></div></header><section class="admin-hero"><span class="kicker">Tu espacio Sport Max</span><h1>Perfil y preferencias</h1><p>Actualiza tu información y deja tu cuenta a tu manera.</p></section>
<?php if($error): ?><div class="error-note" role="alert"><?=h($error)?></div><?php elseif(isset($_GET['guardado'])): ?><div class="success-note">Tus cambios se guardaron correctamente.</div><?php endif; ?>
<section class="admin-card profile-admin"><div class="profile-card-grid"><aside class="profile-photo-wrap"><img class="profile-photo" id="avatar-preview" src="<?=h($avatar)?>" alt="Foto de perfil"><h2 style="font:700 1rem Montserrat,sans-serif;margin:13px 0 4px"><?=h($usuario['nombre_completo'])?></h2><span class="badge <?= $isAdmin?'role-admin':'role-client' ?>"><?=h(ucfirst($usuario['rol']??'cliente'))?></span><label for="foto_perfil" class="admin-button-secondary" style="display:inline-flex;margin:17px auto 5px;cursor:pointer">＋ Cambiar foto</label><input id="foto_perfil" type="file" name="foto_perfil" accept="image/jpeg,image/png,image/webp" form="profile-form" style="display:none"><div class="upload-help">JPG, PNG o WEBP · Máximo 3 MB</div></aside><section><h2 style="font:700 1.1rem Montserrat,sans-serif;margin:2px 0">Información personal</h2><p class="muted" style="font-size:.82rem;margin:6px 0 17px">La foto y los datos del perfil se usan en tu cuenta de Sport Max.</p><form id="profile-form" method="post" enctype="multipart/form-data"><label for="nombre_completo">Nombre completo</label><input id="nombre_completo" name="nombre_completo" value="<?=h($usuario['nombre_completo'])?>" maxlength="120" required autocomplete="name"><label for="correo">Correo electrónico</label><input id="correo" name="correo_electronico" type="email" value="<?=h($usuario['correo_electronico'])?>" maxlength="190" required autocomplete="email"><label for="contrasena">Nueva contraseña <span class="muted">(opcional)</span></label><input id="contrasena" type="password" name="contrasena" minlength="8" autocomplete="new-password" placeholder="Vacío para conservar la actual"><div class="form-actions" style="margin-top:21px"><button class="admin-button" type="submit">Guardar cambios</button><a class="admin-button-secondary" href="<?= $isAdmin?'admin/dashboard.php':'index.php' ?>">Cancelar</a></div></form></section></div></section><footer class="admin-footer">SPORT MAX · Mi cuenta</footer></main>
<script>document.getElementById('foto_perfil').addEventListener('change',function(){if(this.files&&this.files[0])document.getElementById('avatar-preview').src=URL.createObjectURL(this.files[0]);});</script></body></html>
