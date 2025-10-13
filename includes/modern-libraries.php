<!-- ============================================
     LIBRERÍAS MODERNAS - SLEPPYSTORE
     Incluir en todos los archivos del landing
     ============================================ -->

<!-- Flatpickr (Date Picker moderno - CSS + JS) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<!-- Chart.js (Gráficos y estadísticas) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- AOS (Animate On Scroll - CSS + JS) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" />
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>

<!-- Font Awesome 6.4.0 (Iconos modernos) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- SweetAlert2 (Alertas modernas) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

// ============================================
// CONFIGURACIÓN GLOBAL DE SWEETALERT2
// ============================================
if (typeof Swal !== 'undefined') {
    // Toast personalizado para notificaciones
    window.Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        },
        customClass: {
            popup: 'colored-toast'
        },
        iconColor: 'white'
    });
    
    // Configuración predeterminada para confirmaciones
    window.ConfirmDialog = function(options) {
        return Swal.fire({
            title: options.title || '¿Estás seguro?',
            text: options.text || 'Esta acción no se puede deshacer',
            icon: options.icon || 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: options.confirmText || 'Sí, continuar',
            cancelButtonText: options.cancelText || 'Cancelar',
            reverseButtons: true,
            customClass: {
                confirmButton: 'btn btn-danger',
                cancelButton: 'btn btn-secondary'
            }
        });
    };
    
    console.log('🎨 SweetAlert2 configured successfully');
}

// ============================================
// CONFIGURACIÓN DE FLATPICKR (DATE PICKER)
// ============================================
if (typeof flatpickr !== 'undefined') {
    // Configuración en español
    if (flatpickr.l10ns && flatpickr.l10ns.es) {
        flatpickr.localize(flatpickr.l10ns.es);
    }
    
    // Función helper para inicializar datepicker
    window.initDatePicker = function(selector, options = {}) {
        const defaultOptions = {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d/m/Y",
            locale: "es",
            ...options
        };
        
        return flatpickr(selector, defaultOptions);
    };
    
    console.log('📅 Flatpickr configured successfully');
}
</script>
