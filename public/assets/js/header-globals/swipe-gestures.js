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
                if (window.OffcanvasManager && typeof window.OffcanvasManager.forceClose === 'function') {
                    window.OffcanvasManager.forceClose('filters');
                } else {
                    // Fallback por si no existe el manager
                    filtersWrapper.classList.remove('active');
                    const overlay = document.querySelector('.filters-menu-overlay');
                    if (overlay) overlay.classList.remove('active');
                    document.body.classList.remove('filters-active');
                    document.body.style.overflow = '';
                }
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
    // BOTTOM SHEETS DE CHECKOUT: Swipe para cerrar
    // ============================================
    function initCheckoutBottomSheets() {
        // Funciones para abrir los bottom sheets
        window.openAddressBottomSheet = function() {
            const sheet = document.getElementById('addressBottomSheet');
            if (!sheet) return;
            
            sheet.classList.add('active');
            document.body.classList.add('bottom-sheet-open');
        };

        window.openProductsBottomSheet = function() {
            const sheet = document.getElementById('productsBottomSheet');
            if (!sheet) return;
            
            sheet.classList.add('active');
            document.body.classList.add('bottom-sheet-open');
        };

        window.closeAddressBottomSheet = function() {
            const sheet = document.getElementById('addressBottomSheet');
            if (!sheet) return;
            
            sheet.classList.remove('active');
            document.body.classList.remove('bottom-sheet-open');
        };

        window.closeProductsBottomSheet = function() {
            const sheet = document.getElementById('productsBottomSheet');
            if (!sheet) return;
            
            sheet.classList.remove('active');
            document.body.classList.remove('bottom-sheet-open');
        };

        // Inicializar swipe para ambos bottom sheets
        const bottomSheets = document.querySelectorAll('.address-bottom-sheet');
        bottomSheets.forEach(sheet => {
            if (!sheet) return;

            const content = sheet.querySelector('.bottom-sheet-content');
            if (!content) return;

            let startY = 0;
            let currentY = 0;
            let isDragging = false;
            let hasMoved = false;

            // Iniciar el drag solo en el handle o área superior
            content.addEventListener('touchstart', function(e) {
                const handle = e.target.closest('.bottom-sheet-handle');
                const header = e.target.closest('.bottom-sheet-header');
                
                // Solo permitir swipe en el handle o header
                if (!handle && !header) return;
                
                startY = e.touches[0].clientY;
                currentY = startY;
                isDragging = true;
                hasMoved = false;
                content.classList.add('dragging');
                content.style.transition = 'none';
            }, { passive: true });

            content.addEventListener('touchmove', function(e) {
                if (!isDragging) return;

                currentY = e.touches[0].clientY;
                const distance = currentY - startY;

                // Solo permitir deslizar hacia abajo
                if (distance > 0) {
                    content.style.transform = `translateY(${distance}px)`;
                    if (Math.abs(distance) > 5) {
                        hasMoved = true;
                    }
                }
            }, { passive: true });

            content.addEventListener('touchend', function(e) {
                if (!isDragging) return;

                const distance = currentY - startY;
                isDragging = false;
                content.classList.remove('dragging');
                content.style.transition = '';

                // Si deslizó hacia abajo más del threshold o con velocidad suficiente
                if (distance > 100 || (hasMoved && distance > 50)) {
                    content.style.transform = 'translateY(100%)';
                    
                    setTimeout(() => {
                        sheet.classList.remove('active');
                        document.body.classList.remove('bottom-sheet-open');
                        content.style.transform = '';
                    }, 300);
                } else {
                    // Volver a la posición original
                    content.style.transform = '';
                }

                hasMoved = false;
                startY = 0;
                currentY = 0;
            }, { passive: true });

            // Cerrar al hacer clic fuera del contenido
            sheet.addEventListener('click', function(e) {
                if (e.target === sheet) {
                    sheet.classList.remove('active');
                    document.body.classList.remove('bottom-sheet-open');
                }
            });
        });
    }

    // ============================================
    // REVIEW BOTTOM SHEET: Swipe abajo para cerrar
    // ============================================
    function initReviewBottomSheet() {
        const bottomSheet = document.getElementById('reviewBottomSheet');
        if (!bottomSheet) return;

        const content = bottomSheet.querySelector('.bottom-sheet-content');
        if (!content) return;

        let startY = 0;
        let currentY = 0;
        let isDragging = false;
        let hasMoved = false;
        let isScrolling = false;

        // Detectar si el usuario está scrolleando el contenido
        const body = content.querySelector('.bottom-sheet-body');
        if (body) {
            body.addEventListener('touchstart', function(e) {
                if (body.scrollHeight > body.clientHeight && body.scrollTop > 0) {
                    isScrolling = true;
                }
            }, { passive: true });
        }

        content.addEventListener('touchstart', function(e) {
            // No permitir drag si está scrolleando contenido
            if (isScrolling) return;
            
            // Solo permitir drag desde el header
            const header = e.target.closest('.bottom-sheet-header');
            if (!header) return;
            
            startY = e.touches[0].clientY;
            currentY = startY;
            isDragging = true;
            hasMoved = false;
            content.classList.add('dragging');
            content.style.transition = 'none';
        }, { passive: true });

        content.addEventListener('touchmove', function(e) {
            if (!isDragging) return;

            currentY = e.touches[0].clientY;
            const distance = currentY - startY;

            // Solo permitir deslizar hacia abajo
            if (distance > 0) {
                // Aplicar resistencia al deslizamiento
                const resistance = 0.7; // Hacer que se sienta más pesado
                const adjustedDistance = distance * resistance;
                
                content.style.transform = `translateY(${adjustedDistance}px)`;
                
                if (Math.abs(distance) > 5) {
                    hasMoved = true;
                }
            }
        }, { passive: true });

        content.addEventListener('touchend', function(e) {
            if (!isDragging) return;

            const distance = currentY - startY;
            isDragging = false;
            isScrolling = false;
            content.classList.remove('dragging');
            content.style.transition = '';

            // Si deslizó hacia abajo más del threshold, cerrar
            if (distance > 120 || (hasMoved && distance > 80)) {
                // Animar hacia abajo
                content.style.transition = 'transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
                content.style.transform = 'translateY(100%)';
                
                setTimeout(() => {
                    bottomSheet.classList.remove('show');
                    document.body.style.overflow = 'auto';
                    
                    // Limpiar formulario
                    const ratingInputs = bottomSheet.querySelectorAll('input[name="rating"]');
                    ratingInputs.forEach(input => input.checked = false);
                    
                    const titleInput = bottomSheet.querySelector('#reviewTitle');
                    const commentTextarea = bottomSheet.querySelector('#reviewComment');
                    if (titleInput) titleInput.value = '';
                    if (commentTextarea) commentTextarea.value = '';
                    
                    // Resetear transform
                    content.style.transform = '';
                    content.style.transition = '';
                }, 300);
            } else {
                // Volver a la posición original
                content.style.transition = 'transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
                content.style.transform = '';
            }

            hasMoved = false;
            startY = 0;
            currentY = 0;
        }, { passive: true });

        // Cerrar al hacer clic en el backdrop
        const backdrop = bottomSheet.querySelector('.bottom-sheet-backdrop');
        if (backdrop) {
            backdrop.addEventListener('click', function() {
                bottomSheet.classList.remove('show');
                document.body.style.overflow = 'auto';
            });
        }
    }

    // ============================================
    // INICIALIZAR AL CARGAR
    // ============================================
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initOffcanvasSwipe();
            initFiltersSwipe();
            initUserModalSwipe();
            initCheckoutBottomSheets();
            initReviewBottomSheet();
        });
    } else {
        initOffcanvasSwipe();
        initFiltersSwipe();
        initUserModalSwipe();
        initCheckoutBottomSheets();
        initReviewBottomSheet();
    }

})();
