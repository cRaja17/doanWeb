<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class ManageHomeConfig extends Page
{
    protected static ?string $navigationLabel = 'Trang chủ';
    protected ?string $heading = 'Trang chủ';
    protected static ?string $title = 'Trang chủ';
    protected static ?string $cluster = \App\Filament\Clusters\Config\ConfigCluster::class;

    protected string $view = 'filament.pages.manage-home-config';
}
