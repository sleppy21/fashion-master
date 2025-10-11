
<?php
/**
 * VISTA DE GESTI�N DE categoriaS - DISE�O MODERNO
 * Sistema unificado con dise�o actualizado
 */
?>

<div class="admin-module admin-categorias-module">
    <!-- Header del m�dulo -->
    <div class="module-header">
        <div class="module-title">
            <div class="module-icon">
                <i class="fas fa-copyright"></i>
            </div>
            <div class="module-info">
                <h2 class="module-name">Gestión de categorías</h2>
                <p class="module-description">Administra las categorías de productos de la tienda</p>
            </div>
        </div>
        <div class="module-actions">
            <button class="btn-modern btn-primary" onclick="window.showCreatecategoriaModal();">
                <i class="fas fa-plus"></i>
                <span>Nueva categoria</span>
            </button>
            <button class="btn-modern btn-secondary" onclick="exportcategorias()">
                <i class="fas fa-download"></i>
                <span>Exportar Excel</span>
            </button>
            <button class="btn-modern btn-info" onclick="showcategoriasReport()">
                <i class="fas fa-chart-bar"></i>
                <span>Reporte</span>
            </button>
        </div>
    </div>

    <!-- Filtros y b�squeda -->
    <div class="module-filters">
        <div class="search-container">
            <div class="search-input-group">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="search-categorias" class="search-input" 
                       placeholder="Buscar categorias por nombre..." oninput="handlecategoriaSearchInputCategorias()">
                <button class="search-clear" onclick="clearcategoriaSearch()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        
        <div class="filters-grid">
            <div class="filter-group">
                <label class="filter-label">Estado</label>
                <select id="filter-status" class="filter-select" onchange="filtercategoriasCategorias()">
                    <option value="">Todos los estados</option>
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>
            <div class="filter-group">
                <button class="btn-modern btn-outline" onclick="clearAllcategoriaFilters()">
                    <i class="fas fa-filter-circle-xmark"></i>
                    Limpiar
                </button>
            </div>
        </div>
    </div>

    <!-- Contenido del m�dulo -->
    <div class="module-content">
        <div class="data-table-container">
            <div class="table-controls">
                <div class="table-info">
                    <span class="results-count">
                        Mostrando  <span id="showing-end-categorias">0</span> 
                        de <span id="total-categorias">0</span> categorias
                    </span>
                </div>
                <div class="table-actions">
                    <div class="view-options">
                        <button class="view-btn active" data-view="table" onclick="toggleViewcategoriasCategorias('table')">
                            <i class="fas fa-table"></i>
                        </button>
                        <button class="view-btn" data-view="grid" onclick="toggleViewcategoriasCategorias('grid')">
                            <i class="fas fa-th"></i>
                        </button>
                    </div>
                    <div class="bulk-actions" style="display: none;">
                        <span class="selected-count">0</span> seleccionados
                        <select class="bulk-select" onchange="handleBulkcategoriaAction(this.value); this.value='';">
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
                            <th class="no-sort">
                                <span>Imagen</span>
                            </th>
                            <th class="sortable" data-sort="nombre">
                                <span>Nombre categoria</span>
                            </th>
                            <th class="sortable" data-sort="codigo_categoria">
                                <span>Código</span>
                            </th>
                            <th class="sortable" data-sort="descripcion">
                                <span>Descripción</span>
                            </th>
                            <th class="sortable" data-sort="estado">
                                <span>Estado</span>
                            </th>
                            <th class="sortable" data-sort="fecha">
                                <span>Fecha Creación</span>
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

            <!-- Paginaci�n -->
            <div class="pagination-container">
                <div class="pagination-info">
                    <span class="pagination-text">
                        P�gina <span id="current-page-categorias">1</span> de <span id="total-pages-categorias">1</span>
                    </span>
                </div>
                <div class="pagination-controls">
                    <button class="pagination-btn" id="first-page-categorias" onclick="goToFirstPagecategorias()">
                        <i class="fas fa-angle-double-left"></i>
                    </button>
                    <button class="pagination-btn" id="prev-page-categorias" onclick="previousPagecategorias()">
                        <i class="fas fa-angle-left"></i>
                    </button>
                    <div class="pagination-numbers" id="pagination-numbers-categorias">
                        <!-- N�meros de p�gina din�micos -->
                    </div>
                    <button class="pagination-btn" id="next-page-categorias" onclick="nextPagecategorias()">
                        <i class="fas fa-angle-right"></i>
                    </button>
                    <button class="pagination-btn" id="last-page-categorias" onclick="goToLastPagecategorias()">
                        <i class="fas fa-angle-double-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// ============ CONFIGURACI�N ============

// ? CORRECCI�N CR�TICA: Usar namespace espec�fico para evitar conflictos con otros m�dulos
// window.CONFIG es compartido entre todos los m�dulos, causando conflictos
function initializecategoriaConfigCategorias() {
    if (typeof AppConfig !== 'undefined') {
        window.categoria_CONFIG = {
            apiUrl: AppConfig.getApiUrl('categoriaController.php')
        };
    } else {
        // Fallback si config.js no est� cargado
        window.categoria_CONFIG = {
            apiUrl: '/fashion-master/app/controllers/categoriaController.php'
        };
    }
    console.log('?? categoria_CONFIG inicializado:', window.categoria_CONFIG.apiUrl);
}

// SIEMPRE inicializar inmediatamente
initializecategoriaConfigCategorias();

// Alias para compatibilidad (SOLO dentro del contexto de categorias)
const CONFIG = window.categoria_CONFIG;

// Variables globales
let isLoading = false;
let categorias = [];
window.categorias = categorias; // ✅ Hacer accesible globalmente
let loadingTimeout = null; // Para prevenir bloqueos permanentes

// Variables de paginación
let currentPage = 1;
let totalPages = 1;

// Funci�n para obtener la URL correcta de la imagen de la categoria
function getcategoriaImageUrlCategorias(categoria, forceCacheBust = false) {
    // Priorizar url_imagen_categoria, luego imagen_categoria
    let imageUrl = '';
    
    if (categoria.url_imagen_categoria) {
        // Verificar que no sea una URL de placeholder
        if (categoria.url_imagen_categoria.includes('placeholder') || 
            categoria.url_imagen_categoria.includes('via.placeholder')) {
            imageUrl = (typeof AppConfig !== 'undefined') ? AppConfig.getImageUrl('default-product.png') : '/fashion-master/public/assets/img/default-product.png';
        } else {
            imageUrl = categoria.url_imagen_categoria;
        }
    } else if (categoria.imagen_categoria) {
        // Si es un nombre de archivo local, construir la ruta completa
        if (!categoria.imagen_categoria.startsWith('http')) {
            imageUrl = (typeof AppConfig !== 'undefined') ? AppConfig.getImageUrl('products/' + categoria.imagen_categoria) : '/fashion-master/public/assets/img/products/' + categoria.imagen_categoria;
        } else {
            imageUrl = categoria.imagen_categoria;
        }
    } else {
        imageUrl = (typeof AppConfig !== 'undefined') ? AppConfig.getImageUrl('default-product.png') : '/fashion-master/public/assets/img/default-product.png';
    }
    
    // Agregar cache-busting solo si se solicita expl�citamente
    if (forceCacheBust) {
        const cacheBuster = '?v=' + Date.now();
        return imageUrl + cacheBuster;
    }
    
    return imageUrl;
}

// Funci�n auxiliar para mostrar loading en b�squeda
function showSearchLoadingCategorias() {
    const tbody = document.getElementById('categorias-table-body');
    if (tbody) {
        tbody.innerHTML = `
            <tr class="loading-row">
                <td colspan="8" class="loading-cell">
                    <div class="loading-content">
                        <div class="spinner"></div>
                        <span>Buscando categorias...</span>
                    </div>
                </td>
            </tr>
        `;
    }
}

