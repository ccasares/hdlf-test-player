# 🔒 Sistema de Reproductor Seguro

## 📖 Descripción

Este sistema proporciona un reproductor de video ultra-seguro donde **la URL del video NUNCA se expone en el frontend**. El video se transmite a través de una API proxy con validación de tokens de sesión temporal.

## 🎯 Características Principales

### 🛡️ Seguridad Máxima

- ✅ **URL Oculta**: La URL real del video (`http://quasars.ddns.net:8880/videos/webtest.mp4`) nunca aparece en el código frontend
- ✅ **Tokens Temporales**: Cada sesión tiene un token único que expira después de 1 hora
- ✅ **API Proxy**: El video se sirve a través de `/api/stream.php` que valida el token antes de servir
- ✅ **No Inspección**: El inspector del navegador no puede ver la URL real del video
- ✅ **Streaming Seguro**: Soporte completo para Range requests (HTTP 206) sin exponer la fuente

### 🎬 Características del Reproductor

- ✅ Controles HTML5 nativos
- ✅ Soporte para seek/scrubbing
- ✅ Temporizador de expiración de sesión
- ✅ Indicadores de estado en tiempo real
- ✅ Prevención de clic derecho (opcional)
- ✅ Interfaz moderna y responsive

## 🏗️ Arquitectura del Sistema

```
┌─────────────────┐
│   Usuario       │
│   (Navegador)   │
└────────┬────────┘
         │
         │ 1. Solicita acceso
         ▼
┌─────────────────┐
│  access.php     │ ◄── Página de entrada
└────────┬────────┘
         │
         │ 2. Genera token
         ▼
┌─────────────────┐
│ api/session.php │ ◄── Crea token temporal
└────────┬────────┘
         │
         │ 3. Redirige con token
         ▼
┌─────────────────┐
│  player.html    │ ◄── Reproductor seguro
└────────┬────────┘
         │
         │ 4. Solicita video
         ▼
┌─────────────────┐
│ api/stream.php  │ ◄── Proxy que valida y sirve
└────────┬────────┘
         │
         │ 5. Obtiene video
         ▼
┌─────────────────┐
│  Video Remoto   │ ◄── http://quasars.ddns.net:8880
│  (quasars)      │
└─────────────────┘
```

## 📁 Estructura de Archivos

```
/
├── access.php              # Página de acceso (punto de entrada)
├── player.html             # Reproductor seguro
├── api/
│   ├── session.php         # API de gestión de tokens
│   └── stream.php          # API proxy para streaming
├── sessions/               # Tokens temporales (auto-gestionado)
│   └── .gitkeep
└── .env                    # Configuración
```

## 🚀 Instalación y Configuración

### 1. Configurar Variables de Entorno

Edita el archivo `.env`:

```env
# Clave secreta para tokens
SECRET_KEY=tu-clave-secreta-aqui

# Dominio autorizado
ALLOWED_DOMAIN=https://midominio.com

# Código de acceso opcional (dejar vacío para acceso libre)
ACCESS_CODE=mi-codigo-secreto

# URLs base
BASE_URL=https://tu-app.herokuapp.com
```

### 2. Configurar Permisos

```bash
# Dar permisos de escritura al directorio sessions
chmod 755 sessions/
```

### 3. Configurar Heroku

```bash
# Variables de entorno en Heroku
heroku config:set SECRET_KEY=$(openssl rand -hex 32)
heroku config:set ALLOWED_DOMAIN=https://midominio.com
heroku config:set ACCESS_CODE=opcional-codigo-acceso
heroku config:set BASE_URL=https://tu-app.herokuapp.com
```

## 🎮 Flujo de Uso

### Paso 1: Acceder al Sistema

1. El usuario visita `https://tu-app.herokuapp.com/access.php`
2. Ve una pantalla de bienvenida con opciones:
   - **Acceso Directo**: Si no hay código de acceso configurado
   - **Con Código**: Si `ACCESS_CODE` está configurado en `.env`

### Paso 2: Generar Token de Sesión

1. El usuario hace clic en "Acceder al Reproductor"
2. El sistema llama a `POST /api/session.php?action=create`
3. Se genera un token único con:
   - Token HMAC-SHA256
   - Timestamp de expiración (1 hora por defecto)
   - IP del usuario
   - User-Agent
