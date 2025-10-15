/**
 * PRODUCT DETAILS - Handler de funcionalidad
 * Solo controles de cantidad - Los botones de carrito/favoritos están en cart-favorites-handler.js
 */

$(document).ready(function() {
    console.log('✅ Product Details Handler inicializado');
    
    // =================================================================
    // CONTROL DE CANTIDAD +/-
    // =================================================================
    
    // Botón MENOS (-)
    $(document).on('click', '.qty-minus, .qty-btn-detail.qty-minus, .qtybtn.qty-minus', function(e) {
        e.preventDefault();
        const input = $('#product-quantity');
        let val = parseInt(input.val()) || 0;
        
        if(val > 0) {
            input.val(val - 1);
            console.log('➖ Cantidad reducida a:', val - 1);
        }
    });
    
    // Botón MÁS (+)
    $(document).on('click', '.qty-plus, .qty-btn-detail.qty-plus, .qtybtn.qty-plus', function(e) {
        e.preventDefault();
        const maxStock = parseInt($(this).data('max')) || 999;
        const input = $('#product-quantity');
        let val = parseInt(input.val()) || 0;
        
        if(val < maxStock) {
            input.val(val + 1);
            console.log('➕ Cantidad aumentada a:', val + 1);
        } else {
            if (typeof window.showNotification === 'function') {
                showNotification('Stock máximo: ' + maxStock + ' unidades', 'warning');
            }
            console.log('⚠️ Stock máximo alcanzado');
        }
    });
    
    // =================================================================
    // SISTEMA DE NOTIFICACIONES - Usar toast moderno del cart-favorites-handler.js
    // =================================================================
    
    // La función showNotification ya está definida en cart-favorites-handler.js
    // No necesitamos redefinirla aquí, solo verificamos que exista
    if (typeof window.showNotification !== 'function') {
        // Fallback simple si cart-favorites-handler.js no está cargado
        window.showNotification = function(message, type) {
            console.log(`[${type.toUpperCase()}] ${message}`);
            alert(message);
        };
    }
});
