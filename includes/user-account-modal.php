<?php
/**
 * MODAL DE CUENTA DE USUARIO
 * Diseño idéntico a notificaciones y favoritos
 * Se incluye en las páginas principales
 */

// Solo mostrar si hay un usuario logueado
if($usuario_logueado): 
    // Determinar rol en español
    $rol_texto = 'Cliente';
    $rol_icon = 'fa-user';
    $rol_color = 'background: #e3f2fd; color: #1976d2;';
    
    if($usuario_logueado['rol_usuario'] === 'admin') {
        $rol_texto = 'Administrador';
        $rol_icon = 'fa-shield';
        $rol_color = 'background: #ffebee; color: #c62828;';
    } elseif($usuario_logueado['rol_usuario'] === 'vendedor') {
        $rol_texto = 'Vendedor';
        $rol_icon = 'fa-briefcase';
        $rol_color = 'background: #fff3e0; color: #f57c00;';
    }
    
    // Construir ruta del avatar
    $modal_avatar_path = 'public/assets/img/profiles/default-avatar.png';
    if (!empty($usuario_logueado['avatar_usuario'])) {
        if (strpos($usuario_logueado['avatar_usuario'], 'public/assets/img/profiles/') !== false) {
            $modal_avatar_path = $usuario_logueado['avatar_usuario'];
        } elseif ($usuario_logueado['avatar_usuario'] !== 'default-avatar.png') {
            $modal_avatar_path = 'public/assets/img/profiles/' . $usuario_logueado['avatar_usuario'];
        }
    }
    
    $tiene_avatar_custom = !empty($usuario_logueado['avatar_usuario']) && 
                          $usuario_logueado['avatar_usuario'] !== 'default-avatar.png' &&
                          file_exists($modal_avatar_path);
