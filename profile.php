<?php
/**
 * PÁGINA DE PERFIL DE USUARIO
 * Permite ver y editar información personal del usuario
 */

session_start();
require_once 'config/conexion.php';
require_once 'config/config.php';

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
    
    <!-- Font Awesome 6.4.0 (Iconos modernos - Misma versión que cart.php) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="public/assets/css/elegant-icons.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/jquery-ui.min.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/slicknav.min.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/style.css" type="text/css">
    <!-- Header Standard - COMPACTO v5.0 -->
    <link rel="stylesheet" href="public/assets/css/header-standard.css?v=5.0" type="text/css">
    

    <!-- Modern Libraries -->
    <?php include 'includes/modern-libraries.php'; ?>
    
    <!-- User Account Modal Styles -->
    <link rel="stylesheet" href="public/assets/css/user-account-modal.css" type="text/css">
    
    <!-- Favorites Modal Styles -->
    <link rel="stylesheet" href="public/assets/css/favorites-modal.css" type="text/css">
    
    <!-- Dark Mode Styles -->
    <link rel="stylesheet" href="public/assets/css/dark-mode.css" type="text/css">
    
    <!-- Global Responsive Styles - TODO EL PROYECTO -->
    <link rel="stylesheet" href="public/assets/css/global-responsive.css?v=1.0" type="text/css">
    
    <!-- Avatar Flight Animation -->
    <link rel="stylesheet" href="public/assets/css/avatar-flight-animation.css?v=1.0" type="text/css">
    
    <!-- Modern Styles -->
    <link rel="stylesheet" href="public/assets/css/modals-animations.css?v=<?= time() ?>">
    <link rel="stylesheet" href="public/assets/css/notifications-modal.css">
    
    <!-- Profile Styles -->
    <link rel="stylesheet" href="public/assets/css/profile.css" type="text/css">
    
    <!-- Croppie CSS for Avatar Upload -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.min.css">
    
    <!-- Avatar Crop Modal Styles -->
    <link rel="stylesheet" href="public/assets/css/avatar-crop-modal.css" type="text/css">
    
    <!-- Header Fix - DEBE IR AL FINAL -->
    <link rel="stylesheet" href="public/assets/css/shop/shop-header-fix.css?v=<?= time() ?>">
    
    <style>
        /* ============================================
           FONDO DEL BODY
           ============================================ */
        body {
            background-color: #f8f5f2 !important;
        }
        
        /* Dark mode */
        body.dark-mode {
            background-color: #1a1a1a !important;
        }
    </style>
</head>

