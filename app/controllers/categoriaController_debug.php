<?php
/**
 * CONTROLADOR BÁSICO DE CATEGORÍAS - DEBUG
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . '/../../config/conexion.php';

// Headers para respuestas JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Credentials: true');

// Log de debug
error_log("=== CATEGORIA CONTROLLER DEBUG ===");
error_log("Session user_id: " . ($_SESSION['user_id'] ?? 'NO SET'));
error_log("Session rol: " . ($_SESSION['rol'] ?? 'NO SET'));
error_log("Action: " . ($_GET['action'] ?? 'NO ACTION'));

// Verificar permisos de administrador
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'error' => 'Acceso denegado',
        'debug' => [
            'session_exists' => isset($_SESSION['user_id']),
            'user_id' => $_SESSION['user_id'] ?? 'no definido',
            'rol' => $_SESSION['rol'] ?? 'no definido'
        ]
    ]);
    exit;
}

// Obtener la acción solicitada
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    if ($action === 'list') {
        // Versión simplificada de list
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = min(100, max(1, (int)($_GET['limit'] ?? 10)));
        $offset = ($page - 1) * $limit;
        
        // Contar total
        $count_query = "SELECT COUNT(*) as total FROM categoria";
        $stmt = $conn->prepare($count_query);
        $stmt->execute();
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Obtener categorías (simplificado sin JOIN a productos)
        $query = "
            SELECT 
                c.*,
                0 as total_productos
            FROM categoria c
            ORDER BY c.orden ASC, c.nombre_categoria ASC
            LIMIT ? OFFSET ?
        ";
        
        $stmt = $conn->prepare($query);
        $stmt->execute([$limit, $offset]);
        $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Formatear datos
        foreach ($categorias as &$categoria) {
            $categoria['fecha_creacion_formato'] = date('d/m/Y', strtotime($categoria['fecha_creacion_categoria']));
            $categoria['estado_texto'] = $categoria['estado_categoria'] === 'activo' ? 'Activa' : 'Inactiva';
        }
        
        echo json_encode([
            'success' => true,
            'data' => $categorias,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => ceil($total / $limit),
                'total_items' => (int)$total,
                'per_page' => $limit
            ]
        ]);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Acción no válida']);
    }
} catch (Exception $e) {
    http_response_code(500);
    error_log("ERROR en categoria controller: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    echo json_encode([
        'success' => false,
        'error' => 'Error interno: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