?>
<!-- User Account Modal Begin -->
<div id="user-account-modal" class="user-account-modal">
    <div class="user-account-modal-content">
        <!-- ✅ Handle para deslizar (swipe indicator) -->
        <div class="modal-swipe-handle" style="width: 100%; padding: 12px 0 8px 0; display: flex; justify-content: center; cursor: grab; touch-action: pan-y;">
            <div style="width: 40px; height: 4px; background: rgba(0,0,0,0.2); border-radius: 2px;"></div>
        </div>
        
        <button class="user-account-modal-close" aria-label="Cerrar modal" style="position: absolute; top: 8px; right: 8px; width: 28px; height: 28px; border: none; border-radius: 50%; cursor: pointer; z-index: 10; display: flex; align-items: center; justify-content: center; transition: transform 0.3s ease;">
            <i class="fa fa-times" style="font-size: 14px;"></i>
        </button>
        
        <!-- Header Compacto -->
        <div class="user-account-modal-header" style="padding: 10px 12px; border-bottom: 1px solid; flex-shrink: 0;">
            <h3 style="margin: 0; font-size: 14px; font-weight: 700; display: flex; align-items: center; gap: 6px;">
                <i class="fa fa-user-circle"></i> Mi Cuenta
            </h3>
        </div>

        <!-- Body -->
        <div class="user-account-modal-body" style="flex: 1; overflow-y: auto; overflow-x: hidden; padding: 10px; min-height: 0;">
            
            <!-- Avatar y Rol Compacto -->
            <div style="display: flex; gap: 10px; padding: 8px; border-radius: 6px; border: 1px solid #eee; background: white; margin-bottom: 6px; align-items: center;">
                <div style="width: 45px; height: 45px; flex-shrink: 0; border-radius: 50%; overflow: hidden; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(102, 126, 234, 0.25);">
                    <?php if($tiene_avatar_custom): ?>
                        <img src="<?php echo $modal_avatar_path; ?>" 
                             alt="Avatar" 
                             style="width: 100%; height: 100%; object-fit: cover;"
                             crossorigin="anonymous">
                    <?php else: ?>
                        <span style="font-size: 18px; font-weight: 700; color: white;">
                            <?php echo strtoupper(substr($usuario_logueado['nombre_usuario'], 0, 1)); ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div style="flex: 1; min-width: 0;">
                    <div style="font-size: 13px; font-weight: 700; margin-bottom: 3px; line-height: 1.2; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        <?php echo htmlspecialchars($usuario_logueado['nombre_usuario'] . ' ' . ($usuario_logueado['apellido_usuario'] ?? '')); ?>
                    </div>
                    <div style="display: inline-flex; align-items: center; gap: 4px; padding: 2px 6px; border-radius: 8px; font-size: 9px; font-weight: 600; <?php echo $rol_color; ?>">
                        <i class="fa <?php echo $rol_icon; ?>"></i>
                        <?php echo $rol_texto; ?>
                    </div>
                </div>
            </div>

            <!-- Stats Compactos -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 6px; margin-bottom: 8px;">
                <div style="padding: 8px; border-radius: 6px; background: #f8f9fa; text-align: center; cursor: pointer; transition: all 0.3s; border: 1px solid #eee;" onclick="window.location.href='cart.php'">
                    <i class="fa fa-shopping-cart" style="font-size: 16px; color: #667eea; margin-bottom: 2px;"></i>
                    <div style="font-size: 14px; font-weight: 700; line-height: 1; margin-bottom: 1px;"><?php echo $cart_count; ?></div>
                    <div style="font-size: 9px; opacity: 0.7;">Carrito</div>
                </div>
                <div style="padding: 8px; border-radius: 6px; background: #f8f9fa; text-align: center; cursor: pointer; transition: all 0.3s; border: 1px solid #eee;" onclick="document.getElementById('favorites-modal').classList.add('active');">
                    <i class="fa fa-heart" style="font-size: 16px; color: #f87171; margin-bottom: 2px;"></i>
                    <div style="font-size: 14px; font-weight: 700; line-height: 1; margin-bottom: 1px;"><?php echo $favorites_count; ?></div>
                    <div style="font-size: 9px; opacity: 0.7;">Favoritos</div>
                </div>
            </div>

            <!-- Información Compacta (Solo Email) -->
            <div style="display: flex; gap: 8px; padding: 8px; border-radius: 6px; border: 1px solid #eee; background: white; margin-bottom: 8px;">
                <div style="width: 32px; height: 32px; flex-shrink: 0; border-radius: 50%; background: #f3e5f5; color: #7b1fa2; display: flex; align-items: center; justify-content: center; font-size: 14px;">
                    <i class="fa fa-envelope"></i>
                </div>
                <div style="flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 1px;">
                    <small style="font-size: 9px; opacity: 0.6; color: #888;">Email</small>
                    <p style="margin: 0; font-size: 11px; line-height: 1.3; opacity: 0.9; color: #555; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        <?php echo htmlspecialchars($usuario_logueado['email_usuario']); ?>
                    </p>
                </div>
            </div>

            <!-- Acciones Compactas -->
            <div style="display: flex; flex-direction: column; gap: 5px;">
                
                <a href="profile.php" style="display: flex; align-items: center; gap: 8px; padding: 8px; border-radius: 6px; background: white; border: 1px solid #eee; text-decoration: none; transition: all 0.3s;">
                    <div style="width: 30px; height: 30px; flex-shrink: 0; border-radius: 50%; background: #e3f2fd; color: #1976d2; display: flex; align-items: center; justify-content: center; font-size: 13px;">
                        <i class="fa fa-cog"></i>
                    </div>
                    <div style="flex: 1;">
                        <div style="font-size: 12px; font-weight: 600; color: #2c3e50;">Configuración</div>
                    </div>
                    <i class="fa fa-chevron-right" style="font-size: 10px; opacity: 0.3;"></i>
                </a>

                <a href="mis-pedidos.php" style="display: flex; align-items: center; gap: 8px; padding: 8px; border-radius: 6px; background: white; border: 1px solid #eee; text-decoration: none; transition: all 0.3s;">
                    <div style="width: 30px; height: 30px; flex-shrink: 0; border-radius: 50%; background: #fff3e0; color: #f57c00; display: flex; align-items: center; justify-content: center; font-size: 13px;">
                        <i class="fa fa-shopping-bag"></i>
                    </div>
                    <div style="flex: 1;">
                        <div style="font-size: 12px; font-weight: 600; color: #2c3e50;">Mis Pedidos</div>
                    </div>
                    <i class="fa fa-chevron-right" style="font-size: 10px; opacity: 0.3;"></i>
                </a>

                <?php if($usuario_logueado['rol_usuario'] === 'admin'): ?>
                <a href="admin.php" style="display: flex; align-items: center; gap: 8px; padding: 8px; border-radius: 6px; background: white; border: 1px solid #eee; text-decoration: none; transition: all 0.3s;">
                    <div style="width: 30px; height: 30px; flex-shrink: 0; border-radius: 50%; background: #ffebee; color: #c62828; display: flex; align-items: center; justify-content: center; font-size: 13px;">
                        <i class="fa fa-tachometer"></i>
                    </div>
                    <div style="flex: 1;">
                        <div style="font-size: 12px; font-weight: 600; color: #2c3e50;">Panel Admin</div>
                    </div>
                    <i class="fa fa-chevron-right" style="font-size: 10px; opacity: 0.3;"></i>
                </a>
                <?php endif; ?>

                <a href="logout.php" style="display: flex; align-items: center; gap: 8px; padding: 8px; border-radius: 6px; background: #ffebee; border: 1px solid #ffcdd2; text-decoration: none; transition: all 0.3s; margin-top: 4px;">
                    <div style="width: 30px; height: 30px; flex-shrink: 0; border-radius: 50%; background: white; color: #dc3545; display: flex; align-items: center; justify-content: center; font-size: 13px;">
                        <i class="fa fa-sign-out"></i>
                    </div>
                    <div style="flex: 1;">
                        <div style="font-size: 12px; font-weight: 600; color: #dc3545;">Cerrar Sesión</div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
<!-- User Account Modal End -->

<?php endif; ?>
