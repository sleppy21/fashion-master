<!-- Dark Mode - Script Inline CRÍTICO (ANTES de CSS para evitar flash) -->
<script>
(function() {
    // Aplicar tema INMEDIATAMENTE antes de que se renderice la página
    const savedTheme = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const isDarkMode = savedTheme === 'dark' || (!savedTheme && prefersDark);
    
    if (isDarkMode) {
        document.documentElement.classList.add('dark-mode');
        // También agregar al body aunque aún no exista
        document.addEventListener('DOMContentLoaded', function() {
            document.body.classList.add('dark-mode');
        });
    }
})();
</script>

<!-- Dark Mode CSS -->
<link rel="stylesheet" href="public/assets/css/themes/dark-mode.css?v=<?php echo time(); ?>" type="text/css">
<link rel="stylesheet" href="public/assets/css/modals-dark-mode.css?v=<?php echo time(); ?>" type="text/css">

<!-- Dark Mode JavaScript (con cache busting para forzar actualización) -->
<script src="public/assets/js/dark-mode.js?v=<?php echo time(); ?>"></script>
