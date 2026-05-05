<?php

namespace App\Filament\Admin\Resources\EmployeeSalaryResource\Pages;

use App\Filament\Admin\Resources\EmployeeSalaryResource;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeSalaries extends ListRecords
{
    protected static string $resource = EmployeeSalaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('download_all_csv')
                ->label('Download All (Detailed)')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    $staffUsers = \App\Models\User::where('role', 'staff')->get();
                    $csvData = [];
                    $csvData[] = ['Date', 'TX #', 'Customer', 'Staff Name', 'Service', 'Price (KES)', 'Commission Rate', 'Commission Earned (KES)'];

                    foreach ($staffUsers as $staff) {
                        $items = $staff->transactionItems()->with(['transaction.customer', 'service'])->get();
                        
                        foreach ($items as $item) {
                            $commission = $item->commission_amount ?? ($item->line_total * ($item->commission_rate ?? $staff->commission_rate ?? 40) / 100);
                            
                            $csvData[] = [
                                $item->transaction->served_at->format('Y-m-d H:i'),
                                $item->transaction_id,
                                $item->transaction->customer->phone ?? 'Walk-in',
                                $staff->name,
                                $item->service->name ?? 'N/A',
                                $item->line_total,
                                ($item->commission_rate ?? $staff->commission_rate ?? 40) . '%',
                                $commission
                            ];
                        }
                        
                        // Summary row
                        $totalRevenue = $items->sum('line_total');
                        $totalCommission = $items->sum(fn ($i) => $i->commission_amount ?? ($i->line_total * ($i->commission_rate ?? $staff->commission_rate ?? 40) / 100));
                        
                        $csvData[] = ['', '', '', "TOTAL FOR {$staff->name}", '', $totalRevenue, '', $totalCommission];
                        $csvData[] = []; // Spacer
                    }

                    $filename = "all_staff_detailed_report_" . date('Y-m-d') . ".csv";

                    return response()->streamDownload(function () use ($csvData) {
                        $handle = fopen('php://output', 'w');
                        foreach ($csvData as $row) {
                            fputcsv($handle, $row);
                        }
                        fclose($handle);
                    }, $filename, [
                        'Content-Type' => 'text/csv',
                    ]);
                }),
        ];
    }
}
