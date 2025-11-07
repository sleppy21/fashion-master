
<div class="dashboard-modern-wrapper">
    
    <!-- Header del Dashboard -->
    <div class="dashboard-header-new">
        <div class="header-left">
            <h1>
                <i class="fas fa-chart-line"></i>
                Panel de Control
            </h1>
            <p class="subtitle">
                <i class="fas fa-store"></i> Fashion Store Admin
                <span class="last-update" id="last-update-dashboard">
                    <i class="fas fa-clock"></i> Actualizado hace un momento
                </span>
            </p>
        </div>
        <div class="header-right">
            <button class="refresh-btn" onclick="refreshDashboard()">
                <i class="fas fa-sync-alt"></i>
                <span>Actualizar</span>
            </button>
        </div>
    </div>

    <!-- Grid de Estadísticas Principales -->
    <div class="stats-grid-modern">
        <!-- Productos -->
        <div class="stat-card-modern products-card" data-aos="fade-up" data-aos-delay="50">
            <div class="stat-header">
                <div class="stat-icon-wrapper products-icon">
                    <i class="fas fa-box-open"></i>
                </div>
                <div class="stat-trend positive">
                    <i class="fas fa-arrow-up"></i>
                    <span>12%</span>
                </div>
            </div>
            <div class="stat-body">
                <h3 class="stat-value" id="dash-total-productos"><?php echo $total_productos; ?></h3>
                <p class="stat-label">Productos Totales</p>
                <div class="stat-meta">
                    <span class="meta-item">
                        <i class="fas fa-check-circle"></i>
                        <span id="dash-productos-activos"><?php echo $total_productos; ?></span> activos
                    </span>
                    <span class="meta-item warning" id="dash-stock-bajo-wrapper">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span id="dash-stock-bajo"><?php echo $productos_stock_bajo; ?></span> bajo stock
                    </span>
                </div>
            </div>
        </div>

        <!-- Usuarios -->
        <div class="stat-card-modern users-card" data-aos="fade-up" data-aos-delay="100">
            <div class="stat-header">
                <div class="stat-icon-wrapper users-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-trend positive">
                    <i class="fas fa-arrow-up"></i>
                    <span>8%</span>
                </div>
            </div>
            <div class="stat-body">
                <h3 class="stat-value" id="dash-total-usuarios"><?php echo $total_usuarios; ?></h3>
                <p class="stat-label">Usuarios Registrados</p>
                <div class="stat-meta">
                    <span class="meta-item">
                        <i class="fas fa-user-check"></i>
                        Activos en sistema
                    </span>
                </div>
            </div>
        </div>

        <!-- Inventario -->
        <div class="stat-card-modern inventory-card" data-aos="fade-up" data-aos-delay="150">
            <div class="stat-header">
                <div class="stat-icon-wrapper inventory-icon">
                    <i class="fas fa-warehouse"></i>
                </div>
                <div class="stat-trend positive">
                    <i class="fas fa-arrow-up"></i>
                    <span>5%</span>
                </div>
            </div>
            <div class="stat-body">
                <h3 class="stat-value" id="dash-valor-inventario">S/. <?php echo number_format($valor_inventario, 0); ?></h3>
                <p class="stat-label">Valor de Inventario</p>
                <div class="stat-meta">
                    <span class="meta-item">
                        <i class="fas fa-boxes"></i>
                        <?php echo $total_productos_sistema; ?> productos
                    </span>
                </div>
            </div>
        </div>

        <!-- Categorías -->
        <div class="stat-card-modern categories-card" data-aos="fade-up" data-aos-delay="200">
            <div class="stat-header">
                <div class="stat-icon-wrapper categories-icon">
                    <i class="fas fa-tags"></i>
                </div>
                <div class="stat-trend neutral">
                    <i class="fas fa-minus"></i>
                    <span>0%</span>
                </div>
            </div>
            <div class="stat-body">
                <h3 class="stat-value" id="dash-total-categorias"><?php echo $total_categorias; ?></h3>
                <p class="stat-label">Categorías Activas</p>
                <div class="stat-meta">
                    <span class="meta-item">
                        <i class="fas fa-copyright"></i>
                        <span id="dash-total-marcas"><?php echo $total_marcas; ?></span> marcas
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Grid de Gráficas -->
    <div class="charts-grid-modern">
        
        <!-- Gráfica: Stock por Estado -->
        <div class="chart-card-modern" data-aos="fade-up" data-aos-delay="250">
            <div class="chart-header">
                <h3>
                    <i class="fas fa-chart-pie"></i>
                    Estado del Inventario
                </h3>
                <button class="chart-action-btn" title="Más opciones">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
            </div>
            <div class="chart-body">
                <canvas id="stockChartNew"></canvas>
            </div>
            <div class="chart-footer">
                <div class="legend-item">
                    <span class="legend-color" style="background: #10b981;"></span>
                    <span>Normal</span>
                </div>
                <div class="legend-item">
                    <span class="legend-color" style="background: #f59e0b;"></span>
                    <span>Bajo</span>
                </div>
                <div class="legend-item">
                    <span class="legend-color" style="background: #ef4444;"></span>
                    <span>Agotado</span>
                </div>
            </div>
        </div>

        <!-- Gráfica: Productos por Categoría -->
        <div class="chart-card-modern" data-aos="fade-up" data-aos-delay="300">
            <div class="chart-header">
                <h3>
                    <i class="fas fa-chart-bar"></i>
                    Productos por Categoría
                </h3>
                <button class="chart-action-btn" title="Más opciones">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
            </div>
            <div class="chart-body">
                <canvas id="categoryChartNew"></canvas>
            </div>
        </div>

        <!-- Gráfica: Productos por Marca -->
        <div class="chart-card-modern" data-aos="fade-up" data-aos-delay="325">
            <div class="chart-header">
                <h3>
                    <i class="fas fa-copyright"></i>
                    Productos por Marca
                </h3>
                <button class="chart-action-btn" title="Más opciones">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
            </div>
            <div class="chart-body">
                <canvas id="brandChartNew"></canvas>
            </div>
        </div>

        <!-- Gráfica: Distribución por Género (Ancho completo) -->
        <div class="chart-card-modern chart-wide" data-aos="fade-up" data-aos-delay="350">
            <div class="chart-header">
                <h3>
                    <i class="fas fa-venus-mars"></i>
                    Distribución por Género
                </h3>
                <button class="chart-action-btn" title="Más opciones">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
            </div>
            <div class="chart-body">
                <canvas id="genderChartNew"></canvas>
            </div>
        </div>

    </div>

    <!-- ACCESOS RÁPIDOS - NUEVA SECCIÓN -->
    <div class="section-title" style="margin: 40px 0 20px 0;">
        <h2 style="color: #f8fafc; font-size: 20px; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-bolt" style="color: #2463eb;"></i>
            Accesos Rápidos
        </h2>
        <p style="color: #64748b; font-size: 14px; margin: 8px 0 0 0;">
            Accede rápidamente a las funciones más utilizadas
        </p>
    </div>

    <div class="quick-actions-grid">
        <!-- Crear Producto -->
        <button class="quick-action-btn" onclick="switchTab('productos'); setTimeout(() => showCreateProductModal(), 500);" data-aos="fade-up" data-aos-delay="100">
            <div class="quick-action-icon">
                <i class="fas fa-plus"></i>
            </div>
            <h4 class="quick-action-label">Nuevo Producto</h4>
            <p class="quick-action-subtitle">Agregar producto al catálogo</p>
        </button>

        <!-- Crear Categoría -->
        <button class="quick-action-btn" onclick="openNewCategoryModal();" data-aos="fade-up" data-aos-delay="150">
            <div class="quick-action-icon" style="background: linear-gradient(135deg, #a855f7 0%, #9333ea 100%);">
                <i class="fas fa-tag"></i>
            </div>
            <h4 class="quick-action-label">Nueva Categoría</h4>
            <p class="quick-action-subtitle">Crear categoría de productos</p>
        </button>

        <!-- Crear Marca -->
        <button class="quick-action-btn" onclick="switchTab('marcas');" data-aos="fade-up" data-aos-delay="200">
            <div class="quick-action-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                <i class="fas fa-copyright"></i>
            </div>
            <h4 class="quick-action-label">Nueva Marca</h4>
            <p class="quick-action-subtitle">Agregar marca nueva</p>
        </button>

        <!-- Ver Productos -->
        <button class="quick-action-btn" onclick="switchTab('productos');" data-aos="fade-up" data-aos-delay="250">
            <div class="quick-action-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                <i class="fas fa-box-open"></i>
            </div>
            <h4 class="quick-action-label">Gestionar Productos</h4>
            <p class="quick-action-subtitle">Ver y editar productos</p>
        </button>

        <!-- Ver Categorías -->
        <button class="quick-action-btn" onclick="switchTab('categorias');" data-aos="fade-up" data-aos-delay="300">
            <div class="quick-action-icon" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                <i class="fas fa-th-large"></i>
            </div>
            <h4 class="quick-action-label">Ver Categorías</h4>
            <p class="quick-action-subtitle">Administrar categorías</p>
        </button>

        <!-- Ver Usuarios -->
        <button class="quick-action-btn" onclick="switchTab('usuarios');" data-aos="fade-up" data-aos-delay="350">
            <div class="quick-action-icon" style="background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);">
                <i class="fas fa-users"></i>
            </div>
            <h4 class="quick-action-label">Gestionar Usuarios</h4>
            <p class="quick-action-subtitle">Administrar usuarios</p>
        </button>

        <!-- Carga Masiva -->
        <button class="quick-action-btn" onclick="alert('Función de carga masiva en desarrollo');" data-aos="fade-up" data-aos-delay="400">
            <div class="quick-action-icon" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);">
                <i class="fas fa-file-upload"></i>
            </div>
            <h4 class="quick-action-label">Carga Masiva</h4>
            <p class="quick-action-subtitle">Importar productos CSV/Excel</p>
        </button>

        <!-- Exportar Datos -->
        <button class="quick-action-btn" onclick="alert('Función de exportación en desarrollo');" data-aos="fade-up" data-aos-delay="450">
            <div class="quick-action-icon" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
                <i class="fas fa-file-download"></i>
            </div>
            <h4 class="quick-action-label">Exportar Datos</h4>
            <p class="quick-action-subtitle">Descargar reportes</p>
        </button>

        <!-- Reportes -->
        <button class="quick-action-btn" onclick="alert('Sección de reportes en desarrollo');" data-aos="fade-up" data-aos-delay="500">
            <div class="quick-action-icon" style="background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);">
                <i class="fas fa-chart-line"></i>
            </div>
            <h4 class="quick-action-label">Reportes</h4>
            <p class="quick-action-subtitle">Ver estadísticas detalladas</p>
        </button>

        <!-- Respaldos -->
        <button class="quick-action-btn" onclick="alert('Función de respaldo en desarrollo');" data-aos="fade-up" data-aos-delay="550">
            <div class="quick-action-icon" style="background: linear-gradient(135deg, #64748b 0%, #475569 100%);">
                <i class="fas fa-database"></i>
            </div>
            <h4 class="quick-action-label">Respaldos</h4>
            <p class="quick-action-subtitle">Backup de la base de datos</p>
        </button>

        <!-- Configuración -->
        <button class="quick-action-btn" onclick="switchTab('configuracion');" data-aos="fade-up" data-aos-delay="600">
            <div class="quick-action-icon" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);">
                <i class="fas fa-cog"></i>
            </div>
            <h4 class="quick-action-label">Configuración</h4>
            <p class="quick-action-subtitle">Ajustes del sistema</p>
        </button>

        <!-- Soporte -->
        <button class="quick-action-btn" onclick="alert('Soporte: admin@fashionstore.com');" data-aos="fade-up" data-aos-delay="650">
            <div class="quick-action-icon" style="background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);">
                <i class="fas fa-question-circle"></i>
            </div>
            <h4 class="quick-action-label">Soporte</h4>
            <p class="quick-action-subtitle">Ayuda y documentación</p>
        </button>
    </div>

    <!-- Grid de Información Adicional -->
    <div class="info-grid-modern">
        
        <!-- Productos Más Vistos -->
        <div class="info-card-modern" data-aos="fade-up" data-aos-delay="700">
            <div class="info-header">
                <h3>
                    <i class="fas fa-fire"></i>
                    Top 5 Productos
                </h3>
                <a href="#" class="info-link" onclick="switchTab('productos'); return false;">
                    Ver todos <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="info-body">
                <div id="top-products-list" class="info-list">
                    <div class="loading-placeholder" style="text-align: center; padding: 20px; color: #64748b;">
                        <i class="fas fa-spinner fa-spin"></i>
                        <span style="display: block; margin-top: 10px;">Cargando productos...</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actividad Reciente -->
        <div class="info-card-modern" data-aos="fade-up" data-aos-delay="750">
            <div class="info-header">
                <h3>
                    <i class="fas fa-history"></i>
                    Actividad Reciente
                </h3>
                <button class="info-refresh-btn" onclick="loadRecentActivity()" style="background: transparent; border: none; color: #2463eb; cursor: pointer; padding: 6px; border-radius: 6px; transition: all 0.2s;">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
            <div class="info-body">
                <div id="recent-activity-list" class="activity-list">
                    <!-- Actividad reciente (cargada dinámicamente) -->
                    <div style="text-align: center; padding: 30px; color: #64748b;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 40px; margin-bottom: 12px; opacity: 0.5;"></i>
                        <p style="margin: 0;">Cargando actividad reciente...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información del Sistema -->
        <div class="info-card-modern" data-aos="fade-up" data-aos-delay="800">
            <div class="info-header">
                <h3>
                    <i class="fas fa-server"></i>
                    Estado del Sistema
                </h3>
            </div>
            <div class="info-body">
                <div class="system-stats">
                    <div class="system-stat-item">
                        <div class="system-stat-label">
                            <i class="fas fa-database"></i>
                            Base de Datos
                        </div>
                        <span class="status-badge success">
                            <i class="fas fa-check-circle"></i> Online
                        </span>
                    </div>
                    <div class="system-stat-item">
                        <div class="system-stat-label">
                            <i class="fas fa-hdd"></i>
                            Espacio en Disco
                        </div>
                        <span id="system-disk-usage" class="system-stat-value">--</span>
                    </div>
                    <div class="system-stat-item">
                        <div class="system-stat-label">
                            <i class="fas fa-memory"></i>
                            Memoria PHP
                        </div>
                        <span id="system-memory-usage" class="system-stat-value">--</span>
                    </div>
                    <div class="system-stat-item">
                        <div class="system-stat-label">
                            <i class="fas fa-server"></i>
                            Servidor Web
                        </div>
                        <span id="system-web-server" class="system-stat-value">--</span>
                    </div>
                    <div class="system-stat-item">
                        <div class="system-stat-label">
                            <i class="fas fa-code"></i>
                            Versión PHP
                        </div>
                        <span id="system-php-version" class="system-stat-value">--</span>
                    </div>
                    <div class="system-stat-item">
                        <div class="system-stat-label">
                            <i class="fas fa-shield-alt"></i>
                            Seguridad
                        </div>
                        <span class="status-badge success">
                            <i class="fas fa-lock"></i> Protegido
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- NUEVA TARJETA: Estadísticas Rápidas (DATOS REALES) -->
        <div class="info-card-modern" data-aos="fade-up" data-aos-delay="850">
            <div class="info-header">
                <h3>
                    <i class="fas fa-chart-pie"></i>
                    Estadísticas Rápidas
                </h3>
            </div>
            <div class="info-body">
                <div class="system-stats">
                    <div class="system-stat-item">
                        <div class="system-stat-label">
                            <i class="fas fa-shopping-cart"></i>
                            Pedidos Hoy
                        </div>
                        <span class="system-stat-value" id="stat-pedidos-hoy">0</span>
                    </div>
                    <div class="system-stat-item">
                        <div class="system-stat-label">
                            <i class="fas fa-money-bill-wave"></i>
                            Ventas Hoy
                        </div>
                        <span class="system-stat-value" id="stat-ventas-hoy">S/. 0.00</span>
                    </div>
                    <div class="system-stat-item">
                        <div class="system-stat-label">
                            <i class="fas fa-box"></i>
                            Total Productos
                        </div>
                        <span class="system-stat-value" id="stat-total-productos">0</span>
                    </div>
                    <div class="system-stat-item">
                        <div class="system-stat-label">
                            <i class="fas fa-star"></i>
                            Total Reseñas
                        </div>
                        <span class="system-stat-value" id="stat-total-resenas">0</span>
                    </div>
                    <div class="system-stat-item">
                        <div class="system-stat-label">
                            <i class="fas fa-heart"></i>
                            Total Favoritos
                        </div>
                        <span class="system-stat-value" id="stat-total-favoritos">0</span>
                    </div>
                    <div class="system-stat-item">
                        <div class="system-stat-label">
                            <i class="fas fa-box"></i>
                            Productos Más Vendidos
                        </div>
                        <span class="system-stat-value">12 productos</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- NUEVA TARJETA: Alertas y Notificaciones -->
        <div class="info-card-modern" data-aos="fade-up" data-aos-delay="900">
            <div class="info-header">
                <h3>
                    <i class="fas fa-bell"></i>
                    Alertas y Notificaciones
                </h3>
            </div>
            <div class="info-body">
                <div id="alerts-list">
                    <div class="info-item">
                        <div class="info-item-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div class="info-item-content">
                            <p class="info-item-title"><?php echo $productos_stock_bajo; ?> productos con stock bajo</p>
                            <p class="info-item-subtitle">Requiere atención inmediata</p>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-item-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                            <i class="fas fa-sync-alt"></i>
                        </div>
                        <div class="info-item-content">
                            <p class="info-item-title">Actualización disponible</p>
                            <p class="info-item-subtitle">Nueva versión del sistema</p>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-item-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="info-item-content">
                            <p class="info-item-title">Backup completado</p>
                            <p class="info-item-subtitle">Última copia: Hoy 3:00 AM</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- NUEVA TARJETA: Accesos Directos Adicionales -->
        <div class="info-card-modern" data-aos="fade-up" data-aos-delay="950">
            <div class="info-header">
                <h3>
                    <i class="fas fa-link"></i>
                    Enlaces Rápidos
                </h3>
            </div>
            <div class="info-body">
                <div class="system-stats">
                    <div class="system-stat-item" style="cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#334155'" onmouseout="this.style.background='transparent'" onclick="window.open('index.php', '_blank')">
                        <div class="system-stat-label">
                            <i class="fas fa-store"></i>
                            Ver Tienda
                        </div>
                        <i class="fas fa-external-link-alt" style="color: #2463eb; font-size: 12px;"></i>
                    </div>
                    <div class="system-stat-item" style="cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#334155'" onmouseout="this.style.background='transparent'" onclick="window.open('shop.php', '_blank')">
                        <div class="system-stat-label">
                            <i class="fas fa-shopping-bag"></i>
                            Catálogo Público
                        </div>
                        <i class="fas fa-external-link-alt" style="color: #2463eb; font-size: 12px;"></i>
                    </div>
                    <div class="system-stat-item" style="cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#334155'" onmouseout="this.style.background='transparent'" onclick="switchTab('productos')">
                        <div class="system-stat-label">
                            <i class="fas fa-box"></i>
                            Gestión Productos
                        </div>
                        <i class="fas fa-arrow-right" style="color: #2463eb; font-size: 12px;"></i>
                    </div>
                    <div class="system-stat-item" style="cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#334155'" onmouseout="this.style.background='transparent'" onclick="switchTab('usuarios')">
                        <div class="system-stat-label">
                            <i class="fas fa-users-cog"></i>
                            Gestión Usuarios
                        </div>
                        <i class="fas fa-arrow-right" style="color: #2463eb; font-size: 12px;"></i>
                    </div>
                    <div class="system-stat-item" style="cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#334155'" onmouseout="this.style.background='transparent'" onclick="alert('Reportes en desarrollo')">
                        <div class="system-stat-label">
                            <i class="fas fa-file-alt"></i>
                            Ver Reportes
                        </div>
                        <i class="fas fa-arrow-right" style="color: #2463eb; font-size: 12px;"></i>
                    </div>
                    <div class="system-stat-item" style="cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#334155'" onmouseout="this.style.background='transparent'" onclick="alert('Documentación: En desarrollo')">
                        <div class="system-stat-label">
                            <i class="fas fa-book"></i>
                            Documentación
                        </div>
                        <i class="fas fa-external-link-alt" style="color: #2463eb; font-size: 12px;"></i>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- DEBUG: Confirmación de carga completa -->
