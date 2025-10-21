<?php
/**
 * OFFCANVAS MENU GLOBAL - MENÚ MÓVIL RESPONSIVE
 * Este archivo contiene el menú lateral que se muestra en móviles
 * Se incluye en todas las páginas que necesiten el menú móvil
 */
?>
<!-- Offcanvas Menu Begin -->
<div class="offcanvas-menu-overlay"></div>
<div class="offcanvas-menu-wrapper">
    <div class="offcanvas__close" title="Cerrar">+</div>
    
    <!-- Logo -->
    <div class="offcanvas__logo">
        <a href="./index.php"><img src="public/assets/img/logo.png" alt="SleppyStore"></a>
    </div>
    
    <!-- Usuario en móvil - Nuevo diseño desde 0 -->
    <?php if(isset($usuario_logueado) && $usuario_logueado): ?>
    <div class="offcanvas__user-compact" id="offcanvas-user-profile">
        <a href="profile.php" class="user-compact-content" style="text-decoration: none; color: inherit;">
            <div class="user-info-header">
                <?php
                // Lógica del avatar igual que en header
                $offcanvas_avatar_path = 'public/assets/img/profiles/default-avatar.png';
                if (!empty($usuario_logueado['avatar_usuario'])) {
                    if (strpos($usuario_logueado['avatar_usuario'], 'public/assets/img/profiles/') !== false) {
                        $offcanvas_avatar_path = $usuario_logueado['avatar_usuario'];
                    } elseif ($usuario_logueado['avatar_usuario'] !== 'default-avatar.png') {
                        $offcanvas_avatar_path = 'public/assets/img/profiles/' . $usuario_logueado['avatar_usuario'];
                    }
                }
                
                $tiene_avatar_custom = !empty($usuario_logueado['avatar_usuario']) && 
                                      $usuario_logueado['avatar_usuario'] !== 'default-avatar.png' &&
                                      file_exists($offcanvas_avatar_path);
                ?>
                
                <div class="user-avatar-circle">
                    <?php if($tiene_avatar_custom): ?>
                        <img src="<?php echo $offcanvas_avatar_path; ?>" 
                             alt="Avatar" 
                             class="avatar-image"
                             crossorigin="anonymous"
                             style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover; object-position: center; display: block;">
                    <?php else: ?>
                        <div class="avatar-initial" style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold;">
                            <?php echo strtoupper(substr($usuario_logueado['nombre_usuario'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="user-text">
                    <span class="user-greeting">Hola,</span>
                    <span class="user-name"><?php echo htmlspecialchars($usuario_logueado['nombre_usuario']); ?></span>
                </div>
            </div>
        </a>
    </div>
    <?php endif; ?>
    
    <!-- Menú de navegación -->
    <nav class="offcanvas__nav">
        <ul>
            <li><a href="./index.php"><i class="fa fa-home"></i> Inicio</a></li>
            <li><a href="./shop.php"><i class="fa fa-shopping-bag"></i> Tienda</a></li>
            <li><a href="./contact.php"><i class="fa fa-envelope"></i> Contacto</a></li>
            
            <?php if(isset($usuario_logueado) && $usuario_logueado): ?>
            <li><a href="cart.php"><i class="fa fa-shopping-cart"></i> Carrito</a></li>
            <li><a href="./mis-pedidos.php"><i class="fa fa-shopping-bag"></i> Mis Compras</a></li>
            <li><a href="profile.php"><i class="fa fa-user-circle"></i> Mi Perfil</a></li>
            <?php endif; ?>
            
            <?php if(isset($usuario_logueado) && $usuario_logueado && $usuario_logueado['rol_usuario'] === 'admin'): ?>
            <li><a href="./admin.php"><i class="fa fa-shield"></i> Admin</a></li>
            <?php endif; ?>
            
            <!-- Asistente Virtual -->
            <li><a href="#" id="open-chatbot-mobile"><i class="fa fa-robot"></i> Asistente Virtual</a></li>
            
            <?php if(isset($usuario_logueado) && $usuario_logueado): ?>
            <li><a href="logout.php"><i class="fa fa-sign-out"></i> Cerrar Sesión</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    
    <!-- Iconos -->
    <ul class="offcanvas__widget">
        <li><span class="icon_search search-switch"></span></li>
        <?php if(isset($usuario_logueado) && $usuario_logueado): ?>
        <li><a href="#" id="notifications-link-mobile" title="Notificaciones">
            <i class="fa fa-bell"></i>
            <?php if(isset($notifications_count) && $notifications_count > 0): ?>
            <div class="tip"><?php echo $notifications_count; ?></div>
            <?php endif; ?>
        </a></li>
        <li><a href="#" id="favorites-link-mobile"><span class="icon_heart_alt"></span>
            <?php if(isset($favorites_count) && $favorites_count > 0): ?>
            <div class="tip"><?php echo $favorites_count; ?></div>
            <?php endif; ?>
        </a></li>
        <?php else: ?>
        <li><a href="login.php"><span class="icon_heart_alt"></span></a></li>
        <?php endif; ?>
        <li><a href="cart.php"><span class="icon_bag_alt"></span>
            <?php if(isset($cart_count) && $cart_count > 0): ?>
            <div class="tip"><?php echo $cart_count; ?></div>
            <?php endif; ?>
        </a></li>
    </ul>
    
    <!-- Botones de autenticación (solo si no está logueado) -->
    <?php if(!isset($usuario_logueado) || !$usuario_logueado): ?>
    <div class="offcanvas__auth">
        <a href="login.php">Iniciar Sesión</a>
        <a href="register.php">Registrarse</a>
    </div>
    <?php endif; ?>
</div>
<!-- Offcanvas Menu End -->
