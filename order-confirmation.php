<?php
/**
 * PÁGINA DE CONFIRMACIÓN DE PEDIDO
 * Muestra la confirmación del pedido realizado
 */

session_start();
require_once 'config/conexion.php';

$page_title = "Pedido Confirmado";

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
    header('Location: login.php');
    exit;
}

// Obtener ID del pedido
$id_pedido = isset($_GET['pedido']) ? (int)$_GET['pedido'] : 0;

if ($id_pedido <= 0) {
    header('Location: index.php');
    exit;
}

// Obtener datos del pedido
try {
    $pedido_resultado = executeQuery("
        SELECT * FROM pedido 
        WHERE id_pedido = ? AND id_usuario = ?
    ", [$id_pedido, $usuario_logueado['id_usuario']]);
    
    if (!$pedido_resultado || empty($pedido_resultado)) {
        header('Location: index.php');
        exit;
    }
    
    $pedido = $pedido_resultado[0];
    
    // Obtener detalles del pedido
    $detalles_resultado = executeQuery("
        SELECT dp.*, p.url_imagen_producto
        FROM detalle_pedido dp
        LEFT JOIN producto p ON dp.id_producto = p.id_producto
        WHERE dp.id_pedido = ?
    ", [$id_pedido]);
    
    $detalles = $detalles_resultado ? $detalles_resultado : [];
    
} catch(Exception $e) {
    error_log("Error al obtener pedido: " . $e->getMessage());
    header('Location: index.php');
    exit;
}

// Obtener contadores para el header
$cart_count = 0;
$favorites_count = 0;
$notifications_count = 0;
try {
    $cart_resultado = executeQuery("SELECT COUNT(*) as total FROM carrito WHERE id_usuario = ?", [$usuario_logueado['id_usuario']]);
    $cart_count = $cart_resultado && !empty($cart_resultado) ? (int)$cart_resultado[0]['total'] : 0;
    
    $favorites = executeQuery("SELECT COUNT(*) as total FROM favorito WHERE id_usuario = ?", [$usuario_logueado['id_usuario']]);
    $favorites_count = $favorites && !empty($favorites) ? (int)$favorites[0]['total'] : 0;
    
    $notifications = executeQuery("SELECT COUNT(*) as total FROM notificacion WHERE id_usuario = ? AND leida_notificacion = 0 AND estado_notificacion = 'activo'", [$usuario_logueado['id_usuario']]);
    $notifications_count = ($notifications && count($notifications) > 0) ? ($notifications[0]['total'] ?? 0) : 0;
} catch(Exception $e) {
    error_log("Error al obtener contadores: " . $e->getMessage());
}

// Obtener categorías para el menú
$categorias = [];
try {
    $categorias_resultado = executeQuery("SELECT id_categoria, nombre_categoria FROM categoria WHERE status_categoria = 1 ORDER BY id_categoria ASC LIMIT 5");
    $categorias = $categorias_resultado ? $categorias_resultado : [];
} catch(Exception $e) {
    error_log("Error al obtener categorías: " . $e->getMessage());
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

// Mapeo de estados del pedido
$estados_pedido = [
    'pendiente' => ['texto' => 'Pendiente de Pago', 'color' => '#ff9800', 'icono' => 'fa-clock-o'],
    'pagado' => ['texto' => 'Pagado', 'color' => '#2ecc71', 'icono' => 'fa-check-circle'],
    'procesando' => ['texto' => 'En Proceso', 'color' => '#3498db', 'icono' => 'fa-cog'],
    'enviado' => ['texto' => 'Enviado', 'color' => '#9b59b6', 'icono' => 'fa-truck'],
    'entregado' => ['texto' => 'Entregado', 'color' => '#27ae60', 'icono' => 'fa-check-square'],
    'cancelado' => ['texto' => 'Cancelado', 'color' => '#e74c3c', 'icono' => 'fa-times-circle']
];

$estado_actual = $estados_pedido[$pedido['estado_pedido']] ?? $estados_pedido['pendiente'];

// Mapeo de métodos de pago
$metodos_pago = [
    'tarjeta' => 'Tarjeta de Crédito/Débito',
    'transferencia' => 'Transferencia Bancaria',
    'yape' => 'Yape / Plin',
    'efectivo' => 'Efectivo Contra Entrega'
];

$metodo_pago_texto = $metodos_pago[$pedido['metodo_pago_pedido']] ?? $pedido['metodo_pago_pedido'];

// Mapeo de tipos de comprobante
$tipos_comprobante = [
    'boleta' => 'Boleta de Venta',
    'factura' => 'Factura Electrónica',
    'nota' => 'Nota de Venta'
];

$tipo_comprobante_texto = $tipos_comprobante[$pedido['tipo_comprobante_pedido']] ?? $pedido['tipo_comprobante_pedido'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="Confirmación de Pedido - SleppyStore">
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
    
    <!-- Global Responsive Styles - TODO EL PROYECTO -->
    <link rel="stylesheet" href="public/assets/css/global-responsive.css?v=1.0" type="text/css">
    
    <style>
        .confirmation-header {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            color: white;
            padding: 50px 0;
            text-align: center;
            margin-bottom: 40px;
        }
        .confirmation-header i {
            font-size: 80px;
            margin-bottom: 20px;
            animation: checkmark 0.6s ease-in-out;
        }
        @keyframes checkmark {
            0% { transform: scale(0); opacity: 0; }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); opacity: 1; }
        }
        .confirmation-header h2 {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .confirmation-header p {
            font-size: 16px;
            opacity: 0.9;
        }
        .order-number {
            background: white;
            color: #2ecc71;
            display: inline-block;
            padding: 10px 30px;
            border-radius: 50px;
            font-size: 18px;
            font-weight: 700;
            margin-top: 20px;
        }
        
        .info-box {
            background: white;
            border: 1px solid #e1e1e1;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
        }
        .info-box h5 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
            color: #111;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f5f5f5;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #666;
        }
        .info-value {
            color: #111;
            text-align: right;
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 14px;
        }
        
        .order-item {
            display: flex;
            gap: 15px;
            padding: 15px;
            background: #f8f8f8;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .order-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
        }
        .order-item-info {
            flex: 1;
        }
        .order-item-name {
            font-size: 16px;
            font-weight: 600;
            color: #111;
            margin-bottom: 8px;
        }
        .order-item-details {
            font-size: 14px;
            color: #666;
        }
        .order-item-price {
            text-align: right;
        }
        .order-item-unit-price {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }
        .order-item-total-price {
            font-size: 18px;
            font-weight: 700;
            color: #ca1515;
        }
        
        .total-box {
            background: #f8f8f8;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            font-size: 15px;
        }
        .total-row.final {
            border-top: 2px solid #ddd;
            margin-top: 10px;
            padding-top: 15px;
            font-size: 20px;
            font-weight: 700;
            color: #111;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        .btn-primary-custom {
            flex: 1;
            background: #ca1515;
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 16px;
            font-weight: 600;
            text-transform: uppercase;
            border-radius: 50px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        .btn-primary-custom:hover {
            background: #b01010;
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(202,21,21,0.3);
        }
        .btn-secondary-custom {
            flex: 1;
            background: white;
            color: #333;
            border: 2px solid #e1e1e1;
            padding: 15px 30px;
            font-size: 16px;
            font-weight: 600;
            text-transform: uppercase;
            border-radius: 50px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        .btn-secondary-custom:hover {
            background: #f8f8f8;
            border-color: #ca1515;
            color: #ca1515;
            text-decoration: none;
        }
        
        .payment-instructions {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }
        .payment-instructions h6 {
            font-weight: 700;
            color: #856404;
            margin-bottom: 15px;
        }
        .payment-instructions ul {
            margin: 0;
            padding-left: 20px;
        }
        .payment-instructions li {
            color: #856404;
            margin-bottom: 8px;
        }
    </style>
</head>

<body>
    <?php include 'includes/header-section.php'; ?>

    <!-- Confirmation Header -->
    <div class="confirmation-header">
        <div class="container">
            <i class="fa fa-check-circle"></i>
            <h2>¡Pedido Realizado con Éxito!</h2>
            <p>Gracias por tu compra. Hemos recibido tu pedido correctamente.</p>
            <div class="order-number">
                Pedido #<?php echo str_pad($pedido['id_pedido'], 6, '0', STR_PAD_LEFT); ?>
            </div>
        </div>
    </div>

    <!-- Order Details Section -->
    <section class="spad">
        <div class="container">
            <div class="row">
                <!-- Left Column - Order Info -->
                <div class="col-lg-8">
                    <!-- Estado del Pedido -->
                    <div class="info-box">
                        <h5><i class="fa fa-info-circle"></i> Estado del Pedido</h5>
                        <div style="text-align: center; padding: 20px 0;">
                            <div class="status-badge" style="background: <?php echo $estado_actual['color']; ?>; color: white;">
                                <i class="fa <?php echo $estado_actual['icono']; ?>"></i>
                                <?php echo $estado_actual['texto']; ?>
                            </div>
                            <p style="margin-top: 15px; color: #666;">
                                Fecha: <?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Productos del Pedido -->
                    <div class="info-box">
                        <h5><i class="fa fa-shopping-bag"></i> Productos</h5>
                        <?php foreach($detalles as $detalle): 
                            $precio_unitario = $detalle['precio_unitario_detalle'];
                            $precio_con_descuento = $precio_unitario;
                            if($detalle['descuento_porcentaje_detalle'] > 0) {
                                $precio_con_descuento = $precio_unitario - ($precio_unitario * $detalle['descuento_porcentaje_detalle'] / 100);
                            }
                        ?>
                        <div class="order-item">
                            <img src="<?php echo htmlspecialchars($detalle['url_imagen_producto'] ?? 'public/assets/img/product/no-image.jpg'); ?>" 
                                 alt="<?php echo htmlspecialchars($detalle['nombre_producto_detalle']); ?>">
                            <div class="order-item-info">
                                <div class="order-item-name"><?php echo htmlspecialchars($detalle['nombre_producto_detalle']); ?></div>
                                <div class="order-item-details">
                                    Cantidad: <?php echo $detalle['cantidad_detalle']; ?> unidad(es)
                                    <?php if($detalle['descuento_porcentaje_detalle'] > 0): ?>
                                        <br>Descuento: <?php echo $detalle['descuento_porcentaje_detalle']; ?>%
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="order-item-price">
                                <div class="order-item-unit-price">
                                    $<?php echo number_format($precio_con_descuento, 2); ?> c/u
                                </div>
                                <div class="order-item-total-price">
                                    $<?php echo number_format($detalle['subtotal_detalle'], 2); ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <!-- Totales -->
                        <div class="total-box">
                            <div class="total-row">
                                <span>Subtotal:</span>
                                <span>$<?php echo number_format($pedido['subtotal_pedido'], 2); ?></span>
                            </div>
                            <div class="total-row">
                                <span>Envío:</span>
                                <span><?php echo $pedido['costo_envio_pedido'] > 0 ? '$' . number_format($pedido['costo_envio_pedido'], 2) : 'GRATIS'; ?></span>
                            </div>
                            <div class="total-row final">
                                <span>Total:</span>
                                <span>$<?php echo number_format($pedido['total_pedido'], 2); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Instrucciones de Pago (si aplica) -->
                    <?php if($pedido['metodo_pago_pedido'] === 'transferencia'): ?>
                    <div class="payment-instructions">
                        <h6><i class="fa fa-exclamation-triangle"></i> Instrucciones para Transferencia Bancaria</h6>
                        <ul>
                            <li><strong>Banco:</strong> BCP - Banco de Crédito del Perú</li>
                            <li><strong>Cuenta Corriente:</strong> 123-456789-0-12</li>
                            <li><strong>CCI:</strong> 00212345678901234567</li>
                            <li><strong>Titular:</strong> SleppyStore E.I.R.L.</li>
                            <li><strong>Monto a pagar:</strong> $<?php echo number_format($pedido['total_pedido'], 2); ?></li>
                            <li><strong>Referencia:</strong> Pedido #<?php echo str_pad($pedido['id_pedido'], 6, '0', STR_PAD_LEFT); ?></li>
                        </ul>
                        <p style="margin: 15px 0 0; font-size: 13px;">
                            <i class="fa fa-info-circle"></i> Por favor envía el comprobante de pago a: <strong>pagos@sleppystore.com</strong>
                        </p>
                    </div>
                    <?php elseif($pedido['metodo_pago_pedido'] === 'yape'): ?>
                    <div class="payment-instructions">
                        <h6><i class="fa fa-mobile"></i> Instrucciones para Yape / Plin</h6>
                        <ul>
                            <li><strong>Número Yape:</strong> +51 999 888 777</li>
                            <li><strong>Número Plin:</strong> +51 999 888 777</li>
                            <li><strong>Nombre:</strong> SleppyStore</li>
                            <li><strong>Monto a pagar:</strong> $<?php echo number_format($pedido['total_pedido'], 2); ?></li>
                            <li><strong>Mensaje:</strong> Pedido #<?php echo str_pad($pedido['id_pedido'], 6, '0', STR_PAD_LEFT); ?></li>
                        </ul>
                        <p style="margin: 15px 0 0; font-size: 13px;">
                            <i class="fa fa-info-circle"></i> Por favor envía captura del comprobante a: <strong>pagos@sleppystore.com</strong>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Right Column - Customer Info -->
                <div class="col-lg-4">
                    <!-- Información del Cliente -->
                    <div class="info-box">
                        <h5><i class="fa fa-user"></i> Datos del Cliente</h5>
                        <div class="info-row">
                            <div class="info-label">Nombre:</div>
                            <div class="info-value"><?php echo htmlspecialchars($pedido['nombre_cliente_pedido']); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Email:</div>
                            <div class="info-value"><?php echo htmlspecialchars($pedido['email_cliente_pedido']); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Teléfono:</div>
                            <div class="info-value"><?php echo htmlspecialchars($pedido['telefono_cliente_pedido']); ?></div>
                        </div>
                        <?php if (!empty($pedido['dni_pedido'])): ?>
                        <div class="info-row">
                            <div class="info-label">DNI:</div>
                            <div class="info-value"><?php echo htmlspecialchars($pedido['dni_pedido']); ?></div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($pedido['ruc_pedido'])): ?>
                        <div class="info-row">
                            <div class="info-label">RUC:</div>
                            <div class="info-value"><?php echo htmlspecialchars($pedido['ruc_pedido']); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Dirección de Envío -->
                    <div class="info-box">
                        <h5><i class="fa fa-map-marker"></i> Dirección de Envío</h5>
                        <p style="margin: 0; color: #666; line-height: 1.6;">
                            <?php echo htmlspecialchars($pedido['direccion_envio_pedido']); ?>
                        </p>
                    </div>

                    <!-- Información de Facturación -->
                    <div class="info-box">
                        <h5><i class="fa fa-file-text-o"></i> Facturación</h5>
                        <div class="info-row">
                            <div class="info-label">Comprobante:</div>
                            <div class="info-value"><?php echo $tipo_comprobante_texto; ?></div>
                        </div>
                        <?php if($pedido['tipo_comprobante_pedido'] === 'factura'): ?>
                        <div class="info-row">
                            <div class="info-label">Razón Social:</div>
                            <div class="info-value"><?php echo htmlspecialchars($pedido['razon_social_pedido']); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">RUC:</div>
                            <div class="info-value"><?php echo htmlspecialchars($pedido['ruc_pedido']); ?></div>
                        </div>
                        <?php endif; ?>
                        <div class="info-row">
                            <div class="info-label">Método de Pago:</div>
                            <div class="info-value"><?php echo $metodo_pago_texto; ?></div>
                        </div>
                    </div>

                    <?php if($pedido['notas_pedido']): ?>
                    <!-- Notas del Pedido -->
                    <div class="info-box">
                        <h5><i class="fa fa-comment"></i> Notas</h5>
                        <p style="margin: 0; color: #666; font-style: italic;">
                            "<?php echo htmlspecialchars($pedido['notas_pedido']); ?>"
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="action-buttons">
                        <a href="index.php" class="btn-secondary-custom">
                            <i class="fa fa-home"></i> Volver al Inicio
                        </a>
                        <a href="shop.php" class="btn-primary-custom">
                            <i class="fa fa-shopping-bag"></i> Seguir Comprando
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Js Plugins -->
    <script src="public/assets/js/jquery-3.3.1.min.js"></script>
    <script src="public/assets/js/bootstrap.min.js"></script>
    <script src="public/assets/js/jquery.slicknav.js"></script>
    <script src="public/assets/js/main.js"></script>
    <script src="public/assets/js/cart-favorites-handler.js"></script>
    <script src="public/assets/js/user-account-modal.js"></script>

    <?php if($usuario_logueado): ?>
    <?php include 'includes/user-account-modal.php'; ?>
    <?php include 'includes/favorites-modal.php'; ?>
    <?php include 'includes/notifications-modal.php'; ?>
    <?php endif; ?>

</body>
</html>
