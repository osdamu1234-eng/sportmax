<?php
session_start();
require_once __DIR__ . '/conexion.php';
$productosTienda = [];
try {
    $productosTienda = $conexion->query('SELECT p.*, c.nombre AS categoria FROM productos p LEFT JOIN categorias c ON p.id_categoria = c.id ORDER BY p.id DESC')->fetchAll();
} catch (Throwable $e) {
    // La tienda sigue cargando aunque el catálogo todavía no esté creado.
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPORT MAX | Ropa, Calzado y Accesorios Deportivos</title>
    <!-- Fuentes tipográficas modernas de Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Estilos complementarios para Modal de Carrito y Categorías Interactivas */
        .category-card { cursor: pointer; transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .category-card:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,0.15); }
        .cart-badge { background-color: #e63946; color: white; border-radius: 50%; padding: 2px 7px; font-size: 0.8rem; margin-left: 5px; }
        
        /* Modal Carrito */
        .cart-modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); justify-content: center; align-items: center; }
        .cart-modal-content { background-color: #fff; padding: 25px; border-radius: 10px; width: 90%; max-width: 500px; max-height: 80vh; overflow-y: auto; position: relative; box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
        .cart-item { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding: 10px 0; }
        .cart-item button { background: #ff4d4d; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; }
        .filter-btn-container { text-align: center; margin-bottom: 20px; }
        .btn-filter { background: #333; color: white; border: none; padding: 8px 16px; border-radius: 20px; cursor: pointer; margin: 0 5px; font-size: 0.9rem; }
        .btn-filter.active { background: #0066cc; }
    </style>
</head>
<body>

    <!-- 1. HEADER & NAVEGACIÓN -->
    <header class="header">
        <div class="logo">SPORT<span>MAX</span></div>
        
        <!-- Botón Menú Hamburguesa para móviles -->
        <button class="menu-toggle" id="menuToggle" onclick="toggleMenu()" aria-label="Abrir menú">
            ☰
        </button>

        <nav class="navbar" id="navbar">
            <a href="#inicio" onclick="cerrarMenu()">Inicio</a>
            <a href="#nosotros" onclick="cerrarMenu()">Nosotros</a>
            <a href="#categorias" onclick="cerrarMenu()">Categorías</a>
            <a href="#catalogo" onclick="cerrarMenu()">Catálogo</a>
            <a href="#contacto" onclick="cerrarMenu()">Contáctenos</a>

            <?php if (isset($_SESSION['nombre_usuario'])): ?>
                <?php if (!empty($_SESSION['foto_perfil'])): ?><img src="<?= htmlspecialchars($_SESSION['foto_perfil'], ENT_QUOTES, 'UTF-8') ?>" alt="Foto de perfil" style="width:34px;height:34px;object-fit:cover;border-radius:50%;vertical-align:middle"><?php endif; ?>
                <span class="user-welcome">Hola, <?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?></span>
                <a href="perfil.php" class="user-welcome">Mi perfil</a>
                <?php if (($_SESSION['usuario_rol'] ?? '') === 'admin'): ?><a href="admin/dashboard.php" class="user-welcome">Administración</a><?php endif; ?>
                <a href="logout.php" class="btn-logout" onclick="cerrarMenu()">Cerrar Sesión</a>
            <?php else: ?>
                <button class="btn-login" onclick="abrirModal(); cerrarMenu();">Iniciar Sesión</button>
            <?php endif; ?>

            <!-- BOTÓN CARRITO DE COMPRAS -->
            <button class="btn-login" style="background-color: #111;" onclick="abrirCarrito()">
                🛒 Carrito <span id="cart-count" class="cart-badge">0</span>
            </button>
        </nav>
    </header>

    <!-- 2. SECCIÓN INICIO -->
    <section id="inicio" class="hero-section">
        <div class="hero-content">
            <h1>Ropa y Calzado para el Máximo Rendimiento</h1>
            <p>Equípate con lo mejor en moda deportiva. Calidad, estilo y comodidad para superar todos tus límites.</p>
            <a href="#catalogo" class="btn-primary">Explorar Catálogo</a>
        </div>
    </section>

    <!-- 3. SECCIÓN SOBRE NOSOTROS -->
    <section id="nosotros" class="about-section">
        <div class="about-container">
            <div class="about-text">
                <h2>Quiénes Somos</h2>
                <hr class="divider">
                <p>En <strong>SPORT MAX</strong> nos dedicamos a la venta de ropa, calzado y accesorios deportivos para distintas disciplinas. Nuestra prioridad es ofrecer prendas de alta tecnología y rendimiento que acompañen a cada atleta en su rutina diaria.</p>
            </div>

            <div class="mision-vision-grid">
                <div class="mv-card">
                    <div class="mv-icon">⚡</div>
                    <h3>Misión</h3>
                    <p>Brindar vestuario y equipo deportivo de alto desempeño a nuestros clientes, combinando la mejor calidad y confort con un servicio eficiente y personalizado.</p>
                </div>
                
                <div class="mv-card">
                    <div class="mv-icon">🏆</div>
                    <h3>Visión</h3>
                    <p>Convertirnos en la tienda deportiva líder de la región, destacando por nuestra transformación digital, inventario variado y compromiso con la comunidad deportiva.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. SECCIÓN CATEGORÍAS DE PRODUCTOS (Interactivas) -->
    <section id="categorias" class="categories-section">
        <h2>Categorías Destacadas</h2>
        <hr class="divider">
        <div class="categories-grid">
            <div class="category-card" onclick="filtrarCategoria('calzado')">
                <div class="cat-icon">👟</div>
                <h3>Calzado Deportivo</h3>
                <p>Tenis para running, entrenamiento, fútbol y baloncesto de alta amortiguación.</p>
            </div>
            <div class="category-card" onclick="filtrarCategoria('ropa')">
                <div class="cat-icon">👕</div>
                <h3>Ropa Deportiva</h3>
                <p>Camisetas transpirables, sudaderas, chaquetas y licras de alta compresión.</p>
            </div>
            <div class="category-card" onclick="filtrarCategoria('accesorios')">
                <div class="cat-icon">🎒</div>
                <h3>Accesorios</h3>
                <p>Mochilas, termos, guantes, balones y complementos para tu disciplina.</p>
            </div>
        </div>
    </section>

    <!-- 5. SECCIÓN CATÁLOGO DE PRODUCTOS -->
    <section id="catalogo" class="catalog-section">
        <h2>Catálogo de Productos</h2>
        <hr class="divider">

        <!-- Botones de Filtro por Categoría -->
        <div class="filter-btn-container">
            <button class="btn-filter active" onclick="filtrarCategoria('todas')">Todos</button>
            <button class="btn-filter" onclick="filtrarCategoria('calzado')">Calzado</button>
            <button class="btn-filter" onclick="filtrarCategoria('ropa')">Ropa</button>
            <button class="btn-filter" onclick="filtrarCategoria('accesorios')">Accesorios</button>
        </div>

        <div class="products-grid">
            <?php if (!$productosTienda): ?><p>Pronto encontrarás aquí nuestro catálogo.</p><?php endif; ?>
            <?php foreach ($productosTienda as $producto):
                $nombreProducto=(string)$producto['nombre']; $precioProducto=(float)$producto['precio'];
                $catNombre=strtolower((string)($producto['categoria']??''));
                $categoriaProducto=str_contains($catNombre,'calz')?'calzado':(str_contains($catNombre,'ropa')?'ropa':(str_contains($catNombre,'acces')?'accesorios':'todas'));
                $imagenProducto=trim((string)($producto['imagen']??''));
                if($imagenProducto===''||$imagenProducto==='default.jpg') $imagenProducto='https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=600&q=80';
            ?>
            <article class="product-card" data-categoria="<?= htmlspecialchars($categoriaProducto,ENT_QUOTES,'UTF-8') ?>">
                <img src="<?= htmlspecialchars($imagenProducto,ENT_QUOTES,'UTF-8') ?>" alt="<?= htmlspecialchars($nombreProducto,ENT_QUOTES,'UTF-8') ?>" class="product-img" loading="lazy">
                <div class="product-info"><h4><?= htmlspecialchars($nombreProducto,ENT_QUOTES,'UTF-8') ?></h4>
                <?php if(!empty($producto['descripcion'])): ?><p><?= htmlspecialchars($producto['descripcion'],ENT_QUOTES,'UTF-8') ?></p><?php endif; ?>
                <p class="price">$<?= number_format($precioProducto,0,',','.') ?> COP</p>
                <button class="btn-buy" onclick='agregarAlCarrito(<?= json_encode($nombreProducto,JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_TAG|JSON_HEX_AMP) ?>,<?= json_encode($precioProducto) ?>)'>Agregar al Carrito</button></div>
            </article>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- 6. SECCIÓN CONTÁCTENOS -->
    <section id="contacto" class="contact-section">
        <div class="contact-container">
            <h2>Contáctenos</h2>
            <hr class="divider">
            <p>¿Tienes preguntas sobre alguna prenda o pedido? Escríbenos directamente.</p>
            <form action="contacto_be.php" method="POST" class="contact-form">
                <input type="text" name="nombre" placeholder="Nombre Completo" required>
                <input type="email" name="email" placeholder="Correo Electrónico" required>
                
                <select name="tipo_mensaje" required style="width: 100%; padding: 12px; margin-bottom: 15px; border-radius: 5px; border: 1px solid #ccc; font-size: 0.95rem; background-color: #fff; color: #333;">
                    <option value="" disabled selected>-- Selecciona tipo de mensaje --</option>
                    <option value="Solicitud">Solicitud</option>
                    <option value="Queja">Queja</option>
                    <option value="Reclamo">Reclamo</option>
                    <option value="Sugerencia">Sugerencia</option>
                    <option value="Felicitación">Felicitación</option>
                </select>

                <textarea name="mensaje" rows="4" placeholder="¿En qué te podemos asesorar?" required></textarea>
                <button type="submit" class="btn-submit">Enviar Mensaje</button>
            </form>
        </div>
    </section>

    <!-- 7. MODAL DE ACCESO (INICIO DE SESIÓN Y REGISTRO) -->
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="cerrarModal()">&times;</span>
            
            <div class="modal-tabs">
                <button class="tab-btn active" onclick="cambiarTab(event, 'tab-login')">Iniciar Sesión</button>
                <button class="tab-btn" onclick="cambiarTab(event, 'tab-registro')">Registrarse</button>
            </div>

            <!-- Formulario de Login -->
            <div id="tab-login" class="tab-container active">
                <p class="subtitle">Ingresa a tu cuenta de Sport Max</p>
                <?php if (isset($_GET['error']) && in_array($_GET['error'], ['login','permisos','sesion'], true)): ?><p role="alert" style="color:#b42318">Correo o contraseña incorrectos, o debes iniciar sesión para continuar.</p><?php endif; ?>
                <form action="login_be.php" method="POST" class="login-form">
                    <div class="input-group">
                        <label>Correo Electrónico</label>
                        <input type="email" name="correo_electronico" required placeholder="correo@ejemplo.com">
                    </div>
                    <div class="input-group">
                        <label>Contraseña</label>
                        <input type="password" name="contrasena" required placeholder="••••••••">
                    </div>
                    <button type="submit" class="btn-login-submit">Ingresar</button>
                </form>
            </div>

            <!-- Formulario de Registro -->
            <div id="tab-registro" class="tab-container">
                <p class="subtitle">Crea tu cuenta de cliente en Sport Max</p>
                <?php if (isset($_GET['error']) && in_array($_GET['error'], ['registro','correo'], true)): ?><p role="alert" style="color:#b42318"><?= $_GET['error'] === 'correo' ? 'Ese correo ya tiene una cuenta.' : 'Revisa los datos. La contraseña debe tener mínimo 8 caracteres.' ?></p><?php endif; ?>
                <form action="registro_be.php" method="POST" class="login-form">
                    <div class="input-group">
                        <label>Nombre Completo</label>
                        <input type="text" name="nombre_completo" required placeholder="Ej. Carlos Pérez">
                    </div>
                    <div class="input-group">
                        <label>Correo Electrónico</label>
                        <input type="email" name="correo_electronico" required placeholder="correo@ejemplo.com">
                    </div>
                    <div class="input-group">
                        <label>Contraseña</label>
                        <input type="password" name="contrasena" required placeholder="Crea tu contraseña">
                    </div>
                    <button type="submit" class="btn-login-submit">Crear Cuenta</button>
                </form>
            </div>
        </div>
    </div>

    <!-- 8. MODAL DE CARRITO DE COMPRAS -->
    <div id="cartModal" class="cart-modal">
        <div class="cart-modal-content">
            <span class="close-btn" onclick="cerrarCarrito()" style="float:right; cursor:pointer; font-size:1.5rem;">&times;</span>
            <h2>Tu Carrito de Compras</h2>
            <hr class="divider">
            <div id="cart-items-container" style="margin-top:15px;">
                <!-- Se llena dinámicamente con JS -->
            </div>
            <div style="margin-top:20px; text-align:right;">
                <h3>Total: $<span id="cart-total-price">0</span> COP</h3>
            </div>
            <div style="margin-top:20px; display:flex; justify-content:space-between;">
                <button onclick="vaciarCarrito()" style="background:#888; color:white; border:none; padding:10px 15px; border-radius:5px; cursor:pointer;">Vaciar Carrito</button>
                <button onclick="alert('¡Gracias por tu compra! Procesando pedido...')" style="background:#0066cc; color:white; border:none; padding:10px 15px; border-radius:5px; cursor:pointer;">Finalizar Compra</button>
            </div>
        </div>
    </div>

    <!-- 9. FOOTER -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-logo">
                <h3>SPORT<span>MAX</span></h3>
                <p>Tu tienda de confianza para ropa, calzado y accesorios deportivos.</p>
            </div>
            <div class="footer-links">
                <h4>Enlaces Rápidos</h4>
                <a href="#inicio">Inicio</a>
                <a href="#nosotros">Nosotros</a>
                <a href="#catalogo">Catálogo</a>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 Sport Max. Desarrollado por Matthew Navarro, Juan Camilo Serna & Nicolás Ocampo - SENA Regional Quindío.</p>
        </div>
    </footer>

    <!-- SCRIPTS DE INTERACCIÓN Y LÓGICA DE CARRITO -->
    <script>
        // LÓGICA DEL MENÚ NAVEGACIÓN
        function toggleMenu() {
            let navbar = document.getElementById('navbar');
            let toggleBtn = document.getElementById('menuToggle');
            navbar.classList.toggle('active');
            toggleBtn.innerHTML = navbar.classList.contains('active') ? '✕' : '☰';
        }

        function cerrarMenu() {
            let navbar = document.getElementById('navbar');
            let toggleBtn = document.getElementById('menuToggle');
            if (navbar.classList.contains('active')) {
                navbar.classList.remove('active');
                toggleBtn.innerHTML = '☰';
            }
        }  

        // LÓGICA DE MODAL LOGIN
        function abrirModal() {
            document.getElementById('loginModal').style.display = 'flex';
        }

        function cerrarModal() {
            document.getElementById('loginModal').style.display = 'none';
        }

        function cambiarTab(evt, tabId) {
            let tabContainers = document.getElementsByClassName("tab-container");
            for (let i = 0; i < tabContainers.length; i++) {
                tabContainers[i].classList.remove("active");
            }
            let tabBtns = document.getElementsByClassName("tab-btn");
            for (let i = 0; i < tabBtns.length; i++) {
                tabBtns[i].classList.remove("active");
            }
            document.getElementById(tabId).classList.add("active");
            evt.currentTarget.classList.add("active");
        }

        // LÓGICA DE FILTRADO POR CATEGORÍAS
        function filtrarCategoria(cat) {
            let productos = document.querySelectorAll('.product-card');
            productos.forEach(prod => {
                if (cat === 'todas' || prod.getAttribute('data-categoria') === cat) {
                    prod.style.display = 'block';
                } else {
                    prod.style.display = 'none';
                }
            });

            // Dirigir suavemente al catálogo
            window.location.href = '#catalogo';
        }

        // LÓGICA DE CARRITO DE COMPRAS (LOCALSTORAGE)
        let carrito = JSON.parse(localStorage.getItem('sportmax_cart')) || [];

        function actualizarUI() {
            let countSpan = document.getElementById('cart-count');
            let totalCantidad = carrito.reduce((acc, item) => acc + item.cantidad, 0);
            countSpan.innerText = totalCantidad;

            let container = document.getElementById('cart-items-container');
            let totalContainer = document.getElementById('cart-total-price');
            
            if (container) {
                container.innerHTML = '';
                let totalPrecio = 0;

                if (carrito.length === 0) {
                    container.innerHTML = '<p style="text-align:center; color:#777;">El carrito está vacío.</p>';
                } else {
                    carrito.forEach((prod, index) => {
                        let subtotal = prod.precio * prod.cantidad;
                        totalPrecio += subtotal;
                        container.innerHTML += `
                            <div class="cart-item">
                                <div>
                                    <strong>${prod.nombre}</strong><br>
                                    <small>$${prod.precio.toLocaleString('es-CO')} x ${prod.cantidad}</small>
                                </div>
                                <div>
                                    <span>$${subtotal.toLocaleString('es-CO')}</span>
                                    <button onclick="eliminarDelCarrito(${index})" style="margin-left:10px;">✕</button>
                                </div>
                            </div>
                        `;
                    });
                }
                totalContainer.innerText = totalPrecio.toLocaleString('es-CO');
            }

            localStorage.setItem('sportmax_cart', JSON.stringify(carrito));
        }

        function agregarAlCarrito(nombre, precio) {
            let index = carrito.findIndex(item => item.nombre === nombre);
            if (index !== -1) {
                carrito[index].cantidad++;
            } else {
                carrito.push({ nombre: nombre, precio: precio, cantidad: 1 });
            }
            actualizarUI();
            alert(`¡"${nombre}" ha sido agregado al carrito!`);
        }

        function eliminarDelCarrito(index) {
            carrito.splice(index, 1);
            actualizarUI();
        }

        function vaciarCarrito() {
            carrito = [];
            actualizarUI();
        }

        function abrirCarrito() {
            actualizarUI();
            document.getElementById('cartModal').style.display = 'flex';
        }

        function cerrarCarrito() {
            document.getElementById('cartModal').style.display = 'none';
        }

        // CERRAR MODALES AL HACER CLIC FUERA
        window.onclick = function(event) {
            let loginModal = document.getElementById('loginModal');
            let cartModal = document.getElementById('cartModal');
            if (event.target == loginModal) loginModal.style.display = 'none';
            if (event.target == cartModal) cartModal.style.display = 'none';
        }

        // Cargar estado inicial del carrito al iniciar
        document.addEventListener('DOMContentLoaded', function() {
            actualizarUI();
            const params = new URLSearchParams(window.location.search);
            if (params.has('error')) {
                abrirModal();
                if (['registro', 'correo'].includes(params.get('error'))) {
                    document.querySelector('.tab-btn:nth-child(2)').click();
                }
            }
            if (params.has('registro')) alert('¡Tu cuenta fue creada correctamente!');
        });
    </script>
</body>
</html>

