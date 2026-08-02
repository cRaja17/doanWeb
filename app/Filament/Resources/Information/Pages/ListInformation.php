<?php

namespace App\Filament\Resources\Information\Pages;

use App\Filament\Resources\Information\InformationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use LaraZeus\SpatieTranslatable\Resources\Pages\ListRecords\Concerns\Translatable;

class ListInformation extends ListRecords
{
    use Translatable;

    protected static string $resource = InformationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher::make(),
            CreateAction::make(),
        ];
    }
}
