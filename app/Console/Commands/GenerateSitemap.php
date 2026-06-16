<?php

namespace App\Console\Commands;

use App\Support\Sitemap;
use Illuminate\Console\Command;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = 'Generează public/sitemap.xml din categorii + produse active';

    public function handle(): int
    {
        $path = public_path('sitemap.xml');
        file_put_contents($path, Sitemap::xml());

        $this->info('Sitemap scris în: '.$path);

        return self::SUCCESS;
    }
}
