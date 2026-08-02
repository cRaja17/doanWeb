<?php

namespace App\Filament\Resources\Languages;

use App\Filament\Resources\Languages\Pages\CreateLanguage;
use App\Filament\Resources\Languages\Pages\EditLanguage;
use App\Filament\Resources\Languages\Pages\ListLanguages;
use App\Filament\Resources\Languages\Schemas\LanguageForm;
use App\Filament\Resources\Languages\Tables\LanguagesTable;
use App\Models\Language;
use BackedEnum;
use Filament\Resources\Resource;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LanguageResource extends Resource
{
    protected static ?string $modelLabel = 'Ngôn ngữ';
    protected static ?string $pluralModelLabel = 'Ngôn ngữ';
    protected static ?string $navigationLabel = 'Ngôn ngữ';

    use Translatable;

    protected static ?string $model = Language::class;
    protected static ?int $navigationSort = 6;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return LanguageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LanguagesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLanguages::route('/'),
            'create' => CreateLanguage::route('/create'),
            'edit' => EditLanguage::route('/{record}/edit'),
        ];
    }
}
