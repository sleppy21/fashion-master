<?php
session_start();
header('Content-Type: application/json');

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'No autorizado. Por favor inicia sesión.'
    ]);
    exit;
}

require_once __DIR__ . '/../../config/conexion.php';

$id_usuario = $_SESSION['user_id'];

// Log para debugging
error_log("Avatar Upload - User ID: " . $id_usuario);
error_log("Avatar Upload - FILES: " . print_r($_FILES, true));

try {
    // Verificar que se haya enviado un archivo
    if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
        $errorMessage = 'No se recibió ningún archivo';
        if (isset($_FILES['avatar']['error'])) {
            $errorMessage .= ' (Error code: ' . $_FILES['avatar']['error'] . ')';
        }
        throw new Exception($errorMessage);
    }

    $file = $_FILES['avatar'];

    // Validar tipo de archivo
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) {
        throw new Exception('Tipo de archivo no permitido. Solo se permiten imágenes JPG, PNG o GIF');
    }

    // Validar tamaño (5MB máximo)
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception('El archivo es muy grande. Tamaño máximo: 5MB');
    }

    // Crear directorio si no existe
    $uploadDir = __DIR__ . '/../../public/assets/img/profiles/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Obtener avatar actual para eliminarlo después
    $queryAvatar = "SELECT avatar_usuario FROM usuario WHERE id_usuario = ?";
    $currentAvatar = executeQuery($queryAvatar, [$id_usuario]);
    $oldAvatarPath = null;

    if ($currentAvatar && isset($currentAvatar[0]['avatar_usuario'])) {
        $oldAvatar = $currentAvatar[0]['avatar_usuario'];
        
        // Si el avatar no es el por defecto y es solo un nombre de archivo (no una ruta completa)
        if ($oldAvatar !== 'default-avatar.png' && 
            $oldAvatar !== 'public/assets/img/profiles/default-avatar.png' &&
            strpos($oldAvatar, 'public/assets/img/profiles/') === false) {
            $oldAvatarPath = $uploadDir . $oldAvatar;
        }
        // Si es una ruta completa diferente al default
        elseif (strpos($oldAvatar, 'public/assets/img/profiles/') !== false &&
                $oldAvatar !== 'public/assets/img/profiles/default-avatar.png') {
            // Extraer solo el nombre del archivo
            $oldFileName = basename($oldAvatar);
            $oldAvatarPath = $uploadDir . $oldFileName;
        }
    }

    // Generar nombre único para el archivo
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    if (empty($extension)) {
        // Si no tiene extensión, usar la del mime type
        $extensionMap = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif'
        ];
        $extension = $extensionMap[$mimeType] ?? 'jpg';
    }

    $newFileName = 'avatar_' . $id_usuario . '_' . time() . '.' . $extension;
    $uploadPath = $uploadDir . $newFileName;

    // Mover archivo
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        throw new Exception('Error al guardar el archivo en el servidor');
    }
    
    error_log("Avatar Upload - File saved: " . $uploadPath);

    // Actualizar base de datos
    $query = "UPDATE usuario SET avatar_usuario = ? WHERE id_usuario = ?";
    $result = executeQuery($query, [$newFileName, $id_usuario]);

    if ($result === false) {
        // Si falla la actualización, eliminar el archivo subido
        if (file_exists($uploadPath)) {
            unlink($uploadPath);
        }
        throw new Exception('Error al actualizar la base de datos');
    }
    
    error_log("Avatar Upload - Database updated");

    // Eliminar avatar anterior si existe y no es el default
    if ($oldAvatarPath && file_exists($oldAvatarPath)) {
        unlink($oldAvatarPath);
        error_log("Avatar Upload - Old avatar deleted: " . $oldAvatarPath);
    }

    // Actualizar sesión
    $_SESSION['avatar_usuario'] = $newFileName;

    // URL completa del avatar
    $avatarUrl = 'public/assets/img/profiles/' . $newFileName;

    echo json_encode([
        'success' => true,
        'message' => 'Avatar actualizado correctamente',
        'avatar_url' => $avatarUrl,
        'avatar_filename' => $newFileName
    ]);
    
} catch (Exception $e) {
    error_log("Avatar Upload Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