// ====================================================================
// TABLA SMOOTH UPDATER - Actualizador de tabla con animaciones suaves
// ====================================================================
window.categoriaSmoothTableUpdater = {
    /**
     * Elimina una fila de categoria con animaci�n suave
     * @param {number} categoriaId - ID de la categoria a eliminar
     */
    removecategoria: function(categoriaId) {
        // Buscar la fila por data-id
        const row = document.querySelector(`tr[data-id="${categoriaId}"]`);
        
        if (!row) {
            // Fallback: recargar toda la tabla
            if (typeof loadcategorias === 'function') {
                loadcategorias(true);
            }
            return;
        }
        
        // Animaci�n de salida suave
        row.style.transition = 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
        row.style.opacity = '0';
        row.style.transform = 'translateX(-20px) scale(0.95)';
        row.style.backgroundColor = 'rgba(239, 68, 68, 0.1)';
        
        // Despu�s de la animaci�n, eliminar del DOM
        setTimeout(() => {
            // Fade out altura
            const rowHeight = row.offsetHeight;
            row.style.height = rowHeight + 'px';
            row.style.overflow = 'hidden';
            
            // Force reflow
            row.offsetHeight;
            
            // Colapsar altura
            row.style.height = '0';
            row.style.paddingTop = '0';
            row.style.paddingBottom = '0';
            row.style.marginTop = '0';
            row.style.marginBottom = '0';
            
            // Eliminar del DOM despu�s de la animaci�n
            setTimeout(() => {
                row.remove();
                
                // Verificar si la tabla qued� vac�a
                const tbody = document.getElementById('categorias-table-body');
                if (tbody && tbody.querySelectorAll('tr').length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 3rem; color: #64748b;">
                                <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;">
                                    <i class="fas fa-inbox"></i>
                                </div>
                                <div style="font-size: 1.1rem; font-weight: 600;">No hay categorias disponibles</div>
                                <div style="font-size: 0.9rem; margin-top: 0.5rem;">Crea una nueva categoria para comenzar</div>
                            </td>
                        </tr>
                    `;
                }
            }, 400); // Esperar a que termine la animaci�n de colapso
        }, 400); // Esperar a que termine la animaci�n de fade out
    },

    /**
     * Actualiza una fila de categoria espec�fica con animaci�n suave
     * @param {number} categoriaId - ID de la categoria a actualizar
     * @param {object} categoriaData - Datos actualizados de la categoria
     */
    updateSinglecategoria: async function(categoriaId, categoriaData = null) {
        try {
            // Si no tenemos datos, obtenerlos del servidor
            if (!categoriaData) {
                const response = await fetch(`${window.categoria_CONFIG.apiUrl}?action=get&id=${categoriaId}`);
                const result = await response.json();
                
                if (!result.success || !result.data) {
                    throw new Error('No se pudieron obtener los datos de la categoria');
                }
                
                categoriaData = result.data;
            }

            // ? DETECTAR VISTA ACTUAL
            const currentView = getCurrentViewCategorias();
            console.log('??? Vista actual para actualizaci�n:', currentView);
            
            if (currentView === 'grid') {
                // ? ACTUALIZAR VISTA GRID
                console.log('?? Actualizando tarjeta en vista grid...');
                const card = document.querySelector(`.categoria-card[data-categoria-id="${categoriaId}"]`);
                
                if (card) {
                    // Actualizar imagen
                    const img = card.querySelector('img');
                    if (img) {
                        const imageUrl = getcategoriaImageUrlCategorias(categoriaData, true);
                        img.src = imageUrl;
                    }
                    
                    // Actualizar nombre
                    const title = card.querySelector('.categoria-card-title');
                    if (title) {
                        title.textContent = categoriaData.nombre_categoria || 'Sin nombre';
                    }
                    
                    // Actualizar estado
                    const status = card.querySelector('.categoria-card-status');
                    if (status) {
                        status.className = `categoria-card-status ${categoriaData.estado_categoria === 'activo' ? 'active' : 'inactive'}`;
                        status.textContent = categoriaData.estado_categoria === 'activo' ? 'Activo' : 'Inactivo';
                    }
                    
                    // Actualizar c�digo
                    const sku = card.querySelector('.categoria-card-sku');
                    if (sku && categoriaData.codigo_categoria) {
                        sku.textContent = `C�digo: ${categoriaData.codigo_categoria}`;
                    }
                    
                    // Actualizar descripci�n
                    const description = card.querySelector('.categoria-card-description');
                    if (description && categoriaData.descripcion_categoria) {
                        const desc = categoriaData.descripcion_categoria;
                        description.innerHTML = `<i class="fas fa-align-left"></i> ${desc.length > 80 ? desc.substring(0, 80) + '...' : desc}`;
                    }
                    
                    console.log('? Tarjeta actualizada en vista grid');
                    return;
                } else {
                    console.warn('?? No se encontr� tarjeta para actualizar, recargando datos...');
                    if (typeof loadcategoriasData === 'function') {
                        loadcategoriasData();
                    }
                    return;
                }
            }
            
            // ? ACTUALIZAR VISTA TABLA (c�digo original)
            console.log('?? Actualizando fila en vista tabla...');
            const row = document.querySelector(`tr[data-id="${categoriaId}"]`);
            
            if (!row) {
                if (typeof loadcategoriasData === 'function') {
                    loadcategoriasData();
                }
                return;
            }

            // Animaci�n de actualizaci�n: pulso suave
            row.style.transition = 'all 0.3s ease';
            row.style.backgroundColor = 'rgba(34, 197, 94, 0.1)';
            
            // Actualizar el contenido de cada celda
            const cells = row.querySelectorAll('td');
            
            // Celda 0: ID
            if (cells[0]) {
                cells[0].textContent = `#${String(categoriaData.id_categoria).padStart(4, '0')}`;
            }

            // Celda 1: Imagen
            if (cells[1]) {
                const imgContainer = cells[1].querySelector('.categoria-img-container');
                if (imgContainer) {
                    const img = imgContainer.querySelector('img');
                    const imageSrc = categoriaData.url_imagen_categoria || 
                                   (categoriaData.imagen_categoria ? `/fashion-master/public/assets/img/brands/${categoriaData.imagen_categoria}` : 
                                   '/fashion-master/public/assets/img/default-product.jpg');
                    if (img) {
                        img.src = imageSrc;
                        img.alt = categoriaData.nombre_categoria || 'categoria';
                    }
                }
            }

            // Celda 2: Nombre
            if (cells[2]) {
                cells[2].innerHTML = `<strong>${categoriaData.nombre_categoria || ''}</strong>`;
            }

            // Celda 3: Código
            if (cells[3]) {
                cells[3].innerHTML = `<span class="categoria-code">${categoriaData.codigo_categoria || 'N/A'}</span>`;
            }

            // Celda 4: Descripción
            if (cells[4]) {
                const descripcion = categoriaData.descripcion_categoria || 'Sin descripción';
                cells[4].innerHTML = `<span class="categoria-description">${descripcion.substring(0, 50)}${descripcion.length > 50 ? '...' : ''}</span>`;
            }

            // Celda 5: Estado
            if (cells[5]) {
                const isActive = categoriaData.estado_categoria === 'activo';
                cells[5].innerHTML = `
                    <span class="categoria-status categoria-status--${isActive ? 'active' : 'inactive'}">
                        <i class="fas fa-${isActive ? 'check-circle' : 'times-circle'}"></i>
                        ${isActive ? 'Activo' : 'Inactivo'}
                    </span>
                `;
            }

            // Celda 6: Fecha de creación
            if (cells[6] && categoriaData.fecha_creacion_categoria) {
                const fecha = new Date(categoriaData.fecha_creacion_categoria);
                cells[6].textContent = fecha.toLocaleDateString('es-ES');
            }

            // Volver al color normal despu�s de 500ms
            setTimeout(() => {
                row.style.backgroundColor = '';
            }, 500);

        } catch (error) {
            console.error('? Error al actualizar fila:', error);
            // Fallback: recargar toda la tabla
            if (typeof loadcategoriasData === 'function') {
                loadcategoriasData();
            }
        }
    }
};

// ====================================================================
// CARGAR categoriaS - Funci�n principal
// ====================================================================

// Función principal para cargar DATOS de categorias con efectos visuales (DEFINICIÓN TEMPRANA)
// IMPORTANTE: Esta función carga los DATOS, no la vista completa
// Para cargar la vista completa, usar la función loadcategorias() de admin.php
async function loadcategoriasData(forceCacheBust = false, preserveState = null) {
    console.log('?? loadcategoriasData() llamado - isLoading actual:', isLoading);
    console.trace('?? STACK TRACE - Quién llamó a loadcategoriasData:');

    // Prevenir llamadas simultáneas
    if (isLoading) {
        console.warn('?? loadcategoriasData() bloqueado - ya hay una carga en progreso');
        return;
    }
    
    // Asegurar que esté disponible globalmente
    window.loadcategoriasData = loadcategoriasData;
    
    isLoading = true;
    console.log('?? isLoading activado');
    
    // Timeout de seguridad: si despu�s de 10 segundos sigue bloqueado, desbloquear
    if (loadingTimeout) {
        clearTimeout(loadingTimeout);
    }
    loadingTimeout = setTimeout(() => {
        if (isLoading) {
            console.warn('? Timeout de carga alcanzado - desbloqueando isLoading forzadamente');
            isLoading = false;
        }
    }, 10000); // 10 segundos
    
    try {
        // Mostrar loading mejorado
        showSearchLoadingCategorias();
        
        // Usar estado preservado si est� disponible
        if (preserveState) {
            currentPage = preserveState.page || currentPage;
            
            // Restaurar filtros si est�n disponibles
            if (preserveState.searchTerm && typeof $ !== 'undefined') {
                $('#search-categorias').val(preserveState.searchTerm);
            }
            
        }
        
        // Construir URL con par�metros
        const params = new URLSearchParams({
            action: 'list',
            page: currentPage,
            limit: 10
        });
        
        // Agregar filtros si existen
        if (typeof $ !== 'undefined') {
            const searchInput = $('#search-categorias');
            if (searchInput.length && searchInput.val()) {
                params.append('search', searchInput.val());
            }
            
            const statusSelect = $('#filter-status');
            if (statusSelect.length && statusSelect.val() !== '') {
                params.append('status', statusSelect.val());
            }
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
        }
        
        const finalUrl = `${CONFIG.apiUrl}?${params}`;
        console.log('?? URL final del fetch:', finalUrl);
        console.log('?? Par�metros:', Object.fromEntries(params));
        
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
            
            throw new Error('Respuesta del servidor no es JSON v�lido');
        }
        
        if (!data.success) {
            throw new Error(data.error || 'Error desconocido del servidor');
        }
        
        categorias = data.data || [];
        window.categorias = categorias; // ✅ Actualizar referencia global
        console.log('? Datos recibidos del servidor:', {
            success: data.success,
            categoriasCount: categorias.length,
            pagination: data.pagination
        });
        
        displaycategoriasCategorias(categorias, forceCacheBust, preserveState);
        updateStatsCategorias(data.pagination);
        updatePaginationInfoCategorias(data.pagination);
        
        // Actualizar contador de resultados
        if (data.pagination) {
            updateResultsCounter(categorias.length, data.pagination.total_items);
        }
        
        // Destacar categoria reci�n actualizado/creado si est� especificado
        // PRESERVAR ESTADO - sin destacado visual para evitar bugs
        if (preserveState) {
            // Restaurar posici�n de scroll sin animaciones que causen problemas
            if (preserveState.scrollPosition && typeof restoreScrollPosition === 'function') {
                restoreScrollPosition(preserveState.scrollPosition);
            }
        }
        
    } catch (error) {
        console.error('? Error en loadcategorias:', error);
        const tbody = document.getElementById('categorias-table-body');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="loading-cell">
                        <div class="loading-content error">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Error: ${error.message}</span>
                            <button onclick="loadcategoriasData()" class="btn-modern btn-primary">
                                <i class="fas fa-redo"></i> Reintentar
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }
    } finally {
        // Limpiar timeout de seguridad
        if (loadingTimeout) {
            clearTimeout(loadingTimeout);
            loadingTimeout = null;
        }
        
        isLoading = false;
        console.log('?? isLoading desactivado en finally');
        
        // Loading overlay eliminado
    }
}

// Asegurar que la funci�n est� disponible globalmente
window.loadcategoriasData = loadcategoriasData;
// NO sobrescribir window.loadcategorias - esa funci�n debe seguir siendo la de admin.php
// que carga la vista completa. Solo usar loadcategoriasData internamente.

// Funci�n para cargar categor�as en el filtro
// Funci�n para obtener la vista actual
function getCurrentViewCategorias() {
    // PRIMERO: Intentar leer de localStorage
    try {
        const savedView = localStorage.getItem('categorias_current_view');
        if (savedView) {
            console.log('?? Vista le�da de localStorage:', savedView);
            return savedView;
        }
    } catch (e) {
        console.warn('?? No se pudo leer vista de localStorage:', e);
    }
    
    // SEGUNDO: Verificar botones activos en el DOM
    const gridViewBtn = document.querySelector('[data-view="grid"]');
    const tableViewBtn = document.querySelector('[data-view="table"]');
    
    if (gridViewBtn && gridViewBtn.classList.contains('active')) {
        console.log('?? Vista detectada por bot�n activo: grid');
        return 'grid';
    }
    
    // Por defecto, vista tabla
    console.log('?? Vista por defecto: table');
    return 'table';
}

// Helper para obtener URL de imagen de categoria
function getcategoriaImageUrlCategorias(categoria, forceCacheBust = false) {
    const timestamp = forceCacheBust ? '?t=' + new Date().getTime() : '';
    
    // Prioridad 1: url_imagen_categoria (ruta completa)
    if (categoria.url_imagen_categoria && categoria.url_imagen_categoria !== 'NULL' && categoria.url_imagen_categoria !== null) {
        return categoria.url_imagen_categoria + timestamp;
    }
    
    // Prioridad 2: imagen_categoria (nombre de archivo)
    if (categoria.imagen_categoria && categoria.imagen_categoria !== 'NULL' && categoria.imagen_categoria !== null) {
        // Si es default-product.jpg o default-product.png, devolver la ruta completa
        if (categoria.imagen_categoria === 'default-product.jpg' || categoria.imagen_categoria === 'default-product.png') {
            // Usar .jpg que es el que realmente existe
            return '/fashion-master/public/assets/img/default-product.jpg' + timestamp;
        }
        // Si es otro archivo, construir ruta
        const imgPath = '/fashion-master/public/assets/img/products/' + categoria.imagen_categoria + timestamp;
        return imgPath;
    }
    
    // Fallback: imagen por defecto (.jpg es el correcto)
    return '/fashion-master/public/assets/img/default-product.jpg' + timestamp;
}

// Funci�n para mostrar categorias en tabla o grid
function displaycategoriasCategorias(categorias, forceCacheBust = false, preserveState = null) {
    console.log('?? displaycategoriasCategorias() llamado con', categorias?.length || 0, 'categorias');
    console.trace('?? STACK TRACE displaycategoriasCategorias:');
    
    // Detectar vista actual
    const currentView = getCurrentViewCategorias();
    console.log('??? Vista actual:', currentView);
    
    if (currentView === 'grid') {
        // Si est� en vista grid, actualizar grid
        console.log('?? Redirigiendo a displaycategoriasGridCategorias()');
        displaycategoriasGridCategorias(categorias);
        return;
    }
    
    // Si est� en vista tabla, actualizar tabla
    console.log('?? Buscando tbody con ID: categorias-table-body');
    const tbody = document.getElementById('categorias-table-body');
    
    if (!tbody) {
        console.error('? No se encontr� el tbody de categorias');
        console.log('?? Elementos disponibles en el DOM:');
        console.log('  - Todos los tbody:', document.querySelectorAll('tbody'));
        console.log('  - Elementos con "categoria" en ID:', document.querySelectorAll('[id*="categoria"]'));
        return;
    }
    
    console.log('? tbody encontrado:', tbody);
    
    // LIMPIEZA FORZADA COMPLETA - remover TODOS los elementos hijos
    while (tbody.firstChild) {
        tbody.removeChild(tbody.firstChild);
    }
    tbody.innerHTML = '';
    
    if (!categorias || categorias.length === 0) {
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
    
    // Construir HTML completo y asignarlo UNA SOLA VEZ
    const htmlContent = categorias.map((categoria, index) => {
        // Imagen
        const imageUrl = getcategoriaImageUrlCategorias(categoria, true);
        const fallbackImage = '/fashion-master/public/assets/img/default-product.jpg';
        
        // Truncar descripci�n a 100 caracteres
        const descripcion = categoria.descripcion_categoria 
            ? (categoria.descripcion_categoria.length > 100 
                ? categoria.descripcion_categoria.substring(0, 100) + '...' 
                : categoria.descripcion_categoria)
            : 'Sin descripci�n';
            
        return `
        <tr oncontextmenu="return false;" ondblclick="editcategoria(${categoria.id_categoria})" style="cursor: pointer;" data-categoria-id="${categoria.id_categoria}">
            <td><strong>${categoria.id_categoria}</strong></td>
            <td>
                <div class="product-image-cell" ondblclick="event.stopPropagation(); showImageFullSize('${imageUrl}', '${(categoria.nombre_categoria || '').replace(/'/g, "\\'")}')"; style="cursor: zoom-in; width: 50px; height: 50px; border-radius: 8px; overflow: hidden;">
                    <img src="${imageUrl}" 
                         alt="${categoria.nombre_categoria || 'categoria'}" 
                         class="product-thumbnail"
                         style="width: 100%; height: 100%; object-fit: contain; border-radius: 8px;"
                         onerror="this.src='${fallbackImage}'; this.onerror=null;">
                </div>
            </td>
            <td>
                <div class="categoria-info">
                    <strong>${categoria.nombre_categoria || 'Sin nombre'}</strong>
                </div>
            </td>
            <td>
                <code>${categoria.codigo_categoria || 'N/A'}</code>
            </td>
            <td>
                <div class="descripcion-truncate" title="${(categoria.descripcion_categoria || 'Sin descripci�n').replace(/"/g, '&quot;')}">
                    ${descripcion}
                </div>
            </td>
            <td>
                <span class="status-badge ${categoria.estado_categoria === 'activo' ? 'status-active' : 'status-inactive'}">
                    ${categoria.estado_categoria === 'activo' ? 'Activo' : 'Inactivo'}
                </span>
            </td>
            <td>${categoria.fecha_creacion_formato || categoria.fecha_creacion_categoria || 'N/A'}</td>
            <td>
                <div class="action-buttons">
                    <button class="btn-action btn-menu" onclick="event.stopPropagation(); showActionMenuCategorias(${categoria.id_categoria}, '${(categoria.nombre_categoria || '').replace(/'/g, "\\'")}', 0, '${categoria.estado_categoria}', event)" title="Acciones">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                </div>
            </td>
        </tr>`;
    }).join('');
    
    // Asignar TODO el HTML de una sola vez para evitar renderizados parciales
    tbody.innerHTML = htmlContent;
    
    // ✅ Inicializar scroll de tabla después de renderizar
    if (typeof initializeTableScroll === 'function') {
        setTimeout(initializeTableScroll, 100);
    }
}

// Funci�n para actualizar estad�sticas
function updateStatsCategorias(pagination) {
    if (pagination) {
        const { current_page, total_pages, total_items, items_per_page } = pagination;
        const start = ((current_page - 1) * items_per_page) + 1;
        const end = Math.min(current_page * items_per_page, total_items);
        
        const showingStartEl = document.getElementById('showing-start-categorias');
        const showingEndEl = document.getElementById('showing-end-categorias');
        const totalcategoriasEl = document.getElementById('total-categorias');
        
        if (showingStartEl) showingStartEl.textContent = total_items > 0 ? start : 0;
        if (showingEndEl) showingEndEl.textContent = total_items > 0 ? end : 0;
        if (totalcategoriasEl) totalcategoriasEl.textContent = total_items;
    }
}

// Funci�n para actualizar informaci�n de paginaci�n
function updatePaginationInfoCategorias(pagination) {
    if (pagination) {
        currentPage = pagination.current_page || 1;
        totalPages = pagination.total_pages || 1;
        
        // Actualizar elementos de paginaci�n si existen
        const currentPageEl = document.getElementById('current-page-categorias');
        const totalPagesEl = document.getElementById('total-pages-categorias');
        
        if (currentPageEl) currentPageEl.textContent = currentPage;
        if (totalPagesEl) totalPagesEl.textContent = totalPages;
        
        // Actualizar botones de paginaci�n si existen
        const firstBtn = document.querySelector('[onclick="goToFirstPagecategorias()"]');
        const prevBtn = document.querySelector('[onclick="previousPagecategorias()"]');
        const nextBtn = document.querySelector('[onclick="nextPagecategorias()"]');
        const lastBtn = document.querySelector('[onclick="goToLastPagecategorias()"]');
        
        if (firstBtn) firstBtn.disabled = currentPage <= 1;
        if (prevBtn) prevBtn.disabled = currentPage <= 1;
        if (nextBtn) nextBtn.disabled = currentPage >= totalPages;
        if (lastBtn) lastBtn.disabled = currentPage >= totalPages;
    }
}

// Funci�n de filtrado mejorada con jQuery
function filtercategoriasCategorias() {
    if (typeof $ === 'undefined') {
        return filtercategoriasVanilla();
    }
    
    const search = $('#search-categorias').val() || '';
    const category = $('#filter-estado').val() || '';
    const status = $('#filter-status').val() || '';
    const cantidad_categorias = $('#filter-cantidad_categorias').val() || '';
    
    // Mostrar indicador de carga
    showSearchLoadingCategorias();
    
    // Reset p�gina actual
    currentPage = 1;
    
    // Recargar categorias con filtros
    loadcategoriasData();
}

// Funci�n de filtrado con vanilla JS como fallback
function filtercategoriasVanilla() {
    const searchInput = document.getElementById('search-categorias');
    const categorySelect = document.getElementById('filter-estado');
    const statusSelect = document.getElementById('filter-status');
    const cantidad_categoriasSelect = document.getElementById('filter-cantidad_categorias');
    
    const search = searchInput ? searchInput.value || '' : '';
    const category = categorySelect ? categorySelect.value || '' : '';
    const status = statusSelect ? statusSelect.value || '' : '';
    const cantidad_categorias = cantidad_categoriasSelect ? cantidad_categoriasSelect.value || '' : '';
    
    // Mostrar indicador de carga
    showSearchLoadingCategorias();
    
    // Reset p�gina actual
    currentPage = 1;
    
    // Recargar categorias con filtros
    loadcategoriasData();
}

// Funci�n para manejar b�squeda en tiempo real con jQuery
let searchTimeout;
function handlecategoriaSearchInputCategorias() {
    clearTimeout(searchTimeout);
    
    // Mostrar indicador visual de b�squeda
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
            filtercategoriasCategorias();
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
            filtercategoriasCategorias();
        }, 300);
    }
}

