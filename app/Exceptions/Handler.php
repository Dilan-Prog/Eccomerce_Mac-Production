<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Los links firmados de pago-exitoso/transferencia-exitosa (rutas con
        // middleware 'signed') caducan o se invalidan si se reabren después
        // de tiempo, se comparten, o el navegador los precarga dos veces.
        // Antes esto mostraba la página 403 "Invalid signature" genérica de
        // Laravel, sin marca ni forma de volver al sitio — ahora regresa a
        // Inicio con un aviso, igual que cualquier otro link vencido.
        $this->renderable(function (InvalidSignatureException $e, Request $request) {
            toastr('Este enlace ya no es válido o ha caducado.', 'error', 'Enlace inválido');
            return redirect()->route('index');
        });
    }
}
