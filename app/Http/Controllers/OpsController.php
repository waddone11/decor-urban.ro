<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class OpsController extends Controller
{
    /** Pagină index: linkuri către comenzile din whitelist (cu token în link). */
    public function index(Request $request)
    {
        return response()
            ->view('ops.index', [
                'token' => (string) $request->query('token'),
                'commands' => config('ops.commands', []),
                'destructive' => config('ops.destructive', []),
            ])
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    /** Rulează o comandă din whitelist și întoarce output-ul ca text/plain. */
    public function run(Request $request, string $command)
    {
        $whitelist = config('ops.commands', []);

        // Nimic în afara whitelist-ului. Cheie necunoscută → 404.
        if (! array_key_exists($command, $whitelist)) {
            abort(404);
        }

        $destructive = in_array($command, config('ops.destructive', []), true);
        if ($destructive && $request->query('confirm') !== 'YES') {
            Log::channel('ops')->warning('ops: comandă distructivă refuzată (lipsă confirm)', [
                'command' => $command, 'ip' => $request->ip(),
            ]);

            return response(
                "Refuz: comanda distructivă „{$command}\" necesită &confirm=YES în URL.\n",
                422,
            )->header('Content-Type', 'text/plain; charset=utf-8');
        }

        Log::channel('ops')->info('ops: rulez comandă', [
            'command' => $command,
            'artisan' => $whitelist[$command],
            'ip' => $request->ip(),
            'at' => now()->toAtomString(),
        ]);

        // Prod non-interactiv: fără limită de timp pentru comenzi lungi (migrate/seed).
        @set_time_limit(0);

        $exitCode = Artisan::call($whitelist[$command]);
        $output = Artisan::output();

        Log::channel('ops')->info('ops: comandă terminată', [
            'command' => $command, 'exit' => $exitCode, 'ip' => $request->ip(),
        ]);

        $header = "\$ php artisan {$whitelist[$command]}\n".str_repeat('─', 50)."\n";

        return response($header.$output."\n(exit {$exitCode})\n", 200)
            ->header('Content-Type', 'text/plain; charset=utf-8')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
