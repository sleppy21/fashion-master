<!-- ================================================= -->
<!-- DASHBOARD MODERNO - FASHION STORE ADMIN PANEL -->
<!-- Diseño: Oscuro con acentos azules + MÁS INFORMACIÓN -->
<!-- Gráficas: Chart.js v3 -->
<!-- ================================================= -->

<style>
/* ESTILOS DEL DASHBOARD - IMPORTANTE: Dentro de <style> para evitar conflictos */
.dashboard-modern-wrapper {
    padding: 0;
    background: transparent;
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
    overflow-x: hidden;
    min-height: 100vh;
    padding-bottom: 80px;
}

/* Header del Dashboard */
.dashboard-header-new {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding: 24px;
    background: #0f172a;
    border-radius: 12px;
    border: 1px solid #1e293b;
}

.dashboard-header-new .header-left h1 {
    color: #f8fafc;
    font-size: 28px;
    font-weight: 700;
    margin: 0 0 8px 0;
    display: flex;
    align-items: center;
    gap: 12px;
}

.dashboard-header-new .header-left h1 i {
    color: #2463eb;
}

.dashboard-header-new .subtitle {
    color: #94a3b8;
    font-size: 14px;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 12px;
}

.dashboard-header-new .last-update {
    color: #64748b;
    font-size: 12px;
}

.refresh-btn {
    background: linear-gradient(135deg, #2463eb 0%, #1e40af 100%);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.refresh-btn:hover {
    background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
    box-shadow: 0 0 12px rgba(36, 99, 235, 0.3), 
                0 0 24px rgba(36, 99, 235, 0.15);
    filter: brightness(1.08);
}

/* Grid de Estadísticas - FORZAR VISIBILIDAD */
.stats-grid-modern {
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(min(100%, 280px), 1fr));
    gap: 20px;
    margin-bottom: 30px;
    visibility: visible !important;
    opacity: 1 !important;
}

.stat-card-modern {
    background: #0f172a !important;
    border: 1px solid #1e293b;
    border-radius: 12px;
    padding: 20px;
    transition: all 0.3s ease;
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
}

.stat-card-modern:hover {
    border-color: #2463eb;
    box-shadow: 0 0 15px rgba(36, 99, 235, 0.2),
                0 0 30px rgba(36, 99, 235, 0.1);
    filter: brightness(1.03);
    background: #0f172a !important;
}

.stat-header {
    display: flex !important;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 16px;
    visibility: visible !important;
    opacity: 1 !important;
}

.stat-icon-wrapper {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex !important;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    visibility: visible !important;
    opacity: 1 !important;
}

.products-icon {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
    color: white !important;
}

.users-icon {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important;
    color: white !important;
}

.inventory-icon {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
    color: white !important;
}

.categories-icon {
    background: linear-gradient(135deg, #a855f7 0%, #9333ea 100%) !important;
    color: white !important;
}

/* Asegurar que todos los elementos internos sean visibles */
.stat-card-modern * {
    visibility: visible !important;
    opacity: 1 !important;
}

.quick-action-btn * {
    visibility: visible !important;
    opacity: 1 !important;
}

.stat-trend {
    display: flex !important;
    align-items: center;
    gap: 4px;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    visibility: visible !important;
    opacity: 1 !important;
}

.stat-trend.positive {
    background: rgba(16, 185, 129, 0.1) !important;
    color: #10b981 !important;
}

.stat-trend.neutral {
    background: rgba(100, 116, 139, 0.1) !important;
    color: #64748b !important;
}

.stat-body {
    padding-top: 8px;
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
}

.stat-value {
    color: #f8fafc !important;
    font-size: 32px;
    font-weight: 700;
    margin: 0 0 8px 0;
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
}

.stat-label {
    color: #94a3b8 !important;
    font-size: 14px;
    margin: 0 0 12px 0;
    font-weight: 500;
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
}

.stat-meta {
    display: flex !important;
    flex-direction: column;
    gap: 6px;
    visibility: visible !important;
    opacity: 1 !important;
}

.meta-item {
    color: #64748b !important;
    font-size: 12px;
    display: flex !important;
    align-items: center;
    gap: 6px;
    visibility: visible !important;
    opacity: 1 !important;
}

.meta-item i {
    color: #2463eb !important;
    font-size: 11px;
}

.meta-item.warning i {
    color: #f59e0b !important;
}

/* Grid de Gráficas - ASEGURAR VISIBILIDAD */
.charts-grid-modern {
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(min(100%, 350px), 1fr));
    gap: 20px;
    margin-bottom: 30px;
    visibility: visible !important;
    opacity: 1 !important;
}

