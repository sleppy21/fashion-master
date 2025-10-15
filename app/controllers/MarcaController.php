<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Conexión directa a la base de datos
try {
    $host = 'localhost';
    $db_name = 'sleppystore';
    $username = 'root';
    $password = '';
    
    $dsn = "mysql:host=$host;dbname=$db_name;charset=utf8mb4";
    $conn = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    
    switch ($action) {
        case 'list':
            listMarcas($conn);
            break;
        case 'get':
            getMarca($conn);
            break;
        case 'create':
            createMarca($conn);
            break;
        case 'update':
            updateMarca($conn);
            break;
        case 'delete':
            deleteMarca($conn);
            break;
        case 'toggle_status':
            toggleMarcaStatus($conn);
            break;
        case 'change_estado':
            changeMarcaEstado($conn);
            break;
        default:
            echo json_encode([
                'success' => false,
                'error' => 'Acción no válida',
                'action_received' => $action
            ]);
            break;
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}

function listMarcas($conn) {
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 10);
    $offset = ($page - 1) * $limit;
    
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? '';
    
    $where_conditions = [];
    $params = [];
    
    // SIEMPRE filtrar marcas eliminadas (status_marca = 0)
    $where_conditions[] = "m.status_marca = 1";
    
    // Filtro OPCIONAL por ESTADO
    if ($status !== '') {
        if ($status === '1') {
            $status = 'activo';
        } elseif ($status === '0') {
            $status = 'inactivo';
        }
        
        $where_conditions[] = "m.estado_marca = ?";
        $params[] = $status;
    }
    
    // Filtro de búsqueda
    if (!empty($search)) {
        $where_conditions[] = "(m.nombre_marca LIKE ? OR m.descripcion_marca LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    $where_clause = '';
    if (!empty($where_conditions)) {
        $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
    }
    
    // Contar total
    $count_query = "SELECT COUNT(*) as total FROM marca m $where_clause";
    $stmt = $conn->prepare($count_query);
    $stmt->execute($params);
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Obtener marcas con conteo de productos
    $query = "
        SELECT 
            m.*,
            COUNT(p.id_producto) as total_productos
        FROM marca m
        LEFT JOIN producto p ON m.id_marca = p.id_marca AND p.status_producto = 1
        $where_clause
        GROUP BY m.id_marca
        ORDER BY m.id_marca ASC
        LIMIT $limit OFFSET $offset
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $marcas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear datos
    foreach ($marcas as &$marca) {
        $marca['estado_texto'] = $marca['estado_marca'] === 'activo' ? 'Activa' : 'Inactiva';
        $marca['fecha_creacion_formato'] = date('d/m/Y', strtotime($marca['fecha_creacion_marca']));
        $marca['descripcion_corta'] = $marca['descripcion_marca'] ? 
            (strlen($marca['descripcion_marca']) > 100 ? 
                substr($marca['descripcion_marca'], 0, 100) . '...' : 
                $marca['descripcion_marca']) : 
            'Sin descripción';
    }
    
    echo json_encode([
        'success' => true,
        'data' => $marcas,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => ceil($total / $limit),
            'total_items' => (int)$total,
            'items_per_page' => $limit
        ]
    ]);
}

function getMarca($conn) {
    $id = (int)($_GET['id'] ?? 0);
    
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'ID de marca requerido']);
        return;
    }
    
    $query = "
        SELECT 
            m.*,
            COUNT(p.id_producto) as total_productos
        FROM marca m
        LEFT JOIN producto p ON m.id_marca = p.id_marca AND p.status_producto = 1
        WHERE m.id_marca = ?
        GROUP BY m.id_marca
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$id]);
    $marca = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$marca) {
        echo json_encode(['success' => false, 'error' => 'Marca no encontrada']);
        return;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $marca
    ]);
}

