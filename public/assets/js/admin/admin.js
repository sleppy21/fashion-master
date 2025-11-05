// Toggle del menú desplegable de usuario
function toggleUserMenu(event) {
	event.stopPropagation();
	const menu = document.getElementById('userDropdownMenu');
	const icon = document.querySelector('.user-dropdown-icon');
	if (menu.classList.contains('show')) {
		menu.classList.remove('show');
		if (icon) icon.style.transform = 'rotate(0deg)';
	} else {
		menu.classList.add('show');
		if (icon) icon.style.transform = 'rotate(180deg)';
	}
}

// Cerrar menú al hacer click fuera
document.addEventListener('click', function(event) {
	const menu = document.getElementById('userDropdownMenu');
	const userProfile = document.querySelector('.user-profile');
	if (menu && menu.classList.contains('show') && userProfile && !userProfile.contains(event.target)) {
		const icon = document.querySelector('.user-dropdown-icon');
		menu.classList.remove('show');
		if (icon) icon.style.transform = 'rotate(0deg)';
	}
});
