<?php
// Modal para categorías - Vista PHP
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

// Si es edición o vista, obtener datos de la categoría
$categoria = null;
$action = isset($_GET['action']) ? $_GET['action'] : 'create';
$isView = $action === 'view' && isset($_GET['id']);
$isEdit = $action === 'edit' && isset($_GET['id']);
$isCreate = $action === 'create';

if (($isEdit || $isView) && isset($_GET['id'])) {
    try {
        $stmt = $conn->prepare("SELECT * FROM categoria WHERE id_categoria = ?");
        $stmt->execute([$_GET['id']]);
        $categoria = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $categoria = null;
    }
}

$hasData = $categoria !== null;
$modalTitle = $isView ? 'Ver Categoría' : ($isEdit ? 'Editar Categoría' : 'Nueva Categoría');
$iconClass = $isView ? 'eye' : ($isEdit ? 'edit' : 'plus');
?>

<?php if ($isView && $hasData): ?>

<!-- VIEW: Modal Ver Categoría - Diseño Profesional Completo -->
<div id="categoria-view-modal" class="categoria-view-modal" style="opacity: 0; visibility: hidden; pointer-events: none;">
    <!-- IMPORTANTE: ID único para evitar conflictos CSS -->
    <!-- Overlay de fondo - SIN onclick para evitar duplicación (admin.php ya tiene el listener) -->
    <div class="categoria-view-modal__overlay"></div>
    
    <!-- Contenedor del modal -->
    <div class="categoria-view-modal__container">
        <!-- HEADER -->
        <div class="categoria-view-modal__header">
            <div class="categoria-view-modal__header-content">
                <h2 class="categoria-view-modal__title">
                    <span class="categoria-view-modal__title-icon">
                        <i class="fas fa-eye"></i>
                    </span>
                    Ver Categoría
                </h2>
                
                <div class="categoria-view-modal__badge">
                    <i class="fas fa-lock"></i>
                    Solo Lectura
                </div>
            </div>
            
            <button type="button" class="categoria-view-modal__close" onclick="closeCategoriaModal()" aria-label="Cerrar">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- BODY -->
        <div class="categoria-view-modal__body">
            <div class="categoria-view-modal__content">
                <!-- PANEL IZQUIERDO: IMAGEN -->
                <div class="categoria-view-modal__image-section">
                    <div class="categoria-view-modal__image-container">
                        <?php
                        $imagenSrc = '';
                        if (!empty($categoria['url_imagen_categoria'])) {
                            $imagenSrc = $categoria['url_imagen_categoria'];
                        } elseif (!empty($categoria['imagen_categoria'])) {
                            $imagenSrc = 'public/assets/img/products/' . $categoria['imagen_categoria'];
                        }
                        ?>
                        
                        <?php if ($imagenSrc): ?>
                            <img src="<?= htmlspecialchars($imagenSrc) ?>" 
                                 alt="<?= htmlspecialchars($categoria['nombre_categoria']) ?>" 
                                 class="categoria-view-modal__image"
                                 onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'categoria-view-modal__image-placeholder\'><i class=\'fas fa-image\'></i><span>Imagen no disponible</span></div>'">
                        <?php else: ?>
                            <div class="categoria-view-modal__image-placeholder">
                                <i class="fas fa-image"></i>
                                <span>Sin imagen</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="categoria-view-modal__image-name">
                        <?= htmlspecialchars($categoria['nombre_categoria']) ?>
                    </div>
                </div>

                <!-- PANEL DERECHO: INFORMACIÓN -->
                <div class="categoria-view-modal__info-section">
                    <!-- INFORMACIÓN BÁSICA -->
                    <div class="categoria-view-modal__card">
                        <div class="categoria-view-modal__card-header">
                            <div class="categoria-view-modal__card-icon">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <h3 class="categoria-view-modal__card-title">Información Básica</h3>
                        </div>
                        
                        <div class="categoria-view-modal__grid">
                            <div class="categoria-view-modal__field">
                                <label class="categoria-view-modal__field-label">Nombre</label>
                                <div class="categoria-view-modal__field-value">
                                    <?= htmlspecialchars($categoria['nombre_categoria']) ?>
                                </div>
                            </div>
                            
                            <div class="categoria-view-modal__field">
                                <label class="categoria-view-modal__field-label">Código</label>
                                <div class="categoria-view-modal__field-value categoria-view-modal__field-value--code">
                                    <?= htmlspecialchars($categoria['codigo_categoria'] ?: 'N/A') ?>
                                </div>
                            </div>
                            
                            <div class="categoria-view-modal__field">
                                <label class="categoria-view-modal__field-label">Estado</label>
                                <div class="categoria-view-modal__field-value">
                                    <?php 
                                    $isActive = isset($categoria['estado_categoria']) && $categoria['estado_categoria'] === 'activo';
                                    ?>
                                    <span class="categoria-view-modal__status-badge categoria-view-modal__status-badge--<?= $isActive ? 'active' : 'inactive' ?>">
                                        <i class="fas fa-<?= $isActive ? 'check-circle' : 'times-circle' ?>"></i>
                                        <?= $isActive ? 'Activo' : 'Inactivo' ?>
                                    </span>
                                </div>
                            </div>
                            
                            <?php if (!empty($categoria['descripcion_categoria'])): ?>
                            <div class="categoria-view-modal__description">
                                <label class="categoria-view-modal__field-label">Descripción</label>
                                <p class="categoria-view-modal__description-text">
                                    <?= nl2br(htmlspecialchars($categoria['descripcion_categoria'])) ?>
                                </p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- INFORMACIÓN ADICIONAL -->
                    <div class="categoria-view-modal__card">
                        <div class="categoria-view-modal__card-header">
                            <div class="categoria-view-modal__card-icon">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <h3 class="categoria-view-modal__card-title">Información Adicional</h3>
                        </div>
                        
                        <div class="categoria-view-modal__grid">
                            <div class="categoria-view-modal__field">
                                <label class="categoria-view-modal__field-label">Fecha de Registro</label>
                                <div class="categoria-view-modal__field-value">
                                    <?php
                                    $fecha = $categoria['fecha_registro_categoria'] ?? $categoria['fecha_creacion_categoria'] ?? null;
                                    if ($fecha) {
                                        $dt = new DateTime($fecha);
                                        echo $dt->format('d/m/Y H:i');
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </div>
                            </div>
                            
                            <?php if (isset($categoria['fecha_actualizacion_categoria']) && !empty($categoria['fecha_actualizacion_categoria'])): ?>
                            <div class="categoria-view-modal__field">
                                <label class="categoria-view-modal__field-label">Última Actualización</label>
                                <div class="categoria-view-modal__field-value">
                                    <?php
                                    $dt = new DateTime($categoria['fecha_actualizacion_categoria']);
                                    echo $dt->format('d/m/Y H:i');
                                    ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="categoria-view-modal__footer">
            <button type="button" class="categoria-view-modal__btn categoria-view-modal__btn--secondary" onclick="closeCategoriaModal()">
                <i class="fas fa-times"></i>
                Cerrar
            </button>
        </div>
    </div>
</div>

<!-- Script para animación de entrada del modal VIEW -->
<script>
(function() {
    'use strict';
    
    console.log('[CATEGORIA VIEW] Inicializando modal de vista...');
    
    // Animar entrada del modal
    const modal = document.querySelector('.categoria-view-modal');
    if (modal) {
        requestAnimationFrame(() => {
            modal.classList.add('show');
        });
    }
    
    // Cerrar con ESC
    const handleEsc = (e) => {
        if (e.key === 'Escape') {
            closeCategoriaModal();
            document.removeEventListener('keydown', handleEsc);
        }
    };
    document.addEventListener('keydown', handleEsc);
})();
</script>

<?php endif; ?>

<!-- ============================================ -->
<!-- FORMULARIO CREATE/EDIT -->
<!-- ============================================ -->
<?php if (!$isView): ?>

<!-- CREAR / EDITAR: DISEÑO EXACTAMENTE IGUAL A PRODUCTOS -->
<div class="modal-content">
    <div class="modal-header">
        <h2 class="modal-title">
            <i class="fas fa-<?= $iconClass ?>"></i>
            <?= $modalTitle ?>
        </h2>
        <button type="button" class="modal-close" onclick="closeCategoriaModal()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <form id="categoriaForm" enctype="multipart/form-data">
        <input type="hidden" name="action" value="<?= $isEdit ? 'update' : 'create' ?>">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id_categoria" value="<?= $categoria['id_categoria'] ?>">
            <input type="hidden" name="status_categoria" value="<?= $categoria['status_categoria'] ?>">
        <?php else: ?>
            <input type="hidden" name="status_categoria" value="1">
        <?php endif; ?>
        
        <!-- Contenedor de errores -->
        <div id="categoriaFormError" style="display: none; margin: 1rem 1.5rem; padding: 1rem; background: #fee; border-left: 4px solid #f44336; border-radius: 4px;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-exclamation-circle" style="color: #f44336; font-size: 1.2rem;"></i>
                <span id="categoriaFormErrorMessage" style="color: #c62828; font-weight: 500;"></span>
            </div>
        </div>
        
        <div class="modal-body">
            <!-- Información básica -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-info-circle"></i>
                    Información de la Categoría
                </h3>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nombre_categoria">
                            <i class="fas fa-tag"></i>
                            Nombre de la Categoría *
                        </label>
                        <input type="text" 
                               id="nombre_categoria" 
                               name="nombre_categoria" 
                               value="<?= $hasData ? htmlspecialchars($categoria['nombre_categoria']) : '' ?>"
                               required 
                               maxlength="100"
                               placeholder="Ej: Ropa Deportiva, Zapatos, Accesorios...">
                    </div>
                    
                    <div class="form-group">
                        <label for="codigo_categoria">
                            <i class="fas fa-barcode"></i>
                            Código de Categoría
                        </label>
                        <input type="text" 
                               id="codigo_categoria" 
                               name="codigo_categoria" 
                               value="<?= $hasData ? htmlspecialchars($categoria['codigo_categoria']) : '' ?>"
                               maxlength="50"
                               placeholder="Ej: CAT-001, ROPA-DEP...">
                        <small class="form-text">Código único opcional para identificar la categoría</small>
                    </div>
                </div>
            </div>
            
            <!-- Descripción -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-align-left"></i>
                    Descripción
                </h3>
                
                <div class="form-group">
                    <textarea id="descripcion_categoria" 
                              name="descripcion_categoria" 
                              rows="4" 
                              maxlength="500"
                              placeholder="Describe las características principales de la categoría..."><?= $hasData ? htmlspecialchars($categoria['descripcion_categoria']) : '' ?></textarea>
                    <small class="form-text">Máximo 500 caracteres</small>
                </div>
            </div>
            
            <!-- Estado -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-toggle-on"></i>
                    Estado
                </h3>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="estado_categoria">
                            <i class="fas fa-power-off"></i>
                            Estado de la Categoría *
                        </label>
                        <select id="estado_categoria" 
                                name="estado_categoria" 
                                class="form-control"
                                required>
                            <option value="activo" <?= ($hasData && $categoria['estado_categoria'] === 'activo') ? 'selected' : '' ?>>Activo</option>
                            <option value="inactivo" <?= ($hasData && $categoria['estado_categoria'] === 'inactivo') ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Imagen de la Categoría -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-image"></i>
                    Imagen de la Categoría
                </h3>
                
                <div class="file-upload-container">
                    <?php if ($hasData && !empty($categoria['url_imagen_categoria'])): ?>
                        <div class="current-image-section">
                            <div class="current-image-display">
                                <img src="<?= htmlspecialchars($categoria['url_imagen_categoria']) ?>" 
                                     alt="Imagen actual de la categoría" 
                                     class="current-product-image" 
                                     onerror="this.src='public/assets/img/default-category.jpg'; this.onerror=null;">
                            </div>
                            <div class="change-image-section">
                                <button type="button" class="btn-change-image" onclick="document.getElementById('imagen_categoria').click()">
                                    <i class="fas fa-camera"></i> Cambiar imagen
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="file-upload-area" id="uploadArea" onclick="document.getElementById('imagen_categoria').click()">
                            <i class="fas fa-cloud-upload-alt upload-icon"></i>
                            <p class="upload-text"><strong>Haz clic aquí para seleccionar una imagen</strong><br>o arrastra y suelta tu archivo</p>
                            <small class="upload-hint">Formatos: JPG, PNG, GIF (máx. 2MB)</small>
                        </div>
                    <?php endif; ?>

                    <input type="file" id="imagen_categoria" name="imagen_categoria" accept="image/*" style="display:none;">
                </div>
            </div>
        </div>
        
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeCategoriaModal()">
                <i class="fas fa-times"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> <?= $isEdit ? 'Actualizar' : 'Guardar' ?> <span class="btn-text-mobile-hide">Categoría</span>
            </button>
        </div>
    </form>
</div>

<!-- SCRIPTS para el formulario -->
<script>
(function() {
    'use strict';
    
    // ============================================
    // FUNCIONES AUXILIARES PARA COMUNICACIÓN CON VENTANA PADRE
    // ============================================
    
    // Función para obtener la ventana padre
    // Como el modal se carga con fetch() e inyecta en overlay, usar window directamente
    function getParentWindow() {
        // El modal está inyectado en la misma página, no es iframe ni ventana nueva
        // Las funciones ya están en window global
        console.log('📡 Usando window (modal inyectado en página)');
        return window;
    }
    
    // Función para cerrar el modal
    window.closeCategoriaModal = function() {
        console.log('❌ Cerrando modal de categoría');
        
        // Llamar a la función global definida en admin.php
        if (typeof window.closeCategoriaModal === 'function') {
            // Prevenir recursión: esta función YA ES closeCategoriaModal
            // Necesitamos llamar a la del overlay
            const overlay = document.getElementById('categoria-modal-overlay');
            if (overlay) {
                overlay.classList.remove('show');
                setTimeout(() => {
                    overlay.remove();
                    document.body.classList.remove('modal-open');
                    
                    // Recargar lista de categorías
                    if (typeof window.loadCategorias === 'function') {
                        window.loadCategorias();
                    }
                }, 300);
            } else {
                document.body.classList.remove('modal-open');
            }
        }
    };
    
    // Función para actualizar una categoría individual
    window.updateSingleCategoria = async function(id, data) {
        console.log('🔄 Actualizando categoría en tabla:', id);
        
        // Recargar toda la lista (más simple)
        if (typeof window.loadCategorias === 'function') {
            window.loadCategorias();
        }
    };
    
    // Función para recargar la lista de categorías
    window.loadCategoriasData = function() {
        console.log('🔄 Recargando categorías');
        
        if (typeof window.loadCategorias === 'function') {
            window.loadCategorias();
        }
    };
    
    // ============================================
    // FIN FUNCIONES AUXILIARES
    // ============================================
    
    // ⭐ FUNCIÓN PRINCIPAL PARA GUARDAR (EVITA REFRESH DE PÁGINA)
    async function guardarCategoria() {
        console.log('🚀 Iniciando guardado de categoría (SIN REFRESH)');
        
        const form = document.getElementById('categoriaForm');
        if (!form) {
            console.error('❌ Formulario no encontrado');
            return;
        }
        
        // ⭐ GUARDAR TAB ACTIVO ANTES DE CUALQUIER OPERACIÓN
        console.log('💾 Forzando tab "categorias" en localStorage');
        localStorage.setItem('admin_active_tab', 'categorias');
        
        // Ocultar errores previos
        const errorContainer = document.getElementById('categoriaFormError');
        const errorMessage = document.getElementById('categoriaFormErrorMessage');
        if (errorContainer) errorContainer.style.display = 'none';
        
        const formData = new FormData(form);
        const submitBtn = document.getElementById('guardarCategoriaBtn');
        const originalText = submitBtn ? submitBtn.innerHTML : '';
        const isEdit = formData.get('action') === 'update';
        const categoriaId = formData.get('id_categoria');
        
        // ========== DEBUG: Mostrar TODO el FormData ==========
        console.log('📋 === CONTENIDO COMPLETO DE FORMDATA ===');
        for (let [key, value] of formData.entries()) {
            console.log(`  ${key}:`, value);
        }
        console.log('=========================================');
        
        console.log('📤 Enviando datos:', {
            action: formData.get('action'),
            id: categoriaId,
            codigo: formData.get('codigo_categoria'),
            nombre: formData.get('nombre_categoria'),
            descripcion: formData.get('descripcion_categoria'),
            estado: formData.get('estado_categoria')
        });
        
        // Deshabilitar botón y mostrar loading
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
        }
        
        try {
            // URL del controlador
            const controllerUrl = '/fashion-master/app/controllers/categoriaController.php';
            
            const response = await fetch(controllerUrl, {
                method: 'POST',
                body: formData
            });
            
            // Verificar si la respuesta es JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('El servidor no devolvió una respuesta JSON válida');
            }
            
            const result = await response.json();
            
            console.log('📥 RESPUESTA COMPLETA:', result);
            console.log('📊 DATOS RECIBIDOS:', result.data);
            if (result.data) {
                console.log('🔢 CÓDIGO EN RESPUESTA:', result.data.codigo_categoria);
            }
            
            if (result.success) {
                console.log('✅ Categoría guardada exitosamente:', result);
                
                // ⭐ RE-CONFIRMAR TAB ACTIVO ANTES DE CERRAR
                localStorage.setItem('admin_active_tab', 'categorias');
                console.log('💾 Tab "categorias" confirmado en localStorage');
                
                // Cerrar modal
                if (typeof closeCategoriaModal === 'function') {
                    closeCategoriaModal();
                }
                
                // Actualizar tabla en tiempo real (SIN REFRESH)
                if (isEdit && categoriaId && result.data) {
                    // EDITAR: Actualizar fila existente
                    console.log('🔄 Llamando updateSingleCategoria con ID:', categoriaId);
                    if (typeof updateSingleCategoria === 'function') {
                        await updateSingleCategoria(categoriaId, result.data);
                    }
                } else if (!isEdit && result.data) {
                    // CREAR: Recargar tabla para mostrar nueva categoría
                    console.log('🔄 Recargando datos de tabla (AJAX)');
                    if (typeof loadCategoriasData === 'function') {
                        loadCategoriasData();
                    }
                }
                
                console.log('✨ Operación completada - permaneciendo en Categorías (SIN REFRESH)');
            } else {
                // Mostrar error en el modal
                const errorMsg = result.error || 'Error al guardar la categoría';
                console.error('❌ Error del servidor:', errorMsg);
                
                if (errorContainer && errorMessage) {
                    errorMessage.textContent = errorMsg;
                    errorContainer.style.display = 'block';
                    
                    // Scroll al error
                    errorContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
                
                // Restaurar botón
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            }
        } catch (error) {
            console.error('❌ Error de red:', error);
            
            // Mostrar error en el modal
            const errorMsg = 'Error de conexión: ' + error.message;
            
            if (errorContainer && errorMessage) {
                errorMessage.textContent = errorMsg;
                errorContainer.style.display = 'block';
                errorContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } else {
                alert(errorMsg);
            }
            
            // Restaurar botón
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }
    }
    
    // Asignar evento al botón de guardar
    const guardarBtn = document.getElementById('guardarCategoriaBtn');
    if (guardarBtn) {
        guardarBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('🖱️ Click en botón Guardar (prevención de refresh activada)');
            guardarCategoria();
        });
    }
    
    // PREVENCIÓN ADICIONAL: Capturar submit del form (por si acaso)
    const form = document.getElementById('categoriaForm');
    if (form) {
        form.addEventListener('submit', async function(ev) {
            ev.preventDefault();
            ev.stopPropagation();
            
            console.log('📤 === SUBMIT CATEGORÍA INICIADO ===');
            
            // Botón submit
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalHTML = submitBtn ? submitBtn.innerHTML : null;
            
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
                submitBtn.disabled = true;
                submitBtn.style.pointerEvents = 'none';
            }
            
            try {
                const formData = new FormData(form);
                const isEdit = formData.get('action') === 'update';
                const categoriaId = formData.get('id_categoria');
                
                // DEBUG: Mostrar FormData
                console.log('📋 FormData:');
                for (let [key, value] of formData.entries()) {
                    console.log(`  ${key}:`, value);
                }
                
                const controllerUrl = '/fashion-master/app/controllers/categoriaController.php';
                
                const resp = await fetch(controllerUrl, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                
                if (!resp.ok) throw new Error('HTTP ' + resp.status + ' ' + resp.statusText);
                const text = await resp.text();
                
                let data;
                try { data = JSON.parse(text); } catch (err) { throw new Error('Respuesta no JSON: ' + err.message); }
                
                console.log('📥 Response:', data);
                
                if (data.success) {
                    console.log('✅ Categoría guardada');
                    
                    // Actualizar tabla usando funciones globales
                    if (data.data) {
                        if (isEdit && categoriaId) {
                            console.log('🔄 Actualizando en parent...');
                            await window.updateSingleCategoria(categoriaId, data.data);
                        } else {
                            console.log('🔄 Recargando tabla en parent...');
                            window.loadCategoriasData();
                        }
                    }
                    
                    // ⭐ CERRAR MODAL
                    window.closeCategoriaModal();
                    
                } else {
                    // Mostrar error
                    const errorMsg = data.error || 'Error al guardar';
                    console.error('❌ Error:', errorMsg);
                    
                    const errorContainer = document.getElementById('categoriaFormError');
                    const errorMessage = document.getElementById('categoriaFormErrorMessage');
                    
                    if (errorContainer && errorMessage) {
                        errorMessage.textContent = errorMsg;
                        errorContainer.style.display = 'block';
                    } else {
                        alert(errorMsg);
                    }
                }
                
            } catch (error) {
                console.error('❌ Error:', error);
                alert('Error: ' + error.message);
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.style.pointerEvents = '';
                    if (originalHTML !== null) submitBtn.innerHTML = originalHTML;
                }
            }
            
            return false;
        });
    }
    
    // ⭐ PREVENIR ENTER EN INPUTS (NO SUBMIT)
    const formInputs = form.querySelectorAll('input, textarea');
    formInputs.forEach(input => {
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && input.type !== 'textarea') {
                e.preventDefault();
                console.log('⚠️ Enter bloqueado en input (prevención de submit)');
                return false;
            }
        });
    });
    
    // Validación y preview de imagen
    const imgInput = document.getElementById('imagen_categoria');
    if (imgInput) {
        imgInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validar tamaño (2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('La imagen es muy grande. Máximo 2MB.');
                    this.value = '';
                    return;
                }
                
                // Validar tipo
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                if (!validTypes.includes(file.type)) {
                    alert('Solo se permiten imágenes (JPG, PNG, GIF, WebP).');
                    this.value = '';
                    return;
                }
                
                // Preview de la imagen
                const reader = new FileReader();
                reader.onload = function(e) {
                    const uploadArea = document.getElementById('uploadArea');
                    if (uploadArea) {
                        uploadArea.innerHTML = `
                            <div class="image-preview-container">
                                <img src="${e.target.result}" alt="Preview" class="image-preview">
                                <button type="button" class="btn-remove-image" onclick="removeImagePreview()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        `;
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Función para remover preview
    window.removeImagePreview = function() {
        const imgInput = document.getElementById('imagen_categoria');
        const uploadArea = document.getElementById('uploadArea');
        
        if (imgInput) imgInput.value = '';
        if (uploadArea) {
            uploadArea.innerHTML = `
                <i class="fas fa-cloud-upload-alt upload-icon"></i>
                <p class="upload-text"><strong>Haz clic aquí para seleccionar una imagen</strong><br>o arrastra y suelta tu archivo</p>
                <small class="upload-hint">Formatos: JPG, PNG, GIF (máx. 2MB)</small>
            `;
        }
    };
    
    // Drag and drop para imágenes
    const uploadArea = document.getElementById('uploadArea');
    if (uploadArea) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => {
                uploadArea.classList.add('drag-over');
            });
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => {
                uploadArea.classList.remove('drag-over');
            });
        });
        
        uploadArea.addEventListener('drop', function(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files.length > 0) {
                const imgInput = document.getElementById('imagen_categoria');
                if (imgInput) {
                    imgInput.files = files;
                    imgInput.dispatchEvent(new Event('change'));
                }
            }
        });
    }
})();
</script>

