<?php
// Habilitar mostrar errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// 1. REQUERIR LA CONEXIÓN
// (Asegúrate de que este archivo solo *define* $conn y no intenta usarlo)
require_once 'config/conexion.php';

// Verificar si $conn se creó correctamente
if (!isset($conn)) {
    die('Error: No se pudo establecer conexión a la base de datos. Revisa config/conexion.php');
}

// 2. SEGURIDAD ESENCIAL
// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Verificar si el usuario es administrador
if ($_SESSION['rol'] !== 'admin') {
    // Si no es admin, no tiene nada que hacer aquí
    header('Location: index.php');
    exit;
}

// 3. OBTENER DATOS DEL USUARIO (Seguro)
$usuario = htmlspecialchars($_SESSION['nombre'] ?? 'Administrador');
$rol = htmlspecialchars($_SESSION['rol'] ?? 'admin');

// 4. OBTENER LA PESTAÑA ACTUAL
// Usamos un parámetro URL simple. Si no existe, mostramos 'dashboard'.
$current_tab = $_GET['tab'] ?? 'dashboard';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Fashion Store</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <link href="public/assets/css/admin-styles.css" rel="stylesheet">
    <link href="public/assets/css/admin-custom-select-modal.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
</head>
<body>
    
    <div class="admin-container">
        <header class="admin-header">
            <div class="container">
                <div class="header-left">
                    <a href="index.php" class="back-button">
                        <i class="fas fa-arrow-left"></i>
                        <span>Volver</span>
                    </a>
                    <div class="header-title">
                        <h2>Fashion Admin</h2>
                        <p class="header-subtitle">Panel de Control</p>
                    </div>
                </div>
                       
                <div class="admin-user-info">
                    <div class="user-profile" onclick="toggleUserMenu(event)">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($usuario, 0, 2)); ?>
                        </div>
                        <div class="user-details">
                            <h3><?php echo $usuario; ?></h3>
                            <span><?php echo ucfirst($rol); ?></span>
                        </div>
                        <i class="fas fa-chevron-down user-dropdown-icon"></i>
                        
                        <div class="user-dropdown-menu" id="userDropdownMenu">
                            <a href="logout.php" class="dropdown-item logout-item">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Cerrar Sesión</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="admin-main">
            <nav class="admin-nav">
                <div class="nav-tabs">
                    <div class="nav-tab <?php echo ($current_tab === 'dashboard') ? 'active' : ''; ?>">
                        <a href="admin.php?tab=dashboard">
                            <i class="fas fa-chart-line"></i>
                            <span>Dashboard</span>
                        </a>
                    </div>
                    <div class="nav-tab <?php echo ($current_tab === 'productos') ? 'active' : ''; ?>">
                        <a href="admin.php?tab=productos">
                            <i class="fas fa-tshirt"></i>
                            <span>Productos</span>
                        </a>
                    </div>
                    <div class="nav-tab <?php echo ($current_tab === 'categorias') ? 'active' : ''; ?>">
                        <a href="admin.php?tab=categorias">
                            <i class="fas fa-tags"></i>
                            <span>Categorías</span>
                        </a>
                    </div>
                    <div class="nav-tab <?php echo ($current_tab === 'marcas') ? 'active' : ''; ?>">
                        <a href="admin.php?tab=marcas">
                            <i class="fas fa-copyright"></i>
                            <span>Marcas</span>
                        </a>
                    </div>
                    <div class="nav-tab <?php echo ($current_tab === 'usuarios') ? 'active' : ''; ?>">
                        <a href="admin.php?tab=usuarios">
                            <i class="fas fa-users"></i>
                            <span>Usuarios</span>
                        </a>
                    </div>
                    <div class="nav-tab <?php echo ($current_tab === 'configuracion') ? 'active' : ''; ?>">
                        <a href="admin.php?tab=configuracion">
                            <i class="fas fa-cog"></i>
                            <span>Configuración</span>
                        </a>
                    </div>
                </div>
            </nav>

            <div class="tab-content">
                <?php
                switch ($current_tab) {
                    case 'productos':
                        include 'app/views/admin/admin_productos.php';
                        break;

                    case 'categorias':
                        include 'app/views/admin/admin_categorias.php';
                        break;
                        
                    case 'marcas':
                        include 'app/views/admin/admin_marcas.php';
                        break;

                    case 'usuarios':
                        include 'app/views/admin/admin_usuarios.php';
                        break;

                    case 'configuracion':
                        // Mostramos el contenido estático de configuración
                        echo '
                        <div class="tab-pane active" id="configuracion">
                            <div class="section-header">
                                <h1><i class="fas fa-cog"></i> Configuración del Sistema</h1>
                            </div>
                            <div class="config-sections">
                                <div class="config-card">
                                    <h3><i class="fas fa-store"></i> Configuración de Tienda</h3>
                                    <p>Configurar nombre, descripción y datos de la tienda.</p>
                                    <button class="btn-secondary" disabled><i class="fas fa-edit"></i> Editar</button>
                                </div>
                                <div class="config-card">
                                    <h3><i class="fas fa-credit-card"></i> Métodos de Pago</h3>
                                    <p>Configurar opciones de pago disponibles.</p>
                                    <button class="btn-secondary" disabled><i class="fas fa-edit"></i> Configurar</button>
                                </div>
                            </div>
                        </div>';
                        break;

                    case 'dashboard':
                    default:
                        // Por defecto, cargamos el dashboard
                        include 'app/views/admin/admin_dashboard.php';
                        break;
                }
                ?>
            </div>
        </main>
    </div>

</body>
<script src="public/assets/js/admin/admin.js"></script>
<script src="public/assets/js/admin/custom-select-modal.js"></script>
</html>