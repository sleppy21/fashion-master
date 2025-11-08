-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 08-11-2025 a las 05:44:19
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
(229, 9, 9, 1, '2025-11-01 21:19:41'),
(386, 1, 3, 1, '2025-11-07 02:40:31'),
(400, 7, 11, 5, '2025-11-07 05:13:35');

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
(1, 'CAT-000', 'Camisas', 'Camisas para hombre y mujer', 'categoria-1-1760141572.jpg', '/fashion-master/public/assets/img/categories/categoria-1-1760141572.jpg', 1, 'activo', '2025-09-30 19:44:29', '2025-10-12 18:08:42'),
(2, 'CAT-002', 'Pantalones', 'Pantalones de todo tipo', 'default-product.jpg', '/fashion-master/public/assets/img/default-product.jpg', 1, 'activo', '2025-09-30 19:44:29', '2025-10-08 04:20:20'),
(3, 'CAT-003', 'Zapatos', 'Calzado para toda ocasión', 'default-product.jpg', '/fashion-master/public/assets/img/default-product.jpg', 1, 'activo', '2025-09-30 19:44:29', '2025-10-08 04:01:00'),
(4, 'CAT-3', 'Accesorios', 'Complementos y accesorios', 'categoria-4-1760141564.png', '/fashion-master/public/assets/img/categories/categoria-4-1760141564.png', 1, 'activo', '2025-09-30 19:44:29', '2025-10-21 00:25:50'),
(5, 'CAT-005', 'Vestidos', 'Vestidos elegantes y casuales', 'default-product.jpg', '/fashion-master/public/assets/img/default-product.jpg', 1, 'activo', '2025-09-30 19:44:29', '2025-10-21 00:25:48'),
(16, 'dfewrw', 'hola', 'una categoria', 'categoria-1759897392-68e5e730bf141.jpg', '/fashion-master/public/assets/img/categories/categoria-1759897392-68e5e730bf141.jpg', 1, 'activo', '2025-10-08 04:23:12', '2025-10-20 23:36:42'),
(17, 'fffff', 'pipip', 'hola', 'categoria_68f6c915130f7.jpg', '/fashion-master/public/assets/img/categories/categoria_68f6c915130f7.jpg', 1, 'activo', '2025-10-08 04:25:31', '2025-10-21 00:27:24'),
(18, 'CAT-006', 'accesorios gucci', 'de chil', 'categoria_68f6c9650aa84.jpg', '/fashion-master/public/assets/img/categories/categoria_68f6c9650aa84.jpg', 1, 'activo', '2025-10-20 23:44:37', '2025-11-01 21:37:33'),
(19, 'CAT-007', 'prueba', 'hola', 'categoria_68f6cab04027f.jpg', '/fashion-master/public/assets/img/categories/categoria_68f6cab04027f.jpg', 1, 'activo', '2025-10-20 23:50:08', '2025-11-01 21:37:30');

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

--
-- Volcado de datos para la tabla `detalle_pedido`
--

INSERT INTO `detalle_pedido` (`id_detalle_pedido`, `id_pedido`, `id_producto`, `nombre_producto_detalle`, `cantidad_detalle`, `precio_unitario_detalle`, `descuento_porcentaje_detalle`, `subtotal_detalle`, `fecha_creacion_detalle`) VALUES
(1, 1, 9, 'prenda ejemplo', 1, 1.00, 1.00, 0.99, '2025-10-15 04:29:46'),
(2, 1, 8, 'producto 1', 1, 2.00, 100.00, 0.00, '2025-10-15 04:29:46'),
(3, 1, 11, 'producto de prueba', 1, 34.43, 16.00, 28.92, '2025-10-15 04:29:46'),
(4, 1, 6, 'Zapatillas Adidas Stan Smith', 4, 199.90, 5.00, 759.62, '2025-10-15 04:29:46'),
(5, 2, 9, 'prenda ejemplo', 1, 1.00, 1.00, 0.99, '2025-10-15 04:36:13'),
(6, 3, 8, 'producto 1', 3, 2.00, 100.00, 0.00, '2025-10-15 16:33:46'),
(7, 3, 9, 'prenda ejemplo', 1, 1.00, 1.00, 0.99, '2025-10-15 16:33:46'),
(8, 4, 10, 'carrito a control remoto :c', 1, 800.00, 15.00, 680.00, '2025-10-20 03:58:28'),
(9, 4, 11, 'producto de prueba', 1, 34.43, 16.00, 28.92, '2025-10-20 03:58:28'),
(10, 4, 9, 'prenda ejemplo', 1, 1.00, 1.00, 0.99, '2025-10-20 03:58:28'),
(11, 4, 8, 'producto 1', 1, 2.00, 100.00, 0.00, '2025-10-20 03:58:28'),
(12, 4, 3, 'dos amigues :\'D', 1, 299.90, 15.00, 254.92, '2025-10-20 03:58:28'),
(13, 4, 1, 'Camisa Casual Nike', 1, 90.00, 0.00, 90.00, '2025-10-20 03:58:28'),
(14, 4, 4, 'Vestido Casual Zara', 1, 159.90, 20.00, 127.92, '2025-10-20 03:58:28'),
(15, 4, 5, 'Camisa Formal H&M', 1, 69.90, 50.00, 34.95, '2025-10-20 03:58:28'),
(16, 5, 8, 'producto 1', 1, 2.00, 100.00, 0.00, '2025-10-20 04:11:27'),
(17, 5, 5, 'Camisa Formal H&M', 1, 69.90, 50.00, 34.95, '2025-10-20 04:11:27'),
(18, 5, 9, 'prenda ejemplo', 1, 1.00, 1.00, 0.99, '2025-10-20 04:11:27'),
(19, 6, 11, 'producto de prueba', 1, 34.43, 16.00, 28.92, '2025-10-20 04:19:48'),
(20, 7, 9, 'prenda ejemplo', 1, 1.00, 1.00, 0.99, '2025-10-20 04:45:01'),
(21, 8, 2, 'Pantalón Jean Levis 501', 5, 90.00, 0.00, 450.00, '2025-10-21 00:38:36'),
(22, 8, 11, 'producto de prueba', 28, 34.43, 16.00, 809.79, '2025-10-21 00:38:36'),
(23, 9, 2, 'Pantalón Jean Levis 501', 2, 90.00, 0.00, 180.00, '2025-11-01 16:11:55'),
(24, 9, 11, 'producto de prueba', 2, 34.43, 16.00, 57.84, '2025-11-01 16:11:55'),
(25, 9, 12, 'Pruebita', 1, 12.00, 0.00, 12.00, '2025-11-01 16:11:55'),
(26, 10, 12, 'Pruebita', 1, 12.00, 0.00, 12.00, '2025-11-03 18:53:03'),
(27, 10, 11, 'producto de prueba', 1, 34.43, 16.00, 28.92, '2025-11-03 18:53:03'),
(28, 10, 9, 'prenda ejemplo', 1, 1.00, 1.00, 0.99, '2025-11-03 18:53:03'),
(29, 10, 10, 'carrito a control remoto :c', 1, 800.00, 15.00, 680.00, '2025-11-03 18:53:03'),
(30, 11, 4, 'Vestido Casual Zara', 1, 159.90, 20.00, 127.92, '2025-11-07 00:43:51'),
(31, 11, 6, 'Zapatillas Adidas Stan Smith', 12, 199.90, 5.00, 2278.86, '2025-11-07 00:43:51'),
(32, 12, 3, 'dos amigues :\'D', 1, 299.90, 15.00, 254.92, '2025-11-07 01:57:11'),
(33, 12, 5, 'Camisa Formal H&M', 5, 69.90, 50.00, 174.75, '2025-11-07 01:57:11'),
(34, 12, 12, 'Pruebita', 1, 12.00, 0.00, 12.00, '2025-11-07 01:57:11'),
(35, 12, 11, 'producto de prueba', 1, 34.43, 16.00, 28.92, '2025-11-07 01:57:11');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `direccion`
--

