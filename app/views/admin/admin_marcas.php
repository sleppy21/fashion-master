<?php
/**
 * VISTA DE Gestión de Marcas - DESDE CERO
 * Módulo limpio y simple basado en productos
 */
?>

<div class="admin-module admin-marcas-module">
    <!-- Header del módulo -->
    <div class="module-header" data-aos="fade-down" data-aos-duration="600">
        <div class="module-title">
            <div class="module-icon">
                <i class="fas fa-building"></i>
            </div>
            <div class="module-info">
                <h2 class="module-name">Gestión de Marcas</h2>
                <p class="module-description">Administra las marcas de productos</p>
            </div>
        </div>
        <div class="module-actions" data-aos="fade-left" data-aos-delay="200">
            <button class="btn-modern btn-primary" onclick="showCreateMarcaModal()" style="color: white !important;">
                <i class="fas fa-plus" style="color: white !important;"></i>
                <span style="color: white !important;">Nuevo <span class="btn-text-mobile-hide">marca</span></span>
            </button>
            <button class="btn-modern btn-secondary" onclick="exportMarcasExcel()" style="color: white !important;">
                <i class="fas fa-download" style="color: white !important;"></i>
                <span style="color: white !important;">Exportar <span class="btn-text-mobile-hide">Excel</span></span>
            </button>
            <button class="btn-modern btn-info" onclick="showMarcasReport()" style="color: white !important;">
                <i class="fas fa-chart-bar" style="color: white !important;"></i>
                <span style="color: white !important;">Reporte <span class="btn-text-mobile-hide">Stock</span></span>
            </button>
        </div>
    </div>

    <!-- Filtros y búsqueda -->
    <div class="module-filters" data-aos="fade-up" data-aos-duration="600" data-aos-delay="400">
        <div class="search-container">
            <div class="search-input-group">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="search-marcas" class="search-input" 
                       placeholder="Buscar marcas..." oninput="handleSearchMarcas()">
                <button class="search-clear" onclick="clearSearchMarcas()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        
        <div class="filters-grid">
            <div class="filter-group">
                <label class="filter-label">Estado</label>
                <select id="filter-estado-marca" class="filter-select" onchange="filterMarcas()">
                    <option value="">Todos los estados</option>
                    <option value="activo">Activo</option>
                    <option value="inactivo">Inactivo</option>
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label">Fecha</label>
                <select id="filter-fecha-marca" class="filter-select" onchange="loadMarcasData()">
                    <option value="">Todas las fechas</option>
                    <!-- Se cargan dinámicamente -->
                </select>
            </div>
            <div class="filter-group">
                <button class="btn-modern btn-outline" onclick="clearAllFiltersMarcas()">
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
                        Mostrando <span id="showing-end-marcas">0</span> 
                        de <span id="total-marcas">0</span> marcas
                    </span>
                </div>
                <div class="table-actions">
                    <div class="view-options">
                        <button class="view-btn active" data-view="table" onclick="toggleViewMarcas('table')">
                            <i class="fas fa-table"></i>
                        </button>
                        <button class="view-btn" data-view="grid" onclick="toggleViewMarcas('grid')">
                            <i class="fas fa-th"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="data-table-wrapper scrollable-table" id="marcas-table-wrapper">
                <table class="data-table marcas-table">
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
                                <span>Código</span>
                            </th>
                            <th class="sortable" data-sort="descripcion">
                                <span>Descripción</span>
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
                    <tbody id="marcas-table-body" class="table-body">
                        <tr class="loading-row">
                            <td colspan="8" class="loading-cell">
                                <div class="loading-content">
                                    <div class="spinner"></div>
                                    <span>Cargando marcas...</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Vista Grid (oculta por defecto, se muestra en móvil) -->
            <div class="marcas-grid" id="marcas-grid">
                <!-- Las cards se generan dinámicamente -->
            </div>

            <!-- Paginación -->
            <div class="pagination-container">
                <div class="pagination-info">
                    <span class="pagination-text">
                        Página <span id="current-page-marcas">1</span> de <span id="total-pages-marcas">1</span>
                    </span>
                </div>
                <div class="pagination-controls">
                    <button class="pagination-btn" id="first-page-marcas" onclick="goToFirstPageMarcas()">
                        <i class="fas fa-angle-double-left"></i>
                    </button>
                    <button class="pagination-btn" id="prev-page-marcas" onclick="previousPageMarcas()">
                        <i class="fas fa-angle-left"></i>
                    </button>
                    <div class="pagination-numbers" id="pagination-numbers-marcas">
                        <!-- Números de página dinámicos -->
                    </div>
                    <button class="pagination-btn" id="next-page-marcas" onclick="nextPageMarcas()">
                        <i class="fas fa-angle-right"></i>
                    </button>
                    <button class="pagination-btn" id="last-page-marcas" onclick="goToLastPageMarcas()">
                        <i class="fas fa-angle-double-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Botón flotante de filtros (solo móvil) -->
    <button class="mobile-filter-btn" id="mobile-filters-btn" onclick="window.toggleFiltersModalMarcas()" style="display: none;">
        <i class="fas fa-filter"></i>
    </button>

    <!-- Modal de filtros (solo móvil) -->
    <div class="filters-modal" id="filters-modal-marcas" style="display: none;">
        <div class="filters-modal-content">
            <div class="filters-modal-header">
                <h3 class="filters-modal-title"><i class="fas fa-filter"></i> Filtros</h3>
                <button class="filters-modal-close" onclick="window.closeFiltersModalMarcas()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="filters-modal-body">
                <!-- Búsqueda -->
                <div class="modal-search-container">
                    <div class="modal-search-input-group">
                        <i class="fas fa-search modal-search-icon"></i>
                        <input type="text" id="modal-search-marcas" class="modal-search-input" 
                               placeholder="Buscar marcas...">
                    </div>
                </div>

                <!-- Filtros Grid -->
                <div class="modal-filters-grid">
                    <!-- Estado -->
                    <div class="modal-filter-group">
                        <label class="modal-filter-label">
                            <i class="fas fa-toggle-on"></i> Estado
                        </label>
                        <select id="modal-filter-estado-marca" class="modal-filter-select">
                            <option value="">Todos los estados</option>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>

                    <!-- Fecha -->
                    <div class="modal-filter-group">
                        <label class="modal-filter-label">
                            <i class="fas fa-calendar"></i> Fecha
                        </label>
                        <select id="modal-filter-fecha-marca" class="modal-filter-select">
                            <option value="">Todas las fechas</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="modal-filter-actions">
                <button class="btn-modern btn-outline" onclick="window.clearAllFiltersModalMarcas()">
                    <i class="fas fa-times"></i> Limpiar
                </button>
                <button class="btn-modern btn-primary" onclick="window.applyFiltersModalMarcas()">
                    <i class="fas fa-check"></i> Aplicar
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* ===== FORZAR COLOR BLANCO EN BOTONES DEL HEADER - MARCAS ===== */
.admin-marcas-module .module-actions .btn-modern,
.admin-marcas-module .module-actions .btn-modern.btn-primary,
.admin-marcas-module .module-actions .btn-modern.btn-secondary,
.admin-marcas-module .module-actions .btn-modern.btn-info,
.admin-marcas-module .module-actions button {
    color: #ffffff !important;
}

.admin-marcas-module .module-actions .btn-modern i,
.admin-marcas-module .module-actions .btn-modern span,
.admin-marcas-module .module-actions .btn-modern.btn-primary i,
.admin-marcas-module .module-actions .btn-modern.btn-primary span,
.admin-marcas-module .module-actions .btn-modern.btn-secondary i,
.admin-marcas-module .module-actions .btn-modern.btn-secondary span,
.admin-marcas-module .module-actions .btn-modern.btn-info i,
.admin-marcas-module .module-actions .btn-modern.btn-info span,
.admin-marcas-module .module-actions button i,
.admin-marcas-module .module-actions button span {
    color: #ffffff !important;
}

/* ===== CORRECCIÓN DE ESPACIADO - MARCAS ===== */
.admin-marcas-module .module-filters {
    display: flex !important;
    flex-direction: column !important;
    visibility: visible !important;
    padding: 20px !important; /* Reducido de var(--spacing-xl) */
    margin-bottom: 0 !important;
}

.admin-marcas-module .module-content {
    margin-top: 0 !important;
    padding-top: 0 !important;
}

.admin-marcas-module .data-table-container {
    margin-top: 0 !important;
}

.admin-marcas-module .filter-group {
    display: flex !important;
    flex-direction: column !important;
}

@media (max-width: 768px) {
    .admin-marcas-module .module-actions .btn-modern,
    .admin-marcas-module .module-actions .btn-modern *,
    .admin-marcas-module .module-actions button,
    .admin-marcas-module .module-actions button * {
        color: #ffffff !important;
    }
    
    /* Ocultar SOLO filtros en móvil, mantener header visible */
    .admin-marcas-module .module-filters {
        display: none !important;
    }
}
</style>

<script>
// ═══════════════════════════════════════════════════════════════════════
// MÓDULO DE marcaS - COMPLETAMENTE NUEVO Y LIMPIO
// ═══════════════════════════════════════════════════════════════════════

console.log('🏷️ [MARCAS] Módulo iniciando...');

// ===== CONFIGURACIÓN =====
const MARCAS_CONFIG = {
    apiUrl: '/fashion-master/app/controllers/MarcaController.php',
    itemsPerPage: 10
};

// ===== VARIABLES GLOBALES DEL MÓDULO =====
let marcasList = [];
let currentPageMarcas = 1;
let totalPagesMarcas = 1;
let isLoadingMarcas = false;

// Variable para tracking de vista actual (tabla o grid)
window.marcas_currentView = 'table'; // Por defecto tabla

// ===== BANDERA DE INICIALIZACIÓN =====
window.marcasModuleInitialized = window.marcasModuleInitialized || false;

// ===== FUNCIÓN DE INICIALIZACIÓN =====
function initializeMarcasModule() {
    console.log('✅ [MARCAS] Iniciando módulo...');
    
    // Evitar doble inicialización
    if (window.marcasModuleInitialized) {
        console.log('⚠️ [MARCAS] Módulo ya inicializado');
        return;
    }
    
    // Detectar si es móvil y cambiar a vista grid INSTANTÁNEAMENTE
    const isMobile = window.innerWidth <= 768;
    if (isMobile) {
        console.log('📱 Dispositivo móvil detectado, cambiando a vista grid instantáneamente');
        
        // CRÍTICO: Establecer variable de vista ANTES de cargar datos
        window.marcas_currentView = 'grid';
        
        // Cambiar botones activos
        const viewButtons = document.querySelectorAll('.view-btn');
        viewButtons.forEach(btn => {
            btn.classList.remove('active');
            if (btn.dataset.view === 'grid') {
                btn.classList.add('active');
            }
        });
        
        // Ocultar tabla inmediatamente
        const tableWrapper = document.getElementById('marcas-table-wrapper');
        if (tableWrapper) {
            tableWrapper.style.display = 'none';
        }
        
        // Mostrar grid inmediatamente
        const gridContainer = document.querySelector('.marcas-grid');
        if (gridContainer) {
            gridContainer.style.display = 'grid';
        }
    } else {
        // Desktop: vista tabla por defecto
        window.marcas_currentView = 'table';
    }
    
    // Cargar datos
    loadMarcasData();
    
    // 🎨 Inicializar Flatpickr en filtro de fechas (si existe la librería)
    if (typeof flatpickr !== 'undefined') {
        const filterFechaMarca = document.getElementById('filter-fecha-marca');
        if (filterFechaMarca) {
            // Convertir select a input para Flatpickr
            const parent = filterFechaMarca.parentNode;
            const newInput = document.createElement('input');
            newInput.id = 'filter-fecha-marca';
            newInput.type = 'text';
            newInput.className = 'filter-select';
            newInput.placeholder = 'Seleccionar rango de fechas';
            newInput.readOnly = true;
            
            parent.replaceChild(newInput, filterFechaMarca);
            
            flatpickr(newInput, {
                mode: 'range',
                dateFormat: 'Y-m-d',
                onChange: function(selectedDates, dateStr, instance) {
                    if (selectedDates.length === 2) {
                        console.log('📅 Rango de fechas seleccionado:', dateStr);
                        loadMarcasData();
                    }
                },
                onClear: function() {
                    loadMarcasData();
                }
            });
            console.log('📅 Flatpickr inicializado en filtro de fechas de marcas');
        }
    }
    
    // 🎬 Añadir animaciones AOS después de cargar el módulo
    setTimeout(() => {
        // Animar header del módulo
        const moduleHeader = document.querySelector('.admin-marcas-module .module-header');
        if (moduleHeader && typeof AOS !== 'undefined') {
            moduleHeader.setAttribute('data-aos', 'fade-down');
            moduleHeader.setAttribute('data-aos-duration', '600');
        }
        
        // Animar filtros
        const moduleFilters = document.querySelector('.admin-marcas-module .module-filters');
        if (moduleFilters && typeof AOS !== 'undefined') {
            moduleFilters.setAttribute('data-aos', 'fade-up');
            moduleFilters.setAttribute('data-aos-duration', '600');
            moduleFilters.setAttribute('data-aos-delay', '200');
        }
        
        // Refrescar AOS para detectar nuevos elementos
        if (typeof AOS !== 'undefined') {
            AOS.refresh();
            console.log('🎬 Animaciones AOS aplicadas al módulo de marcas');
        }
    }, 100);
    
    // Marcar como inicializado
    window.marcasModuleInitialized = true;
}

