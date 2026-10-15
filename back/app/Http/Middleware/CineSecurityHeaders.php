<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Cabeceras de seguridad para la web publica: sin scripts ni estilos inline,
 * sin iframes de terceros salvo el reproductor de YouTube y sin poder meter la
 * pagina dentro de otro sitio.
 */
class CineSecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $csp = implode('; ', [
            "default-src 'self'",
            // gstatic/apis.google.com: SDK de Firebase y popup de Google (solo login)
            "script-src 'self' https://www.gstatic.com https://apis.google.com",
            "style-src 'self' https://fonts.googleapis.com",
            "font-src 'self' https://fonts.gstatic.com",
            "img-src 'self' data: https://lh3.googleusercontent.com",
            // www.gstatic.com: los .map del SDK de Firebase que pide DevTools
            "connect-src 'self' https://identitytoolkit.googleapis.com https://securetoken.googleapis.com https://www.googleapis.com https://www.gstatic.com",
            "frame-src https://www.youtube-nocookie.com https://" . (config('services.firebase.auth_domain') ?: 'localhost'),
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
        ]);

        $response->headers->set('Content-Security-Policy', $csp);
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'same-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        return $response;
    }
}
