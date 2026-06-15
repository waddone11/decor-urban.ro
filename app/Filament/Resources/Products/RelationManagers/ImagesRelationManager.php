<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    protected static ?string $title = 'Imagini';

    // Randează imediat (nu lazy) — galeria mică, mai bună la editare.
    protected static bool $isLazy = false;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('path')
                    ->label('Imagine')
                    ->disk('public')
                    ->directory(fn (): string => 'products/'.$this->getOwnerRecord()->slug)
                    ->visibility('public')
                    ->image()
                    ->imageEditor()
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('alt')
                    ->label('Text alternativ (alt)')
                    ->maxLength(255),
                Toggle::make('is_primary')
                    ->label('Imagine principală'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('path')
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                ImageColumn::make('path')
                    ->label('Imagine')
                    ->disk('public')
                    ->square()
                    ->height(64),
                TextColumn::make('alt')
                    ->label('Text alternativ')
                    ->placeholder('—')
                    ->wrap()
                    ->toggleable(),
                ToggleColumn::make('is_primary')
                    ->label('Principală'),
                TextColumn::make('sort_order')
                    ->label('Ordine')
                    ->numeric()
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Adaugă imagine'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
