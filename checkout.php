<?php
/**
 * PÁGINA DE CHECKOUT - PROCESO DE PAGO
 * Permite al usuario completar su compra
 */

session_start();
require_once 'config/conexion.php';

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

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Cookie&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Css Styles -->
    <link rel="stylesheet" href="public/assets/css/bootstrap.min.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/font-awesome.min.css" type="text/css">
    
    <!-- Font Awesome 6.4.0 (Iconos modernos - Misma versión que cart.php) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="public/assets/css/elegant-icons.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/jquery-ui.min.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/magnific-popup.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/owl.carousel.min.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/slicknav.min.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/style.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/user-account-modal.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/favorites-modal.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/dark-mode.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/modals-dark-mode.css" type="text/css">
    
    <!-- ✅ FIX: Eliminar barra blanca al lado del scrollbar -->
    <link rel="stylesheet" href="public/assets/css/fix-white-bar.css?v=1.0" type="text/css">
    
    <!-- Global Responsive Styles - TODO EL PROYECTO -->
    <link rel="stylesheet" href="public/assets/css/global-responsive.css?v=1.0" type="text/css">
    
    <!-- Modern Styles -->
    <link rel="stylesheet" href="public/assets/css/modals-animations.css?v=<?= time() ?>">
    <link rel="stylesheet" href="public/assets/css/notifications-modal.css">
    
    <style>
        /* ============================================
           ESTILOS DEL CHECKOUT
           ============================================ */
        
        /* Quitar línea del breadcrumb y ajustar márgenes */
        .breadcrumb-option {
            border-bottom: none !important;
            padding-bottom: 0 !important;
            margin-bottom: 0 !important;
        }

        .checkout.spad {
            padding-top: 30px !important;
        }

        /* Eliminar padding extra en columna del resumen */
        .checkout .row {
            margin-top: 0 !important;
            display: flex !important;
            flex-wrap: wrap !important;
        }

        .checkout .col-lg-8,
        .checkout .col-lg-4,
        .checkout .col-md-4,
        .checkout .col-md-8 {
            padding-top: 0 !important;
        }
        
        .checkout-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            padding: 20px 0;
            border-bottom: 2px solid #f0f0f0;
        }
        .checkout-step {
            flex: 1;
            text-align: center;
            position: relative;
        }
        .checkout-step::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background: #e1e1e1;
            z-index: -1;
        }
        .checkout-step:first-child::before {
            left: 50%;
        }
        .checkout-step:last-child::before {
            right: 50%;
        }
        .step-number {
            width: 40px;
            height: 40px;
            background: #e1e1e1;
            color: #666;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }
        .checkout-step.active .step-number {
            background: #ca1515;
            color: white;
        }
        .checkout-step.completed .step-number {
            background: #28a745;
            color: white;
        }

        /* ============================================
           ESTILOS DEL CHECKOUT
           ============================================ */
        
        .checkout-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            padding: 20px 0;
            border-bottom: 2px solid #f0f0f0;
        }
        .checkout-step {
            flex: 1;
            text-align: center;
            position: relative;
        }
        .checkout-step::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background: #e1e1e1;
            z-index: -1;
        }
        .checkout-step:first-child::before {
            left: 50%;
        }
        .checkout-step:last-child::before {
            right: 50%;
        }
        .step-number {
            width: 40px;
            height: 40px;
            background: #e1e1e1;
            color: #666;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }
        .checkout-step.active .step-number {
            background: #ca1515;
            color: white;
        }
        .checkout-step.completed .step-number {
            background: #2ecc71;
            color: white;
        }
        .step-title {
            font-size: 13px;
            font-weight: 600;
            color: #666;
        }
        .checkout-step.active .step-title {
            color: #111;
        }
        
        .form-section {
            background: #f8f8f8;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            border: 1px solid #e1e1e1;
        }
        .form-section h5 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 20px;
            color: #111;
            padding-bottom: 10px;
            border-bottom: 2px solid #ca1515;
            display: inline-block;
        }
        .form-group label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        .form-control {
            border: 1px solid #e1e1e1;
            border-radius: 5px;
            padding: 12px 15px;
            font-size: 14px;
            background: white;
        }
        .form-control:focus {
            border-color: #ca1515;
            box-shadow: 0 0 0 0.2rem rgba(202,21,21,0.1);
        }
        
        /* MODO OSCURO - Form Sections */
        body.dark-mode .form-section {
            background: linear-gradient(135deg, #2a2a2a 0%, #1f1f1f 100%);
            border: 2px solid #3a3a3a;
        }
        
        body.dark-mode .form-section h5 {
            color: #c9a67c;
            border-bottom-color: #c9a67c;
        }
        
        body.dark-mode .form-group label {
            color: #c9a67c;
        }
        
        body.dark-mode .form-control {
            background: #1a1a1a;
            border-color: #3a3a3a;
            color: #e0e0e0;
        }
        
        body.dark-mode .form-control:focus {
            border-color: #c9a67c;
            box-shadow: 0 0 0 0.2rem rgba(201, 166, 124, 0.2);
        }
        
        body.dark-mode .form-control::placeholder {
            color: #666;
        }
        
        body.dark-mode select.form-control option {
            background: #1a1a1a;
            color: #e0e0e0;
        }
        
        /* ============================================
           GRID DE PRODUCTOS EN CHECKOUT - UNA SOLA FILA CON SCROLL
           ============================================ */
        .checkout-products-grid {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            overflow-x: auto;
            overflow-y: hidden;
            padding-bottom: 10px;
            scrollbar-width: thin;
            scrollbar-color: #c9a67c #f0f0f0;
        }

        .checkout-products-grid::-webkit-scrollbar {
            height: 8px;
        }

        .checkout-products-grid::-webkit-scrollbar-track {
            background: #f0f0f0;
            border-radius: 10px;
        }

        .checkout-products-grid::-webkit-scrollbar-thumb {
            background: #c9a67c;
            border-radius: 10px;
        }

        .checkout-products-grid::-webkit-scrollbar-thumb:hover {
            background: #a08661;
        }

        .checkout-products-more {
            min-width: 140px;
            width: 140px;
            flex-shrink: 0;
            background: linear-gradient(135deg, #c9a67c 0%, #a08661 100%);
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: #1a1a1a;
            font-weight: 700;
            cursor: default;
            border: 2px solid #c9a67c;
            min-height: 200px;
        }

        .checkout-products-more-count {
            font-size: 32px;
            line-height: 1;
        }

        .checkout-products-more-text {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .checkout-product-card {
            min-width: 140px;
            width: 140px;
            flex-shrink: 0;
            background: white;
            border: 2px solid #e1e1e1;
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .checkout-product-card:hover {
            border-color: #c9a67c;
            box-shadow: 0 6px 16px rgba(201, 166, 124, 0.2);
            transform: translateY(-2px);
        }

        .checkout-product-image {
            position: relative;
            width: 100%;
            padding-top: 100%;
            overflow: hidden;
            background: #f8f8f8;
        }

        .checkout-product-image img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .checkout-product-discount {
            position: absolute;
            top: 4px;
            left: 4px;
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
            color: white;
            padding: 2px 5px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 700;
            box-shadow: 0 2px 6px rgba(255, 107, 107, 0.3);
            z-index: 2;
        }

        .checkout-product-quantity {
            position: absolute;
            bottom: 4px;
            right: 4px;
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(6px);
            color: white;
            padding: 3px 6px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: 700;
            z-index: 2;
        }

        .checkout-product-info {
            padding: 8px;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .checkout-product-name {
            font-size: 10px;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
            line-height: 1.2;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis; 
            min-height: 24px;
        }

        .checkout-product-prices {
            display: flex;
            align-items: center;
            gap: 3px;
            flex-wrap: wrap;
        }

        .checkout-product-price-old {
            font-size: 8px;
            color: #999;
            text-decoration: line-through;
        }

        .checkout-product-price {
            font-size: 10px;
            font-weight: 700;
            color: #c9a67c;
        }

        .checkout-product-subtotal {
            display: none;
        }

        /* Responsive para grid de productos */
        @media (max-width: 768px) {
            .checkout-product-card,
            .checkout-products-more {
                min-width: 120px;
                width: 120px;
            }
        }

        @media (max-width: 480px) {
            .checkout-products-grid {
                gap: 6px;
            }
            
            .checkout-product-card,
            .checkout-products-more {
                min-width: 100px;
                width: 100px;
            }
        }
        
        /* MODO OSCURO - Productos */
        body.dark-mode .checkout-product-card {
            background: #2a2a2a;
            border-color: #3a3a3a;
        }
        
        body.dark-mode .checkout-product-card:hover {
            border-color: #c9a67c;
            box-shadow: 0 6px 16px rgba(201, 166, 124, 0.3);
        }
        
        body.dark-mode .checkout-product-name {
            color: #e0e0e0;
        }
        
        body.dark-mode .checkout-product-image {
            background: #1a1a1a;
        }
        
        body.dark-mode .checkout-products-grid::-webkit-scrollbar-track {
            background: #2a2a2a;
        }
        
        body.dark-mode .checkout-products-grid::-webkit-scrollbar-thumb {
            background: #c9a67c;
        }

        /* ============================================
           MÉTODOS DE PAGO
           ============================================ */
        .payment-methods-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 16px;
            margin-top: 20px;
        }

        .payment-method-card {
            background: white;
            border: 2px solid #e1e1e1;
            border-radius: 12px;
            padding: 16px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .payment-method-card:hover {
            border-color: #c9a67c;
            box-shadow: 0 4px 12px rgba(201, 166, 124, 0.2);
            transform: translateY(-2px);
        }

        .payment-method-card.selected {
            border-color: #c9a67c;
            background: linear-gradient(135deg, #fffbf5 0%, #fff8ed 100%);
            box-shadow: 0 4px 12px rgba(201, 166, 124, 0.3);
        }

        .payment-method-check {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #e1e1e1;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .payment-method-card.selected .payment-method-check {
            background: linear-gradient(135deg, #4caf50 0%, #45a049 100%);
            box-shadow: 0 2px 8px rgba(76, 175, 80, 0.4);
        }

        .payment-method-card:not(.selected) .payment-method-check {
            opacity: 0.3;
        }

        .payment-method-highlight {
            border-color: #c9a67c;
            background: linear-gradient(135deg, #fffbf5 0%, #fff8ed 100%);
        }

        .payment-method-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
            color: white;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 700;
            box-shadow: 0 2px 6px rgba(255, 107, 107, 0.3);
        }

        .payment-method-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            background: linear-gradient(135deg, #c9a67c 0%, #a08661 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 22px;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(201, 166, 124, 0.3);
        }

        .payment-method-info {
            flex: 1;
            min-width: 0;
        }

        .payment-method-name {
            font-size: 15px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 4px;
        }

        .payment-method-desc {
            font-size: 12px;
            color: #666;
            line-height: 1.4;
        }

        .payment-method-note {
            font-size: 11px;
            color: #999;
            line-height: 1.4;
            margin-top: 4px;
        }

        .payment-method-promo {
            font-size: 12px;
            color: #ff6b6b;
            font-weight: 600;
            margin-top: 4px;
        }
        
        /* MODO OSCURO - Métodos de Pago */
        body.dark-mode .payment-method-card {
            background: #2a2a2a;
            border-color: #3a3a3a;
        }
        
        body.dark-mode .payment-method-card:hover {
            border-color: #c9a67c;
            box-shadow: 0 4px 12px rgba(201, 166, 124, 0.3);
        }
        
        body.dark-mode .payment-method-card.selected {
            border-color: #c9a67c;
            background: linear-gradient(135deg, #3a3a3a 0%, #2a2a2a 100%);
        }
        
        body.dark-mode .payment-method-name {
            color: #e0e0e0;
        }
        
        body.dark-mode .payment-method-desc {
            color: #a0a0a0;
        }
        
        body.dark-mode .payment-method-note {
            color: #888;
        }
        
        body.dark-mode .payment-method-check {
            background: #3a3a3a;
        }

        /* Responsive para métodos de pago */
        @media (max-width: 768px) {
            .payment-methods-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }
        }
        
        .order-summary {
            background: white;
            border: 1px solid #e1e1e1;
            border-radius: 10px;
            padding: 25px;
            position: sticky;
            top: 20px;
        }
        .order-summary h5 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        /* MODO OSCURO - Order Summary */
        body.dark-mode .order-summary {
            background: linear-gradient(135deg, #2a2a2a 0%, #1f1f1f 100%);
            border: 2px solid #3a3a3a;
        }
        
        body.dark-mode .order-summary h5 {
            color: #c9a67c;
            border-bottom-color: rgba(201, 166, 124, 0.2);
        }
        
        body.dark-mode .order-total-row span {
            color: #d0d0d0 !important;
        }
        
        body.dark-mode .order-total-row.total span {
            color: #c9a67c !important;
        }
        
        body.dark-mode .alert {
            background: #2a2a2a;
            border-color: #3a3a3a;
            color: #d0d0d0;
        }
        .order-item {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
        }
        .order-item:last-child {
            border-bottom: none;
        }
        .order-item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }
        .order-item-info {
            flex: 1;
        }
        .order-item-name {
            font-size: 14px;
            font-weight: 600;
            color: #111;
            margin-bottom: 5px;
        }
        .order-item-details {
            font-size: 12px;
            color: #666;
        }
        .order-item-price {
            font-size: 14px;
            font-weight: 600;
            color: #ca1515;
        }
        
        .order-totals {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #f0f0f0;
        }
        .order-total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 14px;
        }
        .order-total-row.total {
            font-size: 18px;
            font-weight: 700;
            color: #111;
            padding-top: 12px;
            border-top: 2px solid #f0f0f0;
        }
        
        .payment-method {
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .payment-method:hover {
            border-color: #ca1515;
            background: #fff5f5;
        }
        .payment-method input[type="radio"] {
            margin-right: 10px;
        }
        .payment-method.selected {
            border-color: #ca1515;
            background: #fff5f5;
        }
        
        .btn-place-order {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 40px;
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
            border-radius: 50px;
            width: 100%;
            margin-top: 20px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-place-order:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(55, 61, 69, 0.7);
        }
        .btn-place-order:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        
        /* MODO OSCURO - Botones */
        body.dark-mode .btn-place-order {
            background: linear-gradient(135deg, #c9a67c 0%, #a08661 100%);
            color: #1a1a1a;
            box-shadow: 0 4px 15px rgba(201, 166, 124, 0.3);
        }
        
        body.dark-mode .btn-place-order:hover {
            background: linear-gradient(135deg, #a08661 0%, #c9a67c 100%);
            box-shadow: 0 6px 20px rgba(201, 166, 124, 0.4);
        }
        
        body.dark-mode .btn-place-order:disabled {
            background: #3a3a3a;
            color: #666;
        }
        
        .comprobante-type {
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }
        .comprobante-type:hover {
            border-color: #ca1515;
        }
        .comprobante-type input[type="radio"] {
            margin-bottom: 10px;
        }
        .comprobante-type.selected {
            border-color: #ca1515;
            background: #fff5f5;
        }
        .comprobante-icon {
            font-size: 32px;
            color: #666;
            margin-bottom: 10px;
        }
        .comprobante-type.selected .comprobante-icon {
            color: #ca1515;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-danger {
            background: #ffe5e5;
            border: 1px solid #ffcccc;
            color: #cc0000;
        }
        .alert-success {
            background: #e5ffe5;
            border: 1px solid #ccffcc;
            color: #00cc00;
        }
        .alert-warning {
            background: #fff5e5;
            border: 1px solid #ffebcc;
            color: #cc8800;
        }
        
        /* Header fijo (sticky) */
        .header {
            position: sticky !important;
            top: 0;
            z-index: 999;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        /* Ajuste para compensar el header fijo */
        body {
            padding-top: 0;
            background-color: #f8f5f2;
            padding-left: 20px; /* Margen izquierdo para toda la página */
            overflow-x: hidden; /* Evitar scroll horizontal por el padding */
        }
        
        /* Ocultar scrollbar con estilo (quitar barra celeste/transparente) */
        body::-webkit-scrollbar {
            width: 8px;
        }
        
        body::-webkit-scrollbar-track {
            background: transparent; /* Eliminar fondo celeste/transparente */
        }
        
        body::-webkit-scrollbar-thumb {
            background: #c9a67c;
            border-radius: 4px;
        }
        
        body::-webkit-scrollbar-thumb:hover {
            background: #a08661;
        }
        
        /* Para Firefox */
        body {
            scrollbar-width: thin;
            scrollbar-color: #c9a67c transparent;
        }
        
        /* MODO OSCURO - Body y Backgrounds */
        body.dark-mode {
            background-color: #1a1a1a;
            padding-left: 20px; /* Mantener margen izquierdo en modo oscuro */
        }
        
        /* Scrollbar en modo oscuro */
        body.dark-mode::-webkit-scrollbar-track {
            background: transparent; /* Eliminar fondo celeste/transparente en modo oscuro */
        }
        
        body.dark-mode::-webkit-scrollbar-thumb {
            background: #c9a67c;
        }
        
        body.dark-mode::-webkit-scrollbar-thumb:hover {
            background: #a08661;
        }
        
        body.dark-mode .checkout.spad {
            background: #1a1a1a;
        }
        
        body.dark-mode .breadcrumb-option {
            background: #2a2a2a;
            border-bottom: 1px solid #3a3a3a;
        }
        
        body.dark-mode .breadcrumb__links a,
        body.dark-mode .breadcrumb__links span {
            color: #c9a67c;
        }
        
        body.dark-mode .breadcrumb__links a:hover {
            color: #a08661;
        }
        
        body.dark-mode .checkout-step .step-title {
            color: #888;
        }
        
        body.dark-mode .checkout-step.active .step-title {
            color: #c9a67c;
        }
        
        /* Ajustar ancho de los selects de ubigeo */
        #departamento,
        #provincia,
        #distrito {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            padding: 10px 35px 10px 15px !important;
            height: auto !important;
            min-height: 42px !important;
            overflow: visible !important;
            box-sizing: border-box !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            appearance: none !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23333' d='M10.293 3.293L6 7.586 1.707 3.293A1 1 0 00.293 4.707l5 5a1 1 0 001.414 0l5-5a1 1 0 10-1.414-1.414z'/%3E%3C/svg%3E") !important;
            background-repeat: no-repeat !important;
            background-position: right 12px center !important;
            background-size: 14px !important;
            font-size: 14px !important;
            line-height: 1.5 !important;
            color: #333 !important;
        }
        
        /* Asegurar que los selects no se corten */
        .form-control select,
        select.form-control {
            width: 100% !important;
            box-sizing: border-box !important;
            padding: 10px 35px 10px 15px !important;
            height: auto !important;
            min-height: 42px !important;
            font-size: 14px !important;
            line-height: 1.5 !important;
        }
        
        /* Ajustar el contenedor de los selects */
        .form-group select.form-control {
            display: block !important;
            width: 100% !important;
            padding: 10px 35px 10px 15px !important;
            height: auto !important;
            min-height: 42px !important;
            font-size: 14px !important;
            line-height: 1.5 !important;
            background-color: #fff !important;
            background-clip: padding-box !important;
            border: 1px solid #e1e1e1 !important;
            border-radius: 5px !important;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out !important;
        }
        
        /* Asegurar que las columnas no compriman los selects */
        .col-md-4 {
            padding-left: 15px !important;
            padding-right: 15px !important;
            flex: 0 0 33.333333% !important;
            max-width: 33.333333% !important;
        }
        
        /* Asegurar que el row use flexbox correctamente */
        .form-section .row {
            display: flex !important;
            flex-wrap: wrap !important;
            margin-left: -15px !important;
            margin-right: -15px !important;
        }
        
        /* Prevenir que form-group comprima el contenido */
        .form-group {
            margin-bottom: 1rem !important;
            width: 100% !important;
        }
        
        /* Mejorar orden summary para que no se superponga con header */
        .order-summary {
            top: 20px !important; /* Espacio para el header fijo */
        }

        /* ============================================
           FIX: ASEGURAR LAYOUT DE 2 COLUMNAS EN DESKTOP
           ============================================ */
        
        /* Mantener estructura similar a shop.php */
        @media (min-width: 992px) {
            .checkout .row {
                display: flex !important;
                flex-wrap: nowrap !important;
            }
            
            .checkout .col-lg-8 {
                flex: 0 0 66.666667% !important;
                max-width: 66.666667% !important;
                padding-left: 1.5rem;
            }
            
            .checkout .col-lg-4 {
                flex: 0 0 33.333333% !important;
                max-width: 33.333333% !important;
            }
        }
        
        /* Para tablets medianos */
        @media (min-width: 768px) and (max-width: 991px) {
            .checkout .col-md-8 {
                padding-left: 1rem;
            }
        }

        /* ============================================
           ESTILOS RESPONSIVOS PARA MÓVIL
           ============================================ */

        /* Imágenes de productos en móvil - ocultas por defecto */
        .mobile-cart-preview {
            display: none;
        }

        /* Footer sticky móvil - oculto por defecto */
        .mobile-checkout-footer {
            display: none;
        }

        /* Solo visible en móvil */
        @media (max-width: 991px) {
            /* Ocultar sidebar de resumen en móvil */
            .order-summary {
                display: none !important;
            }

            /* Mostrar vista previa de productos en móvil */
            .mobile-cart-preview {
                display: block;
                background: white;
                padding: 15px;
                margin-bottom: 20px;
                border-radius: 12px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.08);
                border: 1px solid #e0e0e0;
            }

            .mobile-cart-preview__header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 15px;
                padding-bottom: 12px;
                border-bottom: 2px solid #f0f0f0;
            }

            .mobile-cart-preview__title {
                font-size: 15px;
                font-weight: 700;
                color: #333;
            }

            .mobile-cart-preview__count {
                background: #ca1515;
                color: white;
                padding: 4px 10px;
                border-radius: 20px;
                font-size: 12px;
                font-weight: 700;
            }

            .mobile-cart-preview__items {
                display: flex;
                gap: 10px;
                overflow-x: auto;
                padding: 5px 0;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: none; /* Firefox */
            }

            .mobile-cart-preview__items::-webkit-scrollbar {
                display: none; /* Chrome, Safari */
            }

            .mobile-cart-preview__item {
                flex-shrink: 0;
                position: relative;
            }

            .mobile-cart-preview__item-image {
                width: 70px;
                height: 70px;
                border-radius: 8px;
                object-fit: cover;
                border: 2px solid #e0e0e0;
            }

            .mobile-cart-preview__item-qty {
                position: absolute;
                top: -5px;
                right: -5px;
                background: #111;
                color: white;
                width: 20px;
                height: 20px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 11px;
                font-weight: 700;
                box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            }

            /* Footer sticky móvil */
            .mobile-checkout-footer {
                display: block;
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                background: white;
                box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.15);
                z-index: 999;
                border-top: 1px solid #e0e0e0;
                backdrop-filter: blur(10px);
                background: rgba(255, 255, 255, 0.98);
            }

            .mobile-checkout-footer__content {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 12px 20px;
                gap: 15px;
                max-width: 1200px;
                margin: 0 auto;
            }

            .mobile-checkout-footer__total {
                flex: 1;
                min-width: 0;
            }

            .mobile-checkout-footer__label {
                font-size: 11px;
                color: #666;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-bottom: 2px;
                font-weight: 500;
            }

            .mobile-checkout-footer__amount {
                font-size: 22px;
                font-weight: 800;
                color: #111;
                line-height: 1.2;
            }

            .mobile-checkout-footer__shipping {
                font-size: 11px;
                color: #28a745;
                font-weight: 600;
                margin-top: 2px;
            }

            .mobile-checkout-footer__action {
                flex-shrink: 0;
            }

            .mobile-place-order-btn {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
                padding: 14px 28px;
                background: linear-gradient(135deg, #ca1515 0%, #a01010 100%);
                color: white;
                border-radius: 50px;
                font-weight: 700;
                font-size: 15px;
                text-decoration: none;
                box-shadow: 0 4px 15px rgba(202, 21, 21, 0.3);
                transition: all 0.3s ease;
                white-space: nowrap;
                border: none;
                cursor: pointer;
            }

            .mobile-place-order-btn:hover {
                background: linear-gradient(135deg, #a01010 0%, #800c0c 100%);
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(202, 21, 21, 0.4);
                color: white;
                text-decoration: none;
            }

            .mobile-place-order-btn i {
                font-size: 14px;
            }

            /* Agregar padding al body para compensar el footer sticky */
            body {
                padding-bottom: 80px !important;
            }

            /* Ajustar sección de checkout */
            .checkout.spad {
                padding: 1px 0 100px !important;
            }

            /* Hacer formulario de ancho completo */
            .checkout .col-lg-8 {
                width: 100%;
                max-width: 100%;
                flex: 0 0 100%;
            }

            /* Ajustar steps en móvil */
            .checkout-steps {
                margin-bottom: 20px;
                padding: 15px 0;
            }

            .step-number {
                width: 35px;
                height: 35px;
                font-size: 14px;
            }

            .step-title {
                font-size: 11px;
            }

            /* Ajustar form sections */
            .form-section {
                padding: 20px 15px;
                margin-bottom: 15px;
            }

            .form-section h5 {
                font-size: 16px;
                margin-bottom: 15px;
            }
        }

        /* Móviles pequeños */
        @media (max-width: 576px) {
            .mobile-checkout-footer__content {
                padding: 10px 15px;
                gap: 12px;
            }

            .mobile-checkout-footer__label {
                font-size: 10px;
            }

            .mobile-checkout-footer__amount {
                font-size: 20px;
            }

            .mobile-checkout-footer__shipping {
                font-size: 10px;
            }

            .mobile-place-order-btn {
                padding: 12px 22px;
                font-size: 14px;
                gap: 8px;
            }

            .mobile-place-order-btn span {
                display: none;
            }

            .mobile-place-order-btn::after {
                content: 'Realizar Pedido';
            }

            body {
                padding-bottom: 70px !important;
            }

            /* Steps más compactos */
            .checkout-steps {
                padding: 10px 0;
                margin-bottom: 15px;
            }

            .step-number {
                width: 30px;
                height: 30px;
                font-size: 13px;
            }

            .step-title {
                font-size: 10px;
            }

            /* Imágenes de productos más pequeñas */
            .mobile-cart-preview__item-image {
                width: 60px;
                height: 60px;
            }

            .mobile-cart-preview {
                padding: 12px;
                margin-bottom: 15px;
            }

            .mobile-cart-preview__title {
                font-size: 14px;
            }

            .mobile-cart-preview__count {
                font-size: 11px;
                padding: 3px 8px;
            }
        }

        /* Pantallas muy pequeñas */
        @media (max-width: 400px) {
            .mobile-checkout-footer__content {
                padding: 8px 12px;
                gap: 10px;
            }

            .mobile-checkout-footer__amount {
                font-size: 18px;
            }

            .mobile-place-order-btn {
                padding: 10px 18px;
                font-size: 13px;
            }
        }

        /* ========================================
           ESTILOS PARA MÉTODOS DE PAGO - MODO CLARO/OSCURO
        ======================================== */
        
        /* Contenedor principal de métodos de pago */
        .payment-methods-section {
            background: white !important;
            border: 2px solid #dee2e6;
            overflow: visible !important; /* Importante para que el badge no se corte */
            position: relative;
        }

        body.dark-mode .payment-methods-section {
            background: white !important; /* Mantener blanco en modo oscuro */
            border: 2px solid #3a3a3a;
            overflow: visible !important;
        }
        
        /* Badge de Yape - Asegurar visibilidad */
        .yape-badge {
            animation: pulse-badge 2s ease-in-out infinite;
        }
        
        @keyframes pulse-badge {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 6px 16px rgba(76, 175, 80, 0.7);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 8px 20px rgba(76, 175, 80, 0.9);
            }
        }

        /* Texto de seguridad */
        .payment-security-text {
            color: #6c757d;
        }

        body.dark-mode .payment-security-text {
            color: #888;
        }

        /* Filas de métodos de pago */
        .payment-method-row {
            background: #ffffff;
            border: 2px solid #dee2e6;
        }

        body.dark-mode .payment-method-row {
            background: #1a1a1a;
            border: 2px solid #3a3a3a;
        }

        /* Yape tiene borde especial */
        .payment-method-row.payment-yape {
            border: 2px solid #e1bee7;
        }

        body.dark-mode .payment-method-row.payment-yape {
            border: 2px solid #6a1b9a;
        }

        /* Hover en métodos de pago */
        .payment-method-row:hover {
            transform: translateX(4px);
            border-color: #c9a67c;
        }

        body.dark-mode .payment-method-row:hover {
            border-color: #c9a67c;
        }

        /* Títulos de métodos de pago */
        .payment-title {
            color: #212529;
            font-weight: 600;
            font-size: 15px;
            margin-bottom: 4px;
        }

        body.dark-mode .payment-title {
            color: #fff;
        }

        /* Subtítulos de métodos de pago */
        .payment-subtitle {
            color: #6c757d;
            font-size: 12px;
        }

        body.dark-mode .payment-subtitle {
            color: #888;
        }

        /* Highlight de Yape */
        .payment-yape-highlight {
            color: #6a1b9a;
            font-size: 13px;
            font-weight: 600;
        }

        body.dark-mode .payment-yape-highlight {
            color: #ba68c8;
        }

        /* Check de pago */
        .payment-check {
            border: 2px solid #dee2e6;
        }

        body.dark-mode .payment-check {
            border: 2px solid #3a3a3a;
        }

        /* Método de pago seleccionado */
        .payment-method-row.selected {
            border-color: #c9a67c !important;
            background: rgba(201, 166, 124, 0.1) !important;
        }

        .payment-method-row.selected .payment-check {
            border-color: #c9a67c !important;
            background: linear-gradient(135deg, #c9a67c 0%, #a8865e 100%) !important;
        }

        .payment-method-row.selected .payment-check i {
            color: white !important;
        }

        /* ========================================
           NUEVAS CARDS DE PAGO - DISEÑO INTUITIVO V2.0
        ======================================== */
        
        .payment-card-new {
            position: relative;
            overflow: visible !important; /* Cambiar de hidden a visible para que el badge se muestre */
        }
        
        /* Card de Yape con badge - overflow visible */
        .payment-card-featured {
            overflow: visible !important;
        }
        
        .payment-card-new::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(135deg, transparent, transparent);
            border-radius: 16px;
            opacity: 0;
            transition: all 0.3s ease;
            z-index: -1;
        }
        
        .payment-card-new:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(201, 166, 124, 0.2) !important;
            border-color: #c9a67c !important;
        }
        
        .payment-card-new.selected {
            border-color: #c9a67c !important;
            background: linear-gradient(135deg, rgba(201, 166, 124, 0.08) 0%, #ffffff 100%) !important;
            box-shadow: 0 8px 24px rgba(201, 166, 124, 0.25) !important;
            transform: translateY(-2px);
        }
        
        body.dark-mode .payment-card-new {
            background: linear-gradient(135deg, #2a2a2a 0%, #252525 100%) !important;
            border-color: #3a3a3a !important;
        }
        
        body.dark-mode .payment-card-new:hover {
            border-color: #c9a67c !important;
            background: linear-gradient(135deg, #333333 0%, #2d2d2d 100%) !important;
            box-shadow: 0 4px 16px rgba(201, 166, 124, 0.3) !important;
        }
        
        body.dark-mode .payment-card-new.selected {
            border-color: #c9a67c !important;
            background: linear-gradient(135deg, #2a2a2a 0%, #252525 100%) !important;
            box-shadow: 0 6px 20px rgba(201, 166, 124, 0.5), 
                        inset 0 0 30px rgba(201, 166, 124, 0.1) !important;
        }
        
        body.dark-mode .payment-card-new h6 {
            color: #e0e0e0 !important;
        }
        
        body.dark-mode .payment-card-new p {
            color: #a0a0a0 !important;
        }
        
        /* Radio button en dark mode */
        body.dark-mode .payment-card-new div[style*="border: 3px solid"] {
            border-color: #3a3a3a !important;
        }
        
        body.dark-mode .payment-card-new.selected div[style*="border: 3px solid"] {
            border-color: #c9a67c !important;
        }
        
        /* Sección de pagos en modo oscuro */
        body.dark-mode .payment-methods-section {
            background: linear-gradient(135deg, #2a2a2a 0%, #1f1f1f 100%) !important;
            border: 2px solid #3a3a3a !important;
        }
        
        /* Texto de seguridad en dark mode */
        body.dark-mode .payment-security-text {
            color: #a0a0a0 !important;
        }
        
        /* Título de la sección en dark mode */
        body.dark-mode .payment-methods-section h5 {
            color: #c9a67c !important;
        }
        
        /* Iconos dentro de las payment cards en dark mode */
        body.dark-mode .payment-card-new svg rect,
        body.dark-mode .payment-card-new svg circle {
            opacity: 0.9;
        }
        
        /* Bordes de los contenedores de iconos en dark mode */
        body.dark-mode .payment-card-new > div[style*="border: 2px solid"] {
            border-color: #4a4a4a !important;
            background: #1a1a1a !important;
        }
        
        /* Badge de Yape en dark mode */
        body.dark-mode .yape-badge {
            border-color: #3a3a3a !important;
        }
        
        /* Mensaje de error en dark mode */
        body.dark-mode #payment-method-error {
            background: linear-gradient(135deg, #3a2f1f 0%, #2a2419 100%) !important;
            border-left-color: #c9a67c !important;
        }
        
        body.dark-mode #payment-method-error p {
            color: #f0d9b5 !important;
        }
        
        /* Radio button en las cards - Actualizado para estructura horizontal */
        .payment-card-new .payment-radio-inner {
            transition: all 0.3s ease;
        }
        
        /* Cuando la card está seleccionada */
        .payment-card-new.selected {
            border-color: #c9a67c !important;
            background: #f9f9f9 !important;
            box-shadow: 0 6px 20px rgba(201, 166, 124, 0.3) !important;
        }
        
        /* Card seleccionada en dark mode - sobrescribir fondo claro */
        body.dark-mode .payment-card-new.selected {
            background: linear-gradient(135deg, #2d2d2d 0%, #282828 100%) !important;
            box-shadow: 0 6px 20px rgba(201, 166, 124, 0.5), 
                        inset 0 0 30px rgba(201, 166, 124, 0.15) !important;
        }
        
        /* Radio button seleccionado (buscar el div con el círculo) */
        .payment-card-new.selected > div > div.payment-radio-inner,
        .payment-card-new.selected div[style*="border-radius: 50%"] .payment-radio-inner {
            background: #c9a67c !important;
            box-shadow: 0 0 0 3px rgba(201, 166, 124, 0.2);
        }
        
        /* Borde del radio button cuando está seleccionado */
        .payment-card-new.selected div[style*="width: 24px"] {
            border-color: #c9a67c !important;
        }
        
        /* Animación shake */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        
        /* Responsive para cards de pago - FILA HORIZONTAL EN DESKTOP */
        .payment-methods-row {
            display: flex;
            gap: 16px;
            overflow: visible !important; /* Permitir que el badge se vea completo */
            position: relative;
        }
        
        @media (min-width: 769px) {
            .payment-methods-row {
                flex-direction: row; /* Forzar fila horizontal en desktop */
                overflow: visible !important;
            }
        }
        
        @media (max-width: 768px) {
            .payment-methods-row {
                flex-direction: column; /* Stack vertical en móvil */
                overflow: visible !important;
            }
            
            body {
                padding-left: 10px; /* Reducir margen izquierdo en móvil */
            }
            
            body.dark-mode {
                padding-left: 10px;
            }
            
            /* Ocultar sección de productos en móviles (ya se muestra en el preview superior) */
            .checkout-products-section {
                display: none !important;
            }
            
            /* ========================================
               MEJORAR SECCIÓN DE DIRECCIÓN EN MÓVILES
            ======================================== */
            .checkout-address-section {
                padding: 14px !important;
                margin-bottom: 18px !important;
            }
            
            /* Header de la dirección (título + botón al costado como icono) */
            .address-header-section {
                flex-direction: row !important;
                gap: 8px;
                align-items: center !important;
                justify-content: space-between !important;
            }
            
            .checkout-address-section h5 {
                font-size: 15px !important;
                flex: 1;
            }
            
            /* Botón cambiar dirección - pequeño icono al costado */
            .btn-change-address {
                width: auto !important;
                padding: 8px 12px !important;
                font-size: 13px !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 6px;
                white-space: nowrap;
            }
            
            /* Card de preview de dirección - más compacto */
            .address-preview-card {
                padding: 12px !important;
                border-radius: 8px !important;
            }
            
            /* Grid de campos - dos columnas compactas */
            .address-grid {
                grid-template-columns: 1fr 1fr !important;
                gap: 10px 8px !important;
                font-size: 13px !important;
            }
            
            /* Cada campo de dirección - sin bordes para compactar */
            .address-field {
                padding: 0;
            }
            
            /* Campos que ocupan ancho completo */
            .address-field-full {
                grid-column: 1 / -1 !important;
            }
            
            /* Labels de los campos - más pequeños */
            .address-label {
                font-size: 10px !important;
                color: #c9a67c !important;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                font-weight: 600 !important;
                margin-bottom: 3px !important;
                display: block;
            }
            
            /* Valores de los campos - compactos */
            .address-value,
            .address-value-ref {
                font-size: 13px !important;
                display: block;
                line-height: 1.4;
                color: #333333;
                font-weight: 500;
            }
            
            /* Dark mode para valores */
            body.dark-mode .address-value,
            body.dark-mode .address-value-ref {
                color: #e0e0e0;
            }
        }

        /* ========================================
           ESTILOS PARA RESUMEN DEL PEDIDO - MODO CLARO/OSCURO
        ======================================== */
        
        /* Contenedor del resumen */
        .checkout-summary {
            background: #ffffff;
            border: 2px solid #e1e1e1;
        }

        body.dark-mode .checkout-summary {
            background: #1a1a1a;
            border: 2px solid #3a3a3a;
        }

        /* Título del resumen */
        .summary-title {
            color: #c9a67c;
            border-bottom: 2px solid #e1e1e1;
        }

        body.dark-mode .summary-title {
            color: #c9a67c;
            border-bottom: 2px solid #3a3a3a;
        }

        /* Filas del resumen */
        .summary-row {
            border-bottom: 1px solid #e1e1e1;
        }

        body.dark-mode .summary-row {
            border-bottom: 1px solid #3a3a3a;
        }

        /* Labels y valores del resumen */
        .summary-label {
            color: #333;
            font-size: 14px;
            font-weight: 600;
        }

        body.dark-mode .summary-label {
            color: #e0e0e0;
        }

        .summary-value {
            color: #333;
            font-size: 14px;
            font-weight: 600;
        }

        body.dark-mode .summary-value {
            color: #e0e0e0;
        }

        /* Total destacado */
        .summary-total {
            background: rgba(201, 166, 124, 0.1);
        }

        body.dark-mode .summary-total {
            background: rgba(201, 166, 124, 0.15);
        }

        .summary-total span {
            color: #c9a67c;
        }

        body.dark-mode .summary-total span {
            color: #c9a67c;
        }

        /* ========================================
           ESTILOS PARA SECCIONES DE CHECKOUT - MODO CLARO/OSCURO
        ======================================== */
        
        /* Sección de dirección */
        .checkout-address-section {
            background: #ffffff;
            border: 2px solid #e1e1e1;
        }

        body.dark-mode .checkout-address-section {
            background: linear-gradient(135deg, #2a2a2a 0%, #1f1f1f 100%);
            border: 2px solid #3a3a3a;
        }

        /* Sección de productos */
        .checkout-products-section {
            background: #ffffff;
            border: 2px solid #e1e1e1;
        }

        body.dark-mode .checkout-products-section {
            background: linear-gradient(135deg, #2a2a2a 0%, #1f1f1f 100%);
            border: 2px solid #3a3a3a;
        }

        /* Botón cambiar dirección */
        .btn-change-address {
            background: #c9a67c;
            color: #1a1a1a;
        }

        body.dark-mode .btn-change-address {
            background: #c9a67c;
            color: #1a1a1a;
        }

        /* Card de preview de dirección */
        .address-preview-card {
            background: rgba(201, 166, 124, 0.1);
        }

        body.dark-mode .address-preview-card {
            background: rgba(201, 166, 124, 0.1);
        }

        /* Labels de dirección */
        .address-label {
            color: #c9a67c;
        }

        body.dark-mode .address-label {
            color: #c9a67c;
        }

        /* Valores de dirección */
        .address-value {
            color: #333;
        }

        body.dark-mode .address-value {
            color: #d0d0d0;
        }

        /* Referencia (estilo especial) */
        .address-value-ref {
            font-style: italic;
            color: #6c757d;
        }

        body.dark-mode .address-value-ref {
            font-style: italic;
            color: #a0a0a0;
        }

        /* ========================================
           ESTILOS PARA MODAL DE DIRECCIÓN - MODO CLARO/OSCURO
        ======================================== */
        
        /* Contenido del modal */
        .address-modal-content {
            background: #ffffff;
        }

        body.dark-mode .address-modal-content {
            background: #1a1a1a;
        }

        /* Body del modal */
        .address-modal-body {
            background: #ffffff;
        }

        body.dark-mode .address-modal-body {
            background: #1a1a1a;
        }

        /* Footer del modal */
        .address-modal-footer {
            background: #f8f9fa;
        }

        body.dark-mode .address-modal-footer {
            background: #1a1a1a;
        }

        /* Opciones de dirección */
        .address-option {
            background: #f8f9fa;
            border: 2px solid #e1e1e1;
        }

        body.dark-mode .address-option {
            background: linear-gradient(135deg, #2a2a2a 0%, #1f1f1f 100%);
            border: 2px solid #3a3a3a;
        }

        .address-option:hover {
            border-color: #c9a67c !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(201, 166, 124, 0.2);
        }

        /* Nombre en la opción */
        .address-option-name {
            color: #c9a67c;
        }

        body.dark-mode .address-option-name {
            color: #c9a67c;
        }

        /* Detalles de dirección */
        .address-option-details {
            color: #333;
        }

        body.dark-mode .address-option-details {
            color: #d0d0d0;
        }

        /* Teléfono */
        .address-option-phone {
            color: #6c757d;
        }

        body.dark-mode .address-option-phone {
            color: #a0a0a0;
        }

        /* Estado vacío */
        .address-empty-state {
            color: #6c757d;
        }

        body.dark-mode .address-empty-state {
            color: #888;
        }

        /* Botón agregar dirección */
        .btn-add-address {
            background: #3a3a3a;
            border: 1px solid #4a4a4a;
            color: #e0e0e0;
        }

        body.dark-mode .btn-add-address {
            background: #3a3a3a;
            border: 1px solid #4a4a4a;
            color: #e0e0e0;
        }

        .btn-add-address:hover {
            background: #c9a67c;
            border: 1px solid #c9a67c;
            color: #1a1a1a;
        }

        /* ========================================
           PREVENIR MOVIMIENTO DE PÁGINA CON MODAL
        ======================================== */
        
        /* Mantener scroll visible cuando modal está abierto */
        body.modal-open {
            overflow: auto !important;
        }

        /* ========================================
           ESTILOS DEL SCROLLBAR GLOBAL
        ======================================== */
        
        /* Scrollbar principal del body */
        body::-webkit-scrollbar {
            width: 12px;
        }

        body::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        body.dark-mode::-webkit-scrollbar-track {
            background: #1a1a1a;
        }

        body::-webkit-scrollbar-thumb {
            background: #c9a67c;
            border-radius: 6px;
        }

        body::-webkit-scrollbar-thumb:hover {
            background: #a08661;
        }

        /* Firefox scrollbar */
        body {
            scrollbar-width: thin;
            scrollbar-color: #c9a67c #f1f1f1;
        }

        body.dark-mode {
            scrollbar-color: #c9a67c #1a1a1a;
        }
    </style>
</head>

<body class="checkout-page">
    <?php include 'includes/offcanvas-menu.php'; ?>

    <?php include 'includes/header-section.php'; ?>

    <!-- Breadcrumb Begin -->
    <div class="breadcrumb-option">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="breadcrumb__links">
                        <a href="./index.php"><i class="fa fa-home"></i> Inicio</a>
                        <a href="./cart.php">Carrito</a>
                        <span>Checkout</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Breadcrumb End -->

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

            <!-- Vista previa de productos (Solo Móvil) -->
            <div class="mobile-cart-preview">
                <div class="mobile-cart-preview__header">
                    <span class="mobile-cart-preview__title">
                        <i class="fa fa-shopping-bag"></i> Tu Pedido
                    </span>
                    <span class="mobile-cart-preview__count">
                        <?php echo count($cart_items); ?> <?php echo count($cart_items) == 1 ? 'producto' : 'productos'; ?>
                    </span>
                </div>
                <div class="mobile-cart-preview__items">
                    <?php foreach($cart_items as $item): ?>
                    <div class="mobile-cart-preview__item">
                        <img src="<?php echo htmlspecialchars($item['url_imagen_producto']); ?>" 
                             alt="<?php echo htmlspecialchars($item['nombre_producto']); ?>"
                             class="mobile-cart-preview__item-image">
                        <div class="mobile-cart-preview__item-qty">
                            <?php echo $item['cantidad_carrito']; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="row" style="align-items: flex-start;">
                <div class="col-lg-8 col-md-8 col-12 ps-lg-4">
                    <form id="checkoutForm" action="app/actions/process_checkout.php" method="POST">
                        
                        <?php if($tiene_direccion_predeterminada): ?>
                        <!-- VISTA SIMPLIFICADA CON DIRECCIÓN PREDETERMINADA -->
                        <div class="form-section checkout-address-section" style="border-radius: 12px; padding: 24px; margin-bottom: 25px;">
                            <div class="address-header-section" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
                                <h5 style="color: #c9a67c; margin: 0; font-size: 18px; font-weight: 700;">
                                    <i class="fa fa-map-marker" style="margin-right: 8px;"></i> Dirección de Envío
                                </h5>
                                <button type="button" class="btn btn-sm btn-change-address" id="btnChangeAddress" 
                                        style="border: none; padding: 6px 16px; border-radius: 6px; font-weight: 600; font-size: 13px;">
                                    <i class="fa fa-edit"></i> Cambiar
                                </button>
                            </div>
                            
                            <div class="address-preview-card" style="border-radius: 8px; padding: 16px; border-left: 4px solid #c9a67c;">
                                <div class="address-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 14px;">
                                    <div class="address-field">
                                        <strong class="address-label" style="display: block; margin-bottom: 4px;">Nombre</strong>
                                        <span class="address-value" id="preview_nombre"><?php echo htmlspecialchars($direccion_predeterminada['nombre_cliente_direccion']); ?></span>
                                    </div>
                                    <div class="address-field">
                                        <strong class="address-label" style="display: block; margin-bottom: 4px;">Teléfono</strong>
                                        <span class="address-value" id="preview_telefono"><?php echo htmlspecialchars($direccion_predeterminada['telefono_direccion']); ?></span>
                                    </div>
                                    <div class="address-field">
                                        <strong class="address-label" style="display: block; margin-bottom: 4px;">DNI/RUC</strong>
                                        <span class="address-value" id="preview_dni"><?php echo htmlspecialchars($direccion_predeterminada['dni_ruc_direccion'] ?? 'No especificado'); ?></span>
                                    </div>
                                    <div class="address-field">
                                        <strong class="address-label" style="display: block; margin-bottom: 4px;">Tipo</strong>
                                        <span class="address-value" id="preview_tipo">
                                            <?php 
                                            $dni_length = strlen($direccion_predeterminada['dni_ruc_direccion'] ?? '');
                                            echo $dni_length === 11 ? 'Factura' : 'Boleta';
                                            ?>
                                        </span>
                                    </div>
                                    <div class="address-field address-field-full" style="grid-column: 1 / -1;">
                                        <strong class="address-label" style="display: block; margin-bottom: 4px;">Dirección</strong>
                                        <span class="address-value" id="preview_direccion"><?php echo htmlspecialchars($direccion_predeterminada['direccion_completa_direccion']); ?></span>
                                    </div>
                                    <?php if(!empty($direccion_predeterminada['referencia_direccion'])): ?>
                                    <div class="address-field address-field-full" style="grid-column: 1 / -1;">
                                        <strong class="address-label" style="display: block; margin-bottom: 4px;">Referencia</strong>
                                        <span class="address-value-ref" id="preview_referencia"><?php echo htmlspecialchars($direccion_predeterminada['referencia_direccion']); ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
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

                        <!-- Productos Seleccionados (Vista Simplificada) -->
                        <div class="form-section checkout-products-section" style="border-radius: 12px; padding: 24px; margin-bottom: 25px;">
                            <h5 style="color: #c9a67c; margin-bottom: 16px; font-size: 18px; font-weight: 700;">
                                <i class="fa fa-shopping-bag"></i> Productos en tu pedido (<?php echo count($cart_items); ?>)
                            </h5>
                            <div class="checkout-products-grid">
                                <?php 
                                $max_visible_products = 5; // Mostrar 5 productos
                                $total_products = count($cart_items);
                                $products_to_show = array_slice($cart_items, 0, $max_visible_products);
                                $remaining_products = $total_products - $max_visible_products;
                                
                                foreach($products_to_show as $item): 
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
                                
                                <?php if($remaining_products > 0): ?>
                                <div class="checkout-products-more">
                                    <div class="checkout-products-more-count">+<?php echo $remaining_products; ?></div>
                                    <div class="checkout-products-more-text">Más</div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Métodos de Pago - EN FILA HORIZONTAL -->
                        <div class="form-section payment-methods-section" style="border-radius: 12px; padding: 24px 24px 20px 24px; margin-bottom: 25px; background: white; overflow: visible; position: relative;">
                            <p class="payment-security-text" style="font-size: 13px; margin: 0 0 12px 0; color: #888; text-align: center;">
                                <i class="fa fa-shield" style="color: #4caf50;"></i> Pago 100% seguro y encriptado
                            </p>
                            <h5 style="color: #c9a67c; margin-bottom: 24px; font-size: 18px; font-weight: 700; text-align: center;">
                                <i class="fa fa-credit-card"></i> Selecciona tu método de pago
                            </h5>
                            
                            <!-- Campo oculto para guardar el método seleccionado -->
                            <input type="hidden" id="metodo_pago" name="metodo_pago" value="" required>
                            
                            <!-- MÉTODOS DE PAGO EN FILA HORIZONTAL -->
                            <div class="payment-methods-row" style="display: flex; gap: 16px; margin-bottom: 20px; margin-top: 20px; position: relative;">
                                
                                <!-- Card Tarjeta - HORIZONTAL -->
                                <div class="payment-card-new" data-payment-method="tarjeta" style="flex: 1; position: relative; border: 3px solid #e1e1e1; border-radius: 12px; padding: 20px; cursor: pointer; transition: all 0.3s ease; background: white; display: flex; align-items: center; gap: 16px; min-height: 80px;">
                                    <div style="flex-shrink: 0; width: 50px; height: 50px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fa fa-credit-card" style="font-size: 24px; color: white;"></i>
                                    </div>
                                    <div style="flex: 1;">
                                        <h6 style="font-size: 15px; font-weight: 700; color: #2c3e50; margin: 0 0 4px 0;">Tarjeta</h6>
                                        <p style="font-size: 12px; color: #666; margin: 0;">Crédito o Débito</p>
                                    </div>
                                    <div style="position: absolute; top: 50%; right: 16px; transform: translateY(-50%); width: 24px; height: 24px; border: 3px solid #e1e1e1; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
                                        <div class="payment-radio-inner" style="width: 12px; height: 12px; border-radius: 50%; background: transparent; transition: all 0.3s ease;"></div>
                                    </div>
                                </div>

                                <!-- Card PagoEfectivo - HORIZONTAL -->
                                <div class="payment-card-new" data-payment-method="pagoefectivo" style="flex: 1; position: relative; border: 3px solid #e1e1e1; border-radius: 12px; padding: 20px; cursor: pointer; transition: all 0.3s ease; background: white; display: flex; align-items: center; gap: 16px; min-height: 80px;">
                                    <div style="flex-shrink: 0; width: 50px; height: 50px; background: white; border-radius: 10px; display: flex; align-items: center; justify-content: center; border: 2px solid #FF6B35;">
                                        <!-- Logo PagoEfectivo simplificado -->
                                        <svg width="36" height="36" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="100" cy="100" r="95" fill="#FF6B35"/>
                                            <path d="M60 60 L60 140 L100 140 C120 140 130 130 130 110 C130 90 120 80 100 80 L60 80 Z M75 75 L100 75 C115 75 120 85 120 100 C120 115 115 125 100 125 L75 125 Z" fill="white"/>
                                        </svg>
                                    </div>
                                    <div style="flex: 1;">
                                        <h6 style="font-size: 15px; font-weight: 700; color: #2c3e50; margin: 0 0 4px 0;">PagoEfectivo</h6>
                                        <p style="font-size: 12px; color: #666; margin: 0;">Agentes y banca</p>
                                    </div>
                                    <div style="position: absolute; top: 50%; right: 16px; transform: translateY(-50%); width: 24px; height: 24px; border: 3px solid #e1e1e1; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
                                        <div class="payment-radio-inner" style="width: 12px; height: 12px; border-radius: 50%; background: transparent; transition: all 0.3s ease;"></div>
                                    </div>
                                </div>

                                <!-- Card Yape - HORIZONTAL CON BADGE -->
                                <div class="payment-card-new payment-card-featured" data-payment-method="yape" style="flex: 1; position: relative; border: 3px solid #e1e1e1; border-radius: 12px; padding: 20px 20px 20px 20px; cursor: pointer; transition: all 0.3s ease; background: white; display: flex; align-items: center; gap: 16px; min-height: 80px; overflow: visible;">
                                    <!-- Badge de descuento - SÚPER VISIBLE -->
                                    <div class="yape-badge" style="position: absolute; top: -14px; right: 20px; background: linear-gradient(135deg, #4caf50 0%, #66bb6a 100%); color: white; padding: 7px 16px; border-radius: 20px; font-size: 11px; font-weight: 800; box-shadow: 0 6px 16px rgba(76, 175, 80, 0.7); z-index: 100; letter-spacing: 0.5px; white-space: nowrap; border: 2px solid white;">
                                        <i class="fa fa-tag"></i> S/4 DESCUENTO
                                    </div>
                                    
                                    <div style="flex-shrink: 0; width: 50px; height: 50px; background: white; border-radius: 10px; display: flex; align-items: center; justify-content: center; border: 2px solid #6A1B9A;">
                                        <!-- Logo Yape simplificado -->
                                        <svg width="40" height="40" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <rect width="200" height="200" rx="40" fill="#6A1B9A"/>
                                            <path d="M100 40 L140 100 L115 100 L115 160 L85 160 L85 100 L60 100 Z" fill="white"/>
                                        </svg>
                                    </div>
                                    <div style="flex: 1;">
                                        <h6 style="font-size: 15px; font-weight: 700; color: #2c3e50; margin: 0 0 4px 0;">Yape</h6>
                                        <p style="font-size: 12px; color: #6a1b9a; margin: 0; font-weight: 600;">Pago instantáneo</p>
                                    </div>
                                    <div style="position: absolute; top: 50%; right: 16px; transform: translateY(-50%); width: 24px; height: 24px; border: 3px solid #e1e1e1; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
                                        <div class="payment-radio-inner" style="width: 12px; height: 12px; border-radius: 50%; background: transparent; transition: all 0.3s ease;"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Mensaje de validación -->
                            <div id="payment-method-error" style="display: none; margin-top: 0; padding: 14px 18px; background: linear-gradient(135deg, #fff3cd 0%, #fffbf0 100%); border-left: 4px solid #ffc107; border-radius: 8px; animation: shake 0.5s;">
                                <p style="color: #856404; font-size: 13px; margin: 0; font-weight: 600;">
                                    <i class="fa fa-exclamation-triangle" style="margin-right: 8px;"></i> Por favor selecciona un método de pago
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

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

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
    <script src="public/assets/js/jquery.slicknav.js"></script>
    <script src="public/assets/js/main.js"></script>
    
    <!-- Header Handler - Actualización en tiempo real de contadores -->
    <script src="public/assets/js/header-handler.js?v=1.0"></script>
    
    <!-- Sistema Global de Contadores -->
    <script src="public/assets/js/global-counters.js"></script>
    
    <!-- Real-time Updates System - DEBE IR ANTES que cart-favorites-handler -->
    <script src="public/assets/js/real-time-updates.js?v=<?= time() ?>"></script>
    
    <script src="public/assets/js/cart-favorites-handler.js"></script>
    <script src="public/assets/js/user-account-modal.js"></script>
    <!-- ❌ REMOVIDO: dark-mode.js (ya se carga desde dark-mode-assets.php en modern-libraries.php) -->

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
        // SELECCIÓN DE MÉTODO DE PAGO - NUEVAS CARDS V2.0
        // ========================================
        document.querySelectorAll('.payment-method-card, .payment-method-row, .payment-card-new').forEach(card => {
            card.addEventListener('click', function() {
                // Remover selección de todas las tarjetas/filas/cards
                document.querySelectorAll('.payment-method-card, .payment-method-row, .payment-card-new').forEach(c => {
                    c.classList.remove('selected');
                    // Remover estilos inline para que los CSS del modo tomen control
                    c.style.border = '';
                    c.style.background = '';
                    
                    // Resetear el check (para el diseño anterior)
                    const check = c.querySelector('.payment-check');
                    if(check) {
                        check.style.background = '';
                        check.style.border = '';
                        const checkIcon = check.querySelector('i');
                        if(checkIcon) checkIcon.style.color = 'transparent';
                    }
                });
                
                // Agregar selección a la tarjeta/fila/card clickeada
                this.classList.add('selected');
                
                // Actualizar el check (los estilos CSS manejan el color según el modo)
                const check = this.querySelector('.payment-check, .payment-method-check');
                if(check) {
                    const checkIcon = check.querySelector('i');
                    if(checkIcon) checkIcon.style.color = 'white';
                }
                
                // Guardar el método seleccionado en AMBOS campos ocultos (si existen)
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
        // Datos de la dirección predeterminada
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

    <!-- Global Offcanvas Menu JavaScript -->
    <script src="public/assets/js/offcanvas-menu.js"></script>

    <?php if($usuario_logueado): ?>
    <?php include 'includes/user-account-modal.php'; ?>
    <?php include 'includes/favorites-modal.php'; ?>
    <?php include 'includes/notifications-modal.php'; ?>
    <?php endif; ?>

    <!-- Chatbot Widget -->
    <?php include 'includes/chatbot-widget.php'; ?>
</body>
</html>





