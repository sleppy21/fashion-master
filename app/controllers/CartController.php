<?php
/**
 * CONTROLADOR DE CARRITO
 * Maneja agregar, quitar, actualizar productos del carrito
 */

class CartController {
    
    /**
     * Mostrar carrito de compras
     */
    public static function showCart() {
        require_once '../app/controllers/AuthController.php';
        AuthController::requireAuth();
        
        try {
            require_once '../app/models/Cart.php';
            
            $user_id = $_SESSION['user_id'];
            $items = Cart::getItems($user_id);
            $totals = Cart::getTotals($user_id);
            
            $data = [
                'page_title' => 'Carrito de Compras',
                'items' => $items,
                'totals' => $totals
            ];
            
            require_once '../app/views/cart/index.php';
            
        } catch(Exception $e) {
            error_log("Error en showCart: " . $e->getMessage());
            $data = [
                'page_title' => 'Error',
                'error' => 'Error al cargar el carrito'
            ];
            require_once '../app/views/errors/500.php';
        }
    }
    
    /**
     * Agregar producto al carrito (AJAX)
     */
    public static function addItem() {
        header('Content-Type: application/json');
        
        if($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            exit;
        }
        
        // Verificar autenticación
        require_once '../app/controllers/AuthController.php';
        if(!AuthController::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['error' => 'Debe iniciar sesión']);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $producto_id = (int)($input['producto_id'] ?? 0);
        $cantidad = (int)($input['cantidad'] ?? 1);
        $user_id = $_SESSION['user_id'];
        
        if($producto_id <= 0 || $cantidad <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos inválidos']);
            exit;
        }
        
        try {
            require_once '../app/models/Cart.php';
            require_once '../app/models/Product.php';
            
            // Verificar que el producto existe y tiene stock
            $producto = Product::findById($producto_id);
            
            if(!$producto) {
                http_response_code(404);
                echo json_encode(['error' => 'Producto no encontrado']);
                exit;
            }
            
            if($producto['stock_producto'] < $cantidad) {
                http_response_code(400);
                echo json_encode(['error' => 'Stock insuficiente']);
                exit;
            }
            
            // Agregar al carrito
            $success = Cart::addItem($user_id, $producto_id, $cantidad);
            
            if($success) {
                $cart_count = Cart::getItemCount($user_id);
                echo json_encode([
                    'success' => true,
                    'message' => 'Producto agregado al carrito',
                    'cart_count' => $cart_count
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error al agregar al carrito']);
            }
            
        } catch(Exception $e) {
            error_log("Error en addItem: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor']);
        }
    }
    
    /**
     * Actualizar cantidad de producto en carrito (AJAX)
     */
    public static function updateQuantity() {
        header('Content-Type: application/json');
        
        if($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            exit;
        }
        
        require_once '../app/controllers/AuthController.php';
        if(!AuthController::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['error' => 'Debe iniciar sesión']);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $producto_id = (int)($input['producto_id'] ?? 0);
        $cantidad = (int)($input['cantidad'] ?? 1);
        $user_id = $_SESSION['user_id'];
        
        if($producto_id <= 0 || $cantidad <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos inválidos']);
            exit;
        }
        
        try {
            require_once '../app/models/Cart.php';
            require_once '../app/models/Product.php';
            
            // Verificar stock
            $producto = Product::findById($producto_id);
            
            if(!$producto) {
                http_response_code(404);
                echo json_encode(['error' => 'Producto no encontrado']);
                exit;
            }
            
            if($producto['stock_producto'] < $cantidad) {
                http_response_code(400);
                echo json_encode(['error' => 'Stock insuficiente']);
                exit;
            }
            
            // Actualizar cantidad
            $success = Cart::updateQuantity($user_id, $producto_id, $cantidad);
            
            if($success) {
                $totals = Cart::getTotals($user_id);
                echo json_encode([
                    'success' => true,
                    'totals' => $totals
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error al actualizar cantidad']);
            }
            
        } catch(Exception $e) {
            error_log("Error en updateQuantity: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor']);
        }
    }
    
    /**
     * Remover producto del carrito (AJAX)
     */
    public static function removeItem() {
        header('Content-Type: application/json');
        
        if($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            exit;
        }
        
        require_once '../app/controllers/AuthController.php';
        if(!AuthController::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['error' => 'Debe iniciar sesión']);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $producto_id = (int)($input['producto_id'] ?? 0);
        $user_id = $_SESSION['user_id'];
        
        if($producto_id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de producto inválido']);
            exit;
        }
        
        try {
            require_once '../app/models/Cart.php';
            
            $success = Cart::removeItem($user_id, $producto_id);
            
            if($success) {
                $cart_count = Cart::getItemCount($user_id);
                $totals = Cart::getTotals($user_id);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Producto eliminado del carrito',
                    'cart_count' => $cart_count,
                    'totals' => $totals
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error al eliminar del carrito']);
            }
            
        } catch(Exception $e) {
            error_log("Error en removeItem: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor']);
        }
    }
    
    /**
     * Limpiar carrito completo (AJAX)
     */
    public static function clearCart() {
        header('Content-Type: application/json');
        
        if($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            exit;
        }
        
        require_once '../app/controllers/AuthController.php';
        if(!AuthController::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['error' => 'Debe iniciar sesión']);
            exit;
        }
        
        try {
            require_once '../app/models/Cart.php';
            
            $user_id = $_SESSION['user_id'];
            $success = Cart::clearCart($user_id);
            
            if($success) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Carrito vaciado'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error al vaciar el carrito']);
            }
            
        } catch(Exception $e) {
            error_log("Error en clearCart: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor']);
        }
    }
    
    /**
     * Obtener cantidad de items en carrito (AJAX)
     */
    public static function getCartCount() {
        header('Content-Type: application/json');
        
        require_once '../app/controllers/AuthController.php';
        
        if(!AuthController::isLoggedIn()) {
            echo json_encode(['count' => 0]);
            exit;
        }
        
        try {
            require_once '../app/models/Cart.php';
            
            $user_id = $_SESSION['user_id'];
            $count = Cart::getItemCount($user_id);
            
            echo json_encode(['count' => $count]);
            
        } catch(Exception $e) {
            error_log("Error en getCartCount: " . $e->getMessage());
            echo json_encode(['count' => 0]);
        }
    }
    
    /**
     * Obtener resumen del carrito (AJAX)
     */
    public static function getCartSummary() {
        header('Content-Type: application/json');
        
        require_once '../app/controllers/AuthController.php';
        
        if(!AuthController::isLoggedIn()) {
            echo json_encode(['items' => [], 'totals' => ['subtotal' => 0, 'total' => 0]]);
            exit;
        }
        
        try {
            require_once '../app/models/Cart.php';
            
            $user_id = $_SESSION['user_id'];
            $items = Cart::getItems($user_id);
            $totals = Cart::getTotals($user_id);
            
            echo json_encode([
                'items' => $items,
                'totals' => $totals
            ]);
            
        } catch(Exception $e) {
            error_log("Error en getCartSummary: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener resumen del carrito']);
        }
    }
}
?>