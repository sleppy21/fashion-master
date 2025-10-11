<?php
// Vista de gestión de usuarios con CRUD completo
?>
<div class="admin-module">
    <div class="module-header">
        <h2 class="module-title">
            <i class="fas fa-users"></i>
            Gestión de Usuarios
        </h2>
        <div class="module-actions">
            <button class="btn-modern primary" onclick="showAddUserModal()">
                <i class="fas fa-user-plus"></i>
                Nuevo Usuario
            </button>
            <button class="btn-modern secondary" onclick="exportUsers()">
                <i class="fas fa-download"></i>
                Exportar
            </button>
        </div>
    </div>

    <!-- Filtros y búsqueda -->
    <div class="module-filters">
        <div class="search-container">
            <input type="text" id="search-usuarios" class="search-input" placeholder="Buscar usuarios...">
            <i class="fas fa-search search-icon"></i>
        </div>
        <div class="filters-grid">
            <select id="filter-rol" class="filter-select">
                <option value="">Todos los roles</option>
                <option value="admin">Administrador</option>
                <option value="vendedor">Vendedor</option>
                <option value="cliente">Cliente</option>
            </select>
            <select id="filter-estado-usuario" class="filter-select">
                <option value="">Todos los estados</option>
                <option value="1">Activo</option>
                <option value="0">Inactivo</option>
            </select>
        </div>
    </div>

    <!-- Tabla de usuarios -->
    <div class="data-table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Avatar</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Último Acceso</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="usuarios-table-body">
                <tr>
                    <td colspan="8" class="text-center loading-row">
                        <i class="fas fa-spinner fa-spin"></i>
                        Cargando usuarios...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="pagination-container">
        <div class="pagination-info">
            Mostrando <span id="pagination-start-users">0</span> a <span id="pagination-end-users">0</span> 
            de <span id="pagination-total-users">0</span> usuarios
        </div>
        <div class="pagination-controls">
            <button class="pagination-btn" onclick="previousPageUsers()" id="prev-btn-users">
                <i class="fas fa-chevron-left"></i>
            </button>
            <span class="pagination-pages-users" id="pagination-pages-users">
                <!-- Páginas dinámicas -->
            </span>
            <button class="pagination-btn" onclick="nextPageUsers()" id="next-btn-users">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>
</div>

<!-- Modal para agregar/editar usuario -->
<div id="user-modal" class="modern-modal">
    <div class="modal-overlay" onclick="closeUserModal()"></div>
    <div class="modal-container large">
        <div class="modal-header">
            <h3 class="modal-title" id="user-modal-title">Nuevo Usuario</h3>
            <button class="modal-close" onclick="closeUserModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="user-form">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="user-name">Nombre Completo *</label>
                        <input type="text" id="user-name" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="user-email">Email *</label>
                        <input type="email" id="user-email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="user-username">Nombre de Usuario *</label>
                        <input type="text" id="user-username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="user-role">Rol *</label>
                        <select id="user-role" name="rol" required>
                            <option value="">Seleccionar rol</option>
                            <option value="cliente">Cliente</option>
                            <option value="vendedor">Vendedor</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="user-password">Contraseña *</label>
                        <input type="password" id="user-password" name="password" required>
                        <small>Mínimo 8 caracteres</small>
                    </div>
                    <div class="form-group">
                        <label for="user-confirm-password">Confirmar Contraseña *</label>
                        <input type="password" id="user-confirm-password" name="confirm_password" required>
                    </div>
                    <div class="form-group">
                        <label for="user-phone">Teléfono</label>
                        <input type="tel" id="user-phone" name="telefono">
                    </div>
                    <div class="form-group">
                        <label for="user-status">Estado</label>
                        <select id="user-status" name="estado">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="user-address">Dirección</label>
                    <textarea id="user-address" name="direccion" rows="3" placeholder="Dirección completa del usuario..."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="user-avatar">Avatar del Usuario</label>
                    <input type="file" id="user-avatar" name="avatar" accept="image/*">
                    <div class="avatar-preview" id="avatar-preview"></div>
                </div>
                
                <div class="permissions-section" id="permissions-section" style="display: none;">
                    <h4>Permisos Especiales</h4>
                    <div class="permissions-grid">
                        <label class="permission-checkbox">
                            <input type="checkbox" name="permissions[]" value="manage_products">
                            <span>Gestionar Productos</span>
                        </label>
                        <label class="permission-checkbox">
                            <input type="checkbox" name="permissions[]" value="manage_categories">
                            <span>Gestionar Categorías</span>
                        </label>
                        <label class="permission-checkbox">
                            <input type="checkbox" name="permissions[]" value="manage_users">
                            <span>Gestionar Usuarios</span>
                        </label>
                        <label class="permission-checkbox">
                            <input type="checkbox" name="permissions[]" value="view_reports">
                            <span>Ver Reportes</span>
                        </label>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closeUserModal()">
                Cancelar
            </button>
            <button type="submit" form="user-form" class="btn-primary">
                <i class="fas fa-save"></i>
                Guardar Usuario
            </button>
        </div>
    </div>
