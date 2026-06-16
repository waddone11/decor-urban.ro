<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

/**
 * Helper artisan din URL (gated de VerifySecretKey). Fiecare metodă rulează o comandă
 * din whitelist și întoarce Artisan::output() ca text/plain. Logare în channel-ul „commands".
 */
class CommandController extends Controller
{
    /** Comenzile mapate (cheie rută → comandă artisan). Doar acestea rulează. */
    private const COMMANDS = [
        'clear-cache' => 'cache:clear',
        'optimize-clear' => 'optimize:clear',
        'optimize' => 'optimize',
        'create-storage-link' => 'storage:link',
        'create-sitemap' => 'sitemap:generate',
        'migrate' => 'migrate --force',
        'migrate-status' => 'migrate:status',
        'about' => 'about',
        'catalog-summary' => 'catalog:summary',
        'queue-restart' => 'queue:restart',
    ];

    public function index(Request $request)
    {
        $secret = (string) $request->query('secret', '');
        $links = array_keys(self::COMMANDS);
        $links[] = 'migrate-fresh-seed';
        $links[] = 'trigger-queue';

        $html = '<!doctype html><meta charset=utf-8><meta name=robots content="noindex,nofollow">'
            .'<title>Commands</title><body style="font-family:ui-monospace,monospace;max-width:640px;margin:2rem auto;padding:0 1rem">'
            .'<h1>⚙️ Commands</h1><ul>';
        foreach ($links as $cmd) {
            $danger = $cmd === 'migrate-fresh-seed';
            $url = url('/commands/'.$cmd).'?secret='.urlencode($secret).($danger ? '&confirm=YES' : '');
            $html .= '<li><a href="'.e($url).'">'.$cmd.'</a>'.($danger ? ' ⚠️ distructiv' : '').'</li>';
        }
        $html .= '</ul><p style="color:#777;font-size:.85rem">Helper deploy. Rotește/șterge SECRET când nu-l folosești.</p></body>';

        return response($html)->header('X-Robots-Tag', 'noindex, nofollow');
    }

    public function clearCache(Request $r) { return $this->run($r, 'clear-cache'); }
    public function optimizeClear(Request $r) { return $this->run($r, 'optimize-clear'); }
    public function optimize(Request $r) { return $this->run($r, 'optimize'); }
    public function createStorageLink(Request $r) { return $this->run($r, 'create-storage-link'); }
    public function createSitemap(Request $r) { return $this->run($r, 'create-sitemap'); }
    public function migrate(Request $r) { return $this->run($r, 'migrate'); }
    public function migrateStatus(Request $r) { return $this->run($r, 'migrate-status'); }
    public function about(Request $r) { return $this->run($r, 'about'); }
    public function catalogSummary(Request $r) { return $this->run($r, 'catalog-summary'); }
    public function queueRestart(Request $r) { return $this->run($r, 'queue-restart'); }

    /** Distructiv → cere ?confirm=YES. */
    public function migrateFreshSeed(Request $request)
    {
        if ($request->query('confirm') !== 'YES') {
            return $this->text("Refuz: comandă distructivă — adaugă &confirm=YES în URL.\n", 422);
        }

        return $this->artisan($request, 'migrate-fresh-seed', 'migrate:fresh --seed --force');
    }

    /** Procesează coada până se golește (util DOAR dacă emailurile trec pe coadă; acum sync). */
    public function triggerQueue(Request $request, ?string $queue = null)
    {
        $cmd = 'queue:work --stop-when-empty --max-time=50'.($queue ? ' --queue='.$queue : '');

        return $this->artisan($request, 'trigger-queue', $cmd);
    }

    private function run(Request $request, string $key)
    {
        return $this->artisan($request, $key, self::COMMANDS[$key]);
    }

    private function artisan(Request $request, string $key, string $command)
    {
        @set_time_limit(0);

        Log::channel('commands')->info('commands: rulez', [
            'command' => $key, 'artisan' => $command, 'ip' => $request->ip(), 'at' => date('c'),
        ]);

        $exit = Artisan::call($command);
        $output = Artisan::output();

        Log::channel('commands')->info('commands: terminat', ['command' => $key, 'exit' => $exit, 'ip' => $request->ip()]);

        return $this->text("\$ php artisan {$command}\n".str_repeat('─', 50)."\n".$output."\n(exit {$exit})\n");
    }

    private function text(string $body, int $status = 200)
    {
        return response($body, $status)
            ->header('Content-Type', 'text/plain; charset=utf-8')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
