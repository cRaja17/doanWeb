<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use Filament\Resources\Pages\CreateRecord;
use LaraZeus\SpatieTranslatable\Resources\Pages\CreateRecord\Concerns\Translatable;

class CreatePost extends CreateRecord
{
    use Translatable;

    
    protected function getHeaderActions(): array
    {
        return [
            \LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher::make(),
        ];
    }
protected static string $resource = PostResource::class;
}
