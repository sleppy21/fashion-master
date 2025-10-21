<?php
/**
 * VISTA DE GESTIÓN DE categorias - DISEÑO MODERNO
 * Sistema unificado con diseño actualizado
 */
?>

<div class="admin-module admin-categories-module">
    <!-- Header del módulo -->
    <div class="module-header">
        <div class="module-title">
            <div class="module-icon">
                <i class="fas fa-tags"></i>
            </div>
            <div class="module-info">
                <h2 class="module-name">Gestión de categorias</h2>
                <p class="module-description">Administra las categorías de productos de la tienda</p>
            </div>
        </div>
        <div class="module-actions">
            <button class="btn-modern btn-primary" onclick="window.showCreateCategoryModal();" style="color: white !important;">
                <i class="fas fa-plus" style="color: white !important;"></i>
                <span style="color: white !important;">Nueva <span class="btn-text-mobile-hide">Categoría</span></span>
            </button>
            <button class="btn-modern btn-secondary" onclick="exportCategories()" style="color: white !important;">
                <i class="fas fa-download" style="color: white !important;"></i>
                <span style="color: white !important;">Exportar <span class="btn-text-mobile-hide">Excel</span></span>
            </button>
            <button class="btn-modern btn-info" onclick="showCategoryReport()" style="color: white !important;">
                <i class="fas fa-chart-bar" style="color: white !important;"></i>
                <span style="color: white !important;">Reporte <span class="btn-text-mobile-hide">Categorías</span></span>
            </button>
        </div>
    </div>

    <!-- Filtros y búsqueda (sidebar responsive) -->
    <div class="module-filters modern-sidebar">
        <div class="search-container">
            <div class="search-input-group">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="search-categorias" class="search-input" 
                       placeholder="Buscar categorias por nombre..." oninput="handleCategorySearchInput()">
                <button class="search-clear" onclick="clearCategorySearch()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        
        <div class="filters-grid">
            <div class="filter-group">
                <label class="filter-label">Estado</label>
                <select id="filter-status" class="filter-select" onchange="filterCategories()">
                    <option value="">Todos los estados</option>
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label">Fecha</label>
                <button type="button" 
                        id="filter-fecha" 
                        class="filter-select-2"
                        style="justify-content: flex-start;">
                    <span id="filter-fecha-text">Seleccionar fechas</span>
                </button>
                <input type="hidden" id="filter-fecha-value">
            </div>
            <div class="filter-group">
                <button class="btn-modern btn-outline" onclick="clearAllCategoryFilters()">
                    <i class="fas fa-filter-circle-xmark"></i>
                    Limpiar
                </button>
            </div>
        </div>
    </div>

    <!-- Contenido del módulo -->
    <div class="module-content">
        <div class="data-table-container">
            <div class="table-controls">
                <div class="table-info">
                    <span class="results-count">
                        Mostrando  <span id="showing-end-products">0</span> 
                        de <span id="total-products">0</span> categorias
                    </span>
                </div>
                <div class="table-actions">
                    <div class="view-options">
                        <button class="view-btn active" data-view="table" onclick="toggleView('table')">
                            <i class="fas fa-table"></i>
                        </button>
                        <button class="view-btn" data-view="grid" onclick="toggleView('grid')">
                            <i class="fas fa-th"></i>
                        </button>
                    </div>
                    <div class="bulk-actions" style="display: none;">
                        <span class="selected-count">0</span> seleccionados
                        <select class="bulk-select" onchange="handleBulkProductAction(this.value); this.value='';">
                            <option value="">Acciones en lote</option>
                            <option value="activar">Activar seleccionados</option>
                            <option value="desactivar">Desactivar seleccionados</option>
                            <option value="eliminar">Eliminar seleccionados</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="data-table-wrapper scrollable-table">
                <table class="data-table products-table">
                    <thead class="table-header">
                        <tr>
                            <th class="sortable" data-sort="numero" data-type="number">
                                <span>N°</span>
                            </th>
                            <th class="no-sort">Imagen</th>
                            <th class="sortable" data-sort="nombre" data-type="text">
                                <span>Nombre</span>
                            </th>
                            <th class="sortable" data-sort="descripcion" data-type="text">
                                <span>Descripción</span>
                            </th>
                            <th class="sortable" data-sort="productos_count" data-type="number">
                                <span>Productos</span>
                            </th>
                            <th class="sortable" data-sort="estado" data-type="text">
                                <span>Estado</span>
                            </th>
                            <th class="sortable" data-sort="fecha" data-type="date">
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
                                    <span>Cargando categorias...</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="pagination-container">
                <div class="pagination-info">
                    <span class="pagination-text">
                        Página <span id="current-page-products">1</span> de <span id="total-pages-products">1</span>
                    </span>
                </div>
                <div class="pagination-controls">
                    <button class="pagination-btn" id="first-page-products" onclick="goToFirstPageProducts()">
                        <i class="fas fa-angle-double-left"></i>
                    </button>
                    <button class="pagination-btn" id="prev-page-products" onclick="previousPageProducts()">
                        <i class="fas fa-angle-left"></i>
                    </button>
                    <div class="pagination-numbers" id="pagination-numbers-products">
                        <!-- Números de página dinámicos -->
                    </div>
                    <button class="pagination-btn" id="next-page-products" onclick="nextPageProducts()">
                        <i class="fas fa-angle-right"></i>
                    </button>
                    <button class="pagination-btn" id="last-page-products" onclick="goToLastPageProducts()">
                        <i class="fas fa-angle-double-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

</div> <!-- FIN admin-module -->

<!-- ========================================
     MOBILE ONLY: Botón y Modal de Filtros
     (FUERA del módulo para que persista)
     ======================================== -->

<!-- Botón flotante de filtros móvil -->
<button class="btn-mobile-filters" id="btnMobileFilters" aria-label="Abrir filtros">
    <i class="fa fa-filter"></i>
    <span class="filter-count" id="filterCount">0</span>
</button>

<script>
// ============ CARGAR SCRIPT ESPECÍFICO DE CATEGORÍAS ============
(function() {
    // Solo cargar si no está ya cargado
    if (!document.querySelector('script[src*="smooth-table-update-categories.js"]')) {
        const script = document.createElement('script');
        script.src = 'public/assets/js/smooth-table-update-categories.js';
        script.onload = function() {
            console.log('✅ smooth-table-update-categories.js cargado para CATEGORÍAS');
            // Disparar evento personalizado cuando el script se cargue
            window.dispatchEvent(new Event('smoothTableUpdaterCategoriesLoaded'));
        };
        script.onerror = function() {
            console.error('❌ Error al cargar smooth-table-update-categories.js');
        };
        document.head.appendChild(script);
    } else {
        // Si ya está cargado, disparar el evento inmediatamente
        setTimeout(() => {
            window.dispatchEvent(new Event('smoothTableUpdaterCategoriesLoaded'));
        }, 100);
    }
})();

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

// Variables de paginación
let currentPage = 1;
let totalPages = 1;

// Variables de ordenamiento
let currentSortColumn = null;
let currentSortOrder = 'asc'; // 'asc' o 'desc'

// Variable para tracking de vista actual (tabla o grid)
window.products_currentView = 'table'; // Por defecto tabla

// Variable global para fechas de categorias (para Flatpickr)
window.productsDatesArray = [];

// ============ MOBILE FILTERS SIDEBAR (shop.php style) ============

// Botón flotante de filtros móvil - Mostrar/ocultar según tamaño de pantalla
function toggleMobileFilterButton() {
    const btn = document.getElementById('btnMobileFilters');
    const isMobile = window.innerWidth <= 768;
    
    console.log('📱 toggleMobileFilterButton:', {
        btnExists: !!btn,
        isMobile: isMobile,
        width: window.innerWidth
    });
    
    if (btn) {
        btn.style.display = isMobile ? 'flex' : 'none';
        console.log('✅ Botón flotante ' + (isMobile ? 'MOSTRADO' : 'OCULTO'));
    } else {
        console.error('❌ Botón btnMobileFilters NO encontrado en DOM');
    }
}

// Inicializar control del sidebar móvil
function initMobileFiltersSidebar() {
    const btnMobileFilters = document.getElementById('btnMobileFilters');
    const sidebar = document.querySelector('.modern-sidebar');
    
    console.log('🎯 initMobileFiltersSidebar:', {
        btnExists: !!btnMobileFilters,
        sidebarExists: !!sidebar,
        sidebarClasses: sidebar ? sidebar.className : 'N/A'
    });
    
    if (btnMobileFilters && sidebar) {
        console.log('✅ Sidebar móvil inicializado correctamente');
        
        // Toggle sidebar al hacer click en el botón
        btnMobileFilters.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            console.log('🔵 Click en botón flotante');
            
            if (sidebar.classList.contains('show-mobile')) {
                // Cerrar sidebar
                sidebar.classList.remove('show-mobile');
                document.body.style.overflow = '';
                
                // Mostrar botón con animación
                setTimeout(() => {
                    btnMobileFilters.classList.remove('hidden');
                }, 300);
                
                console.log('🔒 Sidebar CERRADO');
            } else {
                // Abrir sidebar
                sidebar.classList.add('show-mobile');
                document.body.style.overflow = 'hidden';
                
                // Ocultar botón con animación
                btnMobileFilters.classList.add('hidden');
                
                console.log('🔓 Sidebar ABIERTO');
            }
        });
        
        // Cerrar al hacer click fuera del sidebar
        document.addEventListener('click', function(e) {
            if (sidebar.classList.contains('show-mobile') && 
                !sidebar.contains(e.target) && 
                !btnMobileFilters.contains(e.target)) {
                sidebar.classList.remove('show-mobile');
                document.body.style.overflow = '';
                
                // Mostrar botón con animación
                setTimeout(() => {
                    btnMobileFilters.classList.remove('hidden');
                }, 300);
                
                console.log('🔒 Sidebar cerrado por click fuera');
            }
        });
    } else {
        console.error('❌ No se pudo inicializar sidebar móvil:', {
            btnMissing: !btnMobileFilters,
            sidebarMissing: !sidebar
        });
    }
}

// Actualizar contador de filtros activos
function updateFilterCount() {
    const filterCount = document.getElementById('filterCount');
    if (!filterCount) {
        console.warn('⚠️ filterCount badge no encontrado');
        return;
    }
    
    let count = 0;
    
    // Contar filtros activos
    const categoryFilter = document.getElementById('filter-category');
    const marcaFilter = document.getElementById('filter-marca');
    const statusFilter = document.getElementById('filter-status');
    const stockFilter = document.getElementById('filter-stock');
    const fechaFilter = document.getElementById('filter-fecha-value');
    const searchInput = document.getElementById('search-categorias');
    
    if (categoryFilter && categoryFilter.value) count++;
    if (marcaFilter && marcaFilter.value) count++;
    if (statusFilter && statusFilter.value) count++;
    if (stockFilter && stockFilter.value) count++;
    if (fechaFilter && fechaFilter.value) count++;
    if (searchInput && searchInput.value.trim()) count++;
    
    // Actualizar badge
    filterCount.textContent = count;
    filterCount.style.display = count > 0 ? 'flex' : 'none';
    
    console.log('🔢 Contador de filtros actualizado:', count);
}

// ============ FUNCIONES LEGACY (mantener compatibilidad) ============

function toggleFiltersModal() {
    // Redirigir a la nueva función
    const sidebar = document.querySelector('.modern-sidebar');
    const btn = document.getElementById('btnMobileFilters');
    if (btn) btn.click();
}
window.toggleFiltersModal = toggleFiltersModal;

function closeFiltersModal() {
    const sidebar = document.querySelector('.modern-sidebar');
    if (sidebar) {
        sidebar.classList.remove('show-mobile');
        document.body.style.overflow = '';
    }
}
window.closeFiltersModal = closeFiltersModal;

function closeFiltersModalOnOverlay(event) {
    if (event.target.id === 'filters-modal') {
        closeFiltersModal();
    }
}
window.closeFiltersModalOnOverlay = closeFiltersModalOnOverlay;

// Filtrar categorias desde el modal
function filterCategoriesFromModal() {
    syncFiltersFromModal();
    filterCategories();
}
window.filterCategoriesFromModal = filterCategoriesFromModal;

// Limpiar todos los filtros desde el modal
function clearModalFilters() {
    console.log('🧹 Limpiando filtros del modal');
    
    // Limpiar búsqueda
    const modalSearch = document.getElementById('modal-search-categorias');
    if (modalSearch) modalSearch.value = '';
    
    // Limpiar selects
    const modalCategory = document.getElementById('modal-filter-category');
    if (modalCategory) modalCategory.value = '';
    
    const modalStatus = document.getElementById('modal-filter-status');
    if (modalStatus) modalStatus.value = '';
    
    const modalStock = document.getElementById('modal-filter-stock');
    if (modalStock) modalStock.value = '';
    
    const modalMarca = document.getElementById('modal-filter-marca');
    if (modalMarca) modalMarca.value = '';
    
    // Limpiar fecha (botón + hidden input)
    const modalFechaValue = document.getElementById('modal-filter-fecha-value');
    const modalFechaText = document.getElementById('modal-filter-fecha-text');
    
    if (modalFechaValue) modalFechaValue.value = '';
    if (modalFechaText) {
        modalFechaText.innerHTML = '<i class="fas fa-calendar-alt"></i> Seleccionar fechas';
    }
    
    // Limpiar Flatpickr modal
    if (window.productsDatePickerModal) {
        window.productsDatePickerModal.clear();
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
    
    const desktopMarca = document.getElementById('filter-marca');
    if (desktopMarca) desktopMarca.value = '';
    
    const desktopFechaValue = document.getElementById('filter-fecha-value');
    const desktopFechaText = document.getElementById('filter-fecha-text');
    
    if (desktopFechaValue) desktopFechaValue.value = '';
    if (desktopFechaText) {
        desktopFechaText.innerHTML = '<i class="fas fa-calendar-alt"></i> Seleccionar fechas';
    }
    
    // Limpiar Flatpickr desktop
    if (window.productsDatePicker) {
        window.productsDatePicker.clear();
    }
    
    // Recargar categorias sin filtros
    clearAllCategoryFilters();
    
    console.log('✅ Filtros limpiados');
}
window.clearModalFilters = clearModalFilters;

// ============ END MOBILE FILTERS MODAL FUNCTIONS ============

// Función para obtener la URL correcta de la imagen del categoria
function getProductImageUrl(categoria, forceCacheBust = false) {
    // Priorizar url_imagen_categoria, luego imagen_categoria
    let imageUrl = '';
    
    if (categoria.url_imagen_categoria) {
        // Verificar que no sea una URL de placeholder
        if (categoria.url_imagen_categoria.includes('placeholder') || 
            categoria.url_imagen_categoria.includes('via.placeholder')) {
            imageUrl = (typeof AppConfig !== 'undefined') ? AppConfig.getImageUrl('default-category.png') : '/fashion-master/public/assets/img/default-category.png';
        } else {
            imageUrl = categoria.url_imagen_categoria;
        }
    } else if (categoria.imagen_categoria) {
        // Si es un nombre de archivo local, construir la ruta completa
        if (!categoria.imagen_categoria.startsWith('http')) {
            imageUrl = (typeof AppConfig !== 'undefined') ? AppConfig.getImageUrl('categories/' + categoria.imagen_categoria) : '/fashion-master/public/assets/img/categories/' + categoria.imagen_categoria;
        } else {
            imageUrl = categoria.imagen_categoria;
        }
    } else {
        imageUrl = (typeof AppConfig !== 'undefined') ? AppConfig.getImageUrl('default-category.png') : '/fashion-master/public/assets/img/default-category.png';
    }
    
    // Agregar cache-busting solo si se solicita explícitamente
    if (forceCacheBust) {
        const cacheBuster = '?v=' + Date.now();
        return imageUrl + cacheBuster;
    }
    
    return imageUrl;
}

// Función auxiliar para mostrar loading en búsqueda
function showSearchLoading() {
    const tbody = document.getElementById('categorias-table-body');
    if (tbody) {
        tbody.innerHTML = `
            <tr class="loading-row">
                <td colspan="11" class="loading-cell">
                    <div class="loading-content">
                        <div class="spinner"></div>
                        <span>Buscando categorias...</span>
                    </div>
                </td>
            </tr>
        `;
    }
}

// Función principal para cargar categorias con efectos visuales (DEFINICIÓN TEMPRANA)
async function loadCategorias(forceCacheBust = false, preserveState = null) {
    
    console.log('🚀 loadCategorias iniciada');
    console.log('📊 CONFIG:', window.CONFIG);
    console.log('📍 CONFIG.apiUrl:', window.CONFIG?.apiUrl);
    
    isLoading = true;
    
    try {
        // Mostrar loading mejorado
        showSearchLoading();
        
        // Usar estado preservado si está disponible
        if (preserveState) {
            currentPage = preserveState.page || currentPage;
            
            // Restaurar filtros si están disponibles
            if (preserveState.searchTerm && typeof $ !== 'undefined') {
                $('#search-categorias').val(preserveState.searchTerm);
            }
            
        }
        
        // Construir URL con parámetros
        const params = new URLSearchParams({
            action: 'list',
            page: currentPage,
            limit: 10
        });
        
        console.log('📦 Parámetros iniciales:', Object.fromEntries(params));
        
        // Agregar filtros si existen
        if (typeof $ !== 'undefined') {
            const search = $('#search-categorias').val();
            if (search) params.append('search', search);
            
            const status = $('#filter-status').val();
            if (status !== '') params.append('status', status);
            
            // Usar el hidden input para la fecha
            const fecha = $('#filter-fecha-value').val();
            if (fecha) params.append('fecha', fecha);
        } else {
            // Fallback vanilla JS
            const searchInput = document.getElementById('search-categorias');
            if (searchInput && searchInput.value) {
                params.append('search', searchInput.value);
            }
            
            const statusSelect = document.getElementById('filter-status');
            if (statusSelect && statusSelect.value !== '') {
                params.append('status', statusSelect.value);
            }
            
            // Usar el hidden input para la fecha
            const fechaValue = document.getElementById('filter-fecha-value');
            if (fechaValue && fechaValue.value) {
                params.append('fecha', fechaValue.value);
            }
        }
        
        // Agregar parámetros de ordenamiento si existen
        if (currentSortColumn) {
            params.append('sort_by', currentSortColumn);
            params.append('sort_order', currentSortOrder);
        }
        
        const finalUrl = `${CONFIG.apiUrl}?${params}`;
        
        console.log('🌐 URL final:', finalUrl);
        console.log('📡 Iniciando fetch...');
        
        const response = await fetch(finalUrl, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            cache: 'no-cache'
        });
        
        console.log('✅ Response recibido:', response.status, response.statusText);    
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status} - ${response.statusText}`);
        }
        
        // Obtener texto crudo
        const responseText = await response.text();
        
        console.log('📄 Respuesta text recibida, longitud:', responseText.length);
        
        // Parsear JSON de forma segura
        let data;
        try {
            data = JSON.parse(responseText);
            console.log('✅ JSON parseado correctamente');
            console.log('📊 Success:', data.success);
            console.log('📊 Data items:', data.data?.length);
        } catch (jsonError) {
            console.error('❌ Error al parsear JSON:', jsonError);
            console.error('📄 Respuesta recibida (primeros 500 caracteres):', responseText.substring(0, 500));
            throw new Error('Respuesta del servidor no es JSON válido. Ver consola para detalles.');
        }
        
        if (!data.success) {
            throw new Error(data.error || 'Error desconocido del servidor');
        }
        
        categorias = data.data || [];
        
        console.log('🎯 Categorías recibidas:', categorias.length);
        console.log('📊 Llamando a displayProducts...');
        
        displayProducts(categorias, forceCacheBust, preserveState);
        updateStats(data.pagination);
        updatePaginationInfo(data.pagination);
        
        // Cargar fechas únicas en el filtro
        loadProductDates(categorias);
        
        // Actualizar contador de resultados
        if (data.pagination) {
            updateResultsCounter(categorias.length, data.pagination.total_items);
        }
        
        // Destacar categoria recién actualizado/creado si está especificado
        // PRESERVAR ESTADO - sin destacado visual para evitar bugs
        if (preserveState) {
            // Restaurar posición de scroll sin animaciones que causen problemas
            if (preserveState.scrollPosition && typeof restoreScrollPosition === 'function') {
                restoreScrollPosition(preserveState.scrollPosition);
            }
        }
        
    } catch (error) {
        const tbody = document.getElementById('categorias-table-body');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="11" class="loading-cell">
                        <div class="loading-content error">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Error: ${error.message}</span>
                            <button onclick="loadProducts()" class="btn-modern btn-primary">
                                <i class="fas fa-redo"></i> Reintentar
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }
    } finally {
        isLoading = false;
        
        // Loading overlay eliminado
    }
}

// Crear aliases globales para compatibilidad
window.loadCategorias = loadCategorias;
window.loadcategorias = loadCategorias;
window.loadProducts = loadCategorias;

// 🎯 Función para cargar categorias con SMOOTH UPDATE (sin recargar tabla)
async function loadProductsSmooth() {
    if (!window.categoriasTableUpdater) {
        console.warn('⚠️ smoothTableUpdater no disponible, usando carga normal');
        return loadCategorias();
    }
    
    try {
        // Construir URL con parámetros
        const params = new URLSearchParams({
            action: 'list',
            page: currentPage,
            limit: 10
        });
        
        // Agregar filtros si existen
        const search = document.getElementById('search-categorias')?.value || '';
        if (search) params.append('search', search);
        
        const category = document.getElementById('filter-category')?.value || '';
        if (category) params.append('category', category);
        
        const marca = document.getElementById('filter-marca')?.value || '';
        if (marca) params.append('marca', marca);
        
        const status = document.getElementById('filter-status')?.value || '';
        if (status !== '') params.append('status', status);
        
        const stock = document.getElementById('filter-stock')?.value || '';
        if (stock) params.append('stock_filter', stock);
        
        const fecha = document.getElementById('filter-fecha-value')?.value || '';
        if (fecha) params.append('fecha', fecha);
        
        // Agregar parámetros de ordenamiento si existen
        if (currentSortColumn) {
            params.append('sort_by', currentSortColumn);
            params.append('sort_order', currentSortOrder);
        }
        
        const finalUrl = `${CONFIG.apiUrl}?${params}`;
        
        console.log('🎯 Cargando categorias con smooth update:', finalUrl);
        
        const response = await fetch(finalUrl);
        const data = await response.json();
        
        if (data.success) {
            // Verificar si hay categorias
            if (data.data && data.data.length > 0) {
                // 🎨 SMOOTH UPDATE: Actualizar categorias uno por uno sin recargar la tabla
                await window.categoriasTableUpdater.updateMultipleProducts(data.data);
                
                // Actualizar estadísticas y paginación
                updateStats(data.pagination);
                updatePaginationInfo(data.pagination);
                // updatePaginationControls(); // TODO: Implementar si es necesario
                
                // Actualizar fechas del calendario SIN redibujar (invisible)
                if (typeof loadProductDates === 'function') {
                    loadProductDates(data.data);
                }
                
                console.log('✅ categorias actualizados con smooth update');
            } else {
                // No hay categorias, mostrar mensaje
                const tbody = document.getElementById('categorias-table-body');
                if (tbody) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="11" class="loading-cell">
                                <div class="loading-content no-data">
                                    <i class="fas fa-search" style="font-size: 48px; color: #cbd5e0; margin-bottom: 16px;"></i>
                                    <span style="font-size: 16px; color: #4a5568;">No se encontraron categorias</span>
                                    <small style="color: #a0aec0; margin-top: 8px;">Intenta ajustar los filtros de búsqueda</small>
                                </div>
                            </td>
                        </tr>
                    `;
                }
                
                // Actualizar estadísticas y paginación con valores vacíos
                updateStats({ total: 0 });
                updatePaginationInfo({ total: 0, page: 1, totalPages: 0 });
                
                console.log('ℹ️ No se encontraron categorias con los filtros actuales');
            }
        } else {
            throw new Error(data.message || 'Error al cargar categorias');
        }
    } catch (error) {
        console.error('❌ Error en loadProductsSmooth:', error);
        // Fallback a carga normal
        loadProducts();
    }
}

