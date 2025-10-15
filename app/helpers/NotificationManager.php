<?php
/**
 * SISTEMA DE NOTIFICACIONES AUTOMÁTICAS
 * 
 * Este archivo maneja todas las notificaciones automáticas del sistema
 * Similar a Temu, Amazon, etc.
 * 
 * Tipos de notificaciones implementadas:
 * - Bienvenida (nuevo usuario)
 * - Confirmación de pedido
 * - Pedido en camino
 * - Pedido entregado
 * - Cambio de estado de pedido
 * - Producto de vuelta en stock
 * - Descuento en favoritos
 * - Recordatorio de carrito abandonado
 * - Puntos de recompensa
 * - Cambios de contraseña
 * - Nuevos productos similares
 * - Ofertas especiales
 */

class NotificationManager {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    /**
     * Crear una notificación genérica
     */
    private function createNotification($id_usuario, $titulo, $mensaje, $tipo = 'info', $prioridad = 'media', $url_destino = null) {
        try {
            $query = "INSERT INTO notificacion 
                      (id_usuario, titulo_notificacion, mensaje_notificacion, tipo_notificacion, 
                       prioridad_notificacion, url_destino_notificacion, leida_notificacion, 
                       fecha_creacion_notificacion, estado_notificacion) 
                      VALUES (?, ?, ?, ?, ?, ?, 0, NOW(), 'activo')";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id_usuario, $titulo, $mensaje, $tipo, $prioridad, $url_destino]);
            
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Error al crear notificación: " . $e->getMessage());
            return false;
        }
    }
    
    // ==========================================
    // NOTIFICACIONES DE USUARIO
    // ==========================================
    
    /**
     * Notificación de bienvenida para nuevos usuarios
     */
    public function notifyWelcome($id_usuario, $nombre_usuario) {
        $titulo = "🎉 ¡Bienvenido a SleppyStore!";
        $mensaje = "¡Hola {$nombre_usuario}! Gracias por unirte a nuestra familia. Explora nuestros productos y aprovecha las ofertas especiales para nuevos usuarios.";
        
        return $this->createNotification($id_usuario, $titulo, $mensaje, 'info', 'media', 'shop.php');
    }
    
    /**
     * Notificación de primer inicio de sesión
     */
    public function notifyFirstLogin($id_usuario, $nombre_usuario) {
        $titulo = "👋 ¡Bienvenido de vuelta, {$nombre_usuario}!";
        $mensaje = "Es tu primer inicio de sesión. Te recomendamos explorar nuestras categorías y agregar productos a tus favoritos.";
        
        return $this->createNotification($id_usuario, $titulo, $mensaje, 'info', 'baja', 'shop.php');
    }
    
    /**
     * Notificación de bienvenida en cada login (como Temu/Amazon)
     */
    public function notifyWelcomeBack($id_usuario, $nombre_usuario) {
        $hora = date('H');
        $saludo = ($hora < 12) ? "Buenos días" : (($hora < 20) ? "Buenas tardes" : "Buenas noches");
        
        $titulo = "👋 {$saludo}, {$nombre_usuario}!";
        $mensaje = "¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.";
        
        return $this->createNotification($id_usuario, $titulo, $mensaje, 'info', 'baja', 'shop.php');
    }
    
    /**
     * Notificación de cambio de contraseña
     */
    public function notifyPasswordChanged($id_usuario) {
        $titulo = "🔐 Contraseña actualizada";
        $mensaje = "Tu contraseña ha sido cambiada exitosamente. Si no realizaste este cambio, por favor contacta con soporte inmediatamente.";
        
        return $this->createNotification($id_usuario, $titulo, $mensaje, 'alerta', 'alta', 'change-password.php');
    }
    
    /**
     * Notificación de inicio de sesión desde nuevo dispositivo
     */
    public function notifyNewDeviceLogin($id_usuario, $dispositivo, $ubicacion = 'Ubicación desconocida') {
        $titulo = "🔐 Nuevo inicio de sesión detectado";
        $mensaje = "Se ha detectado un inicio de sesión desde {$dispositivo} en {$ubicacion}. Si no fuiste tú, cambia tu contraseña inmediatamente.";
        
        return $this->createNotification($id_usuario, $titulo, $mensaje, 'alerta', 'alta', 'change-password.php');
    }
    
