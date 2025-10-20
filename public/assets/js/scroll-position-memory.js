/**
 * SCROLL POSITION MEMORY
 * Guarda y restaura la posición del scroll al navegar entre páginas
 * Se aplica globalmente en toda la aplicación
 */

(function() {
    'use strict';

    // PRIMERO: Restaurar scroll INMEDIATAMENTE antes de que se renderice la página
    const savedPosition = sessionStorage.getItem('scrollPosition_' + window.location.pathname);
    if (savedPosition !== null) {
        // Restaurar de inmediato para evitar el "salto" visual
        document.documentElement.scrollTop = parseInt(savedPosition);
        document.body.scrollTop = parseInt(savedPosition); // Para navegadores viejos
        
        // Limpiar después de restaurar
        sessionStorage.removeItem('scrollPosition_' + window.location.pathname);
        
    }

    // Guardar posición del scroll antes de salir de la página
    window.addEventListener('beforeunload', function() {
        sessionStorage.setItem('scrollPosition_' + window.location.pathname, window.scrollY);
    });

    // También manejar navegación con botón atrás del navegador
    window.addEventListener('popstate', function() {
        const savedPos = sessionStorage.getItem('scrollPosition_' + window.location.pathname);
        if (savedPos !== null) {
            window.scrollTo({
                top: parseInt(savedPos),
                behavior: 'smooth'
            });
        }
    });


})();
