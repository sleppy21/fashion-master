// notificacion-global.js
// Toast visual global reutilizable para todo el sitio
(function() {
    'use strict';

    function showNotification(message, type = 'info') {
        // Eliminar notificaciones anteriores
        const existingNotif = document.querySelector('.modern-toast');
        if (existingNotif) existingNotif.remove();

        const isDarkMode = document.body.classList.contains('dark-mode');
        const icons = {
            success: '<i class="fa fa-check-circle"></i>',
            error: '<i class="fa fa-times-circle"></i>',
            info: '<i class="fa fa-info-circle"></i>',
            warning: '<i class="fa fa-exclamation-circle"></i>'
        };
        const colors = {
            success: { bg: '#10b981', shadow: 'rgba(16, 185, 129, 0.4)' },
            error: { bg: '#ef4444', shadow: 'rgba(239, 68, 68, 0.4)' },
            info: { bg: '#3b82f6', shadow: 'rgba(59, 130, 246, 0.4)' },
            warning: { bg: '#f59e0b', shadow: 'rgba(245, 158, 11, 0.4)' }
        };
        const color = colors[type] || colors.info;

        const toast = document.createElement('div');
        toast.className = `modern-toast ${type}`;

        // Contenedor interno para animaciones (animación no debe sobrescribir el centrado)
        const inner = document.createElement('div');
        inner.className = 'toast-inner';
        inner.innerHTML = `
            <div class="toast-icon">${icons[type]}</div>
            <div class="toast-content">
                <div class="toast-message">${message}</div>
            </div>
            <button class="toast-close">
                <i class="fa fa-times"></i>
            </button>
        `;

        // Barra de progreso (animada con transform: scaleX para evitar problemas de reflow)
        const progressBar = document.createElement('div');
        progressBar.className = 'toast-progress-bar';
        progressBar.style.cssText = `
            position: absolute;
            bottom: 0;
            left: 0;
            height: 4px;
            background: ${color.bg};
            width: 100%;
            transform-origin: left;
            transform: scaleX(1);
            animation: progressBar 3s linear forwards;
            border-radius: 0 0 12px 12px;
            pointer-events: none;
        `;

        // Estilos del toast (centrado horizontalmente, bottom fijo, z-index muy alto)
        toast.style.cssText = `
            position: fixed !important;
            left: 50% !important;
            right: auto !important;
            bottom: 30px !important;
            transform: translateX(-50%);
            min-width: 300px;
            max-width: 640px;
            background: ${isDarkMode ? '#1f2937' : 'white'};
            border-radius: 12px;
            box-shadow: ${isDarkMode 
                ? '0 10px 40px rgba(0,0,0,0.5), 0 0 0 1px rgba(255,255,255,0.05)' 
                : '0 10px 40px rgba(0,0,0,0.12), 0 0 0 1px rgba(0,0,0,0.05)'};
            z-index: 2147483647 !important;
            display: block;
            overflow: visible;
            padding: 0;
        `;

        // Estilos del contenedor interno (aquí se aplica la animación)
        inner.style.cssText = `
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 16px 20px 16px;
            font-size: 14px;
            color: ${isDarkMode ? '#e5e7eb' : '#2d3748'};
            font-weight: 500;
            line-height: 1.5;
            animation: slideInUp 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            will-change: transform, opacity;
        `;

        toast.appendChild(inner);
        toast.appendChild(progressBar);
        document.body.appendChild(toast);

        const icon = inner.querySelector('.toast-icon');
        icon.style.cssText = `
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: ${color.bg};
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        `;
        const content = inner.querySelector('.toast-content');
        content.style.cssText = `
            flex: 1;
            font-size: 14px;
            color: ${isDarkMode ? '#e5e7eb' : '#2d3748'};
            font-weight: 500;
            line-height: 1.5;
        `;
        const closeBtn = inner.querySelector('.toast-close');
        closeBtn.style.cssText = `
            background: none;
            border: none;
            color: ${isDarkMode ? '#6b7280' : '#a0aec0'};
            font-size: 18px;
            cursor: pointer;
            padding: 4px;
            line-height: 1;
            transition: all 0.12s ease;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
        `;
        closeBtn.onmouseenter = function() {
            this.style.color = isDarkMode ? '#f3f4f6' : '#2d3748';
            this.style.background = isDarkMode ? '#374151' : '#f7fafc';
        };
        closeBtn.onmouseleave = function() {
            this.style.color = isDarkMode ? '#6b7280' : '#a0aec0';
            this.style.background = 'none';
        };

        // Observador para detectar cambios de tema
        const themeObserver = new MutationObserver(() => {
            const newIsDarkMode = document.body.classList.contains('dark-mode');
            toast.style.background = newIsDarkMode ? '#1f2937' : 'white';
            toast.style.boxShadow = newIsDarkMode 
                ? '0 10px 40px rgba(0,0,0,0.5), 0 0 0 1px rgba(255,255,255,0.05)' 
                : '0 10px 40px rgba(0,0,0,0.12), 0 0 0 1px rgba(0,0,0,0.05)';
            content.style.color = newIsDarkMode ? '#e5e7eb' : '#2d3748';
            closeBtn.style.color = newIsDarkMode ? '#6b7280' : '#a0aec0';
            icon.style.background = (colors[type] || colors.info).bg;
            progressBar.style.background = (colors[type] || colors.info).bg;
        });
        themeObserver.observe(document.body, { attributes: true, attributeFilter: ['class'] });

        // Cerrar al hacer click en la X (aseguramos desconectar observer)
        closeBtn.onclick = function() {
            themeObserver.disconnect();
            toast.remove();
        };

        // Auto cierre con animación de salida (aplica la animación al contenedor interno)
        const hideTimeout = setTimeout(() => {
            inner.style.animation = 'slideOutDown 0.3s ease forwards';
            // detener la barra de progreso inmediatamente en caso de salida temprana
            progressBar.style.animationPlayState = 'paused';
            setTimeout(() => {
                themeObserver.disconnect();
                toast.remove();
            }, 320);
        }, 3000);

        // Si el usuario mueve el mouse sobre el toast, pausamos el cierre y la barra; reanudar al salir
        let paused = false;
        toast.addEventListener('mouseenter', () => {
            if (!paused) {
                clearTimeout(hideTimeout);
                progressBar.style.animationPlayState = 'paused';
                inner.style.animationPlayState = 'paused';
                paused = true;
            }
        });
        toast.addEventListener('mouseleave', () => {
            if (paused) {
                // calcular tiempo restante no trivial aquí, simplemente reiniciamos una nueva cuenta regresiva
                progressBar.style.animation = 'progressBar 3s linear forwards';
                inner.style.animation = 'slideInUp 0.25s ease';
                setTimeout(() => {
                    inner.style.animation = 'slideOutDown 0.3s ease forwards';
                    setTimeout(() => {
                        themeObserver.disconnect();
                        toast.remove();
                    }, 320);
                }, 3000);
                paused = false;
            }
        });

        return toast;
    }
    window.showNotification = showNotification;

    // Inyectar estilos globales del toast solo una vez
    (function injectToastStyles() {
        if (document.getElementById('toast-global-styles')) return;
        const style = document.createElement('style');
        style.id = 'toast-global-styles';
        style.textContent = `
            @keyframes slideInUp {
                from { transform: translateY(24px) scale(0.98); opacity: 0; }
                to { transform: translateY(0) scale(1); opacity: 1; }
            }
            @keyframes slideOutDown {
                from { transform: translateY(0) scale(1); opacity: 1; }
                to { transform: translateY(24px) scale(0.98); opacity: 0; }
            }
            @keyframes progressBar {
                from { transform: scaleX(1); opacity: 1; }
                to { transform: scaleX(0); opacity: 0.9; }
            }
            .modern-toast {
                transition: background-color 0.25s ease, box-shadow 0.25s ease;
                position: fixed;
                overflow: visible;
                pointer-events: auto;
            }
            .toast-inner {
                border-radius: 12px;
            }
            .toast-progress-bar {
                position: absolute !important;
                bottom: 0 !important;
                left: 0 !important;
                height: 4px !important;
                border-radius: 0 0 12px 12px !important;
                pointer-events: none;
            }
            body.dark-mode .modern-toast .toast-content {
                color: #e5e7eb !important;
            }
            body.dark-mode .modern-toast .toast-close {
                color: #6b7280 !important;
            }
            body.dark-mode .modern-toast .toast-close:hover {
                color: #f3f4f6 !important;
                background: #374151 !important;
            }
            body:not(.dark-mode) .modern-toast .toast-close:hover {
                color: #2d3748 !important;
                background: #f7fafc !important;
            }
        `;
        document.head.appendChild(style);
    })();
})();
