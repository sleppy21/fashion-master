/**
 * CONFIGURACIÓN GLOBAL PARA RUTAS
 * Auto-detecta el base path para funcionar en cualquier entorno
 */

(function (window) {
  'use strict';

  // Detectar el base path automáticamente
  function getBasePath() {
    const path = window.location.pathname || '/';
    
    // 1. Si estamos en localhost con /fashion-master/
    if (path.includes('/fashion-master/')) {
      return '/fashion-master';
    }
    
    // 2. Si estamos en túnel (ngrok, cloudflare, etc.) y el path tiene /app/
    // Ejemplo: https://xxx.trycloudflare.com/app/controllers/index.php
    if (path.includes('/app/')) {
      // Extraer todo lo que está ANTES de /app/
      const match = path.match(/^(.*?)\/app\//);
      if (match && match[1]) {
        return match[1];
      }
    }
    
    // 3. Si estamos en admin.php o index.php directamente
    if (path.includes('/admin.php') || path.includes('/index.php')) {
      // Extraer el directorio base
      const match = path.match(/^(.*?)\/(admin|index)\.php/);
      if (match && match[1]) {
        return match[1];
      }
    }
    
    // 4. Si el proyecto está en la raíz del dominio
    return '';
  }

  // Configuración global
  const detectedBasePath = getBasePath();
  
  window.AppConfig = {
    basePath: detectedBasePath,

    // URLs de API
    getApiUrl: function (endpoint) {
      // endpoint puede venir con o sin slash
      const e = endpoint.replace(/^\/+/, '');
      return this.basePath + '/app/controllers/' + e;
    },

    // URLs de vistas
    getViewUrl: function (path) {
      const p = path.replace(/^\/+/, '');
      return this.basePath + '/app/views/' + p;
    },

    // URLs de assets
    getAssetUrl: function (path) {
      const p = path.replace(/^\/+/, '');
      return this.basePath + '/public/assets/' + p;
    },

    // URLs de imágenes
    getImageUrl: function (path) {
      const p = path.replace(/^\/+/, '');
      return this.basePath + '/public/assets/img/' + p;
    },

    // URL base completa
    getBaseUrl: function () {
      return window.location.protocol + '//' + window.location.host + this.basePath;
    }
  };

  // Para compatibilidad hacia atrás / configuración pública
  window.CONFIG = {
    basePath: window.AppConfig.basePath,
    apiUrl: window.AppConfig.getApiUrl('ProductController.php'),
    baseUrl: window.AppConfig.getBaseUrl()
  };

})(window);
