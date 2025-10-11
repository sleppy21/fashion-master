<?php
/**
 * MODAL DE CUENTA DE USUARIO
 * Muestra la información del usuario logueado
 * Se incluye en las páginas principales
 */

// Solo mostrar si hay un usuario logueado
if($usuario_logueado): 
    // Calcular inicial del nombre para el avatar
    $inicial = strtoupper(substr($usuario_logueado['nombre_usuario'], 0, 1));
    
    // Determinar rol en español
    $rol_texto = 'Cliente';
    $rol_icon = 'fa-user';
    if($usuario_logueado['rol_usuario'] === 'admin') {
        $rol_texto = 'Administrador';
        $rol_icon = 'fa-shield';
    } elseif($usuario_logueado['rol_usuario'] === 'vendedor') {
        $rol_texto = 'Vendedor';
        $rol_icon = 'fa-briefcase';
    }
?>
<!-- User Account Modal Begin -->
<div id="user-account-modal" class="user-modal">
    <div class="user-modal-overlay"></div>
    <div class="user-modal-content">
        <button class="user-modal-close" aria-label="Cerrar modal">
            <i class="fa fa-times"></i>
        </button>
        
        <!-- Header con gradiente -->
        <div class="user-modal-header">
            <div class="header-background"></div>
            <div class="user-avatar-container">
                <div class="user-avatar-circle">
                    <span class="avatar-initial"><?php echo $inicial; ?></span>
                    <div class="avatar-status"></div>
                </div>
            </div>
            <div>
                <h3 class="user-name"><?php echo htmlspecialchars($usuario_logueado['nombre_usuario']); ?></h3>
                <div class="user-role-badge">
                    <i class="fa <?php echo $rol_icon; ?>"></i>
                    <span><?php echo $rol_texto; ?></span>
                </div>
            </div>
        </div>

        <!-- Body con información -->
        <div class="user-modal-body">
            <!-- Stats Cards -->
            <div class="user-stats-grid">
                <div class="stat-card stat-cart">
                    <div class="stat-icon">
                        <i class="fa fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-number"><?php echo $cart_count; ?></span>
                        <span class="stat-label">Carrito</span>
                    </div>
                </div>
                <div class="stat-card stat-favorites">
                    <div class="stat-icon">
                        <i class="fa fa-heart"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-number"><?php echo $favorites_count; ?></span>
                        <span class="stat-label">Favoritos</span>
                    </div>
                </div>
            </div>

            <!-- Info Items -->
            <div class="user-info-list">
                <div class="user-info-item">
                    <div class="info-icon">
                        <i class="fa fa-user"></i>
                    </div>
                    <div class="info-content">
                        <span class="info-label">Usuario</span>
                        <span class="info-value">@<?php echo htmlspecialchars($usuario_logueado['nombre_usuario'] ?? ''); ?></span>
                    </div>
                </div>
                
                <div class="user-info-item">
                    <div class="info-icon">
                        <i class="fa fa-envelope"></i>
                    </div>
                    <div class="info-content">
                        <span class="info-label">Correo electrónico</span>
                        <span class="info-value"><?php echo htmlspecialchars($usuario_logueado['email_usuario']); ?></span>
                    </div>
                </div>

                <?php if(!empty($usuario_logueado['telefono_usuario'])): ?>
                <div class="user-info-item">
                    <div class="info-icon">
                        <i class="fa fa-phone"></i>
                    </div>
                    <div class="info-content">
                        <span class="info-label">Teléfono</span>
                        <span class="info-value"><?php echo htmlspecialchars($usuario_logueado['telefono_usuario']); ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <div class="user-info-item">
                    <div class="info-icon">
                        <i class="fa fa-calendar"></i>
                    </div>
                    <div class="info-content">
                        <span class="info-label">Miembro desde</span>
                        <span class="info-value"><?php echo !empty($usuario_logueado['fecha_registro']) ? date('d/m/Y', strtotime($usuario_logueado['fecha_registro'])) : 'N/A'; ?></span>
                    </div>
                </div>

                <?php if(!empty($usuario_logueado['ultimo_acceso'])): ?>
                <div class="user-info-item">
                    <div class="info-icon">
                        <i class="fa fa-clock-o"></i>
                    </div>
                    <div class="info-content">
                        <span class="info-label">Último acceso</span>
                        <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($usuario_logueado['ultimo_acceso'])); ?></span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Footer con acciones -->
        <div class="user-modal-footer">
            <a href="account.php" class="btn-action btn-primary">
                <i class="fa fa-user"></i>
                <span>Mi perfil</span>
            </a>
            
            <?php if($usuario_logueado['rol_usuario'] === 'admin'): ?>
            <a href="admin.php" class="btn-action btn-admin">
                <i class="fa fa-cog"></i>
                <span>Panel Admin</span>
            </a>
            <?php endif; ?>
            
            <a href="logout.php" class="btn-action btn-logout">
                <i class="fa fa-sign-out"></i>
                <span>Cerrar sesión</span>
            </a>
        </div>
    </div>
</div>
<!-- User Account Modal End -->
<?php endif; ?>
