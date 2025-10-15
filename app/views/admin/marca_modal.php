<?php
// Modal para marcas - Vista PHP
// Incluir la conexión para obtener marcas y marcas
require_once __DIR__ . '/../../../config/conexion.php';

// Obtener marcas para el select
try {
    $stmt = $conn->prepare("SELECT id_marca as id, nombre_marca as nombre FROM marca WHERE status_marca = 1 ORDER BY nombre_marca");
    $stmt->execute();
    $marcas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $marcas = [];
}

// Obtener marcas para el select  
try {
    $stmt = $conn->prepare("SELECT id_marca, nombre_marca FROM marca WHERE status_marca = 1 ORDER BY nombre_marca");
    $stmt->execute();
    $marcas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $marcas = [];
}

// Si es edición o vista, obtener datos de la marca
$marca = null;
$action = isset($_GET['action']) ? $_GET['action'] : 'create';
$isView = $action === 'view' && isset($_GET['id']);
$isEdit = $action === 'edit' && isset($_GET['id']);
$isCreate = $action === 'create';

if (($isEdit || $isView) && isset($_GET['id'])) {
    try {
        $stmt = $conn->prepare("SELECT * FROM marca WHERE id_marca = ?");
        $stmt->execute([$_GET['id']]);
        $marca = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $marca = null;
    }
}

$hasData = $marca !== null;
$modalTitle = $isView ? 'Ver Marca' : ($isEdit ? 'Editar Marca' : 'Nueva Marca');
$iconClass = $isView ? 'eye' : ($isEdit ? 'edit' : 'plus');
?>

<?php if ($isView && $hasData): ?>