4. El token se almacena en `sessions/session_{token}.json`

### Paso 3: Acceder al Reproductor

1. El usuario es redirigido a `player.html?token=abc123...`
2. El reproductor valida el token con `GET /api/session.php?action=validate`
3. Si es válido, se habilita la reproducción

### Paso 4: Reproducir Video

1. El reproductor configura el video source a `/api/stream.php?token=abc123`
2. Cuando el usuario da play:
   - El navegador solicita el video a `/api/stream.php`
   - La API valida el token
   - Si es válido, hace proxy del video desde `quasars.ddns.net:8880`
   - El video se transmite al navegador **sin exponer la URL real**

### Paso 5: Expiración

1. El token expira después de 1 hora
2. El reproductor muestra una advertencia 5 minutos antes
3. Después de la expiración, el usuario debe volver a `access.php`

## 🔧 API Reference

### POST /api/session.php?action=create

Crea un nuevo token de sesión.

**Request Body:**
```json
{
  "video_id": "webtest",
  "duration": 3600,
  "access_code": "opcional"
}
```

**Response Success (200):**
```json
{
  "success": true,
  "token": "abc123...",
  "expires": 1729184400,
  "expires_in": 3600,
  "expires_at": "2025-10-17 16:00:00"
}
```

**Response Error (403):**
```json
{
  "success": false,
  "error": "Invalid access code"
}
```

### GET /api/session.php?action=validate&token=...

Valida un token existente.

**Response Success (200):**
```json
{
  "success": true,
  "valid": true,
  "expires": 1729184400,
  "remaining": 2847,
  "video_id": "webtest"
}
```

**Response Error (403):**
```json
{
  "success": false,
  "valid": false,
  "error": "Invalid or expired token"
}
```

### GET /api/stream.php?token=...&video=webtest

Sirve el video a través del proxy.

**Headers:**
- `Range`: Opcional, para streaming parcial
- `X-Session-Token`: Alternativa al parámetro token

**Response:**
- **200**: Video completo
- **206**: Contenido parcial (Range request)
- **401**: Token no proporcionado
- **403**: Token inválido o expirado
- **404**: Video no encontrado
- **502**: Video remoto no disponible

## 🔐 Seguridad

### ¿Cómo se Protege la URL?

1. **Frontend NO conoce la URL**: 
   - `player.html` solo conoce `/api/stream.php`
   - La URL real está hardcodeada en `api/stream.php` (backend)

2. **Validación en cada request**:
   - Cada chunk del video requiere token válido
   - El token se valida en el servidor antes de hacer proxy

3. **Tokens de un solo uso por sesión**:
   - Un token solo funciona para una IP/User-Agent
   - Expira automáticamente después de 1 hora

4. **Sin caché de URL**:
   - El video se transmite en streaming, no se descarga
   - El browser no puede ver la URL de origen

### ¿Se Puede Hackear?

**Inspección del navegador:**
- ❌ No verá la URL real, solo `/api/stream.php?token=...`
- ✅ Puede copiar el token, pero:
  - Solo funciona por 1 hora
  - Solo desde la misma IP (opcional)
  - Solo con el mismo User-Agent (opcional)

**Copiar el token:**
- Si alguien copia el token y lo usa en otro navegador:
  - Funcionará solo si está en la misma IP (por defecto)
  - Puedes agregar validación adicional de User-Agent
  - El token expira en 1 hora

**Descargar el video:**
- Técnicamente posible con herramientas como `wget` + token
- Pero requiere:
  - Tener acceso al sistema
  - Generar un token válido
  - Descargar dentro de 1 hora
- Es similar a cualquier sistema de streaming (Netflix, YouTube, etc.)

### Mejoras de Seguridad Adicionales

Puedes agregar en `api/stream.php`:

```php
// Validar IP
if ($session['ip'] !== $_SERVER['REMOTE_ADDR']) {
    sendError(403, 'IP mismatch');
}

// Validar User-Agent
if ($session['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
    sendError(403, 'User-Agent mismatch');
}

// Limitar número de reproducciones
if ($session['play_count'] >= 3) {
    sendError(403, 'Max plays exceeded');
}
```

