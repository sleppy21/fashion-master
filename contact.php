<?php
/**
 * PÁGINA DE CONTACTO - SLEPPY STORE
 * Formulario de contacto y información
 */

session_start();
require_once 'config/conexion.php';

$page_title = "Contacto - SleppyStore";

// Verificar si hay usuario logueado
$usuario_logueado = null;
if(isset($_SESSION['user_id'])) {
    try {
        $usuario_resultado = executeQuery("SELECT * FROM usuario WHERE id_usuario = ? AND status_usuario = 1", [$_SESSION['user_id']]);
        $usuario_logueado = ($usuario_resultado && count($usuario_resultado) > 0) ? $usuario_resultado[0] : null;
    } catch(Exception $e) {
        error_log("Error al verificar usuario: " . $e->getMessage());
        $usuario_logueado = null;
    }
}

// Obtener contadores
$cart_count = 0;
$favorites_count = 0;
$notifications_count = 0;
if($usuario_logueado) {
    try {
        $cart_items = executeQuery("SELECT COUNT(*) as total FROM carrito WHERE id_usuario = ?", [$usuario_logueado['id_usuario']]);
        $cart_count = ($cart_items && count($cart_items) > 0) ? ($cart_items[0]['total'] ?? 0) : 0;
        
        $favorites = executeQuery("SELECT COUNT(*) as total FROM favorito WHERE id_usuario = ?", [$usuario_logueado['id_usuario']]);
        $favorites_count = ($favorites && count($favorites) > 0) ? ($favorites[0]['total'] ?? 0) : 0;
        
        $notifications = executeQuery("SELECT COUNT(*) as total FROM notificacion WHERE id_usuario = ? AND leida_notificacion = 0 AND estado_notificacion = 'activo'", [$usuario_logueado['id_usuario']]);
        $notifications_count = ($notifications && count($notifications) > 0) ? ($notifications[0]['total'] ?? 0) : 0;
    } catch(Exception $e) {
        error_log("Error al obtener contadores: " . $e->getMessage());
    }
}

// Obtener categorías para el menú
$categorias = [];
try {
    $categorias_resultado = executeQuery("SELECT id_categoria, nombre_categoria FROM categoria WHERE status_categoria = 1 ORDER BY nombre_categoria ASC");
    $categorias = $categorias_resultado ? $categorias_resultado : [];
} catch(Exception $e) {
    error_log("Error al obtener categorías: " . $e->getMessage());
}

// Obtener marcas para el menú
$marcas = [];
try {
    $marcas_resultado = executeQuery("SELECT id_marca, nombre_marca FROM marca WHERE status_marca = 1 ORDER BY nombre_marca ASC");
    $marcas = $marcas_resultado ? $marcas_resultado : [];
} catch(Exception $e) {
    error_log("Error al obtener marcas: " . $e->getMessage());
}