function createMarca($conn) {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // Manejar tanto JSON como FormData
    if (!$data) {
        $data = $_POST;
    }
    
    // Validar campos requeridos
    if (empty($data['nombre_marca'])) {
        echo json_encode(['success' => false, 'error' => 'El nombre es requerido']);
        return;
    }
    
    // Verificar si el nombre ya existe
    $check_query = "SELECT COUNT(*) as count FROM marca WHERE nombre_marca = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->execute([$data['nombre_marca']]);
    $exists = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($exists > 0) {
        echo json_encode(['success' => false, 'error' => 'Ya existe una marca con ese nombre']);
        return;
    }
    
    // Generar código automático si no existe
    $codigo = $data['codigo_marca'] ?? 'MARCA-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    // Procesar imagen desde $_FILES (igual que categorías)
    $imagen_filename = 'default-brand.png';
    $url_imagen = '/fashion-master/public/assets/img/default-product.jpg';
    
    if (isset($_FILES['imagen_marca']) && $_FILES['imagen_marca']['error'] === UPLOAD_ERR_OK) {
        $result = processUploadedImage($_FILES['imagen_marca'], 'brands');
        if ($result['success']) {
            $imagen_filename = $result['filename'];
            $url_imagen = '/fashion-master/public/assets/img/brands/' . $imagen_filename;
        }
    }
    
    // Insertar marca
    $query = "
        INSERT INTO marca (
            codigo_marca, nombre_marca, descripcion_marca,
            imagen_marca, url_imagen_marca, status_marca, estado_marca
        ) VALUES (?, ?, ?, ?, ?, ?, ?)
    ";
    
    $params = [
        $codigo,
        $data['nombre_marca'],
        $data['descripcion_marca'] ?? null,
        $imagen_filename,
        $url_imagen,
        $data['status_marca'] ?? 1,
        $data['estado_marca'] ?? 'activo'
    ];
    
    $stmt = $conn->prepare($query);
    
    if ($stmt->execute($params)) {
        $new_id = $conn->lastInsertId();
        
        // Obtener marca recién creada
        $get_query = "SELECT * FROM marca WHERE id_marca = ?";
        $stmt = $conn->prepare($get_query);
        $stmt->execute([$new_id]);
        $new_marca = $stmt->fetch(PDO::FETCH_ASSOC);
        $new_marca['total_productos'] = 0;
        
        echo json_encode([
            'success' => true,
            'message' => 'Marca creada exitosamente',
            'id' => $new_id,
            'data' => $new_marca
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error al crear marca']);
    }
}

function updateMarca($conn) {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        $data = $_POST;
    }
    
    $id = (int)($data['id_marca'] ?? $_GET['id'] ?? 0);
    
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'ID de marca requerido']);
        return;
    }
    
    // Verificar que la marca existe
    $check_query = "SELECT * FROM marca WHERE id_marca = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->execute([$id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$existing) {
        echo json_encode(['success' => false, 'error' => 'Marca no encontrada']);
        return;
    }
    
    // Construir actualización dinámica
    $update_fields = [];
    $params = [];
    
    $allowed_fields = [
        'codigo_marca', 'nombre_marca', 'descripcion_marca',
        'status_marca', 'estado_marca'
    ];
    
    foreach ($allowed_fields as $field) {
        if (isset($data[$field])) {
            $update_fields[] = "$field = ?";
            $params[] = $data[$field];
        }
    }
    
    // Procesar nueva imagen si existe desde $_FILES (igual que categorías)
    if (isset($_FILES['imagen_marca']) && $_FILES['imagen_marca']['error'] === UPLOAD_ERR_OK) {
        $result = processUploadedImage($_FILES['imagen_marca'], 'brands');
        if ($result['success']) {
            $update_fields[] = "imagen_marca = ?";
            $params[] = $result['filename'];
            
            $update_fields[] = "url_imagen_marca = ?";
            $params[] = '/fashion-master/public/assets/img/brands/' . $result['filename'];
        }
    }
    
    if (empty($update_fields)) {
        echo json_encode(['success' => false, 'error' => 'No hay campos para actualizar']);
        return;
    }
    
    $params[] = $id;
    
    $query = "UPDATE marca SET " . implode(', ', $update_fields) . " WHERE id_marca = ?";
    $stmt = $conn->prepare($query);
    
    if ($stmt->execute($params)) {
        // Obtener marca actualizada
        $get_query = "
            SELECT m.*, COUNT(p.id_producto) as total_productos
            FROM marca m
            LEFT JOIN producto p ON m.id_marca = p.id_marca AND p.status_producto = 1
            WHERE m.id_marca = ?
            GROUP BY m.id_marca
        ";
        $stmt = $conn->prepare($get_query);
        $stmt->execute([$id]);
        $updated_marca = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Formatear fecha para el frontend
        if ($updated_marca && isset($updated_marca['fecha_creacion_marca'])) {
            $updated_marca['fecha_creacion_formato'] = date('d/m/Y', strtotime($updated_marca['fecha_creacion_marca']));
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Marca actualizada exitosamente',
            'data' => $updated_marca
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error al actualizar marca']);
    }
}

