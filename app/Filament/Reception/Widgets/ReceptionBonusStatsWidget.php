<?php

namespace App\Filament\Reception\Widgets;

use App\Models\Transaction;
use App\Models\TransactionItem;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;

class ReceptionBonusStatsWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 99;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate   = $this->filters['endDate'] ?? null;

        $isFiltered = $startDate || $endDate;

        if ($isFiltered) {
            return [];
        }

        return [];
    }
}
