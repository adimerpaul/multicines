<?php

namespace App\Http\Controllers;

use App\Services\ImagenWebp;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    //
    public function upload(Request $request){
        $this->validate($request, [
            'file'=>'required',
        ]);
        if ($request->hasFile('file')) {
            $file=$request->file('file');
            // Opcional: la web de peliculas pide WebP para que las imagenes pesen menos
            if ($request->boolean('webp')) {
                $anchoMaximo = (int) $request->input('max_width', 1280);
                $webp = ImagenWebp::guardar(file_get_contents($file->getRealPath()), '', $anchoMaximo > 0 ? $anchoMaximo : 1280);
                if ($webp) {
                    return $webp;
                }
            }
            $nombreArchivo = time().".".$file->getClientOriginalExtension();
            $file->move(\public_path('imagen'), $nombreArchivo);
        }
        return $nombreArchivo;

    }
}
