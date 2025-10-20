<!-- Modal para CREAR/EDITAR CATEGORÍA -->
<div id="category-modal" class="modal-overlay" style="display: none;">
    <div class="modal-container category-modal-container">
        <!-- Header del Modal -->
        <div class="modal-header">
            <div class="modal-title-section">
                <div class="modal-icon">
                    <i class="fas fa-tags"></i>
                </div>
                <div>
                    <h2 id="category-modal-title" class="modal-title">Nueva Categoría</h2>
                    <p class="modal-subtitle">Complete los datos de la categoría</p>
                </div>
            </div>
            <button class="modal-close-btn" onclick="closeCategoryModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Formulario -->
        <form id="category-form" enctype="multipart/form-data">
            <input type="hidden" id="category-id" name="id_categoria">
            <input type="hidden" id="category-action" name="action" value="create">

            <div class="modal-body">
                <!-- Imagen de Categoría -->
                <div class="form-section">
                    <label class="section-title">
                        <i class="fas fa-image"></i>
                        Imagen de la Categoría
                    </label>
                    <div class="image-upload-container">
                        <div class="image-preview" id="category-image-preview">
                            <img id="category-preview-img" src="/fashion-master/public/assets/img/default-category.png" alt="Vista previa">
                            <div class="image-overlay">
                                <i class="fas fa-camera"></i>
                                <span>Clic para cambiar</span>
                            </div>
                        </div>
                        <input type="file" id="categoria-imagen-input" name="imagen_categoria" 
                               accept="image/*" onchange="previewCategoryImage(this)" style="display: none;">
                        <button type="button" class="btn-upload" onclick="document.getElementById('categoria-imagen-input').click()">
                            <i class="fas fa-upload"></i>
                            Seleccionar Imagen
                        </button>
                        <p class="upload-hint">
                            <i class="fas fa-info-circle"></i>
                            Formatos: JPG, PNG, GIF. Tamaño máx: 5MB
                        </p>
                    </div>
                </div>

                <!-- Información Básica -->
                <div class="form-section">
                    <label class="section-title">
                        <i class="fas fa-info-circle"></i>
                        Información Básica
                    </label>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="category-codigo">
                                Código
                                <span class="field-badge auto-badge">
                                    <i class="fas fa-magic"></i> Auto
                                </span>
                            </label>
                            <input type="text" id="category-codigo" name="codigo_categoria" 
                                   class="form-control" placeholder="Se genera automáticamente" readonly>
                            <small class="form-hint">El código se genera automáticamente (CAT-001, CAT-002...)</small>
                        </div>

                        <div class="form-group">
                            <label for="category-nombre">
                                Nombre de la Categoría
                                <span class="field-required">*</span>
                            </label>
                            <input type="text" id="category-nombre" name="nombre_categoria" 
                                   class="form-control" placeholder="Ej: Camisas, Pantalones, Zapatos..." required>
                            <small class="form-hint">Nombre descriptivo y único para la categoría</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="category-descripcion">
                            Descripción
                        </label>
                        <textarea id="category-descripcion" name="descripcion_categoria" 
                                  class="form-control" rows="4" 
                                  placeholder="Descripción detallada de la categoría y sus productos..."></textarea>
                        <small class="form-hint">Ayuda a los clientes a entender qué productos encontrarán aquí</small>
                    </div>
                </div>

                <!-- Estado de la Categoría -->
                <div class="form-section">
                    <label class="section-title">
                        <i class="fas fa-toggle-on"></i>
                        Estado de la Categoría
                    </label>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="category-estado">Estado de Visibilidad</label>
                            <select id="category-estado" name="estado_categoria" class="form-control">
                                <option value="activo">Activo (Visible en la tienda)</option>
                                <option value="inactivo">Inactivo (Oculto)</option>
                            </select>
                            <small class="form-hint">
                                <i class="fas fa-lightbulb"></i>
                                Las categorías inactivas no se mostrarán en la tienda
                            </small>
                        </div>

                        <div class="form-group">
                            <label>Estado del Sistema</label>
                            <div class="status-display">
                                <span class="badge-status badge-active">
                                    <i class="fas fa-check-circle"></i>
                                    Sistema: Activo
                                </span>
                            </div>
                            <small class="form-hint">
                                <i class="fas fa-info-circle"></i>
                                El estado del sistema se gestiona automáticamente
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer con Botones -->
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeCategoryModal()">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>
                <button type="submit" class="btn-primary" id="category-submit-btn">
                    <i class="fas fa-save"></i>
                    <span id="category-submit-text">Guardar Categoría</span>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
