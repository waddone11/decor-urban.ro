<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Poartă /commands: o singură cheie `secret` (din config commands.secret = .env SECRET),
 * citită din header X-Command-Secret sau query ?secret=. Comparație în timp constant.
 * Lipsă config / cheie greșită → 404 (nu 403 — nu dezvăluim existența rutei).
 */
class VerifySecretKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('commands.secret');

        if (! is_string($secret) || $secret === '') {
            abort(404);
        }

        $provided = $request->header('X-Command-Secret');
        if (! is_string($provided) || $provided === '') {
            $provided = (string) $request->query('secret', '');
        }

        if (! hash_equals($secret, (string) $provided)) {
            abort(404);
        }

        return $next($request);
    }
}
