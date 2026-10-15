<?php

namespace App\Providers;

use App\Http\Controllers\CineAuthController;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Web publica: cliente logueado y config publica de Firebase para el login con Google
        View::composer('cine.layout', function ($view) {
            $cliente = CineAuthController::clienteActual(request());
            $fb = config('services.firebase');
            $view->with('cineCliente', $cliente);
            $view->with('cineAuth', [
                'cliente' => $cliente ? $cliente->publico() : null,
                'loginUrl' => route('cine.login'),
                'logoutUrl' => route('cine.logout'),
                'datosUrl' => route('cine.cliente.datos'),
                'firebase' => [
                    'apiKey' => $fb['api_key'],
                    'authDomain' => $fb['auth_domain'],
                    'projectId' => $fb['project_id'],
                    'storageBucket' => $fb['storage_bucket'],
                    'messagingSenderId' => $fb['messaging_sender_id'],
                    'appId' => $fb['app_id'],
                ],
            ]);
        });
    }
}
