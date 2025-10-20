<?php
/**
 * PÁGINA DE CARRITO DE COMPRAS
 * Muestra todos los productos en el carrito del usuario
 */

session_start();
require_once 'config/conexion.php';

$page_title = "Carrito de Compras";

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
    header('Location: login.php?redirect=cart.php');
    exit;
}

// Obtener items del carrito
$cart_items = [];
$subtotal_sin_descuento = 0;
$descuento_total = 0;
$total_carrito = 0;

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
    
    // Calcular subtotal sin descuento, descuento total y total con descuento
    foreach($cart_items as $item) {
        $precio_original = $item['precio_producto'];
        $cantidad = $item['cantidad_carrito'];
        
        // Subtotal sin descuento (precio original)
        $subtotal_sin_descuento += $precio_original * $cantidad;
        
        // Si tiene descuento, calcular el ahorro
        if($item['descuento_porcentaje_producto'] > 0) {
            $descuento_item = ($precio_original * $item['descuento_porcentaje_producto'] / 100) * $cantidad;
            $descuento_total += $descuento_item;
        }
    }
    
    // Total final = Subtotal - Descuentos
    $total_carrito = $subtotal_sin_descuento - $descuento_total;
    
} catch(Exception $e) {
    error_log("Error al obtener carrito: " . $e->getMessage());
}

