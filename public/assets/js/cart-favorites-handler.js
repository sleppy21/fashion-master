/**
 * CART AND FAVORITES HANDLER
 * Maneja la funcionalidad de carrito y favoritos
 */

(function() {
    'use strict';

    // ===== AGREGAR AL CARRITO =====
    function initCartButtons() {
        // DESHABILITADO - Ahora usa real-time-updates.js con toggle
        console.log('cart-favorites-handler.js: Carrito manejado por real-time-updates.js (toggle)');
        return;
        
        // No inicializar en product-details.php (tiene su propio handler)
        if (window.location.pathname.includes('product-details.php')) {
            console.log('cart-favorites-handler.js: Saltando inicialización en product-details.php');
            return;
        }
        
        const cartButtons = document.querySelectorAll('.add-to-cart');
        
        cartButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation(); // Evitar que dispare el click de la imagen
                
                // Verificar si está deshabilitado
                if (this.hasAttribute('data-disabled')) {
                    return false;
                }
                
                const productId = this.getAttribute('data-id');
                addToCart(productId);
            });
        });
    }

    function addToCart(productId) {
        // Obtener cantidad del input si existe (para product-details.php)
        let cantidad = 1;
        const quantityInput = document.getElementById('product-quantity');
        if (quantityInput) {
            cantidad = parseInt(quantityInput.value) || 0;
            
            // Validar que la cantidad sea mayor a 0
            if (cantidad <= 0) {
                showNotification('Por favor selecciona una cantidad mayor a 0', 'warning');
                return;
            }
        }
        
        // Mostrar loading
        showNotification('Agregando al carrito...', 'info');
        
        // Obtener BASE_URL y limpiar barras duplicadas
        const baseUrl = (window.BASE_URL || '').replace(/\/+$/, '');

        fetch(baseUrl + '/app/actions/add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id_producto=${productId}&cantidad=${cantidad}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                updateCartCount(data.cart_count);
                
                // Agregar animación al icono del carrito
                animateCartIcon();
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error al agregar al carrito', 'error');
        });
    }

    function updateCartCount(count) {
        // Usar el sistema global si está disponible
        if (typeof window.updateCartCount === 'function') {
            window.updateCartCount(count);
        } else {
            // Fallback legacy
            const cartTip = document.querySelector('.header__right__widget a[href*="cart"] .tip');
            if (cartTip) {
                cartTip.textContent = count;
                cartTip.style.display = count > 0 ? 'block' : 'none';
            }
        }
    }

    function animateCartIcon() {
        const cartIcon = document.querySelector('.header__right__widget a[href*="cart"] span');
        if (cartIcon) {
            cartIcon.style.transform = 'scale(1.3)';
            setTimeout(() => {
                cartIcon.style.transform = 'scale(1)';
            }, 300);
        }
    }

    // ===== AGREGAR/QUITAR DE FAVORITOS =====
    function initFavoriteButtons() {
        // DESHABILITADO - Ahora usa real-time-updates.js
        console.log('cart-favorites-handler.js: Favoritos manejados por real-time-updates.js');
        return;
        
        // No inicializar en product-details.php (tiene su propio handler)
        if (window.location.pathname.includes('product-details.php')) {
            console.log('cart-favorites-handler.js: Saltando inicialización de favoritos en product-details.php');
            return;
        }
        
        const favoriteButtons = document.querySelectorAll('.add-to-favorites');
        
        favoriteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation(); // Evitar que dispare el click de la imagen
                
                const productId = this.getAttribute('data-id');
                toggleFavorite(productId, this);
            });
        });
    }

    function toggleFavorite(productId, button) {
        // Obtener BASE_URL y limpiar barras duplicadas
        const baseUrl = (window.BASE_URL || '').replace(/\/+$/, '');
        fetch(baseUrl + '/app/actions/add_to_favorites.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id_producto=${productId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                updateFavoritesCount(data.favorites_count);
                
                // Cambiar icono según la acción
                const icon = button.querySelector('span');
                if (data.action === 'added') {
                    icon.classList.remove('icon_heart_alt');
                    icon.classList.add('icon_heart');
                    button.classList.add('active');
                } else {
                    icon.classList.remove('icon_heart');
                    icon.classList.add('icon_heart_alt');
                    button.classList.remove('active');
                }
                
                // Animar icono
                animateFavoriteIcon(button);
                
                // ACTUALIZAR MODAL DE FAVORITOS EN TIEMPO REAL
                reloadFavoritesModal();
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error al procesar favoritos', 'error');
        });
    }

    // Nueva función para recargar el modal de favoritos
    function reloadFavoritesModal() {
        const modalBody = document.querySelector('#favorites-list');
        if (!modalBody) {
            console.warn('⚠️ Modal de favoritos no encontrado');
            return;
        }

        console.log('🔄 Recargando modal de favoritos...');
        
        // NO mostrar loading, solo actualizar silenciosamente
        const baseUrl = (window.BASE_URL || '').replace(/\/+$/, '');
        fetch(baseUrl + '/app/actions/get_favorites.php')
            .then(response => response.json())
            .then(data => {
                console.log('✅ Respuesta get_favorites:', data);
                
                if (data.success) {
                    // Actualizar el HTML del modal
                    modalBody.innerHTML = data.html;
                    
                    // Actualizar el contador de productos en el header del modal
                    const favCount = document.querySelector('.favorites-count');
                    if (favCount) {
                        favCount.textContent = data.count_text || (data.count + (data.count === 1 ? ' producto' : ' productos'));
                    }
                    
                    console.log(`✅ Modal actualizado con ${data.count} productos`);
                } else {
                    console.error('❌ Error al cargar favoritos:', data.message);
                }
            })
            .catch(error => {
                console.error('❌ Error:', error);
            });
    }

    function updateFavoritesCount(count) {
        // Usar el sistema global si está disponible
        if (typeof window.updateFavoritesCount === 'function') {
            window.updateFavoritesCount(count);
            return;
        }
        
        // Fallback legacy
        const favLink = document.querySelector('.header__right__widget a[href*="favorites"], .header__right__widget #favorites-link');
        if (favLink) {
            let tip = favLink.querySelector('.tip');
            
            if (count > 0) {
                if (!tip) {
                    tip = document.createElement('div');
                    tip.className = 'tip';
                    favLink.appendChild(tip);
                }
                tip.textContent = count;
                tip.style.display = 'block';
            } else {
                if (tip) {
                    tip.style.display = 'none';
                }
            }
        }
        
        // Actualizar contador en el modal con formato correcto
        const favCount = document.querySelector('.favorites-count');
        if (favCount) {
            favCount.textContent = count + (count === 1 ? ' producto' : ' productos');
        }
    }

    function animateFavoriteIcon(button) {
        button.style.transform = 'scale(1.3)';
        setTimeout(() => {
            button.style.transform = 'scale(1)';
        }, 300);
    }

    // ===== MODAL DE FAVORITOS =====
    function initFavoritesModal() {
        // Verificar si las funciones globales ya están definidas (desde header-section.php)
        if (window.toggleFavoritesModal && typeof window.toggleFavoritesModal === 'function') {
            console.log('✅ Favorites Modal ya manejado por header-section.php');
            // Las funciones ya están manejadas por header-section.php
            // NO agregar event listeners duplicados
            return;
        }
        
        console.warn('⚠️ header-section.php no cargado, usando funcionalidad limitada de favoritos');
        
        // CÓDIGO LEGACY (solo si header-section.php no está cargado)
        const favLink = document.getElementById('favorites-link');
        const favLinkMobile = document.getElementById('favorites-link-mobile');
        const modal = document.getElementById('favorites-modal');
        const closeBtn = document.querySelector('.favorites-modal-close');

        // Event listener para desktop
        if (favLink && modal) {
            favLink.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                toggleFavoritesModal();
            });
        }

        // Event listener para mobile
        if (favLinkMobile && modal) {
            favLinkMobile.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                toggleFavoritesModal();
            });
        }

        function toggleFavoritesModal() {
            // Cerrar modal de usuario si está abierto
            const userModal = document.getElementById('user-account-modal');
            if (userModal && userModal.style.display === 'block') {
                userModal.style.display = 'none';
            }
            
            // Toggle: si ya está abierto, cerrarlo
            if (modal.style.display === 'block') {
                closeFavoritesModal();
                return;
            }
            
            openFavoritesModal();
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', closeFavoritesModal);
        }

        // Cerrar al hacer click fuera
        document.addEventListener('click', function(e) {
            if (modal && modal.style.display === 'block') {
                const modalContent = modal.querySelector('.favorites-modal-content');
                const favLinkElement = document.getElementById('favorites-link');
                const favLinkMobileElement = document.getElementById('favorites-link-mobile');
                
                const isClickInsideModal = modalContent && modalContent.contains(e.target);
                const isClickOnFavLink = (favLinkElement && favLinkElement.contains(e.target)) || 
                                         (favLinkMobileElement && favLinkMobileElement.contains(e.target));
                
                if (!isClickInsideModal && !isClickOnFavLink) {
                    closeFavoritesModal();
                }
            }
        });

        // Cerrar con ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal && modal.style.display === 'block') {
                closeFavoritesModal();
            }
        });

        // Botones dentro del modal
        initFavoriteModalButtons();
    }

    function openFavoritesModal() {
        const modal = document.getElementById('favorites-modal');
        if (modal) {
            modal.style.display = 'block';
        }
    }

    function closeFavoritesModal() {
        const modal = document.getElementById('favorites-modal');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    function initFavoriteModalButtons() {
        // Botones para agregar/quitar del carrito desde favoritos
        document.addEventListener('click', function(e) {
            // Soportar ambos nombres de clase
            if (e.target.closest('.btn-favorite-cart') || e.target.closest('.btn-add-to-cart-fav')) {
                e.preventDefault();
                const button = e.target.closest('.btn-favorite-cart') || e.target.closest('.btn-add-to-cart-fav');
                const productId = button.getAttribute('data-id');
                const inCart = button.getAttribute('data-in-cart') === 'true';
                
                if (productId && !button.disabled) {
                    if (inCart) {
                        // Quitar del carrito
                        removeFromCart(productId, button);
                    } else {
                        // Agregar al carrito
                        addToCartFromFavorites(productId, button);
                    }
                }
            }
        });

        // Botones para eliminar de favoritos
        document.addEventListener('click', function(e) {
            // Soportar ambos nombres de clase
            if (e.target.closest('.btn-favorite-remove') || e.target.closest('.btn-remove-favorite')) {
                e.preventDefault();
                const button = e.target.closest('.btn-favorite-remove') || e.target.closest('.btn-remove-favorite');
                const productId = button.getAttribute('data-id');
                if (productId) {
                    removeFavoriteFromModal(productId);
                }
            }
        });
    }

    function addToCartFromFavorites(productId, button) {
        const baseUrl = (window.BASE_URL || '').replace(/\/+$/, '');
        fetch(baseUrl + '/app/actions/add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id_producto=${productId}&cantidad=1`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                updateCartCount(data.cart_count);
                
                // Actualizar modal de favoritos para reflejar el estado del carrito
                reloadFavoritesModal();
                
                // Actualizar botón de carrito en la página si existe la función
                if (typeof window.actualizarBotonCarritoPagina === 'function') {
                    window.actualizarBotonCarritoPagina(productId, true);
                }
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error al agregar al carrito', 'error');
        });
    }

    function removeFromCart(productId, button) {
        const baseUrl = (window.BASE_URL || '').replace(/\/+$/, '');
        fetch(baseUrl + '/app/actions/remove_from_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id_producto=${productId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Producto quitado del carrito', 'success');
                updateCartCount(data.cart_count);
                
                // Actualizar modal de favoritos para reflejar el estado del carrito
                reloadFavoritesModal();
                
                // Actualizar botón de carrito en la página si existe la función
                if (typeof window.actualizarBotonCarritoPagina === 'function') {
                    window.actualizarBotonCarritoPagina(productId, false);
                }
            } else {
                showNotification(data.message || 'Error al quitar del carrito', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error al quitar del carrito', 'error');
        });
    }

    // Función para actualizar el estado visual de todos los botones de carrito de un producto
    function updateCartButtonState(productId, inCart) {
        const buttons = document.querySelectorAll(`.btn-favorite-cart[data-id="${productId}"]`);
        
        buttons.forEach(btn => {
            if (btn.disabled) return; // No tocar botones deshabilitados (sin stock)
            
            btn.setAttribute('data-in-cart', inCart ? 'true' : 'false');
            const icon = btn.querySelector('i');
            
            if (inCart) {
                // Estado: En carrito
                btn.classList.add('in-cart');
                btn.style.background = '#28a745';
                btn.title = 'En carrito - Clic para quitar';
                if (icon) {
                    icon.className = 'fa fa-check-circle';
                }
            } else {
                // Estado: No en carrito
                btn.classList.remove('in-cart');
                btn.style.background = '#2c3e50';
                btn.title = 'Agregar al carrito';
                if (icon) {
                    icon.className = 'fa fa-cart-plus';
                }
            }
        });
    }

    function removeFavoriteFromModal(productId) {
        const baseUrl = (window.BASE_URL || '').replace(/\/+$/, '');
        fetch(baseUrl + '/app/actions/add_to_favorites.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id_producto=${productId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                updateFavoritesCount(data.favorites_count);
                
                // ACTUALIZAR MODAL EN TIEMPO REAL
                reloadFavoritesModal();
                
                // Actualizar iconos en la página si existen (productos relacionados y página de detalles)
                const pageButtons = document.querySelectorAll(`.add-to-favorites[data-id="${productId}"]`);
                pageButtons.forEach(btn => {
                    const icon = btn.querySelector('span');
                    if (icon) {
                        icon.classList.remove('icon_heart');
                        icon.classList.add('icon_heart_alt');
                        btn.classList.remove('active');
                        btn.style.color = '';
                    }
                });
                
                // Actualizar icono de favoritos en product-details.php si existe la función
                if (typeof window.actualizarIconosFavoritos === 'function') {
                    window.actualizarIconosFavoritos(productId, false);
                }
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error al procesar favoritos', 'error');
        });
    }

    // ===== NOTIFICACIONES ESTILO TOAST MODERNO =====
    function showNotification(message, type = 'info') {
        // Eliminar notificaciones anteriores
        const existingNotif = document.querySelector('.modern-toast');
        if (existingNotif) {
            existingNotif.remove();
        }

        // Detectar si está en modo oscuro
        const isDarkMode = document.body.classList.contains('dark-mode');

        // Iconos según tipo
        const icons = {
            success: '<i class="fa fa-check-circle"></i>',
            error: '<i class="fa fa-times-circle"></i>',
            info: '<i class="fa fa-info-circle"></i>',
            warning: '<i class="fa fa-exclamation-circle"></i>'
        };

        // Colores según tipo
        const colors = {
            success: { bg: '#10b981', shadow: 'rgba(16, 185, 129, 0.4)' },
            error: { bg: '#ef4444', shadow: 'rgba(239, 68, 68, 0.4)' },
            info: { bg: '#3b82f6', shadow: 'rgba(59, 130, 246, 0.4)' },
            warning: { bg: '#f59e0b', shadow: 'rgba(245, 158, 11, 0.4)' }
        };

        const color = colors[type] || colors.info;

        // Crear notificación
        const toast = document.createElement('div');
        toast.className = `modern-toast ${type}`;
        toast.innerHTML = `
            <div class="toast-icon">${icons[type]}</div>
            <div class="toast-content">
                <div class="toast-message">${message}</div>
            </div>
            <button class="toast-close" onclick="this.parentElement.remove()">
                <i class="fa fa-times"></i>
            </button>
        `;

        // Estilos inline adaptados al modo oscuro
        toast.style.cssText = `
            position: fixed;
            bottom: 30px;
            right: 30px;
            min-width: 300px;
            max-width: 400px;
            background: ${isDarkMode ? '#1f2937' : 'white'};
            border-radius: 12px;
            box-shadow: ${isDarkMode 
                ? '0 10px 40px rgba(0,0,0,0.5), 0 0 0 1px rgba(255,255,255,0.1)' 
                : '0 10px 40px rgba(0,0,0,0.1), 0 0 0 1px rgba(0,0,0,0.05)'
            };
            z-index: 999999;
            animation: slideInUp 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
            overflow: hidden;
        `;

        // Agregar barra de progreso
        const progressBar = document.createElement('div');
        progressBar.style.cssText = `
            position: absolute;
            bottom: 0;
            left: 0;
            height: 4px;
            background: ${color.bg};
            width: 100%;
            animation: progressBar 3s linear;
            border-radius: 0 0 12px 12px;
        `;
        toast.appendChild(progressBar);

        document.body.appendChild(toast);

        // Agregar estilos a los elementos internos
        const icon = toast.querySelector('.toast-icon');
        icon.style.cssText = `
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: ${color.bg};
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        `;

        const content = toast.querySelector('.toast-content');
        content.style.cssText = `
            flex: 1;
            font-size: 14px;
            color: ${isDarkMode ? '#e5e7eb' : '#2d3748'};
            font-weight: 500;
            line-height: 1.5;
        `;

        const closeBtn = toast.querySelector('.toast-close');
        closeBtn.style.cssText = `
            background: none;
            border: none;
            color: ${isDarkMode ? '#6b7280' : '#a0aec0'};
            font-size: 18px;
            cursor: pointer;
            padding: 4px;
            line-height: 1;
            transition: all 0.2s ease;
            border-radius: 4px;
        `;

        closeBtn.onmouseenter = function() {
            this.style.color = isDarkMode ? '#f3f4f6' : '#2d3748';
            this.style.background = isDarkMode ? '#374151' : '#f7fafc';
        };

        closeBtn.onmouseleave = function() {
            this.style.color = isDarkMode ? '#6b7280' : '#a0aec0';
            this.style.background = 'none';
        };

        // Observador para detectar cambios de tema en tiempo real
        const themeObserver = new MutationObserver(() => {
            const newIsDarkMode = document.body.classList.contains('dark-mode');
            if (newIsDarkMode !== isDarkMode) {
                // Actualizar colores del toast en tiempo real
                toast.style.background = newIsDarkMode ? '#1f2937' : 'white';
                toast.style.boxShadow = newIsDarkMode 
                    ? '0 10px 40px rgba(0,0,0,0.5), 0 0 0 1px rgba(255,255,255,0.1)' 
                    : '0 10px 40px rgba(0,0,0,0.1), 0 0 0 1px rgba(0,0,0,0.05)';
                
                content.style.color = newIsDarkMode ? '#e5e7eb' : '#2d3748';
                closeBtn.style.color = newIsDarkMode ? '#6b7280' : '#a0aec0';
            }
        });

        themeObserver.observe(document.body, { 
            attributes: true, 
            attributeFilter: ['class'] 
        });

        // Eliminar después de 3 segundos
        setTimeout(() => {
            toast.style.animation = 'slideOutDown 0.3s ease';
            setTimeout(() => {
                themeObserver.disconnect(); // Detener observador
                toast.remove();
            }, 300);
        }, 3000);

        return toast; // Retornar para poder eliminarlo manualmente
    }

    // ===== INICIALIZACIÓN =====
    document.addEventListener('DOMContentLoaded', function() {
        initCartButtons();
        initFavoriteButtons();
        initFavoritesModal();
        initProductImageClick();
    });

    // Exponer funciones globalmente para re-inicialización después de AJAX
    window.initCartButtons = initCartButtons;
    window.initFavoriteButtons = initFavoriteButtons;

    // ===== NAVEGACIÓN AL HACER CLICK EN IMÁGENES DE PRODUCTOS =====
    let imageClickInitialized = false;
    
    function initProductImageClick() {
        // DESHABILITADO - Interfiere con real-time-updates.js
        console.log('✅ Product image click handler initialized');
        return;
        
        // Prevenir registros múltiples
        if (imageClickInitialized) {
            console.log('⚠️ Product image click already initialized');
            return;
        }
        
        // Delegación de eventos para manejar productos cargados dinámicamente
        document.addEventListener('click', function(e) {
            console.log('🖱️ Click detectado en:', e.target);
            
            // Click en imagen de producto
            const productImage = e.target.closest('.product-image-clickable');
            
            if (productImage) {
                console.log('📦 Click en imagen de producto detectado');
                console.log('🎯 Target:', e.target.tagName, e.target.className);
                
                // Solo navegar si NO se hizo click en un botón de acción
                const clickedOnButton = e.target.closest('.product__hover') || 
                                       e.target.closest('.add-to-cart') || 
                                       e.target.closest('.add-to-favorites') ||
                                       e.target.closest('.view-details-btn') ||
                                       e.target.closest('a') ||
                                       e.target.closest('button');
                
                if (clickedOnButton) {
                    console.log('❌ Click en botón detectado, no navegar');
                    return;
                }
                
                const url = productImage.getAttribute('data-product-url');
                if (url) {
                    console.log('✅ Navegando a:', url);
                    e.preventDefault();
                    e.stopPropagation();
                    window.location.href = url;
                } else {
                    console.error('❌ No se encontró data-product-url');
                }
            }
        }, true); // Usar captura
        
        imageClickInitialized = true;
        console.log('✅ Product image click handler initialized');
    }

    // Agregar animaciones CSS
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInUp {
            from {
                transform: translateY(100px) scale(0.8);
                opacity: 0;
            }
            to {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
        }
        @keyframes slideOutDown {
            from {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
            to {
                transform: translateY(100px) scale(0.8);
                opacity: 0;
            }
        }
        @keyframes progressBar {
            from {
                width: 100%;
            }
            to {
                width: 0%;
            }
        }
        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 0.6;
            }
            50% {
                transform: scale(1.3);
                opacity: 0.2;
            }
            100% {
                transform: scale(1.6);
                opacity: 0;
            }
        }
        
        /* Estilos del Toast */
        .modern-toast {
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }
        
        /* Adaptación al modo oscuro */
        body.dark-mode .modern-toast {
            background: #1f2937 !important;
            box-shadow: 0 10px 40px rgba(0,0,0,0.5), 0 0 0 1px rgba(255,255,255,0.1) !important;
        }
        
        body.dark-mode .modern-toast .toast-content {
            color: #e5e7eb !important;
        }
        
        body.dark-mode .modern-toast .toast-close {
            color: #6b7280 !important;
        }
        
        body.dark-mode .modern-toast .toast-close:hover {
            color: #f3f4f6 !important;
            background: #374151 !important;
        }
        
        /* Hover en modo claro */
        body:not(.dark-mode) .modern-toast .toast-close:hover {
            color: #2d3748 !important;
            background: #f7fafc !important;
        }
        
        .favorite-item {
            transition: all 0.3s ease;
        }
    `;
    document.head.appendChild(style);

    // ===== FUNCIONES GLOBALES PARA ACTUALIZAR CONTADORES =====
    
    /**
     * Actualizar contador del carrito en el header
     * @param {number} count - Nuevo número de productos en el carrito
     */
    window.updateCartCount = function(count) {
        console.log('🛒 Actualizando contador de carrito:', count);
        
        // Actualizar el badge/tip del carrito
        const cartTips = document.querySelectorAll('.header__right__widget a[href*="cart"] .tip');
        cartTips.forEach(tip => {
            tip.textContent = count;
            if (count > 0) {
                tip.style.display = 'block';
            } else {
                tip.style.display = 'none';
            }
        });
        
        // Animar el icono del carrito
        const cartIcons = document.querySelectorAll('.header__right__widget a[href*="cart"] span');
        cartIcons.forEach(icon => {
            icon.style.transition = 'transform 0.3s ease';
            icon.style.transform = 'scale(1.3)';
            setTimeout(() => {
                icon.style.transform = 'scale(1)';
            }, 300);
        });
    };
    
    /**
     * Actualizar contador de favoritos en el header
     * @param {number} count - Nuevo número de favoritos
     */
    window.updateFavoritesCount = function(count) {
        console.log('❤️ Actualizando contador de favoritos:', count);
        
        // Actualizar el badge/tip de favoritos
        const favTips = document.querySelectorAll('#favorites-link .tip, #favorites-link-mobile .tip');
        favTips.forEach(tip => {
            tip.textContent = count;
            if (count > 0) {
                tip.style.display = 'block';
            } else {
                tip.style.display = 'none';
            }
        });
        
        // Animar el icono de favoritos
        const favIcons = document.querySelectorAll('#favorites-link .icon_heart_alt, #favorites-link-mobile .icon_heart_alt');
        favIcons.forEach(icon => {
            icon.style.transition = 'transform 0.3s ease';
            icon.style.transform = 'scale(1.3)';
            setTimeout(() => {
                icon.style.transform = 'scale(1)';
            }, 300);
        });
    };

    /**
     * Toggle favorito desde cualquier parte de la aplicación
     * @param {number} productId - ID del producto
     */
    window.toggleFavorite = function(productId) {
        console.log('❤️ Toggle favorito para producto:', productId);
        
        // Verificar si el usuario está logueado
        const isLoggedIn = document.querySelector('#favorites-link') !== null;
        if (!isLoggedIn) {
            showNotification('Debes iniciar sesión para agregar favoritos', 'warning');
            setTimeout(() => {
                window.location.href = 'login.php';
            }, 1500);
            return;
        }
        
        // Hacer petición AJAX con el parámetro correcto
        fetch('app/actions/add_to_favorites.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id_producto=${productId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Actualizar contador
                if (typeof data.favorites_count !== 'undefined') {
                    window.updateFavoritesCount(data.favorites_count);
                }
                
                // Actualizar el botón visual
                const buttons = document.querySelectorAll(`.add-to-favorites[data-id="${productId}"]`);
                buttons.forEach(btn => {
                    const icon = btn.querySelector('span');
                    if (data.action === 'added') {
                        btn.classList.add('active');
                        if (icon) {
                            icon.className = 'icon_heart'; // Corazón lleno
                        }
                    } else {
                        btn.classList.remove('active');
                        if (icon) {
                            icon.className = 'icon_heart_alt'; // Corazón vacío
                        }
                    }
                });
                
                // ACTUALIZAR MODAL EN TIEMPO REAL si existe la función
                if (typeof window.refreshFavoritesModal === 'function') {
                    window.refreshFavoritesModal();
                } else if (typeof reloadFavoritesModal === 'function') {
                    reloadFavoritesModal();
                }
                
                // Mostrar notificación
                const message = data.action === 'added' 
                    ? 'Producto agregado a favoritos' 
                    : 'Producto eliminado de favoritos';
                showNotification(message, 'success');
            } else {
                showNotification(data.message || 'Error al actualizar favoritos', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error al actualizar favoritos', 'error');
        });
    };
    
    /**
     * Agregar al carrito desde cualquier parte de la aplicación
     * @param {number} productId - ID del producto
     */
    window.addToCart = function(productId) {
        console.log('🛒 Agregando al carrito producto:', productId);
        
        fetch('app/actions/add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `product_id=${productId}&quantity=1`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Actualizar contador
                if (typeof data.cart_count !== 'undefined') {
                    window.updateCartCount(data.cart_count);
                }
                
                showNotification('Producto agregado al carrito', 'success');
            } else {
                showNotification(data.message || 'Error al agregar al carrito', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error al agregar al carrito', 'error');
        });
    };

    // Exportar funciones globalmente
    window.showNotification = showNotification;
    
    console.log('✅ cart-favorites-handler.js cargado completamente');
    console.log('✅ showNotification exportada globalmente');
    console.log('✅ updateCartCount exportada globalmente');
    console.log('✅ updateFavoritesCount exportada globalmente');
    console.log('✅ toggleFavorite exportada globalmente');
    console.log('✅ addToCart exportada globalmente');

})();
