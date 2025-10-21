<?php
/**
 * PÁGINA DE PAGO - PASO 3
 * Permite al usuario seleccionar método de pago y procesar el pedido
 */

session_start();
require_once 'config/conexion.php';

$page_title = "Método de Pago";

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

// Verificar que existan datos del checkout en sesión
if (!isset($_SESSION['checkout_data'])) {
    header('Location: checkout.php');
    exit;
}

$checkout_data = $_SESSION['checkout_data'];
$cart_items = $checkout_data['cart_items'];
$subtotal = $checkout_data['subtotal'];
$costo_envio = $checkout_data['costo_envio'];
$total = $checkout_data['total'];

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
    <meta name="description" content="Método de Pago - SleppyStore">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - SleppyStore</title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Cookie&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Css Styles -->
    <link rel="stylesheet" href="public/assets/css/bootstrap.min.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/font-awesome.min.css" type="text/css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="public/assets/css/elegant-icons.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/jquery-ui.min.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/magnific-popup.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/owl.carousel.min.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/slicknav.min.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/style.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/header-standard.css?v=5.0" type="text/css">
    <link rel="stylesheet" href="public/assets/css/user-account-modal.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/favorites-modal.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/dark-mode.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/global-responsive.css?v=1.0" type="text/css">
    <link rel="stylesheet" href="public/assets/css/modals-animations.css?v=<?= time() ?>">
    <link rel="stylesheet" href="public/assets/css/notifications-modal.css">
    <link rel="stylesheet" href="public/assets/css/shop/shop-header-fix.css?v=<?= time() ?>">
    
    <style>
        body {
            background-color: #f8f5f2 !important;
            padding-top: 0;
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

        .btn-proceed-payment {
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
        .btn-proceed-payment:hover {
            background: linear-gradient(135deg, #5568d3 0%, #6a3f91 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        .btn-proceed-payment:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .order-summary {
            background: white;
            border: 1px solid #e1e1e1;
            border-radius: 10px;
            padding: 25px;
            position: sticky;
            top: 120px;
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

        .info-box {
            background: white;
            border: 1px solid #e1e1e1;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .info-box h6 {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 15px;
            color: #111;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
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
                        <a href="./checkout.php">Checkout</a>
                        <span>Pago</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Breadcrumb End -->

    <!-- Payment Section Begin -->
    <section class="checkout spad">
        <div class="container">
            <!-- Steps Progress -->
            <div class="checkout-steps">
                <div class="checkout-step completed">
                    <div class="step-number">1</div>
                    <div class="step-title">Carrito</div>
                </div>
                <div class="checkout-step completed">
                    <div class="step-number">2</div>
                    <div class="step-title">Información</div>
                </div>
                <div class="checkout-step active">
                    <div class="step-number">3</div>
                    <div class="step-title">Pago</div>
                </div>
                <div class="checkout-step">
                    <div class="step-number">4</div>
                    <div class="step-title">Confirmación</div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <!-- Información del Cliente (Read-only) -->
                    <div class="info-box">
                        <h6><i class="fa fa-user"></i> Información del Cliente</h6>
                        <div class="info-row">
                            <div class="info-label">Nombre:</div>
                            <div class="info-value"><?php echo htmlspecialchars($checkout_data['nombre']); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Email:</div>
                            <div class="info-value"><?php echo htmlspecialchars($checkout_data['email']); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Teléfono:</div>
                            <div class="info-value"><?php echo htmlspecialchars($checkout_data['telefono']); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label"><?php echo strlen($checkout_data['dni']) == 8 ? 'DNI:' : 'RUC:'; ?></div>
                            <div class="info-value"><?php echo htmlspecialchars($checkout_data['dni']); ?></div>
                        </div>
                    </div>

                    <!-- Dirección de Envío (Read-only) -->
                    <div class="info-box">
                        <h6><i class="fa fa-map-marker"></i> Dirección de Envío</h6>
                        <p style="margin: 0; color: #666;">
                            <?php echo htmlspecialchars($checkout_data['direccion']); ?>
                            <?php if(!empty($checkout_data['referencia'])): ?>
                                <br><small>Ref: <?php echo htmlspecialchars($checkout_data['referencia']); ?></small>
                            <?php endif; ?>
                            <br><?php echo htmlspecialchars($checkout_data['distrito'] . ', ' . $checkout_data['provincia'] . ', ' . $checkout_data['departamento']); ?>
                        </p>
                    </div>

                    <!-- Selección de Método de Pago -->
                    <form id="paymentForm" action="app/actions/process_payment.php" method="POST">
                        <div class="form-section">
                            <h5><i class="fa fa-credit-card"></i> Selecciona tu Método de Pago</h5>
                            
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

                        <button type="submit" class="btn-proceed-payment" id="btnProceedPayment">
                            <i class="fa fa-check"></i> Confirmar y Realizar Pedido
                        </button>
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
                            <div class="order-total-row total">
                                <span>Total:</span>
                                <span>$<?php echo number_format($total, 2); ?></span>
                            </div>
                        </div>

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
    <!-- Payment Section End -->

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
    <script src="public/assets/js/header-handler.js?v=1.0"></script>
    <script src="public/assets/js/global-counters.js"></script>
    <script src="public/assets/js/real-time-updates.js?v=<?= time() ?>"></script>
    <script src="public/assets/js/cart-favorites-handler.js"></script>
    <script src="public/assets/js/user-account-modal.js"></script>
    <script src="public/assets/js/dark-mode.js"></script>

    <script>
        function selectPayment(metodo) {
            // Remover selección anterior
            document.querySelectorAll('.payment-method').forEach(el => el.classList.remove('selected'));
            
            // Seleccionar nuevo
            document.querySelector(`#pago_${metodo}`).checked = true;
            document.querySelector(`#pago_${metodo}`).closest('.payment-method').classList.add('selected');
        }

        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btnSubmit = document.getElementById('btnProceedPayment');
            
            // Validar que se haya seleccionado método de pago
            if(!document.querySelector('input[name="metodo_pago"]:checked')) {
                alert('Por favor selecciona un método de pago');
                return;
            }
            
            // Deshabilitar botón y mostrar loading
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Procesando pedido...';
            
            // Enviar formulario con AJAX
            fetch('app/actions/process_payment.php', {
                method: 'POST',
                body: new FormData(this)
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    // Redirigir a página de confirmación
                    window.location.href = 'order-confirmation.php?order=' + data.order_id;
                } else {
                    alert(data.message || 'Error al procesar el pedido');
                    
                    // Restaurar botón
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = '<i class="fa fa-check"></i> Confirmar y Realizar Pedido';
                }
            })
            .catch(error => {
                alert('Error al procesar el pedido. Por favor intenta nuevamente.');
                
                // Restaurar botón
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="fa fa-check"></i> Confirmar y Realizar Pedido';
            });
        });
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
