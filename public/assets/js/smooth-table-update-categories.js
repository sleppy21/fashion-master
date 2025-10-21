/**
 * SISTEMA DE ACTUALIZACIÓN SUAVE DE TABLA/GRID V3.0 - FIELD-LEVEL UPDATE
 * Actualización GRANULAR a nivel de campo individual
 * Solo actualiza los campos específicos que cambiaron - Ultra rápido
 * ✨ Animaciones SUTILES sin cambios de color ni fondos
 * @version 3.0.0
 * @author Fashion Store Team
 */

/**
 * 📦 FUNCIÓN CENTRALIZADA PARA CALCULAR ESTADO DE STOCK (JavaScript)
 * Replica EXACTAMENTE la misma lógica que CategoryController.php
 * ✅ COMPATIBLE con productos Y categorías (detecta automáticamente)
 * 
 * @param {Object} item - Objeto con stock (producto o categoría)
 * @returns {Object} - {clase: string, texto: string, textoTabla: string}
 */
function calcularEstadoStock(item) {
    // 🔍 DETECTAR AUTOMÁTICAMENTE si es PRODUCTO o CATEGORÍA
    const esProducto = item.stock_actual_producto !== undefined || item.id_producto !== undefined;
    const esCategoria = item.stock_actual_categoria !== undefined || item.id_categoria !== undefined;
    
    // Obtener valores según el tipo de objeto
    let stockActual, stockMinimo;
    
    if (esProducto) {
        stockActual = parseInt(item.stock_actual_producto) || 0;
        stockMinimo = item.stock_minimo_producto ? parseInt(item.stock_minimo_producto) : null;
    } else if (esCategoria) {
        stockActual = parseInt(item.stock_actual_categoria) || 0;
        stockMinimo = item.stock_minimo_categoria ? parseInt(item.stock_minimo_categoria) : null;
    } else {
        // Fallback genérico
        stockActual = parseInt(item.stock_actual) || 0;
        stockMinimo = item.stock_minimo ? parseInt(item.stock_minimo) : null;
    }
    
    // Prioridad 1: Stock en 0 = Agotado
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
    
    // Prioridad 3: Stock normal
    return {
        clase: 'stock-normal',
        texto: 'Normal',
        textoTabla: 'Stock normal'
    };
}

// ✅ EXPORTAR AL SCOPE GLOBAL (compatible con productos)
window.calcularEstadoStock = calcularEstadoStock;

/**
 * 🎨 FUNCIONES PARA GÉNERO
 */
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

class SmoothTableUpdater {
    constructor() {
        this.isUpdating = false;
        this.updateQueue = [];
        this.animationDuration = 200; // ms - animación suave pero sin fondos
        this.cache = new Map(); // Cache de elementos DOM
        this.dataCache = new Map(); // Cache de datos para comparación
        this.observer = null; // Intersection Observer para lazy updates
        this.rafId = null; // RequestAnimationFrame ID para cancelar animaciones
        
        // Mapeo de campos a selectores CSS (Vista Tabla) - ADAPTADO PARA CATEGORÍAS
        this.fieldSelectorsTable = {
            imagen: 'td:nth-child(2) img',
            nombre: 'td:nth-child(3) .product-info strong',
            descripcion: 'td:nth-child(4)',
            total_productos: 'td:nth-child(5) .badge',
            estado: 'td:nth-child(6) .status-badge',
            fecha: 'td:nth-child(7)'
        };
        
        // Mapeo de campos a selectores CSS (Vista Grid) - ADAPTADO PARA CATEGORÍAS
        this.fieldSelectorsGrid = {
            imagen: '.product-card-image-mobile img',
            nombre: '.product-card-title',
            descripcion: '.product-card-description',
            total_productos: '.product-card-category', // Contiene el ícono + texto de productos
            estado: '.product-card-status'
        };
    }