</div>

<!-- Modal para cambiar contraseña -->
<div id="password-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Cambiar Contraseña</h3>
            <button class="modal-close" onclick="closePasswordModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="password-form">
                <input type="hidden" id="password-user-id" name="user_id">
                <div class="form-group">
                    <label for="new-password">Nueva Contraseña *</label>
                    <input type="password" id="new-password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label for="confirm-new-password">Confirmar Nueva Contraseña *</label>
                    <input type="password" id="confirm-new-password" name="confirm_new_password" required>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closePasswordModal()">
                Cancelar
            </button>
            <button type="submit" form="password-form" class="btn-primary">
                <i class="fas fa-key"></i>
                Cambiar Contraseña
            </button>
        </div>
    </div>
</div>

<script>
// ===== GESTIÓN DE USUARIOS - JAVASCRIPT COMPLETO =====

// Variables globales
let currentPage = 1;
let currentFilters = {};
let usuarios = [];

// Inicializar cuando se carga el documento
document.addEventListener('DOMContentLoaded', function() {
    log('🚀 DOM cargado, inicializando gestión de usuarios...');
    
    // Verificar que los elementos existen
    const searchInput = document.getElementById('search-usuarios');
    const filterRole = document.getElementById('filter-rol');
    const filterStatus = document.getElementById('filter-estado-usuario');
    const tableBody = document.getElementById('usuarios-table-body');
    
    log('📋 Verificando elementos DOM:', {
        searchInput: !!searchInput,
        filterRole: !!filterRole, 
        filterStatus: !!filterStatus,
        tableBody: !!tableBody
    });
    
    if (!searchInput || !filterRole || !filterStatus || !tableBody) {
        error('❌ Error: Elementos DOM no encontrados');
        return;
    }
    
    initializeUsuariosManagement();
});

// También inicializar inmediatamente si ya existe la función
if (typeof initializeUsuariosManagement === 'function') {
    log('🔄 Inicializando usuarios inmediatamente...');
    setTimeout(initializeUsuariosManagement, 100);
}

function initializeUsuariosManagement() {
    log('⚙️ Configurando event listeners...');
    setupEventListeners();
    log('📊 Cargando usuarios inicialmente...');
    loadUsuarios();
}

function setupEventListeners() {
    // Búsqueda en tiempo real con debounce más rápido
    document.getElementById('search-usuarios').addEventListener('input', debounce(function(e) {
        log('Searching for:', e.target.value);
        currentFilters.search = e.target.value;
        currentPage = 1;
        loadUsuarios();
    }, 300)); // Reducido a 300ms para búsqueda más rápida
    
    // Filtros
    document.getElementById('filter-rol').addEventListener('change', function(e) {
        log('Filter role:', e.target.value);
        currentFilters.role = e.target.value;
        currentPage = 1;
        loadUsuarios();
    });
    
    document.getElementById('filter-estado-usuario').addEventListener('change', function(e) {
        log('Filter status:', e.target.value);
        currentFilters.status = e.target.value;
        currentPage = 1;
        loadUsuarios();
    });
    
    // Modal events
    setupModalEvents();
}

// Función para mostrar mensaje cuando no hay datos
function showNoDataMessage(message) {
    const tbody = document.getElementById('usuarios-table-body');
    tbody.innerHTML = `
        <tr>
            <td colspan="8" class="text-center no-data">
                <i class="fas fa-exclamation-triangle" style="font-size: 2rem; color: #f59e0b; margin-bottom: 10px;"></i>
                <p>${message}</p>
                <button onclick="loadUsuarios()" class="btn-primary" style="margin-top: 10px;">
                    <i class="fas fa-refresh"></i>
                    Reintentar
                </button>
            </td>
        </tr>
    `;
}

