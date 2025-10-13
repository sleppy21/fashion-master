<?php
/**
 * VISTA DE GESTIoN DE CATEGORoAS - DISEoO MODERNO
 * Sistema unificado con diseoo actualizado
 */
?>

<div class="admin-module admin-categorias-module">
    <!-- Header del modulo -->
    <div class="module-header">
        <div class="module-title">
            <div class="module-icon">
                <i class="fas fa-tags"></i>
            </div>
            <div class="module-info">
                <h2 class="module-name">Gestion de Categoroas</h2>
                <p class="module-description">Administra las categoroas de la tienda</p>
            </div>
        </div>
        <div class="module-actions">
            <button class="btn-modern btn-primary" onclick="window.showCreateCategoriaModal();" style="color: white !important;">
                <i class="fas fa-plus" style="color: white !important;"></i>
                <span style="color: white !important;">Nueva <span class="btn-text-mobile-hide">Categoroa</span></span>
            </button>
            <button class="btn-modern btn-secondary" onclick="exportCategorias()" style="color: white !important;">
                <i class="fas fa-download" style="color: white !important;"></i>
                <span style="color: white !important;">Exportar <span class="btn-text-mobile-hide">Excel</span></span>
            </button>
            <button class="btn-modern btn-info" onclick="showCategoriaReport()" style="color: white !important;">
                <i class="fas fa-chart-bar" style="color: white !important;"></i>
                <span style="color: white !important;">Reporte <span class="btn-text-mobile-hide">Categoroas</span></span>
            </button>
        </div>
    </div>

    <!-- Filtros y bosqueda -->
    <div class="module-filters">
        <div class="search-container">
            <div class="search-input-group">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="search-categorias" class="search-input" 
                       placeholder="Buscar categoroas por nombre..." oninput="handleSearchInputCategorias()">
                <button class="search-clear" onclick="clearCategoriaSearch()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        
        <div class="filters-grid">
            <div class="filter-group">
                <label class="filter-label">Estado</label>
                <select id="filter-status" class="filter-select" onchange="filterCategorias()">
                    <option value="">Todos los estados</option>
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label">Fecha</label>
                <button type="button" 
                        id="filter-fecha-categoria" 
                        class="filter-select-2"
                        style="justify-content: flex-start;">
                    <span id="filter-fecha-categoria-text">Seleccionar fechas</span>
                </button>
                <input type="hidden" id="filter-fecha-categoria-value">
            </div>
            <div class="filter-group">
                <button class="btn-modern btn-outline" onclick="clearAllCategoriaFilters()">
                    <i class="fas fa-filter-circle-xmark"></i>
                    Limpiar
                </button>
            </div>
        </div>
    </div>

    <!-- Contenido del modulo -->
    <div class="module-content">
        <div class="data-table-container">
            <div class="table-controls">
                <div class="table-info">
                    <span class="results-count">
                        Mostrando  <span id="showing-end-categorias">0</span> 
                        de <span id="total-categorias">0</span> categoroas
                    </span>
                </div>
                <div class="table-actions">
                    <div class="view-options">
                        <button class="view-btn active" data-view="table" onclick="toggleCategoriaView('table')">
                            <i class="fas fa-table"></i>
                        </button>
                        <button class="view-btn" data-view="grid" onclick="toggleCategoriaView('grid')">
                            <i class="fas fa-th"></i>
                        </button>
                    </div>
                    <div class="bulk-actions" style="display: none;">
                        <span class="selected-count">0</span> seleccionados
                        <select class="bulk-select" onchange="handleBulkCategoriaAction(this.value); this.value='';">
                            <option value="">Acciones en lote</option>
                            <option value="activar">Activar seleccionados</option>
                            <option value="desactivar">Desactivar seleccionados</option>
                            <option value="eliminar">Eliminar seleccionados</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="data-table-wrapper scrollable-table">
                <table class="data-table categorias-table">
                    <thead class="table-header">
                        <tr>
                            <th class="sortable" data-sort="id">
                                <span>ID</span>
                            </th>
                            <th class="no-sort">Imagen</th>
                            <th class="sortable" data-sort="nombre">
                                <span>Nombre</span>
                            </th>
                            <th class="sortable" data-sort="codigo">
                                <span>Codigo</span>
                            </th>
                            <th class="sortable" data-sort="descripcion">
                                <span>Descripcion</span>
                            </th>
                            <th class="sortable" data-sort="estado">
                                <span>Estado</span>
                            </th>
                            <th class="sortable" data-sort="fecha">
                                <span>Fecha</span>
                            </th>
                            <th class="no-sort actions-column">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="categorias-table-body" class="table-body">
                        <tr class="loading-row">
                            <td colspan="8" class="loading-cell">
                                <div class="loading-content">
                                    <div class="spinner"></div>
                                    <span>Cargando categoroas...</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginacion -->
            <div class="pagination-container">
                <div class="pagination-info">
                    <span class="pagination-text">
                        Pogina <span id="current-page-categorias">1</span> de <span id="total-pages-categorias">1</span>
                    </span>
                </div>
                <div class="pagination-controls">
                    <button class="pagination-btn" id="first-page-categorias" onclick="goToFirstPageCategorias()">
                        <i class="fas fa-angle-double-left"></i>
                    </button>
                    <button class="pagination-btn" id="prev-page-categorias" onclick="previousPageCategorias()">
                        <i class="fas fa-angle-left"></i>
                    </button>
                    <div class="pagination-numbers" id="pagination-numbers-categorias">
                        <!-- Nomeros de pogina dinomicos -->
                    </div>
                    <button class="pagination-btn" id="next-page-categorias" onclick="nextPageCategorias()">
                        <i class="fas fa-angle-right"></i>
                    </button>
                    <button class="pagination-btn" id="last-page-categorias" onclick="goToLastPageCategorias()">
                        <i class="fas fa-angle-double-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================
         MOBILE ONLY: Boton y Modal de Filtros
         ======================================== -->
    
    <!-- Boton flotante para abrir filtros (solo mobile) -->
    <button class="mobile-filter-btn-categorias" id="mobile-filter-btn-categorias" onclick="window.toggleFiltersModalCategorias()" style="display: none;">
        <i class="fas fa-filter"></i>
    </button>

    <!-- Modal de filtros (solo mobile) -->
    <div class="filters-modal-categorias" id="filters-modal-categorias" onclick="if(event.target.id === 'filters-modal-categorias') window.closeFiltersModalCategorias()">
        <div class="filters-modal-categorias-content" onclick="event.stopPropagation();">
            <div class="filters-modal-categorias-header">
                <h3 class="filters-modal-categorias-title">
                    <i class="fas fa-filter"></i> Filtros
                </h3>
                <button class="filters-modal-categorias-close" onclick="window.closeFiltersModalCategorias()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="filters-modal-categorias-body">
                <!-- Bosqueda -->
                <div class="modal-search-container">
                    <div class="modal-search-input-group">
                        <i class="fas fa-search modal-search-icon"></i>
                        <input type="text" id="modal-search-categorias" class="modal-search-input" 
                               placeholder="Buscar categoroas..." oninput="window.handleModalSearchInputCategorias()">
                        <button class="modal-search-clear" onclick="window.clearModalSearchCategorias()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="modal-filters-grid">
                    <div class="modal-filter-group">
                        <label class="modal-filter-label">Categoroa</label>
                        <select id="modal-filter-category" class="modal-filter-select" onchange="window.filterCategoriasFromModal()">
                            <option value="">Todos los tipos</option>
                            <!-- Se cargan dinomicamente -->
                        </select>
                    </div>
                    
                    <div class="modal-filter-group">
                        <label class="modal-filter-label">Estado</label>
                        <select id="modal-filter-status" class="modal-filter-select" onchange="window.filterCategoriasFromModal()">
                            <option value="">Todos los estados</option>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                    
                    <div class="modal-filter-group">
                        <label class="modal-filter-label">Stock</label>
                        <select id="modal-filter-stock" class="modal-filter-select" onchange="window.filterCategoriasFromModal()">
                            <option value="">Todos los estados</option>
                            <option value="agotado">Sin productos</option>
                            <option value="bajo">Con productos</option>
                        </select>
                    </div>
                    
                    <div class="modal-filter-group">
                        <label class="modal-filter-label">Fecha</label>
                        <button type="button" 
                                id="modal-filter-fecha" 
                                class="modal-filter-select"
                                style="justify-content: flex-start;">
                            <span id="modal-filter-fecha-text">Seleccionar fechas</span>
                        </button>
                        <input type="hidden" id="modal-filter-fecha-value">
                    </div>
                </div>

                <!-- Acciones -->
                <div class="modal-filter-actions">
                    <button class="btn-modern btn-outline" onclick="window.clearModalFiltersCategorias()">
                        <i class="fas fa-filter-circle-xmark"></i> Limpiar
                    </button>
                    <button class="btn-modern btn-primary" onclick="window.closeFiltersModalCategorias()">
                        <i class="fas fa-check"></i> Aplicar
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- MODAL PARA CREAR/EDITAR CATEGORoA -->
<div id="categoria-modal" class="modal-overlay" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h2 id="modal-title">Nueva Categoroa</h2>
            <button class="modal-close" onclick="closeCategoriaModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="categoria-form" onsubmit="saveCategoria(event)">
            <div class="modal-body">
                <input type="hidden" id="categoria-id" name="id_categoria">
                <input type="hidden" id="categoria-action" name="action" value="create">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="categoria-nombre">
                            Nombre <span class="required">*</span>
                        </label>
                        <input type="text" 
                               id="categoria-nombre" 
                               name="nombre_categoria" 
                               class="form-control" 
                               placeholder="Ej: Accesorios" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="categoria-codigo">
                            Codigo <span class="required">*</span>
                        </label>
                        <input type="text" 
                               id="categoria-codigo" 
                               name="codigo_categoria" 
                               class="form-control" 
                               placeholder="Ej: CAT-001" 
                               required>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="categoria-descripcion">
                            Descripcion
                        </label>
                        <textarea id="categoria-descripcion" 
                                  name="descripcion_categoria" 
                                  class="form-control" 
                                  rows="3" 
                                  placeholder="Descripcion de la categoroa..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="categoria-imagen">
                            Imagen URL
                        </label>
                        <input type="text" 
                               id="categoria-imagen" 
                               name="imagen_categoria" 
                               class="form-control" 
                               placeholder="URL de la imagen">
                    </div>
                    
                    <div class="form-group">
                        <label for="categoria-estado">
                            Estado <span class="required">*</span>
                        </label>
                        <select id="categoria-estado" 
                                name="estado_categoria" 
                                class="form-control" 
                                required>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn-modern btn-secondary" onclick="closeCategoriaModal()">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="submit" class="btn-modern btn-primary">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL PARA VER DETALLES DE CATEGORoA (SOLO LECTURA) -->
<div id="categoria-view-modal" class="modal-overlay" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h2><i class="fas fa-eye"></i> Detalles de Categoroa</h2>
            <button class="modal-close" onclick="closeCategoriaViewModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="modal-body">
            <div class="view-details-grid">
                <div class="view-detail-item">
                    <label>ID:</label>
                    <span id="view-categoria-id">-</span>
                </div>
                
                <div class="view-detail-item">
                    <label>Codigo:</label>
                    <span id="view-categoria-codigo">-</span>
                </div>
                
                <div class="view-detail-item full-width">
                    <label>Nombre:</label>
                    <span id="view-categoria-nombre">-</span>
                </div>
                
                <div class="view-detail-item full-width">
                    <label>Descripcion:</label>
                    <span id="view-categoria-descripcion">-</span>
                </div>
                
                <div class="view-detail-item">
                    <label>Estado:</label>
                    <span id="view-categoria-estado">-</span>
                </div>
                
                <div class="view-detail-item">
                    <label>Fecha de Creacion:</label>
                    <span id="view-categoria-fecha">-</span>
                </div>
                
                <div class="view-detail-item full-width">
                    <label>Imagen:</label>
                    <div id="view-categoria-imagen-container" style="margin-top: 10px;">
                        <img id="view-categoria-imagen" 
                             src="" 
                             alt="Imagen de categoroa" 
                             style="max-width: 200px; max-height: 200px; border-radius: 8px; display: none;">
                        <span id="view-categoria-sin-imagen" style="color: #999;">Sin imagen</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="modal-footer">
            <button type="button" class="btn-modern btn-secondary" onclick="closeCategoriaViewModal()">
                <i class="fas fa-times"></i> Cerrar
            </button>
            <button type="button" class="btn-modern btn-primary" onclick="closeCategoriaViewModal(); editCategoria(window.currentCategoriaViewId);">
                <i class="fas fa-edit"></i> Editar
            </button>
        </div>
    </div>
</div>

<!-- Script de inicialización inmediata de funciones globales -->
<script>
//
// console.log removed

//
if (!window.toggleCategoriaView) {
    window.toggleCategoriaView = function(viewType) {
        console.log('⚡ toggleCategoriaView llamada (versión temprana):', viewType);
        //
        // pero garantiza que exista desde el inicio
    };
}

if (!window.showCategoriaActionMenu) {
    window.showCategoriaActionMenu = function(button, id, nombre) {
        console.log('⚡ showCategoriaActionMenu llamada (versión temprana):', id);
    };
}

if (!window.closeCategoriasFloatingActions) {
    window.closeCategoriasFloatingActions = function() {
        console.log('⚡ closeCategoriasFloatingActions llamada (versión temprana)');
    };
}

// console.log removed
console.log('toggleCategoriaView exists:', typeof window.toggleCategoriaView);
console.log('showCategoriaActionMenu exists:', typeof window.showCategoriaActionMenu);
</script>

<script>
// ============ CONFIGURACIÓN ============

// Esperar a que AppConfig esté disponible y luego inicializar CONFIG
function initializeConfig() {
    if (typeof AppConfig !== 'undefined') {
        window.CONFIG = {
            apiUrl: AppConfig.getApiUrl('CategoryController.php')
        };
    } else {
        // Fallback si config.js no está cargado
        window.CONFIG = {
            apiUrl: '/fashion-master/app/controllers/CategoryController.php'
        };
    }
}

// Inicializar inmediatamente o esperar a que el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeConfig);
} else {
    initializeConfig();
}

// Variables globales
let isLoading = false;
let categorias = [];

// Variables de paginacion
let currentPage = 1;
let totalPages = 1;

// Variable para tracking de vista actual (tabla o grid)
window.categorias_currentView = 'table'; // Por defecto tabla

// Variable global para fechas de categoroas (para Flatpickr)
window.categoriasDatesArray = [];

// ============ MOBILE FILTERS MODAL FUNCTIONS ============

// Mostrar/ocultar boton de filtros movil basado en el tamaoo de pantalla
function toggleMobileFilterButton() {
    const mobileFilterBtn = document.querySelector('.mobile-filter-btn-categorias');
    const isMobile = window.innerWidth <= 768;
    
    console.log('?? toggleMobileFilterButton - isMobile:', isMobile, 'width:', window.innerWidth);
    
    if (mobileFilterBtn) {
        if (isMobile) {
            mobileFilterBtn.style.display = 'flex';
            console.log('? Boton flotante MOSTRADO');
        } else {
            mobileFilterBtn.style.display = 'none';
            console.log('? Boton flotante OCULTO');
        }
    } else {
        console.error('? Boton flotante no encontrado en DOM');
    }
}

// Abrir/cerrar modal de filtros
function toggleFiltersModalCategorias() {
    console.log('?? toggleFiltersModalCategorias llamado');
    const modal = document.getElementById('filters-modal-categorias');
    if (modal) {
        modal.classList.toggle('active');
        console.log('?? Modal active:', modal.classList.contains('active'));
        if (modal.classList.contains('active')) {
            document.body.style.overflow = 'hidden';
            syncFiltersToModalCategorias();
        } else {
            document.body.style.overflow = '';
        }
    } else {
        console.error('? Modal no encontrado');
    }
}
window.toggleFiltersModalCategorias = toggleFiltersModalCategorias;

function closeFiltersModalCategorias() {
    const modal = document.getElementById('filters-modal-categorias');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}
window.closeFiltersModalCategorias = closeFiltersModalCategorias;

function closeFiltersModalCategoriasOnOverlay(event) {
    if (event.target.id === 'filters-modal-categorias') {
        closeFiltersModalCategorias();
    }
}
window.closeFiltersModalCategoriasOnOverlay = closeFiltersModalCategoriasOnOverlay;

// Sincronizar filtros del desktop al modal
function syncFiltersToModalCategorias() {
    // Sincronizar bosqueda
    const searchDesktop = document.getElementById('search-categorias');
    const searchModal = document.getElementById('modal-search-categorias');
    if (searchDesktop && searchModal) {
        searchModal.value = searchDesktop.value;
    }
    
    // Sincronizar filtros
    const categoryDesktop = document.getElementById('filter-category');
    const statusDesktop = document.getElementById('filter-status');
    const stockDesktop = document.getElementById('filter-stock');
    const fechaDesktop = document.getElementById('filter-fecha');
    
    const categoryModal = document.getElementById('modal-filter-category');
    const statusModal = document.getElementById('modal-filter-status');
    const stockModal = document.getElementById('modal-filter-stock');
    const fechaModal = document.getElementById('modal-filter-fecha');
    
    if (categoryDesktop && categoryModal) categoryModal.value = categoryDesktop.value;
    if (statusDesktop && statusModal) statusModal.value = statusDesktop.value;
    if (stockDesktop && stockModal) stockModal.value = stockDesktop.value;
    if (fechaDesktop && fechaModal) fechaModal.value = fechaDesktop.value;
}

// Sincronizar filtros del modal al desktop
function syncFiltersFromModalCategorias() {
    // Sincronizar bosqueda
    const searchDesktop = document.getElementById('search-categorias');
    const searchModal = document.getElementById('modal-search-categorias');
    if (searchDesktop && searchModal) {
        searchDesktop.value = searchModal.value;
    }
    
    // Sincronizar filtros
    const categoryDesktop = document.getElementById('filter-category');
    const statusDesktop = document.getElementById('filter-status');
    const stockDesktop = document.getElementById('filter-stock');
    const fechaDesktop = document.getElementById('filter-fecha');
    
    const categoryModal = document.getElementById('modal-filter-category');
    const statusModal = document.getElementById('modal-filter-status');
    const stockModal = document.getElementById('modal-filter-stock');
    const fechaModal = document.getElementById('modal-filter-fecha');
    
    if (categoryDesktop && categoryModal) categoryDesktop.value = categoryModal.value;
    if (statusDesktop && statusModal) statusDesktop.value = statusModal.value;
    if (stockDesktop && stockModal) stockDesktop.value = stockModal.value;
    if (fechaDesktop && fechaModal) fechaDesktop.value = fechaModal.value;
}

// Manejar bosqueda desde el modal
function handleModalSearchInputCategorias() {
    const searchValue = document.getElementById('modal-search-categorias').value;
    const searchDesktop = document.getElementById('search-categorias');
    if (searchDesktop) {
        searchDesktop.value = searchValue;
    }
    handleSearchInputCategorias();
}
window.handleModalSearchInputCategorias = handleModalSearchInputCategorias;

// Limpiar bosqueda desde el modal
function clearModalSearchCategorias() {
    document.getElementById('modal-search-categorias').value = '';
    if (typeof $ !== 'undefined') {
        $('#search-categorias').val('');
    }
    clearCategoriaSearch();
}
window.clearModalSearchCategorias = clearModalSearchCategorias;

// Filtrar categoroas desde el modal
function filterCategoriasFromModal() {
    syncFiltersFromModalCategorias();
    filterCategorias();
}
window.filterCategoriasFromModal = filterCategoriasFromModal;

// Limpiar todos los filtros desde el modal
function clearModalFiltersCategorias() {
    console.log('?? Limpiando filtros del modal');
    
    // Limpiar bosqueda
    const modalSearch = document.getElementById('modal-search-categorias');
    if (modalSearch) modalSearch.value = '';
    
    // Limpiar selects
    const modalCategory = document.getElementById('modal-filter-category');
    if (modalCategory) modalCategory.value = '';
    
    const modalStatus = document.getElementById('modal-filter-status');
    if (modalStatus) modalStatus.value = '';
    
    const modalStock = document.getElementById('modal-filter-stock');
    if (modalStock) modalStock.value = '';
    
    // Limpiar fecha (boton + hidden input)
    const modalFechaValue = document.getElementById('modal-filter-fecha-value');
    const modalFechaText = document.getElementById('modal-filter-fecha-text');
    
    if (modalFechaValue) modalFechaValue.value = '';
    if (modalFechaText) {
        modalFechaText.innerHTML = '<i class="fas fa-calendar-alt"></i> Seleccionar fechas';
    }
    
    // Limpiar Flatpickr modal
    if (window.categoriasDatePickerModal) {
        window.categoriasDatePickerModal.clear();
    }
    
    // Sincronizar con desktop
    const desktopSearch = document.getElementById('search-categorias');
    if (desktopSearch) desktopSearch.value = '';
    
    const desktopCategory = document.getElementById('filter-category');
    if (desktopCategory) desktopCategory.value = '';
    
    const desktopStatus = document.getElementById('filter-status');
    if (desktopStatus) desktopStatus.value = '';
    
    const desktopStock = document.getElementById('filter-stock');
    if (desktopStock) desktopStock.value = '';
    
    const desktopFechaValue = document.getElementById('filter-fecha-value');
    const desktopFechaText = document.getElementById('filter-fecha-text');
    
    if (desktopFechaValue) desktopFechaValue.value = '';
    if (desktopFechaText) {
        desktopFechaText.innerHTML = '<i class="fas fa-calendar-alt"></i> Seleccionar fechas';
    }
    
    // Limpiar Flatpickr desktop
    if (window.categoriasDatePicker) {
        window.categoriasDatePicker.clear();
    }
    
    // Recargar categoroas sin filtros
    loadCategorias();
    
    console.log('? Filtros limpiados');
}
window.clearModalFiltersCategorias = clearModalFiltersCategorias;

// Cargar opciones de filtros en el modal
function loadModalFilterOptionsCategorias() {
    if (typeof $ === 'undefined') return;
    
    // Copiar opciones de categoroas
    const categoryOptions = $('#filter-category').html();
    $('#modal-filter-category').html(categoryOptions);
    
    // Copiar opciones de fecha
    const fechaOptions = $('#filter-fecha').html();
    $('#modal-filter-fecha').html(fechaOptions);
}

