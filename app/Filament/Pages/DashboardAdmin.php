<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AffiliateChart;
use App\Filament\Widgets\GGROverview;
use App\Filament\Widgets\GgrTableWidget;
use App\Filament\Widgets\StatsOverview;
use App\Livewire\AffiliateWidgets;
use App\Livewire\LatestAdminComissions;
use App\Livewire\WalletOverview;
use App\Livewire\AdminWidgets;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Filament\Pages\Dashboard\Concerns\HasFiltersAction;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class DashboardAdmin extends \Filament\Pages\Dashboard
{
    use HasFiltersForm, HasFiltersAction;

    /**
     * @return string|\Illuminate\Contracts\Support\Htmlable|null
     */
    public function getSubheading(): string| null|\Illuminate\Contracts\Support\Htmlable
    {
        $roleName = 'Администратор';
        if(auth()->user()->hasRole('afiliado')) {
            $roleName = 'Партнер';
        }

        return "Здравствуйте, $roleName! Добро пожаловать в вашу панель управления.";
    }

    /**
     * @param Form $form
     * @return Form
     */
    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
//                Section::make()
//                    ->schema([
//                        DatePicker::make('startDate'),
//                        DatePicker::make('endDate'),
//                    ])
//                    ->columns(2),
            ]);
    }

    /**
     * @return array|\Filament\Actions\Action[]|\Filament\Actions\ActionGroup[]
     */
    protected function getHeaderActions(): array
    {
        return [
            FilterAction::make()
                ->label('Фильтр')
                ->form([
                    DatePicker::make('startDate')->label('Начальная дата'),
                    DatePicker::make('endDate')->label('Конечная дата'),
                ]),
        ];
    }


    /**
     * @return string[]
     */
    public function getWidgets(): array
    {
        return [
            AffiliateWidgets::class,
            AdminWidgets::class,
            AffiliateChart::class,
            LatestAdminComissions::class,
            WalletOverview::class,
            StatsOverview::class,
            GGROverview::class,
            GgrTableWidget::class,
        ];
    }
}
