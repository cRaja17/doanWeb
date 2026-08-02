<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class ManagePaymentConfig extends Page
{
    protected static ?string $navigationLabel = 'Thanh toán';
    protected ?string $heading = 'Thanh toán';
    protected static ?string $title = 'Thanh toán';
    protected static ?string $cluster = \App\Filament\Clusters\Config\ConfigCluster::class;

    protected string $view = 'filament.pages.manage-payment-config';
}
