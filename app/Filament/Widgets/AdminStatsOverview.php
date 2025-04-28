<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalWarga = User::where('role', 'warga')->count();
        $totalPayments = Payment::count();
        $pendingPayments = Payment::where('status', 'pending')->count();
        $verifiedPayments = Payment::where('status', 'verified')->count();
        
        $totalAmount = Payment::where('status', 'verified')->sum('amount');

        return [
            Stat::make('Total Warga', $totalWarga)
                ->description('Registered community members')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
            Stat::make('Total Payments', $totalPayments)
                ->description('All payment records')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),
            Stat::make('Pending Payments', $pendingPayments)
                ->description('Awaiting verification')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            Stat::make('Verified Payments', $verifiedPayments)
                ->description('Successfully verified')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Total Amount Collected', 'Rp ' . number_format($totalAmount, 0, ',', '.'))
                ->description('From verified payments')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
}