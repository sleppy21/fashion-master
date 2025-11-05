// JS para el navbar de categorías: filtra productos en shop.php al hacer click
// Requiere que el backend acepte el filtro por GET (ya lo hace)

document.addEventListener('DOMContentLoaded', function() {
    const navbar = document.querySelector('.categories-navbar-list');
    const productsContainer = document.getElementById('products-container');
    if (!productsContainer) return;

    // Función para cargar productos (opcionalmente por categoría)
    function cargarProductos(catId = null) {
        productsContainer.innerHTML = '<div class="shop-loader" style="text-align:center;padding:40px 0;"><i class="fa fa-spinner fa-spin fa-2x"></i></div>';
        const params = new URLSearchParams();
        if (catId && catId !== '0') params.set('c', catId);
        fetch('app/actions/get_products_filtered.php?' + params.toString(), {
            method: 'GET',
            credentials: 'same-origin'
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.html) {
                productsContainer.innerHTML = data.html;
            } else {
                productsContainer.innerHTML = '<div class="col-12"><div class="no-products-found"><h2>No se encontraron productos</h2></div></div>';
            }
            setTimeout(() => {
                document.dispatchEvent(new Event('productsUpdated'));
            }, 80);
        })
        .catch(() => {
            productsContainer.innerHTML = '<div class="col-12"><div class="no-products-found"><h2>Error al cargar productos</h2></div></div>';
            setTimeout(() => {
                document.dispatchEvent(new Event('productsUpdated'));
            }, 80);
        });
    }

    // Cargar productos al cargar la página (sin filtro)
    cargarProductos();

    // Filtro por categoría
    if (navbar) {
        navbar.addEventListener('click', function(e) {
            const btn = e.target.closest('button[data-id]');
            if (!btn) return;
            const catId = btn.getAttribute('data-id');
            navbar.querySelectorAll('button').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            cargarProductos(catId);
        });
    }
});
