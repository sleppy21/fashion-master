/**
 * SHOP FILTERS - Maneja filtros del sidebar
 * @version 3.0
 * 
 * RESPONSABILIDAD: Detectar cambios en filtros del sidebar y actualizar ProductFilters
 * NO carga productos directamente, solo actualiza estado
 */

(function() {
    'use strict';
    
    // =============================================
    // EVENT LISTENERS: FILTROS DEL SIDEBAR
    // =============================================
    
    // Filtros de categoría (multi-select con chips)
    document.addEventListener('click', function(e) {
        const chip = e.target.closest('[data-filter-type="categoria"]');
        if (!chip) return;
        
        const multiSelect = chip.getAttribute('data-multi-select') === 'true';
        if (!multiSelect) return;
        
        e.preventDefault();
        const catId = chip.getAttribute('data-filter-value');
        
        // Toggle categoría en el array
        const currentCats = window.ProductFilters.categorias || [];
        const index = currentCats.indexOf(catId);
        
        if (index > -1) {
            // Remover
            currentCats.splice(index, 1);
            chip.classList.remove('active');
        } else {
            // Agregar
            currentCats.push(catId);
            chip.classList.add('active');
        }
        
        window.ProductFilters.categorias = currentCats;
        
        // Disparar evento y cargar
        document.dispatchEvent(new Event('filtersChanged'));
        if (window.loadProducts) {
            window.loadProducts();
        }
    });
    
    // Filtros de género
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('[data-filter-type="genero"]');
        if (!btn) return;
        
        // Verificar que no sea multi-select
        const multiSelect = btn.getAttribute('data-multi-select') === 'true';
        if (multiSelect) return;
        
        e.preventDefault();
        const genero = btn.getAttribute('data-filter-value');
        
        // Actualizar estado
        window.ProductFilters.genero = genero === 'all' ? null : genero;
        
        // Actualizar UI
        document.querySelectorAll('[data-filter-type="genero"]').forEach(b => {
            b.classList.remove('active');
        });
        btn.classList.add('active');
        
        // Disparar evento y cargar
        document.dispatchEvent(new Event('filtersChanged'));
        if (window.loadProducts) {
            window.loadProducts();
        }
    });
    
    // Filtros de marca
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('[data-filter-type="marca"]');
        if (!btn) return;
        
        // Verificar que no sea multi-select
        const multiSelect = btn.getAttribute('data-multi-select') === 'true';
        if (multiSelect) return;
        
        e.preventDefault();
        const marcaId = btn.getAttribute('data-filter-value');
        
        // Actualizar estado
        window.ProductFilters.marca = marcaId === '0' ? null : parseInt(marcaId);
        
        // Actualizar UI
        document.querySelectorAll('[data-filter-type="marca"]').forEach(b => {
            b.classList.remove('active');
        });
        btn.classList.add('active');
        
        // Disparar evento y cargar
        document.dispatchEvent(new Event('filtersChanged'));
        if (window.loadProducts) {
            window.loadProducts();
        }
    });
    
    // Ordenamiento
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('[data-filter-type="ordenar"]');
        if (!btn) return;
        
        e.preventDefault();
        const sortValue = btn.getAttribute('data-filter-value');
        
        // Actualizar estado
        window.ProductFilters.ordenar = sortValue;
        
        // Actualizar UI
        document.querySelectorAll('[data-filter-type="ordenar"]').forEach(b => {
            b.classList.remove('active');
        });
        btn.classList.add('active');
        
        // Cargar productos
        if (window.loadProducts) {
            window.loadProducts();
        }
    });
    
    // =============================================
    // LIMPIAR FILTROS
    // =============================================
    
    window.limpiarFiltros = function() {
        // Resetear estado
        window.ProductFilters = {
            categorias: [],
            genero: null,
            marca: null,
            precio_min: 0,
            precio_max: 10000,
            buscar: '',
            ordenar: 'newest'
        };
        
        // Resetear UI de chips
        document.querySelectorAll('.filter-chip.active').forEach(chip => {
            chip.classList.remove('active');
        });
        
        // Resetear checkboxes
        document.querySelectorAll('.filter-checkbox').forEach(cb => {
            cb.checked = false;
        });
        
        // Activar botones por defecto
        const todosGenero = document.querySelector('[data-filter-type="genero"][data-filter-value="all"]');
        if (todosGenero) todosGenero.classList.add('active');
        
        const todasMarcas = document.querySelector('[data-filter-type="marca"][data-filter-value="0"]');
        if (todasMarcas) todasMarcas.classList.add('active');
        
        const newestSort = document.querySelector('[data-filter-type="ordenar"][data-filter-value="newest"]');
        if (newestSort) newestSort.classList.add('active');
        
        // Disparar evento y cargar
        document.dispatchEvent(new Event('filtersChanged'));
        if (window.loadProducts) {
            window.loadProducts();
        }
    };
    
    // Botón de limpiar filtros
    const btnClearFilters = document.getElementById('clearFilters');
    if (btnClearFilters) {
        btnClearFilters.addEventListener('click', function(e) {
            e.preventDefault();
            window.limpiarFiltros();
        });
    }
    
    // =============================================
    // BÚSQUEDA EN TIEMPO REAL
    // =============================================
    
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            
            searchTimeout = setTimeout(() => {
                const query = this.value.trim();
                window.ProductFilters.buscar = query;
                
                if (window.loadProducts) {
                    window.loadProducts();
                }
            }, 500); // Debounce de 500ms
        });
    }
    
    // =============================================
    // SINCRONIZAR ESTADO INICIAL DESDE URL
    // =============================================
    
    function syncInitialState() {
        // Sincronizar categorías
        const categorias = window.ProductFilters.categorias || [];
        document.querySelectorAll('[data-filter-type="categoria"]').forEach(chip => {
            const chipValue = chip.getAttribute('data-filter-value');
            if (categorias.includes(chipValue)) {
                chip.classList.add('active');
            }
        });
        
        // Sincronizar género
        if (window.ProductFilters.genero) {
            const generoBtn = document.querySelector(`[data-filter-type="genero"][data-filter-value="${window.ProductFilters.genero}"]`);
            if (generoBtn) {
                document.querySelectorAll('[data-filter-type="genero"]').forEach(b => b.classList.remove('active'));
                generoBtn.classList.add('active');
            }
        }
        
        // Sincronizar marca
        if (window.ProductFilters.marca) {
            const marcaBtn = document.querySelector(`[data-filter-type="marca"][data-filter-value="${window.ProductFilters.marca}"]`);
            if (marcaBtn) {
                document.querySelectorAll('[data-filter-type="marca"]').forEach(b => b.classList.remove('active'));
                marcaBtn.classList.add('active');
            }
        }
        
        // Sincronizar ordenamiento
        const sortBtn = document.querySelector(`[data-filter-type="ordenar"][data-filter-value="${window.ProductFilters.ordenar}"]`);
        if (sortBtn) {
            document.querySelectorAll('[data-filter-type="ordenar"]').forEach(b => b.classList.remove('active'));
            sortBtn.classList.add('active');
        }
    }
    
    // =============================================
    // INICIALIZACIÓN
    // =============================================
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', syncInitialState);
    } else {
        syncInitialState();
    }
    
    console.log('✅ Shop Filters inicializado');
    
})();
