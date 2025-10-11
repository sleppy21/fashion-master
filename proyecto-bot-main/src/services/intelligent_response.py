#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Sistema de respuesta inteligente para Fashion Store
Función principal que predice categoría, extrae productos y genera respuestas contextuales
"""

import pandas as pd
import numpy as np
import json
import re
from pathlib import Path
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity

# Configuración de rutas
BASE_DIR = Path(__file__).resolve().parent.parent.parent
DATA_DIR = BASE_DIR / "data"
MODELS_DIR = BASE_DIR / "models"

class IntelligentResponseSystem:
    """Sistema de respuesta inteligente para asistente de tienda de moda"""
    
    def __init__(self):
        self.productos_df = None
        self.productos_data = None
        self.faq_data = None
        self.classifier = None
        self.vectorizer = TfidfVectorizer(stop_words=None, lowercase=True)
        self.product_vectors = None
        
        # Patrones para extracción de información
        self.talla_patterns = [
            r'\btalla\s+([xsmlXSML]+|\d+)\b',
            r'\b([xsmlXSML]{1,3})\b',
            r'\bnúmero\s+(\d+)\b',
            r'\b(\d{2})\b'
        ]
        
        self.color_patterns = [
            r'\b(negro|negra|black)\b',
            r'\b(blanco|blanca|white)\b',
            r'\b(azul|blue)\b',
            r'\b(rojo|roja|red)\b',
            r'\b(verde|green)\b',
            r'\b(amarillo|amarilla|yellow)\b',
            r'\b(rosa|pink)\b',
            r'\b(gris|gray|grey)\b',
            r'\b(marrón|brown|café)\b',
            r'\b(morado|purple|violeta)\b',
            r'\b(naranja|orange)\b',
            r'\b(beige|crema)\b'
        ]
        
    def load_data(self):
        """Carga todos los datos necesarios"""
        try:
            # Cargar productos expandidos
            if (DATA_DIR / "productos_expandidos.json").exists():
                with open(DATA_DIR / "productos_expandidos.json", 'r', encoding='utf-8') as f:
                    self.productos_data = json.load(f)
                self.productos_df = pd.DataFrame(self.productos_data)
                print(f"✅ Cargados {len(self.productos_data)} productos expandidos")
            else:
                # Fallback al catálogo original
                with open(DATA_DIR / "catalogue.json", 'r', encoding='utf-8') as f:
                    catalogue = json.load(f)
                    self.productos_data = catalogue.get('productos', [])
                    self.faq_data = catalogue.get('faq', [])
                self.productos_df = pd.DataFrame(self.productos_data)
                print(f"✅ Cargados {len(self.productos_data)} productos del catálogo base")
            
            # Crear vectores de productos para búsqueda semántica
            self._create_product_vectors()
            
            return True
            
        except Exception as e:
            print(f"❌ Error cargando datos: {e}")
            return False
    
    def _create_product_vectors(self):
        """Crea vectores TF-IDF para búsqueda semántica de productos"""
        try:
            # Crear texto combinado para cada producto
            product_texts = []
            for producto in self.productos_data:
                text_parts = [
                    producto.get('nombre', ''),
                    producto.get('descripcion', ''),
                    producto.get('categoria', ''),
                    ' '.join(producto.get('colores', [])),
                    ' '.join(map(str, producto.get('tallas', [])))
                ]
                product_text = ' '.join(text_parts).lower()
                product_texts.append(product_text)
            
            # Crear vectores
            self.product_vectors = self.vectorizer.fit_transform(product_texts)
            print("✅ Vectores de productos creados para búsqueda semántica")
            
        except Exception as e:
            print(f"❌ Error creando vectores de productos: {e}")
    
    def load_classifier(self):
        """Carga el clasificador de preguntas"""
        try:
            import joblib
            classifier_path = MODELS_DIR / "question_classifier.joblib"
            if classifier_path.exists():
                self.classifier = joblib.load(classifier_path)
                print("✅ Clasificador de preguntas cargado")
                return True
            else:
                print("⚠️ Clasificador no encontrado, usando clasificación básica")
                return False
        except Exception as e:
            print(f"❌ Error cargando clasificador: {e}")
            return False
    
    def extract_product_info(self, question):
        """Extrae información del producto mencionado en la pregunta"""
        question_lower = question.lower()
        
        # Extraer talla
        talla = None
        for pattern in self.talla_patterns:
            match = re.search(pattern, question_lower)
            if match:
                talla = match.group(1).upper()
                break
        
        # Extraer color
        color = None
        for pattern in self.color_patterns:
            match = re.search(pattern, question_lower)
            if match:
                color = match.group(1).lower()
                break
        
        # Buscar producto por similitud semántica
        productos_encontrados = self.find_similar_products(question, limit=3)
        
        return {
            'talla': talla,
            'color': color,
            'productos_similares': productos_encontrados
        }
    
    def find_similar_products(self, query, limit=5):
        """Encuentra productos similares usando búsqueda semántica"""
        if self.product_vectors is None:
            return []
        
        try:
            # Vectorizar consulta
            query_vector = self.vectorizer.transform([query.lower()])
            
            # Calcular similitudes
            similarities = cosine_similarity(query_vector, self.product_vectors).flatten()
            
            # Obtener índices de los más similares
            top_indices = similarities.argsort()[-limit:][::-1]
            
            # Filtrar solo productos con similitud > 0.1
            productos_similares = []
            for idx in top_indices:
                if similarities[idx] > 0.1:
                    producto = self.productos_data[idx].copy()
                    producto['similarity_score'] = similarities[idx]
                    productos_similares.append(producto)
            
            return productos_similares
            
        except Exception as e:
            print(f"❌ Error en búsqueda semántica: {e}")
            return []
    
    def classify_question(self, question):
        """Clasifica la pregunta usando el modelo entrenado o reglas básicas"""
        if self.classifier:
            try:
                prediction = self.classifier.predict([question])[0]
                return prediction
            except:
                pass
        
        # Clasificación básica con reglas
        question_lower = question.lower()
        
        if any(word in question_lower for word in ['precio', 'cuesta', 'vale', 'caro', 'barato', 'oferta', 'descuento']):
            return 'precio'
        elif any(word in question_lower for word in ['talla', 'medida', 'tamaño', 'size', 'número']):
            return 'talla'
        elif any(word in question_lower for word in ['stock', 'disponible', 'hay', 'tienen', 'existencia', 'agotado']):
            return 'disponibilidad'
        elif any(word in question_lower for word in ['color', 'viene', 'tonos', 'colores']):
            return 'color'
        else:
            return 'general'
    
    def generate_response(self, question):
        """Genera respuesta inteligente basada en la pregunta del usuario"""
        try:
            # Clasificar pregunta
            categoria = self.classify_question(question)
            
            # Extraer información del producto
            product_info = self.extract_product_info(question)
            
            # Generar respuesta según categoría
            if categoria == 'precio':
                return self._generate_price_response(question, product_info)
            elif categoria == 'talla':
                return self._generate_size_response(question, product_info)
            elif categoria == 'disponibilidad':
                return self._generate_availability_response(question, product_info)
            elif categoria == 'color':
                return self._generate_color_response(question, product_info)
            else:
                return self._generate_general_response(question)
                
        except Exception as e:
            print(f"❌ Error generando respuesta: {e}")
            return self._get_default_response()
    
    def _generate_price_response(self, question, product_info):
        """Genera respuesta sobre precios"""
        productos = product_info['productos_similares']
        
        if not productos:
            return "💰 Para consultar precios específicos, ¿podrías decirme qué producto te interesa? Tenemos camisetas desde $25.90, pantalones desde $60.90, y muchos productos en oferta. ¿Hay alguna categoría en particular que te gustaría ver?"
        
        response_parts = ["💰 **Información de Precios:**\n"]
        
        for i, producto in enumerate(productos[:3]):
            precio = producto.get('precio_final', producto.get('precio', 'No disponible'))
            descuento = producto.get('descuento', 0)
            
            response_parts.append(f"🏷️ **{producto['nombre']}**")
            if descuento > 0:
                precio_original = producto.get('precio_original', precio)
                response_parts.append(f"   💸 Precio: ${precio:.2f} (${precio_original:.2f} -{descuento}% OFF)")
            else:
                response_parts.append(f"   💸 Precio: ${precio:.2f}")
            
            if producto.get('destacado'):
                response_parts.append("   ⭐ ¡Producto destacado!")
            
            response_parts.append("")
        
        response_parts.append("¿Te interesa alguno de estos productos? ¡Puedo darte más información sobre tallas y disponibilidad! 😊")
        
        return "\n".join(response_parts)
    
    def _generate_size_response(self, question, product_info):
        """Genera respuesta sobre tallas"""
        productos = product_info['productos_similares']
        talla_buscada = product_info['talla']
        
        if not productos:
            return "📏 **Guía de Tallas Fashion Store**\n\n**Ropa:** XS, S, M, L, XL, XXL\n**Pantalones:** 28, 30, 32, 34, 36, 38, 40, 42\n**Calzado:** 35-45\n\n¿Qué producto específico te interesa? Así puedo confirmarte la disponibilidad de tu talla."
        
        response_parts = ["📏 **Información de Tallas:**\n"]
        
        for producto in productos[:2]:
            tallas = producto.get('tallas', [])
            response_parts.append(f"👕 **{producto['nombre']}**")
            response_parts.append(f"   📐 Tallas disponibles: {', '.join(map(str, tallas))}")
            
            # Verificar disponibilidad de talla específica
            if talla_buscada:
                disponibilidad = producto.get('disponibilidad', {})
                if isinstance(disponibilidad, dict):
                    # Buscar en todos los colores
                    talla_disponible = False
                    for color, tallas_color in disponibilidad.items():
                        if isinstance(tallas_color, dict) and talla_buscada in tallas_color:
                            if tallas_color[talla_buscada] > 0:
                                talla_disponible = True
                                response_parts.append(f"   ✅ Talla {talla_buscada} disponible en {color}")
                    
                    if not talla_disponible:
                        response_parts.append(f"   ❌ Talla {talla_buscada} agotada temporalmente")
                elif talla_buscada in tallas:
                    response_parts.append(f"   ✅ Talla {talla_buscada} disponible")
            
            response_parts.append("")
        
        response_parts.append("💡 **Tip:** Si no encuentras tu talla, podemos notificarte cuando llegue nuevo stock. ¿Te ayudo con algo más?")
        
        return "\n".join(response_parts)
    
    def _generate_availability_response(self, question, product_info):
        """Genera respuesta sobre disponibilidad"""
        productos = product_info['productos_similares']
        
        if not productos:
            return "📦 Para verificar disponibilidad, ¿me puedes decir qué producto específico te interesa? Así puedo revisar nuestro inventario actual y darte información precisa sobre stock."
        
        response_parts = ["📦 **Estado de Inventario:**\n"]
        
        for producto in productos[:3]:
            stock_total = producto.get('stock_total', 0)
            disponible = producto.get('disponible', stock_total > 0)
            
            response_parts.append(f"📋 **{producto['nombre']}**")
            
            if disponible and stock_total > 0:
                response_parts.append(f"   ✅ **Disponible** (Stock: {stock_total} unidades)")
                
                # Mostrar disponibilidad por color/talla si existe
                disponibilidad = producto.get('disponibilidad', {})
                if isinstance(disponibilidad, dict):
                    colores_disponibles = []
                    for color, tallas in disponibilidad.items():
                        if isinstance(tallas, dict):
                            total_color = sum(tallas.values())
                            if total_color > 0:
                                colores_disponibles.append(f"{color} ({total_color})")
                    
                    if colores_disponibles:
                        response_parts.append(f"   🎨 Por colores: {', '.join(colores_disponibles[:3])}")
            else:
                response_parts.append("   ❌ **Agotado temporalmente**")
                response_parts.append("   📅 Próxima llegada: 3-5 días hábiles")
            
            response_parts.append("")
        
        response_parts.append("🔔 ¿Te gustaría que te notifique cuando un producto específico esté disponible?")
        
        return "\n".join(response_parts)
    
    def _generate_color_response(self, question, product_info):
        """Genera respuesta sobre colores"""
        productos = product_info['productos_similares']
        
        if not productos:
            return "🎨 **Colores Fashion Store**\n\nOfrecemos una amplia gama de colores: Negro, Blanco, Azul, Rojo, Verde, Rosa, Gris, y muchos más según la temporada.\n\n¿Qué producto específico te interesa? Así puedo mostrarte todos los colores disponibles."
        
        response_parts = ["🎨 **Colores Disponibles:**\n"]
        
        for producto in productos[:2]:
            colores = producto.get('colores', [])
            response_parts.append(f"👕 **{producto['nombre']}**")
            response_parts.append(f"   🌈 Colores: {', '.join(colores)}")
            
            # Mostrar disponibilidad por color
            disponibilidad = producto.get('disponibilidad', {})
            if isinstance(disponibilidad, dict):
                colores_stock = []
                for color in colores:
                    if color in disponibilidad:
                        tallas_color = disponibilidad[color]
                        if isinstance(tallas_color, dict):
                            total_stock = sum(tallas_color.values())
                            if total_stock > 0:
                                colores_stock.append(f"✅ {color}")
                            else:
                                colores_stock.append(f"❌ {color}")
                
                if colores_stock:
                    response_parts.append(f"   📦 Stock: {', '.join(colores_stock)}")
            
            response_parts.append("")
        
        response_parts.append("🎯 ¿Hay algún color específico que te interese? ¡Puedo verificar disponibilidad!")
        
        return "\n".join(response_parts)
    
    def _generate_general_response(self, question):
        """Genera respuesta general o busca en FAQ"""
        # Buscar en FAQ si está disponible
        if self.faq_data:
            question_lower = question.lower()
            for faq in self.faq_data:
                if any(keyword in question_lower for keyword in faq.get('pregunta', '').lower().split()):
                    return f"❓ **{faq['pregunta']}**\n\n💡 {faq['respuesta']}"
        
        return """👋 ¡Hola! Soy tu asistente virtual de Fashion Store.

