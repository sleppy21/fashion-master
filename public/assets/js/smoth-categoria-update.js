/**
 * SISTEMA DE ACTUALIZACIÓN SUAVE DE TABLA/GRID DE CATEGOR\u00cdAS
 * Actualización en tiempo real sin glitches ni bugs visuales
 */

class CATEGOR\u00cdASmoothTableUpdater {
    constructor() {
        this.isUpdating = false;
        this.updateQueue = [];
        this.animationDuration = 300; // ms
    }

    /**
     * Actualizar una sola Categoria en la tabla/grid sin recargar todo
     */
    async updateSingleCategoria(CategoriaId, updatedData = null) {

        
        try {
            // Si no se proporciona data, obtenerla del servidor
            if (!updatedData) {
                const response = await fetch(`${window.CONFIG.apiUrl}?action=get&id=${CategoriaId}`);
                const result = await response.json();
                
                if (!result.success) {
                    throw new Error('Error al obtener datos de la Categoria');
                }
                
                updatedData = result.Categoria;
            }
            
            // Detectar vista actual
            const currentView = this.getCurrentView();
            
            if (currentView === 'grid') {
                await this.updateCategoriaInGrid(CategoriaId, updatedData);
            } else {
                await this.updateCategoriaInTable(CategoriaId, updatedData);
            }
            
            
        } catch (error) {
            console.error('❌ Error actualizando Categoria:', error);
            // Fallback: recargar toda la tabla
            if (typeof window.loadCATEGOR\u00cdAS === 'function') {
                window.loadCATEGOR\u00cdAS();
            }
        }
    }

    /**
     * Actualizar Categoria en la vista tabla
     */
    async updateCategoriaInTable(CategoriaId, CategoriaData) {

        
        const row = document.querySelector(`#CATEGOR\u00cdAS-table-body tr[data-Categoria-id="${CategoriaId}"]`);
        
        if (!row) {
            const allRows = document.querySelectorAll('#CATEGOR\u00cdAS-table-body tr');
            allRows.forEach((r, idx) => {
            });
            
            if (typeof window.loadCATEGOR\u00cdAS === 'function') {
                window.loadCATEGOR\u00cdAS();
            }
            return;
        }
        
        
        // Crear nueva fila HTML
        const newRowHTML = this.createTableRow(CategoriaData);
        
        const tempContainer = document.createElement('tbody');
        tempContainer.innerHTML = newRowHTML;
        const newRow = tempContainer.firstElementChild;
        
        
        // Reemplazar contenido directamente sin animaciones
        row.innerHTML = newRow.innerHTML;
        row.setAttribute('data-Categoria-id', CategoriaId);
        
        
        // Efecto de destaque suave
        this.highlightRow(row);
    }

    /**
     * Actualizar Categoria en la vista grid
     */
    async updateCategoriaInGrid(CategoriaId, CategoriaData) {
        const card = document.querySelector(`.Categoria-card[data-Categoria-id="${CategoriaId}"]`);
        
        if (!card) {
            if (typeof window.loadCATEGOR\u00cdAS === 'function') {
                window.loadCATEGOR\u00cdAS();
            }
            return;
        }
        
        // Crear nueva card HTML
        const newCardHTML = this.createGridCard(CategoriaData);
        const tempContainer = document.createElement('div');
        tempContainer.innerHTML = newCardHTML;
        const newCard = tempContainer.firstElementChild;
        
        // Reemplazar contenido directamente sin animaciones
        card.innerHTML = newCard.innerHTML;
        card.className = newCard.className;
        card.setAttribute('data-Categoria-id', CategoriaId);
        
        // Efecto de destaque suave
        this.highlightCard(card);
    }

    /**
     * Agregar nuevo Categoriao a la tabla/grid
     */
    async addNewCategoria(CategoriaData) {
        const currentView = this.getCurrentView();
        
        if (currentView === 'grid') {
            await this.addCategoriaToGrid(CategoriaData);
        } else {
            await this.addCategoriaToTable(CategoriaData);
        }
    }

