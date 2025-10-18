# 🧪 Pruebas en Local - Reproductor Seguro

Guía para probar el sistema completo en tu computadora antes de desplegar a Heroku.

## 📋 Requisitos

- PHP 7.4 o superior
- Extensión cURL habilitada
- Extensión OpenSSL habilitada

## 🚀 Configuración Inicial

### 1. Verificar PHP

```bash
# Verificar versión
php -v

# Verificar extensiones
php -m | grep curl
php -m | grep openssl
```

### 2. Crear archivo .env

Crea un archivo `.env` en la raíz del proyecto:

```env
SECRET_KEY=test-secret-key-for-local-development-12345678
ALLOWED_DOMAIN=http://localhost:8000
VIDEO_PATH=./videos/
BASE_URL=http://localhost:8000
ACCESS_CODE=
```

**Nota**: Deja `ACCESS_CODE` vacío para acceso libre en desarrollo.

### 3. Dar permisos al directorio sessions

```bash
chmod 755 sessions/
```

## ▶️ Iniciar Servidor Local

```bash
# Desde la raíz del proyecto
php -S localhost:8000
```

Deberías ver:
```
PHP 8.x Development Server (http://localhost:8000) started
```

## 🧪 Probar el Sistema

### Test 1: Página Principal

1. Abre tu navegador
2. Visita: `http://localhost:8000`
3. ✅ Deberías ver la página principal con botones

### Test 2: API de Sesión

**Crear Sesión:**

```bash
curl -X POST http://localhost:8000/api/session.php?action=create \
  -H "Content-Type: application/json" \
  -d '{"video_id": "webtest", "duration": 3600}'
```

**Respuesta esperada:**
```json
{
  "success": true,
  "token": "abc123def456...",
  "expires": 1729184400,
  "expires_in": 3600,
  "expires_at": "2025-10-17 16:00:00"
}
```

**Validar Token:**

```bash
# Reemplaza TOKEN_AQUI con el token que obtuviste
curl http://localhost:8000/api/session.php?action=validate&token=TOKEN_AQUI
```

**Respuesta esperada:**
```json
{
  "success": true,
  "valid": true,
  "expires": 1729184400,
  "remaining": 3598,
  "video_id": "webtest"
}
```

### Test 3: Página de Acceso

1. Visita: `http://localhost:8000/access.php`
2. Haz clic en "Acceso Directo" o "⚡ Acceso Rápido"
3. ✅ Deberías ver el mensaje "Token generado correctamente"
4. ✅ Serás redirigido a `player.html?token=...`

### Test 4: Reproductor

1. Una vez en `player.html?token=...`
2. ✅ Deberías ver el reproductor cargando
3. ✅ El video debería comenzar a cargar desde quasars.ddns.net:8880

### Test 5: API de Streaming

**IMPORTANTE**: Primero genera un token válido (Test 2 o Test 3), luego:

```bash
# Reemplaza TOKEN_AQUI con un token válido
curl -I http://localhost:8000/api/stream.php?token=TOKEN_AQUI
```

**Respuesta esperada (headers):**
```
HTTP/1.1 200 OK
Content-Type: video/mp4
Accept-Ranges: bytes
Content-Length: 2225582
```

## 🐛 Solución de Problemas

### Error: "Address already in use"

Otro proceso está usando el puerto 8000.

**Solución 1**: Usa otro puerto
```bash
php -S localhost:8080
```

**Solución 2**: Encuentra y cierra el proceso
```bash
# Mac/Linux
lsof -ti:8000 | xargs kill

# Windows
netstat -ano | findstr :8000
taskkill /PID [PID_NUMBER] /F
```

### Error: "cURL extension not found"

**Mac con Homebrew:**
```bash
brew install php
```

**Ubuntu/Debian:**
```bash
sudo apt-get install php-curl
```

**Windows:**
1. Abre `php.ini`
2. Busca `;extension=curl`
3. Quita el `;` para descomentarlo
4. Reinicia Apache/PHP

### Error: "sessions/ directory not writable"

```bash
# Mac/Linux
chmod 755 sessions/

# Windows
# Clic derecho en la carpeta > Propiedades > Seguridad > Editar
```

### Error: "Remote video not available"

**Causa**: No puede conectar con quasars.ddns.net:8880

