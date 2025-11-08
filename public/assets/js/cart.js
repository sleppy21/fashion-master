// Modal de opciones para productos en carrito
$(document).ready(function() {
    let selectedCartId = null;
    let selectedProductId = null;

    // Mostrar/ocultar el modal al lado del botón de opciones
    $(document).on('click', '.cart-mobile-item__options-btn', function(e) {
        e.preventDefault();
        var modal = $('#cart-options-modal');
        // Si ya está visible y fue abierto por el mismo botón, ciérralo
        if (modal.is(':visible') && $(this).hasClass('active-options-btn')) {
            modal.hide();
            $(this).removeClass('active-options-btn');
            return;
        }
        // Quitar clase a otros botones
        $('.cart-mobile-item__options-btn').removeClass('active-options-btn');
        $(this).addClass('active-options-btn');
        selectedCartId = $(this).data('id');
        selectedProductId = $(this).data('product-id');
        var btnOffset = $(this).offset();
        var btnWidth = $(this).outerWidth();
        var modalWidth = modal.outerWidth();
        var modalHeight = modal.outerHeight();
        var windowHeight = $(window).height();
        var windowWidth = $(window).width();
        var top = btnOffset.top;
        var left = btnOffset.left - modalWidth - 8;
        // Si el modal se sale por arriba, ajusta top
        if (top + modalHeight > windowHeight) {
            top = windowHeight - modalHeight - 12;
        }
        // Si el modal se sale por la izquierda, ajusta left
        if (left < 0) {
            left = btnOffset.left + btnWidth + 8;
        }
        modal.css({
            top: top,
            left: left,
            display: 'block',
            opacity: 1
        });
    });
    // Cerrar modal
    $(document).on('click', '.cart-options-modal__close', function() {
        $('#cart-options-modal').hide();
    });
    // Cerrar si se hace click fuera del modal o en otro botón
    $(document).on('mousedown', function(e) {
        var modal = $('#cart-options-modal');
        if (modal.is(':visible') && !$(e.target).closest('.cart-options-modal, .cart-mobile-item__options-btn').length) {
            modal.hide();
            $('.cart-mobile-item__options-btn').removeClass('active-options-btn');
        }
    });

    // Ir al producto
    $(document).on('click', '.cart-options-modal__btn.go-to-product', function() {
        if (selectedProductId) {
            window.location.href = 'product-details.php?id=' + selectedProductId;
        }
    });

    // Eliminar del carrito
    $(document).on('click', '.cart-options-modal__btn.remove-cart-item-modal', function() {
        if (selectedCartId) {
            // Preferir función global si existe
            if (typeof window.removeFromCart === 'function') {
                window.removeFromCart(selectedCartId).then(() => {
                    $('#cart-options-modal').fadeOut(120);
                });
            } else {
                // Fallback AJAX
                $.post('app/actions/remove_from_cart.php', {
                    id_carrito: selectedCartId
                }, function(response) {
                    if(response.success) {
                        // Eliminar visualmente
                        $('tr[data-cart-id="' + selectedCartId + '"]').fadeOut(400, function() { $(this).remove(); });
                        $('.cart-mobile-item[data-cart-id="' + selectedCartId + '"]').fadeOut(400, function() { $(this).remove(); });
                        $('#cart-options-modal').fadeOut(120);
                    } else {
                        alert(response.message || 'Error al eliminar');
                    }
                }, 'json');
            }
        }
    });
});
