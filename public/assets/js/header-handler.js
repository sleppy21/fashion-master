/**
 * HEADER HANDLER - Controlador unificado del header
 * Maneja actualización en tiempo real de contadores
 * Compatible con todas las páginas
 * Versión: 1.0
 * Fecha: 2025-10-14
 */

(function() {
    'use strict';
    
    console.log('🎯 Header Handler inicializando...');
    
    // ============================================
    // ACTUALIZAR CONTADOR DE CARRITO
    // ============================================
    function updateCartCount() {
        fetch('app/actions/get_cart_count.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const count = parseInt(data.count) || 0;
                    
                    // Actualizar todos los badges de carrito
                    document.querySelectorAll('.cart-count, #cart-count').forEach(el => {
                        el.textContent = count;
                        
                        // Mostrar/ocultar badge
                        if (count > 0) {
                            el.style.display = 'inline-flex';
                        } else {
                            el.style.display = 'none';
                        }
                    });
                    
                    console.log('🛒 Carrito actualizado:', count);
                }
            })
            .catch(error => {
                console.error('❌ Error al actualizar carrito:', error);
            });
    }
    
    // ============================================
    // ACTUALIZAR CONTADOR DE FAVORITOS
    // ============================================
    function updateFavoritesCount() {
        fetch('app/actions/get_favorites_count.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const count = parseInt(data.count) || 0;
                    
                    // Actualizar todos los badges de favoritos
                    document.querySelectorAll('.favorites-count, #favorites-count').forEach(el => {
                        el.textContent = count;
                        
                        // Mostrar/ocultar badge
                        if (count > 0) {
                            el.style.display = 'inline-flex';
                        } else {
                            el.style.display = 'none';
                        }
                    });
                    
                    console.log('❤️ Favoritos actualizado:', count);
                }
            })
            .catch(error => {
                console.error('❌ Error al actualizar favoritos:', error);
            });
    }
    
    // ============================================
    // ACTUALIZAR CONTADOR DE NOTIFICACIONES
    // ============================================
    function updateNotificationsCount() {
        fetch('app/actions/get_notifications_count.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const count = parseInt(data.count) || 0;
                    
                    // Actualizar todos los badges de notificaciones
                    document.querySelectorAll('.notifications-count, #notifications-count').forEach(el => {
                        el.textContent = count;
                        
                        // Mostrar/ocultar badge
                        if (count > 0) {
                            el.style.display = 'inline-flex';
                        } else {
                            el.style.display = 'none';
                        }
                    });
                    
                    console.log('🔔 Notificaciones actualizado:', count);
                }
            })
            .catch(error => {
                console.error('❌ Error al actualizar notificaciones:', error);
            });
    }
    
    // ============================================
    // ACTUALIZAR TODOS LOS CONTADORES
    // ============================================
    function updateAllCounters() {
        updateCartCount();
        updateFavoritesCount();
        updateNotificationsCount();
    }
    
    // ============================================
    // ESCUCHAR EVENTOS PERSONALIZADOS
    // ============================================
    
    // Evento: Producto agregado al carrito
    document.addEventListener('cartUpdated', function() {
        console.log('🎯 Evento cartUpdated detectado');
        updateCartCount();
    });
    
    // Evento: Favorito agregado/eliminado
    document.addEventListener('favoritesUpdated', function() {
        console.log('🎯 Evento favoritesUpdated detectado');
        updateFavoritesCount();
    });
    
    // Evento: Notificación nueva
    document.addEventListener('notificationsUpdated', function() {
        console.log('🎯 Evento notificationsUpdated detectado');
        updateNotificationsCount();
    });
    
    // Evento: Actualización general
    document.addEventListener('headerUpdate', function() {
        console.log('🎯 Evento headerUpdate detectado');
        updateAllCounters();
    });
    
    // ============================================
    // OBSERVADOR DE MUTACIONES (para cart.php)
    // ============================================
    
    // Observar cambios en la tabla del carrito
    const cartTable = document.querySelector('.cart__table');
    if (cartTable) {
        const observer = new MutationObserver(function() {
            console.log('🔄 Tabla del carrito modificada, actualizando contador...');
            setTimeout(updateCartCount, 500);
        });
        
        observer.observe(cartTable, {
            childList: true,
            subtree: true
        });
    }
    
    // ============================================
    // INTERCEPTAR FORMULARIOS DE AGREGAR AL CARRITO
    // ============================================
    
    document.addEventListener('submit', function(e) {
        // Detectar formularios de agregar al carrito
        if (e.target.matches('form[action*="add_to_cart"]') || 
            e.target.matches('.add-to-cart-form') ||
            e.target.id === 'addToCartForm') {
            
            console.log('📦 Formulario de carrito detectado, actualizará después del envío');
            
            // Actualizar después de 1 segundo
            setTimeout(updateCartCount, 1000);
        }
        
        // Detectar formularios de favoritos
        if (e.target.matches('form[action*="add_to_favorites"]') || 
            e.target.matches('.add-to-favorites-form')) {
            
            console.log('❤️ Formulario de favoritos detectado, actualizará después del envío');
            
            // Actualizar después de 1 segundo
            setTimeout(updateFavoritesCount, 1000);
        }
    });
    
    // ============================================
    // INTERCEPTAR CLICKS EN BOTONES
    // ============================================
    
    document.addEventListener('click', function(e) {
        // Botón de agregar al carrito
        if (e.target.matches('.add-to-cart') || 
            e.target.closest('.add-to-cart')) {
            
            console.log('🛒 Botón agregar al carrito clickeado');
            setTimeout(updateCartCount, 1000);
        }
        
        // Botón de favoritos
        if (e.target.matches('.add-to-favorites') || 
            e.target.closest('.add-to-favorites') ||
            e.target.matches('.btn-favorite') ||
            e.target.closest('.btn-favorite')) {
            
            console.log('❤️ Botón favoritos clickeado');
            setTimeout(updateFavoritesCount, 1000);
        }
        
        // Botón de eliminar del carrito
        if (e.target.matches('.remove-from-cart') || 
            e.target.closest('.remove-from-cart') ||
            e.target.matches('[data-action="remove-from-cart"]')) {
            
            console.log('🗑️ Botón eliminar del carrito clickeado');
            setTimeout(updateCartCount, 1000);
        }
    });
    
    // ============================================
    // ACTUALIZACIÓN PERIÓDICA (cada 30 segundos)
    // ============================================
    
    setInterval(function() {
        console.log('🔄 Actualización periódica de contadores...');
        updateAllCounters();
    }, 30000); // 30 segundos
    
    // ============================================
    // ACTUALIZACIÓN INICIAL
    // ============================================
    
    // Actualizar al cargar la página
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', updateAllCounters);
    } else {
        updateAllCounters();
    }
    
    // ============================================
    // EXPONER FUNCIONES GLOBALMENTE
    // ============================================
    
    window.updateCartCount = updateCartCount;
    window.updateFavoritesCount = updateFavoritesCount;
    window.updateNotificationsCount = updateNotificationsCount;
    window.updateAllCounters = updateAllCounters;
    
    console.log('✅ Header Handler inicializado correctamente');
    
})();
