<!-- ============================================
     LIBRERÍAS MODERNAS - SLEPPYSTORE
     Incluir en todos los archivos del landing
     ============================================ -->

<!-- Flatpickr (Date Picker moderno - CSS + JS) -->


<!-- Font Awesome 6.4.0 (Iconos modernos) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">


<!-- Dark Mode Assets (CSS y JS para modo oscuro) -->
<?php 
if(file_exists(__DIR__ . '/dark-mode-assets.php')) {
    include __DIR__ . '/dark-mode-assets.php';
}
?>

<style>
/* ============================================
   ANIMACIONES AOS PERSONALIZADAS PARA SLEPPYSTORE
   ============================================ */

/* Animaciones base */
[data-aos] {
    transition-property: transform, opacity;
}

.aos-animate {
    will-change: transform, opacity;
}

/* Animaciones personalizadas adicionales */
[data-aos="slide-up"] {
    transform: translateY(100px);
    opacity: 0;
}

[data-aos="slide-up"].aos-animate {
    transform: translateY(0);
    opacity: 1;
}

[data-aos="scale-up"] {
    transform: scale(0.8);
    opacity: 0;
}

[data-aos="scale-up"].aos-animate {
    transform: scale(1);
    opacity: 1;
}

[data-aos="rotate-in"] {
    transform: rotate(-180deg) scale(0.5);
    opacity: 0;
}

[data-aos="rotate-in"].aos-animate {
    transform: rotate(0) scale(1);
    opacity: 1;
}

/* Animaciones para productos */
.product__item[data-aos] {
    transition-duration: 600ms;
}

/* Animaciones para cards */
.services__item[data-aos],
.trend__content[data-aos] {
    transition-duration: 500ms;
}

/* Hover effects con AOS */
.product__item[data-aos].aos-animate:hover {
    transform: translateY(-10px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}
</style>

<script>
// ============================================
// INICIALIZACIÓN DE AOS (ANIMATE ON SCROLL)
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar AOS con configuración optimizada para SleppyStore
    if (typeof AOS !== 'undefined') {
        AOS.init({
            // Configuración de animaciones
            duration: 800,              // Duración de la animación (ms)
            easing: 'ease-in-out',      // Tipo de aceleración
            once: true,                 // Animar solo una vez
            mirror: false,              // No animar en scroll hacia arriba
            
            // Configuración de activación
            offset: 120,                // Offset desde el trigger point (px)
            delay: 0,                   // Delay global (ms)
            
            // Punto de anclaje
            anchorPlacement: 'top-bottom',
            
            // Mobile
            disable: false,             // Nunca desactivar
            
            // Throttle
            throttleDelay: 99,          // Delay en throttle (ms)
            
            // Debounce
            debounceDelay: 50,          // Delay en debounce (ms)
            
            // Settings para diferentes viewports
            disableMutationObserver: false,
            
            // Callback opcional
            startEvent: 'DOMContentLoaded',
            initClassName: 'aos-init',
            animatedClassName: 'aos-animate',
            useClassNames: false,
            
            // Observador de mutaciones
            observerOnce: true
        });
        
        console.log('✨ AOS initialized successfully');
    }

    // Reiniciar AOS cuando se actualice contenido dinámico
    document.addEventListener('contentUpdated', function() {
        if (typeof AOS !== 'undefined') {
            AOS.refresh();
            console.log('🔄 AOS refreshed after content update');
        }
    });
    
    // Reiniciar AOS cuando se carguen productos
    document.addEventListener('productsUpdated', function() {
        if (typeof AOS !== 'undefined') {
            setTimeout(function() {
                AOS.refresh();
                console.log('🛍️ AOS refreshed after products update');
            }, 100);
        }
    });
});
</script>
