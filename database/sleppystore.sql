-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 11-10-2025 a las 04:32:48
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sleppystore`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito`
--

CREATE TABLE `carrito` (
  `id_carrito` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad_carrito` int(11) NOT NULL DEFAULT 1,
  `fecha_agregado_carrito` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `carrito`
--

INSERT INTO `carrito` (`id_carrito`, `id_usuario`, `id_producto`, `cantidad_carrito`, `fecha_agregado_carrito`) VALUES
(2, 7, 9, 2, '2025-10-10 01:00:31'),
(4, 1, 6, 7, '2025-10-10 01:09:59'),
(5, 1, 3, 1, '2025-10-10 15:53:57'),
(6, 1, 5, 1, '2025-10-10 15:54:21'),
(7, 1, 8, 6, '2025-10-10 15:54:24'),
(8, 1, 2, 1, '2025-10-10 15:54:25'),
(10, 7, 2, 2, '2025-10-10 21:44:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria`
--

CREATE TABLE `categoria` (
  `id_categoria` int(11) NOT NULL,
  `codigo_categoria` varchar(50) DEFAULT NULL,
  `nombre_categoria` varchar(100) NOT NULL,
  `descripcion_categoria` text DEFAULT NULL,
  `imagen_categoria` varchar(255) DEFAULT 'default-category.png',
  `url_imagen_categoria` varchar(500) NOT NULL,
  `status_categoria` tinyint(1) DEFAULT 1,
  `estado_categoria` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `fecha_creacion_categoria` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion_categoria` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categoria`
--

INSERT INTO `categoria` (`id_categoria`, `codigo_categoria`, `nombre_categoria`, `descripcion_categoria`, `imagen_categoria`, `url_imagen_categoria`, `status_categoria`, `estado_categoria`, `fecha_creacion_categoria`, `fecha_actualizacion_categoria`) VALUES
(1, 'CAT-001', 'Camisas', 'Camisas para hombre y mujer', 'categoria-1-1760141572.jpg', '/fashion-master/public/assets/img/categories/categoria-1-1760141572.jpg', 1, 'activo', '2025-09-30 19:44:29', '2025-10-11 00:12:52'),
(2, 'CAT-002', 'Pantalones', 'Pantalones de todo tipo', 'default-product.jpg', '/fashion-master/public/assets/img/default-product.jpg', 1, 'activo', '2025-09-30 19:44:29', '2025-10-08 04:20:20'),
(3, 'CAT-003', 'Zapatos', 'Calzado para toda ocasión', 'default-product.jpg', '/fashion-master/public/assets/img/default-product.jpg', 1, 'activo', '2025-09-30 19:44:29', '2025-10-08 04:01:00'),
(4, 'CAT-333', 'Accesorios', 'Complementos y accesorios', 'categoria-4-1760141564.png', '/fashion-master/public/assets/img/categories/categoria-4-1760141564.png', 1, 'inactivo', '2025-09-30 19:44:29', '2025-10-11 00:18:22'),
(5, 'CAT-005', 'Vestidos', 'Vestidos elegantes y casuales', 'default-product.jpg', '/fashion-master/public/assets/img/default-product.jpg', 1, 'activo', '2025-09-30 19:44:29', '2025-10-08 04:01:00'),
(16, 'dfewrw', 'hola', 'una categoria', 'categoria-1759897392-68e5e730bf141.jpg', '/fashion-master/public/assets/img/categories/categoria-1759897392-68e5e730bf141.jpg', 1, 'activo', '2025-10-08 04:23:12', NULL),
(17, 'fffff', 'fff', 'ffff', 'default-product.jpg', '/fashion-master/public/assets/img/default-product.jpg', 1, 'inactivo', '2025-10-08 04:25:31', '2025-10-10 22:11:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chatbot`
--

CREATE TABLE `chatbot` (
  `id_chatbot` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `id_sesion` varchar(191) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `nombre_bot` varchar(100) DEFAULT 'AsistenteIA',
  `avatar_bot` varchar(255) DEFAULT 'default-bot-avatar.png',
  `color_primario` varchar(7) DEFAULT '#1a73e8',
  `color_secundario` varchar(7) DEFAULT '#34a853',
  `mensaje_bienvenida` text DEFAULT 'Hola! Soy tu asistente virtual. ¿En qué puedo ayudarte hoy?',
  `tono_conversacion` enum('formal','casual','amigable','profesional') DEFAULT 'amigable',
  `idioma` varchar(10) DEFAULT 'es',
  `iniciada_chatbot` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultima_actividad_chatbot` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `satisfaccion_chatbot` tinyint(4) DEFAULT NULL CHECK (`satisfaccion_chatbot` >= 1 and `satisfaccion_chatbot` <= 5),
  `estado_chatbot` enum('activo','inactivo','pausado') NOT NULL DEFAULT 'activo',
  `fecha_creacion_chatbot` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion_chatbot` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chatbot_conocimiento`
--

CREATE TABLE `chatbot_conocimiento` (
  `id_conocimiento` int(11) NOT NULL,
  `categoria_conocimiento` varchar(100) NOT NULL,
  `pregunta` text NOT NULL,
  `respuesta` text NOT NULL,
  `palabras_clave` text DEFAULT NULL,
  `variaciones_pregunta` text DEFAULT NULL,
  `embedding_pregunta` text DEFAULT NULL,
  `embedding_respuesta` text DEFAULT NULL,
  `similitud_minima` decimal(4,3) DEFAULT 0.700,
  `requiere_actualizacion_embedding` tinyint(1) DEFAULT 1,
  `prioridad` int(11) DEFAULT 0,
  `tipo_contenido` enum('faq','producto','politica','general','tecnico') DEFAULT 'general',
  `estado_conocimiento` enum('activo','inactivo','revision') NOT NULL DEFAULT 'activo',
  `veces_utilizado` int(11) DEFAULT 0,
  `valoracion_promedio` decimal(3,2) DEFAULT NULL,
  `fecha_creacion_conocimiento` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion_conocimiento` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `chatbot_conocimiento`
--

INSERT INTO `chatbot_conocimiento` (`id_conocimiento`, `categoria_conocimiento`, `pregunta`, `respuesta`, `palabras_clave`, `variaciones_pregunta`, `embedding_pregunta`, `embedding_respuesta`, `similitud_minima`, `requiere_actualizacion_embedding`, `prioridad`, `tipo_contenido`, `estado_conocimiento`, `veces_utilizado`, `valoracion_promedio`, `fecha_creacion_conocimiento`, `fecha_actualizacion_conocimiento`) VALUES
(1, 'Envíos', '¿Cuánto tarda el envío?', 'Los envíos tardan entre 2 a 5 días hábiles dependiendo de tu ubicación. Ofrecemos envío express que llega en 24-48 horas.', 'envio,entrega,tiempo,demora,cuanto tarda', '[\"cuanto demora el envio\",\"en cuanto tiempo llega\",\"cuando llega mi pedido\",\"tiempo de entrega\",\"demora del envio\"]', NULL, NULL, 0.700, 1, 10, 'faq', 'activo', 0, NULL, '2025-10-05 01:38:56', NULL),
(2, 'Devoluciones', '¿Puedo devolver un producto?', 'Sí, aceptamos devoluciones dentro de los 30 días posteriores a la compra. El producto debe estar en su empaque original y sin usar.', 'devolucion,cambio,garantia,retorno', '[\"como devuelvo un producto\",\"quiero devolver algo\",\"politica de devoluciones\",\"puedo hacer un cambio\",\"garantia de devolucion\"]', NULL, NULL, 0.700, 1, 9, 'politica', 'activo', 0, NULL, '2025-10-05 01:38:56', NULL),
(3, 'Pagos', '¿Qué métodos de pago aceptan?', 'Aceptamos tarjetas de crédito/débito (Visa, Mastercard, American Express), PayPal, transferencias bancarias y pago contra entrega.', 'pago,tarjeta,metodo,forma de pago,paypal', '[\"formas de pago\",\"como puedo pagar\",\"metodos de pago aceptados\",\"con que puedo pagar\",\"acepta tarjeta\"]', NULL, NULL, 0.700, 1, 8, 'faq', 'activo', 0, NULL, '2025-10-05 01:38:56', NULL),
(4, 'Productos', '¿Cómo sé mi talla?', 'En cada producto encontrarás una guía de tallas detallada. También puedes contactarnos para asesoría personalizada sobre medidas.', 'talla,medida,tamaño,size,como se mi talla', '[\"que talla soy\",\"como se mi talla\",\"guia de tallas\",\"tabla de medidas\",\"como mido mi talla\"]', NULL, NULL, 0.700, 1, 7, 'producto', 'activo', 0, NULL, '2025-10-05 01:38:56', NULL),
(5, 'Cuenta', '¿Cómo creo una cuenta?', 'Haz click en \"Registrarse\" en la parte superior derecha. Completa tus datos y recibirás un email de confirmación.', 'cuenta,registro,crear cuenta,registrarse,sign up', '[\"como me registro\",\"crear cuenta nueva\",\"quiero registrarme\",\"abrir una cuenta\",\"hacer cuenta\"]', NULL, NULL, 0.700, 1, 6, 'tecnico', 'activo', 0, NULL, '2025-10-05 01:38:56', NULL),
(6, 'Saludos', 'Hola', '¡Hola! 👋 Bienvenido a nuestra tienda. ¿En qué puedo ayudarte hoy?', 'hola,saludo,buenas,hey,hi', '[\"hoa\",\"ola\",\"hoal\",\"hla\",\"buenos dias\",\"buenas tardes\",\"buenas noches\",\"que tal\",\"como estas\"]', NULL, NULL, 0.700, 1, 15, 'general', 'activo', 0, NULL, '2025-10-05 01:38:56', NULL),
(7, 'Despedida', 'Adiós', '¡Gracias por visitarnos! Que tengas un excelente día. Si necesitas algo más, estaré aquí para ayudarte. 😊', 'adios,chao,hasta luego,bye', '[\"chau\",\"nos vemos\",\"hasta pronto\",\"me voy\",\"gracias adios\",\"bye bye\"]', NULL, NULL, 0.700, 1, 14, 'general', 'activo', 0, NULL, '2025-10-05 01:38:56', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chatbot_mensaje`
--

CREATE TABLE `chatbot_mensaje` (
  `id_chatbot_mensaje` int(11) NOT NULL,
  `id_chatbot` int(11) NOT NULL,
  `tipo_mensaje` enum('usuario','bot','sistema') NOT NULL DEFAULT 'usuario',
  `mensaje_chatbot_mensaje` text NOT NULL,
  `fecha_chatbot_mensaje` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_pedido`
--

CREATE TABLE `detalle_pedido` (
  `id_detalle_pedido` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `nombre_producto_detalle` varchar(200) NOT NULL,
  `cantidad_detalle` int(11) NOT NULL,
  `precio_unitario_detalle` decimal(10,2) NOT NULL,
  `descuento_porcentaje_detalle` decimal(5,2) NOT NULL DEFAULT 0.00,
  `subtotal_detalle` decimal(10,2) NOT NULL,
  `fecha_creacion_detalle` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Detalle de productos en cada pedido';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `direccion`
--

CREATE TABLE `direccion` (
  `id_direccion` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `nombre_direccion` varchar(100) NOT NULL,
  `departamento_direccion` varchar(100) NOT NULL,
  `provincia_direccion` varchar(100) NOT NULL,
  `distrito_direccion` varchar(100) NOT NULL,
  `referencia_direccion` text DEFAULT NULL,
  `es_principal` tinyint(1) DEFAULT 0,
  `status_direccion` tinyint(1) DEFAULT 1,
  `fecha_creacion_direccion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `favorito`
--

CREATE TABLE `favorito` (
  `id_favorito` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `fecha_agregado_favorito` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `favorito`
--

INSERT INTO `favorito` (`id_favorito`, `id_usuario`, `id_producto`, `fecha_agregado_favorito`) VALUES
(631, 7, 8, '2025-10-10 21:43:54'),
(632, 7, 2, '2025-10-10 21:44:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_pedido`
--

CREATE TABLE `historial_pedido` (
  `id_historial` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `estado_anterior` enum('pendiente','pagado','procesando','enviado','entregado','cancelado') DEFAULT NULL,
  `estado_nuevo` enum('pendiente','pagado','procesando','enviado','entregado','cancelado') NOT NULL,
  `comentario` text DEFAULT NULL,
  `id_usuario_responsable` int(11) DEFAULT NULL,
  `fecha_cambio` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Historial de cambios de estado de pedidos para auditoría';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `marca`
--

CREATE TABLE `marca` (
  `id_marca` int(11) NOT NULL,
  `codigo_marca` varchar(50) DEFAULT NULL,
  `nombre_marca` varchar(100) NOT NULL,
  `descripcion_marca` text DEFAULT NULL,
  `imagen_marca` varchar(255) DEFAULT 'default-product.png',
  `url_imagen_marca` varchar(500) NOT NULL,
  `status_marca` tinyint(1) DEFAULT 1,
  `estado_marca` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `fecha_creacion_marca` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion_marca` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `marca`
--

INSERT INTO `marca` (`id_marca`, `codigo_marca`, `nombre_marca`, `descripcion_marca`, `imagen_marca`, `url_imagen_marca`, `status_marca`, `estado_marca`, `fecha_creacion_marca`, `fecha_actualizacion_marca`) VALUES
(1, 'MARCA-001', 'Nike', 'Marca deportiva líder mundiall', 'marca_1759813274_68e49e9a7bef0.png', '/fashion-master/public/assets/img/brands/marca_1759813274_68e49e9a7bef0.png', 1, 'activo', '2025-09-30 19:47:49', '2025-10-08 02:09:42'),
(2, 'MARCA-002', 'Adidasa', 'Ropa y calzado deportivo', 'default-product.jpg', '/fashion-master/public/assets/img/default-product.jpg', 1, 'activo', '2025-09-30 19:47:49', '2025-10-08 02:18:56'),
(3, 'MARCA-003', 'Zara', 'Moda contemporánea', 'default-product.jpg', '/fashion-master/public/assets/img/default-product.jpg', 1, 'inactivo', '2025-09-30 19:47:49', '2025-10-08 02:18:51'),
(4, 'MARCA-004', 'H&M!', 'Moda rápida y accesible', 'marca_1759703732_68e2f2b42dda3.png', '/fashion-master/public/assets/img/brands/marca_1759703732_68e2f2b42dda3.png', 1, 'activo', '2025-09-30 19:47:49', '2025-10-08 18:19:17'),
(5, 'MARCA-005', 'Levis', 'Jeans y ropa casual x2', 'marca_1759889145_68e5c6f941acf.png', '/fashion-master/public/assets/img/brands/marca_1759889145_68e5c6f941acf.png', 1, 'activo', '2025-09-30 19:47:49', '2025-10-08 02:05:45'),
(6, '11111', 'prueba', '', 'default-product.jpg', '/fashion-master/public/assets/img/default-product.jpg', 0, 'inactivo', '2025-10-05 21:42:54', '2025-10-05 23:04:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimiento_stock`
--

CREATE TABLE `movimiento_stock` (
  `id_movimiento` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `tipo_movimiento` enum('entrada','salida','venta','compra','ajuste','devolucion') NOT NULL DEFAULT 'ajuste',
  `cantidad_movimiento` int(11) NOT NULL COMMENT 'Cantidad del movimiento (positivo = entrada, negativo = salida)',
  `stock_anterior` int(11) NOT NULL COMMENT 'Stock antes del movimiento',
  `stock_nuevo` int(11) NOT NULL COMMENT 'Stock después del movimiento',
  `motivo_movimiento` text DEFAULT NULL COMMENT 'Descripción del motivo del movimiento',
  `referencia_movimiento` varchar(100) DEFAULT NULL COMMENT 'Referencia externa (ej: número de orden, factura)',
  `fecha_movimiento` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP del usuario que realizó el movimiento'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Historial de todos los movimientos de stock de productos';

--
-- Volcado de datos para la tabla `movimiento_stock`
--

INSERT INTO `movimiento_stock` (`id_movimiento`, `id_producto`, `id_usuario`, `tipo_movimiento`, `cantidad_movimiento`, `stock_anterior`, `stock_nuevo`, `motivo_movimiento`, `referencia_movimiento`, `fecha_movimiento`, `ip_address`) VALUES
(1, 1, NULL, 'compra', 3, 0, 3, 'Stock inicial del sistema', NULL, '2025-09-30 19:47:49', NULL),
(2, 2, NULL, 'compra', 24, 0, 24, 'Stock inicial del sistema', NULL, '2025-09-30 19:47:49', NULL),
(3, 3, NULL, 'compra', 4, 0, 4, 'Stock inicial del sistema', NULL, '2025-09-30 19:47:49', NULL),
(4, 4, NULL, 'compra', 8, 0, 8, 'Stock inicial del sistema', NULL, '2025-09-30 19:47:49', NULL),
(5, 5, NULL, 'compra', 43, 0, 43, 'Stock inicial del sistema', NULL, '2025-09-30 19:47:49', NULL),
(6, 6, NULL, 'compra', 18, 0, 18, 'Stock inicial del sistema', NULL, '2025-09-30 19:47:49', NULL),
(7, 8, NULL, 'compra', 6, 0, 6, 'Stock inicial del sistema', NULL, '2025-10-03 04:46:28', NULL),
(8, 9, NULL, 'compra', 67, 0, 67, 'Stock inicial del sistema', NULL, '2025-10-05 21:25:31', NULL),
(16, 1, NULL, 'entrada', 5, 3, 8, 'Actualización automática de stock', NULL, '2025-10-09 21:26:19', NULL),
(17, 1, NULL, 'salida', -5, 8, 3, 'Actualización automática de stock', NULL, '2025-10-09 21:26:19', NULL),
(18, 1, NULL, 'entrada', 5, 3, 8, 'Actualización automática de stock', NULL, '2025-10-09 21:26:28', NULL),
(19, 1, NULL, 'salida', -5, 8, 3, 'Actualización automática de stock', NULL, '2025-10-09 21:26:28', NULL),
(20, 1, NULL, 'entrada', 1, 3, 4, 'Actualización automática de stock', NULL, '2025-10-10 20:59:38', NULL),
(21, 3, NULL, 'entrada', 4, 4, 8, 'Actualización automática de stock', NULL, '2025-10-10 21:00:42', NULL),
(22, 1, NULL, 'salida', -4, 4, 0, 'Actualización automática de stock', NULL, '2025-10-10 21:01:11', NULL),
(23, 2, NULL, 'salida', -4, 24, 20, 'Actualización automática de stock', NULL, '2025-10-10 23:15:08', NULL),
(24, 3, NULL, 'entrada', 1, 8, 9, 'Actualización automática de stock', NULL, '2025-10-11 00:32:30', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orden`
--

CREATE TABLE `orden` (
  `id_orden` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `estado` enum('pendiente','pagado','enviado','entregado','cancelado') DEFAULT 'pendiente',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `orden`
--

INSERT INTO `orden` (`id_orden`, `id_usuario`, `total`, `estado`, `fecha_creacion`) VALUES
(4, 2, 389.80, 'entregado', '2025-09-30 19:47:49'),
(5, 3, 159.90, 'enviado', '2025-09-30 19:47:49'),
(6, 6, 249.90, 'pagado', '2025-09-30 19:47:49'),
(7, 2, 89.90, 'pendiente', '2025-09-30 19:47:49');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `id_token` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_expiracion` timestamp NOT NULL DEFAULT (current_timestamp() + interval 1 hour),
  `usado` tinyint(1) DEFAULT 0,
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tokens para recuperación de contraseñas con validez de 1 hora';

--
-- Volcado de datos para la tabla `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`id_token`, `id_usuario`, `token`, `email`, `fecha_creacion`, `fecha_expiracion`, `usado`, `ip_address`) VALUES
(4, 7, 'ba784e453de5792cfe7ae30559d6083fd78fc5614e5c3b45e7d6236677fa366e', 'spiritboom672@gmail.com', '2025-10-09 23:25:51', '2025-10-10 00:25:51', 1, '::1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido`
--

CREATE TABLE `pedido` (
  `id_pedido` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `nombre_cliente_pedido` varchar(100) NOT NULL,
  `email_cliente_pedido` varchar(100) NOT NULL,
  `telefono_cliente_pedido` varchar(20) NOT NULL,
  `dni_ruc_pedido` varchar(20) NOT NULL,
  `direccion_envio_pedido` text NOT NULL,
  `departamento_pedido` varchar(100) NOT NULL,
  `provincia_pedido` varchar(100) NOT NULL,
  `distrito_pedido` varchar(100) NOT NULL,
  `tipo_comprobante_pedido` enum('boleta','factura') NOT NULL DEFAULT 'boleta',
  `razon_social_pedido` varchar(200) DEFAULT NULL,
  `metodo_pago_pedido` enum('tarjeta','transferencia','yape','efectivo') NOT NULL,
  `subtotal_pedido` decimal(10,2) NOT NULL,
  `costo_envio_pedido` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_pedido` decimal(10,2) NOT NULL,
  `notas_pedido` text DEFAULT NULL,
  `estado_pedido` enum('pendiente','pagado','procesando','enviado','entregado','cancelado') NOT NULL DEFAULT 'pendiente',
  `numero_tracking` varchar(100) DEFAULT NULL,
  `fecha_pedido` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_pago` timestamp NULL DEFAULT NULL,
  `fecha_envio` timestamp NULL DEFAULT NULL,
  `fecha_entrega` timestamp NULL DEFAULT NULL,
  `fecha_actualizacion` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Almacena informaci??n principal de los pedidos realizados';

--
-- Disparadores `pedido`
--
DELIMITER $$
CREATE TRIGGER `after_pedido_update_estado` AFTER UPDATE ON `pedido` FOR EACH ROW BEGIN
  IF OLD.estado_pedido != NEW.estado_pedido THEN
    INSERT INTO `historial_pedido` (
      `id_pedido`, 
      `estado_anterior`, 
      `estado_nuevo`, 
      `comentario`
    ) VALUES (
      NEW.id_pedido,
      OLD.estado_pedido,
      NEW.estado_pedido,
      CONCAT('Estado actualizado de ', OLD.estado_pedido, ' a ', NEW.estado_pedido)
    );
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `id_producto` int(11) NOT NULL,
  `nombre_producto` varchar(200) NOT NULL,
  `codigo` varchar(100) DEFAULT NULL,
  `descripcion_producto` text DEFAULT NULL,
  `id_categoria` int(11) NOT NULL,
  `id_marca` int(11) DEFAULT NULL,
  `precio_producto` decimal(10,2) NOT NULL,
  `descuento_porcentaje_producto` decimal(5,2) DEFAULT 0.00,
  `genero_producto` enum('M','F','Unisex','Kids') DEFAULT 'Unisex',
  `en_oferta_producto` tinyint(1) DEFAULT 0,
  `stock_actual_producto` int(11) DEFAULT 0,
  `stock_minimo_producto` int(11) NOT NULL DEFAULT 20,
  `stock_maximo_producto` int(11) NOT NULL DEFAULT 100,
  `imagen_producto` varchar(255) DEFAULT 'default-product.png',
  `url_imagen_producto` varchar(500) NOT NULL,
  `status_producto` tinyint(1) DEFAULT 1,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `fecha_creacion_producto` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion_producto` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `producto`
--

INSERT INTO `producto` (`id_producto`, `nombre_producto`, `codigo`, `descripcion_producto`, `id_categoria`, `id_marca`, `precio_producto`, `descuento_porcentaje_producto`, `genero_producto`, `en_oferta_producto`, `stock_actual_producto`, `stock_minimo_producto`, `stock_maximo_producto`, `imagen_producto`, `url_imagen_producto`, `status_producto`, `estado`, `fecha_creacion_producto`, `fecha_actualizacion_producto`) VALUES
(1, 'Camisa Casual Nike', 'CAM-NIKE-001', 'Camisa deportiva de algodón', 3, 4, 90.00, 10.00, 'M', 1, 0, 0, 0, 'product_68e1d018c5d29.jpg', '/fashion-master/public/assets/img/products/product_68e1d018c5d29.jpg', 1, 'inactivo', '2025-09-30 19:47:49', '2025-10-11 00:15:44'),
(2, 'Pantalón Jean Levis 501', 'PAN-LEVIS-501', 'Jean clásico corte recto', 4, 5, 90.00, 0.00, 'Unisex', 0, 20, 20, 100, 'product_68e1d379ba460.jpg', '/fashion-master/public/assets/img/products/product_68e1d379ba460.jpg', 1, 'activo', '2025-09-30 19:47:49', '2025-10-10 23:15:08'),
(3, 'dos amigues :\'D', 'ZAP-NIKE-AIR', 'Zapatillas deportivas con cámara de aire', 16, 1, 299.90, 15.00, 'Unisex', 1, 9, 0, 0, 'product_68e9a4892f2a9.jpg', '/fashion-master/public/assets/img/products/product_68e9a4892f2a9.jpg', 1, 'activo', '2025-09-30 19:47:49', '2025-10-11 00:32:30'),
(4, 'Vestido Casual Zara', 'VES-ZARA-001', 'Vestido elegante para ocasiones especiales', 4, 3, 159.90, 20.00, 'F', 1, 8, 20, 100, '/fashion-master/public/assets/img/default-product.jpg', '/fashion-master/public/assets/img/default-product.jpg', 1, 'inactivo', '2025-09-30 19:47:49', '2025-10-04 00:50:05'),
(5, 'Camisa Formal H&M', 'CAM-HM-002', 'Camisa de vestir para oficina', 4, 4, 69.90, 50.00, 'M', 0, 43, 0, 0, '/fashion-master/public/assets/img/default-product.jpg', '/fashion-master/public/assets/img/default-product.jpg', 1, 'activo', '2025-09-30 19:47:49', '2025-10-10 21:46:25'),
(6, 'Zapatillas Adidas Stan Smith', 'ZAP-ADIDAS-SS', 'Zapatillas clásicas de tenis', 3, 2, 199.90, 5.00, 'Unisex', 0, 18, 20, 100, '/fashion-master/public/assets/img/default-product.jpg', '/fashion-master/public/assets/img/default-product.jpg', 1, 'activo', '2025-09-30 19:47:49', '2025-10-03 22:43:55'),
(8, 'producto 1', 'sdadadsa', 'nada', 4, 4, 2.00, 100.00, 'Unisex', 0, 6, 0, 0, 'product_68e8578c49ec2.jpg', '/fashion-master/public/assets/img/products/product_68e8578c49ec2.jpg', 1, 'activo', '2025-10-03 04:46:28', '2025-10-10 00:47:08'),
(9, 'prenda ejemplo', '2323', 'prenda comun', 1, 4, 1.00, 1.00, 'Unisex', 0, 67, 0, 0, 'product_68e95d0cf1494.jpg', '/fashion-master/public/assets/img/products/product_68e95d0cf1494.jpg', 1, 'activo', '2025-10-05 21:25:31', '2025-10-10 19:22:52');

--
-- Disparadores `producto`
--
DELIMITER $$
CREATE TRIGGER `trigger_producto_stock_update` AFTER UPDATE ON `producto` FOR EACH ROW BEGIN
            IF NEW.stock_actual_producto != OLD.stock_actual_producto THEN
                INSERT INTO `movimiento_stock` (
                    `id_producto`,
                    `tipo_movimiento`,
                    `cantidad_movimiento`,
                    `stock_anterior`,
                    `stock_nuevo`,
                    `motivo_movimiento`
                ) VALUES (
                    NEW.id_producto,
                    CASE 
                        WHEN NEW.stock_actual_producto > OLD.stock_actual_producto THEN 'entrada'
                        WHEN NEW.stock_actual_producto < OLD.stock_actual_producto THEN 'salida'
                        ELSE 'ajuste'
                    END,
                    NEW.stock_actual_producto - OLD.stock_actual_producto,
                    OLD.stock_actual_producto,
                    NEW.stock_actual_producto,
                    CONCAT('Actualización automática de stock')
                );
            END IF;
        END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resena`
--

CREATE TABLE `resena` (
  `id_resena` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_orden` int(11) DEFAULT NULL,
  `calificacion` tinyint(4) NOT NULL CHECK (`calificacion` >= 1 and `calificacion` <= 5),
  `titulo` varchar(200) DEFAULT NULL,
  `comentario` text DEFAULT NULL,
  `verificada` tinyint(1) DEFAULT 0,
  `aprobada` tinyint(1) DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `resena`
--

INSERT INTO `resena` (`id_resena`, `id_producto`, `id_usuario`, `id_orden`, `calificacion`, `titulo`, `comentario`, `verificada`, `aprobada`, `fecha_creacion`) VALUES
(1, 1, 2, NULL, 5, 'Excelente calidad', 'La camisa es de muy buena calidad, el material es suave y cómodo. Muy recomendable.', 1, 1, '2025-10-10 15:39:29'),
(2, 1, 3, NULL, 4, 'Buena compra', 'Me gustó mucho, aunque el color es un poco diferente a la foto.', 1, 1, '2025-10-10 15:39:29'),
(3, 1, 6, NULL, 5, 'Perfecta!', 'Justo lo que buscaba, la talla es correcta y llego rapido.', 1, 1, '2025-10-10 15:39:29'),
(4, 2, 2, NULL, 5, 'Clásico que nunca falla', 'Los Levis 501 son siempre una buena inversión. Calidad garantizada.', 1, 1, '2025-10-10 15:39:29'),
(5, 2, 3, NULL, 4, 'Buen jean', 'Es un buen producto, aunque el precio es un poco elevado.', 1, 1, '2025-10-10 15:39:29'),
(6, 2, 4, NULL, 5, 'Me encanta', 'Super cómodo y se ve muy bien. Vale cada peso.', 1, 1, '2025-10-10 15:39:29'),
(7, 2, 6, NULL, 4, 'Recomendado', 'Buena calidad de tela, aunque tarda un poco en ablandarse.', 1, 1, '2025-10-10 15:39:29'),
(8, 3, 2, NULL, 5, 'Las mejores zapatillas', 'Súper cómodas para correr y para uso diario. Excelente inversión.', 1, 1, '2025-10-10 15:39:29'),
(9, 3, 3, NULL, 5, 'Increíbles!', 'Son hermosas y muy cómodas. La suela con aire se siente genial.', 1, 1, '2025-10-10 15:39:29'),
(10, 3, 4, NULL, 4, 'Muy buenas', 'Me gustan mucho, aunque son un poco grandes. Pedí media talla menos.', 1, 1, '2025-10-10 15:39:29'),
(11, 3, 5, NULL, 5, 'Perfectas', 'Llegaron rápido y en perfectas condiciones. 100% recomendadas.', 1, 1, '2025-10-10 15:39:29'),
(12, 3, 6, NULL, 5, 'Love them!', 'Son exactamente como en las fotos. Muy contenta con la compra.', 1, 1, '2025-10-10 15:39:29'),
(13, 4, 3, NULL, 4, 'Bonito vestido', 'Es muy lindo, la tela es ligera y fresca.', 1, 1, '2025-10-10 15:39:29'),
(14, 4, 6, NULL, 5, 'Hermoso!', 'Me encantó, es elegante y cómodo a la vez.', 1, 1, '2025-10-10 15:39:29'),
(15, 5, 2, NULL, 4, 'Buena para la oficina', 'Perfecta para el trabajo, se ve formal y es cómoda.', 1, 1, '2025-10-10 15:39:29'),
(16, 5, 4, NULL, 3, 'Normal', 'Es una camisa normal, nada especial pero cumple su función.', 1, 1, '2025-10-10 15:39:29'),
(17, 5, 5, NULL, 4, 'Recomendable', 'Buena relación calidad-precio. La uso frecuentemente.', 1, 1, '2025-10-10 15:39:29'),
(18, 6, 2, NULL, 5, 'Clásico atemporal', 'Las Stan Smith nunca pasan de moda. Excelente compra.', 1, 1, '2025-10-10 15:39:29'),
(19, 6, 3, NULL, 5, 'Perfectas', 'Son hermosas y combinan con todo. Muy cómodas.', 1, 1, '2025-10-10 15:39:29'),
(20, 6, 4, NULL, 4, 'Buenas zapatillas', 'Me gustan mucho, aunque al principio eran un poco duras.', 1, 1, '2025-10-10 15:39:29'),
(21, 8, 2, NULL, 3, 'Está bien', 'Es un producto básico, cumple lo que promete.', 1, 1, '2025-10-10 15:39:29'),
(22, 8, 5, NULL, 4, 'Bueno', 'Por el precio está bastante bien.', 1, 1, '2025-10-10 15:39:29'),
(23, 9, 3, NULL, 4, 'Satisfecho', 'Llegó en buenas condiciones, es como se describe.', 1, 1, '2025-10-10 15:39:29'),
(24, 9, 6, NULL, 5, 'Genial', 'Me encantó, superó mis expectativas.', 1, 1, '2025-10-10 15:39:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesion`
--

CREATE TABLE `sesion` (
  `id_sesion` varchar(191) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_expiracion` timestamp NOT NULL DEFAULT (current_timestamp() + interval 1 hour),
  `ip_address` varchar(45) DEFAULT NULL,
  `status_sesion` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL,
  `username_usuario` varchar(50) NOT NULL,
  `password_usuario` varchar(255) NOT NULL,
  `email_usuario` varchar(100) NOT NULL,
  `nombre_usuario` varchar(100) NOT NULL,
  `apellido_usuario` varchar(100) NOT NULL,
  `telefono_usuario` varchar(20) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `genero_usuario` enum('M','F','Otro') DEFAULT 'Otro',
  `avatar_usuario` varchar(255) DEFAULT 'default-avatar.png',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultimo_acceso` timestamp NULL DEFAULT NULL,
  `status_usuario` tinyint(1) DEFAULT 1,
  `verificado_usuario` tinyint(1) DEFAULT 0,
  `rol_usuario` enum('cliente','admin','vendedor') DEFAULT 'cliente',
  `estado_usuario` enum('activo','inactivo') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `username_usuario`, `password_usuario`, `email_usuario`, `nombre_usuario`, `apellido_usuario`, `telefono_usuario`, `fecha_nacimiento`, `genero_usuario`, `avatar_usuario`, `fecha_registro`, `ultimo_acceso`, `status_usuario`, `verificado_usuario`, `rol_usuario`, `estado_usuario`) VALUES
(1, 'admin', 'admin123', 'admin@sleppystore.com', 'Administrador', 'Principal', '+51987654321', '1990-01-15', 'M', 'default-avatar.png', '2025-09-30 19:46:43', '2025-10-11 00:09:07', 1, 1, 'admin', 'activo'),
(2, 'juan_perez', 'juan123', 'juan@email.com', 'Juan', 'Pérez', '+51912345678', '1992-03-20', 'M', 'default-avatar.png', '2025-09-30 19:46:43', NULL, 1, 1, 'cliente', 'activo'),
(3, 'maria_garcia', 'maria123', 'maria@email.com', 'María', 'García', '+51923456789', '1988-07-10', 'F', 'default-avatar.png', '2025-09-30 19:46:43', NULL, 1, 1, 'cliente', 'activo'),
(4, 'carlos_lopez', 'carlos123', 'carlos@email.com', 'Carlos', 'López', '+51934567890', '1995-11-25', 'M', 'default-avatar.png', '2025-09-30 19:46:43', NULL, 1, 0, 'cliente', 'activo'),
(5, 'ana_martinez', 'ana123', 'ana@email.com', 'Ana', 'Martínez', '+51945678901', '1993-05-08', 'F', 'default-avatar.png', '2025-09-30 19:46:43', NULL, 1, 1, 'vendedor', 'activo'),
(6, 'sofia_torres', 'sofia123', 'sofia@email.com', 'Sofía', 'Torres', '+51967890123', '1996-02-14', 'F', 'default-avatar.png', '2025-09-30 19:46:43', NULL, 1, 1, 'cliente', 'activo'),
(7, 'julito', '123456', 'spiritboom672@gmail.com', 'Julito', 'sleppy21', '986079838', '2006-04-21', 'M', 'default-avatar.png', '2025-10-07 18:11:51', '2025-10-10 21:43:46', 1, 0, 'cliente', 'activo');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD PRIMARY KEY (`id_carrito`),
  ADD KEY `idx_carrito_usuario` (`id_usuario`),
  ADD KEY `idx_carrito_producto` (`id_producto`);

--
-- Indices de la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`id_categoria`),
  ADD KEY `idx_status_categoria` (`status_categoria`);

--
-- Indices de la tabla `chatbot`
--
ALTER TABLE `chatbot`
  ADD PRIMARY KEY (`id_chatbot`),
  ADD KEY `idx_chat_usuario` (`id_usuario`),
  ADD KEY `idx_chat_sesion` (`id_sesion`),
  ADD KEY `idx_ultima_actividad` (`ultima_actividad_chatbot`);

--
-- Indices de la tabla `chatbot_conocimiento`
--
ALTER TABLE `chatbot_conocimiento`
  ADD PRIMARY KEY (`id_conocimiento`),
  ADD KEY `idx_categoria` (`categoria_conocimiento`),
  ADD KEY `idx_estado_conocimiento` (`estado_conocimiento`),
  ADD KEY `idx_tipo_contenido` (`tipo_contenido`),
  ADD KEY `idx_prioridad` (`prioridad`);
ALTER TABLE `chatbot_conocimiento` ADD FULLTEXT KEY `idx_pregunta_respuesta` (`pregunta`,`respuesta`,`palabras_clave`);

--
-- Indices de la tabla `chatbot_mensaje`
--
ALTER TABLE `chatbot_mensaje`
  ADD PRIMARY KEY (`id_chatbot_mensaje`),
  ADD KEY `idx_mensaje_chatbot` (`id_chatbot`),
  ADD KEY `idx_tipo_mensaje` (`tipo_mensaje`),
  ADD KEY `idx_fecha_mensaje` (`fecha_chatbot_mensaje`);

--
-- Indices de la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD PRIMARY KEY (`id_detalle_pedido`),
  ADD KEY `idx_detalle_pedido` (`id_pedido`),
  ADD KEY `idx_detalle_producto` (`id_producto`);

--
-- Indices de la tabla `direccion`
--
ALTER TABLE `direccion`
  ADD PRIMARY KEY (`id_direccion`),
  ADD KEY `idx_usuario_id` (`id_usuario`),
  ADD KEY `idx_es_principal` (`es_principal`);

--
-- Indices de la tabla `favorito`
--
ALTER TABLE `favorito`
  ADD PRIMARY KEY (`id_favorito`),
  ADD UNIQUE KEY `unique_user_product` (`id_usuario`,`id_producto`),
  ADD KEY `idx_fav_usuario` (`id_usuario`),
  ADD KEY `idx_fav_producto` (`id_producto`);

--
-- Indices de la tabla `historial_pedido`
--
ALTER TABLE `historial_pedido`
  ADD PRIMARY KEY (`id_historial`),
  ADD KEY `idx_historial_pedido` (`id_pedido`),
  ADD KEY `idx_estado_nuevo` (`estado_nuevo`),
  ADD KEY `idx_fecha_cambio` (`fecha_cambio`),
  ADD KEY `fk_historial_usuario` (`id_usuario_responsable`);

--
-- Indices de la tabla `marca`
--
ALTER TABLE `marca`
  ADD PRIMARY KEY (`id_marca`),
  ADD UNIQUE KEY `nombre_marca` (`nombre_marca`),
  ADD KEY `idx_status_marca` (`status_marca`);

--
-- Indices de la tabla `movimiento_stock`
--
ALTER TABLE `movimiento_stock`
  ADD PRIMARY KEY (`id_movimiento`),
  ADD KEY `idx_producto` (`id_producto`),
  ADD KEY `idx_usuario` (`id_usuario`),
  ADD KEY `idx_tipo` (`tipo_movimiento`),
  ADD KEY `idx_fecha` (`fecha_movimiento`),
  ADD KEY `idx_referencia` (`referencia_movimiento`);

--
-- Indices de la tabla `orden`
--
ALTER TABLE `orden`
  ADD PRIMARY KEY (`id_orden`),
  ADD KEY `idx_orden_usuario` (`id_usuario`),
  ADD KEY `idx_estado_orden` (`estado`);

--
-- Indices de la tabla `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id_token`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_token_usuario` (`id_usuario`),
  ADD KEY `idx_token_email` (`email`),
  ADD KEY `idx_fecha_expiracion` (`fecha_expiracion`),
  ADD KEY `idx_token_activo` (`token`,`usado`,`fecha_expiracion`);

--
-- Indices de la tabla `pedido`
--
ALTER TABLE `pedido`
  ADD PRIMARY KEY (`id_pedido`),
  ADD KEY `idx_pedido_usuario` (`id_usuario`),
  ADD KEY `idx_estado_pedido` (`estado_pedido`),
  ADD KEY `idx_fecha_pedido` (`fecha_pedido`),
  ADD KEY `idx_metodo_pago` (`metodo_pago_pedido`),
  ADD KEY `idx_dni_ruc` (`dni_ruc_pedido`),
  ADD KEY `idx_tipo_comprobante` (`tipo_comprobante_pedido`);

--
-- Indices de la tabla `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`id_producto`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `idx_categoria` (`id_categoria`),
  ADD KEY `idx_marca` (`id_marca`),
  ADD KEY `idx_status_producto` (`status_producto`),
  ADD KEY `idx_stock_bajo` (`stock_actual_producto`,`stock_minimo_producto`);

--
-- Indices de la tabla `resena`
--
ALTER TABLE `resena`
  ADD PRIMARY KEY (`id_resena`),
  ADD UNIQUE KEY `unique_user_product_order` (`id_usuario`,`id_producto`,`id_orden`),
  ADD KEY `id_orden` (`id_orden`),
  ADD KEY `idx_resena_producto` (`id_producto`),
  ADD KEY `idx_resena_usuario` (`id_usuario`),
  ADD KEY `idx_calificacion` (`calificacion`),
  ADD KEY `idx_aprobada` (`aprobada`);

--
-- Indices de la tabla `sesion`
--
ALTER TABLE `sesion`
  ADD PRIMARY KEY (`id_sesion`),
  ADD KEY `idx_usuario_id_sesion` (`id_usuario`),
  ADD KEY `idx_fecha_expiracion` (`fecha_expiracion`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `username_usuario` (`username_usuario`),
  ADD UNIQUE KEY `email_usuario` (`email_usuario`),
  ADD KEY `idx_username` (`username_usuario`),
  ADD KEY `idx_email` (`email_usuario`),
  ADD KEY `idx_status` (`status_usuario`),
  ADD KEY `idx_estado_usuario` (`estado_usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `carrito`
--
ALTER TABLE `carrito`
  MODIFY `id_carrito` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `chatbot`
--
ALTER TABLE `chatbot`
  MODIFY `id_chatbot` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `chatbot_conocimiento`
--
ALTER TABLE `chatbot_conocimiento`
  MODIFY `id_conocimiento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `chatbot_mensaje`
--
ALTER TABLE `chatbot_mensaje`
  MODIFY `id_chatbot_mensaje` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  MODIFY `id_detalle_pedido` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `direccion`
--
ALTER TABLE `direccion`
  MODIFY `id_direccion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `favorito`
--
ALTER TABLE `favorito`
  MODIFY `id_favorito` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=642;

--
-- AUTO_INCREMENT de la tabla `historial_pedido`
--
ALTER TABLE `historial_pedido`
  MODIFY `id_historial` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `marca`
--
ALTER TABLE `marca`
  MODIFY `id_marca` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `movimiento_stock`
--
ALTER TABLE `movimiento_stock`
  MODIFY `id_movimiento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `orden`
--
ALTER TABLE `orden`
  MODIFY `id_orden` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id_token` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `pedido`
--
ALTER TABLE `pedido`
  MODIFY `id_pedido` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `resena`
--
ALTER TABLE `resena`
  MODIFY `id_resena` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD CONSTRAINT `carrito_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE SET NULL,
  ADD CONSTRAINT `carrito_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`) ON DELETE CASCADE;

--
-- Filtros para la tabla `chatbot`
--
ALTER TABLE `chatbot`
  ADD CONSTRAINT `chatbot_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE SET NULL,
  ADD CONSTRAINT `chatbot_ibfk_2` FOREIGN KEY (`id_sesion`) REFERENCES `sesion` (`id_sesion`) ON DELETE SET NULL;

--
-- Filtros para la tabla `chatbot_mensaje`
--
ALTER TABLE `chatbot_mensaje`
  ADD CONSTRAINT `chatbot_mensaje_ibfk_1` FOREIGN KEY (`id_chatbot`) REFERENCES `chatbot` (`id_chatbot`) ON DELETE CASCADE;

--
-- Filtros para la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD CONSTRAINT `fk_detalle_pedido` FOREIGN KEY (`id_pedido`) REFERENCES `pedido` (`id_pedido`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_detalle_producto` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`) ON DELETE CASCADE;

--
-- Filtros para la tabla `direccion`
--
ALTER TABLE `direccion`
  ADD CONSTRAINT `direccion_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `favorito`
--
ALTER TABLE `favorito`
  ADD CONSTRAINT `favorito_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorito_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`) ON DELETE CASCADE;

--
-- Filtros para la tabla `historial_pedido`
--
ALTER TABLE `historial_pedido`
  ADD CONSTRAINT `fk_historial_pedido` FOREIGN KEY (`id_pedido`) REFERENCES `pedido` (`id_pedido`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_historial_usuario` FOREIGN KEY (`id_usuario_responsable`) REFERENCES `usuario` (`id_usuario`) ON DELETE SET NULL;

--
-- Filtros para la tabla `movimiento_stock`
--
ALTER TABLE `movimiento_stock`
  ADD CONSTRAINT `fk_movimiento_producto` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_movimiento_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `orden`
--
ALTER TABLE `orden`
  ADD CONSTRAINT `orden_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE SET NULL;

--
-- Filtros para la tabla `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD CONSTRAINT `password_reset_tokens_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pedido`
--
ALTER TABLE `pedido`
  ADD CONSTRAINT `fk_pedido_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `producto`
--
ALTER TABLE `producto`
  ADD CONSTRAINT `producto_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categoria` (`id_categoria`),
  ADD CONSTRAINT `producto_ibfk_2` FOREIGN KEY (`id_marca`) REFERENCES `marca` (`id_marca`) ON DELETE SET NULL;

--
-- Filtros para la tabla `resena`
--
ALTER TABLE `resena`
  ADD CONSTRAINT `resena_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`) ON DELETE CASCADE,
  ADD CONSTRAINT `resena_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `resena_ibfk_3` FOREIGN KEY (`id_orden`) REFERENCES `orden` (`id_orden`) ON DELETE SET NULL;

--
-- Filtros para la tabla `sesion`
--
ALTER TABLE `sesion`
  ADD CONSTRAINT `sesion_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
