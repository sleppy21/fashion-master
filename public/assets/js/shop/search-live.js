/**
 * SEARCH LIVE MODULE
 * Búsqueda en tiempo real con debounce
 * @version 2.0
 */

(function() {
    'use strict';

    // Configuración
    const CONFIG = {
        debounceTime: 300,
        minCharacters: 2,
        maxSuggestions: 5,
        highlightMatches: true
    };

    // Referencias DOM
    let searchInput = null;
    let searchSuggestions = null;
    let debounceTimer = null;
    let currentRequest = null;

    /**
     * Inicializar búsqueda en vivo
     */
    function initLiveSearch() {
        searchInput = document.getElementById('search-input');
        
       

        // Crear contenedor de sugerencias si no existe
        createSuggestionsContainer();

        // Event listeners
        searchInput.addEventListener('input', handleSearchInput);
        searchInput.addEventListener('focus', handleSearchFocus);
        searchInput.addEventListener('keydown', handleKeyDown);

        // Cerrar sugerencias al hacer click fuera
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchSuggestions.contains(e.target)) {
                hideSuggestions();
            }
        });

        // Obtener búsqueda de URL si existe
        const urlParams = new URLSearchParams(window.location.search);
        const searchQuery = urlParams.get('busqueda');
        if (searchQuery) {
            searchInput.value = searchQuery;
        }

    }

    /**
     * Crear contenedor de sugerencias
     */
    function createSuggestionsContainer() {
        // Buscar si ya existe
        searchSuggestions = document.getElementById('search-suggestions');
        
        if (!searchSuggestions) {
            searchSuggestions = document.createElement('div');
            searchSuggestions.id = 'search-suggestions';
            searchSuggestions.className = 'search-suggestions';
            searchInput.parentElement.appendChild(searchSuggestions);
        }

        // Estilos inline por si no están en CSS
        searchSuggestions.style.cssText = `
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.15);
            max-height: 400px;
            overflow-y: auto;
            z-index: 1000;
            margin-top: 8px;
            display: none;
        `;
    }

    /**
     * Manejar entrada de búsqueda
     * @param {Event} e - Evento de input
     */
    function handleSearchInput(e) {
        const query = e.target.value.trim();

        // Cancelar timer anterior
        if (debounceTimer) {
            clearTimeout(debounceTimer);
        }

        // Si la búsqueda está vacía, limpiar todo
        if (query.length === 0) {
            hideSuggestions();
            if (typeof window.aplicarFiltro === 'function') {
                window.aplicarFiltro('busqueda', '');
            }
            return;
        }

        // Mostrar indicador de carga si hay suficientes caracteres
        if (query.length >= CONFIG.minCharacters) {
            showLoadingSuggestions();
        }

        // Debounce para evitar múltiples peticiones
        debounceTimer = setTimeout(() => {
            if (query.length >= CONFIG.minCharacters) {
                performSearch(query);
            } else {
                hideSuggestions();
            }
        }, CONFIG.debounceTime);
    }

    /**
     * Manejar foco en input
     */
    function handleSearchFocus() {
        const query = searchInput.value.trim();
        if (query.length >= CONFIG.minCharacters) {
            showSuggestions();
        }
    }

    /**
     * Manejar teclas especiales
     * @param {KeyboardEvent} e - Evento de teclado
     */
    function handleKeyDown(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const query = searchInput.value.trim();
            if (query.length > 0) {
                applySearch(query);
            }
        } else if (e.key === 'Escape') {
            hideSuggestions();
            searchInput.blur();
        }
    }

    /**
     * Realizar búsqueda
     * @param {string} query - Término de búsqueda
     */
    function performSearch(query) {

        // Cancelar petición anterior si existe
        if (currentRequest) {
            currentRequest.abort();
        }

        // Crear nuevo AbortController
        const controller = new AbortController();
        currentRequest = controller;

        // Realizar petición AJAX para sugerencias
        fetch(`app/actions/get_search_suggestions.php?q=${encodeURIComponent(query)}`, {
            signal: controller.signal
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displaySuggestions(data.suggestions, query);
            } else {
                hideSuggestions();
            }
        })
        .catch(error => {
            if (error.name !== 'AbortError') {
                hideSuggestions();
            }
        })
        .finally(() => {
            currentRequest = null;
        });
    }

    /**
     * Mostrar sugerencias
     * @param {Array} suggestions - Array de sugerencias
     * @param {string} query - Término buscado
     */
    function displaySuggestions(suggestions, query) {
        if (!suggestions || suggestions.length === 0) {
            showNoResults(query);
            return;
        }

        let html = '<div class="suggestions-list">';

        // Limitar número de sugerencias
        const limitedSuggestions = suggestions.slice(0, CONFIG.maxSuggestions);

        limitedSuggestions.forEach(suggestion => {
            const highlightedName = CONFIG.highlightMatches 
                ? highlightText(suggestion.nombre, query)
                : suggestion.nombre;

            html += `
                <div class="suggestion-item" data-product-id="${suggestion.id}">
                    <div class="suggestion-image">
                        <img src="${suggestion.imagen}" alt="${suggestion.nombre}" loading="lazy">
                    </div>
                    <div class="suggestion-info">
                        <div class="suggestion-name">${highlightedName}</div>
                        <div class="suggestion-category">${suggestion.categoria || ''}</div>
                    </div>
                    <div class="suggestion-price">
                        S/ ${parseFloat(suggestion.precio).toFixed(2)}
                    </div>
                </div>
            `;
        });

        // Botón para ver todos los resultados
        if (suggestions.length > CONFIG.maxSuggestions) {
            html += `
                <div class="suggestion-footer">
                    <button class="btn-view-all-results">
                        Ver todos los resultados (${suggestions.length})
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            `;
        }

        html += '</div>';

        searchSuggestions.innerHTML = html;
        showSuggestions();

        // Event listeners para sugerencias
        attachSuggestionListeners();
    }

    /**
     * Adjuntar event listeners a sugerencias
     */
    function attachSuggestionListeners() {
        // Click en sugerencia individual
        const suggestionItems = searchSuggestions.querySelectorAll('.suggestion-item');
        suggestionItems.forEach(item => {
            item.addEventListener('click', function() {
                const productId = this.dataset.productId;
                window.location.href = `product-details.php?id=${productId}`;
            });
        });

        // Botón ver todos
        const btnViewAll = searchSuggestions.querySelector('.btn-view-all-results');
        if (btnViewAll) {
            btnViewAll.addEventListener('click', function() {
                applySearch(searchInput.value.trim());
            });
        }
    }

    /**
     * Resaltar texto que coincide con la búsqueda
     * @param {string} text - Texto completo
     * @param {string} query - Término buscado
     * @returns {string} HTML con texto resaltado
     */
    function highlightText(text, query) {
        if (!query) return text;

        const regex = new RegExp(`(${escapeRegex(query)})`, 'gi');
        return text.replace(regex, '<mark>$1</mark>');
    }

    /**
     * Escapar caracteres especiales para regex
     * @param {string} str - String a escapar
     * @returns {string} String escapado
     */
    function escapeRegex(str) {
        return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    /**
     * Mostrar indicador de carga
     */
    function showLoadingSuggestions() {
        searchSuggestions.innerHTML = `
            <div class="suggestions-loading">
                <div class="spinner-small"></div>
                <span>Buscando...</span>
            </div>
        `;
        showSuggestions();
    }

    /**
     * Mostrar mensaje de sin resultados
     * @param {string} query - Término buscado
     */
    function showNoResults(query) {
        searchSuggestions.innerHTML = `
            <div class="suggestions-no-results">
                <i class="fas fa-search"></i>
                <p>No se encontraron resultados para "<strong>${escapeHtml(query)}</strong>"</p>
            </div>
        `;
        showSuggestions();
    }

    /**
     * Escapar HTML para prevenir XSS
     * @param {string} text - Texto a escapar
     * @returns {string} Texto escapado
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Mostrar sugerencias
     */
    function showSuggestions() {
        searchSuggestions.style.display = 'block';
    }

    /**
     * Ocultar sugerencias
     */
    function hideSuggestions() {
        searchSuggestions.style.display = 'none';
    }

    /**
     * Aplicar búsqueda (enviar formulario o usar AJAX)
     * @param {string} query - Término de búsqueda
     */
    function applySearch(query) {
        hideSuggestions();


        // Si existe la función global de filtros, usarla
        if (typeof window.aplicarFiltro === 'function') {
            window.aplicarFiltro('busqueda', query);
        } else {
            // Fallback: actualizar URL y recargar
            const url = new URL(window.location);
            url.searchParams.set('busqueda', query);
            window.location.href = url.toString();
        }
    }

    /**
     * Limpiar búsqueda
     */
    function clearSearch() {
        searchInput.value = '';
        hideSuggestions();
        
        if (typeof window.aplicarFiltro === 'function') {
            window.aplicarFiltro('busqueda', '');
        }
    }

    /**
     * Obtener término de búsqueda actual
     * @returns {string} Término de búsqueda
     */
    function getCurrentSearch() {
        return searchInput ? searchInput.value.trim() : '';
    }

    // Inicializar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initLiveSearch);
    } else {
        initLiveSearch();
    }

    // Exportar funciones públicas
    window.LiveSearch = {
        init: initLiveSearch,
        clear: clearSearch,
        getCurrentSearch: getCurrentSearch,
        performSearch: performSearch
    };


})();

