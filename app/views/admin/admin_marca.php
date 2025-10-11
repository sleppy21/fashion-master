


<div class="admin-module admin-Marcas-module">
    <!-- Header del módulo -->
    <div class="module-header">
        <div class="module-title">
            <div class="module-icon">
                <i class="fas fa-copyright"></i>
            </div>
            <div class="module-info">
                <h2 class="module-name">Gestión de Marcas</h2>
                <p class="module-description">Administra las marcas de productos de la tienda</p>
            </div>
        </div>
        <div class="module-actions">
            <button class="btn-modern btn-primary" onclick="window.showCreateMarcaModal();">
                <i class="fas fa-plus"></i>
                <span>Nueva Marca</span>
            </button>
            <button class="btn-modern btn-secondary" onclick="exportMarcas()">
                <i class="fas fa-download"></i>
                <span>Exportar Excel</span>
            </button>
            <button class="btn-modern btn-info" onclick="showMarcasReport()">
                <i class="fas fa-chart-bar"></i>
                <span>Reporte</span>
            </button>
        </div>
    </div>

    <!-- Filtros y búsqueda -->
    <div class="module-filters">
        <div class="search-container">
            <div class="search-input-group">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="search-marcas" class="search-input" 
                       placeholder="Buscar marcas por nombre..." oninput="handlemarcaSearchInputMarcas()">
                <button class="search-clear" onclick="clearMarcaSearch()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        
        <div class="filters-grid">
            <div class="filter-group">
                <label class="filter-label">Estado</label>
                <select id="filter-status" class="filter-select" onchange="filtermarcasMarcas()">
                    <option value="">Todos los estados</option>
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>
            <div class="filter-group">
                <button class="btn-modern btn-outline" onclick="clearAllMarcaFilters()">
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
                        Mostrando  <span id="showing-end-marcas">0</span> 
                        de <span id="total-marcas">0</span> marcas
                    </span>
                </div>
                <div class="table-actions">
                    <div class="view-options">
                        <button class="view-btn active" data-view="table" onclick="toggleViewmarcasMarcas('table')">
                            <i class="fas fa-table"></i>
                        </button>
                        <button class="view-btn" data-view="grid" onclick="toggleViewmarcasMarcas('grid')">
                            <i class="fas fa-th"></i>
                        </button>
                    </div>
                    <div class="bulk-actions" style="display: none;">
                        <span class="selected-count">0</span> seleccionados
                        <select class="bulk-select" onchange="handleBulkMarcaAction(this.value); this.value='';">
                            <option value="">Acciones en lote</option>
                            <option value="activar">Activar seleccionados</option>
                            <option value="desactivar">Desactivar seleccionados</option>
                            <option value="eliminar">Eliminar seleccionados</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="data-table-wrapper scrollable-table">
                <table class="data-table marcas-table">
                    <thead class="table-header">
                        <tr>
                            <th class="sortable" data-sort="id">
                                <span>ID</span>
                            </th>
                            <th class="no-sort">
                                <span>Imagen</span>
                            </th>
                            <th class="sortable" data-sort="nombre">
                                <span>Nombre Marca</span>
                            </th>
                            <th class="sortable" data-sort="codigo_marca">
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

            <!-- Paginación -->
            <div class="pagination-container">
                <div class="pagination-info">
                    <span class="pagination-text">
                        Página <span id="current-page-Marcas">1</span> de <span id="total-pages-Marcas">1</span>
                    </span>
                </div>
                <div class="pagination-controls">
                    <button class="pagination-btn" id="first-page-Marcas" onclick="goToFirstPageMarcas()">
                        <i class="fas fa-angle-double-left"></i>
                    </button>
                    <button class="pagination-btn" id="prev-page-Marcas" onclick="previousPageMarcas()">
                        <i class="fas fa-angle-left"></i>
                    </button>
                    <div class="pagination-numbers" id="pagination-numbers-Marcas">
                        <!-- Números de página dinámicos -->
                    </div>
                    <button class="pagination-btn" id="next-page-Marcas" onclick="nextPageMarcas()">
                        <i class="fas fa-angle-right"></i>
                    </button>
                    <button class="pagination-btn" id="last-page-Marcas" onclick="goToLastPageMarcas()">
                        <i class="fas fa-angle-double-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// ============ CONFIGURACIÓN ============

// ✅ CORRECCIÓN CRÍTICA: Usar namespace específico para evitar conflictos con otros módulos
// window.CONFIG es compartido entre todos los módulos, causando conflictos
function initializemarcaConfigMarcas() {
    if (typeof AppConfig !== 'undefined') {
        window.MARCA_CONFIG = {
            apiUrl: AppConfig.getApiUrl('MarcaController.php')
        };
    } else {
        // Fallback si config.js no está cargado
        window.MARCA_CONFIG = {
            apiUrl: '/fashion-master/app/controllers/MarcaController.php'
        };
    }
    console.log('⚙️ MARCA_CONFIG inicializado:', window.MARCA_CONFIG.apiUrl);
}

// SIEMPRE inicializar inmediatamente
initializemarcaConfigMarcas();

// Alias para compatibilidad (SOLO dentro del contexto de marcas)
const CONFIG = window.MARCA_CONFIG;

// Variables globales
let isLoading = false;
let marcas = [];
window.marcas = marcas; // ✅ Hacer accesible globalmente
let loadingTimeout = null; // Para prevenir bloqueos permanentes

// Variables de paginación
let currentPage = 1;
let totalPages = 1;

// Función para obtener la URL correcta de la imagen de la Marca
function getmarcaImageUrlMarcas(marca, forceCacheBust = false) {
    // Priorizar url_imagen_marca, luego imagen_marca
    let imageUrl = '';
    
    if (marca.url_imagen_marca) {
        // Verificar que no sea una URL de placeholder
        if (marca.url_imagen_marca.includes('placeholder') || 
            marca.url_imagen_marca.includes('via.placeholder')) {
            imageUrl = (typeof AppConfig !== 'undefined') ? AppConfig.getImageUrl('default-product.png') : '/fashion-master/public/assets/img/default-product.png';
        } else {
            imageUrl = marca.url_imagen_marca;
        }
    } else if (marca.imagen_marca) {
        // Si es un nombre de archivo local, construir la ruta completa
        if (!marca.imagen_marca.startsWith('http')) {
            imageUrl = (typeof AppConfig !== 'undefined') ? AppConfig.getImageUrl('products/' + marca.imagen_marca) : '/fashion-master/public/assets/img/products/' + marca.imagen_marca;
        } else {
            imageUrl = marca.imagen_marca;
        }
    } else {
        imageUrl = (typeof AppConfig !== 'undefined') ? AppConfig.getImageUrl('default-product.png') : '/fashion-master/public/assets/img/default-product.png';
    }
    
    // Agregar cache-busting solo si se solicita explícitamente
    if (forceCacheBust) {
        const cacheBuster = '?v=' + Date.now();
        return imageUrl + cacheBuster;
    }
    
    return imageUrl;
}

// Función auxiliar para mostrar loading en búsqueda
function showSearchLoadingMarcas() {
    const tbody = document.getElementById('marcas-table-body');
    if (tbody) {
        tbody.innerHTML = `
            <tr class="loading-row">
                <td colspan="8" class="loading-cell">
                    <div class="loading-content">
                        <div class="spinner"></div>
                        <span>Buscando marcas...</span>
                    </div>
                </td>
            </tr>
        `;
    }
}