    // ==========================================
    // NOTIFICACIONES DE PEDIDOS
    // ==========================================
    
    /**
     * Notificación de pedido confirmado
     */
    public function notifyOrderConfirmed($id_usuario, $id_pedido, $total) {
        $titulo = "✅ Pedido confirmado #" . str_pad($id_pedido, 5, '0', STR_PAD_LEFT);
        $mensaje = "Tu pedido por \$" . number_format($total, 2) . " ha sido confirmado. Estamos preparando tu envío.";
        
        return $this->createNotification($id_usuario, $titulo, $mensaje, 'info', 'media', 'order-confirmation.php?id=' . $id_pedido);
    }
    
    /**
     * Notificación de pedido en proceso
     */
    public function notifyOrderProcessing($id_usuario, $id_pedido) {
        $titulo = "📦 Preparando tu pedido #" . str_pad($id_pedido, 5, '0', STR_PAD_LEFT);
        $mensaje = "Tu pedido está siendo preparado para el envío. Te notificaremos cuando salga de nuestro almacén.";
        
        return $this->createNotification($id_usuario, $titulo, $mensaje, 'info', 'media', 'order-confirmation.php?id=' . $id_pedido);
    }
    
    /**
     * Notificación de pedido enviado
     */
    public function notifyOrderShipped($id_usuario, $id_pedido, $numero_seguimiento = null) {
        $titulo = "🚚 Pedido en camino #" . str_pad($id_pedido, 5, '0', STR_PAD_LEFT);
        $mensaje = "¡Tu pedido ya está en camino! ";
        
        if ($numero_seguimiento) {
            $mensaje .= "Número de seguimiento: {$numero_seguimiento}. ";
        }
        
        $mensaje .= "Llegará en 2-3 días hábiles.";
        
        return $this->createNotification($id_usuario, $titulo, $mensaje, 'info', 'alta', 'order-confirmation.php?id=' . $id_pedido);
    }
    
    /**
     * Notificación de pedido entregado
     */
    public function notifyOrderDelivered($id_usuario, $id_pedido) {
        $titulo = "🎉 Pedido entregado #" . str_pad($id_pedido, 5, '0', STR_PAD_LEFT);
        $mensaje = "Tu pedido ha sido entregado. Esperamos que disfrutes tus productos. ¿Qué te parecieron? Déjanos tu opinión.";
        
        return $this->createNotification($id_usuario, $titulo, $mensaje, 'info', 'media', 'order-confirmation.php?id=' . $id_pedido);
    }
    
    /**
     * Notificación de pedido cancelado
     */
    public function notifyOrderCancelled($id_usuario, $id_pedido, $motivo = '') {
        $titulo = "❌ Pedido cancelado #" . str_pad($id_pedido, 5, '0', STR_PAD_LEFT);
        $mensaje = "Tu pedido ha sido cancelado. ";
        
        if ($motivo) {
            $mensaje .= "Motivo: {$motivo}. ";
        }
        
        $mensaje .= "Si tienes dudas, contáctanos.";
        
        return $this->createNotification($id_usuario, $titulo, $mensaje, 'advertencia', 'alta', null);
    }
    
    // ==========================================
    // NOTIFICACIONES DE PRODUCTOS
    // ==========================================
    
    /**
     * Notificación de producto de vuelta en stock
     */
    public function notifyProductBackInStock($id_usuario, $id_producto, $nombre_producto) {
        $titulo = "🎯 ¡De vuelta en stock!";
        $mensaje = "El producto '{$nombre_producto}' que te interesa está nuevamente disponible. ¡Aprovecha antes de que se agote!";
        
        return $this->createNotification($id_usuario, $titulo, $mensaje, 'info', 'alta', 'product-details.php?id=' . $id_producto);
    }
    
