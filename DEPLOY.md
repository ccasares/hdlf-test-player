# Guía de Despliegue en Heroku

## Escenario: App de Heroku ya creada y vinculada a GitHub

Si ya tienes una app de Heroku creada y vinculada a un repositorio de GitHub, sigue estos pasos:

### 1. Subir los archivos a tu repositorio de GitHub

```bash
cd /Users/ccasares/Downloads/testss

# Si ya tienes el repo clonado, solo añade los archivos
git add .
git commit -m "Add video viewer project with Heroku config"
git push origin main
```

O si tu rama principal es `master`:

```bash
git push origin master
```

### 2. Despliegue automático

Si tienes el **despliegue automático activado** en Heroku:
- Heroku detectará el push a GitHub automáticamente
- Iniciará el build y despliegue
- Puedes ver el progreso en el dashboard de Heroku

Si **NO** tienes despliegue automático:
- Ve a tu app en el [Dashboard de Heroku](https://dashboard.heroku.com/)
- Ve a la pestaña "Deploy"
- En "Manual deploy", selecciona la rama y haz clic en "Deploy Branch"

### 3. Verificar el despliegue

```bash
# Ver logs en tiempo real
heroku logs --tail -a nombre-de-tu-app

# Abrir la aplicación
heroku open -a nombre-de-tu-app
```

---

## Escenario alternativo: Nueva instalación

### Requisitos previos

1. Tener una cuenta en [Heroku](https://www.heroku.com/)
2. Instalar [Heroku CLI](https://devcenter.heroku.com/articles/heroku-cli)
3. Tener Git instalado

### 1. Inicializar repositorio Git (si no existe)

```bash
cd /Users/ccasares/Downloads/testss
git init
git add .
git commit -m "Initial commit"
```

### 2. Login en Heroku

```bash
heroku login
```

### 3. Crear aplicación en Heroku

```bash
heroku create nombre-de-tu-app
```

### 4. Desplegar a Heroku

```bash
git push heroku master
```

### 5. Abrir la aplicación

```bash
heroku open
```

## Comandos útiles

### Ver logs de la aplicación
```bash
heroku logs --tail
```

### Ver información de la app
```bash
heroku info
```

### Reiniciar la aplicación
```bash
heroku restart
```

### Eliminar la aplicación
```bash
heroku apps:destroy nombre-de-tu-app
```

## Actualizaciones futuras

Cuando hagas cambios en el código:

```bash
git add .
git commit -m "Descripción de los cambios"
git push heroku master
```

## Notas

- Heroku detectará automáticamente que es una aplicación PHP por el archivo `composer.json`
- El `Procfile` le indica a Heroku que use Apache para servir los archivos
- El `index.php` sirve el contenido HTML estático

## URL de tu aplicación

Después del despliegue, tu aplicación estará disponible en:
`https://nombre-de-tu-app.herokuapp.com`