    /**
     * 🚀 NUEVA FUNCIÓN: Actualizar SOLO campos específicos que cambiaron
     * @param {number|object} productId - ID del categoria o datos completos
     * @param {object} updatedData - Datos actualizados
     * @param {array} changedFields - Array de campos que cambiaron (opcional, se detecta automáticamente)
     */
    async updateSingleProduct(productId, updatedData = null, changedFields = null) {
        try {
          
            
            // Soporte para pasar el objeto completo como primer parámetro
            if (typeof productId === 'object' && productId !== null) {
                updatedData = productId;
                productId = updatedData.id_categoria || updatedData.id;
            }

            // Validar que tengamos los datos necesarios
            if (!updatedData) {
                const response = await fetch(`${window.CONFIG.apiUrl}?action=get&id=${productId}`);
                const result = await response.json();
                
                if (!result.success) {
                    throw new Error('Error al obtener datos del categoria');
                }
                
                updatedData = result.product;
            }

            // Validar ID
            if (!productId || productId <= 0) {
                throw new Error('ID de categoria inválido');
            }
            
            
            // 🆕 LIMPIAR CACHE DEL ELEMENTO DOM ANTES DE ACTUALIZAR
            // Esto fuerza a que se vuelva a buscar en el DOM con datos frescos
            this.cache.delete(`row-${productId}`);
            this.cache.delete(`card-${productId}`);
            
            
            // Detectar campos que cambiaron si no se especificaron
            if (!changedFields) {
                changedFields = this.detectChangedFields(productId, updatedData);
                
                // 🎯 PARCHE: Si viene desde EDICIÓN y solo detectó estado,
                // forzar actualización de TODOS los campos para evitar problemas de caché
                // NOTA: NO incluir 'fecha' porque fecha_creacion nunca cambia
                // NOTA 2: NO incluir 'codigo' porque no existe en la tabla de categorías
                if (changedFields && changedFields.length <= 2 && 
                    updatedData.nombre_categoria) {
                    // Para TABLA y GRID: mismo conjunto de campos (sin codigo)
                    changedFields = ['imagen', 'nombre', 'descripcion', 'total_productos', 'estado'];
                }
            }
            
            // 🆕 FORZAR ACTUALIZACIÓN DE TODOS LOS CAMPOS AL EDITAR DESDE MODAL
            // Si los datos vienen completos (con nombre_categoria, etc.), actualizar TODO
            if (!changedFields && updatedData.nombre_categoria) {
                changedFields = ['imagen', 'nombre', 'descripcion', 'total_productos', 'estado'];
            }

            
            
            // Detectar vista actual
            const currentView = this.getCurrentView();
            
            // Actualizar SOLO los campos específicos (SIN await)
            if (currentView === 'grid') {
                this.updateFieldsInGrid(productId, updatedData, changedFields);
            } else {
                this.updateFieldsInTable(productId, updatedData, changedFields);
            }
            
            
            // ✅ SOBRESCRIBIR COMPLETAMENTE datos en cache (no mergear)
            // Esto asegura que la próxima comparación use los datos más recientes
            this.dataCache.set(`data-${productId}`, { ...updatedData });

            
        } catch (error) {
            // Fallback mejorado: recargar solo si es crítico
            if (error.message.includes('inválido')) {
                if (typeof window.loadProducts === 'function') {
                    window.loadProducts();
                }
            }
        }
    }

    /**
     * 🎯 Actualizar múltiples categorias con smooth transition (para filtros/búsqueda)
     */
    async updateMultipleProducts(newProductsList) {
        
        try {
            // Obtener categorias actuales en la tabla/grid
            const currentView = this.getCurrentView();
            const currentProductIds = this.getCurrentProductIds(currentView);
            const newProductIds = newProductsList.map(p => p.id_categoria);

            // 1. Ocultar categorias que ya no están en la lista
            const productsToHide = currentProductIds.filter(id => !newProductIds.includes(id));
            for (const productId of productsToHide) {
                await this.hideProduct(productId, currentView);
            }
            
            // 2. Actualizar o mostrar categorias existentes/nuevos
            for (const product of newProductsList) {
                if (currentProductIds.includes(product.id_categoria)) {
                    // Actualizar categoria existente
                    await this.updateSingleProduct(product.id_categoria, product);
                } else {
                    // Mostrar nuevo categoria (si estaba oculto) o agregarlo
                    await this.showProduct(product, currentView);
                }
            }
            
            
        } catch (error) {
            throw error;
        }
    }

