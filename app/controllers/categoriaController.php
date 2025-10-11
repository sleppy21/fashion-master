<?php
/**
 * CONTROLADOR DE CATEGORÍAS - CRUD COMPLETO
 * Maneja todas las operaciones de categorías para el admin
 */

// ⭐ SILENCIAR TODOS LOS ERRORES HTML - SOLO JSON
@ini_set('display_errors', '0');
@ini_set('display_startup_errors', '0');
error_reporting(0); // ⭐ Desactivar reporte visual de errores
@ini_set('log_errors', '1');
@ini_set('error_log', __DIR__ . '/../../logs/categoria_errors.log');

// ⭐ Capturar CUALQUIER output no deseado
ob_start();

session_start();
require_once __DIR__ . '/../../config/conexion.php';

// ⭐ Limpiar cualquier output acumulado antes de enviar JSON
ob_clean();

// Headers para respuestas JSON
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Credentials: true');

// Verificar permisos de administrador
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    http_response_code(403);
    echo json_encode([
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
    switch ($action) {
        case 'list':
            listCategorias();
            break;
        case 'get':
            getCategoria();
            break;
        case 'create':
            createCategoria();
            break;
        case 'update':
            updateCategoria();
            break;
        case 'delete':
            deleteCategoria();
            break;
        case 'toggle_status':
            toggleCategoriaStatus();
            break;
        case 'change_estado':
            changeEstado();
            break;
        case 'upload_image':
            uploadImage();
            break;
        case 'get_stats':
            getCategoriaStats();
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida']);
    }
} catch (Exception $e) {
    // ⭐ Limpiar cualquier output buffer acumulado
    if (ob_get_level()) ob_clean();
    
    // ⭐ Log del error completo
    error_log("❌ ERROR GENERAL categoriaController.php: " . $e->getMessage());
    error_log("   Trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'success' => false,
        'error' => 'Error interno del servidor',
        'details' => $e->getMessage(),
        'action' => $action ?? 'desconocida'
    ]);
    exit;
} catch (Throwable $e) {
    // ⭐ Capturar errores fatales (PHP 7+)
    if (ob_get_level()) ob_clean();
    
    error_log("❌ ERROR FATAL categoriaController.php: " . $e->getMessage());
    error_log("   Trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'success' => false,
        'error' => 'Error fatal del servidor',
        'details' => $e->getMessage()
    ]);
    exit;
}

// ⭐ Al final, limpiar y enviar output
if (ob_get_level()) {
    ob_end_flush();
}

/**
 * Listar categorías con paginación y filtros
 */
