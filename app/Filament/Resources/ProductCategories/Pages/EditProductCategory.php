<?php

namespace App\Filament\Resources\ProductCategories\Pages;

use App\Filament\Resources\ProductCategories\ProductCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use LaraZeus\SpatieTranslatable\Resources\Pages\EditRecord\Concerns\Translatable;

class EditProductCategory extends EditRecord
{
    use Translatable;

    protected static string $resource = ProductCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher::make(),
            DeleteAction::make(),
        ];
    }
}
