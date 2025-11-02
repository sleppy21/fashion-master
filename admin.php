<?php
// Habilitar mostrar errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Debug: Verificar si existe el archivo de conexión
if (!file_exists('config/conexion.php')) {
    die('Error: No se encuentra el archivo config/conexion.php');
}

require_once 'config/conexion.php';

// Debug: Verificar conexión
if (!isset($conn)) {
    die('Error: No se pudo establecer conexión a la base de datos');
}

// Verificar si el usuario está logueado y es administrador
if (!isset($_SESSION['user_id'])) {
    // Redirección simple sin SweetAlert (no está cargado aún)
    header('Location: login.php');
    exit;
}

if ($_SESSION['rol'] !== 'admin') {
    // Redirección simple sin SweetAlert (no está cargado aún)
    header('Location: index.php');
    exit;
}

// Obtener información del usuario
$usuario = $_SESSION['nombre'] ?? 'Administrador';
$rol = $_SESSION['rol'] ?? 'admin';

// Obtener estadísticas básicas
try {
    // Total de productos ACTIVOS
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM producto WHERE estado = 'activo'");
    $stmt->execute();
    $total_productos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Total de usuarios ACTIVOS
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM usuario WHERE estado_usuario = 'activo'");
    $stmt->execute();
    $total_usuarios = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Total de categorías ACTIVAS
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM categoria WHERE estado_categoria = 'activo'");
    $stmt->execute();
    $total_categorias = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Total de marcas
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM marca WHERE estado_marca = 'activo'");
    $stmt->execute();
    $total_marcas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Productos con stock bajo (menos de 10 unidades)
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM producto WHERE stock_actual_producto < 10 AND estado = 'activo'");
    $stmt->execute();
    $productos_stock_bajo = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Productos sin stock
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM producto WHERE stock_actual_producto = 0 AND estado = 'activo'");
    $stmt->execute();
    $productos_sin_stock = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Valor total del inventario (precio * stock)
    $stmt = $conn->prepare("SELECT SUM(precio_producto * stock_actual_producto) as total FROM producto WHERE estado = 'activo'");
    $stmt->execute();
    $valor_inventario = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // Total de todos los productos (activos + inactivos) para comparación
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM producto");
    $stmt->execute();
    $total_productos_sistema = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Calcular porcentajes de crecimiento simulados basados en datos reales
    $porcentaje_productos = $total_productos > 0 ? round(($total_productos / max($total_productos_sistema, 1)) * 100) : 0;
    $porcentaje_categorias = $total_categorias > 0 ? min(100, $total_categorias * 10) : 0;

} catch (PDOException $e) {
    // Valores por defecto en caso de error
    $total_productos = 0;
    $total_usuarios = 0;
    $total_categorias = 0;
    $total_marcas = 0;
    $productos_stock_bajo = 0;
    $productos_sin_stock = 0;
    $valor_inventario = 0;
    $total_productos_sistema = 0;
    $porcentaje_productos = 0;
    $porcentaje_categorias = 0;
    error_log("Error en consultas del dashboard: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Fashion Store</title>
    
    <!-- Fuentes principales -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- CSS del admin - PRIMERO para que sea sobrescrito -->
    <link href="public/assets/css/admin-styles.css" rel="stylesheet">
    
    <!-- CSS de modales - ORDEN IMPORTANTE: específicos primero, animaciones al final -->
    <link href="public/assets/css/views-modal.css" rel="stylesheet">
    <link href="public/assets/css/categoria-modals.css" rel="stylesheet">
    <link href="public/assets/css/product-view-modal.css" rel="stylesheet">
    <link href="public/assets/css/categoria-view-modal.css" rel="stylesheet">
    <link href="public/assets/css/marca-view-modal.css" rel="stylesheet">
    <link href="public/assets/css/view-modal-animations.css" rel="stylesheet"> <!-- DEBE SER EL ÚLTIMO -->
    
    <!-- ========================================== -->
    <!-- LIBRERÍAS MODERNAS -->
    <!-- ========================================== -->
    
    <!-- 1. Flatpickr - Selector de fecha y hora moderno -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    
    <!-- 2. Chart.js - Gráficos y estadísticas -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- 3. Fetch API - Ya viene incluido en navegadores modernos, no necesita importación -->
    
    <!-- 4. AOS.js - Animaciones al hacer scroll -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    
    <!-- 5. Font Awesome - Íconos modernos (actualizado a 6.5.0) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- ========================================== -->
    <!-- FIN LIBRERÍAS MODERNAS -->
    <!-- ========================================== -->
    
    <!-- SheetJS para exportar a Excel -->
    <script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>
    
    <!-- Configuración global de rutas -->
    <script src="public/assets/js/config.js"></script>
    
    <!-- ⭐ Sistema de sincronización de vistas (Grid/Tabla) -->
    <script src="public/assets/js/view-sync-system.js"></script>
    
    <!-- ⚠️ SCRIPTS CARGADOS ESPECÍFICAMENTE POR CADA MÓDULO (NO AQUÍ) -->
    <!-- smooth-table-update.js se carga SOLO en admin_productos.php -->
    <!-- smooth-table-update-categories.js se carga SOLO en admin_categorias.php -->
    <!-- product-modals.js - Funcionalidad integrada directamente en admin.php -->
    
    <!-- Sistema de notificaciones toast -->
    <script src="public/assets/js/cart-favorites-handler.js"></script>
    
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
</head>
<body>
    <!-- Script de inicialización temprana - DEBE estar antes de cualquier onclick -->
    <script>
        // Declaración temprana de switchTab para evitar errores en onclick
        // La función completa se define más abajo
        window.switchTab = function(tabId) {
            // Guardar la petición para ejecutarla cuando esté lista
            if (!window.switchTabReady) {
                window.pendingSwitchTab = tabId;
            }
        };
        window.switchTabReady = false;
    </script>
    
    <!-- ⭐ CONTENEDOR DE NOTIFICACIONES -->
    <!-- Las notificaciones ahora se manejan con SweetAlert2 -->
    <div id="notification-container" style="display: none;"></div>
    
    <div class="admin-container">
        <!-- Header del admin -->
        <header class="admin-header">
            <div class="container">
                <!-- Sección izquierda con botón y texto -->
                <div class="header-left">
                    <!-- Botón de retroceso -->
                    <a href="index.php" class="back-button">
                        <i class="fas fa-arrow-left"></i>
                        <span>Volver</span>
                    </a>
                    
                    <!-- Texto principal -->
                    <div class="header-title">
                        <h2>Fashion Admin</h2>
                        <p class="header-subtitle">Panel de Control Empresarial</p>
                    </div>
                </div>
                       
                <div class="admin-user-info">
                    <div class="user-profile" onclick="toggleUserMenu(event)">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($usuario, 0, 2)); ?>
                        </div>
                        <div class="user-details">
                            <h3><?php echo htmlspecialchars($usuario); ?></h3>
                            <span><?php echo ucfirst($rol); ?></span>
                        </div>
                        <i class="fas fa-chevron-down user-dropdown-icon"></i>
                        
                        <!-- Menú desplegable -->
                        <div class="user-dropdown-menu" id="userDropdownMenu">
                            <div class="dropdown-header">
                                <div class="dropdown-avatar">
                                    <?php echo strtoupper(substr($usuario, 0, 2)); ?>
                                </div>
                                <div class="dropdown-info">
                                    <h4><?php echo htmlspecialchars($usuario); ?></h4>
                                    <p><?php echo ucfirst($rol); ?></p>
                                </div>
                            </div>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item" onclick="alert('Perfil en desarrollo')">
                                <i class="fas fa-user"></i>
                                <span>Mi Perfil</span>
                            </a>
                            <a href="#" class="dropdown-item" onclick="switchTab('configuracion'); toggleUserMenu(event)">
                                <i class="fas fa-cog"></i>
                                <span>Configuración</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="logout.php" class="dropdown-item logout-item">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Cerrar Sesión</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Contenido principal -->
        <main class="admin-main">
            <!-- Navegación por tabs -->
            <nav class="admin-nav">
                <div class="nav-tabs">
                    <div class="nav-tab active" data-tab="dashboard" onclick="switchTab('dashboard')">
                        <a href="javascript:void(0)">
                            <i class="fas fa-chart-line"></i>
                            <span>Dashboard</span>
                        </a>
                    </div>
                    <div class="nav-tab" data-tab="productos" onclick="switchTab('productos')">
                        <a href="javascript:void(0)">
                            <i class="fas fa-tshirt"></i>
                            <span>Productos</span>
                        </a>
                    </div>
                    <div class="nav-tab" data-tab="categorias" onclick="switchTab('categorias')">
                        <a href="javascript:void(0)">
                            <i class="fas fa-tags"></i>
                            <span>Categorías</span>
                        </a>
                    </div>
                    <div class="nav-tab" data-tab="marcas" onclick="switchTab('marcas')">
                        <a href="javascript:void(0)">
                            <i class="fas fa-copyright"></i>
                            <span>Marcas</span>
                        </a>
                    </div>
                    <div class="nav-tab" data-tab="usuarios" onclick="switchTab('usuarios')">
                        <a href="javascript:void(0)">
                            <i class="fas fa-users"></i>
                            <span>Usuarios</span>
                        </a>
                    </div>
                    <div class="nav-tab" data-tab="configuracion" onclick="switchTab('configuracion')">
                        <a href="javascript:void(0)">
                            <i class="fas fa-cog"></i>
                            <span>Configuración</span>
                        </a>
                    </div>
                </div>
            </nav>

            <!-- Contenido de los tabs -->
            <div class="tab-content">
                <!-- Dashboard Tab -->
                <div class="tab-pane active" id="dashboard">
                    <?php include 'app/views/admin/admin_dashboard.php'; ?>
                </div>

                <!-- Productos Tab -->
                <div class="tab-pane" id="productos">
                    <div id="productos-content">
                        <p class="loading-message">
                            <i class="fas fa-spinner fa-spin"></i>
                            Cargando productos...
                        </p>
                    </div>
                </div>

                <!-- Categorías Tab -->
                <div class="tab-pane" id="categorias">
                    <div id="categorias-content">
                        <p class="loading-message">
                            <i class="fas fa-spinner fa-spin"></i>
                            Cargando categorías...
                        </p>
                    </div>
                </div>

                <!-- Marcas Tab -->
                <div class="tab-pane" id="marcas">
                    <div id="marcas-content">
                        <p class="loading-message">
                            <i class="fas fa-spinner fa-spin"></i>
                            Cargando marcas...
                        </p>
                    </div>
                </div>

                <!-- Usuarios Tab -->
                <div class="tab-pane" id="usuarios">
                    <div id="usuarios-content">
                        <p class="loading-message">
                            <i class="fas fa-spinner fa-spin"></i>
                            Cargando usuarios...
                        </p>
                    </div>
                </div>

                <!-- Configuración Tab -->
                <div class="tab-pane" id="configuracion">
                    <div class="section-header">
                        <h1>
                            <i class="fas fa-cog"></i>
                            Configuración del Sistema
                        </h1>
                    </div>
                    
                    <div class="config-sections">
                        <div class="config-card">
                            <h3>
                                <i class="fas fa-store"></i>
                                Configuración de Tienda
                            </h3>
                            <p>Configurar nombre, descripción y datos de la tienda</p>
                            <button class="btn-secondary">
                                <i class="fas fa-edit"></i>
                                Editar
                            </button>
                        </div>
                        
                        <div class="config-card">
                            <h3>
                                <i class="fas fa-credit-card"></i>
                                Métodos de Pago
                            </h3>
                            <p>Configurar opciones de pago disponibles</p>
                            <button class="btn-secondary">
                                <i class="fas fa-edit"></i>
                                Configurar
                            </button>
                        </div>
                        
                        <div class="config-card">
                            <h3>
                                <i class="fas fa-truck"></i>
                                Envíos y Logística
                            </h3>
                            <p>Configurar opciones de envío y costos</p>
                            <button class="btn-secondary">
                                <i class="fas fa-edit"></i>
                                Configurar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- JavaScript -->
    <script>
        // ===== FUNCIONES GLOBALES PRINCIPALES =====
        
        // ===== SISTEMA DE DESTRUCCIÓN DE MÓDULOS =====
        window.destroyCurrentModule = function() {
            
            // ✅ HABILITADO: Destruir módulos automáticamente para empezar desde cero
            // Cada módulo debe tener funciones con nombres únicos para evitar conflictos
            
            // 1. Llamar a las funciones de destrucción específicas de cada módulo
            try {
                if (typeof window.destroyProductosModule === 'function') {
                    window.destroyProductosModule();
                }
            } catch (e) {
            }
            
            try {
                if (typeof window.destroyCategoriasModule === 'function') {
                    window.destroyCategoriasModule();
                }
            } catch (e) {
            }
            
            try {
                if (typeof window.destroyMarcasModule === 'function') {
                    window.destroyMarcasModule();
                }
            } catch (e) {
            }
            
            try {
                if (typeof window.destroyUsuariosModule === 'function') {
                    window.destroyUsuariosModule();
                }
            } catch (e) {
            }
            
            // 2. Cerrar todos los modales abiertos
            try {
                const modals = document.querySelectorAll('.modal, .product-view-modal, [class*="-modal"]');
                modals.forEach(modal => {
                    if (modal.classList.contains('show') || modal.classList.contains('is-open')) {
                        modal.remove();
                    }
                });
                document.body.classList.remove('modal-open');
            } catch (e) {
            }
            
            // 3. Limpiar event listeners de búsqueda global
            const searchInputs = document.querySelectorAll('[id*="search-"]');
            searchInputs.forEach(input => {
                const newInput = input.cloneNode(true);
                input.parentNode.replaceChild(newInput, input);
            });
            
        };
        
        // Hacer switchTab disponible globalmente (REDEFINICIÓN COMPLETA)
        window.switchTab = function(tabId) {
            
            // 1. DESTRUIR MÓDULO ANTERIOR ANTES DE CAMBIAR
            try {
                if (typeof window.destroyCurrentModule === 'function') {
                    window.destroyCurrentModule();
                }
            } catch (e) {
            }
            
            // 2. Cerrar cualquier modal abierto antes de cambiar de sección
            try {
                if (typeof forceCloseModal === 'function') {
                    forceCloseModal();
                }
            } catch (e) {
            }
            
            // 3. Remover clase active de todos los tabs
            document.querySelectorAll('.nav-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.remove('active');
            });
            
            // 4. Activar el tab seleccionado
            const targetTab = document.querySelector(`[data-tab="${tabId}"]`);
            const targetPane = document.getElementById(tabId);
            
            if (targetTab && targetPane) {
                targetTab.classList.add('active');
                targetPane.classList.add('active');
                
                // 5. GUARDAR en localStorage para persistir después de refresh
                try {
                    localStorage.setItem('admin_active_tab', tabId);
                } catch (e) {
                }
                
                // 6. Cargar contenido específico del tab INMEDIATAMENTE
                // La destrucción ya es síncrona, no necesitamos delay
                if (typeof loadTabContent === 'function') {
                    loadTabContent(tabId);
                }
                
            } else {
            }
        };
        
        // Marcar que switchTab está completamente listo
        window.switchTabReady = true;
        
        // Ejecutar tab pendiente si existe
        if (window.pendingSwitchTab) {
            const pendingTab = window.pendingSwitchTab;
            delete window.pendingSwitchTab;
            // Ejecutar en el próximo tick para asegurar que todo esté listo
            setTimeout(() => window.switchTab(pendingTab), 0);
        }
        
        
        // Función para resetear el tab guardado (útil para debugging)
        window.resetActiveTab = function() {
            try {
                localStorage.removeItem('admin_active_tab');
            } catch (e) {
            }
        };
        
        // Función de prueba para verificar que todo funciona
        window.testNavigation = function() {
            switchTab('productos');
        };
        
        // Función para probar directamente
        window.testProductos = function() {
            window.switchTab('productos');
        };
        
        // Event listeners se configurarán en el DOMContentLoaded unificado más abajo
        
        // ===== FUNCIONES DEL MODAL DE PRODUCTOS (DEFINIDAS TEMPRANO) =====
        
        function showCreateProductModal() {
            showModalOverlayCreate();
        }

        function showEditProductModal(productId) {
            showModalOverlayEdit(productId);
        }

        function showViewProductModal(productId) {
            showModalOverlayView(productId);
        }

        function editProduct(productId) {
            showModalOverlayEdit(productId);
        }

        function viewProduct(productId) {
            // Redirigir a la página de detalles del producto
            window.location.href = 'product-details.php?id=' + productId;
        }

        function openProductModal(action, productId) {
            if (action === 'create') {
                showModalOverlayCreate();
            } else if (action === 'edit') {
                showModalOverlayEdit(productId);
            } else if (action === 'view') {
                showModalOverlayView(productId);
            }
        }

        function showModalOverlayCreate() {
            try {
                document.body.classList.add('modal-open');
                
                let overlay = document.getElementById('product-modal-overlay');
                if (overlay) {
                    overlay.remove();
                }
                
                overlay = document.createElement('div');
                overlay.id = 'product-modal-overlay';
                overlay.className = 'modal-overlay';
                // CAMBIO: No crear modal-content aquí, solo un contenedor temporal
                overlay.innerHTML = `
                    <div id="modal-content-wrapper">
                        <div class="loading-spinner">
                            <div class="spinner"></div>
                            <p>Cargando modal...</p>
                        </div>
                    </div>
                `;
                
                // Agregar evento de clic fuera del modal para cerrarlo
                overlay.addEventListener('click', function(e) {
                    if (e.target === overlay) {
                        closeProductModal();
                    }
                });
                
                document.body.appendChild(overlay);
                
                // Activar overlay con delay para animación - USAR .show
                requestAnimationFrame(() => {
                    overlay.classList.add('show');
                });
                
                const fetchUrl = 'app/views/admin/product_modal.php?action=create';
                
                fetch(fetchUrl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Error HTTP: ${response.status}`);
                    }
                    return response.text();
                })
                .then(html => {
                    // CAMBIO: Usar outerHTML para reemplazar completamente el wrapper temporal
                    const wrapper = overlay.querySelector('#modal-content-wrapper');
                    if (wrapper) {
                        wrapper.outerHTML = html;
                        
                        const scripts = overlay.querySelectorAll('script');
                        scripts.forEach((script, index) => {
                            if (script.textContent && script.textContent.trim()) {
                                try {
                                    eval(script.textContent);
                                } catch (scriptError) {
                                }
                            }
                        });
                        
                        setTimeout(() => {
                            if (typeof setupFileUpload === 'function') {
                                setupFileUpload();
                            }
                        }, 200);
                    }
                })
                .catch(error => {
                    const wrapper = overlay.querySelector('#modal-content-wrapper');
                    if (wrapper) {
                        wrapper.innerHTML = `
                            <div style="padding: 20px; text-align: center;">
                                <h3 style="color: #dc3545;">Error al cargar modal</h3>
                                <p>Error: ${error.message}</p>
                                <button onclick="closeProductModal()" style="padding: 8px 16px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer;">Cerrar</button>
                            </div>
                        `;
                    }
                });
            } catch (mainError) {
                
                document.body.classList.remove('modal-open');
                const existingOverlay = document.getElementById('product-modal-overlay');
                if (existingOverlay) {
                    existingOverlay.remove();
                }
            }
        }

        function showModalOverlayEdit(productId) {
            try {
                document.body.classList.add('modal-open');
                
                let overlay = document.getElementById('product-modal-overlay');
                if (overlay) {
                    overlay.remove();
                }
                
                overlay = document.createElement('div');
                overlay.id = 'product-modal-overlay';
                overlay.className = 'modal-overlay';
                // CAMBIO: No crear modal-content aquí, solo un contenedor temporal
                overlay.innerHTML = `
                    <div id="modal-content-wrapper">
                        <div class="loading-spinner">
                            <div class="spinner"></div>
                            <p>Cargando modal...</p>
                        </div>
                    </div>
                `;
                
                // Agregar evento de clic fuera del modal para cerrarlo
                overlay.addEventListener('click', function(e) {
                    if (e.target === overlay) {
                        log('🖱️ Clic fuera del modal detectado, cerrando...');
                        closeProductModal();
                    }
                });
                
                document.body.appendChild(overlay);
                
                // Activar overlay con delay para animación - USAR .show
                requestAnimationFrame(() => {
                    overlay.classList.add('show');
                });
                
                const fetchUrl = `app/views/admin/product_modal.php?action=edit&id=${productId}`;
                
                fetch(fetchUrl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Error HTTP: ${response.status}`);
                    }
                    return response.text();
                })
                .then(html => {
                    // CAMBIO: Usar outerHTML para reemplazar completamente el wrapper temporal
                    const wrapper = overlay.querySelector('#modal-content-wrapper');
                    if (wrapper) {
                        wrapper.outerHTML = html;
                        
                        const scripts = overlay.querySelectorAll('script');
                        scripts.forEach((script, index) => {
                            if (script.textContent && script.textContent.trim()) {
                                try {
                                    eval(script.textContent);
                                } catch (scriptError) {
                                }
                            }
                        });
                        
                        setTimeout(() => {
                            if (typeof setupFileUpload === 'function') {
                                setupFileUpload();
                            }
                        }, 200);
                    }
                })
                .catch(error => {
                    const wrapper = overlay.querySelector('#modal-content-wrapper');
                    if (wrapper) {
                        wrapper.innerHTML = `
                            <div style="padding: 20px; text-align: center;">
                                <h3 style="color: #dc3545;">Error al cargar modal</h3>
                                <p>Error: ${error.message}</p>
                                <button onclick="closeProductModal()" style="padding: 8px 16px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer;">Cerrar</button>
                            </div>
                        `;
                    }
                });
            } catch (mainError) {
                
                document.body.classList.remove('modal-open');
                const existingOverlay = document.getElementById('product-modal-overlay');
                if (existingOverlay) {
                    existingOverlay.remove();
                }
            }
        }

        function showModalOverlayView(productId) {
            try {
                document.body.classList.add('modal-open');
                
                // Verificar y cargar CSS del modal Ver Producto si no está cargado
                if (!document.querySelector('link[href*="product-view-modal.css"]')) {
                    const cssLink = document.createElement('link');
                    cssLink.rel = 'stylesheet';
                    cssLink.href = 'public/assets/css/product-view-modal.css';
                    document.head.appendChild(cssLink);
                }
                
                let existingModal = document.querySelector('.product-view-modal');
                if (existingModal) {
                    existingModal.remove();
                }
                
                // CREAR CONTENEDOR TEMPORAL para cargar el modal
                const tempContainer = document.createElement('div');
                tempContainer.style.display = 'none';
                document.body.appendChild(tempContainer);
                
                const fetchUrl = `app/views/admin/product_modal.php?action=view&id=${productId}`;
                
                fetch(fetchUrl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Error HTTP: ${response.status}`);
                    }
                    return response.text();
                })
                .then(html => {
                    
                    // Insertar HTML en contenedor temporal
                    tempContainer.innerHTML = html;
                    
                    // Buscar el modal Ver Producto en el HTML cargado
                    const productModal = tempContainer.querySelector('.product-view-modal');
                    if (productModal) {
                        log('✅ Modal Ver Producto encontrado en HTML');
                        
                        // Agregar el modal directamente al body
                        document.body.appendChild(productModal);
                        log('✅ Modal Ver Producto agregado al DOM');
                        
                        // Debug: verificar estilos aplicados
                        const computedStyles = window.getComputedStyle(productModal);
                        log('🔍 Estilos iniciales del modal:', {
                            display: computedStyles.display,
                            opacity: computedStyles.opacity,
                            visibility: computedStyles.visibility,
                            position: computedStyles.position,
                            zIndex: computedStyles.zIndex
                        });
                        
                        // Activar modal con animación
                        requestAnimationFrame(() => {
                            productModal.classList.add('show');
                            log('✅ Clase show agregada al modal Ver Producto');
                            
                            // Debug: verificar estilos después de agregar .show
                            setTimeout(() => {
                                const computedStylesAfter = window.getComputedStyle(productModal);
                                log('🔍 Estilos después de .show:', {
                                    display: computedStylesAfter.display,
                                    opacity: computedStylesAfter.opacity,
                                    visibility: computedStylesAfter.visibility,
                                    classes: productModal.className
                                });
                                
                                // Llamar función de debug del modal si existe
                                if (typeof window.debugModal === 'function') {
                                    window.debugModal();
                                }
 
                              
                            }, 50);
                        });
                    }
                    
                    // Limpiar contenedor temporal
                    tempContainer.remove();
                    
                    // Ejecutar scripts dentro del modal
                    const scripts = document.querySelectorAll('.product-view-modal script');
                    scripts.forEach((script, index) => {
                        if (script.textContent && script.textContent.trim()) {
                            try {
                                eval(script.textContent);
                            } catch (scriptError) {
                            }
                        }
                    });
                })
                .catch(error => {
                    // Crear un modal de error simple
                    const errorModal = document.createElement('div');
                    errorModal.className = 'product-view-modal show';
                    errorModal.innerHTML = `
                        <div class="product-view-modal__overlay"></div>
                        <div class="product-view-modal__container">
                            <div class="product-view-modal__header">
                                <h2>Error al cargar modal</h2>
                                <button type="button" class="product-view-modal__close" onclick="closeProductModal()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <div class="product-view-modal__body">
                                <p>Error: ${error.message}</p>
                            </div>
                            <div class="product-view-modal__footer">
                                <button onclick="closeProductModal()" class="product-view-modal__btn product-view-modal__btn--secondary">Cerrar</button>
                            </div>
                        </div>
                    `;
                    document.body.appendChild(errorModal);
                    
                    // Limpiar contenedor temporal si existe
                    const tempContainer = document.querySelector('div[style*="display: none"]');
                    if (tempContainer) tempContainer.remove();
                });
            } catch (mainError) {
                
                document.body.classList.remove('modal-open');
                const existingModal = document.querySelector('.product-view-modal');
                if (existingModal) {
                    existingModal.remove();
                }
            }
        }

        // ===== EXPONER FUNCIONES GLOBALMENTE INMEDIATAMENTE =====
        // Estas funciones deben estar disponibles para admin_productos.php
        window.showCreateProductModal = showCreateProductModal;
        window.showEditProductModal = showEditProductModal;
        window.showViewProductModal = showViewProductModal;
        window.editProduct = editProduct;
        window.viewProduct = viewProduct;
        window.openProductModal = openProductModal;
        window.showModalOverlayCreate = showModalOverlayCreate;
        window.showModalOverlayEdit = showModalOverlayEdit;
        window.showModalOverlayView = showModalOverlayView;
        window.closeProductModal = closeProductModal;
        
        // ===== FUNCIONES DEL MODAL DE CATEGORÍAS (IGUALES A PRODUCTOS) =====
        
        function showCreateCategoriaModal() {
            showModalOverlayCreateCategoria();
        }

        function showEditCategoriaModal(categoriaId) {
            showModalOverlayEditCategoria(categoriaId);
        }

        function showViewCategoriaModal(categoriaId) {
            showModalOverlayViewCategoria(categoriaId);
        }

        function editCategoria(categoriaId) {
            showModalOverlayEditCategoria(categoriaId);
        }

        function verCategoria(categoriaId) {
            showModalOverlayViewCategoria(categoriaId);
        }

        function openCategoriaModal(action, categoriaId) {
            if (action === 'create') {
                showModalOverlayCreateCategoria();
            } else if (action === 'edit') {
                showModalOverlayEditCategoria(categoriaId);
            } else if (action === 'view') {
                showModalOverlayViewCategoria(categoriaId);
            }
        }

        function showModalOverlayCreateCategoria() {
            try {
                document.body.classList.add('modal-open');
                
                let overlay = document.getElementById('categoria-modal-overlay');
                if (overlay) {
                    overlay.remove();
                }
                
                overlay = document.createElement('div');
                overlay.id = 'categoria-modal-overlay';
                overlay.className = 'modal-overlay';
                overlay.innerHTML = `
                    <div id="modal-content-wrapper">
                        <div class="loading-spinner">
                            <div class="spinner"></div>
                            <p>Cargando modal...</p>
                        </div>
                    </div>
                `;
                
                overlay.addEventListener('click', function(e) {
                    if (e.target === overlay) {
                        closeCategoriaModal();
                    }
                });
                
                document.body.appendChild(overlay);
                
                requestAnimationFrame(() => {
                    overlay.classList.add('show');
                });
                
                const fetchUrl = 'app/views/admin/categorias_modal.php?action=create';
                
                fetch(fetchUrl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Error HTTP: ${response.status}`);
                    }
                    return response.text();
                })
                .then(html => {
                    const wrapper = overlay.querySelector('#modal-content-wrapper');
                    if (wrapper) {
                        wrapper.outerHTML = html;
                        
                        const scripts = overlay.querySelectorAll('script');
                        scripts.forEach((script, index) => {
                            if (script.textContent && script.textContent.trim()) {
                                try {
                                    eval(script.textContent);
                                } catch (scriptError) {
                                }
                            }
                        });
                    }
                })
                .catch(error => {
                    const wrapper = overlay.querySelector('#modal-content-wrapper');
                    if (wrapper) {
                        wrapper.innerHTML = `
                            <div style="padding: 20px; text-align: center;">
                                <h3 style="color: #dc3545;">Error al cargar modal</h3>
                                <p>Error: ${error.message}</p>
                                <button onclick="closeCategoriaModal()" style="padding: 8px 16px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer;">Cerrar</button>
                            </div>
                        `;
                    }
                });
            } catch (mainError) {
                document.body.classList.remove('modal-open');
                const existingOverlay = document.getElementById('categoria-modal-overlay');
                if (existingOverlay) {
                    existingOverlay.remove();
                }
            }
        }

        function showModalOverlayEditCategoria(categoriaId) {
            try {
                document.body.classList.add('modal-open');
                
                let overlay = document.getElementById('categoria-modal-overlay');
                if (overlay) {
                    overlay.remove();
                }
                
                overlay = document.createElement('div');
                overlay.id = 'categoria-modal-overlay';
                overlay.className = 'modal-overlay';
                overlay.innerHTML = `
                    <div id="modal-content-wrapper">
                        <div class="loading-spinner">
                            <div class="spinner"></div>
                            <p>Cargando modal...</p>
                        </div>
                    </div>
                `;
                
                overlay.addEventListener('click', function(e) {
                    if (e.target === overlay) {
                        closeCategoriaModal();
                    }
                });
                
                document.body.appendChild(overlay);
                
                requestAnimationFrame(() => {
                    overlay.classList.add('show');
                });
                
                const fetchUrl = `app/views/admin/categorias_modal.php?action=edit&id=${categoriaId}`;
                
                fetch(fetchUrl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Error HTTP: ${response.status}`);
                    }
                    return response.text();
                })
                .then(html => {
                    const wrapper = overlay.querySelector('#modal-content-wrapper');
                    if (wrapper) {
                        wrapper.outerHTML = html;
                        
                        const scripts = overlay.querySelectorAll('script');
                        scripts.forEach((script, index) => {
                            if (script.textContent && script.textContent.trim()) {
                                try {
                                    eval(script.textContent);
                                } catch (scriptError) {
                                }
                            }
                        });
                    }
                })
                .catch(error => {
                    const wrapper = overlay.querySelector('#modal-content-wrapper');
                    if (wrapper) {
                        wrapper.innerHTML = `
                            <div style="padding: 20px; text-align: center;">
                                <h3 style="color: #dc3545;">Error al cargar modal</h3>
                                <p>Error: ${error.message}</p>
                                <button onclick="closeCategoriaModal()" style="padding: 8px 16px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer;">Cerrar</button>
                            </div>
                        `;
                    }
                });
            } catch (mainError) {
                document.body.classList.remove('modal-open');
                const existingOverlay = document.getElementById('categoria-modal-overlay');
                if (existingOverlay) {
                    existingOverlay.remove();
                }
            }
        }

        function showModalOverlayViewCategoria(categoriaId) {
            try {
                document.body.classList.add('modal-open');
                
                let overlay = document.getElementById('categoria-modal-overlay');
                if (overlay) {
                    overlay.remove();
                }
                
                overlay = document.createElement('div');
                overlay.id = 'categoria-modal-overlay';
                overlay.className = 'modal-overlay';
                overlay.innerHTML = `
                    <div id="modal-content-wrapper">
                        <div class="loading-spinner">
                            <div class="spinner"></div>
                            <p>Cargando modal...</p>
                        </div>
                    </div>
                `;
                
                overlay.addEventListener('click', function(e) {
                    if (e.target === overlay) {
                        closeCategoriaModal();
                    }
                });
                
                document.body.appendChild(overlay);
                
                requestAnimationFrame(() => {
                    overlay.classList.add('show');
                });
                
                const fetchUrl = `app/views/admin/categorias_modal.php?action=view&id=${categoriaId}`;
                
                fetch(fetchUrl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Error HTTP: ${response.status}`);
                    }
                    return response.text();
                })
                .then(html => {
                    const wrapper = overlay.querySelector('#modal-content-wrapper');
                    if (wrapper) {
                        wrapper.outerHTML = html;
                        
                        const scripts = overlay.querySelectorAll('script');
                        scripts.forEach((script, index) => {
                            if (script.textContent && script.textContent.trim()) {
                                try {
                                    eval(script.textContent);
                                } catch (scriptError) {
                                }
                            }
                        });
                    }
                })
                .catch(error => {
                    const wrapper = overlay.querySelector('#modal-content-wrapper');
                    if (wrapper) {
                        wrapper.innerHTML = `
                            <div style="padding: 20px; text-align: center;">
                                <h3 style="color: #dc3545;">Error al cargar modal</h3>
                                <p>Error: ${error.message}</p>
                                <button onclick="closeCategoriaModal()" style="padding: 8px 16px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer;">Cerrar</button>
                            </div>
                        `;
                    }
                });
            } catch (mainError) {
                document.body.classList.remove('modal-open');
                const existingOverlay = document.getElementById('categoria-modal-overlay');
                if (existingOverlay) {
                    existingOverlay.remove();
                }
            }
        }

        function closeCategoriaModal() {
            
            const overlay = document.getElementById('categoria-modal-overlay');
            if (overlay) {
                overlay.classList.remove('show');
                setTimeout(() => {
                    overlay.remove();
                    document.body.classList.remove('modal-open');
                    
                    // Recargar lista de categorías si existe la función
                    if (typeof window.loadCategorias === 'function') {
                        window.loadCategorias();
                    }
                }, 300);
            } else {
                document.body.classList.remove('modal-open');
            }
        }

        // Exponer funciones de categorías globalmente
        window.showCreateCategoriaModal = showCreateCategoriaModal;
        window.showEditCategoriaModal = showEditCategoriaModal;
        window.showViewCategoriaModal = showViewCategoriaModal;
        window.editCategoria = editCategoria;
        window.verCategoria = verCategoria;
        window.openCategoriaModal = openCategoriaModal;
        window.showModalOverlayCreateCategoria = showModalOverlayCreateCategoria;
        window.showModalOverlayEditCategoria = showModalOverlayEditCategoria;
        window.showModalOverlayViewCategoria = showModalOverlayViewCategoria;
        window.closeCategoriaModal = closeCategoriaModal;
        
        // Alias en inglés para compatibilidad
        window.showCreateCategoryModal = showCreateCategoriaModal;
        window.showEditCategoryModal = showEditCategoriaModal;
        window.showViewCategoryModal = showViewCategoriaModal;
        
        // ===== FUNCIÓN PARA NUEVA CATEGORÍA =====
        window.openNewCategoryModal = function() {
            
            // Si ya estamos en la tab de categorías, abrir directo
            const categoriaTab = document.getElementById('categorias');
            const isInCategoriaTab = categoriaTab && categoriaTab.classList.contains('active');
            
            if (isInCategoriaTab && typeof window.showCreateCategoriaModal === 'function') {
                window.showCreateCategoriaModal();
                return;
            }
            
            // Si no estamos en la tab, cambiar primero
            switchTab('categorias');
            
            // Esperar a que el contenido de categorías se cargue y abrir modal
            let attempts = 0;
            const maxAttempts = 20; // 2 segundos máximo
            
            const checkAndOpen = setInterval(function() {
                attempts++;
                
                if (typeof window.showCreateCategoriaModal === 'function') {
                    clearInterval(checkAndOpen);
                    window.showCreateCategoriaModal();
                } else if (attempts >= maxAttempts) {
                    clearInterval(checkAndOpen);
                    showNotification('Error al abrir el modal de categoría. Por favor intente de nuevo.', 'error');
                }
            }, 100); // Revisar cada 100ms
        };
       
        window.cleanupFilters = function() {
            try {
                // Remove elements that are modals or mobile filter buttons by class
                document.querySelectorAll('.filters-modal, .mobile-filter-btn, .filters-modal-overlay').forEach(el => el.remove());

                // Remove elements whose id starts with 'filters-modal' or 'mobile-filter' (covers variants)
                document.querySelectorAll('[id]').forEach(el => {
                    const id = el.id || '';
                    if (id.startsWith('filters-modal') || id.startsWith('mobile-filter')) {
                        el.remove();
                    }
                });

                // Ensure body scroll isn't blocked
                document.body.style.overflow = '';
                document.body.classList.remove('modal-open');
            } catch (e) {
            }
        };

        function loadTabContent(tabId) {
            // Limpiar posibles modales/filtros del módulo anterior
            if (typeof window.cleanupFilters === 'function') {
                window.cleanupFilters();
            }
            
            switch(tabId) {
                case 'productos':
                    loadProductosSection();
                    break;
                case 'categorias':
                    loadCategoriasSection();
                    break;
                case 'marcas':
                    loadMarcasSection();
                    break;
                case 'usuarios':
                    loadUsuariosSection();
                    break;
                default:
            }
        }
        
        // ===== FUNCIÓN ÚNICA PARA PRODUCTOS =====
        function loadProductosSection() {
            
            const containerId = 'productos-content';
            const targetContainer = document.getElementById(containerId);

            
            // Limpiar completamente el contenedor
            targetContainer.innerHTML = '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i> Cargando productos...</div>';
            
            fetch('app/views/admin/admin_productos.php?_=' + Date.now()) // Cache busting
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Error HTTP: ${response.status}`);
                    }
                    return response.text();
                })
                .then(html => {
                    
                    // RESETEAR completamente el contenedor
                    targetContainer.innerHTML = '';
                    targetContainer.innerHTML = html;
                    
                    
                    // Ejecutar scripts de forma segura (evitar redeclaración de variables)
                    const scripts = targetContainer.querySelectorAll('script');
                    
                    scripts.forEach((script, index) => {
                        try {
                            if (script.textContent && script.textContent.trim()) {
                                // Ejecutar con eval en lugar de appendChild para evitar conflictos
                                // de redeclaración de variables globales (let/const)
                                eval(script.textContent);
                            }
                        } catch (error) {
                        }
                    });
                    
                    // NOTA: initializeProductsModule() se auto-ejecuta dentro del script evaluado
                    // No es necesario llamarlo aquí
                    
                })
                .catch(error => {
                    targetContainer.innerHTML = `
                        <div class="error-message">
                            <i class="fas fa-exclamation-triangle"></i>
                            <h3>Error al cargar productos</h3>
                            <p>${error.message}</p>
                            <button onclick="loadProductosSection()" class="btn-primary">
                                <i class="fas fa-refresh"></i> Reintentar
                            </button>
                        </div>
                    `;
                });
        }
        
        // ===== FUNCIONES GLOBALES DE CATEGORÍAS (deben estar disponibles antes de cargar el HTML) =====
        
        // Variable global para tracking de vista actual en categorías
        window.categorias_currentView = 'table';
        window.categorias_activeFloatingContainer = null;
        
        // Función para cambiar vista (tabla/grid) - GLOBAL
        window.toggleCategoriaView = function(viewType) {
            
            const isMobile = window.innerWidth <= 768;
            if (isMobile && viewType === 'table') {
                return;
            }
            
            const tableContainer = document.querySelector('.data-table-wrapper');
            const gridContainer = document.querySelector('.categorias-grid');
            const viewButtons = document.querySelectorAll('.view-btn');
            
            if (viewType === 'grid') {
                if (tableContainer) tableContainer.style.display = 'none';
                if (gridContainer) {
                    gridContainer.style.display = 'grid';
                } else {
                    // Si no existe, intentar crearlo
                    if (typeof window.createGridView === 'function') {
                        window.createGridView();
                    }
                }
                window.categorias_currentView = 'grid';
            } else {
                if (tableContainer) tableContainer.style.display = 'block';
                if (gridContainer) gridContainer.style.display = 'none';
                window.categorias_currentView = 'table';
            }
            
            // Actualizar botones activos
            viewButtons.forEach(btn => {
                const btnView = btn.getAttribute('data-view');
                if (btnView === viewType) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });
            
        };
        
        // Función para mostrar menú de acciones - GLOBAL
        window.showCategoriaActionMenu = function(button, categoriaId, categoriaNombre) {
            
            // Si ya hay un menú abierto, cerrarlo primero
            if (window.categorias_activeFloatingContainer) {
                if (typeof window.closeCategoriasFloatingActions === 'function') {
                    window.closeCategoriasFloatingActions();
                }
            }
            
            // Crear contenedor del menú
            if (typeof window.createCategoriaAnimatedFloatingContainer === 'function') {
                window.createCategoriaAnimatedFloatingContainer(button, categoriaId, categoriaNombre);
            }
        };
        
        // Función para cerrar menús flotantes - GLOBAL
        window.closeCategoriasFloatingActions = function() {
            const container = window.categorias_activeFloatingContainer;
            if (container && container.parentElement) {
                container.classList.add('closing');
                setTimeout(() => {
                    if (container.parentElement) {
                        container.remove();
                    }
                    window.categorias_activeFloatingContainer = null;
                }, 300);
            }
        };
        
        
        // ===== FUNCIÓN ÚNICA PARA CATEGORÍAS =====
        function loadCategoriasSection() {
            
            const containerId = 'categorias-content';
            const targetContainer = document.getElementById(containerId);
        
            
            // Limpiar completamente el contenedor
            targetContainer.innerHTML = '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i> Cargando categorías...</div>';
            
            fetch('app/views/admin/admin_categorias.php?_=' + Date.now())
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Error HTTP: ${response.status}`);
                    }
                    return response.text();
                })
                .then(html => {
                    
                    // RESETEAR completamente el contenedor
                    targetContainer.innerHTML = '';
                    targetContainer.innerHTML = html;
                    
                    
                    // Ejecutar scripts de forma segura (evitar redeclaración)
                    const scripts = targetContainer.querySelectorAll('script');
                    
                    scripts.forEach((script, index) => {
                        try {
                            if (script.textContent && script.textContent.trim()) {
                                // Usar Function constructor en lugar de createElement para evitar problemas de codificación
                                const scriptContent = script.textContent;
                                // Limpiar caracteres problemáticos antes de ejecutar
                                const cleanContent = scriptContent
                                    .replace(/[^\x00-\x7F]/g, function(char) {
                                        // Mantener caracteres seguros, reemplazar problemáticos
                                        const code = char.charCodeAt(0);
                                        if (code === 0xFFFD || code > 0x10000) return '';
                                        return char;
                                    });
                                
                                // Ejecutar con Function para mejor manejo de encoding
                                try {
                                    const fn = new Function(cleanContent);
                                    fn();
                                } catch (innerError) {
                                    // Si falla, intentar con eval como fallback
                                    window.eval(cleanContent);
                                }
                            }
                        } catch (error) {
                            
                        }
                    });
                    
                    
                    // Intentar cargar datos después de un breve delay
                    setTimeout(() => {
                        if (typeof window.loadCategoriasData === 'function') {
                            window.loadCategoriasData();
                        } 
                    }, 100);
                })
                .catch(error => {
                    targetContainer.innerHTML = `
                        <div class="error-message">
                            <i class="fas fa-exclamation-triangle"></i>
                            <h3>Error al cargar categorías</h3>
                            <p>${error.message}</p>
                            <button onclick="loadCategoriasSection()" class="btn-primary">
                                <i class="fas fa-refresh"></i> Reintentar
                            </button>
                        </div>
                    `;
                });
        }
        
        // ===== FUNCIÓN ÚNICA PARA MARCAS =====
        function loadMarcasSection() {
            
            const containerId = 'marcas-content';
            const targetContainer = document.getElementById(containerId);
            
            if (!targetContainer) {
                return;
            }
            
            // Limpiar completamente el contenedor
            targetContainer.innerHTML = '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i> Cargando marcas...</div>';
            
            fetch('app/views/admin/admin_marcas.php?_=' + Date.now())
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Error HTTP: ${response.status}`);
                    }
                    return response.text();
                })
                .then(html => {
                    
                    // RESETEAR completamente el contenedor
                    targetContainer.innerHTML = '';
                    targetContainer.innerHTML = html;
                    
                    
                    // Ejecutar scripts de forma segura (evitar redeclaración)
                    const scripts = targetContainer.querySelectorAll('script');
                    
                    scripts.forEach((script, index) => {
                        try {
                            if (script.textContent && script.textContent.trim()) {
                                eval(script.textContent);
                            }
                        } catch (error) {
                        }
                    });
                    

                })
                .catch(error => {
                    targetContainer.innerHTML = `
                        <div class="error-message">
                            <i class="fas fa-exclamation-triangle"></i>
                            <h3>Error al cargar marcas</h3>
                            <p>${error.message}</p>
                            <button onclick="loadMarcasSection()" class="btn-primary">
                                <i class="fas fa-refresh"></i> Reintentar
                            </button>
                        </div>
                    `;
                });
        }
        
        // ===== FUNCIÓN PARA USUARIOS (Mantener compatibilidad) =====
        function loadUsuariosSection() {
            
            const containerId = 'usuarios-content';
            const targetContainer = document.getElementById(containerId);

            
            targetContainer.innerHTML = '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i> Cargando usuarios...</div>';
            
            fetch('app/views/admin/admin_usuarios.php?_=' + Date.now())
                .then(response => response.ok ? response.text() : Promise.reject(response))
                .then(html => {
                    targetContainer.innerHTML = html;
                    const scripts = targetContainer.querySelectorAll('script');
                    scripts.forEach(script => {
                        if (script.textContent && script.textContent.trim()) {
                            try {
                                const newScript = document.createElement('script');
                                newScript.textContent = script.textContent;
                                document.head.appendChild(newScript);
                                document.head.removeChild(newScript);
                            } catch (e) {
                            }
                        }
                    });
                })
                .catch(error => {
                    targetContainer.innerHTML = '<div class="error-message">Error al cargar usuarios</div>';
                });
        }
        
        // ===== ALIAS PARA RETROCOMPATIBILIDAD =====
        // Mantener las funciones antiguas apuntando a las nuevas
        window.loadProductos = loadProductosSection;
        window.loadCategorias = loadCategoriasSection;
        window.loadMarcas = loadMarcasSection;
        window.loadUsuarios = loadUsuariosSection;
        
        // ===== FIN SISTEMA DE CARGA ÚNICO =====
        
        // Funciones placeholder para formularios
        function showAddProductForm() {
            // Solo cambiar a la pestaña de productos
            switchTab('productos');
        }
        
        function showAddCategoryForm() {
            // Funcionalidad pendiente
        }
        
        function showAddUserForm() {
            // Cambiar a la pestaña de usuarios si no está activa
            if (!document.getElementById('usuarios').classList.contains('active')) {
                switchTab('usuarios');
                
                // Esperar a que se cargue la vista y luego abrir el modal
                setTimeout(() => {
                    if (typeof showAddUserModal === 'function') {
                        showAddUserModal();
                    } else {
                        // Si la función no está disponible, hacer clic en el botón
                        const newUserBtn = document.querySelector('#usuarios-content .btn-primary');
                        if (newUserBtn) {
                            newUserBtn.click();
                        }
                    }
                }, 500);
            } else {
                // Si ya está en la pestaña, abrir directamente el modal
                if (typeof showAddUserModal === 'function') {
                    showAddUserModal();
                } else {
                    const newUserBtn = document.querySelector('#usuarios-content .btn-primary');
                    if (newUserBtn) {
                        newUserBtn.click();
                    }
                }
            }
        }



        // Función global para remover preview
        function removeImagePreview() {
            
            const fileInput = document.getElementById('imagen');
            const preview = document.getElementById('imagePreview');
            const container = document.querySelector('.file-upload-container');
            const currentImageDisplay = document.querySelector('.current-image-display');
            
            if (fileInput) fileInput.value = '';
            if (preview) {
                preview.style.display = 'none';
                preview.classList.remove('replacing-current');
            }
            
            // RESTAURAR IMAGEN ORIGINAL si existe
            if (currentImageDisplay) {
                currentImageDisplay.style.display = 'block';
            }
            
            if (container) {
                container.classList.remove('has-file', 'confirmed');
            }
            
            if (window.currentImageBlob) {
                URL.revokeObjectURL(window.currentImageBlob);
                delete window.currentImageBlob;
            }
        }

        // Función global para confirmar nueva imagen
        function confirmNewImage() {
            
            const preview = document.getElementById('imagePreview');
            const container = document.querySelector('.file-upload-container');
            const currentImageDisplay = document.querySelector('.current-image-display');
            
            if (preview) {
                preview.style.display = 'none';
                preview.classList.remove('replacing-current');
            }
            

            
            if (container) {
                container.classList.add('confirmed');
            }
            
            // Mostrar notificación simple
            
            // Podrías mostrar un indicador visual de que hay una imagen pendiente
            const imageSection = document.querySelector('[data-section="imagen"]');
            if (imageSection) {
                imageSection.classList.add('has-pending-image');
            }
        }

        function openProductModal(action, productId = null) {
            
            // Pasar los parámetros a showModalOverlay
            try {
                showModalOverlay(action, productId);
            } catch (error) {
            }
        }

        function showModalOverlay(action = 'create', productId = null) {
            
            // Bloquear scroll del body
            document.body.classList.add('modal-open');
            
            let overlay = document.getElementById('product-modal-overlay');
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.id = 'product-modal-overlay';
                overlay.innerHTML = `
                    <div class="modal-container">
                        <div id="modal-content" class="modal-content">
                            <div style="padding: 40px; text-align: center;">
                                <i class="fas fa-spinner fa-spin" style="font-size: 2.5rem; color: #5b9bd5; margin-bottom: 15px;"></i>
                                <p style="color: #4a5568; margin: 0;">Cargando modal...</p>
                            </div>
                        </div>
                    </div>
                `;
                
                document.body.appendChild(overlay);
            }
            
            // IMPORTANTE: Limpiar eventos previos ANTES de agregar .show
            const oldOverlay = overlay;
            overlay.replaceWith(overlay.cloneNode(true));
            overlay = document.getElementById('product-modal-overlay');
            
            // Agregar evento para cerrar al hacer clic fuera del modal (SIN DUPLICAR)
            overlay.addEventListener('click', function(e) {
                if (e.target === overlay) {
                    closeProductModal();
                }
            }, { once: true }); // once:true previene ejecuciones múltiples
            
            // Mostrar overlay con animación DESPUÉS de configurar eventos
            setTimeout(() => {
                overlay.classList.add('show');
            }, 10);
            
            // Construir URL con parámetros según la acción
            let modalUrl = 'app/views/admin/product_modal.php';
            if (action === 'edit' && productId) {
                modalUrl += `?edit=1&id=${productId}`;
            } else if (action === 'view' && productId) {
                modalUrl += `?view=1&id=${productId}`;
            }
            
            fetch(modalUrl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.text();
                })
                .then(html => {
                    
                    // Reemplazar el contenido de carga con el modal real
                    const modalContent = overlay.querySelector('#modal-content');
                    if (modalContent) {
                        modalContent.outerHTML = html;
                        
                        // Configurar el sistema de archivos después de cargar
                        setTimeout(() => {
                            
                            // Ejecutar todos los scripts del modal cargado
                            const scripts = overlay.querySelectorAll('script');
                            scripts.forEach(script => {
                                if (script.innerHTML.trim()) {
                                    try {
                                        // Ejecutar el script en el contexto global
                                        eval(script.innerHTML);
                                    } catch (error) {
                                    }
                                }
                            });
                            
                            // Intentar ejecutar setupFileUpload si está disponible
                            if (typeof setupFileUpload === 'function') {
                                setupFileUpload();
                            } 
                        }, 200);
                    }
                    
                })
                .catch(error => {
                    const modalContent = overlay.querySelector('#modal-content');
                    if (modalContent) {
                        modalContent.innerHTML = `
                            <div style="padding: 20px; text-align: center;">
                                <h3 style="color: #dc3545;">Error al cargar modal</h3>
                                <p>Error: ${error.message}</p>
                                <button onclick="closeProductModal()" style="padding: 8px 16px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer;">Cerrar</button>
                            </div>
                        `;
                    }
                });
        }

        function closeProductModal() {
            
            // 💾 GUARDAR BORRADOR INMEDIATAMENTE ANTES DE CERRAR
            try {
                if (typeof window.saveFormDraft === 'function') {
                    window.saveFormDraft();
                }
            } catch (e) {
            }
            
            // Manejar modal Ver Producto (principal)
            const viewModal = document.querySelector('.product-view-modal');
            if (viewModal) {
                
                // Quitar clase show y agregar clase closing
                viewModal.classList.remove('show');
                viewModal.classList.add('closing');
                
                // Esperar a que termine la animación antes de eliminar
                setTimeout(() => {
                    if (viewModal.parentNode) {
                        viewModal.remove();
                    }
                    document.body.classList.remove('modal-open');
                }, 400); // 400ms para coincidir con la duración de la animación
                return;
            }
            
            // Fallback: manejar otros tipos de modal
            const modal = document.getElementById('product-modal-overlay');
            if (modal) {
                modal.classList.remove('show');
                modal.classList.add('closing');
                
                setTimeout(() => {
                    if (modal.parentNode) {
                        modal.remove();
                    }
                    document.body.classList.remove('modal-open');
                }, 300);
                return;
            }
            
            // Si no hay modal, solo limpiar
            document.body.classList.remove('modal-open');
        }
        
        // Configurar ESC global para admin.php también
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' || e.keyCode === 27) {
                const activeModal = document.querySelector('.product-view-modal.show, #product-modal-overlay.show');
                if (activeModal) {
                    e.preventDefault();
                    e.stopPropagation();
                    closeProductModal();
                }
            }
        }, true);
    
        // Función showNotification removida - sin notificaciones

        // Función para forzar el cierre inmediato de cualquier modal
        function forceCloseModal() {
            // Desbloquear scroll del body
            document.body.classList.remove('modal-open');
            
            const overlay = document.getElementById('product-modal-overlay');
            if (overlay) {
                overlay.style.display = 'none !important';
                overlay.style.visibility = 'hidden !important';
                overlay.classList.remove('show', 'closing');
                overlay.remove();
            }
        }


        // Función para limpiar modales residuales al cargar la página
        function cleanupModals() {
            // Desbloquear scroll del body por si quedó bloqueado
            document.body.classList.remove('modal-open');
            
            const existingOverlay = document.getElementById('product-modal-overlay');
            if (existingOverlay) {
                existingOverlay.remove();
            }
            
            // Buscar y eliminar cualquier modal residual
            const residualModals = document.querySelectorAll('[id*="modal"], .modal-overlay, .product-modal-overlay');
            residualModals.forEach(modal => {
                if (modal.id !== 'product-modal-overlay') {
                    modal.remove();
                }
            });
        }

        
        // Verificar que la clase ProductModalManager esté disponible
        if (typeof ProductModalManager !== 'undefined') {
            // Crear instancia REAL de ProductModalManager
            window.productModalManager = new ProductModalManager();
        } else {
            // Fallback: crear objeto básico de compatibilidad
            window.productModalManager = {
                showCreateProductModal: showCreateProductModal,
                showViewProductModal: showViewProductModal,
                showEditProductModal: showEditProductModal,
                forceCloseModal: forceCloseModal,
                cleanupModals: cleanupModals
            };
        }

        
        if (typeof SmoothTableUpdater !== 'undefined') {
            window.smoothTableUpdater = new SmoothTableUpdater();
        } else {
            window.smoothTableUpdater = null;
        }


        // Función global de emergencia para limpiar modales (accesible desde consola)
        window.emergencyCleanModal = function() {
            // Desbloquear scroll del body
            document.body.classList.remove('modal-open');
            forceCloseModal();
            cleanupModals();
        };


        // Configuración inicial al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            // Los event listeners ahora están en onclick directo, no necesitamos configurar aquí
            
            // RESTAURAR TAB ACTIVO desde localStorage
            try {
                const savedTab = localStorage.getItem('admin_active_tab');
                
                if (savedTab && savedTab !== 'dashboard') {
                    // Solo cambiar si es diferente de dashboard (que ya está activo por defecto)
                    const tabExists = document.querySelector(`[data-tab="${savedTab}"]`);
                    if (tabExists) {
                        switchTab(savedTab);
                    } 
                } else {
                    // Guardar dashboard como tab activo si no hay nada guardado
                    if (!savedTab) {
                        localStorage.setItem('admin_active_tab', 'dashboard');
                    }
                }
            } catch (e) {
            }
        }); // ← CIERRE de DOMContentLoaded
            

        // También limpiar cuando se recarga la página
        window.addEventListener('load', function() {
            cleanupModals();
        });

        // Limpiar modales cuando se abandona la página
        window.addEventListener('beforeunload', function() {
            forceCloseModal();
        });
        
        
    </script>


    <script>
    /**
     * Toggle del menú desplegable de usuario
     */
    function toggleUserMenu(event) {
        event.stopPropagation();
        const menu = document.getElementById('userDropdownMenu');
        if (menu) {
            menu.classList.toggle('show');
        }
    }
    
    // Cerrar menú al hacer click fuera
    document.addEventListener('click', function(event) {
        const menu = document.getElementById('userDropdownMenu');
        const userProfile = document.querySelector('.user-profile');
        
        if (menu && !userProfile.contains(event.target)) {
            menu.classList.remove('show');
        }
    });
    
    /**
     * Notificación Toast - Para feedback rápido
     */
    window.showToast = function(message, type = 'success') {
        const icons = {
            'success': 'success',
            'error': 'error',
            'warning': 'warning',
            'info': 'info'
        };
        
        const backgrounds = {
            'success': 'linear-gradient(135deg, #2ecc71 0%, #27ae60 100%)',
            'error': 'linear-gradient(135deg, #e74c3c 0%, #c0392b 100%)',
            'warning': 'linear-gradient(135deg, #f39c12 0%, #e67e22 100%)',
            'info': 'linear-gradient(135deg, #3498db 0%, #2980b9 100%)'
        };

        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            },
            customClass: {
                popup: 'modern-toast',
                title: 'modern-toast-title'
            },
            showClass: {
                popup: 'animate__animated animate__fadeInDown animate__faster'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutUp animate__faster'
            }
        });

        Toast.fire({
            icon: icons[type] || 'info',
            title: message,
            background: backgrounds[type] || backgrounds.info,
            color: '#fff',
            iconColor: '#fff'
        });
    };

    /**
     * Alerta Modal Completa - Para mensajes importantes
     */
    window.showAlert = function(title, message, type = 'info') {
        Swal.fire({
            icon: type,
            title: title,
            html: message,
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#3498db',
            customClass: {
                popup: 'modern-alert',
                confirmButton: 'modern-alert-button'
            },
            showClass: {
                popup: 'animate__animated animate__zoomIn animate__faster'
            },
            hideClass: {
                popup: 'animate__animated animate__zoomOut animate__faster'
            }
        });
    };

    /**
     * Confirmación de Eliminación - Con animación de advertencia
     */
    window.confirmDelete = function(message, callback) {
        Swal.fire({
            title: '¿Estás seguro?',
            html: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74c3c',
            cancelButtonColor: '#95a5a6',
            confirmButtonText: '<i class="fas fa-trash"></i> Sí, eliminar',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
            reverseButtons: true,
            focusCancel: true,
            customClass: {
                popup: 'modern-confirm',
                confirmButton: 'modern-confirm-delete',
                cancelButton: 'modern-confirm-cancel'
            },
            showClass: {
                popup: 'animate__animated animate__shakeX animate__faster'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                if (callback) callback();
            }
        });
    };

    /**
     * Confirmación Genérica - Para acciones que requieren confirmación
     */
    window.confirmAction = function(message, callback) {
        Swal.fire({
            title: '¿Confirmas esta acción?',
            html: message,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3498db',
            cancelButtonColor: '#95a5a6',
            confirmButtonText: '<i class="fas fa-check"></i> Confirmar',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
            reverseButtons: true,
            customClass: {
                popup: 'modern-confirm'
            }
        }).then((result) => {
            if (result.isConfirmed && callback) {
                callback();
            }
        });
    };

    /**
     * Loading/Spinner - Indicador de carga con mensaje
     */
    window.showLoading = function(message = 'Procesando...') {
        Swal.fire({
            title: message,
            html: '<div class="modern-loading"><div class="spinner"></div></div>',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            customClass: {
                popup: 'modern-loading-popup'
            },
            didOpen: () => {
                Swal.showLoading();
            }
        });
    };

    /**
     * Cerrar Loading
     */
    window.hideLoading = function() {
        Swal.close();
    };
    
    // ========================================
    // 🎨 ESTILOS PERSONALIZADOS PARA SWEETALERT2
    // ========================================
    const styleModernAlerts = document.createElement('style');
    styleModernAlerts.textContent = `
        /* Importar Animate.css para animaciones */
        @import url('https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css');
        
        /* Toast moderno */
        .modern-toast.swal2-toast {
            border-radius: 12px !important;
            padding: 16px 20px !important;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3) !important;
            backdrop-filter: blur(10px);
        }
        
        .modern-toast-title {
            font-size: 15px !important;
            font-weight: 600 !important;
            font-family: 'Inter', sans-serif !important;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }
        
        .modern-toast .swal2-icon {
            margin: 0 10px 0 0 !important;
        }
        
        .modern-toast .swal2-timer-progress-bar {
            background: rgba(255, 255, 255, 0.8) !important;
            height: 3px !important;
        }
        
        /* Alert modal moderno */
        .modern-alert {
            border-radius: 20px !important;
            padding: 30px !important;
        }
        
        .modern-alert .swal2-title {
            font-family: 'Inter', sans-serif !important;
            font-weight: 700 !important;
            font-size: 24px !important;
        }
        
        .modern-alert-button {
            border-radius: 8px !important;
            padding: 12px 30px !important;
            font-weight: 600 !important;
            font-family: 'Inter', sans-serif !important;
            transition: all 0.3s ease !important;
        }
        
        .modern-alert-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4) !important;
        }
        
        /* Confirmación moderna */
        .modern-confirm {
            border-radius: 20px !important;
            padding: 30px !important;
        }
        
        .modern-confirm .swal2-title {
            font-family: 'Inter', sans-serif !important;
            font-weight: 700 !important;
        }
        
        .modern-confirm-delete {
            border-radius: 8px !important;
            padding: 12px 25px !important;
            font-weight: 600 !important;
            transition: all 0.3s ease !important;
        }
        
        .modern-confirm-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.4) !important;
        }
        
        .modern-confirm-cancel {
            border-radius: 8px !important;
            padding: 12px 25px !important;
            font-weight: 600 !important;
            transition: all 0.3s ease !important;
        }
        
        .modern-confirm-cancel:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(149, 165, 166, 0.4) !important;
        }
        
        /* Loading moderno */
        .modern-loading-popup {
            border-radius: 20px !important;
            padding: 40px !important;
        }
        
        .modern-loading {
            margin: 20px 0;
        }
        
        .modern-loading .spinner {
            border: 4px solid rgba(52, 152, 219, 0.2);
            border-left-color: #3498db;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(styleModernAlerts);

    // ===== MENÚ DESPLEGABLE DE USUARIO =====
    function toggleUserMenu(event) {
        event.stopPropagation();
        const menu = document.getElementById('userDropdownMenu');
        const icon = document.querySelector('.user-dropdown-icon');
        
        if (menu.classList.contains('show')) {
            menu.classList.remove('show');
            icon.style.transform = 'rotate(0deg)';
        } else {
            menu.classList.add('show');
            icon.style.transform = 'rotate(180deg)';
        }
    }

    // Cerrar menú al hacer click fuera
    document.addEventListener('click', function(event) {
        const menu = document.getElementById('userDropdownMenu');
        const icon = document.querySelector('.user-dropdown-icon');
        if (menu && menu.classList.contains('show')) {
            menu.classList.remove('show');
            if (icon) icon.style.transform = 'rotate(0deg)';
        }
    });

    // ========================================
    // 🎬 INICIALIZACIÓN DE LIBRERÍAS MODERNAS
    // ========================================

    /**
     * 1. INICIALIZAR AOS.js - Animaciones on Scroll
     * Configuración mejorada para animaciones más llamativas
     */
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof AOS !== 'undefined') {
            AOS.init({
                duration: 1000,           // Duración más larga para que sea más visible
                easing: 'ease-out-cubic', // Animación más suave
                once: false,              // Permitir que se repita al hacer scroll
                offset: 50,               // Activar antes para mejor UX
                delay: 0,                 // Sin delay global
                mirror: true,             // Animar al hacer scroll arriba/abajo
                anchorPlacement: 'top-bottom'
            });
            
            
            // Refrescar AOS cada vez que cambie el contenido
            window.refreshAOS = function() {
                AOS.refresh();
            };
            
            // Agregar animación a elementos que se carguen dinámicamente
            const observer = new MutationObserver(function(mutations) {
                AOS.refresh();
            });
            
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
            
        } 
    });

    // 2. Inicializar Chart.js - Todos los Gráficos
    window.addEventListener('load', function() {
        // Gráfico 1: Estado del Inventario
        const stockCtx = document.getElementById('stockChart');
        if (stockCtx) {
            stockChart = new Chart(stockCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Stock Saludable', 'Stock Bajo', 'Sin Stock'],
                    datasets: [{
                        data: [
                            <?php echo $total_productos - $productos_stock_bajo - $productos_sin_stock; ?>,
                            <?php echo $productos_stock_bajo; ?>,
                            <?php echo $productos_sin_stock; ?>
                        ],
                        backgroundColor: [
                            '#2ecc71',
                            '#f39c12',
                            '#e74c3c'
                        ],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 12,
                                font: {
                                    size: 11,
                                    family: 'Inter, sans-serif'
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': ' + context.parsed + ' productos';
                                }
                            }
                        }
                    }
                }
            });
        }

        // Gráfico 2: Productos por Categoría
        const categoryCtx = document.getElementById('categoryChart');
        if (categoryCtx) {
            fetch('app/controllers/DashboardController.php?action=getCategoryStats')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.categories) {
                        const labels = data.categories.map(c => c.nombre_categoria);
                        const counts = data.categories.map(c => c.total_productos);
                        
                        categoryChart = new Chart(categoryCtx, {
                            type: 'bar',
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'Productos',
                                    data: counts,
                                    backgroundColor: '#3498db',
                                    borderColor: '#2980b9',
                                    borderWidth: 1,
                                    borderRadius: 5
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            stepSize: 1,
                                            font: {
                                                size: 10,
                                                family: 'Inter, sans-serif'
                                            }
                                        }
                                    },
                                    x: {
                                        ticks: {
                                            font: {
                                                size: 10,
                                                family: 'Inter, sans-serif'
                                            }
                                        }
                                    }
                                },
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                return 'Productos: ' + context.parsed.y;
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }
                })
                .catch(error => {
                });
        }

        // Gráfico 3: Distribución por Género
        const genderCtx = document.getElementById('genderChart');
        if (genderCtx) {
            fetch('app/controllers/DashboardController.php?action=getGenderDistribution')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.distribution) {
                        const labels = data.distribution.map(d => {
                            const generoMap = {
                                'M': 'Masculino',
                                'F': 'Femenino',
                                'Unisex': 'Unisex',
                                'Kids': 'Niños'
                            };
                            return generoMap[d.genero_producto] || d.genero_producto;
                        });
                        const counts = data.distribution.map(d => d.cantidad);
                        const colors = ['#3b82f6', '#ec4899', '#8b5cf6', '#f59e0b'];
                        
                        genderChart = new Chart(genderCtx, {
                            type: 'pie',
                            data: {
                                labels: labels,
                                datasets: [{
                                    data: counts,
                                    backgroundColor: colors.slice(0, counts.length),
                                    borderWidth: 2,
                                    borderColor: '#fff'
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: {
                                            padding: 12,
                                            font: {
                                                size: 11,
                                                family: 'Inter, sans-serif'
                                            }
                                        }
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                return context.label + ': ' + context.parsed + ' productos';
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }
                })
                .catch(error => {
                });
        }

        // Gráfico 4: Ventas Mensuales
        const salesCtx = document.getElementById('salesChart');
        if (salesCtx) {
            fetch('app/controllers/DashboardController.php?action=getSalesData')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.sales && data.sales.mensuales) {
                        const labels = data.sales.mensuales.map(v => {
                            const [year, month] = v.mes.split('-');
                            return new Date(year, month - 1).toLocaleDateString('es-ES', { month: 'short', year: 'numeric' });
                        });
                        const ventasData = data.sales.mensuales.map(v => parseFloat(v.total_ventas));
                        const pedidosData = data.sales.mensuales.map(v => parseInt(v.total_pedidos));
                        
                        salesChart = new Chart(salesCtx, {
                            type: 'line',
                            data: {
                                labels: labels,
                                datasets: [
                                    {
                                        label: 'Ventas (S/.)',
                                        data: ventasData,
                                        borderColor: '#10b981',
                                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                        borderWidth: 2,
                                        fill: true,
                                        tension: 0.4
                                    },
                                    {
                                        label: 'Pedidos',
                                        data: pedidosData,
                                        borderColor: '#3b82f6',
                                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                        borderWidth: 2,
                                        fill: true,
                                        tension: 0.4,
                                        yAxisID: 'y1'
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                interaction: {
                                    mode: 'index',
                                    intersect: false
                                },
                                scales: {
                                    y: {
                                        type: 'linear',
                                        display: true,
                                        position: 'left',
                                        beginAtZero: true,
                                        ticks: {
                                            font: {
                                                size: 10,
                                                family: 'Inter, sans-serif'
                                            },
                                            callback: function(value) {
                                                return 'S/. ' + value.toFixed(0);
                                            }
                                        }
                                    },
                                    y1: {
                                        type: 'linear',
                                        display: true,
                                        position: 'right',
                                        beginAtZero: true,
                                        grid: {
                                            drawOnChartArea: false
                                        },
                                        ticks: {
                                            font: {
                                                size: 10,
                                                family: 'Inter, sans-serif'
                                            }
                                        }
                                    },
                                    x: {
                                        ticks: {
                                            font: {
                                                size: 10,
                                                family: 'Inter, sans-serif'
                                            }
                                        }
                                    }
                                },
                                plugins: {
                                    legend: {
                                        position: 'top',
                                        labels: {
                                            padding: 15,
                                            font: {
                                                size: 11,
                                                family: 'Inter, sans-serif'
                                            }
                                        }
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                let label = context.dataset.label || '';
                                                if (label) {
                                                    label += ': ';
                                                }
                                                if (context.datasetIndex === 0) {
                                                    label += 'S/. ' + context.parsed.y.toFixed(2);
                                                } else {
                                                    label += context.parsed.y + ' pedidos';
                                                }
                                                return label;
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }
                })
                .catch(error => {
                });
        }

        // Cargar datos adicionales del dashboard
        setTimeout(() => {
            loadDashboardExtras();
        }, 1000);
    });



    // ========================================
    // 🔄 SISTEMA DE ACTUALIZACIÓN EN TIEMPO REAL
    // ========================================
    
    /**
     * Actualizar estadísticas del dashboard en tiempo real
     * Usa Fetch API para obtener datos frescos del servidor
     */
    let dashboardUpdateInterval = null;
    let stockChart = null;
    let categoryChart = null;
    let genderChart = null;
    let salesChart = null;
    
    window.updateDashboardStats = async function() {
        try {
            
            // Mostrar indicador de carga en el badge
            const realtimeIndicator = document.getElementById('realtime-indicator');
            if (realtimeIndicator) {
                realtimeIndicator.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right: 5px;"></i>Actualizando...';
            }
            
            // Obtener estadísticas actualizadas con Fetch API
            const response = await fetch('app/controllers/DashboardController.php?action=getStats', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                // Actualizar tarjetas principales
                updateStatCard('total-productos', data.stats.total_productos, 'productos');
                updateStatCard('total-usuarios', data.stats.total_usuarios, 'usuarios');
                updateStatCard('productos-stock-bajo', data.stats.productos_stock_bajo, 'alertas');
                updateStatCard('total-categorias', data.stats.total_categorias, 'categorias');
                updateStatCard('total-marcas', data.stats.total_marcas, 'marcas');
                
                // Actualizar tarjetas secundarias
                updateStatCard('total-pedidos', data.stats.total_pedidos || 0, 'pedidos');
                updateStatCard('total-resenas', data.stats.total_resenas || 0, 'resenas');
                updateStatCard('total-favoritos', data.stats.total_favoritos || 0, 'favoritos');
                updateStatCard('total-carrito', data.stats.total_carrito || 0, 'carrito');
                updateStatCard('productos-semana', data.stats.productos_semana || 0, 'semana');
                
                // Actualizar ventas del mes
                const ventasMesEl = document.getElementById('ventas-mes');
                if (ventasMesEl) {
                    ventasMesEl.textContent = 'S/. ' + formatNumber(data.stats.ventas_mes || 0);
                }
                
                // Actualizar pedidos pendientes
                const pedidosPendientesEl = document.getElementById('pedidos-pendientes');
                if (pedidosPendientesEl) {
                    pedidosPendientesEl.textContent = (data.stats.pedidos_pendientes || 0) + ' pendientes';
                }
                
                // Actualizar calificación promedio
                const calificacionPromedioEl = document.getElementById('calificacion-promedio');
                if (calificacionPromedioEl) {
                    calificacionPromedioEl.textContent = (data.stats.calificacion_promedio || 0) + ' ★ promedio';
                }
                
                // Actualizar valor de inventario con formato
                const valorInventarioEl = document.getElementById('valor-inventario');
                if (valorInventarioEl) {
                    const nuevoValor = 'S/. ' + formatNumber(data.stats.valor_inventario);
                    if (valorInventarioEl.textContent !== nuevoValor) {
                        valorInventarioEl.style.transform = 'scale(1.1)';
                        valorInventarioEl.style.color = '#2ecc71';
                        setTimeout(() => {
                            valorInventarioEl.textContent = nuevoValor;
                            setTimeout(() => {
                                valorInventarioEl.style.transform = 'scale(1)';
                                valorInventarioEl.style.color = '';
                            }, 150);
                        }, 100);
                    }
                }
                
                // Actualizar gráfico de stock si existe
                if (stockChart && data.stats.stock_distribution) {
                    stockChart.data.datasets[0].data = [
                        data.stats.stock_distribution.saludable,
                        data.stats.stock_distribution.bajo,
                        data.stats.stock_distribution.sin_stock
                    ];
                    stockChart.update('active');
                }
                
                // Actualizar tiempo de última actualización
                const lastUpdateTime = document.getElementById('last-update-time');
                if (lastUpdateTime) {
                    const now = new Date();
                    const timeStr = now.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                    lastUpdateTime.innerHTML = `<i class="fas fa-clock"></i> Última actualización: ${timeStr}`;
                    lastUpdateTime.classList.add('stat-updated');
                    setTimeout(() => lastUpdateTime.classList.remove('stat-updated'), 500);
                }
                
                // Restaurar indicador
                if (realtimeIndicator) {
                    realtimeIndicator.innerHTML = '<i class="fas fa-sync-alt" style="margin-right: 5px;"></i>Actualización automática';
                }
                
                
                // Mostrar notificación sutil solo la primera vez
                if (!window.dashboardFirstUpdate) {
                    showNotification('Dashboard actualizado en tiempo real', 'success');
                    window.dashboardFirstUpdate = true;
                }
            }
            
        } catch (error) {
            
            // Mostrar error en el indicador
            const realtimeIndicator = document.getElementById('realtime-indicator');
            if (realtimeIndicator) {
                realtimeIndicator.innerHTML = '<i class="fas fa-exclamation-triangle" style="margin-right: 5px;"></i>Error de conexión';
                realtimeIndicator.style.background = 'linear-gradient(135deg, #e74c3c 0%, #c0392b 100%)';
                
                setTimeout(() => {
                    realtimeIndicator.innerHTML = '<i class="fas fa-sync-alt" style="margin-right: 5px;"></i>Actualización automática';
                    realtimeIndicator.style.background = 'linear-gradient(135deg, #2ecc71 0%, #27ae60 100%)';
                }, 3000);
            }
        }
    };
    
    /**
     * Cargar datos adicionales: Actividad Reciente, Top Productos, etc.
     */
    window.loadDashboardExtras = async function() {
        try {
            // Cargar actividad reciente
            const activityResponse = await fetch('app/controllers/DashboardController.php?action=getRecentActivity');
            const activityData = await activityResponse.json();
            
            if (activityData.success) {
                renderRecentActivity(activityData.activities);
            }
            
            // Cargar productos más favoritos
            const topProductsResponse = await fetch('app/controllers/DashboardController.php?action=getTopProducts');
            const topProductsData = await topProductsResponse.json();
            
            if (topProductsData.success) {
                renderTopProducts(topProductsData.top_products);
            }
            
            // Cargar distribución por género
            const genderResponse = await fetch('app/controllers/DashboardController.php?action=getGenderDistribution');
            const genderData = await genderResponse.json();
            
            if (genderData.success && genderChart) {
                updateGenderChart(genderData.distribution);
            }
            
            // Cargar ventas mensuales
            const salesResponse = await fetch('app/controllers/DashboardController.php?action=getSalesData');
            const salesData = await salesResponse.json();
            
            if (salesData.success && salesChart) {
                updateSalesChart(salesData.sales.mensuales);
            }
            
        } catch (error) {
        }
    };
    
    /**
     * Renderizar actividad reciente
     */
    function renderRecentActivity(activities) {
        const container = document.getElementById('recent-activity');
        if (!container) return;
        
        let html = '';
        
        // Productos recientes
        if (activities.productos_recientes && activities.productos_recientes.length > 0) {
            activities.productos_recientes.slice(0, 5).forEach(producto => {
                html += `
                    <div class="activity-item">
                        <div class="activity-item-header">
                            <span class="activity-item-title">${producto.nombre_producto}</span>
                            <span class="activity-item-time">${formatDateTime(producto.fecha_registro)}</span>
                        </div>
                        <div class="activity-item-details">
                            <span class="activity-badge info">
                                <i class="fas fa-tag"></i> ${producto.nombre_categoria || 'Sin categoría'}
                            </span>
                            <span class="activity-badge success">
                                <i class="fas fa-boxes"></i> Stock: ${producto.stock_actual_producto || 0}
                            </span>
                        </div>
                    </div>
                `;
            });
        } else {
            html = '<p style="text-align: center; color: #999; padding: 20px;">No hay actividad reciente</p>';
        }
        
        container.innerHTML = html;
    }
    
    /**
     * Renderizar productos más favoritos y mejor calificados
     */
    function renderTopProducts(topProducts) {
        // Más favoritos
        const favoritesContainer = document.getElementById('top-favorites');
        if (favoritesContainer && topProducts.favoritos) {
            let html = '';
            topProducts.favoritos.slice(0, 5).forEach(producto => {
                html += `
                    <div class="activity-item">
                        <div class="activity-item-header">
                            <span class="activity-item-title">${producto.nombre_producto}</span>
                            <span class="activity-badge warning">
                                <i class="fas fa-heart"></i> ${producto.total_favoritos}
                            </span>
                        </div>
                        <div class="activity-item-details">
                            <span style="color: #059669; font-weight: 600;">S/. ${formatNumber(producto.precio_producto)}</span>
                            <span style="color: #666;">${producto.nombre_categoria || 'Sin categoría'}</span>
                        </div>
                    </div>
                `;
            });
            favoritesContainer.innerHTML = html || '<p style="text-align: center; color: #999;">Sin datos</p>';
        }
        
        // Mejor calificados
        const ratedContainer = document.getElementById('top-rated');
        if (ratedContainer && topProducts.calificados) {
            let html = '';
            topProducts.calificados.slice(0, 5).forEach(producto => {
                html += `
                    <div class="activity-item">
                        <div class="activity-item-header">
                            <span class="activity-item-title">${producto.nombre_producto}</span>
                            <span class="activity-badge success">
                                <i class="fas fa-star"></i> ${parseFloat(producto.calificacion_promedio).toFixed(1)}
                            </span>
                        </div>
                        <div class="activity-item-details">
                            <span style="color: #059669; font-weight: 600;">S/. ${formatNumber(producto.precio_producto)}</span>
                            <span style="color: #666;">${producto.total_resenas} reseñas</span>
                        </div>
                    </div>
                `;
            });
            ratedContainer.innerHTML = html || '<p style="text-align: center; color: #999;">Sin datos</p>';
        }
    }
    
    /**
     * Actualizar gráfico de género
     */
    function updateGenderChart(distribution) {
        if (!genderChart || !distribution) return;
        
        const labels = distribution.map(d => d.genero_producto);
        const data = distribution.map(d => d.cantidad);
        
        genderChart.data.labels = labels;
        genderChart.data.datasets[0].data = data;
        genderChart.update();
    }
    
    /**
     * Actualizar gráfico de ventas
     */
    function updateSalesChart(ventas) {
        if (!salesChart || !ventas) return;
        
        const labels = ventas.map(v => {
            const [year, month] = v.mes.split('-');
            return new Date(year, month - 1).toLocaleDateString('es-ES', { month: 'short', year: 'numeric' });
        });
        const data = ventas.map(v => parseFloat(v.total_ventas));
        
        salesChart.data.labels = labels;
        salesChart.data.datasets[0].data = data;
        salesChart.update();
    }
    
    /**
     * Formatear fecha y hora
     */
    function formatDateTime(dateStr) {
        const date = new Date(dateStr);
        const now = new Date();
        const diff = now - date;
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        
        if (days === 0) return 'Hoy';
        if (days === 1) return 'Ayer';
        if (days < 7) return `Hace ${days} días`;
        return date.toLocaleDateString('es-ES', { day: 'numeric', month: 'short' });
    }
    
    /**
     * Actualizar una tarjeta de estadística con animación
     */
    function updateStatCard(elementId, newValue, type) {
        const element = document.getElementById(elementId);
        if (!element) return;
        
        const currentValue = element.textContent.trim();
        const newValueStr = newValue.toString();
        
        // Solo actualizar si el valor cambió
        if (currentValue !== newValueStr) {
            // Animación de cambio
            element.style.transform = 'scale(1.1)';
            element.style.color = '#2ecc71';
            
            setTimeout(() => {
                element.textContent = newValueStr;
                
                setTimeout(() => {
                    element.style.transform = 'scale(1)';
                    element.style.color = '';
                }, 150);
            }, 100);
        }
    }
    
    /**
     * Formatear números con separadores de miles
     */
    function formatNumber(num) {
        return new Intl.NumberFormat('es-PE').format(num);
    }
    
    /**
     * Iniciar actualización automática del dashboard
     * Se actualiza cada 30 segundos
     */
    window.startDashboardAutoUpdate = function(intervalSeconds = 30) {
        
        // Limpiar intervalo existente
        if (dashboardUpdateInterval) {
            clearInterval(dashboardUpdateInterval);
        }
        
        // Configurar nuevo intervalo
        dashboardUpdateInterval = setInterval(() => {
            const currentTab = document.querySelector('.tab-content.active');
            
            // Solo actualizar si estamos en la pestaña del dashboard
            if (currentTab && currentTab.id === 'dashboard') {
                updateDashboardStats();
            }
        }, intervalSeconds * 1000);
        
    };
    
    /**
     * Detener actualización automática
     */
    window.stopDashboardAutoUpdate = function() {
        if (dashboardUpdateInterval) {
            clearInterval(dashboardUpdateInterval);
            dashboardUpdateInterval = null;
        }
    };
    
    // Iniciar actualización automática solo si estamos en el dashboard
    window.addEventListener('load', function() {
        const currentTab = document.querySelector('.tab-content.active');
        if (currentTab && currentTab.id === 'dashboard') {
            // Primera actualización después de 5 segundos
            setTimeout(() => {
                updateDashboardStats();
            }, 5000);
            
            // Iniciar actualización automática cada 30 segundos
            startDashboardAutoUpdate(30);
        }
    });
    
    // Actualizar cuando se cambia a la pestaña dashboard
    window.addEventListener('tabchange', function(e) {
        if (e.detail && e.detail.tabId === 'dashboard') {
            updateDashboardStats();
            startDashboardAutoUpdate(30);
        } else {
            stopDashboardAutoUpdate();
        }
    });

    // 5. Ejemplo de uso de SweetAlert2 para reemplazar alert()
    window.alert = function(message) {
        Swal.fire({
            title: 'Aviso',
            text: message,
            icon: 'info',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Entendido'
        });
    };

    </script>

    <!-- Chatbot Widget - DESHABILITADO EN ADMIN -->
    <?php // include 'includes/chatbot-widget.php'; // No mostrar chatbot en panel de administración ?>
</body>
</html>