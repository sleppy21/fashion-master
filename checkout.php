<?php
/**
 * PÁGINA DE CHECKOUT - PROCESO DE PAGO
 * Permite al usuario completar su compra
 */

session_start();
require_once 'config/conexion.php';

require_once 'config/config.php'; // <-- AÑADIDO PARA BASE_URL

$page_title = "Finalizar Compra";

// Verificar si el usuario está logueado
$usuario_logueado = null;
if (isset($_SESSION['user_id'])) {
    try {
        $usuario_resultado = executeQuery("SELECT * FROM usuario WHERE id_usuario = ? AND status_usuario = 1", [$_SESSION['user_id']]);
        $usuario_logueado = $usuario_resultado && !empty($usuario_resultado) ? $usuario_resultado[0] : null;
        
        if (!$usuario_logueado) {
            session_destroy();
            header('Location: login.php');
            exit;
        }
    } catch(Exception $e) {
        error_log("Error al obtener usuario: " . $e->getMessage());
        session_destroy();
        header('Location: login.php');
        exit;
    }
} else {
    header('Location: login.php?redirect=checkout.php');
    exit;
}

// Obtener items del carrito
$cart_items = [];
$subtotal = 0;
try {
    $cart_resultado = executeQuery("
        SELECT c.id_carrito, c.cantidad_carrito,
               p.id_producto, p.nombre_producto, p.precio_producto,
               p.descuento_porcentaje_producto, p.url_imagen_producto,
               p.stock_actual_producto,
               m.nombre_marca
        FROM carrito c
        INNER JOIN producto p ON c.id_producto = p.id_producto
        LEFT JOIN marca m ON p.id_marca = m.id_marca
        WHERE c.id_usuario = ? AND p.status_producto = 1
        ORDER BY c.fecha_agregado_carrito DESC
    ", [$usuario_logueado['id_usuario']]);
    
    $cart_items = $cart_resultado ? $cart_resultado : [];
    
    // Si el carrito está vacío, redirigir a cart.php
    if (empty($cart_items)) {
        header('Location: cart.php');
        exit;
    }
    
    // Calcular subtotal
    foreach($cart_items as $item) {
        $precio = $item['precio_producto'];
        if($item['descuento_porcentaje_producto'] > 0) {
            $precio = $precio - ($precio * $item['descuento_porcentaje_producto'] / 100);
        }
        $subtotal += $precio * $item['cantidad_carrito'];
    }
    
    // Calcular descuento total y precio original total
    $precio_original_total = 0;
    $descuento_total = 0;
    foreach($cart_items as $item) {
        $precio_original = $item['precio_producto'] * $item['cantidad_carrito'];
        $precio_original_total += $precio_original;
        
        if($item['descuento_porcentaje_producto'] > 0) {
            $precio_con_descuento = $item['precio_producto'] - ($item['precio_producto'] * $item['descuento_porcentaje_producto'] / 100);
            $precio_final = $precio_con_descuento * $item['cantidad_carrito'];
            $descuento_total += ($precio_original - $precio_final);
        }
    }
} catch(Exception $e) {
    error_log("Error al obtener carrito: " . $e->getMessage());
    header('Location: cart.php');
    exit;
}

// Calcular costos
$costo_envio = $subtotal >= 100 ? 0 : 15; // Envío gratis si compra es >= $100
$total = $subtotal + $costo_envio;

// Obtener contadores para el header
$cart_count = count($cart_items);
$favorites_count = 0;
$notifications_count = 0;
try {
    $favorites = executeQuery("SELECT COUNT(*) as total FROM favorito WHERE id_usuario = ?", [$usuario_logueado['id_usuario']]);
    $favorites_count = $favorites && !empty($favorites) ? (int)$favorites[0]['total'] : 0;
    
    $notifications = executeQuery("SELECT COUNT(*) as total FROM notificacion WHERE id_usuario = ? AND leida_notificacion = 0 AND estado_notificacion = 'activo'", [$usuario_logueado['id_usuario']]);
    $notifications_count = ($notifications && count($notifications) > 0) ? ($notifications[0]['total'] ?? 0) : 0;
} catch(Exception $e) {
    error_log("Error al obtener favoritos/notificaciones: " . $e->getMessage());
}

// Obtener categorías para el menú
$categorias = [];
try {
    $categorias_resultado = executeQuery("SELECT id_categoria, nombre_categoria FROM categoria WHERE status_categoria = 1 ORDER BY id_categoria ASC LIMIT 5");
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

// Obtener favoritos del usuario
$favoritos_ids = [];
try {
    $favoritos = executeQuery("SELECT id_producto FROM favorito WHERE id_usuario = ?", [$usuario_logueado['id_usuario']]);
    if($favoritos) {
        foreach($favoritos as $fav) {
            $favoritos_ids[] = $fav['id_producto'];
        }
    }
} catch(Exception $e) {
    error_log("Error al obtener favoritos: " . $e->getMessage());
}

// Obtener dirección predeterminada del usuario
$direccion_predeterminada = null;
$tiene_direccion_predeterminada = false;
$mostrar_formulario_completo = isset($_GET['show_full_form']); // Usuario quiere ver formulario completo

try {
    $direccion_resultado = executeQuery("
        SELECT * FROM direccion 
        WHERE id_usuario = ? AND es_principal = 1 AND status_direccion = 1
        LIMIT 1
    ", [$usuario_logueado['id_usuario']]);
    
    if($direccion_resultado && !empty($direccion_resultado) && !$mostrar_formulario_completo) {
        $direccion_predeterminada = $direccion_resultado[0];
        $tiene_direccion_predeterminada = true;
    }
} catch(Exception $e) {
    error_log("Error al obtener dirección predeterminada: " . $e->getMessage());
}

// Obtener TODAS las direcciones del usuario para el modal de selección
$todas_direcciones = [];
try {
    $todas_direcciones = executeQuery("
        SELECT * FROM direccion 
        WHERE id_usuario = ? AND status_direccion = 1
        ORDER BY es_principal DESC, fecha_creacion_direccion DESC
    ", [$usuario_logueado['id_usuario']]);
} catch(Exception $e) {
    error_log("Error al obtener todas las direcciones: " . $e->getMessage());
}

// Verificar si usuario tiene alguna dirección guardada
$tiene_direcciones = false;
try {
    $direcciones_count = executeQuery("
        SELECT COUNT(*) as total FROM direccion 
        WHERE id_usuario = ? AND status_direccion = 1
    ", [$usuario_logueado['id_usuario']]);
    
    $tiene_direcciones = ($direcciones_count && $direcciones_count[0]['total'] > 0);
} catch(Exception $e) {
    error_log("Error al verificar direcciones: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="Checkout - SleppyStore">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - SleppyStore</title>

    <script>
        // BASE_URL sin barra final para evitar duplicados
        window.BASE_URL = '<?= rtrim(BASE_URL, "/") ?>';
        // Verificar y corregir protocolo si es necesario
        if (window.location.protocol === 'https:' && window.BASE_URL.startsWith('http:')) {
            window.BASE_URL = window.BASE_URL.replace('http:', 'https:');
        }
    </script>

   <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cookie&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Modern Styles -->
    <link rel="stylesheet" href="public/assets/css/checkout/checkout.css?v=6.6" type="text/css">

</head>
<body class="checkout-page">
    <?php include 'includes/modern-libraries.php'; ?>

    <?php include 'includes/header-section.php'; ?>

    <!-- Breadcrumb -->
    <?php include 'includes/breadcrumb.php'; ?>

    <!-- Checkout Section Begin -->
    <section class="checkout spad">
        <div class="container-fluid px-lg-5">
            <!-- Steps Progress - OCULTO (Espacio reservado para QR de pago) -->
            <div class="checkout-steps" style="display: none;">
                <div class="checkout-step completed">
                    <div class="step-number">1</div>
                    <div class="step-title">Carrito</div>
                </div>
                <div class="checkout-step completed">
                    <div class="step-number">2</div>
                    <div class="step-title">Información</div>
                </div>
                <div class="checkout-step">
                    <div class="step-number">3</div>
                    <div class="step-title">Pago</div>
                </div>
                <div class="checkout-step">
                    <div class="step-number">4</div>
                    <div class="step-title">Confirmación</div>
                </div>
            </div>

            <!-- Espacio reservado para QR de Yape/PagoEfectivo -->
            <div id="payment-qr-container" style="display: none; text-align: center; padding: 30px; background: rgba(201, 166, 124, 0.1); border-radius: 12px; margin-bottom: 30px;">
                <h4 style="color: #c9a67c; margin-bottom: 20px;">
                    <i class="fa fa-qrcode"></i> Escanea el código QR para pagar
                </h4>
                <div id="qr-code-display" style="display: inline-block; padding: 20px; background: white; border-radius: 12px;">
                    <!-- Aquí se mostrará el QR de Yape o PagoEfectivo -->
                </div>
            </div>

            <div class="row" style="align-items: flex-start;">
                <div class="col-lg-8 col-md-8 col-12 ps-lg-4">
                    <form id="checkoutForm" action="app/actions/process_checkout.php" method="POST">
                        
                        <?php if($tiene_direccion_predeterminada): ?>
                        <!-- VISTA SIMPLIFICADA CON DIRECCIÓN PREDETERMINADA -->
                        <div class="form-section checkout-address-section">
                            
                            <div class="address-preview-card" onclick="openAddressBottomSheet()">
                                <div class="address-icon-wrapper">
                                    <svg xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0 0 1024 1024" width="1em" height="1em" fill="currentColor" class="address-icon">
                                        <path d="M512 53.3c229.9 0 416 190.6 416 425.4 0 10.3-0.4 20.6-1.2 31.1l-1.7 17.8-1.1 7.4c-1.1 8.7-2.5 17.3-4.2 26.1l-3 14.4-3.6 14.7c-18.7 70.4-54.7 134.3-103.7 185.4l-10.7 10.7c-71.8 76.5-167 138.5-273.6 186.6l-13.2 5.9-13.1-5.9c-106.5-48-201.9-110-272.7-185.5-55.4-53.6-95.4-121.7-115.3-196.7l-4.3-17.4c-1.8-8.3-3.4-16.7-4.8-25.4l-3.2-22.2c-1.7-16.1-2.6-31.6-2.6-47 0-234.8 186.1-425.4 416-425.4z m0 64c-194.2 0-352 161.6-352 361.4 0 8.6 0.3 17.4 1 26.3l1.4 14.9 1.1 6.9c1 7.5 2.1 14.8 3.4 21.7l2.6 12.4c15.7 69.3 51.1 131.9 102.3 181.6 60.9 64.9 142.1 119.5 233.6 162.9l6.6 3 6.6-3c85-40.4 161.1-90.3 221.1-150l13.5-14c46.8-45.4 80.8-102.9 97.7-165.7l4.1-17.1 2-9.7 1.9-10.9 2.8-19.3c1.5-13.8 2.3-27 2.3-40 0-199.8-157.8-361.4-352-361.4z m0 238.7c67.6 0 122.4 54.7 122.4 122.3 0 67.5-54.8 122.3-122.4 122.3-67.6 0-122.4-54.8-122.4-122.3 0-67.6 54.8-122.3 122.4-122.3z m0 64c-32.2 0-58.4 26.1-58.4 58.3 0 32.2 26.1 58.3 58.4 58.3 32.2 0 58.4-26.1 58.4-58.3 0-32.2-26.1-58.3-58.4-58.3z"></path>
                                    </svg>
                                </div>
                                
                                <div class="address-content-wrapper">
                                    <div class="address-line-1">
                                        <span class="address-name" id="preview_nombre"><?php echo htmlspecialchars($direccion_predeterminada['nombre_cliente_direccion']); ?></span>
                                        <span class="address-phone" id="preview_telefono"><?php echo htmlspecialchars($direccion_predeterminada['telefono_direccion']); ?></span>
                                    </div>
                                    
                                    <div class="address-line-2">
                                        <span class="address-street" id="preview_direccion"><?php echo htmlspecialchars($direccion_predeterminada['direccion_completa_direccion']); ?></span>
                                    </div>
                                    
                                    <div class="address-line-3">
                                        <span id="preview_distrito"><?php echo htmlspecialchars($direccion_predeterminada['distrito'] ?? ''); ?></span>
                                        <?php if(!empty($direccion_predeterminada['codigo_postal'])): ?>
                                        <span>, </span>
                                        <span id="preview_postal"><?php echo htmlspecialchars($direccion_predeterminada['codigo_postal']); ?></span>
                                        <?php endif; ?>
                                        <span>, </span>
                                        <span id="preview_provincia"><?php echo htmlspecialchars($direccion_predeterminada['provincia'] ?? 'Lima'); ?></span>
                                        <span>, </span>
                                        <span id="preview_departamento"><?php echo htmlspecialchars($direccion_predeterminada['departamento'] ?? 'Lima'); ?></span>
                                        <span>, Perú</span>
                                    </div>
                                </div>
                                
                                <div class="address-arrow-wrapper">
                                    <svg xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0 0 1024 1024" width="1em" height="1em" fill="currentColor" class="arrow-icon">
                                        <path d="M320 215.8c-18.2-18.9-17.6-49 1.3-67.2 17-16.4 43.1-17.5 61.5-3.8l5.8 5.1 315.4 328.7c15.7 16.3 17.4 41.1 5.3 59.3l-5.2 6.5-315.5 329.6c-18.2 19-48.3 19.6-67.2 1.5-17.1-16.3-19.3-42.4-6.4-61.2l4.9-6 284-296.6-283.9-295.9z"></path>
                                    </svg>
                                </div>
                            </div>
                            
                            <!-- Campos ocultos para compatibilidad -->
                            <div style="display: none;">
                                <span id="preview_dni"><?php echo htmlspecialchars($direccion_predeterminada['dni_ruc_direccion'] ?? ''); ?></span>
                                <span id="preview_tipo"><?php echo strlen($direccion_predeterminada['dni_ruc_direccion'] ?? '') === 11 ? 'Factura' : 'Boleta'; ?></span>
                                <span id="preview_referencia"><?php echo htmlspecialchars($direccion_predeterminada['referencia_direccion'] ?? ''); ?></span>
                            </div>
                            
                            <!-- Campos Ocultos con TODOS los datos de la dirección predeterminada -->
                        <div style="display: none;">
                            <?php
                            // Obtener datos completos de la dirección predeterminada
                            $nombre_completo = $direccion_predeterminada['nombre_cliente_direccion'];
                            $email_direccion = $direccion_predeterminada['email_direccion'] ?? $usuario_logueado['email_usuario'];
                            $telefono_direccion = $direccion_predeterminada['telefono_direccion'];
                            $dni_ruc = $direccion_predeterminada['dni_ruc_direccion'] ?? '';
                            $razon_social = $direccion_predeterminada['razon_social_direccion'] ?? '';
                            
                            // Extraer solo la dirección (primera parte antes de la primera coma)
                            $partes_direccion = explode(',', $direccion_predeterminada['direccion_completa_direccion']);
                            $solo_direccion = trim($partes_direccion[0]);
                            // Quitar referencia si existe (formato: "Dirección (Ref: xxx)")
                            if(preg_match('/^(.+?)\s*\(Ref:/', $solo_direccion, $matches)) {
                                $solo_direccion = trim($matches[1]);
                            }
                            
                            // Determinar tipo de comprobante basado en longitud del DNI/RUC
                            $tipo_comprobante = (strlen($dni_ruc) === 11) ? 'factura' : 'boleta';
                            ?>
                            <input type="hidden" name="nombre" value="<?php echo htmlspecialchars($nombre_completo); ?>">
                            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email_direccion); ?>">
                            <input type="hidden" name="telefono" value="<?php echo htmlspecialchars($telefono_direccion); ?>">
                            <input type="hidden" name="dni" value="<?php echo htmlspecialchars($dni_ruc); ?>">
                            <input type="hidden" name="razon_social" value="<?php echo htmlspecialchars($razon_social); ?>">
                            <input type="hidden" name="tipo_comprobante" value="<?php echo htmlspecialchars($tipo_comprobante); ?>">
                            <input type="hidden" name="direccion" value="<?php echo htmlspecialchars($solo_direccion); ?>">
                            <input type="hidden" name="referencia" value="<?php echo htmlspecialchars($direccion_predeterminada['referencia_direccion'] ?? ''); ?>">
                            <input type="hidden" name="departamento" value="<?php echo htmlspecialchars($direccion_predeterminada['departamento_direccion']); ?>">
                            <input type="hidden" name="provincia" value="<?php echo htmlspecialchars($direccion_predeterminada['provincia_direccion']); ?>">
                            <input type="hidden" name="distrito" value="<?php echo htmlspecialchars($direccion_predeterminada['distrito_direccion']); ?>">
                        </div>
                        </div>

                        <!-- Productos Seleccionados (Estilo Temu) -->
                        <div class="temu-products-section">
                            <div class="temu-section-header" onclick="openProductsBottomSheet()">
                                <div class="temu-header-left">
                                    <div class="temu-header-title">
                                        <span class="temu-title-text">Envío gratis</span>
                                    </div>
                                    <div class="temu-header-count">
                                        <span>(<?php echo count($cart_items); ?>)</span>
                                    </div>
                                </div>
                                <div class="temu-header-right">
                                    <svg xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0 0 1024 1024" width="1em" height="1em" fill="currentColor" class="temu-arrow">
                                        <path d="M320 215.8c-18.2-18.9-17.6-49 1.3-67.2 17-16.4 43.1-17.5 61.5-3.8l5.8 5.1 315.4 328.7c15.7 16.3 17.4 41.1 5.3 59.3l-5.2 6.5-315.5 329.6c-18.2 19-48.3 19.6-67.2 1.5-17.1-16.3-19.3-42.4-6.4-61.2l4.9-6 284-296.6-283.9-295.9z"></path>
                                    </svg>
                                </div>
                            </div>
                            
                            <div class="temu-products-scroll-container">
                                <div class="temu-products-scroll-wrapper">
                                    <?php foreach($cart_items as $item): 
                                        $precio_original = $item['precio_producto'];
                                        $precio_con_descuento = $precio_original;
                                        $tiene_descuento = $item['descuento_porcentaje_producto'] > 0;
                                        
                                        if($tiene_descuento) {
                                            $precio_con_descuento = $precio_original - ($precio_original * $item['descuento_porcentaje_producto'] / 100);
                                        }
                                    ?>
                                    <div class="temu-product-item">
                                        <div class="temu-product-image-wrapper">
                                            <img src="<?php echo htmlspecialchars($item['url_imagen_producto']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['nombre_producto']); ?>"
                                                 class="temu-product-image">
                                            
                                            <?php if($tiene_descuento): ?>
                                            <div class="temu-discount-badge" style="background-color:#000000a1; color:#ffffff;">
                                                <span>-<?php echo $item['descuento_porcentaje_producto']; ?>%</span>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="temu-product-price-container">
                                            <div class="temu-product-price">
                                                <span class="temu-price-symbol">S/ </span>
                                                <span class="temu-price-value"><?php echo number_format($precio_con_descuento, 2); ?></span>
                                                <?php if($item['cantidad_carrito'] > 1): ?>
                                                <span class="temu-price-quantity">×<?php echo $item['cantidad_carrito']; ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if($tiene_descuento): ?>
                                            <div class="temu-product-original-price">
                                                <span><?php echo number_format($precio_original, 2); ?></span>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            
                        </div>

                        <!-- Métodos de Pago - Estilo Temu -->
                        <div class="payment-container-temu">
                            <div class="payment-title-wrap">
                                <h2 class="payment-title">
                                    <div class="payment-security-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0 0 1024 1024" width="1em" height="1em" fill="currentColor">
                                            <path d="M462.4 85.2c27.7-11.1 58.7-11.3 86.5-0.4l324.9 125.6c29 11.2 47.8 39.5 46.9 70.6l-6.5 214.4c-4.2 137.7-72.5 265.5-184.7 345.4l-85.3 60.8c-81.8 58.2-191.5 58.2-273.2 0l-80.5-57.3c-114.3-81.4-183-212.4-184.9-352.7l-2.8-212c-0.4-30.3 17.9-57.8 46.1-69.1z m64.3 56.9c-13.4-5.2-28.2-5.1-41.5 0.2l-313.5 125.3c-4.6 1.8-7.5 6.3-7.5 11.1l2.9 212.1c1.6 120.8 60.7 233.5 159.1 303.5l80.4 57.2c60.4 43 141.5 43 201.9 0l85.4-60.8c96.5-68.8 155.3-178.7 158.9-297.1l6.5-214.5c0.1-5-2.9-9.6-7.6-11.4z m169.1 261.2l3.8 3.3c12 12 12 31.4 0 43.4l-188.3 188.3c-12 12-31.4 12-43.4 0l-115.9-115.9c-12-12-12-31.4 0-43.4 12-12 31.4-12 43.5 0l94.1 94.1 166.6-166.5c10.8-10.8 27.6-11.9 39.6-3.3z"></path>
                                        </svg>
                                    </div>
                                    Métodos de pago
                                </h2>
                            </div>
                            
                            <!-- Campo oculto -->
                            <input type="hidden" id="metodo_pago" name="metodo_pago" value="" required>
                            
                            <div class="payment-methods-list">
                                
                                <!-- Tarjeta -->
                                <div class="pay-item-container" data-payment-method="tarjeta">
                                    <div class="pay-detail">
                                        <div class="pay-info">
                                            <div class="pay-check-btn"></div>
                                            <div class="pay-icon">
                                                <img src="https://aimg.kwcdn.com/upload_aimg/temu/ebeb26a5-1ac2-4101-862e-efdbc11544f3.png.slim.png?imageView2/2/w/100/q/60/format/webp" alt="Tarjeta">
                                            </div>
                                            <div class="pay-info-right">
                                                <div class="pay-content">
                                                    <span class="pay-name">Tarjeta</span>
                                                </div>
                                                <div class="pay-subcontent">Paga ahora o paga mensualmente</div>
                                            </div>
                                        </div>
                                        <div class="pay-info-bottom">
                                            <div class="card-brands-list">
                                                <img src="https://aimg.kwcdn.com/upload_aimg/temu/da7f463a-916f-4d91-bcbb-047317a1c35e.png.slim.png?imageView2/2/w/100/q/60/format/webp" alt="Visa">
                                                <img src="https://aimg.kwcdn.com/upload_aimg/temu/b79a2dc3-b089-4cf8-a907-015a25ca12f2.png.slim.png?imageView2/2/w/100/q/60/format/webp" alt="Mastercard">
                                                <img src="https://aimg.kwcdn.com/upload_aimg/temu/fb599a1d-6d42-49f2-ba7a-64b16d01b226.png.slim.png?imageView2/2/w/100/q/60/format/webp" alt="Amex">
                                                <img src="https://aimg.kwcdn.com/upload_aimg/temu/936bf9dc-9bb2-4935-9c5a-a70b800d4cf1.png.slim.png?imageView2/2/w/100/q/60/format/webp" alt="Discover">
                                            </div>
                                            <div class="payment-security-text">
                                                <svg width="12" height="12" viewBox="0 0 12 12" fill="#0A8800"><path d="M6 0l5 2v4c0 3-2 5-5 6-3-1-5-3-5-6V2z"/></svg>
                                                <span>Temu protege la información de tu tarjeta</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- PagoEfectivo -->
                                <div class="pay-item-container selected" data-payment-method="pagoefectivo">
                                    <div class="pay-detail">
                                        <div class="pay-info">
                                            <div class="pay-check-btn checked"></div>
                                            <div class="pay-icon">
                                                <img src="https://aimg.kwcdn.com/upload_aimg/payment/a69c0d07-aa4c-4a07-8495-2bcf924988b7.png?imageView2/2/w/100/q/60/format/webp" alt="PagoEfectivo">
                                            </div>
                                            <div class="pay-info-right">
                                                <div class="pay-content">
                                                    <span class="pay-name">PagoEfectivo</span>
                                                </div>
                                                <div class="pay-subcontent">Por favor pague dentro de 2 días</div>
                                            </div>
                                        </div>
                                        <div class="pay-info-bottom selected-content">
                                            Paga desde tu banca móvil, billetera QR o en efectivo en agentes antes de que expire el código.
                                        </div>
                                    </div>
                                </div>

                                <!-- Yape -->
                                <div class="pay-item-container" data-payment-method="yape">
                                    <div class="pay-detail">
                                        <div class="pay-info">
                                            <div class="pay-check-btn"></div>
                                            <div class="pay-icon">
                                                <img src="https://aimg.kwcdn.com/upload_aimg/payment/5b2f2fe5-0120-403f-8e20-c323ad7fd328.png.slim.png?imageView2/2/w/100/q/60/format/webp" alt="Yape">
                                            </div>
                                            <div class="pay-info-right">
                                                <div class="pay-content">
                                                    <span class="pay-name">Yape</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                            
                            <!-- Mensaje de error -->
                            <div id="payment-method-error" class="payment-error-message">
                                <p><i class="fa fa-exclamation-triangle"></i> Por favor selecciona un método de pago</p>
                            </div>
                        </div>
                        
                        <!-- Resumen de Totales -->
                        <div class="amount-container-temu">
                            
                            <!-- Descuento de artículos -->
                            <?php if ($descuento_total > 0): ?>
                            <div class="amount-row">
                                <div class="amount-desc">
                                    <span>Descuento aplicado:</span>
                                </div>
                                <div class="amount-value">
                                    <span class="price-discount">-S/ <?php echo number_format($descuento_total, 2); ?></span>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Subtotal -->
                            <div class="amount-row">
                                <div class="amount-desc">
                                    <span>Subtotal:</span>
                                </div>
                                <div class="amount-value">
                                    <span class="price-regular">S/ <?php echo number_format($subtotal, 2); ?></span>
                                </div>
                            </div>
                            
                            <!-- Total del pedido -->
                            <div class="amount-row amount-total">
                                <div class="amount-desc">
                                    <span class="total-label">Total a pagar</span>
                                </div>
                                <div class="amount-value">
                                    <span class="total-price">
                                        <span class="currency">S/</span>
                                        <span class="amount"><?php echo number_format(floor($total), 0); ?></span>
                                        <span class="decimals">.<?php echo sprintf('%02d', ($total - floor($total)) * 100); ?></span>
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Términos y condiciones -->
                            <div class="amount-row-terms">
                                <p>
                                    Al finalizar la compra, aceptas nuestros 
                                    <a href="#" class="terms-link">Términos de uso</a> 
                                    y 
                                    <a href="#" class="terms-link">Política de privacidad</a>
                                </p>
                            </div>
                        </div>
                        
                        <?php else: ?>
                        <!-- VISTA COMPLETA SIN DIRECCIÓN PREDETERMINADA -->
                        <!-- Información del Cliente -->
                        <div class="form-section">
                            <h5><i class="fa fa-user"></i> Información del Cliente</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nombre">Nombre Completo *</label>
                                        <input type="text" class="form-control" id="nombre" name="nombre" 
                                               value="<?php 
                                               // Usar nombre real (nombre + apellido), no el username
                                               $nombre_real = trim(($usuario_logueado['nombre_usuario'] ?? '') . ' ' . ($usuario_logueado['apellido_usuario'] ?? ''));
                                               echo htmlspecialchars($nombre_real ?: $usuario_logueado['username']); 
                                               ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email">Email *</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo htmlspecialchars($usuario_logueado['email_usuario'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="telefono">Teléfono *</label>
                                        <input type="tel" class="form-control" id="telefono" name="telefono" 
                                               value="<?php echo htmlspecialchars($usuario_logueado['telefono_usuario'] ?? ''); ?>" 
                                               placeholder="+51 999 999 999" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="dni_ruc">DNI o RUC * <small class="text-muted">(8 o 11 dígitos)</small></label>
                                        <input type="text" class="form-control" id="dni_ruc" name="dni" 
                                               placeholder="DNI: 12345678 o RUC: 20123456789" required maxlength="11">
                                        <small class="form-text text-info" id="comprobante-hint" style="display: none; margin-top: 5px;"></small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Dirección de Envío -->
                        <div class="form-section">
                            <h5><i class="fa fa-map-marker"></i> Dirección de Envío</h5>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="direccion">Dirección Completa * <small class="text-muted">(Calle, Número, Urbanización, etc.)</small></label>
                                        <input type="text" class="form-control" id="direccion" name="direccion" 
                                               placeholder="Ej: Av. José Pardo 610, Miraflores" required>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="referencia">Referencia <small class="text-muted">(Opcional)</small></label>
                                        <input type="text" class="form-control" id="referencia" name="referencia" 
                                               placeholder="Ej: Frente al parque Kennedy, edificio azul">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="departamento">Departamento *</label>
                                        <select class="form-control" id="departamento" name="departamento" required>
                                            <option value="">Seleccionar...</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="provincia">Provincia *</label>
                                        <select class="form-control" id="provincia" name="provincia" required disabled>
                                            <option value="">Selecciona departamento primero</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="distrito">Distrito *</label>
                                        <select class="form-control" id="distrito" name="distrito" required disabled>
                                            <option value="">Selecciona provincia primero</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Productos Seleccionados -->
                        <div class="form-section">
                            <h5><i class="fa fa-shopping-bag"></i> Productos en tu pedido (<?php echo count($cart_items); ?>)</h5>
                            <div class="checkout-products-grid">
                                <?php foreach($cart_items as $item): 
                                    $precio_original = $item['precio_producto'];
                                    $precio_con_descuento = $precio_original;
                                    
                                    if($item['descuento_porcentaje_producto'] > 0) {
                                        $precio_con_descuento = $precio_original - ($precio_original * $item['descuento_porcentaje_producto'] / 100);
                                    }
                                    
                                    $subtotal_item = $precio_con_descuento * $item['cantidad_carrito'];
                                ?>
                                <div class="checkout-product-card">
                                    <div class="checkout-product-image">
                                        <img src="<?php echo htmlspecialchars($item['url_imagen_producto']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['nombre_producto']); ?>">
                                        <?php if($item['descuento_porcentaje_producto'] > 0): ?>
                                        <div class="checkout-product-discount">
                                            -<?php echo $item['descuento_porcentaje_producto']; ?>%
                                        </div>
                                        <?php endif; ?>
                                        <div class="checkout-product-quantity">
                                            × <?php echo $item['cantidad_carrito']; ?>
                                        </div>
                                    </div>
                                    <div class="checkout-product-info">
                                        <h6 class="checkout-product-name"><?php echo htmlspecialchars($item['nombre_producto']); ?></h6>
                                        <div class="checkout-product-prices">
                                            <?php if($item['descuento_porcentaje_producto'] > 0): ?>
                                            <span class="checkout-product-price-old">S/ <?php echo number_format($precio_original, 2); ?></span>
                                            <?php endif; ?>
                                            <span class="checkout-product-price">S/ <?php echo number_format($precio_con_descuento, 2); ?></span>
                                        </div>
                                        <div class="checkout-product-subtotal">
                                            Subtotal: <strong>S/ <?php echo number_format($subtotal_item, 2); ?></strong>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Campo oculto para tipo de comprobante (se auto-completa) -->
                        <input type="hidden" id="tipo_comprobante" name="tipo_comprobante" value="">
                        
                        <!-- Campos adicionales para Factura (aparece automáticamente si RUC detectado) -->
                        <div id="facturaFields" style="display: none;">
                            <div class="form-section">
                                <h5><i class="fa fa-building-o"></i> Datos para Factura Electrónica</h5>
                                <div class="alert alert-warning" style="background: #fff3cd; border: 1px solid #ffc107; color: #856404; margin-bottom: 15px;">
                                    <i class="fa fa-exclamation-triangle"></i> 
                                    Se ha detectado un RUC. Completa los siguientes datos para la factura:
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="razon_social">Razón Social * <small class="text-muted">(Nombre o razón social de la empresa)</small></label>
                                            <input type="text" class="form-control" id="razon_social" name="razon_social"
                                                   placeholder="Ej: EMPRESA SAC, COMERCIAL EIRL, etc.">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Métodos de Pago (Formulario Completo) - COMENTADO -->
                        <?php /* ?>
                        <div class="form-section">
                            <h5><i class="fa fa-credit-card"></i> Método de Pago</h5>
                            <div style="text-align: center; margin-bottom: 20px;">
                                <p style="color: #888; font-size: 12px; margin: 0;">
                                    <i class="fa fa-shield" style="color: #4caf50;"></i> Protegido con altos estándares de seguridad
                                </p>
                            </div>
                            
                            <input type="hidden" id="metodo_pago_full" name="metodo_pago" value="" required>
                            
                            <div class="payment-methods-grid">
                                <div class="payment-method-card" data-payment-method="tarjeta">
                                    <div class="payment-method-icon">
                                        <i class="fa fa-credit-card"></i>
                                    </div>
                                    <div class="payment-method-info">
                                        <div class="payment-method-name">Tarjeta</div>
                                        <div class="payment-method-desc">Paga ahora o paga mensualmente</div>
                                    </div>
                                    <div class="payment-method-check">
                                        <i class="fa fa-check-circle"></i>
                                    </div>
                                </div>

                                <div class="payment-method-card" data-payment-method="pagoefectivo">
                                    <div class="payment-method-icon" style="background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);">
                                        <i class="fa fa-money"></i>
                                    </div>
                                    <div class="payment-method-info">
                                        <div class="payment-method-name">PagoEfectivo</div>
                                        <div class="payment-method-desc">Por favor pague dentro de 2 días</div>
                                        <div class="payment-method-note">Paga desde tu banca móvil, billetera QR o en efectivo en agentes antes de que expire el código.</div>
                                    </div>
                                    <div class="payment-method-check">
                                        <i class="fa fa-check-circle"></i>
                                    </div>
                                </div>

                                <div class="payment-method-card payment-method-highlight" data-payment-method="yape">
                                    <div class="payment-method-badge">
                                        <i class="fa fa-tag"></i> Extra S/ 4 de dto.
                                    </div>
                                    <div class="payment-method-icon" style="background: linear-gradient(135deg, #6a1b9a 0%, #8e24aa 100%);">
                                        <i class="fa fa-mobile"></i>
                                    </div>
                                    <div class="payment-method-info">
                                        <div class="payment-method-name">Yape</div>
                                        <div class="payment-method-promo">Obtén extra S/ 4 de dto. en pedidos superiores a S/ 90.</div>
                                    </div>
                                    <div class="payment-method-check">
                                        <i class="fa fa-check-circle"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="payment-method-error-full" style="display: none; margin-top: 12px; padding: 10px; background: #fff3cd; border-left: 3px solid #ffc107; border-radius: 6px;">
                                <p style="color: #856404; font-size: 13px; margin: 0;">
                                    <i class="fa fa-exclamation-triangle"></i> Por favor selecciona un método de pago
                                </p>
                            </div>
                        </div>
                        <?php */ ?>

                        <!-- Notas Adicionales -->
                        <div class="form-section">
                            <h5><i class="fa fa-comment"></i> Notas del Pedido (Opcional)</h5>
                            <div class="form-group">
                                <textarea class="form-control" name="notas" rows="4" 
                                          placeholder="¿Alguna instrucción especial para tu pedido?"></textarea>
                            </div>
                        </div>
                        
                        <?php endif; ?> <!-- Fin de la vista condicional (completa/simplificada) -->

                    </form>
                </div>

                <!-- Order Summary Sidebar -->
                <div class="col-lg-4 col-md-4 col-12">
                    <div class="order-summary checkout-summary" style="border-radius: 12px; padding: 20px; position: sticky !important; top: 20px !important; z-index: 10; margin-top: 0 !important;">
                        <h5 class="summary-title" style="margin-bottom: 18px; font-size: 17px; font-weight: 700; padding-bottom: 12px; margin-top: 0;">
                            <i class="fa fa-file-text-o"></i> Resumen del Pedido
                        </h5>
                        
                        <?php 
                        // Calcular totales
                        $total_articulos_precio = 0;
                        $total_descuentos = 0;
                        foreach($cart_items as $item): 
                            $precio_original = $item['precio_producto'];
                            $precio_con_descuento = $precio_original;
                            $descuento_item = 0;
                            
                            if($item['descuento_porcentaje_producto'] > 0) {
                                $precio_con_descuento = $precio_original - ($precio_original * $item['descuento_porcentaje_producto'] / 100);
                                $descuento_item = ($precio_original - $precio_con_descuento) * $item['cantidad_carrito'];
                                $total_descuentos += $descuento_item;
                            }
                            
                            $total_articulos_precio += $precio_original * $item['cantidad_carrito'];
                        endforeach;
                        ?>

                        <!-- Totals Breakdown -->
                        <div class="order-totals" style="margin-bottom: 16px;">
                            <!-- Total de artículos -->
                            <div class="order-total-row summary-row" style="display: flex; justify-content: space-between; margin-bottom: 12px; padding-bottom: 12px;">
                                <span class="summary-label">Total de artículos:</span>
                                <span class="summary-value">S/ <?php echo number_format($total_articulos_precio, 2); ?></span>
                            </div>
                            
                            <!-- Descuento de artículos -->
                            <?php if($total_descuentos > 0): ?>
                            <div class="order-total-row summary-row" style="display: flex; justify-content: space-between; margin-bottom: 12px; padding-bottom: 12px;">
                                <span style="color: #4caf50; font-size: 14px; font-weight: 600;">Descuento de artículo(s):</span>
                                <span style="color: #4caf50; font-size: 14px; font-weight: 600;">-S/ <?php echo number_format($total_descuentos, 2); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Envío -->
                            <div class="order-total-row summary-row" style="display: flex; justify-content: space-between; margin-bottom: 16px; padding-bottom: 16px;">
                                <span class="summary-label">Envío:</span>
                                <span style="color: <?php echo $costo_envio == 0 ? '#4caf50' : ''; ?>; font-weight: 700; font-size: 14px;" class="<?php echo $costo_envio == 0 ? '' : 'summary-value'; ?>">
                                    <?php echo $costo_envio == 0 ? 'GRATIS' : 'S/ ' . number_format($costo_envio, 2); ?>
                                </span>
                            </div>
                            
                            <!-- Total -->
                            <div class="order-total-row total summary-total" style="display: flex; justify-content: space-between; padding: 16px; border-radius: 8px; margin-bottom: 20px;">
                                <span style="font-weight: 700; font-size: 18px;">Total</span>
                                <span style="font-weight: 700; font-size: 22px;">S/ <?php echo number_format($total, 2); ?></span>
                            </div>
                            <!-- Botón de pago justo después del total -->
                            <div style="margin: 0 0 20px 0;">
                                <button type="submit" form="checkoutForm" class="btn-place-order" id="btnPlaceOrder" 
                                        style="width: 100%; background: linear-gradient(135deg, #c9a67c 0%, #a08661 100%); color: #1a1a1a; border: none; padding: 16px; border-radius: 8px; font-weight: 700; font-size: 16px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(201, 166, 124, 0.3);">
                                    <i class="fa fa-arrow-right"></i> Continuar al Pago
                                </button>
                            </div>
                        </div>

                        <!-- Notas informativas -->
                        <div style="background: rgba(201, 166, 124, 0.1); border-left: 3px solid #c9a67c; padding: 12px; border-radius: 6px; margin-bottom: 12px;">
                            <p style="color: #d0d0d0; font-size: 12px; margin: 0; line-height: 1.6;">
                                <i class="fa fa-info-circle" style="color: #c9a67c;"></i> 
                                Verifica tu pedido antes de confirmar. El monto final incluye envío e impuestos.
                            </p>
                        </div>
                        
                        <div style="background: rgba(255, 193, 7, 0.1); border-left: 3px solid #ffc107; padding: 12px; border-radius: 6px; margin-bottom: 12px;">
                            <p style="color: #ffc107; font-size: 12px; margin: 0; line-height: 1.6;">
                                <i class="fa fa-exclamation-triangle"></i> 
                                Los productos están sujetos a disponibilidad de stock. Confirma tu compra pronto.
                            </p>
                        </div>

                        <!-- Security Notice -->
                        <div style="background: rgba(201, 166, 124, 0.05); border: 1px solid #3a3a3a; padding: 14px; border-radius: 8px; margin-bottom: 20px;">
                            <h6 style="color: #c9a67c; font-size: 13px; font-weight: 700; margin-bottom: 8px;">
                                <i class="fa fa-lock"></i> Compra 100% Segura
                            </h6>
                            <p style="color: #a0a0a0; font-size: 11px; line-height: 1.6; margin: 0;">
                                Protegemos tu información personal y de pago con encriptación SSL de última generación. 
                                Tus datos están seguros y nunca serán compartidos con terceros. Compra con total confianza.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Checkout Section End -->

    <!-- Footer Sticky Móvil (Solo visible en móvil) -->
    <div class="mobile-checkout-footer">
        <div class="mobile-checkout-footer__content">
            <div class="mobile-checkout-footer__total">
                <div class="mobile-checkout-footer__label">Total a pagar</div>
                <div class="mobile-checkout-footer__amount" id="mobile-footer-total">
                    $<?php echo number_format($total, 2); ?>
                </div>
                <?php if($costo_envio == 0): ?>
                <div class="mobile-checkout-footer__shipping">
                    <i class="fa fa-truck"></i> Envío GRATIS
                </div>
                <?php else: ?>
                <div class="mobile-checkout-footer__shipping" style="color: #666;">
                    + $<?php echo number_format($costo_envio, 2); ?> envío
                </div>
                <?php endif; ?>
            </div>
            <div class="mobile-checkout-footer__action">
                <button type="submit" form="checkoutForm" class="mobile-place-order-btn" id="mobileBtnPlaceOrder">
                    <i class="fa fa-arrow-right"></i>
                    <span>Continuar al Pago</span>
                </button>
            </div>
        </div>
    </div>
    <!-- Footer Sticky Móvil End -->

    <!-- Modal para Guardar Dirección -->
    <div id="saveAddressModal" class="modal fade" tabindex="-1" role="dialog" style="z-index: 9999;">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
                <div class="modal-header" style="border-bottom: 1px solid #eee; padding: 20px 24px;">
                    <h5 class="modal-title" style="font-weight: 700; color: #2c3e50; font-size: 18px;">
                        <i class="fa fa-map-marker" style="color: #667eea; margin-right: 8px;"></i>
                        Guardar Dirección
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="opacity: 0.5;">
                        <span aria-hidden="true" style="font-size: 28px;">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="padding: 24px;">
                    <p style="margin: 0 0 20px 0; color: #555; line-height: 1.6;">
                        ¿Deseas guardar esta dirección para futuras compras? Esto te permitirá completar tus pedidos más rápidamente.
                    </p>
                    
                    <?php if(!$tiene_direccion_predeterminada): ?>
                    <!-- Opción para marcar como predeterminada -->
                    <div style="background: #e8f5e9; border-radius: 8px; padding: 16px; margin-bottom: 20px; border-left: 4px solid #4caf50;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <input type="checkbox" id="chkSetAsDefault" style="width: 18px; height: 18px; cursor: pointer;">
                            <label for="chkSetAsDefault" style="margin: 0; cursor: pointer; font-weight: 600; color: #2e7d32; font-size: 14px;">
                                <i class="fa fa-star" style="color: #ffc107;"></i> Establecer como dirección predeterminada
                            </label>
                        </div>
                        <p style="margin: 8px 0 0 30px; font-size: 12px; color: #558b2f; line-height: 1.4;">
                            Esta dirección se usará automáticamente en tus próximas compras
                        </p>
                    </div>
                    <?php endif; ?>
                    
                    <div style="background: #f8f9fa; border-radius: 8px; padding: 16px;">
                        <p style="margin: 0; font-size: 13px; color: #666; line-height: 1.5;">
                            <i class="fa fa-info-circle" style="color: #667eea; margin-right: 6px;"></i>
                            La dirección se guardará de forma segura y podrás editarla o eliminarla en cualquier momento desde tu perfil.
                        </p>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #eee; padding: 16px 24px; display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" class="btn btn-secondary" id="btnNoSaveAddress" style="padding: 10px 24px; border-radius: 8px; font-weight: 600; background: #6c757d; border: none;">
                        <i class="fa fa-times"></i> No, gracias
                    </button>
                    <button type="button" class="btn btn-primary" id="btnYesSaveAddress" style="padding: 10px 24px; border-radius: 8px; font-weight: 600; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                        <i class="fa fa-check"></i> Sí, guardar
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal para Guardar Dirección End -->

    <!-- Estilos para animaciones del modal -->
    <style>
        /* Animación de entrada del modal */
        @keyframes modalSlideDown {
            from {
                opacity: 0;
                transform: translateY(-100px) scale(0.8);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        @keyframes modalBackdropFade {
            from {
                opacity: 0;
            }
            to {
                opacity: 0.5;
            }
        }
        
        @keyframes floatIcon {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
        }
        
        @keyframes shimmer {
            0% {
                background-position: -1000px 0;
            }
            100% {
                background-position: 1000px 0;
            }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Modal customizado - MODO OSCURO */
        #useDefaultAddressModal.show .modal-dialog {
            animation: modalSlideDown 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }
        
        #useDefaultAddressModal .modal-backdrop.show {
            animation: modalBackdropFade 0.3s ease-in-out;
        }
        
        #useDefaultAddressModal .modal-content {
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
            background: #1a1a1a !important;
        }
        
        #useDefaultAddressModal .modal-header .fa-star {
            animation: floatIcon 2s ease-in-out infinite;
            display: inline-block;
        }
        
        #useDefaultAddressModal .info-card {
            animation: fadeInUp 0.6s ease-out 0.2s both;
        }
        
        #useDefaultAddressModal .info-section {
            transition: all 0.3s ease;
        }
        
        #useDefaultAddressModal .info-section:hover {
            transform: translateX(5px);
        }
        
        #useDefaultAddressModal .btn {
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            position: relative;
            overflow: hidden;
        }
        
        #useDefaultAddressModal .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        #useDefaultAddressModal .btn:hover::before {
            width: 300px;
            height: 300px;
        }
        
        #useDefaultAddressModal .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(201, 166, 124, 0.4);
        }
        
        #useDefaultAddressModal .btn:active {
            transform: translateY(-1px);
        }
        
        #useDefaultAddressModal .close {
            transition: all 0.3s ease;
        }
        
        #useDefaultAddressModal .close:hover {
            transform: rotate(90deg) scale(1.2);
        }
        
        /* Shimmer effect para el header - Color dorado */
        #useDefaultAddressModal .modal-header {
            background: linear-gradient(135deg, #c9a67c 0%, #a08661 100%) !important;
            position: relative;
        }
        
        #useDefaultAddressModal .modal-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: shimmer 3s infinite;
        }
        
        /* Estilos para el modal de selección de direcciones */
        .address-option {
            transition: all 0.3s ease;
        }
        
        .address-option:hover {
            border-color: #c9a67c !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(201, 166, 124, 0.3);
        }
        
        .address-option:active {
            transform: translateY(0);
        }
    </style>

    <!-- Modal para Usar Dirección Predeterminada -->
    <div id="useDefaultAddressModal" class="modal fade" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" style="z-index: 9999;">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 550px;">
            <div class="modal-content" style="border-radius: 16px; border: none; background: #1a1a1a;">
                <div class="modal-header" style="border-bottom: 2px solid rgba(201, 166, 124, 0.2); padding: 20px 28px; border-radius: 16px 16px 0 0; position: relative;">
                    <h5 class="modal-title" style="font-weight: 700; color: white; font-size: 18px; z-index: 1; position: relative;">
                        <i class="fa fa-star" style="color: #ffd700; margin-right: 8px;"></i>
                        Dirección Predeterminada Disponible
                    </h5>
                    <button type="button" class="close" onclick="$('#useDefaultAddressModal').modal('hide')" aria-label="Close" style="opacity: 1; color: white; z-index: 1; position: relative;">
                        <span aria-hidden="true" style="font-size: 28px; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="padding: 28px; background: #1a1a1a;">
                    <?php if($tiene_direccion_predeterminada): ?>
                    <!-- Mostrar información completa guardada -->
                    <div class="info-card" style="background: linear-gradient(135deg, #2a2a2a 0%, #1f1f1f 100%); border-radius: 12px; padding: 20px; margin-bottom: 0; border-left: 4px solid #c9a67c; box-shadow: 0 2px 8px rgba(0,0,0,0.3);">
                        
                        <!-- Información del Cliente -->
                        <div class="info-section" style="margin-bottom: 18px;">
                            <div style="color: #c9a67c; font-weight: 700; margin-bottom: 12px; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                                <i class="fa fa-user"></i> Información del Cliente
                            </div>
                            <div style="padding-left: 24px; color: #e0e0e0; font-size: 14px; line-height: 2;">
                                <?php if(!empty($direccion_predeterminada['nombre_cliente_direccion'])): ?>
                                <div style="display: flex; margin-bottom: 4px;">
                                    <strong style="min-width: 100px; color: #c9a67c;">Nombre:</strong> 
                                    <span style="color: #d0d0d0;"><?php echo htmlspecialchars($direccion_predeterminada['nombre_cliente_direccion']); ?></span>
                                </div>
                                <?php endif; ?>
                                <div style="display: flex; margin-bottom: 4px;">
                                    <strong style="min-width: 100px; color: #c9a67c;">Email:</strong> 
                                    <span style="color: #d0d0d0;"><?php echo htmlspecialchars($usuario_logueado['email_usuario']); ?></span>
                                </div>
                                <?php if(!empty($direccion_predeterminada['telefono_direccion'])): ?>
                                <div style="display: flex; margin-bottom: 4px;">
                                    <strong style="min-width: 100px; color: #c9a67c;">Teléfono:</strong> 
                                    <span style="color: #d0d0d0;"><?php echo htmlspecialchars($direccion_predeterminada['telefono_direccion']); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Dirección de Envío -->
                        <div class="info-section" style="margin-bottom: 14px; padding-top: 14px; border-top: 2px solid rgba(201, 166, 124, 0.2);">
                            <div style="color: #c9a67c; font-weight: 700; margin-bottom: 12px; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                                <i class="fa fa-map-marker"></i> Dirección de Envío
                            </div>
                            <div style="color: #d0d0d0; font-size: 14px; line-height: 1.8; padding-left: 24px;">
                                <?php echo htmlspecialchars($direccion_predeterminada['direccion_completa_direccion'] ?? ''); ?>
                                <?php 
                                // Mostrar ubigeo completo si existe
                                $ubigeo_partes = [];
                                if (!empty($direccion_predeterminada['distrito_direccion'])) {
                                    $ubigeo_partes[] = $direccion_predeterminada['distrito_direccion'];
                                }
                                if (!empty($direccion_predeterminada['provincia_direccion'])) {
                                    $ubigeo_partes[] = $direccion_predeterminada['provincia_direccion'];
                                }
                                if (!empty($direccion_predeterminada['departamento_direccion'])) {
                                    $ubigeo_partes[] = $direccion_predeterminada['departamento_direccion'];
                                }
                                if (!empty($ubigeo_partes)) {
                                    echo '<br><span style="color: #a0a0a0; font-size: 13px;">' . htmlspecialchars(implode(' - ', $ubigeo_partes)) . '</span>';
                                }
                                ?>
                            </div>
                            <?php if(!empty($direccion_predeterminada['referencia_direccion'])): ?>
                            <div style="color: #a0a0a0; font-size: 13px; margin-top: 10px; padding-left: 24px; font-style: italic;">
                                <i class="fa fa-info-circle"></i> Ref: <?php echo htmlspecialchars($direccion_predeterminada['referencia_direccion']); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Método de Pago Favorito -->
                        <?php if(!empty($direccion_predeterminada['metodo_pago_favorito'])): ?>
                        <div class="info-section" style="padding-top: 14px; border-top: 2px solid rgba(201, 166, 124, 0.2);">
                            <div style="color: #c9a67c; font-weight: 700; margin-bottom: 12px; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                                <i class="fa fa-credit-card"></i> Método de Pago Favorito
                            </div>
                            <div style="color: #d0d0d0; font-size: 14px; padding-left: 24px;">
                                <span style="background: #c9a67c; color: #1a1a1a; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                                    <?php 
                                    $metodos = [
                                        'tarjeta' => '💳 Tarjeta',
                                        'transferencia' => '🏦 Transferencia',
                                        'yape' => '📱 Yape/Plin',
                                        'efectivo' => '💵 Efectivo'
                                    ];
                                    echo $metodos[$direccion_predeterminada['metodo_pago_favorito']] ?? $direccion_predeterminada['metodo_pago_favorito'];
                                    ?>
                                </span>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer" style="border-top: 2px solid rgba(201, 166, 124, 0.2); padding: 20px 28px; display: flex; gap: 12px; justify-content: center; background: #1a1a1a; border-radius: 0 0 16px 16px;">
                    <button type="button" class="btn btn-secondary" id="btnUseOtherAddress" style="flex: 1; padding: 10px 24px; border-radius: 10px; font-weight: 600; background: #3a3a3a; border: 1px solid #4a4a4a; font-size: 14px; color: #e0e0e0;">
                        <i class="fa fa-pencil"></i> Usar otra dirección
                    </button>
                    <button type="button" class="btn btn-primary" id="btnUseDefaultAddress" style="flex: 1; padding: 10px 24px; border-radius: 10px; font-weight: 600; background: linear-gradient(135deg, #c9a67c 0%, #a08661 100%); border: none; font-size: 14px; color: #1a1a1a;">
                        <i class="fa fa-check"></i> Usar esta información
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal para Usar Dirección Predeterminada End -->

    <!-- Modal para Seleccionar Dirección (Múltiples opciones) -->
    <div id="selectAddressModal" class="modal fade" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" style="z-index: 9999;">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 650px;">
            <div class="modal-content address-modal-content" style="border-radius: 16px; border: none; max-height: 90vh; display: flex; flex-direction: column;">
                <div class="modal-header" style="border-bottom: 2px solid rgba(201, 166, 124, 0.2); padding: 20px 28px; border-radius: 16px 16px 0 0; background: linear-gradient(135deg, #c9a67c 0%, #a08661 100%);">
                    <h5 class="modal-title" style="font-weight: 700; color: white; font-size: 18px;">
                        <i class="fa fa-map-marker"></i> Selecciona una Dirección de Envío
                    </h5>
                    <button type="button" class="close" onclick="$('#selectAddressModal').modal('hide')" aria-label="Close" style="opacity: 1; color: white;">
                        <span aria-hidden="true" style="font-size: 28px;">&times;</span>
                    </button>
                </div>
                <div class="modal-body address-modal-body" style="padding: 24px; overflow-y: auto; flex: 1;">
                    <?php if(!empty($todas_direcciones)): ?>
                        <div style="display: grid; gap: 16px;">
                            <?php foreach($todas_direcciones as $dir): ?>
                            <div class="address-option" data-address-id="<?php echo $dir['id_direccion']; ?>" 
                                 style="border-radius: 12px; padding: 18px; cursor: pointer; border: 2px solid <?php echo $dir['es_principal'] == 1 ? '#c9a67c' : ''; ?>; transition: all 0.3s ease; position: relative;">
                                
                                <?php if($dir['es_principal'] == 1): ?>
                                <div style="position: absolute; top: 12px; right: 12px; background: #c9a67c; color: #1a1a1a; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700;">
                                    <i class="fa fa-star"></i> PREDETERMINADA
                                </div>
                                <?php endif; ?>
                                
                                <div style="margin-right: 120px;">
                                    <div class="address-option-name" style="font-weight: 700; font-size: 15px; margin-bottom: 8px;">
                                        <?php echo htmlspecialchars($dir['nombre_cliente_direccion']); ?>
                                    </div>
                                    <div class="address-option-details" style="font-size: 13px; line-height: 1.6;">
                                        <div><?php echo htmlspecialchars($dir['direccion_completa_direccion']); ?></div>
                                        <?php if(!empty($dir['telefono_direccion'])): ?>
                                        <div class="address-option-phone" style="margin-top: 6px;">
                                            <i class="fa fa-phone"></i> <?php echo htmlspecialchars($dir['telefono_direccion']); ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- Datos ocultos para JavaScript -->
                                <input type="hidden" class="addr-nombre" value="<?php echo htmlspecialchars($dir['nombre_cliente_direccion']); ?>">
                                <input type="hidden" class="addr-email" value="<?php echo htmlspecialchars($usuario_logueado['email_usuario']); ?>">
                                <input type="hidden" class="addr-telefono" value="<?php echo htmlspecialchars($dir['telefono_direccion']); ?>">
                                <input type="hidden" class="addr-direccion" value="<?php echo htmlspecialchars($dir['direccion_completa_direccion']); ?>">
                                <input type="hidden" class="addr-departamento" value="<?php echo htmlspecialchars($dir['departamento_direccion']); ?>">
                                <input type="hidden" class="addr-provincia" value="<?php echo htmlspecialchars($dir['provincia_direccion']); ?>">
                                <input type="hidden" class="addr-distrito" value="<?php echo htmlspecialchars($dir['distrito_direccion']); ?>">
                                <input type="hidden" class="addr-referencia" value="<?php echo htmlspecialchars($dir['referencia_direccion'] ?? ''); ?>">
                                <input type="hidden" class="addr-metodo-pago" value="<?php echo htmlspecialchars($dir['metodo_pago_favorito'] ?? ''); ?>">
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="address-empty-state" style="text-align: center; padding: 40px;">
                            <i class="fa fa-map-marker" style="font-size: 48px; color: #c9a67c; margin-bottom: 16px;"></i>
                            <p>No tienes direcciones guardadas</p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer address-modal-footer" style="border-top: 2px solid rgba(201, 166, 124, 0.2); padding: 20px 28px; border-radius: 0 0 16px 16px;">
                    <button type="button" class="btn btn-add-address" onclick="window.location.href='profile.php#direcciones'" 
                            style="flex: 1; padding: 12px 24px; border-radius: 10px; font-weight: 600; font-size: 14px;">
                        <i class="fa fa-plus"></i> Agregar nueva dirección
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal para Seleccionar Dirección End -->

    <!-- Bottom Sheet para Seleccionar Dirección (Mobile-First) -->
    <div id="addressBottomSheet" class="address-bottom-sheet">
        <div class="bottom-sheet-content">
            <div class="bottom-sheet-header">
                <div class="bottom-sheet-handle"></div>
                <h3 class="bottom-sheet-title">Direcciones de Envío</h3>
            </div>
            
            <div class="bottom-sheet-body">
                <?php if(!empty($todas_direcciones)): ?>
                    <div class="address-list">
                        <?php foreach($todas_direcciones as $dir): ?>
                        <div class="address-card" onclick="selectAddress(<?php echo $dir['id_direccion']; ?>)">
                            <div class="address-card-header">
                                <div class="address-icon-circle">
                                    <i class="fa fa-map-marker-alt"></i>
                                </div>
                                <div class="address-card-info">
                                    <div class="address-card-name"><?php echo htmlspecialchars($dir['nombre_cliente_direccion']); ?></div>
                                    <div class="address-card-phone"><?php echo htmlspecialchars($dir['telefono_direccion']); ?></div>
                                </div>
                                <?php if($dir['es_principal'] == 1): ?>
                                <div class="address-default-badge">
                                    <i class="fa fa-star"></i>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="address-card-details">
                                <div class="address-card-street"><?php echo htmlspecialchars($dir['direccion_completa_direccion']); ?></div>
                                <div class="address-card-location">
                                    <?php echo htmlspecialchars($dir['distrito_direccion']); ?><?php if(!empty($dir['codigo_postal'])): ?>, <?php echo htmlspecialchars($dir['codigo_postal']); ?><?php endif; ?> <?php echo htmlspecialchars($dir['provincia_direccion']); ?>, <?php echo htmlspecialchars($dir['departamento_direccion']); ?>, Perú
                                </div>
                            </div>
                            
                            <!-- Datos ocultos para JavaScript -->
                            <input type="hidden" class="addr-nombre" value="<?php echo htmlspecialchars($dir['nombre_cliente_direccion']); ?>">
                            <input type="hidden" class="addr-email" value="<?php echo htmlspecialchars($usuario_logueado['email_usuario']); ?>">
                            <input type="hidden" class="addr-telefono" value="<?php echo htmlspecialchars($dir['telefono_direccion']); ?>">
                            <input type="hidden" class="addr-direccion" value="<?php echo htmlspecialchars($dir['direccion_completa_direccion']); ?>">
                            <input type="hidden" class="addr-departamento" value="<?php echo htmlspecialchars($dir['departamento_direccion']); ?>">
                            <input type="hidden" class="addr-provincia" value="<?php echo htmlspecialchars($dir['provincia_direccion']); ?>">
                            <input type="hidden" class="addr-distrito" value="<?php echo htmlspecialchars($dir['distrito_direccion']); ?>">
                            <input type="hidden" class="addr-referencia" value="<?php echo htmlspecialchars($dir['referencia_direccion'] ?? ''); ?>">
                            <input type="hidden" class="addr-metodo-pago" value="<?php echo htmlspecialchars($dir['metodo_pago_favorito'] ?? ''); ?>">
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="address-empty-state">
                        <i class="fa fa-map-marker-alt"></i>
                        <p>No tienes direcciones guardadas</p>
                        <button class="btn-add-first-address" onclick="window.location.href='profile.php#direcciones'">
                            <i class="fa fa-plus"></i> Agregar dirección
                        </button>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if(!empty($todas_direcciones)): ?>
            <div class="bottom-sheet-footer">
                <button type="button" class="btn-add-new-address" onclick="window.location.href='profile.php#direcciones'">
                    <i class="fa fa-plus-circle"></i> Agregar nueva dirección
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- Bottom Sheet End -->

    <!-- Bottom Sheet para Ver Productos Completos -->
    <div id="productsBottomSheet" class="address-bottom-sheet">
        <div class="bottom-sheet-content">
            <div class="bottom-sheet-header">
                <div class="bottom-sheet-handle"></div>
                <h3 class="bottom-sheet-title">Productos en tu pedido (<?php echo count($cart_items); ?>)</h3>
            </div>
            
            <div class="bottom-sheet-body">
                <div class="products-detail-list">
                    <?php foreach($cart_items as $item): 
                        $precio_original = $item['precio_producto'];
                        $precio_con_descuento = $precio_original;
                        $tiene_descuento = $item['descuento_porcentaje_producto'] > 0;
                        
                        if($tiene_descuento) {
                            $precio_con_descuento = $precio_original - ($precio_original * $item['descuento_porcentaje_producto'] / 100);
                        }
                        
                        $subtotal = $precio_con_descuento * $item['cantidad_carrito'];
                    ?>
                    <div class="product-detail-card">
                        <div class="product-detail-image">
                            <img src="<?php echo htmlspecialchars($item['url_imagen_producto']); ?>" 
                                 alt="<?php echo htmlspecialchars($item['nombre_producto']); ?>">
                            <?php if($tiene_descuento): ?>
                            <div class="product-detail-badge" style="background:#FB7701">
                                -<?php echo $item['descuento_porcentaje_producto']; ?>%
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-detail-info">
                            <h4 class="product-detail-name"><?php echo htmlspecialchars($item['nombre_producto']); ?></h4>
                            
                            <div class="product-detail-prices">
                                <div class="product-price-row">
                                    <span class="product-price-label">Precio unitario:</span>
                                    <div class="product-price-values">
                                        <?php if($tiene_descuento): ?>
                                        <span class="product-price-old">S/ <?php echo number_format($precio_original, 2); ?></span>
                                        <?php endif; ?>
                                        <span class="product-price-current">S/ <?php echo number_format($precio_con_descuento, 2); ?></span>
                                    </div>
                                </div>
                                
                                <div class="product-quantity-row">
                                    <span class="product-quantity-label">Cantidad:</span>
                                    <span class="product-quantity-value">×<?php echo $item['cantidad_carrito']; ?></span>
                                </div>
                                
                                <div class="product-subtotal-row">
                                    <span class="product-subtotal-label">Subtotal:</span>
                                    <span class="product-subtotal-value">S/ <?php echo number_format($subtotal, 2); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="bottom-sheet-footer">
                <div class="products-total-summary">
                    <span class="total-label">Total de productos:</span>
                    <span class="total-value">S/ <?php 
                        $total_productos = 0;
                        foreach($cart_items as $item) {
                            $precio = $item['precio_producto'];
                            if($item['descuento_porcentaje_producto'] > 0) {
                                $precio = $precio - ($precio * $item['descuento_porcentaje_producto'] / 100);
                            }
                            $total_productos += $precio * $item['cantidad_carrito'];
                        }
                        echo number_format($total_productos, 2);
                    ?></span>
                </div>
            </div>
        </div>
    </div>
    <!-- Bottom Sheet Productos End -->

    <script>
        // ========================================
        // DATOS DE UBIGEO DE PERÚ (Solo si existen los campos)
        // ========================================
        let ubigeoData = null;
        
        // Verificar si existen los campos de ubigeo antes de cargar
        const departamentoField = document.getElementById('departamento');
        const provinciaField = document.getElementById('provincia');
        const distritoField = document.getElementById('distrito');
        
        if(departamentoField && provinciaField && distritoField) {
            // Cargar datos de ubigeo
            fetch('public/assets/data/peru-ubigeo.json')
                .then(response => response.json())
                .then(data => {
                    ubigeoData = data;
                    cargarDepartamentos();
                })
                .catch(error => {
                    showNotification('⚠️ Error al cargar datos de ubicación', 'error');
                });
            
            // Cargar departamentos en el select
            function cargarDepartamentos() {
                const selectDepartamento = document.getElementById('departamento');
                selectDepartamento.innerHTML = '<option value="">Seleccionar...</option>';
                
                ubigeoData.departamentos.forEach(depto => {
                    const option = document.createElement('option');
                    option.value = depto.nombre;
                    option.textContent = depto.nombre;
                    option.dataset.id = depto.id;
                    selectDepartamento.appendChild(option);
                });
            }
            
            // Cuando selecciona departamento, cargar provincias
            departamentoField.addEventListener('change', function() {
                const selectedDepto = ubigeoData.departamentos.find(d => d.nombre === this.value);
                const selectProvincia = document.getElementById('provincia');
                const selectDistrito = document.getElementById('distrito');
                
                // Limpiar provincia y distrito
                selectProvincia.innerHTML = '<option value="">Seleccionar...</option>';
                selectDistrito.innerHTML = '<option value="">Selecciona provincia primero</option>';
                selectDistrito.disabled = true;
                
                if(selectedDepto) {
                    selectProvincia.disabled = false;
                    selectedDepto.provincias.forEach(prov => {
                        const option = document.createElement('option');
                        option.value = prov.nombre;
                        option.textContent = prov.nombre;
                        option.dataset.id = prov.id;
                        option.dataset.distritos = JSON.stringify(prov.distritos);
                        selectProvincia.appendChild(option);
                    });
                    } else {
                    selectProvincia.disabled = true;
                    }
            });
            
            // Cuando selecciona provincia, cargar distritos
            provinciaField.addEventListener('change', function() {
                const selectDistrito = document.getElementById('distrito');
                selectDistrito.innerHTML = '<option value="">Seleccionar...</option>';
                
                if(this.value) {
                    const selectedOption = this.options[this.selectedIndex];
                    const distritos = JSON.parse(selectedOption.dataset.distritos || '[]');
                    
                    selectDistrito.disabled = false;
                    distritos.forEach(distrito => {
                        const option = document.createElement('option');
                        option.value = distrito;
                        option.textContent = distrito;
                        selectDistrito.appendChild(option);
                    });
                    } else {
                    selectDistrito.disabled = true;
                    }
            });
        } else {
            // Campos de ubigeo no encontrados (usando dirección predeterminada)
        }
        
        // ========================================
        // DETECCIÓN AUTOMÁTICA DE TIPO DE COMPROBANTE (Solo si existe el campo)
        // ========================================
        const dniRucField = document.getElementById('dni_ruc');
        if(dniRucField) {
            dniRucField.addEventListener('input', function(e) {
                // Solo permitir números
                this.value = this.value.replace(/\D/g, '');
                
                const valor = this.value;
                const tipoComprobanteInput = document.getElementById('tipo_comprobante');
                const facturaFields = document.getElementById('facturaFields');
                const razonSocialInput = document.getElementById('razon_social');
                const comprobanteHint = document.getElementById('comprobante-hint');
                
                // Limpiar hints previos
                comprobanteHint.style.display = 'none';
                
                if(valor.length === 8) {
                    // DNI - Boleta
                    tipoComprobanteInput.value = 'boleta';
                    facturaFields.style.display = 'none';
                    razonSocialInput.required = false;
                    
                    comprobanteHint.innerHTML = '<i class="fa fa-check-circle"></i> Se emitirá <strong>Boleta de Venta</strong>';
                    comprobanteHint.className = 'form-text text-success';
                    comprobanteHint.style.display = 'block';
                    
                } else if(valor.length === 11) {
                    // RUC - Factura
                    tipoComprobanteInput.value = 'factura';
                    facturaFields.style.display = 'block';
                    razonSocialInput.required = true;
                    
                    comprobanteHint.innerHTML = '<i class="fa fa-building-o"></i> Se emitirá <strong>Factura Electrónica</strong> - Completa razón social abajo';
                    comprobanteHint.className = 'form-text text-warning';
                    comprobanteHint.style.display = 'block';
                    
                } else if(valor.length > 0) {
                    // Cantidad inválida
                    tipoComprobanteInput.value = '';
                    comprobanteHint.innerHTML = '<i class="fa fa-exclamation-triangle"></i> DNI debe tener 8 dígitos o RUC 11 dígitos';
                    comprobanteHint.className = 'form-text text-danger';
                    comprobanteHint.style.display = 'block';
                } else {
                    // Vacío
                    tipoComprobanteInput.value = '';
                    facturaFields.style.display = 'none';
                }
            });
        }
        
        // ========================================
        // SELECCIÓN DE MÉTODO DE PAGO - ESTILO TEMU
        // ========================================
        document.querySelectorAll('.pay-item-container, .payment-method-card, .payment-method-row, .payment-card-new').forEach(card => {
            card.addEventListener('click', function() {
                // Remover selección de todas las tarjetas
                document.querySelectorAll('.pay-item-container, .payment-method-card, .payment-method-row, .payment-card-new').forEach(c => {
                    c.classList.remove('selected');
                    // Remover estilos inline
                    c.style.border = '';
                    c.style.background = '';
                    
                    // Resetear checks anteriores
                    const check = c.querySelector('.payment-check');
                    if(check) {
                        check.style.background = '';
                        check.style.border = '';
                        const checkIcon = check.querySelector('i');
                        if(checkIcon) checkIcon.style.color = 'transparent';
                    }
                    
                    // Resetear check buttons de Temu
                    const checkBtn = c.querySelector('.pay-check-btn');
                    if(checkBtn) {
                        checkBtn.classList.remove('checked');
                    }
                });
                
                // Agregar selección a la tarjeta clickeada
                this.classList.add('selected');
                
                // Actualizar check button de Temu
                const checkBtn = this.querySelector('.pay-check-btn');
                if(checkBtn) {
                    checkBtn.classList.add('checked');
                }
                
                // Actualizar checks anteriores
                const check = this.querySelector('.payment-check, .payment-method-check');
                if(check) {
                    const checkIcon = check.querySelector('i');
                    if(checkIcon) checkIcon.style.color = 'white';
                }
                
                // Guardar el método seleccionado
                const metodoPago = this.dataset.paymentMethod;
                
                const metodoPagoField = document.getElementById('metodo_pago');
                if(metodoPagoField) {
                    metodoPagoField.value = metodoPago;
                }
                
                const metodoPagoFullField = document.getElementById('metodo_pago_full');
                if(metodoPagoFullField) {
                    metodoPagoFullField.value = metodoPago;
                }
                
                // Ocultar mensajes de error si existen
                const errorDiv = document.getElementById('payment-method-error');
                if(errorDiv) errorDiv.style.display = 'none';
                
                const errorDivFull = document.getElementById('payment-method-error-full');
                if(errorDivFull) errorDivFull.style.display = 'none';
                
            });
            
            // Efecto hover (manejado por CSS)
            card.addEventListener('mouseenter', function() {
                if(!this.classList.contains('selected')) {
                    // El CSS ya maneja el hover
                }
            });
            
            card.addEventListener('mouseleave', function() {
                if(!this.classList.contains('selected')) {
                    // El CSS ya maneja el hover
                }
            });
        });
        
        // ========================================
        // VALIDACIÓN DEL FORMULARIO
        // ========================================
        // MANEJO DEL FORMULARIO DE CHECKOUT CON MODAL DE GUARDAR DIRECCIÓN
        // ========================================
        let checkoutFormData = null;
        
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = this;
            const btnSubmit = document.getElementById('btnPlaceOrder');
            const tieneDireccionPredeterminada = <?php echo $tiene_direccion_predeterminada ? 'true' : 'false'; ?>;

            // =====================================================
            // VALIDACIONES SOLO PARA FORMULARIO SIN DIRECCIÓN
            // =====================================================
            if (!tieneDireccionPredeterminada) {
                // Validar tipo de comprobante (automático)
                const tipoComprobante = document.getElementById('tipo_comprobante');
                if(!tipoComprobante || !tipoComprobante.value) {
                    showNotification('⚠️ Por favor ingresa un DNI (8 dígitos) o RUC (11 dígitos) válido', 'warning');
                    const dniField = document.getElementById('dni_ruc');
                    if(dniField) dniField.focus();
                    return;
                }
                
                // Validar DNI/RUC
                const dniRuc = document.getElementById('dni_ruc');
                if(dniRuc) {
                    const tipoComprobanteValue = tipoComprobante.value;
                    
                    if(tipoComprobanteValue === 'factura') {
                        if(dniRuc.value.length !== 11 || !/^\d+$/.test(dniRuc.value)) {
                            showNotification('⚠️ El RUC debe tener exactamente 11 dígitos numéricos', 'warning');
                            dniRuc.focus();
                            return;
                        }
                        
                        const razonSocial = document.getElementById('razon_social');
                        if(razonSocial && !razonSocial.value.trim()) {
                            showNotification('⚠️ Por favor completa la Razón Social para emitir factura', 'warning');
                            razonSocial.focus();
                            return;
                        }
                    } else if(tipoComprobanteValue === 'boleta') {
                        if(dniRuc.value.length !== 8 || !/^\d+$/.test(dniRuc.value)) {
                            showNotification('⚠️ El DNI debe tener exactamente 8 dígitos numéricos', 'warning');
                            dniRuc.focus();
                            return;
                        }
                    }
                }
                
                // Validar ubicación (selects)
                const departamento = document.getElementById('departamento');
                const provincia = document.getElementById('provincia');
                const distrito = document.getElementById('distrito');
                
                if(departamento && provincia && distrito) {
                    if(!departamento.value || !provincia.value || !distrito.value) {
                        showNotification('⚠️ Por favor completa todos los campos de ubicación', 'warning');
                        return;
                    }
                }
            } else {
                }
            
            // =====================================================
            // VALIDACIÓN DE MÉTODO DE PAGO (SIEMPRE REQUERIDO)
            // =====================================================
            let metodoPago = '';
            const metodoPagoField = document.getElementById('metodo_pago');
            const metodoPagoFullField = document.getElementById('metodo_pago_full');
            
            if(metodoPagoField) {
                metodoPago = metodoPagoField.value;
            } else if(metodoPagoFullField) {
                metodoPago = metodoPagoFullField.value;
            }
            
            if(!metodoPago) {
                // Mostrar mensaje de error correspondiente
                const errorDiv = document.getElementById('payment-method-error');
                const errorDivFull = document.getElementById('payment-method-error-full');
                
                if(errorDiv) errorDiv.style.display = 'block';
                if(errorDivFull) errorDivFull.style.display = 'block';
                
                // Scroll hacia la sección de métodos de pago
                const paymentGrid = document.querySelector('.payment-methods-grid');
                if(paymentGrid) {
                    paymentGrid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                
                showNotification('💳 Por favor selecciona un método de pago', 'warning');
                return;
            }
            
            // =====================================================
            // PREPARAR Y ENVIAR DATOS
            // =====================================================
            checkoutFormData = new FormData(form);
            
            // Si tiene dirección predeterminada, verificar que los campos hidden existan
            if (tieneDireccionPredeterminada) {
                // TODOS los campos vienen de hidden inputs (incluyendo DNI y tipo_comprobante)
                const camposRequeridos = ['nombre', 'email', 'telefono', 'dni', 'tipo_comprobante',
                                         'direccion', 'departamento', 'provincia', 'distrito'];
                
                camposRequeridos.forEach(campo => {
                    const valor = checkoutFormData.get(campo);
                    if (!valor || valor.trim() === '') {
                        // Intentar obtener del DOM directamente
                        const hiddenInput = form.querySelector(`input[name="${campo}"]`);
                        if (hiddenInput && hiddenInput.value) {
                            checkoutFormData.set(campo, hiddenInput.value);
                        } else {
                            }
                    } else {
                        }
                });
                
                // Verificar razon_social (opcional)
                const razonSocial = checkoutFormData.get('razon_social');
            }
            
            // Si no se usa dirección predeterminada, preguntar si se quiere guardar.
            // Si ya se usa, procesar directamente.
            if (!tieneDireccionPredeterminada) {
                if (window.LayerManager) {
                    window.LayerManager.openModal('saveAddressModal');
                }
                $('#saveAddressModal').modal('show');
            } else {
                // Procesar directamente el pedido
                procesarPedido(checkoutFormData);
            }
        });
        
        // Botón "Sí, guardar" - Procesar pedido Y guardar dirección
        document.getElementById('btnYesSaveAddress').addEventListener('click', function() {
            $('#saveAddressModal').modal('hide');
            
            // Agregar flag para guardar dirección
            checkoutFormData.append('guardar_direccion', '1');
            
            // Verificar si debe marcarse como predeterminada
            const chkSetAsDefault = document.getElementById('chkSetAsDefault');
            if(chkSetAsDefault && chkSetAsDefault.checked) {
                checkoutFormData.append('marcar_predeterminada', '1');
            }
            
            procesarPedido(checkoutFormData);
        });
        
        // Botón "No, gracias" - Solo procesar pedido
        document.getElementById('btnNoSaveAddress').addEventListener('click', function() {
            $('#saveAddressModal').modal('hide');
            
            // No agregar flag (o agregar como 0)
            checkoutFormData.append('guardar_direccion', '0');
            
            procesarPedido(checkoutFormData);
        });
        
        // Función para procesar el pedido
        function procesarPedido(formData) {
            const btnSubmit = document.getElementById('btnPlaceOrder');
            const mobileBtnSubmit = document.getElementById('mobileBtnPlaceOrder');
            
            // Deshabilitar botones y mostrar loading
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Guardando información...';
            
            if(mobileBtnSubmit) {
                mobileBtnSubmit.disabled = true;
                mobileBtnSubmit.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Guardando...';
            }
            
            // Enviar con AJAX
            const baseUrl = (window.BASE_URL || '').replace(/\/+$/, '');
            fetch(baseUrl + '/app/actions/process_checkout.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    // Mostrar notificación de éxito
                    if(data.direccion_guardada === true) {
                        showNotification('✅ Dirección guardada en tu perfil', 'success');
                    }
                    
                    // Mostrar notificación de pedido creado
                    showNotification('🎉 ¡Pedido creado exitosamente!', 'success');
                    
                    // Esperar un momento y redirigir
                    setTimeout(() => {
                        window.location.href = 'order-confirmation.php?order=' + data.order_id;
                    }, 1500);
                    
                } else {
                    // Mostrar error con toast
                    let errorMsg = data.message || 'Error al procesar la información';
                    showNotification('❌ ' + errorMsg, 'error');
                    
                    // Restaurar botones
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = '<i class="fa fa-arrow-right"></i> Continuar al Pago';
                    
                    if(mobileBtnSubmit) {
                        mobileBtnSubmit.disabled = false;
                        mobileBtnSubmit.innerHTML = '<i class="fa fa-arrow-right"></i> <span>Continuar al Pago</span>';
                    }
                }
            })
            .catch(error => {
                showNotification('❌ Error al procesar la información', 'error');
                
                // Restaurar botones
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="fa fa-arrow-right"></i> Continuar al Pago';
                
                if(mobileBtnSubmit) {
                    mobileBtnSubmit.disabled = false;
                    mobileBtnSubmit.innerHTML = '<i class="fa fa-arrow-right"></i> <span>Continuar al Pago</span>';
                }
            });
        }
        
        // Formatear teléfono (solo permitir números y +) - Solo si existe el campo
        const telefonoField = document.getElementById('telefono');
        if(telefonoField) {
            telefonoField.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9+\s]/g, '');
            });
        }

        // Toggle de submenús en offcanvas
        $(document).on('click', '.offcanvas-menu-toggle', function(e) {
            e.preventDefault();
            const $this = $(this);
            const $submenu = $this.next('.offcanvas-submenu');
            
            // Toggle del icono
            $this.toggleClass('active');
            
            // Slide toggle del submenu
            $submenu.slideToggle(300);
            
            // Cerrar otros submenus
            $('.offcanvas-menu-toggle').not($this).removeClass('active');
            $('.offcanvas-submenu').not($submenu).slideUp(300);
        });

        // Click en usuario del offcanvas para abrir modal
        $('#offcanvas-user-profile').on('click', function() {
            // Cerrar offcanvas
            $(".offcanvas-menu-wrapper").removeClass("active");
            $(".offcanvas-menu-overlay").removeClass("active");
            
            // Abrir modal de usuario después de cerrar offcanvas
            setTimeout(function() {
                if (window.LayerManager) {
                    window.LayerManager.openModal('userAccountModal');
                }
                $('#userAccountModal').modal('show');
            }, 300);
        });

        // ========================================
        // MODAL DE DIRECCIÓN PREDETERMINADA
        // ========================================
        
        
        <?php if($tiene_direccion_predeterminada): ?>
        const defaultAddress = {
            nombre: '<?php echo addslashes($direccion_predeterminada["nombre_cliente_direccion"] ?? ""); ?>',
            telefono: '<?php echo addslashes($direccion_predeterminada["telefono_direccion"] ?? ""); ?>',
            email: '<?php echo addslashes($usuario_logueado["email_usuario"] ?? ""); ?>',
            direccion: '<?php echo addslashes($direccion_predeterminada["direccion_completa_direccion"] ?? ""); ?>',
            departamento: '<?php echo addslashes($direccion_predeterminada["departamento_direccion"] ?? ""); ?>',
            provincia: '<?php echo addslashes($direccion_predeterminada["provincia_direccion"] ?? ""); ?>',
            distrito: '<?php echo addslashes($direccion_predeterminada["distrito_direccion"] ?? ""); ?>',
            referencia: '<?php echo addslashes($direccion_predeterminada["referencia_direccion"] ?? ""); ?>',
            metodoPago: '<?php echo addslashes($direccion_predeterminada["metodo_pago_favorito"] ?? ""); ?>'
        };
        
        // Debug: Mostrar datos en consola
        // NO mostrar modal automáticamente - el usuario ya tiene dirección predeterminada activa
        // El modal solo se abrirá cuando haga clic en "Cambiar dirección"

        // Botón "Usar esta dirección" - Rellenar campos automáticamente
        $('#btnUseDefaultAddress').on('click', function() {
            $('#useDefaultAddressModal').modal('hide');
            
            // ==========================================
            // 1. RELLENAR INFORMACIÓN DEL CLIENTE
            // ==========================================
            if(defaultAddress.nombre) {
                $('#nombre').val(defaultAddress.nombre);
                }
            if(defaultAddress.email) {
                $('#email').val(defaultAddress.email);
                }
            if(defaultAddress.telefono) {
                $('#telefono').val(defaultAddress.telefono);
                }
            
            // ==========================================
            // 2. RELLENAR DIRECCIÓN DE ENVÍO
            // ==========================================
            
            // Extraer la dirección de la dirección completa
            // Formato: "Dirección, Distrito, Provincia, Departamento"
            // o "Dirección (Ref: referencia), Distrito, Provincia, Departamento"
            const parts = defaultAddress.direccion.split(',');
            let direccionLimpia = parts[0]?.trim() || '';
            
            // Si hay referencia en paréntesis, quitarla
            if(direccionLimpia.includes('(Ref:')) {
                const refMatch = direccionLimpia.match(/\(Ref: (.+?)\)/);
                if(refMatch) {
                    $('#referencia').val(refMatch[1].trim());
                }
                direccionLimpia = direccionLimpia.replace(/\s*\(Ref:.+?\)\s*/, '').trim();
            } else if(defaultAddress.referencia) {
                $('#referencia').val(defaultAddress.referencia);
            }
            
            // Rellenar campo de dirección
            $('#direccion').val(direccionLimpia);
            
            // ==========================================
            // 3. RELLENAR UBIGEO (Departamento, Provincia, Distrito)
            // ==========================================
            
            // Función auxiliar mejorada para esperar a que un select tenga opciones
            function waitForOptions(selector, expectedValue, callback, maxAttempts = 20) {
                let attempts = 0;
                const checkInterval = setInterval(function() {
                    const selectElement = $(selector);
                    const options = selectElement.find('option');
                    const hasOptions = options.length > 1; // Más de 1 (la opción "Seleccionar...")
                    const isEnabled = !selectElement.prop('disabled');
                    
                    attempts++;
                    if (hasOptions && isEnabled) {
                        clearInterval(checkInterval);
                        // Pequeña pausa adicional para asegurar que el DOM está listo
                        setTimeout(callback, 50);
                    } else if (attempts >= maxAttempts) {
                        clearInterval(checkInterval);
                        }
                }, 150); // Revisar cada 150ms
            }
            
            // Seleccionar departamento
            if(defaultAddress.departamento) {
                const deptSelect = $('#departamento');
                deptSelect.val(defaultAddress.departamento);
                // Disparar el evento change usando JavaScript nativo (más compatible)
                const deptElement = document.getElementById('departamento');
                const changeEvent = new Event('change', { bubbles: true });
                deptElement.dispatchEvent(changeEvent);
                
                // Esperar a que se carguen las provincias
                if(defaultAddress.provincia) {
                    waitForOptions('#provincia', defaultAddress.provincia, function() {
                        const provSelect = $('#provincia');
                        provSelect.val(defaultAddress.provincia);
                        // Disparar el evento change para provincia usando JavaScript nativo
                        const provElement = document.getElementById('provincia');
                        const provChangeEvent = new Event('change', { bubbles: true });
                        provElement.dispatchEvent(provChangeEvent);
                        
                        // Esperar a que se carguen los distritos
                        if(defaultAddress.distrito) {
                            waitForOptions('#distrito', defaultAddress.distrito, function() {
                                const distSelect = $('#distrito');
                                distSelect.val(defaultAddress.distrito);
                                });
                        }
                    });
                }
            }
            
            // ==========================================
            // 4. RELLENAR MÉTODO DE PAGO FAVORITO (si existe)
            // ==========================================
            if(defaultAddress.metodoPago) {
                $('#metodo_pago').val(defaultAddress.metodoPago);
            }
            
            // ==========================================
            // 5. EFECTOS VISUALES
            // ==========================================
            
            // Scroll suave hacia el formulario
            $('html, body').animate({
                scrollTop: $('#nombre').offset().top - 100
            }, 600);
            
            // Resaltar brevemente los campos rellenados
            $('#nombre, #email, #telefono, #dni_ruc, #direccion, #departamento, #provincia, #distrito, #referencia, #metodo_pago').each(function() {
                if($(this).val()) {
                    $(this).css('background-color', '#e8f5e9');
                    setTimeout(() => {
                        $(this).css('background-color', '');
                    }, 2000);
                }
            });
        });

        // Botón "Usar otra dirección" - Mostrar formulario completo
        $('#btnUseOtherAddress').on('click', function() {
            $('#useDefaultAddressModal').modal('hide');
            
            // Recargar la página sin usar dirección predeterminada
            // Agregamos un parámetro para indicar que queremos el formulario completo
            window.location.href = 'checkout.php?show_full_form=1';
        });
        
        // Botón "Cambiar dirección" - Para abrir modal de selección de direcciones
        $('#btnChangeAddress').on('click', function() {
            if (window.LayerManager) {
                window.LayerManager.openModal('selectAddressModal');
            }
            $('#selectAddressModal').modal('show');
        });

        // Prevenir que Bootstrap modifique el scrollbar
        $.fn.modal.Constructor.prototype._setScrollbar = function() {};
        $.fn.modal.Constructor.prototype._resetScrollbar = function() {};
        
        // ========================================
        // SELECTOR DE DIRECCIÓN (MODAL LEGACY)
        // ========================================
        
        // Seleccionar una dirección del modal
        $('.address-option').on('click', function() {
            // Obtener todos los datos de la dirección seleccionada
            const addressData = {
                nombre: $(this).find('.addr-nombre').val(),
                email: $(this).find('.addr-email').val(),
                telefono: $(this).find('.addr-telefono').val(),
                direccion: $(this).find('.addr-direccion').val(),
                departamento: $(this).find('.addr-departamento').val(),
                provincia: $(this).find('.addr-provincia').val(),
                distrito: $(this).find('.addr-distrito').val(),
                referencia: $(this).find('.addr-referencia').val(),
                metodo_pago: $(this).find('.addr-metodo-pago').val()
            };
            
            // Actualizar los campos del formulario (hidden inputs en vista simplificada)
            $('#nombre_completo').val(addressData.nombre);
            $('#email').val(addressData.email);
            $('#telefono').val(addressData.telefono);
            $('#direccion_completa').val(addressData.direccion);
            $('#departamento').val(addressData.departamento);
            $('#provincia').val(addressData.provincia);
            $('#distrito').val(addressData.distrito);
            $('#referencia').val(addressData.referencia);
            $('#metodo_pago').val(addressData.metodo_pago);
            
            // Actualizar la vista previa en la vista simplificada
            $('#preview_nombre').text(addressData.nombre);
            $('#preview_telefono').text(addressData.telefono);
            $('#preview_direccion').text(addressData.direccion + ', ' + addressData.distrito + ', ' + addressData.provincia + ', ' + addressData.departamento);
            
            // Cerrar el modal
            $('#selectAddressModal').modal('hide');
            
            // Mostrar notificación de éxito
            Swal.fire({
                icon: 'success',
                title: 'Dirección actualizada',
                text: 'Se ha seleccionado la dirección correctamente',
                timer: 2000,
                showConfirmButton: false,
                background: '#1a1a1a',
                color: '#fff',
                iconColor: '#c9a67c'
            });
        });
        
        <?php endif; ?>
    </script>
</body>
    
</html>





