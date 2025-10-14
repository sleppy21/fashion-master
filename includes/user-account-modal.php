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
        <button class="user-modal-close" aria-label="Cerrar modal" style="position: absolute; top: 12px; right: 12px; width: 32px; height: 32px; border: none; border-radius: 50%; cursor: pointer; z-index: 10; display: flex; align-items: center; justify-content: center; transition: transform 0.3s ease;">
            <i class="fa fa-times"></i>
        </button>
        
        <!-- Header -->
        <div class="user-modal-header" style="padding: 15px; border-bottom: 2px solid; flex-shrink: 0;">
            <h3 style="margin: 0 0 5px 0; font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                <i class="fa fa-user-circle"></i> Mi Cuenta
            </h3>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <p style="margin: 0; font-size: 12px; font-weight: 500;">
                    <?php echo htmlspecialchars($usuario_logueado['nombre_usuario'] . ' ' . ($usuario_logueado['apellido_usuario'] ?? '')); ?>
                </p>
                <p style="margin: 0; font-size: 11px; opacity: 0.8;">
                    <i class="fa <?php echo $rol_icon; ?>" style="margin-right: 4px;"></i><?php echo $rol_texto; ?>
                </p>
            </div>
        </div>

        <!-- Body -->
        <div class="user-modal-body" style="flex: 1; overflow-y: auto; overflow-x: hidden; padding: 12px; min-height: 0;">
            <!-- Stats Grid -->
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; margin-bottom: 15px;">
                <div style="text-align: center; padding: 12px; border-radius: 8px; background: #f8f9fa;">
                    <div style="font-size: 18px; font-weight: 700; color: #ca1515; margin-bottom: 4px;">
                        <?php echo $cart_count; ?>
                    </div>
                    <div style="font-size: 10px; opacity: 0.7; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">
                        Carrito
                    </div>
                </div>
                <div style="text-align: center; padding: 12px; border-radius: 8px; background: #f8f9fa;">
                    <div style="font-size: 18px; font-weight: 700; color: #ca1515; margin-bottom: 4px;">
                        <?php echo $favorites_count; ?>
                    </div>
                    <div style="font-size: 10px; opacity: 0.7; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">
                        Favoritos
                    </div>
                </div>
                <div style="text-align: center; padding: 12px; border-radius: 8px; background: #f8f9fa;">
                    <div style="font-size: 18px; font-weight: 700; color: #ca1515; margin-bottom: 4px;">
                        <?php 
                        $fecha_registro = !empty($usuario_logueado['fecha_registro']) ? strtotime($usuario_logueado['fecha_registro']) : time();
                        $dias_miembro = floor((time() - $fecha_registro) / (60 * 60 * 24));
                        echo $dias_miembro;
                        ?>
                    </div>
                    <div style="font-size: 10px; opacity: 0.7; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">
                        Días
                    </div>
                </div>
            </div>

            <!-- User Info -->
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <!-- Email -->
                <div style="padding: 10px; border-radius: 8px; background: #f8f9fa; display: flex; align-items: center; gap: 10px;">
                    <i class="fa fa-envelope" style="color: #ca1515; font-size: 14px;"></i>
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-size: 10px; margin-bottom: 2px; opacity: 0.6; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Email</div>
                        <div style="font-size: 12px; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?php echo htmlspecialchars($usuario_logueado['email_usuario']); ?></div>
                    </div>
                </div>

                <!-- Username -->
                <div style="padding: 10px; border-radius: 8px; background: #f8f9fa; display: flex; align-items: center; gap: 10px;">
                    <i class="fa fa-user" style="color: #ca1515; font-size: 14px;"></i>
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-size: 10px; margin-bottom: 2px; opacity: 0.6; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Usuario</div>
                        <div style="font-size: 12px; font-weight: 600;">@<?php echo htmlspecialchars($usuario_logueado['username_usuario']); ?></div>
                    </div>
                </div>

                <!-- Phone -->
                <div style="padding: 10px; border-radius: 8px; background: #f8f9fa; display: flex; align-items: center; gap: 10px;">
                    <i class="fa fa-phone" style="color: #ca1515; font-size: 14px;"></i>
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-size: 10px; margin-bottom: 2px; opacity: 0.6; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Teléfono</div>
                        <div style="font-size: 12px; font-weight: 600;"><?php echo htmlspecialchars($usuario_logueado['telefono_usuario'] ?? 'No registrado'); ?></div>
                    </div>
                </div>

                <!-- Member Since -->
                <div style="padding: 10px; border-radius: 8px; background: #f8f9fa; display: flex; align-items: center; gap: 10px;">
                    <i class="fa fa-calendar" style="color: #ca1515; font-size: 14px;"></i>
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-size: 10px; margin-bottom: 2px; opacity: 0.6; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Miembro desde</div>
                        <div style="font-size: 12px; font-weight: 600;">
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

                <!-- Last Access -->
                <div style="padding: 10px; border-radius: 8px; background: #f8f9fa; display: flex; align-items: center; gap: 10px;">
                    <i class="fa fa-clock-o" style="color: #ca1515; font-size: 14px;"></i>
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-size: 10px; margin-bottom: 2px; opacity: 0.6; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Último acceso</div>
                        <div style="font-size: 12px; font-weight: 600;">
                            <?php 
                            if(!empty($usuario_logueado['ultimo_acceso'])) {
                                $fecha = new DateTime($usuario_logueado['ultimo_acceso']);
                                echo $fecha->format('d/m/Y H:i');
                            } else {
                                echo date('d/m/Y H:i');
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="user-modal-footer" style="padding: 12px; border-top: 1px solid #e9ecef; display: flex; flex-direction: column; gap: 8px; flex-shrink: 0;">
            <?php if($usuario_logueado['rol_usuario'] === 'admin'): ?>
                <a href="admin.php" class="btn-admin" style="padding: 10px; border-radius: 8px; background: #111; color: white; text-align: center; text-decoration: none; font-size: 13px; font-weight: 600; transition: background 0.3s ease;">
                    <i class="fa fa-tachometer"></i> Panel Admin
                </a>
            <?php endif; ?>
            <a href="logout.php" class="btn-logout" style="padding: 10px; border-radius: 8px; background: #f8f9fa; color: #111; text-align: center; text-decoration: none; font-size: 13px; font-weight: 600; transition: background 0.3s ease;">
                <i class="fa fa-sign-out"></i> Cerrar sesión
            </a>
        </div>
    </div>
</div>
<!-- User Account Modal End -->

<?php endif; ?>
