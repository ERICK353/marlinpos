<?php

namespace App\Filament\Shop\Resources;

use App\Filament\Shop\Resources\EmployeeSalaryResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class EmployeeSalaryResource extends Resource
{
    protected static ?string $model = User::class;

    public static function getNavigationIcon(): string { return 'heroicon-o-banknotes'; }
    public static function getNavigationGroup(): ?string { return 'Staff Management'; }
    public static function getNavigationLabel(): string { return 'Employee Salaries'; }
    public static function getModelLabel(): string { return 'Employee Salary'; }
    public static function getNavigationSort(): ?int { return 2; }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('role', 'staff');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Employee Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextInputColumn::make('commission_rate')
                    ->label('Comm. Rate (%)')
                    ->type('number')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_revenue')
                    ->label('Total Revenue')
                    ->money('KES')
                    ->getStateUsing(fn (User $record) => $record->transactionItems()->sum('line_total')),
                Tables\Columns\TextColumn::make('total_commission')
                    ->label('Total Commission')
                    ->money('KES')
                    ->color('success')
                    ->getStateUsing(function (User $record) {
                        return $record->transactionItems()
                            ->get()
                            ->sum(fn ($item) => $item->commission_amount ?? ($item->line_total * ($record->commission_rate ?? 40) / 100));
                    }),
            ])
            ->filters([
                Tables\Filters\Filter::make('served_at')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereHas('transactionItems', fn ($q) => 
                                    $q->whereHas('transaction', fn ($t) => $t->whereDate('served_at', '>=', $date))
                                ),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereHas('transactionItems', fn ($q) => 
                                    $q->whereHas('transaction', fn ($t) => $t->whereDate('served_at', '<=', $date))
                                ),
                            );
                    })
            ])
            ->actions([
                \Filament\Actions\Action::make('download_csv')
                    ->label('Download CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function (User $record) {
                        $csvData = [];
                        $csvData[] = ['Date', 'TX #', 'Customer', 'Staff Name', 'Service', 'Price (KES)', 'Commission Rate', 'Commission Earned (KES)'];

                        $items = $record->transactionItems()->with(['transaction.customer', 'service'])->get();
                        
                        foreach ($items as $item) {
                            $commission = $item->commission_amount ?? ($item->line_total * ($item->commission_rate ?? $record->commission_rate ?? 40) / 100);
                            
                            $csvData[] = [
                                $item->transaction->served_at->format('Y-m-d H:i'),
                                $item->transaction_id,
                                $item->transaction->customer->phone ?? 'Walk-in',
                                $record->name,
                                $item->service->name ?? 'N/A',
                                $item->line_total,
                                ($item->commission_rate ?? $record->commission_rate ?? 40) . '%',
                                $commission
                            ];
                        }
                        
                        // Summary row
                        $totalRevenue = $items->sum('line_total');
                        $totalCommission = $items->sum(fn ($i) => $i->commission_amount ?? ($i->line_total * ($i->commission_rate ?? $record->commission_rate ?? 40) / 100));
                        
                        $csvData[] = ['', '', '', "TOTAL FOR {$record->name}", '', $totalRevenue, '', $totalCommission];

                        $filename = "salary_report_{$record->name}_" . date('Y-m-d') . ".csv";

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
                \Filament\Actions\Action::make('view_details')
                    ->label('View Items')
                    ->icon('heroicon-o-eye')
                    ->url(fn (User $record) => TransactionResource::getUrl('index', [
                        'tableFilters[staff][value]' => $record->id,
                    ])),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployeeSalaries::route('/'),
        ];
    }
}
