<?php

namespace App\Filament\Resources\LanguageLines\Pages;

use App\Filament\Resources\LanguageLines\LanguageLineResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use LaraZeus\SpatieTranslatable\Resources\Pages\ListRecords\Concerns\Translatable;

class ListLanguageLines extends ListRecords
{
    use Translatable;

    protected static string $resource = LanguageLineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher::make(),
            CreateAction::make(),
        ];
    }
}