    /**
     * Agregar Categoriao a la tabla
     */
    async addCategoriaToTable(CategoriaData) {
        const tbody = document.getElementById('Categoriaos-table-body');
        
        if (!tbody) return;
        
        const newRowHTML = this.createTableRow(CategoriaData);
        const tempContainer = document.createElement('tbody');
        tempContainer.innerHTML = newRowHTML;
        const newRow = tempContainer.firstElementChild;
        
        // Insertar al inicio con animación
        newRow.style.opacity = '0';
        newRow.style.transform = 'translateY(-20px)';
        newRow.style.transition = `all ${this.animationDuration}ms ease-out`;
        
        tbody.insertBefore(newRow, tbody.firstChild);
        
        requestAnimationFrame(() => {
            newRow.style.opacity = '1';
            newRow.style.transform = 'translateY(0)';
        });
        
        // Efecto de destaque
        setTimeout(() => {
            this.highlightRow(newRow);
            newRow.style.transition = '';
            newRow.style.transform = '';
        }, this.animationDuration);
    }

    /**
     * Agregar Categoriao al grid
     */
    async addCategoriaToGrid(CategoriaData) {
        const grid = document.querySelector('.CATEGOR\u00cdAS-grid');
        
        if (!grid) return;
        
        const newCardHTML = this.createGridCard(CategoriaData);
        const tempContainer = document.createElement('div');
        tempContainer.innerHTML = newCardHTML;
        const newCard = tempContainer.firstElementChild;
        
        // Insertar al inicio con animación
        newCard.style.opacity = '0';
        newCard.style.transform = 'scale(0.8)';
        newCard.style.transition = `all ${this.animationDuration}ms ease-out`;
        
        grid.insertBefore(newCard, grid.firstChild);
        
        requestAnimationFrame(() => {
            newCard.style.opacity = '1';
            newCard.style.transform = 'scale(1)';
        });
        
        // Efecto de destaque
        setTimeout(() => {
            this.highlightCard(newCard);
            newCard.style.transition = '';
            newCard.style.transform = '';
        }, this.animationDuration);
    }

    /**
     * Eliminar Categoriao de la tabla/grid
     */
    async removeCategoria(CategoriaId) {
        const currentView = this.getCurrentView();
        const element = currentView === 'grid' 
            ? document.querySelector(`.Categoria-card[data-Categoria-id="${CategoriaId}"]`)
            : document.querySelector(`#Categoriaos-table-body tr[data-Categoria-id="${CategoriaId}"]`);
        
        if (!element) return;
        
        // Animación de salida
        element.style.transition = `all ${this.animationDuration}ms ease-out`;
        element.style.opacity = '0';
        element.style.transform = currentView === 'grid' ? 'scale(0.8)' : 'translateX(-100%)';
        
        await this.wait(this.animationDuration);
        
        element.remove();
    }

