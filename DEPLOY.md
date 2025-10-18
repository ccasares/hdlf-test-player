# 🚀 Guía de Despliegue en Heroku

Esta guía te ayudará a desplegar el servidor de videos protegidos en Heroku paso a paso.

## 📋 Prerrequisitos

1. Cuenta en [Heroku](https://signup.heroku.com/)
2. [Heroku CLI](https://devcenter.heroku.com/articles/heroku-cli) instalado
3. Git instalado
4. Este repositorio clonado localmente

## 🔧 Pasos para Desplegar

### 1. Iniciar sesión en Heroku

```bash
heroku login
```

Esto abrirá tu navegador para que te autentiques.

### 2. Crear una aplicación en Heroku

```bash
heroku create nombre-de-tu-app
```

O si quieres que Heroku genere un nombre automático:

```bash
heroku create
```

**Ejemplo**:
```bash
heroku create mi-video-server
# Creará: https://mi-video-server.herokuapp.com
```

### 3. Configurar Variables de Entorno

**Opción A: Desde la terminal**

```bash
# Generar y configurar clave secreta (recomendado)
heroku config:set SECRET_KEY=$(openssl rand -hex 32)

# Configurar dominio autorizado
heroku config:set ALLOWED_DOMAIN=https://midominio.com

# Configurar URL base (usar la URL de tu app de Heroku)
heroku config:set BASE_URL=https://mi-video-server.herokuapp.com
```

**Opción B: Desde el Dashboard de Heroku**

1. Ve a https://dashboard.heroku.com/apps
2. Selecciona tu aplicación
3. Ve a "Settings" > "Config Vars"
4. Añade las siguientes variables:

| Key | Value |
|-----|-------|
| `SECRET_KEY` | `tu-clave-secreta-aqui` |
| `ALLOWED_DOMAIN` | `https://midominio.com` |
| `BASE_URL` | `https://mi-video-server.herokuapp.com` |

### 4. Verificar configuración

```bash
heroku config
```

Deberías ver algo como:
```
=== mi-video-server Config Vars
ALLOWED_DOMAIN: https://midominio.com
BASE_URL:       https://mi-video-server.herokuapp.com
SECRET_KEY:     abc123...xyz789
```

### 5. Preparar el repositorio (si aún no está)

```bash
# Si no has inicializado git:
git init
git add .
git commit -m "Initial commit"
```

### 6. Conectar con Heroku

```bash
# Si creaste la app con heroku create, esto ya está hecho
# Si no, añade el remote manualmente:
heroku git:remote -a nombre-de-tu-app
```

### 7. Desplegar

```bash
git push heroku main
```

O si tu rama principal se llama `master`:

```bash
git push heroku master
```

### 8. Verificar el despliegue

```bash
# Ver logs
heroku logs --tail

# Abrir la aplicación en el navegador
heroku open
```

## 📹 Subir Videos de Prueba (Opcional)

Si quieres incluir videos de prueba en tu repositorio:

```bash
# Crear directorio si no existe
mkdir -p videos

# Copiar videos pequeños (< 100MB recomendado)
cp ~/mis-videos/sample.mp4 videos/

# Añadir al repositorio
git add videos/
git commit -m "Add sample videos"
git push heroku main
```

⚠️ **Nota**: Heroku tiene límites de tamaño de slug (500MB). Para videos grandes, considera usar servicios externos como:
- AWS S3
- Cloudinary
- Vimeo
- YouTube

## 🧪 Probar el Servidor

### 1. Acceder a la página principal

```bash
heroku open
# O visita: https://tu-app.herokuapp.com
```

### 2. Probar la generación de URLs

```bash
# Probar API
curl -X POST https://tu-app.herokuapp.com/generate_url.php \
  -H "Content-Type: application/json" \
  -d '{"file": "sample.mp4", "expires_in": 60}'
```

### 3. Ver el demo interactivo

Visita: `https://tu-app.herokuapp.com/demo.html`

## 🔄 Actualizar el Servidor

Cuando hagas cambios en el código:

```bash
git add .
git commit -m "Descripción de los cambios"
git push heroku main
```

## 🛠️ Comandos Útiles

```bash
# Ver logs en tiempo real
heroku logs --tail

# Reiniciar la aplicación
heroku restart

# Ver información de la app
heroku info

# Abrir shell en Heroku
heroku run bash

# Ver variables de entorno
heroku config

# Añadir variable de entorno
heroku config:set VARIABLE=valor

# Eliminar variable de entorno
heroku config:unset VARIABLE

# Escalar dynos (cambiar plan)
heroku ps:scale web=1

# Ver estado de los dynos
heroku ps
```

## 🐛 Solución de Problemas

### Error: "Application error" al abrir la app

**Solución**:
```bash
heroku logs --tail
```
Revisa los logs para identificar el error específico.

### Error: "composer.lock" no encontrado

**Solución**:
```bash
composer install
git add composer.lock
git commit -m "Add composer.lock"
git push heroku main
```

### Error: Videos no se encuentran (404)

**Causa**: Los videos no están en el repositorio o la ruta es incorrecta.

**Solución**:
1. Verifica que los videos estén en `/videos/`
2. Asegúrate de que estén commiteados en git
3. O usa una URL externa para los videos

### Error: "SECRET_KEY not set"

**Solución**:
```bash
heroku config:set SECRET_KEY=$(openssl rand -hex 32)
```

## 💰 Costos y Límites

### Plan Free (Hobby)
- ✅ Gratis
- ✅ 550 horas/mes (con verificación de tarjeta: 1000 horas)
- ❌ Duerme después de 30 minutos de inactividad
- ❌ Límite de 512MB RAM
- ❌ Límite de 500MB en slug size

### Plan Hobby ($7/mes)
- ✅ No duerme
- ✅ 512MB RAM
- ✅ Métricas y logs mejorados

### Recomendaciones
- Para pruebas: usa el plan Free
- Para producción: usa Hobby o superior
- Para videos grandes: usa almacenamiento externo (S3, Cloudinary)

## 🔐 Seguridad en Producción

### 1. Generar clave secreta robusta

```bash
# Generar clave de 64 caracteres
openssl rand -hex 32
```

### 2. Usar HTTPS

Heroku proporciona HTTPS automáticamente en `*.herokuapp.com`

### 3. Configurar dominio personalizado

```bash
heroku domains:add www.midominio.com
```

Luego configura el DNS en tu proveedor.

### 4. Habilitar verificación de tarjeta

Esto aumenta tus horas gratis y mejora los límites:
```
Dashboard > Account Settings > Billing
```

## 📚 Recursos Adicionales

- [Documentación de Heroku](https://devcenter.heroku.com/)
- [Heroku PHP Support](https://devcenter.heroku.com/articles/getting-started-with-php)
- [Configurar dominio personalizado](https://devcenter.heroku.com/articles/custom-domains)
- [SSL en Heroku](https://devcenter.heroku.com/articles/ssl)

## ✅ Checklist de Despliegue

- [ ] Cuenta de Heroku creada
- [ ] Heroku CLI instalado
- [ ] Aplicación creada (`heroku create`)
- [ ] Variables de entorno configuradas
  - [ ] `SECRET_KEY`
  - [ ] `ALLOWED_DOMAIN`
  - [ ] `BASE_URL`
- [ ] Código commiteado en git
- [ ] Desplegado (`git push heroku main`)
- [ ] Probado en el navegador
- [ ] Demo interactivo funciona
- [ ] API de generación de URLs funciona

---

**¡Listo! Tu servidor de videos protegidos está en producción 🎉**

Si tienes problemas, revisa los logs con `heroku logs --tail` o abre un issue en el repositorio.

