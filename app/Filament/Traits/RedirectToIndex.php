<?php

namespace App\Filament\Traits;

trait RedirectToIndex
{
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}