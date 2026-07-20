<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['images', 'categories']))
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('primary_image_path')
                    ->label('Imagine')
                    ->disk('public')
                    ->square()
                    ->height(56)
                    ->defaultImageUrl(url('/favicon.ico')),
                TextColumn::make('name')
                    ->label('Nume')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('code')
                    ->label('Cod')
                    ->searchable()
                    ->badge()
                    ->color('gray')
                    ->placeholder('—'),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('categories.name')
                    ->label('Categorii')
                    ->badge()
                    ->separator(','),
                TextColumn::make('price_status')
                    ->label('Preț')
                    ->state(fn ($record): string => $record->price_on_request
                        ? 'La cerere'
                        : number_format((float) $record->price, 2, ',', '.').' RON')
                    ->badge()
                    ->color(fn ($record): string => $record->price_on_request ? 'warning' : 'success'),
                ToggleColumn::make('is_active')
                    ->label('Activ'),
                ToggleColumn::make('feed_enabled')
                    ->label('Feed')
                    ->toggleable(),
                IconColumn::make('show_in_google_business')
                    ->label('GBP')
                    ->boolean()
                    ->toggleable(),
                TextColumn::make('sort_order')
                    ->label('Ordine')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('categories')
                    ->label('Categorie')
                    ->relationship('categories', 'name')
                    ->multiple()
                    ->preload(),
                TernaryFilter::make('price_on_request')
                    ->label('Preț la cerere'),
                TernaryFilter::make('is_active')
                    ->label('Activ'),
                TernaryFilter::make('feed_enabled')
                    ->label('În feeduri'),
                TernaryFilter::make('show_in_google_business')
                    ->label('Google Business'),
                TernaryFilter::make('available_seap')
                    ->label('SEAP/SICAP'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // Marcare SEAP în bloc (127 de produse nu se bifează unul câte unul).
                    BulkAction::make('seap_on')
                        ->label('Marchează disponibil pe SEAP')
                        ->icon('heroicon-o-building-library')
                        ->color('success')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records): void {
                            $records->each->update(['available_seap' => true]);
                            Notification::make()->title($records->count().' produse marcate SEAP')->success()->send();
                        }),
                    BulkAction::make('seap_off')
                        ->label('Scoate de pe SEAP')
                        ->icon('heroicon-o-building-library')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records): void {
                            $records->each->update(['available_seap' => false]);
                            Notification::make()->title($records->count().' produse scoase de pe SEAP')->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
