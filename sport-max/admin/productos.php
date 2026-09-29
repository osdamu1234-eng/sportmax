<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../conexion.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    if (($_POST['action'] ?? '') === 'delete' && $id > 0) {
        $delete = $conexion->prepare('DELETE FROM productos WHERE id = ?');
        $delete->execute([$id]);
        header('Location: productos.php?eliminado=1'); exit;
    }
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = filter_var($_POST['precio'] ?? null, FILTER_VALIDATE_FLOAT);
    $stock = filter_var($_POST['stock'] ?? null, FILTER_VALIDATE_INT);
    $imagen = trim($_POST['imagen'] ?? '');
    $categoria = (int)($_POST['id_categoria'] ?? 0);
    if ($nombre === '' || $precio === false || $precio < 0 || $stock === false || $stock < 0) $error = 'Revisa el nombre, precio y unidades disponibles.';
    elseif (strlen($imagen) > 500) $error = 'La dirección de la imagen es demasiado larga.';
    else {
        try {
            if ($categoria > 0) {
                $sql = $id ? 'UPDATE productos SET id_categoria=?, nombre=?, descripcion=?, precio=?, stock=?, imagen=? WHERE id=?' : 'INSERT INTO productos (id_categoria, nombre, descripcion, precio, stock, imagen) VALUES (?, ?, ?, ?, ?, ?)';
                $values = [$categoria, $nombre, $descripcion, $precio, $stock, $imagen];
            } else {
                $sql = $id ? 'UPDATE productos SET nombre=?, descripcion=?, precio=?, stock=?, imagen=? WHERE id=?' : 'INSERT INTO productos (nombre, descripcion, precio, stock, imagen) VALUES (?, ?, ?, ?, ?)';
                $values = [$nombre, $descripcion, $precio, $stock, $imagen];
            }
            if ($id) $values[] = $id;
            $save = $conexion->prepare($sql); $save->execute($values);
            header('Location: productos.php?guardado=1'); exit;
        } catch (Throwable $e) { $error = 'No se pudo guardar. Revisa la estructura de productos y categorías.'; }
    }
}