function listCategorias() {
    global $conn;
    
    // VALIDACIÓN Y SANITIZACIÓN DE ENTRADA
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = min(100, max(1, (int)($_GET['limit'] ?? 10))); // Límite máximo 100
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $status_filter = isset($_GET['status']) && $_GET['status'] !== '' ? $_GET['status'] : null;
    
    $offset = ($page - 1) * $limit;
    
    // Construir consulta base con prepared statements
    $where_conditions = [];
    $params = [];
    
    // FILTRO OBLIGATORIO: Solo categorías NO eliminadas (status_categoria = 1)
    $where_conditions[] = "c.status_categoria = 1";
    
    if (!empty($search)) {
        // SANITIZAR búsqueda para prevenir SQL injection
        $search = htmlspecialchars($search, ENT_QUOTES, 'UTF-8');
        $where_conditions[] = "(c.nombre_categoria LIKE ? OR c.codigo_categoria LIKE ? OR c.descripcion_categoria LIKE ?)";
        $search_param = "%$search%";
        $params = array_merge($params, [$search_param, $search_param, $search_param]);
    }
    
    if ($status_filter !== null) {
        // Filtrar por estado_categoria (ENUM) - convertir valores
        $where_conditions[] = "c.estado_categoria = ?";
        // Aceptar tanto 'activo'/'inactivo' como 1/0
        if ($status_filter === '1' || $status_filter === 'activo') {
            $params[] = 'activo';
        } elseif ($status_filter === '0' || $status_filter === 'inactivo') {
            $params[] = 'inactivo';
        } else {
            $params[] = $status_filter;
        }
    }
    
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // Contar total de registros
    $count_query = "SELECT COUNT(*) as total FROM categoria c $where_clause";
    $stmt = $conn->prepare($count_query);
    $stmt->execute($params);
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Obtener categorías paginadas
    $query = "
        SELECT 
            c.*,
            0 as total_productos
        FROM categoria c
        $where_clause 
        ORDER BY c.nombre_categoria ASC
        LIMIT ? OFFSET ?
    ";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear datos para el frontend con sanitización XSS
    foreach ($categorias as &$categoria) {
        // Sanitizar todos los campos de texto para prevenir XSS
        $categoria['nombre_categoria'] = htmlspecialchars($categoria['nombre_categoria'] ?? '', ENT_QUOTES, 'UTF-8');
        $categoria['codigo_categoria'] = htmlspecialchars($categoria['codigo_categoria'] ?? '', ENT_QUOTES, 'UTF-8');
        $categoria['descripcion_categoria'] = htmlspecialchars($categoria['descripcion_categoria'] ?? '', ENT_QUOTES, 'UTF-8');
        
        $categoria['fecha_creacion_formato'] = date('d/m/Y', strtotime($categoria['fecha_creacion_categoria']));
        $categoria['estado_texto'] = $categoria['estado_categoria'] === 'activo' ? 'Activa' : 'Inactiva';
        // estado_categoria ya viene como 'activo'/'inactivo' del ENUM, no necesita conversión
        $categoria['descripcion_corta'] = $categoria['descripcion_categoria'] ? 
            (strlen($categoria['descripcion_categoria']) > 100 ? 
                substr($categoria['descripcion_categoria'], 0, 100) . '...' : 
                $categoria['descripcion_categoria']) : 
            'Sin descripción';
    }
    
    echo json_encode([
        'success' => true,
        'data' => $categorias,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => ceil($total / $limit),
            'total_items' => (int)$total,
            'total_records' => (int)$total,
            'per_page' => $limit,
            'items_per_page' => $limit
        ]
    ]);
}

/**
 * Obtener una categoría específica
 */
function getCategoria() {
    global $conn;
    
    $id = (int)($_GET['id'] ?? 0);
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de categoría requerido']);
        return;
    }
    
    $query = "
        SELECT 
            c.*,
            0 as total_productos
        FROM categoria c
        WHERE c.id_categoria = ?
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$id]);
    $categoria = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$categoria) {
        http_response_code(404);
        echo json_encode(['error' => 'Categoría no encontrada']);
        return;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $categoria
    ]);
}

/**
 * Crear nueva categoría
 */
