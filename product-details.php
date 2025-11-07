<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'config/conexion.php';
require_once 'config/config.php';

// Verificar si el usuario está logueado
$usuario_logueado = null;
if (isset($_SESSION['user_id'])) {
    try {
        $usuario_resultado = executeQuery("SELECT * FROM usuario WHERE id_usuario = ? AND status_usuario = 1", [$_SESSION['user_id']]);
        $usuario_logueado = $usuario_resultado && !empty($usuario_resultado) ? $usuario_resultado[0] : null;
    } catch(Exception $e) {
        error_log("Error al obtener usuario: " . $e->getMessage());
    }
}

// Obtener contadores para el header
$cart_count = 0;
$favorites_count = 0;
$notifications_count = 0;

if ($usuario_logueado) {
    try {
        $cart = executeQuery("SELECT COUNT(*) as total FROM carrito WHERE id_usuario = ?", [$usuario_logueado['id_usuario']]);
        $cart_count = $cart && !empty($cart) ? (int)$cart[0]['total'] : 0;
        
        $favorites = executeQuery("SELECT COUNT(*) as total FROM favorito WHERE id_usuario = ?", [$usuario_logueado['id_usuario']]);
        $favorites_count = $favorites && !empty($favorites) ? (int)$favorites[0]['total'] : 0;
        
        $notifications = executeQuery("SELECT COUNT(*) as total FROM notificacion WHERE id_usuario = ? AND leida_notificacion = 0 AND estado_notificacion = 'activo'", [$usuario_logueado['id_usuario']]);
        $notifications_count = ($notifications && count($notifications) > 0) ? ($notifications[0]['total'] ?? 0) : 0;
    } catch(Exception $e) {
        error_log("Error al obtener contadores: " . $e->getMessage());
    }
}

// Obtener categorías para el menú
$categorias = [];
try {
    $categorias_resultado = executeQuery("SELECT id_categoria, nombre_categoria FROM categoria WHERE status_categoria = 1 ORDER BY id_categoria ASC LIMIT 5");
    $categorias = $categorias_resultado ? $categorias_resultado : [];
} catch(Exception $e) {
    error_log("Error al obtener categorías: " . $e->getMessage());
}

// Obtener marcas para el menú
$marcas = [];
try {
    $marcas_resultado = executeQuery("SELECT id_marca, nombre_marca FROM marca WHERE status_marca = 1 ORDER BY nombre_marca ASC");
    $marcas = $marcas_resultado ? $marcas_resultado : [];
} catch(Exception $e) {
    error_log("Error al obtener marcas: " . $e->getMessage());
}

// Obtener favoritos del usuario (IDs)
$favoritos_ids = [];
if ($usuario_logueado) {
    try {
        $favoritos = executeQuery("SELECT id_producto FROM favorito WHERE id_usuario = ?", [$usuario_logueado['id_usuario']]);
        if($favoritos) {
            foreach($favoritos as $fav) {
                $favoritos_ids[] = $fav['id_producto'];
            }
        }
    } catch(Exception $e) {
        error_log("Error al obtener favoritos: " . $e->getMessage());
    }
}

// Obtener ID del producto
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    header('Location: shop.php');
    exit;
}

// Obtener información del producto usando executeQuery (PDO)
$query = "SELECT p.*, c.nombre_categoria, m.nombre_marca,
          ROUND(p.precio_producto * (1 - p.descuento_porcentaje_producto/100), 2) as precio_final,
          (SELECT AVG(calificacion) FROM resena WHERE id_producto = p.id_producto AND aprobada = 1) as rating_promedio,
          (SELECT COUNT(*) FROM resena WHERE id_producto = p.id_producto AND aprobada = 1) as total_resenas
          FROM producto p
          LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
          LEFT JOIN marca m ON p.id_marca = m.id_marca
          WHERE p.id_producto = ? AND p.status_producto = 1";

$resultado = executeQuery($query, [$product_id]);
$producto = $resultado && !empty($resultado) ? $resultado[0] : null;

if (!$producto) {
    header('Location: shop.php');
    exit;
}

