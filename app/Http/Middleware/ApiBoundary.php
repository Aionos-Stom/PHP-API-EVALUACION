<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class ApiBoundary
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $response = $this->process($request, $next);
        } catch (Throwable $error) {
            $handler = app(ExceptionHandler::class);
            $handler->report($error);
            $response = $handler->render($request, $error);
        }

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Content-Security-Policy', "default-src 'none'; frame-ancestors 'none'");
        $response->headers->set('Referrer-Policy', 'no-referrer');
        $response->headers->set('Cache-Control', 'no-store');
        $response->headers->remove('X-Powered-By');

        return $response;
    }

    private function process(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');
        // Única ruta pública. Un endpoint nuevo queda protegido automáticamente.
        if ($request->getPathInfo() === '/health' && $request->isMethod('GET')) {
            return $next($request);
        }

        $configuredHash = (string) config('api.token_hash');
        abort_unless(preg_match('/\A[a-f0-9]{64}\z/', $configuredHash) === 1, 503);
        abort_unless(config('app.key'), 503);
        // El servidor incorporado y SQLite se entregan para evaluación local.
        abort_unless(app()->environment('local', 'testing'), 503);

        $limit = max(1, (int) config('api.rate_limit'));
        $key = 'api-ip:'.hash('sha256', (string) $request->ip());
        if (RateLimiter::tooManyAttempts($key, $limit)) {
            return response()->json(['message' => 'Demasiadas solicitudes.'], 429, [
                'Retry-After' => (string) RateLimiter::availableIn($key),
            ]);
        }
        RateLimiter::hit($key, 60);

        $token = $request->bearerToken();
        if (! is_string($token) || ! hash_equals($configuredHash, hash('sha256', $token))) {
            return response()->json(['message' => 'No autorizado.'], 401, [
                'WWW-Authenticate' => 'Bearer',
            ]);
        }

        $maximum = max(1, (int) config('api.max_body_bytes'));
        abort_if((int) $request->header('Content-Length', '0') > $maximum, 413);
        // Comprueba los bytes reales, también sin Content-Length.
        abort_if(strlen($request->getContent()) > $maximum, 413);
        if ($request->isMethod('POST') || $request->isMethod('PUT')) {
            abort_unless($request->isJson(), 415);
            try {
                $decoded = json_decode($request->getContent(), false, 32, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                abort(400);
            }
            abort_unless($decoded instanceof \stdClass, 400);
        }

        return $next($request);
    }
}
