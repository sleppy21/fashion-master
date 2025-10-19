/**
 * PRICE SLIDER MODULE
 * Control de rango de precios con noUiSlider
 * @version 2.0
 */

(function() {
    'use strict';

    // Verificar que noUiSlider esté disponible
    if (typeof noUiSlider === 'undefined') {
        return;
    }

    // Configuración
    const CONFIG = {
        minPrice: 0,
        maxPrice: 10000,
        step: 100,
        currency: '$',
        debounceTime: 800 // Aumentado a 800ms para reducir llamadas
    };

    // Referencias DOM
    let priceSlider = null;
    let minPriceInput = null;
    let maxPriceInput = null;
    let displayMinPrice = null;
    let displayMaxPrice = null;
    let resetBtn = null;
    let markCurrentMin = null;
    let markCurrentMax = null;
    let debounceTimer = null;

    /**
     * Inicializar el slider de precios
     */
    function initPriceSlider() {
        
        // Obtener elementos del DOM
        priceSlider = document.getElementById('price-slider');
        minPriceInput = document.getElementById('min-price');
        maxPriceInput = document.getElementById('max-price');
        displayMinPrice = document.getElementById('display-min-price');
        displayMaxPrice = document.getElementById('display-max-price');
        resetBtn = document.getElementById('resetPriceBtn');
        markCurrentMin = document.querySelector('.mark-current-min');
        markCurrentMax = document.querySelector('.mark-current-max');

        if (!priceSlider || !minPriceInput || !maxPriceInput) {
            return;
        }

        // Obtener valores iniciales de los inputs o URL
        const urlParams = new URLSearchParams(window.location.search);
        const minValue = parseFloat(minPriceInput.value) || parseFloat(urlParams.get('precio_min')) || CONFIG.minPrice;
        const maxValue = parseFloat(maxPriceInput.value) || parseFloat(urlParams.get('precio_max')) || CONFIG.maxPrice;


        // Verificar si el slider ya existe y destruirlo
        if (priceSlider.noUiSlider) {
            priceSlider.noUiSlider.destroy();
        }

        // Crear el slider con DOS HANDLES
        try {
            noUiSlider.create(priceSlider, {
                start: [minValue, maxValue], // Dos valores iniciales
                connect: true, // Conectar entre los dos handles
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
                },
                tooltips: false, // Sin tooltips para usar nuestros displays
                behaviour: 'tap-drag', // Permitir tap y drag
                animate: true,
                animationDuration: 300
            });

            // Actualizar displays y marcas cuando el slider cambia (en tiempo real)
            priceSlider.noUiSlider.on('update', function(values, handle) {
                const minVal = parseFloat(values[0]);
                const maxVal = parseFloat(values[1]);
                
                // Actualizar inputs ocultos
                minPriceInput.value = minVal;
                maxPriceInput.value = maxVal;
                
                // Actualizar displays visuales grandes
                if (displayMinPrice) {
                    displayMinPrice.textContent = formatPrice(minVal);
                }
                if (displayMaxPrice) {
                    displayMaxPrice.textContent = formatPrice(maxVal);
                }

                // Actualizar marcas dinámicas
                updateDynamicMarks(minVal, maxVal);
            });

            // Aplicar filtro SOLO cuando el usuario SUELTA el handle (evita múltiples llamadas)
            priceSlider.noUiSlider.on('end', function(values) {
                const minVal = parseFloat(values[0]);
                const maxVal = parseFloat(values[1]);
                applyPriceFilter(minVal, maxVal);
            });

            // Botón de reset
            if (resetBtn) {
                resetBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    resetPriceSlider();
                });
            }

            // Inicializar marcas
            updateDynamicMarks(minValue, maxValue);


        } catch (error) {
        }
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
        // Cancelar timer anterior si existe
        if (debounceTimer) {
            clearTimeout(debounceTimer);
            debounceTimer = null;
        }

        // Aplicar filtro con debounce para evitar llamadas múltiples
        debounceTimer = setTimeout(() => {

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
            
            debounceTimer = null;
        }, CONFIG.debounceTime);
    }

    /**
     * Resetear el slider a valores por defecto
     */
    function resetPriceSlider() {
        if (!priceSlider || !priceSlider.noUiSlider) return;

        priceSlider.noUiSlider.set([CONFIG.minPrice, CONFIG.maxPrice]);
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

    }

    /**
     * Formatear precio para mostrar
     * @param {number} price - Precio a formatear
     * @returns {string} Precio formateado
     */
    function formatPrice(price) {
        // Formatear con separador de miles
        const formatted = price.toLocaleString('es-ES', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
        return `${CONFIG.currency}${formatted}`;
    }

    /**
     * Formatear precio compacto para marcas (1000 = 1k)
     * @param {number} price - Precio a formatear
     * @returns {string} Precio formateado compacto
     */
    function formatPriceCompact(price) {
        if (price >= 1000) {
            const k = (price / 1000).toFixed(1);
            return `${CONFIG.currency}${k}k`;
        }
        return `${CONFIG.currency}${price}`;
    }

    /**
     * Actualizar marcas dinámicas del slider
     * @param {number} minVal - Valor mínimo actual
     * @param {number} maxVal - Valor máximo actual
     */
    function updateDynamicMarks(minVal, maxVal) {
        // Solo mostrar marcas si no son los valores extremos
        const showMinMark = minVal > CONFIG.minPrice;
        const showMaxMark = maxVal < CONFIG.maxPrice;

        if (markCurrentMin) {
            if (showMinMark) {
                markCurrentMin.textContent = formatPriceCompact(minVal);
                markCurrentMin.classList.add('active');
                markCurrentMin.setAttribute('data-value', minVal);
            } else {
                markCurrentMin.textContent = '-';
                markCurrentMin.classList.remove('active');
            }
        }

        if (markCurrentMax) {
            if (showMaxMark) {
                markCurrentMax.textContent = formatPriceCompact(maxVal);
                markCurrentMax.classList.add('active');
                markCurrentMax.setAttribute('data-value', maxVal);
            } else {
                markCurrentMax.textContent = '-';
                markCurrentMax.classList.remove('active');
            }
        }
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


})();
