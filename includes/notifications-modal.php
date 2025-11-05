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

        
        <!-- Header -->
        <div class="notifications-modal-header">
            <div class="notifications-modal-title">
                <i class="fa fa-bell"></i>
                <span>Notificaciones</span>
            </div>
            <div class="notifications-header-meta">
                <div class="notifications-count">
                    <span class="notif-count-number"><?php echo count($notificaciones_usuario); ?></span> <?php echo count($notificaciones_usuario) == 1 ? 'notificación' : 'notificaciones'; ?>
                </div>
                <div class="notifications-update">
                    <i class="fa fa-clock-o"></i>
                    <span>Actualizado ahora</span>
                </div>
            </div>
        </div>

        <!-- Body -->
    <div class="notifications-modal-body" id="notifications-list">
            <?php if(!empty($notificaciones_usuario)): ?>
                <?php foreach($notificaciones_usuario as $notif): 
                    $iconClass = 'fa-bell';
                    if($notif['tipo_notificacion'] == 'pedido') $iconClass = 'fa-shopping-cart';
                    elseif($notif['tipo_notificacion'] == 'promocion') $iconClass = 'fa-tag';
                    elseif($notif['tipo_notificacion'] == 'sistema') $iconClass = 'fa-info-circle';
                    elseif($notif['tipo_notificacion'] == 'alerta') $iconClass = 'fa-exclamation-circle';
                    $iconType = $notif['tipo_notificacion'];
                    $isUnread = $notif['leida_notificacion'] == 0;
                    $dateStr = date('d/m/Y H:i', strtotime($notif['fecha_creacion_notificacion']));
                    $prioridadBadge = $notif['prioridad_notificacion'] === 'alta' ? 
                        '<span class="notification-priority-badge">ALTA</span>' : '';
                ?>
                <div class="notification-item-wrapper">
                    <!-- Fondo gris izquierdo -->
                    <div class="notification-delete-bg-left">
                        <i class="fa fa-trash"></i>
                    </div>
                    <!-- Fondo gris derecho -->
                    <div class="notification-delete-bg-right">
                        <i class="fa fa-trash"></i>
                    </div>
                    <!-- Tarjeta de notificación deslizable -->
                    <div class="notification-item<?php echo $isUnread ? ' unread' : ''; ?>" data-id="<?php echo $notif['id_notificacion']; ?>">
                        
                        <div class="notification-info">
                            <h6><?php echo htmlspecialchars($notif['titulo_notificacion'] ?? 'Notificación'); ?><?php echo $prioridadBadge; ?></h6>
                            <p><?php echo htmlspecialchars($notif['mensaje_notificacion']); ?></p>
                            <small><?php echo $dateStr; ?></small>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="notifications-empty">
                    <i class="fa fa-bell-o"></i>
                    <p>No tienes notificaciones</p>
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
            let startY = 0;
            let currentX = 0;
            let currentY = 0;
            let isDragging = false;
            let isHorizontalSwipe = false;
            let startTime = 0;
            
            const SWIPE_THRESHOLD = 100; // Pixels para activar eliminación
            const VELOCITY_THRESHOLD = 0.3; // Velocidad mínima para swipe rápido
            const DIRECTION_THRESHOLD = 10; // Pixels para determinar dirección inicial
            
            // Mouse Events
            item.addEventListener('mousedown', handleStart);
            document.addEventListener('mousemove', handleMove);
            document.addEventListener('mouseup', handleEnd);
            
            // Touch Events
            item.addEventListener('touchstart', handleStart, { passive: true });
            document.addEventListener('touchmove', handleMove, { passive: false });
            document.addEventListener('touchend', handleEnd);
            
            function handleStart(e) {
                startTime = Date.now();
                item.style.cursor = 'grabbing';
                
                if (e.type === 'mousedown') {
                    startX = e.clientX;
                    startY = e.clientY;
                    isDragging = true;
                    isHorizontalSwipe = null; // No determinado aún
                } else if (e.type === 'touchstart') {
                    startX = e.touches[0].clientX;
                    startY = e.touches[0].clientY;
                    isDragging = true;
                    isHorizontalSwipe = null; // No determinado aún
                }
            }
            
            function handleMove(e) {
                if (!isDragging) return;
                
                let clientX, clientY;
                if (e.type === 'mousemove') {
                    clientX = e.clientX;
                    clientY = e.clientY;
                } else if (e.type === 'touchmove') {
                    clientX = e.touches[0].clientX;
                    clientY = e.touches[0].clientY;
                }
                
                const deltaX = clientX - startX;
                const deltaY = clientY - startY;
                
                // Determinar dirección del gesto en el primer movimiento significativo
                if (isHorizontalSwipe === null && (Math.abs(deltaX) > DIRECTION_THRESHOLD || Math.abs(deltaY) > DIRECTION_THRESHOLD)) {
                    isHorizontalSwipe = Math.abs(deltaX) > Math.abs(deltaY);
                }
                
                // Solo manejar swipe horizontal
                if (isHorizontalSwipe === true) {
                    // Prevenir scroll solo si es swipe horizontal
                    e.preventDefault();
                    
                    item.style.transition = 'none';
                    deleteBgLeft.style.transition = 'none';
                    deleteBgRight.style.transition = 'none';
                    
                    currentX = deltaX;
                    currentY = deltaY;
                    
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
                // Si es vertical (scroll), no hacer nada y dejar que el navegador maneje el scroll
            }
            
            function handleEnd(e) {
                if (!isDragging) return;
                
                isDragging = false;
                item.style.cursor = 'grab';
                item.style.transition = 'transform 0.3s ease, opacity 0.3s ease';
                deleteBgLeft.style.transition = 'opacity 0.2s';
                deleteBgRight.style.transition = 'opacity 0.2s';
                
                // Solo procesar eliminación si fue un swipe horizontal
                if (isHorizontalSwipe === true) {
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
                } else {
                    // Si fue vertical, simplemente resetear cualquier transformación
                    item.style.transform = 'translateX(0)';
                    deleteBgLeft.style.opacity = '0';
                    deleteBgRight.style.opacity = '0';
                }
                
                currentX = 0;
                currentY = 0;
                isHorizontalSwipe = null;
            }
        });
    }

    // Eliminar notificación con animación
    function deleteNotification(id, wrapper, item, direction) {
        // Usar la función global de RealTimeUpdates
        if (window.RealTimeUpdates && window.RealTimeUpdates.deleteNotification) {
            // Animar primero
            const translateValue = direction > 0 ? '100%' : '-100%';
            item.style.transform = `translateX(${translateValue})`;
            item.style.opacity = '0';
            
            // Llamar a la función global
            window.RealTimeUpdates.deleteNotification(id, wrapper);
        }
    }

    // Inicializar
    markAllAsReadOnOpen();
    initSwipeToDelete();
})();
</script>
<?php endif; ?>
