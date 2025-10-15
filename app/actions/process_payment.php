<?php
/**
 * PROCESAR PAGO - Backend para procesar el pedido final
 * 1. Obtiene datos de sesión
 * 2. Valida método de pago
 * 3. Crea el pedido
 * 4. Crea los detalles del pedido
 * 5. Descuenta el stock
 * 6. Limpia el carrito
 * 7. Crea notificación
 * 8. Redirige a página de confirmación
 */

session_start();
require_once '../../config/conexion.php';

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Debes iniciar sesión para continuar.'
    ]);
    exit;
}

// Verificar que sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido.'
    ]);
    exit;
}

// Verificar que existan datos del checkout en sesión
if (!isset($_SESSION['checkout_data'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'No hay datos de checkout. Por favor inicia el proceso nuevamente.'
    ]);
    exit;
}

try {
    // ==========================
    // 1. OBTENER DATOS
    // ==========================
    $id_usuario = $_SESSION['user_id'];
    $checkout_data = $_SESSION['checkout_data'];
    $metodo_pago = $_POST['metodo_pago'] ?? '';
    
    // Validar método de pago
    if (!in_array($metodo_pago, ['tarjeta', 'transferencia', 'yape', 'efectivo'])) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Por favor selecciona un método de pago válido.'
        ]);
        exit;
    }
    
    // Extraer datos del checkout
    $nombre = $checkout_data['nombre'];
    $email = $checkout_data['email'];
    $telefono = $checkout_data['telefono'];
    $dni = $checkout_data['dni'];
    $direccion = $checkout_data['direccion'];
    $referencia = $checkout_data['referencia'];
    $departamento = $checkout_data['departamento'];
    $provincia = $checkout_data['provincia'];
    $distrito = $checkout_data['distrito'];
    $tipo_comprobante = $checkout_data['tipo_comprobante'];
    $razon_social = $checkout_data['razon_social'];
    $notas = $checkout_data['notas'];
    $cart_items = $checkout_data['cart_items'];
    $subtotal = $checkout_data['subtotal'];
    $costo_envio = $checkout_data['costo_envio'];
    $total = $checkout_data['total'];
    
    // ==========================
    // 2. VALIDAR STOCK NUEVAMENTE
    // ==========================
    $productos_sin_stock = [];
    foreach ($cart_items as $item) {
        // Revalidar stock actual en la base de datos
        $producto_actual = executeQuery("SELECT stock_actual_producto, status_producto FROM producto WHERE id_producto = ?", [$item['id_producto']]);
        
        if (!$producto_actual || empty($producto_actual)) {
            $productos_sin_stock[] = $item['nombre_producto'] . ' (producto no encontrado)';
        } elseif ($producto_actual[0]['status_producto'] != 1) {
            $productos_sin_stock[] = $item['nombre_producto'] . ' (producto no disponible)';
        } elseif ($producto_actual[0]['stock_actual_producto'] < $item['cantidad_carrito']) {
            $productos_sin_stock[] = $item['nombre_producto'] . ' (stock insuficiente: ' . $producto_actual[0]['stock_actual_producto'] . ' disponibles)';
        }
    }
    
    if (!empty($productos_sin_stock)) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Los siguientes productos no tienen stock suficiente: ' . implode(', ', $productos_sin_stock)
        ]);
        exit;
    }
    
    // ==========================
    // 3. INICIAR TRANSACCIÓN
    // ==========================
    $conexion = getDB();
    $conexion->beginTransaction();
    
    try {
        // ==========================
        // 4. CREAR EL PEDIDO
        // ==========================
        $direccion_completa = $direccion . 
                            ($referencia ? ' (Ref: ' . $referencia . ')' : '') . 
                            ', ' . $distrito . ', ' . $provincia . ', ' . $departamento;
        
        // Lógica inteligente: detectar si es DNI (8 dígitos) o RUC (11 dígitos)
        $dni_value = null;
        $ruc_value = null;
        
        if (strlen($dni) == 8) {
            $dni_value = $dni;
        } elseif (strlen($dni) == 11) {
            $ruc_value = $dni;
        }
        
        $sql_pedido = "INSERT INTO pedido (
            id_usuario,
            nombre_cliente_pedido,
            email_cliente_pedido,
            telefono_cliente_pedido,
            dni_pedido,
            ruc_pedido,
            direccion_envio_pedido,
            departamento_pedido,
            provincia_pedido,
            distrito_pedido,
            tipo_comprobante_pedido,
            razon_social_pedido,
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
            $dni_value,
            $ruc_value,
            $direccion_completa,
            $departamento,
            $provincia,
            $distrito,
            $tipo_comprobante,
            $razon_social ?: null,
            $metodo_pago,
            $subtotal,
            $costo_envio,
            $total,
            $notas ?: null
        ]);
        
        $id_pedido = $conexion->lastInsertId();
        
        // ==========================
        // 5. CREAR DETALLES DEL PEDIDO Y DESCONTAR STOCK
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
        // 6. LIMPIAR EL CARRITO
        // ==========================
        $sql_clear_cart = "DELETE FROM carrito WHERE id_usuario = ?";
        $stmt_clear_cart = $conexion->prepare($sql_clear_cart);
        $stmt_clear_cart->execute([$id_usuario]);
        
        // ==========================
        // 7. CREAR NOTIFICACIÓN
        // ==========================
        try {
            $titulo_notificacion = "¡Pedido Realizado! #" . str_pad($id_pedido, 6, '0', STR_PAD_LEFT);
            $mensaje_notificacion = "Tu pedido por $" . number_format($total, 2) . " ha sido procesado exitosamente. Te notificaremos cuando sea enviado.";
            
            $sql_notificacion = "INSERT INTO notificacion (
                id_usuario,
                tipo_notificacion,
                titulo_notificacion,
                mensaje_notificacion,
                id_referencia_notificacion,
                tipo_referencia_notificacion,
                leida_notificacion,
                estado_notificacion,
                fecha_creacion_notificacion
            ) VALUES (?, 'pedido', ?, ?, ?, 'pedido', 0, 'activo', NOW())";
            
            $stmt_notificacion = $conexion->prepare($sql_notificacion);
            $stmt_notificacion->execute([
                $id_usuario,
                $titulo_notificacion,
                $mensaje_notificacion,
                $id_pedido
            ]);
        } catch (Exception $e) {
            // Log pero no fallar por esto
            error_log("Error al crear notificación: " . $e->getMessage());
        }
        
        // ==========================
        // 8. CONFIRMAR TRANSACCIÓN
        // ==========================
        $conexion->commit();
        
        // ==========================
        // 9. LIMPIAR SESIÓN Y PREPARAR RESPUESTA
        // ==========================
        unset($_SESSION['checkout_data']); // Limpiar datos del checkout
        
        $_SESSION['checkout_success'] = true;
        $_SESSION['pedido_id'] = $id_pedido;
        $_SESSION['pedido_total'] = $total;
        $_SESSION['metodo_pago'] = $metodo_pago;
        
        // Retornar respuesta JSON
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'order_id' => $id_pedido,
            'message' => 'Pedido procesado exitosamente'
        ]);
        exit;
        
    } catch (Exception $e) {
        // Rollback en caso de error
        $conexion->rollBack();
        error_log("Error en transacción de pago: " . $e->getMessage());
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Ocurrió un error al procesar tu pedido. Por favor intenta nuevamente.',
            'error' => $e->getMessage()
        ]);
        exit;
    }
    
} catch (Exception $e) {
    error_log("Error general en pago: " . $e->getMessage());
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Ocurrió un error inesperado. Por favor intenta nuevamente.',
        'error' => $e->getMessage()
    ]);
    exit;
}
