<?php

namespace App\Filament\Resources\LanguageLines;

use App\Filament\Resources\LanguageLines\Pages\CreateLanguageLine;
use App\Filament\Resources\LanguageLines\Pages\EditLanguageLine;
use App\Filament\Resources\LanguageLines\Pages\ListLanguageLines;
use App\Filament\Resources\LanguageLines\Schemas\LanguageLineForm;
use App\Filament\Resources\LanguageLines\Tables\LanguageLinesTable;
use App\Models\LanguageLine;
use BackedEnum;
use Filament\Resources\Resource;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LanguageLineResource extends Resource
{
    protected static ?string $modelLabel = 'Cấu hình ngôn ngữ';
    protected static ?string $pluralModelLabel = 'Cấu hình ngôn ngữ';
    protected static ?string $navigationLabel = 'Cấu hình ngôn ngữ';

    use Translatable;

    protected static ?string $model = LanguageLine::class;
    protected static ?int $navigationSort = 7;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return LanguageLineForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LanguageLinesTable::configure($table);
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
            'index' => ListLanguageLines::route('/'),
            'create' => CreateLanguageLine::route('/create'),
            'edit' => EditLanguageLine::route('/{record}/edit'),
        ];
    }
}
