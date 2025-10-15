<?php
/**
 * API del Bot SleppyStore para Apache/PHP
 * Endpoint unificado en puerto 80 con Apache
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, Accept, Origin, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

class BotAPI {
    private $knowledgeBase;
    
    public function __construct() {
        $this->loadKnowledgeBase();
    }
    
    private function loadKnowledgeBase() {
        $contextDir = __DIR__ . '/../data/context/';
        $this->knowledgeBase = [
            'productos' => $this->loadJSON($contextDir . 'productos.json'),
            'faqs' => $this->loadJSON($contextDir . 'faqs.json'),
            'ofertas' => $this->loadJSON($contextDir . 'ofertas.json'),
            'tiendas' => $this->loadJSON($contextDir . 'tiendas.json')
        ];
    }
    
    private function loadJSON($file) {
        if (file_exists($file)) {
            return json_decode(file_get_contents($file), true) ?: [];
        }
        return [];
    }
    
    public function processChat($message) {
        $message = strtolower(trim($message));
        $response = $this->getIntelligentResponse($message);
        
        return [
            'response' => $response,
            'status' => 'success',
            'timestamp' => date('c'),
            'conversation_id' => uniqid(),
            'ai_powered' => true,
            'system_type' => 'advanced_php'
        ];
    }
    
    private function getIntelligentResponse($message) {
        // Análisis de intención avanzado
        $intent = $this->analyzeIntent($message);
        
        switch ($intent['category']) {
            case 'productos':
                return $this->handleProductsQuery($message, $intent);
            case 'ofertas':
                return $this->handleOffersQuery($message, $intent);
            case 'envios':
                return $this->handleShippingQuery($message, $intent);
            case 'tallas':
                return $this->handleSizesQuery($message, $intent);
            case 'cambios':
                return $this->handleReturnsQuery($message, $intent);
            case 'pagos':
                return $this->handlePaymentQuery($message, $intent);
            case 'horarios':
                return $this->handleScheduleQuery($message, $intent);
            case 'contacto':
                return $this->handleContactQuery($message, $intent);
            case 'saludo':
                return $this->handleGreeting($message, $intent);
            default:
                return $this->handleDefaultQuery($message, $intent);
        }
    }
    
    private function analyzeIntent($message) {
        $keywords = [
            'productos' => ['producto', 'ropa', 'vestido', 'camisa', 'pantalon', 'zapatos', 'accesorios', 'buscar', 'catalogo', 'disponible'],
            'ofertas' => ['oferta', 'descuento', 'promocion', 'rebaja', 'barato', 'precio', 'descuentos', 'promociones'],
            'envios' => ['envio', 'envios', 'delivery', 'entrega', 'shipping', 'cuanto tarda', 'enviar', 'domicilio'],
            'tallas' => ['talla', 'tallas', 'tamaño', 'medida', 'medidas', 'guia', 'que talla', 'tamaños'],
            'cambios' => ['cambio', 'cambios', 'devolucion', 'devolver', 'cambiar', 'garantia', 'return', 'devoluciones'],
            'pagos' => ['pago', 'pagos', 'tarjeta', 'efectivo', 'transferencia', 'cuotas', 'financiacion', 'pagar'],
            'horarios' => ['horario', 'horarios', 'hora', 'abierto', 'cerrado', 'cuando', 'atencion', 'abiertos'],
            'contacto' => ['contacto', 'telefono', 'email', 'direccion', 'ubicacion', 'donde', 'contactar', 'ubicados'],
            'saludo' => ['hola', 'buenas', 'buenos dias', 'buenas tardes', 'buenas noches', 'saludos', 'hey', 'que tal']
        ];
        
        $scores = [];
        foreach ($keywords as $category => $words) {
            $score = 0;
            foreach ($words as $word) {
                if (strpos($message, $word) !== false) {
                    $score += 1;
                }
            }
            $scores[$category] = $score;
        }
        
        $bestCategory = array_keys($scores, max($scores))[0];
        $confidence = max($scores) > 0 ? max($scores) / count(explode(' ', $message)) : 0;
        
        return [
            'category' => $bestCategory,
            'confidence' => $confidence,
            'scores' => $scores
        ];
    }
    
    private function handleProductsQuery($message, $intent) {
        $productos = $this->knowledgeBase['productos'] ?? [];
        
        if (empty($productos)) {
            return "🛍️ **Catálogo de Productos**\n\nTenemos una amplia selección de:\n\n👗 **Vestidos** - Casuales, elegantes, de fiesta\n👕 **Camisetas** - Básicas, estampadas, deportivas\n👖 **Pantalones** - Jeans, formales, deportivos\n👠 **Calzado** - Tacones, deportivos, botas\n👜 **Accesorios** - Bolsos, joyas, bufandas\n\n¿Qué tipo específico te interesa?";
        }
        
        // Buscar productos específicos
        $foundProducts = [];
        foreach ($productos as $product) {
            if (isset($product['nombre']) && strpos(strtolower($product['nombre']), $message) !== false) {
                $foundProducts[] = $product;
            }
        }
        
        if (!empty($foundProducts)) {
            $response = "🛍️ **Productos encontrados:**\n\n";
            foreach (array_slice($foundProducts, 0, 3) as $product) {
                $response .= "• **{$product['nombre']}**\n";
                if (isset($product['precio'])) {
                    $response .= "  💰 Precio: \${$product['precio']}\n";
                }
                if (isset($product['descripcion'])) {
                    $response .= "  📝 {$product['descripcion']}\n";
                }
                $response .= "\n";
            }
            return $response . "¿Te interesa alguno específico?";
        }
        
        return "🛍️ **Catálogo Fashion Store**\n\nTenemos productos en todas las categorías:\n\n👗 Vestidos y faldas\n👕 Camisetas y blusas\n👖 Pantalones y jeans\n👠 Calzado para toda ocasión\n👜 Accesorios únicos\n\n💫 Todos con la mejor calidad y estilo\n\n¿Hay alguna categoría específica que te interese?";
    }
    
    private function handleOffersQuery($message, $intent) {
        return "🔥 **¡Ofertas Especiales Activas!**\n\n🎉 **Hasta 50% OFF** en artículos seleccionados\n💳 **15% adicional** pagando con tarjeta de crédito\n📦 **Envío GRATIS** en compras mayores a \$99\n👕 **3x2** en camisetas básicas\n👗 **20% OFF** en nueva colección de vestidos\n🛍️ **Combo especial**: 2 pantalones + 1 blusa por \$89\n\n⏰ **Ofertas válidas hasta fin de mes**\n🎯 **Descuentos automáticos** al agregar al carrito\n\n¿Te interesa alguna categoría en particular?";
    }
    
    private function handleShippingQuery($message, $intent) {
        return "📦 **Información de Envíos**\n\n🚚 **Envío estándar:** 3-5 días hábiles (\$15)\n⚡ **Envío express:** 1-2 días hábiles (\$25)\n🆓 **Envío gratis:** En compras mayores a \$99\n📍 **Cobertura:** Todo el territorio nacional\n📱 **Tracking:** Seguimiento en tiempo real\n📊 **Entrega garantizada** con código de seguimiento\n\n🏪 **Retiro en tienda:** Gratis, listo en 2 horas\n📅 **Horarios de retiro:** Lun-Sáb 10AM-8PM\n\n¿Necesitas calcular el envío para tu ubicación específica?";
    }
    
    private function handleSizesQuery($message, $intent) {
        return "📏 **Guía de Tallas - Fashion Store**\n\n👗 **VESTIDOS Y BLUSAS**\nXS: 32-34 | S: 36-38 | M: 40-42 | L: 44-46 | XL: 48-50\n\n👖 **PANTALONES**\n26: XS | 28: S | 30: M | 32: L | 34: XL\n\n👠 **CALZADO**\n35-36: S | 37-38: M | 39-40: L | 41-42: XL\n\n📐 **Tips para elegir tu talla:**\n• Mide tu contorno de busto, cintura y cadera\n• Consulta nuestra tabla específica por producto\n• En caso de duda, elige la talla mayor\n• Ofrecemos cambios gratuitos por talla\n\n¿Necesitas ayuda con alguna prenda específica?";
    }
    
    private function handleReturnsQuery($message, $intent) {
        return "🔄 **Política de Cambios y Devoluciones**\n\n✅ **30 días** para cambios y devoluciones\n🏷️ Productos con **etiquetas originales**\n📄 **Comprobante** de compra requerido\n💰 **Reembolso completo** o cambio por otro producto\n🆓 **Sin costo** para cambios en tienda física\n📦 **Envío de devolución:** \$10 (descontado del reembolso)\n\n⚡ **Proceso rápido:**\n1. Contacta a servicio al cliente\n2. Recibe etiqueta de devolución\n3. Envía el producto\n4. Reembolso en 3-5 días hábiles\n\n¿Necesitas procesar algún cambio o devolución?";
    }
    
    private function handlePaymentQuery($message, $intent) {
        return "💳 **Métodos de Pago Disponibles**\n\n💳 **Tarjetas:** Crédito y débito (Visa, MasterCard, Amex)\n📱 **Pago digital:** PayPal, Apple Pay, Google Pay\n💰 **Efectivo:** Solo en tienda física\n🏦 **Transferencia:** Bancaria (descuento 5%)\n\n📊 **Financiación disponible:**\n• **Cuotas sin interés:** Hasta 12 meses\n• **Financiación:** Hasta 24 cuotas con interés\n• **Plan joven:** Estudiantes 15% descuento\n\n🔒 **Seguridad garantizada:**\n• Encriptación SSL\n• Protección antifraude\n• Datos seguros\n\n¿Necesitas información sobre algún método específico?";
    }
    
    private function handleScheduleQuery($message, $intent) {
        return "🕒 **Horarios de Atención**\n\n🏪 **Tienda física:**\n• **Lunes a Sábado:** 10:00 AM - 9:00 PM\n• **Domingos:** 11:00 AM - 7:00 PM\n• **Feriados:** 12:00 PM - 6:00 PM\n\n💻 **Tienda online:** 24/7 disponible\n\n📞 **Atención al cliente:**\n• **Lunes a Viernes:** 9:00 AM - 6:00 PM\n• **Sábados:** 10:00 AM - 4:00 PM\n• **Chat en vivo:** Disponible 24/7\n\n📧 **Email:** soporte@fashionstore.com\n⏱️ **Respuesta:** Máximo 24 horas\n\n¿Necesitas contactar con algún departamento específico?";
    }
    
    private function handleContactQuery($message, $intent) {
        return "📞 **Contactanos - Fashion Store**\n\n📱 **WhatsApp:** +1 234-567-8900\n📧 **Email:** info@fashionstore.com\n📞 **Teléfono:** (011) 4567-8900\n\n🏪 **Tienda física:**\n📍 **Dirección:** Av. Principal 123, Centro Comercial Plaza\n🚇 **Transporte:** Metro Línea A - Estación Centro\n🚗 **Estacionamiento:** Gratuito 2 horas\n\n💬 **Canales digitales:**\n• 🌐 **Web:** www.fashionstore.com\n• 📱 **App móvil:** Descarga gratuita\n• 📘 **Facebook:** @FashionStore\n• 📸 **Instagram:** @fashion_store_oficial\n\n¿Cómo prefieres que te contactemos?";
    }
    
    private function handleGreeting($message, $intent) {
        $greetings = [
            "¡Hola! 👋 Bienvenido a Fashion Store. Soy tu asistente virtual y estoy aquí para ayudarte con cualquier consulta sobre nuestros productos, ofertas, envíos y más. ¿En qué te puedo ayudar hoy?",
            "¡Buenas! 😊 Soy el asistente de Fashion Store. Puedo ayudarte a encontrar el outfit perfecto, resolver dudas sobre tallas, ofertas, envíos y todo lo que necesites. ¿Qué te interesa?",
            "¡Hola! ✨ Bienvenido a Fashion Store, tu destino de moda favorito. Estoy aquí para hacer tu experiencia de compra increíble. ¿En qué puedo asistirte?"
        ];
        
        return $greetings[array_rand($greetings)];
    }
    
    private function handleDefaultQuery($message, $intent) {
        return "🤖 **Asistente Fashion Store**\n\nHola, soy tu asistente virtual especializado en moda. Puedo ayudarte con:\n\n🛍️ **Productos y catálogo**\n📏 **Guía de tallas**\n🔥 **Ofertas y promociones**\n📦 **Información de envíos**\n🕒 **Horarios de atención**\n🔄 **Cambios y devoluciones**\n💳 **Métodos de pago**\n📞 **Información de contacto**\n\n✨ **Ejemplos de consultas:**\n• \"¿Qué vestidos tienen en oferta?\"\n• \"¿Cuánto cuesta el envío?\"\n• \"¿Cuál es mi talla ideal?\"\n\n¿En qué te puedo ayudar específicamente?";
    }
    
    public function getHealthStatus() {
        return [
            'status' => 'healthy',
            'service' => 'Fashion Store Bot API',
            'version' => '2.0.0',
            'timestamp' => date('c'),
            'features' => [
                'intelligent_responses' => true,
                'knowledge_base' => true,
                'intent_analysis' => true,
                'multilingual' => false
            ]
        ];
    }
    
    public function getSuggestions() {
        return [
            'suggestions' => [
                ['text' => 'Ver ofertas del día 🔥', 'action' => 'ofertas'],
                ['text' => 'Guía de tallas 📏', 'action' => 'tallas'],
                ['text' => 'Información de envíos 📦', 'action' => 'envios'],
                ['text' => 'Horarios de atención 🕒', 'action' => 'horarios'],
                ['text' => 'Contactar soporte 📞', 'action' => 'contacto'],
                ['text' => 'Política de cambios 🔄', 'action' => 'cambios'],
                ['text' => 'Métodos de pago 💳', 'action' => 'pagos'],
                ['text' => 'Ver catálogo 👗', 'action' => 'productos']
            ]
        ];
    }
}

// Manejo de la API
try {
    $bot = new BotAPI();
    
    $action = $_GET['action'] ?? $_POST['action'] ?? 'chat';
    
    switch ($action) {
        case 'health':
            echo json_encode($bot->getHealthStatus());
            break;
            
        case 'suggestions':
            echo json_encode($bot->getSuggestions());
            break;
            
        case 'chat':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'Método no permitido']);
                break;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $message = $input['message'] ?? '';
            
            if (empty($message)) {
                http_response_code(400);
                echo json_encode(['error' => 'Mensaje requerido']);
                break;
            }
            
            $response = $bot->processChat($message);
            echo json_encode($response);
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint no encontrado']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error interno del servidor',
        'message' => $e->getMessage()
    ]);
}
?>