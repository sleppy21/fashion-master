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
 * Replica EXACTAMENTE la misma lógica que ProductController.php
 * 
 * @param {Object} producto - Objeto con stock_actual_producto y stock_minimo_producto
 * @returns {Object} - {clase: string, texto: string, textoTabla: string}
 */
function calcularEstadoStock(producto) {
    const stockActual = parseInt(producto.stock_actual_producto) || 0;
    const stockMinimo = producto.stock_minimo_producto ? parseInt(producto.stock_minimo_producto) : null;
    
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

// ⚠️ PREVENIR REDECLARACIÓN
if (typeof SmoothTableUpdater === 'undefined') {
    console.log('✅ Declarando SmoothTableUpdater V3.0 - Field-Level Updates');

class SmoothTableUpdater {
    constructor() {
        this.isUpdating = false;
        this.updateQueue = [];
        this.animationDuration = 200; // ms - animación suave pero sin fondos
        this.cache = new Map(); // Cache de elementos DOM
        this.dataCache = new Map(); // Cache de datos para comparación
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
     * 🚀 NUEVA FUNCIÓN: Actualizar SOLO campos específicos que cambiaron
     * @param {number|object} productId - ID del producto o datos completos
     * @param {object} updatedData - Datos actualizados
     * @param {array} changedFields - Array de campos que cambiaron (opcional, se detecta automáticamente)
     */
    async updateSingleProduct(productId, updatedData = null, changedFields = null) {
        try {
            
            // Soporte para pasar el objeto completo como primer parámetro
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
                throw new Error('ID de producto inválido');
            }
            
            // 🆕 LIMPIAR CACHE DEL ELEMENTO DOM ANTES DE ACTUALIZAR
            // Esto fuerza a que se vuelva a buscar en el DOM con datos frescos
            this.cache.delete(`row-${productId}`);
            this.cache.delete(`card-${productId}`);
            
            // Detectar campos que cambiaron si no se especificaron
            if (!changedFields) {
                changedFields = this.detectChangedFields(productId, updatedData);
                
                // 🎯 PARCHE: Si viene desde EDICIÓN y solo detectó estado/stock,
                // forzar actualización de TODOS los campos para evitar problemas de caché
                // NOTA: NO incluir 'fecha' porque fecha_creacion nunca cambia
                if (changedFields && changedFields.length <= 2 && 
                    updatedData.nombre_producto && updatedData.nombre_marca) {
                    changedFields = ['imagen', 'nombre', 'categoria', 'marca', 'genero', 'precio', 'stock', 'estado'];
                }
            }
            
            // 🆕 FORZAR ACTUALIZACIÓN DE TODOS LOS CAMPOS AL EDITAR DESDE MODAL
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
            
            // Actualizar SOLO los campos específicos (SIN await)
            if (currentView === 'grid') {
                this.updateFieldsInGrid(productId, updatedData, changedFields);
            } else {
                this.updateFieldsInTable(productId, updatedData, changedFields);
            }
            
            // ✅ SOBRESCRIBIR COMPLETAMENTE datos en cache (no mergear)
            // Esto asegura que la próxima comparación use los datos más recientes
            this.dataCache.set(`data-${productId}`, { ...updatedData });
            console.log('💾 Cache de datos actualizado para producto', productId);
            
            console.log(`✅ Producto ${productId} actualizado exitosamente (${changedFields.length} campo(s))`);
            
        } catch (error) {
            console.error('❌ Error actualizando producto:', error);
            // Fallback mejorado: recargar solo si es crítico
            if (error.message.includes('inválido')) {
                console.error('Error crítico, recargando tabla completa...');
                if (typeof window.loadProducts === 'function') {
                    window.loadProducts();
                }
            }
        }
    }

    /**
     * 🎯 Actualizar múltiples productos con smooth transition (para filtros/búsqueda)
     */
    async updateMultipleProducts(newProductsList) {
        console.log('🔄 Actualizando múltiples productos con smooth transition:', newProductsList.length);
        
        try {
            // Obtener productos actuales en la tabla/grid
            const currentView = this.getCurrentView();
            const currentProductIds = this.getCurrentProductIds(currentView);
            const newProductIds = newProductsList.map(p => p.id_producto);
            
            console.log('📊 Productos actuales:', currentProductIds);
            console.log('📊 Productos nuevos:', newProductIds);
            
            // 1. Ocultar productos que ya no están en la lista
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
            
            console.log('✅ Actualización múltiple completada');
            
        } catch (error) {
            console.error('❌ Error en updateMultipleProducts:', error);
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
            console.log('🆕 Sin caché previo, actualizando TODOS los campos del producto', productId);
            return ['imagen', 'nombre', 'categoria', 'marca', 'genero', 'precio', 'stock', 'estado'];
        }
        
        const changed = [];
        
        // 🔍 Log de comparación para debugging
        console.log('🔍 Comparando datos:', {
            productId,
            cached_marca: cachedData.nombre_marca,
            new_marca: newData.nombre_marca,
            cached_stock: cachedData.stock_actual_producto,
            new_stock: newData.stock_actual_producto
        });
        
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
            console.log('✅ Marca cambió:', cachedData.nombre_marca, '→', newData.nombre_marca);
            changed.push('marca');
        }
        
        if (cachedData.genero_producto !== newData.genero_producto) {
            console.log('✅ Género cambió:', cachedData.genero_producto, '→', newData.genero_producto);
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
        
        console.log('🎯 Campos que cambiaron:', changed);
        return changed.length > 0 ? changed : null;
    }

    /**
     * 🎯 Actualizar SOLO campos específicos en la vista TABLA
     */
    updateFieldsInTable(productId, productData, changedFields) {
        console.log('🔵 updateFieldsInTable:', { productId, changedFields });
        
        // Buscar fila en cache o DOM
        let row = this.cache.get(`row-${productId}`);
        
        if (!row || !document.contains(row)) {
            row = document.querySelector(`#productos-table-body tr[data-product-id="${productId}"]`);
            console.log('🔵 Fila encontrada en DOM:', row);
            
            if (!row) {
                console.warn(`⚠️ Fila del producto ${productId} no encontrada`);
                return;
            }
            
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
            
            if (!card) {
                console.warn(`⚠️ Card del producto ${productId} no encontrada`);
                return;
            }
            
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

        if (!selector) {
            console.warn(`⚠️ No hay selector para el campo: ${field}`);
            return;
        }

        const element = container.querySelector(selector);

        if (!element) {
            console.warn(`⚠️ Elemento no encontrado para campo: ${field} (selector: ${selector})`);
            console.warn('🔍 Contenedor:', container);
            console.warn('🔍 ViewType:', viewType);
            return;
        }

        // Obtener nuevo valor
        const newValue = this.getFieldValue(field, productData);
        const currentValue = this.getCurrentFieldValue(element, field);

        // Solo actualizar si el valor cambió
        if (newValue === currentValue) {
            console.log(`  ⏭️ Campo '${field}' sin cambios: ${currentValue}`);
            return;
        }

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

        // Log breve
        console.log(`  ✓ Campo '${field}' actualizado: ${currentValue} → ${newValue}`);
    }

    /**
     * 📝 Obtener valor de un campo desde los datos del producto
     */
    getFieldValue(field, productData) {
        switch (field) {
            case 'nombre':
                return productData.nombre_producto || 'Sin nombre';
            case 'categoria':
                return productData.nombre_categoria || productData.categoria_nombre || 'Sin categoría';
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
                console.log('📅 Fecha extraída:', { raw: fecha, split: fechaSplit });
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
        } else {
            return element.textContent.trim();
        }
    }

    /**
     * 🖊️ Establecer nuevo valor en un campo del DOM
     */
    setFieldValue(element, field, productData, newValue) {
        console.log(`🔧 setFieldValue: campo=${field}, newValue="${newValue}"`);
        
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
            
            // ✅ Usar función centralizada para calcular estado del stock
            const estadoStock = calcularEstadoStock(productData);
            
            // 🔍 LOG TEMPORAL PARA DIAGNÓSTICO
            console.log('🔍 STOCK UPDATE DEBUG:', {
                productId: productData.id_producto,
                stock_actual: stock,
                stock_minimo_raw: productData.stock_minimo_producto,
                stock_minimo_parsed: productData.stock_minimo_producto ? parseInt(productData.stock_minimo_producto) : null,
                resultado: estadoStock
            });
            
            // Para TABLA: Actualizar número y clase
            if (element.classList.contains('stock-number')) {
                element.textContent = stock;
                // Reemplazar solo la clase stock-*
                element.className = element.className.replace(/stock-\w+/g, '') + ' ' + estadoStock.clase;
                element.className = element.className.trim();
                
                // Actualizar también el texto de estado si existe
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
     * 🖼️ Obtener URL de imagen del producto
     */
    getProductImageUrl(producto, forceCacheBust = false) {
        const timestamp = forceCacheBust ? '?t=' + new Date().getTime() : '';
        if (producto.url_imagen_producto && producto.url_imagen_producto !== 'NULL') {
            return producto.url_imagen_producto + timestamp;
        } else if (producto.imagen_producto && producto.imagen_producto !== 'NULL') {
            return '/fashion-master/public/assets/img/products/' + producto.imagen_producto + timestamp;
        }
        return '/fashion-master/public/assets/img/default-product.jpg';
    }

    /**
     * 🎯 FUNCIÓN ESPECÍFICA: Actualizar solo el estado del producto
     * Ultra-rápido para cambios de estado
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
     * 📦 FUNCIÓN ESPECÍFICA: Actualizar solo el stock del producto
     * Ultra-rápido para cambios de stock
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
     * 💰 FUNCIÓN ESPECÍFICA: Actualizar solo el precio del producto
     */
    async updateProductPrecio(productId, newPrecio) {
        const productData = {
            id_producto: productId,
            precio_producto: newPrecio
        };
        
        await this.updateSingleProduct(productId, productData, ['precio']);
    }

    /**
     * 🖼️ FUNCIÓN ESPECÍFICA: Actualizar solo la imagen del producto
     */
    async updateProductImagen(productId, newImageUrl) {
        const productData = {
            id_producto: productId,
            url_imagen_producto: newImageUrl
        };
        
        await this.updateSingleProduct(productId, productData, ['imagen']);
    }

    /**
     * 📝 FUNCIÓN ESPECÍFICA: Actualizar solo el nombre del producto
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
                console.warn(`⚠️ Fila del producto ${productId} no encontrada, recargando tabla...`);
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
            console.log(`⏭️ Producto ${productId} sin cambios, omitiendo actualización`);
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
     * LEGACY: Actualizar producto en la vista grid (mantener compatibilidad)
     * @deprecated Usar updateSingleProduct() en su lugar
     */
    async updateProductInGrid(productId, productData) {
        // Buscar card en cache o DOM
        let card = this.cache.get(`card-${productId}`);
        
        if (!card || !document.contains(card)) {
            card = document.querySelector(`.product-card[data-product-id="${productId}"]`);
            
            if (!card) {
                console.warn(`⚠️ Card del producto ${productId} no encontrada, recargando grid...`);
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
            console.log(`⏭️ Card ${productId} sin cambios, omitiendo actualización`);
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
     * Eliminar producto de la tabla/grid con animación
     */
    async removeProduct(productId) {
        const currentView = this.getCurrentView();
        const element = currentView === 'grid' 
            ? document.querySelector(`.product-card[data-product-id="${productId}"]`)
            : document.querySelector(`#productos-table-body tr[data-product-id="${productId}"]`);
        
        if (!element) {
            console.warn(`⚠️ Elemento del producto ${productId} no encontrado`);
            return;
        }
        
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
        
        console.log(`✅ Producto ${productId} eliminado`);
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
     * Limpiar cache (útil al cambiar de vista o recargar)
     */
    clearCache() {
        this.cache.clear();
        console.log('🧹 Cache limpiado');
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
        
        // ✅ Usar función centralizada para calcular estado del stock
        const estadoStock = calcularEstadoStock(producto);
        
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
     * 📋 Obtener IDs de productos actualmente visibles
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
     * 🙈 Ocultar producto con animación smooth
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
     * 👁️ Mostrar producto con animación smooth
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
                console.log('⚠️ Producto nuevo detectado, recargando tabla');
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
        console.log('🗑️ SmoothTableUpdater destruido');
    }
}

// ===== EXPORTAR CLASE AL SCOPE GLOBAL =====
window.SmoothTableUpdater = SmoothTableUpdater;
console.log('✅ SmoothTableUpdater V3.0 - Field-Level Updates exportado al scope global');

// Crear instancia global con error handling
try {
    window.smoothTableUpdater = new SmoothTableUpdater();
    console.log('✅ Instancia global smoothTableUpdater V3.0 creada');
    
    // Agregar método de recarga segura
    window.smoothTableUpdater.safeReload = function() {
        console.log('🔄 Recarga segura activada');
        this.clearCache();
        this.dataCache.clear();
        if (typeof window.loadProducts === 'function') {
            window.loadProducts();
        } else if (typeof window.loadProductos === 'function') {
            window.loadProductos();
        } else {
            console.warn('⚠️ Función de carga de productos no encontrada');
        }
    };
    
    // Limpiar cache automáticamente cuando se cambia de vista
    const originalToggleView = window.toggleView;
    if (typeof originalToggleView === 'function') {
        window.toggleView = function(...args) {
            window.smoothTableUpdater.clearCache();
            return originalToggleView.apply(this, args);
        };
    }
    
    // ⚡ NUEVAS FUNCIONES RÁPIDAS GLOBALES
    window.updateProductEstado = async function(productId, newEstado) {
        if (window.smoothTableUpdater) {
            await window.smoothTableUpdater.updateProductEstado(productId, newEstado);
        }
    };
    
    window.updateProductStock = async function(productId, productData) {
        if (window.smoothTableUpdater) {
            await window.smoothTableUpdater.updateProductStock(productId, productData);
        }
    };
    
    window.updateProductPrecio = async function(productId, newPrecio) {
        if (window.smoothTableUpdater) {
            await window.smoothTableUpdater.updateProductPrecio(productId, newPrecio);
        }
    };
    
    window.updateProductImagen = async function(productId, newImageUrl) {
        if (window.smoothTableUpdater) {
            await window.smoothTableUpdater.updateProductImagen(productId, newImageUrl);
        }
    };
    
    console.log('✅ Funciones globales rápidas creadas: updateProductEstado, updateProductStock, updateProductPrecio, updateProductImagen');
    
} catch (error) {
    console.error('❌ Error creando instancia de smoothTableUpdater:', error);
    window.smoothTableUpdater = null;
}

// Exportar para uso en otros módulos
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SmoothTableUpdater;
}

} else {
    console.warn('⚠️ SmoothTableUpdater ya existe - saltando redeclaración');
    // Si ya existe, solo asegurar que la instancia global esté creada
    if (!window.smoothTableUpdater) {
        try {
            window.smoothTableUpdater = new SmoothTableUpdater();
            console.log('✅ Instancia global smoothTableUpdater creada (segunda oportunidad)');
        } catch (error) {
            console.error('❌ Error creando instancia:', error);
        }
    }
}

// ===== UTILIDADES GLOBALES =====

/**
 * Función helper para actualizar múltiples productos de forma eficiente
 */
window.updateMultipleProducts = async function(products) {
    if (!window.smoothTableUpdater) {
        return;
    }
    
    console.log(`📦 Actualizando ${products.length} productos...`);
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