.chart-card-modern {
    background: #0f172a !important;
    border: 1px solid #1e293b !important;
    border-radius: 12px;
    padding: 20px;
    transition: all 0.3s ease;
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
}

.chart-card-modern:hover {
    border-color: #2463eb !important;
    box-shadow: 0 0 12px rgba(36, 99, 235, 0.2),
                0 0 24px rgba(36, 99, 235, 0.1);
    filter: brightness(1.02);
}

.chart-card-modern.chart-wide {
    grid-column: 1 / -1;
    margin-top: -10px !important;
    margin-bottom: -10px !important;
}

.chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.chart-header h3 {
    color: #f8fafc;
    font-size: 16px;
    font-weight: 600;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.chart-header h3 i {
    color: #2463eb;
    font-size: 14px;
}

.chart-action-btn {
    background: transparent;
    border: none;
    color: #64748b;
    padding: 6px 10px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.chart-action-btn:hover {
    background: rgba(36, 99, 235, 0.1);
    color: #2463eb;
}

.chart-body {
    min-height: 250px;
    display: flex !important;
    align-items: center;
    justify-content: center;
    visibility: visible !important;
    opacity: 1 !important;
}

.chart-body canvas {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
}

.chart-footer {
    display: flex;
    gap: 16px;
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid #1e293b;
    justify-content: center;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: #94a3b8;
}

.legend-color {
    width: 12px;
    height: 12px;
    border-radius: 3px;
}

/* Grid de Información - FORZAR VISIBILIDAD */
.info-grid-modern {
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(min(100%, 350px), 1fr));
    gap: 20px;
    margin-bottom: 30px;
    visibility: visible !important;
    opacity: 1 !important;
}

.info-card-modern {
    background: #0f172a !important;
    border: 1px solid #1e293b;
    border-radius: 12px;
    padding: 20px;
    transition: all 0.3s ease;
    max-height: 400px;
    overflow-y: auto;
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
}

.info-card-modern:hover {
    border-color: #2463eb;
    box-shadow: 0 0 12px rgba(36, 99, 235, 0.15),
                0 0 24px rgba(36, 99, 235, 0.08);
    filter: brightness(1.02);
}

.info-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.info-header h3 {
    color: #f8fafc;
    font-size: 16px;
    font-weight: 600;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.info-header h3 i {
    color: #2463eb;
    font-size: 14px;
}

.info-link {
    color: #2463eb;
    font-size: 13px;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
}

.info-link:hover {
    color: #60a5fa;
    gap: 8px;
}

.info-item {
    display: flex !important;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: #1e293b !important;
    border-radius: 8px;
    margin-bottom: 10px;
    transition: all 0.2s ease;
    visibility: visible !important;
    opacity: 1 !important;
}

.info-item:hover {
    background: #334155;
    transform: translateX(4px);
}

.info-item-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(36, 99, 235, 0.1);
    color: #2463eb;
    font-size: 16px;
}

.info-item-content {
    flex: 1;
}

.info-item-title {
    color: #f8fafc;
    font-size: 14px;
    font-weight: 600;
    margin: 0 0 4px 0;
}

.info-item-subtitle {
    color: #64748b;
    font-size: 12px;
    margin: 0;
}

/* Acciones Rápidas - FORZAR VISIBILIDAD */
.quick-actions-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 16px;
    margin-bottom: 30px;
    visibility: visible !important;
    opacity: 1 !important;
}

.quick-action-btn {
    background: #0f172a !important;
    border: 1px solid #1e293b;
    border-radius: 12px;
    padding: 20px 16px;
    cursor: pointer;
    transition: all 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    display: flex !important;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    text-align: center;
    visibility: visible !important;
    opacity: 1 !important;
    position: relative;
    overflow: visible;
    transform-origin: center;
}

.quick-action-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: radial-gradient(circle at center, rgba(36, 99, 235, 0.08) 0%, transparent 70%);
    opacity: 0;
    transition: all 0.4s ease;
    z-index: 0;
    border-radius: 12px;
}

