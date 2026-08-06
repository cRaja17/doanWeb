<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Chi tiết dòng hàng của đơn. Chỉ để xem: dữ liệu là bản chụp lúc khách đặt
 * hàng nên không mở action thêm/sửa/xoá.
 */
class OrderDetailsRelationManager extends RelationManager
{
    protected static string $relationship = 'details';

    protected static ?string $title = 'Chi tiết đơn hàng';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('product.image')->label('Ảnh')
                    ->disk('public')
                    ->square(),
                TextColumn::make('name')->label('Sản phẩm')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('product_code')->label('Mã SP')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('quantity')->label('SL'),
                TextColumn::make('price')->label('Đơn giá')
                    ->formatStateUsing(fn ($state, $record): string => static::money($state, $record->currency_code)),
                TextColumn::make('subtotal')->label('Thành tiền')
                    ->formatStateUsing(fn ($state, $record): string => static::money($state, $record->currency_code))
                    ->weight('bold'),
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([])
            ->paginated(false);
    }

    /**
     * Giữ phần thập phân khi có (49.99 USD) nhưng không thêm ",00" thừa cho VND.
     */
    protected static function money($value, ?string $currency): string
    {
        $amount = (float) $value;

        $formatted = fmod($amount, 1.0) === 0.0
            ? number_format($amount)
            : number_format($amount, 2);

        return trim($formatted . ' ' . ($currency ?? ''));
    }
}
