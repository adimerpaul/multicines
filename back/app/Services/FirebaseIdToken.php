<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Verifica un ID token de Firebase Auth (JWT RS256 firmado por Google) sin SDK:
 * firma con los certificados publicos de Google, audiencia, emisor y fechas.
 * Se usa una sola vez al iniciar sesion; despues manda la sesion de Laravel.
 */
class FirebaseIdToken
{
    private const CERTS_URL = 'https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com';
    private const LEEWAY = 60;

    public function __construct(private string $projectId)
    {
        if ($projectId === '') {
            throw new RuntimeException('FIREBASE_PROJECT_ID no configurado');
        }
    }

    /**
     * @return array claims del token (sub, email, name, picture, firebase.sign_in_provider...)
     */
    public function verificar(string $jwt): array
    {
        $partes = explode('.', $jwt);
        if (count($partes) !== 3) {
            throw new RuntimeException('Token mal formado');
        }
        [$h64, $p64, $s64] = $partes;

        $header = json_decode($this->b64($h64), true);
        $claims = json_decode($this->b64($p64), true);
        if (!is_array($header) || !is_array($claims)) {
            throw new RuntimeException('Token ilegible');
        }
        if (($header['alg'] ?? '') !== 'RS256' || empty($header['kid'])) {
            throw new RuntimeException('Algoritmo no permitido');
        }

        $cert = $this->certificados()[$header['kid']] ?? $this->certificados(true)[$header['kid']] ?? null;
        if (!$cert) {
            throw new RuntimeException('Certificado desconocido');
        }
        $ok = openssl_verify($h64 . '.' . $p64, $this->b64($s64), $cert, OPENSSL_ALGO_SHA256);
        if ($ok !== 1) {
            throw new RuntimeException('Firma invalida');
        }

        $ahora = time();
        if (($claims['aud'] ?? null) !== $this->projectId) {
            throw new RuntimeException('Audiencia invalida');
        }
        if (($claims['iss'] ?? null) !== 'https://securetoken.google.com/' . $this->projectId) {
            throw new RuntimeException('Emisor invalido');
        }
        if (($claims['exp'] ?? 0) < $ahora - self::LEEWAY) {
            throw new RuntimeException('Token vencido');
        }
        if (($claims['iat'] ?? PHP_INT_MAX) > $ahora + self::LEEWAY || ($claims['auth_time'] ?? PHP_INT_MAX) > $ahora + self::LEEWAY) {
            throw new RuntimeException('Token con fecha futura');
        }
        if (empty($claims['sub']) || !is_string($claims['sub']) || strlen($claims['sub']) > 128) {
            throw new RuntimeException('Usuario invalido');
        }

        return $claims;
    }

    /** Certificados publicos de Google, cacheados segun su Cache-Control. */
    private function certificados(bool $refrescar = false): array
    {
        $key = 'firebase_securetoken_certs';
        if ($refrescar) {
            Cache::forget($key);
        }
        $cache = Cache::get($key);
        if (is_array($cache)) {
            return $cache;
        }

        $res = Http::timeout(10)->get(self::CERTS_URL);
        if (!$res->ok() || !is_array($res->json())) {
            throw new RuntimeException('No se pudo obtener los certificados de Google');
        }
        $ttl = 3600;
        if (preg_match('/max-age=(\d+)/', (string) $res->header('Cache-Control'), $m)) {
            $ttl = max(60, (int) $m[1]);
        }
        Cache::put($key, $res->json(), $ttl);

        return $res->json();
    }

    private function b64(string $s): string
    {
        return (string) base64_decode(strtr($s, '-_', '+/') . str_repeat('=', (4 - strlen($s) % 4) % 4));
    }
}
