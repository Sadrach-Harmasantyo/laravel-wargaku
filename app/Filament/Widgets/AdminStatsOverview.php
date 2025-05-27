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
        $rejectedPayments = Payment::where('status', 'rejected')->count();
        
        $totalAmount = Payment::where('status', 'verified')->sum('amount');

        return [
            Stat::make('Total Warga', $totalWarga)
                ->description('Total warga terdaftar')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
            Stat::make('Total Pembayaran', $totalPayments)
                ->description('Total pembayaran yang dilakukan')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),
            Stat::make('Pembayaran Tertunda', $pendingPayments)
                ->description('Menunggu verifikasi')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            Stat::make('Pembayaran Terverifikasi', $verifiedPayments)
                ->description('Sukses terverifikasi')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Pembayaran Ditolak', $rejectedPayments)
                ->description('Pembayaran bermasalah')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
            Stat::make('Total Uang Terkumpul', 'Rp ' . number_format($totalAmount, 0, ',', '.'))
                ->description('Pembayaran terverifikasi')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
}