function deleteMarca($conn) {
    $id = (int)($_POST['id_marca'] ?? $_GET['id'] ?? 0);
    
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'ID de marca requerido']);
        return;
    }
    
    // Verificar si hay productos con esta marca
    $productos_query = "SELECT COUNT(*) as count FROM producto WHERE id_marca = ? AND status_producto = 1";
    $stmt = $conn->prepare($productos_query);
    $stmt->execute([$id]);
    $productos_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($productos_count > 0) {
        echo json_encode([
            'success' => false,
            'error' => "No se puede eliminar la marca porque tiene $productos_count productos asociados"
        ]);
        return;
    }
    
    // Soft delete - cambiar AMBOS campos: estado_marca Y status_marca
    $query = "UPDATE marca SET estado_marca = 'inactivo', status_marca = 0 WHERE id_marca = ?";
    $stmt = $conn->prepare($query);
    
    if ($stmt->execute([$id])) {
        echo json_encode([
            'success' => true,
            'message' => 'Marca eliminada exitosamente'
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error al eliminar marca']);
    }
}

function toggleMarcaStatus($conn) {
    $id = (int)($_POST['id_marca'] ?? $_GET['id'] ?? 0);
    
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'ID de marca requerido']);
        return;
    }
    
    $query = "SELECT status_marca FROM marca WHERE id_marca = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$id]);
    $current_status = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$current_status) {
        echo json_encode(['success' => false, 'error' => 'Marca no encontrada']);
        return;
    }
    
    $new_status = $current_status['status_marca'] ? 0 : 1;
    $update_query = "UPDATE marca SET status_marca = ? WHERE id_marca = ?";
    $stmt = $conn->prepare($update_query);
    
    if ($stmt->execute([$new_status, $id])) {
        echo json_encode([
            'success' => true,
            'message' => $new_status ? 'Marca activada' : 'Marca desactivada',
            'new_status' => $new_status
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error al cambiar estado']);
    }
}

function changeMarcaEstado($conn) {
    $id = (int)($_POST['id_marca'] ?? $_GET['id'] ?? 0);
    
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'ID requerido']);
        return;
    }
    
    $query = "SELECT estado_marca FROM marca WHERE id_marca = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$id]);
    $current = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$current) {
        echo json_encode(['success' => false, 'error' => 'Marca no encontrada']);
        return;
    }
    
    $new_estado = $current['estado_marca'] === 'activo' ? 'inactivo' : 'activo';
    
    $update_query = "UPDATE marca SET estado_marca = ? WHERE id_marca = ?";
    $stmt = $conn->prepare($update_query);
    
    if ($stmt->execute([$new_estado, $id])) {
        $get_query = "
            SELECT m.*, COUNT(p.id_producto) as total_productos
            FROM marca m
            LEFT JOIN producto p ON m.id_marca = p.id_marca AND p.status_producto = 1
            WHERE m.id_marca = ?
            GROUP BY m.id_marca
        ";
        $stmt = $conn->prepare($get_query);
        $stmt->execute([$id]);
        $updated_marca = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Formatear fecha para el frontend
        if ($updated_marca && isset($updated_marca['fecha_creacion_marca'])) {
            $updated_marca['fecha_creacion_formato'] = date('d/m/Y', strtotime($updated_marca['fecha_creacion_marca']));
        }
        
        echo json_encode([
            'success' => true,
            'message' => $new_estado === 'activo' ? 'Marca activada' : 'Marca desactivada',
            'data' => $updated_marca
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error al cambiar estado']);
    }
}

function processBase64Image($base64String, $folder = 'brands') {
    try {
        if (strpos($base64String, ';base64,') !== false) {
            list($type, $base64String) = explode(';', $base64String);
            list(, $base64String) = explode(',', $base64String);
        }
        
        $imageData = base64_decode($base64String);
        
        if ($imageData === false) {
            return ['success' => false, 'error' => 'Error decodificando imagen'];
        }
        
        $filename = 'marca_' . time() . '_' . uniqid() . '.png';
        $upload_dir = __DIR__ . '/../../public/assets/img/' . $folder . '/';
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $filepath = $upload_dir . $filename;
        
        if (file_put_contents($filepath, $imageData)) {
            return [
                'success' => true,
                'filename' => $filename,
                'url' => '/fashion-master/public/assets/img/' . $folder . '/' . $filename
            ];
        }
        
        return ['success' => false, 'error' => 'Error guardando imagen'];
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Procesar imagen subida desde $_FILES (igual que CategoryController)
 */
function processUploadedImage($file, $folder = 'brands') {
    try {
        // Verificar errores de upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'Error al subir archivo'];
        }
        
        // Validar tamaño (5MB)
        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'error' => 'La imagen excede el tamaño máximo de 5MB'];
        }
        
        // Validar tipo de archivo
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
            return ['success' => false, 'error' => 'Tipo de archivo no permitido. Solo JPG, PNG, GIF, WebP'];
        }
        
        // Generar nombre único
        $extension = $allowed_types[$mime_type];
        $filename = 'marca-' . time() . '-' . rand(1000000, 9999999) . '.' . $extension;
        
        // Directorio de destino
        $upload_dir = __DIR__ . '/../../public/assets/img/' . $folder . '/';
        
        // Crear directorio si no existe
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                return ['success' => false, 'error' => 'No se pudo crear el directorio'];
            }
        }
        
        $filepath = $upload_dir . $filename;
        
        // Mover archivo
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return ['success' => false, 'error' => 'Error al guardar la imagen'];
        }
        
        return [
            'success' => true,
            'filename' => $filename,
            'path' => $filepath
        ];
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}
?>
