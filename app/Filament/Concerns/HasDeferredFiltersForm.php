<?php

namespace App\Filament\Concerns;

use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;

trait HasDeferredFiltersForm
{
    use HasFiltersForm;

    /**
     * @var array<string, mixed> | null
     */
    public ?array $deferredFilters = null;

    public function getFiltersForm(): Schema
    {
        if ((! $this->isCachingSchemas) && $this->hasCachedSchema('filtersForm')) {
            return $this->getSchema('filtersForm');
        }

        $schema = $this->makeSchema()
            ->columns([
                'md' => 2,
                'xl' => 3,
                '2xl' => 4,
            ])
            ->extraAttributes(['wire:partial' => 'table-filters-form'])
            ->statePath('deferredFilters')
            ->partiallyRender();

        return $this->filtersForm($schema);
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('startDate')
                    ->label('Start Date'),
                DatePicker::make('endDate')
                    ->label('End Date'),
                Actions::make([
                    $this->getFiltersApplyAction(),
                    $this->getFiltersResetAction(),
                ])
                    ->alignment(Alignment::End)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public function applyFilters(): void
    {
        $this->filters = $this->deferredFilters;

        $this->updatedFilters();
    }

    public function resetFiltersForm(): void
    {
        $this->deferredFilters = null;
        $this->filters = null;

        $this->getFiltersForm()->fill();

        $this->updatedFilters();
    }

    public function getFiltersApplyAction(): Action
    {
        return Action::make('applyFilters')
            ->label('Apply Filters')
            ->action('applyFilters')
            ->button();
    }

    public function getFiltersResetAction(): Action
    {
        return Action::make('resetFilters')
            ->label('Reset')
            ->action('resetFiltersForm')
            ->color('gray')
            ->button();
    }
}
