<!-- Avatar Crop Modal -->
<div id="avatar-crop-modal" class="avatar-crop-modal hidden">
    <div class="avatar-crop-overlay"></div>
    <div class="avatar-crop-content">
        <div class="avatar-crop-header">
            <h3><i class="fa fa-camera"></i> Recortar Foto de Perfil</h3>
            <button class="avatar-crop-close" id="closeCropModal">
                <i class="fa fa-times"></i>
            </button>
        </div>
        
        <div class="avatar-crop-body">
            <!-- Área de recorte -->
            <div class="crop-container">
                <div id="crop-image"></div>
            </div>
            
            <!-- Información -->
            <div class="crop-info">
                <i class="fa fa-info-circle"></i>
                <p>Arrastra y ajusta la imagen para seleccionar el área que deseas usar como foto de perfil</p>
            </div>
        </div>
        
        <div class="avatar-crop-footer">
            <button type="button" class="btn btn-cancel">
                <i class="fa fa-times"></i> Cancelar
            </button>
            <button type="button" class="btn btn-upload">
                <i class="fa fa-upload"></i> Subir Avatar
            </button>
        </div>
    </div>
</div>