// ====================================================================
// TABLA SMOOTH UPDATER - Actualizador de tabla con animaciones suaves
// ====================================================================
window.marcaSmoothTableUpdater = {
    /**
     * Elimina una fila de marca con animación suave
     * @param {number} marcaId - ID de la marca a eliminar
     */
    removeMarca: function(marcaId) {
        // Buscar la fila por data-id
        const row = document.querySelector(`tr[data-id="${marcaId}"]`);
        
        if (!row) {
            // Fallback: recargar toda la tabla
            if (typeof loadMarcas === 'function') {
                loadMarcas(true);
            }
            return;
        }
        
        // Animación de salida suave
        row.style.transition = 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
        row.style.opacity = '0';
        row.style.transform = 'translateX(-20px) scale(0.95)';
        row.style.backgroundColor = 'rgba(239, 68, 68, 0.1)';
        
        // Después de la animación, eliminar del DOM
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
            
            // Eliminar del DOM después de la animación
            setTimeout(() => {
                row.remove();
                
                // Verificar si la tabla quedó vacía
                const tbody = document.getElementById('marcas-table-body');
                if (tbody && tbody.querySelectorAll('tr').length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 3rem; color: #64748b;">
                                <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;">
                                    <i class="fas fa-inbox"></i>
                                </div>
                                <div style="font-size: 1.1rem; font-weight: 600;">No hay marcas disponibles</div>
                                <div style="font-size: 0.9rem; margin-top: 0.5rem;">Crea una nueva marca para comenzar</div>
                            </td>
                        </tr>
                    `;
                }
            }, 400); // Esperar a que termine la animación de colapso
        }, 400); // Esperar a que termine la animación de fade out
    },

    /**
     * Actualiza una fila de marca específica con animación suave
     * @param {number} marcaId - ID de la marca a actualizar
     * @param {object} marcaData - Datos actualizados de la marca
     */
    updateSingleMarca: async function(marcaId, marcaData = null) {
        try {
            // Si no tenemos datos, obtenerlos del servidor
            if (!marcaData) {
                const response = await fetch(`${window.MARCA_CONFIG.apiUrl}?action=get&id=${marcaId}`);
                const result = await response.json();
                
                if (!result.success || !result.data) {
                    throw new Error('No se pudieron obtener los datos de la marca');
                }
                
                marcaData = result.data;
            }

            // ✅ DETECTAR VISTA ACTUAL
            const currentView = getCurrentViewMarcas();
            console.log('👁️ Vista actual para actualización:', currentView);
            
            if (currentView === 'grid') {
                // ✅ ACTUALIZAR VISTA GRID
                console.log('🔄 Actualizando tarjeta en vista grid...');
                const card = document.querySelector(`.marca-card[data-marca-id="${marcaId}"]`);
                
                if (card) {
                    // Actualizar imagen
                    const img = card.querySelector('img');
                    if (img) {
                        const imageUrl = getmarcaImageUrlMarcas(marcaData, true);
                        img.src = imageUrl;
                    }
                    
                    // Actualizar nombre
                    const title = card.querySelector('.marca-card-title');
                    if (title) {
                        title.textContent = marcaData.nombre_marca || 'Sin nombre';
                    }
                    
                    // Actualizar estado
                    const status = card.querySelector('.marca-card-status');
                    if (status) {
                        status.className = `marca-card-status ${marcaData.estado_marca === 'activo' ? 'active' : 'inactive'}`;
                        status.textContent = marcaData.estado_marca === 'activo' ? 'Activo' : 'Inactivo';
                    }
                    
                    // Actualizar código
                    const sku = card.querySelector('.marca-card-sku');
                    if (sku && marcaData.codigo_marca) {
                        sku.textContent = `Código: ${marcaData.codigo_marca}`;
                    }
                    
                    // Actualizar descripción
                    const description = card.querySelector('.marca-card-description');
                    if (description && marcaData.descripcion_marca) {
                        const desc = marcaData.descripcion_marca;
                        description.innerHTML = `<i class="fas fa-align-left"></i> ${desc.length > 80 ? desc.substring(0, 80) + '...' : desc}`;
                    }
                    
                    console.log('✅ Tarjeta actualizada en vista grid');
                    return;
                } else {
                    console.warn('⚠️ No se encontró tarjeta para actualizar, recargando datos...');
                    if (typeof loadMarcasData === 'function') {
                        loadMarcasData();
                    }
                    return;
                }
            }
            
            // ✅ ACTUALIZAR VISTA TABLA (código original)
            console.log('🔄 Actualizando fila en vista tabla...');
            const row = document.querySelector(`tr[data-id="${marcaId}"]`);
            
            if (!row) {
                if (typeof loadMarcasData === 'function') {
                    loadMarcasData();
                }
                return;
            }

            // Animación de actualización: pulso suave
            row.style.transition = 'all 0.3s ease';
            row.style.backgroundColor = 'rgba(34, 197, 94, 0.1)';
            
            // Actualizar el contenido de cada celda
            const cells = row.querySelectorAll('td');
            
            // Celda 0: ID
            if (cells[0]) {
                cells[0].textContent = `#${String(marcaData.id_marca).padStart(4, '0')}`;
            }

            // Celda 1: Imagen
            if (cells[1]) {
                const imgContainer = cells[1].querySelector('.marca-img-container');
                if (imgContainer) {
                    const img = imgContainer.querySelector('img');
                    const imageSrc = marcaData.url_imagen_marca || 
                                   (marcaData.imagen_marca ? `/fashion-master/public/assets/img/brands/${marcaData.imagen_marca}` : 
                                   '/fashion-master/public/assets/img/default-product.jpg');
                    if (img) {
                        img.src = imageSrc;
                        img.alt = marcaData.nombre_marca || 'Marca';
                    }
                }
            }

            // Celda 2: Nombre
            if (cells[2]) {
                cells[2].innerHTML = `<strong>${marcaData.nombre_marca || ''}</strong>`;
            }

            // Celda 3: Código
            if (cells[3]) {
                cells[3].innerHTML = `<span class="marca-code">${marcaData.codigo_marca || 'N/A'}</span>`;
            }

            // Celda 4: Descripción
            if (cells[4]) {
                const descripcion = marcaData.descripcion_marca || 'Sin descripción';
                cells[4].innerHTML = `<span class="marca-description">${descripcion.substring(0, 50)}${descripcion.length > 50 ? '...' : ''}</span>`;
            }

            // Celda 5: Estado
            if (cells[5]) {
                const isActive = marcaData.estado_marca === 'activo';
                cells[5].innerHTML = `
                    <span class="marca-status marca-status--${isActive ? 'active' : 'inactive'}">
                        <i class="fas fa-${isActive ? 'check-circle' : 'times-circle'}"></i>
                        ${isActive ? 'Activo' : 'Inactivo'}
                    </span>
                `;
            }

            // Celda 6: Fecha de creación
            if (cells[6] && marcaData.fecha_creacion_marca) {
                const fecha = new Date(marcaData.fecha_creacion_marca);
                cells[6].textContent = fecha.toLocaleDateString('es-ES');
            }

            // Volver al color normal después de 500ms
            setTimeout(() => {
                row.style.backgroundColor = '';
            }, 500);

        } catch (error) {
            console.error('❌ Error al actualizar fila:', error);
            // Fallback: recargar toda la tabla
            if (typeof loadMarcasData === 'function') {
                loadMarcasData();
            }
        }
    }
};

// ====================================================================
// CARGAR MARCAS - Función principal
// ====================================================================

// Función principal para cargar DATOS de Marcas con efectos visuales (DEFINICIÓN TEMPRANA)
// IMPORTANTE: Esta función carga los DATOS, no la vista completa
// Para cargar la vista completa, usar la función loadMarcas() de admin.php
async function loadMarcasData(forceCacheBust = false, preserveState = null) {
    console.log('📂 loadMarcasData() llamado - isLoading actual:', isLoading);
    console.trace('📂 STACK TRACE - Quién llamó a loadMarcasData:');

    // Prevenir llamadas simultáneas
    if (isLoading) {
        console.warn('⚠️ loadMarcasData() bloqueado - ya hay una carga en progreso');
        return;
    }
    
    // Asegurar que esté disponible globalmente
    window.loadMarcasData = loadMarcasData;
    
    isLoading = true;
    console.log('🔒 isLoading activado');
    
    // Timeout de seguridad: si después de 10 segundos sigue bloqueado, desbloquear
    if (loadingTimeout) {
        clearTimeout(loadingTimeout);
    }
    loadingTimeout = setTimeout(() => {
        if (isLoading) {
            console.warn('⏰ Timeout de carga alcanzado - desbloqueando isLoading forzadamente');
            isLoading = false;
        }
    }, 10000); // 10 segundos
    
    try {
        // Mostrar loading mejorado
        showSearchLoadingMarcas();
        
        // Usar estado preservado si está disponible
        if (preserveState) {
            currentPage = preserveState.page || currentPage;
            
            // Restaurar filtros si están disponibles
            if (preserveState.searchTerm && typeof $ !== 'undefined') {
                $('#search-marcas').val(preserveState.searchTerm);
            }
            
        }
        
        // Construir URL con parámetros
        const params = new URLSearchParams({
            action: 'list',
            page: currentPage,
            limit: 10
        });
        
        // Agregar filtros si existen
        if (typeof $ !== 'undefined') {
            const searchInput = $('#search-marcas');
            if (searchInput.length && searchInput.val()) {
                params.append('search', searchInput.val());
            }
            
            const statusSelect = $('#filter-status');
            if (statusSelect.length && statusSelect.val() !== '') {
                params.append('status', statusSelect.val());
            }
        } else {
            // Fallback vanilla JS
            const searchInput = document.getElementById('search-marcas');
            if (searchInput && searchInput.value) {
                params.append('search', searchInput.value);
            }
            
            const statusSelect = document.getElementById('filter-status');
            if (statusSelect && statusSelect.value !== '') {
                params.append('status', statusSelect.value);
            }
        }
        
        const finalUrl = `${CONFIG.apiUrl}?${params}`;
        console.log('🌐 URL final del fetch:', finalUrl);
        console.log('📋 Parámetros:', Object.fromEntries(params));
        
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
            
            throw new Error('Respuesta del servidor no es JSON válido');
        }
        
        if (!data.success) {
            throw new Error(data.error || 'Error desconocido del servidor');
        }
        
        marcas = data.data || [];
        window.marcas = marcas; // ✅ Actualizar referencia global
        console.log('✅ Datos recibidos del servidor:', {
            success: data.success,
            marcasCount: marcas.length,
            pagination: data.pagination
        });
        
        displaymarcasMarcas(marcas, forceCacheBust, preserveState);
        updateStatsMarcas(data.pagination);
        updatePaginationInfoMarcas(data.pagination);
        
        // Actualizar contador de resultados
        if (data.pagination) {
            updateResultsCounter(marcas.length, data.pagination.total_items);
        }
        
        // Destacar marca recién actualizado/creado si está especificado
        // PRESERVAR ESTADO - sin destacado visual para evitar bugs
        if (preserveState) {
            // Restaurar posición de scroll sin animaciones que causen problemas
            if (preserveState.scrollPosition && typeof restoreScrollPosition === 'function') {
                restoreScrollPosition(preserveState.scrollPosition);
            }
        }
        
    } catch (error) {
        console.error('❌ Error en loadMarcas:', error);
        const tbody = document.getElementById('marcas-table-body');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="loading-cell">
                        <div class="loading-content error">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Error: ${error.message}</span>
                            <button onclick="loadMarcasData()" class="btn-modern btn-primary">
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
        console.log('🔓 isLoading desactivado en finally');
        
        // Loading overlay eliminado
    }
}

// Asegurar que la función esté disponible globalmente
window.loadMarcasData = loadMarcasData;
// NO sobrescribir window.loadMarcas - esa función debe seguir siendo la de admin.php
// que carga la vista completa. Solo usar loadMarcasData internamente.

// Función para cargar categorías en el filtro
// Función para obtener la vista actual
function getCurrentViewMarcas() {
    // PRIMERO: Intentar leer de localStorage
    try {
        const savedView = localStorage.getItem('marcas_current_view');
        if (savedView) {
            console.log('📖 Vista leída de localStorage:', savedView);
            return savedView;
        }
    } catch (e) {
        console.warn('⚠️ No se pudo leer vista de localStorage:', e);
    }
    
    // SEGUNDO: Verificar botones activos en el DOM
    const gridViewBtn = document.querySelector('[data-view="grid"]');
    const tableViewBtn = document.querySelector('[data-view="table"]');
    
    if (gridViewBtn && gridViewBtn.classList.contains('active')) {
        console.log('📖 Vista detectada por botón activo: grid');
        return 'grid';
    }
    
    // Por defecto, vista tabla
    console.log('📖 Vista por defecto: table');
    return 'table';
}

// Helper para obtener URL de imagen de marca
function getmarcaImageUrlMarcas(marca, forceCacheBust = false) {
    const timestamp = forceCacheBust ? '?t=' + new Date().getTime() : '';
    
    // Prioridad 1: url_imagen_marca (ruta completa)
    if (marca.url_imagen_marca && marca.url_imagen_marca !== 'NULL' && marca.url_imagen_marca !== null) {
        return marca.url_imagen_marca + timestamp;
    }
    
    // Prioridad 2: imagen_marca (nombre de archivo)
    if (marca.imagen_marca && marca.imagen_marca !== 'NULL' && marca.imagen_marca !== null) {
        // Si es default-product.jpg o default-product.png, devolver la ruta completa
        if (marca.imagen_marca === 'default-product.jpg' || marca.imagen_marca === 'default-product.png') {
            // Usar .jpg que es el que realmente existe
            return '/fashion-master/public/assets/img/default-product.jpg' + timestamp;
        }
        // Si es otro archivo, construir ruta
        const imgPath = '/fashion-master/public/assets/img/products/' + marca.imagen_marca + timestamp;
        return imgPath;
    }
    
    // Fallback: imagen por defecto (.jpg es el correcto)
    return '/fashion-master/public/assets/img/default-product.jpg' + timestamp;
}

