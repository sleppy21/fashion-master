#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Bot SleppyStore - Aplicación principal Flask
Punto de entrada para ejecutar solo el bot
"""

import os
import sys
from pathlib import Path

# Agregar el directorio actual al path
current_dir = Path(__file__).parent
sys.path.insert(0, str(current_dir))

from flask import Flask
from src.api.chat_routes import chat_bp
from flask_cors import CORS

def create_app():
    """Factory para crear la aplicación Flask"""
    app = Flask(__name__)
    app.config['JSON_AS_ASCII'] = False
    
    # CORS
    CORS(app, resources={
        r"/api/*": {
            "origins": ["http://localhost:3000", "http://127.0.0.1:3000"],
            "methods": ["GET", "POST", "OPTIONS"],
            "allow_headers": ["Content-Type", "Authorization"]
        }
    })
    
    # Registrar blueprint
    app.register_blueprint(chat_bp)
    
    @app.route('/health')
    def health():
        return {"status": "ok", "service": "SleppyStore Bot"}
    
    return app

if __name__ == '__main__':
    app = create_app()
    print("🤖 SleppyStore Bot iniciando...")
    print("📡 API disponible en: http://localhost:3000/api/v1/chat/ask")
    print("🔧 Health check: http://localhost:3000/health")
    app.run(host='0.0.0.0', port=3000, debug=False)