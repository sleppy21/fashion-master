<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

/**
 * 📦 FUNCIÓN CENTRALIZADA PARA CALCULAR ESTADO DE STOCK
 * Esta es la ÚNICA fuente de verdad para determinar el estado del stock
 * Usa SOLO los valores de la base de datos sin fallbacks
 * 
 * @param array $producto - Array con stock_actual_producto y stock_minimo_producto
 * @return string - 'Agotado', 'Stock bajo' o 'Normal'
 */
function calcularEstadoStock($producto) {
    $stockActual = isset($producto['stock_actual_producto']) ? (int)$producto['stock_actual_producto'] : 0;
    $stockMinimo = isset($producto['stock_minimo_producto']) ? (int)$producto['stock_minimo_producto'] : null;
    
    // Prioridad 1: Stock en 0 = Agotado
    if ($stockActual == 0) {
        return 'Agotado';
    }
    
    // Prioridad 2: Stock <= stock_minimo (solo si stock_minimo existe y es > 0 en la BD)
    if ($stockMinimo !== null && $stockMinimo > 0 && $stockActual <= $stockMinimo) {
        return 'Stock bajo';
    }
    
    // Prioridad 3: Stock normal (por encima del mínimo o sin mínimo definido)
    return 'Normal';
}

/**
 * 🏷️ GENERAR CÓDIGO AUTOMÁTICO TIPO SLUG
 * Genera código en formato: CAM-NIKE-PAN (primeras 3 letras de producto-marca-categoría)
 * 
 * @param string $nombre - Nombre del producto
 * @param string $marca - Nombre de la marca
 * @param string $categoria - Nombre de la categoría
 * @param PDO $conn - Conexión a la base de datos
 * @return string - Código único generado
 */
function generarCodigoProducto($nombre, $marca, $categoria, $conn) {
    // Función auxiliar para obtener 3 primeras letras
    function getPrimeras3Letras($texto) {
        // Remover caracteres especiales y espacios
        $texto = preg_replace('/[^A-Za-z0-9\s]/', '', $texto);
        $texto = trim($texto);
        
        // Si es vacío, usar XXX
        if (empty($texto)) return 'XXX';
        
        // Obtener primera palabra
        $palabras = explode(' ', $texto);
        $palabra = $palabras[0];
        
        // Tomar primeras 3 letras (o menos si es más corta)
        $letras = strtoupper(substr($palabra, 0, 3));
        
        // Rellenar con X si es menor a 3 caracteres
        return str_pad($letras, 3, 'X', STR_PAD_RIGHT);
    }
    
    // Generar partes del código
    $parteNombre = getPrimeras3Letras($nombre);
    $parteMarca = getPrimeras3Letras($marca ?: 'MARCA');
    $parteCategoria = getPrimeras3Letras($categoria ?: 'CAT');
    
    // Código base
    $codigoBase = $parteNombre . '-' . $parteMarca . '-' . $parteCategoria;
    
    // Verificar si existe en la BD
    $stmt = $conn->prepare("SELECT COUNT(*) FROM producto WHERE codigo = ?");
    $stmt->execute([$codigoBase]);
    $existe = $stmt->fetchColumn();
    
    // Si existe, agregar número incremental
    if ($existe > 0) {
        $contador = 1;
        do {
            $codigoConNumero = $codigoBase . '-' . str_pad($contador, 3, '0', STR_PAD_LEFT);
            $stmt->execute([$codigoConNumero]);
            $existe = $stmt->fetchColumn();
            $contador++;
        } while ($existe > 0 && $contador < 1000);
        
        return $codigoConNumero;
    }
    
    return $codigoBase;
}

