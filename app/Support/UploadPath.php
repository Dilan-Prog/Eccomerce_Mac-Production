<?php

namespace App\Support;

/**
 * Punto central que resuelve dónde viven físicamente los archivos subidos
 * por usuarios (imágenes de producto, galería, logos, avatares, banners de
 * slider — todo lo que hoy cuelga de public_path('uploads/...')).
 *
 * Se apoya en UPLOADS_BASE_PATH para poder apuntar fuera de public_html en
 * producción: el auto-deploy vía git resetea el árbol deployado en cada
 * push, lo que borraría cualquier cosa bajo public/uploads que no esté
 * trackeada en git (está en .gitignore a propósito, porque es contenido
 * subido en tiempo de ejecución, no código). Cuando UPLOADS_BASE_PATH no
 * está seteada (dev local), cae en public_path() para no romper el
 * comportamiento actual.
 */
class UploadPath
{
    public static function base(): string
    {
        return env('UPLOADS_BASE_PATH') ?: public_path();
    }

    public static function full(string $relativePath): string
    {
        return static::base() . '/' . ltrim($relativePath, '/');
    }
}
