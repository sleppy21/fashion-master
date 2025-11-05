// ============ CARGAR SCRIPT ESPECÍFICO DE PRODUCTOS ============
(function() {
    // Solo cargar si no está ya cargado
    if (!document.querySelector('script[src*="smooth-table-update.js"]')) {
        const script = document.createElement('script');
        script.src = 'public/assets/js/smooth-table-update.js';
        script.onload = function() {
            // Disparar evento personalizado cuando el script se cargue
            window.dispatchEvent(new Event('smoothTableUpdaterLoaded'));
        };
        script.onerror = function() {
        };
        document.head.appendChild(script);
    } else {
        // Si ya está cargado, disparar el evento inmediatamente
        setTimeout(() => {
            window.dispatchEvent(new Event('smoothTableUpdaterLoaded'));
        }, 100);
    }
})();

// ============ CONFIGURACIÓN ============

// Esperar a que AppConfig esté disponible y luego inicializar CONFIG
function initializeConfig() {
    if (typeof AppConfig !== 'undefined') {
        window.CONFIG = {
            apiUrl: AppConfig.getApiUrl('ProductController.php')
        };
    } else {
        // Fallback si config.js no está cargado
        window.CONFIG = {
            apiUrl: '/fashion-master/app/controllers/ProductController.php'
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
let productos = [];

// 🐛 DEBUG MODE - Cambiar a false para producción
const DEBUG_MODE = false;

// Variables de paginación
let currentPage = 1;
let totalPages = 1;

// Variables de ordenamiento
let currentSortColumn = null;
let currentSortOrder = 'asc'; // 'asc' o 'desc'

// Variable para tracking de vista actual (tabla o grid)
window.productos_currentView = 'table'; // Por defecto tabla

// Variable global para fechas de productos (para Flatpickr)
window.productsDatesArray = [];

// ============ SISTEMA DE ACTUALIZACIÓN EN TIEMPO REAL ============
let autoRefreshInterval = null;
let lastUpdateTimestamp = Date.now();
const AUTO_REFRESH_DELAY = 30000; // 30 segundos

// Función para iniciar auto-refresh
function startAutoRefresh() {
    if (autoRefreshInterval) return; // Ya está activo
    
    autoRefreshInterval = setInterval(async () => {
        // Solo actualizar si no hay operaciones en curso
        if (!isLoading && window.productos_currentView === 'table') {
            await loadProductsSmooth();
        }
    }, AUTO_REFRESH_DELAY);
}

// Función para detener auto-refresh
function stopAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
        autoRefreshInterval = null;
    }
}

// Reiniciar timestamp de actualización
function resetUpdateTimestamp() {
    lastUpdateTimestamp = Date.now();
}

// ============ FUNCIONES DE LOG CONDICIONAL ============
function debugLog(...args) {
    if (DEBUG_MODE) console.log(...args);
}

function debugWarn(...args) {
    if (DEBUG_MODE) console.warn(...args);
}

// ============ MOBILE FILTERS SIDEBAR (shop.php style) ============

// ⭐ FUNCIÓN AUXILIAR: Sincronizar estado de vista
function ensureViewSync() {
    const gridContainer = document.querySelector('.products-grid');
    const tableContainer = document.querySelector('.data-table-wrapper');
    
    // Determinar cuál está realmente visible
    const gridVisible = gridContainer && gridContainer.style.display === 'grid';
    const tableVisible = tableContainer && tableContainer.style.display !== 'none' && !gridVisible;
    
    // Actualizar currentView basándose en la realidad del DOM
    if (gridVisible) {
        window.productos_currentView = 'grid';
    } else if (tableVisible) {
        window.productos_currentView = 'table';
    }
    
    return window.productos_currentView;
}

// Botón flotante de filtros móvil - Mostrar/ocultar según tamaño de pantalla
function toggleMobileFilterButton() {
    const btn = document.getElementById('btnMobileFilters');
    const isMobile = window.innerWidth <= 768;
    
    if (btn) {
        btn.style.display = isMobile ? 'flex' : 'none';
    }
}

// Inicializar control del sidebar móvil
function initMobileFiltersSidebar() {
    const btnMobileFilters = document.getElementById('btnMobileFilters');
    const sidebar = document.querySelector('.modern-sidebar');

    
    if (btnMobileFilters && sidebar) {
        
        // Toggle sidebar al hacer click en el botón
        btnMobileFilters.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            
            if (sidebar.classList.contains('show-mobile')) {
                // Cerrar sidebar
                sidebar.classList.remove('show-mobile');
                document.body.style.overflow = '';
                
                // Mostrar bxa
                setTimeout(() => {
                    btnMobileFilters.classList.remove('hidden');
                }, 300);
                
            } else {
                // Abrir sidebar
                sidebar.classList.add('show-mobile');
                document.body.style.overflow = 'hidden';
                
                // Ocultar botón con animación
                btnMobileFilters.classList.add('hidden');
                
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
                
            }
        });
    }
}