CREATE TABLE `direccion` (
  `id_direccion` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `nombre_cliente_direccion` varchar(100) NOT NULL,
  `telefono_direccion` varchar(20) DEFAULT NULL,
  `email_direccion` varchar(100) DEFAULT NULL,
  `dni_ruc_direccion` varchar(11) DEFAULT NULL,
  `razon_social_direccion` varchar(200) DEFAULT NULL,
  `direccion_completa_direccion` text NOT NULL,
  `departamento_direccion` varchar(100) NOT NULL,
  `provincia_direccion` varchar(100) NOT NULL,
  `distrito_direccion` varchar(100) NOT NULL,
  `referencia_direccion` text DEFAULT NULL,
  `metodo_pago_favorito` enum('tarjeta','transferencia','yape','efectivo') DEFAULT NULL,
  `es_principal` tinyint(1) DEFAULT 0,
  `status_direccion` tinyint(1) DEFAULT 1,
  `fecha_creacion_direccion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `direccion`
--

INSERT INTO `direccion` (`id_direccion`, `id_usuario`, `nombre_cliente_direccion`, `telefono_direccion`, `email_direccion`, `dni_ruc_direccion`, `razon_social_direccion`, `direccion_completa_direccion`, `departamento_direccion`, `provincia_direccion`, `distrito_direccion`, `referencia_direccion`, `metodo_pago_favorito`, `es_principal`, `status_direccion`, `fecha_creacion_direccion`) VALUES
(1, 1, 'Administrador Principal', '+51987654321', 'admin@sleppystore.com', '12345678', NULL, '6666666, Olleros, Chachapoyas, Amazonas', 'Amazonas', 'Chachapoyas', 'Olleros', NULL, NULL, 1, 0, '2025-10-15 04:36:13'),
(2, 7, 'Julito sleppy21', '986079838', 'spiritboom672@gmail.com', NULL, NULL, '77777777, Neshuya, Padre Abad, Ucayali', 'Ucayali', 'Padre Abad', 'Neshuya', NULL, NULL, 0, 0, '2025-10-15 16:33:46'),
(3, 7, 'Julito sleppy21ghh', '986079838', 'spiritboom672@gmail.com', '56565655555', NULL, '5555 (Ref: 555), Campoverde, Coronel Portillo, Ucayali', 'Ucayali', 'Coronel Portillo', 'Campoverde', '555', NULL, 0, 0, '2025-10-15 17:21:03'),
(4, 7, 'julio', '986079838', 'spiritboom672@gmail.com', '44444444444', '4444444', '4444 (Ref: 444), Campoverde, Coronel Portillo, Ucayali', 'Arequipa', 'Callao', 'Miraflores', '444', NULL, 0, 0, '2025-10-15 18:07:52'),
(5, 7, 'Julito sleppy21', '986079838', 'spiritboom672@gmail.com', '44444444', '', 'fdgfdgfdgfdsg (Ref: fdsgdsfgds), Pararin, Recuay, Áncash', 'Áncash', 'Recuay', 'Pararin', 'fdsgdsfgds', NULL, 0, 0, '2025-10-15 18:18:20'),
(6, 7, 'sd123123', '23232323', 'spiritboom672@gmail.com', '87654321', NULL, '232323232', 'Arequipa', 'Callao', 'Miraflores', '2323232', NULL, 0, 0, '2025-10-15 18:49:49'),
(7, 7, 'Julito sleppy21', '986079838', 'spiritboom672@gmail.com', '12312312', '', '231231 (Ref: 23), Campoverde, Coronel Portillo, Ucayali', 'Ucayali', 'Coronel Portillo', 'Campoverde', '23', 'yape', 1, 1, '2025-10-20 04:11:27'),
(8, 1, 'julio', '999999999', 'admin@sleppystore.com', '55555555', '', '564htyuytyt', 'Amazonas', 'Bagua', 'Bagua', '', NULL, 1, 0, '2025-10-20 17:46:02'),
(9, 1, 'julio', '999999999', 'admin@sleppystore.com', '77777777', '', '54645635643', 'Lima', 'Barranca', 'Barranca', '', NULL, 1, 0, '2025-10-20 17:56:24'),
(10, 1, 'julio', '999999999', 'admin@sleppystore.com', '56565655', '', '564htyuytyt', 'Áncash', 'Pallasca', 'Pampas', '', NULL, 1, 0, '2025-10-20 17:58:22'),
(11, 1, 'ryutrurye', '675764575', 'admin@sleppystore.com', '88888888', '', '56465464565', 'Lima', 'Oyón', 'Oyón', '', NULL, 1, 0, '2025-10-20 18:03:30'),
(12, 1, 'eerwsdfsdfds', '545354344', 'admin@sleppystore.com', '43243233', '', '23erewsfdsfds', 'Lambayeque', 'Chiclayo', 'Picsi', '', NULL, 1, 1, '2025-11-03 02:59:27');

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
(1126, 7, 11, '2025-11-01 17:28:27'),
(1127, 7, 12, '2025-11-01 17:28:28'),
(1129, 7, 3, '2025-11-01 17:53:25'),
(1135, 7, 10, '2025-11-02 17:05:25'),
(1275, 1, 5, '2025-11-07 14:27:46'),
(1280, 1, 9, '2025-11-08 04:09:21'),
(1281, 1, 11, '2025-11-08 04:09:22'),
(1282, 1, 2, '2025-11-08 04:17:53');

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
(1, 'MARCA-001', 'Nike', 'Marca deportiva líder mundiall', 'marca-1760245144-6762657.jpg', '/fashion-master/public/assets/img/brands/marca-1760245144-6762657.jpg', 1, 'activo', '2025-09-30 19:47:49', '2025-10-12 04:59:04'),
(2, 'MARCA-002', 'Adidasa', 'Ropa y calzado deportivo', 'marca-1760247991-5919282.jpg', '/fashion-master/public/assets/img/brands/marca-1760247991-5919282.jpg', 1, 'inactivo', '2025-09-30 19:47:49', '2025-10-12 05:57:45'),
(3, 'MARCA-003', 'Zara', 'Moda contemporánea', 'marca-1760248066-6569177.jpg', '/fashion-master/public/assets/img/brands/marca-1760248066-6569177.jpg', 1, 'inactivo', '2025-09-30 19:47:49', '2025-10-12 05:47:46'),
(4, 'MARCA-004', 'H&M!', 'Moda rápida y accesible', 'marca_1759703732_68e2f2b42dda3.png', '/fashion-master/public/assets/img/brands/marca_1759703732_68e2f2b42dda3.png', 1, 'activo', '2025-09-30 19:47:49', '2025-10-12 05:55:57'),
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
(24, 3, NULL, 'entrada', 1, 8, 9, 'Actualización automática de stock', NULL, '2025-10-11 00:32:30', NULL),
(25, 2, NULL, 'salida', -1, 20, 19, 'Actualización automática de stock', NULL, '2025-10-12 22:26:48', NULL),
(26, 2, NULL, 'entrada', 11, 19, 30, 'Actualización automática de stock', NULL, '2025-10-12 23:28:28', NULL),
(27, 3, NULL, 'entrada', 21, 9, 30, 'Actualización automática de stock', NULL, '2025-10-13 04:55:00', NULL),
(28, 2, NULL, 'salida', -15, 30, 15, 'Actualización automática de stock', NULL, '2025-10-13 04:55:12', NULL),
(29, 1, NULL, 'entrada', 1, 0, 1, 'Actualización automática de stock', NULL, '2025-10-13 05:02:11', NULL),
(30, 3, NULL, 'salida', -16, 30, 14, 'Actualización automática de stock', NULL, '2025-10-13 05:02:18', NULL),
(31, 3, NULL, 'salida', -4, 14, 10, 'Actualización automática de stock', NULL, '2025-10-13 05:04:02', NULL),
(32, 3, NULL, 'entrada', 4, 10, 14, 'Actualización automática de stock', NULL, '2025-10-13 05:04:15', NULL),
(33, 1, NULL, 'entrada', 1, 1, 2, 'Actualización automática de stock', NULL, '2025-10-13 05:09:24', NULL),
(34, 1, NULL, 'entrada', 1, 2, 3, 'Actualización automática de stock', NULL, '2025-10-13 05:13:46', NULL),
(35, 1, NULL, 'salida', -1, 3, 2, 'Actualización automática de stock', NULL, '2025-10-13 15:51:07', NULL),
(36, 1, NULL, 'entrada', 8, 2, 10, 'Actualización automática de stock', NULL, '2025-10-13 15:55:53', NULL),
(37, 6, NULL, 'salida', -14, 18, 4, 'Actualización automática de stock', NULL, '2025-10-13 16:02:11', NULL),
(38, 1, NULL, 'entrada', 1, 10, 11, 'Actualización automática de stock', NULL, '2025-10-13 16:05:37', NULL),
(39, 1, NULL, 'salida', -1, 11, 10, 'Actualización automática de stock', NULL, '2025-10-13 16:10:03', NULL),
(40, 1, NULL, 'entrada', 1, 10, 11, 'Actualización automática de stock', NULL, '2025-10-13 16:11:57', NULL),
(41, 3, NULL, 'salida', -3, 14, 11, 'Actualización automática de stock', NULL, '2025-10-13 16:12:14', NULL),
(42, 1, NULL, 'salida', -1, 11, 10, 'Actualización automática de stock', NULL, '2025-10-13 16:13:13', NULL),
(43, 1, NULL, 'salida', -9, 10, 1, 'Actualización automática de stock', NULL, '2025-10-13 16:18:51', NULL),
(44, 1, NULL, 'salida', -1, 1, 0, 'Actualización automática de stock', NULL, '2025-10-13 16:19:29', NULL),
(45, 2, NULL, 'salida', -4, 15, 11, 'Actualización automática de stock', NULL, '2025-10-13 16:19:47', NULL),
(46, 2, NULL, 'entrada', 1, 11, 12, 'Actualización automática de stock', NULL, '2025-10-13 16:22:24', NULL),
(47, 3, NULL, 'entrada', 2, 11, 13, 'Actualización automática de stock', NULL, '2025-10-13 16:25:17', NULL),
(48, 5, NULL, 'entrada', 2, 43, 45, 'Actualización automática de stock', NULL, '2025-10-13 16:28:12', NULL),
(49, 5, NULL, 'entrada', 12, 45, 57, 'Actualización automática de stock', NULL, '2025-10-13 16:55:49', NULL),
(50, 2, NULL, 'entrada', 2, 12, 14, 'Actualización automática de stock', NULL, '2025-10-13 17:37:28', NULL),
(51, 3, NULL, 'salida', -1, 13, 12, 'Actualización automática de stock', NULL, '2025-10-13 20:11:31', NULL),
(52, 10, NULL, 'entrada', 6, 1, 7, 'Actualización automática de stock', NULL, '2025-10-13 22:06:58', NULL),
(58, 3, NULL, 'salida', -2, 12, 10, 'Actualización automática de stock', NULL, '2025-10-13 22:51:49', NULL),
(59, 3, NULL, 'entrada', 1, 10, 11, 'Actualización automática de stock', NULL, '2025-10-13 22:52:01', NULL),
(60, 3, NULL, 'entrada', 1, 11, 12, 'Actualización automática de stock', NULL, '2025-10-13 22:52:17', NULL),
(62, 2, NULL, 'salida', -1, 14, 13, 'Actualización automática de stock', NULL, '2025-10-14 01:11:32', NULL),
(64, 3, NULL, 'entrada', 1, 12, 13, 'Actualización automática de stock', NULL, '2025-10-14 01:18:30', NULL),
(67, 1, NULL, 'entrada', 1, 0, 1, 'Actualización automática de stock', NULL, '2025-10-14 20:07:28', NULL),
(68, 1, NULL, 'entrada', 1, 1, 2, 'Actualización automática de stock', NULL, '2025-10-14 20:07:50', NULL),
(69, 1, NULL, 'entrada', 1, 2, 3, 'Actualización automática de stock', NULL, '2025-10-14 20:11:12', NULL),
(70, 9, NULL, 'salida', -1, 67, 66, 'Actualización automática de stock', NULL, '2025-10-15 04:29:46', NULL),
(71, 8, NULL, 'salida', -1, 6, 5, 'Actualización automática de stock', NULL, '2025-10-15 04:29:46', NULL),
(72, 11, NULL, 'salida', -1, 110, 109, 'Actualización automática de stock', NULL, '2025-10-15 04:29:46', NULL),
(73, 6, NULL, 'salida', -4, 4, 0, 'Actualización automática de stock', NULL, '2025-10-15 04:29:46', NULL),
(74, 9, NULL, 'salida', -1, 66, 65, 'Actualización automática de stock', NULL, '2025-10-15 04:36:13', NULL),
(75, 8, NULL, 'salida', -3, 5, 2, 'Actualización automática de stock', NULL, '2025-10-15 16:33:46', NULL),
(76, 9, NULL, 'salida', -1, 65, 64, 'Actualización automática de stock', NULL, '2025-10-15 16:33:46', NULL),
(77, 1, NULL, 'entrada', 1, 3, 4, 'Actualización automática de stock', NULL, '2025-10-19 20:56:20', NULL),
(78, 10, NULL, 'salida', -1, 7, 6, 'Actualización automática de stock', NULL, '2025-10-20 03:58:28', NULL),
(79, 11, NULL, 'salida', -1, 109, 108, 'Actualización automática de stock', NULL, '2025-10-20 03:58:28', NULL),
(80, 9, NULL, 'salida', -1, 64, 63, 'Actualización automática de stock', NULL, '2025-10-20 03:58:28', NULL),
(81, 8, NULL, 'salida', -1, 2, 1, 'Actualización automática de stock', NULL, '2025-10-20 03:58:28', NULL),
(82, 3, NULL, 'salida', -1, 13, 12, 'Actualización automática de stock', NULL, '2025-10-20 03:58:28', NULL),
(83, 1, NULL, 'salida', -1, 4, 3, 'Actualización automática de stock', NULL, '2025-10-20 03:58:28', NULL),
(84, 4, NULL, 'salida', -1, 8, 7, 'Actualización automática de stock', NULL, '2025-10-20 03:58:28', NULL),
(85, 5, NULL, 'salida', -1, 57, 56, 'Actualización automática de stock', NULL, '2025-10-20 03:58:28', NULL),
(86, 8, NULL, 'salida', -1, 1, 0, 'Actualización automática de stock', NULL, '2025-10-20 04:11:27', NULL),
(87, 5, NULL, 'salida', -1, 56, 55, 'Actualización automática de stock', NULL, '2025-10-20 04:11:27', NULL),
(88, 9, NULL, 'salida', -1, 63, 62, 'Actualización automática de stock', NULL, '2025-10-20 04:11:27', NULL),
(89, 11, NULL, 'salida', -1, 108, 107, 'Actualización automática de stock', NULL, '2025-10-20 04:19:48', NULL),
(90, 9, NULL, 'salida', -1, 62, 61, 'Actualización automática de stock', NULL, '2025-10-20 04:45:01', NULL),
(91, 1, NULL, 'entrada', 5, 3, 8, 'Actualización automática de stock', NULL, '2025-10-20 13:25:24', NULL),
(92, 6, NULL, 'entrada', 100, 0, 100, 'Actualización automática de stock', NULL, '2025-10-20 17:12:34', NULL),
(93, 4, NULL, 'entrada', 1, 7, 8, 'Actualización automática de stock', NULL, '2025-10-20 18:15:50', NULL),
(94, 4, NULL, 'entrada', 1, 8, 9, 'Actualización automática de stock', NULL, '2025-10-21 00:05:17', NULL),
(95, 2, NULL, 'salida', -5, 13, 8, 'Actualización automática de stock', NULL, '2025-10-21 00:38:36', NULL),
(96, 11, NULL, 'salida', -28, 107, 79, 'Actualización automática de stock', NULL, '2025-10-21 00:38:36', NULL),
(97, 2, NULL, 'salida', -2, 8, 6, 'Actualización automática de stock', NULL, '2025-11-01 16:11:55', NULL),
(98, 11, NULL, 'salida', -2, 79, 77, 'Actualización automática de stock', NULL, '2025-11-01 16:11:55', NULL),
(99, 12, NULL, 'salida', -1, 10, 9, 'Actualización automática de stock', NULL, '2025-11-01 16:11:55', NULL),
(100, 1, NULL, 'entrada', 2, 8, 10, 'Actualización automática de stock', NULL, '2025-11-01 18:04:16', NULL),
(101, 12, NULL, 'salida', -1, 9, 8, 'Actualización automática de stock', NULL, '2025-11-03 18:53:03', NULL),
(102, 11, NULL, 'salida', -1, 77, 76, 'Actualización automática de stock', NULL, '2025-11-03 18:53:03', NULL),
(103, 9, NULL, 'salida', -1, 61, 60, 'Actualización automática de stock', NULL, '2025-11-03 18:53:03', NULL),
(104, 10, NULL, 'salida', -1, 6, 5, 'Actualización automática de stock', NULL, '2025-11-03 18:53:03', NULL),
(105, 4, NULL, 'salida', -1, 9, 8, 'Actualización automática de stock', NULL, '2025-11-07 00:43:51', NULL),
(106, 6, NULL, 'salida', -12, 100, 88, 'Actualización automática de stock', NULL, '2025-11-07 00:43:51', NULL),
(107, 3, NULL, 'salida', -1, 12, 11, 'Actualización automática de stock', NULL, '2025-11-07 01:57:11', NULL),
(108, 5, NULL, 'salida', -5, 55, 50, 'Actualización automática de stock', NULL, '2025-11-07 01:57:11', NULL),
(109, 12, NULL, 'salida', -1, 8, 7, 'Actualización automática de stock', NULL, '2025-11-07 01:57:11', NULL),
(110, 11, NULL, 'salida', -1, 76, 75, 'Actualización automática de stock', NULL, '2025-11-07 01:57:11', NULL),
(111, 2, NULL, 'entrada', 44, 6, 50, 'Actualización automática de stock', NULL, '2025-11-07 15:02:24', NULL),
(112, 2, NULL, 'entrada', 9, 50, 59, 'Actualización automática de stock', NULL, '2025-11-07 15:03:38', NULL),
(113, 2, NULL, 'salida', -8, 59, 51, 'Actualización automática de stock', NULL, '2025-11-07 15:05:17', NULL),
(114, 8, NULL, 'entrada', 10, 0, 10, 'Actualización automática de stock', NULL, '2025-11-08 03:05:01', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificacion`
--

CREATE TABLE `notificacion` (
  `id_notificacion` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `titulo_notificacion` varchar(150) DEFAULT NULL,
  `mensaje_notificacion` text NOT NULL,
  `tipo_notificacion` enum('info','alerta','advertencia','sistema') NOT NULL DEFAULT 'info',
  `url_destino_notificacion` varchar(255) DEFAULT NULL,
  `leida_notificacion` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_creacion_notificacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_lectura_notificacion` timestamp NULL DEFAULT NULL,
  `prioridad_notificacion` enum('baja','media','alta') NOT NULL DEFAULT 'media',
  `estado_notificacion` enum('activo','archivado','eliminado') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Sistema de notificaciones para usuarios';

--
-- Volcado de datos para la tabla `notificacion`
--

INSERT INTO `notificacion` (`id_notificacion`, `id_usuario`, `titulo_notificacion`, `mensaje_notificacion`, `tipo_notificacion`, `url_destino_notificacion`, `leida_notificacion`, `fecha_creacion_notificacion`, `fecha_lectura_notificacion`, `prioridad_notificacion`, `estado_notificacion`) VALUES
(1, 1, 'Bienvenido a SleppyStore', 'Gracias por registrarte en nuestra tienda. Explora nuestros productos y ofertas especiales.', 'info', NULL, 0, '2025-10-13 20:58:02', NULL, 'media', 'eliminado'),
(2, 1, 'Nueva oferta disponible', 'Tenemos un 20% de descuento en toda la categorÝa de camisas. No te lo pierdas!', 'alerta', NULL, 0, '2025-10-13 20:58:02', NULL, 'alta', 'eliminado'),
(3, 1, 'Tu pedido estß en camino', 'Tu pedido #12345 ha sido enviado y llegarß en 2-3 dÝas hßbiles.', 'info', NULL, 0, '2025-10-13 20:58:02', NULL, 'media', 'eliminado'),
(4, 2, 'íBienvenido a SleppyStore! ??', 'Hola Juan! Gracias por ser parte de nuestra tienda. Explora nuestros productos, ofertas especiales y disfruta de una experiencia de compra ·nica. íEstamos aquÝ para ayudarte!', 'info', NULL, 0, '2025-10-13 21:12:33', NULL, 'media', 'activo'),
(5, 3, 'íBienvenido a SleppyStore! ??', 'Hola María! Gracias por ser parte de nuestra tienda. Explora nuestros productos, ofertas especiales y disfruta de una experiencia de compra ·nica. íEstamos aquÝ para ayudarte!', 'info', NULL, 0, '2025-10-13 21:12:33', NULL, 'media', 'activo'),
(6, 4, 'íBienvenido a SleppyStore! ??', 'Hola Carlos! Gracias por ser parte de nuestra tienda. Explora nuestros productos, ofertas especiales y disfruta de una experiencia de compra ·nica. íEstamos aquÝ para ayudarte!', 'info', NULL, 0, '2025-10-13 21:12:33', NULL, 'media', 'activo'),
(7, 5, 'íBienvenido a SleppyStore! ??', 'Hola Ana! Gracias por ser parte de nuestra tienda. Explora nuestros productos, ofertas especiales y disfruta de una experiencia de compra ·nica. íEstamos aquÝ para ayudarte!', 'info', NULL, 0, '2025-10-13 21:12:33', NULL, 'media', 'activo'),
(8, 6, 'íBienvenido a SleppyStore! ??', 'Hola Sofía! Gracias por ser parte de nuestra tienda. Explora nuestros productos, ofertas especiales y disfruta de una experiencia de compra ·nica. íEstamos aquÝ para ayudarte!', 'info', NULL, 0, '2025-10-13 21:12:33', NULL, 'media', 'activo'),
(9, 7, 'íBienvenido a SleppyStore! ??', 'Hola Julito! Gracias por ser parte de nuestra tienda. Explora nuestros productos, ofertas especiales y disfruta de una experiencia de compra ·nica. íEstamos aquÝ para ayudarte!', 'info', NULL, 0, '2025-10-13 21:12:33', NULL, 'media', 'eliminado'),
(11, 1, '­ƒÄë Nueva promoci├│n disponible', 'Descubre nuestras ofertas especiales del mes. ┬íHasta 50% de descuento en productos seleccionados!', 'info', NULL, 1, '2025-10-13 21:35:34', '2025-10-13 21:55:36', 'media', 'eliminado'),
(12, 1, 'ÔÜá´©Å Stock bajo en favoritos', 'Algunos productos de tu lista de favoritos tienen stock limitado. ┬íAprovecha antes de que se agoten!', 'alerta', NULL, 0, '2025-10-13 19:35:34', NULL, 'alta', 'eliminado'),
(13, 1, 'ÔÜí Actualizaci├│n del sistema', 'Hemos mejorado la velocidad de carga y a├▒adido nuevas funciones. Explora las novedades.', 'sistema', NULL, 0, '2025-10-13 16:35:34', NULL, 'baja', 'eliminado'),
(14, 1, '­ƒôª Tu pedido est├í en camino', 'El pedido #12345 ha sido enviado y llegar├í en 2-3 d├¡as h├íbiles. Puedes rastrear tu env├¡o en tiempo real.', 'info', NULL, 0, '2025-10-12 21:35:34', NULL, 'media', 'eliminado'),
(15, 1, '­ƒÆ│ M├®todo de pago por vencer', 'Tu tarjeta registrada vence pronto. Actualiza tu m├®todo de pago para evitar interrupciones.', 'advertencia', NULL, 0, '2025-10-10 21:35:34', NULL, 'alta', 'eliminado'),
(16, 1, '­ƒÄü Puntos de recompensa disponibles', 'Has acumulado 500 puntos. Canj├®alos por descuentos en tu pr├│xima compra.', 'info', NULL, 1, '2025-10-08 21:35:34', '2025-10-09 21:35:34', 'baja', 'eliminado'),
(17, 1, '­ƒöÉ Inicio de sesi├│n detectado', 'Se detect├│ un inicio de sesi├│n desde un nuevo dispositivo. Si no fuiste t├║, cambia tu contrase├▒a inmediatamente.', 'alerta', NULL, 0, '2025-10-13 21:25:34', NULL, 'alta', 'eliminado'),
(18, 1, '­ƒøá´©Å Mantenimiento programado', 'El sistema estar├í en mantenimiento el pr├│ximo s├íbado de 2:00 AM a 4:00 AM. Disculpa las molestias.', 'sistema', NULL, 0, '2025-10-11 21:35:34', NULL, 'media', 'eliminado'),
(19, 1, '­ƒôº Verifica tu correo electr├│nico', 'Hemos enviado un enlace de verificaci├│n a tu correo. Por favor conf├¡rmalo para activar todas las funciones.', 'advertencia', NULL, 0, '2025-10-13 15:35:34', NULL, 'media', 'eliminado'),
(20, 1, '­ƒîƒ Nuevo producto destacado', '┬íAcaba de llegar! El producto m├ís esperado del a├▒o ya est├í disponible. S├® de los primeros en tenerlo.', 'info', NULL, 0, '2025-10-13 21:05:34', NULL, 'alta', 'eliminado'),
(21, 1, '👋 Buenos días, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-10-13 22:12:48', NULL, 'baja', 'eliminado'),
(22, 1, '👋 Buenos días, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-10-14 00:59:07', NULL, 'baja', 'eliminado'),
(23, 1, '👋 Buenas tardes, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-10-14 14:17:32', NULL, 'baja', 'eliminado'),
(24, 1, '👋 Buenas tardes, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 1, '2025-10-14 15:26:49', '2025-10-14 19:17:28', 'baja', 'eliminado'),
(25, 1, '👋 Buenas tardes, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-10-14 15:27:12', NULL, 'baja', 'eliminado'),
(26, 1, '👋 Buenas tardes, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 1, '2025-10-14 15:29:18', '2025-10-14 19:07:06', 'baja', 'eliminado'),
(27, 1, '👋 Buenas tardes, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 1, '2025-10-14 15:30:25', '2025-10-14 19:02:38', 'baja', 'eliminado'),
(28, 1, '👋 Buenas tardes, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 1, '2025-10-14 15:31:09', '2025-10-14 19:02:37', 'baja', 'eliminado'),
(29, 1, '👋 Buenas tardes, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 1, '2025-10-14 15:34:09', '2025-10-14 19:02:35', 'baja', 'eliminado'),
(30, 7, '👋 Buenas tardes, Julito!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 1, '2025-10-14 15:35:19', '2025-10-14 15:48:14', 'baja', 'eliminado'),
(31, 1, '👋 Buenas tardes, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 1, '2025-10-14 17:22:54', '2025-10-14 18:31:08', 'baja', 'eliminado'),
(32, 7, '👋 Buenas tardes, Julito!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-10-14 17:29:16', NULL, 'baja', 'eliminado'),
(33, 1, '👋 Buenas tardes, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 1, '2025-10-14 17:46:13', '2025-10-14 18:30:23', 'baja', 'eliminado'),
(34, 1, '👋 Buenas noches, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-10-14 20:07:22', NULL, 'baja', 'eliminado'),
(35, 1, '👋 Buenos días, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-10-15 04:26:59', NULL, 'baja', 'eliminado'),
(36, 1, '??? Pedido #1 confirmado', '??Gracias por tu compra, Administrador! Tu pedido por $789.53 ha sido confirmado y est?? siendo procesado. Te notificaremos cuando sea enviado.', 'info', 'order-confirmation.php?id=1', 0, '2025-10-15 04:29:46', NULL, 'alta', 'eliminado'),
(37, 7, '?????? ????ltimas unidades! - producto 1', 'El producto \"producto 1\" que tienes en favoritos solo tiene 5 unidades disponibles. ??Aprovecha antes de que se agote!', 'advertencia', 'product-details.php?id=8', 0, '2025-10-15 04:29:46', NULL, 'alta', 'eliminado'),
(38, 1, '??? Pedido #2 confirmado', '??Gracias por tu compra, Administrador! Tu pedido por $15.99 ha sido confirmado y est?? siendo procesado. Te notificaremos cuando sea enviado.', 'info', 'order-confirmation.php?id=2', 0, '2025-10-15 04:36:13', NULL, 'alta', 'eliminado'),
(39, 7, '👋 Buenos días, Julito!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-10-15 04:51:05', NULL, 'baja', 'eliminado'),
(40, 1, '👋 Buenos días, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-10-15 04:51:18', NULL, 'baja', 'eliminado'),
(41, 7, '👋 Buenos días, Julito!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-10-15 06:59:05', NULL, 'baja', 'eliminado'),
(42, 1, '👋 Buenos días, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-10-15 07:05:27', NULL, 'baja', 'eliminado'),
(43, 7, '👋 Buenas tardes, Julito!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-10-15 15:28:41', NULL, 'baja', 'eliminado'),
(44, 7, '👋 Buenas tardes, Julito!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-10-15 15:34:27', NULL, 'baja', 'eliminado'),
(45, 7, '??? Pedido #3 confirmado', '??Gracias por tu compra, Julito! Tu pedido por $15.99 ha sido confirmado y est?? siendo procesado. Te notificaremos cuando sea enviado.', 'info', 'order-confirmation.php?id=3', 0, '2025-10-15 16:33:46', NULL, 'alta', 'eliminado'),
(46, 7, '👋 Buenas noches, Julito!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-10-15 20:17:04', NULL, 'baja', 'eliminado'),
(47, 1, '👋 Buenas noches, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-10-19 20:48:58', NULL, 'baja', 'eliminado'),
(48, 1, '👋 Buenas noches, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-10-19 20:52:51', NULL, 'baja', 'eliminado'),
(49, 1, '👋 Buenas noches, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-10-19 20:55:12', NULL, 'baja', 'eliminado'),
(50, 1, '👋 Buenas noches, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-10-19 21:18:40', NULL, 'baja', 'eliminado'),
(51, 7, '👋 Buenos días, Julito!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-10-20 03:24:49', NULL, 'baja', 'eliminado'),
(52, 7, '??? Pedido #4 confirmado', '??Gracias por tu compra, Julito! Tu pedido por $1217.70 ha sido confirmado y est?? siendo procesado. Te notificaremos cuando sea enviado.', 'info', 'order-confirmation.php?id=4', 0, '2025-10-20 03:58:28', NULL, 'alta', 'eliminado'),
(53, 7, '??? Pedido #5 confirmado', '??Gracias por tu compra, Julito! Tu pedido por $50.94 ha sido confirmado y est?? siendo procesado. Te notificaremos cuando sea enviado.', 'info', 'order-confirmation.php?id=5', 0, '2025-10-20 04:11:27', NULL, 'alta', 'eliminado'),
(54, 7, '??? Pedido #6 confirmado', '??Gracias por tu compra, Julito! Tu pedido por $43.92 ha sido confirmado y est?? siendo procesado. Te notificaremos cuando sea enviado.', 'info', 'order-confirmation.php?id=6', 0, '2025-10-20 04:19:48', NULL, 'alta', 'eliminado'),
(55, 7, '??? Pedido #7 confirmado', '??Gracias por tu compra, Julito! Tu pedido por $15.99 ha sido confirmado y est?? siendo procesado. Te notificaremos cuando sea enviado.', 'info', 'order-confirmation.php?id=7', 0, '2025-10-20 04:45:01', NULL, 'alta', 'eliminado'),
(56, 1, '👋 Buenos días, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-10-20 05:07:12', NULL, 'baja', 'eliminado'),
(57, 7, '👋 Buenos días, Julito!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-10-20 05:13:40', NULL, 'baja', 'eliminado'),
(58, 1, '👋 Buenas tardes, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-10-20 13:20:36', NULL, 'baja', 'eliminado'),
(59, 1, '??? Pedido #8 confirmado', '??Gracias por tu compra, Administrador! Tu pedido por $1259.79 ha sido confirmado y est?? siendo procesado. Te notificaremos cuando sea enviado.', 'info', 'order-confirmation.php?id=8', 0, '2025-10-21 00:38:36', NULL, 'alta', 'eliminado'),
(60, 1, '👋 Buenos días, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-10-21 03:28:56', NULL, 'baja', 'eliminado'),
(61, 1, '👋 Buenos días, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-10-21 03:32:17', NULL, 'baja', 'eliminado'),
(62, 1, '👋 Buenos días, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-10-21 03:53:09', NULL, 'baja', 'eliminado'),
(63, 7, '👋 Buenas tardes, Julito!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-10-21 13:53:33', NULL, 'baja', 'eliminado'),
(64, 7, '👋 Buenas tardes, Julito!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-10-21 14:01:04', NULL, 'baja', 'eliminado'),
(65, 1, '👋 Buenas tardes, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-10-21 14:54:33', NULL, 'baja', 'eliminado'),
(66, 1, '??? Pedido #9 confirmado', '??Gracias por tu compra, Administrador! Tu pedido por $249.84 ha sido confirmado y est?? siendo procesado. Te notificaremos cuando sea enviado.', 'info', 'order-confirmation.php?id=9', 0, '2025-11-01 16:11:55', NULL, 'alta', 'eliminado'),
(67, 1, '👋 Buenas tardes, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-11-01 16:19:07', NULL, 'baja', 'eliminado'),
(68, 7, '👋 Buenas tardes, Julito!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-11-01 17:15:49', NULL, 'baja', 'eliminado'),
(69, 7, '👋 Buenas tardes, Julito!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-11-01 17:24:27', NULL, 'baja', 'eliminado'),
(70, 7, '👋 Buenas tardes, Julito!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-11-01 17:26:59', NULL, 'baja', 'eliminado'),
(71, 7, '👋 Buenas tardes, Julito!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-11-01 17:29:27', NULL, 'baja', 'activo'),
(72, 1, '👋 Buenas tardes, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-11-01 17:53:39', NULL, 'baja', 'eliminado'),
(73, 7, '👋 Buenas tardes, Julito!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-11-01 18:31:50', NULL, 'baja', 'activo'),
(74, 1, '👋 Buenas tardes, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-11-01 18:38:00', NULL, 'baja', 'eliminado'),
(75, 7, '👋 Buenas tardes, Julito!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-11-01 18:41:21', NULL, 'baja', 'activo'),
(76, 1, '👋 Buenas noches, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-11-01 20:01:45', NULL, 'baja', 'eliminado'),
(77, 1, '👋 Buenas noches, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-11-01 20:03:05', NULL, 'baja', 'eliminado'),
(78, 1, '👋 Buenas noches, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-11-01 20:25:15', NULL, 'baja', 'eliminado'),
(79, 1, '👋 Buenas noches, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-11-01 21:17:04', NULL, 'baja', 'eliminado'),
(80, 9, '??Bienvenido a SleppyStore! ????', 'Hola Alexsa! Gracias por registrarte en nuestra tienda. Explora nuestros productos, ofertas especiales y disfruta de una experiencia de compra ??nica. ??Estamos aqu?? para ayudarte!', 'info', '/fashion-master/shop.php', 0, '2025-11-01 21:18:24', NULL, 'media', 'activo'),
(81, 9, '🎉 ¡Bienvenido a SleppyStore!', '¡Hola Alexsa! Gracias por unirte a nuestra familia. Explora nuestros productos y aprovecha las ofertas especiales para nuevos usuarios.', 'info', 'shop.php', 0, '2025-11-01 21:18:24', NULL, 'media', 'activo'),
(82, 9, '📧 Verifica tu correo electrónico', 'Por favor verifica tu correo electrónico (alexsandraroldan16@gmail.com) para activar todas las funciones de tu cuenta.', 'advertencia', NULL, 0, '2025-11-01 21:18:24', NULL, 'media', 'activo'),
(83, 9, '👋 Buenas noches, Alexsa!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-11-01 21:18:42', NULL, 'baja', 'activo'),
(84, 7, '👋 Buenas tardes, Julito!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-11-02 16:50:41', NULL, 'baja', 'activo'),
(85, 7, '👋 Buenas tardes, Julito!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-11-02 16:54:26', NULL, 'baja', 'activo'),
(86, 1, '👋 Buenas tardes, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-11-02 17:17:53', NULL, 'baja', 'eliminado'),
(87, 1, '👋 Buenas noches, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-11-02 19:42:26', NULL, 'baja', 'eliminado'),
(88, 1, '👋 Buenas noches, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-11-02 21:02:44', NULL, 'baja', 'eliminado'),
(89, 1, '👋 Buenas noches, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-11-02 21:16:26', NULL, 'baja', 'eliminado'),
(90, 1, '👋 Buenos días, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-11-03 05:04:36', NULL, 'baja', 'eliminado'),
(91, 1, '👋 Buenas tardes, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-11-03 16:25:39', NULL, 'baja', 'eliminado'),
(92, 7, '👋 Buenas tardes, Julito!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-11-03 18:51:12', NULL, 'baja', 'activo'),
(93, 7, '??? Pedido #10 confirmado', '??Gracias por tu compra, Julito! Tu pedido por $721.91 ha sido confirmado y est?? siendo procesado. Te notificaremos cuando sea enviado.', 'info', 'order-confirmation.php?id=10', 0, '2025-11-03 18:53:03', NULL, 'alta', 'activo'),
(94, 7, '?????? ????ltimas unidades! - carrito a control remoto :c', 'El producto \"carrito a control remoto :c\" que tienes en favoritos solo tiene 5 unidades disponibles. ??Aprovecha antes de que se agote!', 'advertencia', 'product-details.php?id=10', 0, '2025-11-03 18:53:03', NULL, 'alta', 'activo'),
(95, 1, '👋 Buenas noches, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-11-03 20:34:54', NULL, 'baja', 'eliminado'),
(96, 1, '👋 Buenas noches, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-11-03 20:44:45', NULL, 'baja', 'eliminado'),
(97, 7, '👋 Buenas noches, Julito!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-11-03 21:10:27', NULL, 'baja', 'activo'),
(98, 1, '👋 Buenas noches, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-11-03 21:10:46', NULL, 'baja', 'eliminado'),
(99, 1, '👋 Buenas noches, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-11-03 22:19:33', NULL, 'baja', 'eliminado'),
(100, 7, '👋 Buenas noches, Julito!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-11-03 22:26:48', NULL, 'baja', 'eliminado'),
(101, 1, '👋 Buenas noches, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-11-03 22:33:49', NULL, 'baja', 'eliminado'),
(102, 1, '👋 Buenos días, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-11-04 00:32:59', NULL, 'baja', 'eliminado'),
(103, 1, '👋 Buenas noches, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-11-05 19:04:50', NULL, 'baja', 'eliminado'),
(104, 1, '👋 Buenas tardes, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-11-06 16:12:41', NULL, 'baja', 'eliminado'),
(105, 1, '👋 Buenas noches, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-11-06 20:54:03', NULL, 'baja', 'eliminado'),
(106, 1, '??? Pedido #11 confirmado', '??Gracias por tu compra, Administrador! Tu pedido por $2406.78 ha sido confirmado y est?? siendo procesado. Te notificaremos cuando sea enviado.', 'info', 'order-confirmation.php?id=11', 0, '2025-11-07 00:43:51', NULL, 'alta', 'eliminado'),
(107, 1, '??? Pedido #12 confirmado', '??Gracias por tu compra, Administrador! Tu pedido por $470.59 ha sido confirmado y est?? siendo procesado. Te notificaremos cuando sea enviado.', 'info', 'order-confirmation.php?id=12', 0, '2025-11-07 01:57:11', NULL, 'alta', 'eliminado'),
(108, 7, '👋 Buenos días, Julito!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-11-07 05:04:19', NULL, 'baja', 'activo'),
(109, 1, '👋 Buenos días, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-11-07 05:04:48', NULL, 'baja', 'eliminado'),
(110, 1, '👋 Buenos días, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-11-07 05:05:12', NULL, 'baja', 'eliminado'),
(111, 7, '👋 Buenos días, Julito!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-11-07 05:05:35', NULL, 'baja', 'activo'),
(112, 1, '👋 Buenos días, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-11-07 05:20:54', NULL, 'baja', 'activo'),
(113, 1, '👋 Buenos días, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-11-07 05:22:16', NULL, 'baja', 'eliminado'),
(114, 1, '👋 Buenas tardes, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-11-07 14:25:28', NULL, 'baja', 'eliminado'),
(115, 1, '👋 Buenas tardes, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-11-07 14:26:45', NULL, 'baja', 'eliminado'),
(116, 7, '👋 Buenos días, Julito!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-11-08 01:53:00', NULL, 'baja', 'activo'),
(117, 1, '👋 Buenos días, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-11-08 01:53:49', NULL, 'baja', 'activo'),
(118, 1, '👋 Buenos días, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-11-08 01:57:00', NULL, 'baja', 'activo'),
(119, 1, '👋 Buenos días, Administrador!', '¡Nos alegra verte de nuevo! Descubre las novedades y ofertas especiales que tenemos para ti.', 'info', 'shop.php', 0, '2025-11-08 01:58:20', NULL, 'baja', 'activo');

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
(7, 7, 'cf3a1b595cab69e29cc1c184e15e45f27ef1a61e265c6a1034c68f0dc88156f2', 'spiritboom672@gmail.com', '2025-10-14 15:34:56', '2025-10-14 16:34:56', 1, '::1');

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
  `dni_pedido` varchar(20) DEFAULT NULL COMMENT 'DNI del cliente (8 dígitos)',
  `ruc_pedido` varchar(11) DEFAULT NULL COMMENT 'RUC del cliente cuando el tipo de comprobante es factura (11 dígitos)',
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
-- Volcado de datos para la tabla `pedido`
--

INSERT INTO `pedido` (`id_pedido`, `id_usuario`, `nombre_cliente_pedido`, `email_cliente_pedido`, `telefono_cliente_pedido`, `dni_pedido`, `ruc_pedido`, `direccion_envio_pedido`, `departamento_pedido`, `provincia_pedido`, `distrito_pedido`, `tipo_comprobante_pedido`, `razon_social_pedido`, `metodo_pago_pedido`, `subtotal_pedido`, `costo_envio_pedido`, `total_pedido`, `notas_pedido`, `estado_pedido`, `numero_tracking`, `fecha_pedido`, `fecha_pago`, `fecha_envio`, `fecha_entrega`, `fecha_actualizacion`) VALUES
(1, 1, 'Administrador Principal', 'admin@sleppystore.com', '+519876543216', '66666666', NULL, '5676575576, Balsas, Chachapoyas, Amazonas', 'Amazonas', 'Chachapoyas', 'Balsas', 'boleta', NULL, 'yape', 789.53, 0.00, 789.53, NULL, 'pendiente', NULL, '2025-10-15 04:29:46', NULL, NULL, NULL, NULL),
(2, 1, 'Administrador Principal', 'admin@sleppystore.com', '+51987654321', '66666666', NULL, '6666666, Olleros, Chachapoyas, Amazonas', 'Amazonas', 'Chachapoyas', 'Olleros', 'boleta', NULL, 'tarjeta', 0.99, 15.00, 15.99, NULL, 'pendiente', NULL, '2025-10-15 04:36:13', NULL, NULL, NULL, NULL),
(3, 7, 'Julito sleppy21', 'spiritboom672@gmail.com', '986079838', '66666666', NULL, '77777777 (Ref: 77), Neshuya, Padre Abad, Ucayali', 'Ucayali', 'Padre Abad', 'Neshuya', 'boleta', NULL, 'yape', 0.99, 15.00, 15.99, NULL, 'pendiente', NULL, '2025-10-15 16:33:46', NULL, NULL, NULL, NULL),
(4, 7, 'sd123123', 'spiritboom672@gmail.com', '23232323', '87654321', NULL, '232323232 (Ref: 2323232), Miraflores, Callao, Arequipa', 'Arequipa', 'Callao', 'Miraflores', 'boleta', NULL, 'yape', 1217.70, 0.00, 1217.70, NULL, 'pendiente', NULL, '2025-10-20 03:58:28', NULL, NULL, NULL, NULL),
(5, 7, 'Julito sleppy21', 'spiritboom672@gmail.com', '986079838', '12312312', NULL, '231231 (Ref: 23), Campoverde, Coronel Portillo, Ucayali', 'Ucayali', 'Coronel Portillo', 'Campoverde', 'boleta', NULL, 'yape', 35.94, 15.00, 50.94, NULL, 'pendiente', NULL, '2025-10-20 04:11:27', NULL, NULL, NULL, NULL),
(6, 7, 'Julito sleppy21', 'spiritboom672@gmail.com', '986079838', '12312312', NULL, '231231 (Ref: 23), Campoverde, Coronel Portillo, Ucayali', 'Ucayali', 'Coronel Portillo', 'Campoverde', 'boleta', NULL, 'yape', 28.92, 15.00, 43.92, NULL, 'pendiente', NULL, '2025-10-20 04:19:48', NULL, NULL, NULL, NULL),
(7, 7, 'Julito sleppy21', 'spiritboom672@gmail.com', '986079838', '12312312', NULL, '231231 (Ref: 23), Campoverde, Coronel Portillo, Ucayali', 'Ucayali', 'Coronel Portillo', 'Campoverde', 'boleta', NULL, 'yape', 0.99, 15.00, 15.99, NULL, 'pendiente', NULL, '2025-10-20 04:45:01', NULL, NULL, NULL, NULL),
(8, 1, 'ryutrurye', 'admin@sleppystore.com', '675764575', '88888888', NULL, '56465464565, Oyón, Oyón, Lima', 'Lima', 'Oyón', 'Oyón', 'boleta', NULL, 'yape', 1259.79, 0.00, 1259.79, NULL, 'pendiente', NULL, '2025-10-21 00:38:36', NULL, NULL, NULL, NULL),
(9, 1, 'ryutrurye', 'admin@sleppystore.com', '675764575', '88888888', NULL, '56465464565, Oyón, Oyón, Lima', 'Lima', 'Oyón', 'Oyón', 'boleta', NULL, 'yape', 249.84, 0.00, 249.84, NULL, 'pendiente', NULL, '2025-11-01 16:11:55', NULL, NULL, NULL, NULL),
(10, 7, 'Julito sleppy21', 'spiritboom672@gmail.com', '986079838', '12312312', NULL, '231231 (Ref: 23), Campoverde, Coronel Portillo, Ucayali', 'Ucayali', 'Coronel Portillo', 'Campoverde', 'boleta', NULL, 'yape', 721.91, 0.00, 721.91, NULL, 'pendiente', NULL, '2025-11-03 18:53:03', NULL, NULL, NULL, NULL),
(11, 1, 'eerwsdfsdfds', 'admin@sleppystore.com', '545354344', '43243233', NULL, '23erewsfdsfds, Picsi, Chiclayo, Lambayeque', 'Lambayeque', 'Chiclayo', 'Picsi', 'boleta', NULL, '', 2406.78, 0.00, 2406.78, NULL, 'pendiente', NULL, '2025-11-07 00:43:51', NULL, NULL, NULL, NULL),
(12, 1, 'eerwsdfsdfds', 'admin@sleppystore.com', '545354344', '43243233', NULL, '23erewsfdsfds, Picsi, Chiclayo, Lambayeque', 'Lambayeque', 'Chiclayo', 'Picsi', 'boleta', NULL, 'yape', 470.59, 0.00, 470.59, NULL, 'pendiente', NULL, '2025-11-07 01:57:11', NULL, NULL, NULL, NULL);

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
DELIMITER $$
CREATE TRIGGER `trigger_estado_pedido` AFTER UPDATE ON `pedido` FOR EACH ROW BEGIN
    DECLARE titulo_msg VARCHAR(255);
    DECLARE mensaje_msg TEXT;
    DECLARE tipo_msg VARCHAR(50);
    
    
    IF OLD.estado_pedido != NEW.estado_pedido THEN
        
        
        CASE NEW.estado_pedido
            WHEN 'procesando' THEN
                SET titulo_msg = CONCAT('???? Pedido #', NEW.id_pedido, ' en proceso');
                SET mensaje_msg = 'Tu pedido est?? siendo preparado. Pronto lo enviaremos.';
                SET tipo_msg = 'info';
                
            WHEN 'enviado' THEN
                SET titulo_msg = CONCAT('???? Pedido #', NEW.id_pedido, ' enviado');
                SET mensaje_msg = '??Tu pedido ya est?? en camino! Recibir??s un email con el c??digo de seguimiento.';
                SET tipo_msg = 'info';
                
            WHEN 'entregado' THEN
                SET titulo_msg = CONCAT('???? Pedido #', NEW.id_pedido, ' entregado');
                SET mensaje_msg = '??Tu pedido ha sido entregado! Esperamos que disfrutes tus productos. ??Gracias por tu compra!';
                SET tipo_msg = 'info';
                
            WHEN 'cancelado' THEN
                SET titulo_msg = CONCAT('??? Pedido #', NEW.id_pedido, ' cancelado');
                SET mensaje_msg = 'Tu pedido ha sido cancelado. Si no realizaste esta acci??n, por favor contacta con soporte.';
                SET tipo_msg = 'alerta';
                
            ELSE
                SET titulo_msg = CONCAT('???? Actualizaci??n pedido #', NEW.id_pedido);
                SET mensaje_msg = CONCAT('El estado de tu pedido cambi?? a: ', NEW.estado_pedido);
                SET tipo_msg = 'info';
        END CASE;
        
        
        INSERT INTO notificacion (
            id_usuario,
            titulo_notificacion,
            mensaje_notificacion,
            tipo_notificacion,
            prioridad_notificacion,
            url_destino_notificacion
        ) VALUES (
            NEW.id_usuario,
            titulo_msg,
            mensaje_msg,
            tipo_msg,
            'alta',
            CONCAT('order-confirmation.php?id=', NEW.id_pedido)
        );
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trigger_pedido_confirmado` AFTER INSERT ON `pedido` FOR EACH ROW BEGIN
    
    SET @nombre_usuario = (SELECT nombre_usuario FROM usuario WHERE id_usuario = NEW.id_usuario);
    
    INSERT INTO notificacion (
        id_usuario,
        titulo_notificacion,
        mensaje_notificacion,
        tipo_notificacion,
        prioridad_notificacion,
        url_destino_notificacion
    ) VALUES (
        NEW.id_usuario,
        CONCAT('??? Pedido #', NEW.id_pedido, ' confirmado'),
        CONCAT('??Gracias por tu compra, ', @nombre_usuario, '! Tu pedido por $', ROUND(NEW.total_pedido, 2), ' ha sido confirmado y est?? siendo procesado. Te notificaremos cuando sea enviado.'),
        'info',
        'alta',
        CONCAT('order-confirmation.php?id=', NEW.id_pedido)
    );
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
  `id_subcategoria` int(11) DEFAULT NULL,
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

INSERT INTO `producto` (`id_producto`, `nombre_producto`, `codigo`, `descripcion_producto`, `id_categoria`, `id_subcategoria`, `id_marca`, `precio_producto`, `descuento_porcentaje_producto`, `genero_producto`, `en_oferta_producto`, `stock_actual_producto`, `stock_minimo_producto`, `stock_maximo_producto`, `imagen_producto`, `url_imagen_producto`, `status_producto`, `estado`, `fecha_creacion_producto`, `fecha_actualizacion_producto`) VALUES
(1, 'Camisa Casual Nike', 'CAM-HMX-FFF-001', 'Camisa deportiva de algodón\r\n\r\n\r\n\r\n', 17, NULL, 4, 90.00, 0.00, 'F', 0, 10, 20, 300, 'product_68e1d018c5d29.jpg', '/fashion-master/public/assets/img/products/product_68e1d018c5d29.jpg', 1, 'activo', '2025-09-30 19:47:49', '2025-11-08 03:04:51'),
(2, 'Pantalón Jean Levis 501', 'PAN-ADI-ACC', 'Jean clásico corte recto', 4, 16, 2, 50.00, 0.00, 'F', 0, 51, 20, 300, 'product_68e1d379ba460.png', '/fashion-master/public/assets/img/products/product_68e1d379ba460.png', 1, 'activo', '2025-09-30 19:47:49', '2025-11-08 03:04:57'),
(3, 'dos amigues :\'D', 'DOS-HMX-PIP', 'Zapatillas deportivas con cámara de aire', 17, NULL, 4, 299.90, 15.00, 'F', 1, 11, 20, 300, 'product_68e9a4892f2a9.jpg', '/fashion-master/public/assets/img/products/product_68e9a4892f2a9.jpg', 1, 'activo', '2025-09-30 19:47:49', '2025-11-07 01:57:11'),
(4, 'Vestido Casual Zara', 'VES-HMX-PIP', 'Vestido elegante para ocasiones especiales', 17, NULL, 4, 159.90, 20.00, 'F', 1, 8, 20, 300, 'product_68ed1f1e21a90.jpg', '/fashion-master/public/assets/img/products/product_68ed1f1e21a90.jpg', 1, 'activo', '2025-09-30 19:47:49', '2025-11-07 00:43:51'),
(5, 'Camisa Formal H&M', 'CAM-HMX-ACC-001', 'Camisa de vestir para oficina', 4, NULL, 4, 69.90, 50.00, 'F', 1, 50, 20, 300, 'product_68ee96914e8d1.jpg', '/fashion-master/public/assets/img/products/product_68ee96914e8d1.jpg', 1, 'activo', '2025-09-30 19:47:49', '2025-11-07 01:57:11'),
(6, 'Zapatillas Adidas Stan Smith', 'ZAP-ADI-ZAP-001', 'Zapatillas clásicas de tenis', 3, NULL, 2, 199.90, 5.00, 'Unisex', 1, 88, 20, 300, 'product_68ee969cc8117.jpg', '/fashion-master/public/assets/img/products/product_68ee969cc8117.jpg', 1, 'activo', '2025-09-30 19:47:49', '2025-11-07 00:43:51'),
(8, 'producto 1', 'PRO-HMX-ACC', 'nada', 4, NULL, 4, 2.00, 100.00, 'Kids', 1, 10, 20, 300, 'product_68e8578c49ec2.jpg', '/fashion-master/public/assets/img/products/product_68e8578c49ec2.jpg', 1, 'activo', '2025-10-03 04:46:28', '2025-11-08 03:05:01'),
(9, 'prenda ejemplo', '2323', 'prenda comun', 1, NULL, 4, 1.00, 1.00, 'Unisex', 0, 60, 20, 300, 'product_68e95d0cf1494.jpg', '/fashion-master/public/assets/img/products/product_68e95d0cf1494.jpg', 1, 'activo', '2025-10-05 21:25:31', '2025-11-03 18:53:03'),
(10, 'carrito a control remoto :c', 'CAR-ADI-HOL', 'un carrito bonito\r\n-pq si\r\n-me gusta\r\n-no es de geys\r\n-a fucking control remoto :0', 16, NULL, 2, 800.00, 15.00, 'Unisex', 0, 5, 20, 300, 'product_68ed2ee63865f.jpg', '/fashion-master/public/assets/img/products/product_68ed2ee63865f.jpg', 1, 'activo', '2025-10-13 16:55:02', '2025-11-03 18:53:03'),
(11, 'producto de prueba', 'PRO-LEV-HOL-001', 'un productiño de pruebiña', 16, NULL, 5, 34.43, 0.00, 'Unisex', 0, 75, 20, 300, 'product_68ed35cfee743.png', '/fashion-master/public/assets/img/products/product_68ed35cfee743.png', 1, 'activo', '2025-10-13 17:24:31', '2025-11-07 16:46:36'),
(12, 'Pruebita', 'PRU-HMX-PIP', 'De chill', 17, NULL, 4, 12.00, 0.00, 'Kids', 0, 7, 20, 300, 'product_68f79f2b3f70d.jpg', '/fashion-master/public/assets/img/products/product_68f79f2b3f70d.jpg', 1, 'activo', '2025-10-21 14:56:43', '2025-11-07 01:57:11');

--
-- Disparadores `producto`
--
DELIMITER $$
CREATE TRIGGER `trigger_descuento_favorito` AFTER UPDATE ON `producto` FOR EACH ROW BEGIN
    
    IF NEW.precio_producto < OLD.precio_producto * 0.95 THEN
        
        
        SET @descuento = ROUND(((OLD.precio_producto - NEW.precio_producto) / OLD.precio_producto) * 100, 0);
        
        INSERT INTO notificacion (
            id_usuario,
            titulo_notificacion,
            mensaje_notificacion,
            tipo_notificacion,
            prioridad_notificacion,
            url_destino_notificacion
        )
        SELECT 
            f.id_usuario,
            CONCAT('???? ??', @descuento, '% OFF! - ', NEW.nombre_producto),
            CONCAT('??Oferta especial! "', NEW.nombre_producto, '" baj?? de $', ROUND(OLD.precio_producto, 2), ' a solo $', ROUND(NEW.precio_producto, 2), '. ??Ahorra $', ROUND(OLD.precio_producto - NEW.precio_producto, 2), '!'),
            'info',
            'alta',
            CONCAT('product-details.php?id=', NEW.id_producto)
        FROM favorito f
        WHERE f.id_producto = NEW.id_producto
        AND NOT EXISTS (
            
            SELECT 1 FROM notificacion n
            WHERE n.id_usuario = f.id_usuario
            AND n.mensaje_notificacion LIKE CONCAT('%', NEW.nombre_producto, '%descuento%')
            AND n.fecha_creacion_notificacion > DATE_SUB(NOW(), INTERVAL 72 HOUR)
        );
    END IF;
END
$$
DELIMITER ;
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
DELIMITER $$
CREATE TRIGGER `trigger_stock_bajo` AFTER UPDATE ON `producto` FOR EACH ROW BEGIN
    
    IF OLD.stock_actual_producto > 5 AND NEW.stock_actual_producto <= 5 AND NEW.stock_actual_producto > 0 THEN
        
        INSERT INTO notificacion (
            id_usuario,
            titulo_notificacion,
            mensaje_notificacion,
            tipo_notificacion,
            prioridad_notificacion,
            url_destino_notificacion
        )
        SELECT 
            f.id_usuario,
            CONCAT('?????? ????ltimas unidades! - ', NEW.nombre_producto),
            CONCAT('El producto "', NEW.nombre_producto, '" que tienes en favoritos solo tiene ', NEW.stock_actual_producto, ' unidades disponibles. ??Aprovecha antes de que se agote!'),
            'advertencia',
            'alta',
            CONCAT('product-details.php?id=', NEW.id_producto)
        FROM favorito f
        WHERE f.id_producto = NEW.id_producto
        AND NOT EXISTS (
            
            SELECT 1 FROM notificacion n
            WHERE n.id_usuario = f.id_usuario
            AND n.mensaje_notificacion LIKE CONCAT('%', NEW.nombre_producto, '%stock%')
            AND n.fecha_creacion_notificacion > DATE_SUB(NOW(), INTERVAL 24 HOUR)
        );
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trigger_vuelta_stock` AFTER UPDATE ON `producto` FOR EACH ROW BEGIN
    
    IF OLD.stock_actual_producto = 0 AND NEW.stock_actual_producto > 0 THEN
        
        INSERT INTO notificacion (
            id_usuario,
            titulo_notificacion,
            mensaje_notificacion,
            tipo_notificacion,
            prioridad_notificacion,
            url_destino_notificacion
        )
        SELECT 
            f.id_usuario,
            CONCAT('??? ??Volvi?? el stock! - ', NEW.nombre_producto),
            CONCAT('Buenas noticias: "', NEW.nombre_producto, '" que estaba agotado ahora tiene ', NEW.stock_actual_producto, ' unidades disponibles. ??C??mpralo antes de que se agote de nuevo!'),
            'info',
            'alta',
            CONCAT('product-details.php?id=', NEW.id_producto)
        FROM favorito f
        WHERE f.id_producto = NEW.id_producto
        AND NOT EXISTS (
            
            SELECT 1 FROM notificacion n
            WHERE n.id_usuario = f.id_usuario
            AND n.mensaje_notificacion LIKE CONCAT('%', NEW.nombre_producto, '%stock%')
            AND n.fecha_creacion_notificacion > DATE_SUB(NOW(), INTERVAL 48 HOUR)
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
  `likes_count` int(11) DEFAULT 0,
  `reportes_count` int(11) DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `resena`
--

INSERT INTO `resena` (`id_resena`, `id_producto`, `id_usuario`, `id_orden`, `calificacion`, `titulo`, `comentario`, `verificada`, `aprobada`, `likes_count`, `reportes_count`, `fecha_creacion`) VALUES
(1, 1, 2, NULL, 5, 'Excelente calidad', 'La camisa es de muy buena calidad, el material es suave y cómodo. Muy recomendable.', 1, 1, 0, 0, '2025-10-10 15:39:29'),
(2, 1, 3, NULL, 4, 'Buena compra', 'Me gustó mucho, aunque el color es un poco diferente a la foto.', 1, 1, 0, 0, '2025-10-10 15:39:29'),
(3, 1, 6, NULL, 5, 'Perfecta!', 'Justo lo que buscaba, la talla es correcta y llego rapido.', 1, 1, 0, 0, '2025-10-10 15:39:29'),
(4, 2, 2, NULL, 5, 'Clásico que nunca falla', 'Los Levis 501 son siempre una buena inversión. Calidad garantizada.', 1, 1, 1, 0, '2025-10-10 15:39:29'),
(5, 2, 3, NULL, 4, 'Buen jean', 'Es un buen producto, aunque el precio es un poco elevado.', 1, 1, 0, 0, '2025-10-10 15:39:29'),
(6, 2, 4, NULL, 5, 'Me encanta', 'Super cómodo y se ve muy bien. Vale cada peso.', 1, 1, 0, 0, '2025-10-10 15:39:29'),
(7, 2, 6, NULL, 4, 'Recomendado', 'Buena calidad de tela, aunque tarda un poco en ablandarse.', 1, 1, 0, 0, '2025-10-10 15:39:29'),
(8, 3, 2, NULL, 5, 'Las mejores zapatillas', 'Súper cómodas para correr y para uso diario. Excelente inversión.', 1, 1, 0, 0, '2025-10-10 15:39:29'),
(9, 3, 3, NULL, 5, 'Increíbles!', 'Son hermosas y muy cómodas. La suela con aire se siente genial.', 1, 1, 0, 0, '2025-10-10 15:39:29'),
(10, 3, 4, NULL, 4, 'Muy buenas', 'Me gustan mucho, aunque son un poco grandes. Pedí media talla menos.', 1, 1, 0, 0, '2025-10-10 15:39:29'),
(11, 3, 5, NULL, 5, 'Perfectas', 'Llegaron rápido y en perfectas condiciones. 100% recomendadas.', 1, 1, 1, 1, '2025-10-10 15:39:29'),
(12, 3, 6, NULL, 5, 'Love them!', 'Son exactamente como en las fotos. Muy contenta con la compra.', 1, 1, 0, 0, '2025-10-10 15:39:29'),
(13, 4, 3, NULL, 4, 'Bonito vestido', 'Es muy lindo, la tela es ligera y fresca.', 1, 1, 0, 0, '2025-10-10 15:39:29'),
(14, 4, 6, NULL, 5, 'Hermoso!', 'Me encantó, es elegante y cómodo a la vez.', 1, 1, 0, 0, '2025-10-10 15:39:29'),
(15, 5, 2, NULL, 4, 'Buena para la oficina', 'Perfecta para el trabajo, se ve formal y es cómoda.', 1, 1, 1, 1, '2025-10-10 15:39:29'),
(16, 5, 4, NULL, 3, 'Normal', 'Es una camisa normal, nada especial pero cumple su función.', 1, 1, 1, 0, '2025-10-10 15:39:29'),
(17, 5, 5, NULL, 4, 'Recomendable', 'Buena relación calidad-precio. La uso frecuentemente.', 1, 1, 0, 0, '2025-10-10 15:39:29'),
(18, 6, 2, NULL, 5, 'Clásico atemporal', 'Las Stan Smith nunca pasan de moda. Excelente compra.', 1, 1, 0, 0, '2025-10-10 15:39:29'),
(19, 6, 3, NULL, 5, 'Perfectas', 'Son hermosas y combinan con todo. Muy cómodas.', 1, 1, 0, 0, '2025-10-10 15:39:29'),
(20, 6, 4, NULL, 4, 'Buenas zapatillas', 'Me gustan mucho, aunque al principio eran un poco duras.', 1, 1, 0, 0, '2025-10-10 15:39:29'),
(21, 8, 2, NULL, 3, 'Está bien', 'Es un producto básico, cumple lo que promete.', 1, 1, 0, 0, '2025-10-10 15:39:29'),
(22, 8, 5, NULL, 4, 'Bueno', 'Por el precio está bastante bien.', 1, 1, 0, 0, '2025-10-10 15:39:29'),
(23, 9, 3, NULL, 4, 'Satisfecho', 'Llegó en buenas condiciones, es como se describe.', 1, 1, 0, 0, '2025-10-10 15:39:29'),
(24, 9, 6, NULL, 5, 'Genial', 'Me encantó, superó mis expectativas.', 1, 1, 0, 0, '2025-10-10 15:39:29'),
(25, 2, 1, NULL, 3, '', 'hola\nadsadadsadas', 0, 0, 0, 0, '2025-11-07 02:38:38'),
(26, 3, 1, NULL, 5, '', 'Utgjjgjjgg', 0, 0, 0, 0, '2025-11-07 02:46:30'),
(28, 6, 1, NULL, 5, 'Yhgc', '2423231332456421 aaaaaa gey el que lo lea', 0, 1, 0, 0, '2025-11-07 03:08:48'),
(29, 11, 1, NULL, 1, 'Prueba', 'Comentario comentariamente comentado', 0, 1, 2, 0, '2025-11-07 05:10:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resena_likes`
--

CREATE TABLE `resena_likes` (
  `id_like` int(11) NOT NULL,
  `id_resena` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `resena_likes`
--

INSERT INTO `resena_likes` (`id_like`, `id_resena`, `id_usuario`, `fecha_creacion`) VALUES
(2, 16, 1, '2025-11-07 01:33:23'),
(3, 15, 1, '2025-11-07 01:34:16'),
(4, 11, 1, '2025-11-07 01:46:47'),
(6, 29, 7, '2025-11-07 05:10:22'),
(7, 29, 1, '2025-11-07 05:10:23'),
(8, 4, 1, '2025-11-07 17:15:52');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resena_reportes`
--

CREATE TABLE `resena_reportes` (
  `id_reporte` int(11) NOT NULL,
  `id_resena` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `razon` varchar(500) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `resena_reportes`
--

INSERT INTO `resena_reportes` (`id_reporte`, `id_resena`, `id_usuario`, `razon`, `fecha_creacion`) VALUES
(1, 15, 1, 'Spam o publicidad', '2025-11-07 01:38:46'),
(2, 11, 1, 'Lenguaje violento o amenazante', '2025-11-07 01:46:55');

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
-- Estructura de tabla para la tabla `subcategoria`
--

CREATE TABLE `subcategoria` (
  `id_subcategoria` int(11) NOT NULL,
  `id_categoria` int(11) NOT NULL,
  `codigo_subcategoria` varchar(50) DEFAULT NULL,
  `nombre_subcategoria` varchar(100) NOT NULL,
  `descripcion_subcategoria` text DEFAULT NULL,
  `imagen_subcategoria` varchar(255) DEFAULT 'default-subcategory.png',
  `url_imagen_subcategoria` varchar(500) DEFAULT NULL,
  `status_subcategoria` tinyint(1) DEFAULT 1,
  `estado_subcategoria` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `orden_subcategoria` int(11) DEFAULT 0,
  `fecha_creacion_subcategoria` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion_subcategoria` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `subcategoria`
--

INSERT INTO `subcategoria` (`id_subcategoria`, `id_categoria`, `codigo_subcategoria`, `nombre_subcategoria`, `descripcion_subcategoria`, `imagen_subcategoria`, `url_imagen_subcategoria`, `status_subcategoria`, `estado_subcategoria`, `orden_subcategoria`, `fecha_creacion_subcategoria`, `fecha_actualizacion_subcategoria`) VALUES
(1, 1, 'SUB-CAM-001', 'Camisas Formales', 'Camisas elegantes para oficina y eventos', 'default-subcategory.png', NULL, 1, 'activo', 1, '2025-11-06 21:06:35', NULL),
(2, 1, 'SUB-CAM-002', 'Camisas Casuales', 'Camisas informales para el d??a a d??a', 'default-subcategory.png', NULL, 1, 'activo', 2, '2025-11-06 21:06:35', NULL),
(3, 1, 'SUB-CAM-003', 'Camisas Deportivas', 'Camisas tipo polo y deportivas', 'default-subcategory.png', NULL, 1, 'activo', 3, '2025-11-06 21:06:35', NULL),
(4, 1, 'SUB-CAM-004', 'Camisas de Vestir', 'Camisas de alta calidad para ocasiones especiales', 'default-subcategory.png', NULL, 1, 'activo', 4, '2025-11-06 21:06:35', NULL),
(5, 2, 'SUB-PAN-001', 'Jeans', 'Pantalones de mezclilla en diferentes estilos', 'default-subcategory.png', NULL, 1, 'activo', 1, '2025-11-06 21:06:35', NULL),
(6, 2, 'SUB-PAN-002', 'Pantalones de Vestir', 'Pantalones formales para oficina', 'default-subcategory.png', NULL, 1, 'activo', 2, '2025-11-06 21:06:35', NULL),
(7, 2, 'SUB-PAN-003', 'Pantalones Deportivos', 'Pantalones para hacer ejercicio y deporte', 'default-subcategory.png', NULL, 1, 'activo', 3, '2025-11-06 21:06:35', NULL),
(8, 2, 'SUB-PAN-004', 'Shorts', 'Pantalones cortos para verano', 'default-subcategory.png', NULL, 1, 'activo', 4, '2025-11-06 21:06:35', NULL),
(9, 2, 'SUB-PAN-005', 'Chinos', 'Pantalones chinos casuales', 'default-subcategory.png', NULL, 1, 'activo', 5, '2025-11-06 21:06:35', NULL),
(10, 3, 'SUB-ZAP-001', 'Zapatos Formales', 'Zapatos de vestir y oficina', 'default-subcategory.png', NULL, 1, 'activo', 1, '2025-11-06 21:06:35', NULL),
(11, 3, 'SUB-ZAP-002', 'Sneakers', 'Zapatillas deportivas y casuales', 'default-subcategory.png', NULL, 1, 'activo', 2, '2025-11-06 21:06:35', NULL),
(12, 3, 'SUB-ZAP-003', 'Botas', 'Botas de diferentes estilos', 'default-subcategory.png', NULL, 1, 'activo', 3, '2025-11-06 21:06:35', NULL),
(13, 3, 'SUB-ZAP-004', 'Sandalias', 'Sandalias y chancletas', 'default-subcategory.png', NULL, 1, 'activo', 4, '2025-11-06 21:06:35', NULL),
(14, 3, 'SUB-ZAP-005', 'Zapatos Deportivos', 'Calzado para running y deporte', 'default-subcategory.png', NULL, 1, 'activo', 5, '2025-11-06 21:06:35', NULL),
(15, 4, 'SUB-ACC-001', 'Cinturones', 'Cinturones de cuero y tela', 'default-subcategory.png', NULL, 1, 'activo', 1, '2025-11-06 21:06:35', NULL),
(16, 4, 'SUB-ACC-002', 'Gorras', 'Gorras y sombreros', 'default-subcategory.png', NULL, 1, 'activo', 2, '2025-11-06 21:06:35', NULL),
(17, 4, 'SUB-ACC-003', 'Carteras', 'Billeteras y carteras', 'default-subcategory.png', NULL, 1, 'activo', 3, '2025-11-06 21:06:35', NULL),
(18, 4, 'SUB-ACC-004', 'Relojes', 'Relojes de pulsera', 'default-subcategory.png', NULL, 1, 'activo', 4, '2025-11-06 21:06:35', NULL),
(19, 4, 'SUB-ACC-005', 'Gafas de Sol', 'Lentes y gafas solares', 'default-subcategory.png', NULL, 1, 'activo', 5, '2025-11-06 21:06:35', NULL),
(20, 4, 'SUB-ACC-006', 'Bufandas', 'Bufandas y pa??uelos', 'default-subcategory.png', NULL, 1, 'activo', 6, '2025-11-06 21:06:35', NULL),
(21, 5, 'SUB-VES-001', 'Vestidos de Noche', 'Vestidos elegantes para eventos', 'default-subcategory.png', NULL, 1, 'activo', 1, '2025-11-06 21:06:35', NULL),
(22, 5, 'SUB-VES-002', 'Vestidos Casuales', 'Vestidos informales para el d??a', 'default-subcategory.png', NULL, 1, 'activo', 2, '2025-11-06 21:06:35', NULL),
(23, 5, 'SUB-VES-003', 'Vestidos de C??ctel', 'Vestidos semi-formales', 'default-subcategory.png', NULL, 1, 'activo', 3, '2025-11-06 21:06:35', NULL),
(24, 5, 'SUB-VES-004', 'Vestidos de Verano', 'Vestidos ligeros para clima c??lido', 'default-subcategory.png', NULL, 1, 'activo', 4, '2025-11-06 21:06:35', NULL),
(25, 5, 'SUB-VES-005', 'Vestidos de Fiesta', 'Vestidos para celebraciones', 'default-subcategory.png', NULL, 1, 'activo', 5, '2025-11-06 21:06:35', NULL);

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
  `avatar_usuario` varchar(255) DEFAULT 'public/assets/img/profiles/default-avatar.png',
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
(1, 'admin', 'admin123', 'admin@sleppystore.com', 'Administrador', 'Principal', '+5198765432122', '1990-01-15', 'M', 'avatar_1_1762473987.jpg', '2025-09-30 19:46:43', '2025-11-08 01:58:20', 1, 1, 'admin', 'activo'),
(2, 'juan_perez', 'juan123', 'juan@email.com', 'Juan', 'Pérez', '+51912345678', '1992-03-20', 'M', 'public/assets/img/profiles/default-avatar.png', '2025-09-30 19:46:43', NULL, 1, 1, 'cliente', 'activo'),
(3, 'maria_garcia', 'maria123', 'maria@email.com', 'María', 'García', '+51923456789', '1988-07-10', 'F', 'public/assets/img/profiles/default-avatar.png', '2025-09-30 19:46:43', NULL, 1, 1, 'cliente', 'activo'),
(4, 'carlos_lopez', 'carlos123', 'carlos@email.com', 'Carlos', 'López', '+51934567890', '1995-11-25', 'M', 'public/assets/img/profiles/default-avatar.png', '2025-09-30 19:46:43', NULL, 1, 0, 'cliente', 'activo'),
(5, 'ana_martinez', 'ana123', 'ana@email.com', 'Ana', 'Martínez', '+51945678901', '1993-05-08', 'F', 'public/assets/img/profiles/default-avatar.png', '2025-09-30 19:46:43', NULL, 1, 1, 'vendedor', 'activo'),
(6, 'sofia_torres', 'sofia123', 'sofia@email.com', 'Sofía', 'Torres', '+51967890123', '1996-02-14', 'F', 'public/assets/img/profiles/default-avatar.png', '2025-09-30 19:46:43', NULL, 1, 1, 'cliente', 'activo'),
(7, 'julito', '123456', 'spiritboom672@gmail.com', 'Julito', 'sleppy21', '986079838', '2006-04-21', 'M', 'avatar_7_1761056504.jpg', '2025-10-07 18:11:51', '2025-11-08 01:53:00', 1, 0, 'cliente', 'activo'),
(9, 'mochixs', 'alexsa_2005', 'alexsandraroldan16@gmail.com', 'Alexsa', 'Nose', '999999999', '2005-04-16', 'Otro', 'public/assets/img/profiles/default-avatar.png', '2025-11-01 21:18:24', '2025-11-01 21:18:42', 1, 0, 'cliente', 'activo');

--
-- Disparadores `usuario`
--
DELIMITER $$
CREATE TRIGGER `after_usuario_insert` AFTER INSERT ON `usuario` FOR EACH ROW BEGIN
    
    INSERT INTO notificacion (
        id_usuario,
        titulo_notificacion,
        mensaje_notificacion,
        tipo_notificacion,
        prioridad_notificacion,
        url_destino_notificacion
    ) VALUES (
        NEW.id_usuario,
        '??Bienvenido a SleppyStore! ????',
        CONCAT('Hola ', NEW.nombre_usuario, '! Gracias por registrarte en nuestra tienda. Explora nuestros productos, ofertas especiales y disfruta de una experiencia de compra ??nica. ??Estamos aqu?? para ayudarte!'),
        'info',
        'media',
        '/fashion-master/shop.php'
    );
END
$$
DELIMITER ;

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
-- Indices de la tabla `notificacion`
--
ALTER TABLE `notificacion`
  ADD PRIMARY KEY (`id_notificacion`),
  ADD KEY `idx_notif_usuario` (`id_usuario`),
  ADD KEY `idx_notif_leida` (`leida_notificacion`),
  ADD KEY `idx_notif_tipo` (`tipo_notificacion`),
  ADD KEY `idx_notif_prioridad` (`prioridad_notificacion`),
  ADD KEY `idx_notif_fecha` (`fecha_creacion_notificacion`),
  ADD KEY `idx_notif_estado` (`estado_notificacion`);

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
  ADD KEY `idx_dni` (`dni_pedido`),
  ADD KEY `idx_ruc` (`ruc_pedido`),
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
  ADD KEY `idx_stock_bajo` (`stock_actual_producto`,`stock_minimo_producto`),
  ADD KEY `idx_subcategoria` (`id_subcategoria`);

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
-- Indices de la tabla `resena_likes`
--
ALTER TABLE `resena_likes`
  ADD PRIMARY KEY (`id_like`),
  ADD UNIQUE KEY `unique_user_review` (`id_resena`,`id_usuario`),
  ADD KEY `idx_resena` (`id_resena`),
  ADD KEY `idx_usuario` (`id_usuario`);

--
-- Indices de la tabla `resena_reportes`
--
ALTER TABLE `resena_reportes`
  ADD PRIMARY KEY (`id_reporte`),
  ADD UNIQUE KEY `unique_user_review_report` (`id_resena`,`id_usuario`),
  ADD KEY `idx_resena` (`id_resena`),
  ADD KEY `idx_usuario` (`id_usuario`);

--
-- Indices de la tabla `sesion`
--
ALTER TABLE `sesion`
  ADD PRIMARY KEY (`id_sesion`),
  ADD KEY `idx_usuario_id_sesion` (`id_usuario`),
  ADD KEY `idx_fecha_expiracion` (`fecha_expiracion`);

--
-- Indices de la tabla `subcategoria`
--
ALTER TABLE `subcategoria`
  ADD PRIMARY KEY (`id_subcategoria`),
  ADD KEY `fk_subcategoria_categoria` (`id_categoria`);

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
  MODIFY `id_carrito` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=407;

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

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
  MODIFY `id_detalle_pedido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de la tabla `direccion`
--
ALTER TABLE `direccion`
  MODIFY `id_direccion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `favorito`
--
ALTER TABLE `favorito`
  MODIFY `id_favorito` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1283;

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
  MODIFY `id_movimiento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- AUTO_INCREMENT de la tabla `notificacion`
--
ALTER TABLE `notificacion`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- AUTO_INCREMENT de la tabla `orden`
--
ALTER TABLE `orden`
  MODIFY `id_orden` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id_token` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `pedido`
--
ALTER TABLE `pedido`
  MODIFY `id_pedido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `resena`
--
ALTER TABLE `resena`
  MODIFY `id_resena` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de la tabla `resena_likes`
--
ALTER TABLE `resena_likes`
  MODIFY `id_like` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `resena_reportes`
--
ALTER TABLE `resena_reportes`
  MODIFY `id_reporte` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `subcategoria`
--
ALTER TABLE `subcategoria`
  MODIFY `id_subcategoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
-- Filtros para la tabla `notificacion`
--
ALTER TABLE `notificacion`
  ADD CONSTRAINT `fk_notificacion_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `fk_producto_subcategoria` FOREIGN KEY (`id_subcategoria`) REFERENCES `subcategoria` (`id_subcategoria`) ON DELETE SET NULL ON UPDATE CASCADE,
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
-- Filtros para la tabla `resena_likes`
--
ALTER TABLE `resena_likes`
  ADD CONSTRAINT `resena_likes_ibfk_1` FOREIGN KEY (`id_resena`) REFERENCES `resena` (`id_resena`) ON DELETE CASCADE,
  ADD CONSTRAINT `resena_likes_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `resena_reportes`
--
ALTER TABLE `resena_reportes`
  ADD CONSTRAINT `resena_reportes_ibfk_1` FOREIGN KEY (`id_resena`) REFERENCES `resena` (`id_resena`) ON DELETE CASCADE,
  ADD CONSTRAINT `resena_reportes_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `sesion`
--
ALTER TABLE `sesion`
  ADD CONSTRAINT `sesion_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE SET NULL;

--
-- Filtros para la tabla `subcategoria`
--
ALTER TABLE `subcategoria`
  ADD CONSTRAINT `fk_subcategoria_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categoria` (`id_categoria`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
