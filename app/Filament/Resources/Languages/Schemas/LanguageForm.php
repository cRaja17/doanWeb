<?php

namespace App\Filament\Resources\Languages\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Awcodes\Curator\Models\Media;
use Filament\Schemas\Schema;

class LanguageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Tên')
                    ->required(),
                CuratorPicker::make('flag')->label('Cờ')
                    ->afterStateHydrated(function (CuratorPicker $component, $state): void {
                        if (filled($state) && !is_numeric($state) && is_string($state)) {
                            $state = Media::where('path', $state)->value('id');
                        }

                        $component->state(
                            filled($state) ? get_media_items([$state])->toArray() : [],
                        );
                    })
                    ->dehydrateStateUsing(
                        fn($state, $record) => filled($state)
                        ? Media::find(collect($state)->first()['id'] ?? null)?->path
                        : $record?->getRawOriginal('flag'),
                    )
                    ->default(null)
                    ->disk('public')
                    ->constrained(),
                TextInput::make('abbr')
                    ->default(null),
                TextInput::make('script')
                    ->default(null),
                TextInput::make('native')
                    ->default(null),
                TextInput::make('unit')->label('Đơn vị')
                    ->default('VND'),
                Toggle::make('active')->label('Trạng thái')
                    ->required()
                    ->default(true),
                Toggle::make('default')->label('Mặc định')
                    ->required()
                    ->default(false),
            ]);
    }
}