**Verificar**:
```bash
curl -I http://quasars.ddns.net:8880/videos/webtest.mp4
```

Si falla, el servidor quasars no está accesible. Soluciones:

1. Verifica tu conexión a internet
2. Verifica que el servidor quasars esté en línea
3. Usa un video local de prueba (modifica `api/stream.php`)

### Video no carga en el reproductor

**Debug en el navegador:**

1. Abre DevTools (F12)
2. Ve a la pestaña "Console"
3. Busca errores en rojo
4. Ve a la pestaña "Network"
5. Filtra por "stream.php"
6. Verifica el status code (debe ser 200 o 206)

**Errores comunes:**

- **401**: No hay token (vuelve a access.php)
- **403**: Token inválido o expirado (genera nuevo token)
- **502**: Servidor quasars no responde

## 📊 Monitorear Sesiones

### Ver sesiones activas

```bash
# Mac/Linux
ls -lh sessions/

# Ver contenido de una sesión
cat sessions/session_abc123.json
```

Contenido de ejemplo:
```json
{
  "token": "abc123...",
  "created": 1729180800,
  "expires": 1729184400,
  "video_id": "webtest",
  "ip": "127.0.0.1",
  "user_agent": "Mozilla/5.0..."
}
```

### Limpiar sesiones expiradas

```bash
# Eliminar sesiones de más de 60 minutos
find sessions/ -name "session_*.json" -mmin +60 -delete
```

## 🔧 Pruebas Avanzadas

### Test de Range Requests (Streaming parcial)

```bash
# Solicitar primeros 1000 bytes
curl -H "Range: bytes=0-999" \
  http://localhost:8000/api/stream.php?token=TOKEN_AQUI \
  -o test_chunk.mp4
```

Debería devolver HTTP 206 (Partial Content)

### Test de Validación de IP

Modifica `api/stream.php` para habilitar validación de IP:

```php
// Después de validar sesión
if ($session['ip'] !== $_SERVER['REMOTE_ADDR']) {
    sendError(403, 'IP mismatch');
}
```

Luego intenta acceder desde otra computadora con el mismo token.

### Test de Expiración

1. Crea un token con duración de 10 segundos:

```bash
curl -X POST http://localhost:8000/api/session.php?action=create \
  -H "Content-Type: application/json" \
  -d '{"video_id": "webtest", "duration": 10}'
```

2. Espera 15 segundos

3. Intenta validar:

```bash
curl http://localhost:8000/api/session.php?action=validate&token=TOKEN_AQUI
```

Debería responder:
```json
{
  "success": false,
  "valid": false,
  "error": "Invalid or expired token"
}
```

## 🎯 Checklist de Pruebas

Antes de desplegar a Heroku, verifica que todo funcione:

- [ ] Servidor PHP inicia sin errores
- [ ] Página principal carga correctamente
- [ ] API de sesión crea tokens válidos
- [ ] API de sesión valida tokens correctamente
- [ ] Página de acceso funciona y redirige
- [ ] Reproductor carga y muestra interfaz
- [ ] Video se carga desde la API proxy
- [ ] Video se reproduce correctamente
- [ ] Controles del video funcionan (play, pause, seek)
- [ ] Temporizador de expiración se actualiza
- [ ] Token expira correctamente después del tiempo configurado
- [ ] Sesiones se guardan en `/sessions/`
- [ ] No se expone la URL de quasars en el navegador

## 📝 Logs y Debugging

### Habilitar logs detallados

En `api/stream.php`, agrega al inicio:

```php
error_log("Stream request - Token: $token, Video: $videoId");
```

Ver logs:
```bash
tail -f /var/log/php/error.log
```

### Debug en el navegador

1. Abre DevTools (F12)
2. Ve a Console
3. Escribe:
```javascript
sessionToken
sessionExpires
```

4. Ve a Network
5. Filtra por "stream.php"
6. Verifica headers de respuesta

## 🚀 Siguiente Paso

Una vez que todo funciona en local, estás listo para desplegar a Heroku:

```bash
# Ver guía de despliegue
cat HEROKU_SETUP.txt
```

---

**¿Todo funciona? ¡Perfecto! Ahora despliega a Heroku 🚀**

**¿Algo no funciona? Revisa la sección de Solución de Problemas ⬆️**

