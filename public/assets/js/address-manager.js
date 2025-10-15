/**
 * Manejo de Direcciones en Profile
 */
(function() {
    'use strict';

    console.log('📍 Iniciando address-manager.js...');

    // Variables globales
    let isEditMode = false;
    let currentAddressId = null;

    // Elementos del DOM
    const addressModal = $('#addressModal');
    const addressForm = $('#addressForm');
    const saveAddressBtn = $('#saveAddressBtn');
    const addressModalTitle = $('#addressModalTitle');

    /**
     * Abrir modal para agregar dirección
     */
    function openAddAddressModal() {
        console.log('➕ Abriendo modal para agregar dirección');
        isEditMode = false;
        currentAddressId = null;
        
        addressModalTitle.text('Agregar Dirección');
        addressForm[0].reset();
        $('#address_id').val('');
        
        addressModal.modal('show');
    }

    /**
     * Abrir modal para editar dirección
     */
    async function openEditAddressModal(addressId) {
        console.log('✏️ Abriendo modal para editar dirección:', addressId);
        isEditMode = true;
        currentAddressId = addressId;
        
        try {
            // Mostrar loading
            saveAddressBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Cargando...');
            
            const response = await fetch(`app/actions/get_address.php?id_direccion=${addressId}`);
            const data = await response.json();
            
            if (data.success) {
                const dir = data.direccion;
                
                // Llenar el formulario
                $('#address_id').val(dir.id_direccion);
                $('#address_name').val(dir.nombre_direccion);
                $('#address_phone').val(dir.telefono_direccion || '');
                $('#address_full').val(dir.direccion_completa_direccion);
                $('#address_departamento').val(dir.departamento_direccion);
                $('#address_provincia').val(dir.provincia_direccion);
                $('#address_distrito').val(dir.distrito_direccion);
                $('#address_reference').val(dir.referencia_direccion || '');
                
                addressModalTitle.text('Editar Dirección');
                addressModal.modal('show');
            } else {
                showToast(data.error || 'No se pudo cargar la dirección', 'error');
            }
        } catch (error) {
            console.error('Error al cargar dirección:', error);
            showToast('Error al cargar la dirección', 'error');
        } finally {
            saveAddressBtn.prop('disabled', false).html('<i class="fa fa-save"></i> Guardar Dirección');
        }
    }

    /**
     * Guardar dirección (crear o actualizar)
     */
    async function saveAddress() {
        console.log('💾 Guardando dirección...');
        
        // Validar formulario
        if (!addressForm[0].checkValidity()) {
            addressForm[0].reportValidity();
            return;
        }
        
        const formData = new FormData(addressForm[0]);
        const action = isEditMode ? 'update_address.php' : 'create_address.php';
        
        try {
            // Mostrar loading
            saveAddressBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');
            
            const response = await fetch(`app/actions/${action}`, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showToast(data.message, 'success');
                addressModal.modal('hide');
                
                // Recargar la página después de 1 segundo
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showToast(data.error || 'Error al guardar la dirección', 'error');
                saveAddressBtn.prop('disabled', false).html('<i class="fa fa-save"></i> Guardar Dirección');
            }
        } catch (error) {
            console.error('Error al guardar dirección:', error);
            showToast('Error al guardar la dirección', 'error');
            saveAddressBtn.prop('disabled', false).html('<i class="fa fa-save"></i> Guardar Dirección');
        }
    }

    /**
     * Establecer dirección como predeterminada
     */
    async function setDefaultAddress(addressId) {
        console.log('⭐ Estableciendo dirección predeterminada:', addressId);
        
        try {
            const formData = new FormData();
            formData.append('id_direccion', addressId);
            
            const response = await fetch('app/actions/set_default_address.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showToast(data.message, 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 800);
            } else {
                showToast(data.error || 'Error al establecer dirección predeterminada', 'error');
            }
        } catch (error) {
            console.error('Error al establecer dirección predeterminada:', error);
            showToast('Error al establecer dirección predeterminada', 'error');
        }
    }

    /**
     * Eliminar dirección con confirmación
     */
    async function deleteAddress(addressId) {
        console.log('🗑️ Solicitando eliminar dirección:', addressId);
        
        // Mostrar toast de confirmación
        const confirmDelete = confirm('¿Estás seguro de eliminar esta dirección? Esta acción no se puede deshacer.');
        
        if (confirmDelete) {
            try {
                const formData = new FormData();
                formData.append('id_direccion', addressId);
                
                const response = await fetch('app/actions/delete_address.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showToast(data.message, 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 800);
                } else {
                    showToast(data.error || 'Error al eliminar la dirección', 'error');
                }
            } catch (error) {
                console.error('Error al eliminar dirección:', error);
                showToast('Error al eliminar la dirección', 'error');
            }
        }
    }

    /**
     * Mostrar notificación toast
     */
    function showToast(message, type = 'info') {
        // Usar la función global si existe
        if (typeof window.showNotification === 'function') {
            window.showNotification(message, type);
        } else {
            // Fallback simple
            alert(message);
        }
    }

    /**
     * Event Listeners
     */
    $(document).ready(function() {
        console.log('✅ DOM Ready - Configurando event listeners para direcciones');

        // Botón agregar dirección (header)
        $(document).on('click', '#btn-add-address, #btn-add-first-address', function(e) {
            e.preventDefault();
            openAddAddressModal();
        });

        // Botón guardar dirección
        saveAddressBtn.on('click', function(e) {
            e.preventDefault();
            saveAddress();
        });

        // Botón editar dirección
        $(document).on('click', '.btn-edit-address', function(e) {
            e.preventDefault();
            const addressId = $(this).data('id');
            openEditAddressModal(addressId);
        });

        // Botón establecer como predeterminada
        $(document).on('click', '.btn-set-default', function(e) {
            e.preventDefault();
            const addressId = $(this).data('id');
            setDefaultAddress(addressId);
        });

        // Botón eliminar dirección
        $(document).on('click', '.btn-delete-address', function(e) {
            e.preventDefault();
            const addressId = $(this).data('id');
            deleteAddress(addressId);
        });

        // Reset del formulario al cerrar el modal
        addressModal.on('hidden.bs.modal', function() {
            addressForm[0].reset();
            $('#address_id').val('');
            isEditMode = false;
            currentAddressId = null;
        });

        console.log('✅ Event listeners de direcciones configurados');
    });

})();
