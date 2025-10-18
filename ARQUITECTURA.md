# 🏗️ Arquitectura del Sistema

## 📊 Diagrama de Flujo

```
┌─────────────────────────────────────────────────────────────────┐
│                    USUARIO / NAVEGADOR                          │
└───────────────────────────┬─────────────────────────────────────┘
                            │
                            │ 1. Visita sitio web
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│                     INDEX.PHP (Landing)                         │
│  - Página principal del servidor                                │
│  - Muestra características y endpoints                          │
│  - Botones: "Reproductor Ultra-Seguro" y "Demo Clásico"       │
└───────────────┬─────────────────────────┬───────────────────────┘
                │                         │
   ┌────────────┘                         └────────────┐
   │ 2a. Sistema Nuevo                    2b. Legacy  │
   │ (RECOMENDADO)                                     │
   ▼                                                   ▼
┌──────────────────────────┐              ┌──────────────────────┐
│    ACCESS.PHP            │              │    DEMO.HTML         │
│  - Página de acceso      │              │  - Demo clásico      │
│  - Botón "Acceso Directo"│              │  - URLs firmadas     │
│  - Input código opcional │              └──────────────────────┘
└─────────┬────────────────┘
          │ 3. Solicita token
          ▼
┌──────────────────────────────────────────────────────────────────┐
│              API/SESSION.PHP (Gestión de Tokens)                 │
│                                                                  │
│  POST ?action=create                                             │
│  - Genera token único con HMAC-SHA256                           │
│  - Establece expiración (1 hora por defecto)                    │
│  - Guarda en /sessions/session_{token}.json                     │
│  - Registra IP y User-Agent                                     │
│  - Valida código de acceso (si está configurado)                │
│                                                                  │
│  GET ?action=validate&token=...                                  │
│  - Verifica que el token existe                                 │
│  - Verifica que no ha expirado                                  │
│  - Devuelve tiempo restante                                     │
└─────────┬────────────────────────────────────────────────────────┘
          │ 4. Devuelve token
          ▼
┌──────────────────────────────────────────────────────────────────┐
│              PLAYER.HTML?token=abc123                            │
│  - Reproductor seguro de video                                  │
│  - Valida token al cargar                                       │
│  - Configura source: /api/stream.php?token=...                  │
│  - Muestra contador de expiración                               │
│  - Interfaz moderna con controles                               │
└─────────┬────────────────────────────────────────────────────────┘
          │ 5. Solicita video (play)
          ▼
┌──────────────────────────────────────────────────────────────────┐
│              API/STREAM.PHP (Proxy de Video)                     │
│                                                                  │
│  1. Recibe request con token                                    │
│  2. Valida token en /sessions/                                  │
│  3. Verifica que no ha expirado                                 │
│  4. Valida IP (opcional)                                        │
│  5. Valida User-Agent (opcional)                                │
│  6. Hace proxy del video desde URL remota                       │
│  7. Soporta Range requests (HTTP 206)                           │
│  8. Transmite video al navegador                                │
│                                                                  │
│  ⚠️  LA URL REAL DEL VIDEO ESTÁ AQUÍ (línea 45)                │
│  ⚠️  NUNCA SE EXPONE AL FRONTEND                                │
└─────────┬────────────────────────────────────────────────────────┘
          │ 6. Obtiene video
          ▼
┌──────────────────────────────────────────────────────────────────┐
│         VIDEO REMOTO (quasars.ddns.net:8880)                    │
│  - Servidor externo con el video                                │
│  - http://quasars.ddns.net:8880/videos/webtest.mp4             │
│  - Solo accesible vía API proxy                                 │
└──────────────────────────────────────────────────────────────────┘
```

## 🔒 Capa de Seguridad

```
┌────────────────────────────────────────────────────────────────┐
│                    CAPAS DE PROTECCIÓN                         │
└────────────────────────────────────────────────────────────────┘

1️⃣  ACCESO INICIAL (access.php)
    ↓
    └─→ Código de acceso opcional (ACCESS_CODE en .env)
    
2️⃣  GENERACIÓN DE TOKEN (api/session.php)
    ↓
    └─→ Token HMAC-SHA256 único
    └─→ Timestamp de expiración
    └─→ Registro de IP y User-Agent
    
3️⃣  VALIDACIÓN EN REPRODUCTOR (player.html)
    ↓
    └─→ Verifica token antes de cargar
    └─→ Monitorea expiración en tiempo real
    
4️⃣  VALIDACIÓN EN CADA REQUEST (api/stream.php)
    ↓
    └─→ Valida token en cada chunk del video
    └─→ Verifica IP (opcional)
    └─→ Verifica User-Agent (opcional)
    └─→ Verifica expiración
    
5️⃣  PROXY INTERMEDIARIO
    ↓
    └─→ URL real NUNCA expuesta al frontend
    └─→ Navegador solo ve /api/stream.php
    └─→ Imposible obtener URL con inspector
```

