<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class ManageHeaderFooterConfig extends Page
{
    protected static ?string $navigationLabel = 'Header & Footer';
    protected ?string $heading = 'Header & Footer';
    protected static ?string $title = 'Header & Footer';
    protected static ?string $cluster = \App\Filament\Clusters\Config\ConfigCluster::class;

    protected string $view = 'filament.pages.manage-header-footer-config';
}
