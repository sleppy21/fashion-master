/**
 * USER ACCOUNT MODAL - VERSIÓN DROPDOWN
 * Script para controlar el dropdown de cuenta de usuario
 */

document.addEventListener('DOMContentLoaded', function() {
    // Obtener elementos del DOM
    const modal = document.getElementById('user-account-modal');
    const userLink = document.getElementById('user-account-link');
    const closeBtn = document.querySelector('.user-modal-close');
    
    // Verificar que los elementos existan
    if (!modal || !userLink) {
        return; // Salir si no hay usuario logueado
    }
    
    // Variable para controlar el estado de animación
    let isAnimating = false;
    
    // Función para abrir el dropdown
    function openModal(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Si está animando, no hacer nada
        if (isAnimating) return;
        
        // Cerrar modal de favoritos si está abierto
        const favoritesModal = document.getElementById('favorites-modal');
        if (favoritesModal && favoritesModal.style.display === 'block') {
            favoritesModal.style.display = 'none';
        }
        
        // Toggle: si ya está abierto, cerrarlo
        if (modal.style.display === 'block') {
            closeModal();
            return;
        }
        
        isAnimating = true;
        
        // Limpiar cualquier clase o estilo previo
        modal.classList.remove('active', 'closing');
        modal.style.display = 'block';
        
        // Forzar reflow para que la animación funcione
        void modal.offsetWidth;
        
        // Agregar clase para animaciones CSS
        requestAnimationFrame(() => {
            modal.classList.add('active');
            setTimeout(() => {
                isAnimating = false;
            }, 350);
        });
    }
    
    // Función para cerrar el dropdown
    function closeModal() {
        // Si está animando, no hacer nada
        if (isAnimating) return;
        
        isAnimating = true;
        
        // Agregar clase de cierre para animación
        modal.classList.add('closing');
        modal.classList.remove('active');
        
        // Esperar a que termine la animación antes de ocultar
        setTimeout(() => {
            modal.style.display = 'none';
            modal.classList.remove('closing');
            isAnimating = false;
        }, 300);
    }
    
    // Event listener para abrir modal
    userLink.addEventListener('click', openModal);
    
    // Event listener para cerrar con botón X
    if (closeBtn) {
        closeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            closeModal();
        });
    }
    
    // Cerrar dropdown al hacer clic fuera de él
    document.addEventListener('click', function(event) {
        const modalContent = document.querySelector('.user-modal-content');
        if (modal.style.display === 'block' && 
            !modalContent.contains(event.target) && 
            !userLink.contains(event.target)) {
            closeModal();
        }
    });
    
    // Cerrar dropdown con la tecla ESC
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && modal.style.display === 'block') {
            closeModal();
        }
    });
    
    // Prevenir que los clics dentro del contenido cierren el modal
    const modalContent = document.querySelector('.user-modal-content');
    if (modalContent) {
        modalContent.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
    
    // Animación para las stats cards al abrir el modal
    userLink.addEventListener('click', function() {
        setTimeout(animateStats, 100);
    });
    
    // Función para animar los números de las estadísticas
    function animateStats() {
        const statNumbers = document.querySelectorAll('.stat-number');
        
        statNumbers.forEach(function(stat) {
            const finalNumber = parseInt(stat.textContent);
            let currentNumber = 0;
            const increment = Math.ceil(finalNumber / 15);
            const duration = 400;
            const stepTime = duration / 15;
            
            stat.textContent = '0';
            
            const timer = setInterval(function() {
                currentNumber += increment;
                if (currentNumber >= finalNumber) {
                    stat.textContent = finalNumber;
                    clearInterval(timer);
                } else {
                    stat.textContent = currentNumber;
                }
            }, stepTime);
        });
    }
    
    // Añadir efecto ripple a los botones
    const buttons = document.querySelectorAll('.btn-action');
    buttons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            // Crear elemento ripple
            const ripple = document.createElement('span');
            ripple.classList.add('ripple-effect');
            
            // Posicionar el ripple
            const rect = button.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            
            // Agregar al botón
            button.appendChild(ripple);
            
            // Remover después de la animación
            setTimeout(() => ripple.remove(), 600);
        });
    });
    
    // Log de inicialización (remover en producción)
    console.log('✅ User Account Dropdown initialized successfully');
});