window.loadProductsSmooth = loadProductsSmooth;

// ============ FUNCIONES DE ORDENAMIENTO ============

/**
 * Ordena la tabla localmente (cliente) sin hacer petición al servidor
 * @param {string} column - Columna a ordenar
 * @param {string} type - Tipo de dato (text, number, date, stock)
 * 
 * COMPORTAMIENTO ESPECIAL DE LA COLUMNA N°:
 * - N° siempre muestra 1, 2, 3... (posición visual, NO el ID real del categoria)
 * - Primer click: Mantiene orden actual (ASC)
 * - Segundo click: Invierte orden completo (DESC)
 * - Tercer click: Vuelve al orden original (ASC)
 * 
 * Ejemplo con categorias ID 1, 3, 6, 7 (después de soft delete del ID 6):
 * ASC:  N°1 (ID:1), N°2 (ID:3), N°3 (ID:7)
 * DESC: N°1 (ID:7), N°2 (ID:3), N°3 (ID:1)  ← Orden invertido
 */
function sortTableLocally(column, type) {
    console.log(`🔄 Ordenando por ${column} (${type}) - Orden: ${currentSortOrder}`);
    
    // Obtener todas las filas de la tabla
    const tbody = document.getElementById('categorias-table-body');
    if (!tbody) return;
    
    const rows = Array.from(tbody.querySelectorAll('tr:not(.loading-row):not(.empty-row)'));
    
    if (rows.length === 0) {
        console.log('⚠️ No hay filas para ordenar');
        return;
    }
    
    // Mapeo de columnas a índices
    const columnIndexMap = {
        'numero': 0,      // N°
        'nombre': 2,      // categoria
        'categoria': 3,   // Categoría
        'marca': 4,       // Marca
        'genero': 5,      // Género
        'precio': 6,      // Precio
        'stock': 7,       // Stock
        'estado': 8,      // Estado
        'fecha': 9        // Fecha
    };
    
    const columnIndex = columnIndexMap[column];
    if (columnIndex === undefined) {
        console.error('❌ Columna no válida:', column);
        return;
    }
    
    // ⚡ CASO ESPECIAL: Columna N° simplemente invierte el orden completo
    if (column === 'numero') {
        // SIEMPRE invertir el array en cada click (no importa si es ASC o DESC)
        rows.reverse();
        
        const totalRows = rows.length;
        
        // Limpiar tbody
        tbody.innerHTML = '';
        
        // Re-insertar filas con números invertidos visualmente
        rows.forEach((row, index) => {
            // Actualizar N° de fila
            const numeroCell = row.children[0];
            if (numeroCell) {
                // Si es DESC, mostrar números invertidos (N → 1)
                // Si es ASC, mostrar números normales (1 → N)
                if (currentSortOrder === 'desc') {
                    numeroCell.textContent = totalRows - index; // 10, 9, 8, 7...
                } else {
                    numeroCell.textContent = index + 1; // 1, 2, 3, 4...
                }
            }
            
            // Agregar animación
            row.style.opacity = '0';
            row.style.transform = 'translateX(-10px)';
            tbody.appendChild(row);
            
            // Animar entrada
            setTimeout(() => {
                row.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
                row.style.opacity = '1';
                row.style.transform = 'translateX(0)';
            }, index * 20);
        });
        
        console.log(`✅ Tabla ordenada por N° (${currentSortOrder === 'asc' ? 'Orden original 1→N' : 'Orden invertido N→1'})`);
        return; // Salir de la función
    }
    
    // Ordenar filas (para otras columnas)
    rows.sort((rowA, rowB) => {
        const cellA = rowA.children[columnIndex];
        const cellB = rowB.children[columnIndex];
        
        if (!cellA || !cellB) return 0;
        
        let valueA, valueB;
        
        switch (type) {
            case 'number':
                // Para precio
                valueA = parseFloat(cellA.textContent.replace(/[^0-9.-]/g, '')) || 0;
                valueB = parseFloat(cellB.textContent.replace(/[^0-9.-]/g, '')) || 0;
                break;
                
            case 'stock':
                // Para stock: primero por nivel (normal > bajo > agotado), luego por cantidad
                const stockA = cellA.querySelector('.stock-number');
                const stockB = cellB.querySelector('.stock-number');
                
                const numA = stockA ? parseInt(stockA.textContent) || 0 : 0;
                const numB = stockB ? parseInt(stockB.textContent) || 0 : 0;
                
                // Determinar nivel de stock
                const getLevelPriority = (num) => {
                    if (num === 0) return 0; // Agotado (prioridad baja)
                    if (num < 10) return 1;  // Bajo (prioridad media)
                    return 2;                // Normal (prioridad alta)
                };
                
                const levelA = getLevelPriority(numA);
                const levelB = getLevelPriority(numB);
                
                // Si están en diferente nivel, ordenar por nivel
                if (levelA !== levelB) {
                    valueA = levelA;
                    valueB = levelB;
                } else {
                    // Si están en el mismo nivel, ordenar por cantidad
                    valueA = numA;
                    valueB = numB;
                }
                break;
                
            case 'date':
                // Para fecha
                const dateStrA = cellA.textContent.trim();
                const dateStrB = cellB.textContent.trim();
                valueA = dateStrA === '-' ? 0 : new Date(dateStrA).getTime();
                valueB = dateStrB === '-' ? 0 : new Date(dateStrB).getTime();
                break;
                
            case 'text':
            default:
                // Para texto (categoria, código, categoría, marca, estado)
                valueA = cellA.textContent.trim().toLowerCase();
                valueB = cellB.textContent.trim().toLowerCase();
                break;
        }
        
        // Comparar valores
        if (valueA < valueB) return currentSortOrder === 'asc' ? -1 : 1;
        if (valueA > valueB) return currentSortOrder === 'asc' ? 1 : -1;
        return 0;
    });
    
    // Limpiar tbody
    tbody.innerHTML = '';
    
    // Re-insertar filas ordenadas con animación suave
    rows.forEach((row, index) => {
        // Actualizar N° de fila
        const numeroCell = row.children[0];
        if (numeroCell) {
            numeroCell.textContent = index + 1;
        }
        
        // Agregar animación
        row.style.opacity = '0';
        row.style.transform = 'translateX(-10px)';
        tbody.appendChild(row);
        
        // Animar entrada
        setTimeout(() => {
            row.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
            row.style.opacity = '1';
            row.style.transform = 'translateX(0)';
        }, index * 20); // Escalonar animación
    });
    
    console.log(`✅ Tabla ordenada por ${column} (${currentSortOrder})`);
}

/**
 * Maneja el click en una columna sortable
 */
function handleSortClick(column, type) {
    // ⚡ CASO ESPECIAL: Columna N° siempre alterna en cada click
    if (column === 'numero') {
        // Establecer columna actual
        currentSortColumn = 'numero';
        // Alternar orden en cada click (la inversión visual siempre ocurre)
        currentSortOrder = currentSortOrder === 'asc' ? 'desc' : 'asc';
    } else {
        // Otras columnas: comportamiento normal
        if (currentSortColumn === column) {
            currentSortOrder = currentSortOrder === 'asc' ? 'desc' : 'asc';
        } else {
            // Nueva columna, comenzar con ascendente
            currentSortColumn = column;
            currentSortOrder = 'asc';
        }
    }
    
    // Actualizar iconos de ordenamiento
    updateSortIcons(column);
    
    // Ordenar localmente (más rápido, sin petición al servidor)
    sortTableLocally(column, type);
}

/**
 * Actualiza el estado visual de las columnas para mostrar cuál está ordenada
 */
function updateSortIcons(activeColumn) {
    const headers = document.querySelectorAll('th.sortable');
    
    headers.forEach(header => {
        const column = header.getAttribute('data-sort');
        
        if (column === activeColumn) {
            // Columna activa - agregar clase sorted
            header.classList.add('sorted');
            
            // Opcional: agregar indicador de dirección en el atributo
            header.setAttribute('data-sort-direction', currentSortOrder);
        } else {
            // Columna inactiva
            header.classList.remove('sorted');
            header.removeAttribute('data-sort-direction');
        }
    });
}

/**
 * Inicializa los eventos de ordenamiento en las columnas
 */
function initializeSortingEvents() {
    const sortableHeaders = document.querySelectorAll('th.sortable');
    
    sortableHeaders.forEach(header => {
        const column = header.getAttribute('data-sort');
        const type = header.getAttribute('data-type') || 'text';
        
        // Remover eventos anteriores
        header.replaceWith(header.cloneNode(true));
    });
    
    // Re-obtener headers después de clonar
    const newHeaders = document.querySelectorAll('th.sortable');
    
    newHeaders.forEach(header => {
        const column = header.getAttribute('data-sort');
        const type = header.getAttribute('data-type') || 'text';
        
        header.style.cursor = 'pointer';
        header.style.userSelect = 'none';
        
        header.addEventListener('click', () => {
            handleSortClick(column, type);
        });
    });
    
    console.log('✅ Eventos de ordenamiento inicializados en', newHeaders.length, 'columnas');
}

window.initializeSortingEvents = initializeSortingEvents;
window.handleSortClick = handleSortClick;

// Función para cargar categorías en el filtro
async function loadCategories() {
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
            console.error('❌ Error al parsear JSON de categorías:', jsonError);
            console.error('📄 Respuesta recibida (primeros 500 caracteres):', responseText.substring(0, 500));
            throw new Error('Respuesta del servidor no es JSON válido. Ver consola para detalles.');
        }
        
        if (data.success && data.data) {
            const categorySelect = document.getElementById('filter-category');
            if (categorySelect) {
                // Limpiar opciones existentes excepto "Todas las categorías"
                categorySelect.innerHTML = '<option value="">Todas las categorías</option>';
                
                // Agregar categorías
                data.data.forEach(categoria => {
                    const option = document.createElement('option');
                    option.value = categoria.id_categoria;
                    option.textContent = categoria.nombre_categoria;
                    categorySelect.appendChild(option);
                });
            }
        }
    } catch (error) {
        console.error('❌ Error cargando categorías:', error);
    }
}

window.loadCategories = loadCategories;

// Función para cargar marcas en el filtro
async function loadMarcas() {
    try {
        const url = `${CONFIG.apiUrl}?action=get_marcas`;
        
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
        
        const responseText = await response.text();
        
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (jsonError) {
            console.error('❌ Error al parsear JSON de marcas:', jsonError);
            console.error('📄 Respuesta recibida (primeros 500 caracteres):', responseText.substring(0, 500));
            throw new Error('Respuesta del servidor no es JSON válido. Ver consola para detalles.');
        }
        
        if (data.success && data.data) {
            const marcaSelect = document.getElementById('filter-marca');
            if (marcaSelect) {
                // Limpiar opciones existentes excepto "Todas las marcas"
                marcaSelect.innerHTML = '<option value="">Todas las marcas</option>';
                
                // Agregar marcas
                data.data.forEach(marca => {
                    const option = document.createElement('option');
                    option.value = marca.id_marca;
                    option.textContent = marca.nombre_marca;
                    marcaSelect.appendChild(option);
                });
            }
        }
    } catch (error) {
        console.error('❌ Error cargando marcas:', error);
    }
}

window.loadMarcas = loadMarcas;

// Función para cargar fechas únicas de categorias en el filtro
function loadProductDates(products) {
    try {
        const fechaSelect = document.getElementById('filter-fecha');
        if (!fechaSelect || !products || products.length === 0) return;
        
        // Extraer fechas únicas (formato YYYY-MM-DD)
        const fechasSet = new Set();
        products.forEach(categoria => {
            if (categoria.fecha_creacion_categoria) {
                // Extraer solo la parte de la fecha (YYYY-MM-DD)
                const fecha = categoria.fecha_creacion_categoria.split(' ')[0];
                fechasSet.add(fecha);
            }
        });
        
        // Convertir a array y ordenar de más reciente a más antigua
        const fechasUnicas = Array.from(fechasSet).sort((a, b) => b.localeCompare(a));
        
        // Guardar fechas en variable global para Flatpickr
        window.productsDatesArray = fechasUnicas;
        // console.log('📅 Fechas de categorias guardadas:', window.productsDatesArray); // Comentado para reducir spam
        
        // ⚡ NO REDIBUJAR - Solo actualizar datos internos (invisible al usuario)
        // El redibujado solo se hará cuando el usuario abra el calendario
        // Esto elimina el parpadeo visual durante los filtros
        
        // ✅ Flatpickr se actualiza automáticamente cuando se abre gracias a onDayCreate
        // console.log('✅ Fechas actualizadas silenciosamente sin redibujar');
        
        
        // Guardar opción seleccionada actual
        const valorActual = fechaSelect.value;
        
        // Solo actualizar SELECT si es SELECT (no INPUT de Flatpickr)
        if (fechaSelect.tagName === 'SELECT') {
            // Limpiar y agregar opción predeterminada
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
            
            // Restaurar selección si existía
            if (valorActual && fechasUnicas.includes(valorActual)) {
                fechaSelect.value = valorActual;
            }
        }
    } catch (error) {
        console.error('❌ Error cargando fechas:', error);
    }
}

// Función para mostrar categorias en tabla
function displayProducts(products, forceCacheBust = false, preserveState = null) {
    // FORZAR vista grid en móvil SIEMPRE
    const isMobile = window.innerWidth <= 768;
    
    if (isMobile) {
        console.log('📱 Móvil detectado en displayProducts, usando grid');
        displayProductsGrid(products);
        return;
    }
    
    // En desktop, usar la vista actual
    const currentView = getCurrentView();
    if (currentView === 'grid') {
        // Si está en vista grid, actualizar grid
        displayProductsGrid(products);
        return;
    }
    
    // Si está en vista tabla, actualizar tabla
    const tbody = document.getElementById('categorias-table-body');
    
    if (!products || products.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="loading-cell">
                    <div class="loading-content no-data">
                        <i class="fas fa-box-open"></i>
                        <span>No se encontraron categorias</span>
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = products.map((categoria, index) => {
        // Calcular total de productos de esta categoría
        const totalProductos = categoria.total_productos || categoria.productos_count || 0;
        
        return `
        <tr oncontextmenu="return false;" ondblclick="editCategoria(${categoria.id_categoria})" style="cursor: pointer;" data-product-id="${categoria.id_categoria}">
            <td><strong>${index + 1}</strong></td>
            <td onclick="event.stopPropagation();" ondblclick="event.stopPropagation(); showImageFullSize('${getProductImageUrl(categoria, forceCacheBust)}', '${(categoria.nombre_categoria || '').replace(/'/g, "\\'")}')"; style="cursor: zoom-in;">
                <div class="product-image-small">
                    <img src="${getProductImageUrl(categoria, forceCacheBust)}" 
                         alt="categoria" 
                         onerror="this.src='${AppConfig ? AppConfig.getImageUrl('default-category.png') : '/fashion-master/public/assets/img/default-category.png'}'; this.onerror=null;">
                </div>
            </td>
            <td>
                <div class="product-info">
                    <strong>${categoria.nombre_categoria}</strong>
                </div>
            </td>
            <td>
                ${categoria.descripcion_categoria || 'Sin descripción'}
            </td>
            <td>
                <span class="badge badge-info">${totalProductos} prod. relacionado${totalProductos !== 1 ? 's' : ''}</span>
            </td>
            <td>
                <span class="status-badge ${categoria.estado_categoria === 'activo' ? 'status-active' : 'status-inactive'}">
                    ${categoria.estado_categoria === 'activo' ? 'Activo' : 'Inactivo'}
                </span>
            </td>
            <td>${categoria.fecha_creacion_categoria ? categoria.fecha_creacion_categoria.split(' ')[0] : '-'}</td>
            <td>
                <div class="action-buttons">
                    <button class="btn-action btn-menu" onclick="event.stopPropagation(); showActionMenu(${categoria.id_categoria}, '${(categoria.nombre_categoria || '').replace(/'/g, "\\'")}', 0, '${categoria.estado_categoria}', event)" title="Acciones">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                </div>
            </td>
        </tr>`;
    }).join('');
    
    // 🔄 Inicializar eventos de ordenamiento después de renderizar la tabla
    setTimeout(() => {
        if (typeof initializeSortingEvents === 'function') {
            initializeSortingEvents();
        }
    }, 100);
}

// Función para actualizar estadísticas
function updateStats(pagination) {
    if (pagination) {
        const { current_page, total_pages, total_items, items_per_page } = pagination;
        const start = ((current_page - 1) * items_per_page) + 1;
        const end = Math.min(current_page * items_per_page, total_items);
        
        const showingStartEl = document.getElementById('showing-start-products');
        const showingEndEl = document.getElementById('showing-end-products');
        const totalProductsEl = document.getElementById('total-products');
        
        if (showingStartEl) showingStartEl.textContent = total_items > 0 ? start : 0;
        if (showingEndEl) showingEndEl.textContent = total_items > 0 ? end : 0;
        if (totalProductsEl) totalProductsEl.textContent = total_items;
    }
}

// Función para actualizar información de paginación
function updatePaginationInfo(pagination) {
    if (pagination) {
        currentPage = pagination.current_page || 1;
        totalPages = pagination.total_pages || 1;
        
        // Actualizar elementos de paginación si existen
        const currentPageEl = document.getElementById('current-page-products');
        const totalPagesEl = document.getElementById('total-pages-products');
        
        if (currentPageEl) currentPageEl.textContent = currentPage;
        if (totalPagesEl) totalPagesEl.textContent = totalPages;
        
        // Actualizar botones de paginación si existen
        const firstBtn = document.querySelector('[onclick="goToFirstPageProducts()"]');
        const prevBtn = document.querySelector('[onclick="previousPageProducts()"]');
        const nextBtn = document.querySelector('[onclick="nextPageProducts()"]');
        const lastBtn = document.querySelector('[onclick="goToLastPageProducts()"]');
        
        if (firstBtn) firstBtn.disabled = currentPage <= 1;
        if (prevBtn) prevBtn.disabled = currentPage <= 1;
        if (nextBtn) nextBtn.disabled = currentPage >= totalPages;
        if (lastBtn) lastBtn.disabled = currentPage >= totalPages;
    }
}

// Función de filtrado mejorada con jQuery
function filterCategories() {
    if (typeof $ === 'undefined') {
        return filterCategoriesVanilla();
    }
    
    const search = $('#search-categorias').val() || '';
    const status = $('#filter-status').val() || '';
    
    // Actualizar contador de filtros activos
    if (typeof updateFilterCount === 'function') {
        updateFilterCount();
    }
    
    // Mostrar indicador de carga
    showSearchLoading();
    
    // Reset página actual
    currentPage = 1;
    
    // 🎯 Recargar categorias con filtros
    console.log('🔍 Filtrando categorías con búsqueda:', search, 'y estado:', status);
    loadCategorias();
}

// Función de filtrado con vanilla JS como fallback
function filterCategoriesVanilla() {
    const searchInput = document.getElementById('search-categorias');
    const categorySelect = document.getElementById('filter-category');
    const marcaSelect = document.getElementById('filter-marca');
    const statusSelect = document.getElementById('filter-status');
    const stockSelect = document.getElementById('filter-stock');
    
    const search = searchInput ? searchInput.value || '' : '';
    const category = categorySelect ? categorySelect.value || '' : '';
    const marca = marcaSelect ? marcaSelect.value || '' : '';
    const status = statusSelect ? statusSelect.value || '' : '';
    const stock = stockSelect ? stockSelect.value || '' : '';
    
    // Mostrar indicador de carga
    showSearchLoading();
    
    // Reset página actual
    currentPage = 1;
    
    // 🎯 Recargar categorias con filtros
    console.log('🔍 Filtrando categorías (vanilla) con búsqueda:', search, 'y estado:', status);
    loadCategorias();
}

// Función para manejar búsqueda en tiempo real con jQuery
let searchTimeout;
function handleCategorySearchInput() {
    clearTimeout(searchTimeout);
    
    // Mostrar indicador visual de búsqueda
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
            filterCategories();
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
            filterCategories();
        }, 300);
    }
}