// Función para cambiar vista (tabla/grid)
function toggleViewcategoriasCategorias(viewType) {
    console.log('?? toggleViewcategoriasCategorias() llamado con viewType:', viewType);
    
    // CERRAR MENÚS FLOTANTES si están abiertos (evita errores de posición)
    if (activeFloatingContainer) {
        closeFloatingActionsAnimatedCategorias();
    }
    
    const tableContainer = document.querySelector('.data-table-wrapper');
    const gridContainer = document.querySelector('.categorias-grid');
    const viewButtons = document.querySelectorAll('.view-btn');
    
    // Si no existe el grid, crearlo
    if (!gridContainer) {
        createGridViewCategorias();
    }
    
    viewButtons.forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.view === viewType) {
            btn.classList.add('active');
        }
    });
    
    // GUARDAR la vista actual en localStorage
    try {
        localStorage.setItem('categorias_current_view', viewType);
        console.log('?? Vista guardada en localStorage:', viewType);
    } catch (e) {
        console.warn('⚠️ No se pudo guardar vista en localStorage:', e);
    }
    
    if (viewType === 'grid') {
        tableContainer.style.display = 'none';
        document.querySelector('.categorias-grid').style.display = 'grid';
        // ✅ SOLO actualizar la vista, NO recargar datos
        console.log('?? Cambiando a vista GRID - actualizando displaycategoriasCategorias()');
        if (window.categorias && window.categorias.length > 0) {
            displaycategoriasCategorias(window.categorias);
        } else {
            console.warn('?? No hay datos cargados, cargando...');
            loadcategoriasData();
        }
    } else {
        tableContainer.style.display = 'block';
        document.querySelector('.categorias-grid').style.display = 'none';
        // ✅ SOLO actualizar la vista, NO recargar datos
        console.log('?? Cambiando a vista TABLA - actualizando displaycategoriasCategorias()');
        if (window.categorias && window.categorias.length > 0) {
            displaycategoriasCategorias(window.categorias);
        } else {
            console.warn('?? No hay datos cargados, cargando...');
            loadcategoriasData();
        }
    }
}


// Funci�n para formatear fecha
function formatearFechaCategorias(fecha) {
    if (!fecha) return 'N/A';
    const date = new Date(fecha);
    const opciones = { year: 'numeric', month: 'short', day: 'numeric' };
    return date.toLocaleDateString('es-ES', opciones);
}

// Funci�n para crear vista grid
function createGridViewCategorias() {
    // Verificar si ya existe el grid
    let gridContainer = document.querySelector('.categorias-grid');
    if (gridContainer) {
        console.log('? Grid ya existe, no se crear� de nuevo');
        return gridContainer;
    }
    
    // Crear nuevo contenedor grid
    gridContainer = document.createElement('div');
    gridContainer.className = 'categorias-grid';
    gridContainer.style.display = 'none';
    
    // Insertar despu�s de la tabla
    const tableWrapper = document.querySelector('.data-table-wrapper');
    if (tableWrapper && tableWrapper.parentNode) {
        tableWrapper.parentNode.insertBefore(gridContainer, tableWrapper.nextSibling);
        console.log('? Grid creado correctamente');
    } else {
        console.error('? No se pudo encontrar tableWrapper para insertar grid');
    }
    
    return gridContainer;
}

// Funci�n para mostrar categorias en grid
function displaycategoriasGridCategorias(categorias) {
    const gridContainer = document.querySelector('.categorias-grid');
    if (!gridContainer) {
        console.error('? No se encontr� el contenedor grid de categorias');
        return;
    }
    
    // LIMPIAR COMPLETAMENTE antes de agregar nuevos datos
    gridContainer.innerHTML = '';
    
    if (!categorias || categorias.length === 0) {
        gridContainer.innerHTML = `
            <div class="no-categorias-message">
                <i class="fas fa-box-open"></i>
                <p>No se encontraron categorias</p>
            </div>
        `;
        return;
    }
    
    // Construir HTML completo y asignarlo UNA SOLA VEZ
    const htmlContent = categorias.map(categoria => {
        const imageUrl = getcategoriaImageUrlCategorias(categoria, true);
        const fallbackImage = '/fashion-master/public/assets/img/default-product.jpg';
        
        return `
            <div class="categoria-card" ondblclick="editcategoria(${categoria.id_categoria})" style="cursor: pointer;" data-categoria-id="${categoria.id_categoria}">
                <div class="categoria-card-header">
                    <div class="categoria-card-image" ondblclick="event.stopPropagation(); showImageFullSize('${imageUrl}', '${categoria.nombre_categoria || 'categoria'}')" style="cursor: zoom-in; border-radius: 12px; overflow: hidden;">
                        <img src="${imageUrl}" alt="${categoria.nombre_categoria || 'categoria'}" style="border-radius: 12px;" onerror="this.src='${fallbackImage}'; this.onerror=null;">
                    </div>
                    <h3 class="categoria-card-title">${categoria.nombre_categoria || 'Sin nombre'}</h3>
                    <span class="categoria-card-status ${categoria.estado_categoria === 'activo' ? 'active' : 'inactive'}">
                        ${categoria.estado_categoria === 'activo' ? 'Activo' : 'Inactivo'}
                    </span>
                </div>
                
                <div class="categoria-card-body">
                    ${categoria.codigo_categoria ? `<div class="categoria-card-sku">C�digo: ${categoria.codigo_categoria}</div>` : ''}
                    
                    ${categoria.descripcion_categoria ? `
                    <div class="categoria-card-description">
                        <i class="fas fa-align-left"></i> ${categoria.descripcion_categoria.length > 80 ? categoria.descripcion_categoria.substring(0, 80) + '...' : categoria.descripcion_categoria}
                    </div>
                    ` : ''}
                    
                    <div class="categoria-card-date">
                        <i class="fas fa-calendar"></i> ${formatearFechaCategorias(categoria.fecha_creacion_categoria)}
                    </div>
                </div>
                
                <div class="categoria-card-actions">
                    <button class="categoria-card-btn btn-view" onclick="event.stopPropagation(); viewcategoria(${categoria.id_categoria})" title="Ver categoria" style="background-color: #1a73e8 !important; color: white !important; border: none !important; box-shadow: 0 4px 8px rgba(26, 115, 232, 0.3) !important;">
                        <i class="fas fa-eye" style="color: white !important;"></i>
                    </button>
                    <button class="categoria-card-btn btn-edit" onclick="event.stopPropagation(); editcategoria(${categoria.id_categoria})" title="Editar categoria" style="background-color: #34a853 !important; color: white !important; border: none !important; box-shadow: 0 4px 8px rgba(52, 168, 83, 0.3) !important;">
                        <i class="fas fa-edit" style="color: white !important;"></i>
                    </button>
                    <button class="categoria-card-btn ${categoria.estado_categoria === 'activo' ? 'btn-deactivate' : 'btn-activate'}" 
                            onclick="event.stopPropagation(); changecategoriaEstado(${categoria.id_categoria})" 
                            title="${categoria.estado_categoria === 'activo' ? 'Desactivar' : 'Activar'} categoria"
                            style="background-color: #6f42c1 !important; color: white !important; border: none !important;">
                        <i class="fas fa-${categoria.estado_categoria === 'activo' ? 'power-off' : 'toggle-on'}" style="color: white !important;"></i>
                    </button>
                    <button class="categoria-card-btn btn-delete" onclick="event.stopPropagation(); deletecategoria(${categoria.id_categoria}, '${(categoria.nombre_categoria || 'categoria').replace(/'/g, "\\'")}')\" title="Eliminar categoria" style="background-color: #f44336 !important; color: white !important; border: none !important; box-shadow: 0 4px 8px rgba(244, 67, 54, 0.3) !important;">
                        <i class="fas fa-trash" style="color: white !important;"></i>
                    </button>
                </div>
            </div>
        `;
    }).join('');
    
    // Asignar el HTML completo de una sola vez
    gridContainer.innerHTML = htmlContent;
}

// ============ FUNCIONES PRINCIPALES EstadoS ============

// ===================================
// SISTEMA DE BOTONES FLOTANTES ANIMADOS - VERSIÓN AVANZADA
// ===================================

// Variables globales para el sistema flotante
let activeFloatingContainer = null;
let activecategoriaId = null;
let isAnimating = false;
let animationTimeout = null;
let floatingButtons = [];
let centerButton = null;

// Función principal para mostrar botones flotantes
function showActionMenuCategorias(categoriaId, categoriaName, cantidad_categorias, estado, event) {
    // CERRAR BURBUJA DE cantidad_categorias SI EST� ABIERTA
    const existingBubbles = document.querySelectorAll('.cantidad_categorias-update-bubble');
    existingBubbles.forEach(bubble => {
        if (bubble && bubble.parentNode) {
            // Animaci�n de salida
            bubble.style.transform = 'scale(0)';
            bubble.style.opacity = '0';
            setTimeout(() => {
                if (bubble && bubble.parentNode) {
                    bubble.remove();
                }
            }, 400);
        }
    });
    
    // Eliminar overlay de la burbuja de cantidad_categorias
    const cantidad_categoriasOverlays = document.querySelectorAll('.cantidad_categorias-bubble-overlay');
    cantidad_categoriasOverlays.forEach(overlay => {
        if (overlay && overlay.parentNode) {
            overlay.remove();
        }
    });
    
    // Prevenir múltiples ejecuciones
    if (isAnimating) return;
    
    // Si ya está abierto para el mismo Estado, cerrarlo
    if (activeFloatingContainer && activecategoriaId === categoriaId) {
        closeFloatingActionsAnimatedCategorias();
        return;
    }
    
    // Cerrar cualquier menú anterior
    if (activeFloatingContainer) {
        closeFloatingActionsAnimatedCategorias();
    }
    
    // Obtener el botón que disparó el evento - MEJORADO
    let triggerButton = null;
    
    if (event && event.currentTarget) {
        triggerButton = event.currentTarget;
    } else if (event && event.target) {
        // Buscar el bot�n padre si el click fue en el icono
        triggerButton = event.target.closest('.btn-menu');
    } else {
        // Fallback robusto: buscar entre todos los .btn-menu y comparar atributo onclick
        const allMenuButtons = document.querySelectorAll('.btn-menu');
        for (const btn of allMenuButtons) {
            const onclickAttr = btn.getAttribute('onclick') || '';
            if (onclickAttr.includes(`showActionMenuCategorias(${categoriaId}`)) {
                triggerButton = btn;
                break;
            }
        }
    }
    
    if (!triggerButton) {
        return;
    }
    
    isAnimating = true;
    activecategoriaId = categoriaId;
    
    // Crear contenedor flotante con animaciones
    createAnimatedFloatingContainerCategorias(triggerButton, categoriaId, categoriaName, cantidad_categorias, estado);
}

