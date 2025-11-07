<?php
/**
 * Componente: Navbar de Categorías (carousel horizontal)
 * Uso: include 'includes/categories-navbar.php';
 * RECIBE $categorias desde shop.php (ya consultadas por ShopController)
 * 
 * @requires array $categorias - Array de categorías desde ShopController
 */

// ✅ OPTIMIZACIÓN: Ya no consulta la BD, recibe $categorias desde shop.php
// Fallback solo si no vienen categorías (compatibilidad)
if (!isset($categorias) || empty($categorias)) {
    require_once __DIR__ . '/../config/conexion.php';
    $stmt = $conn->prepare("SELECT id_categoria, nombre_categoria FROM categoria WHERE status_categoria = 1 AND estado_categoria = 'activo' ORDER BY nombre_categoria ASC");
    $stmt->execute();
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<nav class="categories-navbar-carousel">
    <div class="categories-navbar-wrapper">
        <ul class="categories-navbar-list" role="tablist">
            <li class="category-navbar-item active"><button type="button" data-id="0">Todos</button></li>
            <?php foreach ($categorias as $cat): ?>
                <li class="category-navbar-item">
                    <button type="button" data-id="<?= $cat['id_categoria'] ?>">
                        <?= htmlspecialchars($cat['nombre_categoria']) ?>
                    </button>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</nav>
