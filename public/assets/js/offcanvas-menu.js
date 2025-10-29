/**
 * OFFCANVAS MENU - FUNCIONALIDAD GLOBAL
 * Maneja la apertura/cierre del menú lateral móvil
 */

// Implementación jQuery
(function($) {
    'use strict';
    
    // Verificar disponibilidad de jQuery
    if (typeof $ === 'undefined') {
        console.error('jQuery no está disponible para offcanvas-menu.js');
        return;
    }

    // Función principal de inicialización
    function initOffcanvasMenu() {
        // Abrir offcanvas usando delegación de eventos
        $(document).on('click', '.canvas__open', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            $('.offcanvas-menu-wrapper').addClass('active');
            $('.offcanvas-menu-overlay').addClass('active');
            $('footer, .footer, .mobile-cart-footer__content').hide();
        });

        // Cerrar offcanvas con botón X
        $('.offcanvas__close').on('click', function(e) {
            e.preventDefault();
            $('.offcanvas-menu-wrapper').removeClass('active');
            $('.offcanvas-menu-overlay').removeClass('active');
            $('footer, .footer, .mobile-cart-footer__content').show();
        });

        // Cerrar offcanvas con overlay
        $('.offcanvas-menu-overlay').on('click', function(e) {
            e.preventDefault();
            $('.offcanvas-menu-wrapper').removeClass('active');
            $(this).removeClass('active');
            $('footer, .footer, .mobile-cart-footer__content').show();
        });

        // Toggle submenus en offcanvas
        $(document).on('click', '.offcanvas-menu-toggle', function(e) {
            e.preventDefault();
            const $this = $(this);
            const $submenu = $this.next('.offcanvas-submenu');
            
            $this.toggleClass('active');
            $submenu.slideToggle(300);
            
            // Cerrar otros submenus
            $('.offcanvas-menu-toggle').not($this).removeClass('active');
            $('.offcanvas-submenu').not($submenu).slideUp(300);
        });

        // Click en perfil de usuario
        $('#offcanvas-user-profile').on('click', function() {
            closeOffcanvasAndDo(function() {
                $('#userAccountModal').modal('show');
            });
        });

        // Click en favoritos
        $('#favorites-link-mobile').on('click', function(e) {
            e.preventDefault();
            closeOffcanvasAndDo(function() {
                $('#favoritesModal').modal('show');
            });
        });

        // Observer para el footer
        setInterval(function() {
            if (!$('.offcanvas-menu-wrapper').hasClass('active')) {
                $('footer, .footer, .mobile-cart-footer__content').show();
            }
        }, 500);
    }

    // Función helper para cerrar offcanvas y ejecutar callback
    function closeOffcanvasAndDo(callback) {
        $('.offcanvas-menu-wrapper').removeClass('active');
        $('.offcanvas-menu-overlay').removeClass('active');
        $('footer, .footer, .mobile-cart-footer__content').show();
        
        if (typeof callback === 'function') {
            setTimeout(callback, 300);
        }
    }

    // Inicializar cuando el DOM esté listo
    $(document).ready(initOffcanvasMenu);

})(jQuery);

// Backup en Vanilla JS (fuera del IIFE de jQuery)
(function() {
    'use strict';

    function initVanillaOffcanvas() {
        const openOffcanvas = () => {
            const wrapper = document.querySelector('.offcanvas-menu-wrapper');
            const overlay = document.querySelector('.offcanvas-menu-overlay');
            
            if (wrapper && overlay) {
                wrapper.classList.add('active');
                overlay.classList.add('active');
                
                // Ocultar footer
                document.querySelectorAll('footer, .footer, .mobile-cart-footer__content')
                    .forEach(el => el.style.display = 'none');
            }
        };
        
        const closeOffcanvas = () => {
            const wrapper = document.querySelector('.offcanvas-menu-wrapper');
            const overlay = document.querySelector('.offcanvas-menu-overlay');
            
            if (wrapper && overlay) {
                wrapper.classList.remove('active');
                overlay.classList.remove('active');
                
                // Mostrar footer
                document.querySelectorAll('footer, .footer, .mobile-cart-footer__content')
                    .forEach(el => el.style.display = 'block');
            }
        };
        
        // Event listeners
        document.addEventListener('click', function(e) {
            // Abrir menu
            if (e.target.closest('.canvas__open')) {
                e.preventDefault();
                e.stopPropagation();
                openOffcanvas();
            }
            
            // Cerrar con X
            if (e.target.closest('.offcanvas__close')) {
                e.preventDefault();
                closeOffcanvas();
            }
            
            // Cerrar con overlay
            if (e.target.classList.contains('offcanvas-menu-overlay')) {
                e.preventDefault();
                closeOffcanvas();
            }
        });
    }

    // Inicializar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initVanillaOffcanvas);
    } else {
        initVanillaOffcanvas();
    }
})();