/* Modal Overlay */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(5px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10000;
    padding: 20px;
    animation: fadeIn 0.3s;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal-container {
    background: white;
    border-radius: 20px;
    max-width: 800px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
    animation: slideUp 0.3s;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Modal Header */
.modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    border-radius: 20px 20px 0 0;
}

.modal-title-section {
    display: flex;
    gap: 20px;
    align-items: center;
}

.modal-icon {
    background: rgba(255, 255, 255, 0.2);
    width: 60px;
    height: 60px;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
}

.modal-title {
    font-size: 24px;
    font-weight: 700;
    margin: 0;
}

.modal-subtitle {
    font-size: 14px;
    opacity: 0.9;
    margin: 5px 0 0;
}

.modal-close-btn {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    width: 40px;
    height: 40px;
    border-radius: 10px;
    color: white;
    cursor: pointer;
    font-size: 18px;
    transition: all 0.3s;
}

.modal-close-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: rotate(90deg);
}

/* Modal Body */
.modal-body {
    padding: 30px;
}

.form-section {
    margin-bottom: 30px;
    padding-bottom: 30px;
    border-bottom: 2px solid #f3f4f6;
}

.form-section:last-child {
    border-bottom: none;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 16px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 20px;
}

.section-title i {
    color: #667eea;
    font-size: 18px;
}

/* Image Upload */
.image-upload-container {
    text-align: center;
}

.image-preview {
    width: 200px;
    height: 200px;
    margin: 0 auto 20px;
    border-radius: 15px;
    overflow: hidden;
    position: relative;
    cursor: pointer;
    border: 3px solid #e5e7eb;
    transition: all 0.3s;
}

.image-preview:hover {
    border-color: #667eea;
    transform: scale(1.05);
}

.image-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: white;
    opacity: 0;
    transition: all 0.3s;
}

.image-preview:hover .image-overlay {
    opacity: 1;
}

.image-overlay i {
    font-size: 32px;
    margin-bottom: 10px;
}

.btn-upload {
    padding: 12px 30px;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s;
}

.btn-upload:hover {
    background: #5568d3;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
}

