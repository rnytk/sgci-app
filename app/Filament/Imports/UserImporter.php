<?php

namespace App\Filament\Imports;

use App\Models\User;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;
use Illuminate\Support\Facades\Hash;

class UserImporter extends Importer
{
    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Nombre')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
                ImportColumn::make('email')
                ->label('Correo electrónico')
                ->requiredMapping()
                ->rules(['required', 'email', 'max:255']),
            /*ImportColumn::make('email_verified_at')
                ->rules(['email', 'datetime']),*/
            /*ImportColumn::make('password')
                ->requiredMapping()
                ->rules(['required', 'max:255']),*/
            ImportColumn::make('agency')
            ->label('Agencia')
                ->relationship(),
            ImportColumn::make('jobPosition')
                ->label('Puesto')
                ->relationship(),
            ImportColumn::make('is_active')
                ->label('Activo')
                ->requiredMapping()
                ->boolean()
                ->rules(['required', 'boolean']),
        ];
    }

    public function resolveRecord(): User
    {
        $user = User::firstOrNew([
        'email' => $this->data['email'],
    ]);

    if (! $user->exists) {
        $user->password = Hash::make('capacitacion123');
    }

    return $user;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your user import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
