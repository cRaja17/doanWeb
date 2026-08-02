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
                Textarea::make('content')
                    ->default(null)
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
                Select::make('status')->label('Trạng thái thanh toán')
                    ->options(Order::statusOptions())
                    ->default(Order::STATUS_PENDING)
                    ->native(false)
                    ->required(),
                Select::make('order_status')->label('Tình trạng')
                    ->options(Order::orderStatusOptions())
                    ->default(Order::ORDER_STATUS_WAITING)
                    ->native(false)
                    ->required(),
            ]);
    }
}
