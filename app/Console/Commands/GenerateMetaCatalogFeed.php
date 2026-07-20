<?php

namespace App\Console\Commands;

use App\Support\Feeds\ProductFeed;
use Illuminate\Console\Command;

class GenerateMetaCatalogFeed extends Command
{
    protected $signature = 'feeds:meta';

    protected $description = 'Regenerează cache-ul pentru feed-ul Meta Catalog';

    public function handle(): int
    {
        ProductFeed::forgetCache();
        ProductFeed::metaCsv();

        $this->line(ProductFeed::summaryText('Meta Catalog feed'));

        return self::SUCCESS;
    }
}
