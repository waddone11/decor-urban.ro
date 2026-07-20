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
                            ->gt(0)
                            ->prefix('RON')
                            ->visible(fn (Get $get): bool => ! $get('price_on_request'))
                            ->required(fn (Get $get): bool => ! $get('price_on_request')),
                        TextInput::make('sale_price')
                            ->label('Preț promoțional')
                            ->numeric()
                            ->prefix('RON')
                            ->lt('price')
                            ->visible(fn (Get $get): bool => ! $get('price_on_request')),
                        TextInput::make('availability')
                            ->label('Disponibilitate')
                            ->placeholder('in stock')
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

                Section::make('Feeduri și cataloage')
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->components([
                        Toggle::make('feed_enabled')
                            ->label('Include în feeduri Merchant/Meta')
                            ->default(false)
                            ->live()
                            ->rules([
                                fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get): void {
                                    if (! $value) {
                                        return;
                                    }
                                    if ($get('quote_only') || $get('price_on_request')) {
                                        $fail('Feedul nu poate fi activat pentru produse la cerere.');
                                    }
                                    if (! $get('price') || (float) $get('price') <= 0) {
                                        $fail('Feedul cere un preț real mai mare decât zero.');
                                    }
                                    if (! $get('is_active')) {
                                        $fail('Feedul nu poate fi activat pentru produse inactive.');
                                    }
                                },
                            ]),
                        Toggle::make('quote_only')
                            ->label('Produs doar la cerere')
                            ->default(true)
                            ->helperText('Activ = exclus din feeduri comerciale.'),
                        Toggle::make('show_in_google_business')
                            ->label('Export Google Business Products')
                            ->default(false),
                        Select::make('condition')
                            ->label('Condiție')
                            ->options(['new' => 'new', 'refurbished' => 'refurbished', 'used' => 'used'])
                            ->default('new'),
                        TextInput::make('currency')
                            ->label('Monedă')
                            ->default('RON')
                            ->maxLength(3),
                        TextInput::make('brand')
                            ->label('Brand')
                            ->default(config('business.name'))
                            ->maxLength(255),
                        TextInput::make('mpn')->label('MPN')->maxLength(255),
                        TextInput::make('gtin')->label('GTIN')->maxLength(255),
                        TextInput::make('google_product_category')->label('Google product category')->maxLength(255),
                        TextInput::make('facebook_product_category')->label('Facebook product category')->maxLength(255),
                        TextInput::make('shipping_weight')->label('Greutate livrare')->maxLength(255),
                        TextInput::make('minimum_order_quantity')->label('Cantitate minimă')->numeric()->minValue(1),
                        TextInput::make('custom_label_0')->label('Custom label 0')->maxLength(255),
                        TextInput::make('custom_label_1')->label('Custom label 1')->maxLength(255),
                        TextInput::make('custom_label_2')->label('Custom label 2')->maxLength(255),
                        TextInput::make('custom_label_3')->label('Custom label 3')->maxLength(255),
                        TextInput::make('custom_label_4')->label('Custom label 4')->maxLength(255),
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