    /**
     * 🔍 Detectar qué campos cambiaron comparando con cache
     */
    detectChangedFields(productId, newData) {
        const cachedData = this.dataCache.get(`data-${productId}`);
        
        if (!cachedData) {
            // Primera vez: Guardar datos completos en cache Y actualizar TODOS los campos
            this.dataCache.set(`data-${productId}`, { ...newData });
            // ✅ Retornar TODOS los campos principales para actualizar en primera carga
            // NOTA: NO incluir 'fecha' porque fecha_creacion nunca cambia y puede causar problemas
            return ['imagen', 'nombre', 'descripcion', 'total_productos', 'estado'];
        }
        
        const changed = [];

        
        // Comparar campo por campo
        if (cachedData.url_imagen_categoria !== newData.url_imagen_categoria || 
            cachedData.imagen_categoria !== newData.imagen_categoria) {
            changed.push('imagen');
        }
        
        if (cachedData.nombre_categoria !== newData.nombre_categoria) {
            changed.push('nombre');
        }
        
        if (cachedData.descripcion_categoria !== newData.descripcion_categoria) {
            changed.push('descripcion');
        }
        
        if (parseInt(cachedData.total_productos) !== parseInt(newData.total_productos)) {
            changed.push('total_productos');
        }
        
        if (String(cachedData.estado_categoria).toLowerCase() !== String(newData.estado_categoria).toLowerCase()) {
            changed.push('estado');
        }
        
        return changed.length > 0 ? changed : null;
    }

    /**
     * 🎯 Actualizar SOLO campos específicos en la vista TABLA
     */
    updateFieldsInTable(productId, productData, changedFields) {
        
        // Buscar fila en cache o DOM
        let row = this.cache.get(`row-${productId}`);
        
        if (!row || !document.contains(row)) {
            row = document.querySelector(`#categorias-table-body tr[data-product-id="${productId}"]`);
            
            this.cache.set(`row-${productId}`, row);
        }
        
        // Actualizar cada campo que cambió (SIN await - paralelo)
        for (const field of changedFields) {
            this.updateSingleField(row, field, productData, 'table');
        }
    }

    /**
     * 🎯 Actualizar SOLO campos específicos en la vista GRID
     */
    updateFieldsInGrid(productId, productData, changedFields) {
        // Buscar card en cache o DOM
        let card = this.cache.get(`card-${productId}`);
        
        if (!card || !document.contains(card)) {
            card = document.querySelector(`.product-card[data-product-id="${productId}"]`);
            
            this.cache.set(`card-${productId}`, card);
        }
        
        // Actualizar cada campo que cambió (SIN await - paralelo)
        for (const field of changedFields) {
            this.updateSingleField(card, field, productData, 'grid');
        }
    }

    /**
     * ⚡ Actualizar UN SOLO CAMPO - Solo animación de movimiento (sin cambios de color)
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

        // Animación sutil NO bloqueante (solo visual)
        element.style.transition = `transform 120ms cubic-bezier(0.4, 0, 0.2, 1)`;
        if (field === 'estado') {
            element.style.transform = 'scale(1.05)';
        } else if (field === 'stock') {
            element.style.transform = 'scale(1.03)';
        } else {
            element.style.transform = 'scale(1.02)';
        }

        // Restaurar después de la animación (no await)
        setTimeout(() => {
            try {
                element.style.transform = 'scale(1)';
                setTimeout(() => { element.style.transition = ''; }, 120);
            } catch (e) {
                // elemento pudo haber sido removido
            }
        }, 80);

    }

    /**
     * 📝 Obtener valor de un campo desde los datos de la categoría
     */
    getFieldValue(field, productData) {
        switch (field) {
            case 'codigo':
                return productData.codigo_categoria || '-';
            case 'nombre':
                return productData.nombre_categoria || 'Sin nombre';
            case 'descripcion':
                // Truncar descripción si es muy larga
                const desc = productData.descripcion_categoria || 'Sin descripción';
                return desc.length > 100 ? desc.substring(0, 97) + '...' : desc;
            case 'total_productos':
                return String(parseInt(productData.total_productos) || 0);
            case 'estado':
                return productData.estado_categoria === 'activo' ? 'Activo' : 'Inactivo';
            case 'imagen':
                return this.getCategoryImageUrl(productData, true);
            case 'fecha':
                // Extraer solo la fecha (YYYY-MM-DD) sin la hora
                const fecha = productData.fecha_creacion_categoria || 
                             productData.fecha_actualizacion_categoria || 
                             productData.fecha || '';
                const fechaSplit = fecha ? fecha.split(' ')[0] : '-';
                return fechaSplit;
            default:
                return '';
        }
    }

