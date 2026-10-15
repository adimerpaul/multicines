<?php

namespace App\Services;

/**
 * Convierte imagenes a WebP (y las achica) antes de guardarlas en public/imagen.
 *
 * Las imagenes de la web de peliculas se sirven por internet: un poster
 * "original" de TMDB pesa 1-3 MB en JPG y como WebP a 780px queda en ~60-120 KB.
 * Si GD no puede leer/escribir la imagen se devuelve null y el llamador guarda
 * el archivo original tal cual.
 */
class ImagenWebp
{
    /**
     * Guarda los bytes como .webp dentro de public/imagen/{subDirectorio}.
     * Devuelve la ruta relativa a public/imagen (ej. "webmovies/123_abc.webp") o null.
     */
    public static function guardar(string $bytes, string $subDirectorio = '', int $anchoMaximo = 1280, int $calidad = 80): ?string
    {
        if (!function_exists('imagewebp')) {
            return null;
        }

        $imagen = @imagecreatefromstring($bytes);
        if ($imagen === false) {
            return null;
        }

        try {
            if (!imageistruecolor($imagen)) {
                imagepalettetotruecolor($imagen);
            }

            if (imagesx($imagen) > $anchoMaximo) {
                $escalada = imagescale($imagen, $anchoMaximo, -1, IMG_BICUBIC);
                if ($escalada !== false) {
                    imagedestroy($imagen);
                    $imagen = $escalada;
                }
            }

            imagealphablending($imagen, false);
            imagesavealpha($imagen, true);

            $subDirectorio = trim($subDirectorio, '/');
            $directorio = public_path('imagen' . ($subDirectorio !== '' ? '/' . $subDirectorio : ''));
            if (!is_dir($directorio)) {
                mkdir($directorio, 0755, true);
            }

            $nombre = time() . '_' . \Illuminate\Support\Str::random(8) . '.webp';
            if (!imagewebp($imagen, $directorio . DIRECTORY_SEPARATOR . $nombre, $calidad)) {
                return null;
            }

            return ($subDirectorio !== '' ? $subDirectorio . '/' : '') . $nombre;
        } finally {
            imagedestroy($imagen);
        }
    }
}
