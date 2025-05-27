<?php

namespace App\Filament\Warga\Pages;

use App\Models\Expense;
use App\Models\Payment;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class FinancialReport extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'Laporan Keuangan';
    protected static ?string $title = 'Laporan Keuangan Bulanan';
    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.financial-report-warga';

    public ?array $data = [];
    public $selectedMonth;
    public $selectedYear;
    
    // Flag untuk melacak apakah tabel sudah diinisialisasi
    protected $tableInitialized = false;

    public function mount(): void
    {
        $this->selectedMonth = now()->month;
        $this->selectedYear = now()->year;
        $this->form->fill();
        
        // Inisialisasi data laporan
        $this->generateReport();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Filter Laporan')
                    ->schema([
                        Select::make('selectedMonth')
                            ->label('Bulan')
                            ->options([
                                1 => 'Januari',
                                2 => 'Februari',
                                3 => 'Maret',
                                4 => 'April',
                                5 => 'Mei',
                                6 => 'Juni',
                                7 => 'Juli',
                                8 => 'Agustus',
                                9 => 'September',
                                10 => 'Oktober',
                                11 => 'November',
                                12 => 'Desember',
                            ])
                            ->default(now()->month)
                            ->reactive()
                            ->afterStateUpdated(function () {
                                $this->generateReport();
                            }),
                        
                        Select::make('selectedYear')
                            ->label('Tahun')
                            ->options(function () {
                                $years = [];
                                $currentYear = now()->year;
                                
                                for ($i = $currentYear - 2; $i <= $currentYear + 1; $i++) {
                                    $years[$i] = $i;
                                }
                                
                                return $years;
                            })
                            ->default(now()->year)
                            ->reactive()
                            ->afterStateUpdated(function () {
                                $this->generateReport();
                            }),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Payment::query()
                    ->where('status', 'verified')
                    ->whereMonth('payment_for_month', $this->selectedMonth)
                    ->whereYear('payment_for_month', $this->selectedYear)
            )
            ->columns([
                //
            ])
            ->filters([
                // Tidak ada filter tambahan karena sudah ada filter bulan dan tahun
            ]);
    }

    public function generateReport(): void
    {
        // Mendapatkan data untuk bulan dan tahun yang dipilih
        $startDate = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        
        // Set locale to Indonesian
        Carbon::setLocale('id');
        
        // Menghitung total pendapatan (pembayaran terverifikasi)
        $totalIncome = Payment::where('status', 'verified')
            ->whereMonth('payment_date', $this->selectedMonth)
            ->whereYear('payment_date', $this->selectedYear)
            ->sum('amount');
        
        // Menghitung total pengeluaran
        $totalExpenses = Expense::whereMonth('expense_date', $this->selectedMonth)
            ->whereYear('expense_date', $this->selectedYear)
            ->sum('amount');
        
        // Menghitung saldo
        $balance = $totalIncome - $totalExpenses;

        // Mendapatkan riwayat pendapatan
        $incomeHistory = Payment::where('status', 'verified')
            ->whereMonth('payment_date', $this->selectedMonth)
            ->whereYear('payment_date', $this->selectedYear)
            ->orderBy('payment_date', 'desc')
            ->get()
            ->map(function ($payment) {
                return [
                    'date' => Carbon::parse($payment->payment_date)->translatedFormat('d F Y'),
                    'payment_for' => Carbon::parse($payment->payment_for_month)->translatedFormat('F Y'),
                    'amount' => $payment->amount,
                ];
            });

        // Mendapatkan detail pengeluaran
        $expenseDetails = Expense::whereMonth('expense_date', $this->selectedMonth)
            ->whereYear('expense_date', $this->selectedYear)
            ->orderBy('expense_date', 'desc')
            ->get()
            ->map(function ($expense) {
                return [
                    'date' => Carbon::parse($expense->expense_date)->translatedFormat('d F Y'),
                    'title' => $expense->title,
                    'category' => $expense->category,
                    'amount' => $expense->amount,
                    'description' => $expense->description,
                ];
            });
        
        // Menyimpan data untuk digunakan di view
        $this->data = [
            'month' => Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->translatedFormat('F Y'),
            'totalIncome' => $totalIncome,
            'totalExpenses' => $totalExpenses,
            'balance' => $balance,
            'incomeHistory' => $incomeHistory,
            'expenseDetails' => $expenseDetails,
        ];
        
        // Menandai bahwa tabel sudah diinisialisasi
        $this->tableInitialized = true;
    }
}