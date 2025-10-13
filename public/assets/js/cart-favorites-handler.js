/**
 * CART AND FAVORITES HANDLER
 * Maneja la funcionalidad de carrito y favoritos
 */

(function() {
    'use strict';

    // ===== AGREGAR AL CARRITO =====
    function initCartButtons() {
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

        fetch('app/actions/add_to_cart.php', {
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
        const cartTip = document.querySelector('.header__right__widget a[href*="cart"] .tip');
        if (cartTip) {
            cartTip.textContent = count;
            cartTip.style.display = count > 0 ? 'block' : 'none';
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
                showNotification(data.message, 'success');
                updateFavoritesCount(data.favorites_count);
                
                // Cambiar icono según la acción
                const icon = button.querySelector('span');
                if (data.action === 'added') {
                    icon.classList.remove('icon_heart_alt');
                    icon.classList.add('icon_heart');
                    button.classList.add('active');
                    button.style.color = '#e74c3c';
                } else {
                    icon.classList.remove('icon_heart');
                    icon.classList.add('icon_heart_alt');
                    button.classList.remove('active');
                    button.style.color = '';
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
        const modalBody = document.querySelector('.favorites-modal-body');
        if (!modalBody) return;

        // NO mostrar loading, solo actualizar silenciosamente
        fetch('app/actions/get_favorites.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Actualizar el HTML del modal
                    modalBody.innerHTML = data.html;
                    
                    // NO re-inicializar botones porque ya están usando delegación de eventos
                    console.log('✅ Favorites modal updated silently');
                } else {
                    console.error('Error al cargar favoritos:', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    function updateFavoritesCount(count) {
        // Actualizar badge del header - SOLO cambiar número sin animaciones
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
        
        // Actualizar contador en el modal - SOLO cambiar número
        const favCount = document.querySelector('.favorites-count');
        if (favCount) {
            favCount.textContent = count;
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
        fetch('app/actions/add_to_cart.php', {
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
                
                // Actualizar TODOS los botones de este producto en el modal
                updateCartButtonState(productId, true);
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
        fetch('app/actions/remove_from_cart.php', {
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
                
                // Actualizar TODOS los botones de este producto en el modal
                updateCartButtonState(productId, false);
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
                showNotification(data.message, 'success');
                updateFavoritesCount(data.favorites_count);
                
                // ACTUALIZAR MODAL EN TIEMPO REAL
                reloadFavoritesModal();
                
                // Actualizar iconos en la página si existen
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

        // Iconos según tipo
        const icons = {
            success: '<i class="fa fa-check-circle"></i>',
            error: '<i class="fa fa-times-circle"></i>',
            info: '<i class="fa fa-info-circle"></i>'
        };

        // Colores según tipo
        const colors = {
            success: { bg: '#10b981', shadow: 'rgba(16, 185, 129, 0.4)' },
            error: { bg: '#ef4444', shadow: 'rgba(239, 68, 68, 0.4)' },
            info: { bg: '#3b82f6', shadow: 'rgba(59, 130, 246, 0.4)' }
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

        // Estilos inline para evitar dependencias
        toast.style.cssText = `
            position: fixed;
            bottom: 30px;
            right: 30px;
            min-width: 300px;
            max-width: 400px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1), 0 0 0 1px rgba(0,0,0,0.05);
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
            color: #2d3748;
            font-weight: 500;
            line-height: 1.5;
        `;

        const closeBtn = toast.querySelector('.toast-close');
        closeBtn.style.cssText = `
            background: none;
            border: none;
            color: #a0aec0;
            font-size: 18px;
            cursor: pointer;
            padding: 4px;
            line-height: 1;
            transition: all 0.2s ease;
            border-radius: 4px;
        `;

        closeBtn.onmouseenter = function() {
            this.style.color = '#2d3748';
            this.style.background = '#f7fafc';
        };

        closeBtn.onmouseleave = function() {
            this.style.color = '#a0aec0';
            this.style.background = 'none';
        };

        // Eliminar después de 3 segundos
        setTimeout(() => {
            toast.style.animation = 'slideOutDown 0.3s ease';
            setTimeout(() => {
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
        .favorite-item {
            transition: all 0.3s ease;
        }
    `;
    document.head.appendChild(style);

})();