// Actualizar contador de filtros activos
function updateFilterCount() {
    const filterCount = document.getElementById('filterCount');

    
    let count = 0;
    
    // Contar filtros activos
    const categoryFilter = document.getElementById('filter-category');
    const marcaFilter = document.getElementById('filter-marca');
    const statusFilter = document.getElementById('filter-status');
    const stockFilter = document.getElementById('filter-stock');
    const fechaFilter = document.getElementById('filter-fecha-value');
    const searchInput = document.getElementById('search-productos');
    
    if (categoryFilter && categoryFilter.value) count++;
    if (marcaFilter && marcaFilter.value) count++;
    if (statusFilter && statusFilter.value) count++;
    if (stockFilter && stockFilter.value) count++;
    if (fechaFilter && fechaFilter.value) count++;
    if (searchInput && searchInput.value.trim()) count++;
    
    // Actualizar badge
    filterCount.textContent = count;
    filterCount.style.display = count > 0 ? 'flex' : 'none';
    
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

// Filtrar productos desde el modal
function filterProductsFromModal() {
    syncFiltersFromModal();
    filterProducts();
}
window.filterProductsFromModal = filterProductsFromModal;

// Limpiar todos los filtros desde el modal
function clearModalFilters() {
    
    // Limpiar búsqueda
    const modalSearch = document.getElementById('modal-search-productos');
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
    const desktopSearch = document.getElementById('search-productos');
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
    
    // Recargar productos sin filtros
    clearAllProductFilters();
    
}
window.clearModalFilters = clearModalFilters;

// ============ END MOBILE FILTERS MODAL FUNCTIONS ============

// Función para obtener la URL correcta de la imagen del producto
function getProductImageUrl(producto, forceCacheBust = false) {
    // Priorizar url_imagen_producto, luego imagen_producto
    let imageUrl = '';
    
    if (producto.url_imagen_producto) {
        // Verificar que no sea una URL de placeholder
        if (producto.url_imagen_producto.includes('placeholder') || 
            producto.url_imagen_producto.includes('via.placeholder')) {
            imageUrl = (typeof AppConfig !== 'undefined') ? AppConfig.getImageUrl('default-product.jpg') : '/fashion-master/public/assets/img/default-product.jpg';
        } else {
            imageUrl = producto.url_imagen_producto;
        }
    } else if (producto.imagen_producto) {
        // Si es un nombre de archivo local, construir la ruta completa
        if (!producto.imagen_producto.startsWith('http')) {
            imageUrl = (typeof AppConfig !== 'undefined') ? AppConfig.getImageUrl('products/' + producto.imagen_producto) : '/fashion-master/public/assets/img/products/' + producto.imagen_producto;
        } else {
            imageUrl = producto.imagen_producto;
        }
    } else {
        imageUrl = (typeof AppConfig !== 'undefined') ? AppConfig.getImageUrl('default-product.jpg') : '/fashion-master/public/assets/img/default-product.jpg';
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
    const tbody = document.getElementById('productos-table-body');
    if (tbody) {
        tbody.innerHTML = `
            <tr class="loading-row">
                <td colspan="11" class="loading-cell">
                    <div class="loading-content">
                        <div class="spinner"></div>
                        <span>Buscando productos...</span>
                    </div>
                </td>
            </tr>
        `;
    }
}

// Función principal para cargar productos con efectos visuales (DEFINICIÓN TEMPRANA)
async function loadProducts(forceCacheBust = false, preserveState = null) {

    // También crear un alias para compatibilidad
    window.loadProductos = loadProducts;
    window.loadProducts = loadProducts; // Asegurar que esté disponible globalmente
    
    isLoading = true;
    
    try {
        // Mostrar loading mejorado
        showSearchLoading();
        
        // Usar estado preservado si está disponible
        if (preserveState) {
            currentPage = preserveState.page || currentPage;
            
            // Restaurar filtros si están disponibles
            if (preserveState.searchTerm && typeof $ !== 'undefined') {
                $('#search-productos').val(preserveState.searchTerm);
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
            const search = $('#search-productos').val();
            if (search) params.append('search', search);
            
            const category = $('#filter-category').val();
            if (category) params.append('category', category);
            
            const marca = $('#filter-marca').val();
            if (marca) params.append('marca', marca);
            
            const status = $('#filter-status').val();
            if (status !== '') params.append('status', status);
            
            const stock = $('#filter-stock').val();
            if (stock) params.append('stock_filter', stock);
            
            // Usar el hidden input para la fecha
            const fecha = $('#filter-fecha-value').val();
            if (fecha) params.append('fecha', fecha);
        } else {
            // Fallback vanilla JS
            const searchInput = document.getElementById('search-productos');
            if (searchInput && searchInput.value) {
                params.append('search', searchInput.value);
            }
            
            const categorySelect = document.getElementById('filter-category');
            if (categorySelect && categorySelect.value) {
                params.append('category', categorySelect.value);
            }
            
            const marcaSelect = document.getElementById('filter-marca');
            if (marcaSelect && marcaSelect.value) {
                params.append('marca', marcaSelect.value);
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
        
        // Agregar parámetros de ordenamiento si existen
        if (currentSortColumn) {
            params.append('sort_by', currentSortColumn);
            params.append('sort_order', currentSortOrder);
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
            
            throw new Error('Respuesta del servidor no es JSON válido');
        }
        
        if (!data.success) {
            throw new Error(data.error || 'Error desconocido del servidor');
        }
        
        productos = data.data || [];
        
        displayProductos(productos, forceCacheBust, preserveState);
        updateStats(data.pagination);
        updatePaginationInfo(data.pagination);
        
        // Cargar fechas únicas en el filtro
        loadProductDates(productos);
        
        // Actualizar contador de resultados
        if (data.pagination) {
            updateResultsCounter(productos.length, data.pagination.total_items);
        }
        
        // Destacar producto recién actualizado/creado si está especificado
        // PRESERVAR ESTADO - sin destacado visual para evitar bugs
        if (preserveState) {
            // Restaurar posición de scroll sin animaciones que causen problemas
            if (preserveState.scrollPosition && typeof restoreScrollPosition === 'function') {
                restoreScrollPosition(preserveState.scrollPosition);
            }
        }
        
    } catch (error) {
        const tbody = document.getElementById('productos-table-body');
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

// Asegurar que las funciones estén disponibles globalmente inmediatamente
window.loadProducts = loadProducts;
window.loadProductos = loadProducts;

// 🎯 Función para cargar productos con SMOOTH UPDATE (sin recargar tabla)
async function loadProductsSmooth() {
    if (!window.productosTableUpdater) {
        return loadProducts();
    }
    
    try {
        // Construir URL con parámetros
        const params = new URLSearchParams({
            action: 'list',
            page: currentPage,
            limit: 10
        });
        
        // Agregar filtros si existen
        const search = document.getElementById('search-productos')?.value || '';
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
                
        const response = await fetch(finalUrl);
        const data = await response.json();
        
        if (data.success) {
            // Actualizar timestamp
            resetUpdateTimestamp();
            
            // Verificar si hay productos
            if (data.data && data.data.length > 0) {
                // 🎨 SMOOTH UPDATE: Actualizar productos uno por uno sin recargar la tabla
                await window.productosTableUpdater.updateMultipleProducts(data.data);
                
                // Actualizar estadísticas y paginación
                updateStats(data.pagination);
                updatePaginationInfo(data.pagination);
                
                // Actualizar fechas del calendario SIN redibujar (invisible)
                if (typeof loadProductDates === 'function') {
                    loadProductDates(data.data);
                }
                
            } else {
                // No hay productos, mostrar mensaje
                const tbody = document.getElementById('productos-table-body');
                if (tbody) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="11" class="loading-cell">
                                <div class="loading-content no-data">
                                    <i class="fas fa-search" style="font-size: 48px; color: #cbd5e0; margin-bottom: 16px;"></i>
                                    <span style="font-size: 16px; color: #4a5568;">No se encontraron productos</span>
                                    <small style="color: #a0aec0; margin-top: 8px;">Intenta ajustar los filtros de búsqueda</small>
                                </div>
                            </td>
                        </tr>
                    `;
                }
                
                // Actualizar estadísticas y paginación con valores vacíos
                updateStats({ total: 0 });
                updatePaginationInfo({ total: 0, page: 1, totalPages: 0 });
                
            }
        } else {
            throw new Error(data.message || 'Error al cargar productos');
        }
    } catch (error) {
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
 * - N° siempre muestra 1, 2, 3... (posición visual, NO el ID real del producto)
 * - Primer click: Mantiene orden actual (ASC)
 * - Segundo click: Invierte orden completo (DESC)
 * - Tercer click: Vuelve al orden original (ASC)
 * 
 * Ejemplo con productos ID 1, 3, 6, 7 (después de soft delete del ID 6):
 * ASC:  N°1 (ID:1), N°2 (ID:3), N°3 (ID:7)
 * DESC: N°1 (ID:7), N°2 (ID:3), N°3 (ID:1)  ← Orden invertido
 */
function sortTableLocally(column, type) {
    
    // Obtener todas las filas de la tabla
    const tbody = document.getElementById('productos-table-body');
    if (!tbody) return;
    
    const rows = Array.from(tbody.querySelectorAll('tr:not(.loading-row):not(.empty-row)'));
    
    if (rows.length === 0) {
        return;
    }
    
    // Mapeo de columnas a índices
    const columnIndexMap = {
        'numero': 0,      // N°
        'nombre': 2,      // Producto
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
                // Para texto (producto, código, categoría, marca, estado)
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
            throw new Error('Respuesta del servidor no es JSON válido');
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
            throw new Error('Respuesta del servidor no es JSON válido');
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
    }
}

window.loadMarcas = loadMarcas;

// Función para cargar fechas únicas de productos en el filtro
function loadProductDates(products) {
    try {
        const fechaSelect = document.getElementById('filter-fecha');
        if (!fechaSelect || !products || products.length === 0) return;
        
        // Extraer fechas únicas (formato YYYY-MM-DD)
        const fechasSet = new Set();
        products.forEach(producto => {
            if (producto.fecha_creacion_producto) {
                // Extraer solo la parte de la fecha (YYYY-MM-DD)
                const fecha = producto.fecha_creacion_producto.split(' ')[0];
                fechasSet.add(fecha);
            }
        });
        
        // Convertir a array y ordenar de más reciente a más antigua
        const fechasUnicas = Array.from(fechasSet).sort((a, b) => b.localeCompare(a));
        
        // Guardar fechas en variable global para Flatpickr
        window.productsDatesArray = fechasUnicas;
     
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
    }
}

// Función para mostrar productos en tabla
function displayProductos(products, forceCacheBust = false, preserveState = null) {
    
    
    
    // FORZAR vista grid en móvil SIEMPRE
    const isMobile = window.innerWidth <= 768;
    
    if (isMobile) {
        
        displayProductosGrid(products);
        return;
    }
    
    // En desktop, verificar vista actual
    const currentView = window.productos_currentView || 'table';
    
    
    if (currentView === 'grid') {
        
        displayProductosGrid(products);
        return;
    }
    
    // Vista tabla
    
    const tbody = document.getElementById('productos-table-body');
    
    if (!products || products.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="11" class="loading-cell">
                    <div class="loading-content no-data">
                        <i class="fas fa-box-open"></i>
                        <span>No se encontraron productos</span>
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = products.map((producto, index) => {
        return `
        <tr oncontextmenu="return false;" ondblclick="editProduct(${producto.id_producto})" style="cursor: pointer;" data-product-id="${producto.id_producto}">
            <td><strong>${index + 1}</strong></td>
            <td onclick="event.stopPropagation();" ondblclick="event.stopPropagation(); showImageFullSize('${getProductImageUrl(producto, forceCacheBust)}', '${(producto.nombre_producto || '').replace(/'/g, "\\'")}')"; style="cursor: zoom-in;">
                <div class="product-image-small">
                    <img src="${getProductImageUrl(producto, forceCacheBust)}" 
                         alt="Producto" 
                         onerror="this.src='${AppConfig ? AppConfig.getImageUrl('default-product.jpg') : '/fashion-master/public/assets/img/default-product.jpg'}'; this.onerror=null;">
                </div>
            </td>
            <td>
                <div class="product-info">
                    <strong>${producto.nombre_producto}</strong>
                </div>
            </td>
            <td>
                ${producto.nombre_categoria || producto.categoria_nombre || 'Sin categoría'}
            </td>
            <td>
                ${producto.nombre_marca || producto.marca_nombre || 'Sin marca'}
            </td>
            <td>
                <span class="genero-badge ${getGeneroBadgeClass(producto.genero_producto)}">
                    ${getGeneroLabel(producto.genero_producto)}
                </span>
            </td>
            <td>
                <div class="price-info">
                    <strong>${producto.precio_formato || '$' + producto.precio_producto}</strong>
                </div>
            </td>
            <td>
                <div class="stock-info">
                    <span class="stock-number ${getStockClass(producto)}">${producto.stock_actual_producto}</span>
                    <small class="stock-status">${producto.estado_stock}</small>
                </div>
            </td>
            <td>
                <span class="status-badge ${producto.estado === 'activo' ? 'status-active' : 'status-inactive'}">
                    ${producto.estado === 'activo' ? 'Activo' : 'Inactivo'}
                </span>
            </td>
            <td>${producto.fecha_creacion_producto ? producto.fecha_creacion_producto.split(' ')[0] : '-'}</td>
            <td>
                <div class="action-buttons">
                    <button class="btn-action btn-menu" onclick="event.stopPropagation(); showActionMenu(${producto.id_producto}, '${(producto.nombre_producto || '').replace(/'/g, "\\'")}', ${producto.stock_actual_producto}, '${producto.estado}', event)" title="Acciones">
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

// Función para obtener clase de stock
// NOTA: Función getStockClass eliminada - usar calcularEstadoStock() centralizada en smooth-table-update.js
function getStockClass(producto) {
    // ✅ Calcular directamente si calcularEstadoStock no está disponible
    if (typeof calcularEstadoStock === 'function') {
        const resultado = calcularEstadoStock(producto);
        return resultado.clase;
    }
    
    // Fallback: calcular inline
    const stockActual = parseInt(producto.stock_actual_producto) || 0;
    const stockMinimo = producto.stock_minimo_producto ? parseInt(producto.stock_minimo_producto) : null;
    
    // Prioridad 1: Stock en 0 = Agotado (ROJO)
    if (stockActual === 0) {
        return 'stock-agotado';
    }
    
    // Prioridad 2: Stock <= stock_minimo = Stock Bajo (NARANJA)
    if (stockMinimo !== null && stockMinimo > 0 && stockActual <= stockMinimo) {
        return 'stock-bajo';
    }
    
    // Prioridad 3: Stock > stock_minimo = Normal (VERDE)
    return 'stock-normal';
}

// Funciones para género
function getGeneroLabel(genero) {
    const labels = {
        'M': 'Masculino',
        'F': 'Femenino',
        'Unisex': 'Unisex',
        'Kids': 'Niños'
    };
    return labels[genero] || genero || 'N/A';
}

function getGeneroBadgeClass(genero) {
    const classes = {
        'M': 'genero-masculino',
        'F': 'genero-femenino',
        'Unisex': 'genero-unisex',
        'Kids': 'genero-kids'
    };
    return classes[genero] || 'genero-default';
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
function filterProducts() {
    if (typeof $ === 'undefined') {
        return filterProductsVanilla();
    }
    
    const search = $('#search-productos').val() || '';
    const category = $('#filter-category').val() || '';
    const marca = $('#filter-marca').val() || '';
    const status = $('#filter-status').val() || '';
    const stock = $('#filter-stock').val() || '';
    
    // Actualizar contador de filtros activos
    if (typeof updateFilterCount === 'function') {
        updateFilterCount();
    }
    
    // Mostrar indicador de carga
    showSearchLoading();
    
    // Reset página actual
    currentPage = 1;
    
    // 🎯 SMOOTH UPDATE: Recargar productos con transición suave
    if (typeof loadProductsSmooth === 'function' && window.productosTableUpdater) {
        loadProductsSmooth();
    } else {
        loadProducts();
    }
}

// Función de filtrado con vanilla JS como fallback
function filterProductsVanilla() {
    const searchInput = document.getElementById('search-productos');
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
    
    // 🎯 SMOOTH UPDATE: Recargar productos con transición suave
    if (typeof loadProductsSmooth === 'function' && window.productosTableUpdater) {
        loadProductsSmooth();
    } else {
        loadProducts();
    }
}

// Función para manejar búsqueda en tiempo real con jQuery
let searchTimeout;
function handleSearchInput() {
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
            filterProducts();
        }, 300); // Reducido para mejor responsividad
    } else {
        // Fallback vanilla JS
        const searchInput = document.getElementById('search-productos');
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
            filterProducts();
        }, 300);
    }
}

// Función para cambiar vista (tabla/grid)
function toggleProductoView(viewType, skipAnimation = false) {
    // PC: Solo tabla, Móvil: Solo grid (sin cambios permitidos)
    const isMobile = window.innerWidth <= 768;
    
    // Bloquear cambios de vista (PC siempre tabla, móvil siempre grid)
    if (isMobile && viewType === 'table') return; // Móvil no puede ir a tabla
    if (!isMobile && viewType === 'grid') return; // PC no puede ir a grid
    
    // Obtener contenedores
    const tableContainer = document.querySelector('.data-table-wrapper');
    const gridContainer = document.querySelector('.products-grid');
    
    if (!tableContainer) return;
    
    // Cerrar flotantes
    if (typeof closeStockBubble === 'function') closeStockBubble();
    if (window.productos_activeFloatingContainer) closeFloatingActionsAnimated();
    
    // Crear grid si no existe (solo para móvil)
    if (!gridContainer && isMobile) {
        createGridView();
    }
    
    const grid = document.querySelector('.products-grid');
    
    // VISTA SEGÚN DISPOSITIVO
    if (isMobile) {
        // MÓVIL: Solo grid
        if (grid) {
            tableContainer.style.display = 'none';
            grid.style.display = 'grid';
            grid.style.opacity = '1';
            window.productos_currentView = 'grid';
            
            // Cargar solo si vacío
            if (!grid.querySelector('.product-card')) {
                loadProducts();
            }
        }
    } else {
        // PC: Solo tabla
        if (grid) grid.style.display = 'none';
        tableContainer.style.display = 'block';
        tableContainer.style.opacity = '1';
        window.productos_currentView = 'table';
        
        // Cargar solo si vacía
        const tbody = tableContainer.querySelector('tbody');
        if (!tbody || !tbody.querySelector('tr[data-product-id]')) {
            loadProducts();
        }
    }
}

// Exponer globalmente
window.toggleProductoView = toggleProductoView;

// Función para crear vista grid (SIMPLIFICADA)
function createGridView() {
    const gridContainer = document.createElement('div');
    gridContainer.className = 'products-grid active'; // ← AGREGAR .active
    gridContainer.style.setProperty('display', 'grid', 'important');
    gridContainer.style.setProperty('visibility', 'visible', 'important');
    
    const tableWrapper = document.querySelector('.data-table-wrapper');
    tableWrapper.parentNode.insertBefore(gridContainer, tableWrapper.nextSibling);
}

// Función para mostrar productos en grid (SIMPLIFICADA)
function displayProductosGrid(products) {
    let gridContainer = document.querySelector('.products-grid');
    
    if (!gridContainer) {
        createGridView();
        gridContainer = document.querySelector('.products-grid');
    }
    
    if (!gridContainer) return;
    
    // TRIPLE FUERZA: clase + inline styles + !important
    gridContainer.classList.add('active');
    gridContainer.style.setProperty('display', 'grid', 'important');
    gridContainer.style.setProperty('visibility', 'visible', 'important');
    gridContainer.style.setProperty('opacity', '1', 'important');
    
    if (!products || products.length === 0) {
        gridContainer.innerHTML = `
            <div class="no-products-message">
                <i class="fas fa-box-open"></i>
                <p>No se encontraron productos</p>
            </div>
        `;
        gridContainer.classList.add('active'); // Re-forzar
        return;
    }
    
    gridContainer.innerHTML = products.map(producto => {
        const stock = parseInt(producto.stock_actual_producto) || 0;
        
        let estadoStock;
        if (typeof calcularEstadoStock === 'function') {
            estadoStock = calcularEstadoStock(producto);
        } else {
            const stockMinimo = producto.stock_minimo_producto ? parseInt(producto.stock_minimo_producto) : null;
            if (stock === 0) {
                estadoStock = { clase: 'stock-agotado', texto: 'Agotado' };
            } else if (stockMinimo !== null && stockMinimo > 0 && stock <= stockMinimo) {
                estadoStock = { clase: 'stock-bajo', texto: 'Bajo' };
            } else {
                estadoStock = { clase: 'stock-normal', texto: 'Normal' };
            }
        }
        
        const imageUrl = getProductImageUrl(producto);
        const hasImage = imageUrl && !imageUrl.includes('default-product.jpg');
        
        const imageHTML = `
            <div class="product-card-image-mobile ${hasImage ? '' : 'no-image'}">
                ${hasImage 
                    ? `<img src="${imageUrl}" alt="${producto.nombre_producto || 'Producto'}" onerror="this.parentElement.classList.add('no-image'); this.style.display='none'; this.parentElement.innerHTML='<i class=\\'fas fa-image\\'></i>';">` 
                    : '<i class="fas fa-image"></i>'}
            </div>
        `;
        
        return `
            <div class="product-card" ondblclick="editProduct(${producto.id_producto})" style="cursor: pointer;" data-product-id="${producto.id_producto}">
                ${imageHTML}
                <div class="product-card-header">
                    <h3 class="product-card-title">${producto.nombre_producto || 'Sin nombre'}</h3>
                    <span class="product-card-status ${producto.estado === 'activo' ? 'active' : 'inactive'}">
                        ${producto.estado === 'activo' ? 'Activo' : 'Inactivo'}
                    </span>
                </div>
                
                <div class="product-card-body">
                    ${producto.codigo ? `<div class="product-card-sku">Código: ${producto.codigo}</div>` : ''}
                    <div class="product-card-category">
                        <i class="fas fa-tag"></i> ${producto.nombre_categoria || producto.categoria_nombre || 'Sin categoría'}
                    </div>
                    
                    <div class="product-card-genero">
                        <span class="genero-badge ${getGeneroBadgeClass(producto.genero_producto)}">
                            ${getGeneroLabel(producto.genero_producto)}
                        </span>
                    </div>
                    
                    <div class="product-card-stock">
                        <span class="${estadoStock.clase}">
                            <i class="fas fa-box"></i> ${stock} unidades (${estadoStock.texto})
                        </span>
                    </div>
                    
                    <div class="product-card-price">
                        <i class="fas fa-dollar-sign"></i>
                        $${parseFloat(producto.precio_producto || 0).toLocaleString('es-CO')}
                        ${producto.precio_descuento_producto ? `<span class="discount-price">$${parseFloat(producto.precio_descuento_producto).toLocaleString('es-CO')}</span>` : ''}
                    </div>
                </div>
                
                <div class="product-card-actions">
                    <button class="product-card-btn btn-view" onclick="event.stopPropagation(); window.location.href='product-details.php?id=${producto.id_producto}'" title="Ver producto" style="background-color: #1a73e8 !important; color: white !important; border: none !important; box-shadow: 0 4px 8px rgba(26, 115, 232, 0.3) !important;">
                        <i class="fas fa-eye" style="color: white !important;"></i>
                    </button>
                    <button class="product-card-btn btn-edit" onclick="event.stopPropagation(); editProduct(${producto.id_producto})" title="Editar producto" style="background-color: #34a853 !important; color: white !important; border: none !important; box-shadow: 0 4px 8px rgba(52, 168, 83, 0.3) !important;">
                        <i class="fas fa-edit" style="color: white !important;"></i>
                    </button>
                    <button class="product-card-btn ${producto.estado === 'activo' ? 'btn-deactivate' : 'btn-activate'}" 
                            onclick="event.stopPropagation(); changeProductEstado(${producto.id_producto})" 
                            title="${producto.estado === 'activo' ? 'Desactivar' : 'Activar'} producto"
                            style="background-color: #6f42c1 !important; color: white !important; border: none !important;">
                        <i class="fas fa-${producto.estado === 'activo' ? 'power-off' : 'toggle-on'}" style="color: white !important;"></i>
                    </button>
                    <button class="product-card-btn btn-stock" onclick="event.stopPropagation(); updateStock(${producto.id_producto}, ${producto.stock_actual_producto}, event)" title="Actualizar stock" style="background-color: #fd7e14 !important; color: white !important; border: none !important;">
                        <i class="fas fa-boxes" style="color: white !important;"></i>
                    </button>
                    <button class="product-card-btn btn-delete" onclick="event.stopPropagation(); deleteProduct(${producto.id_producto}, '${(producto.nombre_producto || 'Producto').replace(/'/g, "\\'")}')\" title="Eliminar producto" style="background-color: #f44336 !important; color: white !important; border: none !important; box-shadow: 0 4px 8px rgba(244, 67, 54, 0.3) !important;">
                        <i class="fas fa-trash" style="color: white !important;"></i>
                    </button>
                </div>
            </div>
        `;
    }).join('');
    
    // TRIPLE FUERZA después de renderizar
    gridContainer.classList.add('active');
    gridContainer.style.setProperty('display', 'grid', 'important');
    gridContainer.style.setProperty('visibility', 'visible', 'important');
    gridContainer.style.setProperty('opacity', '1', 'important');


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

// ============ FUNCIONES PRINCIPALES PRODUCTOS ============

// ===================================
// SISTEMA DE BOTONES FLOTANTES ANIMADOS - VERSIÓN AVANZADA
// ===================================

// Variables globales para el sistema flotante
let productos_activeFloatingContainer = null;
// SISTEMA DE BOTONES FLOTANTES ANIMADOS - SIMPLIFICADO Y MODULAR
const FloatingMenu = (() => {
    let activeContainer = null;
    let activeProductId = null;
    let isAnimating = false;
    let isClosing = false;
    let animationTimeout = null;
    let floatingButtons = [];
    let centerButton = null;
    let lastClickTime = 0;
    const clickDebounceDelay = 300;

    function cleanupOrphanedContainers() {
        document.querySelectorAll('.animated-floating-container').forEach(c => c.remove());
        document.querySelectorAll('.animated-floating-button, .animated-center-button').forEach(b => b.remove());
    }

    function show(productId, productName, stock, estado, event) {
        if (isClosing) return;
        const now = Date.now();
        if (now - lastClickTime < clickDebounceDelay) return;
        lastClickTime = now;
        document.querySelectorAll('.stock-update-bubble').forEach(b => b.remove());
        document.querySelectorAll('.stock-bubble-overlay').forEach(o => o.remove());
        if (activeContainer && activeProductId === productId) { close(); return; }
        if (activeContainer && activeProductId !== productId) close();
        open(productId, productName, stock, estado, event);
    }

    function open(productId, productName, stock, estado, event) {
        cleanupOrphanedContainers();
        let triggerButton = event && event.currentTarget ? event.currentTarget : null;
        if (!triggerButton) return;
        if (!document.contains(triggerButton)) return;
        isAnimating = true;
        activeProductId = productId;
        createContainer(triggerButton, productId, productName, stock, estado);
    }

    function createContainer(triggerButton, productId, productName, stock, estado) {
        if (activeContainer) activeContainer.remove();
        activeContainer = document.createElement('div');
        activeContainer.className = 'animated-floating-container';
        activeContainer.triggerButton = triggerButton;
        // Botón central
        centerButton = document.createElement('div');
        centerButton.className = 'animated-center-button';
        centerButton.innerHTML = '<i class="fas fa-ellipsis-v"></i>';
        centerButton.onclick = close;
        activeContainer.appendChild(centerButton);
        // Acciones
        const actions = [
            { icon: 'fa-eye', fn: () => window.location.href = 'product-details.php?id=' + productId },
            { icon: 'fa-edit', fn: () => editProduct(productId) },
            { icon: 'fa-boxes', fn: () => updateStock(productId, stock, event) },
            { icon: estado === 'activo' ? 'fa-power-off' : 'fa-toggle-on', fn: () => changeProductEstado(productId) },
            { icon: 'fa-trash', fn: () => deleteProduct(productId, productName) }
        ];
        floatingButtons = [];
        actions.forEach((action, i) => {
            const btn = document.createElement('div');
            btn.className = 'animated-floating-button';
            btn.innerHTML = `<i class="fas ${action.icon}"></i>`;
            btn.onclick = () => { close(); setTimeout(action.fn, 200); };
            activeContainer.appendChild(btn);
            floatingButtons.push(btn);
        });
        document.body.appendChild(activeContainer);
        updatePositions();
        setupListeners();
        isAnimating = false;
    }

    function updatePositions() {
        if (!activeContainer || !activeContainer.triggerButton) return;
        const rect = activeContainer.triggerButton.getBoundingClientRect();
        const cx = rect.left + rect.width / 2;
        const cy = rect.top + rect.height / 2;
        if (centerButton) {
            centerButton.style.left = `${cx - 22.5}px`;
            centerButton.style.top = `${cy - 22.5}px`;
        }
        const radius = 80;
        floatingButtons.forEach((btn, i) => {
            const angle = (i / floatingButtons.length) * 2 * Math.PI - Math.PI / 2;
            const x = cx + Math.cos(angle) * radius;
            const y = cy + Math.sin(angle) * radius;
            btn.style.left = `${x - 27.5}px`;
            btn.style.top = `${y - 27.5}px`;
        });
    }

    function setupListeners() {
        setTimeout(() => {
            document.addEventListener('click', handleClickOutside);
            window.addEventListener('resize', updatePositions, { passive: true });
            window.addEventListener('scroll', updatePositions, { passive: true });
        }, 100);
        activeContainer.cleanup = cleanupListeners;
    }
    function cleanupListeners() {
        document.removeEventListener('click', handleClickOutside);
        window.removeEventListener('resize', updatePositions);
        window.removeEventListener('scroll', updatePositions);
    }
    function handleClickOutside(e) {
        if (activeContainer && !activeContainer.contains(e.target)) close();
    }
    function close() {
        if (!activeContainer) return;
        isClosing = true;
        if (activeContainer.cleanup) activeContainer.cleanup();
        activeContainer.remove();
        activeContainer = null;
        centerButton = null;
        floatingButtons = [];
        activeProductId = null;
        isAnimating = false;
        isClosing = false;
    }
    return { show, close, cleanupOrphanedContainers };
})();

// Exponer API simplificada globalmente
window.showActionMenu = FloatingMenu.show;
window.closeFloatingActions = FloatingMenu.close;
window.cleanupOrphanedContainers = FloatingMenu.cleanupOrphanedContainers;
function updateAnimatedButtonPositions() {
    if (!productos_activeFloatingContainer) {
        return;
    }
    
    if (!productos_activeFloatingContainer.triggerButton) {
        return;
    }
    
    // Verificar que el trigger button aún existe en el DOM
    if (!document.contains(productos_activeFloatingContainer.triggerButton)) {
        closeFloatingActionsAnimated();
        return;
    }
    
    // Usar getBoundingClientRect para obtener posición fija en la ventana
    const triggerRect = productos_activeFloatingContainer.triggerButton.getBoundingClientRect();
    
    // Calcular centro del botón trigger en coordenadas de ventana (fixed)
    const finalCenterX = triggerRect.left + triggerRect.width / 2;
    const finalCenterY = triggerRect.top + triggerRect.height / 2;
    
    // Actualizar posición del botón central
    if (productos_centerButton) {
        productos_centerButton.style.left = `${finalCenterX - 22.5}px`;
        productos_centerButton.style.top = `${finalCenterY - 22.5}px`;
    }
    
    // Actualizar posición de cada botón flotante
    productos_floatingButtons.forEach((button, index) => {
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
    if (productos_centerButton) {
        setTimeout(() => {
            productos_centerButton.style.transform = 'scale(1) rotate(360deg)';
            productos_centerButton.style.opacity = '1';
        }, 100);
    }
    
    // Animar botones flotantes con delay escalonado
    productos_floatingButtons.forEach((button, index) => {
        setTimeout(() => {
            button.style.transform = 'scale(1) rotate(0deg)';
            button.style.opacity = '1';
        }, 200 + (index * 100));
    });
    
    // Finalizar animación de apertura - bloquear cierre hasta que termine la entrada
    setTimeout(() => {
        productos_isAnimating = false;
    }, 200 + (productos_floatingButtons.length * 100) + 200); // Bloquear hasta que termine la animación
}

// Event listeners animados
function setupAnimatedEventListeners() {
    // Cerrar al hacer click fuera con animación
    const handleClick = (e) => {
        if (productos_activeFloatingContainer && !productos_activeFloatingContainer.contains(e.target)) {
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
        if (!productos_activeFloatingContainer) return;
        
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            if (productos_activeFloatingContainer && !productos_isAnimating) {
                updateAnimatedButtonPositions();
            }
        }, 150);
    };
    
    // Manejar scroll - actualizar posiciones en tiempo real
    let scrollTimeout;
    const handleScroll = () => {
        if (!productos_activeFloatingContainer) return;
        
        // Actualizar posiciones inmediatamente para tracking fluido
        if (!productos_isAnimating && !productos_isClosing) {
            updateAnimatedButtonPositions();
        }
        
        // También verificar si el trigger sigue visible (con throttle)
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(() => {
            if (productos_activeFloatingContainer && productos_activeFloatingContainer.triggerButton) {
                const rect = productos_activeFloatingContainer.triggerButton.getBoundingClientRect();
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
        document.querySelector('.data-table-wrapper'),  // Tabla de productos
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
    productos_activeFloatingContainer.cleanup = () => {
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
    if (!productos_activeFloatingContainer) {
        productos_isAnimating = false;
        stopContinuousTracking();
        return;
    }
    
    // Si ya está cerrando, no hacer nada
    if (productos_isClosing) {
        return;
    }
    
    // Si está animando la apertura, no permitir cerrar
    if (productos_isAnimating) {
        return;
    }
    
    productos_isAnimating = true;
    productos_isClosing = true;
    
    // Detener tracking continuo
    stopContinuousTracking();
    
    // Limpiar timeouts
    if (productos_animationTimeout) {
        clearTimeout(productos_animationTimeout);
        productos_animationTimeout = null;
    }
    
    hideTooltip();
    
    const containerToClose = productos_activeFloatingContainer;
    const buttonsToClose = [...productos_floatingButtons];
    const centerButtonToClose = productos_centerButton;
    
    productos_cancelableTimeouts.forEach(timeout => clearTimeout(timeout));
    productos_cancelableTimeouts = [];
    
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
                    if (!productos_isClosing) return;
                    
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
                }
            }, index * 30); // 30ms de delay entre cada botón
            
            productos_cancelableTimeouts.push(timeout);
        }
    });
    
    // Botón central hace un "pulso" y desaparece
    if (centerButtonToClose && document.contains(centerButtonToClose)) {
        const timeout = setTimeout(() => {
            try {
                if (!productos_isClosing) return;
                
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
            }
        }, buttonsToClose.length * 30 + 50);
        
        productos_cancelableTimeouts.push(timeout);
    }
    
    // Cleanup optimizado
    const cleanupDelay = buttonsToClose.length * 30 + 350;
    productos_animationTimeout = setTimeout(() => {
        if (!productos_isClosing) return;
        
        try {
            if (containerToClose && document.contains(containerToClose)) {
                if (containerToClose.cleanup) {
                    containerToClose.cleanup();
                }
                containerToClose.remove();
            }
        } catch (e) {
        }
        
        productos_activeFloatingContainer = null;
        productos_centerButton = null;
        productos_floatingButtons = [];
        productos_activeProductId = null;
        productos_isAnimating = false;
        productos_isClosing = false;
        productos_cancelableTimeouts = [];
        
        cleanupOrphanedContainers();
    }, cleanupDelay);
    
    productos_cancelableTimeouts.push(productos_animationTimeout);
}

// Cerrar menú flotante con animación (usa la versión rápida para todo)
function closeFloatingActionsAnimated() {
    // Usar la animación rápida pero fluida para todos los casos
    closeFloatingActionsAnimatedFast();
}

// Función para cancelar cierre suave y restaurar botones
function cancelSoftClose() {    
    // Cancelar todos los timeouts pendientes
    productos_cancelableTimeouts.forEach(timeout => {
        if (timeout) clearTimeout(timeout);
    });
    productos_cancelableTimeouts = [];
    
    if (productos_animationTimeout) {
        clearTimeout(productos_animationTimeout);
        productos_animationTimeout = null;
    }
    
    // Marcar que ya no está cerrando
    productos_isClosing = false;
    
    // Si hay botones que están en medio de animación de cierre, restaurarlos suavemente
    if (productos_floatingButtons.length > 0) {
        productos_floatingButtons.forEach((button, index) => {
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
                }
            }
        });
    }
    
    // Restaurar botón central
    if (productos_centerButton && document.contains(productos_centerButton)) {
        try {
            productos_centerButton.style.transition = 'all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
            setTimeout(() => {
                productos_centerButton.style.transform = 'scale(1) rotate(360deg)';
                productos_centerButton.style.opacity = '1';
            }, productos_floatingButtons.length * 30);
        } catch (e) {
        }
    }
    
    // Resetear flag de animación después de restaurar
    setTimeout(() => {
        productos_isAnimating = false;
    }, productos_floatingButtons.length * 30 + 300);
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
        if (productos_animationTimeout) {
            clearTimeout(productos_animationTimeout);
            productos_animationTimeout = null;
        }
        
        // Ocultar tooltip inmediatamente
        hideTooltip();
        
        // Si hay un contenedor activo, eliminarlo inmediatamente
        if (productos_activeFloatingContainer) {
            try {
                // Limpiar eventos si existen
                if (productos_activeFloatingContainer.cleanup) {
                    productos_activeFloatingContainer.cleanup();
                }
                
                // Remover del DOM inmediatamente
                productos_activeFloatingContainer.remove();
            } catch (e) {
            }
            
            // Resetear variables globales
            productos_activeFloatingContainer = null;
            productos_centerButton = null;
            productos_floatingButtons = [];
            productos_activeProductId = null;
            productos_isAnimating = false;
        }
        
        // Asegurarse de que no queden elementos flotantes huérfanos
        const orphanedContainers = document.querySelectorAll('.animated-floating-container');
        orphanedContainers.forEach(container => {
            try {
                container.remove();
            } catch (e) {
            }
        });
    }, 320); // Retraso de 150ms antes del cierre forzado
}

// ============ SISTEMA DE MODALES ============



// Función para exportar productos
async function exportProducts() {
    try {
        showNotification('Preparando exportación...', 'info');
        
        if (!productos || productos.length === 0) {
            showNotification('No hay productos para exportar', 'warning');
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

        // Datos de productos
        productos.forEach(producto => {
            const genero = producto.genero_producto || producto.genero || 'Unisex';
            const generoLabel = genero === 'M' ? 'Masculino' : 
                              genero === 'F' ? 'Femenino' : 
                              genero === 'Kids' ? 'Kids' : 'Unisex';
            
            excelData.push([
                producto.id_producto || '',
                producto.nombre_producto || '',
                producto.categoria_nombre || producto.nombre_categoria || '',
                producto.marca_producto || '',
                generoLabel,
                producto.precio_producto != null ? parseFloat(producto.precio_producto) : 0,
                producto.stock_actual_producto != null ? parseInt(producto.stock_actual_producto) : 0,
                producto.stock_minimo_producto != null ? parseInt(producto.stock_minimo_producto) : 0,
                (producto.activo == 1 || producto.status_producto == 1) ? 'Activo' : 'Inactivo',
                producto.fecha_creacion_producto || ''
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
        XLSX.utils.book_append_sheet(wb, ws, "Productos");

        // Generar archivo
        const fileName = `Productos_${new Date().toISOString().split('T')[0]}.xlsx`;
        XLSX.writeFile(wb, fileName);

        showNotification(`Excel exportado: ${productos.length} productos`, 'success');
        
    } catch (error) {
        showNotification('Error al exportar productos', 'error');
    }
}

// Función para mostrar reporte de stock
function showStockReport() {
    try {
        if (!productos || productos.length === 0) {
            showNotification('No hay productos para generar reporte', 'warning');
            return;
        }

        // Verificar que XLSX esté disponible
        if (typeof XLSX === 'undefined') {
            showNotification('Librería de Excel no disponible', 'error');
            return;
        }

        showNotification('Generando reporte de stock...', 'info');

        // Clasificar productos por estado de stock
        const stockCritico = [];  // Stock = 0
        const stockBajo = [];     // Stock <= stock_minimo
        const stockNormal = [];   // Stock > stock_minimo

        productos.forEach(producto => {
            const stockActual = parseInt(producto.stock_actual_producto) || 0;
            const stockMinimo = parseInt(producto.stock_minimo_producto) || 5;
            const genero = producto.genero_producto || producto.genero || 'Unisex';
            const generoLabel = genero === 'M' ? 'Masculino' : 
                              genero === 'F' ? 'Femenino' : 
                              genero === 'Kids' ? 'Kids' : 'Unisex';

            const item = {
                id: producto.id_producto || '',
                nombre: producto.nombre_producto || '',
                categoria: producto.categoria_nombre || producto.nombre_categoria || '',
                marca: producto.marca_producto || '',
                genero: generoLabel,
                stockActual: stockActual,
                stockMinimo: stockMinimo,
                diferencia: stockActual - stockMinimo,
                precio: parseFloat(producto.precio_producto) || 0,
                valorInventario: stockActual * (parseFloat(producto.precio_producto) || 0)
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
        resumenData.push(['Total de Productos:', productos.length]);
        resumenData.push(['Productos sin Stock (Crítico):', stockCritico.length]);
        resumenData.push(['Productos con Stock Bajo:', stockBajo.length]);
        resumenData.push(['Productos con Stock Normal:', stockNormal.length]);
        resumenData.push([]);
        
        // Calcular valor total del inventario
        const valorTotal = productos.reduce((sum, p) => {
            return sum + ((parseInt(p.stock_actual_producto) || 0) * (parseFloat(p.precio_producto) || 0));
        }, 0);
        
        resumenData.push(['VALOR DE INVENTARIO']);
        resumenData.push(['Valor Total (S/):', valorTotal.toFixed(2)]);
        resumenData.push([]);
        
        // Estadísticas por categoría
        resumenData.push(['DISTRIBUCIÓN POR CATEGORÍA']);
        const categorias = {};
        productos.forEach(p => {
            const cat = p.categoria_nombre || p.nombre_categoria || 'Sin categoría';
            if (!categorias[cat]) {
                categorias[cat] = { cantidad: 0, stock: 0 };
            }
            categorias[cat].cantidad++;
            categorias[cat].stock += parseInt(p.stock_actual_producto) || 0;
        });
        
        resumenData.push(['Categoría', 'Productos', 'Stock Total']);
        Object.entries(categorias).forEach(([cat, data]) => {
            resumenData.push([cat, data.cantidad, data.stock]);
        });

        const wsResumen = XLSX.utils.aoa_to_sheet(resumenData);
        wsResumen['!cols'] = [{ wch: 35 }, { wch: 20 }];
        XLSX.utils.book_append_sheet(wb, wsResumen, "Resumen");

        // ==================== HOJA 2: STOCK CRÍTICO ====================
        const criticoData = [];
        criticoData.push(['PRODUCTOS SIN STOCK - REQUIEREN ATENCIÓN INMEDIATA']);
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
        bajoData.push(['PRODUCTOS CON STOCK BAJO - REQUIEREN REPOSICIÓN']);
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
        inventarioData.push(['INVENTARIO COMPLETO - TODOS LOS PRODUCTOS']);
        inventarioData.push([]);
        inventarioData.push([
            'ID', 'Nombre', 'Categoría', 'Marca', 'Género', 
            'Stock Actual', 'Stock Mínimo', 'Diferencia', 'Precio (S/)', 
            'Valor Inventario (S/)', 'Estado Stock'
        ]);
        
        productos.forEach(producto => {
            const stockActual = parseInt(producto.stock_actual_producto) || 0;
            const stockMinimo = parseInt(producto.stock_minimo_producto) || 5;
            const precio = parseFloat(producto.precio_producto) || 0;
            const genero = producto.genero_producto || producto.genero || 'Unisex';
            const generoLabel = genero === 'M' ? 'Masculino' : 
                              genero === 'F' ? 'Femenino' : 
                              genero === 'Kids' ? 'Kids' : 'Unisex';
            
            let estadoStock = 'Normal';
            if (stockActual === 0) estadoStock = 'CRÍTICO';
            else if (stockActual <= stockMinimo) estadoStock = 'Bajo';
            
            inventarioData.push([
                producto.id_producto || '',
                producto.nombre_producto || '',
                producto.categoria_nombre || producto.nombre_categoria || '',
                producto.marca_producto || '',
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
        showNotification('Error al generar reporte de stock', 'error');
    }
}

// Función para limpiar búsqueda con animación
function clearProductSearch() {
    if (typeof $ !== 'undefined') {
        const searchInput = $('#search-productos');
        searchInput.val('').focus();
        
        // Animación visual
        const searchContainer = searchInput.parent();
        searchContainer.addClass('search-cleared');
        
        setTimeout(() => {
            searchContainer.removeClass('search-cleared');
        }, 300);
    } else {
        // Fallback vanilla JS
        const searchInput = document.getElementById('search-productos');
        if (searchInput) {
            searchInput.value = '';
            searchInput.focus();
        }
    }
    
    filterProducts();
}

// Función para limpiar todos los filtros con efectos visuales
function clearAllProductFilters() {
    if (typeof $ !== 'undefined') {
        // Limpiar todos los campos con jQuery
        $('#search-productos').val('');
        $('#filter-category').val('');
        $('#filter-marca').val(''); // ✅ Limpiar filtro de marca
        $('#filter-status').val('');
        $('#filter-stock').val('');
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
            'search-productos',
            'filter-category',
            'filter-marca', // ✅ Limpiar filtro de marca
            'filter-status',
            'filter-stock',
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
    filterProducts();
}

// Función para acciones en lote
async function handleBulkProductAction(action) {
    const selectedProducts = getSelectedProducts();
    
    if (selectedProducts.length === 0) {
        // // showNotification('Por favor selecciona al menos un producto', 'warning');
        return;
    }    
    const confirmMessage = `¿Estás seguro de ${action} ${selectedProducts.length} producto(s)?`;
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
            // showNotification(`${action} completado para ${selectedProducts.length} producto(s)`, 'success');
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

// Función para ver producto (wrapper que llama al parent)
function viewProduct(id) {
    
    // CERRAR BURBUJA DE STOCK si está abierta
    closeStockBubble();
    
    // Verificar si el ID es válido
    if (!id || id === 'undefined' || id === 'null') {
        if (typeof showNotification === 'function') {
            // showNotification('Error: ID de producto inválido', 'error');
        }
        return;
    }

    
    // Como NO estamos en iframe, parent === window
    // Buscar directamente en window
    if (typeof window.showViewProductModal === 'function') {
        window.showViewProductModal(id);
    } else if (typeof window.viewProduct !== viewProduct && typeof window.viewProduct === 'function') {
        // Evitar recursión infinita
        window.viewProduct(id);
    } else {
        // Fallback: abrir en nueva ventana
        const url = AppConfig ? AppConfig.getViewUrl(`admin/product_modal.php?action=view&id=${id}`) : `/fashion-master/app/views/admin/product_modal.php?action=view&id=${id}`;
        window.open(url, 'ProductView', 'width=900,height=700');
    }
}

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
    }

// Función para editar producto
async function editProduct(id) {
    
    // CERRAR BURBUJA DE STOCK si está abierta
    closeStockBubble();
    
    // Verificar si el ID es válido
    if (!id || id === 'undefined' || id === 'null') {
        if (typeof showNotification === 'function') {
            // showNotification('Error: ID de producto inválido', 'error');
        }
        return;
    }

    
    // Como NO estamos en iframe, parent === window
    // Buscar directamente en window
    if (typeof window.showEditProductModal === 'function') {
        window.showEditProductModal(id);
    } else if (typeof window.editProduct !== editProduct && typeof window.editProduct === 'function') {
        // Evitar recursión infinita
        window.editProduct(id);
    } else {
        // Fallback: abrir en nueva ventana
        const url = AppConfig ? AppConfig.getViewUrl(`admin/product_modal.php?action=edit&id=${id}`) : `/fashion-master/app/views/admin/product_modal.php?action=edit&id=${id}`;
        window.open(url, 'ProductEdit', 'width=900,height=700');
    }
}

// Función para actualizar stock - MEJORADA CON BURBUJA SIN BOTONES
function updateStock(id, currentStock, event) {
    // VERIFICAR SI YA EXISTE UNA BURBUJA ABIERTA PARA ESTE PRODUCTO (TOGGLE)
    const existingBubble = document.querySelector(`.stock-update-bubble[data-product-id="${id}"]`);
    if (existingBubble) {
        closeStockBubble();
        return; // SALIR - No abrir de nuevo
    }
    
    // CERRAR MENÚ FLOTANTE SI ESTÁ ABIERTO (sin bloquear futuros menús)
    if (productos_activeFloatingContainer) {
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
    productos_activeFloatingContainer = null;
    productos_activeProductId = null;
    productos_isAnimating = false;
    if (productos_animationTimeout) {
        clearTimeout(productos_animationTimeout);
        productos_animationTimeout = null;
    }
    
    // Eliminar cualquier burbuja existente (de otros productos)
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
    stockBubble.setAttribute('data-product-id', id); // Agregar ID del producto para identificar
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
    
    // Si aún no tenemos el botón, buscarlo en el DOM por el ID del producto
    if (!triggerButton) {
        
        // Determinar qué vista está visible actualmente
        const tableContainer = document.querySelector('.data-table-wrapper');
        const gridContainer = document.querySelector('.products-grid');
        const isTableVisible = tableContainer && tableContainer.style.display !== 'none';
        const isGridVisible = gridContainer && gridContainer.style.display !== 'none';
        
        
        // Buscar en la vista VISIBLE primero
        if (isGridVisible) {
            // Buscar en vista grid (visible)
            const productCard = document.querySelector(`.product-card[data-product-id="${id}"]`);
            if (productCard) {
                triggerButton = productCard.querySelector('.btn-stock');
                if (triggerButton) {
                    isGridView = true;
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
                }
            }
        }
    }
    
    // Último recurso: buscar por atributo onclick en la tabla
    if (!triggerButton) {
        triggerButton = document.querySelector(`[onclick*="showActionMenu(${id}"]`);
        if (triggerButton) {
            isGridView = false;
        }
    }

    
    // VALIDAR QUE EL BOTÓN ESTÉ VISIBLE (no en una vista oculta)
    const rect = triggerButton.getBoundingClientRect();
    if (rect.width === 0 || rect.height === 0) {
        closeStockBubble(); // Cerrar cualquier burbuja residual
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

        const input = stockBubble.querySelector('#stockInput');

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
            
            if (data.success) {
             
                
                // Mostrar notificación de éxito
                if (typeof showNotification === 'function') {
                    showNotification(`✅ Stock actualizado a ${newStock} unidades`, 'success');
                }
                
                // Usar actualización SUAVE sin recargar toda la tabla
                if (window.productosTableUpdater && data.product) {
 
                    try {
                        // Actualizar solo este producto especificando que cambió el campo 'stock'
                        // Parámetros: (productId, updatedData, changedFields)
                        window.productosTableUpdater.updateSingleProduct(data.product.id_producto, data.product, ['stock']);
                    } catch (error) {
                        loadProducts(true);
                    }
                } else {

                    loadProducts(true);
                }
                
                // Cerrar burbuja y overlay
                setTimeout(() => {
                    if (overlay && overlay.parentNode) overlay.remove();
                    if (stockBubble && stockBubble.parentNode) stockBubble.remove();
                }, 400);
            } else {
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
    
    if (!confirm(`¿Estás seguro de ${action} este producto?`)) return;
    
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
            // showNotification(`Producto ${action} exitosamente`, 'success');
            loadProducts(); // Recargar lista
        } else {
            throw new Error(result.message || 'Error al cambiar estado');
        }
        
    } catch (error) {
        // showNotification('Error: ' + error.message, 'error');
    }
}

// Función para cambiar estado del producto (activo/inactivo)
async function changeProductEstado(id) {
    try {
        // Obtener estado actual del producto
        const response = await fetch(`${CONFIG.apiUrl}?action=get&id=${id}`);
        const result = await response.json();
        
        if (!response.ok || !result.success) {
            return;
        }
        
        const currentEstado = result.product.estado;
        const newEstado = currentEstado === 'activo' ? 'inactivo' : 'activo';
        
        
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
            
            // Usar actualización suave si está disponible
            if (window.productosTableUpdater && updateResult.product) {
                window.productosTableUpdater.updateSingleProduct(updateResult.product);
            } else {
                // Recargar lista sin notificaciones
                loadProducts();
            }
        } 
        
    } catch (error) {
    }
}


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

// Función para obtener productos seleccionados
function getSelectedProducts() {
    const checkboxes = document.querySelectorAll('input[name="product_select"]:checked');
    return Array.from(checkboxes).map(cb => parseInt(cb.value));
}

// Función para limpiar selección de productos
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
    
    // Asegurar que CONFIG esté inicializado
    if (typeof CONFIG === 'undefined' || !CONFIG.apiUrl) {
        initializeConfig();
    }

    
    // Verificar que los elementos necesarios existen
    const tbody = document.getElementById('productos-table-body');
    
    // Determinar dispositivo y preparar vista (SIMPLIFICADO)
    const isMobile = window.innerWidth <= 768;
    if (isMobile) {
        window.productos_currentView = 'grid';
        
        const tableElement = document.querySelector('.data-table-wrapper table');
        if (tableElement) {
            tableElement.style.display = 'none';
        }
        
        let gridContainer = document.querySelector('.products-grid');
        if (!gridContainer) {
            createGridView();
            gridContainer = document.querySelector('.products-grid');
        }
        
        if (gridContainer) {
            gridContainer.style.setProperty('display', 'grid', 'important');
            gridContainer.style.setProperty('visibility', 'visible', 'important');
        }
    } else {
        const tableContainer = document.querySelector('.data-table-wrapper');
        const gridContainer = document.querySelector('.products-grid');
        
        if (tableContainer) tableContainer.style.display = 'block';
        if (gridContainer) gridContainer.style.display = 'none';
    }
    
    // Cargar categorías, marcas y productos
    loadCategories();
    loadMarcas();
    
    // Inicializar modal de filtros móvil
    toggleMobileFilterButton();
    window.addEventListener('resize', toggleMobileFilterButton);
    
    // Inicializar control del sidebar móvil
    initMobileFiltersSidebar();
    
    // Cargar productos
    loadProducts();
    
    // ========================================
    // INICIALIZAR LIBRERÍAS MODERNAS
    // ========================================
    
    // 1. Flatpickr para filtro de fecha - BOTÓN que abre calendario
    const filterFecha = document.getElementById('filter-fecha');
    const filterFechaValue = document.getElementById('filter-fecha-value');
    const filterFechaText = document.getElementById('filter-fecha-text');
    
    if (filterFecha && typeof flatpickr !== 'undefined') {
        
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
                                dayElem.title = 'Hay productos en esta fecha';
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
                    filterProducts();
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
                isCalendarOpen = true;
                filterFecha.classList.add('calendar-open');
                
                // ⚡ REDIBUJAR SILENCIOSAMENTE para actualizar marcas (solo cuando se abre)
                window.productsDatePicker.redraw();
                
                // ⭐ LIMPIAR fechas automáticamente al abrir (como hacer click en "Limpiar")
                window.productsDatePicker.clear();
                
                // Limpiar valores
                if (filterFechaValue) filterFechaValue.value = '';
                if (filterFechaText) filterFechaText.textContent = 'Seleccionar fechas';
                
                // Re-cargar TODOS los productos (sin filtro de fecha)
                filterProducts();
                
                // FORZAR marcado múltiples veces
                setTimeout(() => markMonthsWithProducts(), 10);
                setTimeout(() => markMonthsWithProducts(), 50);
                setTimeout(() => markMonthsWithProducts(), 100);
                setTimeout(() => startObserving(), 150);
            },
            onClose: function() {
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
                // Marcar visualmente las fechas con productos
                const dateStr = dayElem.dateObj.toISOString().split('T')[0];
                if (window.productsDatesArray && window.productsDatesArray.includes(dateStr)) {
                    dayElem.classList.add('has-products');
                    dayElem.title = 'Hay productos en esta fecha';
                }
            }
        });
        
        // Función para marcar meses con productos
        function markMonthsWithProducts() {
            if (!window.productsDatesArray || window.productsDatesArray.length === 0) return;
            
            const calendarEl = document.querySelector('.flatpickr-calendar:not(.inline)');
            if (!calendarEl) return;
            
            // Obtener meses únicos de las fechas de productos
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
                    
                    // Agregar indicador si hay productos este mes (círculo verde como los días)
                    if (monthsWithProducts.has(currentYearMonth)) {
                        const indicator = document.createElement('span');
                        indicator.className = 'month-has-products-indicator';
                        indicator.innerHTML = '<span class="green-dot"></span>';
                        indicator.title = 'Hay productos en este mes';
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
                        
                        // Si hay productos, usar el caracter ⬤ (círculo grande) que se ve mejor
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
            
            // Re-marcar todos los días con productos (FORZAR)
            const days = calendarEl.querySelectorAll('.flatpickr-day:not(.flatpickr-disabled)');
            days.forEach(dayElem => {
                if (dayElem.dateObj) {
                    const dateStr = dayElem.dateObj.toISOString().split('T')[0];
                    if (window.productsDatesArray.includes(dateStr)) {
                        if (!dayElem.classList.contains('has-products')) {
                            dayElem.classList.add('has-products');
                            dayElem.title = 'Hay productos en esta fecha';
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
        
    }
    
    // 2. Flatpickr para filtro de fecha en modal móvil - BOTÓN que abre calendario
    const filterFechaModal = document.getElementById('modal-filter-fecha');
    const filterFechaModalValue = document.getElementById('modal-filter-fecha-value');
    const filterFechaModalText = document.getElementById('modal-filter-fecha-text');
    
    if (filterFechaModal && typeof flatpickr !== 'undefined') {
        
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
                                dayElem.title = 'Hay productos en esta fecha';
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
                    filterProducts();
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
                
                // Re-cargar TODOS los productos (sin filtro de fecha)
                filterProducts();
                
                // FORZAR marcado múltiples veces
                setTimeout(() => markMonthsWithProducts(), 50);
                setTimeout(() => markMonthsWithProducts(), 100);
                setTimeout(() => markMonthsWithProducts(), 200);
                setTimeout(() => startObservingModal(), 250);
            },
            onClose: function() {
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
                // Marcar visualmente las fechas con productos - SOLO CLASE
                const dateStr = dayElem.dateObj.toISOString().split('T')[0];
                if (window.productsDatesArray && window.productsDatesArray.includes(dateStr)) {
                    dayElem.classList.add('has-products');
                    dayElem.title = 'Hay productos en esta fecha';
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
        
    }
    
    // 3. Agregar animaciones AOS a elementos
    const moduleHeader = document.querySelector('.admin-products-module .module-header');
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
                debugLog('📱 Cambio a móvil detectado');
                
                viewButtons.forEach(btn => {
                    if (btn.dataset.view === 'table') {
                        btn.disabled = true;
                        btn.style.opacity = '0.5';
                        btn.style.cursor = 'not-allowed';
                        btn.title = 'Vista tabla no disponible en móvil';
                    }
                });
                
                // Solo cambiar si NO está en grid
                if (window.productos_currentView !== 'grid') {
                    toggleProductoView('grid', true);
                }
            } else {
                // Si cambió a desktop, desbloquear botones
                debugLog('💻 Cambio a desktop detectado');
                
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
        }
    }, 200);
    
    
    const initSmoothUpdater = () => {
        // 🔥 SIEMPRE destruir instancia anterior antes de crear nueva
        if (window.productosTableUpdater) {
            if (typeof window.productosTableUpdater.destroy === 'function') {
                window.productosTableUpdater.destroy();
            }
            window.productosTableUpdater = null;
        }
        
        // ✅ Crear NUEVA instancia SOLO si la clase está disponible
        if (typeof ProductosTableUpdater !== 'undefined') {
            window.productosTableUpdater = new ProductosTableUpdater();
        }
    };
    
    // Escuchar el evento de carga del script
    window.addEventListener('smoothTableUpdaterLoaded', initSmoothUpdater, { once: true });
    
    // Fallback: intentar inicializar inmediatamente si ya está disponible
    setTimeout(initSmoothUpdater, 300);
    
    // Función de debugging para verificar funciones disponibles
    window.debugProductsFunctions = function() {
        const functions = [
            'loadProducts', 'loadCategories', 'filterProducts', 'handleSearchInput', 
            'toggleView', 'showActionMenu', 'editProduct', 'viewProduct', 'deleteProduct',
            'toggleProductStatus', 'updateStock', 'exportProducts'
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
window.loadProducts = loadProducts;
window.loadProductos = loadProducts;
window.loadCategories = loadCategories;
window.filterProducts = filterProducts;
window.handleSearchInput = handleSearchInput;
window.toggleProductoView = toggleProductoView;
window.showActionMenu = showActionMenu;
window.clearProductSearch = clearProductSearch;
window.clearAllProductFilters = clearAllProductFilters;
window.exportProducts = exportProducts;
window.showStockReport = showStockReport;
window.editProduct = editProduct;
window.viewProduct = viewProduct;
window.deleteProduct = deleteProduct;
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
    const gridViewBtn = document.querySelector('[onclick="toggleProductoView(\'grid\')"]');
    const tableViewBtn = document.querySelector('[onclick="toggleProductoView(\'table\')"]');
    
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
    const searchInput = document.getElementById('search-productos');
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
                <p>Para eliminar el producto <strong>"${productName}"</strong>, escribe la palabra <strong>"eliminar"</strong> en el campo de abajo:</p>
                
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
                    <i class="fas fa-trash"></i> Eliminar Producto
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
    
    // ⚡ OPTIMISTIC UI: Eliminar fila inmediatamente (feedback instantáneo)
    closeDeleteConfirmation();
    
    if (window.productosTableUpdater && typeof window.productosTableUpdater.removeProduct === 'function') {
        window.productosTableUpdater.removeProduct(productId).catch(() => {
            // Si falla la animación, continuar de todas formas
        });
    }
    
    // Actualizar contadores inmediatamente
    const totalElement = document.getElementById('total-products');
    if (totalElement) {
        const currentTotal = parseInt(totalElement.textContent) || 0;
        totalElement.textContent = Math.max(0, currentTotal - 1);
    }
    
    const showingEndElement = document.getElementById('showing-end-products');
    if (showingEndElement) {
        const currentShowing = parseInt(showingEndElement.textContent) || 0;
        showingEndElement.textContent = Math.max(0, currentShowing - 1);
    }
    
    // Proceder con eliminación en servidor (confirmación)
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', productId);
    
    fetch(`${CONFIG.apiUrl}`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(`Producto "${productName}" eliminado exitosamente`, 'success');
            resetUpdateTimestamp(); // Reiniciar timer de auto-refresh
        } else {
            // ⚠️ REVERTIR cambio optimista en caso de error
            showNotification('Error al eliminar producto: ' + (data.error || 'Error desconocido'), 'error');
            loadProducts(true); // Recargar para restaurar el producto
        }
    })
    .catch(error => {
        // ⚠️ REVERTIR cambio optimista en caso de error de red
        showNotification('Error de conexión al eliminar producto', 'error');
        loadProducts(true); // Recargar para restaurar el producto
    });
}

// Función para alternar estado del producto (activo/inactivo)
function toggleProductStatus(productId, currentStatus) {
    const newStatus = currentStatus ? 0 : 1;
    
    // ⚡ OPTIMISTIC UI: Actualizar el botón inmediatamente
    const row = document.querySelector(`tr[data-product-id="${productId}"]`);
    if (row) {
        const statusBtn = row.querySelector('.btn-toggle-status');
        if (statusBtn) {
            // Actualizar visual inmediatamente
            if (newStatus === 1) {
                statusBtn.innerHTML = '<i class="fas fa-check-circle"></i> Activo';
                statusBtn.className = 'btn-toggle-status status-active';
            } else {
                statusBtn.innerHTML = '<i class="fas fa-times-circle"></i> Inactivo';
                statusBtn.className = 'btn-toggle-status status-inactive';
            }
            statusBtn.disabled = true; // Deshabilitar mientras se procesa
        }
    }
    
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
            resetUpdateTimestamp(); // Reiniciar timer de auto-refresh
            
            // Re-habilitar botón
            if (row) {
                const statusBtn = row.querySelector('.btn-toggle-status');
                if (statusBtn) {
                    statusBtn.disabled = false;
                }
            }
            
            // Usar actualización suave si está disponible
            if (window.productosTableUpdater && data.product) {
                window.productosTableUpdater.updateSingleProduct(data.product);
            }
        } else {
            // ⚠️ REVERTIR cambio optimista en caso de error
            if (row) {
                const statusBtn = row.querySelector('.btn-toggle-status');
                if (statusBtn) {
                    statusBtn.disabled = false;
                    // Revertir al estado original
                    if (currentStatus) {
                        statusBtn.innerHTML = '<i class="fas fa-check-circle"></i> Activo';
                        statusBtn.className = 'btn-toggle-status status-active';
                    } else {
                        statusBtn.innerHTML = '<i class="fas fa-times-circle"></i> Inactivo';
                        statusBtn.className = 'btn-toggle-status status-inactive';
                    }
                }
            }
            if (typeof showNotification === 'function') {
                showNotification('Error al cambiar estado: ' + (data.error || 'Error desconocido'), 'error');
            }
        }
    })
    .catch(error => {
        // ⚠️ REVERTIR cambio optimista en caso de error de red
        if (row) {
            const statusBtn = row.querySelector('.btn-toggle-status');
            if (statusBtn) {
                statusBtn.disabled = false;
                // Revertir al estado original
                if (currentStatus) {
                    statusBtn.innerHTML = '<i class="fas fa-check-circle"></i> Activo';
                    statusBtn.className = 'btn-toggle-status status-active';
                } else {
                    statusBtn.innerHTML = '<i class="fas fa-times-circle"></i> Inactivo';
                    statusBtn.className = 'btn-toggle-status status-inactive';
                }
            }
        }
        if (typeof showNotification === 'function') {
            showNotification('Error de conexión', 'error');
        }
    });
}
        if (typeof showNotification === 'function') {
            // showNotification('Error de conexión al cambiar estado', 'error');
        } else {
            // alert('Error de conexión al cambiar estado');
        }
 


// Función wrapper para eliminar producto
function deleteProduct(productId, productName) {
    showDeleteConfirmation(productId, productName || 'Producto');
}

// ============ FUNCIÓN PARA MOSTRAR IMAGEN EN TAMAÑO REAL ============

function showImageFullSize(imageUrl, productName) {
    
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
    img.alt = productName || 'Producto';
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
            }
        });
        // Resetear variables globales
        productos_activeFloatingContainer = null;
        productos_centerButton = null;
        productos_floatingButtons = [];
        productos_activeProductId = null;
        productos_isAnimating = false;
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

// ===== FUNCIÓN DE DESTRUCCIÓN DEL MÓDULO DE PRODUCTOS =====
window.destroyProductosModule = function() {
    
    try {
        // 🔥 0. DESTRUIR UPDATER DE PRODUCTOS PRIMERO
        if (window.productosTableUpdater) {
            if (typeof window.productosTableUpdater.destroy === 'function') {
                window.productosTableUpdater.destroy();
            }
            window.productosTableUpdater = null;
        }
        
        // 1. Limpiar variable de estado de carga
        if (typeof isLoading !== 'undefined') {
            isLoading = false;
        }
        
        // 2. Limpiar arrays de datos
        if (typeof productos !== 'undefined') {
            productos = [];
        }
        
        // 3. Resetear paginación
        if (typeof currentPage !== 'undefined') {
            currentPage = 1;
        }
        if (typeof totalPages !== 'undefined') {
            totalPages = 1;
        }
        
        // 4. Limpiar event listeners clonando elementos
        const searchInput = document.getElementById('search-productos');
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
        
        // 5. Limpiar modales de productos
        const productModals = document.querySelectorAll('.product-view-modal, .product-modal, [id*="product-modal"]');
        productModals.forEach(modal => {
            modal.remove();
        });
        
        // 6. Limpiar burbujas flotantes de stock Y contenedores flotantes de productos SOLAMENTE
        const stockBubbles = document.querySelectorAll('.stock-update-bubble');
        stockBubbles.forEach(bubble => {
            bubble.remove();
        });
        
        // Limpiar SOLO los contenedores flotantes que pertenecen a productos
        if (productos_activeFloatingContainer && document.contains(productos_activeFloatingContainer)) {
            productos_activeFloatingContainer.remove();
        }
        
        // Resetear variables flotantes de productos
        productos_activeFloatingContainer = null;
        productos_centerButton = null;
        productos_floatingButtons = [];
        productos_activeProductId = null;
        productos_isAnimating = false;
        
        // 7. Limpiar confirmaciones de eliminación
        const deleteConfirmations = document.querySelectorAll('.delete-confirmation-overlay');
        deleteConfirmations.forEach(confirmation => {
            confirmation.remove();
        });
        
        // 8. Limpiar el tbody de la tabla
        const tbody = document.getElementById('productos-table-body');
        if (tbody) {
            tbody.innerHTML = '';
        }
        
        // 9. LIMPIAR CONTENIDO DEL GRID (pero NO eliminarlo - mantener estado)
        const gridContainer = document.querySelector('.products-grid');
        if (gridContainer) {
            // Solo limpiar contenido, NO cambiar display ni eliminar
            gridContainer.innerHTML = '';
        }
        
        
        // 10. Remover clases de body que puedan interferir
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
        
        // 11. ANULAR FUNCIONES GLOBALES para liberar memoria y evitar colisiones
        console.log('🧹 Anulando funciones globales de productos...');
        window.toggleProductoView = null;
        window.displayProductos = null;
        window.displayProductosGrid = null;
        window.loadProducts = null;
        window.filterProducts = null;
        window.showActionMenu = null;
        window.closeFloatingActionsAnimated = null;
        
        // 12. RESETEAR VARIABLE DE VISTA GLOBAL
        window.productos_currentView = null;
        
        console.log('✅ Módulo de productos destruido correctamente');
        
    } catch (error) {
        console.error('❌ Error al destruir módulo de productos:', error);
    }
};

