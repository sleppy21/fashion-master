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
    <div class="offcanvas__close">+</div>
    
    <!-- Logo -->
    <div class="offcanvas__logo">
        <a href="./index.php"><img src="public/assets/img/logo.png" alt="SleppyStore"></a>
    </div>
    
    <!-- Usuario en móvil - Nuevo diseño desde 0 -->
    <?php if(isset($usuario_logueado) && $usuario_logueado): ?>
    <div class="offcanvas__user-compact" id="offcanvas-user-profile">
        <a href="profile.php" class="user-compact-content" style="text-decoration: none; color: inherit;">
            <div class="user-info-header">
                <div class="user-avatar-circle">
                    <i class="fa fa-user"></i>
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
            
            <!-- Categorías con submenu -->
            <li class="offcanvas-has-submenu">
                <a href="#" class="offcanvas-menu-toggle">
                    <i class="fa fa-list"></i> Categorías
                    <i class="fa fa-angle-down" style="float: right; margin-top: 2px;"></i>
                </a>
                <ul class="offcanvas-submenu" style="display: none;">
                    <?php if(isset($categorias) && !empty($categorias)): ?>
                        <?php foreach($categorias as $categoria): ?>
                        <li><a href="shop.php?categoria=<?php echo urlencode($categoria['id_categoria']); ?>"><?php echo htmlspecialchars($categoria['nombre_categoria']); ?></a></li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </li>
            
            <!-- Marcas con submenu -->
            <li class="offcanvas-has-submenu">
                <a href="#" class="offcanvas-menu-toggle">
                    <i class="fa fa-tags"></i> Marcas
                    <i class="fa fa-angle-down" style="float: right; margin-top: 2px;"></i>
                </a>
                <ul class="offcanvas-submenu" style="display: none;">
                    <?php if(isset($marcas) && !empty($marcas)): ?>
                        <?php foreach($marcas as $marca): ?>
                        <li><a href="shop.php?marca=<?php echo urlencode($marca['id_marca']); ?>"><?php echo htmlspecialchars($marca['nombre_marca']); ?></a></li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </li>
            
            <li><a href="./shop.php"><i class="fa fa-shopping-bag"></i> Tienda</a></li>
            <li><a href="./contact.php"><i class="fa fa-envelope"></i> Contacto</a></li>
            
            <?php if(isset($usuario_logueado) && $usuario_logueado): ?>
            <li><a href="profile.php"><i class="fa fa-user-circle"></i> Mi Perfil</a></li>
            <li class="offcanvas-has-submenu">
                <a href="cart.php">
                    <i class="fa fa-shopping-cart"></i> Carrito
                </a>
            </li>
            <?php endif; ?>
            
            <?php if(isset($usuario_logueado) && $usuario_logueado && $usuario_logueado['rol_usuario'] === 'admin'): ?>
            <li class="offcanvas-has-submenu">
                <a href="./admin.php">
                    <i class="fa fa-shield"></i> Vista Administrador
                </a>
            </li>
            <?php endif; ?>
            
            <?php if(isset($usuario_logueado) && $usuario_logueado): ?>
            <li class="offcanvas-has-submenu">
                <a href="logout.php">
                    <i class="fa fa-sign-out"></i> Cerrar Sesión
                </a>
            </li>
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
