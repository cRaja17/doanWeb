<?php

namespace App\Filament\Clusters\Config;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class ConfigCluster extends Cluster
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationLabel = 'Cấu hình';
}