// Inicializar en carga de pogina
document.addEventListener('DOMContentLoaded', function() {
    console.log('?? DOMContentLoaded - Inicializando modal de filtros');
    
    toggleMobileFilterButton();
    
    // Escuchar cambios de tamaoo de ventana
    window.addEventListener('resize', toggleMobileFilterButton);
    
    // Cargar opciones de filtros en el modal cuando se carguen en desktop
    setTimeout(loadModalFilterOptionsCategorias, 1000);
});

// ============ END MOBILE FILTERS MODAL FUNCTIONS ============

// Funcion para obtener la URL correcta de la imagen del categoroa
function getCategoriaImageUrl(categoroa, forceCacheBust = false) {
    // Priorizar url_imagen_categoria, luego imagen_categoria
    let imageUrl = '';
    
    if (categoroa.url_imagen_categoria) {
        // Verificar que no sea una URL de placeholder
        if (categoroa.url_imagen_categoria.includes('placeholder') || 
            categoroa.url_imagen_categoria.includes('via.placeholder')) {
            imageUrl = (typeof AppConfig !== 'undefined') ? AppConfig.getImageUrl('default-category.jpg') : '/fashion-master/public/assets/img/default-category.jpg';
        } else {
            imageUrl = categoroa.url_imagen_categoria;
        }
    } else if (categoroa.imagen_categoria) {
        // Si es un nombre de archivo local, construir la ruta completa
        if (!categoroa.imagen_categoria.startsWith('http')) {
            imageUrl = (typeof AppConfig !== 'undefined') ? AppConfig.getImageUrl('categories/' + categoroa.imagen_categoria) : '/fashion-master/public/assets/img/categories/' + categoroa.imagen_categoria;
        } else {
            imageUrl = categoroa.imagen_categoria;
        }
    } else {
        imageUrl = (typeof AppConfig !== 'undefined') ? AppConfig.getImageUrl('default-category.jpg') : '/fashion-master/public/assets/img/default-category.jpg';
    }
    
    // Agregar cache-busting solo si se solicita explocitamente
    if (forceCacheBust) {
        const cacheBuster = '?v=' + Date.now();
        return imageUrl + cacheBuster;
    }
    
    return imageUrl;
}

// Funcion auxiliar para mostrar loading en bosqueda
function showSearchLoading() {
    const tbody = document.getElementById('categorias-table-body');
    if (tbody) {
        tbody.innerHTML = `
            <tr class="loading-row">
                <td colspan="8" class="loading-cell">
                    <div class="loading-content">
                        <div class="spinner"></div>
                        <span>Buscando categoroas...</span>
                    </div>
                </td>
            </tr>
        `;
    }
    
    // Tambion mostrar loading en vista grid
    const gridContainer = document.querySelector('.categorias-grid');
    if (gridContainer) {
        gridContainer.innerHTML = `
            <div class="no-products-message">
                <div class="spinner"></div>
                <p>Buscando categoroas...</p>
            </div>
        `;
    }
}

// Funcion principal para cargar categoroas con efectos visuales (DEFINICIoN TEMPRANA)
async function loadCategorias(forceCacheBust = false, preserveState = null) {

    // Tambion crear un alias para compatibilidad
    window.loadCategorias = loadCategorias;
    window.loadCategorias = loadCategorias; // Asegurar que esto disponible globalmente
    
    isLoading = true;
    
    try {
        // Mostrar loading mejorado
        showSearchLoading();
        
        // Usar estado preservado si esto disponible
        if (preserveState) {
            currentPage = preserveState.page || currentPage;
            
            // Restaurar filtros si eston disponibles
            if (preserveState.searchTerm && typeof $ !== 'undefined') {
                $('#search-categorias').val(preserveState.searchTerm);
            }
            
        }
        
        // Construir URL con parometros
        const params = new URLSearchParams({
            action: 'list',
            page: currentPage,
            limit: 10
        });
        
        // Agregar filtros si existen
        if (typeof $ !== 'undefined') {
            const search = $('#search-categorias').val();
            if (search) params.append('search', search);
            
            const category = $('#filter-category').val();
            if (category) params.append('category', category);
            
            const status = $('#filter-status').val();
            if (status !== '') params.append('status', status);
            
            const stock = $('#filter-stock').val();
            if (stock) params.append('stock_filter', stock);
            
            // Usar el hidden input para la fecha
            const fecha = $('#filter-fecha-value').val();
            if (fecha) params.append('fecha', fecha);
        } else {
            // Fallback vanilla JS
            const searchInput = document.getElementById('search-categorias');
            if (searchInput && searchInput.value) {
                params.append('search', searchInput.value);
            }
            
            const categorySelect = document.getElementById('filter-category');
            if (categorySelect && categorySelect.value) {
                params.append('category', categorySelect.value);
            }
            
            const statusSelect = document.getElementById('filter-status');
            if (statusSelect && statusSelect.value !== '') {
                params.append('status', statusSelect.value);
            }
            
            const stockSelect = document.getElementById('filter-stock');
            if (stockSelect && stockSelect.value) {
                params.append('stock_filter', stockSelect.value);
            }
            
            // Usar el hidden input para la fecha
            const fechaValue = document.getElementById('filter-fecha-value');
            if (fechaValue && fechaValue.value) {
                params.append('fecha', fechaValue.value);
            }
        }
        
        const finalUrl = `${CONFIG.apiUrl}?${params}`;
        
        const response = await fetch(finalUrl, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            cache: 'no-cache'
        });    
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status} - ${response.statusText}`);
        }
        
        // Obtener texto crudo
        const responseText = await response.text();
        
        // Parsear JSON de forma segura
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (jsonError) {
            
            throw new Error('Respuesta del servidor no es JSON volido');
        }
        
        if (!data.success) {
            throw new Error(data.error || 'Error desconocido del servidor');
        }
        
        const categorias = data.data || [];
        
        displayCategorias(categorias, forceCacheBust, preserveState);
        updateStats(data.pagination);
        updatePaginationInfo(data.pagination);
        
        // Cargar fechas onicas en el filtro
        loadCategoriaDates(categorias);
        
        // Actualizar contador de resultados
        if (data.pagination) {
            updateResultsCounter(categorias.length, data.pagination.total_items);
        }
        
        // Destacar categoroa recion actualizado/creado si esto especificado
        // PRESERVAR ESTADO - sin destacado visual para evitar bugs
        if (preserveState) {
            // Restaurar posicion de scroll sin animaciones que causen problemas
            if (preserveState.scrollPosition && typeof restoreScrollPosition === 'function') {
                restoreScrollPosition(preserveState.scrollPosition);
            }
        }
        
    } catch (error) {
        const tbody = document.getElementById('categorias-table-body');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="loading-cell">
                        <div class="loading-content error">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Error: ${error.message}</span>
                            <button onclick="loadCategorias()" class="btn-modern btn-primary">
                                <i class="fas fa-redo"></i> Reintentar
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }
        
        // Tambion mostrar error en vista grid
        const gridContainer = document.querySelector('.categorias-grid');
        if (gridContainer) {
            gridContainer.innerHTML = `
                <div class="no-products-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Error: ${error.message}</p>
                    <button onclick="loadCategorias()" class="btn-modern btn-primary">
                        <i class="fas fa-redo"></i> Reintentar
                    </button>
                </div>
            `;
        }
    } finally {
        isLoading = false;
        
        // Loading overlay eliminado
    }
}

// Asegurar que las funciones eston disponibles globalmente inmediatamente
window.loadCategorias = loadCategorias;
window.loadCategorias = loadCategorias;

// Funcion para cargar categoroas en el filtro
async function loadTiposCategoria() {
    try {
        const url = `${CONFIG.apiUrl}?action=get_categories`;
        
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            cache: 'no-cache'
        });
                
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status} - ${response.statusText}`);
        }
        
        // Obtener texto crudo primero
        const responseText = await response.text();
        
        // Parsear JSON de forma segura
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (jsonError) {
            console.error('? Error al parsear JSON de categoroas:', jsonError);
            throw new Error('Respuesta del servidor no es JSON volido');
        }
        
        if (data.success && data.data) {
            const categorySelect = document.getElementById('filter-category');
            if (categorySelect) {
                // Limpiar opciones existentes excepto "Todos los tipos"
                categorySelect.innerHTML = '<option value="">Todos los tipos</option>';
                
                // Agregar categoroas
                data.data.forEach(categoria => {
                    const option = document.createElement('option');
                    option.value = categoria.id_categoria;
                    option.textContent = categoria.nombre_categoria;
                    categorySelect.appendChild(option);
                });
                
                // Sincronizar con el modal
                loadModalFilterOptionsCategorias();
            }
        }
    } catch (error) {
        console.error('? Error cargando categoroas:', error);
    }
}

window.loadTiposCategoria = loadTiposCategoria;

// Funcion para cargar fechas onicas de categoroas en el filtro
function loadCategoriaDates(products) {
    try {
        const fechaSelect = document.getElementById('filter-fecha');
        if (!fechaSelect || !products || products.length === 0) return;
        
        // Extraer fechas onicas (formato YYYY-MM-DD)
        const fechasSet = new Set();
        products.forEach(categoroa => {
            if (categoroa.fecha_creacion_categoroa) {
                // Extraer solo la parte de la fecha (YYYY-MM-DD)
                const fecha = categoroa.fecha_creacion_categoroa.split(' ')[0];
                fechasSet.add(fecha);
            }
        });
        
        // Convertir a array y ordenar de mos reciente a mos antigua
        const fechasUnicas = Array.from(fechasSet).sort((a, b) => b.localeCompare(a));
        
        // Guardar fechas en variable global para Flatpickr
        window.categoriasDatesArray = fechasUnicas;
        console.log('?? Fechas de categoroas guardadas:', window.categoriasDatesArray);
        
        // NO actualizar 'enable' - permitir seleccionar cualquier fecha para rangos
        // Solo redibujar para actualizar los estilos visuales
        if (window.categoriasDatePicker) {
            window.categoriasDatePicker.redraw();
            console.log('? Flatpickr desktop redibujado con', fechasUnicas.length, 'fechas marcadas');
        }
        
        if (window.categoriasDatePickerModal) {
            window.categoriasDatePickerModal.redraw();
            console.log('? Flatpickr modal redibujado con', fechasUnicas.length, 'fechas marcadas');
        }
        
        // Guardar opcion seleccionada actual
        const valorActual = fechaSelect.value;
        
        // Solo actualizar SELECT si es SELECT (no INPUT de Flatpickr)
        if (fechaSelect.tagName === 'SELECT') {
            // Limpiar y agregar opcion predeterminada
            fechaSelect.innerHTML = '<option value="">Todas las fechas</option>';
            
            // Agregar opciones de fechas
            fechasUnicas.forEach(fecha => {
                const option = document.createElement('option');
                option.value = fecha;
                // Formatear fecha para mostrar (DD/MM/YYYY)
                const [year, month, day] = fecha.split('-');
                option.textContent = `${day}/${month}/${year}`;
                fechaSelect.appendChild(option);
            });
            
            // Restaurar seleccion si existoa
            if (valorActual && fechasUnicas.includes(valorActual)) {
                fechaSelect.value = valorActual;
            }
            
            // Sincronizar con el modal
            loadModalFilterOptionsCategorias();
        }
    } catch (error) {
        console.error('? Error cargando fechas:', error);
    }
}

// Funcion para mostrar categoroas en tabla
function displayCategorias(categorias, forceCacheBust = false, preserveState = null) {
    // FORZAR vista grid en movil SIEMPRE
    const isMobile = window.innerWidth <= 768;
    
    if (isMobile) {
        console.log('?? Movil detectado en displayCategorias, usando grid');
        displayCategoriasGrid(categorias);
        return;
    }
    
    // En desktop, usar la vista actual
    const currentView = getCurrentView();
    if (currentView === 'grid') {
        // Si esto en vista grid, actualizar grid
        displayCategoriasGrid(categorias);
        return;
    }
    
    // Si esto en vista tabla, actualizar tabla
    const tbody = document.getElementById('categorias-table-body');
    
    if (!categorias || categorias.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="loading-cell">
                    <div class="loading-content no-data">
                        <i class="fas fa-folder-open"></i>
                        <span>No se encontraron categoroas</span>
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = categorias.map((categoria, index) => {
        // Obtener la URL de la imagen
        const imageUrl = getCategoriaImageUrl(categoria, forceCacheBust);
        
        // Determinar el estado
        const estadoTexto = categoria.estado_categoria === 'activo' || categoria.estado === 'activo' ? 'Activo' : 'Inactivo';
        const estadoClass = categoria.estado_categoria === 'activo' || categoria.estado === 'activo' ? 'status-active' : 'status-inactive';
        
        return `
        <tr oncontextmenu="return false;" ondblclick="editCategoria(${categoria.id_categoria})" style="cursor: pointer;" data-categoria-id="${categoria.id_categoria}">
            <td>${categoria.id_categoria}</td>
            <td onclick="event.stopPropagation();" ondblclick="event.stopPropagation(); showImageFullSize('${imageUrl}', '${(categoria.nombre_categoria || '').replace(/'/g, "\\'")}');" style="cursor: zoom-in;">
                <div class="categoria-image-small">
                    <img src="${imageUrl}" 
                         alt="${categoria.nombre_categoria || 'Categoroa'}" 
                         onerror="this.src='${AppConfig ? AppConfig.getImageUrl('default-category.jpg') : '/fashion-master/public/assets/img/default-category.jpg'}'; this.onerror=null;">
                </div>
            </td>
            <td>
                <div class="product-info">
                    <strong>${categoria.nombre_categoria || 'Sin nombre'}</strong>
                </div>
            </td>
            <td>
                <code>${categoria.codigo_categoria || 'N/A'}</code>
            </td>
            <td>
                <span class="categoria-descripcion">${categoria.descripcion_categoria || 'Sin descripcion'}</span>
            </td>
            <td>
                <span class="status-badge ${estadoClass}">
                    ${estadoTexto}
                </span>
            </td>
            <td>${categoria.fecha_creacion_formato || categoria.fecha_registro || 'N/A'}</td>
            <td>
                <div class="action-buttons">
                    <button class="btn-action btn-menu" onclick="event.stopPropagation(); showCategoriaActionMenu(${categoria.id_categoria}, '${(categoria.nombre_categoria || '').replace(/'/g, "\\'")}', '${categoria.estado_categoria || categoria.estado}', event)" title="Acciones">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                </div>
            </td>
        </tr>`;
    }).join('');
}

// Funcion para obtener clase de stock
function getStockClass(categoroa) {
    const stock = parseInt(categoroa.stock_actual_categoroa) || 0;
    const stockMinimo = parseInt(categoroa.stock_minimo_categoroa); // Tomar valor directo de BD
    const stockMaximo = parseInt(categoroa.stock_maximo_categoroa); // Tomar valor directo de BD
    
    if (stock === 0) return 'sin-productos';
    if (stockMinimo && stock <= stockMinimo) return 'pocos-productos';
    return 'con-productos'; // Verde para stock > stock_minimo
}

// Funcion para actualizar estadosticas
function updateStats(pagination) {
    if (pagination) {
        const { current_page, total_pages, total_items, items_per_page } = pagination;
        const start = ((current_page - 1) * items_per_page) + 1;
        const end = Math.min(current_page * items_per_page, total_items);
        
        const showingStartEl = document.getElementById('showing-start-products');
        const showingEndEl = document.getElementById('showing-end-categorias');
        const totalProductsEl = document.getElementById('total-categorias');
        
        if (showingStartEl) showingStartEl.textContent = total_items > 0 ? start : 0;
        if (showingEndEl) showingEndEl.textContent = total_items > 0 ? end : 0;
        if (totalProductsEl) totalProductsEl.textContent = total_items;
    }
}

// Funcion para actualizar informacion de paginacion
function updatePaginationInfo(pagination) {
    if (pagination) {
        currentPage = pagination.current_page || 1;
        totalPages = pagination.total_pages || 1;
        
        // Actualizar elementos de paginacion si existen
        const currentPageEl = document.getElementById('current-page-categorias');
        const totalPagesEl = document.getElementById('total-pages-categorias');
        
        if (currentPageEl) currentPageEl.textContent = currentPage;
        if (totalPagesEl) totalPagesEl.textContent = totalPages;
        
        // Actualizar botones de paginacion si existen
        const firstBtn = document.querySelector('[onclick="goToFirstPageCategorias()"]');
        const prevBtn = document.querySelector('[onclick="previousPageCategorias()"]');
        const nextBtn = document.querySelector('[onclick="nextPageCategorias()"]');
        const lastBtn = document.querySelector('[onclick="goToLastPageCategorias()"]');
        
        if (firstBtn) firstBtn.disabled = currentPage <= 1;
        if (prevBtn) prevBtn.disabled = currentPage <= 1;
        if (nextBtn) nextBtn.disabled = currentPage >= totalPages;
        if (lastBtn) lastBtn.disabled = currentPage >= totalPages;
    }
}

// Funcion de filtrado mejorada con jQuery
function filterCategorias() {
    if (typeof $ === 'undefined') {
        return filterCategoriasVanilla();
    }
    
    const search = $('#search-categorias').val() || '';
    const category = $('#filter-category').val() || '';
    const status = $('#filter-status').val() || '';
    const stock = $('#filter-stock').val() || '';
    
    // Mostrar indicador de carga
    showSearchLoading();
    
    // Reset pogina actual
    currentPage = 1;
    
    // Recargar categoroas con filtros
    loadCategorias();
}

// Funcion de filtrado con vanilla JS como fallback
function filterCategoriasVanilla() {
    const searchInput = document.getElementById('search-categorias');
    const categorySelect = document.getElementById('filter-category');
    const statusSelect = document.getElementById('filter-status');
    const stockSelect = document.getElementById('filter-stock');
    
    const search = searchInput ? searchInput.value || '' : '';
    const category = categorySelect ? categorySelect.value || '' : '';
    const status = statusSelect ? statusSelect.value || '' : '';
    const stock = stockSelect ? stockSelect.value || '' : '';
    
    // Mostrar indicador de carga
    showSearchLoading();
    
    // Reset pogina actual
    currentPage = 1;
    
    // Recargar categoroas con filtros
    loadCategorias();
}

// Funcion para manejar bosqueda en tiempo real con jQuery
let searchTimeout;
function handleSearchInputCategorias() {
    clearTimeout(searchTimeout);
    
    // Mostrar indicador visual de bosqueda
    if (typeof $ !== 'undefined') {
        const searchIcon = $('.search-icon');
        if (searchIcon.length) {
            searchIcon.removeClass('fa-search').addClass('fa-spinner fa-spin');
        }
        
        searchTimeout = setTimeout(() => {
            // Restaurar icono
            if (searchIcon.length) {
                searchIcon.removeClass('fa-spinner fa-spin').addClass('fa-search');
            }
            filterCategorias();
        }, 300); // Reducido para mejor responsividad
    } else {
        // Fallback vanilla JS
        const searchInput = document.getElementById('search-categorias');
        const searchIcon = searchInput?.parentElement?.querySelector('.search-icon');
        
        if (searchIcon) {
            searchIcon.classList.remove('fa-search');
            searchIcon.classList.add('fa-spinner', 'fa-spin');
        }
        
        searchTimeout = setTimeout(() => {
            if (searchIcon) {
                searchIcon.classList.remove('fa-spinner', 'fa-spin');
                searchIcon.classList.add('fa-search');
            }
            filterCategorias();
        }, 300);
    }
}

// Funcion para cambiar vista (tabla/grid)
function toggleCategoriaView(viewType) {
    console.log('?? Cambiando vista a:', viewType);
    
    // BLOQUEAR cambio a tabla en movil
    const isMobile = window.innerWidth <= 768;
    if (isMobile && viewType === 'table') {
        console.warn('? Vista tabla bloqueada en movil');
        return; // No permitir cambio
    }
    
    // CERRAR BURBUJA DE STOCK si esto abierta (evita que quede con coordenadas incorrectas)
    closeCantidadBubble();
    
    // CERRAR MENoS FLOTANTES si eston abiertos
    if (categorias_activeFloatingContainer) {
        closeCategoriasFloatingActions();
    }
    
    const tableContainer = document.querySelector('.data-table-wrapper');
    const gridContainer = document.querySelector('.categorias-grid');
    const viewButtons = document.querySelectorAll('.view-btn');
    
    // Si no existe el grid, crearlo
    if (!gridContainer) {
        createGridView();
    }
    
    viewButtons.forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.view === viewType) {
            btn.classList.add('active');
        }
    });
    
    if (viewType === 'grid') {
        tableContainer.style.display = 'none';
        document.querySelector('.categorias-grid').style.display = 'grid';
        window.categorias_currentView = 'grid';
        // Recargar categoroas para asegurar datos actualizados
        loadCategorias();
    } else {
        tableContainer.style.display = 'block';
        document.querySelector('.categorias-grid').style.display = 'none';
        window.categorias_currentView = 'table';
        // Recargar categoroas para asegurar datos actualizados
        loadCategorias();
    }
}

// Funcion para crear vista grid
function createGridView() {
    const gridContainer = document.createElement('div');
    gridContainer.className = 'categorias-grid';
    gridContainer.style.display = 'none';
    
    // Insertar despuos de la tabla
    const tableWrapper = document.querySelector('.data-table-wrapper');
    tableWrapper.parentNode.insertBefore(gridContainer, tableWrapper.nextSibling);
}

