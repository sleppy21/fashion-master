/**
 * ========================================
 * CUSTOM SELECT MODAL - Admin
 * Sistema de selects personalizados con modales
 * ========================================
 */

class CustomSelectModal {
    constructor() {
        this.activeModal = null;
        this.activeSelectId = null;
        this.options = [];
        this.selectedValue = null;
        this.searchTerm = '';
        
        // Configuración por defecto
        this.config = {
            showSearch: false,
            searchPlaceholder: 'Buscar...',
            emptyMessage: 'No se encontraron resultados',
            icons: {
                marca: 'fa-copyright',
                categoria: 'fa-tags',
                genero: 'fa-venus-mars',
                default: 'fa-list'
            }
        };
    }

    /**
     * Inicializar todos los selects personalizados
     */
    init() {
        // Buscar todos los selects que queremos convertir
        const selectsToConvert = [
            'id_marca',
            'id_categoria',
            'id_subcategoria', 
            'genero_producto'
        ];

        selectsToConvert.forEach(selectId => {
            const selectElement = document.getElementById(selectId);
            if (selectElement) {
                this.convertSelectToCustom(selectElement);
            }
        });

        // Event listener para cerrar al hacer click fuera
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('select-modal-overlay')) {
                this.closeModal();
            }
        });

        // Event listener para tecla ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.activeModal) {
                this.closeModal();
            }
        });
    }

    /**
     * Convertir un select normal en custom select con modal
     */
    convertSelectToCustom(selectElement) {
        const selectId = selectElement.id;
        const selectName = selectElement.getAttribute('name');
        const isRequired = selectElement.hasAttribute('required');
        
        // Obtener el label asociado
        const label = document.querySelector(`label[for="${selectId}"]`);
        const labelText = label ? label.textContent.replace('*', '').trim() : 'Seleccionar';

        // Crear wrapper
        const wrapper = document.createElement('div');
        wrapper.className = 'custom-select-wrapper';
        wrapper.setAttribute('data-select-id', selectId);

        // Crear trigger (botón que abre el modal)
        const trigger = document.createElement('div');
        trigger.className = 'custom-select-trigger empty';
        trigger.setAttribute('tabindex', '0');
        trigger.setAttribute('role', 'button');
        trigger.setAttribute('aria-haspopup', 'dialog');
        trigger.innerHTML = `
            <span class="custom-select-value">Seleccionar ${labelText.toLowerCase()}</span>
            <i class="fas fa-chevron-down custom-select-trigger-icon"></i>
        `;

        // Envolver el select original
        selectElement.parentNode.insertBefore(wrapper, selectElement);
        wrapper.appendChild(trigger);
        wrapper.appendChild(selectElement);

        // Event listeners
        trigger.addEventListener('click', () => this.openModal(selectId));
        trigger.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.openModal(selectId);
            }
        });

        // Actualizar trigger cuando cambie el select
        selectElement.addEventListener('change', () => {
            this.updateTrigger(selectId);
        });
    }

    /**
     * Abrir modal de selección
     */
    openModal(selectId) {
        const selectElement = document.getElementById(selectId);
        if (!selectElement) return;

        this.activeSelectId = selectId;
        this.selectedValue = selectElement.value;
        
        // Obtener opciones del select
        this.options = Array.from(selectElement.options)
            .filter(option => option.value !== '') // Excluir opción vacía
            .map(option => ({
                value: option.value,
                text: option.textContent,
                selected: option.value === this.selectedValue,
                disabled: option.disabled
            }));

        // Determinar tipo de select para el icono
        let selectType = 'default';
        if (selectId.includes('marca')) selectType = 'marca';
        else if (selectId.includes('categoria') || selectId.includes('subcategoria')) selectType = 'categoria';
        else if (selectId.includes('genero')) selectType = 'genero';

        // Obtener título del modal
        const label = document.querySelector(`label[for="${selectId}"]`);
        const title = label ? label.textContent.replace('*', '').trim() : 'Seleccionar';

        // Crear modal
        this.createModal(title, selectType);
        
        // Añadir clase active al trigger
        const trigger = document.querySelector(`[data-select-id="${selectId}"] .custom-select-trigger`);
        if (trigger) {
            trigger.classList.add('active');
        }
    }

    /**
     * Crear el HTML del modal
     */
    createModal(title, selectType) {
        // Eliminar modal anterior si existe
        const existingModal = document.getElementById('customSelectModal');
        if (existingModal) {
            existingModal.remove();
        }

        const iconClass = this.config.icons[selectType] || this.config.icons.default;
        const showSearch = this.options.length > 5;

        const modalHTML = `
            <div id="customSelectModal" class="select-modal-overlay">
                <div class="select-modal-container">
                    <!-- Header -->
                    <div class="select-modal-header">
                        <h3 class="select-modal-title">
                            <i class="fas ${iconClass}"></i>
                            ${title}
                        </h3>
                        <button type="button" class="select-modal-close" onclick="customSelectModal.closeModal()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    ${showSearch ? `
                    <!-- Búsqueda -->
                    <div class="select-modal-search">
                        <div class="select-modal-search-wrapper">
                            <i class="fas fa-search select-modal-search-icon"></i>
                            <input 
                                type="text" 
                                class="select-modal-search-input" 
                                placeholder="${this.config.searchPlaceholder}"
                                id="selectModalSearch"
                            >
                        </div>
                    </div>
                    ` : ''}

                    <!-- Body - Lista de opciones -->
                    <div class="select-modal-body">
                        <ul class="select-options-list" id="selectOptionsList">
                            ${this.renderOptions()}
                        </ul>
                    </div>
                </div>
            </div>
        `;

        // Insertar modal en el body
        document.body.insertAdjacentHTML('beforeend', modalHTML);

        // Obtener referencias
        this.activeModal = document.getElementById('customSelectModal');
        
        // Mostrar modal con animación
        setTimeout(() => {
            this.activeModal.classList.add('show');
        }, 10);

        // Event listeners
        if (showSearch) {
            const searchInput = document.getElementById('selectModalSearch');
            if (searchInput) {
                searchInput.addEventListener('input', (e) => this.handleSearch(e.target.value));
                // Auto focus en el input de búsqueda
                setTimeout(() => searchInput.focus(), 100);
            }
        }

        // Event listeners para las opciones
        this.attachOptionListeners();
    }

    /**
     * Renderizar opciones
     */
    renderOptions() {
        const filteredOptions = this.searchTerm 
            ? this.options.filter(opt => 
                opt.text.toLowerCase().includes(this.searchTerm.toLowerCase())
              )
            : this.options;

        if (filteredOptions.length === 0) {
            return `
                <div class="select-options-empty">
                    <i class="fas fa-inbox"></i>
                    <p>${this.config.emptyMessage}</p>
                </div>
            `;
        }

        return filteredOptions.map(option => `
            <li class="select-option-item ${option.selected ? 'selected' : ''} ${option.disabled ? 'disabled' : ''}"
                data-value="${option.value}"
                role="option"
                aria-selected="${option.selected}">
                <div class="select-option-content">
                    <div class="select-option-text">
                        <p class="select-option-name">${option.text}</p>
                    </div>
                </div>
                <div class="select-option-check">
                    <i class="fas fa-check"></i>
                </div>
            </li>
        `).join('');
    }

    /**
     * Adjuntar event listeners a las opciones
     */
    attachOptionListeners() {
        const optionItems = document.querySelectorAll('.select-option-item:not(.disabled)');
        optionItems.forEach(item => {
            item.addEventListener('click', () => {
                const value = item.getAttribute('data-value');
                this.selectOption(value);
            });
        });
    }

    /**
     * Manejar búsqueda
     */
    handleSearch(term) {
        this.searchTerm = term;
        const listContainer = document.getElementById('selectOptionsList');
        if (listContainer) {
            listContainer.innerHTML = this.renderOptions();
            this.attachOptionListeners();
        }
    }

    /**
     * Seleccionar una opción
     */
    selectOption(value) {
        if (!this.activeSelectId) return;

        const selectElement = document.getElementById(this.activeSelectId);
        if (!selectElement) return;

        // Actualizar el select original
        selectElement.value = value;
        
        // Disparar evento change
        const changeEvent = new Event('change', { bubbles: true });
        selectElement.dispatchEvent(changeEvent);

        // Actualizar el trigger
        this.updateTrigger(this.activeSelectId);

        // Cerrar modal
        this.closeModal();
    }

    /**
     * Actualizar el trigger con el valor seleccionado
     */
    updateTrigger(selectId) {
        const selectElement = document.getElementById(selectId);
        const wrapper = document.querySelector(`[data-select-id="${selectId}"]`);
        
        if (!selectElement || !wrapper) return;

        const trigger = wrapper.querySelector('.custom-select-trigger');
        const valueSpan = wrapper.querySelector('.custom-select-value');
        
        if (!trigger || !valueSpan) return;

        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const selectedText = selectedOption.textContent;
        const hasValue = selectElement.value !== '';

        valueSpan.textContent = selectedText;
        
        if (hasValue) {
            trigger.classList.remove('empty');
            trigger.classList.add('has-value');
        } else {
            trigger.classList.remove('has-value');
            trigger.classList.add('empty');
        }
    }

    /**
     * Cerrar modal
     */
    closeModal() {
        if (!this.activeModal) return;

        // Remover clase active del trigger
        if (this.activeSelectId) {
            const trigger = document.querySelector(`[data-select-id="${this.activeSelectId}"] .custom-select-trigger`);
            if (trigger) {
                trigger.classList.remove('active');
            }
        }

        // Animar salida
        this.activeModal.classList.remove('show');
        
        setTimeout(() => {
            if (this.activeModal) {
                this.activeModal.remove();
                this.activeModal = null;
            }
            this.activeSelectId = null;
            this.selectedValue = null;
            this.searchTerm = '';
        }, 200);
    }

    /**
     * Destruir y limpiar
     */
    destroy() {
        this.closeModal();
        
        // Restaurar selects originales
        document.querySelectorAll('.custom-select-wrapper').forEach(wrapper => {
            const select = wrapper.querySelector('select');
            if (select) {
                wrapper.parentNode.insertBefore(select, wrapper);
                wrapper.remove();
            }
        });
    }
}

// Instancia global
let customSelectModal;

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    customSelectModal = new CustomSelectModal();
    customSelectModal.init();
});

// También inicializar cuando se abra el modal de producto
// (para asegurar que funcione con contenido dinámico)
if (typeof ProductModal !== 'undefined') {
    const originalOpen = ProductModal.open;
    ProductModal.open = function(...args) {
        originalOpen.apply(this, args);
        // Reinicializar custom selects después de un pequeño delay
        setTimeout(() => {
            if (customSelectModal) {
                customSelectModal.destroy();
            }
            customSelectModal = new CustomSelectModal();
            customSelectModal.init();
        }, 100);
    };
}
