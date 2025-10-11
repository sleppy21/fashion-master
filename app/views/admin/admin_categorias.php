<?php
/**
 * VISTA DE GESTIÓN DE CATEGORÍAS - DESDE CERO
 * Módulo limpio y simple basado en productos
 */
?>

<div class="admin-module admin-categorias-module">
    <!-- Header del módulo -->
    <div class="module-header">
        <div class="module-title">
            <div class="module-icon">
                <i class="fas fa-tags"></i>
            </div>
            <div class="module-info">
                <h2 class="module-name">Gestión de Categorías</h2>
                <p class="module-description">Administra las categorías de productos</p>
            </div>
        </div>
        <div class="module-actions">
            <button class="btn-modern btn-primary" onclick="showCreateCategoriaModal()" style="color: white !important;">
                <i class="fas fa-plus" style="color: white !important;"></i>
                <span style="color: white !important;">Nuevo <span class="btn-text-mobile-hide">Categoría</span></span>
            </button>
            <button class="btn-modern btn-secondary" onclick="exportCategoriasExcel()" style="color: white !important;">
                <i class="fas fa-download" style="color: white !important;"></i>
                <span style="color: white !important;">Exportar <span class="btn-text-mobile-hide">Excel</span></span>
            </button>
            <button class="btn-modern btn-info" onclick="showCategoriasReport()" style="color: white !important;">
                <i class="fas fa-chart-bar" style="color: white !important;"></i>
                <span style="color: white !important;">Reporte <span class="btn-text-mobile-hide">Stock</span></span>
            </button>
        </div>
    </div>

    <!-- Filtros y búsqueda -->
    <div class="module-filters">
        <div class="search-container">
            <div class="search-input-group">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="search-categorias" class="search-input" 
                       placeholder="Buscar categorías..." oninput="handleSearchCategorias()">
                <button class="search-clear" onclick="clearSearchCategorias()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        
        <div class="filters-grid">
            <div class="filter-group">
                <label class="filter-label">Estado</label>
                <select id="filter-estado-categoria" class="filter-select" onchange="filterCategorias()">
                    <option value="">Todos los estados</option>
                    <option value="activo">Activo</option>
                    <option value="inactivo">Inactivo</option>
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label">Fecha</label>
                <select id="filter-fecha-categoria" class="filter-select" onchange="loadCategoriasData()">
                    <option value="">Todas las fechas</option>
                    <!-- Se cargan dinámicamente -->
                </select>
            </div>
            <div class="filter-group">
                <button class="btn-modern btn-outline" onclick="clearAllFiltersCategorias()">
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
                        Mostrando <span id="showing-end-categorias">0</span> 
                        de <span id="total-categorias">0</span> categorías
                    </span>
                </div>
                <div class="table-actions">
                    <div class="view-options">
                        <button class="view-btn active" data-view="table" onclick="toggleViewCategorias('table')">
                            <i class="fas fa-table"></i>
                        </button>
                        <button class="view-btn" data-view="grid" onclick="toggleViewCategorias('grid')">
                            <i class="fas fa-th"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="data-table-wrapper scrollable-table" id="categorias-table-wrapper">
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
                    <tbody id="categorias-table-body" class="table-body">
                        <tr class="loading-row">
                            <td colspan="8" class="loading-cell">
                                <div class="loading-content">
                                    <div class="spinner"></div>
                                    <span>Cargando categorías...</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Vista Grid (oculta por defecto, se muestra en móvil) -->
            <div class="categorias-grid" id="categorias-grid">
                <!-- Las cards se generan dinámicamente -->
            </div>

            <!-- Paginación -->
            <div class="pagination-container">
                <div class="pagination-info">
                    <span class="pagination-text">
                        Página <span id="current-page-categorias">1</span> de <span id="total-pages-categorias">1</span>
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
                        <!-- Números de página dinámicos -->
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

    <!-- Botón flotante de filtros (solo móvil) -->
    <button class="mobile-filter-btn" id="mobile-filters-btn" onclick="window.toggleFiltersModalCategorias()" style="display: none;">
        <i class="fas fa-filter"></i>
    </button>

    <!-- Modal de filtros (solo móvil) -->
    <div class="filters-modal" id="filters-modal-categorias" style="display: none;">
        <div class="filters-modal-content">
            <div class="filters-modal-header">
                <h3 class="filters-modal-title"><i class="fas fa-filter"></i> Filtros</h3>
                <button class="filters-modal-close" onclick="window.closeFiltersModalCategorias()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="filters-modal-body">
                <!-- Búsqueda -->
                <div class="modal-search-container">
                    <div class="modal-search-input-group">
                        <i class="fas fa-search modal-search-icon"></i>
                        <input type="text" id="modal-search-categorias" class="modal-search-input" 
                               placeholder="Buscar categorías...">
                    </div>
                </div>

                <!-- Filtros Grid -->
                <div class="modal-filters-grid">
                    <!-- Estado -->
                    <div class="modal-filter-group">
                        <label class="modal-filter-label">
                            <i class="fas fa-toggle-on"></i> Estado
                        </label>
                        <select id="modal-filter-estado-categoria" class="modal-filter-select">
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
                        <select id="modal-filter-fecha-categoria" class="modal-filter-select">
                            <option value="">Todas las fechas</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="modal-filter-actions">
                <button class="btn-modern btn-outline" onclick="window.clearAllFiltersModalCategorias()">
                    <i class="fas fa-times"></i> Limpiar
                </button>
                <button class="btn-modern btn-primary" onclick="window.applyFiltersModalCategorias()">
                    <i class="fas fa-check"></i> Aplicar
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* ===== FORZAR COLOR BLANCO EN BOTONES DEL HEADER - CATEGORÍAS ===== */
.admin-categorias-module .module-actions .btn-modern,
.admin-categorias-module .module-actions .btn-modern.btn-primary,
.admin-categorias-module .module-actions .btn-modern.btn-secondary,
.admin-categorias-module .module-actions .btn-modern.btn-info,
.admin-categorias-module .module-actions button {
    color: #ffffff !important;
}

.admin-categorias-module .module-actions .btn-modern i,
.admin-categorias-module .module-actions .btn-modern span,
.admin-categorias-module .module-actions .btn-modern.btn-primary i,
.admin-categorias-module .module-actions .btn-modern.btn-primary span,
.admin-categorias-module .module-actions .btn-modern.btn-secondary i,
.admin-categorias-module .module-actions .btn-modern.btn-secondary span,
.admin-categorias-module .module-actions .btn-modern.btn-info i,
.admin-categorias-module .module-actions .btn-modern.btn-info span,
.admin-categorias-module .module-actions button i,
.admin-categorias-module .module-actions button span {
    color: #ffffff !important;
}

@media (max-width: 768px) {
    .admin-categorias-module .module-actions .btn-modern,
    .admin-categorias-module .module-actions .btn-modern *,
    .admin-categorias-module .module-actions button,
    .admin-categorias-module .module-actions button * {
        color: #ffffff !important;
    }
}
</style>

<script>
// ═══════════════════════════════════════════════════════════════════════
// MÓDULO DE CATEGORÍAS - COMPLETAMENTE NUEVO Y LIMPIO
// ═══════════════════════════════════════════════════════════════════════

console.log('🏷️ [CATEGORIAS] Módulo iniciando...');

// ===== CONFIGURACIÓN =====
const CATEGORIAS_CONFIG = {
    apiUrl: '/fashion-master/app/controllers/CategoryController.php',
    itemsPerPage: 10
};

// ===== VARIABLES GLOBALES DEL MÓDULO =====
let categoriasList = [];
let currentPageCategorias = 1;
let totalPagesCategorias = 1;
let isLoadingCategorias = false;

// ===== BANDERA DE INICIALIZACIÓN =====
window.categoriasModuleInitialized = window.categoriasModuleInitialized || false;

// ===== FUNCIÓN DE INICIALIZACIÓN =====
function initializeCategoriasModule() {
    console.log('✅ [CATEGORIAS] Iniciando módulo...');
    
    // Evitar doble inicialización
    if (window.categoriasModuleInitialized) {
        console.log('⚠️ [CATEGORIAS] Módulo ya inicializado');
        return;
    }
    
    // Detectar si es móvil y cambiar a vista grid INSTANTÁNEAMENTE
    const isMobile = window.innerWidth <= 768;
    if (isMobile) {
        console.log('📱 Dispositivo móvil detectado, cambiando a vista grid instantáneamente');
        
        // Cambiar botones activos
        const viewButtons = document.querySelectorAll('.view-btn');
        viewButtons.forEach(btn => {
            btn.classList.remove('active');
            if (btn.dataset.view === 'grid') {
                btn.classList.add('active');
            }
        });
        
        // Ocultar tabla inmediatamente
        const tableWrapper = document.getElementById('categorias-table-wrapper');
        if (tableWrapper) {
            tableWrapper.style.display = 'none';
        }
        
        // Crear grid container si no existe
        let gridContainer = document.querySelector('.products-grid');
        if (!gridContainer) {
            createCategoriasGridContainer();
        }
        
        // Mostrar grid inmediatamente
        gridContainer = document.querySelector('.products-grid');
        if (gridContainer) {
            gridContainer.style.display = 'grid';
        }
    }
    
    // Cargar datos
    loadCategoriasData();
    
    // Marcar como inicializado
    window.categoriasModuleInitialized = true;
}

