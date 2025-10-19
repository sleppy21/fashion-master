<!-- BUSCADOR GLOBAL - DROPDOWN -->
<div id="global-search-modal" class="global-search-modal">
    <div class="search-dropdown-content">
        <!-- Input de búsqueda -->
        <div class="search-input-wrapper">
            <i class="fa fa-search search-icon"></i>
            <input type="text" 
                   id="global-search-input" 
                   class="global-search-input" 
                   placeholder="Buscar productos..."
                   autocomplete="off">
            <button class="search-clear-btn" id="search-clear-btn" style="display: none;">
                <i class="fa fa-times"></i>
            </button>
        </div>
        
        <!-- Resultados -->
        <div class="search-results-wrapper" id="search-results-wrapper">
            
            <!-- Loading -->
            <div class="search-loading" id="search-loading" style="display: none;">
                <div class="spinner-search"></div>
                <p>Buscando...</p>
            </div>
            
            <!-- Productos -->
            <div class="search-products-list" id="search-products-list" style="display: none;">
            </div>
            
            <!-- Sin resultados -->
            <div class="search-no-results" id="search-no-results" style="display: none;">
                <i class="fa fa-search"></i>
                <p>No se encontraron resultados</p>
            </div>
            
        </div>
    </div>
</div>
