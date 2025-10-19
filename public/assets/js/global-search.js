/**
 * GLOBAL SEARCH DROPDOWN
 * Búsqueda simple de productos
 */

document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    // Elementos
    const modal = document.getElementById('global-search-modal');
    const trigger = document.getElementById('global-search-trigger');
    const searchInput = document.getElementById('global-search-input');
    const clearBtn = document.getElementById('search-clear-btn');
    const productsList = document.getElementById('search-products-list');
    const loadingState = document.getElementById('search-loading');
    const noResultsState = document.getElementById('search-no-results');

    let searchTimeout = null;

    console.log('🔍 Global Search Inicializado', {
        modal: !!modal,
        trigger: !!trigger,
        input: !!searchInput
    });

    if (!modal || !trigger || !searchInput) {
        console.error('❌ Elementos no encontrados');
        return;
    }

    // ========================================
    // ABRIR/CERRAR
    // ========================================
    function abrirBuscador() {
        console.log('✅ Abriendo buscador');
        modal.classList.add('active');
        setTimeout(() => searchInput.focus(), 100);
    }

    function cerrarBuscador() {
        console.log('🔒 Cerrando buscador');
        modal.classList.remove('active');
        searchInput.value = '';
        limpiarResultados();
    }

    // ========================================
    // LIMPIAR RESULTADOS
    // ========================================
    function limpiarResultados() {
        productsList.style.display = 'none';
        loadingState.style.display = 'none';
        noResultsState.style.display = 'none';
        productsList.innerHTML = '';
    }

    // ========================================
    // BUSCAR PRODUCTOS
    // ========================================
    function buscarProductos(query) {
        clearTimeout(searchTimeout);
        
        if (query.length === 0) {
            limpiarResultados();
            clearBtn.style.display = 'none';
            return;
        }

        clearBtn.style.display = 'flex';

        // Mostrar loading
        limpiarResultados();
        loadingState.style.display = 'block';

        searchTimeout = setTimeout(() => {
            console.log('🔎 Buscando:', query);
            console.log('🌐 URL:', `app/actions/global_search.php?q=${encodeURIComponent(query)}`);
            
            fetch(`app/actions/global_search.php?q=${encodeURIComponent(query)}`)
                .then(response => {
                    console.log('📡 Respuesta recibida, status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}`);
                    }
                    return response.text();
                })
                .then(text => {
                    console.log('📄 Texto recibido:', text);
                    const data = JSON.parse(text);
                    console.log('📦 Datos parseados:', data);
                    loadingState.style.display = 'none';
                    
                    if (data.products && data.products.length > 0) {
                        mostrarProductos(data.products);
                    } else {
                        console.log('⚠️ No hay productos');
                        noResultsState.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('❌ Error completo:', error);
                    loadingState.style.display = 'none';
                    noResultsState.style.display = 'block';
                });
        }, 300);
    }

    // ========================================
    // MOSTRAR PRODUCTOS
    // ========================================
    function mostrarProductos(products) {
        console.log('📋 Mostrando', products.length, 'productos');
        productsList.innerHTML = '';
        
        products.forEach(product => {
            const item = document.createElement('a');
            item.className = 'search-product-item';
            item.href = `product-details.php?id=${product.id}`;
            
            const oldPriceHtml = product.precio_anterior 
                ? `<span class="search-product-old-price">S/ ${parseFloat(product.precio_anterior).toFixed(2)}</span>`
                : '';
            
            item.innerHTML = `
                <img src="${product.imagen}" 
                     alt="${product.nombre}" 
                     class="search-product-image" 
                     onerror="this.src='public/assets/images/default-product.png'">
                <div class="search-product-info">
                    <div class="search-product-name">${product.nombre}</div>
                    <div class="search-product-meta">
                        <span class="search-product-price">S/ ${parseFloat(product.precio).toFixed(2)}</span>
                        ${oldPriceHtml}
                        <span class="search-product-category">${product.categoria}</span>
                    </div>
                </div>
            `;
            
            productsList.appendChild(item);
        });
        
        productsList.style.display = 'block';
    }

    // ========================================
    // EVENTOS
    // ========================================
    
    // Click en trigger
    trigger.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('🖱️ Click en trigger');
        
        if (modal.classList.contains('active')) {
            cerrarBuscador();
        } else {
            abrirBuscador();
        }
    });

    // Input de búsqueda
    searchInput.addEventListener('input', function(e) {
        const query = e.target.value.trim();
        console.log('⌨️ Input:', query);
        buscarProductos(query);
    });

    // Botón limpiar
    clearBtn.addEventListener('click', function() {
        console.log('🗑️ Limpiar búsqueda');
        searchInput.value = '';
        clearBtn.style.display = 'none';
        limpiarResultados();
        searchInput.focus();
    });

    // Cerrar al hacer click fuera
    document.addEventListener('click', function(e) {
        if (modal.classList.contains('active')) {
            if (!modal.contains(e.target) && e.target !== trigger) {
                console.log('🔒 Click fuera - cerrando');
                cerrarBuscador();
            }
        }
    });

    // Cerrar con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.classList.contains('active')) {
            console.log('⌨️ ESC - cerrando');
            cerrarBuscador();
        }
    });

    console.log('✅ Global Search listo!');
});