// Función para mostrar Marcas en tabla o grid
function displaymarcasMarcas(Marcas, forceCacheBust = false, preserveState = null) {
    console.log('📊 displaymarcasMarcas() llamado con', Marcas?.length || 0, 'marcas');
    
    // Detectar vista actual
    const currentView = getCurrentViewMarcas();
    console.log('👁️ Vista actual:', currentView);
    
    if (currentView === 'grid') {
        // Si está en vista grid, actualizar grid
        console.log('🔄 Redirigiendo a displaymarcasGridMarcas()');
        displaymarcasGridMarcas(Marcas);
        return;
    }
    
    // Si está en vista tabla, actualizar tabla
    console.log('🔍 Buscando tbody con ID: marcas-table-body');
    const tbody = document.getElementById('marcas-table-body');
    
    if (!tbody) {
        console.error('❌ No se encontró el tbody de marcas');
        console.log('📋 Elementos disponibles en el DOM:');
        console.log('  - Todos los tbody:', document.querySelectorAll('tbody'));
        console.log('  - Elementos con "marca" en ID:', document.querySelectorAll('[id*="marca"]'));
        return;
    }
    
    console.log('✅ tbody encontrado:', tbody);
    
    // LIMPIEZA FORZADA COMPLETA - remover TODOS los elementos hijos
    while (tbody.firstChild) {
        tbody.removeChild(tbody.firstChild);
    }
    tbody.innerHTML = '';
    
    if (!Marcas || Marcas.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="loading-cell">
                    <div class="loading-content no-data">
                        <i class="fas fa-box-open"></i>
                        <span>No se encontraron marcas</span>
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    // Construir HTML completo y asignarlo UNA SOLA VEZ
    const htmlContent = Marcas.map((marca, index) => {
        // Imagen
        const imageUrl = getmarcaImageUrlMarcas(marca, true);
        const fallbackImage = '/fashion-master/public/assets/img/default-product.jpg';
        
        // Truncar descripción a 100 caracteres
        const descripcion = marca.descripcion_marca 
            ? (marca.descripcion_marca.length > 100 
                ? marca.descripcion_marca.substring(0, 100) + '...' 
                : marca.descripcion_marca)
            : 'Sin descripción';
            
        return `
        <tr oncontextmenu="return false;" ondblclick="editMarca(${marca.id_marca})" style="cursor: pointer;" data-marca-id="${marca.id_marca}">
            <td><strong>${marca.id_marca}</strong></td>
            <td>
                <div class="product-image-cell" ondblclick="event.stopPropagation(); showImageFullSize('${imageUrl}', '${(marca.nombre_marca || '').replace(/'/g, "\\'")}')"; style="cursor: zoom-in; width: 50px; height: 50px; border-radius: 8px; overflow: hidden;">
                    <img src="${imageUrl}" 
                         alt="${marca.nombre_marca || 'Marca'}" 
                         class="product-thumbnail"
                         style="width: 100%; height: 100%; object-fit: contain; border-radius: 8px;"
                         onerror="this.src='${fallbackImage}'; this.onerror=null;">
                </div>
            </td>
            <td>
                <div class="marca-info">
                    <strong>${marca.nombre_marca || 'Sin nombre'}</strong>
                </div>
            </td>
            <td>
                <code>${marca.codigo_marca || 'N/A'}</code>
            </td>
            <td>
                <div class="descripcion-truncate" title="${(marca.descripcion_marca || 'Sin descripción').replace(/"/g, '&quot;')}">
                    ${descripcion}
                </div>
            </td>
            <td>
                <span class="status-badge ${marca.estado_marca === 'activo' ? 'status-active' : 'status-inactive'}">
                    ${marca.estado_marca === 'activo' ? 'Activo' : 'Inactivo'}
                </span>
            </td>
            <td>${marca.fecha_creacion_formato || marca.fecha_creacion_marca || 'N/A'}</td>
            <td>
                <div class="action-buttons">
                    <button class="btn-action btn-menu" onclick="event.stopPropagation(); showActionMenuMarcas(${marca.id_marca}, '${(marca.nombre_marca || '').replace(/'/g, "\\'")}', 0, '${marca.estado_marca}', event)" title="Acciones">
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

// Función para actualizar estadísticas
function updateStatsMarcas(pagination) {
    if (pagination) {
        const { current_page, total_pages, total_items, items_per_page } = pagination;
        const start = ((current_page - 1) * items_per_page) + 1;
        const end = Math.min(current_page * items_per_page, total_items);
        
        const showingStartEl = document.getElementById('showing-start-marcas');
        const showingEndEl = document.getElementById('showing-end-marcas');
        const totalMarcasEl = document.getElementById('total-marcas');
        
        if (showingStartEl) showingStartEl.textContent = total_items > 0 ? start : 0;
        if (showingEndEl) showingEndEl.textContent = total_items > 0 ? end : 0;
        if (totalMarcasEl) totalMarcasEl.textContent = total_items;
    }
}

// Función para actualizar información de paginación
function updatePaginationInfoMarcas(pagination) {
    if (pagination) {
        currentPage = pagination.current_page || 1;
        totalPages = pagination.total_pages || 1;
        
        // Actualizar elementos de paginación si existen
        const currentPageEl = document.getElementById('current-page-Marcas');
        const totalPagesEl = document.getElementById('total-pages-Marcas');
        
        if (currentPageEl) currentPageEl.textContent = currentPage;
        if (totalPagesEl) totalPagesEl.textContent = totalPages;
        
        // Actualizar botones de paginación si existen
        const firstBtn = document.querySelector('[onclick="goToFirstPageMarcas()"]');
        const prevBtn = document.querySelector('[onclick="previousPageMarcas()"]');
        const nextBtn = document.querySelector('[onclick="nextPageMarcas()"]');
        const lastBtn = document.querySelector('[onclick="goToLastPageMarcas()"]');
        
        if (firstBtn) firstBtn.disabled = currentPage <= 1;
        if (prevBtn) prevBtn.disabled = currentPage <= 1;
        if (nextBtn) nextBtn.disabled = currentPage >= totalPages;
        if (lastBtn) lastBtn.disabled = currentPage >= totalPages;
    }
}

// Función de filtrado mejorada con jQuery
function filtermarcasMarcas() {
    if (typeof $ === 'undefined') {
        return filterMarcasVanilla();
    }
    
    const search = $('#search-marcas').val() || '';
    const category = $('#filter-estado').val() || '';
    const status = $('#filter-status').val() || '';
    const cantidad_marcas = $('#filter-cantidad_marcas').val() || '';
    
    // Mostrar indicador de carga
    showSearchLoadingMarcas();
    
    // Reset página actual
    currentPage = 1;
    
    // Recargar marcas con filtros
    loadMarcasData();
}

// Función de filtrado con vanilla JS como fallback
function filterMarcasVanilla() {
    const searchInput = document.getElementById('search-marcas');
    const categorySelect = document.getElementById('filter-estado');
    const statusSelect = document.getElementById('filter-status');
    const cantidad_marcasSelect = document.getElementById('filter-cantidad_marcas');
    
    const search = searchInput ? searchInput.value || '' : '';
    const category = categorySelect ? categorySelect.value || '' : '';
    const status = statusSelect ? statusSelect.value || '' : '';
    const cantidad_marcas = cantidad_marcasSelect ? cantidad_marcasSelect.value || '' : '';
    
    // Mostrar indicador de carga
    showSearchLoadingMarcas();
    
    // Reset página actual
    currentPage = 1;
    
    // Recargar marcas con filtros
    loadMarcasData();
}

// Función para manejar búsqueda en tiempo real con jQuery
let searchTimeout;
function handlemarcaSearchInputMarcas() {
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
            filtermarcasMarcas();
        }, 300); // Reducido para mejor responsividad
    } else {
        // Fallback vanilla JS
        const searchInput = document.getElementById('search-marcas');
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
            filtermarcasMarcas();
        }, 300);
    }
}

// Función para cambiar vista (tabla/grid)
function toggleViewmarcasMarcas(viewType) {
    // CERRAR MENÚS FLOTANTES si están abiertos (evita errores de posición)
    if (activeFloatingContainer) {
        closeFloatingActionsAnimatedMarcas();
    }
    
    const tableContainer = document.querySelector('.data-table-wrapper');
    const gridContainer = document.querySelector('.marcas-grid');
    const viewButtons = document.querySelectorAll('.view-btn');
    
    // Si no existe el grid, crearlo
    if (!gridContainer) {
        createGridViewMarcas();
    }
    
    viewButtons.forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.view === viewType) {
            btn.classList.add('active');
        }
    });
    
    // GUARDAR la vista actual en localStorage
    try {
        localStorage.setItem('marcas_current_view', viewType);
    } catch (e) {
        console.warn('⚠️ No se pudo guardar vista en localStorage:', e);
    }
    
    if (viewType === 'grid') {
        tableContainer.style.display = 'none';
        document.querySelector('.marcas-grid').style.display = 'grid';
        // ✅ NO recargar - solo actualizar displaymarcasMarcas con datos existentes
        console.log('🔄 Cambiando a vista GRID - actualizando displaymarcasMarcas()');
        if (window.marcas && window.marcas.length > 0) {
            displaymarcasMarcas(window.marcas);
        }
    } else {
        tableContainer.style.display = 'block';
        document.querySelector('.marcas-grid').style.display = 'none';
        // ✅ NO recargar - solo actualizar displaymarcasMarcas con datos existentes
        console.log('🔄 Cambiando a vista TABLA - actualizando displaymarcasMarcas()');
        if (window.marcas && window.marcas.length > 0) {
            displaymarcasMarcas(window.marcas);
        }
    }
}

// Función para formatear fecha
function formatearFechaMarcas(fecha) {
    if (!fecha) return 'N/A';
    const date = new Date(fecha);
    const opciones = { year: 'numeric', month: 'short', day: 'numeric' };
    return date.toLocaleDateString('es-ES', opciones);
}

// Función para crear vista grid
function createGridViewMarcas() {
    // Verificar si ya existe el grid
    let gridContainer = document.querySelector('.marcas-grid');
    if (gridContainer) {
        console.log('✅ Grid ya existe, no se creará de nuevo');
        return gridContainer;
    }
    
    // Crear nuevo contenedor grid
    gridContainer = document.createElement('div');
    gridContainer.className = 'marcas-grid';
    gridContainer.style.display = 'none';
    
    // Insertar después de la tabla
    const tableWrapper = document.querySelector('.data-table-wrapper');
    if (tableWrapper && tableWrapper.parentNode) {
        tableWrapper.parentNode.insertBefore(gridContainer, tableWrapper.nextSibling);
        console.log('✅ Grid creado correctamente');
    } else {
        console.error('❌ No se pudo encontrar tableWrapper para insertar grid');
    }
    
    return gridContainer;
}

// Función para mostrar marcas en grid
function displaymarcasGridMarcas(marcas) {
    const gridContainer = document.querySelector('.marcas-grid');
    if (!gridContainer) {
        console.error('❌ No se encontró el contenedor grid de marcas');
        return;
    }
    
    // LIMPIAR COMPLETAMENTE antes de agregar nuevos datos
    gridContainer.innerHTML = '';
    
    if (!marcas || marcas.length === 0) {
        gridContainer.innerHTML = `
            <div class="no-marcas-message">
                <i class="fas fa-box-open"></i>
                <p>No se encontraron marcas</p>
            </div>
        `;
        return;
    }
    
    // Construir HTML completo y asignarlo UNA SOLA VEZ
    const htmlContent = marcas.map(marca => {
        const imageUrl = getmarcaImageUrlMarcas(marca, true);
        const fallbackImage = '/fashion-master/public/assets/img/default-product.jpg';
        
        return `
            <div class="marca-card" ondblclick="editMarca(${marca.id_marca})" style="cursor: pointer;" data-marca-id="${marca.id_marca}">
                <div class="marca-card-header">
                    <div class="marca-card-image" ondblclick="event.stopPropagation(); showImageFullSize('${imageUrl}', '${marca.nombre_marca || 'Marca'}')" style="cursor: zoom-in; border-radius: 12px; overflow: hidden;">
                        <img src="${imageUrl}" alt="${marca.nombre_marca || 'Marca'}" style="border-radius: 12px;" onerror="this.src='${fallbackImage}'; this.onerror=null;">
                    </div>
                    <h3 class="marca-card-title">${marca.nombre_marca || 'Sin nombre'}</h3>
                    <span class="marca-card-status ${marca.estado_marca === 'activo' ? 'active' : 'inactive'}">
                        ${marca.estado_marca === 'activo' ? 'Activo' : 'Inactivo'}
                    </span>
                </div>
                
                <div class="marca-card-body">
                    ${marca.codigo_marca ? `<div class="marca-card-sku">Código: ${marca.codigo_marca}</div>` : ''}
                    
                    ${marca.descripcion_marca ? `
                    <div class="marca-card-description">
                        <i class="fas fa-align-left"></i> ${marca.descripcion_marca.length > 80 ? marca.descripcion_marca.substring(0, 80) + '...' : marca.descripcion_marca}
                    </div>
                    ` : ''}
                    
                    <div class="marca-card-date">
                        <i class="fas fa-calendar"></i> ${formatearFechaMarcas(marca.fecha_creacion_marca)}
                    </div>
                </div>
                
                <div class="marca-card-actions">
                    <button class="marca-card-btn btn-view" onclick="event.stopPropagation(); viewMarca(${marca.id_marca})" title="Ver marca" style="background-color: #1a73e8 !important; color: white !important; border: none !important; box-shadow: 0 4px 8px rgba(26, 115, 232, 0.3) !important;">
                        <i class="fas fa-eye" style="color: white !important;"></i>
                    </button>
                    <button class="marca-card-btn btn-edit" onclick="event.stopPropagation(); editMarca(${marca.id_marca})" title="Editar marca" style="background-color: #34a853 !important; color: white !important; border: none !important; box-shadow: 0 4px 8px rgba(52, 168, 83, 0.3) !important;">
                        <i class="fas fa-edit" style="color: white !important;"></i>
                    </button>
                    <button class="marca-card-btn ${marca.estado_marca === 'activo' ? 'btn-deactivate' : 'btn-activate'}" 
                            onclick="event.stopPropagation(); changeMarcaEstado(${marca.id_marca})" 
                            title="${marca.estado_marca === 'activo' ? 'Desactivar' : 'Activar'} marca"
                            style="background-color: #6f42c1 !important; color: white !important; border: none !important;">
                        <i class="fas fa-${marca.estado_marca === 'activo' ? 'power-off' : 'toggle-on'}" style="color: white !important;"></i>
                    </button>
                    <button class="marca-card-btn btn-delete" onclick="event.stopPropagation(); deleteMarca(${marca.id_marca}, '${(marca.nombre_marca || 'marca').replace(/'/g, "\\'")}')\" title="Eliminar marca" style="background-color: #f44336 !important; color: white !important; border: none !important; box-shadow: 0 4px 8px rgba(244, 67, 54, 0.3) !important;">
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
let activeMarcaId = null;
let isAnimating = false;
let animationTimeout = null;
let floatingButtons = [];
let centerButton = null;

// Función principal para mostrar botones flotantes
function showActionMenuMarcas(MarcaId, MarcaName, cantidad_marcas, estado, event) {
    // CERRAR BURBUJA DE cantidad_marcas SI ESTÁ ABIERTA
    const existingBubbles = document.querySelectorAll('.cantidad_marcas-update-bubble');
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
    
    // Eliminar overlay de la burbuja de cantidad_marcas
    const cantidad_marcasOverlays = document.querySelectorAll('.cantidad_marcas-bubble-overlay');
    cantidad_marcasOverlays.forEach(overlay => {
        if (overlay && overlay.parentNode) {
            overlay.remove();
        }
    });
    
    // Prevenir múltiples ejecuciones
    if (isAnimating) return;
    
    // Si ya está abierto para el mismo Estado, cerrarlo
    if (activeFloatingContainer && activeMarcaId === MarcaId) {
        closeFloatingActionsAnimatedMarcas();
        return;
    }
    
    // Cerrar cualquier menú anterior
    if (activeFloatingContainer) {
        closeFloatingActionsAnimatedMarcas();
    }
    
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
            if (onclickAttr.includes(`showActionMenuMarcas(${MarcaId}`)) {
                triggerButton = btn;
                break;
            }
        }
    }
    
    if (!triggerButton) {
        return;
    }
    
    isAnimating = true;
    activeMarcaId = MarcaId;
    
    // Crear contenedor flotante con animaciones
    createAnimatedFloatingContainerMarcas(triggerButton, MarcaId, MarcaName, cantidad_marcas, estado);
}

// Crear el contenedor flotante con animaciones avanzadas
function createAnimatedFloatingContainerMarcas(triggerButton, MarcaId, MarcaName, cantidad_marcas, estado) {
    // Limpiar cualquier menú anterior
    if (activeFloatingContainer) {
        closeFloatingActionsAnimatedMarcas();
    }
    
    // Verificar que tenemos un trigger button válido
    if (!triggerButton) {
        isAnimating = false;
        return;
    }
    
    // Crear contenedor principal con ID único
    activeFloatingContainer = document.createElement('div');
    activeFloatingContainer.id = 'animated-floating-menu-' + MarcaId;
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
    
    // Crear botón central con los tres puntitos
    createCenterButtonMarcas();
    
    // Definir acciones con colores vibrantes
    // Definir acciones con colores vibrantes (usando closures para capturar event)
    const actions = [
        { icon: 'fa-eye', color: '#1a73e8', label: 'Ver', actionFn: () => viewMarca(MarcaId) },
        { icon: 'fa-edit', color: '#34a853', label: 'Editar', actionFn: () => editMarca(MarcaId) },
        { icon: estado === 'activo' ? 'fa-power-off' : 'fa-toggle-on', color: '#9c27b0', label: estado === 'activo' ? 'Desactivar' : 'Activar', actionFn: () => changeMarcaEstado(MarcaId) },
        { icon: 'fa-trash', color: '#f44336', label: 'Eliminar', actionFn: () => deleteMarca(MarcaId, MarcaName) }
    ];
    
    // Crear botones flotantes con animaciones
    floatingButtons = [];
    const radius = 80;
    
    actions.forEach((action, index) => {
        const angle = (index / actions.length) * 2 * Math.PI - Math.PI / 2;
        createAnimatedButtonMarcas(action, index, angle, radius);
    });
    
    // Agregar al contenedor de la tabla
    if (tableContainer) {
        tableContainer.appendChild(activeFloatingContainer);
    } else {
        document.body.appendChild(activeFloatingContainer);
    }
    
    // Actualizar posiciones iniciales
    updateAnimatedButtonPositionsMarcas();
    
    activeMarcaId = MarcaId;
    
    // Event listeners con animaciones
    setupAnimatedEventListenersMarcas();
    
    // Iniciar animación de entrada
    startOpenAnimationMarcas();
}

// Crear botón central con tres puntitos (para cerrar)
function createCenterButtonMarcas() {
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
        closeFloatingActionsAnimatedMarcas();
    });
    
    activeFloatingContainer.appendChild(centerButton);
}

