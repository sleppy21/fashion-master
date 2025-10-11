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
    echo '<script>alert("No hay sesión iniciada. Redirigiendo al login..."); window.location.href="login.php";</script>';
    exit;
}

if ($_SESSION['rol'] !== 'admin') {
    echo '<script>alert("No tienes permisos de administrador. Rol actual: ' . ($_SESSION['rol'] ?? 'No definido') . '"); window.location.href="index.php";</script>';
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
    <link href="public/assets/css/view-modal-animations.css" rel="stylesheet"> <!-- DEBE SER EL ÚLTIMO -->
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- SheetJS para exportar a Excel -->
    <script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>
    
    <!-- Configuración global de rutas -->
    <script src="public/assets/js/config.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Sistema de actualización suave de tabla -->
    <script src="public/assets/js/smooth-table-update.js"></script>
    
    <!-- Sistema de modales de productos -->
    <script src="public/assets/js/product-modals.js"></script>
    
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
</head>
<body>
    <!-- Script de inicialización temprana - DEBE estar antes de cualquier onclick -->
    <script>
        // Declaración temprana de switchTab para evitar errores en onclick
        // La función completa se define más abajo
        window.switchTab = function(tabId) {
            console.log('⏳ switchTab llamado tempranamente para:', tabId);
            // Guardar la petición para ejecutarla cuando esté lista
            if (!window.switchTabReady) {
                window.pendingSwitchTab = tabId;
                console.log('📌 Tab guardado como pendiente:', tabId);
            }
        };
        window.switchTabReady = false;
    </script>
    
    <!-- ⭐ CONTENEDOR DE NOTIFICACIONES -->
    <!-- ⭐ CONTENEDOR DE NOTIFICACIONES (Abajo a la derecha como shop.php) -->
    <div id="notification-container" style="position: fixed; bottom: 20px; right: 20px; z-index: 99999; display: flex; flex-direction: column-reverse; gap: 10px;"></div>
    
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
                
                <!-- Logo centrado -->
                <div class="admin-logo">
                    <div class="logo-icon">
                        <i class="fas fa-crown"></i>
                    </div>
                </div>
                
                <div class="admin-user-info">
                    <div class="user-profile">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($usuario, 0, 2)); ?>
                        </div>
                        <div class="user-details">
                            <h3><?php echo htmlspecialchars($usuario); ?></h3>
                            <span><?php echo ucfirst($rol); ?></span>
                        </div>
                    </div>
                    <a href="logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        Salir
                    </a>
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
                    <div class="dashboard-header">
                        <h1>
                            <i class="fas fa-tachometer-alt"></i>
                            Dashboard Principal
                        </h1>
                        <p>Resumen general del sistema de Fashion Store</p>
                    </div>

                    <!-- Tarjetas de estadísticas -->
                    <div class="stats-grid">
                        <div class="stat-card products">
                            <div class="stat-icon">
                                <i class="fas fa-tshirt"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?php echo $total_productos; ?></h3>
                                <p>Productos Activos</p>
                                <div class="stat-trend <?php echo $productos_stock_bajo > 0 ? 'warning' : 'success'; ?>">
                                    <?php if ($productos_stock_bajo > 0): ?>
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <span><?php echo $productos_stock_bajo; ?> con stock bajo</span>
                                    <?php else: ?>
                                        <i class="fas fa-check-circle"></i>
                                        <span>Stock saludable</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="stat-card users">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?php echo $total_usuarios; ?></h3>
                                <p>Usuarios Activos</p>
                                <div class="stat-trend info">
                                    <i class="fas fa-user-check"></i>
                                    <span>Registrados en el sistema</span>
                                </div>
                            </div>
                        </div>

                        <div class="stat-card sales">
                            <div class="stat-icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div class="stat-content">
                                <h3>S/. <?php echo number_format($valor_inventario, 0, ',', '.'); ?></h3>
                                <p>Valor de Inventario</p>
                                <div class="stat-trend success">
                                    <i class="fas fa-chart-line"></i>
                                    <span><?php echo $total_productos_sistema; ?> productos totales</span>
                                </div>
                            </div>
                        </div>

                        <div class="stat-card orders">
                            <div class="stat-icon">
                                <i class="fas fa-tags"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?php echo $total_categorias; ?></h3>
                                <p>Categorías Activas</p>
                                <div class="stat-trend info">
                                    <i class="fas fa-copyright"></i>
                                    <span><?php echo $total_marcas; ?> marcas activas</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Acciones rápidas -->
                    <div class="quick-actions">
                        <h2>
                            <i class="fas fa-bolt"></i>
                            Acciones Rápidas
                        </h2>
                        <div class="actions-grid">
                            <button class="action-btn add-product" onclick="showCreateProductModal()">
                                <i class="fas fa-plus-circle"></i>
                                <span>Agregar Producto</span>
                            </button>
                            <button class="action-btn add-category" onclick="openNewCategoryModal()">
                                <i class="fas fa-tag"></i>
                                <span>Nueva Categoría</span>
                            </button>
                            <button class="action-btn manage-users" onclick="switchTab('usuarios')">
                                <i class="fas fa-user-plus"></i>
                                <span>Gestionar Usuarios</span>
                            </button>
                            <button class="action-btn settings" onclick="switchTab('configuracion')">
                                <i class="fas fa-cogs"></i>
                                <span>Configuración</span>
                            </button>
                        </div>
                    </div>
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
            console.log('🧹 Limpiando módulo actual...');
            
            // ✅ HABILITADO: Destruir módulos automáticamente para empezar desde cero
            // Cada módulo debe tener funciones con nombres únicos para evitar conflictos
            
            // 1. Llamar a las funciones de destrucción específicas de cada módulo
            try {
                if (typeof window.destroyProductosModule === 'function') {
                    console.log('  → Limpiando módulo Productos');
                    window.destroyProductosModule();
                }
            } catch (e) {
                console.warn('⚠️ Error al destruir Productos:', e);
            }
            
            try {
                if (typeof window.destroyCategoriasModule === 'function') {
                    console.log('  → Limpiando módulo Categorías');
                    window.destroyCategoriasModule();
                }
            } catch (e) {
                console.warn('⚠️ Error al destruir Categorías:', e);
            }
            
            try {
                if (typeof window.destroyMarcasModule === 'function') {
                    console.log('  → Limpiando módulo Marcas');
                    window.destroyMarcasModule();
                }
            } catch (e) {
                console.warn('⚠️ Error al destruir Marcas:', e);
            }
            
            try {
                if (typeof window.destroyUsuariosModule === 'function') {
                    console.log('  → Limpiando módulo Usuarios');
                    window.destroyUsuariosModule();
                }
            } catch (e) {
                console.warn('⚠️ Error al destruir Usuarios:', e);
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
                console.warn('⚠️ Error al limpiar modales:', e);
            }
            
            // 3. Limpiar event listeners de búsqueda global
            const searchInputs = document.querySelectorAll('[id*="search-"]');
            searchInputs.forEach(input => {
                const newInput = input.cloneNode(true);
                input.parentNode.replaceChild(newInput, input);
            });
            
            console.log('✅ Destrucción de módulo completada');
        };
        
        // Hacer switchTab disponible globalmente (REDEFINICIÓN COMPLETA)
        window.switchTab = function(tabId) {
            console.log('🔄 Cambiando a tab:', tabId);
            
            // 1. DESTRUIR MÓDULO ANTERIOR ANTES DE CAMBIAR
            try {
                if (typeof window.destroyCurrentModule === 'function') {
                    window.destroyCurrentModule();
                }
            } catch (e) {
                console.warn('⚠️ Error durante destrucción:', e);
            }
            
            // 2. Cerrar cualquier modal abierto antes de cambiar de sección
            try {
                if (typeof forceCloseModal === 'function') {
                    forceCloseModal();
                }
            } catch (e) {
                console.warn('⚠️ Error al cerrar modal:', e);
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
                    console.log('💾 Tab guardado en localStorage:', tabId);
                } catch (e) {
                    console.warn('⚠️ No se pudo guardar en localStorage:', e);
                }
                
                // 6. Cargar contenido específico del tab INMEDIATAMENTE
                // La destrucción ya es síncrona, no necesitamos delay
                if (typeof loadTabContent === 'function') {
                    loadTabContent(tabId);
                }
                
                console.log('✅ Tab cambiado exitosamente a:', tabId);
            } else {
                console.error('❌ No se encontró tab o pane para:', tabId);
            }
        };
        
        // Marcar que switchTab está completamente listo
        window.switchTabReady = true;
        
        // Ejecutar tab pendiente si existe
        if (window.pendingSwitchTab) {
            console.log('🎯 Ejecutando tab pendiente:', window.pendingSwitchTab);
            const pendingTab = window.pendingSwitchTab;
            delete window.pendingSwitchTab;
            // Ejecutar en el próximo tick para asegurar que todo esté listo
            setTimeout(() => window.switchTab(pendingTab), 0);
        }
        
        console.log('✅ switchTab completamente cargado y listo');
        
        // Función para resetear el tab guardado (útil para debugging)
        window.resetActiveTab = function() {
            try {
                localStorage.removeItem('admin_active_tab');
                console.log('🗑️ Tab guardado eliminado. Refresca la página para volver a Dashboard.');
            } catch (e) {
                console.error('❌ Error al eliminar tab guardado:', e);
            }
        };
        
        // Función de prueba para verificar que todo funciona
        window.testNavigation = function() {
            console.log('🧪 Probando navegación...');
            switchTab('productos');
        };
        
        // Función para probar directamente
        window.testProductos = function() {
            console.log('🧪 Probando productos directamente...');
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
                console.log('🚀 showModalOverlayCreate iniciado');
                document.body.classList.add('modal-open');
                
                let overlay = document.getElementById('product-modal-overlay');
                if (overlay) {
                    console.log('🗑️ Eliminando overlay existente');
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
                        console.log('🖱️ Clic fuera del modal detectado, cerrando...');
                        closeProductModal();
                    }
                });
                
                document.body.appendChild(overlay);
                console.log('✅ Overlay agregado al DOM');
                
                // Activar overlay con delay para animación - USAR .show
                requestAnimationFrame(() => {
                    overlay.classList.add('show');
                    console.log('✅ Clase show agregada al overlay');
                });
                
                const fetchUrl = 'app/views/admin/product_modal.php?action=create';
                console.log('🆕 URL para CREAR:', fetchUrl);
                
                fetch(fetchUrl)
                .then(response => {
                    console.log('📡 Respuesta recibida:', response.status);
                    if (!response.ok) {
                        throw new Error(`Error HTTP: ${response.status}`);
                    }
                    return response.text();
                })
                .then(html => {
                    console.log('📄 HTML recibido, longitud:', html.length);
                    // CAMBIO: Usar outerHTML para reemplazar completamente el wrapper temporal
                    const wrapper = overlay.querySelector('#modal-content-wrapper');
                    if (wrapper) {
                        wrapper.outerHTML = html;
                        console.log('✅ Modal content reemplazado completamente (sin duplicados)');
                        
                        const scripts = overlay.querySelectorAll('script');
                        console.log('📜 Scripts encontrados:', scripts.length);
                        scripts.forEach((script, index) => {
                            if (script.textContent && script.textContent.trim()) {
                                try {
                                    eval(script.textContent);
                                } catch (scriptError) {
                                    console.error(`❌ Error en script ${index + 1}:`, scriptError);
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
                    console.error('❌ Error cargando modal crear:', error);
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
                console.error('❌ Error general en showModalOverlayCreate:', mainError);
                
                document.body.classList.remove('modal-open');
                const existingOverlay = document.getElementById('product-modal-overlay');
                if (existingOverlay) {
                    existingOverlay.remove();
                }
            }
        }

        function showModalOverlayEdit(productId) {
            try {
                console.log('✏️ showModalOverlayEdit iniciado con ID:', productId);
                document.body.classList.add('modal-open');
                
                let overlay = document.getElementById('product-modal-overlay');
                if (overlay) {
                    console.log('🗑️ Eliminando overlay existente');
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
                        console.log('🖱️ Clic fuera del modal detectado, cerrando...');
                        closeProductModal();
                    }
                });
                
                document.body.appendChild(overlay);
                console.log('✅ Overlay agregado al DOM');
                
                // Activar overlay con delay para animación - USAR .show
                requestAnimationFrame(() => {
                    overlay.classList.add('show');
                    console.log('✅ Clase show agregada al overlay');
                });
                
                const fetchUrl = `app/views/admin/product_modal.php?action=edit&id=${productId}`;
                console.log('✏️ URL para EDITAR:', fetchUrl);
                
                fetch(fetchUrl)
                .then(response => {
                    console.log('📡 Respuesta recibida:', response.status);
                    if (!response.ok) {
                        throw new Error(`Error HTTP: ${response.status}`);
                    }
                    return response.text();
                })
                .then(html => {
                    console.log('📄 HTML recibido, longitud:', html.length);
                    // CAMBIO: Usar outerHTML para reemplazar completamente el wrapper temporal
                    const wrapper = overlay.querySelector('#modal-content-wrapper');
                    if (wrapper) {
                        wrapper.outerHTML = html;
                        console.log('✅ Modal content reemplazado completamente (sin duplicados)');
                        
                        const scripts = overlay.querySelectorAll('script');
                        scripts.forEach((script, index) => {
                            if (script.textContent && script.textContent.trim()) {
                                try {
                                    eval(script.textContent);
                                } catch (scriptError) {
                                    console.error(`❌ Error en script ${index + 1}:`, scriptError);
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
                    console.error('❌ Error cargando modal editar:', error);
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
                console.error('❌ Error general en showModalOverlayEdit:', mainError);
                
                document.body.classList.remove('modal-open');
                const existingOverlay = document.getElementById('product-modal-overlay');
                if (existingOverlay) {
                    existingOverlay.remove();
                }
            }
        }

        function showModalOverlayView(productId) {
            try {
                console.log('👁️ showModalOverlayView iniciado con ID:', productId);
                document.body.classList.add('modal-open');
                
                // Verificar y cargar CSS del modal Ver Producto si no está cargado
                if (!document.querySelector('link[href*="product-view-modal.css"]')) {
                    console.log('📎 Cargando CSS del modal Ver Producto...');
                    const cssLink = document.createElement('link');
                    cssLink.rel = 'stylesheet';
                    cssLink.href = 'public/assets/css/product-view-modal.css';
                    document.head.appendChild(cssLink);
                }
                
                let existingModal = document.querySelector('.product-view-modal');
                if (existingModal) {
                    console.log('🗑️ Eliminando modal existente');
                    existingModal.remove();
                }
                
                // CREAR CONTENEDOR TEMPORAL para cargar el modal
                const tempContainer = document.createElement('div');
                tempContainer.style.display = 'none';
                document.body.appendChild(tempContainer);
                
                const fetchUrl = `app/views/admin/product_modal.php?action=view&id=${productId}`;
                console.log('👁️ URL para VER:', fetchUrl);
                
                fetch(fetchUrl)
                .then(response => {
                    console.log('📡 Respuesta recibida:', response.status);
                    if (!response.ok) {
                        throw new Error(`Error HTTP: ${response.status}`);
                    }
                    return response.text();
                })
                .then(html => {
                    console.log('📄 HTML recibido, longitud:', html.length);
                    
                    // Insertar HTML en contenedor temporal
                    tempContainer.innerHTML = html;
                    
                    // Buscar el modal Ver Producto en el HTML cargado
                    const productModal = tempContainer.querySelector('.product-view-modal');
                    if (productModal) {
                        console.log('✅ Modal Ver Producto encontrado en HTML');
                        
                        // Agregar el modal directamente al body
                        document.body.appendChild(productModal);
                        console.log('✅ Modal Ver Producto agregado al DOM');
                        
                        // Debug: verificar estilos aplicados
                        const computedStyles = window.getComputedStyle(productModal);
                        console.log('🔍 Estilos iniciales del modal:', {
                            display: computedStyles.display,
                            opacity: computedStyles.opacity,
                            visibility: computedStyles.visibility,
                            position: computedStyles.position,
                            zIndex: computedStyles.zIndex
                        });
                        
                        // Activar modal con animación
                        requestAnimationFrame(() => {
                            productModal.classList.add('show');
                            console.log('✅ Clase show agregada al modal Ver Producto');
                            
                            // Debug: verificar estilos después de agregar .show
                            setTimeout(() => {
                                const computedStylesAfter = window.getComputedStyle(productModal);
                                console.log('🔍 Estilos después de .show:', {
                                    display: computedStylesAfter.display,
                                    opacity: computedStylesAfter.opacity,
                                    visibility: computedStylesAfter.visibility,
                                    classes: productModal.className
                                });
                                
                                // Llamar función de debug del modal si existe
                                if (typeof window.debugModal === 'function') {
                                    window.debugModal();
                                }
                                
                                // Debug adicional: verificar elementos por encima del modal
                                const elementsAtCenter = document.elementsFromPoint(window.innerWidth/2, window.innerHeight/2);
                                console.log('🔍 Elementos en centro de pantalla:', elementsAtCenter.map(el => ({
                                    tag: el.tagName,
                                    class: el.className,
                                    id: el.id,
                                    zIndex: window.getComputedStyle(el).zIndex
                                })));
                                
                                // Verificar si el modal está realmente visible
                                const modalRect = productModal.getBoundingClientRect();
                                console.log('🔍 Posición del modal:', {
                                    top: modalRect.top,
                                    left: modalRect.left,
                                    width: modalRect.width,
                                    height: modalRect.height,
                                    visible: modalRect.width > 0 && modalRect.height > 0
                                });
                                
                                console.log('✅ Modal configurado correctamente, usando estilos CSS');
                            }, 50);
                        });
                    } else {
                        console.error('❌ No se encontró .product-view-modal en el HTML');
                        console.log('🔍 Contenido del tempContainer:', tempContainer.innerHTML.substring(0, 500));
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
                                console.error(`❌ Error en script ${index + 1}:`, scriptError);
                            }
                        }
                    });
                })
                .catch(error => {
                    console.error('❌ Error cargando modal ver:', error);
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
                console.error('❌ Error general en showModalOverlayView:', mainError);
                
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
        
        // ===== FUNCIÓN PARA NUEVA CATEGORÍA =====
        window.openNewCategoryModal = function() {
            console.log('🏷️ Abriendo modal de nueva categoría directamente');
            
            // Si ya estamos en la tab de categorías, abrir directo
            const categoriaTab = document.getElementById('categorias');
            const isInCategoriaTab = categoriaTab && categoriaTab.classList.contains('active');
            
            if (isInCategoriaTab && typeof window.showCreateCategoriaModal === 'function') {
                console.log('✅ Ya en tab de categorías, abriendo modal directo');
                window.showCreateCategoriaModal();
                return;
            }
            
            // Si no estamos en la tab, cambiar primero
            console.log('📍 Cambiando a tab de categorías...');
            switchTab('categorias');
            
            // Esperar a que el contenido de categorías se cargue y abrir modal
            let attempts = 0;
            const maxAttempts = 20; // 2 segundos máximo
            
            const checkAndOpen = setInterval(function() {
                attempts++;
                
                if (typeof window.showCreateCategoriaModal === 'function') {
                    console.log('✅ Función encontrada, abriendo modal');
                    clearInterval(checkAndOpen);
                    window.showCreateCategoriaModal();
                } else if (attempts >= maxAttempts) {
                    console.error('❌ Timeout: no se pudo encontrar la función del modal');
                    clearInterval(checkAndOpen);
                    showNotification('Error al abrir el modal de categoría. Por favor intente de nuevo.', 'error');
                }
            }, 100); // Revisar cada 100ms
        };
        
        console.log('✅ Funciones de modal expuestas globalmente:', {
            showCreateProductModal: typeof window.showCreateProductModal,
            showEditProductModal: typeof window.showEditProductModal,
            showViewProductModal: typeof window.showViewProductModal,
            editProduct: typeof window.editProduct,
            viewProduct: typeof window.viewProduct,
            closeProductModal: typeof window.closeProductModal
        });
        
        // ===== FIN FUNCIONES DEL MODAL =====
        
        // ===== SISTEMA DE CARGA ÚNICO POR SECCIÓN =====
        // Cada sección tiene su propia función de carga completamente independiente
        // Limpiar elementos de filtros/modales residuales entre cambios de sección
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
                console.warn('⚠️ cleanupFilters error:', e);
            }
        };

        function loadTabContent(tabId) {
            console.log('🔄 loadTabContent llamado para:', tabId);
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
                    console.log('📊 Tab por defecto:', tabId);
            }
        }
        
        // ===== FUNCIÓN ÚNICA PARA PRODUCTOS =====
        function loadProductosSection() {
            console.log('📦 [PRODUCTOS] Iniciando carga de sección...');
            
            const containerId = 'productos-content';
            const targetContainer = document.getElementById(containerId);
            
            if (!targetContainer) {
                console.error('❌ [PRODUCTOS] Contenedor no encontrado');
                return;
            }
            
            // Limpiar completamente el contenedor
            targetContainer.innerHTML = '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i> Cargando productos...</div>';
            
            console.log('📂 [PRODUCTOS] Iniciando fetch...');
            fetch('app/views/admin/admin_productos.php?_=' + Date.now()) // Cache busting
                .then(response => {
                    console.log('📡 [PRODUCTOS] Respuesta recibida:', response.status);
                    if (!response.ok) {
                        throw new Error(`Error HTTP: ${response.status}`);
                    }
                    return response.text();
                })
                .then(html => {
                    console.log('📄 [PRODUCTOS] HTML recibido, longitud:', html.length);
                    
                    // RESETEAR completamente el contenedor
                    targetContainer.innerHTML = '';
                    targetContainer.innerHTML = html;
                    
                    console.log('✅ [PRODUCTOS] HTML insertado');
                    
                    // Ejecutar scripts de forma segura (evitar redeclaración de variables)
                    const scripts = targetContainer.querySelectorAll('script');
                    console.log('🔧 [PRODUCTOS] Scripts encontrados:', scripts.length);
                    
                    scripts.forEach((script, index) => {
                        try {
                            if (script.textContent && script.textContent.trim()) {
                                // Ejecutar con eval en lugar de appendChild para evitar conflictos
                                // de redeclaración de variables globales (let/const)
                                eval(script.textContent);
                                console.log(`✅ [PRODUCTOS] Script ${index} ejecutado`);
                            }
                        } catch (error) {
                            console.error(`❌ [PRODUCTOS] Error ejecutando script ${index}:`, error);
                        }
                    });
                    
                    // NOTA: initializeProductsModule() se auto-ejecuta dentro del script evaluado
                    // No es necesario llamarlo aquí
                    
                    console.log('✅ [PRODUCTOS] Sección cargada completamente');
                })
                .catch(error => {
                    console.error('❌ [PRODUCTOS] Error en carga:', error);
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
        
        // ===== FUNCIÓN ÚNICA PARA CATEGORÍAS =====
        function loadCategoriasSection() {
            console.log('🏷️ [CATEGORIAS] Iniciando carga de sección...');
            
            const containerId = 'categorias-content';
            const targetContainer = document.getElementById(containerId);
            
            if (!targetContainer) {
                console.error('❌ [CATEGORIAS] Contenedor no encontrado');
                return;
            }
            
            // Limpiar completamente el contenedor
            targetContainer.innerHTML = '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i> Cargando categorías...</div>';
            
            console.log('📂 [CATEGORIAS] Iniciando fetch...');
            fetch('app/views/admin/admin_categorias.php?_=' + Date.now())
                .then(response => {
                    console.log('📡 [CATEGORIAS] Respuesta recibida:', response.status);
                    if (!response.ok) {
                        throw new Error(`Error HTTP: ${response.status}`);
                    }
                    return response.text();
                })
                .then(html => {
                    console.log('📄 [CATEGORIAS] HTML recibido, longitud:', html.length);
                    
                    // RESETEAR completamente el contenedor
                    targetContainer.innerHTML = '';
                    targetContainer.innerHTML = html;
                    
                    console.log('✅ [CATEGORIAS] HTML insertado');
                    
                    // Ejecutar scripts de forma segura (evitar redeclaración)
                    const scripts = targetContainer.querySelectorAll('script');
                    console.log('🔧 [CATEGORIAS] Scripts encontrados:', scripts.length);
                    
                    scripts.forEach((script, index) => {
                        try {
                            if (script.textContent && script.textContent.trim()) {
                                eval(script.textContent);
                                console.log(`✅ [CATEGORIAS] Script ${index} ejecutado`);
                            }
                        } catch (error) {
                            console.error(`❌ [CATEGORIAS] Error ejecutando script ${index}:`, error);
                        }
                    });
                    
                    // NOTA: initializecategoriasModule() se auto-ejecuta dentro del script evaluado
                    // No es necesario llamarlo aquí
                    
                    console.log('✅ [CATEGORIAS] Sección cargada completamente');
                })
                .catch(error => {
                    console.error('❌ [CATEGORIAS] Error en carga:', error);
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
            console.log('©️ [MARCAS] Iniciando carga de sección...');
            
            const containerId = 'marcas-content';
            const targetContainer = document.getElementById(containerId);
            
            if (!targetContainer) {
                console.error('❌ [MARCAS] Contenedor no encontrado');
                return;
            }
            
            // Limpiar completamente el contenedor
            targetContainer.innerHTML = '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i> Cargando marcas...</div>';
            
            console.log('📂 [MARCAS] Iniciando fetch...');
            fetch('app/views/admin/admin_marca.php?_=' + Date.now())
                .then(response => {
                    console.log('📡 [MARCAS] Respuesta recibida:', response.status);
                    if (!response.ok) {
                        throw new Error(`Error HTTP: ${response.status}`);
                    }
                    return response.text();
                })
                .then(html => {
                    console.log('📄 [MARCAS] HTML recibido, longitud:', html.length);
                    
                    // RESETEAR completamente el contenedor
                    targetContainer.innerHTML = '';
                    targetContainer.innerHTML = html;
                    
                    console.log('✅ [MARCAS] HTML insertado');
                    
                    // Ejecutar scripts de forma segura (evitar redeclaración)
                    const scripts = targetContainer.querySelectorAll('script');
                    console.log('🔧 [MARCAS] Scripts encontrados:', scripts.length);
                    
                    scripts.forEach((script, index) => {
                        try {
                            if (script.textContent && script.textContent.trim()) {
                                eval(script.textContent);
                                console.log(`✅ [MARCAS] Script ${index} ejecutado`);
                            }
                        } catch (error) {
                            console.error(`❌ [MARCAS] Error ejecutando script ${index}:`, error);
                        }
                    });
                    
                    // NOTA: initializeMarcasModule() se auto-ejecuta dentro del script evaluado
                    // No es necesario llamarlo aquí
                    
                    console.log('✅ [MARCAS] Sección cargada completamente');
                })
                .catch(error => {
                    console.error('❌ [MARCAS] Error en carga:', error);
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
            console.log('👥 [USUARIOS] Iniciando carga de sección...');
            
            const containerId = 'usuarios-content';
            const targetContainer = document.getElementById(containerId);
            
            if (!targetContainer) {
                console.error('❌ [USUARIOS] Contenedor no encontrado');
                return;
            }
            
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
                                console.error('[USUARIOS] Error script:', e);
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
            console.log('Removiendo preview (ADMIN BACKUP)');
            
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
                console.log('👁️ Imagen original restaurada');
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
            console.log('Confirmando nueva imagen (ADMIN BACKUP)');
            
            const preview = document.getElementById('imagePreview');
            const container = document.querySelector('.file-upload-container');
            const currentImageDisplay = document.querySelector('.current-image-display');
            
            if (preview) {
                preview.style.display = 'none';
                preview.classList.remove('replacing-current');
            }
            
            // La imagen original permanece oculta hasta que se guarde
            if (currentImageDisplay) {
                console.log('📝 Imagen original permanece oculta hasta guardar');
            }
            
            if (container) {
                container.classList.add('confirmed');
            }
            
            // Mostrar notificación simple
            console.log('Nueva imagen confirmada. Se actualizará al guardar.');
            
            // Podrías mostrar un indicador visual de que hay una imagen pendiente
            const imageSection = document.querySelector('[data-section="imagen"]');
            if (imageSection) {
                imageSection.classList.add('has-pending-image');
            }
        }

        // ===== FIN FUNCIONES BACKUP =====
        console.log('✅ Funciones backup del modal cargadas');

        // FUNCIÓN DE TEST GLOBAL
        window.testFileInput = function() {
            console.log('🧪 === TEST FILE INPUT (GLOBAL) ===');
            const input = document.getElementById('imagen');
            console.log('Input element:', input);
            
            if (input) {
                console.log('✅ Input encontrado');
                
                // REMOVER TODOS LOS EVENT LISTENERS EXISTENTES
                const newInput = input.cloneNode(true);
                input.parentNode.replaceChild(newInput, input);
                console.log('🔄 Input clonado para eliminar event listeners');
                
                // Agregar event listener súper simple
                newInput.addEventListener('change', function(e) {
                    console.log('🎉 ¡¡¡EVENT CHANGE FUNCIONANDO!!!');
                    console.log('📁 Files:', e.target.files);
                    
                    if (e.target.files && e.target.files[0]) {
                        const file = e.target.files[0];
                        console.log('📄 Archivo seleccionado:', {
                            name: file.name,
                            type: file.type,
                            size: file.size
                        });
                        
                        // Test simple de preview
                        const preview = document.getElementById('imagePreview');
                        const previewImg = document.getElementById('previewImg');
                        
                        if (preview && previewImg) {
                            console.log('🖼️ Elementos de preview encontrados, mostrando...');
                            const url = URL.createObjectURL(file);
                            previewImg.src = url;
                            preview.style.display = 'block';
                            console.log('✅ Preview mostrado!');
                        } else {
                            console.log('❌ No se encontraron elementos de preview');
                        }
                    }
                });
                
                console.log('🖱️ Forzando click...');
                newInput.click();
                
            } else {
                console.error('❌ Input no encontrado');
            }
        };

        function openProductModal(action, productId = null) {
            console.log('🔄 openProductModal llamada:', action, productId);
            
            // Pasar los parámetros a showModalOverlay
            try {
                showModalOverlay(action, productId);
            } catch (error) {
                console.error('❌ Error en openProductModal:', error);
            }
        }

        function showModalOverlay(action = 'create', productId = null) {
            console.log('🎯 showModalOverlay() ejecutada - iniciando carga del modal:', action, productId);
            
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
                    console.log('🖱️ Click en overlay detectado');
                    closeProductModal();
                }
            }, { once: true }); // once:true previene ejecuciones múltiples
            
            // Mostrar overlay con animación DESPUÉS de configurar eventos
            setTimeout(() => {
                overlay.classList.add('show');
                console.log('✨ Clase "show" agregada al overlay:', overlay.className);
            }, 10);
            
            // Construir URL con parámetros según la acción
            let modalUrl = 'app/views/admin/product_modal.php';
            if (action === 'edit' && productId) {
                modalUrl += `?edit=1&id=${productId}`;
            } else if (action === 'view' && productId) {
                modalUrl += `?view=1&id=${productId}`;
            }
            
            console.log('🌐 Iniciando fetch del modal PHP con URL:', modalUrl);
            fetch(modalUrl)
                .then(response => {
                    console.log('📡 Respuesta recibida:', response.status, response.statusText);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.text();
                })
                .then(html => {
                    console.log('📄 HTML recibido, longitud:', html.length);
                    
                    // Reemplazar el contenido de carga con el modal real
                    const modalContent = overlay.querySelector('#modal-content');
                    if (modalContent) {
                        modalContent.outerHTML = html;
                        console.log('✅ Contenido del modal reemplazado');
                        
                        // Configurar el sistema de archivos después de cargar
                        setTimeout(() => {
                            console.log('⚙️ Configurando sistema de archivos...');
                            
                            // Ejecutar todos los scripts del modal cargado
                            const scripts = overlay.querySelectorAll('script');
                            scripts.forEach(script => {
                                if (script.innerHTML.trim()) {
                                    try {
                                        // Ejecutar el script en el contexto global
                                        eval(script.innerHTML);
                                        console.log('✅ Script del modal ejecutado');
                                    } catch (error) {
                                        console.error('❌ Error ejecutando script:', error);
                                    }
                                }
                            });
                            
                            // Intentar ejecutar setupFileUpload si está disponible
                            if (typeof setupFileUpload === 'function') {
                                setupFileUpload();
                                console.log('✅ setupFileUpload ejecutado');
                            } else {
                                console.warn('⚠️ setupFileUpload no está disponible, pero los scripts ya se ejecutaron');
                            }
                        }, 200);
                    }
                    
                    console.log('🎉 Modal cargado exitosamente');
                })
                .catch(error => {
                    console.error('❌ Error cargando modal:', error);
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
            console.log('🚪 closeProductModal() PRINCIPAL iniciado');
            
            // Manejar modal Ver Producto (principal)
            const viewModal = document.querySelector('.product-view-modal');
            if (viewModal) {
                console.log('✅ Modal Ver Producto encontrado, cerrando con animación...');
                
                // Quitar clase show y agregar clase closing
                viewModal.classList.remove('show');
                viewModal.classList.add('closing');
                
                // Esperar a que termine la animación antes de eliminar
                setTimeout(() => {
                    if (viewModal.parentNode) {
                        viewModal.remove();
                        console.log('✅ Modal Ver Producto eliminado del DOM');
                    }
                    document.body.classList.remove('modal-open');
                }, 400); // 400ms para coincidir con la duración de la animación
                return;
            }
            
            // Fallback: manejar otros tipos de modal
            const modal = document.getElementById('product-modal-overlay');
            if (modal) {
                console.log('✅ Modal overlay encontrado, cerrando...');
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
            console.log('⚠️ No se encontró modal para cerrar, solo limpiando estado');
            document.body.classList.remove('modal-open');
        }
        
        // Configurar ESC global para admin.php también
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' || e.keyCode === 27) {
                const activeModal = document.querySelector('.product-view-modal.show, #product-modal-overlay.show');
                if (activeModal) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('⌨️ ESC presionada en admin.php - cerrando modal');
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

        // Función de depuración para verificar el modal
        function debugModal() {
            const overlay = document.getElementById('product-modal-overlay');
            if (overlay) {
                console.log('🔍 Estado del modal:');
                console.log('Display:', overlay.style.display);
                console.log('Position:', overlay.style.position);
                console.log('Z-index:', overlay.style.zIndex);
                console.log('Classes:', overlay.className);
                console.log('Overlay element:', overlay);
                
                // Forzar estilos de depuración
                overlay.style.border = '3px solid red';
                overlay.style.background = 'rgba(255, 0, 0, 0.2)';
                console.log('✅ Estilos de depuración aplicados');
            } else {
                console.log('❌ No se encontró el modal overlay');
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

        // ===== INICIALIZAR ProductModalManager desde product-modals.js =====
        console.log('🔧 Verificando disponibilidad de ProductModalManager...');
        console.log('ProductModalManager type:', typeof ProductModalManager);
        console.log('ProductModalManager:', ProductModalManager);
        
        // Verificar que la clase ProductModalManager esté disponible
        if (typeof ProductModalManager !== 'undefined') {
            // Crear instancia REAL de ProductModalManager
            console.log('✅ ProductModalManager encontrado - creando instancia...');
            window.productModalManager = new ProductModalManager();
            console.log('✅ ProductModalManager inicializado correctamente');
            console.log('📋 Métodos disponibles:', Object.getOwnPropertyNames(Object.getPrototypeOf(window.productModalManager)));
            console.log('🔍 closeModal method:', typeof window.productModalManager.closeModal);
        } else {
            console.error('❌ ProductModalManager no está definido - verificar carga de product-modals.js');
            // Fallback: crear objeto básico de compatibilidad
            window.productModalManager = {
                showCreateProductModal: showCreateProductModal,
                showViewProductModal: showViewProductModal,
                showEditProductModal: showEditProductModal,
                forceCloseModal: forceCloseModal,
                cleanupModals: cleanupModals
            };
        }

        // ===== INICIALIZAR SmoothTableUpdater desde smooth-table-update.js =====
        console.log('🔧 Verificando disponibilidad de SmoothTableUpdater...');
        console.log('SmoothTableUpdater type:', typeof SmoothTableUpdater);
        
        if (typeof SmoothTableUpdater !== 'undefined') {
            console.log('✅ SmoothTableUpdater encontrado - creando instancia...');
            window.smoothTableUpdater = new SmoothTableUpdater();
            console.log('✅ SmoothTableUpdater inicializado correctamente');
            console.log('📋 Métodos disponibles:', Object.getOwnPropertyNames(Object.getPrototypeOf(window.smoothTableUpdater)));
        } else {
            console.error('❌ SmoothTableUpdater no está definido - verificar carga de smooth-table-update.js');
            window.smoothTableUpdater = null;
        }


        // Función global de emergencia para limpiar modales (accesible desde consola)
        window.emergencyCleanModal = function() {
            console.log('🧹 Ejecutando limpieza de emergencia de modales...');
            // Desbloquear scroll del body
            document.body.classList.remove('modal-open');
            forceCloseModal();
            cleanupModals();
            console.log('✅ Limpieza completada');
        };

        // Función de test para modal
        window.testModal = function() {
            console.log('🧪 === TEST MODAL ===');
            console.log('Verificando funciones...');
            
            if (typeof showCreateProductModal === 'function') {
                console.log('✅ showCreateProductModal existe');
                try {
                    showCreateProductModal();
                    console.log('✅ showCreateProductModal ejecutada');
                } catch (error) {
                    console.error('❌ Error ejecutando showCreateProductModal:', error);
                }
            } else {
                console.error('❌ showCreateProductModal no está definida');
            }
        };

        // Función de test para cerrar modal
        window.testCloseModal = function() {
            console.log('🧪 === TEST CLOSE MODAL ===');
            console.log('Verificando función de cierre...');
            
            if (typeof window.closeProductModal === 'function') {
                console.log('✅ closeProductModal existe globalmente');
                try {
                    window.closeProductModal();
                    console.log('✅ closeProductModal ejecutada');
                } catch (error) {
                    console.error('❌ Error ejecutando closeProductModal:', error);
                }
            } else {
                console.error('❌ closeProductModal no está definida globalmente');
            }
        };

        // Configuración inicial al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            // Los event listeners ahora están en onclick directo, no necesitamos configurar aquí
            
            // RESTAURAR TAB ACTIVO desde localStorage
            try {
                const savedTab = localStorage.getItem('admin_active_tab');
                console.log('📂 Tab guardado encontrado en localStorage:', savedTab);
                
                if (savedTab && savedTab !== 'dashboard') {
                    // Solo cambiar si es diferente de dashboard (que ya está activo por defecto)
                    const tabExists = document.querySelector(`[data-tab="${savedTab}"]`);
                    if (tabExists) {
                        console.log('🔄 Restaurando tab:', savedTab);
                        switchTab(savedTab);
                    } else {
                        console.warn('⚠️ Tab guardado no existe, manteniendo dashboard');
                    }
                } else {
                    console.log('📊 Dashboard activo (tab por defecto)');
                    // Guardar dashboard como tab activo si no hay nada guardado
                    if (!savedTab) {
                        localStorage.setItem('admin_active_tab', 'dashboard');
                    }
                }
            } catch (e) {
                console.warn('⚠️ Error al restaurar tab desde localStorage:', e);
            }
            
            // Verificar funciones de navegación primero
            console.log('🔍 Verificando funciones de navegación:', {
                switchTab: typeof window.switchTab,
                loadTabContent: typeof loadTabContent,
                loadProductos: typeof loadProductos
            });
            
            // Inicializar sistema de modales
            cleanupModals();
            console.log('📱 Sistema de modales inicializado');
            
            // Verificar que las funciones críticas existan
            console.log('🔍 Verificando funciones del modal:', {
                showCreateProductModal: typeof showCreateProductModal,
                openProductModal: typeof openProductModal,
                showModalOverlay: typeof showModalOverlay,
                closeProductModal: typeof closeProductModal
            });
            
            // Verificar botones después de un momento
            setTimeout(function() {
                console.log('🔍 === VERIFICACIÓN DE BOTONES ===');
                
                const createBtn = document.querySelector('.action-btn.add-product');
                const editBtns = document.querySelectorAll('.edit-product-btn');
                
                console.log('Botón Nuevo Producto encontrado:', !!createBtn);
                console.log('Cantidad de edit-product-btn:', editBtns.length);
                
                if (createBtn) {
                    console.log('createBtn existe:', createBtn);
                    console.log('createBtn onclick:', createBtn.onclick);
                } else {
                    console.warn('⚠️ Botón Nuevo Producto no encontrado');
                }
                
                // También verificar si hay elementos de modal
                const modalOverlay = document.getElementById('productModalOverlay');
                const productModal = document.getElementById('productModal');
                console.log('productModalOverlay encontrado:', !!modalOverlay);
                console.log('productModal encontrado:', !!productModal);
                
                console.log('🧪 Ejecuta testModal() en la consola para probar el modal');
            }, 1000);
        });

        // También limpiar cuando se recarga la página
        window.addEventListener('load', function() {
            cleanupModals();
        });

        // Limpiar modales cuando se abandona la página
        window.addEventListener('beforeunload', function() {
            forceCloseModal();
        });
        
        // ===== FUNCIÓN DE DIAGNÓSTICO DEL SISTEMA =====
        window.diagnosticoSistema = function() {
            console.log('');
            console.log('═══════════════════════════════════════════════════════');
            console.log('🔍 DIAGNÓSTICO DEL SISTEMA SPA');
            console.log('═══════════════════════════════════════════════════════');
            console.log('');
            
            // 1. Verificar funciones de navegación
            console.log('📍 FUNCIONES DE NAVEGACIÓN:');
            console.log('  ✓ switchTab:', typeof window.switchTab === 'function' ? '✅' : '❌');
            console.log('  ✓ loadTabContent:', typeof loadTabContent === 'function' ? '✅' : '❌');
            console.log('  ✓ destroyCurrentModule:', typeof window.destroyCurrentModule === 'function' ? '✅' : '❌');
            console.log('');
            
            // 2. Verificar funciones de carga específicas
            console.log('📦 FUNCIONES DE CARGA ÚNICAS:');
            console.log('  ✓ loadProductosSection:', typeof loadProductosSection === 'function' ? '✅' : '❌');
            console.log('  ✓ loadCategoriasSection:', typeof loadCategoriasSection === 'function' ? '✅' : '❌');
            console.log('  ✓ loadMarcasSection:', typeof loadMarcasSection === 'function' ? '✅' : '❌');
            console.log('  ✓ loadUsuariosSection:', typeof loadUsuariosSection === 'function' ? '✅' : '❌');
            console.log('');
            
            // 3. Verificar funciones de destrucción
            console.log('🗑️ FUNCIONES DE DESTRUCCIÓN:');
            console.log('  ✓ destroyProductosModule:', typeof window.destroyProductosModule === 'function' ? '✅ (cargado)' : '⏳ (se cargará con el módulo)');
            console.log('  ✓ destroyCategoriasModule:', typeof window.destroyCategoriasModule === 'function' ? '✅ (cargado)' : '⏳ (se cargará con el módulo)');
            console.log('  ✓ destroyMarcasModule:', typeof window.destroyMarcasModule === 'function' ? '✅ (cargado)' : '⏳ (se cargará con el módulo)');
            console.log('');
            
            // 4. Verificar estado actual
            console.log('📊 ESTADO ACTUAL:');
            const activeTab = localStorage.getItem('admin_active_tab') || 'dashboard';
            console.log('  • Tab activo:', activeTab);
            console.log('  • Modales abiertos:', document.querySelectorAll('.modal-overlay, .product-view-modal').length);
            console.log('  • Body bloqueado:', document.body.classList.contains('modal-open') ? '⚠️ SÍ' : '✅ NO');
            console.log('');
            
            // 5. Verificar localStorage de vistas
            console.log('👁️ ESTADO DE VISTAS (tabla/grid):');
            console.log('  • Productos:', localStorage.getItem('productos_view_mode') || 'table (default)');
            console.log('  • Categorías:', localStorage.getItem('categorias_view_mode') || 'table (default)');
            console.log('  • Marcas:', localStorage.getItem('marcas_view_mode') || 'table (default)');
            console.log('');
            
            // 6. Verificar funciones de modal
            console.log('🎭 FUNCIONES DE MODAL:');
            console.log('  ✓ showCreateProductModal:', typeof window.showCreateProductModal === 'function' ? '✅' : '❌');
            console.log('  ✓ showEditProductModal:', typeof window.showEditProductModal === 'function' ? '✅' : '❌');
            console.log('  ✓ closeProductModal:', typeof window.closeProductModal === 'function' ? '✅' : '❌');
            console.log('');
            
            console.log('═══════════════════════════════════════════════════════');
            console.log('💡 COMANDOS ÚTILES:');
            console.log('  • diagnosticoSistema() - Ver este diagnóstico');
            console.log('  • resetActiveTab() - Resetear tab activo');
            console.log('  • testNavigation() - Probar navegación');
            console.log('  • switchTab("productos") - Cambiar a productos');
            console.log('  • window.destroyCurrentModule() - Limpiar módulo actual');
            console.log('═══════════════════════════════════════════════════════');
            console.log('');
            
            return 'Diagnóstico completado ✅';
        };
        
        // Ejecutar diagnóstico al cargar
        console.log('🚀 Sistema SPA cargado. Ejecuta diagnosticoSistema() para verificar.');
    </script>
    
    <!-- ⭐ SISTEMA DE NOTIFICACIONES (Estilo idéntico a shop.php) -->
    <script>
    window.showNotification = function(message, type = 'info') {
        const container = document.getElementById('notification-container');
        if (!container) return;
        
        // Colores e iconos idénticos a shop.php
        const colors = {
            'success': '#2ecc71',
            'error': '#e74c3c',
            'warning': '#f39c12',
            'info': '#3498db'
        };
        
        const icons = {
            'success': '✓',
            'error': '✕',
            'warning': '⚠',
            'info': 'ℹ'
        };
        
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: relative;
            background: ${colors[type] || colors.info};
            color: white;
            padding: 15px 25px 15px 20px;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideInRight 0.3s ease;
            cursor: pointer;
        `;
        
        notification.innerHTML = `
            <span style="font-size: 20px;">${icons[type]}</span>
            <span>${message}</span>
        `;
        
        // Click para cerrar
        notification.onclick = function() {
            this.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => this.remove(), 300);
        };
        
        container.appendChild(notification);
        
        // Auto-cerrar después de 3 segundos (igual que shop.php)
        setTimeout(() => {
            notification.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    };
    
    // Animaciones (igual que shop.php)
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInRight {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
    </script>

</body>
</html>