/**
 * SISTEMA DE ACTUALIZACIÓN SUAVE DE TABLA/GRID DE MARCAS
 * Actualización en tiempo real sin glitches ni bugs visuales
 */

// ⚠️ PREVENIR REDECLARACIÓN
if (typeof MarcaSmoothTableUpdater === 'undefined') {
    console.log('✅ Declarando MarcaSmoothTableUpdater por primera vez');

class MarcaSmoothTableUpdater {
    constructor() {
        this.isUpdating = false;
        this.updateQueue = [];
        this.animationDuration = 300; // ms
    }

    /**
     * Actualizar una sola marca en la tabla/grid sin recargar todo
     */
    async updateSingleMarca(marcaId, updatedData = null) {
        
        try {
            // Si no se proporciona data, obtenerla del servidor
            if (!updatedData) {
                const response = await fetch(`${window.CONFIG.apiUrl}?action=get&id=${marcaId}`);
                const result = await response.json();
                
                if (!result.success) {
                    throw new Error('Error al obtener datos de la marca');
                }
                
                updatedData = result.marca;
            }
            
            // Detectar vista actual
            const currentView = this.getCurrentView();
            
            if (currentView === 'grid') {
                await this.updateMarcaInGrid(marcaId, updatedData);
            } else {
                await this.updateMarcaInTable(marcaId, updatedData);
            }
            
            
        } catch (error) {
            console.error('❌ Error actualizando marca:', error);
            // Fallback: recargar toda la tabla
            if (typeof window.loadMarcas === 'function') {
                window.loadMarcas();
            }
        }
    }

    /**
     * Actualizar marca en la vista tabla
     */
    async updateMarcaInTable(marcaId, marcaData) {
        
        const row = document.querySelector(`#marcas-table-body tr[data-marca-id="${marcaId}"]`);
        
        if (!row) {
    
            const allRows = document.querySelectorAll('#marcas-table-body tr');
            allRows.forEach((r, idx) => {
            });
            
            if (typeof window.loadMarcas === 'function') {
                window.loadMarcas();
            }
            return;
        }
        
        
        // Crear nueva fila HTML
        const newRowHTML = this.createTableRow(marcaData);
        
        const tempContainer = document.createElement('tbody');
        tempContainer.innerHTML = newRowHTML;
        const newRow = tempContainer.firstElementChild;
        
        
        // Reemplazar contenido directamente sin animaciones
        row.innerHTML = newRow.innerHTML;
        row.setAttribute('data-marca-id', marcaId);
        
        
        // Efecto de destaque suave
        this.highlightRow(row);
    }

    /**
     * Actualizar marca en la vista grid
     */
    async updateMarcaInGrid(marcaId, marcaData) {
        const card = document.querySelector(`.marca-card[data-marca-id="${marcaId}"]`);
        
        if (!card) {
            if (typeof window.loadMarcas === 'function') {
                window.loadMarcas();
            }
            return;
        }
        
        // Crear nueva card HTML
        const newCardHTML = this.createGridCard(marcaData);
        const tempContainer = document.createElement('div');
        tempContainer.innerHTML = newCardHTML;
        const newCard = tempContainer.firstElementChild;
        
        // Reemplazar contenido directamente sin animaciones
        card.innerHTML = newCard.innerHTML;
        card.className = newCard.className;
        card.setAttribute('data-marca-id', marcaId);
        
        // Efecto de destaque suave
        this.highlightCard(card);
    }

    /**
     * Agregar nuevo Marcao a la tabla/grid
     */
    async addNewMarca(MarcaData) {
        const currentView = this.getCurrentView();
        
        if (currentView === 'grid') {
            await this.addMarcaToGrid(MarcaData);
        } else {
            await this.addMarcaToTable(MarcaData);
        }
    }

    /**
     * Agregar Marcao a la tabla
     */
    async addMarcaToTable(MarcaData) {
        const tbody = document.getElementById('marcas-table-body');
        
        if (!tbody) return;
        
        const newRowHTML = this.createTableRow(MarcaData);
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
     * Agregar Marcao al grid
     */
    async addMarcaToGrid(MarcaData) {
        const grid = document.querySelector('.Marcas-grid');
        
        if (!grid) return;
        
        const newCardHTML = this.createGridCard(MarcaData);
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
     * Eliminar Marcao de la tabla/grid
     */
    async removeMarca(MarcaId) {
        const currentView = this.getCurrentView();
        const element = currentView === 'grid' 
            ? document.querySelector(`.Marca-card[data-Marca-id="${MarcaId}"]`)
            : document.querySelector(`#Marcaos-table-body tr[data-Marca-id="${MarcaId}"]`);
        
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
     * IMPORTANTE: Este HTML debe coincidir EXACTAMENTE con el generado en displayMarcas()
     */
    createTableRow(marca) {
        // Imagen
        const imageUrl = this.getMarcaImageUrl(marca, true);
        const fallbackImage = '/fashion-master/public/assets/img/default-product.jpg';
        
        // Truncar descripción a 100 caracteres
        const descripcion = marca.descripcion_marca 
            ? (marca.descripcion_marca.length > 100 
                ? marca.descripcion_marca.substring(0, 100) + '...' 
                : marca.descripcion_marca)
            : 'Sin descripción';
        
        // Fecha
        const fecha = marca.fecha_creacion_formato || marca.fecha_creacion_marca || 'N/A';
        
        // HTML con orden: ID, Imagen, Código, Nombre, Descripción, Estado, Fecha, Acciones
        return `
        <tr oncontextmenu="return false;" ondblclick="editMarca(${marca.id_marca})" style="cursor: pointer;" data-marca-id="${marca.id_marca}">
            <td><strong>${marca.id_marca}</strong></td>
            <td>
                <div class="product-image-cell" ondblclick="event.stopPropagation(); showImageFullSize('${imageUrl}', '${(marca.nombre_marca || '').replace(/'/g, "\\'")}')"; style="cursor: zoom-in; width: 50px; height: 50px; border-radius: 8px; overflow: hidden;">
                    <img src="${imageUrl}" 
                         alt="${marca.nombre_marca || 'Marca'}" 
                         class="product-thumbnail"
                         style="width: 100%; height: 100%; object-fit: contain; border-radius: 8px;"
                         onerror="this.src='${fallbackImage}'; this.onerror=null;">
                </div>
            </td>
            <td>
                <code>${marca.codigo_marca || 'N/A'}</code>
            </td>
            <td>
                <div class="marca-info">
                    <strong>${marca.nombre_marca || 'Sin nombre'}</strong>
                </div>
            </td>
            <td>
                <div class="descripcion-truncate" title="${(marca.descripcion_marca || 'Sin descripción').replace(/"/g, '&quot;')}">
                    ${descripcion}
                </div>
            </td>
            <td>
                <span class="status-badge ${marca.estado_marca === 'activo' ? 'status-active' : 'status-inactive'}">
                    ${marca.estado_marca === 'activo' ? 'Activo' : 'Inactivo'}
                </span>
            </td>
            <td>${fecha}</td>
            <td>
                <div class="action-buttons">
                    <button class="btn-action btn-menu" onclick="event.stopPropagation(); showActionMenu(${marca.id_marca}, '${(marca.nombre_marca || '').replace(/'/g, "\\'")}', 0, '${marca.estado_marca}', event)" title="Acciones">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                </div>
            </td>
        </tr>`;
    }

    /**
     * Helper para obtener URL de imagen de marca
     */
    getMarcaImageUrl(marca, forceCacheBust = false) {
        const timestamp = forceCacheBust ? '?t=' + new Date().getTime() : '';
        if (marca.url_imagen_marca && marca.url_imagen_marca !== 'NULL') {
            return marca.url_imagen_marca + timestamp;
        } else if (marca.imagen_marca && marca.imagen_marca !== 'NULL') {
            // Si es default-product (jpg o png), usar la ruta completa con .jpg
            if (marca.imagen_marca === 'default-product.jpg' || marca.imagen_marca === 'default-product.png') {
                return '/fashion-master/public/assets/img/default-product.jpg' + timestamp;
            }
            return '/fashion-master/public/assets/img/products/' + marca.imagen_marca + timestamp;
        }
        return '/fashion-master/public/assets/img/default-product.jpg';
    }

    /**
     * Crear HTML de card del grid
     * IMPORTANTE: Este HTML debe coincidir EXACTAMENTE con el generado en displayMarcasGrid()
     */
    createGridCard(marca) {
        const imageUrl = this.getMarcaImageUrl(marca, true);
        const fallbackImage = '/fashion-master/public/assets/img/default-product.jpg';
        
        return `
            <div class="marca-card" ondblclick="editMarca(${marca.id_marca})" style="cursor: pointer;" data-marca-id="${marca.id_marca}">
                <div class="marca-card-header">
                    <div class="marca-card-image" ondblclick="event.stopPropagation(); showImageFullSize('${imageUrl}', '${marca.nombre_marca || 'Marca'}')\" style="cursor: zoom-in; border-radius: 12px; overflow: hidden;">
                        <img src="${imageUrl}" alt="${marca.nombre_marca || 'Marca'}" style="border-radius: 12px;" onerror="this.src='${fallbackImage}'; this.onerror=null;">
                    </div>
                    <h3 class="marca-card-title">${marca.nombre_marca || 'Sin nombre'}</h3>
                    <span class="marca-card-status ${marca.estado_marca === 'activo' ? 'active' : 'inactive'}">
                        ${marca.estado_marca === 'activo' ? 'Activo' : 'Inactivo'}
                    </span>
                </div>
                
                <div class="marca-card-body">
                    ${marca.codigo_marca ? `<div class="marca-card-sku">Código: ${marca.codigo_marca}</div>` : ''}
                    
                    ${marca.descripcion_marca ? `
                    <div class="marca-card-description">
                        <i class="fas fa-align-left"></i> ${marca.descripcion_marca.length > 80 ? marca.descripcion_marca.substring(0, 80) + '...' : marca.descripcion_marca}
                    </div>
                    ` : ''}
                    
                    <div class="marca-card-date">
                        <i class="fas fa-calendar"></i> ${this.formatearFecha(marca.fecha_creacion_marca)}
                    </div>
                </div>
                
                <div class="marca-card-actions">
                    <button class="marca-card-btn btn-view" onclick="event.stopPropagation(); viewMarca(${marca.id_marca})" title="Ver marca" style="background-color: #1a73e8 !important; color: white !important; border: none !important; box-shadow: 0 4px 8px rgba(26, 115, 232, 0.3) !important;">
                        <i class="fas fa-eye" style="color: white !important;"></i>
                    </button>
                    <button class="marca-card-btn btn-edit" onclick="event.stopPropagation(); editMarca(${marca.id_marca})" title="Editar marca" style="background-color: #34a853 !important; color: white !important; border: none !important; box-shadow: 0 4px 8px rgba(52, 168, 83, 0.3) !important;">
                        <i class="fas fa-edit" style="color: white !important;"></i>
                    </button>
                    <button class="marca-card-btn ${marca.estado_marca === 'activo' ? 'btn-deactivate' : 'btn-activate'}" 
                            onclick="event.stopPropagation(); changeMarcaEstado(${marca.id_marca})" 
                            title="${marca.estado_marca === 'activo' ? 'Desactivar' : 'Activar'} marca"
                            style="background-color: #6f42c1 !important; color: white !important; border: none !important;">
                        <i class="fas fa-${marca.estado_marca === 'activo' ? 'power-off' : 'toggle-on'}" style="color: white !important;"></i>
                    </button>
                    <button class="marca-card-btn btn-delete" onclick="event.stopPropagation(); deleteMarca(${marca.id_marca}, '${(marca.nombre_marca || 'marca').replace(/'/g, "\\'")}')\" title="Eliminar marca" style="background-color: #f44336 !important; color: white !important; border: none !important; box-shadow: 0 4px 8px rgba(244, 67, 54, 0.3) !important;">
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

// Crear instancia global para marcas
window.marcaSmoothTableUpdater = new MarcaSmoothTableUpdater();

// Mantener compatibilidad con código existente que busca smoothTableUpdater
// Solo si no existe (para que no sobrescriba el de productos)
if (!window.smoothTableUpdater) {
    window.smoothTableUpdater = window.marcaSmoothTableUpdater;
}

// Exportar para uso en otros módulos
if (typeof module !== 'undefined' && module.exports) {
    module.exports = MarcaSmoothTableUpdater;
}

} else {
    console.warn('⚠️ MarcaSmoothTableUpdater ya existe - saltando redeclaración');
    // Si ya existe, solo asegurar que la instancia global esté creada
    if (!window.marcaSmoothTableUpdater) {
        window.marcaSmoothTableUpdater = new MarcaSmoothTableUpdater();
    }
}

