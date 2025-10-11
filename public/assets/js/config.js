/**
 * CONFIGURACIÓN GLOBAL PARA RUTAS
 * Auto-detecta el base path para funcionar en cualquier entorno
 */

(function (window) {
  'use strict';

  // Detectar el base path automáticamente
  function getBasePath() {
    const path = window.location.pathname || '/';
    // Si estamos en localhost o hosting con /fashion-master/
    if (path.includes('/fashion-master/')) {
      return '/fashion-master';
    }
    // Si el proyecto está en la raíz del dominio (hosting)
    return '';
  }

  // Configuración global
  window.AppConfig = {
    basePath: getBasePath(),

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
