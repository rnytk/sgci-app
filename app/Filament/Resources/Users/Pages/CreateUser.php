<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Filament\Traits\RedirectToIndex;
use App\Services\UserInvitationService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateUser extends CreateRecord
{
    use RedirectToIndex;
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['password'] = Hash::make(Str::password(32));

        return $data;
    }

    protected function afterCreate(): void
    {
        app(UserInvitationService::class)->send($this->record);
    }
}
