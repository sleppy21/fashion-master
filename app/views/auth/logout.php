<?php
// Cargar configuración de rutas
require_once __DIR__ . '/../../../config/path.php';

session_start();

// Eliminar cookie de "recordar sesión" si existe
if(isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
    setcookie('user_id', '', time() - 3600, '/');
}

// Destruir la sesión
$_SESSION = array();

if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

session_destroy();

// Redirigir al login con mensaje de éxito
header('Location: ' . url('login.php'));
exit;
?>
