<?php

namespace App\Filament\Widgets;

use App\Models\AffiliateHistory;
use App\Models\Order;
use App\Models\User;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Helpers\Core as Helper;

class StatsUserDetailOverview extends BaseWidget
{

    public User $record;

    public function mount($record)
    {
       $this->record = $record;
    }

    /**
     * @return array|Stat[]
     */
    protected function getStats(): array
    {
        $totalGanhos = Order::where('user_id', $this->record->id)->where('type', 'win')->sum('amount');
        $totalPerdas = Order::where('user_id', $this->record->id)->where('type', 'loss')->sum('amount');
        $totalAfiliados = AffiliateHistory::where('inviter', $this->record->id)->sum('commission_paid');

        return [
            Stat::make('Общий выигрыш', Helper::amountFormatDecimal(Helper::formatNumber($totalGanhos)))
                ->description('Общий выигрыш на платформе')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make('Общий проигрыш', Helper::amountFormatDecimal(Helper::formatNumber($totalPerdas)))
                ->description('Общий проигрыш на платформе')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
            Stat::make('Доход от партнерской программы', Helper::amountFormatDecimal(Helper::formatNumber($totalAfiliados)))
                ->description('Общий доход от партнерской программы')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
        ];
    }
}
