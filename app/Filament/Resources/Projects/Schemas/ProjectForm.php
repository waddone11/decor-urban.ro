<?php

namespace App\Filament\Resources\Projects\Schemas;

use App\Models\Project;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Proiect')
                    ->columns(2)
                    ->components([
                        TextInput::make('title')
                            ->label('Titlu')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug((string) $state))),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Generat automat din titlu; editabil.'),
                        TextInput::make('location')
                            ->label('Locație')
                            ->placeholder('ex. Primăria Slatina, Olt')
                            ->maxLength(255),
                        Select::make('client_type')
                            ->label('Tip client')
                            ->options(Project::CLIENT_TYPES)
                            ->native(false),
                        TextInput::make('year')
                            ->label('An')
                            ->maxLength(10),
                        TextInput::make('sort_order')
                            ->label('Ordine')
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_published')
                            ->label('Publicat')
                            ->helperText('Apare public pe /proiecte doar când e activ.'),
                    ]),

                Section::make('Conținut')
                    ->components([
                        Textarea::make('summary')
                            ->label('Rezumat (card)')
                            ->rows(2)
                            ->maxLength(500)
                            ->columnSpanFull(),
                        RichEditor::make('body')
                            ->label('Ce am făcut')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
