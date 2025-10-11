<?php
// Modal para marcas - Vista PHP (Mejorado: diseño "Ver marca" con scroll arreglado, animaciones suaves y corrección de colores)
// Incluir la conexión para obtener categorías y marcas
require_once __DIR__ . '/../../../config/conexion.php';

// Obtener categorías para el select
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

// Si es edici�n o vista, obtener datos del marca
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
$modalTitle = $isView ? 'Ver marca' : ($isEdit ? 'Editar marca' : 'Nuevo marca');
$iconClass = $isView ? 'eye' : ($isEdit ? 'edit' : 'plus');
?>

<?php if ($isView && $hasData): ?>

<!-- VIEW: Modal Ver Marca - Diseño Profesional Mejorado -->
<div class="product-view-modal marca-view-enhanced show" id="marcaViewModal">
    <div class="product-view-modal__overlay"></div>
    
    <div class="product-view-modal__container" style="max-width: 1000px;">
        <!-- HEADER MEJORADO -->
        <div class="product-view-modal__header" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); padding: 2rem 2.5rem; border-bottom: 2px solid rgba(251, 191, 36, 0.2);">
            <div class="product-view-modal__header-content" style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
                <div style="display: flex; align-items: center; gap: 1.5rem;">
                    <div class="product-view-modal__icon" style="width: 60px; height: 60px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 28px; color: white; box-shadow: 0 8px 16px rgba(245, 158, 11, 0.3);">
                        <i class="fas fa-tag"></i>
                    </div>
                    <div>
                        <h2 class="product-view-modal__title" style="font-size: 28px; font-weight: 700; color: #f1f5f9; margin: 0 0 0.5rem 0; letter-spacing: -0.5px;">
                            <?= htmlspecialchars($marca['nombre_marca']) ?>
                        </h2>
                        <div style="display: flex; gap: 1rem; align-items: center;">
                            <span class="product-view-modal__badge" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.4rem 0.9rem; border-radius: 20px; font-size: 13px; font-weight: 600; background: rgba(251, 191, 36, 0.15); color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.3);">
                                <i class="fas fa-lock"></i>
                                Solo Lectura
                            </span>
                            <?php if ($marca['estado_marca'] === 'activo'): ?>
                                <span style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.4rem 0.9rem; border-radius: 20px; font-size: 13px; font-weight: 600; background: rgba(34, 197, 94, 0.15); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.3);">
                                    <i class="fas fa-check-circle"></i>
                                    Activo
                                </span>
                            <?php else: ?>
                                <span style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.4rem 0.9rem; border-radius: 20px; font-size: 13px; font-weight: 600; background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3);">
                                    <i class="fas fa-times-circle"></i>
                                    Inactivo
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <button type="button" class="product-view-modal__close" onclick="closeMarcaModal()" aria-label="Cerrar" style="width: 44px; height: 44px; background: rgba(148, 163, 184, 0.1); border: 1px solid rgba(148, 163, 184, 0.2); border-radius: 12px; color: #cbd5e1; font-size: 20px; cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- BODY MEJORADO -->
        <div class="product-view-modal__body" style="padding: 2.5rem; max-height: 70vh; overflow-y: auto;">
            <div class="product-view-modal__content" style="display: grid; grid-template-columns: 380px 1fr; gap: 2.5rem;">
                
                <!-- PANEL IZQUIERDO: IMAGEN DESTACADA (STICKY) -->
                <div class="product-view-modal__image-section" style="display: flex; flex-direction: column; gap: 1.5rem; position: sticky; top: 0; align-self: start;">
                    <!-- Imagen Principal con efecto glassmorphism -->
                    <div class="product-view-modal__image-container" style="position: relative; height: 450px; background: linear-gradient(135deg, rgba(248, 250, 252, 0.95) 0%, rgba(226, 232, 240, 0.95) 100%); backdrop-filter: blur(10px); padding: 30px; border-radius: 20px; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1), inset 0 0 0 1px rgba(255, 255, 255, 0.5); overflow: hidden; display: flex; align-items: flex-end; justify-content: center; padding-bottom: 50px;">
                        <!-- Decoración de fondo -->
                        <div style="position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(245, 158, 11, 0.05) 0%, transparent 70%); pointer-events: none;"></div>
                        
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
                             style="position: relative; z-index: 1; max-width: 100%; max-height: 350px; object-fit: contain; border-radius: 12px; cursor: zoom-in; transition: transform 0.3s ease;"
                             onerror="this.src='/fashion-master/public/assets/img/default-product.jpg'; this.onerror=null;"
                             onmouseover="this.style.transform='scale(1.05)'"
                             onmouseout="this.style.transform='scale(1)'">
                        
                        <!-- Badge de zoom -->
                        <div style="position: absolute; bottom: 20px; right: 20px; background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(8px); padding: 0.6rem 1rem; border-radius: 8px; color: white; font-size: 12px; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; pointer-events: none; z-index: 2;">
                            <i class="fas fa-search-plus"></i>
                            Doble clic para ampliar
                        </div>
                    </div>
                    
                    <!-- Card de ID -->
                    <div style="background: linear-gradient(135deg, #334155 0%, #1e293b 100%); padding: 1.5rem; border-radius: 16px; border: 1px solid rgba(148, 163, 184, 0.1); box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span style="color: #94a3b8; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">ID de Marca</span>
                            <i class="fas fa-hashtag" style="color: #f59e0b;"></i>
                        </div>
                        <div style="color: #f1f5f9; font-size: 24px; font-weight: 700; font-family: 'Courier New', monospace;">#<?= str_pad($marca['id_marca'], 4, '0', STR_PAD_LEFT) ?></div>
                    </div>
                </div>

                <!-- PANEL DERECHO: INFORMACIÓN DETALLADA -->
                <div class="product-view-modal__details-section" style="display: flex; flex-direction: column; gap: 1.5rem;">
                    
                    <!-- CARD: Información Básica -->
                    <div class="product-view-modal__info-card" style="background: rgba(51, 65, 85, 0.4); backdrop-filter: blur(10px); border: 1px solid rgba(148, 163, 184, 0.1); border-radius: 16px; padding: 1.8rem; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);">
                        <div class="product-view-modal__info-header" style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid rgba(139, 92, 246, 0.2);">
                            <div class="product-view-modal__info-icon" style="width: 44px; height: 44px; background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; color: white; box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <h3 class="product-view-modal__info-title" style="font-size: 20px; font-weight: 700; color: #f1f5f9; margin: 0;">Información Básica</h3>
                        </div>
                        
                        <div class="product-view-modal__info-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
                            
                            <div class="product-view-modal__info-item" style="background: rgba(30, 41, 59, 0.4); padding: 1.2rem; border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.08); transition: all 0.3s ease;" onmouseover="this.style.background='rgba(30, 41, 59, 0.6)'; this.style.borderColor='rgba(139, 92, 246, 0.3)'" onmouseout="this.style.background='rgba(30, 41, 59, 0.4)'; this.style.borderColor='rgba(148, 163, 184, 0.08)'">
                                <label class="product-view-modal__info-label" style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.6rem;">
                                    <i class="fas fa-tag" style="margin-right: 0.4rem; color: #f59e0b;"></i>
                                    Nombre de la Marca
                                </label>
                                <div class="product-view-modal__info-value" style="color: #e2e8f0; font-size: 16px; font-weight: 600;">
                                    <?= htmlspecialchars($marca['nombre_marca']) ?>
                                </div>
                            </div>
                            
                            <div class="product-view-modal__info-item" style="background: rgba(30, 41, 59, 0.4); padding: 1.2rem; border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.08); transition: all 0.3s ease;" onmouseover="this.style.background='rgba(30, 41, 59, 0.6)'; this.style.borderColor='rgba(139, 92, 246, 0.3)'" onmouseout="this.style.background='rgba(30, 41, 59, 0.4)'; this.style.borderColor='rgba(148, 163, 184, 0.08)'">
                                <label class="product-view-modal__info-label" style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.6rem;">
                                    <i class="fas fa-barcode" style="margin-right: 0.4rem; color: #06b6d4;"></i>
                                    Código
                                </label>
                                <div class="product-view-modal__info-value" style="color: #e2e8f0; font-size: 16px; font-weight: 600; font-family: 'Courier New', monospace;">
                                    <?= htmlspecialchars($marca['codigo_marca'] ?? 'N/A') ?>
                                </div>
                            </div>
                            
                            <?php if (!empty($marca['descripcion_marca'])): ?>
                            <div class="product-view-modal__info-item" style="grid-column: 1 / -1; background: rgba(30, 41, 59, 0.4); padding: 1.2rem; border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.08);">
                                <label class="product-view-modal__info-label" style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.6rem;">
                                    <i class="fas fa-align-left" style="margin-right: 0.4rem; color: #10b981;"></i>
                                    Descripción
                                </label>
                                <div class="product-view-modal__info-value" style="color: #cbd5e1; font-size: 15px; line-height: 1.7; font-weight: 400;">
                                    <?= nl2br(htmlspecialchars($marca['descripcion_marca'])) ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- CARD: Estado y Configuración -->
                    <div class="product-view-modal__info-card" style="background: rgba(51, 65, 85, 0.4); backdrop-filter: blur(10px); border: 1px solid rgba(148, 163, 184, 0.1); border-radius: 16px; padding: 1.8rem; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);">
                        <div class="product-view-modal__info-header" style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid rgba(34, 197, 94, 0.2);">
                            <div class="product-view-modal__info-icon" style="width: 44px; height: 44px; background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; color: white; box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);">
                                <i class="fas fa-cogs"></i>
                            </div>
                            <h3 class="product-view-modal__info-title" style="font-size: 20px; font-weight: 700; color: #f1f5f9; margin: 0;">Estado y Configuración</h3>
                        </div>
                        
                        <div class="product-view-modal__info-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
                            
                            <div class="product-view-modal__info-item" style="background: rgba(30, 41, 59, 0.4); padding: 1.2rem; border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.08); transition: all 0.3s ease;" onmouseover="this.style.background='rgba(30, 41, 59, 0.6)'; this.style.borderColor='rgba(34, 197, 94, 0.3)'" onmouseout="this.style.background='rgba(30, 41, 59, 0.4)'; this.style.borderColor='rgba(148, 163, 184, 0.08)'">
                                <label class="product-view-modal__info-label" style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.6rem;">
                                    <i class="fas fa-toggle-on" style="margin-right: 0.4rem; color: <?= $marca['estado_marca'] === 'activo' ? '#22c55e' : '#ef4444' ?>;"></i>
                                    Estado
                                </label>
                                <div class="product-view-modal__info-value" style="color: #e2e8f0; font-size: 16px; font-weight: 600;">
                                    <span style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border-radius: 10px; background: <?= $marca['estado_marca'] === 'activo' ? 'rgba(34, 197, 94, 0.15)' : 'rgba(239, 68, 68, 0.15)' ?>; color: <?= $marca['estado_marca'] === 'activo' ? '#22c55e' : '#ef4444' ?>; border: 1px solid <?= $marca['estado_marca'] === 'activo' ? 'rgba(34, 197, 94, 0.3)' : 'rgba(239, 68, 68, 0.3)' ?>;">
                                        <i class="fas fa-<?= $marca['estado_marca'] === 'activo' ? 'check-circle' : 'times-circle' ?>"></i>
                                        <?= ucfirst(htmlspecialchars($marca['estado_marca'])) ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="product-view-modal__info-item" style="background: rgba(30, 41, 59, 0.4); padding: 1.2rem; border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.08); transition: all 0.3s ease;" onmouseover="this.style.background='rgba(30, 41, 59, 0.6)'; this.style.borderColor='rgba(34, 197, 94, 0.3)'" onmouseout="this.style.background='rgba(30, 41, 59, 0.4)'; this.style.borderColor='rgba(148, 163, 184, 0.08)'">
                                <label class="product-view-modal__info-label" style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.6rem;">
                                    <i class="fas fa-database" style="margin-right: 0.4rem; color: #3b82f6;"></i>
                                    Status
                                </label>
                                <div class="product-view-modal__info-value" style="color: #e2e8f0; font-size: 16px; font-weight: 600;">
                                    <?= $marca['status_marca'] == 1 ? '<span style="color: #22c55e;"><i class="fas fa-check-circle"></i> Visible</span>' : '<span style="color: #ef4444;"><i class="fas fa-eye-slash"></i> Oculto</span>' ?>
                                </div>
                            </div>

                            <?php if (!empty($marca['pais_origen'])): ?>
                            <div class="product-view-modal__info-item" style="background: rgba(30, 41, 59, 0.4); padding: 1.2rem; border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.08); transition: all 0.3s ease;" onmouseover="this.style.background='rgba(30, 41, 59, 0.6)'; this.style.borderColor='rgba(34, 197, 94, 0.3)'" onmouseout="this.style.background='rgba(30, 41, 59, 0.4)'; this.style.borderColor='rgba(148, 163, 184, 0.08)'">
                                <label class="product-view-modal__info-label" style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.6rem;">
                                    <i class="fas fa-globe" style="margin-right: 0.4rem; color: #8b5cf6;"></i>
                                    País de Origen
                                </label>
                                <div class="product-view-modal__info-value" style="color: #e2e8f0; font-size: 16px; font-weight: 600;">
                                    <?= htmlspecialchars($marca['pais_origen']) ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($marca['sitio_web'])): ?>
                            <div class="product-view-modal__info-item" style="background: rgba(30, 41, 59, 0.4); padding: 1.2rem; border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.08); transition: all 0.3s ease;" onmouseover="this.style.background='rgba(30, 41, 59, 0.6)'; this.style.borderColor='rgba(34, 197, 94, 0.3)'" onmouseout="this.style.background='rgba(30, 41, 59, 0.4)'; this.style.borderColor='rgba(148, 163, 184, 0.08)'">
                                <label class="product-view-modal__info-label" style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.6rem;">
                                    <i class="fas fa-link" style="margin-right: 0.4rem; color: #06b6d4;"></i>
                                    Sitio Web
                                </label>
                                <div class="product-view-modal__info-value" style="color: #06b6d4; font-size: 14px; font-weight: 600; word-break: break-all;">
                                    <a href="<?= htmlspecialchars($marca['sitio_web']) ?>" target="_blank" style="color: #06b6d4; text-decoration: none; transition: color 0.3s;" onmouseover="this.style.color='#0891b2'" onmouseout="this.style.color='#06b6d4'">
                                        <i class="fas fa-external-link-alt" style="margin-right: 0.4rem;"></i>
                                        <?= htmlspecialchars($marca['sitio_web']) ?>
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- CARD: Información del Sistema -->
                    <div class="product-view-modal__info-card" style="background: rgba(51, 65, 85, 0.4); backdrop-filter: blur(10px); border: 1px solid rgba(148, 163, 184, 0.1); border-radius: 16px; padding: 1.8rem; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);">
                        <div class="product-view-modal__info-header" style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid rgba(6, 182, 212, 0.2);">
                            <div class="product-view-modal__info-icon" style="width: 44px; height: 44px; background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; color: white; box-shadow: 0 4px 12px rgba(6, 182, 212, 0.3);">
                                <i class="fas fa-clock"></i>
                            </div>
                            <h3 class="product-view-modal__info-title" style="font-size: 20px; font-weight: 700; color: #f1f5f9; margin: 0;">Información del Sistema</h3>
                        </div>
                        
                        <div class="product-view-modal__info-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
                            
                            <?php if (!empty($marca['fecha_creacion_marca'])): ?>
                            <div class="product-view-modal__info-item" style="background: rgba(30, 41, 59, 0.4); padding: 1.2rem; border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.08); transition: all 0.3s ease;" onmouseover="this.style.background='rgba(30, 41, 59, 0.6)'; this.style.borderColor='rgba(6, 182, 212, 0.3)'" onmouseout="this.style.background='rgba(30, 41, 59, 0.4)'; this.style.borderColor='rgba(148, 163, 184, 0.08)'">
                                <label class="product-view-modal__info-label" style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.6rem;">
                                    <i class="fas fa-calendar-plus" style="margin-right: 0.4rem; color: #22c55e;"></i>
                                    Creado
                                </label>
                                <div class="product-view-modal__info-value" style="color: #e2e8f0; font-size: 15px; font-weight: 600;">
                                    <?= date('d/m/Y', strtotime($marca['fecha_creacion_marca'])) ?>
                                </div>
                                <div style="color: #94a3b8; font-size: 13px; margin-top: 0.3rem;">
                                    <?= date('H:i:s', strtotime($marca['fecha_creacion_marca'])) ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($marca['fecha_actualizacion_marca'])): ?>
                            <div class="product-view-modal__info-item" style="background: rgba(30, 41, 59, 0.4); padding: 1.2rem; border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.08); transition: all 0.3s ease;" onmouseover="this.style.background='rgba(30, 41, 59, 0.6)'; this.style.borderColor='rgba(6, 182, 212, 0.3)'" onmouseout="this.style.background='rgba(30, 41, 59, 0.4)'; this.style.borderColor='rgba(148, 163, 184, 0.08)'">
                                <label class="product-view-modal__info-label" style="display: block; color: #94a3b8; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.6rem;">
                                    <i class="fas fa-calendar-edit" style="margin-right: 0.4rem; color: #f59e0b;"></i>
                                    Modificado
                                </label>
                                <div class="product-view-modal__info-value" style="color: #e2e8f0; font-size: 15px; font-weight: 600;">
                                    <?= date('d/m/Y', strtotime($marca['fecha_actualizacion_marca'])) ?>
                                </div>
                                <div style="color: #94a3b8; font-size: 13px; margin-top: 0.3rem;">
                                    <?= date('H:i:s', strtotime($marca['fecha_actualizacion_marca'])) ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- FOOTER PROFESIONAL -->
        <div class="product-view-modal__footer" style="padding: 1.5rem 2.5rem; background: rgba(15, 23, 42, 0.4); backdrop-filter: blur(10px); border-top: 2px solid rgba(148, 163, 184, 0.1); display: flex; justify-content: space-between; align-items: center;">
            <div style="color: #94a3b8; font-size: 13px; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-info-circle"></i>
                <span>Modal de solo lectura</span>
            </div>
            <button type="button" class="product-view-modal__btn product-view-modal__btn--secondary" onclick="closeMarcaModal()" style="padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #64748b 0%, #475569 100%); color: white; border: none; border-radius: 10px; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; gap: 0.6rem; box-shadow: 0 4px 12px rgba(100, 116, 139, 0.3);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(100, 116, 139, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(100, 116, 139, 0.3)'">
                <i class="fas fa-times"></i>
                Cerrar
            </button>
        </div>
    </div>
