/**
 * HEADER HANDLER - Actualización de contadores
 * Actualiza contadores del header cuando hay eventos
 */

(function() {
    'use strict';
    
    // Obtener BASE_URL
    const getBaseUrl = () => (window.BASE_URL || '').replace(/\/+$/, '');
    
    // Actualizar contador de carrito
    function updateCartCount() {
        const baseUrl = getBaseUrl();
        fetch(baseUrl + '/app/actions/get_cart_count.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const count = parseInt(data.count) || 0;
                    document.querySelectorAll('.cart-count, #cart-count').forEach(el => {
                        el.textContent = count;
                        el.style.display = count > 0 ? 'inline-flex' : 'none';
                    });
                }
            })
            .catch(() => {});
    }
    
    // Actualizar contador de favoritos
    function updateFavoritesCount() {
        const baseUrl = getBaseUrl();
        fetch(baseUrl + '/app/actions/get_favorites_count.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const count = parseInt(data.count) || 0;
                    document.querySelectorAll('#favorites-count').forEach(el => {
                        el.textContent = count;
                        el.style.display = count > 0 ? 'inline-flex' : 'none';
                    });
                }
            })
            .catch(() => {});
    }
    
    // Actualizar contador de notificaciones
    function updateNotificationsCount() {
        const baseUrl = getBaseUrl();
        fetch(baseUrl + '/app/actions/get_notifications_count.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const count = parseInt(data.count) || 0;
                    document.querySelectorAll('.notifications-count, #notifications-count').forEach(el => {
                        el.textContent = count;
                        el.style.display = count > 0 ? 'inline-flex' : 'none';
                    });
                }
            })
            .catch(() => {});
    }
    
    // Actualizar todos los contadores
    function updateAllCounters() {
        updateCartCount();
        updateFavoritesCount();
        updateNotificationsCount();
    }
    
    // Eventos personalizados
    document.addEventListener('cartUpdated', updateCartCount);
    document.addEventListener('favoritesUpdated', updateFavoritesCount);
    document.addEventListener('notificationsUpdated', updateNotificationsCount);
    document.addEventListener('headerUpdate', updateAllCounters);
    
    // Actualizar al cargar la página
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', updateAllCounters, { once: true });
    } else {
        updateAllCounters();
    }
    
    // Exponer funciones globalmente
    window.updateCartCount = updateCartCount;
    window.updateFavoritesCount = updateFavoritesCount;
    window.updateNotificationsCount = updateNotificationsCount;
    window.updateAllCounters = updateAllCounters;
    
})();