function createCategoria() {
    global $conn;
    
    // Intentar leer JSON del body, si falla usar $_POST
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // Si no hay JSON válido, usar $_POST directamente
    if (json_last_error() !== JSON_ERROR_NONE || empty($data)) {
        $data = $_POST;
    }
    
    // PROCESAR IMAGEN SI SE SUBIÓ
    if (isset($_FILES['imagen_categoria']) && $_FILES['imagen_categoria']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['imagen_categoria'];
        
        // Validar tipo de archivo
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime_type, $allowed_types)) {
            http_response_code(400);
            echo json_encode(['error' => 'Tipo de archivo no permitido. Solo se permiten imágenes JPG, PNG, GIF y WebP']);
            return;
        }
        
        // Validar tamaño (máximo 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            http_response_code(400);
            echo json_encode(['error' => 'La imagen excede el tamaño máximo de 5MB']);
            return;
        }
        
        // Directorio de destino
        $upload_dir = __DIR__ . '/../../public/assets/img/categories/';
        
        // Crear directorio si no existe
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Generar nombre único para el archivo
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = 'categoria-' . time() . '-' . uniqid() . '.' . $extension;
        $filepath = $upload_dir . $filename;
        
        // Mover archivo
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Agregar a datos para actualizar
            $data['imagen_categoria'] = $filename;
            $data['url_imagen_categoria'] = '/fashion-master/public/assets/img/categories/' . $filename;
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al guardar la imagen']);
            return;
        }
    }
    
    // Validar campos requeridos
    if (empty($data['nombre_categoria']) || !is_string($data['nombre_categoria'])) {
        http_response_code(400);
        echo json_encode(['error' => 'El nombre de la categoría es requerido']);
        return;
    }
    
    // SANITIZAR Y VALIDAR datos de entrada
    $nombre = trim($data['nombre_categoria']);
    $descripcion = isset($data['descripcion_categoria']) ? trim($data['descripcion_categoria']) : null;
    $codigo = isset($data['codigo_categoria']) ? trim($data['codigo_categoria']) : null;
    $imagen = isset($data['imagen_categoria']) ? trim($data['imagen_categoria']) : 'default-product.jpg';
    $url_imagen = isset($data['url_imagen_categoria']) ? trim($data['url_imagen_categoria']) : '/fashion-master/public/assets/img/default-product.jpg';
    // estado_categoria es ENUM('activo','inactivo'), convertir entrada a string
    $estado = isset($data['estado_categoria']) ? (($data['estado_categoria'] == 1 || $data['estado_categoria'] === 'activo') ? 'activo' : 'inactivo') : 'activo';
    
    // Validar longitud de campos
    if (strlen($nombre) < 2 || strlen($nombre) > 255) {
        http_response_code(400);
        echo json_encode(['error' => 'El nombre debe tener entre 2 y 255 caracteres']);
        return;
    }
    
    if ($descripcion && strlen($descripcion) > 1000) {
        http_response_code(400);
        echo json_encode(['error' => 'La descripción no puede exceder 1000 caracteres']);
        return;
    }
    
    if ($codigo && strlen($codigo) > 50) {
        http_response_code(400);
        echo json_encode(['error' => 'El código no puede exceder 50 caracteres']);
        return;
    }
    
    // Validar status
    if (!in_array($estado, ['activo', 'inactivo'], true)) {
        http_response_code(400);
        echo json_encode(['error' => 'Estado inválido']);
        return;
    }
    
    // Verificar si el nombre ya existe
    $check_query = "SELECT COUNT(*) as count FROM categoria WHERE nombre_categoria = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->execute([$nombre]);
    $exists = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($exists > 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Ya existe una categoría con ese nombre']);
        return;
    }
    
    // Verificar código único si se proporciona
    if ($codigo) {
        $check_code_query = "SELECT COUNT(*) as count FROM categoria WHERE codigo_categoria = ?";
        $stmt = $conn->prepare($check_code_query);
        $stmt->execute([$codigo]);
        $code_exists = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($code_exists > 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Ya existe una categoría con ese código']);
            return;
        }
    }
    
    // Transacción para inserción segura
    try {
        $conn->beginTransaction();
        
        // Insertar categoría
        $query = "
            INSERT INTO categoria (
                nombre_categoria, codigo_categoria, descripcion_categoria,
                imagen_categoria, url_imagen_categoria, estado_categoria, fecha_creacion_categoria
            ) VALUES (?, ?, ?, ?, ?, ?, NOW())
        ";
        
        $params = [
            $nombre,
            $codigo,
            $descripcion,
            $imagen,
            $url_imagen,
            $estado
        ];
        
        $stmt = $conn->prepare($query);
        
        if ($stmt->execute($params)) {
            $new_id = $conn->lastInsertId();
            $conn->commit();
            
            // Obtener los datos completos de la categoría recién creada
            $select_query = "SELECT * FROM categoria WHERE id_categoria = ?";
            $select_stmt = $conn->prepare($select_query);
            $select_stmt->execute([$new_id]);
            $categoria_creada = $select_stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'message' => 'Categoría creada exitosamente',
                'id' => (int)$new_id,
                'data' => $categoria_creada
            ]);
        } else {
            $conn->rollBack();
            http_response_code(500);
            echo json_encode(['error' => 'Error al crear categoría']);
        }
    } catch (Exception $e) {
        $conn->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
}

/**
 * Actualizar categoría existente
 */
