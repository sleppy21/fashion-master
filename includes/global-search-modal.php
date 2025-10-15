<!-- MODAL DE BÚSQUEDA GLOBAL -->
<div id="global-search-modal" class="global-search-modal">
    <div class="search-modal-overlay"></div>
    <div class="search-modal-content">
        <!-- Header del modal -->
        <div class="search-modal-header">
            <div class="search-input-container">
                <i class="fa fa-search search-icon"></i>
                <input type="text" 
                       id="global-search-input" 
                       class="global-search-input" 
                       placeholder="Buscar productos, ir a carrito, tienda, configuración..."
                       autocomplete="off">
                <button class="search-clear-btn" id="search-clear-btn" style="display: none;">
                    <i class="fa fa-times"></i>
                </button>
            </div>
            <button class="search-modal-close" id="search-modal-close-btn">
                <i class="fa fa-times"></i>
            </button>
        </div>
        
        <!-- Sugerencias rápidas (cuando no hay búsqueda) -->
        <div class="search-quick-actions" id="search-quick-actions">
            <p class="quick-actions-title">Accesos rápidos:</p>
            <div class="quick-actions-grid">
                <button class="quick-action-btn" data-action="cart">
                    <i class="fa fa-shopping-cart"></i>
                    <span>Carrito</span>
                </button>
                <button class="quick-action-btn" data-action="shop">
                    <i class="fa fa-store"></i>
                    <span>Tienda</span>
                </button>
                <button class="quick-action-btn" data-action="favorites">
                    <i class="fa fa-heart"></i>
                    <span>Favoritos</span>
                </button>
                <button class="quick-action-btn" data-action="notifications">
                    <i class="fa fa-bell"></i>
                    <span>Notificaciones</span>
                </button>
                <button class="quick-action-btn" data-action="profile">
                    <i class="fa fa-user"></i>
                    <span>Perfil</span>
                </button>
                <button class="quick-action-btn" data-action="settings">
                    <i class="fa fa-cog"></i>
                    <span>Configuración</span>
                </button>
                <?php if(isset($usuario_logueado) && $usuario_logueado['rol_usuario'] === 'admin'): ?>
                <button class="quick-action-btn" data-action="admin">
                    <i class="fa fa-shield"></i>
                    <span>Admin</span>
                </button>
                <?php endif; ?>
                <button class="quick-action-btn" data-action="contact">
                    <i class="fa fa-envelope"></i>
                    <span>Contacto</span>
                </button>
            </div>
        </div>
        
        <!-- Resultados de búsqueda -->
        <div class="search-results-container" id="search-results-container" style="display: none;">
            <!-- Navegación (accesos directos) -->
            <div class="search-section" id="search-nav-section" style="display: none;">
                <h3 class="search-section-title">
                    <i class="fa fa-compass"></i> Ir a...
                </h3>
                <div class="search-nav-results" id="search-nav-results"></div>
            </div>
            
            <!-- Productos -->
            <div class="search-section" id="search-products-section" style="display: none;">
                <h3 class="search-section-title">
                    <i class="fa fa-shopping-bag"></i> Productos
                </h3>
                <div class="search-products-results" id="search-products-results"></div>
            </div>
            
            <!-- Sin resultados -->
            <div class="search-no-results" id="search-no-results" style="display: none;">
                <i class="fa fa-search"></i>
                <p>No se encontraron resultados</p>
            </div>
            
            <!-- Loading -->
            <div class="search-loading" id="search-loading" style="display: none;">
                <div class="spinner-search"></div>
                <p>Buscando...</p>
            </div>
        </div>
    </div>
</div>