// Funcion para mostrar categoroas en grid
function displayCategoriasGrid(categorias) {
    const gridContainer = document.querySelector('.categorias-grid');
    if (!gridContainer) return;
    
    if (!categorias || categorias.length === 0) {
        gridContainer.innerHTML = `
            <div class="no-products-message">
                <i class="fas fa-folder-open"></i>
                <p>No se encontraron categoroas</p>
            </div>
        `;
        return;
    }
    
    // Detectar si es movil
    const isMobile = window.innerWidth <= 768;
    
    gridContainer.innerHTML = categorias.map(categoria => {
        // Obtener la URL de la imagen
        const imageUrl = getCategoriaImageUrl(categoria);
        const hasImage = imageUrl && !imageUrl.includes('default-category.jpg') && !imageUrl.includes('default-product.jpg');
        
        // Determinar el estado
        const estadoTexto = categoria.estado_categoria === 'activo' || categoria.estado === 'activo' ? 'Activo' : 'Inactivo';
        const estadoClass = categoria.estado_categoria === 'activo' || categoria.estado === 'activo' ? 'active' : 'inactive';
        
        const imageHTML = `
            <div class="categoria-card-image-mobile ${hasImage ? '' : 'no-image'}">
                ${hasImage 
                    ? `<img src="${imageUrl}" alt="${categoria.nombre_categoria || 'Categoroa'}" onerror="this.parentElement.classList.add('no-image'); this.style.display='none'; this.parentElement.innerHTML='<i class=\\'fas fa-image\\'></i>';">` 
                    : '<i class="fas fa-image"></i>'}
            </div>
        `;
        
        return `
            <div class="categoria-card" ondblclick="editCategoria(${categoria.id_categoria})" style="cursor: pointer;" data-categoria-id="${categoria.id_categoria}">
                ${imageHTML}
                <div class="categoria-card-header">
                    <h3 class="categoria-card-title">${categoria.nombre_categoria || 'Sin nombre'}</h3>
                    <span class="categoria-card-status ${estadoClass}">
                        ${estadoTexto}
                    </span>
                </div>
                
                <div class="categoria-card-body">
                    ${categoria.codigo_categoria ? `<div class="categoria-card-sku">Codigo: ${categoria.codigo_categoria}</div>` : ''}
                    
                    <div class="categoria-card-category">
                        <i class="fas fa-align-left"></i> ${categoria.descripcion_categoria || 'Sin descripcion'}
                    </div>
                    
                    <div class="categoria-card-price">
                        <i class="fas fa-calendar"></i>
                        ${categoria.fecha_creacion_formato || categoria.fecha_registro || 'N/A'}
                    </div>
                </div>
                
                <div class="categoria-card-actions">
                    <button class="categoria-card-btn btn-view" onclick="event.stopPropagation(); verCategoria(${categoria.id_categoria})" title="Ver categoroa" style="background-color: #1a73e8 !important; color: white !important; border: none !important; box-shadow: 0 4px 8px rgba(26, 115, 232, 0.3) !important;">
                        <i class="fas fa-eye" style="color: white !important;"></i>
                    </button>
                    <button class="categoria-card-btn btn-edit" onclick="event.stopPropagation(); editCategoria(${categoria.id_categoria})" title="Editar categoroa" style="background-color: #34a853 !important; color: white !important; border: none !important; box-shadow: 0 4px 8px rgba(52, 168, 83, 0.3) !important;">
                        <i class="fas fa-edit" style="color: white !important;"></i>
                    </button>
                    <button class="categoria-card-btn ${estadoClass === 'active' ? 'btn-deactivate' : 'btn-activate'}" 
                            onclick="event.stopPropagation(); changeCategoriaEstado(${categoria.id_categoria})" 
                            title="${estadoTexto === 'Activo' ? 'Desactivar' : 'Activar'} categoroa"
                            style="background-color: #6f42c1 !important; color: white !important; border: none !important;">
                        <i class="fas fa-${estadoTexto === 'Activo' ? 'power-off' : 'toggle-on'}" style="color: white !important;"></i>
                    </button>
                    <button class="categoria-card-btn btn-delete" onclick="event.stopPropagation(); deleteCategoria(${categoria.id_categoria}, '${(categoria.nombre_categoria || 'Categoroa').replace(/'/g, "\\'")}');" title="Eliminar categoroa" style="background-color: #f44336 !important; color: white !important; border: none !important; box-shadow: 0 4px 8px rgba(244, 67, 54, 0.3) !important;">
                        <i class="fas fa-trash" style="color: white !important;"></i>
                    </button>
                </div>
            </div>
        `;
    }).join('');
}

// Funcion para aplicar Masonry layout (DESACTIVADA - causaba problemas de espacio vacoo)
function applyMasonryLayout() {
    // Desactivada - se usa grid normal ahora
    return;
    
    const gridContainer = document.querySelector('.categorias-grid');
    if (!gridContainer || window.innerWidth > 768) return;
    
    // Esperar a que las imogenes se carguen
    const images = gridContainer.querySelectorAll('img');
    let loadedImages = 0;
    const totalImages = images.length;
    
    const positionCards = () => {
        const cards = gridContainer.querySelectorAll('.categoria-card');
        cards.forEach(card => {
            const height = card.offsetHeight;
            const rowSpan = Math.ceil((height + 10) / 8); // 10 es el gap, 8 es grid-auto-rows
            card.style.gridRowEnd = `span ${rowSpan}`;
        });
    };
    
    if (totalImages === 0) {
        // Si no hay imogenes, aplicar inmediatamente
        setTimeout(positionCards, 50);
    } else {
        // Esperar a que las imogenes se carguen
        images.forEach(img => {
            if (img.complete) {
                loadedImages++;
            } else {
                img.addEventListener('load', () => {
                    loadedImages++;
                    if (loadedImages === totalImages) {
                        positionCards();
                    }
                });
                img.addEventListener('error', () => {
                    loadedImages++;
                    if (loadedImages === totalImages) {
                        positionCards();
                    }
                });
            }
        });
        
        // Si todas ya eston cargadas
        if (loadedImages === totalImages) {
            positionCards();
        }
    }
    
    // Reajustar en cambios de tamaoo
    let resizeTimeout;
    window.addEventListener('resize', () => {
        if (window.innerWidth <= 768) {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(positionCards, 100);
        }
    });
}

// ============ FUNCIONES PRINCIPALES CATEGORoAS ============

// ===================================
// SISTEMA DE BOTONES FLOTANTES ANIMADOS - VERSIoN AVANZADA
// ===================================

// Variables globales para el sistema flotante
let categorias_activeFloatingContainer = null;
let categorias_activeCategoriaId = null;
let categorias_isAnimating = false;
let categorias_animationTimeout = null;
let categorias_floatingButtons = [];
let categorias_centerButton = null;

// Funcion principal para mostrar botones flotantes
function showCategoriaActionMenu(categoriaId, categoriaNombre, estado, event) {
    // CERRAR BURBUJA DE STOCK SI ESTo ABIERTA
    const existingBubbles = document.querySelectorAll('.stock-update-bubble');
    existingBubbles.forEach(bubble => {
        if (bubble && bubble.parentNode) {
            // Animacion de salida
            bubble.style.transform = 'scale(0)';
            bubble.style.opacity = '0';
            setTimeout(() => {
                if (bubble && bubble.parentNode) {
                    bubble.remove();
                }
            }, 400);
        }
    });
    
    // Eliminar overlay de la burbuja de stock
    const stockOverlays = document.querySelectorAll('.stock-bubble-overlay');
    stockOverlays.forEach(overlay => {
        if (overlay && overlay.parentNode) {
            overlay.remove();
        }
    });
    
    // Prevenir moltiples ejecuciones
    if (categorias_isAnimating) return;
    
    // Si ya esto abierto para la misma categoroa, cerrarlo
    if (categorias_activeFloatingContainer && categorias_activeCategoriaId === categoriaId) {
        closeCategoriasFloatingActions();
        return;
    }
    
    // Cerrar cualquier meno anterior
    if (categorias_activeFloatingContainer) {
        closeCategoriasFloatingActions();
    }
    
    // Obtener el boton que disparo el evento - MEJORADO
    let triggerButton = null;
    
    if (event && event.currentTarget) {
        triggerButton = event.currentTarget;
    } else if (event && event.target) {
        // Buscar el boton padre si el click fue en el icono
        triggerButton = event.target.closest('.btn-menu');
    } else {
        // Fallback robusto: buscar entre todos los .btn-menu y comparar atributo onclick
        const allMenuButtons = document.querySelectorAll('.btn-menu');
        for (const btn of allMenuButtons) {
            const onclickAttr = btn.getAttribute('onclick') || '';
            if (onclickAttr.includes(`showCategoriaActionMenu(${categoriaId}`)) {
                triggerButton = btn;
                break;
            }
        }
    }
    
    if (!triggerButton) {
        return;
    }
    
    categorias_isAnimating = true;
    categorias_activeCategoriaId = categoriaId;
    
    // Crear contenedor flotante con animaciones
    createCategoriaAnimatedFloatingContainer(triggerButton, categoriaId, categoriaNombre, estado);
}

// Crear el contenedor flotante con animaciones avanzadas
function createCategoriaAnimatedFloatingContainer(triggerButton, categoriaId, categoriaNombre, estado) {
    // Limpiar cualquier meno anterior
    if (categorias_activeFloatingContainer) {
        closeCategoriasFloatingActions();
    }
    
    // Verificar que tenemos un trigger button volido
    if (!triggerButton) {
        categorias_isAnimating = false;
        return;
    }
    
    // Crear contenedor principal con ID onico
    categorias_activeFloatingContainer = document.createElement('div');
    categorias_activeFloatingContainer.id = 'animated-floating-menu-' + categoriaId;
    categorias_activeFloatingContainer.className = 'animated-floating-container';
    
    // Asegurar que el contenedor padre tenga position relative
    const tableContainer = document.querySelector('.data-table-wrapper') || 
                          document.querySelector('.module-content') || 
                          document.querySelector('.admin-module');
    
    if (tableContainer) {
        const computedStyle = window.getComputedStyle(tableContainer);
        if (computedStyle.position === 'static') {
            tableContainer.style.position = 'relative';
        }
    }
    
    // Estilos del contenedor principal
    categorias_activeFloatingContainer.style.cssText = `
        position: absolute !important;
        z-index: 999999 !important;
        pointer-events: none !important;
        top: 0 !important;
        left: 0 !important;
        width: auto !important;
        height: auto !important;
        display: block !important;
    `;
    
    // Guardar referencia al trigger button
    categorias_activeFloatingContainer.triggerButton = triggerButton;
    
    // Crear boton central con los tres puntitos
    createCenterButton();
    
    // Definir acciones con colores vibrantes (incluye boton VER para categoroas)
    const actions = [
        { icon: 'fa-eye', color: '#1a73e8', actionFn: () => verCategoria(categoriaId) },
        { icon: 'fa-edit', color: '#34a853', actionFn: () => editCategoria(categoriaId) },
        { icon: estado === 'activo' ? 'fa-power-off' : 'fa-toggle-on', color: '#9c27b0', actionFn: () => changeCategoriaEstado(categoriaId) },
        { icon: 'fa-trash', color: '#f44336', actionFn: () => deleteCategoria(categoriaId, categoriaNombre) }
    ];
    
    // Crear botones flotantes con animaciones
    categorias_floatingButtons = [];
    const radius = 80;
    
    actions.forEach((action, index) => {
        const angle = (index / actions.length) * 2 * Math.PI - Math.PI / 2;
        createAnimatedButton(action, index, angle, radius);
    });
    
    // Agregar al contenedor de la tabla
    if (tableContainer) {
        tableContainer.appendChild(categorias_activeFloatingContainer);
    } else {
        document.body.appendChild(categorias_activeFloatingContainer);
    }
    
    // Actualizar posiciones iniciales
    updateAnimatedButtonPositions();
    
    categorias_activeCategoriaId = categoriaId;
    
    // Event listeners con animaciones
    setupAnimatedEventListeners();
    
    // Iniciar animacion de entrada
    startOpenAnimation();
}

// Crear boton central con tres puntitos (para cerrar)
function createCenterButton() {
    categorias_centerButton = document.createElement('div');
    categorias_centerButton.className = 'animated-center-button';
    categorias_centerButton.innerHTML = '<i class="fas fa-ellipsis-v"></i>';
    
    categorias_centerButton.style.cssText = `
        position: absolute !important;
        width: 45px !important;
        height: 45px !important;
        border-radius: 50% !important;
        background: transparent !important;
        color: white !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        cursor: pointer !important;
        z-index: 1000000 !important;
        font-size: 16px !important;
        box-shadow: none !important;
        pointer-events: auto !important;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
        border: none !important;
        transform: scale(0) rotate(0deg) !important;
        opacity: 0 !important;
    `;
    
    // Efectos hover
    categorias_centerButton.addEventListener('mouseenter', () => {
        categorias_centerButton.style.transform = 'scale(1.15) rotate(180deg)';
        categorias_centerButton.style.boxShadow = '0 6px 25px rgba(102, 126, 234, 0.3)';
        categorias_centerButton.style.background = 'rgba(255, 255, 255, 0.1)';
    });
    
    categorias_centerButton.addEventListener('mouseleave', () => {
        categorias_centerButton.style.transform = 'scale(1) rotate(360deg)';
        categorias_centerButton.style.boxShadow = 'none';
        categorias_centerButton.style.background = 'transparent';
    });
    
    // Click para cerrar
    categorias_centerButton.addEventListener('click', (e) => {
        e.stopPropagation();
        closeCategoriasFloatingActions();
    });
    
    categorias_activeFloatingContainer.appendChild(categorias_centerButton);
}

// Crear boton animado individual
function createAnimatedButton(action, index, angle, radius) {
    const button = document.createElement('div');
    button.innerHTML = `<i class="fas ${action.icon}"></i>`;
    button.dataset.angle = angle;
    button.dataset.radius = radius;
    button.dataset.index = index;
    button.className = 'animated-floating-button';
    
    // Gradientes personalizados para cada color
    const gradients = {
        '#1a73e8': 'linear-gradient(45deg, #1a73e8 0%, #4285f4 100%)',
        '#34a853': 'linear-gradient(45deg, #34a853 0%, #4caf50 100%)',
        '#ff9800': 'linear-gradient(45deg, #ff9800 0%, #ffc107 100%)',
        '#9c27b0': 'linear-gradient(45deg, #9c27b0 0%, #e91e63 100%)',
        '#f44336': 'linear-gradient(45deg, #f44336 0%, #ff5722 100%)'
    };
    
    button.style.cssText = `
        position: absolute !important;
        width: 55px !important;
        height: 55px !important;
        border-radius: 50% !important;
        background: ${gradients[action.color] || action.color} !important;
        color: white !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        cursor: pointer !important;
        z-index: 1000001 !important;
        font-size: 20px !important;
        box-shadow: 0 6px 20px ${action.color}40 !important;
        pointer-events: auto !important;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
        border: none !important;
        transform: scale(0) rotate(-180deg) !important;
        opacity: 0 !important;
        backdrop-filter: blur(10px) !important;
    `;
    
    // Efectos hover avanzados
    button.addEventListener('mouseenter', () => {
        button.style.transform = 'scale(1.2) rotate(15deg)';
        button.style.boxShadow = `0 10px 30px ${action.color}60`;
        button.style.zIndex = '1000003';
        
        // Crear ripple effect
        createRippleEffect(button, action.color);
        
        // Tooltip deshabilitado - solo iconos
        // showTooltip(button, action.label);
    });
    
    button.addEventListener('mouseleave', () => {
        button.style.transform = 'scale(1) rotate(0deg)';
        button.style.boxShadow = `0 6px 20px ${action.color}40`;
        button.style.zIndex = '1000001';
        
        // Ocultar tooltip
        hideTooltip();
    });
    
    // Click handler con animacion
    button.addEventListener('click', (e) => {
        e.stopPropagation();
        
        // Forzar cierre inmediato del meno
        forceCloseCategoriasFloatingActions();
        
        // Animacion de click del boton
        button.style.transform = 'scale(0.9) rotate(180deg)';
        setTimeout(() => {
            button.style.transform = 'scale(1.1) rotate(360deg)';
        }, 100);
        
        // Ejecutar accion despuos de un delay monimo
        setTimeout(() => {
            try {
                action.actionFn();
            } catch (err) {
                console.error('Error ejecutando accion flotante:', err);
            }
        }, 200);
    });
    
    categorias_activeFloatingContainer.appendChild(button);
    categorias_floatingButtons.push(button);
}

// Crear efecto ripple
function createRippleEffect(button, color) {
    const ripple = document.createElement('div');
    ripple.style.cssText = `
        position: absolute !important;
        top: 50% !important;
        left: 50% !important;
        width: 10px !important;
        height: 10px !important;
        background: ${color} !important;
        border-radius: 50% !important;
        transform: translate(-50%, -50%) scale(0) !important;
        opacity: 0.6 !important;
        pointer-events: none !important;
        animation: rippleEffect 0.6s ease-out !important;
        z-index: -1 !important;
    `;
    
    // Agregar CSS de animacion si no existe
    if (!document.querySelector('#ripple-animation-styles')) {
        const styles = document.createElement('style');
        styles.id = 'ripple-animation-styles';
        styles.textContent = `
            @keyframes rippleEffect {
                0% { transform: translate(-50%, -50%) scale(0); opacity: 0.6; }
                100% { transform: translate(-50%, -50%) scale(4); opacity: 0; }
            }
        `;
        document.head.appendChild(styles);
    }
    
    button.appendChild(ripple);
    
    setTimeout(() => {
        if (ripple.parentNode) {
            ripple.remove();
        }
    }, 600);
}

// Mostrar tooltip
function showTooltip(button, text) {
    // Remover tooltip anterior si existe
    hideTooltip();
    
    const tooltip = document.createElement('div');
    tooltip.id = 'floating-tooltip';
    tooltip.textContent = text;
    tooltip.style.cssText = `
        position: absolute !important;
        background: rgba(0, 0, 0, 0.9) !important;
        color: white !important;
        padding: 8px 12px !important;
        border-radius: 6px !important;
        font-size: 14px !important;
        font-weight: 500 !important;
        white-space: nowrap !important;
        z-index: 10004 !important;
        pointer-events: none !important;
        opacity: 0 !important;
        transform: translateY(10px) !important;
        transition: all 0.2s ease !important;
        backdrop-filter: blur(10px) !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
    `;
    
    document.body.appendChild(tooltip);
    
    // Posicionar tooltip
    const buttonRect = button.getBoundingClientRect();
    tooltip.style.left = (buttonRect.left + buttonRect.width / 2 - tooltip.offsetWidth / 2) + 'px';
    tooltip.style.top = (buttonRect.bottom + 10) + 'px';
    
    // Animar entrada
    setTimeout(() => {
        tooltip.style.opacity = '1';
        tooltip.style.transform = 'translateY(0)';
    }, 50);
}

// Ocultar tooltip
function hideTooltip() {
    const tooltip = document.getElementById('floating-tooltip');
    if (tooltip) {
        tooltip.style.opacity = '0';
        tooltip.style.transform = 'translateY(10px)';
        setTimeout(() => {
            if (tooltip.parentNode) {
                tooltip.remove();
            }
        }, 200);
    }
}

// Funcion para actualizar posiciones de botones con animaciones
function updateAnimatedButtonPositions() {
    if (!categorias_activeFloatingContainer) {
        return;
    }
    
    if (!categorias_activeFloatingContainer.triggerButton) {
        return;
    }
    
    // Verificar que el trigger button aon existe en el DOM
    if (!document.contains(categorias_activeFloatingContainer.triggerButton)) {
        closeCategoriasFloatingActions();
        return;
    }
    
    // Obtener el contenedor padre donde eston los botones
    const container = categorias_activeFloatingContainer.parentElement;
    if (!container) {
        return;
    }
    
    // Asegurar que el contenedor tenga position relative
    const containerStyle = window.getComputedStyle(container);
    if (containerStyle.position === 'static') {
        container.style.position = 'relative';
    }
    
    // Obtener posiciones relativas
    const containerRect = container.getBoundingClientRect();
    const triggerRect = categorias_activeFloatingContainer.triggerButton.getBoundingClientRect();
    
    // Calcular posicion relativa del trigger respecto al contenedor
    const centerX = triggerRect.left - containerRect.left + triggerRect.width / 2;
    const centerY = triggerRect.top - containerRect.top + triggerRect.height / 2;
    
    // Ajustar por scroll del contenedor si es necesario
    const scrollLeft = container.scrollLeft || 0;
    const scrollTop = container.scrollTop || 0;
    
    const finalCenterX = centerX + scrollLeft;
    const finalCenterY = centerY + scrollTop;
    
    // Actualizar posicion del boton central
    if (categorias_centerButton) {
        categorias_centerButton.style.left = `${finalCenterX - 22.5}px`;
        categorias_centerButton.style.top = `${finalCenterY - 22.5}px`;
    }
    
    // Actualizar posicion de cada boton flotante
    categorias_floatingButtons.forEach((button, index) => {
        const angle = parseFloat(button.dataset.angle);
        const radius = parseFloat(button.dataset.radius);
        
        if (isNaN(angle) || isNaN(radius)) {
            return;
        }
        
        const x = finalCenterX + Math.cos(angle) * radius;
        const y = finalCenterY + Math.sin(angle) * radius;
        
        button.style.left = `${x - 27.5}px`;
        button.style.top = `${y - 27.5}px`;
    });
}

// Iniciar animacion de apertura
function startOpenAnimation() {
    // Animar boton central primero
    if (categorias_centerButton) {
        setTimeout(() => {
            categorias_centerButton.style.transform = 'scale(1) rotate(360deg)';
            categorias_centerButton.style.opacity = '1';
        }, 100);
    }
    
    // Animar botones flotantes con delay escalonado
    categorias_floatingButtons.forEach((button, index) => {
        setTimeout(() => {
            button.style.transform = 'scale(1) rotate(0deg)';
            button.style.opacity = '1';
        }, 200 + (index * 100)); // 100ms de delay entre cada boton
    });
    
    // Finalizar animacion
    setTimeout(() => {
        categorias_isAnimating = false;
    }, 200 + (categorias_floatingButtons.length * 100) + 400);
}

