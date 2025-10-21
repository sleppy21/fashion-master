<?php
/**
 * URL HELPER
 * Funciones para manejar URLs de forma dinámica
 * Compatible con localhost, ngrok, Cloudflare Tunnel, y cualquier hosting
 */

/**
 * Convierte una ruta relativa a URL completa
 * @param string $path Ruta relativa (ej: "public/assets/img/products/imagen.jpg")
 * @return string URL completa
 */
function asset_url($path) {
    // Remover slashes iniciales
    $path = ltrim($path, '/');
    
    // Si ya es una URL completa (http:// o https://), devolverla tal cual
    if (preg_match('/^https?:\/\//', $path)) {
        return $path;
    }
    
    // Si es un placeholder externo, devolverlo tal cual
    if (strpos($path, 'placeholder') !== false || strpos($path, 'via.placeholder') !== false) {
        return $path;
    }
    
    // Remover /fashion-master/ si existe en el path (para evitar duplicados)
    $path = preg_replace('/^fashion-master\//', '', $path);
    
    // Construir URL completa
    return BASE_URL . '/' . $path;
}

/**
 * Convierte URL de imagen de producto
 * @param string $imagen_producto Nombre del archivo o ruta
 * @return string URL completa
 */
function product_image_url($imagen_producto) {
    if (empty($imagen_producto)) {
        return asset_url('public/assets/img/default-product.jpg');
    }
    
    // Si ya es una URL completa
    if (preg_match('/^https?:\/\//', $imagen_producto)) {
        return $imagen_producto;
    }
    
    // Si es placeholder
    if (strpos($imagen_producto, 'placeholder') !== false) {
        return $imagen_producto;
    }
    
    // Si ya tiene la ruta completa
    if (strpos($imagen_producto, 'public/assets') !== false) {
        return asset_url($imagen_producto);
    }
    
    // Solo nombre de archivo
    return asset_url('public/assets/img/products/' . $imagen_producto);
}

/**
 * Convierte URL de imagen de categoría
 * @param string $imagen_categoria Nombre del archivo o ruta
 * @return string URL completa
 */
function category_image_url($imagen_categoria) {
    if (empty($imagen_categoria)) {
        return asset_url('public/assets/img/default-category.png');
    }
    
    if (preg_match('/^https?:\/\//', $imagen_categoria)) {
        return $imagen_categoria;
    }
    
    if (strpos($imagen_categoria, 'placeholder') !== false) {
        return $imagen_categoria;
    }
    
    if (strpos($imagen_categoria, 'public/assets') !== false) {
        return asset_url($imagen_categoria);
    }
    
    return asset_url('public/assets/img/categories/' . $imagen_categoria);
}

/**
 * Convierte URL de imagen de marca
 * @param string $imagen_marca Nombre del archivo o ruta
 * @return string URL completa
 */
function brand_image_url($imagen_marca) {
    if (empty($imagen_marca)) {
        return asset_url('public/assets/img/default-brand.png');
    }
    
    if (preg_match('/^https?:\/\//', $imagen_marca)) {
        return $imagen_marca;
    }
    
    if (strpos($imagen_marca, 'placeholder') !== false) {
        return $imagen_marca;
    }
    
    if (strpos($imagen_marca, 'public/assets') !== false) {
        return asset_url($imagen_marca);
    }
    
    return asset_url('public/assets/img/brands/' . $imagen_marca);
}

/**
 * Convierte URL de avatar de usuario
 * @param string $imagen_usuario Nombre del archivo o ruta
 * @return string URL completa
 */
function user_avatar_url($imagen_usuario) {
    if (empty($imagen_usuario)) {
        return asset_url('public/assets/img/default-avatar.png');
    }
    
    if (preg_match('/^https?:\/\//', $imagen_usuario)) {
        return $imagen_usuario;
    }
    
    if (strpos($imagen_usuario, 'placeholder') !== false) {
        return $imagen_usuario;
    }
    
    if (strpos($imagen_usuario, 'public/assets') !== false) {
        return asset_url($imagen_usuario);
    }
    
    return asset_url('public/assets/img/users/' . $imagen_usuario);
}
?>