// Función para cambiar vista (tabla/grid)
function toggleView(viewType, skipAnimation = false) {
    console.log('🔄 Cambiando vista a:', viewType);
    
    // BLOQUEAR cambio a tabla en móvil
    const isMobile = window.innerWidth <= 768;
    if (isMobile && viewType === 'table') {
        console.warn('⛔ Vista tabla bloqueada en móvil');
        return; // No permitir cambio
    }
    
    // 💾 GUARDAR vista en localStorage
    try {
        localStorage.setItem('products_view_preference', viewType);
        console.log('💾 Vista guardada en localStorage:', viewType);
    } catch (e) {
        console.warn('⚠️ No se pudo guardar vista en localStorage:', e);
    }
    
    // LIMPIAR CACHE del smooth updater al cambiar vista
    if (window.categoriasTableUpdater) {
        window.categoriasTableUpdater.clearCache();
        console.log('🧹 Cache del updater limpiado al cambiar vista');
    }
    
    // CERRAR BURBUJA DE STOCK si está abierta (evita que quede con coordenadas incorrectas)
    closeStockBubble();
    
    // CERRAR MENÚS FLOTANTES si están abiertos
    if (categorias_activeFloatingContainer) {
        closeFloatingActionsAnimated();
    }
    
    const tableContainer = document.querySelector('.data-table-wrapper');
    const gridContainer = document.querySelector('.products-grid');
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
    
    // 🎨 TRANSICIÓN SUAVE entre vistas
    const fadeOutDuration = skipAnimation ? 0 : 200;
    const fadeInDuration = skipAnimation ? 0 : 300;
    
    if (viewType === 'grid') {
        // Fade out tabla
        tableContainer.style.transition = `opacity ${fadeOutDuration}ms ease, transform ${fadeOutDuration}ms ease`;
        tableContainer.style.opacity = '0';
        tableContainer.style.transform = 'scale(0.98)';
        
        setTimeout(() => {
            tableContainer.style.display = 'none';
            document.querySelector('.products-grid').style.display = 'grid';
            window.products_currentView = 'grid';
            
            // Fade in grid
            const grid = document.querySelector('.products-grid');
            grid.style.opacity = '0';
            grid.style.transform = 'scale(0.98)';
            grid.style.transition = `opacity ${fadeInDuration}ms ease, transform ${fadeInDuration}ms ease`;
            
            setTimeout(() => {
                grid.style.opacity = '1';
                grid.style.transform = 'scale(1)';
            }, 10);
            
            // Recargar categorias
            setTimeout(() => {
                loadProducts();
            }, fadeInDuration);
        }, fadeOutDuration);
        
    } else {
        // Fade out grid
        const grid = document.querySelector('.products-grid');
        grid.style.transition = `opacity ${fadeOutDuration}ms ease, transform ${fadeOutDuration}ms ease`;
        grid.style.opacity = '0';
        grid.style.transform = 'scale(0.98)';
        
        setTimeout(() => {
            grid.style.display = 'none';
            tableContainer.style.display = 'block';
            window.products_currentView = 'table';
            
            // Fade in tabla
            tableContainer.style.opacity = '0';
            tableContainer.style.transform = 'scale(0.98)';
            tableContainer.style.transition = `opacity ${fadeInDuration}ms ease, transform ${fadeInDuration}ms ease`;
            
            setTimeout(() => {
                tableContainer.style.opacity = '1';
                tableContainer.style.transform = 'scale(1)';
            }, 10);
            
            // Recargar categorias
            setTimeout(() => {
                loadProducts();
            }, fadeInDuration);
        }, fadeOutDuration);
    }
}

// Función para crear vista grid
function createGridView() {
    const gridContainer = document.createElement('div');
    gridContainer.className = 'products-grid';
    gridContainer.style.display = 'none';
    
    // Insertar después de la tabla
    const tableWrapper = document.querySelector('.data-table-wrapper');
    tableWrapper.parentNode.insertBefore(gridContainer, tableWrapper.nextSibling);
}

// Función para mostrar categorias en grid
function displayProductsGrid(products) {
    const gridContainer = document.querySelector('.products-grid');
    if (!gridContainer) return;
    
    if (!products || products.length === 0) {
        gridContainer.innerHTML = `
            <div class="no-products-message">
                <i class="fas fa-box-open"></i>
                <p>No se encontraron categorías</p>
            </div>
        `;
        return;
    }
    
    // Detectar si es móvil
    const isMobile = window.innerWidth <= 768;
    
    gridContainer.innerHTML = products.map(categoria => {
        const totalProductos = categoria.total_productos || categoria.productos_count || 0;
        
        // Generar HTML de imagen SIEMPRE usando la misma función que la tabla
        const imageUrl = getProductImageUrl(categoria);
        const hasImage = imageUrl && !imageUrl.includes('default-product.jpg');
        
        const imageHTML = `
            <div class="product-card-image-mobile ${hasImage ? '' : 'no-image'}">
                ${hasImage 
                    ? `<img src="${imageUrl}" alt="${categoria.nombre_categoria || 'categoría'}" onerror="this.parentElement.classList.add('no-image'); this.style.display='none'; this.parentElement.innerHTML='<i class=\\'fas fa-image\\'></i>';">` 
                    : '<i class="fas fa-image"></i>'}
            </div>
        `;
        
        return `
            <div class="product-card" ondblclick="editCategoria(${categoria.id_categoria})" style="cursor: pointer;" data-product-id="${categoria.id_categoria}">
                ${imageHTML}
                <div class="product-card-header">
                    <h3 class="product-card-title">${categoria.nombre_categoria || 'Sin nombre'}</h3>
                    <span class="product-card-status ${categoria.estado_categoria === 'activo' ? 'active' : 'inactive'}">
                        ${categoria.estado_categoria === 'activo' ? 'Activo' : 'Inactivo'}
                    </span>
                </div>
                
                <div class="product-card-body">
                    <div class="product-card-category">
                        <i class="fas fa-boxes"></i> ${totalProductos} producto${totalProductos !== 1 ? 's' : ''}
                    </div>
                    
                    ${categoria.descripcion_categoria ? `
                    <div class="product-card-description" style="margin-top: 10px; font-size: 0.85rem; color: rgba(255,255,255,0.7); line-height: 1.4;">
                        ${categoria.descripcion_categoria.substring(0, 80)}${categoria.descripcion_categoria.length > 80 ? '...' : ''}
                    </div>
                    ` : ''}
                </div>
                
                <div class="product-card-actions">
                    <button class="product-card-btn btn-edit" onclick="event.stopPropagation(); editCategoria(${categoria.id_categoria})" title="Editar categoría" style="background-color: #34a853 !important; color: white !important; border: none !important; box-shadow: 0 4px 8px rgba(52, 168, 83, 0.3) !important;">
                        <i class="fas fa-edit" style="color: white !important;"></i>
                    </button>
                    <button class="product-card-btn ${categoria.estado_categoria === 'activo' ? 'btn-deactivate' : 'btn-activate'}" 
                            onclick="event.stopPropagation(); changeCategoriaEstado(${categoria.id_categoria})" 
                            title="${categoria.estado_categoria === 'activo' ? 'Desactivar' : 'Activar'} categoría"
                            style="background-color: #6f42c1 !important; color: white !important; border: none !important;">
                        <i class="fas fa-${categoria.estado_categoria === 'activo' ? 'power-off' : 'toggle-on'}" style="color: white !important;"></i>
                    </button>
                    <button class="product-card-btn btn-delete" onclick="event.stopPropagation(); deleteCategoria(${categoria.id_categoria}, '${(categoria.nombre_categoria || 'categoría').replace(/'/g, "\\'")}')\" title="Eliminar categoría" style="background-color: #f44336 !important; color: white !important; border: none !important; box-shadow: 0 4px 8px rgba(244, 67, 54, 0.3) !important;">
                        <i class="fas fa-trash" style="color: white !important;"></i>
                    </button>
                </div>
            </div>
        `;
    }).join('');
}


// Función para aplicar Masonry layout (DESACTIVADA - causaba problemas de espacio vacío)
function applyMasonryLayout() {
    // Desactivada - se usa grid normal ahora
    return;
    
    const gridContainer = document.querySelector('.products-grid');
    if (!gridContainer || window.innerWidth > 768) return;
    
    // Esperar a que las imágenes se carguen
    const images = gridContainer.querySelectorAll('img');
    let loadedImages = 0;
    const totalImages = images.length;
    
    const positionCards = () => {
        const cards = gridContainer.querySelectorAll('.product-card');
        cards.forEach(card => {
            const height = card.offsetHeight;
            const rowSpan = Math.ceil((height + 10) / 8); // 10 es el gap, 8 es grid-auto-rows
            card.style.gridRowEnd = `span ${rowSpan}`;
        });
    };
    
    if (totalImages === 0) {
        // Si no hay imágenes, aplicar inmediatamente
        setTimeout(positionCards, 50);
    } else {
        // Esperar a que las imágenes se carguen
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
        
        // Si todas ya están cargadas
        if (loadedImages === totalImages) {
            positionCards();
        }
    }
    
    // Reajustar en cambios de tamaño
    let resizeTimeout;
    window.addEventListener('resize', () => {
        if (window.innerWidth <= 768) {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(positionCards, 100);
        }
    });
}

// ============ FUNCIONES PRINCIPALES categorias ============

// ===================================
// SISTEMA DE BOTONES FLOTANTES ANIMADOS - VERSIÓN AVANZADA
// ===================================

// Variables globales para el sistema flotante
let categorias_activeFloatingContainer = null;
let categorias_activeProductId = null;
let categorias_isAnimating = false;
let categorias_isClosing = false; // Nueva bandera para estado de cierre
let categorias_animationTimeout = null;
let categorias_floatingButtons = [];
let categorias_centerButton = null;
let categorias_lastClickTime = 0;
let categorias_clickDebounceDelay = 300; // 300ms entre clicks
let categorias_cancelableTimeouts = []; // Array para almacenar timeouts cancelables