// Event listeners animados
function setupAnimatedEventListeners() {
    // Cerrar al hacer click fuera con animacion
    const handleClick = (e) => {
        if (categorias_activeFloatingContainer && !categorias_activeFloatingContainer.contains(e.target)) {
            closeCategoriasFloatingActions();
        }
    };
    
    // Actualizar posiciones en resize
    const handleResize = () => {
        if (categorias_activeFloatingContainer) {
            setTimeout(() => {
                updateAnimatedButtonPositions();
            }, 100);
        }
    };
    
    // Manejar scroll del contenedor padre
    const handleScroll = () => {
        if (categorias_activeFloatingContainer) {
            updateAnimatedButtonPositions();
        }
    };
    
    document.addEventListener('click', handleClick);
    window.addEventListener('resize', handleResize, { passive: true });
    
    // Agregar listener de scroll al contenedor padre
    const container = categorias_activeFloatingContainer.parentElement;
    if (container) {
        container.addEventListener('scroll', handleScroll, { passive: true });
    }
    
    // Limpiar listeners cuando se cierre
    categorias_activeFloatingContainer.cleanup = () => {
        document.removeEventListener('click', handleClick);
        window.removeEventListener('resize', handleResize);
        if (container) {
            container.removeEventListener('scroll', handleScroll);
        }
    };
}

// Cerrar meno flotante con animacion avanzada
function closeCategoriasFloatingActions() {
    if (!categorias_activeFloatingContainer || categorias_isAnimating) return;
    
    categorias_isAnimating = true;
    
    // Limpiar timeout anterior si existe
    if (categorias_animationTimeout) {
        clearTimeout(categorias_animationTimeout);
    }
    
    // Ocultar tooltip si existe
    hideTooltip();
    
    // Animar salida de botones flotantes (en orden inverso)
    categorias_floatingButtons.forEach((button, index) => {
        setTimeout(() => {
            button.style.transform = 'scale(0) rotate(-180deg)';
            button.style.opacity = '0';
        }, index * 50);
    });
    
    // Animar salida del boton central
    if (categorias_centerButton) {
        setTimeout(() => {
            categorias_centerButton.style.transform = 'scale(0) rotate(-360deg)';
            categorias_centerButton.style.opacity = '0';
        }, categorias_floatingButtons.length * 50 + 100);
    }
    
    // Limpiar despuos de que termine la animacion
    categorias_animationTimeout = setTimeout(() => {
        if (categorias_activeFloatingContainer) {
            if (categorias_activeFloatingContainer.cleanup) {
                categorias_activeFloatingContainer.cleanup();
            }
            
            categorias_activeFloatingContainer.remove();
            categorias_activeFloatingContainer = null;
            categorias_centerButton = null;
            categorias_floatingButtons = [];
            categorias_activeCategoriaId = null;
            categorias_isAnimating = false;
        }
    }, categorias_floatingButtons.length * 50 + 400);
}

// Mantener compatibilidad con funcion anterior
function closeCategoriaFloatingActionsAlias() {
    closeCategoriasFloatingActions();
}

// Funcion para forzar el cierre con retraso del meno flotante
function forceCloseCategoriasFloatingActions() {
    // Agregar un retraso antes del cierre forzado
    setTimeout(() => {
        // Limpiar cualquier timeout pendiente
        if (categorias_animationTimeout) {
            clearTimeout(categorias_animationTimeout);
            categorias_animationTimeout = null;
        }
        
        // Ocultar tooltip inmediatamente
        hideTooltip();
        
        // Si hay un contenedor activo, eliminarlo inmediatamente
        if (categorias_activeFloatingContainer) {
            try {
                // Limpiar eventos si existen
                if (categorias_activeFloatingContainer.cleanup) {
                    categorias_activeFloatingContainer.cleanup();
                }
                
                // Remover del DOM inmediatamente
                categorias_activeFloatingContainer.remove();
            } catch (e) {
            }
            
            // Resetear variables globales
            categorias_activeFloatingContainer = null;
            categorias_centerButton = null;
            categorias_floatingButtons = [];
            categorias_activeCategoriaId = null;
            categorias_isAnimating = false;
        }
        
        // Asegurarse de que no queden elementos flotantes huorfanos
        const orphanedContainers = document.querySelectorAll('.animated-floating-container');
        orphanedContainers.forEach(container => {
            try {
                container.remove();
            } catch (e) {
                console.warn('Error eliminando contenedor huorfano:', e);
            }
        });
    }, 320); // Retraso de 150ms antes del cierre forzado
}

// ============ SISTEMA DE MODALES ============



// Funcion para exportar categoroas
async function exportCategorias() {
    
    try {
        showNotification('Preparando exportacion...', 'info');
        
        const response = await fetch(`${CONFIG.apiUrl}?action=export`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        if (response.ok) {
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `categoroas_${new Date().toISOString().split('T')[0]}.csv`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
            
            showNotification('Categoroas exportados exitosamente', 'success');
        } else {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        
    } catch (error) {
        
        // Generar CSV del lado del cliente como fallback
        if (categoroas && categoroas.length > 0) {
            generateClientSideCSV();
        } else {
            showNotification('No hay categoroas para exportar', 'warning');
        }
    }
}

// Funcion para generar CSV del lado del cliente
function generateClientSideCSV() {
    const headers = ['ID', 'Nombre', 'Codigo', 'Categoroa', 'Stock', 'Precio', 'Estado'];
    let csvContent = headers.join(',') + '\n';
    
    categoroas.forEach(categoroa => {
        const row = [
            categoroa.id_categoroa || '',
            `"${(categoroa.nombre_categoroa || '').replace(/"/g, '""')}"`,
            categoroa.codigo_categoria || '',
            `"${(categoroa.categoria_nombre || categoroa.nombre_categoria || '').replace(/"/g, '""')}"`,
            categoroa.stock_actual_categoroa != null ? categoroa.stock_actual_categoroa : 0,
            categoroa.precio_categoroa != null ? categoroa.precio_categoroa : 0,
            (categoroa.activo == 1 || categoroa.status_categoroa == 1) ? 'Activo' : 'Inactivo'
        ];
        csvContent += row.join(',') + '\n';
    });
    
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.style.display = 'none';
    a.href = url;
    a.download = `categoroas_${new Date().toISOString().split('T')[0]}.csv`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
    
    // showNotification('Categoroas exportados exitosamente', 'success');
}

// Funcion para mostrar reporte de stock
function showCategoriaReport() {
    // Implementar modal de reporte de stock
    // showNotification('Reporte de stock - Funcionalidad en desarrollo', 'info');
}

// Funcion para limpiar bosqueda con animacion
function clearCategoriaSearch() {
    if (typeof $ !== 'undefined') {
        const searchInput = $('#search-categorias');
        searchInput.val('').focus();
        
        // Animacion visual
        const searchContainer = searchInput.parent();
        searchContainer.addClass('search-cleared');
        
        setTimeout(() => {
            searchContainer.removeClass('search-cleared');
        }, 300);
    } else {
        // Fallback vanilla JS
        const searchInput = document.getElementById('search-categorias');
        if (searchInput) {
            searchInput.value = '';
            searchInput.focus();
        }
    }
    
    filterCategorias();
}

// Funcion para limpiar todos los filtros con efectos visuales
function clearAllCategoriaFilters() {
    if (typeof $ !== 'undefined') {
        // Limpiar todos los campos con jQuery
        $('#search-categorias').val('');
        $('#filter-category').val('');
        $('#filter-status').val('');
        $('#filter-stock').val('');
        $('#filter-fecha-value').val('');
        
        // Limpiar Flatpickr
        if (window.categoriasDatePicker) {
            window.categoriasDatePicker.clear();
        }
        
        // Resetear texto del boton de fecha
        const filterFechaText = document.getElementById('filter-fecha-text');
        if (filterFechaText) {
            filterFechaText.textContent = 'Seleccionar fechas';
        }
        
        // Efecto visual de limpieza
        $('.module-filters').addClass('filters-clearing');
        
        setTimeout(() => {
            $('.module-filters').removeClass('filters-clearing');
        }, 400);
    } else {
        // Fallback vanilla JS
        const elements = [
            'search-categorias',
            'filter-category',
            'filter-status',
            'filter-stock',
            'filter-fecha-value'
        ];
        
        elements.forEach(id => {
            const element = document.getElementById(id);
            if (element) element.value = '';
        });
        
        // Limpiar Flatpickr
        if (window.categoriasDatePicker) {
            window.categoriasDatePicker.clear();
        }
        
        // Resetear texto del boton de fecha
        const filterFechaText = document.getElementById('filter-fecha-text');
        if (filterFechaText) {
            filterFechaText.textContent = 'Seleccionar fechas';
        }
    }
    
    // Mostrar notificacion
    // showNotification('Filtros limpiados', 'info');
    
    filterCategorias();
}

// Funcion para acciones en lote
async function handleBulkCategoriaAction(action) {
    const selectedProducts = getSelectedProducts();
    
    if (selectedProducts.length === 0) {
        // // showNotification('Por favor selecciona al menos un categoroa', 'warning');
        return;
    }    
    const confirmMessage = `oEstos seguro de ${action} ${selectedProducts.length} categoroa(s)?`;
    if (!confirm(confirmMessage)) return;
    
    try {
        let endpoint = '';
        let method = 'POST';
        
        switch (action) {
            case 'activar':
                endpoint = '?action=bulk-activate';
                break;
            case 'desactivar':
                endpoint = '?action=bulk-deactivate';
                break;
            case 'eliminar':
                endpoint = '?action=bulk-delete';
                method = 'DELETE';
                break;
            default:
                throw new Error('Accion no volida');
        }
        
        const response = await fetch(`${CONFIG.apiUrl}${endpoint}`, {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ ids: selectedProducts })
        });
        
        const result = await response.json();
        
        if (response.ok) {
            // showNotification(`${action} completado para ${selectedProducts.length} categoroa(s)`, 'success');
            loadCategorias(); // Recargar lista
            clearProductSelection();
        } else {
            throw new Error(result.message || 'Error en operacion en lote');
        }
        
    } catch (error) {
        // showNotification('Error: ' + error.message, 'error');
    }
}

// Funcion para toggle select all
function toggleSelectAllProducts(checkbox) {
    
    const productCheckboxes = document.querySelectorAll('input[name="product_select"]');
    productCheckboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
    
    updateBulkActionButtons();
}

// Funcion para ver categoroa (wrapper que llama al parent)
function viewCategoria(id) {
    console.log('??? viewCategoria() llamado con ID:', id);
    
    // CERRAR BURBUJA DE STOCK si esto abierta
    closeCantidadBubble();
    
    // Verificar si el ID es volido
    if (!id || id === 'undefined' || id === 'null') {
        console.error('? ID involido para ver:', id);
        if (typeof showNotification === 'function') {
            // showNotification('Error: ID de categoroa involido', 'error');
        }
        return;
    }
    
    // Debug: Verificar disponibilidad de funciones
    console.log('?? Buscando showViewProductModal en:', {
        'window': typeof window.showViewProductModal,
        'parent': typeof parent?.showViewProductModal,
        'top': typeof top?.showViewProductModal
    });
    
    // Como NO estamos en iframe, parent === window
    // Buscar directamente en window
    if (typeof window.showViewProductModal === 'function') {
        console.log('? Llamando a window.showViewProductModal');
        window.showViewProductModal(id);
    } else if (typeof window.viewCategoria !== viewCategoria && typeof window.viewCategoria === 'function') {
        // Evitar recursion infinita
        console.log('? Llamando a window.viewCategoria (funcion diferente)');
        window.viewCategoria(id);
    } else {
        console.error('? showViewProductModal NO disponible. Funciones disponibles:', Object.keys(window).filter(k => k.includes('Product')));
        console.warn('?? Usando fallback: abrir en nueva ventana');
        // Fallback: abrir en nueva ventana
        const url = AppConfig ? AppConfig.getViewUrl(`admin/product_modal.php?action=view&id=${id}`) : `/fashion-master/app/views/admin/product_modal.php?action=view&id=${id}`;
        window.open(url, 'ProductView', 'width=900,height=700');
    }
}

// ===== FUNCIoN GLOBAL PARA CERRAR BURBUJA DE STOCK =====
function closeCantidadBubble() {
    const existingBubbles = document.querySelectorAll('.stock-update-bubble');
    const existingOverlays = document.querySelectorAll('.stock-bubble-overlay');
    
    existingBubbles.forEach(bubble => {
        // Limpiar listeners si existen
        if (bubble.updatePositionListener) {
            window.removeEventListener('scroll', bubble.updatePositionListener, true);
            window.removeEventListener('resize', bubble.updatePositionListener);
        }
        
        // Animacion de salida
        bubble.style.transform = 'scale(0)';
        bubble.style.opacity = '0';
        
        setTimeout(() => {
            if (bubble && bubble.parentNode) {
                bubble.remove();
            }
        }, 400);
    });
    
    existingOverlays.forEach(overlay => {
        setTimeout(() => {
            if (overlay && overlay.parentNode) {
                overlay.remove();
            }
        }, 400);
    });
    
    console.log('??? Burbujas de stock cerradas');
}

// Funcion para editar categoroa
// Funcion editCategoria ahora esto definida al final del archivo en la seccion CRUD

