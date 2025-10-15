/**
 * Error Handler para Fashion Store
 * Maneja errores JavaScript y previene que la página se rompa
 */

(function($) {
    'use strict';
    // Función para verificar si una función/plugin existe
    function checkPlugin(name, fn) {
        if (typeof fn === 'undefined') {
            console.warn('Plugin "' + name + '" no está disponible');
            return false;
        }
        return true;
    }
    
    // Función para ejecutar código de forma segura
    function safeExecute(name, callback) {
        try {
            callback();
        } catch (error) {
            console.warn('Error en ' + name + ':', error.message);
        }
    }
    
    $(document).ready(function() {
        // Verificar plugins disponibles
        const plugins = [
            { name: 'slicknav', fn: $.fn.slicknav },
            { name: 'owlCarousel', fn: $.fn.owlCarousel },
            { name: 'magnificPopup', fn: $.fn.magnificPopup },
            { name: 'niceScroll', fn: $.fn.niceScroll }
        ];
        
        plugins.forEach(function(plugin) {
            checkPlugin(plugin.name, plugin.fn);
        });
        
    });
    
    // Manejar errores globales de JavaScript
    window.onerror = function(msg, url, lineNo, columnNo, error) {
        return false; // No prevenir el comportamiento por defecto
    };
    
})(jQuery);