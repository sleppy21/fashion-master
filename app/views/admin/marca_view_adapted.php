<?php
/**
 * MODAL VER MARCA - ADAPTADO DE PRODUCT-VIEW-MODAL
 * Reutiliza las clases de product-view-modal.css
 */

// Este archivo es solo de referencia para el nuevo modal
// El modal real está en marca_modal.php líneas 45-460
?>

<?php if ($isView && $hasData): ?>

<!-- VIEW: Modal Ver Marca - Usando product-view-modal -->
<div class="product-view-modal marca-adapted" id="marcaViewModal">
    <div class="product-view-modal__overlay"></div>
    
    <div class="product-view-modal__container" style="max-width: 900px;">
        <!-- HEADER -->
        <div class="product-view-modal__header">
            <div class="product-view-modal__header-content">
                <h2 class="product-view-modal__title">
                    <span class="product-view-modal__icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                        <i class="fas fa-tag"></i>
                    </span>
                    Ver Marca
                </h2>
                
                <span class="product-view-modal__badge product-view-modal__badge--readonly" style="background: rgba(251, 191, 36, 0.1); border-color: rgba(251, 191, 36, 0.3); color: #fbbf24;">
                    <i class="fas fa-lock"></i>
                    Solo Lectura
                </span>
            </div>
            
            <button type="button" class="product-view-modal__close" onclick="closeMarcaModal()" aria-label="Cerrar">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- BODY -->
        <div class="product-view-modal__body">
            <div class="product-view-modal__content" style="grid-template-columns: 350px 1fr;">
                
                <!-- PANEL IZQUIERDO: IMAGEN -->
                <div class="product-view-modal__image-section">
                    <div class="product-view-modal__image-container" style="height: 350px; background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); padding: 20px; border-radius: 12px;">
                        <?php
                        $imagenSrc = '';
                        if (!empty($marca['url_imagen_marca']) && $marca['url_imagen_marca'] !== 'NULL') {
                            $imagenSrc = $marca['url_imagen_marca'];
                        } elseif (!empty($marca['imagen_marca']) && $marca['imagen_marca'] !== 'NULL') {
                            $imagenSrc = '/fashion-master/public/assets/img/brands/' . $marca['imagen_marca'];
                        } else {
                            $imagenSrc = '/fashion-master/public/assets/img/default-product.jpg';
                        }
                        ?>
                        
                        <img src="<?= htmlspecialchars($imagenSrc) ?>" 
                             alt="<?= htmlspecialchars($marca['nombre_marca']) ?>" 
                             class="product-view-modal__main-image"
                             ondblclick="showImageFullSize('<?= htmlspecialchars($imagenSrc) ?>', '<?= htmlspecialchars($marca['nombre_marca']) ?>')"
                             style="max-width: 100%; max-height: 310px; object-fit: contain; border-radius: 8px; cursor: zoom-in;"
                             onerror="this.src='/fashion-master/public/assets/img/default-product.jpg'; this.onerror=null;">
                    </div>
                    
                    <div class="product-view-modal__product-name" style="text-align: center; margin-top: 15px; font-size: 18px; font-weight: 600; color: #f1f5f9; background: rgba(148, 163, 184, 0.1); padding: 12px; border-radius: 8px;">
                        <?= htmlspecialchars($marca['nombre_marca']) ?>
                    </div>
                </div>

                <!-- PANEL DERECHO: INFORMACIÓN -->
                <div class="product-view-modal__details-section">
                    
                    <!-- INFORMACIÓN BÁSICA -->
                    <div class="product-view-modal__info-card">
                        <div class="product-view-modal__info-header">
                            <div class="product-view-modal__info-icon" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <h3 class="product-view-modal__info-title">Información Básica</h3>
                        </div>
                        
                        <div class="product-view-modal__info-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                            
                            <div class="product-view-modal__info-item">
                                <label class="product-view-modal__info-label">Nombre de la Marca</label>
                                <div class="product-view-modal__info-value">
                                    <?= htmlspecialchars($marca['nombre_marca']) ?>
                                </div>
                            </div>
                            
                            <div class="product-view-modal__info-item">
                                <label class="product-view-modal__info-label">Estado</label>
                                <div class="product-view-modal__info-value">
                                    <?php if ($marca['estado_marca'] === 'activo'): ?>
                                        <span class="product-view-modal__badge product-view-modal__badge--success" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; background: rgba(34, 197, 94, 0.15); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.3);">
                                            <i class="fas fa-check-circle"></i>
                                            Activo
                                        </span>
                                    <?php else: ?>
                                        <span class="product-view-modal__badge product-view-modal__badge--danger" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3);">
                                            <i class="fas fa-times-circle"></i>
                                            Inactivo
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <?php if (!empty($marca['descripcion_marca'])): ?>
                            <div class="product-view-modal__info-item" style="grid-column: 1 / -1;">
                                <label class="product-view-modal__info-label">Descripción</label>
                                <div class="product-view-modal__info-value" style="line-height: 1.6;">
                                    <?= nl2br(htmlspecialchars($marca['descripcion_marca'])) ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                        </div>
                    </div>

                    <!-- METADATOS -->
                    <div class="product-view-modal__info-card">
                        <div class="product-view-modal__info-header">
                            <div class="product-view-modal__info-icon" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);">
                                <i class="fas fa-clock"></i>
                            </div>
                            <h3 class="product-view-modal__info-title">Información del Sistema</h3>
                        </div>
                        
                        <div class="product-view-modal__info-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                            
                            <?php if (!empty($marca['fecha_creacion'])): ?>
                            <div class="product-view-modal__info-item">
                                <label class="product-view-modal__info-label">Fecha de Creación</label>
                                <div class="product-view-modal__info-value">
                                    <i class="fas fa-calendar-plus" style="margin-right: 6px; color: #94a3b8;"></i>
                                    <?= date('d/m/Y H:i', strtotime($marca['fecha_creacion'])) ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($marca['fecha_modificacion'])): ?>
                            <div class="product-view-modal__info-item">
                                <label class="product-view-modal__info-label">Última Modificación</label>
                                <div class="product-view-modal__info-value">
                                    <i class="fas fa-calendar-edit" style="margin-right: 6px; color: #94a3b8;"></i>
                                    <?= date('d/m/Y H:i', strtotime($marca['fecha_modificacion'])) ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="product-view-modal__footer" style="padding: 20px 32px; background: rgba(148, 163, 184, 0.05); border-top: 1px solid rgba(148, 163, 184, 0.1); display: flex; justify-content: flex-end; gap: 12px;">
            <button type="button" class="product-view-modal__btn product-view-modal__btn--secondary" onclick="closeMarcaModal()" style="padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 8px; border: none; background: rgba(148, 163, 184, 0.15); color: #cbd5e1;">
                <i class="fas fa-times"></i>
                Cerrar
            </button>
        </div>
    </div>
</div>

<?php endif; ?>