## 📁 Estructura de Archivos Detallada

```
/
│
├── 🏠 PÁGINA PRINCIPAL
│   └── index.php                    Landing page con información
│
├── 🔒 SISTEMA NUEVO (Ultra-Seguro)
│   ├── access.php                   Punto de entrada, genera tokens
│   ├── player.html                  Reproductor seguro
│   └── api/
│       ├── session.php              Gestión de tokens temporales
│       └── stream.php               Proxy de video (URL oculta aquí)
│
├── 📺 SISTEMA LEGACY (URLs Firmadas)
│   ├── video.php                    Servidor de videos con tokens HMAC
│   ├── generate_url.php             Generador de URLs firmadas
│   └── demo.html                    Demo del sistema legacy
│
├── 💾 DATOS
│   ├── sessions/                    Tokens temporales (auto-gestionado)
│   │   └── session_*.json          Un archivo por sesión activa
│   └── videos/                      Videos locales (opcional)
│       └── .gitkeep
│
├── ⚙️ CONFIGURACIÓN
│   ├── .env                         Variables de entorno (NO en git)
│   ├── .env.example                 Plantilla de configuración
│   ├── composer.json                Dependencias PHP
│   ├── Procfile                     Configuración Heroku
│   ├── app.json                     Deploy con un clic
│   ├── .htaccess                    Configuración Apache
│   └── .gitignore                   Archivos ignorados
│
└── 📚 DOCUMENTACIÓN
    ├── LEEME_PRIMERO.txt           👈 COMIENZA AQUÍ
    ├── TEST_LOCAL.md               Probar en local
    ├── HEROKU_SETUP.txt            Desplegar a Heroku (paso a paso)
    ├── REPRODUCTOR_SEGURO.md       Docs completas sistema nuevo
    ├── README.md                    Docs técnicas completas
    ├── DEPLOY.md                    Guía de despliegue
    ├── INICIO_RAPIDO.md            Guía rápida 5 minutos
    ├── CONFIGURACION_ENV.txt       Cómo crear .env
    └── ARQUITECTURA.md             Este archivo
```

## 🔄 Flujo de Datos

### Flujo de Creación de Sesión

```
Usuario → access.php → api/session.php → sessions/session_abc123.json
                            ↓
                    Devuelve token
                            ↓
              player.html?token=abc123
```

### Flujo de Reproducción de Video

```
player.html → api/stream.php?token=abc123
                     ↓
            Valida token en sessions/
                     ↓
         Hace proxy desde quasars.ddns.net:8880
                     ↓
        Transmite chunks al navegador
                     ↓
             Video se reproduce
```

### Flujo de Expiración

```
Token creado (T+0)
    ↓
Token válido (T+0 a T+3600)
    ↓
Advertencia en reproductor (T+3295)
    ↓
Token expira (T+3600)
    ↓
sessions/session_abc123.json eliminado
    ↓
Usuario debe volver a access.php
```

## 🎯 Componentes Clave

### 1. Sistema de Tokens (api/session.php)

**Responsabilidades:**
- Generar tokens únicos HMAC-SHA256
- Almacenar sesiones en archivos JSON
- Validar tokens existentes
- Limpiar sesiones expiradas
- Aplicar código de acceso (opcional)

**Estructura de un token:**
```json
{
  "token": "abc123def456...",
  "created": 1729180800,
  "expires": 1729184400,
  "video_id": "webtest",
  "ip": "127.0.0.1",
  "user_agent": "Mozilla/5.0..."
}
```

### 2. API Proxy (api/stream.php)

**Responsabilidades:**
- Validar token en cada request
- Hacer proxy del video desde URL remota
- Soportar Range requests para streaming
- Ocultar URL real del frontend
- Manejar errores de conexión

**URLs soportadas:**
```php
$videoUrls = [
    'webtest' => 'http://quasars.ddns.net:8880/videos/webtest.mp4'
];
```

### 3. Reproductor (player.html)

**Responsabilidades:**
- Validar token al cargar
- Configurar source del video
- Mostrar contador de expiración
- Manejar eventos del video
- Prevenir inspección (opcional)

