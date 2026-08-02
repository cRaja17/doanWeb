<?php

namespace App\Filament\Resources\Languages\Pages;

use App\Filament\Resources\Languages\LanguageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use LaraZeus\SpatieTranslatable\Resources\Pages\ListRecords\Concerns\Translatable;

class ListLanguages extends ListRecords
{
    use Translatable;

    protected static string $resource = LanguageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher::make(),
            CreateAction::make(),
        ];
    }
}
