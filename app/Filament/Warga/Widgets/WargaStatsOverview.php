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
            Stat::make('Pembayaran', $totalPayments)
                ->description('Seluruh riwayat pembayaran')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),
            Stat::make('Pembayaran Tertunda', $pendingPayments)
                ->description('Menunggu verifikasi admin')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            Stat::make('Pembayaran Terverifikasi', $verifiedPayments)
                ->description('Sukses terverifikasi')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Total Pembayaran', 'Rp ' . number_format($totalAmountPaid, 0, ',', '.'))
                ->description('Jumlah pembayaran berhasil')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            // Stat::make('Active Bank Accounts', $activeBankAccounts)
            //     ->description('Available for payments')
            //     ->descriptionIcon('heroicon-m-building-library')
            //     ->color('primary'),
        ];
    }
}