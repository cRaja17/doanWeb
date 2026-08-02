<?php

namespace App\Filament\Resources\Information;

use App\Filament\Resources\Information\Pages\CreateInformation;
use App\Filament\Resources\Information\Pages\EditInformation;
use App\Filament\Resources\Information\Pages\ListInformation;
use App\Filament\Resources\Information\Schemas\InformationForm;
use App\Filament\Resources\Information\Tables\InformationTable;
use App\Models\Information;
use BackedEnum;
use Filament\Resources\Resource;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class InformationResource extends Resource
{
    protected static ?string $modelLabel = 'Thông tin';
    protected static ?string $pluralModelLabel = 'Thông tin';
    protected static ?string $navigationLabel = 'Trang thông tin';
    protected static ?int $navigationSort = 6;

    use Translatable;

    protected static ?string $model = Information::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return InformationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InformationTable::configure($table);
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
            'index' => ListInformation::route('/'),
            'create' => CreateInformation::route('/create'),
            'edit' => EditInformation::route('/{record}/edit'),
        ];
    }
}