</div>

<script>
// JavaScript específico para el modal Ver marca
(function() {
    'use strict';
    
    // Función para cerrar el modal Ver marca
    // ✅ SIMPLIFICADO: Solo usar closeMarcaModal global
    function closeViewmarcaModal() {
        console.log('✅ closeViewmarcaModal() - llamando a función global');
        
        // Siempre llamar a la función global
        if (typeof window.closeMarcaModal === 'function') {
            window.closeMarcaModal();
        } else {
            console.error('❌ closeMarcaModal no está disponible');
        }
    }
    
    // Exponer función localmente
    window.closeViewmarcaModal = closeViewmarcaModal;
    
    // Funci�n para activar animaciones del stock despu�s de que el modal aparezca
    function activateStockAnimations() {
        const stockBar = document.querySelector('.marca-view-modal__stock-fill');
        if (stockBar) {
            // Guardar el ancho original
            const originalWidth = stockBar.style.width;
            const stockValue = parseInt(originalWidth);
            
            // Resetear a 0
            stockBar.style.width = '0%';
            stockBar.style.transition = 'none';
            
            // Aplicar el ancho original despu�s de un delay con transici�n suave
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
        const discountField = document.querySelector('.marca-view-modal__field-value--discount');
        if (discountField) {
            const discountValue = parseFloat(discountField.dataset.discount);
            
            // Intensificar efectos seg�n el porcentaje de descuento
            if (discountValue >= 50) {
                discountField.style.animation = 'discountPulse 1s ease-in-out infinite, fireEffect 0.5s ease-in-out infinite';
                discountField.style.boxShadow = '0 0 40px rgba(255, 68, 68, 0.8), 0 0 80px rgba(255, 107, 107, 0.4)';
            } else if (discountValue >= 30) {
                discountField.style.animation = 'discountPulse 1.5s ease-in-out infinite';
                discountField.style.boxShadow = '0 0 25px rgba(255, 68, 68, 0.6)';
            }
        }
    }
    
    // Activar animaciones despu�s de que el modal est� visible
    setTimeout(activateStockAnimations, 1000);
    
    // Funci�n de debug para verificar estado del modal
    window.debugModal = function() {
        const modal = document.querySelector('.marca-view-modal');
        if (modal) {
            const styles = window.getComputedStyle(modal);
            console.log('?? Debug modal Ver marca:', {
                found: true,
                classes: modal.className,
                display: styles.display,
                opacity: styles.opacity,
                visibility: styles.visibility,
                position: styles.position,
                zIndex: styles.zIndex,
                top: styles.top,
                left: styles.left,
                background: styles.background,
                backdropFilter: styles.backdropFilter
            });
        } else {
            console.log('? Modal Ver marca no encontrado en DOM');
        }
    };
    
    // ✅ SIMPLIFICADO: Los botones usan onclick="closeMarcaModal()" en el HTML
    // Solo necesitamos configurar el overlay para cerrar al hacer click fuera
    function setupCloseEvents() {
        console.log('⚙️ Configurando eventos de cierre del modal');
        
        // Cerrar con overlay (click en fondo)
        const overlay = document.querySelector('.marca-view-modal__overlay');
        if (overlay) {
            overlay.removeEventListener('click', overlay.clickHandler);
            overlay.clickHandler = function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('🔘 Click en overlay (fondo)');
                if (typeof window.closeMarcaModal === 'function') {
                    window.closeMarcaModal();
                }
            };
            overlay.addEventListener('click', overlay.clickHandler);
        }
    }
    
    // Configurar evento ESC GLOBAL inmediatamente
    if (!window.modalEscapeConfigured) {
        const globalEscHandler = function(e) {
            if (e.key === 'Escape' || e.keyCode === 27) {
                const activeModal = document.querySelector('.marca-view-modal.show');
                if (activeModal) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('?? Tecla ESC presionada - cerrando modal');
                    if (typeof window.closeMarcaModal === 'function') {
                        window.closeMarcaModal();
                    } else {
                        closeViewmarcaModal();
                    }
                }
            }
        };
        
        // Agregar listener global para ESC
        document.addEventListener('keydown', globalEscHandler, true);
        window.modalEscapeConfigured = true;
        console.log('? Event listener ESC global configurado');
    }
    
    // Configurar eventos inmediatamente
    setupCloseEvents();
    
})();
</script>

