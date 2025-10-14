/**
 * PRICE SLIDER MODULE
 * Control de rango de precios con noUiSlider
 * @version 2.0
 */

(function() {
    'use strict';

    // Verificar que noUiSlider esté disponible
    if (typeof noUiSlider === 'undefined') {
        console.error('noUiSlider no está cargado. Asegúrate de incluir la librería.');
        return;
    }

    // Configuración
    const CONFIG = {
        minPrice: 0,
        maxPrice: 5000,
        step: 50,
        currency: 'S/',
        debounceTime: 500
    };

    // Referencias DOM
    let priceSlider = null;
    let minPriceInput = null;
    let maxPriceInput = null;
    let debounceTimer = null;

    /**
     * Inicializar el slider de precios
     */
    function initPriceSlider() {
        // Obtener elementos del DOM
        priceSlider = document.getElementById('price-slider');
        minPriceInput = document.getElementById('min-price');
        maxPriceInput = document.getElementById('max-price');

        if (!priceSlider || !minPriceInput || !maxPriceInput) {
            console.warn('Elementos del price slider no encontrados en el DOM');
            return;
        }

        // Obtener valores iniciales de los inputs o URL
        const urlParams = new URLSearchParams(window.location.search);
        const minValue = parseFloat(urlParams.get('precio_min')) || CONFIG.minPrice;
        const maxValue = parseFloat(urlParams.get('precio_max')) || CONFIG.maxPrice;

        // Crear el slider
        noUiSlider.create(priceSlider, {
            start: [minValue, maxValue],
            connect: true,
            step: CONFIG.step,
            range: {
                'min': CONFIG.minPrice,
                'max': CONFIG.maxPrice
            },
            format: {
                to: function(value) {
                    return Math.round(value);
                },
                from: function(value) {
                    return Number(value);
                }
            }
        });

        // Actualizar inputs cuando el slider cambia
        priceSlider.noUiSlider.on('update', function(values, handle) {
            const value = values[handle];
            
            if (handle === 0) {
                minPriceInput.value = value;
            } else {
                maxPriceInput.value = value;
            }
        });

        // Aplicar filtro cuando termina de arrastrar
        priceSlider.noUiSlider.on('set', function(values) {
            applyPriceFilter(values[0], values[1]);
        });

        // Event listeners para los inputs
        minPriceInput.addEventListener('change', function() {
            updateSliderFromInputs();
        });

        maxPriceInput.addEventListener('change', function() {
            updateSliderFromInputs();
        });

        // Permitir Enter para aplicar
        [minPriceInput, maxPriceInput].forEach(input => {
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    updateSliderFromInputs();
                }
            });
        });

        console.log('✅ Price Slider inicializado correctamente');
    }

    /**
     * Actualizar slider desde los inputs
     */
    function updateSliderFromInputs() {
        if (!priceSlider || !priceSlider.noUiSlider) return;

        let minValue = parseFloat(minPriceInput.value) || CONFIG.minPrice;
        let maxValue = parseFloat(maxPriceInput.value) || CONFIG.maxPrice;

        // Validar límites
        minValue = Math.max(CONFIG.minPrice, Math.min(minValue, CONFIG.maxPrice));
        maxValue = Math.max(CONFIG.minPrice, Math.min(maxValue, CONFIG.maxPrice));

        // Asegurar que min no sea mayor que max
        if (minValue > maxValue) {
            [minValue, maxValue] = [maxValue, minValue];
        }

        // Actualizar slider
        priceSlider.noUiSlider.set([minValue, maxValue]);
    }

    /**
     * Aplicar filtro de precio
     * @param {number} min - Precio mínimo
     * @param {number} max - Precio máximo
     */
    function applyPriceFilter(min, max) {
        // Cancelar timer anterior
        if (debounceTimer) {
            clearTimeout(debounceTimer);
        }

        // Debounce para evitar múltiples peticiones
        debounceTimer = setTimeout(() => {
            console.log(`Aplicando filtro de precio: ${CONFIG.currency}${min} - ${CONFIG.currency}${max}`);

            // Si existe la función global de filtros, usarla
            if (typeof window.aplicarFiltro === 'function') {
                window.aplicarFiltro('precio', { min: min, max: max });
            } else {
                // Fallback: actualizar URL y recargar
                const url = new URL(window.location);
                url.searchParams.set('precio_min', min);
                url.searchParams.set('precio_max', max);
                window.location.href = url.toString();
            }
        }, CONFIG.debounceTime);
    }

    /**
     * Resetear el slider a valores por defecto
     */
    function resetPriceSlider() {
        if (!priceSlider || !priceSlider.noUiSlider) return;

        priceSlider.noUiSlider.set([CONFIG.minPrice, CONFIG.maxPrice]);
        console.log('Price slider reseteado');
    }

    /**
     * Obtener valores actuales del slider
     * @returns {Object} Objeto con min y max
     */
    function getCurrentPriceRange() {
        if (!priceSlider || !priceSlider.noUiSlider) {
            return { min: CONFIG.minPrice, max: CONFIG.maxPrice };
        }

        const values = priceSlider.noUiSlider.get();
        return {
            min: parseFloat(values[0]),
            max: parseFloat(values[1])
        };
    }

    /**
     * Establecer rango de precios programáticamente
     * @param {number} min - Precio mínimo
     * @param {number} max - Precio máximo
     */
    function setPriceRange(min, max) {
        if (!priceSlider || !priceSlider.noUiSlider) return;

        min = Math.max(CONFIG.minPrice, Math.min(min, CONFIG.maxPrice));
        max = Math.max(CONFIG.minPrice, Math.min(max, CONFIG.maxPrice));

        priceSlider.noUiSlider.set([min, max]);
    }

    /**
     * Actualizar límites del slider dinámicamente
     * @param {number} newMin - Nuevo mínimo
     * @param {number} newMax - Nuevo máximo
     */
    function updateSliderLimits(newMin, newMax) {
        if (!priceSlider || !priceSlider.noUiSlider) return;

        CONFIG.minPrice = newMin;
        CONFIG.maxPrice = newMax;

        priceSlider.noUiSlider.updateOptions({
            range: {
                'min': newMin,
                'max': newMax
            }
        });

        console.log(`Límites del slider actualizados: ${newMin} - ${newMax}`);
    }

    /**
     * Formatear precio para mostrar
     * @param {number} price - Precio a formatear
     * @returns {string} Precio formateado
     */
    function formatPrice(price) {
        return `${CONFIG.currency}${price.toFixed(2)}`;
    }

    // Inicializar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPriceSlider);
    } else {
        initPriceSlider();
    }

    // Exportar funciones públicas al objeto window
    window.PriceSlider = {
        init: initPriceSlider,
        reset: resetPriceSlider,
        getCurrentRange: getCurrentPriceRange,
        setPriceRange: setPriceRange,
        updateLimits: updateSliderLimits,
        formatPrice: formatPrice
    };

    console.log('📊 Módulo Price Slider cargado');

})();