.upload-hint {
    margin-top: 15px;
    font-size: 13px;
    color: #6b7280;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

/* Form Grid */
.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-group label {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    display: flex;
    align-items: center;
    gap: 8px;
}

.field-required {
    color: #ef4444;
    font-size: 16px;
}

.field-badge {
    padding: 3px 8px;
    border-radius: 5px;
    font-size: 11px;
    font-weight: 600;
}

.auto-badge {
    background: #dbeafe;
    color: #1e40af;
}

.form-control {
    padding: 12px 15px;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    font-size: 14px;
    transition: all 0.3s;
    font-family: inherit;
}

.form-control:focus {
    border-color: #667eea;
    outline: none;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-control:read-only {
    background: #f9fafb;
    cursor: not-allowed;
}

textarea.form-control {
    resize: vertical;
    min-height: 100px;
}

.form-hint {
    font-size: 12px;
    color: #6b7280;
    display: flex;
    align-items: center;
    gap: 5px;
}

.form-hint i {
    color: #3b82f6;
}

.status-display {
    padding: 12px 15px;
    background: #f9fafb;
    border-radius: 10px;
    display: flex;
    align-items: center;
}

.badge-status {
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.badge-active {
    background: #d1fae5;
    color: #065f46;
}

.badge-inactive {
    background: #fee2e2;
    color: #991b1b;
}

/* Modal Footer */
.modal-footer {
    padding: 20px 30px;
    background: #f9fafb;
    border-radius: 0 0 20px 20px;
    display: flex;
    justify-content: flex-end;
    gap: 15px;
}

.modal-footer button {
    padding: 12px 30px;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
}

.btn-primary {
    background: #10b981;
    color: white;
}

.btn-primary:hover {
    background: #059669;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3);
}

.btn-secondary {
    background: #e5e7eb;
    color: #374151;
}

.btn-secondary:hover {
    background: #d1d5db;
}

/* Responsive */
@media (max-width: 768px) {
    .modal-container {
        max-width: 100%;
        max-height: 100vh;
        border-radius: 0;
    }

    .modal-header {
        border-radius: 0;
        padding: 20px;
    }

    .modal-icon {
        width: 50px;
        height: 50px;
        font-size: 24px;
    }

    .modal-title {
        font-size: 20px;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }

    .modal-footer {
        flex-direction: column;
    }

    .modal-footer button {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
// ===================================
//    GESTIÓN DEL MODAL DE CATEGORÍA
// ===================================

/**
 * Mostrar modal para CREAR categoría
 */
function showCreateCategoryModal() {
    document.getElementById('category-modal-title').textContent = 'Nueva Categoría';
    document.getElementById('category-action').value = 'create';
    document.getElementById('category-form').reset();
    document.getElementById('category-id').value = '';
    document.getElementById('category-codigo').value = 'Se generará automáticamente';
    document.getElementById('category-preview-img').src = '/fashion-master/public/assets/img/default-category.png';
    document.getElementById('category-submit-text').textContent = 'Crear Categoría';
    
    document.getElementById('category-modal').style.display = 'flex';
}

/**
 * Mostrar modal para EDITAR categoría
 */
function editCategory(categoryId) {
    // Obtener datos de la categoría
    fetch(`${CONFIG.apiUrl}?action=get&id=${categoryId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const cat = data.category;
                
                document.getElementById('category-modal-title').textContent = 'Editar Categoría';
                document.getElementById('category-action').value = 'update';
                document.getElementById('category-id').value = cat.id_categoria;
                document.getElementById('category-codigo').value = cat.codigo_categoria;
                document.getElementById('category-nombre').value = cat.nombre_categoria;
                document.getElementById('category-descripcion').value = cat.descripcion_categoria || '';
                document.getElementById('category-estado').value = cat.estado_categoria;
                document.getElementById('category-preview-img').src = cat.url_imagen_categoria || '/fashion-master/public/assets/img/default-category.png';
                document.getElementById('category-submit-text').textContent = 'Actualizar Categoría';
                
                document.getElementById('category-modal').style.display = 'flex';
            } else {
                showError(data.error || 'Error al cargar categoría');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Error de conexión');
        });
}

/**
 * Cerrar modal
 */
function closeCategoryModal() {
    document.getElementById('category-modal').style.display = 'none';
    document.getElementById('category-form').reset();
}

/**
 * Vista previa de imagen
 */
function previewCategoryImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('category-preview-img').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

/**
 * Enviar formulario
 */
document.addEventListener('DOMContentLoaded', function() {
    const categoryForm = document.getElementById('category-form');
    
    if (categoryForm) {
        categoryForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('category-submit-btn');
            const submitText = document.getElementById('category-submit-text');
            const originalText = submitText.textContent;
            
            // Deshabilitar botón
            submitBtn.disabled = true;
            submitText.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
            
            const formData = new FormData(this);
            
            fetch(CONFIG.apiUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess(data.message || 'Categoría guardada correctamente');
                    closeCategoryModal();
                    loadCategories(); // Recargar lista
                } else {
                    showError(data.error || 'Error al guardar categoría');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Error de conexión con el servidor');
            })
            .finally(() => {
                // Rehabilitar botón
                submitBtn.disabled = false;
                submitText.textContent = originalText;
            });
        });
    }
});

// Cerrar modal al hacer clic fuera
document.addEventListener('click', function(e) {
    const modal = document.getElementById('category-modal');
    if (modal && e.target === modal) {
        closeCategoryModal();
    }
});

// Cerrar modal con tecla ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('category-modal');
        if (modal && modal.style.display === 'flex') {
            closeCategoryModal();
        }
    }
});
</script>
