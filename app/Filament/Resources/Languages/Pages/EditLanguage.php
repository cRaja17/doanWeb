<?php

namespace App\Filament\Resources\Languages\Pages;

use App\Filament\Resources\Languages\LanguageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use LaraZeus\SpatieTranslatable\Resources\Pages\EditRecord\Concerns\Translatable;

class EditLanguage extends EditRecord
{
    use Translatable;

    protected static string $resource = LanguageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher::make(),
            DeleteAction::make(),
        ];
    }
}