## 🎨 Personalización

### Cambiar Duración de la Sesión

En `api/session.php`, cambia:

```php
$SESSION_DURATION = 3600; // 1 hora
// A:
$SESSION_DURATION = 7200; // 2 horas
```

### Agregar Más Videos

En `api/stream.php`, modifica el array `$videoUrls`:

```php
$videoUrls = [
    'webtest' => 'http://quasars.ddns.net:8880/videos/webtest.mp4',
    'video2' => 'http://quasars.ddns.net:8880/videos/otro-video.mp4',
    'video3' => 'https://otro-servidor.com/video.mp4'
];
```

Luego accede con: `/api/stream.php?token=...&video=video2`

### Habilitar Código de Acceso

En `.env`:

```env
ACCESS_CODE=mi-codigo-secreto-123
```

En Heroku:

```bash
heroku config:set ACCESS_CODE=mi-codigo-secreto-123
```

### Personalizar la UI

Los archivos HTML (`access.php` y `player.html`) tienen CSS inline que puedes modificar fácilmente.

## 📊 Monitoreo

### Ver Sesiones Activas

```bash
# Linux/Mac
ls -lh sessions/

# Ver contenido de una sesión
cat sessions/session_abc123.json
```

### Limpiar Sesiones Expiradas

Las sesiones se limpian automáticamente, pero puedes forzar:

```bash
find sessions/ -name "session_*.json" -mmin +60 -delete
```

## 🐛 Solución de Problemas

### Error: "Session token required"

**Causa**: No se proporcionó token o se perdió en la URL

**Solución**: Vuelve a `access.php` y genera un nuevo token

### Error: "Invalid or expired session token"

**Causa**: El token expiró (>1 hora) o es inválido

**Solución**: Genera un nuevo token desde `access.php`

### Error: "Remote video not available"

**Causa**: El servidor `quasars.ddns.net:8880` no está disponible

**Solución**: Verifica que el servidor de videos esté en línea

### El video no carga

1. Abre la consola del navegador (F12)
2. Verifica errores en la pestaña Console
3. Verifica que el token sea válido: `/api/session.php?action=validate&token=TU_TOKEN`
4. Verifica que la API de streaming responda: `/api/stream.php?token=TU_TOKEN`

### Sesiones no se limpian

**Causa**: PHP no tiene permisos de escritura en `/sessions/`

**Solución**:
```bash
chmod 755 sessions/
```

## 🌐 Despliegue en Heroku

Ya está configurado para Heroku con `composer.json` y `Procfile`.

```bash
# Deploy
git add .
git commit -m "Add secure video player"
git push heroku main

# Configurar variables
heroku config:set SECRET_KEY=$(openssl rand -hex 32)
heroku config:set BASE_URL=$(heroku info -s | grep web_url | cut -d= -f2)
```

## 📝 Ejemplo de Uso Completo

1. **Usuario visita**: `https://mi-app.herokuapp.com/access.php`
2. **Usuario hace clic**: "Acceso Directo"
3. **Sistema genera token**: `abc123def456...`
4. **Usuario es redirigido**: `https://mi-app.herokuapp.com/player.html?token=abc123...`
5. **Reproductor valida token**: ✓ Válido
6. **Usuario da play**: Video comienza
7. **Browser solicita**: `GET /api/stream.php?token=abc123...`
8. **API valida**: Token válido → hace proxy del video
9. **Video se reproduce**: Sin exponer URL real
10. **Después de 1 hora**: Token expira, usuario debe renovar

## 🎉 Ventajas del Sistema

- ✅ **Máxima Seguridad**: URL nunca expuesta al cliente
- ✅ **Fácil de Usar**: Solo visitar una página
- ✅ **Escalable**: Agregar más videos es simple
- ✅ **Compatible**: Funciona en todos los navegadores modernos
- ✅ **Flexible**: Código de acceso opcional
- ✅ **Auditable**: Cada sesión se registra con IP y User-Agent

---

**¡Tu video está ahora ultra-protegido! 🔒🎬**

Para más información, consulta el `README.md` principal.

