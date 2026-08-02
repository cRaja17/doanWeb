<?php

namespace App\Filament\Resources\ProductCategories\Pages;

use App\Filament\Resources\ProductCategories\ProductCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use LaraZeus\SpatieTranslatable\Resources\Pages\ListRecords\Concerns\Translatable;

class ListProductCategories extends ListRecords
{
    use Translatable;

    protected static string $resource = ProductCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher::make(),
            CreateAction::make(),
        ];
    }
}
