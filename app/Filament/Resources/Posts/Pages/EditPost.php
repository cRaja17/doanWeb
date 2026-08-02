<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use LaraZeus\SpatieTranslatable\Resources\Pages\EditRecord\Concerns\Translatable;

class EditPost extends EditRecord
{
    use Translatable;

    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher::make(),
            DeleteAction::make(),
        ];
    }
}