// Funcion para actualizar stock - MEJORADA CON BURBUJA SIN BOTONES
function updateCantidad(id, currentStock, event) {
    // VERIFICAR SI YA EXISTE UNA BURBUJA ABIERTA PARA ESTE CATEGORoA (TOGGLE)
    const existingBubble = document.querySelector(`.stock-update-bubble[data-product-id="${id}"]`);
    if (existingBubble) {
        console.log('?? Burbuja ya existe para este categoroa, cerrando (TOGGLE)...');
        closeCantidadBubble();
        return; // SALIR - No abrir de nuevo
    }
    
    // CERRAR MENo FLOTANTE SI ESTo ABIERTO (sin bloquear futuros menos)
    if (categorias_activeFloatingContainer) {
        // Cerrar con animacion
        closeCategoriasFloatingActions();
    }
    
    // Forzar eliminacion de cualquier meno flotante residual
    const allFloatingMenus = document.querySelectorAll('.animated-floating-container');
    allFloatingMenus.forEach(menu => {
        if (menu && menu.parentNode) {
            menu.remove();
        }
    });
    
    // Resetear variables globales del meno flotante
    categorias_activeFloatingContainer = null;
    categorias_activeCategoriaId = null;
    categorias_isAnimating = false;
    if (categorias_animationTimeout) {
        clearTimeout(categorias_animationTimeout);
        categorias_animationTimeout = null;
    }
    
    // Eliminar cualquier burbuja existente (de otros categoroas)
    closeCantidadBubble();
    
    // Crear overlay SIN bloquear scroll - solo para detectar clicks
    const overlay = document.createElement('div');
    overlay.className = 'stock-bubble-overlay';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1000000;
        background: transparent;
        pointer-events: none;
    `;
    
    // Crear burbuja de stock - PEQUEoA (50x50px) estilo botones flotantes, expandible hasta 3 dogitos
    const stockBubble = document.createElement('div');
    stockBubble.className = 'stock-update-bubble';
    stockBubble.setAttribute('data-product-id', id); // Agregar ID del categoroa para identificar
    stockBubble.innerHTML = `
        <input type="number" 
               id="stockInput" 
               value="${currentStock}" 
               min="0" 
               max="999"
               class="stock-input-circle"
               placeholder="0"
               autocomplete="off"
               maxlength="3"
               style="border: none !important; outline: none !important; box-shadow: none !important; text-decoration: none !important; -webkit-appearance: none !important; border-bottom: none !important;">
    `;
    
    // Encontrar el boton que disparo la accion (puede ser btn-menu de tabla o btn-stock de grid)
    // Primero intentar obtenerlo del evento
    let triggerButton = null;
    let isGridView = false;
    
    if (event) {
        // Intentar desde currentTarget
        triggerButton = event.currentTarget;
        
        // Verificar si es un boton de la vista grid
        if (triggerButton && triggerButton.classList.contains('categoria-card-btn')) {
            isGridView = true;
            console.log('? Detectado: Vista Grid desde boton');
        }
        // Si es un boton flotante, ignorar y buscar el boton real
        else if (triggerButton && triggerButton.classList.contains('animated-floating-button')) {
            triggerButton = null; // Resetear para buscar el boton correcto
            console.log('?? Evento desde boton flotante, buscando boton real...');
        }
        // Si es el btn-menu de la tabla
        else if (triggerButton && triggerButton.classList.contains('btn-menu')) {
            isGridView = false;
            console.log('? Detectado: Vista Tabla desde btn-menu');
        }
    }
    
    // Si aon no tenemos el boton, buscarlo en el DOM por el ID del categoroa
    if (!triggerButton) {
        console.log('?? Buscando boton en DOM para categoroa ID:', id);
        
        // Determinar quo vista esto visible actualmente
        const tableContainer = document.querySelector('.data-table-wrapper');
        const gridContainer = document.querySelector('.categorias-grid');
        const isTableVisible = tableContainer && tableContainer.style.display !== 'none';
        const isGridVisible = gridContainer && gridContainer.style.display !== 'none';
        
        console.log('?? Vistas visibles - Tabla:', isTableVisible, 'Grid:', isGridVisible);
        
        // Buscar en la vista VISIBLE primero
        if (isGridVisible) {
            // Buscar en vista grid (visible)
            const productCard = document.querySelector(`.categoria-card[data-product-id="${id}"]`);
            if (productCard) {
                triggerButton = productCard.querySelector('.btn-stock');
                if (triggerButton) {
                    isGridView = true;
                    console.log('? Encontrado en Grid:', triggerButton);
                }
            }
        }
        
        if (!triggerButton && isTableVisible) {
            // Buscar en la tabla (visible)
            const productRow = document.querySelector(`tr[data-product-id="${id}"]`);
            if (productRow) {
                triggerButton = productRow.querySelector('.btn-menu');
                if (triggerButton) {
                    isGridView = false;
                    console.log('? Encontrado en Tabla:', triggerButton);
                }
            }
        }
    }
    
    // oltimo recurso: buscar por atributo onclick en la tabla
    if (!triggerButton) {
        triggerButton = document.querySelector(`[onclick*="showActionMenu(${id}"]`);
        if (triggerButton) {
            isGridView = false;
            console.log('? Encontrado por onclick:', triggerButton);
        }
    }
    
    if (!triggerButton) {
        console.error('? No se encontro el boton para el categoroa', id);
        return;
    }
    
    // VALIDAR QUE EL BOToN ESTo VISIBLE (no en una vista oculta)
    const rect = triggerButton.getBoundingClientRect();
    if (rect.width === 0 || rect.height === 0) {
        console.error('? El boton encontrado esto oculto (width/height = 0)');
        console.error('   Boton:', triggerButton);
        console.error('   Rect:', rect);
        closeCantidadBubble(); // Cerrar cualquier burbuja residual
        return;
    }
    
    console.log('? Boton final encontrado:', triggerButton, 'Vista Grid:', isGridView);
    
    // USAR POSICIoN FIXED (viewport) como los botones flotantes
    const triggerRect = triggerButton.getBoundingClientRect();
    
    // Calcular centro del boton en coordenadas del viewport
    const centerX = triggerRect.left + (triggerRect.width / 2);
    const centerY = triggerRect.top + (triggerRect.height / 2);
    
    // Posicion segon la vista
    const bubbleSize = 40;
    const radius = 65;
    let angle;
    
    if (isGridView) {
        // En vista grid: arriba del boton (ongulo 270o = -p/2)
        angle = -Math.PI / 2; // 270o = arriba
    } else {
        // En vista tabla: a la izquierda del boton (ongulo 180o = p)
        angle = Math.PI; // 180o = izquierda
    }
    
    // Calcular posicion con POSITION FIXED (coordenadas del viewport)
    const bubbleX = centerX + (Math.cos(angle) * radius) - (bubbleSize / 2);
    const bubbleY = centerY + (Math.sin(angle) * radius) - (bubbleSize / 2);
    
    // DEBUG: Mostrar valores calculados
    console.log('?? Colculo con POSITION FIXED:', {
        'Trigger (boton viewport)': { 
            top: triggerRect.top.toFixed(2), 
            left: triggerRect.left.toFixed(2),
            width: triggerRect.width,
            height: triggerRect.height
        },
        'Centro (viewport)': { 
            centerX: centerX.toFixed(2), 
            centerY: centerY.toFixed(2) 
        },
        'Formula': {
            'cos(p) * 65': (Math.cos(angle) * radius).toFixed(2),
            'sin(p) * 65': (Math.sin(angle) * radius).toFixed(2),
            'bubbleSize/2': (bubbleSize / 2)
        },
        '?? POSICIoN FINAL (fixed)': { 
            bubbleX: bubbleX.toFixed(2), 
            bubbleY: bubbleY.toFixed(2) 
        }
    });
    
    // Aplicar estilos - POSICIoN FIXED (viewport) como botones flotantes - Se expande segon dogitos
    stockBubble.style.cssText = `
        position: fixed !important;
        left: ${bubbleX}px !important;
        top: ${bubbleY}px !important;
        min-width: ${bubbleSize}px !important;
        width: ${bubbleSize}px !important;
        height: ${bubbleSize}px !important;
        background: linear-gradient(135deg, #10b981 0%, #059669 50%, #047857 100%) !important;
        border: 2px solid rgba(16, 185, 129, 0.3);
        border-radius: 20px !important;
        padding: 0 6px !important;
        box-shadow: 0 8px 24px rgba(16, 185, 129, 0.4), 
                    0 4px 12px rgba(5, 150, 105, 0.3),
                    0 0 0 1px rgba(255, 255, 255, 0.1),
                    inset 0 1px 2px rgba(255, 255, 255, 0.15);
        z-index: 1000002 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        transform: scale(0) !important;
        opacity: 0 !important;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
        pointer-events: auto !important;
        backdrop-filter: blur(10px);
    `;
    
    // Guardar referencia al boton para recalcular posicion en scroll/resize
    stockBubble.triggerButton = triggerButton;
    stockBubble.isGridView = isGridView;
    
    // Estilos para el input - SIN SUBRAYADO y con expansion ovalada
    const style = document.createElement('style');
    style.id = 'stock-bubble-styles';
    style.textContent = `
        .stock-update-bubble {
            white-space: nowrap;
        }
        
        .stock-input-circle {
            background: transparent !important;
            border: 0 !important;
            border-width: 0 !important;
            border-style: none !important;
            border-color: transparent !important;
            border-top: 0 !important;
            border-right: 0 !important;
            border-bottom: 0 !important;
            border-left: 0 !important;
            outline: 0 !important;
            outline-width: 0 !important;
            outline-style: none !important;
            outline-color: transparent !important;
            outline-offset: 0 !important;
            padding: 0 !important;
            margin: 0 !important;
            font-size: 18px !important;
            font-weight: 900 !important;
            text-align: center !important;
            width: 100% !important;
            height: 100% !important;
            color: #ffffff !important;
            transition: none !important;
            font-family: 'Segoe UI', 'Arial', sans-serif !important;
            letter-spacing: -0.5px !important;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2) !important;
            -webkit-appearance: none !important;
            -moz-appearance: textfield !important;
            appearance: none !important;
            box-shadow: none !important;
            -webkit-box-shadow: none !important;
            -moz-box-shadow: none !important;
            text-decoration: none !important;
            text-decoration-line: none !important;
            text-decoration-style: none !important;
            text-decoration-color: transparent !important;
            border-image: none !important;
            background-image: none !important;
            background-clip: padding-box !important;
            -webkit-text-fill-color: #ffffff !important;
            caret-color: #ffffff !important;
        }
        
        .stock-input-circle:focus,
        .stock-input-circle:active,
        .stock-input-circle:hover,
        .stock-input-circle:visited,
        .stock-input-circle:focus-visible,
        .stock-input-circle:focus-within {
            outline: 0 !important;
            outline-width: 0 !important;
            outline-style: none !important;
            outline-color: transparent !important;
            outline-offset: 0 !important;
            border: 0 !important;
            border-width: 0 !important;
            border-style: none !important;
            border-color: transparent !important;
            border-top: 0 !important;
            border-right: 0 !important;
            border-bottom: 0 !important;
            border-left: 0 !important;
            box-shadow: none !important;
            -webkit-box-shadow: none !important;
            -moz-box-shadow: none !important;
            background: transparent !important;
            text-decoration: none !important;
            text-decoration-line: none !important;
            text-decoration-style: none !important;
            text-decoration-color: transparent !important;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            text-shadow: 0 0 12px rgba(255, 255, 255, 0.8),
                         0 2px 4px rgba(0, 0, 0, 0.3) !important;
        }
        
        .stock-input-circle::-webkit-outer-spin-button,
        .stock-input-circle::-webkit-inner-spin-button {
            -webkit-appearance: none !important;
            margin: 0 !important;
            display: none !important;
        }
        
        .stock-input-circle[type=number] {
            -moz-appearance: textfield !important;
        }
        
        .stock-input-circle::placeholder {
            color: rgba(255, 255, 255, 0.5) !important;
            font-size: 18px !important;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2) !important;
        }
        
        /* Forzar eliminacion de cualquier estilo de Chrome/Edge */
        input[type=number].stock-input-circle::-webkit-textfield-decoration-container {
            border: none !important;
            outline: none !important;
        }
        
        @keyframes shake {
            0%, 100% { transform: scale(1) translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: scale(1) translateX(-5px); }
            20%, 40%, 60%, 80% { transform: scale(1) translateX(5px); }
        }
    `;
    
    // Eliminar style anterior si existe
    const oldStyle = document.getElementById('stock-bubble-styles');
    if (oldStyle) oldStyle.remove();
    document.head.appendChild(style);
    
    // Agregar overlay al body (sin bloquear scroll)
    document.body.appendChild(overlay);
    
    // Agregar burbuja al BODY (position fixed)
    document.body.appendChild(stockBubble);
    
    // Actualizar posicion en scroll/resize (con position fixed)
    const updateBubblePosition = () => {
        if (!stockBubble || !stockBubble.triggerButton) return;
        
        const triggerRect = stockBubble.triggerButton.getBoundingClientRect();
        
        const centerX = triggerRect.left + triggerRect.width / 2;
        const centerY = triggerRect.top + triggerRect.height / 2;
        
        const bubbleSize = 40;
        const radius = 65;
        
        // Usar el ongulo guardado segon la vista
        const angle = stockBubble.isGridView ? (-Math.PI / 2) : Math.PI;
        
        const bubbleX = centerX + Math.cos(angle) * radius - bubbleSize / 2;
        const bubbleY = centerY + Math.sin(angle) * radius - bubbleSize / 2;
        
        if (stockBubble && stockBubble.style) {
            stockBubble.style.left = bubbleX + 'px';
            stockBubble.style.top = bubbleY + 'px';
        }
    };
    
    // Listener para scroll/resize
    window.addEventListener('scroll', updateBubblePosition, true);
    window.addEventListener('resize', updateBubblePosition);
    stockBubble.updatePositionListener = updateBubblePosition;
    
    // Activar animacion de entrada con reflow
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            if (stockBubble && stockBubble.style) {
                stockBubble.style.transform = 'scale(1)';
                stockBubble.style.opacity = '1';
            }
        });
    });
    
    // Focus en el input
    setTimeout(() => {
        const input = stockBubble?.querySelector('#stockInput');
        if (input) {
            input.focus();
            input.select();
            
            // Ajustar ancho de la burbuja segon el nomero de dogitos (expansion ovalada)
            const adjustBubbleWidth = () => {
                const value = input.value.toString();
                const numDigits = value.length || 1;
                
                // Ancho base 40px, +12px por cada dogito extra
                let newWidth = 40;
                if (numDigits === 2) {
                    newWidth = 52; // Mos ovalado para 2 dogitos
                } else if (numDigits >= 3) {
                    newWidth = 64; // Mos ovalado para 3 dogitos
                }
                
                stockBubble.style.width = newWidth + 'px';
                
                // Recalcular posicion para centrar la burbuja expandida
                const triggerRect = triggerButton.getBoundingClientRect();
                const centerX = triggerRect.left + (triggerRect.width / 2);
                const centerY = triggerRect.top + (triggerRect.height / 2);
                const radius = 65;
                const angle = isGridView ? (-Math.PI / 2) : Math.PI;
                
                const bubbleX = centerX + (Math.cos(angle) * radius) - (newWidth / 2);
                const bubbleY = centerY + (Math.sin(angle) * radius) - (40 / 2);
                
                stockBubble.style.left = bubbleX + 'px';
                stockBubble.style.top = bubbleY + 'px';
            };
            
            // Limitar a 3 dogitos
            input.addEventListener('input', function(e) {
                // Eliminar cualquier carocter no numorico
                this.value = this.value.replace(/[^0-9]/g, '');
                
                // Limitar a 3 dogitos (moximo 999)
                if (this.value.length > 3) {
                    this.value = this.value.slice(0, 3);
                }
                
                // Validar que no exceda 999
                if (parseInt(this.value) > 999) {
                    this.value = '999';
                }
                
                // Ajustar ancho de la burbuja
                adjustBubbleWidth();
            });
            
            // Ajustar ancho inicial
            adjustBubbleWidth();
        }
    }, 450);
    
    // Funcion para guardar
    function saveStock() {
        if (!stockBubble) {
            console.error('? stockBubble no existe');
            return;
        }
        
        const input = stockBubble.querySelector('#stockInput');
        if (!input) {
            console.error('? input no existe');
            return;
        }
        
        const newStock = parseInt(input.value);
        
        if (isNaN(newStock) || newStock < 0 || newStock > 999) {
            // Animacion de error - shake sin afectar el scale
            const originalTransform = stockBubble.style.transform;
            stockBubble.style.animation = 'shake 0.5s ease-in-out';
            input.style.color = '#fee2e2';
            input.style.textShadow = '0 0 10px rgba(239, 68, 68, 0.8)';
            
            setTimeout(() => {
                if (stockBubble) {
                    stockBubble.style.animation = '';
                    stockBubble.style.transform = originalTransform;
                }
                if (input) {
                    input.style.color = '';
                    input.style.textShadow = '';
                }
            }, 500);
            return;
        }
        
        // Animacion de salida
        stockBubble.style.transform = 'scale(0)';
        stockBubble.style.opacity = '0';
        
        // Limpiar click outside handler
        if (clickOutsideHandler) {
            document.removeEventListener('click', clickOutsideHandler);
            clickOutsideHandler = null;
        }
        
        // Llamada AJAX para actualizar stock
        const formData = new FormData();
        formData.append('action', 'update_stock');
        formData.append('id', id);
        formData.append('stock', newStock);
        
        fetch(`${CONFIG.apiUrl}`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Mostrar notificacion de oxito
                if (typeof showNotification === 'function') {
                    // showNotification(`? Stock actualizado a ${newStock} unidades`, 'success');
                }
                
                // Usar actualizacion suave si esto disponible
                if (window.smoothTableUpdater && data.product) {
                    console.log('?? Usando actualizacion suave para cambiar stock del categoroa:', id);
                    window.smoothTableUpdater.updateSingleProduct(data.product);
                } else {
                    console.log('?? SmoothTableUpdater no disponible o categoroa no retornado - usando recarga tradicional');
                    // Actualizar lista inmediatamente
                    loadCategorias(true);
                }
                
                // Cerrar burbuja y overlay
                setTimeout(() => {
                    if (overlay && overlay.parentNode) overlay.remove();
                    if (stockBubble && stockBubble.parentNode) stockBubble.remove();
                }, 400);
            } else {
                if (typeof showNotification === 'function') {
                    // showNotification('? Error al actualizar stock', 'error');
                }
                if (overlay && overlay.parentNode) overlay.remove();
                if (stockBubble && stockBubble.parentNode) stockBubble.remove();
            }
        })
        .catch(error => {
            if (typeof showNotification === 'function') {
                // showNotification('? Error de conexion', 'error');
            }
            if (overlay && overlay.parentNode) overlay.remove();
            if (stockBubble && stockBubble.parentNode) stockBubble.remove();
        });
    }
    
    // Variable para guardar el handler del click outside
    let clickOutsideHandler = null;
    
    // Funcion para cerrar sin guardar
    function closeBubble() {
        if (!stockBubble) return;
        
        // Limpiar listeners
        if (stockBubble.updatePositionListener) {
            window.removeEventListener('scroll', stockBubble.updatePositionListener, true);
            window.removeEventListener('resize', stockBubble.updatePositionListener);
        }
        
        // Limpiar click outside handler
        if (clickOutsideHandler) {
            document.removeEventListener('click', clickOutsideHandler);
            clickOutsideHandler = null;
        }
        
        stockBubble.style.transform = 'scale(0)';
        stockBubble.style.opacity = '0';
        setTimeout(() => {
            if (overlay && overlay.parentNode) overlay.remove();
            if (stockBubble && stockBubble.parentNode) stockBubble.remove();
        }, 400);
    }
    
    // Eventos del input
    const input = stockBubble.querySelector('#stockInput');
    
    if (!input) {
        console.error('? No se encontro el input de stock');
        return;
    }
    
    // Guardar con Enter
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            saveStock();
        }
        if (e.key === 'Escape') {
            e.preventDefault();
            closeBubble();
        }
    });
    
    // Cerrar al hacer click en el overlay (fuera de la burbuja)
    overlay.addEventListener('click', function(e) {
        // Solo si el click es directamente en el overlay, no en sus hijos
        if (e.target === overlay) {
            saveStock(); // Guardar al hacer click fuera
        }
    });
    
    // MANTENER pointer-events: none en overlay para permitir scroll
    // El click se detectaro solo cuando hagamos click en el orea del overlay
    
    // Prevenir que clicks en la burbuja cierren el overlay
    stockBubble.addEventListener('click', function(e) {
        e.stopPropagation();
    });
    
    // Cerrar al hacer click fuera (usando evento en document)
    clickOutsideHandler = function(e) {
        // Si el click no es en la burbuja ni en el overlay, guardar
        if (!stockBubble.contains(e.target) && e.target !== stockBubble) {
            saveStock();
            document.removeEventListener('click', clickOutsideHandler);
            clickOutsideHandler = null;
        }
    };
    
    // Agregar listener despuos de un pequeoo delay para evitar que se cierre inmediatamente
    setTimeout(() => {
        document.addEventListener('click', clickOutsideHandler);
    }, 100);
}

// Funcion para toggle status
async function toggleProductStatus(id, currentStatus) {
    
    const newStatus = !currentStatus;
    const action = newStatus ? 'activar' : 'desactivar';
    
    if (!confirm(`oEstos seguro de ${action} este categoroa?`)) return;
    
    try {
        const response = await fetch(`${CONFIG.apiUrl}?action=toggle_status&id=${id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ status: newStatus })
        });
        
        const result = await response.json();
        
        if (response.ok) {
            // showNotification(`Categoroa ${action} exitosamente`, 'success');
            loadCategorias(); // Recargar lista
        } else {
            throw new Error(result.message || 'Error al cambiar estado');
        }
        
    } catch (error) {
        // showNotification('Error: ' + error.message, 'error');
    }
}

// Funcion changeCategoriaEstado ahora esto definida al final del archivo en la seccion CRUD


// ============ FUNCIONES DE PAGINACIoN ============

function goToFirstPageCategorias() {
    if (currentPage > 1) {
        currentPage = 1;
        loadCategorias();
    }
}

function previousPageCategorias() {
    if (currentPage > 1) {
        currentPage--;
        loadCategorias();
    }
}

function nextPageCategorias() {
    if (currentPage < totalPages) {
        currentPage++;
        loadCategorias();
    }
}

function goToLastPageCategorias() {
    if (currentPage < totalPages) {
        currentPage = totalPages;
        loadCategorias();
    }
}

// ============ FUNCIONES AUXILIARES ============

// Funcion para obtener categoroas seleccionados
function getSelectedProducts() {
    const checkboxes = document.querySelectorAll('input[name="product_select"]:checked');
    return Array.from(checkboxes).map(cb => parseInt(cb.value));
}

// Funcion para limpiar seleccion de categoroas
function clearProductSelection() {
    const checkboxes = document.querySelectorAll('input[name="product_select"]');
    checkboxes.forEach(cb => cb.checked = false);
    
    const selectAll = document.querySelector('input[type="checkbox"][onchange*="toggleSelectAllProducts"]');
    if (selectAll) selectAll.checked = false;
    
    updateBulkActionButtons();
}

// Funcion para actualizar botones de acciones en lote
function updateBulkActionButtons() {
    const selected = getSelectedProducts();
    const bulkActions = document.querySelector('.bulk-actions');
    
    if (bulkActions) {
        if (selected.length > 0) {
            bulkActions.style.display = 'flex';
            bulkActions.querySelector('.selected-count').textContent = selected.length;
        } else {
            bulkActions.style.display = 'none';
        }
    }
}

// ============ NOTIFICACIONES DESACTIVADAS ============
// Todas las funciones de notificacion han sido desactivadas por solicitud del usuario

// ============ INICIALIZACIoN ============

// Funcion para actualizar contador de resultados
function updateResultsCounter(showing, total) {
    const showingStartEl = document.getElementById('showing-start-products');
    const showingEndEl = document.getElementById('showing-end-categorias');
    const totalProductsEl = document.getElementById('total-categorias');
    
    if (showingStartEl) showingStartEl.textContent = showing > 0 ? 1 : 0;
    if (showingEndEl) showingEndEl.textContent = showing;
    if (totalProductsEl) totalProductsEl.textContent = total;
}

