<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Tên')
                    ->required(),
                TextInput::make('email')->label('Email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')->label('Mật khẩu')
                    ->password()
                    ->required(),
            ]);
    }
}
