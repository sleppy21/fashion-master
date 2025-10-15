<!-- Header Section Begin -->
<!-- VERSION 3.0 - ESTÁNDAR UNIFICADO - <?php echo date('Y-m-d H:i:s'); ?> -->

<script>
    // APLICAR DARK MODE INMEDIATAMENTE - ANTES DEL RENDER
    // Esto previene el flash blanco al cargar la página
    (function() {
        const savedTheme = localStorage.getItem('theme');
        const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        
        if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
            document.documentElement.classList.add('dark-mode');
            document.body.classList.add('dark-mode');
        }
    })();
</script>

<!-- Header HTML Comienza -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

     <!-- Header global - COMPACTO v5.0 -->

    <link rel="stylesheet" href="public/assets/css/style.css">
    <link rel="stylesheet" href="public/assets/css/modals-animations.css">
    <link rel="stylesheet" href="public/assets/css/header-responsive.css">
    <link rel="stylesheet" href="public/assets/css/header-bootstrap-layout.css">
    <link rel="stylesheet" href="public/assets/css/badges-override.css">
    <link rel="stylesheet" href="public/assets/css/global-search-modal.css?v=<?php echo time(); ?>">

</head>
<body>
    
</body>
</html>
 


<header class="header">
    <div class="container-fluid px-3">
        <div class="row align-items-center g-2 justify-content-between">
            <!-- Logo Section - Oculto en móvil, visible en desktop -->
            <div class="col-auto d-none d-lg-block" style="min-width: 80px;">
                <div class="header__logo">
                    <!-- Logo removido -->
                </div>
            </div>
            
            <!-- Menu Section - Centrado y responsive -->
            <div class="col-12 col-lg order-3 order-lg-2 d-flex justify-content-center">
                <nav class="header__menu">
                    <ul class="d-flex justify-content-center align-items-center flex-wrap m-0 p-0 list-unstyled">
                        <li><a href="./index.php">Inicio</a></li>
                        <li><a href="./shop.php">Tienda</a></li>
                        <li><a href="./contact.php">Contacto</a></li>
                        <?php if(isset($usuario_logueado) && $usuario_logueado): ?>
                        <li><a href="./cart.php">Carrito</a></li>
                        <li><a href="./mis-pedidos.php">Mis Compras</a></li>
                        <?php endif; ?>
                        <?php if(isset($usuario_logueado) && $usuario_logueado && $usuario_logueado['rol_usuario'] === 'admin'): ?>
                        <li class="admin-menu-item"><a href="./admin.php" class="admin-link">
                             Admin
                            <span class="admin-badge"></span>
                        </a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
            
            <!-- Right Section - Icons and Avatar -->
            <div class="col-auto order-2 order-lg-3" style="min-width: 200px;">
                <div class="header__right">
                    <ul class="header__right__widget d-flex align-items-center justify-content-end m-0 p-0 list-unstyled">
                        <li><span class="icon_search" id="global-search-trigger" style="cursor: pointer;"></span></li>
                        <li>
                            <a href="#" id="dark-mode-toggle" title="Cambiar tema">
                                <i class="fa fa-moon"></i>
                            </a>
                        </li>
                        <?php if(isset($usuario_logueado) && $usuario_logueado): ?>
                        <li><a href="profile.php#settings" title="Ajustes">
                            <i class="fa fa-cog"></i>
                        </a></li>
                        <li><a href="#" id="notifications-link" title="Notificaciones">
                            <i class="fa fa-bell"></i>
                            <?php if(isset($notifications_count) && $notifications_count > 0): ?>
                            <div class="tip" id="notifications-count"><?php echo $notifications_count; ?></div>
                            <?php else: ?>
                            <div class="tip" id="notifications-count" style="display: none;">0</div>
                            <?php endif; ?>
                        </a></li>
                        <li><a href="#" id="favorites-link" title="Favoritos"><span class="icon_heart_alt"></span>
                            <?php if(isset($favorites_count) && $favorites_count > 0): ?>
                            <div class="tip" id="favorites-count"><?php echo $favorites_count; ?></div>
                            <?php else: ?>
                            <div class="tip" id="favorites-count" style="display: none;">0</div>
                            <?php endif; ?>
                        </a></li>
                        <li><a href="cart.php" title="Carrito"><i class="fa fa-shopping-cart"></i>
                            <?php if(isset($cart_count) && $cart_count > 0): ?>
                            <div class="tip" id="cart-count"><?php echo $cart_count; ?></div>
                            <?php else: ?>
                            <div class="tip" id="cart-count" style="display: none;">0</div>
                            <?php endif; ?>
                        </a></li>
                        
                        <!-- Botón de Avatar de Usuario -->
                        <li class="user-avatar-btn-container">
                            <a href="#" id="user-account-link" class="header-avatar-link" title="Mi cuenta">
                                <?php 
                                // Construir ruta del avatar
                                $header_avatar_path = 'public/assets/img/profiles/default-avatar.png';
                                if (!empty($usuario_logueado['avatar_usuario'])) {
                                    if (strpos($usuario_logueado['avatar_usuario'], 'public/assets/img/profiles/') !== false) {
                                        $header_avatar_path = $usuario_logueado['avatar_usuario'];
                                    } elseif ($usuario_logueado['avatar_usuario'] !== 'default-avatar.png') {
                                        $header_avatar_path = 'public/assets/img/profiles/' . $usuario_logueado['avatar_usuario'];
                                    }
                                }
                                
                                $tiene_avatar_custom = !empty($usuario_logueado['avatar_usuario']) && 
                                                      $usuario_logueado['avatar_usuario'] !== 'default-avatar.png' &&
                                                      file_exists($header_avatar_path);
                                ?>
                                
                                <div class="header-user-avatar">
                                    <?php if($tiene_avatar_custom): ?>
                                        <img src="<?php echo $header_avatar_path; ?>" 
                                             alt="Avatar" 
                                             class="avatar-image"
                                             crossorigin="anonymous"
                                             style="width: 38px !important; height: 38px !important; border-radius: 50% !important; object-fit: cover !important; object-position: center !important; display: block !important;">
                                    <?php else: ?>
                                        <div class="avatar-initial">
                                            <?php echo strtoupper(substr($usuario_logueado['nombre_usuario'], 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="avatar-status-dot"></div>
                                </div>
                            </a>
                        </li>
                        <?php else: ?>
                        <li><a href="login.php" title="Favoritos"><span class="icon_heart_alt"></span></a></li>
                        <li><a href="cart.php" title="Carrito"><span class="icon_bag_alt"></span></a></li>
                        <li><a href="login.php" class="btn-login-header">Iniciar Sesión</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</header>
<!-- Header Section End -->
<script>
/* Cleanup rápido: remover inline styles de color/background que rompen el dark mode en los modales */
(function(){
    'use strict';

    function cleanFavoritesModalStyles(){
        if(!document.body.classList.contains('dark-mode')) return;
        const selectors = [
            '#favorites-modal',
            '.favorites-modal-content',
            '.favorites-modal-header',
            '.favorites-modal-body',
            '.favorite-item',
            '.favorite-product-name',
            '.price-current',
            '.price-old',
            '.favorite-date',
            '.btn-favorite-cart',
            '.btn-favorite-remove',
            '.stock-badge',
            '.favorites-empty',
            '.favorites-empty i',
            '.favorites-empty p',
            '.btn-shop-now',
            // Modal de notificaciones
            '#notifications-modal',
            '.notifications-modal',
            '.notifications-modal-content',
            '.notifications-modal-header',
            '.notifications-modal-body',
            '.notifications-modal-close',
            '.notification-item',
            '.notification-icon',
            '.notification-info',
            '.notification-actions',
            '.btn-notif-read',
            '.btn-notif-delete',
            '.notifications-loading',
            '.notifications-count',
            '.notifications-update',
            // Modal de usuario
            '#user-account-modal',
            '.user-modal',
            '.user-modal-overlay',
            '.user-modal-content',
            '.user-modal-close',
            '.user-modal-header',
            '.user-modal-body',
            '.user-modal-footer',
            '.user-avatar-circle',
            '.avatar-initial',
            '.avatar-status',
            '.user-name',
            '.user-role-badge',
            '.stat-card',
            '.stat-icon',
            '.stat-number',
            '.stat-label',
            '.user-info-item',
            '.info-icon',
            '.info-label',
            '.info-value',
            '.btn-action',
            '.btn-primary',
            '.btn-admin',
            '.btn-logout',
            '.user-stats-grid',
            '.user-info-list',
            '.user-avatar-container',
            '.stat-info',
            '.info-content',
            '.header-background'
        ];

        selectors.forEach(sel => {
            document.querySelectorAll(sel).forEach(el => {
                try {
                    if(!el || !el.style) return;
                    // Remover propiedades de color que puedan venir inline
                    el.style.removeProperty('background');
                    el.style.removeProperty('background-color');
                    el.style.removeProperty('color');
                    el.style.removeProperty('border-color');
                    el.style.removeProperty('border');
                } catch(e) {
                    // segurizar
                }
            });
        });
    }

    // Ejecutar al cargar (SIN DELAY)
    document.addEventListener('DOMContentLoaded', function(){
        cleanFavoritesModalStyles();
    });

    // EJECUTAR INMEDIATAMENTE cuando se abre el modal (SIN setTimeout)
    document.addEventListener('click', function(e){
        if(e.target.closest('#favorites-link') || e.target.closest('#favorites-link-mobile') || 
           e.target.closest('#user-account-link') || e.target.closest('#user-profile-link') || 
           e.target.closest('#notifications-link') || e.target.closest('#notifications-link-mobile')){
            cleanFavoritesModalStyles();
        }
    });
    
    // Observar cambios dinámicos (SIN setTimeout)
    (function initObserver(){
        const attach = () => {
            const favList = document.getElementById('favorites-list');
            const notifList = document.getElementById('notifications-list');
            
            if(favList) {
                const obs = new MutationObserver(function(){
                    cleanFavoritesModalStyles();
                });
                obs.observe(favList, { childList: true, subtree: true });
            }
            
            if(notifList) {
                const obs = new MutationObserver(function(){
                    cleanFavoritesModalStyles();
                });
                obs.observe(notifList, { childList: true, subtree: true });
            }
        };

        attach();
        const bodyObs = new MutationObserver(function(muts, obs){
            attach();
        });
        bodyObs.observe(document.body, { childList: true, subtree: true });
    })();

    // Exponer función para debugging
    window.cleanFavoritesModalStyles = cleanFavoritesModalStyles;
})();

// ====================================================================
// FUNCIONES UNIFICADAS PARA ABRIR/CERRAR MODALES - CON ANIMACIONES
// ====================================================================
(function() {
    'use strict';
    
    // ============================================
    // FUNCIÓN PARA ABRIR MODAL - CON ANIMACIÓN SLIDE
    // ============================================
    // Track the last trigger element that opened a modal so we can toggle a class on it
    let lastModalTrigger = null;

    function openModal(modalId, triggerElement) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        // Remove closing class if present
        modal.classList.remove('modal-closing');

        // Show modal
        modal.style.display = 'block';

        // Force reflow for animation
        void modal.offsetWidth;

        // Add open class
        modal.classList.add('modal-open');

        // If a trigger element is provided, mark it as active so CSS can use it as parent
            if (triggerElement && triggerElement.classList) {
            try {
                triggerElement.classList.add('modal-trigger-active');
                lastModalTrigger = triggerElement;
                // Notify other modules that a modal trigger changed
                try { document.dispatchEvent(new CustomEvent('modalTriggerChanged')); } catch(e) {}
            } catch (e) {
                // ignore
            }
        }
    }
    
    // ============================================
    // FUNCIÓN PARA CERRAR MODAL - CON ANIMACIÓN SLIDE
    // ============================================
    function closeModal(modalId) {
        const modal = document.getElementById(modalId);

        if (!modal || !modal.classList.contains('modal-open')) {
            return;
        }

        modal.classList.add('modal-closing');
        modal.classList.remove('modal-open');

        // Remove active class from last trigger if present
        if (lastModalTrigger && lastModalTrigger.classList) {
            try {
                lastModalTrigger.classList.remove('modal-trigger-active');
                console.log('✅ Modal trigger desactivado:', modalId);
                try { 
                    document.dispatchEvent(new CustomEvent('modalTriggerChanged', { 
                        detail: { action: 'closed', modalId: modalId } 
                    })); 
                } catch(e) {}
            } catch (e) {
                console.error('❌ Error al desactivar trigger:', e);
            }
            lastModalTrigger = null;
        }

        setTimeout(() => {
            if (modal.classList.contains('modal-closing')) {
                modal.style.display = 'none';
                modal.classList.remove('modal-closing');
            }
        }, 250);
    }
    
    // ============================================
    // CERRAR TODOS LOS MODALES
    // ============================================
    function closeAllModalsInstant() {
        ['favorites-modal', 'notifications-modal', 'user-account-modal'].forEach(id => {
            const modal = document.getElementById(id);
            if (modal) {
                modal.style.display = 'none';
                modal.classList.remove('modal-open', 'modal-closing');
            }
        });
    }
    
    // Cerrar todos los modales con animación
    function closeAllModals() {
        ['favorites-modal', 'notifications-modal', 'user-account-modal'].forEach(id => {
            closeModal(id);
        });
    }
    
    // Función para cargar favoritos
    function loadFavorites() {
        console.log('📦 Cargando favoritos...');
        // Los favoritos ya están cargados en el PHP del modal
    }
    
    // FAVORITOS - ULTRA OPTIMIZADO
    function toggleFavoritesModal() {
        const modal = document.getElementById('favorites-modal');
        if (!modal) return;
        
        const isVisible = modal.classList.contains('modal-open');
        
        if (isVisible) {
            closeModal('favorites-modal');
        } else {
            // Cerrar otros modales usando closeModal para restaurar scroll correctamente
            const notifModal = document.getElementById('notifications-modal');
            const userModal = document.getElementById('user-account-modal');
            
            if (notifModal && notifModal.classList.contains('modal-open')) {
                closeModal('notifications-modal');
            }
            if (userModal && userModal.classList.contains('modal-open')) {
                closeModal('user-account-modal');
            }
            
            // Abrir favoritos INSTANTÁNEAMENTE
            openModal('favorites-modal');
        }
    }
    
    // NOTIFICACIONES - ULTRA OPTIMIZADO
    function toggleNotificationsModal() {
        const modal = document.getElementById('notifications-modal');
        if (!modal) return;
        
        const isVisible = modal.classList.contains('modal-open');
        
        if (isVisible) {
            closeModal('notifications-modal');
        } else {
            // Cerrar otros modales usando closeModal para restaurar scroll correctamente
            const favModal = document.getElementById('favorites-modal');
            const userModal = document.getElementById('user-account-modal');
            
            if (favModal && favModal.classList.contains('modal-open')) {
                closeModal('favorites-modal');
            }
            if (userModal && userModal.classList.contains('modal-open')) {
                closeModal('user-account-modal');
            }
            
            // Abrir notificaciones INSTANTÁNEAMENTE
            openModal('notifications-modal');
        }
    }
    
    // USUARIO - ULTRA OPTIMIZADO
    function toggleUserModal() {
        const modal = document.getElementById('user-account-modal');
        if (!modal) {
            console.error('❌ Modal de usuario no encontrado');
            return;
        }
        
        const isVisible = modal.classList.contains('modal-open');
        
        if (isVisible) {
            closeModal('user-account-modal');
        } else {
            // Cerrar otros modales usando closeModal para restaurar scroll correctamente
            const favModal = document.getElementById('favorites-modal');
            const notifModal = document.getElementById('notifications-modal');
            
            if (favModal && favModal.classList.contains('modal-open')) {
                closeModal('favorites-modal');
            }
            if (notifModal && notifModal.classList.contains('modal-open')) {
                closeModal('notifications-modal');
            }
            
            // Abrir usuario INSTANTÁNEAMENTE
            openModal('user-account-modal');
            console.log('✅ Modal de usuario abierto');
        }
    }
    
    // Inicializar modales
    function initModals() {
        // Verificar que los modales existan
        const favModal = document.getElementById('favorites-modal');
        const notifModal = document.getElementById('notifications-modal');
        const userModal = document.getElementById('user-account-modal');
        
        if (!favModal || !notifModal || !userModal) {
            setTimeout(initModals, 100);
            return;
        }
        
        // FAVORITOS
        document.querySelectorAll('#favorites-link, #favorites-link-mobile').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                // Pass the trigger element so we can mark it active
                const trigger = e.currentTarget || this;
                const modal = document.getElementById('favorites-modal');
                const isVisible = modal && modal.classList.contains('modal-open');
                if (isVisible) {
                    closeModal('favorites-modal');
                } else {
                    // Close others and open with trigger
                    const notifModal = document.getElementById('notifications-modal');
                    const userModal = document.getElementById('user-account-modal');
                    if (notifModal && notifModal.classList.contains('modal-open')) {
                        closeModal('notifications-modal');
                    }
                    if (userModal && userModal.classList.contains('modal-open')) {
                        closeModal('user-account-modal');
                    }
                    openModal('favorites-modal', trigger);
                }
            });
        });
        
        document.querySelectorAll('.favorites-modal-close').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                closeModal('favorites-modal');
            });
        });
        
        // NOTIFICACIONES
        document.querySelectorAll('#notifications-link, #notifications-link-mobile').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const trigger = e.currentTarget || this;
                const modal = document.getElementById('notifications-modal');
                const isVisible = modal && modal.classList.contains('modal-open');
                if (isVisible) {
                    closeModal('notifications-modal');
                } else {
                    const favModal = document.getElementById('favorites-modal');
                    const userModal = document.getElementById('user-account-modal');
                    if (favModal && favModal.classList.contains('modal-open')) {
                        closeModal('favorites-modal');
                    }
                    if (userModal && userModal.classList.contains('modal-open')) {
                        closeModal('user-account-modal');
                    }
                    openModal('notifications-modal', trigger);
                }
            });
        });
        
        document.querySelectorAll('.notifications-modal-close').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                closeModal('notifications-modal');
            });
        });
        
        // USUARIO
        document.querySelectorAll('#user-account-link, #user-account-link-mobile, #user-profile-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const trigger = e.currentTarget || this;
                const modal = document.getElementById('user-account-modal');
                const isVisible = modal && modal.classList.contains('modal-open');
                if (isVisible) {
                    closeModal('user-account-modal');
                } else {
                    const favModal = document.getElementById('favorites-modal');
                    const notifModal = document.getElementById('notifications-modal');
                    if (favModal && favModal.classList.contains('modal-open')) {
                        closeModal('favorites-modal');
                    }
                    if (notifModal && notifModal.classList.contains('modal-open')) {
                        closeModal('notifications-modal');
                    }
                    openModal('user-account-modal', trigger);
                    console.log('✅ Modal de usuario abierto');
                }
            });
        });
        
        document.querySelectorAll('.user-modal-close').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                closeModal('user-account-modal');
            });
        });
        
        // Cerrar al hacer clic fuera
        document.addEventListener('click', function(e) {
            const modals = [
                { id: 'favorites-modal', selector: '.favorites-modal-content', link: '#favorites-link' },
                { id: 'notifications-modal', selector: '.notifications-modal-content', link: '#notifications-link' },
                { id: 'user-account-modal', selector: '.user-modal-content', link: '#user-account-link', altLink: '#user-profile-link' }
            ];
            
            modals.forEach(({ id, selector, link, altLink }) => {
                const modal = document.getElementById(id);
                if (modal && modal.classList.contains('modal-open')) {
                    const content = modal.querySelector(selector);
                    const linkElement = document.querySelector(link);
                    const linkMobile = document.querySelector(link + '-mobile');
                    const altLinkElement = altLink ? document.querySelector(altLink) : null;
                    
                    const isClickInside = content && content.contains(e.target);
                    const isClickOnLink = (linkElement && linkElement.contains(e.target)) || 
                                          (linkMobile && linkMobile.contains(e.target)) ||
                                          (altLinkElement && altLinkElement.contains(e.target));                    if (!isClickInside && !isClickOnLink) {
                        closeModal(id);
                    }
                }
            });
        });
        
        // Cerrar con tecla ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAllModals();
            }
        });
        
        console.log('✅ Eventos configurados correctamente');
    }
    
    // Inicializar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initModals);
    } else {
        initModals();
    }
    
    // Exponer funciones globalmente (SIN CALLBACKS - para evitar delays)
    window.openFavoritesModal = () => openModal('favorites-modal');
    window.closeFavoritesModal = () => closeModal('favorites-modal');
    window.toggleFavoritesModal = toggleFavoritesModal;
    window.openNotificationsModal = () => openModal('notifications-modal');
    window.closeNotificationsModal = () => closeModal('notifications-modal');
    window.toggleNotificationsModal = toggleNotificationsModal;
    window.openUserModal = () => openModal('user-account-modal');
    window.closeUserModal = () => closeModal('user-account-modal');
    window.toggleUserModal = toggleUserModal;
    window.closeAllModals = closeAllModals;
    window.closeAllModalsInstant = closeAllModalsInstant;
    
    console.log('🌐 Funciones de modales expuestas globalmente');
})();
</script>

