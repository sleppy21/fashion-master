<?php
/**
 * OBTENER PRODUCTOS FILTRADOS VÍA AJAX
 * Evita el refresh completo de la página
 */

session_start();
require_once __DIR__ . '/../../config/conexion.php';

header('Content-Type: application/json');

try {
    // Obtener filtros de la URL
    $filtro_categoria = isset($_GET['c']) ? (int)$_GET['c'] : (isset($_GET['categoria']) ? (int)$_GET['categoria'] : null);
    $filtro_genero = isset($_GET['g']) ? $_GET['g'] : (isset($_GET['genero']) ? $_GET['genero'] : null);
    $filtro_marca = isset($_GET['m']) ? (int)$_GET['m'] : (isset($_GET['marca']) ? (int)$_GET['marca'] : null);
    $filtro_precio_min = isset($_GET['pmin']) ? (float)$_GET['pmin'] : (isset($_GET['precio_min']) ? (float)$_GET['precio_min'] : 0);
    $filtro_precio_max = isset($_GET['pmax']) ? (float)$_GET['pmax'] : (isset($_GET['precio_max']) ? (float)$_GET['precio_max'] : 9999);
    $filtro_buscar = isset($_GET['q']) ? trim($_GET['q']) : (isset($_GET['buscar']) ? trim($_GET['buscar']) : '');
    $filtro_ordenar = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

    // Construir query con filtros
    $sql = "
        SELECT DISTINCT
            p.id_producto,
            p.nombre_producto,
            p.descripcion_producto,
            p.precio_producto,
            p.descuento_porcentaje_producto,
            p.stock_actual_producto,
            p.url_imagen_producto,
            p.genero_producto,
            m.nombre_marca,
            c.nombre_categoria,
            COALESCE(AVG(r.calificacion), 0) as calificacion_promedio,
            COALESCE(COUNT(DISTINCT r.id_resena), 0) as total_resenas,
            (p.precio_producto - (p.precio_producto * p.descuento_porcentaje_producto / 100)) as precio_final
        FROM producto p
        LEFT JOIN marca m ON p.id_marca = m.id_marca
        LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
        LEFT JOIN resena r ON p.id_producto = r.id_producto AND r.aprobada = 1
        WHERE p.status_producto = 1
    ";

    $params = [];

    if ($filtro_categoria) {
        $sql .= " AND p.id_categoria = ?";
        $params[] = $filtro_categoria;
    }

    if ($filtro_genero && $filtro_genero !== 'all') {
        $sql .= " AND p.genero_producto = ?";
        $params[] = $filtro_genero;
    }

    if ($filtro_marca) {
        $sql .= " AND p.id_marca = ?";
        $params[] = $filtro_marca;
    }

    if (!empty($filtro_buscar)) {
        $sql .= " AND (p.nombre_producto LIKE ? OR p.descripcion_producto LIKE ? OR m.nombre_marca LIKE ?)";
        $search_term = "%{$filtro_buscar}%";
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
    }

    $sql .= " GROUP BY p.id_producto";
    $sql .= " HAVING (p.precio_producto - (p.precio_producto * p.descuento_porcentaje_producto / 100)) BETWEEN ? AND ?";
    $params[] = $filtro_precio_min;
    $params[] = $filtro_precio_max;

    // Agregar ordenamiento según el filtro seleccionado
    switch($filtro_ordenar) {
        case 'price_asc':
            $sql .= " ORDER BY precio_final ASC";
            break;
        case 'price_desc':
            $sql .= " ORDER BY precio_final DESC";
            break;
        case 'name_asc':
            $sql .= " ORDER BY p.nombre_producto ASC";
            break;
        case 'name_desc':
            $sql .= " ORDER BY p.nombre_producto DESC";
            break;
        case 'newest':
            $sql .= " ORDER BY p.id_producto DESC";
            break;
        case 'rating':
            $sql .= " ORDER BY calificacion_promedio DESC, total_resenas DESC";
            break;
        default:
            $sql .= " ORDER BY p.id_producto DESC";
            break;
    }

    $productos = executeQuery($sql, $params);

    // Obtener favoritos del usuario si está logueado
    $favoritos_ids = [];
    if (isset($_SESSION['user_id'])) {
        $favoritos = executeQuery("
            SELECT id_producto 
            FROM favorito 
            WHERE id_usuario = ?
        ", [$_SESSION['user_id']]);
        
        if ($favoritos && !empty($favoritos)) {
            $favoritos_ids = array_column($favoritos, 'id_producto');
        }
    }

    // Generar HTML de productos
    ob_start();
    
    if (!empty($productos)):
        foreach($productos as $producto):
            $precio_original = $producto['precio_producto'];
            $tiene_descuento = $producto['descuento_porcentaje_producto'] > 0;
            $precio_final = $precio_original;
            if($tiene_descuento) {
                $precio_final = $precio_original - ($precio_original * $producto['descuento_porcentaje_producto'] / 100);
            }
            
            $imagen_url = !empty($producto['url_imagen_producto']) ? $producto['url_imagen_producto'] : 'public/assets/img/default-product.jpg';
            $sin_stock = $producto['stock_actual_producto'] <= 0;
            $es_favorito = in_array($producto['id_producto'], $favoritos_ids);
    ?>
        <div class="grid-item col-lg-4 col-md-6 col-6">
            <div class="product__item <?php echo $tiene_descuento ? 'sale' : ''; ?>">
                <div class="product__item__pic set-bg product-image-clickable" 
                     data-setbg="<?php echo htmlspecialchars($imagen_url); ?>"
                     data-id="<?php echo $producto['id_producto']; ?>"
                     data-product-url="product-details.php?id=<?php echo $producto['id_producto']; ?>"
                     style="background-image: url('<?php echo htmlspecialchars($imagen_url); ?>'); background-size: cover; background-position: center; cursor: pointer;">
                    <?php if($sin_stock): ?>
                        <div class="label stockout">Sin stock</div>
                    <?php elseif($tiene_descuento): ?>
                        <div class="label sale">-<?php echo round($producto['descuento_porcentaje_producto']); ?>%</div>
                    <?php endif; ?>
                    <ul class="product__hover">
                        <li><a href="product-details.php?id=<?php echo $producto['id_producto']; ?>" class="view-details-btn"><span class="icon_search"></span></a></li>
                        <li>
                            <a href="#" class="add-to-favorites <?php echo $es_favorito ? 'active' : ''; ?>" 
                               data-id="<?php echo $producto['id_producto']; ?>">
                               <span class="icon_heart<?php echo $es_favorito ? '' : '_alt'; ?>"></span>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="add-to-cart" data-id="<?php echo $producto['id_producto']; ?>" 
                               <?php echo $sin_stock ? 'style="opacity:0.5;" data-disabled="true"' : ''; ?>>
                               <span class="icon_bag_alt"></span>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="product__item__text">
                    <h6><span style="cursor: pointer;" onclick="window.location.href='product-details.php?id=<?php echo $producto['id_producto']; ?>'">
                        <?php echo htmlspecialchars($producto['nombre_producto']); ?>
                    </span></h6>
                    <div class="rating">
                        <?php 
                        $calificacion = round($producto['calificacion_promedio']);
                        $total_resenas = $producto['total_resenas'];
                        for($i = 1; $i <= 5; $i++): 
                            if($i <= $calificacion): ?>
                                <i class="fa fa-star"></i>
                            <?php else: ?>
                                <i class="fa fa-star-o"></i>
                            <?php endif;
                        endfor; ?>
                        <?php if($total_resenas > 0): ?>
                            <span style="font-size: 11px; color: #999; margin-left: 5px;">(<?php echo $total_resenas; ?>)</span>
                        <?php endif; ?>
                    </div>
                    <div class="product__price">
                        $<?php echo number_format($precio_final, 2); ?>
                        <?php if($tiene_descuento): ?>
                            <span>$<?php echo number_format($precio_original, 2); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php
        endforeach;
    else:
    ?>
        <div class="col-12">
            <div class="alert alert-info text-center">
                <i class="fa fa-info-circle"></i> No se encontraron productos con los filtros seleccionados.
            </div>
        </div>
    <?php
    endif;
    
    $html = ob_get_clean();

    echo json_encode([
        'success' => true,
        'html' => $html,
        'count' => count($productos)
    ]);

} catch (Exception $e) {
    error_log("Error en get_products_filtered.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al filtrar productos: ' . $e->getMessage()
    ]);
}