// Crear botón animado individual
function createAnimatedButtonMarcas(action, index, angle, radius) {
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
        createRippleEffectMarcas(button, action.color);
        
        // Mostrar tooltip
        showTooltipMarcas(button, action.label);
    });
    
    button.addEventListener('mouseleave', () => {
        button.style.transform = 'scale(1) rotate(0deg)';
        button.style.boxShadow = `0 6px 20px ${action.color}40`;
        button.style.zIndex = '1000001';
        
        // Ocultar tooltip
        hideTooltipMarcas();
    });
    
    // Click handler con animación
    button.addEventListener('click', (e) => {
        e.stopPropagation();
        
        // Forzar cierre inmediato del menú
        forceCloseFloatingActionsMarcas();
        
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
    
    activeFloatingContainer.appendChild(button);
    floatingButtons.push(button);
}

// Crear efecto ripple
function createRippleEffectMarcas(button, color) {
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
function showTooltipMarcas(button, text) {
    // Remover tooltip anterior si existe
    hideTooltipMarcas();
    
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
function hideTooltipMarcas() {
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
function updateAnimatedButtonPositionsMarcas() {
    if (!activeFloatingContainer) {
        return;
    }
    
    if (!activeFloatingContainer.triggerButton) {
        return;
    }
    
    // Verificar que el trigger button aún existe en el DOM
    if (!document.contains(activeFloatingContainer.triggerButton)) {
        closeFloatingActionsAnimatedMarcas();
        return;
    }
    
    // Obtener el contenedor padre donde están los botones
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
    
    // Calcular posición relativa del trigger respecto al contenedor
    const centerX = triggerRect.left - containerRect.left + triggerRect.width / 2;
    const centerY = triggerRect.top - containerRect.top + triggerRect.height / 2;
    
    // Ajustar por scroll del contenedor si es necesario
    const scrollLeft = container.scrollLeft || 0;
    const scrollTop = container.scrollTop || 0;
    
    const finalCenterX = centerX + scrollLeft;
    const finalCenterY = centerY + scrollTop;
    
    // Actualizar posición del botón central
    if (centerButton) {
        centerButton.style.left = `${finalCenterX - 22.5}px`;
        centerButton.style.top = `${finalCenterY - 22.5}px`;
    }
    
    // Actualizar posición de cada botón flotante
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

// Iniciar animación de apertura
function startOpenAnimationMarcas() {
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
function setupAnimatedEventListenersMarcas() {
    // Cerrar al hacer click fuera con animación
    const handleClick = (e) => {
        if (activeFloatingContainer && !activeFloatingContainer.contains(e.target)) {
            closeFloatingActionsAnimatedMarcas();
        }
    };
    
    // Actualizar posiciones en resize
    const handleResize = () => {
        if (activeFloatingContainer) {
            setTimeout(() => {
                updateAnimatedButtonPositionsMarcas();
            }, 100);
        }
    };
    
    // Manejar scroll del contenedor padre
    const handleScroll = () => {
        if (activeFloatingContainer) {
            updateAnimatedButtonPositionsMarcas();
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

// Cerrar menú flotante con animación avanzada
function closeFloatingActionsAnimatedMarcas() {
    if (!activeFloatingContainer || isAnimating) return;
    
    // Verificar que el contenedor aún existe en el DOM
    if (!document.contains(activeFloatingContainer)) {
        // Resetear variables si el contenedor ya fue removido
        activeFloatingContainer = null;
        centerButton = null;
        floatingButtons = [];
        activeMarcaId = null;
        isAnimating = false;
        return;
    }
    
    isAnimating = true;
    
    // Limpiar timeout anterior si existe
    if (animationTimeout) {
        clearTimeout(animationTimeout);
    }
    
    // Ocultar tooltip si existe
    hideTooltipMarcas();
    
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
        activeMarcaId = null;
        isAnimating = false;
    }, floatingButtons.length * 50 + 400);
}

// Mantener compatibilidad con función anterior
function closeFloatingActionsMarcas() {
    closeFloatingActionsAnimatedMarcas();
}

// Función para forzar el cierre con retraso del menú flotante
function forceCloseFloatingActionsMarcas() {
    // Agregar un retraso antes del cierre forzado
    setTimeout(() => {
        // Limpiar cualquier timeout pendiente
        if (animationTimeout) {
            clearTimeout(animationTimeout);
            animationTimeout = null;
        }
        
        // Ocultar tooltip inmediatamente
        hideTooltipMarcas();
        
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
            activeMarcaId = null;
            isAnimating = false;
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



// Función para exportar Estados
async function exportMarcas() {
    
    try {
        // // showNotification('Preparando exportación...', 'info');
        
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
            a.download = `marcas_${new Date().toISOString().split('T')[0]}.csv`;
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
        if (marcas && marcas.length > 0) {
            generateClientSideCSV();
        } else {
            // // showNotification('No hay marcas para exportar', 'warning');
        }
    }
}

// Función para generar CSV del lado del cliente
function generateClientSideCSV() {
    const headers = ['ID', 'Código', 'Nombre', 'Descripción', 'Estado', 'Fecha Creación'];
    let csvContent = headers.join(',') + '\n';
    
    marcas.forEach(marca => {
        const row = [
            marca.id_marca || '',
            marca.codigo_marca || '',
            `"${(marca.nombre_marca || '').replace(/"/g, '""')}"`,
            `"${(marca.descripcion_marca || '').replace(/"/g, '""')}"`,
            marca.estado_marca === 'activo' ? 'Activo' : 'Inactivo',
            marca.fecha_creacion_marca || ''
        ];
        csvContent += row.join(',') + '\n';
    });
    
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.style.display = 'none';
    a.href = url;
    a.download = `marcas_${new Date().toISOString().split('T')[0]}.csv`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
    
    // showNotification('Marcas exportadas exitosamente', 'success');
}

// Función para mostrar reporte de cantidad_marcas
function showMarcasReport() {
    // Implementar modal de reporte de marcas
    // showNotification('Reporte de marcas - Funcionalidad en desarrollo', 'info');
}

// Función para limpiar búsqueda con animación
function clearMarcaSearch() {
    if (typeof $ !== 'undefined') {
        const searchInput = $('#search-marcas');
        searchInput.val('').focus();
        
        // Animación visual
        const searchContainer = searchInput.parent();
        searchContainer.addClass('search-cleared');
        
        setTimeout(() => {
            searchContainer.removeClass('search-cleared');
        }, 300);
    } else {
        // Fallback vanilla JS
        const searchInput = document.getElementById('search-marcas');
        if (searchInput) {
            searchInput.value = '';
            searchInput.focus();
        }
    }
    
    filtermarcasMarcas();
}

// Función para limpiar todos los filtros con efectos visuales
function clearAllMarcaFilters() {
    if (typeof $ !== 'undefined') {
        // Limpiar todos los campos con jQuery
        $('#search-marcas').val('');
        $('#filter-marca').val('');
        $('#filter-status').val('');
        $('#filter-cantidad_marcas').val('');
        
        // Efecto visual de limpieza
        $('.module-filters').addClass('filters-clearing');
        
        setTimeout(() => {
            $('.module-filters').removeClass('filters-clearing');
        }, 400);
    } else {
        // Fallback vanilla JS
        const elements = [
            'search-marcas',
            'filter-estado',
            'filter-status',
            'filter-cantidad_marcas'
        ];
        
        elements.forEach(id => {
            const element = document.getElementById(id);
            if (element) element.value = '';
        });
    }
    
    // Mostrar notificación
    // showNotification('Filtros limpiados', 'info');
    
    filtermarcasMarcas();
}

// Función para acciones en lote
async function handleBulkMarcaAction(action) {
    const selectedMarcas = getSelectedMarcas();
    
    if (selectedMarcas.length === 0) {
        // // showNotification('Por favor selecciona al menos una Marca', 'warning');
        return;
    }    
    const confirmMessage = `¿Estás seguro de ${action} ${selectedMarcas.length} Marca(s)?`;
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
            body: JSON.stringify({ ids: selectedMarcas })
        });
        
        const result = await response.json();
        
        if (response.ok) {
            // showNotification(`${action} completado para ${selectedMarcas.length} Marca(s)`, 'success');
            loadMarcasData(); // Recargar lista
            clearMarcaSelection();
        } else {
            throw new Error(result.message || 'Error en operación en lote');
        }
        
    } catch (error) {
        // showNotification('Error: ' + error.message, 'error');
    }
}

// Función para toggle select all
function toggleSelectAllMarcas(checkbox) {
    
    const MarcaCheckboxes = document.querySelectorAll('input[name="Marca_select"]');
    MarcaCheckboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
    
    updateBulkActionButtons();
}

// Función para ver Estado (wrapper que llama al parent)
function viewMarca(id) {
    if (!id || typeof id === 'undefined' || id === null) {
        console.error('❌ ID inválido para ver:', id);
        return;
    }
    
    // CERRAR MENÚ FLOTANTE antes de abrir modal
    closeFloatingActionsAnimatedMarcas();
    
    // Usar la nueva función modal
    showViewMarcaModal(id);
}

// ===== FUNCIÓN GLOBAL PARA CERRAR BURBUJA DE cantidad_marcas =====
function closecantidad_marcasBubble() {
    const existingBubbles = document.querySelectorAll('.cantidad_marcas-update-bubble');
    const existingOverlays = document.querySelectorAll('.cantidad_marcas-bubble-overlay');
    
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
}

// ===== FUNCIONES DE MODALES PARA MARCAS =====

function showCreateMarcaModal() {
    // CERRAR MENÚ FLOTANTE si está abierto
    if (activeFloatingContainer) {
        closeFloatingActionsAnimatedMarcas();
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
    fetch('app/views/admin/marca_modal.php?action=create')
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

function showEditMarcaModal(id) {
    // CERRAR MENÚ FLOTANTE si está abierto
    if (activeFloatingContainer) {
        closeFloatingActionsAnimatedMarcas();
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
                <p>Cargando marca...</p>
            </div>
        </div>
    `;
    
    document.body.appendChild(overlay);
    document.body.classList.add('modal-open');
    
    requestAnimationFrame(() => {
        overlay.classList.add('show');
    });
    
    fetch(`app/views/admin/marca_modal.php?action=edit&id=${id}`)
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

function showViewMarcaModal(id) {
    // CERRAR MENÚ FLOTANTE si está abierto
    if (activeFloatingContainer) {
        closeFloatingActionsAnimatedMarcas();
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
                <p>Cargando marca...</p>
            </div>
        </div>
    `;
    
    document.body.appendChild(overlay);
    document.body.classList.add('modal-open');
    
    requestAnimationFrame(() => {
        overlay.classList.add('show');
    });
    
    fetch(`app/views/admin/marca_modal.php?action=view&id=${id}`)
        .then(response => response.text())
        .then(html => {
            const wrapper = overlay.querySelector('#modal-content-wrapper');
            if (wrapper) {
                wrapper.outerHTML = html;
                
                // Buscar el modal de ver marca y agregarle la clase "show" con animación
                const viewModal = overlay.querySelector('.product-view-modal, #marcaViewModal');
                if (viewModal) {
                    // Agregar clase show después de un frame para activar la animación
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

function closeMarcaModal() {
    console.log('🚪 closeMarcaModal() llamado');
    
    // BUSCAR MODAL DE VER (puede estar directo en body o dentro de overlay)
    const viewModalDirect = document.querySelector('body > .product-view-modal, body > #marcaViewModal');
    const overlay = document.getElementById('product-modal-overlay');
    
    // CASO 1: Modal de VER directo en body (sin overlay)
    if (viewModalDirect && !overlay) {
        console.log('✅ Cerrando modal de VER (directo en body)...');
        
        // Remover inmediatamente sin animación
        viewModalDirect.remove();
        document.body.classList.remove('modal-open');
        console.log('✅ Modal de VER removido del DOM');
        
        return;
    }
    
    // CASO 2: Modal dentro de overlay
    if (overlay) {
        console.log('✅ Cerrando modal con overlay...');
        
        // Remover inmediatamente sin animación
        overlay.remove();
        document.body.classList.remove('modal-open');
        console.log('✅ Overlay removido del DOM');
        
        return;
    }
    
    // CASO 3: No se encontró ningún modal
    console.warn('⚠️ No se encontró ningún modal abierto para cerrar');
    document.body.classList.remove('modal-open');
}

// Función para editar Estado
async function editMarca(id) {
    if (!id || typeof id === 'undefined' || id === null) {
        console.error('❌ ID inválido para editar:', id);
        return;
    }
    
    // CERRAR MENÚ FLOTANTE antes de abrir modal
    closeFloatingActionsAnimatedMarcas();
    
    // Usar la nueva función modal
    showEditMarcaModal(id);
}

// Función para actualizar cantidad_marcas - MEJORADA CON BURBUJA SIN BOTONES
function updatecantidad_marcas(id, currentcantidad_marcas, event) {
    // VERIFICAR SI YA EXISTE UNA BURBUJA ABIERTA PARA ESTE Estado (TOGGLE)
    const existingBubble = document.querySelector(`.cantidad_marcas-update-bubble[data-Marca-id="${id}"]`);
    if (existingBubble) {
        closecantidad_marcasBubble();
        return; // SALIR - No abrir de nuevo
    }
    
    // CERRAR MENÚ FLOTANTE SI ESTÁ ABIERTO (sin bloquear futuros menús)
    if (activeFloatingContainer) {
        // Cerrar con animación
        closeFloatingActionsAnimatedMarcas();
    }
    
    // Forzar eliminación de cualquier menú flotante residual
    const allFloatingMenus = document.querySelectorAll('.animated-floating-container');
    allFloatingMenus.forEach(menu => {
        if (menu && menu.parentNode) {
            menu.remove();
        }
    });
    
    // Resetear variables globales del menú flotante
    activeFloatingContainer = null;
    activeMarcaId = null;
    isAnimating = false;
    if (animationTimeout) {
        clearTimeout(animationTimeout);
        animationTimeout = null;
    }
    
    // Eliminar cualquier burbuja existente (de otros Estados)
    closecantidad_marcasBubble();
    
    // Crear overlay SIN bloquear scroll - solo para detectar clicks
    const overlay = document.createElement('div');
    overlay.className = 'cantidad_marcas-bubble-overlay';
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
    
    // Crear burbuja de cantidad_marcas - PEQUEÑA (50x50px) estilo botones flotantes, expandible hasta 3 dígitos
    const cantidad_marcasBubble = document.createElement('div');
    cantidad_marcasBubble.className = 'cantidad_marcas-update-bubble';
    cantidad_marcasBubble.setAttribute('data-Marca-id', id); // Agregar ID del Estado para identificar
    cantidad_marcasBubble.innerHTML = `
        <input type="number" 
               id="cantidad_marcasInput" 
               value="${currentcantidad_marcas}" 
               min="0" 
               max="999"
               class="cantidad_marcas-input-circle"
               placeholder="0"
               autocomplete="off"
               maxlength="3"
               style="border: none !important; outline: none !important; box-shadow: none !important; text-decoration: none !important; -webkit-appearance: none !important; border-bottom: none !important;">
    `;
    
    // Encontrar el botón que disparó la acción (puede ser btn-menu de tabla o btn-cantidad_marcas de grid)
    // Primero intentar obtenerlo del evento
    let triggerButton = null;
    let isGridView = false;
    
    if (event) {
        // Intentar desde currentTarget
        triggerButton = event.currentTarget;
        
        // Verificar si es un botón de la vista grid
        if (triggerButton && triggerButton.classList.contains('Marca-card-btn')) {
            isGridView = true;
        }
        // Si es un botón flotante, ignorar y buscar el botón real
        else if (triggerButton && triggerButton.classList.contains('animated-floating-button')) {
            triggerButton = null; // Resetear para buscar el botón correcto
        }
        // Si es el btn-menu de la tabla
        else if (triggerButton && triggerButton.classList.contains('btn-menu')) {
            isGridView = false;
        }
    }
    
    // Si aún no tenemos el botón, buscarlo en el DOM por el ID del Estado
    if (!triggerButton) {
        // Primero buscar en vista grid
        const MarcaCard = document.querySelector(`.Marca-card[data-Marca-id="${id}"]`);
        if (MarcaCard) {
            triggerButton = MarcaCard.querySelector('.btn-cantidad_marcas');
            if (triggerButton) {
                isGridView = true;
            }
        }
        
        // Si no está en grid, buscar en la tabla
        if (!triggerButton) {
            const MarcaRow = document.querySelector(`tr[data-Marca-id="${id}"]`);
            if (MarcaRow) {
                triggerButton = MarcaRow.querySelector('.btn-menu');
                if (triggerButton) {
                    isGridView = false;
                }
            }
        }
    }
    
    // Último recurso: buscar por atributo onclick en la tabla
    if (!triggerButton) {
        triggerButton = document.querySelector(`[onclick*="showActionMenuMarcas(${id}"]`);
        if (triggerButton) {
            isGridView = false;
        }
    }
    
    if (!triggerButton) {
        console.error('❌ No se encontró el botón para el Estado', id);
        return;
    }
    
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
    
    // Aplicar estilos - POSICIÓN FIXED (viewport) como botones flotantes - Se expande según dígitos
    cantidad_marcasBubble.style.cssText = `
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
    cantidad_marcasBubble.triggerButton = triggerButton;
    cantidad_marcasBubble.isGridView = isGridView;
    
    // Estilos para el input - SIN SUBRAYADO y con expansión ovalada
    const style = document.createElement('style');
    style.id = 'cantidad_marcas-bubble-styles';
    style.textContent = `
        .cantidad_marcas-update-bubble {
            white-space: nowrap;
        }
        
        .cantidad_marcas-input-circle {
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
        
        .cantidad_marcas-input-circle:focus,
        .cantidad_marcas-input-circle:active,
        .cantidad_marcas-input-circle:hover,
        .cantidad_marcas-input-circle:visited,
        .cantidad_marcas-input-circle:focus-visible,
        .cantidad_marcas-input-circle:focus-within {
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
        
        .cantidad_marcas-input-circle::-webkit-outer-spin-button,
        .cantidad_marcas-input-circle::-webkit-inner-spin-button {
            -webkit-appearance: none !important;
            margin: 0 !important;
            display: none !important;
        }
        
        .cantidad_marcas-input-circle[type=number] {
            -moz-appearance: textfield !important;
        }
        
        .cantidad_marcas-input-circle::placeholder {
            color: rgba(255, 255, 255, 0.5) !important;
            font-size: 18px !important;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2) !important;
        }
        
        /* Forzar eliminación de cualquier estilo de Chrome/Edge */
        input[type=number].cantidad_marcas-input-circle::-webkit-textfield-decoration-container {
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
    const oldStyle = document.getElementById('cantidad_marcas-bubble-styles');
    if (oldStyle) oldStyle.remove();
    document.head.appendChild(style);
    
    // Agregar overlay al body (sin bloquear scroll)
    document.body.appendChild(overlay);
    
    // Agregar burbuja al BODY (position fixed)
    document.body.appendChild(cantidad_marcasBubble);
    
    // Actualizar posición en scroll/resize (con position fixed)
    const updateBubblePosition = () => {
        if (!cantidad_marcasBubble || !cantidad_marcasBubble.triggerButton) return;
        
        const triggerRect = cantidad_marcasBubble.triggerButton.getBoundingClientRect();
        
        const centerX = triggerRect.left + triggerRect.width / 2;
        const centerY = triggerRect.top + triggerRect.height / 2;
        
        const bubbleSize = 40;
        const radius = 65;
        
        // Usar el ángulo guardado según la vista
        const angle = cantidad_marcasBubble.isGridView ? (-Math.PI / 2) : Math.PI;
        
        const bubbleX = centerX + Math.cos(angle) * radius - bubbleSize / 2;
        const bubbleY = centerY + Math.sin(angle) * radius - bubbleSize / 2;
        
        if (cantidad_marcasBubble && cantidad_marcasBubble.style) {
            cantidad_marcasBubble.style.left = bubbleX + 'px';
            cantidad_marcasBubble.style.top = bubbleY + 'px';
        }
    };
    
    // Listener para scroll/resize
    window.addEventListener('scroll', updateBubblePosition, true);
    window.addEventListener('resize', updateBubblePosition);
    cantidad_marcasBubble.updatePositionListener = updateBubblePosition;
    
    // Activar animación de entrada con reflow
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            if (cantidad_marcasBubble && cantidad_marcasBubble.style) {
                cantidad_marcasBubble.style.transform = 'scale(1)';
                cantidad_marcasBubble.style.opacity = '1';
            }
        });
    });
    
    // Focus en el input
    setTimeout(() => {
        const input = cantidad_marcasBubble?.querySelector('#cantidad_marcasInput');
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
                
                cantidad_marcasBubble.style.width = newWidth + 'px';
                
                // Recalcular posición para centrar la burbuja expandida
                const triggerRect = triggerButton.getBoundingClientRect();
                const centerX = triggerRect.left + (triggerRect.width / 2);
                const centerY = triggerRect.top + (triggerRect.height / 2);
                const radius = 65;
                const angle = isGridView ? (-Math.PI / 2) : Math.PI;
                
                const bubbleX = centerX + (Math.cos(angle) * radius) - (newWidth / 2);
                const bubbleY = centerY + (Math.sin(angle) * radius) - (40 / 2);
                
                cantidad_marcasBubble.style.left = bubbleX + 'px';
                cantidad_marcasBubble.style.top = bubbleY + 'px';
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
    function savecantidad_marcas() {
        if (!cantidad_marcasBubble) {
            console.error('❌ cantidad_marcasBubble no existe');
            return;
        }
        
        const input = cantidad_marcasBubble.querySelector('#cantidad_marcasInput');
        if (!input) {
            console.error('❌ input no existe');
            return;
        }
        
        const newcantidad_marcas = parseInt(input.value);
        
        if (isNaN(newcantidad_marcas) || newcantidad_marcas < 0 || newcantidad_marcas > 999) {
            // Animación de error - shake sin afectar el scale
            const originalTransform = cantidad_marcasBubble.style.transform;
            cantidad_marcasBubble.style.animation = 'shake 0.5s ease-in-out';
            input.style.color = '#fee2e2';
            input.style.textShadow = '0 0 10px rgba(239, 68, 68, 0.8)';
            
            setTimeout(() => {
                if (cantidad_marcasBubble) {
                    cantidad_marcasBubble.style.animation = '';
                    cantidad_marcasBubble.style.transform = originalTransform;
                }
                if (input) {
                    input.style.color = '';
                    input.style.textShadow = '';
                }
            }, 500);
            return;
        }
        
        // Animación de salida
        cantidad_marcasBubble.style.transform = 'scale(0)';
        cantidad_marcasBubble.style.opacity = '0';
        
        // Limpiar click outside handler
        if (clickOutsideHandler) {
            document.removeEventListener('click', clickOutsideHandler);
            clickOutsideHandler = null;
        }
        
        // Llamada AJAX para actualizar cantidad_marcas
        const formData = new FormData();
        formData.append('action', 'update_cantidad_marcas');
        formData.append('id', id);
        formData.append('cantidad_marcas', newcantidad_marcas);
        
        fetch(`${CONFIG.apiUrl}`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Mostrar notificación de éxito
                if (typeof showNotification === 'function') {
                    // showNotification(`✅ cantidad_marcas actualizado a ${newcantidad_marcas} unidades`, 'success');
                }
                
                // Usar actualización suave si está disponible
                if (window.marcaSmoothTableUpdater && data.Marca) {
                    window.marcaSmoothTableUpdater.updateSingleMarca(data.Marca);
                } else {
                    // Actualizar lista inmediatamente
                    loadMarcas(true);
                }
                
                // Cerrar burbuja y overlay
                setTimeout(() => {
                    if (overlay && overlay.parentNode) overlay.remove();
                    if (cantidad_marcasBubble && cantidad_marcasBubble.parentNode) cantidad_marcasBubble.remove();
                }, 400);
            } else {
                if (typeof showNotification === 'function') {
                    // showNotification('❌ Error al actualizar cantidad_marcas', 'error');
                }
                if (overlay && overlay.parentNode) overlay.remove();
                if (cantidad_marcasBubble && cantidad_marcasBubble.parentNode) cantidad_marcasBubble.remove();
            }
        })
        .catch(error => {
            if (typeof showNotification === 'function') {
                // showNotification('❌ Error de conexión', 'error');
            }
            if (overlay && overlay.parentNode) overlay.remove();
            if (cantidad_marcasBubble && cantidad_marcasBubble.parentNode) cantidad_marcasBubble.remove();
        });
    }
    
    // Variable para guardar el handler del click outside
    let clickOutsideHandler = null;
    
    // Función para cerrar sin guardar
    function closeBubble() {
        if (!cantidad_marcasBubble) return;
        
        // Limpiar listeners
        if (cantidad_marcasBubble.updatePositionListener) {
            window.removeEventListener('scroll', cantidad_marcasBubble.updatePositionListener, true);
            window.removeEventListener('resize', cantidad_marcasBubble.updatePositionListener);
        }
        
        // Limpiar click outside handler
        if (clickOutsideHandler) {
            document.removeEventListener('click', clickOutsideHandler);
            clickOutsideHandler = null;
        }
        
        cantidad_marcasBubble.style.transform = 'scale(0)';
        cantidad_marcasBubble.style.opacity = '0';
        setTimeout(() => {
            if (overlay && overlay.parentNode) overlay.remove();
            if (cantidad_marcasBubble && cantidad_marcasBubble.parentNode) cantidad_marcasBubble.remove();
        }, 400);
    }
    
    // Eventos del input
    const input = cantidad_marcasBubble.querySelector('#cantidad_marcasInput');
    
    if (!input) {
        console.error('❌ No se encontró el input de cantidad_marcas');
        return;
    }
    
    // Guardar con Enter
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            savecantidad_marcas();
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
            savecantidad_marcas(); // Guardar al hacer click fuera
        }
    });
    
    // MANTENER pointer-events: none en overlay para permitir scroll
    // El click se detectará solo cuando hagamos click en el área del overlay
    
    // Prevenir que clicks en la burbuja cierren el overlay
    cantidad_marcasBubble.addEventListener('click', function(e) {
        e.stopPropagation();
    });
    
    // Cerrar al hacer click fuera (usando evento en document)
    clickOutsideHandler = function(e) {
        // Si el click no es en la burbuja ni en el overlay, guardar
        if (!cantidad_marcasBubble.contains(e.target) && e.target !== cantidad_marcasBubble) {
            savecantidad_marcas();
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
async function toggleMarcaStatus(id, currentStatus) {
    
    const newStatus = !currentStatus;
    const action = newStatus ? 'activar' : 'desactivar';
    
    if (!confirm(`¿Estás seguro de ${action} este Estado?`)) return;
    
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
            loadMarcas(); // Recargar lista
        } else {
            throw new Error(result.message || 'Error al cambiar estado');
        }
        
    } catch (error) {
        // showNotification('Error: ' + error.message, 'error');
    }
}

// Función para cambiar estado de la marca (activo/inactivo)
async function changeMarcaEstado(id) {
    try {
        // CERRAR MENÚ FLOTANTE antes de cambiar estado
        closeFloatingActionsAnimatedMarcas();
        
        // Obtener estado actual de la marca
        const response = await fetch(`${CONFIG.apiUrl}?action=get&id=${id}`);
        const result = await response.json();
        
        if (!response.ok || !result.success) {
            console.error('Error al obtener datos de la marca');
            return;
        }
        
        const marca = result.data || result.marca;
        const currentEstado = marca.estado_marca;
        const newEstado = currentEstado === 'activo' ? 'inactivo' : 'activo';
        
        // Cambiar estado directamente sin confirmación
        const updateResponse = await fetch(`${CONFIG.apiUrl}?action=change_estado`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `id_marca=${id}&estado=${newEstado}`
        });
        
        const updateResult = await updateResponse.json();
        
        if (updateResponse.ok && updateResult.success) {
            // Usar actualización suave si está disponible
            const marcaData = updateResult.data;
            
            // ✅ CORRECCIÓN: Usar marcaSmoothTableUpdater específico para marcas
            if (window.marcaSmoothTableUpdater && marcaData) {
                // Asegurar que el ID sea número
                const marcaId = parseInt(marcaData.id_marca || id);
                
                window.marcaSmoothTableUpdater.updateSingleMarca(marcaId, marcaData)
                    .then(() => {
                        // Tabla actualizada sin recargar página
                    })
                    .catch(err => {
                        console.error('❌ Error en updateSingleMarca:', err);
                        loadMarcasData();
                    });
            } else {
                loadMarcasData();
            }
        } else {
            console.error('Error al cambiar estado:', updateResult.error);
        }
        
    } catch (error) {
        console.error('Error en changeMarcaEstado:', error.message);
    }
}


// ============ FUNCIONES DE PAGINACIÓN ============

function goToFirstPageMarcas() {
    if (currentPage > 1) {
        currentPage = 1;
        loadMarcasData();
    }
}

function previousPageMarcas() {
    if (currentPage > 1) {
        currentPage--;
        loadMarcasData();
    }
}

function nextPageMarcas() {
    if (currentPage < totalPages) {
        currentPage++;
        loadMarcasData();
    }
}

function goToLastPageMarcas() {
    if (currentPage < totalPages) {
        currentPage = totalPages;
        loadMarcasData();
    }
}

// ============ FUNCIONES AUXILIARES ============

// Función para obtener Estados seleccionados
function getSelectedMarcas() {
    const checkboxes = document.querySelectorAll('input[name="Marca_select"]:checked');
    return Array.from(checkboxes).map(cb => parseInt(cb.value));
}

// Función para limpiar selección de Estados
function clearMarcaSelection() {
    const checkboxes = document.querySelectorAll('input[name="Marca_select"]');
    checkboxes.forEach(cb => cb.checked = false);
    
    const selectAll = document.querySelector('input[type="checkbox"][onchange*="toggleSelectAllMarcas"]');
    if (selectAll) selectAll.checked = false;
    
    updateBulkActionButtons();
}

// Función para actualizar botones de acciones en lote
function updateBulkActionButtons() {
    const selected = getSelectedMarcas();
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
    const showingStartEl = document.getElementById('showing-start-Marcas');
    const showingEndEl = document.getElementById('showing-end-Estados');
    const totalMarcasEl = document.getElementById('total-Estados');
    
    if (showingStartEl) showingStartEl.textContent = showing > 0 ? 1 : 0;
    if (showingEndEl) showingEndEl.textContent = showing;
    if (totalMarcasEl) totalMarcasEl.textContent = total;
}

// ===== BANDERA DE INICIALIZACIÓN ÚNICA =====
window.marcasModuleInitialized = window.marcasModuleInitialized || false;

// 🔥 ARRAY PARA RASTREAR TIMEOUTS PENDIENTES
if (!window.marcasPendingTimeouts) {
    window.marcasPendingTimeouts = [];
}

// 🧹 FUNCIÓN PARA CANCELAR TODOS LOS TIMEOUTS PENDIENTES
window.cancelMarcasPendingTimeouts = function() {
    console.log('🧹 Cancelando', window.marcasPendingTimeouts.length, 'timeouts pendientes de marcas');
    window.marcasPendingTimeouts.forEach(timeoutId => {
        clearTimeout(timeoutId);
    });
    window.marcasPendingTimeouts = [];
};

// Función de inicialización principal
function initializeMarcasModule() {
    console.log('🎯 initializeMarcasModule() llamado');
    
    // 🔥 VERIFICACIÓN CRÍTICA: Solo inicializar si estamos en la sección de marcas
    const marcasPane = document.getElementById('marcas');
    if (!marcasPane || !marcasPane.classList.contains('active')) {
        console.warn('⚠️ Sección de marcas no está activa, abortando inicialización');
        return;
    }
    
    // 🔥 PREVENIR INICIALIZACIÓN MÚLTIPLE
    if (window.marcasModuleInitialized) {
        console.log('⚠️ Módulo ya inicializado, saltando...');
        return;
    }
    
    // 🧹 CANCELAR CUALQUIER TIMEOUT PENDIENTE ANTERIOR
    if (typeof window.cancelMarcasPendingTimeouts === 'function') {
        window.cancelMarcasPendingTimeouts();
    }
    
    // Asegurar que MARCA_CONFIG esté inicializado
    if (typeof window.MARCA_CONFIG === 'undefined' || !window.MARCA_CONFIG.apiUrl) {
        initializemarcaConfigMarcas();
    }

    // ✅ SIMPLIFICADO: En lugar de esperar al DOM con múltiples timeouts,
    // solo verificar que los elementos existen y cargar directamente
    console.log('🔍 Verificando disponibilidad del DOM...');
    
    const marcasContent = document.getElementById('marcas-content');
    const marcasTable = document.querySelector('.marcas-table');
    const tbody = document.getElementById('marcas-table-body');
    
    console.log('� Estado del DOM:');
    console.log('  - marcas-content:', !!marcasContent);
    console.log('  - marcas-table:', !!marcasTable);
    console.log('  - tbody:', !!tbody);
    
    if (!tbody) {
        console.error('❌ tbody no está disponible. El DOM puede no estar completamente cargado.');
        console.error('⚠️ No se cargarán marcas automáticamente. Llame a loadMarcasData() manualmente si es necesario.');
        // No llamar loadMarcasData() aquí, dejar que el usuario lo haga manualmente
    } else {
        console.log('✅ DOM disponible, cargando marcas...');
        
        // ✅ RESTAURAR VISTA GUARDADA EN LOCALSTORAGE
        try {
            const savedView = localStorage.getItem('marcas_current_view');
            if (savedView && savedView === 'grid') {
                console.log('📖 Restaurando vista guardada:', savedView);
                // Crear grid si no existe
                createGridViewMarcas();
                // Aplicar vista grid
                const gridViewBtn = document.querySelector('[data-view="grid"]');
                const tableViewBtn = document.querySelector('[data-view="table"]');
                const tableContainer = document.querySelector('.data-table-wrapper');
                const gridContainer = document.querySelector('.marcas-grid');
                
                if (gridViewBtn && tableViewBtn && tableContainer && gridContainer) {
                    gridViewBtn.classList.add('active');
                    tableViewBtn.classList.remove('active');
                    tableContainer.style.display = 'none';
                    gridContainer.style.display = 'grid';
                    console.log('✅ Vista grid restaurada correctamente');
                }
            }
        } catch (e) {
            console.warn('⚠️ Error al restaurar vista:', e);
        }
        
        // SIEMPRE cargar datos cuando se inicializa el módulo
        // Esto asegura que los datos estén frescos después de navegar entre secciones
        console.log('📊 Cargando datos de marcas...');
        loadMarcasData();
        
        // Inicializar funciones de UI que antes estaban en DOMContentLoaded/load
        if (typeof initializeTableScroll === 'function') {
            initializeTableScroll();
        }
        if (typeof initializeDragScroll === 'function') {
            initializeDragScroll();
        }
    }
    
    // MARCAR COMO INICIALIZADO
    window.marcasModuleInitialized = true;
    
    // Función de debugging para verificar funciones disponibles
    window.debugMarcasFunctions = function() {
        const functions = [
            'loadMarcas', 'filterMarcas', 'handleSearchInput', 
            'toggleViewMarcas', 'showActionMenuMarcas', 'editMarca', 'viewMarca', 'deleteMarca',
            'toggleMarcaStatus', 'exportMarcas'
        ];
        
        const parentFunctions = ['showEditMarcaModal', 'showViewMarcaModal', 'showCreateMarcaModal'];
        parentFunctions.forEach(func => {

        });
    };
}

// ✅ EXPONER LA FUNCIÓN DE INICIALIZACIÓN GLOBALMENTE
window.initializeMarcasModule = initializeMarcasModule;

// ✅ EJECUTAR INICIALIZACIÓN INMEDIATAMENTE (dentro del eval())
// Esto asegura que se ejecute en el momento correcto, cuando el DOM ya está listo
initializeMarcasModule();

// NO usar timeout fallback - causa problemas de ejecución cruzada entre secciones


// ============ FUNCIONES DE ESTADO PARA PRESERVACIÓN ============

// Función para obtener el término de búsqueda actual
window.getSearchTerm = function() {
    const searchInput = document.getElementById('search-marcas');
    return searchInput ? searchInput.value.trim() : '';
};

// Función para obtener los filtros actuales
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
function showDeleteConfirmationMarcas(MarcaId, MarcaName) {
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
                <h3><i class="fas fa-exclamation-triangle"></i> Confirmar Eliminación</h3>
            </div>
            <div class="delete-modal-body">
                <div class="warning-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <p>Para eliminar la Marca <strong>"${MarcaName}"</strong>, escribe la palabra <strong>"eliminar"</strong> en el campo de abajo:</p>
                
                <input type="text" id="deleteConfirmInput" class="confirmation-input" placeholder="Escribe 'eliminar' para confirmar" autocomplete="off">
                <div id="deleteError" class="delete-error">
                    Por favor escribe exactamente "eliminar" para confirmar
                </div>
            </div>
            <div class="delete-modal-footer">
                <button type="button" class="btn-cancel-delete" onclick="closeDeleteConfirmationMarcas()">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn-confirm-delete" onclick="confirmDeleteMarcas(${MarcaId}, '${MarcaName.replace(/'/g, "\\'")}')">
                    <i class="fas fa-trash"></i> Eliminar Marca
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
        
        // También agregar .show al modal interno
        const deleteModal = overlay.querySelector('.delete-confirmation-modal');
        if (deleteModal) {
            deleteModal.classList.add('show');
        }
    });
    
    // Focus en el input después de la animación
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
                confirmDeleteMarcas(MarcaId, MarcaName);
            }
        });
    }
    
    // Permitir cerrar con ESC
    const handleEsc = (e) => {
        if (e.key === 'Escape') {
            closeDeleteConfirmationMarcas();
            document.removeEventListener('keydown', handleEsc);
        }
    };
    document.addEventListener('keydown', handleEsc);
    
    // Cerrar al hacer click en el overlay (fondo oscuro)
    overlay.addEventListener('click', function(e) {
        // Solo cerrar si se hace click directamente en el overlay, no en el modal
        if (e.target === overlay) {
            closeDeleteConfirmationMarcas();
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
function closeDeleteConfirmationMarcas() {
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
        }, 250); // Duración de la animación fadeOut actualizada
    }
}

// Cerrar modal al hacer click en el backdrop
function setupDeleteModalBackdropCloseMarcas() {
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-confirmation-overlay')) {
            closeDeleteConfirmationMarcas();
        }
    });
}

// Cerrar modal al hacer click en el backdrop
function setupDeleteModalBackdropCloseMarcas() {
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-confirmation-overlay')) {
            closeDeleteConfirmationMarcas();
        }
    });
}