// Funcion de inicializacion principal
function initializeProductsModule() {
    
    // Asegurar que CONFIG esto inicializado
    if (typeof CONFIG === 'undefined' || !CONFIG.apiUrl) {
        initializeConfig();
    }

    
    // Verificar que los elementos necesarios existen
    const tbody = document.getElementById('categorias-table-body');
    
    // Detectar si es movil y preparar vista grid ANTES de cargar
    const isMobile = window.innerWidth <= 768;
    if (isMobile) {
        console.log('?? Dispositivo movil detectado, preparando vista grid');
        
        // Actualizar variable global de vista
        window.categorias_currentView = 'grid';
        
        // 1. Ocultar tabla INMEDIATAMENTE (antes que nada)
        const tableContainer = document.querySelector('.data-table-wrapper');
        if (tableContainer) {
            tableContainer.style.display = 'none !important';
            tableContainer.style.visibility = 'hidden';
        }
        
        // 2. Crear y mostrar grid container ANTES de cargar datos
        let gridContainer = document.querySelector('.categorias-grid');
        if (!gridContainer) {
            createGridView();
            gridContainer = document.querySelector('.categorias-grid');
        }
        
        // 3. Configurar grid para que esto visible desde el inicio
        if (gridContainer) {
            gridContainer.style.display = 'grid';
            gridContainer.style.visibility = 'visible';
            gridContainer.innerHTML = `
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px 20px; color: #94a3b8;">
                    <div style="display: flex; flex-direction: column; align-items: center; gap: 12px;">
                        <div class="spinner" style="border: 3px solid #e2e8f0; border-top-color: #3b82f6; width: 40px; height: 40px;"></div>
                        <span style="font-size: 14px;">Cargando categoroas...</span>
                    </div>
                </div>
            `;
        }
        
        // 4. Cambiar botones activos y BLOQUEAR en movil
        const viewButtons = document.querySelectorAll('.view-btn');
        viewButtons.forEach(btn => {
            btn.classList.remove('active');
            if (btn.dataset.view === 'grid') {
                btn.classList.add('active');
            }
            
            // BLOQUEAR botones en movil (solo grid permitido)
            if (btn.dataset.view === 'table') {
                btn.disabled = true;
                btn.style.opacity = '0.5';
                btn.style.cursor = 'not-allowed';
                btn.title = 'Vista tabla no disponible en movil';
            }
        });
        
        console.log('?? Botones de vista bloqueados en movil (solo grid)');
    } else {
        // En desktop, asegurar que los botones eston desbloqueados
        const viewButtons = document.querySelectorAll('.view-btn');
        viewButtons.forEach(btn => {
            btn.disabled = false;
            btn.style.opacity = '1';
            btn.style.cursor = 'pointer';
            btn.title = '';
        });
    }
    
    // ?? NOTA: Las categoroas NO tienen tipos/subcategoroas, por lo que no se carga loadTiposCategoria()
    // loadTiposCategoria(); // ? DESHABILITADO: las categoroas no usan este endpoint
    
    // En movil, cargar categoroas y luego forzar vista grid INSTANToNEAMENTE
    if (isMobile) {
        loadCategorias().then(() => {
            console.log('?? Categoroas cargados, ejecutando toggleView(grid) automoticamente');
            toggleView('grid'); // ? INSTANToNEO, sin timeout
        });
    } else {
        loadCategorias();
    }
    
    // ========================================
    // INICIALIZAR LIBRERoAS MODERNAS
    // ========================================
    
    // 1. Flatpickr para filtro de fecha - BOToN que abre calendario
    const filterFecha = document.getElementById('filter-fecha-categoria');
    const filterFechaValue = document.getElementById('filter-fecha-categoria-value');
    const filterFechaText = document.getElementById('filter-fecha-categoria-text');
    
    if (filterFecha && typeof flatpickr !== 'undefined') {
        console.log('?? Inicializando Flatpickr en boton de fecha para categoroas');
        
        // Crear input invisible para Flatpickr
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'text';
        hiddenInput.style.display = 'none';
        hiddenInput.id = 'flatpickr-hidden-input-categorias';
        filterFecha.parentNode.appendChild(hiddenInput);
        
        // Variable para controlar si el calendario esto abierto
        let isCalendarOpen = false;
        
        // ? DECLARAR calendarObserver ANTES de Flatpickr
        const calendarObserver = new MutationObserver(function(mutations) {
            // Re-marcar inmediatamente cuando haya cualquier cambio
            const calendar = document.querySelector('.flatpickr-calendar:not(.inline)');
            if (calendar && window.categoriasDatesArray && window.categoriasDatesArray.length > 0) {
                const days = calendar.querySelectorAll('.flatpickr-day:not(.flatpickr-disabled)');
                days.forEach(dayElem => {
                    if (dayElem.dateObj) {
                        const dateStr = dayElem.dateObj.toISOString().split('T')[0];
                        if (window.categoriasDatesArray.includes(dateStr)) {
                            if (!dayElem.classList.contains('has-products')) {
                                dayElem.classList.add('has-products');
                                dayElem.title = 'Hay categoroas en esta fecha';
                            }
                        }
                    }
                });
            }
        });
        
        // ? DECLARAR startObserving ANTES de Flatpickr
        const startObserving = () => {
            const calendar = document.querySelector('.flatpickr-calendar:not(.inline)');
            if (calendar) {
                calendarObserver.observe(calendar, {
                    childList: true,
                    subtree: true,
                    attributes: true,
                    attributeFilter: ['class', 'aria-label']
                });
                
                // FORZAR marcado inmediato despuos de iniciar observacion
                if (typeof markMonthsWithProducts === 'function') {
                    markMonthsWithProducts();
                }
            }
        };
        
        // Inicializar Flatpickr en el input invisible
        window.categoriasDatePicker = flatpickr(hiddenInput, {
            mode: "range",
            dateFormat: "Y-m-d",
            allowInput: false,
            inline: false,
            position: "auto",
            positionElement: filterFecha,
            animate: true,
            appendTo: document.body,
            showMonths: 1,
            enableTime: false,
            // NO mostrar doas de otros meses
            showOtherMonths: false,
            locale: {
                firstDayOfWeek: 1,
                weekdays: {
                    shorthand: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                    longhand: ['Domingo', 'Lunes', 'Martes', 'Miorcoles', 'Jueves', 'Viernes', 'Sobado']
                },
                months: {
                    shorthand: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                    longhand: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']
                }
            },
            // NO filtrar HASTA que se complete el rango (2 fechas)
            onChange: function(selectedDates, dateStr, instance) {
                console.log('?? Fechas seleccionadas:', selectedDates.length, dateStr);
                
                // Actualizar hidden input
                if (filterFechaValue) filterFechaValue.value = dateStr;
                
                // Actualizar texto del boton
                if (filterFechaText) {
                    if (dateStr && selectedDates.length === 2) {
                        const dates = dateStr.split(' to ');
                        filterFechaText.textContent = `${dates[0]} ? ${dates[1]}`;
                    } else if (dateStr && selectedDates.length === 1) {
                        filterFechaText.textContent = `${dateStr} (selecciona fin)`;
                    } else {
                        filterFechaText.textContent = 'Seleccionar fechas';
                    }
                }
                
                // FILTRAR SOLO cuando se seleccionen 2 fechas (rango completo)
                if (selectedDates.length === 2) {
                    console.log('? Rango completo seleccionado, filtrando...');
                    filterCategorias();
                }
            },
            onReady: function(selectedDates, dateStr, instance) {
                // FORZAR marcado moltiples veces para asegurar
                setTimeout(() => markMonthsWithProducts(), 10);
                setTimeout(() => markMonthsWithProducts(), 50);
                setTimeout(() => markMonthsWithProducts(), 100);
                setTimeout(() => startObserving(), 150);
            },
            onOpen: function() {
                console.log('?? Calendario abierto - LIMPIANDO filtros automoticamente');
                isCalendarOpen = true;
                filterFecha.classList.add('calendar-open');
                
                // ? LIMPIAR fechas automoticamente al abrir (como hacer click en "Limpiar")
                window.categoriasDatePicker.clear();
                
                // Limpiar valores
                if (filterFechaValue) filterFechaValue.value = '';
                if (filterFechaText) filterFechaText.textContent = 'Seleccionar fechas';
                
                // Re-cargar TODOS los categoroas (sin filtro de fecha)
                filterCategorias();
                
                // FORZAR marcado moltiples veces
                setTimeout(() => markMonthsWithProducts(), 10);
                setTimeout(() => markMonthsWithProducts(), 50);
                setTimeout(() => markMonthsWithProducts(), 100);
                setTimeout(() => startObserving(), 150);
            },
            onClose: function() {
                console.log('?? Calendario cerrado');
                isCalendarOpen = false;
                filterFecha.classList.remove('calendar-open');
                calendarObserver.disconnect();
            },
            onMonthChange: function() {
                // FORZAR marcado al cambiar mes
                setTimeout(() => markMonthsWithProducts(), 10);
                setTimeout(() => markMonthsWithProducts(), 50);
                setTimeout(() => markMonthsWithProducts(), 100);
            },
            onYearChange: function() {
                // FORZAR marcado al cambiar aoo
                setTimeout(() => markMonthsWithProducts(), 10);
                setTimeout(() => markMonthsWithProducts(), 50);
                setTimeout(() => markMonthsWithProducts(), 100);
            },
            onDayCreate: function(dObj, dStr, fp, dayElem) {
                // Marcar visualmente las fechas con categoroas
                const dateStr = dayElem.dateObj.toISOString().split('T')[0];
                if (window.categoriasDatesArray && window.categoriasDatesArray.includes(dateStr)) {
                    dayElem.classList.add('has-products');
                    dayElem.title = 'Hay categoroas en esta fecha';
                }
            }
        });
        
        // Funcion para marcar meses con categoroas
        function markMonthsWithProducts() {
            if (!window.categoriasDatesArray || window.categoriasDatesArray.length === 0) return;
            
            const calendarEl = document.querySelector('.flatpickr-calendar:not(.inline)');
            if (!calendarEl) return;
            
            // Obtener meses onicos de las fechas de categoroas
            const monthsWithProducts = new Set();
            window.categoriasDatesArray.forEach(dateStr => {
                const [year, month] = dateStr.split('-');
                monthsWithProducts.add(`${year}-${month}`);
            });
            
            // Agregar indicador al mes actual del calendario
            const currentMonthEl = calendarEl.querySelector('.flatpickr-current-month');
            if (currentMonthEl) {
                const yearInput = currentMonthEl.querySelector('.numInput');
                const monthSelect = currentMonthEl.querySelector('.flatpickr-monthDropdown-months');
                
                if (yearInput && monthSelect) {
                    const year = yearInput.value;
                    const month = String(monthSelect.selectedIndex + 1).padStart(2, '0');
                    const currentYearMonth = `${year}-${month}`;
                    
                    // Remover indicadores anteriores
                    const oldIndicator = currentMonthEl.querySelector('.month-has-products-indicator');
                    if (oldIndicator) oldIndicator.remove();
                    
                    // Agregar indicador si hay categoroas este mes (corculo verde como los doas)
                    if (monthsWithProducts.has(currentYearMonth)) {
                        const indicator = document.createElement('span');
                        indicator.className = 'month-has-products-indicator';
                        indicator.innerHTML = '<span class="green-dot"></span>';
                        indicator.title = 'Hay categoroas en este mes';
                        currentMonthEl.appendChild(indicator);
                    }
                    
                    // Hacer el aoo editable (NO readonly, NO convertir a texto)
                    if (yearInput && yearInput.type === 'number') {
                        // Mantener como number pero quitar las flechas con CSS
                        yearInput.removeAttribute('readonly');
                        yearInput.style.pointerEvents = 'auto';
                        
                        // Permitir que Flatpickr maneje el cambio de aoo automoticamente
                        // al cambiar de mes (diciembre -> enero = siguiente aoo)
                    }
                    
                    // Marcar opciones del dropdown con corculo verde
                    const options = monthSelect.querySelectorAll('option');
                    options.forEach((option, index) => {
                        const monthNum = String(index + 1).padStart(2, '0');
                        const yearMonth = `${year}-${monthNum}`;
                        
                        // Limpiar texto previo
                        let originalText = option.textContent
                            .replace(' ?', '').replace('?', '')
                            .replace(' ??', '').replace('??', '')
                            .replace(' ??', '').replace('??', '')
                            .replace(' ?', '').replace('?', '')
                            .trim();
                        
                        // Resetear estilos
                        option.style.fontWeight = '500';
                        
                        // Si hay categoroas, usar el caracter ? (corculo grande) que se ve mejor
                        if (monthsWithProducts.has(yearMonth)) {
                            // Usar espacio + caracter especial de corculo
                            option.textContent = originalText;
                            option.value = option.value; // Mantener el value
                            // Agregar un prefijo visual
                            option.textContent = '? ' + originalText;
                            option.setAttribute('data-has-products', 'true');
                            option.style.color = '#10b981'; // Todo el texto verde
                            option.style.fontWeight = '600';
                        } else {
                            option.textContent = originalText;
                            option.removeAttribute('data-has-products');
                            option.style.color = 'white';
                        }
                    });
                }
            }
            
            // Re-marcar todos los doas con categoroas (FORZAR)
            const days = calendarEl.querySelectorAll('.flatpickr-day:not(.flatpickr-disabled)');
            days.forEach(dayElem => {
                if (dayElem.dateObj) {
                    const dateStr = dayElem.dateObj.toISOString().split('T')[0];
                    if (window.categoriasDatesArray.includes(dateStr)) {
                        if (!dayElem.classList.contains('has-products')) {
                            dayElem.classList.add('has-products');
                            dayElem.title = 'Hay categoroas en esta fecha';
                        }
                    }
                }
            });
        }
        
        // Toggle calendario al hacer click en el boton
        filterFecha.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (isCalendarOpen) {
                window.categoriasDatePicker.close();
            } else {
                window.categoriasDatePicker.open();
            }
        });
        
        console.log('? Flatpickr inicializado en boton');
    }
    
    // 2. Flatpickr para filtro de fecha en modal movil - BOToN que abre calendario
    const filterFechaModal = document.getElementById('modal-filter-fecha');
    const filterFechaModalValue = document.getElementById('modal-filter-fecha-value');
    const filterFechaModalText = document.getElementById('modal-filter-fecha-text');
    
    if (filterFechaModal && typeof flatpickr !== 'undefined') {
        console.log('?? Inicializando Flatpickr en boton de fecha modal');
        
        // Crear input invisible para Flatpickr
        const hiddenInputModal = document.createElement('input');
        hiddenInputModal.type = 'text';
        hiddenInputModal.style.display = 'none';
        hiddenInputModal.id = 'flatpickr-hidden-input-modal';
        filterFechaModal.parentNode.appendChild(hiddenInputModal);
        
        // Variable para controlar si el calendario esto abierto
        let isModalCalendarOpen = false;
        
        // ? DECLARAR calendarObserverModal ANTES de Flatpickr
        const calendarObserverModal = new MutationObserver(function(mutations) {
            // Re-marcar inmediatamente cuando haya cualquier cambio
            const calendar = document.querySelector('.flatpickr-calendar:not(.inline)');
            if (calendar && window.categoriasDatesArray && window.categoriasDatesArray.length > 0) {
                const days = calendar.querySelectorAll('.flatpickr-day:not(.flatpickr-disabled)');
                days.forEach(dayElem => {
                    if (dayElem.dateObj) {
                        const dateStr = dayElem.dateObj.toISOString().split('T')[0];
                        if (window.categoriasDatesArray.includes(dateStr)) {
                            if (!dayElem.classList.contains('has-products')) {
                                dayElem.classList.add('has-products');
                                dayElem.title = 'Hay categoroas en esta fecha';
                            }
                        }
                    }
                });
            }
        });
        
        // ? DECLARAR startObservingModal ANTES de Flatpickr
        const startObservingModal = () => {
            const calendar = document.querySelector('.flatpickr-calendar:not(.inline)');
            if (calendar) {
                calendarObserverModal.observe(calendar, {
                    childList: true,
                    subtree: true,
                    attributes: true,
                    attributeFilter: ['class', 'aria-label']
                });
                
                // FORZAR marcado inmediato despuos de iniciar observacion
                if (typeof markMonthsWithProducts === 'function') {
                    markMonthsWithProducts();
                }
            }
        };
        
        // Inicializar Flatpickr en el input invisible
        window.categoriasDatePickerModal = flatpickr(hiddenInputModal, {
            mode: "range",
            dateFormat: "Y-m-d",
            allowInput: false,
            inline: false,
            position: "auto",
            positionElement: filterFechaModal,
            animate: true,
            appendTo: document.body,
            showMonths: 1,
            enableTime: false,
            // NO mostrar doas de otros meses
            showOtherMonths: false,
            locale: {
                firstDayOfWeek: 1,
                weekdays: {
                    shorthand: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                    longhand: ['Domingo', 'Lunes', 'Martes', 'Miorcoles', 'Jueves', 'Viernes', 'Sobado']
                },
                months: {
                    shorthand: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                    longhand: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']
                }
            },
            // NO filtrar HASTA que se complete el rango (2 fechas)
            onChange: function(selectedDates, dateStr, instance) {
                console.log('?? Fechas modal seleccionadas:', selectedDates.length, dateStr);
                
                // Actualizar hidden input
                if (filterFechaModalValue) filterFechaModalValue.value = dateStr;
                
                // Actualizar texto del boton modal SIN ICONOS
                if (filterFechaModalText) {
                    if (dateStr && selectedDates.length === 2) {
                        const dates = dateStr.split(' to ');
                        filterFechaModalText.textContent = `${dates[0]} ? ${dates[1]}`;
                    } else if (dateStr && selectedDates.length === 1) {
                        filterFechaModalText.textContent = `${dateStr} (selecciona fin)`;
                    } else {
                        filterFechaModalText.textContent = 'Seleccionar fechas';
                    }
                }
                
                // Sincronizar con desktop
                if (filterFechaValue) filterFechaValue.value = dateStr;
                if (filterFechaText && selectedDates.length === 2) {
                    const dates = dateStr.split(' to ');
                    filterFechaText.textContent = `${dates[0]} ? ${dates[1]}`;
                } else if (filterFechaText && selectedDates.length === 1) {
                    filterFechaText.textContent = `${dateStr} (selecciona fin)`;
                } else if (filterFechaText) {
                    filterFechaText.textContent = 'Seleccionar fechas';
                }
                
                // FILTRAR SOLO cuando se seleccionen 2 fechas (rango completo)
                if (selectedDates.length === 2) {
                    console.log('? Rango completo seleccionado en modal, filtrando...');
                    filterCategorias();
                }
            },
            onReady: function(selectedDates, dateStr, instance) {
                // FORZAR marcado moltiples veces
                setTimeout(() => markMonthsWithProducts(), 50);
                setTimeout(() => markMonthsWithProducts(), 100);
                setTimeout(() => markMonthsWithProducts(), 200);
                setTimeout(() => startObservingModal(), 250);
            },
            onOpen: function() {
                console.log('?? Calendario modal abierto - LIMPIANDO filtros automoticamente');
                isModalCalendarOpen = true;
                filterFechaModal.classList.add('calendar-open');
                
                // ? LIMPIAR fechas automoticamente al abrir (como hacer click en "Limpiar")
                window.categoriasDatePickerModal.clear();
                
                // Limpiar valores modal
                if (filterFechaModalValue) filterFechaModalValue.value = '';
                if (filterFechaModalText) filterFechaModalText.textContent = 'Seleccionar fechas';
                
                // Sincronizar limpieza con desktop
                if (filterFechaValue) filterFechaValue.value = '';
                if (filterFechaText) filterFechaText.textContent = 'Seleccionar fechas';
                
                // Re-cargar TODOS los categoroas (sin filtro de fecha)
                filterCategorias();
                
                // FORZAR marcado moltiples veces
                setTimeout(() => markMonthsWithProducts(), 50);
                setTimeout(() => markMonthsWithProducts(), 100);
                setTimeout(() => markMonthsWithProducts(), 200);
                setTimeout(() => startObservingModal(), 250);
            },
            onClose: function() {
                console.log('?? Calendario modal cerrado');
                isModalCalendarOpen = false;
                filterFechaModal.classList.remove('calendar-open');
                calendarObserverModal.disconnect();
            },
            onMonthChange: function() {
                // FORZAR marcado al cambiar mes
                setTimeout(() => markMonthsWithProducts(), 10);
                setTimeout(() => markMonthsWithProducts(), 50);
                setTimeout(() => markMonthsWithProducts(), 100);
            },
            onYearChange: function() {
                // FORZAR marcado al cambiar aoo
                setTimeout(() => markMonthsWithProducts(), 10);
                setTimeout(() => markMonthsWithProducts(), 50);
                setTimeout(() => markMonthsWithProducts(), 100);
            },
            onDayCreate: function(dObj, dStr, fp, dayElem) {
                // Marcar visualmente las fechas con categoroas - SOLO CLASE
                const dateStr = dayElem.dateObj.toISOString().split('T')[0];
                if (window.categoriasDatesArray && window.categoriasDatesArray.includes(dateStr)) {
                    dayElem.classList.add('has-products');
                    dayElem.title = 'Hay categoroas en esta fecha';
                }
            }
        });
        
        // Toggle calendario al hacer click en el boton
        filterFechaModal.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (isModalCalendarOpen) {
                window.categoriasDatePickerModal.close();
            } else {
                window.categoriasDatePickerModal.open();
            }
        });
        
        console.log('? Flatpickr modal inicializado en boton');
    }
    
    // 3. Agregar animaciones AOS a elementos
    const moduleHeader = document.querySelector('.admin-categorias-module .module-header');
    if (moduleHeader && typeof AOS !== 'undefined') {
        moduleHeader.setAttribute('data-aos', 'fade-down');
        
        // Animar filtros
        const filterGroups = document.querySelectorAll('.filter-group');
        filterGroups.forEach((group, index) => {
            group.setAttribute('data-aos', 'fade-up');
            group.setAttribute('data-aos-delay', (index * 50).toString());
        });
        
        // Refrescar AOS despuos de agregar atributos
        if (AOS.refresh) {
            AOS.refresh();
        }
    }
    
    console.log('? Libreroas modernas inicializadas en Categoroas');
    
    // ========================================
    // LISTENER PARA CAMBIOS DE TAMAoO (Mobile ? Desktop)
    // ========================================
    
    let resizeTimeout;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            const isMobileNow = window.innerWidth <= 768;
            const viewButtons = document.querySelectorAll('.view-btn');
            
            if (isMobileNow) {
                // Si cambio a movil, forzar grid y bloquear botones
                console.log('?? Cambio a movil detectado');
                window.categorias_currentView = 'grid';
                
                viewButtons.forEach(btn => {
                    if (btn.dataset.view === 'table') {
                        btn.disabled = true;
                        btn.style.opacity = '0.5';
                        btn.style.cursor = 'not-allowed';
                        btn.title = 'Vista tabla no disponible en movil';
                    }
                });
                
                // Forzar vista grid
                toggleView('grid');
            } else {
                // Si cambio a desktop, desbloquear botones
                console.log('?? Cambio a desktop detectado');
                
                viewButtons.forEach(btn => {
                    btn.disabled = false;
                    btn.style.opacity = '1';
                    btn.style.cursor = 'pointer';
                    btn.title = '';
                });
            }
        }, 250);
    });
    
    // ========================================
    // FIN LIBRERoAS MODERNAS
    // ========================================
    
    // Inicializar funciones de UI que antes estaban en DOMContentLoaded/load
    if (typeof initializeTableScroll === 'function') {
        initializeTableScroll();
    }
    if (typeof initializeDragScroll === 'function') {
        initializeDragScroll();
    }
    if (typeof setupDeleteModalBackdropClose === 'function') {
        setupDeleteModalBackdropClose();
    }
    
    // Funcion de debugging para verificar funciones disponibles
    window.debugProductsFunctions = function() {
        const functions = [
            'loadCategorias', 'loadTiposCategoria', 'filterCategorias', 'handleSearchInputCategorias', 
            'toggleView', 'showActionMenu', 'editCategoria', 'viewCategoria', 'deleteCategoria',
            'toggleProductStatus', 'updateCantidad', 'exportCategorias'
        ];
        
        const parentFunctions = ['showEditProductModal', 'showViewProductModal', 'showCreateCategoriaModal'];
        parentFunctions.forEach(func => {

        });
    };
}

// ? EXPONER LA FUNCIoN DE INICIALIZACIoN GLOBALMENTE
window.initializeProductsModule = initializeProductsModule;

// ? EJECUTAR INICIALIZACIoN INMEDIATAMENTE (dentro del eval())
// Esto asegura que se ejecute en el momento correcto, cuando el DOM ya esto listo
initializeProductsModule();

// NOTA: Al ejecutar dentro del eval(), la funcion se ejecuta en el momento exacto
// cuando todo el codigo esto definido y el contenedor ya tiene el HTML insertado

// Asegurar que las funciones eston disponibles globalmente de inmediato
window.loadCategorias = loadCategorias;
window.loadCategorias = loadCategorias;
window.loadTiposCategoria = loadTiposCategoria;
window.filterCategorias = filterCategorias;
window.handleSearchInputCategorias = handleSearchInputCategorias;
window.toggleCategoriaView = toggleCategoriaView;
window.showCategoriaActionMenu = showCategoriaActionMenu;
window.clearCategoriaSearch = clearCategoriaSearch;
window.clearAllCategoriaFilters = clearAllCategoriaFilters;
window.exportCategorias = exportCategorias;
window.showCategoriaReport = showCategoriaReport;
// window.editCategoria = editCategoria; // Se define mos adelante en seccion CRUD
// window.viewCategoria = viewCategoria; // Se define mos adelante en seccion CRUD
// window.deleteCategoria = deleteCategoria; // Se define mos adelante en seccion CRUD
window.toggleProductStatus = toggleProductStatus;
// window.changeCategoriaEstado = changeCategoriaEstado; // Se define mos adelante en seccion CRUD
window.updateCantidad = updateCantidad;
window.closeCantidadBubble = closeCantidadBubble; // Exponer funcion para cerrar burbuja
window.showDeleteConfirmation = showDeleteConfirmation;
window.closeDeleteConfirmation = closeDeleteConfirmation;
window.setupDeleteModalBackdropClose = setupDeleteModalBackdropClose;
window.confirmDelete = confirmDelete;
window.handleBulkCategoriaAction = handleBulkCategoriaAction;
window.createGridView = createGridView;
window.displayCategoriasGrid = displayCategoriasGrid;
window.closeCategoriasFloatingActions = closeCategoriaFloatingActionsAlias;
window.closeCategoriasFloatingActions = closeCategoriasFloatingActions;
window.createCategoriaAnimatedFloatingContainer = createCategoriaAnimatedFloatingContainer;
window.updateAnimatedButtonPositions = updateAnimatedButtonPositions;
window.forceCloseCategoriasFloatingActions = forceCloseCategoriasFloatingActions;
window.showImageFullSize = showImageFullSize;

// ============ FUNCIONES DE ESTADO PARA PRESERVACIoN ============

// Funcion para obtener la vista actual
window.getCurrentView = function() {
    const gridViewBtn = document.querySelector('[onclick="toggleCategoriaView\(\\'grid\\'\)"]');
    const tableViewBtn = document.querySelector('[onclick="toggleCategoriaView\(\\'table\\'\)"]');
    
    if (gridViewBtn && gridViewBtn.classList.contains('active')) {
        return 'grid';
    } else if (tableViewBtn && tableViewBtn.classList.contains('active')) {
        return 'table';
    }
    
    // Verificar por el contenido visible
    const gridContainer = document.querySelector('.categorias-grid');
    const tableContainer = document.querySelector('.categorias-table');
    
    if (gridContainer && gridContainer.style.display !== 'none') {
        return 'grid';
    } else if (tableContainer && tableContainer.style.display !== 'none') {
        return 'table';
    }
    
    return 'table'; // Default
};

// Funcion para obtener el tormino de bosqueda actual
window.getSearchTerm = function() {
    const searchInput = document.getElementById('search-categorias');
    return searchInput ? searchInput.value.trim() : '';
};

// Funcion para obtener los filtros actuales
window.getCurrentFilters = function() {
    const filters = {};
    
    if (typeof $ !== 'undefined') {
        const category = $('#filter-category').val();
        const status = $('#filter-status').val();
        
        if (category) filters.category = category;
        if (status !== '') filters.status = status;
    }
    
    return filters;
};

// Funcion para preservar scroll position
window.preserveScrollPosition = function() {
    const mainContent = document.querySelector('.tab-content') || document.body;
    return {
        top: mainContent.scrollTop,
        left: mainContent.scrollLeft
    };
};

// Funcion para restaurar scroll position
window.restoreScrollPosition = function(position) {
    if (!position) return;
    
    const mainContent = document.querySelector('.tab-content') || document.body;
    setTimeout(() => {
        mainContent.scrollTo({
            top: position.top,
            left: position.left,
            behavior: 'auto'
        });
    }, 100);
};

// Hacer currentPage accesible globalmente para preservacion de estado
window.currentPage = currentPage;

// ============ FUNCIONES DE DESTACADO Y ANIMACIONES ============

// Funcion de destacado eliminada para evitar problemas visuales


// Sistema de loading overlay y actualizacion forzada eliminados

// ============ FUNCIONES DE ELIMINAR Y TOGGLE STATUS ============

