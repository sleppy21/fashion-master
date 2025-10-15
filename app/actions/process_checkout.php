<?php
/**
 * PROCESAR CHECKOUT - Backend para procesar el pedido
 * 1. Valida datos del formulario
 * 2. Verifica stock disponible
 * 3. Crea el pedido
 * 4. Crea los detalles del pedido
 * 5. Descuenta el stock
 * 6. Limpia el carrito
 * 7. Redirige a página de confirmación
 */

session_start();
require_once '../../config/conexion.php';

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}

// Verificar que sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../checkout.php');
    exit;
}

try {
    // ==========================
    // 1. OBTENER DATOS DEL FORMULARIO
    // ==========================
    $id_usuario = $_SESSION['user_id'];
    
    // Información del cliente
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $dni = trim($_POST['dni'] ?? '');
    
    // Dirección de envío
    $direccion = trim($_POST['direccion'] ?? '');
    $referencia = trim($_POST['referencia'] ?? '');
    $departamento = trim($_POST['departamento'] ?? '');
    $provincia = trim($_POST['provincia'] ?? '');
    $distrito = trim($_POST['distrito'] ?? '');
    
    // Tipo de comprobante
    $tipo_comprobante = $_POST['tipo_comprobante'] ?? '';
    $razon_social = trim($_POST['razon_social'] ?? '');
    // El RUC ya viene en el campo DNI cuando es factura
    
    // Método de pago
    $metodo_pago = $_POST['metodo_pago'] ?? '';
    
    // Notas
    $notas = trim($_POST['notas'] ?? '');
    
    // ==========================
    // 2. VALIDACIONES BÁSICAS
    // ==========================
    if (empty($nombre) || empty($email) || empty($telefono) || empty($dni)) {
        $_SESSION['checkout_error'] = 'Por favor completa todos los campos obligatorios de información del cliente.';
        header('Location: ../../checkout.php');
        exit;
    }
    
    if (empty($direccion) || empty($departamento) || empty($provincia) || empty($distrito)) {
        $_SESSION['checkout_error'] = 'Por favor completa todos los campos obligatorios de dirección de envío.';
        header('Location: ../../checkout.php');
        exit;
    }
    
    if (!in_array($tipo_comprobante, ['boleta', 'factura'])) {
        $_SESSION['checkout_error'] = 'Tipo de comprobante inválido. Solo se permite Boleta o Factura.';
        header('Location: ../../checkout.php');
        exit;
    }
    
    if (!in_array($metodo_pago, ['tarjeta', 'transferencia', 'yape', 'efectivo'])) {
        $_SESSION['checkout_error'] = 'Por favor selecciona un método de pago válido.';
        header('Location: ../../checkout.php');
        exit;
    }
    
    // Validar email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['checkout_error'] = 'El email ingresado no es válido.';
        header('Location: ../../checkout.php');
        exit;
    }
    
    // Validar DNI (8 dígitos) para boleta o RUC (11 dígitos) para factura
    if ($tipo_comprobante === 'boleta') {
        if (strlen($dni) !== 8 || !ctype_digit($dni)) {
            $_SESSION['checkout_error'] = 'Para Boleta, el DNI debe tener exactamente 8 dígitos numéricos.';
            header('Location: ../../checkout.php');
            exit;
        }
    } elseif ($tipo_comprobante === 'factura') {
        if (strlen($dni) !== 11 || !ctype_digit($dni)) {
            $_SESSION['checkout_error'] = 'Para Factura, el RUC debe tener exactamente 11 dígitos numéricos.';
            header('Location: ../../checkout.php');
            exit;
        }
        if (empty($razon_social)) {
            $_SESSION['checkout_error'] = 'Por favor completa la Razón Social para emitir factura.';
            header('Location: ../../checkout.php');
            exit;
        }
    }
    
    // ==========================
    // 3. OBTENER ITEMS DEL CARRITO
    // ==========================
    $cart_items = executeQuery("
        SELECT c.id_carrito, c.id_producto, c.cantidad_carrito,
               p.nombre_producto, p.precio_producto, p.descuento_porcentaje_producto,
               p.stock_actual_producto, p.status_producto
        FROM carrito c
        INNER JOIN producto p ON c.id_producto = p.id_producto
        WHERE c.id_usuario = ?
    ", [$id_usuario]);
    
    if (!$cart_items || empty($cart_items)) {
        $_SESSION['checkout_error'] = 'Tu carrito está vacío.';
        header('Location: ../../cart.php');
        exit;
    }
    
    // ==========================
    // 4. VALIDAR STOCK DISPONIBLE
    // ==========================
    $productos_sin_stock = [];
    foreach ($cart_items as $item) {
        if ($item['status_producto'] != 1) {
            $productos_sin_stock[] = $item['nombre_producto'] . ' (producto no disponible)';
        } elseif ($item['stock_actual_producto'] < $item['cantidad_carrito']) {
            $productos_sin_stock[] = $item['nombre_producto'] . ' (stock insuficiente: ' . $item['stock_actual_producto'] . ' disponibles)';
        }
    }
    
    if (!empty($productos_sin_stock)) {
        $_SESSION['checkout_error'] = 'Los siguientes productos no tienen stock suficiente: ' . implode(', ', $productos_sin_stock);
        header('Location: ../../cart.php');
        exit;
    }
    
    // ==========================
    // 5. CALCULAR TOTALES
    // ==========================
    $subtotal = 0;
    foreach ($cart_items as $item) {
        $precio = $item['precio_producto'];
        if ($item['descuento_porcentaje_producto'] > 0) {
            $precio = $precio - ($precio * $item['descuento_porcentaje_producto'] / 100);
        }
        $subtotal += $precio * $item['cantidad_carrito'];
    }
    
    $costo_envio = $subtotal >= 100 ? 0 : 15;
    $total = $subtotal + $costo_envio;
    
    // ==========================
    // 6. INICIAR TRANSACCIÓN
    // ==========================
    $conexion = getDB();
    $conexion->beginTransaction();
    
    try {
        // ==========================
        // 7. CREAR EL PEDIDO
        // ==========================
        $direccion_completa = $direccion . 
                            ($referencia ? ' (Ref: ' . $referencia . ')' : '') . 
                            ', ' . $distrito . ', ' . $provincia . ', ' . $departamento;
        
        $sql_pedido = "INSERT INTO pedido (
            id_usuario,
            nombre_cliente_pedido,
            email_cliente_pedido,
            telefono_cliente_pedido,
            dni_ruc_pedido,
            direccion_envio_pedido,
            departamento_pedido,
            provincia_pedido,
            distrito_pedido,
            tipo_comprobante_pedido,
            razon_social_pedido,
            ruc_pedido,
            metodo_pago_pedido,
            subtotal_pedido,
            costo_envio_pedido,
            total_pedido,
            notas_pedido,
            estado_pedido,
            fecha_pedido
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente', NOW())";
        
        $stmt_pedido = $conexion->prepare($sql_pedido);
        $stmt_pedido->execute([
            $id_usuario,
            $nombre,
            $email,
            $telefono,
            $dni, // DNI o RUC según el caso
            $direccion_completa,
            $departamento,
            $provincia,
            $distrito,
            $tipo_comprobante,
            $razon_social ?: null,
            $tipo_comprobante === 'factura' ? $dni : null, // RUC solo si es factura
            $metodo_pago,
            $subtotal,
            $costo_envio,
            $total,
            $notas ?: null
        ]);
        
        $id_pedido = $conexion->lastInsertId();
        
        // ==========================
        // 8. CREAR DETALLES DEL PEDIDO Y DESCONTAR STOCK
        // ==========================
        $sql_detalle = "INSERT INTO detalle_pedido (
            id_pedido,
            id_producto,
            nombre_producto_detalle,
            cantidad_detalle,
            precio_unitario_detalle,
            descuento_porcentaje_detalle,
            subtotal_detalle
        ) VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt_detalle = $conexion->prepare($sql_detalle);
        
        $sql_update_stock = "UPDATE producto 
                            SET stock_actual_producto = stock_actual_producto - ? 
                            WHERE id_producto = ? AND stock_actual_producto >= ?";
        
        $stmt_update_stock = $conexion->prepare($sql_update_stock);
        
        foreach ($cart_items as $item) {
            $precio_unitario = $item['precio_producto'];
            $descuento = $item['descuento_porcentaje_producto'];
            $cantidad = $item['cantidad_carrito'];
            
            $precio_con_descuento = $precio_unitario;
            if ($descuento > 0) {
                $precio_con_descuento = $precio_unitario - ($precio_unitario * $descuento / 100);
            }
            
            $subtotal_item = $precio_con_descuento * $cantidad;
            
            // Insertar detalle del pedido
            $stmt_detalle->execute([
                $id_pedido,
                $item['id_producto'],
                $item['nombre_producto'],
                $cantidad,
                $precio_unitario,
                $descuento,
                $subtotal_item
            ]);
            
            // Descontar stock
            $stmt_update_stock->execute([
                $cantidad,
                $item['id_producto'],
                $cantidad
            ]);
            
            // Verificar que se actualizó el stock
            if ($stmt_update_stock->rowCount() === 0) {
                throw new Exception("Error al actualizar stock del producto: " . $item['nombre_producto']);
            }
        }
        
        // ==========================
        // 9. LIMPIAR EL CARRITO
        // ==========================
        $sql_clear_cart = "DELETE FROM carrito WHERE id_usuario = ?";
        $stmt_clear_cart = $conexion->prepare($sql_clear_cart);
        $stmt_clear_cart->execute([$id_usuario]);
        
        // ==========================
        // 10. CONFIRMAR TRANSACCIÓN
        // ==========================
        $conexion->commit();
        
        // ==========================
        // 11. REDIRIGIR A PÁGINA DE CONFIRMACIÓN
        // ==========================
        $_SESSION['checkout_success'] = true;
        $_SESSION['pedido_id'] = $id_pedido;
        $_SESSION['pedido_total'] = $total;
        $_SESSION['metodo_pago'] = $metodo_pago;
        
        header('Location: ../../order-confirmation.php?pedido=' . $id_pedido);
        exit;
        
    } catch (Exception $e) {
        // Rollback en caso de error
        $conexion->rollBack();
        error_log("Error en transacción de checkout: " . $e->getMessage());
        $_SESSION['checkout_error'] = 'Ocurrió un error al procesar tu pedido. Por favor intenta nuevamente.';
        header('Location: ../../checkout.php');
        exit;
    }
    
} catch (Exception $e) {
    error_log("Error general en checkout: " . $e->getMessage());
    $_SESSION['checkout_error'] = 'Ocurrió un error inesperado. Por favor intenta nuevamente.';
    header('Location: ../../checkout.php');
    exit;
}
