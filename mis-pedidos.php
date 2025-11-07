<?php
/**
 * MIS PEDIDOS - HISTORIAL DE COMPRAS
 * Muestra todos los pedidos realizados por el usuario
 */

session_start();
require_once 'config/conexion.php';
require_once 'config/config.php'; // <-- Para BASE_URL global

$page_title = "Mis Compras";

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

// Filtro de estado
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : 'todos';

// Obtener pedidos del usuario
try {
    $sql = "SELECT * FROM pedido WHERE id_usuario = ?";
    $params = [$usuario_logueado['id_usuario']];
    
    if ($filtro_estado !== 'todos') {
        $sql .= " AND estado_pedido = ?";
        $params[] = $filtro_estado;
    }
    
    $sql .= " ORDER BY fecha_pedido DESC";
    
    $pedidos_resultado = executeQuery($sql, $params);
    $pedidos = $pedidos_resultado ? $pedidos_resultado : [];
    
} catch(Exception $e) {
    error_log("Error al obtener pedidos: " . $e->getMessage());
    $pedidos = [];
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

// Mapeo de estados
$estados_config = [
    'pendiente' => ['texto' => 'Pendiente', 'color' => '#ff9800', 'icono' => 'fa-clock-o'],
    'pagado' => ['texto' => 'Pagado', 'color' => '#2ecc71', 'icono' => 'fa-check-circle'],
    'procesando' => ['texto' => 'Procesando', 'color' => '#3498db', 'icono' => 'fa-cog'],
    'enviado' => ['texto' => 'Enviado', 'color' => '#9b59b6', 'icono' => 'fa-truck'],
    'entregado' => ['texto' => 'Entregado', 'color' => '#27ae60', 'icono' => 'fa-check-square'],
    'cancelado' => ['texto' => 'Cancelado', 'color' => '#e74c3c', 'icono' => 'fa-times-circle']
];

// Mapeo de métodos de pago
$metodos_pago = [
    'tarjeta' => 'Tarjeta',
    'transferencia' => 'Transferencia',
    'yape' => 'Yape/Plin',
    'efectivo' => 'Contra Entrega'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="Mis Compras - SleppyStore">
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
    <link rel="stylesheet" href="public/assets/css/global-responsive.css?v=1.0" type="text/css">
    
    <style>
        .orders-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 0 40px;
            margin-bottom: 40px;
        }
        .orders-header h1 {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .orders-header p {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .filters-bar {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .filter-tabs {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .filter-tab {
            padding: 10px 20px;
            border: 2px solid #e1e1e1;
            border-radius: 50px;
            background: white;
            color: #666;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            font-size: 14px;
        }
        .filter-tab:hover {
            border-color: #ca1515;
            color: #ca1515;
            text-decoration: none;
        }
        .filter-tab.active {
            background: #ca1515;
            border-color: #ca1515;
            color: white;
        }
        
        .order-card {
            background: white;
            border: 1px solid #e1e1e1;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        .order-card:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }
        .order-number {
            font-size: 20px;
            font-weight: 700;
            color: #111;
        }
        .order-date {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }
        .order-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 14px;
            color: white;
        }
        
        .order-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        .detail-item {
            display: flex;
            flex-direction: column;
        }
        .detail-label {
            font-size: 13px;
            color: #999;
            margin-bottom: 5px;
            font-weight: 500;
        }
        .detail-value {
            font-size: 15px;
            color: #111;
            font-weight: 600;
        }
        
        .order-summary {
            background: #f8f8f8;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
        }
        .summary-row.total {
            border-top: 2px solid #ddd;
            margin-top: 10px;
            padding-top: 12px;
            font-size: 18px;
            font-weight: 700;
            color: #ca1515;
        }
        
        .order-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        .btn-action {
            padding: 10px 25px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }
        .btn-primary {
            background: #ca1515;
            color: white;
        }
        .btn-primary:hover {
            background: #a01111;
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
        }
        .btn-secondary {
            background: white;
            color: #666;
            border: 2px solid #e1e1e1;
        }
        .btn-secondary:hover {
            border-color: #ca1515;
            color: #ca1515;
            text-decoration: none;
        }
        
        .empty-state {
            text-align: center;
            padding: 80px 20px;
        }
        .empty-state i {
            font-size: 80px;
            color: #ddd;
            margin-bottom: 20px;
        }
        .empty-state h3 {
            font-size: 24px;
            font-weight: 700;
            color: #666;
            margin-bottom: 15px;
        }
        .empty-state p {
            color: #999;
            margin-bottom: 30px;
        }
        
        @media (max-width: 768px) {
            .orders-header h1 {
                font-size: 28px;
            }
            .order-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            .order-details {
                grid-template-columns: 1fr;
            }
            .order-actions {
                flex-direction: column;
            }
            .btn-action {
                width: 100%;
                text-align: center;
            }
        }
        
        /* Modal de Detalles */
        .modal-order-details {
            max-width: 800px;
        }
        .product-item-modal {
            display: flex;
            gap: 15px;
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
        }
        .product-item-modal:last-child {
            border-bottom: none;
        }
        .product-img-modal {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }
        .product-info-modal {
            flex: 1;
        }
        .product-name-modal {
            font-weight: 600;
            color: #111;
            margin-bottom: 5px;
        }
        .product-meta-modal {
            font-size: 14px;
            color: #666;
        }
        .product-price-modal {
            font-weight: 700;
            color: #ca1515;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <!-- Page Preloder -->
    <div id="preloder">
        <div class="loader"></div>

    <script>
        // BASE_URL sin barra final para evitar duplicados
        window.BASE_URL = '<?= rtrim(BASE_URL, "/") ?>';
        if (window.location.protocol === 'https:' && window.BASE_URL.startsWith('http:')) {
            window.BASE_URL = window.BASE_URL.replace('http:', 'https:');
        }
    </script>
    </div>

    <!-- Header Section -->
    <?php include 'includes/header-section.php'; ?>

    <!-- Orders Header -->
    <div class="orders-header">
        <div class="container">
            <h1><i class="fa fa-shopping-bag"></i> Mis Compras</h1>
            <p>Gestiona y revisa el estado de todos tus pedidos</p>
        </div>
    </div>

    <!-- Orders Section -->
    <section class="spad">
        <div class="container">
            
            <!-- Filtros -->
            <div class="filters-bar">
                <div class="filter-tabs">
                    <a href="mis-pedidos.php?estado=todos" class="filter-tab <?php echo $filtro_estado === 'todos' ? 'active' : ''; ?>">
                        <i class="fa fa-list"></i> Todos
                    </a>
                    <a href="mis-pedidos.php?estado=pendiente" class="filter-tab <?php echo $filtro_estado === 'pendiente' ? 'active' : ''; ?>">
                        <i class="fa fa-clock-o"></i> Pendientes
                    </a>
                    <a href="mis-pedidos.php?estado=pagado" class="filter-tab <?php echo $filtro_estado === 'pagado' ? 'active' : ''; ?>">
                        <i class="fa fa-check-circle"></i> Pagados
                    </a>
                    <a href="mis-pedidos.php?estado=procesando" class="filter-tab <?php echo $filtro_estado === 'procesando' ? 'active' : ''; ?>">
                        <i class="fa fa-cog"></i> Procesando
                    </a>
                    <a href="mis-pedidos.php?estado=enviado" class="filter-tab <?php echo $filtro_estado === 'enviado' ? 'active' : ''; ?>">
                        <i class="fa fa-truck"></i> Enviados
                    </a>
                    <a href="mis-pedidos.php?estado=entregado" class="filter-tab <?php echo $filtro_estado === 'entregado' ? 'active' : ''; ?>">
                        <i class="fa fa-check-square"></i> Entregados
                    </a>
                </div>
            </div>

            <!-- Lista de Pedidos -->
            <div class="orders-list">
                <?php if (empty($pedidos)): ?>
                    <div class="empty-state">
                        <i class="fa fa-shopping-bag"></i>
                        <h3>No tienes pedidos<?php echo $filtro_estado !== 'todos' ? ' en este estado' : ''; ?></h3>
                        <p>Explora nuestra tienda y realiza tu primera compra</p>
                        <a href="shop.php" class="btn-action btn-primary">
                            <i class="fa fa-shopping-cart"></i> Ir a la Tienda
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach($pedidos as $pedido): 
                        $estado = $estados_config[$pedido['estado_pedido']] ?? $estados_config['pendiente'];
                        
                        // Obtener productos del pedido
                        $detalles = executeQuery("
                            SELECT dp.*, p.url_imagen_producto, p.nombre_producto
                            FROM detalle_pedido dp
                            LEFT JOIN producto p ON dp.id_producto = p.id_producto
                            WHERE dp.id_pedido = ?
                        ", [$pedido['id_pedido']]);
                        
                        $total_productos = 0;
                        if($detalles) {
                            foreach($detalles as $detalle) {
                                $total_productos += $detalle['cantidad_detalle'];
                            }
                        }
                    ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div>
                                <div class="order-number">
                                    <i class="fa fa-hashtag"></i> Pedido #<?php echo str_pad($pedido['id_pedido'], 6, '0', STR_PAD_LEFT); ?>
                                </div>
                                <div class="order-date">
                                    <i class="fa fa-calendar"></i> <?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?>
                                </div>
                            </div>
                            <div>
                                <span class="order-status" style="background: <?php echo $estado['color']; ?>;">
                                    <i class="fa <?php echo $estado['icono']; ?>"></i>
                                    <?php echo $estado['texto']; ?>
                                </span>
                            </div>
                        </div>

                        <div class="order-details">
                            <div class="detail-item">
                                <span class="detail-label">Productos</span>
                                <span class="detail-value"><?php echo $total_productos; ?> <?php echo $total_productos === 1 ? 'artículo' : 'artículos'; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Método de Pago</span>
                                <span class="detail-value"><?php echo $metodos_pago[$pedido['metodo_pago_pedido']] ?? 'N/A'; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Dirección</span>
                                <span class="detail-value"><?php echo htmlspecialchars($pedido['distrito_pedido']); ?>, <?php echo htmlspecialchars($pedido['provincia_pedido']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Total</span>
                                <span class="detail-value" style="color: #ca1515; font-size: 18px;">S/ <?php echo number_format($pedido['total_pedido'], 2); ?></span>
                            </div>
                        </div>

                        <div class="order-actions">
                            <button class="btn-action btn-secondary" onclick="verDetalles(<?php echo $pedido['id_pedido']; ?>)">
                                <i class="fa fa-eye"></i> Ver Detalles
                            </button>
                            <a href="order-confirmation.php?order=<?php echo $pedido['id_pedido']; ?>" class="btn-action btn-primary">
                                <i class="fa fa-file-text-o"></i> Ver Comprobante
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Modal de Detalles del Pedido -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-order-details" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-shopping-bag"></i> Detalles del Pedido</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="orderDetailsContent">
                    <div style="text-align: center; padding: 40px;">
                        <i class="fa fa-spinner fa-spin" style="font-size: 40px; color: #ca1515;"></i>
                        <p style="margin-top: 20px; color: #666;">Cargando detalles...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Section -->
    <?php include 'includes/footer-section.php'; ?>

    <!-- Modales ya incluidos en header-section.php - NO duplicar -->

    <!-- Js Plugins -->
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

    <script>
        function verDetalles(idPedido) {
            if (window.OffcanvasManager) {
                window.OffcanvasManager.openModal('orderDetailsModal');
            }
            $('#orderDetailsModal').modal('show');
            
            // Cargar detalles vía AJAX
            $.ajax({
                url: 'app/actions/get_order_details.php',
                method: 'POST',
                data: { id_pedido: idPedido },
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        mostrarDetalles(response.data);
                    } else {
                        $('#orderDetailsContent').html(`
                            <div style="text-align: center; padding: 40px;">
                                <i class="fa fa-exclamation-triangle" style="font-size: 50px; color: #ff9800;"></i>
                                <p style="margin-top: 20px; color: #666;">${response.message || 'Error al cargar detalles'}</p>
                            </div>
                        `);
                    }
                },
                error: function() {
                    $('#orderDetailsContent').html(`
                        <div style="text-align: center; padding: 40px;">
                            <i class="fa fa-times-circle" style="font-size: 50px; color: #e74c3c;"></i>
                            <p style="margin-top: 20px; color: #666;">Error de conexión. Intenta nuevamente.</p>
                        </div>
                    `);
                }
            });
        }
        
        function mostrarDetalles(data) {
            let html = `
                <div style="margin-bottom: 20px;">
                    <h6 style="font-weight: 700; margin-bottom: 15px; color: #111;">
                        <i class="fa fa-info-circle"></i> Información del Pedido
                    </h6>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; background: #f8f8f8; padding: 15px; border-radius: 8px;">
                        <div>
                            <small style="color: #999;">Número de Pedido</small>
                            <div style="font-weight: 600;">#${String(data.pedido.id_pedido).padStart(6, '0')}</div>
                        </div>
                        <div>
                            <small style="color: #999;">Estado</small>
                            <div style="font-weight: 600; color: ${getEstadoColor(data.pedido.estado_pedido)};">
                                ${getEstadoTexto(data.pedido.estado_pedido)}
                            </div>
                        </div>
                        <div>
                            <small style="color: #999;">Fecha</small>
                            <div style="font-weight: 600;">${formatDate(data.pedido.fecha_pedido)}</div>
                        </div>
                        <div>
                            <small style="color: #999;">Método de Pago</small>
                            <div style="font-weight: 600;">${getMetodoPago(data.pedido.metodo_pago_pedido)}</div>
                        </div>
                    </div>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <h6 style="font-weight: 700; margin-bottom: 15px; color: #111;">
                        <i class="fa fa-shopping-bag"></i> Productos
                    </h6>
            `;
            
            data.detalles.forEach(function(detalle) {
                html += `
                    <div class="product-item-modal">
                        <img src="${detalle.url_imagen_producto || 'public/assets/img/product/no-image.jpg'}" 
                             alt="${detalle.nombre_producto_detalle}" 
                             class="product-img-modal">
                        <div class="product-info-modal">
                            <div class="product-name-modal">${detalle.nombre_producto_detalle}</div>
                            <div class="product-meta-modal">
                                Cantidad: ${detalle.cantidad_detalle} × S/ ${parseFloat(detalle.precio_unitario_detalle).toFixed(2)}
                                ${detalle.descuento_porcentaje_detalle > 0 ? `<br>Descuento: ${detalle.descuento_porcentaje_detalle}%` : ''}
                            </div>
                        </div>
                        <div class="product-price-modal">
                            S/ ${parseFloat(detalle.subtotal_detalle).toFixed(2)}
                        </div>
                    </div>
                `;
            });
            
            html += `
                </div>
                
                <div style="background: #f8f8f8; padding: 20px; border-radius: 8px;">
                    <div style="display: flex; justify-content: space-between; padding: 8px 0;">
                        <span>Subtotal:</span>
                        <span>S/ ${parseFloat(data.pedido.subtotal_pedido).toFixed(2)}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 8px 0;">
                        <span>Envío:</span>
                        <span>${data.pedido.costo_envio_pedido > 0 ? 'S/ ' + parseFloat(data.pedido.costo_envio_pedido).toFixed(2) : 'GRATIS'}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 15px 0 0; margin-top: 10px; border-top: 2px solid #ddd; font-size: 18px; font-weight: 700; color: #ca1515;">
                        <span>TOTAL:</span>
                        <span>S/ ${parseFloat(data.pedido.total_pedido).toFixed(2)}</span>
                    </div>
                </div>
                
                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e1e1e1;">
                    <h6 style="font-weight: 700; margin-bottom: 10px; color: #111;">
                        <i class="fa fa-map-marker"></i> Dirección de Entrega
                    </h6>
                    <p style="margin: 0; color: #666; line-height: 1.6;">
                        ${data.pedido.direccion_envio_pedido}
                    </p>
                </div>
            `;
            
            $('#orderDetailsContent').html(html);
        }
        
        function getEstadoColor(estado) {
            const colores = {
                'pendiente': '#ff9800',
                'pagado': '#2ecc71',
                'procesando': '#3498db',
                'enviado': '#9b59b6',
                'entregado': '#27ae60',
                'cancelado': '#e74c3c'
            };
            return colores[estado] || '#999';
        }
        
        function getEstadoTexto(estado) {
            const textos = {
                'pendiente': 'Pendiente',
                'pagado': 'Pagado',
                'procesando': 'Procesando',
                'enviado': 'Enviado',
                'entregado': 'Entregado',
                'cancelado': 'Cancelado'
            };
            return textos[estado] || estado;
        }
        
        function getMetodoPago(metodo) {
            const metodos = {
                'tarjeta': 'Tarjeta de Crédito/Débito',
                'transferencia': 'Transferencia Bancaria',
                'yape': 'Yape / Plin',
                'efectivo': 'Contra Entrega'
            };
            return metodos[metodo] || metodo;
        }
        
        function formatDate(dateString) {
            const date = new Date(dateString);
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            return `${day}/${month}/${year} ${hours}:${minutes}`;
        }
    </script>
    
    <!-- Chatbot Widget -->
    <?php include 'includes/chatbot-widget.php'; ?>
</body>
</html>
