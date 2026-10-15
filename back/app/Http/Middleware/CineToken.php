<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Token de la web publica (Blade). La pagina lo recibe al renderizarse y lo
 * manda en cada llamada AJAX; el servidor solo responde si coincide con el de
 * la sesion, la peticion viene del mismo dominio y el token no vencio.
 * Asi los endpoints de la cartelera solo funcionan desde estas paginas.
 */
class CineToken
{
    const SESSION_KEY = 'cine_token';
    const SESSION_AT = 'cine_token_at';
    const TTL_SECONDS = 7200;

    public static function issue(Request $request): string
    {
        $session = $request->session();
        $token = $session->get(self::SESSION_KEY);
        $at = (int) $session->get(self::SESSION_AT, 0);

        if (!$token || time() - $at > self::TTL_SECONDS) {
            $token = Str::random(64);
            $session->put(self::SESSION_KEY, $token);
            $session->put(self::SESSION_AT, time());
        }

        return $token;
    }

    public function handle(Request $request, Closure $next)
    {
        $session = $request->session();
        $token = (string) $session->get(self::SESSION_KEY, '');
        $at = (int) $session->get(self::SESSION_AT, 0);
        $enviado = (string) $request->header('X-Cine-Token', '');

        if ($token === '' || $enviado === '' || !hash_equals($token, $enviado)) {
            return $this->rechazar('Token invalido');
        }
        if (time() - $at > self::TTL_SECONDS) {
            return $this->rechazar('La sesion vencio, recarga la pagina', 419);
        }
        if ($request->header('X-Requested-With') !== 'XMLHttpRequest') {
            return $this->rechazar('Peticion no permitida');
        }
        if (!$this->mismoOrigen($request)) {
            return $this->rechazar('Origen no permitido');
        }

        $response = $next($request);
        $response->headers->set('Cache-Control', 'no-store, private');

        return $response;
    }

    private function mismoOrigen(Request $request): bool
    {
        $origen = $request->header('Origin') ?: $request->header('Referer');
        if (!$origen) {
            return false;
        }
        $host = parse_url($origen, PHP_URL_HOST);
        $port = parse_url($origen, PHP_URL_PORT);

        return $host === $request->getHost() && (int) ($port ?: ($request->isSecure() ? 443 : 80)) === (int) $request->getPort();
    }

    private function rechazar(string $mensaje, int $status = 403)
    {
        return response()->json(['message' => $mensaje], $status);
    }
}