<?php endif; ?>

<!-- ============================================ -->
<!-- SCRIPTS COMUNES PARA CREATE/EDIT Y VIEW -->
<!-- ============================================ -->
<script>
(function() {
    'use strict';
    
    console.log('🔧 Script categoria_modal.php cargado');
    
    // ========================================
    // ⭐ PREVISUALIZACIÓN Y VALIDACIÓN DE IMAGEN
    // ========================================
    const imgInput = document.getElementById('imagen_categoria');
    if (imgInput) {
        console.log('✅ Input de imagen encontrado, configurando preview');
        
        imgInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                console.log('📸 Archivo seleccionado:', file.name, file.size, 'bytes');
                
                // Validar tamaño (2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('La imagen es muy grande. Máximo 2MB.');
                    this.value = '';
                    return;
                }
                
                // Validar tipo
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                if (!validTypes.includes(file.type)) {
                    alert('Solo se permiten imágenes (JPG, PNG, GIF, WebP).');
                    this.value = '';
                    return;
                }
                
                // Preview de la imagen
                const reader = new FileReader();
                reader.onload = function(e) {
                    console.log('✅ Imagen cargada, mostrando preview');
                    
                    // Buscar elementos existentes
                    const uploadArea = document.getElementById('uploadArea');
                    const currentImageDisplay = document.querySelector('.current-image-display');
                    const fileUploadContainer = document.querySelector('.file-upload-container');
                    
                    console.log('🔍 uploadArea:', uploadArea);
                    console.log('🔍 currentImageDisplay:', currentImageDisplay);
                    
                    if (currentImageDisplay) {
                        // MODO EDITAR: Ya existe una imagen, actualizarla
                        const img = currentImageDisplay.querySelector('img');
                        if (img) {
                            img.src = e.target.result;
                            console.log('✅ Preview actualizado en imagen existente (EDITAR)');
                        }
                    } else if (uploadArea) {
                        // MODO CREAR: Reemplazar uploadArea con preview cuadrado (1:1)
                        uploadArea.innerHTML = `
                            <div class="image-preview-container" style="text-align: center;">
                                <img src="${e.target.result}" alt="Preview" style="width: 150px; height: 150px; object-fit: contain; border-radius: 8px; margin-bottom: 10px; border: 2px solid #e0e0e0; background: rgba(0, 0, 0, 0.05);">
                                <br>
                                <button type="button" class="btn btn-secondary btn-sm" onclick="removeImagePreview()">
                                    <i class="fas fa-times"></i> Quitar imagen
                                </button>
                            </div>
                        `;
                        console.log('✅ Preview creado en uploadArea (CREAR)');
                    } else if (fileUploadContainer) {
                        // FALLBACK: Crear preview cuadrado en el contenedor
                        fileUploadContainer.innerHTML = `
                            <div class="image-preview-container" style="text-align: center;">
                                <img src="${e.target.result}" alt="Preview" style="width: 150px; height: 150px; object-fit: contain; border-radius: 8px; margin-bottom: 10px; border: 2px solid #e0e0e0; background: rgba(0, 0, 0, 0.05);">
                                <br>
                                <button type="button" class="btn btn-secondary btn-sm" onclick="removeImagePreview()">
                                    <i class="fas fa-times"></i> Quitar imagen
                                </button>
                                <input type="file" id="imagen_categoria" name="imagen_categoria" accept="image/*" style="display:none;">
                            </div>
                        `;
                        console.log('✅ Preview creado en fileUploadContainer (FALLBACK)');
                        
                        // Re-asignar el input
                        const newInput = document.getElementById('imagen_categoria');
                        if (newInput) {
                            newInput.addEventListener('change', arguments.callee);
                        }
                    } else {
                        console.error('❌ No se encontró ningún contenedor para mostrar el preview');
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    } else {
        console.warn('⚠️ Input de imagen no encontrado');
    }
    
    // Función para remover preview
    window.removeImagePreview = function() {
        console.log('🗑️ Removiendo preview de imagen');
        const imgInput = document.getElementById('imagen_categoria');
        const uploadArea = document.getElementById('uploadArea');
        const fileUploadContainer = document.querySelector('.file-upload-container');
        
        if (imgInput) {
            imgInput.value = '';
            console.log('✅ Input limpiado');
        }
        
        if (uploadArea) {
            // Restaurar uploadArea original
            uploadArea.innerHTML = `
                <i class="fas fa-cloud-upload-alt upload-icon"></i>
                <p class="upload-text"><strong>Haz clic aquí para seleccionar una imagen</strong><br>o arrastra y suelta tu archivo</p>
                <small class="upload-hint">Formatos: JPG, PNG, GIF (máx. 2MB)</small>
            `;
            uploadArea.onclick = function() { document.getElementById('imagen_categoria').click(); };
            console.log('✅ UploadArea restaurado');
        } else if (fileUploadContainer) {
            // Restaurar desde contenedor
            fileUploadContainer.innerHTML = `
                <div class="file-upload-area" id="uploadArea" onclick="document.getElementById('imagen_categoria').click()">
                    <i class="fas fa-cloud-upload-alt upload-icon"></i>
                    <p class="upload-text"><strong>Haz clic aquí para seleccionar una imagen</strong><br>o arrastra y suelta tu archivo</p>
                    <small class="upload-hint">Formatos: JPG, PNG, GIF (máx. 2MB)</small>
                </div>
                <input type="file" id="imagen_categoria" name="imagen_categoria" accept="image/*" style="display:none;">
            `;
            console.log('✅ FileUploadContainer restaurado');
            
            // Re-configurar el nuevo input
            const newInput = document.getElementById('imagen_categoria');
            if (newInput && window.setupImagePreview) {
                window.setupImagePreview();
            }
        }
    };
    
    // ========================================
    // ⭐ DRAG AND DROP PARA IMÁGENES
    // ========================================
    const uploadArea = document.getElementById('uploadArea');
    if (uploadArea) {
        console.log('✅ Upload area encontrada, configurando drag & drop');
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => {
                uploadArea.classList.add('drag-over');
            });
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => {
                uploadArea.classList.remove('drag-over');
            });
        });
        
        uploadArea.addEventListener('drop', function(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files.length > 0) {
                console.log('📂 Archivo arrastrado:', files[0].name);
                const imgInput = document.getElementById('imagen_categoria');
                if (imgInput) {
                    imgInput.files = files;
                    imgInput.dispatchEvent(new Event('change'));
                }
            }
        });
    } else {
        console.warn('⚠️ Upload area no encontrada');
    }
    
    // ========================================
    // ⭐ CAPTURAR SUBMIT DEL FORM
    // ========================================
    const form = document.getElementById('categoriaForm');
    if (!form) {
        console.warn('⚠️ Formulario categoriaForm no encontrado (modo VIEW)');
        return;
    }
    
    console.log('✅ Formulario encontrado, configurando submit handler');
    
    // ⭐ Variables para detectar cambios en el formulario
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
        console.log('📸 Datos originales del formulario capturados');
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
                    console.log(`🔄 Campo cambiado: ${key}`);
                    hasChanges = true;
                }
            } else {
                if (originalFormData[key] !== currentValue) {
                    console.log(`🔄 Campo cambiado: ${key} (de "${originalFormData[key]}" a "${currentValue}")`);
                    hasChanges = true;
                }
            }
        }
        
        // Verificar si se eliminaron campos
        for (let key in originalFormData) {
            if (!currentFormData.has(key) && key !== 'imagen_categoria') {
                console.log(`🔄 Campo eliminado: ${key}`);
                hasChanges = true;
            }
        }
        
        return hasChanges;
    }
    
    // ⭐ Capturar datos originales después de cargar el formulario
    setTimeout(() => captureOriginalFormData(form), 500);
    
    form.addEventListener('submit', async function(ev) {
        ev.preventDefault();
        ev.stopPropagation();
        
        console.log('📤 === SUBMIT CATEGORÍA INICIADO ===');
        
        // ⭐ VALIDAR SI HUBO CAMBIOS
        if (!hasFormChanged(form)) {
            console.log('ℹ️ No se detectaron cambios en el formulario');
            
            // Mostrar notificación
            if (typeof window.showNotification === 'function') {
                window.showNotification('No se realizaron cambios', 'info');
            }
            
            // Cerrar modal
            if (typeof window.closeCategoriaModal === 'function') {
                window.closeCategoriaModal();
            }
            
            return;
        }
        
        console.log('✅ Se detectaron cambios, procediendo a guardar...');
        
        // Botón submit
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalHTML = submitBtn ? submitBtn.innerHTML : null;
        
        if (submitBtn) {
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
            submitBtn.disabled = true;
            submitBtn.style.pointerEvents = 'none';
        }
        
        try {
            const formData = new FormData(form);
            const isEdit = formData.get('action') === 'update';
            const categoriaId = formData.get('id_categoria');
            
            // DEBUG: Mostrar FormData
            console.log('📋 FormData:');
            for (let [key, value] of formData.entries()) {
                console.log(`  ${key}:`, value);
            }
            
            const controllerUrl = '/fashion-master/app/controllers/categoriaController.php';
            
            const resp = await fetch(controllerUrl, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            
            if (!resp.ok) throw new Error('HTTP ' + resp.status + ' ' + resp.statusText);
            const text = await resp.text();
            
            let data;
            try { data = JSON.parse(text); } catch (err) { throw new Error('Respuesta no JSON: ' + err.message); }
            
            console.log('📥 Response:', data);
            
            if (data.success) {
                console.log('✅ Categoría guardada');
                
                // ⭐ GUARDAR TAB ACTIVO EN LOCALSTORAGE
                if (window.localStorage) {
                    window.localStorage.setItem('admin_active_tab', 'categorias');
                    console.log('💾 Tab "categorias" guardado en localStorage');
                }
                
                // Mostrar notificación de éxito
                if (typeof window.showNotification === 'function') {
                    window.showNotification(
                        isEdit ? 'Categoría actualizada correctamente' : 'Categoría creada correctamente',
                        'success'
                    );
                }
                
                // Actualizar tabla
                if (data.data) {
                    if (isEdit && categoriaId) {
                        console.log('🔄 Actualizando categoría...');
                        await window.updateSingleCategoria(categoriaId, data.data);
                    } else {
                        console.log('🔄 Recargando tabla...');
                        window.loadCategoriasData();
                    }
                }
                
                // ⭐ CERRAR MODAL
                window.closeCategoriaModal();
                
            } else {
                // Mostrar error
                const errorMsg = data.error || 'Error al guardar';
                console.error('❌ Error:', errorMsg);
                
                // ⭐ MOSTRAR NOTIFICACIÓN DE ERROR
                if (typeof window.showNotification === 'function') {
                    window.showNotification(errorMsg, 'error');
                }
                
                const errorContainer = document.getElementById('categoriaFormError');
                const errorMessage = document.getElementById('categoriaFormErrorMessage');
                
                if (errorContainer && errorMessage) {
                    errorMessage.textContent = errorMsg;
                    errorContainer.style.display = 'block';
                } else {
                    alert(errorMsg);
                }
            }
            
        } catch (error) {
            console.error('❌ Error:', error);
            alert('Error: ' + error.message);
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.style.pointerEvents = '';
                if (originalHTML !== null) submitBtn.innerHTML = originalHTML;
            }
        }
        
        return false;
    });
    
    console.log('✅ Submit handler configurado correctamente');
    
})();
</script>
