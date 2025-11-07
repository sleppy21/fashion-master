#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""
Fashion Store - Túnel HTTP Permanente
Mantiene un túnel ngrok activo 24/7 sin interrupciones
"""

from pyngrok import ngrok, conf
import time
import sys
import os

def print_banner():
    """Muestra banner del túnel"""
    print("\n" + "="*60)
    print("🌐 FASHION STORE - TÚNEL HTTP ACTIVO")
    print("="*60 + "\n")

def start_tunnel():
    """Inicia el túnel HTTP y lo mantiene activo"""
    
    # Configuración de ngrok
    port = 80  # Puerto de Apache/XAMPP
    
    try:
        print_banner()
        print("🔧 Configurando túnel HTTP...")
        print(f"📡 Puerto local: {port}")
        print("\n⏳ Iniciando túnel ngrok (puede tardar unos segundos)...\n")
        
        # Crear túnel HTTP
        public_url = ngrok.connect(port, "http")
        
        print("✅ ¡TÚNEL ACTIVO!")
        print("\n" + "="*60)
        print("🌍 URL PÚBLICA:")
        print(f"   {public_url}")
        print("="*60)
        print("\n📱 Accede desde cualquier dispositivo con esta URL")
        print("\n🔗 URL completa del proyecto:")
        print(f"   {public_url}/fashion-master/")
        print(f"   {public_url}/fashion-master/admin.php")
        print("\n⚠️  IMPORTANTE:")
        print("   - Mantén esta ventana ABIERTA para que el túnel funcione")
        print("   - NO cierres esta terminal")
        print("   - El túnel está activo 24/7 mientras esta ventana esté abierta")
        print("\n🛑 Para detener el túnel: Presiona Ctrl+C")
        print("\n" + "="*60 + "\n")
        
        # Mostrar información de túneles activos
        tunnels = ngrok.get_tunnels()
        if tunnels:
            print("📊 Túneles activos:")
            for tunnel in tunnels:
                print(f"   • {tunnel.public_url} -> {tunnel.config['addr']}")
            print()
        
        # Mantener el script ejecutándose
        print("⏰ Túnel activo... (esperando conexiones)\n")
        
        try:
            while True:
                time.sleep(1)
        except KeyboardInterrupt:
            print("\n\n🛑 Deteniendo túnel...")
            ngrok.disconnect(public_url)
            print("✅ Túnel cerrado correctamente")
            sys.exit(0)
            
    except Exception as e:
        print(f"\n❌ Error al crear túnel: {str(e)}")
        print("\n💡 Posibles soluciones:")
        print("   1. Verifica que XAMPP esté ejecutándose")
        print("   2. Verifica que Apache esté en el puerto 80")
        print("   3. Si tienes firewall, permite ngrok")
        print("\n🔧 Para más ayuda: https://ngrok.com/docs")
        sys.exit(1)

if __name__ == "__main__":
    # Limpiar pantalla
    os.system('cls' if os.name == 'nt' else 'clear')
    
    # Iniciar túnel
    start_tunnel()
