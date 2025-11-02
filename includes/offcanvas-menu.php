<?php
/**
 * OFFCANVAS MENU GLOBAL - MENÚ MÓVIL RESPONSIVE
 * Este archivo contiene el menú lateral que se muestra en móviles
 * Se incluye en todas las páginas que necesiten el menú móvil
 */
?>
<!-- Offcanvas Menu Begin -->
<div class="offcanvas-menu-overlay" onclick="
    if (window.OffcanvasManager) {
        window.OffcanvasManager.close('menu');
    } else {
        const wrapper = document.querySelector('.offcanvas-menu-wrapper');
        const overlay = document.querySelector('.offcanvas-menu-overlay');
        if (wrapper) wrapper.classList.remove('active');
        if (overlay) overlay.classList.remove('active');
        document.body.classList.remove('offcanvas-active');
        document.body.style.overflow = '';
    }
"></div>
<div class="offcanvas-menu-wrapper">
    <!-- Indicador de swipe (girado 90 grados para swipe izquierda) -->
    <div class="offcanvas-swipe-handle">
        <div style="width: 4px; height: 40px; background: rgba(0,0,0,0.2); border-radius: 2px; margin: 0 auto;"></div>
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
            
            <!-- Asistente Virtual - Abrir modal del chatbot -->
            <li><a href="#" id="open-chatbot-mobile"><i class="fa fa-comments"></i> Asistente Virtual</a></li>
            
            <?php if(isset($usuario_logueado) && $usuario_logueado): ?>
            <li><a href="logout.php"><i class="fa fa-sign-out"></i> Cerrar Sesión</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    
    <!-- Botones de autenticación (solo si no está logueado) -->
    <?php if(!isset($usuario_logueado) || !$usuario_logueado): ?>
    <div class="offcanvas__auth">
        <a href="login.php">Iniciar Sesión</a>
        <a href="register.php">Registrarse</a>
    </div>
    <?php endif; ?>
</div>
<!-- Offcanvas Menu End -->

<script>
// Abrir chatbot desde menú móvil - VERSION MEJORADA
document.addEventListener('DOMContentLoaded', function() {
    const chatbotMobileBtn = document.getElementById('open-chatbot-mobile');
    
    if (!chatbotMobileBtn) {
        console.warn('[Chatbot Mobile] Botón no encontrado');
        return;
    }
    
    chatbotMobileBtn.addEventListener('click', function(e) {
        e.preventDefault();
        
        console.log('[Chatbot Mobile] Click detectado');
        
        // Cerrar menú offcanvas
        const offcanvasWrapper = document.querySelector('.offcanvas-menu-wrapper');
        const offcanvasOverlay = document.querySelector('.offcanvas-menu-overlay');
        
        if (offcanvasWrapper) offcanvasWrapper.classList.remove('active');
        if (offcanvasOverlay) offcanvasOverlay.classList.remove('active');
        document.body.classList.remove('offcanvas-active');
        
        // Función para abrir el chatbot
        function tryOpenChatbot() {
            // Método 1: Usar openChat() si existe
            if (window.fashionStoreChat && typeof window.fashionStoreChat.openChat === 'function') {
                console.log('[Chatbot Mobile] ✅ Abriendo con openChat()');
                window.fashionStoreChat.openChat();
                return true;
            }
            
            // Método 2: Usar toggleChat() si existe
            if (window.fashionStoreChat && typeof window.fashionStoreChat.toggleChat === 'function') {
                console.log('[Chatbot Mobile] ✅ Abriendo con toggleChat()');
                window.fashionStoreChat.toggleChat();
                return true;
            }
            
            // Método 3: Click en botón flotante
            const chatButton = document.getElementById('fsChatButton');
            if (chatButton) {
                console.log('[Chatbot Mobile] ✅ Click en botón flotante');
                chatButton.click();
                return true;
            }
            
            return false;
        }
        
        // Esperar animación de cierre del menú
        setTimeout(function() {
            // Intentar abrir inmediatamente
            if (tryOpenChatbot()) {
                return;
            }
            
            // Si no funcionó, esperar a que se inicialice
            console.log('[Chatbot Mobile] Widget no listo, esperando...');
            let attempts = 0;
            const maxAttempts = 30; // 3 segundos
            
            const checkInterval = setInterval(function() {
                attempts++;
                
                if (tryOpenChatbot()) {
                    clearInterval(checkInterval);
                    console.log('[Chatbot Mobile] ✅ Widget listo después de', attempts * 100, 'ms');
                } else if (attempts >= maxAttempts) {
                    clearInterval(checkInterval);
                    console.error('[Chatbot Mobile] ❌ Timeout');
                    alert('El asistente virtual no está disponible. Por favor, recarga la página.');
                }
            }, 100);
        }, 300);
    });
});
</script>
