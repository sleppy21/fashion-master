/**
 * DARK MODE TOGGLE - OPTIMIZADO SIN FLASH
 * Sistema de cambio de tema oscuro/claro con persistencia global
 * NOTA: El tema inicial ya fue aplicado por el script inline en dark-mode-assets.php
 */

(function() {
    'use strict';

    // Función para aplicar el tema
    function applyTheme(isDark) {
        if (isDark) {
            document.documentElement.classList.add('dark-mode');
            document.body.classList.add('dark-mode');
        } else {
            document.documentElement.classList.remove('dark-mode');
            document.body.classList.remove('dark-mode');
        }
        updateToggleIcon(isDark);
        cleanModalInlineStyles();
    }

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

    // Verificar estado actual del tema (ya aplicado por script inline)
    const isDarkMode = document.documentElement.classList.contains('dark-mode');
    
    // Esperar a que el DOM esté listo para actualizar icono y limpiar estilos
    document.addEventListener('DOMContentLoaded', function() {
        // Solo actualizar el icono, NO aplicar tema de nuevo (ya está aplicado)
        updateToggleIcon(isDarkMode);
        
        // Limpiar inline styles al cargar
        setTimeout(() => cleanModalInlineStyles(), 100);
    });

    // Event listener para el botón de toggle (MÚLTIPLES ESTRATEGIAS)
    const initToggleButton = () => {
        console.log('[Dark Mode] Intentando inicializar botón...');
        const toggleBtn = document.getElementById('dark-mode-toggle');
        
        if (toggleBtn) {
            console.log('[Dark Mode] ✅ Botón encontrado');
            
            // Remover listeners previos (evitar duplicados)
            const newBtn = toggleBtn.cloneNode(true);
            toggleBtn.parentNode.replaceChild(newBtn, toggleBtn);
            
            // Añadir listener al nuevo botón
            newBtn.addEventListener('click', function(e) {
                console.log('[Dark Mode] 🖱️ Click detectado');
                e.preventDefault();
                e.stopPropagation();
                toggleDarkMode();
            });
            
            console.log('[Dark Mode] ✅ Listener añadido correctamente');
        } else {
            console.warn('[Dark Mode] ⚠️ Botón no encontrado todavía');
        }
    };
    
    // ESTRATEGIA 1: DOMContentLoaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initToggleButton);
    } else {
        // ESTRATEGIA 2: DOM ya está listo
        initToggleButton();
    }
    
    // ESTRATEGIA 3: Reintentar después de 500ms (para AJAX/lazy loading)
    setTimeout(initToggleButton, 500);

    // También limpiar cuando se abren los modales
    document.addEventListener('click', function(e) {
        const target = e.target;
        if (target && (target.closest('#favorites-link') || target.closest('#user-account-link'))) {
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
        }
    };

    // Inicializar observer cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', observeModalChanges);
    } else {
        observeModalChanges();
    }

    // Función para cambiar el modo (CON PERSISTENCIA GLOBAL)
    function toggleDarkMode() {
        const isDark = document.body.classList.toggle('dark-mode');
        document.documentElement.classList.toggle('dark-mode');
        
        // ✅ Guardar preferencia en localStorage (se aplicará en TODAS las páginas)
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        
        // Actualizar icono
        updateToggleIcon(isDark);
        
        // Animación suave
        document.body.style.transition = 'background-color 0.3s ease, color 0.3s ease';
        
        // Limpiar inline styles de modales
        cleanModalInlineStyles();
        
        // 🔄 Disparar evento para sincronizar otras pestañas
        window.dispatchEvent(new CustomEvent('themeChanged', { 
            detail: { isDark: isDark } 
        }));
    }

    // 🔄 Escuchar cambios de tema desde otras pestañas/ventanas
    window.addEventListener('storage', function(e) {
        if (e.key === 'theme') {
            const newIsDark = e.newValue === 'dark';
            applyTheme(newIsDark);
        }
    });

    // ❌ DESACTIVADO: Listener de preferencia del sistema
    // Ya no usamos la preferencia del sistema para evitar cambios automáticos
    /*
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
        if (!localStorage.getItem('theme')) {
            if (e.matches) {
                document.documentElement.classList.add('dark-mode');
                document.body.classList.add('dark-mode');
                updateToggleIcon(true);
                cleanModalInlineStyles();
            } else {
                document.documentElement.classList.remove('dark-mode');
                document.body.classList.remove('dark-mode');
                updateToggleIcon(false);
            }
        }
    });
    */

})();
