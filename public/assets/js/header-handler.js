/**/**

 * HEADER HANDLER - ULTRA SIMPLIFICADO * HEADER HANDLER - ULTRA SIMPLIFICADO

 * Solo actualiza contadores cuando hay eventos EXPLÍCITOS * Solo actualiza contadores cuando hay eventos EXPLÍCITOS

 * SIN actualizaciones periódicas, SIN observadores, SIN evaluaciones * SIN actualizaciones periódicas, SIN observadores, SIN evaluaciones

 */ */



(function() {(function() {

    'use strict';    'use strict';

        

    // Actualizar contador de carrito    // Actualizar contador de carrito (sin verificaciones, directo)

    function updateCartCount() {    function updateCartCount() {

        fetch('app/actions/get_cart_count.php')        fetch('app/actions/get_cart_count.php')

            .then(response => response.json())            .then(response => response.json())

            .then(data => {            .then(data => {

                if (data.success) {                if (data.success) {

                    const count = parseInt(data.count) || 0;                    const count = parseInt(data.count) || 0;

                    document.querySelectorAll('.cart-count, #cart-count').forEach(el => {                    document.querySelectorAll('.cart-count, #cart-count').forEach(el => {

                        el.textContent = count;                        el.textContent = count;

                        el.style.display = count > 0 ? 'inline-flex' : 'none';                        el.style.display = count > 0 ? 'inline-flex' : 'none';

                    });                    });

                }                }

            })            })

            .catch(() => {});            .catch(() => {}); // Silenciar errores

    }    }

        

    // Actualizar contador de favoritos    // Actualizar contador de favoritos (sin verificaciones, directo)

    function updateFavoritesCount() {    function updateFavoritesCount() {

        fetch('app/actions/get_favorites_count.php')        fetch('app/actions/get_favorites_count.php')

            .then(response => response.json())            .then(response => response.json())

            .then(data => {            .then(data => {

                if (data.success) {                if (data.success) {

                    const count = parseInt(data.count) || 0;                    const count = parseInt(data.count) || 0;

                    document.querySelectorAll('#favorites-count').forEach(el => {                    document.querySelectorAll('#favorites-count').forEach(el => {

                        el.textContent = count;                        el.textContent = count;

                        el.style.display = count > 0 ? 'inline-flex' : 'none';                        el.style.display = count > 0 ? 'inline-flex' : 'none';

                    });                    });

                }                }

            })            })

            .catch(() => {});            .catch(() => {}); // Silenciar errores

    }    }

        

    // Actualizar contador de notificaciones    // Actualizar contador de notificaciones (sin verificaciones, directo)

    function updateNotificationsCount() {    function updateNotificationsCount() {

        fetch('app/actions/get_notifications_count.php')        fetch('app/actions/get_notifications_count.php')

            .then(response => response.json())            .then(response => response.json())

            .then(data => {            .then(data => {

                if (data.success) {                if (data.success) {

                    const count = parseInt(data.count) || 0;                    const count = parseInt(data.count) || 0;

                    document.querySelectorAll('.notifications-count, #notifications-count').forEach(el => {                    document.querySelectorAll('.notifications-count, #notifications-count').forEach(el => {

                        el.textContent = count;                        el.textContent = count;

                        el.style.display = count > 0 ? 'inline-flex' : 'none';                        el.style.display = count > 0 ? 'inline-flex' : 'none';

                    });                    });

                }                }

            })            })

            .catch(() => {});            .catch(() => {}); // Silenciar errores

    }    }

        

    // Actualizar todos    // Actualizar todos los contadores

    function updateAllCounters() {    function updateAllCounters() {

        updateCartCount();        updateCartCount();

        updateFavoritesCount();        updateFavoritesCount();

        updateNotificationsCount();        updateNotificationsCount();

    }    }

        

    // Solo eventos personalizados    // EVENTOS PERSONALIZADOS (solo escuchar, no evaluar)

    document.addEventListener('cartUpdated', updateCartCount);    document.addEventListener('cartUpdated', updateCartCount);

    document.addEventListener('favoritesUpdated', updateFavoritesCount);    document.addEventListener('favoritesUpdated', updateFavoritesCount);

    document.addEventListener('notificationsUpdated', updateNotificationsCount);    document.addEventListener('notificationsUpdated', updateNotificationsCount);

    document.addEventListener('headerUpdate', updateAllCounters);    document.addEventListener('headerUpdate', updateAllCounters);

        

    // Actualizar UNA SOLA VEZ al cargar    // ELIMINAR: MutationObserver

    if (document.readyState === 'loading') {    // ELIMINAR: Actualización periódica

        document.addEventListener('DOMContentLoaded', updateAllCounters, { once: true });    // ELIMINAR: Interceptores de formularios

    } else {    // ELIMINAR: Interceptores de clicks

        updateAllCounters();    

    }    // Actualizar SOLO al cargar la página (una sola vez)

        if (document.readyState === 'loading') {

    // Exponer globalmente        document.addEventListener('DOMContentLoaded', updateAllCounters, { once: true });

    window.updateCartCount = updateCartCount;    } else {

    window.updateFavoritesCount = updateFavoritesCount;        updateAllCounters();

    window.updateNotificationsCount = updateNotificationsCount;    }

    window.updateAllCounters = updateAllCounters;    

        // Exponer funciones globalmente (para llamadas manuales)

})();    window.updateCartCount = updateCartCount;

    window.updateFavoritesCount = updateFavoritesCount;
    window.updateNotificationsCount = updateNotificationsCount;
    window.updateAllCounters = updateAllCounters;
    
})();
    
    // ============================================
    // ACTUALIZAR CONTADOR DE CARRITO (SIN REFLOW)
    // ============================================
    function updateCartCount() {
        fetch('app/actions/get_cart_count.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const count = parseInt(data.count) || 0;
                    
                    // Actualizar SOLO si el valor cambió (evitar repaints innecesarios)
                    document.querySelectorAll('.cart-count, #cart-count').forEach(el => {
                        const currentCount = parseInt(el.textContent) || 0;
                        
                        // Solo actualizar si cambió el valor
                        if (currentCount !== count) {
                            // Usar requestAnimationFrame para evitar bloqueo visual
                            requestAnimationFrame(() => {
                                el.textContent = count;
                                el.style.display = count > 0 ? 'inline-flex' : 'none';
                            });
                        }
                    });
                    
                    // Log solo si cambió
                    const currentTotal = parseInt(document.querySelector('.cart-count, #cart-count')?.textContent) || 0;
                    if (currentTotal !== count) {
                    }
                }
            })
            .catch(error => {
                // Silenciar errores para evitar spam en consola
            });
    }
    
    // ============================================
    // ACTUALIZAR CONTADOR DE FAVORITOS (SIN REFLOW)
    // ============================================
    function updateFavoritesCount() {
        fetch('app/actions/get_favorites_count.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const count = parseInt(data.count) || 0;
                    
                    // Actualizar badges del header SOLO si cambió
                    document.querySelectorAll('#favorites-count').forEach(el => {
                        const currentCount = parseInt(el.textContent) || 0;
                        
                        if (currentCount !== count) {
                            requestAnimationFrame(() => {
                                el.textContent = count;
                                el.style.display = count > 0 ? 'inline-flex' : 'none';
                            });
                        }
                    });
                    
                    // Actualizar contador del modal SOLO si cambió
                    const modalCount = document.querySelector('.favorites-count');
                    if (modalCount) {
                        const currentModalCount = parseInt(modalCount.querySelector('.fav-count-number')?.textContent) || 0;
                        
                        if (currentModalCount !== count) {
                            requestAnimationFrame(() => {
                                const countNumber = modalCount.querySelector('.fav-count-number');
                                const countText = count === 1 ? 'producto favorito' : 'productos favoritos';
                                
                                if (countNumber) {
                                    countNumber.textContent = count;
                                } else {
                                    modalCount.innerHTML = `<span class="fav-count-number">${count}</span> ${countText}`;
                                }
                            });
                        }
                    }
                }
            })
            .catch(error => {
                // Silenciar errores
            });
    }
    
    // ============================================
    // ACTUALIZAR CONTADOR DE NOTIFICACIONES (SIN REFLOW)
    // ============================================
    function updateNotificationsCount() {
        fetch('app/actions/get_notifications_count.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const count = parseInt(data.count) || 0;
                    
                    // Actualizar SOLO si cambió
                    document.querySelectorAll('.notifications-count, #notifications-count').forEach(el => {
                        const currentCount = parseInt(el.textContent) || 0;
                        
                        if (currentCount !== count) {
                            requestAnimationFrame(() => {
                                el.textContent = count;
                                el.style.display = count > 0 ? 'inline-flex' : 'none';
                            });
                        }
                    });
                }
            })
            .catch(error => {
                // Silenciar errores
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
    // ESCUCHAR EVENTOS PERSONALIZADOS (silencioso)
    // ============================================
    
    // Evento: Producto agregado al carrito
    document.addEventListener('cartUpdated', function() {
        updateCartCount();
    });
    
    // Evento: Favorito agregado/eliminado
    document.addEventListener('favoritesUpdated', function() {
        updateFavoritesCount();
    });
    
    // Evento: Notificación nueva
    document.addEventListener('notificationsUpdated', function() {
        updateNotificationsCount();
    });
    
    // Evento: Actualización general
    document.addEventListener('headerUpdate', function() {
        updateAllCounters();
    });
    
    // ============================================
    // OBSERVADOR DE MUTACIONES (DESHABILITADO - causa reflows)
    // ============================================
    
    // ELIMINADO: MutationObserver del carrito
    // Causa actualizaciones constantes y refrescos visuales
    // Las actualizaciones se hacen solo por eventos específicos
    
    // ============================================
    // INTERCEPTAR FORMULARIOS DE AGREGAR AL CARRITO
    // ============================================
    
    document.addEventListener('submit', function(e) {
        // Detectar formularios de agregar al carrito
        if (e.target.matches('form[action*="add_to_cart"]') || 
            e.target.matches('.add-to-cart-form') ||
            e.target.id === 'addToCartForm') {
            
            // Actualizar después de 1 segundo (solo una vez)
            setTimeout(updateCartCount, 1000);
        }
        
        // Detectar formularios de favoritos
        if (e.target.matches('form[action*="add_to_favorites"]') || 
            e.target.matches('.add-to-favorites-form')) {
            
            // Actualizar después de 1 segundo (solo una vez)
            setTimeout(updateFavoritesCount, 1000);
        }
    });
    
    // ============================================
    // INTERCEPTAR CLICKS EN BOTONES (silencioso)
    // ============================================
    
    document.addEventListener('click', function(e) {
        // Botón de agregar al carrito
        if (e.target.matches('.add-to-cart') || 
            e.target.closest('.add-to-cart')) {
            
            setTimeout(updateCartCount, 1000);
        }
        
        // Botón de favoritos
        if (e.target.matches('.add-to-favorites') || 
            e.target.closest('.add-to-favorites') ||
            e.target.matches('.btn-favorite') ||
            e.target.closest('.btn-favorite')) {
            
            setTimeout(updateFavoritesCount, 1000);
        }
        
        // Botón de eliminar del carrito
        if (e.target.matches('.remove-from-cart') || 
            e.target.closest('.remove-from-cart') ||
            e.target.matches('[data-action="remove-from-cart"]')) {
            
            setTimeout(updateCartCount, 1000);
        }
    });
    
    // ============================================
    // ACTUALIZACIÓN PERIÓDICA (cada 5 MINUTOS - reducido para evitar refrescos)
    // ============================================
    
    setInterval(function() {
        // Actualización silenciosa en segundo plano
        updateAllCounters();
    }, 300000); // 5 minutos (300,000 ms) - era 30 segundos
    
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
    
    // Log final silencioso
    
})();
