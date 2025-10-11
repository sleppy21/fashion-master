/**
 * OFFCANVAS MENU - FUNCIONALIDAD GLOBAL
 * Maneja la apertura/cierre del menú lateral móvil
 */

$(document).ready(function() {
    'use strict';

    // ============================================
    // TOGGLE OFFCANVAS MENU
    // ============================================
    
    // Abrir offcanvas
    $('.canvas__open').on('click', function(e) {
        e.preventDefault();
        $('.offcanvas-menu-wrapper').addClass('active');
        $('.offcanvas-menu-overlay').addClass('active');
        // Ocultar footer y footer móvil del carrito
        $('footer, .footer, .mobile-cart-footer__content').hide();
    });

    // Cerrar offcanvas con botón X
    $('.offcanvas__close').on('click', function(e) {
        e.preventDefault();
        $('.offcanvas-menu-wrapper').removeClass('active');
        $('.offcanvas-menu-overlay').removeClass('active');
        // Mostrar footer y footer móvil del carrito
        $('footer, .footer, .mobile-cart-footer__content').show();
    });

    // Cerrar offcanvas con overlay
    $('.offcanvas-menu-overlay').on('click', function(e) {
        e.preventDefault();
        $('.offcanvas-menu-wrapper').removeClass('active');
        $(this).removeClass('active');
        // Mostrar footer y footer móvil del carrito
        $('footer, .footer, .mobile-cart-footer__content').show();
    });

    // ============================================
    // TOGGLE SUBMENUS EN OFFCANVAS
    // ============================================
    $(document).on('click', '.offcanvas-menu-toggle', function(e) {
        e.preventDefault();
        const $this = $(this);
        const $submenu = $this.next('.offcanvas-submenu');
        
        // Toggle del icono
        $this.toggleClass('active');
        
        // Slide toggle del submenu
        $submenu.slideToggle(300);
        
        // Cerrar otros submenus
        $('.offcanvas-menu-toggle').not($this).removeClass('active');
        $('.offcanvas-submenu').not($submenu).slideUp(300);
    });

    // ============================================
    // CLICK EN PERFIL DE USUARIO EN OFFCANVAS
    // ============================================
    $('#offcanvas-user-profile').on('click', function() {
        // Cerrar offcanvas
        $('.offcanvas-menu-wrapper').removeClass('active');
        $('.offcanvas-menu-overlay').removeClass('active');
        // Mostrar footer y footer móvil del carrito
        $('footer, .footer, .mobile-cart-footer__content').show();
        
        // Abrir modal de usuario después de cerrar offcanvas
        setTimeout(function() {
            $('#userAccountModal').modal('show');
        }, 300);
    });

    // ============================================
    // CLICK EN FAVORITOS DESDE OFFCANVAS
    // ============================================
    $('#favorites-link-mobile').on('click', function(e) {
        e.preventDefault();
        
        // Cerrar offcanvas
        $('.offcanvas-menu-wrapper').removeClass('active');
        $('.offcanvas-menu-overlay').removeClass('active');
        // Mostrar footer y footer móvil del carrito
        $('footer, .footer, .mobile-cart-footer__content').show();
        
        // Abrir modal de favoritos
        setTimeout(function() {
            $('#favoritesModal').modal('show');
        }, 300);
    });

    // ============================================
    // OBSERVER PARA DETECTAR CUANDO SE CIERRA EL MENÚ
    // Y SIEMPRE MOSTRAR EL FOOTER
    // ============================================
    setInterval(function() {
        if (!$('.offcanvas-menu-wrapper').hasClass('active')) {
            $('footer, .footer, .mobile-cart-footer__content').show();
        }
    }, 500);
});