// Crear el contenedor flotante con animaciones avanzadas
function createAnimatedFloatingContainerCategorias(triggerButton, categoriaId, categoriaName, cantidad_categorias, estado) {
    // Limpiar cualquier menú anterior
    if (activeFloatingContainer) {
        closeFloatingActionsAnimatedCategorias();
    }
    
    // Verificar que tenemos un trigger button válido
    if (!triggerButton) {
        isAnimating = false;
        return;
    }
    
    // Crear contenedor principal con ID único
    activeFloatingContainer = document.createElement('div');
    activeFloatingContainer.id = 'animated-floating-menu-' + categoriaId;
    activeFloatingContainer.className = 'animated-floating-container';
    
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
    activeFloatingContainer.style.cssText = `
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
    activeFloatingContainer.triggerButton = triggerButton;
    
    // Crear bot�n central con los tres puntitos
    createCenterButtonCategorias();
    
    // Definir acciones con colores vibrantes
    // Definir acciones con colores vibrantes (usando closures para capturar event)
    const actions = [
        { icon: 'fa-eye', color: '#1a73e8', label: 'Ver', actionFn: () => viewcategoria(categoriaId) },
        { icon: 'fa-edit', color: '#34a853', label: 'Editar', actionFn: () => editcategoria(categoriaId) },
        { icon: estado === 'activo' ? 'fa-power-off' : 'fa-toggle-on', color: '#9c27b0', label: estado === 'activo' ? 'Desactivar' : 'Activar', actionFn: () => changecategoriaEstado(categoriaId) },
        { icon: 'fa-trash', color: '#f44336', label: 'Eliminar', actionFn: () => deletecategoria(categoriaId, categoriaName) }
    ];
    
    // Crear botones flotantes con animaciones
    floatingButtons = [];
    const radius = 80;
    
    actions.forEach((action, index) => {
        const angle = (index / actions.length) * 2 * Math.PI - Math.PI / 2;
        createAnimatedButtonCategorias(action, index, angle, radius);
    });
    
    // Agregar al contenedor de la tabla
    if (tableContainer) {
        tableContainer.appendChild(activeFloatingContainer);
    } else {
        document.body.appendChild(activeFloatingContainer);
    }
    
    // Actualizar posiciones iniciales
    updateAnimatedButtonPositionsCategorias();
    
    activecategoriaId = categoriaId;
    
    // Event listeners con animaciones
    setupAnimatedEventListenersCategorias();
    
    // Iniciar animaci�n de entrada
    startOpenAnimationCategorias();
}

// Crear bot�n central con tres puntitos (para cerrar)
function createCenterButtonCategorias() {
    centerButton = document.createElement('div');
    centerButton.className = 'animated-center-button';
    centerButton.innerHTML = '<i class="fas fa-ellipsis-v"></i>';
    
    centerButton.style.cssText = `
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
    centerButton.addEventListener('mouseenter', () => {
        centerButton.style.transform = 'scale(1.15) rotate(180deg)';
        centerButton.style.boxShadow = '0 6px 25px rgba(102, 126, 234, 0.3)';
        centerButton.style.background = 'rgba(255, 255, 255, 0.1)';
    });
    
    centerButton.addEventListener('mouseleave', () => {
        centerButton.style.transform = 'scale(1) rotate(360deg)';
        centerButton.style.boxShadow = 'none';
        centerButton.style.background = 'transparent';
    });
    
    // Click para cerrar
    centerButton.addEventListener('click', (e) => {
        e.stopPropagation();
        closeFloatingActionsAnimatedCategorias();
    });
    
    activeFloatingContainer.appendChild(centerButton);
}

// Crear bot�n animado individual
function createAnimatedButtonCategorias(action, index, angle, radius) {
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
        createRippleEffectCategorias(button, action.color);
        
        // Mostrar tooltip
        showTooltipCategorias(button, action.label);
    });
    
    button.addEventListener('mouseleave', () => {
        button.style.transform = 'scale(1) rotate(0deg)';
        button.style.boxShadow = `0 6px 20px ${action.color}40`;
        button.style.zIndex = '1000001';
        
        // Ocultar tooltip
        hideTooltipCategorias();
    });
    
    // Click handler con animaci�n
    button.addEventListener('click', (e) => {
        e.stopPropagation();
        
        // Forzar cierre inmediato del men�
        forceCloseFloatingActionsCategorias();
        
        // Animaci�n de click del bot�n
        button.style.transform = 'scale(0.9) rotate(180deg)';
        setTimeout(() => {
            button.style.transform = 'scale(1.1) rotate(360deg)';
        }, 100);
        
        // Ejecutar acci�n despu�s de un delay m�nimo
        setTimeout(() => {
            try {
                action.actionFn();
            } catch (err) {
                console.error('Error ejecutando acci�n flotante:', err);
            }
        }, 200);
    });
    
    activeFloatingContainer.appendChild(button);
    floatingButtons.push(button);
}

