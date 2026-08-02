<?php

namespace App\Filament\Resources\PostCategories\Pages;

use App\Filament\Resources\PostCategories\PostCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use LaraZeus\SpatieTranslatable\Resources\Pages\ListRecords\Concerns\Translatable;

class ListPostCategories extends ListRecords
{
    use Translatable;

    protected static string $resource = PostCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher::make(),
            CreateAction::make(),
        ];
    }
}
