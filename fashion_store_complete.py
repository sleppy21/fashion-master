#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Fashion Store - Servidor Web Completo con IA
Servidor que maneja tanto el landing page como las peticiones del bot con IA integrada
"""

import os
import sys
import datetime
import json
import socket
from flask import Flask, jsonify, request, send_from_directory, render_template_string
from flask_cors import CORS
import threading
import time

# Agregar el directorio del proyecto-bot-main al path
project_bot_path = os.path.join(os.path.dirname(__file__), 'proyecto-bot-main')
if os.path.exists(project_bot_path):
    sys.path.insert(0, project_bot_path)

# Configuración básica
app = Flask(__name__, static_folder='.', static_url_path='')
app.config['JSON_AS_ASCII'] = False
app.config['SECRET_KEY'] = 'fashion-store-secret-key-2024'

# CORS optimizado para el frontend
CORS(app, resources={
    r"/*": {
        "origins": ["http://localhost:3000", "http://127.0.0.1:3000", "http://192.168.1.30:3000"],
        "methods": ["GET", "POST", "OPTIONS"],
        "allow_headers": ["Content-Type", "Authorization", "Accept", "Origin", "X-Requested-With"]
    }
})

# Estado del bot
bot_status = {
    "initialized": True,
    "ready": True,
    "conversations": 0,
    "start_time": datetime.datetime.now(),
    "ai_system": None
}

# Inicializar sistema de IA
def initialize_ai_system():
    """Inicializa el sistema de IA inteligente"""
    try:
        # Intentar cargar el sistema avanzado primero
        try:
            from src.services.advanced_intelligent_response import AdvancedIntelligentResponseSystem
            
            ai_system = AdvancedIntelligentResponseSystem()
            
            # Inicializar embeddings
            embeddings_loaded = ai_system.initialize_embeddings_model()
            if embeddings_loaded:
                print("✅ Sistema de embeddings avanzado cargado")
            else:
                print("⚠️ Usando TF-IDF como fallback para embeddings")
            
            # Cargar datos
            if ai_system.load_data():
                print("✅ Datos de productos y FAQ cargados")
            else:
                print("⚠️ Error cargando datos, usando respuestas básicas")
            
            # Crear vectores avanzados
            if ai_system.create_advanced_vectors():
                print("✅ Vectores avanzados creados")
            else:
                print("⚠️ Error creando vectores avanzados")
            
            bot_status["ai_system"] = ai_system
            bot_status["system_type"] = "advanced"
            print("🤖 Sistema de IA Avanzado inicializado correctamente")
            return True
            
        except ImportError as e:
            print(f"⚠️ Sistema avanzado no disponible: {e}")
            print("📝 Intentando sistema básico...")
            
            # Fallback al sistema básico
            from src.services.intelligent_response import IntelligentResponseSystem
            
            ai_system = IntelligentResponseSystem()
            
            # Cargar datos
            if ai_system.load_data():
                print("✅ Datos de productos cargados (sistema básico)")
            else:
                print("⚠️ Error cargando datos, usando respuestas básicas")
            
            # Cargar clasificador
            if ai_system.load_classifier():
                print("✅ Clasificador de IA cargado (sistema básico)")
            else:
                print("⚠️ Clasificador no disponible, usando clasificación básica")
            
            bot_status["ai_system"] = ai_system
            bot_status["system_type"] = "basic"
            print("🤖 Sistema de IA Básico inicializado correctamente")
            return True
            
    except Exception as e:
        print(f"❌ Error inicializando IA: {e}")
        bot_status["system_type"] = "fallback"
        return False

# Inicializar IA al arrancar
print("🚀 Inicializando sistema básico (sin modelo IA)...")
# initialize_ai_system()  # Deshabilitado para evitar descargas

@app.before_request
def handle_preflight():
    if request.method == "OPTIONS":
        response = jsonify({'status': 'ok'})
        response.headers.add("Access-Control-Allow-Origin", request.headers.get('Origin', 'http://127.0.0.1:3000'))
        response.headers.add('Access-Control-Allow-Headers', 'Content-Type, Authorization, Accept, Origin, X-Requested-With')
        response.headers.add('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
        response.headers.add('Access-Control-Allow-Credentials', 'true')
        return response

@app.after_request
def after_request(response):
    origin = request.headers.get('Origin')
    allowed_origins = ['http://localhost:3000', 'http://127.0.0.1:3000', 'http://192.168.1.30:3000']
    if origin in allowed_origins:
        response.headers['Access-Control-Allow-Origin'] = origin
        response.headers['Access-Control-Allow-Credentials'] = 'true'
    response.headers['Access-Control-Allow-Headers'] = 'Content-Type, Authorization, Accept, Origin, X-Requested-With'
    response.headers['Access-Control-Allow-Methods'] = 'GET, POST, OPTIONS'
    response.headers['X-Content-Type-Options'] = 'nosniff'
    response.headers['X-Frame-Options'] = 'SAMEORIGIN'
    response.headers['Content-Security-Policy'] = "default-src 'self' 'unsafe-inline' 'unsafe-eval' http: https: data: blob: ws:"
    return response

# Servir el landing page con PHP
@app.route('/')
def index():
    """Servir la página principal PHP"""
    try:
        import subprocess
        import os
        
        # Directorio del proyecto
        project_dir = os.path.dirname(os.path.abspath(__file__))
        
        # Buscar index.php
        index_php_path = os.path.join(project_dir, 'index.php')
        
        if not os.path.exists(index_php_path):
            return "Error: index.php no encontrado", 404
        
        # Configurar variables de entorno para PHP
        env = os.environ.copy()
        env['SCRIPT_FILENAME'] = index_php_path
        env['REQUEST_METHOD'] = 'GET'
        env['REQUEST_URI'] = '/'
        env['QUERY_STRING'] = request.query_string.decode('utf-8') if request.query_string else ''
        env['SERVER_NAME'] = 'localhost'
        env['SERVER_PORT'] = '3000'
        env['HTTP_HOST'] = 'localhost:3000'
        
        # Buscar PHP ejecutable
        php_paths = [
            'C:\\xampp\\php\\php.exe',
            'C:\\php\\php.exe',
            'php'  # Si está en PATH
        ]
        
        php_executable = None
        for path in php_paths:
            try:
                result = subprocess.run([path, '--version'], capture_output=True, text=True, timeout=5)
                if result.returncode == 0:
                    php_executable = path
                    break
            except:
                continue
        
        if not php_executable:
            return "Error: PHP no encontrado. Instale XAMPP o configure PHP en el PATH", 500
        
        # Ejecutar PHP
        try:
            result = subprocess.run(
                [php_executable, '-f', index_php_path],
                cwd=project_dir,
                capture_output=True,
                text=True,
                timeout=30,
                env=env
            )
            
            if result.returncode == 0:
                return result.stdout
            else:
                print(f"Error PHP: {result.stderr}")
                return f"Error ejecutando PHP: {result.stderr}", 500
                
        except subprocess.TimeoutExpired:
            return "Error: Timeout ejecutando PHP", 500
        except Exception as e:
            return f"Error ejecutando PHP: {str(e)}", 500
            
    except Exception as e:
        print(f"Error general: {str(e)}")
        # Fallback a HTML estático si existe
        try:
            index_html_path = os.path.join(os.path.dirname(__file__), 'index.html')
            if os.path.exists(index_html_path):
                with open(index_html_path, 'r', encoding='utf-8') as f:
                    return f.read()
        except:
            pass
        
        return f"Error al cargar la página: {str(e)}", 500

# Servir archivos PHP y estáticos
@app.route('/<path:filename>')
def serve_static(filename):
    """Servir archivos PHP y estáticos (CSS, JS, imágenes, etc.)"""
    try:
        project_dir = os.path.dirname(os.path.abspath(__file__))
        
        # Si es un archivo PHP, ejecutarlo
        if filename.endswith('.php'):
            return serve_php_file(filename)
        
        # Para archivos estáticos, buscar en directorios
        static_paths = [project_dir, os.path.join(project_dir, '..')]
        
        for base_path in static_paths:
            file_path = os.path.join(base_path, filename)
            if os.path.exists(file_path):
                return send_from_directory(base_path, filename)
        
        return f"Archivo {filename} no encontrado", 404
    except Exception as e:
        return f"Error al servir {filename}: {str(e)}", 404

def serve_php_file(filename):
    """Ejecutar archivo PHP específico"""
    try:
        import subprocess
        
        project_dir = os.path.dirname(os.path.abspath(__file__))
        php_file_path = os.path.join(project_dir, filename)
        
        if not os.path.exists(php_file_path):
            return f"Archivo PHP {filename} no encontrado", 404
        
        # Configurar variables de entorno para PHP
        env = os.environ.copy()
        env['SCRIPT_FILENAME'] = php_file_path
        env['REQUEST_METHOD'] = request.method
        env['REQUEST_URI'] = '/' + filename
        env['QUERY_STRING'] = request.query_string.decode('utf-8') if request.query_string else ''
        env['SERVER_NAME'] = 'localhost'
        env['SERVER_PORT'] = '3000'
        env['HTTP_HOST'] = 'localhost:3000'
        
        # Si es POST, pasar datos
        if request.method == 'POST':
            env['CONTENT_TYPE'] = request.content_type or 'application/x-www-form-urlencoded'
            env['CONTENT_LENGTH'] = str(len(request.data))
        
        # Buscar PHP ejecutable
        php_paths = [
            'C:\\xampp\\php\\php.exe',
            'C:\\php\\php.exe',
            'php'
        ]
        
        php_executable = None
        for path in php_paths:
            try:
                result = subprocess.run([path, '--version'], capture_output=True, text=True, timeout=5)
                if result.returncode == 0:
                    php_executable = path
                    break
            except:
                continue
        
        if not php_executable:
            return "Error: PHP no encontrado", 500
        
        # Ejecutar PHP
        try:
            process = subprocess.Popen(
                [php_executable, '-f', php_file_path],
                cwd=project_dir,
                stdin=subprocess.PIPE,
                stdout=subprocess.PIPE,
                stderr=subprocess.PIPE,
                text=True,
                env=env
            )
            
            # Enviar datos POST si los hay
            post_data = None
            if request.method == 'POST' and request.data:
                post_data = request.data.decode('utf-8')
            
            stdout, stderr = process.communicate(input=post_data, timeout=30)
            
            if process.returncode == 0:
                return stdout
            else:
                print(f"Error PHP en {filename}: {stderr}")
                return f"Error ejecutando {filename}: {stderr}", 500
                
        except subprocess.TimeoutExpired:
            return f"Error: Timeout ejecutando {filename}", 500
        except Exception as e:
            return f"Error ejecutando {filename}: {str(e)}", 500
            
    except Exception as e:
        return f"Error al procesar {filename}: {str(e)}", 500

# Rutas específicas para páginas PHP principales
@app.route('/login.php', methods=['GET', 'POST'])
def login_page():
    """Página de login"""
    return serve_php_file('login.php')

@app.route('/validate_login.php', methods=['POST'])
def validate_login():
    """Validación de login"""
    return serve_php_file('validate_login.php')

@app.route('/logout.php')
def logout_page():
    """Cerrar sesión"""
    return serve_php_file('logout.php')

@app.route('/shop.php')
def shop_page():
    """Página de tienda"""
    return serve_php_file('shop.php')

# API del bot
@app.route('/health')
def health():
    """Health check del bot con información de IA"""
    ai_status = "enabled" if bot_status["ai_system"] else "basic_mode"
    
    return jsonify({
        "status": "healthy",
        "ready": bot_status["ready"],
        "ai_system": ai_status,
        "conversations": bot_status["conversations"],
        "timestamp": datetime.datetime.now().isoformat(),
        "service": "Fashion Store Complete Server with AI"
    }), 200

@app.route('/api/reload-ai', methods=['POST'])
def reload_ai():
    """Endpoint para recargar el sistema de IA"""
    try:
        success = initialize_ai_system()
        return jsonify({
            "status": "success" if success else "warning",
            "ai_enabled": bot_status["ai_system"] is not None,
            "message": "Sistema de IA recargado" if success else "Sistema básico activo"
        }), 200
    except Exception as e:
        return jsonify({
            "status": "error",
            "message": f"Error recargando IA: {e}"
        }), 500

@app.route('/api/chat', methods=['POST'])
def chat():
    """Endpoint principal del chat con IA integrada avanzada"""
    try:
        data = request.get_json()
        if not data or not data.get('message'):
            return jsonify({
                "error": "Mensaje requerido",
                "response": "Por favor, envía un mensaje válido."
            }), 400
            
        user_message = data.get('message', '').strip()
        bot_status["conversations"] += 1
        
        # Usar sistema de IA avanzado si está disponible
        if bot_status["ai_system"]:
            try:
                if bot_status.get("system_type") == "advanced":
                    # Sistema avanzado con análisis vectorial
                    response = bot_status["ai_system"].generate_intelligent_response(user_message)
                    ai_powered = True
                else:
                    # Sistema básico
                    response = bot_status["ai_system"].generate_response(user_message)
                    ai_powered = True
            except Exception as e:
                print(f"⚠️ Error en IA, usando respuestas básicas: {e}")
                response = get_intelligent_response(user_message)
                ai_powered = False
        else:
            # Fallback al sistema básico
            response = get_intelligent_response(user_message)
            ai_powered = False
        
        return jsonify({
            "response": response,
            "status": "success",
            "conversation_id": bot_status["conversations"],
            "timestamp": datetime.datetime.now().isoformat(),
            "ai_powered": ai_powered,
            "system_type": bot_status.get("system_type", "fallback")
        }), 200
        
    except Exception as e:
        print(f"Error en chat: {e}")
        return jsonify({
            "error": "Error del servidor",
            "response": "Disculpa, ha ocurrido un error. Por favor intenta de nuevo."
        }), 500

def get_intelligent_response(message):
    """Sistema de respuestas básico (fallback)"""
    message = message.lower()
    
    # Respuestas categorizadas por temas
    responses = {
        # Saludos
        "saludos": {
            "keywords": ["hola", "buenos", "buenas", "hey", "hi", "saludo"],
            "response": "¡Hola! 👋 Bienvenido a Fashion Store. Soy tu asistente virtual y estoy aquí para ayudarte. ¿En qué puedo asistirte hoy?"
        },
        
        # Productos
        "productos": {
            "keywords": ["producto", "ropa", "catálogo", "qué venden", "artículos", "tienda"],
            "response": "🛍️ En Fashion Store tenemos una amplia colección:\n\n👗 Ropa para mujer (vestidos, blusas, pantalones)\n👔 Ropa para hombre (camisas, pantalones, chaquetas)\n👶 Ropa infantil\n👜 Accesorios (bolsos, cinturones, joyas)\n👠 Calzado\n💄 Cosméticos\n\n¿Te interesa alguna categoría en particular?"
        },
        
        # Tallas
        "tallas": {
            "keywords": ["talla", "tamaño", "medida", "size", "guía"],
            "response": "📏 **Guía de Tallas Fashion Store**\n\n**Mujer:**\nXS (32-34) | S (36-38) | M (40-42) | L (44-46) | XL (48-50)\n\n**Hombre:**\nS (36-38) | M (40-42) | L (44-46) | XL (48-50) | XXL (52-54)\n\n**Calzado:** Disponible del 35 al 45\n\n💡 ¿Necesitas ayuda con alguna prenda específica?"
        },
        
        # Ofertas
        "ofertas": {
            "keywords": ["oferta", "descuento", "promoción", "rebaja", "barato", "precio"],
            "response": "🔥 **¡Ofertas Especiales!**\n\n🎉 Hasta 50% OFF en artículos seleccionados\n💳 15% adicional pagando con tarjeta\n📦 Envío GRATIS en compras +$99\n👕 3x2 en camisetas básicas\n👗 20% OFF en nueva colección\n\n⏰ Ofertas válidas hasta fin de mes. ¿Te interesa alguna categoría?"
        },
        
        # Envíos
        "envios": {
            "keywords": ["envío", "delivery", "entrega", "shipping", "cuánto tarda", "enviar"],
            "response": "📦 **Información de Envíos**\n\n🚚 **Envío estándar:** 3-5 días hábiles ($15)\n⚡ **Envío express:** 1-2 días hábiles ($25)\n🆓 **Envío gratis:** En compras mayores a $99\n📍 **Cobertura:** Todo el país\n📱 **Tracking:** Seguimiento en tiempo real\n\n¿Necesitas calcular el envío para tu ubicación?"
        },
        
        # Horarios
        "horarios": {
            "keywords": ["horario", "hora", "abierto", "cerrado", "cuándo", "atención"],
            "response": "🕒 **Horarios de Atención**\n\n🏪 **Tienda física:**\nLunes a Sábado: 10:00 AM - 9:00 PM\nDomingos: 11:00 AM - 7:00 PM\n\n💻 **Tienda online:** 24/7\n\n📞 **Atención al cliente:**\nLunes a Viernes: 9:00 AM - 6:00 PM\n📧 Email: soporte@fashionstore.com"
        },
        
        # Cambios y devoluciones
        "cambios": {
            "keywords": ["cambio", "devolución", "devolver", "cambiar", "garantía", "return"],
            "response": "🔄 **Política de Cambios y Devoluciones**\n\n✅ **30 días** para cambios y devoluciones\n🏷️ Productos con **etiquetas originales**\n📄 **Comprobante** de compra requerido\n💰 **Reembolso completo** o cambio por otro producto\n🆓 **Sin costo** para cambios en tienda\n\n¿Necesitas hacer algún cambio?"
        },
        
        # Contacto
        "contacto": {
            "keywords": ["contacto", "teléfono", "email", "dirección", "ubicación", "dónde"],
            "response": "📞 **Contáctanos**\n\n📱 WhatsApp: +1 234-567-8900\n📧 Email: info@fashionstore.com\n🏪 Dirección: Av. Principal 123, Centro\n💬 Chat en vivo: Disponible 24/7\n📱 App móvil: Descárgala gratis\n\n¿Cómo prefieres que te contactemos?"
        },
        
        # Pagos
        "pagos": {
            "keywords": ["pago", "tarjeta", "efectivo", "transferencia", "cuotas", "financiación"],
            "response": "💳 **Métodos de Pago**\n\n💳 Tarjetas de crédito/débito (Visa, MasterCard)\n📱 Pago móvil (PayPal, Apple Pay, Google Pay)\n💰 Efectivo (solo en tienda)\n🏦 Transferencia bancaria\n📊 **Cuotas sin interés** hasta 12 meses\n\n¿Necesitas información sobre financiación?"
        }
    }
    
    # Buscar respuesta inteligente
    for category, info in responses.items():
        for keyword in info["keywords"]:
            if keyword in message:
                return info["response"]
    
    # Respuesta por defecto más inteligente
    return """🤖 Hola, soy tu asistente virtual de Fashion Store. 