// Conexión directa a la base de datos
try {
    $host = 'localhost';
    $db_name = 'sleppystore';
    $username = 'root';
    $password = '';
    
    $dsn = "mysql:host=$host;dbname=$db_name;charset=utf8mb4";
    $conn = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    
    switch ($action) {
        case 'list':
            listProductos($conn);
            break;
        case 'get_categories':
            getCategorias($conn);
            break;
        case 'get_brands':
        case 'get_marcas': // Alias para compatibilidad
            getMarcas($conn);
            break;
        case 'get':
            getProducto($conn);
            break;
        case 'create':
            createProducto($conn);
            break;
        case 'update':
            updateProducto($conn);
            break;
        case 'delete':
            deleteProducto($conn);
            break;
        case 'toggle_status':
            toggleProductStatus($conn);
            break;
        case 'change_estado':
            changeProductEstado($conn);
            break;
        case 'update_stock':
            updateProductStock($conn);
            break;
        default:
            echo json_encode([
                'success' => false,
                'error' => 'Acción no válida',
                'action_received' => $action
            ]);
            break;
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}

function listProductos($conn) {
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 10);
    $offset = ($page - 1) * $limit;
    
    // Obtener parámetros de filtros y búsqueda
    $search = $_GET['search'] ?? '';
    $category = $_GET['category'] ?? '';
    $status = $_GET['status'] ?? ''; // Este filtro es SOLO para el campo 'estado' (activo/inactivo)
    $stock_filter = $_GET['stock_filter'] ?? '';
    $fecha = $_GET['fecha'] ?? ''; // Filtro por fecha de creación
    
    // Construir WHERE clause
    $where_conditions = [];
    $params = [];
    
    // FILTRO OBLIGATORIO: Solo productos NO eliminados (status_producto = 1)
    // ESTO NUNCA CAMBIA - SIEMPRE DEBE ESTAR ACTIVO
    $where_conditions[] = "p.status_producto = 1";
    
    // Filtro OPCIONAL por ESTADO (activo/inactivo)
    // Si $status está vacío = "Todos los estados" = muestra activos + inactivos
    // Si $status tiene valor = filtrar por ese estado específico
    if ($status !== '') {
        // Convertir número a texto para compatibilidad
        if ($status === '1') {
            $status = 'activo';
        } elseif ($status === '0') {
            $status = 'inactivo';
        }
        
        $where_conditions[] = "p.estado = ?";
        $params[] = $status;
    }
    
    // Filtro de búsqueda por nombre
    if (!empty($search)) {
        $where_conditions[] = "p.nombre_producto LIKE ?";
        $params[] = "%$search%";
    }
    
    // Filtro por categoría
    if (!empty($category)) {
        $where_conditions[] = "p.id_categoria = ?";
        $params[] = (int)$category;
    }
    
    // Filtro por marca
    $marca = $_GET['marca'] ?? '';
    if (!empty($marca)) {
        $where_conditions[] = "p.id_marca = ?";
        $params[] = (int)$marca;
    }
    
    // Filtro por stock
    if (!empty($stock_filter)) {
        switch ($stock_filter) {
            case 'agotado':
                $where_conditions[] = "p.stock_actual_producto = 0";
                break;
            case 'bajo':
                // Usar stock_minimo_producto de la BD (solo productos que tienen el campo definido)
                $where_conditions[] = "p.stock_actual_producto > 0 AND p.stock_actual_producto <= p.stock_minimo_producto";
                break;
        }
    }
    
    // Filtro por fecha de creación - Soporta rango "YYYY-MM-DD to YYYY-MM-DD"
    if (!empty($fecha)) {
        // Verificar si es un rango de fechas
        if (strpos($fecha, ' to ') !== false) {
            // Es un rango: "2025-01-01 to 2025-01-31"
            list($fecha_inicio, $fecha_fin) = explode(' to ', $fecha);
            $fecha_inicio = trim($fecha_inicio);
            $fecha_fin = trim($fecha_fin);
            
            $where_conditions[] = "DATE(p.fecha_creacion_producto) BETWEEN ? AND ?";
            $params[] = $fecha_inicio;
            $params[] = $fecha_fin;
        } else {
            // Es una fecha exacta
            $where_conditions[] = "DATE(p.fecha_creacion_producto) = ?";
            $params[] = $fecha;
        }
    }
    
    // Construir query base
    $where_clause = '';
    if (!empty($where_conditions)) {
        $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
    }
    
    // Contar total de productos con filtros
    $count_query = "
        SELECT COUNT(*) as total 
        FROM producto p
        LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
        LEFT JOIN marca m ON p.id_marca = m.id_marca
        $where_clause
    ";
    
    $stmt = $conn->prepare($count_query);
    $stmt->execute($params);
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Obtener productos con filtros
    $query = "
        SELECT 
            p.id_producto,
            p.nombre_producto,
            p.codigo,
            p.descripcion_producto,
            p.precio_producto,
            p.descuento_porcentaje_producto,
            p.genero_producto,
            p.stock_actual_producto,
            p.stock_minimo_producto,
            p.stock_maximo_producto,
            p.status_producto,
            p.estado,
            p.fecha_creacion_producto,
            p.imagen_producto,
            p.url_imagen_producto,
            c.nombre_categoria,
            c.id_categoria,
            m.nombre_marca,
            m.id_marca
        FROM producto p
        LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
        LEFT JOIN marca m ON p.id_marca = m.id_marca
        $where_clause
        ORDER BY p.id_producto ASC 
        LIMIT $limit OFFSET $offset
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear datos
    foreach ($productos as &$producto) {
        $producto['precio_formato'] = '$' . number_format($producto['precio_producto'], 2);
        $producto['estado_texto'] = $producto['estado'] === 'activo' ? 'Activo' : 'Inactivo';
        $producto['fecha_creacion_formato'] = date('d/m/Y', strtotime($producto['fecha_creacion_producto']));
        
        // ✅ Usar función centralizada para calcular estado del stock
        $producto['estado_stock'] = calcularEstadoStock($producto);
        
        // Formatear descuento si existe
        if ($producto['descuento_porcentaje_producto'] > 0) {
            $precio_descuento = $producto['precio_producto'] * (1 - $producto['descuento_porcentaje_producto'] / 100);
            $producto['precio_descuento_formato'] = '$' . number_format($precio_descuento, 2);
            $producto['descuento_formato'] = '-' . $producto['descuento_porcentaje_producto'] . '%';
        }
        
        // CORREGIR URLs DE IMÁGENES
        // Si la url_imagen_producto contiene placeholder o no es válida, usar imagen por defecto
        if (empty($producto['url_imagen_producto']) || 
            strpos($producto['url_imagen_producto'], 'placeholder') !== false ||
            strpos($producto['url_imagen_producto'], 'via.placeholder') !== false) {
            
            // Verificar si existe imagen_producto física
            if (!empty($producto['imagen_producto']) && 
                strpos($producto['imagen_producto'], 'product_') === 0) {
                // Verificar si el archivo existe físicamente
                $image_path = $_SERVER['DOCUMENT_ROOT'] . '/fashion-master/public/assets/img/products/' . $producto['imagen_producto'];
                if (file_exists($image_path)) {
                    // El archivo existe, generar URL correcta
                    $producto['url_imagen_producto'] = '/fashion-master/public/assets/img/products/' . $producto['imagen_producto'];
                } else {
                    // El archivo no existe, usar imagen por defecto
                    $producto['url_imagen_producto'] = '/fashion-master/public/assets/img/default-product.jpg';
                    // También limpiar el nombre del archivo inexistente
                    $producto['imagen_producto'] = 'default-product.jpg';
                }
            } else {
                // Usar imagen por defecto
                $producto['url_imagen_producto'] = '/fashion-master/public/assets/img/default-product.jpg';
            }
        } else {
            // La URL no es placeholder, verificar si la imagen referenciada existe
            if (!empty($producto['imagen_producto']) && 
                strpos($producto['imagen_producto'], 'product_') === 0) {
                $image_path = $_SERVER['DOCUMENT_ROOT'] . '/fashion-master/public/assets/img/products/' . $producto['imagen_producto'];
                if (!file_exists($image_path)) {
                    // El archivo no existe, cambiar a imagen por defecto
                    $producto['url_imagen_producto'] = '/fashion-master/public/assets/img/default-product.jpg';
                    $producto['imagen_producto'] = 'default-product.jpg';
                }
            }
        }
        
        // Si imagen_producto está vacía, usar nombre por defecto
        if (empty($producto['imagen_producto'])) {
            $producto['imagen_producto'] = 'default-product.jpg';
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => $productos,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => ceil($total / $limit),
            'total_items' => (int)$total,
            'items_per_page' => $limit
        ],
        'filters_applied' => [
            'search' => $search,
            'category' => $category,
            'status' => $status,
            'stock_filter' => $stock_filter
        ]
    ]);
}

