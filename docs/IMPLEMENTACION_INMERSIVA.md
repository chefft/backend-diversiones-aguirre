# Implementacion Inmersiva

## Flujo de assets (modelos y 360)

1. Guarda tus fuentes `.blend` en:
`storage/app/public/models/source/`

2. Exporta cada modelo desde Blender como `glTF 2.0 (.glb)` y guardalo en:
`storage/app/public/models/`

3. Guarda imagenes 360 equirectangulares (JPG/PNG 2:1) en:
`storage/app/public/panoramas/`

4. Crea el enlace publico de storage:

```bash
php artisan storage:link
```

5. En base de datos guarda rutas relativas:
- `games.model_3d_path`: `models/rueda.glb`
- `galleries_360.image_path`: `panoramas/plaza-central.jpg`

El backend expone automaticamente URL publicas (`model_3d_url`, `image_url`).

## Cardboard (movil)

1. Abre la app desde HTTPS o localhost.
2. Entra al tab `Panorama 360`.
3. Pulsa `Activar Cardboard`.
4. Da permiso de sensores/giroscopio cuando el navegador lo pida.
5. Coloca el telefono en el visor Cardboard.

Si el navegador no permite giroscopio, el modo Cardboard sigue en estereo y puedes orientar con toque.

## Recomendaciones de calidad visual

- Modelos `.glb` idealmente menores a 15-25 MB.
- Texturas comprimidas (2K suficiente para movil).
- Panoramas 360 en 4096x2048 o 6144x3072.
- Evita texturas innecesarias sin uso para acelerar carga.
