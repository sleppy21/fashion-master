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
    
    function forceOpenFilters() {
        const wrapper = document.querySelector('.filters-menu-wrapper');
        const overlay = document.querySelector('.filters-menu-overlay');
        
        if (wrapper && overlay) {
            wrapper.classList.add('active');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }
    
    function forceCloseFilters() {
        const wrapper = document.querySelector('.filters-menu-wrapper');
        const overlay = document.querySelector('.filters-menu-overlay');
        
        if (wrapper && overlay) {
            wrapper.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    }
    
    let initialized = false;
    let sidebarOriginalParent = null;
    let sidebarClone = null;
    
    function init() {
        // PREVENIR MÚLTIPLES INICIALIZACIONES
        if (initialized) return;
        
        const btnFilters = document.getElementById('btnMobileFilters');
        const sidebar = document.getElementById('shopFilters');
        const wrapper = document.querySelector('.filters-menu-wrapper');
        const overlay = document.querySelector('.filters-menu-overlay');
        const closeBtn = document.querySelector('.filters__close');
        
        // Si no existen los elementos, NO reintentar (evita loop infinito)
        if (!btnFilters || !sidebar || !wrapper) {
            return;
        }
        
        // Marcar como inicializado para evitar duplicados
        initialized = true;
        
        // Actualizar cache de elementos
        elements.btnFilters = btnFilters;
        elements.sidebar = sidebar;
        elements.wrapper = wrapper;
        elements.overlay = overlay;
        elements.closeBtn = closeBtn;
        
        // ✅ FIX: CLONAR el sidebar en lugar de moverlo
        // Guardar el padre original
        sidebarOriginalParent = sidebar.parentElement;
        
        // Crear un clon del sidebar para el wrapper móvil
        sidebarClone = sidebar.cloneNode(true);
        sidebarClone.id = 'shopFilters-mobile'; // Cambiar ID para evitar duplicados
        wrapper.appendChild(sidebarClone);
        
        // Mantener el sidebar original en desktop (NO moverlo)
        sidebar.style.display = 'block';
        
        // Mostrar botón solo en móvil
        if (window.innerWidth <= 991) {
            btnFilters.style.display = 'flex';
        }
        
        // Event: Click en botón (usar once para evitar duplicados)
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
        if (closeBtn) {
            closeBtn.addEventListener('click', forceCloseFilters);
        }
        
        // Event: Click en overlay
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
        
        // ✅ FIX: Sincronizar clics entre sidebar original y clon móvil
        // Los filtros se manejan con event delegation en shop-filters.js
        // que ya escucha todos los clicks en document, por lo que
        // funcionarán tanto en el sidebar original como en el clon
        
    }
    
    // Iniciar cuando el DOM esté listo (SOLO UNA VEZ)
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        // DOM ya cargado, iniciar inmediatamente
        init();
    }
})();
