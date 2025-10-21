/**
 * AJAX COUNTERS AUTO-REFRESH
 * Actualiza contadores en tiempo real sin recargar página
 * @version 2.0 - Octubre 2025
 */

(function() {
    'use strict';
    
    const REFRESH_INTERVAL = 30000; // 30 segundos
    const BASE_URL = window.location.origin + '/fashion-master';
    let refreshTimer = null;
    
    /**
     * Actualiza contadores mediante AJAX
     */
    async function refreshCounters() {
        try {
            const response = await fetch(`${BASE_URL}/app/ajax/get-modal-data.php?modal=counters&t=${Date.now()}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Cache-Control': 'no-cache'
                }
            });
            
            // Si es 401 (no autorizado), el usuario no está logueado - no es un error
            if (response.status === 401) {
                stopAutoRefresh(); // Detener actualizaciones automáticas
                return;
            }
            
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            
            const data = await response.json();
            
            if (data.success) {
                updateCounter('cart', data.cart_count || 0);
                updateCounter('favorites', data.favorites_count || 0);
                updateCounter('notifications', data.notifications_unread || 0);
                
            }
            
        } catch (error) {
        }
    }
    
    /**
     * Actualiza un contador específico con animación
     */
    function updateCounter(type, count) {
        const selectors = {
            cart: '#cart-count',
            favorites: '#favorites-count',
            notifications: '#notifications-count'
        };
        
        const selector = selectors[type];
        if (!selector) return;
        
        const el = document.querySelector(selector);
        if (!el) return;
        
        const currentCount = parseInt(el.textContent) || 0;
        
        if (currentCount !== count) {
            el.style.transform = 'scale(1.3)';
            el.textContent = count;
            
            // Mostrar u ocultar según el conteo
            if (count > 0) {
                el.style.display = '';
            } else {
                el.style.display = 'none';
            }
            
            setTimeout(() => { 
                el.style.transform = 'scale(1)'; 
            }, 200);
        }
    }
    
    /**
     * Inicia auto-refresh
     */
    function startAutoRefresh() {
        if (refreshTimer) clearInterval(refreshTimer);
        refreshTimer = setInterval(refreshCounters, REFRESH_INTERVAL);
    }
    
    /**
     * Detiene auto-refresh
     */
    function stopAutoRefresh() {
        if (refreshTimer) {
            clearInterval(refreshTimer);
            refreshTimer = null;
        }
    }
    
    // Inicializar
    function init() {
        setTimeout(refreshCounters, 1000);
        startAutoRefresh();
        
        // Refrescar al cambiar de pestaña
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) refreshCounters();
        });
    }
    
    // Exponer globalmente
    window.refreshCounters = refreshCounters;
    window.startCountersAutoRefresh = startAutoRefresh;
    window.stopCountersAutoRefresh = stopAutoRefresh;
    
    // Ejecutar al cargar
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
})();
