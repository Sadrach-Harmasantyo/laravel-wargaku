<?php

namespace App\Filament\Warga\Pages;

use App\Filament\Warga\Widgets\WargaStatsOverview;
use Filament\Pages\Dashboard as BasePage;

class Dashboard extends BasePage
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected function getHeaderWidgets(): array
    {
        return [
            WargaStatsOverview::class,
        ];
    }
}