/**
 * PRODUCT TOUCH HANDLER
 * Maneja el comportamiento de touch en móviles para las tarjetas de productos
 * - Primer toque: Muestra los botones hover
 * - Los botones permiten: Ver detalles, Favoritos, Carrito
 * 
 * GLOBAL: Se usa en shop.php, cart.php, product-details.php
 */

(function() {
    'use strict';

    let isMobile = window.innerWidth <= 768;
    
    // Actualizar detección de móvil en resize
    window.addEventListener('resize', function() {
        isMobile = window.innerWidth <= 768;
    });

    // Inicializar cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
        initProductTouchHandler();
    });

    function initProductTouchHandler() {
        // Solo aplicar en móviles
        if (!isMobile) return;

        const productCards = document.querySelectorAll('.product-card-modern');
        
        productCards.forEach(function(card) {
            const imageWrapper = card.querySelector('.product-image-wrapper');
            
            if (!imageWrapper) return;

            // Marcar como no iniciado
            card.removeAttribute('data-hover-active');
            
            // Remover event listeners previos
            imageWrapper.removeEventListener('click', handleImageClick);
            
            // Agregar event listener
            imageWrapper.addEventListener('click', handleImageClick);
        });
        
        console.log(`🎯 Touch handler inicializado para ${productCards.length} tarjetas`);
    }

    function handleImageClick(e) {
        const card = this.closest('.product-card-modern');
        if (!card) return;
        
        // Si se clickea en un botón hover, no hacer nada (dejar que funcione)
        if (e.target.closest('.product__hover')) {
            return;
        }
        
        const isHoverActive = card.hasAttribute('data-hover-active');
        
        if (!isHoverActive) {
            // Primer click: Mostrar botones
            activateHover(card);
            closeOtherCards(card);
        } else {
            // Segundo click: Navegar a product-details
            const productId = card.getAttribute('data-product-id');
            if (productId) {
                window.location.href = `product-details.php?id=${productId}`;
            }
        }
    }

    function activateHover(card) {
        // Marcar como activo
        card.setAttribute('data-hover-active', 'true');
        
        // Agregar clase CSS para mostrar botones
        card.classList.add('force-hover');
        
        // Auto-cerrar después de 5 segundos de inactividad
        setTimeout(function() {
            if (card.hasAttribute('data-hover-active')) {
                deactivateHover(card);
            }
        }, 5000);
    }

    function deactivateHover(card) {
        card.removeAttribute('data-hover-active');
        card.classList.remove('force-hover');
    }

    function closeOtherCards(currentCard) {
        const allCards = document.querySelectorAll('.product-card-modern[data-hover-active]');
        allCards.forEach(function(card) {
            if (card !== currentCard) {
                deactivateHover(card);
            }
        });
    }

    // Cerrar hover cuando se toca fuera de las tarjetas
    document.addEventListener('click', function(e) {
        if (!isMobile) return;
        
        const card = e.target.closest('.product-card-modern');
        if (!card) {
            // Tocó fuera de cualquier tarjeta, cerrar todas
            const activeCards = document.querySelectorAll('.product-card-modern[data-hover-active]');
            activeCards.forEach(deactivateHover);
        }
    });

    // Exponer función para re-inicializar después de AJAX
    window.reinitProductTouchHandler = initProductTouchHandler;

})();
