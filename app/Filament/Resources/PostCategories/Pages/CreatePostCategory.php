<?php

namespace App\Filament\Resources\PostCategories\Pages;

use App\Filament\Resources\PostCategories\PostCategoryResource;
use Filament\Resources\Pages\CreateRecord;
use LaraZeus\SpatieTranslatable\Resources\Pages\CreateRecord\Concerns\Translatable;

class CreatePostCategory extends CreateRecord
{
    use Translatable;

    
    protected function getHeaderActions(): array
    {
        return [
            \LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher::make(),
        ];
    }
protected static string $resource = PostCategoryResource::class;
}
