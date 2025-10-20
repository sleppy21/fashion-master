/**
 * FETCH API HANDLER - SleppyStore
 * Reemplazo moderno de jQuery AJAX con Fetch API
 * Actualiza contenido en tiempo real sin recargar la página
 */

// ============================================
// FUNCIONES HELPER PARA FETCH API
// ============================================

/**
 * Wrapper moderno para hacer peticiones FETCH
 * @param {string} url - URL del endpoint
 * @param {object} options - Opciones de la petición
 * @returns {Promise} - Promesa con la respuesta
 */
async function fetchAPI(url, options = {}) {
    const defaultOptions = {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    };

    const config = { ...defaultOptions, ...options };

    try {
        const response = await fetch(url, config);
        
        // Verificar si la respuesta es exitosa
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        // Intentar parsear como JSON
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return await response.json();
        }
        
        // Si no es JSON, devolver texto
        return await response.text();
    } catch (error) {
        console.error('Fetch API Error:', error);
        throw error;
    }
}

/**
 * GET Request usando Fetch
 */
async function fetchGET(url, params = {}) {
    const queryString = new URLSearchParams(params).toString();
    const fullUrl = queryString ? `${url}?${queryString}` : url;
    
    return await fetchAPI(fullUrl, { method: 'GET' });
}

/**
 * POST Request usando Fetch
 */
async function fetchPOST(url, data = {}) {
    return await fetchAPI(url, {
        method: 'POST',
        body: JSON.stringify(data)
    });
}

/**
 * POST con FormData (para archivos)
 */
async function fetchPOSTFormData(url, formData) {
    return await fetchAPI(url, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    });
}

// ============================================
// FUNCIONES DE ACTUALIZACIÓN EN TIEMPO REAL
// ============================================

/**
 * Actualizar contador del carrito en tiempo real
 */
async function updateCartCount() {
    try {
        const data = await fetchGET('app/actions/get_cart_count.php');
        
        if (data.success) {
            // Actualizar todos los elementos con la clase cart-count
            document.querySelectorAll('.tip').forEach(el => {
                const count = parseInt(data.count);
                if (count > 0) {
                    el.textContent = count;
                    el.style.display = 'flex';
                } else {
                    el.style.display = 'none';
                }
            });
        }
    } catch (error) {
        console.error('Error updating cart count:', error);
    }
}

/**
 * Actualizar contador de favoritos en tiempo real
 */
async function updateFavoritesCount() {
    try {
        const data = await fetchGET('app/actions/get_favorites_count.php');
        
        if (data.success) {
            // Actualizar el contador de favoritos con formato correcto
            document.querySelectorAll('.favorites-count').forEach(el => {
                const countNumber = el.querySelector('.fav-count-number');
                const countText = data.count === 1 ? 'producto favorito' : 'productos favoritos';
                
                if (countNumber) {
                    countNumber.textContent = data.count;
                } else {
                    el.innerHTML = `<span class="fav-count-number">${data.count}</span> ${countText}`;
                }
            });
        }
    } catch (error) {
        console.error('Error updating favorites count:', error);
    }
}

/**
 * Actualizar contador de favoritos directamente (sin hacer fetch)
 */
function updateFavoritesCountDirectly(count) {
    // Actualizar badges en el header
    document.querySelectorAll('#favorites-count, .favorites-count-badge').forEach(el => {
        el.textContent = count;
        if (count > 0) {
            el.style.display = '';
        } else {
            el.style.display = 'none';
        }
    });
    
    // Actualizar texto completo de favoritos
    document.querySelectorAll('.favorites-count').forEach(el => {
        const countNumber = el.querySelector('.fav-count-number');
        const countText = count === 1 ? 'producto favorito' : 'productos favoritos';
        
        if (countNumber) {
            countNumber.textContent = count;
        } else {
            el.innerHTML = `<span class="fav-count-number">${count}</span> ${countText}`;
        }
    });
}

/**
 * Agregar producto al carrito usando Fetch API
 */
