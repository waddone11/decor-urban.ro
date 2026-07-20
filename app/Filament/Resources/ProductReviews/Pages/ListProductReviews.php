<?php

namespace App\Filament\Resources\ProductReviews\Pages;

use App\Filament\Resources\ProductReviews\ProductReviewResource;
use Filament\Resources\Pages\ListRecords;

class ListProductReviews extends ListRecords
{
    protected static string $resource = ProductReviewResource::class;

    // Fără „creează" — recenziile vin doar din formularul public (nu se fabrică).
    protected function getHeaderActions(): array
    {
        return [];
    }
}
