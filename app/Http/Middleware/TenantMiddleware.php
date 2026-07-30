<?php

namespace App\Http\Middleware;

use App\Models\Empresa;
use App\Services\TenantConnectionManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    public function __construct(
        private readonly TenantConnectionManager $connections,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();

        if (in_array($host, config('tenancy.central_hosts'), true)) {
            return $next($request);
        }

        $suffix = '.'.config('tenancy.base_domain');
        abort_unless(str_ends_with($host, $suffix), 404);

        $subdomain = substr($host, 0, -strlen($suffix));
        abort_unless(
            $subdomain !== ''
            && ! str_contains($subdomain, '.')
            && preg_match('/^[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/', $subdomain),
            404
        );

        $empresa = Empresa::query()->where('dominio', $subdomain)->firstOrFail();

        abort_unless(
            $empresa->activo && $empresa->estado === 'activa',
            403,
            'La empresa está suspendida.'
        );
        abort_if(
            $empresa->fecha_expiracion?->isPast(),
            402,
            'La suscripción de la empresa está vencida.'
        );

        $this->connections->connect($empresa);
        View::share('empresa', $empresa);
        app()->instance(Empresa::class, $empresa);

        return $next($request);
    }
}
