/**
 * DARK MODE TOGGLE
 * Sistema de cambio de tema oscuro/claro
 */

(function() {
    'use strict';

    // Actualizar icono del botón
    function updateToggleIcon(isDark) {
        const icon = document.querySelector('#dark-mode-toggle i');
        if (icon) {
            if (isDark) {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
            } else {
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
            }
        }
    }

    // Limpiar inline styles que interfieren con dark mode
    function cleanModalInlineStyles() {
        const isDark = document.body.classList.contains('dark-mode');
        
        if (isDark) {
            // Lista de selectores a limpiar
            const selectors = [
                '.favorites-modal-content',
                '.favorites-modal-header',
                '.favorites-modal-body',
                '.favorite-item',
                '.favorite-product-name',
                '.price-current',
                '.price-old',
                '.favorite-date',
                '.btn-favorite-cart',
                '.btn-favorite-remove',
                '.stock-badge',
                '.favorites-empty i',
                '.favorites-empty p',
                '.btn-shop-now',
                '.user-modal-content',
                '.user-modal-header',
                '.user-modal-body'
            ];
            
            selectors.forEach(selector => {
                const elements = document.querySelectorAll(selector);
                elements.forEach(el => {
                    // Remover propiedades de color del inline style
                    if (el.style) {
                        el.style.removeProperty('background');
                        el.style.removeProperty('background-color');
                        el.style.removeProperty('color');
                        el.style.removeProperty('border-color');
                    }
                });
            });
        }
    }

    // Verificar preferencia guardada o preferencia del sistema
    const savedTheme = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    // Aplicar tema inicial
    if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
        document.body.classList.add('dark-mode');
        updateToggleIcon(true);
        // Limpiar inline styles al cargar si ya está en dark mode
        setTimeout(() => cleanModalInlineStyles(), 100);
    }

    // Event listener para el botón de toggle (verificar que existe)
    const toggleBtn = document.getElementById('dark-mode-toggle');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            toggleDarkMode();
        });
    } else {
        console.warn('⚠️ Dark mode toggle button not found');
    }

    // Limpiar inline styles cuando la página carga
    window.addEventListener('DOMContentLoaded', function() {
        cleanModalInlineStyles();
    });

    // También limpiar cuando se abren los modales
    document.addEventListener('click', function(e) {
        if (e.target.closest('#favorites-link') || e.target.closest('#user-account-link')) {
            setTimeout(cleanModalInlineStyles, 100);
        }
    });

    // Observar cambios en el modal de favoritos (cuando se actualiza dinámicamente)
    const observeModalChanges = () => {
        const favoritesModal = document.getElementById('favorites-list');
        if (favoritesModal) {
            const observer = new MutationObserver(function(mutations) {
                if (document.body.classList.contains('dark-mode')) {
                    cleanModalInlineStyles();
                }
            });
            
            observer.observe(favoritesModal, {
                childList: true,
                subtree: true
            });
            
            console.log('✅ Modal observer initialized');
        }
    };

    // Inicializar observer cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', observeModalChanges);
    } else {
        observeModalChanges();
    }

    // Función para cambiar el modo
    function toggleDarkMode() {
        const isDark = document.body.classList.toggle('dark-mode');
        
        // Guardar preferencia
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        
        // Actualizar icono
        updateToggleIcon(isDark);
        
        // Animación suave
        document.body.style.transition = 'background-color 0.3s ease, color 0.3s ease';
        
        // Limpiar inline styles de modales
        cleanModalInlineStyles();
        
        console.log('Modo oscuro:', isDark ? 'Activado' : 'Desactivado');
    }

    // Detectar cambios en la preferencia del sistema
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
        if (!localStorage.getItem('theme')) {
            if (e.matches) {
                document.body.classList.add('dark-mode');
                updateToggleIcon(true);
                cleanModalInlineStyles();
            } else {
                document.body.classList.remove('dark-mode');
                updateToggleIcon(false);
            }
        }
    });

    console.log('✅ Dark Mode system initialized');
})();
