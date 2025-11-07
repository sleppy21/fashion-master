/* ========================================
   PRODUCT DETAILS - JAVASCRIPT
   ======================================== */

$(document).ready(function() {
    
    // ========================================
    // TABS MODERNOS - NUEVO SISTEMA
    // ========================================
    
    $('.modern-tab-btn').on('click', function() {
        const tabName = $(this).data('tab');
        
        // Remover active de todos los botones y panes
        $('.modern-tab-btn').removeClass('active');
        $('.modern-tab-pane').removeClass('active');
        
        // Activar el seleccionado
        $(this).addClass('active');
        $(`.modern-tab-pane[data-tab-content="${tabName}"]`).addClass('active');
    });
    
    // ========================================
    // CANTIDAD - CONTROLES (LEGACY - botones +/-)
    // ========================================
    
    $('.qty-btn').on('click', function() {
        const action = $(this).data('action');
        const input = $('#quantity');
        let currentValue = parseInt(input.val()) || 1;
        const max = parseInt(input.attr('max')) || 999;
        const min = parseInt(input.attr('min')) || 1;
        
        if (action === 'plus' && currentValue < max) {
            input.val(currentValue + 1);
        } else if (action === 'minus' && currentValue > min) {
            input.val(currentValue - 1);
        }
    });
    
    // Validar entrada manual
    $('#quantity').on('input', function() {
        let value = parseInt($(this).val());
        const max = parseInt($(this).attr('max')) || 999;
        const min = parseInt($(this).attr('min')) || 1;
        
        if (value > max) {
            $(this).val(max);
        } else if (value < min || isNaN(value)) {
            $(this).val(min);
        }
    });
    
    
    // ========================================
    // DROPDOWN DE CANTIDAD (NUEVO SELECTOR)
    // ========================================
    
    console.log('🔍 Inicializando dropdown de cantidad...');
    console.log('Cantidad de .qty-display-btn encontrados:', $('.qty-display-btn').length);
    console.log('Cantidad de .qty-dropdown encontrados:', $('.qty-dropdown').length);
    console.log('Cantidad de .qty-option encontradas:', $('.qty-option').length);
    
    // Verificar que el botón existe y es clickeable
    if ($('.qty-display-btn').length === 0) {
        console.error('❌ ERROR: No se encontró el botón .qty-display-btn');
    } else {
        console.log('✅ Botón encontrado:', $('.qty-display-btn')[0]);
    }
    
    // Toggle dropdown al hacer click en el botón
    $(document).on('click', '.qty-display-btn', function(e) {
        console.log('🖱️ Click detectado en qty-display-btn');
        e.preventDefault();
        e.stopPropagation();
        
        const btn = $(this);
        const wrapper = btn.closest('.qty-select-wrapper');
        const dropdown = wrapper.find('.qty-dropdown');
        
        console.log('📦 Wrapper encontrado:', wrapper.length);
        console.log('📋 Dropdown encontrado:', dropdown.length);
        
        // Cerrar otros dropdowns abiertos
        $('.qty-dropdown').not(dropdown).removeClass('active');
        $('.qty-display-btn').not(btn).removeClass('active');
        
        // Toggle del dropdown actual
        dropdown.toggleClass('active');
        btn.toggleClass('active');
        
        console.log('✨ Dropdown active class:', dropdown.hasClass('active'));
        console.log('🎯 Display del dropdown:', dropdown.css('display'));
    });
    
    // Seleccionar cantidad del dropdown
    $(document).on('click', '.qty-option', function(e) {
        e.stopPropagation();
        const option = $(this);
        const newQty = parseInt(option.data('value'));
        const wrapper = option.closest('.qty-select-wrapper');
        const btn = wrapper.find('.qty-display-btn');
        const dropdown = wrapper.find('.qty-dropdown');
        const maxStock = btn.data('max');
        const currentQty = parseInt(btn.data('current')) || 0;
        
        // Si la cantidad es la misma, solo cerrar el dropdown
        if(newQty === currentQty) {
            dropdown.removeClass('active');
            btn.removeClass('active');
            return;
        }
        
        // Validar stock
        if(newQty > maxStock) {
            showNotification('Stock máximo alcanzado: ' + maxStock, 'warning');
            return;
        }
        
        // Actualizar visualmente
        btn.find('.qty-value').text(newQty);
        btn.data('current', newQty);
        
        // Actualizar clases de selección
        wrapper.find('.qty-option').removeClass('selected');
        option.addClass('selected');
        
        // Cerrar dropdown
        dropdown.removeClass('active');
        btn.removeClass('active');
        
        // 🔥 ACTUALIZAR CARRITO AUTOMÁTICAMENTE si el producto ya está en el carrito
        if(currentQty > 0) {
            const productId = parseInt($('.btn-add-to-cart-details').data('product-id'));
            
            $.ajax({
                url: 'app/actions/update_cart_quantity.php',
                type: 'POST',
                data: {
                    id_producto: productId,
                    cantidad: newQty
                },
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        showNotification('Cantidad actualizada en el carrito', 'success');
                        // Actualizar el contador del carrito
                        if(typeof updateCartCount === 'function') {
                            updateCartCount();
                        }
                    } else {
                        showNotification(response.message || 'Error al actualizar', 'error');
                        // Revertir visualmente
                        btn.find('.qty-value').text(currentQty);
                        btn.data('current', currentQty);
                    }
                },
                error: function() {
                    showNotification('Error de conexión', 'error');
                    // Revertir visualmente
                    btn.find('.qty-value').text(currentQty);
                    btn.data('current', currentQty);
                }
            });
        }
    });
    
    // Cerrar dropdowns al hacer click fuera
    $(document).on('click', function() {
        $('.qty-dropdown').removeClass('active');
        $('.qty-display-btn').removeClass('active');
    });
    
    // Prevenir que se cierre al hacer click dentro del dropdown
    $(document).on('click', '.qty-dropdown', function(e) {
        e.stopPropagation();
    });
    
    
    // ========================================
    // AGREGAR/QUITAR DEL CARRITO (SWITCH)
    // ========================================
    
    $('.btn-add-to-cart, .btn-add-to-cart-details').on('click', function() {
        const button = $(this);
        const productId = button.data('product-id');
        // Usar solo la clase 'in-cart' como fuente de verdad
        const isInCart = button.hasClass('in-cart');
        
        // Si está en el carrito, eliminarlo (SIN CONFIRMACIÓN)
        if (isInCart) {
            button.prop('disabled', true);
            const originalHtml = button.html();
            button.html('<i class="fas fa-spinner fa-spin"></i> <span>Quitando...</span>');
            
            $.ajax({
                url: 'app/actions/remove_from_cart.php',
                method: 'POST',
                data: { id_producto: productId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showNotification('Producto eliminado del carrito', 'success');
                        
                        // Actualizar estado del botón - usar solo clase
                        button.removeClass('in-cart');
                        button.attr('data-in-cart', 'false');
                        button.html('<i class="fas fa-shopping-bag"></i> <span>Agregar al Carrito</span>');
                        
                        // Resetear cantidad a 1
                        const qtyBtn = $('.qty-display-btn');
                        qtyBtn.find('.qty-value').text('1');
                        qtyBtn.data('current', 1);
                        qtyBtn.attr('data-current', '1');
                        $('.qty-option').removeClass('selected');
                        $('.qty-option[data-value="1"]').addClass('selected');
                        
                        // Actualizar contador
                        if (typeof updateCartCount === 'function') {
                            updateCartCount();
                        }
                    } else {
                        showNotification(response.message || 'Error al quitar', 'error');
                        button.html(originalHtml);
                    }
                    button.prop('disabled', false);
                },
                error: function() {
                    showNotification('Error de conexión', 'error');
                    button.html(originalHtml);
                    button.prop('disabled', false);
                }
            });
            
            return;
        }
        
        // Si NO está en el carrito, agregarlo
        let quantity = 1;
        const qtyDropdown = $('.qty-display-btn');
        if (qtyDropdown.length > 0) {
            quantity = parseInt(qtyDropdown.data('current')) || 1;
        } else {
            quantity = parseInt($('#quantity').val()) || 1;
        }
        
        button.prop('disabled', true);
        const originalHtml = button.html();
        button.html('<i class="fas fa-spinner fa-spin"></i> <span>Agregando...</span>');
        
        $.ajax({
            url: 'app/actions/add_to_cart.php',
            method: 'POST',
            data: {
                id_producto: productId,
                cantidad: quantity
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showNotification('¡Producto agregado al carrito!', 'success');
                    
                    // Actualizar estado del botón - usar solo clase
                    button.addClass('in-cart');
                    button.attr('data-in-cart', 'true');
                    button.html('<i class="fas fa-shopping-bag"></i> <span>Agregado al Carrito</span>');
                    
                    // Actualizar cantidad en el dropdown
                    qtyDropdown.data('current', quantity);
                    qtyDropdown.attr('data-current', quantity);
                    
                    // Actualizar contador del carrito si existe
                    if (typeof updateCartCount === 'function') {
                        updateCartCount();
                    }
                } else {
                    showNotification(response.message || 'Error al agregar al carrito', 'error');
                    button.html('<i class="fas fa-shopping-bag"></i> <span>Agregar al Carrito</span>');
                }
                button.prop('disabled', false);
            },
            error: function() {
                showNotification('Error de conexión', 'error');
                button.html('<i class="fas fa-shopping-bag"></i> <span>Agregar al Carrito</span>');
                button.prop('disabled', false);
            }
        });
    });
    
    
    // ========================================
    // AGREGAR A FAVORITOS
    // ========================================
    
    $('.btn-add-to-favorites, .btn-favorite-details').on('click', function() {
        const productId = $(this).data('product-id');
        const button = $(this);
        const icon = button.find('i');
        
        $.ajax({
            url: 'app/actions/add_to_favorites.php',
            method: 'POST',
            data: { id_producto: productId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Toggle icono y clase active
                    if (icon.hasClass('far')) {
                        icon.removeClass('far').addClass('fas');
                        button.addClass('active');
                        button.attr('title', 'Quitar de favoritos');
                        showNotification('¡Agregado a favoritos!', 'success');
                    } else {
                        icon.removeClass('fas').addClass('far');
                        button.removeClass('active');
                        button.attr('title', 'Agregar a favoritos');
                        showNotification('Eliminado de favoritos', 'info');
                    }
                    
                    // Actualizar contador de favoritos
                    if (typeof updateFavoritesCount === 'function') {
                        updateFavoritesCount();
                    }
                    
                    // Refrescar modal de favoritos
                    if (typeof refreshFavoritesModal === 'function') {
                        refreshFavoritesModal();
                    }
                } else {
                    showNotification(response.message || 'Error al procesar favorito', 'error');
                }
            },
            error: function() {
                showNotification('Error de conexión', 'error');
            }
        });
    });
    
    
    // ========================================
    
    // ========================================
    // THUMBNAILS - CAMBIAR IMAGEN PRINCIPAL
    // ========================================
    
    $('.thumbnail-item').on('click', function() {
        const imageSrc = $(this).find('img').attr('src');
        
        // Actualizar imagen principal
        $('#mainProductImage').attr('src', imageSrc);
        
        // Actualizar activo
        $('.thumbnail-item').removeClass('active');
        $(this).addClass('active');
    });
    
    
    // ========================================
    // GALERÍA - ZOOM DE IMAGEN
    // ========================================
    
    $('.btn-zoom').on('click', function() {
        const imageSrc = $(this).data('image');
        $('#zoomedImage').attr('src', imageSrc);
        
        const modal = new bootstrap.Modal(document.getElementById('imageZoomModal'));
        modal.show();
    });
    
    
    // ========================================
    // COMPARTIR EN REDES SOCIALES
    // ========================================
    
    $('.share-btn').on('click', function(e) {
        e.preventDefault();
        
        const productTitle = $('.product-title').text();
        const productUrl = window.location.href;
        const network = $(this).attr('class').split(' ').find(c => 
            ['facebook', 'twitter', 'whatsapp', 'pinterest'].includes(c)
        );
        
        let shareUrl = '';
        
        switch(network) {
            case 'facebook':
                shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(productUrl)}`;
                break;
            case 'twitter':
                shareUrl = `https://twitter.com/intent/tweet?url=${encodeURIComponent(productUrl)}&text=${encodeURIComponent(productTitle)}`;
                break;
            case 'whatsapp':
                shareUrl = `https://wa.me/?text=${encodeURIComponent(productTitle + ' - ' + productUrl)}`;
                break;
            case 'pinterest':
                const imageUrl = $('#mainProductImage').attr('src');
                shareUrl = `https://pinterest.com/pin/create/button/?url=${encodeURIComponent(productUrl)}&media=${encodeURIComponent(imageUrl)}&description=${encodeURIComponent(productTitle)}`;
                break;
        }
        
        if (shareUrl) {
            window.open(shareUrl, '_blank', 'width=600,height=400');
        }
    });
    
    
    // ========================================
    // TABS - SCROLL SUAVE AL CAMBIAR
    // ========================================
    
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        // Scroll suave hacia la sección de tabs
        $('html, body').animate({
            scrollTop: $('.product-tabs-section').offset().top - 100
        }, 400);
    });
    
    
    // ========================================
    // ESCRIBIR RESEÑA - BOTTOM SHEET
    // ========================================
    
    $(document).on('click', '.btn-write-review, .btn-write-review-modern', function() {
        console.log('🖊️ Click en botón escribir reseña detectado');
        $('#reviewBottomSheet').addClass('show');
        $('body').css('overflow', 'hidden');
    });
    
    // Cerrar bottom sheet
    $(document).on('click', '.close-bottom-sheet, .bottom-sheet-backdrop', function() {
        $('#reviewBottomSheet').removeClass('show');
        $('body').css('overflow', 'auto');
    });
    
    // Prevenir cierre al hacer click dentro del sheet
    $('.bottom-sheet-content').on('click', function(e) {
        e.stopPropagation();
    });
    
    
    // ========================================
    // MENÚ DE OPCIONES DE RESEÑAS (3 PUNTOS)
    // ========================================
    
    // Toggle del menú de opciones
    $(document).on('click', '.btn-review-options', function(e) {
        e.stopPropagation();
        const dropdown = $(this).siblings('.review-options-dropdown');
        
        // Cerrar todos los demás menús
        $('.review-options-dropdown').not(dropdown).removeClass('show');
        
        // Toggle del menú actual
        dropdown.toggleClass('show');
    });
    
    // Cerrar menú al hacer click fuera
    $(document).on('click', function() {
        $('.review-options-dropdown').removeClass('show');
    });
    
    // Prevenir que el menú se cierre al hacer click dentro de él
    $(document).on('click', '.review-options-dropdown', function(e) {
        e.stopPropagation();
    });
    
    
    // ========================================
    // EDITAR RESEÑA
    // ========================================
    
    $(document).on('click', '.btn-edit-review', function() {
        const reviewId = $(this).data('review-id');
        const rating = $(this).data('rating');
        const title = $(this).data('title');
        const comment = $(this).data('comment');
        
        // Llenar el formulario con los datos de la reseña
        $('#reviewId').val(reviewId);
        $(`input[name="rating"][value="${rating}"]`).prop('checked', true);
        $('#reviewTitle').val(title);
        $('#reviewComment').val(comment);
        
        // Cambiar el título y texto del botón
        $('#reviewBottomSheet h4').text('Editar tu reseña');
        $('#submitReviewBtn').text('Actualizar Reseña');
        
        // Abrir el bottom sheet
        $('#reviewBottomSheet').addClass('show');
        $('body').css('overflow', 'hidden');
        
        // Cerrar el menú de opciones
        $('.review-options-dropdown').removeClass('show');
    });
    
    
    // ========================================
    // ELIMINAR RESEÑA (SIN CONFIRMACIÓN)
    // ========================================
    
    $(document).on('click', '.btn-delete-review', function() {
        const reviewId = $(this).data('review-id');
        const reviewItem = $(this).closest('.review-item');
        
        // Cerrar menú de opciones
        $('.review-options-dropdown').removeClass('show');
        
        // Eliminar reseña
        $.ajax({
            url: window.BASE_URL + '/app/actions/delete_review.php',
            method: 'POST',
            data: {
                id_resena: reviewId
            },
            success: function(response) {
                // Intentar parsear si es string
                if (typeof response === 'string') {
                    try {
                        response = JSON.parse(response);
                    } catch (e) {
                        console.error('Error parseando respuesta:', response);
                        showNotification('Error en el servidor. Intenta de nuevo.', 'error');
                        return;
                    }
                }
                
                if (response.success) {
                    // Animar y eliminar del DOM
                    reviewItem.fadeOut(400, function() {
                        $(this).remove();
                        
                        // Actualizar contador de reseñas
                        const reviewsTab = $('.modern-tab-btn[data-tab="reviews"]');
                        const currentCount = parseInt(reviewsTab.find('.tab-badge').text()) || 0;
                        if (currentCount > 0) {
                            reviewsTab.find('.tab-badge').text(currentCount - 1);
                        }
                        
                        // Si no quedan reseñas, mostrar mensaje
                        if ($('.review-item').length === 0) {
                            $('#reviewsTab').append(`
                                <div class="no-reviews-message text-center py-5">
                                    <i class="fas fa-comment-slash fa-3x mb-3" style="color: var(--text-secondary);"></i>
                                    <p class="text-muted">No hay reseñas todavía. ¡Sé el primero en opinar!</p>
                                </div>
                            `);
                        }
                    });
                    
                    showNotification(response.message, 'success');
                } else {
                    showNotification(response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX al eliminar:', {xhr: xhr, status: status, error: error});
                console.error('Response Text:', xhr.responseText);
                showNotification('Error al eliminar la reseña. Intenta de nuevo.', 'error');
            }
        });
    });
    
    
    // ========================================
    // MODIFICAR LÓGICA DE ENVÍO (CREATE VS UPDATE)
    // ========================================
    
    // Reemplazar el handler anterior del botón submit
    $('#submitReviewBtn').off('click').on('click', function() {
        const reviewId = $('#reviewId').val(); // Si existe, es edición
        const rating = $('input[name="rating"]:checked').val();
        const title = $('#reviewTitle').val().trim();
        const comment = $('#reviewComment').val().trim();
        
        if (!rating) {
            showNotification('Por favor, selecciona una calificación', 'warning');
            return;
        }
        
        if (!comment) {
            showNotification('Por favor, escribe un comentario', 'warning');
            return;
        }
        
        if (comment.length < 10) {
            showNotification('El comentario debe tener al menos 10 caracteres', 'warning');
            return;
        }
        
        const $submitBtn = $(this);
        $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');
        
        // Determinar URL según si es crear o actualizar
        const url = reviewId 
            ? window.BASE_URL + '/app/actions/update_review.php'
            : window.BASE_URL + '/app/actions/create_review.php';
        
        // Preparar datos
        const data = {
            calificacion: rating,
            titulo: title,
            comentario: comment
        };
        
        if (reviewId) {
            data.id_resena = reviewId;
        } else {
            // Solo para crear nueva reseña
            const productId = $('.btn-add-to-cart-details, .btn-add-to-cart').data('product-id');
            data.id_producto = productId;
        }
        
        // Enviar reseña
        $.ajax({
            url: url,
            method: 'POST',
            data: data,
            success: function(response) {
                // Intentar parsear si es string
                if (typeof response === 'string') {
                    try {
                        response = JSON.parse(response);
                    } catch (e) {
                        console.error('Error parseando respuesta:', response);
                        showNotification('Error en el servidor. Intenta de nuevo.', 'error');
                        $submitBtn.prop('disabled', false).html(reviewId ? 'Actualizar Reseña' : 'Enviar Reseña');
                        return;
                    }
                }
                
                if (response.success) {
                    showNotification(response.message, 'success');
                    
                    // Limpiar formulario
                    $('#reviewId').val('');
                    $('input[name="rating"]').prop('checked', false);
                    $('#reviewTitle').val('');
                    $('#reviewComment').val('');
                    
                    // Restaurar textos originales
                    $('#reviewBottomSheet h4').text('Escribe tu reseña');
                    $('#submitReviewBtn').text('Enviar Reseña');
                    
                    // Cerrar el sheet
                    $('#reviewBottomSheet').removeClass('show');
                    $('body').css('overflow', 'auto');
                    
                    // Recargar la página para mostrar cambios
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    showNotification(response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX:', {xhr: xhr, status: status, error: error});
                console.error('Response Text:', xhr.responseText);
                showNotification('Error al procesar la reseña. Intenta de nuevo.', 'error');
            },
            complete: function() {
                $submitBtn.prop('disabled', false).html(reviewId ? 'Actualizar Reseña' : 'Enviar Reseña');
            }
        });
    });
    
    
    // ========================================
    // ZOOM DE IMAGEN PRINCIPAL (HOVER)
    // ========================================
    
    const imageWrapper = $('.main-image-wrapper');
    const mainImage = $('#mainProductImage');
    
    imageWrapper.on('mousemove', function(e) {
        const offset = $(this).offset();
        const x = e.pageX - offset.left;
        const y = e.pageY - offset.top;
        
        const percentX = (x / $(this).width()) * 100;
        const percentY = (y / $(this).height()) * 100;
        
        mainImage.css('transform-origin', `${percentX}% ${percentY}%`);
    });
    
    
    // ========================================
    // ANIMACIONES DE ENTRADA
    // ========================================
    
    // Animar elementos al cargar
    setTimeout(() => {
        $('.product-image-container').addClass('animate-fade-in');
        $('.product-info-container').addClass('animate-fade-in');
    }, 100);
    
    
    // ========================================
    // FUNCIÓN AUXILIAR - NOTIFICACIONES
    // ========================================
    
    
    // ========================================
    // VERIFICAR SI ESTÁ EN FAVORITOS AL CARGAR
    // ========================================
    
    const productId = $('.btn-add-to-favorites, .btn-favorite-details').data('product-id');
    
    if (productId) {
        $.ajax({
            url: 'app/actions/get_favorites.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success && Array.isArray(response.favorites)) {
                    const isFavorite = response.favorites.some(fav => fav.id_producto == productId);
                    
                    if (isFavorite) {
                        const button = $('.btn-add-to-favorites, .btn-favorite-details');
                        button.find('i').removeClass('far').addClass('fas');
                        button.addClass('active');
                    }
                }
            }
        });
    }
    
});