function getCategorias($conn) {
    try {
        $query = "SELECT id_categoria, nombre_categoria FROM categoria WHERE status_categoria = 1 ORDER BY nombre_categoria";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $categorias
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Error al obtener categorías: ' . $e->getMessage()
        ]);
    }
}

function getProducto($conn) {
    $id = (int)($_GET['id'] ?? 0);
    
    if (!$id) {
        echo json_encode([
            'success' => false,
            'error' => 'ID de producto requerido'
        ]);
        return;
    }
    
    try {
        $query = "
            SELECT 
                p.*,
                c.nombre_categoria,
                m.nombre_marca
            FROM producto p
            LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
            LEFT JOIN marca m ON p.id_marca = m.id_marca
            WHERE p.id_producto = ?
        ";
        
        $stmt = $conn->prepare($query);
        $stmt->execute([$id]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$producto) {
            echo json_encode([
                'success' => false,
                'error' => 'Producto no encontrado'
            ]);
            return;
        }
        
        // CORREGIR URLs DE IMÁGENES para un solo producto
        if (empty($producto['url_imagen_producto']) || 
            strpos($producto['url_imagen_producto'], 'placeholder') !== false ||
            strpos($producto['url_imagen_producto'], 'via.placeholder') !== false) {
            
            // Verificar si existe imagen_producto física
            if (!empty($producto['imagen_producto']) && 
                strpos($producto['imagen_producto'], 'product_') === 0) {
                // Verificar si el archivo existe físicamente
                $image_path = $_SERVER['DOCUMENT_ROOT'] . '/fashion-master/public/assets/img/products/' . $producto['imagen_producto'];
                if (file_exists($image_path)) {
                    // El archivo existe, generar URL correcta
                    $producto['url_imagen_producto'] = '/fashion-master/public/assets/img/products/' . $producto['imagen_producto'];
                } else {
                    // El archivo no existe, usar imagen por defecto
                    $producto['url_imagen_producto'] = '/fashion-master/public/assets/img/default-product.jpg';
                    $producto['imagen_producto'] = 'default-product.jpg';
                }
            } else {
                // Usar imagen por defecto
                $producto['url_imagen_producto'] = '/fashion-master/public/assets/img/default-product.jpg';
            }
        } else {
            // La URL no es placeholder, verificar si la imagen referenciada existe
            if (!empty($producto['imagen_producto']) && 
                strpos($producto['imagen_producto'], 'product_') === 0) {
                $image_path = $_SERVER['DOCUMENT_ROOT'] . '/fashion-master/public/assets/img/products/' . $producto['imagen_producto'];
                if (!file_exists($image_path)) {
                    // El archivo no existe, cambiar a imagen por defecto
                    $producto['url_imagen_producto'] = '/fashion-master/public/assets/img/default-product.jpg';
                    $producto['imagen_producto'] = 'default-product.jpg';
                }
            }
        }
        
        // Si imagen_producto está vacía, usar nombre por defecto
        if (empty($producto['imagen_producto'])) {
            $producto['imagen_producto'] = 'default-product.jpg';
        }
        
        echo json_encode([
            'success' => true,
            'product' => $producto // Cambiar 'data' por 'product' para consistencia con el modal
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

function createProducto($conn) {
    try {
        // Usar nombres de campos del formulario pero mapear a BD
        $nombre = $_POST['nombre_producto'] ?? '';
        $precio = $_POST['precio_producto'] ?? '';
        $categoria_id = $_POST['id_categoria'] ?? '';
        $marca_id = $_POST['id_marca'] ?? null; // ID de marca (puede ser NULL)
        $descripcion = $_POST['descripcion_producto'] ?? '';
        $stock = $_POST['stock_actual_producto'] ?? 0;
        $genero = $_POST['genero_producto'] ?? 'Unisex'; // Género del producto
        // 🔒 STOCK MÍNIMO FIJO EN 20 - NO SE PUEDE MODIFICAR DESDE LA APLICACIÓN
        $stock_minimo = 20; // Valor fijo, solo modificable desde phpMyAdmin
        // 🔒 STOCK MÁXIMO FIJO EN 300 - NO SE PUEDE MODIFICAR DESDE LA APLICACIÓN
        $stock_maximo = 300; // Valor fijo, solo modificable desde phpMyAdmin
        // Mapear precio_descuento_producto a descuento_porcentaje_producto
        $descuento_porcentaje = $_POST['precio_descuento_producto'] ?? 0;
        // 🔥 LÓGICA AUTOMÁTICA: Si hay descuento, en_oferta_producto = 1, sino = 0
        $en_oferta = ($descuento_porcentaje > 0) ? 1 : 0;
        // CORRECCIÓN: Obtener status_producto del POST (siempre será 1 para nuevos productos)
        $status_producto = isset($_POST['status_producto']) ? (int)$_POST['status_producto'] : 1;
        $estado = $_POST['estado'] ?? 'activo';
        
        if (empty($nombre) || empty($precio) || empty($categoria_id)) {
            echo json_encode([
                'success' => false,
                'error' => 'Los campos nombre, precio y categoría son requeridos'
            ]);
            return;
        }
        
        // 🏷️ GENERAR CÓDIGO AUTOMÁTICAMENTE (tipo slug)
        // Obtener nombres de marca y categoría para generar el código
        $nombreMarca = 'MARCA';
        $nombreCategoria = 'CAT';
        
        if ($marca_id) {
            $stmt = $conn->prepare("SELECT nombre_marca FROM marca WHERE id_marca = ?");
            $stmt->execute([$marca_id]);
            $marca = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($marca) {
                $nombreMarca = $marca['nombre_marca'];
            }
        }
        
        if ($categoria_id) {
            $stmt = $conn->prepare("SELECT nombre_categoria FROM categoria WHERE id_categoria = ?");
            $stmt->execute([$categoria_id]);
            $categoria = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($categoria) {
                $nombreCategoria = $categoria['nombre_categoria'];
            }
        }
        
        // Generar código automático: CAM-NIKE-PAN
        $codigo = generarCodigoProducto($nombre, $nombreMarca, $nombreCategoria, $conn);
        
        // Manejar imagen - SOLO SE PROCESA EN SUBMIT
        // FORMATO ESTANDARIZADO:
        // imagen_producto: solo nombre (ej: product_123.jpg o default-product.jpg)
        // url_imagen_producto: ruta completa (ej: /fashion-master/public/assets/img/products/product_123.jpg)
        $imagen_nombre = 'default-product.jpg';
        $url_imagen = '/fashion-master/public/assets/img/default-product.jpg';
        
        // Verificar si hay un archivo de imagen válido en el momento del submit
        if (isset($_FILES['imagen_producto']) && $_FILES['imagen_producto']['error'] === UPLOAD_ERR_OK) {
            // Generar nombre único y subir archivo
            $imagen_nombre = handleImageUpload($_FILES['imagen_producto']);
            // ESTANDARIZAR: Siempre usar ruta absoluta desde raíz web
            $url_imagen = '/fashion-master/public/assets/img/products/' . $imagen_nombre;
        }
        
        $query = "
            INSERT INTO producto (
                nombre_producto, codigo, descripcion_producto, precio_producto,
                descuento_porcentaje_producto, stock_actual_producto, stock_minimo_producto, stock_maximo_producto,
                id_categoria, id_marca, genero_producto, imagen_producto, url_imagen_producto, status_producto, estado, en_oferta_producto, fecha_creacion_producto
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ";
        
        $stmt = $conn->prepare($query);
        
        if ($stmt->execute([
            $nombre, 
            $codigo, 
            $descripcion, 
            (float)$precio, 
            (float)$descuento_porcentaje,
            (int)$stock,
            (int)$stock_minimo,
            (int)$stock_maximo,
            (int)$categoria_id,
            $marca_id ? (int)$marca_id : null,
            $genero,
            $imagen_nombre,
            $url_imagen,
            $status_producto,
            $estado,
            $en_oferta
        ])) {
            $productId = $conn->lastInsertId();
            
            // Obtener el producto completo con todos los joins
            $getQuery = "
                SELECT 
                    p.*,
                    c.nombre_categoria,
                    m.nombre_marca
                FROM producto p
                LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
                LEFT JOIN marca m ON p.id_marca = m.id_marca
                WHERE p.id_producto = ?
            ";
            
            $getStmt = $conn->prepare($getQuery);
            $getStmt->execute([$productId]);
            $fullProduct = $getStmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'message' => 'Producto creado exitosamente',
                'id' => $productId,
                'product_id' => $productId,
                'product' => $fullProduct // Devolver producto completo para actualización suave
            ]);
        } else {
            throw new Exception('Error al insertar producto');
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Error al crear producto: ' . $e->getMessage()
        ]);
    }
}

function updateProducto($conn) {
    $id = (int)($_POST['id_producto'] ?? $_POST['id'] ?? 0);
    
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'ID requerido']);
        return;
    }
    
    try {
        // Obtener producto actual para mantener imagen si no se cambia
        $query = "SELECT imagen_producto, url_imagen_producto, status_producto FROM producto WHERE id_producto = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$id]);
        $current_product = $stmt->fetch();
        
        if (!$current_product) {
            echo json_encode(['success' => false, 'error' => 'Producto no encontrado']);
            return;
        }
        
        // Obtener datos del formulario
        $nombre = $_POST['nombre_producto'] ?? '';
        $precio = $_POST['precio_producto'] ?? '';
        $categoria_id = $_POST['id_categoria'] ?? '';
        $marca_id = $_POST['id_marca'] ?? null; // ID de marca (puede ser NULL)
        $descripcion = $_POST['descripcion_producto'] ?? '';
        $stock = $_POST['stock_actual_producto'] ?? 0;
        $genero = $_POST['genero_producto'] ?? 'Unisex'; // Género del producto
        // 🔒 STOCK MÍNIMO FIJO EN 20 - NO SE PUEDE MODIFICAR DESDE LA APLICACIÓN
        $stock_minimo = 20; // Valor fijo, solo modificable desde phpMyAdmin
        // 🔒 STOCK MÁXIMO FIJO EN 300 - NO SE PUEDE MODIFICAR DESDE LA APLICACIÓN
        $stock_maximo = 300; // Valor fijo, solo modificable desde phpMyAdmin
        $descuento_porcentaje = $_POST['precio_descuento_producto'] ?? 0;
        // 🔥 LÓGICA AUTOMÁTICA: Si hay descuento, en_oferta_producto = 1, sino = 0
        $en_oferta = ($descuento_porcentaje > 0) ? 1 : 0;
        
        // 🏷️ REGENERAR CÓDIGO AUTOMÁTICAMENTE si cambia nombre, marca o categoría
        // Obtener nombres de marca y categoría actuales
        $nombreMarca = 'MARCA';
        $nombreCategoria = 'CAT';
        
        if ($marca_id) {
            $stmt = $conn->prepare("SELECT nombre_marca FROM marca WHERE id_marca = ?");
            $stmt->execute([$marca_id]);
            $marca = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($marca) {
                $nombreMarca = $marca['nombre_marca'];
            }
        }
        
        if ($categoria_id) {
            $stmt = $conn->prepare("SELECT nombre_categoria FROM categoria WHERE id_categoria = ?");
            $stmt->execute([$categoria_id]);
            $categoria = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($categoria) {
                $nombreCategoria = $categoria['nombre_categoria'];
            }
        }
        
        // Regenerar código automático
        $codigo = generarCodigoProducto($nombre, $nombreMarca, $nombreCategoria, $conn);
        
        // ⭐ VALIDAR CÓDIGO DUPLICADO
        if (!empty($codigo)) {
            $check_code_query = "SELECT COUNT(*) as count FROM producto 
                                WHERE codigo = ? AND id_producto != ?";
            $stmt_check = $conn->prepare($check_code_query);
            $stmt_check->execute([$codigo, $id]);
            $code_exists = $stmt_check->fetchColumn();
            
            if ($code_exists > 0) {
                echo json_encode([
                    'success' => false, 
                    'error' => 'Ya existe otro producto con ese código'
                ]);
                return;
            }
        }
        
        // CORRECCIÓN: Mantener status_producto del POST (viene del hidden) o del producto actual
        $status_producto = isset($_POST['status_producto']) ? (int)$_POST['status_producto'] : $current_product['status_producto'];
        $estado = $_POST['estado'] ?? 'activo';
        
        // Manejar imagen - SOLO SE PROCESA EN SUBMIT
        // FORMATO ESTANDARIZADO:
        // imagen_producto: solo nombre (ej: product_123.jpg)
        // url_imagen_producto: ruta completa (ej: /fashion-master/public/assets/img/products/product_123.jpg)
        $imagen_nombre = $current_product['imagen_producto'];
        $url_imagen = $current_product['url_imagen_producto'];
        
        // Verificar si hay un archivo de imagen válido en el momento del submit
        if (isset($_FILES['imagen_producto']) && $_FILES['imagen_producto']['error'] === UPLOAD_ERR_OK) {
            // Eliminar imagen anterior si no es la default y existe físicamente
            if ($imagen_nombre && $imagen_nombre !== 'default-product.png' && $imagen_nombre !== 'default-product.jpg') {
                $old_path = $_SERVER['DOCUMENT_ROOT'] . '/fashion-master/public/assets/img/products/' . $imagen_nombre;
                if (file_exists($old_path)) {
                    unlink($old_path);
                }
                
                // Eliminar también la versión _shop si existe
                $shop_version = str_replace('.', '_shop.', $imagen_nombre);
                $old_shop_path = $_SERVER['DOCUMENT_ROOT'] . '/fashion-master/public/assets/img/products/' . $shop_version;
                if (file_exists($old_shop_path)) {
                    unlink($old_shop_path);
                }
            }
            
            // Generar nombre único y subir archivo
            $imagen_nombre = handleImageUpload($_FILES['imagen_producto']);
            // ESTANDARIZAR: Siempre usar ruta absoluta desde raíz web
            $url_imagen = '/fashion-master/public/assets/img/products/' . $imagen_nombre;
        }
        
        // Actualizar producto
        $query = "UPDATE producto SET 
            nombre_producto = ?, 
            codigo = ?,
            descripcion_producto = ?, 
            precio_producto = ?, 
            descuento_porcentaje_producto = ?,
            stock_actual_producto = ?, 
            stock_minimo_producto = ?,
            stock_maximo_producto = ?,
            id_categoria = ?, 
            id_marca = ?,
            genero_producto = ?,
            imagen_producto = ?,
            url_imagen_producto = ?,
            status_producto = ?,
            estado = ?,
            en_oferta_producto = ?,
            fecha_actualizacion_producto = NOW()
            WHERE id_producto = ?";
        
        $stmt = $conn->prepare($query);
        
        if ($stmt->execute([
            $nombre,
            $codigo,
            $descripcion,
            (float)$precio,
            (float)$descuento_porcentaje,
            (int)$stock,
            (int)$stock_minimo,
            (int)$stock_maximo,
            (int)$categoria_id,
            $marca_id ? (int)$marca_id : null,
            $genero,
            $imagen_nombre,
            $url_imagen,
            $status_producto,
            $estado,
            $en_oferta,
            $id
        ])) {
            // Obtener el producto completo con todos los joins
            $getQuery = "
                SELECT 
                    p.*,
                    c.nombre_categoria,
                    m.nombre_marca
                FROM producto p
                LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
                LEFT JOIN marca m ON p.id_marca = m.id_marca
                WHERE p.id_producto = ?
            ";
            
            $getStmt = $conn->prepare($getQuery);
            $getStmt->execute([$id]);
            $fullProduct = $getStmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'message' => 'Producto actualizado exitosamente',
                'id' => $id,
                'product' => $fullProduct // Devolver producto completo para actualización suave
            ]);
        } else {
            throw new Exception('Error al actualizar producto');
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Error al actualizar producto: ' . $e->getMessage()
        ]);
    }
}