<!-- VIEW: Modal Ver Marca - Diseño Profesional Completo -->
<div id="marca-view-modal" class="marca-view-modal" style="opacity: 0; visibility: hidden; pointer-events: none;">
    <!-- IMPORTANTE: ID único para evitar conflictos CSS -->
    <!-- Overlay de fondo - SIN onclick para evitar duplicación (admin.php ya tiene el listener) -->
    <div class="marca-view-modal__overlay"></div>
    
    <!-- Contenedor del modal -->
    <div class="marca-view-modal__container">
        <!-- HEADER -->
        <div class="marca-view-modal__header">
            <div class="marca-view-modal__header-content">
                <h2 class="marca-view-modal__title">
                    <span class="marca-view-modal__title-icon">
                        <i class="fas fa-eye"></i>
                    </span>
                    Ver Marca
                </h2>
                
                <div class="marca-view-modal__badge">
                    <i class="fas fa-lock"></i>
                    Solo Lectura
                </div>
            </div>
            
            <button type="button" class="marca-view-modal__close" onclick="closeMarcaModal()" aria-label="Cerrar">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- BODY -->
        <div class="marca-view-modal__body">
            <div class="marca-view-modal__content">
                <!-- PANEL IZQUIERDO: IMAGEN -->
                <div class="marca-view-modal__image-section">
                    <div class="marca-view-modal__image-container">
                        <?php
                        $imagenSrc = '';
                        if (!empty($marca['url_imagen_marca'])) {
                            $imagenSrc = $marca['url_imagen_marca'];
                        } elseif (!empty($marca['imagen_marca'])) {
                            $imagenSrc = 'public/assets/img/products/' . $marca['imagen_marca'];
                        }
                        ?>
                        
                        <?php if ($imagenSrc): ?>
                            <img src="<?= htmlspecialchars($imagenSrc) ?>" 
                                 alt="<?= htmlspecialchars($marca['nombre_marca']) ?>" 
                                 class="marca-view-modal__image"
                                 onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'marca-view-modal__image-placeholder\'><i class=\'fas fa-image\'></i><span>Imagen no disponible</span></div>'">
                        <?php else: ?>
                            <div class="marca-view-modal__image-placeholder">
                                <i class="fas fa-image"></i>
                                <span>Sin imagen</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="marca-view-modal__image-name">
                        <?= htmlspecialchars($marca['nombre_marca']) ?>
                    </div>
                </div>

                <!-- PANEL DERECHO: INFORMACIÓN -->
                <div class="marca-view-modal__info-section">
                    <!-- INFORMACIÓN BÁSICA -->
                    <div class="marca-view-modal__card">
                        <div class="marca-view-modal__card-header">
                            <div class="marca-view-modal__card-icon">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <h3 class="marca-view-modal__card-title">Información Básica</h3>
                        </div>
                        
                        <div class="marca-view-modal__grid">
                            <div class="marca-view-modal__field">
                                <label class="marca-view-modal__field-label">Nombre</label>
                                <div class="marca-view-modal__field-value">
                                    <?= htmlspecialchars($marca['nombre_marca']) ?>
                                </div>
                            </div>
                            
                            <div class="marca-view-modal__field">
                                <label class="marca-view-modal__field-label">Código</label>
                                <div class="marca-view-modal__field-value marca-view-modal__field-value--code">
                                    <?= htmlspecialchars($marca['codigo_marca'] ?: 'N/A') ?>
                                </div>
                            </div>
                            
                            <div class="marca-view-modal__field">
                                <label class="marca-view-modal__field-label">Estado</label>
                                <div class="marca-view-modal__field-value">
                                    <?php 
                                    $isActive = isset($marca['estado_marca']) && $marca['estado_marca'] === 'activo';
                                    ?>
                                    <span class="marca-view-modal__status-badge marca-view-modal__status-badge--<?= $isActive ? 'active' : 'inactive' ?>">
                                        <i class="fas fa-<?= $isActive ? 'check-circle' : 'times-circle' ?>"></i>
                                        <?= $isActive ? 'Activo' : 'Inactivo' ?>
                                    </span>
                                </div>
                            </div>
                            
                            <?php if (!empty($marca['descripcion_marca'])): ?>
                            <div class="marca-view-modal__description">
                                <label class="marca-view-modal__field-label">Descripción</label>
                                <p class="marca-view-modal__description-text">
                                    <?= nl2br(htmlspecialchars($marca['descripcion_marca'])) ?>
                                </p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- INFORMACIÓN ADICIONAL -->
                    <div class="marca-view-modal__card">
                        <div class="marca-view-modal__card-header">
                            <div class="marca-view-modal__card-icon">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <h3 class="marca-view-modal__card-title">Información Adicional</h3>
                        </div>
                        
                        <div class="marca-view-modal__grid">
                            <div class="marca-view-modal__field">
                                <label class="marca-view-modal__field-label">Fecha de Registro</label>
                                <div class="marca-view-modal__field-value">
                                    <?php
                                    $fecha = $marca['fecha_registro_marca'] ?? $marca['fecha_creacion_marca'] ?? null;
                                    if ($fecha) {
                                        $dt = new DateTime($fecha);
                                        echo $dt->format('d/m/Y H:i');
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </div>
                            </div>
                            
                            <?php if (isset($marca['fecha_actualizacion_marca']) && !empty($marca['fecha_actualizacion_marca'])): ?>
                            <div class="marca-view-modal__field">
                                <label class="marca-view-modal__field-label">Última Actualización</label>
                                <div class="marca-view-modal__field-value">
                                    <?php
                                    $dt = new DateTime($marca['fecha_actualizacion_marca']);
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
        <div class="marca-view-modal__footer">
            <button type="button" class="marca-view-modal__btn marca-view-modal__btn--secondary" onclick="closeMarcaModal()">
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
    
    console.log('[marca VIEW] Inicializando modal de vista...');
    
    // Animar entrada del modal
    const modal = document.querySelector('.marca-view-modal');
    if (modal) {
        requestAnimationFrame(() => {
            modal.classList.add('show');
        });
    }
    
    // Cerrar con ESC
    const handleEsc = (e) => {
        if (e.key === 'Escape') {
            closeMarcaModal();
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
        <button type="button" class="modal-close" onclick="closeMarcaModal()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <form id="marcaForm" enctype="multipart/form-data">
        <input type="hidden" name="action" value="<?= $isEdit ? 'update' : 'create' ?>">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id_marca" value="<?= $marca['id_marca'] ?>">
            <input type="hidden" name="status_marca" value="<?= $marca['status_marca'] ?>">
        <?php else: ?>
            <input type="hidden" name="status_marca" value="1">
        <?php endif; ?>
        
        <!-- Contenedor de errores -->
        <div id="marcaFormError" style="display: none; margin: 1rem 1.5rem; padding: 1rem; background: #fee; border-left: 4px solid #f44336; border-radius: 4px;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-exclamation-circle" style="color: #f44336; font-size: 1.2rem;"></i>
                <span id="marcaFormErrorMessage" style="color: #c62828; font-weight: 500;"></span>
            </div>
        </div>
        
        <div class="modal-body">
            <!-- Información básica -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-info-circle"></i>
                    Información de la Marca
                </h3>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nombre_marca">
                            <i class="fas fa-tag"></i>
                            Nombre de la Marca *
                        </label>
                        <input type="text" 
                               id="nombre_marca" 
                               name="nombre_marca" 
                               value="<?= $hasData ? htmlspecialchars($marca['nombre_marca']) : '' ?>"
                               required 
                               maxlength="100"
                               placeholder="Ej: Ropa Deportiva, Zapatos, Accesorios...">
                    </div>
                    
                    <div class="form-group">
                        <label for="codigo_marca">
                            <i class="fas fa-barcode"></i>
                            Código de Marca
                        </label>
                        <input type="text" 
                               id="codigo_marca" 
                               name="codigo_marca" 
                               value="<?= $hasData ? htmlspecialchars($marca['codigo_marca']) : '' ?>"
                               maxlength="50"
                               placeholder="Ej: CAT-001, ROPA-DEP...">
                        <small class="form-text">Código único opcional para identificar la marca</small>
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
                    <textarea id="descripcion_marca" 
                              name="descripcion_marca" 
                              rows="4" 
                              maxlength="500"
                              placeholder="Describe las características principales de la marca..."><?= $hasData ? htmlspecialchars($marca['descripcion_marca']) : '' ?></textarea>
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
                        <label for="estado_marca">
                            <i class="fas fa-power-off"></i>
                            Estado de la Marca *
                        </label>
                        <select id="estado_marca" 
                                name="estado_marca" 
                                class="form-control"
                                required>
                            <option value="activo" <?= ($hasData && $marca['estado_marca'] === 'activo') ? 'selected' : '' ?>>Activo</option>
                            <option value="inactivo" <?= ($hasData && $marca['estado_marca'] === 'inactivo') ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Imagen de la Marca -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-image"></i>
                    Imagen de la Marca
                </h3>
                
                <div class="file-upload-container">
                    <?php if ($hasData && !empty($marca['url_imagen_marca'])): ?>
                        <div class="current-image-section">
                            <div class="current-image-display">
                                <img src="<?= htmlspecialchars($marca['url_imagen_marca']) ?>" 
                                     alt="imagen actual de la Marca" 
                                     class="current-product-image" 
                                     onerror="this.src='public/assets/img/default-category.jpg'; this.onerror=null;">
                            </div>
                            <div class="change-image-section">
                                <button type="button" class="btn-change-image" onclick="document.getElementById('imagen_marca').click()">
                                    <i class="fas fa-camera"></i> Cambiar imagen
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="file-upload-area" id="uploadArea" onclick="document.getElementById('imagen_marca').click()">
                            <i class="fas fa-cloud-upload-alt upload-icon"></i>
                            <p class="upload-text"><strong>Haz clic aquí para seleccionar una imagen</strong><br>o arrastra y suelta tu archivo</p>
                            <small class="upload-hint">Formatos: JPG, PNG, GIF (máx. 5MB)</small>
                        </div>
                    <?php endif; ?>

                    <input type="file" id="imagen_marca" name="imagen_marca" accept="image/*" style="display:none;">
                </div>
            </div>
        </div>
        
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeMarcaModal()">
                <i class="fas fa-times"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> <?= $isEdit ? 'Actualizar' : 'Guardar' ?> <span class="btn-text-mobile-hide">marca</span>
            </button>
        </div>
    </form>
</div>

<!-- SCRIPTS para el formulario -->
<script>
(function() {
    'use strict';
    
    // ? FUNCIÓN PRINCIPAL PARA GUARDAR (EVITA REFRESH DE PÁGINA)
    async function guardarmarca() {
        console.log('?? Iniciando guardado de marca (SIN REFRESH)');
        
        const form = document.getElementById('marcaForm');
        if (!form) {
            console.error('? Formulario no encontrado');
            return;
        }
        
        // ? GUARDAR TAB ACTIVO ANTES DE CUALQUIER OPERACIÓN
        console.log('?? Forzando tab "marcas" en localStorage');
        localStorage.setItem('admin_active_tab', 'marcas');
        
        // Ocultar errores previos
        const errorContainer = document.getElementById('marcaFormError');
        const errorMessage = document.getElementById('marcaFormErrorMessage');
        if (errorContainer) errorContainer.style.display = 'none';
        
        const formData = new FormData(form);
        const submitBtn = document.getElementById('guardarMarcaBtn');
        const originalText = submitBtn ? submitBtn.innerHTML : '';
        const isEdit = formData.get('action') === 'update';
        const marcaId = formData.get('id_marca');
        
        // ========== DEBUG: Mostrar TODO el FormData ==========
        console.log('? === CONTENIDO COMPLETO DE FORMDATA ===');
        for (let [key, value] of formData.entries()) {
            console.log(`  ${key}:`, value);
        }
        console.log('=========================================');
        
        console.log('??? Enviando datos:', {
            action: formData.get('action'),
            id: marcaId,
            codigo: formData.get('codigo_marca'),
            nombre: formData.get('nombre_marca'),
            descripcion: formData.get('descripcion_marca'),
            estado: formData.get('estado_marca')
        });
        
        // Deshabilitar botón y mostrar loading
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
        }
        
        try {
            // URL del controlador
            const controllerUrl = '/fashion-master/app/controllers/MarcaController.php';
            
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
            
            console.log('?? RESPUESTA COMPLETA:', result);
            console.log('?? DATOS RECIBIDOS:', result.data);
            if (result.data) {
                console.log('?? CÓDIGO EN RESPUESTA:', result.data.codigo_marca);
            }
            
            if (result.success) {
                console.log('? marca guardada exitosamente:', result);
                
                // ? RE-CONFIRMAR TAB ACTIVO ANTES DE CERRAR
                localStorage.setItem('admin_active_tab', 'marcas');
                console.log('?? Tab "marcas" confirmado en localStorage');
                
                // Cerrar modal
                if (typeof closeMarcaModal === 'function') {
                    closeMarcaModal();
                }
                
                // Actualizar tabla en tiempo real (SIN REFRESH)
                if (isEdit && marcaId && result.data) {
                    // EDITAR: Actualizar fila existente
                    console.log('?? Llamando updateSingleMarca con ID:', marcaId);
                    if (typeof updateSingleMarca === 'function') {
                        await updateSingleMarca(marcaId, result.data);
                    }
                } else if (!isEdit && result.data) {
                    // CREAR: Recargar tabla para mostrar Nueva Marca
                    console.log('?? Recargando datos de tabla (AJAX)');
                    if (typeof loadMarcasData === 'function') {
                        loadMarcasData();
                    }
                }
                
                console.log('? Operación completada - permaneciendo en marcas (SIN REFRESH)');
            } else {
                // Mostrar error en el modal
                const errorMsg = result.error || 'Error al guardar la marca';
                console.error('? Error del servidor:', errorMsg);
                
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
            console.error('? Error de red:', error);
            
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
    const guardarBtn = document.getElementById('guardarMarcaBtn');
    if (guardarBtn) {
        guardarBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('??? Click en botón Guardar (prevención de refresh activada)');
            guardarmarca();
        });
    }
    
    // PREVENCIÓN ADICIONAL: Capturar submit del form (por si acaso)
    const form = document.getElementById('marcaForm');
    if (form) {
        form.addEventListener('submit', async function(ev) {
            ev.preventDefault();
            ev.stopPropagation();
            
            console.log('?? === SUBMIT marca INICIADO ===');
            
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
                const marcaId = formData.get('id_marca');
                
                // DEBUG: Mostrar FormData
                console.log('?? FormData:');
                for (let [key, value] of formData.entries()) {
                    console.log(`  ${key}:`, value);
                }
                
                const controllerUrl = '/fashion-master/app/controllers/MarcaController.php';
                
                const resp = await fetch(controllerUrl, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                
                if (!resp.ok) throw new Error('HTTP ' + resp.status + ' ' + resp.statusText);
                const text = await resp.text();
                
                let data;
                try { data = JSON.parse(text); } catch (err) { throw new Error('Respuesta no JSON: ' + err.message); }
                
                console.log('?? Response:', data);
                
                if (data.success) {
                    console.log('? marca guardada');
                    
                    // Actualizar tabla en PARENT window
                    if (data.data) {
                        if (isEdit && marcaId) {
                            console.log('?? Actualizando en parent...');
                            if (window.parent && typeof window.parent.updateSingleMarca === 'function') {
                                await window.parent.updateSingleMarca(marcaId, data.data);
                            }
                        } else {
                            console.log('?? Recargando tabla en parent...');
                            if (window.parent && typeof window.parent.loadMarcasData === 'function') {
                                window.parent.loadMarcasData();
                            }
                        }
                    }
                    
                    // ? CERRAR MODAL (PARENT)
                    if (typeof window.closeMarcaModal === 'function') {
                        window.closeMarcaModal();
                    } else if (window.parent && typeof window.parent.closeMarcaModal === 'function') {
                        window.parent.closeMarcaModal();
                    } else {
                        console.error('? closeMarcaModal no disponible');
                    }
                    
                } else {
                    // Mostrar error
                    const errorMsg = data.error || 'Error al guardar';
                    console.error('? Error:', errorMsg);
                    
                    const errorContainer = document.getElementById('marcaFormError');
                    const errorMessage = document.getElementById('marcaFormErrorMessage');
                    
                    if (errorContainer && errorMessage) {
                        errorMessage.textContent = errorMsg;
                        errorContainer.style.display = 'block';
                    } else {
                        alert(errorMsg);
                    }
                }
                
            } catch (error) {
                console.error('? Error:', error);
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
    
    // ? PREVENIR ENTER EN INPUTS (NO SUBMIT)
    const formInputs = form.querySelectorAll('input, textarea');
    formInputs.forEach(input => {
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && input.type !== 'textarea') {
                e.preventDefault();
                console.log('?? Enter bloqueado en input (prevención de submit)');
                return false;
            }
        });
    });
    
    // Validación y preview de imagen
    const imgInput = document.getElementById('imagen_marca');
    if (imgInput) {
        imgInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validar tamaño (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('La imagen es muy grande. Máximo 5MB.');
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
        const imgInput = document.getElementById('imagen_marca');
        const uploadArea = document.getElementById('uploadArea');
        
        if (imgInput) imgInput.value = '';
        if (uploadArea) {
            uploadArea.innerHTML = `
                <i class="fas fa-cloud-upload-alt upload-icon"></i>
                <p class="upload-text"><strong>Haz clic aquí para seleccionar una imagen</strong><br>o arrastra y suelta tu archivo</p>
                <small class="upload-hint">Formatos: JPG, PNG, GIF (máx. 5MB)</small>
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
                const imgInput = document.getElementById('imagen_marca');
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
    
    console.log('?? Script marca_modal.php cargado');
    
    // ========================================
    // ? PREVISUALIZACIÓN Y VALIDACIÓN DE IMAGEN
    // ========================================
    const imgInput = document.getElementById('imagen_marca');
    if (imgInput) {
        console.log('? Input de imagen encontrado, configurando preview');
        
        imgInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                console.log('?? Archivo seleccionado:', file.name, file.size, 'bytes');
                
                // Validar tamaño (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('La imagen es muy grande. Máximo 5MB.');
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
                    console.log('? Imagen cargada, mostrando preview');
                    
                    // Buscar elementos existentes
                    const uploadArea = document.getElementById('uploadArea');
                    const currentImageDisplay = document.querySelector('.current-image-display');
                    const fileUploadContainer = document.querySelector('.file-upload-container');
                    
                    console.log('?? uploadArea:', uploadArea);
                    console.log('?? currentImageDisplay:', currentImageDisplay);
                    
                    if (currentImageDisplay) {
                        // MODO EDITAR: Ya existe una imagen, actualizarla
                        const img = currentImageDisplay.querySelector('img');
                        if (img) {
                            img.src = e.target.result;
                            console.log('? Preview actualizado en imagen existente (EDITAR)');
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
                        console.log('? Preview creado en uploadArea (CREAR)');
                    } else if (fileUploadContainer) {
                        // FALLBACK: Crear preview cuadrado en el contenedor
                        fileUploadContainer.innerHTML = `
                            <div class="image-preview-container" style="text-align: center;">
                                <img src="${e.target.result}" alt="Preview" style="width: 150px; height: 150px; object-fit: contain; border-radius: 8px; margin-bottom: 10px; border: 2px solid #e0e0e0; background: rgba(0, 0, 0, 0.05);">
                                <br>
                                <button type="button" class="btn btn-secondary btn-sm" onclick="removeImagePreview()">
                                    <i class="fas fa-times"></i> Quitar imagen
                                </button>
                                <input type="file" id="imagen_marca" name="imagen_marca" accept="image/*" style="display:none;">
                            </div>
                        `;
                        console.log('? Preview creado en fileUploadContainer (FALLBACK)');
                        
                        // Re-asignar el input
                        const newInput = document.getElementById('imagen_marca');
                        if (newInput) {
                            newInput.addEventListener('change', arguments.callee);
                        }
                    } else {
                        console.error('? No se encontró ningún contenedor para mostrar el preview');
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    } else {
        console.warn('?? Input de imagen no encontrado');
    }
    
    // Función para remover preview
    window.removeImagePreview = function() {
        console.log('??? Removiendo preview de imagen');
        const imgInput = document.getElementById('imagen_marca');
        const uploadArea = document.getElementById('uploadArea');
        const fileUploadContainer = document.querySelector('.file-upload-container');
        
        if (imgInput) {
            imgInput.value = '';
            console.log('? Input limpiado');
        }
        
        if (uploadArea) {
            // Restaurar uploadArea original
            uploadArea.innerHTML = `
                <i class="fas fa-cloud-upload-alt upload-icon"></i>
                <p class="upload-text"><strong>Haz clic aquí para seleccionar una imagen</strong><br>o arrastra y suelta tu archivo</p>
                <small class="upload-hint">Formatos: JPG, PNG, GIF (máx. 5MB)</small>
            `;
            uploadArea.onclick = function() { document.getElementById('imagen_marca').click(); };
            console.log('? UploadArea restaurado');
        } else if (fileUploadContainer) {
            // Restaurar desde contenedor
            fileUploadContainer.innerHTML = `
                <div class="file-upload-area" id="uploadArea" onclick="document.getElementById('imagen_marca').click()">
                    <i class="fas fa-cloud-upload-alt upload-icon"></i>
                    <p class="upload-text"><strong>Haz clic aquí para seleccionar una imagen</strong><br>o arrastra y suelta tu archivo</p>
                    <small class="upload-hint">Formatos: JPG, PNG, GIF (máx. 5MB)</small>
                </div>
                <input type="file" id="imagen_marca" name="imagen_marca" accept="image/*" style="display:none;">
            `;
            console.log('? FileUploadContainer restaurado');
            
            // Re-configurar el nuevo input
            const newInput = document.getElementById('imagen_marca');
            if (newInput && window.setupImagePreview) {
                window.setupImagePreview();
            }
        }
    };
    
    // ========================================
    // ? DRAG AND DROP PARA IMÁGENES
    // ========================================
    const uploadArea = document.getElementById('uploadArea');
    if (uploadArea) {
        console.log('? Upload area encontrada, configurando drag & drop');
        
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
                console.log('?? Archivo arrastrado:', files[0].name);
                const imgInput = document.getElementById('imagen_marca');
                if (imgInput) {
                    imgInput.files = files;
                    imgInput.dispatchEvent(new Event('change'));
                }
            }
        });
    } else {
        console.warn('?? Upload area no encontrada');
    }
    
    // ========================================
    // ? CAPTURAR SUBMIT DEL FORM
    // ========================================
    const form = document.getElementById('marcaForm');
    if (!form) {
        console.warn('?? Formulario marcaForm no encontrado (modo VIEW)');
        return;
    }
    
    console.log('? Formulario encontrado, configurando submit handler');
    
    // ? Variables para detectar cambios en el formulario
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
        console.log('?? Datos originales del formulario capturados');
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
                    console.log(`?? Campo cambiado: ${key}`);
                    hasChanges = true;
                }
            } else {
                if (originalFormData[key] !== currentValue) {
                    console.log(`?? Campo cambiado: ${key} (de "${originalFormData[key]}" a "${currentValue}")`);
                    hasChanges = true;
                }
            }
        }
        
        // Verificar si se eliminaron campos
        for (let key in originalFormData) {
            if (!currentFormData.has(key) && key !== 'imagen_marca') {
                console.log(`?? Campo eliminado: ${key}`);
                hasChanges = true;
            }
        }
        
        return hasChanges;
    }
    
    // ? Capturar datos originales después de cargar el formulario
    setTimeout(() => captureOriginalFormData(form), 500);
    
    form.addEventListener('submit', async function(ev) {
        ev.preventDefault();
        ev.stopPropagation();
        
        console.log('?? === SUBMIT marca INICIADO ===');
        
        // ? VALIDAR SI HUBO CAMBIOS
        if (!hasFormChanged(form)) {
            console.log('?? No se detectaron cambios en el formulario');
            
            // Mostrar notificación
            if (typeof window.showNotification === 'function') {
                window.showNotification('No se realizaron cambios', 'info');
            }
            
            // Cerrar modal
            if (typeof window.closeMarcaModal === 'function') {
                window.closeMarcaModal();
            }
            
            return;
        }
        
        console.log('? Se detectaron cambios, procediendo a guardar...');
        
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
            const marcaId = formData.get('id_marca');
            
            // DEBUG: Mostrar FormData
            console.log('?? FormData:');
            for (let [key, value] of formData.entries()) {
                console.log(`  ${key}:`, value);
            }
            
            const controllerUrl = '/fashion-master/app/controllers/MarcaController.php';
            
            const resp = await fetch(controllerUrl, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            
            if (!resp.ok) throw new Error('HTTP ' + resp.status + ' ' + resp.statusText);
            const text = await resp.text();
            
            let data;
            try { data = JSON.parse(text); } catch (err) { throw new Error('Respuesta no JSON: ' + err.message); }
            
            console.log('?? Response:', data);
            
            if (data.success) {
                console.log('? marca guardada');
                
                // ? GUARDAR TAB ACTIVO EN LOCALSTORAGE
                if (window.localStorage) {
                    window.localStorage.setItem('admin_active_tab', 'marcas');
                    console.log('?? Tab "marcas" guardado en localStorage');
                }
                
                // Mostrar notificación de éxito
                if (typeof window.showNotification === 'function') {
                    window.showNotification(
                        isEdit ? 'marca actualizada correctamente' : 'marca creada correctamente',
                        'success'
                    );
                }
                
                // Actualizar tabla
                if (data.data) {
                    if (isEdit && marcaId) {
                        console.log('?? Actualizando marca...');
                        if (typeof window.updateSingleMarca === 'function') {
                            await window.updateSingleMarca(marcaId, data.data);
                        }
                    } else {
                        console.log('?? Recargando tabla...');
                        if (typeof window.loadMarcasData === 'function') {
                            window.loadMarcasData();
                        }
                    }
                }
                
                // ? CERRAR MODAL
                if (typeof window.closeMarcaModal === 'function') {
                    window.closeMarcaModal();
                } else {
                    console.error('? closeMarcaModal no disponible');
                }
                
            } else {
                // Mostrar error
                const errorMsg = data.error || 'Error al guardar';
                console.error('? Error:', errorMsg);
                
                // ? MOSTRAR NOTIFICACIÓN DE ERROR
                if (typeof window.showNotification === 'function') {
                    window.showNotification(errorMsg, 'error');
                }
                
                const errorContainer = document.getElementById('marcaFormError');
                const errorMessage = document.getElementById('marcaFormErrorMessage');
                
                if (errorContainer && errorMessage) {
                    errorMessage.textContent = errorMsg;
                    errorContainer.style.display = 'block';
                } else {
                    alert(errorMsg);
                }
            }
            
        } catch (error) {
            console.error('? Error:', error);
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
    
    console.log('? Submit handler configurado correctamente');
    
})();
</script>