// ===== CARGAR DATOS =====
async function loadMarcasData() {
    if (isLoadingMarcas) return;
    
    isLoadingMarcas = true;
    console.log('📥 [MARCAS] Cargando datos...');
    
    try {
        const params = new URLSearchParams({
            action: 'list',
            page: currentPageMarcas,
            limit: MARCAS_CONFIG.itemsPerPage
        });
        
        // Agregar filtros
        const searchInput = document.getElementById('search-marcas');
        if (searchInput && searchInput.value) {
            params.append('search', searchInput.value);
            console.log('🔍 Búsqueda:', searchInput.value);
        }
        
        const estadoSelect = document.getElementById('filter-estado-marca');
        if (estadoSelect && estadoSelect.value) {
            params.append('status', estadoSelect.value); // CORREGIDO: usar 'status' en vez de 'estado'
            console.log('🔽 Filtro estado:', estadoSelect.value);
        }
        
        const fechaSelect = document.getElementById('filter-fecha-marca');
        if (fechaSelect && fechaSelect.value) {
            params.append('fecha', fechaSelect.value);
            console.log('📅 Filtro fecha:', fechaSelect.value);
        }
        
        console.log('🌐 [DEBUG] URL completa:', `${MARCAS_CONFIG.apiUrl}?${params}`);
        
        const response = await fetch(`${MARCAS_CONFIG.apiUrl}?${params}`);
        const data = await response.json();
        
        if (data.success) {
            marcasList = data.data || [];
            console.log('✅ [MARCAS] Datos cargados:', marcasList.length);
            
            // 🔍 DEBUG: Mostrar información del filtro
            if (data.debug) {
                console.log('🔍 [DEBUG] WHERE clause:', data.debug.where_clause);
                console.log('🔍 [DEBUG] Params:', data.debug.params);
                console.log('🔍 [DEBUG] Fecha filter:', data.debug.fecha_filter);
            }
            
            // Cargar fechas únicas en el filtro solo si no hay filtro activo
            // Para evitar que se reconstruya el select con datos filtrados
            const fechaSelect = document.getElementById('filter-fecha-marca');
            const hasFechaFilter = fechaSelect && fechaSelect.value;
            if (!hasFechaFilter) {
                loadMarcasDates(marcasList);
            }
            
            // Actualizar UI
            displayMarcas(marcasList);
            updatePaginationMarcas(data.pagination);
            updateStatsMarcas(data.pagination);
        } else {
            throw new Error(data.error || 'Error al cargar marcas');
        }
        
    } catch (error) {
        console.error('❌ [MARCAS] Error:', error);
        showErrorMarcas(error.message);
    } finally {
        isLoadingMarcas = false;
    }
}

// ===== MOSTRAR marcaS EN TABLA - DISEÑO EXACTO DE PRODUCTOS =====
function displayMarcas(marcas) {
    const tbody = document.getElementById('marcas-table-body');
    if (!tbody) return;
    
    // Mensaje cuando no hay marcas
    if (!marcas || marcas.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="loading-cell">
                    <div class="loading-content no-data">
                        <i class="fas fa-building"></i>
                        <span>No se encontraron marcas</span>
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    // Renderizar filas con estructura EXACTA de productos
    tbody.innerHTML = marcas.map((cat, index) => {
        const imageUrl = cat.url_imagen_marca || '/fashion-master/public/assets/img/default-product.jpg';
        const isActive = cat.estado_marca === 'activo';
        
        // Formatear fecha
        const fechaFormateada = cat.fecha_creacion_formato || 
                               cat.fecha_registro_marca || 
                               cat.fecha_creacion_marca ||
                               formatDate(cat.fecha_registro_marca) ||
                               formatDate(cat.fecha_creacion_marca);
        
        return `
        <tr oncontextmenu="return false;" ondblclick="showEditMarcaModal(${cat.id_marca})" style="cursor: pointer;" data-marca-id="${cat.id_marca}">
            <td>${cat.id_marca}</td>
            <td onclick="event.stopPropagation();" ondblclick="event.stopPropagation(); showImageFullSize('${imageUrl}', '${(cat.nombre_marca || '').replace(/'/g, "\\'")}');" style="cursor: zoom-in;">
                <div class="product-image-small">
                    <img src="${imageUrl}" 
                         alt="${cat.nombre_marca}" 
                         onerror="this.src='/fashion-master/public/assets/img/default-product.jpg'">
                </div>
            </td>
            <td>
                <div class="product-info">
                    <strong>${cat.nombre_marca}</strong>
                </div>
            </td>
            <td>
                <code>${cat.codigo_marca || 'N/A'}</code>
            </td>
            <td>${truncateText(cat.descripcion_marca || 'Sin descripción', 50)}</td>
            <td>
                <span class="status-badge ${isActive ? 'status-active' : 'status-inactive'}">
                    ${isActive ? 'Activo' : 'Inactivo'}
                </span>
            </td>
            <td>${fechaFormateada || 'N/A'}</td>
            <td>
                <div class="action-buttons">
                    <button class="btn-action btn-menu" onclick="event.stopPropagation(); showActionMenuMarca(${cat.id_marca}, '${(cat.nombre_marca || '').replace(/'/g, "\\'")}', '${cat.estado_marca}', event)">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                </div>
            </td>
        </tr>`;
    }).join('');
    
    // ⭐ ACTUALIZAR GRID SI ESTÁ ACTIVO O SI ES MÓVIL
    const gridContainer = document.querySelector('.marcas-grid');
    const activeBtn = document.querySelector('.view-btn.active');
    const isMobile = window.innerWidth <= 768;
    
    // En móvil SIEMPRE actualizar grid, en desktop solo si el botón grid está activo
    if (gridContainer && (isMobile || (activeBtn && activeBtn.dataset.view === 'grid'))) {
        displayMarcasGrid(marcas);
    }
}

// ===== ACTUALIZAR UNA SOLA marca EN TIEMPO REAL =====
async function updateSingleMarca(marcaId, updatedData = null) {
    console.log('🔄 === INICIANDO updateSingleMarca ===');
    console.log('🆔 ID recibido:', marcaId);
    console.log('📦 Datos recibidos:', updatedData);
    
    try {
        // Si no se proporciona data, obtenerla del servidor
        if (!updatedData) {
            console.log('⬇️ Obteniendo datos del servidor...');
            const response = await fetch(`${MARCAS_CONFIG.apiUrl}?action=get&id=${marcaId}`);
            const result = await response.json();
            
            if (!result.success) {
                throw new Error('Error al obtener datos de la marca');
            }
            
            updatedData = result.data;
            console.log('📥 Datos obtenidos del servidor:', updatedData);
        }
        
        // VERIFICAR DATOS ANTES DE ACTUALIZAR
        console.log('🔍 Verificando datos para actualizar:');
        console.log('  - ID:', updatedData.id_marca);
        console.log('  - Código:', updatedData.codigo_marca);
        console.log('  - Nombre:', updatedData.nombre_marca);
        console.log('  - Estado:', updatedData.estado_marca);
        
        // Buscar la fila en la tabla
        const selector = `#marcas-table-body tr[data-marca-id="${marcaId}"]`;
        console.log('🔎 Buscando fila con selector:', selector);
        
        const row = document.querySelector(selector);
        
        if (!row) {
            console.warn('⚠️ Fila no encontrada en tabla');
            console.log('📋 Filas disponibles en la tabla:');
            const allRows = document.querySelectorAll('#marcas-table-body tr[data-marca-id]');
            allRows.forEach(r => {
                console.log('  - Fila con ID:', r.getAttribute('data-marca-id'));
            });
            console.log('🔄 Recargando tabla completa como fallback...');
            if (typeof loadMarcasData === 'function') {
                loadMarcasData();
            }
            return;
        }
        
        console.log('✅ Fila encontrada, actualizando contenido...');
        
        // Crear nueva fila HTML
        const imageUrl = updatedData.url_imagen_marca || '/fashion-master/public/assets/img/default-product.jpg';
        const isActive = updatedData.estado_marca === 'activo';
        const fechaFormateada = updatedData.fecha_creacion_formato || 
                               formatDate(updatedData.fecha_creacion_marca) || 'N/A';
        
        const newRowHTML = `
            <td>${updatedData.id_marca}</td>
            <td onclick="event.stopPropagation();" style="cursor: zoom-in;">
                <div class="product-image-small">
                    <img src="${imageUrl}" 
                         alt="${updatedData.nombre_marca}" 
                         onerror="this.src='/fashion-master/public/assets/img/default-product.jpg'">
                </div>
            </td>
            <td>
                <div class="product-info">
                    <strong>${updatedData.nombre_marca}</strong>
                </div>
            </td>
            <td>
                <code>${updatedData.codigo_marca || 'N/A'}</code>
            </td>
            <td>${truncateText(updatedData.descripcion_marca || 'Sin descripción', 50)}</td>
            <td>
                <span class="status-badge ${isActive ? 'status-active' : 'status-inactive'}">
                    ${isActive ? 'Activo' : 'Inactivo'}
                </span>
            </td>
            <td>${fechaFormateada}</td>
            <td>
                <div class="action-buttons">
                    <button class="btn-action btn-menu" onclick="event.stopPropagation(); showActionMenuMarca(${updatedData.id_marca}, '${(updatedData.nombre_marca || '').replace(/'/g, "\\'")}', '${updatedData.estado_marca}', event)">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                </div>
            </td>
        `;
        
        // Actualizar contenido de la fila
        row.innerHTML = newRowHTML;
        row.setAttribute('ondblclick', `editarmarca(${marcaId})`);
        row.setAttribute('data-marca-id', marcaId);
        
        // Actualizar evento de doble click en la imagen
        const imgCell = row.querySelector('td:nth-child(2)');
        if (imgCell) {
            imgCell.setAttribute('ondblclick', `event.stopPropagation(); showImageFullSize('${imageUrl}', '${(updatedData.nombre_marca || '').replace(/'/g, "\\'")}');`);
        }
        
        console.log('✅ HTML de fila actualizado');
        
        // ⭐ ACTUALIZAR GRID SI ESTÁ ACTIVO
        const gridContainer = document.querySelector('.marcas-grid');
        const activeBtn = document.querySelector('.view-btn.active');
        const isMobile = window.innerWidth <= 768;
        
        console.log('🔍 DEBUG updateSingleMarca - Grid check:');
        console.log('  - gridContainer:', gridContainer ? 'EXISTE' : 'NO EXISTE');
        console.log('  - activeBtn:', activeBtn ? activeBtn : 'NO EXISTE');
        console.log('  - activeBtn.dataset.view:', activeBtn ? activeBtn.dataset.view : 'N/A');
        console.log('  - isMobile:', isMobile);
        console.log('  - Condición grid:', gridContainer && (isMobile || (activeBtn && activeBtn.dataset.view === 'grid')));
        
        if (gridContainer && (isMobile || (activeBtn && activeBtn.dataset.view === 'grid'))) {
            console.log('🔄 Actualizando grid...');
            
            // Buscar índice de la marca en marcasList
            const index = marcasList.findIndex(m => m.id_marca == marcaId);
            if (index !== -1) {
                // Actualizar datos en el array
                marcasList[index] = updatedData;
                console.log('✅ Datos actualizados en marcasList');
            }
            
            // ⭐ EN MÓVIL: Actualizar solo la tarjeta específica SIN EFECTOS
            if (isMobile) {
                console.log('📱 Móvil detectado - Actualización silenciosa de tarjeta');
                const card = gridContainer.querySelector(`[data-marca-id="${marcaId}"]`);
                
                if (card) {
                    // Generar HTML de la tarjeta actualizada
                    const isActive = updatedData.estado_marca === 'activo';
                    const estadoText = isActive ? 'Activo' : 'Inactivo';
                    const estadoClass = isActive ? 'active' : 'inactive';
                    const fechaFormateada = updatedData.fecha_creacion_formato || 
                                           formatDate(updatedData.fecha_registro_marca) || 
                                           formatDate(updatedData.fecha_creacion_marca) || 
                                           'N/A';
                    const imageUrl = updatedData.url_imagen_marca || '/fashion-master/public/assets/img/default-product.jpg';
                    
                    const imageHTML = `
                        <div class="product-card-image-mobile ${updatedData.url_imagen_marca ? '' : 'no-image'}">
                            ${updatedData.url_imagen_marca 
                                ? `<img src="${updatedData.url_imagen_marca}" alt="${updatedData.nombre_marca || 'marca'}" onerror="this.parentElement.classList.add('no-image'); this.style.display='none'; this.parentElement.innerHTML='<i class=\\'fas fa-building\\'></i>';">` 
                                : '<i class="fas fa-building"></i>'}
                        </div>
                    `;
                    
                    // Reemplazar contenido de la tarjeta SIN animaciones
                    card.outerHTML = `
                        <div class="product-card" ondblclick="showEditMarcaModal(${updatedData.id_marca})" style="cursor: pointer;" data-marca-id="${updatedData.id_marca}">
                            ${imageHTML}
                            <div class="product-card-header">
                                <h3 class="product-card-title">${updatedData.nombre_marca || 'Sin nombre'}</h3>
                                <span class="product-card-status ${estadoClass}">
                                    ${estadoText}
                                </span>
                            </div>
                            
                            <div class="product-card-body">
                                ${updatedData.codigo_marca ? `<div class="product-card-sku">Código: ${updatedData.codigo_marca}</div>` : ''}
                                
                                ${updatedData.descripcion_marca ? `
                                <div class="product-card-category">
                                    <i class="fas fa-align-left"></i> ${truncateText(updatedData.descripcion_marca, 60)}
                                </div>
                                ` : ''}
                                
                                <div class="product-card-price">
                                    <i class="fas fa-calendar"></i>
                                    ${fechaFormateada}
                                </div>
                            </div>
                            
                            <div class="product-card-actions">
                                <button class="product-card-btn btn-view" onclick="event.stopPropagation(); showViewMarcaModal(${updatedData.id_marca})" style="background-color: #1a73e8 !important; color: white !important; border: none !important; box-shadow: 0 4px 8px rgba(26, 115, 232, 0.3) !important;">
                                    <i class="fas fa-eye" style="color: white !important;"></i>
                                </button>
                                <button class="product-card-btn btn-edit" onclick="event.stopPropagation(); showEditMarcaModal(${updatedData.id_marca})" style="background-color: #34a853 !important; color: white !important; border: none !important; box-shadow: 0 4px 8px rgba(52, 168, 83, 0.3) !important;">
                                    <i class="fas fa-edit" style="color: white !important;"></i>
                                </button>
                                <button class="product-card-btn ${isActive ? 'btn-deactivate' : 'btn-activate'}" 
                                        onclick="event.stopPropagation(); toggleEstadoMarca(${updatedData.id_marca})" 
                                        style="background-color: #6f42c1 !important; color: white !important; border: none !important;">
                                    <i class="fas fa-${isActive ? 'power-off' : 'toggle-on'}" style="color: white !important;"></i>
                                </button>
                                <button class="product-card-btn btn-delete" onclick="event.stopPropagation(); deleteMarca(${updatedData.id_marca}, '${(updatedData.nombre_marca || 'marca').replace(/'/g, "\\'")}')\" style="background-color: #f44336 !important; color: white !important; border: none !important; box-shadow: 0 4px 8px rgba(244, 67, 54, 0.3) !important;">
                                    <i class="fas fa-trash" style="color: white !important;"></i>
                                </button>
                            </div>
                        </div>
                    `;
                    
                    console.log('✅ Tarjeta actualizada silenciosamente (sin efectos)');
                } else {
                    console.warn('⚠️ Tarjeta no encontrada en grid, regenerando todo...');
                    displayMarcasGrid(marcasList);
                }
            } else {
                // ⭐ EN PC: Regenerar grid completo
                console.log('💻 PC detectado - Regenerando grid completo');
                displayMarcasGrid(marcasList);
                console.log('✅ Grid actualizado');
            }
        } else {
            console.log('⚠️ NO se actualiza grid (condición no cumplida)');
        }
        
        // ⭐ SIN EFECTOS VISUALES - Solo actualización silenciosa
        
        console.log('✅ === updateSingleMarca COMPLETADO ===');
        
    } catch (error) {
        console.error('❌ Error actualizando marca:', error);
        console.error('📍 Stack:', error.stack);
        // Fallback: recargar toda la tabla
        if (typeof loadMarcasData === 'function') {
            console.log('🔄 Recargando tabla completa por error...');
            loadMarcasData();
        }
    }
}

// ===== VISTA GRID - DISEÑO EXACTO DE PRODUCTOS =====

// Función para alternar entre tabla y grid
function toggleViewMarcas(viewType) {
    console.log('🔄 [MARCAS] Cambiando vista a:', viewType);
    
    const tableWrapper = document.getElementById('marcas-table-wrapper');
    let gridContainer = document.querySelector('.marcas-grid'); // Usar clase correcta
    
    // ⭐ CERRAR BURBUJAS FLOTANTES INSTANTÁNEAMENTE (SIN ANIMACIÓN)
    if (mar_floatingContainer) {
        console.log('🗑️ [MARCAS] Cerrando burbuja flotante INSTANTÁNEAMENTE');
        
        // Limpiar timeouts
        if (mar_animationTimeout) {
            clearTimeout(mar_animationTimeout);
            mar_animationTimeout = null;
        }
        
        // Eliminar tooltip si existe
        hideTooltipMarca();
        
        // Eliminar contenedor SIN animación
        if (mar_floatingContainer.cleanup) {
            mar_floatingContainer.cleanup();
        }
        mar_floatingContainer.remove();
        
        // Resetear variables globales
        mar_floatingContainer = null;
        mar_centerButton = null;
        mar_floatingButtons = [];
        mar_activeMarcaId = null;
        mar_isAnimating = false;
        
        console.log('✅ [MARCAS] Burbuja eliminada instantáneamente');
    }
    
    // ⭐ LIMPIEZA ADICIONAL: Eliminar cualquier contenedor huérfano
    const orphanedContainers = document.querySelectorAll('.animated-floating-container');
    if (orphanedContainers.length > 0) {
        console.log(`🧹 [MARCAS] Limpiando ${orphanedContainers.length} contenedor(es) huérfano(s)`);
        orphanedContainers.forEach(container => container.remove());
    }
    
    // El grid ya existe en el HTML (#marcas-grid)
    if (!gridContainer) {
        console.error('❌ No se encontró #marcas-grid');
        return;
    }
    
    // Actualizar botones de vista
    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.view === viewType) {
            btn.classList.add('active');
        }
    });
    
    // Mostrar/ocultar vistas
    if (viewType === 'grid') {
        tableWrapper.style.display = 'none';
        gridContainer.style.display = 'grid'; // Mostrar grid
        displayMarcasGrid(marcasList);
    } else {
        tableWrapper.style.display = 'block';
        if (gridContainer) {
            gridContainer.style.display = 'none';
        }
    }
    
    // Guardar preferencia
    try {
        localStorage.setItem('marcas_view_mode', viewType);
    } catch (e) {
        console.warn('No se pudo guardar preferencia de vista:', e);
    }
}

