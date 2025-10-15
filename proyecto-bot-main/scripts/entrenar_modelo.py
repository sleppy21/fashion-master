#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Entrenador de modelo de clasificación de preguntas
Usa scikit-learn para crear un pipeline con CountVectorizer y MultinomialNB
"""

import pandas as pd
import numpy as np
import joblib
import json
from pathlib import Path
from sklearn.feature_extraction.text import CountVectorizer, TfidfVectorizer
from sklearn.naive_bayes import MultinomialNB
from sklearn.pipeline import Pipeline
from sklearn.model_selection import train_test_split, cross_val_score
from sklearn.metrics import classification_report, confusion_matrix, accuracy_score
import re

# Configuración de rutas
BASE_DIR = Path(__file__).resolve().parent.parent
DATA_DIR = BASE_DIR / "data"
MODELS_DIR = BASE_DIR / "models"

class QuestionClassifier:
    """Clasificador de preguntas de clientes usando Naive Bayes"""
    
    def __init__(self):
        self.pipeline = None
        self.categories = ['precio', 'talla', 'disponibilidad', 'color', 'general']
        self.model_path = MODELS_DIR / "question_classifier.joblib"
        self.vectorizer_path = MODELS_DIR / "vectorizer.joblib"
        
    def preprocess_text(self, text):
        """Preprocesa el texto para mejorar la clasificación"""
        # Convertir a minúsculas
        text = text.lower()
        
        # Remover caracteres especiales pero mantener espacios
        text = re.sub(r'[^\w\sáéíóúñü]', '', text)
        
        # Normalizar espacios
        text = ' '.join(text.split())
        
        return text
    
    def expand_training_data(self, df_preguntas):
        """Expande el dataset de entrenamiento con más variaciones"""
        expanded_data = []
        
        # Datos originales
        for _, row in df_preguntas.iterrows():
            expanded_data.append({
                'pregunta': self.preprocess_text(row['pregunta']),
                'categoria': row['categoria']
            })
        
        # Expansiones adicionales para mejorar el modelo
        expansions = {
            'precio': [
                'cuanto vale', 'que precio tiene', 'esta caro', 'esta barato',
                'cuanto me sale', 'valor del producto', 'costo total',
                'precio con descuento', 'oferta especial', 'promocion',
                'esta en rebaja', 'descuento disponible', 'precio final'
            ],
            'talla': [
                'que medidas tienen', 'mi talla es', 'necesito talla',
                'talla correcta', 'medida disponible', 'que tan grande',
                'talla pequeña', 'talla mediana', 'talla grande',
                'numero de pie', 'medida de cintura', 'largo de pantalon'
            ],
            'disponibilidad': [
                'hay existencia', 'esta en inventario', 'cuando llega',
                'agotado', 'sin stock', 'disponible ahora',
                'pueden conseguir', 'cuando estara listo', 'reservar',
                'apartado', 'pedido especial', 'fuera de stock'
            ],
            'color': [
                'que tonos hay', 'viene en otro color', 'color disponible',
                'variedad de colores', 'colorido', 'tonalidad',
                'matiz', 'en que color viene', 'colores fashion',
                'color de moda', 'combinacion de colores', 'colores neutros'
            ],
            'general': [
                'informacion general', 'ayuda por favor', 'necesito asistencia',
                'como funciona', 'donde comprar', 'horario de atencion',
                'ubicacion tienda', 'formas de pago', 'politica devolucion',
                'garantia producto', 'servicio cliente', 'contactar'
            ]
        }
        
        # Agregar expansiones
        for categoria, frases in expansions.items():
            for frase in frases:
                expanded_data.append({
                    'pregunta': self.preprocess_text(frase),
                    'categoria': categoria
                })
        
        return pd.DataFrame(expanded_data)
    
    def create_pipeline(self):
        """Crea el pipeline de clasificación"""
        # Usar TfidfVectorizer en lugar de CountVectorizer para mejor rendimiento
        vectorizer = TfidfVectorizer(
            lowercase=True,
            stop_words=None,  # No removemos stop words en español
            ngram_range=(1, 2),  # Usar unigramas y bigramas
            max_features=1000,   # Limitar características
            min_df=1,           # Mínima frecuencia
            max_df=0.8          # Máxima frecuencia
        )
        
        # Naive Bayes con suavizado
        classifier = MultinomialNB(alpha=0.1)
        
        # Crear pipeline
        self.pipeline = Pipeline([
            ('vectorizer', vectorizer),
            ('classifier', classifier)
        ])
        
        return self.pipeline
    
    def train(self, training_file="preguntas_entrenamiento.csv"):
        """Entrena el modelo de clasificación"""
        print("🤖 Iniciando entrenamiento del clasificador de preguntas...")
        
        # Cargar datos de entrenamiento
        try:
            df_preguntas = pd.read_csv(DATA_DIR / training_file)
            print(f"📊 Cargadas {len(df_preguntas)} preguntas base")
        except FileNotFoundError:
            print(f"❌ No se encontró el archivo {training_file}")
            return False
        
        # Expandir datos de entrenamiento
        df_expanded = self.expand_training_data(df_preguntas)
        print(f"📈 Dataset expandido a {len(df_expanded)} ejemplos")
        
        # Preparar datos
        X = df_expanded['pregunta'].values
        y = df_expanded['categoria'].values
        
        # Dividir en entrenamiento y prueba
        X_train, X_test, y_train, y_test = train_test_split(
            X, y, test_size=0.2, random_state=42, stratify=y
        )
        
        # Crear y entrenar pipeline
        self.create_pipeline()
        self.pipeline.fit(X_train, y_train)
        
        # Evaluar modelo
        y_pred = self.pipeline.predict(X_test)
        accuracy = accuracy_score(y_test, y_pred)
        
        print(f"🎯 Precisión del modelo: {accuracy:.3f}")
        
        # Validación cruzada
        cv_scores = cross_val_score(self.pipeline, X, y, cv=5)
        print(f"📊 Validación cruzada: {cv_scores.mean():.3f} (+/- {cv_scores.std() * 2:.3f})")
        
        # Reporte detallado
        print("\n📋 Reporte de clasificación:")
        print(classification_report(y_test, y_pred))
        
        # Guardar modelo
        self.save_model()
        
        # Probar con ejemplos
        self.test_examples()
        
        return True
    
    def save_model(self):
        """Guarda el modelo entrenado"""
        try:
            joblib.dump(self.pipeline, self.model_path)
            print(f"💾 Modelo guardado en: {self.model_path}")
            return True
        except Exception as e:
            print(f"❌ Error guardando modelo: {e}")
            return False
    
    def load_model(self):
        """Carga el modelo previamente entrenado"""
        try:
            self.pipeline = joblib.load(self.model_path)
            print(f"✅ Modelo cargado desde: {self.model_path}")
            return True
        except FileNotFoundError:
            print(f"❌ No se encontró modelo en: {self.model_path}")
            return False
        except Exception as e:
            print(f"❌ Error cargando modelo: {e}")
            return False
    
    def predict(self, question):
        """Predice la categoría de una pregunta"""
        if self.pipeline is None:
            if not self.load_model():
                return "general"  # Categoría por defecto
        
        try:
            # Preprocesar pregunta
            processed_question = self.preprocess_text(question)
            
            # Predecir categoría
            prediction = self.pipeline.predict([processed_question])[0]
            
            # Obtener probabilidades
            probabilities = self.pipeline.predict_proba([processed_question])[0]
            confidence = max(probabilities)
            
            # Si la confianza es baja, devolver 'general'
            if confidence < 0.3:
                return "general"
            
            return prediction
            
        except Exception as e:
            print(f"❌ Error en predicción: {e}")
            return "general"
    
    def get_prediction_with_confidence(self, question):
        """Obtiene predicción con nivel de confianza"""
        if self.pipeline is None:
            if not self.load_model():
                return "general", 0.0
        
        try:
            processed_question = self.preprocess_text(question)
            prediction = self.pipeline.predict([processed_question])[0]
            probabilities = self.pipeline.predict_proba([processed_question])[0]
            confidence = max(probabilities)
            
            return prediction, confidence
            
        except Exception as e:
            print(f"❌ Error en predicción con confianza: {e}")
            return "general", 0.0
    
    def test_examples(self):
        """Prueba el modelo con ejemplos específicos"""
        test_questions = [
            "¿Cuánto cuesta esta camiseta?",
            "¿Tienen talla M disponible?",
            "¿Hay stock de este producto?",
            "¿En qué colores viene?",
            "¿Cuáles son sus horarios?",
            "¿Está disponible en rojo talla L?",
            "¿Qué precio tiene el jean azul?",
            "¿Tienen existencias?",
            "Necesito ayuda",
            "¿Viene en negro?"
        ]
        
        print("\n🧪 PRUEBAS DEL MODELO")
        print("=" * 50)
        
        for question in test_questions:
            prediction, confidence = self.get_prediction_with_confidence(question)
            print(f"❓ '{question}'")
            print(f"   🎯 Categoría: {prediction} (confianza: {confidence:.2f})")
            print()

def main():
    """Función principal para entrenar el modelo"""
    print("🚀 Entrenador de Clasificador de Preguntas - Fashion Store")
    print("=" * 60)
    
    # Crear directorio de modelos si no existe
    MODELS_DIR.mkdir(exist_ok=True)
    
    # Inicializar y entrenar clasificador
    classifier = QuestionClassifier()
    
    # Entrenar modelo
    success = classifier.train()
    
    if success:
        print("\n✅ ¡Entrenamiento completado exitosamente!")
        print(f"📁 Modelo guardado en: {MODELS_DIR}")
    else:
        print("\n❌ Error durante el entrenamiento")

if __name__ == "__main__":
    main()