<body>
    <!-- Offcanvas Menu -->
    <?php include 'includes/offcanvas-menu.php'; ?>
    
    <!-- Header con modales -->
    <?php include 'includes/header-section.php'; ?>
    
    <!-- Profile Section -->
    <section class="profile-section">
        <div class="container">
            <div class="row">
                <!-- Sidebar -->
                <div class="col-lg-4 mb-4">
                    <div class="profile-sidebar">
                        <!-- Avatar Section -->
                        <div class="profile-avatar-section">
                            <div class="avatar-wrapper" id="avatar-upload-area" style="cursor: pointer;">
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
                        
                        <!-- Stats Cards -->
                        <div class="profile-stats">
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
                        <div class="quick-actions">
                            <a href="cart.php" class="action-btn">
                                <i class="fa fa-shopping-cart"></i>
                                <span>Mi Carrito</span>
                                <?php if($cart_count > 0): ?>
                                    <span class="action-badge"><?php echo $cart_count; ?></span>
                                <?php endif; ?>
                            </a>
                            <a href="#" class="action-btn" onclick="document.getElementById('favorites-modal').style.display='block'; return false;">
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
                    </div>
                </div>
                
                <!-- Main Content -->
                <div class="col-lg-8">
                    <!-- Tabs Navigation -->
                    <div class="profile-tabs">
                        <button class="tab-btn active" data-tab="personal-info">
                            <i class="fa fa-user"></i> Información Personal
                        </button>
                        <button class="tab-btn" data-tab="security">
                            <i class="fa fa-lock"></i> Seguridad
                        </button>
                        <button class="tab-btn" data-tab="addresses">
                            <i class="fa fa-map-marker-alt"></i> Direcciones
                        </button>
                        <button class="tab-btn" data-tab="settings">
                            <i class="fa fa-cog"></i> Configuración
                        </button>
                    </div>
                    
                    <!-- Tab Content: Personal Info -->
                    <div class="tab-content active" id="personal-info">
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
                                            <label><i class="fa fa-user"></i> Nombre</label>
                                            <input type="text" class="form-control" name="nombre" 
                                                   value="<?php echo htmlspecialchars($usuario_logueado['nombre_usuario']); ?>" disabled>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><i class="fa fa-user"></i> Apellido</label>
                                            <input type="text" class="form-control" name="apellido" 
                                                   value="<?php echo htmlspecialchars($usuario_logueado['apellido_usuario']); ?>" disabled>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><i class="fa fa-at"></i> Nombre de Usuario</label>
                                            <input type="text" class="form-control" name="username" 
                                                   value="<?php echo htmlspecialchars($usuario_logueado['username_usuario']); ?>" disabled>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><i class="fa fa-envelope"></i> Email</label>
                                            <input type="email" class="form-control" name="email" 
                                                   value="<?php echo htmlspecialchars($usuario_logueado['email_usuario']); ?>" disabled>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><i class="fa fa-phone"></i> Teléfono</label>
                                            <input type="tel" class="form-control" name="telefono" 
                                                   value="<?php echo htmlspecialchars($usuario_logueado['telefono_usuario'] ?? ''); ?>" disabled>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><i class="fa fa-birthday-cake"></i> Fecha de Nacimiento</label>
                                            <input type="date" class="form-control" name="fecha_nacimiento" 
                                                   value="<?php echo $usuario_logueado['fecha_nacimiento'] ?? ''; ?>" disabled>
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
                    <div class="tab-content" id="security">
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
                    <div class="tab-content" id="addresses">
                        <div class="profile-card">
                            <div class="card-header">
                                <h4><i class="fa fa-map-marked-alt"></i> Mis Direcciones</h4>
                                <button type="button" class="btn-edit" id="btn-add-address">
                                    <i class="fa fa-plus"></i> Agregar Dirección
                                </button>
                            </div>
                            
                            <div class="addresses-list">
                                <?php if (empty($direcciones_usuario)): ?>
                                    <div class="empty-state">
                                        <i class="fa fa-map-marker-alt"></i>
                                        <h5>No tienes direcciones guardadas</h5>
                                        <p>Agrega una dirección para agilizar tus compras</p>
                                        <button type="button" class="btn btn-primary" id="btn-add-first-address">
                                            <i class="fa fa-plus"></i> Agregar Primera Dirección
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
                <div class="tab-content" id="settings">
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
                                            <label class="theme-option">
                                                <input type="radio" name="theme_mode" value="auto">
                                                <div class="theme-card">
                                                    <i class="fa fa-palette"></i>
                                                    <span>Dinámico</span>
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
    
    <!-- Avatar Crop Modal -->
    <?php include 'includes/avatar-crop-modal.php'; ?>
    
    <!-- Modal para Agregar/Editar Dirección -->
    <div class="modal fade" id="addressModal" tabindex="-1" role="dialog" aria-labelledby="addressModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
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
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="address_name">Nombre de la Dirección *</label>
                                    <input type="text" class="form-control" id="address_name" name="nombre_direccion" 
                                           placeholder="Ej: Casa, Trabajo, Casa de mamá" required>
                                    <small class="form-text text-muted">Un nombre para identificar fácilmente esta dirección</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="address_phone">Teléfono</label>
                                    <input type="tel" class="form-control" id="address_phone" name="telefono" 
                                           placeholder="999 999 999" maxlength="15">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="address_full">Dirección Completa *</label>
                            <input type="text" class="form-control" id="address_full" name="direccion_completa" 
                                   placeholder="Calle, número, urbanización" required>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="address_departamento">Departamento *</label>
                                    <select class="form-control" id="address_departamento" name="departamento" required>
                                        <option value="">Seleccionar</option>
                                        <option value="Lima">Lima</option>
                                        <option value="Arequipa">Arequipa</option>
                                        <option value="Cusco">Cusco</option>
                                        <option value="La Libertad">La Libertad</option>
                                        <option value="Piura">Piura</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="address_provincia">Provincia *</label>
                                    <select class="form-control" id="address_provincia" name="provincia" required>
                                        <option value="">Seleccionar</option>
                                        <option value="Lima">Lima</option>
                                        <option value="Callao">Callao</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="address_distrito">Distrito *</label>
                                    <select class="form-control" id="address_distrito" name="distrito" required>
                                        <option value="">Seleccionar</option>
                                        <option value="Miraflores">Miraflores</option>
                                        <option value="San Isidro">San Isidro</option>
                                        <option value="Surco">Surco</option>
                                        <option value="La Molina">La Molina</option>
                                        <option value="San Borja">San Borja</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="address_reference">Referencia</label>
                            <textarea class="form-control" id="address_reference" name="referencia" 
                                      rows="2" placeholder="Alguna referencia para encontrar tu dirección más fácil"></textarea>
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
            
            window.BASE_URL = baseUrlFromPHP;
            console.log('🌐 BASE_URL configurado:', window.BASE_URL);
        })();
    </script>
    <script src="public/assets/js/jquery-3.3.1.min.js"></script>
    
    <!-- Fetch API Handler Moderno - Reemplaza AJAX/jQuery -->
    <script src="public/assets/js/fetch-api-handler.js"></script>
    
    <script src="public/assets/js/bootstrap.min.js"></script>
    <script src="public/assets/js/jquery-ui.min.js"></script>
    <script src="public/assets/js/jquery.slicknav.js"></script>
    <script src="public/assets/js/main.js"></script>
    
    <!-- Header Handler - Actualización en tiempo real de contadores -->
    <script src="public/assets/js/header-handler.js?v=1.0"></script>
    
    <!-- Sistema Global de Contadores -->
    <script src="public/assets/js/global-counters.js"></script>
    
    <!-- Real-time Updates System - DEBE IR ANTES que cart-favorites-handler -->
    <script src="public/assets/js/real-time-updates.js?v=<?= time() ?>"></script>
    
    <script src="public/assets/js/user-account-modal.js"></script>
    <script src="public/assets/js/cart-favorites-handler.js"></script>
    
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
    
    <!-- Address Manager Script -->
    <script src="public/assets/js/address-manager.js"></script>
    
    <!-- Settings Manager Script -->
    <script src="public/assets/js/settings-manager.js"></script>
    
    <!-- Dark Mode Script -->
    <script src="public/assets/js/dark-mode.js"></script>
    
    <!-- Offcanvas Menu Global JS -->
    <script src="public/assets/js/offcanvas-menu.js"></script>
    
    <!-- Chatbot Widget -->
    <?php include 'includes/chatbot-widget.php'; ?>
</body>
</html>