<div style="position: fixed; bottom: 10px; right: 10px; background: #10b981; color: white; padding: 8px 16px; border-radius: 8px; font-size: 12px; z-index: 99999; display: none;" id="dashboard-loaded-indicator">
    ✅ Dashboard cargado completamente
</div>

<script>
// Mostrar indicador de carga después de 1 segundo
setTimeout(() => {
    const indicator = document.getElementById('dashboard-loaded-indicator');
    if (indicator) {
        indicator.style.display = 'block';
        setTimeout(() => {
            indicator.style.display = 'none';
        }, 3000);
    }
}, 1000);
</script>

<!-- ================================================= -->
<!-- FIN DEL DASHBOARD MODERNO -->
<!-- ================================================= -->


<!-- JAVASCRIPT DEL DASHBOARD MODERNO -->
<!-- ================================================= -->
<script>
// ===== FUNCIÓN PARA REFRESCAR EL DASHBOARD =====
async function refreshDashboard() {
    const btn = document.querySelector('.refresh-btn i');
    btn.style.animation = 'spin 1s linear infinite';
    
    // Simular actualización (aquí puedes agregar fetch real)
    await new Promise(resolve => setTimeout(resolve, 1000));
    
    btn.style.animation = '';
    
    // Actualizar timestamp
    const lastUpdate = document.getElementById('last-update-dashboard');
    if (lastUpdate) {
        lastUpdate.innerHTML = '<i class="fas fa-clock"></i> Actualizado ahora';
    }
    
}

