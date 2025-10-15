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
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Por favor completa todos los campos obligatorios de información del cliente.',
            'error' => 'Campos vacíos: nombre, email, telefono o dni'
        ]);
        exit;
    }
    
    if (empty($direccion) || empty($departamento) || empty($provincia) || empty($distrito)) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Por favor completa todos los campos obligatorios de dirección de envío.',
            'error' => 'Campos vacíos en dirección'
        ]);
        exit;
    }
    
    if (!in_array($tipo_comprobante, ['boleta', 'factura'])) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Tipo de comprobante inválido. Solo se permite Boleta o Factura.',
            'error' => 'Tipo comprobante: ' . $tipo_comprobante
        ]);
        exit;
    }
    
    if (!in_array($metodo_pago, ['tarjeta', 'transferencia', 'yape', 'efectivo'])) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Por favor selecciona un método de pago válido.',
            'error' => 'Método pago: ' . $metodo_pago
        ]);
        exit;
    }
    
    // Validar email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'El email ingresado no es válido.',
            'error' => 'Email inválido: ' . $email
        ]);
        exit;
    }
    
    // Validar DNI (8 dígitos) para boleta o RUC (11 dígitos) para factura
    if ($tipo_comprobante === 'boleta') {
        if (strlen($dni) !== 8 || !ctype_digit($dni)) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Para Boleta, el DNI debe tener exactamente 8 dígitos numéricos.',
                'error' => 'DNI inválido: ' . $dni
            ]);
            exit;
        }
    } elseif ($tipo_comprobante === 'factura') {
        if (strlen($dni) !== 11 || !ctype_digit($dni)) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Para Factura, el RUC debe tener exactamente 11 dígitos numéricos.',
                'error' => 'RUC inválido: ' . $dni
            ]);
            exit;
        }
        if (empty($razon_social)) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Por favor completa la Razón Social para emitir factura.',
                'error' => 'Razón social vacía'
            ]);
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
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Tu carrito está vacío.',
            'error' => 'Carrito vacío'
        ]);
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
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Los siguientes productos no tienen stock suficiente: ' . implode(', ', $productos_sin_stock),
            'error' => 'Stock insuficiente',
            'productos_sin_stock' => $productos_sin_stock
        ]);
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
        
        // Lógica inteligente: detectar si es DNI (8 dígitos) o RUC (11 dígitos)
        $dni_value = null;
        $ruc_value = null;
        
        if (strlen($dni) == 8) {
            // Es un DNI
            $dni_value = $dni;
        } elseif (strlen($dni) == 11) {
            // Es un RUC
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
            $dni_value, // DNI si tiene 8 dígitos, NULL si no
            $ruc_value, // RUC si tiene 11 dígitos, NULL si no
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
        // 10. GUARDAR DIRECCIÓN (SI SE SOLICITÓ)
        // ==========================
        $direccion_guardada = false;
        if (isset($_POST['guardar_direccion']) && $_POST['guardar_direccion'] === '1') {
            try {
                // Construir dirección completa
                $direccion_completa = $direccion . ', ' . $distrito . ', ' . $provincia . ', ' . $departamento;
                
                // Verificar si ya existe una dirección exactamente igual para evitar duplicados
                $sql_check = "SELECT id_direccion FROM direccion 
                             WHERE id_usuario = ? 
                             AND direccion_completa_direccion = ?
                             AND status_direccion = 1";
                $stmt_check = $conexion->prepare($sql_check);
                $stmt_check->execute([$id_usuario, $direccion_completa]);
                
                // Solo insertar si no existe
                if ($stmt_check->rowCount() === 0) {
                    $sql_save_address = "INSERT INTO direccion 
                                        (id_usuario, nombre_direccion, telefono_direccion, 
                                         direccion_completa_direccion, departamento_direccion, 
                                         provincia_direccion, distrito_direccion, 
                                         referencia_direccion, es_principal, status_direccion, 
                                         fecha_creacion_direccion) 
                                        VALUES (?, ?, ?, ?, ?, ?, ?, NULL, 0, 1, NOW())";
                    $stmt_save_address = $conexion->prepare($sql_save_address);
                    $stmt_save_address->execute([
                        $id_usuario, 
                        $nombre, 
                        $telefono, 
                        $direccion_completa, 
                        $departamento, 
                        $provincia, 
                        $distrito
                    ]);
                    $direccion_guardada = true;
                } else {
                    // Ya existe, consideramos que está "guardada"
                    $direccion_guardada = true;
                }
            } catch (Exception $e) {
                // Log el error pero no fallar el pedido por esto
                error_log("Error al guardar dirección: " . $e->getMessage());
                $direccion_guardada = false;
            }
        }
        
        // ==========================
        // 11. CONFIRMAR TRANSACCIÓN
        // ==========================
        $conexion->commit();
        
        // ==========================
        // 12. PREPARAR SESIÓN Y RESPUESTA JSON
        // ==========================
        $_SESSION['checkout_success'] = true;
        $_SESSION['pedido_id'] = $id_pedido;
        $_SESSION['pedido_total'] = $total;
        $_SESSION['metodo_pago'] = $metodo_pago;
        
        // Retornar respuesta JSON
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'order_id' => $id_pedido,
            'message' => 'Pedido procesado exitosamente',
            'direccion_guardada' => $direccion_guardada
        ]);
        exit;
        
    } catch (Exception $e) {
        // Rollback en caso de error
        $conexion->rollBack();
        error_log("Error en transacción de checkout: " . $e->getMessage());
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Ocurrió un error al procesar tu pedido. Por favor intenta nuevamente.',
            'error' => $e->getMessage()
        ]);
        exit;
    }
    
} catch (Exception $e) {
    error_log("Error general en checkout: " . $e->getMessage());
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Ocurrió un error inesperado. Por favor intenta nuevamente.',
        'error' => $e->getMessage()
    ]);
    exit;
}
