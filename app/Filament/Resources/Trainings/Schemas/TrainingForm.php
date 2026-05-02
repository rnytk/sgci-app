<?php

namespace App\Filament\Resources\Trainings\Schemas;

use App\Models\Training;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;
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
                            ->required(),

                        Select::make('type')
                            ->label('Tipo de Contenido')
                            ->options([
                                'pdf' => 'Documento PDF (Cargar)',
                                'video' => 'Video (YouTube/Vimeo)',
                            ])
                            ->required()
                            ->live(), // Habilita la reactividad inmediata para los demás campos

                        // CAMPO A: Se muestra solo si es PDF
                       // CAMPO 1: Para archivos físicos (Usa el nombre real de la DB)
                                FileUpload::make('url_path')
                                    ->label('Adjuntar PDF')
                                    ->directory('trainings')
                                    ->visible(fn (Get $get) => $get('type') === 'pdf')
                                    ->required(fn (Get $get) => $get('type') === 'pdf'),

                                // CAMPO 2: Para enlaces (Nombre diferente para evitar colisión)
                                TextInput::make('external_url')
                                    ->label('URL del Video/Enlace')
                                    ->placeholder('https://...')
                                    ->visible(fn (Get $get) => $get('type') === 'video')
                                    ->required(fn (Get $get) => $get('type') === 'video')
                                    // Esta es la magia: cuando cargue el registro, si es video, pon el valor de url_path aquí
                                    ->afterStateHydrated(function (TextInput $component, $state, $record) {
                                        if ($record && $record->type === 'video') {
                                            $component->state($record->url_path);
                                        }
                                    })
                                    // Cuando el usuario escriba, guarda el valor en el campo real url_path
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        $set('url_path', $state);
                                    }),


                            ])
                            ->columns(3)
                            ->defaultItems(0)
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null),
                    ]),
            ]);
    }
}