    /**
     * 📖 Obtener valor actual de un campo desde el DOM
     */
    getCurrentFieldValue(element, field) {
        if (field === 'imagen') {
            return element.src || '';
        } else if (field === 'estado') {
            return element.textContent.trim();
        } else if (field === 'total_productos') {
            // Extraer solo el número (puede estar en badge o en div con texto)
            const text = element.textContent.trim();
            const match = text.match(/\d+/);
            return match ? match[0] : '0';
        } else {
            return element.textContent.trim();
        }
    }

    /**
     * 🖊️ Establecer nuevo valor en un campo del DOM
     */
    setFieldValue(element, field, productData, newValue) {
        
        if (field === 'imagen') {
            // Verificar si es una imagen válida o imagen por defecto
            const hasImage = newValue && !newValue.includes('default-category.png') && !newValue.includes('default-product.jpg');
            
            // � Detectar si estamos en TABLA o GRID
            const tdParent = element.closest('td');
            const gridParent = element.closest('.product-card-image-mobile');
            
            if (tdParent) {
                // 📊 VISTA TABLA
                element.src = newValue;
                
                // 🔧 Actualizar también el evento ondblclick del TD padre para la previsualización
                const categoryName = (productData.nombre_categoria || '').replace(/'/g, "\\'");
                tdParent.setAttribute('ondblclick', 
                    `event.stopPropagation(); showImageFullSize('${newValue}', '${categoryName}')`
                );
                
            } else if (gridParent) {
                // 🎴 VISTA GRID
                if (hasImage) {
                    // Hay imagen válida - mostrar img
                    gridParent.className = 'product-card-image-mobile';
                    gridParent.innerHTML = `<img src="${newValue}" alt="${productData.nombre_categoria || 'categoría'}" onerror="this.parentElement.classList.add('no-image'); this.style.display='none'; this.parentElement.innerHTML='<i class=\\'fas fa-image\\'></i>';">`;
                } else {
                    // No hay imagen - mostrar ícono
                    gridParent.className = 'product-card-image-mobile no-image';
                    gridParent.innerHTML = '<i class="fas fa-image"></i>';
                }
            } else {
                // Fallback - solo actualizar src
                element.src = newValue;
            }
        } else if (field === 'codigo') {
            element.textContent = newValue;
        } else if (field === 'nombre') {
            element.textContent = newValue;
        } else if (field === 'descripcion') {
            element.textContent = newValue;
        } else if (field === 'total_productos') {
            const count = parseInt(newValue);
            const plural = count !== 1 ? 's' : '';
            
            // Detectar si es GRID o TABLA
            if (element.classList.contains('product-card-category')) {
                // Es GRID - actualizar con ícono
                element.innerHTML = `<i class="fas fa-boxes"></i> ${count} producto${plural}`;
            } else {
                // Es TABLA - actualizar badge CON TEXTO DESCRIPTIVO
                element.textContent = `${count} prod. relacionado${plural}`;
                // Cambiar clase según la cantidad
                if (count === 0) {
                    element.className = 'badge badge-secondary';
                } else if (count > 10) {
                    element.className = 'badge badge-success';
                } else {
                    element.className = 'badge badge-info';
                }
            }
        } else if (field === 'estado') {
            const isActive = productData.estado_categoria === 'activo';
            element.textContent = newValue;
            
            // Detectar si es GRID o TABLA y aplicar clases correctas
            if (element.classList.contains('product-card-status')) {
                // Es GRID
                element.className = 'product-card-status ' + (isActive ? 'active' : 'inactive');
            } else {
                // Es TABLA
                element.className = 'status-badge ' + (isActive ? 'status-active' : 'status-inactive');
            }
        } else if (field === 'fecha') {
            element.textContent = newValue;
        } else {
            element.textContent = newValue;
        }
    }

    /**
     * 🖼️ Obtener URL de imagen del categoria
     */
    getCategoryImageUrl(categoria, forceCacheBust = false) {
        const timestamp = forceCacheBust ? '?t=' + new Date().getTime() : '';
        if (categoria.url_imagen_categoria && categoria.url_imagen_categoria !== 'NULL') {
            return categoria.url_imagen_categoria + timestamp;
        } else if (categoria.imagen_categoria && categoria.imagen_categoria !== 'NULL') {
            return (window.AppConfig ? window.AppConfig.getImageUrl('categories/') : '/fashion-master/public/assets/img/categories/') + categoria.imagen_categoria + timestamp;
        }
        return (window.AppConfig ? window.AppConfig.getImageUrl('default-category.png') : '/fashion-master/public/assets/img/default-category.png');
    }

    /**
     * 🎯 FUNCIÓN ESPECÍFICA: Actualizar solo el estado del categoria
     * Ultra-rápido para cambios de estado
     */
    async updateProductEstado(productId, newEstado) {
        const productData = {
            id_categoria: productId,
            estado: newEstado
        };
        
        // Solo actualizar el campo de estado
        await this.updateSingleProduct(productId, productData, ['estado']);
    }

    /**
     * 📦 FUNCIÓN ESPECÍFICA: Actualizar solo el stock del categoria
     * Ultra-rápido para cambios de stock
     */
    async updateProductStock(productId, productData) {
        // productData puede ser solo el objeto completo del servidor
        // o un objeto simple con stock_actual_categoria
        const updateData = {
            id_categoria: productId,
            ...productData
        };
        
        // Solo actualizar el campo de stock
        await this.updateSingleProduct(productId, updateData, ['stock']);
    }

    /**
     * 💰 FUNCIÓN ESPECÍFICA: Actualizar solo el precio del categoria
     */
    async updateProductPrecio(productId, newPrecio) {
        const productData = {
            id_categoria: productId,
            precio_categoria: newPrecio
        };
        
        await this.updateSingleProduct(productId, productData, ['precio']);
    }

    /**
     * 🖼️ FUNCIÓN ESPECÍFICA: Actualizar solo la imagen del categoria
     */
    async updateProductImagen(productId, newImageUrl) {
        const productData = {
            id_categoria: productId,
            url_imagen_categoria: newImageUrl
        };
        
        await this.updateSingleProduct(productId, productData, ['imagen']);
    }

    /**
     * 📝 FUNCIÓN ESPECÍFICA: Actualizar solo el nombre del categoria
     */
    async updateProductNombre(productId, newNombre) {
        const productData = {
            id_categoria: productId,
            nombre_categoria: newNombre
        };
        
        await this.updateSingleProduct(productId, productData, ['nombre']);
    }

    /**
     * LEGACY: Actualizar categoria en la vista tabla (mantener compatibilidad)
     * @deprecated Usar updateSingleProduct() en su lugar
     */
    async updateProductInTable(productId, productData) {
        // Buscar fila en cache o DOM
        let row = this.cache.get(`row-${productId}`);
        
        if (!row || !document.contains(row)) {
            row = document.querySelector(`#categorias-table-body tr[data-product-id="${productId}"]`);
            
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
        
        // Verificar si realmente hay cambios (evitar updates innecesarios)
        if (this.isRowEqual(row, newRow)) {
            return;
        }
        
        // ✅ Animación sutil de escala (sin cambios de color)
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
     * LEGACY: Actualizar categoria en la vista grid (mantener compatibilidad)
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
        
        // Verificar cambios
        if (this.isCardEqual(card, newCard)) {
            return;
        }
        
        // ✅ Animación sutil de escala (sin colores ni sombras)
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
        
        // Limpiar transición
        await this.wait(this.animationDuration);
        card.style.transition = '';
        
        // Actualizar cache
        this.cache.set(`card-${productId}`, card);
    }

    /**
     * Agregar nuevo categoria a la tabla/grid
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
     * Agregar categoria a la tabla
     */
    async addProductToTable(productData) {
        const tbody = document.getElementById('categorias-table-body');
        
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
        
        // 🔢 RECALCULAR ÍNDICES de todas las filas después de insertar
        this.recalculateRowNumbers();
        
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
     * Agregar categoria al grid
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
     * Eliminar categoria de la tabla/grid con animación
     */
    async removeProduct(productId) {
        
        const currentView = this.getCurrentView();
        
        const element = currentView === 'grid' 
            ? document.querySelector(`.product-card[data-product-id="${productId}"]`)
            : document.querySelector(`#categorias-table-body tr[data-product-id="${productId}"]`);
        
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
        
        
        // 🔢 RECALCULAR ÍNDICES después de eliminar
        if (currentView !== 'grid') {
            this.recalculateRowNumbers();
        }
        
    }

    /**
     * 🔢 Recalcular números de fila en la tabla
     */
    recalculateRowNumbers() {
        const tbody = document.getElementById('categorias-table-body');
        if (!tbody) return;
        
        const rows = tbody.querySelectorAll('tr[data-product-id]');
        rows.forEach((row, index) => {
            const firstCell = row.querySelector('td:first-child strong');
            if (firstCell) {
                firstCell.textContent = index + 1;
            }
        });
        
    }

    /**
     * Verificar si una fila tiene cambios (comparación inteligente)
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


    /**
     * Crear HTML de fila de tabla
     * IMPORTANTE: Este HTML debe coincidir EXACTAMENTE con el generado en displayProducts()
     */
    createTableRow(categoria) {
        // Calcular clase de stock
        const stock = parseInt(categoria.stock_actual_categoria) || 0;
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
        const getProductImageUrl = (categoria, forceCacheBust = false) => {
            const timestamp = forceCacheBust ? '?t=' + new Date().getTime() : '';
            if (categoria.url_imagen_categoria && categoria.url_imagen_categoria !== 'NULL') {
                return categoria.url_imagen_categoria + timestamp;
            } else if (categoria.imagen_categoria && categoria.imagen_categoria !== 'NULL') {
                return (window.AppConfig ? window.AppConfig.getImageUrl('categories/') : '/fashion-master/public/assets/img/categories/') + categoria.imagen_categoria + timestamp;
            }
            return (window.AppConfig ? window.AppConfig.getImageUrl('default-category.png') : '/fashion-master/public/assets/img/default-category.png');
        };
        
        const imageUrl = getProductImageUrl(categoria, true);
        const fallbackImage = (window.AppConfig ? window.AppConfig.getImageUrl('default-category.png') : '/fashion-master/public/assets/img/default-category.png');
        
        // Total productos
        const totalProductos = categoria.total_productos || categoria.productos_count || 0;
        const pluralProductos = totalProductos !== 1 ? 's' : '';
        
        // Fecha
        const fecha = categoria.fecha_creacion_categoria ? categoria.fecha_creacion_categoria.split(' ')[0] : 'N/A';
        
        // HTML EXACTO como en displayProducts() de admin_categorias.php
        return `
        <tr oncontextmenu="return false;" ondblclick="editCategoria(${categoria.id_categoria})" style="cursor: pointer;" data-product-id="${categoria.id_categoria}">
            <td><strong>1</strong></td>
            <td onclick="event.stopPropagation();" ondblclick="event.stopPropagation(); showImageFullSize('${imageUrl}', '${(categoria.nombre_categoria || '').replace(/'/g, "\\'")}')"; style="cursor: zoom-in;">
                <div class="product-image-small">
                    <img src="${imageUrl}" 
                         alt="categoría" 
                         onerror="this.src='${fallbackImage}'; this.onerror=null;">
                </div>
            </td>
            <td>
                <div class="product-info">
                    <strong>${categoria.nombre_categoria || 'Sin nombre'}</strong>
                </div>
            </td>
            <td>
                ${categoria.descripcion_categoria || 'Sin descripción'}
            </td>
            <td>
                <span class="badge badge-info">${totalProductos} prod. relacionado${pluralProductos}</span>
            </td>
            <td>
                <span class="status-badge ${categoria.estado_categoria === 'activo' ? 'status-active' : 'status-inactive'}">
                    ${categoria.estado_categoria === 'activo' ? 'Activo' : 'Inactivo'}
                </span>
            </td>
            <td>${fecha}</td>
            <td>
                <div class="action-buttons">
                    <button class="btn-action btn-menu" onclick="event.stopPropagation(); showActionMenu(${categoria.id_categoria}, '${(categoria.nombre_categoria || '').replace(/'/g, "\\'")}', 0, '${categoria.estado_categoria}', event)" title="Acciones">
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
    createGridCard(categoria) {
        const stock = parseInt(categoria.stock_actual_categoria) || 0;
        
        // ✅ Usar función centralizada para calcular estado del stock
        const estadoStock = calcularEstadoStock(categoria);
        
        const precio = parseFloat(categoria.precio_categoria || 0).toLocaleString('es-CO');
        const descuentoPrecio = categoria.precio_descuento_categoria 
            ? `<span class="discount-price">$${parseFloat(categoria.precio_descuento_categoria).toLocaleString('es-CO')}</span>` 
            : '';
        
        // Generar HTML de imagen usando la misma función que displayProductsGrid
        const imageUrl = typeof window.getProductImageUrl === 'function' 
            ? window.getProductImageUrl(categoria) 
            : (categoria.url_imagen_categoria || '');
        const hasImage = imageUrl && !imageUrl.includes('default-product.jpg');
        
        const imageHTML = `
            <div class="product-card-image-mobile ${hasImage ? '' : 'no-image'}">
                ${hasImage 
                    ? `<img src="${imageUrl}" alt="${categoria.nombre_categoria || 'categoria'}" onerror="this.parentElement.classList.add('no-image'); this.style.display='none'; this.parentElement.innerHTML='<i class=\\'fas fa-image\\'></i>';">` 
                    : '<i class="fas fa-image"></i>'}
            </div>
        `;
        
        // Total de productos para mostrar
        const totalProductos = categoria.total_productos || categoria.productos_count || 0;
        
        // HTML EXACTO como en displayProductsGrid() de admin_categorias.php
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
    }

    /**
     * Efecto de destaque en fila - Solo animación sutil (sin colores)
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
     * Efecto de destaque en card - Solo animación sutil (sin sombras de colores)
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
     * Helper: Esperar tiempo específico (optimizado)
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
     * 📋 Obtener IDs de categorias actualmente visibles
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
            const rows = document.querySelectorAll('#categorias-table-body tr[data-product-id]');
            rows.forEach(row => {
                const id = parseInt(row.dataset.productId);
                if (id) ids.push(id);
            });
        }
        return ids;
    }

    /**
     * 🙈 Ocultar categoria con animación smooth
     */
    async hideProduct(productId, viewType) {
        return new Promise((resolve) => {
            let element;
            if (viewType === 'grid') {
                element = document.querySelector(`.product-card[data-product-id="${productId}"]`);
            } else {
                element = document.querySelector(`#categorias-table-body tr[data-product-id="${productId}"]`);
            }
            
            if (!element) {
                resolve();
                return;
            }
            
            // Animación de salida
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
     * 👁️ Mostrar categoria con animación smooth
     */
    async showProduct(product, viewType) {
        return new Promise((resolve) => {
            let element;
            if (viewType === 'grid') {
                element = document.querySelector(`.product-card[data-product-id="${product.id_categoria}"]`);
            } else {
                element = document.querySelector(`#categorias-table-body tr[data-product-id="${product.id_categoria}"]`);
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
                // categoria nuevo - recargar tabla completa
                if (typeof window.loadProducts === 'function') {
                    window.loadProducts();
                }
                resolve();
            } else {
                // Ya está visible
                resolve();
            }
        });
    }

    /**
     * Destructor: Limpiar recursos
     */
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
window.CategoriasTableUpdater = SmoothTableUpdater;

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
 * Forzar limpieza de cache (útil para debugging)
 */
window.clearProductCache = function() {
    if (window.smoothTableUpdater) {
        window.smoothTableUpdater.clearCache();
        window.smoothTableUpdater.dataCache.clear();
    }
};




