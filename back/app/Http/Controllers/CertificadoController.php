<?php

namespace App\Http\Controllers;

use App\Models\Certificado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CertificadoController extends Controller
{
    const PERMISO = 32;

    private function autorizar(Request $request)
    {
        abort_unless(
            $request->user()->permisos()->where('permisos.id', self::PERMISO)->exists(),
            403,
            'No tiene permiso para gestionar certificados digitales'
        );
    }

    public function index(Request $request)
    {
        $this->autorizar($request);
        return Certificado::with('user:id,name')->orderBy('id', 'desc')->get();
    }

    /**
     * Sube un .p12, extrae la llave privada y el certificado publico, y los guarda en BD.
     */
    public function store(Request $request)
    {
        $this->autorizar($request);
        $request->validate([
            'archivo' => 'required|file|max:2048',
            'password' => 'required|string',
        ]);

        $file = $request->file('archivo');
        $ext = strtolower($file->getClientOriginalExtension());
        if (!in_array($ext, ['p12', 'pfx'])) {
            return response()->json(['message' => 'El archivo debe ser .p12 o .pfx'], 422);
        }

        $contenido = file_get_contents($file->getRealPath());
        if (!openssl_pkcs12_read($contenido, $certs, $request->password)) {
            return response()->json(['message' => 'No se pudo abrir el certificado: la contraseña es incorrecta o el archivo no es válido'], 422);
        }

        $info = openssl_x509_parse($certs['cert']);
        if (!$info) {
            return response()->json(['message' => 'El certificado X.509 no es válido'], 422);
        }

        // Llave privada en PEM sin contraseña (en BD queda cifrada con APP_KEY)
        // openssl_pkcs12_read ya devuelve la llave en PEM sin cifrar
        $privatePem = $certs['pkey'] ?? '';
        $pkey = $privatePem ? openssl_pkey_get_private($privatePem) : false;
        if (!$pkey) {
            return response()->json(['message' => 'No se pudo extraer la llave privada'], 422);
        }
        if (!openssl_x509_check_private_key($certs['cert'], $pkey)) {
            return response()->json(['message' => 'La llave privada no corresponde al certificado'], 422);
        }

        $subject = $info['subject'] ?? [];
        $p12Path = 'certificados/' . date('Ymd_His') . '_' . uniqid() . '.p12';
        Storage::disk('local')->put($p12Path, $contenido);

        $certificado = Certificado::create([
            'archivo' => $file->getClientOriginalName(),
            'titular' => $subject['CN'] ?? null,
            'organizacion' => $subject['O'] ?? null,
            'cargo' => $subject['title'] ?? null,
            'nit' => $subject['serialNumber'] ?? null,
            'email' => $subject['emailAddress'] ?? null,
            'emisor' => $info['issuer']['CN'] ?? null,
            'serial' => $info['serialNumberHex'] ?? ($info['serialNumber'] ?? null),
            'fechaInicio' => date('Y-m-d H:i:s', $info['validFrom_time_t']),
            'fechaFin' => date('Y-m-d H:i:s', $info['validTo_time_t']),
            'publicKey' => $certs['cert'],
            'privateKey' => $privatePem,
            'p12Path' => $p12Path,
            'activo' => false,
            'user_id' => $request->user()->id,
        ]);

        if (filter_var($request->input('activar', false), FILTER_VALIDATE_BOOLEAN)) {
            if ($info['validTo_time_t'] < time()) {
                return response()->json(['message' => 'Certificado guardado, pero está vencido y no se activó'], 422);
            }
            $this->activarCertificado($certificado);
        }

        return $certificado->fresh('user:id,name');
    }

    public function activar(Request $request, Certificado $certificado)
    {
        $this->autorizar($request);
        if ($certificado->fechaFin && strtotime($certificado->fechaFin) < time()) {
            return response()->json(['message' => 'El certificado está vencido, no se puede activar'], 422);
        }
        $this->activarCertificado($certificado);
        return $certificado->fresh('user:id,name');
    }

    public function destroy(Request $request, Certificado $certificado)
    {
        $this->autorizar($request);
        if ($certificado->activo) {
            return response()->json(['message' => 'No se puede eliminar el certificado activo'], 422);
        }
        $certificado->delete();
        return response()->json(['message' => 'Certificado eliminado']);
    }

    /**
     * Escribe las llaves en storage/key/, que es de donde Firmar.php las lee al firmar
     * las facturas. Antes respalda los archivos anteriores.
     */
    private function activarCertificado(Certificado $certificado)
    {
        $dir = storage_path('key');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $stamp = date('Ymd_His');
        foreach (['privateKey', 'publicKey'] as $f) {
            if (file_exists("$dir/$f.pem")) {
                copy("$dir/$f.pem", "$dir/$f.bak_$stamp.pem");
            }
        }
        file_put_contents("$dir/privateKey.pem", $certificado->privateKey);
        file_put_contents("$dir/publicKey.pem", $certificado->publicKey);

        DB::transaction(function () use ($certificado) {
            Certificado::where('id', '!=', $certificado->id)->update(['activo' => false]);
            $certificado->activo = true;
            $certificado->fechaActivacion = now();
            $certificado->save();
        });
    }
}