// Procesar formulario de contacto
$mensaje_enviado = false;
$error_envio = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar_contacto'])) {
    $nombre = $_POST['nombre'] ?? '';
    $email = $_POST['email'] ?? '';
    $asunto = $_POST['asunto'] ?? '';
    $mensaje = $_POST['mensaje'] ?? '';
    
    // Validar campos
    if (!empty($nombre) && !empty($email) && !empty($mensaje)) {
        // Aquí puedes agregar la lógica para guardar en BD o enviar email
        // Por ahora solo mostraremos mensaje de éxito
        $mensaje_enviado = true;
    } else {
        $error_envio = true;
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="description" content="Contacta con Sleppy Store - Tu tienda de moda online">
    <meta name="keywords" content="contacto, tienda moda, sleppy store">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo $page_title; ?></title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Cookie&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Css Styles -->
    <link rel="stylesheet" href="public/assets/css/bootstrap.min.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/font-awesome.min.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/elegant-icons.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/jquery-ui.min.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/magnific-popup.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/owl.carousel.min.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/slicknav.min.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/style.css" type="text/css">
    
    <!-- AOS Animation -->
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
    
    <!-- Modals CSS -->
    <link rel="stylesheet" href="public/assets/css/user-account-modal.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/favorites-modal.css" type="text/css">
    
    <!-- Dark Mode CSS -->
    <link rel="stylesheet" href="public/assets/css/dark-mode.css?v=<?php echo time(); ?>" type="text/css">
    
    <!-- Global Responsive CSS -->
    <link rel="stylesheet" href="public/assets/css/global-responsive.css?v=1.0" type="text/css">
    
    <!-- Modern Styles -->
    <link rel="stylesheet" href="public/assets/css/shop/product-cards-modern.css?v=3.0">
    <link rel="stylesheet" href="public/assets/css/modals-animations.css?v=<?= time() ?>">
    <link rel="stylesheet" href="public/assets/css/notifications-modal.css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        /* Estilos personalizados para la página de contacto */
        .contact {
            padding: 80px 0;
        }
        
        .contact__content {
            background: var(--bg-primary, #fff);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }
        
        .dark-mode .contact__content {
            background: var(--bg-secondary, #1a1a1a);
            box-shadow: 0 2px 20px rgba(255,255,255,0.05);
        }
        
        .contact__address h5,
        .contact__form h5 {
            color: var(--text-primary, #111);
            font-weight: 700;
            margin-bottom: 30px;
            font-size: 24px;
            position: relative;
            padding-bottom: 15px;
        }
        
        .contact__address h5::after,
        .contact__form h5::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: #ca1515;
        }
        
        .contact__address ul {
            list-style: none;
            padding: 0;
        }
        
        .contact__address ul li {
            margin-bottom: 30px;
            padding: 20px;
            background: var(--bg-secondary, #f8f8f8);
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .dark-mode .contact__address ul li {
            background: var(--bg-primary, #2a2a2a);
        }
        
        .contact__address ul li:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .contact__address ul li h6 {
            color: var(--text-primary, #111);
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .contact__address ul li h6 i {
            color: #ca1515;
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .contact__address ul li p {
            color: var(--text-secondary, #666);
            margin: 0;
            line-height: 1.8;
        }
        
        .contact__form {
            margin-top: 50px;
        }
        
        .contact__form input,
        .contact__form textarea {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid var(--border-color, #e5e5e5);
            background: var(--bg-secondary, #f8f8f8);
            color: var(--text-primary, #111);
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-family: 'Montserrat', sans-serif;
        }
        
        .dark-mode .contact__form input,
        .dark-mode .contact__form textarea {
            background: var(--bg-primary, #2a2a2a);
            border-color: var(--border-color, #3a3a3a);
        }
        
        .contact__form input:focus,
        .contact__form textarea:focus {
            outline: none;
            border-color: #ca1515;
            background: var(--bg-primary, #fff);
            box-shadow: 0 0 0 4px rgba(202, 21, 21, 0.1);
        }
        
        .dark-mode .contact__form input:focus,
        .dark-mode .contact__form textarea:focus {
            background: var(--bg-secondary, #1a1a1a);
        }
        
        .contact__form textarea {
            min-height: 150px;
            resize: vertical;
        }
        
        .contact__map {
            height: 100%;
            min-height: 600px;
        }
        
        .contact__map iframe {
            width: 100%;
            height: 100%;
            min-height: 600px;
            border-radius: 10px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }
        
        .site-btn {
            background: #ca1515;
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 14px;
        }
        
        .site-btn:hover {
            background: #a01111;
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(202, 21, 21, 0.3);
        }
        
        .site-btn:active {
            transform: translateY(0);
        }
        
        /* Breadcrumb personalizado */
        .breadcrumb-option {
            padding: 30px 0;
            background: var(--bg-secondary, #f8f8f8);
            border-bottom: 1px solid var(--border-color, #e5e5e5);
        }
        
        .dark-mode .breadcrumb-option {
            background: var(--bg-primary, #1a1a1a);
            border-bottom-color: var(--border-color, #2a2a2a);
        }
        
        .breadcrumb__links {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .breadcrumb__links a {
            color: var(--text-secondary, #666);
            text-decoration: none;
            transition: color 0.3s;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .breadcrumb__links a:hover {
            color: #ca1515;
        }
        
        .breadcrumb__links span {
            color: var(--text-primary, #111);
            font-weight: 600;
        }
        
        /* Responsive */
        @media (max-width: 991px) {
            .contact__map {
                margin-top: 40px;
                min-height: 400px;
            }
            
            .contact__map iframe {
                min-height: 400px;
            }
        }
        
        @media (max-width: 768px) {
            .contact__content {
                padding: 25px;
            }
            
            .contact__form {
                margin-top: 30px;
            }
            
            .contact__address h5,
            .contact__form h5 {
                font-size: 20px;
            }
            
            .contact {
                padding: 40px 0;
            }
        }
    </style>
</head>

<body>
    <!-- Page Preloder -->
    <div id="preloder">
        <div class="loader"></div>
    </div>

    <!-- Offcanvas Menu -->
    <?php include 'includes/offcanvas-menu.php'; ?>
    
    <!-- Header Section -->
    <?php include 'includes/header-section.php'; ?>

    <!-- Breadcrumb Begin -->
    <div class="breadcrumb-option">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="breadcrumb__links">
                        <a href="./index.php"><i class="fa fa-home"></i> Inicio</a>
                        <span>Contacto</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Breadcrumb End -->

    <!-- Contact Section Begin -->
    <section class="contact spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-md-6" data-aos="fade-right">
                    <div class="contact__content">
                        <div class="contact__address">
                            <h5>Información de Contacto</h5>
                            <ul>
                                <li data-aos="fade-up" data-aos-delay="100">
                                    <h6><i class="fa fa-map-marker"></i> Dirección</h6>
                                    <p>Av. Principal 123, Lima, Perú</p>
                                </li>
                                <li data-aos="fade-up" data-aos-delay="200">
                                    <h6><i class="fa fa-phone"></i> Teléfonos</h6>
                                    <p><span>+51 999 888 777</span><span style="margin-left: 15px;">+51 999 666 555</span></p>
                                </li>
                                <li data-aos="fade-up" data-aos-delay="300">
                                    <h6><i class="fa fa-envelope"></i> Email</h6>
                                    <p>contacto@sleppystore.com</p>
                                </li>
                                <li data-aos="fade-up" data-aos-delay="400">
                                    <h6><i class="fa fa-clock-o"></i> Horario de Atención</h6>
                                    <p>Lunes a Viernes: 9:00 AM - 6:00 PM<br>Sábados: 9:00 AM - 2:00 PM</p>
                                </li>
                            </ul>
                        </div>
                        <div class="contact__form" data-aos="fade-up" data-aos-delay="500">
                            <h5>Envíanos un Mensaje</h5>
                            <form action="contact.php" method="POST">
                                <input type="text" name="nombre" placeholder="Nombre completo" required>
                                <input type="email" name="email" placeholder="Email" required>
                                <input type="text" name="asunto" placeholder="Asunto">
                                <textarea name="mensaje" placeholder="Tu mensaje" required></textarea>
                                <button type="submit" name="enviar_contacto" class="site-btn">Enviar Mensaje</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6" data-aos="fade-left">
                    <div class="contact__map">
                        <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d62414.59288802869!2d-77.06517247832031!3d-12.046373899999996!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x9105c8b5d35662e7%3A0x14206cb9cc452e4a!2sLima%2C%20Per%C3%BA!5e0!3m2!1ses!2spe!4v1234567890123!5m2!1ses!2spe"
                        height="780" style="border:0" allowfullscreen="">
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Contact Section End -->

    <!-- Instagram Begin -->
    <div class="instagram">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-2 col-md-4 col-sm-4 p-0">
                    <div class="instagram__item set-bg" data-setbg="public/assets/img/instagram/insta-1.jpg">
                        <div class="instagram__text">
                            <i class="fa fa-instagram"></i>
                            <a href="#">@ sleppy_store</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-4 p-0">
                    <div class="instagram__item set-bg" data-setbg="public/assets/img/instagram/insta-2.jpg">
                        <div class="instagram__text">
                            <i class="fa fa-instagram"></i>
                            <a href="#">@ sleppy_store</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-4 p-0">
                    <div class="instagram__item set-bg" data-setbg="public/assets/img/instagram/insta-3.jpg">
                        <div class="instagram__text">
                            <i class="fa fa-instagram"></i>
                            <a href="#">@ sleppy_store</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-4 p-0">
                    <div class="instagram__item set-bg" data-setbg="public/assets/img/instagram/insta-4.jpg">
                        <div class="instagram__text">
                            <i class="fa fa-instagram"></i>
                            <a href="#">@ sleppy_store</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-4 p-0">
                    <div class="instagram__item set-bg" data-setbg="public/assets/img/instagram/insta-5.jpg">
                        <div class="instagram__text">
                            <i class="fa fa-instagram"></i>
                            <a href="#">@ sleppy_store</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-4 p-0">
                    <div class="instagram__item set-bg" data-setbg="public/assets/img/instagram/insta-6.jpg">
                        <div class="instagram__text">
                            <i class="fa fa-instagram"></i>
                            <a href="#">@ sleppy_store</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Instagram End -->

    <!-- Footer Section Begin -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 col-sm-7">
                    <div class="footer__about">
                        <div class="footer__logo">
                            <h3 style="color: var(--text-primary);">Sleppy Store</h3>
                        </div>
                        <p>Tu tienda de moda online de confianza. Las mejores marcas y tendencias al mejor precio.</p>
                        <div class="footer__payment">
                            <a href="#"><img src="public/assets/img/payment/payment-1.png" alt=""></a>
                            <a href="#"><img src="public/assets/img/payment/payment-2.png" alt=""></a>
                            <a href="#"><img src="public/assets/img/payment/payment-3.png" alt=""></a>
                            <a href="#"><img src="public/assets/img/payment/payment-4.png" alt=""></a>
                            <a href="#"><img src="public/assets/img/payment/payment-5.png" alt=""></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-5">
                    <div class="footer__widget">
                        <h6>Enlaces Rápidos</h6>
                        <ul>
                            <li><a href="index.php">Inicio</a></li>
                            <li><a href="shop.php">Tienda</a></li>
                            <li><a href="contact.php">Contacto</a></li>
                            <li><a href="#">FAQ</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-4">
                    <div class="footer__widget">
                        <h6>Mi Cuenta</h6>
                        <ul>
                            <li><a href="profile.php">Mi Perfil</a></li>
                            <li><a href="order-confirmation.php">Mis Pedidos</a></li>
                            <li><a href="cart.php">Carrito</a></li>
                            <li><a href="#">Favoritos</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4 col-md-8 col-sm-8">
                    <div class="footer__newslatter">
                        <h6>NEWSLETTER</h6>
                        <form action="#">
                            <input type="text" placeholder="Email">
                            <button type="submit" class="site-btn">Suscribirse</button>
                        </form>
                        <div class="footer__social">
                            <a href="#"><i class="fa fa-facebook"></i></a>
                            <a href="#"><i class="fa fa-twitter"></i></a>
                            <a href="#"><i class="fa fa-youtube-play"></i></a>
                            <a href="#"><i class="fa fa-instagram"></i></a>
                            <a href="#"><i class="fa fa-pinterest"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="footer__copyright__text">
                        <p>Copyright &copy; <script>document.write(new Date().getFullYear());</script> Sleppy Store. Todos los derechos reservados.</p>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <!-- Footer Section End -->

    <!-- Search Begin -->
    <div class="search-model">
        <div class="h-100 d-flex align-items-center justify-content-center">
            <div class="search-close-switch">+</div>
            <form class="search-model-form" action="shop.php" method="GET">
                <input type="text" name="search" id="search-input" placeholder="Buscar productos...">
            </form>
        </div>
    </div>
    <!-- Search End -->

    <!-- Modales ya incluidos en header-section.php - NO duplicar -->

    <!-- Dark Mode Assets -->
    <?php include 'includes/dark-mode-assets.php'; ?>

    <!-- Chatbot Widget -->
    <?php include 'includes/chatbot-widget.php'; ?>

    <!-- Js Plugins -->
    <script>
        // BASE URL para peticiones AJAX - Compatible con ngrok y cualquier dominio
        (function() {
            var baseUrlFromPHP = '<?php echo defined("BASE_URL") ? BASE_URL : ""; ?>';
            
            // Si no hay BASE_URL definida en PHP, calcularla desde JavaScript
            if (!baseUrlFromPHP || baseUrlFromPHP === '') {
                var path = window.location.pathname;
                var pathParts = path.split('/').filter(function(p) { return p !== ''; });
                
                // Buscar 'fashion-master' en el path
                var basePath = '';
                if (pathParts.includes('fashion-master')) {
                    var index = pathParts.indexOf('fashion-master');
                    basePath = '/' + pathParts.slice(0, index + 1).join('/');
                }
                
                baseUrlFromPHP = window.location.origin + basePath;
            }
            
            // CRÍTICO: Si la página está en HTTPS, forzar BASE_URL a HTTPS
            if (window.location.protocol === 'https:' && baseUrlFromPHP.startsWith('http://')) {
                baseUrlFromPHP = baseUrlFromPHP.replace('http://', 'https://');
            }
            
            window.BASE_URL = baseUrlFromPHP;
        })();
    </script>
    <script src="public/assets/js/jquery-3.3.1.min.js"></script>
    <script src="public/assets/js/bootstrap.min.js"></script>
    <script src="public/assets/js/jquery.magnific-popup.min.js"></script>
    <script src="public/assets/js/jquery-ui.min.js"></script>
    <script src="public/assets/js/mixitup.min.js"></script>
    <script src="public/assets/js/jquery.countdown.min.js"></script>
    <script src="public/assets/js/jquery.slicknav.js"></script>
    <script src="public/assets/js/owl.carousel.min.js"></script>
    <script src="public/assets/js/jquery.nicescroll.min.js"></script>
    <script src="public/assets/js/main.js"></script>
    
    <!-- AOS Animation -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true
        });
    </script>

    <?php if($mensaje_enviado): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: '¡Mensaje Enviado!',
            text: 'Gracias por contactarnos. Te responderemos pronto.',
            confirmButtonText: 'OK',
            confirmButtonColor: '#ca1515'
        });
    </script>
    <?php endif; ?>

    <?php if($error_envio): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Por favor completa todos los campos requeridos.',
            confirmButtonText: 'OK',
            confirmButtonColor: '#ca1515'
        });
    </script>
    <?php endif; ?>
</body>

</html>
