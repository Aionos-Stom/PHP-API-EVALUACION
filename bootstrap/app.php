<?php

declare(strict_types=1);

use App\Http\Middleware\ApiBoundary;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(api: __DIR__.'/../routes/api.php', apiPrefix: '')
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(ApiBoundary::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(fn (Request $request, Throwable $error): bool => true);
        $exceptions->render(function (Throwable $error, Request $request) {
            $status = match (true) {
                $error instanceof ValidationException => 422,
                $error instanceof ModelNotFoundException => 404,
                $error instanceof UniqueConstraintViolationException => 409,
                $error instanceof HttpExceptionInterface => $error->getStatusCode(),
                default => 500,
            };
            $messages = [
                400 => 'Solicitud JSON inválida.',
                401 => 'No autorizado.',
                403 => 'Acceso denegado.',
                404 => 'Recurso no encontrado.',
                405 => 'Método no permitido.',
                409 => 'Ya existe un precio para este producto y divisa.',
                413 => 'El cuerpo supera el tamaño permitido.',
                415 => 'Utiliza Content-Type: application/json.',
                422 => 'Datos de entrada inválidos.',
                429 => 'Demasiadas solicitudes.',
                503 => 'Servicio no disponible.',
            ];
            $body = ['message' => $messages[$status] ?? 'Error interno del servidor.'];
            if ($error instanceof ValidationException) {
                $body['errors'] = $error->errors();
            }

            return response()->json(
                $body,
                $status,
                $error instanceof HttpExceptionInterface ? $error->getHeaders() : [],
            );
        });
        // SQL, bindings y credenciales no se escriben en los registros de excepciones.
        $exceptions->report(function (Throwable $error): void {
            logger()->error('Fallo de aplicación.', ['exception_type' => $error::class]);
        })->stop();
    })->create();