$edit = null;
if (isset($_GET['editar'])) { $find = $conexion->prepare('SELECT * FROM productos WHERE id=?'); $find->execute([(int)$_GET['editar']]); $edit = $find->fetch(); }
try { $allProducts = $conexion->query('SELECT * FROM productos ORDER BY id DESC')->fetchAll(); }
catch (Throwable $e) { $allProducts = []; $error = 'No está disponible la tabla productos.'; }
try { $categories = $conexion->query('SELECT id,nombre FROM categorias ORDER BY nombre')->fetchAll(); }
catch (Throwable $e) { $categories = []; }
$query = trim($_GET['q'] ?? '');
$products = array_values(array_filter($allProducts, static function ($product) use ($query) { return $query === '' || stripos($product['nombre'], $query) !== false; }));
$stockUnits = array_sum(array_map(static fn($product) => (int)$product['stock'], $allProducts));
$lowStock = count(array_filter($allProducts, static fn($product) => (int)$product['stock'] < 5));
$avatar = !empty($_SESSION['foto_perfil']) ? '../' . $_SESSION['foto_perfil'] : 'https://ui-avatars.com/api/?name=' . rawurlencode($_SESSION['usuario_nombre'] ?? 'Admin') . '&background=0876ea&color=fff';
function h($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Productos | Sport Max Admin</title><link rel="stylesheet" href="admin.css"></head><body class="admin-body">
<aside class="admin-sidebar"><a class="admin-brand" href="../index.php">SPORT<span>MAX</span></a><p class="admin-label">Administración</p><nav class="admin-nav"><a href="dashboard.php"><span class="ico">◈</span>Resumen</a><a class="active" href="productos.php"><span class="ico">▣</span>Productos</a><a href="usuarios.php"><span class="ico">♙</span>Usuarios</a><a href="mensajes.php"><span class="ico">✉</span>Mensajes</a><a href="../perfil.php"><span class="ico">◉</span>Mi perfil</a></nav><div class="sidebar-account"><img src="<?= h($avatar) ?>" alt=""><div><strong><?= h($_SESSION['usuario_nombre'] ?? 'Admin') ?></strong><small>Administrador</small></div><a href="../logout.php">↗</a></div></aside>
<main class="admin-main"><header class="admin-topbar"><span class="crumb">Sport Max / Catálogo</span><div class="top-links"><a href="../index.php">↗ Ver tienda</a><a href="../logout.php">Cerrar sesión</a></div></header>
<section class="admin-hero"><span class="kicker">Inventario Sport Max</span><h1>Productos que mueven el juego</h1><p>Organiza el catálogo, cuida tu inventario y mantén la tienda al día.</p></section>
<?php if ($error): ?><div class="error-note" role="alert"><?= h($error) ?></div><?php endif; ?>
<?php if (isset($_GET['guardado'])): ?><div class="success-note">Producto guardado correctamente.</div><?php elseif (isset($_GET['eliminado'])): ?><div class="success-note">Producto eliminado del catálogo.</div><?php endif; ?>
<section class="stats-row"><article class="mini-stat"><span class="stat-ico">▣</span><div><small>Productos en catálogo</small><strong><?= count($allProducts) ?></strong></div></article><article class="mini-stat"><span class="stat-ico">▤</span><div><small>Unidades en inventario</small><strong><?= number_format($stockUnits) ?></strong></div></article><article class="mini-stat"><span class="stat-ico">⚠</span><div><small>Stock bajo</small><strong><?= $lowStock ?></strong></div></article><article class="mini-stat"><span class="stat-ico">◉</span><div><small>Categorías</small><strong><?= count($categories) ?></strong></div></article></section>
<div class="heading-row"><div><h2><?= $edit ? 'Editar producto' : 'Añadir al catálogo' ?></h2><p>Completa la información que verán tus clientes en la tienda.</p></div><?php if ($edit): ?><a class="admin-button-secondary" href="productos.php">＋ Nuevo producto</a><?php endif; ?></div>
<section class="admin-card"><form method="post"><input type="hidden" name="id" value="<?= h($edit['id'] ?? '') ?>"><div class="form-grid"><div class="form-field"><label for="nombre">Nombre del producto</label><input class="field" id="nombre" name="nombre" maxlength="180" required value="<?= h($edit['nombre'] ?? '') ?>" placeholder="Ej. Tenis de running"></div><div class="form-field"><label for="categoria">Categoría</label><select class="field" id="categoria" name="id_categoria"><option value="0">Sin categoría</option><?php foreach ($categories as $category): ?><option value="<?= (int)$category['id'] ?>" <?= isset($edit['id_categoria']) && (int)$edit['id_categoria'] === (int)$category['id'] ? 'selected' : '' ?>><?= h($category['nombre']) ?></option><?php endforeach; ?></select></div><div class="form-field"><label for="precio">Precio (COP)</label><input class="field" id="precio" name="precio" type="number" min="0" step="100" required value="<?= h($edit['precio'] ?? '') ?>" placeholder="120000"></div><div class="form-field"><label for="stock">Unidades disponibles</label><input class="field" id="stock" name="stock" type="number" min="0" step="1" required value="<?= h($edit['stock'] ?? '0') ?>"></div><div class="form-field full"><label for="imagen">Imagen del producto · URL</label><input class="field" id="imagen" name="imagen" maxlength="500" value="<?= h($edit['imagen'] ?? '') ?>" placeholder="https://..."></div><div class="form-field full"><label for="descripcion">Descripción</label><textarea class="field" id="descripcion" name="descripcion" placeholder="Materiales, uso recomendado, características..."><?= h($edit['descripcion'] ?? '') ?></textarea></div></div><div class="form-actions"><button class="admin-button" type="submit"><?= $edit ? '✓ Guardar cambios' : '＋ Crear producto' ?></button><?php if ($edit): ?><a class="admin-button-secondary" href="productos.php">Cancelar</a><?php endif; ?></div></form></section>
<div class="heading-row"><div><h2>Tu catálogo</h2><p><?= count($products) ?> producto<?= count($products) === 1 ? '' : 's' ?><?= $query ? ' para “' . h($query) . '”' : '' ?></p></div></div>
<section class="admin-card"><div class="toolbar"><form class="search-form" method="get"><input type="search" name="q" value="<?= h($query) ?>" placeholder="Buscar producto por nombre"><button class="admin-button-secondary">Buscar</button></form></div><div class="table-wrap"><table class="admin-table"><thead><tr><th>Producto</th><th>Precio</th><th>Stock</th><th>Acciones</th></tr></thead><tbody><?php foreach ($products as $product): ?><tr><td><div class="item-cell"><img src="<?= h(!empty($product['imagen']) && $product['imagen'] !== 'default.jpg' ? $product['imagen'] : 'https://placehold.co/90x90/eaf3ff/0876ea?text=SM') ?>" alt=""><span><?= h($product['nombre']) ?></span></div></td><td>$<?= number_format((float)$product['precio'], 0, ',', '.') ?></td><td><span class="badge <?= (int)$product['stock'] < 5 ? 'low' : '' ?>"><?= (int)$product['stock'] ?> unidades</span></td><td><div class="form-actions"><a class="admin-button-secondary" href="?editar=<?= (int)$product['id'] ?>">Editar</a><form method="post" onsubmit="return confirm('¿Eliminar este producto del catálogo?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$product['id'] ?>"><button class="admin-button-danger" type="submit">Eliminar</button></form></div></td></tr><?php endforeach; ?><?php if (!$products): ?><tr><td colspan="4"><div class="admin-empty">No encontramos productos con ese nombre.</div></td></tr><?php endif; ?></tbody></table></div></section><footer class="admin-footer">SPORT MAX · Administración de catálogo</footer></main>
</body></html>
