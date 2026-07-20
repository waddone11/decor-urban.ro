<?php

namespace App\Console\Commands;

use App\Support\Feeds\ProductFeed;
use Illuminate\Console\Command;

class GenerateGoogleBusinessProductsExport extends Command
{
    protected $signature = 'feeds:google-business';

    protected $description = 'Regenerează cache-ul pentru exportul manual Google Business Products';

    public function handle(): int
    {
        ProductFeed::forgetCache();
        ProductFeed::googleBusinessProductsCsv();

        $this->line(ProductFeed::summaryText('Google Business Products export'));

        return self::SUCCESS;
    }
}
