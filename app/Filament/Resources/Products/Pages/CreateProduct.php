<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;
use LaraZeus\SpatieTranslatable\Resources\Pages\CreateRecord\Concerns\Translatable;

class CreateProduct extends CreateRecord
{
    use Translatable;

    
    protected function getHeaderActions(): array
    {
        return [
            \LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher::make(),
        ];
    }
protected static string $resource = ProductResource::class;
}
