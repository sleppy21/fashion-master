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
try {
    $favorites = executeQuery("SELECT COUNT(*) as total FROM favorito WHERE id_usuario = ?", [$usuario_logueado['id_usuario']]);
    $favorites_count = $favorites && !empty($favorites) ? (int)$favorites[0]['total'] : 0;
} catch(Exception $e) {
    error_log("Error al obtener favoritos: " . $e->getMessage());
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
    <link rel="stylesheet" href="public/assets/css/elegant-icons.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/jquery-ui.min.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/magnific-popup.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/owl.carousel.min.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/slicknav.min.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/style.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/user-account-modal.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/favorites-modal.css" type="text/css">
    
    <!-- Header Responsive Global CSS -->
    <link rel="stylesheet" href="public/assets/css/header-responsive.css?v=2.0" type="text/css">
    
    <!-- Header Override - Máxima prioridad -->
    <link rel="stylesheet" href="public/assets/css/header-override.css?v=2.0" type="text/css">
    
    <!-- Global Responsive Styles - TODO EL PROYECTO -->
    <link rel="stylesheet" href="public/assets/css/global-responsive.css?v=1.0" type="text/css">
    
    <style>
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
        }
        .form-control:focus {
            border-color: #ca1515;
            box-shadow: 0 0 0 0.2rem rgba(202,21,21,0.1);
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
            background: #ca1515;
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
            background: #b01010;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(202,21,21,0.3);
        }
        .btn-place-order:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
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
        }
        
        /* Ajustar ancho de los selects de ubigeo */
        #departamento,
        #provincia,
        #distrito {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            padding-right: 30px !important;
            overflow: visible !important;
        }
        
        /* Asegurar que los selects no se corten */
        .form-control select,
        select.form-control {
            width: 100% !important;
            box-sizing: border-box !important;
            padding-right: 30px !important;
        }
        
        /* Ajustar el contenedor de los selects */
        .form-group select.form-control {
            display: block !important;
            width: 100% !important;
            padding: 12px 30px 12px 15px !important;
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
        }
        
        /* Mejorar orden summary para que no se superponga con header */
        .order-summary {
            top: 120px !important; /* Espacio para el header fijo */
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
                padding: 20px 0 100px !important;
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
    </style>
</head>

<body>
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
        <div class="container">
            <!-- Steps Progress -->
            <div class="checkout-steps">
                <div class="checkout-step completed">
                    <div class="step-number">1</div>
                    <div class="step-title">Carrito</div>
                </div>
                <div class="checkout-step active">
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

            <div class="row">
                <div class="col-lg-8">
                    <form id="checkoutForm" action="app/actions/process_checkout.php" method="POST">
                        
                        <!-- Información del Cliente -->
                        <div class="form-section">
                            <h5><i class="fa fa-user"></i> Información del Cliente</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nombre">Nombre Completo *</label>
                                        <input type="text" class="form-control" id="nombre" name="nombre" 
                                               value="<?php echo htmlspecialchars($usuario_logueado['nombre_usuario'] . ' ' . $usuario_logueado['apellido_usuario']); ?>" required>
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

                        <!-- Método de Pago -->
                        <div class="form-section">
                            <h5><i class="fa fa-credit-card"></i> Método de Pago</h5>
                            
                            <div class="payment-method" onclick="selectPayment('tarjeta')">
                                <input type="radio" name="metodo_pago" value="tarjeta" id="pago_tarjeta" required>
                                <label for="pago_tarjeta" style="cursor: pointer; margin: 0;">
                                    <i class="fa fa-credit-card"></i>
                                    <strong>Tarjeta de Crédito/Débito</strong>
                                    <p style="margin: 5px 0 0; color: #666; font-size: 13px;">Pago seguro con Visa, Mastercard, American Express</p>
                                </label>
                            </div>

                            <div class="payment-method" onclick="selectPayment('transferencia')">
                                <input type="radio" name="metodo_pago" value="transferencia" id="pago_transferencia" required>
                                <label for="pago_transferencia" style="cursor: pointer; margin: 0;">
                                    <i class="fa fa-bank"></i>
                                    <strong>Transferencia Bancaria</strong>
                                    <p style="margin: 5px 0 0; color: #666; font-size: 13px;">Realizarás el pago mediante transferencia</p>
                                </label>
                            </div>

                            <div class="payment-method" onclick="selectPayment('yape')">
                                <input type="radio" name="metodo_pago" value="yape" id="pago_yape" required>
                                <label for="pago_yape" style="cursor: pointer; margin: 0;">
                                    <i class="fa fa-mobile" style="font-size: 20px;"></i>
                                    <strong>Yape / Plin</strong>
                                    <p style="margin: 5px 0 0; color: #666; font-size: 13px;">Pago instantáneo con billetera digital</p>
                                </label>
                            </div>

                            <div class="payment-method" onclick="selectPayment('efectivo')">
                                <input type="radio" name="metodo_pago" value="efectivo" id="pago_efectivo" required>
                                <label for="pago_efectivo" style="cursor: pointer; margin: 0;">
                                    <i class="fa fa-money"></i>
                                    <strong>Efectivo Contra Entrega</strong>
                                    <p style="margin: 5px 0 0; color: #666; font-size: 13px;">Paga en efectivo al recibir tu pedido</p>
                                </label>
                            </div>
                        </div>

                        <!-- Notas Adicionales -->
                        <div class="form-section">
                            <h5><i class="fa fa-comment"></i> Notas del Pedido (Opcional)</h5>
                            <div class="form-group">
                                <textarea class="form-control" name="notas" rows="4" 
                                          placeholder="¿Alguna instrucción especial para tu pedido?"></textarea>
                            </div>
                        </div>

                    </form>
                </div>

                <!-- Order Summary Sidebar -->
                <div class="col-lg-4">
                    <div class="order-summary">
                        <h5>Resumen del Pedido</h5>
                        
                        <!-- Cart Items -->
                        <?php foreach($cart_items as $item): 
                            $precio = $item['precio_producto'];
                            $precio_original = $precio;
                            if($item['descuento_porcentaje_producto'] > 0) {
                                $precio = $precio - ($precio * $item['descuento_porcentaje_producto'] / 100);
                            }
                            $item_total = $precio * $item['cantidad_carrito'];
                        ?>
                        <div class="order-item">
                            <img src="<?php echo htmlspecialchars($item['url_imagen_producto']); ?>" 
                                 alt="<?php echo htmlspecialchars($item['nombre_producto']); ?>">
                            <div class="order-item-info">
                                <div class="order-item-name"><?php echo htmlspecialchars($item['nombre_producto']); ?></div>
                                <div class="order-item-details">
                                    Cantidad: <?php echo $item['cantidad_carrito']; ?> 
                                    × $<?php echo number_format($precio, 2); ?>
                                </div>
                            </div>
                            <div class="order-item-price">
                                $<?php echo number_format($item_total, 2); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <!-- Totals -->
                        <div class="order-totals">
                            <div class="order-total-row">
                                <span>Subtotal:</span>
                                <span>$<?php echo number_format($subtotal, 2); ?></span>
                            </div>
                            <div class="order-total-row">
                                <span>Envío:</span>
                                <span><?php echo $costo_envio > 0 ? '$' . number_format($costo_envio, 2) : 'GRATIS'; ?></span>
                            </div>
                            <?php if($subtotal < 100 && $costo_envio > 0): ?>
                            <div class="order-total-row" style="font-size: 12px; color: #ca1515;">
                                <span colspan="2">
                                    <i class="fa fa-info-circle"></i>
                                    Agrega $<?php echo number_format(100 - $subtotal, 2); ?> más para envío gratis
                                </span>
                            </div>
                            <?php endif; ?>
                            <div class="order-total-row total">
                                <span>Total:</span>
                                <span>$<?php echo number_format($total, 2); ?></span>
                            </div>
                        </div>

                        <!-- Place Order Button -->
                        <button type="submit" form="checkoutForm" class="btn-place-order" id="btnPlaceOrder">
                            <i class="fa fa-lock"></i> Realizar Pedido
                        </button>

                        <!-- Security Notice -->
                        <div style="text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #f0f0f0;">
                            <p style="font-size: 12px; color: #666; margin: 0;">
                                <i class="fa fa-shield"></i> Pago 100% seguro y encriptado
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
                    <i class="fa fa-lock"></i>
                    <span>Realizar Pedido</span>
                </button>
            </div>
        </div>
    </div>
    <!-- Footer Sticky Móvil End -->

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Js Plugins -->
    <script src="public/assets/js/jquery-3.3.1.min.js"></script>
    <script src="public/assets/js/bootstrap.min.js"></script>
    <script src="public/assets/js/jquery.slicknav.js"></script>
    <script src="public/assets/js/main.js"></script>
    <script src="public/assets/js/cart-favorites-handler.js"></script>
    <script src="public/assets/js/user-account-modal.js"></script>

    <script>
        // ========================================
        // DATOS DE UBIGEO DE PERÚ
        // ========================================
        let ubigeoData = null;
        
        // Cargar datos de ubigeo
        fetch('public/assets/data/peru-ubigeo.json')
            .then(response => response.json())
            .then(data => {
                ubigeoData = data;
                cargarDepartamentos();
            })
            .catch(error => {
                console.error('Error al cargar ubigeo:', error);
                alert('Error al cargar datos de ubicación. Por favor recarga la página.');
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
        document.getElementById('departamento').addEventListener('change', function() {
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
        document.getElementById('provincia').addEventListener('change', function() {
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
        
        // ========================================
        // DETECCIÓN AUTOMÁTICA DE TIPO DE COMPROBANTE
        // ========================================
        document.getElementById('dni_ruc').addEventListener('input', function(e) {
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
        
        // ========================================
        // MÉTODO DE PAGO
        // ========================================
        function selectPayment(metodo) {
            // Remover selección anterior
            document.querySelectorAll('.payment-method').forEach(el => el.classList.remove('selected'));
            
            // Seleccionar nuevo
            document.querySelector(`#pago_${metodo}`).checked = true;
            document.querySelector(`#pago_${metodo}`).closest('.payment-method').classList.add('selected');
        }
        
        // ========================================
        // VALIDACIÓN DEL FORMULARIO
        // ========================================
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = this;
            const btnSubmit = document.getElementById('btnPlaceOrder');
            
            // Validar tipo de comprobante (automático)
            const tipoComprobante = document.getElementById('tipo_comprobante').value;
            if(!tipoComprobante) {
                alert('Por favor ingresa un DNI (8 dígitos) o RUC (11 dígitos) válido');
                document.getElementById('dni_ruc').focus();
                return;
            }
            
            // Validar que se haya seleccionado método de pago
            if(!document.querySelector('input[name="metodo_pago"]:checked')) {
                alert('Por favor selecciona un método de pago');
                return;
            }
            
            // Validar DNI/RUC
            const dniRuc = document.getElementById('dni_ruc').value;
            
            if(tipoComprobante === 'factura') {
                if(dniRuc.length !== 11 || !/^\d+$/.test(dniRuc)) {
                    alert('El RUC debe tener exactamente 11 dígitos numéricos');
                    document.getElementById('dni_ruc').focus();
                    return;
                }
                
                const razonSocial = document.getElementById('razon_social').value.trim();
                if(!razonSocial) {
                    alert('Por favor completa la Razón Social para emitir factura');
                    document.getElementById('razon_social').focus();
                    return;
                }
            } else if(tipoComprobante === 'boleta') {
                if(dniRuc.length !== 8 || !/^\d+$/.test(dniRuc)) {
                    alert('El DNI debe tener exactamente 8 dígitos numéricos');
                    document.getElementById('dni_ruc').focus();
                    return;
                }
            }
            
            // Validar ubicación (selects)
            const departamento = document.getElementById('departamento').value;
            const provincia = document.getElementById('provincia').value;
            const distrito = document.getElementById('distrito').value;
            
            if(!departamento || !provincia || !distrito) {
                alert('Por favor completa todos los campos de ubicación (Departamento, Provincia, Distrito)');
                return;
            }
            
            // Deshabilitar botón y mostrar loading
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Procesando pedido...';
            
            // Enviar formulario
            form.submit();
        });
        
        // Formatear teléfono (solo permitir números y +)
        document.getElementById('telefono').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9+\s]/g, '');
        });

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
                $('#userAccountModal').modal('show');
            }, 300);
        });
    </script>

    <!-- Global Offcanvas Menu JavaScript -->
    <script src="public/assets/js/offcanvas-menu.js"></script>

    <?php if($usuario_logueado): ?>
    <?php include 'includes/user-account-modal.php'; ?>
    <?php include 'includes/favorites-modal.php'; ?>
    <?php endif; ?>

</body>
</html>
