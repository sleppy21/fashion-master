<?php
/**
 * VALIDACIÓN DE LOGIN
 * Procesa las credenciales y crea la sesión
 */

session_start();
require_once __DIR__ . '/../../config/conexion.php';

// Verificar que sea POST
if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/auth/login.php');
    exit;
}

// Obtener datos del formulario
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']);

// Validar campos
if(empty($username) || empty($password)) {
    $_SESSION['login_error'] = 'Por favor complete todos los campos';
    $_SESSION['last_username'] = $username;
    header('Location: ../views/auth/login.php');
    exit;
}

try {
    // Buscar usuario por username o email
    $query = "SELECT * FROM usuario WHERE (username_usuario = ? OR email_usuario = ?) AND status_usuario = 1";
    $usuarios = executeQuery($query, [$username, $username]);
    
    if(empty($usuarios)) {
        $_SESSION['login_error'] = 'Usuario no encontrado o cuenta inactiva';
        $_SESSION['last_username'] = $username;
        header('Location: ../views/auth/login.php');
        exit;
    }
    
    $usuario = $usuarios[0];
    
    // Verificar contraseña
    $password_valid = false;
    
    // Primero verificar si es contraseña simple (sin hash)
    if($password === $usuario['password_usuario']) {
        $password_valid = true;
    } 
    // Luego verificar si es contraseña hasheada
    else if(password_verify($password, $usuario['password_usuario'])) {
        $password_valid = true;
    }
    
    if(!$password_valid) {
        $_SESSION['login_error'] = 'Contraseña incorrecta';
        $_SESSION['last_username'] = $username;
        header('Location: ../views/auth/login.php');
        exit;
    }
    
    // Verificar si está verificado
    if(!$usuario['verificado_usuario']) {
        $_SESSION['login_error'] = 'Tu cuenta no ha sido verificada. Revisa tu email.';
        $_SESSION['last_username'] = $username;
        header('Location: ../views/auth/login.php');
        exit;
    }
    
    // Login exitoso - crear sesión
    $_SESSION['user_id'] = $usuario['id_usuario'];
    $_SESSION['username'] = $usuario['username_usuario'];
    $_SESSION['email'] = $usuario['email_usuario'];
    $_SESSION['nombre'] = $usuario['nombre_usuario'];
    $_SESSION['apellido'] = $usuario['apellido_usuario'];
    $_SESSION['rol'] = $usuario['rol_usuario'];
    $_SESSION['login_time'] = time();
    
    // Actualizar último acceso
    executeNonQuery("UPDATE usuario SET ultimo_acceso = NOW() WHERE id_usuario = ?", [$usuario['id_usuario']]);
    
    // Si marcó "recordar", establecer cookie (opcional)
    if($remember) {
        // Cookie por 30 días
        $remember_token = bin2hex(random_bytes(32));
        setcookie('remember_token', $remember_token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
        
        // Guardar token en base de datos (requiere tabla remember_tokens)
        // executeNonQuery("INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, ?)", 
        //     [$usuario['id_usuario'], hash('sha256', $remember_token), date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60))]);
    }
    
    // Registrar en sesiones (opcional - para tracking)
    executeNonQuery("INSERT INTO sesion (id_usuario, ip_cliente, user_agent) VALUES (?, ?, ?)", [
        $usuario['id_usuario'],
        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ]);
    
    // Mensaje de bienvenida
    $_SESSION['welcome_message'] = "¡Bienvenido de vuelta, " . $usuario['nombre_usuario'] . "!";
    
    // Redirigir según el rol
    switch($usuario['rol_usuario']) {
        case 'admin':
            $redirect_url = '../../admin/dashboard.php';
            // Si no existe admin, ir a index
            if(!file_exists(__DIR__ . '/../../admin/dashboard.php')) {
                $redirect_url = '../../index.php';
            }
            break;
            
        case 'vendedor':
            $redirect_url = '../../seller/dashboard.php';
            // Si no existe seller, ir a index
            if(!file_exists(__DIR__ . '/../../seller/dashboard.php')) {
                $redirect_url = '../../index.php';
            }
            break;
            
        default:
            $redirect_url = '../../index.php';
    }
    
    // Verificar si hay URL de retorno
    if(isset($_SESSION['return_url'])) {
        $redirect_url = $_SESSION['return_url'];
        unset($_SESSION['return_url']);
    }
    
    header('Location: ' . $redirect_url);
    exit;
    
} catch(Exception $e) {
    error_log("Error en login: " . $e->getMessage());
    $_SESSION['login_error'] = 'Error interno del servidor. Intente nuevamente.';
    $_SESSION['last_username'] = $username;
    header('Location: ../views/auth/login.php');
    exit;
}
?>