    /**
     * Notificación de descuento en favoritos
     */
    public function notifyFavoriteOnSale($id_usuario, $id_producto, $nombre_producto, $descuento) {
        $titulo = "🔥 ¡Descuento en tus favoritos!";
        $mensaje = "El producto '{$nombre_producto}' ahora tiene {$descuento}% de descuento. ¡No te lo pierdas!";
        
        return $this->createNotification($id_usuario, $titulo, $mensaje, 'info', 'alta', 'product-details.php?id=' . $id_producto);
    }
    
    /**
     * Notificación de stock bajo en favoritos
     */
    public function notifyFavoriteLowStock($id_usuario, $id_producto, $nombre_producto, $stock) {
        $titulo = "⚠️ Stock limitado en favoritos";
        $mensaje = "Quedan solo {$stock} unidades del producto '{$nombre_producto}'. ¡Compra antes de que se agote!";
        
        return $this->createNotification($id_usuario, $titulo, $mensaje, 'alerta', 'alta', 'product-details.php?id=' . $id_producto);
    }
    
    /**
     * Notificación de nuevos productos similares
     */
    public function notifyNewSimilarProducts($id_usuario, $categoria) {
        $titulo = "✨ Nuevos productos para ti";
        $mensaje = "Hemos agregado nuevos productos en {$categoria} que podrían interesarte. ¡Échales un vistazo!";
        
        return $this->createNotification($id_usuario, $titulo, $mensaje, 'info', 'baja', 'shop.php');
    }
    
    // ==========================================
    // NOTIFICACIONES DE CARRITO
    // ==========================================
    
    /**
     * Notificación de carrito abandonado
     */
    public function notifyAbandonedCart($id_usuario, $cantidad_productos, $total) {
        $titulo = "🛒 ¡Tienes productos en tu carrito!";
        $mensaje = "Tienes {$cantidad_productos} producto(s) esperándote por \$" . number_format($total, 2) . ". Completa tu compra y recibe un descuento especial.";
        
        return $this->createNotification($id_usuario, $titulo, $mensaje, 'info', 'media', 'cart.php');
    }
    
    /**
     * Notificación de cambio de precio en carrito
     */
    public function notifyCartPriceChange($id_usuario, $producto, $precio_anterior, $precio_nuevo) {
        $titulo = "💰 Cambio de precio en tu carrito";
        $mensaje = "El producto '{$producto}' cambió de \$" . number_format($precio_anterior, 2) . " a \$" . number_format($precio_nuevo, 2) . ".";
        
        $tipo = $precio_nuevo < $precio_anterior ? 'info' : 'advertencia';
        $prioridad = $precio_nuevo < $precio_anterior ? 'alta' : 'media';
        
        return $this->createNotification($id_usuario, $titulo, $mensaje, $tipo, $prioridad, 'cart.php');
    }
    
    // ==========================================
    // NOTIFICACIONES DE PROMOCIONES
    // ==========================================
    
    /**
     * Notificación de oferta especial personalizada
     */
    public function notifySpecialOffer($id_usuario, $descuento, $categoria = null) {
        $titulo = "🎁 ¡Oferta especial para ti!";
        $mensaje = "Tenemos un {$descuento}% de descuento especial";
        
        if ($categoria) {
            $mensaje .= " en productos de {$categoria}";
        }
        
        $mensaje .= " solo para ti. ¡Válido por 48 horas!";
        
        return $this->createNotification($id_usuario, $titulo, $mensaje, 'info', 'alta', 'shop.php');
    }
    
    /**
     * Notificación de puntos de recompensa
     */
    public function notifyRewardPoints($id_usuario, $puntos, $accion = 'ganado') {
        $titulo = $accion === 'ganado' ? "🌟 ¡Has ganado puntos!" : "🎁 Puntos canjeados";
        $mensaje = $accion === 'ganado' 
            ? "Has ganado {$puntos} puntos de recompensa. Canjéalos por descuentos en tu próxima compra."
            : "Has canjeado {$puntos} puntos. ¡Disfruta tu descuento!";
        
        return $this->createNotification($id_usuario, $titulo, $mensaje, 'info', 'media', null);
    }
    
