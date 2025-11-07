/**
 * PRODUCT IMAGE ADAPTER
 * Ajusta la altura de las tarjetas de productos según la proporción real de cada imagen
 * 
 * NOTA: PHP calcula el aspect ratio inicial. Este JS solo ajusta productos cargados dinámicamente (AJAX)
 * o imágenes externas que PHP no pudo procesar.
 */

(function() {
    'use strict';

    /**
     * Ajusta la altura de una tarjeta según la proporción de su imagen
     */
    function adjustCardImageHeight(card) {
        const imageWrapper = card.querySelector('.product-image-wrapper');
        const image = card.querySelector('.product-image');
        
        if (!imageWrapper || !image) return;
        
        // Si ya tiene padding-top inline (calculado por PHP), no hacer nada
        if (imageWrapper.style.paddingTop) {
            console.log('✅ Padding-top ya definido por PHP:', imageWrapper.style.paddingTop);
            return;
        }
        
        // Solo calcular si PHP no pudo hacerlo (imágenes externas)
        const applyHeight = () => {
            if (image.naturalWidth > 0 && image.naturalHeight > 0) {
                setWrapperHeight(imageWrapper, image);
            }
        };
        
        if (image.complete && image.naturalWidth > 0) {
            applyHeight();
        } else {
            image.addEventListener('load', applyHeight, { once: true });
        }
    }

    /**
     * Calcula y aplica la altura óptima al wrapper
     */
    function setWrapperHeight(wrapper, image) {
        const naturalWidth = image.naturalWidth;
        const naturalHeight = image.naturalHeight;
        
        if (naturalWidth === 0 || naturalHeight === 0) {
            return;
        }
        
        const aspectRatio = naturalHeight / naturalWidth;
        const paddingTop = (aspectRatio * 100) + '%';
        
        console.log(`📏 JS calculando: ${naturalWidth}x${naturalHeight} → ${paddingTop}`);
        
        wrapper.style.paddingTop = paddingTop;
    }

    /**
     * Ajusta todas las tarjetas de productos en la página
     */
    function adjustAllProductCards() {
        const productCards = document.querySelectorAll('.product-card-modern');
        
        if (productCards.length === 0) return;
        
        console.log(`📏 Image Adapter: Verificando ${productCards.length} tarjetas...`);
        
        productCards.forEach(function(card) {
            adjustCardImageHeight(card);
        });
    }

    // Auto-ejecutar cuando el DOM esté listo (solo para productos dinámicos)
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', adjustAllProductCards);
    } else {
        adjustAllProductCards();
    }

    // Re-ejecutar cuando se cargan productos dinámicamente (AJAX)
    document.addEventListener('productsLoaded', function(e) {
        console.log('🔄 Evento productsLoaded - Verificando tarjetas nuevas');
        setTimeout(adjustAllProductCards, 100);
    });
    
    document.addEventListener('productsUpdated', function() {
        console.log('🔄 Evento productsUpdated - Verificando tarjetas');
        setTimeout(adjustAllProductCards, 100);
    });

    // Exponer funciones globalmente
    window.adjustCardImageHeight = adjustCardImageHeight;
    window.adjustAllProductCards = adjustAllProductCards;

})();
