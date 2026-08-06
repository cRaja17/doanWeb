<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('transaction_code')
                    ->default(null),
                TextInput::make('name')->label('Tên')
                    ->default(null),
                TextInput::make('gender')
                    ->default(null),
                TextInput::make('email')->label('Email')
                    ->label('Email address')
                    ->email()
                    ->default(null),
                TextInput::make('phone')->label('Số điện thoại')
                    ->tel()
                    ->default(null),
                Textarea::make('address')->label('Địa chỉ')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('message')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('method_payment')->label('Phương thức TT')
                    ->default(null),
                // Dòng hàng đã chuyển sang bảng `order_details` (xem tab Chi tiết
                // đơn hàng). Cột này chỉ còn là bản lưu JSON của đơn cũ.
                Textarea::make('content')->label('Bản lưu JSON (cũ)')
                    ->default(null)
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpanFull(),
                TextInput::make('total')->label('Tổng tiền')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('unit_payment')
                    ->default(null),
                TextInput::make('ip')
                    ->default(null),
                TextInput::make('proxy')
                    ->default(null),
                // Chỉ cổng thanh toán (payOS webhook / PayPal capture) được đổi cột này.
                // `disabled()` khoá phía UI, `dehydrated(false)` đảm bảo giá trị gửi lên
                // từ request bị bỏ qua kể cả khi có người sửa DOM.
                Select::make('status')->label('Trạng thái thanh toán')
                    ->options(Order::statusOptions())
                    ->default(Order::STATUS_PENDING)
                    ->native(false)
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('Do cổng thanh toán tự cập nhật, không sửa thủ công.'),
                Select::make('order_status')->label('Tình trạng')
                    ->options(Order::orderStatusOptions())
                    ->default(Order::ORDER_STATUS_WAITING)
                    ->native(false)
                    ->required(),
            ]);
    }
}
