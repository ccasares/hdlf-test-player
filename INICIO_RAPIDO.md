# ⚡ Inicio Rápido

Configuración básica en 5 minutos.

## 🎯 Para Heroku (Recomendado para principiantes)

```bash
# 1. Instalar Heroku CLI
# Visita: https://devcenter.heroku.com/articles/heroku-cli

# 2. Login
heroku login

# 3. Crear app
heroku create mi-video-server

# 4. Configurar variables
heroku config:set SECRET_KEY=$(openssl rand -hex 32)
heroku config:set ALLOWED_DOMAIN=https://midominio.com
heroku config:set BASE_URL=https://mi-video-server.herokuapp.com

# 5. Desplegar
git push heroku main

# 6. Abrir en navegador
heroku open
```

**¡Listo!** Tu servidor está funcionando en: `https://mi-video-server.herokuapp.com`

---

## 🏠 Para Synology NAS

### Paso 1: Configurar archivo .env

Crea un archivo `.env` en la raíz con este contenido:

```env
SECRET_KEY=genera-una-clave-con-openssl-rand-hex-32
ALLOWED_DOMAIN=https://midominio.com
VIDEO_PATH=/volume1/web/videos/
BASE_URL=https://midominio.com
```

**Generar clave secreta**:
```bash
openssl rand -hex 32
```

### Paso 2: Subir archivos al NAS

Sube todos los archivos PHP a `/volume1/web/`:

```
/volume1/web/
├── video.php
├── generate_url.php
├── index.php
├── demo.html
├── .env
└── videos/
    └── (tus archivos de video aquí)
```

### Paso 3: Configurar Web Station

1. Abre **Web Station** en DSM
2. Crea un **Virtual Host** nuevo
3. Selecciona PHP 7.4 o superior
4. Apunta al directorio `/volume1/web/`

### Paso 4: Probar

Visita `https://tu-nas.com/demo.html`

---

## 💻 Para desarrollo local

```bash
# 1. Crear archivo .env (ver CONFIGURACION_ENV.txt)

# 2. Iniciar servidor PHP
php -S localhost:8000

# 3. Abrir navegador
# Visita: http://localhost:8000
```

---

## 🧪 Prueba rápida

### Generar URL de prueba

```bash
php generate_url.php test.mp4 60
```

### Probar API

```bash
curl -X POST http://localhost:8000/generate_url.php \
  -H "Content-Type: application/json" \
  -d '{"file": "test.mp4", "expires_in": 60}'
```

---

## 📱 Uso básico

### En tu HTML

```html
<video id="player" controls width="800"></video>

<script>
fetch('/generate_url.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({file: 'video1.mp4', expires_in: 60})
})
.then(r => r.json())
.then(data => {
    document.getElementById('player').src = data.url;
});
</script>
```

---

## ❓ ¿Problemas?

### Error 403: Invalid referer
- Verifica que `ALLOWED_DOMAIN` coincida con tu dominio
- Para localhost en desarrollo, usa `http://localhost`

### Error 404: Video not found
- Verifica que el video existe en `VIDEO_PATH`
- Verifica que la ruta en `.env` es correcta

### Videos no cargan
- Revisa los logs del servidor
- Verifica permisos de lectura en los archivos
- Asegúrate de que PHP tiene acceso a la carpeta de videos

---

## 📚 Más información

- **Documentación completa**: Ver `README.md`
- **Despliegue en Heroku**: Ver `DEPLOY.md`
- **Configuración .env**: Ver `CONFIGURACION_ENV.txt`

---

**¡Disfruta de tus videos protegidos! 🎬**

