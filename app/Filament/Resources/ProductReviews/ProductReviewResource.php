<?php

namespace App\Filament\Resources\ProductReviews;

use App\Filament\Resources\ProductReviews\Pages\ListProductReviews;
use App\Filament\Resources\ProductReviews\Tables\ProductReviewsTable;
use App\Models\ProductReview;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

/**
 * Moderare recenzii REALE de la clienți: owner-ul doar aprobă/respinge.
 * Fără create și fără editare de conținut — recenziile nu se fabrică din admin.
 */
class ProductReviewResource extends Resource
{
    protected static ?string $model = ProductReview::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|UnitEnum|null $navigationGroup = 'Comenzi';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Recenzii produse';

    protected static ?string $modelLabel = 'recenzie';

    protected static ?string $pluralModelLabel = 'recenzii';

    /** Badge cu numărul de recenzii în așteptare. */
    public static function getNavigationBadge(): ?string
    {
        $count = ProductReview::where('status', 'pending')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    /** Recenziile vin DOAR din formularul public — nu se creează din admin. */
    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return ProductReviewsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductReviews::route('/'),
        ];
    }
}
