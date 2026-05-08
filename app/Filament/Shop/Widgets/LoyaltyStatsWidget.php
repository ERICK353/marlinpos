<?php

namespace App\Filament\Shop\Widgets;

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
        $freeHaircuts  = Transaction::where('is_free_haircut', true)->count();
        $eligible      = Customer::where('loyalty_count', '>=', 9)->count();

        return [
            Stat::make('Enrolled Members', $enrolled)
                ->description('In loyalty programme')
                ->descriptionIcon('heroicon-m-identification')
                ->color('primary'),

            Stat::make('Free Haircuts Given', $freeHaircuts)
                ->description('All time')
                ->descriptionIcon('heroicon-m-gift')
                ->color('success'),

            Stat::make('Ready for Free Haircut', $eligible)
                ->description('9 haircuts completed')
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),
        ];
    }
}