function setupModalEvents() {
    // Configurar preview de avatar
    document.getElementById('user-avatar').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('avatar-preview');
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `<img src="${e.target.result}" alt="Avatar Preview" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover;">`;
            };
            reader.readAsDataURL(file);
        } else {
            preview.innerHTML = '';
        }
    });
    
    // Mostrar permisos especiales para admin y vendedor
    document.getElementById('user-role').addEventListener('change', function(e) {
        const permissionsSection = document.getElementById('permissions-section');
        if (e.target.value === 'admin' || e.target.value === 'vendedor') {
            permissionsSection.style.display = 'block';
        } else {
            permissionsSection.style.display = 'none';
        }
    });
    
    // Validar confirmación de contraseña
    document.getElementById('user-confirm-password').addEventListener('input', function(e) {
        const password = document.getElementById('user-password').value;
        const confirmPassword = e.target.value;
        
        if (password !== confirmPassword) {
            e.target.setCustomValidity('Las contraseñas no coinciden');
        } else {
            e.target.setCustomValidity('');
        }
    });
    
    // Formulario de usuario
    document.getElementById('user-form').addEventListener('submit', function(e) {
        e.preventDefault();
        saveUsuario();
    });
    
    // Formulario de cambio de contraseña
    document.getElementById('password-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const newPassword = document.getElementById('new-password').value;
        const confirmPassword = document.getElementById('confirm-new-password').value;
        
        if (newPassword !== confirmPassword) {
            showAlert('Las contraseñas no coinciden', 'error');
            return;
        }
        
        changeUserPassword();
    });
}

// ===== FUNCIONES PRINCIPALES =====

function loadUsuarios() {
    log('🔄 === INICIANDO CARGA DE USUARIOS ===');
    log('📄 Página actual:', currentPage);
    log('🔍 Filtros actuales:', currentFilters);
    
    showLoading();
    
    const params = new URLSearchParams({
        action: 'list',
        page: currentPage,
        limit: 10,
        ...currentFilters
    });
    
    const url = `app/controllers/UsuarioController.php?${params}`;
    log('🌐 URL de petición:', url);
    
    fetch(url, {
        method: 'GET',
        credentials: 'same-origin', // Incluir cookies de sesión
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest' // Indicar que es AJAX
        }
    })
        .then(response => {
            log('📨 Response recibida:');
            log('  - Status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status} - ${response.statusText}`);
            }
            return response.text(); // Cambiar a text() primero para ver la respuesta raw
        })
        .then(rawResponse => {
            
            let data;
            try {
                data = JSON.parse(rawResponse);
            } catch (e) {
                throw new Error('Respuesta no es JSON válido: ' + e.message);
            }
            
            if (data.success) {
                usuarios = data.data;
                renderUsuariosTable(data.data);
                renderPagination(data.pagination);
            } else {
            }
        })
        .catch(error => {    
            showAlert('Error de conexión: ' + error.message, 'error');
            showNoDataMessage('Error de conexión. Verifique que esté logueado como administrador y que el servidor esté funcionando.');
        })
        .finally(() => {
            hideLoading();
        });
}

