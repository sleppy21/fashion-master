/**
 * REAL-TIME UPDATES - Sistema de actualización en tiempo real
 * Actualiza modales y productos sin recargar la página
 * @version 1.0
 */

const RealTimeUpdates = (function() {
    'use strict';

    const baseUrl = (window.BASE_URL || '').replace(/\/+$/, '');

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

        fetch(baseUrl + '/app/actions/delete_notification.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Actualizar contador inmediatamente con el valor del servidor
                if (typeof data.notifications_count !== 'undefined') {
                    updateNotificationCountDirectly(data.notifications_count);
                }
                
                // Eliminar elemento del DOM después de la animación
                setTimeout(() => {
                    if (element) {
                        element.remove();
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
                
                // Actualizar contador inmediatamente con el valor del servidor
                if (typeof data.notifications_count !== 'undefined') {
                    updateNotificationCountDirectly(data.notifications_count);
                }
                
                showToast('Notificación marcada como leída', 'success');
            } else {
                showToast(data.message || 'Error al marcar notificación', 'error');
            }
        })
        .catch(error => {
            // console.error('Error:', error);
            showToast('Error al marcar notificación', 'error');
        });
    }

    // Actualizar contador de notificaciones con un valor directo (sin fetch)
    function updateNotificationCountDirectly(count) {
        count = parseInt(count) || 0;
        
        // Actualizar contador en el header (badge)
        const headerCountElements = document.querySelectorAll('#notifications-count');
        headerCountElements.forEach(el => {
            if (count > 0) {
                el.textContent = count;
                el.style.display = 'flex';
            } else {
                el.style.display = 'none';
            }
        });

        // Actualizar contador en el modal
        const modalCountElements = document.querySelectorAll('.notifications-count .notif-count-number');
        modalCountElements.forEach(el => {
            el.textContent = count;
            const countText = count === 1 ? 'notificación' : 'notificaciones';
            el.parentElement.innerHTML = `<span class="notif-count-number">${count}</span> ${countText}`;
        });
    }

    function updateNotificationCount() {
        fetch(baseUrl + '/app/actions/get_notifications_count.php')
            .then(response => response.json())
            .then(data => {
                updateNotificationCountDirectly(data.count);
            })
            .catch(error => {/* console.error('Error:', error); */});
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
            .catch(error => {/* console.error('Error:', error); */});
    }

    // ============================================
    // FAVORITOS - AGREGAR/QUITAR EN TIEMPO REAL
    // ============================================
    
    function addToFavorites(productId, button) {
        
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
            if (data.success) {
                // Actualizar TODOS los botones de este producto en la página
                updateFavoriteButtons(productId, data.action === 'added');
                
                // Actualizar contador con el valor del servidor (más confiable)
                if (typeof data.favorites_count !== 'undefined') {
                    updateFavoritesCountDirectly(data.favorites_count);
                } else {
                    // Fallback: hacer fetch si el servidor no devuelve el count
                    updateFavoritesCount();
                }
                
                // SIEMPRE actualizar el modal completo para reflejar cambios en tiempo real
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
            
            // Agregar efecto heartBeat al ícono
            if (icon) {
                icon.style.animation = 'heartBeat 0.6s ease';
                setTimeout(() => {
                    icon.style.animation = '';
                }, 600);
            }
            
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
                
                // Actualizar contador inmediatamente con el valor del servidor
                if (typeof data.favorites_count !== 'undefined') {
                    updateFavoritesCountDirectly(data.favorites_count);
                }
                
                // Eliminar del modal después de la animación
                setTimeout(() => {
                    if (listItem) {
                        listItem.remove();
                    }
                    
                    // Contar items restantes DESPUÉS de eliminar del DOM
                    const remainingItems = document.querySelectorAll('.favorite-item');
                    const remainingCount = remainingItems.length;
                    
                    if (remainingCount === 0) {
                        // Mostrar mensaje de vacío igual que en PHP
                        const container = document.querySelector('#favorites-list');
                        if (container) {
                            container.innerHTML = `
                                <div class="favorites-empty">
                                    <i class="fa fa-heart-o"></i>
                                    <p>No tienes productos favoritos</p>
                                    <a href="shop.php" class="btn-shop-now">Explorar productos</a>
                                </div>
                            `;
                        }
                    }
                    
                    // Actualizar contador del modal
                    const countEl = document.querySelector('.favorites-count');
                    if (countEl) {
                        const countNumber = countEl.querySelector('.fav-count-number');
                        const countText = remainingCount === 1 ? 'producto favorito' : 'productos favoritos';
                        
                        if (countNumber) {
                            countNumber.textContent = remainingCount;
                        } else {
                            countEl.innerHTML = `<span class="fav-count-number">${remainingCount}</span> ${countText}`;
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

    // Actualizar contador de favoritos con un valor directo (sin fetch)
    function updateFavoritesCountDirectly(count) {
        count = parseInt(count) || 0;
        
        // Actualizar badges del header
        const countElements = document.querySelectorAll('#favorites-count');
        countElements.forEach(el => {
            if (count > 0) {
                el.textContent = count;
                el.style.display = 'flex';
            } else {
                el.style.display = 'none';
            }
        });
        
        // Actualizar contador del modal
        const modalCount = document.querySelector('.favorites-count');
        if (modalCount) {
            const countNumber = modalCount.querySelector('.fav-count-number');
            const countText = count === 1 ? 'producto favorito' : 'productos favoritos';
            
            if (countNumber) {
                countNumber.textContent = count;
            } else {
                modalCount.innerHTML = `<span class="fav-count-number">${count}</span> ${countText}`;
            }
        }
    }

    function updateFavoritesCount() {
        fetch(baseUrl + '/app/actions/get_favorites_count.php')
            .then(response => response.json())
            .then(data => {
                const count = parseInt(data.count) || 0;
                
                // Actualizar badges del header
                const countElements = document.querySelectorAll('#favorites-count');
                countElements.forEach(el => {
                    if (count > 0) {
                        el.textContent = count;
                        el.style.display = 'flex';
                    } else {
                        el.style.display = 'none';
                    }
                });
                
                // Actualizar contador del modal
                const modalCount = document.querySelector('.favorites-count');
                if (modalCount) {
                    const countNumber = modalCount.querySelector('.fav-count-number');
                    const countText = count === 1 ? 'producto favorito' : 'productos favoritos';
                    
                    if (countNumber) {
                        countNumber.textContent = count;
                    } else {
                        modalCount.innerHTML = `<span class="fav-count-number">${count}</span> ${countText}`;
                    }
                }
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
                        const countNumber = countEl.querySelector('.fav-count-number');
                        if (countNumber) {
                            countNumber.textContent = '0';
                        } else {
                            countEl.innerHTML = '<span class="fav-count-number">0</span> productos favoritos';
                        }
                    }
                }
            })
            .catch(error => console.error('Error:', error));
    }

    function refreshFavoritesModal() {
        const container = document.querySelector('#favorites-list');
        fetch(baseUrl + '/app/actions/get_favorites.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Actualizar el HTML del modal usando la respuesta correcta
                    container.innerHTML = data.html;
                    // Actualizar contador en el header del modal
                    const countEl = document.querySelector('.favorites-count');
                    if (countEl) {
                        const countNumber = countEl.querySelector('.fav-count-number');
                        const countText = data.count === 1 ? 'producto favorito' : 'productos favoritos';
                        if (countNumber) {
                            countNumber.textContent = data.count;
                        } else {
                            countEl.innerHTML = `<span class="fav-count-number">${data.count}</span> ${countText}`;
                        }
                    }
                    // --- SINCRONIZAR ESTADO DE BOTONES CARRITO EN MODAL FAVORITOS ---
                    if (Array.isArray(data.cart_ids)) {
                        // Primero, limpiar todos los botones .btn-favorite-cart
                        const allCartBtns = container.querySelectorAll('.btn-favorite-cart');
                        allCartBtns.forEach(btn => {
                            const pid = btn.dataset.id;
                            if (data.cart_ids.includes(pid) || data.cart_ids.includes(parseInt(pid))) {
                                // Marcar como en carrito
                                btn.classList.add('in-cart');
                                btn.setAttribute('data-in-cart', 'true');
                                const icon = btn.querySelector('i');
                                if (icon) icon.className = 'fa fa-check-circle';
                                btn.title = 'En carrito - Clic para quitar';
                            } else {
                                // Marcar como NO en carrito
                                btn.classList.remove('in-cart');
                                btn.setAttribute('data-in-cart', 'false');
                                const icon = btn.querySelector('i');
                                if (icon) icon.className = 'fa fa-cart-plus';
                                btn.title = 'Agregar al carrito';
                            }
                        });
                    }
                } else {
                    showToast('Error al actualizar favoritos', 'error');
                }
            })
            .catch(error => {
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
                // Forzar refresh del modal de favoritos para sincronizar el estado visual
                refreshFavoritesModal();
                showToast('Producto quitado del carrito', 'info');
            } else {
                showToast(data.message || 'Error al quitar del carrito', 'error');
            }
        })
        .catch(error => {
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
            .btn-add-cart-modern[data-id="${productId}"],
            .btn-favorite-cart[data-id="${productId}"],
            .btn-cart[data-id="${productId}"]
        `);

        buttons.forEach(btn => {
            const icon = btn.querySelector('i');
            const iconSpan = btn.querySelector('span.icon_bag_alt, span.icon_check');
            const textSpan = btn.querySelector('span:not([class*="icon"])');

            if (inCart) {
                btn.classList.add('in-cart');
                btn.setAttribute('data-in-cart', 'true');
                btn.dataset.inCart = 'true';

                if (btn.classList.contains('btn-add-cart-modern')) {
                    btn.title = 'Ir al carrito';
                    if (textSpan) {
                        textSpan.textContent = 'Ir al Carrito';
                    } else {
                        const newSpan = document.createElement('span');
                        newSpan.textContent = 'Ir al Carrito';
                        btn.appendChild(newSpan);
                    }
                    if (icon && icon.classList.contains('fa')) {
                        icon.className = 'fa fa-shopping-cart';
                    }
                } else {
                    btn.title = 'Quitar del carrito';
                    // SHOP: iconos tipo span
                    if (iconSpan && iconSpan.classList.contains('icon_bag_alt')) {
                        iconSpan.className = 'icon_check';
                    }
                    // MODAL FAVORITOS: iconos tipo <i>
                    if (btn.classList.contains('btn-favorite-cart') && icon && icon.classList.contains('fa')) {
                        icon.className = 'fa fa-check-circle';
                    }
                }
            } else {
                btn.classList.remove('in-cart');
                btn.setAttribute('data-in-cart', 'false');
                btn.dataset.inCart = 'false';

                if (btn.classList.contains('btn-add-cart-modern')) {
                    btn.title = 'Agregar al carrito';
                    if (textSpan) {
                        textSpan.textContent = 'Agregar al Carrito';
                    } else {
                        const newSpan = document.createElement('span');
                        newSpan.textContent = 'Agregar al Carrito';
                        btn.appendChild(newSpan);
                    }
                    if (icon && icon.classList.contains('fa')) {
                        icon.className = 'fa fa-shopping-cart';
                    }
                } else {
                    btn.title = 'Agregar al carrito';
                    if (iconSpan && iconSpan.classList.contains('icon_check')) {
                        iconSpan.className = 'icon_bag_alt';
                    }
                    // MODAL FAVORITOS: iconos tipo <i>
                    if (btn.classList.contains('btn-favorite-cart') && icon && icon.classList.contains('fa')) {
                        icon.className = 'fa fa-cart-plus';
                    }
                }
            }
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
                
            })
            .catch(error => {/* console.error('❌ Error al actualizar contador de carrito:', error); */});
    }

    // ============================================
    // TOAST NOTIFICATIONS
    // ============================================
    
    // ============================================
    // TOAST NOTIFICATIONS - USA LA FUNCIÓN GLOBAL
    // ============================================
    
    function showToast(message, type = 'info') {

        window.showNotification(message, type);
    }

    // ============================================
    // INICIALIZAR EVENT LISTENERS
    // ✅ FIX: Event delegation OPTIMIZADO - verificar SOLO si el click es relevante
    // ============================================
    
    function init() {
        
        // ✅ Event delegation OPTIMIZADO: Salir temprano si no es click relevante
        document.addEventListener('click', function(e) {
            const target = e.target;
            
            // ✅ OPTIMIZACIÓN: Si no es un elemento interactivo, salir inmediatamente
            if (!target.closest('button, a, .notification-item, .favorite-item')) {
                return; // Salir temprano para evitar 5000+ verificaciones innecesarias
            }
            
            // Ahora sí, verificar qué tipo de elemento es
            const clickedElement = target.closest(
                '.delete-notification-btn, .btn-notif-delete, ' +
                '.mark-read-btn, .btn-notif-read, ' +
                '.add-to-favorites, ' +
                '.btn-favorite-remove, ' +
                '.btn-favorite-cart, ' +
                '.btn-add-cart-modern, ' +
                '.add-to-cart'
            );
            
            if (!clickedElement) return; // No es ningún botón que nos interese
            
            e.preventDefault();
            e.stopPropagation();
            
            // ✅ OPTIMIZACIÓN: Usar el elemento ya encontrado en lugar de buscar otra vez
            const btn = clickedElement;
            const productId = btn.dataset.id || btn.getAttribute('data-id');
            
            // Determinar qué tipo de acción ejecutar basado en la clase
            if (btn.matches('.delete-notification-btn, .btn-notif-delete')) {
                const id = btn.dataset.notificationId || productId;
                const item = btn.closest('.notification-item');
                deleteNotification(id, item);
            }
            else if (btn.matches('.mark-read-btn, .btn-notif-read')) {
                const id = btn.dataset.notificationId || productId;
                const item = btn.closest('.notification-item');
                markNotificationAsRead(id, item);
            }
            else if (btn.matches('.add-to-favorites')) {
                addToFavorites(productId, btn);
            }
            else if (btn.matches('.btn-favorite-remove')) {
                const item = btn.closest('.favorite-item');
                removeFromFavorites(productId, btn, item);
            }
            else if (btn.matches('.btn-favorite-cart')) {
                const inCart = btn.dataset.inCart === 'true' || btn.classList.contains('in-cart');
                if (inCart) {
                    removeFromCart(productId, btn);
                } else {
                    addToCart(productId, 1, btn);
                }
            }
            else if (btn.matches('.btn-add-cart-modern')) {
                if (btn.disabled || btn.dataset.disabled === 'true') {
                    showToast('Producto sin stock', 'warning');
                    return;
                }
                
                const inCart = btn.dataset.inCart === 'true' || btn.classList.contains('in-cart');
                if (inCart) {
                    window.location.href = baseUrl + '/cart.php';
                } else {
                    const quantity = btn.dataset.quantity || 1;
                    addToCart(productId, quantity, btn);
                }
            }
            else if (btn.matches('.add-to-cart')) {
                if (!productId || productId === 'null' || productId === 'undefined') {
                    showToast('Error: ID de producto no válido', 'error');
                    return;
                }
                
                if (btn.dataset.disabled === 'true') {
                    showToast('Producto sin stock', 'warning');
                    return;
                }
                
                const inCart = btn.dataset.inCart === 'true' || btn.classList.contains('in-cart');
                if (inCart) {
                    removeFromCart(productId, btn);
                } else {
                    const quantity = btn.dataset.quantity || 1;
                    addToCart(productId, quantity, btn);
                }
            }
        });

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
        updateNotificationCountDirectly,
        updateFavoritesCount,
        updateFavoritesCountDirectly,
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
