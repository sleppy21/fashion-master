import re
import random
import unicodedata
from typing import Dict, List, Optional, Any, Tuple

# Dependencias: scikit-learn
# pip install scikit-learn
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.linear_model import LogisticRegression
from sklearn.pipeline import make_pipeline


class IntentProcessor:
    """
    Procesador híbrido de intenciones:
    - Preprocesado (normalizar, quitar tildes/puntuacion)
    - Clasificador TF-IDF + LogisticRegression (entrena con los patrones)
    - Fallback por coincidencia exacta/regex si la confianza es baja
    """

    INTENCIONES = {
        "saludo": {
            "patrones": [
                "hola",
                "buenos dias",
                "buenas tardes",
                "buenas noches",
                "hi",
                "hello",
                "que tal",
                "saludos",
                "hey",
                "buenas",
            ],
            "respuestas": [
                "Hola! Soy tu asistente de Fashion Store. ¿En qué puedo ayudarte hoy?",
                "Bienvenido/a! ¿Qué estás buscando el día de hoy?",
                "Hola! Me alegra verte. ¿Necesitas ayuda con algo en particular?",
            ],
        },
        "despedida": {
            "patrones": [
                "adios",
                "chau",
                "hasta luego",
                "bye",
                "gracias",
                "nos vemos",
                "hasta pronto",
                "goodbye",
            ],
            "respuestas": [
                "Hasta pronto! Fue un placer ayudarte. Vuelve cuando quieras!",
                "Gracias por tu visita! Si necesitas algo más, aquí estaré.",
                "Que tengas un excelente día! Recuerda que estamos 24/7 para ti.",
            ],
        },
        "precio": {
            "patrones": [
                "precio",
                "cuanto cuesta",
                "valor",
                "costo",
                "cuanto vale",
                "cuanto es",
                "precios",
            ],
            "respuestas": [
                "Te puedo ayudar con precios! ¿Qué producto te interesa?",
                "Claro! Dime qué artículo quieres consultar el precio.",
                "Perfecto! ¿Cuál es el producto que quieres conocer su precio?",
            ],
        },
        "stock": {
            "patrones": [
                "hay",
                "tienen",
                "disponible",
                "stock",
                "queda",
                "talla",
                "disponibilidad",
                "existe",
            ],
            "respuestas": [
                "Te ayudo a verificar disponibilidad! ¿Qué producto buscas?",
                "Claro! Dime qué artículo y talla necesitas consultar.",
                "Perfecto! ¿Cuál es el producto que quieres verificar?",
            ],
        },
        "ubicacion": {
            "patrones": [
                "donde",
                "ubicacion",
                "direccion",
                "tienda",
                "local",
                "encuentro",
                "como llego",
            ],
            "respuestas": [
                "Estamos en Av. Las Flores 123, Centro Comercial Plaza Mayor, Local 45, Lima.",
                "Nuestra dirección es Av. Las Flores 123, en Plaza Mayor. Tenemos estacionamiento gratuito!",
                "Nos encuentras en Plaza Mayor, Local 45. Horario de 10:00 AM a 9:00 PM.",
            ],
        },
        "ofertas": {
            "patrones": [
                "ofertas",
                "descuentos",
                "promociones",
                "oferta",
                "descuento",
                "promocion",
                "rebajas",
                "liquidacion",
            ],
            "respuestas": [
                "Tenemos excelentes ofertas! Te muestro las promociones actuales.",
                "Perfecto! Aquí tienes nuestras mejores ofertas del momento.",
                "Genial! Te comparto las promociones disponibles ahora.",
            ],
        },
    }

    def __init__(self, min_confidence: float = 0.60):
        self.min_confidence = min_confidence
        self._vectorizer_clf = None  # pipeline
        self._build_and_train()

    # -------------------------
    # Preprocesado / utilidades
    # -------------------------
    @staticmethod
    def _normalize(text: str) -> str:
        """minusculas, quitar tildes, quitar puntuacion redundante, normalizar espacios"""
        text = text.lower().strip()
        # remove accents
        text = unicodedata.normalize("NFD", text)
        text = "".join(ch for ch in text if unicodedata.category(ch) != "Mn")
        # replace non-word characters with space (keep letras y numeros)
        text = re.sub(r"[^\w\s]", " ", text, flags=re.UNICODE)
        # collapse spaces
        text = re.sub(r"\s+", " ", text).strip()
        return text

    # -------------------------
    # Entrenamiento (simple)
    # -------------------------
    def _build_and_train(self) -> None:
        """Construye dataset desde INTENCIONES y entrena un clasificador TF-IDF + LR"""
        texts: List[str] = []
        labels: List[str] = []

        for intent, data in self.INTENCIONES.items():
            for pattern in data.get("patrones", []):
                texts.append(self._normalize(pattern))
                labels.append(intent)

        # Si no hay datos suficientes (muy improbable aquí), dejar pipeline en None
        if not texts:
            self._vectorizer_clf = None
            return

        # Pipeline TF-IDF (char/word mix) + LogisticRegression
        vectorizer = TfidfVectorizer(analyzer="word", ngram_range=(1, 2), min_df=1)
        clf = LogisticRegression(max_iter=200, solver="liblinear")
        self._vectorizer_clf = make_pipeline(vectorizer, clf)
        self._vectorizer_clf.fit(texts, labels)

    # -------------------------
    # Detección de intención
    # -------------------------
    def detectar_intencion(self, texto: str) -> Tuple[Optional[str], float]:
        """
        Devuelve (intencion, confidence)
        - Intención por ML si supera min_confidence
        - Si no, fallback por matching de patrones (substrings / regex)
        """
        if not texto or not texto.strip():
            return None, 0.0

        norm = self._normalize(texto)

        # Intento ML si pipeline existe
        if self._vectorizer_clf:
            probs = self._vectorizer_clf.predict_proba([norm])[0]
            labels = self._vectorizer_clf.named_steps[
                list(self._vectorizer_clf.named_steps.keys())[-1]
            ].classes_
            # clase con mayor probabilidad
            best_idx = int(probs.argmax())
            best_label = labels[best_idx]
            best_prob = float(probs[best_idx])

            if best_prob >= self.min_confidence:
                return best_label, best_prob

        # Fallback: buscar patrones por substring o palabras completas
        for intent, data in self.INTENCIONES.items():
            for pattern in data.get("patrones", []):
                p_norm = self._normalize(pattern)
                # buscar palabra completa con regex para evitar coincidencias parciales extrañas
                if re.search(rf"\b{re.escape(p_norm)}\b", norm):
                    return intent, 0.65  # confianza heurística razonable
                # substring simple como respaldo
                if p_norm in norm:
                    return intent, 0.55

        return None, 0.0

    # -------------------------
    # Obtener respuesta
    # -------------------------
    @staticmethod
    def obtener_respuesta(intencion: Optional[str]) -> str:
        if intencion and intencion in IntentProcessor.INTENCIONES:
            respuestas = IntentProcessor.INTENCIONES[intencion]["respuestas"]
            return random.choice(respuestas)
        return "Hola! ¿Cómo puedo ayudarte hoy? Puedo asistirte con productos, precios, ubicación y más."

    # -------------------------
    # Método de alto nivel
    # -------------------------
    def procesar_mensaje(self, texto: str) -> Dict[str, Any]:
        intent, confidence = self.detectar_intencion(texto)
        respuesta = self.obtener_respuesta(intent)
        return {
            "answer": respuesta,
            "intent": intent or "general",
            "confidence": round(float(confidence), 3),
        }


# -------------------------
# Ejemplo rápido de uso
# -------------------------
if __name__ == "__main__":
    ip = IntentProcessor(min_confidence=0.6)
    ejemplos = [
        "Hola, buenas!",
        "¿Cuánto cuesta la chaqueta roja?",
        "¿Tienen talla S?",
        "¿Dónde están ubicados?",
        "Gracias, adios",
        "¿Hay descuentos ahora?",
        "buscar algo aleatorio que no matchee"
    ]
    for e in ejemplos:
        print(e, "->", ip.procesar_mensaje(e))
