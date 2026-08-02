<?php

namespace App\Filament\Resources\PostCategories\Pages;

use App\Filament\Resources\PostCategories\PostCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use LaraZeus\SpatieTranslatable\Resources\Pages\EditRecord\Concerns\Translatable;

class EditPostCategory extends EditRecord
{
    use Translatable;

    protected static string $resource = PostCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher::make(),
            DeleteAction::make(),
        ];
    }
}
