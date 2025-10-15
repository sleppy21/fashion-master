/**
 * PRODUCT NAVIGATION
 * Maneja la navegación a product-details con doble click en cualquier imagen de producto
 */

(function() {
    'use strict';

    // Inicializar cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
        initProductDoubleClick();
    });

    // También re-inicializar después de actualizaciones AJAX (para modal de favoritos)
    function initProductDoubleClick() {
        // Seleccionar TODAS las imágenes de productos en la página
        const productImages = document.querySelectorAll('.product__item__pic, .favorite-image');
        
        productImages.forEach(function(image) {
            // Remover event listeners previos si existen (evitar duplicados)
            image.removeEventListener('dblclick', handleDoubleClick);
            image.removeEventListener('click', handleSingleClick);
            
            // Agregar event listener para doble click
            image.addEventListener('dblclick', handleDoubleClick);
            
            // Agregar event listener para click simple (prevenir navegación)
            image.addEventListener('click', handleSingleClick);
            
            // Agregar cursor pointer para indicar que es clickeable
            image.style.cursor = 'pointer';
        });
        
        console.log(`✅ Product navigation initialized for ${productImages.length} images`);
    }
    
    // Prevenir click simple en links dentro de la imagen
    function handleSingleClick(e) {
        // Solo prevenir si es un link dentro de la imagen
        if(e.target.tagName === 'A' || e.target.closest('a')) {
            e.preventDefault();
            e.stopPropagation();
        }
    }

    function handleDoubleClick(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        
        // Prevenir cualquier otro comportamiento
        if(e.target.tagName === 'A' || e.target.closest('a')) {
            e.target.onclick = function(ev) {
                ev.preventDefault();
                return false;
            };
        }
        
        // Obtener el ID del producto de diferentes formas posibles
        let productId = null;
        
        // Método 1: data-id directo en la imagen
        if(this.dataset.id) {
            productId = this.dataset.id;
        }
        
        // Método 2: buscar en el padre .product__item
        if(!productId) {
            const productItem = this.closest('.product__item');
            if(productItem) {
                // Buscar en botones de favoritos o carrito
                const favBtn = productItem.querySelector('[data-id]');
                if(favBtn) {
                    productId = favBtn.dataset.id;
                }
            }
        }
        
        // Método 3: buscar en el padre .favorite-item (para modal de favoritos)
        if(!productId) {
            const favoriteItem = this.closest('.favorite-item');
            if(favoriteItem) {
                productId = favoriteItem.dataset.id;
            }
        }
        
        // Método 4: buscar en ondblclick attribute (shop.php lo tiene)
        if(!productId && this.hasAttribute('ondblclick')) {
            const onclickValue = this.getAttribute('ondblclick');
            const match = onclickValue.match(/id=(\d+)/);
            if(match) {
                productId = match[1];
            }
        }
        
        // Si se encontró el ID, navegar
        if(productId) {
            console.log(`🔄 Navigating to product ${productId}`);
            window.location.href = `product-details.php?id=${productId}`;
        } else {
            console.warn('⚠️ Could not find product ID for navigation');
        }
    }

    // Exponer función para re-inicializar después de AJAX
    window.reinitProductDoubleClick = initProductDoubleClick;

})();