// Verificar si el usuario tiene dirección predeterminada
$tiene_direccion_predeterminada = false;
try {
    $direccion_check = executeQuery("
        SELECT COUNT(*) as total 
        FROM direccion 
        WHERE id_usuario = ? AND es_principal = 1 AND status_direccion = 1
    ", [$usuario_logueado['id_usuario']]);
    
    $tiene_direccion_predeterminada = ($direccion_check && $direccion_check[0]['total'] > 0);
} catch(Exception $e) {
    error_log("Error al verificar dirección: " . $e->getMessage());
}

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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="Carrito de Compras - SleppyStore">
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
    <link rel="stylesheet" href="public/assets/css/slicknav.min.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/style.css" type="text/css">
    

    <?php include 'includes/modern-libraries.php'; ?>
    
    <!-- User Account Modal Styles -->
    <link rel="stylesheet" href="public/assets/css/user-account-modal.css" type="text/css">
    
    <!-- Favorites Modal Styles -->
    <link rel="stylesheet" href="public/assets/css/favorites-modal.css" type="text/css">
    
    <!-- Dark Mode Styles -->
    <link rel="stylesheet" href="public/assets/css/dark-mode.css" type="text/css">
    
    <!-- ✅ FIX: Eliminar barra blanca al lado del scrollbar -->
    <link rel="stylesheet" href="public/assets/css/fix-white-bar.css?v=1.0" type="text/css">
    
    <!-- Modern Styles -->
    <link rel="stylesheet" href="public/assets/css/modals-animations.css?v=<?= time() ?>">
    <link rel="stylesheet" href="public/assets/css/notifications-modal.css">
    
    <!-- Header Fix - DEBE IR AL FINAL -->
    <link rel="stylesheet" href="public/assets/css/shop/shop-header-fix.css?v=<?= time() ?>">
    
    <!-- Global Responsive Styles - TODO EL PROYECTO -->
    
    <style>
        /* ============================================
           FONDO DEL BODY
           ============================================ */
        body {
            background-color: #f8f5f2 !important;
        }
        
        /* Dark mode */
        body.dark-mode {
            background-color: #1a1a1a !important;
        }
        
        /* ============================================
           ESTILOS ESPECÍFICOS DEL CARRITO
           Los estilos del header responsive están en: header-responsive.css
           ============================================ */
        
        /* ============================================
           ESTILOS DE TABLA DE CARRITO - DESKTOP
           ============================================ */
        
        /* Ocultar tabla por defecto en móvil */
        .shop__cart__table {
            display: none;
        }
        
        /* Mostrar solo en desktop */
        @media (min-width: 992px) {
            .shop__cart__table {
                display: block;
                background: white;
                margin-bottom: 40px;
                width: 100%;
                border-radius: 16px;
                padding: 20px;
                box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
                overflow: visible !important;
            }
        }
        
        .shop__cart__table table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 12px;
            min-width: auto !important;
            table-layout: fixed;
        }
        
        .shop__cart__table thead th {
            padding: 12px 6px;
            background: #f8f9fa;
            color: #666;
            font-weight: 600;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border: none;
        }
        
        .shop__cart__table thead th:first-child {
            border-radius: 8px 0 0 8px;
            padding-left: 12px;
            width: 35%;
        }
        
        .shop__cart__table thead th:nth-child(2) {
            width: 16%;
        }
        
        .shop__cart__table thead th:nth-child(3) {
            width: 24%;
        }
        
        .shop__cart__table thead th:nth-child(4) {
            width: 16%;
        }
        
        .shop__cart__table thead th:last-child {
            border-radius: 0 8px 8px 0;
            padding-right: 12px;
            width: 7%;
        }
        
        .shop__cart__table tbody tr {
            background: white;
            transition: all 0.3s ease;
        }
        
        .shop__cart__table tbody tr:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .shop__cart__table tbody td {
            padding: 12px 6px;
            vertical-align: middle;
            background: white;
            border-top: 1px solid #f1f1f1;
            border-bottom: 1px solid #f1f1f1;
            overflow: hidden;
        }
        
        .shop__cart__table tbody td:first-child {
            border-left: 1px solid #f1f1f1;
            border-radius: 10px 0 0 10px;
            padding-left: 12px;
        }
        
        .shop__cart__table tbody td:last-child {
            border-right: 1px solid #f1f1f1;
            border-radius: 0 10px 10px 0;
            padding-right: 12px;
        }
        
        .cart__product__item {
            display: flex;
            align-items: center;
            gap: 8px;
            max-width: 100%;
            overflow: hidden;
        }
        
        .cart__product__item img {
            width: 55px;
            height: 55px;
            object-fit: cover;
            border-radius: 6px;
            flex-shrink: 0;
        }
        
        .cart__product__item__title {
            flex: 1;
            min-width: 0;
            overflow: hidden;
        }
        
        .cart__product__item__title h6 {
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 3px;
            color: #333;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            word-break: break-word;
            line-height: 1.3;
        }
        
        .cart__price,
        .cart__total {
            font-size: 12px;
            font-weight: 700;
            color: #333;
        }
        
        /* Ajustes para que todo quepa bien en desktop */
        @media (min-width: 992px) {
            .shop__cart__table {
                padding: 20px;
                overflow: visible !important;
            }
            
            .shop__cart__table table {
                min-width: auto !important;
                table-layout: fixed;
            }
            
            .shop__cart__table thead th {
                font-size: 10px;
                padding: 12px 8px;
            }
            
            .shop__cart__table tbody td {
                padding: 14px 8px;
            }
            
            .cart__product__item img {
                width: 60px;
                height: 60px;
            }
            
            .cart__product__item__title h6 {
                font-size: 13px;
            }
            
            .cart__price,
            .cart__total {
                font-size: 13px;
            }
        }
        
        /* Pantallas más grandes - más espacio */
        @media (min-width: 1200px) {
            .shop__cart__table {
                padding: 25px;
            }
            
            .shop__cart__table thead th {
                padding: 14px 10px;
                font-size: 11px;
            }
            
            .shop__cart__table tbody td {
                padding: 16px 10px;
            }
            
            .cart__product__item {
                gap: 10px;
            }
            
            .cart__product__item img {
                width: 65px;
                height: 65px;
            }
            
            .cart__product__item__title h6 {
                font-size: 14px;
            }
            
            .cart__price,
            .cart__total {
                font-size: 14px;
            }
        }
        
        /* Pantallas extra grandes */
        @media (min-width: 1400px) {
            .shop__cart__table {
                padding: 30px;
            }
            
            .shop__cart__table thead th {
                padding: 15px 12px;
                font-size: 12px;
            }
            
            .shop__cart__table tbody td {
                padding: 18px 12px;
            }
            
            .cart__product__item {
                gap: 12px;
            }
            
            .cart__product__item img {
                width: 70px;
                height: 70px;
            }
            
            .cart__product__item__title h6 {
                font-size: 15px;
            }
            
            .cart__price,
            .cart__total {
                font-size: 15px;
            }
        }
        
        /* ============================================
           FOOTER STICKY MÓVIL
           ============================================ */
        
        /* Ocultar por defecto en desktop */
        .mobile-cart-footer {
            display: none;
        }

        /* Solo visible en móvil */
        @media (max-width: 991px) {
            .mobile-cart-footer {
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

            .mobile-cart-footer__content {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 12px 20px;
                gap: 15px;
                max-width: 1200px;
                margin: 0 auto;
            }

            .mobile-cart-footer__total {
                flex: 1;
                min-width: 0;
            }

            .mobile-cart-footer__label {
                font-size: 11px;
                color: #666;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-bottom: 2px;
                font-weight: 500;
            }

            .mobile-cart-footer__amount {
                font-size: 22px;
                font-weight: 800;
                color: #111;
                line-height: 1.2;
            }

            .mobile-cart-footer__savings {
                font-size: 11px;
                color: #28a745;
                font-weight: 600;
                margin-top: 2px;
            }

            .mobile-cart-footer__action {
                flex-shrink: 0;
            }

            .mobile-checkout-btn {
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
            }

            .mobile-checkout-btn:hover {
                background: linear-gradient(135deg, #a01010 0%, #800c0c 100%);
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(202, 21, 21, 0.4);
                color: white;
                text-decoration: none;
            }

            .mobile-checkout-btn i {
                font-size: 14px;
                transition: transform 0.3s ease;
            }

            .mobile-checkout-btn:hover i {
                transform: translateX(3px);
            }

            /* Agregar padding al body para compensar el footer sticky */
            body {
                padding-bottom: 80px;
            }

            /* Ocultar el sidebar de resumen en móvil ya que tenemos el footer */
            .cart-summary-sidebar {
                display: none !important;
            }
        }

        /* Móviles pequeños - ajustes */
        @media (max-width: 576px) {
            .mobile-cart-footer__content {
                padding: 10px 15px;
                gap: 12px;
            }

            .mobile-cart-footer__label {
                font-size: 10px;
            }

            .mobile-cart-footer__amount {
                font-size: 20px;
            }

            .mobile-cart-footer__savings {
                font-size: 10px;
            }

            .mobile-checkout-btn {
                padding: 12px 22px;
                font-size: 14px;
                gap: 8px;
            }

            .mobile-checkout-btn span {
                display: none;
            }

            .mobile-checkout-btn::after {
                content: 'Pagar';
            }

            body {
                padding-bottom: 70px;
            }
        }

        /* Pantallas muy pequeñas */
        @media (max-width: 400px) {
            .mobile-cart-footer__content {
                padding: 8px 12px;
                gap: 10px;
            }

            .mobile-cart-footer__amount {
                font-size: 18px;
            }

            .mobile-checkout-btn {
                padding: 10px 18px;
                font-size: 13px;
            }
        }

        /* ============================================
           ESTILOS DEL CARRITO
           ============================================ */

        .cart-empty {
            text-align: center;
            padding: 100px 20px;
        }
        .cart-empty i {
            font-size: 100px;
            color: #ddd;
            margin-bottom: 30px;
        }
        .cart-empty h3 {
            font-size: 28px;
            color: #333;
            margin-bottom: 15px;
        }
        .cart-empty p {
            font-size: 16px;
            color: #999;
            margin-bottom: 30px;
        }
        .btn-continue-shopping {
            display: inline-block;
            padding: 15px 40px;
            background: #ca1515;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-continue-shopping:hover {
            background: #a01010;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(202, 21, 21, 0.3);
            color: white;
        }

        /* ========================================
           SIDEBAR DEL CARRITO - ESTILO MODERNO
           ======================================== */
        .cart-summary-sidebar {
            background: #ffffff;
            border-radius: 20px;
            padding: 0;
            position: sticky;
            top: 90px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
        }
        
        .cart-summary-sidebar:hover {
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }

        /* Sección Código de Descuento */
        .discount-section {
            padding: 30px;
            border-bottom: 1px solid #f0f0f0;
            background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
        }
        
        .discount-section h6 {
            font-size: 15px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
            letter-spacing: 0.3px;
        }
        
        .discount-section h6 i {
            color: #667eea;
            font-size: 16px;
        }
        
        .discount-form {
            margin: 0;
        }
        
        .input-group {
            display: flex;
            gap: 10px;
        }
        
        .discount-input {
            flex: 1;
            padding: 14px 18px;
            border: 2px solid #e8e8e8;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: #ffffff;
            color: #2c3e50;
            font-weight: 500;
        }
        
        .discount-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            background: #ffffff;
        }
        
        .discount-input::placeholder {
            color: #95a5a6;
            font-weight: 400;
        }
        
        .btn-apply-discount {
            padding: 14px 28px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            white-space: nowrap;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn-apply-discount:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-apply-discount:active {
            transform: translateY(0);
        }

        #coupon-message {
            padding: 10px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            animation: slideInRight 0.3s ease-out;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        #coupon-message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        #coupon-message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Sección Totales */
        .cart-totals-section {
            padding: 30px;
            border-bottom: 1px solid #f0f0f0;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
        }
        
        .cart-totals-section h5 {
            font-size: 17px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e8e8e8;
            display: flex;
            align-items: center;
            gap: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .cart-totals-section h5 i {
            color: #667eea;
            font-size: 18px;
        }
        
        .totals-list {
            margin-bottom: 25px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 16px 0;
            font-size: 15px;
            color: #34495e;
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.2s ease;
        }
        
        .total-row:last-child {
            border-bottom: none;
        }
        
        .total-row.discount-row {
            padding: 14px 18px;
            margin: 10px -18px;
            border-radius: 10px;
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            display: none;
            font-weight: 600;
        }
        
        .total-row.discount-row.active {
            display: flex;
        }
        
        .total-row.total-final {
            font-size: 20px;
            font-weight: 700;
            color: #2c3e50;
            padding-top: 20px;
            margin-top: 15px;
            border-top: 3px solid #667eea;
            background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
            padding: 20px 15px 15px 15px;
            margin: 15px -15px 0 -15px;
            border-radius: 10px;
        }
        
        .total-row .amount {
            font-weight: 700;
            color: #2c3e50;
        }
        
        .total-row.total-final .amount {
            font-size: 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .btn-proceed-checkout {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            width: 100%;
            padding: 18px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 16px;
            text-transform: uppercase;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.35);
            letter-spacing: 1px;
            position: relative;
            overflow: hidden;
        }
        
        .btn-proceed-checkout::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }
        
        .btn-proceed-checkout:hover::before {
            left: 100%;
        }
        
        .btn-proceed-checkout:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(102, 126, 234, 0.45);
            color: #ffffff;
        }
        
        .btn-proceed-checkout:active {
            transform: translateY(-1px);
        }
        
        .btn-proceed-checkout i {
            font-size: 20px;
            transition: transform 0.3s ease;
        }
        
        .btn-proceed-checkout:hover i {
            transform: translateX(5px);
        }

        /* Sección de Confianza y Seguridad */
        .trust-section {
            padding: 30px;
            background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
            border-bottom-left-radius: 20px;
            border-bottom-right-radius: 20px;
        }
        
        .trust-badge {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 15px;
            padding: 16px 18px;
            background: #ffffff;
            border-radius: 12px;
            border: 2px solid #e8f5e9;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }
        
        .trust-badge:hover {
            transform: translateX(8px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.15);
            border-color: #c8e6c9;
        }
        
        .trust-badge:last-child {
            margin-bottom: 0;
        }
        
        .trust-badge:last-of-type {
            margin-bottom: 0;
        }
        
        .trust-badge i {
            font-size: 26px;
            color: #4caf50;
            min-width: 26px;
            margin-top: 2px;
            transition: all 0.3s ease;
        }
        
        .trust-badge:hover i {
            color: #667eea;
            transform: scale(1.15);
        }
        
        .trust-text {
            flex: 1;
        }
        
        .trust-text strong {
            display: block;
            font-size: 14px;
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 4px;
            letter-spacing: 0.3px;
        }
        
        .trust-text p {
            margin: 0;
            font-size: 12px;
            color: #7f8c8d;
            line-height: 1.5;
        }

        .secure-payments {
            margin-top: 25px;
            padding-top: 25px;
            border-top: 2px solid #e8e8e8;
        }
        
        .payment-icons {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .payment-icons img {
            transition: all 0.3s ease;
            filter: grayscale(0%);
        }
        
        .payment-icons img:hover {
            transform: scale(1.15);
            filter: brightness(1.1);
        }

        /* ========================================
           MODO OSCURO - SIDEBAR CARRITO
           ======================================== */
        
        /* Sidebar principal */
        body.dark-mode .cart-summary-sidebar {
            background: #2a2a2e;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
        }
        
        /* Sección de descuento */
        body.dark-mode .discount-section {
            background: linear-gradient(135deg, #35353a 0%, #2a2a2e 100%);
            border-bottom-color: #404040;
        }
        
        body.dark-mode .discount-section h6 {
            color: #e0e0e0;
        }
        
        body.dark-mode .discount-section h6 i {
            color: #c9a67c;
        }
        
        /* Input de descuento */
        body.dark-mode .discount-input {
            background: #35353a;
            border-color: #404040;
            color: #e0e0e0;
        }
        
        body.dark-mode .discount-input:focus {
            border-color: #c9a67c;
            background: #404040;
            box-shadow: 0 0 0 4px rgba(201, 166, 124, 0.15);
        }
        
        body.dark-mode .discount-input::placeholder {
            color: #6c757d;
        }
        
        /* Botón aplicar descuento */
        body.dark-mode .btn-apply-discount {
            background: #c9a67c !important;
            box-shadow: 0 4px 15px rgba(201, 166, 124, 0.4);
        }
        
        body.dark-mode .btn-apply-discount:hover {
            box-shadow: 0 6px 20px #c9a67c !important;
            background-color: #c9a67c !important;
        }
        
        /* Sección de totales */
        body.dark-mode .cart-totals-section {
            background: linear-gradient(135deg, #2a2a2e 0%, #35353a 100%);
            border-bottom-color: #404040;
        }
        
        body.dark-mode .cart-totals-section h5 {
            color: #e0e0e0;
            border-bottom-color: #404040;
        }
        
        body.dark-mode .cart-totals-section h5 i {
            color: #c9a67c;
        }
        
        /* Filas de totales */
        body.dark-mode .total-row {
            color: #b0b0b0;
            border-bottom-color: #404040;
        }
        
        body.dark-mode .total-row .amount {
            color: #e0e0e0;
        }
        
        /* Fila de descuento */
        body.dark-mode .total-row.discount-row {
            background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 100%);
            color: #a5d6a7;
        }
        
        /* Total final */
        body.dark-mode .total-row.total-final {
            background: linear-gradient(135deg, #35353a 0%, #2a2a2e 100%);
            border-top-color: #c9a67c;
            color: #e0e0e0;
        }
        
        body.dark-mode .total-row.total-final .amount {
            background: linear-gradient(135deg, #c9a67c 0%, #b8956b 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        /* Botón proceder al pago */
        body.dark-mode .btn-proceed-checkout {
            background: linear-gradient(135deg, #c9a67c 0%, #b8956b 100%);
            box-shadow: 0 8px 25px rgba(201, 166, 124, 0.4);
        }
        
        body.dark-mode .btn-proceed-checkout:hover {
            box-shadow: 0 12px 35px rgba(201, 166, 124, 0.5);
        }
        
        /* Sección de confianza */
        body.dark-mode .trust-section {
            background: linear-gradient(135deg, #35353a 0%, #2a2a2e 100%);
        }
        
        body.dark-mode .trust-badge {
            background: #35353a;
            border-color: #2e7d32;
        }
        
        body.dark-mode .trust-badge:hover {
            box-shadow: 0 6px 20px rgba(201, 166, 124, 0.2);
            border-color: #c9a67c;
        }
        
        body.dark-mode .trust-badge i {
            color: #66bb6a;
        }
        
        body.dark-mode .trust-badge:hover i {
            color: #c9a67c;
        }
        
        body.dark-mode .trust-text strong {
            color: #e0e0e0;
        }
        
        body.dark-mode .trust-text p {
            color: #9e9e9e;
        }
        
        /* Pagos seguros */
        body.dark-mode .secure-payments {
            border-top-color: #404040;
        }
        
        /* Mensaje de cupón */
        body.dark-mode #coupon-message.success {
            background: #1b5e20;
            color: #a5d6a7;
            border-color: #2e7d32;
        }
        
        body.dark-mode #coupon-message.error {
            background: #b71c1c;
            color: #ef9a9a;
            border-color: #c62828;
        }

        /* Vista móvil - oculta por defecto */
        .cart-mobile-view {
            display: none;
        }

        /* Estilos base para mejor responsividad */
        .container {
            padding-left: 15px;
            padding-right: 15px;
        }

        .shop-cart.spad {
            padding: 60px 0;
        }
        
        /* Desktop - espaciado optimizado */
        @media (min-width: 992px) {
            .shop-cart.spad {
                padding-top: 30px;
                padding-bottom: 60px;
            }
            
            /* REMOVIDO - ya está definido arriba con más espaciado */
            
            /* Reducir espacio entre breadcrumb y productos */
            .shop__cart__table {
                margin-top: 0;
                margin-bottom: 30px;
            }
            
            .cart-summary-sidebar {
                margin-top: 0;
            }
        }

        /* Responsive */
        @media (max-width: 991px) {
            .cart-summary-sidebar {
                position: relative;
                top: 0;
                margin-top: 30px;
            }
            
            .trust-section {
                padding: 20px;
            }
            
            .trust-badge {
                padding: 10px;
            }
        }

        /* Responsive para tablets */
        @media (max-width: 768px) {
            /* Ocultar tabla en tablets también */
            .shop__cart__table {
                display: none !important;
            }

            .shop__cart__table table {
                display: none;
            }
            
            /* Mostrar vista de tarjetas móvil */
            .cart-mobile-view {
                display: block !important;
            }

            .shop__cart__table thead th {
                font-size: 13px;
                padding: 12px 10px;
                background: #f8f8f8;
                position: sticky;
                top: 0;
                z-index: 10;
            }

            .shop__cart__table tbody td {
                padding: 15px 10px;
                vertical-align: middle;
            }

            /* Reducir tamaño de imagen de producto */
            .cart__product__item img {
                width: 70px !important;
                height: 70px !important;
            }

            .cart__product__item__title h6 {
                font-size: 13px;
            }

            .cart__product__item__title p {
                font-size: 11px;
            }

            .cart__product__item__title .rating i {
                font-size: 11px;
            }

            /* Ajustar botones de cantidad */
            .quantity-controls {
                gap: 4px;
            }

            .qty-btn {
                width: 28px;
                height: 28px;
                font-size: 14px;
            }

            .quantity-input {
                width: 40px;
                font-size: 13px;
            }

            /* Precios y totales más pequeños */
            .cart__price {
                font-size: 14px;
            }

            .cart__total {
                font-size: 15px;
                font-weight: 700;
            }

            /* Botón eliminar */
            .remove-cart-item {
                width: 28px;
                height: 28px;
                font-size: 13px;
            }

            /* Sidebar del carrito */
            .discount-section,
            .cart-totals-section {
                padding: 20px;
            }

            .discount-section h6,
            .cart-totals-section h5 {
                font-size: 15px;
            }

            /* Botón proceder al pago */
            .btn-proceed-checkout {
                font-size: 14px;
                padding: 14px 18px;
            }

            /* Trust badges */
            .trust-badge {
                margin-bottom: 12px;
                padding: 10px;
            }

            .trust-badge i {
                font-size: 20px;
            }

            .trust-text strong {
                font-size: 13px;
            }

            .trust-text p {
                font-size: 11px;
            }

            /* Iconos de pago */
            .payment-icons img {
                height: 22px;
            }
        }

        /* Responsive para móviles */
        @media (max-width: 576px) {
            /* Ocultar tabla y mostrar vista de tarjetas */
            .shop__cart__table {
                display: none !important;
            }
            
            .shop__cart__table table {
                display: none;
            }

            /* Vista de tarjetas para móvil */
            .cart-mobile-view {
                display: block !important;
            }

            .cart-mobile-item {
                background: white;
                border: 1px solid #e0e0e0;
                border-radius: 12px;
                padding: 15px;
                margin-bottom: 15px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            }

            .cart-mobile-item__header {
                display: flex;
                gap: 12px;
                margin-bottom: 12px;
                padding-bottom: 12px;
                border-bottom: 1px solid #f0f0f0;
                position: relative;
            }

            .cart-mobile-item__image {
                flex-shrink: 0;
            }

            .cart-mobile-item__image img {
                width: 80px;
                height: 80px;
                object-fit: cover;
                border-radius: 8px;
            }

            .cart-mobile-item__info {
                flex: 1;
                min-width: 0;
            }

            .cart-mobile-item__title {
                font-size: 14px;
                font-weight: 600;
                color: #333;
                margin-bottom: 4px;
                line-height: 1.3;
            }

            .cart-mobile-item__brand {
                font-size: 12px;
                color: #666;
                margin-bottom: 6px;
            }

            .cart-mobile-item__price {
                font-size: 16px;
                font-weight: 700;
                color: #111;
            }

            .cart-mobile-item__price-original {
                font-size: 13px;
                color: #999;
                text-decoration: line-through;
                margin-left: 6px;
            }

            .cart-mobile-item__discount-badge {
                display: inline-block;
                background: #ff4757;
                color: white;
                padding: 2px 6px;
                border-radius: 4px;
                font-size: 11px;
                font-weight: 600;
                margin-left: 6px;
            }

            .cart-mobile-item__body {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 12px;
            }

            .cart-mobile-item__quantity {
                display: flex;
                flex-direction: column;
                gap: 4px;
            }

            .cart-mobile-item__quantity-label {
                font-size: 10px;
                color: #666;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .cart-mobile-item__total {
                text-align: right;
                flex-shrink: 0;
            }

            .cart-mobile-item__total-label {
                font-size: 10px;
                color: #666;
                margin-bottom: 3px;
                text-transform: uppercase;
            }

            .cart-mobile-item__total-amount {
                font-size: 16px;
                font-weight: 700;
                color: #111;
            }

            .cart-mobile-item__remove {
                position: absolute;
                top: 0;
                right: 0;
            }

            .cart-mobile-item__remove-btn {
                background: transparent;
                color: #999;
                border: none;
                padding: 0;
                width: 32px;
                height: 32px;
                border-radius: 50%;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .cart-mobile-item__remove-btn:hover {
                background: #f8f8f8;
                color: #dc3545;
                transform: scale(1.1);
            }

            /* Ajustes del sidebar en móvil */
            .cart-summary-sidebar {
                margin-top: 20px;
                border-radius: 12px;
            }

            .discount-section {
                padding: 18px;
            }

            .discount-section h6 {
                font-size: 14px;
                margin-bottom: 12px;
            }

            .input-group {
                flex-direction: column;
                gap: 10px;
            }

            .discount-input {
                width: 100%;
                padding: 12px;
                font-size: 14px;
            }

            .btn-apply-discount {
                width: 100%;
                padding: 12px;
                font-size: 14px;
            }

            .cart-totals-section {
                padding: 18px;
            }

            .cart-totals-section h5 {
                font-size: 16px;
                margin-bottom: 15px;
            }

            .total-row {
                font-size: 14px;
                padding: 12px 0;
            }

            .total-row.total-final {
                font-size: 18px;
                padding-top: 15px;
                margin-top: 10px;
            }

            .total-row.total-final .amount {
                font-size: 20px;
            }

            .btn-proceed-checkout {
                font-size: 14px;
                padding: 14px 16px;
                border-radius: 30px;
            }

            .btn-proceed-checkout i {
                font-size: 16px;
            }

            /* Trust section en móvil */
            .trust-section {
                padding: 18px;
            }

            .trust-badge {
                flex-direction: row;
                gap: 10px;
                padding: 10px;
                margin-bottom: 10px;
            }

            .trust-badge i {
                font-size: 18px;
                min-width: 20px;
            }

            .trust-text strong {
                font-size: 12px;
            }

            .trust-text p {
                font-size: 10px;
            }

            .secure-payments {
                margin-top: 15px;
                padding-top: 15px;
            }

            .payment-icons {
                gap: 10px;
            }

            .payment-icons img {
                height: 20px;
            }

            /* Notificación de cupón en móvil */
            #coupon-message {
                top: 60px;
                right: 10px;
                left: 10px;
                min-width: auto;
                font-size: 12px;
                padding: 10px;
            }

            /* Botón continuar comprando - ya manejado en estilos principales */

            /* Carrito vacío */
            .cart-empty {
                padding: 30px 20px;
            }

            .cart-empty i {
                font-size: 60px;
            }

            .cart-empty h3 {
                font-size: 20px;
            }

            .cart-empty p {
                font-size: 13px;
            }

            .btn-continue-shopping {
                padding: 12px 30px;
                font-size: 14px;
            }

            /* Título de la sección */
            .shop-cart h2,
            .shop-cart h3 {
                font-size: 20px;
                margin-bottom: 20px;
            }

            /* Mejorar espaciado general */
            .shop-cart.spad {
                padding: 10px 0; /* Reducido aún más */
                padding-bottom: 100px; /* Espacio extra para el footer sticky móvil */
            }

            .container {
                padding-left: 15px;
                padding-right: 15px;
            }

            /* Mejorar tabla en móvil cuando está visible */
            .shop__cart__table {
                margin-bottom: 20px;
            }

            /* Ajustar mensajes de alerta */
            .alert {
                font-size: 13px;
                padding: 12px;
                margin-bottom: 15px;
            }
        }

        /* Ajustes extra pequeños */
        @media (max-width: 400px) {
            .cart-mobile-item {
                padding: 12px;
            }

            .cart-mobile-item__image img {
                width: 70px;
                height: 70px;
            }

            .cart-mobile-item__title {
                font-size: 13px;
            }

            .cart-mobile-item__price {
                font-size: 15px;
            }

            .cart-mobile-item__total-amount {
                font-size: 16px;
            }

            .quantity-controls {
                gap: 3px;
            }

            .qty-btn {
                width: 26px;
                height: 26px;
                font-size: 13px;
            }

            .quantity-input {
                width: 35px;
                font-size: 12px;
            }

            .payment-icons img {
                height: 18px;
            }

            /* Botón continuar comprando - ya manejado en estilos principales */

            /* Espaciado general */
            .shop-cart.spad {
                padding: 5px 0; /* Muy reducido */
                padding-bottom: 100px;
            }

            .mb-4 {
                margin-bottom: 20px !important;
            }
        }

        /* Landscape en móviles */
        @media (max-width: 767px) and (orientation: landscape) {
            .cart-mobile-item {
                padding: 12px;
            }

            .cart-summary-sidebar {
                margin-top: 15px;
            }

            .trust-badge {
                padding: 8px;
                margin-bottom: 8px;
            }

            .secure-payments {
                margin-top: 10px;
                padding-top: 10px;
            }
        }

        /* Mejoras para tabletas en modo portrait */
        @media (min-width: 577px) and (max-width: 991px) {
            .col-lg-9,
            .col-lg-3 {
                padding-left: 15px;
                padding-right: 15px;
            }

            .shop__cart__table {
                margin-bottom: 30px;
            }

            .cart-summary-sidebar {
                margin-top: 30px;
            }
        }
    
    </style>
</head>

<body>
    <?php include 'includes/offcanvas-menu.php'; ?>

    <!-- Header Section Begin -->
    <?php include 'includes/header-section.php'; ?>
    <!-- Header Section End -->

    <?php include 'includes/breadcrumb.php'; ?>

    <!-- Shop Cart Section Begin -->
    <section class="shop-cart spad">
        <div class="container" style="max-width: 1400px;">
            <?php if(!empty($cart_items)): ?>
            <div class="row">
                <!-- Lista de Productos (Izquierda) -->
                <div class="col-lg-9">
                    <div class="shop__cart__table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Precio</th>
                                    <th>Cantidad</th>
                                    <th>Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($cart_items as $item): 
                                    $precio_original = $item['precio_producto'];
                                    $tiene_descuento = $item['descuento_porcentaje_producto'] > 0;
                                    $precio_final = $precio_original;
                                    if($tiene_descuento) {
                                        $precio_final = $precio_original - ($precio_original * $item['descuento_porcentaje_producto'] / 100);
                                    }
                                    $subtotal = $precio_final * $item['cantidad_carrito'];
                                    $imagen_url = !empty($item['url_imagen_producto']) ? $item['url_imagen_producto'] : 'public/assets/img/default-product.jpg';
                                ?>
                                <tr data-cart-id="<?php echo $item['id_carrito']; ?>">
                                    <td class="cart__product__item">
                                        <img src="<?php echo htmlspecialchars($imagen_url); ?>" alt="<?php echo htmlspecialchars($item['nombre_producto']); ?>" style="width: 90px; height: 90px; object-fit: cover; border-radius: 8px;">
                                        <div class="cart__product__item__title">
                                            <h6><a href="product-details.php?id=<?php echo $item['id_producto']; ?>"><?php echo htmlspecialchars($item['nombre_producto']); ?></a></h6>
                                            <div class="rating">
                                                <i class="fa fa-star"></i>
                                                <i class="fa fa-star"></i>
                                                <i class="fa fa-star"></i>
                                                <i class="fa fa-star"></i>
                                                <i class="fa fa-star-o"></i>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="cart__price">
                                        $<?php echo number_format($precio_final, 2); ?>
                                        <?php if($tiene_descuento): ?>
                                            <br><small style="text-decoration: line-through; color: #999;">$<?php echo number_format($precio_original, 2); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="cart__quantity">
                                        <div class="pro-qty" style="display: flex; align-items: center; gap: 5px;">
                                            <button class="qty-btn qty-minus" data-id="<?php echo $item['id_carrito']; ?>" style="width: 30px; height: 30px; border: 1px solid #ddd; background: white; cursor: pointer; border-radius: 4px; font-size: 16px; display: flex; align-items: center; justify-content: center; transition: all 0.3s;">
                                                <i class="fa fa-minus"></i>
                                            </button>
                                            <input type="text" value="<?php echo $item['cantidad_carrito']; ?>" data-id="<?php echo $item['id_carrito']; ?>" data-max="<?php echo $item['stock_actual_producto']; ?>" class="quantity-input" readonly style="width: 50px; text-align: center; border: 1px solid #ddd; border-radius: 4px; height: 30px;">
                                            <button class="qty-btn qty-plus" data-id="<?php echo $item['id_carrito']; ?>" data-max="<?php echo $item['stock_actual_producto']; ?>" style="width: 30px; height: 30px; border: 1px solid #ddd; background: white; cursor: pointer; border-radius: 4px; font-size: 16px; display: flex; align-items: center; justify-content: center; transition: all 0.3s;">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="cart__total">$<?php echo number_format($subtotal, 2); ?></td>
                                    <td class="cart__close" style="text-align: center;">
                                        <button class="remove-cart-item" data-id="<?php echo $item['id_carrito']; ?>" style="background: #fee; border: none; width: 32px; height: 32px; border-radius: 6px; cursor: pointer; transition: all 0.3s; display: inline-flex; align-items: center; justify-content: center;">
                                            <i class="fa fa-trash" style="color: #dc3545; font-size: 14px;"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Vista Móvil: Tarjetas (oculta en desktop, visible en móvil) -->
                    <div class="cart-mobile-view" style="display: none;">
                        <?php foreach($cart_items as $item): 
                            $precio_original = $item['precio_producto'];
                            $tiene_descuento = $item['descuento_porcentaje_producto'] > 0;
                            $precio_final = $precio_original;
                            if($tiene_descuento) {
                                $precio_final = $precio_original - ($precio_original * $item['descuento_porcentaje_producto'] / 100);
                            }
                            $subtotal = $precio_final * $item['cantidad_carrito'];
                            $imagen_url = !empty($item['url_imagen_producto']) ? $item['url_imagen_producto'] : 'public/assets/img/default-product.jpg';
                        ?>
                        <div class="cart-mobile-item" data-cart-id="<?php echo $item['id_carrito']; ?>">
                            <div class="cart-mobile-item__header">
                                <div class="cart-mobile-item__image">
                                    <img src="<?php echo htmlspecialchars($imagen_url); ?>" alt="<?php echo htmlspecialchars($item['nombre_producto']); ?>">
                                </div>
                                <div class="cart-mobile-item__info">
                                    <div class="cart-mobile-item__title">
                                        <?php echo htmlspecialchars($item['nombre_producto']); ?>
                                    </div>
                                    <div class="cart-mobile-item__brand">
                                        <?php echo htmlspecialchars($item['nombre_marca'] ?? 'Sin marca'); ?>
                                    </div>
                                    <div class="cart-mobile-item__price">
                                        $<?php echo number_format($precio_final, 2); ?>
                                        <?php if($tiene_descuento): ?>
                                            <span class="cart-mobile-item__price-original">$<?php echo number_format($precio_original, 2); ?></span>
                                            <span class="cart-mobile-item__discount-badge">-<?php echo $item['descuento_porcentaje_producto']; ?>%</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="cart-mobile-item__remove">
                                    <button class="cart-mobile-item__remove-btn remove-cart-item" data-id="<?php echo $item['id_carrito']; ?>">
                                        <i class="fa fa-trash-o"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="cart-mobile-item__body">
                                <div class="cart-mobile-item__quantity">
                                    <span class="cart-mobile-item__quantity-label">Cantidad</span>
                                    <div class="quantity-controls" style="display: flex; align-items: center; gap: 5px;">
                                        <button class="qty-btn qty-minus" data-id="<?php echo $item['id_carrito']; ?>" style="width: 30px; height: 30px; border: 1px solid #ddd; background: white; cursor: pointer; border-radius: 4px; font-size: 16px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fa fa-minus"></i>
                                        </button>
                                        <input type="number" 
                                               class="quantity-input" 
                                               data-id="<?php echo $item['id_carrito']; ?>" 
                                               value="<?php echo $item['cantidad_carrito']; ?>" 
                                               readonly
                                               style="width: 50px; height: 30px; text-align: center; border: 1px solid #ddd; border-radius: 4px; font-weight: 600;">
                                        <button class="qty-btn qty-plus" data-id="<?php echo $item['id_carrito']; ?>" data-max="<?php echo $item['stock_actual_producto']; ?>" style="width: 30px; height: 30px; border: 1px solid #ddd; background: white; cursor: pointer; border-radius: 4px; font-size: 16px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="cart-mobile-item__total">
                                    <div class="cart-mobile-item__total-label">Total</div>
                                    <div class="cart-mobile-item__total-amount cart__total">
                                        $<?php echo number_format($subtotal, 2); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Resumen del Carrito (Derecha) -->
                <div class="col-lg-3">
                    <div class="cart-summary-sidebar">
                        
                        <!-- Código de descuento -->
                        <div class="discount-section">
                            <h6><i class="fa fa-tag"></i> Código de descuento</h6>
                            <form id="coupon-form" class="discount-form">
                                <div class="input-group">
                                    <input type="text" id="coupon-code" placeholder="Ingresa tu código" class="discount-input">
                                    <button type="submit" class="btn-apply-discount">Aplicar</button>
                                </div>
                            </form>
                            <!-- 
                            Cupones de prueba disponibles:
                            DESCUENTO10 = 10% de descuento
                            DESCUENTO20 = 20% de descuento
                            BIENVENIDO = 15% de descuento
                            VERANO25 = 25% de descuento
                            PROMO30 = 30% de descuento
                            -->
                        </div>

                    <!-- Mensaje de cupón fuera del sidebar -->
                    <div id="coupon-message" style="display: none;"></div>

                        <!-- Resumen del carrito -->
                        <div class="cart-totals-section">
                            <h5><i class="fa fa-shopping-cart"></i> Resumen del carrito</h5>
                            <div class="totals-list">
                                <div class="total-row">
                                    <span>Subtotal</span>
                                    <span class="amount" id="cart-subtotal">$<?php echo number_format($subtotal_sin_descuento, 2); ?></span>
                                </div>
                                <?php if($descuento_total > 0): ?>
                                <div class="total-row discount-row active" id="discount-row">
                                    <span style="color: rgba(0, 0, 0, 0.7);"><i class="fa fa-ticket"></i> Descuento</span>
                                    <span class="amount" style="color: rgba(0, 0, 0, 0.7);" id="cart-discount">-$<?php echo number_format($descuento_total, 2); ?></span>
                                </div>
                                <?php else: ?>
                                <div class="total-row discount-row" id="discount-row">
                                    <span style="color: rgba(0, 0, 0, 0.7);"><i class="fa fa-ticket"></i> Descuento</span>
                                    <span class="amount" style="color: rgba(0, 0, 0, 0.7);" id="cart-discount">-$0.00</span>
                                </div>
                                <?php endif; ?>
                                <div class="total-row total-final">
                                    <span>Total</span>
                                    <span class="amount" id="cart-total">$<?php echo number_format($total_carrito, 2); ?></span>
                                </div>
                            </div>
                            
                            <?php if($tiene_direccion_predeterminada): ?>
                            <a href="checkout.php" class="btn-proceed-checkout">
                                <i class="fa fa-lock"></i> Proceder al Pago
                            </a>
                            <?php else: ?>
                            <a href="profile.php?seccion=direcciones" class="btn-proceed-checkout" id="btn-add-address">
                                <i class="fa fa-arrow-right"></i> Continuar
                            </a>
                            <?php endif; ?>
                        </div>

                        <!-- Información de Seguridad y Confianza -->
                        <div class="trust-section">
                            <div class="trust-badge">
                                <i class="fa fa-shield"></i>
                                <div class="trust-text">
                                    <strong>Pago 100% Seguro</strong>
                                    <p>Protección SSL y encriptación de datos</p>
                                </div>
                            </div>
                            
                            <div class="trust-badge">
                                <i class="fa fa-truck"></i>
                                <div class="trust-text">
                                    <strong>Envío Garantizado</strong>
                                    <p>Seguimiento en tiempo real de tu pedido</p>
                                </div>
                            </div>
                            
                            <div class="trust-badge">
                                <i class="fa fa-undo"></i>
                                <div class="trust-text">
                                    <strong>Devolución Gratis</strong>
                                    <p>30 días para cambios y devoluciones</p>
                                </div>
                            </div>
                            
                            <div class="trust-badge">
                                <i class="fa fa-check-circle"></i>
                                <div class="trust-text">
                                    <strong>Compra Verificada</strong>
                                    <p>Productos 100% originales y garantizados</p>
                                </div>
                            </div>
                            
                            <div class="trust-badge">
                                <i class="fa fa-headphones"></i>
                                <div class="trust-text">
                                    <strong>Soporte 24/7</strong>
                                    <p>Atención al cliente siempre disponible</p>
                                </div>
                            </div>

            <div class="secure-payments">
                                <p style="font-size: 11px; color: rgba(0, 0, 0, 0.6); text-align: center; margin: 15px 0 8px;">
                                    <i class="fa fa-lock"></i> Métodos de pago seguros
                                </p>
                                <div class="payment-icons">
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5e/Visa_Inc._logo.svg/2560px-Visa_Inc._logo.svg.png" alt="Visa" style="height: 25px; object-fit: contain;" title="Visa">
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2a/Mastercard-logo.svg/1280px-Mastercard-logo.svg.png" alt="Mastercard" style="height: 25px; object-fit: contain;" title="Mastercard">
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/f/fa/American_Express_logo_%282018%29.svg/601px-American_Express_logo_%282018%29.svg.png" alt="American Express" style="height: 25px; object-fit: contain;" title="American Express">
                                    <img src="https://seeklogo.com/images/M/maestro-logo-9C54F0C006-seeklogo.com.png" alt="Maestro" style="height: 25px; object-fit: contain;" title="Maestro">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="shop__cart__table">
                        <div class="cart-empty">
                            <i class="fa fa-shopping-cart"></i>
                            <h3>Tu carrito está vacío</h3>
                            <p>¡Agrega productos para comenzar tu compra!</p>
                            <a href="shop.php" class="btn-continue-shopping">Explorar Productos</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Productos Relacionados -->
            <?php if(!empty($cart_items)): 
                // Obtener IDs de productos en el carrito y sus categorías
                $product_ids_in_cart = array_column($cart_items, 'id_producto');
                $category_ids = [];
                
                foreach($cart_items as $item) {
                    $cat_result = executeQuery("SELECT id_categoria FROM producto WHERE id_producto = ?", [$item['id_producto']]);
                    if($cat_result && !empty($cat_result)) {
                        $category_ids[] = $cat_result[0]['id_categoria'];
                    }
                }
                
                $category_ids = array_unique($category_ids);
                
                // Buscar productos relacionados (misma categoría, no en carrito, con stock)
                $related_products = [];
                if(!empty($category_ids)) {
                    $placeholders = implode(',', array_fill(0, count($category_ids), '?'));
                    $exclude_placeholders = implode(',', array_fill(0, count($product_ids_in_cart), '?'));
                    
                    $params = array_merge($category_ids, $product_ids_in_cart);
                    
                    $related_query = "
                        SELECT DISTINCT
                            p.id_producto,
                            p.nombre_producto,
                            p.precio_producto,
                            p.descuento_porcentaje_producto,
                            p.stock_actual_producto,
                            p.url_imagen_producto,
                            m.nombre_marca,
                            c.nombre_categoria,
                            COALESCE(AVG(r.calificacion), 0) as rating,
                            COALESCE(COUNT(DISTINCT r.id_resena), 0) as total_reviews
                        FROM producto p
                        LEFT JOIN marca m ON p.id_marca = m.id_marca
                        LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
                        LEFT JOIN resena r ON p.id_producto = r.id_producto AND r.aprobada = 1
                        WHERE p.id_categoria IN ($placeholders)
                        AND p.id_producto NOT IN ($exclude_placeholders)
                        AND p.status_producto = 1
                        AND p.stock_actual_producto > 0
                        GROUP BY p.id_producto
                        ORDER BY RAND()
                        LIMIT 8
                    ";
                    
                    $related_products = executeQuery($related_query, $params);
                }
            ?>
            
            <?php if(!empty($related_products)): ?>
            <div class="row" style="margin-top: 60px;">
                <div class="col-lg-12">
                    <div class="section-title">
                        <h4>Productos que tal vez quieras agregar</h4>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <?php foreach($related_products as $product): 
                    $precio_original = $product['precio_producto'];
                    $tiene_descuento = $product['descuento_porcentaje_producto'] > 0;
                    $precio_final = $precio_original;
                    $precio_anterior = null;
                    
                    if($tiene_descuento) {
                        $precio_anterior = $precio_original;
                        $precio_final = $precio_original * (1 - ($product['descuento_porcentaje_producto'] / 100));
                    }
                    
                    $imagen_url = !empty($product['url_imagen_producto']) ? $product['url_imagen_producto'] : 'public/assets/img/default-product.jpg';
                    $rating = round($product['rating'], 1);
                    $total_reviews = $product['total_reviews'];
                ?>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="product__item">
                        <?php if($tiene_descuento): ?>
                        <div class="product__item__pic set-bg" data-setbg="<?php echo htmlspecialchars($imagen_url); ?>" style="background-image: url('<?php echo htmlspecialchars($imagen_url); ?>');">
                            <div class="label sale">-<?php echo round($product['descuento_porcentaje_producto']); ?>%</div>
                            <ul class="product__hover">
                                <li><a href="<?php echo htmlspecialchars($imagen_url); ?>" class="image-popup"><span class="arrow_expand"></span></a></li>
                                <li><a href="#" class="add-to-favorites" data-id="<?php echo $product['id_producto']; ?>"><span class="icon_heart_alt"></span></a></li>
                                <li><a href="product-details.php?id=<?php echo $product['id_producto']; ?>"><span class="icon_bag_alt"></span></a></li>
                            </ul>
                        </div>
                        <?php else: ?>
                        <div class="product__item__pic set-bg" data-setbg="<?php echo htmlspecialchars($imagen_url); ?>" style="background-image: url('<?php echo htmlspecialchars($imagen_url); ?>');">
                            <ul class="product__hover">
                                <li><a href="<?php echo htmlspecialchars($imagen_url); ?>" class="image-popup"><span class="arrow_expand"></span></a></li>
                                <li><a href="#" class="add-to-favorites" data-id="<?php echo $product['id_producto']; ?>"><span class="icon_heart_alt"></span></a></li>
                                <li><a href="product-details.php?id=<?php echo $product['id_producto']; ?>"><span class="icon_bag_alt"></span></a></li>
                            </ul>
                        </div>
                        <?php endif; ?>
                        <div class="product__item__text">
                            <h6><a href="product-details.php?id=<?php echo $product['id_producto']; ?>"><?php echo htmlspecialchars($product['nombre_producto']); ?></a></h6>
                            <div class="rating">
                                <?php 
                                $full_stars = floor($rating);
                                $has_half = ($rating - $full_stars) >= 0.5;
                                
                                for($i = 0; $i < $full_stars; $i++): ?>
                                    <i class="fa fa-star"></i>
                                <?php endfor; ?>
                                
                                <?php if($has_half): ?>
                                    <i class="fa fa-star-half-o"></i>
                                <?php endif; ?>
                                
                                <?php for($i = 0; $i < (5 - $full_stars - ($has_half ? 1 : 0)); $i++): ?>
                                    <i class="fa fa-star-o"></i>
                                <?php endfor; ?>
                                
                                <span>(<?php echo $total_reviews; ?>)</span>
                            </div>
                            <div class="product__price">
                                $<?php echo number_format($precio_final, 2); ?>
                                <?php if($tiene_descuento): ?>
                                <span>$<?php echo number_format($precio_anterior, 2); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <?php endif; ?>
            <!-- Productos Relacionados End -->
        </div>
    </section>
    <!-- Shop Cart Section End -->

    <!-- Footer Sticky Móvil (Solo visible en móvil) -->
    <?php if(!empty($cart_items)): ?>
    <div class="mobile-cart-footer">
        <div class="mobile-cart-footer__content">
            <div class="mobile-cart-footer__total">
                <div class="mobile-cart-footer__label">Total a pagar</div>
                <div class="mobile-cart-footer__amount" id="mobile-footer-total">
                    $<?php echo number_format($total_carrito, 2); ?>
                </div>
                <?php if($descuento_total > 0): ?>
                <div class="mobile-cart-footer__savings">
                    Ahorras $<?php echo number_format($descuento_total, 2); ?>
                </div>
                <?php endif; ?>
            </div>
            <div class="mobile-cart-footer__action">
                <?php if($tiene_direccion_predeterminada): ?>
                <a href="checkout.php" class="mobile-checkout-btn">
                    <span>Proceder al Pago</span>
                    <i class="fa fa-arrow-right"></i>
                </a>
                <?php else: ?>
                <a href="profile.php?seccion=direcciones" class="mobile-checkout-btn" id="mobile-btn-add-address">
                    <span>Continuar</span>
                    <i class="fa fa-arrow-right"></i>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <!-- Footer Sticky Móvil End -->

    <!-- Footer Section Begin -->
    <?php include 'includes/footer.php'; ?>
    <!-- Footer Section End -->

    <?php 
    include 'includes/user-account-modal.php';
    include 'includes/favorites-modal.php';
    include 'includes/notifications-modal.php';
    ?>

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
            
            window.BASE_URL = baseUrlFromPHP;
            console.log('🌐 BASE_URL configurado:', window.BASE_URL);
        })();
    </script>
    <script src="public/assets/js/jquery-3.3.1.min.js"></script>
    
    <!-- Fetch API Handler Moderno - Reemplaza AJAX/jQuery -->
    <script src="public/assets/js/fetch-api-handler.js"></script>
    
    <script src="public/assets/js/bootstrap.min.js"></script>
    <script src="public/assets/js/jquery-ui.min.js"></script>
    <script src="public/assets/js/jquery.slicknav.js"></script>
    <script src="public/assets/js/main.js"></script>
    
    <!-- Header Handler - Actualización en tiempo real de contadores -->
    <script src="public/assets/js/header-handler.js?v=1.0"></script>
    
    <!-- Sistema Global de Contadores -->
    <script src="public/assets/js/global-counters.js"></script>
    
    <!-- Real-time Updates System - DEBE IR ANTES que cart-favorites-handler -->
    <script src="public/assets/js/real-time-updates.js?v=<?= time() ?>"></script>
    
    <script src="public/assets/js/user-account-modal.js"></script>
    <script src="public/assets/js/cart-favorites-handler.js"></script>
    
    <!-- Dark Mode Script -->
    <script src="public/assets/js/dark-mode.js"></script>
    
    <!-- Offcanvas Menu Global JS -->
    <script src="public/assets/js/offcanvas-menu.js"></script>
    
    <script>
    // Variable global para almacenar descuento de cupón aplicado
    let appliedDiscount = 0;
    let discountPercentage = 0;
    let couponCode = '';

    // Función para actualizar los totales en el sidebar
    function updateCartTotals() {
        let subtotalSinDescuento = 0;
        let descuentoProductos = 0;
        
        // Determinar qué vista usar (desktop o móvil)
        const isMobile = window.innerWidth <= 576;
        const itemsSelector = isMobile ? '.cart-mobile-item[data-cart-id]' : 'tr[data-cart-id]';
        
        // Calcular subtotal y descuentos de productos
        $(itemsSelector).each(function() {
            const item = $(this);
            
            if(isMobile) {
                // Vista móvil
                const priceText = item.find('.cart-mobile-item__price').text().trim().split('$')[1];
                const precioFinal = parseFloat(priceText.replace(/,/g, ''));
                
                const priceOriginalText = item.find('.cart-mobile-item__price-original').text().trim().replace('$', '').replace(/,/g, '');
                const precioOriginal = priceOriginalText ? parseFloat(priceOriginalText) : precioFinal;
                
                const cantidad = parseInt(item.find('.quantity-input').val());
                
                subtotalSinDescuento += precioOriginal * cantidad;
                if(precioOriginal > precioFinal) {
                    descuentoProductos += (precioOriginal - precioFinal) * cantidad;
                }
            } else {
                // Vista desktop (tabla)
                const priceCell = item.find('.cart__price');
                let precioOriginalText = priceCell.clone().children().remove().end().text().trim();
                precioOriginalText = precioOriginalText.replace('$', '').replace(/,/g, '').trim();
                const precioOriginal = parseFloat(precioOriginalText);
                
                const priceTachado = priceCell.find('small').text().replace('$', '').replace(/,/g, '').trim();
                const precioConDescuento = priceTachado ? parseFloat(precioOriginalText) : precioOriginal;
                
                const cantidad = parseInt(item.find('.quantity-input').val());
                
                if(priceTachado) {
                    const precioOriginalReal = parseFloat(priceTachado);
                    subtotalSinDescuento += precioOriginalReal * cantidad;
                    descuentoProductos += (precioOriginalReal - precioOriginal) * cantidad;
                } else {
                    subtotalSinDescuento += precioOriginal * cantidad;
                }
            }
        });
        
        // Calcular descuento adicional por cupón
        const descuentoCupon = subtotalSinDescuento * (discountPercentage / 100);
        const descuentoTotal = descuentoProductos + descuentoCupon;
        const total = subtotalSinDescuento - descuentoTotal;
        
        // Actualizar los valores en el DOM
        $('#cart-subtotal').text('$' + subtotalSinDescuento.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
        
        if(descuentoTotal > 0) {
            $('#discount-row').addClass('active').show();
            $('#cart-discount').text('-$' + descuentoTotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
        } else {
            $('#discount-row').removeClass('active').hide();
        }
        
        $('#cart-total').text('$' + total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
        
        // Actualizar también el footer móvil
        $('#mobile-footer-total').text('$' + total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
        
        // Actualizar o mostrar/ocultar el texto de ahorro en el footer móvil
        const savingsElement = $('.mobile-cart-footer__savings');
        if(descuentoTotal > 0) {
            if(savingsElement.length === 0) {
                // Crear el elemento si no existe
                $('.mobile-cart-footer__total').append(
                    '<div class="mobile-cart-footer__savings">Ahorras $' + 
                    descuentoTotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',') + 
                    '</div>'
                );
            } else {
                // Actualizar el elemento existente
                savingsElement.text('Ahorras $' + descuentoTotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
                savingsElement.show();
            }
        } else {
            savingsElement.hide();
        }
    }

    // Botones +/- para cantidad
    $(document).on('click', '.qty-plus', function() {
        const button = $(this);
        const cartId = button.data('id');
        const maxStock = button.data('max');
        const input = $('input[data-id="' + cartId + '"]');
        let currentQty = parseInt(input.val());
        
        if(currentQty < maxStock) {
            currentQty++;
            input.val(currentQty);
            updateCartQuantityAjax(cartId, currentQty);
        } else {
            alert('Stock máximo alcanzado: ' + maxStock);
        }
    });
    
    $(document).on('click', '.qty-minus', function() {
        const button = $(this);
        const cartId = button.data('id');
        const input = $('input[data-id="' + cartId + '"]');
        let currentQty = parseInt(input.val());
        
        if(currentQty > 1) {
            currentQty--;
            input.val(currentQty);
            updateCartQuantityAjax(cartId, currentQty);
        }
    });
    
    // Hover effects para botones
    $(document).on('mouseenter', '.qty-btn', function() {
        $(this).css({
            'background': '#f0f0f0',
            'border-color': '#999'
        });
    });
    
    $(document).on('mouseleave', '.qty-btn', function() {
        $(this).css({
            'background': 'white',
            'border-color': '#ddd'
        });
    });
    
    // Hover para botón eliminar
    $(document).on('mouseenter', '.remove-cart-item', function() {
        $(this).css({
            'background': '#dc3545',
            'transform': 'scale(1.1)'
        });
        $(this).find('i').css('color', 'white');
    });
    
    $(document).on('mouseleave', '.remove-cart-item', function() {
        $(this).css({
            'background': '#fee',
            'transform': 'scale(1)'
        });
        $(this).find('i').css('color', '#dc3545');
    });
    
    // Actualizar cantidad del carrito CON AJAX (solo actualiza totales)
    function updateCartQuantityAjax(cartId, quantity) {
        $.post('app/actions/update_cart_quantity.php', {
            id_carrito: cartId,
            cantidad: quantity
        }, function(response) {
            if(response.success) {
                // Actualizar el precio total de la fila en tabla desktop
                const row = $('tr[data-cart-id="' + cartId + '"]');
                const priceCell = row.find('.cart__price');
                
                // Obtener el precio (primera línea del texto, sin el precio tachado)
                let priceText = priceCell.clone().children().remove().end().text().trim();
                priceText = priceText.replace('$', '').replace(/,/g, '').trim();
                
                const price = parseFloat(priceText);
                
                if(!isNaN(price)) {
                    const newTotal = price * quantity;
                    
                    // Actualizar total de la fila en tabla
                    row.find('.cart__total').text('$' + newTotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
                    
                    // Actualizar total en vista móvil
                    const mobileItem = $('.cart-mobile-item[data-cart-id="' + cartId + '"]');
                    mobileItem.find('.cart-mobile-item__total-amount').text('$' + newTotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
                    
                    // Actualizar input de cantidad en vista móvil
                    mobileItem.find('.quantity-input').val(quantity);
                    
                    // Actualizar los totales del sidebar
                    updateCartTotals();
                } else {
                    console.error('Error al calcular precio:', priceText);
                    location.reload();
                }
            } else {
                alert(response.message);
                // Revertir cantidad si hay error
                location.reload();
            }
        }, 'json').fail(function() {
            alert('Error al actualizar el carrito');
            location.reload();
        });
    }
    
    // Eliminar item del carrito (usando Fetch API handler moderno)
    $(document).on('click', '.remove-cart-item', function() {
        const cartId = $(this).data('id');

        // Preferir la función global removeFromCart si está disponible
        if (typeof window.removeFromCart === 'function') {
            window.removeFromCart(cartId).then(success => {
                if (!success) {
                    // Si la eliminación falló, puedes mostrar un mensaje o fallback
                    // Aquí dejamos que removeFromCart muestre notificación cuando falle
                }
            }).catch(err => {
                console.error('Error calling removeFromCart:', err);
            });
        } else {
            // Fallback al método antiguo por compatibilidad
            $.post('app/actions/remove_from_cart.php', {
                id_carrito: cartId
            }, function(response) {
                if(response.success) {
                    // Eliminar fila de la tabla desktop con animación
                    $('tr[data-cart-id="' + cartId + '"]').fadeOut(400, function() {
                        $(this).remove();
                        // Actualizar totales
                        updateCartTotals();
                        // Verificar si quedó vacío
                        if($('tr[data-cart-id]').length === 0 && $('.cart-mobile-item[data-cart-id]').length === 0) {
                            location.reload();
                        }
                    });

                    // Eliminar item de la vista móvil con animación
                    $('.cart-mobile-item[data-cart-id="' + cartId + '"]').fadeOut(400, function() {
                        $(this).remove();
                        updateCartTotals();
                        if($('tr[data-cart-id]').length === 0 && $('.cart-mobile-item[data-cart-id]').length === 0) {
                            location.reload();
                        }
                    });
                } else {
                    alert(response.message);
                }
            }, 'json').fail(function() {
                alert('Error al eliminar el producto');
            });
        }
    });

    // Escuchar evento personalizado disparado por fetch-api-handler.js cuando se elimina un item
    document.addEventListener('cartItemRemoved', function(e) {
        const cartId = e && e.detail && e.detail.cartId;
        if (!cartId) return;

        // Animación: si la fila aún existe, hacer fadeOut antes de removerla
        const $row = $('tr[data-cart-id="' + cartId + '"]');
        if ($row.length) {
            $row.fadeOut(300, function() {
                $(this).remove();
                updateCartTotals();
                // Si quedó vacío, recargar para mostrar la vista vacía
                if($('tr[data-cart-id]').length === 0 && $('.cart-mobile-item[data-cart-id]').length === 0) {
                    location.reload();
                }
            });
        }

        const $mobile = $('.cart-mobile-item[data-cart-id="' + cartId + '"]');
        if ($mobile.length) {
            $mobile.fadeOut(300, function() {
                $(this).remove();
                updateCartTotals();
                if($('tr[data-cart-id]').length === 0 && $('.cart-mobile-item[data-cart-id]').length === 0) {
                    location.reload();
                }
            });
        }
    });

    // Formulario de código de descuento
    $('#coupon-form').on('submit', function(e) {
        e.preventDefault();
        const codigo = $('#coupon-code').val().trim().toUpperCase();
        const messageDiv = $('#coupon-message');
        
        if(!codigo) {
            messageDiv.removeClass('success').addClass('error')
                .html('<i class="fa fa-exclamation-circle"></i> Por favor ingresa un código de descuento')
                .fadeIn();
            
            // Ocultar mensaje después de 3 segundos
            setTimeout(function() {
                messageDiv.fadeOut();
            }, 3000);
            
            return;
        }
        
        // Deshabilitar botón mientras procesa
        const btn = $('.btn-apply-discount');
        const originalText = btn.text();
        btn.prop('disabled', true).text('Verificando...');
        
        // Simular validación de cupón (puedes implementar una llamada AJAX real)
        setTimeout(function() {
            // Cupones de ejemplo (puedes cambiar esto por una llamada AJAX a tu backend)
            const validCoupons = {
                'DESCUENTO10': 10,
                'DESCUENTO20': 20,
                'BIENVENIDO': 15,
                'VERANO25': 25,
                'PROMO30': 30
            };
            
            if(validCoupons[codigo]) {
                discountPercentage = validCoupons[codigo];
                couponCode = codigo;
                
                messageDiv.removeClass('error').addClass('success')
                    .html('<i class="fa fa-check-circle"></i> ¡Cupón aplicado! Descuento del ' + discountPercentage + '%')
                    .fadeIn();
                
                // Ocultar mensaje después de 4 segundos
                setTimeout(function() {
                    messageDiv.fadeOut();
                }, 4000);
                
                // Actualizar totales con el descuento
                updateCartTotals();
                
                // Deshabilitar el campo y botón
                $('#coupon-code').prop('disabled', true);
                btn.text('✓ Aplicado').css({
                    'background': 'rgba(40, 167, 69, 0.2)',
                    'border-color': '#28a745',
                    'color': '#28a745'
                });
                
            } else {
                messageDiv.removeClass('success').addClass('error')
                    .html('<i class="fa fa-times-circle"></i> Código de descuento inválido o expirado')
                    .fadeIn();
                
                // Ocultar mensaje de error después de 4 segundos
                setTimeout(function() {
                    messageDiv.fadeOut();
                }, 4000);
                
                btn.prop('disabled', false).text(originalText);
            }
        }, 800);
    });

    // Inicializar totales al cargar la página
    $(document).ready(function() {
        // Inicializar descuento desde PHP
        const descuentoInicial = <?php echo $descuento_total; ?>;
        
        // Mostrar u ocultar fila de descuento según si hay descuento
        if(descuentoInicial > 0) {
            $('#discount-row').addClass('active').show();
        } else {
            $('#discount-row').removeClass('active').hide();
        }
        
        updateCartTotals();

        // El manejo del offcanvas ahora está en offcanvas-menu.js
    });
    </script>
    
    <!-- Chatbot Widget -->
    <?php include 'includes/chatbot-widget.php'; ?>
</body>
</html>
