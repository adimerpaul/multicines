<?php

namespace App\Http\Controllers;

use App\Http\Middleware\CineToken;
use App\Models\Programa;
use App\Models\WebMovie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Web publica de la cartelera (Blade). Usa los datos de Peliculas Web
 * (web_movies + programas vinculados en web_movie_programa).
 * Por ahora solo muestra peliculas y deja elegir asientos; el pago QR y la
 * factura son solo diseno, no se guarda nada.
 */
class CineController extends Controller
{
    const DIAS_CARTELERA = 7;
    const DOW = ['dom', 'lun', 'mar', 'mié', 'jue', 'vie', 'sáb'];
    const MESES = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

    public function home(Request $request)
    {
        $peliculas = WebMovie::with(['programas' => fn($q) => $this->filtrarFunciones($q)])
            ->where('estado', 'ACTIVO')
            ->where('tipo', 'pelicula')
            ->orderByDesc('puntaje_web')
            ->orderByDesc('popularidad')
            ->get()
            ->map(fn($m) => $this->peliculaVista($m));

        $hero = $peliculas->filter(fn($m) => in_array($m['carrusel'], ['carrusel_1', 'ambos']) && $m['backdrop'])->values();
        if ($hero->isEmpty()) {
            $hero = $peliculas->filter(fn($m) => $m['backdrop'])->take(6)->values();
        }

        $proximos = WebMovie::where('estado', 'ACTIVO')
            ->where('tipo', 'proximo')
            ->orderBy('fecha_estreno')
            ->get()
            ->map(fn($m) => $this->peliculaVista($m));

        return view('cine.home', [
            'cineToken' => CineToken::issue($request),
            'hero' => $hero->take(6),
            'peliculas' => $peliculas,
            'proximos' => $proximos,
            'dias' => $this->dias(),
        ]);
    }

    public function comprar(Request $request, $id)
    {
        $movie = WebMovie::with(['programas' => fn($q) => $this->filtrarFunciones($q), 'actores', 'studio'])
            ->where('estado', 'ACTIVO')
            ->where('tipo', 'pelicula')
            ->findOrFail((int) $id);

        $pelicula = $this->peliculaVista($movie) + $this->fichaVista($movie);
        $dias = collect($this->dias())
            ->filter(fn($d) => collect($pelicula['funciones'])->contains('fecha', $d['fecha']))
            ->values();

        return view('cine.comprar', [
            'cineToken' => CineToken::issue($request),
            'pelicula' => $pelicula,
            'dias' => $dias,
            'cineData' => [
                'funciones' => $pelicula['funciones'],
                'ventaOnline' => $pelicula['ventaOnline'],
                'funcionInicial' => (int) $request->query('funcion', 0),
                'diaInicial' => (string) $request->query('dia', ''),
                'asientosUrl' => route('cine.asientos'),
                'inicioUrl' => route('cine.home'),
            ],
        ]);
    }

    /**
     * Mapa de asientos de una funcion. Solo responde para funciones vigentes
     * vinculadas a una pelicula web activa (ver middleware cine.token).
     */
    public function asientos(Request $request)
    {
        $data = $request->validate(['programa_id' => 'required|integer|min:1']);

        $programa = Programa::with('sala', 'price', 'movie')
            ->where('id', $data['programa_id'])
            ->where('activo', 'ACTIVO')
            ->where('horaInicio', '>=', now())
            ->whereHas('webMovies', fn($q) => $q->where('estado', 'ACTIVO'))
            ->first();

        if (!$programa) {
            return response()->json(['message' => 'Funcion no disponible'], 404);
        }

        $id = $programa->id;
        $seats = DB::table('seats as a')
            ->where('a.sala_id', $programa->sala_id)
            ->whereNull('a.deleted_at')
            ->select('a.fila', 'a.columna', 'a.letra', 'a.activo')
            ->selectRaw('EXISTS(SELECT 1 FROM tickets t WHERE t.programa_id = ? AND t.fila = a.fila AND t.columna = a.columna AND t.letra = a.letra AND t.devuelto = 0 AND t.deleted_at IS NULL) AS ocupado', [$id])
            ->selectRaw('EXISTS(SELECT 1 FROM momentaneos m WHERE m.programa_id = ? AND m.fila = a.fila AND m.columna = a.columna AND m.letra = a.letra) AS reservado', [$id])
            ->orderBy('a.fila')
            ->orderByDesc('a.columna')
            ->get()
            ->map(fn($s) => [
                'f' => (int) $s->fila,
                'c' => (int) $s->columna,
                'l' => $s->letra,
                'e' => $s->activo !== 'ACTIVO' ? 'I' : ($s->ocupado ? 'O' : ($s->reservado ? 'R' : 'L')),
            ]);

        // Bloqueada si la pelicula web o esta funcion en particular estan en "solo boleteria"
        $ventaOnline = !$programa->webMovies()
            ->where(fn($q) => $q->where('web_movies.venta_online', false)
                ->orWhere('web_movie_programa.venta_online', false))
            ->exists();

        return response()->json([
            'sala' => [
                'nombre' => $programa->sala->nombre,
                'filas' => (int) $programa->sala->filas,
                'columnas' => (int) $programa->sala->columnas,
            ],
            'precio' => (float) optional($programa->price)->precio,
            'venta_online' => $ventaOnline,
            'asientos' => $seats,
        ]);
    }

