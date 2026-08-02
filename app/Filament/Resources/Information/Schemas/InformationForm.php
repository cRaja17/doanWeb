<?php

namespace App\Filament\Resources\Information\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Toggle;
use App\Support\SlugField;

class InformationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Grid::make(2)->columnSpanFull()->schema([
                    TextInput::make('name')->label('Tên')
                        ->default(null)
                        ->live(onBlur: true)
                        ->afterStateUpdated(SlugField::suggestFrom('name')),
                    SlugField::make('information'),
                ]),

                \Filament\Schemas\Components\Grid::make(2)->columnSpanFull()->schema([
                    Textarea::make('meta_title')->label('SEO Tiêu đề')
                        ->default(null)->rows(2),
                    Textarea::make('meta_keyword')->label('SEO Từ khóa')
                        ->default(null)->rows(2),
                ]),

                \Filament\Schemas\Components\Grid::make(2)->columnSpanFull()->schema([
                    Textarea::make('meta_description')->label('SEO Mô tả')
                        ->default(null)->rows(2),
                    \Filament\Forms\Components\Select::make('meta_robots')->label('SEO Robots')
                        ->options([
                            'index, follow' => 'Index, Follow',
                            'noindex, follow' => 'Noindex, Follow',
                            'index, nofollow' => 'Index, Nofollow',
                            'noindex, nofollow' => 'Noindex, Nofollow',
                        ])
                        ->default('index, follow'),
                ]),

                \Filament\Schemas\Components\Grid::make(2)->columnSpanFull()->schema([
                    \Filament\Forms\Components\Select::make('rel')->label('Rel')
                        ->options([
                            'dofollow' => 'Dofollow',
                            'nofollow' => 'Nofollow',
                        ])
                        ->default('dofollow'),
                    \Filament\Forms\Components\Select::make('target')->label('Target')
                        ->options([
                            '_self' => '_self (Cửa sổ hiện tại)',
                            '_blank' => '_blank (Cửa sổ mới)',
                        ])
                        ->default('_self'),
                ]),

                \Filament\Schemas\Components\Grid::make(3)->columnSpanFull()->schema([
                    Toggle::make('status')->label('Trạng thái')->default(true),
                    Toggle::make('view_header')->label('Hiển thị Header')->default(false),
                    Toggle::make('view_footer')->label('Hiển thị Footer')->default(false),
                ]),

                RichEditor::make('description')->label('Mô tả')
                    ->default(null)
                    ->fileAttachmentsDisk('public')
                    ->fileAttachmentsDirectory('media')
                    ->extraInputAttributes(['style' => 'min-height: 400px;'])
                    ->columnSpanFull(),
            ]);
    }
}