// Funcion para mostrar burbuja de confirmacion de eliminacion
function showDeleteConfirmation(productId, productName) {
    console.log('??? showDeleteConfirmation llamada:', productId, productName);
    
    // Verificar si ya existe un modal
    const existingOverlay = document.querySelector('.delete-confirmation-overlay');
    if (existingOverlay) {
        console.log('? Modal ya existe, eliminondolo primero');
        existingOverlay.remove();
    }
    
    // Crear overlay con estilos profesionales
    const overlay = document.createElement('div');
    overlay.className = 'delete-confirmation-overlay';
    console.log('? Overlay creado');
    
    overlay.innerHTML = `
        <div class="delete-confirmation-modal">
            <div class="delete-modal-header">
                <h3><i class="fas fa-exclamation-triangle"></i> Confirmar Eliminacion</h3>
            </div>
            <div class="delete-modal-body">
                <div class="warning-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <p>Para eliminar el categoroa <strong>"${productName}"</strong>, escribe la palabra <strong>"eliminar"</strong> en el campo de abajo:</p>
                
                <input type="text" id="deleteConfirmInput" class="confirmation-input" placeholder="Escribe 'eliminar' para confirmar" autocomplete="off">
                <div id="deleteError" class="delete-error">
                    Por favor escribe exactamente "eliminar" para confirmar
                </div>
            </div>
            <div class="delete-modal-footer">
                <button type="button" class="btn-cancel-delete" onclick="closeDeleteConfirmation()">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn-confirm-delete" onclick="confirmDelete(${productId}, '${productName.replace(/'/g, "\\'")}')">
                    <i class="fas fa-trash"></i> Eliminar Categoroa
                </button>
            </div>
        </div>
    `;
    
    // Agregar estilos profesionales para el modal de delete
    overlay.style.cssText = `
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        height: 100% !important;
        background: rgba(0, 0, 0, 0.5) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        z-index: 1000002 !important;
        opacity: 1 !important;
        visibility: visible !important;
    `;
    
    const modal = overlay.querySelector('.delete-confirmation-modal');
    modal.style.cssText = `
        border-radius: 12px;
        padding: 0;
        max-width: 500px;
        width: 90%;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
        animation: modalSlideIn 0.3s ease-out;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.1);
    `;
    
    // Estilos para elementos internos
    const style = document.createElement('style');
    style.textContent = `
        @keyframes modalSlideIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .delete-modal-header {
            background: #1e293b;
            color: white;
            padding: 1.5rem 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }
        .delete-confirmation-modal h3 {
            color: white;
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }
        .delete-modal-body {
            padding: 2rem;
            text-align: center;
        }
        .warning-icon {
            font-size: 3rem;
            color: #dc2626;
            margin-bottom: 1rem;
            display: block;
            background: rgba(220, 38, 38, 0.1);
            padding: 1rem;
            border-radius: 50%;
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid rgba(220, 38, 38, 0.2);
        }
        .delete-confirmation-modal p {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 1.5rem;
            font-size: 1rem;
            line-height: 1.5;
        }
        .confirmation-input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.95rem;
            margin-bottom: 0.5rem;
            text-align: center;
            font-weight: 500;
            background-color: #ffffff;
            transition: all 0.3s ease;
            color: #151c32ff;
            box-sizing: border-box;
        }
        .confirmation-input:focus {
            outline: none;
            border-color: #1e293b;
            box-shadow: 0 0 0 3px rgba(30, 41, 59, 0.15);
        }
        .delete-error {
            color: #dc2626;
            font-size: 0.875rem;
            margin-bottom: 1rem;
            font-weight: 500;
            display: none;
            padding: 0.75rem;
            background: rgba(220, 38, 38, 0.1);
            border: 1px solid rgba(220, 38, 38, 0.2);
            border-radius: 6px;
        }
        .delete-modal-footer {
            padding: 1.5rem 2rem;
            background: #1e293b;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: center;
            gap: 0.75rem;
        }
        .btn-confirm-delete {
            background: #dc2626;
            color: white;
            border: 2px solid #dc2626;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 120px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            justify-content: center;
        }
        .btn-confirm-delete:hover {
            background: #b91c1c;
            border-color: #b91c1c;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.35);
        }
        .btn-cancel-delete {
            background: transparent;
            color: rgba(255, 255, 255, 0.7);
            border: 2px solid #e2e8f0;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 120px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            justify-content: center;
        }
        .btn-cancel-delete:hover {
            background: #1e293b;
            border-color: #1e293b;
            color: white;
            box-shadow: 0 4px 12px rgba(30, 41, 59, 0.2);
        }
    `;
    document.head.appendChild(style);
    
    console.log('?? Estilos agregados');
    
    // Agregar al DOM
    document.body.appendChild(overlay);
    console.log('?? Modal agregado al DOM');
    
    // Forzar reflow para que las animaciones funcionen
    overlay.offsetHeight;
    
    // Agregar clase 'show' para activar animaciones CSS
    requestAnimationFrame(() => {
        overlay.classList.add('show');
        
        // Tambion agregar .show al modal interno
        const deleteModal = overlay.querySelector('.delete-confirmation-modal');
        if (deleteModal) {
            deleteModal.classList.add('show');
        }
        
        console.log('? Clase "show" agregada - animacion iniciada');
    });
    
    // Focus en el input despuos de la animacion
    setTimeout(() => {
        const input = document.getElementById('deleteConfirmInput');
        if (input) {
            input.focus();
            console.log('?? Focus en input');
        }
    }, 350);
    
    // Permitir confirmar con Enter
    const input = document.getElementById('deleteConfirmInput');
    if (input) {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                confirmDelete(productId, productName);
            }
        });
    }
    
    // Permitir cerrar con ESC
    const handleEsc = (e) => {
        if (e.key === 'Escape') {
            closeDeleteConfirmation();
            document.removeEventListener('keydown', handleEsc);
        }
    };
    document.addEventListener('keydown', handleEsc);
    
    // Cerrar al hacer click en el overlay (fondo oscuro)
    overlay.addEventListener('click', function(e) {
        // Solo cerrar si se hace click directamente en el overlay, no en el modal
        if (e.target === overlay) {
            console.log('?? Click en overlay detectado - cerrando modal');
            closeDeleteConfirmation();
        }
    });
    
    // Prevenir que clicks dentro del modal cierren el overlay
    const deleteModal = overlay.querySelector('.delete-confirmation-modal');
    if (deleteModal) {
        deleteModal.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
}

// Funcion para cerrar la confirmacion con animacion
function closeDeleteConfirmation() {
    console.log('?? Cerrando modal de eliminacion con animacion');
    const overlay = document.querySelector('.delete-confirmation-overlay');
    if (overlay) {
        // Agregar clases de salida para animacion
        overlay.classList.remove('show');
        overlay.classList.add('hide');
        
        const deleteModal = overlay.querySelector('.delete-confirmation-modal');
        if (deleteModal) {
            deleteModal.classList.add('hide');
        }
        
        // Remover del DOM despuos de que termine la animacion
        setTimeout(() => {
            overlay.remove();
            console.log('? Modal eliminado del DOM');
        }, 250); // Duracion de la animacion fadeOut actualizada
    }
}

// Cerrar modal al hacer click en el backdrop
function setupDeleteModalBackdropClose() {
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-confirmation-overlay')) {
            closeDeleteConfirmation();
        }
    });
}

// Cerrar modal al hacer click en el backdrop
function setupDeleteModalBackdropClose() {
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-confirmation-overlay')) {
            closeDeleteConfirmation();
        }
    });
}

// Funcion para confirmar eliminacion
function confirmDelete(productId, productName) {
    const input = document.getElementById('deleteConfirmInput');
    const errorDiv = document.getElementById('deleteError');
    
    if (input.value.toLowerCase().trim() !== 'eliminar') {
        errorDiv.style.display = 'block';
        input.style.borderColor = '#dc2626';
        input.focus();
        return;
    }
    
    // Proceder con eliminacion
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', productId);
    
    fetch(`${CONFIG.apiUrl}`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        closeDeleteConfirmation();
        
        if (data.success) {
            // Mostrar notificacion de oxito
            if (typeof showNotification === 'function') {
                // showNotification(`Categoroa "${productName}" eliminado exitosamente`, 'success');
            }
            
            // Usar actualizacion suave si esto disponible
            if (window.smoothTableUpdater) {
                console.log('?? Usando actualizacion suave para eliminar categoroa:', productId);
                window.smoothTableUpdater.removeProduct(productId);
            } else {
                console.log('?? SmoothTableUpdater no disponible - usando recarga tradicional');
                // Actualizar lista inmediatamente sin reload
                loadCategorias(true);
            }
        } else {
            if (typeof showNotification === 'function') {
                // showNotification('Error al eliminar categoroa: ' + (data.error || 'Error desconocido'), 'error');
            } else {
                // alert('Error al eliminar categoroa: ' + (data.error || 'Error desconocido'));
            }
        }
    })
    .catch(error => {
        closeDeleteConfirmation();
        if (typeof showNotification === 'function') {
            // showNotification('Error de conexion al eliminar categoroa', 'error');
        } else {
            // alert('Error de conexion al eliminar categoroa');
        }
    });
}

// Funcion para alternar estado del categoroa (activo/inactivo)
function toggleProductStatus(productId, currentStatus) {
    const newStatus = currentStatus ? 0 : 1;
    
    const formData = new FormData();
    formData.append('action', 'toggle_status');
    formData.append('id', productId);
    formData.append('status', newStatus);
    
    fetch(`${CONFIG.apiUrl}`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Usar actualizacion suave si esto disponible
            if (window.smoothTableUpdater && data.product) {
                console.log('?? Usando actualizacion suave para cambiar estado del categoroa:', productId);
                window.smoothTableUpdater.updateSingleProduct(data.product);
            } else {
                console.log('?? SmoothTableUpdater no disponible o categoroa no retornado - usando recarga tradicional');
                // Actualizar lista inmediatamente sin reload
                loadCategorias(true);
            }
        } else {
            if (typeof showNotification === 'function') {
                // showNotification('Error al cambiar estado: ' + (data.error || 'Error desconocido'), 'error');
            } else {
                // alert('Error al cambiar estado: ' + (data.error || 'Error desconocido'));
            }
        }
    })
    .catch(error => {
        if (typeof showNotification === 'function') {
            // showNotification('Error de conexion al cambiar estado', 'error');
        } else {
            // alert('Error de conexion al cambiar estado');
        }
    });
}

// Funcion wrapper para eliminar categoroa
// deleteCategoria esto definida al final del archivo en la seccion CRUD
// Esta funcion wrapper se mantiene por compatibilidad
function deleteCategoriaWrapper(productId, productName) {
    console.log('?? deleteCategoria wrapper llamada:', productId, productName);
    // Llamar a la funcion CRUD principal
    if (typeof window.deleteCategoria === 'function') {
        window.deleteCategoria(productId, productName || 'Categoroa');
    }
}

// ============ FUNCIoN PARA MOSTRAR IMAGEN EN TAMAoO REAL ============