<!-- ========================================
     MODALES GLOBALES (disponibles en todas las páginas)
     ======================================== -->
<?php 
// Incluir modales (cada uno tiene su propio condicional de usuario logueado)
include __DIR__ . '/favorites-modal.php';
include __DIR__ . '/notifications-modal.php';
include __DIR__ . '/user-account-modal.php';
include __DIR__ . '/global-search-modal.php';

// Script para actualización AJAX de contadores (solo si hay usuario)
if(isset($usuario_logueado) && $usuario_logueado): 
    // Asegurar que el BASE_URL use el mismo protocolo que la página actual
    $secure_base_url = rtrim(BASE_URL, '/');
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        $secure_base_url = str_replace('http://', 'https://', $secure_base_url);
    }
    echo '<script src="' . $secure_base_url . '/public/assets/js/ajax-counters.js?v=' . time() . '"></script>';
    
    // Script para colores dinámicos de avatar (shadow extraction)
    echo '<script src="' . $secure_base_url . '/public/assets/js/image-color-extractor.js?v=' . time() . '"></script>';
    
    // Script para gestionar shadow del avatar del header
    echo '<script src="' . $secure_base_url . '/public/assets/js/header-avatar-shadow.js?v=' . time() . '"></script>';
endif;
?>

<!-- Script para header responsive (siempre cargado) -->
<script src="public/assets/js/header-responsive.js?v=<?php echo time(); ?>"></script>

<!-- Script para búsqueda global (siempre cargado) -->
<script src="public/assets/js/global-search.js?v=<?php echo time(); ?>"></script>