// Crear efecto ripple
function createRippleEffectCategorias(button, color) {
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
    
    // Agregar CSS de animaci�n si no existe
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
function showTooltipCategorias(button, text) {
    // Remover tooltip anterior si existe
    hideTooltipCategorias();
    
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
function hideTooltipCategorias() {
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

// Funci�n para actualizar posiciones de botones con animaciones
function updateAnimatedButtonPositionsCategorias() {
    if (!activeFloatingContainer) {
        return;
    }
    
    if (!activeFloatingContainer.triggerButton) {
        return;
    }
    
    // Verificar que el trigger button a�n existe en el DOM
    if (!document.contains(activeFloatingContainer.triggerButton)) {
        closeFloatingActionsAnimatedCategorias();
        return;
    }
    
    // Obtener el contenedor padre donde est�n los botones
    const container = activeFloatingContainer.parentElement;
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
    const triggerRect = activeFloatingContainer.triggerButton.getBoundingClientRect();
    
    // Calcular posici�n relativa del trigger respecto al contenedor
    const centerX = triggerRect.left - containerRect.left + triggerRect.width / 2;
    const centerY = triggerRect.top - containerRect.top + triggerRect.height / 2;
    
    // Ajustar por scroll del contenedor si es necesario
    const scrollLeft = container.scrollLeft || 0;
    const scrollTop = container.scrollTop || 0;
    
    const finalCenterX = centerX + scrollLeft;
    const finalCenterY = centerY + scrollTop;
    
    // Actualizar posici�n del bot�n central
    if (centerButton) {
        centerButton.style.left = `${finalCenterX - 22.5}px`;
        centerButton.style.top = `${finalCenterY - 22.5}px`;
    }
    
    // Actualizar posici�n de cada bot�n flotante
    floatingButtons.forEach((button, index) => {
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

// Iniciar animaci�n de apertura
function startOpenAnimationCategorias() {
    // Verificar que el contenedor aún existe
    if (!activeFloatingContainer || !document.contains(activeFloatingContainer)) {
        return;
    }
    
    // Animar botón central primero
    if (centerButton && document.contains(centerButton)) {
        setTimeout(() => {
            if (centerButton && document.contains(centerButton)) {
                centerButton.style.transform = 'scale(1) rotate(360deg)';
                centerButton.style.opacity = '1';
            }
        }, 100);
    }
    
    // Animar botones flotantes con delay escalonado
    floatingButtons.forEach((button, index) => {
        setTimeout(() => {
            if (button && document.contains(button)) {
                button.style.transform = 'scale(1) rotate(0deg)';
                button.style.opacity = '1';
            }
        }, 200 + (index * 100)); // 100ms de delay entre cada botón
    });
    
    // Finalizar animación
    setTimeout(() => {
        isAnimating = false;
    }, 200 + (floatingButtons.length * 100) + 400);
}

// Event listeners animados
function setupAnimatedEventListenersCategorias() {
    // Cerrar al hacer click fuera con animaci�n
    const handleClick = (e) => {
        if (activeFloatingContainer && !activeFloatingContainer.contains(e.target)) {
            closeFloatingActionsAnimatedCategorias();
        }
    };
    
    // Actualizar posiciones en resize
    const handleResize = () => {
        if (activeFloatingContainer) {
            setTimeout(() => {
                updateAnimatedButtonPositionsCategorias();
            }, 100);
        }
    };
    
    // Manejar scroll del contenedor padre
    const handleScroll = () => {
        if (activeFloatingContainer) {
            updateAnimatedButtonPositionsCategorias();
        }
    };
    
    document.addEventListener('click', handleClick);
    window.addEventListener('resize', handleResize, { passive: true });
    
    // Agregar listener de scroll al contenedor padre
    const container = activeFloatingContainer.parentElement;
    if (container) {
        container.addEventListener('scroll', handleScroll, { passive: true });
    }
    
    // Limpiar listeners cuando se cierre
    activeFloatingContainer.cleanup = () => {
        document.removeEventListener('click', handleClick);
        window.removeEventListener('resize', handleResize);
        if (container) {
            container.removeEventListener('scroll', handleScroll);
        }
    };
}

// Cerrar men� flotante con animaci�n avanzada
function closeFloatingActionsAnimatedCategorias() {
    if (!activeFloatingContainer || isAnimating) return;
    
    // Verificar que el contenedor aún existe en el DOM
    if (!document.contains(activeFloatingContainer)) {
        // Resetear variables si el contenedor ya fue removido
        activeFloatingContainer = null;
        centerButton = null;
        floatingButtons = [];
        activecategoriaId = null;
        isAnimating = false;
        return;
    }
    
    isAnimating = true;
    
    // Limpiar timeout anterior si existe
    if (animationTimeout) {
        clearTimeout(animationTimeout);
    }
    
    // Ocultar tooltip si existe
    hideTooltipCategorias();
    
    // Animar salida de botones flotantes (en orden inverso)
    floatingButtons.forEach((button, index) => {
        setTimeout(() => {
            if (button && document.contains(button)) {
                button.style.transform = 'scale(0) rotate(-180deg)';
                button.style.opacity = '0';
            }
        }, index * 50);
    });
    
    // Animar salida del botón central
    if (centerButton && document.contains(centerButton)) {
        setTimeout(() => {
            centerButton.style.transform = 'scale(0) rotate(-360deg)';
            centerButton.style.opacity = '0';
        }, floatingButtons.length * 50 + 100);
    }
    
    // Limpiar después de que termine la animación
    animationTimeout = setTimeout(() => {
        if (activeFloatingContainer && document.contains(activeFloatingContainer)) {
            if (activeFloatingContainer.cleanup) {
                activeFloatingContainer.cleanup();
            }
            
            activeFloatingContainer.remove();
        }
        
        activeFloatingContainer = null;
        centerButton = null;
        floatingButtons = [];
        activecategoriaId = null;
        isAnimating = false;
    }, floatingButtons.length * 50 + 400);
}

// Mantener compatibilidad con funci�n anterior
function closeFloatingActionsCategorias() {
    closeFloatingActionsAnimatedCategorias();
}

// Funci�n para forzar el cierre con retraso del men� flotante
function forceCloseFloatingActionsCategorias() {
    // Agregar un retraso antes del cierre forzado
    setTimeout(() => {
        // Limpiar cualquier timeout pendiente
        if (animationTimeout) {
            clearTimeout(animationTimeout);
            animationTimeout = null;
        }
        
        // Ocultar tooltip inmediatamente
        hideTooltipCategorias();
        
        // Si hay un contenedor activo, eliminarlo inmediatamente
        if (activeFloatingContainer) {
            try {
                // Limpiar eventos si existen
                if (activeFloatingContainer.cleanup) {
                    activeFloatingContainer.cleanup();
                }
                
                // Remover del DOM inmediatamente
                activeFloatingContainer.remove();
            } catch (e) {
            }
            
            // Resetear variables globales
            activeFloatingContainer = null;
            centerButton = null;
            floatingButtons = [];
            activecategoriaId = null;
            isAnimating = false;
        }
        
        // Asegurarse de que no queden elementos flotantes hu�rfanos
        const orphanedContainers = document.querySelectorAll('.animated-floating-container');
        orphanedContainers.forEach(container => {
            try {
                container.remove();
            } catch (e) {
                console.warn('Error eliminando contenedor hu�rfano:', e);
            }
        });
    }, 320); // Retraso de 150ms antes del cierre forzado
}

// ============ SISTEMA DE MODALES ============



// Funci�n para exportar Estados
async function exportcategorias() {
    
    try {
        // // showNotification('Preparando exportaci�n...', 'info');
        
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
            a.download = `categorias_${new Date().toISOString().split('T')[0]}.csv`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
            
            // // showNotification('Estados exportados exitosamente', 'success');
        } else {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        
    } catch (error) {
        
        // Generar CSV del lado del cliente como fallback
        if (categorias && categorias.length > 0) {
            generateClientSideCSV();
        } else {
            // // showNotification('No hay categorias para exportar', 'warning');
        }
    }
}

// Funci�n para generar CSV del lado del cliente
function generateClientSideCSV() {
    const headers = ['ID', 'C�digo', 'Nombre', 'Descripci�n', 'Estado', 'Fecha Creaci�n'];
    let csvContent = headers.join(',') + '\n';
    
    categorias.forEach(categoria => {
        const row = [
            categoria.id_categoria || '',
            categoria.codigo_categoria || '',
            `"${(categoria.nombre_categoria || '').replace(/"/g, '""')}"`,
            `"${(categoria.descripcion_categoria || '').replace(/"/g, '""')}"`,
            categoria.estado_categoria === 'activo' ? 'Activo' : 'Inactivo',
            categoria.fecha_creacion_categoria || ''
        ];
        csvContent += row.join(',') + '\n';
    });
    
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.style.display = 'none';
    a.href = url;
    a.download = `categorias_${new Date().toISOString().split('T')[0]}.csv`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
    
    // showNotification('categorias exportadas exitosamente', 'success');
}

// Funci�n para mostrar reporte de cantidad_categorias
function showcategoriasReport() {
    // Implementar modal de reporte de categorias
    // showNotification('Reporte de categorias - Funcionalidad en desarrollo', 'info');
}

// Funci�n para limpiar b�squeda con animaci�n
function clearcategoriaSearch() {
    if (typeof $ !== 'undefined') {
        const searchInput = $('#search-categorias');
        searchInput.val('').focus();
        
        // Animaci�n visual
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
    
    filtercategoriasCategorias();
}

// Funci�n para limpiar todos los filtros con efectos visuales
function clearAllcategoriaFilters() {
    if (typeof $ !== 'undefined') {
        // Limpiar todos los campos con jQuery
        $('#search-categorias').val('');
        $('#filter-categoria').val('');
        $('#filter-status').val('');
        $('#filter-cantidad_categorias').val('');
        
        // Efecto visual de limpieza
        $('.module-filters').addClass('filters-clearing');
        
        setTimeout(() => {
            $('.module-filters').removeClass('filters-clearing');
        }, 400);
    } else {
        // Fallback vanilla JS
        const elements = [
            'search-categorias',
            'filter-estado',
            'filter-status',
            'filter-cantidad_categorias'
        ];
        
        elements.forEach(id => {
            const element = document.getElementById(id);
            if (element) element.value = '';
        });
    }
    
    // Mostrar notificaci�n
    // showNotification('Filtros limpiados', 'info');
    
    filtercategoriasCategorias();
}

// Funci�n para acciones en lote
async function handleBulkcategoriaAction(action) {
    const selectedcategorias = getSelectedcategorias();
    
    if (selectedcategorias.length === 0) {
        // // showNotification('Por favor selecciona al menos una categoria', 'warning');
        return;
    }    
    const confirmMessage = `�Est�s seguro de ${action} ${selectedcategorias.length} categoria(s)?`;
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
                throw new Error('Acci�n no v�lida');
        }
        
        const response = await fetch(`${CONFIG.apiUrl}${endpoint}`, {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ ids: selectedcategorias })
        });
        
        const result = await response.json();
        
        if (response.ok) {
            // showNotification(`${action} completado para ${selectedcategorias.length} categoria(s)`, 'success');
            loadcategoriasData(); // Recargar lista
            clearcategoriaSelection();
        } else {
            throw new Error(result.message || 'Error en operaci�n en lote');
        }
        
    } catch (error) {
        // showNotification('Error: ' + error.message, 'error');
    }
}

// Funci�n para toggle select all
function toggleSelectAllcategorias(checkbox) {
    
    const categoriaCheckboxes = document.querySelectorAll('input[name="categoria_select"]');
    categoriaCheckboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
    
    updateBulkActionButtons();
}

// Funci�n para ver Estado (wrapper que llama al parent)
function viewcategoria(id) {
    if (!id || typeof id === 'undefined' || id === null) {
        console.error('? ID inv�lido para ver:', id);
        return;
    }
    
    // CERRAR MEN� FLOTANTE antes de abrir modal
    closeFloatingActionsAnimatedCategorias();
    
    // Usar la nueva funci�n modal
    showViewcategoriaModal(id);
}

// ===== FUNCI�N GLOBAL PARA CERRAR BURBUJA DE cantidad_categorias =====
function closecantidad_categoriasBubble() {
    const existingBubbles = document.querySelectorAll('.cantidad_categorias-update-bubble');
    const existingOverlays = document.querySelectorAll('.cantidad_categorias-bubble-overlay');
    
    existingBubbles.forEach(bubble => {
        // Limpiar listeners si existen
        if (bubble.updatePositionListener) {
            window.removeEventListener('scroll', bubble.updatePositionListener, true);
            window.removeEventListener('resize', bubble.updatePositionListener);
        }
        
        // Animaci�n de salida
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
}

// ===== FUNCIONES DE MODALES PARA categoriaS =====

function showCreatecategoriaModal() {
    // CERRAR MEN� FLOTANTE si est� abierto
    if (activeFloatingContainer) {
        closeFloatingActionsAnimatedCategorias();
    }
    
    // Crear overlay
    let overlay = document.getElementById('product-modal-overlay');
    if (overlay) {
        overlay.remove();
    }
    
    overlay = document.createElement('div');
    overlay.id = 'product-modal-overlay';
    overlay.className = 'modal-overlay';
    overlay.innerHTML = `
        <div id="modal-content-wrapper">
            <div class="loading-spinner">
                <div class="spinner"></div>
                <p>Cargando formulario...</p>
            </div>
        </div>
    `;
    
    document.body.appendChild(overlay);
    document.body.classList.add('modal-open');
    
    requestAnimationFrame(() => {
        overlay.classList.add('show');
    });
    
    // Cargar modal
    fetch('app/views/admin/categoria_modal.php?action=create')
        .then(response => response.text())
        .then(html => {
            const wrapper = overlay.querySelector('#modal-content-wrapper');
            if (wrapper) {
                wrapper.outerHTML = html;
                
                // Ejecutar scripts del modal
                const scripts = overlay.querySelectorAll('script');
                scripts.forEach(script => {
                    if (script.textContent && script.textContent.trim()) {
                        try {
                            eval(script.textContent);
                        } catch (err) {
                            console.error('Error ejecutando script:', err);
                        }
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error cargando modal:', error);
            overlay.querySelector('#modal-content-wrapper').innerHTML = `
                <div style="padding: 20px; text-align: center; color: white;">
                    <h3>Error al cargar modal</h3>
                    <p>${error.message}</p>
                </div>
            `;
        });
}

function showEditcategoriaModal(id) {
    // CERRAR MEN� FLOTANTE si est� abierto
    if (activeFloatingContainer) {
        closeFloatingActionsAnimatedCategorias();
    }
    
    let overlay = document.getElementById('product-modal-overlay');
    if (overlay) {
        overlay.remove();
    }
    
    overlay = document.createElement('div');
    overlay.id = 'product-modal-overlay';
    overlay.className = 'modal-overlay';
    overlay.innerHTML = `
        <div id="modal-content-wrapper">
            <div class="loading-spinner">
                <div class="spinner"></div>
                <p>Cargando categoria...</p>
            </div>
        </div>
    `;
    
    document.body.appendChild(overlay);
    document.body.classList.add('modal-open');
    
    requestAnimationFrame(() => {
        overlay.classList.add('show');
    });
    
    fetch(`app/views/admin/categoria_modal.php?action=edit&id=${id}`)
        .then(response => response.text())
        .then(html => {
            const wrapper = overlay.querySelector('#modal-content-wrapper');
            if (wrapper) {
                wrapper.outerHTML = html;
                
                const scripts = overlay.querySelectorAll('script');
                scripts.forEach(script => {
                    if (script.textContent && script.textContent.trim()) {
                        try {
                            eval(script.textContent);
                        } catch (err) {
                            console.error('Error ejecutando script:', err);
                        }
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error cargando modal:', error);
        });
}

function showViewcategoriaModal(id) {
    // CERRAR MEN� FLOTANTE si est� abierto
    if (activeFloatingContainer) {
        closeFloatingActionsAnimatedCategorias();
    }
    
    let overlay = document.getElementById('product-modal-overlay');
    if (overlay) {
        overlay.remove();
    }
    
    overlay = document.createElement('div');
    overlay.id = 'product-modal-overlay';
    overlay.className = 'modal-overlay';
    overlay.innerHTML = `
        <div id="modal-content-wrapper">
            <div class="loading-spinner">
                <div class="spinner"></div>
                <p>Cargando categoria...</p>
            </div>
        </div>
    `;
    
    document.body.appendChild(overlay);
    document.body.classList.add('modal-open');
    
    requestAnimationFrame(() => {
        overlay.classList.add('show');
    });
    
    fetch(`app/views/admin/categoria_modal.php?action=view&id=${id}`)
        .then(response => response.text())
        .then(html => {
            const wrapper = overlay.querySelector('#modal-content-wrapper');
            if (wrapper) {
                wrapper.outerHTML = html;
                
                // Buscar el modal de ver categoria y agregarle la clase "show" con animaci�n
                const viewModal = overlay.querySelector('.product-view-modal, #categoriaViewModal');
                if (viewModal) {
                    // Agregar clase show despu�s de un frame para activar la animaci�n
                    requestAnimationFrame(() => {
                        viewModal.classList.add('show');
                    });
                }
                
                const scripts = overlay.querySelectorAll('script');
                scripts.forEach(script => {
                    if (script.textContent && script.textContent.trim()) {
                        try {
                            eval(script.textContent);
                        } catch (err) {
                            console.error('Error ejecutando script:', err);
                        }
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error cargando modal:', error);
        });
}

function closecategoriaModal() {
    console.log('?? closecategoriaModal() llamado');
    
    // BUSCAR MODAL DE VER (puede estar directo en body o dentro de overlay)
    const viewModalDirect = document.querySelector('body > .product-view-modal, body > #categoriaViewModal');
    const overlay = document.getElementById('product-modal-overlay');
    
    // CASO 1: Modal de VER directo en body (sin overlay)
    if (viewModalDirect && !overlay) {
        console.log('? Cerrando modal de VER (directo en body)...');
        
        // Remover inmediatamente sin animaci�n
        viewModalDirect.remove();
        document.body.classList.remove('modal-open');
        console.log('? Modal de VER removido del DOM');
        
        return;
    }
    
    // CASO 2: Modal dentro de overlay
    if (overlay) {
        console.log('? Cerrando modal con overlay...');
        
        // Remover inmediatamente sin animaci�n
        overlay.remove();
        document.body.classList.remove('modal-open');
        console.log('? Overlay removido del DOM');
        
        return;
    }
    
    // CASO 3: No se encontr� ning�n modal
    console.warn('?? No se encontr� ning�n modal abierto para cerrar');
    document.body.classList.remove('modal-open');
}

// Funci�n para editar Estado
async function editcategoria(id) {
    if (!id || typeof id === 'undefined' || id === null) {
        console.error('? ID inv�lido para editar:', id);
        return;
    }
    
    // CERRAR MEN� FLOTANTE antes de abrir modal
    closeFloatingActionsAnimatedCategorias();
    
    // Usar la nueva funci�n modal
    showEditcategoriaModal(id);
}

// Funci�n para actualizar cantidad_categorias - MEJORADA CON BURBUJA SIN BOTONES
function updatecantidad_categorias(id, currentcantidad_categorias, event) {
    // VERIFICAR SI YA EXISTE UNA BURBUJA ABIERTA PARA ESTE Estado (TOGGLE)
    const existingBubble = document.querySelector(`.cantidad_categorias-update-bubble[data-categoria-id="${id}"]`);
    if (existingBubble) {
        closecantidad_categoriasBubble();
        return; // SALIR - No abrir de nuevo
    }
    
    // CERRAR MEN� FLOTANTE SI EST� ABIERTO (sin bloquear futuros men�s)
    if (activeFloatingContainer) {
        // Cerrar con animaci�n
        closeFloatingActionsAnimatedCategorias();
    }
    
    // Forzar eliminaci�n de cualquier men� flotante residual
    const allFloatingMenus = document.querySelectorAll('.animated-floating-container');
    allFloatingMenus.forEach(menu => {
        if (menu && menu.parentNode) {
            menu.remove();
        }
    });
    
    // Resetear variables globales del men� flotante
    activeFloatingContainer = null;
    activecategoriaId = null;
    isAnimating = false;
    if (animationTimeout) {
        clearTimeout(animationTimeout);
        animationTimeout = null;
    }
    
    // Eliminar cualquier burbuja existente (de otros Estados)
    closecantidad_categoriasBubble();
    
    // Crear overlay SIN bloquear scroll - solo para detectar clicks
    const overlay = document.createElement('div');
    overlay.className = 'cantidad_categorias-bubble-overlay';
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
    
    // Crear burbuja de cantidad_categorias - PEQUE�A (50x50px) estilo botones flotantes, expandible hasta 3 d�gitos
    const cantidad_categoriasBubble = document.createElement('div');
    cantidad_categoriasBubble.className = 'cantidad_categorias-update-bubble';
    cantidad_categoriasBubble.setAttribute('data-categoria-id', id); // Agregar ID del Estado para identificar
    cantidad_categoriasBubble.innerHTML = `
        <input type="number" 
               id="cantidad_categoriasInput" 
               value="${currentcantidad_categorias}" 
               min="0" 
               max="999"
               class="cantidad_categorias-input-circle"
               placeholder="0"
               autocomplete="off"
               maxlength="3"
               style="border: none !important; outline: none !important; box-shadow: none !important; text-decoration: none !important; -webkit-appearance: none !important; border-bottom: none !important;">
    `;
    
    // Encontrar el bot�n que dispar� la acci�n (puede ser btn-menu de tabla o btn-cantidad_categorias de grid)
    // Primero intentar obtenerlo del evento
    let triggerButton = null;
    let isGridView = false;
    
    if (event) {
        // Intentar desde currentTarget
        triggerButton = event.currentTarget;
        
        // Verificar si es un bot�n de la vista grid
        if (triggerButton && triggerButton.classList.contains('categoria-card-btn')) {
            isGridView = true;
        }
        // Si es un bot�n flotante, ignorar y buscar el bot�n real
        else if (triggerButton && triggerButton.classList.contains('animated-floating-button')) {
            triggerButton = null; // Resetear para buscar el bot�n correcto
        }
        // Si es el btn-menu de la tabla
        else if (triggerButton && triggerButton.classList.contains('btn-menu')) {
            isGridView = false;
        }
    }
    
    // Si a�n no tenemos el bot�n, buscarlo en el DOM por el ID del Estado
    if (!triggerButton) {
        // Primero buscar en vista grid
        const categoriaCard = document.querySelector(`.categoria-card[data-categoria-id="${id}"]`);
        if (categoriaCard) {
            triggerButton = categoriaCard.querySelector('.btn-cantidad_categorias');
            if (triggerButton) {
                isGridView = true;
            }
        }
        
        // Si no est� en grid, buscar en la tabla
        if (!triggerButton) {
            const categoriaRow = document.querySelector(`tr[data-categoria-id="${id}"]`);
            if (categoriaRow) {
                triggerButton = categoriaRow.querySelector('.btn-menu');
                if (triggerButton) {
                    isGridView = false;
                }
            }
        }
    }
    
    // �ltimo recurso: buscar por atributo onclick en la tabla
    if (!triggerButton) {
        triggerButton = document.querySelector(`[onclick*="showActionMenuCategorias(${id}"]`);
        if (triggerButton) {
            isGridView = false;
        }
    }
    
    if (!triggerButton) {
        console.error('? No se encontr� el bot�n para el Estado', id);
        return;
    }
    
    // USAR POSICI�N FIXED (viewport) como los botones flotantes
    const triggerRect = triggerButton.getBoundingClientRect();
    
    // Calcular centro del bot�n en coordenadas del viewport
    const centerX = triggerRect.left + (triggerRect.width / 2);
    const centerY = triggerRect.top + (triggerRect.height / 2);
    
    // Posici�n seg�n la vista
    const bubbleSize = 40;
    const radius = 65;
    let angle;
    
    if (isGridView) {
        // En vista grid: arriba del bot�n (�ngulo 270� = -p/2)
        angle = -Math.PI / 2; // 270� = arriba
    } else {
        // En vista tabla: a la izquierda del bot�n (�ngulo 180� = p)
        angle = Math.PI; // 180� = izquierda
    }
    
    // Calcular posici�n con POSITION FIXED (coordenadas del viewport)
    const bubbleX = centerX + (Math.cos(angle) * radius) - (bubbleSize / 2);
    const bubbleY = centerY + (Math.sin(angle) * radius) - (bubbleSize / 2);
    
    // Aplicar estilos - POSICI�N FIXED (viewport) como botones flotantes - Se expande seg�n d�gitos
    cantidad_categoriasBubble.style.cssText = `
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
    
    // Guardar referencia al bot�n para recalcular posici�n en scroll/resize
    cantidad_categoriasBubble.triggerButton = triggerButton;
    cantidad_categoriasBubble.isGridView = isGridView;
    
    // Estilos para el input - SIN SUBRAYADO y con expansi�n ovalada
    const style = document.createElement('style');
    style.id = 'cantidad_categorias-bubble-styles';
    style.textContent = `
        .cantidad_categorias-update-bubble {
            white-space: nowrap;
        }
        
        .cantidad_categorias-input-circle {
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
        
        .cantidad_categorias-input-circle:focus,
        .cantidad_categorias-input-circle:active,
        .cantidad_categorias-input-circle:hover,
        .cantidad_categorias-input-circle:visited,
        .cantidad_categorias-input-circle:focus-visible,
        .cantidad_categorias-input-circle:focus-within {
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
        
        .cantidad_categorias-input-circle::-webkit-outer-spin-button,
        .cantidad_categorias-input-circle::-webkit-inner-spin-button {
            -webkit-appearance: none !important;
            margin: 0 !important;
            display: none !important;
        }
        
        .cantidad_categorias-input-circle[type=number] {
            -moz-appearance: textfield !important;
        }
        
        .cantidad_categorias-input-circle::placeholder {
            color: rgba(255, 255, 255, 0.5) !important;
            font-size: 18px !important;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2) !important;
        }
        
        /* Forzar eliminaci�n de cualquier estilo de Chrome/Edge */
        input[type=number].cantidad_categorias-input-circle::-webkit-textfield-decoration-container {
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
    const oldStyle = document.getElementById('cantidad_categorias-bubble-styles');
    if (oldStyle) oldStyle.remove();
    document.head.appendChild(style);
    
    // Agregar overlay al body (sin bloquear scroll)
    document.body.appendChild(overlay);
    
    // Agregar burbuja al BODY (position fixed)
    document.body.appendChild(cantidad_categoriasBubble);
    
    // Actualizar posici�n en scroll/resize (con position fixed)
    const updateBubblePosition = () => {
        if (!cantidad_categoriasBubble || !cantidad_categoriasBubble.triggerButton) return;
        
        const triggerRect = cantidad_categoriasBubble.triggerButton.getBoundingClientRect();
        
        const centerX = triggerRect.left + triggerRect.width / 2;
        const centerY = triggerRect.top + triggerRect.height / 2;
        
        const bubbleSize = 40;
        const radius = 65;
        
        // Usar el �ngulo guardado seg�n la vista
        const angle = cantidad_categoriasBubble.isGridView ? (-Math.PI / 2) : Math.PI;
        
        const bubbleX = centerX + Math.cos(angle) * radius - bubbleSize / 2;
        const bubbleY = centerY + Math.sin(angle) * radius - bubbleSize / 2;
        
        if (cantidad_categoriasBubble && cantidad_categoriasBubble.style) {
            cantidad_categoriasBubble.style.left = bubbleX + 'px';
            cantidad_categoriasBubble.style.top = bubbleY + 'px';
        }
    };
    
    // Listener para scroll/resize
    window.addEventListener('scroll', updateBubblePosition, true);
    window.addEventListener('resize', updateBubblePosition);
    cantidad_categoriasBubble.updatePositionListener = updateBubblePosition;
    
    // Activar animaci�n de entrada con reflow
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            if (cantidad_categoriasBubble && cantidad_categoriasBubble.style) {
                cantidad_categoriasBubble.style.transform = 'scale(1)';
                cantidad_categoriasBubble.style.opacity = '1';
            }
        });
    });
    
    // Focus en el input
    setTimeout(() => {
        const input = cantidad_categoriasBubble?.querySelector('#cantidad_categoriasInput');
        if (input) {
            input.focus();
            input.select();
            
            // Ajustar ancho de la burbuja seg�n el n�mero de d�gitos (expansi�n ovalada)
            const adjustBubbleWidth = () => {
                const value = input.value.toString();
                const numDigits = value.length || 1;
                
                // Ancho base 40px, +12px por cada d�gito extra
                let newWidth = 40;
                if (numDigits === 2) {
                    newWidth = 52; // M�s ovalado para 2 d�gitos
                } else if (numDigits >= 3) {
                    newWidth = 64; // M�s ovalado para 3 d�gitos
                }
                
                cantidad_categoriasBubble.style.width = newWidth + 'px';
                
                // Recalcular posici�n para centrar la burbuja expandida
                const triggerRect = triggerButton.getBoundingClientRect();
                const centerX = triggerRect.left + (triggerRect.width / 2);
                const centerY = triggerRect.top + (triggerRect.height / 2);
                const radius = 65;
                const angle = isGridView ? (-Math.PI / 2) : Math.PI;
                
                const bubbleX = centerX + (Math.cos(angle) * radius) - (newWidth / 2);
                const bubbleY = centerY + (Math.sin(angle) * radius) - (40 / 2);
                
                cantidad_categoriasBubble.style.left = bubbleX + 'px';
                cantidad_categoriasBubble.style.top = bubbleY + 'px';
            };
            
            // Limitar a 3 d�gitos
            input.addEventListener('input', function(e) {
                // Eliminar cualquier car�cter no num�rico
                this.value = this.value.replace(/[^0-9]/g, '');
                
                // Limitar a 3 d�gitos (m�ximo 999)
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
    
    // Funci�n para guardar
    function savecantidad_categorias() {
        if (!cantidad_categoriasBubble) {
            console.error('? cantidad_categoriasBubble no existe');
            return;
        }
        
        const input = cantidad_categoriasBubble.querySelector('#cantidad_categoriasInput');
        if (!input) {
            console.error('? input no existe');
            return;
        }
        
        const newcantidad_categorias = parseInt(input.value);
        
        if (isNaN(newcantidad_categorias) || newcantidad_categorias < 0 || newcantidad_categorias > 999) {
            // Animaci�n de error - shake sin afectar el scale
            const originalTransform = cantidad_categoriasBubble.style.transform;
            cantidad_categoriasBubble.style.animation = 'shake 0.5s ease-in-out';
            input.style.color = '#fee2e2';
            input.style.textShadow = '0 0 10px rgba(239, 68, 68, 0.8)';
            
            setTimeout(() => {
                if (cantidad_categoriasBubble) {
                    cantidad_categoriasBubble.style.animation = '';
                    cantidad_categoriasBubble.style.transform = originalTransform;
                }
                if (input) {
                    input.style.color = '';
                    input.style.textShadow = '';
                }
            }, 500);
            return;
        }
        
        // Animaci�n de salida
        cantidad_categoriasBubble.style.transform = 'scale(0)';
        cantidad_categoriasBubble.style.opacity = '0';
        
        // Limpiar click outside handler
        if (clickOutsideHandler) {
            document.removeEventListener('click', clickOutsideHandler);
            clickOutsideHandler = null;
        }
        
        // Llamada AJAX para actualizar cantidad_categorias
        const formData = new FormData();
        formData.append('action', 'update_cantidad_categorias');
        formData.append('id', id);
        formData.append('cantidad_categorias', newcantidad_categorias);
        
        fetch(`${CONFIG.apiUrl}`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Mostrar notificaci�n de �xito
                if (typeof showNotification === 'function') {
                    // showNotification(`? cantidad_categorias actualizado a ${newcantidad_categorias} unidades`, 'success');
                }
                
                // Usar actualizaci�n suave si est� disponible
                if (window.categoriaSmoothTableUpdater && data.categoria) {
                    window.categoriaSmoothTableUpdater.updateSinglecategoria(data.categoria);
                } else {
                    // Actualizar lista inmediatamente
                    loadcategorias(true);
                }
                
                // Cerrar burbuja y overlay
                setTimeout(() => {
                    if (overlay && overlay.parentNode) overlay.remove();
                    if (cantidad_categoriasBubble && cantidad_categoriasBubble.parentNode) cantidad_categoriasBubble.remove();
                }, 400);
            } else {
                if (typeof showNotification === 'function') {
                    // showNotification('? Error al actualizar cantidad_categorias', 'error');
                }
                if (overlay && overlay.parentNode) overlay.remove();
                if (cantidad_categoriasBubble && cantidad_categoriasBubble.parentNode) cantidad_categoriasBubble.remove();
            }
        })
        .catch(error => {
            if (typeof showNotification === 'function') {
                // showNotification('? Error de conexi�n', 'error');
            }
            if (overlay && overlay.parentNode) overlay.remove();
            if (cantidad_categoriasBubble && cantidad_categoriasBubble.parentNode) cantidad_categoriasBubble.remove();
        });
    }
    
    // Variable para guardar el handler del click outside
    let clickOutsideHandler = null;
    
    // Funci�n para cerrar sin guardar
    function closeBubble() {
        if (!cantidad_categoriasBubble) return;
        
        // Limpiar listeners
        if (cantidad_categoriasBubble.updatePositionListener) {
            window.removeEventListener('scroll', cantidad_categoriasBubble.updatePositionListener, true);
            window.removeEventListener('resize', cantidad_categoriasBubble.updatePositionListener);
        }
        
        // Limpiar click outside handler
        if (clickOutsideHandler) {
            document.removeEventListener('click', clickOutsideHandler);
            clickOutsideHandler = null;
        }
        
        cantidad_categoriasBubble.style.transform = 'scale(0)';
        cantidad_categoriasBubble.style.opacity = '0';
        setTimeout(() => {
            if (overlay && overlay.parentNode) overlay.remove();
            if (cantidad_categoriasBubble && cantidad_categoriasBubble.parentNode) cantidad_categoriasBubble.remove();
        }, 400);
    }
    
    // Eventos del input
    const input = cantidad_categoriasBubble.querySelector('#cantidad_categoriasInput');
    
    if (!input) {
        console.error('? No se encontr� el input de cantidad_categorias');
        return;
    }
    
    // Guardar con Enter
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            savecantidad_categorias();
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
            savecantidad_categorias(); // Guardar al hacer click fuera
        }
    });
    
    // MANTENER pointer-events: none en overlay para permitir scroll
    // El click se detectar� solo cuando hagamos click en el �rea del overlay
    
    // Prevenir que clicks en la burbuja cierren el overlay
    cantidad_categoriasBubble.addEventListener('click', function(e) {
        e.stopPropagation();
    });
    
    // Cerrar al hacer click fuera (usando evento en document)
    clickOutsideHandler = function(e) {
        // Si el click no es en la burbuja ni en el overlay, guardar
        if (!cantidad_categoriasBubble.contains(e.target) && e.target !== cantidad_categoriasBubble) {
            savecantidad_categorias();
            document.removeEventListener('click', clickOutsideHandler);
            clickOutsideHandler = null;
        }
    };
    
    // Agregar listener despu�s de un peque�o delay para evitar que se cierre inmediatamente
    setTimeout(() => {
        document.addEventListener('click', clickOutsideHandler);
    }, 100);
}

// Funci�n para toggle status
async function togglecategoriaStatus(id, currentStatus) {
    
    const newStatus = !currentStatus;
    const action = newStatus ? 'activar' : 'desactivar';
    
    if (!confirm(`�Est�s seguro de ${action} este Estado?`)) return;
    
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
            // showNotification(`Estado ${action} exitosamente`, 'success');
            loadcategorias(); // Recargar lista
        } else {
            throw new Error(result.message || 'Error al cambiar estado');
        }
        
    } catch (error) {
        // showNotification('Error: ' + error.message, 'error');
    }
}

// Funci�n para cambiar estado de la categoria (activo/inactivo)
async function changecategoriaEstado(id) {
    try {
        // CERRAR MEN� FLOTANTE antes de cambiar estado
        closeFloatingActionsAnimatedCategorias();
        
        // Obtener estado actual de la categoria
        const response = await fetch(`${CONFIG.apiUrl}?action=get&id=${id}`);
        const result = await response.json();
        
        if (!response.ok || !result.success) {
            console.error('Error al obtener datos de la categoria');
            return;
        }
        
        const categoria = result.data || result.categoria;
        const currentEstado = categoria.estado_categoria;
        const newEstado = currentEstado === 'activo' ? 'inactivo' : 'activo';
        
        // Cambiar estado directamente sin confirmaci�n
        const updateResponse = await fetch(`${CONFIG.apiUrl}?action=change_estado`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `id_categoria=${id}&estado=${newEstado}`
        });
        
        const updateResult = await updateResponse.json();
        
        if (updateResponse.ok && updateResult.success) {
            // Usar actualizaci�n suave si est� disponible
            const categoriaData = updateResult.data;
            
            // ? CORRECCI�N: Usar categoriaSmoothTableUpdater espec�fico para categorias
            if (window.categoriaSmoothTableUpdater && categoriaData) {
                // Asegurar que el ID sea n�mero
                const categoriaId = parseInt(categoriaData.id_categoria || id);
                
                window.categoriaSmoothTableUpdater.updateSinglecategoria(categoriaId, categoriaData)
                    .then(() => {
                        // Tabla actualizada sin recargar p�gina
                    })
                    .catch(err => {
                        console.error('? Error en updateSinglecategoria:', err);
                        loadcategoriasData();
                    });
            } else {
                loadcategoriasData();
            }
        } else {
            console.error('Error al cambiar estado:', updateResult.error);
        }
        
    } catch (error) {
        console.error('Error en changecategoriaEstado:', error.message);
    }
}


// ============ FUNCIONES DE PAGINACI�N ============

function goToFirstPagecategorias() {
    if (currentPage > 1) {
        currentPage = 1;
        loadcategoriasData();
    }
}

function previousPagecategorias() {
    if (currentPage > 1) {
        currentPage--;
        loadcategoriasData();
    }
}

function nextPagecategorias() {
    if (currentPage < totalPages) {
        currentPage++;
        loadcategoriasData();
    }
}

function goToLastPagecategorias() {
    if (currentPage < totalPages) {
        currentPage = totalPages;
        loadcategoriasData();
    }
}

// ============ FUNCIONES AUXILIARES ============

// Funci�n para obtener Estados seleccionados
function getSelectedcategorias() {
    const checkboxes = document.querySelectorAll('input[name="categoria_select"]:checked');
    return Array.from(checkboxes).map(cb => parseInt(cb.value));
}

// Funci�n para limpiar selecci�n de Estados
function clearcategoriaSelection() {
    const checkboxes = document.querySelectorAll('input[name="categoria_select"]');
    checkboxes.forEach(cb => cb.checked = false);
    
    const selectAll = document.querySelector('input[type="checkbox"][onchange*="toggleSelectAllcategorias"]');
    if (selectAll) selectAll.checked = false;
    
    updateBulkActionButtons();
}

// Funci�n para actualizar botones de acciones en lote
function updateBulkActionButtons() {
    const selected = getSelectedcategorias();
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
// Todas las funciones de notificaci�n han sido desactivadas por solicitud del usuario

// ============ INICIALIZACI�N ============

// Funci�n para actualizar contador de resultados
function updateResultsCounter(showing, total) {
    const showingStartEl = document.getElementById('showing-start-categorias');
    const showingEndEl = document.getElementById('showing-end-Estados');
    const totalcategoriasEl = document.getElementById('total-Estados');
    
    if (showingStartEl) showingStartEl.textContent = showing > 0 ? 1 : 0;
    if (showingEndEl) showingEndEl.textContent = showing;
    if (totalcategoriasEl) totalcategoriasEl.textContent = total;
}

// ===== BANDERA DE INICIALIZACI�N �NICA =====
window.categoriasModuleInitialized = window.categoriasModuleInitialized || false;

// ?? ARRAY PARA RASTREAR TIMEOUTS PENDIENTES
if (!window.categoriasPendingTimeouts) {
    window.categoriasPendingTimeouts = [];
}

// ?? FUNCI�N PARA CANCELAR TODOS LOS TIMEOUTS PENDIENTES
window.cancelcategoriasPendingTimeouts = function() {
    console.log('?? Cancelando', window.categoriasPendingTimeouts.length, 'timeouts pendientes de categorias');
    window.categoriasPendingTimeouts.forEach(timeoutId => {
        clearTimeout(timeoutId);
    });
    window.categoriasPendingTimeouts = [];
};

// Funci�n de inicializaci�n principal
function initializecategoriasModule() {
    console.log('?? initializecategoriasModule() llamado');
    
    // ?? VERIFICACI�N CR�TICA: Solo inicializar si estamos en la secci�n de categorias
    const categoriasPane = document.getElementById('categorias');
    if (!categoriasPane || !categoriasPane.classList.contains('active')) {
        console.warn('?? Secci�n de categorias no est� activa, abortando inicializaci�n');
        return;
    }
    
    // ?? PREVENIR INICIALIZACI�N M�LTIPLE
    if (window.categoriasModuleInitialized) {
        console.log('?? M�dulo ya inicializado, saltando...');
        return;
    }
    
    // ?? CANCELAR CUALQUIER TIMEOUT PENDIENTE ANTERIOR
    if (typeof window.cancelcategoriasPendingTimeouts === 'function') {
        window.cancelcategoriasPendingTimeouts();
    }
    
    // Asegurar que categoria_CONFIG est� inicializado
    if (typeof window.categoria_CONFIG === 'undefined' || !window.categoria_CONFIG.apiUrl) {
        initializecategoriaConfigCategorias();
    }

    // ? SIMPLIFICADO: En lugar de esperar al DOM con m�ltiples timeouts,
    // solo verificar que los elementos existen y cargar directamente
    console.log('?? Verificando disponibilidad del DOM...');
    
    const categoriasContent = document.getElementById('categorias-content');
    const categoriasTable = document.querySelector('.categorias-table');
    const tbody = document.getElementById('categorias-table-body');
    
    console.log('? Estado del DOM:');
    console.log('  - categorias-content:', !!categoriasContent);
    console.log('  - categorias-table:', !!categoriasTable);
    console.log('  - tbody:', !!tbody);
    
    if (!tbody) {
        console.error('? tbody no est� disponible. El DOM puede no estar completamente cargado.');
        console.error('?? No se cargar�n categorias autom�ticamente. Llame a loadcategoriasData() manualmente si es necesario.');
        // No llamar loadcategoriasData() aqu�, dejar que el usuario lo haga manualmente
    } else {
        console.log('? DOM disponible, cargando categorias...');
        
        // ? RESTAURAR VISTA GUARDADA EN LOCALSTORAGE
        try {
            const savedView = localStorage.getItem('categorias_current_view');
            if (savedView && savedView === 'grid') {
                console.log('?? Restaurando vista guardada:', savedView);
                // Crear grid si no existe
                createGridViewCategorias();
                // Aplicar vista grid
                const gridViewBtn = document.querySelector('[data-view="grid"]');
                const tableViewBtn = document.querySelector('[data-view="table"]');
                const tableContainer = document.querySelector('.data-table-wrapper');
                const gridContainer = document.querySelector('.categorias-grid');
                
                if (gridViewBtn && tableViewBtn && tableContainer && gridContainer) {
                    gridViewBtn.classList.add('active');
                    tableViewBtn.classList.remove('active');
                    tableContainer.style.display = 'none';
                    gridContainer.style.display = 'grid';
                    console.log('? Vista grid restaurada correctamente');
                }
            }
        } catch (e) {
            console.warn('?? Error al restaurar vista:', e);
        }
        
        // SIEMPRE cargar datos cuando se inicializa el módulo
        // Esto asegura que los datos estén frescos después de navegar entre secciones
        console.log('📊 Cargando datos de categorias...');
        loadcategoriasData();
        
        // Inicializar funciones de UI que antes estaban en DOMContentLoaded/load
        if (typeof initializeTableScroll === 'function') {
            initializeTableScroll();
        }
        if (typeof initializeDragScroll === 'function') {
            initializeDragScroll();
        }
    }
    
    // categoriaR COMO INICIALIZADO
    window.categoriasModuleInitialized = true;
    
    // Funci�n de debugging para verificar funciones disponibles
    window.debugcategoriasFunctions = function() {
        const functions = [
            'loadcategorias', 'filtercategorias', 'handleSearchInput', 
            'toggleViewcategorias', 'showActionMenuCategorias', 'editcategoria', 'viewcategoria', 'deletecategoria',
            'togglecategoriaStatus', 'exportcategorias'
        ];
        
        const parentFunctions = ['showEditcategoriaModal', 'showViewcategoriaModal', 'showCreatecategoriaModal'];
        parentFunctions.forEach(func => {

        });
    };
}

// ✅ EXPONER LA FUNCIÓN DE INICIALIZACIÓN GLOBALMENTE
window.initializecategoriasModule = initializecategoriasModule;

// ✅ EJECUTAR INICIALIZACIÓN INMEDIATAMENTE (dentro del eval())
// Esto asegura que se ejecute en el momento correcto, cuando el DOM ya está listo
initializecategoriasModule();

// NO usar timeout fallback - causa problemas de ejecución cruzada entre secciones

// Asegurar que las funciones estén disponibles globalmente de inmediato
window.loadcategoriasData = loadcategoriasData;
// IMPORTANTE: NO sobrescribir window.loadcategorias
// La función loadcategorias() de admin.php debe mantenerse para cargar la vista completa
// window.loadcategorias se usa solo para botones dentro de admin_categoria.php que llaman a loadcategoriasData
window.filtercategorias = filtercategoriasCategorias;
window.filtercategoriasCategorias = filtercategoriasCategorias;
window.handleSearchInput = handlecategoriaSearchInputCategorias;
window.handlecategoriaSearchInput = handlecategoriaSearchInputCategorias;
window.handlecategoriaSearchInputCategorias = handlecategoriaSearchInputCategorias;
window.toggleViewcategorias = toggleViewcategoriasCategorias;
window.toggleViewcategoriasCategorias = toggleViewcategoriasCategorias;
window.showActionMenuCategorias = showActionMenuCategorias;
window.clearcategoriaSearch = clearcategoriaSearch;
window.clearAllcategoriaFilters = clearAllcategoriaFilters;
window.exportcategorias = exportcategorias;
window.showcategoriasReport = showcategoriasReport;
window.showCreatecategoriaModal = showCreatecategoriaModal;
window.showEditcategoriaModal = showEditcategoriaModal;
window.showViewcategoriaModal = showViewcategoriaModal;
window.closecategoriaModal = closecategoriaModal;
window.closeCategoriaModal = closecategoriaModal; // ✅ Alias con mayúscula para compatibilidad con modal
window.getCurrentViewCategorias = getCurrentViewCategorias;
window.updateStatsCategorias = updateStatsCategorias;
window.updatePaginationInfoCategorias = updatePaginationInfoCategorias;
window.showSearchLoadingCategorias = showSearchLoadingCategorias;
window.formatearFechaCategorias = formatearFechaCategorias;
window.displaycategoriasCategorias = displaycategoriasCategorias;
window.displaycategoriasGridCategorias = displaycategoriasGridCategorias;
window.createGridViewCategorias = createGridViewCategorias;
window.getcategoriaImageUrlCategorias = getcategoriaImageUrlCategorias;
window.initializecategoriaConfigCategorias = initializecategoriaConfigCategorias;

// ===== FUNCI�N DE LIMPIEZA PARA SISTEMA DE NAVEGACI�N =====
/**
 * Funci�n de limpieza espec�fica para el m�dulo de categorias
 * Se ejecuta autom�ticamente al cambiar de secci�n
 */
window.adminCleanupFunctions = window.adminCleanupFunctions || {};
window.adminCleanupFunctions.categorias = function() {
    try {
        // 1. RESETEAR FLAG DE INICIALIZACI�N (CR�TICO)
        window.categoriasModuleInitialized = false;
        
        // 2. Limpiar variables globales espec�ficas de categorias
        if (typeof window.categorias !== 'undefined') window.categorias = [];
        if (typeof window.activecategoriaId !== 'undefined') window.activecategoriaId = null;
        if (typeof window.activeFloatingContainer !== 'undefined') window.activeFloatingContainer = null;
        if (typeof window.currentcategoriasView !== 'undefined') window.currentcategoriasView = null;
        if (typeof window.isLoading !== 'undefined') window.isLoading = false;
        
        // 3. Cerrar modales de categorias
        const categoriaModals = document.querySelectorAll('.categoria-modal, #categoria-modal');
        categoriaModals.forEach(modal => {
            modal.classList.remove('show');
            setTimeout(() => {
                if (modal.parentNode) {
                    modal.remove();
                }
            }, 300);
        });
        
        // 4. Cerrar menús de acciones flotantes SOLO de categorías (por ID)
        const floatingMenus = document.querySelectorAll('.animated-floating-container[id^="animated-floating-menu-"]');
        floatingMenus.forEach(menu => {
            // Solo eliminar si es de categorías (verificando que existe activeFloatingContainer local)
            if (menu && menu.parentNode && activeFloatingContainer === menu) {
                menu.remove();
            }
        });
        activeFloatingContainer = null;
        activecategoriaId = null;
        isAnimating = false;
        floatingButtons = [];
        centerButton = null;
        
        // 5. Remover overlays residuales
        const overlays = document.querySelectorAll('.delete-modal-overlay, .modal-overlay');
        overlays.forEach(overlay => overlay.remove());
        
        // 6. Limpiar intervalos y timers si existen
        if (typeof window.categoriaRefreshInterval !== 'undefined') {
            clearInterval(window.categoriaRefreshInterval);
            window.categoriaRefreshInterval = null;
        }
        
        console.log('Modulo CATEGORIAS limpiado completamente');
        
    } catch (error) {
        console.warn('?? Error en cleanup de categorias:', error);
    }
};

// Alias para que admin.php pueda llamarlo con el nombre esperado
window.destroyCategoriasModule = window.adminCleanupFunctions.categorias;

// Registrar función de limpieza globalmente
window.editcategoria = editcategoria;
window.viewcategoria = viewcategoria;
window.deletecategoria = deletecategoria;
window.togglecategoriaStatus = togglecategoriaStatus;
window.changecategoriaEstado = changecategoriaEstado;
window.showDeleteConfirmationCategorias = showDeleteConfirmationCategorias;
window.closeDeleteConfirmationCategorias = closeDeleteConfirmationCategorias;
window.setupDeleteModalBackdropCloseCategorias = setupDeleteModalBackdropCloseCategorias;
window.confirmDeleteCategorias = confirmDeleteCategorias;
window.handleBulkcategoriaAction = handleBulkcategoriaAction;
window.createGridViewCategorias = createGridViewCategorias;
window.displaycategoriasGrid = displaycategoriasGridCategorias;
window.displaycategoriasGridCategorias = displaycategoriasGridCategorias;
window.closeFloatingActionsCategorias = closeFloatingActionsCategorias;
window.closeFloatingActionsAnimatedCategorias = closeFloatingActionsAnimatedCategorias;
window.createAnimatedFloatingContainerCategorias = createAnimatedFloatingContainerCategorias;
window.updateAnimatedButtonPositionsCategorias = updateAnimatedButtonPositionsCategorias;
window.forceCloseFloatingActionsCategorias = forceCloseFloatingActionsCategorias;
window.showImageFullSize = showImageFullSize;
window.getCurrentView = getCurrentViewCategorias;

// ============ FUNCIONES DE ESTADO PARA PRESERVACI�N ============

// Funci�n para obtener el t�rmino de b�squeda actual
window.getSearchTerm = function() {
    const searchInput = document.getElementById('search-categorias');
    return searchInput ? searchInput.value.trim() : '';
};

// Funci�n para obtener los filtros actuales
window.getCurrentFilters = function() {
    const filters = {};
    
    if (typeof $ !== 'undefined') {
        const category = $('#filter-estado').val();
        const status = $('#filter-status').val();
        
        if (category) filters.category = category;
        if (status !== '') filters.status = status;
    }
    
    return filters;
};

// Funci�n para preservar scroll position
window.preserveScrollPosition = function() {
    const mainContent = document.querySelector('.tab-content') || document.body;
    return {
        top: mainContent.scrollTop,
        left: mainContent.scrollLeft
    };
};

// Funci�n para restaurar scroll position
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

// Hacer currentPage accesible globalmente para preservaci�n de estado
window.currentPage = currentPage;

// ============ FUNCIONES DE DESTACADO Y ANIMACIONES ============

// Funci�n de destacado eliminada para evitar problemas visuales


// Sistema de loading overlay y actualizaci�n forzada eliminados

// ============ FUNCIONES DE ELIMINAR Y TOGGLE STATUS ============

// Funci�n para mostrar burbuja de confirmaci�n de eliminaci�n
function showDeleteConfirmationCategorias(categoriaId, categoriaName) {
    // Verificar si ya existe un modal
    const existingOverlay = document.querySelector('.delete-confirmation-overlay');
    if (existingOverlay) {
        existingOverlay.remove();
    }
    
    // Crear overlay con estilos profesionales
    const overlay = document.createElement('div');
    overlay.className = 'delete-confirmation-overlay';
    
    overlay.innerHTML = `
        <div class="delete-confirmation-modal">
            <div class="delete-modal-header">
                <h3><i class="fas fa-exclamation-triangle"></i> Confirmar Eliminaci�n</h3>
            </div>
            <div class="delete-modal-body">
                <div class="warning-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <p>Para eliminar la categoria <strong>"${categoriaName}"</strong>, escribe la palabra <strong>"eliminar"</strong> en el campo de abajo:</p>
                
                <input type="text" id="deleteConfirmInput" class="confirmation-input" placeholder="Escribe 'eliminar' para confirmar" autocomplete="off">
                <div id="deleteError" class="delete-error">
                    Por favor escribe exactamente "eliminar" para confirmar
                </div>
            </div>
            <div class="delete-modal-footer">
                <button type="button" class="btn-cancel-delete" onclick="closeDeleteConfirmationCategorias()">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn-confirm-delete" onclick="confirmDeleteCategorias(${categoriaId}, '${categoriaName.replace(/'/g, "\\'")}')">
                    <i class="fas fa-trash"></i> Eliminar categoria
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
    
    // Agregar al DOM
    document.body.appendChild(overlay);
    
    // Forzar reflow para que las animaciones funcionen
    overlay.offsetHeight;
    
    // Agregar clase 'show' para activar animaciones CSS
    requestAnimationFrame(() => {
        overlay.classList.add('show');
        
        // Tambi�n agregar .show al modal interno
        const deleteModal = overlay.querySelector('.delete-confirmation-modal');
        if (deleteModal) {
            deleteModal.classList.add('show');
        }
    });
    
    // Focus en el input despu�s de la animaci�n
    setTimeout(() => {
        const input = document.getElementById('deleteConfirmInput');
        if (input) {
            input.focus();
        }
    }, 350);
    
    // Permitir confirmar con Enter
    const input = document.getElementById('deleteConfirmInput');
    if (input) {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                confirmDeleteCategorias(categoriaId, categoriaName);
            }
        });
    }
    
    // Permitir cerrar con ESC
    const handleEsc = (e) => {
        if (e.key === 'Escape') {
            closeDeleteConfirmationCategorias();
            document.removeEventListener('keydown', handleEsc);
        }
    };
    document.addEventListener('keydown', handleEsc);
    
    // Cerrar al hacer click en el overlay (fondo oscuro)
    overlay.addEventListener('click', function(e) {
        // Solo cerrar si se hace click directamente en el overlay, no en el modal
        if (e.target === overlay) {
            closeDeleteConfirmationCategorias();
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

// Funci�n para cerrar la confirmaci�n con animaci�n
function closeDeleteConfirmationCategorias() {
    const overlay = document.querySelector('.delete-confirmation-overlay');
    if (overlay) {
        // Agregar clases de salida para animaci�n
        overlay.classList.remove('show');
        overlay.classList.add('hide');
        
        const deleteModal = overlay.querySelector('.delete-confirmation-modal');
        if (deleteModal) {
            deleteModal.classList.add('hide');
        }
        
        // Remover del DOM despu�s de que termine la animaci�n
        setTimeout(() => {
            overlay.remove();
        }, 250); // Duraci�n de la animaci�n fadeOut actualizada
    }
}

// Cerrar modal al hacer click en el backdrop
function setupDeleteModalBackdropCloseCategorias() {
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-confirmation-overlay')) {
            closeDeleteConfirmationCategorias();
        }
    });
}

// Cerrar modal al hacer click en el backdrop
function setupDeleteModalBackdropCloseCategorias() {
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-confirmation-overlay')) {
            closeDeleteConfirmationCategorias();
        }
    });
}

// Funci�n para confirmar eliminaci�n
function confirmDeleteCategorias(categoriaId, categoriaName) {
    const input = document.getElementById('deleteConfirmInput');
    const errorDiv = document.getElementById('deleteError');
    
    if (input.value.toLowerCase().trim() !== 'eliminar') {
        errorDiv.style.display = 'block';
        input.style.borderColor = '#dc2626';
        input.focus();
        return;
    }
    
    // Proceder con eliminaci�n
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id_categoria', categoriaId);
    
    fetch(window.categoria_CONFIG.apiUrl, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        closeDeleteConfirmationCategorias();
        
        if (data.success) {
            // Mostrar notificaci�n de �xito
            if (typeof showNotification === 'function') {
                // showNotification(`Estado "${categoriaName}" eliminado exitosamente`, 'success');
            }
            
            // Usar actualizaci�n suave si est� disponible
            if (window.categoriaSmoothTableUpdater) {
                window.categoriaSmoothTableUpdater.removecategoria(categoriaId);
            } else {
                // Actualizar lista inmediatamente sin reload
                loadcategorias(true);
            }
        } else {
            if (typeof showNotification === 'function') {
                // showNotification('Error al eliminar categoria: ' + (data.error || 'Error desconocido'), 'error');
            } else {
                // alert('Error al eliminar categoria: ' + (data.error || 'Error desconocido'));
            }
        }
    })
    .catch(error => {
        closeDeleteConfirmationCategorias();
        if (typeof showNotification === 'function') {
            // showNotification('Error de conexi�n al eliminar categoria', 'error');
        } else {
            // alert('Error de conexi�n al eliminar categoria');
        }
    });
}

// Funci�n para alternar estado del Estado (activo/inactivo)
function togglecategoriaStatus(categoriaId, currentStatus) {
    const newStatus = currentStatus ? 0 : 1;
    
    const formData = new FormData();
    formData.append('action', 'toggle_status');
    formData.append('id', categoriaId);
    formData.append('status', newStatus);
    
    fetch(window.categoria_CONFIG.apiUrl, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Usar actualizaci�n suave si est� disponible
            if (window.categoriaSmoothTableUpdater && data.categoria) {
                window.categoriaSmoothTableUpdater.updateSinglecategoria(data.categoria);
            } else {
                // Actualizar lista inmediatamente sin reload
                loadcategorias(true);
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
            // showNotification('Error de conexi�n al cambiar estado', 'error');
        } else {
            // alert('Error de conexi�n al cambiar estado');
        }
    });
}

// Funci�n wrapper para eliminar categoria
function deletecategoria(categoriaId, categoriaName) {
    // CERRAR MEN� FLOTANTE antes de mostrar confirmaci�n
    closeFloatingActionsAnimatedCategorias();
    
    showDeleteConfirmationCategorias(categoriaId, categoriaName || 'categoria');
}

// ============ FUNCI�N PARA MOSTRAR IMAGEN EN TAMA�O REAL ============

function showImageFullSize(imageUrl, categoriaName) {
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
    img.alt = categoriaName || 'Estado';
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
        img.src = AppConfig ? AppConfig.getImageUrl('default-categoria.jpg') : '/fashion-master/public/assets/img/default-categoria.jpg';
    };
}

// Hacer la funci�n global
window.showImageFullSize = showImageFullSize;

// ============ FIN FUNCIONES DE ELIMINAR Y TOGGLE STATUS ============

// Sistema de limpieza autom�tica para evitar men�s hu�rfanos
setInterval(() => {
    const orphanedContainers = document.querySelectorAll('.animated-floating-container');
    if (orphanedContainers.length > 1) {
        // Si hay m�s de un contenedor, algo est� mal, limpiar todos
        orphanedContainers.forEach(container => {
            try {
                container.remove();
            } catch (e) {
                console.warn('Error limpiando contenedor hu�rfano:', e);
            }
        });
        // Resetear variables globales
        activeFloatingContainer = null;
        centerButton = null;
        floatingButtons = [];
        activecategoriaId = null;
        isAnimating = false;
    }
}, 2000); // Verificar cada 2 segundos

// Limpiar al cambiar de p�gina o recargar
window.addEventListener('beforeunload', () => {
    forceCloseFloatingActionsCategorias();
});

// ===== FUNCIONALIDAD DE SCROLL MEJORADO PARA LA TABLA =====
function initializeTableScroll() {
    const scrollableTable = document.querySelector('.scrollable-table');
    if (!scrollableTable) return;
    
    let scrollTimeout;
    
    // Detectar cuando se est� haciendo scroll
    scrollableTable.addEventListener('scroll', function() {
        // Agregar clase durante el scroll
        this.classList.add('scrolling');
        
        // Remover clase despu�s de que termine el scroll
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
// En su lugar, initializecategoriasModule() ya llama a esto directamente

// ✅ WRAPPER REMOVIDO - initializeTableScroll se llama directamente en displaycategoriasCategorias()
// Esto evita el bucle infinito que causaba el wrapper anterior

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
        // No aplicar drag si se est� clickeando en un bot�n, input o link
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
        
        // Prevenir selecci�n de texto completamente
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
        
        // Restaurar selecci�n de texto
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
    
    // Prevenir click accidental despu�s de drag
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
// En su lugar, initializecategoriasModule() llama a initializeDragScroll() directamente

// ===== FUNCIÓN DE DESTRUCCIÓN DEL MÓDULO DE CATEGORÍAS =====
window.destroyCategoriasModule = function() {
    console.log('🗑️ Destruyendo módulo de categorías...');
    
    try {
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
        
        // 4. Limpiar timeouts pendientes de categorías
        if (typeof categoriasTimeouts !== 'undefined' && Array.isArray(categoriasTimeouts)) {
            console.log(`🧹 Cancelando ${categoriasTimeouts.length} timeouts pendientes de categorias`);
            categoriasTimeouts.forEach(timeoutId => clearTimeout(timeoutId));
            categoriasTimeouts = [];
        }
        
        // 5. Limpiar event listeners clonando elementos
        const searchInput = document.getElementById('search-categorias');
        if (searchInput && searchInput.parentNode) {
            const newSearch = searchInput.cloneNode(true);
            searchInput.parentNode.replaceChild(newSearch, searchInput);
        }
        
        const filterStatus = document.getElementById('filter-status-categoria');
        if (filterStatus && filterStatus.parentNode) {
            const newFilterStatus = filterStatus.cloneNode(true);
            filterStatus.parentNode.replaceChild(newFilterStatus, filterStatus);
        }
        
        // 6. Limpiar modales de categorías
        const categoriaModals = document.querySelectorAll('.categoria-modal, [id*="categoria-modal"]');
        categoriaModals.forEach(modal => {
            modal.remove();
        });
        
        // 7. Limpiar confirmaciones de eliminación
        const deleteConfirmations = document.querySelectorAll('.delete-confirmation-overlay');
        deleteConfirmations.forEach(confirmation => {
            confirmation.remove();
        });
        
        // 7.5. Limpiar botones flotantes y burbujas (NUEVO)
        const floatingContainers = document.querySelectorAll('.animated-floating-container');
        floatingContainers.forEach(container => container.remove());
        
        const bubbles = document.querySelectorAll('.cantidad_categorias-update-bubble');
        bubbles.forEach(bubble => bubble.remove());
        
        // 8. Limpiar el tbody de la tabla
        const tbody = document.getElementById('categorias-table-body');
        if (tbody) {
            tbody.innerHTML = '';
        }
        
        // 9. RESETEAR VISTA A TABLA (estado inicial)
        console.log('🔄 [CATEGORIAS] Reseteando vista a tabla (estado inicial)...');
        
        // Remover vista grid si existe
        const gridContainer = document.querySelector('.categorias-grid');
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
            localStorage.removeItem('categorias_current_view'); // ← Nombre correcto
            localStorage.removeItem('categorias_view_mode'); // Limpiar ambos por compatibilidad
        } catch (e) {}
        
        console.log('✅ [CATEGORIAS] Vista reseteada a tabla');
        
        // 10. RESETEAR BANDERA DE INICIALIZACIÓN (CRÍTICO)
        window.categoriasModuleInitialized = false;
        console.log('🔄 [CATEGORIAS] Bandera de inicialización reseteada');
        
        // 11. Remover clases de body que puedan interferir
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
        
        console.log('✅ Módulo de categorías destruido correctamente');
        
    } catch (error) {
        console.error('❌ Error al destruir módulo de categorías:', error);
    }
};

</script>

<style>
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
    user-select: none; /* Evitar selecci�n de texto */
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

/* Animaci�n suave del scroll */
.scrollable-table {
    scroll-behavior: smooth;
}

/* ===== FORZAR PRIMER PLANO PARA ELEMENTOS FLOTANTES ===== */
.animated-floating-container,
.animated-floating-button,
.cantidad_categorias-update-bubble {
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

/* Asegurar que los tooltips tambi�n est�n en primer plano */
.floating-tooltip {
    z-index: 1000000 !important;
}

/* Forzar primer plano en elementos espec�ficos que pueden interferir */
.modal-content,
.modal-overlay,
#categoria-modal-overlay {
    z-index: 99999 !important;
}

/* Asegurar que las burbujas est�n por encima de modales */
.animated-floating-container,
.cantidad_categorias-update-bubble {
    z-index: 1000001 !important;
}
</style>




