/**
 * USER ACCOUNT MODAL - VERSIÓN DROPDOWN
 * Script para controlar el dropdown de cuenta de usuario
 * 
 * NOTA: Event listeners ahora manejados por header-section.php
 * Este archivo solo mantiene funciones auxiliares
 */

document.addEventListener('DOMContentLoaded', function() {
    // Verificar si las funciones globales ya están definidas (desde header-section.php)
    if (window.toggleUserModal && typeof window.toggleUserModal === 'function') {
        // Las funciones ya están manejadas por header-section.php
        // No agregar event listeners adicionales
        return;
    }
    
    
    // CÓDIGO LEGACY (solo si header-section.php no está cargado)
    // NO SE EJECUTA en condiciones normales
    
    const modal = document.getElementById('user-account-modal');
    const userLink = document.getElementById('user-account-link');
    const userProfileLink = document.getElementById('user-profile-link');
    const closeBtn = document.querySelector('.user-modal-close');
    
    if (!modal || (!userLink && !userProfileLink)) {
        return;
    }
    
    let isAnimating = false;
    
    function openModal(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (isAnimating) return;
        
        const favoritesModal = document.getElementById('favorites-modal');
        if (favoritesModal && favoritesModal.style.display === 'block') {
            favoritesModal.style.display = 'none';
        }
        
        if (modal.style.display === 'block') {
            closeModal();
            return;
        }
        
        isAnimating = true;
        
        modal.classList.remove('active', 'closing');
        modal.style.display = 'block';
        
        void modal.offsetWidth;
        
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
    
    // SOLO agregar event listeners si estamos en modo legacy
    if (userLink) {
        userLink.addEventListener('click', openModal);
    }
    if (userProfileLink) {
        userProfileLink.addEventListener('click', openModal);
    }
    
    if (closeBtn) {
        closeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            closeModal();
        });
    }
    
    document.addEventListener('click', function(event) {
        const modalContent = document.querySelector('.user-modal-content');
        if (modal.style.display === 'block' && 
            !modalContent.contains(event.target) && 
            !userLink?.contains(event.target) &&
            !userProfileLink?.contains(event.target)) {
            closeModal();
        }
    });
    
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && modal.style.display === 'block') {
            closeModal();
        }
    });
    
    const modalContent = document.querySelector('.user-modal-content');
    if (modalContent) {
        modalContent.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
    
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
    
});