    /**
     * Crear HTML de fila de tabla
     * IMPORTANTE: Este HTML debe coincidir EXACTAMENTE con el generado en displayCATEGOR\u00cdAS()
     */
    createTableRow(Categoria) {
        // Imagen
        const imageUrl = this.getCategoriaImageUrl(Categoria, true);
        const fallbackImage = '/fashion-master/public/assets/img/default-product.jpg';
        
        // Truncar descripción a 100 caracteres
        const descripcion = Categoria.descripcion_Categoria 
            ? (Categoria.descripcion_Categoria.length > 100 
                ? Categoria.descripcion_Categoria.substring(0, 100) + '...' 
                : Categoria.descripcion_Categoria)
            : 'Sin descripción';
        
        // Fecha
        const fecha = Categoria.fecha_creacion_formato || Categoria.fecha_creacion_Categoria || 'N/A';
        
        // HTML con orden: ID, Imagen, Código, Nombre, Descripción, Estado, Fecha, Acciones
        return `
        <tr oncontextmenu="return false;" ondblclick="editCategoria(${Categoria.id_Categoria})" style="cursor: pointer;" data-Categoria-id="${Categoria.id_Categoria}">
            <td><strong>${Categoria.id_Categoria}</strong></td>
            <td>
                <div class="product-image-cell" ondblclick="event.stopPropagation(); showImageFullSize('${imageUrl}', '${(Categoria.nombre_Categoria || '').replace(/'/g, "\\'")}')"; style="cursor: zoom-in; width: 50px; height: 50px; border-radius: 8px; overflow: hidden;">
                    <img src="${imageUrl}" 
                         alt="${Categoria.nombre_Categoria || 'Categoria'}" 
                         class="product-thumbnail"
                         style="width: 100%; height: 100%; object-fit: contain; border-radius: 8px;"
                         onerror="this.src='${fallbackImage}'; this.onerror=null;">
                </div>
            </td>
            <td>
                <code>${Categoria.codigo_Categoria || 'N/A'}</code>
            </td>
            <td>
                <div class="Categoria-info">
                    <strong>${Categoria.nombre_Categoria || 'Sin nombre'}</strong>
                </div>
            </td>
            <td>
                <div class="descripcion-truncate" title="${(Categoria.descripcion_Categoria || 'Sin descripción').replace(/"/g, '&quot;')}">
                    ${descripcion}
                </div>
            </td>
            <td>
                <span class="status-badge ${Categoria.estado_Categoria === 'activo' ? 'status-active' : 'status-inactive'}">
                    ${Categoria.estado_Categoria === 'activo' ? 'Activo' : 'Inactivo'}
                </span>
            </td>
            <td>${fecha}</td>
            <td>
                <div class="action-buttons">
                    <button class="btn-action btn-menu" onclick="event.stopPropagation(); showActionMenu(${Categoria.id_Categoria}, '${(Categoria.nombre_Categoria || '').replace(/'/g, "\\'")}', 0, '${Categoria.estado_Categoria}', event)" title="Acciones">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                </div>
            </td>
        </tr>`;
    }

    /**
     * Helper para obtener URL de imagen de Categoria
     */
    getCategoriaImageUrl(Categoria, forceCacheBust = false) {
        const timestamp = forceCacheBust ? '?t=' + new Date().getTime() : '';
        if (Categoria.url_imagen_Categoria && Categoria.url_imagen_Categoria !== 'NULL') {
            return Categoria.url_imagen_Categoria + timestamp;
        } else if (Categoria.imagen_Categoria && Categoria.imagen_Categoria !== 'NULL') {
            // Si es default-product (jpg o png), usar la ruta completa con .jpg
            if (Categoria.imagen_Categoria === 'default-product.jpg' || Categoria.imagen_Categoria === 'default-product.png') {
                return '/fashion-master/public/assets/img/default-product.jpg' + timestamp;
            }
            return '/fashion-master/public/assets/img/products/' + Categoria.imagen_Categoria + timestamp;
        }
        return '/fashion-master/public/assets/img/default-product.jpg';
    }

