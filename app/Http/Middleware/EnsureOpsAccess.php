<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Poartă /ops: dacă ops e dezactivat SAU token-ul lipsește/e greșit → 404
 * (nu 403 — nu dezvăluim că ruta există). Comparație în timp constant.
 */
class EnsureOpsAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $enabled = (bool) config('ops.enabled', false);
        $expected = (string) config('ops.token', '');
        $given = (string) $request->query('token', '');

        if (! $enabled || $expected === '' || ! hash_equals($expected, $given)) {
            abort(404);
        }

        return $next($request);
    }
}