function deleteProducto($conn) {
    $id = (int)($_POST['id'] ?? 0);
    
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'ID requerido']);
        return;
    }
    
    try {
        $query = "UPDATE producto SET status_producto = 0 WHERE id_producto = ?";
        $stmt = $conn->prepare($query);
        
        if ($stmt->execute([$id])) {
            echo json_encode(['success' => true, 'message' => 'Producto eliminado']);
        } else {
            throw new Exception('Error al eliminar');
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function toggleProductStatus($conn) {
    $id = (int)($_POST['id'] ?? 0);
    $status = (int)($_POST['status'] ?? 1);
    
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'ID requerido']);
        return;
    }
    
    try {
        $query = "UPDATE producto SET status_producto = ? WHERE id_producto = ?";
        $stmt = $conn->prepare($query);
        
        if ($stmt->execute([$status, $id])) {
            // Obtener el producto completo con todos los joins
            $getQuery = "
                SELECT 
                    p.*,
                    c.nombre_categoria,
                    m.nombre_marca
                FROM producto p
                LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
                LEFT JOIN marca m ON p.id_marca = m.id_marca
                WHERE p.id_producto = ?
            ";
            
            $getStmt = $conn->prepare($getQuery);
            $getStmt->execute([$id]);
            $fullProduct = $getStmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'message' => 'Estado actualizado',
                'product' => $fullProduct // Devolver producto completo para actualización suave
            ]);
        } else {
            throw new Exception('Error al actualizar estado');
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function updateProductStock($conn) {
    $id = (int)($_POST['id'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'ID requerido']);
        return;
    }
    
    try {
        $query = "UPDATE producto SET stock_actual_producto = ? WHERE id_producto = ?";
        $stmt = $conn->prepare($query);
        
        if ($stmt->execute([$stock, $id])) {
            // Obtener el producto completo con todos los joins
            $getQuery = "
                SELECT 
                    p.*,
                    c.nombre_categoria,
                    m.nombre_marca
                FROM producto p
                LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
                LEFT JOIN marca m ON p.id_marca = m.id_marca
                WHERE p.id_producto = ?
            ";
            
            $getStmt = $conn->prepare($getQuery);
            $getStmt->execute([$id]);
            $fullProduct = $getStmt->fetch(PDO::FETCH_ASSOC);
            
            // ✅ Usar función centralizada para calcular estado del stock
            $fullProduct['estado_stock'] = calcularEstadoStock($fullProduct);
            
            // Formatear precio
            $fullProduct['precio_formato'] = '$' . number_format($fullProduct['precio_producto'], 2);
            
            echo json_encode([
                'success' => true,
                'message' => 'Stock actualizado',
                'product' => $fullProduct // Devolver producto completo para actualización suave
            ]);
        } else {
            throw new Exception('Error al actualizar stock');
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

// Función para cambiar estado del producto (activo/inactivo)
function changeProductEstado($conn) {
    $id = (int)($_POST['id'] ?? 0);
    $estado = $_POST['estado'] ?? '';
    
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'ID requerido']);
        return;
    }
    
    // Validar que sea 'activo' o 'inactivo'
    if (!in_array($estado, ['activo', 'inactivo'])) {
        echo json_encode(['success' => false, 'error' => 'Estado inválido. Debe ser activo o inactivo. Recibido: ' . $estado]);
        return;
    }
    
    try {
        $query = "UPDATE producto SET estado = ? WHERE id_producto = ?";
        $stmt = $conn->prepare($query);
        
        if ($stmt->execute([$estado, $id])) {
            // Obtener el producto completo con todos los joins
            $getQuery = "
                SELECT 
                    p.*,
                    c.nombre_categoria,
                    m.nombre_marca
                FROM producto p
                LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
                LEFT JOIN marca m ON p.id_marca = m.id_marca
                WHERE p.id_producto = ?
            ";
            
            $getStmt = $conn->prepare($getQuery);
            $getStmt->execute([$id]);
            $fullProduct = $getStmt->fetch(PDO::FETCH_ASSOC);
            
            // ✅ Usar función centralizada para calcular estado del stock
            $fullProduct['estado_stock'] = calcularEstadoStock($fullProduct);
            
            // Formatear precio
            $fullProduct['precio_formato'] = '$' . number_format($fullProduct['precio_producto'], 2);
            
            echo json_encode([
                'success' => true,
                'message' => 'Estado del producto actualizado',
                'product' => $fullProduct // Devolver producto completo para actualización suave
            ]);
        } else {
            throw new Exception('Error al actualizar estado del producto');
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

// Función para manejar subida de imágenes
function handleImageUpload($file) {
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_extensions)) {
        throw new Exception('Tipo de archivo no permitido. Solo se permiten: ' . implode(', ', $allowed_extensions));
    }
    
    $max_size = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $max_size) {
        throw new Exception('El archivo es demasiado grande. Tamaño máximo: 5MB');
    }
    
    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/fashion-master/public/assets/img/products/';
    
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $unique_name = 'product_' . uniqid() . '.' . $file_extension;
    $upload_path = $upload_dir . $unique_name;
    
    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        throw new Exception('Error al subir la imagen');
    }
    
    return $unique_name;
}

