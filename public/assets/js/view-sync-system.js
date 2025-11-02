/**
 * SISTEMA DE SINCRONIZACIÓN DE VISTAS (Tabla/Grid)
 * Versión: 2.0 - Optimizado y sin conflictos
 * Fecha: 1 de noviembre de 2025
 * 
 * Este sistema garantiza que las vistas siempre estén sincronizadas
 * entre el DOM, localStorage, botones y variables globales.
 */

class ViewSyncSystem {
    constructor(options = {}) {
        this.DEBUG_MODE = options.debugMode || false;
        this.currentView = 'table'; // Vista inicial
        this.tableContainer = null;
        this.gridContainer = null;
        this.viewButtons = [];
        this.updater = null; // Referencia al smooth table updater
        this.loadDataCallback = null; // Función para cargar datos
        
        this.log('🎯 ViewSyncSystem inicializado');
    }
    
    // ============ LOGGING ============
    log(...args) {
        if (this.DEBUG_MODE) console.log('[ViewSync]', ...args);
    }
    
    warn(...args) {
        if (this.DEBUG_MODE) console.warn('[ViewSync]', ...args);
    }
    
    error(...args) {
        console.error('[ViewSync]', ...args);
    }
    
    // ============ INICIALIZACIÓN ============
    init(config = {}) {
        this.log('🚀 Inicializando sistema...');
        
        // Guardar configuración
        this.updater = config.updater || null;
        this.loadDataCallback = config.loadDataCallback || null;
        
        // Obtener contenedores
        this.tableContainer = document.querySelector('.data-table-wrapper');
        this.gridContainer = document.querySelector('.products-grid');
        this.viewButtons = document.querySelectorAll('.view-btn');
        
        if (!this.tableContainer) {
            this.error('❌ No se encontró .data-table-wrapper');
            return false;
        }
        
        // Crear grid si no existe
        if (!this.gridContainer) {
            this.createGridView();
            this.gridContainer = document.querySelector('.products-grid');
        }
        
        // Restaurar vista guardada
        const savedView = this.getSavedView();
        this.currentView = savedView || 'table';
        
        this.log('✅ Sistema inicializado con vista:', this.currentView);
        return true;
    }
    
    // ============ GESTIÓN DE VISTA GUARDADA ============
    getSavedView() {
        try {
            return localStorage.getItem('products_view_preference');
        } catch (e) {
            this.warn('No se pudo leer localStorage:', e);
            return null;
        }
    }
    
    saveView(viewType) {
        try {
            localStorage.setItem('products_view_preference', viewType);
            this.log('💾 Vista guardada:', viewType);
        } catch (e) {
            this.error('Error guardando vista:', e);
        }
    }
    
    // ============ SINCRONIZACIÓN DE ESTADO ============
    syncState(viewType) {
        this.log('🔄 Sincronizando estado a:', viewType);
        
        // 1. Actualizar variable interna
        this.currentView = viewType;
        
        // 2. Actualizar variable global (para compatibilidad)
        window.products_currentView = viewType;
        
        // 3. Guardar en localStorage
        this.saveView(viewType);
        
        // 4. Actualizar botones
        this.viewButtons.forEach(btn => {
            btn.classList.toggle('active', btn.dataset.view === viewType);
        });
        
        this.log('✅ Estado sincronizado');
    }
    
    // ============ OBTENER ESTADO REAL ============
    getRealViewState() {
        // Verificar qué contenedor está realmente visible
        const gridVisible = this.gridContainer && 
                          this.gridContainer.style.display === 'grid';
        const tableVisible = this.tableContainer && 
                           this.tableContainer.style.display !== 'none';
        
        if (gridVisible) return 'grid';
        if (tableVisible) return 'table';
        
        // Fallback
        return this.currentView;
    }
    
    // ============ CREACIÓN DE GRID ============
    createGridView() {
        this.log('🔨 Creando contenedor grid...');
        
        const gridContainer = document.createElement('div');
        gridContainer.className = 'products-grid';
        gridContainer.style.display = 'none';
        gridContainer.style.opacity = '1';
        
        // Insertar después de la tabla
        if (this.tableContainer && this.tableContainer.parentNode) {
            this.tableContainer.parentNode.insertBefore(
                gridContainer, 
                this.tableContainer.nextSibling
            );
            this.log('✅ Grid creado e insertado');
            return true;
        } else {
            this.error('❌ No se pudo insertar grid');
            return false;
        }
    }
    
    // ============ VERIFICAR SI VISTA TIENE DATOS ============
    hasData(viewType) {
        if (viewType === 'grid') {
            return this.gridContainer && 
                   this.gridContainer.querySelector('.product-card') !== null;
        } else {
            const tbody = this.tableContainer?.querySelector('tbody');
            return tbody && tbody.querySelector('tr:not(.no-data)') !== null;
        }
    }
    
