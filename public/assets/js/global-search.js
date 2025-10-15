/**
 * GLOBAL SEARCH MODAL
 * Búsqueda global con productos y atajos de navegación
 * @version 1.0
 */

document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    // ========================================
    // NAVIGATION SHORTCUTS MAPPING
    // ========================================
    const navigationShortcuts = {
        'carrito': { url: 'cart.php', icon: 'fa-shopping-cart', title: 'Carrito de Compras', desc: 'Ver tus productos en el carrito' },
        'cart': { url: 'cart.php', icon: 'fa-shopping-cart', title: 'Carrito de Compras', desc: 'Ver tus productos en el carrito' },
        
        'tienda': { url: 'shop.php', icon: 'fa-store', title: 'Tienda', desc: 'Explorar todos los productos' },
        'shop': { url: 'shop.php', icon: 'fa-store', title: 'Tienda', desc: 'Explorar todos los productos' },
        
        'favoritos': { url: '#', modal: 'favorites', icon: 'fa-heart', title: 'Favoritos', desc: 'Ver tus productos favoritos' },
        'favorites': { url: '#', modal: 'favorites', icon: 'fa-heart', title: 'Favoritos', desc: 'Ver tus productos favoritos' },
        
        'notificaciones': { url: '#', modal: 'notifications', icon: 'fa-bell', title: 'Notificaciones', desc: 'Ver tus notificaciones' },
        'notifications': { url: '#', modal: 'notifications', icon: 'fa-bell', title: 'Notificaciones', desc: 'Ver tus notificaciones' },
        
        'perfil': { url: 'profile.php', icon: 'fa-user', title: 'Mi Perfil', desc: 'Ver y editar tu perfil' },
        'profile': { url: 'profile.php', icon: 'fa-user', title: 'Mi Perfil', desc: 'Ver y editar tu perfil' },
        
        'configuracion': { url: 'profile.php#settings', icon: 'fa-cog', title: 'Configuración', desc: 'Ajustes de tu cuenta' },
        'settings': { url: 'profile.php#settings', icon: 'fa-cog', title: 'Configuración', desc: 'Ajustes de tu cuenta' },
        'ajustes': { url: 'profile.php#settings', icon: 'fa-cog', title: 'Configuración', desc: 'Ajustes de tu cuenta' },
        
        'admin': { url: 'admin.php', icon: 'fa-shield-alt', title: 'Administración', desc: 'Panel de administrador' },
        'administracion': { url: 'admin.php', icon: 'fa-shield-alt', title: 'Administración', desc: 'Panel de administrador' },
        
        'contacto': { url: 'contact.php', icon: 'fa-envelope', title: 'Contacto', desc: 'Envíanos un mensaje' },
        'contact': { url: 'contact.php', icon: 'fa-envelope', title: 'Contacto', desc: 'Envíanos un mensaje' },
        
        'direccion': { url: 'profile.php#addresses', icon: 'fa-map-marker-alt', title: 'Direcciones', desc: 'Gestionar tus direcciones' },
        'address': { url: 'profile.php#addresses', icon: 'fa-map-marker-alt', title: 'Direcciones', desc: 'Gestionar tus direcciones' },
        
        'seguridad': { url: 'profile.php#security', icon: 'fa-lock', title: 'Seguridad', desc: 'Cambiar contraseña y seguridad' },
        'security': { url: 'profile.php#security', icon: 'fa-lock', title: 'Seguridad', desc: 'Cambiar contraseña y seguridad' },
        
        'info': { url: 'profile.php#info', icon: 'fa-info-circle', title: 'Información', desc: 'Información de la cuenta' },
        'informacion': { url: 'profile.php#info', icon: 'fa-info-circle', title: 'Información', desc: 'Información de la cuenta' }
    };

    // ========================================
    // ELEMENTS
    // ========================================
    const modal = document.getElementById('global-search-modal');
    const trigger = document.getElementById('global-search-trigger');
    const closeBtn = document.getElementById('search-modal-close-btn');
    const overlay = document.querySelector('.search-modal-overlay');
    const searchInput = document.getElementById('global-search-input');
    const clearBtn = document.getElementById('search-clear-btn');
    
    const navResultsSection = document.getElementById('nav-results-section');
    const navResults = document.getElementById('search-nav-results');
    const productsResultsSection = document.getElementById('products-results-section');
    const productsResults = document.getElementById('search-products-results');
    const loadingState = document.getElementById('search-loading');
    const noResultsState = document.getElementById('search-no-results');

    let searchTimeout = null;

    // Debug: Verificar que los elementos existen
    console.log('Global Search - Elements found:', {
        modal: !!modal,
        trigger: !!trigger,
        closeBtn: !!closeBtn,
        searchInput: !!searchInput
    });

    if (!modal || !trigger) {
        console.error('Global Search - Required elements not found!');
        return;
    }

    // ========================================
    // OPEN/CLOSE MODAL
    // ========================================
    function openModal() {
        console.log('Opening modal...');
        modal.style.display = 'flex';
        setTimeout(() => {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            searchInput.focus();
        }, 10);
    }

    function closeModal() {
        console.log('Closing modal...');
        modal.classList.remove('active');
        document.body.style.overflow = '';
        searchInput.value = '';
        setTimeout(() => {
            modal.style.display = 'none';
        }, 200); // Esperar a que termine la animación
        clearResults();
    }

    // ========================================
    // CLEAR RESULTS
    // ========================================
    function clearResults() {
        navResultsSection.style.display = 'none';
        productsResultsSection.style.display = 'none';
        loadingState.style.display = 'none';
        noResultsState.style.display = 'none';
        navResults.innerHTML = '';
        productsResults.innerHTML = '';
    }

    // ========================================
    // SEARCH FUNCTION
    // ========================================
    function performSearch(query) {
        clearTimeout(searchTimeout);
        
        if (query.length === 0) {
            clearResults();
            return;
        }

        // Show loading
        clearResults();
        loadingState.style.display = 'flex';

        searchTimeout = setTimeout(() => {
            const queryLower = query.toLowerCase().trim();
            
            // Check navigation shortcuts first
            const matchedShortcuts = [];
            for (const [keyword, data] of Object.entries(navigationShortcuts)) {
                if (keyword.includes(queryLower) || queryLower.includes(keyword)) {
                    matchedShortcuts.push({ keyword, ...data });
                }
            }

            // If exact navigation match, navigate directly
            if (navigationShortcuts[queryLower]) {
                const shortcut = navigationShortcuts[queryLower];
                if (shortcut.modal) {
                    closeModal();
                    openModalById(shortcut.modal);
                } else {
                    window.location.href = shortcut.url;
                }
                return;
            }

            // Search products via AJAX
            searchProducts(query, matchedShortcuts);
        }, 300);
    }

    // ========================================
    // SEARCH PRODUCTS
    // ========================================
    function searchProducts(query, navigationMatches) {
        fetch(`app/actions/global_search.php?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                loadingState.style.display = 'none';
                
                const hasNav = navigationMatches.length > 0;
                const hasProducts = data.products && data.products.length > 0;

                if (!hasNav && !hasProducts) {
                    noResultsState.style.display = 'flex';
                    return;
                }

                // Show navigation results
                if (hasNav) {
                    displayNavigationResults(navigationMatches);
                }

                // Show product results
                if (hasProducts) {
                    displayProductResults(data.products);
                }
            })
            .catch(error => {
                console.error('Search error:', error);
                loadingState.style.display = 'none';
                noResultsState.style.display = 'flex';
            });
    }

    // ========================================
    // DISPLAY NAVIGATION RESULTS
    // ========================================
    function displayNavigationResults(shortcuts) {
        navResults.innerHTML = '';
        
        shortcuts.forEach(shortcut => {
            const item = document.createElement('a');
            item.className = 'search-nav-item';
            item.href = shortcut.url;
            
            if (shortcut.modal) {
                item.addEventListener('click', (e) => {
                    e.preventDefault();
                    closeModal();
                    openModalById(shortcut.modal);
                });
            }
            
            item.innerHTML = `
                <div class="search-nav-icon">
                    <i class="fa ${shortcut.icon}"></i>
                </div>
                <div class="search-nav-info">
                    <div class="search-nav-title">${shortcut.title}</div>
                    <div class="search-nav-desc">${shortcut.desc}</div>
                </div>
                <i class="fa fa-chevron-right search-nav-arrow"></i>
            `;
            
            navResults.appendChild(item);
        });
        
        navResultsSection.style.display = 'block';
    }

    // ========================================
    // DISPLAY PRODUCT RESULTS
    // ========================================
    function displayProductResults(products) {
        productsResults.innerHTML = '';
        
        products.forEach(product => {
            const item = document.createElement('a');
            item.className = 'search-product-item';
            item.href = `product-details.php?id=${product.id}`;
            
            const oldPriceHtml = product.precio_anterior 
                ? `<span class="search-product-old-price">S/ ${parseFloat(product.precio_anterior).toFixed(2)}</span>`
                : '';
            
            item.innerHTML = `
                <img src="${product.imagen}" alt="${product.nombre}" class="search-product-image" onerror="this.src='public/assets/images/default-product.png'">
                <div class="search-product-info">
                    <div class="search-product-name">${product.nombre}</div>
                    <div class="search-product-meta">
                        <span class="search-product-price">S/ ${parseFloat(product.precio).toFixed(2)}</span>
                        ${oldPriceHtml}
                        <span class="search-product-category">${product.categoria}</span>
                    </div>
                </div>
            `;
            
            productsResults.appendChild(item);
        });
        
        productsResultsSection.style.display = 'block';
    }

    // ========================================
    // OPEN MODAL BY ID
    // ========================================
    function openModalById(modalType) {
        const modals = {
            'favorites': document.getElementById('favorites-modal'),
            'notifications': document.getElementById('notifications-modal')
        };
        
        if (modals[modalType]) {
            modals[modalType].classList.add('active');
        }
    }

    // ========================================
    // QUICK ACTIONS
    // ========================================
    document.querySelectorAll('.quick-action-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const action = btn.dataset.action;
            
            switch(action) {
                case 'cart':
                    window.location.href = 'cart.php';
                    break;
                case 'shop':
                    window.location.href = 'shop.php';
                    break;
                case 'favorites':
                    closeModal();
                    openModalById('favorites');
                    break;
                case 'notifications':
                    closeModal();
                    openModalById('notifications');
                    break;
                case 'profile':
                    window.location.href = 'profile.php';
                    break;
                case 'settings':
                    window.location.href = 'profile.php#settings';
                    break;
                case 'admin':
                    window.location.href = 'admin.php';
                    break;
                case 'contact':
                    window.location.href = 'contact.php';
                    break;
            }
        });
    });

    // ========================================
    // EVENT LISTENERS
    // ========================================
    trigger?.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        console.log('Global Search - Trigger clicked!');
        openModal();
    });

    closeBtn?.addEventListener('click', closeModal);
    overlay?.addEventListener('click', closeModal);

    searchInput?.addEventListener('input', (e) => {
        const query = e.target.value;
        performSearch(query);
        
        // Show/hide clear button
        clearBtn.style.display = query.length > 0 ? 'flex' : 'none';
    });

    clearBtn?.addEventListener('click', () => {
        searchInput.value = '';
        clearBtn.style.display = 'none';
        clearResults();
        searchInput.focus();
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', (e) => {
        // Ctrl/Cmd + K to open search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            openModal();
        }
        
        // Escape to close
        if (e.key === 'Escape' && modal.classList.contains('active')) {
            closeModal();
        }
    });

});
