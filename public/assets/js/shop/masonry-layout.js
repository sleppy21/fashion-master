(function() {
    'use strict';
    class SimpleMasonry {
        constructor(container) {
            this.container = document.querySelector(container);
            if (!this.container) return;
            this.items = [];
            this.columnHeights = [];
            this.resizeTimeout = null;
            this.init();
        }
        init() {
            this.layout();
            this.bindEvents();
        }
        getColumns() {
            const width = window.innerWidth;
            if (width <= 576) return 2;
            if (width <= 992) return 3;
            return 4;
        }
        getGap() {
            const width = window.innerWidth;
            if (width <= 576) return 12;
            if (width <= 992) return 20;
            return 30;
        }
        layout() {
            this.items = Array.from(this.container.querySelectorAll('[class*="col-"]'));
            if (!this.items.length) {
                console.log('⏸️ Masonry: Sin items para procesar');
                return;
            }

            const cols = this.getColumns();
            const gap = this.getGap();
            this.columnHeights = Array(cols).fill(0);
            this.container.style.position = 'relative';
            const leftPositions = [];
            this.items.forEach((item) => {
                item.style.position = 'static';
                const rect = item.getBoundingClientRect();
                const containerRect = this.container.getBoundingClientRect();
                leftPositions.push(rect.left - containerRect.left);
            });
            this.items.forEach((item, index) => {
                const col = index % cols;
                const itemHeight = item.offsetHeight;
                const leftPos = leftPositions[index];
                item.style.position = 'absolute';
                item.style.left = leftPos + 'px';
                item.style.top = this.columnHeights[col] + 'px';
                this.columnHeights[col] += itemHeight + gap;
            });
            const maxHeight = Math.max(...this.columnHeights);
            this.container.style.height = maxHeight + 'px';
            console.log('Masonry aplicado:', this.items.length, 'items');
        }
        bindEvents() {
            window.addEventListener('resize', () => {
                clearTimeout(this.resizeTimeout);
                this.resizeTimeout = setTimeout(() => this.layout(), 250);
            });
            const observer = new MutationObserver(() => setTimeout(() => this.layout(), 50));
            observer.observe(this.container, { childList: true, subtree: false });
        }
        refresh() {
            this.layout();
        }
    }
    function init() {
        const grid = document.querySelector('.products-grid-modern .row');
        if (grid) {
            if (window.productMasonry) window.productMasonry = null;
            console.log('🚀 Masonry: Inicializando...');
            window.productMasonry = new SimpleMasonry('.products-grid-modern .row');
            window.refreshMasonry = () => { if (window.productMasonry) window.productMasonry.refresh(); };
            window.reinitMasonry = () => init();
        } else {
            console.log('⏸️ Masonry: Grid no encontrado, esperando...');
        }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
        // Reinicializar cuando se actualicen productos (AJAX)
    document.addEventListener('productsUpdated', () => {
        console.log('🔄 Productos actualizados - Aplicando Masonry INMEDIATAMENTE');
        init(); // Sin timeout - ejecutar INMEDIATAMENTE
    });
})();