    // ============ CAMBIAR VISTA (PRINCIPAL) ============
    changeView(viewType, options = {}) {
        const skipAnimation = options.skipAnimation || false;
        const forceReload = options.forceReload || false;
        
        this.log('🎯 changeView:', viewType, '| Actual:', this.getRealViewState());
        
        // === VALIDACIONES ===
        const currentState = this.getRealViewState();
        
        // 1. Si ya estamos en esa vista y no es forzado, salir
        if (currentState === viewType && this.currentView === viewType && !forceReload) {
            this.log('✅ Ya en vista', viewType);
            return Promise.resolve();
        }
        
        // 2. Bloquear tabla en móvil
        const isMobile = window.innerWidth <= 768;
        if (isMobile && viewType === 'table') {
            this.warn('⛔ Vista tabla bloqueada en móvil');
            return Promise.reject('Vista tabla no disponible en móvil');
        }
        
        // === PREPARACIÓN ===
        // Limpiar caché del updater
        if (this.updater && typeof this.updater.clearCache === 'function') {
            this.updater.clearCache();
        }
        
        // Cerrar elementos flotantes
        if (typeof window.closeStockBubble === 'function') {
            window.closeStockBubble();
        }
        
        // === EJECUTAR CAMBIO ===
        return new Promise((resolve) => {
            const fadeOutDuration = skipAnimation ? 0 : 150;
            const fadeInDuration = skipAnimation ? 0 : 200;
            
            if (viewType === 'grid') {
                this._activateGridView(fadeOutDuration, fadeInDuration, forceReload, resolve);
            } else {
                this._activateTableView(fadeOutDuration, fadeInDuration, forceReload, resolve);
            }
        });
    }
    
    // ============ ACTIVAR VISTA GRID (PRIVADO) ============
    _activateGridView(fadeOutDuration, fadeInDuration, forceReload, resolve) {
        this.log('📱 Activando vista GRID');
        
        // Fade out tabla
        if (fadeOutDuration > 0) {
            this.tableContainer.style.transition = `opacity ${fadeOutDuration}ms ease`;
            this.tableContainer.style.opacity = '0';
        }
        
        setTimeout(() => {
            // Ocultar tabla
            this.tableContainer.style.display = 'none';
            this.tableContainer.style.opacity = '1';
            
            // Mostrar grid
            this.gridContainer.style.display = 'grid';
            
            // Sincronizar estado INMEDIATAMENTE
            this.syncState('grid');
            
            // Fade in grid
            if (fadeInDuration > 0) {
                this.gridContainer.style.opacity = '0';
                this.gridContainer.style.transition = `opacity ${fadeInDuration}ms ease`;
                
                setTimeout(() => {
                    this.gridContainer.style.opacity = '1';
                }, 50);
            } else {
                this.gridContainer.style.opacity = '1';
            }
            
            // Cargar datos si es necesario
            const hasData = this.hasData('grid');
            if (!hasData || forceReload) {
                this.log('📦 Cargando datos para grid...');
                if (this.loadDataCallback) {
                    this.loadDataCallback().then(() => {
                        this.log('✅ Datos cargados en grid');
                        resolve();
                    });
                } else {
                    this.warn('⚠️ No hay callback para cargar datos');
                    resolve();
                }
            } else {
                this.log('✅ Grid ya tiene datos');
                resolve();
            }
        }, fadeOutDuration);
    }
    
    // ============ ACTIVAR VISTA TABLA (PRIVADO) ============
    _activateTableView(fadeOutDuration, fadeInDuration, forceReload, resolve) {
        this.log('📋 Activando vista TABLA');
        
        // Fade out grid
        if (fadeOutDuration > 0 && this.gridContainer) {
            this.gridContainer.style.transition = `opacity ${fadeOutDuration}ms ease`;
            this.gridContainer.style.opacity = '0';
        }
        
        setTimeout(() => {
            // Ocultar grid
            if (this.gridContainer) {
                this.gridContainer.style.display = 'none';
                this.gridContainer.style.opacity = '1';
            }
            
            // Mostrar tabla
            this.tableContainer.style.display = 'block';
            
            // Sincronizar estado INMEDIATAMENTE
            this.syncState('table');
            
            // Fade in tabla
            if (fadeInDuration > 0) {
                this.tableContainer.style.opacity = '0';
                this.tableContainer.style.transition = `opacity ${fadeInDuration}ms ease`;
                
                setTimeout(() => {
                    this.tableContainer.style.opacity = '1';
                }, 50);
            } else {
                this.tableContainer.style.opacity = '1';
            }
            
            // Cargar datos si es necesario
            const hasData = this.hasData('table');
            if (!hasData || forceReload) {
                this.log('📋 Cargando datos para tabla...');
                if (this.loadDataCallback) {
                    this.loadDataCallback().then(() => {
                        this.log('✅ Datos cargados en tabla');
                        resolve();
                    });
                } else {
                    this.warn('⚠️ No hay callback para cargar datos');
                    resolve();
                }
            } else {
                this.log('✅ Tabla ya tiene datos');
                resolve();
            }
        }, fadeOutDuration);
    }
    
    // ============ MÉTODOS DE UTILIDAD ============
    getCurrentView() {
        return this.currentView;
    }
    
    isGridView() {
        return this.currentView === 'grid';
    }
    
    isTableView() {
        return this.currentView === 'table';
    }
    
    // Destruir instancia
    destroy() {
        this.log('🗑️ Destruyendo ViewSyncSystem...');
        this.updater = null;
        this.loadDataCallback = null;
        this.tableContainer = null;
        this.gridContainer = null;
        this.viewButtons = [];
    }
}

// Exportar para uso global
window.ViewSyncSystem = ViewSyncSystem;
console.log('✅ ViewSyncSystem cargado');
