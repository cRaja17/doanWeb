<?php

namespace App\Filament\Resources\LanguageLines\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class LanguageLineForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('group')->label('Nhóm')
                    ->required(),
                TextInput::make('key')->label('Khóa')
                    ->required(),
                Textarea::make('text')->label('Văn bản')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
