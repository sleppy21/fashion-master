/**
 * CATEGORIES NAVBAR - Maneja el carousel de categorías
 * @version 3.0
 * 
 * RESPONSABILIDAD: Detectar clics en navbar y actualizar ProductFilters
 * NO carga productos directamente, solo actualiza estado
 */

(function() {
    'use strict';
    
    const navbar = document.querySelector('.categories-navbar-list');
    if (!navbar) return;
    
    // =============================================
    // SINCRONIZAR ESTADO VISUAL
    // =============================================
    
    function syncVisualState() {
        const categorias = window.ProductFilters.categorias || [];
        
        // Actualizar botones del navbar
        const navbarButtons = navbar.querySelectorAll('button[data-id]');
        navbarButtons.forEach(btn => {
            const btnId = btn.getAttribute('data-id');
            const li = btn.closest('li');
            if (!li) return;
            
            // Si hay una sola categoría activa
            if (categorias.length === 1 && btnId == categorias[0]) {
                li.classList.add('active');
            }
            // Si es "Todos" (0) y no hay categorías, O si categorias es vacío al inicio
            else if (btnId === '0' && categorias.length === 0) {
                li.classList.add('active');
            }
            // Desactivar el resto
            else {
                li.classList.remove('active');
            }
        });
        
        // Sincronizar chips del sidebar
        const sidebarChips = document.querySelectorAll('[data-filter-type="categoria"]');
        sidebarChips.forEach(chip => {
            const chipValue = chip.getAttribute('data-filter-value');
            
            if (categorias.includes(chipValue)) {
                chip.classList.add('active');
            } else {
                chip.classList.remove('active');
            }
        });
    }
    
    // =============================================
    // EVENT LISTENER: CLICK EN NAVBAR
    // =============================================
    
    navbar.addEventListener('click', function(e) {
        const btn = e.target.closest('button[data-id]');
        if (!btn) return;
        
        e.preventDefault();
        const catId = btn.getAttribute('data-id');
        
        // Actualizar ProductFilters
        if (catId === '0') {
            // "Todos" - limpiar categorías
            window.ProductFilters.categorias = [];
        } else {
            // Selección única desde navbar
            window.ProductFilters.categorias = [catId];
        }
        
        // Sincronizar visual
        syncVisualState();
        
        // Cargar productos
        if (window.loadProducts) {
            window.loadProducts();
        }
    });
    
    // =============================================
    // ESCUCHAR CAMBIOS DESDE SIDEBAR
    // =============================================
    
    document.addEventListener('filtersChanged', function() {
        syncVisualState();
    });
    
    // =============================================
    // SINCRONIZAR ESTADO INICIAL
    // =============================================
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', syncVisualState);
    } else {
        syncVisualState();
    }
    
    console.log('✅ Categories Navbar inicializado');
    
})();
