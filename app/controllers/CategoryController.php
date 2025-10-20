<?php
// Deshabilitar salida de errores para no romper JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Usar error_log de PHP por defecto
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

/**
 * 🏷️ GENERAR CÓDIGO AUTOMÁTICO PARA CATEGORÍA
 * Genera código en formato: CAT-001, CAT-002, etc.
 * 
 * @param string $nombre - Nombre de la categoría
 * @param PDO $conn - Conexión a la base de datos
 * @return string - Código único generado
 */
function generarCodigoCategoria($nombre, $conn) {
    // Obtener el último número de código
    $stmt = $conn->query("SELECT codigo_categoria FROM categoria 
                          WHERE codigo_categoria LIKE 'CAT-%' 
                          ORDER BY id_categoria DESC LIMIT 1");
    $ultimo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($ultimo && preg_match('/CAT-(\d+)/', $ultimo['codigo_categoria'], $matches)) {
        $numero = intval($matches[1]) + 1;
    } else {
        // Buscar el ID más alto para empezar desde ahí
        $stmt = $conn->query("SELECT MAX(id_categoria) as max_id FROM categoria");
        $maxId = $stmt->fetch(PDO::FETCH_ASSOC);
        $numero = ($maxId['max_id'] ?? 0) + 1;
    }
    
    return 'CAT-' . str_pad($numero, 3, '0', STR_PAD_LEFT);
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
            listCategorias($conn);
            break;
        case 'get':
            getCategoria($conn);
            break;
        case 'create':
            createCategoria($conn);
            break;
        case 'update':
            updateCategoria($conn);
            break;
        case 'delete':
            deleteCategoria($conn);
            break;
        case 'toggle_status':
            toggleCategoriaStatus($conn);
            break;
        case 'change_estado':
            changeCategoriaEstado($conn);
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

function listCategorias($conn) {
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 10);
    $offset = ($page - 1) * $limit;
    
    // Obtener parámetros de filtros y búsqueda
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? ''; // filtro por estado (activo/inactivo)
    $fecha = $_GET['fecha'] ?? ''; // Filtro por fecha de creación
    
    // Construir WHERE clause
    $where_conditions = [];
    $params = [];
    
    // FILTRO OBLIGATORIO: Solo categorías NO eliminadas (status_categoria = 1)
    $where_conditions[] = "c.status_categoria = 1";
    
    // Filtro OPCIONAL por ESTADO (activo/inactivo)
    if ($status !== '') {
        if ($status === '1') {
            $status = 'activo';
        } elseif ($status === '0') {
            $status = 'inactivo';
        }
        
        $where_conditions[] = "c.estado_categoria = ?";
        $params[] = $status;
    }
    
    // Filtro de búsqueda por nombre
    if (!empty($search)) {
        $where_conditions[] = "c.nombre_categoria LIKE ?";
        $params[] = "%$search%";
    }
    
    // Filtro por fecha de creación
    if (!empty($fecha)) {
        if (strpos($fecha, ' to ') !== false) {
            list($fecha_inicio, $fecha_fin) = explode(' to ', $fecha);
            $fecha_inicio = trim($fecha_inicio);
            $fecha_fin = trim($fecha_fin);
            
            $where_conditions[] = "DATE(c.fecha_creacion_categoria) BETWEEN ? AND ?";
            $params[] = $fecha_inicio;
            $params[] = $fecha_fin;
        } else {
            $where_conditions[] = "DATE(c.fecha_creacion_categoria) = ?";
            $params[] = $fecha;
        }
    }
    
    // Construir query base
    $where_clause = '';
    if (!empty($where_conditions)) {
        $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
    }
    
    // Contar total de categorías con filtros
    $count_query = "
        SELECT COUNT(*) as total 
        FROM categoria c
        $where_clause
    ";
    
    $stmt = $conn->prepare($count_query);
    $stmt->execute($params);
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Obtener categorías con filtros
    $query = "
        SELECT 
            c.id_categoria,
            c.codigo_categoria,
            c.nombre_categoria,
            c.descripcion_categoria,
            c.imagen_categoria,
            c.url_imagen_categoria,
            c.status_categoria,
            c.estado_categoria,
            c.fecha_creacion_categoria,
            c.fecha_actualizacion_categoria,
            (SELECT COUNT(*) FROM producto p WHERE p.id_categoria = c.id_categoria AND p.status_producto = 1) as total_productos
        FROM categoria c
        $where_clause
        ORDER BY c.id_categoria DESC
        LIMIT $limit OFFSET $offset
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear datos
    foreach ($categorias as &$categoria) {
        $categoria['estado_texto'] = $categoria['estado_categoria'] === 'activo' ? 'Activo' : 'Inactivo';
        $categoria['fecha_creacion_formato'] = date('d/m/Y', strtotime($categoria['fecha_creacion_categoria']));
        
        // CORREGIR URLs DE IMÁGENES
        if (empty($categoria['url_imagen_categoria']) || 
            strpos($categoria['url_imagen_categoria'], 'placeholder') !== false ||
            strpos($categoria['url_imagen_categoria'], 'via.placeholder') !== false) {
            
            if (!empty($categoria['imagen_categoria']) && 
                $categoria['imagen_categoria'] !== 'default-category.png') {
                $image_path = $_SERVER['DOCUMENT_ROOT'] . '/fashion-master/public/assets/img/categories/' . $categoria['imagen_categoria'];
                if (file_exists($image_path)) {
                    $categoria['url_imagen_categoria'] = '/fashion-master/public/assets/img/categories/' . $categoria['imagen_categoria'];
                } else {
                    $categoria['url_imagen_categoria'] = '/fashion-master/public/assets/img/default-category.png';
                    $categoria['imagen_categoria'] = 'default-category.png';
                }
            } else {
                $categoria['url_imagen_categoria'] = '/fashion-master/public/assets/img/default-category.png';
            }
        }
        
        if (empty($categoria['imagen_categoria'])) {
            $categoria['imagen_categoria'] = 'default-category.png';
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => $categorias,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => ceil($total / $limit),
            'total_items' => (int)$total,
            'items_per_page' => $limit
        ],
        'filters_applied' => [
            'search' => $search,
            'status' => $status
        ]
    ]);
}

