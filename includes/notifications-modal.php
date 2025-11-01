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
                <div class="notification-item-wrapper" style="position: relative; margin-bottom: 8px;">
                    <!-- Fondo gris izquierdo -->
                    <div class="notification-delete-bg-left" style="position: absolute; top: 0; left: 0; bottom: 0; width: 80px; background: #f5f5f5; border-radius: 8px 0 0 8px; display: flex; align-items: center; justify-content: flex-start; padding-left: 20px; opacity: 0; transition: opacity 0.2s;">
                        <i class="fa fa-trash" style="color: #999; font-size: 20px;"></i>
                    </div>
                    
                    <!-- Fondo gris derecho -->
                    <div class="notification-delete-bg-right" style="position: absolute; top: 0; right: 0; bottom: 0; width: 80px; background: #f5f5f5; border-radius: 0 8px 8px 0; display: flex; align-items: center; justify-content: flex-end; padding-right: 20px; opacity: 0; transition: opacity 0.2s;">
                        <i class="fa fa-trash" style="color: #999; font-size: 20px;"></i>
                    </div>
                    
                    <!-- Tarjeta de notificación deslizable -->
                    <div class="notification-item" 
                         data-id="<?php echo $notif['id_notificacion']; ?>" 
                         style="position: relative; display: flex; gap: 10px; padding: 10px; border-radius: 8px; border: 1px solid #eee; background: white; cursor: grab; touch-action: pan-y; <?php echo $isUnread ? 'border-left: 3px solid #667eea; background: #f8f9ff;' : ''; ?>">
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
        const baseUrl = (window.BASE_URL || '').replace(/\/+$/, '');
        
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
    }
    
    // ==========================================
    // SWIPE TO DELETE - MOUSE Y TOUCH (AMBAS DIRECCIONES)
    // ==========================================
    
    function initSwipeToDelete() {
        const items = document.querySelectorAll('.notification-item');
        
        items.forEach(item => {
            const wrapper = item.parentElement;
            const deleteBgLeft = wrapper.querySelector('.notification-delete-bg-left');
            const deleteBgRight = wrapper.querySelector('.notification-delete-bg-right');
            
            let startX = 0;
            let currentX = 0;
            let isDragging = false;
            let startTime = 0;
            
            const SWIPE_THRESHOLD = 100; // Pixels para activar eliminación
            const VELOCITY_THRESHOLD = 0.3; // Velocidad mínima para swipe rápido
            
            // Mouse Events
            item.addEventListener('mousedown', handleStart);
            document.addEventListener('mousemove', handleMove);
            document.addEventListener('mouseup', handleEnd);
            
            // Touch Events
            item.addEventListener('touchstart', handleStart, { passive: true });
            document.addEventListener('touchmove', handleMove, { passive: false });
            document.addEventListener('touchend', handleEnd);
            
            function handleStart(e) {
                isDragging = true;
                startTime = Date.now();
                item.style.cursor = 'grabbing';
                item.style.transition = 'none';
                deleteBgLeft.style.transition = 'none';
                deleteBgRight.style.transition = 'none';
                
                if (e.type === 'mousedown') {
                    startX = e.clientX;
                } else if (e.type === 'touchstart') {
                    startX = e.touches[0].clientX;
                }
            }
            
            function handleMove(e) {
                if (!isDragging) return;
                
                e.preventDefault();
                
                if (e.type === 'mousemove') {
                    currentX = e.clientX - startX;
                } else if (e.type === 'touchmove') {
                    currentX = e.touches[0].clientX - startX;
                }
                
                // Limitar el deslizamiento máximo en ambas direcciones
                const maxSwipe = 150;
                if (currentX > maxSwipe) {
                    currentX = maxSwipe;
                } else if (currentX < -maxSwipe) {
                    currentX = -maxSwipe;
                }
                
                // Aplicar transformación
                item.style.transform = `translateX(${currentX}px)`;
                
                // Mostrar fondo correspondiente según dirección
                const distance = Math.abs(currentX);
                const opacity = Math.min(distance / SWIPE_THRESHOLD, 1);
                
                if (currentX > 0) {
                    // Deslizando hacia la derecha - mostrar fondo izquierdo
                    deleteBgLeft.style.opacity = opacity;
                    deleteBgRight.style.opacity = 0;
                } else if (currentX < 0) {
                    // Deslizando hacia la izquierda - mostrar fondo derecho
                    deleteBgRight.style.opacity = opacity;
                    deleteBgLeft.style.opacity = 0;
                } else {
                    deleteBgLeft.style.opacity = 0;
                    deleteBgRight.style.opacity = 0;
                }
            }
            
            function handleEnd(e) {
                if (!isDragging) return;
                
                isDragging = false;
                item.style.cursor = 'grab';
                item.style.transition = 'transform 0.3s ease, opacity 0.3s ease';
                deleteBgLeft.style.transition = 'opacity 0.2s';
                deleteBgRight.style.transition = 'opacity 0.2s';
                
                const endTime = Date.now();
                const timeDiff = endTime - startTime;
                const velocity = Math.abs(currentX) / timeDiff;
                
                // Determinar si se debe eliminar (cualquier dirección)
                const shouldDelete = Math.abs(currentX) >= SWIPE_THRESHOLD || velocity >= VELOCITY_THRESHOLD;
                
                if (shouldDelete) {
                    // Eliminar notificación - animar en la dirección del swipe
                    const id = item.getAttribute('data-id');
                    const direction = currentX > 0 ? 1 : -1;
                    deleteNotification(id, wrapper, item, direction);
                } else {
                    // Regresar a posición original
                    item.style.transform = 'translateX(0)';
                    deleteBgLeft.style.opacity = '0';
                    deleteBgRight.style.opacity = '0';
                }
                
                currentX = 0;
            }
        });
    }

    // Eliminar notificación con animación
    function deleteNotification(id, wrapper, item, direction) {
        const baseUrl = (window.BASE_URL || '').replace(/\/+$/, '');
        
        // Animación de salida en la dirección del swipe
        const translateValue = direction > 0 ? '100%' : '-100%';
        item.style.transform = `translateX(${translateValue})`;
        item.style.opacity = '0';
        
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
                    wrapper.remove();
                    
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
                item.style.transform = 'translateX(0)';
                item.style.opacity = '1';
                showToast(data.message || 'Error al eliminar notificación', 'error');
            }
        })
        .catch(error => {
            item.style.transform = 'translateX(0)';
            item.style.opacity = '1';
            showToast('Error al eliminar notificación', 'error');
            console.error('Error:', error);
        });
    }

    // Inicializar
    markAllAsReadOnOpen();
    initSwipeToDelete();
})();
</script>
<?php endif; ?>
