<?php
/**
 * PÁGINA DE PERFIL DE USUARIO
 * Permite ver y editar información personal del usuario
 */

session_start();
require_once 'config/conexion.php';
require_once 'config/config.php'; // <-- Para BASE_URL global

$page_title = "Mi Perfil";

// Verificar si el usuario está logueado
$usuario_logueado = null;
if (isset($_SESSION['user_id'])) {
    try {
        $usuario_resultado = executeQuery("SELECT * FROM usuario WHERE id_usuario = ? AND status_usuario = 1", [$_SESSION['user_id']]);
        $usuario_logueado = $usuario_resultado && !empty($usuario_resultado) ? $usuario_resultado[0] : null;
        
        if (!$usuario_logueado) {
            session_destroy();
            header('Location: login.php');
            exit;
        }
    } catch(Exception $e) {
        error_log("Error al obtener usuario: " . $e->getMessage());
        session_destroy();
        header('Location: login.php');
        exit;
    }
} else {
    header('Location: login.php?redirect=profile.php');
    exit;
}

// Obtener contadores para el header
$cart_count = 0;
$favorites_count = 0;
$notifications_count = 0;

try {
    $cart_resultado = executeQuery("SELECT COUNT(*) as total FROM carrito WHERE id_usuario = ?", [$usuario_logueado['id_usuario']]);
    $cart_count = $cart_resultado && !empty($cart_resultado) ? $cart_resultado[0]['total'] : 0;
    
    $fav_resultado = executeQuery("SELECT COUNT(*) as total FROM favorito WHERE id_usuario = ?", [$usuario_logueado['id_usuario']]);
    $favorites_count = $fav_resultado && !empty($fav_resultado) ? $fav_resultado[0]['total'] : 0;
    
    $notifications = executeQuery("SELECT COUNT(*) as total FROM notificacion WHERE id_usuario = ? AND leida_notificacion = 0 AND estado_notificacion = 'activo'", [$usuario_logueado['id_usuario']]);
    $notifications_count = ($notifications && count($notifications) > 0) ? ($notifications[0]['total'] ?? 0) : 0;
} catch(Exception $e) {
    error_log("Error al obtener contadores: " . $e->getMessage());
}

// Obtener categorías para el menú
$categorias = [];
try {
    $categorias_resultado = executeQuery("SELECT id_categoria, nombre_categoria FROM categoria WHERE status_categoria = 1 ORDER BY id_categoria ASC LIMIT 5");
    $categorias = $categorias_resultado ? $categorias_resultado : [];
} catch(Exception $e) {
    error_log("Error al obtener categorías: " . $e->getMessage());
}

// Obtener marcas para el menú
$marcas = [];
try {
    $marcas_resultado = executeQuery("SELECT id_marca, nombre_marca FROM marca WHERE status_marca = 1 ORDER BY nombre_marca ASC");
    $marcas = $marcas_resultado ? $marcas_resultado : [];
} catch(Exception $e) {
    error_log("Error al obtener marcas: " . $e->getMessage());
}

// Obtener estadísticas del usuario
$total_ordenes = 0;
$total_gastado = 0;

try {
    $ordenes_resultado = executeQuery("SELECT COUNT(*) as total FROM pedido WHERE id_usuario = ?", [$usuario_logueado['id_usuario']]);
    $total_ordenes = $ordenes_resultado && !empty($ordenes_resultado) ? $ordenes_resultado[0]['total'] : 0;
    
    $gastado_resultado = executeQuery("SELECT SUM(total_pedido) as total FROM pedido WHERE id_usuario = ? AND estado_pedido != 'cancelado'", [$usuario_logueado['id_usuario']]);
    $total_gastado = $gastado_resultado && !empty($gastado_resultado) && $gastado_resultado[0]['total'] ? $gastado_resultado[0]['total'] : 0;
} catch(Exception $e) {
    error_log("Error al obtener estadísticas: " . $e->getMessage());
}

// Determinar rol en español
$rol_texto = 'Cliente';
$rol_color = '#3498db';
if($usuario_logueado['rol_usuario'] === 'admin') {
    $rol_texto = 'Administrador';
    $rol_color = '#9b59b6';
} elseif($usuario_logueado['rol_usuario'] === 'vendedor') {
    $rol_texto = 'Vendedor';
    $rol_color = '#e67e22';
}

// Detectar si viene de cart.php sin dirección - Auto-abrir modal SOLO si no hay direcciones
$seccion_activa = isset($_GET['seccion']) ? $_GET['seccion'] : 'personal-info';

// Obtener direcciones guardadas del usuario
$direcciones_usuario = [];
try {
    $direcciones_resultado = executeQuery(
        "SELECT * FROM direccion 
         WHERE id_usuario = ? AND status_direccion = 1
         ORDER BY es_principal DESC, fecha_creacion_direccion DESC", 
        [$usuario_logueado['id_usuario']]
    );
    $direcciones_usuario = $direcciones_resultado ? $direcciones_resultado : [];
} catch(Exception $e) {
    error_log("Error al obtener direcciones: " . $e->getMessage());
}

// Auto-abrir modal SOLO si viene de cart y NO tiene direcciones
$auto_abrir_direccion = ($seccion_activa === 'direcciones' && empty($direcciones_usuario));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Mi Perfil - SleppyStore">
    <meta name="keywords" content="perfil, cuenta, usuario, configuración">
    <title><?= $page_title ?> - SleppyStore</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cookie&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Css Styles -->
    <link rel="stylesheet" href="public/assets/css/bootstrap.min.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/font-awesome.min.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/elegant-icons.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/jquery-ui.min.css" type="text/css">
    
    <!-- Modern Libraries (incluye Font Awesome 6.4.0 y Dark Mode) -->
    <?php include 'includes/modern-libraries.php'; ?>

    <!-- Avatar Flight Animation -->
    <link rel="stylesheet" href="public/assets/css/avatar-flight-animation.css?v=2.0" type="text/css">
   
    <!-- Breadcrumb Styles -->
    <link rel="stylesheet" href="public/assets/css/breadcrumb-modern.css" type="text/css">
   
    <!-- Profile Styles -->
    <link rel="stylesheet" href="public/assets/css/profile/profile.css?v=10.0" type="text/css">
    
    <!-- Croppie CSS for Avatar Upload -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.min.css">
    
    <!-- Avatar Crop Modal Styles -->
    <link rel="stylesheet" href="public/assets/css/avatar-crop-modal.css?v=2.0" type="text/css">
  
</head>

