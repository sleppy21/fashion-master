#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Sistema de Respuesta Inteligente Avanzado con Análisis Vectorial
Utiliza embeddings avanzados y análisis semántico profundo para comprensión contextual
"""

import pandas as pd
import numpy as np
import json
import re
import pickle
from pathlib import Path
from typing import List, Dict, Tuple, Any, Optional
from dataclasses import dataclass
import logging

# Importaciones para embeddings avanzados
try:
    from sentence_transformers import SentenceTransformer
    SENTENCE_TRANSFORMERS_AVAILABLE = True
except ImportError:
    SENTENCE_TRANSFORMERS_AVAILABLE = False
    print("⚠️ sentence-transformers no disponible, usando TF-IDF como fallback")

from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity, euclidean_distances
from sklearn.preprocessing import normalize
from sklearn.decomposition import TruncatedSVD
from sklearn.cluster import KMeans

# Configuración de rutas
BASE_DIR = Path(__file__).resolve().parent.parent.parent
DATA_DIR = BASE_DIR / "data"
MODELS_DIR = BASE_DIR / "models"

# Configuración de logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

@dataclass
class QueryAnalysis:
    """Resultado del análisis de una consulta"""
    original_query: str
    processed_query: str
    intent_category: str
    confidence_score: float
    extracted_entities: Dict[str, Any]
    semantic_context: Dict[str, float]
    suggested_responses: List[str]

@dataclass
class ProductMatch:
    """Resultado de búsqueda de producto"""
    product: Dict[str, Any]
    similarity_score: float
    match_reasons: List[str]
    confidence_level: str

class AdvancedIntelligentResponseSystem:
    """Sistema de respuesta inteligente avanzado con análisis vectorial profundo"""
    
    def __init__(self, model_name: str = "paraphrase-multilingual-MiniLM-L12-v2"):
        """
        Inicializa el sistema con modelo de embeddings especificado
        
        Args:
            model_name: Nombre del modelo de sentence-transformers a usar
        """
        self.model_name = model_name
        self.sentence_model = None
        self.fallback_vectorizer = TfidfVectorizer(
            stop_words=None, 
            lowercase=True,
            ngram_range=(1, 3),  # Incluir trigramas
            max_features=10000,
            sublinear_tf=True
        )
        
        # Datos
        self.productos_df = None
        self.productos_data = None
        self.faq_data = None
        self.training_data = None
        
        # Vectores y modelos
        self.product_vectors = None
        self.faq_vectors = None
        self.intent_vectors = None
        self.query_examples_vectors = None
        
        # Mapeos y clasificadores
        self.intent_classifier = None
        self.semantic_clusters = None
        self.entity_extractors = self._initialize_entity_extractors()
        
        # Cache de vectores para optimización
        self.vector_cache = {}
        
        # Métricas de rendimiento
        self.performance_metrics = {
            'total_queries': 0,
            'successful_matches': 0,
            'high_confidence_responses': 0,
            'fallback_responses': 0
        }
        
    def _initialize_entity_extractors(self) -> Dict[str, Any]:
        """Inicializa extractores de entidades específicas"""
        return {
            'talla_patterns': [
                r'\btalla\s*([xsmlXSML]+|\d+)\b',
                r'\b([xsmlXSML]{1,3})\b',
                r'\bnúmero\s*(\d+)\b',
                r'\b(extra\s*grande|extra\s*large|muy\s*grande)\b',
                r'\b(extra\s*pequeña|extra\s*small|muy\s*pequeña)\b',
                r'\bsize\s*([xsmlXSML]+|\d+)\b'
            ],
            'color_patterns': [
                r'\b(negro|negra|black|oscuro|carbon)\b',
                r'\b(blanco|blanca|white|claro|marfil|hueso|crema)\b',
                r'\b(azul|blue|celeste|marino|índigo|navy)\b',
                r'\b(rojo|roja|red|bermejo|escarlata|granate|burgundy)\b',
                r'\b(verde|green|esmeralda|oliva|lime|sage)\b',
                r'\b(amarillo|amarilla|yellow|dorado|gold|mostaza)\b',
                r'\b(rosa|pink|rosado|fucsia|magenta)\b',
                r'\b(gris|gray|grey|plomo|plateado|acero|silver)\b',
                r'\b(marrón|brown|café|camel|beige|tierra)\b',
                r'\b(morado|purple|violeta|lila|lavanda)\b',
                r'\b(naranja|orange|coral|durazno|peach)\b'
            ],
            'precio_indicators': [
                r'\b(precio|costo|valor|vale|cuesta|tarifa)\b',
                r'\b(cuánto|quanto|how\s*much|cost)\b',
                r'\b(barato|económico|cheap|affordable)\b',
                r'\b(caro|costoso|expensive|premium)\b',
                r'\b(oferta|descuento|rebaja|sale|promoción)\b'
            ],
            'disponibilidad_indicators': [
                r'\b(stock|existencia|disponible|hay|tienen)\b',
                r'\b(agotado|sold\s*out|terminado|acabado)\b',
                r'\b(cuántos|cantidad|quedan|sobran)\b',
                r'\b(llega|viene|restock|reposición)\b'
            ]
        }
    
    def initialize_embeddings_model(self) -> bool:
        """Inicializa el modelo de embeddings"""
        if not SENTENCE_TRANSFORMERS_AVAILABLE:
            logger.warning("sentence-transformers no disponible, usando fallback")
            return False
            
        try:
            logger.info(f"Cargando modelo de embeddings: {self.model_name}")
            self.sentence_model = SentenceTransformer(self.model_name)
            logger.info("✅ Modelo de embeddings cargado exitosamente")
            return True
        except Exception as e:
            logger.error(f"❌ Error cargando modelo de embeddings: {e}")
            return False
    
    def load_data(self) -> bool:
        """Carga todos los datos necesarios"""
        try:
            # Cargar productos expandidos
            if (DATA_DIR / "productos_expandidos.json").exists():
                with open(DATA_DIR / "productos_expandidos.json", 'r', encoding='utf-8') as f:
                    self.productos_data = json.load(f)
                self.productos_df = pd.DataFrame(self.productos_data)
                logger.info(f"✅ Cargados {len(self.productos_data)} productos expandidos")
            else:
                # Fallback al catálogo original
                with open(DATA_DIR / "catalogue.json", 'r', encoding='utf-8') as f:
                    catalogue = json.load(f)
                    self.productos_data = catalogue.get('productos', [])
                    self.faq_data = catalogue.get('faq', [])
                self.productos_df = pd.DataFrame(self.productos_data)
                logger.info(f"✅ Cargados {len(self.productos_data)} productos del catálogo base")
            
            # Cargar FAQ expandidos
            if (DATA_DIR / "context" / "faqs.json").exists():
                with open(DATA_DIR / "context" / "faqs.json", 'r', encoding='utf-8') as f:
                    faq_expanded = json.load(f)
                    self.faq_data = self._flatten_faq_data(faq_expanded)
                logger.info(f"✅ Cargados {len(self.faq_data)} FAQs expandidos")
            
            # Cargar datos de entrenamiento expandidos
            if (DATA_DIR / "preguntas_entrenamiento_expandido.csv").exists():
                self.training_data = pd.read_csv(DATA_DIR / "preguntas_entrenamiento_expandido.csv")
                logger.info(f"✅ Cargados {len(self.training_data)} ejemplos de entrenamiento")
            
            return True
            
        except Exception as e:
            logger.error(f"❌ Error cargando datos: {e}")
            return False
    
    def _flatten_faq_data(self, faq_expanded: Dict) -> List[Dict]:
        """Aplana la estructura JSON de FAQs para procesamiento"""
        flattened = []
        
        for categoria in faq_expanded.get('preguntas_frecuentes', []):
            cat_name = categoria.get('categoria', 'General')
            for pregunta_data in categoria.get('preguntas', []):
                flattened.append({
                    'categoria': cat_name,
                    'pregunta': pregunta_data.get('pregunta', ''),
                    'respuesta': pregunta_data.get('respuesta', ''),
                    'palabras_clave': pregunta_data.get('palabras_clave', [])
                })
        
        return flattened
    
    def create_advanced_vectors(self) -> bool:
        """Crea vectores avanzados para todos los datos"""
        try:
            logger.info("🔄 Creando vectores avanzados...")
            
            # Vectores de productos
            if self.productos_data:
                self._create_product_vectors()
            
            # Vectores de FAQ
            if self.faq_data:
                self._create_faq_vectors()
            
            # Vectores de ejemplos de entrenamiento
            if self.training_data is not None:
                self._create_training_vectors()
            
            logger.info("✅ Vectores avanzados creados exitosamente")
            return True
            
        except Exception as e:
            logger.error(f"❌ Error creando vectores: {e}")
            return False
    
    def _create_product_vectors(self):
        """Crea vectores para productos usando embeddings avanzados"""
        product_texts = []
        
        for producto in self.productos_data:
            # Crear texto enriquecido para cada producto
            text_parts = [
                producto.get('nombre', ''),
                producto.get('descripcion', ''),
                producto.get('categoria', ''),
                producto.get('marca', ''),
                producto.get('material', ''),
                ' '.join(producto.get('colores', [])),
                ' '.join(map(str, producto.get('tallas', []))),
                ' '.join(producto.get('etiquetas', []))
            ]
            
            # Agregar información contextual
            if producto.get('destacado'):
                text_parts.append('producto destacado bestseller popular')
            if producto.get('nuevo'):
                text_parts.append('producto nuevo reciente lanzamiento')
            if producto.get('descuento', 0) > 0:
                text_parts.append('oferta descuento promoción rebaja')
            
            # Agregar contexto de temporada
            temporada = producto.get('temporada', '')
            if temporada:
                text_parts.append(f'temporada {temporada.lower()}')
            
            product_text = ' '.join(text_parts).lower()
            product_texts.append(product_text)
        
        # Crear vectores usando el mejor método disponible
        if self.sentence_model:
            self.product_vectors = self.sentence_model.encode(product_texts)
            logger.info("✅ Vectores de productos creados con sentence-transformers")
        else:
            self.product_vectors = self.fallback_vectorizer.fit_transform(product_texts)
            logger.info("✅ Vectores de productos creados con TF-IDF")
    
    def _create_faq_vectors(self):
        """Crea vectores para FAQs"""
        if not self.faq_data:
            return
            
        faq_texts = []
        for faq in self.faq_data:
            # Combinar pregunta, respuesta y palabras clave
            text_parts = [
                faq.get('pregunta', ''),
                faq.get('respuesta', ''),
                ' '.join(faq.get('palabras_clave', []))
            ]
            faq_text = ' '.join(text_parts).lower()
            faq_texts.append(faq_text)
        
        if self.sentence_model:
            self.faq_vectors = self.sentence_model.encode(faq_texts)
        else:
            # Usar el mismo vectorizer que productos para consistencia
            try:
                self.faq_vectors = self.fallback_vectorizer.transform(faq_texts)
            except:
                # Si falla, crear nuevo vectorizer
                faq_vectorizer = TfidfVectorizer(stop_words=None, lowercase=True)
                self.faq_vectors = faq_vectorizer.fit_transform(faq_texts)
        
        logger.info("✅ Vectores de FAQ creados")
    
    def _create_training_vectors(self):
        """Crea vectores para datos de entrenamiento"""
        if self.training_data is None or len(self.training_data) == 0:
            return
        
        # Crear vectores para ejemplos de entrenamiento
        training_texts = []
        for _, row in self.training_data.iterrows():
            text_parts = [
                row.get('pregunta', ''),
                row.get('contexto', ''),
                row.get('sinonimos', '')
            ]
            training_text = ' '.join(str(part) for part in text_parts if pd.notna(part)).lower()
            training_texts.append(training_text)
        
        if self.sentence_model:
            self.query_examples_vectors = self.sentence_model.encode(training_texts)
        else:
            try:
                self.query_examples_vectors = self.fallback_vectorizer.transform(training_texts)
            except:
                vectorizer = TfidfVectorizer(stop_words=None, lowercase=True)
                self.query_examples_vectors = vectorizer.fit_transform(training_texts)
        
        logger.info("✅ Vectores de entrenamiento creados")
    
    def analyze_query_semantically(self, query: str) -> QueryAnalysis:
        """Analiza una consulta usando análisis semántico avanzado"""
        try:
            self.performance_metrics['total_queries'] += 1
            
            # Procesar la consulta
            processed_query = self._preprocess_query(query)
            
            # Extraer entidades
            entities = self._extract_entities(query)
            
            # Clasificar intención usando vectores
            intent_category, confidence = self._classify_intent_vectorial(query)
            
            # Analizar contexto semántico
            semantic_context = self._analyze_semantic_context(query)
            
            # Generar respuestas sugeridas
            suggested_responses = self._generate_suggested_responses(query, intent_category, entities)
            
            return QueryAnalysis(
                original_query=query,
                processed_query=processed_query,
                intent_category=intent_category,
                confidence_score=confidence,
                extracted_entities=entities,
                semantic_context=semantic_context,
                suggested_responses=suggested_responses
            )
            
        except Exception as e:
            logger.error(f"❌ Error en análisis semántico: {e}")
            return self._create_fallback_analysis(query)
    
    def _preprocess_query(self, query: str) -> str:
        """Preprocesa la consulta para análisis"""
        # Convertir a minúsculas
        processed = query.lower()
        
        # Normalizar espacios
        processed = re.sub(r'\s+', ' ', processed).strip()
        
        # Expandir contracciones comunes
        contractions = {
            'q': 'que',
            'xq': 'porque',
            'pq': 'porque',
            'tb': 'también',
            'tmb': 'también',
        }
        
        for contraction, expansion in contractions.items():
            processed = re.sub(rf'\b{contraction}\b', expansion, processed)
        
        return processed
    
    def _extract_entities(self, query: str) -> Dict[str, Any]:
        """Extrae entidades específicas de la consulta"""
        entities = {
            'tallas': [],
            'colores': [],
            'precios': [],
            'productos': [],
            'marcas': []
        }
        
        query_lower = query.lower()
        
        # Extraer tallas
        for pattern in self.entity_extractors['talla_patterns']:
            matches = re.findall(pattern, query_lower, re.IGNORECASE)
            entities['tallas'].extend(matches)
        
        # Extraer colores
        for pattern in self.entity_extractors['color_patterns']:
            matches = re.findall(pattern, query_lower)
            entities['colores'].extend(matches)
        
        # Extraer indicadores de precio
        for pattern in self.entity_extractors['precio_indicators']:
            if re.search(pattern, query_lower):
                entities['precios'].append('precio_mencionado')
        
        # Limpiar duplicados
        for key in entities:
            if isinstance(entities[key], list):
                entities[key] = list(set(entities[key]))
        
        return entities
    
    def _classify_intent_vectorial(self, query: str) -> Tuple[str, float]:
        """Clasifica la intención usando análisis vectorial"""
        if self.query_examples_vectors is None or self.training_data is None:
            return self._classify_intent_basic(query)
        
        try:
            # Vectorizar la consulta
            if self.sentence_model:
                query_vector = self.sentence_model.encode([query])
                similarities = cosine_similarity(query_vector, self.query_examples_vectors)[0]
            else:
                query_vector = self.fallback_vectorizer.transform([query])
                similarities = cosine_similarity(query_vector, self.query_examples_vectors)[0]
            
            # Encontrar la mejor coincidencia
            best_match_idx = np.argmax(similarities)
            confidence = similarities[best_match_idx]
            
            if confidence > 0.3:  # Umbral de confianza
                intent = self.training_data.iloc[best_match_idx]['categoria']
                self.performance_metrics['high_confidence_responses'] += 1
                return intent, confidence
            else:
                return self._classify_intent_basic(query)
                
        except Exception as e:
            logger.error(f"❌ Error en clasificación vectorial: {e}")
            return self._classify_intent_basic(query)
    
    def _classify_intent_basic(self, query: str) -> Tuple[str, float]:
        """Clasificación básica de intención como fallback"""
        query_lower = query.lower()
        
        # Reglas de clasificación básica con mayor precisión
        classification_rules = {
            'precio': {
                'keywords': ['precio', 'cuesta', 'vale', 'caro', 'barato', 'oferta', 'descuento', 'cuánto', 'valor', 'costo'],
                'confidence': 0.8
            },
            'talla': {
                'keywords': ['talla', 'medida', 'tamaño', 'size', 'número', 'ajuste', 'queda'],
                'confidence': 0.8
            },
            'disponibilidad': {
                'keywords': ['stock', 'disponible', 'hay', 'tienen', 'existencia', 'agotado', 'queda'],
                'confidence': 0.8
            },
            'color': {
                'keywords': ['color', 'viene', 'tonos', 'colores', 'negro', 'blanco', 'azul', 'rojo'],
                'confidence': 0.7
            },
            'envio': {
                'keywords': ['envío', 'delivery', 'entrega', 'shipping', 'domicilio'],
                'confidence': 0.8
            },
            'pago': {
                'keywords': ['pago', 'tarjeta', 'efectivo', 'cuotas', 'transferencia'],
                'confidence': 0.8
            },
            'devolucion': {
                'keywords': ['devolución', 'cambio', 'devolver', 'cambiar', 'garantía'],
                'confidence': 0.8
            }
        }
        
        for intent, rule in classification_rules.items():
            if any(keyword in query_lower for keyword in rule['keywords']):
                return intent, rule['confidence']
        
        return 'general', 0.5
    
    def _analyze_semantic_context(self, query: str) -> Dict[str, float]:
        """Analiza el contexto semántico de la consulta"""
        context = {
            'urgencia': 0.0,
            'cortesia': 0.0,
            'especificidad': 0.0,
            'emocion_positiva': 0.0,
            'emocion_negativa': 0.0
        }
        
        query_lower = query.lower()
        
        # Detectar urgencia
        urgency_words = ['necesito', 'urgente', 'rápido', 'ahora', 'ya', 'inmediato']
        context['urgencia'] = sum(1 for word in urgency_words if word in query_lower) / len(urgency_words)
        
        # Detectar cortesía
        courtesy_words = ['por favor', 'gracias', 'podrían', 'me gustaría', 'disculpe']
        context['cortesia'] = sum(1 for word in courtesy_words if word in query_lower) / len(courtesy_words)
        
        # Detectar especificidad (presencia de detalles)
        specific_indicators = len(re.findall(r'\b(talla|color|marca|modelo|número)\s+\w+', query_lower))
        context['especificidad'] = min(specific_indicators / 3.0, 1.0)
        
        # Detectar emociones
        positive_words = ['genial', 'excelente', 'perfecto', 'me gusta', 'fantástico']
        negative_words = ['mal', 'terrible', 'horrible', 'no me gusta', 'decepcionado']
        
        context['emocion_positiva'] = sum(1 for word in positive_words if word in query_lower) / len(positive_words)
        context['emocion_negativa'] = sum(1 for word in negative_words if word in query_lower) / len(negative_words)
        
        return context
    
    def _generate_suggested_responses(self, query: str, intent: str, entities: Dict) -> List[str]:
        """Genera respuestas sugeridas basadas en el análisis"""
        suggestions = []
        
        # Respuestas específicas por intención
        if intent == 'precio':
            if entities['productos']:
                suggestions.append(f"Te puedo ayudar con los precios de {', '.join(entities['productos'])}")
            else:
                suggestions.append("¿Qué producto específico te interesa conocer el precio?")
        
        elif intent == 'talla':
            if entities['tallas']:
                suggestions.append(f"Verificaré la disponibilidad de talla {', '.join(entities['tallas'])}")
            else:
                suggestions.append("¿Qué talla necesitas? Tengo la guía completa de medidas")
        
        elif intent == 'color':
            if entities['colores']:
                suggestions.append(f"Te muestro la disponibilidad en {', '.join(entities['colores'])}")
            else:
                suggestions.append("¿Qué colores te interesan? Tenemos una amplia gama")
        
        # Respuesta general siempre disponible
        suggestions.append("¿En qué más puedo ayudarte?")
        
        return suggestions[:3]  # Máximo 3 sugerencias
    
    def find_best_products_advanced(self, query: str, limit: int = 5) -> List[ProductMatch]:
        """Encuentra productos usando búsqueda semántica avanzada"""
        if self.product_vectors is None:
            return []
        
        try:
            # Vectorizar la consulta
            if self.sentence_model:
                query_vector = self.sentence_model.encode([query])
                similarities = cosine_similarity(query_vector, self.product_vectors)[0]
            else:
                query_vector = self.fallback_vectorizer.transform([query])
                similarities = cosine_similarity(query_vector, self.product_vectors)[0]
            
            # Obtener índices ordenados por similitud
            top_indices = similarities.argsort()[-limit:][::-1]
            
            product_matches = []
            for idx in top_indices:
                if similarities[idx] > 0.1:  # Umbral mínimo
                    producto = self.productos_data[idx].copy()
                    
                    # Calcular razones de coincidencia
                    match_reasons = self._calculate_match_reasons(query, producto, similarities[idx])
                    
                    # Determinar nivel de confianza
                    confidence_level = self._determine_confidence_level(similarities[idx])
                    
                    product_match = ProductMatch(
                        product=producto,
                        similarity_score=similarities[idx],
                        match_reasons=match_reasons,
                        confidence_level=confidence_level
                    )
                    
                    product_matches.append(product_match)
            
            if product_matches:
                self.performance_metrics['successful_matches'] += 1
            
            return product_matches
            
        except Exception as e:
            logger.error(f"❌ Error en búsqueda avanzada de productos: {e}")
            return []
    
    def _calculate_match_reasons(self, query: str, producto: Dict, similarity: float) -> List[str]:
        """Calcula las razones por las que un producto coincide con la consulta"""
        reasons = []
        query_lower = query.lower()
        
        # Coincidencias exactas
        if producto.get('nombre', '').lower() in query_lower:
            reasons.append("Coincidencia exacta en nombre")
        
        if producto.get('categoria', '').lower() in query_lower:
            reasons.append("Coincidencia en categoría")
        
        # Coincidencias en colores
        for color in producto.get('colores', []):
            if color.lower() in query_lower:
                reasons.append(f"Disponible en {color}")
        
        # Coincidencias en tallas
        for talla in producto.get('tallas', []):
            if str(talla).lower() in query_lower:
                reasons.append(f"Disponible en talla {talla}")
        
        # Coincidencias semánticas
        if similarity > 0.7:
            reasons.append("Alta similitud semántica")
        elif similarity > 0.5:
            reasons.append("Buena similitud semántica")
        
        # Productos destacados
        if producto.get('destacado'):
            reasons.append("Producto destacado")
        
        # Ofertas
        if producto.get('descuento', 0) > 0:
            reasons.append(f"En oferta ({producto['descuento']}% descuento)")
        
        return reasons[:3]  # Máximo 3 razones
    
    def _determine_confidence_level(self, similarity: float) -> str:
        """Determina el nivel de confianza basado en la similitud"""
        if similarity > 0.8:
            return "muy_alta"
        elif similarity > 0.6:
            return "alta"
        elif similarity > 0.4:
            return "media"
        elif similarity > 0.2:
            return "baja"
        else:
            return "muy_baja"
    
    def generate_intelligent_response(self, query: str) -> str:
        """Genera respuesta inteligente usando todo el sistema avanzado"""
        try:
            # Análisis completo de la consulta
            analysis = self.analyze_query_semantically(query)
            
            # Búsqueda de productos relevantes
            product_matches = self.find_best_products_advanced(query, limit=3)
            
            # Búsqueda en FAQ
            faq_response = self._search_faq_advanced(query)
            
            # Generar respuesta basada en el análisis
            response = self._compose_intelligent_response(analysis, product_matches, faq_response)
            
            return response
            
        except Exception as e:
            logger.error(f"❌ Error generando respuesta inteligente: {e}")
            self.performance_metrics['fallback_responses'] += 1
            return self._get_fallback_response()
    
    def _search_faq_advanced(self, query: str) -> Optional[Dict]:
        """Búsqueda avanzada en FAQ usando vectores"""
        if self.faq_vectors is None or not self.faq_data:
            return None
        
        try:
            if self.sentence_model:
                query_vector = self.sentence_model.encode([query])
                similarities = cosine_similarity(query_vector, self.faq_vectors)[0]
            else:
                query_vector = self.fallback_vectorizer.transform([query])
                similarities = cosine_similarity(query_vector, self.faq_vectors)[0]
            
            best_match_idx = np.argmax(similarities)
            
            if similarities[best_match_idx] > 0.4:  # Umbral para FAQ
                return self.faq_data[best_match_idx]
            
            return None
            
        except Exception as e:
            logger.error(f"❌ Error en búsqueda FAQ avanzada: {e}")
            return None
    
    def _compose_intelligent_response(self, analysis: QueryAnalysis, products: List[ProductMatch], faq: Optional[Dict]) -> str:
        """Compone respuesta inteligente basada en todos los análisis"""
        response_parts = []
        
        # Saludo contextual
        if analysis.semantic_context.get('cortesia', 0) > 0.3:
            response_parts.append("¡Por supuesto! Con gusto te ayudo.")
        else:
            response_parts.append("¡Hola! Te ayudo con tu consulta.")
        
        # Respuesta específica por intención
        if analysis.intent_category == 'precio':
            response_parts.extend(self._generate_price_response_advanced(analysis, products))
        elif analysis.intent_category == 'talla':
            response_parts.extend(self._generate_size_response_advanced(analysis, products))
        elif analysis.intent_category == 'disponibilidad':
            response_parts.extend(self._generate_availability_response_advanced(analysis, products))
        elif analysis.intent_category == 'color':
            response_parts.extend(self._generate_color_response_advanced(analysis, products))
        elif faq:
            response_parts.append(f"\n💡 **{faq['pregunta']}**")
            response_parts.append(f"{faq['respuesta']}")
        else:
            response_parts.extend(self._generate_general_response_advanced(analysis, products))
        
        # Productos recomendados si hay coincidencias
        if products and analysis.intent_category != 'general':
            response_parts.append("\n🛍️ **Productos que podrían interesarte:**")
            for i, match in enumerate(products[:2], 1):
                product = match.product
                response_parts.append(f"\n{i}. **{product['nombre']}**")
                response_parts.append(f"   💰 ${product.get('precio_final', product.get('precio', 'N/A'))}")
                if match.match_reasons:
                    response_parts.append(f"   ✨ {match.match_reasons[0]}")
        
        # Sugerencias adicionales
        if analysis.suggested_responses:
            response_parts.append(f"\n💬 {analysis.suggested_responses[0]}")
        
        return "\n".join(response_parts)
    
    def _generate_price_response_advanced(self, analysis: QueryAnalysis, products: List[ProductMatch]) -> List[str]:
        """Genera respuesta avanzada sobre precios"""
        parts = []
        
        if not products:
            parts.append("\n💰 Para darte información precisa de precios, ¿podrías especificar qué producto te interesa?")
            parts.append("Tenemos precios desde $25.90 y constantes ofertas especiales.")
        else:
            parts.append("\n💰 **Información de Precios:**")
            for product in products[:2]:
                precio = product.product.get('precio_final', product.product.get('precio', 'N/A'))
                descuento = product.product.get('descuento', 0)
                
                if descuento > 0:
                    precio_original = product.product.get('precio_original', precio)
                    parts.append(f"• **{product.product['nombre']}**: ${precio} (antes ${precio_original} - {descuento}% OFF)")
                else:
                    parts.append(f"• **{product.product['nombre']}**: ${precio}")
        
        return parts
    
    def _generate_size_response_advanced(self, analysis: QueryAnalysis, products: List[ProductMatch]) -> List[str]:
        """Genera respuesta avanzada sobre tallas"""
        parts = []
        
        tallas_consultadas = analysis.extracted_entities.get('tallas', [])
        
        if not products:
            parts.append("\n📏 **Guía de Tallas:**")
            parts.append("• **Ropa**: XS, S, M, L, XL, XXL")
            parts.append("• **Pantalones**: 28-42")
            parts.append("• **Calzado**: 35-45")
            if tallas_consultadas:
                parts.append(f"\n¿Te interesa la talla {', '.join(tallas_consultadas)} en algún producto específico?")
        else:
            parts.append("\n📏 **Información de Tallas:**")
            for product in products[:2]:
                tallas = product.product.get('tallas', [])
                parts.append(f"• **{product.product['nombre']}**: {', '.join(map(str, tallas))}")
                
                if tallas_consultadas:
                    disponibles = [t for t in tallas_consultadas if str(t).upper() in [str(talla).upper() for talla in tallas]]
                    if disponibles:
                        parts.append(f"  ✅ Talla {', '.join(disponibles)} disponible")
                    else:
                        parts.append(f"  ❌ Talla {', '.join(tallas_consultadas)} no disponible")
        
        return parts
    
    def _generate_availability_response_advanced(self, analysis: QueryAnalysis, products: List[ProductMatch]) -> List[str]:
        """Genera respuesta avanzada sobre disponibilidad"""
        parts = []
        
        if not products:
            parts.append("\n📦 Para verificar disponibilidad específica, necesito saber qué producto te interesa.")
            parts.append("Puedo revisar nuestro inventario en tiempo real.")
        else:
            parts.append("\n📦 **Estado de Inventario:**")
            for product in products[:2]:
                stock = product.product.get('stock_total', 0)
                disponible = product.product.get('disponible', stock > 0)
                
                if disponible:
                    parts.append(f"• **{product.product['nombre']}**: ✅ Disponible ({stock} unidades)")
                else:
                    parts.append(f"• **{product.product['nombre']}**: ❌ Agotado temporalmente")
                    parts.append("  📅 Próxima llegada: 3-5 días hábiles")
        
        return parts
    
    def _generate_color_response_advanced(self, analysis: QueryAnalysis, products: List[ProductMatch]) -> List[str]:
        """Genera respuesta avanzada sobre colores"""
        parts = []
        
        colores_consultados = analysis.extracted_entities.get('colores', [])
        
        if not products:
            parts.append("\n🎨 **Gama de Colores Disponibles:**")
            parts.append("Negro, Blanco, Azul, Rojo, Verde, Rosa, Gris, Marrón, y más")
            if colores_consultados:
                parts.append(f"\n¿Buscas algo específico en {', '.join(colores_consultados)}?")
        else:
            parts.append("\n🎨 **Colores Disponibles:**")
            for product in products[:2]:
                colores = product.product.get('colores', [])
                parts.append(f"• **{product.product['nombre']}**: {', '.join(colores)}")
                
                if colores_consultados:
                    coincidencias = [c for c in colores_consultados if any(c.lower() in color.lower() for color in colores)]
                    if coincidencias:
                        parts.append(f"  ✅ Disponible en {', '.join(coincidencias)}")
        
        return parts
    
    def _generate_general_response_advanced(self, analysis: QueryAnalysis, products: List[ProductMatch]) -> List[str]:
        """Genera respuesta general avanzada"""
        parts = []
        
        urgencia = analysis.semantic_context.get('urgencia', 0)
        
        if urgencia > 0.5:
            parts.append("\n⚡ Entiendo que necesitas información rápida.")
        
        parts.append("\n🛍️ **Estoy aquí para ayudarte con:**")
        parts.append("• 💰 Consultar precios y ofertas")
        parts.append("• 📏 Verificar tallas y disponibilidad")
        parts.append("• 🎨 Mostrar colores disponibles")
        parts.append("• 📦 Información de envíos y políticas")
        
        if products:
            parts.append("\n✨ Encontré algunos productos que podrían interesarte:")
        
        return parts
    
    def _create_fallback_analysis(self, query: str) -> QueryAnalysis:
        """Crea análisis básico en caso de error"""
        return QueryAnalysis(
            original_query=query,
            processed_query=query.lower(),
            intent_category='general',
            confidence_score=0.3,
            extracted_entities={},
            semantic_context={},
            suggested_responses=["¿En qué puedo ayudarte específicamente?"]
        )
    
    def _get_fallback_response(self) -> str:
        """Respuesta de emergencia"""
        return """😊 Disculpa, tuve un pequeño problema procesando tu consulta.

