<?php

namespace App\Filament\Reception\Widgets;

use Filament\Widgets\Widget;

class InstallAppWidget extends Widget
{
    protected static string $view = 'filament.shop.widgets.install-app-widget'; // Reuse the same view
    
    protected static ?int $sort = -10;
}
