<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCentralAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(in_array($request->getHost(), config('tenancy.central_hosts'), true), 404);
        abort_unless($request->user() && $request->user()->empresa_id === null, 403);

        return $next($request);
    }
}
