<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AdminStatsOverview;
use Filament\Pages\Dashboard as BasePage;

class Dashboard extends BasePage
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected function getHeaderWidgets(): array
    {
        return [
            AdminStatsOverview::class,
        ];
    }
}