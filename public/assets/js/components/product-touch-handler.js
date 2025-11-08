/**
 * PRODUCT TOUCH HANDLER (Versión Robusta)
 * Maneja el comportamiento de touch en móviles para las tarjetas de productos
 * - Primer toque: Muestra los botones hover
 * - Tocar fuera o en otra tarjeta: Cierra la tarjeta activa
 * - Solo una tarjeta puede estar activa a la vez.
 *
 * GLOBAL: Se usa en shop.php, cart.php, product-details.php
 */

(function() {
    'use strict';

    /**
     * @type {HTMLElement | null} La única tarjeta de producto que está activa (mostrando hover)
     */
    let currentActiveCard = null;

    function isTouchDevice() {
        // Comprobación estándar de dispositivo táctil
        return (('ontouchstart' in window) || navigator.maxTouchPoints > 0 || navigator.msMaxTouchPoints > 0);
    }

    /**
     * Inicializa los listeners de eventos delegados en el documento.
     */
    function initProductTouchHandler() {
        if (!isTouchDevice()) return;

        // Usar 'pointerdown' es más rápido que 'click' y captura la intención
        document.addEventListener('pointerdown', onPointerDown, { passive: true });
        
        // Exponer re-init para compatibilidad (aunque con delegación no es necesario)
        window.reinitProductTouchHandler = function() {
            console.log('product-touch-handler: reinit (delegated)');
        };

        console.log('product-touch-handler: initialized (delegated, robust)');
    }

    /**
     * Manejador principal para todos los eventos 'pointerdown' en el documento.
     * @param {PointerEvent} e
     */
    function onPointerDown(e) {
        // Solo manejar toques (ignorar mouse)
        if (e.pointerType && e.pointerType !== 'touch' && e.pointerType !== 'pen') return;

        const card = e.target.closest('.product-card-modern');

        // --- SIEMPRE cerrar todas las demás antes de abrir ---
        if (card) {
            document.querySelectorAll('.product-card-modern.force-hover').forEach(function(otherCard) {
                if (otherCard !== card) deactivateHover(otherCard);
            });
        } else {
            // Si tocó fuera de cualquier tarjeta, cerrar todas
            document.querySelectorAll('.product-card-modern.force-hover').forEach(function(otherCard) {
                deactivateHover(otherCard);
            });
            return;
        }

        // --- CASO: Tocó un botón de hover (Añadir, Favorito, etc.) ---
        if (e.target.closest('.product__hover')) {
            // Si el usuario interactúa con los botones,
            // reiniciamos el temporizador de cierre automático.
            resetAutoClose(card);
            return;
        }

        // La fuente de la verdad: ¿Es esta tarjeta la que ya está activa?
        const isActive = (card === currentActiveCard);

        if (!isActive) {
            // Abrimos la nueva tarjeta (esta función también cierra la anterior)
            activateHover(card);
            e.stopPropagation();
        } else {
            // Segundo toque: navegar
            navigateToProduct(card);
        }
    }

    /**
     * Activa el estado hover en una tarjeta y cierra cualquier otra que estuviera activa.
     * @param {HTMLElement} card La tarjeta a activar.
     */
    function activateHover(card) {
        // 1. Cierra TODAS las tarjetas abiertas (robusto)
        document.querySelectorAll('.product-card-modern.force-hover').forEach(function(otherCard) {
            if (otherCard !== card) deactivateHover(otherCard);
        });

        // 2. Activa la nueva tarjeta
        card.setAttribute('data-hover-active', 'true');
        card.classList.add('force-hover');
        currentActiveCard = card;

        // 3. Inicia el temporizador de cierre automático
        resetAutoClose(card);
    }

    /**
     * Desactiva el estado hover de una tarjeta.
     * @param {HTMLElement} card La tarjeta a desactivar.
     */
    function deactivateHover(card) {
        if (!card) return;

        card.removeAttribute('data-hover-active');
        card.classList.remove('force-hover');
        clearExistingCloseTimeout(card);

        // Si esta era la tarjeta activa, actualizamos el estado global
        if (card === currentActiveCard) {
            currentActiveCard = null;
        }
    }

    /**
     * Navega a la página de detalles del producto.
     * @param {HTMLElement} card
     */
    function navigateToProduct(card) {
        const link = card.querySelector('.product-info a, a.product-link');
        let href = null;

        if (link && link.href) {
            href = link.href;
        } else {
            const productId = card.getAttribute('data-product-id');
            if (productId) {
                href = 'product-details.php?id=' + encodeURIComponent(productId);
            }
        }

        if (href) {
            // Pequeño retardo para permitir feedback visual del toque
            setTimeout(function() {
                window.location.href = href;
            }, 50);
        }
    }

    /**
     * (Re)inicia el temporizador de cierre automático para una tarjeta.
     * @param {HTMLElement} card
     */
    function resetAutoClose(card) {
        clearExistingCloseTimeout(card);
        
        card.__closeTimeout = setTimeout(function() {
            // Solo cierra si la tarjeta sigue siendo la activa
            if (card === currentActiveCard) {
                deactivateHover(card);
            }
        }, 5000); // 5 segundos de inactividad
    }

    /**
     * Limpia cualquier temporizador de cierre existente en la tarjeta.
     * @param {HTMLElement} card
     */
    function clearExistingCloseTimeout(card) {
        if (card && card.__closeTimeout) {
            clearTimeout(card.__closeTimeout);
            card.__closeTimeout = null;
        }
    }

    // --- Inicialización ---
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initProductTouchHandler);
    } else {
        initProductTouchHandler(); // El DOM ya está listo
    }

})();