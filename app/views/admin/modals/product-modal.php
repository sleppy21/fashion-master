<!-- Modal Crear/Editar Producto -->
<div id="productModal" class="product-modal-overlay" style="display: none;">
    <div class="product-modal-container">
        <!-- Header del Modal -->
        <div class="product-modal-header">
            <div class="product-modal-title-wrapper">
                <i class="fas fa-box-open product-modal-icon"></i>
                <h2 class="product-modal-title" id="productModalTitle">Nuevo Producto</h2>
            </div>
            <button type="button" class="product-modal-close" onclick="closeProductModal()" aria-label="Cerrar modal">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Body del Modal con Scroll -->
        <div class="product-modal-body">
            <form id="productForm" class="product-form">
                <input type="hidden" id="product_id" name="product_id">
                
                <!-- Sección: Información Básica -->
                <div class="product-form-section">
                    <h3 class="product-form-section-title">
                        <i class="fas fa-info-circle"></i>
                        Información Básica
                    </h3>
                    
                    <div class="product-form-grid">
                        <!-- Nombre del Producto -->
                        <div class="product-form-group">
                            <label for="nombre_producto" class="product-form-label">
                                Nombre del Producto <span class="required">*</span>
                            </label>
                            <input type="text" 
                                   id="nombre_producto" 
                                   name="nombre_producto" 
                                   class="product-form-input" 
                                   placeholder="Ej: Camisa Casual Nike"
                                   required>
                            <span class="product-form-error" id="error-nombre_producto"></span>
                        </div>

                        <!-- Código -->
                        <div class="product-form-group">
                            <label for="codigo" class="product-form-label">
                                Código <span class="required">*</span>
                            </label>
                            <input type="text" 
                                   id="codigo" 
                                   name="codigo" 
                                   class="product-form-input" 
                                   placeholder="Ej: PRD-001"
                                   required>
                            <span class="product-form-error" id="error-codigo"></span>
                        </div>

                        <!-- Precio -->
                        <div class="product-form-group">
                            <label for="precio_producto" class="product-form-label">
                                Precio <span class="required">*</span>
                            </label>
                            <div class="product-form-input-group">
                                <input type="number" 
                                       id="precio_producto" 
                                       name="precio_producto" 
                                       class="product-form-input product-form-input-with-prefix" 
                                       placeholder="0.00"
                                       step="0.01"
                                       min="0"
                                       required>
                            </div>
                            <span class="product-form-error" id="error-precio_producto"></span>
                        </div>

                        <!-- Descuento -->
                        <div class="product-form-group">
                            <label for="descuento_porcentaje_producto" class="product-form-label">
                                Descuento (%)
                            </label>
                            <div class="product-form-input-group">
                                <input type="number" 
                                       id="descuento_porcentaje_producto" 
                                       name="descuento_porcentaje_producto" 
                                       class="product-form-input product-form-input-with-suffix" 
                                       placeholder="0"
                                       step="1"
                                       min="0"
                                       max="100"
                                       value="0">
                                <span class="product-form-input-suffix">%</span>
                            </div>
                        </div>

                        <!-- Descripción -->
                        <div class="product-form-group product-form-group-full">
                            <label for="descripcion_producto" class="product-form-label">
                                Descripción
                            </label>
                            <textarea id="descripcion_producto" 
                                      name="descripcion_producto" 
                                      class="product-form-textarea" 
                                      rows="3"
                                      placeholder="Descripción detallada del producto..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Sección: Categorización -->
                <div class="product-form-section">
                    <h3 class="product-form-section-title">
                        <i class="fas fa-tags"></i>
                        Categorización
                    </h3>
                    
                    <div class="product-form-grid">
                        <!-- Categoría -->
                        <div class="product-form-group">
                            <label for="id_categoria" class="product-form-label">
                                Categoría <span class="required">*</span>
                            </label>
                            <select id="id_categoria" 
                                    name="id_categoria" 
                                    class="product-form-select"
                                    required>
                                <option value="">Seleccionar categoría</option>
                            </select>
                            <span class="product-form-error" id="error-id_categoria"></span>
                        </div>

                        <!-- Subcategoría -->
                        <div class="product-form-group">
                            <label for="id_subcategoria" class="product-form-label">
                                Subcategoría
                            </label>
                            <select id="id_subcategoria" 
                                    name="id_subcategoria" 
                                    class="product-form-select"
                                    disabled>
                                <option value="">Primero selecciona una categoría</option>
                            </select>
                            <span class="product-form-error" id="error-id_subcategoria"></span>
                        </div>

                        <!-- Marca -->
                        <div class="product-form-group">
                            <label for="id_marca" class="product-form-label">
                                Marca <span class="required">*</span>
                            </label>
                            <select id="id_marca" 
                                    name="id_marca" 
                                    class="product-form-select"
                                    required>
                                <option value="">Seleccionar marca</option>
                            </select>
                            <span class="product-form-error" id="error-id_marca"></span>
                        </div>

                        <!-- Género -->
                        <div class="product-form-group">
                            <label for="genero_producto" class="product-form-label">
                                Género <span class="required">*</span>
                            </label>
                            <select id="genero_producto" 
                                    name="genero_producto" 
                                    class="product-form-select"
                                    required>
                                <option value="">Seleccionar género</option>
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                                <option value="Unisex">Unisex</option>
                                <option value="Kids">Niños</option>
                            </select>
                            <span class="product-form-error" id="error-genero_producto"></span>
                        </div>
                    </div>
                </div>

                <!-- Sección: Inventario -->
                <div class="product-form-section">
                    <h3 class="product-form-section-title">
                        <i class="fas fa-boxes"></i>
                        Inventario
                    </h3>
                    
                    <div class="product-form-grid">
                        <!-- Stock Actual -->
                        <div class="product-form-group">
                            <label for="stock_actual_producto" class="product-form-label">
                                Stock Actual <span class="required">*</span>
                            </label>
                            <input type="number" 
                                   id="stock_actual_producto" 
                                   name="stock_actual_producto" 
                                   class="product-form-input" 
                                   placeholder="0"
                                   min="0"
                                   value="0"
                                   required>
                            <span class="product-form-error" id="error-stock_actual_producto"></span>
                        </div>

                        <!-- Stock Mínimo -->
                        <div class="product-form-group">
                            <label for="stock_minimo_producto" class="product-form-label">
                                Stock Mínimo
                            </label>
                            <input type="number" 
                                   id="stock_minimo_producto" 
                                   name="stock_minimo_producto" 
                                   class="product-form-input" 
                                   placeholder="0"
                                   min="0"
                                   value="0">
                        </div>
                    </div>
                </div>

                <!-- Sección: Imagen -->
                <div class="product-form-section">
                    <h3 class="product-form-section-title">
                        <i class="fas fa-image"></i>
                        Imagen del Producto
                    </h3>
                    
                    <div class="product-image-upload-container">
                        <!-- PREVISUALIZACIÓN CON TARJETA DE TIENDA MEJORADA -->
                        <div id="imagePreviewWrapper" class="product-preview-large">
                            <?php 
                            // Incluir componente de tarjetas de tienda
                            require_once __DIR__ . '/../../components/product-card.php';
                            
                            // Crear producto de previsualización con datos iniciales
                            $preview_product = [
                                'id_producto' => 0,
                                'nombre_producto' => 'Nombre del Producto',
                                'precio_producto' => 99.99,
                                'descuento_porcentaje_producto' => 0,
                                'stock_actual_producto' => 10,
                                'url_imagen_producto' => 'public/assets/img/default-product.jpg',
                                'nombre_categoria' => 'CATEGORÍA',
                                'calificacion_promedio' => 0,
                                'total_resenas' => 0
                            ];
                            
                            // Renderizar tarjeta de previsualización (sin hover buttons)
                            renderProductCard($preview_product, false, false, false);
                            ?>
                        </div>

                        <div class="product-image-upload-actions">
                            <label for="imagen_producto" class="btn-modern btn-upload">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Seleccionar Imagen</span>
                            </label>
                            <input type="file" 
                                   id="imagen_producto" 
                                   name="imagen_producto" 
                                   accept="image/*"
                                   style="display: none;"
                                   onchange="ProductModal.previewImage(event)">
                            
                            <button type="button" 
                                    class="btn-modern btn-remove" 
                                    id="btnRemoveImage"
                                    onclick="ProductModal.removeImage()"
                                    style="display: none;">
                                <i class="fas fa-trash-alt"></i>
                                <span>Quitar Imagen</span>
                            </button>
                        </div>
                        
                        <p class="product-image-help-text">
                            <i class="fas fa-info-circle"></i>
                            Formatos: JPG, PNG, GIF. Tamaño máximo: 2MB. Dimensiones recomendadas: 800x800px
                        </p>
                    </div>
                </div>
            </form>
        </div>

        <!-- Footer del Modal -->
        <div class="product-modal-footer">
            <button type="button" 
                    class="btn-modern btn-cancel" 
                    onclick="ProductModal.close()">
                <i class="fas fa-times"></i>
                <span>Cancelar</span>
            </button>
            <button type="button" 
                    class="btn-modern btn-save" 
                    id="btnSaveProduct"
                    onclick="ProductModal.save()">
                <i class="fas fa-save"></i>
                <span id="btnSaveText">Guardar Producto</span>
            </button>
        </div>
    </div>
</div>