function renderUsuariosTable(usuarios) {
    const tbody = document.getElementById('usuarios-table-body');
    
    if (usuarios.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center no-data">
                    <i class="fas fa-users" style="font-size: 2rem; color: #ccc; margin-bottom: 10px;"></i>
                    <p>No se encontraron usuarios</p>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = usuarios.map(usuario => `
        <tr>
            <td>${usuario.id_usuario}</td>
            <td>
                <div class="user-avatar-small">
                    <img src="public/assets/img/avatars/${usuario.avatar_usuario || 'default-avatar.png'}" 
                         alt="Avatar" 
                         onerror="this.src='public/assets/img/avatars/default-avatar.png'">
                </div>
            </td>
            <td>
                <div class="user-info">
                    <strong>${usuario.nombre_completo}</strong>
                    <small>@${usuario.username_usuario}</small>
                </div>
            </td>
            <td>${usuario.email_usuario}</td>
            <td>
                <span class="role-badge role-${usuario.rol_usuario}">
                    ${usuario.rol_texto}
                </span>
            </td>
            <td>
                <span class="status-badge ${usuario.status_usuario ? 'status-active' : 'status-inactive'}">
                    ${usuario.estado_texto}
                </span>
            </td>
            <td>${usuario.ultimo_acceso_formato}</td>
            <td>
                <div class="action-buttons">
                    <button class="btn-action btn-view" onclick="viewUser(${usuario.id_usuario})" title="Ver">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn-action btn-edit" onclick="editUser(${usuario.id_usuario})" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-action btn-password" onclick="changePassword(${usuario.id_usuario})" title="Cambiar contraseña">
                        <i class="fas fa-key"></i>
                    </button>
                    <button class="btn-action ${usuario.status_usuario ? 'btn-deactivate' : 'btn-activate'}" 
                            onclick="toggleUserStatus(${usuario.id_usuario}, ${usuario.status_usuario})" 
                            title="${usuario.status_usuario ? 'Desactivar' : 'Activar'}">
                        <i class="fas fa-${usuario.status_usuario ? 'ban' : 'check'}"></i>
                    </button>
                    ${usuario.status_usuario ? `
                        <button class="btn-action btn-delete" onclick="deleteUser(${usuario.id_usuario})" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    ` : ''}
                </div>
            </td>
        </tr>
    `).join('');
}

function renderPagination(pagination) {
    const { current_page, total_pages, total_records, per_page } = pagination;
    
    // Actualizar información de paginación
    document.getElementById('pagination-start-users').textContent = total_records > 0 ? ((current_page - 1) * per_page + 1) : 0;
    document.getElementById('pagination-end-users').textContent = Math.min(current_page * per_page, total_records);
    document.getElementById('pagination-total-users').textContent = total_records;
    
    // Actualizar botones de navegación
    const prevBtn = document.getElementById('prev-btn-users');
    const nextBtn = document.getElementById('next-btn-users');
    
    prevBtn.disabled = current_page <= 1;
    nextBtn.disabled = current_page >= total_pages;
    
    // Generar páginas
    const pagesContainer = document.getElementById('pagination-pages-users');
    let pagesHTML = '';
    
    for (let i = 1; i <= total_pages; i++) {
        if (i === current_page || i === 1 || i === total_pages || (i >= current_page - 1 && i <= current_page + 1)) {
            pagesHTML += `<button class="pagination-btn ${i === current_page ? 'active' : ''}" onclick="goToPage(${i})">${i}</button>`;
        } else if (i === current_page - 2 || i === current_page + 2) {
            pagesHTML += '<span class="pagination-dots">...</span>';
        }
    }
    
    pagesContainer.innerHTML = pagesHTML;
}

// ===== OPERACIONES CRUD =====

function showAddUserModal() {
    document.getElementById('user-modal-title').textContent = 'Nuevo Usuario';
    document.getElementById('user-form').reset();
    document.getElementById('user-form').setAttribute('data-mode', 'create');
    document.getElementById('user-form').removeAttribute('data-user-id');
    
    // Mostrar campos de contraseña para nuevo usuario
    document.getElementById('user-password').required = true;
    document.getElementById('user-confirm-password').required = true;
    
    document.getElementById('user-modal').classList.add('show');
}

function editUser(id) {
    // Cargar datos del usuario
    fetch(`app/controllers/UsuarioController.php?action=get&id=${id}`, {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const usuario = data.data;
                
                document.getElementById('user-modal-title').textContent = 'Editar Usuario';
                document.getElementById('user-form').setAttribute('data-mode', 'edit');
                document.getElementById('user-form').setAttribute('data-user-id', id);
                
                // Llenar formulario
                document.getElementById('user-name').value = usuario.nombre_usuario;
                document.getElementById('user-email').value = usuario.email_usuario;
                document.getElementById('user-username').value = usuario.username_usuario;
                document.getElementById('user-role').value = usuario.rol_usuario;
                document.getElementById('user-phone').value = usuario.telefono_usuario || '';
                document.getElementById('user-status').value = usuario.status_usuario;
                
                // Para edición, la contraseña no es obligatoria
                document.getElementById('user-password').required = false;
                document.getElementById('user-confirm-password').required = false;
                
                // Trigger role change for permissions
                document.getElementById('user-role').dispatchEvent(new Event('change'));
                
                document.getElementById('user-modal').classList.add('show');
            } else {
                showAlert('Error al cargar usuario: ' + data.error, 'error');
            }
        })
        .catch(error => {
            showAlert('Error de conexión', 'error');
        });
}

