/**
 * LAYER MANAGER - Gestor de Capas de UI
 * Coordina la apertura y cierre de overlays para evitar conflictos
 * - Filtros móviles
 * - Menú offcanvas
 * - Modales (favoritos, notificaciones, usuario)
 * - Otros overlays
 */

(function(window) {
    'use strict';
    
    // Singleton para gestionar las capas
    const LayerManager = {
        activeLayer: null,
        layers: {
            offcanvas: null,
            filters: null,
            modal: null
        },
        
        /**
         * Inicializar el gestor
         */
        init() {
            this.setupEventListeners();
        },
        
        /**
         * Cerrar TODAS las capas activas
         */
        closeAll() {
            
            // Cerrar offcanvas
            this.closeOffcanvas();
            
            // Cerrar filtros
            this.closeFilters();
            
            // Cerrar modales
            this.closeModals();
            
            // Resetear body
            document.body.style.overflow = '';
            this.activeLayer = null;
        },
        
        /**
         * Cerrar offcanvas
         */
        closeOffcanvas() {
            // Usar el controlador del header si está disponible
            if (window.OffcanvasMenu) {
                window.OffcanvasMenu.close();
                return;
            }
            
            // Fallback
            const wrapper = document.querySelector('.offcanvas-menu-wrapper');
            const overlay = document.querySelector('.offcanvas-menu-overlay');
            
            if (wrapper && overlay) {
                wrapper.classList.remove('active');
                overlay.classList.remove('active');
            }
        },
        
        /**
         * Cerrar filtros
         */
        closeFilters() {
            const wrapper = document.querySelector('.filters-menu-wrapper');
            const overlay = document.querySelector('.filters-menu-overlay');
            
            if (wrapper && overlay) {
                wrapper.classList.remove('active');
                overlay.classList.remove('active');
            }
        },
        
        /**
         * Cerrar modales
         */
        closeModals() {
            // Modales comunes
            const modals = [
                '#favoritesModal',
                '#notificationsModal',
                '#accountModal',
                '#loginModal',
                '#registerModal'
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
            
        },
        
        /**
         * Abrir offcanvas (cierra todo lo demás)
         */
        openOffcanvas() {
            // Cerrar filtros y modales antes
            this.closeFilters();
            this.closeModals();
            
            // Usar el controlador del header si está disponible
            if (window.OffcanvasMenu) {
                window.OffcanvasMenu.open();
                this.activeLayer = 'offcanvas';
                return;
            }
            
            // Fallback
            const wrapper = document.querySelector('.offcanvas-menu-wrapper');
            const overlay = document.querySelector('.offcanvas-menu-overlay');
            
            if (wrapper && overlay) {
                wrapper.classList.add('active');
                overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
                this.activeLayer = 'offcanvas';
            }
        },
        
        /**
         * Toggle offcanvas (abrir o cerrar)
         */
        toggleOffcanvas() {
            // Usar el controlador del header si está disponible
            if (window.OffcanvasMenu) {
                window.OffcanvasMenu.toggle();
                return;
            }
            
            // Fallback
            const wrapper = document.querySelector('.offcanvas-menu-wrapper');
            
            if (wrapper && wrapper.classList.contains('active')) {
                this.closeOffcanvas();
            } else {
                this.openOffcanvas();
            }
        },
        
        /**
         * Abrir filtros (cierra todo lo demás)
         */
        openFilters() {
            
            // Cerrar offcanvas y modales antes
            this.closeOffcanvas();
            this.closeModals();
            
            const wrapper = document.querySelector('.filters-menu-wrapper');
            const overlay = document.querySelector('.filters-menu-overlay');
            
            if (wrapper && overlay) {
                wrapper.classList.add('active');
                overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
                this.activeLayer = 'filters';
            }
        },
        
        /**
         * Abrir modal (cierra offcanvas y filtros antes)
         */
        openModal(modalId) {
            
            // Cerrar offcanvas y filtros antes
            this.closeOffcanvas();
            this.closeFilters();
            
            this.activeLayer = 'modal';
        },
        
        /**
         * Configurar event listeners globales
         */
        setupEventListeners() {
            // Detectar cuando se abre un modal de Bootstrap
            if (window.$) {
                $(document).on('show.bs.modal', '.modal', () => {
                    this.closeOffcanvas();
                    this.closeFilters();
                    this.activeLayer = 'modal';
                });
            }
            
            // ESC key - cerrar todo
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    this.closeAll();
                }
            });
            
        }
    };
    
    // Exponer globalmente
    window.LayerManager = LayerManager;
    
    // Auto-inicializar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => LayerManager.init());
    } else {
        LayerManager.init();
    }
    
    
})(window);
