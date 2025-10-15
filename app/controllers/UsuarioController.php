<?php
/**
 * CONTROLADOR DE USUARIOS - CRUD COMPLETO
 * Maneja todas las operaciones de usuarios para el admin
 */

session_start();
require_once __DIR__ . '/../../config/conexion.php';

// Headers para respuestas JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Credentials: true');

// Verificar permisos de administrador
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'error' => 'Acceso denegado',
        'debug' => [
            'session_exists' => isset($_SESSION['user_id']),
            'user_id' => $_SESSION['user_id'] ?? 'no definido',
            'rol' => $_SESSION['rol'] ?? 'no definido',
            'required_rol' => 'admin',
            'session_data' => $_SESSION
        ]
    ]);
    exit;
}

// Obtener la acción solicitada
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Log de la acción solicitada
error_log("UsuarioController: Acción solicitada = $action");
error_log("UsuarioController: GET params = " . json_encode($_GET));
error_log("UsuarioController: POST params = " . json_encode($_POST));

try {
    switch ($action) {
        case 'list':
            listUsuarios();
            break;
        case 'get':
            getUsuario();
            break;
        case 'create':
            createUsuario();
            break;
        case 'update':
            updateUsuario();
            break;
        case 'delete':
            deleteUsuario();
            break;
        case 'toggle_status':
            toggleUsuarioStatus();
            break;
        case 'change_password':
            changePassword();
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
 * Listar usuarios con paginación y filtros
 */
function listUsuarios() {
    global $conn;
    
    error_log("UsuarioController::listUsuarios() - Iniciando");
    
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 10);
    $search = $_GET['search'] ?? '';
    $role_filter = $_GET['role'] ?? '';
    $status_filter = $_GET['status'] ?? '';
    
    error_log("Parámetros: page=$page, limit=$limit, search='$search', role='$role_filter', status='$status_filter'");
    
    $offset = ($page - 1) * $limit;
    
    // Construir consulta base
    $where_conditions = [];
    $params = [];
    
    if (!empty($search)) {
        $where_conditions[] = "(nombre_usuario LIKE ? OR apellido_usuario LIKE ? OR email_usuario LIKE ? OR username_usuario LIKE ?)";
        $search_param = "%$search%";
        $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
    }
    
    if (!empty($role_filter)) {
        $where_conditions[] = "rol_usuario = ?";
        $params[] = $role_filter;
    }
    
    if ($status_filter !== '') {
        $where_conditions[] = "status_usuario = ?";
        $params[] = (int)$status_filter;
    }
    
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // Contar total de registros
    $count_query = "SELECT COUNT(*) as total FROM usuario $where_clause";
    error_log("Count query: $count_query");
    error_log("Count params: " . json_encode($params));
    
    $stmt = $conn->prepare($count_query);
    $stmt->execute($params);
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    error_log("Total de usuarios encontrados: $total");
    
    // Obtener usuarios paginados
    $query = "
        SELECT 
            id_usuario,
            username_usuario,
            email_usuario,
            nombre_usuario,
            apellido_usuario,
            telefono_usuario,
            rol_usuario,
            status_usuario,
            verificado_usuario,
            avatar_usuario,
            fecha_registro,
            ultimo_acceso
        FROM usuario 
        $where_clause 
        ORDER BY fecha_registro DESC 
        LIMIT ? OFFSET ?
    ";
    
    $params[] = $limit;
    $params[] = $offset;
    
    error_log("Query principal: $query");
    error_log("Params principales: " . json_encode($params));
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    error_log("Usuarios obtenidos de BD: " . count($usuarios));
    
    if (empty($usuarios)) {
        error_log("⚠️ No se encontraron usuarios en la base de datos");
    }
    
    // Formatear datos para el frontend
    foreach ($usuarios as &$usuario) {
        // Asegurar que todos los campos necesarios están presentes
        $usuario['nombre_completo'] = trim(($usuario['nombre_usuario'] ?? '') . ' ' . ($usuario['apellido_usuario'] ?? ''));
        $usuario['estado_texto'] = $usuario['status_usuario'] ? 'Activo' : 'Inactivo';
        $usuario['rol_texto'] = ucfirst($usuario['rol_usuario'] ?? 'cliente');
        $usuario['ultimo_acceso_formato'] = $usuario['ultimo_acceso'] ? 
            date('d/m/Y H:i', strtotime($usuario['ultimo_acceso'])) : 'Nunca';
        $usuario['fecha_registro_formato'] = date('d/m/Y', strtotime($usuario['fecha_registro']));
        
        // Asegurar campos para la vista
        $usuario['avatar_usuario'] = $usuario['avatar_usuario'] ?? 'default-avatar.png';
        
        // Debug: agregar información adicional
        error_log("Usuario procesado: " . json_encode([
            'id' => $usuario['id_usuario'],
            'nombre' => $usuario['nombre_completo'],
            'email' => $usuario['email_usuario'],
            'rol' => $usuario['rol_usuario']
        ]));
    }
    
    $response = [
        'success' => true,
        'data' => $usuarios,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => ceil($total / $limit),
            'total_records' => $total,
            'per_page' => $limit
        ],
        'debug' => [
            'total_found' => $total,
            'users_returned' => count($usuarios),
            'page' => $page,
            'limit' => $limit
        ]
    ];
    
    error_log("Respuesta final: " . json_encode($response));
    
    echo json_encode($response);
}

