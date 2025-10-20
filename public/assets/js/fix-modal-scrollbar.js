/**
 * FIX: Eliminar barra lateral blanca al abrir modales
 * Previene que aparezca espacio en blanco y que la página se estire
 */
(function() {
    'use strict';
    
    // Variable para guardar el scroll actual
    let scrollPosition = 0;
    
    // Función para prevenir el padding-right y estiramiento
    function preventModalPadding() {
        
        // Cuando se ESTÁ ABRIENDO cualquier modal
        $(document).on('show.bs.modal', '.modal', function() {
            // Guardar posición actual del scroll
            scrollPosition = window.pageYOffset;
            
            // Aplicar fix inmediatamente
            setTimeout(function() {
                document.body.style.paddingRight = '0px';
                document.body.style.marginRight = '0px';
                document.body.style.overflow = 'hidden';
                document.body.style.position = 'fixed';
                document.body.style.top = `-${scrollPosition}px`;
                document.body.style.width = '100%';
            }, 0);
        });
        
        // Cuando se CIERRA cualquier modal
        $(document).on('hidden.bs.modal', '.modal', function() {
            // Verificar si hay otros modales abiertos
            if ($('.modal.show').length === 0) {
                document.body.style.paddingRight = '';
                document.body.style.marginRight = '';
                document.body.style.overflow = '';
                document.body.style.position = '';
                document.body.style.top = '';
                document.body.style.width = '';
                
                // Restaurar posición del scroll
                window.scrollTo(0, scrollPosition);
            }
        });
        
        // Observer para interceptar cuando Bootstrap intenta agregar padding
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                    if (document.body.classList.contains('modal-open')) {
                        // Detectar si Bootstrap agregó padding-right
                        const paddingRight = document.body.style.paddingRight;
                        const marginRight = document.body.style.marginRight;
                        
                        if (paddingRight && paddingRight !== '0px') {
                            document.body.style.paddingRight = '0px';
                        }
                        if (marginRight && marginRight !== '0px') {
                            document.body.style.marginRight = '0px';
                        }
                    }
                }
            });
        });
        
        // Observar cambios en el estilo del body
        observer.observe(document.body, {
            attributes: true,
            attributeFilter: ['style']
        });
    }
    
    // Ejecutar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', preventModalPadding);
    } else {
        preventModalPadding();
    }
    
})();
