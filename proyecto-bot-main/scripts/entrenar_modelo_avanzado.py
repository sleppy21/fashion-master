#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script de entrenamiento mejorado para el modelo del bot
Utiliza los nuevos datos expandidos y el sistema avanzado
"""

import pandas as pd
import numpy as np
import json
import pickle
from pathlib import Path
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.model_selection import train_test_split
from sklearn.naive_bayes import MultinomialNB
from sklearn.ensemble import RandomForestClassifier
from sklearn.svm import SVC
from sklearn.metrics import classification_report, accuracy_score
from sklearn.pipeline import Pipeline
import joblib
import logging

# Configurar logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Rutas
BASE_DIR = Path(__file__).resolve().parent.parent
DATA_DIR = BASE_DIR / "data"
MODELS_DIR = BASE_DIR / "models"

def load_training_data():
    """Carga datos de entrenamiento expandidos"""
    try:
        # Cargar datos expandidos
        df_expandido = pd.read_csv(DATA_DIR / "preguntas_entrenamiento_expandido.csv")
        logger.info(f"✅ Cargados {len(df_expandido)} ejemplos expandidos")
        
        # Cargar datos originales si existen
        df_original = None
        if (DATA_DIR / "preguntas_entrenamiento.csv").exists():
            df_original = pd.read_csv(DATA_DIR / "preguntas_entrenamiento.csv")
            logger.info(f"✅ Cargados {len(df_original)} ejemplos originales")
            
            # Combinar datasets
            df_combined = pd.concat([df_original, df_expandido], ignore_index=True)
        else:
            df_combined = df_expandido
        
        # Limpiar datos
        df_combined = df_combined.dropna(subset=['pregunta', 'categoria'])
        df_combined['pregunta'] = df_combined['pregunta'].str.strip()
        df_combined['categoria'] = df_combined['categoria'].str.strip()
        
        logger.info(f"✅ Dataset final: {len(df_combined)} ejemplos en {df_combined['categoria'].nunique()} categorías")
        logger.info(f"Distribución por categoría:\n{df_combined['categoria'].value_counts()}")
        
        return df_combined
        
    except Exception as e:
        logger.error(f"❌ Error cargando datos: {e}")
        return None

def create_enhanced_features(texts, vectorizer=None, fit=True):
    """Crea características mejoradas para el entrenamiento"""
    
    if vectorizer is None:
        vectorizer = TfidfVectorizer(
            lowercase=True,
            stop_words=None,  # No remover stop words en español
            ngram_range=(1, 3),  # Incluir trigramas
            max_features=10000,
            sublinear_tf=True,
            min_df=2,
            max_df=0.95
        )
    
    if fit:
        # Ajustar vectorizer y transformar
        tfidf_features = vectorizer.fit_transform(texts)
        logger.info(f"✅ Características TF-IDF creadas: {tfidf_features.shape}")
    else:
        # Solo transformar
        tfidf_features = vectorizer.transform(texts)
    
    return tfidf_features, vectorizer

def add_linguistic_features(df):
    """Agrega características lingüísticas adicionales"""
    df = df.copy()
    
    # Longitud de la pregunta
    df['pregunta_longitud'] = df['pregunta'].str.len()
    df['pregunta_palabras'] = df['pregunta'].str.split().str.len()
    
    # Presencia de signos de interrogación
    df['tiene_interrogacion'] = df['pregunta'].str.contains(r'[?¿]').astype(int)
    
    # Presencia de números
    df['tiene_numeros'] = df['pregunta'].str.contains(r'\d').astype(int)
    
    # Presencia de palabras clave por categoría
    keywords_by_category = {
        'precio': ['precio', 'costo', 'vale', 'cuesta', 'caro', 'barato', 'oferta'],
        'talla': ['talla', 'medida', 'numero', 'size', 'grande', 'pequeño'],
        'disponibilidad': ['stock', 'hay', 'tienen', 'disponible', 'agotado'],
        'color': ['color', 'negro', 'blanco', 'azul', 'rojo', 'verde'],
        'envio': ['envio', 'delivery', 'entrega', 'domicilio'],
        'general': ['hola', 'ayuda', 'gracias', 'informacion']
    }
    
    for categoria, keywords in keywords_by_category.items():
        col_name = f'keywords_{categoria}'
        df[col_name] = df['pregunta'].str.lower().apply(
            lambda x: sum(1 for kw in keywords if kw in x)
        )
    
    return df

def train_multiple_models(X_train, X_test, y_train, y_test):
    """Entrena múltiples modelos y selecciona el mejor"""
    
    models = {
        'naive_bayes': MultinomialNB(alpha=0.1),
        'random_forest': RandomForestClassifier(
            n_estimators=100, 
            max_depth=20,
            min_samples_split=5,
            random_state=42
        ),
        'svm': SVC(
            kernel='linear',
            C=1.0,
            probability=True,
            random_state=42
        )
    }
    
    results = {}
    trained_models = {}
    
    for name, model in models.items():
        logger.info(f"🔄 Entrenando modelo: {name}")
        
        try:
            # Entrenar modelo
            model.fit(X_train, y_train)
            
            # Evaluar en conjunto de prueba
            y_pred = model.predict(X_test)
            accuracy = accuracy_score(y_test, y_pred)
            
            results[name] = {
                'accuracy': accuracy,
                'model': model,
                'predictions': y_pred
            }
            
            trained_models[name] = model
            
            logger.info(f"✅ {name}: Accuracy = {accuracy:.4f}")
            
            # Imprimir reporte detallado para el mejor modelo hasta ahora
            if name == 'svm':  # SVM suele ser el mejor para clasificación de texto
                logger.info(f"\n📊 Reporte detallado para {name}:")
                logger.info(f"\n{classification_report(y_test, y_pred)}")
            
        except Exception as e:
            logger.error(f"❌ Error entrenando {name}: {e}")
            results[name] = {'accuracy': 0, 'model': None}
    
    # Seleccionar mejor modelo
    best_model_name = max(results.keys(), key=lambda x: results[x]['accuracy'])
    best_model = results[best_model_name]['model']
    best_accuracy = results[best_model_name]['accuracy']
    
    logger.info(f"🏆 Mejor modelo: {best_model_name} (Accuracy: {best_accuracy:.4f})")
    
    return best_model, best_model_name, results

def save_model_and_vectorizer(model, vectorizer, model_name, results):
    """Guarda el modelo entrenado y el vectorizer"""
    
    # Crear directorio de modelos si no existe
    MODELS_DIR.mkdir(exist_ok=True)
    
    try:
        # Guardar modelo principal
        model_path = MODELS_DIR / "question_classifier.joblib"
        joblib.dump(model, model_path)
        logger.info(f"✅ Modelo guardado en: {model_path}")
        
        # Guardar vectorizer
        vectorizer_path = MODELS_DIR / "tfidf_vectorizer.joblib"
        joblib.dump(vectorizer, vectorizer_path)
        logger.info(f"✅ Vectorizer guardado en: {vectorizer_path}")
        
        # Guardar metadatos del modelo
        metadata = {
            'model_type': model_name,
            'accuracy': results[model_name]['accuracy'],
            'all_results': {name: res['accuracy'] for name, res in results.items()},
            'feature_count': vectorizer.max_features,
            'ngram_range': vectorizer.ngram_range,
            'training_date': pd.Timestamp.now().isoformat()
        }
        
        metadata_path = MODELS_DIR / "model_metadata.json"
        with open(metadata_path, 'w', encoding='utf-8') as f:
            json.dump(metadata, f, indent=2, ensure_ascii=False)
        
        logger.info(f"✅ Metadatos guardados en: {metadata_path}")
        
        return True
        
    except Exception as e:
        logger.error(f"❌ Error guardando modelo: {e}")
        return False

def evaluate_model_performance(model, X_test, y_test, categories):
    """Evalúa el rendimiento del modelo en detalle"""
    
    logger.info("📊 EVALUACIÓN DETALLADA DEL MODELO")
    logger.info("=" * 60)
    
    # Predicciones
    y_pred = model.predict(X_test)
    y_pred_proba = model.predict_proba(X_test) if hasattr(model, 'predict_proba') else None
    
    # Accuracy general
    accuracy = accuracy_score(y_test, y_pred)
    logger.info(f"🎯 Accuracy general: {accuracy:.4f}")
    
    # Reporte por categoría
    logger.info(f"\n📋 Reporte por categoría:")
    logger.info(f"\n{classification_report(y_test, y_pred)}")
    
    # Análisis de confianza (si disponible)
    if y_pred_proba is not None:
        confidence_scores = np.max(y_pred_proba, axis=1)
        avg_confidence = np.mean(confidence_scores)
        logger.info(f"\n🎲 Confianza promedio: {avg_confidence:.4f}")
        
        # Distribución de confianza
        high_conf = np.sum(confidence_scores > 0.8)
        med_conf = np.sum((confidence_scores > 0.5) & (confidence_scores <= 0.8))
        low_conf = np.sum(confidence_scores <= 0.5)
        
        logger.info(f"📊 Distribución de confianza:")
        logger.info(f"   Alta (>0.8): {high_conf} ({high_conf/len(confidence_scores)*100:.1f}%)")
        logger.info(f"   Media (0.5-0.8): {med_conf} ({med_conf/len(confidence_scores)*100:.1f}%)")
        logger.info(f"   Baja (≤0.5): {low_conf} ({low_conf/len(confidence_scores)*100:.1f}%)")
    
    return accuracy

def test_model_with_examples(model, vectorizer):
    """Prueba el modelo con ejemplos específicos"""
    
    test_examples = [
        "Cuanto cuesta la camiseta negra?",
        "Tienen talla M en jeans?",
        "Hay stock de polos azules?",
        "En que colores viene el blazer?",
        "Como puedo hacer una devolucion?",
        "Cual es su horario de atencion?",
        "Hacen envios a domicilio?",
        "Hola, necesito ayuda",
        "Que metodos de pago aceptan?",
        "Busco algo economico para regalo"
    ]
    
    logger.info("🧪 PRUEBAS CON EJEMPLOS ESPECÍFICOS")
    logger.info("=" * 50)
    
    for example in test_examples:
        # Vectorizar ejemplo
        example_vector = vectorizer.transform([example])
        
        # Predecir
        prediction = model.predict(example_vector)[0]
        
        # Obtener probabilidades si están disponibles
        if hasattr(model, 'predict_proba'):
            probabilities = model.predict_proba(example_vector)[0]
            confidence = np.max(probabilities)
            logger.info(f"❓ '{example}' → {prediction} (confianza: {confidence:.3f})")
        else:
            logger.info(f"❓ '{example}' → {prediction}")

def main():
    """Función principal de entrenamiento"""
    logger.info("🚀 INICIANDO ENTRENAMIENTO DEL MODELO AVANZADO")
    logger.info("=" * 70)
    
    # 1. Cargar datos
    df = load_training_data()
    if df is None:
        logger.error("❌ No se pudieron cargar los datos")
        return False
    
    # 2. Agregar características lingüísticas
    df = add_linguistic_features(df)
    
    # 3. Preparar datos para entrenamiento
    X_text = df['pregunta'].values
    y = df['categoria'].values
    
    # 4. Dividir en entrenamiento y prueba
    X_text_train, X_text_test, y_train, y_test = train_test_split(
        X_text, y, test_size=0.2, random_state=42, stratify=y
    )
    
    logger.info(f"📊 División de datos:")
    logger.info(f"   Entrenamiento: {len(X_text_train)} ejemplos")
    logger.info(f"   Prueba: {len(X_text_test)} ejemplos")
    
    # 5. Crear características TF-IDF
    X_train_tfidf, vectorizer = create_enhanced_features(X_text_train, fit=True)
    X_test_tfidf, _ = create_enhanced_features(X_text_test, vectorizer, fit=False)
    
    # 6. Entrenar múltiples modelos
    best_model, best_model_name, results = train_multiple_models(
        X_train_tfidf, X_test_tfidf, y_train, y_test
    )
    
    if best_model is None:
        logger.error("❌ No se pudo entrenar ningún modelo")
        return False
    
    # 7. Evaluación detallada
    categories = np.unique(y)
    accuracy = evaluate_model_performance(best_model, X_test_tfidf, y_test, categories)
    
    # 8. Guardar modelo y vectorizer
    success = save_model_and_vectorizer(best_model, vectorizer, best_model_name, results)
    
    if not success:
        logger.error("❌ Error guardando el modelo")
        return False
    
    # 9. Pruebas con ejemplos
    test_model_with_examples(best_model, vectorizer)
    
    logger.info("\n🎉 ENTRENAMIENTO COMPLETADO EXITOSAMENTE")
    logger.info(f"✅ Mejor modelo: {best_model_name}")
    logger.info(f"✅ Accuracy final: {accuracy:.4f}")
    logger.info(f"✅ Archivos guardados en: {MODELS_DIR}")
    
    return True

if __name__ == "__main__":
    success = main()
    if success:
        print("\n🚀 El modelo ha sido entrenado y está listo para uso!")
    else:
        print("\n❌ Error durante el entrenamiento. Revisa los logs.")