// Función principal para mostrar botones flotantes
function showActionMenu(productId, productName, stock, estado, event) {
    // Si está cerrando suavemente, permitir cancelación y apertura rápida
    if (categorias_isClosing) {
        console.log('Cancelando cierre suave para abrir nuevo menú...');
        cancelSoftClose();
        // Reducir debounce para apertura más rápida después de cancelar
        categorias_lastClickTime = Date.now() - categorias_clickDebounceDelay + 50;
    }
    
    // Debounce: prevenir clicks muy rápidos
    const currentTime = Date.now();
    if (currentTime - categorias_lastClickTime < categorias_clickDebounceDelay) {
        console.log('Click muy rápido, ignorando...');
        return;
    }
    categorias_lastClickTime = currentTime;
    
    // Si está abriendo, no permitir
    if (categorias_isAnimating && !categorias_isClosing) {
        console.log('Ya hay una animación de apertura en curso...');
        return;
    }
    
    // CERRAR BURBUJA DE STOCK SI ESTÁ ABIERTA
    const existingBubbles = document.querySelectorAll('.stock-update-bubble');
    existingBubbles.forEach(bubble => {
        if (bubble && bubble.parentNode) {
            // Animación de salida
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
    
    // Si ya está abierto para el mismo categoria, cerrarlo suavemente
    if (categorias_activeFloatingContainer && categorias_activeProductId === productId) {
        closeFloatingActionsAnimated();
        return;
    }
    
    // Cerrar cualquier menú anterior con cierre rápido cancelable
    if (categorias_activeFloatingContainer && categorias_activeProductId !== productId) {
        closeFloatingActionsAnimated();
        // Esperar menos tiempo ya que el cierre es más rápido
        setTimeout(() => {
            // Verificar si el cierre no fue cancelado
            if (!categorias_isClosing || !categorias_activeFloatingContainer) {
                openNewMenu(productId, productName, stock, estado, event);
            }
        }, 400);
        return;
    }
    
    // Abrir directamente si no hay menú activo
    openNewMenu(productId, productName, stock, estado, event);
}

// Función auxiliar para abrir un nuevo menú
function openNewMenu(productId, productName, stock, estado, event) {
    // Limpiar cualquier contenedor huérfano antes de abrir
    cleanupOrphanedContainers();
    
    // Obtener el botón que disparó el evento - MEJORADO
    let triggerButton = null;
    
    if (event && event.currentTarget) {
        triggerButton = event.currentTarget;
    } else if (event && event.target) {
        // Buscar el botón padre si el click fue en el icono
        triggerButton = event.target.closest('.btn-menu');
    } else {
        // Fallback robusto: buscar entre todos los .btn-menu y comparar atributo onclick
        const allMenuButtons = document.querySelectorAll('.btn-menu');
        for (const btn of allMenuButtons) {
            const onclickAttr = btn.getAttribute('onclick') || '';
            if (onclickAttr.includes(`showActionMenu(${productId}`)) {
                triggerButton = btn;
                break;
            }
        }
    }
    
    if (!triggerButton) {
        console.warn('No se encontró el botón trigger para el categoria', productId);
        categorias_isAnimating = false;
        return;
    }
    
    // Verificar que el botón aún existe en el DOM
    if (!document.contains(triggerButton)) {
        console.warn('El botón trigger ya no está en el DOM');
        categorias_isAnimating = false;
        return;
    }
    
    categorias_isAnimating = true;
    categorias_activeProductId = productId;
    
    // Crear contenedor flotante con animaciones
    createAnimatedFloatingContainer(triggerButton, productId, productName, stock, estado);
}

// Función para limpiar contenedores huérfanos
function cleanupOrphanedContainers() {
    const orphanedContainers = document.querySelectorAll('.animated-floating-container');
    orphanedContainers.forEach(container => {
        try {
            if (container !== categorias_activeFloatingContainer) {
                container.remove();
            }
        } catch (e) {
            console.warn('Error eliminando contenedor huérfano:', e);
        }
    });
    
    // Limpiar botones huérfanos también
    const orphanedButtons = document.querySelectorAll('.animated-floating-button, .animated-center-button');
    orphanedButtons.forEach(button => {
        try {
            if (!button.closest('.animated-floating-container')) {
                button.remove();
            }
        } catch (e) {
            console.warn('Error eliminando botón huérfano:', e);
        }
    });
}

// Crear el contenedor flotante con animaciones avanzadas
function createAnimatedFloatingContainer(triggerButton, productId, productName, stock, estado) {
    // Limpiar cualquier menú anterior
    if (categorias_activeFloatingContainer) {
        closeFloatingActionsAnimated();
    }
    
    // Verificar que tenemos un trigger button válido
    if (!triggerButton) {
        categorias_isAnimating = false;
        return;
    }
    
    // Crear contenedor principal con ID único
    categorias_activeFloatingContainer = document.createElement('div');
    categorias_activeFloatingContainer.id = 'animated-floating-menu-' + productId;
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
        position: fixed !important;
        z-index: 9999999 !important;
        pointer-events: none !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        display: block !important;
    `;
    
    // Guardar referencia al trigger button
    categorias_activeFloatingContainer.triggerButton = triggerButton;
    
    // Crear botón central con los tres puntitos
    createCenterButton();
    
    // Definir acciones con colores vibrantes
    // Definir acciones con colores vibrantes (usando closures para capturar event) - SIN LABELS
    const actions = [
        { icon: 'fa-eye', color: '#1a73e8', actionFn: () => viewCategoria(productId) },
        { icon: 'fa-edit', color: '#34a853', actionFn: () => editCategoria(productId) },
        { icon: estado === 'activo' ? 'fa-power-off' : 'fa-toggle-on', color: '#9c27b0', actionFn: () => changeCategoriaEstado(productId) },
        { icon: 'fa-trash', color: '#f44336', actionFn: () => deleteCategoria(productId, productName) }
    ];
    
    // Crear botones flotantes con animaciones
    categorias_floatingButtons = [];
    const radius = 80;
    
    actions.forEach((action, index) => {
        const angle = (index / actions.length) * 2 * Math.PI - Math.PI / 2;
        createAnimatedButton(action, index, angle, radius);
    });
    
    // Agregar directamente al body para evitar problemas de z-index con la tabla
    document.body.appendChild(categorias_activeFloatingContainer);
    
    // Actualizar posiciones iniciales
    updateAnimatedButtonPositions();
    
    categorias_activeProductId = productId;
    
    // Event listeners con animaciones
    setupAnimatedEventListeners();
    
    // 🎯 Iniciar tracking continuo inmediato (antes de la animación)
    startContinuousTracking();
    
    // Iniciar animación de entrada
    startOpenAnimation();
}

// 🎯 Sistema de tracking continuo inmediato
let categorias_trackingInterval = null;

function startContinuousTracking() {
    // Limpiar interval anterior si existe
    if (categorias_trackingInterval) {
        clearInterval(categorias_trackingInterval);
    }
    
    // Actualizar posiciones cada 16ms (~60fps) para tracking ultra suave
    categorias_trackingInterval = setInterval(() => {
        if (categorias_activeFloatingContainer && !categorias_isClosing) {
            updateAnimatedButtonPositions();
        } else {
            // Si ya no hay contenedor, limpiar interval
            clearInterval(categorias_trackingInterval);
            categorias_trackingInterval = null;
        }
    }, 16); // 60 FPS
}

function stopContinuousTracking() {
    if (categorias_trackingInterval) {
        clearInterval(categorias_trackingInterval);
        categorias_trackingInterval = null;
    }
}

// Crear botón central con tres puntitos (para cerrar)
function createCenterButton() {
    categorias_centerButton = document.createElement('div');
    categorias_centerButton.className = 'animated-center-button';
    categorias_centerButton.innerHTML = '<i class="fas fa-ellipsis-v"></i>';
    
    categorias_centerButton.style.cssText = `
        position: fixed !important;
        width: 45px !important;
        height: 45px !important;
        border-radius: 50% !important;
        background: transparent !important;
        color: white !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        cursor: pointer !important;
        z-index: 10000000 !important;
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
    
    // Click para cerrar - RÁPIDO
    categorias_centerButton.addEventListener('click', (e) => {
        e.stopPropagation();
        closeFloatingActionsAnimatedFast(); // Usar versión rápida al hacer click directo
    });
    
    categorias_activeFloatingContainer.appendChild(categorias_centerButton);
}

// Crear botón animado individual
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
        position: fixed !important;
        width: 55px !important;
        height: 55px !important;
        border-radius: 50% !important;
        background: ${gradients[action.color] || action.color} !important;
        color: white !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        cursor: pointer !important;
        z-index: 10000001 !important;
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
        button.style.zIndex = '10000003';
        
        // Crear ripple effect
        createRippleEffect(button, action.color);
        
        // Tooltip deshabilitado - solo iconos
        // showTooltip(button, action.label);
    });
    
    button.addEventListener('mouseleave', () => {
        button.style.transform = 'scale(1) rotate(0deg)';
        button.style.boxShadow = `0 6px 20px ${action.color}40`;
        button.style.zIndex = '10000001';
        
        // Ocultar tooltip
        hideTooltip();
    });
    
    // Click handler con animación
    button.addEventListener('click', (e) => {
        e.stopPropagation();
        
        // Forzar cierre inmediato del menú
        forceCloseFloatingActions();
        
        // Animación de click del botón
        button.style.transform = 'scale(0.9) rotate(180deg)';
        setTimeout(() => {
            button.style.transform = 'scale(1.1) rotate(360deg)';
        }, 100);
        
        // Ejecutar acción después de un delay mínimo
        setTimeout(() => {
            try {
                action.actionFn();
            } catch (err) {
                console.error('Error ejecutando acción flotante:', err);
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
    
    // Agregar CSS de animación si no existe
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

// Función para actualizar posiciones de botones con animaciones
function updateAnimatedButtonPositions() {
    if (!categorias_activeFloatingContainer) {
        return;
    }
    
    if (!categorias_activeFloatingContainer.triggerButton) {
        return;
    }
    
    // Verificar que el trigger button aún existe en el DOM
    if (!document.contains(categorias_activeFloatingContainer.triggerButton)) {
        closeFloatingActionsAnimated();
        return;
    }
    
    // Usar getBoundingClientRect para obtener posición fija en la ventana
    const triggerRect = categorias_activeFloatingContainer.triggerButton.getBoundingClientRect();
    
    // Calcular centro del botón trigger en coordenadas de ventana (fixed)
    const finalCenterX = triggerRect.left + triggerRect.width / 2;
    const finalCenterY = triggerRect.top + triggerRect.height / 2;
    
    // Actualizar posición del botón central
    if (categorias_centerButton) {
        categorias_centerButton.style.left = `${finalCenterX - 22.5}px`;
        categorias_centerButton.style.top = `${finalCenterY - 22.5}px`;
    }
    
    // Actualizar posición de cada botón flotante
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

// Iniciar animación de apertura
function startOpenAnimation() {
    // Animar botón central primero
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
        }, 200 + (index * 100));
    });
    
    // Finalizar animación de apertura - bloquear cierre hasta que termine la entrada
    setTimeout(() => {
        categorias_isAnimating = false;
    }, 200 + (categorias_floatingButtons.length * 100) + 200); // Bloquear hasta que termine la animación
}

// Event listeners animados
function setupAnimatedEventListeners() {
    // Cerrar al hacer click fuera con animación
    const handleClick = (e) => {
        if (categorias_activeFloatingContainer && !categorias_activeFloatingContainer.contains(e.target)) {
            // Verificar que no es el botón trigger
            const isTriggerButton = e.target.closest('.btn-menu');
            if (!isTriggerButton) {
                closeFloatingActionsAnimated();
            }
        }
    };
    
    // Actualizar posiciones en resize con throttle
    let resizeTimeout;
    const handleResize = () => {
        if (!categorias_activeFloatingContainer) return;
        
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            if (categorias_activeFloatingContainer && !categorias_isAnimating) {
                updateAnimatedButtonPositions();
            }
        }, 150);
    };
    
    // Manejar scroll - actualizar posiciones en tiempo real
    let scrollTimeout;
    const handleScroll = () => {
        if (!categorias_activeFloatingContainer) return;
        
        // Actualizar posiciones inmediatamente para tracking fluido
        if (!categorias_isAnimating && !categorias_isClosing) {
            updateAnimatedButtonPositions();
        }
        
        // También verificar si el trigger sigue visible (con throttle)
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(() => {
            if (categorias_activeFloatingContainer && categorias_activeFloatingContainer.triggerButton) {
                const rect = categorias_activeFloatingContainer.triggerButton.getBoundingClientRect();
                const isVisible = rect.top >= -50 && rect.bottom <= (window.innerHeight + 50);
                
                if (!isVisible) {
                    // Si el trigger ya no es visible, cerrar el menú suavemente
                    closeFloatingActionsAnimated();
                }
            }
        }, 150);
    };
    
    // Agregar listeners
    setTimeout(() => {
        document.addEventListener('click', handleClick);
    }, 100); // Delay para evitar que el click que abre el menú lo cierre
    
    window.addEventListener('resize', handleResize, { passive: true });
    
    // Agregar listener de scroll a múltiples contenedores posibles
    const scrollableContainers = [
        document.querySelector('.data-table-wrapper'),  // Tabla de categorias
        document.querySelector('.scrollable-table'),    // Tabla scrollable
        document.querySelector('.admin-main'),          // ✨ Contenedor principal de admin.php
        document.querySelector('main'),                 // Tag main genérico
        document.body,                                  // Body del documento
        window                                          // Ventana global
    ];
    
    scrollableContainers.forEach(container => {
        if (container) {
            container.addEventListener('scroll', handleScroll, { passive: true });
        }
    });
    
    // Limpiar listeners cuando se cierre
    categorias_activeFloatingContainer.cleanup = () => {
        document.removeEventListener('click', handleClick);
        window.removeEventListener('resize', handleResize);
        
        clearTimeout(resizeTimeout);
        clearTimeout(scrollTimeout);
        
        scrollableContainers.forEach(container => {
            if (container) {
                container.removeEventListener('scroll', handleScroll);
            }
        });
    };
}

// ✨ Función para crear efecto de partículas
function createParticleEffect(sourceElement, centerX, centerY) {
    const particleCount = 8; // Número de partículas por botón
    const colors = ['#007bff', '#0056b3', '#66a3ff', '#ffffff'];
    
    for (let i = 0; i < particleCount; i++) {
        const particle = document.createElement('div');
        particle.className = 'floating-particle';
        
        // Posición inicial en el centro del botón
        particle.style.cssText = `
            position: fixed;
            left: ${centerX}px;
            top: ${centerY}px;
            width: 6px;
            height: 6px;
            background: ${colors[Math.floor(Math.random() * colors.length)]};
            border-radius: 50%;
            pointer-events: none;
            z-index: 10000000;
            opacity: 1;
            box-shadow: 0 0 8px rgba(0, 123, 255, 0.6);
        `;
        
        document.body.appendChild(particle);
        
        // Calcular dirección aleatoria
        const angle = (Math.PI * 2 * i) / particleCount + (Math.random() - 0.5) * 0.5;
        const distance = 30 + Math.random() * 40;
        const deltaX = Math.cos(angle) * distance;
        const deltaY = Math.sin(angle) * distance;
        
        // Animar partícula
        requestAnimationFrame(() => {
            particle.style.transition = 'all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
            particle.style.transform = `translate(${deltaX}px, ${deltaY}px) scale(0)`;
            particle.style.opacity = '0';
        });
        
        // Limpiar después de la animación
        setTimeout(() => {
            if (particle && particle.parentNode) {
                particle.parentNode.removeChild(particle);
            }
        }, 400);
    }
}

// ⚡ Cerrar menú flotante con animación RÁPIDA pero fluida
function closeFloatingActionsAnimatedFast() {
    // Si no hay contenedor activo, no hacer nada
    if (!categorias_activeFloatingContainer) {
        categorias_isAnimating = false;
        stopContinuousTracking();
        return;
    }
    
    // Si ya está cerrando, no hacer nada
    if (categorias_isClosing) {
        return;
    }
    
    // Si está animando la apertura, no permitir cerrar
    if (categorias_isAnimating) {
        return;
    }
    
    categorias_isAnimating = true;
    categorias_isClosing = true;
    
    // Detener tracking continuo
    stopContinuousTracking();
    
    // Limpiar timeouts
    if (categorias_animationTimeout) {
        clearTimeout(categorias_animationTimeout);
        categorias_animationTimeout = null;
    }
    
    hideTooltip();
    
    const containerToClose = categorias_activeFloatingContainer;
    const buttonsToClose = [...categorias_floatingButtons];
    const centerButtonToClose = categorias_centerButton;
    
    categorias_cancelableTimeouts.forEach(timeout => clearTimeout(timeout));
    categorias_cancelableTimeouts = [];
    
    // � ANIMACIÓN DE IMPLOSIÓN CON EFECTO PARTÍCULAS
    // Obtener posición del centro
    let centerX = 0, centerY = 0;
    if (centerButtonToClose && document.contains(centerButtonToClose)) {
        const rect = centerButtonToClose.getBoundingClientRect();
        centerX = rect.left + rect.width / 2;
        centerY = rect.top + rect.height / 2;
    }
    
    // Animar botones hacia el centro con delay escalonado
    buttonsToClose.forEach((button, index) => {
        if (button && document.contains(button)) {
            const timeout = setTimeout(() => {
                try {
                    if (!categorias_isClosing) return;
                    
                    // Obtener posición actual del botón
                    const buttonRect = button.getBoundingClientRect();
                    const buttonCenterX = buttonRect.left + buttonRect.width / 2;
                    const buttonCenterY = buttonRect.top + buttonRect.height / 2;
                    
                    // Calcular distancia al centro
                    const deltaX = centerX - buttonCenterX;
                    const deltaY = centerY - buttonCenterY;
                    
                    // 🎨 Efecto de partículas antes de la implosión
                    createParticleEffect(button, buttonCenterX, buttonCenterY);
                    
                    // Animación de implosión hacia el centro
                    button.style.transition = 'all 0.25s cubic-bezier(0.68, -0.55, 0.265, 1.55)';
                    button.style.transform = `translate(${deltaX}px, ${deltaY}px) scale(0) rotate(360deg)`;
                    button.style.opacity = '0';
                    button.style.filter = 'blur(3px)';
                } catch (e) {
                    console.warn('Error animando botón:', e);
                }
            }, index * 30); // 30ms de delay entre cada botón
            
            categorias_cancelableTimeouts.push(timeout);
        }
    });
    
    // Botón central hace un "pulso" y desaparece
    if (centerButtonToClose && document.contains(centerButtonToClose)) {
        const timeout = setTimeout(() => {
            try {
                if (!categorias_isClosing) return;
                
                // Pulso rápido antes de desaparecer
                centerButtonToClose.style.transition = 'all 0.15s cubic-bezier(0.68, -0.55, 0.265, 1.55)';
                centerButtonToClose.style.transform = 'scale(1.3) rotate(180deg)';
                
                // Luego desaparece
                setTimeout(() => {
                    if (centerButtonToClose && document.contains(centerButtonToClose)) {
                        centerButtonToClose.style.transition = 'all 0.15s cubic-bezier(0.4, 0.0, 0.2, 1)';
                        centerButtonToClose.style.transform = 'scale(0) rotate(360deg)';
                        centerButtonToClose.style.opacity = '0';
                        centerButtonToClose.style.filter = 'blur(4px)';
                    }
                }, 150);
            } catch (e) {
                console.warn('Error animando botón central:', e);
            }
        }, buttonsToClose.length * 30 + 50);
        
        categorias_cancelableTimeouts.push(timeout);
    }
    
    // Cleanup optimizado
    const cleanupDelay = buttonsToClose.length * 30 + 350;
    categorias_animationTimeout = setTimeout(() => {
        if (!categorias_isClosing) return;
        
        try {
            if (containerToClose && document.contains(containerToClose)) {
                if (containerToClose.cleanup) {
                    containerToClose.cleanup();
                }
                containerToClose.remove();
            }
        } catch (e) {
            console.warn('Error removiendo contenedor:', e);
        }
        
        categorias_activeFloatingContainer = null;
        categorias_centerButton = null;
        categorias_floatingButtons = [];
        categorias_activeProductId = null;
        categorias_isAnimating = false;
        categorias_isClosing = false;
        categorias_cancelableTimeouts = [];
        
        cleanupOrphanedContainers();
    }, cleanupDelay);
    
    categorias_cancelableTimeouts.push(categorias_animationTimeout);
}

// Cerrar menú flotante con animación (usa la versión rápida para todo)
function closeFloatingActionsAnimated() {
    // Usar la animación rápida pero fluida para todos los casos
    closeFloatingActionsAnimatedFast();
}

// Función para cancelar cierre suave y restaurar botones
function cancelSoftClose() {
    console.log('🔄 Cancelando cierre suave...');
    
    // Cancelar todos los timeouts pendientes
    categorias_cancelableTimeouts.forEach(timeout => {
        if (timeout) clearTimeout(timeout);
    });
    categorias_cancelableTimeouts = [];
    
    if (categorias_animationTimeout) {
        clearTimeout(categorias_animationTimeout);
        categorias_animationTimeout = null;
    }
    
    // Marcar que ya no está cerrando
    categorias_isClosing = false;
    
    // Si hay botones que están en medio de animación de cierre, restaurarlos suavemente
    if (categorias_floatingButtons.length > 0) {
        categorias_floatingButtons.forEach((button, index) => {
            if (button && document.contains(button)) {
                try {
                    // Restaurar transición suave
                    button.style.transition = 'all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
                    
                    // Restaurar estado visible con delay escalonado
                    setTimeout(() => {
                        button.style.transform = 'scale(1) rotate(0deg)';
                        button.style.opacity = '1';
                    }, index * 30);
                } catch (e) {
                    console.warn('Error restaurando botón:', e);
                }
            }
        });
    }
    
    // Restaurar botón central
    if (categorias_centerButton && document.contains(categorias_centerButton)) {
        try {
            categorias_centerButton.style.transition = 'all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
            setTimeout(() => {
                categorias_centerButton.style.transform = 'scale(1) rotate(360deg)';
                categorias_centerButton.style.opacity = '1';
            }, categorias_floatingButtons.length * 30);
        } catch (e) {
            console.warn('Error restaurando botón central:', e);
        }
    }
    
    // Resetear flag de animación después de restaurar
    setTimeout(() => {
        categorias_isAnimating = false;
        console.log('✅ Restauración completada, listo para nueva acción');
    }, categorias_floatingButtons.length * 30 + 300);
}

// Mantener compatibilidad con función anterior
function closeFloatingActions() {
    closeFloatingActionsAnimated();
}

// Función para forzar el cierre con retraso del menú flotante
function forceCloseFloatingActions() {
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
            categorias_activeProductId = null;
            categorias_isAnimating = false;
        }
        
        // Asegurarse de que no queden elementos flotantes huérfanos
        const orphanedContainers = document.querySelectorAll('.animated-floating-container');
        orphanedContainers.forEach(container => {
            try {
                container.remove();
            } catch (e) {
                console.warn('Error eliminando contenedor huérfano:', e);
            }
        });
    }, 320); // Retraso de 150ms antes del cierre forzado
}

// ============ SISTEMA DE MODALES ============



// Función para exportar categorias
async function exportCategories() {
    try {
        showNotification('Preparando exportación...', 'info');
        
        if (!categorias || categorias.length === 0) {
            showNotification('No hay categorias para exportar', 'warning');
            return;
        }

        // Verificar que XLSX esté disponible
        if (typeof XLSX === 'undefined') {
            showNotification('Librería de Excel no disponible', 'error');
            return;
        }

        // Preparar datos para Excel
        const excelData = [];
        
        // Encabezados
        excelData.push([
            'ID',
            'Nombre',
            'Categoría',
            'Marca',
            'Género',
            'Precio (S/)',
            'Stock Actual',
            'Stock Mínimo',
            'Estado',
            'Fecha Creación'
        ]);

        // Datos de categorias
        categorias.forEach(categoria => {
            const genero = categoria.genero_categoria || categoria.genero || 'Unisex';
            const generoLabel = genero === 'M' ? 'Masculino' : 
                              genero === 'F' ? 'Femenino' : 
                              genero === 'Kids' ? 'Kids' : 'Unisex';
            
            excelData.push([
                categoria.id_categoria || '',
                categoria.nombre_categoria || '',
                categoria.categoria_nombre || categoria.nombre_categoria || '',
                categoria.marca_categoria || '',
                generoLabel,
                categoria.precio_categoria != null ? parseFloat(categoria.precio_categoria) : 0,
                categoria.stock_actual_categoria != null ? parseInt(categoria.stock_actual_categoria) : 0,
                categoria.stock_minimo_categoria != null ? parseInt(categoria.stock_minimo_categoria) : 0,
                (categoria.activo == 1 || categoria.status_categoria == 1) ? 'Activo' : 'Inactivo',
                categoria.fecha_creacion_categoria || ''
            ]);
        });

        // Crear libro de Excel
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(excelData);

        // Configurar anchos de columna
        ws['!cols'] = [
            { wch: 8 },  // ID
            { wch: 40 }, // Nombre (más ancho)
            { wch: 20 }, // Categoría
            { wch: 15 }, // Marca
            { wch: 12 }, // Género
            { wch: 12 }, // Precio
            { wch: 12 }, // Stock Actual
            { wch: 12 }, // Stock Mínimo
            { wch: 10 }, // Estado
            { wch: 18 }  // Fecha
        ];

        // Estilo para encabezados (primera fila)
        const headerRange = XLSX.utils.decode_range(ws['!ref']);
        for (let C = headerRange.s.c; C <= headerRange.e.c; ++C) {
            const address = XLSX.utils.encode_col(C) + "1";
            if (!ws[address]) continue;
            ws[address].s = {
                font: { bold: true, color: { rgb: "FFFFFF" } },
                fill: { fgColor: { rgb: "4472C4" } },
                alignment: { horizontal: "center", vertical: "center" }
            };
        }

        // Agregar hoja al libro
        XLSX.utils.book_append_sheet(wb, ws, "categorias");

        // Generar archivo
        const fileName = `categorias_${new Date().toISOString().split('T')[0]}.xlsx`;
        XLSX.writeFile(wb, fileName);

        showNotification(`Excel exportado: ${categorias.length} categorias`, 'success');
        
    } catch (error) {
        console.error('Error al exportar:', error);
        showNotification('Error al exportar categorias', 'error');
    }
}