// Crear contenedor grid con clases exactas de productos
function createMarcasGridContainer() {
    console.log('🏗️ [MARCAS] Creando contenedor grid...');
    
    const tableContainer = document.querySelector('.data-table-container');
    if (!tableContainer) {
        console.error('❌ No se encontró .data-table-container');
        return null;
    }
    
    // Crear grid container con ID y clase específica para marcas
    const gridContainer = document.createElement('div');
    gridContainer.id = 'marcas-grid';
    gridContainer.className = 'marcas-grid'; // ⭐ Clase específica para marcas
    gridContainer.style.display = 'none'; // Oculto inicialmente
    
    // Insertar después del wrapper de tabla (más simple y robusto)
    const tableWrapper = document.getElementById('marcas-table-wrapper');
    if (tableWrapper) {
        // Insertar justo después de la tabla
        tableWrapper.insertAdjacentElement('afterend', gridContainer);
        console.log('✅ [MARCAS] Grid insertado después de tabla');
    } else {
        // Fallback: agregar al final del contenedor
        tableContainer.appendChild(gridContainer);
        console.log('✅ [MARCAS] Grid agregado al final del contenedor');
    }
    
    console.log('✅ [MARCAS] Grid container creado exitosamente');
    return gridContainer;
}

// Mostrar marcas en grid - DISEÑO EXACTO DE PRODUCTOS
function displayMarcasGrid(marcas) {
    const gridContainer = document.querySelector('.marcas-grid');
    if (!gridContainer) {
        console.error('Grid container no encontrado');
        return;
    }
    
    // Mensaje cuando no hay marcas
    if (!marcas || marcas.length === 0) {
        gridContainer.innerHTML = `
            <div class="no-products-message">
                <i class="fas fa-building"></i>
                <p>No se encontraron marcas</p>
            </div>
        `;
        return;
    }
    
    // Detectar si es móvil
    const isMobile = window.innerWidth <= 768;
    
    // Generar cards con estructura EXACTA de productos + animaciones AOS
    gridContainer.innerHTML = marcas.map((cat, index) => {
        const imageUrl = cat.url_imagen_marca || '/fashion-master/public/assets/img/default-product.jpg';
        const isActive = cat.estado_marca === 'activo';
        const estadoText = isActive ? 'Activo' : 'Inactivo';
        const estadoClass = isActive ? 'active' : 'inactive';
        
        // Formatear fecha correctamente (intentar varios campos)
        const fechaFormateada = cat.fecha_creacion_formato || 
                               formatDate(cat.fecha_registro_marca) || 
                               formatDate(cat.fecha_creacion_marca) || 
                               'N/A';
        
        // Configurar animaciones AOS basadas en device
        const aosAnimation = isMobile ? 'fade-up' : 'zoom-in';
        const aosDelay = index * 50; // Delay progresivo
        const aosDuration = isMobile ? '400' : '600';
        
        // Generar HTML de imagen (tanto para móvil como PC)
        const imageHTML = `
            <div class="product-card-image-mobile ${cat.url_imagen_marca ? '' : 'no-image'}">
                ${cat.url_imagen_marca 
                    ? `<img src="${cat.url_imagen_marca}" alt="${cat.nombre_marca || 'marca'}" onerror="this.parentElement.classList.add('no-image'); this.style.display='none'; this.parentElement.innerHTML='<i class=\\'fas fa-building\\'></i>';">` 
                    : '<i class="fas fa-building"></i>'}
            </div>
        `;
        
        return `
            <div class="product-card" 
                 ondblclick="showEditMarcaModal(${cat.id_marca})" 
                 style="cursor: pointer;" 
                 data-marca-id="${cat.id_marca}"
                 data-aos="${aosAnimation}"
                 data-aos-delay="${aosDelay}"
                 data-aos-duration="${aosDuration}"
                 data-aos-once="false">
                ${imageHTML}
                <div class="product-card-header">
                    <h3 class="product-card-title">${cat.nombre_marca || 'Sin nombre'}</h3>
                    <span class="product-card-status ${estadoClass}">
                        ${estadoText}
                    </span>
                </div>
                
                <div class="product-card-body">
                    ${cat.codigo_marca ? `<div class="product-card-sku">Código: ${cat.codigo_marca}</div>` : ''}
                    
                    ${cat.descripcion_marca ? `
                    <div class="product-card-category">
                        <i class="fas fa-align-left"></i> ${truncateText(cat.descripcion_marca, 60)}
                    </div>
                    ` : ''}
                    
                    <div class="product-card-price">
                        <i class="fas fa-calendar"></i>
                        ${fechaFormateada}
                    </div>
                </div>
                
                <div class="product-card-actions">
                    <button class="product-card-btn btn-view" onclick="event.stopPropagation(); showViewMarcaModal(${cat.id_marca})" style="background-color: #1a73e8 !important; color: white !important; border: none !important; box-shadow: 0 4px 8px rgba(26, 115, 232, 0.3) !important;">
                        <i class="fas fa-eye" style="color: white !important;"></i>
                    </button>
                    <button class="product-card-btn btn-edit" onclick="event.stopPropagation(); showEditMarcaModal(${cat.id_marca})" style="background-color: #34a853 !important; color: white !important; border: none !important; box-shadow: 0 4px 8px rgba(52, 168, 83, 0.3) !important;">
                        <i class="fas fa-edit" style="color: white !important;"></i>
                    </button>
                    <button class="product-card-btn ${isActive ? 'btn-deactivate' : 'btn-activate'}" 
                            onclick="event.stopPropagation(); toggleEstadoMarca(${cat.id_marca})" 
                            style="background-color: #6f42c1 !important; color: white !important; border: none !important;">
                        <i class="fas fa-${isActive ? 'power-off' : 'toggle-on'}" style="color: white !important;"></i>
                    </button>
                    <button class="product-card-btn btn-delete" onclick="event.stopPropagation(); deleteMarca(${cat.id_marca}, '${(cat.nombre_marca || 'marca').replace(/'/g, "\\'")}')\" style="background-color: #f44336 !important; color: white !important; border: none !important; box-shadow: 0 4px 8px rgba(244, 67, 54, 0.3) !important;">
                        <i class="fas fa-trash" style="color: white !important;"></i>
                    </button>
                </div>
            </div>
        `;
    }).join('');
    
    // Aplicar Masonry layout en móvil
    if (isMobile) {
        applyMasonryLayoutMarcas();
    }
    
    // Refrescar AOS para detectar nuevas tarjetas
    if (typeof AOS !== 'undefined') {
        AOS.refresh();
    }
    
    console.log(`✅ [MARCAS] Grid renderizado con ${marcas.length} marcas + animaciones AOS`);
}

