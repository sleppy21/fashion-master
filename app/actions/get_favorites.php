<?php
/**
 * GET FAVORITES - Obtener lista actualizada de favoritos
 * Para actualización en tiempo real del modal
 */

session_start();
header('Content-Type: application/json');

require_once '../../config/conexion.php';

// Verificar usuario logueado
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Usuario no autenticado'
    ]);
    exit;
}

$id_usuario = $_SESSION['user_id'];

try {
    // Obtener favoritos del usuario CON información de si están en el carrito
    $favoritos = executeQuery("
        SELECT p.id_producto, p.nombre_producto, p.precio_producto, p.url_imagen_producto,
               COALESCE(p.descuento_porcentaje_producto, 0) as descuento_porcentaje_producto,
               p.stock_actual_producto,
               f.fecha_agregado_favorito,
               CASE WHEN c.id_producto IS NOT NULL THEN 1 ELSE 0 END as en_carrito
        FROM favorito f
        INNER JOIN producto p ON f.id_producto = p.id_producto
        LEFT JOIN carrito c ON c.id_producto = p.id_producto AND c.id_usuario = ?
        WHERE f.id_usuario = ? AND p.status_producto = 1
        ORDER BY f.fecha_agregado_favorito DESC
    ", [$id_usuario, $id_usuario]);

    // Generar HTML
    ob_start();
    
    if (!empty($favoritos)):
        foreach($favoritos as $producto):
            $precio_original = $producto['precio_producto'];
            $descuento = $producto['descuento_porcentaje_producto'];
            $precio_final = $descuento > 0 ? $precio_original - ($precio_original * $descuento / 100) : $precio_original;
            $imagen_url = !empty($producto['url_imagen_producto']) ? $producto['url_imagen_producto'] : 'public/assets/img/default-product.jpg';
            $sin_stock = $producto['stock_actual_producto'] <= 0;
    ?>
    <div class="favorite-item" data-id="<?php echo $producto['id_producto']; ?>" style="display: flex; gap: 10px; padding: 10px; border-radius: 8px; margin-bottom: 8px; border: 1px solid; transition: all 0.3s ease;">
        <div class="favorite-image" 
             style="position: relative; width: 70px; height: 70px; flex-shrink: 0; border-radius: 6px; overflow: hidden; cursor: pointer;"
             onclick="window.location.href='product-details.php?id=<?php echo $producto['id_producto']; ?>';">
            <img src="<?php echo htmlspecialchars($imagen_url); ?>" alt="<?php echo htmlspecialchars($producto['nombre_producto']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
            <?php if($sin_stock): ?>
                <span class="stock-badge out" style="position: absolute; top: 5px; left: 5px; padding: 3px 8px; border-radius: 4px; font-size: 10px; font-weight: 600; text-transform: uppercase;">Sin stock</span>
            <?php elseif($descuento > 0): ?>
                <span class="stock-badge sale" style="position: absolute; top: 5px; left: 5px; padding: 3px 8px; border-radius: 4px; font-size: 10px; font-weight: 600; text-transform: uppercase;">-<?php echo round($descuento); ?>%</span>
            <?php endif; ?>
        </div>
        <div class="favorite-info" style="flex: 1; display: flex; flex-direction: column; gap: 4px;">
            <h6 style="margin: 0; font-size: 13px; font-weight: 600; line-height: 1.3;"><span class="favorite-product-name" style="cursor: pointer; transition: color 0.3s ease;" onclick="window.location.href='product-details.php?id=<?php echo $producto['id_producto']; ?>'"><?php echo htmlspecialchars($producto['nombre_producto']); ?></span></h6>
            <div class="favorite-price" style="display: flex; align-items: center; gap: 6px;">
                <span class="price-current" style="font-size: 15px; font-weight: 700;">$<?php echo number_format($precio_final, 2); ?></span>
                <?php if($descuento > 0): ?>
                    <span class="price-old" style="font-size: 12px; text-decoration: line-through;">$<?php echo number_format($precio_original, 2); ?></span>
                <?php endif; ?>
            </div>
            <small class="favorite-date" style="font-size: 10px; margin-top: auto;">Agregado: <?php echo date('d/m/Y', strtotime($producto['fecha_agregado_favorito'])); ?></small>
        </div>
        <div class="favorite-actions" style="display: flex; flex-direction: column; gap: 5px; justify-content: center;">
            <?php 
            $en_carrito = isset($producto['en_carrito']) && $producto['en_carrito'] == 1;
            $icono_carrito = $en_carrito ? 'fa-check-circle' : 'fa-cart-plus';
            $title_carrito = $sin_stock ? 'Sin stock' : ($en_carrito ? 'En carrito - Clic para quitar' : 'Agregar al carrito');
            ?>
            <button class="btn-favorite-cart <?php echo $en_carrito ? 'in-cart' : ''; ?>" 
                    data-id="<?php echo $producto['id_producto']; ?>" 
                    data-in-cart="<?php echo $en_carrito ? 'true' : 'false'; ?>"
                    <?php echo $sin_stock ? 'disabled' : ''; ?> 
                    title="<?php echo $title_carrito; ?>"
                    style="width: 32px; height: 32px; border: none; border-radius: 6px; cursor: <?php echo $sin_stock ? 'not-allowed' : 'pointer'; ?>; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; font-size: 13px;">
                <i class="fa <?php echo $icono_carrito; ?>"></i>
            </button>
            <button class="btn-favorite-remove" data-id="<?php echo $producto['id_producto']; ?>" title="Quitar de favoritos" style="width: 32px; height: 32px; border: none; border-radius: 6px; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; font-size: 13px;">
                <i class="fa fa-heart-broken"></i>
            </button>
        </div>
    </div>
    <?php
        endforeach;
    else:
    ?>
    <div class="favorites-empty" style="text-align: center; padding: 60px 20px;">
        <i class="fa fa-heart-o" style="font-size: 80px; margin-bottom: 20px; display: block;"></i>
        <p style="font-size: 16px; margin-bottom: 20px;">No tienes productos favoritos</p>
        <a href="shop.php" class="btn-shop-now" style="display: inline-block; padding: 10px 24px; text-decoration: none; border-radius: 20px; font-weight: 600; font-size: 13px; transition: all 0.3s ease;">Explorar productos</a>
    </div>
    <?php
    endif;
    
    $html = ob_get_clean();
    
    $count = count($favoritos);
    $count_text = $count == 1 ? '1 producto' : $count . ' productos';

    echo json_encode([
        'success' => true,
        'html' => $html,
        'count' => $count,
        'count_text' => $count_text
    ]);

} catch (Exception $e) {
    error_log("Error en get_favorites.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener favoritos: ' . $e->getMessage()
    ]);
}
?>
