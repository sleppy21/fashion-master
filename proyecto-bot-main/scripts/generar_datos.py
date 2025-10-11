#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Generador de datos de productos para Fashion Store
Crea un dataset expandido con productos, tallas, colores, precios y disponibilidad
"""

import pandas as pd
import numpy as np
import json
import random
from pathlib import Path

# Configuración de rutas
BASE_DIR = Path(__file__).resolve().parent.parent
DATA_DIR = BASE_DIR / "data"

class ProductDataGenerator:
    """Generador de datos de productos para tienda de moda"""
    
    def __init__(self):
        self.categorias = [
            "Camisetas", "Polos", "Camisas", "Pantalones", "Jeans", 
            "Shorts", "Sacos", "Blazers", "Chaquetas", "Vestidos",
            "Faldas", "Blusas", "Suéteres", "Hoodies", "Zapatillas",
            "Zapatos", "Sandalias", "Botas", "Bolsos", "Carteras",
            "Cinturones", "Gorros", "Bufandas", "Accesorios"
        ]
        
        self.tipos_producto = {
            "Camisetas": ["Camiseta Basic", "Camiseta Premium", "Camiseta Estampada", "Camiseta Deportiva"],
            "Polos": ["Polo Clásico", "Polo Piqué", "Polo Sport", "Polo Elegante"],
            "Camisas": ["Camisa Formal", "Camisa Casual", "Camisa Oxford", "Camisa Lino"],
            "Pantalones": ["Pantalón Formal", "Pantalón Casual", "Pantalón Deportivo", "Pantalón Chino"],
            "Jeans": ["Jeans Slim", "Jeans Regular", "Jeans Skinny", "Jeans Relaxed"],
            "Vestidos": ["Vestido Casual", "Vestido Elegante", "Vestido Fiesta", "Vestido Verano"],
            "Zapatillas": ["Zapatillas Deportivas", "Zapatillas Casual", "Zapatillas Running"],
            "Zapatos": ["Zapatos Formales", "Zapatos Casual", "Zapatos Oxford"],
            "Bolsos": ["Bolso de Mano", "Bolso Bandolera", "Bolso Tote", "Bolso Clutch"]
        }
        
        self.tallas_ropa = ["XS", "S", "M", "L", "XL", "XXL"]
        self.tallas_calzado = ["35", "36", "37", "38", "39", "40", "41", "42", "43", "44", "45"]
        self.tallas_pantalon = ["28", "30", "32", "34", "36", "38", "40", "42"]
        
        self.colores = [
            "Negro", "Blanco", "Gris", "Azul Marino", "Azul", "Rojo", "Verde",
            "Rosa", "Morado", "Amarillo", "Naranja", "Marrón", "Beige", "Crema",
            "Verde Oliva", "Azul Cielo", "Rosa Pastel", "Gris Oscuro", "Bordeaux"
        ]
        
        self.materiales = [
            "Algodón 100%", "Poliéster", "Algodón-Poliéster", "Lana", "Lino",
            "Seda", "Denim", "Cuero", "Ante", "Lycra", "Spandex", "Viscosa"
        ]
        
        self.marcas = [
            "Fashion Store", "Premium Line", "Classic Collection", "Modern Style",
            "Urban Wear", "Elegant Touch", "Sport Active", "Casual Comfort"
        ]
    
    def generar_precio(self, categoria):
        """Genera precios realistas según la categoría"""
        rangos_precio = {
            "Camisetas": (25, 80),
            "Polos": (35, 90),
            "Camisas": (45, 150),
            "Pantalones": (60, 180),
            "Jeans": (80, 200),
            "Vestidos": (70, 250),
            "Sacos": (150, 400),
            "Blazers": (180, 450),
            "Zapatillas": (90, 300),
            "Zapatos": (120, 350),
            "Bolsos": (40, 200),
            "Accesorios": (15, 80)
        }
        
        rango = rangos_precio.get(categoria, (30, 100))
        precio_base = random.uniform(rango[0], rango[1])
        # Redondear a .90 o .50
        precio_final = round(precio_base) - 0.10 if random.choice([True, False]) else round(precio_base) - 0.50
        return max(precio_final, 10.00)
    
    def obtener_tallas(self, categoria):
        """Obtiene tallas apropiadas según la categoría"""
        if categoria in ["Zapatillas", "Zapatos", "Sandalias", "Botas"]:
            return random.sample(self.tallas_calzado, random.randint(4, 8))
        elif categoria in ["Pantalones", "Jeans", "Shorts"]:
            return random.sample(self.tallas_pantalon, random.randint(3, 6))
        elif categoria in ["Bolsos", "Carteras", "Cinturones", "Accesorios", "Gorros", "Bufandas"]:
            return ["Único"]
        else:
            return random.sample(self.tallas_ropa, random.randint(3, 5))
    
    def generar_disponibilidad(self, tallas):
        """Genera disponibilidad por talla"""
        disponibilidad = {}
        for talla in tallas:
            # 80% de probabilidad de tener stock
            if random.random() < 0.8:
                stock = random.randint(1, 50)
            else:
                stock = 0
            disponibilidad[talla] = stock
        return disponibilidad
    
    def generar_productos(self, num_productos=200):
        """Genera dataset completo de productos"""
        productos = []
        
        for i in range(num_productos):
            categoria = random.choice(self.categorias)
            
            # Obtener tipo específico si existe
            if categoria in self.tipos_producto:
                tipo = random.choice(self.tipos_producto[categoria])
            else:
                tipo = categoria
            
            # Generar características del producto
            marca = random.choice(self.marcas)
            material = random.choice(self.materiales)
            colores = random.sample(self.colores, random.randint(1, 4))
            tallas = self.obtener_tallas(categoria)
            precio = self.generar_precio(categoria)
            
            # Generar disponibilidad por talla y color
            disponibilidad_detallada = {}
            stock_total = 0
            
            for color in colores:
                disponibilidad_detallada[color] = {}
                for talla in tallas:
                    stock = random.randint(0, 20) if random.random() < 0.85 else 0
                    disponibilidad_detallada[color][talla] = stock
                    stock_total += stock
            
            # Generar características adicionales
            descuento = random.randint(0, 50) if random.random() < 0.3 else 0
            precio_final = precio * (1 - descuento/100) if descuento > 0 else precio
            
            producto = {
                "id": f"FS{str(i+1).zfill(4)}",
                "nombre": f"{tipo} {marca}",
                "descripcion": f"{tipo} en {material}, diseño moderno y cómodo. Perfecto para uso diario.",
                "categoria": categoria,
                "marca": marca,
                "material": material,
                "precio_original": round(precio, 2),
                "descuento": descuento,
                "precio_final": round(precio_final, 2),
                "colores": colores,
                "tallas": tallas,
                "disponibilidad": disponibilidad_detallada,
                "stock_total": stock_total,
                "disponible": stock_total > 0,
                "destacado": random.random() < 0.2,
                "nuevo": random.random() < 0.15,
                "temporada": random.choice(["Primavera", "Verano", "Otoño", "Invierno", "Todo el año"]),
                "puntuacion": round(random.uniform(3.5, 5.0), 1),
                "num_resenas": random.randint(0, 150),
                "codigo_barras": f"78{random.randint(10000000000, 99999999999)}",
                "fecha_ingreso": f"2024-{random.randint(1, 12):02d}-{random.randint(1, 28):02d}"
            }
            
            productos.append(producto)
        
        return productos
    
    def generar_preguntas_entrenamiento(self):
        """Genera preguntas de ejemplo para entrenar el clasificador"""
        preguntas = []
        
        # Preguntas sobre precio
        preguntas_precio = [
            "¿Cuánto cuesta la camiseta negra?",
            "¿Qué precio tienen los jeans?",
            "¿Cuál es el precio del polo azul?",
            "¿Tienen descuentos en pantalones?",
            "¿Hay ofertas en vestidos?",
            "¿Cuánto sale el blazer gris?",
            "¿Qué tan caro es este producto?",
            "¿Está en oferta?",
            "¿Tienen precios especiales?",
            "¿Cuál es el costo de las zapatillas?"
        ]
        
        # Preguntas sobre tallas
        preguntas_talla = [
            "¿Tienen talla M en camisetas?",
            "¿Qué tallas hay disponibles?",
            "¿Hay talla XL en stock?",
            "¿Tienen mi talla?",
            "¿Qué tallas manejan?",
            "¿Hay talla 38 en zapatos?",
            "¿Tienen talla S disponible?",
            "¿Qué tallas tienen en pantalones?",
            "¿Hay talla grande?",
            "¿Tienen talla 42 en jeans?"
        ]
        
        # Preguntas sobre disponibilidad
        preguntas_disponibilidad = [
            "¿Tienen en stock?",
            "¿Está disponible?",
            "¿Hay existencias?",
            "¿Cuándo llega más stock?",
            "¿Tienen disponible en rojo?",
            "¿Hay en color negro?",
            "¿Tienen existencias del polo azul?",
            "¿Está agotado?",
            "¿Hay en tienda?",
            "¿Cuántos quedan?"
        ]
        
        # Preguntas sobre colores
        preguntas_color = [
            "¿Qué colores tienen?",
            "¿Viene en azul?",
            "¿Hay en color rojo?",
            "¿Qué colores manejan?",
            "¿Tienen en negro?",
            "¿Hay más colores disponibles?",
            "¿En qué colores viene?",
            "¿Tienen en blanco?",
            "¿Hay variedad de colores?",
            "¿Qué tonos tienen?"
        ]
        
        # Preguntas generales
        preguntas_general = [
            "¿Me puedes ayudar?",
            "Busco información",
            "¿Qué productos tienen?",
            "¿Cómo puedo comprar?",
            "Necesito ayuda",
            "¿Dónde están ubicados?",
            "¿Cuáles son sus horarios?",
            "¿Hacen envíos?",
            "¿Aceptan devoluciones?",
            "¿Cómo es el proceso de cambio?"
        ]
        
        # Crear dataset con etiquetas
        for pregunta in preguntas_precio:
            preguntas.append({"pregunta": pregunta, "categoria": "precio"})
        
        for pregunta in preguntas_talla:
            preguntas.append({"pregunta": pregunta, "categoria": "talla"})
        
        for pregunta in preguntas_disponibilidad:
            preguntas.append({"pregunta": pregunta, "categoria": "disponibilidad"})
        
        for pregunta in preguntas_color:
            preguntas.append({"pregunta": pregunta, "categoria": "color"})
        
        for pregunta in preguntas_general:
            preguntas.append({"pregunta": pregunta, "categoria": "general"})
        
        return preguntas
    
    def guardar_datos(self, productos, preguntas, filename_productos="productos_expandidos.csv", filename_preguntas="preguntas_entrenamiento.csv"):
        """Guarda los datos generados en archivos CSV y JSON"""
        
        # Guardar productos como CSV para análisis
        df_productos = pd.DataFrame(productos)
        df_productos.to_csv(DATA_DIR / filename_productos, index=False, encoding='utf-8')
        
        # Guardar productos como JSON para el bot
        with open(DATA_DIR / "productos_expandidos.json", 'w', encoding='utf-8') as f:
            json.dump(productos, f, ensure_ascii=False, indent=2)
        
        # Guardar preguntas de entrenamiento
        df_preguntas = pd.DataFrame(preguntas)
        df_preguntas.to_csv(DATA_DIR / filename_preguntas, index=False, encoding='utf-8')
        
        print(f"✅ Datos guardados exitosamente:")
        print(f"   📦 {len(productos)} productos en {filename_productos}")
        print(f"   ❓ {len(preguntas)} preguntas de entrenamiento en {filename_preguntas}")
        print(f"   💾 Archivos guardados en: {DATA_DIR}")
        
        return df_productos, df_preguntas
    
    def mostrar_estadisticas(self, productos):
        """Muestra estadísticas de los productos generados"""
        df = pd.DataFrame(productos)
        
        print("\n📊 ESTADÍSTICAS DE PRODUCTOS GENERADOS")
        print("=" * 50)
        print(f"Total de productos: {len(productos)}")
        print(f"Categorías únicas: {df['categoria'].nunique()}")
        print(f"Productos disponibles: {df['disponible'].sum()}")
        print(f"Productos destacados: {df['destacado'].sum()}")
        print(f"Productos nuevos: {df['nuevo'].sum()}")
        
        print(f"\n💰 Precios:")
        print(f"   Precio promedio: ${df['precio_final'].mean():.2f}")
        print(f"   Precio mínimo: ${df['precio_final'].min():.2f}")
        print(f"   Precio máximo: ${df['precio_final'].max():.2f}")
        
        print(f"\n📦 Stock:")
        print(f"   Stock total: {df['stock_total'].sum():,} unidades")
        print(f"   Stock promedio por producto: {df['stock_total'].mean():.1f}")
        
        print(f"\n🏷️ Top 5 categorías por cantidad:")
        print(df['categoria'].value_counts().head().to_string())

def main():
    """Función principal para generar datos"""
    print("🚀 Generando datos para Fashion Store...")
    
    generator = ProductDataGenerator()
    
    # Generar productos y preguntas
    productos = generator.generar_productos(num_productos=300)
    preguntas = generator.generar_preguntas_entrenamiento()
    
    # Guardar datos
    df_productos, df_preguntas = generator.guardar_datos(productos, preguntas)
    
    # Mostrar estadísticas
    generator.mostrar_estadisticas(productos)
    
    print("\n✨ ¡Generación de datos completada exitosamente!")

if __name__ == "__main__":
    main()