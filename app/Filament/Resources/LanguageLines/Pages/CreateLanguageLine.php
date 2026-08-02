<?php

namespace App\Filament\Resources\LanguageLines\Pages;

use App\Filament\Resources\LanguageLines\LanguageLineResource;
use Filament\Resources\Pages\CreateRecord;
use LaraZeus\SpatieTranslatable\Resources\Pages\CreateRecord\Concerns\Translatable;

class CreateLanguageLine extends CreateRecord
{
    use Translatable;

    
    protected function getHeaderActions(): array
    {
        return [
            \LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher::make(),
        ];
    }
protected static string $resource = LanguageLineResource::class;
}
