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
use Illuminate\Support\Facades\Auth;
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
                            ->dehydrated(false),
                              
                        TextInput::make('title')
                            ->label('Título de la Capacitación')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn(Set $set, ?string $state) => $set('slug', Str::slug($state))),

                        TextInput::make('slug')
                            ->label('Slug / URL Amigable')
                            ->required()
                            ->unique(Training::class, 'slug', ignoreRecord: true)
                            ->readOnly()
                            ->prefix('sgci/'),

                        Textarea::make('description')
                            ->label('Resumen del Contenido')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),

                Repeater::make('mediaResources')
                    ->relationship('mediaResources')
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
                            ->live(),

                        FileUpload::make('url_path')
                            ->label('Adjuntar PDF')
                            ->disk('public')
                            ->directory('trainings')
                            ->visible(fn(Get $get) => $get('type') === 'pdf')
                            ->required(fn(Get $get) => $get('type') === 'pdf'),

                        TextInput::make('external_url')
                            ->label('URL del Video/Enlace')
                            ->placeholder('https://...')
                            ->visible(fn(Get $get) => $get('type') === 'video')
                            ->required(fn(Get $get) => $get('type') === 'video')

                            ->afterStateHydrated(function (TextInput $component, $state, $record) {
                                if ($record && $record->type === 'video') {
                                    $component->state($record->url_path);
                                }
                            }),
                    ])
                    ->columns(3)
                    ->defaultItems(0)
                    ->itemLabel(fn(array $state): ?string => $state['name'] ?? null)
                
                    ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                        if (($data['type'] ?? null) === 'video' && empty($data['url_path'])) {
                            $data['url_path'] = $data['external_url'] ?? null;
                        }
                        unset($data['external_url']);
                        return $data;
                    })
            
                    ->mutateRelationshipDataBeforeSaveUsing(function (array $data): array {
                        if (($data['type'] ?? null) === 'video' && empty($data['url_path'])) {
                            $data['url_path'] = $data['external_url'] ?? null;
                        }
                        unset($data['external_url']);
                        return $data;
                    }),
            ]);
    }
}