<?php else: ?>

<!-- CREAR / EDITAR: se mantiene la versi�n original sin cambios de estilos para evitar conflictos -->
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
    
    <form id="marcaForm" method="POST" action="" enctype="multipart/form-data">
        <input type="hidden" name="action" value="<?= $isEdit ? 'update' : 'create' ?>">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id_marca" value="<?= $marca['id_marca'] ?>">
            <!-- Mantener status_marca sin modificar al editar -->
            <input type="hidden" name="status_marca" value="<?= $marca['status_marca'] ?>">
        <?php else: ?>
            <!-- Para marcas nuevos, establecer status_marca en 1 (activo/no eliminado) -->
            <input type="hidden" name="status_marca" value="1">
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
                        <label for="nombre_marca">
                            <i class="fas fa-tag"></i>
                            Nombre de la Marca
                        </label>
                        <input type="text" 
                               id="nombre_marca" 
                               name="nombre_marca" 
                               value="<?= $hasData ? htmlspecialchars($marca['nombre_marca']) : '' ?>"
                               required 
                               maxlength="100"
                               <?= $isView ? 'readonly' : '' ?>
                               placeholder="Ej: Nike, Adidas, etc..">
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
                               <?= $isView ? 'readonly' : '' ?>
                               placeholder="Ej: MARCA-001">
                        <small class="form-text">Código único identificador (opcional)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="estado_marca">
                            <i class="fas fa-toggle-on"></i>
                            Estado
                        </label>
                        <select id="estado_marca" 
                                name="estado_marca" 
                                class="form-control"
                                <?= $isView ? 'disabled readonly style="background-color: #f8f9fa !important; cursor: not-allowed !important; pointer-events: none !important;"' : '' ?>>
                            <option value="activo" <?= ($hasData && $marca['estado_marca'] === 'activo') ? 'selected' : '' ?>>Activo</option>
                            <option value="inactivo" <?= ($hasData && $marca['estado_marca'] === 'inactivo') ? 'selected' : '' ?>>Inactivo</option>
                        </select>
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
                              maxlength="1000"
                              placeholder="Describe la marca, su historia, valores, etc..."
                              <?= $isView ? 'readonly' : '' ?>><?= $hasData ? htmlspecialchars($marca['descripcion_marca']) : '' ?></textarea>
                    <small class="form-text">Máximo 1000 caracteres (opcional)</small>
                </div>
            </div>
            
            <!-- Imagen de la marca -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-image"></i>
                    Imagen de la Marca 
                </h3>
                
                <div class="file-upload-container" style="display: flex; flex-direction: column; align-items: center;">
                    <?php if ($hasData && (!empty($marca['url_imagen_marca']) || !empty($marca['imagen_marca']))): ?>
                        <div class="current-image-section" style="display: flex; flex-direction: column; align-items: center; gap: 15px;">
                            <div class="current-image-display" style="max-width: 100%; max-height: 350px; overflow: hidden; display: flex; align-items: center; justify-content: center; background: #f8f9fa; border-radius: 8px; padding: 20px;">
                                <?php 
                                $imagenSrc = '';
                                if (!empty($marca['url_imagen_marca']) && $marca['url_imagen_marca'] !== 'NULL') {
                                    $imagenSrc = $marca['url_imagen_marca'];
                                } elseif (!empty($marca['imagen_marca']) && $marca['imagen_marca'] !== 'NULL') {
                                    $imagenSrc = '/fashion-master/public/assets/img/products/' . $marca['imagen_marca'];
                                } else {
                                    $imagenSrc = '/fashion-master/public/assets/img/default-product.png';
                                }
                                ?>
                                <img src="<?= htmlspecialchars($imagenSrc) ?>" 
                                     alt="Imagen actual de la marca" 
                                     class="current-marca-image" 
                                     ondblclick="showImageFullSize('<?= htmlspecialchars($imagenSrc) ?>', '<?= htmlspecialchars($marca['nombre_marca']) ?>')"
                                     style="max-width: 100%; max-height: 310px; object-fit: contain; border-radius: 8px; cursor: zoom-in;"
                                     onerror="this.src='/fashion-master/public/assets/img/default-product.png'; this.onerror=null;">
                            </div>
                            <?php if (!$isView): ?>
                            <div class="change-image-section" style="text-align: center;">
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
                        <p class="upload-text"><strong>Haz clic aqui para seleccionar una imagen</strong><br>o arrastra y suelta tu archivo</p>
                        <small class="upload-hint">Formatos: JPG, PNG, GIF (max. 5MB)</small>
                    </div>
                    <?php endif; ?>

                    <input type="file" id="imagen" name="imagen_marca_file" accept="image/*" style="display:none;">
                    <input type="hidden" id="imagen_marca_base64" name="imagen_marca_base64" value="">
                    <div id="imagePreview" class="image-preview-section" style="display:none; flex-direction: column; align-items: center; gap: 15px;">
                        <div class="preview-image-display" style="width: 200px; height: 200px; overflow: hidden; display: flex; align-items: center; justify-content: center; background: #f8f9fa; border-radius: 8px; border: 2px solid #e2e8f0;">
                            <img id="previewImg" src="" alt="Preview de nueva imagen" class="preview-marca-image" style="width: 100%; height: 100%; object-fit: contain;">
                        </div>
                        <div class="preview-actions-section" style="margin-top: 0; text-align: center;"><button type="button" class="btn-remove-image" onclick="removeImagePreview()"><i class="fas fa-trash"></i> Eliminar imagen</button></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</button>
            <?php if (!$isView): ?>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= $isEdit ? 'Actualizar' : 'Guardar' ?> marca</button>
            <?php endif; ?>
        </div>
    </form>