    /**
     * Notificación de cupón próximo a vencer
     */
    public function notifyCouponExpiring($id_usuario, $codigo_cupon, $dias_restantes) {
        $titulo = "⏰ Tu cupón está por vencer";
        $mensaje = "El cupón '{$codigo_cupon}' vence en {$dias_restantes} día(s). ¡Úsalo antes de que expire!";
        
        return $this->createNotification($id_usuario, $titulo, $mensaje, 'advertencia', 'alta', 'shop.php');
    }
    
    // ==========================================
    // NOTIFICACIONES DEL SISTEMA
    // ==========================================
    
    /**
     * Notificación de mantenimiento programado
     */
    public function notifyMaintenance($id_usuario, $fecha, $duracion) {
        $titulo = "🛠️ Mantenimiento programado";
        $mensaje = "El sistema estará en mantenimiento el {$fecha} durante aproximadamente {$duracion}. Disculpa las molestias.";
        
        return $this->createNotification($id_usuario, $titulo, $mensaje, 'sistema', 'media', null);
    }
    
    /**
     * Notificación de actualización de sistema
     */
    public function notifySystemUpdate($id_usuario) {
        $titulo = "⚡ Nueva actualización disponible";
        $mensaje = "Hemos mejorado la experiencia de compra con nuevas funciones y mejor rendimiento. ¡Explora las novedades!";
        
        return $this->createNotification($id_usuario, $titulo, $mensaje, 'sistema', 'baja', null);
    }
    
    /**
     * Notificación de verificación de email
     */
    public function notifyEmailVerification($id_usuario, $email) {
        $titulo = "📧 Verifica tu correo electrónico";
        $mensaje = "Por favor verifica tu correo electrónico ({$email}) para activar todas las funciones de tu cuenta.";
        
        return $this->createNotification($id_usuario, $titulo, $mensaje, 'advertencia', 'media', null);
    }
    
    // ==========================================
    // NOTIFICACIONES MASIVAS
    // ==========================================
    
    /**
     * Enviar notificación a múltiples usuarios
     */
    public function notifyMultipleUsers($ids_usuarios, $titulo, $mensaje, $tipo = 'info', $prioridad = 'media', $url_destino = null) {
        $success_count = 0;
        
        foreach ($ids_usuarios as $id_usuario) {
            if ($this->createNotification($id_usuario, $titulo, $mensaje, $tipo, $prioridad, $url_destino)) {
                $success_count++;
            }
        }
        
        return $success_count;
    }
    
    /**
     * Enviar notificación a todos los usuarios activos
     */
    public function notifyAllUsers($titulo, $mensaje, $tipo = 'info', $prioridad = 'baja', $url_destino = null) {
        try {
            $query = "SELECT id_usuario FROM usuario WHERE status_usuario = 1";
            $stmt = $this->conn->query($query);
            $usuarios = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            return $this->notifyMultipleUsers($usuarios, $titulo, $mensaje, $tipo, $prioridad, $url_destino);
        } catch (Exception $e) {
            error_log("Error al notificar a todos los usuarios: " . $e->getMessage());
            return 0;
        }
    }
}

/**
 * Función helper para obtener instancia del NotificationManager
 */
function getNotificationManager($conn = null) {
    if ($conn === null) {
        require_once __DIR__ . '/../config/conexion.php';
        global $conn;
    }
    return new NotificationManager($conn);
}

/**
 * EJEMPLOS DE USO:
 * 
 * // Notificación de bienvenida
 * $nm = getNotificationManager();
 * $nm->notifyWelcome($id_usuario, $nombre_usuario);
 * 
 * // Notificación de pedido confirmado
 * $nm->notifyOrderConfirmed($id_usuario, $id_pedido, $total);
 * 
 * // Notificación de producto de vuelta en stock
 * $nm->notifyProductBackInStock($id_usuario, $id_producto, $nombre_producto);
 * 
 * // Notificación de carrito abandonado
 * $nm->notifyAbandonedCart($id_usuario, $cantidad_productos, $total);
 * 
 * // Notificación a todos los usuarios
 * $nm->notifyAllUsers("🎉 ¡Gran promoción!", "50% de descuento...", 'info', 'alta', 'shop.php');
 */
?>