function getCategoria($conn) {
    $id = (int)($_GET['id'] ?? 0);
    
    if (!$id) {
        echo json_encode([
            'success' => false,
            'error' => 'ID de categoría requerido'
        ]);
        return;
    }
    
    try {
        $query = "
            SELECT 
                c.*,
                (SELECT COUNT(*) FROM producto p WHERE p.id_categoria = c.id_categoria AND p.status_producto = 1) as total_productos
            FROM categoria c
            WHERE c.id_categoria = ?
        ";
        
        $stmt = $conn->prepare($query);
        $stmt->execute([$id]);
        $categoria = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$categoria) {
            echo json_encode([
                'success' => false,
                'error' => 'Categoría no encontrada'
            ]);
            return;
        }
        
        // CORREGIR URLs DE IMÁGENES
        if (empty($categoria['url_imagen_categoria']) || 
            strpos($categoria['url_imagen_categoria'], 'placeholder') !== false) {
            
            if (!empty($categoria['imagen_categoria']) && 
                $categoria['imagen_categoria'] !== 'default-category.png') {
                $image_path = $_SERVER['DOCUMENT_ROOT'] . '/fashion-master/public/assets/img/categories/' . $categoria['imagen_categoria'];
                if (file_exists($image_path)) {
                    $categoria['url_imagen_categoria'] = '/fashion-master/public/assets/img/categories/' . $categoria['imagen_categoria'];
                } else {
                    $categoria['url_imagen_categoria'] = '/fashion-master/public/assets/img/default-category.png';
                    $categoria['imagen_categoria'] = 'default-category.png';
                }
            } else {
                $categoria['url_imagen_categoria'] = '/fashion-master/public/assets/img/default-category.png';
            }
        }
        
        if (empty($categoria['imagen_categoria'])) {
            $categoria['imagen_categoria'] = 'default-category.png';
        }
        
        echo json_encode([
            'success' => true,
            'category' => $categoria
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

function createCategoria($conn) {
    try {
        $nombre = $_POST['nombre_categoria'] ?? '';
        $descripcion = $_POST['descripcion_categoria'] ?? '';
        $status_categoria = isset($_POST['status_categoria']) ? (int)$_POST['status_categoria'] : 1;
        $estado = $_POST['estado_categoria'] ?? 'activo';
        
        if (empty($nombre)) {
            echo json_encode([
                'success' => false,
                'error' => 'El nombre de la categoría es requerido'
            ]);
            return;
        }
        
        // Generar código automático
        $codigo = generarCodigoCategoria($nombre, $conn);
        
        // Manejar imagen
        $imagen_nombre = 'default-category.png';
        $url_imagen = '/fashion-master/public/assets/img/default-category.png';
        
        if (isset($_FILES['imagen_categoria']) && $_FILES['imagen_categoria']['error'] === UPLOAD_ERR_OK) {
            $imagen_nombre = handleImageUpload($_FILES['imagen_categoria'], 'categories');
            $url_imagen = '/fashion-master/public/assets/img/categories/' . $imagen_nombre;
        }
        
        $query = "
            INSERT INTO categoria (
                codigo_categoria, nombre_categoria, descripcion_categoria,
                imagen_categoria, url_imagen_categoria, status_categoria, 
                estado_categoria, fecha_creacion_categoria
            ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ";
        
        $stmt = $conn->prepare($query);
        
        if ($stmt->execute([
            $codigo,
            $nombre,
            $descripcion,
            $imagen_nombre,
            $url_imagen,
            $status_categoria,
            $estado
        ])) {
            $categoriaId = $conn->lastInsertId();
            
            // Obtener la categoría completa
            $getQuery = "
                SELECT 
                    c.*,
                    (SELECT COUNT(*) FROM producto p WHERE p.id_categoria = c.id_categoria AND p.status_producto = 1) as total_productos
                FROM categoria c
                WHERE c.id_categoria = ?
            ";
            
            $getStmt = $conn->prepare($getQuery);
            $getStmt->execute([$categoriaId]);
            $fullCategory = $getStmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'message' => 'Categoría creada exitosamente',
                'id' => $categoriaId,
                'category_id' => $categoriaId,
                'category' => $fullCategory
            ]);
        } else {
            throw new Exception('Error al insertar categoría');
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Error al crear categoría: ' . $e->getMessage()
        ]);
    }
}

function updateCategoria($conn) {
    $id = (int)($_POST['id_categoria'] ?? $_POST['id'] ?? 0);
    
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'ID requerido']);
        return;
    }
    
    try {
        // Obtener categoría actual
        $query = "SELECT imagen_categoria, url_imagen_categoria, status_categoria FROM categoria WHERE id_categoria = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$id]);
        $current_categoria = $stmt->fetch();
        
        if (!$current_categoria) {
            echo json_encode(['success' => false, 'error' => 'Categoría no encontrada']);
            return;
        }
        
        $nombre = $_POST['nombre_categoria'] ?? '';
        $descripcion = $_POST['descripcion_categoria'] ?? '';
        $status_categoria = isset($_POST['status_categoria']) ? (int)$_POST['status_categoria'] : $current_categoria['status_categoria'];
        $estado = $_POST['estado_categoria'] ?? 'activo';
        
        // Manejar imagen
        $imagen_nombre = $current_categoria['imagen_categoria'];
        $url_imagen = $current_categoria['url_imagen_categoria'];
        
        if (isset($_FILES['imagen_categoria']) && $_FILES['imagen_categoria']['error'] === UPLOAD_ERR_OK) {
            // Eliminar imagen anterior si no es la default
            if ($imagen_nombre && $imagen_nombre !== 'default-category.png') {
                $old_path = $_SERVER['DOCUMENT_ROOT'] . '/fashion-master/public/assets/img/categories/' . $imagen_nombre;
                if (file_exists($old_path)) {
                    unlink($old_path);
                }
            }
            
            $imagen_nombre = handleImageUpload($_FILES['imagen_categoria'], 'categories');
            $url_imagen = '/fashion-master/public/assets/img/categories/' . $imagen_nombre;
        }
        
        $query = "UPDATE categoria SET 
            nombre_categoria = ?, 
            descripcion_categoria = ?, 
            imagen_categoria = ?,
            url_imagen_categoria = ?,
            status_categoria = ?,
            estado_categoria = ?,
            fecha_actualizacion_categoria = NOW()
            WHERE id_categoria = ?";
        
        $stmt = $conn->prepare($query);
        
        if ($stmt->execute([
            $nombre,
            $descripcion,
            $imagen_nombre,
            $url_imagen,
            $status_categoria,
            $estado,
            $id
        ])) {
            // Obtener categoría completa
            $getQuery = "
                SELECT 
                    c.*,
                    (SELECT COUNT(*) FROM producto p WHERE p.id_categoria = c.id_categoria AND p.status_producto = 1) as total_productos
                FROM categoria c
                WHERE c.id_categoria = ?
            ";
            
            $getStmt = $conn->prepare($getQuery);
            $getStmt->execute([$id]);
            $fullCategory = $getStmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'message' => 'Categoría actualizada exitosamente',
                'id' => $id,
                'category' => $fullCategory
            ]);
        } else {
            throw new Exception('Error al actualizar categoría');
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Error al actualizar categoría: ' . $e->getMessage()
        ]);
    }
}

