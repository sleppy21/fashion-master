/**
 * SWIPE GESTURES - Gestos táctiles para cerrar modales y menús
 * - Offcanvas: Swipe izquierda para cerrar (con feedback visual)
 * - Modal de usuario: Swipe abajo para cerrar (con feedback visual)
 */

(function() {
    'use strict';

    // ============================================
    // OFFCANVAS MENU: Swipe izquierda para cerrar
    // ============================================
    function initOffcanvasSwipe() {
        const offcanvasWrapper = document.querySelector('.offcanvas-menu-wrapper');
        
        if (!offcanvasWrapper) return;

        let touchStartX = 0;
        let touchCurrentX = 0;
        let isSwiping = false;
        const swipeThreshold = 100; // Debe deslizar al menos 100px
        const velocityThreshold = 0.3; // Velocidad mínima

        offcanvasWrapper.addEventListener('touchstart', function(e) {
            touchStartX = e.touches[0].clientX;
            touchCurrentX = touchStartX;
            isSwiping = true;
            offcanvasWrapper.style.transition = 'none'; // Quitar transición durante el swipe
        }, { passive: true });

        offcanvasWrapper.addEventListener('touchmove', function(e) {
            if (!isSwiping) return;
            
            touchCurrentX = e.touches[0].clientX;
            const deltaX = touchCurrentX - touchStartX;
            
            // Solo permitir swipe hacia la izquierda (deltaX negativo)
            if (deltaX < 0) {
                // Aplicar transformación para seguir el dedo
                const translateX = Math.max(deltaX, -offcanvasWrapper.offsetWidth);
                offcanvasWrapper.style.transform = `translateX(${translateX}px)`;
            }
        }, { passive: true });

        offcanvasWrapper.addEventListener('touchend', function(e) {
            if (!isSwiping) return;
            
            const deltaX = touchCurrentX - touchStartX;
            const swipeDistance = Math.abs(deltaX);
            
            // Restaurar transición
            offcanvasWrapper.style.transition = '';
            offcanvasWrapper.style.transform = '';
            
            // Si deslizó hacia la izquierda más del threshold, cerrar
            if (deltaX < 0 && swipeDistance > swipeThreshold) {
                const overlay = document.querySelector('.offcanvas-menu-overlay');
                
                offcanvasWrapper.classList.remove('active');
                if (overlay) overlay.classList.remove('active');
                document.body.classList.remove('offcanvas-active');
                
            }
            
            isSwiping = false;
        }, { passive: true });
    }

    // ============================================
    // FILTERS OFFCANVAS: Swipe izquierda para cerrar
    // ============================================
    function initFiltersSwipe() {
        const filtersWrapper = document.querySelector('.filters-menu-wrapper');
        
        if (!filtersWrapper) return;

        let touchStartX = 0;
        let touchCurrentX = 0;
        let isSwiping = false;
        const swipeThreshold = 100; // Debe deslizar al menos 100px
        const velocityThreshold = 0.3; // Velocidad mínima

        filtersWrapper.addEventListener('touchstart', function(e) {
            touchStartX = e.touches[0].clientX;
            touchCurrentX = touchStartX;
            isSwiping = true;
            filtersWrapper.style.transition = 'none'; // Quitar transición durante el swipe
        }, { passive: true });

        filtersWrapper.addEventListener('touchmove', function(e) {
            if (!isSwiping) return;
            
            touchCurrentX = e.touches[0].clientX;
            const deltaX = touchCurrentX - touchStartX;
            
            // Solo permitir swipe hacia la izquierda (deltaX negativo)
            if (deltaX < 0) {
                // Aplicar transformación para seguir el dedo
                const translateX = Math.max(deltaX, -filtersWrapper.offsetWidth);
                filtersWrapper.style.transform = `translateX(${translateX}px)`;
            }
        }, { passive: true });

        filtersWrapper.addEventListener('touchend', function(e) {
            if (!isSwiping) return;
            
            const deltaX = touchCurrentX - touchStartX;
            const swipeDistance = Math.abs(deltaX);
            
            // Restaurar transición
            filtersWrapper.style.transition = '';
            filtersWrapper.style.transform = '';
            
            // Si deslizó hacia la izquierda más del threshold, cerrar
            if (deltaX < 0 && swipeDistance > swipeThreshold) {
                const overlay = document.querySelector('.filters-menu-overlay');
                
                filtersWrapper.classList.remove('active');
                if (overlay) overlay.classList.remove('active');
                document.body.classList.remove('filters-active');
                
            }
            
            isSwiping = false;
        }, { passive: true });
    }

    // ============================================
    // MODAL DE USUARIO: Swipe abajo para cerrar
    // ============================================
    function initUserModalSwipe() {
        const userModal = document.getElementById('user-account-modal');
        const userModalContent = document.querySelector('.user-account-modal-content');
        
        if (!userModalContent) return;

        let startY = 0;
        let currentY = 0;
        let isDragging = false;
        let hasMoved = false;

        userModalContent.addEventListener('touchstart', function(e) {
            // Solo en el área del handle o fondo del modal, no en botones/links
            const target = e.target;
            if (target.tagName === 'A' || target.tagName === 'BUTTON' || target.closest('a') || target.closest('button')) {
                return; // No interferir con botones y enlaces
            }
            
            startY = e.touches[0].clientY;
            currentY = startY;
            isDragging = true;
            hasMoved = false;
            userModalContent.classList.add('dragging');
        }, { passive: true });

        userModalContent.addEventListener('touchmove', function(e) {
            if (!isDragging) return;
            
            currentY = e.touches[0].clientY;
            const distance = currentY - startY;
            
            // Marcar que hubo movimiento
            if (Math.abs(distance) > 5) {
                hasMoved = true;
            }
            
            // Permitir deslizar hacia abajo (distance > 0) o hacia arriba con límite (distance < 0)
            if (hasMoved) {
                if (distance > 0) {
                    // Deslizar hacia abajo - sin límite
                    userModalContent.style.transform = 'translateY(' + distance + 'px)';
                } else {
                    // Deslizar hacia arriba - límite de 50px
                    const limitedDistance = Math.max(distance, -50);
                    userModalContent.style.transform = 'translateY(' + limitedDistance + 'px)';
                }
            }
        }, { passive: true });

        userModalContent.addEventListener('touchend', function(e) {
            if (!isDragging) return;
            
            const distance = currentY - startY;
            isDragging = false;
            hasMoved = false;
            userModalContent.classList.remove('dragging');
            
            // Cerrar si deslizó hacia abajo >80px o hacia arriba >30px
            if ((distance > 80 && Math.abs(distance) > 10) || (distance < -30 && Math.abs(distance) > 10)) {
                // Bloquear animaciones CSS completamente
                userModal.classList.add('swipe-closing');
                
                // Animación de cierre suave con JS
                userModalContent.style.transition = 'transform 0.3s ease-out';
                userModalContent.style.transform = 'translateY(100%)';
                
                setTimeout(function() {
                    // Cerrar completamente
                    userModal.classList.remove('modal-open');
                    userModal.classList.remove('swipe-closing');
                    userModal.style.display = 'none';
                    
                    // Limpiar estilos inline
                    userModalContent.style.transition = '';
                    userModalContent.style.transform = '';
                    
                    // Desbloquear scroll
                    if (window.innerWidth <= 767) {
                        document.body.classList.remove('modal-scroll-lock');
                    }
                }, 300);
            } else if (Math.abs(distance) > 0) {
                // Volver a posición original solo si hubo movimiento
                userModalContent.style.transition = 'transform 0.3s ease-out';
                userModalContent.style.transform = 'translateY(0)';
                
                setTimeout(function() {
                    userModalContent.style.transition = '';
                }, 300);
            } else {
                // Resetear sin animación si no hubo movimiento (tap normal)
                userModalContent.style.transform = '';
            }
            
            startY = 0;
            currentY = 0;
        }, { passive: true });
    }

    // ============================================
    // INICIALIZAR AL CARGAR
    // ============================================
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initOffcanvasSwipe();
            initFiltersSwipe();
            initUserModalSwipe();
        });
    } else {
        initOffcanvasSwipe();
        initFiltersSwipe();
        initUserModalSwipe();
    }

})();
