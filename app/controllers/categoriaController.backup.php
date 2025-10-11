<?php
/**
 * CONTROLADOR DE CATEGORÍAS - CRUD COMPLETO
 * Maneja todas las operaciones de categorías para el admin
 */

session_start();
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../models/Category.php';

// Headers para respuestas JSON
header('Content-Type: application/json');
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
        case 'update_order':
            updateOrder();
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
    http_response_code(500);
    echo json_encode(['error' => 'Error interno: ' . $e->getMessage()]);
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
    $status_filter = isset($_GET['status']) && $_GET['status'] !== '' ? (int)$_GET['status'] : null;
    
    // Validar que status_filter sea 0 o 1
    if ($status_filter !== null && !in_array($status_filter, [0, 1], true)) {
        http_response_code(400);
        echo json_encode(['error' => 'Valor de status inválido']);
        return;
    }
    
    $offset = ($page - 1) * $limit;
    
    // Construir consulta base con prepared statements
    $where_conditions = [];
    $params = [];
    
    if (!empty($search)) {
        // SANITIZAR búsqueda para prevenir SQL injection
        $search = htmlspecialchars($search, ENT_QUOTES, 'UTF-8');
        $where_conditions[] = "(c.nombre_categoria LIKE ? OR c.codigo_categoria LIKE ? OR c.descripcion_categoria LIKE ?)";
        $search_param = "%$search%";
        $params = array_merge($params, [$search_param, $search_param, $search_param]);
    }
    
    if ($status_filter !== null) {
        // Filtrar por estado_categoria (ENUM) - convertir 1/0 a 'activo'/'inactivo'
        $where_conditions[] = "c.estado_categoria = ?";
        $params[] = $status_filter == 1 ? 'activo' : 'inactivo';
    }
    
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // Contar total de registros
    $count_query = "SELECT COUNT(*) as total FROM categoria c $where_clause";
    $stmt = $conn->prepare($count_query);
    $stmt->execute($params);
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Obtener categorías paginadas con conteo de productos
    $query = "
        SELECT 
            c.*,
            COUNT(p.id_producto) as total_productos
        FROM categoria c
        LEFT JOIN producto p ON c.id_categoria = p.id_categoria AND p.estado_producto = 1
        $where_clause 
        GROUP BY c.id_categoria
        ORDER BY c.orden ASC, c.nombre_categoria ASC
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
        $categoria['slug_categoria'] = htmlspecialchars($categoria['slug_categoria'] ?? '', ENT_QUOTES, 'UTF-8');
        
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
            COUNT(p.id_producto) as total_productos
        FROM categoria c
        LEFT JOIN producto p ON c.id_categoria = p.id_categoria AND p.estado_producto = 1
        WHERE c.id_categoria = ?
        GROUP BY c.id_categoria
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
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    // VALIDACIÓN ROBUSTA DE ENTRADA
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['error' => 'JSON inválido']);
        return;
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
    $imagen = isset($data['imagen_categoria']) ? trim($data['imagen_categoria']) : null;
    $estado = isset($data['estado_categoria']) ? (int)$data['estado_categoria'] : 1;
    $orden = isset($data['orden']) ? (int)$data['orden'] : null;
    
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
    if (!in_array($estado, [0, 1], true)) {
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
    
    // Generar slug automático seguro
    $slug = generarSlugSeguro($nombre, $conn);
    
    // Obtener siguiente orden si no se proporcionó
    if ($orden === null) {
        $order_query = "SELECT COALESCE(MAX(orden), 0) + 1 as next_order FROM categoria";
        $stmt = $conn->prepare($order_query);
        $stmt->execute();
        $orden = $stmt->fetch(PDO::FETCH_ASSOC)['next_order'];
    }
    
    // Transacción para inserción segura
    try {
        $conn->beginTransaction();
        
        // Insertar categoría
        $query = "
            INSERT INTO categoria (
                nombre_categoria, codigo_categoria, slug_categoria, descripcion_categoria,
                imagen_categoria, estado_categoria, orden, fecha_creacion_categoria
            ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ";
        
        $params = [
            $nombre,
            $codigo,
            $slug,
            $descripcion,
            $imagen,
            $estado,
            $orden
        ];
        
        $stmt = $conn->prepare($query);
        
        if ($stmt->execute($params)) {
            $new_id = $conn->lastInsertId();
            $conn->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Categoría creada exitosamente',
                'id' => (int)$new_id,
                'slug' => $slug
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
 * Función auxiliar para generar slug seguro y único
 */
function generarSlugSeguro($texto, $conn, $id_excluir = null) {
    // Convertir a minúsculas y reemplazar caracteres especiales
    $slug = strtolower(trim($texto));
    
    // Mapeo de caracteres con acento
    $caracteres_especiales = [
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
        'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
        'ä' => 'a', 'ë' => 'e', 'ï' => 'i', 'ö' => 'o', 'ü' => 'u',
        'ñ' => 'n', 'ç' => 'c', 'ã' => 'a', 'õ' => 'o'
    ];
    
    $slug = strtr($slug, $caracteres_especiales);
    
    // Reemplazar espacios y caracteres no alfanuméricos con guiones
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    
    // Eliminar guiones duplicados y del inicio/final
    $slug = trim(preg_replace('/-+/', '-', $slug), '-');
    
    // Limitar longitud
    $slug = substr($slug, 0, 200);
    
    // Verificar unicidad
    $slug_base = $slug;
    $contador = 1;
    
    while (true) {
        if ($id_excluir) {
            $check_query = "SELECT COUNT(*) as count FROM categoria WHERE slug_categoria = ? AND id_categoria != ?";
            $stmt = $conn->prepare($check_query);
            $stmt->execute([$slug, $id_excluir]);
        } else {
            $check_query = "SELECT COUNT(*) as count FROM categoria WHERE slug_categoria = ?";
            $stmt = $conn->prepare($check_query);
            $stmt->execute([$slug]);
        }
        
        $exists = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($exists == 0) {
            break;
        }
        
        $slug = $slug_base . '-' . $contador;
        $contador++;
        
        // Prevenir bucle infinito
        if ($contador > 1000) {
            $slug = $slug_base . '-' . uniqid();
            break;
        }
    }
    
    return $slug;
}

/**
 * Actualizar categoría existente
 */
function updateCategoria() {
    global $conn;
    
    $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
    $data = json_decode(file_get_contents('php://input'), true);
    
    // VALIDACIÓN DE ENTRADA
    if (!$id || $id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de categoría requerido y debe ser válido']);
        return;
    }
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['error' => 'JSON inválido']);
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
    
    $allowed_fields = [
        'nombre_categoria' => ['type' => 'string', 'max' => 255, 'min' => 2],
        'codigo_categoria' => ['type' => 'string', 'max' => 50],
        'descripcion_categoria' => ['type' => 'string', 'max' => 1000],
        'imagen_categoria' => ['type' => 'string', 'max' => 500],
        'url_imagen_categoria' => ['type' => 'string', 'max' => 500],
        'estado_categoria' => ['type' => 'int', 'values' => [0, 1]],
        'orden' => ['type' => 'int', 'min' => 0]
    ];
    
    $nuevo_slug = null;
    
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
            
            // Si se actualiza el nombre, marcar para regenerar slug
            if ($field === 'nombre_categoria') {
                $nuevo_slug = generarSlugSeguro($value, $conn, $id);
            }
        }
    }
    
    // Agregar slug si se generó uno nuevo
    if ($nuevo_slug !== null) {
        $update_fields[] = "slug_categoria = ?";
        $params[] = $nuevo_slug;
    }
    
    if (empty($update_fields)) {
        http_response_code(400);
        echo json_encode(['error' => 'No hay campos válidos para actualizar']);
        return;
    }
    
    // Agregar fecha de actualización
    $update_fields[] = "fecha_actualizacion_categoria = NOW()";
    $params[] = $id;
    
    // Ejecutar actualización en transacción
    try {
        $conn->beginTransaction();
        
        $query = "UPDATE categoria SET " . implode(', ', $update_fields) . " WHERE id_categoria = ?";
        $stmt = $conn->prepare($query);
        
        if ($stmt->execute($params)) {
            $conn->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Categoría actualizada exitosamente',
                'slug' => $nuevo_slug
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
    
    $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de categoría requerido']);
        return;
    }
    
    // Verificar si hay productos en esta categoría
    $productos_query = "SELECT COUNT(*) as count FROM producto WHERE id_categoria = ? AND estado_producto = 1";
    $stmt = $conn->prepare($productos_query);
    $stmt->execute([$id]);
    $productos_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($productos_count > 0) {
        http_response_code(400);
        echo json_encode([
            'error' => "No se puede eliminar la categoría porque tiene $productos_count productos asociados. Primero mueva o elimine los productos."
        ]);
        return;
    }
    
    // Soft delete - cambiar estado a 0
    $query = "UPDATE categoria SET estado_categoria = 0 WHERE id_categoria = ?";
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
    $new_status = $current_status['estado_categoria'] ? 0 : 1;
    $update_query = "UPDATE categoria SET estado_categoria = ? WHERE id_categoria = ?";
    $stmt = $conn->prepare($update_query);
    
    if ($stmt->execute([$new_status, $id])) {
        echo json_encode([
            'success' => true,
            'message' => $new_status ? 'Categoría activada' : 'Categoría desactivada',
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
    
    $id = (int)($_POST['id_categoria'] ?? $_GET['id'] ?? 0);
    $estado = $_POST['estado'] ?? '';
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de categoría requerido']);
        return;
    }
    
    // Validar estado
    if (!in_array($estado, ['activo', 'inactivo'], true)) {
        http_response_code(400);
        echo json_encode(['error' => 'Estado inválido. Debe ser "activo" o "inactivo"']);
        return;
    }
    
    // Convertir estado a número
    $nuevo_estado = $estado === 'activo' ? 1 : 0;
    
    // Actualizar estado
    $update_query = "UPDATE categoria SET estado_categoria = ? WHERE id_categoria = ?";
    $stmt = $conn->prepare($update_query);
    
    if ($stmt->execute([$nuevo_estado, $id])) {
        // Obtener datos completos de la categoría actualizada
        $query = "
            SELECT 
                c.*,
                COUNT(p.id_producto) as total_productos
            FROM categoria c
            LEFT JOIN producto p ON c.id_categoria = p.id_categoria AND p.estado_producto = 1
            WHERE c.id_categoria = ?
            GROUP BY c.id_categoria
        ";
        
        $stmt = $conn->prepare($query);
        $stmt->execute([$id]);
        $categoria = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Formatear datos
        $categoria['estado_categoria'] = $categoria['estado_categoria'] ? 'activo' : 'inactivo';
        $categoria['fecha_creacion_formato'] = date('d/m/Y', strtotime($categoria['fecha_creacion_categoria']));
        
        echo json_encode([
            'success' => true,
            'message' => $nuevo_estado ? 'Categoría activada' : 'Categoría desactivada',
            'data' => $categoria,
            'Categoria' => $categoria // Alias para compatibilidad
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al cambiar estado de la categoría']);
    }
}

/**
 * Actualizar orden de categorías
 */
function updateOrder() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['categories']) || !is_array($data['categories'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Datos de categorías requeridos']);
        return;
    }
    
    $conn->beginTransaction();
    
    try {
        $update_query = "UPDATE categoria SET orden = ? WHERE id_categoria = ?";
        $stmt = $conn->prepare($update_query);
        
        foreach ($data['categories'] as $index => $category_id) {
            $order = $index + 1;
            $stmt->execute([$order, $category_id]);
        }
        
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Orden de categorías actualizado exitosamente'
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar orden: ' . $e->getMessage()]);
    }
}

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
    $query = "SELECT COUNT(*) as total FROM categoria WHERE estado_categoria = 1";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $stats['active'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Categorías con productos
    $query = "SELECT COUNT(DISTINCT c.id_categoria) as total 
              FROM categoria c 
              INNER JOIN producto p ON c.id_categoria = p.id_categoria 
              WHERE p.estado_producto = 1";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $stats['with_products'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Categoría con más productos
    $query = "SELECT c.nombre_categoria, COUNT(p.id_producto) as total_productos
              FROM categoria c 
              LEFT JOIN producto p ON c.id_categoria = p.id_categoria AND p.estado_producto = 1
              GROUP BY c.id_categoria, c.nombre_categoria
              ORDER BY total_productos DESC 
              LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $top_category = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['most_products'] = $top_category ?: ['nombre_categoria' => 'N/A', 'total_productos' => 0];
    
    // Distribución de productos por categoría (top 5)
    $query = "SELECT c.nombre_categoria, COUNT(p.id_producto) as total_productos
              FROM categoria c 
              LEFT JOIN producto p ON c.id_categoria = p.id_categoria AND p.estado_producto = 1
              WHERE c.estado_categoria = 1
              GROUP BY c.id_categoria, c.nombre_categoria
              ORDER BY total_productos DESC 
              LIMIT 5";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $stats['distribution'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $stats
    ]);
}
?>