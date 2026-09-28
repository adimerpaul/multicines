<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\MovieFooterImage;
use Illuminate\Http\Request;

/**
 * Imagenes de pie de boleto de una pelicula. El archivo llega en la misma
 * peticion y se guarda siempre como WebP reducido: las imagenes pesadas
 * demoraban la impresion del boleto.
 */
class MovieFooterImageController extends Controller
{
    /** Lado mayor maximo en px: el pie mide ~7x6 cm en la termica. */
    const LADO_MAXIMO = 800;
    const CALIDAD_WEBP = 82;

    public function index(Movie $movie)
    {
        return $movie->footerImages()->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'movie_id' => 'required|exists:movies,id',
            'file' => 'required|file|mimes:png,jpg,jpeg,webp',
            'nombre' => 'nullable|string|max:100',
        ], [
            'file.mimes' => 'La imagen debe ser PNG, JPG o WebP',
        ]);

        $imagen = $this->guardarWebp($request->file('file')->getRealPath());
        if ($imagen === null) {
            return response()->json(['message' => 'No se pudo leer la imagen'], 422);
        }

        $orden = MovieFooterImage::where('movie_id', $request->movie_id)->max('orden');

        return MovieFooterImage::create([
            'movie_id' => $request->movie_id,
            'imagen' => $imagen,
            'nombre' => $request->nombre,
            'orden' => $orden === null ? 0 : $orden + 1,
        ]);
    }

    /**
     * Recibe los ids en el orden en que deben imprimirse.
     */
    public function ordenar(Request $request, Movie $movie)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
        ]);

        foreach ($request->ids as $orden => $id) {
            MovieFooterImage::where('id', $id)
                ->where('movie_id', $movie->id)
                ->update(['orden' => $orden]);
        }

        return $movie->footerImages()->get();
    }

    public function destroy(MovieFooterImage $movieFooterImage)
    {
        $movieFooterImage->delete();
    }

    /**
     * Reduce la imagen a LADO_MAXIMO y la guarda en public/imagen como WebP,
     * conservando la transparencia de los PNG. Devuelve el nombre del archivo.
     */
    private function guardarWebp(string $ruta): ?string
    {
        $origen = @imagecreatefromstring(file_get_contents($ruta));
        if (!$origen) {
            return null;
        }

        $ancho = imagesx($origen);
        $alto = imagesy($origen);
        $escala = min(1, self::LADO_MAXIMO / max($ancho, $alto));
        $nuevoAncho = max(1, (int) round($ancho * $escala));
        $nuevoAlto = max(1, (int) round($alto * $escala));

        $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
        imagealphablending($destino, false);
        imagesavealpha($destino, true);
        imagefill($destino, 0, 0, imagecolorallocatealpha($destino, 0, 0, 0, 127));
        imagecopyresampled($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);

        $nombre = 'pie_' . time() . '_' . uniqid() . '.webp';
        $ok = imagewebp($destino, public_path('imagen/' . $nombre), self::CALIDAD_WEBP);

        imagedestroy($origen);
        imagedestroy($destino);

        return $ok ? $nombre : null;
    }
}
