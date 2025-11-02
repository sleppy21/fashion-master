# 🌐 Túnel Público Gratuito - Fashion Store

## ✅ Túnel Activo Ahora

**URL Pública:** https://d59deabcd46e18.lhr.life

Tu tienda fashion-master está ahora accesible públicamente en la URL de arriba.

Para acceder a la tienda, visita:
- **Página principal:** https://d59deabcd46e18.lhr.life/fashion-master/

---

## 📋 Cómo Funciona

Este túnel se creó usando **serveo.net**, un servicio gratuito que:
- ✅ No requiere instalación de software
- ✅ No requiere cuenta ni registro
- ✅ Usa SSH (incluido en Windows)
- ✅ Proporciona HTTPS automáticamente
- ⚠️ La URL cambia cada vez que abres el túnel (a menos que uses un subdominio personalizado)

---

## 🚀 Cómo Abrir el Túnel Nuevamente

Cuando cierres esta sesión o terminal, el túnel se cerrará. Para abrirlo de nuevo:

**RECOMENDADO (más estable):**
```powershell
ssh -R 80:localhost:80 nokey@localhost.run
```

**Alternativa (si localhost.run no funciona):**
```powershell
ssh -R 80:localhost:80 serveo.net
```

**Importante:** Cada vez que ejecutes este comando, obtendrás una URL diferente.

### Opción: Usar un Subdominio Personalizado (si está disponible)

```powershell
ssh -R fashionstore:80:localhost:80 serveo.net
```

Esto intentará usar `https://fashionstore.serveo.net` (si el nombre está libre).

---

## ⏹️ Cómo Detener el Túnel

Presiona `Ctrl + C` en la terminal donde está corriendo el túnel SSH.

---

## 🔒 Notas de Seguridad

⚠️ **IMPORTANTE:**
- Tu servidor local ahora es accesible públicamente en Internet
- Asegúrate de que las páginas de administración (`admin.php`) estén protegidas con contraseña
- No compartas la URL pública si contiene datos sensibles
- Este túnel es para desarrollo/pruebas, no para producción
- Cierra el túnel cuando termines de usarlo

---

## 🌍 Alternativas Gratuitas

Si serveo.net no funciona o prefieres otra opción:

### 1. **localhost.run**
```powershell
ssh -R 80:localhost:80 localhost.run
```

### 2. **ngrok** (requiere registro gratuito)
- Descargar desde: https://ngrok.com/download
- Ejecutar: `ngrok http 80`

### 3. **localtunnel** (requiere Node.js)
```powershell
npx localtunnel --port 80
```

---

## 📝 Estado del Túnel Actual

- ✅ Servidor: Apache/XAMPP corriendo en puerto 80
- ✅ Proyecto: C:\xampp\htdocs\fashion-master
- ✅ Túnel: Activo con localhost.run
- 🌐 URL Pública: https://d59deabcd46e18.lhr.life/fashion-master/
- 🔑 Connection ID: 57cc197d-ff2e-441a-a1a0-2e3353279fdf

---

**Última actualización:** 2 de noviembre de 2025