function deleteCategoria($conn) {
    $id = (int)($_POST['id'] ?? 0);
    
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'ID requerido']);
        return;
    }
    
    try {
        // Verificar si tiene productos asociados
        $checkQuery = "SELECT COUNT(*) as total FROM producto WHERE id_categoria = ? AND status_producto = 1";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->execute([$id]);
        $result = $checkStmt->fetch();
        
        if ($result['total'] > 0) {
            echo json_encode([
                'success' => false,
                'error' => 'No se puede eliminar. Esta categoría tiene ' . $result['total'] . ' producto(s) asociado(s).'
            ]);
            return;
        }
        
        $query = "UPDATE categoria SET status_categoria = 0 WHERE id_categoria = ?";
        $stmt = $conn->prepare($query);
        
        if ($stmt->execute([$id])) {
            echo json_encode(['success' => true, 'message' => 'Categoría eliminada']);
        } else {
            throw new Exception('Error al eliminar');
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function toggleCategoriaStatus($conn) {
    $id = (int)($_POST['id'] ?? 0);
    $status = (int)($_POST['status'] ?? 1);
    
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'ID requerido']);
        return;
    }
    
    try {
        $query = "UPDATE categoria SET status_categoria = ? WHERE id_categoria = ?";
        $stmt = $conn->prepare($query);
        
        if ($stmt->execute([$status, $id])) {
            $getQuery = "
                SELECT 
                    c.*,
                    (SELECT COUNT(*) FROM producto p WHERE p.id_categoria = c.id_categoria AND p.status_producto = 1) as total_productos
                FROM categoria c
                WHERE c.id_categoria = ?
            ";
            
            $getStmt = $conn->prepare($getQuery);
            $getStmt->execute([$id]);
            $fullCategory = $getStmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'message' => 'Estado actualizado',
                'category' => $fullCategory
            ]);
        } else {
            throw new Exception('Error al actualizar estado');
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function changeCategoriaEstado($conn) {
    $id = (int)($_POST['id'] ?? 0);
    $estado = $_POST['estado'] ?? '';
    
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'ID requerido']);
        return;
    }
    
    if (!in_array($estado, ['activo', 'inactivo'])) {
        echo json_encode(['success' => false, 'error' => 'Estado inválido']);
        return;
    }
    
    try {
        $query = "UPDATE categoria SET estado_categoria = ? WHERE id_categoria = ?";
        $stmt = $conn->prepare($query);
        
        if ($stmt->execute([$estado, $id])) {
            $getQuery = "
                SELECT 
                    c.*,
                    (SELECT COUNT(*) FROM producto p WHERE p.id_categoria = c.id_categoria AND p.status_producto = 1) as total_productos
                FROM categoria c
                WHERE c.id_categoria = ?
            ";
            
            $getStmt = $conn->prepare($getQuery);
            $getStmt->execute([$id]);
            $fullCategory = $getStmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'message' => 'Estado de categoría actualizado',
                'category' => $fullCategory
            ]);
        } else {
            throw new Exception('Error al actualizar estado');
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function handleImageUpload($file, $folder = 'categories') {
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_extensions)) {
        throw new Exception('Tipo de archivo no permitido');
    }
    
    $max_size = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $max_size) {
        throw new Exception('El archivo es demasiado grande. Tamaño máximo: 5MB');
    }
    
    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/fashion-master/public/assets/img/' . $folder . '/';
    
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $unique_name = 'categoria_' . uniqid() . '.' . $file_extension;
    $upload_path = $upload_dir . $unique_name;
    
    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        throw new Exception('Error al subir la imagen');
    }
    
    return $unique_name;
}