// Función para crear versión de imagen para tienda con fondo difuminado
function createShopVersion($original_path, $upload_dir, $filename) {
    // Verificar que GD esté disponible
    if (!extension_loaded('gd')) {
        throw new Exception('Extensión GD no disponible');
    }
    
    // Obtener información de la imagen original
    $image_info = @getimagesize($original_path);
    if (!$image_info) {
        throw new Exception('No se puede leer la imagen');
    }
    
    list($orig_width, $orig_height, $image_type) = $image_info;
    
    // Crear imagen desde archivo según su tipo
    $original = null;
    switch ($image_type) {
        case IMAGETYPE_JPEG:
            $original = @imagecreatefromjpeg($original_path);
            break;
        case IMAGETYPE_PNG:
            $original = @imagecreatefrompng($original_path);
            break;
        case IMAGETYPE_GIF:
            $original = @imagecreatefromgif($original_path);
            break;
        case IMAGETYPE_WEBP:
            if (function_exists('imagecreatefromwebp')) {
                $original = @imagecreatefromwebp($original_path);
            }
            break;
    }
    
    if (!$original) {
        throw new Exception('No se puede crear imagen desde archivo');
    }
    
    // Dimensiones del canvas final (cuadrado 800x800)
    $canvas_size = 800;
    
    // Crear canvas
    $canvas = imagecreatetruecolor($canvas_size, $canvas_size);
    if (!$canvas) {
        imagedestroy($original);
        throw new Exception('No se puede crear canvas');
    }
    
    // IMPORTANTE: Para JPEG, usar color de fondo en lugar de transparente
    // Color de fondo oscuro neutro (#1a1a1a)
    $bg_color = imagecolorallocate($canvas, 26, 26, 26);
    imagefill($canvas, 0, 0, $bg_color);
    
    // Habilitar alpha blending
    imagealphablending($canvas, true);
    imagesavealpha($canvas, true);
    
    // PASO 1: Crear fondo difuminado (background blur)
    // Calcular dimensiones para que la imagen cubra todo el canvas
    $bg_scale = max($canvas_size / $orig_width, $canvas_size / $orig_height) * 1.3; // 30% más grande
    $bg_width = (int)($orig_width * $bg_scale);
    $bg_height = (int)($orig_height * $bg_scale);
    $bg_x = (int)(($canvas_size - $bg_width) / 2);
    $bg_y = (int)(($canvas_size - $bg_height) / 2);
    
    // Copiar imagen redimensionada como fondo
    imagecopyresampled($canvas, $original, $bg_x, $bg_y, 0, 0, $bg_width, $bg_height, $orig_width, $orig_height);
    
    // Aplicar blur gaussiano múltiple para efecto difuminado intenso
    for ($i = 0; $i < 30; $i++) {
        imagefilter($canvas, IMG_FILTER_GAUSSIAN_BLUR);
    }
    
    // Oscurecer el fondo difuminado para que no compita con la imagen principal
    imagefilter($canvas, IMG_FILTER_BRIGHTNESS, -50);
    imagefilter($canvas, IMG_FILTER_CONTRAST, -20);
    
    // PASO 2: Superponer imagen original centrada y contenida
    // Calcular dimensiones para mantener aspect ratio (contain)
    $scale = min($canvas_size * 0.90 / $orig_width, $canvas_size * 0.90 / $orig_height);
    $new_width = (int)($orig_width * $scale);
    $new_height = (int)($orig_height * $scale);
    $x = (int)(($canvas_size - $new_width) / 2);
    $y = (int)(($canvas_size - $new_height) / 2);
    
    // Copiar imagen original centrada encima del fondo difuminado
    imagecopyresampled($canvas, $original, $x, $y, 0, 0, $new_width, $new_height, $orig_width, $orig_height);
    
    // Guardar imagen para tienda con sufijo _shop
    $shop_filename = str_replace('.', '_shop.', $filename);
    $shop_path = $upload_dir . $shop_filename;
    
    // Guardar según formato - Preferir PNG para mejor calidad con fondos
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $saved = false;
    
    // Si es JPG/JPEG, convertir a PNG para mantener mejor calidad del fondo difuminado
    if (in_array($extension, ['jpg', 'jpeg'])) {
        $shop_filename = str_replace(['.jpg', '.jpeg'], '.png', $shop_filename);
        $shop_path = $upload_dir . $shop_filename;
        $saved = imagepng($canvas, $shop_path, 9); // Máxima compresión PNG
    } else {
        switch ($extension) {
            case 'png':
                $saved = imagepng($canvas, $shop_path, 9);
                break;
            case 'gif':
                $saved = imagegif($canvas, $shop_path);
                break;
            case 'webp':
                if (function_exists('imagewebp')) {
                    $saved = imagewebp($canvas, $shop_path, 90);
                }
                break;
        }
    }
    
    // Liberar memoria
    imagedestroy($canvas);
    imagedestroy($original);
    
    if (!$saved) {
        throw new Exception('No se puede guardar imagen shop');
    }
}

// Función para obtener marcas
function getMarcas($conn) {
    try {
        $query = "SELECT id_marca, nombre_marca, codigo_marca 
                  FROM marca 
                  WHERE status_marca = 1 
                  ORDER BY nombre_marca ASC";
        
        $stmt = $conn->query($query);
        $marcas = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true, 
            'data' => $marcas
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false, 
            'error' => 'Error al obtener marcas: ' . $e->getMessage()
        ]);
    }
}
?>
