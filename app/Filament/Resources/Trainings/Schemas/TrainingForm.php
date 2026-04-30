<?php

namespace App\Filament\Resources\Trainings\Schemas;

use App\Models\Training;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class TrainingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del Catálogo')
                    ->description('Defina el título, descripción y slug de la capacitación.')
                    ->schema([
                        // Trazabilidad: Quién registra el curso
                        Hidden::make('user_id')
                            ->default(auth()->id()),

                        TextInput::make('title')
                            ->label('Título de la Capacitación')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),

                        TextInput::make('slug')
                            ->label('Slug / URL Amigable')
                            ->required()
                            ->unique(Training::class, 'slug', ignoreRecord: true)
                            ->readOnly()
                            ->prefix('sgci/'),

                        Textarea::make('description')
                            ->label('Resumen del Contenido')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Recursos Multimedia')
                    ->description('Módulo de Recursos (media_resources)')
                    ->schema([
                        Repeater::make('mediaResources')
                            ->relationship('mediaResources') // Relación HasMany
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nombre del Recurso')
                                    ->placeholder('Ej. Manual PDF')
                                    ->required(),
                                
                                Select::make('type')
                                    ->label('Tipo')
                                    ->options([
                                        'pdf' => 'Documento PDF',
                                        'video' => 'Video / Stream',
                                        'link' => 'Enlace Externo',
                                    ])
                                    ->required(),

                                TextInput::make('url_path')
                                    ->label('Ruta o URL')
                                    ->placeholder('https://...')
                                    ->required(),
                            ])
                            ->columns(3)
                            ->defaultItems(0)
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null),
                    ]),
            ]);
    }
}