🛍️ **Puedo ayudarte con:**
• Consultar precios y ofertas
• Verificar tallas disponibles
• Revisar stock y disponibilidad
• Mostrar colores disponibles
• Información general de la tienda

💬 ¿En qué puedo asistirte hoy? Puedes preguntarme sobre cualquier producto específico."""
    
    def _get_default_response(self):
        """Respuesta por defecto en caso de error"""
        return """😊 Disculpa, no pude procesar tu consulta correctamente.

🔍 **Puedes intentar preguntarme:**
• "¿Cuánto cuesta la camiseta negra?"
• "¿Tienen talla M en stock?"
• "¿Qué colores hay disponibles?"
• "¿Cuáles son sus horarios?"

¡Estoy aquí para ayudarte!"""

def main():
    """Función de prueba del sistema de respuesta"""
    print("🤖 Sistema de Respuesta Inteligente - Fashion Store")
    print("=" * 60)
    
    # Inicializar sistema
    response_system = IntelligentResponseSystem()
    
    # Cargar datos
    if not response_system.load_data():
        print("❌ Error cargando datos")
        return
    
    # Cargar clasificador
    response_system.load_classifier()
    
    # Preguntas de prueba
    test_questions = [
        "¿Cuánto cuesta la camiseta premium?",
        "¿Tienen talla M en jeans?",
        "¿Hay stock de polos azules?",
        "¿En qué colores viene el blazer?",
        "¿Cuáles son sus horarios?",
        "¿Tienen descuentos?",
    ]
    
    print("\n🧪 PRUEBAS DEL SISTEMA DE RESPUESTA")
    print("=" * 50)
    
    for question in test_questions:
        print(f"\n❓ Pregunta: {question}")
        print("-" * 40)
        response = response_system.generate_response(question)
        print(response)
        print("\n" + "="*50)

if __name__ == "__main__":
    main()