/**
 * Obtener un usuario específico
 */
function getUsuario() {
    global $conn;
    
    $id = (int)($_GET['id'] ?? 0);
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de usuario requerido']);
        return;
    }
    
    $query = "
        SELECT 
            id_usuario,
            username_usuario,
            email_usuario,
            nombre_usuario,
            apellido_usuario,
            telefono_usuario,
            fecha_nacimiento,
            genero_usuario,
            rol_usuario,
            status_usuario,
            verificado_usuario,
            avatar_usuario,
            fecha_registro,
            ultimo_acceso
        FROM usuario 
        WHERE id_usuario = ?
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        http_response_code(404);
        echo json_encode(['error' => 'Usuario no encontrado']);
        return;
    }
    
    // No enviar la contraseña
    unset($usuario['password_usuario']);
    
    echo json_encode([
        'success' => true,
        'data' => $usuario
    ]);
}

/**
 * Crear nuevo usuario
 */
function createUsuario() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validar campos requeridos
    $required_fields = ['username_usuario', 'email_usuario', 'nombre_usuario', 'apellido_usuario', 'password_usuario', 'rol_usuario'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "El campo $field es requerido"]);
            return;
        }
    }
    
    // Verificar si el username o email ya existen
    $check_query = "SELECT COUNT(*) as count FROM usuario WHERE username_usuario = ? OR email_usuario = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->execute([$data['username_usuario'], $data['email_usuario']]);
    $exists = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($exists > 0) {
        http_response_code(400);
        echo json_encode(['error' => 'El nombre de usuario o email ya están en uso']);
        return;
    }
    
    // Hash de la contraseña
    $password_hash = password_hash($data['password_usuario'], PASSWORD_DEFAULT);
    
    // Insertar usuario
    $query = "
        INSERT INTO usuario (
            username_usuario, email_usuario, password_usuario, nombre_usuario, 
            apellido_usuario, telefono_usuario, fecha_nacimiento, genero_usuario,
            rol_usuario, status_usuario, verificado_usuario
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";
    
    $params = [
        $data['username_usuario'],
        $data['email_usuario'],
        $password_hash,
        $data['nombre_usuario'],
        $data['apellido_usuario'],
        $data['telefono_usuario'] ?? null,
        $data['fecha_nacimiento'] ?? null,
        $data['genero_usuario'] ?? 'Otro',
        $data['rol_usuario'],
        $data['status_usuario'] ?? 1,
        $data['verificado_usuario'] ?? 1
    ];
    
    $stmt = $conn->prepare($query);
    
    if ($stmt->execute($params)) {
        $new_id = $conn->lastInsertId();
        echo json_encode([
            'success' => true,
            'message' => 'Usuario creado exitosamente',
            'id' => $new_id
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al crear usuario']);
    }
}

/**
 * Actualizar usuario existente
 */
function updateUsuario() {
    global $conn;
    
    $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de usuario requerido']);
        return;
    }
    
    // Verificar que el usuario existe
    $check_query = "SELECT id_usuario FROM usuario WHERE id_usuario = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->execute([$id]);
    
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'Usuario no encontrado']);
        return;
    }
    
    // Construir consulta de actualización dinámicamente
    $update_fields = [];
    $params = [];
    
    $allowed_fields = [
        'username_usuario', 'email_usuario', 'nombre_usuario', 'apellido_usuario',
        'telefono_usuario', 'fecha_nacimiento', 'genero_usuario', 'rol_usuario',
        'status_usuario', 'verificado_usuario'
    ];
    
    foreach ($allowed_fields as $field) {
        if (isset($data[$field])) {
            $update_fields[] = "$field = ?";
            $params[] = $data[$field];
        }
    }
    
    if (empty($update_fields)) {
        http_response_code(400);
        echo json_encode(['error' => 'No hay campos para actualizar']);
        return;
    }
    
    $params[] = $id;
    
    $query = "UPDATE usuario SET " . implode(', ', $update_fields) . " WHERE id_usuario = ?";
    $stmt = $conn->prepare($query);
    
    if ($stmt->execute($params)) {
        echo json_encode([
            'success' => true,
            'message' => 'Usuario actualizado exitosamente'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar usuario']);
    }
}

/**
 * Eliminar usuario (soft delete)
 */
function deleteUsuario() {
    global $conn;
    
    $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de usuario requerido']);
        return;
    }
    
    // No permitir eliminar al usuario actual
    if ($id == $_SESSION['user_id']) {
        http_response_code(400);
        echo json_encode(['error' => 'No puedes eliminar tu propio usuario']);
        return;
    }
    
    // Soft delete - cambiar status a 0
    $query = "UPDATE usuario SET status_usuario = 0 WHERE id_usuario = ?";
    $stmt = $conn->prepare($query);
    
    if ($stmt->execute([$id])) {
        echo json_encode([
            'success' => true,
            'message' => 'Usuario desactivado exitosamente'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al desactivar usuario']);
    }
}

/**
 * Cambiar estado del usuario (activar/desactivar)
 */
function toggleUsuarioStatus() {
    global $conn;
    
    $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de usuario requerido']);
        return;
    }
    
    // No permitir cambiar estado del usuario actual
    if ($id == $_SESSION['user_id']) {
        http_response_code(400);
        echo json_encode(['error' => 'No puedes cambiar tu propio estado']);
        return;
    }
    
    // Obtener estado actual
    $query = "SELECT status_usuario FROM usuario WHERE id_usuario = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$id]);
    $current_status = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$current_status) {
        http_response_code(404);
        echo json_encode(['error' => 'Usuario no encontrado']);
        return;
    }
    
    // Cambiar estado
    $new_status = $current_status['status_usuario'] ? 0 : 1;
    $update_query = "UPDATE usuario SET status_usuario = ? WHERE id_usuario = ?";
    $stmt = $conn->prepare($update_query);
    
    if ($stmt->execute([$new_status, $id])) {
        echo json_encode([
            'success' => true,
            'message' => $new_status ? 'Usuario activado' : 'Usuario desactivado',
            'new_status' => $new_status
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al cambiar estado del usuario']);
    }
}

/**
 * Cambiar contraseña de usuario
 */
function changePassword() {
    global $conn;
    
    $id = (int)($_POST['id'] ?? 0);
    $new_password = $_POST['new_password'] ?? '';
    
    if (!$id || !$new_password) {
        http_response_code(400);
        echo json_encode(['error' => 'ID y nueva contraseña son requeridos']);
        return;
    }
    
    // Verificar que el usuario existe
    $check_query = "SELECT id_usuario FROM usuario WHERE id_usuario = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->execute([$id]);
    
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'Usuario no encontrado']);
        return;
    }
    
    // Hash de la nueva contraseña
    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Actualizar contraseña
    $query = "UPDATE usuario SET password_usuario = ? WHERE id_usuario = ?";
    $stmt = $conn->prepare($query);
    
    if ($stmt->execute([$password_hash, $id])) {
        echo json_encode([
            'success' => true,
            'message' => 'Contraseña actualizada exitosamente'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar contraseña']);
    }
}
?>