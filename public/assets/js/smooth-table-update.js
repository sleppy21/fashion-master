/**
 * SISTEMA DE ACTUALIZACIÓN SUAVE DE TABLA/GRID
 * Actualización en tiempo real sin glitches ni bugs visuales
 */

// ⚠️ PREVENIR REDECLARACIÓN
if (typeof SmoothTableUpdater === 'undefined') {
    console.log('✅ Declarando SmoothTableUpdater por primera vez');

class SmoothTableUpdater {
    constructor() {
        this.isUpdating = false;
        this.updateQueue = [];
        this.animationDuration = 300; // ms
    }

    /**
     * Actualizar un solo producto en la tabla/grid sin recargar todo
     */
    async updateSingleProduct(productId, updatedData = null) {

        
        try {
            // Si no se proporciona data, obtenerla del servidor
            if (!updatedData) {
                const response = await fetch(`${window.CONFIG.apiUrl}?action=get&id=${productId}`);
                const result = await response.json();
                
                if (!result.success) {
                    throw new Error('Error al obtener datos del producto');
                }
                
                updatedData = result.product;
            }
            
            // Detectar vista actual
            const currentView = this.getCurrentView();
            
            if (currentView === 'grid') {
                await this.updateProductInGrid(productId, updatedData);
            } else {
                await this.updateProductInTable(productId, updatedData);
            }
            
            
        } catch (error) {
            console.error('❌ Error actualizando producto:', error);
            // Fallback: recargar toda la tabla
            if (typeof window.loadProducts === 'function') {
                window.loadProducts();
            }
        }
    }

    /**
     * Actualizar producto en la vista tabla
     */
    async updateProductInTable(productId, productData) {

        
        const row = document.querySelector(`#productos-table-body tr[data-product-id="${productId}"]`);
        
        if (!row) {

            const allRows = document.querySelectorAll('#productos-table-body tr');
            allRows.forEach((r, idx) => {
            });
            
            if (typeof window.loadProducts === 'function') {
                window.loadProducts();
            }
            return;
        }
        
        
        // Crear nueva fila HTML
        const newRowHTML = this.createTableRow(productData);
        
        const tempContainer = document.createElement('tbody');
        tempContainer.innerHTML = newRowHTML;
        const newRow = tempContainer.firstElementChild;
        
        
        // Reemplazar contenido directamente sin animaciones
        row.innerHTML = newRow.innerHTML;
        row.setAttribute('data-product-id', productId);
        
        
        // Efecto de destaque suave
        this.highlightRow(row);
    }

    /**
     * Actualizar producto en la vista grid
     */
    async updateProductInGrid(productId, productData) {
        const card = document.querySelector(`.product-card[data-product-id="${productId}"]`);
        
        if (!card) {
            if (typeof window.loadProducts === 'function') {
                window.loadProducts();
            }
            return;
        }
        
        // Crear nueva card HTML
        const newCardHTML = this.createGridCard(productData);
        const tempContainer = document.createElement('div');
        tempContainer.innerHTML = newCardHTML;
        const newCard = tempContainer.firstElementChild;
        
        // Reemplazar contenido directamente sin animaciones
        card.innerHTML = newCard.innerHTML;
        card.className = newCard.className;
        card.setAttribute('data-product-id', productId);
        
        // Efecto de destaque suave
        this.highlightCard(card);
    }

    /**
     * Agregar nuevo producto a la tabla/grid
     */
    async addNewProduct(productData) {
        const currentView = this.getCurrentView();
        
        if (currentView === 'grid') {
            await this.addProductToGrid(productData);
        } else {
            await this.addProductToTable(productData);
        }
    }

    /**
     * Agregar producto a la tabla
     */
    async addProductToTable(productData) {
        const tbody = document.getElementById('productos-table-body');
        
        if (!tbody) return;
        
        const newRowHTML = this.createTableRow(productData);
        const tempContainer = document.createElement('tbody');
        tempContainer.innerHTML = newRowHTML;
        const newRow = tempContainer.firstElementChild;
        
        // Insertar al inicio con animación
        newRow.style.opacity = '0';
        newRow.style.transform = 'translateY(-20px)';
        newRow.style.transition = `all ${this.animationDuration}ms ease-out`;
        
        tbody.insertBefore(newRow, tbody.firstChild);
        
        requestAnimationFrame(() => {
            newRow.style.opacity = '1';
            newRow.style.transform = 'translateY(0)';
        });
        
        // Efecto de destaque
        setTimeout(() => {
            this.highlightRow(newRow);
            newRow.style.transition = '';
            newRow.style.transform = '';
        }, this.animationDuration);
    }

    /**
     * Agregar producto al grid
     */
    async addProductToGrid(productData) {
        const grid = document.querySelector('.products-grid');
        
        if (!grid) return;
        
        const newCardHTML = this.createGridCard(productData);
        const tempContainer = document.createElement('div');
        tempContainer.innerHTML = newCardHTML;
        const newCard = tempContainer.firstElementChild;
        
        // Insertar al inicio con animación
        newCard.style.opacity = '0';
        newCard.style.transform = 'scale(0.8)';
        newCard.style.transition = `all ${this.animationDuration}ms ease-out`;
        
        grid.insertBefore(newCard, grid.firstChild);
        
        requestAnimationFrame(() => {
            newCard.style.opacity = '1';
            newCard.style.transform = 'scale(1)';
        });
        
        // Efecto de destaque
        setTimeout(() => {
            this.highlightCard(newCard);
            newCard.style.transition = '';
            newCard.style.transform = '';
        }, this.animationDuration);
    }

    /**
     * Eliminar producto de la tabla/grid
     */
    async removeProduct(productId) {
        const currentView = this.getCurrentView();
        const element = currentView === 'grid' 
            ? document.querySelector(`.product-card[data-product-id="${productId}"]`)
            : document.querySelector(`#productos-table-body tr[data-product-id="${productId}"]`);
        
        if (!element) return;
        
        // Animación de salida
        element.style.transition = `all ${this.animationDuration}ms ease-out`;
        element.style.opacity = '0';
        element.style.transform = currentView === 'grid' ? 'scale(0.8)' : 'translateX(-100%)';
        
        await this.wait(this.animationDuration);
        
        element.remove();
    }

    /**
     * Crear HTML de fila de tabla
     * IMPORTANTE: Este HTML debe coincidir EXACTAMENTE con el generado en displayProducts()
     */
    createTableRow(producto) {
        // Calcular clase de stock
        const stock = parseInt(producto.stock_actual_producto) || 0;
        let stockClass = 'stock-normal';
        let estadoStock = 'En stock';
        if (stock === 0) {
            stockClass = 'stock-agotado';
            estadoStock = 'Agotado';
        } else if (stock < 20) {
            stockClass = 'stock-bajo';
            estadoStock = 'Stock bajo';
        }
        
        // URL de imagen con AppConfig o fallback
        const getProductImageUrl = (producto, forceCacheBust = false) => {
            const timestamp = forceCacheBust ? '?t=' + new Date().getTime() : '';
            if (producto.url_imagen_producto && producto.url_imagen_producto !== 'NULL') {
                return producto.url_imagen_producto + timestamp;
            } else if (producto.imagen_producto && producto.imagen_producto !== 'NULL') {
                return '/fashion-master/public/assets/img/products/' + producto.imagen_producto + timestamp;
            }
            return '/fashion-master/public/assets/img/default-product.jpg';
        };
        
        const imageUrl = getProductImageUrl(producto, true);
        const fallbackImage = window.AppConfig 
            ? window.AppConfig.getImageUrl('default-product.jpg') 
            : '/fashion-master/public/assets/img/default-product.jpg';
        
        // Formatear precio
        const precioFormato = producto.precio_formato || '$' + parseFloat(producto.precio_producto || 0).toFixed(2);
        
        // Fecha
        const fecha = producto.fecha_creacion_formato || producto.fecha_creacion_producto || 'N/A';
        
        // HTML EXACTO como en displayProducts()
        return `
        <tr oncontextmenu="return false;" ondblclick="editProduct(${producto.id_producto})" style="cursor: pointer;" data-product-id="${producto.id_producto}">
            <td>${producto.id_producto}</td>
            <td onclick="event.stopPropagation();" ondblclick="event.stopPropagation(); showImageFullSize('${imageUrl}', '${(producto.nombre_producto || '').replace(/'/g, "\\'")}')"; style="cursor: zoom-in;">
                <div class="product-image-small">
                    <img src="${imageUrl}" 
                         alt="Producto" 
                         onerror="this.src='${fallbackImage}'; this.onerror=null;">
                </div>
            </td>
            <td>
                <div class="product-info">
                    <strong>${producto.nombre_producto || 'Sin nombre'}</strong>
                </div>
            </td>
            <td>
                <code>${producto.codigo || 'N/A'}</code>
            </td>
            <td>
                ${producto.nombre_categoria || producto.categoria_nombre || 'Sin categoría'}
            </td>
            <td>
                ${producto.nombre_marca || producto.marca_nombre || 'Sin marca'}
            </td>
            <td>
                <div class="price-info">
                    <strong>${precioFormato}</strong>
                </div>
            </td>
            <td>
                <div class="stock-info">
                    <span class="stock-number ${stockClass}">${stock}</span>
                    <small class="stock-status">${estadoStock}</small>
                </div>
            </td>
            <td>
                <span class="status-badge ${producto.estado === 'activo' ? 'status-active' : 'status-inactive'}">
                    ${producto.estado === 'activo' ? 'Activo' : 'Inactivo'}
                </span>
            </td>
            <td>${fecha}</td>
            <td>
                <div class="action-buttons">
                    <button class="btn-action btn-menu" onclick="event.stopPropagation(); showActionMenu(${producto.id_producto}, '${(producto.nombre_producto || '').replace(/'/g, "\\'")}', ${stock}, '${producto.estado}', event)" title="Acciones">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                </div>
            </td>
        </tr>`;
    }

    /**
     * Crear HTML de card del grid
     */
    /**
     * Crear HTML de card del grid
     * IMPORTANTE: Este HTML debe coincidir EXACTAMENTE con el generado en displayProductsGrid()
     */
    createGridCard(producto) {
        const stock = parseInt(producto.stock_actual_producto) || 0;
        const stockMinimo = parseInt(producto.stock_minimo_producto);
        const stockMaximo = parseInt(producto.stock_maximo_producto);
        
        let stockClass = 'stock-normal'; // Por defecto verde
        let stockText = 'Normal';
        
        if (stock === 0) {
            stockClass = 'stock-agotado';
            stockText = 'Agotado';
        } else if (stockMinimo && stock <= stockMinimo) {
            stockClass = 'stock-bajo';
            stockText = 'Bajo';
        } else {
            stockClass = 'stock-normal'; // Verde para stock > stock_minimo
            stockText = 'Normal';
        }
        
        const precio = parseFloat(producto.precio_producto || 0).toLocaleString('es-CO');
        const descuentoPrecio = producto.precio_descuento_producto 
            ? `<span class="discount-price">$${parseFloat(producto.precio_descuento_producto).toLocaleString('es-CO')}</span>` 
            : '';
        
        // Generar HTML de imagen usando la misma función que displayProductsGrid
        const imageUrl = typeof window.getProductImageUrl === 'function' 
            ? window.getProductImageUrl(producto) 
            : (producto.url_imagen_producto || '');
        const hasImage = imageUrl && !imageUrl.includes('default-product.jpg');
        
        const imageHTML = `
            <div class="product-card-image-mobile ${hasImage ? '' : 'no-image'}">
                ${hasImage 
                    ? `<img src="${imageUrl}" alt="${producto.nombre_producto || 'Producto'}" onerror="this.parentElement.classList.add('no-image'); this.style.display='none'; this.parentElement.innerHTML='<i class=\\'fas fa-image\\'></i>';">` 
                    : '<i class="fas fa-image"></i>'}
            </div>
        `;
        
        // HTML EXACTO como en displayProductsGrid()
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
                    
                    <div class="product-card-stock">
                        <span class="${stockClass}">
                            <i class="fas fa-box"></i> ${stock} unidades (${stockText})
                        </span>
                    </div>
                    
                    <div class="product-card-price">
                        <i class="fas fa-dollar-sign"></i>
                        $${precio}
                        ${descuentoPrecio}
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
    }

    /**
     * Efecto de destaque en fila (desactivado)
     */
    highlightRow(row) {
        // Sin efectos visuales
    }

    /**
     * Efecto de destaque en card (desactivado)
     */
    highlightCard(card) {
        // Sin efectos visuales
    }

    /**
     * Obtener vista actual
     */
    getCurrentView() {
        const activeBtn = document.querySelector('.view-btn.active');
        return activeBtn ? activeBtn.dataset.view : 'table';
    }

    /**
     * Helper: Esperar tiempo específico
     */
    wait(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
}

// ===== EXPORTAR CLASE AL SCOPE GLOBAL =====
window.SmoothTableUpdater = SmoothTableUpdater;
console.log('✅ SmoothTableUpdater exportado al scope global');

// Crear instancia global
window.smoothTableUpdater = new SmoothTableUpdater();
console.log('✅ Instancia global smoothTableUpdater creada');

// Exportar para uso en otros módulos
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SmoothTableUpdater;
}

} else {
    console.warn('⚠️ SmoothTableUpdater ya existe - saltando redeclaración');
    // Si ya existe, solo asegurar que la instancia global esté creada
    if (!window.smoothTableUpdater) {
        window.smoothTableUpdater = new SmoothTableUpdater();
    }
}

