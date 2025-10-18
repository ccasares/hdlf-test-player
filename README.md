# 🔒 Servidor de Videos Protegidos

Sistema completo de streaming de videos con autenticación basada en tokens temporales y validación HMAC-SHA256. Compatible con **Synology NAS** (Web Station / Nginx + PHP) y **Heroku**.

## ✨ Características

- ✅ **Tokens Temporales**: URLs con expiración automática
- ✅ **Validación HMAC-SHA256**: Seguridad criptográfica robusta
- ✅ **Protección por Referer**: Solo dominios autorizados pueden acceder
- ✅ **Streaming Parcial**: Soporte completo para Range requests (HTTP 206)
- ✅ **Sin Acceso Directo**: Los videos no son accesibles sin token válido
- ✅ **Prevención de Directory Traversal**: Seguridad contra ataques de ruta
- ✅ **API REST**: Generación de URLs firmadas vía JSON
- ✅ **CLI**: Herramienta de línea de comandos para generar URLs

## 📋 Requisitos

- PHP 7.4 o superior
- Servidor web (Nginx, Apache, o Heroku)
- Extensión PHP: `hash` (incluida por defecto)

## 🚀 Instalación

### Opción 1: Synology NAS (Web Station / Nginx + PHP)

1. **Subir archivos al NAS**:
   ```bash
   # Estructura en el NAS:
   /volume1/web/
   ├── video.php
   ├── generate_url.php
   ├── index.php
   ├── demo.html
   ├── .env
   └── videos/
       ├── video1.mp4
       ├── video2.mp4
       └── ...
   ```

2. **Configurar variables de entorno**:
   Edita el archivo `.env`:
   ```env
   SECRET_KEY=tu-clave-secreta-generada-con-openssl-rand-hex-32
   ALLOWED_DOMAIN=https://midominio.com
   VIDEO_PATH=/volume1/web/videos/
   BASE_URL=https://midominio.com
   ```

3. **Configurar Web Station**:
   - Crea un servidor web virtual
   - Habilita PHP 7.4 o superior
   - Apunta al directorio `/volume1/web/`

4. **Permisos**:
   ```bash
   chmod 644 video.php generate_url.php
   chmod 755 videos/
   chmod 600 .env
   ```

### Opción 2: Heroku

1. **Clonar o descargar este repositorio**:
   ```bash
   git clone https://github.com/tuusuario/protected-video-server.git
   cd protected-video-server
   ```

2. **Crear aplicación en Heroku**:
   ```bash
   heroku create mi-video-server
   ```

3. **Configurar variables de entorno**:
   ```bash
   heroku config:set SECRET_KEY=$(openssl rand -hex 32)
   heroku config:set ALLOWED_DOMAIN=https://midominio.com
   heroku config:set BASE_URL=https://mi-video-server.herokuapp.com
   ```

4. **Desplegar**:
   ```bash
   git add .
   git commit -m "Initial deployment"
   git push heroku main
   ```

5. **Subir videos** (opcional para demo):
   ```bash
   # Crear directorio videos/ y subir archivos de prueba
   mkdir videos
   # Copiar tus videos aquí
   git add videos/
   git commit -m "Add sample videos"
   git push heroku main
   ```

## 🔐 Configuración de Seguridad

### Generar clave secreta

```bash
# En Linux/Mac:
openssl rand -hex 32

# En Windows (PowerShell):
[Convert]::ToBase64String((1..32 | ForEach-Object { Get-Random -Maximum 256 }))
```

### Variables de entorno

| Variable | Descripción | Ejemplo |
|----------|-------------|---------|
| `SECRET_KEY` | Clave secreta para HMAC (32+ caracteres) | `abc123...xyz789` |
| `ALLOWED_DOMAIN` | Dominio autorizado para referer | `https://midominio.com` |
| `VIDEO_PATH` | Ruta donde están los videos | `/volume1/web/videos/` |
| `BASE_URL` | URL base del servidor | `https://tu-app.herokuapp.com` |

## 📖 Uso

### 1. Generar URLs desde CLI

```bash
# Sintaxis: php generate_url.php <archivo> [minutos_validez]
php generate_url.php video1.mp4 60

# Salida:
# ✓ URL generada correctamente
# Archivo: video1.mp4
# Válida hasta: 2025-10-17 15:30:00
# URL: https://tu-servidor.com/video.php?file=video1.mp4&token=abc123...&expires=1729180200
```

### 2. Generar URLs desde API (POST)

**Solicitud para un archivo**:
```bash
curl -X POST https://tu-servidor.com/generate_url.php \
  -H "Content-Type: application/json" \
  -d '{
    "file": "video1.mp4",
    "expires_in": 60
  }'
```

**Respuesta**:
```json
{
  "success": true,
  "file": "video1.mp4",
  "url": "https://tu-servidor.com/video.php?file=video1.mp4&token=abc123...&expires=1729180200",
  "expires_at": "2025-10-17 15:30:00",
  "valid_for": "60 minutes"
}
```

**Solicitud para múltiples archivos**:
```bash
curl -X POST https://tu-servidor.com/generate_url.php \
  -H "Content-Type: application/json" \
  -d '{
    "files": ["video1.mp4", "video2.mp4", "video3.mp4"],
    "expires_in": 120
  }'
```

