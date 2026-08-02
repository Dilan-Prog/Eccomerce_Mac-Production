# Recordatorios críticos del proyecto

## Rutas de frontend — NO TOCAR

Nunca modifiques rutas ni vistas de frontend de productos/catálogo (todo lo que no viva bajo `/admin/*`). El sitio público ya está posicionado en buscadores, y cualquier cambio a esas rutas puede arruinar el SEO.

Solo las rutas del panel administrativo (`routes/admin.php`, todo bajo `/admin/*`) pueden modificarse. Si una tarea pareciera requerir tocar una ruta de frontend, detente y pregunta antes de hacerlo.

## Bug conocido: `ImageUploadTrait` no limpia imágenes viejas

`app/Traits/ImageUploadTrait.php`'s `updateImage()` (y `resolveOrCopyImage()`, que reutiliza la misma lógica) nunca borra el archivo de imagen anterior al reemplazarla:

```php
if ($oldaPath && File::exists(UploadPath::full($oldaPath))) {
    File::delete(UploadPath::full($oldaPath));
}
```

El problema: `$oldaPath` es lo que se guarda en la base de datos — una URL completa (ej. `http://localhost/uploads/logo/foo.webp`) — pero `UploadPath::full()` espera una ruta relativa. `File::exists()` siempre da `false`, así que el archivo viejo nunca se borra y queda huérfano en disco (bajo `public/uploads/**` o el `UPLOADS_BASE_PATH` configurado) cada vez que se reemplaza una imagen (Marca, Slider, Productos, etc.).

El método `deleteImage()` en el mismo archivo sí lo hace bien — le quita el prefijo `config('app.url')` antes de resolver la ruta. Ese es el patrón a seguir el día que se arregle este bug.
