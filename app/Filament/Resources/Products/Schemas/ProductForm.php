<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Principal')
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->label('Nume')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug((string) $state))),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Generat automat din nume; editabil.'),
                        TextInput::make('code')
                            ->label('Cod produs')
                            ->maxLength(255)
                            ->helperText('Codul NU e unic (poate exista pe produse diferite).'),
                        RichEditor::make('description')
                            ->label('Descriere')
                            ->columnSpanFull(),
                    ]),

                Section::make('Preț')
                    ->columns(2)
                    ->components([
                        Toggle::make('price_on_request')
                            ->label('Preț la cerere')
                            ->default(true)
                            ->live()
                            ->helperText('Activ = nu se afișează preț, doar „La cerere".'),
                        TextInput::make('price')
                            ->label('Preț (RON)')
                            ->numeric()
                            ->prefix('RON')
                            ->visible(fn (Get $get): bool => ! $get('price_on_request'))
                            ->required(fn (Get $get): bool => ! $get('price_on_request')),
                        TextInput::make('availability')
                            ->label('Disponibilitate')
                            ->placeholder('la comanda')
                            ->maxLength(255),
                    ]),

                Section::make('Organizare')
                    ->columns(2)
                    ->components([
                        Select::make('categories')
                            ->label('Categorii')
                            ->multiple()
                            ->relationship('categories', 'name')
                            ->preload()
                            ->searchable()
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label('Activ')
                            ->default(true),
                        TextInput::make('sort_order')
                            ->label('Ordine')
                            ->numeric()
                            ->default(0),
                    ]),

                Section::make('SEO')
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->components([
                        TextInput::make('meta_title')
                            ->label('Meta titlu')
                            ->maxLength(255),
                        TextInput::make('meta_keywords')
                            ->label('Meta cuvinte cheie')
                            ->maxLength(255),
                        Textarea::make('meta_description')
                            ->label('Meta descriere')
                            ->columnSpanFull(),
                        Placeholder::make('legacy_urls_display')
                            ->label('URL-uri vechi (pentru 301 redirects viitoare)')
                            ->columnSpanFull()
                            ->content(fn (?Product $record): string => $record && ! empty($record->legacy_urls)
                                ? implode("\n", $record->legacy_urls)
                                : '—'),
                    ]),
            ]);
    }
}