🔍 **Puedes preguntarme sobre:**
• Precios y ofertas
• Tallas disponibles  
• Colores y estilos
• Disponibilidad de productos

¡Inténtalo de nuevo o sé más específico!"""
    
    def get_performance_metrics(self) -> Dict[str, Any]:
        """Obtiene métricas de rendimiento del sistema"""
        if self.performance_metrics['total_queries'] > 0:
            success_rate = self.performance_metrics['successful_matches'] / self.performance_metrics['total_queries']
            confidence_rate = self.performance_metrics['high_confidence_responses'] / self.performance_metrics['total_queries']
        else:
            success_rate = 0
            confidence_rate = 0
        
        return {
            **self.performance_metrics,
            'success_rate': success_rate,
            'confidence_rate': confidence_rate,
            'model_type': 'sentence-transformers' if self.sentence_model else 'tfidf',
            'vector_cache_size': len(self.vector_cache)
        }
    
    def save_model_state(self, filepath: str) -> bool:
        """Guarda el estado del modelo para carga rápida"""
        try:
            state = {
                'product_vectors': self.product_vectors,
                'faq_vectors': self.faq_vectors,
                'query_examples_vectors': self.query_examples_vectors,
                'performance_metrics': self.performance_metrics,
                'model_name': self.model_name
            }
            
            with open(filepath, 'wb') as f:
                pickle.dump(state, f)
            
            logger.info(f"✅ Estado del modelo guardado en {filepath}")
            return True
            
        except Exception as e:
            logger.error(f"❌ Error guardando estado: {e}")
            return False
    
    def load_model_state(self, filepath: str) -> bool:
        """Carga estado previamente guardado"""
        try:
            with open(filepath, 'rb') as f:
                state = pickle.load(f)
            
            self.product_vectors = state.get('product_vectors')
            self.faq_vectors = state.get('faq_vectors')
            self.query_examples_vectors = state.get('query_examples_vectors')
            self.performance_metrics = state.get('performance_metrics', self.performance_metrics)
            
            logger.info(f"✅ Estado del modelo cargado desde {filepath}")
            return True
            
        except Exception as e:
            logger.error(f"❌ Error cargando estado: {e}")
            return False

def main():
    """Función de prueba del sistema avanzado"""
    print("🤖 Sistema de Respuesta Inteligente Avanzado - Fashion Store")
    print("=" * 70)
    
    # Inicializar sistema
    system = AdvancedIntelligentResponseSystem()
    
    # Inicializar embeddings
    system.initialize_embeddings_model()
    
    # Cargar datos
    if not system.load_data():
        print("❌ Error cargando datos")
        return
    
    # Crear vectores
    if not system.create_advanced_vectors():
        print("❌ Error creando vectores")
        return
    
    # Preguntas de prueba más complejas
    test_questions = [
        "Hola, me interesa saber cuánto vale una camiseta en color negro talla M",
        "¿Tienen stock de jeans azules?",
        "Busco algo económico para regalo",
        "¿Qué colores manejan en blazers?",
        "Necesito urgentemente información sobre envíos",
        "¿Puedo devolver si no me queda bien?",
        "¿Hay descuentos para estudiantes?",
        "Me gustaría ver zapatos cómodos para trabajo"
    ]
    
    print("\n🧪 PRUEBAS DEL SISTEMA AVANZADO")
    print("=" * 50)
    
    for question in test_questions:
        print(f"\n❓ Consulta: {question}")
        print("-" * 50)
        
        # Análisis semántico
        analysis = system.analyze_query_semantically(question)
        print(f"🧠 Intención: {analysis.intent_category} (confianza: {analysis.confidence_score:.2f})")
        print(f"🔍 Entidades: {analysis.extracted_entities}")
        
        # Respuesta completa
        response = system.generate_intelligent_response(question)
        print(f"\n🤖 Respuesta:\n{response}")
        print("\n" + "="*60)
    
    # Mostrar métricas
    metrics = system.get_performance_metrics()
    print(f"\n📊 MÉTRICAS DE RENDIMIENTO:")
    print(f"Total consultas: {metrics['total_queries']}")
    print(f"Tasa de éxito: {metrics['success_rate']:.2f}")
    print(f"Tasa de confianza alta: {metrics['confidence_rate']:.2f}")
    print(f"Tipo de modelo: {metrics['model_type']}")

if __name__ == "__main__":
    main()