    private function filtrarFunciones($query)
    {
        $hasta = now()->startOfDay()->addDays(self::DIAS_CARTELERA - 1)->toDateString();

        return $query->where('programas.activo', 'ACTIVO')
            ->where('programas.horaInicio', '>=', now())
            ->where('programas.fecha', '<=', $hasta)
            ->with('movie:id,formato,clasificacion,imagen', 'sala:id,nombre', 'price:id,precio')
            ->orderBy('programas.horaInicio');
    }

    private function peliculaVista(WebMovie $m): array
    {
        $funciones = $m->relationLoaded('programas') ? $m->programas : collect();
        $primera = $funciones->first();
        $poster = $m->imagen ?: optional(optional($primera)->movie)->imagen;

        return [
            'id' => $m->id,
            'titulo' => $m->titulo,
            'carrusel' => $m->carrusel_tipo,
            'poster' => asset('imagen/' . ($poster ?: 'default.jpg')),
            'backdrop' => $m->backdrop_imagen ? asset('imagen/' . $m->backdrop_imagen) : null,
            'genero' => $m->genero,
            'duracion' => $m->duracion,
            'clasificacion' => $m->clasificacion ?: optional(optional($primera)->movie)->clasificacion ?: 'ATP',
            'sinopsis' => $m->descripcion,
            'tagline' => $m->tagline,
            'estreno' => $m->fecha_estreno && $m->fecha_estreno >= now()->subDays(7)->toDateString(),
            'fechaEstreno' => $m->fecha_estreno ? $this->fechaCorta($m->fecha_estreno) : 'Fecha por anunciar',
            'trailer' => $this->youtubeId($m->trailer_youtube ?: $m->url_video_youtube),
            'ventaOnline' => $m->venta_online !== false,
            'funciones' => $funciones->map(fn($p) => [
                'id' => $p->id,
                'fecha' => date('Y-m-d', strtotime($p->fecha)),
                'hora' => date('H:i', strtotime($p->horaInicio)),
                'formato' => optional($p->movie)->formato ?: '2D',
                'sala' => optional($p->sala)->nombre,
                'precio' => (float) optional($p->price)->precio,
                'subtitulada' => $p->subtitulada === 'SI',
                'ventaOnline' => $m->venta_online !== false && (bool) optional($p->pivot)->venta_online,
            ])->values()->all(),
        ];
    }

    /**
     * Datos extra de la ficha (solo en la pagina de compra): puntajes, reparto y detalles.
     */
    private function fichaVista(WebMovie $m): array
    {
        $num = fn($v) => $v !== null && $v !== '' && (float) $v > 0 ? round((float) $v, 1) : null;
        $limpiar = fn(?string $v) => $v ? trim(preg_replace('/\s*,\s*(,\s*)*/', ', ', $v), ", \t") ?: null : null;

        $puntajes = array_values(array_filter([
            $num($m->puntaje_web) ? ['nombre' => 'Puntaje', 'valor' => $num($m->puntaje_web), 'escala' => '/10', 'principal' => true] : null,
            $num($m->puntaje_ibm) ? ['nombre' => 'IMDb', 'valor' => $num($m->puntaje_ibm), 'escala' => '/10', 'principal' => false] : null,
            $num($m->puntaje_tomatoes) ? ['nombre' => 'Tomatoes', 'valor' => $num($m->puntaje_tomatoes), 'escala' => '/10', 'principal' => false] : null,
            $num($m->puntaje_metacritic) ? ['nombre' => 'Metacritic', 'valor' => $num($m->puntaje_metacritic), 'escala' => '/10', 'principal' => false] : null,
        ]));

        $detalles = array_filter([
            'Estreno' => $m->fecha_estreno ? $this->fechaCorta($m->fecha_estreno) : null,
            'Estudio' => optional($m->studio)->nombre,
            'País' => $limpiar($m->pais),
            'Idioma' => $limpiar($m->idioma),
            'Calidad' => $m->calidad,
            'Premios' => $m->premios,
        ]);

        return [
            'anio' => $m->anio,
            'puntajes' => $puntajes,
            'votos' => $m->votos_total ? number_format($m->votos_total, 0, ',', '.') : null,
            'detalles' => $detalles,
            'urlOficial' => $m->url_oficial && preg_match('~^https?://~i', $m->url_oficial) ? $m->url_oficial : null,
            'reparto' => $m->actores->take(12)->map(fn($a) => [
                'nombre' => $a->nombre,
                'foto' => $a->imagen ? asset('imagen/' . $a->imagen) : null,
            ])->values()->all(),
        ];
    }

    private function dias(): array
    {
        $dias = [];
        for ($i = 0; $i < self::DIAS_CARTELERA; $i++) {
            $d = now()->startOfDay()->addDays($i);
            $dias[] = [
                'fecha' => $d->toDateString(),
                'dow' => $i === 0 ? 'hoy' : self::DOW[$d->dayOfWeek],
                'num' => $d->day,
                'mes' => self::MESES[$d->month - 1],
                'largo' => self::DOW[$d->dayOfWeek] . ' ' . $d->day . ' ' . self::MESES[$d->month - 1],
            ];
        }

        return $dias;
    }

    private function fechaCorta($fecha): string
    {
        $t = strtotime($fecha);

        return date('j', $t) . ' ' . self::MESES[date('n', $t) - 1] . ' ' . date('Y', $t);
    }

    private function youtubeId(?string $url): ?string
    {
        if (!$url) {
            return null;
        }
        if (preg_match('~(?:youtu\.be/|v=|embed/|shorts/)([A-Za-z0-9_-]{11})~', $url, $m)) {
            return $m[1];
        }

        return null;
    }
}
