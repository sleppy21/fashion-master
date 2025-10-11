<?php
/**
 * Controlador de Autenticación
 * Maneja todas las operaciones de login, registro, recuperación y cambio de contraseña
 */

// Cargar configuración de rutas
require_once __DIR__ . '/../../config/path.php';

class AuthController {
    private $db;
    
    public function __construct() {
        // Conexión a la base de datos
        $host = 'localhost';
        $dbname = 'sleppystore';
        $username = 'root';
        $password = '';
        
        try {
            $this->db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }
    
    /**
     * Procesar login de usuario
     */
    public function login() {
        session_start();
        
        if($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('login.php'));
            exit;
        }
        
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']) && $_POST['remember'] == '1';
        
        // Validaciones
        if(empty($username) || empty($password)) {
            $_SESSION['error'] = 'Por favor completa todos los campos';
            $_SESSION['last_username'] = $username;
            header('Location: ' . url('login.php'));
            exit;
        }
        
        try {
            // Buscar usuario por username o email
            $stmt = $this->db->prepare("
                SELECT * FROM usuario 
                WHERE (username_usuario = :username OR email_usuario = :username) 
                AND status_usuario = 1
            ");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verificar usuario y contraseña
            if(!$user || $user['password_usuario'] !== $password) {
                $_SESSION['error'] = 'Credenciales incorrectas';
                $_SESSION['last_username'] = $username;
                header('Location: ' . url('login.php'));
                exit;
            }
            
            // Login exitoso - crear sesión
            $_SESSION['user_id'] = $user['id_usuario'];
            $_SESSION['username'] = $user['username_usuario'];
            $_SESSION['email'] = $user['email_usuario'];
            $_SESSION['nombre_completo'] = $user['nombre_usuario'] . ' ' . $user['apellido_usuario'];
            $_SESSION['rol'] = $user['rol_usuario'];
            
            // Actualizar último acceso
            $stmt = $this->db->prepare("UPDATE usuario SET ultimo_acceso = NOW() WHERE id_usuario = :id");
            $stmt->execute(['id' => $user['id_usuario']]);
            
            // Si marcó "Recordar sesión", crear cookie
            if($remember) {
                $token = bin2hex(random_bytes(32));
                
                // Guardar token en cookie (30 días)
                setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/');
                setcookie('user_id', $user['id_usuario'], time() + (30 * 24 * 60 * 60), '/');
            }
            
            // Redirigir según el rol
            if($user['rol_usuario'] === 'admin') {
                header('Location: ' . url('index.php'));
            } else {
                header('Location: ' . url('index.php'));
            }
            exit;
            
        } catch(PDOException $e) {
            $_SESSION['error'] = 'Error en el sistema. Intenta nuevamente.';
            header('Location: ' . url('login.php'));
            exit;
        }
    }
    
    /**
     * Login desde cookie (recordar sesión)
     */
    public function loginFromCookie() {
        if(!isset($_COOKIE['remember_token']) || !isset($_COOKIE['user_id'])) {
            return false;
        }
        
        try {
            $userId = $_COOKIE['user_id'];
            
            // Verificar que el usuario existe y está activo
            $stmt = $this->db->prepare("SELECT * FROM usuario WHERE id_usuario = :id AND status_usuario = 1");
            $stmt->execute(['id' => $userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if($user) {
                session_start();
                $_SESSION['user_id'] = $user['id_usuario'];
                $_SESSION['username'] = $user['username_usuario'];
                $_SESSION['email'] = $user['email_usuario'];
                $_SESSION['nombre_completo'] = $user['nombre_usuario'] . ' ' . $user['apellido_usuario'];
                $_SESSION['rol'] = $user['rol_usuario'];
                
                // Actualizar último acceso
                $stmt = $this->db->prepare("UPDATE usuario SET ultimo_acceso = NOW() WHERE id_usuario = :id");
                $stmt->execute(['id' => $user['id_usuario']]);
                
                // Redirigir al inicio
                header('Location: ' . url('index.php'));
                exit;
            }
        } catch(PDOException $e) {
            return false;
        }
        
        return false;
    }
    
    /**
     * Registrar nuevo usuario
     */
    public function register() {
        session_start();
        
        if($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('register.php'));
            exit;
        }
        
        // Obtener datos del formulario
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? null;
        $genero = $_POST['genero'] ?? 'Otro';
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';
        $terms = isset($_POST['terms']) && $_POST['terms'] == '1';
        
        // Guardar datos del formulario para repoblar en caso de error
        $_SESSION['form_data'] = $_POST;
        
        // Validaciones
        if(empty($nombre) || empty($apellido) || empty($username) || empty($email) || empty($password)) {
            $_SESSION['error'] = 'Por favor completa todos los campos obligatorios';
            header('Location: ' . url('register.php'));
            exit;
        }
        
        if($password !== $password_confirm) {
            $_SESSION['error'] = 'Las contraseñas no coinciden';
            header('Location: ' . url('register.php'));
            exit;
        }
        
        if(strlen($password) < 6) {
            $_SESSION['error'] = 'La contraseña debe tener al menos 6 caracteres';
            header('Location: ' . url('register.php'));
            exit;
        }
        
        if(!$terms) {
            $_SESSION['error'] = 'Debes aceptar los términos y condiciones';
            header('Location: ' . url('register.php'));
            exit;
        }
        
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'El email no es válido';
            header('Location: ' . url('register.php'));
            exit;
        }
        
        try {
            // Verificar si el username ya existe
            $stmt = $this->db->prepare("SELECT id_usuario FROM usuario WHERE username_usuario = :username");
            $stmt->execute(['username' => $username]);
            if($stmt->fetch()) {
                $_SESSION['error'] = 'El nombre de usuario ya está en uso';
                header('Location: ' . url('register.php'));
                exit;
            }
            
            // Verificar si el email ya existe
            $stmt = $this->db->prepare("SELECT id_usuario FROM usuario WHERE email_usuario = :email");
            $stmt->execute(['email' => $email]);
            if($stmt->fetch()) {
                $_SESSION['error'] = 'El email ya está registrado';
                header('Location: ' . url('register.php'));
                exit;
            }
            
            // Insertar nuevo usuario
            $stmt = $this->db->prepare("
                INSERT INTO usuario (
                    username_usuario, password_usuario, email_usuario, 
                    nombre_usuario, apellido_usuario, telefono_usuario, 
                    fecha_nacimiento, genero_usuario, rol_usuario, 
                    status_usuario, fecha_registro
                ) VALUES (
                    :username, :password, :email, 
                    :nombre, :apellido, :telefono, 
                    :fecha_nacimiento, :genero, 'cliente', 
                    1, NOW()
                )
            ");
            
            $result = $stmt->execute([
                'username' => $username,
                'password' => $password, // En producción, usar password_hash()
                'email' => $email,
                'nombre' => $nombre,
                'apellido' => $apellido,
                'telefono' => $telefono ?: null,
                'fecha_nacimiento' => $fecha_nacimiento ?: null,
                'genero' => $genero
            ]);
            
            if($result) {
                unset($_SESSION['form_data']);
                $_SESSION['success'] = '¡Registro exitoso! Ya puedes iniciar sesión';
                header('Location: ' . url('login.php'));
                exit;
            }
            
        } catch(PDOException $e) {
            $_SESSION['error'] = 'Error al registrar usuario: ' . $e->getMessage();
            header('Location: ' . url('register.php'));
            exit;
        }
    }
    
    /**
     * Solicitar recuperación de contraseña
     */
    public function forgotPassword() {
        session_start();
        
        if($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('forgot-password.php'));
            exit;
        }
        
        $email = trim($_POST['email'] ?? '');
        
        if(empty($email)) {
            $_SESSION['error'] = 'Por favor ingresa tu email';
            header('Location: ' . url('forgot-password.php'));
            exit;
        }
        
        try {
            // Buscar usuario por email
            $stmt = $this->db->prepare("SELECT * FROM usuario WHERE email_usuario = :email AND status_usuario = 1");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if(!$user) {
                // Por seguridad, no revelar si el email existe o no
                $_SESSION['success'] = 'Si el email está registrado, recibirás un enlace de recuperación';
                header('Location: ' . url('forgot-password.php'));
                exit;
            }
            
            // Generar token único
            $token = bin2hex(random_bytes(32));
            $ip = $_SERVER['REMOTE_ADDR'] ?? null;
            
            // Eliminar tokens anteriores del usuario
            $stmt = $this->db->prepare("DELETE FROM password_reset_tokens WHERE id_usuario = :id");
            $stmt->execute(['id' => $user['id_usuario']]);
            
            // Guardar nuevo token
            $stmt = $this->db->prepare("
                INSERT INTO password_reset_tokens (id_usuario, token, email, ip_address, fecha_creacion, fecha_expiracion)
                VALUES (:id, :token, :email, :ip, NOW(), DATE_ADD(NOW(), INTERVAL 1 HOUR))
            ");
            $stmt->execute([
                'id' => $user['id_usuario'],
                'token' => $token,
                'email' => $email,
                'ip' => $ip
            ]);
            
            // Enviar email con el enlace
            require_once __DIR__ . '/../helpers/EmailHelper.php';
            $emailHelper = new EmailHelper();
            $resetLink = absolute_url('reset-password.php?token=' . $token);
            
            $sent = $emailHelper->sendPasswordResetEmail(
                $email, 
                $user['nombre_usuario'], 
                $resetLink
            );
            
            $_SESSION['success'] = 'Te hemos enviado un email con las instrucciones para recuperar tu contraseña';
            header('Location: ' . url('forgot-password.php'));
            exit;
            
        } catch(PDOException $e) {
            $_SESSION['error'] = 'Error en el sistema. Intenta nuevamente.';
            header('Location: ' . url('forgot-password.php'));
            exit;
        }
    }
    
    /**
     * Restablecer contraseña con token
     */
    public function resetPassword() {
        session_start();
        
        if($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('login.php'));
            exit;
        }
        
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';
        
        // Validaciones
        if(empty($token) || empty($password)) {
            $_SESSION['error'] = 'Datos incompletos';
            header('Location: ' . url('reset-password.php?token=' . urlencode($token)));
            exit;
        }
        
        if($password !== $password_confirm) {
            $_SESSION['error'] = 'Las contraseñas no coinciden';
            header('Location: ' . url('reset-password.php?token=' . urlencode($token)));
            exit;
        }
        
        if(strlen($password) < 6) {
            $_SESSION['error'] = 'La contraseña debe tener al menos 6 caracteres';
            header('Location: ' . url('reset-password.php?token=' . urlencode($token)));
            exit;
        }
        
        try {
            // Verificar token
            $stmt = $this->db->prepare("
                SELECT * FROM password_reset_tokens 
                WHERE token = :token 
                AND usado = 0 
                AND fecha_expiracion > NOW()
            ");
            $stmt->execute(['token' => $token]);
            $resetToken = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if(!$resetToken) {
                $_SESSION['error'] = 'El enlace de recuperación es inválido o ha expirado';
                header('Location: ' . url('forgot-password.php'));
                exit;
            }
            
            // Actualizar contraseña
            $stmt = $this->db->prepare("UPDATE usuario SET password_usuario = :password WHERE id_usuario = :id");
            $stmt->execute([
                'password' => $password, // En producción usar password_hash()
                'id' => $resetToken['id_usuario']
            ]);
            
            // Marcar token como usado
            $stmt = $this->db->prepare("UPDATE password_reset_tokens SET usado = 1 WHERE id_token = :id");
            $stmt->execute(['id' => $resetToken['id_token']]);
            
            $_SESSION['success'] = '¡Contraseña restablecida exitosamente! Ya puedes iniciar sesión';
            header('Location: ' . url('login.php'));
            exit;
            
        } catch(PDOException $e) {
            $_SESSION['error'] = 'Error al restablecer contraseña';
            header('Location: ' . url('reset-password.php?token=' . urlencode($token)));
            exit;
        }
    }
    
    /**
     * Cambiar contraseña (usuario autenticado)
     */
    public function changePassword() {
        session_start();
        
        if(!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Debes iniciar sesión';
            header('Location: ' . url('login.php'));
            exit;
        }
        
        if($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('change-password.php'));
            exit;
        }
        
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validaciones
        if(empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $_SESSION['error'] = 'Por favor completa todos los campos';
            header('Location: ' . url('change-password.php'));
            exit;
        }
        
        if($new_password !== $confirm_password) {
            $_SESSION['error'] = 'Las contraseñas nuevas no coinciden';
            header('Location: ' . url('change-password.php'));
            exit;
        }
        
        if(strlen($new_password) < 6) {
            $_SESSION['error'] = 'La nueva contraseña debe tener al menos 6 caracteres';
            header('Location: ' . url('change-password.php'));
            exit;
        }
        
        if($current_password === $new_password) {
            $_SESSION['error'] = 'La nueva contraseña debe ser diferente a la actual';
            header('Location: ' . url('change-password.php'));
            exit;
        }
        
        try {
            // Verificar contraseña actual
            $stmt = $this->db->prepare("SELECT password_usuario FROM usuario WHERE id_usuario = :id");
            $stmt->execute(['id' => $_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if(!$user || $user['password_usuario'] !== $current_password) {
                $_SESSION['error'] = 'La contraseña actual es incorrecta';
                header('Location: ' . url('change-password.php'));
                exit;
            }
            
            // Actualizar contraseña
            $stmt = $this->db->prepare("UPDATE usuario SET password_usuario = :password WHERE id_usuario = :id");
            $stmt->execute([
                'password' => $new_password, // En producción usar password_hash()
                'id' => $_SESSION['user_id']
            ]);
            
            $_SESSION['success'] = '¡Contraseña actualizada exitosamente!';
            header('Location: ' . url('change-password.php'));
            exit;
            
        } catch(PDOException $e) {
            $_SESSION['error'] = 'Error al cambiar contraseña';
            header('Location: ' . url('change-password.php'));
            exit;
        }
    }
}

// Procesar la acción solicitada
$action = $_POST['action'] ?? $_GET['action'] ?? null;

if ($action) {
    $controller = new AuthController();

    switch ($action) {
        case 'login':
            $controller->login();
            break;
        case 'register':
            $controller->register();
            break;
        case 'forgot-password':
            $controller->forgotPassword();
            break;
        case 'reset-password':
            $controller->resetPassword();
            break;
        case 'change-password':
            $controller->changePassword();
            break;
        default:
            header('Location: ' . url('login.php'));
            break;
    }
}
?>
