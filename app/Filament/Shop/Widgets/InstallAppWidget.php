<?php

namespace App\Filament\Shop\Widgets;

use Filament\Widgets\Widget;

class InstallAppWidget extends Widget
{
    protected static string $view = 'filament.shop.widgets.install-app-widget';
    
    protected static ?int $sort = -10; // Show at the very top
}
