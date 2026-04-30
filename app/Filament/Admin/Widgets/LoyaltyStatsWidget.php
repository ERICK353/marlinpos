<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Customer;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LoyaltyStatsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        $enrolled      = Customer::whereNotNull('enrolled_at')->count();
        $freeShaves    = Transaction::where('is_free_shave', true)->count();
        $eligible      = Customer::where('loyalty_count', '>=', 9)->count();

        return [
            Stat::make('Enrolled Members', $enrolled)
                ->description('In loyalty programme')
                ->descriptionIcon('heroicon-m-identification')
                ->color('primary'),

            Stat::make('Free Shaves Given', $freeShaves)
                ->description('All time')
                ->descriptionIcon('heroicon-m-gift')
                ->color('success'),

            Stat::make('Ready for Free Shave', $eligible)
                ->description('9 shaves completed')
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),
        ];
    }
}
