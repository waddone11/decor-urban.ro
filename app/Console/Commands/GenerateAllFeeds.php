<?php

namespace App\Console\Commands;

use App\Support\Feeds\ProductFeed;
use Illuminate\Console\Command;

class GenerateAllFeeds extends Command
{
    protected $signature = 'feeds:all';

    protected $description = 'Regenerează cache-ul pentru feed-urile Google Merchant și Meta Catalog';

    public function handle(): int
    {
        ProductFeed::forgetCache();
        ProductFeed::googleXml();
        ProductFeed::metaCsv();

        $this->line(ProductFeed::summaryText('Toate feed-urile'));

        return self::SUCCESS;
    }
}
