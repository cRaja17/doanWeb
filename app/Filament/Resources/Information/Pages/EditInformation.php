<?php

namespace App\Filament\Resources\Information\Pages;

use App\Filament\Resources\Information\InformationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use LaraZeus\SpatieTranslatable\Resources\Pages\EditRecord\Concerns\Translatable;

class EditInformation extends EditRecord
{
    use Translatable;

    protected static string $resource = InformationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher::make(),
            DeleteAction::make(),
        ];
    }
}