// Estilos CSS inline para sugerencias (si no están en archivo CSS)
if (!document.getElementById('search-live-styles')) {
    const searchStyles = document.createElement('style');
    searchStyles.id = 'search-live-styles';
    searchStyles.textContent = `
    .search-suggestions {
        animation: fadeInDown 0.3s ease;
    }

    .suggestions-list {
        padding: 8px;
    }

    .suggestion-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        border-radius: 10px;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }

    .suggestion-item:hover {
        background: rgba(102, 126, 234, 0.05);
    }

    .suggestion-image {
        width: 50px;
        height: 50px;
        flex-shrink: 0;
    }

    .suggestion-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 8px;
    }

    .suggestion-info {
        flex: 1;
        min-width: 0;
    }

    .suggestion-name {
        font-weight: 600;
        color: #1a1a2e;
        font-size: 14px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .suggestion-name mark {
        background: rgba(102, 126, 234, 0.2);
        color: #667eea;
        padding: 0 2px;
        border-radius: 2px;
    }

    .suggestion-category {
        font-size: 12px;
        color: #6c757d;
    }

    .suggestion-price {
        font-weight: 700;
        color: #667eea;
        font-size: 16px;
    }

    .suggestions-loading,
    .suggestions-no-results {
        padding: 24px;
        text-align: center;
        color: #6c757d;
    }

    .suggestions-loading {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
    }

    .spinner-small {
        width: 20px;
        height: 20px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid #667eea;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    .suggestions-no-results i {
        font-size: 32px;
        color: #dee2e6;
        margin-bottom: 8px;
    }

    .suggestion-footer {
        padding: 8px;
        border-top: 1px solid #e9ecef;
    }

    .btn-view-all-results {
        width: 100%;
        padding: 12px;
        border: none;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-view-all-results:hover {
        transform: scale(1.02);
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    body.dark-mode .search-suggestions {
        background: #2a2a2e;
        box-shadow: 0 8px 32px rgba(0,0,0,0.5);
    }

    body.dark-mode .suggestion-item:hover {
        background: rgba(102, 126, 234, 0.15);
    }

    body.dark-mode .suggestion-name {
        color: #e0e0e0;
    }

    body.dark-mode .suggestion-footer {
        border-top-color: #404040;
    }
`;
    document.head.appendChild(searchStyles);
}
