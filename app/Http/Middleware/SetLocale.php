<?php

namespace App\Http\Middleware;

use App\Models\Configuracion;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->has('app_locale')) {
            app()->setLocale($request->session()->get('app_locale'));
        } else {
            try {
                $config = Configuracion::where('descripcion', 'idioma')->first();
                if ($config && in_array($config->observacion, ['es', 'en'])) {
                    app()->setLocale($config->observacion);
                    $request->session()->put('app_locale', $config->observacion);
                }
            } catch (\Exception $e) {
            }
        }
        return $next($request);
    }
}
