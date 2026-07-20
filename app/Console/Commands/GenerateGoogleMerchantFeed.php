<?php

namespace App\Console\Commands;

use App\Support\Feeds\ProductFeed;
use Illuminate\Console\Command;

class GenerateGoogleMerchantFeed extends Command
{
    protected $signature = 'feeds:google';

    protected $description = 'Regenerează cache-ul pentru feed-ul Google Merchant Center';

    public function handle(): int
    {
        ProductFeed::forgetCache();
        ProductFeed::googleXml();

        $this->line(ProductFeed::summaryText('Google Merchant Center feed'));

        return self::SUCCESS;
    }
}
