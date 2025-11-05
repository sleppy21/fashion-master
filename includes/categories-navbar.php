<?php
/**
 * Componente: Navbar de Categorías (carousel horizontal)
 * Uso: include 'includes/categories-navbar.php';
 * Obtiene categorías activas de la BD y las muestra en un navbar horizontal deslizable.
 */
require_once __DIR__ . '/../config/conexion.php';

// Consulta categorías activas (orden alfabético)
$stmt = $conn->prepare("SELECT id_categoria, nombre_categoria FROM categoria WHERE status_categoria = 1 AND estado_categoria = 'activo' ORDER BY nombre_categoria ASC");
$stmt->execute();
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