    /**
     * Crear HTML de card del grid
     * IMPORTANTE: Este HTML debe coincidir EXACTAMENTE con el generado en displayCATEGOR\u00cdASGrid()
     */
    createGridCard(Categoria) {
        const imageUrl = this.getCategoriaImageUrl(Categoria, true);
        const fallbackImage = '/fashion-master/public/assets/img/default-product.jpg';
        
        return `
            <div class="Categoria-card" ondblclick="editCategoria(${Categoria.id_Categoria})" style="cursor: pointer;" data-Categoria-id="${Categoria.id_Categoria}">
                <div class="Categoria-card-header">
                    <div class="Categoria-card-image" ondblclick="event.stopPropagation(); showImageFullSize('${imageUrl}', '${Categoria.nombre_Categoria || 'Categoria'}')\" style="cursor: zoom-in; border-radius: 12px; overflow: hidden;">
                        <img src="${imageUrl}" alt="${Categoria.nombre_Categoria || 'Categoria'}" style="border-radius: 12px;" onerror="this.src='${fallbackImage}'; this.onerror=null;">
                    </div>
                    <h3 class="Categoria-card-title">${Categoria.nombre_Categoria || 'Sin nombre'}</h3>
                    <span class="Categoria-card-status ${Categoria.estado_Categoria === 'activo' ? 'active' : 'inactive'}">
                        ${Categoria.estado_Categoria === 'activo' ? 'Activo' : 'Inactivo'}
                    </span>
                </div>
                
                <div class="Categoria-card-body">
                    ${Categoria.codigo_Categoria ? `<div class="Categoria-card-sku">Código: ${Categoria.codigo_Categoria}</div>` : ''}
                    
                    ${Categoria.descripcion_Categoria ? `
                    <div class="Categoria-card-description">
                        <i class="fas fa-align-left"></i> ${Categoria.descripcion_Categoria.length > 80 ? Categoria.descripcion_Categoria.substring(0, 80) + '...' : Categoria.descripcion_Categoria}
                    </div>
                    ` : ''}
                    
                    <div class="Categoria-card-date">
                        <i class="fas fa-calendar"></i> ${this.formatearFecha(Categoria.fecha_creacion_Categoria)}
                    </div>
                </div>
                
                <div class="Categoria-card-actions">
                    <button class="Categoria-card-btn btn-view" onclick="event.stopPropagation(); viewCategoria(${Categoria.id_Categoria})" title="Ver Categoria" style="background-color: #1a73e8 !important; color: white !important; border: none !important; box-shadow: 0 4px 8px rgba(26, 115, 232, 0.3) !important;">
                        <i class="fas fa-eye" style="color: white !important;"></i>
                    </button>
                    <button class="Categoria-card-btn btn-edit" onclick="event.stopPropagation(); editCategoria(${Categoria.id_Categoria})" title="Editar Categoria" style="background-color: #34a853 !important; color: white !important; border: none !important; box-shadow: 0 4px 8px rgba(52, 168, 83, 0.3) !important;">
                        <i class="fas fa-edit" style="color: white !important;"></i>
                    </button>
                    <button class="Categoria-card-btn ${Categoria.estado_Categoria === 'activo' ? 'btn-deactivate' : 'btn-activate'}" 
                            onclick="event.stopPropagation(); changeCategoriaEstado(${Categoria.id_Categoria})" 
                            title="${Categoria.estado_Categoria === 'activo' ? 'Desactivar' : 'Activar'} Categoria"
                            style="background-color: #6f42c1 !important; color: white !important; border: none !important;">
                        <i class="fas fa-${Categoria.estado_Categoria === 'activo' ? 'power-off' : 'toggle-on'}" style="color: white !important;"></i>
                    </button>
                    <button class="Categoria-card-btn btn-delete" onclick="event.stopPropagation(); deleteCategoria(${Categoria.id_Categoria}, '${(Categoria.nombre_Categoria || 'Categoria').replace(/'/g, "\\'")}')\" title="Eliminar Categoria" style="background-color: #f44336 !important; color: white !important; border: none !important; box-shadow: 0 4px 8px rgba(244, 67, 54, 0.3) !important;">
                        <i class="fas fa-trash" style="color: white !important;"></i>
                    </button>
                </div>
            </div>
        `;
    }
    
    /**
     * Helper para formatear fecha
     */
    formatearFecha(fecha) {
        if (!fecha) return 'N/A';
        if (typeof formatearFecha === 'function') {
            return formatearFecha(fecha);
        }
        return fecha;
    }

    /**
     * Efecto de destaque en fila (desactivado)
     */
    highlightRow(row) {
        // Sin efectos visuales
    }

    /**
     * Efecto de destaque en card (desactivado)
     */
    highlightCard(card) {
        // Sin efectos visuales
    }

    /**
     * Obtener vista actual
     */
    getCurrentView() {
        const activeBtn = document.querySelector('.view-btn.active');
        return activeBtn ? activeBtn.dataset.view : 'table';
    }

    /**
     * Helper: Esperar tiempo específico
     */
    wait(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
}

// Crear instancia global para CATEGOR\u00cdAS
window.CATEGOR\u00cdASmoothTableUpdater = new CATEGOR\u00cdASmoothTableUpdater();

// Mantener compatibilidad con código existente que busca smoothTableUpdater
// Solo si no existe (para que no sobrescriba el de productos)
if (!window.smoothTableUpdater) {
    window.smoothTableUpdater = window.CATEGOR\u00cdASmoothTableUpdater;
}

// Exportar para uso en otros módulos
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CATEGOR\u00cdASmoothTableUpdater;
}