.quick-action-btn:hover::before {
    opacity: 1;
    transform: scale(1.5);
}

.quick-action-btn:hover {
    border-color: #3b82f6 !important;
    box-shadow: 0 0 15px rgba(36, 99, 235, 0.25), 
                0 0 30px rgba(36, 99, 235, 0.12) !important;
    filter: brightness(1.05) !important;
    background: linear-gradient(135deg, rgba(36, 99, 235, 0.04) 0%, rgba(30, 64, 175, 0.02) 100%) !important;
}

.quick-action-btn:active {
    transition: all 0.1s ease;
    filter: brightness(0.9) !important;
    box-shadow: 0 0 8px rgba(36, 99, 235, 0.2) !important;
}

.quick-action-btn > * {
    position: relative;
    z-index: 1;
}

.quick-action-icon {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    background: linear-gradient(135deg, #2463eb 0%, #1e40af 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    box-shadow: 0 4px 12px rgba(36, 99, 235, 0.3);
}

.quick-action-btn:hover .quick-action-icon {
    box-shadow: 0 0 12px rgba(36, 99, 235, 0.4),
                0 0 24px rgba(36, 99, 235, 0.2);
    filter: brightness(1.1);
}

.quick-action-label {
    color: #f8fafc;
    font-size: 14px;
    font-weight: 700;
    margin: 0;
    line-height: 1.3;
    transition: all 0.3s ease;
    letter-spacing: 0.3px;
}

.quick-action-btn:hover .quick-action-label {
    color: #ffffff;
}

.quick-action-subtitle {
    color: #64748b;
    font-size: 11px;
    margin: 0;
    line-height: 1.4;
    transition: all 0.3s ease;
    font-weight: 500;
}

.quick-action-btn:hover .quick-action-subtitle {
    color: #94a3b8;
}

/* Sistema de Estado - FORZAR VISIBILIDAD */
.system-stats {
    display: flex !important;
    flex-direction: column;
    gap: 16px;
    visibility: visible !important;
    opacity: 1 !important;
}

.system-stat-item {
    display: flex !important;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    background: #1e293b !important;
    border-radius: 8px;
    visibility: visible !important;
    opacity: 1 !important;
}

.system-stat-label {
    color: #94a3b8;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.system-stat-label i {
    color: #2463eb;
}

.system-stat-value {
    color: #f8fafc;
    font-weight: 600;
    font-size: 13px;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}

.status-badge.success {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.status-badge.warning {
    background: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
}

.status-badge.danger {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

/* Responsive Mejorado */
@media (max-width: 1200px) {
    .stats-grid-modern {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .quick-actions-grid {
        grid-template-columns: repeat(3, 1fr);
    }
    
    .info-grid-modern {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* PREVENIR OVERFLOW HORIZONTAL EN TODOS LOS BREAKPOINTS */
* {
    max-width: 100%;
}

.dashboard-modern-wrapper,
.dashboard-modern-wrapper * {
    box-sizing: border-box;
}

/* Mejorar rendimiento en móviles */
.stat-card-modern,
.chart-card-modern,
.info-card-modern,
.quick-action-btn {
    -webkit-transform: translateZ(0);
    transform: translateZ(0);
    will-change: transform;
}

/* Optimizar animaciones en móvil */
@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}

/* Touch feedback mejorado */
@media (hover: none) and (pointer: coarse) {
    .quick-action-btn:active,
    .refresh-btn:active,
    .chart-action-btn:active,
    .info-link:active {
        opacity: 0.7;
        transform: scale(0.98);
    }
    
    .quick-action-btn {
        -webkit-tap-highlight-color: rgba(36, 99, 235, 0.1);
    }
}

@media (max-width: 768px) {
    /* Dashboard wrapper - OPTIMIZADO MÓVIL */
    .dashboard-modern-wrapper {
        padding: 10px !important;
        width: 100% !important;
        max-width: 100vw !important;
        overflow-x: hidden !important;
        box-sizing: border-box !important;
        min-height: calc(100vh - 60px) !important;
        padding-bottom: 40px !important;
    }
    
    /* Asegurar que todos los hijos respeten el ancho */
    .dashboard-modern-wrapper > * {
        max-width: 100% !important;
        box-sizing: border-box !important;
    }
    
    /* Header responsive - COMPACTO Y LEGIBLE */
    .dashboard-header-new {
        flex-direction: column;
        gap: 12px;
        align-items: flex-start;
        padding: 14px !important;
        margin-bottom: 16px !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }
    
    .dashboard-header-new .header-left {
        width: 100%;
    }
    
    .dashboard-header-new .header-left h1 {
        font-size: 18px !important;
        gap: 8px;
    }
    
    .dashboard-header-new .header-left h1 i {
        font-size: 16px !important;
    }
    
    .dashboard-header-new .subtitle {
        font-size: 12px !important;
        flex-wrap: wrap;
        gap: 8px;
    }
    
    .dashboard-header-new .last-update {
        font-size: 11px !important;
        margin-top: 4px;
        display: block;
    }
    
    .refresh-btn {
        width: 100%;
        justify-content: center;
        padding: 10px 16px !important;
        font-size: 13px !important;
    }
    
    /* Stats grid - 2 COLUMNAS OPTIMIZADAS */
    .stats-grid-modern {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 12px !important;
        margin-bottom: 16px !important;
        width: 100% !important;
        max-width: 100% !important;
    }
    
    .stat-card-modern {
        padding: 14px !important;
        width: 100% !important;
        min-width: 0 !important;
        border-radius: 10px !important;
    }
    
    .stat-header {
        flex-direction: row !important;
        gap: 8px;
        align-items: center !important;
        justify-content: space-between;
        margin-bottom: 12px !important;
    }
    
    .stat-icon-wrapper {
        width: 38px !important;
        height: 38px !important;
        font-size: 16px !important;
        border-radius: 10px !important;
    }
    
    .stat-trend {
        font-size: 10px !important;
        padding: 3px 6px !important;
    }
    
    .stat-trend i {
        font-size: 9px !important;
    }
    
    .stat-value {
        font-size: 22px !important;
        margin-bottom: 5px !important;
        line-height: 1.2;
    }
    
    .stat-label {
        font-size: 11px !important;
        margin-bottom: 8px !important;
        line-height: 1.3;
    }
    
    .stat-meta {
        gap: 4px !important;
    }
    
    .meta-item {
        font-size: 10px !important;
        padding: 2px 0 !important;
        line-height: 1.4;
    }
    
    .meta-item i {
        font-size: 9px !important;
    }
    
    /* Charts grid - 1 COLUMNA PARA MEJOR VISUALIZACIÓN */
    .charts-grid-modern {
        grid-template-columns: 1fr !important;
        gap: 12px !important;
        margin-bottom: 16px !important;
        width: 100% !important;
        max-width: 100% !important;
    }
    
    .chart-card-modern {
        padding: 14px !important;
        width: 100% !important;
        min-width: 0 !important;
        border-radius: 10px !important;
    }
    
    /* La gráfica ancha no necesita span en 1 columna */
    .chart-card-modern.chart-wide {
        grid-column: 1 / -1 !important;
        margin-top: 0 !important;
        margin-bottom: 0 !important;
    }
    
    .chart-header {
        margin-bottom: 14px !important;
        flex-wrap: wrap;
    }
    
    .chart-header h3 {
        font-size: 13px !important;
        gap: 6px;
    }
    
    .chart-header h3 i {
        font-size: 12px !important;
    }
    
    .chart-action-btn {
        padding: 5px 8px !important;
        font-size: 11px !important;
    }
    
    .chart-body {
        min-height: 200px !important;
        padding: 8px 0 !important;
    }
    
    .chart-body canvas {
        max-height: 250px !important;
    }
    
    .chart-footer {
        margin-top: 12px !important;
        padding-top: 12px !important;
        gap: 10px !important;
        flex-wrap: wrap;
        justify-content: flex-start;
    }
    
    .legend-item {
        font-size: 11px !important;
        gap: 6px;
    }
    
    .legend-color {
        width: 12px !important;
        height: 12px !important;
    }
    
    /* Info grid - 1 COLUMNA OPTIMIZADA */
    .info-grid-modern {
        grid-template-columns: 1fr !important;
        gap: 12px !important;
        margin-bottom: 16px !important;
        width: 100% !important;
        max-width: 100% !important;
    }
    
    .info-card-modern {
        padding: 14px !important;
        max-height: 300px !important;
        width: 100% !important;
        min-width: 0 !important;
        border-radius: 10px !important;
    }
    
    /* Scrollbar personalizado para info cards */
    .info-card-modern::-webkit-scrollbar {
        width: 6px;
    }
    
    .info-card-modern::-webkit-scrollbar-track {
        background: rgba(30, 41, 59, 0.5);
        border-radius: 3px;
    }
    
    .info-card-modern::-webkit-scrollbar-thumb {
        background: #2463eb;
        border-radius: 3px;
    }
    
    .info-header {
        margin-bottom: 12px !important;
        flex-wrap: wrap;
    }
    
    .info-header h3 {
        font-size: 14px !important;
        gap: 6px;
    }
    
    .info-header h3 i {
        font-size: 12px !important;
    }
    
    .info-link {
        font-size: 11px !important;
        gap: 4px;
    }
    
    .info-item {
        padding: 10px !important;
        margin-bottom: 8px !important;
        border-radius: 8px !important;
    }
    
    .info-item-icon {
        width: 36px !important;
        height: 36px !important;
        font-size: 14px !important;
        border-radius: 8px !important;
    }
    
    .info-item-content {
        flex: 1;
        min-width: 0;
    }
    
    .info-item-title {
        font-size: 12px !important;
        margin-bottom: 3px !important;
        line-height: 1.3;
    }
    
    .info-item-subtitle {
        font-size: 11px !important;
        line-height: 1.3;
    }
    
    /* Quick actions - 3 COLUMNAS BALANCEADAS */
    .quick-actions-grid {
        grid-template-columns: repeat(3, 1fr) !important;
        gap: 10px !important;
        margin-bottom: 16px !important;
        width: 100% !important;
        max-width: 100% !important;
    }
    
    .quick-action-btn {
        padding: 12px 8px !important;
        min-height: 95px !important;
        width: 100% !important;
        min-width: 0 !important;
        border-radius: 10px !important;
    }
    
    .quick-action-icon {
        width: 36px !important;
        height: 36px !important;
        font-size: 16px !important;
        margin-bottom: 6px !important;
        border-radius: 10px !important;
    }
    
    .quick-action-label {
        font-size: 10px !important;
        line-height: 1.2 !important;
        margin: 5px 0 3px 0 !important;
        font-weight: 600 !important;
    }
    
    .quick-action-subtitle {
        font-size: 9px !important;
        line-height: 1.2 !important;
        opacity: 0.85;
    }
    
    /* System stats optimizados */
    .system-stats {
        gap: 10px !important;
    }
    
    .system-stat-item {
        padding: 10px !important;
        margin-bottom: 0 !important;
        border-radius: 8px !important;
        flex-wrap: wrap;
        gap: 8px;
    }
    
    .system-stat-label {
        font-size: 11px !important;
        gap: 6px;
        flex: 1;
        min-width: 0;
    }
    
    .system-stat-label i {
        font-size: 10px !important;
    }
    
    .system-stat-value {
        font-size: 11px !important;
        text-align: right;
    }
    
    .status-badge {
        padding: 4px 10px !important;
        font-size: 10px !important;
    }
    
    .status-badge i {
        font-size: 9px !important;
    }
    
    /* Section title responsive */
    .section-title {
        margin: 20px 0 12px 0 !important;
        padding: 0 4px;
    }
    
    .section-title h2 {
        font-size: 16px !important;
        margin-bottom: 4px !important;
        gap: 8px;
    }
    
    .section-title h2 i {
        font-size: 14px !important;
    }
    
    .section-title p {
        font-size: 11px !important;
        line-height: 1.4;
    }
    
    /* Botón de refresh optimizado */
    .info-refresh-btn {
        padding: 6px 8px !important;
        font-size: 12px !important;
    }
}

@media (max-width: 480px) {
    /* Extra pequeño - OPTIMIZACIÓN MÁXIMA */
    
    /* Dashboard wrapper ultra compacto pero espacioso */
    .dashboard-modern-wrapper {
        padding: 8px !important;
        min-height: calc(100vh - 50px) !important;
        padding-bottom: 30px !important;
    }
    
    /* Header ultra compacto */
    .dashboard-header-new {
        padding: 12px !important;
        margin-bottom: 12px !important;
        gap: 8px;
    }
    
    .dashboard-header-new .header-left h1 {
        font-size: 16px !important;
        gap: 6px;
    }
    
    .dashboard-header-new .header-left h1 i {
        font-size: 14px !important;
    }
    
    .dashboard-header-new .subtitle {
        font-size: 11px !important;
        gap: 6px;
    }
    
    .dashboard-header-new .last-update {
        font-size: 10px !important;
        margin-top: 2px;
    }
    
    .refresh-btn {
        padding: 8px 14px !important;
        font-size: 12px !important;
    }
    
    .refresh-btn i {
        font-size: 11px !important;
    }
    
    /* Stats en 2 columnas optimizadas */
    .stats-grid-modern {
        gap: 10px !important;
        margin-bottom: 14px !important;
    }
    
    .stat-card-modern {
        padding: 12px !important;
    }
    
    .stat-header {
        margin-bottom: 10px !important;
    }
    
    .stat-icon-wrapper {
        width: 34px !important;
        height: 34px !important;
        font-size: 14px !important;
    }
    
    .stat-trend {
        font-size: 9px !important;
        padding: 3px 5px !important;
    }
    
    .stat-value {
        font-size: 20px !important;
        margin-bottom: 4px !important;
    }
    
    .stat-label {
        font-size: 10px !important;
        margin-bottom: 6px !important;
    }
    
    .meta-item {
        font-size: 9px !important;
    }
    
    .meta-item i {
        font-size: 8px !important;
    }
    
    /* Charts - 1 columna para mejor visualización */
    .charts-grid-modern {
        gap: 10px !important;
        margin-bottom: 14px !important;
    }
    
    .chart-card-modern {
        padding: 12px !important;
    }
    
    .chart-header {
        margin-bottom: 12px !important;
    }
    
    .chart-header h3 {
        font-size: 12px !important;
    }
    
    .chart-header h3 i {
        font-size: 11px !important;
    }
    
    .chart-action-btn {
        font-size: 10px !important;
        padding: 4px 6px !important;
    }
    
    .chart-body {
        min-height: 180px !important;
        padding: 6px 0 !important;
    }
    
    .chart-body canvas {
        max-height: 220px !important;
    }
    
    .chart-footer {
        margin-top: 10px !important;
        padding-top: 10px !important;
        gap: 8px !important;
    }
    
    .legend-item {
        font-size: 10px !important;
    }
    
    .legend-color {
        width: 10px !important;
        height: 10px !important;
    }
    
    /* Quick actions en 3 columnas - MEJOR BALANCE */
    .quick-actions-grid {
        grid-template-columns: repeat(3, 1fr) !important;
        gap: 8px !important;
        margin-bottom: 14px !important;
    }
    
    .quick-action-btn {
        padding: 10px 6px !important;
        min-height: 88px !important;
    }
    
    /* Ocultar overlay de fondo en móviles pequeños */
    .quick-action-btn::before {
        display: none !important;
    }
    
    .quick-action-icon {
        width: 32px !important;
        height: 32px !important;
        font-size: 14px !important;
        border-radius: 9px !important;
        margin-bottom: 4px !important;
    }
    
    .quick-action-label {
        font-size: 9px !important;
        margin: 4px 0 2px 0 !important;
        line-height: 1.15 !important;
    }
    
    .quick-action-subtitle {
        font-size: 8px !important;
        line-height: 1.15 !important;
    }
    
    /* Info cards optimizadas */
    .info-grid-modern {
        gap: 10px !important;
        margin-bottom: 14px !important;
    }
    
    .info-card-modern {
        padding: 12px !important;
        max-height: 280px !important;
    }
    
    .info-header {
        margin-bottom: 10px !important;
    }
    
    .info-header h3 {
        font-size: 13px !important;
    }
    
    .info-header h3 i {
        font-size: 11px !important;
    }
    
    .info-link {
        font-size: 10px !important;
    }
    
    .info-item {
        padding: 8px !important;
        margin-bottom: 6px !important;
    }
    
    .info-item-icon {
        width: 32px !important;
        height: 32px !important;
        font-size: 12px !important;
    }
    
    .info-item-title {
        font-size: 11px !important;
    }
    
    .info-item-subtitle {
        font-size: 10px !important;
    }
    
    /* System stats compactos */
    .system-stat-item {
        padding: 8px !important;
        margin-bottom: 0 !important;
    }
    
    .system-stat-label {
        font-size: 10px !important;
    }
    
    .system-stat-label i {
        font-size: 9px !important;
    }
    
    .system-stat-value {
        font-size: 10px !important;
    }
    
    .status-badge {
        padding: 3px 8px !important;
        font-size: 9px !important;
    }
    
    .status-badge i {
        font-size: 8px !important;
    }
    
    /* Section titles compactos */
    .section-title {
        margin: 16px 0 10px 0 !important;
    }
    
    .section-title h2 {
        font-size: 14px !important;
        gap: 6px;
    }
    
    .section-title h2 i {
        font-size: 12px !important;
    }
    
    .section-title p {
        font-size: 10px !important;
    }
}

/* Landscape móvil - OPTIMIZADO */
@media (max-width: 896px) and (orientation: landscape) {
    .dashboard-modern-wrapper {
        padding: 8px 12px !important;
        padding-bottom: 40px !important;
    }
    
    .stats-grid-modern {
        grid-template-columns: repeat(4, 1fr) !important;
        gap: 10px !important;
    }
    
    .stat-card-modern {
        padding: 10px !important;
    }
    
    .stat-value {
        font-size: 18px !important;
    }
    
    .stat-label {
        font-size: 10px !important;
    }
    
    .quick-actions-grid {
        grid-template-columns: repeat(6, 1fr) !important;
        gap: 8px !important;
    }
    
    .quick-action-btn {
        padding: 10px 6px !important;
        min-height: 85px !important;
    }
    
    .quick-action-icon {
        width: 30px !important;
        height: 30px !important;
        font-size: 13px !important;
    }
    
    .quick-action-label {
        font-size: 9px !important;
    }
    
    .quick-action-subtitle {
        font-size: 8px !important;
    }
    
    .charts-grid-modern {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 10px !important;
    }
    
    .chart-body {
        min-height: 150px !important;
    }
    
    .chart-body canvas {
        max-height: 180px !important;
    }
    
    .info-grid-modern {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 10px !important;
    }
    
    .info-card-modern {
        max-height: 220px !important;
    }
}

/* Ajustes para tablets - OPTIMIZADO */
@media (min-width: 769px) and (max-width: 1024px) {
    .dashboard-modern-wrapper {
        padding: 16px !important;
        padding-bottom: 50px !important;
    }
    
    .stats-grid-modern {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 16px !important;
    }
    
    .stat-card-modern {
        padding: 18px !important;
    }
    
    .quick-actions-grid {
        grid-template-columns: repeat(4, 1fr) !important;
        gap: 12px !important;
    }
    
    .quick-action-btn {
        padding: 14px 10px !important;
        min-height: 100px !important;
    }
    
    .quick-action-icon {
        width: 40px !important;
        height: 40px !important;
        font-size: 18px !important;
    }
    
    .quick-action-label {
        font-size: 11px !important;
    }
    
    .quick-action-subtitle {
        font-size: 10px !important;
    }
    
    .charts-grid-modern {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 16px !important;
    }
    
    .info-grid-modern {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 16px !important;
    }
}

/* Media query para vista PC - Ajustar espaciado de chart-wide */
@media (min-width: 1024px) {
    .chart-card-modern.chart-wide {
        margin-top: -15px !important;
        margin-bottom: 0px !important;
    }
}
</style>

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
    
    console.log('✅ Dashboard actualizado');
}

// ===== INICIALIZAR GRÁFICAS CON CHART.JS =====
function initDashboardCharts() {
    // Verificar si Chart.js está cargado
    if (typeof Chart === 'undefined') {
        console.error('❌ Chart.js no está cargado');
        return;
    }
    
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
    
    console.log('✅ Gráficas del dashboard inicializadas');
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
        console.error('❌ Error al cargar actividad:', error);
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
        console.error('Error cargando top productos:', error);
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
    console.log('🔄 Actualizando dashboard...');
    
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
        console.log('📊 Datos recibidos:', data);
        
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
        
        console.log('✅ Dashboard actualizado correctamente');
        
    } catch (error) {
        console.error('❌ Error al actualizar dashboard:', error);
        
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
            console.log('🎨 Inicializando Dashboard Moderno...');
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
