<?php

namespace App\Filament\Warga\Widgets;

use App\Models\BankAccount;
use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class WargaStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $userId = Auth::id();
        
        $totalPayments = Payment::where('user_id', $userId)->count();
        $pendingPayments = Payment::where('user_id', $userId)->where('status', 'pending')->count();
        $verifiedPayments = Payment::where('user_id', $userId)->where('status', 'verified')->count();
        
        $totalAmountPaid = Payment::where('user_id', $userId)
            ->where('status', 'verified')
            ->sum('amount');
            
        // $activeBankAccounts = BankAccount::where('is_active', true)->count();

        return [
            Stat::make('My Payments', $totalPayments)
                ->description('All your payment records')
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
            Stat::make('Total Amount Paid', 'Rp ' . number_format($totalAmountPaid, 0, ',', '.'))
                ->description('From verified payments')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            // Stat::make('Active Bank Accounts', $activeBankAccounts)
            //     ->description('Available for payments')
            //     ->descriptionIcon('heroicon-m-building-library')
            //     ->color('primary'),
        ];
    }
}