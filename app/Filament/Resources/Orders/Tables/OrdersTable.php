<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Mail\OrderPlacedAdmin;
use App\Mail\OrderPlacedCustomer;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount('items'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('number')
                    ->label('Număr')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),
                TextColumn::make('customer_name')
                    ->label('Client')
                    ->description(fn (Order $r): ?string => $r->company)
                    ->searchable()
                    ->wrap(),
                TextColumn::make('phone')
                    ->label('Telefon')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('items_count')
                    ->label('Produse')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('payment_method')
                    ->label('Metodă')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Order::PAYMENT_METHODS[$state] ?? $state)
                    ->color(fn (string $state): string => $state === 'ramburs' ? 'info' : 'success'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Order::STATUS_LABELS[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'noua' => 'warning',
                        'in_lucru' => 'info',
                        'ofertata' => 'primary',
                        'confirmata' => 'success',
                        'livrata' => 'success',
                        'anulata' => 'danger',
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
                    ->options(Order::STATUS_LABELS),
                SelectFilter::make('payment_method')
                    ->label('Metodă')
                    ->options(Order::PAYMENT_METHODS),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('resend')
                    ->label('Retrimite email')
                    ->icon('heroicon-o-envelope')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->action(function (Order $record): void {
                        $record->loadMissing('items');
                        Mail::to($record->email)->send(new OrderPlacedCustomer($record));
                        Mail::to(config('contact.email'))->send(new OrderPlacedAdmin($record));

                        Notification::make()->title('Emailuri retrimise')->success()->send();
                    }),
            ]);
    }
}
