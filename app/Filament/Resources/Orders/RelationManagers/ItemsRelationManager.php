<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Produsele comenzii — snapshot la momentul comenzii, READ-ONLY
 * (fără create/edit/delete; comanda rămâne validă chiar dacă produsul se schimbă).
 */
class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Produse';

    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product_name')
                    ->label('Produs')
                    ->wrap(),
                TextColumn::make('product_code')
                    ->label('Cod')
                    ->placeholder('—'),
                TextColumn::make('quantity')
                    ->label('Cantitate')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('unit_price')
                    ->label('Preț unitar')
                    ->placeholder('la cerere')
                    ->money('RON'),
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
