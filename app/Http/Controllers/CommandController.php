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
        'config-clear' => 'config:clear',
        'route-clear' => 'route:clear',
        'view-clear' => 'view:clear',
        'optimize-clear' => 'optimize:clear',
        'config-cache' => 'config:cache',
        'route-cache' => 'route:cache',
        'view-cache' => 'view:cache',
        'optimize' => 'optimize',
        'create-storage-link' => 'storage:link',
        'create-sitemap' => 'sitemap:generate',
        'migrate' => 'migrate --force',
        'migrate-status' => 'migrate:status',
        'about' => 'about',
        'catalog-summary' => 'catalog:summary',
        'queue-restart' => 'queue:restart',
        'thumbnails' => 'images:thumbnails',
        'export-snapshot' => 'catalog:export-snapshot',
        'feeds-google' => 'feeds:google',
        'feeds-meta' => 'feeds:meta',
        'feeds-all' => 'feeds:all',
        'google-business-export' => 'feeds:google-business',
        'reviews-fetch' => 'google:reviews-fetch',
    ];

    private const GROUPS = [
        'Feed-uri' => ['feeds-google', 'feeds-meta', 'feeds-all', 'google-business-export'],
        'Recenzii' => ['reviews-fetch'],
        'Mentenanță' => [
            'clear-cache', 'config-clear', 'route-clear', 'view-clear', 'optimize-clear',
            'config-cache', 'route-cache', 'view-cache', 'optimize', 'create-storage-link',
            'create-sitemap', 'migrate', 'migrate-status', 'about', 'catalog-summary',
            'queue-restart', 'trigger-queue', 'thumbnails', 'export-snapshot', 'migrate-fresh-seed',
        ],
    ];

    public function index(Request $request)
    {
        $secret = (string) $request->query('secret', '');
        $html = '<!doctype html><meta charset=utf-8><meta name=robots content="noindex,nofollow">'
            .'<title>Commands</title><body style="font-family:ui-monospace,monospace;max-width:640px;margin:2rem auto;padding:0 1rem">'
            .'<h1>Commands</h1>';

        foreach (self::GROUPS as $group => $links) {
            $html .= '<h2>'.e($group).'</h2><ul>';
            foreach ($links as $cmd) {
                $danger = $cmd === 'migrate-fresh-seed';
                $url = url('/commands/'.$cmd).'?secret='.urlencode($secret).($danger ? '&confirm=YES' : '');
                $html .= '<li><a href="'.e($url).'">'.$cmd.'</a>'.($danger ? ' <strong style="color:#b42318">distructiv: cere confirm=YES</strong>' : '').'</li>';
            }
            $html .= '</ul>';
        }
        $html .= '<p style="color:#777;font-size:.85rem">Runner permanent, protejat de SECRET. Folosește HTTPS, rotește SECRET periodic și nu pune cheia în linkuri partajate.</p></body>';

        return response($html)->header('X-Robots-Tag', 'noindex, nofollow');
    }

    public function clearCache(Request $r)
    {
        return $this->run($r, 'clear-cache');
    }

    public function configClear(Request $r)
    {
        return $this->run($r, 'config-clear');
    }

    public function routeClear(Request $r)
    {
        return $this->run($r, 'route-clear');
    }

    public function viewClear(Request $r)
    {
        return $this->run($r, 'view-clear');
    }

    public function optimizeClear(Request $r)
    {
        return $this->run($r, 'optimize-clear');
    }

    public function configCache(Request $r)
    {
        return $this->run($r, 'config-cache');
    }

    public function routeCache(Request $r)
    {
        return $this->run($r, 'route-cache');
    }

    public function viewCache(Request $r)
    {
        return $this->run($r, 'view-cache');
    }

    public function optimize(Request $r)
    {
        return $this->run($r, 'optimize');
    }

    public function createStorageLink(Request $r)
    {
        return $this->run($r, 'create-storage-link');
    }

    public function createSitemap(Request $r)
    {
        return $this->run($r, 'create-sitemap');
    }

    public function migrate(Request $r)
    {
        return $this->run($r, 'migrate');
    }

    public function migrateStatus(Request $r)
    {
        return $this->run($r, 'migrate-status');
    }

    public function about(Request $r)
    {
        return $this->run($r, 'about');
    }

    public function catalogSummary(Request $r)
    {
        return $this->run($r, 'catalog-summary');
    }

    public function queueRestart(Request $r)
    {
        return $this->run($r, 'queue-restart');
    }

    public function thumbnails(Request $r)
    {
        return $this->run($r, 'thumbnails');
    }

    public function exportSnapshot(Request $r)
    {
        return $this->run($r, 'export-snapshot');
    }

    public function feedsGoogle(Request $r)
    {
        return $this->run($r, 'feeds-google');
    }

    public function feedsMeta(Request $r)
    {
        return $this->run($r, 'feeds-meta');
    }

    public function feedsAll(Request $r)
    {
        return $this->run($r, 'feeds-all');
    }

    public function googleBusinessExport(Request $r)
    {
        return $this->run($r, 'google-business-export');
    }

    public function reviewsFetch(Request $r)
    {
        return $this->run($r, 'reviews-fetch');
    }

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