// ===== CARGAR DATOS =====
async function loadCategoriasData() {
    if (isLoadingCategorias) return;
    
    isLoadingCategorias = true;
    console.log('📥 [CATEGORIAS] Cargando datos...');
    
    try {
        const params = new URLSearchParams({
            action: 'list',
            page: currentPageCategorias,
            limit: CATEGORIAS_CONFIG.itemsPerPage
        });
        
        // Agregar filtros
        const searchInput = document.getElementById('search-categorias');
        if (searchInput && searchInput.value) {
            params.append('search', searchInput.value);
            console.log('🔍 Búsqueda:', searchInput.value);
        }
        
        const estadoSelect = document.getElementById('filter-estado-categoria');
        if (estadoSelect && estadoSelect.value) {
            params.append('status', estadoSelect.value); // CORREGIDO: usar 'status' en vez de 'estado'
            console.log('🔽 Filtro estado:', estadoSelect.value);
        }
        
        const fechaSelect = document.getElementById('filter-fecha-categoria');
        if (fechaSelect && fechaSelect.value) {
            params.append('fecha', fechaSelect.value);
            console.log('📅 Filtro fecha:', fechaSelect.value);
        }
        
        console.log('🌐 [DEBUG] URL completa:', `${CATEGORIAS_CONFIG.apiUrl}?${params}`);
        
        const response = await fetch(`${CATEGORIAS_CONFIG.apiUrl}?${params}`);
        const data = await response.json();
        
        if (data.success) {
            categoriasList = data.data || [];
            console.log('✅ [CATEGORIAS] Datos cargados:', categoriasList.length);
            
            // 🔍 DEBUG: Mostrar información del filtro
            if (data.debug) {
                console.log('🔍 [DEBUG] WHERE clause:', data.debug.where_clause);
                console.log('🔍 [DEBUG] Params:', data.debug.params);
                console.log('🔍 [DEBUG] Fecha filter:', data.debug.fecha_filter);
            }
            
            // Cargar fechas únicas en el filtro solo si no hay filtro activo
            // Para evitar que se reconstruya el select con datos filtrados
            const fechaSelect = document.getElementById('filter-fecha-categoria');
            const hasFechaFilter = fechaSelect && fechaSelect.value;
            if (!hasFechaFilter) {
                loadCategoriasDates(categoriasList);
            }
            
            // Actualizar UI
            displayCategorias(categoriasList);
            updatePaginationCategorias(data.pagination);
            updateStatsCategorias(data.pagination);
        } else {
            throw new Error(data.error || 'Error al cargar categorías');
        }
        
    } catch (error) {
        console.error('❌ [CATEGORIAS] Error:', error);
        showErrorCategorias(error.message);
    } finally {
        isLoadingCategorias = false;
    }
}

