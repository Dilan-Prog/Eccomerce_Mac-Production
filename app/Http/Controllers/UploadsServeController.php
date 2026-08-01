<?php

namespace App\Http\Controllers;

use App\Support\UploadPath;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sirve /uploads/{path} leyendo desde UploadPath::base() en vez de
 * public/uploads. Apache solo llega hasta acá (vía .htaccess) cuando el
 * archivo pedido ya NO existe físicamente en public/ — que es el caso una
 * vez que UPLOADS_BASE_PATH apunta fuera del árbol deployado. Sin auth
 * porque son los mismos assets públicos que antes servía Apache
 * directamente.
 */
class UploadsServeController extends Controller
{
    public function show(string $path): Response
    {
        $base = realpath(UploadPath::full('uploads'));
        $full = realpath(UploadPath::full('uploads/' . $path));

        // La comprobación de que $full realmente cuelga de $base evita path
        // traversal (../../algo-sensible) a través del parámetro de ruta.
        if (!$base || !$full || !str_starts_with($full, $base) || !is_file($full)) {
            abort(404);
        }

        return response()->file($full, [
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
