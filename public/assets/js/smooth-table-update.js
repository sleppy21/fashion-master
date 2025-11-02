
function calcularEstadoStock(item) {
    // ðŸ” DETECTAR AUTOMÃTICAMENTE si es PRODUCTO o CATEGORÃA
    const esProducto = item.stock_actual_producto !== undefined || item.id_producto !== undefined;
    const esCategoria = item.stock_actual_categoria !== undefined || item.id_categoria !== undefined;
    
    // Obtener valores segÃºn el tipo de objeto
    let stockActual, stockMinimo;
    
    if (esProducto) {
        stockActual = parseInt(item.stock_actual_producto) || 0;
        stockMinimo = item.stock_minimo_producto ? parseInt(item.stock_minimo_producto) : null;
    } else if (esCategoria) {
        stockActual = parseInt(item.stock_actual_categoria) || 0;
        stockMinimo = item.stock_minimo_categoria ? parseInt(item.stock_minimo_categoria) : null;
    } else {
        // Fallback genÃ©rico
        stockActual = parseInt(item.stock_actual) || 0;
        stockMinimo = item.stock_minimo ? parseInt(item.stock_minimo) : null;
    }
    
    // Prioridad 1: Stock en 0 = Agotado (ROJO)
    if (stockActual === 0) {
        return {
            clase: 'stock-agotado',
            texto: 'Agotado',
            textoTabla: 'Agotado'
        };
    }
    
    // Prioridad 2: Stock <= stock_minimo (solo si stock_minimo existe y es > 0 en la BD)
    if (stockMinimo !== null && stockMinimo > 0 && stockActual <= stockMinimo) {
        return {
            clase: 'stock-bajo',
            texto: 'Bajo',
            textoTabla: 'Stock bajo'
        };
    }
    
    // Prioridad 3: Stock normal (VERDE)
    return {
        clase: 'stock-normal',
        texto: 'Normal',
        textoTabla: 'Stock normal'
    };
}

// âœ… EXPORTAR AL SCOPE GLOBAL
window.calcularEstadoStock = calcularEstadoStock;

/**
 * ðŸŽ¨ FUNCIONES PARA GÃ‰NERO
 */