// Función para confirmar eliminación
function confirmDeleteMarcas(MarcaId, MarcaName) {
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
    formData.append('id_marca', MarcaId);
    
    fetch(window.MARCA_CONFIG.apiUrl, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        closeDeleteConfirmationMarcas();
        
        if (data.success) {
            // Mostrar notificación de éxito
            if (typeof showNotification === 'function') {
                // showNotification(`Estado "${MarcaName}" eliminado exitosamente`, 'success');
            }
            
            // Usar actualización suave si está disponible
            if (window.marcaSmoothTableUpdater) {
                window.marcaSmoothTableUpdater.removeMarca(MarcaId);
            } else {
                // Actualizar lista inmediatamente sin reload
                loadMarcas(true);
            }
        } else {
            if (typeof showNotification === 'function') {
                // showNotification('Error al eliminar Marca: ' + (data.error || 'Error desconocido'), 'error');
            } else {
                // alert('Error al eliminar Marca: ' + (data.error || 'Error desconocido'));
            }
        }
    })
    .catch(error => {
        closeDeleteConfirmationMarcas();
        if (typeof showNotification === 'function') {
            // showNotification('Error de conexión al eliminar Marca', 'error');
        } else {
            // alert('Error de conexión al eliminar Marca');
        }
    });
}