</div>
<!-- SCRIPTS compartidos para crear/editar (id�nticos a originales, no afectan la vista) -->
<script>
/**
 * Scripts mejorados para modal "Ver marca" (y reutilizables para crear/editar)
 * - Animaciones suaves (entrada, salida, stagger, parallax)
 * - File upload con recorte en canvas (preview) y reemplazo del archivo enviado
 * - Submit AJAX con animaci�n del bot�n y manejo de respuesta
 * - Scroll/touch handling para evitar que la p�gina detr�s se mueva
 *
 * Pegar este <script> tal cual en el modal (ya no necesita m�s cambios).
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

    if (!fileInput) return; // funciona s�lo en crear/editar

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
        alert('Por favor, selecciona un archivo de imagen v�lido.');
        return;
      }
      if (file.size > 5 * 1024 * 1024) {
        alert('El archivo es demasiado grande. M�ximo 5MB.');
        return;
      }

      const reader = new FileReader();
      reader.onload = function (ev) {
        const tmp = new Image();
        tmp.onload = function () {
          // ADAPTAR imagen completa (contain) en lugar de recortar
          const targetSize = 800; // tama�o del contenedor cuadrado
          const canvas = document.createElement('canvas');
          canvas.width = targetSize;
          canvas.height = targetSize;
          const ctx = canvas.getContext('2d');

          // Calcular el escalado para que la imagen completa entre en el canvas
          const scale = Math.min(targetSize / tmp.width, targetSize / tmp.height);
          const scaledWidth = tmp.width * scale;
          const scaledHeight = tmp.height * scale;

          // Centrar la imagen en el canvas
          const offsetX = (targetSize - scaledWidth) / 2;
          const offsetY = (targetSize - scaledHeight) / 2;

          // Fondo blanco para �reas vac�as
          ctx.fillStyle = '#ffffff';
          ctx.fillRect(0, 0, targetSize, targetSize);

          // Dibujar la imagen completa adaptada (contain)
          ctx.drawImage(tmp, offsetX, offsetY, scaledWidth, scaledHeight);

          // Convertir a dataURL
          const dataUrl = canvas.toDataURL('image/jpeg', 0.9);

          // Mostrar preview (animado)
          if (previewImg) {
            previewImg.src = dataUrl;
          }
          if (preview) {
            preview.style.display = 'block';
            preview.style.opacity = '1';
            // Sin requestAnimationFrame ni animateClassOnce para evitar delays
          }

          // ocultar uploadArea y currentImageSection
          if (uploadArea) uploadArea.style.display = 'none';
          if (currentImageSection) currentImageSection.style.display = 'none';

          // Guardar dataURL en el input hidden para enviar al backend
          const base64Input = document.getElementById('imagen_marca_base64');
          if (base64Input) {
            base64Input.value = dataUrl;
            console.log('📸 Imagen convertida a base64 y guardada en input hidden');
          }
          
          // También guardar en dataset por compatibilidad
          newInput.dataset.cropped = dataUrl;
        };
        tmp.src = ev.target.result;
      };
      reader.readAsDataURL(file);
    }
  }

  /* --------------------------
     Detecci�n de cambios en el formulario
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
    console.log('?? Datos originales del formulario capturados:', originalFormData);
  }
  
  function hasFormChanged(form) {
    const currentFormData = new FormData(form);
    let hasChanges = false;
    
    // Comparar cada campo
    for (let [key, value] of currentFormData.entries()) {
      let currentValue = value;
      if (value instanceof File) {
        // Ignorar archivos vac�os (no seleccionados)
        if (value.size === 0) continue;
        currentValue = value.name + '|' + value.size;
        if (originalFormData[key] !== currentValue) {
          console.log(`?? Cambio detectado en ${key}: archivo nuevo seleccionado`);
          hasChanges = true;
        }
      } else {
        if (originalFormData[key] !== currentValue) {
          console.log(`?? Cambio detectado en ${key}: "${originalFormData[key]}" ? "${currentValue}"`);
          hasChanges = true;
        }
      }
    }
    
    // Verificar si se eliminaron campos
    for (let key in originalFormData) {
      if (!currentFormData.has(key) && key !== 'imagen_marca') {
        console.log(`?? Cambio detectado: campo ${key} eliminado`);
        hasChanges = true;
      }
    }
    
    return hasChanges;
  }

  /* --------------------------
     Form submit (AJAX) con reemplazo de imagen por preview cropped
     -------------------------- */
  function setupFormSubmitScoped(root = document) {
    const form = $('#marcaForm', root);
    if (!form) return;
    
    // Capturar datos originales cuando se carga el formulario
    setTimeout(() => captureOriginalFormData(form), 500);

    form.addEventListener('submit', async function (ev) {
      ev.preventDefault();
      
      // VALIDACIÓN: Verificar si realmente hubo cambios
      if (!hasFormChanged(form)) {
        console.log('⚠️ No se detectaron cambios en el formulario. Cerrando modal sin actualizar.');
        
        // Mostrar mensaje sutil
        if (window.parent && typeof window.parent.showNotification === 'function') {
          window.parent.showNotification('No se realizaron cambios', 'info');
        }
        
        // ✅ Cerrar modal INMEDIATAMENTE sin actualizar tabla
        if (typeof window.closeMarcaModal === 'function') {
          window.closeMarcaModal();
        } else {
          console.error('❌ closeMarcaModal no disponible');
        }
        
        return; // Detener el envío
      }
      
      console.log('? Cambios detectados, procediendo con el guardado...');

      const targetUrl = (window.AppConfig && typeof window.AppConfig.getApiUrl === 'function')
        ? window.AppConfig.getApiUrl('marcaController.php')
        : '/app/controllers/marcaController.php';

      // Bot�n submit
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
            // NOTE: algunos navegadores a�aden autom�ticamente el archivo, FormData.delete debe funcionar
            fd.delete('imagen_marca');
            const blob = dataURLToBlob(fileInput.dataset.cropped);
            // nombre de archivo recomendado
            const filename = (fileInput.files && fileInput.files[0] && fileInput.files[0].name) ? fileInput.files[0].name.replace(/\.[^/.]+$/, '') + '-cropped.jpg' : 'imagen-cropped.jpg';
            fd.append('imagen_marca', blob, filename);
          } catch (err) {
            console.warn('No se pudo adjuntar imagen recortada, se enviar� el archivo original si existe.', err);
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
          // CORRECCIÓN: No mostrar notificaciones, solo cerrar el modal silenciosamente
          
          // Primero recargar la tabla con la marca actualizada
          // El backend retorna data.data (no data.marca)
          const marcaActualizada = data.data || data.marca;
          
          if (marcaActualizada) {
            console.log('🔄 marca actualizada recibida, actualizando tabla...');
            console.log('📦 Datos:', marcaActualizada);
            reloadParentmarcasTable(marcaActualizada);
          } else {
            console.warn('⚠️ No se recibieron datos de marca actualizada');
          }
          
          // ✅ CIERRE INMEDIATO - Sin delays
          if (typeof window.closeMarcaModal === 'function') {
            window.closeMarcaModal();
          } else {
            console.error('❌ closeMarcaModal no disponible');
          }
        } else {
          // CORRECCIÓN: No mostrar notificaciones de error
          console.error('Error al guardar:', data.error || 'Error desconocido');
        }

      } catch (error) {
        console.error('Error al enviar formulario:', error);
        if (window.parent && typeof window.parent.showNotification === 'function') {
          window.parent.showNotification('Error de conexi�n al guardar marca', 'error');
        } else {
          alert('Error de conexi�n al guardar marca: ' + (error && error.message ? error.message : error));
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
  function reloadParentmarcasTable(updatedmarca = null) {
    console.log('🔄 Recargando tabla de marcas...');
    console.log('📦 Datos recibidos:', updatedmarca);
    
    // Definir targetWindow al inicio (ámbito global de la función)
    const targetWindow = window;
    
    // Intentar actualización suave primero
    try {
      // ✅ CORRECCIÓN: Usar marcaSmoothTableUpdater específico para marcas
      if (targetWindow.marcaSmoothTableUpdater && updatedmarca) {
        console.log('✅ MarcaSmoothTableUpdater disponible, actualizando...');
        
        // CORRECCIÓN: Pasar el ID como número y los datos completos
        const marcaId = parseInt(updatedmarca.id_marca || updatedmarca.id);
        
        console.log('🎯 Llamando updateSingleMarca con ID:', marcaId);
        console.log('📋 Datos completos:', updatedmarca);
        
        targetWindow.marcaSmoothTableUpdater.updateSingleMarca(marcaId, updatedmarca)
          .then(() => {
            console.log('✅ Tabla actualizada suavemente sin recargar');
          })
          .catch(err => {
            console.error('❌ Error en actualización suave:', err);
            // Fallback a recarga completa
            fallbackReload(targetWindow);
          });
      } else {
        console.log('⚠️ MarcaSmoothTableUpdater no disponible o datos vacíos');
        console.log('  - marcaSmoothTableUpdater:', !!targetWindow.marcaSmoothTableUpdater);
        console.log('  - updatedmarca:', !!updatedmarca);
        fallbackReload(targetWindow);
      }
    } catch (err) {
      console.error('❌ Error al recargar tabla:', err);
      fallbackReload(targetWindow);
    }
  }
  
  function fallbackReload(targetWindow) {
    console.log('🔄 Fallback: recargando tabla completa');
    if (typeof targetWindow.loadMarcas === 'function') {
      targetWindow.loadMarcas();
    } else {
      console.warn('loadMarcas no encontrado en targetWindow');
    }
  }

  /* --------------------------
     Animaciones / Parallax / Accessibility / Scroll lock
     -------------------------- */
  function pvEnhanceScoped(root = document) {
    const modalRoot = root.querySelector('.marca-view-modal') || root;
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
    const closeButtons = $all('.modern-modal-close, .modal-close, .marca-view-modal__close, .marca-view-modal__btn--secondary, .btn-secondary', modalRoot);
    closeButtons.forEach(btn => {
      btn.addEventListener('click', (ev) => {
        ev.preventDefault();
        ev.stopPropagation(); // Prevenir burbujeo de eventos
        
        console.log('🚪 Botón de cierre clickeado - cerrando modal');
        
        // Llamar a la función global closeMarcaModal
        if (typeof window.closeMarcaModal === 'function') {
          window.closeMarcaModal();
        } else if (typeof window.parent !== 'undefined' && typeof window.parent.closeMarcaModal === 'function') {
          window.parent.closeMarcaModal();
        } else {
          console.error('⚠️ closeMarcaModal no encontrado - usando fallback');
          // Fallback: remover el overlay directamente
          const overlay = document.getElementById('product-modal-overlay');
          if (overlay) {
            overlay.classList.remove('show');
            setTimeout(() => {
              if (overlay.parentNode) {
                overlay.remove();
              }
              document.body.classList.remove('modal-open');
            }, 300);
          }
        }
      }, { once: true }); // once:true previene clicks múltiples
    });
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
    
    // Mostrar upload area o current image seg�n corresponda
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
    try { setupFileUploadScoped(context); } catch (e) { console.warn('setupFileUploadScoped error', e); }

    // setup form submit
    try { setupFormSubmitScoped(context); } catch (e) { console.warn('setupFormSubmitScoped error', e); }

    // enhance view modal animations & behavior
    try { pvEnhanceScoped(context); } catch (e) { console.warn('pvEnhanceScoped error', e); }
    
    // Hacer que la imagen sea fixed pero mantener su posici�n en el grid
    try {
      const imageSection = context.querySelector('.marca-view-modal__image-section');
      const modalBody = context.querySelector('.marca-view-modal__body');
      const modalContainer = context.querySelector('.marca-view-modal__container');
      
      if (imageSection && modalBody && modalContainer) {
        // Guardar la posici�n original
        const rect = imageSection.getBoundingClientRect();
        const leftPosition = rect.left;
        
        // Obtener la posici�n del header para calcular el centro del body
        const header = context.querySelector('.marca-view-modal__header');
        const footer = context.querySelector('.marca-view-modal__footer');
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
        
        // Actualizar posici�n si se redimensiona la ventana
        window.addEventListener('resize', function() {
          const newRect = imageSection.parentElement.getBoundingClientRect();
          imageSection.style.left = newRect.left + 'px';
        });
      }
    } catch (e) { console.warn('fixed image error', e); }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => initModalScripts(document));
  } else {
    initModalScripts(document);
  }

  // Exponer utilidades en window por si otro script necesita reinicializar (por ejemplo modales din�micos)
  window.PVModal = {
    init: initModalScripts,
    dataURLToBlob
  };

})();
</script>
<?php endif; ?>
