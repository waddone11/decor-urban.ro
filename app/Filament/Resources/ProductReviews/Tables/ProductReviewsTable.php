<?php

namespace App\Filament\Resources\ProductReviews\Tables;

use App\Models\ProductReview;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProductReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('product'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('product.name')
                    ->label('Produs')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('author_name')
                    ->label('Autor')
                    ->description(fn (ProductReview $r): string => $r->author_email)
                    ->searchable(),
                TextColumn::make('rating')
                    ->label('Rating')
                    ->badge()
                    ->formatStateUsing(fn (int $state): string => $state.' ★')
                    ->color(fn (int $state): string => $state >= 4 ? 'success' : ($state >= 3 ? 'warning' : 'danger')),
                TextColumn::make('body')
                    ->label('Recenzie')
                    ->limit(80)
                    ->tooltip(fn (ProductReview $r): string => $r->body)
                    ->wrap(),
                IconColumn::make('verified_purchase')
                    ->label('Verificată')
                    ->boolean(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ProductReview::STATUS_LABELS[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(ProductReview::STATUS_LABELS),
                TernaryFilter::make('verified_purchase')
                    ->label('Achiziție verificată'),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Aprobă')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (ProductReview $r): bool => $r->status !== 'approved')
                    ->requiresConfirmation()
                    ->action(function (ProductReview $record): void {
                        $record->update(['status' => 'approved']);
                        Notification::make()->title('Recenzie aprobată')->success()->send();
                    }),
                Action::make('reject')
                    ->label('Respinge')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (ProductReview $r): bool => $r->status !== 'rejected')
                    ->requiresConfirmation()
                    ->action(function (ProductReview $record): void {
                        $record->update(['status' => 'rejected']);
                        Notification::make()->title('Recenzie respinsă')->send();
                    }),
            ]);
    }
}