// Función para aplicar Masonry layout en marcas
function applyMasonryLayoutMarcas() {
    const gridContainer = document.querySelector('.marcas-grid');
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

// ===== FUNCIONES DE UTILIDAD =====
function truncateText(text, length) {
    if (!text || text.length <= length) return text || '';
    return text.substring(0, length) + '...';
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', { year: 'numeric', month: 'short', day: 'numeric' });
}

function showErrorMarcas(message) {
    const tbody = document.getElementById('marcas-table-body');
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="error-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Error: ${message}</p>
                    <button onclick="loadMarcasData()" class="btn-modern btn-primary">
                        <i class="fas fa-refresh"></i> Reintentar
                    </button>
                </td>
            </tr>
        `;
    }
}

// ===== ACTUALIZAR STATS =====
function updateStatsMarcas(pagination) {
    if (!pagination) return;
    
    const showingEl = document.getElementById('showing-end-marcas');
    const totalEl = document.getElementById('total-marcas');
    
    if (showingEl) showingEl.textContent = pagination.total_items || 0;
    if (totalEl) totalEl.textContent = pagination.total_items || 0;
}

// ═════════════════════════════════════════════════════════════════════════════
// SISTEMA DE MENÚ FLOTANTE - CON NOMBRES ÚNICOS PARA marcaS
// ═════════════════════════════════════════════════════════════════════════════

// Variables globales ÚNICAS para marcas
let mar_floatingContainer = null;
let mar_activeMarcaId = null;
let mar_isAnimating = false;
let mar_animationTimeout = null;
let mar_floatingButtons = [];
let mar_centerButton = null;

// Función principal para mostrar botones flotantes - RENOMBRADA
function showActionMenuMarca(marcaId, marcaNombre, estado, event) {
    console.log('═══════════════════════════════════════════════════════════');
    console.log('🎯 [MARCA] showActionMenuMarca LLAMADA');
    console.log('   ID:', marcaId);
    console.log('   Nombre:', marcaNombre);
    console.log('   Estado:', estado);
    console.log('   Event:', event);
    console.log('   mar_isAnimating:', mar_isAnimating);
    console.log('   mar_floatingContainer:', mar_floatingContainer);
    console.log('   mar_activeMarcaId:', mar_activeMarcaId);
    console.log('═══════════════════════════════════════════════════════════');
    
    // Prevenir múltiples ejecuciones
    if (mar_isAnimating) {
        console.log('⏸️ [MARCA] Animación en progreso, ABORTANDO');
        return;
    }
    
    // Si ya está abierto para la misma marca, cerrarlo
    if (mar_floatingContainer && mar_activeMarcaId === marcaId) {
        console.log('🔄 [MARCA] Mismo menú abierto, cerrando...');
        closeFloatingActionsMarca();
        return;
    }
    
    // Cerrar cualquier menú anterior
    if (mar_floatingContainer) {
        console.log('🧹 [MARCA] Cerrando menú anterior...');
        closeFloatingActionsMarca();
    }
    
    // Obtener el botón que disparó el evento
    let triggerButton = null;
    
    console.log('🔍 [MARCA] Buscando trigger button...');
    
    if (event && event.currentTarget) {
        triggerButton = event.currentTarget;
        console.log('✅ [MARCA] Trigger button encontrado: currentTarget', triggerButton);
    } else if (event && event.target) {
        triggerButton = event.target.closest('.btn-menu');
        console.log('✅ [MARCA] Trigger button encontrado: target.closest', triggerButton);
    } else {
        console.log('⚠️ [MARCA] No hay event, buscando en DOM...');
        const allMenuButtons = document.querySelectorAll('.btn-menu');
        console.log('   Total botones .btn-menu encontrados:', allMenuButtons.length);
        for (const btn of allMenuButtons) {
            const onclickAttr = btn.getAttribute('onclick') || '';
            if (onclickAttr.includes(`showActionMenuMarca(${marcaId}`)) {
                triggerButton = btn;
                console.log('✅ [MARCA] Trigger button encontrado por onclick:', triggerButton);
                break;
            }
        }
    }
    
    if (!triggerButton) {
        console.error('❌ [MARCA] No se encontró trigger button - ABORTANDO');
        console.log('   Event:', event);
        console.log('   event.currentTarget:', event?.currentTarget);
        console.log('   event.target:', event?.target);
        return;
    }
    
    console.log('🎨 [MARCA] Creando menú flotante...');
    mar_isAnimating = true;
    mar_activeMarcaId = marcaId;
    
    // Crear contenedor flotante con animaciones
    createFloatingContainerMarca(triggerButton, marcaId, marcaNombre, estado);
}

// Crear el contenedor flotante con animaciones avanzadas
function createFloatingContainerMarca(triggerButton, marcaId, marcaNombre, estado) {
    console.log('🎨 [MARCA] createFloatingContainerMarca INICIANDO');
    console.log('   triggerButton:', triggerButton);
    console.log('   marcaId:', marcaId);
    
    if (mar_floatingContainer) {
        console.log('🧹 [MARCA] Limpiando contenedor anterior...');
        closeFloatingActionsMarca();
    }
    
    if (!triggerButton) {
        console.error('❌ [MARCA] No hay triggerButton - ABORTANDO');
        mar_isAnimating = false;
        return;
    }
    
    console.log('📦 [MARCA] Creando contenedor...');
    mar_floatingContainer = document.createElement('div');
    mar_floatingContainer.id = 'mar-floating-menu-' + marcaId;
    mar_floatingContainer.className = 'mar-floating-container';
    console.log('   Contenedor creado:', mar_floatingContainer);
    
    const tableContainer = document.querySelector('.data-table-wrapper') || 
                          document.querySelector('.module-content') || 
                          document.querySelector('.admin-module');
    
    
    if (tableContainer) {
        const computedStyle = window.getComputedStyle(tableContainer);
        console.log('   Position actual:', computedStyle.position);
        if (computedStyle.position === 'static') {
            tableContainer.style.position = 'relative';
            console.log('   Position cambiada a: relative');
        }
    }
    
    mar_floatingContainer.style.cssText = `
        position: fixed !important;
        z-index: 999999 !important;
        pointer-events: none !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        display: block !important;
    `;
    
    mar_floatingContainer.triggerButton = triggerButton;
    
    console.log('⚙️ [MARCA] Creando botón central...');
    createCenterButtonMarca();
    
    const actions = [
        { icon: 'fa-eye', color: '#1a73e8', actionFn: () => viewMarcaAction(marcaId) },
        { icon: 'fa-edit', color: '#34a853', actionFn: () => editMarcaAction(marcaId) },
        { icon: estado === 'activo' ? 'fa-power-off' : 'fa-toggle-on', color: '#9c27b0', actionFn: () => changeMarcaEstadoAction(marcaId) },
        { icon: 'fa-trash', color: '#f44336', actionFn: () => deleteMarcaAction(marcaId, marcaNombre) }
    ];
    
    mar_floatingButtons = [];
    const radius = 80;
    
    actions.forEach((action, index) => {
        const angle = (index / actions.length) * 2 * Math.PI - Math.PI / 2;
        console.log(`   Botón ${index + 1}:`, action.label, 'ángulo:', angle);
        createButtonMarca(action, index, angle, radius);
    });
    
    console.log('📍 [MARCA] Agregando contenedor al DOM...');
    
    // AGREGAR INDICADOR VISUAL PARA DEBUG
    const debugIndicator = document.createElement('div');
    debugIndicator.style.cssText = `
        position: fixed !important;
        top: 10px !important;
        right: 10px !important;
        background: red !important;
        color: white !important;
        padding: 10px 20px !important;
        border-radius: 5px !important;
        z-index: 99999999 !important;
        font-weight: bold !important;
        box-shadow: 0 4px 6px rgba(0,0,0,0.3) !important;
        font-size: 14px !important;
    `;
    
    // Con position: fixed, SIEMPRE agregar al body
    document.body.appendChild(mar_floatingContainer);
    console.log('   ✅ Contenedor agregado a BODY (position: fixed)');
    
    console.log('📐 [MARCA] Actualizando posiciones...');
    updateButtonPositionsMarca();
    
    mar_activeMarcaId = marcaId;
    
    console.log('🎧 [MARCA] Configurando event listeners...');
    setupEventListenersMarca();
    
    console.log('🚀 [MARCA] Iniciando animación de apertura...');
    startOpenAnimationMarca();
    
    console.log('✅ [MARCA] createFloatingContainerMarca COMPLETADO');
}

// Crear botón central con tres puntitos
function createCenterButtonMarca() {
    mar_centerButton = document.createElement('div');
    mar_centerButton.className = 'mar-center-button';
    mar_centerButton.innerHTML = '<i class="fas fa-ellipsis-v"></i>';
    
    mar_centerButton.style.cssText = `
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
    
    mar_centerButton.addEventListener('mouseenter', () => {
        mar_centerButton.style.transform = 'scale(1.15) rotate(180deg)';
    });
    
    mar_centerButton.addEventListener('mouseleave', () => {
        mar_centerButton.style.transform = 'scale(1) rotate(360deg)';
        mar_centerButton.style.boxShadow = 'none';
        mar_centerButton.style.background = 'transparent';
    });
    
    mar_centerButton.addEventListener('click', (e) => {
        e.stopPropagation();
        closeFloatingActionsMarca();
    });
    
    mar_floatingContainer.appendChild(mar_centerButton);
}

// Crear botón animado individual
function createButtonMarca(action, index, angle, radius) {
    const button = document.createElement('div');
    button.innerHTML = `<i class="fas ${action.icon}"></i>`;
    button.dataset.angle = angle;
    button.dataset.radius = radius;
    button.dataset.index = index;
    button.className = 'mar-floating-button';
    
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
    
    button.addEventListener('mouseenter', () => {
        button.style.transform = 'scale(1.2) rotate(15deg)';
        button.style.boxShadow = `0 10px 30px ${action.color}60`;
        button.style.zIndex = '1000003';
        createRippleMarca(button, action.color);
        // Tooltip deshabilitado - solo iconos
        // showTooltipMarca(button, action.label);
    });
    
    button.addEventListener('mouseleave', () => {
        button.style.transform = 'scale(1) rotate(0deg)';
        button.style.boxShadow = `0 6px 20px ${action.color}40`;
        button.style.zIndex = '1000001';
        hideTooltipMarca();
    });
    
    button.addEventListener('click', (e) => {
        e.stopPropagation();
        forceCloseMarca();
        
        button.style.transform = 'scale(0.9) rotate(180deg)';
        setTimeout(() => {
            button.style.transform = 'scale(1.1) rotate(360deg)';
        }, 100);
        
        setTimeout(() => {
            try {
                action.actionFn();
            } catch (err) {
                console.error('❌ Error ejecutando acción:', err);
            }
        }, 200);
    });
    
    mar_floatingContainer.appendChild(button);
    mar_floatingButtons.push(button);
}

// Crear efecto ripple
function createRippleMarca(button, color) {
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
        animation: marRippleEffect 0.6s ease-out !important;
        z-index: -1 !important;
    `;
    
    if (!document.querySelector('#mar-ripple-styles')) {
        const styles = document.createElement('style');
        styles.id = 'mar-ripple-styles';
        styles.textContent = `
            @keyframes marRippleEffect {
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
function showTooltipMarca(button, text) {
    hideTooltipMarca();
    
    const tooltip = document.createElement('div');
    tooltip.id = 'mar-tooltip';
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
    
    const buttonRect = button.getBoundingClientRect();
    tooltip.style.left = (buttonRect.left + buttonRect.width / 2 - tooltip.offsetWidth / 2) + 'px';
    tooltip.style.top = (buttonRect.bottom + 10) + 'px';
    
    setTimeout(() => {
        tooltip.style.opacity = '1';
        tooltip.style.transform = 'translateY(0)';
    }, 50);
}

// Ocultar tooltip
function hideTooltipMarca() {
    const tooltip = document.getElementById('mar-tooltip');
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

// Actualizar posiciones de botones
function updateButtonPositionsMarca() {
    console.log('📐 [MARCA] updateButtonPositionsMarca INICIANDO');
    console.log('   mar_floatingContainer:', mar_floatingContainer);
    console.log('   mar_floatingContainer.triggerButton:', mar_floatingContainer?.triggerButton);
    
    if (!mar_floatingContainer || !mar_floatingContainer.triggerButton) {
        console.log('⚠️ [MARCA] No hay contenedor o trigger button');
        return;
    }
    
    if (!document.contains(mar_floatingContainer.triggerButton)) {
        console.log('⚠️ [MARCA] Trigger button no está en el DOM');
        closeFloatingActionsMarca();
        return;
    }
    
    // USAR POSICIÓN FIJA RELATIVA AL VIEWPORT (no al contenedor)
    const triggerRect = mar_floatingContainer.triggerButton.getBoundingClientRect();
    
    console.log('📏 [MARCA] Posiciones (VIEWPORT):');
    console.log('   triggerRect:', triggerRect);
    console.log('   window.innerWidth:', window.innerWidth);
    console.log('   window.innerHeight:', window.innerHeight);
    
    // Calcular centro del botón trigger en coordenadas del viewport
    const centerX = triggerRect.left + triggerRect.width / 2;
    const centerY = triggerRect.top + triggerRect.height / 2;
    
    console.log('🎯 [MARCA] Centro calculado (VIEWPORT):');
    console.log('   centerX:', centerX, 'centerY:', centerY);
    
    if (mar_centerButton) {
        mar_centerButton.style.left = `${centerX - 22.5}px`;
        mar_centerButton.style.top = `${centerY - 22.5}px`;
        console.log('✅ [MARCA] Botón central posicionado:', {
            left: mar_centerButton.style.left,
            top: mar_centerButton.style.top
        });
    }
    
    mar_floatingButtons.forEach((button, index) => {
        const angle = parseFloat(button.dataset.angle);
        const radius = parseFloat(button.dataset.radius);
        
        if (isNaN(angle) || isNaN(radius)) return;
        
        const x = centerX + Math.cos(angle) * radius;
        const y = centerY + Math.sin(angle) * radius;
        
        button.style.left = `${x - 27.5}px`;
        button.style.top = `${y - 27.5}px`;
        
        console.log(`   Botón ${index}:`, {
            angle,
            radius,
            x: button.style.left,
            y: button.style.top
        });
    });
    
    console.log('✅ [MARCA] Posiciones actualizadas (FIXED al viewport)');
}

// Iniciar animación de apertura
function startOpenAnimationMarca() {
    console.log('🚀 [MARCA] startOpenAnimationMarca INICIANDO');
    console.log('   mar_centerButton:', mar_centerButton);
    console.log('   mar_floatingButtons:', mar_floatingButtons.length);
    
    if (mar_centerButton) {
        console.log('   Estado inicial del botón central:', {
            transform: mar_centerButton.style.transform,
            opacity: mar_centerButton.style.opacity,
            left: mar_centerButton.style.left,
            top: mar_centerButton.style.top
        });
        
        setTimeout(() => {
            mar_centerButton.style.transform = 'scale(1) rotate(360deg)';
            mar_centerButton.style.opacity = '1';
            console.log('✅ [MARCA] Botón central animado:', {
                transform: mar_centerButton.style.transform,
                opacity: mar_centerButton.style.opacity
            });
        }, 100);
    }
    
    mar_floatingButtons.forEach((button, index) => {
        setTimeout(() => {
            console.log(`🎨 [MARCA] Animando botón ${index}...`);
            button.style.transform = 'scale(1) rotate(0deg)';
            button.style.opacity = '1';
            console.log(`   Estado:`, {
                transform: button.style.transform,
                opacity: button.style.opacity,
                left: button.style.left,
                top: button.style.top
            });
        }, 200 + (index * 100));
    });
    
    setTimeout(() => {
        mar_isAnimating = false;
        console.log('✅ [MARCA] Animación completada, mar_isAnimating:', mar_isAnimating);
    }, 200 + (mar_floatingButtons.length * 100) + 400);
}

// Event listeners animados
function setupEventListenersMarca() {
    const handleClick = (e) => {
        if (mar_floatingContainer && !mar_floatingContainer.contains(e.target)) {
            closeFloatingActionsMarca();
        }
    };
    
    const handleResize = () => {
        if (mar_floatingContainer) {
            setTimeout(() => {
                updateButtonPositionsMarca();
            }, 100);
        }
    };
    
    const handleScroll = () => {
        if (mar_floatingContainer) {
            updateButtonPositionsMarca();
        }
    };
    
    document.addEventListener('click', handleClick);
    window.addEventListener('resize', handleResize, { passive: true });
    
    const container = mar_floatingContainer.parentElement;
    if (container) {
        container.addEventListener('scroll', handleScroll, { passive: true });
    }
    
    mar_floatingContainer.cleanup = () => {
        document.removeEventListener('click', handleClick);
        window.removeEventListener('resize', handleResize);
        if (container) {
            container.removeEventListener('scroll', handleScroll);
        }
    };
}

// Cerrar menú flotante con animación
function closeFloatingActionsMarca() {
    if (!mar_floatingContainer || mar_isAnimating) return;
    
    mar_isAnimating = true;
    
    if (mar_animationTimeout) {
        clearTimeout(mar_animationTimeout);
    }
    
    hideTooltipMarca();
    
    mar_floatingButtons.forEach((button, index) => {
        setTimeout(() => {
            button.style.transform = 'scale(0) rotate(-180deg)';
            button.style.opacity = '0';
        }, index * 50);
    });
    
    if (mar_centerButton) {
        setTimeout(() => {
            mar_centerButton.style.transform = 'scale(0) rotate(-360deg)';
            mar_centerButton.style.opacity = '0';
        }, mar_floatingButtons.length * 50 + 100);
    }
    
    mar_animationTimeout = setTimeout(() => {
        if (mar_floatingContainer) {
            if (mar_floatingContainer.cleanup) {
                mar_floatingContainer.cleanup();
            }
            
            mar_floatingContainer.remove();
            mar_floatingContainer = null;
            mar_centerButton = null;
            mar_floatingButtons = [];
            mar_activeMarcaId = null;
            mar_isAnimating = false;
            
            // Eliminar indicador DEBUG
            const debugIndicator = document.getElementById('mar-debug-indicator-' + mar_activeMarcaId);
            if (debugIndicator) {
                debugIndicator.remove();
                console.log('🔴 [MARCA] Indicador DEBUG eliminado');
            }
        }
    }, mar_floatingButtons.length * 50 + 400);
}

// Forzar cierre del menú flotante
function forceCloseMarca() {
    setTimeout(() => {
        if (mar_animationTimeout) {
            clearTimeout(mar_animationTimeout);
            mar_animationTimeout = null;
        }
        
        hideTooltipMarca();
        
        if (mar_floatingContainer) {
            try {
                if (mar_floatingContainer.cleanup) {
                    mar_floatingContainer.cleanup();
                }
                mar_floatingContainer.remove();
            } catch (e) {}
            
            mar_floatingContainer = null;
            mar_centerButton = null;
            mar_floatingButtons = [];
            mar_activeMarcaId = null;
            mar_isAnimating = false;
        }
        
        const orphanedContainers = document.querySelectorAll('.mar-floating-container');
        orphanedContainers.forEach(container => {
            try {
                container.remove();
            } catch (e) {}
        });
    }, 320);
}

// ═════════════════════════════════════════════════════════════════════════════
// ═════════════════════════════════════════════════════════════════════════════
// FUNCIONES DE ACCIONES - CON NOMBRES ÚNICOS
// ═════════════════════════════════════════════════════════════════════════════

// Ver Marca
function viewMarcaAction(marcaId) {
    console.log('👁️ Ver Marca:', marcaId);
    showViewMarcaModal(marcaId);
}

// Editar Marca
function editMarcaAction(marcaId) {
    console.log('✏️ Editar Marca:', marcaId);
    showEditMarcaModal(marcaId);
}

// Cambiar estado de marca - EN TIEMPO REAL
async function changeMarcaEstadoAction(marcaId) {
    console.log('⚡ Cambiando estado de marca:', marcaId);
    
    try {
        // Buscar marca actual para determinar el nuevo estado
        const marca = marcasList.find(c => c.id_marca == marcaId);
        if (!marca) {
            throw new Error('marca no encontrada');
        }
        
        const nuevoEstado = marca.estado_marca === 'activo' ? 'inactivo' : 'activo';
        console.log('   Estado actual:', marca.estado_marca, '→ Nuevo:', nuevoEstado);
        
        const response = await fetch(`${MARCAS_CONFIG.apiUrl}?action=change_estado`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `id_marca=${marcaId}&estado=${nuevoEstado}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            console.log('✅ Estado cambiado exitosamente');
            
            // Actualizar en el array local
            marca.estado_marca = nuevoEstado;
            
            // Actualizar fila en tabla
            const row = document.querySelector(`tr[data-marca-id="${marcaId}"]`);
            if (row) {
                const badge = row.querySelector('.status-badge');
                if (badge) {
                    const isActive = nuevoEstado === 'activo';
                    badge.className = `status-badge ${isActive ? 'status-active' : 'status-inactive'}`;
                    badge.textContent = isActive ? 'Activo' : 'Inactivo';
                }
            }
            
            // Actualizar card en grid si está visible
            const card = document.querySelector(`.product-card[data-marca-id="${marcaId}"] .status-badge`);
            if (card) {
                const isActive = nuevoEstado === 'activo';
                card.className = `status-badge ${isActive ? 'status-active' : 'status-inactive'}`;
                card.textContent = isActive ? 'Activo' : 'Inactivo';
            }
            
            // Actualizar botón de acción en grid
            const gridActionBtn = document.querySelector(`.product-card[data-marca-id="${marcaId}"] .toggle-status-btn`);
            if (gridActionBtn) {
                const isActive = nuevoEstado === 'activo';
                gridActionBtn.innerHTML = `<i class="fas fa-${isActive ? 'power-off' : 'toggle-on'}"></i>`;
                gridActionBtn.title = isActive ? 'Desactivar' : 'Activar';
            }
        } else {
            throw new Error(data.error || 'Error al cambiar estado');
        }
        
    } catch (error) {
        console.error('❌ Error:', error);
        showNotification('Error al cambiar el estado de la marca: ' + error.message, 'error');
    }
}

// Eliminar marca con modal de confirmación
function deleteMarcaAction(marcaId, marcaNombre) {
    console.log('🗑️ Eliminar marca:', marcaId, marcaNombre);
    // 🔴 OCULTAR menú flotante (no cerrar, para evitar errores de null)
    if (mar_floatingContainer) {
        mar_floatingContainer.style.opacity = '0';
        mar_floatingContainer.style.pointerEvents = 'none';
    }
    showDeleteConfirmationMarca(marcaId, marcaNombre); // ✅ Usar la versión CON estilos inline
}

// ===== FUNCIÓN PARA ELIMINAR DESDE GRID - CON CONFIRMACIÓN =====
function deleteMarca(marcaId, marcaNombre) {
    console.log('🗑️ deleteMarca wrapper llamada desde grid:', marcaId, marcaNombre);
    // 🔴 OCULTAR menú flotante si existe
    if (typeof mar_floatingContainer !== 'undefined' && mar_floatingContainer) {
        mar_floatingContainer.style.opacity = '0';
        mar_floatingContainer.style.pointerEvents = 'none';
    }
    showDeleteConfirmationMarca(marcaId, marcaNombre || 'marca');
}

// ═════════════════════════════════════════════════════════════════════════════
// FILTROS Y BÚSQUEDA EN TIEMPO REAL
// ═════════════════════════════════════════════════════════════════════════════

// Debounce para búsqueda en tiempo real
let searchDebounceTimer = null;

function handleSearchMarcas() {
    clearTimeout(searchDebounceTimer);
    
    searchDebounceTimer = setTimeout(() => {
        console.log('🔍 Buscando marcas...');
        currentPageMarcas = 1; // Resetear a página 1
        loadMarcasData();
    }, 500); // 500ms de delay
}

function clearSearchMarcas() {
    const searchInput = document.getElementById('search-marcas');
    if (searchInput) {
        searchInput.value = '';
        handleSearchMarcas();
    }
}

function filterMarcas() {
    console.log('🔽 Filtrando marcas...');
    currentPageMarcas = 1; // Resetear a página 1
    loadMarcasData();
}

// ═════════════════════════════════════════════════════════════════════════════
// FILTROS Y BÚSQUEDA
// ═════════════════════════════════════════════════════════════════════════════

function clearAllFiltersMarcas() {
    console.log('🧹 Limpiando todos los filtros...');
    
    // Limpiar todos los campos con JavaScript nativo
    const searchInput = document.getElementById('search-marcas');
    const filterEstado = document.getElementById('filter-estado-marca');
    const filterFecha = document.getElementById('filter-fecha-marca');
    
    if (searchInput) searchInput.value = '';
    if (filterEstado) filterEstado.value = '';
    if (filterFecha) {
        filterFecha.innerHTML = '<option value="">Todas las fechas</option>';
        filterFecha.value = '';
    }
    
    // Efecto visual de limpieza
    const moduleFilters = document.querySelector('.module-filters');
    if (moduleFilters) {
        moduleFilters.classList.add('filters-clearing');
        
        setTimeout(() => {
            moduleFilters.classList.remove('filters-clearing');
        }, 400);
    }
    
    // Resetear página y recargar
    currentPageMarcas = 1;
    loadMarcasData();
    
    console.log('✅ Filtros limpiados');
}

// Función para cargar fechas únicas en el filtro
function loadMarcasDates(marcas) {
    // Validar que marcas existe y es un array
    if (!marcas || !Array.isArray(marcas) || marcas.length === 0) {
        console.log('⚠️ loadMarcasDates: No hay marcas para procesar');
        return;
    }
    
    console.log('📅 loadMarcasDates() iniciado con', marcas.length, 'marcas');
    console.log('📊 Primera marca completa:', marcas[0]);
    
    const fechaSelect = document.getElementById('filter-fecha-marca');
    if (!fechaSelect) {
        console.error('❌ No se encontró el select filter-fecha-marca');
        return;
    }
    
    // Si el select ya tiene más de 1 opción (Todas las fechas + otras), no reconstruir
    if (fechaSelect.options.length > 1) {
        console.log('✓ El select ya tiene fechas cargadas, omitiendo reconstrucción');
        return;
    }
    
    // Extraer fechas únicas de las marcas
    const fechasSet = new Set();
    marcas.forEach((cat, index) => {
        console.log(`  🔍 marca ${index}:`, {
            nombre: cat.nombre_marca,
            fecha_registro: cat.fecha_registro,
            fecha_creacion: cat.fecha_creacion_marca,
            fecha_creacion_formato: cat.fecha_creacion_formato
        });
        
        // Intentar múltiples campos de fecha
        let fecha = cat.fecha_registro || cat.fecha_creacion_marca || cat.fecha_creacion_formato;
        
        if (fecha) {
            // Si es formato dd/mm/yyyy, convertir a yyyy-mm-dd
            if (fecha.includes('/')) {
                const parts = fecha.split('/');
                if (parts.length === 3) {
                    fecha = `${parts[2]}-${parts[1].padStart(2, '0')}-${parts[0].padStart(2, '0')}`;
                }
            } else {
                // Si es timestamp, extraer solo la fecha
                fecha = fecha.split(' ')[0];
            }
            
            fechasSet.add(fecha);
            console.log(`    ✓ Fecha extraída: ${fecha}`);
        } else {
            console.log(`    ⚠️ Sin fecha en marca ${cat.nombre_marca}`);
        }
    });
    
    console.log('📊 Total fechas únicas:', fechasSet.size);
    console.log('📊 Fechas:', Array.from(fechasSet));
    
    // Convertir a array y ordenar descendente (más reciente primero)
    const fechasArray = Array.from(fechasSet).sort((a, b) => b.localeCompare(a));
    
    // VALIDACIÓN CRÍTICA: Verificar que fechasArray existe y es un array
    if (!fechasArray || !Array.isArray(fechasArray)) {
        console.error('❌ fechasArray no es un array válido:', fechasArray);
        return;
    }
    
    // Guardar opción seleccionada actual
    const selectedValue = fechaSelect.value;
    
    // Limpiar opciones excepto la primera
    fechaSelect.innerHTML = '<option value="">Todas las fechas</option>';
    
    // Agregar opciones de fechas con validación
    if (fechasArray.length > 0) {
        fechasArray.forEach(fecha => {
            if (fecha) { // Validar que la fecha existe
                const option = document.createElement('option');
                option.value = fecha;
                option.textContent = formatearFecha(fecha);
                fechaSelect.appendChild(option);
                console.log('  ➕ Opción agregada:', fecha, '→', formatearFecha(fecha));
            }
        });
    }
    
    // Restaurar selección si existe
    if (selectedValue && fechasArray.includes(selectedValue)) {
        fechaSelect.value = selectedValue;
    }
    
    console.log('✅ Filtro de fechas cargado con', fechasArray.length, 'opciones');
}

// Función auxiliar para formatear fechas
function formatearFecha(fechaStr) {
    // Convertir YYYY-MM-DD a DD/MM/YYYY (formato numérico como productos)
    const [year, month, day] = fechaStr.split('-');
    return `${day}/${month}/${year}`;
}

// ═════════════════════════════════════════════════════════════════════════════
// MODALES (CREAR, EDITAR, VER)
// ═════════════════════════════════════════════════════════════════════════════
// FUNCIONES PARA MODALES DE marcaS (Crear, Editar, Ver)
// ═════════════════════════════════════════════════════════════════════════════

function showCreateMarcaModal() {
    console.log('🆕 Abriendo modal de crear marca');
    openMarcaModal('create');
}

function showEditMarcaModal(marcaId) {
    console.log('✏️ Abriendo modal de Editar Marca:', marcaId);
    openMarcaModal('edit', marcaId);
}

function showViewMarcaModal(marcaId) {
    console.log('👁️ Abriendo modal de Ver Marca:', marcaId);
    openMarcaModal('view', marcaId);
}

function openMarcaModal(action, marcaId = null) {
    console.log('🔄 openMarcaModal:', action, marcaId);
    
    // Bloquear scroll del body
    document.body.classList.add('marca-modal-open');
    
    // Construir URL
    let modalUrl = 'app/views/admin/marca_modal.php';
    const params = new URLSearchParams();
    params.append('action', action);
    if (marcaId) {
        params.append('id', marcaId);
    }
    modalUrl += '?' + params.toString();
    
    console.log('🌐 Cargando modal marca desde:', modalUrl);
    
    // Crear contenedor temporal para loading
    const loadingDiv = document.createElement('div');
    loadingDiv.id = 'marca-modal-loading';
    loadingDiv.className = 'marca-view-modal';
    loadingDiv.innerHTML = `
        <div class="marca-view-modal__overlay"></div>
        <div class="marca-view-modal__container" style="display: flex; align-items: center; justify-content: center; min-height: 400px;">
            <div style="text-align: center; color: white;">
                <i class="fas fa-spinner fa-spin" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                <p style="font-size: 1.125rem;">Cargando...</p>
            </div>
        </div>
    `;
    document.body.appendChild(loadingDiv);
    
    // Mostrar loading con animación
    setTimeout(() => {
        loadingDiv.classList.add('show');
    }, 10);
    
    fetch(modalUrl)
        .then(response => {
            console.log('📡 Respuesta recibida:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(html => {
            console.log('📄 HTML recibido, longitud:', html.length);
            
            // Remover loading
            if (loadingDiv) {
                loadingDiv.remove();
            }
            
            // Crear un div temporal para parsear el HTML
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;
            
            // Extraer el modal (VIEW usa #marca-view-modal con ID único)
            let modal = tempDiv.querySelector('#marca-view-modal');
            let isViewModal = true;
            
            if (!modal) {
                // Es modal CREATE/EDIT, buscar .modal-content
                modal = tempDiv.querySelector('.modal-content');
                isViewModal = false;
                
                if (modal) {
                    // Crear el overlay y wrapper para modal-content (igual que productos)
                    const overlay = document.createElement('div');
                    overlay.className = 'modal-overlay';
                    overlay.id = 'marca-modal-overlay';
                    
                    overlay.appendChild(modal);
                    document.body.appendChild(overlay);
                    
                    // ⭐ EJECUTAR SCRIPTS DEL MODAL (CRÍTICO)
                    const scripts = tempDiv.querySelectorAll('script');
                    console.log(`📜 Ejecutando ${scripts.length} scripts del modal`);
                    scripts.forEach((script, index) => {
                        if (script.textContent && script.textContent.trim()) {
                            try {
                                console.log(`  ✅ Ejecutando script ${index + 1}`);
                                eval(script.textContent);
                            } catch (scriptError) {
                                console.error(`  ❌ Error en script ${index + 1}:`, scriptError);
                            }
                        }
                    });
                    
                    // Mostrar con animación
                    setTimeout(() => {
                        overlay.classList.add('show');
                        console.log('✅ Modal marca CREATE/EDIT mostrado');
                    }, 10);
                    
                    // Event listener para cerrar
                    overlay.addEventListener('click', (e) => {
                        if (e.target === overlay) {
                            closeMarcaModal();
                        }
                    });
                    
                    // Botón close
                    const closeBtn = modal.querySelector('.modal-close');
                    if (closeBtn) {
                        closeBtn.addEventListener('click', closeMarcaModal);
                    }
                    
                    // Listener ESC
                    const handleEsc = (e) => {
                        if (e.key === 'Escape') {
                            closeMarcaModal();
                            document.removeEventListener('keydown', handleEsc);
                        }
                    };
                    document.addEventListener('keydown', handleEsc);
                }
            } else {
                // Es modal VIEW, agregar al body directamente
                document.body.appendChild(modal);
                
                // ⭐ EJECUTAR SCRIPTS DEL MODAL VIEW (CRÍTICO)
                const scripts = tempDiv.querySelectorAll('script');
                console.log(`📜 Ejecutando ${scripts.length} scripts del modal VIEW`);
                scripts.forEach((script, index) => {
                    if (script.textContent && script.textContent.trim()) {
                        try {
                            console.log(`  ✅ Ejecutando script ${index + 1}`);
                            eval(script.textContent);
                        } catch (scriptError) {
                            console.error(`  ❌ Error en script ${index + 1}:`, scriptError);
                        }
                    }
                });
                
                // Agregar clase show después de un momento para activar animación FADE
                console.log('⏳ Esperando 100ms antes de agregar clase .show...');
                setTimeout(() => {
                    // ⚡ REMOVER estilos inline para que CSS tome control
                    modal.style.opacity = '';
                    modal.style.visibility = '';
                    modal.style.pointerEvents = '';
                    
                    modal.classList.add('show');
                    console.log('✅ Clase .show agregada - Modal marca VIEW con FADE IN (1s)');
                    console.log('📊 Clases del modal:', modal.className);
                    console.log('📊 Computed opacity:', window.getComputedStyle(modal).opacity);
                }, 100); // Aumentado a 100ms para asegurar que CSS está completamente cargado
                
                // Agregar event listener para cerrar al hacer click en overlay
                const overlay = modal.querySelector('.marca-view-modal__overlay');
                if (overlay) {
                    overlay.addEventListener('click', closeMarcaModal);
                }
                
                // Agregar listener para ESC
                const handleEsc = (e) => {
                    if (e.key === 'Escape') {
                        closeMarcaModal();
                        document.removeEventListener('keydown', handleEsc);
                    }
                };
                document.addEventListener('keydown', handleEsc);
            }
            
            if (!modal) {
                throw new Error('No se encontró el modal en el HTML');
            }
        })
        .catch(error => {
            console.error('❌ Error cargando modal marca:', error);
            
            // Remover loading
            if (loadingDiv) {
                loadingDiv.remove();
            }
            
            // Desbloquear scroll
            document.body.classList.remove('marca-modal-open');
            
            showNotification('Error al cargar el modal: ' + error.message, 'error');
        });
}

function closeMarcaModal() {
    console.log('🚪 closeMarcaModal() iniciado');
    
    // Buscar el modal VIEW por ID
    let modal = document.getElementById('marca-view-modal');
    let isViewModal = true;
    
    if (!modal) {
        // Buscar modal CREATE/EDIT (dentro del overlay)
        const overlay = document.getElementById('marca-modal-overlay');
        if (overlay) {
            isViewModal = false;
            // Remover clase show del overlay
            overlay.classList.remove('show');
            overlay.classList.add('closing');
            console.log('🎬 Animación de cierre CREATE/EDIT iniciada');
            
            // Desbloquear scroll del body
            document.body.classList.remove('marca-modal-open');
            document.body.classList.remove('modal-open');
            
            // Remover del DOM después de la animación
            setTimeout(() => {
                if (overlay && overlay.parentNode) {
                    overlay.remove();
                    console.log('🗑️ Overlay CREATE/EDIT eliminado del DOM');
                }
            }, 300);
            return;
        }
    }
    
    if (!modal) {
        console.log('❌ No hay modal para cerrar');
        // Desbloquear scroll por si acaso
        document.body.classList.remove('marca-modal-open');
        document.body.classList.remove('modal-open');
        return;
    }
    
    // Es modal VIEW
    // Remover clase show para animación de salida
    modal.classList.remove('show');
    modal.classList.add('closing');
    console.log('🎬 Animación de cierre VIEW iniciada (duración: 0.3s)');
    
    // Desbloquear scroll del body
    document.body.classList.remove('marca-modal-open');
    
    // Remover del DOM después de la animación (0.3s = 300ms)
    setTimeout(() => {
        if (modal && modal.parentNode) {
            modal.remove();
            console.log('✅ Modal marca eliminado del DOM');
        }
    }, 300); // Ajustado a 300ms
}

// ═════════════════════════════════════════════════════════════════════════════

// ===== FUNCIONES DE EXPORTACIÓN Y REPORTES =====
function exportMarcasExcel() {
    console.log('📊 Exportando marcas a Excel...');
    
    // Obtener todas las marcas con los filtros actuales
    const searchInput = document.getElementById('search-marcas');
    const estadoSelect = document.getElementById('filter-estado-marca');
    
    const params = new URLSearchParams({
        action: 'list',
        page: 1,
        limit: 99999 // Obtener todas las marcas
    });
    
    if (searchInput && searchInput.value) {
        params.append('search', searchInput.value);
    }
    
    if (estadoSelect && estadoSelect.value) {
        params.append('status', estadoSelect.value);
    }
    
    fetch(`${MARCAS_CONFIG.apiUrl}?${params}`)
        .then(response => response.json())
        .then(data => {
            if (!data.success || !data.data || data.data.length === 0) {
                showNotification('No hay marcas para exportar', 'warning');
                return;
            }
            
            // Preparar datos para Excel
            const marcasData = data.data.map(cat => ({
                'ID': cat.id_marca,
                'Código': cat.codigo_marca || 'N/A',
                'Nombre': cat.nombre_marca,
                'Descripción': cat.descripcion_marca || 'Sin descripción',
                'Estado': cat.estado_marca === 'activo' ? 'Activo' : 'Inactivo',
                'Fecha Creación': cat.fecha_creacion_formato || formatDate(cat.fecha_creacion_marca)
            }));
            
            // Crear hoja de cálculo
            const worksheet = XLSX.utils.json_to_sheet(marcasData);
            
            // Ajustar anchos de columna
            const colWidths = [
                { wch: 8 },  // ID
                { wch: 15 }, // Código
                { wch: 30 }, // Nombre
                { wch: 50 }, // Descripción
                { wch: 12 }, // Estado
                { wch: 15 }  // Fecha
            ];
            worksheet['!cols'] = colWidths;
            
            // Crear libro de trabajo
            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, worksheet, 'marcas');
            
            // Generar nombre de archivo con fecha
            const fechaHoy = new Date().toISOString().split('T')[0];
            const fileName = `marcas_${fechaHoy}.xlsx`;
            
            // Descargar archivo
            XLSX.writeFile(workbook, fileName);
            
            console.log('✅ Excel exportado:', fileName);
        })
        .catch(error => {
            console.error('❌ Error al exportar Excel:', error);
            showNotification('Error al exportar marcas a Excel', 'error');
        });
}

function showMarcasReport() {
    showNotification('🚧 Función de reporte en desarrollo', 'info');
    // TODO: Implementar reporte de marcas
}

// ===== PAGINACIÓN =====
function updatePaginationMarcas(pagination) {
    if (!pagination) return;
    
    currentPageMarcas = pagination.current_page || 1;
    totalPagesMarcas = pagination.total_pages || 1;
    
    const currentEl = document.getElementById('current-page-marcas');
    const totalEl = document.getElementById('total-pages-marcas');
    
    if (currentEl) currentEl.textContent = currentPageMarcas;
    if (totalEl) totalEl.textContent = totalPagesMarcas;
    
    // Actualizar botones
    const firstBtn = document.getElementById('first-btn-marcas');
    const prevBtn = document.getElementById('prev-btn-marcas');
    const nextBtn = document.getElementById('next-btn-marcas');
    const lastBtn = document.getElementById('last-btn-marcas');
    
    if (firstBtn) firstBtn.disabled = currentPageMarcas <= 1;
    if (prevBtn) prevBtn.disabled = currentPageMarcas <= 1;
    if (nextBtn) nextBtn.disabled = currentPageMarcas >= totalPagesMarcas;
    if (lastBtn) lastBtn.disabled = currentPageMarcas >= totalPagesMarcas;
}

function goToFirstPageMarcas() {
    if (currentPageMarcas > 1) {
        currentPageMarcas = 1;
        loadMarcasData();
    }
}

function previousPageMarcas() {
    if (currentPageMarcas > 1) {
        currentPageMarcas--;
        loadMarcasData();
    }
}

function nextPageMarcas() {
    if (currentPageMarcas < totalPagesMarcas) {
        currentPageMarcas++;
        loadMarcasData();
    }
}

function goToLastPageMarcas() {
    if (currentPageMarcas < totalPagesMarcas) {
        currentPageMarcas = totalPagesMarcas;
        loadMarcasData();
    }
}

// ===== FILTROS Y BÚSQUEDA =====
function handleSearchMarcas() {
    clearTimeout(window.searchmarcasTimeout);
    window.searchmarcasTimeout = setTimeout(() => {
        currentPageMarcas = 1;
        loadMarcasData();
    }, 300);
}

function filterMarcas() {
    currentPageMarcas = 1;
    loadMarcasData();
}

function clearSearchMarcas() {
    const searchInput = document.getElementById('search-marcas');
    if (searchInput) {
        searchInput.value = '';
        loadMarcasData();
    }
}

function clearAllFiltersMarcas() {
    console.log('🧹 Limpiando todos los filtros...');
    
    const searchInput = document.getElementById('search-marcas');
    const estadoSelect = document.getElementById('filter-estado-marca');
    
    // Limpiar valores con animación
    if (searchInput) {
        searchInput.value = '';
        searchInput.classList.add('cleared');
        setTimeout(() => searchInput.classList.remove('cleared'), 300);
    }
    
    if (estadoSelect) {
        estadoSelect.value = '';
        estadoSelect.classList.add('cleared');
        setTimeout(() => estadoSelect.classList.remove('cleared'), 300);
    }
    
    // Resetear página y recargar
    currentPageMarcas = 1;
    loadMarcasData();
    
    console.log('✅ Filtros limpiados');
}
// ===== FUNCIÓN PARA CAMBIAR ESTADO - SIN NOTIFICACIONES =====
async function toggleEstadoMarca(id) {
    try {
        console.log('⚡ Cambiando estado de marca:', id);
        
        // Obtener estado actual
        const response = await fetch(`${MARCAS_CONFIG.apiUrl}?action=get&id=${id}`);
        const result = await response.json();
        
        if (!response.ok || !result.success) {
            console.error('❌ Error al obtener datos de la marca');
            return;
        }
        
        const currentEstado = result.data.estado_marca;
        const newEstado = currentEstado === 'activo' ? 'inactivo' : 'activo';
        
        console.log(`🔄 Cambiando de ${currentEstado} a ${newEstado}`);
        
        // Cambiar estado sin confirmación
        const updateResponse = await fetch(`${MARCAS_CONFIG.apiUrl}?action=change_estado`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `id_marca=${id}&estado=${newEstado}`
        });
        
        const updateResult = await updateResponse.json();
        
        if (updateResponse.ok && updateResult.success) {
            console.log('✅ Estado cambiado exitosamente');
            
            // Actualizar solo esta marca en tiempo real (tabla Y grid)
            const updatedData = {
                ...result.data,
                estado_marca: newEstado
            };
            
            // Actualizar en la lista global
            const index = marcasList.findIndex(m => m.id_marca == id);
            if (index !== -1) {
                marcasList[index] = updatedData;
            }
            
            // Actualizar UI (tabla y grid si está activo)
            await updateSingleMarca(id, updatedData);
            
        } else {
            console.error('❌ Error al cambiar estado:', updateResult.error);
        }
        
    } catch (error) {
        console.error('❌ Error en toggleEstadoMarca:', error.message);
    }
}

// Función para mostrar modal de confirmación de eliminación
function showDeleteConfirmationMarca(marcaId, marcaNombre) {
    console.log('🗑️ showDeleteConfirmationMarca llamada:', marcaId, marcaNombre);
    
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
                <p>Para eliminar la marca <strong>"${marcaNombre}"</strong>, escribe la palabra <strong>"eliminar"</strong> en el campo de abajo:</p>
                
                <input type="text" id="deleteConfirmInput" class="confirmation-input" placeholder="Escribe 'eliminar' para confirmar" autocomplete="off">
                <div id="deleteError" class="delete-error">
                    Por favor escribe exactamente "eliminar" para confirmar
                </div>
            </div>
            <div class="delete-modal-footer">
                <button type="button" class="btn-cancel-delete" onclick="closeDeleteConfirmationMarca()">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn-confirm-delete" onclick="confirmDeleteMarca(${marcaId}, '${marcaNombre.replace(/'/g, "\\'")}')">
                    <i class="fas fa-trash"></i> Eliminar marca
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
        .confirmation-input.invalid {
            border-color: #dc2626 !important;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.15) !important;
        }
        .confirmation-input.valid {
            border-color: #10b981 !important;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15) !important;
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
                confirmDeleteMarca(marcaId, marcaNombre);
            }
        });
    }
    
    // Permitir cerrar con ESC
    const handleEsc = (e) => {
        if (e.key === 'Escape') {
            closeDeleteConfirmationMarca();
            document.removeEventListener('keydown', handleEsc);
        }
    };
    document.addEventListener('keydown', handleEsc);
    
    // Cerrar al hacer click en el overlay (fondo oscuro)
    overlay.addEventListener('click', function(e) {
        // Solo cerrar si se hace click directamente en el overlay, no en el modal
        if (e.target === overlay) {
            console.log('👆 Click en overlay detectado - cerrando modal');
            closeDeleteConfirmationMarca();
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
function closeDeleteConfirmationMarca() {
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


// Función para confirmar eliminación
async function confirmDeleteMarca(marcaId, marcaNombre) {
    const input = document.getElementById('deleteConfirmInput');
    const errorDiv = document.getElementById('deleteError');
    
    if (!input) return;
    
    const confirmText = input.value.trim().toLowerCase();
    
    if (confirmText !== 'eliminar') {
        console.log('❌ Texto de confirmación incorrecto:', confirmText);
        if (errorDiv) {
            errorDiv.style.display = 'block';
        }
        input.classList.add('invalid');
        input.focus();
        return;
    }
    
    // Validación correcta
    input.classList.remove('invalid');
    input.classList.add('valid');
    
    console.log('✅ Confirmación válida, eliminando marca:', marcaId);
    
    try {
        const response = await fetch(`${MARCAS_CONFIG.apiUrl}?action=delete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `id=${marcaId}`
        });
        
        const result = await response.json();
        
        if (response.ok && result.success) {
            console.log('✅ Marca eliminada exitosamente');
            closeDeleteConfirmationMarca();
            loadMarcasData();
        } else {
            console.error('❌ Error al eliminar:', result.error);
            if (errorDiv) {
                errorDiv.textContent = result.error || 'Error al eliminar la marca';
                errorDiv.style.display = 'block';
            }
        }
    } catch (error) {
        console.error('❌ Error de red:', error);
        if (errorDiv) {
            errorDiv.textContent = 'Error de conexión. Intenta nuevamente.';
            errorDiv.style.display = 'block';
        }
    }
}

// ===== FUNCIÓN DE DESTRUCCIÓN =====
window.destroyMarcasModule = function() {
    console.log('🗑️ [MARCAS] Destruyendo módulo...');
    
    try {
        // 1. Limpiar variables globales
        marcasList = [];
        currentPageMarcas = 1;
        totalPagesMarcas = 1;
        isLoadingMarcas = false;
        
        // 2. Resetear bandera
        window.marcasModuleInitialized = false;
        
        // 3. Limpiar timeouts
        if (window.searchmarcasTimeout) {
            clearTimeout(window.searchmarcasTimeout);
        }
        
        // 4. RESETEAR VISTA A TABLA (estado inicial)
        console.log('🔄 [MARCAS] Reseteando vista a tabla...');
        
        // Asegurar que grid esté oculto (no lo eliminamos porque viene en el HTML)
        const gridContainer = document.querySelector('.marcas-grid');
        if (gridContainer) {
            gridContainer.style.display = 'none';
            gridContainer.innerHTML = ''; // Limpiar contenido
        }
        
        // Asegurar que tabla esté visible
        const tableWrapper = document.getElementById('marcas-table-wrapper');
        if (tableWrapper) {
            tableWrapper.style.display = 'block';
        }
        
        // Resetear botones de vista
        const viewButtons = document.querySelectorAll('.view-btn');
        viewButtons.forEach(btn => {
            btn.classList.remove('active');
            if (btn.dataset.view === 'table') {
                btn.classList.add('active');
            }
        });
        
        // 5. Limpiar localStorage
        try {
            localStorage.removeItem('marcas_view_mode');
        } catch (e) {
            console.warn('No se pudo limpiar localStorage:', e);
        }
        
        // 6. Cerrar menú flotante de acciones si está abierto
        if (typeof mar_floatingContainer !== 'undefined' && mar_floatingContainer) {
            if (mar_floatingContainer.cleanup) {
                mar_floatingContainer.cleanup();
            }
            mar_floatingContainer.remove();
            mar_floatingContainer = null;
            mar_centerButton = null;
            mar_floatingButtons = [];
            mar_activeMarcaId = null;
            mar_isAnimating = false;
        }
        
        // Limpiar timeouts de animación
        if (typeof mar_animationTimeout !== 'undefined' && mar_animationTimeout) {
            clearTimeout(mar_animationTimeout);
            mar_animationTimeout = null;
        }
        
        // Limpiar tooltips
        if (typeof hideTooltipMarca === 'function') {
            hideTooltipMarca();
        }
        
        // 7. Limpiar modales y overlays de marcas
        const modals = document.querySelectorAll('[id*="marca-modal"], .marca-modal, .delete-confirmation-overlay');
        modals.forEach(modal => {
            if (modal && modal.parentNode) {
                modal.remove();
            }
        });
        
        console.log('✅ [MARCAS] Vista reseteada a tabla');
        console.log('✅ [MARCAS] Módulo destruido correctamente');
        
    } catch (error) {
        console.error('❌ [MARCAS] Error al destruir módulo:', error);
    }
};

// ===== EXPONER FUNCIONES GLOBALMENTE =====
window.initializeMarcasModule = initializeMarcasModule;
window.loadMarcasData = loadMarcasData;
window.toggleViewMarcas = toggleViewMarcas;
window.handleSearchMarcas = handleSearchMarcas;
window.filterMarcas = filterMarcas;
window.clearSearchMarcas = clearSearchMarcas;
window.clearAllFiltersMarcas = clearAllFiltersMarcas;
window.showCreateMarcaModal = showCreateMarcaModal;
window.showEditMarcaModal = showEditMarcaModal;
window.showViewMarcaModal = showViewMarcaModal;
window.closeMarcaModal = closeMarcaModal; // ⭐ Función para cerrar modal
window.openMarcaModal = openMarcaModal; // ⭐ Función para abrir modal
window.showActionMenuMarca = showActionMenuMarca; // ⭐ Función principal del menú flotante
window.viewMarcaAction = viewMarcaAction;
window.editMarcaAction = editMarcaAction;
window.changeMarcaEstadoAction = changeMarcaEstadoAction;
window.deleteMarcaAction = deleteMarcaAction;
window.confirmDeleteMarca = confirmDeleteMarca;
window.toggleEstadoMarca = toggleEstadoMarca; // ⭐ Para botones del grid
window.deleteMarca = deleteMarca; // ⭐ Para botones del grid (CORREGIDO: deleteMarca con M mayúscula)
window.showDeleteConfirmationMarca = showDeleteConfirmationMarca; // ⭐ Modal de confirmación (CON estilos)
window.closeDeleteConfirmationMarca = closeDeleteConfirmationMarca; // ⭐ Cerrar modal
window.goToFirstPageMarcas = goToFirstPageMarcas;
window.previousPageMarcas = previousPageMarcas;
window.nextPageMarcas = nextPageMarcas;
window.goToLastPageMarcas = goToLastPageMarcas;
window.exportMarcasExcel = exportMarcasExcel;
window.showMarcasReport = showMarcasReport;
window.updateSingleMarca = updateSingleMarca; // ⭐ Actualización en tiempo real

console.log('✅ [MARCAS] Funciones expuestas globalmente');

// ===== AUTO-INICIALIZACIÓN =====
setTimeout(() => {
    console.log('🚀 [MARCAS] Auto-inicializando...');
    initializeMarcasModule();
}, 50);

</script>

<style>
/* Estilos específicos para marcas */
.marcas-grid .grid-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    overflow: hidden;
    transition: all 0.2s;
}

.marcas-grid .grid-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.marcas-grid .card-image {
    position: relative;
    height: 180px;
    overflow: hidden;
    background: rgba(255, 255, 255, 0.05);
    display: flex;
    align-items: center;
    justify-content: center;
}

.marcas-grid .card-image img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.marcas-grid .card-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    padding: 4px 12px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    color: white;
}

.marcas-grid .card-body {
    padding: 16px;
}

.marcas-grid .card-body h4 {
    margin: 0 0 8px;
    font-size: 16px;
    font-weight: 600;
    color: #1f2937;
}

.marcas-grid .card-code {
    font-size: 13px;
    color: #6b7280;
    margin-bottom: 8px;
    font-family: monospace;
}

.marcas-grid .card-description {
    font-size: 14px;
    color: #6b7280;
    line-height: 1.5;
}

.marcas-grid .card-actions {
    padding: 12px;
    border-top: 1px solid #e5e7eb;
    display: flex;
    gap: 8px;
    justify-content: center;
}

.table-image {
    width: 60px;
    height: 60px;
    object-fit: contain;
    border-radius: 6px;
    background: rgba(255, 255, 255, 0.05);
}

/* ═══════════════════════════════════════════════════════════════════════
   ESTILOS DE MODALES - Overlay simple (el CSS real está en marca-view-modal.css)
   ═══════════════════════════════════════════════════════════════════════ */

/* Modal Overlay - Solo estructura básica */
.modal-overlay-marca {
    /* El overlay lo maneja marca-view-modal.css */
    display: none !important;
}

/* Bloquear scroll cuando modal está abierto */
body.marca-modal-open {
    overflow: hidden !important;
}

/* Estilos específicos para imágenes de marcas en tablas */
.admin-marcas-module .product-image-small {
    background: rgba(255, 255, 255, 0.05);
}

.admin-marcas-module .product-image-small img {
    object-fit: contain !important;
}

</style>

<script>
// ═══════════════════════════════════════════════════════════════════════
// FUNCIÓN GLOBAL: MOSTRAR IMAGEN EN TAMAÑO COMPLETO
// ═══════════════════════════════════════════════════════════════════════
function showImageFullSize(imageUrl, categoryName) {
    console.log('🖼️ Mostrando imagen en tamaño real:', imageUrl);
    
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
    img.alt = categoryName || 'marca';
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
        img.src = 'public/assets/img/default-category.jpg';
    };
}

// Hacer la función global
window.showImageFullSize = showImageFullSize;

// ═══════════════════════════════════════════════════════════════════════
// FUNCIONES DEL MODAL DE FILTROS (MÓVIL)
// ═══════════════════════════════════════════════════════════════════════

// Toggle del modal de filtros
window.toggleFiltersModalMarcas = function() {
    const modal = document.getElementById('filters-modal-marcas');
    const isVisible = modal.style.display === 'flex';
    
    if (isVisible) {
        closeFiltersModalMarcas();
    } else {
        // Sincronizar filtros del desktop al modal
        syncFiltersToModalMarcas();
        modal.style.display = 'flex';
        setTimeout(() => modal.classList.add('active'), 10);
    }
};

// Cerrar modal de filtros
window.closeFiltersModalMarcas = function() {
    const modal = document.getElementById('filters-modal-marcas');
    modal.classList.remove('active');
    setTimeout(() => modal.style.display = 'none', 300);
};

// Sincronizar filtros del desktop al modal
window.syncFiltersToModalMarcas = function() {
    // Búsqueda
    const desktopSearch = document.getElementById('search-marcas');
    const modalSearch = document.getElementById('modal-search-marcas');
    if (desktopSearch && modalSearch) {
        modalSearch.value = desktopSearch.value;
    }
    
    // Estado
    const desktopEstado = document.getElementById('filter-estado-marca');
    const modalEstado = document.getElementById('modal-filter-estado-marca');
    if (desktopEstado && modalEstado) {
        modalEstado.value = desktopEstado.value;
    }
    
    // Fecha
    const desktopFecha = document.getElementById('filter-fecha-marca');
    const modalFecha = document.getElementById('modal-filter-fecha-marca');
    if (desktopFecha && modalFecha) {
        modalFecha.value = desktopFecha.value;
    }
};

// Sincronizar filtros del modal al desktop
window.syncFiltersFromModalMarcas = function() {
    // Búsqueda
    const modalSearch = document.getElementById('modal-search-marcas');
    const desktopSearch = document.getElementById('search-marcas');
    if (modalSearch && desktopSearch) {
        desktopSearch.value = modalSearch.value;
    }
    
    // Estado
    const modalEstado = document.getElementById('modal-filter-estado-marca');
    const desktopEstado = document.getElementById('filter-estado-marca');
    if (modalEstado && desktopEstado) {
        desktopEstado.value = modalEstado.value;
    }
    
    // Fecha
    const modalFecha = document.getElementById('modal-filter-fecha-marca');
    const desktopFecha = document.getElementById('filter-fecha-marca');
    if (modalFecha && desktopFecha) {
        desktopFecha.value = modalFecha.value;
    }
};

// Aplicar filtros desde el modal
window.applyFiltersModalMarcas = function() {
    syncFiltersFromModalMarcas();
    filterMarcas();
    closeFiltersModalMarcas();
};

// Limpiar filtros desde el modal
window.clearAllFiltersModalMarcas = function() {
    // Limpiar campos del modal
    document.getElementById('modal-search-marcas').value = '';
    document.getElementById('modal-filter-estado-marca').value = '';
    document.getElementById('modal-filter-fecha-marca').value = '';
    
    // Sincronizar y aplicar
    syncFiltersFromModalMarcas();
    clearAllFiltersMarcas();
    closeFiltersModalMarcas();
};

// Detectar si es móvil y mostrar/ocultar elementos
function detectMobileViewMarcas() {
    const isMobile = window.innerWidth <= 768;
    const filterBtn = document.getElementById('mobile-filters-btn');
    const desktopFilters = document.querySelector('.module-filters');
    
    if (filterBtn) {
        filterBtn.style.display = isMobile ? 'flex' : 'none';
    }
    
    if (desktopFilters) {
        desktopFilters.style.display = isMobile ? 'none' : 'block';
    }
    
    // Auto-cambiar a vista grid en móvil
    if (isMobile) {
        const gridBtn = document.querySelector('.view-btn[data-view="grid"]');
        if (gridBtn && !gridBtn.classList.contains('active')) {
            toggleViewMarcas('grid');
        }
    }
}

// Ejecutar al cargar y al redimensionar
window.addEventListener('load', detectMobileViewMarcas);
window.addEventListener('resize', detectMobileViewMarcas);

</script>

