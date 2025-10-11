<!-- Header Section Begin -->
<header class="header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-xl-3 col-lg-2">
                <div class="header__logo">
                    <a href="./index.php"><img src="public/assets/img/logo.png" alt="SleppyStore"></a>
                </div>
            </div>
            <div class="col-xl-6 col-lg-7">
                <nav class="header__menu">
                    <ul>
                        <li><a href="./index.php">Inicio</a></li>
                        <li><a href="#">Categorías</a>
                            <ul class="dropdown">
                                <?php if(isset($categorias) && !empty($categorias)): ?>
                                    <?php foreach($categorias as $categoria): ?>
                                    <li><a href="shop.php?categoria=<?php echo urlencode($categoria['id_categoria']); ?>"><?php echo htmlspecialchars($categoria['nombre_categoria']); ?></a></li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </li>
                        <li><a href="./shop.php">Tienda</a></li>
                        <li><a href="./contact.php">Contacto</a></li>
                        <?php if(isset($usuario_logueado) && $usuario_logueado && $usuario_logueado['rol_usuario'] === 'admin'): ?>
                        <li class="admin-menu-item"><a href="./admin.php" class="admin-link">
                             Admin
                            <span class="admin-badge"></span>
                        </a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
            <div class="col-lg-3">
                <div class="header__right">
                    <div class="header__right__auth">
                        <?php if(isset($usuario_logueado) && $usuario_logueado): ?>
                            <a href="#" id="user-account-link">
                                <i class="fa fa-user-circle"></i>
                                <span>Hola, <?php echo htmlspecialchars($usuario_logueado['nombre_usuario']); ?></span>
                            </a>
                        <?php else: ?>
                            <a href="login.php">Iniciar Sesión</a>
                            <a href="register.php">Registrarse</a>
                        <?php endif; ?>
                    </div>
                    <ul class="header__right__widget">
                        <li><span class="icon_search search-switch"></span></li>
                        <?php if(isset($usuario_logueado) && $usuario_logueado): ?>
                        <li><a href="#" id="favorites-link"><span class="icon_heart_alt"></span>
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
                    <!-- Botón hamburguesa dentro del header__right -->
                    <div class="canvas__open">
                        <i class="fa fa-bars"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
<!-- Header Section End -->
