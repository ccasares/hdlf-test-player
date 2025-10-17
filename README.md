# Visor de Video YouTube

Proyecto web simple para visualizar un video de YouTube con overlays para ocultar elementos no deseados.

## Características

- **Modo de privacidad mejorada**: Usa `youtube-nocookie.com` en lugar de `youtube.com` para mejor privacidad
- **Iframe optimizado**: Configurado con parámetros para minimizar elementos de YouTube
- **Overlays**: Capas transparentes que cubren las áreas donde aparecen botones y sugerencias
- **Diseño moderno**: Fondo degradado y sombras elegantes
- **Responsive**: Se adapta a diferentes tamaños de pantalla

## Parámetros de YouTube utilizados

- **youtube-nocookie.com**: Dominio de privacidad mejorada que no guarda cookies de seguimiento
- `modestbranding=1`: Minimiza el logo de YouTube
- `rel=0`: Reduce videos relacionados al final
- `showinfo=0`: Oculta información del video
- `iv_load_policy=3`: Desactiva anotaciones
- `disablekb=1`: Desactiva controles de teclado (ayuda a reducir overlays)
- `origin`: Parámetro de seguridad para especificar el dominio de origen

## Uso

### Local
Simplemente abre `index.html` en tu navegador web.

### Despliegue en Heroku
Consulta el archivo `DEPLOY.md` para instrucciones detalladas sobre cómo desplegar este proyecto en Heroku.

## Notas

Los overlays ayudan a minimizar la visibilidad de:
- Botón "Ver en YouTube" en la esquina superior derecha
- Sugerencias de videos relacionados cuando se pausa
- Otros elementos de la interfaz de YouTube

⚠️ **Limitación**: Debido a las políticas de YouTube, no es posible eliminar completamente todos los elementos de su interfaz, pero los overlays ayudan a reducir su visibilidad.

