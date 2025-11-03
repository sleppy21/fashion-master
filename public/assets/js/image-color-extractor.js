/**
 * IMAGE COLOR EXTRACTOR - Sistema Universal
 * Extrae colores dominantes de cualquier imagen y aplica shadows dinámicos
 * Compatible con: avatares, productos, thumbnails, etc.
 * @version 1.0 - Octubre 2025
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
            // Retornar color por defecto (púrpura)
            return { r: 102, g: 126, b: 234 };
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
     * Aplica el box-shadow dinámico a una imagen
     */
    function applyDynamicShadow(img, config = {}) {
        const {
            saturation = 1.8,
            shadowLayers = [
                { blur: 12, spread: 4, opacity: 0.4 },
                { blur: 24, spread: 8, opacity: 0.3 },
                { blur: 30, spread: 0, opacity: 0.2 }
            ],
            debug = false
        } = config;

        const color = getAverageColor(img);
        const saturatedColor = increaseSaturation(color.r, color.g, color.b, saturation);
        
        if (debug) {
        }
        
        // Crear shadows múltiples con el color extraído
        const shadows = shadowLayers.map(layer => 
            `0 ${layer.spread}px ${layer.blur}px rgba(${saturatedColor.r}, ${saturatedColor.g}, ${saturatedColor.b}, ${layer.opacity})`
        );
        
        const shadowValue = shadows.join(', ');
        
        if (debug) {
        }
        
        // Aplicar shadow con setProperty para mayor compatibilidad
        img.style.setProperty('box-shadow', shadowValue, 'important');
        
        // Guardar el color en el elemento
        img.dataset.shadowColor = `${saturatedColor.r}, ${saturatedColor.g}, ${saturatedColor.b}`;
        img.dataset.shadowApplied = 'true';
        
        if (debug) {
        }
    }

    /**
     * Procesa una imagen cuando está lista
     */
    function processImage(img, config = {}) {
        // Evitar procesar la misma imagen múltiples veces
        if (img.dataset.shadowApplied === 'true') {
            return;
        }

        // Asegurar que la imagen tenga crossorigin
        if (!img.hasAttribute('crossorigin')) {
            img.setAttribute('crossorigin', 'anonymous');
        }

        // Si la imagen ya está cargada
        if (img.complete && img.naturalWidth > 0) {
            applyDynamicShadow(img, config);
        } else {
            // Esperar a que la imagen se cargue
            img.addEventListener('load', function() {
                applyDynamicShadow(img, config);
            }, { once: true });
            
            img.addEventListener('error', function() {
            }, { once: true });
        }
    }

    /**
     * Inicializar el sistema para diferentes tipos de imágenes
     */
    function init() {
        
        // CONFIGURACIONES POR TIPO DE IMAGEN
        const imageConfigs = [
            // Avatares de usuario
            {
                selector: '.avatar-image, .modal-avatar-img',
                config: {
                    saturation: 1.8,
                    debug: true
                }
            },
            // Imágenes de productos - DESHABILITADO
            /*
            {
                selector: '.product-image',
                config: {
                    saturation: 1.6,
                    shadowLayers: [
                        { blur: 15, spread: 5, opacity: 0.35 },
                        { blur: 30, spread: 10, opacity: 0.25 },
                        { blur: 40, spread: 0, opacity: 0.15 }
                    ],
                    debug: false
                }
            },
            */
            // Imágenes en product details
            {
                selector: '.product__details__pic__slider img, .product__big__img',
                config: {
                    saturation: 1.7,
                    shadowLayers: [
                        { blur: 20, spread: 10, opacity: 0.4 },
                        { blur: 40, spread: 15, opacity: 0.3 },
                        { blur: 50, spread: 0, opacity: 0.2 }
                    ],
                    debug: true
                }
            },
            // Thumbnails
            {
                selector: '.product-image-small img, .thumbnail img',
                config: {
                    saturation: 1.5,
                    shadowLayers: [
                        { blur: 8, spread: 2, opacity: 0.3 },
                        { blur: 16, spread: 4, opacity: 0.2 }
                    ],
                    debug: false
                }
            }
        ];

        // Procesar cada tipo de imagen
        imageConfigs.forEach(({ selector, config }) => {
            const images = document.querySelectorAll(selector);
            images.forEach(img => processImage(img, config));
        });

        // Observar nuevas imágenes que se agreguen dinámicamente (AJAX, lazy loading, etc.)
        const observer = new MutationObserver(mutations => {
            mutations.forEach(mutation => {
                mutation.addedNodes.forEach(node => {
                    if (node.nodeType === 1) { // Element node
                        // Verificar si el nodo es una imagen
                        imageConfigs.forEach(({ selector, config }) => {
                            if (node.matches && node.matches(selector)) {
                                processImage(node, config);
                            }
                            // Buscar imágenes dentro del nodo
                            const images = node.querySelectorAll ? node.querySelectorAll(selector) : [];
                            images.forEach(img => processImage(img, config));
                        });
                    }
                });
            });
        });

        // Observar cambios en el body
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

    }

    // Ejecutar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Exponer función pública para uso manual
    window.ImageColorExtractor = {
        processImage: processImage,
        applyDynamicShadow: applyDynamicShadow,
        getAverageColor: getAverageColor,
        increaseSaturation: increaseSaturation
    };

})();
