
<div class="admin-module admin-products-module">
    <!-- Header del módulo -->
    <div class="module-header">
        <div class="module-title">
            <div class="module-icon">
                <i class="fas fa-tshirt"></i>
            </div>
            <div class="module-info">
                <h2 class="module-name">Gestión de Productos</h2>
                <p class="module-description">Administra el catálogo de productos de la tienda</p>
            </div>
        </div>
        <div class="module-actions">
            <button class="btn-modern btn-primary" onclick="closeStockBubble(); window.showCreateProductModal();" style="color: white !important;">
                <i class="fas fa-plus" style="color: white !important;"></i>
                <span style="color: white !important;">Nuevo <span class="btn-text-mobile-hide">Producto</span></span>
            </button>
            <button class="btn-modern btn-secondary" onclick="exportProducts()" style="color: white !important;">
                <i class="fas fa-download" style="color: white !important;"></i>
                <span style="color: white !important;">Exportar <span class="btn-text-mobile-hide">Excel</span></span>
            </button>
            <button class="btn-modern btn-info" onclick="showStockReport()" style="color: white !important;">
                <i class="fas fa-chart-bar" style="color: white !important;"></i>
                <span style="color: white !important;">Reporte <span class="btn-text-mobile-hide">Stock</span></span>
            </button>
        </div>
    </div>

    <!-- Filtros y búsqueda (sidebar responsive) -->
    <div class="module-filters modern-sidebar">
        <div class="search-container">
            <div class="search-input-group">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="search-productos" class="search-input" 
                       placeholder="Buscar productos por nombre..." oninput="handleSearchInput()">
                <button class="search-clear" onclick="clearProductSearch()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        
        <div class="filters-grid">
            <div class="filter-group">
                <label class="filter-label">Categoría</label>
                <select id="filter-category" class="filter-select" onchange="filterProducts()">
                    <option value="">Todas las categorías</option>
                    <!-- Se cargan dinámicamente -->
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label">Marca</label>
                <select id="filter-marca" class="filter-select" onchange="filterProducts()">
                    <option value="">Todas las marcas</option>
                    <!-- Se cargan dinámicamente -->
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label">Estado</label>
                <select id="filter-status" class="filter-select" onchange="filterProducts()">
                    <option value="">Todos los estados</option>
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label">Stock</label>
                <select id="filter-stock" class="filter-select" onchange="filterProducts()">
                    <option value="">Todo el stock</option>
                    <option value="agotado">Agotado</option>
                    <option value="bajo">Stock bajo</option>
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
                <button class="btn-modern btn-outline" onclick="clearAllProductFilters()">
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
                        de <span id="total-products">0</span> productos
                    </span>
                </div>
                <div class="table-actions">
                    <!-- Botones de vista: SOLO TABLA en PC, SOLO GRID en móvil -->
                    <div class="view-options">
                        <!-- Botón TABLA: Solo visible en DESKTOP -->
                        <button class="view-btn active desktop-only" data-view="table" onclick="toggleProductoView('table')">
                            <i class="fas fa-table"></i>
                        </button>
                        <!-- Botón GRID: Solo visible en MÓVIL -->
                        <button class="view-btn mobile-only" data-view="grid" onclick="toggleProductoView('grid')">
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
                                <span>Producto</span>
                            </th>
                            <th class="sortable" data-sort="categoria" data-type="text">
                                <span>Categoría</span>
                            </th>
                            <th class="sortable" data-sort="marca" data-type="text">
                                <span>Marca</span>
                            </th>
                            <th class="sortable" data-sort="genero" data-type="text">
                                <span>Género</span>
                            </th>
                            <th class="sortable" data-sort="precio" data-type="number">
                                <span>Precio</span>
                            </th>
                            <th class="sortable" data-sort="stock" data-type="stock">
                                <span>Stock</span>
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
                    <tbody id="productos-table-body" class="table-body">
                        <tr class="loading-row">
                            <td colspan="11" class="loading-cell">
                                <div class="loading-content">
                                    <div class="spinner"></div>
                                    <span>Cargando productos...</span>
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

</div> 

<!-- Botón flotante de filtros móvil -->
<button class="btn-mobile-filters" id="btnMobileFilters" aria-label="Abrir filtros">
    <i class="fa fa-filter"></i>
</button>

<script src="public/assets/js/admin/admin_productos.js?v=1.0"></script>
