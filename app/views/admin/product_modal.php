<?php
// Modal para productos - Vista PHP (Mejorado: diseño “Ver Producto” con scroll arreglado, animaciones suaves y corrección de colores)
// Incluir la conexión para obtener categorías y marcas
require_once __DIR__ . '/../../../config/conexion.php';

// Obtener categorías para el select
try {
    $stmt = $conn->prepare("SELECT id_categoria as id, nombre_categoria as nombre FROM categoria WHERE status_categoria = 1 ORDER BY nombre_categoria");
    $stmt->execute();
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categorias = [];
}

// Obtener marcas para el select  
try {
    $stmt = $conn->prepare("SELECT id_marca, nombre_marca FROM marca WHERE status_marca = 1 ORDER BY nombre_marca");
    $stmt->execute();
    $marcas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $marcas = [];
}

// Si es edición o vista, obtener datos del producto
$producto = null;
$action = isset($_GET['action']) ? $_GET['action'] : 'create';
$isView = $action === 'view' && isset($_GET['id']);
$isEdit = $action === 'edit' && isset($_GET['id']);
$isCreate = $action === 'create';

if (($isEdit || $isView) && isset($_GET['id'])) {
    try {
        $stmt = $conn->prepare("SELECT * FROM producto WHERE id_producto = ?");
        $stmt->execute([$_GET['id']]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $producto = null;
    }
}

$hasData = $producto !== null;
$modalTitle = $isView ? 'Ver Producto' : ($isEdit ? 'Editar Producto' : 'Nuevo Producto');
$iconClass = $isView ? 'eye' : ($isEdit ? 'edit' : 'plus');
?>

<?php if ($isView && $hasData): ?>

<!-- VIEW: Modal Ver Producto - Diseño Profesional Completo -->
<div class="product-view-modal show">
    <!-- Overlay de fondo - SIN onclick para evitar duplicación (admin.php ya tiene el listener) -->
    <div class="product-view-modal__overlay"></div>
    
    <!-- Contenedor del modal -->
    <div class="product-view-modal__container">
        <!-- HEADER -->
        <div class="product-view-modal__header">
            <div class="product-view-modal__header-content">
                <h2 class="product-view-modal__title">
                    <span class="product-view-modal__title-icon">
                        <i class="fas fa-eye"></i>
                    </span>
                    Ver Producto
                </h2>
                
                <div class="product-view-modal__badge">
                    <i class="fas fa-lock"></i>
                    Solo Lectura
                </div>
            </div>
            
            <button type="button" class="product-view-modal__close" onclick="closeProductModal()" aria-label="Cerrar">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- BODY -->
        <div class="product-view-modal__body">
            <div class="product-view-modal__content">
                <!-- PANEL IZQUIERDO: IMAGEN -->
                <div class="product-view-modal__image-section">
                    <div class="product-view-modal__image-container">
                        <?php
                        $imagenSrc = '';
                        if (!empty($producto['url_imagen_producto'])) {
                            $imagenSrc = $producto['url_imagen_producto'];
                        } elseif (!empty($producto['imagen_producto'])) {
                            $imagenSrc = 'public/assets/img/products/' . $producto['imagen_producto'];
                        }
                        ?>
                        
                        <?php if ($imagenSrc): ?>
                            <img src="<?= htmlspecialchars($imagenSrc) ?>" 
                                 alt="<?= htmlspecialchars($producto['nombre_producto']) ?>" 
                                 class="product-view-modal__image"
                                 onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'product-view-modal__image-placeholder\'><i class=\'fas fa-image\'></i><span>Imagen no disponible</span></div>'">
                        <?php else: ?>
                            <div class="product-view-modal__image-placeholder">
                                <i class="fas fa-image"></i>
                                <span>Sin imagen</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="product-view-modal__image-name">
                        <?= htmlspecialchars($producto['nombre_producto']) ?>
                    </div>
                </div>

                <!-- PANEL DERECHO: INFORMACIÓN -->
                <div class="product-view-modal__info-section">
                    <!-- INFORMACIÓN BÁSICA -->
                    <div class="product-view-modal__card">
                        <div class="product-view-modal__card-header">
                            <div class="product-view-modal__card-icon">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <h3 class="product-view-modal__card-title">Información Básica</h3>
                        </div>
                        
                        <div class="product-view-modal__grid">
                            <div class="product-view-modal__field">
                                <label class="product-view-modal__field-label">Nombre</label>
                                <div class="product-view-modal__field-value">
                                    <?= htmlspecialchars($producto['nombre_producto']) ?>
                                </div>
                            </div>
                            
                            <div class="product-view-modal__field">
                                <label class="product-view-modal__field-label">Categoría</label>
                                <div class="product-view-modal__field-value">
                                    <?php
                                    $categoria_nombre = 'Sin categoría';
                                    foreach ($categorias as $cat) {
                                        if ($cat['id'] == $producto['id_categoria']) {
                                            $categoria_nombre = $cat['nombre'];
                                            break;
                                        }
                                    }
                                    echo htmlspecialchars($categoria_nombre);
                                    ?>
                                </div>
                            </div>
                            
                            <div class="product-view-modal__field">
                                <label class="product-view-modal__field-label">Marca</label>
                                <div class="product-view-modal__field-value">
                                    <?php
                                    $marca_nombre = 'Sin marca';
                                    foreach ($marcas as $marca) {
                                        if ($marca['id_marca'] == $producto['id_marca']) {
                                            $marca_nombre = $marca['nombre_marca'];
                                            break;
                                        }
                                    }
                                    echo htmlspecialchars($marca_nombre);
                                    ?>
                                </div>
                            </div>
                            
                            <div class="product-view-modal__field">
                                <label class="product-view-modal__field-label">Código</label>
                                <div class="product-view-modal__field-value product-view-modal__field-value--code">
                                    <?= htmlspecialchars($producto['codigo'] ?: 'N/A') ?>
                                </div>
                            </div>
                            
                            <?php if (!empty($producto['descripcion_producto'])): ?>
                            <div class="product-view-modal__description">
                                <label class="product-view-modal__field-label">Descripción</label>
                                <p class="product-view-modal__description-text">
                                    <?= nl2br(htmlspecialchars($producto['descripcion_producto'])) ?>
                                </p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- INFORMACIÓN COMERCIAL -->
                    <div class="product-view-modal__card">
                        <div class="product-view-modal__card-header">
                            <div class="product-view-modal__card-icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <h3 class="product-view-modal__card-title">Información Comercial</h3>
                        </div>
                        
                        <div class="product-view-modal__grid">
                            <div class="product-view-modal__field">
                                <label class="product-view-modal__field-label">Precio</label>
                                <div class="product-view-modal__field-value product-view-modal__field-value--price" data-price="$<?= number_format($producto['precio_producto'], 2) ?>">
                                    $<?= number_format($producto['precio_producto'], 2) ?>
                                </div>
                            </div>
                            
                            <div class="product-view-modal__field">
                                <label class="product-view-modal__field-label">Estado</label>
                                <div class="product-view-modal__field-value">
                                    <span class="product-view-modal__status-badge product-view-modal__status-badge--<?= $producto['estado'] === 'activo' ? 'active' : 'inactive' ?>">
                                        <i class="fas fa-<?= $producto['estado'] === 'activo' ? 'check-circle' : 'times-circle' ?>"></i>
                                        <?= ucfirst(htmlspecialchars($producto['estado'])) ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="product-view-modal__field">
                                <label class="product-view-modal__field-label">Stock Disponible</label>
                                <div class="product-view-modal__field-value">
                                    <div class="product-view-modal__stock">
                                        <div class="product-view-modal__stock-amount">
                                            <span class="product-view-modal__stock-number">
                                                <?= htmlspecialchars($producto['stock_actual_producto']) ?>
                                            </span>
                                            <span class="product-view-modal__stock-label">unidades</span>
                                        </div>
                                        <div class="product-view-modal__stock-bar">
                                            <?php
                                            $stock = (int)$producto['stock_actual_producto'];
                                            $percentage = min(100, ($stock / 100) * 100);
                                            $stockClass = '';
                                            if ($stock == 0) {
                                                $stockClass = 'product-view-modal__stock-fill--empty';
                                            } elseif ($stock < 20) {
                                                $stockClass = 'product-view-modal__stock-fill--low';
                                            }
                                            ?>
                                            <div class="product-view-modal__stock-fill <?= $stockClass ?>" 
                                                 style="width: <?= $percentage ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (isset($producto['descuento_porcentaje_producto']) && $producto['descuento_porcentaje_producto'] > 0): ?>
                            <div class="product-view-modal__field">
                                <label class="product-view-modal__field-label">Descuento</label>
                                <div class="product-view-modal__field-value product-view-modal__field-value--discount" 
                                     data-discount="<?= $producto['descuento_porcentaje_producto'] ?>">
                                    <span class="discount-percentage"><?= number_format($producto['descuento_porcentaje_producto'], 0) ?>%</span>
                                    <div class="discount-fire-effect"></div>
                                    <div class="discount-savings">¡Ahorras <?= number_format(($producto['precio_producto'] * $producto['descuento_porcentaje_producto']) / 100, 2) ?>!</div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="product-view-modal__footer">
            <button type="button" class="product-view-modal__btn product-view-modal__btn--secondary" onclick="closeProductModal()">
                <i class="fas fa-times"></i>
                Cerrar
            </button>
        </div>
    </div>
</div>

<script>
// JavaScript específico para el modal Ver Producto
(function() {
    'use strict';
    
    // Función para cerrar el modal Ver Producto
    // ✅ SIMPLIFICADO: Solo usar closeProductModal global
    function closeViewProductModal() {
        // Siempre llamar a la función global
        if (typeof window.closeProductModal === 'function') {
            window.closeProductModal();
        } else {
            console.error('❌ closeProductModal no está disponible');
        }
    }
    
    // Exponer función localmente
    window.closeViewProductModal = closeViewProductModal;
    
    // Función para activar animaciones del stock después de que el modal aparezca
    function activateStockAnimations() {
        const stockBar = document.querySelector('.product-view-modal__stock-fill');
        if (stockBar) {
            // Guardar el ancho original
            const originalWidth = stockBar.style.width;
            const stockValue = parseInt(originalWidth);
            
            // Resetear a 0
            stockBar.style.width = '0%';
            stockBar.style.transition = 'none';
            
            // Aplicar el ancho original después de un delay con transición suave
            setTimeout(() => {
                stockBar.style.transition = 'width 2s cubic-bezier(0.4, 0, 0.2, 1)';
                stockBar.style.width = originalWidth;
                
                // Agregar efectos especiales basados en el stock
                if (stockValue > 80) {
                    stockBar.style.animation = 'stockPulseHigh 3s ease-in-out infinite';
                } else if (stockValue > 50) {
                    stockBar.style.animation = 'stockPulseMedium 2s ease-in-out infinite';
                } else if (stockValue > 0) {
                    stockBar.style.animation = 'stockPulseLow 1.5s ease-in-out infinite';
                }
            }, 500);
        }
        
        // Activar animaciones del descuento
        const discountField = document.querySelector('.product-view-modal__field-value--discount');
        if (discountField) {
            const discountValue = parseFloat(discountField.dataset.discount);
            
            // Intensificar efectos según el porcentaje de descuento
            if (discountValue >= 50) {
                discountField.style.animation = 'discountPulse 1s ease-in-out infinite, fireEffect 0.5s ease-in-out infinite';
                discountField.style.boxShadow = '0 0 40px rgba(255, 68, 68, 0.8), 0 0 80px rgba(255, 107, 107, 0.4)';
            } else if (discountValue >= 30) {
                discountField.style.animation = 'discountPulse 1.5s ease-in-out infinite';
                discountField.style.boxShadow = '0 0 25px rgba(255, 68, 68, 0.6)';
            }
        }
    }
    
    // Activar animaciones después de que el modal esté visible
    setTimeout(activateStockAnimations, 1000);
    
        
    // ✅ SIMPLIFICADO: Los botones usan onclick="closeProductModal()" en el HTML
    // Solo necesitamos configurar el overlay para cerrar al hacer click fuera
    function setupCloseEvents() {
        
        // Cerrar con overlay (click en fondo)
        const overlay = document.querySelector('.product-view-modal__overlay');
        if (overlay) {
            overlay.removeEventListener('click', overlay.clickHandler);
            overlay.clickHandler = function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('🔘 Click en overlay');
                if (typeof window.closeProductModal === 'function') {
                    window.closeProductModal();
                }
            };
            overlay.addEventListener('click', overlay.clickHandler);
        }
    }
    
    // Configurar evento ESC GLOBAL inmediatamente
    if (!window.modalEscapeConfigured) {
        const globalEscHandler = function(e) {
            if (e.key === 'Escape' || e.keyCode === 27) {
                const activeModal = document.querySelector('.product-view-modal.show');
                if (activeModal) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (typeof window.closeProductModal === 'function') {
                        window.closeProductModal();
                    } else {
                        closeViewProductModal();
                    }
                }
            }
        };
        
        // Agregar listener global para ESC
        document.addEventListener('keydown', globalEscHandler, true);
        window.modalEscapeConfigured = true;
    }
    
    // Configurar eventos inmediatamente
    setupCloseEvents();
    
})();
</script>

