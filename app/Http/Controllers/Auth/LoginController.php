<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers {
        login as protected authenticate;
    }

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function login(Request $request)
    {
        if (in_array($request->getHost(), config('tenancy.central_hosts'), true)) {
            $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required', 'string'],
            ]);

            $empresa = Empresa::query()
                ->where('email_admin', strtolower($request->string('email')->toString()))
                ->first();

            if ($empresa) {
                if (! $empresa->activo || $empresa->estado !== 'activa') {
                    return back()
                        ->withInput($request->only('email'))
                        ->withErrors(['email' => 'La empresa está suspendida. Contactá al administrador del servicio.']);
                }

                if ($empresa->fecha_expiracion?->isPast()) {
                    return back()
                        ->withInput($request->only('email'))
                        ->withErrors(['email' => 'La suscripción de la empresa está vencida.']);
                }

                return redirect()->away($this->tenantLoginUrl($request, $empresa));
            }
        }

        return $this->authenticate($request);
    }

    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }

    private function tenantLoginUrl(Request $request, Empresa $empresa): string
    {
        $query = http_build_query(['email' => $empresa->email_admin]);
        $port = in_array($request->getPort(), [80, 443], true)
            ? ''
            : ':'.$request->getPort();

        return sprintf(
            '%s://%s.%s%s/login?%s',
            $request->getScheme(),
            $empresa->dominio,
            config('tenancy.base_domain'),
            $port,
            $query
        );
    }
}