function updateCategoria() {
    global $conn;
    
    // ========== LOGS DE DEBUG ==========
    error_log("========== INICIO updateCategoria ==========");
    error_log("📥 $_POST completo: " . print_r($_POST, true));
    error_log("📥 $_FILES: " . print_r($_FILES, true));
    error_log("========================================");
    
    $id = (int)($_POST['id_categoria'] ?? $_POST['id'] ?? $_GET['id'] ?? 0);
    
    error_log("🆔 ID extraído: " . $id);
    
    // Intentar leer JSON del body, si falla usar $_POST
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // Si no hay JSON válido, usar $_POST directamente
    if (json_last_error() !== JSON_ERROR_NONE || empty($data)) {
        $data = $_POST;
        error_log("📋 Usando $_POST como data");
    } else {
        error_log("📋 Usando JSON del body");
    }
    
    error_log("📊 Data final para procesar: " . print_r($data, true));
    
    // PROCESAR IMAGEN SI SE SUBIÓ
    if (isset($_FILES['imagen_categoria']) && $_FILES['imagen_categoria']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['imagen_categoria'];
        
        // Validar tipo de archivo
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime_type, $allowed_types)) {
            http_response_code(400);
            echo json_encode(['error' => 'Tipo de archivo no permitido. Solo se permiten imágenes JPG, PNG, GIF y WebP']);
            return;
        }
        
        // Validar tamaño (máximo 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            http_response_code(400);
            echo json_encode(['error' => 'La imagen excede el tamaño máximo de 5MB']);
            return;
        }
        
        // Directorio de destino
        $upload_dir = __DIR__ . '/../../public/assets/img/categories/';
        
        // Crear directorio si no existe
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Generar nombre único para el archivo
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = 'categoria-' . $id . '-' . time() . '.' . $extension;
        $filepath = $upload_dir . $filename;
        
        // Mover archivo
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Agregar a datos para actualizar
            $data['imagen_categoria'] = $filename;
            $data['url_imagen_categoria'] = '/fashion-master/public/assets/img/categories/' . $filename;
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al guardar la imagen']);
            return;
        }
    }
    
    // VALIDACIÓN DE ENTRADA
    if (!$id || $id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de categoría requerido y debe ser válido']);
        return;
    }
    
    // Verificar que la categoría existe
    $check_query = "SELECT id_categoria, nombre_categoria FROM categoria WHERE id_categoria = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->execute([$id]);
    $categoria_actual = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$categoria_actual) {
        http_response_code(404);
        echo json_encode(['error' => 'Categoría no encontrada']);
        return;
    }
    
    // Construir consulta de actualización dinámicamente
    $update_fields = [];
    $params = [];
    
    // VALIDAR DUPLICADOS antes de actualizar
    if (isset($data['nombre_categoria']) && !empty($data['nombre_categoria'])) {
        $check_name_query = "SELECT COUNT(*) as count FROM categoria WHERE nombre_categoria = ? AND id_categoria != ?";
        $stmt = $conn->prepare($check_name_query);
        $stmt->execute([trim($data['nombre_categoria']), $id]);
        $name_exists = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($name_exists > 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Ya existe otra categoría con ese nombre']);
            return;
        }
    }
    
    if (isset($data['codigo_categoria']) && !empty($data['codigo_categoria'])) {
        $check_code_query = "SELECT COUNT(*) as count FROM categoria WHERE codigo_categoria = ? AND id_categoria != ?";
        $stmt = $conn->prepare($check_code_query);
        $stmt->execute([trim($data['codigo_categoria']), $id]);
        $code_exists = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($code_exists > 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Ya existe otra categoría con ese código']);
            return;
        }
    }
    
    $allowed_fields = [
        'nombre_categoria' => ['type' => 'string', 'max' => 255, 'min' => 2],
        'codigo_categoria' => ['type' => 'string', 'max' => 50],
        'descripcion_categoria' => ['type' => 'string', 'max' => 1000],
        'imagen_categoria' => ['type' => 'string', 'max' => 500],
        'url_imagen_categoria' => ['type' => 'string', 'max' => 500],
        'estado_categoria' => ['type' => 'enum', 'values' => ['activo', 'inactivo']],
        'status_categoria' => ['type' => 'int', 'values' => [0, 1]]
    ];
    
    foreach ($allowed_fields as $field => $validation) {
        if (isset($data[$field])) {
            $value = $data[$field];
            
            // Validación por tipo
            if ($validation['type'] === 'string') {
                $value = trim($value);
                
                // Validar longitud mínima
                if (isset($validation['min']) && strlen($value) < $validation['min']) {
                    http_response_code(400);
                    echo json_encode(['error' => "El campo $field debe tener al menos {$validation['min']} caracteres"]);
                    return;
                }
                
                // Validar longitud máxima
                if (isset($validation['max']) && strlen($value) > $validation['max']) {
                    http_response_code(400);
                    echo json_encode(['error' => "El campo $field no puede exceder {$validation['max']} caracteres"]);
                    return;
                }
            } elseif ($validation['type'] === 'enum') {
                // Validar ENUM - convertir valores numéricos a string
                if ($value == 1 || $value === 'activo') {
                    $value = 'activo';
                } elseif ($value == 0 || $value === 'inactivo') {
                    $value = 'inactivo';
                }
                
                // Validar valores permitidos
                if (isset($validation['values']) && !in_array($value, $validation['values'], true)) {
                    http_response_code(400);
                    echo json_encode(['error' => "Valor inválido para $field"]);
                    return;
                }
            } elseif ($validation['type'] === 'int') {
                $value = (int)$value;
                
                // Validar valores permitidos
                if (isset($validation['values']) && !in_array($value, $validation['values'], true)) {
                    http_response_code(400);
                    echo json_encode(['error' => "Valor inválido para $field"]);
                    return;
                }
                
                // Validar mínimo
                if (isset($validation['min']) && $value < $validation['min']) {
                    http_response_code(400);
                    echo json_encode(['error' => "El campo $field debe ser mayor o igual a {$validation['min']}"]);
                    return;
                }
            }
            
            $update_fields[] = "$field = ?";
            $params[] = $value;
        }
    }
    
    if (empty($update_fields)) {
        http_response_code(400);
        echo json_encode(['error' => 'No hay campos válidos para actualizar']);
        return;
    }
    
    // Agregar fecha de actualización
    $update_fields[] = "fecha_actualizacion_categoria = NOW()";
    $params[] = $id;
    
    // ========== LOGS ANTES DE UPDATE ==========
    error_log("🔧 Campos a actualizar: " . implode(', ', $update_fields));
    error_log("🔧 Parámetros: " . print_r($params, true));
    error_log("🔧 Query que se ejecutará: UPDATE categoria SET " . implode(', ', $update_fields) . " WHERE id_categoria = ?");
    // ==========================================
    
    // Ejecutar actualización en transacción
    try {
        $conn->beginTransaction();
        
        $query = "UPDATE categoria SET " . implode(', ', $update_fields) . " WHERE id_categoria = ?";
        $stmt = $conn->prepare($query);
        
        error_log("⚡ Ejecutando UPDATE...");
        $execute_result = $stmt->execute($params);
        error_log("⚡ Resultado execute: " . ($execute_result ? 'TRUE' : 'FALSE'));
        error_log("⚡ Filas afectadas: " . $stmt->rowCount());
        
        if ($execute_result) {
            $conn->commit();
            
            // Obtener los datos actualizados de la categoría
            $select_query = "SELECT * FROM categoria WHERE id_categoria = ?";
            $select_stmt = $conn->prepare($select_query);
            $select_stmt->execute([$id]);
            $categoria_actualizada = $select_stmt->fetch(PDO::FETCH_ASSOC);
            
            // LOG PARA DEBUG
            error_log("✅ Categoría actualizada - ID: " . $id);
            error_log("📊 Datos devueltos: " . json_encode($categoria_actualizada));
            error_log("🔢 Código en respuesta: " . $categoria_actualizada['codigo_categoria']);
            
            echo json_encode([
                'success' => true,
                'message' => 'Categoría actualizada exitosamente',
                'data' => $categoria_actualizada
            ]);
        } else {
            $conn->rollBack();
            http_response_code(500);
            echo json_encode(['error' => 'Error al actualizar categoría']);
        }
    } catch (Exception $e) {
        $conn->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
}