async function addToCart(productId, quantity = 1) {
    try {
        // Mostrar loader con SweetAlert2
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Agregando al carrito...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }

        const data = await fetchPOST('app/actions/add_to_cart.php', {
            id_producto: productId,
            cantidad: quantity
        });

        if (typeof Swal !== 'undefined') {
            Swal.close();
        }

        if (data.success) {
            // Actualizar contador del carrito
            await updateCartCount();

            // Mostrar notificación de éxito
            if (typeof Toast !== 'undefined') {
                Toast.fire({
                    icon: 'success',
                    title: data.message || 'Producto agregado al carrito'
                });
            }

            // Disparar evento personalizado
            document.dispatchEvent(new CustomEvent('cartUpdated', { 
                detail: { productId, quantity, action: 'add' }
            }));

            return true;
        } else {
            throw new Error(data.message || 'Error al agregar al carrito');
        }
    } catch (error) {
        console.error('Error adding to cart:', error);
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'No se pudo agregar el producto al carrito'
            });
        }
        
        return false;
    }
}

/**
 * Agregar/Quitar de favoritos usando Fetch API
 */
async function toggleFavorite(productId) {
    try {
        const data = await fetchPOST('app/actions/add_to_favorites.php', {
            id_producto: productId
        });

        if (data.success) {
            // Actualizar contador de favoritos
            if (typeof data.favorites_count !== 'undefined') {
                updateFavoritesCountDirectly(data.favorites_count);
            } else {
                await updateFavoritesCount();
            }

            // Actualizar icono del botón
            const buttons = document.querySelectorAll(`[data-id="${productId}"].add-to-favorites, [data-id="${productId}"].btn-favorite`);
            buttons.forEach(btn => {
                const icon = btn.querySelector('span');
                if (data.action === 'added') {
                    btn.classList.add('active');
                    if (icon) icon.className = 'icon_heart';
                } else {
                    btn.classList.remove('active');
                    if (icon) icon.className = 'icon_heart_alt';
                }
            });

            // Mostrar notificación
            if (typeof Toast !== 'undefined') {
                Toast.fire({
                    icon: 'success',
                    title: data.message
                });
            } else if (typeof showToast === 'function') {
                showToast(data.message, data.action === 'added' ? 'success' : 'info');
            }

            // Disparar evento personalizado
            document.dispatchEvent(new CustomEvent('favoritesUpdated', {
                detail: { productId, action: data.action }
            }));

            return true;
        } else {
            throw new Error(data.message || 'Error al actualizar favoritos');
        }
    } catch (error) {
        console.error('Error toggling favorite:', error);
        
        if (typeof Toast !== 'undefined') {
            Toast.fire({
                icon: 'error',
                title: error.message || 'Error al actualizar favoritos'
            });
        } else if (typeof showToast === 'function') {
            showToast(error.message || 'Error al actualizar favoritos', 'error');
        }
        
        return false;
    }
}

/**
 * Actualizar cantidad en el carrito
 */
async function updateCartQuantity(cartId, quantity) {
    try {
        const data = await fetchPOST('app/actions/update_cart_quantity.php', {
            id_carrito: cartId,
            cantidad: quantity
        });

        if (data.success) {
            // Actualizar totales en la UI
            if (data.totals) {
                updateCartTotals(data.totals);
            }

            // Disparar evento
            document.dispatchEvent(new CustomEvent('cartQuantityUpdated', {
                detail: { cartId, quantity }
            }));

            return true;
        } else {
            throw new Error(data.message || 'Error al actualizar cantidad');
        }
    } catch (error) {
        console.error('Error updating cart quantity:', error);
        
        if (typeof Toast !== 'undefined') {
            Toast.fire({
                icon: 'error',
                title: error.message || 'Error al actualizar cantidad'
            });
        }
        
        return false;
    }
}

/**
 * Eliminar producto del carrito
 */
