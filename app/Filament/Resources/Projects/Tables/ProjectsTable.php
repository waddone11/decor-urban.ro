<?php

namespace App\Filament\Resources\Projects\Tables;

use App\Models\Project;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('images'))
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('primary_image_path')
                    ->label('Imagine')
                    ->disk('public')
                    ->square()
                    ->height(56)
                    ->defaultImageUrl(url('/favicon.ico')),
                TextColumn::make('title')
                    ->label('Titlu')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('location')
                    ->label('Locație')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('client_type')
                    ->label('Tip client')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state ? (Project::CLIENT_TYPES[$state] ?? $state) : '—')
                    ->color('gray'),
                TextColumn::make('year')
                    ->label('An')
                    ->placeholder('—')
                    ->toggleable(),
                ToggleColumn::make('is_published')
                    ->label('Publicat'),
                TextColumn::make('sort_order')
                    ->label('Ordine')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                TernaryFilter::make('is_published')
                    ->label('Publicat'),
                SelectFilter::make('client_type')
                    ->label('Tip client')
                    ->options(Project::CLIENT_TYPES),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
