/**
 * EFECTO FLUIDO SIMPLE PARA MENÚ DEL HEADER
 * Subrayado animado que se mueve entre items
 */

(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        console.log('🚀 [FLUID MENU] Script simple cargado');

        const menuItems = document.querySelectorAll('.header__menu li');
        if (!menuItems.length) return console.error('❌ [FLUID MENU] No hay items');

        const menuList = document.querySelector('.header__menu > ul');
        if (!menuList) return console.error('❌ [FLUID MENU] No hay UL');

        console.log('✅ [FLUID MENU] Menú encontrado con', menuItems.length, 'items');

        // Crear indicador simple
        const indicator = document.createElement('div');
        indicator.className = 'menu-indicator';
        indicator.style.cssText = `
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            background: linear-gradient(90deg, transparent, #ca8a04, transparent);
            border-radius: 3px;
            transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            pointer-events: none;
            opacity: 0;
            box-shadow: 0 0 10px rgba(202, 138, 4, 0.6);
        `;
        
        menuList.style.position = 'relative';
        menuList.appendChild(indicator);

        function moveIndicator(item) {
            const rect = item.getBoundingClientRect();
            const menuRect = menuList.getBoundingClientRect();
            const left = rect.left - menuRect.left;
            const width = rect.width;

            indicator.style.left = left + 'px';
            indicator.style.width = width + 'px';
            indicator.style.opacity = '1';

            console.log('🎯 [FLUID MENU] Moviendo a:', item.textContent.trim());
        }

        // Event listeners
        menuItems.forEach((item, index) => {
            item.addEventListener('mouseenter', () => moveIndicator(item));
        });

        menuList.addEventListener('mouseleave', () => {
            indicator.style.opacity = '0';
            console.log('👋 [FLUID MENU] Mouse salió');
        });

        console.log('🎉 [FLUID MENU] Sistema simple activado');
    });
})();


        function createElem(tag, cls) {
            const el = document.createElement(tag);
            if (cls) el.className = cls;
            return el;
        }

        function createFluidElements() {
            // Forzar que el UL tenga position relative (el contenedor de los items)
            menuList.style.position = 'relative';
            menuList.style.overflow = 'visible';
            // El gap puede causar problemas, pero lo respetamos
            
            fluidBlob = createElem('div', 'menu-fluid-blob');
            // FORZAR TODOS LOS ESTILOS INLINE PARA EVITAR CONFLICTOS
            Object.assign(fluidBlob.style, {
                position: 'absolute',
                display: 'block',
                top: '0px', // Alineado al top del UL
                left: '0px',
                transform: 'translateY(0)',
                width: '100px',
                height: '50px', // Altura de los items del menú
                // TEST - ROJO BRILLANTE PARA VERIFICAR VISIBILIDAD
                background: 'rgba(255, 0, 0, 0.8)',  // ROJO para prueba
                borderRadius: '50px',
                opacity: '0',
                pointerEvents: 'none',
                zIndex: '-1', // DETRÁS de los items (negativo)
                transition: 'left 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55), width 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55), opacity 0.3s ease-out',
                // Shadow más visible
                boxShadow: '0 0 40px rgba(255, 0, 0, 0.9)',
                // Sin blur para máxima visibilidad
                filter: 'none',
                backdropFilter: 'none'
            });
            menuList.insertBefore(fluidBlob, menuList.firstChild);

            fluidWave = createElem('div', 'menu-fluid-wave');
            Object.assign(fluidWave.style, {
                position: 'absolute',
                display: 'block',
                top: '0px',
                left: '0px',
                transform: 'translateY(0) scale(0.95)',
                width: '100px',
                height: '50px',
                background: 'radial-gradient(ellipse at center, rgba(202, 138, 4, 0.15) 0%, transparent 70%)',
                borderRadius: '50px',
                opacity: '0',
                pointerEvents: 'none',
                zIndex: '0', // Más bajo que el blob
                transition: 'left 0.7s cubic-bezier(0.68, -0.55, 0.265, 1.55), width 0.7s cubic-bezier(0.68, -0.55, 0.265, 1.55), opacity 0.4s ease-out, transform 0.7s cubic-bezier(0.68, -0.55, 0.265, 1.55)',
                filter: 'blur(8px)'
            });
            menuList.insertBefore(fluidWave, menuList.firstChild);

            console.log('✨ [FLUID MENU] Elementos creados con inline styles forzados');
            
            // quick verify
            setTimeout(() => {
                const blobExists = !!document.querySelector('.menu-fluid-blob');
                const waveExists = !!document.querySelector('.menu-fluid-wave');
                console.log('🔍 [FLUID MENU] DOM check:', { blob: blobExists, wave: waveExists });
                
                if (blobExists) {
                    const computedBlob = window.getComputedStyle(fluidBlob);
                    console.log('📊 [FLUID MENU] Blob computed styles:', {
                        position: computedBlob.position,
                        zIndex: computedBlob.zIndex,
                        opacity: computedBlob.opacity,
                        display: computedBlob.display
                    });
                }
            }, 100);
        }

        createFluidElements();

        function getOffset(el) {
            const rect = el.getBoundingClientRect();
            return { left: rect.left + window.scrollX, top: rect.top + window.scrollY, width: rect.width, height: rect.height };
        }

        function createFluidParticles(fromIndex, toIndex) {
            if (fromIndex === -1 || toIndex === -1 || fromIndex === toIndex) return;
            const fromItem = menuItems[fromIndex];
            const toItem = menuItems[toIndex];
            const fromPos = getOffset(fromItem);
            const toPos = getOffset(toItem);
            const menuPos = getOffset(menu);
            const distance = Math.abs(toPos.left - fromPos.left);
            const particleCount = Math.min(8, Math.floor(distance / 30));

            for (let i = 0; i < particleCount; i++) {
                const p = createElem('div', 'menu-fluid-particle');
                const progress = i / Math.max(1, particleCount);
                const startLeft = fromPos.left - menuPos.left + (toPos.left - fromPos.left) * progress;
                const randomX = (Math.random() - 0.5) * 40;
                const randomY = (Math.random() - 0.5) * 30;
                p.style.left = startLeft + 'px';
                p.style.top = (fromPos.top - menuPos.top) + 'px';
                p.style.setProperty('--tx', randomX + 'px');
                p.style.setProperty('--ty', randomY + 'px');
                p.style.animationDelay = (i * 0.05) + 's';
                menu.appendChild(p);
                setTimeout(() => p.remove(), 1100 + (i * 50));
            }
        }

        function animateBlobToItem(index) {
            if (index < 0 || index >= menuItems.length) return;
            if (!fluidBlob || !fluidWave) return;
            
            const item = menuItems[index];
            const itemRect = item.getBoundingClientRect();
            const menuListRect = menuList.getBoundingClientRect();
            
            // Calcular posición relativa al UL usando getBoundingClientRect para precisión
            const leftPosition = itemRect.left - menuListRect.left;
            const blobWidth = itemRect.width;

            console.log('🌊 [FLUID MENU] Animando a item', index, ':', item.textContent.trim(), {
                leftPosition,
                blobWidth,
                itemRect: {
                    left: itemRect.left,
                    width: itemRect.width
                },
                menuListRect: {
                    left: menuListRect.left
                }
            });

            // FORZAR con inline styles
            fluidBlob.style.left = leftPosition + 'px';
            fluidBlob.style.width = blobWidth + 'px';
            fluidBlob.style.opacity = '1';
            fluidBlob.classList.add('active');

            fluidWave.style.left = (leftPosition - 5) + 'px';
            fluidWave.style.width = (blobWidth + 10) + 'px';
            fluidWave.style.opacity = '1';
            fluidWave.style.transform = 'translateY(0) scale(1.1)'; // Sin -50%
            fluidWave.classList.add('active');

            if (currentIndex !== -1 && currentIndex !== index) createFluidParticles(currentIndex, index);
            currentIndex = index;
        }

        // attach events
        menuItems.forEach((li, idx) => {
            li.addEventListener('mouseenter', () => {
                animateBlobToItem(idx);
            });
        });

        menu.addEventListener('mouseleave', () => {
            console.log('👋 [FLUID MENU] Mouse salió del menú');
            if (fluidBlob) {
                fluidBlob.style.opacity = '0';
                fluidBlob.classList.remove('active');
            }
            if (fluidWave) {
                fluidWave.style.opacity = '0';
                fluidWave.style.transform = 'translateY(-50%) scale(0.95)';
                fluidWave.classList.remove('active');
            }
            currentIndex = -1;
        });

        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                if (currentIndex !== -1) animateBlobToItem(currentIndex);
            }, 150);
        });

        // quick test - MÁS AGRESIVO Y VISIBLE
        setTimeout(() => {
            console.log('🧪 [FLUID MENU] ===== INICIANDO TEST VISUAL =====');
            console.log('🧪 [FLUID MENU] Mostrando blob en el primer item por 2.5 segundos');
            animateBlobToItem(0);
            
            // Verificar que el blob sea visible
            setTimeout(() => {
                if (fluidBlob) {
                    const blobRect = fluidBlob.getBoundingClientRect();
                    const blobComputed = window.getComputedStyle(fluidBlob);
                    const parent = fluidBlob.parentElement;
                    const parentComputed = parent ? window.getComputedStyle(parent) : null;
                    
                    console.log('🧪 [FLUID MENU] ===== DIAGNÓSTICO COMPLETO =====');
                    console.log('🧪 [FLUID MENU] Estado del blob:', {
                        opacity: fluidBlob.style.opacity,
                        opacityComputed: blobComputed.opacity,
                        left: fluidBlob.style.left,
                        width: fluidBlob.style.width,
                        zIndex: blobComputed.zIndex,
                        display: blobComputed.display,
                        position: blobComputed.position,
                        visible: blobRect.width > 0 && blobRect.height > 0,
                        rect: blobRect,
                        background: blobComputed.background
                    });
                    
                    if (parent) {
                        console.log('🧪 [FLUID MENU] Contenedor padre:', {
                            overflow: parentComputed.overflow,
                            overflowX: parentComputed.overflowX,
                            overflowY: parentComputed.overflowY,
                            position: parentComputed.position,
                            zIndex: parentComputed.zIndex
                        });
                    }
                    
                    // Buscar elementos ::after que puedan estar bloqueando
                    const menuLinks = document.querySelectorAll('.header__menu ul li a');
                    console.log('🧪 [FLUID MENU] Verificando ::after en', menuLinks.length, 'links:');
                    menuLinks.forEach((link, i) => {
                        const afterStyles = window.getComputedStyle(link, '::after');
                        if (afterStyles.content !== 'none' && afterStyles.display !== 'none') {
                            console.warn('⚠️ [FLUID MENU] Link', i, 'tiene ::after VISIBLE:', {
                                content: afterStyles.content,
                                display: afterStyles.display,
                                opacity: afterStyles.opacity,
                                zIndex: afterStyles.zIndex,
                                position: afterStyles.position
                            });
                        }
                    });
                }
            }, 100);
            
            setTimeout(() => {
                console.log('🧪 [FLUID MENU] Ocultando blob');
                if (fluidBlob) {
                    fluidBlob.style.opacity = '0';
                    fluidBlob.classList.remove('active');
                }
                if (fluidWave) {
                    fluidWave.style.opacity = '0';
                    fluidWave.classList.remove('active');
                }
                currentIndex = -1;
            }, 2500);
        }, 500);

        console.log('🎉 [FLUID MENU] (vanilla) Sistema activado');
    });
})();
