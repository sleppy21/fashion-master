/**
 * MÓDULO DE GESTIÓN DE PRODUCTOS - ADMIN PANEL
 * Versión: 2.0 - Sistema completo optimizado
 */

(function() {
    'use strict';

    // ============================================================================
    // CONFIGURACIÓN
    // ============================================================================

    const CONFIG = {
        apiUrl: window.PHP_CONFIG?.apiProductController || '',
        baseUrl: window.PHP_CONFIG?.baseUrl || '',
        imagesUrl: window.PHP_CONFIG?.imagesUrl || '',
        maxImageSize: 2 * 1024 * 1024,
        allowedImageTypes: ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'],
        itemsPerPage: 10
    };

    console.log('📦 Admin Productos - Configuración cargada:', CONFIG);

    // ============================================================================
    // ESTADO GLOBAL
    // ============================================================================

    let state = {
        productos: [],
        categories: [],
        brands: [],
        currentPage: 1,
        totalPages: 1,
        sortColumn: null,
        sortOrder: 'asc',
        isLoading: false,
        currentProductId: null,
        currentView: window.innerWidth <= 768 ? 'grid' : 'table' // grid en móvil, tabla en PC
    };

    // ============================================================================
    // INICIALIZACIÓN
    // ============================================================================

    function init() {
        console.log('🚀 Inicializando módulo de productos...');
        
        if (!CONFIG.apiUrl) {
            console.error('❌ Error: PHP_CONFIG no está disponible');
            showError('Error de configuración. Por favor, recarga la página.');
            return;
        }

        setupEventListeners();
        loadInitialData();
    }

    // ============================================================================
    // EVENT LISTENERS
    // ============================================================================

    function setupEventListeners() {
        console.log('🎯 Configurando event listeners...');

        // Los botones usan onclick directamente en HTML, no necesitan listeners aquí
        
        // Listener para cambio de tamaño de ventana
        window.addEventListener('resize', debounce(() => {
            const newView = window.innerWidth <= 768 ? 'grid' : 'table';
            if (newView !== state.currentView) {
                state.currentView = newView;
                renderProducts();
            }
        }, 300));

        console.log('✅ Event listeners configurados');
    }

    // ============================================================================
    // CARGA DE DATOS
    // ============================================================================

    async function loadInitialData() {
        console.log('📥 Cargando datos iniciales...');
        
        try {
            showLoading(true);

            // Cargar categorías y marcas en paralelo
            const [categories, brands] = await Promise.all([
                loadCategories(),
                loadBrands()
            ]);

            state.categories = categories;
            state.brands = brands;

            console.log('📦 Categorías cargadas:', categories.length);
            console.log('🏷️ Marcas cargadas:', brands.length);

            // Poblar filtros
            populateFilters();

            // Cargar productos
            await loadProducts();

            console.log('✅ Datos iniciales cargados correctamente');

        } catch (error) {
            console.error('❌ Error al cargar datos iniciales:', error);
            showError('Error al cargar los datos iniciales: ' + error.message);
        } finally {
            showLoading(false);
        }
    }

    async function loadCategories() {
        try {
            const response = await fetch(`${CONFIG.apiUrl}?action=get_categories`);
            const data = await response.json();
            return data.success ? data.data : [];
        } catch (error) {
            console.error('❌ Error al cargar categorías:', error);
            return [];
        }
    }

    async function loadBrands() {
        try {
            const response = await fetch(`${CONFIG.apiUrl}?action=get_marcas`);
            const data = await response.json();
            return data.success ? data.data : [];
        } catch (error) {
            console.error('❌ Error al cargar marcas:', error);
            return [];
        }
    }

    async function loadProducts() {
        console.log('📦 Cargando productos...');
        
        try {
            showLoading(true);

            const filters = getFilters();
            const params = new URLSearchParams({
                action: 'list',
                page: state.currentPage,
                limit: CONFIG.itemsPerPage,
                ...filters
            });

            if (state.sortColumn) {
                params.append('sort_by', state.sortColumn);
                params.append('sort_order', state.sortOrder);
            }

            const response = await fetch(`${CONFIG.apiUrl}?${params}`);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();
            console.log('📦 Respuesta del servidor:', data);

            if (data.success) {
                state.productos = data.data || [];
                state.totalPages = data.pagination?.total_pages || data.total_pages || 1;
                state.currentPage = data.pagination?.current_page || data.current_page || 1;

                console.log(`✅ ${state.productos.length} productos cargados`);
                
                renderProducts();
                renderPagination();
                updateStats();
            } else {
                throw new Error(data.message || data.error || 'Error al cargar productos');
            }

        } catch (error) {
            console.error('❌ Error al cargar productos:', error);
            showError('Error al cargar productos: ' + error.message);
            state.productos = [];
            renderProducts();
        } finally {
            showLoading(false);
        }
    }

    function getFilters() {
        const filters = {};

        const search = document.getElementById('search-productos')?.value?.trim();
        if (search) filters.search = search;

        const category = document.getElementById('filter-category')?.value;
        if (category) filters.category = category;

        const brand = document.getElementById('filter-marca')?.value;
        if (brand) filters.marca = brand;

        const status = document.getElementById('filter-status')?.value;
        if (status) filters.status = status;

        return filters;
    }

    // ============================================================================
    // RENDERIZADO
    // ============================================================================

    function renderProducts() {
        // Detectar vista actual según el ancho de pantalla
        const isMobile = window.innerWidth <= 768;
        console.log(`📱 Renderizando productos - Ancho: ${window.innerWidth}px - Vista: ${isMobile ? 'GRID (móvil)' : 'TABLA (desktop)'}`);
        
        if (isMobile) {
            state.currentView = 'grid';
            renderProductsGrid();
        } else {
            state.currentView = 'table';
            renderProductsTable();
        }
    }

    function renderProductsTable() {
        const tableContainer = document.querySelector('.data-table-wrapper');
        const gridContainer = document.getElementById('productos-grid-container');
        const container = document.getElementById('productos-table-body');

        console.log('📋 Renderizando vista de TABLA');

        // Mostrar tabla y ocultar grid
        if (tableContainer) {
            tableContainer.style.display = '';
        }
        if (gridContainer) {
            gridContainer.style.display = 'none';
        }
        
        if (!container) {
            console.error('❌ No se encontró el contenedor de productos (tabla)');
            return;
        }

        if (state.productos.length === 0) {
            container.innerHTML = `
                <tr>
                    <td colspan="11" class="text-center py-5">
                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No se encontraron productos</p>
                    </td>
                </tr>
            `;
            return;
        }

        container.innerHTML = state.productos.map(product => `
            <tr data-product-id="${product.id_producto}">
                <td>${product.id_producto}</td>
                <td>
                    <img src="${getProductImage(product.imagen_producto)}" 
                         alt="${escapeHtml(product.nombre_producto)}"
                         class="img-thumbnail" 
                         style="width: 50px; height: 50px; object-fit: cover;">
                </td>
                <td>
                    <strong>${escapeHtml(product.nombre_producto)}</strong>
                    <br>
                    <small class="text-muted">${escapeHtml(product.codigo_producto || 'N/A')}</small>
                </td>
                <td>${escapeHtml(product.categoria_nombre || 'Sin categoría')}</td>
                <td>${escapeHtml(product.marca_nombre || 'Sin marca')}</td>
                <td>${escapeHtml(product.genero_producto || 'N/A')}</td>
                <td>$${parseFloat(product.precio_producto).toFixed(2)}</td>
                <td>
                    <span class="badge ${getStockBadgeClass(product.stock_producto)}">
                        ${product.stock_producto}
                    </span>
                </td>
                <td>
                    <span class="badge ${product.estado_producto === '1' ? 'bg-success' : 'bg-danger'}">
                        ${product.estado_producto === '1' ? 'Activo' : 'Inactivo'}
                    </span>
                </td>
                <td>${formatDate(product.fecha_registro)}</td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="window.adminProductos.editProduct(${product.id_producto})" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-outline-warning" onclick="window.adminProductos.toggleStatus(${product.id_producto})" title="Cambiar estado">
                            <i class="fas fa-toggle-on"></i>
                        </button>
                        <button class="btn btn-outline-danger" onclick="window.adminProductos.deleteProduct(${product.id_producto})" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');

        console.log(`✅ ${state.productos.length} productos renderizados (tabla)`);
    }

    function renderProductsGrid() {
        // Buscar o crear contenedor grid
        let container = document.getElementById('productos-grid-container');
        const tableWrapper = document.querySelector('.data-table-wrapper');
        
        if (!container) {
            // Crear contenedor grid si no existe
            if (!tableWrapper) {
                console.error('❌ No se encontró el wrapper de tabla');
                return;
            }
            
            container = document.createElement('div');
            container.id = 'productos-grid-container';
            container.className = 'products-grid-view';
            tableWrapper.parentNode.insertBefore(container, tableWrapper);
        }

        // Ocultar tabla y mostrar grid
        if (tableWrapper) {
            tableWrapper.style.display = 'none';
        }
        container.style.display = 'grid';

        if (state.productos.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No se encontraron productos</p>
                </div>
            `;
            return;
        }

        container.innerHTML = state.productos.map(product => `
            <div class="product-card product-card-modern" data-product-id="${product.id_producto}">
                <div class="product-image-wrapper">
                    <img class="product-image" 
                         src="${getProductImage(product.imagen_producto)}" 
                         alt="${escapeHtml(product.nombre_producto)}">
                    <span class="product-card-badge ${product.estado_producto === '1' ? 'badge-success' : 'badge-danger'}">
                        ${product.estado_producto === '1' ? 'Activo' : 'Inactivo'}
                    </span>
                </div>
                <div class="product-card-body">
                    <h3 class="product-card-title">${escapeHtml(product.nombre_producto)}</h3>
                    <p class="product-card-code">${escapeHtml(product.codigo_producto || 'N/A')}</p>
                    <div class="product-card-info">
                        <span><i class="fas fa-tag"></i> ${escapeHtml(product.categoria_nombre || 'Sin categoría')}</span>
                        <span><i class="fas fa-copyright"></i> ${escapeHtml(product.marca_nombre || 'Sin marca')}</span>
                    </div>
                    <div class="product-card-price">
                        <span class="price">$${parseFloat(product.precio_producto).toFixed(2)}</span>
                        <span class="stock badge ${getStockBadgeClass(product.stock_producto)}">
                            <i class="fas fa-boxes"></i> ${product.stock_producto}
                        </span>
                    </div>
                    <div class="product-card-actions">
                        <button class="btn btn-menu" 
                                style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #007bff, #0056b3); color: #ffffff; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 1rem; box-shadow: 0 2px 8px rgba(0, 123, 255, 0.3); transition: all 0.3s ease;"
                                onmouseenter="this.style.background='linear-gradient(135deg, #0056b3, #003d82)'; this.style.transform='scale(1.1)'"
                                onmouseleave="this.style.background='linear-gradient(135deg, #007bff, #0056b3)'; this.style.transform='scale(1)'"
                                onclick="showActionMenu(${product.id_producto}, '${escapeHtml(product.nombre_producto)}', ${product.stock_producto}, '${product.estado}', event)">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                    </div>
                </div>
            </div>
        `).join('');

        console.log(`✅ ${state.productos.length} productos renderizados (grid)`);
        
        // Disparar evento para que el adapter global procese las imágenes
        document.dispatchEvent(new CustomEvent('productsUpdated'));
    }

    function renderPagination() {
        const container = document.getElementById('pagination-numbers-products');
        
        if (!container || state.totalPages <= 1) {
            if (container) container.innerHTML = '';
            return;
        }

        let html = '';

        // Páginas
        for (let i = 1; i <= state.totalPages; i++) {
            if (
                i === 1 ||
                i === state.totalPages ||
                (i >= state.currentPage - 2 && i <= state.currentPage + 2)
            ) {
                html += `
                    <button class="page-number ${i === state.currentPage ? 'active' : ''}" 
                            onclick="window.adminProductos.goToPage(${i})">
                        ${i}
                    </button>
                `;
            } else if (i === state.currentPage - 3 || i === state.currentPage + 3) {
                html += '<span class="page-ellipsis">...</span>';
            }
        }

        container.innerHTML = html;
        
        // Actualizar info de página
        const currentPageEl = document.getElementById('current-page-products');
        const totalPagesEl = document.getElementById('total-pages-products');
        if (currentPageEl) currentPageEl.textContent = state.currentPage;
        if (totalPagesEl) totalPagesEl.textContent = state.totalPages;
    }

    function populateFilters() {
        // Poblar categorías
        const categoryFilter = document.getElementById('filter-category');
        if (categoryFilter && state.categories.length > 0) {
            categoryFilter.innerHTML = '<option value="">Todas las categorías</option>' +
                state.categories.map(cat => 
                    `<option value="${cat.id_categoria}">${escapeHtml(cat.nombre_categoria)}</option>`
                ).join('');
        }

        // Poblar marcas
        const brandFilter = document.getElementById('filter-marca');
        if (brandFilter && state.brands.length > 0) {
            brandFilter.innerHTML = '<option value="">Todas las marcas</option>' +
                state.brands.map(brand => 
                    `<option value="${brand.id_marca}">${escapeHtml(brand.nombre_marca)}</option>`
                ).join('');
        }
    }

    function updateStats() {
        const showingEnd = document.getElementById('showing-end-products');
        const totalElement = document.getElementById('total-products');
        
        if (showingEnd) {
            showingEnd.textContent = state.productos.length;
        }
        if (totalElement) {
            totalElement.textContent = state.productos.length;
        }
    }

    // ============================================================================
    // ACCIONES DE PRODUCTO
    // ============================================================================

    async function editProduct(id) {
        // Usar ProductModal en lugar de openProductModal
        if (window.ProductModal) {
            window.ProductModal.open(id);
        }
    }

    async function deleteProduct(id) {
        if (!confirm('¿Estás seguro de eliminar este producto?')) {
            return;
        }

        try {
            showLoading(true);

            const response = await fetch(`${CONFIG.apiUrl}?action=delete&id=${id}`, {
                method: 'DELETE'
            });

            const data = await response.json();

            if (data.success) {
                showSuccess('Producto eliminado correctamente');
                loadProducts();
            } else {
                throw new Error(data.message || 'Error al eliminar producto');
            }

        } catch (error) {
            console.error('❌ Error al eliminar producto:', error);
            showError('Error al eliminar producto: ' + error.message);
        } finally {
            showLoading(false);
        }
    }

    /**
     * Alterna el estado de un producto entre activo/inactivo
     */
    async function toggleStatus(id) {
        try {
            const response = await fetch(`${CONFIG.apiUrl}?action=toggle_status&id=${id}`, {
                method: 'POST'
            });

            const data = await response.json();

            if (data.success) {
                // Recargar productos para actualizar la vista completa
                await loadProducts();
                
                console.log(`✅ Estado actualizado: ${data.new_estado}`);
            } else {
                throw new Error(data.message || data.error || 'Error al cambiar estado');
            }

        } catch (error) {
            console.error('❌ Error al cambiar estado:', error);
            showError('Error al cambiar estado: ' + error.message);
        }
    }

    function goToPage(page) {
        if (page < 1 || page > state.totalPages || page === state.currentPage) {
            return;
        }

        state.currentPage = page;
        loadProducts();
    }

    // ============================================================================
    // UTILIDADES
    // ============================================================================

    function getProductImage(imageName) {
        if (!imageName) {
            return `${CONFIG.baseUrl}/public/assets/img/products/default.jpg`;
        }
        return `${CONFIG.imagesUrl}/${imageName}`;
    }

    function getStockBadgeClass(stock) {
        stock = parseInt(stock);
        if (stock === 0) return 'bg-danger';
        if (stock < 10) return 'bg-warning';
        return 'bg-success';
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        return `${day}/${month}/${year}`;
    }

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    function showLoading(show) {
        state.isLoading = show;
        const spinner = document.getElementById('loadingSpinner');
        if (spinner) {
            spinner.style.display = show ? 'block' : 'none';
        }
    }

    function showError(message) {
        console.error('❌', message);
        alert(message);
    }

    function showSuccess(message) {
        console.log('✅', message);
        alert(message);
    }

    // ============================================================================
    // API PÚBLICA
    // ============================================================================

    // ============================================================================
    // ACTUALIZACIÓN EN TIEMPO REAL
    // ============================================================================

    /**
     * Actualiza un producto específico en la vista sin recargar toda la página
     * @param {number} productId - ID del producto a actualizar
     */
    async function refreshProduct(productId) {
        try {
            console.log('🔄 Actualizando producto ID:', productId);

            // Obtener datos actualizados del producto
            const response = await fetch(`${CONFIG.apiUrl}?action=get&id=${productId}`);
            const data = await response.json();

            if (!data.success || !data.product && !data.data) {
                throw new Error('No se pudieron obtener los datos del producto');
            }

            let updatedProduct = data.product || data.data;

            // Normalizar TODOS los campos de la BD a los que usa la vista
            updatedProduct = {
                // IDs
                id_producto: updatedProduct.id_producto,
                id_categoria: updatedProduct.id_categoria,
                id_marca: updatedProduct.id_marca,
                
                // Información básica
                nombre_producto: updatedProduct.nombre_producto,
                codigo_producto: updatedProduct.codigo || updatedProduct.codigo_producto,
                descripcion_producto: updatedProduct.descripcion_producto,
                precio_producto: updatedProduct.precio_producto,
                
                // Categorización
                genero_producto: updatedProduct.genero_producto,
                
                // Stock (BD usa stock_actual_producto, vista usa stock_producto)
                stock_producto: updatedProduct.stock_actual_producto || updatedProduct.stock_producto || 0,
                stock_actual_producto: updatedProduct.stock_actual_producto,
                stock_minimo_producto: updatedProduct.stock_minimo_producto,
                
                // Estado (BD usa 'estado', vista usa 'estado_producto')
                estado_producto: updatedProduct.estado === 'activo' ? '1' : '0',
                estado: updatedProduct.estado,
                status_producto: updatedProduct.status_producto,
                
                // Imagen
                imagen_producto: updatedProduct.imagen_producto,
                url_imagen_producto: updatedProduct.url_imagen_producto,
                
                // Fechas
                fecha_registro: updatedProduct.fecha_creacion_producto || updatedProduct.fecha_registro,
                fecha_creacion_producto: updatedProduct.fecha_creacion_producto,
                fecha_actualizacion_producto: updatedProduct.fecha_actualizacion_producto
            };

            // Agregar nombres de categoría y marca desde el estado
            if (updatedProduct.id_categoria && state.categories.length > 0) {
                const category = state.categories.find(c => c.id_categoria == updatedProduct.id_categoria);
                if (category) {
                    updatedProduct.categoria_nombre = category.nombre_categoria;
                }
            }

            if (updatedProduct.id_marca && state.brands.length > 0) {
                const brand = state.brands.find(b => b.id_marca == updatedProduct.id_marca);
                if (brand) {
                    updatedProduct.marca_nombre = brand.nombre_marca;
                }
            }

            console.log('📦 Producto normalizado:', updatedProduct);

            // Actualizar en el estado
            const index = state.productos.findIndex(p => p.id_producto == productId);
            if (index !== -1) {
                state.productos[index] = updatedProduct;
            }

            // Actualizar en la vista actual
            if (state.currentView === 'grid') {
                updateProductCardInPlace(productId, updatedProduct);
            } else {
                updateProductRowInPlace(productId, updatedProduct);
            }

            console.log('✅ Producto actualizado en tiempo real');

        } catch (error) {
            console.error('❌ Error al actualizar producto:', error);
            // Si falla, recargar toda la lista como fallback
            loadProducts();
        }
    }

    /**
     * Actualiza una tarjeta de producto en la vista grid
     */
    function updateProductCardInPlace(productId, product) {
        const card = document.querySelector(`.product-card[data-product-id="${productId}"]`);
        if (!card) return;

        // Crear nueva tarjeta
        const newCardHTML = `
            <div class="product-image-wrapper">
                <img class="product-image" 
                     src="${getProductImage(product.imagen_producto)}" 
                     alt="${escapeHtml(product.nombre_producto)}">
                <span class="product-card-badge ${product.estado_producto === '1' ? 'badge-success' : 'badge-danger'}">
                    ${product.estado_producto === '1' ? 'Activo' : 'Inactivo'}
                </span>
            </div>
            <div class="product-card-body">
                <h3 class="product-card-title">${escapeHtml(product.nombre_producto)}</h3>
                <p class="product-card-code">${escapeHtml(product.codigo_producto || 'N/A')}</p>
                <div class="product-card-info">
                    <span><i class="fas fa-tag"></i> ${escapeHtml(product.categoria_nombre || 'Sin categoría')}</span>
                    <span><i class="fas fa-copyright"></i> ${escapeHtml(product.marca_nombre || 'Sin marca')}</span>
                </div>
                <div class="product-card-price">
                    <span class="price">$${parseFloat(product.precio_producto).toFixed(2)}</span>
                    <span class="stock badge ${getStockBadgeClass(product.stock_producto)}">
                        <i class="fas fa-boxes"></i> ${product.stock_producto}
                    </span>
                </div>
                <div class="product-card-actions">
                    <button class="btn btn-menu" 
                            style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #007bff, #0056b3); color: #ffffff; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 1rem; box-shadow: 0 2px 8px rgba(0, 123, 255, 0.3); transition: all 0.3s ease;"
                            onmouseenter="this.style.background='linear-gradient(135deg, #0056b3, #003d82)'; this.style.transform='scale(1.1)'"
                            onmouseleave="this.style.background='linear-gradient(135deg, #007bff, #0056b3)'; this.style.transform='scale(1)'"
                            onclick="showActionMenu(${product.id_producto}, '${escapeHtml(product.nombre_producto)}', ${product.stock_producto}, '${product.estado}', event)">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                </div>
            </div>
        `;

        // Animación de actualización
        card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
        card.style.opacity = '0.5';
        card.style.transform = 'scale(0.95)';

        setTimeout(() => {
            card.innerHTML = newCardHTML;
            card.style.opacity = '1';
            card.style.transform = 'scale(1)';
            
            // Disparar evento para que el adapter global procese la imagen actualizada
            document.dispatchEvent(new CustomEvent('productsUpdated'));
        }, 300);
    }

    /**
     * Actualiza una fila de producto en la vista tabla
     */
    function updateProductRowInPlace(productId, product) {
        const row = document.querySelector(`tr[data-product-id="${productId}"]`);
        if (!row) return;

        // Crear nueva fila
        const newRowHTML = `
            <td>${product.id_producto}</td>
            <td>
                <img src="${getProductImage(product.imagen_producto)}" 
                     alt="${escapeHtml(product.nombre_producto)}"
                     class="img-thumbnail" 
                     style="width: 50px; height: 50px; object-fit: cover;">
            </td>
            <td>
                <strong>${escapeHtml(product.nombre_producto)}</strong>
                <br>
                <small class="text-muted">${escapeHtml(product.codigo_producto || 'N/A')}</small>
            </td>
            <td>${escapeHtml(product.categoria_nombre || 'Sin categoría')}</td>
            <td>${escapeHtml(product.marca_nombre || 'Sin marca')}</td>
            <td>${escapeHtml(product.genero_producto || 'N/A')}</td>
            <td>$${parseFloat(product.precio_producto).toFixed(2)}</td>
            <td>
                <span class="badge ${getStockBadgeClass(product.stock_producto)}">
                    ${product.stock_producto}
                </span>
            </td>
            <td>
                <span class="badge ${product.estado_producto === '1' ? 'bg-success' : 'bg-danger'}">
                    ${product.estado_producto === '1' ? 'Activo' : 'Inactivo'}
                </span>
            </td>
            <td>${formatDate(product.fecha_registro)}</td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="window.adminProductos.editProduct(${product.id_producto})" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-outline-warning" onclick="window.adminProductos.toggleStatus(${product.id_producto})" title="Cambiar estado">
                        <i class="fas fa-toggle-on"></i>
                    </button>
                    <button class="btn btn-outline-danger" onclick="window.adminProductos.deleteProduct(${product.id_producto})" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        `;

        // Animación de actualización
        row.style.transition = 'background-color 0.6s ease';
        row.style.backgroundColor = '#e7f3ff';

        setTimeout(() => {
            row.innerHTML = newRowHTML;
            setTimeout(() => {
                row.style.backgroundColor = '';
            }, 600);
        }, 100);
    }

    // ============================================================================
    // EXPORTAR API PÚBLICA
    // ============================================================================

    window.adminProductos = {
        init,
        loadProducts,
        refreshProduct,
        editProduct,
        deleteProduct,
        toggleStatus,
        goToPage
    };

    // Exponer funciones globales para compatibilidad con HTML onclick/oninput
    window.handleSearchInput = function() {
        const searchInput = document.getElementById('search-productos');
        if (searchInput) {
            clearTimeout(window.searchTimeout);
            window.searchTimeout = setTimeout(() => {
                state.currentPage = 1;
                loadProducts();
            }, 500);
        }
    };

    window.filterProducts = function() {
        state.currentPage = 1;
        loadProducts();
    };

    window.clearProductSearch = function() {
        const searchInput = document.getElementById('search-productos');
        if (searchInput) {
            searchInput.value = '';
            state.currentPage = 1;
            loadProducts();
        }
    };

    window.clearAllProductFilters = function() {
        document.getElementById('search-productos').value = '';
        document.getElementById('filter-category').value = '';
        document.getElementById('filter-marca').value = '';
        document.getElementById('filter-status').value = '';
        document.getElementById('filter-stock').value = '';
        state.currentPage = 1;
        loadProducts();
    };

    window.toggleProductoView = function(view) {
        state.currentView = view;
        renderProducts();
    };

    window.exportProducts = function() {
        console.log('Exportar productos...');
        alert('Función de exportación en desarrollo');
    };

    window.showStockReport = function() {
        console.log('Mostrar reporte de stock...');
        alert('Reporte de stock en desarrollo');
    };

    window.handleBulkProductAction = function(action) {
        console.log('Acción en lote:', action);
        alert('Acciones en lote en desarrollo');
    };

    window.goToFirstPageProducts = function() {
        goToPage(1);
    };

    window.previousPageProducts = function() {
        if (state.currentPage > 1) {
            goToPage(state.currentPage - 1);
        }
    };

    window.nextPageProducts = function() {
        if (state.currentPage < state.totalPages) {
            goToPage(state.currentPage + 1);
        }
    };

    window.goToLastPageProducts = function() {
        goToPage(state.totalPages);
    };

    // ============================================================================
    // AUTO-INICIALIZACIÓN
    // ============================================================================

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
// ============================================================================
// SISTEMA DE BOTONES FLOTANTES ANIMADOS - V2.0 (OPTIMIZADO)
// Mejoras:
// 1. Sombras (box-shadow) mejoradas para más profundidad.
// 2. Tracking de scroll ultra-fluido usando requestAnimationFrame (elimina delay/jank).
// 3. Animación de partículas robusta con 'transitionend' y fallback.
// ============================================================================

const FloatingMenu = (() => {
    let activeContainer = null;
    let activeProductId = null;
    let isAnimating = false;
    let isClosing = false;
    let floatingButtons = [];
    let centerButton = null;
    let lastClickTime = 0;
    
    // Bucle de animación para el tracking
    let currentRafId = null;
    let isTrackingDirty = false; // Flag para solicitar actualización de posición
    
    const clickDebounceDelay = 200;

    // Colores únicos para cada botón
    const buttonColors = [
        { bg: 'linear-gradient(135deg, #3b82f6, #2563eb)', hover: 'linear-gradient(135deg, #2563eb, #1d4ed8)', particle: '#3b82f6' }, // Azul
        { bg: 'linear-gradient(135deg, #10b981, #059669)', hover: 'linear-gradient(135deg, #059669, #047857)', particle: '#10b981' }, // Verde
        { bg: 'linear-gradient(135deg, #f59e0b, #d97706)', hover: 'linear-gradient(135deg, #d97706, #b45309)', particle: '#f59e0b' }, // Naranja
        { bg: 'linear-gradient(135deg, #8b5cf6, #7c3aed)', hover: 'linear-gradient(135deg, #7c3aed, #6d28d9)', particle: '#8b5cf6' }, // Violeta
        { bg: 'linear-gradient(135deg, #ef4444, #dc2626)', hover: 'linear-gradient(135deg, #dc2626, #b91c1c)', particle: '#ef4444' }  // Rojo
    ];

    function cleanupOrphanedContainers() {
        document.querySelectorAll('.animated-floating-container').forEach(c => c.remove());
        document.querySelectorAll('.floating-particle').forEach(p => p.remove());
    }

    /**
     * (MEJORADO) Crea partículas con limpieza robusta usando 'transitionend'
     */
    function createParticles(x, y, color) {
        const particleCount = 8; // Aumentado para más impacto
        for (let i = 0; i < particleCount; i++) {
            const particle = document.createElement('div');
            particle.className = 'floating-particle'; // Para limpieza
            particle.style.cssText = `
                position: fixed;
                left: ${x}px;
                top: ${y}px;
                width: 6px;
                height: 6px;
                background: ${color};
                border-radius: 50%;
                pointer-events: none;
                z-index: 10002;
                opacity: 1;
                box-shadow: 0 0 10px ${color}, 0 0 5px ${color}; /* Sombra más intensa */
                will-change: transform, opacity; /* Optimización */
            `;
            document.body.appendChild(particle);
            
            const angle = (Math.PI * 2 * i) / particleCount + (Math.random() - 0.5) * 0.5; // Ligera aleatoriedad
            const distance = 50 + Math.random() * 40; // Distancia aumentada
            const deltaX = Math.cos(angle) * distance;
            const deltaY = Math.sin(angle) * distance;
            
            // Usar 'transitionend' para limpieza robusta
            particle.addEventListener('transitionend', () => {
                particle.remove();
            }, { once: true });
            
            requestAnimationFrame(() => {
                particle.style.transition = 'all 0.6s cubic-bezier(0.17, 0.84, 0.44, 1)'; // Easing suave
                particle.style.transform = `translate(${deltaX}px, ${deltaY}px) scale(0)`;
                particle.style.opacity = '0';
            });
            
            // Fallback por si 'transitionend' no se dispara (ej. tab inactivo)
            setTimeout(() => {
                if (document.body.contains(particle)) {
                    particle.remove();
                }
            }, 700); // 600ms anim + 100ms buffer
        }
    }

    function show(productId, productName, stock, estado, event) {
        if (isClosing) return;
        const now = Date.now();
        if (now - lastClickTime < clickDebounceDelay) return;
        lastClickTime = now;
        
        if (activeContainer && activeProductId === productId) { 
            close(); 
            return; 
        }
        if (activeContainer && activeProductId !== productId) close();
        open(productId, productName, stock, estado, event);
    }

    function open(productId, productName, stock, estado, event) {
        cleanupOrphanedContainers();
        const triggerButton = event?.currentTarget;
        if (!triggerButton || !document.contains(triggerButton)) return;
        
        isAnimating = true;
        activeProductId = productId;
        createContainer(triggerButton, productId, productName, stock, estado);
    }

    function createContainer(triggerButton, productId, productName, stock, estado) {
        if (activeContainer) activeContainer.remove();
        
        activeContainer = document.createElement('div');
        activeContainer.className = 'animated-floating-container'; // Para limpieza
        activeContainer.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 10000;
        `;
        activeContainer.triggerButton = triggerButton;
        
        // Botón central
        centerButton = document.createElement('div');
        centerButton.style.cssText = `
            position: fixed;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            pointer-events: all;
            /* (MEJORA) Sombra más profunda */
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4), 0 2px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
            transform: scale(0) rotate(-180deg);
            opacity: 0;
            font-size: 1.125rem;
            z-index: 10001;
            will-change: transform, opacity, box-shadow;
        `;
        centerButton.innerHTML = '<i class="fas fa-ellipsis-v"></i>';
        centerButton.onclick = close;
        centerButton.onmouseenter = function() {
            if (!isClosing) {
                this.style.transform = 'scale(1.2) rotate(180deg)';
                this.style.background = 'linear-gradient(135deg, #ef4444, #dc2626)';
                /* (MEJORA) Sombra hover */
                this.style.boxShadow = '0 8px 28px rgba(239, 68, 68, 0.5), 0 3px 8px rgba(0, 0, 0, 0.1)';
            }
        };
        centerButton.onmouseleave = function() {
            if (!isClosing) {
                this.style.transform = 'scale(1) rotate(0deg)';
                this.style.background = 'linear-gradient(135deg, #007bff, #0056b3)';
                /* (MEJORA) Sombra base */
                this.style.boxShadow = '0 6px 20px rgba(0, 123, 255, 0.4), 0 2px 6px rgba(0, 0, 0, 0.1)';
            }
        };
        activeContainer.appendChild(centerButton);
        
        // Acciones con colores únicos
        const actions = [
            { icon: 'fa-eye', fn: () => window.location.href = 'product-details.php?id=' + productId },
            { icon: 'fa-edit', fn: () => window.adminProductos.editProduct(productId) },
            { icon: 'fa-boxes', fn: () => console.log('Stock') },
            { icon: estado === 'activo' ? 'fa-power-off' : 'fa-toggle-on', fn: () => window.adminProductos.toggleStatus(productId) },
            { icon: 'fa-trash', fn: () => window.adminProductos.deleteProduct(productId) }
        ];
        
        floatingButtons = [];
        actions.forEach((action, i) => {
            const colors = buttonColors[i];
            const btn = document.createElement('div');
            btn.style.cssText = `
                position: fixed;
                width: 56px;
                height: 56px;
                border-radius: 50%;
                background: ${colors.bg};
                color: #fff;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                pointer-events: all;
                /* (MEJORA) Sombra de botón más suave y profunda */
                box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15), 0 2px 6px rgba(0, 0, 0, 0.1);
                transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
                transform: scale(0) rotate(180deg);
                opacity: 0;
                font-size: 1.35rem;
                border: 2px solid rgba(255, 255, 255, 0.2);
                z-index: 10000;
                will-change: transform, opacity, box-shadow;
            `;
            btn.innerHTML = `<i class="fas ${action.icon}"></i>`;
            btn.dataset.color = colors.particle;
            btn.dataset.baseGradient = colors.bg;
            btn.dataset.hoverGradient = colors.hover;
            
            btn.onmouseenter = function() {
                if (!isClosing) {
                    this.style.transform = 'scale(1.25) rotate(0deg)';
                    this.style.background = this.dataset.hoverGradient;
                    /* (MEJORA) Sombra hover más pronunciada */
                    this.style.boxShadow = '0 8px 24px rgba(0, 0, 0, 0.25), 0 4px 10px rgba(0, 0, 0, 0.15)';
                }
            };
            btn.onmouseleave = function() {
                if (!isClosing) {
                    this.style.transform = 'scale(1) rotate(0deg)';
                    this.style.background = this.dataset.baseGradient;
                    /* (MEJORA) Sombra base */
                    this.style.boxShadow = '0 4px 16px rgba(0, 0, 0, 0.15), 0 2px 6px rgba(0, 0, 0, 0.1)';
                }
            };
            btn.onclick = () => { 
                close(); 
                setTimeout(action.fn, 250); 
            };
            activeContainer.appendChild(btn);
            floatingButtons.push(btn);
        });
        
        document.body.appendChild(activeContainer);
        updatePositions(); // Posición inicial
        setupListeners(); // Configurar listeners
        startAnimation(); // Animación de entrada
    }

    /**
     * (MEJORA) Actualiza la posición (se llama desde el RAF loop)
     */
    function updatePositions() {
        if (!activeContainer?.triggerButton || !document.contains(activeContainer.triggerButton)) {
            close(); // El botón original desapareció, cerrar menú
            return;
        }
        
        const rect = activeContainer.triggerButton.getBoundingClientRect();
        const cx = rect.left + rect.width / 2;
        const cy = rect.top + rect.height / 2;
        
        if (centerButton) {
            centerButton.style.left = `${cx - 24}px`;
            centerButton.style.top = `${cy - 24}px`;
        }
        
        const radius = 90;
        floatingButtons.forEach((btn, i) => {
            const angle = (i / floatingButtons.length) * 2 * Math.PI - Math.PI / 2;
            const x = cx + Math.cos(angle) * radius;
            const y = cy + Math.sin(angle) * radius;
            btn.style.left = `${x - 28}px`;
            btn.style.top = `${y - 28}px`;
        });
    }

    function startAnimation() {
        requestAnimationFrame(() => {
            if (centerButton) {
                centerButton.style.transform = 'scale(1) rotate(0deg)';
                centerButton.style.opacity = '1';
            }
        });
        
        floatingButtons.forEach((btn, i) => {
            setTimeout(() => {
                btn.style.transform = 'scale(1) rotate(0deg)';
                btn.style.opacity = '1';
            }, 40 + (i * 30));
        });
        
        setTimeout(() => {
            isAnimating = false;
        }, 40 + (floatingButtons.length * 30) + 60);
    }

    /**
     * (MEJORA) Bucle de tracking de posición con requestAnimationFrame
     */
    function trackingLoop() {
        if (!activeContainer) {
            currentRafId = null; // Detener bucle
            return;
        }
        
        if (isTrackingDirty) {
            updatePositions();
            isTrackingDirty = false; // Limpiar flag
        }
        
        currentRafId = requestAnimationFrame(trackingLoop); // Siguiente fotograma
    }

    function setupListeners() {
        const handleClick = (e) => {
            if (activeContainer && !activeContainer.contains(e.target) && !e.target.closest('.btn-menu')) {
                close();
            }
        };
        
        /**
         * (MEJORA) Handler de scroll/resize que solo levanta un flag.
         * El trabajo real lo hace el trackingLoop (RAF).
         */
        const handlePositionUpdate = () => {
            if (!isTrackingDirty && activeContainer && !isAnimating && !isClosing) {
                isTrackingDirty = true; // Solicitar actualización
            }
        };
        
        setTimeout(() => document.addEventListener('click', handleClick), 100);
        
        // Listeners pasivos para máximo rendimiento
        window.addEventListener('resize', handlePositionUpdate, { passive: true });
        window.addEventListener('scroll', handlePositionUpdate, { passive: true, capture: true });
        
        const scrollContainers = [
            document.querySelector('.data-table-wrapper'),
            document.querySelector('.admin-main'),
            document.body
        ];
        
        scrollContainers.forEach(c => {
            if (c) c.addEventListener('scroll', handlePositionUpdate, { passive: true });
        });
        
        // Iniciar el bucle de tracking
        if (!currentRafId) {
            trackingLoop();
        }
        
        // Función de limpieza
        activeContainer.cleanup = () => {
            if (currentRafId) {
                cancelAnimationFrame(currentRafId); // Detener el bucle RAF
                currentRafId = null;
            }
            document.removeEventListener('click', handleClick);
            window.removeEventListener('resize', handlePositionUpdate);
            window.removeEventListener('scroll', handlePositionUpdate, { capture: true });
            scrollContainers.forEach(c => {
                if (c) c.removeEventListener('scroll', handlePositionUpdate);
            });
        };
    }

    function close() {
        if (!activeContainer || isClosing) return;
        isClosing = true;
        
        if (activeContainer.cleanup) activeContainer.cleanup();
        
        // Generar partículas inmediatamente antes de cerrar
        floatingButtons.forEach((btn, i) => {
            if (btn && document.contains(btn)) {
                const rect = btn.getBoundingClientRect();
                const x = rect.left + rect.width / 2;
                const y = rect.top + rect.height / 2;
                
                // Partículas inmediatas
                setTimeout(() => {
                    createParticles(x, y, btn.dataset.color);
                }, i * 25);
                
                // Animación de cierre
                setTimeout(() => {
                    btn.style.transform = 'scale(0) rotate(-180deg)';
                    btn.style.opacity = '0';
                }, i * 25);
            }
        });
        
        setTimeout(() => {
            if (centerButton && document.contains(centerButton)) {
                centerButton.style.transform = 'scale(0) rotate(180deg)';
                centerButton.style.opacity = '0';
            }
        }, floatingButtons.length * 25 + 60);
        
        // Tiempo total para limpiar todo
        setTimeout(() => {
            if (activeContainer) activeContainer.remove();
            activeContainer = null;
            centerButton = null;
            floatingButtons = [];
            activeProductId = null;
            isAnimating = false;
            isClosing = false;
            isTrackingDirty = false;
            // El bucle RAF se detendrá solo en la siguiente iteración
        }, floatingButtons.length * 25 + 300);
    }

    return { show, close, cleanupOrphanedContainers };
})();

// Asignación a la API pública de la ventana
window.showActionMenu = FloatingMenu.show;
window.closeFloatingActions = FloatingMenu.close;
window.cleanupOrphanedContainers = FloatingMenu.cleanupOrphanedContainers;