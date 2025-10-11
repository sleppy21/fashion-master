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
    // SISTEMA DE NOTIFICACIONES (solo si no existe)
    // =================================================================
    
    if (typeof window.showNotification !== 'function') {
        function showNotification(message, type) {
            const colors = {
                'success': '#2ecc71',
                'error': '#e74c3c',
                'warning': '#f39c12',
                'info': '#3498db'
            };
            
            const icons = {
                'success': '✓',
                'error': '✕',
                'warning': '⚠',
                'info': 'ℹ'
            };
            
            const notification = $('<div></div>')
                .css({
                    'position': 'fixed',
                    'top': '20px',
                    'right': '20px',
                    'background': colors[type] || colors.info,
                    'color': 'white',
                    'padding': '15px 25px 15px 20px',
                    'border-radius': '50px',
                    'box-shadow': '0 4px 15px rgba(0,0,0,0.3)',
                    'z-index': '10000',
                    'font-weight': '600',
                    'display': 'flex',
                    'align-items': 'center',
                    'gap': '10px',
                    'animation': 'slideInRight 0.3s ease'
                })
                .html('<span style="font-size: 20px;">' + icons[type] + '</span>' + message)
                .appendTo('body');
            
            setTimeout(function() {
                notification.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 3000);
        }
        
        window.showNotification = showNotification;
    }
});