<body>
    <!-- Offcanvas Menu -->
    <?php include 'includes/offcanvas-menu.php'; ?>

    <script>
        // BASE_URL sin barra final para evitar duplicados
        window.BASE_URL = '<?= rtrim(BASE_URL, "/") ?>';
        if (window.location.protocol === 'https:' && window.BASE_URL.startsWith('http:')) {
            window.BASE_URL = window.BASE_URL.replace('http:', 'https:');
        }
    </script>
    
    <!-- Header con modales -->
    <?php include 'includes/header-section.php'; ?>
    
    <!-- Breadcrumb -->
    <div class="breadcrumb-option">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="breadcrumb__links">
                        <a href="./index.php"><i class="fa fa-home"></i> Inicio</a>
                        <span><i class="fa fa-user"></i> Mi Perfil</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Profile Section -->
    <section class="profile-section profile-page">
        <div class="container">
            <div class="row g-4">
                <!-- Sidebar - Balance mejorado -->
                <div class="col-lg-4 col-xl-3 mb-4">
                    <div class="profile-sidebar">
                        <!-- Avatar Section -->
                        <div class="profile-avatar-section">
                            <div class="avatar-wrapper" id="avatar-upload-area">
                                <?php 
                                $avatar_path = 'public/assets/img/profiles/default-avatar.png';
                                if (!empty($usuario_logueado['avatar_usuario'])) {
                                    // Si ya contiene la ruta completa, usarla directamente
                                    if (strpos($usuario_logueado['avatar_usuario'], 'public/assets/img/profiles/') !== false) {
                                        $avatar_path = $usuario_logueado['avatar_usuario'];
                                    } 
                                    // Si solo tiene el nombre del archivo y no es el default, construir la ruta
                                    elseif ($usuario_logueado['avatar_usuario'] !== 'default-avatar.png') {
                                        $avatar_path = 'public/assets/img/profiles/' . $usuario_logueado['avatar_usuario'];
                                    }
                                }
                                ?>
                                <div class="profile-avatar">
                                    <img src="<?php echo $avatar_path; ?>" alt="Avatar" class="avatar-image">
                                </div>
                                <button type="button" class="btn-change-avatar" title="Cambiar foto">
                                    <i class="fa fa-camera"></i>
                                </button>
                            </div>
                            <input type="file" id="avatar-file-input" accept="image/*" style="display: none;">
                            <div class="profile-info-wrapper">
                                <h3 class="profile-name">
                                    <?php echo htmlspecialchars($usuario_logueado['nombre_usuario'] . ' ' . $usuario_logueado['apellido_usuario']); ?>
                                </h3>
                                <span class="profile-role-badge" style="background: <?php echo $rol_color; ?>">
                                    <i class="fa fa-shield"></i> <?php echo $rol_texto; ?>
                                </span>
                                <p class="profile-email">
                                    <i class="fa fa-envelope"></i>
                                    <?php echo htmlspecialchars($usuario_logueado['email_usuario']); ?>
                                </p>
                            </div>
                        </div>
                        
                        <!-- Stats Cards -->
                        <!-- Oculto en móvil -->
                        <div class="profile-stats d-none d-md-flex">
                            <div class="stat-item">
                                <div class="stat-icon">
                                    <i class="fa fa-shopping-bag"></i>
                                </div>
                                <div class="stat-details">
                                    <span class="stat-number"><?php echo $total_ordenes; ?></span>
                                    <span class="stat-label">Pedidos</span>
                                </div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-icon">
                                    <i class="fa fa-dollar-sign"></i>
                                </div>
                                <div class="stat-details">
                                    <span class="stat-number">$<?php echo number_format($total_gastado, 2); ?></span>
                                    <span class="stat-label">Total Gastado</span>
                                </div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-icon">
                                    <i class="fa fa-heart"></i>
                                </div>
                                <div class="stat-details">
                                    <span class="stat-number"><?php echo $favorites_count; ?></span>
                                    <span class="stat-label">Favoritos</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="quick-actions d-none d-md-flex">
                            <a href="cart.php" class="action-btn">
                                <i class="fa fa-shopping-cart"></i>
                                <span>Mi Carrito</span>
                                <?php if($cart_count > 0): ?>
                                    <span class="action-badge"><?php echo $cart_count; ?></span>
                                <?php endif; ?>
                            </a>
                            <a href="#" class="action-btn" id="toggle-favorites-sidebar-btn">
                                <i class="fa fa-heart"></i>
                                <span>Mis Favoritos</span>
                                <?php if($favorites_count > 0): ?>
                                    <span class="action-badge"><?php echo $favorites_count; ?></span>
                                <?php endif; ?>
                            </a>
                            <?php if($usuario_logueado['rol_usuario'] === 'admin'): ?>
                                <a href="admin.php" class="action-btn">
                                    <i class="fa fa-tachometer"></i>
                                    <span>Panel Admin</span>
                                </a>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Quick Section Links - Sutiles -->
                        <!-- Oculto en móvil -->
                        <div class="quick-section-links d-none d-md-block">
                            <p>Ir a sección</p>
                            <div>
                                <a href="profile.php?seccion=personal-info">
                                    <i class="fa fa-chevron-right"></i>
                                    <span>Información Personal</span>
                                </a>
                                <a href="profile.php?seccion=seguridad">
                                    <i class="fa fa-chevron-right"></i>
                                    <span>Seguridad</span>
                                </a>
                                <a href="profile.php?seccion=direcciones">
                                    <i class="fa fa-chevron-right"></i>
                                    <span>Direcciones</span>
                                </a>
                                <a href="profile.php?seccion=configuracion">
                                    <i class="fa fa-chevron-right"></i>
                                    <span>Configuración</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Main Content - Balance mejorado -->
                <div class="col-lg-8 col-xl-9">
                    <!-- Tabs Navigation -->
                    <div class="profile-tabs">
                        <button class="tab-btn <?php echo $seccion_activa === 'personal-info' ? 'active' : ''; ?>" data-tab="personal-info" data-label="Perfil">
                            <i class="fa fa-user"></i> <span class="tab-text">Información Personal</span>
                        </button>
                        <button class="tab-btn <?php echo $seccion_activa === 'seguridad' ? 'active' : ''; ?>" data-tab="security" data-label="Seguridad">
                            <i class="fa fa-lock"></i> <span class="tab-text">Seguridad</span>
                        </button>
                        <button class="tab-btn <?php echo $seccion_activa === 'direcciones' ? 'active' : ''; ?>" data-tab="addresses" data-label="Ubicación">
                            <i class="fa fa-map-marker-alt"></i> <span class="tab-text">Direcciones</span>
                        </button>
                        <button class="tab-btn <?php echo $seccion_activa === 'configuracion' ? 'active' : ''; ?>" data-tab="settings" data-label="Config">
                            <i class="fa fa-cog"></i> <span class="tab-text">Configuración</span>
                        </button>
                    </div>
                    
                    <!-- Tab Content: Personal Info -->
                    <div class="tab-content <?php echo $seccion_activa === 'personal-info' ? 'active' : ''; ?>" id="personal-info">
                        <div class="profile-card">
                            <div class="card-header">
                                <h4><i class="fa fa-user-edit"></i> Editar Información Personal</h4>
                                <button type="button" class="btn-edit" id="btn-edit-personal">
                                    <i class="fa fa-edit"></i> Editar
                                </button>
                            </div>
                            <form id="form-personal-info" class="profile-form">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><i class="fa fa-user"></i> Nombre *</label>
                                            <input type="text" class="form-control" name="nombre" 
                                                   value="<?php echo htmlspecialchars($usuario_logueado['nombre_usuario']); ?>" 
                                                   required minlength="2" maxlength="50" disabled>
                                            <small class="form-text text-muted">Mínimo 2 caracteres</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><i class="fa fa-user"></i> Apellido *</label>
                                            <input type="text" class="form-control" name="apellido" 
                                                   value="<?php echo htmlspecialchars($usuario_logueado['apellido_usuario']); ?>" 
                                                   required minlength="2" maxlength="50" disabled>
                                            <small class="form-text text-muted">Mínimo 2 caracteres</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><i class="fa fa-at"></i> Nombre de Usuario</label>
                                            <input type="text" class="form-control" name="username" 
                                                   value="<?php echo htmlspecialchars($usuario_logueado['username_usuario']); ?>" 
                                                   disabled readonly>
                                            <small class="form-text text-muted">El nombre de usuario no se puede modificar</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><i class="fa fa-envelope"></i> Email</label>
                                            <input type="email" class="form-control" name="email" 
                                                   value="<?php echo htmlspecialchars($usuario_logueado['email_usuario']); ?>" 
                                                   disabled readonly>
                                            <small class="form-text text-muted">El email no se puede modificar</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><i class="fa fa-phone"></i> Teléfono</label>
                                            <input type="tel" class="form-control" name="telefono" 
                                                   value="<?php echo htmlspecialchars($usuario_logueado['telefono_usuario'] ?? ''); ?>" 
                                                   pattern="[0-9+\-\s\(\)]+" maxlength="15" 
                                                   placeholder="999 999 999" disabled>
                                            <small class="form-text text-muted">Solo números y símbolos: + - ( )</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><i class="fa fa-birthday-cake"></i> Fecha de Nacimiento</label>
                                            <input type="date" class="form-control" name="fecha_nacimiento" 
                                                   value="<?php echo $usuario_logueado['fecha_nacimiento'] ?? ''; ?>" 
                                                   max="<?php echo date('Y-m-d', strtotime('-18 years')); ?>" disabled>
                                            <small class="form-text text-muted">Debes ser mayor de 18 años</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label><i class="fa fa-venus-mars"></i> Género</label>
                                    <select class="form-control" name="genero" disabled>
                                        <option value="M" <?php echo ($usuario_logueado['genero_usuario'] ?? '') == 'M' ? 'selected' : ''; ?>>Masculino</option>
                                        <option value="F" <?php echo ($usuario_logueado['genero_usuario'] ?? '') == 'F' ? 'selected' : ''; ?>>Femenino</option>
                                        <option value="Otro" <?php echo ($usuario_logueado['genero_usuario'] ?? '') == 'Otro' ? 'selected' : ''; ?>>Otro</option>
                                    </select>
                                </div>
                                
                                <div class="form-actions" style="display: none;">
                                    <button type="submit" class="btn btn-save">
                                        <i class="fa fa-save"></i> Guardar Cambios
                                    </button>
                                    <button type="button" class="btn btn-cancel" id="btn-cancel-personal">
                                        <i class="fa fa-times"></i> Cancelar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Tab Content: Security -->
                    <div class="tab-content <?php echo $seccion_activa === 'seguridad' ? 'active' : ''; ?>" id="security">
                        <div class="profile-card">
                            <div class="card-header">
                                <h4><i class="fa fa-shield-alt"></i> Seguridad de la Cuenta</h4>
                            </div>
                            <form id="form-change-password" class="profile-form">
                                <div class="form-group">
                                    <label><i class="fa fa-lock"></i> Contraseña Actual</label>
                                    <div class="password-input-wrapper">
                                        <input type="password" class="form-control" name="current_password" 
                                               placeholder="Ingresa tu contraseña actual" required>
                                        <button type="button" class="toggle-password">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label><i class="fa fa-key"></i> Nueva Contraseña</label>
                                    <div class="password-input-wrapper">
                                        <input type="password" class="form-control" name="new_password" 
                                               placeholder="Ingresa tu nueva contraseña" required minlength="6">
                                        <button type="button" class="toggle-password">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    </div>
                                    <small class="form-text">Mínimo 6 caracteres</small>
                                </div>
                                
                                <div class="form-group">
                                    <label><i class="fa fa-check-circle"></i> Confirmar Nueva Contraseña</label>
                                    <div class="password-input-wrapper">
                                        <input type="password" class="form-control" name="confirm_password" 
                                               placeholder="Confirma tu nueva contraseña" required minlength="6">
                                        <button type="button" class="toggle-password">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-save">
                                        <i class="fa fa-save"></i> Cambiar Contraseña
                                    </button>
                                </div>
                            </form>
                            
                            <!-- Account Info -->
                            <div class="account-info mt-4">
                                <h5><i class="fa fa-info-circle"></i> Información de la Cuenta</h5>
                                <div class="info-row">
                                    <span class="info-label">Fecha de Registro:</span>
                                    <span class="info-value">
                                        <?php 
                                        $fecha_registro = new DateTime($usuario_logueado['fecha_registro']);
                                        echo $fecha_registro->format('d/m/Y H:i');
                                        ?>
                                    </span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Último Acceso:</span>
                                    <span class="info-value">
                                        <?php 
                                        if($usuario_logueado['ultimo_acceso']) {
                                            $ultimo_acceso = new DateTime($usuario_logueado['ultimo_acceso']);
                                            echo $ultimo_acceso->format('d/m/Y H:i');
                                        } else {
                                            echo 'No disponible';
                                        }
                                        ?>
                                    </span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Estado de la Cuenta:</span>
                                    <span class="info-value">
                                        <span class="badge badge-success">
                                            <i class="fa fa-check-circle"></i> Activa
                                        </span>
                                    </span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Verificación:</span>
                                    <span class="info-value">
                                        <?php if($usuario_logueado['verificado_usuario']): ?>
                                            <span class="badge badge-success">
                                                <i class="fa fa-check-circle"></i> Verificada
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">
                                                <i class="fa fa-exclamation-circle"></i> Pendiente
                                            </span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tab Content: Addresses -->
                    <div class="tab-content <?php echo $seccion_activa === 'direcciones' ? 'active' : ''; ?>" id="addresses">
                        <div class="profile-card">
                            <div class="card-header">
                                <h4><i class="fa fa-map-marked-alt"></i> Mis Direcciones</h4>
                                <button type="button" class="btn-edit" id="btn-add-address">
                                    <i class="fa fa-plus"></i> Agregar Dirección
                                </button>
                            </div>
                            
                            <div class="addresses-list">
                                <?php if (empty($direcciones_usuario)): ?>
                                    <div class="empty-state" style="padding: 80px 40px; text-align: center; background: linear-gradient(135deg, rgba(201,166,124,0.05) 0%, rgba(201,166,124,0.02) 100%); border-radius: 16px; border: 2px dashed rgba(201,166,124,0.3);">
                                        <div style="margin-bottom: 24px;">
                                            <i class="fa fa-map-marker-alt" style="font-size: 72px; color: #c9a67c; opacity: 0.4;"></i>
                                        </div>
                                        <h4 style="color: #c9a67c; margin-bottom: 16px; font-size: 24px; font-weight: 600;">¡Agrega tu primera dirección!</h4>
                                        <p style="color: #777; margin-bottom: 32px; font-size: 15px; line-height: 1.6; max-width: 500px; margin-left: auto; margin-right: auto;">
                                            Guarda tus direcciones de envío para que tus compras<br>sean más rápidas y fáciles
                                        </p>
                                        <button type="button" class="btn btn-primary" id="btn-add-first-address" style="padding: 14px 36px; font-size: 15px; border-radius: 10px; background: linear-gradient(135deg, #c9a67c 0%, #a08661 100%); border: none; font-weight: 600; transition: all 0.3s; box-shadow: 0 4px 15px rgba(201,166,124,0.3);">
                                            <i class="fa fa-plus-circle" style="margin-right: 8px;"></i> Agregar Primera Dirección
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($direcciones_usuario as $direccion): ?>
                                        <div class="address-card <?= $direccion['es_principal'] == 1 ? 'default' : '' ?>">
                                            <div class="address-header">
                                                <div class="address-title">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                    <strong><?= htmlspecialchars($direccion['nombre_cliente_direccion']) ?></strong>
                                                    <?php if ($direccion['es_principal'] == 1): ?>
                                                        <span class="badge-default">Predeterminada</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="address-actions">
                                                    <?php if ($direccion['es_principal'] != 1): ?>
                                                        <button type="button" class="btn-action btn-set-default" 
                                                                data-id="<?= $direccion['id_direccion'] ?>"
                                                                title="Establecer como predeterminada">
                                                            <i class="fas fa-star"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <button type="button" class="btn-action btn-edit-address" 
                                                            data-id="<?= $direccion['id_direccion'] ?>"
                                                            title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn-action btn-delete-address" 
                                                            data-id="<?= $direccion['id_direccion'] ?>"
                                                            title="Eliminar">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="address-body">
                                                <?php if (!empty($direccion['nombre_cliente_direccion'])): ?>
                                                    <p><i class="fas fa-user"></i> <strong><?= htmlspecialchars($direccion['nombre_cliente_direccion']) ?></strong></p>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($direccion['email_direccion'])): ?>
                                                    <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($direccion['email_direccion']) ?></p>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($direccion['telefono_direccion'])): ?>
                                                    <p><i class="fas fa-phone"></i> <?= htmlspecialchars($direccion['telefono_direccion']) ?></p>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($direccion['dni_ruc_direccion'])): ?>
                                                    <p>
                                                        <i class="fas fa-id-card"></i> 
                                                        <?= strlen($direccion['dni_ruc_direccion']) === 11 ? 'RUC:' : 'DNI:' ?> 
                                                        <?= htmlspecialchars($direccion['dni_ruc_direccion']) ?>
                                                    </p>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($direccion['razon_social_direccion'])): ?>
                                                    <p><i class="fas fa-building"></i> Razón Social: <?= htmlspecialchars($direccion['razon_social_direccion']) ?></p>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($direccion['direccion_completa_direccion'])): ?>
                                                    <p><i class="fas fa-location-dot"></i> <?= htmlspecialchars($direccion['direccion_completa_direccion']) ?></p>
                                                <?php endif; ?>
                                                
                                                <div class="address-details">
                                                    <?php if (!empty($direccion['distrito_direccion'])): ?>
                                                    <span class="detail-item">
                                                        <i class="fas fa-building"></i> 
                                                        <?= htmlspecialchars($direccion['distrito_direccion']) ?>
                                                    </span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($direccion['provincia_direccion'])): ?>
                                                    <span class="detail-item">
                                                        <i class="fas fa-city"></i> 
                                                        <?= htmlspecialchars($direccion['provincia_direccion']) ?>
                                                    </span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($direccion['departamento_direccion'])): ?>
                                                    <span class="detail-item">
                                                        <i class="fas fa-map"></i> 
                                                        <?= htmlspecialchars($direccion['departamento_direccion']) ?>
                                                    </span>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <?php if (!empty($direccion['referencia_direccion'])): ?>
                                                    <p class="address-reference">
                                                        <i class="fas fa-info-circle"></i> 
                                                        <em>Ref: <?= htmlspecialchars($direccion['referencia_direccion']) ?></em>
                                                    </p>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($direccion['metodo_pago_favorito'])): ?>
                                                    <p class="address-payment">
                                                        <i class="fas fa-credit-card"></i> 
                                                        <strong>Método de pago favorito:</strong>
                                                        <?php 
                                                        $metodos = [
                                                            'tarjeta' => 'Tarjeta de Crédito/Débito',
                                                            'transferencia' => 'Transferencia Bancaria',
                                                            'yape' => 'Yape / Plin',
                                                            'efectivo' => 'Efectivo Contra Entrega'
                                                        ];
                                                        echo $metodos[$direccion['metodo_pago_favorito']] ?? $direccion['metodo_pago_favorito'];
                                                        ?>
                                                    </p>
                                                <?php endif; ?>
                                                
                                                <p class="address-date">
                                                    <i class="fas fa-calendar"></i> 
                                                    Agregada el <?= date('d/m/Y', strtotime($direccion['fecha_creacion_direccion'])) ?>
                                                </p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                <!-- Tab Content: Settings -->
                <div class="tab-content <?php echo $seccion_activa === 'configuracion' ? 'active' : ''; ?>" id="settings">
                    <div class="profile-card">
                        <div class="card-header">
                            <h4><i class="fa fa-cog"></i> Configuración de la Aplicación</h4>
                            <p style="font-size: 13px; color: #666; margin: 8px 0 0 0;">Personaliza tu experiencia en SleppyStore</p>
                        </div>
                        
                        <form id="settings-form" style="padding: 25px;">
                            <!-- Tema de Color -->
                            <div class="settings-section">
                                <h5 class="settings-title">
                                    <i class="fa fa-palette"></i> Apariencia
                                </h5>
                                
                                <div class="setting-item">
                                    <div class="setting-label">
                                        <strong>Modo de Color</strong>
                                        <p>Elige el tema de color de la interfaz</p>
                                    </div>
                                    <div class="setting-control">
                                        <div class="theme-selector">
                                            <label class="theme-option">
                                                <input type="radio" name="theme_mode" value="light" checked>
                                                <div class="theme-card">
                                                    <i class="fa fa-sun"></i>
                                                    <span>Claro</span>
                                                </div>
                                            </label>
                                            <label class="theme-option">
                                                <input type="radio" name="theme_mode" value="dark">
                                                <div class="theme-card">
                                                    <i class="fa fa-moon"></i>
                                                    <span>Oscuro</span>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Notificaciones -->
                            <div class="settings-section">
                                <h5 class="settings-title">
                                    <i class="fa fa-bell"></i> Notificaciones
                                </h5>
                                
                                <div class="setting-item">
                                    <div class="setting-label">
                                        <strong>Notificaciones Push</strong>
                                        <p>Recibe notificaciones en tiempo real</p>
                                    </div>
                                    <div class="setting-control">
                                        <label class="switch">
                                            <input type="checkbox" name="push_notifications" checked>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="setting-item">
                                    <div class="setting-label">
                                        <strong>Notificaciones por Email</strong>
                                        <p>Recibe actualizaciones en tu correo</p>
                                    </div>
                                    <div class="setting-control">
                                        <label class="switch">
                                            <input type="checkbox" name="email_notifications" checked>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="setting-item">
                                    <div class="setting-label">
                                        <strong>Ofertas y Promociones</strong>
                                        <p>Recibe notificaciones sobre ofertas especiales</p>
                                    </div>
                                    <div class="setting-control">
                                        <label class="switch">
                                            <input type="checkbox" name="promo_notifications" checked>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="setting-item">
                                    <div class="setting-label">
                                        <strong>Actualizaciones de Pedidos</strong>
                                        <p>Notificaciones sobre el estado de tus pedidos</p>
                                    </div>
                                    <div class="setting-control">
                                        <label class="switch">
                                            <input type="checkbox" name="order_notifications" checked>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Preferencias de Compra -->
                            <div class="settings-section">
                                <h5 class="settings-title">
                                    <i class="fa fa-shopping-bag"></i> Preferencias de Compra
                                </h5>
                                
                                <div class="setting-item">
                                    <div class="setting-label">
                                        <strong>Idioma</strong>
                                        <p>Selecciona el idioma de la interfaz</p>
                                    </div>
                                    <div class="setting-control">
                                        <select class="form-control" name="language" style="max-width: 200px;">
                                            <option value="es" selected>Español</option>
                                            <option value="en">English</option>
                                            <option value="pt">Português</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="setting-item">
                                    <div class="setting-label">
                                        <strong>Moneda</strong>
                                        <p>Moneda predeterminada para precios</p>
                                    </div>
                                    <div class="setting-control">
                                        <select class="form-control" name="currency" style="max-width: 200px;">
                                            <option value="PEN" selected>Soles (S/)</option>
                                            <option value="USD">Dólares ($)</option>
                                            <option value="EUR">Euros (€)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="setting-item">
                                    <div class="setting-label">
                                        <strong>Guardar Carrito</strong>
                                        <p>Mantener productos en el carrito al cerrar sesión</p>
                                    </div>
                                    <div class="setting-control">
                                        <label class="switch">
                                            <input type="checkbox" name="save_cart" checked>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Privacidad -->
                            <div class="settings-section">
                                <h5 class="settings-title">
                                    <i class="fa fa-shield-alt"></i> Privacidad
                                </h5>
                                
                                <div class="setting-item">
                                    <div class="setting-label">
                                        <strong>Perfil Público</strong>
                                        <p>Permite que otros usuarios vean tu perfil</p>
                                    </div>
                                    <div class="setting-control">
                                        <label class="switch">
                                            <input type="checkbox" name="public_profile">
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="setting-item">
                                    <div class="setting-label">
                                        <strong>Compartir Actividad</strong>
                                        <p>Permite compartir tu actividad de compras</p>
                                    </div>
                                    <div class="setting-control">
                                        <label class="switch">
                                            <input type="checkbox" name="share_activity">
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Botones de Acción -->
                            <div class="settings-actions" style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; text-align: center;">
                                <p style="margin: 0 0 15px 0; color: #2ecc71; font-size: 0.95rem;">
                                    <i class="fa fa-check-circle"></i> 
                                    <strong>Los cambios se guardan automáticamente</strong>
                                </p>
                                <button type="button" class="btn btn-secondary btn-reset-settings">
                                    <i class="fa fa-undo"></i> Restaurar Predeterminados
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </section>
    
    <!-- Avatar Crop Modal (inline) -->
    <div id="avatar-crop-modal" class="avatar-crop-modal hidden">
        <div class="avatar-crop-overlay"></div>
        <div class="avatar-crop-content">
            <button class="avatar-crop-close" id="closeCropModal">
                <i class="fa fa-times"></i>
            </button>
            <div class="avatar-crop-body">
                <!-- Área de recorte -->
                <div class="crop-container">
                    <div id="crop-image"></div>
                </div>
            </div>
            <div class="avatar-crop-footer">
                <button type="button" class="btn btn-cancel">
                    <i class="fa fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-upload">
                    <i class="fa fa-upload"></i> Subir Avatar
                </button>
            </div>
        </div>
    </div>
    
    <!-- Modal para Agregar/Editar Dirección -->
    <div class="modal fade" id="addressModal" tabindex="-1" role="dialog" aria-labelledby="addressModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addressModalLabel">
                        <i class="fa fa-map-marker-alt"></i> 
                        <span id="addressModalTitle">Agregar Dirección</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addressForm">
                        <input type="hidden" id="address_id" name="id_direccion">
                        <!-- Email oculto - se envía automáticamente -->
                        <input type="hidden" id="address_email" name="email" value="<?php echo htmlspecialchars($usuario_logueado['email_usuario']); ?>">
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="address_name">
                                        <i class="fa fa-user"></i>
                                        Nombre del Titular *
                                    </label>
                                    <input type="text" class="form-control" id="address_name" name="nombre_direccion" 
                                           placeholder="Ej: Juan Pérez García" required minlength="3" maxlength="100"
                                           pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+" 
                                           title="Solo se permiten letras y espacios">
                                    <small class="form-text text-muted">Persona que recibirá el pedido</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="address_dni">
                                        <i class="fa fa-id-card"></i>
                                        DNI/RUC *
                                    </label>
                                    <input type="text" class="form-control" id="address_dni" name="dni" 
                                           placeholder="DNI (8) o RUC (11)" required
                                           pattern="[0-9]{8}|[0-9]{11}" 
                                           maxlength="11"
                                           title="Ingrese DNI de 8 dígitos o RUC de 11 dígitos">
                                    <small class="form-text text-muted">DNI: 8 dígitos / RUC: 11 dígitos</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="address_phone">
                                        <i class="fa fa-phone"></i>
                                        Teléfono *
                                    </label>
                                    <input type="tel" class="form-control" id="address_phone" name="telefono" 
                                           placeholder="999 999 999" required
                                           pattern="[0-9]{9}" 
                                           maxlength="9"
                                           title="Ingrese exactamente 9 dígitos numéricos">
                                    <small class="form-text text-muted">Exactamente 9 dígitos</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Campo Razón Social - Visible solo cuando es RUC -->
                        <div class="form-group" id="razon-social-group" style="display: none;">
                            <label for="address_razon_social">
                                <i class="fa fa-building"></i>
                                Razón Social *
                            </label>
                            <input type="text" class="form-control" id="address_razon_social" name="razon_social" 
                                   placeholder="Nombre de la empresa" minlength="3" maxlength="150">
                            <small class="form-text text-muted">Nombre completo de la empresa (requerido para RUC)</small>
                        </div>

                        <div class="form-group">
                            <label for="address_full">
                                <i class="fa fa-map-marked-alt"></i>
                                Dirección Completa *
                            </label>
                            <input type="text" class="form-control" id="address_full" name="direccion_completa" 
                                   placeholder="Calle, número, urbanización, piso/dpto" required minlength="10" maxlength="200">
                            <small class="form-text text-muted">Dirección exacta donde se entregará el pedido</small>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="address_departamento">
                                        <i class="fa fa-globe-americas"></i>
                                        Departamento *
                                    </label>
                                    <select class="form-control" id="address_departamento" name="departamento" required>
                                        <option value="">Seleccionar departamento</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="address_provincia">
                                        <i class="fa fa-city"></i>
                                        Provincia *
                                    </label>
                                    <select class="form-control" id="address_provincia" name="provincia" required disabled>
                                        <option value="">Seleccione departamento primero</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="address_distrito">
                                        <i class="fa fa-map-pin"></i>
                                        Distrito *
                                    </label>
                                    <select class="form-control" id="address_distrito" name="distrito" required disabled>
                                        <option value="">Seleccione provincia primero</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="address_reference">
                                <i class="fa fa-info-circle"></i>
                                Referencia (Opcional)
                            </label>
                            <input type="text" class="form-control" id="address_reference" name="referencia" 
                                   placeholder="Ej: Casa azul con puerta negra, al costado de la bodega" 
                                   maxlength="150">
                            <small class="form-text text-muted">Referencias para ubicar tu dirección más fácilmente</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-primary" id="saveAddressBtn">
                        <i class="fa fa-save"></i> Guardar Dirección
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    
    <!-- Js Plugins -->
    <script>
        // BASE URL para peticiones AJAX - Compatible con ngrok y cualquier dominio
        (function() {
            var baseUrlFromPHP = '<?php echo defined("BASE_URL") ? BASE_URL : ""; ?>';
            
            // Si no hay BASE_URL definida en PHP, calcularla desde JavaScript
            if (!baseUrlFromPHP || baseUrlFromPHP === '') {
                var path = window.location.pathname;
                var pathParts = path.split('/').filter(function(p) { return p !== ''; });
                
                // Buscar 'fashion-master' en el path
                var basePath = '';
                if (pathParts.includes('fashion-master')) {
                    var index = pathParts.indexOf('fashion-master');
                    basePath = '/' + pathParts.slice(0, index + 1).join('/');
                }
                
                baseUrlFromPHP = window.location.origin + basePath;
            }
            
            // CRÍTICO: Si la página está en HTTPS, forzar BASE_URL a HTTPS
            if (window.location.protocol === 'https:' && baseUrlFromPHP.startsWith('http://')) {
                baseUrlFromPHP = baseUrlFromPHP.replace('http://', 'https://');
            }
            
            window.BASE_URL = baseUrlFromPHP;
        })();
    </script>
    <script src="public/assets/js/jquery-3.3.1.min.js"></script>
    
    <!-- Fetch API Handler Moderno - Reemplaza AJAX/jQuery -->
    <script src="public/assets/js/fetch-api-handler.js"></script>
    
    <script src="public/assets/js/bootstrap.min.js"></script>
    <script src="public/assets/js/jquery-ui.min.js"></script>
    <script src="public/assets/js/jquery.slicknav.js"></script>
    
    <!-- Header Handler - Actualización en tiempo real de contadores -->
    <script src="public/assets/js/header-handler.js?v=1.0"></script>
    
    <!-- Sistema Global de Contadores -->
    <script src="public/assets/js/global-counters.js"></script>
    
    <!-- Real-time Updates System - DEBE IR ANTES que cart-favorites-handler -->
    <script src="public/assets/js/real-time-updates.js?v=<?= time() ?>"></script>
    
    <script src="public/assets/js/user-account-modal.js"></script>

    
    <!-- Profile Script (Ya no necesita SweetAlert2 - usa toast nativo) -->
    <script src="public/assets/js/profile.js"></script>
    
    <!-- Croppie JS for Avatar Upload -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.min.js"></script>
    
    <!-- Avatar Flight Animation con Tracking en Tiempo Real -->
    <script src="public/assets/js/avatar-flight-animation-realtime.js"></script>
    
    <!-- Avatar Upload Script -->
    <script src="public/assets/js/avatar-upload.js"></script>
    
    <!-- Avatar Color Extractor - Shadow dinámico basado en la imagen -->
    <script src="public/assets/js/avatar-color-extractor.js"></script>
    
    <!-- Script para cargar UBIGEO del Perú -->
    <script>
        // Variable global para almacenar datos de ubigeo
        let ubigeoDataProfile = null;
        
        // Cargar datos de ubigeo al inicio
        fetch('public/assets/data/peru-ubigeo.json')
            .then(response => response.json())
            .then(data => {
                ubigeoDataProfile = data;
                cargarDepartamentos();
            })
            .catch(error => {
            });
        
        // Función para cargar departamentos en el select
        function cargarDepartamentos() {
            const selectDepartamento = document.getElementById('address_departamento');
            if (!selectDepartamento) return;
            
            selectDepartamento.innerHTML = '<option value="">Seleccionar departamento</option>';
            
            ubigeoDataProfile.departamentos.forEach(depto => {
                const option = document.createElement('option');
                option.value = depto.nombre;
                option.textContent = depto.nombre;
                option.dataset.id = depto.id;
                selectDepartamento.appendChild(option);
            });
        }
        
        // Evento cuando se selecciona un departamento
        document.addEventListener('DOMContentLoaded', function() {
            const selectDepartamento = document.getElementById('address_departamento');
            const selectProvincia = document.getElementById('address_provincia');
            const selectDistrito = document.getElementById('address_distrito');
            
            // Validación en tiempo real para el nombre (solo letras)
            const addressName = document.getElementById('address_name');
            if (addressName) {
                addressName.addEventListener('input', function(e) {
                    // Remover cualquier caracter que no sea letra o espacio
                    this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
                    
                    // Validar longitud mínima
                    if (this.value.length >= 3) {
                        this.setCustomValidity('');
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                    } else if (this.value.length > 0) {
                        this.setCustomValidity('El nombre debe tener al menos 3 caracteres');
                        this.classList.remove('is-valid');
                        this.classList.add('is-invalid');
                    } else {
                        this.classList.remove('is-valid', 'is-invalid');
                    }
                });
            }
            
            // Validación en tiempo real para DNI/RUC (solo números, 8 u 11 dígitos)
            const addressDni = document.getElementById('address_dni');
            const razonSocialGroup = document.getElementById('razon-social-group');
            const razonSocialInput = document.getElementById('address_razon_social');
            
            if (addressDni) {
                addressDni.addEventListener('input', function(e) {
                    // Solo permitir números
                    this.value = this.value.replace(/[^0-9]/g, '');
                    
                    // Limitar a 11 caracteres máximo
                    if (this.value.length > 11) {
                        this.value = this.value.slice(0, 11);
                    }
                    
                    // Validar que sea 8 o 11 dígitos
                    const length = this.value.length;
                    
                    // Mostrar/ocultar Razón Social según el tipo
                    if (length === 11) {
                        // Es RUC - Mostrar y hacer obligatorio Razón Social
                        razonSocialGroup.style.display = 'block';
                        razonSocialInput.required = true;
                    } else {
                        // Es DNI o vacío - Ocultar Razón Social
                        razonSocialGroup.style.display = 'none';
                        razonSocialInput.required = false;
                        razonSocialInput.value = '';
                        razonSocialInput.classList.remove('is-valid', 'is-invalid');
                    }
                    
                    if (length === 8 || length === 11) {
                        this.setCustomValidity('');
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                    } else if (length > 0) {
                        this.setCustomValidity('Debe ser DNI (8 dígitos) o RUC (11 dígitos)');
                        this.classList.remove('is-valid');
                        this.classList.add('is-invalid');
                    } else {
                        this.classList.remove('is-valid', 'is-invalid');
                    }
                });
            }
            
            // Validación para Razón Social
            if (razonSocialInput) {
                razonSocialInput.addEventListener('input', function() {
                    if (this.required) {
                        if (this.value.trim().length >= 3) {
                            this.setCustomValidity('');
                            this.classList.remove('is-invalid');
                            this.classList.add('is-valid');
                        } else if (this.value.length > 0) {
                            this.setCustomValidity('La razón social debe tener al menos 3 caracteres');
                            this.classList.remove('is-valid');
                            this.classList.add('is-invalid');
                        } else {
                            this.classList.remove('is-valid', 'is-invalid');
                        }
                    }
                });
            }
            
            // Validación en tiempo real para el teléfono (exactamente 9 dígitos)
            const addressPhone = document.getElementById('address_phone');
            if (addressPhone) {
                addressPhone.addEventListener('input', function(e) {
                    // Solo permitir números
                    this.value = this.value.replace(/[^0-9]/g, '');
                    
                    // Limitar a 9 dígitos
                    if (this.value.length > 9) {
                        this.value = this.value.slice(0, 9);
                    }
                    
                    // Validar que tenga exactamente 9 dígitos
                    if (this.value.length === 9) {
                        this.setCustomValidity('');
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                    } else if (this.value.length > 0) {
                        this.setCustomValidity('El teléfono debe tener exactamente 9 dígitos');
                        this.classList.remove('is-valid');
                        this.classList.add('is-invalid');
                    } else {
                        this.classList.remove('is-valid', 'is-invalid');
                    }
                });
            }
            
            // Validación para dirección completa
            const addressFull = document.getElementById('address_full');
            if (addressFull) {
                addressFull.addEventListener('input', function() {
                    if (this.value.trim().length >= 10) {
                        this.setCustomValidity('');
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                    } else if (this.value.length > 0) {
                        this.setCustomValidity('La dirección debe tener al menos 10 caracteres');
                        this.classList.remove('is-valid');
                        this.classList.add('is-invalid');
                    } else {
                        this.classList.remove('is-valid', 'is-invalid');
                    }
                });
            }
            
            // Validación para selects (departamento, provincia, distrito)
            // Las variables ya están declaradas al inicio del DOMContentLoaded
            
            if (selectDepartamento) {
                selectDepartamento.addEventListener('change', function() {
                    if (this.value) {
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                    } else {
                        this.classList.remove('is-valid', 'is-invalid');
                    }
                });
            }
            
            if (selectProvincia) {
                selectProvincia.addEventListener('change', function() {
                    if (this.value) {
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                    } else {
                        this.classList.remove('is-valid', 'is-invalid');
                    }
                });
            }
            
            if (selectDistrito) {
                selectDistrito.addEventListener('change', function() {
                    if (this.value) {
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                    } else {
                        this.classList.remove('is-valid', 'is-invalid');
                    }
                });
            }
            
            if (selectDepartamento && selectProvincia && selectDistrito) {
                selectDepartamento.addEventListener('change', function() {
                    
                    // Limpiar provincia y distrito
                    selectProvincia.innerHTML = '<option value="">Seleccione departamento primero</option>';
                    selectDistrito.innerHTML = '<option value="">Seleccione provincia primero</option>';
                    selectProvincia.disabled = true;
                    selectDistrito.disabled = true;
                    // Quitar validación visual al limpiar
                    selectProvincia.classList.remove('is-valid', 'is-invalid');
                    selectDistrito.classList.remove('is-valid', 'is-invalid');
                    
                    if (this.value && ubigeoDataProfile) {
                        const departamento = ubigeoDataProfile.departamentos.find(d => d.nombre === this.value);
                        
                        if (departamento && departamento.provincias) {
                            selectProvincia.innerHTML = '<option value="">Seleccionar provincia</option>';
                            selectProvincia.disabled = false;
                            
                            departamento.provincias.forEach(prov => {
                                const option = document.createElement('option');
                                option.value = prov.nombre;
                                option.textContent = prov.nombre;
                                option.dataset.distritos = JSON.stringify(prov.distritos);
                                selectProvincia.appendChild(option);
                            });
                        } 
                    } 
                      
                });
                
                selectProvincia.addEventListener('change', function() {
                    
                    // Limpiar distrito
                    selectDistrito.innerHTML = '<option value="">Seleccione provincia primero</option>';
                    selectDistrito.disabled = true;
                    // Quitar validación visual al limpiar
                    selectDistrito.classList.remove('is-valid', 'is-invalid');
                    
                    if (this.value) {
                        const selectedOption = this.options[this.selectedIndex];
                        const distritos = JSON.parse(selectedOption.dataset.distritos || '[]');
                                                
                        if (distritos && distritos.length > 0) {
                            selectDistrito.innerHTML = '<option value="">Seleccionar distrito</option>';
                            selectDistrito.disabled = false;
                            
                            distritos.forEach(distrito => {
                                const option = document.createElement('option');
                                option.value = distrito;
                                option.textContent = distrito;
                                selectDistrito.appendChild(option);
                            });
                        }
                    }
                });
            }
            
            // Auto-ajustar altura del textarea de referencia
            const addressReference = document.getElementById('address_reference');
            if (addressReference) {
                addressReference.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = (this.scrollHeight) + 'px';
                });
            }
        });
    </script>
    
    <!-- Address Manager Script -->
    <script src="public/assets/js/address-manager.js"></script>
    
    <!-- Fix Modal Scrollbar - PREVENIR BARRA LATERAL -->
    <script src="public/assets/js/fix-modal-scrollbar.js"></script>
    
    <!-- Settings Manager Script -->
    <script src="public/assets/js/settings-manager.js"></script>
    
    <!-- ❌ REMOVIDO: Dark Mode Script (ya se carga desde dark-mode-assets.php en modern-libraries.php) -->
    
    <!-- ❌ REMOVIDO: offcanvas-menu.js (archivo no existe) -->
    
    <!-- Script para conectar botón de favoritos del sidebar con el modal del header -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // ============================================
            // FIX: MANEJO DEL MODAL DE DIRECCIONES
            // ============================================
            $('#addressModal').on('show.bs.modal', function() {
                // Detectar si es móvil
                const isMobile = window.innerWidth <= 768;
                
                if (isMobile) {
                    // En móvil: No bloquear scroll del body (el modal maneja su propio scroll)
                    document.body.style.overflow = 'hidden';
                    document.body.style.paddingRight = '0px';
                    document.documentElement.style.overflow = 'hidden';
                    
                    // Asegurar que el header-section sea visible
                    const header = document.querySelector('.header-section, header');
                    if (header) {
                        header.style.position = 'relative';
                        header.style.zIndex = '1060';
                    }
                } else {
                    // En desktop: comportamiento normal
                    document.body.style.overflow = 'hidden';
                    document.body.style.paddingRight = '0px';
                    document.documentElement.style.overflow = 'hidden';
                }
            });
            
            $('#addressModal').on('hidden.bs.modal', function() {
                // Restaurar estilos
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
                document.documentElement.style.overflow = '';
                
                // Restaurar z-index del header
                const header = document.querySelector('.header-section, header');
                if (header) {
                    header.style.position = '';
                    header.style.zIndex = '';
                }
            });
            
            // Auto-abrir modal de agregar dirección si viene de cart.php
            <?php if($auto_abrir_direccion): ?>
            setTimeout(function() {
                const btnAddAddress = document.getElementById('btn-add-address');
                if(btnAddAddress) {
                    btnAddAddress.click();
                    
                    // Scroll suave a la sección de direcciones
                    const addressesSection = document.getElementById('addresses');
                    if(addressesSection) {
                        addressesSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }
            }, 500);
            <?php endif; ?>
            
            const sidebarFavBtn = document.getElementById('toggle-favorites-sidebar-btn');
            const headerFavBtn = document.getElementById('favorites-link');
            
            if (sidebarFavBtn && headerFavBtn) {
                sidebarFavBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Crear y despachar un evento de click nativo
                    const clickEvent = new MouseEvent('click', {
                        bubbles: true,
                        cancelable: true,
                        view: window
                    });
                    
                    headerFavBtn.dispatchEvent(clickEvent);
                });
            }
            
            // Posicionar el sidebar fijo correctamente centrado
            function positionFixedSidebar() {
                const sidebar = document.querySelector('.profile-sidebar');
                const container = document.querySelector('.profile-section > div');
                
                if (sidebar && container && window.innerWidth >= 992) {
                    const containerRect = container.getBoundingClientRect();
                    const leftPosition = containerRect.left + 15; // 15px es el padding del col
                    sidebar.style.left = leftPosition + 'px';
                } else if (sidebar) {
                    sidebar.style.left = '';
                }
            }
            
            // Ejecutar al cargar y al redimensionar
            positionFixedSidebar();
            window.addEventListener('resize', positionFixedSidebar);
            window.addEventListener('scroll', positionFixedSidebar);
        });
    </script>
    
    <!-- Chatbot Widget -->
    <?php include 'includes/chatbot-widget.php'; ?>

    <!-- Avatar Crop Modal JS (inline) -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mostrar modal al hacer click en el botón de cambiar avatar
        const openBtn = document.querySelector('.btn-change-avatar');
        const modal = document.getElementById('avatar-crop-modal');
        const closeBtn = document.getElementById('closeCropModal');
        const cancelBtn = modal ? modal.querySelector('.btn-cancel') : null;
        const uploadBtn = modal ? modal.querySelector('.btn-upload') : null;
        let croppieInstance = null;

        if (openBtn && modal) {
            openBtn.addEventListener('click', function(e) {
                e.preventDefault();
                modal.classList.remove('hidden');
                // Inicializar Croppie si no existe
                if (!croppieInstance) {
                    croppieInstance = new Croppie(document.getElementById('crop-image'), {
                        viewport: { width: 200, height: 200, type: 'circle' },
                        boundary: { width: 300, height: 300 },
                        enableOrientation: true
                    });
                }
            });
        }

        // Cerrar modal
        function closeModal() {
            modal.classList.add('hidden');
            // Opcional: croppieInstance.destroy(); croppieInstance = null;
        }
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

        // Subir avatar (ejemplo básico, debes adaptar a tu backend)
        if (uploadBtn) {
            uploadBtn.addEventListener('click', function() {
                if (croppieInstance) {
                    uploadBtn.disabled = true;
                    croppieInstance.result({ type: 'base64', size: 'viewport', format: 'png' }).then(function(base64) {
                        // Aquí puedes hacer un fetch/ajax para subir la imagen
                        // Ejemplo:
                        fetch('public/assets/js/avatar-upload.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ avatar: base64 })
                        })
                        .then(res => res.json())
                        .then(data => {
                            // Actualizar avatar en la página si es necesario
                            if (data.success && data.avatar_url) {
                                document.querySelector('.avatar-image').src = data.avatar_url;
                                closeModal();
                            }
                        })
                        .catch(() => {})
                        .finally(() => { uploadBtn.disabled = false; });
                    });
                }
            });
        }
    });
    </script>
</body>
</html>