// Función para mostrar reporte de stock
function showCategoryReport() {
    try {
        if (!categorias || categorias.length === 0) {
            showNotification('No hay categorias para generar reporte', 'warning');
            return;
        }

        // Verificar que XLSX esté disponible
        if (typeof XLSX === 'undefined') {
            showNotification('Librería de Excel no disponible', 'error');
            return;
        }

        showNotification('Generando reporte de stock...', 'info');

        // Clasificar categorias por estado de stock
        const stockCritico = [];  // Stock = 0
        const stockBajo = [];     // Stock <= stock_minimo
        const stockNormal = [];   // Stock > stock_minimo

        categorias.forEach(categoria => {
            const stockActual = parseInt(categoria.stock_actual_categoria) || 0;
            const stockMinimo = parseInt(categoria.stock_minimo_categoria) || 5;
            const genero = categoria.genero_categoria || categoria.genero || 'Unisex';
            const generoLabel = genero === 'M' ? 'Masculino' : 
                              genero === 'F' ? 'Femenino' : 
                              genero === 'Kids' ? 'Kids' : 'Unisex';

            const item = {
                id: categoria.id_categoria || '',
                nombre: categoria.nombre_categoria || '',
                categoria: categoria.categoria_nombre || categoria.nombre_categoria || '',
                marca: categoria.marca_categoria || '',
                genero: generoLabel,
                stockActual: stockActual,
                stockMinimo: stockMinimo,
                diferencia: stockActual - stockMinimo,
                precio: parseFloat(categoria.precio_categoria) || 0,
                valorInventario: stockActual * (parseFloat(categoria.precio_categoria) || 0)
            };

            if (stockActual === 0) {
                stockCritico.push(item);
            } else if (stockActual <= stockMinimo) {
                stockBajo.push(item);
            } else {
                stockNormal.push(item);
            }
        });

        // Crear libro de Excel
        const wb = XLSX.utils.book_new();

        // ==================== HOJA 1: RESUMEN EJECUTIVO ====================
        const resumenData = [];
        resumenData.push(['REPORTE DE INVENTARIO - RESUMEN EJECUTIVO']);
        resumenData.push(['Fecha de Generación:', new Date().toLocaleString('es-PE')]);
        resumenData.push([]);
        resumenData.push(['INDICADORES CLAVE']);
        resumenData.push(['Total de categorias:', categorias.length]);
        resumenData.push(['categorias sin Stock (Crítico):', stockCritico.length]);
        resumenData.push(['categorias con Stock Bajo:', stockBajo.length]);
        resumenData.push(['categorias con Stock Normal:', stockNormal.length]);
        resumenData.push([]);
        
        // Calcular valor total del inventario
        const valorTotal = categorias.reduce((sum, p) => {
            return sum + ((parseInt(p.stock_actual_categoria) || 0) * (parseFloat(p.precio_categoria) || 0));
        }, 0);
        
        resumenData.push(['VALOR DE INVENTARIO']);
        resumenData.push(['Valor Total (S/):', valorTotal.toFixed(2)]);
        resumenData.push([]);
        
        // Estadísticas por categoría
        resumenData.push(['DISTRIBUCIÓN POR CATEGORÍA']);
        const categorias = {};
        categorias.forEach(p => {
            const cat = p.categoria_nombre || p.nombre_categoria || 'Sin categoría';
            if (!categorias[cat]) {
                categorias[cat] = { cantidad: 0, stock: 0 };
            }
            categorias[cat].cantidad++;
            categorias[cat].stock += parseInt(p.stock_actual_categoria) || 0;
        });
        
        resumenData.push(['Categoría', 'categorias', 'Stock Total']);
        Object.entries(categorias).forEach(([cat, data]) => {
            resumenData.push([cat, data.cantidad, data.stock]);
        });

        const wsResumen = XLSX.utils.aoa_to_sheet(resumenData);
        wsResumen['!cols'] = [{ wch: 35 }, { wch: 20 }];
        XLSX.utils.book_append_sheet(wb, wsResumen, "Resumen");

        // ==================== HOJA 2: STOCK CRÍTICO ====================
        const criticoData = [];
        criticoData.push(['categorias SIN STOCK - REQUIEREN ATENCIÓN INMEDIATA']);
        criticoData.push([]);
        criticoData.push(['ID', 'Nombre', 'Categoría', 'Marca', 'Género', 'Stock Actual', 'Stock Mínimo', 'Precio (S/)']);
        
        stockCritico.forEach(item => {
            criticoData.push([
                item.id, item.nombre, item.categoria, 
                item.marca, item.genero, item.stockActual, item.stockMinimo, item.precio
            ]);
        });

        const wsCritico = XLSX.utils.aoa_to_sheet(criticoData);
        wsCritico['!cols'] = [
            { wch: 8 }, { wch: 40 }, { wch: 20 }, 
            { wch: 15 }, { wch: 12 }, { wch: 12 }, { wch: 12 }, { wch: 12 }
        ];
        XLSX.utils.book_append_sheet(wb, wsCritico, "Stock Crítico");

        // ==================== HOJA 3: STOCK BAJO ====================
        const bajoData = [];
        bajoData.push(['categorias CON STOCK BAJO - REQUIEREN REPOSICIÓN']);
        bajoData.push([]);
        bajoData.push(['ID', 'Nombre', 'Categoría', 'Marca', 'Género', 'Stock Actual', 'Stock Mínimo', 'Diferencia', 'Precio (S/)']);
        
        stockBajo.forEach(item => {
            bajoData.push([
                item.id, item.nombre, item.categoria, 
                item.marca, item.genero, item.stockActual, item.stockMinimo, item.diferencia, item.precio
            ]);
        });

        const wsBajo = XLSX.utils.aoa_to_sheet(bajoData);
        wsBajo['!cols'] = [
            { wch: 8 }, { wch: 40 }, { wch: 20 }, 
            { wch: 15 }, { wch: 12 }, { wch: 12 }, { wch: 12 }, { wch: 12 }, { wch: 12 }
        ];
        XLSX.utils.book_append_sheet(wb, wsBajo, "Stock Bajo");

        // ==================== HOJA 4: INVENTARIO COMPLETO ====================
        const inventarioData = [];
        inventarioData.push(['INVENTARIO COMPLETO - TODOS LOS categorias']);
        inventarioData.push([]);
        inventarioData.push([
            'ID', 'Nombre', 'Categoría', 'Marca', 'Género', 
            'Stock Actual', 'Stock Mínimo', 'Diferencia', 'Precio (S/)', 
            'Valor Inventario (S/)', 'Estado Stock'
        ]);
        
        categorias.forEach(categoria => {
            const stockActual = parseInt(categoria.stock_actual_categoria) || 0;
            const stockMinimo = parseInt(categoria.stock_minimo_categoria) || 5;
            const precio = parseFloat(categoria.precio_categoria) || 0;
            const genero = categoria.genero_categoria || categoria.genero || 'Unisex';
            const generoLabel = genero === 'M' ? 'Masculino' : 
                              genero === 'F' ? 'Femenino' : 
                              genero === 'Kids' ? 'Kids' : 'Unisex';
            
            let estadoStock = 'Normal';
            if (stockActual === 0) estadoStock = 'CRÍTICO';
            else if (stockActual <= stockMinimo) estadoStock = 'Bajo';
            
            inventarioData.push([
                categoria.id_categoria || '',
                categoria.nombre_categoria || '',
                categoria.categoria_nombre || categoria.nombre_categoria || '',
                categoria.marca_categoria || '',
                generoLabel,
                stockActual,
                stockMinimo,
                stockActual - stockMinimo,
                precio,
                (stockActual * precio).toFixed(2),
                estadoStock
            ]);
        });

        const wsInventario = XLSX.utils.aoa_to_sheet(inventarioData);
        wsInventario['!cols'] = [
            { wch: 8 }, { wch: 40 }, { wch: 20 }, 
            { wch: 15 }, { wch: 12 }, { wch: 12 }, { wch: 12 }, 
            { wch: 12 }, { wch: 12 }, { wch: 15 }, { wch: 12 }
        ];
        XLSX.utils.book_append_sheet(wb, wsInventario, "Inventario Completo");

        // Generar archivo
        const fileName = `Reporte_Stock_${new Date().toISOString().split('T')[0]}.xlsx`;
        XLSX.writeFile(wb, fileName);

        // Mostrar resumen en notificación
        const mensaje = `Reporte generado: ${stockCritico.length} críticos, ${stockBajo.length} bajos, ${stockNormal.length} normales`;
        showNotification(mensaje, 'success');
        
    } catch (error) {
        console.error('Error al generar reporte:', error);
        showNotification('Error al generar reporte de stock', 'error');
    }
}

// Función para limpiar búsqueda con animación
function clearCategorySearch() {
    if (typeof $ !== 'undefined') {
        const searchInput = $('#search-categorias');
        searchInput.val('').focus();
        
        // Animación visual
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
    
    filterCategories();
}

// Función para limpiar todos los filtros con efectos visuales
function clearAllCategoryFilters() {
    if (typeof $ !== 'undefined') {
        // Limpiar todos los campos con jQuery
        $('#search-categorias').val('');
        $('#filter-status').val('');
        $('#filter-fecha-value').val('');
        
        // Limpiar Flatpickr
        if (window.productsDatePicker) {
            window.productsDatePicker.clear();
        }
        
        // Resetear texto del botón de fecha
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
            'filter-status',
            'filter-fecha-value'
        ];
        
        elements.forEach(id => {
            const element = document.getElementById(id);
            if (element) element.value = '';
        });
        
        // Limpiar Flatpickr
        if (window.productsDatePicker) {
            window.productsDatePicker.clear();
        }
        
        // Resetear texto del botón de fecha
        const filterFechaText = document.getElementById('filter-fecha-text');
        if (filterFechaText) {
            filterFechaText.textContent = 'Seleccionar fechas';
        }
    }
    
    // 🔄 Limpiar estado de ordenamiento de columnas
    currentSortColumn = null;
    currentSortOrder = 'asc';
    
    // Remover clases 'sorted' de todas las columnas
    const sortedHeaders = document.querySelectorAll('th.sortable.sorted');
    sortedHeaders.forEach(header => {
        header.classList.remove('sorted');
        header.removeAttribute('data-sort-direction');
    });
    
    console.log('✅ Estado de ordenamiento limpiado');
    
    // Mostrar notificación
    // showNotification('Filtros limpiados', 'info');
    
    filterCategories();
}

// Función para acciones en lote
async function handleBulkProductAction(action) {
    const selectedProducts = getSelectedProducts();
    
    if (selectedProducts.length === 0) {
        // // showNotification('Por favor selecciona al menos un categoria', 'warning');
        return;
    }    
    const confirmMessage = `¿Estás seguro de ${action} ${selectedProducts.length} categoria(s)?`;
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
                throw new Error('Acción no válida');
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
            // showNotification(`${action} completado para ${selectedProducts.length} categoria(s)`, 'success');
            loadProducts(); // Recargar lista
            clearProductSelection();
        } else {
            throw new Error(result.message || 'Error en operación en lote');
        }
        
    } catch (error) {
        // showNotification('Error: ' + error.message, 'error');
    }
}

// Función para toggle select all
function toggleSelectAllProducts(checkbox) {
    
    const productCheckboxes = document.querySelectorAll('input[name="product_select"]');
    productCheckboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
    
    updateBulkActionButtons();
}

// Función para ver categoria (wrapper que llama al parent)
function viewCategoria(id) {
    console.log('👁️ viewCategoria() llamado con ID:', id);
    
    // Verificar si el ID es válido
    if (!id || id === 'undefined' || id === 'null') {
        console.error('❌ ID inválido para ver:', id);
        if (typeof showNotification === 'function') {
            showNotification('Error: ID de categoría inválido', 'error');
        }
        return;
    }
    
    // Llamar a la función de modal de categoría
    console.log('✅ Redirigiendo a showViewCategoriaModal');
    if (typeof window.showViewCategoriaModal === 'function') {
        window.showViewCategoriaModal(id);
    } else {
        console.error('❌ showViewCategoriaModal NO disponible');
        alert('Error: No se pudo abrir el modal de ver categoría');
    }
}

// Alias para compatibilidad con código existente
window.viewProduct = viewCategoria;

