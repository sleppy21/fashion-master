#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script para probar respuestas del bot de Fashion Store
Permite hacer pruebas rápidas del sistema de IA sin iniciar el servidor completo
"""

import sys
import os
from pathlib import Path

# Agregar paths necesarios
current_dir = Path(__file__).resolve().parent.parent
sys.path.insert(0, str(current_dir))

def test_bot_responses():
    """Prueba las respuestas del bot con preguntas variadas"""
    print("🤖 PRUEBAS DEL BOT FASHION STORE")
    print("=" * 60)
    
    try:
        # Importar sistema de IA
        from src.services.intelligent_response import IntelligentResponseSystem
        
        # Inicializar sistema
        ai_system = IntelligentResponseSystem()
        
        # Cargar datos
        print("📊 Cargando datos...")
        if not ai_system.load_data():
            print("❌ Error cargando datos")
            return False
        
        # Cargar clasificador
        print("🤖 Cargando clasificador...")
        ai_system.load_classifier()
        
        # Preguntas de prueba categorizadas
        test_cases = {
            "Precios": [
                "¿Cuánto cuesta la camiseta Premium Line?",
                "¿Qué precio tienen los jeans?",
                "¿Hay descuentos en pantalones?",
                "¿Está en oferta el blazer?",
                "¿Cuánto sale el polo azul?"
            ],
            "Tallas": [
                "¿Tienen talla M en camisetas?",
                "¿Qué tallas hay disponibles en jeans?",
                "¿Hay talla XL en stock?",
                "¿Tienen número 38 en zapatos?",
                "¿Qué tallas manejan en pantalones?"
            ],
            "Disponibilidad": [
                "¿Tienen en stock camisetas negras?",
                "¿Está disponible el polo azul?",
                "¿Hay existencias de jeans?",
                "¿Cuándo llega más inventario?",
                "¿Hay en tienda vestidos elegantes?"
            ],
            "Colores": [
                "¿Qué colores tienen en camisetas?",
                "¿Viene en azul el blazer?",
                "¿Hay polos en color rojo?",
                "¿Qué tonos manejan en pantalones?",
                "¿Tienen jeans en negro?"
            ],
            "General": [
                "¿Cuáles son sus horarios?",
                "¿Dónde están ubicados?",
                "¿Cómo puedo hacer un cambio?",
                "¿Qué métodos de pago aceptan?",
                "¿Hacen envíos a domicilio?"
            ]
        }
        
        # Ejecutar pruebas
        for categoria, preguntas in test_cases.items():
            print(f"\n📋 CATEGORÍA: {categoria.upper()}")
            print("-" * 50)
            
            for i, pregunta in enumerate(preguntas, 1):
                print(f"\n{i}. ❓ {pregunta}")
                print("   " + "~" * 40)
                
                try:
                    respuesta = ai_system.generate_response(pregunta)
                    print(f"   🤖 {respuesta}")
                except Exception as e:
                    print(f"   ❌ Error: {e}")
                
                print()
        
        print("\n✅ Pruebas completadas exitosamente!")
        return True
        
    except ImportError as e:
        print(f"❌ Error importando módulos: {e}")
        print("💡 Asegúrate de que las dependencias estén instaladas")
        return False
    except Exception as e:
        print(f"❌ Error durante las pruebas: {e}")
        return False

def interactive_test():
    """Modo interactivo para probar preguntas en tiempo real"""
    print("\n🔄 MODO INTERACTIVO")
    print("=" * 40)
    print("Escribe 'salir' para terminar")
    
    try:
        from src.services.intelligent_response import IntelligentResponseSystem
        
        ai_system = IntelligentResponseSystem()
        
        if not ai_system.load_data():
            print("❌ Error cargando datos")
            return
        
        ai_system.load_classifier()
        
        while True:
            print("\n" + "-" * 40)
            pregunta = input("❓ Tu pregunta: ").strip()
            
            if pregunta.lower() in ['salir', 'exit', 'quit']:
                print("👋 ¡Hasta luego!")
                break
            
            if not pregunta:
                continue
            
            try:
                respuesta = ai_system.generate_response(pregunta)
                print(f"\n🤖 Respuesta:")
                print(respuesta)
            except Exception as e:
                print(f"❌ Error: {e}")
    
    except Exception as e:
        print(f"❌ Error en modo interactivo: {e}")

def benchmark_performance():
    """Prueba el rendimiento del sistema"""
    print("\n⚡ BENCHMARK DE RENDIMIENTO")
    print("=" * 40)
    
    try:
        import time
        from src.services.intelligent_response import IntelligentResponseSystem
        
        ai_system = IntelligentResponseSystem()
        
        if not ai_system.load_data():
            print("❌ Error cargando datos")
            return
        
        ai_system.load_classifier()
        
        test_questions = [
            "¿Cuánto cuesta la camiseta?",
            "¿Tienen talla M?",
            "¿Hay stock disponible?",
            "¿Qué colores tienen?",
            "¿Cuáles son sus horarios?"
        ] * 10  # 50 preguntas total
        
        print(f"🧪 Probando con {len(test_questions)} preguntas...")
        
        start_time = time.time()
        
        for i, question in enumerate(test_questions, 1):
            try:
                ai_system.generate_response(question)
                if i % 10 == 0:
                    print(f"   Procesadas: {i}/{len(test_questions)}")
            except Exception as e:
                print(f"   ❌ Error en pregunta {i}: {e}")
        
        end_time = time.time()
        total_time = end_time - start_time
        avg_time = total_time / len(test_questions)
        
        print(f"\n📊 RESULTADOS:")
        print(f"   ⏱️ Tiempo total: {total_time:.2f} segundos")
        print(f"   📈 Tiempo promedio por pregunta: {avg_time:.3f} segundos")
        print(f"   🚀 Preguntas por segundo: {len(test_questions)/total_time:.2f}")
        
    except Exception as e:
        print(f"❌ Error en benchmark: {e}")

def main():
    """Función principal del script de pruebas"""
    print("🚀 TESTING SUITE - FASHION STORE BOT")
    print("=" * 60)
    
    options = {
        "1": ("Pruebas Automáticas", test_bot_responses),
        "2": ("Modo Interactivo", interactive_test),
        "3": ("Benchmark de Rendimiento", benchmark_performance),
        "4": ("Todas las Pruebas", lambda: [test_bot_responses(), benchmark_performance()])
    }
    
    print("\n📋 Opciones disponibles:")
    for key, (name, _) in options.items():
        print(f"   {key}. {name}")
    
    while True:
        choice = input("\n🔹 Selecciona una opción (1-4) o 'q' para salir: ").strip()
        
        if choice.lower() in ['q', 'quit', 'salir']:
            print("👋 ¡Hasta luego!")
            break
        
        if choice in options:
            name, func = options[choice]
            print(f"\n🚀 Ejecutando: {name}")
            print("=" * 60)
            func()
        else:
            print("❌ Opción no válida. Intenta de nuevo.")

if __name__ == "__main__":
    main()