Puedo ayudarte con:
• 🛍️ Productos y catálogo
• 📏 Guía de tallas
• 🔥 Ofertas y promociones
• 📦 Información de envíos
• 🕒 Horarios de atención
• 🔄 Cambios y devoluciones
• 📞 Información de contacto

¿En qué te puedo ayudar específicamente?"""

@app.route('/api/suggestions')
def suggestions():
    """Sugerencias rápidas"""
    return jsonify({
        "suggestions": [
            {"text": "Ver ofertas del día 🔥", "action": "ofertas"},
            {"text": "Guía de tallas 📏", "action": "tallas"},
            {"text": "Información de envíos 📦", "action": "envios"},
            {"text": "Horarios de atención 🕒", "action": "horarios"},
            {"text": "Contactar soporte 📞", "action": "contacto"}
        ]
    })

if __name__ == "__main__":
    print("🚀 SleppyStore - Servidor PHP con IA Integrada")
    print("=" * 50)
    
    # Verificar si PHP está disponible
    php_available = False
    try:
        import subprocess
        php_paths = ['C:\\xampp\\php\\php.exe', 'C:\\php\\php.exe', 'php']
        for php_path in php_paths:
            try:
                result = subprocess.run([php_path, '--version'], capture_output=True, text=True, timeout=5)
                if result.returncode == 0:
                    php_available = True
                    print(f"✅ PHP encontrado: {php_path}")
                    break
            except:
                continue
    except:
        pass
    
    if not php_available:
        print("⚠️ PHP no encontrado - Instale XAMPP o configure PHP")
    
    port = 5000
    print(f"🚀 Iniciando servidor en puerto {port}...")
    print(f"🌐 Página Principal (PHP): http://localhost:{port}")
    print(f"🔐 Login: http://localhost:{port}/login.php")
    print(f"🛍️ Tienda: http://localhost:{port}/shop.php")
    print(f"🤖 Bot API: http://localhost:{port}/api/chat")
    print(f"🔧 Health Check: http://localhost:{port}/health")
    print(f"🤖 Sistema IA: {'Activado' if bot_status['ai_system'] else 'Modo Básico'}")
    print("=" * 50)
    
    # Datos de prueba para login
    print("👥 USUARIOS DE PRUEBA:")
    print("   Admin: admin / password")
    print("   Cliente: juan_perez / password")
    print("   Vendedor: ana_martinez / password")
    print("=" * 50)
    
    try:
        app.run(
            host='0.0.0.0',
            port=port,
            debug=False,
            threaded=True
        )
    except Exception as e:
        print(f"❌ Error iniciando el servidor: {e}")
        sys.exit(1)