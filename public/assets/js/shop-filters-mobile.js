/**
 * FILTROS MÓVILES - Patrón Offcanvas
 * Abre los filtros laterales igual que el menú hamburguesa
 */
(function() {
    'use strict';
    
    // Funciones para abrir/cerrar usando LayerManager
    function forceOpenFilters() {
        
        // Usar LayerManager si está disponible
        if (window.LayerManager) {
            window.LayerManager.openFilters();
        } else {
            // Fallback manual
            const wrapper = document.querySelector('.filters-menu-wrapper');
            const overlay = document.querySelector('.filters-menu-overlay');
            
            if (wrapper && overlay) {
                wrapper.classList.add('active');
                overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        }
    }
    
    function forceCloseFilters() {
        
        // Usar LayerManager si está disponible
        if (window.LayerManager) {
            window.LayerManager.closeFilters();
        } else {
            // Fallback manual
            const wrapper = document.querySelector('.filters-menu-wrapper');
            const overlay = document.querySelector('.filters-menu-overlay');
            
            if (wrapper && overlay) {
                wrapper.classList.remove('active');
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        }
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
