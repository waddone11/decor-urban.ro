<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Comandă')
                    ->columns(3)
                    ->components([
                        TextInput::make('number')
                            ->label('Număr')
                            ->disabled(),
                        Select::make('status')
                            ->label('Status')
                            ->options(Order::STATUS_LABELS)
                            ->required()
                            ->native(false)
                            ->helperText('Workflow comandă — editabil.'),
                        TextInput::make('total')
                            ->label('Total (RON)')
                            ->numeric()
                            ->prefix('RON')
                            ->helperText('Opțional — completezi după ofertare.'),
                    ]),

                Section::make('Client')
                    ->columns(2)
                    ->components([
                        TextInput::make('customer_name')->label('Nume')->disabled(),
                        TextInput::make('company')->label('Firmă / instituție')->disabled(),
                        TextInput::make('cui')->label('CUI')->disabled(),
                        TextInput::make('phone')->label('Telefon')->disabled(),
                        TextInput::make('email')->label('Email')->disabled(),
                        TextInput::make('payment_method')
                            ->label('Metodă')
                            ->formatStateUsing(fn (?string $state): string => Order::PAYMENT_METHODS[$state] ?? (string) $state)
                            ->disabled(),
                    ]),

                Section::make('Livrare')
                    ->columns(3)
                    ->components([
                        TextInput::make('county')->label('Județ')->disabled(),
                        TextInput::make('city')->label('Localitate')->disabled(),
                        TextInput::make('address')->label('Adresă')->disabled()->columnSpan(3),
                        Textarea::make('notes')->label('Note client')->disabled()->columnSpanFull(),
                    ]),
            ]);
    }
}
