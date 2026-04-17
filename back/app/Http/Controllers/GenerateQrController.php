<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Throwable;

class GenerateQrController extends Controller
{
    private string $baseUrl = 'https://apimktdesa.baneco.com.bo/ApiGateway';
    private string $aesKey = '40A318B299F245C2B697176723088629';
    private string $userName = '26551010';
    private string $passwordPlain = '1234';
    private string $accountCredit = '1061602532';
    private string $currency = 'BOB';
    private bool $singleUse = true;
    private bool $modifyAmount = false;
    private string $branchCode = 'E0001';

    public function generarQr(Request $request)
    {
        $payload = $request->validate([
            'client' => 'nullable|array',
            'client.nombreRazonSocial' => 'nullable|string',
            'client.numeroDocumento' => 'nullable|string',
            'montoTotal' => 'required|numeric|min:0.01',
        ]);

        try {
            $client = $payload['client'] ?? [];
            $amount = round((float) $payload['montoTotal'], 2);
            $clientName = trim((string) ($client['nombreRazonSocial'] ?? 'CLIENTE'));
            $clientDocument = trim((string) ($client['numeroDocumento'] ?? ''));
            $description = 'Candy ' . ($clientName !== '' ? $clientName : 'CLIENTE');
            $transactionId = 'CANDY' . now()->format('YmdHis');

            $passwordEncrypted = $this->encrypt($this->passwordPlain);
            $auth = $this->post('/api/authentication/authenticate', [
                'userName' => $this->userName,
                'password' => $passwordEncrypted,
            ]);

            if (($auth['responseCode'] ?? null) !== 0) {
                return response()->json([
                    'message' => (string) ($auth['message'] ?? 'No se pudo autenticar con Baneco.'),
                ], 422);
            }

            $token = (string) ($auth['token'] ?? '');
            if ($token === '') {
                return response()->json([
                    'message' => 'Baneco no devolvio token de autenticacion.',
                ], 422);
            }

            $accountCreditEncrypted = $this->encrypt($this->accountCredit);
            $qrResponse = $this->post(
                '/api/qrsimple/generateQR',
                [
                    'transactionId' => $transactionId,
                    'accountCredit' => $accountCreditEncrypted,
                    'currency' => $this->currency,
                    'amount' => $amount,
                    'description' => $description,
                    'dueDate' => now()->addDays(7)->format('Y-m-d'),
                    'singleUse' => $this->singleUse,
                    'modifyAmount' => $this->modifyAmount,
                    'branchCode' => $this->branchCode,
                ],
                $token
            );

            if (($qrResponse['responseCode'] ?? null) !== 0) {
                return response()->json([
                    'message' => (string) ($qrResponse['message'] ?? 'No se pudo generar el QR.'),
                ], 422);
            }

            $qrImage = (string) ($qrResponse['qrImage'] ?? '');
            if ($qrImage === '') {
                return response()->json([
                    'message' => 'Baneco no devolvio la imagen del QR.',
                ], 422);
            }

            if (!str_starts_with($qrImage, 'data:image')) {
                $qrImage = 'data:image/png;base64,' . $qrImage;
            }

            return response()->json([
                'message' => (string) ($qrResponse['message'] ?? 'QR generado correctamente.'),
                'qr' => $qrImage,
                'qrId' => (string) ($qrResponse['qrId'] ?? ''),
                'transactionId' => $transactionId,
                'cliente' => $clientName,
                'documento' => $clientDocument,
                'monto' => number_format($amount, 2, '.', ''),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Error al generar el QR: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function encrypt(string $text): string
    {
        $response = $this->banecoRequest()
            ->get(rtrim($this->baseUrl, '/') . '/api/authentication/encrypt', [
                'text' => $text,
                'aesKey' => $this->aesKey,
            ]);

        $this->throwIfFailed($response);

        return trim((string) $response->body(), "\" \r\n\t");
    }

    private function post(string $path, array $body, ?string $token = null): array
    {
        $request = $this->banecoRequest()->contentType('application/json');

        if ($token !== null && $token !== '') {
            $request = $request->withToken($token);
        }

        $response = $request->post(rtrim($this->baseUrl, '/') . $path, $body);

        $this->throwIfFailed($response);

        $decoded = $response->json();
        if (!is_array($decoded)) {
            throw new \RuntimeException('Respuesta JSON invalida del servicio QR.');
        }

        return $decoded;
    }

    private function banecoRequest()
    {
        return Http::timeout(30)
            ->acceptJson()
            ->withOptions([
                'verify' => false,
            ]);
    }

    private function throwIfFailed(Response $response): void
    {
        if ($response->successful()) {
            return;
        }

        $message = trim((string) $response->body());
        throw new \RuntimeException(
            'HTTP ' . $response->status() . ($message !== '' ? ': ' . $message : '')
        );
    }
}
