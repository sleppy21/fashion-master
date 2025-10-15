/**
 * SCRIPT DE PRUEBA - Sistema de Triggers de Modales
 * Ejecuta esto en la consola del navegador para verificar el funcionamiento
 */

(function() {
    console.log('🧪 Iniciando pruebas del sistema de triggers...\n');
    
    // Test 1: Verificar que los elementos existen
    console.log('📋 Test 1: Verificando elementos del DOM');
    const sidebar = document.querySelector('.modern-sidebar');
    const favLink = document.querySelector('#favorites-link');
    const notifLink = document.querySelector('#notifications-link');
    const userLink = document.querySelector('#user-account-link');
    
    console.log('  • Sidebar:', sidebar ? '✅ Encontrado' : '❌ No encontrado');
    console.log('  • Botón favoritos:', favLink ? '✅ Encontrado' : '❌ No encontrado');
    console.log('  • Botón notificaciones:', notifLink ? '✅ Encontrado' : '❌ No encontrado');
    console.log('  • Botón usuario:', userLink ? '✅ Encontrado' : '❌ No encontrado');
    
    // Test 2: Verificar funciones globales
    console.log('\n📋 Test 2: Verificando funciones globales');
    console.log('  • toggleFavoritesModal:', typeof window.toggleFavoritesModal === 'function' ? '✅ Disponible' : '❌ No disponible');
    console.log('  • toggleNotificationsModal:', typeof window.toggleNotificationsModal === 'function' ? '✅ Disponible' : '❌ No disponible');
    console.log('  • toggleUserModal:', typeof window.toggleUserModal === 'function' ? '✅ Disponible' : '❌ No disponible');
    
    // Test 3: Verificar estado inicial
    console.log('\n📋 Test 3: Estado inicial');
    const hasTriggerActive = document.querySelector('.modal-trigger-active') !== null;
    const hasStickyActive = sidebar && sidebar.classList.contains('sticky-parent-active');
    
    console.log('  • Trigger activo:', hasTriggerActive ? '⚠️ SÍ (debería ser NO)' : '✅ NO');
    console.log('  • Sidebar sticky:', hasStickyActive ? '⚠️ SÍ (debería ser NO)' : '✅ NO');
    
    // Test 4: Simular apertura de modal
    console.log('\n📋 Test 4: Simulando apertura de modal de favoritos...');
    
    if (favLink && typeof window.toggleFavoritesModal === 'function') {
        // Escuchar el evento
        let eventReceived = false;
        document.addEventListener('modalTriggerChanged', function testListener(e) {
            eventReceived = true;
            console.log('  • Evento recibido:', e.detail);
            document.removeEventListener('modalTriggerChanged', testListener);
        });
        
        // Esperar un momento y verificar
        setTimeout(() => {
            favLink.click();
            
            setTimeout(() => {
                const triggerActive = favLink.classList.contains('modal-trigger-active');
                const sidebarSticky = sidebar && sidebar.classList.contains('sticky-parent-active');
                
                console.log('\n📊 Resultados después de abrir modal:');
                console.log('  • Trigger activo en botón:', triggerActive ? '✅ SÍ' : '❌ NO (debería ser SÍ)');
                console.log('  • Sidebar sticky activo:', sidebarSticky ? '✅ SÍ' : '❌ NO (debería ser SÍ)');
                console.log('  • Evento disparado:', eventReceived ? '✅ SÍ' : '❌ NO (debería ser SÍ)');
                
                // Test 5: Cerrar modal
                console.log('\n📋 Test 5: Cerrando modal...');
                setTimeout(() => {
                    favLink.click(); // Toggle cierra
                    
                    setTimeout(() => {
                        const triggerAfterClose = favLink.classList.contains('modal-trigger-active');
                        const sidebarAfterClose = sidebar && sidebar.classList.contains('sticky-parent-active');
                        
                        console.log('\n📊 Resultados después de cerrar modal:');
                        console.log('  • Trigger activo:', triggerAfterClose ? '❌ SÍ (debería ser NO)' : '✅ NO');
                        console.log('  • Sidebar sticky:', sidebarAfterClose ? '❌ SÍ (debería ser NO)' : '✅ NO');
                        
                        // Resumen final
                        console.log('\n' + '='.repeat(50));
                        console.log('✅ PRUEBAS COMPLETADAS');
                        console.log('='.repeat(50));
                        
                        const allGood = !triggerAfterClose && !sidebarAfterClose;
                        if (allGood) {
                            console.log('\n🎉 ¡Todo funciona correctamente!');
                        } else {
                            console.log('\n⚠️ Hay algunos problemas. Revisa los resultados arriba.');
                        }
                    }, 500);
                }, 1000);
            }, 500);
        }, 100);
    } else {
        console.log('  ❌ No se puede ejecutar la prueba (elementos no encontrados)');
    }
    
})();

// Funciones de ayuda para debugging manual
window.testStickyBehavior = function() {
    console.log('\n🔍 Estado actual del sistema:');
    const sidebar = document.querySelector('.modern-sidebar');
    const triggers = document.querySelectorAll('.modal-trigger-active');
    
    console.log('  • Triggers activos:', triggers.length);
    triggers.forEach((t, i) => {
        console.log(`    ${i + 1}. ${t.id || t.className}`);
    });
    
    console.log('  • Sidebar tiene sticky-parent-active:', 
        sidebar && sidebar.classList.contains('sticky-parent-active') ? 'SÍ' : 'NO');
    
    if (sidebar) {
        const computedStyle = window.getComputedStyle(sidebar);
        console.log('  • Position computada:', computedStyle.position);
        console.log('  • Top computado:', computedStyle.top);
    }
};

console.log('\n💡 TIP: Ejecuta testStickyBehavior() en cualquier momento para ver el estado');