// ========================================
// ANIMACIONES CSS GENERALES
// ========================================

const style = document.createElement('style');
style.textContent = `
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .animate-fade-in {
        animation: fadeIn 0.6s ease;
    }
`;

document.head.appendChild(style);


// ========================================
// LAZY LOADING DE IMÁGENES
// ========================================

if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src || img.src;
                img.classList.add('loaded');
                observer.unobserve(img);
            }
        });
    });
    
    document.querySelectorAll('.gallery-image[data-src]').forEach(img => {
        imageObserver.observe(img);
    });
}


// ========================================
// SCROLL TO TOP (EN TABS)
// ========================================

window.scrollToTabs = function() {
    const tabsSection = document.querySelector('.product-tabs-section');
    if (tabsSection) {
        tabsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
};


// ========================================
// MANTENER ALTURA DEL CONTENEDOR DE TABS
// ========================================

$(document).ready(function() {
    const tabContent = $('.product-tabs-content');
    
    $('button[data-bs-toggle="tab"]').on('show.bs.tab', function (e) {
        const currentHeight = tabContent.height();
        tabContent.css('min-height', currentHeight + 'px');
    });
    
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        setTimeout(() => {
            tabContent.css('min-height', 'auto');
        }, 300);
    });
});


// ========================================
// COPIAR ENLACE DEL PRODUCTO
// ========================================