function showImageFullSize(imageUrl, productName) {
    console.log('??? Mostrando imagen en tamaoo real:', imageUrl);
    
    // Crear overlay con fondo transparente
    const overlay = document.createElement('div');
    overlay.className = 'image-fullsize-overlay';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.15);
        z-index: 1000005;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
        backdrop-filter: blur(3px);
        cursor: zoom-out;
    `;
    
    // Crear imagen directamente sin contenedor
    const img = document.createElement('img');
    img.src = imageUrl;
    img.alt = productName || 'Categoroa';
    img.style.cssText = `
        max-width: 95vw;
        max-height: 95vh;
        object-fit: contain;
        cursor: zoom-out;
        opacity: 0;
        transition: opacity 0.3s ease;
    `;
    
    // Funcion para cerrar con animacion
    const closeModal = () => {
        overlay.style.opacity = '0';
        img.style.opacity = '0';
        
        setTimeout(() => {
            if (overlay.parentNode) {
                overlay.parentNode.removeChild(overlay);
            }
        }, 300);
    };
    
    // Cerrar al hacer click en cualquier parte
    overlay.addEventListener('click', closeModal);
    
    // Cerrar con ESC
    const handleEsc = (e) => {
        if (e.key === 'Escape') {
            closeModal();
            document.removeEventListener('keydown', handleEsc);
        }
    };
    document.addEventListener('keydown', handleEsc);
    
    // Ensamblar elementos
    overlay.appendChild(img);
    
    // Agregar al DOM
    document.body.appendChild(overlay);
    
    // Animar entrada
    requestAnimationFrame(() => {
        overlay.style.opacity = '1';
        
        // Esperar un frame mos para que la imagen cargue
        setTimeout(() => {
            img.style.opacity = '1';
        }, 50);
    });
    
    // Manejar error de carga de imagen
    img.onerror = () => {
        img.src = AppConfig ? AppConfig.getImageUrl('default-product.jpg') : '/fashion-master/public/assets/img/default-product.jpg';
    };
}

// Hacer la funcion global
window.showImageFullSize = showImageFullSize;

// ============ FIN FUNCIONES DE ELIMINAR Y TOGGLE STATUS ============

// Sistema de limpieza automotica para evitar menos huorfanos
setInterval(() => {
    const orphanedContainers = document.querySelectorAll('.animated-floating-container');
    if (orphanedContainers.length > 1) {
        // Si hay mos de un contenedor, algo esto mal, limpiar todos
        orphanedContainers.forEach(container => {
            try {
                container.remove();
            } catch (e) {
                console.warn('Error limpiando contenedor huorfano:', e);
            }
        });
        // Resetear variables globales
        categorias_activeFloatingContainer = null;
        categorias_centerButton = null;
        categorias_floatingButtons = [];
        categorias_activeCategoriaId = null;
        categorias_isAnimating = false;
    }
}, 2000); // Verificar cada 2 segundos

// Limpiar al cambiar de pogina o recargar
window.addEventListener('beforeunload', () => {
    forceCloseCategoriasFloatingActions();
});

// ===== FUNCIONALIDAD DE SCROLL MEJORADO PARA LA TABLA =====
function initializeTableScroll() {
    const scrollableTable = document.querySelector('.scrollable-table');
    if (!scrollableTable) return;
    
    let scrollTimeout;
    
    // Detectar cuando se esto haciendo scroll
    scrollableTable.addEventListener('scroll', function() {
        // Agregar clase durante el scroll
        this.classList.add('scrolling');
        
        // Remover clase despuos de que termine el scroll
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(() => {
            this.classList.remove('scrolling');
        }, 150);
        
        // Detectar si tiene contenido que requiere scroll
        if (this.scrollHeight > this.clientHeight || this.scrollWidth > this.clientWidth) {
            this.classList.add('has-scroll');
        } else {
            this.classList.remove('has-scroll');
        }
    });
    
    // Verificar inicialmente si necesita scroll
    const checkScroll = () => {
        if (scrollableTable.scrollHeight > scrollableTable.clientHeight || 
            scrollableTable.scrollWidth > scrollableTable.clientWidth) {
            scrollableTable.classList.add('has-scroll');
        } else {
            scrollableTable.classList.remove('has-scroll');
        }
    };
    
    // Verificar cuando cambie el contenido
    const observer = new MutationObserver(checkScroll);
    observer.observe(scrollableTable, {
        childList: true,
        subtree: true,
        attributes: true,
        attributeFilter: ['style']
    });
    
    // Verificar al redimensionar
    window.addEventListener('resize', checkScroll);
    
    // Verificar inicialmente
    setTimeout(checkScroll, 100);
}

// Inicializar el scroll mejorado cuando se carga la tabla
// ? DESACTIVADO: No usar DOMContentLoaded porque se ejecuta en eval() cada vez que se carga el modulo
// document.addEventListener('DOMContentLoaded', initializeTableScroll);
// En su lugar, initializeProductsModule() ya llama a esto directamente

// Tambion inicializar cuando se actualiza la tabla
const originalDisplayProducts = displayCategorias;
if (typeof displayCategorias === 'function') {
    displayCategorias = function(...args) {
        const result = originalDisplayProducts.apply(this, args);
        setTimeout(initializeTableScroll, 100);
        return result;
    };
}

// ===== SISTEMA DE DRAG-SCROLL PARA LA TABLA (COMO TOUCH) =====
function initializeDragScroll() {
    const scrollableTable = document.querySelector('.scrollable-table');
    if (!scrollableTable) return;
    
    let isDragging = false;
    let startX = 0;
    let startY = 0;
    let scrollLeft = 0;
    let scrollTop = 0;
    let velocityX = 0;
    let velocityY = 0;
    let lastX = 0;
    let lastY = 0;
    let lastTime = 0;
    
    // Iniciar drag
    scrollableTable.addEventListener('mousedown', function(e) {
        // No aplicar drag si se esto clickeando en un boton, input o link
        if (e.target.closest('button, a, input, select, textarea, .btn-menu, .categoria-card-btn')) {
            return;
        }
        
        isDragging = true;
        startX = e.pageX - scrollableTable.offsetLeft;
        startY = e.pageY - scrollableTable.offsetTop;
        scrollLeft = scrollableTable.scrollLeft;
        scrollTop = scrollableTable.scrollTop;
        lastX = e.pageX;
        lastY = e.pageY;
        lastTime = Date.now();
        velocityX = 0;
        velocityY = 0;
        
        scrollableTable.classList.add('dragging');
        
        // Prevenir seleccion de texto completamente
        e.preventDefault();
        document.body.style.userSelect = 'none';
        document.body.style.webkitUserSelect = 'none';
        document.body.style.mozUserSelect = 'none';
        document.body.style.msUserSelect = 'none';
    });
    
    // Mover durante drag
    scrollableTable.addEventListener('mousemove', function(e) {
        if (!isDragging) return;
        
        e.preventDefault();
        
        const currentTime = Date.now();
        const deltaTime = currentTime - lastTime;
        
        const x = e.pageX - scrollableTable.offsetLeft;
        const y = e.pageY - scrollableTable.offsetTop;
        
        const walkX = (x - startX);
        const walkY = (y - startY);
        
        // Calcular velocidad para momentum scrolling
        if (deltaTime > 0) {
            velocityX = (e.pageX - lastX) / deltaTime;
            velocityY = (e.pageY - lastY) / deltaTime;
        }
        
        scrollableTable.scrollLeft = scrollLeft - walkX;
        scrollableTable.scrollTop = scrollTop - walkY;
        
        lastX = e.pageX;
        lastY = e.pageY;
        lastTime = currentTime;
    });
    
    // Finalizar drag
    const endDrag = function(e) {
        if (!isDragging) return;
        
        isDragging = false;
        scrollableTable.classList.remove('dragging');
        
        // Restaurar seleccion de texto
        document.body.style.userSelect = '';
        document.body.style.webkitUserSelect = '';
        document.body.style.mozUserSelect = '';
        document.body.style.msUserSelect = '';
        
        // Aplicar momentum scrolling (inercia)
        const friction = 0.95;
        const minVelocity = 0.1;
        
        function momentum() {
            if (Math.abs(velocityX) < minVelocity && Math.abs(velocityY) < minVelocity) {
                return;
            }
            
            scrollableTable.scrollLeft -= velocityX * 10;
            scrollableTable.scrollTop -= velocityY * 10;
            
            velocityX *= friction;
            velocityY *= friction;
            
            requestAnimationFrame(momentum);
        }
        
        // Solo aplicar momentum si la velocidad es significativa
        if (Math.abs(velocityX) > minVelocity || Math.abs(velocityY) > minVelocity) {
            momentum();
        }
    };
    
    scrollableTable.addEventListener('mouseup', endDrag);
    scrollableTable.addEventListener('mouseleave', endDrag);
    
    // Prevenir click accidental despuos de drag
    scrollableTable.addEventListener('click', function(e) {
        if (Math.abs(velocityX) > 0.5 || Math.abs(velocityY) > 0.5) {
            e.stopPropagation();
            e.preventDefault();
        }
    }, true);
}

// Inicializar drag-scroll cuando carga el DOM
// ? DESACTIVADO: No usar DOMContentLoaded/load porque se acumulan event listeners
// document.addEventListener('DOMContentLoaded', function() {
//     initializeDragScroll();
// });

// window.addEventListener('load', function() {
//     initializeDragScroll();
// });
// En su lugar, initializeProductsModule() llama a initializeDragScroll() directamente

// ===== FUNCIoN DE DESTRUCCIoN DEL MoDULO DE CATEGORoAS =====
window.destroyCategoroasModule = function() {
    console.log('??? Destruyendo modulo de categoroas...');
    
    try {
        // 1. Limpiar variable de estado de carga
        if (typeof isLoading !== 'undefined') {
            isLoading = false;
        }
        
        // 2. Limpiar arrays de datos
        if (typeof categorias !== 'undefined') {
            categorias = [];
        }
        
        // 3. Resetear paginacion
        if (typeof currentPage !== 'undefined') {
            currentPage = 1;
        }
        if (typeof totalPages !== 'undefined') {
            totalPages = 1;
        }
        
        // 4. Limpiar event listeners clonando elementos
        const searchInput = document.getElementById('search-categorias');
        if (searchInput && searchInput.parentNode) {
            const newSearch = searchInput.cloneNode(true);
            searchInput.parentNode.replaceChild(newSearch, searchInput);
        }
        
        const filterCategory = document.getElementById('filter-category');
        if (filterCategory && filterCategory.parentNode) {
            const newFilter = filterCategory.cloneNode(true);
            filterCategory.parentNode.replaceChild(newFilter, filterCategory);
        }
        
        const filterStatus = document.getElementById('filter-status');
        if (filterStatus && filterStatus.parentNode) {
            const newFilterStatus = filterStatus.cloneNode(true);
            filterStatus.parentNode.replaceChild(newFilterStatus, filterStatus);
        }
        
        const filterStock = document.getElementById('filter-stock');
        if (filterStock && filterStock.parentNode) {
            const newFilterStock = filterStock.cloneNode(true);
            filterStock.parentNode.replaceChild(newFilterStock, filterStock);
        }
        
        // 5. Limpiar modales de categoroas
        const productModals = document.querySelectorAll('.product-view-modal, .product-modal, [id*="product-modal"]');
        productModals.forEach(modal => {
            modal.remove();
        });
        
        // 6. Limpiar burbujas flotantes de stock Y contenedores flotantes de categoroas SOLAMENTE
        const stockBubbles = document.querySelectorAll('.stock-update-bubble');
        stockBubbles.forEach(bubble => {
            bubble.remove();
        });
        
        // Limpiar SOLO los contenedores flotantes que pertenecen a categoroas
        if (categorias_activeFloatingContainer && document.contains(categorias_activeFloatingContainer)) {
            categorias_activeFloatingContainer.remove();
        }
        
        // Resetear variables flotantes de categoroas
        categorias_activeFloatingContainer = null;
        categorias_centerButton = null;
        categorias_floatingButtons = [];
        categorias_activeCategoriaId = null;
        categorias_isAnimating = false;
        
        // 7. Limpiar confirmaciones de eliminacion
        const deleteConfirmations = document.querySelectorAll('.delete-confirmation-overlay');
        deleteConfirmations.forEach(confirmation => {
            confirmation.remove();
        });
        
        // 8. Limpiar el tbody de la tabla
        const tbody = document.getElementById('categorias-table-body');
        if (tbody) {
            tbody.innerHTML = '';
        }
        
        // 9. RESETEAR VISTA A TABLA (estado inicial)
        console.log('?? Reseteando vista a tabla (estado inicial)...');
        
        // Remover vista grid si existe
        const gridContainer = document.querySelector('.categorias-grid');
        if (gridContainer) {
            gridContainer.remove();
        }
        
        // Asegurar que la tabla esto visible
        const tableContainer = document.querySelector('.data-table-wrapper');
        if (tableContainer) {
            tableContainer.style.display = 'block';
        }
        
        // Resetear botones de vista
        const viewButtons = document.querySelectorAll('.view-btn');
        viewButtons.forEach(btn => {
            btn.classList.remove('active');
            if (btn.dataset.view === 'table') {
                btn.classList.add('active');
            }
        });
        
        // Limpiar localStorage de vista
        try {
            localStorage.removeItem('categoroas_view_mode');
        } catch (e) {}
        
        console.log('? Vista reseteada a tabla');
        
        // 10. Remover clases de body que puedan interferir
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
        
        console.log('? Modulo de categoroas destruido correctamente');
        
    } catch (error) {
        console.error('? Error al destruir modulo de categoroas:', error);
    }
};

// ============================================================
// FUNCIONES CRUD PARA CATEGORoAS
// ============================================================
// NOTA: Las funciones showCreateCategoriaModal, editCategoria, verCategoria
// eston definidas en admin.php y se exponen globalmente.
// Aquo solo definimos funciones auxiliares si son necesarias.

// Funcion para eliminar categoroa con confirmacion
async function deleteCategoria(id, nombre) {
    console.log('??? Intentando eliminar categoroa ID:', id);
    
    if (!confirm(`oEstos seguro de eliminar la categoroa "${nombre}"?\n\nEsta accion no se puede deshacer.`)) {
        return;
    }
    
    try {
        const response = await fetch(`${CONFIG.apiUrl}?action=delete&id=${id}`, {
            method: 'DELETE'
        });
        
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.error || 'Error al eliminar categoroa');
        }
        
        // Recargar lista
        loadCategorias();
        
        // Mostrar mensaje de oxito
        alert('? Categoroa eliminada exitosamente');
        
    } catch (error) {
        console.error('? Error al eliminar categoroa:', error);
        alert('Error al eliminar la categoroa: ' + error.message);
    }
}
window.deleteCategoria = deleteCategoria;

// Funcion para cambiar estado de categoroa
async function changeCategoriaEstado(id) {
    console.log('?? Cambiando estado de categoroa ID:', id);
    
    try {
        const response = await fetch(`${CONFIG.apiUrl}?action=toggle_status&id=${id}`, {
            method: 'POST'
        });
        
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.error || 'Error al cambiar estado');
        }
        
        // Recargar lista
        loadCategorias();
        
        console.log('? Estado cambiado exitosamente');
        
    } catch (error) {
        console.error('? Error al cambiar estado:', error);
        alert('Error al cambiar el estado: ' + error.message);
    }
}
window.changeCategoriaEstado = changeCategoriaEstado;

// Funcion auxiliar para actualizar una categoroa individual en la tabla (llamada desde el modal)
async function updateSingleCategoria(id, categoriaData) {
    console.log('?? Actualizando categoroa ID:', id, 'con datos:', categoriaData);
    
    // Recargar toda la lista (mos simple y seguro)
    if (typeof loadCategorias === 'function') {
        loadCategorias();
    }
}
window.updateSingleCategoria = updateSingleCategoria;

// Funcion auxiliar para recargar datos (llamada desde el modal)
function loadCategoriasData() {
    console.log('?? Recargando datos de categoroas');
    
    if (typeof loadCategorias === 'function') {
        loadCategorias();
    }
}
window.loadCategoriasData = loadCategoriasData;

// ============================================================
// FIN DE FUNCIONES CRUD
// ============================================================

</script>

<style>
/* ===== FORZAR COLOR BLANCO EN BOTONES DEL HEADER - MoXIMA PRIORIDAD ===== */
.module-act#fffffffftn-modern,
.module-actions .btn-modern.btn-primary,
.module-actions .btn-modern.btn-secondary,
.module-actions .btn-modern.btn-info,
.module-actions button {
    color: #ffffff !important;
}

.module-actions .btn-modern i,
.module-actions .btn-modern span,
.module-actions .btn-modern.btn-primary i,
.module-actions .btn-modern.btn-primary span,
.module-actions .btn-modern.btn-secondary i,
.module-actions .btn-modern.btn-secondary span,
.module-actions .btn-modern.btn-info i,
.module-actions .btn-modern.btn-info span,
.module-actions button i,
.module-actions button span {
    color: #ffffff !important;
}

@media (max-width: 768px) {
    .module-actions .btn-modern,
    .module-actions .btn-modern *,
    .module-actions button,
    .module-actions button * {
        color: #ffffff !important;
    }
    
    /* Ocultar SOLO filtros en movil, mantener header visible */
    .module-filters {
        display: none !important;
    }
    
    /* Ocultar botones de vista (tabla/grid) y acciones en lote en movil */
    .table-actions {
        display: none !important;
    }
}

/* ===== ESTILOS PARA BOToN DE FECHA FLATPICKR - MISMO ESTILO QUE SELECT ===== */
#filter-fecha,
#modal-filter-fecha {
    /* Mismo estilo que .filter-select - fondo oscuro con texto blanco */
    padding: 0.5rem 1rem;
    border: 1px solid #334155;
    border-radius: 8px;
    font-size: 0.875rem;
    background: #0f162b !important; /* Fondo oscuro como los select */
    color: #ffffff !important; /* Texto blanco */
    transition: all 0.2s ease-in-out;
    text-align: left;
    cursor: pointer;
    display: block;
    width: 100%;
    font-family: "Inter", "Segoe UI", -apple-system, BlinkMacSystemFont, sans-serif;
}

#filter-fecha:hover,
#modal-filter-fecha:hover {
    border-color: #2463eb;
}

#filter-fecha:focus,
#modal-filter-fecha:focus,
#filter-fecha.calendar-open,
#modal-filter-fecha.calendar-open {
    outline: none;
    border-color: #1e40af;
    box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
}

#filter-fecha-text,
#modal-filter-fecha-text {
    font-size: 0.875rem;
    color: #ffffff !important; /* Texto blanco */
}

/* ASEGURAR texto blanco en movil */
@media (max-width: 768px) {
    #filter-fecha,
    #modal-filter-fecha {
        background: #2c3e50 !important;
        color: #ffffff !important;
    }
    
    #filter-fecha-text,
    #modal-filter-fecha-text {
        color: #ffffff !important;
    }
}

/* Indicador de mes con categoroas - mismo estilo que los doas */
.month-has-products-indicator {
    display: inline-flex;
    align-items: center;
    margin-left: 8px;
    position: relative;
}

.month-has-products-indicator .green-dot {
    display: inline-block;
    width: 6px;
    height: 6px;
    background: rgba(182, 185, 16, 0) !important;
    border-radius: 50%;
    box-shadow: 0 0 6px rgba(16, 185, 129, 0);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { 
        opacity: 1;
        transform: scale(1);
    }
    50% { 
        opacity: 0.7;
        transform: scale(1.1);
    }
}

/* Flatpickr con colores de la paleta del admin */
.flatpickr-calendar {
    background: #1e293b;
    border: 1px solid #334155;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
    border-radius: 12px;
    animation: flatpickrFadeIn 0.3s ease;
    z-index: 99999 !important; /* Asegurar que aparezca encima de todo */
}

/* Mejorar posicion en movil - SIN SCROLL HORIZONTAL */
@media (max-width: 768px) {
    .flatpickr-calendar {
        max-width: calc(100vw - 20px) !important; /* 10px de margen a cada lado */
        width: auto !important;
        left: 50% !important;
        transform: translateX(-50%) !important;
        margin-top: 10px;
    }
    
    /* Ajustar header en movil */
    .flatpickr-months .flatpickr-prev-month {
        left: 8px !important;
        width: 32px !important;
        height: 32px !important;
    }
    
    .flatpickr-months .flatpickr-next-month {
        right: 8px !important;
        width: 32px !important;
        height: 32px !important;
    }
    
    .flatpickr-current-month {
        padding: 0 46px 0 42px !important; /* Menos padding en movil */
        gap: 6px !important;
    }
    
    .flatpickr-current-month .flatpickr-monthDropdown-months {
        min-width: 85px !important;
        width: 85px !important;
        font-size: 12px !important;
        padding: 6px 8px !important;
        margin: 0 4px 0 0 !important;
    }
    
    .flatpickr-current-month .numInputWrapper {
        min-width: 55px !important;
        width: 55px !important;
        font-size: 12px !important;
        padding: 6px 8px !important;
    }
    
    .flatpickr-calendar.arrowTop::before,
    .flatpickr-calendar.arrowTop::after {
        left: 50% !important;
        transform: translateX(-50%);
    }
}

@keyframes flatpickrFadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.flatpickr-months {
    background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
    border-radius: 12px 12px 0 0;
    padding: 12px 16px;
    position: relative;
}

/* Contenedor del mes actual */
.flatpickr-months .flatpickr-month {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    height: 40px !important;
    position: relative;
    width: 100%;
}

/* Boton flecha IZQUIERDA - Posicion absoluta */
.flatpickr-months .flatpickr-prev-month {
    position: absolute !important;
    left: 12px !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
    padding: 8px !important;
    cursor: pointer;
    z-index: 3;
    width: 36px !important;
    height: 36px !important;
}

/* Boton flecha DERECHA - Posicion absoluta con mos espacio */
.flatpickr-months .flatpickr-next-month {
    position: absolute !important;
    right: 16px !important; /* Mos espacio a la derecha */
    top: 50% !important;
    transform: translateY(-50%) !important;
    padding: 8px !important;
    cursor: pointer;
    z-index: 3;
    width: 36px !important;
    height: 36px !important;
}

/* Contenedor central con mes y aoo - CENTRADO con mos padding derecho */
.flatpickr-current-month {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 8px !important; /* Separacion entre mes y aoo reducida */
    width: 100% !important;
    color: white;
    font-weight: 600;
    height: 100%;
    position: relative;
    z-index: 2;
    padding: 0 56px 0 52px !important; /* Mos espacio derecho (56px), menos izquierdo (52px) */
}

/* Dropdown de MES - ancho fijo con mos espacio a la derecha */
.flatpickr-current-month .flatpickr-monthDropdown-months {
    flex-shrink: 0;
    margin: 0 8px 0 0 !important; /* 8px de margen derecho */
    min-width: 100px !important; /* Ancho reducido */
    width: 100px !important;
    text-align: center;
}

/* Input de AoO - ancho fijo */
.flatpickr-current-month .numInputWrapper {
    flex-shrink: 0;
    margin: 0 !important;
    min-width: 65px !important; /* Ancho reducido */
    width: 65px !important;
}

.flatpickr-weekdays {
    background: #0f172a;
    padding: 8px 0;
}

.flatpickr-weekday {
    color: rgba(255, 255, 255, 0.5) !important; /* Blanco transparente */
    font-weight: 600;
    font-size: 13px;
}

.flatpickr-days {
    background: #1e293b;
}

.flatpickr-day {
    color: #f1f5f9;
    border-radius: 6px;
    border: 1px solid transparent;
    transition: all 0.2s ease;
}

/* OCULTAR doas de otros meses */
.flatpickr-day.prevMonthDay,
.flatpickr-day.nextMonthDay {
    visibility: hidden !important;
    opacity: 0 !important;
    pointer-events: none !important;
}

.flatpickr-day:hover:not(.flatpickr-disabled):not(.selected) {
    background: #334155;
    border-color: #3b82f6;
    color: white;
}

/* Doas seleccionados (inicio y fin del rango) */
.flatpickr-day.selected,
.flatpickr-day.startRange,
.flatpickr-day.endRange {
    background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%) !important;
    border-color: #3b82f6 !important;
    color: white !important;
    font-weight: bold;
}

/* Rango intermedio - MEJORADO sin rayas blancas */
.flatpickr-day.inRange {
    background: rgba(59, 130, 246, 0.25) !important;
    border-color: transparent !important;
    color: #e0e7ff !important;
    box-shadow: none !important;
}

/* Hover sobre doas en rango */
.flatpickr-day.inRange:hover {
    background: rgba(59, 130, 246, 0.35) !important;
}

/* Doas con categoroas marcados - Indicador SIEMPRE visible */
.flatpickr-day.has-products {
    position: relative;
}

.flatpickr-day.has-products::after {
    content: '' !important;
    position: absolute !important;
    bottom: 3px !important;
    left: 50% !important;
    transform: translateX(-50%) !important;
    width: 5px !important;
    height: 5px !important;
    background: #10b981 !important;
    border-radius: 50% !important;
    z-index: 10 !important;
    display: block !important;
    opacity: 1 !important;
    visibility: visible !important;
}

/* Si el doa con categoroas esto seleccionado, cambiar color del punto a blanco */
.flatpickr-day.has-products.selected::after,
.flatpickr-day.has-products.startRange::after,
.flatpickr-day.has-products.endRange::after {
    background: #ffffff !important;
}

/* Si esto en el rango, mantener verde pero mos visible */
.flatpickr-day.has-products.inRange::after {
    background: #10b981 !important;
    box-shadow: 0 0 4px rgba(16, 185, 129, 0.6) !important;
}

/* Doas deshabilitados */
.flatpickr-day.flatpickr-disabled {
    color: #475569;
    opacity: 0.5;
}

/* Doa de hoy */
.flatpickr-day.today {
    border-color: #3b82f6;
    font-weight: 600;
}

.flatpickr-day.today:not(.selected) {
    color: #3b82f6;
}

/* Botones de navegacion - Mejorados con tamaoo fijo */
.flatpickr-prev-month,
.flatpickr-next-month {
    fill: white !important;
    width: 36px !important;
    height: 36px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    border-radius: 8px !important;
    transition: all 0.2s ease !important;
    flex-shrink: 0 !important; /* No se encogen */
}

.flatpickr-prev-month:hover,
.flatpickr-next-month:hover {
    background: rgba(255, 255, 255, 0.15) !important;
    fill: #e0e7ff !important;
}

.flatpickr-prev-month svg,
.flatpickr-next-month svg {
    width: 18px !important;
    height: 18px !important;
}

/* Dropdown de mes y aoo - MEJORADO con anchos fijos */
.flatpickr-monthDropdown-months,
.numInputWrapper {
    background: rgba(255, 255, 255, 0.1) !important;
    color: white !important;
    border: 1px solid rgba(255, 255, 255, 0.2) !important;
    border-radius: 8px !important;
    padding: 7px 10px !important; /* Padding reducido */
    font-weight: 600 !important;
    font-size: 13px !important; /* Tamaoo de fuente reducido */
    transition: all 0.2s ease !important;
    cursor: pointer !important;
    text-align: center !important;
}

/* Input de aoo con ancho fijo */
.numInputWrapper {
    min-width: 65px !important;
    width: 65px !important;
}

/* Dropdown de mes con ancho fijo */
.flatpickr-monthDropdown-months {
    min-width: 100px !important;
    width: 100px !important;
}

.flatpickr-monthDropdown-months:hover,
.numInputWrapper:hover {
    background: rgba(255, 255, 255, 0.15) !important;
    border-color: rgba(255, 255, 255, 0.3) !important;
}

.flatpickr-monthDropdown-months:focus,
.numInputWrapper:focus {
    outline: none !important;
    background: rgba(255, 255, 255, 0.2) !important;
    border-color: rgba(255, 255, 255, 0.4) !important;
    box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1) !important;
}

/* Opciones del dropdown de mes */
.flatpickr-monthDropdown-months option {
    background: #1e293b !important;
    color: white !important;
    padding: 8px !important;
    font-size: 13px !important;
}

/* Opciones con categoroas - TODO EN VERDE (mos simple y efectivo) */
.flatpickr-monthDropdown-months option[data-has-products="true"] {
    background: #1e293b !important;
    color: #10b981 !important; /* Verde */
    font-weight: 600 !important;
}

/* Opcion seleccionada - fondo azul con texto blanco visible */
.flatpickr-monthDropdown-months option:checked {
    background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%) !important;
    color: white !important;
    font-weight: 700 !important;
    -webkit-text-fill-color: white !important;
}

/* Opcion al hacer hover */
.flatpickr-monthDropdown-months option:hover {
    background: #334155 !important;
    color: white !important;
    -webkit-text-fill-color: white !important;
}

/* Focus del select - corregir contraste */
.flatpickr-monthDropdown-months:focus {
    outline: none !important;
    background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%) !important;
    color: white !important;
    border-color: #60a5fa !important;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3) !important;
}

/* Opciones cuando el select tiene focus */
.flatpickr-monthDropdown-months:focus option {
    background: #1e293b !important;
    color: white !important;
}

/* Opciones con categoroas mantienen texto blanco, el emoji ?? es naturalmente verde */
.flatpickr-monthDropdown-months option[data-has-products="true"] {
    color: white !important;
    background: #1e293b !important;
}

/* Input de aoo - Quitar flechas y hacerlo tipo texto */
.numInputWrapper {
    position: relative;
}

.numInputWrapper input {
    background: transparent !important;
    color: white !important;
    border: none !important;
    font-weight: 600 !important;
    -moz-appearance: textfield !important; /* Firefox */
    appearance: textfield !important;
}

/* Ocultar flechas en Chrome, Safari, Edge */
.numInputWrapper input::-webkit-outer-spin-button,
.numInputWrapper input::-webkit-inner-spin-button {
    -webkit-appearance: none !important;
    margin: 0 !important;
    display: none !important;
}

/* Ocultar las flechas de Flatpickr */
.numInputWrapper span.arrowUp,
.numInputWrapper span.arrowDown {
    display: none !important;
}

/* Animacion de cierre */
.flatpickr-calendar.animate.close {
    animation: flatpickrFadeOut 0.2s ease;
}

@keyframes flatpickrFadeOut {
    from {
        opacity: 1;
        transform: translateY(0);
    }
    to {
        opacity: 0;
        transform: translateY(-10px);
    }
}

/* ===== PREVENIR DOBLE SCROLLBAR ===== */
.data-table-container:has(.scrollable-table) {
    overflow: visible !important;
}

.data-table-wrapper.scrollable-table {
    overflow-y: auto !important;
    overflow-x: hidden !important;
}

/* ===== BARRA DE SCROLL PERSONALIZADA PARA LA TABLA ===== */
.scrollable-table {
    max-height: calc(100vh - 250px);
    min-height: 500px;
    overflow-y: auto;
    overflow-x: hidden;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    background: #1e293b;
    position: relative;
    cursor: default; /* Cursor normal ya que no hay drag horizontal */
}

.scrollable-table.dragging {
    cursor: default; /* Cursor normal */
    user-select: none; /* Evitar seleccion de texto */
}

/* Estilos personalizados para la barra de scroll - SIMPLIFICADO */
.scrollable-table::-webkit-scrollbar {
    width: 10px;
    height: 0; /* Sin scrollbar horizontal */
}

.scrollable-table::-webkit-scrollbar-track {
    background: #0f172a;
    border-radius: 5px;
}

.scrollable-table::-webkit-scrollbar-thumb {
    background: linear-gradient(180deg, #475569 0%, #334155 100%);
    border-radius: 5px;
    transition: all 0.3s ease;
}

.scrollable-table::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(180deg, #64748b 0%, #475569 100%);
}

.scrollable-table::-webkit-scrollbar-thumb:active {
    background: linear-gradient(180deg, #334155 0%, #1e293b 100%);
}

/* Esquina del scroll */
.scrollable-table::-webkit-scrollbar-corner {
    background: #0f172a;
}

/* Ocultar botones de flechas */
.scrollable-table::-webkit-scrollbar-button {
    display: none;
    width: 0;
    height: 0;
}

/* Mejorar la tabla dentro del contenedor con scroll */
.scrollable-table .data-table {
    margin: 0;
    border-radius: 0;
    box-shadow: none;
    border-collapse: separate;
    border-spacing: 0;
}

.scrollable-table .table-header th:first-child {
    border-top-left-radius: 8px;
}

.scrollable-table .table-header th:last-child {
    border-top-right-radius: 8px;
}

/* Sticky header dentro del scroll */
.scrollable-table .table-header {
    position: sticky;
    top: 0;
    z-index: 20;
    background: #1e293b;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Animacion suave del scroll */
.scrollable-table {
    scroll-behavior: smooth;
}

/* ===== FORZAR PRIMER PLANO PARA ELEMENTOS FLOTANTES ===== */
.animated-floating-container,
.animated-floating-button,
.stock-update-bubble {
    z-index: 999999 !important;
    position: relative !important;
}

.delete-confirmation-overlay {
    z-index: 1000002 !important;
    position: fixed !important;
}

.animated-floating-container * {
    z-index: 999999 !important;
}

/* Asegurar que los tooltips tambion eston en primer plano */
.floating-tooltip {
    z-index: 1000000 !important;
}

/* Forzar primer plano en elementos especoficos que pueden interferir */
.modal-content,
.modal-overlay,
#product-modal-overlay {
    z-index: 99999 !important;
}

/* Asegurar que las burbujas eston por encima de modales */
.animated-floating-container,
.stock-update-bubble {
    z-index: 1000001 !important;
}
</style>

