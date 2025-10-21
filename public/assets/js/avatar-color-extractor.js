/**
 * AVATAR COLOR EXTRACTOR
 * Extrae los colores dominantes de la imagen del avatar
 * y aplica un box-shadow dinámico basado en esos colores
 */

(function() {
    'use strict';


    /**
     * Extrae el color dominante de una imagen
     */
    function getAverageColor(img) {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        
        canvas.width = img.naturalWidth || img.width;
        canvas.height = img.naturalHeight || img.height;
        
        try {
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const data = imageData.data;
            
            let r = 0, g = 0, b = 0;
            let count = 0;
            
            // Muestrear cada 4 píxeles para mejor rendimiento
            for (let i = 0; i < data.length; i += 16) {
                r += data[i];
                g += data[i + 1];
                b += data[i + 2];
                count++;
            }
            
            r = Math.floor(r / count);
            g = Math.floor(g / count);
            b = Math.floor(b / count);
            
            return { r, g, b };
        } catch (e) {
            // Retornar color por defecto (púrpura del gradiente)
            return { r: 102, g: 126, b: 234 }; // #667eea
        }
    }

    /**
     * Aumenta la saturación de un color RGB
     */
    function increaseSaturation(r, g, b, amount = 1.5) {
        // Convertir a HSL
        r /= 255;
        g /= 255;
        b /= 255;

        const max = Math.max(r, g, b);
        const min = Math.min(r, g, b);
        let h, s, l = (max + min) / 2;

        if (max === min) {
            h = s = 0;
        } else {
            const d = max - min;
            s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
            
            switch (max) {
                case r: h = ((g - b) / d + (g < b ? 6 : 0)) / 6; break;
                case g: h = ((b - r) / d + 2) / 6; break;
                case b: h = ((r - g) / d + 4) / 6; break;
            }
        }

        // Aumentar saturación
        s = Math.min(1, s * amount);

        // Convertir de vuelta a RGB
        function hue2rgb(p, q, t) {
            if (t < 0) t += 1;
            if (t > 1) t -= 1;
            if (t < 1/6) return p + (q - p) * 6 * t;
            if (t < 1/2) return q;
            if (t < 2/3) return p + (q - p) * (2/3 - t) * 6;
            return p;
        }

        let r2, g2, b2;
        if (s === 0) {
            r2 = g2 = b2 = l;
        } else {
            const q = l < 0.5 ? l * (1 + s) : l + s - l * s;
            const p = 2 * l - q;
            r2 = hue2rgb(p, q, h + 1/3);
            g2 = hue2rgb(p, q, h);
            b2 = hue2rgb(p, q, h - 1/3);
        }

        return {
            r: Math.round(r2 * 255),
            g: Math.round(g2 * 255),
            b: Math.round(b2 * 255)
        };
    }

    /**
     * Aplica el box-shadow dinámico al avatar
     */
    function applyDynamicShadow(avatarElement, img) {
        
        const color = getAverageColor(img);
        const saturatedColor = increaseSaturation(color.r, color.g, color.b, 1.8);
        
        
        // Crear shadows múltiples con el color extraído
        const shadows = [
            `0 4px 12px rgba(${saturatedColor.r}, ${saturatedColor.g}, ${saturatedColor.b}, 0.4)`,
            `0 8px 24px rgba(${saturatedColor.r}, ${saturatedColor.g}, ${saturatedColor.b}, 0.3)`,
            `0 0 30px rgba(${saturatedColor.r}, ${saturatedColor.g}, ${saturatedColor.b}, 0.2)`
        ];
        
        avatarElement.style.boxShadow = shadows.join(', ');
        
        // Guardar el color para usarlo en hover
        avatarElement.dataset.shadowColor = `${saturatedColor.r}, ${saturatedColor.g}, ${saturatedColor.b}`;
    }

    /**
     * Procesa un avatar (profile o modal)
     */
    function processAvatar(container, isModal = false) {
        const avatarImg = container.querySelector(isModal ? '.modal-avatar-img' : '.avatar-image');
        
        if (!avatarImg) {
            return;
        }


        // Si la imagen ya está cargada
        if (avatarImg.complete && avatarImg.naturalWidth > 0) {
            applyDynamicShadow(container, avatarImg);
        } else {
            // Esperar a que la imagen se cargue
            avatarImg.addEventListener('load', function() {
                applyDynamicShadow(container, avatarImg);
            });
            
            avatarImg.addEventListener('error', function() {
            });
        }
    }

    /**
     * Inicializar cuando el DOM esté listo
     */
    function init() {
        
        // Procesar avatar del profile (sidebar)
        const profileAvatar = document.querySelector('.profile-avatar');
        if (profileAvatar) {
            processAvatar(profileAvatar, false);
        }

        // Procesar avatar del modal (cuando se abra)
        const modalAvatar = document.getElementById('modal-user-avatar');
        if (modalAvatar) {
            // Observar cuando el modal se muestre
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                        const modal = document.getElementById('user-account-modal');
                        if (modal && modal.style.display !== 'none') {
                            processAvatar(modalAvatar, true);
                        }
                    }
                });
            });

            const modal = document.getElementById('user-account-modal');
            if (modal) {
                observer.observe(modal, { attributes: true });
            }

            // También procesar inmediatamente si tiene imagen
            const hasImage = modalAvatar.querySelector('.modal-avatar-img');
            if (hasImage) {
                processAvatar(modalAvatar, true);
            }
        }

    }

    // Ejecutar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
