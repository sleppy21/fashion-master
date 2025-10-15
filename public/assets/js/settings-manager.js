/**
 * SETTINGS MANAGER
 * Maneja la configuración personalizada del usuario con guardado automático
 */

(function() {
    'use strict';

    // Extraer color del shadow del avatar
    function extractAvatarColor() {
        // Primero intentar obtener el color del data-shadow-color de la imagen
        const avatarImg = document.querySelector('.avatar-image[data-shadow-color]');
        
        if (avatarImg && avatarImg.dataset.shadowColor) {
            const rgb = avatarImg.dataset.shadowColor.split(',').map(n => parseInt(n.trim()));
            
            if (rgb.length === 3) {
                const [r, g, b] = rgb;
                
                // Convertir RGB a HEX
                const toHex = (n) => {
                    const hex = n.toString(16);
                    return hex.length === 1 ? '0' + hex : hex;
                };
                
                const hexColor = `#${toHex(r)}${toHex(g)}${toHex(b)}`;
                console.log(`🎨 Color dominante extraído del avatar: ${hexColor} (RGB: ${r}, ${g}, ${b})`);
                return hexColor;
            }
        }
        
        // Si no hay imagen con data-shadow-color, intentar extraer del avatar-wrapper
        const avatar = document.querySelector('.profile-avatar[data-shadow-color]');
        if (avatar && avatar.dataset.shadowColor) {
            const rgb = avatar.dataset.shadowColor.split(',').map(n => parseInt(n.trim()));
            
            if (rgb.length === 3) {
                const [r, g, b] = rgb;
                
                const toHex = (n) => {
                    const hex = n.toString(16);
                    return hex.length === 1 ? '0' + hex : hex;
                };
                
                const hexColor = `#${toHex(r)}${toHex(g)}${toHex(b)}`;
                console.log(`🎨 Color dominante extraído del contenedor: ${hexColor} (RGB: ${r}, ${g}, ${b})`);
                return hexColor;
            }
        }
        
        // Fallback: intentar leer del box-shadow aplicado
        const avatarElement = document.querySelector('.profile-avatar, .avatar-circle');
        if (avatarElement) {
            const computedStyle = window.getComputedStyle(avatarElement);
            const boxShadow = computedStyle.boxShadow;
            
            // Extraer rgba del box-shadow
            const rgbaMatch = boxShadow.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/);
            
            if (rgbaMatch) {
                const r = parseInt(rgbaMatch[1]);
                const g = parseInt(rgbaMatch[2]);
                const b = parseInt(rgbaMatch[3]);
                
                const toHex = (n) => {
                    const hex = n.toString(16);
                    return hex.length === 1 ? '0' + hex : hex;
                };
                
                const hexColor = `#${toHex(r)}${toHex(g)}${toHex(b)}`;
                console.log(`🎨 Color extraído del box-shadow: ${hexColor}`);
                return hexColor;
            }
        }
        
        console.log('⚠️ No se pudo extraer color del avatar, usando color por defecto');
        return '#c9a67c'; // Color dorado por defecto
    }

    // Aplicar color dinámico a la página
    function applyDynamicColor(color) {
        document.documentElement.style.setProperty('--primary-color', color);
        
        // Crear estilos personalizados
        let styleElement = document.getElementById('dynamic-primary-color');
        if (!styleElement) {
            styleElement = document.createElement('style');
            styleElement.id = 'dynamic-primary-color';
            document.head.appendChild(styleElement);
        }
        
        // Calcular color más oscuro para gradientes
        const darkerColor = adjustColor(color, -20);
        
        styleElement.textContent = `
            :root {
                --primary-color: ${color};
            }
            .btn-primary,
            .btn-save-settings {
                background: linear-gradient(135deg, ${color} 0%, ${darkerColor} 100%) !important;
            }
            .theme-option input[type="radio"]:checked + .theme-card {
                border-color: ${color} !important;
            }
            .theme-option input[type="radio"]:checked + .theme-card i {
                color: ${color} !important;
            }
            input:checked + .slider {
                background-color: ${color} !important;
            }
            .settings-title i {
                color: ${color} !important;
            }
            .profile-avatar,
            .avatar-circle {
                box-shadow: 0 8px 24px ${color}4d !important;
            }
            .profile-stats-item {
                border-left-color: ${color} !important;
            }
        `;
    }

    // Ajustar brillo del color
    function adjustColor(color, percent) {
        const num = parseInt(color.replace("#", ""), 16);
        const amt = Math.round(2.55 * percent);
        const R = (num >> 16) + amt;
        const G = (num >> 8 & 0x00FF) + amt;
        const B = (num & 0x0000FF) + amt;
        return "#" + (0x1000000 + (R < 255 ? R < 1 ? 0 : R : 255) * 0x10000 +
            (G < 255 ? G < 1 ? 0 : G : 255) * 0x100 +
            (B < 255 ? B < 1 ? 0 : B : 255))
            .toString(16).slice(1);
    }

    // Cargar configuraciones guardadas del localStorage
    function loadSettings() {
        const settings = {
            theme_mode: localStorage.getItem('theme_mode') || 'light',
            push_notifications: localStorage.getItem('push_notifications') !== 'false',
            email_notifications: localStorage.getItem('email_notifications') !== 'false',
            promo_notifications: localStorage.getItem('promo_notifications') !== 'false',
            order_notifications: localStorage.getItem('order_notifications') !== 'false',
            language: localStorage.getItem('language') || 'es',
            currency: localStorage.getItem('currency') || 'PEN',
            save_cart: localStorage.getItem('save_cart') !== 'false',
            public_profile: localStorage.getItem('public_profile') === 'true',
            share_activity: localStorage.getItem('share_activity') === 'true'
        };
        
        return settings;
    }

    // Actualizar icono del header según el tema
    function updateHeaderIcon(theme) {
        const darkModeToggle = document.getElementById('dark-mode-toggle');
        if (!darkModeToggle) return;

        const icon = darkModeToggle.querySelector('i');
        if (!icon) return;

        // Cambiar icono según el tema
        icon.className = ''; // Limpiar clases
        
        if (theme === 'dark') {
            icon.className = 'fa fa-moon';
        } else if (theme === 'light') {
            icon.className = 'fa fa-sun';
        } else if (theme === 'auto') {
            icon.className = 'fa fa-palette';
        }
    }

    // Aplicar tema y guardar
    function applyTheme(themeMode) {
        localStorage.setItem('theme_mode', themeMode);
        
        if (themeMode === 'dark') {
            document.body.classList.add('dark-mode');
            document.documentElement.classList.add('dark-mode');
            localStorage.setItem('theme', 'dark');
            updateHeaderIcon('dark');
        } else if (themeMode === 'light') {
            document.body.classList.remove('dark-mode');
            document.documentElement.classList.remove('dark-mode');
            localStorage.setItem('theme', 'light');
            updateHeaderIcon('light');
        } else if (themeMode === 'auto') {
            // Modo dinámico: usa modo oscuro pero con color del avatar
            document.body.classList.add('dark-mode');
            document.documentElement.classList.add('dark-mode');
            localStorage.setItem('theme', 'dark');
            updateHeaderIcon('auto');
            
            // Aplicar color dinámico del avatar
            const avatarColor = extractAvatarColor();
            applyDynamicColor(avatarColor);
            localStorage.setItem('dynamic_color', avatarColor);
        }
    }

    // Aplicar configuraciones al formulario
    function applySettingsToForm() {
        const settings = loadSettings();
        const form = document.getElementById('settings-form');
        if (!form) return;

        // Aplicar valores de radio buttons (tema)
        const themeRadio = document.querySelector(`input[name="theme_mode"][value="${settings.theme_mode}"]`);
        if (themeRadio) {
            themeRadio.checked = true;
        }
        
        // Aplicar valores de selects
        const langSelect = form.querySelector('[name="language"]');
        const currencySelect = form.querySelector('[name="currency"]');
        if (langSelect) langSelect.value = settings.language;
        if (currencySelect) currencySelect.value = settings.currency;

        // Aplicar valores de checkboxes
        form.querySelector('[name="push_notifications"]').checked = settings.push_notifications;
        form.querySelector('[name="email_notifications"]').checked = settings.email_notifications;
        form.querySelector('[name="promo_notifications"]').checked = settings.promo_notifications;
        form.querySelector('[name="order_notifications"]').checked = settings.order_notifications;
        form.querySelector('[name="save_cart"]').checked = settings.save_cart;
        form.querySelector('[name="public_profile"]').checked = settings.public_profile;
        form.querySelector('[name="share_activity"]').checked = settings.share_activity;

        // Aplicar tema actual
        applyTheme(settings.theme_mode);
    }

    // Guardar un setting individual
    function saveSetting(key, value) {
        localStorage.setItem(key, value);
        console.log(`⚙️ Configuración guardada: ${key} = ${value}`);
    }

    // Restaurar configuración predeterminada
    function resetSettings() {
        const defaults = {
            theme_mode: 'light',
            push_notifications: true,
            email_notifications: true,
            promo_notifications: true,
            order_notifications: true,
            language: 'es',
            currency: 'PEN',
            save_cart: true,
            public_profile: false,
            share_activity: false
        };

        // Limpiar localStorage
        Object.keys(defaults).forEach(key => {
            localStorage.setItem(key, defaults[key]);
        });

        // Limpiar color dinámico
        localStorage.removeItem('dynamic_color');
        const styleElement = document.getElementById('dynamic-primary-color');
        if (styleElement) {
            styleElement.remove();
        }

        // Aplicar tema claro
        applyTheme('light');

        // Recargar formulario
        applySettingsToForm();
        
        showToast('✅ Configuración restaurada a valores predeterminados', 'success');
    }

    // Mostrar toast de notificación
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast-notification ${type}`;
        toast.innerHTML = `
            <i class="fa ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
            <span>${message}</span>
        `;
        
        toast.style.cssText = `
            position: fixed;
            top: 100px;
            right: 20px;
            background: ${type === 'success' ? '#2ecc71' : '#e74c3c'};
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            z-index: 10000;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease;
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 2000);
    }

    // Inicializar cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('settings-form');
        if (!form) return;

        // Cargar y aplicar configuraciones
        applySettingsToForm();

        // ===== LISTENERS DE EVENTOS DE AVATAR (TIEMPO REAL) =====
        
        // Escuchar cuando se actualiza el shadow del avatar (desde avatar-upload.js)
        document.addEventListener('avatarShadowUpdated', function(e) {
            const currentTheme = localStorage.getItem('theme_mode');
            
            if (currentTheme === 'auto') {
                console.log('🎨 avatarShadowUpdated recibido:', e.detail);
                
                // Actualizar modo dinámico con el nuevo color
                const { r, g, b } = e.detail;
                const toHex = (n) => {
                    const hex = n.toString(16);
                    return hex.length === 1 ? '0' + hex : hex;
                };
                const hexColor = `#${toHex(r)}${toHex(g)}${toHex(b)}`;
                
                console.log('✨ Aplicando nuevo color dinámico:', hexColor);
                applyDynamicColor(hexColor);
                localStorage.setItem('dynamic_color', hexColor);
            }
        });
        
        // Escuchar cuando se actualiza el color del avatar al finalizar la animación
        document.addEventListener('avatarColorUpdated', function(e) {
            const currentTheme = localStorage.getItem('theme_mode');
            
            if (currentTheme === 'auto') {
                console.log('🎨 avatarColorUpdated recibido:', e.detail);
                
                // Actualizar modo dinámico con el nuevo color
                const { r, g, b } = e.detail;
                const toHex = (n) => {
                    const hex = n.toString(16);
                    return hex.length === 1 ? '0' + hex : hex;
                };
                const hexColor = `#${toHex(r)}${toHex(g)}${toHex(b)}`;
                
                console.log('✨ Aplicando color del header:', hexColor);
                applyDynamicColor(hexColor);
                localStorage.setItem('dynamic_color', hexColor);
            }
        });

        // ===== GUARDADO AUTOMÁTICO EN TIEMPO REAL =====

        // Cambio de tema (auto-save, SIN notificación)
        const themeInputs = form.querySelectorAll('[name="theme_mode"]');
        themeInputs.forEach(input => {
            input.addEventListener('change', function() {
                if (this.checked) {
                    applyTheme(this.value);
                }
            });
        });

        // Cambio de idioma (auto-save)
        const langSelect = form.querySelector('[name="language"]');
        if (langSelect) {
            langSelect.addEventListener('change', function() {
                saveSetting('language', this.value);
                showToast('✅ Idioma actualizado', 'success');
            });
        }

        // Cambio de moneda (auto-save)
        const currencySelect = form.querySelector('[name="currency"]');
        if (currencySelect) {
            currencySelect.addEventListener('change', function() {
                saveSetting('currency', this.value);
                showToast('✅ Moneda actualizada', 'success');
            });
        }

        // Cambios en checkboxes (auto-save)
        const checkboxes = form.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                saveSetting(this.name, this.checked);
                showToast('✅ Configuración actualizada', 'success');
            });
        });

        // Ocultar botón de guardar (ya no es necesario)
        const saveBtn = document.querySelector('.btn-save-settings');
        if (saveBtn) {
            saveBtn.style.display = 'none';
        }

        // Botón de reset
        const resetBtn = document.querySelector('.btn-reset-settings');
        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                if (confirm('¿Estás seguro de restaurar la configuración predeterminada?')) {
                    resetSettings();
                }
            });
        }
    });

    // Agregar estilos para las animaciones del toast
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);

})();
