/**
 * URL FIXER - Arregla automáticamente URLs incorrectas
 * Compatible con localhost, túneles (ngrok, cloudflare), y cualquier hosting
 * NO requiere modificar archivos PHP
 */

(function() {
    'use strict';
    
    
    // Esperar a que AppConfig esté disponible
    function waitForAppConfig(callback) {
        if (window.AppConfig) {
            callback();
        } else {
            setTimeout(() => waitForAppConfig(callback), 50);
        }
    }
    
    waitForAppConfig(() => {
        
        // Función para corregir un elemento img
        function fixImageUrl(img) {
            const originalSrc = img.getAttribute('src') || img.src;
            if (!originalSrc) return;
            
            // Si ya es una URL completa con el protocolo correcto, skip
            if (originalSrc.startsWith(window.location.protocol)) {
                return;
            }
            
            // Si es data: o blob:, skip
            if (originalSrc.startsWith('data:') || originalSrc.startsWith('blob:')) {
                return;
            }
            
            // Corregir protocolo si es necesario
            const fixedSrc = window.AppConfig.fixUrl(originalSrc);
            
            if (fixedSrc !== originalSrc) {
                img.src = fixedSrc;
            }
        }
        
        // Función para corregir un elemento link (CSS)
        function fixLinkUrl(link) {
            const originalHref = link.getAttribute('href');
            if (!originalHref) return;
            
            // Solo procesar stylesheets
            if (link.rel !== 'stylesheet') return;
            
            // Si ya es una URL completa con el protocolo correcto, skip
            if (originalHref.startsWith(window.location.protocol) || 
                originalHref.startsWith('data:') || 
                originalHref.startsWith('blob:')) {
                return;
            }
            
            const fixedHref = window.AppConfig.fixUrl(originalHref);
            
            if (fixedHref !== originalHref) {
                link.href = fixedHref;
            }
        }
        
        // Función para corregir un elemento script
        function fixScriptUrl(script) {
            const originalSrc = script.getAttribute('src');
            if (!originalSrc) return;
            
            // Si ya es una URL completa con el protocolo correcto, skip
            if (originalSrc.startsWith(window.location.protocol) || 
                originalSrc.startsWith('data:') || 
                originalSrc.startsWith('blob:')) {
                return;
            }
            
            const fixedSrc = window.AppConfig.fixUrl(originalSrc);
            
            if (fixedSrc !== originalSrc) {
                
                // Crear nuevo script con URL corregida
                const newScript = document.createElement('script');
                newScript.src = fixedSrc;
                if (script.type) newScript.type = script.type;
                if (script.async) newScript.async = true;
                if (script.defer) newScript.defer = true;
                
                // Reemplazar el script viejo
                script.parentNode.insertBefore(newScript, script);
                script.remove();
            }
        }
        
        // Corregir todas las imágenes existentes
        function fixAllImages() {
            document.querySelectorAll('img').forEach(fixImageUrl);
        }
        
        // Corregir todos los links existentes
        function fixAllLinks() {
            document.querySelectorAll('link[rel="stylesheet"]').forEach(fixLinkUrl);
        }
        
        // Corregir todos los scripts existentes
        function fixAllScripts() {
            document.querySelectorAll('script[src]').forEach(fixScriptUrl);
        }
        
        // Ejecutar corrección inicial
        fixAllImages();
        fixAllLinks();
        fixAllScripts();
        
        // Observer para elementos dinámicos
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1) { // Element node
                        if (node.tagName === 'IMG') {
                            fixImageUrl(node);
                        } else if (node.tagName === 'LINK' && node.rel === 'stylesheet') {
                            fixLinkUrl(node);
                        } else if (node.tagName === 'SCRIPT' && node.src) {
                            fixScriptUrl(node);
                        }
                        
                        // También buscar dentro del nuevo elemento
                        node.querySelectorAll && node.querySelectorAll('img').forEach(fixImageUrl);
                        node.querySelectorAll && node.querySelectorAll('link[rel="stylesheet"]').forEach(fixLinkUrl);
                    }
                });
            });
        });
        
        // Observar todo el documento
        observer.observe(document.documentElement, {
            childList: true,
            subtree: true
        });
        
        // Ejecutar cada 2 segundos para atrapar elementos que se cargan tarde
        setInterval(() => {
            fixAllImages();
            fixAllLinks();
        }, 2000);
        
    });
})();