function getGeneroLabel(genero) {
    const labels = {
        'M': 'Masculino',
        'F': 'Femenino',
        'Unisex': 'Unisex',
        'Kids': 'NiÃ±os'
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

// âœ… EXPORTAR AL SCOPE GLOBAL
window.getGeneroLabel = getGeneroLabel;
window.getGeneroBadgeClass = getGeneroBadgeClass;

// âš ï¸ PREVENIR REDECLARACIÃ“N
if (typeof SmoothTableUpdater === 'undefined') {

class SmoothTableUpdater {
    constructor() {
        this.isUpdating = false;
        this.updateQueue = [];
        this.animationDuration = 200; // ms - animaciÃ³n suave pero sin fondos
        this.cache = new Map(); // Cache de elementos DOM
        this.dataCache = new Map(); // Cache de datos para comparaciÃ³n
        this.observer = null; // Intersection Observer para lazy updates
        this.rafId = null; // RequestAnimationFrame ID para cancelar animaciones
        
        // Mapeo de campos a selectores CSS (Vista Tabla)
        this.fieldSelectorsTable = {
            imagen: 'td:nth-child(2) img',
            nombre: 'td:nth-child(3) strong',
            categoria: 'td:nth-child(4)',
            marca: 'td:nth-child(5)',
            genero: 'td:nth-child(6) .genero-badge',
            precio: 'td:nth-child(7) strong',
            stock: 'td:nth-child(8) .stock-number',
            stock_status: 'td:nth-child(8) .stock-status',
            stock_class: 'td:nth-child(8) .stock-number',
            estado: 'td:nth-child(9) .status-badge',
            fecha: 'td:nth-child(10)'
        };
        
        // Mapeo de campos a selectores CSS (Vista Grid)
        this.fieldSelectorsGrid = {
            imagen: '.product-card-image-mobile img',
            nombre: '.product-card-title',
            categoria: '.product-card-category',
            marca: '.product-card-brand',
            genero: '.product-card-genero .genero-badge',
            precio: '.product-card-price',
            stock: '.product-card-stock span',
            estado: '.product-card-status'
        };
    }

    /**
     * ðŸš€ NUEVA FUNCIÃ“N: Actualizar SOLO campos especÃ­ficos que cambiaron
     * @param {number|object} productId - ID del producto o datos completos
     * @param {object} updatedData - Datos actualizados
     * @param {array} changedFields - Array de campos que cambiaron (opcional, se detecta automÃ¡ticamente)
     */
    async updateSingleProduct(productId, updatedData = null, changedFields = null) {
        try {
            
            // Soporte para pasar el objeto completo como primer parÃ¡metro
            if (typeof productId === 'object' && productId !== null) {
                updatedData = productId;
                productId = updatedData.id_producto || updatedData.id;
            }

            // Validar que tengamos los datos necesarios
            if (!updatedData) {
                const response = await fetch(`${window.CONFIG.apiUrl}?action=get&id=${productId}`);
                const result = await response.json();
                
                if (!result.success) {
                    throw new Error('Error al obtener datos del producto');
                }
                
                updatedData = result.product;
            }

            // Validar ID
            if (!productId || productId <= 0) {
                throw new Error('ID de producto invÃ¡lido');
            }
            
            // ðŸ†• LIMPIAR CACHE DEL ELEMENTO DOM ANTES DE ACTUALIZAR
            // Esto fuerza a que se vuelva a buscar en el DOM con datos frescos
            this.cache.delete(`row-${productId}`);
            this.cache.delete(`card-${productId}`);
            
            // Detectar campos que cambiaron si no se especificaron
            if (!changedFields) {
                changedFields = this.detectChangedFields(productId, updatedData);
                
                // ðŸŽ¯ PARCHE: Si viene desde EDICIÃ“N y solo detectÃ³ estado/stock,
                // forzar actualizaciÃ³n de TODOS los campos para evitar problemas de cachÃ©
                // NOTA: NO incluir 'fecha' porque fecha_creacion nunca cambia
                if (changedFields && changedFields.length <= 2 && 
                    updatedData.nombre_producto && updatedData.nombre_marca) {
                    changedFields = ['imagen', 'nombre', 'categoria', 'marca', 'genero', 'precio', 'stock', 'estado'];
                }
            }
            
            // ðŸ†• FORZAR ACTUALIZACIÃ“N DE TODOS LOS CAMPOS AL EDITAR DESDE MODAL
            // Si los datos vienen completos (con nombre_producto, etc.), actualizar TODO
            if (!changedFields && updatedData.nombre_producto) {
                changedFields = ['imagen', 'nombre', 'categoria', 'marca', 'genero', 'precio', 'stock', 'estado'];
            }
            
            // Si no hay cambios, salir
            if (!changedFields || changedFields.length === 0) {
                return;
            }
            
            
            // Detectar vista actual
            const currentView = this.getCurrentView();
            
            // Actualizar SOLO los campos especÃ­ficos (SIN await)
            if (currentView === 'grid') {
                this.updateFieldsInGrid(productId, updatedData, changedFields);
            } else {
                this.updateFieldsInTable(productId, updatedData, changedFields);
            }
            
            // âœ… SOBRESCRIBIR COMPLETAMENTE datos en cache (no mergear)
            // Esto asegura que la prÃ³xima comparaciÃ³n use los datos mÃ¡s recientes
            this.dataCache.set(`data-${productId}`, { ...updatedData });
          
            
        } catch (error) {
            // Fallback mejorado: recargar solo si es crÃ­tico
            if (error.message.includes('invÃ¡lido')) {
                if (typeof window.loadProducts === 'function') {
                    window.loadProducts();
                }
            }
        }
    }

    /**
     * ðŸŽ¯ Actualizar mÃºltiples productos con smooth transition (para filtros/bÃºsqueda)
     */
    async updateMultipleProducts(newProductsList) {
        
        try {
            // Obtener productos actuales en la tabla/grid
            const currentView = this.getCurrentView();
            const currentProductIds = this.getCurrentProductIds(currentView);
            const newProductIds = newProductsList.map(p => p.id_producto);
                        
            // 1. Ocultar productos que ya no estÃ¡n en la lista
            const productsToHide = currentProductIds.filter(id => !newProductIds.includes(id));
            for (const productId of productsToHide) {
                await this.hideProduct(productId, currentView);
            }
            
            // 2. Actualizar o mostrar productos existentes/nuevos
            for (const product of newProductsList) {
                if (currentProductIds.includes(product.id_producto)) {
                    // Actualizar producto existente
                    await this.updateSingleProduct(product.id_producto, product);
                } else {
                    // Mostrar nuevo producto (si estaba oculto) o agregarlo
                    await this.showProduct(product, currentView);
                }
            }
            
            
        } catch (error) {
            throw error;
        }
    }

    /**
     * ðŸ” Detectar quÃ© campos cambiaron comparando con cache
     */
    detectChangedFields(productId, newData) {
        const cachedData = this.dataCache.get(`data-${productId}`);
        
        if (!cachedData) {
            // Primera vez: Guardar datos completos en cache Y actualizar TODOS los campos
            this.dataCache.set(`data-${productId}`, { ...newData });
            // âœ… Retornar TODOS los campos principales para actualizar en primera carga
            // NOTA: NO incluir 'fecha' porque fecha_creacion nunca cambia y puede causar problemas
            return ['imagen', 'nombre', 'categoria', 'marca', 'genero', 'precio', 'stock', 'estado'];
        }
        
        const changed = [];

        
        // Comparar campo por campo
        if (cachedData.url_imagen_producto !== newData.url_imagen_producto || 
            cachedData.imagen_producto !== newData.imagen_producto) {
            changed.push('imagen');
        }
        
        if (cachedData.nombre_producto !== newData.nombre_producto) {
            changed.push('nombre');
        }
        
        if (cachedData.nombre_categoria !== newData.nombre_categoria) {
            changed.push('categoria');
        }
        
        if (cachedData.nombre_marca !== newData.nombre_marca) {
            changed.push('marca');
        }
        
        if (cachedData.genero_producto !== newData.genero_producto) {
            changed.push('genero');
        }
        
        if (parseFloat(cachedData.precio_producto) !== parseFloat(newData.precio_producto)) {
            changed.push('precio');
        }
        
        if (parseInt(cachedData.stock_actual_producto) !== parseInt(newData.stock_actual_producto)) {
            changed.push('stock');
        }
        
        if (String(cachedData.estado).toLowerCase() !== String(newData.estado).toLowerCase()) {
            changed.push('estado');
        }
        
        return changed.length > 0 ? changed : null;
    }

    /**
     * ðŸŽ¯ Actualizar SOLO campos especÃ­ficos en la vista TABLA
     */
    updateFieldsInTable(productId, productData, changedFields) {
        
        // Buscar fila en cache o DOM
        let row = this.cache.get(`row-${productId}`);
        
        if (!row || !document.contains(row)) {
            row = document.querySelector(`#productos-table-body tr[data-product-id="${productId}"]`);

            
            this.cache.set(`row-${productId}`, row);
        }
        
        // Actualizar cada campo que cambiÃ³ (SIN await - paralelo)
        for (const field of changedFields) {
            this.updateSingleField(row, field, productData, 'table');
        }
    }

    /**
     * ðŸŽ¯ Actualizar SOLO campos especÃ­ficos en la vista GRID
     */
    updateFieldsInGrid(productId, productData, changedFields) {
        // Buscar card en cache o DOM
        let card = this.cache.get(`card-${productId}`);
        
        if (!card || !document.contains(card)) {
            card = document.querySelector(`.product-card[data-product-id="${productId}"]`);
            
            this.cache.set(`card-${productId}`, card);
        }
        
        // Actualizar cada campo que cambiÃ³ (SIN await - paralelo)
        for (const field of changedFields) {
            this.updateSingleField(card, field, productData, 'grid');
        }
    }

    /**
     * âš¡ Actualizar UN SOLO CAMPO - Solo animaciÃ³n de movimiento (sin cambios de color)
     */
    updateSingleField(container, field, productData, viewType) {
        const selectors = viewType === 'table' ? this.fieldSelectorsTable : this.fieldSelectorsGrid;
        const selector = selectors[field];
        const element = container.querySelector(selector);


        // Obtener nuevo valor
        const newValue = this.getFieldValue(field, productData);
        const currentValue = this.getCurrentFieldValue(element, field);


        // Actualizar contenido INMEDIATAMENTE
        this.setFieldValue(element, field, productData, newValue);

        // AnimaciÃ³n sutil NO bloqueante (solo visual)
        element.style.transition = `transform 120ms cubic-bezier(0.4, 0, 0.2, 1)`;
        if (field === 'estado') {
            element.style.transform = 'scale(1.05)';
        } else if (field === 'stock') {
            element.style.transform = 'scale(1.03)';
        } else {
            element.style.transform = 'scale(1.02)';
        }

        // Restaurar despuÃ©s de la animaciÃ³n (no await)
        setTimeout(() => {
            try {
                element.style.transform = 'scale(1)';
                setTimeout(() => { element.style.transition = ''; }, 120);
            } catch (e) {
                // elemento pudo haber sido removido
            }
        }, 80);

        // Log breve
    }

    /**
     * ðŸ“ Obtener valor de un campo desde los datos del producto
     */
    getFieldValue(field, productData) {
        switch (field) {
            case 'nombre':
                return productData.nombre_producto || 'Sin nombre';
            case 'categoria':
                return productData.nombre_categoria || productData.categoria_nombre || 'Sin categorÃ­a';
            case 'marca':
                return productData.nombre_marca || productData.marca_nombre || 'Sin marca';
            case 'genero':
                return getGeneroLabel(productData.genero_producto);
            case 'precio':
                return productData.precio_formato || '$' + parseFloat(productData.precio_producto || 0).toFixed(2);
            case 'stock':
                return String(parseInt(productData.stock_actual_producto) || 0);
            case 'estado':
                return productData.estado === 'activo' ? 'Activo' : 'Inactivo';
            case 'imagen':
                return this.getProductImageUrl(productData, true);
            case 'fecha':
                // Extraer solo la fecha (YYYY-MM-DD) sin la hora
                const fecha = productData.fecha_creacion_producto || 
                             productData.fecha_actualizacion_producto || 
                             productData.fecha || '';
                const fechaSplit = fecha ? fecha.split(' ')[0] : '-';
                return fechaSplit;
            default:
                return '';
        }
    }

    /**
     * ðŸ“– Obtener valor actual de un campo desde el DOM
     */
    getCurrentFieldValue(element, field) {
        if (field === 'imagen') {
            return element.src || '';
        } else if (field === 'estado') {
            return element.textContent.trim();
        } else {
            return element.textContent.trim();
        }
    }

    /**
     * ðŸ–Šï¸ Establecer nuevo valor en un campo del DOM
     */
    setFieldValue(element, field, productData, newValue) {
        
        if (field === 'imagen') {
            element.src = newValue;
        } else if (field === 'genero') {
            element.textContent = newValue;
            // Actualizar clase de color
            const generoClass = getGeneroBadgeClass(productData.genero_producto);
            element.className = 'genero-badge ' + generoClass;
        } else if (field === 'estado') {
            const isActive = productData.estado === 'activo';
            element.textContent = newValue;
            
            // Detectar si es GRID o TABLA y aplicar clases correctas
            if (element.classList.contains('product-card-status')) {
                // Es GRID
                element.className = 'product-card-status ' + (isActive ? 'active' : 'inactive');
            } else {
                // Es TABLA
                element.className = 'status-badge ' + (isActive ? 'status-active' : 'status-inactive');
            }
        } else if (field === 'stock') {
            const stock = parseInt(productData.stock_actual_producto) || 0;
            
            // âœ… Usar funciÃ³n centralizada para calcular estado del stock
            const estadoStock = calcularEstadoStock(productData);
            
            
            // Para TABLA: Actualizar nÃºmero y clase
            if (element.classList.contains('stock-number')) {
                element.textContent = stock;
                // Reemplazar solo la clase stock-*
                element.className = element.className.replace(/stock-\w+/g, '') + ' ' + estadoStock.clase;
                element.className = element.className.trim();
                
                // Actualizar tambiÃ©n el texto de estado si existe
                const statusElement = element.parentElement?.querySelector('.stock-status');
                if (statusElement) {
                    statusElement.textContent = estadoStock.textoTabla;
                }
            }
            // Para GRID: Actualizar el texto completo del span (con icono)
            else if (element.closest('.product-card-stock')) {
                // El element YA ES el span correcto (por el selector .product-card-stock span)
                // Actualizar clase
                element.className = estadoStock.clase;
                // Actualizar contenido completo con icono
                element.innerHTML = `<i class="fas fa-box"></i> ${stock} unidades (${estadoStock.texto})`;
            }
        } else if (field === 'precio') {
            element.textContent = newValue;
        } else {
            element.textContent = newValue;
        }
    }

    /**
     * ðŸ–¼ï¸ Obtener URL de imagen del producto
     */
    getProductImageUrl(producto, forceCacheBust = false) {
        const timestamp = forceCacheBust ? '?t=' + new Date().getTime() : '';
        if (producto.url_imagen_producto && producto.url_imagen_producto !== 'NULL') {
            return producto.url_imagen_producto + timestamp;
        } else if (producto.imagen_producto && producto.imagen_producto !== 'NULL') {
            return (window.AppConfig ? window.AppConfig.getImageUrl('products/') : '/fashion-master/public/assets/img/products/') + producto.imagen_producto + timestamp;
        }
        return (window.AppConfig ? window.AppConfig.getImageUrl('default-product.jpg') : '/fashion-master/public/assets/img/default-product.jpg');
    }

    /**
     * ðŸŽ¯ FUNCIÃ“N ESPECÃFICA: Actualizar solo el estado del producto
     * Ultra-rÃ¡pido para cambios de estado
     */
    async updateProductEstado(productId, newEstado) {
        const productData = {
            id_producto: productId,
            estado: newEstado
        };
        
        // Solo actualizar el campo de estado
        await this.updateSingleProduct(productId, productData, ['estado']);
    }

    /**
     * ðŸ“¦ FUNCIÃ“N ESPECÃFICA: Actualizar solo el stock del producto
     * Ultra-rÃ¡pido para cambios de stock
     */
    async updateProductStock(productId, productData) {
        // productData puede ser solo el objeto completo del servidor
        // o un objeto simple con stock_actual_producto
        const updateData = {
            id_producto: productId,
            ...productData
        };
        
        // Solo actualizar el campo de stock
        await this.updateSingleProduct(productId, updateData, ['stock']);
    }

    /**
     * ðŸ’° FUNCIÃ“N ESPECÃFICA: Actualizar solo el precio del producto
     */
    async updateProductPrecio(productId, newPrecio) {
        const productData = {
            id_producto: productId,
            precio_producto: newPrecio
        };
        
        await this.updateSingleProduct(productId, productData, ['precio']);
    }

    /**
     * ðŸ–¼ï¸ FUNCIÃ“N ESPECÃFICA: Actualizar solo la imagen del producto
     */
    async updateProductImagen(productId, newImageUrl) {
        const productData = {
            id_producto: productId,
            url_imagen_producto: newImageUrl
        };
        
        await this.updateSingleProduct(productId, productData, ['imagen']);
    }

    /**
     * ðŸ“ FUNCIÃ“N ESPECÃFICA: Actualizar solo el nombre del producto
     */
    async updateProductNombre(productId, newNombre) {
        const productData = {
            id_producto: productId,
            nombre_producto: newNombre
        };
        
        await this.updateSingleProduct(productId, productData, ['nombre']);
    }

    /**
     * LEGACY: Actualizar producto en la vista tabla (mantener compatibilidad)
     * @deprecated Usar updateSingleProduct() en su lugar
     */
    async updateProductInTable(productId, productData) {
        // Buscar fila en cache o DOM
        let row = this.cache.get(`row-${productId}`);
        if (!row || !document.contains(row)) {
            row = document.querySelector(`#productos-table-body tr[data-product-id="${productId}"]`);
            if (!row) {
                if (typeof window.loadProducts === 'function') {
                    window.loadProducts();
                }
                return;
            }
            // Guardar en cache para próximas actualizaciones
            this.cache.set(`row-${productId}`, row);
        }
        // Crear nueva fila HTML de forma eficiente
        const newRowHTML = this.createTableRow(productData);
        const template = document.createElement('template');
        template.innerHTML = newRowHTML.trim();
        const newRow = template.content.firstElementChild;
        // Animación sutil de escala (sin cambios de color)
        row.style.transition = `transform ${this.animationDuration}ms ease`;
        row.style.transform = 'scale(0.99)';
        // Reemplazar contenido
        row.innerHTML = newRow.innerHTML;
        row.setAttribute('data-product-id', productId);
        // Restaurar escala original
        await this.wait(50);
        row.style.transform = 'scale(1)';
        // Limpiar transición después de la animación
        await this.wait(this.animationDuration);
        row.style.transition = '';
        // Actualizar cache
        this.cache.set(`row-${productId}`, row);
    }

    /**
     * LEGACY: Actualizar producto en la vista grid (mantener compatibilidad)
     * @deprecated Usar updateSingleProduct() en su lugar
     */
    async updateProductInGrid(productId, productData) {
        // Buscar card en cache o DOM
        let card = this.cache.get(`card-${productId}`);
        
        if (!card || !document.contains(card)) {
            card = document.querySelector(`.product-card[data-product-id="${productId}"]`);
            
            if (!card) {
                if (typeof window.loadProducts === 'function') {
                    window.loadProducts();
                }
                return;
            }
            
            // Guardar en cache
            this.cache.set(`card-${productId}`, card);
        }
        
        // Crear nueva card HTML
        const newCardHTML = this.createGridCard(productData);
        const template = document.createElement('template');
        template.innerHTML = newCardHTML.trim();
        const newCard = template.content.firstElementChild;
        
        
        // âœ… AnimaciÃ³n sutil de escala (sin colores ni sombras)
        card.style.transition = `transform ${this.animationDuration}ms ease`;
        card.style.transform = 'scale(0.98)';
        
        await this.wait(this.animationDuration / 2);
        
        // Reemplazar contenido
        card.innerHTML = newCard.innerHTML;
        card.className = newCard.className;
        card.setAttribute('data-product-id', productId);
        
        // Restaurar escala
        await this.wait(50);
        card.style.transform = 'scale(1)';
        
        // Limpiar transiciÃ³n
        await this.wait(this.animationDuration);
        card.style.transition = '';
        
        // Actualizar cache
        this.cache.set(`card-${productId}`, card);
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
        
        // Insertar al inicio con animaciÃ³n
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
        
        // Insertar al inicio con animaciÃ³n
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
     * Eliminar producto de la tabla/grid con animaciÃ³n
     */
    async removeProduct(productId) {
        
        const currentView = this.getCurrentView();
        const element = currentView === 'grid'
            ? document.querySelector(`.product-card[data-product-id="${productId}"]`)
            : document.querySelector(`#productos-table-body tr[data-product-id="${productId}"]`);
        if (!element) return;
        // Animación de salida mejorada
        element.style.transition = `all ${this.animationDuration}ms cubic-bezier(0.4, 0, 0.2, 1)`;
        element.style.opacity = '0';
        element.style.transform = currentView === 'grid'
            ? 'scale(0.9)'
            : 'translateX(-20px)';
        await this.wait(this.animationDuration);
        // Remover del DOM y cache
        element.remove();
        this.cache.delete(currentView === 'grid' ? `card-${productId}` : `row-${productId}`);

    }

    /**
     * Verificar si una fila tiene cambios (comparaciÃ³n inteligente)
     */
    isRowEqual(oldRow, newRow) {
        // Comparar atributos clave
        const oldAttrs = {
            id: oldRow.getAttribute('data-product-id'),
            html: oldRow.querySelector('td:nth-child(3)')?.textContent?.trim() // Nombre
        };
        
        const newAttrs = {
            id: newRow.getAttribute('data-product-id'),
            html: newRow.querySelector('td:nth-child(3)')?.textContent?.trim()
        };
        
        return oldAttrs.id === newAttrs.id && oldAttrs.html === newAttrs.html;
    }

    /**
     * Verificar si una card tiene cambios
     */
    isCardEqual(oldCard, newCard) {
        const oldTitle = oldCard.querySelector('.product-card-title')?.textContent?.trim();
        const newTitle = newCard.querySelector('.product-card-title')?.textContent?.trim();
        
        return oldTitle === newTitle;
    }


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
                return (window.AppConfig ? window.AppConfig.getImageUrl('products/') : '/fashion-master/public/assets/img/products/') + producto.imagen_producto + timestamp;
            }
            return (window.AppConfig ? window.AppConfig.getImageUrl('default-product.jpg') : '/fashion-master/public/assets/img/default-product.jpg');
        };
        
        const imageUrl = getProductImageUrl(producto, true);
        const fallbackImage = window.AppConfig 
            ? window.AppConfig.getImageUrl('default-product.jpg') 
            : (window.AppConfig ? window.AppConfig.getImageUrl('default-product.jpg') : '/fashion-master/public/assets/img/default-product.jpg');
        
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
                ${producto.nombre_categoria || producto.categoria_nombre || 'Sin categorÃ­a'}
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
        
        // âœ… Usar funciÃ³n centralizada para calcular estado del stock
        const estadoStock = calcularEstadoStock(producto);
        
        const precio = parseFloat(producto.precio_producto || 0).toLocaleString('es-CO');
        const descuentoPrecio = producto.precio_descuento_producto 
            ? `<span class="discount-price">$${parseFloat(producto.precio_descuento_producto).toLocaleString('es-CO')}</span>` 
            : '';
        
        // Generar HTML de imagen usando la misma funciÃ³n que displayProductsGrid
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
                    ${producto.codigo ? `<div class="product-card-sku">CÃ³digo: ${producto.codigo}</div>` : ''}
                    <div class="product-card-category">
                        <i class="fas fa-tag"></i> ${producto.nombre_categoria || producto.categoria_nombre || 'Sin categorÃ­a'}
                    </div>
                    
                    <div class="product-card-stock">
                        <span class="${estadoStock.clase}">
                            <i class="fas fa-box"></i> ${stock} unidades (${estadoStock.texto})
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
     * Efecto de destaque en fila - Solo animaciÃ³n sutil (sin colores)
     */
    highlightRow(row) {
        // Efecto visual sutil de pulso
        if (this.rafId) {
            cancelAnimationFrame(this.rafId);
        }
        
        this.rafId = requestAnimationFrame(() => {
            row.style.transition = 'transform 300ms ease-out';
            row.style.transform = 'scale(1.01)';
            
            setTimeout(() => {
                row.style.transform = 'scale(1)';
                
                setTimeout(() => {
                    row.style.transition = '';
                }, 300);
            }, 100);
        });
    }

    /**
     * Efecto de destaque en card - Solo animaciÃ³n sutil (sin sombras de colores)
     */
    highlightCard(card) {
        if (this.rafId) {
            cancelAnimationFrame(this.rafId);
        }
        
        this.rafId = requestAnimationFrame(() => {
            card.style.transition = 'transform 300ms ease-out';
            card.style.transform = 'scale(1.02)';
            
            setTimeout(() => {
                card.style.transform = 'scale(1)';
                setTimeout(() => {
                    card.style.transition = '';
                }, 300);
            }, 300);
        });
    }

    /**
     * Obtener vista actual (OPTIMIZADO con cache)
     */
    getCurrentView() {
        // Cache de 100ms para evitar queries repetitivas
        const now = Date.now();
        if (this._viewCache && (now - this._viewCacheTime) < 100) {
            return this._viewCache;
        }
        
        const activeBtn = document.querySelector('.view-btn.active');
        const view = activeBtn ? activeBtn.dataset.view : 'table';
        
        this._viewCache = view;
        this._viewCacheTime = now;
        
        return view;
    }

    /**
     * Helper: Esperar tiempo especÃ­fico (optimizado)
     */
    wait(ms) {
        return new Promise(resolve => {
            if (ms <= 0) {
                resolve();
            } else {
                setTimeout(resolve, ms);
            }
        });
    }

    /**
     * ðŸ“‹ Obtener IDs de productos actualmente visibles
     */
    getCurrentProductIds(viewType) {
        const ids = [];
        if (viewType === 'grid') {
            const cards = document.querySelectorAll('.product-card[data-product-id]');
            cards.forEach(card => {
                const id = parseInt(card.dataset.productId);
                if (id) ids.push(id);
            });
        } else {
            const rows = document.querySelectorAll('#productos-table-body tr[data-product-id]');
            rows.forEach(row => {
                const id = parseInt(row.dataset.productId);
                if (id) ids.push(id);
            });
        }
        return ids;
    }

    /**
     * ðŸ™ˆ Ocultar producto con animaciÃ³n smooth
     */
    async hideProduct(productId, viewType) {
        return new Promise((resolve) => {
            let element;
            if (viewType === 'grid') {
                element = document.querySelector(`.product-card[data-product-id="${productId}"]`);
            } else {
                element = document.querySelector(`#productos-table-body tr[data-product-id="${productId}"]`);
            }
            
            if (!element) {
                resolve();
                return;
            }
            
            // AnimaciÃ³n de salida
            element.style.transition = 'all 0.2s ease-out';
            element.style.opacity = '0';
            element.style.transform = 'scale(0.95)';
            
            setTimeout(() => {
                element.style.display = 'none';
                resolve();
            }, 200);
        });
    }

    /**
     * ðŸ‘ï¸ Mostrar producto con animaciÃ³n smooth
     */
    async showProduct(product, viewType) {
        return new Promise((resolve) => {
            let element;
            if (viewType === 'grid') {
                element = document.querySelector(`.product-card[data-product-id="${product.id_producto}"]`);
            } else {
                element = document.querySelector(`#productos-table-body tr[data-product-id="${product.id_producto}"]`);
            }
            
            if (element && element.style.display === 'none') {
                // Mostrar elemento oculto
                element.style.display = '';
                element.style.opacity = '0';
                element.style.transform = 'scale(0.95)';
                
                setTimeout(() => {
                    element.style.transition = 'all 0.2s ease-out';
                    element.style.opacity = '1';
                    element.style.transform = 'scale(1)';
                    
                    setTimeout(() => {
                        element.style.transition = '';
                        resolve();
                    }, 200);
                }, 10);
            } else if (!element) {
                // Producto nuevo - recargar tabla completa
                if (typeof window.loadProducts === 'function') {
                    window.loadProducts();
                }
                resolve();
            } else {
                // Ya estÃ¡ visible
                resolve();
            }
        });
    }

    /**
     * 🧹 Limpiar todo el caché
     */
    clearCache() {
        console.log('🧹 Limpiando caché completo...');
        this.cache.clear();
        this.dataCache.clear();
        console.log('✅ Caché limpiado');
    }

    destroy() {
        this.clearCache();
        if (this.rafId) {
            cancelAnimationFrame(this.rafId);
        }
        if (this.observer) {
            this.observer.disconnect();
        }
    }
}

// ===== EXPORTAR CLASE AL SCOPE GLOBAL =====
window.ProductosTableUpdater = SmoothTableUpdater;


// Exportar para uso en otros mÃ³dulos
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SmoothTableUpdater;
}

window.updateMultipleProducts = async function(products) {
    if (!window.smoothTableUpdater) {
        return;
    }
    
    const startTime = performance.now();
    
    // Actualizar en lotes para mejor performance
    const batchSize = 5;
    for (let i = 0; i < products.length; i += batchSize) {
        const batch = products.slice(i, i + batchSize);
        await Promise.all(
            batch.map(product => 
                window.smoothTableUpdater.updateSingleProduct(product)
            )
        );
    }
    
    const endTime = performance.now();
};

/**
 * Forzar limpieza de cache (Ãºtil para debugging)
 */
window.clearProductCache = function() {
    if (window.smoothTableUpdater) {
        window.smoothTableUpdater.clearCache();
        window.smoothTableUpdater.dataCache.clear();
    }
};
}