<!-- Dark Mode - Script Inline CRÍTICO (ANTES de CSS para evitar flash) -->
<script>
(function() {
    // Aplicar tema INMEDIATAMENTE antes de que se renderice la página
    let savedTheme = localStorage.getItem('theme');
    
    // Si no hay tema guardado, establecer 'light' como predeterminado
    if (!savedTheme) {
        savedTheme = 'light';
        localStorage.setItem('theme', 'light');
    }
    
    // CORREGIDO: Solo activar dark mode si el usuario lo eligió explícitamente
    // No usar preferencia del sistema para evitar cambios automáticos
    const isDarkMode = savedTheme === 'dark';
    
    if (isDarkMode) {
        document.documentElement.classList.add('dark-mode');
        // También agregar al body aunque aún no exista
        document.addEventListener('DOMContentLoaded', function() {
            document.body.classList.add('dark-mode');
        });
    }
})();
</script>


<!-- Dark Mode CSS Unificado -->
<link rel="stylesheet" href="public/assets/css/dark-mode/dark-mode.css?v=<?php echo time(); ?>" type="text/css">

<!-- Dark Mode JavaScript (nueva ubicación) -->
<script src="public/assets/js/dark-mode/dark-mode.js?v=<?php echo time(); ?>"></script>
