<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EsAdministrador
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(
            $request->user()?->esAdministrador(),
            403,
            'Esta sección es solo para el personal de la biblioteca.'
        );

        return $next($request);
    }
}