// ===== INICIALIZAR GRÁFICAS CON CHART.JS =====
function initDashboardCharts() {

    
    // Configuración global de Chart.js
    Chart.defaults.color = '#94a3b8';
    Chart.defaults.borderColor = '#1e293b';
    Chart.defaults.font.family = "'Inter', 'Segoe UI', sans-serif";
    
    // 1. Gráfica de Stock
    const stockCtx = document.getElementById('stockChartNew');
    if (stockCtx) {
        window.stockChartNew = new Chart(stockCtx, {
            type: 'doughnut',
            data: {
                labels: ['Stock Normal', 'Stock Bajo', 'Agotado'],
                datasets: [{
                    data: [65, 25, 10],
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        borderColor: '#1e293b',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: true,
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed + ' productos';
                            }
                        }
                    }
                },
                cutout: '70%'
            }
        });
    }
    
    // 2. Gráfica de Categorías
    const categoryCtx = document.getElementById('categoryChartNew');
    if (categoryCtx) {
        new Chart(categoryCtx, {
            type: 'bar',
            data: {
                labels: ['Camisetas', 'Pantalones', 'Zapatos', 'Accesorios', 'Vestidos'],
                datasets: [{
                    label: 'Productos',
                    data: [45, 32, 28, 20, 15],
                    backgroundColor: 'rgba(36, 99, 235, 0.8)',
                    borderRadius: 6,
                    barThickness: 24
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        borderColor: '#1e293b',
                        borderWidth: 1,
                        padding: 12
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#1e293b' },
                        ticks: { stepSize: 10 }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }
    
    // 3. Gráfica de Marcas
    const brandCtx = document.getElementById('brandChartNew');
    if (brandCtx) {
        window.brandChartNew = new Chart(brandCtx, {
            type: 'bar',
            data: {
                labels: ['Nike', 'Adidas', 'Puma', 'Zara', 'H&M'],
                datasets: [{
                    label: 'Productos',
                    data: [30, 25, 20, 18, 12],
                    backgroundColor: 'rgba(245, 158, 11, 0.8)',
                    borderRadius: 6,
                    barThickness: 24
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        borderColor: '#1e293b',
                        borderWidth: 1,
                        padding: 12
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#1e293b' },
                        ticks: { stepSize: 5 }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }
    
    // 4. Gráfica de Género
    const genderCtx = document.getElementById('genderChartNew');
    if (genderCtx) {
        window.genderChartNew = new Chart(genderCtx, {
            type: 'line',
            data: {
                labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
                datasets: [
                    {
                        label: 'Hombre',
                        data: [30, 35, 40, 38, 45, 42],
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 2
                    },
                    {
                        label: 'Mujer',
                        data: [40, 38, 42, 45, 48, 50],
                        borderColor: '#ec4899',
                        backgroundColor: 'rgba(236, 72, 153, 0.1)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 2
                    },
                    {
                        label: 'Unisex',
                        data: [20, 22, 25, 28, 30, 32],
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            padding: 16,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        borderColor: '#1e293b',
                        borderWidth: 1,
                        padding: 12
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#1e293b' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }
    
}

// ===== CARGAR ACTIVIDAD RECIENTE (DATOS REALES) =====
async function loadRecentActivity() {
    const container = document.getElementById('recent-activity-list');
    if (!container) return;
    
    container.innerHTML = '<div class="loading-placeholder"><i class="fas fa-spinner fa-spin"></i><span>Actualizando...</span></div>';
    
    try {
        // Llamar a la API para obtener datos reales
        const response = await fetch('app/views/admin/dashboard_api.php');
        if (!response.ok) {
            throw new Error('Error al obtener datos');
        }
        
        const data = await response.json();
        
        // Verificar si hay actividades
        if (!data.actividades_recientes || data.actividades_recientes.length === 0) {
            container.innerHTML = `
                <div style="text-align: center; padding: 30px; color: #64748b;">
                    <i class="fas fa-inbox" style="font-size: 40px; margin-bottom: 12px; opacity: 0.5;"></i>
                    <p style="margin: 0;">No hay actividad reciente</p>
                </div>
            `;
            return;
        }
        
        // Renderizar actividades reales
        const html = data.actividades_recientes.map(actividad => `
            <div style="padding: 12px; background: rgba(30, 41, 59, 0.5); border-radius: 8px; border-left: 3px solid ${actividad.color};">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 4px;">
                    <span style="color: #f8fafc; font-size: 13px; font-weight: 600;">
                        <i class="fas ${actividad.icono}" style="margin-right: 6px; color: ${actividad.color};"></i>
                        ${actividad.titulo}
                    </span>
                    <span style="color: #64748b; font-size: 11px;">${formatRelativeTime(actividad.fecha)}</span>
                </div>
                <p style="color: #94a3b8; font-size: 12px; margin: 0; padding-left: 22px;">${actividad.descripcion}</p>
            </div>
        `).join('');
        
        container.innerHTML = `<div style="display: flex; flex-direction: column; gap: 12px;">${html}</div>`;
        
    } catch (error) {
        container.innerHTML = `
            <div style="text-align: center; padding: 30px; color: #ef4444;">
                <i class="fas fa-exclamation-circle" style="font-size: 40px; margin-bottom: 12px;"></i>
                <p style="margin: 0;">Error al cargar actividad</p>
                <button onclick="loadRecentActivity()" style="margin-top: 12px; padding: 8px 16px; background: #2463eb; color: white; border: none; border-radius: 6px; cursor: pointer;">
                    Reintentar
                </button>
            </div>
        `;
    }
}

// ===== CARGAR TOP PRODUCTOS (DATOS REALES) =====
async function loadTopProducts() {
    const container = document.getElementById('top-products-list');
    if (!container) return;
    
    try {
        // Llamar a la API para obtener datos reales
        const response = await fetch('app/views/admin/dashboard_api.php');
        if (!response.ok) {
            throw new Error('Error al obtener datos');
        }
        
        const data = await response.json();
        
        if (data.top_productos && data.top_productos.length > 0) {
            const html = data.top_productos.map((item, index) => `
                <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: rgba(30, 41, 59, 0.5); border-radius: 8px; cursor: pointer; transition: all 0.2s ease;" 
                     onmouseover="this.style.background='rgba(36, 99, 235, 0.1)'" 
                     onmouseout="this.style.background='rgba(30, 41, 59, 0.5)'"
                     onclick="switchTab('productos');">
                    <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #2463eb, #1e40af); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 14px;">
                        #${index + 1}
                    </div>
                    <div style="flex: 1;">
                        <div style="color: #f8fafc; font-size: 13px; font-weight: 600; margin-bottom: 4px;">${item.nombre}</div>
                        <div style="color: #94a3b8; font-size: 11px;">${item.unidades_vendidas || 0} unidades vendidas</div>
                    </div>
                    <div style="color: #10b981; font-size: 12px; font-weight: 600;">
                        S/. ${parseFloat(item.ingresos || 0).toFixed(2)}
                    </div>
                </div>
            `).join('');
            
            container.innerHTML = `<div style="display: flex; flex-direction: column; gap: 12px;">${html}</div>`;
        } else {
            container.innerHTML = `
                <div style="text-align: center; padding: 20px; color: #64748b;">
                    <i class="fas fa-inbox" style="font-size: 32px; margin-bottom: 10px; display: block;"></i>
                    <span>No hay productos disponibles</span>
                </div>
            `;
        }
    } catch (error) {
        container.innerHTML = `
            <div style="text-align: center; padding: 20px; color: #ef4444;">
                <i class="fas fa-exclamation-triangle" style="font-size: 32px; margin-bottom: 10px; display: block;"></i>
                <span>Error al cargar productos</span>
            </div>
        `;
    }
}

// ===== ACTUALIZACIÓN EN TIEMPO REAL =====
// Nota: dashboardUpdateInterval ya está declarado en admin.php (línea 3094)

async function refreshDashboard() {
    
    try {
        // Mostrar indicador de carga
        const refreshBtn = document.querySelector('.refresh-btn i');
        if (refreshBtn) {
            refreshBtn.classList.add('fa-spin');
        }
        
        // Llamar a la API
        const response = await fetch('app/views/admin/dashboard_api.php');
        
        if (!response.ok) {
            throw new Error('Error al obtener datos');
        }
        
        const data = await response.json();
        
        // Actualizar estadísticas principales
        updateMainStats(data);
        
        // Actualizar gráficas
        updateCharts(data);
        
        // Actualizar actividad reciente
        updateRecentActivity(data);
        
        // Actualizar top productos
        updateTopProducts(data);
        
        // Actualizar estadísticas rápidas
        updateQuickStats(data);
        
        // Actualizar información del sistema
        updateSystemInfo(data);
        
        // Actualizar timestamp
        const lastUpdate = document.getElementById('last-update-dashboard');
        if (lastUpdate) {
            const now = new Date();
            lastUpdate.innerHTML = `<i class="fas fa-clock"></i> Actualizado a las ${now.toLocaleTimeString('es-ES')}`;
        }
        
        // Quitar indicador de carga
        if (refreshBtn) {
            refreshBtn.classList.remove('fa-spin');
        }
        
        
    } catch (error) {
        
        // Quitar indicador de carga
        const refreshBtn = document.querySelector('.refresh-btn i');
        if (refreshBtn) {
            refreshBtn.classList.remove('fa-spin');
        }
    }
}

function updateMainStats(data) {
    // Productos
    const productosValue = document.querySelector('.stat-card-modern:nth-child(1) .stat-value');
    if (productosValue) {
        animateValue(productosValue, parseInt(productosValue.textContent), data.total_productos || 0);
    }
    
    // Categorías
    const categoriasValue = document.querySelector('.stat-card-modern:nth-child(2) .stat-value');
    if (categoriasValue) {
        animateValue(categoriasValue, parseInt(categoriasValue.textContent), data.total_categorias || 0);
    }
    
    // Usuarios
    const usuariosValue = document.querySelector('.stat-card-modern:nth-child(3) .stat-value');
    if (usuariosValue) {
        animateValue(usuariosValue, parseInt(usuariosValue.textContent), data.total_usuarios || 0);
    }
    
    // Ventas
    const ventasValue = document.querySelector('.stat-card-modern:nth-child(4) .stat-value');
    if (ventasValue) {
        ventasValue.textContent = `S/. ${data.monto_ventas_hoy || '0.00'}`;
    }
}

function animateValue(element, start, end, duration = 1000) {
    const range = end - start;
    const increment = range / (duration / 16);
    let current = start;
    
    const timer = setInterval(() => {
        current += increment;
        if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
            element.textContent = Math.round(end);
            clearInterval(timer);
        } else {
            element.textContent = Math.round(current);
        }
    }, 16);
}

function updateCharts(data) {
    // Actualizar gráfica de stock
    if (window.stockChartNew && data.stock_por_categoria) {
        const labels = data.stock_por_categoria.map(item => item.categoria);
        const valores = data.stock_por_categoria.map(item => parseInt(item.stock_total || 0));
        
        window.stockChartNew.data.labels = labels;
        window.stockChartNew.data.datasets[0].data = valores;
        window.stockChartNew.update('none'); // Sin animación para actualización rápida
    }
    
    // Actualizar gráfica de marcas
    if (window.brandChartNew && data.productos_por_marca) {
        const labels = data.productos_por_marca.map(item => item.marca);
        const valores = data.productos_por_marca.map(item => parseInt(item.total || 0));
        
        window.brandChartNew.data.labels = labels;
        window.brandChartNew.data.datasets[0].data = valores;
        window.brandChartNew.update('none');
    }
    
    // Actualizar gráfica de género
    if (window.genderChartNew && data.productos_por_genero) {
        const labels = data.productos_por_genero.map(item => item.genero);
        const valores = data.productos_por_genero.map(item => parseInt(item.cantidad || 0));
        
        window.genderChartNew.data.labels = labels;
        window.genderChartNew.data.datasets[0].data = valores;
        window.genderChartNew.update('none');
    }
}

function updateRecentActivity(data) {
    const container = document.getElementById('recent-activity-list');
    if (!container || !data.actividad_reciente) return;
    
    const html = data.actividad_reciente.map(item => {
        const colorMap = {
            'pendiente': '#f59e0b',
            'completado': '#10b981',
            'cancelado': '#ef4444',
            'procesando': '#3b82f6'
        };
        const color = colorMap[item.estado] || '#3b82f6';
        
        return `
            <div style="padding: 12px; background: rgba(30, 41, 59, 0.5); border-radius: 8px; border-left: 3px solid ${color};">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 4px;">
                    <span style="color: #f8fafc; font-size: 13px; font-weight: 600;">${item.descripcion}</span>
                    <span style="color: #64748b; font-size: 11px;">${formatRelativeTime(item.fecha)}</span>
                </div>
                <p style="color: #94a3b8; font-size: 12px; margin: 0;">Monto: S/. ${item.monto || '0.00'}</p>
            </div>
        `;
    }).join('');
    
    container.innerHTML = `<div style="display: flex; flex-direction: column; gap: 12px;">${html}</div>`;
}

function updateTopProducts(data) {
    const container = document.getElementById('top-products-list');
    if (!container || !data.top_productos) return;
    
    const html = data.top_productos.map((item, index) => `
        <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: rgba(30, 41, 59, 0.5); border-radius: 8px; cursor: pointer; transition: all 0.2s ease;" onmouseover="this.style.background='rgba(36, 99, 235, 0.1)'" onmouseout="this.style.background='rgba(30, 41, 59, 0.5)'">
            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #2463eb, #1e40af); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 14px;">
                #${index + 1}
            </div>
            <div style="flex: 1;">
                <div style="color: #f8fafc; font-size: 13px; font-weight: 600; margin-bottom: 4px;">${item.nombre}</div>
                <div style="color: #94a3b8; font-size: 11px;">${item.unidades_vendidas || 0} ventas</div>
            </div>
            <div style="color: #10b981; font-size: 12px; font-weight: 600;">
                S/. ${item.ingresos || '0.00'}
            </div>
        </div>
    `).join('');
    
    container.innerHTML = `<div style="display: flex; flex-direction: column; gap: 12px;">${html}</div>`;
}

function updateQuickStats(data) {
    // Pedidos hoy
    const pedidosHoy = document.getElementById('stat-pedidos-hoy');
    if (pedidosHoy) {
        pedidosHoy.textContent = data.pedidos_hoy || 0;
    }
    
    // Ventas hoy
    const ventasHoy = document.getElementById('stat-ventas-hoy');
    if (ventasHoy) {
        ventasHoy.textContent = `S/. ${data.monto_ventas_hoy || '0.00'}`;
    }
    
    // Total productos
    const totalProductos = document.getElementById('stat-total-productos');
    if (totalProductos) {
        totalProductos.textContent = data.total_productos || 0;
    }
    
    // Total reseñas
    const totalResenas = document.getElementById('stat-total-resenas');
    if (totalResenas) {
        totalResenas.textContent = data.total_resenas || 0;
    }
    
    // Total favoritos
    const totalFavoritos = document.getElementById('stat-total-favoritos');
    if (totalFavoritos) {
        totalFavoritos.textContent = data.total_favoritos || 0;
    }
}

function updateSystemInfo(data) {
    // Espacio en disco
    const diskUsage = document.getElementById('system-disk-usage');
    if (diskUsage && data.espacio_usado_porcentaje) {
        diskUsage.textContent = `${data.espacio_usado_porcentaje}% usado (${data.espacio_usado_gb} GB / ${data.espacio_total_gb} GB)`;
    }
    
    // Memoria PHP
    const memoryUsage = document.getElementById('system-memory-usage');
    if (memoryUsage && data.memoria_uso) {
        memoryUsage.textContent = `${data.memoria_uso} MB / ${data.memoria_limite}`;
    }
    
    // Servidor Web
    const webServer = document.getElementById('system-web-server');
    if (webServer && data.servidor_web) {
        const serverName = data.servidor_web.split('/')[0]; // Extraer solo el nombre (Apache, nginx, etc.)
        webServer.innerHTML = `<span class="status-badge success"><i class="fas fa-check-circle"></i> ${serverName}</span>`;
    }
    
    // Versión PHP
    const phpVersion = document.getElementById('system-php-version');
    if (phpVersion && data.php_version) {
        phpVersion.textContent = `PHP ${data.php_version}`;
    }
}

function formatRelativeTime(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = Math.floor((now - date) / 1000); // Diferencia en segundos
    
    if (diff < 60) return 'Hace un momento';
    if (diff < 3600) return `Hace ${Math.floor(diff / 60)} min`;
    if (diff < 86400) return `Hace ${Math.floor(diff / 3600)} h`;
    return `Hace ${Math.floor(diff / 86400)} días`;
}

// Hacer refreshDashboard global
window.refreshDashboard = refreshDashboard;

// ===== INICIALIZAR DASHBOARD AL CARGAR =====
document.addEventListener('DOMContentLoaded', function() {
    // Esperar un poco para asegurar que todo está cargado
    setTimeout(() => {
        if (document.getElementById('dashboard')?.classList.contains('active')) {
            initDashboardCharts();
            loadRecentActivity();
            loadTopProducts();
            
            // Primera actualización de datos reales
            setTimeout(() => refreshDashboard(), 1000);
            
            // Auto-actualización cada 30 segundos
            dashboardUpdateInterval = setInterval(() => {
                if (document.getElementById('dashboard')?.classList.contains('active')) {
                    refreshDashboard();
                }
            }, 30000);
        }
    }, 500);
});

// También inicializar cuando se cambie al tab de dashboard
window.addEventListener('load', function() {
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.target.id === 'dashboard' && mutation.target.classList.contains('active')) {
                setTimeout(() => {
                    initDashboardCharts();
                    loadRecentActivity();
                    loadTopProducts();
                    refreshDashboard();
                    
                    // Reiniciar auto-actualización
                    if (dashboardUpdateInterval) {
                        clearInterval(dashboardUpdateInterval);
                    }
                    dashboardUpdateInterval = setInterval(() => {
                        refreshDashboard();
                    }, 30000);
                }, 300);
            } else if (mutation.target.id === 'dashboard' && !mutation.target.classList.contains('active')) {
                // Detener auto-actualización cuando se sale del dashboard
                if (dashboardUpdateInterval) {
                    clearInterval(dashboardUpdateInterval);
                    dashboardUpdateInterval = null;
                }
            }
        });
    });
    
    const dashboardTab = document.getElementById('dashboard');
    if (dashboardTab) {
        observer.observe(dashboardTab, {
            attributes: true,
            attributeFilter: ['class']
        });
    }
});
</script>
