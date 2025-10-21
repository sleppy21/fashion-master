/**
 * Manejo de Direcciones en Profile
 */
(function() {
    'use strict';


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
                $('#address_name').val(dir.nombre_cliente_direccion);
                $('#address_dni').val(dir.dni_ruc_direccion || '');
                $('#address_phone').val(dir.telefono_direccion || '');
                $('#address_full').val(dir.direccion_completa_direccion);
                $('#address_reference').val(dir.referencia_direccion || '');
                $('#address_razon_social').val(dir.razon_social_direccion || '');
                
                // Si tiene RUC (11 dígitos), mostrar campo de razón social
                if (dir.dni_ruc_direccion && dir.dni_ruc_direccion.length === 11) {
                    $('#razon-social-group').show();
                    $('#address_razon_social').prop('required', true);
                }
                
                // Cargar cascada de ubigeo (departamento → provincia → distrito)
                
                // Esperar a que ubigeoDataProfile esté cargado
                const waitForUbigeo = setInterval(function() {
                    if (typeof ubigeoDataProfile !== 'undefined' && ubigeoDataProfile !== null) {
                        clearInterval(waitForUbigeo);
                        
                        // 1. Cargar departamento
                        $('#address_departamento').val(dir.departamento_direccion);
                        
                        // 2. Cargar provincias del departamento seleccionado
                        if (dir.departamento_direccion && ubigeoDataProfile) {
                            const departamento = ubigeoDataProfile.departamentos.find(d => d.nombre === dir.departamento_direccion);
                            
                            if (departamento && departamento.provincias) {
                                const selectProvincia = $('#address_provincia');
                                selectProvincia.html('<option value="">Seleccionar provincia</option>');
                                selectProvincia.prop('disabled', false);
                                
                                departamento.provincias.forEach(prov => {
                                    const option = $('<option></option>')
                                        .val(prov.nombre)
                                        .text(prov.nombre)
                                        .data('distritos', prov.distritos);
                                    selectProvincia.append(option);
                                });
                                
                                // 3. Seleccionar provincia guardada
                                selectProvincia.val(dir.provincia_direccion);
                                
                                // 4. Cargar distritos de la provincia seleccionada
                                if (dir.provincia_direccion) {
                                    const provinciaOption = selectProvincia.find('option:selected');
                                    const distritos = provinciaOption.data('distritos') || [];
                                    
                                    if (distritos.length > 0) {
                                        const selectDistrito = $('#address_distrito');
                                        selectDistrito.html('<option value="">Seleccionar distrito</option>');
                                        selectDistrito.prop('disabled', false);
                                        
                                        distritos.forEach(distrito => {
                                            const option = $('<option></option>')
                                                .val(distrito)
                                                .text(distrito);
                                            selectDistrito.append(option);
                                        });
                                        
                                        // 5. Seleccionar distrito guardado
                                        selectDistrito.val(dir.distrito_direccion);
                                        
                                    }
                                }
                            }
                        }
                    }
                }, 100); // Revisar cada 100ms hasta que ubigeoDataProfile esté disponible
                
                addressModalTitle.text('Editar Dirección');
                addressModal.modal('show');
            } else {
                showToast(data.error || 'No se pudo cargar la dirección', 'error');
            }
        } catch (error) {
            showToast('Error al cargar la dirección', 'error');
        } finally {
            saveAddressBtn.prop('disabled', false).html('<i class="fa fa-save"></i> Guardar Dirección');
        }
    }

    /**
     * Guardar dirección (crear o actualizar)
     */
    async function saveAddress() {
        
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
            showToast('Error al guardar la dirección', 'error');
            saveAddressBtn.prop('disabled', false).html('<i class="fa fa-save"></i> Guardar Dirección');
        }
    }

    /**
     * Establecer dirección como predeterminada
     */
    async function setDefaultAddress(addressId) {
        
        try {
            const baseUrl = (window.BASE_URL || '').replace(/\/+$/, '');
            const formData = new FormData();
            formData.append('id_direccion', addressId);
            
            const response = await fetch(baseUrl + '/app/actions/set_default_address.php', {
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
            showToast('Error al establecer dirección predeterminada', 'error');
        }
    }

    /**
     * Eliminar dirección (sin confirmación)
     */
    async function deleteAddress(addressId) {
        
        try {
            const baseUrl = (window.BASE_URL || '').replace(/\/+$/, '');
            const formData = new FormData();
            formData.append('id_direccion', addressId);
            
            const response = await fetch(baseUrl + '/app/actions/delete_address.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Recargar sin mostrar mensaje
                window.location.reload();
            } else {
                showToast(data.error || 'Error al eliminar la dirección', 'error');
            }
        } catch (error) {
            showToast('Error al eliminar la dirección', 'error');
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

    });

})();
