<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Limpiar Cache del Navegador</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 600px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 32px;
        }
        
        .version {
            color: #666;
            font-size: 14px;
            margin-bottom: 30px;
        }
        
        .info {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 20px 0;
            text-align: left;
            border-radius: 8px;
        }
        
        .info h3 {
            color: #667eea;
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .info ol {
            margin-left: 20px;
        }
        
        .info li {
            margin: 10px 0;
            color: #555;
            line-height: 1.6;
        }
        
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 40px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            margin-top: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }
        
        .status {
            margin-top: 20px;
            padding: 15px;
            background: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
            color: #155724;
        }
        
        code {
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 4px;
            color: #e83e8c;
            font-family: 'Courier New', monospace;
        }
        
        .keyboard {
            background: #333;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 13px;
            display: inline-block;
            margin: 0 2px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔄 Limpiar Cache del Navegador</h1>
        <p class="version">SleppyStore - Sistema de actualización v3.0</p>
        
        <div class="status">
            ✅ Archivos actualizados correctamente en el servidor<br>
            📅 Última actualización: <?php echo date('Y-m-d H:i:s'); ?><br>
            🔢 Timestamp: <?php echo time(); ?>
        </div>
        
        <div class="info">
            <h3>📋 Instrucciones para limpiar el cache:</h3>
            <ol>
                <li>
                    <strong>Recarga forzada (Recomendado):</strong><br>
                    Presiona <span class="keyboard">Ctrl</span> + <span class="keyboard">Shift</span> + <span class="keyboard">R</span>
                    (En Mac: <span class="keyboard">Cmd</span> + <span class="keyboard">Shift</span> + <span class="keyboard">R</span>)
                </li>
                <li>
                    <strong>O usa modo incógnito:</strong><br>
                    Presiona <span class="keyboard">Ctrl</span> + <span class="keyboard">Shift</span> + <span class="keyboard">N</span>
                    (En Mac: <span class="keyboard">Cmd</span> + <span class="keyboard">Shift</span> + <span class="keyboard">N</span>)
                </li>
                <li>
                    <strong>O limpia el cache manualmente:</strong><br>
                    Presiona <span class="keyboard">Ctrl</span> + <span class="keyboard">Shift</span> + <span class="keyboard">Delete</span><br>
                    Selecciona "Imágenes y archivos en caché" → Eliminar datos
                </li>
                <li>
                    <strong>DevTools (para desarrollo):</strong><br>
                    Presiona <span class="keyboard">F12</span> → Pestaña Network → Marca "Disable cache"
                </li>
            </ol>
        </div>
        
        <div class="info">
            <h3>✅ Problemas resueltos:</h3>
            <ul style="list-style: none; margin-left: 0;">
                <li>✅ Error <code>searchTimeout already declared</code></li>
                <li>✅ Error <code>aplicarFiltro is not defined</code></li>
                <li>✅ Filtros en tiempo real con Fetch API</li>
                <li>✅ Checkboxes pequeños en móvil (16px)</li>
                <li>✅ Breadcrumbs consistentes en todas las páginas</li>
            </ul>
        </div>
        
        <a href="shop.php?v=<?php echo time(); ?>" class="btn">🛍️ Ir a la Tienda (Versión Limpia)</a>
        <a href="index.php?v=<?php echo time(); ?>" class="btn">🏠 Ir al Inicio</a>
    </div>
    
    <script>
        // Auto-recarga después de 3 segundos si se presiona un botón
        document.querySelectorAll('.btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                // Agregar timestamp a la URL para forzar recarga
                const url = new URL(this.href);
                url.searchParams.set('cache_bust', Date.now());
                this.href = url.toString();
            });
        });
    </script>
</body>
</html>