function saveUsuario() {
    const form = document.getElementById('user-form');
    const formData = new FormData(form);
    const mode = form.getAttribute('data-mode');
    const userId = form.getAttribute('data-user-id');
    
    // Convertir FormData a objeto
    const userData = {};
    formData.forEach((value, key) => {
        userData[key] = value;
    });
    
    // URL y método según el modo
    let url = 'app/controllers/UsuarioController.php?action=';
    url += mode === 'create' ? 'create' : 'update';
    
    if (mode === 'edit') {
        userData.id = userId;
    }
    
    fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(userData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            closeUserModal();
            loadUsuarios();
        } else {
            showAlert('Error: ' + data.error, 'error');
        }
    })
    .catch(error => {
        showAlert('Error de conexión', 'error');
    });
}

function deleteUser(id) {
    if (confirm('¿Estás seguro de que deseas eliminar este usuario? Esta acción no se puede deshacer.')) {
        fetch(`app/controllers/UsuarioController.php?action=delete&id=${id}`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                loadUsuarios();
            } else {
                showAlert('Error: ' + data.error, 'error');
            }
        })
        .catch(error => {
            showAlert('Error de conexión', 'error');
        });
    }
}

function toggleUserStatus(id, currentStatus) {
    const action = currentStatus ? 'desactivar' : 'activar';
    
    if (confirm(`¿Deseas ${action} este usuario?`)) {
        fetch(`app/controllers/UsuarioController.php?action=toggle_status&id=${id}`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                loadUsuarios();
            } else {
                showAlert('Error: ' + data.error, 'error');
            }
        })
        .catch(error => {
            showAlert('Error de conexión', 'error');
        });
    }
}

function changePassword(id) {
    document.getElementById('password-user-id').value = id;
    document.getElementById('password-modal').classList.add('show');
}

function changeUserPassword() {
    const userId = document.getElementById('password-user-id').value;
    const newPassword = document.getElementById('new-password').value;
    
    const formData = new FormData();
    formData.append('action', 'change_password');
    formData.append('id', userId);
    formData.append('new_password', newPassword);
    
    fetch('app/controllers/UsuarioController.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            closePasswordModal();
        } else {
            showAlert('Error: ' + data.error, 'error');
        }
    })
    .catch(error => {
        showAlert('Error de conexión', 'error');
    });
}

// ===== FUNCIONES DE UI =====

function closeUserModal() {
    document.getElementById('user-modal').classList.remove('show');
}

function closePasswordModal() {
    document.getElementById('password-modal').classList.remove('show');
    document.getElementById('password-form').reset();
}

function viewUser(id) {
    // Implementar vista detallada del usuario
    alert('Función de vista detallada - ID: ' + id);
}

function exportUsers() {
    // Implementar exportación de usuarios
    showAlert('Función de exportación en desarrollo', 'info');
}

// ===== PAGINACIÓN =====

function previousPageUsers() {
    if (currentPage > 1) {
        currentPage--;
        loadUsuarios();
    }
}

function nextPageUsers() {
    currentPage++;
    loadUsuarios();
}

function goToPage(page) {
    currentPage = page;
    loadUsuarios();
}

// ===== FUNCIONES AUXILIARES =====

function showLoading() {
    document.getElementById('usuarios-table-body').innerHTML = `
        <tr>
            <td colspan="8" class="text-center loading-row">
                <i class="fas fa-spinner fa-spin"></i>
                Cargando usuarios...
            </td>
        </tr>
    `;
}

function hideLoading() {
    // El loading se oculta automáticamente al renderizar la tabla
}

function showAlert(message, type = 'info') {
    // Crear o actualizar el alert
    let alertContainer = document.querySelector('.alert-container');
    if (!alertContainer) {
        alertContainer = document.createElement('div');
        alertContainer.className = 'alert-container';
        document.body.appendChild(alertContainer);
    }
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'times' : 'info'}"></i>
        ${message}
    `;
    
    alertContainer.appendChild(alert);
    
    // Auto-remover después de 5 segundos
    setTimeout(() => {
        alert.remove();
    }, 5000);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
</script>