function copyProductLink() {
    const url = window.location.href;
    
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(() => {
            showNotification('¡Enlace copiado al portapapeles!', 'success');
        }).catch(err => {
            console.error('Error al copiar:', err);
        });
    } else {
        // Fallback para navegadores antiguos
        const tempInput = document.createElement('input');
        tempInput.value = url;
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand('copy');
        document.body.removeChild(tempInput);
        showNotification('¡Enlace copiado!', 'success');
    }
}


// ========================================
// FORMATEAR PRECIO CON SEPARADORES
// ========================================

function formatPrice(price) {
    return parseFloat(price).toLocaleString('es-ES', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}


// ========================================
// CALCULAR PRECIO TOTAL SEGÚN CANTIDAD
// ========================================

$(document).ready(function() {
    const pricePerUnit = parseFloat($('.current-price').text().replace(/[^0-9.]/g, ''));
    
    if (!isNaN(pricePerUnit)) {
        $('#quantity').on('input change', function() {
            const quantity = parseInt($(this).val()) || 1;
            const totalPrice = pricePerUnit * quantity;
            
            // Opcional: Mostrar precio total
            // $('.total-price-display').text('$' + formatPrice(totalPrice));
        });
    }
});


// ========================================
// LIKE Y REPORTAR RESEÑAS
// ========================================

$(document).ready(function() {
    // Manejar click en botón "Útil" (like)
    $(document).on('click', '.btn-like-review', function() {
        const btn = $(this);
        const reviewId = btn.data('review-id');
        
        if (btn.prop('disabled')) {
            showNotification('Debes iniciar sesión para marcar reseñas como útiles', 'warning');
            return;
        }
        
        $.ajax({
            url: window.BASE_URL + '/app/actions/like_review.php',
            type: 'POST',
            data: { id_resena: reviewId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const icon = btn.find('i');
                    const countSpan = btn.find('.likes-count');
                    
                    if (response.action === 'added') {
                        // Usuario dio like
                        btn.addClass('liked');
                        icon.removeClass('far').addClass('fas');
                        showNotification(response.message, 'success');
                    } else {
                        // Usuario quitó like
                        btn.removeClass('liked');
                        icon.removeClass('fas').addClass('far');
                        showNotification(response.message, 'info');
                    }
                    
                    // Actualizar contador
                    if (response.likes_count > 0) {
                        countSpan.text('(' + response.likes_count + ')');
                    } else {
                        countSpan.text('');
                    }
                } else {
                    showNotification(response.message || 'Error al procesar la solicitud', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en AJAX like:', error);
                showNotification('Error de conexión. Intenta nuevamente.', 'error');
            }
        });
    });
    
    // Manejar click en botón "Reportar" - Abrir modal
    let currentReportReviewId = null;
    let currentReportBtn = null;
    
    $(document).on('click', '.btn-report-review', function() {
        const btn = $(this);
        const reviewId = btn.data('review-id');
        
        if (btn.prop('disabled')) {
            showNotification('Debes iniciar sesión para reportar reseñas', 'warning');
            return;
        }
        
        // Guardar referencia del botón y ID de reseña
        currentReportReviewId = reviewId;
        currentReportBtn = btn;
        
        // Resetear el modal
        $('input[name="reportReason"]').prop('checked', false);
        $('#otherReasonText').val('');
        $('#otherReasonContainer').hide();
        
        // Mostrar el modal
        $('#reportReviewModal').modal('show');
    });
    
    // Mostrar/ocultar textarea cuando se selecciona "Otro motivo"
    $('input[name="reportReason"]').on('change', function() {
        if ($(this).val() === 'Otro motivo') {
            $('#otherReasonContainer').slideDown(200);
        } else {
            $('#otherReasonContainer').slideUp(200);
        }
    });
    
    // Manejar envío del reporte desde el modal
    $('#confirmReportBtn').on('click', function() {
        const selectedReason = $('input[name="reportReason"]:checked');
        
        if (!selectedReason.length) {
            showNotification('Por favor, selecciona un motivo', 'warning');
            return;
        }
        
        let razon = selectedReason.val();
        
        // Si seleccionó "Otro motivo", usar el texto ingresado
        if (razon === 'Otro motivo') {
            const otherText = $('#otherReasonText').val().trim();
            if (!otherText) {
                showNotification('Por favor, describe el motivo', 'warning');
                return;
            }
            razon = 'Otro: ' + otherText;
        }
        
        // Deshabilitar botón mientras se procesa
        $(this).prop('disabled', true).text('Enviando...');
        
        $.ajax({
            url: window.BASE_URL + '/app/actions/report_review.php',
            type: 'POST',
            data: { 
                id_resena: currentReportReviewId,
                razon: razon
            },
            dataType: 'json',
            success: function(response) {
                // Cerrar el modal
                $('#reportReviewModal').modal('hide');
                
                if (response.success) {
                    showNotification(response.message, 'success');
                    currentReportBtn.prop('disabled', true);
                    currentReportBtn.html('<i class="fas fa-flag"></i> Reportado');
                } else {
                    showNotification(response.message || 'Error al enviar el reporte', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en AJAX reporte:', error);
                $('#reportReviewModal').modal('hide');
                showNotification('Error de conexión. Intenta nuevamente.', 'error');
            },
            complete: function() {
                // Rehabilitar botón del modal
                $('#confirmReportBtn').prop('disabled', false).text('Enviar');
            }
        });
    });
    
    
    // ========================================
    // FILTRADO DE PRODUCTOS RELACIONADOS POR CATEGORÍA
    // ========================================
    
    // Por defecto, mostrar productos recomendados
    let showingRecommendations = true;
    let currentProductId = $('.btn-add-to-cart-details').data('product-id');
    
    // Inicializar ProductFilters si no existe (para evitar conflictos con shop.php)
    if (!window.ProductFilters) {
        window.ProductFilters = {
            categorias: [],
            genero: null,
            marca: null,
            precio_min: 0,
            precio_max: 10000,
            buscar: '',
            ordenar: 'newest'
        };
    }
    
    // Función personalizada para cargar productos relacionados
    function loadRelatedProductsCustom(categoryId) {
        const container = $('#related-products-container');
        
        // Mostrar loader moderno
        container.html(`
            <div class="col-12">
                <div class="loading-products">
                    <div class="modern-loader">
                        <div class="modern-loader-ring"></div>
                        <div class="modern-loader-ring"></div>
                        <div class="modern-loader-ring"></div>
                        <i class="modern-loader-icon fa fa-shopping-bag"></i>
                    </div>
                    <p class="loading-text">
                        Cargando productos<span class="loading-dots"><span></span><span></span><span></span></span>
                    </p>
                </div>
            </div>
        `);
        
        // Preparar parámetros según lo que espera get_products_filtered.php
        let ajaxData = {};
        
        if (categoryId) {
            // Si hay categoría específica, enviar como 'c' (category)
            ajaxData.c = categoryId;
        }
        // No agregar más filtros para mostrar todos los productos de la categoría o recomendados
        
        $.ajax({
            url: 'app/actions/get_products_filtered.php',
            method: 'GET',
            data: ajaxData,
            dataType: 'json',
            success: function(response) {
                if (response.success && response.html) {
                    // Eliminar el <div class='row'> wrapper que agrega get_products_filtered.php
                    // porque el contenedor ya es un row
                    let cleanHtml = response.html;
                    cleanHtml = cleanHtml.replace(/<div class=['"]row['"]>/g, '');
                    cleanHtml = cleanHtml.replace(/<\/div>\s*$/g, ''); // Eliminar último </div>
                    
                    container.html(cleanHtml);
                    
                    // Disparar evento para Masonry
                    document.dispatchEvent(new CustomEvent('productsUpdated'));
                    
                    // Refrescar Masonry después de cargar
                    setTimeout(() => {
                        if (typeof window.refreshMasonry === 'function') {
                            window.refreshMasonry();
                        }
                    }, 100);
                } else if (response.success && response.products && response.products.length > 0) {
                    // Si viene en formato JSON de productos
                    let html = '';
                    response.products.forEach(function(product) {
                        html += renderProductCardHTML(product);
                    });
                    container.html(html);
                    
                    // Disparar evento para Masonry
                    document.dispatchEvent(new CustomEvent('productsUpdated'));
                    
                    setTimeout(() => {
                        if (typeof window.refreshMasonry === 'function') {
                            window.refreshMasonry();
                        }
                    }, 100);
                } else {
                    // No hay productos - mostrar animación elegante (igual que product-loader.js)
                    container.html(`
                        <div class="col-12">
                            <div class="no-products-found">
                                <div class="no-products-animation">
                                    <div class="empty-box">
                                        <div class="box-lid"></div>
                                        <div class="box-body">
                                            <i class="fa fa-search"></i>
                                        </div>
                                        <div class="box-shadow"></div>
                                    </div>
                                </div>
                                <h3 class="no-products-title">No se encontraron productos</h3>
                                <p class="no-products-subtitle">No hay productos disponibles en esta categoría</p>
                                <button class="btn-clear-filters" onclick="$('.categories-navbar-list button[data-id=0]').click()">
                                    <i class="fa fa-refresh"></i> Ver todos los productos
                                </button>
                            </div>
                        </div>
                    `);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al cargar productos relacionados:', {xhr, status, error});
                container.html(`
                    <div class="col-12">
                        <div class="error-products">
                            <div class="error-animation">
                                <i class="fa fa-exclamation-triangle"></i>
                            </div>
                            <h3 class="error-title">Error al cargar productos</h3>
                            <p class="error-subtitle">Por favor, intenta nuevamente</p>
                            <button class="btn-retry" onclick="location.reload()">
                                <i class="fa fa-redo"></i> Reintentar
                            </button>
                        </div>
                    </div>
                `);
            }
        });
    }
    
    // Manejar clicks en las categorías del navbar (solo en la sección de productos relacionados)
    $('.related-products-section').on('click', '.categories-navbar-list button[data-id]', function(e) {
        e.preventDefault();
        e.stopPropagation(); // Evitar que categories-navbar.js lo procese
        
        const categoryId = $(this).attr('data-id');
        const categoryName = $(this).find('.category-name').text();
        
        // Actualizar estado activo visualmente
        $('.related-products-section .categories-navbar-list li').removeClass('active');
        $(this).closest('li').addClass('active');
        
        // Si es "Todos" (0), mostrar recomendaciones
        if (categoryId === '0') {
            showingRecommendations = true;
            loadRelatedProductsCustom(null);
        } else {
            // Si selecciona una categoría, mostrar todos los productos de esa categoría
            showingRecommendations = false;
            loadRelatedProductsCustom(categoryId);
        }
    });
    
    // ========================================
    // CARGAR PRODUCTOS RELACIONADOS AL INICIO
    // ========================================
    
    // Cargar productos recomendados automáticamente al cargar la página
    if ($('#related-products-container').length > 0) {
        console.log('📦 Cargando productos relacionados iniciales...');
        loadRelatedProductsCustom(null); // null = modo recomendaciones
    }
    
    });
    
    function renderProductCardHTML(product) {
        // Esta función genera el HTML de una tarjeta de producto
        // Reutiliza la estructura del componente product-card.php
        return `
            <div class="col-lg-3 col-md-4 col-sm-6 col-6 mb-4">
                <div class="product-card-modern">
                    <div class="product-image-wrapper">
                        ${product.descuento_porcentaje_producto > 0 ? `<span class="discount-badge">-${Math.round(product.descuento_porcentaje_producto)}%</span>` : ''}
                        <a href="product-details.php?id=${product.id_producto}">
                            <img src="${product.url_imagen_producto}" alt="${product.nombre_producto}" class="product-image">
                        </a>
                        <div class="product-actions">
                            <button class="btn-favorite ${product.is_favorite ? 'active' : ''}" data-product-id="${product.id_producto}">
                                <i class="${product.is_favorite ? 'fas' : 'far'} fa-heart"></i>
                            </button>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3 class="product-title">
                            <a href="product-details.php?id=${product.id_producto}">${product.nombre_producto}</a>
                        </h3>
                        <div class="product-rating">
                            ${generateStarsHTML(product.calificacion_promedio || 0)}
                            <span class="reviews-count">(${product.total_resenas || 0})</span>
                        </div>
                        <div class="product-price">
                            ${product.descuento_porcentaje_producto > 0 ? 
                                `<span class="price-original">$${parseFloat(product.precio_producto).toFixed(2)}</span>
                                 <span class="price-final">$${parseFloat(product.precio_final).toFixed(2)}</span>` :
                                `<span class="price-final">$${parseFloat(product.precio_producto).toFixed(2)}</span>`
                            }
                        </div>
                        <button class="btn-add-to-cart ${product.in_cart ? 'in-cart' : ''}" data-product-id="${product.id_producto}">
                            <i class="fas fa-shopping-cart"></i>
                            <span>${product.in_cart ? 'En el Carrito' : 'Agregar'}</span>
                        </button>
                    </div>
                </div>
            </div>
        `;
    }
    
    function generateStarsHTML(rating) {
        let stars = '';
        const fullStars = Math.floor(rating);
        const hasHalfStar = rating % 1 >= 0.5;
        
        for (let i = 1; i <= 5; i++) {
            if (i <= fullStars) {
                stars += '<i class="fas fa-star"></i>';
            } else if (i === fullStars + 1 && hasHalfStar) {
                stars += '<i class="fas fa-star-half-alt"></i>';
            } else {
                stars += '<i class="far fa-star"></i>';
            }
        }
        
        return stars;
    }

 // Cierre de $(document).ready principal


// ========================================
// INICIALIZAR MASONRY AL CARGAR (FUERA DE DOCUMENT.READY)
// ========================================

// Esperar a que TODO cargue, incluyendo imágenes
window.addEventListener('load', function() {
    setTimeout(function() {
        if (typeof window.reinitMasonry === 'function') {
            console.log('🎨 Inicializando Masonry para productos relacionados...');
            window.reinitMasonry();
        }
    }, 300);
});

console.log('✅ Product Details JS cargado correctamente');
