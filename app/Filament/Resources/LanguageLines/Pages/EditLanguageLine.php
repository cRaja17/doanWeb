<?php

namespace App\Filament\Resources\LanguageLines\Pages;

use App\Filament\Resources\LanguageLines\LanguageLineResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use LaraZeus\SpatieTranslatable\Resources\Pages\EditRecord\Concerns\Translatable;

class EditLanguageLine extends EditRecord
{
    use Translatable;

    protected static string $resource = LanguageLineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher::make(),
            DeleteAction::make(),
        ];
    }
}
