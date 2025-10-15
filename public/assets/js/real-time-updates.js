/**
 * REAL-TIME UPDATES - Sistema de actualización en tiempo real
 * Actualiza modales y productos sin recargar la página
 * @version 1.0
 */

const RealTimeUpdates = (function() {
    'use strict';

    const baseUrl = window.BASE_URL || '';

    // ============================================
    // NOTIFICACIONES - ELIMINAR/MARCAR SIN CONFIRMACIÓN
    // ============================================
    
    function deleteNotification(id, element) {
        // Animación de salida inmediata
        if (element) {
            element.style.opacity = '0';
            element.style.transform = 'translateX(100%)';
            element.style.transition = 'all 0.3s ease';
        }

        // Crear FormData
        const formData = new FormData();
        formData.append('id', id);

        fetch(baseUrl + '/app/actions/delete_notification.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Eliminar elemento del DOM después de la animación
                setTimeout(() => {
                    if (element) {
                        element.remove();
                    }
                    updateNotificationCount();
                    updateNotificationsList();
                }, 300);
                
                showToast('Notificación eliminada', 'success');
            } else {
                // Revertir animación si falló
                if (element) {
                    element.style.opacity = '1';
                    element.style.transform = 'translateX(0)';
                }
                showToast(data.message || 'Error al eliminar notificación', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (element) {
                element.style.opacity = '1';
                element.style.transform = 'translateX(0)';
            }
            showToast('Error al eliminar notificación', 'error');
        });
    }

    function markNotificationAsRead(id, element) {
        // Crear FormData
        const formData = new FormData();
        formData.append('id', id);

        fetch(baseUrl + '/app/actions/mark_notification_read.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Actualizar visualmente
                if (element) {
                    element.classList.remove('unread');
                    element.classList.add('read');
                    const unreadIndicator = element.querySelector('.unread-indicator');
                    if (unreadIndicator) {
                        unreadIndicator.remove();
                    }
                }
                updateNotificationCount();
                showToast('Notificación marcada como leída', 'success');
            } else {
                showToast(data.message || 'Error al marcar notificación', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error al marcar notificación', 'error');
        });
    }

    function updateNotificationCount() {
        fetch(baseUrl + '/app/actions/get_notifications_count.php')
            .then(response => response.json())
            .then(data => {
                const countElements = document.querySelectorAll('#notifications-count');
                countElements.forEach(el => {
                    if (data.count > 0) {
                        el.textContent = data.count;
                        el.style.display = 'flex';
                    } else {
                        el.style.display = 'none';
                    }
                });
            })
            .catch(error => console.error('Error:', error));
    }

    function updateNotificationsList() {
        const container = document.querySelector('.notifications-list');
        if (!container) return;

        fetch(baseUrl + '/app/actions/get_notifications.php')
            .then(response => response.json())
            .then(data => {
                if (data.notifications && data.notifications.length === 0) {
                    container.innerHTML = `
                        <div class="empty-state" style="text-align: center; padding: 40px 20px;">
                            <i class="fa fa-bell-slash" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
                            <p style="color: #999; font-size: 14px;">No tienes notificaciones</p>
                        </div>
                    `;
                }
            })
            .catch(error => console.error('Error:', error));
    }

    // ============================================
    // FAVORITOS - AGREGAR/QUITAR EN TIEMPO REAL
    // ============================================
    
    function addToFavorites(productId, button) {
        console.log('❤️ addToFavorites llamada para producto:', productId);
        
        // Animación inmediata del botón
        if (button) {
            button.classList.add('loading');
            button.disabled = true;
        }

        const formData = new FormData();
        formData.append('id_producto', productId);

        fetch(baseUrl + '/app/actions/add_to_favorites.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log('✅ Respuesta de add_to_favorites.php:', data);
            
            if (data.success) {
                // Actualizar TODOS los botones de este producto en la página
                updateFavoriteButtons(productId, data.action === 'added');
                
                // Actualizar contador
                updateFavoritesCount();
                
                // SIEMPRE actualizar el modal completo para reflejar cambios en tiempo real
                console.log('🔄 Actualizando modal de favoritos...');
                refreshFavoritesModal();
                
                // Mostrar notificación apropiada
                if (data.action === 'added') {
                    showToast('Agregado a favoritos', 'success');
                } else {
                    showToast('Quitado de favoritos', 'info');
                }
            } else {
                showToast(data.message || 'Error al procesar favorito', 'error');
            }
        })
        .catch(error => {
            console.error('❌ Error:', error);
            showToast('Error al procesar favorito', 'error');
        })
        .finally(() => {
            if (button) {
                button.classList.remove('loading');
                button.disabled = false;
            }
        });
    }

    function updateFavoriteButtons(productId, isActive) {
        // Actualizar TODOS los botones de favoritos de este producto
        const buttons = document.querySelectorAll(`
            .add-to-favorites[data-id="${productId}"],
            .btn-favorite[data-id="${productId}"],
            .favorite-btn[data-id="${productId}"]
        `);
        
        buttons.forEach(btn => {
            const icon = btn.querySelector('span') || btn.querySelector('i');
            
            // Agregar efecto pulse
            btn.style.animation = 'pulse 0.3s ease';
            setTimeout(() => {
                btn.style.animation = '';
            }, 300);
            
            if (isActive) {
                // Activar
                btn.classList.add('active');
                btn.title = 'Quitar de favoritos';
                if (icon) {
                    // Cambiar icono según el tipo
                    if (icon.classList.contains('icon_heart_alt')) {
                        icon.className = 'icon_heart';
                    } else if (icon.classList.contains('fa-heart-o')) {
                        icon.className = 'fa fa-heart';
                    } else {
                        icon.className = 'icon_heart';
                    }
                }
            } else {
                // Desactivar
                btn.classList.remove('active');
                btn.title = 'Agregar a favoritos';
                if (icon) {
                    // Cambiar icono según el tipo
                    if (icon.classList.contains('icon_heart')) {
                        icon.className = 'icon_heart_alt';
                    } else if (icon.classList.contains('fa-heart')) {
                        icon.className = 'fa fa-heart-o';
                    } else {
                        icon.className = 'icon_heart_alt';
                    }
                }
            }
        });
    }

    function removeFromFavorites(productId, button, listItem) {
        if (button) {
            button.classList.add('loading');
            button.disabled = true;
        }

        // Animación de salida del item
        if (listItem) {
            listItem.style.transition = 'all 0.3s ease';
            listItem.style.opacity = '0';
            listItem.style.transform = 'translateX(-100%)';
        }

        // Usar FormData
        const formData = new FormData();
        formData.append('id_producto', productId);

        fetch(baseUrl + '/app/actions/add_to_favorites.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Actualizar TODOS los botones de este producto en la página
                updateFavoriteButtons(productId, false);
                
                // Actualizar contador
                updateFavoritesCount();
                
                // Eliminar del modal después de la animación
                setTimeout(() => {
                    if (listItem) {
                        listItem.remove();
                    }
                    
                    // Verificar si quedan productos en favoritos
                    const remainingItems = document.querySelectorAll('.favorite-item');
                    if (remainingItems.length === 0) {
                        // Mostrar mensaje de vacío inmediatamente
                        const container = document.querySelector('#favorites-list'); // ID, no clase
                        if (container) {
                            container.innerHTML = `
                                <div class="empty-state" style="text-align: center; padding: 60px 20px;">
                                    <i class="fa fa-heart-o" style="font-size: 80px; margin-bottom: 20px; opacity: 0.3; color: #ccc;"></i>
                                    <p style="font-size: 16px; margin-bottom: 20px; color: #666;">No tienes productos favoritos</p>
                                    <a href="shop.php" style="display: inline-block; padding: 10px 24px; background: #2c3e50 !important; color: white; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 600; transition: all 0.3s ease; box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);">
                                        <i class="fa fa-shopping-bag" style="margin-right: 8px;"></i>Explorar Productos
                                    </a>
                                </div>
                            `;
                        }
                        
                        // Actualizar contador del header
                        const countEl = document.querySelector('.favorites-count');
                        if (countEl) {
                            countEl.textContent = '0 productos';
                        }
                    }
                }, 300);
                
                showToast('Quitado de favoritos', 'success');
            } else {
                // Revertir animación si falló
                if (listItem) {
                    listItem.style.opacity = '1';
                    listItem.style.transform = 'translateX(0)';
                }
                showToast(data.message || 'Error al quitar de favoritos', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (listItem) {
                listItem.style.opacity = '1';
                listItem.style.transform = 'translateX(0)';
            }
            showToast('Error al quitar de favoritos', 'error');
        })
        .finally(() => {
            if (button) {
                button.classList.remove('loading');
                button.disabled = false;
            }
        });
    }

    function updateFavoritesCount() {
        fetch(baseUrl + '/app/actions/get_favorites_count.php')
            .then(response => response.json())
            .then(data => {
                const countElements = document.querySelectorAll('#favorites-count');
                countElements.forEach(el => {
                    if (data.count > 0) {
                        el.textContent = data.count;
                        el.style.display = 'flex';
                    } else {
                        el.style.display = 'none';
                    }
                });
            })
            .catch(error => console.error('Error:', error));
    }

    function updateFavoritesList() {
        const container = document.querySelector('#favorites-list'); // ID, no clase
        if (!container) return;

        fetch(baseUrl + '/app/actions/get_favorites.php')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.count === 0) {
                    container.innerHTML = data.html;
                    
                    // Actualizar contador del header
                    const countEl = document.querySelector('.favorites-count');
                    if (countEl) {
                        countEl.textContent = '0 productos';
                    }
                }
            })
            .catch(error => console.error('Error:', error));
    }

    function refreshFavoritesModal() {
        const container = document.querySelector('#favorites-list');
        if (!container) {
            console.warn('⚠️ Contenedor #favorites-list no encontrado');
            return;
        }

        console.log('🔄 Actualizando modal de favoritos...');

        fetch(baseUrl + '/app/actions/get_favorites.php')
            .then(response => response.json())
            .then(data => {
                console.log('✅ Respuesta de get_favorites.php:', data);
                
                if (data.success) {
                    // Actualizar el HTML del modal usando la respuesta correcta
                    container.innerHTML = data.html;
                    
                    // Actualizar contador en el header del modal
                    const countEl = document.querySelector('.favorites-count');
                    if (countEl) {
                        countEl.textContent = data.count_text || (data.count + (data.count === 1 ? ' producto' : ' productos'));
                    }
                    
                    console.log(`✅ Modal actualizado con ${data.count} productos`);
                } else {
                    console.error('❌ Error en la respuesta:', data.message);
                    showToast('Error al actualizar favoritos', 'error');
                }
            })
            .catch(error => {
                console.error('❌ Error al cargar favoritos:', error);
                showToast('Error al actualizar favoritos', 'error');
            });
    }

    // ============================================
    // CARRITO - AGREGAR EN TIEMPO REAL
    // ============================================
    
    function addToCart(productId, quantity = 1, button) {
        if (button) {
            button.classList.add('loading');
            button.disabled = true;
        }

        // Usar FormData para coincidir con el formato esperado
        const formData = new FormData();
        formData.append('id_producto', productId);
        formData.append('cantidad', quantity);

        fetch(baseUrl + '/app/actions/add_to_cart.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Actualizar contador del carrito
                updateCartCount();
                
                // Actualizar TODOS los botones de carrito de este producto
                updateCartButtons(productId, true);
                
                showToast('Producto agregado al carrito', 'success');
            } else {
                showToast(data.message || 'Error al agregar al carrito', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error al agregar al carrito', 'error');
        })
        .finally(() => {
            if (button) {
                button.classList.remove('loading');
                button.disabled = false;
            }
        });
    }

    function removeFromCart(productId, button) {
        if (button) {
            button.classList.add('loading');
            button.disabled = true;
        }

        // Usar FormData
        const formData = new FormData();
        formData.append('id_producto', productId);

        fetch(baseUrl + '/app/actions/remove_from_cart.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Actualizar contador
                updateCartCount();
                
                // Actualizar TODOS los botones de carrito de este producto
                updateCartButtons(productId, false);
                
                showToast('Producto quitado del carrito', 'info');
            } else {
                showToast(data.message || 'Error al quitar del carrito', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error al quitar del carrito', 'error');
        })
        .finally(() => {
            if (button) {
                button.classList.remove('loading');
                button.disabled = false;
            }
        });
    }

    function updateCartButtons(productId, inCart) {
        // Actualizar TODOS los botones de carrito de este producto en la página
        const buttons = document.querySelectorAll(`
            .add-to-cart[data-id="${productId}"],
            .btn-favorite-cart[data-id="${productId}"],
            .btn-cart[data-id="${productId}"]
        `);
        
        console.log(`🔄 Actualizando botones de carrito para producto ${productId}, inCart: ${inCart}, botones encontrados: ${buttons.length}`);
        
        buttons.forEach(btn => {
            const icon = btn.querySelector('span') || btn.querySelector('i');
            
            // Agregar efecto pulse
            btn.style.animation = 'pulse 0.3s ease';
            setTimeout(() => {
                btn.style.animation = '';
            }, 300);
            
            if (inCart) {
                // Marcar como en carrito
                btn.classList.add('in-cart');
                btn.setAttribute('data-in-cart', 'true');
                btn.dataset.inCart = 'true';
                btn.title = 'Quitar del carrito';
                
                if (icon) {
                    // Cambiar icono según el tipo
                    if (icon.classList.contains('icon_bag_alt')) {
                        icon.className = 'icon_check';
                    } else if (icon.classList.contains('fa-cart-plus')) {
                        icon.className = 'fa fa-check-circle';
                    } else if (icon.classList.contains('fa-shopping-cart')) {
                        icon.className = 'fa fa-check-circle';
                    }
                }
            } else {
                // Marcar como no en carrito
                btn.classList.remove('in-cart');
                btn.setAttribute('data-in-cart', 'false');
                btn.dataset.inCart = 'false';
                btn.title = 'Agregar al carrito';
                
                if (icon) {
                    // Cambiar icono según el tipo
                    if (icon.classList.contains('icon_check')) {
                        icon.className = 'icon_bag_alt';
                    } else if (icon.classList.contains('fa-check-circle')) {
                        // Determinar si es del modal de favoritos o shop
                        if (btn.classList.contains('btn-favorite-cart')) {
                            icon.className = 'fa fa-cart-plus';
                        } else {
                            icon.className = 'icon_bag_alt';
                        }
                    }
                }
            }
            
            console.log(`  ✅ Botón actualizado:`, {
                element: btn.tagName,
                class: btn.className,
                dataInCart: btn.dataset.inCart,
                icon: icon?.className
            });
        });
    }

    function updateCartCount() {
        fetch(baseUrl + '/app/actions/get_cart_count.php')
            .then(response => response.json())
            .then(data => {
                const countElements = document.querySelectorAll('#cart-count');
                countElements.forEach(el => {
                    if (data.count > 0) {
                        el.textContent = data.count;
                        el.style.display = 'flex';
                    } else {
                        el.style.display = 'none';
                    }
                });
                
                // Actualizar también usando la función global si está disponible
                if (typeof window.updateCartCounter === 'function') {
                    window.updateCartCounter(data.count);
                } else if (typeof window.GlobalCounters !== 'undefined' && typeof window.GlobalCounters.updateCart === 'function') {
                    window.GlobalCounters.updateCart(data.count);
                }
                
                console.log('🛒 Contador de carrito actualizado:', data.count);
            })
            .catch(error => console.error('❌ Error al actualizar contador de carrito:', error));
    }

    // ============================================
    // TOAST NOTIFICATIONS
    // ============================================
    
    // ============================================
    // TOAST NOTIFICATIONS - USA LA FUNCIÓN GLOBAL
    // ============================================
    
    function showToast(message, type = 'info') {
        // Usar la función showNotification de cart-favorites-handler.js
        if (typeof window.showNotification === 'function') {
            window.showNotification(message, type);
        } else {
            // Fallback si no está disponible
            console.log(`[${type.toUpperCase()}] ${message}`);
        }
    }

    // ============================================
    // INICIALIZAR EVENT LISTENERS
    // ============================================
    
    function init() {
        console.log('🚀 Real-time-updates.js: Inicializando event listeners...');
        
        // Event delegation para botones dinámicos
        document.addEventListener('click', function(e) {
            // Eliminar notificación
            if (e.target.closest('.delete-notification-btn') || e.target.closest('.btn-notif-delete')) {
                e.preventDefault();
                const btn = e.target.closest('.delete-notification-btn') || e.target.closest('.btn-notif-delete');
                const id = btn.dataset.notificationId || btn.dataset.id || btn.getAttribute('data-id');
                const item = btn.closest('.notification-item');
                deleteNotification(id, item);
            }

            // Marcar notificación como leída
            if (e.target.closest('.mark-read-btn') || e.target.closest('.btn-notif-read')) {
                e.preventDefault();
                const btn = e.target.closest('.mark-read-btn') || e.target.closest('.btn-notif-read');
                const id = btn.dataset.notificationId || btn.dataset.id || btn.getAttribute('data-id');
                const item = btn.closest('.notification-item');
                markNotificationAsRead(id, item);
            }

            // FAVORITOS - Agregar/Quitar desde SHOP (clase: add-to-favorites)
            if (e.target.closest('.add-to-favorites')) {
                console.log('❤️ Click detectado en .add-to-favorites');
                e.preventDefault();
                e.stopPropagation();
                const btn = e.target.closest('.add-to-favorites');
                const productId = btn.dataset.id || btn.getAttribute('data-id');
                
                console.log('💝 Botón de favoritos:', {
                    btn,
                    productId,
                    classes: btn.className
                });
                
                addToFavorites(productId, btn);
            }

            // FAVORITOS - Quitar desde MODAL (clase: btn-favorite-remove)
            if (e.target.closest('.btn-favorite-remove')) {
                e.preventDefault();
                e.stopPropagation();
                const btn = e.target.closest('.btn-favorite-remove');
                const productId = btn.dataset.id || btn.getAttribute('data-id');
                const item = btn.closest('.favorite-item');
                removeFromFavorites(productId, btn, item);
            }

            // CARRITO - Agregar/quitar desde favoritos (clase: btn-favorite-cart)
            if (e.target.closest('.btn-favorite-cart')) {
                e.preventDefault();
                e.stopPropagation();
                const btn = e.target.closest('.btn-favorite-cart');
                const productId = btn.dataset.id || btn.getAttribute('data-id');
                const inCart = btn.dataset.inCart === 'true' || btn.classList.contains('in-cart');
                
                if (inCart) {
                    removeFromCart(productId, btn);
                } else {
                    addToCart(productId, 1, btn);
                }
            }

            // CARRITO - Agregar/Quitar desde SHOP (clase: add-to-cart) - TOGGLE
            if (e.target.closest('.add-to-cart')) {
                console.log('🎯 Click detectado en .add-to-cart');
                e.preventDefault();
                e.stopPropagation();
                const btn = e.target.closest('.add-to-cart');
                const productId = btn.dataset.id || btn.getAttribute('data-id');
                
                console.log('📦 Botón de carrito:', {
                    btn,
                    productId,
                    classes: btn.className,
                    dataId: btn.dataset.id
                });
                
                // No agregar si está deshabilitado
                if (btn.dataset.disabled === 'true') {
                    showToast('Producto sin stock', 'warning');
                    return;
                }
                
                // Toggle: verificar si ya está en carrito
                const inCart = btn.dataset.inCart === 'true' || btn.classList.contains('in-cart');
                
                console.log('🛒 Toggle Carrito:', {
                    productId,
                    inCart,
                    dataInCart: btn.dataset.inCart,
                    hasClass: btn.classList.contains('in-cart')
                });
                
                if (inCart) {
                    removeFromCart(productId, btn);
                } else {
                    const quantity = btn.dataset.quantity || 1;
                    addToCart(productId, quantity, btn);
                }
            }
        });

        console.log('✅ Real-time updates initialized');
    }

    // Agregar estilos para animaciones
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        @keyframes slideInLeft {
            from {
                transform: translateX(-20px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.15);
            }
            100% {
                transform: scale(1);
            }
        }

        .loading {
            position: relative;
            pointer-events: none;
            opacity: 0.6;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 16px;
            height: 16px;
            border: 2px solid #fff;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to { transform: translate(-50%, -50%) rotate(360deg); }
        }
    `;
    document.head.appendChild(style);

    // API Pública
    return {
        init,
        deleteNotification,
        markNotificationAsRead,
        addToFavorites,
        removeFromFavorites,
        addToCart,
        removeFromCart,
        updateNotificationCount,
        updateFavoritesCount,
        updateCartCount,
        showToast,
        refreshFavoritesModal,
        updateFavoritesList
    };
})();

// Inicializar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', RealTimeUpdates.init);
} else {
    RealTimeUpdates.init();
}

// Exportar globalmente
window.RealTimeUpdates = RealTimeUpdates;

// Exportar funciones globales para compatibilidad con otros scripts
window.refreshFavoritesModal = function() {
    if (RealTimeUpdates && RealTimeUpdates.refreshFavoritesModal) {
        RealTimeUpdates.refreshFavoritesModal();
    }
};

window.updateFavoritesList = function() {
    if (RealTimeUpdates && RealTimeUpdates.updateFavoritesList) {
        RealTimeUpdates.updateFavoritesList();
    }
};
