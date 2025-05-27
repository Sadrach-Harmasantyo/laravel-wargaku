<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use App\Models\Payment;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FinancialOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Current month data
        $currentMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        
        // Current month income
        $currentMonthIncome = Payment::where('status', 'verified')
            ->whereYear('payment_date', $currentMonth->year)
            ->whereMonth('payment_date', $currentMonth->month)
            ->sum('amount');
            
        // Last month income
        $lastMonthIncome = Payment::where('status', 'verified')
            ->whereYear('payment_date', $lastMonth->year)
            ->whereMonth('payment_date', $lastMonth->month)
            ->sum('amount');
            
        // Income trend
        $incomeTrend = $lastMonthIncome > 0 
            ? ($currentMonthIncome - $lastMonthIncome) / $lastMonthIncome * 100 
            : 100;
            
        // Current month expenses
        $currentMonthExpenses = Expense::whereYear('expense_date', $currentMonth->year)
            ->whereMonth('expense_date', $currentMonth->month)
            ->sum('amount');
            
        // Last month expenses
        $lastMonthExpenses = Expense::whereYear('expense_date', $lastMonth->year)
            ->whereMonth('expense_date', $lastMonth->month)
            ->sum('amount');
            
        // Expense trend
        $expenseTrend = $lastMonthExpenses > 0 
            ? ($currentMonthExpenses - $lastMonthExpenses) / $lastMonthExpenses * 100 
            : 100;
            
        // Current balance
        $currentBalance = $currentMonthIncome - $currentMonthExpenses;
        
        // Last month balance
        $lastMonthBalance = $lastMonthIncome - $lastMonthExpenses;
        
        // Balance trend
        $balanceTrend = $lastMonthBalance != 0 
            ? ($currentBalance - $lastMonthBalance) / abs($lastMonthBalance) * 100 
            : 100;

        return [
            Stat::make('Pendapatan Bulan Ini', 'Rp ' . number_format($currentMonthIncome, 0, ',', '.'))
                ->description($incomeTrend >= 0 ? 'Naik ' . number_format(abs($incomeTrend), 1) . '%' : 'Turun ' . number_format(abs($incomeTrend), 1) . '%')
                ->descriptionIcon($incomeTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($incomeTrend >= 0 ? 'success' : 'danger')
                ->chart([
                    $lastMonthIncome / 1000, 
                    $currentMonthIncome / 1000
                ]),
                
            Stat::make('Pengeluaran Bulan Ini', 'Rp ' . number_format($currentMonthExpenses, 0, ',', '.'))
                ->description($expenseTrend <= 0 ? 'Turun ' . number_format(abs($expenseTrend), 1) . '%' : 'Naik ' . number_format(abs($expenseTrend), 1) . '%')
                ->descriptionIcon($expenseTrend <= 0 ? 'heroicon-m-arrow-trending-down' : 'heroicon-m-arrow-trending-up')
                ->color($expenseTrend <= 0 ? 'success' : 'danger')
                ->chart([
                    $lastMonthExpenses / 1000, 
                    $currentMonthExpenses / 1000
                ]),
                
            Stat::make('Saldo Bulan Ini', 'Rp ' . number_format($currentBalance, 0, ',', '.'))
                ->description($balanceTrend >= 0 ? 'Meningkat ' . number_format(abs($balanceTrend), 1) . '%' : 'Menurun ' . number_format(abs($balanceTrend), 1) . '%')
                ->descriptionIcon($balanceTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($balanceTrend >= 0 ? 'success' : 'danger')
                ->chart([
                    $lastMonthBalance / 1000, 
                    $currentBalance / 1000
                ]),
        ];
    }
}