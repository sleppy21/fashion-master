/**
 * MASONRY LAYOUT - Efecto Pinterest SIMPLE
 * Respeta Bootstrap, solo apila verticalmente
 * @version 3.0
 * @date 2025-11-02
 */

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
            // Esperar a que las imágenes carguen
            this.waitForImages().then(() => {
                setTimeout(() => {
                    this.layout();
                    this.bindEvents();
                }, 100);
            });
        }

        waitForImages() {
            return new Promise((resolve) => {
                const images = this.container.querySelectorAll('img');
                if (images.length === 0) {
                    resolve();
                    return;
                }

                let loaded = 0;
                const total = images.length;

                const checkComplete = () => {
                    loaded++;
                    if (loaded === total) resolve();
                };

                images.forEach(img => {
                    if (img.complete) {
                        checkComplete();
                    } else {
                        img.onload = checkComplete;
                        img.onerror = checkComplete;
                    }
                });
            });
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
            if (!this.items.length) return;

            const cols = this.getColumns();
            const gap = this.getGap();
            
            // Resetear alturas de columnas
            this.columnHeights = Array(cols).fill(0);
            
            // Hacer el contenedor relative
            this.container.style.position = 'relative';

            // Guardar posiciones left originales de Bootstrap
            const leftPositions = [];
            this.items.forEach((item) => {
                item.style.position = 'static';
                const rect = item.getBoundingClientRect();
                const containerRect = this.container.getBoundingClientRect();
                leftPositions.push(rect.left - containerRect.left);
            });

            this.items.forEach((item, index) => {
                // Determinar a qué columna pertenece según Bootstrap
                const col = index % cols;
                
                // Obtener altura del item
                const itemHeight = item.offsetHeight;
                
                // Usar la posición left original de Bootstrap
                const leftPos = leftPositions[index];
                
                // Aplicar position absolute
                item.style.position = 'absolute';
                item.style.left = leftPos + 'px';
                item.style.top = this.columnHeights[col] + 'px';
                
                // Actualizar altura de esta columna
                this.columnHeights[col] += itemHeight + gap;
            });

            // Establecer altura total del contenedor
            const maxHeight = Math.max(...this.columnHeights);
            this.container.style.height = maxHeight + 'px';
        }

        bindEvents() {
            window.addEventListener('resize', () => {
                clearTimeout(this.resizeTimeout);
                this.resizeTimeout = setTimeout(() => {
                    this.layout();
                }, 250);
            });

            // Observar cambios en el contenedor
            const observer = new MutationObserver(() => {
                this.waitForImages().then(() => {
                    this.layout();
                });
            });

            observer.observe(this.container, {
                childList: true,
                subtree: false
            });
        }

        refresh() {
            this.waitForImages().then(() => {
                this.layout();
            });
        }
    }

    // Inicializar
    function init() {
        const grid = document.querySelector('.products-grid-modern .row');
        if (grid) {
            // Si ya existe, destruirlo primero
            if (window.productMasonry) {
                window.productMasonry = null;
            }
            
            window.productMasonry = new SimpleMasonry('.products-grid-modern .row');
            
            window.refreshMasonry = () => {
                if (window.productMasonry) {
                    window.productMasonry.refresh();
                }
            };
            
            window.reinitMasonry = () => {
                setTimeout(() => {
                    init();
                }, 100);
            };
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Reinicializar cuando se actualicen productos
    document.addEventListener('productsUpdated', () => {
        setTimeout(() => {
            init();
        }, 150);
    });

})();
