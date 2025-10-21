/**
 * SISTEMA DE ACTUALIZACIÓN GLOBAL DE CONTADORES
 * Mantiene sincronizados los contadores del header en TODAS las páginas
 * Version 1.0 - 2025
 */

(function() {
    'use strict';

    // Sistema de eventos globales para comunicación entre módulos
    const EventBus = {
        events: {},
        
        on: function(event, callback) {
            if (!this.events[event]) {
                this.events[event] = [];
            }
            this.events[event].push(callback);
        },
        
        emit: function(event, data) {
            if (this.events[event]) {
                this.events[event].forEach(callback => callback(data));
            }
        }
    };

    // Exponer EventBus globalmente
    window.EventBus = EventBus;

    // ===== ACTUALIZACIÓN DE CONTADORES =====
    
    /**
     * Actualiza el contador del carrito en el header
     */
    function updateCartCount(count) {
        count = parseInt(count) || 0;
        
        // Actualizar TODOS los elementos del carrito en header y offcanvas
        const selectors = [
            '.header__right__widget a[href*="cart"] .tip',
            '.tip-count', // Selector genérico para tips de carrito
            '#cart-count', // ID específico si existe
            '.offcanvas__cart__links a[href*="cart"] .tip' // Offcanvas menu
        ];
        
        selectors.forEach(selector => {
            document.querySelectorAll(selector).forEach(element => {
                element.textContent = count;
                element.style.display = count > 0 ? 'flex' : 'none';
                
                // Agregar clase para animación
                element.classList.add('updated');
                setTimeout(() => element.classList.remove('updated'), 300);
            });
        });
        
        // Emitir evento global
        EventBus.emit('cartCountUpdated', { count });
        
    }

    /**
     * Actualiza el contador de favoritos en el header
     */
    function updateFavoritesCount(count) {
        count = parseInt(count) || 0;
        
        // Actualizar solo los BADGES (números sin texto) - NO incluir .favorites-count del modal
        const selectors = [
            '.header__right__widget #favorites-link .tip',
            '#favorites-count', // ID específico del badge
            '.offcanvas__cart__links a:has(.icon_heart_alt) .tip' // Offcanvas menu
        ];
        
        selectors.forEach(selector => {
            document.querySelectorAll(selector).forEach(element => {
                element.textContent = count;
                element.style.display = count > 0 ? 'flex' : 'none';
                
                // Agregar clase para animación
                element.classList.add('updated');
                setTimeout(() => element.classList.remove('updated'), 300);
            });
        });
        
        // Actualizar el contador del modal CON TEXTO COMPLETO
        const modalCount = document.querySelector('.favorites-modal-header .favorites-count');
        if (modalCount) {
            const countNumber = modalCount.querySelector('.fav-count-number');
            const countText = count === 1 ? 'producto favorito' : 'productos favoritos';
            
            if (countNumber) {
                countNumber.textContent = count;
            } else {
                modalCount.innerHTML = `<span class="fav-count-number">${count}</span> ${countText}`;
            }
        }
        
        // Emitir evento global
        EventBus.emit('favoritesCountUpdated', { count });
        
    }

    /**
     * Actualiza el contador de notificaciones en el header
     */
    function updateNotificationsCount(count) {
        count = parseInt(count) || 0;
        
        // Actualizar TODOS los elementos de notificaciones
        const selectors = [
            '.header__right__widget #notifications-link .tip',
            '.notifications-count', // Selector genérico
            '#notifications-count', // ID específico si existe
            '.offcanvas__cart__links a:has(.fa-bell) .tip' // Offcanvas menu
        ];
        
        selectors.forEach(selector => {
            document.querySelectorAll(selector).forEach(element => {
                element.textContent = count;
                element.style.display = count > 0 ? 'flex' : 'none';
                
                // Agregar clase para animación
                element.classList.add('updated');
                setTimeout(() => element.classList.remove('updated'), 300);
            });
        });
        
        // Emitir evento global
        EventBus.emit('notificationsCountUpdated', { count });
        
    }

    /**
     * Refresca todos los contadores desde el servidor
     */
    function refreshAllCounters() {
        
        // Obtener BASE_URL sin trailing slash
        const baseUrl = (window.BASE_URL || '').replace(/\/+$/, '');
        
        // Carrito
        fetch(baseUrl + '/app/actions/get_cart_count.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateCartCount(data.count);
                }
            })
            .catch(error => console.error('Error fetching cart count:', error));
        
        // Favoritos
        fetch(baseUrl + '/app/actions/get_favorites_count.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateFavoritesCount(data.count);
                }
            })
            .catch(error => console.error('Error fetching favorites count:', error));
        
        // Notificaciones
        fetch(baseUrl + '/app/actions/get_notifications_count.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateNotificationsCount(data.count);
                }
            })
            .catch(error => console.error('Error fetching notifications count:', error));
    }

    // ===== EXPONER FUNCIONES GLOBALMENTE =====
    window.updateCartCount = updateCartCount;
    window.updateFavoritesCount = updateFavoritesCount;
    window.updateNotificationsCount = updateNotificationsCount;
    window.refreshAllCounters = refreshAllCounters;

    // ===== AUTO-INICIALIZACIÓN =====
    document.addEventListener('DOMContentLoaded', function() {
        
        // Refrescar contadores cada 30 segundos si el usuario está logueado
        if (document.querySelector('#user-account-link')) {
            setInterval(refreshAllCounters, 30000);
        }
        
        // Refrescar cuando la página se vuelve visible
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                refreshAllCounters();
            }
        });
    });

    // ===== ESTILOS PARA ANIMACIÓN DE ACTUALIZACIÓN =====
    const style = document.createElement('style');
    style.textContent = `
        .tip.updated {
            animation: pulse-tip 0.3s ease-out;
        }
        
        @keyframes pulse-tip {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
    `;
    document.head.appendChild(style);

})();
