/**
 * CLASE PARA GESTIÓN DE MODALES DE PRODUCTOS
 * Sistema modular y reutilizable para todos los modales
 */

// ⚠️ PREVENIR REDECLARACIÓN: Solo declarar si no existe
if (typeof ProductModalManager === 'undefined') {
    console.log('✅ Declarando ProductModalManager por primera vez');
    
class ProductModalManager {
    constructor() {
        this.currentModal = null;
        this.currentProductId = null;
        this.originalBodyOverflow = '';
        this.config = {
            // La URL se establece dinámicamente desde config.js
            get apiUrl() { return AppConfig.getApiUrl('ProductController.php'); },
            animationDuration: 300
        };
        
        this.init();
    }
    
    init() {
        this.setupGlobalEventListeners();
        this.clearModalCache();
    }
    
    // ===== LIMPIEZA DE CACHE =====
    
    clearModalCache() {
        this.currentProductId = null;
        this.currentModal = null;
        
        // Limpiar cualquier dato temporal que pueda estar cacheado
        if (window.tempProductData) {
            delete window.tempProductData;
        }
        
        // Remover TODOS los modales de productos que puedan estar en el DOM
        const allProductModals = document.querySelectorAll('[id^="product-modal"], [id^="status-modal"], [id^="stock-modal"]');
        allProductModals.forEach(modal => {
            if (modal && modal.parentNode) {
                modal.remove();
            }
        });
        
        // También limpiar cualquier modal-overlay genérico
        const orphanModals = document.querySelectorAll('.modal-overlay');
        orphanModals.forEach(modal => {
            if (modal && modal.parentNode) {
                modal.remove();
            }
        });
    }
    
    setupGlobalEventListeners() {
        // ESC para cerrar modales
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.currentModal) {
                e.preventDefault();
                this.closeModal();
            }
        });
        
        // Click en overlay para cerrar - MEJORADO para permitir animación
        document.addEventListener('click', (e) => {
            // Solo cerrar si se hace click directamente en el overlay (fondo)
            // Y si el modal NO está en proceso de cierre
            if (e.target.classList.contains('modal-overlay') && 
                this.currentModal && 
                !this.currentModal.classList.contains('hide')) {
                e.preventDefault();
                e.stopPropagation();
                this.closeModal();
            }
        });
    }
    
    // ===== GESTIÓN DE SCROLL - OCULTAR SCROLLBARS SIEMPRE =====
    // Mantener scrollbars ocultos en todo momento PERO permitir scroll funcional
    
    preventBodyScroll() {
        // Ocultar scrollbars pero PERMITIR scroll vertical - FORZADO con !important
        document.documentElement.style.setProperty('overflow-y', 'auto', 'important');
        document.documentElement.style.setProperty('overflow-x', 'hidden', 'important');
        document.body.style.setProperty('overflow-y', 'auto', 'important');
        document.body.style.setProperty('overflow-x', 'hidden', 'important');
        document.documentElement.style.setProperty('scrollbar-width', 'none', 'important');
        document.body.style.setProperty('scrollbar-width', 'none', 'important');
        document.documentElement.style.setProperty('-ms-overflow-style', 'none', 'important');
        document.body.style.setProperty('-ms-overflow-style', 'none', 'important');
    }
    
    restoreBodyScroll() {
        // Restaurar scroll funcional PERO mantener scrollbars ocultos - FORZADO con !important
        document.documentElement.style.setProperty('overflow-y', 'auto', 'important');
        document.documentElement.style.setProperty('overflow-x', 'hidden', 'important');
        document.body.style.setProperty('overflow-y', 'auto', 'important');
        document.body.style.setProperty('overflow-x', 'hidden', 'important');
        document.documentElement.style.setProperty('scrollbar-width', 'none', 'important');
        document.body.style.setProperty('scrollbar-width', 'none', 'important');
        document.documentElement.style.setProperty('-ms-overflow-style', 'none', 'important');
        document.body.style.setProperty('-ms-overflow-style', 'none', 'important');
    }
    
    // ===== MODAL GENÉRICO =====
    
    createModal(id, size = 'medium') {
        // Eliminar modal existente si existe
        const existingModal = document.getElementById(id);
        if (existingModal) {
            existingModal.remove();
        }
        
        // Limpiar cualquier modal huérfano con el mismo ID
        const orphanModals = document.querySelectorAll(`[id="${id}"]`);
        orphanModals.forEach(modal => {
            modal.remove();
        });
        
        const modal = document.createElement('div');
        modal.id = id;
        modal.className = 'modal-overlay';
        modal.innerHTML = `
            <div class="modal-container size-${size}">
                <div class="modal-header">
                    <h2 class="modal-title"></h2>
                    <button class="modal-close" type="button">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body"></div>
                <div class="modal-footer"></div>
            </div>
        `;
        
        // NO agregar al DOM aquí - dejar que showModal() lo haga
        // document.body.appendChild(modal); // ❌ COMENTADO
        
        // Agregar event listener al botón cerrar
        const closeBtn = modal.querySelector('.modal-close');
        closeBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.closeModal();
        });
        
        return modal;
    }
    
    showModal(modalElement) {
        this.currentModal = modalElement;
        this.preventBodyScroll();
        
        // Agregar al DOM
        if (!modalElement.parentNode) {
            document.body.appendChild(modalElement);
        }
        
        // Limpiar clases y establecer estado inicial con !important
        modalElement.classList.remove('show', 'hide', 'closing');
        modalElement.style.setProperty('opacity', '0', 'important');
        modalElement.style.setProperty('visibility', 'visible', 'important');
        modalElement.style.setProperty('display', 'flex', 'important');
        
        // Forzar reflow
        modalElement.offsetHeight;
        
        // Fade in con animación CSS + estilos inline
        requestAnimationFrame(() => {
            modalElement.classList.add('show');
            modalElement.style.setProperty('animation', 'overlayFadeInModal 0.3s ease-out forwards', 'important');
            modalElement.style.setProperty('transition', 'opacity 0.3s ease-out', 'important');
            modalElement.style.setProperty('opacity', '1', 'important');
        });
    }
    
    closeModal() {
        // GUARD: Prevenir ejecuciones duplicadas
        if (!this.currentModal) {
            this.currentModal = document.querySelector('.modal-overlay');
            if (!this.currentModal) return;
        }
        
        if (this.currentModal.classList.contains('closing')) {
            return; // Ya está cerrándose
        }
        
        this.currentProductId = null;
        const modalElement = this.currentModal;
        
        // FORZAR fade out con múltiples métodos para asegurar que se vea
        // 1. Agregar clase closing (activa la animación CSS)
        modalElement.classList.add('closing');
        modalElement.classList.remove('show');
        
        // 2. FORZAR con estilos inline y !important (por si CSS no funciona)
        modalElement.style.setProperty('animation', 'overlayFadeOutModal 0.3s ease-in forwards', 'important');
        modalElement.style.setProperty('transition', 'opacity 0.3s ease-in', 'important');
        modalElement.style.setProperty('opacity', '0', 'important');
        modalElement.style.setProperty('pointer-events', 'none', 'important');
        
        // Remover del DOM después de la animación
        setTimeout(() => {
            if (modalElement && modalElement.parentNode) {
                modalElement.remove();
            }
            this.currentModal = null;
            this.restoreBodyScroll();
            this.clearModalCache();
        }, 350); // 300ms animación + 50ms buffer
    }
    
    // ===== MODAL DE CREAR/EDITAR PRODUCTO =====
    
    showCreateProductModal() {
        this.clearModalCache();
        
        const modalId = 'product-modal-create';
        const modal = this.createModal(modalId, 'large');
        const title = modal.querySelector('.modal-title');
        const body = modal.querySelector('.modal-body');
        const footer = modal.querySelector('.modal-footer');
        
        title.innerHTML = '<i class="fas fa-plus-circle"></i> Crear Nuevo Producto';
        body.innerHTML = this.getProductFormHTML();
        footer.innerHTML = this.getProductFormFooterHTML('create');
        
        Promise.all([this.loadCategories(), this.loadBrands()]).then(() => {
            this.showModal(modal);
            this.setupProductFormEvents(modal, 'create');
        });
    }
    
    async showEditProductModal(productId) {
        this.clearModalCache();
        
        const modalId = `product-modal-edit-${productId}`;
        const modal = this.createModal(modalId, 'large');
        const title = modal.querySelector('.modal-title');
        const body = modal.querySelector('.modal-body');
        const footer = modal.querySelector('.modal-footer');
        
        title.innerHTML = '<i class="fas fa-edit"></i> Editar Producto';
        
        body.innerHTML = this.getProductFormHTML();
        footer.innerHTML = this.getProductFormFooterHTML('edit');
        
        try {
            await this.loadCategories();
            await this.loadBrands();
            await this.loadProductData(productId, modal);
            this.showModal(modal);
            this.setupProductFormEvents(modal, 'edit', productId);
        } catch (error) {
            this.showNotification('Error al cargar el producto', 'error');
        }
    }
    
    async showViewProductModal(productId) {
        this.clearModalCache();
        
        if (!productId || productId === 'undefined' || productId === 'null') {
            return;
        }
        
        this.currentProductId = productId;
        
        const modalId = `product-modal-view-${productId}`;
        const modal = this.createModal(modalId, 'large');
        const title = modal.querySelector('.modal-title');
        const body = modal.querySelector('.modal-body');
        const footer = modal.querySelector('.modal-footer');
        
        title.innerHTML = '<i class="fas fa-eye"></i> Ver Producto';
        body.innerHTML = this.getProductFormHTML();
        footer.innerHTML = '<button type="button" class="modal-btn modal-btn-secondary" onclick="productModalManager.closeModal()"><i class="fas fa-times"></i> Cerrar</button>';
        
        try {
            // Cargar todo en paralelo para mayor velocidad
            await Promise.all([
                this.loadCategories(),
                this.loadBrands(),
                this.loadProductData(productId, modal)
            ]);
            
            this.disableFormInputs(modal);
            this.showModal(modal);
        } catch (error) {
            this.showNotification('Error al cargar el producto', 'error');
        }
    }
    
    // ===== MODAL DE ESTADO =====
    
    showStatusModal(productId, currentStatus, productName) {
        
        const modal = this.createModal('status-modal', 'small');
        const title = modal.querySelector('.modal-title');
        const body = modal.querySelector('.modal-body');
        const footer = modal.querySelector('.modal-footer');
        
        const newStatus = !currentStatus;
        const actionText = newStatus ? 'Activar' : 'Desactivar';
        const iconClass = newStatus ? 'fas fa-check-circle' : 'fas fa-ban';
        const colorClass = newStatus ? 'success' : 'error';
        
        title.innerHTML = `<i class="${iconClass}"></i> ${actionText} Producto`;
        
        body.innerHTML = `
            <div class="status-toggle">
                <div class="status-icon ${newStatus ? 'active' : 'inactive'}">
                    <i class="${iconClass}"></i>
                </div>
                <div class="status-info">
                    <h4>${actionText} "${productName}"</h4>
                    <p>¿Estás seguro de que deseas ${actionText.toLowerCase()} este producto?</p>
                </div>
            </div>
            
            <div class="modal-alert modal-alert-${colorClass}">
                <i class="${iconClass}"></i>
                <div>
                    <strong>Importante:</strong> 
                    ${newStatus 
                        ? 'El producto será visible en la tienda y estará disponible para compra.' 
                        : 'El producto será ocultado de la tienda y no estará disponible para compra.'}
                </div>
            </div>
        `;
        
        footer.innerHTML = `
            <button type="button" class="modal-btn modal-btn-secondary" onclick="productModalManager.closeModal()">
                <i class="fas fa-times"></i> Cancelar
            </button>
            <button type="button" class="modal-btn modal-btn-${colorClass}" onclick="productModalManager.toggleProductStatus(${productId}, ${currentStatus})">
                <i class="${iconClass}"></i> ${actionText}
            </button>
        `;
        
        this.showModal(modal);
    }
    
    // ===== MODAL DE STOCK =====
    
    showStockModal(productId, currentStock, productName) {
        
        const modal = this.createModal('stock-modal', 'medium');
        const title = modal.querySelector('.modal-title');
        const body = modal.querySelector('.modal-body');
        const footer = modal.querySelector('.modal-footer');
        
        title.innerHTML = '<i class="fas fa-boxes"></i> Actualizar Stock';
        
        body.innerHTML = `
            <form id="stock-form">
                <input type="hidden" id="stock-product-id" value="${productId}">
                
                <div class="modal-alert modal-alert-info">
                    <i class="fas fa-info-circle"></i>
                    <div>Actualizando stock para: <strong>${productName}</strong></div>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Stock Actual</label>
                        <input type="number" class="form-input" id="current-stock" value="${currentStock}" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label required">Nuevo Stock</label>
                        <input type="number" class="form-input" id="new-stock" name="new_stock" min="0" value="${currentStock}" required>
                    </div>
                    
                    <div class="form-group span-full">
                        <label class="form-label">Motivo del Cambio</label>
                        <select class="form-select" id="stock-reason" name="reason">
                            <option value="adjustment">Ajuste de inventario</option>
                            <option value="damage">Producto dañado</option>
                            <option value="return">Devolución</option>
                            <option value="sale">Venta manual</option>
                            <option value="restock">Reposición de stock</option>
                            <option value="other">Otro</option>
                        </select>
                    </div>
                    
                    <div class="form-group span-full">
                        <label class="form-label">Notas Adicionales</label>
                        <textarea class="form-textarea" id="stock-notes" name="notes" placeholder="Descripción detallada del cambio..."></textarea>
                    </div>
                </div>
            </form>
        `;
        
        footer.innerHTML = `
            <button type="button" class="modal-btn modal-btn-secondary" onclick="productModalManager.closeModal()">
                <i class="fas fa-times"></i> Cancelar
            </button>
            <button type="button" class="modal-btn modal-btn-success" onclick="productModalManager.saveStock()">
                <i class="fas fa-save"></i> Actualizar Stock
            </button>
        `;
        
        this.showModal(modal);
        
        // Foco en el campo de nuevo stock
        setTimeout(() => {
            const newStockInput = modal.querySelector('#new-stock');
            if (newStockInput) {
                newStockInput.focus();
                newStockInput.select();
            }
        }, 100);
    }
    
    // ===== FUNCIONES DE DATOS =====
    
    async loadCategories() {
        try {
            const response = await fetch(`${this.config.apiUrl}?action=get_categories`);
            const result = await response.json();
            
            if (response.ok && result.success) {
                const categorySelect = document.getElementById('product-category');
                if (categorySelect) {
                    categorySelect.innerHTML = '<option value="">Seleccionar categoría...</option>';
                    result.data.forEach(category => {
                        const option = document.createElement('option');
                        option.value = category.id_categoria;
                        option.textContent = category.nombre_categoria;
                        categorySelect.appendChild(option);
                    });
                }
            }
        } catch (error) {
            console.error('Error cargando categorías:', error);
            throw error;
        }
    }
    
    async loadBrands() {
        try {
            const response = await fetch(`${this.config.apiUrl}?action=get_brands`);
            const result = await response.json();
            
            if (response.ok && result.success) {
                const brandSelect = document.getElementById('product-brand');
                if (brandSelect) {
                    brandSelect.innerHTML = '<option value="">Seleccionar marca...</option>';
                    result.data.forEach(brand => {
                        const option = document.createElement('option');
                        option.value = brand.id_marca;
                        option.textContent = brand.nombre_marca;
                        brandSelect.appendChild(option);
                    });
                }
            }
        } catch (error) {
            console.error('Error cargando marcas:', error);
            // No lanzar error si las marcas fallan, es opcional
        }
    }
    
    async loadProductData(productId, modal) {
        try {
            // Asegurar que el ID es válido antes de hacer la petición
            if (!productId || productId === 'undefined' || productId === 'null') {
                throw new Error('ID de producto inválido');
            }
            
            // LIMPIAR FORMULARIO COMPLETAMENTE ANTES DE CARGAR NUEVOS DATOS
            this.clearProductForm(modal);
            
            const url = `${this.config.apiUrl}?action=get&id=${productId}&_t=${Date.now()}`;
            
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Cache-Control': 'no-cache'
                }
            });
            
            const result = await response.json();
            
            if (response.ok && result.success) {
                const product = result.product; // Cambiado de result.data a result.product
                this.fillProductForm(modal, product);
            } else {
                throw new Error(result.error || result.message || 'Error al cargar producto');
            }
        } catch (error) {
            console.error('❌ Error cargando producto:', error);
            throw error;
        }
    }
    
    // ===== LIMPIEZA DE FORMULARIO =====
    
    clearProductForm(modal) {
        const form = modal.querySelector('#product-form');
        if (!form) return;
        
        // Limpiar todos los campos
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            if (input.type === 'checkbox' || input.type === 'radio') {
                input.checked = false;
            } else {
                input.value = '';
            }
        });
        
        // Limpiar preview de imagen
        const imagePreview = form.querySelector('#image-preview');
        if (imagePreview) imagePreview.innerHTML = '';
        
        const fileInput = form.querySelector('input[type="file"]');
        if (fileInput) fileInput.value = '';
    }
    
    fillProductForm(modal, product) {
        const form = modal.querySelector('#product-form');
        if (!form) return;
        
        // Llenar campos básicos
        this.setFieldValue(form, 'product-id', product.id_producto);
        this.setFieldValue(form, 'product-name', product.nombre_producto);
        this.setFieldValue(form, 'product-codigo', product.codigo);
        this.setFieldValue(form, 'product-description', product.descripcion_producto);
        this.setFieldValue(form, 'product-category', product.id_categoria);
        this.setFieldValue(form, 'product-brand', product.id_marca);
        this.setFieldValue(form, 'product-stock', product.stock_actual_producto);
        this.setFieldValue(form, 'product-price', product.precio_producto);
        this.setFieldValue(form, 'product-discount-price', product.precio_descuento_producto);
        this.setFieldValue(form, 'product-discount-percent', product.descuento_porcentaje_producto);
        this.setFieldValue(form, 'product-estado', product.estado);
        
        // Mostrar imagen si existe
        if (product.url_imagen_producto || product.imagen_producto) {
            const imagePreview = form.querySelector('#image-preview');
            if (imagePreview) {
                // Construir URL correcta de imagen
                let imageUrl = '';
                
                if (product.url_imagen_producto && product.url_imagen_producto !== 'NULL' && product.url_imagen_producto !== '') {
                    // Si tiene URL completa, usarla directamente
                    imageUrl = product.url_imagen_producto;
                } else if (product.imagen_producto && product.imagen_producto !== 'NULL' && product.imagen_producto !== '') {
                    // Si solo tiene el nombre, construir la ruta completa
                    if (product.imagen_producto.startsWith('/') || product.imagen_producto.startsWith('http')) {
                        imageUrl = product.imagen_producto;
                    } else {
                        imageUrl = '/fashion-master/public/assets/img/products/' + product.imagen_producto;
                    }
                } else {
                    // Imagen por defecto
                    imageUrl = '/fashion-master/public/assets/img/default-product.jpg';
                }
                
                // Crear elemento img con manejo de errores
                imagePreview.innerHTML = `<img src="${imageUrl}" alt="Producto ${product.nombre_producto || ''}" class="file-preview" onerror="this.src='/fashion-master/public/assets/img/default-product.jpg'; this.onerror=null;">`;
            }
        } else {
            // Si no hay imagen, mostrar placeholder
            const imagePreview = form.querySelector('#image-preview');
            if (imagePreview) {
                imagePreview.innerHTML = `<img src="/fashion-master/public/assets/img/default-product.jpg" alt="Sin imagen" class="file-preview">`;
            }
        }
    }
    
    setFieldValue(form, fieldId, value) {
        const field = form.querySelector(`#${fieldId}`);
        if (field && value !== null && value !== undefined) {
            field.value = value;
        }
    }
    
    disableFormInputs(modal) {
        const inputs = modal.querySelectorAll('.form-input, .form-select, .form-textarea');
        inputs.forEach(input => {
            input.disabled = true;
            input.style.background = 'var(--background-secondary)';
        });
    }
    
    // ===== ACCIONES =====
    
    async toggleProductStatus(productId, currentStatus) {
        try {
            this.showLoading('Actualizando estado...');
            
            const newStatus = currentStatus ? 0 : 1; // Invertir el estado
            
            const formData = new FormData();
            formData.append('action', 'toggle-status');
            formData.append('producto_id', productId);
            formData.append('nuevo_estado', newStatus);
            
            const response = await fetch(this.config.apiUrl, {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (response.ok && result.success) {
                this.closeModal();
                this.showNotification('Estado actualizado correctamente', 'success');
                // Recargar productos
                if (typeof loadProducts === 'function') {
                    loadProducts();
                }
            } else {
                throw new Error(result.error || result.message || 'Error al actualizar estado');
            }
        } catch (error) {
            console.error('Error actualizando estado:', error);
            this.showNotification('Error al actualizar estado: ' + error.message, 'error');
        } finally {
            this.hideLoading();
        }
    }
    
    async saveStock() {
        try {
            const form = document.getElementById('stock-form');
            if (!form) {
                throw new Error('Formulario de stock no encontrado');
            }
            
            const productId = document.getElementById('stock-product-id').value;
            const newStock = document.getElementById('new-stock').value;
            const reason = document.getElementById('stock-reason').value;
            const notes = document.getElementById('stock-notes').value;
            
            if (!productId || newStock === '') {
                throw new Error('Datos incompletos');
            }
            
            const formData = new FormData();
            formData.append('action', 'update-stock');
            formData.append('producto_id', productId);
            formData.append('nuevo_stock', newStock);
            formData.append('motivo', reason);
            formData.append('notas', notes);
            
            this.showLoading('Actualizando stock...');
            
            const response = await fetch(this.config.apiUrl, {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (response.ok && result.success) {
                this.closeModal();
                this.showNotification('Stock actualizado correctamente', 'success');
                // Recargar productos
                if (typeof loadProducts === 'function') {
                    loadProducts();
                }
            } else {
                throw new Error(result.error || result.message || 'Error al actualizar stock');
            }
        } catch (error) {
            console.error('Error actualizando stock:', error);
            this.showNotification('Error al actualizar stock: ' + error.message, 'error');
        } finally {
            this.hideLoading();
        }
    }
    
    // ===== UTILIDADES =====
    
    showNotification(message, type = 'info') {
        // Usar la función global existente
        if (typeof showNotification === 'function') {
            showNotification(message, type);
        }
    }
    
    showLoading(message = 'Cargando...') {
        // Crear overlay de carga si no existe
        let loadingOverlay = document.getElementById('modal-loading-overlay');
        if (!loadingOverlay) {
            loadingOverlay = document.createElement('div');
            loadingOverlay.id = 'modal-loading-overlay';
            loadingOverlay.className = 'modal-loading-overlay';
            loadingOverlay.innerHTML = `
                <div class="modal-loading-spinner">
                    <div class="spinner"></div>
                    <p class="loading-message">${message}</p>
                </div>
            `;
            document.body.appendChild(loadingOverlay);
        } else {
            const msgElement = loadingOverlay.querySelector('.loading-message');
            if (msgElement) msgElement.textContent = message;
        }
        
        // Mostrar overlay
        setTimeout(() => {
            loadingOverlay.classList.add('show');
        }, 10);
    }
    
    hideLoading() {
        const loadingOverlay = document.getElementById('modal-loading-overlay');
        if (loadingOverlay) {
            loadingOverlay.classList.remove('show');
            setTimeout(() => {
                if (loadingOverlay && loadingOverlay.parentNode) {
                    loadingOverlay.remove();
                }
            }, 300);
        }
    }
    
    // ===== TEMPLATES HTML =====
    
    getProductFormHTML() {
        return `
            <div class="tab-navigation">
                <button class="tab-btn active" onclick="productModalManager.switchTab('general')">
                    <i class="fas fa-info-circle"></i> General
                </button>
                <button class="tab-btn" onclick="productModalManager.switchTab('inventory')">
                    <i class="fas fa-boxes"></i> Inventario
                </button>
                <button class="tab-btn" onclick="productModalManager.switchTab('pricing')">
                    <i class="fas fa-dollar-sign"></i> Precios
                </button>
            </div>
            
            <form id="product-form">
                <input type="hidden" id="product-id" name="id">
                
                <div id="tab-general" class="tab-content active">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label required">Nombre del Producto</label>
                            <input type="text" class="form-input" id="product-name" name="nombre" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Código</label>
                            <input type="text" class="form-input" id="product-codigo" name="codigo">
                        </div>
                        

                        
                        <div class="form-group span-full">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-textarea" id="product-description" name="descripcion"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label required">Categoría</label>
                            <select class="form-select" id="product-category" name="categoria" required>
                                <option value="">Seleccionar categoría...</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Marca</label>
                            <select class="form-select" id="product-brand" name="marca">
                                <option value="">Seleccionar marca...</option>
                            </select>
                        </div>
                        
                        <div class="form-group span-full">
                            <label class="form-label">Imagen del Producto</label>
                            <div class="file-upload" onclick="document.getElementById('product-image').click()">
                                <input type="file" id="product-image" accept="image/*" style="display: none;">
                                <div class="file-upload-icon">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                </div>
                                <p class="file-upload-text">Haz clic para subir imagen o arrastra aquí</p>
                                <div id="image-preview"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div id="tab-inventory" class="tab-content">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label required">Stock Actual</label>
                            <input type="number" class="form-input" id="product-stock" name="stock" min="0" required>
                        </div>
                    </div>
                </div>
                
                <div id="tab-pricing" class="tab-content">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label required">Precio Regular</label>
                            <input type="number" class="form-input" id="product-price" name="precio" step="0.01" min="0" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Precio de Descuento</label>
                            <input type="number" class="form-input" id="product-discount-price" name="precio_descuento" step="0.01" min="0">
                        </div>
                        
                        <div class="form-group span-full">
                            <label class="form-label">Porcentaje de Descuento</label>
                            <input type="number" class="form-input" id="product-discount-percent" name="descuento_porcentaje" min="0" max="100">
                            <span class="form-help">Se calculará automáticamente basado en los precios</span>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Estado del Producto</label>
                            <select class="form-select" id="product-estado" name="estado">
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        `;
    }
    
    getProductFormFooterHTML(mode) {
        if (mode === 'create') {
            return `
                <button type="button" class="modal-btn modal-btn-secondary" onclick="productModalManager.closeModal()">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="submit" class="modal-btn modal-btn-primary" form="product-form">
                    <i class="fas fa-save"></i> Crear Producto
                </button>
            `;
        } else {
            return `
                <button type="button" class="modal-btn modal-btn-secondary" onclick="productModalManager.closeModal()">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="submit" class="modal-btn modal-btn-primary" form="product-form">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
            `;
        }
    }

    switchTab(tabName) {
        // Remover clase active de todos los tabs y contenidos
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        
        // Activar el tab seleccionado
        const activeBtn = document.querySelector(`.tab-btn[onclick*="${tabName}"]`);
        const activeContent = document.getElementById(`tab-${tabName}`);
        
        if (activeBtn && activeContent) {
            activeBtn.classList.add('active');
            activeContent.classList.add('active');
        }
    }
    
    switchTab(tabName) {
        
        // Desactivar todos los tabs
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        
        // Activar tab seleccionado
        const activeBtn = document.querySelector(`[onclick="productModalManager.switchTab('${tabName}')"]`);
        const activeContent = document.getElementById(`tab-${tabName}`);
        
        if (activeBtn) activeBtn.classList.add('active');
        if (activeContent) activeContent.classList.add('active');
    }
    
    setupProductFormEvents(modal, mode, productId = null) {
        // Event listener para upload de imagen
        const fileInput = modal.querySelector('#product-image');
        if (fileInput) {
            fileInput.addEventListener('change', (e) => this.handleImageUpload(e));
        }
        
        // Event listener para submit del formulario
        const form = modal.querySelector('form');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                if (mode === 'create') {
                    this.submitCreateProduct(form);
                } else if (mode === 'edit') {
                    this.submitEditProduct(form, productId);
                }
            });
        }
        
        // Validación de precio automática
        const priceInput = modal.querySelector('#product-price');
        const discountPriceInput = modal.querySelector('#product-discount-price');
        const discountPercentInput = modal.querySelector('#product-discount-percent');
        
        if (priceInput && discountPriceInput && discountPercentInput) {
            const calculateDiscount = () => {
                const price = parseFloat(priceInput.value) || 0;
                const discountPrice = parseFloat(discountPriceInput.value) || 0;
                
                if (price > 0 && discountPrice > 0 && discountPrice < price) {
                    const percentage = ((price - discountPrice) / price * 100).toFixed(2);
                    discountPercentInput.value = percentage;
                }
            };
            
            priceInput.addEventListener('input', calculateDiscount);
            discountPriceInput.addEventListener('input', calculateDiscount);
        }
    }

    async submitCreateProduct(form) {
        try {
            this.showLoading('Creando producto...');
            
            const formData = new FormData(form);
            formData.append('action', 'create');
            
            const response = await fetch(this.config.apiUrl, {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (response.ok && result.success) {
                this.closeModal();
                this.showNotification('Producto creado correctamente', 'success');
                
                // USAR ACTUALIZACIÓN SUAVE en lugar de recargar toda la tabla
                if (window.smoothTableUpdater && result.product) {
                    await window.smoothTableUpdater.addNewProduct(result.product);
                } else {
                    // Fallback: Recargar productos
                    if (typeof loadProducts === 'function') {
                        loadProducts();
                    }
                }
            } else {
                throw new Error(result.error || result.message || 'Error al crear producto');
            }
        } catch (error) {
            console.error('❌ Error creando producto:', error);
            this.showNotification('Error al crear producto: ' + error.message, 'error');
        } finally {
            this.hideLoading();
        }
    }

    async submitEditProduct(form, productId) {
        try {
            this.showLoading('Actualizando producto...');
            
            const formData = new FormData(form);
            formData.append('action', 'update');
            formData.append('id', productId);
            
            const response = await fetch(this.config.apiUrl, {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (response.ok && result.success) {
                this.closeModal();
                this.showNotification('Producto actualizado correctamente', 'success');
                
                // USAR ACTUALIZACIÓN SUAVE en lugar de recargar toda la tabla
                if (window.smoothTableUpdater && result.product) {
                    await window.smoothTableUpdater.updateSingleProduct(productId, result.product);
                } else {
                    // Fallback: Recargar productos
                    if (typeof loadProducts === 'function') {
                        loadProducts();
                    }
                }
            } else {
                throw new Error(result.error || result.message || 'Error al actualizar producto');
            }
        } catch (error) {
            console.error('❌ Error actualizando producto:', error);
            this.showNotification('Error al actualizar producto: ' + error.message, 'error');
        } finally {
            this.hideLoading();
        }
    }
    
    handleImageUpload(event) {
        const file = event.target.files[0];
        if (!file) return;
        
        // Validar tipo de archivo
        if (!file.type.startsWith('image/')) {
            this.showNotification('Por favor selecciona un archivo de imagen válido', 'error');
            return;
        }
        
        // Validar tamaño (max 5MB)
        if (file.size > 5 * 1024 * 1024) {
            this.showNotification('La imagen debe ser menor a 5MB', 'error');
            return;
        }
        
        const reader = new FileReader();
        reader.onload = (e) => {
            const imagePreview = document.getElementById('image-preview');
            if (imagePreview) {
                imagePreview.innerHTML = `<img src="${e.target.result}" alt="Preview" class="file-preview">`;
            }
        };
        reader.readAsDataURL(file);
    }
}

// Crear instancia global
window.productModalManager = new ProductModalManager();

// Exponer funciones globales para compatibilidad con onclick
window.showCreateProductModal = function() {
    window.productModalManager.showCreateProductModal();
};

window.showEditProductModal = function(productId) {
    window.productModalManager.showEditProductModal(productId);
};

window.showViewProductModal = function(productId) {
    window.productModalManager.showViewProductModal(productId);
};

window.showStatusModal = function(productId, currentStatus, productName) {
    window.productModalManager.showStatusModal(productId, currentStatus, productName);
};

window.showStockModal = function(productId, currentStock, productName) {
    window.productModalManager.showStockModal(productId, currentStock, productName);
};


// ===== OCULTAR SCROLLBARS AL CARGAR LA PÁGINA - VERSIÓN DEFINITIVA =====
// Ejecutar inmediatamente cuando se carga este script
(function() {
    
    // Crear un <style> tag para forzar scrollbars ocultos con máxima prioridad
    function injectScrollbarCSS() {
        // Buscar si ya existe el style
        let styleTag = document.getElementById('force-hide-scrollbar');
        if (!styleTag) {
            styleTag = document.createElement('style');
            styleTag.id = 'force-hide-scrollbar';
            styleTag.innerHTML = `
                /* FORZAR OCULTACIÓN DE SCROLLBARS - MÁXIMA PRIORIDAD */
                html, html[style], body, body[style], body.modal-open {
                    scrollbar-width: none !important;
                    -ms-overflow-style: none !important;
                    overflow-x: hidden !important;
                    overflow-y: auto !important;
                }
                
                html::-webkit-scrollbar,
                html[style]::-webkit-scrollbar,
                body::-webkit-scrollbar,
                body[style]::-webkit-scrollbar,
                body.modal-open::-webkit-scrollbar {
                    display: none !important;
                    width: 0 !important;
                    height: 0 !important;
                    background: transparent !important;
                }
                
                /* Aplicar a TODOS los elementos */
                *::-webkit-scrollbar {
                    display: none !important;
                    width: 0 !important;
                    height: 0 !important;
                }
            `;
            document.head.appendChild(styleTag);
        }
    }
    
    // Función para ocultar scrollbars PERO permitir scroll funcional
    function hideAllScrollbars() {
        // Inyectar CSS primero
        injectScrollbarCSS();
        
        // Aplicar estilos inline como respaldo
        const elements = [document.documentElement, document.body];
        elements.forEach(el => {
            if (el) {
                el.style.setProperty('overflow-y', 'auto', 'important');
                el.style.setProperty('overflow-x', 'hidden', 'important');
                el.style.setProperty('scrollbar-width', 'none', 'important');
                el.style.setProperty('-ms-overflow-style', 'none', 'important');
            }
        });
            }
    
    // Ejecutar inmediatamente
    hideAllScrollbars();
    
    // Ejecutar cuando DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', hideAllScrollbars);
    } else {
        hideAllScrollbars();
    }
    
    // Ejecutar cuando la ventana esté completamente cargada
    window.addEventListener('load', hideAllScrollbars);
    
    // MutationObserver para detectar cambios en atributos de style
    if (typeof MutationObserver !== 'undefined' && document.body) {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                    // Re-aplicar estilos si fueron modificados
                    hideAllScrollbars();
                }
            });
        });
        
        // Observar cambios en html y body (verificando que existan)
        if (document.documentElement) {
            observer.observe(document.documentElement, { attributes: true, attributeFilter: ['style'] });
        }
        if (document.body) {
            observer.observe(document.body, { attributes: true, attributeFilter: ['style'] });
        }
        
    }
    
})();

} else {
    console.warn('⚠️ ProductModalManager ya existe - saltando redeclaración');
}