// Verificar si el producto actual está en favoritos
$is_favorite = in_array($producto['id_producto'], $favoritos_ids);

// Verificar si el usuario puede escribir reseñas (admin o ha comprado el producto)
$puede_escribir_resena = false;
if ($usuario_logueado) {
    // Si es admin, puede escribir reseña
    if (isset($usuario_logueado['rol_usuario']) && $usuario_logueado['rol_usuario'] === 'admin') {
        $puede_escribir_resena = true;
    } else {
        // Si no es admin, verificar si ha comprado el producto
        try {
            $ha_comprado = executeQuery("
                SELECT COUNT(*) as total 
                FROM detalle_pedido pd
                INNER JOIN pedido p ON pd.id_pedido = p.id_pedido
                WHERE p.id_usuario = ? 
                AND pd.id_producto = ?
                AND p.estado_pedido IN ('entregado', 'completado')
            ", [$usuario_logueado['id_usuario'], $producto['id_producto']]);
            
            if ($ha_comprado && !empty($ha_comprado) && $ha_comprado[0]['total'] > 0) {
                $puede_escribir_resena = true;
            }
        } catch(Exception $e) {
            error_log("Error al verificar compra: " . $e->getMessage());
        }
    }
}

// Verificar si el producto actual está en el carrito y obtener cantidad
$cart_quantity = 0;
$is_in_cart = false;
if ($usuario_logueado) {
    try {
        $cart_check = executeQuery(
            "SELECT cantidad_carrito FROM carrito WHERE id_usuario = ? AND id_producto = ?", 
            [$usuario_logueado['id_usuario'], $producto['id_producto']]
        );
        if ($cart_check && !empty($cart_check)) {
            $is_in_cart = true;
            $cart_quantity = $cart_check[0]['cantidad_carrito'];
        }
    } catch(Exception $e) {
        error_log("Error al verificar carrito: " . $e->getMessage());
    }
}

// Obtener reseñas del producto usando executeQuery (PDO)
$query_reviews = "SELECT r.*, u.nombre_usuario, u.apellido_usuario, u.avatar_usuario
                  FROM resena r
                  INNER JOIN usuario u ON r.id_usuario = u.id_usuario
                  WHERE r.id_producto = ? AND r.aprobada = 1
                  ORDER BY r.fecha_creacion DESC";
$resenas_resultado = executeQuery($query_reviews, [$product_id]);
$resenas = $resenas_resultado ? $resenas_resultado : [];

// Obtener los likes del usuario actual (si está logueado)
$user_likes = [];
if ($usuario_logueado) {
    $query_likes = "SELECT id_resena FROM resena_likes WHERE id_usuario = ?";
    $likes_resultado = executeQuery($query_likes, [$usuario_logueado['id_usuario']]);
    $user_likes = array_column($likes_resultado, 'id_resena');
}

// Obtener productos relacionados (misma categoría) usando executeQuery (PDO)
$query_related = "SELECT p.*, 
                  ROUND(p.precio_producto * (1 - p.descuento_porcentaje_producto/100), 2) as precio_final,
                  (SELECT AVG(calificacion) FROM resena WHERE id_producto = p.id_producto AND aprobada = 1) as calificacion_promedio,
                  (SELECT COUNT(*) FROM resena WHERE id_producto = p.id_producto AND aprobada = 1) as total_resenas
                  FROM producto p
                  WHERE p.id_categoria = ? AND p.id_producto != ? AND p.status_producto = 1
                  ORDER BY RAND()
                  LIMIT 8";
$productos_relacionados = executeQuery($query_related, [$producto['id_categoria'], $product_id]);
$productos_relacionados = $productos_relacionados ? $productos_relacionados : [];

// Obtener categorías para el navbar
$query_categorias = "SELECT id_categoria, nombre_categoria 
                     FROM categoria 
                     WHERE status_categoria = 1 AND estado_categoria = 'activo' 
                     ORDER BY nombre_categoria ASC";
$categorias = executeQuery($query_categorias, []);
$categorias = $categorias ? $categorias : [];

$page_title = htmlspecialchars($producto['nombre_producto']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Fashion Store</title>
    
    <script>
        // BASE_URL sin barra final para evitar duplicados
        window.BASE_URL = '<?= rtrim(BASE_URL, "/") ?>';
        // Verificar y corregir protocolo si es necesario
        if (window.location.protocol === 'https:' && window.BASE_URL.startsWith('http:')) {
            window.BASE_URL = window.BASE_URL.replace('http:', 'https:');
        }
    </script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cookie&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="public/assets/css/bootstrap.min.css" type="text/css">
    
    <!-- Font Awesome 6.4.0 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- Modals & Dark Mode -->
    <link rel="stylesheet" href="public/assets/css/components/modals.css" type="text/css">
    <link rel="stylesheet" href="public/assets/css/components/categories-navbar.css?v=<?= time() ?>" type="text/css">
    <link rel="stylesheet" href="public/assets/css/dark-mode/dark-mode.css" type="text/css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="public/assets/css/product-details/product-details.css?v=<?= time() ?>">
    <link rel="stylesheet" href="public/assets/css/shop/product-cards-modern.css?v=<?= time() ?>">
</head>
<body>
    
    <?php include 'includes/header-section.php'; ?>
    
    <!-- Breadcrumb -->
    <?php include 'includes/breadcrumb.php'; ?>

    <!-- Product Details Section -->
    <div class="product-details-section">
        <div class="container">
            <div class="row">
                
                <!-- Columna Izquierda - Imagen del Producto -->
                <div class="col-lg-6 col-md-12 mb-4">
                    <div class="product-image-container">
                        <div class="main-image-wrapper">
                            <?php if ($producto['descuento_porcentaje_producto'] > 0): ?>
                                <span class="discount-badge">-<?= intval($producto['descuento_porcentaje_producto']) ?>%</span>
                            <?php endif; ?>
                            
                            <img id="mainProductImage" 
                                 src="<?= htmlspecialchars($producto['url_imagen_producto']) ?>" 
                                 alt="<?= htmlspecialchars($producto['nombre_producto']) ?>"
                                 class="img-fluid main-product-image">
                            
                            <div class="image-zoom-lens"></div>
                        </div>
                        
                        <!-- Thumbnails (si hay múltiples imágenes, por ahora solo mostramos la principal) -->
                        <div class="thumbnail-gallery">
                            <div class="thumbnail-item active">
                                <img src="<?= htmlspecialchars($producto['url_imagen_producto']) ?>" 
                                     alt="Thumbnail 1" 
                                     class="img-fluid">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna Derecha - Información del Producto -->
                <div class="col-lg-6 col-md-12">
                    <div class="product-info-container">
                        
                        <!-- Categoría y Código -->
                        <div class="product-meta">
                            <span class="product-category-badge">
                                <i class="fas fa-tag"></i> <?= htmlspecialchars($producto['nombre_categoria']) ?>
                            </span>
                            <?php if ($producto['nombre_marca']): ?>
                                <span class="product-brand-badge">
                                    <i class="fas fa-award"></i> <?= htmlspecialchars($producto['nombre_marca']) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Título del Producto -->
                        <h1 class="product-title"><?= htmlspecialchars($producto['nombre_producto']) ?></h1>

                        <!-- Rating -->
                        <div class="product-rating-section">
                            <div class="stars-display">
                                <?php
                                $rating = $producto['rating_promedio'] ?? 0;
                                $fullStars = floor($rating);
                                $hasHalfStar = ($rating - $fullStars) >= 0.5;
                                
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $fullStars) {
                                        echo '<i class="fas fa-star star-filled"></i>';
                                    } elseif ($i == $fullStars + 1 && $hasHalfStar) {
                                        echo '<i class="fas fa-star-half-alt star-filled"></i>';
                                    } else {
                                        echo '<i class="far fa-star star-empty"></i>';
                                    }
                                }
                                ?>
                            </div>
                            <span class="rating-text">
                                <?= number_format($rating, 1) ?> 
                                <span class="reviews-count">(<?= $producto['total_resenas'] ?> reseñas)</span>
                            </span>
                        </div>

                        <!-- Precio -->
                        <div class="product-pricing">
                            <?php if ($producto['descuento_porcentaje_producto'] > 0): ?>
                                <div class="price-wrapper">
                                    <span class="current-price">$<?= number_format($producto['precio_final'], 2) ?></span>
                                    <span class="original-price">$<?= number_format($producto['precio_producto'], 2) ?></span>
                                    <span class="savings-text">Ahorras $<?= number_format($producto['precio_producto'] - $producto['precio_final'], 2) ?></span>
                                </div>
                            <?php else: ?>
                                <span class="current-price">$<?= number_format($producto['precio_producto'], 2) ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Descripción Corta -->
                        <div class="product-short-description">
                            <p><?= nl2br(htmlspecialchars($producto['descripcion_producto'])) ?></p>
                        </div>

                        <!-- Stock Status -->
                        <?php if ($producto['stock_actual_producto'] > 0 && $producto['stock_actual_producto'] <= 10): ?>
                        <div class="stock-status-section">
                            <div class="stock-super-discount">
                                <i class="fas fa-bolt"></i>
                                <span>¡EN SUPER DESCUENTO! Solo quedan <?= $producto['stock_actual_producto'] ?> unidades</span>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Cantidad y Botones de Acción -->
                        <div class="product-actions">
                            <div class="quantity-selector">
                                <label for="quantity">Cantidad:</label>
                                <div class="qty-select-wrapper">
                                    <button class="qty-display-btn" 
                                            type="button"
                                            data-max="<?= $producto['stock_actual_producto'] ?>"
                                            data-current="<?= $cart_quantity > 0 ? $cart_quantity : 1 ?>">
                                        <span class="qty-value"><?= $cart_quantity > 0 ? $cart_quantity : 1 ?></span>
                                        <span class="arrow">▼</span>
                                    </button>
                                    <div class="qty-dropdown">
                                        <?php 
                                        $max_qty = min(30, max(1, $producto['stock_actual_producto']));
                                        $current_qty = $cart_quantity > 0 ? $cart_quantity : 1;
                                        for($i = 1; $i <= $max_qty; $i++): 
                                        ?>
                                            <div class="qty-option <?= ($i == $current_qty) ? 'selected' : '' ?>" 
                                                 data-value="<?= $i ?>">
                                                <?= $i ?>
                                            </div>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="action-buttons">
                                <button class="btn btn-add-to-cart-details <?= $is_in_cart ? 'in-cart' : '' ?>" 
                                        data-product-id="<?= $producto['id_producto'] ?>"
                                        data-in-cart="<?= $is_in_cart ? 'true' : 'false' ?>">
                                    <i class="fas fa-shopping-bag"></i>
                                    <span><?= $is_in_cart ? 'Agregado al Carrito' : 'Agregar al Carrito' ?></span>
                                </button>
                                
                                <button class="btn-favorite-details <?= $is_favorite ? 'active' : '' ?>" 
                                        data-product-id="<?= $producto['id_producto'] ?>" 
                                        title="<?= $is_favorite ? 'Quitar de favoritos' : 'Agregar a favoritos' ?>">
                                    <i class="<?= $is_favorite ? 'fas' : 'far' ?> fa-heart"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Share Buttons -->
                        <div class="product-share">
                            <span class="share-label">Compartir:</span>
                            <div class="share-buttons">
                                <a href="#" class="share-btn facebook" title="Facebook">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                                <a href="#" class="share-btn twitter" title="Twitter">
                                    <i class="fab fa-twitter"></i>
                                </a>
                                <a href="#" class="share-btn whatsapp" title="WhatsApp">
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                                <a href="#" class="share-btn pinterest" title="Pinterest">
                                    <i class="fab fa-pinterest-p"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Tabs Section: Reseñas y Galería -->
    <div class="product-tabs-section">
        <div class="container">
            
            <!-- Tabs Navigation Moderna -->
            <div class="modern-tabs-nav">
                <button class="modern-tab-btn active" data-tab="reviews">
                    <i class="fas fa-star"></i>
                    <span>Reseñas</span>
                    <span class="tab-badge"><?= $producto['total_resenas'] ?></span>
                </button>
                <button class="modern-tab-btn" data-tab="gallery">
                    <i class="fas fa-images"></i>
                    <span>Galería</span>
                </button>
            </div>

            <!-- Tabs Content -->
            <div class="modern-tabs-content">
                
                <!-- TAB 1: RESEÑAS -->
                <div class="modern-tab-pane active" data-tab-content="reviews">
                    <div class="reviews-container">
                        
                        <!-- Resumen de Calificaciones -->
                        <div class="reviews-summary">
                            <div class="rating-overview-compact">
                                <div class="rating-number"><?= number_format($rating, 1) ?></div>
                                <div class="rating-stars">
                                    <?php
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $fullStars) {
                                            echo '<i class="fas fa-star"></i>';
                                        } elseif ($i == $fullStars + 1 && $hasHalfStar) {
                                            echo '<i class="fas fa-star-half-alt"></i>';
                                        } else {
                                            echo '<i class="far fa-star"></i>';
                                        }
                                    }
                                    ?>
                                </div>
                                <div class="rating-total">(<?= $producto['total_resenas'] ?>)</div>
                            </div>

                            <div class="rating-breakdown">
                                <?php
                                // Obtener distribución de calificaciones
                                $query_dist = "SELECT calificacion, COUNT(*) as count 
                                              FROM resena 
                                              WHERE id_producto = ? AND aprobada = 1 
                                              GROUP BY calificacion 
                                              ORDER BY calificacion DESC";
                                $dist_resultado = executeQuery($query_dist, [$product_id]);
                                $distribution = [];
                                if ($dist_resultado) {
                                    foreach ($dist_resultado as $row) {
                                        $distribution[$row['calificacion']] = $row['count'];
                                    }
                                }
                                
                                for ($i = 5; $i >= 1; $i--) {
                                    $count = $distribution[$i] ?? 0;
                                    $percentage = $producto['total_resenas'] > 0 ? ($count / $producto['total_resenas']) * 100 : 0;
                                ?>
                                    <div class="rating-bar-item">
                                        <span class="stars-label"><?= $i ?> <i class="fas fa-star"></i></span>
                                        <div class="progress-bar-wrapper">
                                            <div class="progress">
                                                <div class="progress-bar" style="width: <?= $percentage ?>%"></div>
                                            </div>
                                        </div>
                                        <span class="count-label"><?= $count ?></span>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>

                        <!-- Lista de Reseñas -->
                        <div class="reviews-list">
                            <h3 class="reviews-list-title">Opiniones de clientes</h3>
                            
                            <?php if (!empty($resenas) && count($resenas) > 0): ?>
                                <?php foreach ($resenas as $review): 
                                    // Construir ruta del avatar
                                    $avatar_path = 'public/assets/img/default-avatar.png'; // Default
                                    
                                    if (!empty($review['avatar_usuario'])) {
                                        // Si el avatar ya tiene la ruta completa
                                        if (strpos($review['avatar_usuario'], 'public/assets/img/profiles/') !== false) {
                                            $avatar_path = $review['avatar_usuario'];
                                        } 
                                        // Si es solo el nombre del archivo
                                        elseif ($review['avatar_usuario'] !== 'default-avatar.png') {
                                            $avatar_path = 'public/assets/img/profiles/' . $review['avatar_usuario'];
                                        }
                                    }
                                ?>
                                    <div class="review-item">
                                        <div class="review-header">
                                            <div class="reviewer-info">
                                                <img src="<?= htmlspecialchars($avatar_path) ?>" 
                                                     alt="<?= htmlspecialchars($review['nombre_usuario']) ?>" 
                                                     class="reviewer-avatar"
                                                     onerror="this.src='public/assets/img/default-avatar.png'">
                                                <div class="reviewer-details">
                                                    <h4 class="reviewer-name">
                                                        <?= htmlspecialchars($review['nombre_usuario'] . ' ' . $review['apellido_usuario']) ?>
                                                    </h4>
                                                    <div class="review-meta">
                                                        <div class="review-stars">
                                                            <?php
                                                            for ($i = 1; $i <= 5; $i++) {
                                                                echo $i <= $review['calificacion'] 
                                                                    ? '<i class="fas fa-star"></i>' 
                                                                    : '<i class="far fa-star"></i>';
                                                            }
                                                            ?>
                                                        </div>
                                                        <span class="review-date">
                                                            <?= date('d/m/Y', strtotime($review['fecha_creacion'])) ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Menú de opciones (solo si es la reseña del usuario logueado) -->
                                            <?php if ($usuario_logueado && $usuario_logueado['id_usuario'] == $review['id_usuario']): ?>
                                                <div class="review-options-menu">
                                                    <button class="btn-review-options" data-review-id="<?= $review['id_resena'] ?>">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <div class="review-options-dropdown">
                                                        <button class="review-option-item btn-edit-review" 
                                                                data-review-id="<?= $review['id_resena'] ?>"
                                                                data-rating="<?= $review['calificacion'] ?>"
                                                                data-title="<?= htmlspecialchars($review['titulo'] ?? '') ?>"
                                                                data-comment="<?= htmlspecialchars($review['comentario']) ?>">
                                                            <i class="fas fa-edit"></i> Editar
                                                        </button>
                                                        <button class="review-option-item btn-delete-review" data-review-id="<?= $review['id_resena'] ?>">
                                                            <i class="fas fa-trash-alt"></i> Eliminar
                                                        </button>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php if (!empty($review['titulo'])): ?>
                                            <h5 class="review-title"><?= htmlspecialchars($review['titulo']) ?></h5>
                                        <?php endif; ?>
                                        
                                        <p class="review-comment"><?= nl2br(htmlspecialchars($review['comentario'])) ?></p>
                                        
                                        <div class="review-actions" data-review-id="<?= $review['id_resena'] ?>">
                                            <?php 
                                            $user_liked = in_array($review['id_resena'], $user_likes);
                                            $likes_count = $review['likes_count'] ?? 0;
                                            ?>
                                            <button class="review-action-btn btn-like-review <?= $user_liked ? 'liked' : '' ?>" 
                                                    data-review-id="<?= $review['id_resena'] ?>"
                                                    <?= !$usuario_logueado ? 'disabled title="Inicia sesión para marcar como útil"' : '' ?>>
                                                <i class="<?= $user_liked ? 'fas' : 'far' ?> fa-thumbs-up"></i> 
                                                Útil <span class="likes-count"><?= $likes_count > 0 ? "($likes_count)" : '' ?></span>
                                            </button>
                                            <button class="review-action-btn btn-report-review" 
                                                    data-review-id="<?= $review['id_resena'] ?>"
                                                    <?= !$usuario_logueado ? 'disabled title="Inicia sesión para reportar"' : '' ?>>
                                                <i class="far fa-flag"></i> Reportar
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="no-reviews">
                                    <i class="fas fa-comment-slash"></i>
                                    <p>Aún no hay reseñas para este producto.</p>
                                    <p class="text-muted">¡Sé el primero en compartir tu opinión!</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Botón para escribir reseña (solo si es admin o ha comprado) -->
                        <?php if ($puede_escribir_resena): ?>
                        <div class="write-review-section">
                            <button class="btn btn-write-review-modern">
                                <i class="fas fa-edit"></i>
                                <span>Escribir Reseña</span>
                            </button>
                        </div>
                        <?php else: ?>
                        <div class="write-review-section">
                            <div class="alert alert-info text-center" style="margin: 20px 0; padding: 15px; background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border: 1px solid #bae6fd; border-radius: 12px;">
                                <i class="fas fa-info-circle" style="color: #0284c7; margin-right: 8px;"></i>
                                <span style="color: #0369a1; font-weight: 500;">
                                    <?php if (!$usuario_logueado): ?>
                                        Debes <a href="login.php" style="color: #0284c7; text-decoration: underline;">iniciar sesión</a> para escribir una reseña
                                    <?php else: ?>
                                        Debes comprar este producto para poder escribir una reseña
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>


                <!-- TAB 2: GALERÍA -->
                <div class="modern-tab-pane" data-tab-content="gallery">
                    <div class="gallery-container">
                        <h3 class="gallery-title">Galería de Imágenes</h3>
                        
                        <div class="gallery-grid">
                            <!-- Por ahora solo mostramos la imagen principal -->
                            <div class="gallery-item">
                                <img src="<?= htmlspecialchars($producto['url_imagen_producto']) ?>" 
                                     alt="<?= htmlspecialchars($producto['nombre_producto']) ?>"
                                     class="img-fluid gallery-image">
                                <div class="gallery-overlay">
                                    <button class="btn-zoom" data-image="<?= htmlspecialchars($producto['url_imagen_producto']) ?>">
                                        <i class="fas fa-search-plus"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Aquí se pueden agregar más imágenes si existen en la BD -->
                            <!-- Placeholder para demostración -->
                            <div class="gallery-item placeholder">
                                <div class="placeholder-content">
                                    <i class="fas fa-camera"></i>
                                    <p>Más imágenes próximamente</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Productos Relacionados -->
    <div class="related-products-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Productos Relacionados</h2>
                <p class="section-subtitle">Otros productos que te pueden interesar</p>
            </div>

            <!-- Navbar de Categorías -->
            <?php include 'includes/categories-navbar.php'; ?>

            <div class="products-grid-modern">
                <div class="row" id="related-products-container">
                    <?php if (!empty($productos_relacionados) && count($productos_relacionados) > 0): ?>
                    <?php 
                    // Incluir función del componente
                    require_once 'app/views/components/product-card.php';
                    
                    // Obtener productos en carrito del usuario
                    $productos_en_carrito = [];
                    if ($usuario_logueado) {
                        try {
                            $carrito = executeQuery("SELECT id_producto FROM carrito WHERE id_usuario = ?", [$usuario_logueado['id_usuario']]);
                            if ($carrito) {
                                $productos_en_carrito = array_column($carrito, 'id_producto');
                            }
                        } catch(Exception $e) {
                            error_log("Error al obtener carrito: " . $e->getMessage());
                        }
                    }
                    
                    foreach ($productos_relacionados as $related): 
                        // Verificar si está en favoritos
                        $is_favorite = in_array($related['id_producto'], $favoritos_ids);
                        // Verificar si está en carrito
                        $in_cart = in_array($related['id_producto'], $productos_en_carrito);
                        // Pasar datos necesarios al componente
                        renderProductCard($related, $is_favorite, $usuario_logueado !== null, $in_cart);
                    endforeach; 
                    ?>
                <?php else: ?>
                    <p class="text-center w-100">No hay productos relacionados en este momento.</p>
                <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Zoom de Imagen -->
    <div class="modal fade" id="imageZoomModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <img id="zoomedImage" src="" alt="Producto" class="img-fluid">
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Reportar Reseña -->
    <div class="modal fade" id="reportReviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="report-reasons">
                        <div class="form-check report-option">
                            <input class="form-check-input" type="radio" name="reportReason" id="reason1" value="Contenido ofensivo o inapropiado">
                            <label class="form-check-label" for="reason1">
                                <span>Contenido ofensivo o inapropiado</span>
                            </label>
                        </div>
                        <div class="form-check report-option">
                            <input class="form-check-input" type="radio" name="reportReason" id="reason2" value="Spam o publicidad">
                            <label class="form-check-label" for="reason2">
                                <span>Spam o publicidad</span>
                            </label>
                        </div>
                        <div class="form-check report-option">
                            <input class="form-check-input" type="radio" name="reportReason" id="reason3" value="Información falsa o engañosa">
                            <label class="form-check-label" for="reason3">
                                <span>Información falsa o engañosa</span>
                            </label>
                        </div>
                        <div class="form-check report-option">
                            <input class="form-check-input" type="radio" name="reportReason" id="reason4" value="Lenguaje violento o amenazante">
                            <label class="form-check-label" for="reason4">
                                <span>Lenguaje violento o amenazante</span>
                            </label>
                        </div>
                        <div class="form-check report-option">
                            <input class="form-check-input" type="radio" name="reportReason" id="reason5" value="Otro motivo">
                            <label class="form-check-label" for="reason5">
                                <span>Otro motivo</span>
                            </label>
                        </div>
                    </div>
                    <div id="otherReasonContainer" style="display: none; margin-top: 12px;">
                        <textarea class="form-control" id="otherReasonText" rows="2" placeholder="Describe el motivo..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger btn-sm" id="confirmReportBtn">Enviar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Sheet para Escribir/Editar Reseña -->
    <div class="bottom-sheet" id="reviewBottomSheet">
        <div class="bottom-sheet-backdrop"></div>
        <div class="bottom-sheet-content">
            <div class="bottom-sheet-header">
                <h5 id="reviewSheetTitle">Escribe tu reseña</h5>
                <button type="button" class="close-bottom-sheet">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="bottom-sheet-body">
                <!-- ID de la reseña (oculto, para ediciones) -->
                <input type="hidden" id="reviewId" value="">
                
                <!-- Calificación con estrellas -->
                <div class="rating-input-group">
                    <label>Calificación:</label>
                    <div class="star-rating-input">
                        <input type="radio" name="rating" id="star5" value="5">
                        <label for="star5"><i class="fas fa-star"></i></label>
                        
                        <input type="radio" name="rating" id="star4" value="4">
                        <label for="star4"><i class="fas fa-star"></i></label>
                        
                        <input type="radio" name="rating" id="star3" value="3">
                        <label for="star3"><i class="fas fa-star"></i></label>
                        
                        <input type="radio" name="rating" id="star2" value="2">
                        <label for="star2"><i class="fas fa-star"></i></label>
                        
                        <input type="radio" name="rating" id="star1" value="1">
                        <label for="star1"><i class="fas fa-star"></i></label>
                    </div>
                </div>
                
                <!-- Título (opcional) -->
                <div class="form-group">
                    <label for="reviewTitle">Título (opcional)</label>
                    <input type="text" class="form-control" id="reviewTitle" placeholder="Resume tu experiencia">
                </div>
                
                <!-- Comentario -->
                <div class="form-group">
                    <label for="reviewComment">Comentario <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="reviewComment" rows="4" placeholder="Cuéntanos qué te pareció el producto..."></textarea>
                </div>
            </div>
            <div class="bottom-sheet-footer">
                <button type="button" class="btn btn-secondary" onclick="$('#reviewBottomSheet').removeClass('show'); $('body').css('overflow', 'auto');">Cancelar</button>
                <button type="button" class="btn btn-primary" id="submitReviewBtn">Enviar Reseña</button>
            </div>
        </div>
    </div>

    <!-- Chatbot ya se carga en header-section.php, no duplicar -->

    <!-- Bootstrap y jQuery ya se cargan en header-section.php, no duplicar -->
    
    <!-- Notificaciones Globales -->
    <script src="public/assets/js/header-globals/notificacion-global.js?v=<?= time() ?>"></script>
    
    <!-- Swipe Gestures para Bottom Sheets -->
    <script src="public/assets/js/header-globals/swipe-gestures.js?v=<?= time() ?>"></script>
    
    <!-- Product Loader para cargar productos dinámicamente -->
    <script src="public/assets/js/shop/product-loader.js?v=<?= time() ?>"></script>
    
    <!-- Masonry Layout para grid de productos -->
    <script src="public/assets/js/shop/masonry-layout.js?v=<?= time() ?>"></script>
    
    <!-- Real-time Updates ya se carga en header-section.php, no duplicar -->
    
    <!-- Image Adapter: PRIMERO - Ajusta altura de tarjetas (GLOBAL) -->
    <script src="public/assets/js/components/product-image-adapter.js?v=1.0"></script>
    
    <!-- Custom JS -->
    <script src="public/assets/js/product-details/product-details.js?v=<?= time() ?>"></script>
    
    <!-- Touch handler para móviles -->
    <script src="public/assets/js/components/product-touch-handler.js"></script>

</body>
</html>
