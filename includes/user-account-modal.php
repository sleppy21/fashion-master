<?php
/**
 * MODAL DE CUENTA DE USUARIO
 * Muestra la información del usuario logueado
 * Se incluye en las páginas principales
 */

// Solo mostrar si hay un usuario logueado
if($usuario_logueado): 
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
<div id="user-account-modal" class="user-modal" style="display: none;">
    <div class="user-modal-content">
        <button class="user-modal-close" aria-label="Cerrar modal">
            <i class="fa fa-times"></i>
        </button>
        
        <!-- Header con Avatar -->
        <div class="user-modal-header">
            <div class="user-avatar-container">
                <div class="user-avatar-circle" id="modal-user-avatar">
                    <?php 
                    // Construir ruta del avatar igual que en profile.php
                    $modal_avatar_path = 'public/assets/img/profiles/default-avatar.png';
                    if (!empty($usuario_logueado['avatar_usuario'])) {
                        // Si la ruta ya incluye el directorio completo
                        if (strpos($usuario_logueado['avatar_usuario'], 'public/assets/img/profiles/') !== false) {
                            $modal_avatar_path = $usuario_logueado['avatar_usuario'];
                        }
                        // Si solo es el nombre del archivo
                        elseif ($usuario_logueado['avatar_usuario'] !== 'default-avatar.png') {
                            $modal_avatar_path = 'public/assets/img/profiles/' . $usuario_logueado['avatar_usuario'];
                        }
                    }
                    
                    // Verificar si existe una imagen personalizada
                    $tiene_avatar_custom = !empty($usuario_logueado['avatar_usuario']) && 
                                          $usuario_logueado['avatar_usuario'] !== 'default-avatar.png' &&
                                          file_exists($modal_avatar_path);
                    ?>
                    
                    <?php if($tiene_avatar_custom): ?>
                        <!-- Mostrar imagen del avatar -->
                        <img src="<?php echo $modal_avatar_path; ?>" 
                             alt="Avatar" 
                             class="modal-avatar-img" 
                             crossorigin="anonymous">
                    <?php else: ?>
                        <!-- Mostrar inicial si no tiene avatar -->
                        <div class="avatar-initial">
                            <?php echo strtoupper(substr($usuario_logueado['nombre_usuario'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="user-info-header">
                <h3 class="user-name">
                    <?php echo htmlspecialchars($usuario_logueado['nombre_usuario'] . ' ' . ($usuario_logueado['apellido_usuario'] ?? '')); ?>
                </h3>
                <span class="user-role-badge">
                    <i class="fa <?php echo $rol_icon; ?>"></i>
                    <?php echo $rol_texto; ?>
                </span>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="user-stats-grid">
            <div class="stat-card stat-cart" onclick="window.location.href='cart.php'">
                <div class="stat-icon">
                    <i class="fa fa-shopping-cart"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo $cart_count; ?></div>
                    <div class="stat-label">Carrito</div>
                </div>
            </div>
            <div class="stat-card stat-favorites" onclick="document.getElementById('favorites-modal').style.display='block'">
                <div class="stat-icon">
                    <i class="fa fa-heart"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo $favorites_count; ?></div>
                    <div class="stat-label">Favoritos</div>
                </div>
            </div>
        </div>

        <!-- User Info -->
        <div class="user-info-list">
            <!-- Email -->
            <div class="user-info-item">
                <div class="info-icon">
                    <i class="fa fa-envelope"></i>
                </div>
                <div class="info-content">
                    <div class="info-label">Email</div>
                    <div class="info-value"><?php echo htmlspecialchars($usuario_logueado['email_usuario']); ?></div>
                </div>
            </div>

            <!-- Username -->
            <div class="user-info-item">
                <div class="info-icon">
                    <i class="fa fa-user"></i>
                </div>
                <div class="info-content">
                    <div class="info-label">Usuario</div>
                    <div class="info-value">
                        <?php 
                        // Usar username_usuario si existe, sino usar email o nombre
                        if (isset($usuario_logueado['username_usuario']) && !empty($usuario_logueado['username_usuario'])) {
                            echo '@' . htmlspecialchars($usuario_logueado['username_usuario']);
                        } elseif (isset($usuario_logueado['email_usuario']) && !empty($usuario_logueado['email_usuario'])) {
                            echo htmlspecialchars($usuario_logueado['email_usuario']);
                        } else {
                            echo htmlspecialchars($usuario_logueado['nombre_usuario'] ?? 'Usuario');
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- Phone -->
            <div class="user-info-item">
                <div class="info-icon">
                    <i class="fa fa-phone"></i>
                </div>
                <div class="info-content">
                    <div class="info-label">Teléfono</div>
                    <div class="info-value"><?php echo htmlspecialchars($usuario_logueado['telefono_usuario'] ?? 'No registrado'); ?></div>
                </div>
            </div>

            <!-- Member Since -->
            <div class="user-info-item">
                <div class="info-icon">
                    <i class="fa fa-calendar"></i>
                </div>
                <div class="info-content">
                    <div class="info-label">Miembro desde</div>
                    <div class="info-value">
                        <?php 
                        if(!empty($usuario_logueado['fecha_registro'])) {
                            $fecha = new DateTime($usuario_logueado['fecha_registro']);
                            echo $fecha->format('d/m/Y');
                        } else {
                            echo 'No disponible';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="user-modal-footer">
            <a href="profile.php" class="btn-action btn-settings">
                <i class="fa fa-cog"></i> Configuración
            </a>
            <?php if($usuario_logueado['rol_usuario'] === 'admin'): ?>
                <a href="admin.php" class="btn-action btn-admin">
                    <i class="fa fa-tachometer"></i> Admin
                </a>
            <?php endif; ?>
            <a href="logout.php" class="btn-action btn-logout">
                <i class="fa fa-sign-out"></i> Cerrar sesión
            </a>
        </div>
    </div>
</div>
<!-- User Account Modal End -->

<?php endif; ?>