// ===== MOSTRAR CATEGORÍAS EN TABLA - DISEÑO EXACTO DE PRODUCTOS =====
function displayCategorias(categorias) {
    const tbody = document.getElementById('categorias-table-body');
    if (!tbody) return;
    
    // Mensaje cuando no hay categorías
    if (!categorias || categorias.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="loading-cell">
                    <div class="loading-content no-data">
                        <i class="fas fa-tags"></i>
                        <span>No se encontraron categorías</span>
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    // Renderizar filas con estructura EXACTA de productos
    tbody.innerHTML = categorias.map((cat, index) => {
        const imageUrl = cat.url_imagen_categoria || '/fashion-master/public/assets/img/default-product.jpg';
        const isActive = cat.estado_categoria === 'activo';
        
        // Formatear fecha
        const fechaFormateada = cat.fecha_creacion_formato || 
                               cat.fecha_registro_categoria || 
                               cat.fecha_creacion_categoria ||
                               formatDate(cat.fecha_registro_categoria) ||
                               formatDate(cat.fecha_creacion_categoria);
        
        return `
        <tr oncontextmenu="return false;" ondblclick="editarCategoria(${cat.id_categoria})" style="cursor: pointer;" data-categoria-id="${cat.id_categoria}">
            <td>${cat.id_categoria}</td>
            <td onclick="event.stopPropagation();" ondblclick="event.stopPropagation(); showImageFullSize('${imageUrl}', '${(cat.nombre_categoria || '').replace(/'/g, "\\'")}');" style="cursor: zoom-in;">
                <div class="product-image-small">
                    <img src="${imageUrl}" 
                         alt="${cat.nombre_categoria}" 
                         onerror="this.src='/fashion-master/public/assets/img/default-product.jpg'">
                </div>
            </td>
            <td>
                <div class="product-info">
                    <strong>${cat.nombre_categoria}</strong>
                </div>
            </td>
            <td>
                <code>${cat.codigo_categoria || 'N/A'}</code>
            </td>
            <td>${truncateText(cat.descripcion_categoria || 'Sin descripción', 50)}</td>
            <td>
                <span class="status-badge ${isActive ? 'status-active' : 'status-inactive'}">
                    ${isActive ? 'Activo' : 'Inactivo'}
                </span>
            </td>
            <td>${fechaFormateada || 'N/A'}</td>
            <td>
                <div class="action-buttons">
                    <button class="btn-action btn-menu" onclick="event.stopPropagation(); showActionMenuCategoria(${cat.id_categoria}, '${(cat.nombre_categoria || '').replace(/'/g, "\\'")}', '${cat.estado_categoria}', event)">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                </div>
            </td>
        </tr>`;
    }).join('');
    
    // ⭐ ACTUALIZAR GRID SI ESTÁ ACTIVO
    const gridContainer = document.querySelector('.categorias-grid');
    const activeBtn = document.querySelector('.view-btn.active');
    
    if (gridContainer && activeBtn && activeBtn.dataset.view === 'grid') {
        displayCategoriasGrid(categorias);
    }
}

// ===== ACTUALIZAR UNA SOLA CATEGORÍA EN TIEMPO REAL =====
async function updateSingleCategoria(categoriaId, updatedData = null) {
    console.log('🔄 === INICIANDO updateSingleCategoria ===');
    console.log('🆔 ID recibido:', categoriaId);
    console.log('📦 Datos recibidos:', updatedData);
    
    try {
        // Si no se proporciona data, obtenerla del servidor
        if (!updatedData) {
            console.log('⬇️ Obteniendo datos del servidor...');
            const response = await fetch(`${CATEGORIAS_CONFIG.apiUrl}?action=get&id=${categoriaId}`);
            const result = await response.json();
            
            if (!result.success) {
                throw new Error('Error al obtener datos de la categoría');
            }
            
            updatedData = result.data;
            console.log('📥 Datos obtenidos del servidor:', updatedData);
        }
        
        // VERIFICAR DATOS ANTES DE ACTUALIZAR
        console.log('🔍 Verificando datos para actualizar:');
        console.log('  - ID:', updatedData.id_categoria);
        console.log('  - Código:', updatedData.codigo_categoria);
        console.log('  - Nombre:', updatedData.nombre_categoria);
        console.log('  - Estado:', updatedData.estado_categoria);
        
        // Buscar la fila en la tabla
        const selector = `#categorias-table-body tr[data-categoria-id="${categoriaId}"]`;
        console.log('🔎 Buscando fila con selector:', selector);
        
        const row = document.querySelector(selector);
        
        if (!row) {
            console.warn('⚠️ Fila no encontrada en tabla');
            console.log('📋 Filas disponibles en la tabla:');
            const allRows = document.querySelectorAll('#categorias-table-body tr[data-categoria-id]');
            allRows.forEach(r => {
                console.log('  - Fila con ID:', r.getAttribute('data-categoria-id'));
            });
            console.log('🔄 Recargando tabla completa como fallback...');
            if (typeof loadCategoriasData === 'function') {
                loadCategoriasData();
            }
            return;
        }
        
        console.log('✅ Fila encontrada, actualizando contenido...');
        
        // Crear nueva fila HTML
        const imageUrl = updatedData.url_imagen_categoria || '/fashion-master/public/assets/img/default-product.jpg';
        const isActive = updatedData.estado_categoria === 'activo';
        const fechaFormateada = updatedData.fecha_creacion_formato || 
                               formatDate(updatedData.fecha_creacion_categoria) || 'N/A';
        
        const newRowHTML = `
            <td>${updatedData.id_categoria}</td>
            <td onclick="event.stopPropagation();" style="cursor: zoom-in;">
                <div class="product-image-small">
                    <img src="${imageUrl}" 
                         alt="${updatedData.nombre_categoria}" 
                         onerror="this.src='/fashion-master/public/assets/img/default-product.jpg'">
                </div>
            </td>
            <td>
                <div class="product-info">
                    <strong>${updatedData.nombre_categoria}</strong>
                </div>
            </td>
            <td>
                <code>${updatedData.codigo_categoria || 'N/A'}</code>
            </td>
            <td>${truncateText(updatedData.descripcion_categoria || 'Sin descripción', 50)}</td>
            <td>
                <span class="status-badge ${isActive ? 'status-active' : 'status-inactive'}">
                    ${isActive ? 'Activo' : 'Inactivo'}
                </span>
            </td>
            <td>${fechaFormateada}</td>
            <td>
                <div class="action-buttons">
                    <button class="btn-action btn-menu" onclick="event.stopPropagation(); showActionMenuCategoria(${updatedData.id_categoria}, '${(updatedData.nombre_categoria || '').replace(/'/g, "\\'")}', '${updatedData.estado_categoria}', event)">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                </div>
            </td>
        `;
        
        // Actualizar contenido de la fila
        row.innerHTML = newRowHTML;
        row.setAttribute('ondblclick', `editarCategoria(${categoriaId})`);
        row.setAttribute('data-categoria-id', categoriaId);
        
        // Actualizar evento de doble click en la imagen
        const imgCell = row.querySelector('td:nth-child(2)');
        if (imgCell) {
            imgCell.setAttribute('ondblclick', `event.stopPropagation(); showImageFullSize('${imageUrl}', '${(updatedData.nombre_categoria || '').replace(/'/g, "\\'")}');`);
        }
        
        console.log('✅ HTML de fila actualizado');
        
        // ⭐ SIN EFECTOS VISUALES - Solo actualización silenciosa
        
        console.log('✅ === updateSingleCategoria COMPLETADO ===');
        
    } catch (error) {
        console.error('❌ Error actualizando categoría:', error);
        console.error('📍 Stack:', error.stack);
        // Fallback: recargar toda la tabla
        if (typeof loadCategoriasData === 'function') {
            console.log('🔄 Recargando tabla completa por error...');
            loadCategoriasData();
        }
    }
}

// ===== VISTA GRID - DISEÑO EXACTO DE PRODUCTOS =====

// Función para alternar entre tabla y grid
function toggleViewCategorias(viewType) {
    console.log('🔄 [CATEGORIAS] Cambiando vista a:', viewType);
    
    const tableWrapper = document.getElementById('categorias-table-wrapper');
    let gridContainer = document.querySelector('.categorias-grid'); // Usar clase correcta
    
    // ⭐ CERRAR BURBUJAS FLOTANTES INSTANTÁNEAMENTE (SIN ANIMACIÓN)
    if (cat_floatingContainer) {
        console.log('🗑️ [CATEGORIAS] Cerrando burbuja flotante INSTANTÁNEAMENTE');
        
        // Limpiar timeouts
        if (cat_animationTimeout) {
            clearTimeout(cat_animationTimeout);
            cat_animationTimeout = null;
        }
        
        // Eliminar tooltip si existe
        hideTooltipCategoria();
        
        // Eliminar contenedor SIN animación
        if (cat_floatingContainer.cleanup) {
            cat_floatingContainer.cleanup();
        }
        cat_floatingContainer.remove();
        
        // Resetear variables globales
        cat_floatingContainer = null;
        cat_centerButton = null;
        cat_floatingButtons = [];
        cat_activeCategoriaId = null;
        cat_isAnimating = false;
        
        console.log('✅ [CATEGORIAS] Burbuja eliminada instantáneamente');
    }
    
    // ⭐ LIMPIEZA ADICIONAL: Eliminar cualquier contenedor huérfano
    const orphanedContainers = document.querySelectorAll('.animated-floating-container');
    if (orphanedContainers.length > 0) {
        console.log(`🧹 [CATEGORIAS] Limpiando ${orphanedContainers.length} contenedor(es) huérfano(s)`);
        orphanedContainers.forEach(container => container.remove());
    }
    
    // El grid ya existe en el HTML, solo necesitamos mostrarlo
    if (!gridContainer) {
        console.error('❌ No se encontró .categorias-grid');
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
        displayCategoriasGrid(categoriasList);
    } else {
        tableWrapper.style.display = 'block';
        if (gridContainer) {
            gridContainer.style.display = 'none';
        }
    }
    
    // Guardar preferencia
    try {
        localStorage.setItem('categorias_view_mode', viewType);
    } catch (e) {
        console.warn('No se pudo guardar preferencia de vista:', e);
    }
}

// Crear contenedor grid con clases exactas de productos
function createCategoriasGridContainer() {
    console.log('🏗️ [CATEGORIAS] Creando contenedor grid...');
    
    const tableContainer = document.querySelector('.data-table-container');
    if (!tableContainer) {
        console.error('❌ No se encontró .data-table-container');
        return null;
    }
    
    // Crear grid container usando las mismas clases que productos
    const gridContainer = document.createElement('div');
    gridContainer.className = 'products-grid'; // ⭐ Usar clase de productos para CSS global
    gridContainer.style.display = 'none'; // Oculto inicialmente
    
    // Insertar después del wrapper de tabla (más simple y robusto)
    const tableWrapper = document.getElementById('categorias-table-wrapper');
    if (tableWrapper) {
        // Insertar justo después de la tabla
        tableWrapper.insertAdjacentElement('afterend', gridContainer);
        console.log('✅ [CATEGORIAS] Grid insertado después de tabla');
    } else {
        // Fallback: agregar al final del contenedor
        tableContainer.appendChild(gridContainer);
        console.log('✅ [CATEGORIAS] Grid agregado al final del contenedor');
    }
    
    console.log('✅ [CATEGORIAS] Grid container creado exitosamente');
    return gridContainer;
}

// Mostrar categorías en grid - DISEÑO EXACTO DE PRODUCTOS
function displayCategoriasGrid(categorias) {
    const gridContainer = document.querySelector('.categorias-grid');
    if (!gridContainer) {
        console.error('Grid container no encontrado');
        return;
    }
    
    // Mensaje cuando no hay categorías
    if (!categorias || categorias.length === 0) {
        gridContainer.innerHTML = `
            <div class="no-products-message">
                <i class="fas fa-tags"></i>
                <p>No se encontraron categorías</p>
            </div>
        `;
        return;
    }
    
    // Detectar si es móvil
    const isMobile = window.innerWidth <= 768;
    
    // Generar cards con estructura EXACTA de productos
    gridContainer.innerHTML = categorias.map(cat => {
        const imageUrl = cat.url_imagen_categoria || '/fashion-master/public/assets/img/default-product.jpg';
        const isActive = cat.estado_categoria === 'activo';
        const estadoText = isActive ? 'Activo' : 'Inactivo';
        const estadoClass = isActive ? 'active' : 'inactive';
        
        // Formatear fecha correctamente (intentar varios campos)
        const fechaFormateada = cat.fecha_creacion_formato || 
                               formatDate(cat.fecha_registro_categoria) || 
                               formatDate(cat.fecha_creacion_categoria) || 
                               'N/A';
        
        // Generar HTML de imagen (tanto para móvil como PC)
        const imageHTML = `
            <div class="product-card-image-mobile ${cat.url_imagen_categoria ? '' : 'no-image'}">
                ${cat.url_imagen_categoria 
                    ? `<img src="${cat.url_imagen_categoria}" alt="${cat.nombre_categoria || 'Categoría'}" onerror="this.parentElement.classList.add('no-image'); this.style.display='none'; this.parentElement.innerHTML='<i class=\\'fas fa-tags\\'></i>';">` 
                    : '<i class="fas fa-tags"></i>'}
            </div>
        `;
        
        return `
            <div class="product-card" ondblclick="showEditCategoriaModal(${cat.id_categoria})" style="cursor: pointer;" data-categoria-id="${cat.id_categoria}">
                ${imageHTML}
                <div class="product-card-header">
                    <h3 class="product-card-title">${cat.nombre_categoria || 'Sin nombre'}</h3>
                    <span class="product-card-status ${estadoClass}">
                        ${estadoText}
                    </span>
                </div>
                
                <div class="product-card-body">
                    ${cat.codigo_categoria ? `<div class="product-card-sku">Código: ${cat.codigo_categoria}</div>` : ''}
                    
                    ${cat.descripcion_categoria ? `
                    <div class="product-card-category">
                        <i class="fas fa-align-left"></i> ${truncateText(cat.descripcion_categoria, 60)}
                    </div>
                    ` : ''}
                    
                    <div class="product-card-price">
                        <i class="fas fa-calendar"></i>
                        ${fechaFormateada}
                    </div>
                </div>
                
                <div class="product-card-actions">
                    <button class="product-card-btn btn-view" onclick="event.stopPropagation(); showViewCategoriaModal(${cat.id_categoria})" style="background-color: #1a73e8 !important; color: white !important; border: none !important; box-shadow: 0 4px 8px rgba(26, 115, 232, 0.3) !important;">
                        <i class="fas fa-eye" style="color: white !important;"></i>
                    </button>
                    <button class="product-card-btn btn-edit" onclick="event.stopPropagation(); showEditCategoriaModal(${cat.id_categoria})" style="background-color: #34a853 !important; color: white !important; border: none !important; box-shadow: 0 4px 8px rgba(52, 168, 83, 0.3) !important;">
                        <i class="fas fa-edit" style="color: white !important;"></i>
                    </button>
                    <button class="product-card-btn ${isActive ? 'btn-deactivate' : 'btn-activate'}" 
                            onclick="event.stopPropagation(); toggleEstadoCategoria(${cat.id_categoria})" 
                            style="background-color: #6f42c1 !important; color: white !important; border: none !important;">
                        <i class="fas fa-${isActive ? 'power-off' : 'toggle-on'}" style="color: white !important;"></i>
                    </button>
                    <button class="product-card-btn btn-delete" onclick="event.stopPropagation(); deleteCategoria(${cat.id_categoria}, '${(cat.nombre_categoria || 'Categoría').replace(/'/g, "\\'")}')\" style="background-color: #f44336 !important; color: white !important; border: none !important; box-shadow: 0 4px 8px rgba(244, 67, 54, 0.3) !important;">
                        <i class="fas fa-trash" style="color: white !important;"></i>
                    </button>
                </div>
            </div>
        `;
    }).join('');
    
    // Aplicar Masonry layout en móvil
    if (isMobile) {
        applyMasonryLayoutCategorias();
    }
    
    console.log(`✅ [CATEGORIAS] Grid renderizado con ${categorias.length} categorías`);
}

// Función para aplicar Masonry layout en categorías
function applyMasonryLayoutCategorias() {
    const gridContainer = document.querySelector('.categorias-grid');
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

function showErrorCategorias(message) {
    const tbody = document.getElementById('categorias-table-body');
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="error-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Error: ${message}</p>
                    <button onclick="loadCategoriasData()" class="btn-modern btn-primary">
                        <i class="fas fa-refresh"></i> Reintentar
                    </button>
                </td>
            </tr>
        `;
    }
}

// ===== ACTUALIZAR STATS =====
function updateStatsCategorias(pagination) {
    if (!pagination) return;
    
    const showingEl = document.getElementById('showing-end-categorias');
    const totalEl = document.getElementById('total-categorias');
    
    if (showingEl) showingEl.textContent = pagination.total_items || 0;
    if (totalEl) totalEl.textContent = pagination.total_items || 0;
}

// ═════════════════════════════════════════════════════════════════════════════
// SISTEMA DE MENÚ FLOTANTE - CON NOMBRES ÚNICOS PARA CATEGORÍAS
// ═════════════════════════════════════════════════════════════════════════════

// Variables globales ÚNICAS para categorías
let cat_floatingContainer = null;
let cat_activeCategoriaId = null;
let cat_isAnimating = false;
let cat_animationTimeout = null;
let cat_floatingButtons = [];
let cat_centerButton = null;

// Función principal para mostrar botones flotantes - RENOMBRADA
function showActionMenuCategoria(categoriaId, categoriaNombre, estado, event) {
    console.log('═══════════════════════════════════════════════════════════');
    console.log('🎯 [CATEGORIA] showActionMenuCategoria LLAMADA');
    console.log('   ID:', categoriaId);
    console.log('   Nombre:', categoriaNombre);
    console.log('   Estado:', estado);
    console.log('   Event:', event);
    console.log('   cat_isAnimating:', cat_isAnimating);
    console.log('   cat_floatingContainer:', cat_floatingContainer);
    console.log('   cat_activeCategoriaId:', cat_activeCategoriaId);
    console.log('═══════════════════════════════════════════════════════════');
    
    // Prevenir múltiples ejecuciones
    if (cat_isAnimating) {
        console.log('⏸️ [CATEGORIA] Animación en progreso, ABORTANDO');
        return;
    }
    
    // Si ya está abierto para la misma categoría, cerrarlo
    if (cat_floatingContainer && cat_activeCategoriaId === categoriaId) {
        console.log('🔄 [CATEGORIA] Mismo menú abierto, cerrando...');
        closeFloatingActionsCategoria();
        return;
    }
    
    // Cerrar cualquier menú anterior
    if (cat_floatingContainer) {
        console.log('🧹 [CATEGORIA] Cerrando menú anterior...');
        closeFloatingActionsCategoria();
    }
    
    // Obtener el botón que disparó el evento
    let triggerButton = null;
    
    console.log('🔍 [CATEGORIA] Buscando trigger button...');
    
    if (event && event.currentTarget) {
        triggerButton = event.currentTarget;
        console.log('✅ [CATEGORIA] Trigger button encontrado: currentTarget', triggerButton);
    } else if (event && event.target) {
        triggerButton = event.target.closest('.btn-menu');
        console.log('✅ [CATEGORIA] Trigger button encontrado: target.closest', triggerButton);
    } else {
        console.log('⚠️ [CATEGORIA] No hay event, buscando en DOM...');
        const allMenuButtons = document.querySelectorAll('.btn-menu');
        console.log('   Total botones .btn-menu encontrados:', allMenuButtons.length);
        for (const btn of allMenuButtons) {
            const onclickAttr = btn.getAttribute('onclick') || '';
            if (onclickAttr.includes(`showActionMenuCategoria(${categoriaId}`)) {
                triggerButton = btn;
                console.log('✅ [CATEGORIA] Trigger button encontrado por onclick:', triggerButton);
                break;
            }
        }
    }
    
    if (!triggerButton) {
        console.error('❌ [CATEGORIA] No se encontró trigger button - ABORTANDO');
        console.log('   Event:', event);
        console.log('   event.currentTarget:', event?.currentTarget);
        console.log('   event.target:', event?.target);
        return;
    }
    
    console.log('🎨 [CATEGORIA] Creando menú flotante...');
    cat_isAnimating = true;
    cat_activeCategoriaId = categoriaId;
    
    // Crear contenedor flotante con animaciones
    createFloatingContainerCategoria(triggerButton, categoriaId, categoriaNombre, estado);
}

// Crear el contenedor flotante con animaciones avanzadas
function createFloatingContainerCategoria(triggerButton, categoriaId, categoriaNombre, estado) {
    console.log('🎨 [CATEGORIA] createFloatingContainerCategoria INICIANDO');
    console.log('   triggerButton:', triggerButton);
    console.log('   categoriaId:', categoriaId);
    
    if (cat_floatingContainer) {
        console.log('🧹 [CATEGORIA] Limpiando contenedor anterior...');
        closeFloatingActionsCategoria();
    }
    
    if (!triggerButton) {
        console.error('❌ [CATEGORIA] No hay triggerButton - ABORTANDO');
        cat_isAnimating = false;
        return;
    }
    
    console.log('📦 [CATEGORIA] Creando contenedor...');
    cat_floatingContainer = document.createElement('div');
    cat_floatingContainer.id = 'cat-floating-menu-' + categoriaId;
    cat_floatingContainer.className = 'cat-floating-container';
    console.log('   Contenedor creado:', cat_floatingContainer);
    
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
    
    cat_floatingContainer.style.cssText = `
        position: fixed !important;
        z-index: 999999 !important;
        pointer-events: none !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        display: block !important;
    `;
    
    cat_floatingContainer.triggerButton = triggerButton;
    
    console.log('⚙️ [CATEGORIA] Creando botón central...');
    createCenterButtonCategoria();
    
    const actions = [
        { icon: 'fa-eye', color: '#1a73e8', actionFn: () => viewCategoriaAction(categoriaId) },
        { icon: 'fa-edit', color: '#34a853', actionFn: () => editCategoriaAction(categoriaId) },
        { icon: estado === 'activo' ? 'fa-power-off' : 'fa-toggle-on', color: '#9c27b0', actionFn: () => changeCategoriaEstadoAction(categoriaId) },
        { icon: 'fa-trash', color: '#f44336', actionFn: () => deleteCategoriaAction(categoriaId, categoriaNombre) }
    ];
    
    cat_floatingButtons = [];
    const radius = 80;
    
    actions.forEach((action, index) => {
        const angle = (index / actions.length) * 2 * Math.PI - Math.PI / 2;
        console.log(`   Botón ${index + 1}:`, action.label, 'ángulo:', angle);
        createButtonCategoria(action, index, angle, radius);
    });
    
    console.log('📍 [CATEGORIA] Agregando contenedor al DOM...');
    
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
    document.body.appendChild(cat_floatingContainer);
    console.log('   ✅ Contenedor agregado a BODY (position: fixed)');
    
    console.log('📐 [CATEGORIA] Actualizando posiciones...');
    updateButtonPositionsCategoria();
    
    cat_activeCategoriaId = categoriaId;
    
    console.log('🎧 [CATEGORIA] Configurando event listeners...');
    setupEventListenersCategoria();
    
    console.log('🚀 [CATEGORIA] Iniciando animación de apertura...');
    startOpenAnimationCategoria();
    
    console.log('✅ [CATEGORIA] createFloatingContainerCategoria COMPLETADO');
}

// Crear botón central con tres puntitos
function createCenterButtonCategoria() {
    cat_centerButton = document.createElement('div');
    cat_centerButton.className = 'cat-center-button';
    cat_centerButton.innerHTML = '<i class="fas fa-ellipsis-v"></i>';
    
    cat_centerButton.style.cssText = `
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
    
    cat_centerButton.addEventListener('mouseenter', () => {
        cat_centerButton.style.transform = 'scale(1.15) rotate(180deg)';
    });
    
    cat_centerButton.addEventListener('mouseleave', () => {
        cat_centerButton.style.transform = 'scale(1) rotate(360deg)';
        cat_centerButton.style.boxShadow = 'none';
        cat_centerButton.style.background = 'transparent';
    });
    
    cat_centerButton.addEventListener('click', (e) => {
        e.stopPropagation();
        closeFloatingActionsCategoria();
    });
    
    cat_floatingContainer.appendChild(cat_centerButton);
}

// Crear botón animado individual
function createButtonCategoria(action, index, angle, radius) {
    const button = document.createElement('div');
    button.innerHTML = `<i class="fas ${action.icon}"></i>`;
    button.dataset.angle = angle;
    button.dataset.radius = radius;
    button.dataset.index = index;
    button.className = 'cat-floating-button';
    
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
        createRippleCategoria(button, action.color);
        // Tooltip deshabilitado - solo iconos
        // showTooltipCategoria(button, action.label);
    });
    
    button.addEventListener('mouseleave', () => {
        button.style.transform = 'scale(1) rotate(0deg)';
        button.style.boxShadow = `0 6px 20px ${action.color}40`;
        button.style.zIndex = '1000001';
        hideTooltipCategoria();
    });
    
    button.addEventListener('click', (e) => {
        e.stopPropagation();
        forceCloseCategoria();
        
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
    
    cat_floatingContainer.appendChild(button);
    cat_floatingButtons.push(button);
}

// Crear efecto ripple
function createRippleCategoria(button, color) {
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
        animation: catRippleEffect 0.6s ease-out !important;
        z-index: -1 !important;
    `;
    
    if (!document.querySelector('#cat-ripple-styles')) {
        const styles = document.createElement('style');
        styles.id = 'cat-ripple-styles';
        styles.textContent = `
            @keyframes catRippleEffect {
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
function showTooltipCategoria(button, text) {
    hideTooltipCategoria();
    
    const tooltip = document.createElement('div');
    tooltip.id = 'cat-tooltip';
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
function hideTooltipCategoria() {
    const tooltip = document.getElementById('cat-tooltip');
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
function updateButtonPositionsCategoria() {
    console.log('📐 [CATEGORIA] updateButtonPositionsCategoria INICIANDO');
    console.log('   cat_floatingContainer:', cat_floatingContainer);
    console.log('   cat_floatingContainer.triggerButton:', cat_floatingContainer?.triggerButton);
    
    if (!cat_floatingContainer || !cat_floatingContainer.triggerButton) {
        console.log('⚠️ [CATEGORIA] No hay contenedor o trigger button');
        return;
    }
    
    if (!document.contains(cat_floatingContainer.triggerButton)) {
        console.log('⚠️ [CATEGORIA] Trigger button no está en el DOM');
        closeFloatingActionsCategoria();
        return;
    }
    
    // USAR POSICIÓN FIJA RELATIVA AL VIEWPORT (no al contenedor)
    const triggerRect = cat_floatingContainer.triggerButton.getBoundingClientRect();
    
    console.log('📏 [CATEGORIA] Posiciones (VIEWPORT):');
    console.log('   triggerRect:', triggerRect);
    console.log('   window.innerWidth:', window.innerWidth);
    console.log('   window.innerHeight:', window.innerHeight);
    
    // Calcular centro del botón trigger en coordenadas del viewport
    const centerX = triggerRect.left + triggerRect.width / 2;
    const centerY = triggerRect.top + triggerRect.height / 2;
    
    console.log('🎯 [CATEGORIA] Centro calculado (VIEWPORT):');
    console.log('   centerX:', centerX, 'centerY:', centerY);
    
    if (cat_centerButton) {
        cat_centerButton.style.left = `${centerX - 22.5}px`;
        cat_centerButton.style.top = `${centerY - 22.5}px`;
        console.log('✅ [CATEGORIA] Botón central posicionado:', {
            left: cat_centerButton.style.left,
            top: cat_centerButton.style.top
        });
    }
    
    cat_floatingButtons.forEach((button, index) => {
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
    
    console.log('✅ [CATEGORIA] Posiciones actualizadas (FIXED al viewport)');
}

// Iniciar animación de apertura
function startOpenAnimationCategoria() {
    console.log('🚀 [CATEGORIA] startOpenAnimationCategoria INICIANDO');
    console.log('   cat_centerButton:', cat_centerButton);
    console.log('   cat_floatingButtons:', cat_floatingButtons.length);
    
    if (cat_centerButton) {
        console.log('   Estado inicial del botón central:', {
            transform: cat_centerButton.style.transform,
            opacity: cat_centerButton.style.opacity,
            left: cat_centerButton.style.left,
            top: cat_centerButton.style.top
        });
        
        setTimeout(() => {
            cat_centerButton.style.transform = 'scale(1) rotate(360deg)';
            cat_centerButton.style.opacity = '1';
            console.log('✅ [CATEGORIA] Botón central animado:', {
                transform: cat_centerButton.style.transform,
                opacity: cat_centerButton.style.opacity
            });
        }, 100);
    }
    
    cat_floatingButtons.forEach((button, index) => {
        setTimeout(() => {
            console.log(`🎨 [CATEGORIA] Animando botón ${index}...`);
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
        cat_isAnimating = false;
        console.log('✅ [CATEGORIA] Animación completada, cat_isAnimating:', cat_isAnimating);
    }, 200 + (cat_floatingButtons.length * 100) + 400);
}

// Event listeners animados
function setupEventListenersCategoria() {
    const handleClick = (e) => {
        if (cat_floatingContainer && !cat_floatingContainer.contains(e.target)) {
            closeFloatingActionsCategoria();
        }
    };
    
    const handleResize = () => {
        if (cat_floatingContainer) {
            setTimeout(() => {
                updateButtonPositionsCategoria();
            }, 100);
        }
    };
    
    const handleScroll = () => {
        if (cat_floatingContainer) {
            updateButtonPositionsCategoria();
        }
    };
    
    document.addEventListener('click', handleClick);
    window.addEventListener('resize', handleResize, { passive: true });
    
    const container = cat_floatingContainer.parentElement;
    if (container) {
        container.addEventListener('scroll', handleScroll, { passive: true });
    }
    
    cat_floatingContainer.cleanup = () => {
        document.removeEventListener('click', handleClick);
        window.removeEventListener('resize', handleResize);
        if (container) {
            container.removeEventListener('scroll', handleScroll);
        }
    };
}

// Cerrar menú flotante con animación
function closeFloatingActionsCategoria() {
    if (!cat_floatingContainer || cat_isAnimating) return;
    
    cat_isAnimating = true;
    
    if (cat_animationTimeout) {
        clearTimeout(cat_animationTimeout);
    }
    
    hideTooltipCategoria();
    
    cat_floatingButtons.forEach((button, index) => {
        setTimeout(() => {
            button.style.transform = 'scale(0) rotate(-180deg)';
            button.style.opacity = '0';
        }, index * 50);
    });
    
    if (cat_centerButton) {
        setTimeout(() => {
            cat_centerButton.style.transform = 'scale(0) rotate(-360deg)';
            cat_centerButton.style.opacity = '0';
        }, cat_floatingButtons.length * 50 + 100);
    }
    
    cat_animationTimeout = setTimeout(() => {
        if (cat_floatingContainer) {
            if (cat_floatingContainer.cleanup) {
                cat_floatingContainer.cleanup();
            }
            
            cat_floatingContainer.remove();
            cat_floatingContainer = null;
            cat_centerButton = null;
            cat_floatingButtons = [];
            cat_activeCategoriaId = null;
            cat_isAnimating = false;
            
            // Eliminar indicador DEBUG
            const debugIndicator = document.getElementById('cat-debug-indicator-' + cat_activeCategoriaId);
            if (debugIndicator) {
                debugIndicator.remove();
                console.log('🔴 [CATEGORIA] Indicador DEBUG eliminado');
            }
        }
    }, cat_floatingButtons.length * 50 + 400);
}

// Forzar cierre del menú flotante
function forceCloseCategoria() {
    setTimeout(() => {
        if (cat_animationTimeout) {
            clearTimeout(cat_animationTimeout);
            cat_animationTimeout = null;
        }
        
        hideTooltipCategoria();
        
        if (cat_floatingContainer) {
            try {
                if (cat_floatingContainer.cleanup) {
                    cat_floatingContainer.cleanup();
                }
                cat_floatingContainer.remove();
            } catch (e) {}
            
            cat_floatingContainer = null;
            cat_centerButton = null;
            cat_floatingButtons = [];
            cat_activeCategoriaId = null;
            cat_isAnimating = false;
        }
        
        const orphanedContainers = document.querySelectorAll('.cat-floating-container');
        orphanedContainers.forEach(container => {
            try {
                container.remove();
            } catch (e) {}
        });
    }, 320);
}

// ═════════════════════════════════════════════════════════════════════════════
// FUNCIONES DE MODALES - CREAR, EDITAR, VER
// ═════════════════════════════════════════════════════════════════════════════

// Variable global para el iframe del modal
let categoriaModalIframe = null;

// Crear modal de categoría
function showCreateCategoriaModal() {
    console.log('➕ Abriendo modal de crear categoría');
    closeFloatingActionsCategoria();
    openCategoriaModal('create');
}

// Ver categoría
function showViewCategoriaModal(categoriaId) {
    console.log('👁️ Ver categoría:', categoriaId);
    closeFloatingActionsCategoria();
    openCategoriaModal('view', categoriaId);
}

// Editar categoría
function showEditCategoriaModal(categoriaId) {
    console.log('✏️ Editar categoría:', categoriaId);
    closeFloatingActionsCategoria();
    openCategoriaModal('edit', categoriaId);
}

// Abrir modal genérico de categoría (iframe)
function openCategoriaModal(action, categoriaId = null) {
    console.log('🔧 openCategoriaModal -', action, categoriaId);
    
    // Cerrar modal anterior si existe
    closeCategoriaModal();
    
    // Construir URL con parámetros
    let url = `app/views/admin/categoria_modal.php?action=${action}`;
    if (categoriaId) {
        url += `&id=${categoriaId}`;
    }
    
    console.log('📄 URL del modal:', url);
    
    // Crear overlay
    const overlay = document.createElement('div');
    overlay.className = 'modal-overlay show';
    overlay.id = 'categoriaModalOverlay';
    
    // Crear contenedor del modal
    const modalContainer = document.createElement('div');
    modalContainer.className = 'modal-container show';
    modalContainer.id = 'categoriaModalContainer';
    
    // Crear iframe
    categoriaModalIframe = document.createElement('iframe');
    categoriaModalIframe.src = url;
    categoriaModalIframe.className = 'modal-iframe';
    categoriaModalIframe.id = 'categoriaModalIframe';
    categoriaModalIframe.style.cssText = `
        width: 100%;
        height: 100%;
        border: none;
        background: transparent;
    `;
    
    modalContainer.appendChild(categoriaModalIframe);
    overlay.appendChild(modalContainer);
    document.body.appendChild(overlay);
    
    console.log('✅ Modal de categoría abierto');
    
    // Cerrar con click en overlay
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            closeCategoriaModal();
        }
    });
    
    // Cerrar con ESC
    const escHandler = (e) => {
        if (e.key === 'Escape') {
            closeCategoriaModal();
            document.removeEventListener('keydown', escHandler);
        }
    };
    document.addEventListener('keydown', escHandler);
}

// Cerrar modal de categoría
function closeCategoriaModal() {
    console.log('❌ Cerrando modal de categoría');
    
    const overlay = document.getElementById('categoriaModalOverlay');
    const container = document.getElementById('categoriaModalContainer');
    
    if (overlay) {
        overlay.classList.remove('show');
        setTimeout(() => {
            overlay.remove();
        }, 300);
    }
    
    if (container) {
        container.classList.remove('show');
    }
    
    if (categoriaModalIframe) {
        categoriaModalIframe.remove();
        categoriaModalIframe = null;
    }
    
    // Recargar datos de categorías
    if (typeof loadCategoriasData === 'function') {
        loadCategoriasData();
    }
    
    console.log('✅ Modal de categoría cerrado');
}

// ═════════════════════════════════════════════════════════════════════════════
// FUNCIONES DE ACCIONES - CON NOMBRES ÚNICOS
// ═════════════════════════════════════════════════════════════════════════════

// Ver categoría
function viewCategoriaAction(categoriaId) {
    console.log('👁️ Ver categoría:', categoriaId);
    showViewCategoriaModal(categoriaId);
}

// Editar categoría
function editCategoriaAction(categoriaId) {
    console.log('✏️ Editar categoría:', categoriaId);
    showEditCategoriaModal(categoriaId);
}

// Cambiar estado de categoría - EN TIEMPO REAL
async function changeCategoriaEstadoAction(categoriaId) {
    console.log('⚡ Cambiando estado de categoría:', categoriaId);
    
    try {
        // Buscar categoría actual para determinar el nuevo estado
        const categoria = categoriasList.find(c => c.id_categoria == categoriaId);
        if (!categoria) {
            throw new Error('Categoría no encontrada');
        }
        
        const nuevoEstado = categoria.estado_categoria === 'activo' ? 'inactivo' : 'activo';
        console.log('   Estado actual:', categoria.estado_categoria, '→ Nuevo:', nuevoEstado);
        
        const response = await fetch(`${CATEGORIAS_CONFIG.apiUrl}?action=change_estado`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `id_categoria=${categoriaId}&estado=${nuevoEstado}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            console.log('✅ Estado cambiado exitosamente');
            
            // Actualizar en el array local
            categoria.estado_categoria = nuevoEstado;
            
            // Actualizar fila en tabla
            const row = document.querySelector(`tr[data-categoria-id="${categoriaId}"]`);
            if (row) {
                const badge = row.querySelector('.status-badge');
                if (badge) {
                    const isActive = nuevoEstado === 'activo';
                    badge.className = `status-badge ${isActive ? 'status-active' : 'status-inactive'}`;
                    badge.textContent = isActive ? 'Activo' : 'Inactivo';
                }
            }
            
            // Actualizar card en grid si está visible
            const card = document.querySelector(`.product-card[data-categoria-id="${categoriaId}"] .status-badge`);
            if (card) {
                const isActive = nuevoEstado === 'activo';
                card.className = `status-badge ${isActive ? 'status-active' : 'status-inactive'}`;
                card.textContent = isActive ? 'Activo' : 'Inactivo';
            }
            
            // Actualizar botón de acción en grid
            const gridActionBtn = document.querySelector(`.product-card[data-categoria-id="${categoriaId}"] .toggle-status-btn`);
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
        alert('Error al cambiar el estado de la categoría: ' + error.message);
    }
}

// Eliminar categoría con modal de confirmación
function deleteCategoriaAction(categoriaId, categoriaNombre) {
    console.log('🗑️ Eliminar categoría:', categoriaId, categoriaNombre);
    // 🔴 OCULTAR menú flotante (no cerrar, para evitar errores de null)
    if (cat_floatingContainer) {
        cat_floatingContainer.style.opacity = '0';
        cat_floatingContainer.style.pointerEvents = 'none';
    }
    showDeleteConfirmationCategoria(categoriaId, categoriaNombre); // ✅ Usar la versión CON estilos inline
}

// ═════════════════════════════════════════════════════════════════════════════
// FILTROS Y BÚSQUEDA EN TIEMPO REAL
// ═════════════════════════════════════════════════════════════════════════════

// Debounce para búsqueda en tiempo real
let searchDebounceTimer = null;

function handleSearchCategorias() {
    clearTimeout(searchDebounceTimer);
    
    searchDebounceTimer = setTimeout(() => {
        console.log('🔍 Buscando categorías...');
        currentPageCategorias = 1; // Resetear a página 1
        loadCategoriasData();
    }, 500); // 500ms de delay
}

function clearSearchCategorias() {
    const searchInput = document.getElementById('search-categorias');
    if (searchInput) {
        searchInput.value = '';
        handleSearchCategorias();
    }
}

function filterCategorias() {
    console.log('🔽 Filtrando categorías...');
    currentPageCategorias = 1; // Resetear a página 1
    loadCategoriasData();
}

// ═════════════════════════════════════════════════════════════════════════════
// FILTROS Y BÚSQUEDA
// ═════════════════════════════════════════════════════════════════════════════

function clearAllFiltersCategorias() {
    console.log('🧹 Limpiando todos los filtros...');
    
    if (typeof $ !== 'undefined') {
        // Limpiar todos los campos con jQuery
        $('#search-categorias').val('');
        $('#filter-estado-categoria').val('');
        $('#filter-fecha-categoria').val('');
        
        // Forzar la recarga del select de fechas limpiándolo
        const fechaSelect = document.getElementById('filter-fecha-categoria');
        if (fechaSelect) {
            fechaSelect.innerHTML = '<option value="">Todas las fechas</option>';
        }
        
        // Efecto visual de limpieza (igual que productos)
        $('.module-filters').addClass('filters-clearing');
        
        setTimeout(() => {
            $('.module-filters').removeClass('filters-clearing');
        }, 400);
    } else {
        // Fallback vanilla JS
        const elements = [
            'search-categorias',
            'filter-estado-categoria',
            'filter-fecha-categoria'
        ];
        
        elements.forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.value = '';
                element.classList.add('cleared');
                setTimeout(() => element.classList.remove('cleared'), 300);
            }
        });
        
        // Forzar la recarga del select de fechas limpiándolo
        const fechaSelect = document.getElementById('filter-fecha-categoria');
        if (fechaSelect) {
            fechaSelect.innerHTML = '<option value="">Todas las fechas</option>';
        }
    }
    
    // Resetear página y recargar
    currentPageCategorias = 1;
    loadCategoriasData();
    
    console.log('✅ Filtros limpiados');
}

// Función para cargar fechas únicas en el filtro
function loadCategoriasDates(categorias) {
    console.log('📅 loadCategoriasDates() iniciado con', categorias.length, 'categorías');
    console.log('📊 Primera categoría completa:', categorias[0]);
    
    const fechaSelect = document.getElementById('filter-fecha-categoria');
    if (!fechaSelect) {
        console.error('❌ No se encontró el select filter-fecha-categoria');
        return;
    }
    
    // Si el select ya tiene más de 1 opción (Todas las fechas + otras), no reconstruir
    if (fechaSelect.options.length > 1) {
        console.log('✓ El select ya tiene fechas cargadas, omitiendo reconstrucción');
        return;
    }
    
    // Extraer fechas únicas de las categorías
    const fechasSet = new Set();
    categorias.forEach((cat, index) => {
        console.log(`  🔍 Categoría ${index}:`, {
            nombre: cat.nombre_categoria,
            fecha_registro: cat.fecha_registro,
            fecha_creacion: cat.fecha_creacion_categoria,
            fecha_creacion_formato: cat.fecha_creacion_formato
        });
        
        // Intentar múltiples campos de fecha
        let fecha = cat.fecha_registro || cat.fecha_creacion_categoria || cat.fecha_creacion_formato;
        
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
            console.log(`    ⚠️ Sin fecha en categoría ${cat.nombre_categoria}`);
        }
    });
    
    console.log('📊 Total fechas únicas:', fechasSet.size);
    console.log('📊 Fechas:', Array.from(fechasSet));
    
    // Convertir a array y ordenar descendente (más reciente primero)
    const fechasArray = Array.from(fechasSet).sort((a, b) => b.localeCompare(a));
    
    // Guardar opción seleccionada actual
    const selectedValue = fechaSelect.value;
    
    // Limpiar opciones excepto la primera
    fechaSelect.innerHTML = '<option value="">Todas las fechas</option>';
    
    // Agregar opciones de fechas
    fechasArray.forEach(fecha => {
        const option = document.createElement('option');
        option.value = fecha;
        option.textContent = formatearFecha(fecha);
        fechaSelect.appendChild(option);
        console.log('  ➕ Opción agregada:', fecha, '→', formatearFecha(fecha));
    });
    
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
// FUNCIONES PARA MODALES DE CATEGORÍAS (Crear, Editar, Ver)
// ═════════════════════════════════════════════════════════════════════════════

function showCreateCategoriaModal() {
    console.log('🆕 Abriendo modal de crear categoría');
    openCategoriaModal('create');
}

function showEditCategoriaModal(categoriaId) {
    console.log('✏️ Abriendo modal de editar categoría:', categoriaId);
    openCategoriaModal('edit', categoriaId);
}

function showViewCategoriaModal(categoriaId) {
    console.log('👁️ Abriendo modal de ver categoría:', categoriaId);
    openCategoriaModal('view', categoriaId);
}

function openCategoriaModal(action, categoriaId = null) {
    console.log('🔄 openCategoriaModal:', action, categoriaId);
    
    // Bloquear scroll del body
    document.body.classList.add('categoria-modal-open');
    
    // Construir URL
    let modalUrl = 'app/views/admin/categoria_modal.php';
    const params = new URLSearchParams();
    params.append('action', action);
    if (categoriaId) {
        params.append('id', categoriaId);
    }
    modalUrl += '?' + params.toString();
    
    console.log('🌐 Cargando modal categoría desde:', modalUrl);
    
    // Crear contenedor temporal para loading
    const loadingDiv = document.createElement('div');
    loadingDiv.id = 'categoria-modal-loading';
    loadingDiv.className = 'categoria-view-modal';
    loadingDiv.innerHTML = `
        <div class="categoria-view-modal__overlay"></div>
        <div class="categoria-view-modal__container" style="display: flex; align-items: center; justify-content: center; min-height: 400px;">
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
            
            // Extraer el modal (VIEW usa #categoria-view-modal con ID único)
            let modal = tempDiv.querySelector('#categoria-view-modal');
            let isViewModal = true;
            
            if (!modal) {
                // Es modal CREATE/EDIT, buscar .modal-content
                modal = tempDiv.querySelector('.modal-content');
                isViewModal = false;
                
                if (modal) {
                    // Crear el overlay y wrapper para modal-content (igual que productos)
                    const overlay = document.createElement('div');
                    overlay.className = 'modal-overlay';
                    overlay.id = 'categoria-modal-overlay';
                    
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
                        console.log('✅ Modal categoría CREATE/EDIT mostrado');
                    }, 10);
                    
                    // Event listener para cerrar
                    overlay.addEventListener('click', (e) => {
                        if (e.target === overlay) {
                            closeCategoriaModal();
                        }
                    });
                    
                    // Botón close
                    const closeBtn = modal.querySelector('.modal-close');
                    if (closeBtn) {
                        closeBtn.addEventListener('click', closeCategoriaModal);
                    }
                    
                    // Listener ESC
                    const handleEsc = (e) => {
                        if (e.key === 'Escape') {
                            closeCategoriaModal();
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
                    console.log('✅ Clase .show agregada - Modal categoría VIEW con FADE IN (1s)');
                    console.log('📊 Clases del modal:', modal.className);
                    console.log('📊 Computed opacity:', window.getComputedStyle(modal).opacity);
                }, 100); // Aumentado a 100ms para asegurar que CSS está completamente cargado
                
                // Agregar event listener para cerrar al hacer click en overlay
                const overlay = modal.querySelector('.categoria-view-modal__overlay');
                if (overlay) {
                    overlay.addEventListener('click', closeCategoriaModal);
                }
                
                // Agregar listener para ESC
                const handleEsc = (e) => {
                    if (e.key === 'Escape') {
                        closeCategoriaModal();
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
            console.error('❌ Error cargando modal categoría:', error);
            
            // Remover loading
            if (loadingDiv) {
                loadingDiv.remove();
            }
            
            // Desbloquear scroll
            document.body.classList.remove('categoria-modal-open');
            
            alert('Error al cargar el modal: ' + error.message);
        });
}

function closeCategoriaModal() {
    console.log('🚪 closeCategoriaModal() iniciado');
    
    // Buscar el modal VIEW por ID
    let modal = document.getElementById('categoria-view-modal');
    let isViewModal = true;
    
    if (!modal) {
        // Buscar modal CREATE/EDIT (dentro del overlay)
        const overlay = document.getElementById('categoria-modal-overlay');
        if (overlay) {
            isViewModal = false;
            // Remover clase show del overlay
            overlay.classList.remove('show');
            overlay.classList.add('closing');
            console.log('🎬 Animación de cierre CREATE/EDIT iniciada');
            
            // Desbloquear scroll del body
            document.body.classList.remove('categoria-modal-open');
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
        document.body.classList.remove('categoria-modal-open');
        document.body.classList.remove('modal-open');
        return;
    }
    
    // Es modal VIEW
    // Remover clase show para animación de salida
    modal.classList.remove('show');
    modal.classList.add('closing');
    console.log('🎬 Animación de cierre VIEW iniciada (duración: 0.3s)');
    
    // Desbloquear scroll del body
    document.body.classList.remove('categoria-modal-open');
    
    // Remover del DOM después de la animación (0.3s = 300ms)
    setTimeout(() => {
        if (modal && modal.parentNode) {
            modal.remove();
            console.log('✅ Modal categoría eliminado del DOM');
        }
    }, 300); // Ajustado a 300ms
}

// ═════════════════════════════════════════════════════════════════════════════

// ===== FUNCIONES DE EXPORTACIÓN Y REPORTES =====
function exportCategoriasExcel() {
    console.log('📊 Exportando categorías a Excel...');
    
    // Obtener todas las categorías con los filtros actuales
    const searchInput = document.getElementById('search-categorias');
    const estadoSelect = document.getElementById('filter-estado-categoria');
    
    const params = new URLSearchParams({
        action: 'list',
        page: 1,
        limit: 99999 // Obtener todas las categorías
    });
    
    if (searchInput && searchInput.value) {
        params.append('search', searchInput.value);
    }
    
    if (estadoSelect && estadoSelect.value) {
        params.append('status', estadoSelect.value);
    }
    
    fetch(`${CATEGORIAS_CONFIG.apiUrl}?${params}`)
        .then(response => response.json())
        .then(data => {
            if (!data.success || !data.data || data.data.length === 0) {
                alert('No hay categorías para exportar');
                return;
            }
            
            // Preparar datos para Excel
            const categoriasData = data.data.map(cat => ({
                'ID': cat.id_categoria,
                'Código': cat.codigo_categoria || 'N/A',
                'Nombre': cat.nombre_categoria,
                'Descripción': cat.descripcion_categoria || 'Sin descripción',
                'Estado': cat.estado_categoria === 'activo' ? 'Activo' : 'Inactivo',
                'Fecha Creación': cat.fecha_creacion_formato || formatDate(cat.fecha_creacion_categoria)
            }));
            
            // Crear hoja de cálculo
            const worksheet = XLSX.utils.json_to_sheet(categoriasData);
            
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
            XLSX.utils.book_append_sheet(workbook, worksheet, 'Categorías');
            
            // Generar nombre de archivo con fecha
            const fechaHoy = new Date().toISOString().split('T')[0];
            const fileName = `categorias_${fechaHoy}.xlsx`;
            
            // Descargar archivo
            XLSX.writeFile(workbook, fileName);
            
            console.log('✅ Excel exportado:', fileName);
        })
        .catch(error => {
            console.error('❌ Error al exportar Excel:', error);
            alert('Error al exportar categorías a Excel');
        });
}

function showCategoriasReport() {
    alert('🚧 Función de reporte en desarrollo');
    // TODO: Implementar reporte de categorías
}

// ===== PAGINACIÓN =====
function updatePaginationCategorias(pagination) {
    if (!pagination) return;
    
    currentPageCategorias = pagination.current_page || 1;
    totalPagesCategorias = pagination.total_pages || 1;
    
    const currentEl = document.getElementById('current-page-categorias');
    const totalEl = document.getElementById('total-pages-categorias');
    
    if (currentEl) currentEl.textContent = currentPageCategorias;
    if (totalEl) totalEl.textContent = totalPagesCategorias;
    
    // Actualizar botones
    const firstBtn = document.getElementById('first-btn-categorias');
    const prevBtn = document.getElementById('prev-btn-categorias');
    const nextBtn = document.getElementById('next-btn-categorias');
    const lastBtn = document.getElementById('last-btn-categorias');
    
    if (firstBtn) firstBtn.disabled = currentPageCategorias <= 1;
    if (prevBtn) prevBtn.disabled = currentPageCategorias <= 1;
    if (nextBtn) nextBtn.disabled = currentPageCategorias >= totalPagesCategorias;
    if (lastBtn) lastBtn.disabled = currentPageCategorias >= totalPagesCategorias;
}

function goToFirstPageCategorias() {
    if (currentPageCategorias > 1) {
        currentPageCategorias = 1;
        loadCategoriasData();
    }
}

function previousPageCategorias() {
    if (currentPageCategorias > 1) {
        currentPageCategorias--;
        loadCategoriasData();
    }
}

function nextPageCategorias() {
    if (currentPageCategorias < totalPagesCategorias) {
        currentPageCategorias++;
        loadCategoriasData();
    }
}

function goToLastPageCategorias() {
    if (currentPageCategorias < totalPagesCategorias) {
        currentPageCategorias = totalPagesCategorias;
        loadCategoriasData();
    }
}

// ===== FILTROS Y BÚSQUEDA =====
function handleSearchCategorias() {
    clearTimeout(window.searchCategoriasTimeout);
    window.searchCategoriasTimeout = setTimeout(() => {
        currentPageCategorias = 1;
        loadCategoriasData();
    }, 300);
}

function filterCategorias() {
    currentPageCategorias = 1;
    loadCategoriasData();
}

function clearSearchCategorias() {
    const searchInput = document.getElementById('search-categorias');
    if (searchInput) {
        searchInput.value = '';
        loadCategoriasData();
    }
}

function clearAllFiltersCategorias() {
    console.log('🧹 Limpiando todos los filtros...');
    
    const searchInput = document.getElementById('search-categorias');
    const estadoSelect = document.getElementById('filter-estado-categoria');
    
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
    currentPageCategorias = 1;
    loadCategoriasData();
    
    console.log('✅ Filtros limpiados');
}
// ===== FUNCIÓN PARA CAMBIAR ESTADO - SIN NOTIFICACIONES =====
async function toggleEstadoCategoria(id) {
    try {
        console.log('⚡ Cambiando estado de categoría:', id);
        
        // Obtener estado actual
        const response = await fetch(`${CATEGORIAS_CONFIG.apiUrl}?action=get&id=${id}`);
        const result = await response.json();
        
        if (!response.ok || !result.success) {
            console.error('❌ Error al obtener datos de la categoría');
            return;
        }
        
        const currentEstado = result.data.estado_categoria;
        const newEstado = currentEstado === 'activo' ? 'inactivo' : 'activo';
        
        console.log(`🔄 Cambiando de ${currentEstado} a ${newEstado}`);
        
        // Cambiar estado sin confirmación
        const updateResponse = await fetch(`${CATEGORIAS_CONFIG.apiUrl}?action=change_estado`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `id=${id}&estado=${newEstado}`
        });
        
        const updateResult = await updateResponse.json();
        
        if (updateResponse.ok && updateResult.success) {
            console.log('✅ Estado cambiado exitosamente - SIN notificación');
            // Recargar datos sin notificaciones
            loadCategoriasData();
        } else {
            console.error('❌ Error al cambiar estado:', updateResult.error);
        }
        
    } catch (error) {
        console.error('❌ Error en toggleEstadoCategoria:', error.message);
    }
}

// ===== FUNCIÓN PARA ELIMINAR - CON CONFIRMACIÓN COMO PRODUCTOS =====
function deleteCategoria(categoriaId, categoriaNombre) {
    console.log('🗑️ deleteCategoria wrapper llamada:', categoriaId, categoriaNombre);
    // 🔴 OCULTAR menú flotante (no cerrar, para evitar errores de null)
    if (cat_floatingContainer) {
        cat_floatingContainer.style.opacity = '0';
        cat_floatingContainer.style.pointerEvents = 'none';
    }
    showDeleteConfirmationCategoria(categoriaId, categoriaNombre || 'Categoría');
}

// Función para mostrar modal de confirmación de eliminación
function showDeleteConfirmationCategoria(categoriaId, categoriaNombre) {
    console.log('🗑️ showDeleteConfirmationCategoria llamada:', categoriaId, categoriaNombre);
    
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
                <p>Para eliminar la categoría <strong>"${categoriaNombre}"</strong>, escribe la palabra <strong>"eliminar"</strong> en el campo de abajo:</p>
                
                <input type="text" id="deleteConfirmInput" class="confirmation-input" placeholder="Escribe 'eliminar' para confirmar" autocomplete="off">
                <div id="deleteError" class="delete-error">
                    Por favor escribe exactamente "eliminar" para confirmar
                </div>
            </div>
            <div class="delete-modal-footer">
                <button type="button" class="btn-cancel-delete" onclick="closeDeleteConfirmationCategoria()">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn-confirm-delete" onclick="confirmDeleteCategoria(${categoriaId}, '${categoriaNombre.replace(/'/g, "\\'")}')">
                    <i class="fas fa-trash"></i> Eliminar Categoría
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
                confirmDeleteCategoria(categoriaId, categoriaNombre);
            }
        });
    }
    
    // Permitir cerrar con ESC
    const handleEsc = (e) => {
        if (e.key === 'Escape') {
            closeDeleteConfirmationCategoria();
            document.removeEventListener('keydown', handleEsc);
        }
    };
    document.addEventListener('keydown', handleEsc);
    
    // Cerrar al hacer click en el overlay (fondo oscuro)
    overlay.addEventListener('click', function(e) {
        // Solo cerrar si se hace click directamente en el overlay, no en el modal
        if (e.target === overlay) {
            console.log('👆 Click en overlay detectado - cerrando modal');
            closeDeleteConfirmationCategoria();
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
function closeDeleteConfirmationCategoria() {
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
async function confirmDeleteCategoria(categoriaId, categoriaNombre) {
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
    
    console.log('✅ Confirmación válida, eliminando categoría:', categoriaId);
    
    try {
        const response = await fetch(`${CATEGORIAS_CONFIG.apiUrl}?action=delete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `id=${categoriaId}`
        });
        
        const result = await response.json();
        
        if (response.ok && result.success) {
            console.log('✅ Categoría eliminada exitosamente');
            closeDeleteConfirmationCategoria();
            loadCategoriasData();
        } else {
            console.error('❌ Error al eliminar:', result.error);
            if (errorDiv) {
                errorDiv.textContent = result.error || 'Error al eliminar la categoría';
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
window.destroyCategoriasModule = function() {
    console.log('🗑️ [CATEGORIAS] Destruyendo módulo...');
    
    try {
        // 1. Limpiar variables globales
        categoriasList = [];
        currentPageCategorias = 1;
        totalPagesCategorias = 1;
        isLoadingCategorias = false;
        
        // 2. Resetear bandera
        window.categoriasModuleInitialized = false;
        
        // 3. Limpiar timeouts
        if (window.searchCategoriasTimeout) {
            clearTimeout(window.searchCategoriasTimeout);
        }
        
        // 4. RESETEAR VISTA A TABLA (estado inicial)
        console.log('🔄 [CATEGORIAS] Reseteando vista a tabla...');
        
        // Remover grid container si existe
        const gridContainer = document.querySelector('.products-grid');
        if (gridContainer) {
            gridContainer.remove();
        }
        
        // Asegurar que tabla esté visible
        const tableWrapper = document.getElementById('categorias-table-wrapper');
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
            localStorage.removeItem('categorias_view_mode');
        } catch (e) {
            console.warn('No se pudo limpiar localStorage:', e);
        }
        
        // 6. Cerrar menú flotante de acciones si está abierto
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
        
        // Limpiar timeouts de animación
        if (categorias_animationTimeout) {
            clearTimeout(categorias_animationTimeout);
            categorias_animationTimeout = null;
        }
        
        // Limpiar tooltips
        hideTooltipCategorias();
        
        // 7. Limpiar modales y overlays de categorías
        const modals = document.querySelectorAll('[id*="categoria-modal"], .categoria-modal, .delete-confirmation-overlay');
        modals.forEach(modal => {
            if (modal && modal.parentNode) {
                modal.remove();
            }
        });
        
        console.log('✅ [CATEGORIAS] Vista reseteada a tabla');
        console.log('✅ [CATEGORIAS] Módulo destruido correctamente');
        
    } catch (error) {
        console.error('❌ [CATEGORIAS] Error al destruir módulo:', error);
    }
};

// ===== EXPONER FUNCIONES GLOBALMENTE =====
window.initializeCategoriasModule = initializeCategoriasModule;
window.loadCategoriasData = loadCategoriasData;
window.toggleViewCategorias = toggleViewCategorias;
window.handleSearchCategorias = handleSearchCategorias;
window.filterCategorias = filterCategorias;
window.clearSearchCategorias = clearSearchCategorias;
window.clearAllFiltersCategorias = clearAllFiltersCategorias;
window.showCreateCategoriaModal = showCreateCategoriaModal;
window.showEditCategoriaModal = showEditCategoriaModal;
window.showViewCategoriaModal = showViewCategoriaModal;
window.closeCategoriaModal = closeCategoriaModal; // ⭐ Función para cerrar modal
window.openCategoriaModal = openCategoriaModal; // ⭐ Función para abrir modal
window.showActionMenuCategoria = showActionMenuCategoria; // ⭐ Función principal del menú flotante
window.viewCategoriaAction = viewCategoriaAction;
window.editCategoriaAction = editCategoriaAction;
window.changeCategoriaEstadoAction = changeCategoriaEstadoAction;
window.deleteCategoriaAction = deleteCategoriaAction;
window.confirmDeleteCategoria = confirmDeleteCategoria;
window.toggleEstadoCategoria = toggleEstadoCategoria; // ⭐ Para botones del grid
window.deleteCategoria = deleteCategoria; // ⭐ Para botones del grid
window.showDeleteConfirmationCategoria = showDeleteConfirmationCategoria; // ⭐ Modal de confirmación (CON estilos)
window.closeDeleteConfirmationCategoria = closeDeleteConfirmationCategoria; // ⭐ Cerrar modal
window.goToFirstPageCategorias = goToFirstPageCategorias;
window.previousPageCategorias = previousPageCategorias;
window.nextPageCategorias = nextPageCategorias;
window.goToLastPageCategorias = goToLastPageCategorias;
window.exportCategoriasExcel = exportCategoriasExcel;
window.showCategoriasReport = showCategoriasReport;
window.updateSingleCategoria = updateSingleCategoria; // ⭐ Actualización en tiempo real

console.log('✅ [CATEGORIAS] Funciones expuestas globalmente');

// ===== AUTO-INICIALIZACIÓN =====
setTimeout(() => {
    console.log('🚀 [CATEGORIAS] Auto-inicializando...');
    initializeCategoriasModule();
}, 50);

</script>

<style>
/* Estilos específicos para categorías */
.categorias-grid .grid-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    overflow: hidden;
    transition: all 0.2s;
}

.categorias-grid .grid-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.categorias-grid .card-image {
    position: relative;
    height: 180px;
    overflow: hidden;
    background: rgba(255, 255, 255, 0.05);
    display: flex;
    align-items: center;
    justify-content: center;
}

.categorias-grid .card-image img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.categorias-grid .card-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    padding: 4px 12px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    color: white;
}

.categorias-grid .card-body {
    padding: 16px;
}

.categorias-grid .card-body h4 {
    margin: 0 0 8px;
    font-size: 16px;
    font-weight: 600;
    color: #1f2937;
}

.categorias-grid .card-code {
    font-size: 13px;
    color: #6b7280;
    margin-bottom: 8px;
    font-family: monospace;
}

.categorias-grid .card-description {
    font-size: 14px;
    color: #6b7280;
    line-height: 1.5;
}

.categorias-grid .card-actions {
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
   ESTILOS DE MODALES - Overlay simple (el CSS real está en categoria-view-modal.css)
   ═══════════════════════════════════════════════════════════════════════ */

/* Modal Overlay - Solo estructura básica */
.modal-overlay-categoria {
    /* El overlay lo maneja categoria-view-modal.css */
    display: none !important;
}

/* Bloquear scroll cuando modal está abierto */
body.categoria-modal-open {
    overflow: hidden !important;
}

/* Estilos específicos para imágenes de categorías en tablas */
.admin-categorias-module .product-image-small {
    background: rgba(255, 255, 255, 0.05);
}

.admin-categorias-module .product-image-small img {
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
    img.alt = categoryName || 'Categoría';
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
window.toggleFiltersModalCategorias = function() {
    const modal = document.getElementById('filters-modal-categorias');
    const isVisible = modal.style.display === 'flex';
    
    if (isVisible) {
        closeFiltersModalCategorias();
    } else {
        // Sincronizar filtros del desktop al modal
        syncFiltersToModalCategorias();
        modal.style.display = 'flex';
        setTimeout(() => modal.classList.add('active'), 10);
    }
};

// Cerrar modal de filtros
window.closeFiltersModalCategorias = function() {
    const modal = document.getElementById('filters-modal-categorias');
    modal.classList.remove('active');
    setTimeout(() => modal.style.display = 'none', 300);
};

// Sincronizar filtros del desktop al modal
window.syncFiltersToModalCategorias = function() {
    // Búsqueda
    const desktopSearch = document.getElementById('search-categorias');
    const modalSearch = document.getElementById('modal-search-categorias');
    if (desktopSearch && modalSearch) {
        modalSearch.value = desktopSearch.value;
    }
    
    // Estado
    const desktopEstado = document.getElementById('filter-estado-categoria');
    const modalEstado = document.getElementById('modal-filter-estado-categoria');
    if (desktopEstado && modalEstado) {
        modalEstado.value = desktopEstado.value;
    }
    
    // Fecha
    const desktopFecha = document.getElementById('filter-fecha-categoria');
    const modalFecha = document.getElementById('modal-filter-fecha-categoria');
    if (desktopFecha && modalFecha) {
        modalFecha.value = desktopFecha.value;
    }
};

// Sincronizar filtros del modal al desktop
window.syncFiltersFromModalCategorias = function() {
    // Búsqueda
    const modalSearch = document.getElementById('modal-search-categorias');
    const desktopSearch = document.getElementById('search-categorias');
    if (modalSearch && desktopSearch) {
        desktopSearch.value = modalSearch.value;
    }
    
    // Estado
    const modalEstado = document.getElementById('modal-filter-estado-categoria');
    const desktopEstado = document.getElementById('filter-estado-categoria');
    if (modalEstado && desktopEstado) {
        desktopEstado.value = modalEstado.value;
    }
    
    // Fecha
    const modalFecha = document.getElementById('modal-filter-fecha-categoria');
    const desktopFecha = document.getElementById('filter-fecha-categoria');
    if (modalFecha && desktopFecha) {
        desktopFecha.value = modalFecha.value;
    }
};

// Aplicar filtros desde el modal
window.applyFiltersModalCategorias = function() {
    syncFiltersFromModalCategorias();
    filterCategorias();
    closeFiltersModalCategorias();
};

// Limpiar filtros desde el modal
window.clearAllFiltersModalCategorias = function() {
    // Limpiar campos del modal
    document.getElementById('modal-search-categorias').value = '';
    document.getElementById('modal-filter-estado-categoria').value = '';
    document.getElementById('modal-filter-fecha-categoria').value = '';
    
    // Sincronizar y aplicar
    syncFiltersFromModalCategorias();
    clearAllFiltersCategorias();
    closeFiltersModalCategorias();
};

// Detectar si es móvil y mostrar/ocultar elementos
function detectMobileViewCategorias() {
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
            toggleViewCategorias('grid');
        }
    }
}

// Ejecutar al cargar y al redimensionar
window.addEventListener('load', detectMobileViewCategorias);
window.addEventListener('resize', detectMobileViewCategorias);

</script>