async function removeFromCart(cartId) {
    try {
        // Confirmar con el usuario
        if (typeof Swal !== 'undefined') {
            const result = await Swal.fire({
                title: '¿Eliminar producto?',
                text: '¿Estás seguro de eliminar este producto del carrito?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });

            if (!result.isConfirmed) {
                return false;
            }
        }

        const data = await fetchPOST('app/actions/remove_from_cart.php', {
            id_carrito: cartId
        });

        if (data.success) {
            // Actualizar contador del carrito
            await updateCartCount();

            // Eliminar fila de la tabla
            const row = document.querySelector(`tr[data-cart-id="${cartId}"]`);
            if (row) {
                row.remove();
            }

            // Actualizar totales
            if (data.totals) {
                updateCartTotals(data.totals);
            }

            // Mostrar notificación
            if (typeof Toast !== 'undefined') {
                Toast.fire({
                    icon: 'success',
                    title: 'Producto eliminado del carrito'
                });
            }

            // Disparar evento
            document.dispatchEvent(new CustomEvent('cartItemRemoved', {
                detail: { cartId }
            }));

            return true;
        } else {
            throw new Error(data.message || 'Error al eliminar producto');
        }
    } catch (error) {
        console.error('Error removing from cart:', error);
        
        if (typeof Toast !== 'undefined') {
            Toast.fire({
                icon: 'error',
                title: error.message || 'Error al eliminar producto'
            });
        }
        
        return false;
    }
}

/**
 * Actualizar totales del carrito en la UI
 */
function updateCartTotals(totals) {
    // Actualizar subtotal
    const subtotalElements = document.querySelectorAll('.cart-subtotal');
    subtotalElements.forEach(el => {
        el.textContent = `$${parseFloat(totals.subtotal).toFixed(2)}`;
    });

    // Actualizar descuento
    const discountElements = document.querySelectorAll('.cart-discount');
    discountElements.forEach(el => {
        el.textContent = `-$${parseFloat(totals.discount).toFixed(2)}`;
    });

    // Actualizar total
    const totalElements = document.querySelectorAll('.cart-total');
    totalElements.forEach(el => {
        el.textContent = `$${parseFloat(totals.total).toFixed(2)}`;
    });
}

/**
 * Cargar productos filtrados usando Fetch API
 */
async function loadFilteredProducts(filters = {}) {
    try {
        // Mostrar indicador de carga
        const container = document.getElementById('productosGrid');
        if (container) {
            container.innerHTML = '<div class="col-12 text-center" style="padding: 60px;"><i class="fa fa-spinner fa-spin fa-3x"></i></div>';
        }

        const data = await fetchGET('app/actions/get_products_filtered.php', filters);

        if (data.success && container) {
            // Actualizar HTML de productos
            container.innerHTML = data.html;

            // Reinicializar AOS si está disponible
            if (typeof AOS !== 'undefined') {
                AOS.refresh();
            }

            // Disparar evento de contenido actualizado
            document.dispatchEvent(new CustomEvent('productsUpdated', {
                detail: { filters, count: data.count }
            }));

            return true;
        } else {
            throw new Error(data.message || 'Error al cargar productos');
        }
    } catch (error) {
        console.error('Error loading filtered products:', error);
        
        const container = document.getElementById('productosGrid');
        if (container) {
            container.innerHTML = '<div class="col-12"><div class="alert alert-danger">Error al cargar productos</div></div>';
        }
        
        return false;
    }
}

/**
 * Búsqueda en tiempo real de productos
 */
let searchTimeout;
function searchProducts(query) {
    clearTimeout(searchTimeout);
    
    searchTimeout = setTimeout(async () => {
        try {
            await loadFilteredProducts({ q: query });
        } catch (error) {
            console.error('Error searching products:', error);
        }
    }, 500); // Debounce de 500ms
}

// ============================================
// AUTO-INICIALIZACIÓN
// ============================================

document.addEventListener('DOMContentLoaded', function() {

    // Auto-actualizar contadores cada 30 segundos
    setInterval(() => {
        updateCartCount();
        updateFavoritesCount();
    }, 30000);

    // Escuchar eventos de actualización
    document.addEventListener('cartUpdated', () => {
    });

    document.addEventListener('favoritesUpdated', () => {
    });
});

// Exportar funciones globalmente
window.fetchAPI = fetchAPI;
window.fetchGET = fetchGET;
window.fetchPOST = fetchPOST;
window.fetchPOSTFormData = fetchPOSTFormData;
window.addToCart = addToCart;
window.toggleFavorite = toggleFavorite;
window.updateCartQuantity = updateCartQuantity;
window.removeFromCart = removeFromCart;
window.updateCartCount = updateCartCount;
window.updateFavoritesCount = updateFavoritesCount;
window.loadFilteredProducts = loadFilteredProducts;
window.searchProducts = searchProducts;
