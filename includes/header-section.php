<!-- Header Section Begin -->
<style>
    /* Estilos globales para dropdowns - aplican en todas las resoluciones */
    .header__menu .dropdown {
        display: block !important;
        flex-direction: column !important;
        background: #ffffff !important;
        border-radius: 10px !important;
        box-shadow: 0 6px 25px rgba(0, 0, 0, 0.12) !important;
        padding: 15px 0 !important;
        min-width: 200px !important;
        border: 1px solid #e8e8e8 !important;
        margin-top: 10px !important;
    }
    
    .header__menu .dropdown li {
        display: block !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    
    .header__menu .dropdown li a {
        display: block !important;
        width: 100% !important;
        padding: 12px 25px !important;
        font-size: 14px !important;
        color: #444 !important;
        transition: all 0.25s ease !important;
        border-left: 3px solid transparent !important;
        font-weight: 400 !important;
        letter-spacing: 0.3px !important;
    }
    
    .header__menu .dropdown li a:hover {
        background: linear-gradient(90deg, #fff5f5 0%, #ffffff 100%) !important;
        color: #ca1515 !important;
        border-left-color: #ca1515 !important;
        padding-left: 30px !important;
        font-weight: 500 !important;
    }
    
    .header__menu .dropdown li:first-child a {
        border-top-left-radius: 8px !important;
        border-top-right-radius: 8px !important;
    }
    
    .header__menu .dropdown li:last-child a {
        border-bottom-left-radius: 8px !important;
        border-bottom-right-radius: 8px !important;
    }
    
    /* Estilos de tips (notificaciones) - negro con blanco */
    .tip {
        background: #000000 !important;
        color: #ffffff !important;
        border: none !important;
    }

    /* Optimización del header en desktop */
    @media (min-width: 992px) {
        .header .container-fluid {
            padding-left: 15px !important;
            padding-right: 15px !important;
        }
        
        .header .row {
            align-items: center !important;
            margin: 0 !important;
        }
        
        .header .col-lg-3:last-child {
            flex: 0 0 auto !important;
            max-width: none !important;
            width: auto !important;
        }
        
        .header__logo {
            padding: 8px 0 !important;
        }
        
        .header__logo img {
            max-height: 40px !important;
        }
        
        .header__menu {
            padding: 8px 0 !important;
        }
        
        .header__menu ul {
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
        }
        
        .header__menu ul > li {
            margin-right: 25px !important;
        }
        
        .header__menu ul > li > a {
            font-size: 13px !important;
            padding: 8px 0 !important;
        }
        
        /* Optimizar la sección derecha del header */
        .header__right {
            display: flex !important;
            align-items: center !important;
            justify-content: flex-end !important;
            gap: 20px !important;
            padding: 5px 20px 5px 0 !important;
            flex-wrap: nowrap !important;
            width: auto !important;
            min-width: fit-content !important;
        }
        
        .header__right__auth {
            flex-shrink: 0 !important;
            margin: 0 !important;
            padding: 0 15px 0 0 !important;
            border-right: 1px solid #e0e0e0 !important;
            width: auto !important;
            min-width: fit-content !important;
        }
        
        .header__right__auth a {
            font-size: 11px !important;
            white-space: nowrap !important;
            padding: 5px 10px !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
            color: #333 !important;
            width: auto !important;
        }
        
        .header__right__auth a i {
            font-size: 13px !important;
            flex-shrink: 0 !important;
        }
        
        .header__right__auth a span {
            white-space: nowrap !important;
            display: inline-block !important;
            vertical-align: middle !important;
            width: auto !important;
            min-width: fit-content !important;
        }
        
        .header__right__widget {
            display: flex !important;
            align-items: center !important;
            gap: 15px !important;
            margin: 0 !important;
            padding: 0 !important;
            list-style: none !important;
        }
        
        .header__right__widget li {
            margin: 0 !important;
            padding: 0 !important;
        }
        
        .header__right__widget li a,
        .header__right__widget li span {
            font-size: 16px !important;
            padding: 5px 8px !important;
            display: inline-flex !important;
            align-items: center !important;
            position: relative !important;
        }
        
        .header__right__widget .tip {
            font-size: 9px !important;
            width: 16px !important;
            height: 16px !important;
            line-height: 16px !important;
            top: 2px !important;
            right: 2px !important;
            background: #000000 !important;
            color: #ffffff !important;
            border: none !important;
        }
        
        .canvas__open {
            display: none !important;
        }
    }
</style>
<header class="header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-xl-3 col-lg-2">
                <div class="header__logo">
                    <!-- Logo removido -->
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
                        <li><a href="#">Marcas</a>
                            <ul class="dropdown">
                                <?php if(isset($marcas) && !empty($marcas)): ?>
                                    <?php foreach($marcas as $marca): ?>
                                    <li><a href="shop.php?marca=<?php echo urlencode($marca['id_marca']); ?>"><?php echo htmlspecialchars($marca['nombre_marca']); ?></a></li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li><a href="shop.php">Ver todas</a></li>
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