// Función para alternar estado del Estado (activo/inactivo)
function toggleMarcaStatus(MarcaId, currentStatus) {
    const newStatus = currentStatus ? 0 : 1;
    
    const formData = new FormData();
    formData.append('action', 'toggle_status');
    formData.append('id', MarcaId);
    formData.append('status', newStatus);
    
    fetch(window.MARCA_CONFIG.apiUrl, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Usar actualización suave si está disponible
            if (window.marcaSmoothTableUpdater && data.Marca) {
                window.marcaSmoothTableUpdater.updateSingleMarca(data.Marca);
            } else {
                // Actualizar lista inmediatamente sin reload
                loadMarcas(true);
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

// Función wrapper para eliminar Marca
function deleteMarca(MarcaId, MarcaName) {
    // CERRAR MENÚ FLOTANTE antes de mostrar confirmación
    closeFloatingActionsAnimatedMarcas();
    
    showDeleteConfirmationMarcas(MarcaId, MarcaName || 'Marca');
}

// ============ FUNCIÓN PARA MOSTRAR IMAGEN EN TAMAÑO REAL ============

function showImageFullSize(imageUrl, MarcaName) {
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
    img.alt = MarcaName || 'Estado';
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
        img.src = AppConfig ? AppConfig.getImageUrl('default-Marca.jpg') : '/fashion-master/public/assets/img/default-Marca.jpg';
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
        activeFloatingContainer = null;
        centerButton = null;
        floatingButtons = [];
        activeMarcaId = null;
        isAnimating = false;
    }
}, 2000); // Verificar cada 2 segundos

// Limpiar al cambiar de página o recargar
window.addEventListener('beforeunload', () => {
    forceCloseFloatingActionsMarcas();
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
// En su lugar, initializeMarcasModule() ya llama a esto directamente

// ✅ WRAPPER REMOVIDO - initializeTableScroll se llama directamente en displaymarcasMarcas()
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
        // No aplicar drag si se está clickeando en un botón, input o link
        if (e.target.closest('button, a, input, select, textarea, .btn-menu, .Marca-card-btn')) {
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

// ❌ DESACTIVADO: No usar DOMContentLoaded/load porque se acumulan event listeners
// document.addEventListener('DOMContentLoaded', function() {
//     initializeDragScroll();
// });

// window.addEventListener('load', function() {
//     initializeDragScroll();
// });
// En su lugar, initializeMarcasModule() llama a initializeDragScroll() directamente

// ===== FUNCIÓN DE DESTRUCCIÓN DEL MÓDULO DE MARCAS =====
window.destroyMarcasModule = function() {
    console.log('🗑️ Destruyendo módulo de marcas...');
    
    try {
        // 1. Limpiar variable de estado de carga
        if (typeof isLoading !== 'undefined') {
            isLoading = false;
        }
        
        // 2. Limpiar arrays de datos
        if (typeof marcas !== 'undefined') {
            marcas = [];
        }
        
        // 3. Resetear paginación
        if (typeof currentPage !== 'undefined') {
            currentPage = 1;
        }
        if (typeof totalPages !== 'undefined') {
            totalPages = 1;
        }
        
        // 4. Limpiar timeouts pendientes de marcas
        if (typeof marcasTimeouts !== 'undefined' && Array.isArray(marcasTimeouts)) {
            console.log(`🧹 Cancelando ${marcasTimeouts.length} timeouts pendientes de marcas`);
            marcasTimeouts.forEach(timeoutId => clearTimeout(timeoutId));
            marcasTimeouts = [];
        }
        
        // 5. Limpiar event listeners clonando elementos
        const searchInput = document.getElementById('search-marcas');
        if (searchInput && searchInput.parentNode) {
            const newSearch = searchInput.cloneNode(true);
            searchInput.parentNode.replaceChild(newSearch, searchInput);
        }
        
        const filterStatus = document.getElementById('filter-status-marca');
        if (filterStatus && filterStatus.parentNode) {
            const newFilterStatus = filterStatus.cloneNode(true);
            filterStatus.parentNode.replaceChild(newFilterStatus, filterStatus);
        }
        
        // 6. Limpiar modales de marcas
        const marcaModals = document.querySelectorAll('.marca-modal, [id*="marca-modal"]');
        marcaModals.forEach(modal => {
            modal.remove();
        });
        
        // 7. Limpiar confirmaciones de eliminación
        const deleteConfirmations = document.querySelectorAll('.delete-confirmation-overlay');
        deleteConfirmations.forEach(confirmation => {
            confirmation.remove();
        });
        
        // 7.5. Limpiar botones flotantes y burbujas (NUEVO)
        const floatingContainers = document.querySelectorAll('.animated-floating-container');
        floatingContainers.forEach(container => {
            container.remove();
        });
        
        const bubbles = document.querySelectorAll('.cantidad_marcas-update-bubble');
        bubbles.forEach(bubble => {
            bubble.remove();
        });
        
        // 8. Limpiar el tbody de la tabla
        const tbody = document.getElementById('marcas-table-body');
        if (tbody) {
            tbody.innerHTML = '';
        }
        
        // 9. RESETEAR VISTA A TABLA (estado inicial)
        console.log('🔄 [MARCAS] Reseteando vista a tabla (estado inicial)...');
        
        // Remover vista grid si existe
        const gridContainer = document.querySelector('.marcas-grid');
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
            localStorage.removeItem('marcas_current_view'); // ← Nombre correcto
            localStorage.removeItem('marcas_view_mode'); // Limpiar ambos por compatibilidad
        } catch (e) {}
        
        console.log('✅ [MARCAS] Vista reseteada a tabla');
        
        // 10. RESETEAR BANDERA DE INICIALIZACIÓN (CRÍTICO)
        window.marcasModuleInitialized = false;
        console.log('🔄 [MARCAS] Bandera de inicialización reseteada');
        
        // 11. Remover clases de body que puedan interferir
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
        
        console.log('✅ Módulo de marcas destruido correctamente');
        
    } catch (error) {
        console.error('❌ Error al destruir módulo de marcas:', error);
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

/* ===== FORZAR PRIMER PLANO PARA ELEMENTOS FLOTANTES ===== */
.animated-floating-container,
.animated-floating-button,
.cantidad_marcas-update-bubble {
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

/* Asegurar que los tooltips también estén en primer plano */
.floating-tooltip {
    z-index: 1000000 !important;
}

/* Forzar primer plano en elementos específicos que pueden interferir */
.modal-content,
.modal-overlay,
#Marca-modal-overlay {
    z-index: 99999 !important;
}

/* Asegurar que las burbujas estén por encima de modales */
.animated-floating-container,
.cantidad_marcas-update-bubble {
    z-index: 1000001 !important;
}
</style>



