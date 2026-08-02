<?php

namespace App\Filament\Resources\Information\Pages;

use App\Filament\Resources\Information\InformationResource;
use Filament\Resources\Pages\CreateRecord;
use LaraZeus\SpatieTranslatable\Resources\Pages\CreateRecord\Concerns\Translatable;

class CreateInformation extends CreateRecord
{
    use Translatable;

    
    protected function getHeaderActions(): array
    {
        return [
            \LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher::make(),
        ];
    }
protected static string $resource = InformationResource::class;
}
