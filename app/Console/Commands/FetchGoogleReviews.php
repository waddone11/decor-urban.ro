<?php

namespace App\Console\Commands;

use App\Support\GoogleReviews;
use Illuminate\Console\Command;

class FetchGoogleReviews extends Command
{
    protected $signature = 'google:reviews-fetch';

    protected $description = 'Preia recenziile reale din Google Places API și le salvează în cache 24h';

    public function handle(): int
    {
        GoogleReviews::forgetCache();
        GoogleReviews::fetchAndCache();

        $this->line(GoogleReviews::summaryText());

        return self::SUCCESS;
    }
}