<?php else: ?>

<!-- CREAR / EDITAR: se mantiene la versión original sin cambios de estilos para evitar conflictos -->
<div class="modal-content">
    <div class="modal-header">
        <h2 class="modal-title">
            <i class="fas fa-<?= $iconClass ?>"></i>
            <?= $modalTitle ?>
        </h2>
        <button type="button" class="modal-close">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <form id="productForm" method="POST" action="" enctype="multipart/form-data">
        <input type="hidden" name="action" value="<?= $isEdit ? 'update' : 'create' ?>">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id_producto" value="<?= $producto['id_producto'] ?>">
            <!-- Mantener status_producto sin modificar al editar -->
            <input type="hidden" name="status_producto" value="<?= $producto['status_producto'] ?>">
        <?php else: ?>
            <!-- Para productos nuevos, establecer status_producto en 1 (activo/no eliminado) -->
            <input type="hidden" name="status_producto" value="1">
        <?php endif; ?>
        
        <div class="modal-body">
            <!-- Información básica -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-info-circle"></i>
                    Información Básica
                </h3>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nombre_producto">
                            <i class="fas fa-tag"></i>
                            Nombre del Producto *
                        </label>
                        <input type="text" 
                               id="nombre_producto" 
                               name="nombre_producto" 
                               value="<?= $hasData ? htmlspecialchars($producto['nombre_producto']) : '' ?>"
                               required 
                               maxlength="100"
                               <?= $isView ? 'readonly' : '' ?>
                               placeholder="Ej: Camisa Casual Nike">
                    </div>
                    
                    <div class="form-group">
                        <label for="categoria">
                            <i class="fas fa-folder"></i>
                            Categoría *
                        </label>
                        <select id="categoria" name="id_categoria" required <?= $isView ? 'disabled' : '' ?>>
                            <option value="">Seleccionar categoría</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?= $categoria['id'] ?>" 
                                    <?= ($hasData && $producto['id_categoria'] == $categoria['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($categoria['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="marca">
                            <i class="fas fa-certificate"></i>
                            Marca
                        </label>
                        <select id="marca" 
                                name="id_marca" 
                                <?= $isView ? 'disabled' : '' ?>>
                            <option value="">Seleccionar marca</option>
                            <?php foreach ($marcas as $marca): ?>
                                <option value="<?= $marca['id_marca'] ?>" 
                                        <?= ($hasData && $producto['id_marca'] == $marca['id_marca']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($marca['nombre_marca']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Resto del formulario (idéntico al original) -->
            <!-- ... (mantengo el mismo HTML/JS que ya tenías para crear/editar) -->
            
            <!-- Descripción -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-align-left"></i>
                    Descripción
                </h3>
                
                <div class="form-group">
                    <textarea id="descripcion" 
                              name="descripcion_producto" 
                              rows="4" 
                              maxlength="500"
                              placeholder="Describe las características principales del producto..."
                              <?= $isView ? 'readonly' : '' ?>><?= $hasData ? htmlspecialchars($producto['descripcion_producto']) : '' ?></textarea>
                    <small class="form-text">Máximo 500 caracteres</small>
                </div>
            </div>
            
            <!-- Precios y Stock -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-dollar-sign"></i>
                    Precios y Stock
                </h3>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="precio">
                            Precio *
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" 
                                   id="precio" 
                                   name="precio_producto" 
                                   value="<?= $hasData ? $producto['precio_producto'] : '' ?>"
                                   required 
                                   min="0" 
                                   step="0.01"
                                   <?= $isView ? 'readonly' : '' ?>
                                   placeholder="0.00">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="stock">
                            <i class="fas fa-boxes"></i>
                            Stock *
                        </label>
                        <input type="number" 
                               id="stock" 
                               name="stock_actual_producto" 
                               value="<?= $hasData ? $producto['stock_actual_producto'] : '' ?>"
                               required 
                               min="0"
                               <?= $isView ? 'readonly' : '' ?>
                               placeholder="0">
                    </div>
                    
                    <div class="form-group">
                        <label for="codigo">
                            <i class="fas fa-barcode"></i>
                            Código
                        </label>
                        <input type="text" 
                               id="codigo" 
                               name="codigo" 
                               value="<?= $hasData ? htmlspecialchars($producto['codigo']) : '' ?>"
                               <?= $isView ? 'readonly' : '' ?>
                               maxlength="50"
                               placeholder="Código único del producto">
                    </div>
                    
                    <div class="form-group">
                        <label for="descuento">
                            <i class="fas fa-percent"></i>
                            Descuento (%)
                        </label>
                        <input type="number" 
                               id="descuento" 
                               name="precio_descuento_producto" 
                               value="<?= $hasData ? $producto['descuento_porcentaje_producto'] : '0' ?>"
                               min="0" 
                               max="100"
                               step="0.01"
                               <?= $isView ? 'readonly' : '' ?>
                               placeholder="0.00">
                    </div>
                    
                    <div class="form-group">
                        <label for="estado">
                            <i class="fas fa-toggle-on"></i>
                            Estado del Producto
                        </label>
                        <select id="estado" 
                                name="estado" 
                                class="form-control"
                                <?= $isView ? 'disabled readonly style="background-color: #f8f9fa !important; cursor: not-allowed !important; pointer-events: none !important;"' : '' ?>>
                            <option value="activo" <?= ($hasData && $producto['estado'] === 'activo') ? 'selected' : '' ?>>Activo</option>
                            <option value="inactivo" <?= ($hasData && $producto['estado'] === 'inactivo') ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Imagen del Producto (igual que original) -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-image"></i>
                    Imagen del Producto
                </h3>
                
                <div class="file-upload-container">
                    <?php if ($hasData && (!empty($producto['url_imagen_producto']) || !empty($producto['imagen_producto']))): ?>
                        <div class="current-image-section">
                            <div class="current-image-display">
                                <?php 
                                $imagenSrc = '';
                                if (!empty($producto['url_imagen_producto'])) {
                                    $imagenSrc = $producto['url_imagen_producto'];
                                } elseif (!empty($producto['imagen_producto'])) {
                                    $imagenSrc = 'public/assets/img/products/' . $producto['imagen_producto'];
                                }
                                ?>
                                <img src="<?= htmlspecialchars($imagenSrc) ?>" alt="Imagen actual del producto" class="current-product-image" onerror="this.src='public/assets/img/default-product.jpg'; this.onerror=null;">
                            </div>
                            <?php if (!$isView): ?>
                            <div class="change-image-section">
                                <button type="button" class="btn-change-image" onclick="triggerFileInput()">
                                    <i class="fas fa-camera"></i> Cambiar imagen
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!$hasData): ?>
                    <div class="file-upload-area" id="uploadArea" onclick="triggerFileInput()">
                        <i class="fas fa-cloud-upload-alt upload-icon"></i>
                        <p class="upload-text"><strong>Haz clic aquí para seleccionar una imagen</strong><br>o arrastra y suelta tu archivo</p>
                        <small class="upload-hint">Formatos: JPG, PNG, GIF (máx. 5MB)</small>
                    </div>
                    <?php endif; ?>

                    <input type="file" id="imagen" name="imagen_producto" accept="image/*" style="display:none;">
                    <div id="imagePreview" class="image-preview-section" style="display:none;">
                        <div class="preview-image-display"><img id="previewImg" src="" alt="Preview de nueva imagen" class="preview-product-image"></div>
                        <div class="preview-actions-section"><button type="button" class="btn-remove-image" onclick="removeImagePreview()"><i class="fas fa-trash"></i> Eliminar imagen</button></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</button>
            <?php if (!$isView): ?>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= $isEdit ? 'Actualizar' : 'Guardar' ?> Producto</button>
            <?php endif; ?>
        </div>
    </form>
</div>
<!-- SCRIPTS compartidos para crear/editar (idénticos a originales, no afectan la vista) -->
<script>
/**
 * Scripts mejorados para modal "Ver Producto" (y reutilizables para crear/editar)
 * - Animaciones suaves (entrada, salida, stagger, parallax)
 * - File upload con recorte en canvas (preview) y reemplazo del archivo enviado
 * - Submit AJAX con animación del botón y manejo de respuesta
 * - Scroll/touch handling para evitar que la página detrás se mueva
 *
 * Pegar este <script> tal cual en el modal (ya no necesita más cambios).
 */

(function () {
  'use strict';

  /* --------------------------
     Helpers
     -------------------------- */
  function $(selector, ctx = document) { return ctx.querySelector(selector); }
  function $all(selector, ctx = document) { return Array.from((ctx || document).querySelectorAll(selector)); }
  function isTouch() { return ('ontouchstart' in window) || navigator.maxTouchPoints > 0; }

  function dataURLToBlob(dataURL) {
    // dataURL: "data:image/jpeg;base64,...."
    const parts = dataURL.split(',');
    const mime = parts[0].match(/:(.*?);/)[1];
    const bstr = atob(parts[1]);
    let n = bstr.length;
    const u8 = new Uint8Array(n);
    while (n--) u8[n] = bstr.charCodeAt(n);
    return new Blob([u8], { type: mime });
  }

  function animateClassOnce(el, klass, duration = 400) {
    if (!el) return;
    el.classList.add(klass);
    setTimeout(() => el.classList.remove(klass), duration + 20);
  }

  /* --------------------------
     File upload + preview (cropping square)
     -------------------------- */
  function setupFileUploadScoped(root = document) {
    const fileInput = $('#imagen', root);
    const uploadArea = $('#uploadArea', root);
    const preview = $('#imagePreview', root);
    const previewImg = $('#previewImg', root);
    const currentImageSection = $('.current-image-section', root);

    if (!fileInput) return; // funciona sólo en crear/editar

    // Reemplazar input para quitar event listeners previos
    const newInput = fileInput.cloneNode(true);
    fileInput.parentNode.replaceChild(newInput, fileInput);

    newInput.addEventListener('change', handleFileSelect);

    if (uploadArea) {
      uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('dragover');
      });
      uploadArea.addEventListener('dragleave', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
      });
      uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        const files = e.dataTransfer.files;
        if (files && files.length) {
          // asignar archivos al input
          try {
            newInput.files = files;
          } catch (err) {
            // algunos navegadores no permiten asignar FileList directamente
          }
          handleFileSelect({ target: { files } });
        }
      });
    }

    function handleFileSelect(e) {
      if (!e || !e.target || !e.target.files || !e.target.files[0]) return;
      const file = e.target.files[0];

      // Validaciones
      if (!file.type.startsWith('image/')) {
        alert('Por favor, selecciona un archivo de imagen válido.');
        return;
      }
      if (file.size > 5 * 1024 * 1024) {
        alert('El archivo es demasiado grande. Máximo 5MB.');
        return;
      }

      const reader = new FileReader();
      reader.onload = function (ev) {
        const tmp = new Image();
        tmp.onload = function () {
          // MOSTRAR imagen completa SIN recortar (contain) - igual que categorías
          const targetSize = 800;
          const canvas = document.createElement('canvas');
          canvas.width = targetSize;
          canvas.height = targetSize;
          const ctx = canvas.getContext('2d');

          // Calcular escala para que la imagen COMPLETA entre en el canvas (contain)
          const scale = Math.min(targetSize / tmp.width, targetSize / tmp.height);
          const scaledWidth = tmp.width * scale;
          const scaledHeight = tmp.height * scale;

          // Centrar la imagen
          const offsetX = (targetSize - scaledWidth) / 2;
          const offsetY = (targetSize - scaledHeight) / 2;

          // FONDO TRANSPARENTE (sin relleno blanco)
          // El CSS se encargará del fondo con rgba(255,255,255,0.05)
          
          // Dibujar imagen completa SIN recortar (contain)
          ctx.drawImage(tmp, offsetX, offsetY, scaledWidth, scaledHeight);

          // Convertir a dataURL con PNG para mantener transparencia
          const dataUrl = canvas.toDataURL('image/png', 0.9);

          // Mostrar preview (animado)
          if (previewImg) {
            previewImg.src = dataUrl;
          }
          if (preview) {
            preview.style.display = 'block';
            preview.style.opacity = '1';
          }

          // ocultar uploadArea y currentImageSection
          if (uploadArea) uploadArea.style.display = 'none';
          if (currentImageSection) currentImageSection.style.display = 'none';

          // Guardar dataURL en el input como atributo para que setupFormSubmit pueda usarlo
          newInput.dataset.cropped = dataUrl;
        };
        tmp.src = ev.target.result;
      };
      reader.readAsDataURL(file);
    }
  }

  /* --------------------------
     Detección de cambios en el formulario
     -------------------------- */
  let originalFormData = {};
  
  function captureOriginalFormData(form) {
    originalFormData = {};
    const formData = new FormData(form);
    for (let [key, value] of formData.entries()) {
      if (value instanceof File) {
        originalFormData[key] = value.name + '|' + value.size;
      } else {
        originalFormData[key] = value;
      }
    }
  }
  
  function hasFormChanged(form) {
    const currentFormData = new FormData(form);
    let hasChanges = false;
    
    // Comparar cada campo
    for (let [key, value] of currentFormData.entries()) {
      let currentValue = value;
      if (value instanceof File) {
        // Ignorar archivos vacíos (no seleccionados)
        if (value.size === 0) continue;
        currentValue = value.name + '|' + value.size;
        if (originalFormData[key] !== currentValue) {
          hasChanges = true;
        }
      } else {
        if (originalFormData[key] !== currentValue) {
          hasChanges = true;
        }
      }
    }
    
    // Verificar si se eliminaron campos
    for (let key in originalFormData) {
      if (!currentFormData.has(key) && key !== 'imagen_producto') {
        hasChanges = true;
      }
    }
    
    return hasChanges;
  }

  /* --------------------------
     Form submit (AJAX) con reemplazo de imagen por preview cropped
     -------------------------- */
  function setupFormSubmitScoped(root = document) {
    const form = $('#productForm', root);
    if (!form) return;
    
    // Capturar datos originales cuando se carga el formulario
    setTimeout(() => captureOriginalFormData(form), 500);

    form.addEventListener('submit', async function (ev) {
      ev.preventDefault();
      
      // VALIDACIÓN: Verificar si realmente hubo cambios
      if (!hasFormChanged(form)) {
        
        // Mostrar mensaje sutil
        if (window.parent && typeof window.parent.showNotification === 'function') {
          window.parent.showNotification('No se realizaron cambios', 'info');
        }
        
        // ✅ Cerrar modal INMEDIATAMENTE sin actualizar tabla
        if (typeof window.closeProductModal === 'function') {
          window.closeProductModal();
        } else if (typeof window.parent !== 'undefined' && typeof window.parent.closeProductModal === 'function') {
          window.parent.closeProductModal();
        }
        
        return; // Detener el envío
      }
      
      const targetUrl = (window.AppConfig && typeof window.AppConfig.getApiUrl === 'function')
        ? window.AppConfig.getApiUrl('ProductController.php')
        : '/app/controllers/ProductController.php';

      // Botón submit
      const submitBtn = form.querySelector('button[type="submit"]');
      const originalHTML = submitBtn ? submitBtn.innerHTML : null;

      if (submitBtn) {
        submitBtn.innerHTML = '<span class="pv-spinner" aria-hidden="true" style="display:inline-block;vertical-align:middle;"><i class="fas fa-spinner fa-spin"></i></span> Guardando...';
        submitBtn.disabled = true;
        submitBtn.style.pointerEvents = 'none';
      }

      try {
        // Construir FormData y, si existe preview cropped, reemplazar la imagen
        const fd = new FormData(form);

        // buscar input file y su dataset.cropped
        const fileInput = $('#imagen', form);
        if (fileInput && fileInput.dataset && fileInput.dataset.cropped) {
          try {
            // quitar entrada anterior (si existe)
            // NOTE: algunos navegadores añaden automáticamente el archivo, FormData.delete debe funcionar
            fd.delete('imagen_producto');
            const blob = dataURLToBlob(fileInput.dataset.cropped);
            // nombre de archivo recomendado
            const filename = (fileInput.files && fileInput.files[0] && fileInput.files[0].name) ? fileInput.files[0].name.replace(/\.[^/.]+$/, '') + '-cropped.jpg' : 'imagen-cropped.jpg';
            fd.append('imagen_producto', blob, filename);
          } catch (err) {
          }
        }

        // Fetch con timeout (opcional)
        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 20000);

        const resp = await fetch(targetUrl, {
          method: 'POST',
          body: fd,
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          signal: controller.signal
        });
        clearTimeout(timeout);

        if (!resp.ok) throw new Error('HTTP ' + resp.status + ' ' + resp.statusText);
        const text = await resp.text();

        let data;
        try { data = JSON.parse(text); } catch (err) { throw new Error('Respuesta no JSON: ' + err.message); }

        if (data.success) {
          // ⭐ GUARDAR TAB ACTIVO EN LOCALSTORAGE (PARENT)
          if (window.parent && window.parent.localStorage) {
            window.parent.localStorage.setItem('admin_active_tab', 'productos');
            console.log('💾 Tab "productos" guardado en localStorage');
          }
          
          // ⭐ MOSTRAR NOTIFICACIÓN DE ÉXITO
          const isEdit = form.querySelector('input[name="id_producto"]') && form.querySelector('input[name="id_producto"]').value;
          if (window.parent && typeof window.parent.showNotification === 'function') {
            window.parent.showNotification(
              isEdit ? 'Producto actualizado correctamente' : 'Producto creado correctamente',
              'success'
            );
          }
          
          // Primero recargar la tabla con el producto actualizado
          if (data.product) {
            reloadParentProductsTable(data.product);
          }
          
          // ✅ CIERRE INMEDIATO - Sin delays
          if (typeof window.closeProductModal === 'function') {
            window.closeProductModal();
          } else if (typeof window.parent !== 'undefined' && typeof window.parent.closeProductModal === 'function') {
            window.parent.closeProductModal();
          } else {
            console.error('❌ closeProductModal no disponible');
          }
        } else {
          // ⭐ MOSTRAR NOTIFICACIÓN DE ERROR
          const errorMsg = data.error || data.message || 'Error al guardar producto';
          if (window.parent && typeof window.parent.showNotification === 'function') {
            window.parent.showNotification(errorMsg, 'error');
          } else {
            alert(errorMsg);
          }
        }

      } catch (error) {
        if (window.parent && typeof window.parent.showNotification === 'function') {
          window.parent.showNotification('Error de conexión al guardar producto', 'error');
        } else {
          alert('Error de conexión al guardar producto: ' + (error && error.message ? error.message : error));
        }
      } finally {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.style.pointerEvents = '';
          if (originalHTML !== null) submitBtn.innerHTML = originalHTML;
        }
      }
    });
  }

  /* --------------------------
     RECARGA AUTOMÁTICA DE TABLA DESPUÉS DE GUARDAR
     -------------------------- */
  function reloadParentProductsTable(updatedProduct = null) {
    
    // Intentar actualización suave primero
    try {
      // Acceder a window.parent o window dependiendo del contexto
      const targetWindow = (window.parent && window.parent !== window) ? window.parent : window;
      
      if (targetWindow.smoothTableUpdater && updatedProduct) {
        targetWindow.smoothTableUpdater.updateSingleProduct(updatedProduct.id_producto, updatedProduct)
          .then(() => {
          })
          .catch(err => {
            // Fallback a recarga completa
            fallbackReload(targetWindow);
          });
      } else {
        fallbackReload(targetWindow);
      }
    } catch (err) {
    }
  }
  
  function fallbackReload(targetWindow) {
    if (typeof targetWindow.loadProducts === 'function') {
      targetWindow.loadProducts();
    } else if (typeof targetWindow.loadProductos === 'function') {
      targetWindow.loadProductos();
    }
  }

  /* --------------------------
     Animaciones / Parallax / Accessibility / Scroll lock
     -------------------------- */
  function pvEnhanceScoped(root = document) {
    const modalRoot = root.querySelector('.product-view-modal') || root;
    if (!modalRoot) return;

    // stagger reveal sections - DESACTIVADO para evitar delays al cerrar
    const sections = $all('.info-section', modalRoot);
    sections.forEach((s) => s.classList.add('pv-show')); // Inmediato sin setTimeout

    // parallax blobs and image tilt (only non-touch)
    const header = $('.modern-modal-header', modalRoot);
    const blobs = $all('.pv-blob', modalRoot);
    const imageContainer = $('.image-container', modalRoot);

    if (header && !isTouch()) {
      header.addEventListener('mousemove', (ev) => {
        const r = header.getBoundingClientRect();
        const cx = r.left + r.width / 2;
        const cy = r.top + r.height / 2;
        const dx = (ev.clientX - cx) / r.width;
        const dy = (ev.clientY - cy) / r.height;
        blobs.forEach((b, idx) => {
          const speed = idx === 0 ? 18 : 8;
          b.style.transform = `translate3d(${dx * speed}px, ${-dy * speed}px, 0)`;
        });
        if (imageContainer) imageContainer.style.transform = `perspective(900px) rotateX(${dy * 4}deg) rotateY(${dx * 6}deg)`;
      });
      header.addEventListener('mouseleave', () => {
        blobs.forEach(b => b.style.transform = '');
        if (imageContainer) imageContainer.style.transform = '';
      });
    }

    // focus close button - SIN DELAY
    const closeBtn = $('.modern-modal-close', modalRoot) || $('.modal-close', modalRoot);
    if (closeBtn) closeBtn.focus(); // Inmediato sin setTimeout

    // prevent body scroll leak: allow scroll only inside modal body
    const body = $('.modern-modal-body', modalRoot);
    if (body) {
      body.addEventListener('wheel', (e) => {
        const atTop = body.scrollTop === 0 && e.deltaY < 0;
        const atBottom = Math.ceil(body.scrollTop + body.clientHeight) >= body.scrollHeight && e.deltaY > 0;
        if (atTop || atBottom) e.preventDefault();
      }, { passive: false });

      let startY = 0;
      body.addEventListener('touchstart', (e) => { startY = e.touches ? e.touches[0].clientY : 0; }, { passive: true });
      body.addEventListener('touchmove', (e) => {
        const curY = e.touches ? e.touches[0].clientY : 0;
        const diff = startY - curY;
        const atTop = body.scrollTop === 0 && diff < 0;
        const atBottom = Math.ceil(body.scrollTop + body.clientHeight) >= body.scrollHeight && diff > 0;
        if (atTop || atBottom) e.preventDefault();
      }, { passive: false });
    }

    // close with smooth animation when close button is pressed
    const closeButtons = $all('.modern-modal-close, .modal-close, .product-view-modal__close, .product-view-modal__btn--secondary, .btn-secondary', modalRoot);
    closeButtons.forEach(btn => {
      btn.addEventListener('click', (ev) => {
        ev.preventDefault();
        ev.stopPropagation(); // Prevenir burbujeo de eventos
        
        console.log('🔘 Botón de cerrar modal clickeado');
        
        // Llamar a closeProductModal() globalmente
        if (typeof window.closeProductModal === 'function') {
          console.log('✅ Llamando a window.closeProductModal()');
          window.closeProductModal();
        } else if (typeof window.parent !== 'undefined' && typeof window.parent.closeProductModal === 'function') {
          console.log('✅ Llamando a window.parent.closeProductModal()');
          window.parent.closeProductModal();
        } else {
          console.error('❌ closeProductModal no encontrado');
        }
      }, { once: true }); // once:true previene clicks múltiples
    });
    
    // Prevenir cierre al hacer clic dentro del modal-content
    const modalContent = $('.modal-content', modalRoot) || $('.product-view-modal__container', modalRoot);
    if (modalContent) {
      modalContent.addEventListener('click', (e) => {
        e.stopPropagation(); // Prevenir que el clic se propague al overlay
        console.log('🖱️ Clic dentro del modal-content, previniendo cierre');
      });
    }
  }

  /* --------------------------
     Funciones globales para HTML onclick
     -------------------------- */
  window.triggerFileInput = function() {
    const fileInput = document.getElementById('imagen');
    if (fileInput) {
      fileInput.click();
    }
  };

  window.removeImagePreview = function() {
    const fileInput = document.getElementById('imagen');
    const preview = document.getElementById('imagePreview');
    const uploadArea = document.getElementById('uploadArea');
    const currentImageSection = document.querySelector('.current-image-section');
    
    if (fileInput) {
      fileInput.value = '';
      delete fileInput.dataset.cropped;
    }
    
    if (preview) {
      preview.style.display = 'none';
      const previewImg = document.getElementById('previewImg');
      if (previewImg) previewImg.src = '';
    }
    
    // Mostrar upload area o current image según corresponda
    if (currentImageSection) {
      currentImageSection.style.display = 'block';
    } else if (uploadArea) {
      uploadArea.style.display = 'flex';
    }
  };

  /* --------------------------
     Initializer
     -------------------------- */
  function initModalScripts(context = document) {
    // setup file upload (only if input exists)
    try { setupFileUploadScoped(context); } catch (e) { }

    // setup form submit
    try { setupFormSubmitScoped(context); } catch (e) { }

    // enhance view modal animations & behavior
    try { pvEnhanceScoped(context); } catch (e) { }
    
    // Hacer que la imagen sea fixed pero mantener su posición en el grid
    try {
      const imageSection = context.querySelector('.product-view-modal__image-section');
      const modalBody = context.querySelector('.product-view-modal__body');
      const modalContainer = context.querySelector('.product-view-modal__container');
      
      if (imageSection && modalBody && modalContainer) {
        // Guardar la posición original
        const rect = imageSection.getBoundingClientRect();
        const leftPosition = rect.left;
        
        // Obtener la posición del header para calcular el centro del body
        const header = context.querySelector('.product-view-modal__header');
        const footer = context.querySelector('.product-view-modal__footer');
        const headerHeight = header ? header.offsetHeight : 0;
        const footerHeight = footer ? footer.offsetHeight : 0;
        
        // Calcular el centro del area del body
        const containerRect = modalContainer.getBoundingClientRect();
        const bodyTop = containerRect.top + headerHeight;
        const bodyHeight = containerRect.height - headerHeight - footerHeight;
        const centerY = bodyTop + (bodyHeight / 2);
        
        // Hacer la imagen fixed
        imageSection.style.position = 'fixed';
        imageSection.style.left = leftPosition + 'px';
        imageSection.style.top = centerY + 'px';
        imageSection.style.transform = 'translateY(-50%)';
        imageSection.style.width = '400px';
        
        // Actualizar posición si se redimensiona la ventana
        window.addEventListener('resize', function() {
          const newRect = imageSection.parentElement.getBoundingClientRect();
          imageSection.style.left = newRect.left + 'px';
        });
      }
    } catch (e) { }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => initModalScripts(document));
  } else {
    initModalScripts(document);
  }

  // Exponer utilidades en window por si otro script necesita reinicializar (por ejemplo modales dinámicos)
  window.PVModal = {
    init: initModalScripts,
    dataURLToBlob
  };

})();
</script>
<?php endif; ?>