/**
 * Eliminar categoría (soft delete)
 */
function deleteCategoria() {
    global $conn;
    
    // Leer el cuerpo de la solicitud
    $input = file_get_contents('php://input');
    parse_str($input, $parsed);
    
    // Intentar obtener ID de múltiples fuentes
    $id = (int)($parsed['id'] ?? $_POST['id'] ?? $_GET['id'] ?? 0);
    
    if (!$id) {
        http_response_code(400);
        echo json_encode([
            'error' => 'ID de categoría requerido',
            'debug' => [
                'parsed' => $parsed,
                'POST' => $_POST,
                'GET' => $_GET,
                'input' => $input
            ]
        ]);
        return;
    }
    
    // Soft delete - cambiar status_categoria a 0 (mantener estado_categoria como estaba)
    $query = "UPDATE categoria SET status_categoria = 0 WHERE id_categoria = ?";
    $stmt = $conn->prepare($query);
    
    if ($stmt->execute([$id])) {
        echo json_encode([
            'success' => true,
            'message' => 'Categoría eliminada exitosamente'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al eliminar categoría']);
    }
}

/**
 * Cambiar estado de la categoría (activar/desactivar)
 */
function toggleCategoriaStatus() {
    global $conn;
    
    $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de categoría requerido']);
        return;
    }
    
    // Obtener estado actual
    $query = "SELECT estado_categoria FROM categoria WHERE id_categoria = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$id]);
    $current_status = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$current_status) {
        http_response_code(404);
        echo json_encode(['error' => 'Categoría no encontrada']);
        return;
    }
    
    // Cambiar estado
    $new_status = ($current_status['estado_categoria'] === 'activo') ? 'inactivo' : 'activo';
    $update_query = "UPDATE categoria SET estado_categoria = ? WHERE id_categoria = ?";
    $stmt = $conn->prepare($update_query);
    
    if ($stmt->execute([$new_status, $id])) {
        echo json_encode([
            'success' => true,
            'message' => ($new_status === 'activo') ? 'Categoría activada' : 'Categoría desactivada',
            'new_status' => $new_status
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al cambiar estado de la categoría']);
    }
}

/**
 * Cambiar estado específico de categoría (para updateSingleCategoria)
 */
function changeEstado() {
    global $conn;
    
    // Leer el cuerpo de la solicitud
    $input = file_get_contents('php://input');
    parse_str($input, $parsed);
    
    // Intentar obtener ID de múltiples fuentes
    $id = (int)($parsed['id_categoria'] ?? $_POST['id_categoria'] ?? $_GET['id'] ?? $parsed['id'] ?? 0);
    $estado = $parsed['estado'] ?? $_POST['estado'] ?? '';
    
    if (!$id) {
        http_response_code(400);
        echo json_encode([
            'error' => 'ID de categoría requerido',
            'debug' => [
                'parsed' => $parsed,
                'POST' => $_POST,
                'GET' => $_GET,
                'input' => $input
            ]
        ]);
        return;
    }
    
    // Validar estado
    if (!in_array($estado, ['activo', 'inactivo'], true)) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Estado inválido. Debe ser "activo" o "inactivo"',
            'recibido' => $estado
        ]);
        return;
    }
    
    // Actualizar estado
    $update_query = "UPDATE categoria SET estado_categoria = ? WHERE id_categoria = ?";
    $stmt = $conn->prepare($update_query);
    
    if ($stmt->execute([$estado, $id])) {
        // Obtener datos completos de la categoría actualizada
        $query = "
            SELECT 
                c.*,
                0 as total_productos
            FROM categoria c
            WHERE c.id_categoria = ?
        ";
        
        $stmt = $conn->prepare($query);
        $stmt->execute([$id]);
        $categoria = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Formatear datos
        $categoria['fecha_creacion_formato'] = date('d/m/Y', strtotime($categoria['fecha_creacion_categoria']));
        
        echo json_encode([
            'success' => true,
            'message' => ($estado === 'activo') ? 'Categoría activada' : 'Categoría desactivada',
            'data' => $categoria,
            'categoria' => $categoria // Alias para compatibilidad
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al cambiar estado de la categoría']);
    }
}

/**
 * Actualizar orden de categorías
 */
/**
 * Subir imagen de categoría con validación de seguridad
 */
function uploadImage() {
    // VALIDACIÓN DE ARCHIVO
    if (!isset($_FILES['image'])) {
        http_response_code(400);
        echo json_encode(['error' => 'No se recibió ninguna imagen']);
        return;
    }
    
    $file = $_FILES['image'];
    
    // Verificar errores de upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => 'El archivo excede el tamaño máximo permitido',
            UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño máximo del formulario',
            UPLOAD_ERR_PARTIAL => 'El archivo se subió parcialmente',
            UPLOAD_ERR_NO_FILE => 'No se subió ningún archivo',
            UPLOAD_ERR_NO_TMP_DIR => 'Falta la carpeta temporal',
            UPLOAD_ERR_CANT_WRITE => 'Error al escribir el archivo',
            UPLOAD_ERR_EXTENSION => 'Una extensión de PHP detuvo la subida'
        ];
        echo json_encode(['error' => $error_messages[$file['error']] ?? 'Error desconocido al subir archivo']);
        return;
    }
    
    $upload_dir = __DIR__ . '/../../public/assets/img/categories/';
    
    // Crear directorio si no existe con permisos seguros
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo crear el directorio de imágenes']);
            return;
        }
    }
    
    // VALIDACIÓN ESTRICTA DE TIPO DE ARCHIVO
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $allowed_types = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp'
    ];
    
    if (!array_key_exists($mime_type, $allowed_types)) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Tipo de archivo no permitido. Solo se permiten imágenes JPG, PNG, GIF y WebP',
            'detected_type' => $mime_type
        ]);
        return;
    }
    
    // VALIDACIÓN DE TAMAÑO (max 5MB)
    $max_size = 5 * 1024 * 1024;
    if ($file['size'] > $max_size) {
        http_response_code(400);
        echo json_encode([
            'error' => 'El archivo es demasiado grande (máximo 5MB)',
            'size' => round($file['size'] / 1024 / 1024, 2) . 'MB'
        ]);
        return;
    }
    
    // VALIDACIÓN DE DIMENSIONES DE IMAGEN
    $image_info = getimagesize($file['tmp_name']);
    if ($image_info === false) {
        http_response_code(400);
        echo json_encode(['error' => 'El archivo no es una imagen válida']);
        return;
    }
    
    list($width, $height) = $image_info;
    
    // Validar dimensiones mínimas y máximas
    if ($width < 100 || $height < 100) {
        http_response_code(400);
        echo json_encode(['error' => 'La imagen es demasiado pequeña (mínimo 100x100px)']);
        return;
    }
    
    if ($width > 4000 || $height > 4000) {
        http_response_code(400);
        echo json_encode(['error' => 'La imagen es demasiado grande (máximo 4000x4000px)']);
        return;
    }
    
    // Generar nombre único y seguro
    $extension = $allowed_types[$mime_type];
    $filename = 'category_' . uniqid() . '_' . time() . '.' . $extension;
    
    // Sanitizar nombre de archivo para prevenir path traversal
    $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $filename);
    
    $filepath = $upload_dir . $filename;
    
    // Mover archivo con validación
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Establecer permisos seguros
        chmod($filepath, 0644);
        
        // Optimizar imagen si es muy grande
        optimizarImagen($filepath, $mime_type);
        
        echo json_encode([
            'success' => true,
            'message' => 'Imagen subida exitosamente',
            'filename' => $filename,
            'url' => '/fashion-master/public/assets/img/categories/' . $filename,
            'size' => filesize($filepath),
            'dimensions' => [
                'width' => $width,
                'height' => $height
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al guardar la imagen en el servidor']);
    }
}

/**
 * Optimizar imagen para reducir tamaño
 */
function optimizarImagen($filepath, $mime_type) {
    // Cargar imagen según tipo
    switch ($mime_type) {
        case 'image/jpeg':
            $imagen = imagecreatefromjpeg($filepath);
            if ($imagen) {
                imagejpeg($imagen, $filepath, 85); // Calidad 85%
                imagedestroy($imagen);
            }
            break;
        case 'image/png':
            $imagen = imagecreatefrompng($filepath);
            if ($imagen) {
                imagepng($imagen, $filepath, 7); // Compresión nivel 7
                imagedestroy($imagen);
            }
            break;
        case 'image/gif':
            // GIF no se optimiza para preservar animaciones
            break;
        case 'image/webp':
            if (function_exists('imagecreatefromwebp')) {
                $imagen = imagecreatefromwebp($filepath);
                if ($imagen) {
                    imagewebp($imagen, $filepath, 85);
                    imagedestroy($imagen);
                }
            }
            break;
    }
}

/**
 * Obtener estadísticas de categorías
 */
function getCategoriaStats() {
    global $conn;
    
    $stats = [];
    
    // Total de categorías
    $query = "SELECT COUNT(*) as total FROM categoria";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Categorías activas
    $query = "SELECT COUNT(*) as total FROM categoria WHERE estado_categoria = 'activo'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $stats['active'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Categorías con productos (comentado - tabla producto no existe aún)
    $stats['with_products'] = 0;
    
    // Categoría con más productos (comentado - tabla producto no existe aún)
    $stats['most_products'] = ['nombre_categoria' => 'N/A', 'total_productos' => 0];
    
    // Distribución de productos por categoría (comentado - tabla producto no existe aún)
    $stats['distribution'] = [];
    
    echo json_encode([
        'success' => true,
        'data' => $stats
    ]);
}
?>