**Respuesta**:
```json
{
  "success": true,
  "count": 3,
  "videos": {
    "video1.mp4": {
      "url": "https://...",
      "expires_at": "2025-10-17 16:30:00",
      "valid_for": "120 minutes"
    },
    "video2.mp4": { ... },
    "video3.mp4": { ... }
  }
}
```

### 3. Integrar en tu aplicación web

**HTML**:
```html
<video id="myVideo" controls width="800">
  <source id="videoSource" type="video/mp4">
</video>

<script>
  async function loadVideo(filename) {
    const response = await fetch('/generate_url.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ file: filename, expires_in: 60 })
    });
    
    const data = await response.json();
    if (data.success) {
      document.getElementById('videoSource').src = data.url;
      document.getElementById('myVideo').load();
    }
  }
  
  loadVideo('video1.mp4');
</script>
```

### 4. Demo Interactivo

Visita `https://tu-servidor.com/demo.html` para ver un reproductor completo con interfaz gráfica.

## 🛡️ Seguridad

### ¿Cómo funciona?

1. **Generación de Token**:
   - Se crea un HMAC-SHA256 usando: `hash_hmac('sha256', file + expires, SECRET_KEY)`
   - El token es único para cada archivo y tiempo de expiración

2. **Validación en el servidor**:
   - ✅ Verifica que el token sea válido (HMAC correcto)
   - ✅ Verifica que no haya expirado (timestamp)
   - ✅ Verifica el referer (dominio autorizado)
   - ✅ Previene directory traversal (`../`)

3. **Respuesta**:
   - Si todo es válido: sirve el video con soporte Range (HTTP 206)
   - Si algo falla: devuelve HTTP 403 Forbidden

### Prevención de ataques

| Ataque | Protección |
|--------|-----------|
| Token inválido | HMAC-SHA256 con clave secreta |
| Token expirado | Validación de timestamp |
| Acceso directo | Validación de referer |
| Directory traversal | Sanitización de rutas |
| Replay attack | Tokens temporales con expiración |
| Copiar enlaces | Validación de dominio referer |

## 📁 Estructura de Archivos

```
.
├── video.php              # Servidor de videos protegidos
├── generate_url.php       # Generador de URLs firmadas
├── index.php              # Página de inicio
├── demo.html              # Demo interactivo
├── .env                   # Configuración (NO subir a git)
├── .env.example           # Ejemplo de configuración
├── .gitignore             # Archivos ignorados por git
├── composer.json          # Dependencias PHP
├── Procfile               # Configuración Heroku
├── README.md              # Este archivo
└── videos/                # Directorio de videos
    ├── .gitkeep
    ├── video1.mp4
    └── video2.mp4
```

## 🧪 Testing

### Probar generación de URL

```bash
php generate_url.php test.mp4 5
```

### Probar API

```bash
curl -X GET https://tu-servidor.com/generate_url.php
```

### Probar acceso protegido

```bash
# Sin token (debe fallar con 403)
curl -I https://tu-servidor.com/video.php?file=video1.mp4

# Con token válido (debe devolver video)
curl -I "https://tu-servidor.com/video.php?file=video1.mp4&token=abc123...&expires=1729180200" \
  -H "Referer: https://midominio.com"
```

## 🐛 Solución de Problemas

### Error 403: Invalid referer

- **Causa**: El referer no coincide con `ALLOWED_DOMAIN`
- **Solución**: Verifica que la página esté en el dominio autorizado o actualiza `ALLOWED_DOMAIN` en `.env`

### Error 403: Token has expired

- **Causa**: El tiempo de expiración ha pasado
- **Solución**: Genera una nueva URL con `generate_url.php`

### Error 403: Invalid token

- **Causa**: El token HMAC no es válido
- **Solución**: Verifica que `SECRET_KEY` sea la misma en generación y validación

### Error 404: Video not found

- **Causa**: El archivo no existe en `VIDEO_PATH`
- **Solución**: Verifica que el archivo exista y la ruta sea correcta

### Videos no cargan en Heroku

- **Causa**: Heroku no permite almacenamiento persistente
- **Solución**: Usa un servicio externo (S3, Cloudinary) o súbelos al repositorio (solo para archivos pequeños)

## 🔄 Actualización y Mantenimiento

### Cambiar clave secreta

1. Genera una nueva clave: `openssl rand -hex 32`
2. Actualiza `SECRET_KEY` en `.env` (Synology) o Heroku Config Vars
3. Los tokens antiguos dejarán de funcionar

### Actualizar dominio autorizado

```bash
# Heroku
heroku config:set ALLOWED_DOMAIN=https://nuevo-dominio.com

# Synology: edita .env
ALLOWED_DOMAIN=https://nuevo-dominio.com
```

## 📊 Características Avanzadas

### Soporte Range Requests

El servidor soporta solicitudes parciales (HTTP 206), permitiendo:
- Seek/scrubbing en videos
- Descargas resumibles
- Streaming eficiente

### Múltiples formatos

Soporta cualquier tipo de video:
- MP4 (H.264/H.265)
- WebM
- OGG
- MOV
- AVI

## 📝 Licencia

Este proyecto es de código abierto y está disponible bajo la licencia MIT.

## 🤝 Contribuciones

Las contribuciones son bienvenidas. Por favor:

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📧 Soporte

Si tienes preguntas o problemas:
- Abre un issue en GitHub
- Consulta la documentación en este README

---

**Hecho con ❤️ para servir videos de forma segura**

