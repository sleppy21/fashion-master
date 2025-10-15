<?php
/**
 * BREADCRUMB DINÁMICO CON SEGUIMIENTO DE NAVEGACIÓN
 * Genera breadcrumbs que reflejan la ruta real del usuario
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Determinar la página actual
$current_page = basename($_SERVER['PHP_SELF'], '.php');

// Configuración de páginas (metadata para cada página)
$page_metadata = [
    'index' => [
        'text' => '<i class="fa fa-home"></i> Inicio',
        'url' => './index.php',
        'order' => 0  // Orden en el flujo de compra
    ],
    'shop' => [
        'text' => 'Tienda',
        'url' => './shop.php',
        'order' => 1
    ],
    'product-details' => [
        'text' => 'Detalles del Producto',
        'url' => null,  // No enlazable (varía por producto)
        'order' => 2
    ],
    'cart' => [
        'text' => 'Carrito',
        'url' => './cart.php',
        'order' => 3
    ],
    'checkout' => [
        'text' => 'Checkout',
        'url' => './checkout.php',
        'order' => 4
    ],
    'order-confirmation' => [
        'text' => 'Confirmación',
        'url' => null,  // No enlazable (una sola vez)
        'order' => 5
    ]
];

// Inicializar historial de navegación si no existe
if (!isset($_SESSION['breadcrumb_trail'])) {
    $_SESSION['breadcrumb_trail'] = [];
}

// Limpiar historial si el usuario vuelve al inicio
if ($current_page === 'index') {
    $_SESSION['breadcrumb_trail'] = ['index'];
} else {
    // Verificar si la página ya existe en el historial
    $page_index = array_search($current_page, $_SESSION['breadcrumb_trail']);
    
    if ($page_index !== false) {
        // Si existe, eliminar desde esa posición hasta el final (eliminar loops)
        $_SESSION['breadcrumb_trail'] = array_slice($_SESSION['breadcrumb_trail'], 0, $page_index + 1);
    } else {
        // Si no existe, agregarla al final
        $_SESSION['breadcrumb_trail'][] = $current_page;
        
        // Limitar el historial a las últimas 10 páginas (para no crecer infinitamente)
        if (count($_SESSION['breadcrumb_trail']) > 10) {
            $_SESSION['breadcrumb_trail'] = array_slice($_SESSION['breadcrumb_trail'], -10);
        }
    }
}

// Generar links del breadcrumb basado en el historial
$breadcrumb_links = [];

// Limitar a las últimas 5 páginas visitadas (más Inicio = 6 total)
$max_pages = 5;
$trail = $_SESSION['breadcrumb_trail'];
$trail_to_show = count($trail) > $max_pages ? array_slice($trail, -$max_pages) : $trail;

// Siempre empezar con Inicio (si no estamos en inicio)
if ($current_page !== 'index') {
    $breadcrumb_links[] = [
        'url' => './index.php',
        'text' => '<i class="fa fa-home"></i> Inicio',
        'active' => false
    ];
}

// Agregar páginas del historial (solo las últimas 5)
foreach ($trail_to_show as $page) {
    if ($page === 'index') continue; // Ya agregado
    
    $metadata = $page_metadata[$page] ?? null;
    if (!$metadata) continue;
    
    $is_current = ($page === $current_page);
    
    $breadcrumb_links[] = [
        'url' => $is_current ? null : $metadata['url'],
        'text' => $metadata['text'],
        'active' => $is_current
    ];
}

// Crear configuración compatible con el formato anterior
$config = ['links' => $breadcrumb_links];
?>

<!-- Breadcrumb Begin -->
<div class="breadcrumb-option">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="breadcrumb__links">
                    <?php foreach($config['links'] as $link): ?>
                        <?php if ($link['active']): ?>
                            <span><?php echo $link['text']; ?></span>
                        <?php else: ?>
                            <a href="<?php echo $link['url']; ?>"><?php echo $link['text']; ?></a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Breadcrumb End -->