## 🔐 Niveles de Seguridad

### Nivel 1: Básico (Por defecto)
- ✅ URL oculta en el backend
- ✅ Tokens temporales con expiración
- ✅ Validación HMAC

### Nivel 2: Medio (Configurable)
- ✅ Todo lo anterior +
- ✅ Código de acceso para entrar
- ✅ Validación de IP en cada request

### Nivel 3: Avanzado (Personalizable)
- ✅ Todo lo anterior +
- ✅ Validación de User-Agent
- ✅ Límite de reproducciones por token
- ✅ Rate limiting
- ✅ Logging de accesos

## 📈 Escalabilidad

### Videos Locales vs Remotos

**Remoto (Actual):**
```php
// En api/stream.php
$VIDEO_URL = 'http://quasars.ddns.net:8880/videos/webtest.mp4';
```

**Local:**
```php
// En api/stream.php
$VIDEO_URL = __DIR__ . '/../videos/webtest.mp4';
```

**Múltiples Videos:**
```php
$videoUrls = [
    'webtest' => 'http://quasars.ddns.net:8880/videos/webtest.mp4',
    'video2' => 'http://otro-server.com/video2.mp4',
    'video3' => './videos/local.mp4'
];
```

### Almacenamiento de Sesiones

**Actual (Archivos):**
- Pros: Simple, sin dependencias
- Contras: No escalable a múltiples servidores

**Alternativas:**
- Redis: Rápido, distribuido
- Memcached: En memoria, rápido
- Base de datos: PostgreSQL, MySQL
- JWT: Sin estado, descentralizado

## 🔧 Puntos de Extensión

### 1. Agregar Autenticación de Usuario

En `api/session.php`:
```php
// Verificar usuario/contraseña
if (!validateUser($username, $password)) {
    sendError(401, 'Invalid credentials');
}
```

### 2. Agregar Logging

En `api/stream.php`:
```php
function logAccess($token, $ip, $video) {
    $log = date('Y-m-d H:i:s') . " - $ip - $video - $token\n";
    file_put_contents('access.log', $log, FILE_APPEND);
}
```

### 3. Agregar Rate Limiting

En `api/stream.php`:
```php
if (getRequestCount($ip) > 100) {
    sendError(429, 'Too many requests');
}
```

### 4. Agregar Analytics

En `player.html`:
```javascript
// Enviar evento cuando se reproduce
fetch('/api/analytics.php', {
    method: 'POST',
    body: JSON.stringify({
        event: 'video_play',
        video_id: 'webtest',
        timestamp: Date.now()
    })
});
```

## 🌐 Despliegue

### Heroku
```
Tipo: PaaS (Platform as a Service)
Costo: Gratis hasta 1000h/mes
Escalabilidad: Vertical (más dynos)
```

### Synology NAS
```
Tipo: On-premise
Costo: Hardware inicial
Escalabilidad: Limitada por hardware
```

### VPS (DigitalOcean, AWS, etc.)
```
Tipo: IaaS (Infrastructure as a Service)
Costo: Variable según uso
Escalabilidad: Horizontal y vertical
```

## 🎨 Personalización

### Colores y Tema

En `player.html` y `access.php`, busca los gradientes:
```css
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
```

### Duración de Sesión

En `api/session.php`:
```php
$SESSION_DURATION = 3600; // Cambia a lo que necesites
```

### Códigos de Acceso

En `.env`:
```env
ACCESS_CODE=tu-codigo-aqui
```

## 📊 Monitoreo y Logs

### Ver Sesiones Activas
```bash
ls -lh sessions/
```

### Ver Logs de Heroku
```bash
heroku logs --tail
```

### Logs Personalizados
```php
error_log("Custom message: $variable");
```

## 🚀 Mejoras Futuras

1. **Dashboard Admin**: Panel para ver sesiones activas
2. **Múltiples Usuarios**: Sistema de usuarios con permisos
3. **Playlist**: Reproducir múltiples videos en secuencia
4. **Subtítulos**: Soporte para .srt/.vtt
5. **Calidad Adaptativa**: HLS/DASH streaming
6. **Marcadores**: Guardar posición de reproducción
7. **Compartir**: Links temporales para compartir
8. **Estadísticas**: Analytics de visualización

---

**Este documento describe la arquitectura completa del sistema de reproducción segura de videos.**

Para implementación y uso, consulta:
- `LEEME_PRIMERO.txt` - Inicio rápido
- `REPRODUCTOR_SEGURO.md` - Documentación completa
- `TEST_LOCAL.md` - Pruebas locales

