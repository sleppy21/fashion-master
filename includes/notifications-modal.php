<?php
/**
 * MODAL DE NOTIFICACIONES - OPTIMIZADO SIN DELAYS
 * Carga las notificaciones desde PHP (no AJAX al abrir)
 * Se incluye en las páginas principales
 */

// Solo mostrar si hay un usuario logueado
if($usuario_logueado): 
    // Obtener notificaciones del usuario desde PHP
    try {
        $notificaciones_usuario = executeQuery("
            SELECT id_notificacion, titulo_notificacion, mensaje_notificacion,
                   tipo_notificacion, prioridad_notificacion, leida_notificacion,
                   fecha_creacion_notificacion
            FROM notificacion
            WHERE id_usuario = ? AND estado_notificacion = 'activo'
            ORDER BY fecha_creacion_notificacion DESC
            LIMIT 50
        ", [$usuario_logueado['id_usuario']]);
        $notificaciones_usuario = $notificaciones_usuario ? $notificaciones_usuario : [];
    } catch(Exception $e) {
        error_log("Error al obtener notificaciones: " . $e->getMessage());
        $notificaciones_usuario = [];
    }
?>
<!-- Notifications Modal Begin -->
<div id="notifications-modal" class="notifications-modal">
    <div class="notifications-modal-content">
        <button class="notifications-modal-close" aria-label="Cerrar modal" style="position: absolute; top: 12px; right: 12px; width: 32px; height: 32px; border: none; border-radius: 50%; cursor: pointer; z-index: 10; display: flex; align-items: center; justify-content: center; transition: transform 0.3s ease;">
            <i class="fa fa-times"></i>
        </button>
        
        <!-- Header -->
        <div class="notifications-modal-header" style="padding: 15px; border-bottom: 2px solid; flex-shrink: 0;">
            <h3 style="margin: 0 0 5px 0; font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                <i class="fa fa-bell"></i> Notificaciones
            </h3>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <p class="notifications-count" style="margin: 0; font-size: 12px; font-weight: 500;">
                    <span class="notif-count-number"><?php echo count($notificaciones_usuario); ?></span> <?php echo count($notificaciones_usuario) == 1 ? 'notificación' : 'notificaciones'; ?>
                </p>
                <p class="notifications-update" style="margin: 0; font-size: 11px; opacity: 0.8;">
                    <i class="fa fa-clock-o" style="margin-right: 4px;"></i>Actualizado ahora
                </p>
            </div>
        </div>

        <!-- Body -->
        <div class="notifications-modal-body" id="notifications-list" style="flex: 1; overflow-y: auto; overflow-x: hidden; padding: 12px; min-height: 0;">
            <?php if(!empty($notificaciones_usuario)): ?>
                <?php foreach($notificaciones_usuario as $notif): 
                    // Determinar icono
                    $iconClass = 'fa-bell';
                    if($notif['tipo_notificacion'] == 'pedido') $iconClass = 'fa-shopping-cart';
                    elseif($notif['tipo_notificacion'] == 'promocion') $iconClass = 'fa-tag';
                    elseif($notif['tipo_notificacion'] == 'sistema') $iconClass = 'fa-info-circle';
                    elseif($notif['tipo_notificacion'] == 'alerta') $iconClass = 'fa-exclamation-circle';
                    
                    // Determinar color
                    $iconColor = 'background: #e3f2fd; color: #1976d2;';
                    if($notif['tipo_notificacion'] == 'promocion') $iconColor = 'background: #fff3e0; color: #f57c00;';
                    elseif($notif['tipo_notificacion'] == 'sistema') $iconColor = 'background: #f3e5f5; color: #7b1fa2;';
                    elseif($notif['tipo_notificacion'] == 'alerta') $iconColor = 'background: #ffebee; color: #c62828;';
                    
                    $isUnread = $notif['leida_notificacion'] == 0;
                    $dateStr = date('d/m/Y H:i', strtotime($notif['fecha_creacion_notificacion']));
                    $prioridadBadge = $notif['prioridad_notificacion'] === 'alta' ? 
                        '<span style="display: inline-block; padding: 2px 6px; border-radius: 10px; font-size: 10px; font-weight: 600; background: #fee; color: #d32f2f; margin-left: 6px;">ALTA</span>' : '';
                ?>
                <div class="notification-item" data-id="<?php echo $notif['id_notificacion']; ?>" 
                     style="display: flex; gap: 10px; padding: 10px; border-radius: 8px; margin-bottom: 8px; border: 1px solid #eee; <?php echo $isUnread ? 'border-left: 3px solid #667eea; background: #f8f9ff;' : ''; ?>">
                    <div class="notification-icon" 
                         style="width: 42px; height: 42px; flex-shrink: 0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px; <?php echo $iconColor; ?>">
                        <i class="fa <?php echo $iconClass; ?>"></i>
                    </div>
                    <div class="notification-info" style="flex: 1; display: flex; flex-direction: column; gap: 4px;">
                        <h6 style="margin: 0; font-size: 13px; font-weight: 600; line-height: 1.3; color: #2c3e50;">
                            <?php echo htmlspecialchars($notif['titulo_notificacion'] ?? 'Notificación'); ?><?php echo $prioridadBadge; ?>
                        </h6>
                        <p style="margin: 0; font-size: 12px; line-height: 1.4; opacity: 0.8; color: #555;">
                            <?php echo htmlspecialchars($notif['mensaje_notificacion']); ?>
                        </p>
                        <small style="font-size: 10px; margin-top: auto; opacity: 0.6; color: #888;">
                            <?php echo $dateStr; ?>
                        </small>
                    </div>
                    <div class="notification-actions" style="display: flex; flex-direction: column; gap: 5px; justify-content: center;">
                        <button class="btn-notif-delete" data-id="<?php echo $notif['id_notificacion']; ?>" 
                                style="width: 32px; height: 32px; border: none; border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 13px; transition: all 0.3s ease; background: #ffebee; color: #c62828;" 
                                title="Eliminar">
                            <i class="fa fa-trash-o"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 60px 20px;">
                    <i class="fa fa-bell-o" style="font-size: 80px; margin-bottom: 20px; opacity: 0.3; color: #ccc;"></i>
                    <p style="font-size: 16px; margin-bottom: 20px; color: #666;">No tienes notificaciones</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<!-- Notifications Modal End -->

<script>
(function() {
    'use strict';
    
    // Función para mostrar toast notifications - USA LA GLOBAL
    function showToast(message, type = 'info') {
        // Usar la función showNotification de cart-favorites-handler.js
        if (typeof window.showNotification === 'function') {
            window.showNotification(message, type);
        } else {
            // Fallback simple
            alert(message);
        }
    }
    
    // Marcar todas las notificaciones como leídas al abrir el modal
    function markAllAsReadOnOpen() {
        const modal = document.getElementById('notifications-modal');
        const notificationBtn = document.querySelector('[data-modal="notifications-modal"]');
        
        if (!modal || !notificationBtn) return;
        
        // Detectar cuando se abre el modal
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === 'class') {
                    if (modal.classList.contains('active')) {
                        // Modal abierto - marcar todas como leídas
                        markAllUnreadAsRead();
                    }
                }
            });
        });
        
        observer.observe(modal, { attributes: true });
    }
    
    // Marcar todas las notificaciones no leídas
    function markAllUnreadAsRead() {
        const baseUrl = window.BASE_URL || '';
        
        // Obtener todas las notificaciones no leídas
        const unreadItems = document.querySelectorAll('.notification-item[style*="border-left: 3px solid"]');
        
        if (unreadItems.length === 0) return;
        
        // Marcar visualmente primero
        unreadItems.forEach(item => {
            item.style.borderLeft = '1px solid #eee';
            item.style.background = 'transparent';
        });
        
        // Llamar al endpoint para marcar todas como leídas
        fetch(baseUrl + '/app/actions/mark_all_notifications_read.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Actualizar contador
                if (window.updateNotificationCount) {
                    window.updateNotificationCount();
                }
            }
        })
        .catch(error => console.error('Error:', error));
    }
    
    // Event listeners para botones de notificaciones
    function attachEventListeners() {
        // Eliminar notificación - SIN CONFIRMACIÓN, CON TOAST
        document.querySelectorAll('.btn-notif-delete').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const id = this.getAttribute('data-id');
                const item = this.closest('.notification-item');
                deleteNotification(id, item);
            });
        });
    }

    // Eliminar notificación con toast y animación - SIN CONFIRMACIÓN
    function deleteNotification(id, item) {
        const baseUrl = window.BASE_URL || '';
        
        // Animación de salida inmediata
        if (item) {
            item.style.transition = 'all 0.3s ease';
            item.style.opacity = '0';
            item.style.transform = 'translateX(100%)';
        }
        
        fetch(baseUrl + '/app/actions/delete_notification.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Eliminar del DOM después de la animación
                setTimeout(() => {
                    if (item) {
                        item.remove();
                    }
                    
                    // Verificar si quedan notificaciones
                    const remainingItems = document.querySelectorAll('.notification-item');
                    const remainingCount = remainingItems.length;
                    
                    if (remainingCount === 0) {
                        // Mostrar mensaje de vacío
                        const container = document.getElementById('notifications-list');
                        if (container) {
                            container.innerHTML = `
                                <div style="text-align: center; padding: 60px 20px;">
                                    <i class="fa fa-bell-o" style="font-size: 80px; margin-bottom: 20px; opacity: 0.3; color: #ccc;"></i>
                                    <p style="font-size: 16px; margin-bottom: 20px; color: #666;">No tienes notificaciones</p>
                                </div>
                            `;
                        }
                    }
                    
                    // Actualizar contador en el header del modal
                    const countEl = document.querySelector('.notifications-count');
                    if (countEl) {
                        const countNumber = countEl.querySelector('.notif-count-number');
                        const countText = remainingCount === 1 ? 'notificación' : 'notificaciones';
                        
                        if (countNumber) {
                            countNumber.textContent = remainingCount;
                        } else {
                            countEl.innerHTML = `<span class="notif-count-number">${remainingCount}</span> ${countText}`;
                        }
                    }
                    
                    // Actualizar contador sin recargar
                    if (window.updateNotificationCount) {
                        window.updateNotificationCount();
                    }
                }, 300);
                
                showToast('Notificación eliminada', 'success');
            } else {
                // Revertir animación si falló
                if (item) {
                    item.style.opacity = '1';
                    item.style.transform = 'translateX(0)';
                }
                showToast(data.message || 'Error al eliminar notificación', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (item) {
                item.style.opacity = '1';
                item.style.transform = 'translateX(0)';
            }
            showToast('Error al eliminar notificación', 'error');
        });
    }

    // Inicializar
    attachEventListeners();
    markAllAsReadOnOpen();
})();
</script>
<?php endif; ?>
