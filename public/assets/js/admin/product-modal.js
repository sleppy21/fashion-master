/**
 * ============================================================================
 * PRODUCT MODAL - Gestión del Modal de Productos
 * ============================================================================
 * Maneja la apertura, cierre, carga de datos y guardado de productos
 */

const ProductModal = (() => {
    'use strict';

    // ============================================================================
    // ESTADO PRIVADO
    // ============================================================================
    
    let currentProductId = null;
    let isLoading = false;

    // ============================================================================
    // CONFIGURACIÓN
    // ============================================================================
    
    const CONFIG = {
        get apiUrl() {
            return window.PHP_CONFIG?.apiProductController || '';
        },
        get imagesUrl() {
            return window.PHP_CONFIG?.imagesUrl || '';
        },
        maxImageSize: 2 * 1024 * 1024, // 2MB
        allowedImageTypes: ['image/jpeg', 'image/png', 'image/gif', 'image/webp']
    };

    // ============================================================================
    // HELPERS (usando funciones del módulo principal)
    // ============================================================================
    
    // Referencia a la función escapeHtml del módulo adminProductos
    const escapeHtml = (text) => {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    };

    // ============================================================================
    // FUNCIONES DE APERTURA/CIERRE
    // ============================================================================

    /**
     * Abre el modal de producto
     * @param {number|null} productId - ID del producto a editar (null para crear)
     */
    async function open(productId = null) {
        console.log('🔧 Abriendo modal de producto...', productId ? `ID: ${productId}` : 'Nuevo producto');
        
        currentProductId = productId;

        const modal = document.getElementById('productModal');
        if (!modal) {
            console.error('❌ No se encontró el modal de producto');
            return;
        }

        // Resetear formulario
        const form = document.getElementById('productForm');
        if (form) {
            form.reset();
            clearErrors();
        }

        // Cambiar título
        const title = modal.querySelector('.product-modal-title');
        if (title) {
            title.textContent = productId ? 'Editar Producto' : 'Nuevo Producto';
        }

        // Resetear imagen
        resetImagePreview();

        // Configurar listeners para actualización en tiempo real
        setupPreviewListeners();

        // Cargar categorías y marcas
        await Promise.all([
            loadCategories(),
            loadBrands()
        ]);

        // Si es edición, cargar datos
        if (productId) {
            await loadProductData(productId);
        } else {
            // Si es nuevo producto, actualizar la tarjeta con valores por defecto
            updateEstadoToggle(); // Inicializar toggle en estado activo
            updatePreviewCard();
        }

        // Prevenir scroll en body
        document.body.classList.add('modal-open');

        // Mostrar modal: primero cambiar display, luego agregar clase show
        modal.style.display = 'block';
        requestAnimationFrame(() => {
            modal.classList.add('show');
        });
        
        // Cerrar menú flotante si está abierto
        if (window.closeFloatingActions) {
            window.closeFloatingActions();
        }
    }

    /**
     * Cierra el modal de producto
     */
    function close() {
        const modal = document.getElementById('productModal');
        if (modal) {
            modal.classList.remove('show');
            // Esperar a que termine la animación antes de ocultar
            setTimeout(() => {
                modal.style.display = 'none';
                // Permitir scroll en body nuevamente
                document.body.classList.remove('modal-open');
                // Resetear estado
                currentProductId = null;
                resetImagePreview();
                clearErrors();
            }, 400);
        }
    }

    // ============================================================================
    // CARGA DE DATOS
    // ============================================================================

    /**
     * Carga los datos de un producto existente
     * @param {number} productId - ID del producto
     */
    async function loadProductData(productId) {
        try {
            showModalLoading(true);

            const response = await fetch(`${CONFIG.apiUrl}?action=get&id=${productId}`);
            const data = await response.json();

            console.log('📦 Respuesta del servidor:', data);

            if (data.success && (data.product || data.data)) {
                const product = data.product || data.data;
                console.log('✅ Producto cargado:', product);

                // Llenar formulario con los IDs correctos del HTML
                setFieldValue('product_id', product.id_producto);
                setFieldValue('nombre_producto', product.nombre_producto);
                setFieldValue('codigo', product.codigo || product.codigo_producto);
                setFieldValue('descripcion_producto', product.descripcion_producto);
                setFieldValue('precio_producto', product.precio_producto);
                setFieldValue('id_categoria', product.id_categoria);
                setFieldValue('id_marca', product.id_marca);
                setFieldValue('genero_producto', product.genero_producto);
                setFieldValue('stock_actual_producto', product.stock_producto || product.stock_actual_producto);
                setFieldValue('stock_minimo_producto', product.stock_minimo_producto || 0);
                setFieldValue('estado', product.estado || product.estado_producto);
                // Descuento
                setFieldValue('descuento_porcentaje_producto', product.descuento_porcentaje_producto || 0);

                // Cargar subcategorías y seleccionar la del producto
                if (product.id_categoria) {
                    await loadSubcategories(product.id_categoria);
                    setFieldValue('id_subcategoria', product.id_subcategoria);
                }

                // Mostrar imagen actual
                if (product.imagen_producto) {
                    showImagePreview(getProductImageUrl(product.imagen_producto));
                }

                // Actualizar botón toggle de estado
                updateEstadoToggle();

                // Actualizar tarjeta de previsualización con todos los datos
                updatePreviewCard();

            } else {
                throw new Error(data.message || 'Error al cargar producto');
            }

        } catch (error) {
            console.error('❌ Error al cargar producto:', error);
            alert('Error al cargar producto: ' + error.message);
        } finally {
            showModalLoading(false);
        }
    }

    /**
     * Carga las categorías en el select del modal
     */
    async function loadCategories() {
        try {
            const response = await fetch(`${CONFIG.apiUrl}?action=get_categories`);
            const data = await response.json();
            
            if (data.success && data.data) {
                const select = document.getElementById('id_categoria');
                if (select) {
                    select.innerHTML = '<option value="">Seleccionar categoría</option>' +
                        data.data.map(cat => 
                            `<option value="${cat.id_categoria}">${escapeHtml(cat.nombre_categoria)}</option>`
                        ).join('');
                    console.log('✅ Categorías cargadas:', data.data.length);
                    
                    // Agregar evento para cargar subcategorías cuando cambie la categoría
                    select.addEventListener('change', function() {
                        loadSubcategories(this.value);
                    });
                }
            }
        } catch (error) {
            console.error('❌ Error al cargar categorías:', error);
        }
    }

    /**
     * Carga las subcategorías según la categoría seleccionada
     * @param {number} categoryId - ID de la categoría
     */
    async function loadSubcategories(categoryId) {
        const select = document.getElementById('id_subcategoria');
        
        if (!categoryId) {
            // Si no hay categoría seleccionada, deshabilitar subcategoría
            select.innerHTML = '<option value="">Primero selecciona una categoría</option>';
            select.disabled = true;
            return;
        }
        
        try {
            select.disabled = true;
            select.innerHTML = '<option value="">Cargando subcategorías...</option>';
            
            const response = await fetch(`${CONFIG.apiUrl}?action=get_subcategories&id_categoria=${categoryId}`);
            const data = await response.json();
            
            if (data.success && data.data && data.data.length > 0) {
                select.innerHTML = '<option value="">Seleccionar subcategoría</option>' +
                    data.data.map(subcat => 
                        `<option value="${subcat.id_subcategoria}">${escapeHtml(subcat.nombre_subcategoria)}</option>`
                    ).join('');
                select.disabled = false;
                console.log('✅ Subcategorías cargadas:', data.data.length);
            } else {
                select.innerHTML = '<option value="">No hay subcategorías disponibles</option>';
                select.disabled = true;
            }
        } catch (error) {
            console.error('❌ Error al cargar subcategorías:', error);
            select.innerHTML = '<option value="">Error al cargar subcategorías</option>';
            select.disabled = true;
        }
    }

    /**
     * Carga las marcas en el select del modal
     */
    async function loadBrands() {
        try {
            const response = await fetch(`${CONFIG.apiUrl}?action=get_marcas`);
            const data = await response.json();
            
            if (data.success && data.data) {
                const select = document.getElementById('id_marca');
                if (select) {
                    select.innerHTML = '<option value="">Seleccionar marca</option>' +
                        data.data.map(brand => 
                            `<option value="${brand.id_marca}">${escapeHtml(brand.nombre_marca)}</option>`
                        ).join('');
                    console.log('✅ Marcas cargadas:', data.data.length);
                }
            }
        } catch (error) {
            console.error('❌ Error al cargar marcas:', error);
        }
    }

    // ============================================================================
    // GUARDADO DE DATOS
    // ============================================================================

    /**
     * Guarda el producto (crear o actualizar)
     */
    async function save() {
        try {
            // Validar formulario
            if (!validateForm()) {
                return;
            }

            showModalLoading(true);

            const formData = new FormData(document.getElementById('productForm'));
            const action = currentProductId ? 'update' : 'create';
            
            if (currentProductId) {
                formData.append('id', currentProductId);
            }
            formData.append('action', action);

            const response = await fetch(CONFIG.apiUrl, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                alert(currentProductId ? 'Producto actualizado correctamente' : 'Producto creado correctamente');
                
                // Cerrar modal
                close();
                
                // Actualizar en tiempo real sin recargar página
                if (window.adminProductos) {
                    if (currentProductId) {
                        // Si es actualización, recargar solo ese producto
                        await window.adminProductos.refreshProduct(currentProductId);
                    } else {
                        // Si es creación, recargar toda la lista
                        await window.adminProductos.loadProducts();
                    }
                }
            } else {
                throw new Error(data.message || 'Error al guardar producto');
            }

        } catch (error) {
            console.error('❌ Error al guardar producto:', error);
            alert('Error al guardar producto: ' + error.message);
        } finally {
            showModalLoading(false);
        }
    }

    // ============================================================================
    // MANEJO DE IMAGEN
    // ============================================================================

    /**
     * Previsualiza la imagen seleccionada
     * 
     * 🎯 OPTIMIZACIÓN DE IMÁGENES:
     * - Primera vez: Se genera un ID único (product_xxxxx.jpg)
     * - Reemplazo: Se reutiliza el MISMO ID, solo cambia extensión si es necesaria
     * - El backend automáticamente elimina la imagen anterior del servidor
     * - Evita acumulación de archivos huérfanos en /public/assets/img/products/
     * 
     * @param {Event} event - Evento del input file
     */
    function previewImage(event) {
        const file = event.target.files[0];
        
        if (!file) return;

        // Validar tipo
        if (!CONFIG.allowedImageTypes.includes(file.type)) {
            alert('Formato de imagen no permitido. Use JPG, PNG, GIF o WEBP');
            event.target.value = '';
            return;
        }

        // Validar tamaño
        if (file.size > CONFIG.maxImageSize) {
            alert('La imagen es demasiado grande. Tamaño máximo: 2MB');
            event.target.value = '';
            return;
        }

        // Mostrar preview (la optimización de ID se maneja en el backend)
        const reader = new FileReader();
        reader.onload = (e) => {
            showImagePreview(e.target.result);
        };
        reader.readAsDataURL(file);
    }

    /**
     * Muestra la vista previa de una imagen con estilo de tarjeta de tienda
     * @param {string} imageUrl - URL de la imagen
     */
    function showImagePreview(imageUrl) {
        // Buscar la imagen dentro de la tarjeta de producto
        const productCard = document.querySelector('#imagePreviewWrapper .product-card-modern');
        if (!productCard) return;
        
        const productImage = productCard.querySelector('.product-image');
        if (productImage) {
            // Limpiar src anterior para forzar recarga
            productImage.src = '';
            
            // Establecer nueva imagen
            productImage.src = imageUrl;
            
            // Función para ajustar altura
            const adjustHeight = () => {
                if (window.adjustCardImageHeight) {
                    window.adjustCardImageHeight(productCard);
                }
            };
            
            // Ajustar cuando la imagen cargue
            productImage.onload = adjustHeight;
            
            // Si la imagen ya está cacheada, ajustar inmediatamente
            if (productImage.complete && productImage.naturalWidth > 0) {
                adjustHeight();
            }
        }
        
        // Mostrar botón de quitar
        const btnRemove = document.getElementById('btnRemoveImage');
        if (btnRemove) {
            btnRemove.style.display = 'inline-flex';
        }
    }

    /**
     * Actualiza toda la tarjeta de previsualización con los datos del formulario
     */
    function updatePreviewCard() {
        const productCard = document.querySelector('#imagePreviewWrapper .product-card-modern');
        if (!productCard) return;

        // Obtener valores del formulario
        const nombre = document.getElementById('nombre_producto')?.value || 'Nombre del Producto';
        const precio = parseFloat(document.getElementById('precio_producto')?.value || 0);
        const descuento = parseFloat(document.getElementById('descuento_porcentaje_producto')?.value || 0);
        const categoria = document.getElementById('id_categoria')?.selectedOptions[0]?.text || 'CATEGORÍA';
        const stock = parseInt(document.getElementById('stock_actual_producto')?.value || 0);

        // Actualizar nombre
        const nameElement = productCard.querySelector('.product-name a');
        if (nameElement) {
            nameElement.textContent = nombre;
        }

        // Actualizar categoría
        const categoryElement = productCard.querySelector('.product-category');
        if (categoryElement) {
            categoryElement.textContent = categoria.toUpperCase();
        }

        // Calcular precios
        const precioFinal = precio - (precio * descuento / 100);

        // Actualizar precio
        const priceCurrentElement = productCard.querySelector('.price-current');
        if (priceCurrentElement) {
            priceCurrentElement.textContent = 'S/ ' + precioFinal.toFixed(2);
        }

        // Actualizar/mostrar precio original si hay descuento
        let priceOriginalElement = productCard.querySelector('.price-original');
        if (descuento > 0) {
            if (!priceOriginalElement) {
                priceOriginalElement = document.createElement('span');
                priceOriginalElement.className = 'price-original';
                productCard.querySelector('.product-price')?.appendChild(priceOriginalElement);
            }
            priceOriginalElement.textContent = 'S/ ' + precio.toFixed(2);
            priceOriginalElement.style.display = 'inline';
        } else if (priceOriginalElement) {
            priceOriginalElement.style.display = 'none';
        }

        // Actualizar badge de descuento
        const badges = productCard.querySelector('.product-badges');
        if (badges) {
            let badgeSale = badges.querySelector('.badge-sale');
            if (descuento > 0) {
                if (!badgeSale) {
                    badgeSale = document.createElement('span');
                    badgeSale.className = 'badge-modern badge-sale';
                    badges.appendChild(badgeSale);
                }
                badgeSale.textContent = '-' + Math.round(descuento) + '%';
                badgeSale.style.display = 'inline-block';
                
                // Ocultar badge de agotado si existe
                const badgeOutOfStock = badges.querySelector('.badge-out-of-stock');
                if (badgeOutOfStock) badgeOutOfStock.style.display = 'none';
            } else if (badgeSale) {
                badgeSale.style.display = 'none';
            }

            // Mostrar badge de agotado si stock = 0
            let badgeOutOfStock = badges.querySelector('.badge-out-of-stock');
            if (stock === 0) {
                if (!badgeOutOfStock) {
                    badgeOutOfStock = document.createElement('span');
                    badgeOutOfStock.className = 'badge-modern badge-out-of-stock';
                    badgeOutOfStock.textContent = 'AGOTADO';
                    badges.appendChild(badgeOutOfStock);
                }
                badgeOutOfStock.style.display = 'inline-block';
            } else if (badgeOutOfStock) {
                badgeOutOfStock.style.display = 'none';
            }
        }

        // Actualizar advertencia de stock bajo
        let stockWarning = productCard.querySelector('.stock-warning');
        if (stock > 0 && stock <= 5) {
            if (!stockWarning) {
                stockWarning = document.createElement('div');
                stockWarning.className = 'stock-warning';
                stockWarning.innerHTML = '<i class="fa fa-exclamation-circle"></i> ';
                productCard.querySelector('.product-info')?.appendChild(stockWarning);
            }
            const warningText = stockWarning.querySelector('span') || document.createElement('span');
            warningText.textContent = '¡Ultimos ' + stock + '!' ;
            if (!stockWarning.querySelector('span')) {
                stockWarning.appendChild(warningText);
            }
            stockWarning.style.display = 'block';
        } else if (stockWarning) {
            stockWarning.style.display = 'none';
        }

        // Actualizar badge de estado (activo/inactivo)
        const estado = document.getElementById('estado')?.value || 'activo';
        updateEstadoBadge(productCard, estado);
    }

    /**
     * Actualiza el badge de estado en la tarjeta de previsualización
     * @param {HTMLElement} productCard - Tarjeta de producto
     * @param {string} estado - Estado actual ('activo' o 'inactivo')
     */
    function updateEstadoBadge(productCard, estado) {
        if (!productCard) return;

        const badges = productCard.querySelector('.product-badges');
        if (!badges) return;

        let badgeEstado = badges.querySelector('.badge-estado');
        
        if (!badgeEstado) {
            badgeEstado = document.createElement('span');
            badgeEstado.className = 'badge-modern badge-estado';
            badges.appendChild(badgeEstado);
        }

        // Remover clases de estado anteriores
        badgeEstado.classList.remove('badge-activo', 'badge-inactivo');
        
        // Agregar clase según estado
        if (estado === 'activo') {
            badgeEstado.classList.add('badge-activo');
            badgeEstado.innerHTML = '<i class="fas fa-check-circle"></i> ACTIVO';
        } else {
            badgeEstado.classList.add('badge-inactivo');
            badgeEstado.innerHTML = '<i class="fas fa-times-circle"></i> INACTIVO';
        }
    }

    /**
     * Elimina la imagen seleccionada
     */
    /**
     * Elimina la imagen seleccionada y restaura la imagen por defecto
     */
    function removeImage() {
        const input = document.getElementById('imagen_producto');
        if (input) {
            input.value = '';
        }
        resetImagePreview();
    }

    /**
     * Resetea la vista previa de imagen al estado inicial (imagen por defecto)
     */
    function resetImagePreview() {
        const productCard = document.querySelector('#imagePreviewWrapper .product-card-modern');
        if (!productCard) return;
        
        const productImage = productCard.querySelector('.product-image');
        if (productImage) {
            // Usar imagen por defecto
            const defaultImage = 'public/assets/img/default-product.jpg';
            productImage.src = defaultImage;
            
            // Función para ajustar altura
            const adjustHeight = () => {
                if (window.adjustCardImageHeight) {
                    window.adjustCardImageHeight(productCard);
                    console.log('✅ Adapter aplicado a imagen por defecto');
                }
            };
            
            // Ajustar cuando la imagen cargue
            productImage.onload = adjustHeight;
            
            // Si la imagen ya está cacheada, ajustar inmediatamente
            if (productImage.complete && productImage.naturalWidth > 0) {
                adjustHeight();
            }
        }
        
        // Ocultar botón de quitar imagen
        const btnRemove = document.getElementById('btnRemoveImage');
        if (btnRemove) {
            btnRemove.style.display = 'none';
        }
    }

    /**
     * Configurar listeners para actualizar la tarjeta en tiempo real
     */
    function setupPreviewListeners() {
        const fieldsToWatch = [
            'nombre_producto',
            'precio_producto', 
            'descuento_porcentaje_producto',
            'id_categoria',
            'stock_actual_producto',
            'estado' // Agregar estado a la lista de campos observados
        ];

        fieldsToWatch.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('input', updatePreviewCard);
                field.addEventListener('change', updatePreviewCard);
            }
        });
    }

    // ============================================================================
    // TOGGLE DE ESTADO
    // ============================================================================

    /**
     * Cambia el estado del producto entre activo/inactivo
     */
    function toggleEstado() {
        const estadoSelect = document.getElementById('estado');
        const btnToggle = document.getElementById('btnToggleEstado');
        
        if (!estadoSelect || !btnToggle) return;
        
        // Cambiar valor del select
        const nuevoEstado = estadoSelect.value === 'activo' ? 'inactivo' : 'activo';
        estadoSelect.value = nuevoEstado;
        
        // Actualizar apariencia del botón
        updateEstadoToggle();
        
        // Actualizar previsualización
        updatePreviewCard();
        
        console.log(`🔄 Estado cambiado a: ${nuevoEstado}`);
    }

    /**
     * Actualiza la apariencia del botón toggle según el estado actual
     */
    function updateEstadoToggle() {
        const estadoSelect = document.getElementById('estado');
        const btnToggle = document.getElementById('btnToggleEstado');
        
        if (!estadoSelect || !btnToggle) return;
        
        const estadoActual = estadoSelect.value;
        
        // Remover clases anteriores
        btnToggle.classList.remove('activo', 'inactivo');
        
        // Agregar clase según estado
        btnToggle.classList.add(estadoActual);
        
        // Actualizar título
        btnToggle.title = estadoActual === 'activo' 
            ? 'Estado: Activo (click para desactivar)' 
            : 'Estado: Inactivo (click para activar)';
    }

    // ============================================================================
    // VALIDACIÓN
    // ============================================================================

    /**
     * Valida el formulario antes de guardar
     * @returns {boolean} - True si es válido
     */
    function validateForm() {
        clearErrors();
        let isValid = true;

        const requiredFields = [
            { id: 'nombre_producto', message: 'El nombre es requerido' },
            { id: 'codigo', message: 'El código es requerido' },
            { id: 'precio_producto', message: 'El precio es requerido' },
            { id: 'id_categoria', message: 'La categoría es requerida' },
            { id: 'id_marca', message: 'La marca es requerida' },
            { id: 'genero_producto', message: 'El género es requerido' },
            { id: 'stock_actual_producto', message: 'El stock es requerido' }
        ];

        requiredFields.forEach(field => {
            const element = document.getElementById(field.id);
            if (element && !element.value.trim()) {
                showFieldError(field.id, field.message);
                isValid = false;
            }
        });

        // Validar precio
        const precio = document.getElementById('precio_producto');
        if (precio && parseFloat(precio.value) <= 0) {
            showFieldError('precio_producto', 'El precio debe ser mayor a 0');
            isValid = false;
        }

        return isValid;
    }

    /**
     * Muestra un error en un campo específico
     * @param {string} fieldId - ID del campo
     * @param {string} message - Mensaje de error
     */
    function showFieldError(fieldId, message) {
        const errorElement = document.getElementById(`error-${fieldId}`);
        const fieldGroup = document.getElementById(fieldId)?.closest('.product-form-group');
        
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        }
        
        if (fieldGroup) {
            fieldGroup.classList.add('has-error');
        }
    }

    /**
     * Limpia todos los errores del formulario
     */
    function clearErrors() {
        document.querySelectorAll('.product-form-error').forEach(error => {
            error.textContent = '';
            error.style.display = 'none';
        });
        
        document.querySelectorAll('.product-form-group').forEach(group => {
            group.classList.remove('has-error');
        });
    }

    // ============================================================================
    // UTILIDADES
    // ============================================================================

    /**
     * Establece el valor de un campo del formulario
     * @param {string} id - ID del campo
     * @param {*} value - Valor a establecer
     */
    function setFieldValue(id, value) {
        const field = document.getElementById(id);
        if (field) field.value = value || '';
    }

    /**
     * Obtiene la URL completa de una imagen de producto
     * @param {string} imageName - Nombre del archivo de imagen
     * @returns {string} - URL completa
     */
    function getProductImageUrl(imageName) {
        if (!imageName) return '';
        if (imageName.startsWith('http')) return imageName;
        return `${CONFIG.imagesUrl}/${imageName}`;
    }

    /**
     * Muestra el indicador de carga (específico del modal)
     * @param {boolean} show - Mostrar u ocultar
     */
    function showModalLoading(show) {
        isLoading = show;
        const btnSave = document.getElementById('btnSaveProduct');
        const btnText = document.getElementById('btnSaveText');
        
        if (btnSave) {
            btnSave.disabled = show;
        }
        
        if (btnText) {
            btnText.textContent = show ? 'Guardando...' : 'Guardar Producto';
        }
    }

    // ============================================================================
    // INICIALIZACIÓN
    // ============================================================================

    /**
     * Inicializa el modal (listeners, etc)
     */
    function init() {
        console.log('🎬 Inicializando ProductModal...');
        
        // Listener para cerrar modal al hacer clic en el overlay
        const modalOverlay = document.getElementById('productModal');
        if (modalOverlay) {
            modalOverlay.addEventListener('click', (e) => {
                if (e.target === modalOverlay) {
                    close();
                }
            });
        }

        // Listener para tecla ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const modal = document.getElementById('productModal');
                if (modal && modal.classList.contains('show')) {
                    close();
                }
            }
        });
        
        console.log('✅ ProductModal inicializado');
    }

    // Auto-inicializar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // ============================================================================
    // API PÚBLICA
    // ============================================================================

    return {
        open,
        close,
        save,
        previewImage,
        removeImage,
        loadProductData,
        updatePreviewCard,
        toggleEstado,
        updateEstadoToggle
    };

})();

// Exponer globalmente para compatibilidad con HTML onclick
window.ProductModal = ProductModal;
window.closeProductModal = ProductModal.close;
window.saveProduct = ProductModal.save;
window.previewProductImage = ProductModal.previewImage;
window.removeProductImage = ProductModal.removeImage;