// ===== FUNCIÓN GLOBAL PARA CERRAR BURBUJA DE STOCK =====
function closeStockBubble() {
    const existingBubbles = document.querySelectorAll('.stock-update-bubble');
    const existingOverlays = document.querySelectorAll('.stock-bubble-overlay');
    
    existingBubbles.forEach(bubble => {
        // Limpiar listeners si existen
        if (bubble.updatePositionListener) {
            window.removeEventListener('scroll', bubble.updatePositionListener, true);
            window.removeEventListener('resize', bubble.updatePositionListener);
        }
        
        // Animación de salida
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
    
    console.log('🗑️ Burbujas de stock cerradas');
}

// Función para editar categoria
async function editCategoria(id) {
    console.log('🔧 editCategoria() llamado con ID:', id);
    
    // Verificar si el ID es válido
    if (!id || id === 'undefined' || id === 'null') {
        console.error('❌ ID inválido para editar:', id);
        if (typeof showNotification === 'function') {
            showNotification('Error: ID de categoría inválido', 'error');
        }
        return;
    }
    
    // Debug: Verificar disponibilidad de funciones
    console.log('🔍 Buscando showEditCategoriaModal en:', {
        'window': typeof window.showEditCategoriaModal,
        'parent': typeof parent?.showEditCategoriaModal,
        'top': typeof top?.showEditCategoriaModal
    });
    
    // Como NO estamos en iframe, parent === window
    // Buscar directamente en window
    if (typeof window.showEditCategoriaModal === 'function') {
        console.log('✅ Llamando a window.showEditCategoriaModal');
        window.showEditCategoriaModal(id);
    } else {
        console.error('❌ showEditCategoriaModal NO disponible. Funciones disponibles:', Object.keys(window).filter(k => k.includes('Categoria')));
        console.warn('⚠️ Usando fallback: abrir en nueva ventana');
        // Fallback: abrir en nueva ventana
        const url = AppConfig ? AppConfig.getViewUrl(`admin/categorias_modal.php?action=edit&id=${id}`) : `/fashion-master/app/views/admin/categorias_modal.php?action=edit&id=${id}`;
        window.open(url, 'CategoryEdit', 'width=900,height=700');
    }
}

// Alias para compatibilidad con código existente
window.editProduct = editCategoria;

// Función para actualizar stock - MEJORADA CON BURBUJA SIN BOTONES
function updateStock(id, currentStock, event) {
    // VERIFICAR SI YA EXISTE UNA BURBUJA ABIERTA PARA ESTE categoria (TOGGLE)
    const existingBubble = document.querySelector(`.stock-update-bubble[data-product-id="${id}"]`);
    if (existingBubble) {
        console.log('🔄 Burbuja ya existe para este categoria, cerrando (TOGGLE)...');
        closeStockBubble();
        return; // SALIR - No abrir de nuevo
    }
    
    // CERRAR MENÚ FLOTANTE SI ESTÁ ABIERTO (sin bloquear futuros menús)
    if (categorias_activeFloatingContainer) {
        // Cerrar con animación
        closeFloatingActionsAnimated();
    }
    
    // Forzar eliminación de cualquier menú flotante residual
    const allFloatingMenus = document.querySelectorAll('.animated-floating-container');
    allFloatingMenus.forEach(menu => {
        if (menu && menu.parentNode) {
            menu.remove();
        }
    });
    
    // Resetear variables globales del menú flotante
    categorias_activeFloatingContainer = null;
    categorias_activeProductId = null;
    categorias_isAnimating = false;
    if (categorias_animationTimeout) {
        clearTimeout(categorias_animationTimeout);
        categorias_animationTimeout = null;
    }
    
    // Eliminar cualquier burbuja existente (de otros categorias)
    closeStockBubble();
    
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
    
    // Crear burbuja de stock - PEQUEÑA (50x50px) estilo botones flotantes, expandible hasta 3 dígitos
    const stockBubble = document.createElement('div');
    stockBubble.className = 'stock-update-bubble';
    stockBubble.setAttribute('data-product-id', id); // Agregar ID del categoria para identificar
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
    
    // Encontrar el botón que disparó la acción (puede ser btn-menu de tabla o btn-stock de grid)
    // Primero intentar obtenerlo del evento
    let triggerButton = null;
    let isGridView = false;
    
    if (event) {
        // Intentar desde currentTarget
        triggerButton = event.currentTarget;
        
        // Verificar si es un botón de la vista grid
        if (triggerButton && triggerButton.classList.contains('product-card-btn')) {
            isGridView = true;
            console.log('✅ Detectado: Vista Grid desde botón');
        }
        // Si es un botón flotante, ignorar y buscar el botón real
        else if (triggerButton && triggerButton.classList.contains('animated-floating-button')) {
            triggerButton = null; // Resetear para buscar el botón correcto
            console.log('⚠️ Evento desde botón flotante, buscando botón real...');
        }
        // Si es el btn-menu de la tabla
        else if (triggerButton && triggerButton.classList.contains('btn-menu')) {
            isGridView = false;
            console.log('✅ Detectado: Vista Tabla desde btn-menu');
        }
    }
    
    // Si aún no tenemos el botón, buscarlo en el DOM por el ID del categoria
    if (!triggerButton) {
        console.log('🔍 Buscando botón en DOM para categoria ID:', id);
        
        // Determinar qué vista está visible actualmente
        const tableContainer = document.querySelector('.data-table-wrapper');
        const gridContainer = document.querySelector('.products-grid');
        const isTableVisible = tableContainer && tableContainer.style.display !== 'none';
        const isGridVisible = gridContainer && gridContainer.style.display !== 'none';
        
        console.log('📊 Vistas visibles - Tabla:', isTableVisible, 'Grid:', isGridVisible);
        
        // Buscar en la vista VISIBLE primero
        if (isGridVisible) {
            // Buscar en vista grid (visible)
            const productCard = document.querySelector(`.product-card[data-product-id="${id}"]`);
            if (productCard) {
                triggerButton = productCard.querySelector('.btn-stock');
                if (triggerButton) {
                    isGridView = true;
                    console.log('✅ Encontrado en Grid:', triggerButton);
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
                    console.log('✅ Encontrado en Tabla:', triggerButton);
                }
            }
        }
    }
    
    // Último recurso: buscar por atributo onclick en la tabla
    if (!triggerButton) {
        triggerButton = document.querySelector(`[onclick*="showActionMenu(${id}"]`);
        if (triggerButton) {
            isGridView = false;
            console.log('✅ Encontrado por onclick:', triggerButton);
        }
    }
    
    if (!triggerButton) {
        console.error('❌ No se encontró el botón para el categoria', id);
        return;
    }
    
    // VALIDAR QUE EL BOTÓN ESTÉ VISIBLE (no en una vista oculta)
    const rect = triggerButton.getBoundingClientRect();
    if (rect.width === 0 || rect.height === 0) {
        console.error('❌ El botón encontrado está oculto (width/height = 0)');
        console.error('   Botón:', triggerButton);
        console.error('   Rect:', rect);
        closeStockBubble(); // Cerrar cualquier burbuja residual
        return;
    }
    
    console.log('✅ Botón final encontrado:', triggerButton, 'Vista Grid:', isGridView);
    
    // USAR POSICIÓN FIXED (viewport) como los botones flotantes
    const triggerRect = triggerButton.getBoundingClientRect();
    
    // Calcular centro del botón en coordenadas del viewport
    const centerX = triggerRect.left + (triggerRect.width / 2);
    const centerY = triggerRect.top + (triggerRect.height / 2);
    
    // Posición según la vista
    const bubbleSize = 40;
    const radius = 65;
    let angle;
    
    if (isGridView) {
        // En vista grid: arriba del botón (ángulo 270° = -π/2)
        angle = -Math.PI / 2; // 270° = arriba
    } else {
        // En vista tabla: a la izquierda del botón (ángulo 180° = π)
        angle = Math.PI; // 180° = izquierda
    }
    
    // Calcular posición con POSITION FIXED (coordenadas del viewport)
    const bubbleX = centerX + (Math.cos(angle) * radius) - (bubbleSize / 2);
    const bubbleY = centerY + (Math.sin(angle) * radius) - (bubbleSize / 2);
    
    // DEBUG: Mostrar valores calculados
    console.log('📍 Cálculo con POSITION FIXED:', {
        'Trigger (botón viewport)': { 
            top: triggerRect.top.toFixed(2), 
            left: triggerRect.left.toFixed(2),
            width: triggerRect.width,
            height: triggerRect.height
        },
        'Centro (viewport)': { 
            centerX: centerX.toFixed(2), 
            centerY: centerY.toFixed(2) 
        },
        'Fórmula': {
            'cos(π) * 65': (Math.cos(angle) * radius).toFixed(2),
            'sin(π) * 65': (Math.sin(angle) * radius).toFixed(2),
            'bubbleSize/2': (bubbleSize / 2)
        },
        '🎯 POSICIÓN FINAL (fixed)': { 
            bubbleX: bubbleX.toFixed(2), 
            bubbleY: bubbleY.toFixed(2) 
        }
    });
    
    // Aplicar estilos - POSICIÓN FIXED (viewport) como botones flotantes - Se expande según dígitos
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
    
    // Guardar referencia al botón para recalcular posición en scroll/resize
    stockBubble.triggerButton = triggerButton;
    stockBubble.isGridView = isGridView;
    
    // Estilos para el input - SIN SUBRAYADO y con expansión ovalada
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
        
        /* Forzar eliminación de cualquier estilo de Chrome/Edge */
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
    
    // Actualizar posición en scroll/resize (con position fixed)
    const updateBubblePosition = () => {
        if (!stockBubble || !stockBubble.triggerButton) return;
        
        const triggerRect = stockBubble.triggerButton.getBoundingClientRect();
        
        const centerX = triggerRect.left + triggerRect.width / 2;
        const centerY = triggerRect.top + triggerRect.height / 2;
        
        const bubbleSize = 40;
        const radius = 65;
        
        // Usar el ángulo guardado según la vista
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
    
    // Activar animación de entrada con reflow
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
            
            // Ajustar ancho de la burbuja según el número de dígitos (expansión ovalada)
            const adjustBubbleWidth = () => {
                const value = input.value.toString();
                const numDigits = value.length || 1;
                
                // Ancho base 40px, +12px por cada dígito extra
                let newWidth = 40;
                if (numDigits === 2) {
                    newWidth = 52; // Más ovalado para 2 dígitos
                } else if (numDigits >= 3) {
                    newWidth = 64; // Más ovalado para 3 dígitos
                }
                
                stockBubble.style.width = newWidth + 'px';
                
                // Recalcular posición para centrar la burbuja expandida
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
            
            // Limitar a 3 dígitos
            input.addEventListener('input', function(e) {
                // Eliminar cualquier carácter no numérico
                this.value = this.value.replace(/[^0-9]/g, '');
                
                // Limitar a 3 dígitos (máximo 999)
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
    
    // Función para guardar
    function saveStock() {
        if (!stockBubble) {
            console.error('❌ stockBubble no existe');
            return;
        }
        
        const input = stockBubble.querySelector('#stockInput');
        if (!input) {
            console.error('❌ input no existe');
            return;
        }
        
        const newStock = parseInt(input.value);
        
        if (isNaN(newStock) || newStock < 0 || newStock > 999) {
            // Animación de error - shake sin afectar el scale
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
        
        // Animación de salida
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
            console.log('📦 Respuesta del servidor (update_stock):', data);
            
            if (data.success) {
                console.log('✅ Stock actualizado exitosamente en BD');
                console.log('📊 categoria recibido:', data.product);
                
                // Mostrar notificación de éxito
                if (typeof showNotification === 'function') {
                    showNotification(`✅ Stock actualizado a ${newStock} unidades`, 'success');
                }
                
                // Usar actualización SUAVE sin recargar toda la tabla
                if (window.categoriasTableUpdater && data.product) {
                    console.log('🎯 Usando SmoothTableUpdater para actualizar solo el stock del categoria:', id);
                    console.log('� Verificando smoothTableUpdater:', typeof window.categoriasTableUpdater);
                    
                    try {
                        // Actualizar solo este categoria especificando que cambió el campo 'stock'
                        // Parámetros: (productId, updatedData, changedFields)
                        window.categoriasTableUpdater.updateSingleProduct(data.product.id_categoria, data.product, ['stock']);
                        console.log('✅ Actualización suave completada - solo campo stock');
                    } catch (error) {
                        console.error('❌ Error en smoothTableUpdater:', error);
                        console.log('🔄 Fallback: recargando tabla completa...');
                        loadProducts(true);
                    }
                } else {
                    console.warn('⚠️ SmoothTableUpdater no disponible o categoria no retornado');
                    console.warn('   - smoothTableUpdater existe:', !!window.categoriasTableUpdater);
                    console.warn('   - categoria recibido:', !!data.product);
                    console.log('🔄 Fallback: recargando tabla completa...');
                    loadProducts(true);
                }
                
                // Cerrar burbuja y overlay
                setTimeout(() => {
                    if (overlay && overlay.parentNode) overlay.remove();
                    if (stockBubble && stockBubble.parentNode) stockBubble.remove();
                }, 400);
            } else {
                console.error('❌ Error del servidor:', data.error || 'Error desconocido');
                if (typeof showNotification === 'function') {
                    showNotification('❌ Error al actualizar stock: ' + (data.error || 'Error desconocido'), 'error');
                }
                if (overlay && overlay.parentNode) overlay.remove();
                if (stockBubble && stockBubble.parentNode) stockBubble.remove();
            }
        })
        .catch(error => {
            if (typeof showNotification === 'function') {
                // showNotification('❌ Error de conexión', 'error');
            }
            if (overlay && overlay.parentNode) overlay.remove();
            if (stockBubble && stockBubble.parentNode) stockBubble.remove();
        });
    }
    
    // Variable para guardar el handler del click outside
    let clickOutsideHandler = null;
    
    // Función para cerrar sin guardar
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
        console.error('❌ No se encontró el input de stock');
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
    // El click se detectará solo cuando hagamos click en el área del overlay
    
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
    
    // Agregar listener después de un pequeño delay para evitar que se cierre inmediatamente
    setTimeout(() => {
        document.addEventListener('click', clickOutsideHandler);
    }, 100);
}

// Función para toggle status
async function toggleProductStatus(id, currentStatus) {
    
    const newStatus = !currentStatus;
    const action = newStatus ? 'activar' : 'desactivar';
    
    if (!confirm(`¿Estás seguro de ${action} este categoria?`)) return;
    
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
            // showNotification(`categoria ${action} exitosamente`, 'success');
            loadProducts(); // Recargar lista
        } else {
            throw new Error(result.message || 'Error al cambiar estado');
        }
        
    } catch (error) {
        // showNotification('Error: ' + error.message, 'error');
    }
}

// Función para cambiar estado del categoria (activo/inactivo)
async function changeCategoriaEstado(id) {
    try {
        // Obtener estado actual de la categoría
        const response = await fetch(`${CONFIG.apiUrl}?action=get&id=${id}`);
        const result = await response.json();
        
        if (!response.ok || !result.success) {
            console.error('Error al obtener datos de la categoría');
            if (typeof showNotification === 'function') {
                showNotification('Error al obtener datos de la categoría', 'error');
            }
            return;
        }
        
        const currentEstado = result.category ? result.category.estado_categoria : 'activo';
        const newEstado = currentEstado === 'activo' ? 'inactivo' : 'activo';
        
        console.log(`Cambiando estado de ${currentEstado} a ${newEstado} para categoría ${id}`);
        
        // Cambiar estado directamente sin confirmación
        const updateResponse = await fetch(`${CONFIG.apiUrl}?action=change_estado`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `id=${id}&estado=${newEstado}`
        });
        
        const updateResult = await updateResponse.json();
        
        if (updateResponse.ok && updateResult.success) {
            console.log('✅ Estado de categoría cambiado exitosamente');
            
            // NO mostrar notificación al cambiar estado (solo en crear/editar)
            
            // Verificar smoothTableUpdater
            console.log('🔍 window.categoriasTableUpdater:', window.categoriasTableUpdater);
            console.log('🔍 typeof updateSingleProduct:', typeof window.categoriasTableUpdater?.updateSingleProduct);
            
            // Usar actualización suave si está disponible
            if (window.categoriasTableUpdater && updateResult.category) {
                console.log('🎯 Usando actualización suave para cambiar estado de la categoría:', id);
                console.log('📊 Datos de categoría a actualizar:', updateResult.category);
                
                // LLAMAR con await para ver si hay errores
                try {
                    await window.categoriasTableUpdater.updateSingleProduct(id, updateResult.category);
                    console.log('✅ updateSingleProduct completado sin errores');
                } catch (error) {
                    console.error('❌ Error en updateSingleProduct:', error);
                    console.error('Stack:', error.stack);
                }
            } else {
                console.log('⚠️ SmoothTableUpdater no disponible o categoría no retornada');
                console.log('   - smoothTableUpdater existe:', !!window.categoriasTableUpdater);
                console.log('   - category existe:', !!updateResult.category);
                // Recargar lista
                loadCategorias();
            }
        } else {
            console.error('Error al cambiar estado de categoría:', updateResult.error);
            if (typeof showNotification === 'function') {
                showNotification(updateResult.error || 'Error al cambiar estado', 'error');
            }
        }
        
    } catch (error) {
        console.error('Error en changeCategoriaEstado:', error.message);
        if (typeof showNotification === 'function') {
            showNotification('Error de conexión al cambiar estado', 'error');
        }
    }
}

// Alias para compatibilidad con código existente
window.changeProductEstado = changeCategoriaEstado;


// ============ FUNCIONES DE PAGINACIÓN ============

function goToFirstPageProducts() {
    if (currentPage > 1) {
        currentPage = 1;
        loadProducts();
    }
}

function previousPageProducts() {
    if (currentPage > 1) {
        currentPage--;
        loadProducts();
    }
}

function nextPageProducts() {
    if (currentPage < totalPages) {
        currentPage++;
        loadProducts();
    }
}

function goToLastPageProducts() {
    if (currentPage < totalPages) {
        currentPage = totalPages;
        loadProducts();
    }
}

// ============ FUNCIONES AUXILIARES ============

// Función para obtener categorias seleccionados
function getSelectedProducts() {
    const checkboxes = document.querySelectorAll('input[name="product_select"]:checked');
    return Array.from(checkboxes).map(cb => parseInt(cb.value));
}

// Función para limpiar selección de categorias
function clearProductSelection() {
    const checkboxes = document.querySelectorAll('input[name="product_select"]');
    checkboxes.forEach(cb => cb.checked = false);
    
    const selectAll = document.querySelector('input[type="checkbox"][onchange*="toggleSelectAllProducts"]');
    if (selectAll) selectAll.checked = false;
    
    updateBulkActionButtons();
}

// Función para actualizar botones de acciones en lote
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
// Todas las funciones de notificación han sido desactivadas por solicitud del usuario

// ============ INICIALIZACIÓN ============

// Función para actualizar contador de resultados
function updateResultsCounter(showing, total) {
    const showingStartEl = document.getElementById('showing-start-products');
    const showingEndEl = document.getElementById('showing-end-products');
    const totalProductsEl = document.getElementById('total-products');
    
    if (showingStartEl) showingStartEl.textContent = showing > 0 ? 1 : 0;
    if (showingEndEl) showingEndEl.textContent = showing;
    if (totalProductsEl) totalProductsEl.textContent = total;
}

// Función de inicialización principal
function initializeProductsModule() {
    
    // ===== INICIALIZAR CATEGORIASTABLEUPDATER PARA CATEGORÍAS (FORZADO) =====
    const initCategoriasUpdater = () => {
        console.log('🔧 Verificando disponibilidad de CategoriasTableUpdater...');
        console.log('CategoriasTableUpdater type:', typeof CategoriasTableUpdater);
        
        // 🔥 SIEMPRE destruir instancia anterior antes de crear nueva
        if (window.categoriasTableUpdater) {
            console.log('🗑️ Destruyendo instancia previa de CategoriasTableUpdater...');
            if (typeof window.categoriasTableUpdater.destroy === 'function') {
                window.categoriasTableUpdater.destroy();
            }
            window.categoriasTableUpdater = null;
        }
        
        // ✅ Crear NUEVA instancia SOLO si la clase está disponible
        if (typeof CategoriasTableUpdater !== 'undefined') {
            console.log('✅ CategoriasTableUpdater encontrado - creando NUEVA instancia para CATEGORÍAS...');
            window.categoriasTableUpdater = new CategoriasTableUpdater();
            console.log('✅ CategoriasTableUpdater para CATEGORÍAS inicializado correctamente');
            console.log('📋 Métodos disponibles:', Object.getOwnPropertyNames(Object.getPrototypeOf(window.categoriasTableUpdater)));
        } else {
            console.error('❌ CategoriasTableUpdater no está definido - verificar carga de smooth-table-update-categories.js');
            window.categoriasTableUpdater = null;
        }
    };
    
    // Escuchar el evento de carga del script
    window.addEventListener('smoothTableUpdaterCategoriesLoaded', initCategoriasUpdater, { once: true });
    
    // Fallback: intentar inicializar inmediatamente si ya está disponible
    setTimeout(initCategoriasUpdater, 300);
    
    // Asegurar que CONFIG esté inicializado
    if (typeof CONFIG === 'undefined' || !CONFIG.apiUrl) {
        initializeConfig();
    }

    
    // Verificar que los elementos necesarios existen
    const tbody = document.getElementById('categorias-table-body');
    
    // 💾 RESTAURAR vista desde localStorage
    let savedView = null;
    try {
        savedView = localStorage.getItem('products_view_preference');
        if (savedView) {
            console.log('💾 Vista guardada encontrada:', savedView);
        }
    } catch (e) {
        console.warn('⚠️ No se pudo leer localStorage:', e);
    }
    
    // Detectar si es móvil y preparar vista grid ANTES de cargar
    const isMobile = window.innerWidth <= 768;
    if (isMobile) {
        console.log('📱 Dispositivo móvil detectado, preparando vista grid');
        
        // Actualizar variable global de vista
        window.products_currentView = 'grid';
        
        // 1. Ocultar tabla INMEDIATAMENTE (antes que nada)
        const tableContainer = document.querySelector('.data-table-wrapper');
        if (tableContainer) {
            tableContainer.style.display = 'none !important';
            tableContainer.style.visibility = 'hidden';
        }
        
        // 2. Crear y mostrar grid container ANTES de cargar datos
        let gridContainer = document.querySelector('.products-grid');
        if (!gridContainer) {
            createGridView();
            gridContainer = document.querySelector('.products-grid');
        }
        
        // 3. Configurar grid para que esté visible desde el inicio
        if (gridContainer) {
            gridContainer.style.display = 'grid';
            gridContainer.style.visibility = 'visible';
            gridContainer.innerHTML = `
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px 20px; color: #94a3b8;">
                    <div style="display: flex; flex-direction: column; align-items: center; gap: 12px;">
                        <div class="spinner" style="border: 3px solid #e2e8f0; border-top-color: #3b82f6; width: 40px; height: 40px;"></div>
                        <span style="font-size: 14px;">Cargando categorias...</span>
                    </div>
                </div>
            `;
        }
        
        // 4. Cambiar botones activos y BLOQUEAR en móvil
        const viewButtons = document.querySelectorAll('.view-btn');
        viewButtons.forEach(btn => {
            btn.classList.remove('active');
            if (btn.dataset.view === 'grid') {
                btn.classList.add('active');
            }
            
            // BLOQUEAR botones en móvil (solo grid permitido)
            if (btn.dataset.view === 'table') {
                btn.disabled = true;
                btn.style.opacity = '0.5';
                btn.style.cursor = 'not-allowed';
                btn.title = 'Vista tabla no disponible en móvil';
            }
        });
        
        console.log('🔒 Botones de vista bloqueados en móvil (solo grid)');
    } else {
        // En desktop, asegurar que los botones estén desbloqueados
        const viewButtons = document.querySelectorAll('.view-btn');
        viewButtons.forEach(btn => {
            btn.disabled = false;
            btn.style.opacity = '1';
            btn.style.cursor = 'pointer';
            btn.title = '';
        });
    }
    
    // Cargar categorías, marcas y categorias
    loadCategories();
    loadMarcas();
    
    // Inicializar modal de filtros móvil
    console.log('🔧 Inicializando modal de filtros móvil...');
    toggleMobileFilterButton();
    window.addEventListener('resize', toggleMobileFilterButton);
    
    // Inicializar control del sidebar móvil (shop.php style)
    initMobileFiltersSidebar();
    
    // En móvil, cargar categorias y luego forzar vista grid INSTANTÁNEAMENTE
    if (isMobile) {
        loadCategorias().then(() => {
            console.log('🎯 categorias cargados, ejecutando toggleView(grid) automáticamente');
            toggleView('grid', true); // skipAnimation = true para móvil
        });
    } else {
        // En desktop, restaurar vista guardada o usar tabla por defecto
        if (savedView && (savedView === 'grid' || savedView === 'table')) {
            console.log('🔄 Restaurando vista guardada:', savedView);
            loadCategorias().then(() => {
                // Usar skipAnimation para carga inicial (más rápido)
                toggleView(savedView, true);
            });
        } else {
            // Vista por defecto: tabla
            loadCategorias();
        }
    }
    
    // ========================================
    // INICIALIZAR LIBRERÍAS MODERNAS
    // ========================================
    
    // 1. Flatpickr para filtro de fecha - BOTÓN que abre calendario
    const filterFecha = document.getElementById('filter-fecha');
    const filterFechaValue = document.getElementById('filter-fecha-value');
    const filterFechaText = document.getElementById('filter-fecha-text');
    
    if (filterFecha && typeof flatpickr !== 'undefined') {
        console.log('📅 Inicializando Flatpickr en botón de fecha');
        
        // Crear input invisible para Flatpickr
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'text';
        hiddenInput.style.display = 'none';
        hiddenInput.id = 'flatpickr-hidden-input';
        filterFecha.parentNode.appendChild(hiddenInput);
        
        // Variable para controlar si el calendario está abierto
        let isCalendarOpen = false;
        
        // ⭐ DECLARAR calendarObserver ANTES de Flatpickr
        const calendarObserver = new MutationObserver(function(mutations) {
            // Re-marcar inmediatamente cuando haya cualquier cambio
            const calendar = document.querySelector('.flatpickr-calendar:not(.inline)');
            if (calendar && window.productsDatesArray && window.productsDatesArray.length > 0) {
                const days = calendar.querySelectorAll('.flatpickr-day:not(.flatpickr-disabled)');
                days.forEach(dayElem => {
                    if (dayElem.dateObj) {
                        const dateStr = dayElem.dateObj.toISOString().split('T')[0];
                        if (window.productsDatesArray.includes(dateStr)) {
                            if (!dayElem.classList.contains('has-products')) {
                                dayElem.classList.add('has-products');
                                dayElem.title = 'Hay categorias en esta fecha';
                            }
                        }
                    }
                });
            }
        });
        
        // ⭐ DECLARAR startObserving ANTES de Flatpickr
        const startObserving = () => {
            const calendar = document.querySelector('.flatpickr-calendar:not(.inline)');
            if (calendar) {
                calendarObserver.observe(calendar, {
                    childList: true,
                    subtree: true,
                    attributes: true,
                    attributeFilter: ['class', 'aria-label']
                });
                
                // FORZAR marcado inmediato después de iniciar observación
                if (typeof markMonthsWithProducts === 'function') {
                    markMonthsWithProducts();
                }
            }
        };
        
        // Inicializar Flatpickr en el input invisible
        window.productsDatePicker = flatpickr(hiddenInput, {
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
            // NO mostrar días de otros meses
            showOtherMonths: false,
            locale: {
                firstDayOfWeek: 1,
                weekdays: {
                    shorthand: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                    longhand: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado']
                },
                months: {
                    shorthand: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                    longhand: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']
                }
            },
            // NO filtrar HASTA que se complete el rango (2 fechas)
            onChange: function(selectedDates, dateStr, instance) {
                console.log('📅 Fechas seleccionadas:', selectedDates.length, dateStr);
                
                // Actualizar hidden input
                if (filterFechaValue) filterFechaValue.value = dateStr;
                
                // Actualizar texto del botón
                if (filterFechaText) {
                    if (dateStr && selectedDates.length === 2) {
                        const dates = dateStr.split(' to ');
                        filterFechaText.textContent = `${dates[0]} → ${dates[1]}`;
                    } else if (dateStr && selectedDates.length === 1) {
                        filterFechaText.textContent = `${dateStr} (selecciona fin)`;
                    } else {
                        filterFechaText.textContent = 'Seleccionar fechas';
                    }
                }
                
                // FILTRAR SOLO cuando se seleccionen 2 fechas (rango completo)
                if (selectedDates.length === 2) {
                    console.log('✅ Rango completo seleccionado, filtrando...');
                    filterCategories();
                }
            },
            onReady: function(selectedDates, dateStr, instance) {
                // FORZAR marcado múltiples veces para asegurar
                setTimeout(() => markMonthsWithProducts(), 10);
                setTimeout(() => markMonthsWithProducts(), 50);
                setTimeout(() => markMonthsWithProducts(), 100);
                setTimeout(() => startObserving(), 150);
            },
            onOpen: function() {
                console.log('📅 Calendario abierto - LIMPIANDO filtros automáticamente');
                isCalendarOpen = true;
                filterFecha.classList.add('calendar-open');
                
                // ⚡ REDIBUJAR SILENCIOSAMENTE para actualizar marcas (solo cuando se abre)
                window.productsDatePicker.redraw();
                
                // ⭐ LIMPIAR fechas automáticamente al abrir (como hacer click en "Limpiar")
                window.productsDatePicker.clear();
                
                // Limpiar valores
                if (filterFechaValue) filterFechaValue.value = '';
                if (filterFechaText) filterFechaText.textContent = 'Seleccionar fechas';
                
                // Re-cargar TODOS los categorias (sin filtro de fecha)
                filterCategories();
                
                // FORZAR marcado múltiples veces
                setTimeout(() => markMonthsWithProducts(), 10);
                setTimeout(() => markMonthsWithProducts(), 50);
                setTimeout(() => markMonthsWithProducts(), 100);
                setTimeout(() => startObserving(), 150);
            },
            onClose: function() {
                console.log('📅 Calendario cerrado');
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
                // FORZAR marcado al cambiar año
                setTimeout(() => markMonthsWithProducts(), 10);
                setTimeout(() => markMonthsWithProducts(), 50);
                setTimeout(() => markMonthsWithProducts(), 100);
            },
            onDayCreate: function(dObj, dStr, fp, dayElem) {
                // Marcar visualmente las fechas con categorias
                const dateStr = dayElem.dateObj.toISOString().split('T')[0];
                if (window.productsDatesArray && window.productsDatesArray.includes(dateStr)) {
                    dayElem.classList.add('has-products');
                    dayElem.title = 'Hay categorias en esta fecha';
                }
            }
        });
        
        // Función para marcar meses con categorias
        function markMonthsWithProducts() {
            if (!window.productsDatesArray || window.productsDatesArray.length === 0) return;
            
            const calendarEl = document.querySelector('.flatpickr-calendar:not(.inline)');
            if (!calendarEl) return;
            
            // Obtener meses únicos de las fechas de categorias
            const monthsWithProducts = new Set();
            window.productsDatesArray.forEach(dateStr => {
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
                    
                    // Agregar indicador si hay categorias este mes (círculo verde como los días)
                    if (monthsWithProducts.has(currentYearMonth)) {
                        const indicator = document.createElement('span');
                        indicator.className = 'month-has-products-indicator';
                        indicator.innerHTML = '<span class="green-dot"></span>';
                        indicator.title = 'Hay categorias en este mes';
                        currentMonthEl.appendChild(indicator);
                    }
                    
                    // Hacer el año editable (NO readonly, NO convertir a texto)
                    if (yearInput && yearInput.type === 'number') {
                        // Mantener como number pero quitar las flechas con CSS
                        yearInput.removeAttribute('readonly');
                        yearInput.style.pointerEvents = 'auto';
                        
                        // Permitir que Flatpickr maneje el cambio de año automáticamente
                        // al cambiar de mes (diciembre -> enero = siguiente año)
                    }
                    
                    // Marcar opciones del dropdown con círculo verde
                    const options = monthSelect.querySelectorAll('option');
                    options.forEach((option, index) => {
                        const monthNum = String(index + 1).padStart(2, '0');
                        const yearMonth = `${year}-${monthNum}`;
                        
                        // Limpiar texto previo
                        let originalText = option.textContent
                            .replace(' ●', '').replace('●', '')
                            .replace(' 🟢', '').replace('🟢', '')
                            .replace(' 🔵', '').replace('🔵', '')
                            .replace(' ⬤', '').replace('⬤', '')
                            .trim();
                        
                        // Resetear estilos
                        option.style.fontWeight = '500';
                        
                        // Si hay categorias, usar el caracter ⬤ (círculo grande) que se ve mejor
                        if (monthsWithProducts.has(yearMonth)) {
                            // Usar espacio + caracter especial de círculo
                            option.textContent = originalText;
                            option.value = option.value; // Mantener el value
                            // Agregar un prefijo visual
                            option.textContent = '● ' + originalText;
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
            
            // Re-marcar todos los días con categorias (FORZAR)
            const days = calendarEl.querySelectorAll('.flatpickr-day:not(.flatpickr-disabled)');
            days.forEach(dayElem => {
                if (dayElem.dateObj) {
                    const dateStr = dayElem.dateObj.toISOString().split('T')[0];
                    if (window.productsDatesArray.includes(dateStr)) {
                        if (!dayElem.classList.contains('has-products')) {
                            dayElem.classList.add('has-products');
                            dayElem.title = 'Hay categorias en esta fecha';
                        }
                    }
                }
            });
        }
        
        // Toggle calendario al hacer click en el botón
        filterFecha.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (isCalendarOpen) {
                window.productsDatePicker.close();
            } else {
                window.productsDatePicker.open();
            }
        });
        
        console.log('✅ Flatpickr inicializado en botón');
    }
    
    // 2. Flatpickr para filtro de fecha en modal móvil - BOTÓN que abre calendario
    const filterFechaModal = document.getElementById('modal-filter-fecha');
    const filterFechaModalValue = document.getElementById('modal-filter-fecha-value');
    const filterFechaModalText = document.getElementById('modal-filter-fecha-text');
    
    if (filterFechaModal && typeof flatpickr !== 'undefined') {
        console.log('📅 Inicializando Flatpickr en botón de fecha modal');
        
        // Crear input invisible para Flatpickr
        const hiddenInputModal = document.createElement('input');
        hiddenInputModal.type = 'text';
        hiddenInputModal.style.display = 'none';
        hiddenInputModal.id = 'flatpickr-hidden-input-modal';
        filterFechaModal.parentNode.appendChild(hiddenInputModal);
        
        // Variable para controlar si el calendario está abierto
        let isModalCalendarOpen = false;
        
        // ⭐ DECLARAR calendarObserverModal ANTES de Flatpickr
        const calendarObserverModal = new MutationObserver(function(mutations) {
            // Re-marcar inmediatamente cuando haya cualquier cambio
            const calendar = document.querySelector('.flatpickr-calendar:not(.inline)');
            if (calendar && window.productsDatesArray && window.productsDatesArray.length > 0) {
                const days = calendar.querySelectorAll('.flatpickr-day:not(.flatpickr-disabled)');
                days.forEach(dayElem => {
                    if (dayElem.dateObj) {
                        const dateStr = dayElem.dateObj.toISOString().split('T')[0];
                        if (window.productsDatesArray.includes(dateStr)) {
                            if (!dayElem.classList.contains('has-products')) {
                                dayElem.classList.add('has-products');
                                dayElem.title = 'Hay categorias en esta fecha';
                            }
                        }
                    }
                });
            }
        });
        
        // ⭐ DECLARAR startObservingModal ANTES de Flatpickr
        const startObservingModal = () => {
            const calendar = document.querySelector('.flatpickr-calendar:not(.inline)');
            if (calendar) {
                calendarObserverModal.observe(calendar, {
                    childList: true,
                    subtree: true,
                    attributes: true,
                    attributeFilter: ['class', 'aria-label']
                });
                
                // FORZAR marcado inmediato después de iniciar observación
                if (typeof markMonthsWithProducts === 'function') {
                    markMonthsWithProducts();
                }
            }
        };
        
        // Inicializar Flatpickr en el input invisible
        window.productsDatePickerModal = flatpickr(hiddenInputModal, {
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
            // NO mostrar días de otros meses
            showOtherMonths: false,
            locale: {
                firstDayOfWeek: 1,
                weekdays: {
                    shorthand: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                    longhand: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado']
                },
                months: {
                    shorthand: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                    longhand: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']
                }
            },
            // NO filtrar HASTA que se complete el rango (2 fechas)
            onChange: function(selectedDates, dateStr, instance) {
                console.log('📅 Fechas modal seleccionadas:', selectedDates.length, dateStr);
                
                // Actualizar hidden input
                if (filterFechaModalValue) filterFechaModalValue.value = dateStr;
                
                // Actualizar texto del botón modal SIN ICONOS
                if (filterFechaModalText) {
                    if (dateStr && selectedDates.length === 2) {
                        const dates = dateStr.split(' to ');
                        filterFechaModalText.textContent = `${dates[0]} → ${dates[1]}`;
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
                    filterFechaText.textContent = `${dates[0]} → ${dates[1]}`;
                } else if (filterFechaText && selectedDates.length === 1) {
                    filterFechaText.textContent = `${dateStr} (selecciona fin)`;
                } else if (filterFechaText) {
                    filterFechaText.textContent = 'Seleccionar fechas';
                }
                
                // FILTRAR SOLO cuando se seleccionen 2 fechas (rango completo)
                if (selectedDates.length === 2) {
                    console.log('✅ Rango completo seleccionado en modal, filtrando...');
                    filterCategories();
                }
            },
            onReady: function(selectedDates, dateStr, instance) {
                // FORZAR marcado múltiples veces
                setTimeout(() => markMonthsWithProducts(), 50);
                setTimeout(() => markMonthsWithProducts(), 100);
                setTimeout(() => markMonthsWithProducts(), 200);
                setTimeout(() => startObservingModal(), 250);
            },
            onOpen: function() {
                console.log('📅 Calendario modal abierto - LIMPIANDO filtros automáticamente');
                isModalCalendarOpen = true;
                filterFechaModal.classList.add('calendar-open');
                
                // ⚡ REDIBUJAR SILENCIOSAMENTE para actualizar marcas (solo cuando se abre)
                window.productsDatePickerModal.redraw();
                
                // ⭐ LIMPIAR fechas automáticamente al abrir (como hacer click en "Limpiar")
                window.productsDatePickerModal.clear();
                
                // Limpiar valores modal
                if (filterFechaModalValue) filterFechaModalValue.value = '';
                if (filterFechaModalText) filterFechaModalText.textContent = 'Seleccionar fechas';
                
                // Sincronizar limpieza con desktop
                if (filterFechaValue) filterFechaValue.value = '';
                if (filterFechaText) filterFechaText.textContent = 'Seleccionar fechas';
                
                // Re-cargar TODOS los categorias (sin filtro de fecha)
                filterCategories();
                
                // FORZAR marcado múltiples veces
                setTimeout(() => markMonthsWithProducts(), 50);
                setTimeout(() => markMonthsWithProducts(), 100);
                setTimeout(() => markMonthsWithProducts(), 200);
                setTimeout(() => startObservingModal(), 250);
            },
            onClose: function() {
                console.log('📅 Calendario modal cerrado');
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
                // FORZAR marcado al cambiar año
                setTimeout(() => markMonthsWithProducts(), 10);
                setTimeout(() => markMonthsWithProducts(), 50);
                setTimeout(() => markMonthsWithProducts(), 100);
            },
            onDayCreate: function(dObj, dStr, fp, dayElem) {
                // Marcar visualmente las fechas con categorias - SOLO CLASE
                const dateStr = dayElem.dateObj.toISOString().split('T')[0];
                if (window.productsDatesArray && window.productsDatesArray.includes(dateStr)) {
                    dayElem.classList.add('has-products');
                    dayElem.title = 'Hay categorias en esta fecha';
                }
            }
        });
        
        // Toggle calendario al hacer click en el botón
        filterFechaModal.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (isModalCalendarOpen) {
                window.productsDatePickerModal.close();
            } else {
                window.productsDatePickerModal.open();
            }
        });
        
        console.log('✅ Flatpickr modal inicializado en botón');
    }
    
    // 3. Agregar animaciones AOS a elementos
    const moduleHeader = document.querySelector('.admin-categories-module .module-header');
    if (moduleHeader && typeof AOS !== 'undefined') {
        moduleHeader.setAttribute('data-aos', 'fade-down');
        
        // Animar filtros
        const filterGroups = document.querySelectorAll('.filter-group');
        filterGroups.forEach((group, index) => {
            group.setAttribute('data-aos', 'fade-up');
            group.setAttribute('data-aos-delay', (index * 50).toString());
        });
        
        // Refrescar AOS después de agregar atributos
        if (AOS.refresh) {
            AOS.refresh();
        }
    }
    
    console.log('✅ Librerías modernas inicializadas en categorias');
    
    // ========================================
    // LISTENER PARA CAMBIOS DE TAMAÑO (Mobile ↔ Desktop)
    // ========================================
    
    let resizeTimeout;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            const isMobileNow = window.innerWidth <= 768;
            const viewButtons = document.querySelectorAll('.view-btn');
            
            if (isMobileNow) {
                // Si cambió a móvil, forzar grid y bloquear botones
                console.log('📱 Cambio a móvil detectado');
                window.products_currentView = 'grid';
                
                viewButtons.forEach(btn => {
                    if (btn.dataset.view === 'table') {
                        btn.disabled = true;
                        btn.style.opacity = '0.5';
                        btn.style.cursor = 'not-allowed';
                        btn.title = 'Vista tabla no disponible en móvil';
                    }
                });
                
                // Forzar vista grid
                toggleView('grid');
            } else {
                // Si cambió a desktop, desbloquear botones
                console.log('💻 Cambio a desktop detectado');
                
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
    // FIN LIBRERÍAS MODERNAS
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
    
    // 🔄 Inicializar eventos de ordenamiento de columnas
    setTimeout(() => {
        if (typeof initializeSortingEvents === 'function') {
            initializeSortingEvents();
            console.log('✅ Eventos de ordenamiento inicializados en módulo');
        }
    }, 200);
    
    // Función de debugging para verificar funciones disponibles
    window.debugProductsFunctions = function() {
        const functions = [
            'loadProducts', 'loadCategories', 'filterCategories', 'handleCategorySearchInput', 
            'toggleView', 'showActionMenu', 'editProduct', 'viewProduct', 'deleteProduct',
            'toggleProductStatus', 'updateStock', 'exportCategories'
        ];
        
        const parentFunctions = ['showEditProductModal', 'showViewProductModal', 'showCreateProductModal'];
        parentFunctions.forEach(func => {

        });
    };
}

// ✅ EXPONER LA FUNCIÓN DE INICIALIZACIÓN GLOBALMENTE
window.initializeProductsModule = initializeProductsModule;

// ✅ EJECUTAR INICIALIZACIÓN INMEDIATAMENTE (dentro del eval())
// Esto asegura que se ejecute en el momento correcto, cuando el DOM ya está listo
initializeProductsModule();

// NOTA: Al ejecutar dentro del eval(), la función se ejecuta en el momento exacto
// cuando todo el código está definido y el contenedor ya tiene el HTML insertado

// Asegurar que las funciones estén disponibles globalmente de inmediato
window.loadProducts = loadCategorias;
window.loadcategorias = loadCategorias;
window.loadCategorias = loadCategorias;
window.loadCategories = loadCategories;
window.filterCategories = filterCategories;
window.handleCategorySearchInput = handleCategorySearchInput;
window.toggleView = toggleView;
window.showActionMenu = showActionMenu;
window.clearCategorySearch = clearCategorySearch;
window.clearAllCategoryFilters = clearAllCategoryFilters;
window.exportCategories = exportCategories;
window.showCategoryReport = showCategoryReport;
window.editProduct = editCategoria;
window.editCategoria = editCategoria;
window.viewProduct = viewCategoria;
window.viewCategoria = viewCategoria;
window.deleteProduct = deleteCategoria;
window.deleteCategoria = deleteCategoria;
window.changeProductEstado = changeCategoriaEstado;
window.changeCategoriaEstado = changeCategoriaEstado;
window.toggleProductStatus = toggleProductStatus;
window.changeProductEstado = changeProductEstado;
window.updateStock = updateStock;
window.closeStockBubble = closeStockBubble; // Exponer función para cerrar burbuja
window.showDeleteConfirmation = showDeleteConfirmation;
window.closeDeleteConfirmation = closeDeleteConfirmation;
window.setupDeleteModalBackdropClose = setupDeleteModalBackdropClose;
window.confirmDelete = confirmDelete;
window.handleBulkProductAction = handleBulkProductAction;
window.createGridView = createGridView;
window.displayProductsGrid = displayProductsGrid;
window.closeFloatingActions = closeFloatingActions;

// ============ FUNCIONES DE MODAL PARA CATEGORÍAS ============
// Estas funciones llaman a las funciones del admin.php principal

window.showCreateCategoryModal = function() {
    console.log('🆕 showCreateCategoryModal llamada desde admin_categorias.php');
    
    // Verificar si existe la función en parent (admin.php)
    if (typeof parent.showModalOverlayCreateCategoria === 'function') {
        console.log('✅ Llamando a parent.showModalOverlayCreateCategoria');
        parent.showModalOverlayCreateCategoria();
    } else if (typeof window.showModalOverlayCreateCategoria === 'function') {
        console.log('✅ Llamando a window.showModalOverlayCreateCategoria');
        window.showModalOverlayCreateCategoria();
    } else if (typeof top.showModalOverlayCreateCategoria === 'function') {
        console.log('✅ Llamando a top.showModalOverlayCreateCategoria');
        top.showModalOverlayCreateCategoria();
    } else {
        console.error('❌ No se encontró showModalOverlayCreateCategoria en ningún scope');
        alert('Error: No se pudo abrir el modal de crear categoría');
    }
};

window.showEditCategoriaModal = function(id) {
    console.log('✏️ showEditCategoriaModal llamada con ID:', id);
    
    // Verificar si existe la función en parent (admin.php)
    if (typeof parent.showModalOverlayEditCategoria === 'function') {
        console.log('✅ Llamando a parent.showModalOverlayEditCategoria');
        parent.showModalOverlayEditCategoria(id);
    } else if (typeof window.showModalOverlayEditCategoria === 'function') {
        console.log('✅ Llamando a window.showModalOverlayEditCategoria');
        window.showModalOverlayEditCategoria(id);
    } else if (typeof top.showModalOverlayEditCategoria === 'function') {
        console.log('✅ Llamando a top.showModalOverlayEditCategoria');
        top.showModalOverlayEditCategoria(id);
    } else {
        console.error('❌ No se encontró showModalOverlayEditCategoria en ningún scope');
        alert('Error: No se pudo abrir el modal de editar categoría');
    }
};

window.showViewCategoriaModal = function(id) {
    console.log('👁️ showViewCategoriaModal llamada con ID:', id);
    
    // Verificar si existe la función en parent (admin.php)
    if (typeof parent.showModalOverlayViewCategoria === 'function') {
        console.log('✅ Llamando a parent.showModalOverlayViewCategoria');
        parent.showModalOverlayViewCategoria(id);
    } else if (typeof window.showModalOverlayViewCategoria === 'function') {
        console.log('✅ Llamando a window.showModalOverlayViewCategoria');
        window.showModalOverlayViewCategoria(id);
    } else if (typeof top.showModalOverlayViewCategoria === 'function') {
        console.log('✅ Llamando a top.showModalOverlayViewCategoria');
        top.showModalOverlayViewCategoria(id);
    } else {
        console.error('❌ No se encontró showModalOverlayViewCategoria en ningún scope');
        alert('Error: No se pudo abrir el modal de ver categoría');
    }
};

// ============ FIN DE FUNCIONES DE MODAL ============

window.closeFloatingActionsAnimated = closeFloatingActionsAnimated;
window.closeFloatingActionsAnimatedFast = closeFloatingActionsAnimatedFast;
window.cancelSoftClose = cancelSoftClose;
window.openNewMenu = openNewMenu;
window.cleanupOrphanedContainers = cleanupOrphanedContainers;
window.createAnimatedFloatingContainer = createAnimatedFloatingContainer;
window.updateAnimatedButtonPositions = updateAnimatedButtonPositions;
window.startContinuousTracking = startContinuousTracking;
window.stopContinuousTracking = stopContinuousTracking;
window.forceCloseFloatingActions = forceCloseFloatingActions;
window.showImageFullSize = showImageFullSize;

// ============ FUNCIONES DE ESTADO PARA PRESERVACIÓN ============

// Función para obtener la vista actual
window.getCurrentView = function() {
    const gridViewBtn = document.querySelector('[onclick="toggleView(\'grid\')"]');
    const tableViewBtn = document.querySelector('[onclick="toggleView(\'table\')"]');
    
    if (gridViewBtn && gridViewBtn.classList.contains('active')) {
        return 'grid';
    } else if (tableViewBtn && tableViewBtn.classList.contains('active')) {
        return 'table';
    }
    
    // Verificar por el contenido visible
    const gridContainer = document.querySelector('.products-grid');
    const tableContainer = document.querySelector('.products-table');
    
    if (gridContainer && gridContainer.style.display !== 'none') {
        return 'grid';
    } else if (tableContainer && tableContainer.style.display !== 'none') {
        return 'table';
    }
    
    return 'table'; // Default
};

// Función para obtener el término de búsqueda actual
window.getSearchTerm = function() {
    const searchInput = document.getElementById('search-categorias');
    return searchInput ? searchInput.value.trim() : '';
};

// Función para obtener los filtros actuales
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

// Función para preservar scroll position
window.preserveScrollPosition = function() {
    const mainContent = document.querySelector('.tab-content') || document.body;
    return {
        top: mainContent.scrollTop,
        left: mainContent.scrollLeft
    };
};

// Función para restaurar scroll position
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

// Hacer currentPage accesible globalmente para preservación de estado
window.currentPage = currentPage;

// ============ FUNCIONES DE DESTACADO Y ANIMACIONES ============

// Función de destacado eliminada para evitar problemas visuales


// Sistema de loading overlay y actualización forzada eliminados

// ============ FUNCIONES DE ELIMINAR Y TOGGLE STATUS ============

// Función para mostrar burbuja de confirmación de eliminación
function showDeleteConfirmation(productId, productName) {
    console.log('🗑️ showDeleteConfirmation llamada para categoría:', productId, productName);
    
    // Verificar si ya existe un modal
    const existingOverlay = document.querySelector('.delete-confirmation-overlay');
    if (existingOverlay) {
        console.log('❌ Modal ya existe, eliminándolo primero');
        existingOverlay.remove();
    }
    
    // Crear overlay con estilos profesionales
    const overlay = document.createElement('div');
    overlay.className = 'delete-confirmation-overlay';
    console.log('✅ Overlay creado');
    
    overlay.innerHTML = `
        <div class="delete-confirmation-modal">
            <div class="delete-modal-header">
                <h3><i class="fas fa-exclamation-triangle"></i> Confirmar Eliminación</h3>
            </div>
            <div class="delete-modal-body">
                <div class="warning-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <p>Para eliminar la categoría <strong>"${productName}"</strong>, escribe la palabra <strong>"eliminar"</strong> en el campo de abajo:</p>
                
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
                    <i class="fas fa-trash"></i> Eliminar categoría
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
    
    console.log('📝 Estilos agregados');
    
    // Agregar al DOM
    document.body.appendChild(overlay);
    console.log('🎯 Modal agregado al DOM');
    
    // Forzar reflow para que las animaciones funcionen
    overlay.offsetHeight;
    
    // Agregar clase 'show' para activar animaciones CSS
    requestAnimationFrame(() => {
        overlay.classList.add('show');
        
        // También agregar .show al modal interno
        const deleteModal = overlay.querySelector('.delete-confirmation-modal');
        if (deleteModal) {
            deleteModal.classList.add('show');
        }
        
        console.log('✨ Clase "show" agregada - animación iniciada');
    });
    
    // Focus en el input después de la animación
    setTimeout(() => {
        const input = document.getElementById('deleteConfirmInput');
        if (input) {
            input.focus();
            console.log('⌨️ Focus en input');
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
            console.log('👆 Click en overlay detectado - cerrando modal');
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

// Función para cerrar la confirmación con animación
function closeDeleteConfirmation() {
    console.log('🔴 Cerrando modal de eliminación con animación');
    const overlay = document.querySelector('.delete-confirmation-overlay');
    if (overlay) {
        // Agregar clases de salida para animación
        overlay.classList.remove('show');
        overlay.classList.add('hide');
        
        const deleteModal = overlay.querySelector('.delete-confirmation-modal');
        if (deleteModal) {
            deleteModal.classList.add('hide');
        }
        
        // Remover del DOM después de que termine la animación
        setTimeout(() => {
            overlay.remove();
            console.log('✅ Modal eliminado del DOM');
        }, 250); // Duración de la animación fadeOut actualizada
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

// Función para confirmar eliminación
function confirmDelete(productId, productName) {
    const input = document.getElementById('deleteConfirmInput');
    const errorDiv = document.getElementById('deleteError');
    
    if (input.value.toLowerCase().trim() !== 'eliminar') {
        errorDiv.style.display = 'block';
        input.style.borderColor = '#dc2626';
        input.focus();
        return;
    }
    
    // Proceder con eliminación
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
        
        console.log('📦 Respuesta del servidor al eliminar:', data);
        
        if (data.success) {
            // Mostrar notificación de éxito
            showNotification(`Categoría "${productName}" eliminada exitosamente`, 'success');
            
            // Usar actualización suave si está disponible
            if (window.categoriasTableUpdater) {
                console.log('🎯 Usando actualización suave para eliminar categoría:', productId);
                window.categoriasTableUpdater.removeProduct(productId);
            } else {
                console.log('⚠️ SmoothTableUpdater no disponible - usando recarga tradicional');
                // Actualizar lista inmediatamente sin reload
                loadCategorias(true);
            }
        } else {
            showNotification('Error al eliminar categoría: ' + (data.error || 'Error desconocido'), 'error');
        }
    })
    .catch(error => {
        console.error('❌ Error al eliminar categoría:', error);
        closeDeleteConfirmation();
        showNotification('Error de conexión al eliminar categoría', 'error');
    });
}

// Función para alternar estado de la categoría (activo/inactivo)
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
            // Usar actualización suave si está disponible
            if (window.categoriasTableUpdater && (data.category || data.categoria)) {
                console.log('🎯 Usando actualización suave para cambiar estado de la categoría:', productId);
                window.categoriasTableUpdater.updateSingleProduct(productId, data.category || data.categoria);
            } else {
                console.log('⚠️ SmoothTableUpdater no disponible o categoría no retornada - usando recarga tradicional');
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
            // showNotification('Error de conexión al cambiar estado', 'error');
        } else {
            // alert('Error de conexión al cambiar estado');
        }
    });
}

// Función wrapper para eliminar categoria
function deleteCategoria(categoriaId, categoriaName) {
    console.log('🚀 deleteCategoria wrapper llamada:', categoriaId, categoriaName);
    showDeleteConfirmation(categoriaId, categoriaName || 'categoría');
}

// Alias para compatibilidad con código existente
window.deleteProduct = deleteCategoria;

// ============ FUNCIÓN PARA MOSTRAR IMAGEN EN TAMAÑO REAL ============

function showImageFullSize(imageUrl, productName) {
    console.log('🖼️ Mostrando imagen de categoría en tamaño real:', imageUrl);
    
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
    img.alt = productName || 'categoría';
    img.style.cssText = `
        max-width: 95vw;
        max-height: 95vh;
        object-fit: contain;
        cursor: zoom-out;
        opacity: 0;
        transition: opacity 0.3s ease;
    `;
    
    // Función para cerrar con animación
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
        
        // Esperar un frame más para que la imagen cargue
        setTimeout(() => {
            img.style.opacity = '1';
        }, 50);
    });
    
    // Manejar error de carga de imagen
    img.onerror = () => {
        img.src = AppConfig ? AppConfig.getImageUrl('default-product.jpg') : '/fashion-master/public/assets/img/default-product.jpg';
    };
}

// Hacer la función global
window.showImageFullSize = showImageFullSize;

// ============ FIN FUNCIONES DE ELIMINAR Y TOGGLE STATUS ============

// Sistema de limpieza automática para evitar menús huérfanos
setInterval(() => {
    const orphanedContainers = document.querySelectorAll('.animated-floating-container');
    if (orphanedContainers.length > 1) {
        // Si hay más de un contenedor, algo está mal, limpiar todos
        orphanedContainers.forEach(container => {
            try {
                container.remove();
            } catch (e) {
                console.warn('Error limpiando contenedor huérfano:', e);
            }
        });
        // Resetear variables globales
        categorias_activeFloatingContainer = null;
        categorias_centerButton = null;
        categorias_floatingButtons = [];
        categorias_activeProductId = null;
        categorias_isAnimating = false;
    }
}, 2000); // Verificar cada 2 segundos

// Limpiar al cambiar de página o recargar
window.addEventListener('beforeunload', () => {
    forceCloseFloatingActions();
});

// ===== FUNCIONALIDAD DE SCROLL MEJORADO PARA LA TABLA =====
function initializeTableScroll() {
    const scrollableTable = document.querySelector('.scrollable-table');
    if (!scrollableTable) return;
    
    let scrollTimeout;
    
    // Detectar cuando se está haciendo scroll
    scrollableTable.addEventListener('scroll', function() {
        // Agregar clase durante el scroll
        this.classList.add('scrolling');
        
        // Remover clase después de que termine el scroll
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
// ❌ DESACTIVADO: No usar DOMContentLoaded porque se ejecuta en eval() cada vez que se carga el módulo
// document.addEventListener('DOMContentLoaded', initializeTableScroll);
// En su lugar, initializeProductsModule() ya llama a esto directamente

// También inicializar cuando se actualiza la tabla
const originalDisplayProducts = displayProducts;
if (typeof displayProducts === 'function') {
    displayProducts = function(...args) {
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
        // No aplicar drag si se está clickeando en un botón, input o link
        if (e.target.closest('button, a, input, select, textarea, .btn-menu, .product-card-btn')) {
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
        
        // Prevenir selección de texto completamente
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
        
        // Restaurar selección de texto
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
    
    // Prevenir click accidental después de drag
    scrollableTable.addEventListener('click', function(e) {
        if (Math.abs(velocityX) > 0.5 || Math.abs(velocityY) > 0.5) {
            e.stopPropagation();
            e.preventDefault();
        }
    }, true);
}

// Inicializar drag-scroll cuando carga el DOM
// ❌ DESACTIVADO: No usar DOMContentLoaded/load porque se acumulan event listeners
// document.addEventListener('DOMContentLoaded', function() {
//     initializeDragScroll();
// });

// window.addEventListener('load', function() {
//     initializeDragScroll();
// });
// En su lugar, initializeProductsModule() llama a initializeDragScroll() directamente

// ===== FUNCIÓN DE DESTRUCCIÓN DEL MÓDULO DE categorias =====
window.destroyCategoriasModule = function() {
    console.log('🗑️ Destruyendo módulo de categorias...');
    
    try {
        // 🔥 0. DESTRUIR UPDATER DE CATEGORÍAS PRIMERO
        if (window.categoriasTableUpdater) {
            console.log('🗑️ Destruyendo CategoriasTableUpdater...');
            if (typeof window.categoriasTableUpdater.destroy === 'function') {
                window.categoriasTableUpdater.destroy();
            }
            window.categoriasTableUpdater = null;
        }
        
        // 1. Limpiar variable de estado de carga
        if (typeof isLoading !== 'undefined') {
            isLoading = false;
        }
        
        // 2. Limpiar arrays de datos
        if (typeof categorias !== 'undefined') {
            categorias = [];
        }
        
        // 3. Resetear paginación
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
        
        // 5. Limpiar modales de categorias
        const productModals = document.querySelectorAll('.product-view-modal, .product-modal, [id*="product-modal"]');
        productModals.forEach(modal => {
            modal.remove();
        });
        
        // 6. Limpiar burbujas flotantes de stock Y contenedores flotantes de categorias SOLAMENTE
        const stockBubbles = document.querySelectorAll('.stock-update-bubble');
        stockBubbles.forEach(bubble => {
            bubble.remove();
        });
        
        // Limpiar SOLO los contenedores flotantes que pertenecen a categorias
        if (categorias_activeFloatingContainer && document.contains(categorias_activeFloatingContainer)) {
            categorias_activeFloatingContainer.remove();
        }
        
        // Resetear variables flotantes de categorias
        categorias_activeFloatingContainer = null;
        categorias_centerButton = null;
        categorias_floatingButtons = [];
        categorias_activeProductId = null;
        categorias_isAnimating = false;
        
        // 7. Limpiar confirmaciones de eliminación
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
        console.log('🔄 Reseteando vista a tabla (estado inicial)...');
        
        // Remover vista grid si existe
        const gridContainer = document.querySelector('.products-grid');
        if (gridContainer) {
            gridContainer.remove();
        }
        
        // Asegurar que la tabla esté visible
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
            localStorage.removeItem('categorias_view_mode');
        } catch (e) {}
        
        console.log('✅ Vista reseteada a tabla');
        
        // 10. Remover clases de body que puedan interferir
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
        
        console.log('✅ Módulo de categorias destruido correctamente');
        
    } catch (error) {
        console.error('❌ Error al destruir módulo de categorias:', error);
    }
};

</script>

<style>
/* ===== FORZAR COLOR BLANCO EN BOTONES DEL HEADER - MÁXIMA PRIORIDAD ===== */
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

/* ========================================
   BOTÓN FLOTANTE DE FILTROS MÓVIL (shop.php style)
   ======================================== */
.btn-mobile-filters {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
    border: none;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
    cursor: pointer;
    z-index: 999;
    display: none;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    transition: all 0.3s ease, opacity 0.3s ease, transform 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    opacity: 1;
    transform: scale(1) rotate(0deg);
}

.btn-mobile-filters:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 16px rgba(59, 130, 246, 0.5);
}

.btn-mobile-filters:active {
    transform: scale(0.95);
}

/* Animación al ocultar botón */
.btn-mobile-filters.hidden {
    opacity: 0 !important;
    transform: scale(0) rotate(180deg) !important;
    pointer-events: none !important;
}

.btn-mobile-filters .filter-count {
    position: absolute;
    top: -5px;
    right: -5px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #dc3545;
    color: white;
    font-size: 12px;
    font-weight: 700;
    display: none;
    align-items: center;
    justify-content: center;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
}

/* ========================================
   SIDEBAR RESPONSIVE (shop.php style)
   ======================================== */
@media (max-width: 768px) {
    .btn-mobile-filters {
        display: flex !important;
    }
    
    .modern-sidebar {
        display: none !important;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 320px !important;
        max-width: 85vw !important;
        height: 100vh !important;
        max-height: 100vh !important;
        z-index: 99999 !important;
        overflow-y: auto !important;
        transform: translateX(-100%) !important;
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
        box-shadow: 4px 0 20px rgba(0, 0, 0, 0.3) !important;
        background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%) !important;
        padding: 1rem !important;
        will-change: transform !important;
    }
    
    .modern-sidebar.show-mobile {
        display: block !important;
        transform: translateX(0) !important;
    }
    
    /* Overlay oscuro cuando sidebar está abierto */
    .modern-sidebar.show-mobile::before {
        content: '' !important;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        background: rgba(0, 0, 0, 0.6) !important;
        z-index: -1 !important;
        animation: fadeIn 0.3s ease !important;
    }
    
    /* Botón de cerrar dentro del sidebar */
    .sidebar-close-btn {
        position: absolute !important;
        top: 1rem !important;
        right: 1rem !important;
        width: 40px !important;
        height: 40px !important;
        border-radius: 50% !important;
        background: rgba(255, 255, 255, 0.1) !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
        color: #ffffff !important;
        font-size: 18px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        cursor: pointer !important;
        transition: all 0.2s ease !important;
        z-index: 10 !important;
    }
    
    .sidebar-close-btn:hover {
        background: rgba(255, 255, 255, 0.2) !important;
        transform: rotate(90deg) !important;
    }
    
    .sidebar-close-btn:active {
        transform: rotate(90deg) scale(0.9) !important;
    }
    
    /* Animación del overlay */
    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }
    
    /* Mejorar el diseño de los filtros dentro del sidebar */
    .modern-sidebar .search-container {
        margin-bottom: 1rem !important;
    }
    
    .modern-sidebar .filters-grid {
        margin-top: 3rem !important; /* Espacio para el botón de cerrar */
    }
    
    .modern-sidebar .filter-group label {
        color: #cbd5e1 !important;
        font-weight: 600 !important;
    }
    
    .modern-sidebar .filter-select {
        background: rgba(255, 255, 255, 0.05) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        color: #ffffff !important;
    }
    
    .modern-sidebar .filter-select:focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
    }
    
    .module-actions .btn-modern,
    .module-actions .btn-modern *,
    .module-actions button,
    .module-actions button * {
        color: #ffffff !important;
    }
    
    /* Ocultar botones de vista (tabla/grid) y acciones en lote en móvil */
    .table-actions {
        display: none !important;
    }
}

/* ===== ESTILOS PARA BOTÓN DE FECHA FLATPICKR - MISMO ESTILO QUE SELECT ===== */
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

/* ASEGURAR texto blanco en móvil */
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

/* Indicador de mes con categorias - mismo estilo que los días */
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

/* Mejorar posición en móvil - SIN SCROLL HORIZONTAL */
@media (max-width: 768px) {
    .flatpickr-calendar {
        max-width: calc(100vw - 20px) !important; /* 10px de margen a cada lado */
        width: auto !important;
        left: 50% !important;
        transform: translateX(-50%) !important;
        margin-top: 10px;
    }
    
    /* Ajustar header en móvil */
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
        padding: 0 46px 0 42px !important; /* Menos padding en móvil */
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

/* Botón flecha IZQUIERDA - Posición absoluta */
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

/* Botón flecha DERECHA - Posición absoluta con más espacio */
.flatpickr-months .flatpickr-next-month {
    position: absolute !important;
    right: 16px !important; /* Más espacio a la derecha */
    top: 50% !important;
    transform: translateY(-50%) !important;
    padding: 8px !important;
    cursor: pointer;
    z-index: 3;
    width: 36px !important;
    height: 36px !important;
}

/* Contenedor central con mes y año - CENTRADO con más padding derecho */
.flatpickr-current-month {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 8px !important; /* Separación entre mes y año reducida */
    width: 100% !important;
    color: white;
    font-weight: 600;
    height: 100%;
    position: relative;
    z-index: 2;
    padding: 0 56px 0 52px !important; /* Más espacio derecho (56px), menos izquierdo (52px) */
}

/* Dropdown de MES - ancho fijo con más espacio a la derecha */
.flatpickr-current-month .flatpickr-monthDropdown-months {
    flex-shrink: 0;
    margin: 0 8px 0 0 !important; /* 8px de margen derecho */
    min-width: 100px !important; /* Ancho reducido */
    width: 100px !important;
    text-align: center;
}

/* Input de AÑO - ancho fijo */
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

/* OCULTAR días de otros meses */
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

/* Días seleccionados (inicio y fin del rango) */
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

/* Hover sobre días en rango */
.flatpickr-day.inRange:hover {
    background: rgba(59, 130, 246, 0.35) !important;
}

/* Días con categorias marcados - Indicador SIEMPRE visible */
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

/* Si el día con categorias está seleccionado, cambiar color del punto a blanco */
.flatpickr-day.has-products.selected::after,
.flatpickr-day.has-products.startRange::after,
.flatpickr-day.has-products.endRange::after {
    background: #ffffff !important;
}

/* Si está en el rango, mantener verde pero más visible */
.flatpickr-day.has-products.inRange::after {
    background: #10b981 !important;
    box-shadow: 0 0 4px rgba(16, 185, 129, 0.6) !important;
}

/* Días deshabilitados */
.flatpickr-day.flatpickr-disabled {
    color: #475569;
    opacity: 0.5;
}

/* Día de hoy */
.flatpickr-day.today {
    border-color: #3b82f6;
    font-weight: 600;
}

.flatpickr-day.today:not(.selected) {
    color: #3b82f6;
}

/* Botones de navegación - Mejorados con tamaño fijo */
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

/* Dropdown de mes y año - MEJORADO con anchos fijos */
.flatpickr-monthDropdown-months,
.numInputWrapper {
    background: rgba(255, 255, 255, 0.1) !important;
    color: white !important;
    border: 1px solid rgba(255, 255, 255, 0.2) !important;
    border-radius: 8px !important;
    padding: 7px 10px !important; /* Padding reducido */
    font-weight: 600 !important;
    font-size: 13px !important; /* Tamaño de fuente reducido */
    transition: all 0.2s ease !important;
    cursor: pointer !important;
    text-align: center !important;
}

/* Input de año con ancho fijo */
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

/* Opciones con categorias - TODO EN VERDE (más simple y efectivo) */
.flatpickr-monthDropdown-months option[data-has-products="true"] {
    background: #1e293b !important;
    color: #10b981 !important; /* Verde */
    font-weight: 600 !important;
}

/* Opción seleccionada - fondo azul con texto blanco visible */
.flatpickr-monthDropdown-months option:checked {
    background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%) !important;
    color: white !important;
    font-weight: 700 !important;
    -webkit-text-fill-color: white !important;
}

/* Opción al hacer hover */
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

/* Opciones con categorias mantienen texto blanco, el emoji 🟢 es naturalmente verde */
.flatpickr-monthDropdown-months option[data-has-products="true"] {
    color: white !important;
    background: #1e293b !important;
}

/* Input de año - Quitar flechas y hacerlo tipo texto */
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

/* Animación de cierre */
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
    user-select: none; /* Evitar selección de texto */
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

/* Animación suave del scroll */
.scrollable-table {
    scroll-behavior: smooth;
}

/* ===== FORZAR PRIMER PLANO PARA ELEMENTOS FLOTANTES - MÁXIMA PRIORIDAD ===== */
.animated-floating-container {
    z-index: 9999999 !important;
    position: fixed !important;
}

.animated-floating-button {
    z-index: 10000001 !important;
    position: fixed !important;
}

.animated-center-button {
    z-index: 10000000 !important;
    position: fixed !important;
}

.stock-update-bubble {
    z-index: 1000001 !important;
    position: relative !important;
}

.delete-confirmation-overlay {
    z-index: 10000002 !important;
    position: fixed !important;
}

/* NO aplicar z-index genérico al contenedor */
.animated-floating-container .animated-center-button {
    z-index: 10000000 !important;
}

.animated-floating-container .animated-floating-button {
    z-index: 10000001 !important;
}

/* Asegurar que los tooltips también estén en primer plano */
.floating-tooltip {
    z-index: 10000005 !important;
}

/* Mantener modales en nivel inferior */
.modal-content,
.modal-overlay,
#product-modal-overlay {
    z-index: 99999 !important;
}

/* ===== ESTILOS PARA ORDENAMIENTO DE COLUMNAS ===== */
th.sortable {
    cursor: pointer;
    user-select: none;
    position: relative;
    transition: all 0.2s ease;
}

th.sortable:hover {
    background: rgba(36, 99, 235, 0.1);
}

th.sortable:active {
    background: rgba(36, 99, 235, 0.2);
}

th.sortable.sorted {
    background: rgba(36, 99, 235, 0.15);
    color: #2463eb;
    font-weight: 600;
}

/* Indicador de dirección de ordenamiento (opcional) */
th.sortable.sorted[data-sort-direction="asc"]::after {
    content: " ↑";
    font-size: 0.85rem;
    opacity: 0.7;
}

th.sortable.sorted[data-sort-direction="desc"]::after {
    content: " ↓";
    font-size: 0.85rem;
    opacity: 0.7;
}

/* Animación para filas ordenadas */
tbody tr {
    transition: opacity 0.2s ease, transform 0.2s ease;
}

tbody tr.sorting {
    opacity: 0.5;
}

/* ===== BADGES DE GÉNERO (SOLO COLOR DE TEXTO) ===== */
.genero-badge {
    font-weight: 600;
}

/* Margen inferior en vista grid */
.product-card-genero {
    margin-bottom: 10px;
}

.genero-masculino {
    color: #3b82f6;
}

.genero-femenino {
    color: #ec4899;
}

.genero-unisex {
    color: #8b5cf6;
}

.genero-kids {
    color: #f59e0b;
}

.genero-default {
    color: #6b7280;
}

/* ===== MENSAJE DE NO HAY RESULTADOS ===== */
.loading-content.no-data {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
    color: #94a3b8;
}

.loading-content.no-data i {
    font-size: 64px;
    color: #475569;
    margin-bottom: 20px;
    opacity: 0.5;
}

.loading-content.no-data span {
    font-size: 18px;
    font-weight: 500;
    color: #cbd5e0;
    margin-bottom: 8px;
}

.loading-content.no-data small {
    font-size: 14px;
    color: #64748b;
    text-align: center;
}
</style>



