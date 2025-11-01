/**
 * Optimized Mobile Filters for Shop Page
 * Versión optimizada que evita recargas y congelamiento
 */
(function($) {
    'use strict';

    // Cache de elementos DOM
    const elements = {
        btnFilters: null,
        sidebar: null,
        wrapper: null,
        overlay: null,
        closeBtn: null
    };

    function openFilters() {
        if (!elements.wrapper || !elements.overlay) return;
        elements.wrapper.classList.add('active');
        elements.overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeFilters() {
        if (!elements.wrapper || !elements.overlay) return;
        elements.wrapper.classList.remove('active');
        elements.overlay.classList.remove('active');
        document.body.style.overflow = '';
    }
    
    function init() {
        const btnFilters = document.getElementById('btnMobileFilters');
        const sidebar = document.getElementById('shopFilters');
        const wrapper = document.querySelector('.filters-menu-wrapper');
        
        if (!btnFilters || !sidebar || !wrapper) {
            setTimeout(init, 100);
            return;
        }
        
        
        // MOVER el sidebar DENTRO del wrapper
        wrapper.appendChild(sidebar);
        sidebar.style.display = 'block';
        
        // Mostrar botón solo en móvil
        if (window.innerWidth <= 991) {
            btnFilters.style.display = 'flex';
        }
        
        // Event: Click en botón
        btnFilters.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            forceOpenFilters();
        });
        
        // Event: Touch para móviles
        btnFilters.addEventListener('touchstart', function(e) {
            e.preventDefault();
            forceOpenFilters();
        }, { passive: false });
        
        // Event: Click en botón cerrar
        const closeBtn = document.querySelector('.filters__close');
        if (closeBtn) {
            closeBtn.addEventListener('click', forceCloseFilters);
        }
        
        // Event: Click en overlay
        const overlay = document.querySelector('.filters-menu-overlay');
        if (overlay) {
            overlay.addEventListener('click', forceCloseFilters);
        }
        
        // Event: Responsive
        window.addEventListener('resize', function() {
            if (window.innerWidth > 991) {
                btnFilters.style.display = 'none';
                forceCloseFilters();
            } else {
                btnFilters.style.display = 'flex';
            }
        });
        
    }
    
    // Iniciar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    // También probar inmediatamente
    setTimeout(init, 100);
    setTimeout(init, 500);
})();
