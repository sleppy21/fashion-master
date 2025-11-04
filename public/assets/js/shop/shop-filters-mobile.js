/**
 * Mobile Filters para Shop Page - Versión Simplificada
 * Incluye sistema de gestión de offcanvas
 */
(function() {
    'use strict';

    let initialized = false;
    
    // SISTEMA DE GESTIÓN DE OFFCANVAS Y MODALES GLOBAL
    window.OffcanvasManager = {
        activeOffcanvas: null,
        
        // Registrar y abrir un offcanvas
        open: function(offcanvasId) {
            // Si hay otro offcanvas abierto, cerrarlo primero
            if (this.activeOffcanvas && this.activeOffcanvas !== offcanvasId) {
                this.close(this.activeOffcanvas);
            }
            
            // Cerrar modales antes de abrir offcanvas
            this.closeModals();
            
            // Abrir el offcanvas específico
            if (offcanvasId === 'filters') {
                const wrapper = document.querySelector('.filters-menu-wrapper');
                const overlay = document.querySelector('.filters-menu-overlay');
                if (wrapper && overlay) {
                    wrapper.classList.add('active');
                    overlay.classList.add('active');
                    document.body.classList.add('offcanvas-active');
                    document.body.style.overflow = 'hidden';
                }
            } else if (offcanvasId === 'menu') {
                const wrapper = document.querySelector('.offcanvas-menu-wrapper');
                const overlay = document.querySelector('.offcanvas-menu-overlay');
                if (wrapper && overlay) {
                    wrapper.classList.add('active');
                    overlay.classList.add('active');
                    document.body.classList.add('offcanvas-active');
                    document.body.style.overflow = 'hidden';
                }
            }
            
            this.activeOffcanvas = offcanvasId;
            console.log('✅ Offcanvas abierto:', offcanvasId);
        },
        
        // Cerrar un offcanvas específico
        close: function(offcanvasId) {
            if (this.activeOffcanvas !== offcanvasId) return;
            this.activeOffcanvas = null;
            // Si ya está cerrado, no hacer nada
            if (offcanvasId === 'filters') {
                const wrapper = document.querySelector('.filters-menu-wrapper');
                const overlay = document.querySelector('.filters-menu-overlay');
                if (wrapper && !wrapper.classList.contains('active')) return;
                if (wrapper) wrapper.classList.remove('active');
                if (overlay) overlay.classList.remove('active');
            } else if (offcanvasId === 'menu') {
                const wrapper = document.querySelector('.offcanvas-menu-wrapper');
                const overlay = document.querySelector('.offcanvas-menu-overlay');
                if (wrapper && !wrapper.classList.contains('active')) return;
                if (wrapper) wrapper.classList.remove('active');
                if (overlay) overlay.classList.remove('active');
            }
            document.body.classList.remove('offcanvas-active');
            document.body.style.overflow = '';
            console.log('✅ Offcanvas cerrado:', offcanvasId);
        },

        // Cierre forzado por swipe (sin duplicidad)
        forceClose: function(offcanvasId) {
            if (this.activeOffcanvas !== offcanvasId) return;
            this.activeOffcanvas = null;
            if (offcanvasId === 'filters') {
                const wrapper = document.querySelector('.filters-menu-wrapper');
                const overlay = document.querySelector('.filters-menu-overlay');
                if (wrapper) wrapper.classList.remove('active');
                if (overlay) overlay.classList.remove('active');
            } else if (offcanvasId === 'menu') {
                const wrapper = document.querySelector('.offcanvas-menu-wrapper');
                const overlay = document.querySelector('.offcanvas-menu-overlay');
                if (wrapper) wrapper.classList.remove('active');
                if (overlay) overlay.classList.remove('active');
            }
            document.body.classList.remove('offcanvas-active');
            document.body.style.overflow = '';
            console.log('✅ Offcanvas cerrado por swipe:', offcanvasId);
        },
        
        // Verificar si hay algún offcanvas abierto
        isAnyOpen: function() {
            return this.activeOffcanvas !== null;
        },
        
        // Cerrar todos los offcanvas
        closeAll: function() {
            if (this.activeOffcanvas) {
                this.close(this.activeOffcanvas);
            }
        },
        
        // Abrir modal (cierra offcanvas primero)
        openModal: function(modalId) {
            console.log('✅ Abriendo modal:', modalId);
            this.closeAll();
        },
        
        // Cerrar modales
        closeModals: function() {
            const modals = [
                '#favoritesModal',
                '#notificationsModal',
                '#accountModal',
                '#loginModal',
                '#registerModal',
                '#userAccountModal',
                '#saveAddressModal',
                '#selectAddressModal',
                '#orderDetailsModal'
            ];
            
            modals.forEach(modalId => {
                const modal = document.querySelector(modalId);
                if (modal) {
                    // Bootstrap modals
                    if (window.bootstrap && bootstrap.Modal) {
                        const bsModal = bootstrap.Modal.getInstance(modal);
                        if (bsModal) {
                            bsModal.hide();
                        }
                    }
                    // jQuery modals
                    else if (window.$ && $(modal).hasClass('show')) {
                        $(modal).modal('hide');
                    }
                    // Manual close
                    else {
                        modal.classList.remove('show');
                        modal.style.display = 'none';
                    }
                }
            });
            
            // Cerrar backdrop de Bootstrap
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(backdrop => backdrop.remove());
        }
    };
    
    function init() {
        // Prevenir múltiples inicializaciones
        if (initialized) return;
        initialized = true;
        
        const btnFilters = document.getElementById('btnMobileFilters');
        const shopFilters = document.getElementById('shopFilters');
        const wrapper = document.querySelector('.filters-menu-wrapper');
        const overlay = document.querySelector('.filters-menu-overlay');
        const closeBtn = document.querySelector('.filters__close');
        const mobileContent = document.getElementById('mobile-filters-content');
        
        // Verificar que existan todos los elementos necesarios
        if (!btnFilters || !shopFilters || !wrapper || !mobileContent) {
            console.warn('⚠️ Mobile filters: elementos no encontrados');
            return;
        }
        
        console.log('✅ Inicializando filtros móviles...');
        
        // PASO 1: Clonar SOLO las secciones de filtros (sin el header del sidebar)
        const filterSections = shopFilters.querySelectorAll('.filter-section');
        
        if (filterSections.length === 0) {
            console.warn('⚠️ No se encontraron secciones de filtros');
            return;
        }
        
        // Limpiar contenido previo
        mobileContent.innerHTML = '';
        
        // Clonar cada sección de filtros
        filterSections.forEach(section => {
            const clone = section.cloneNode(true);
            mobileContent.appendChild(clone);
        });
        
        console.log(`✅ ${filterSections.length} secciones de filtros clonadas`);
        
        // PASO 2: Event handlers para abrir/cerrar
        btnFilters.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleFilters();
        });
        
        if (closeBtn) {
            closeBtn.addEventListener('click', closeFilters);
        }
        
        if (overlay) {
            overlay.addEventListener('click', closeFilters);
        }
        
        // Detectar clicks fuera del wrapper para cerrar el offcanvas
        document.addEventListener('click', function(e) {
            if (wrapper && wrapper.classList.contains('active')) {
                // Ignorar clicks en el botón de filtros y sus hijos
                const btnFilters = document.getElementById('btnMobileFilters');
                if (btnFilters && (e.target === btnFilters || btnFilters.contains(e.target))) {
                    return;
                }
                
                // Si el click NO está dentro del wrapper, cerrar el offcanvas
                if (!wrapper.contains(e.target) && !e.target.closest('.filters-menu-wrapper')) {
                    closeFilters();
                }
            }
        });
        
        // PASO 3: Responsive
        handleResize();
        window.addEventListener('resize', handleResize);
        
        console.log('✅ Filtros móviles inicializados correctamente');
    }
    
    function toggleFilters() {
        const wrapper = document.querySelector('.filters-menu-wrapper');
        const isOpen = wrapper && wrapper.classList.contains('active');
        
        if (isOpen) {
            closeFilters();
        } else {
            openFilters();
        }
    }
    
    function openFilters() {
        const wrapper = document.querySelector('.filters-menu-wrapper');
        const overlay = document.querySelector('.filters-menu-overlay');
        
        if (wrapper && overlay) {
            // Usar el manager para coordinar
            window.OffcanvasManager.open('filters');
            console.log('✅ Filtros móviles abiertos');
        }
    }
    
    function closeFilters() {
        // Usar el manager para cerrar
        window.OffcanvasManager.close('filters');
        console.log('✅ Filtros móviles cerrados');
    }
    
    function handleResize() {
        const btnFilters = document.getElementById('btnMobileFilters');
        if (!btnFilters) return;
        
        if (window.innerWidth > 991) {
            btnFilters.style.display = 'none';
            closeFilters();
        } else {
            btnFilters.style.display = 'flex';
        }
    }
    
    // Iniciar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    // ============================================
    // EVENT LISTENERS GLOBALES
    // ============================================
    
    // Detectar cuando se abre un modal de Bootstrap
    if (window.$) {
        $(document).on('show.bs.modal', '.modal', function() {
            window.OffcanvasManager.closeAll();
        });
    }
    
    // ESC key - cerrar todo
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            window.OffcanvasManager.closeAll();
            window.OffcanvasManager.closeModals();
        }
    });
    
    // ============================================
    // ALIAS PARA COMPATIBILIDAD CON CÓDIGO EXISTENTE
    // ============================================
    
    // Alias LayerManager para mantener compatibilidad
    window.LayerManager = {
        closeAll: () => {
            window.OffcanvasManager.closeAll();
            window.OffcanvasManager.closeModals();
        },
        closeOffcanvas: () => {
            if (window.OffcanvasMenu) {
                window.OffcanvasMenu.close();
            } else {
                window.OffcanvasManager.close('menu');
            }
        },
        closeFilters: () => window.OffcanvasManager.close('filters'),
        closeModals: () => window.OffcanvasManager.closeModals(),
        openOffcanvas: () => {
            if (window.OffcanvasMenu) {
                window.OffcanvasMenu.open();
            } else {
                window.OffcanvasManager.open('menu');
            }
        },
        openFilters: () => window.OffcanvasManager.open('filters'),
        openModal: (modalId) => window.OffcanvasManager.openModal(modalId),
        toggleOffcanvas: () => {
            if (window.OffcanvasMenu) {
                window.OffcanvasMenu.toggle();
            } else {
                const wrapper = document.querySelector('.offcanvas-menu-wrapper');
                if (wrapper && wrapper.classList.contains('active')) {
                    window.OffcanvasManager.close('menu');
                } else {
                    window.OffcanvasManager.open('menu');
                }
            }
        }
    };
    
    console.log('✅ LayerManager (alias) y OffcanvasManager listos');

})();
