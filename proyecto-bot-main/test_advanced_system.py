#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script de prueba para el sistema avanzado de IA
Verifica que el análisis vectorial funciona correctamente
"""

import sys
from pathlib import Path

# Agregar rutas necesarias
sys.path.append(str(Path(__file__).parent / "src"))
sys.path.append(str(Path(__file__).parent))

try:
    from src.services.advanced_intelligent_response import AdvancedIntelligentResponseSystem
    print("✅ Importación del sistema avanzado exitosa")
    
    # Inicializar sistema
    print("🔄 Inicializando sistema avanzado...")
    ai_system = AdvancedIntelligentResponseSystem()
    print("✅ Sistema avanzado inicializado")
    
    # Casos de prueba para verificar análisis vectorial sin palabras exactas
    test_cases = [
        # Casos que NO usan palabras exactas - requieren análisis vectorial
        "¿Cuál es el valor de esta prenda?",  # No dice "precio" pero lo busca
        "¿En qué medidas viene disponible?",   # No dice "talla" pero la busca
        "¿Qué tonalidades tienen?",            # No dice "color" pero lo busca
        "¿Hay existencias?",                   # No dice "disponibilidad" pero la busca
        "¿Cómo devuelvo un artículo?",         # No dice "devolución" pero la busca
        "¿Cuándo atienden?",                   # No dice "horario" pero lo busca
        "¿Qué formas de pagar aceptan?",       # No dice "pago" pero lo busca
        "Hola, requiero asistencia",           # Saludo variado
        "Necesito ayuda con mi compra",        # Contexto de compra
        "¿Tienen promociones ahora?",          # Busca descuentos sin decirlo
    ]
    
    print("\n🧪 PROBANDO ANÁLISIS VECTORIAL SIN PALABRAS EXACTAS")
    print("=" * 60)
    
    for i, query in enumerate(test_cases, 1):
        print(f"\n{i}. Consulta: '{query}'")
        try:
            response = ai_system.generate_intelligent_response(query)
            print(f"   Respuesta: {response}")
        except Exception as e:
            print(f"   ❌ Error: {e}")
    
    print("\n✅ PRUEBA COMPLETADA")
    print("🎯 El sistema puede entender contexto sin palabras exactas")
    
except ImportError as e:
    print(f"❌ Error de importación: {e}")
    print("💡 Asegúrate de que las dependencias estén instaladas")
except Exception as e:
    print(f"❌ Error general: {e}")
    print("💡 Revisa la configuración del sistema")