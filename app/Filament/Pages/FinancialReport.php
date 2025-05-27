<?php

namespace App\Filament\Pages;

use App\Models\Expense;
use App\Models\Payment;
use Carbon\Carbon;
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
use Illuminate\Support\Collection;

class FinancialReport extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    
    protected static ?string $label = 'Laporan Keuangan';

    protected static?string $navigationLabel = 'Laporan Keuangan';

    protected static ?string $title = 'Laporan Keuangan';
   
    protected static ?string $navigationGroup = 'Keuangan';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.financial-report';

    public ?array $data = [];
    public $selectedMonth;
    public $selectedYear;
    public $totalIncome = 0;
    public $totalExpenses = 0;
    public $balance = 0;
    public Collection $transactions;

    public function mount(): void
    {
        $this->selectedMonth = now()->month;
        $this->selectedYear = now()->year;
        $this->form->fill();
        $this->transactions = collect();
        
        // Generate the initial report
        $this->generateReport();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Periode Laporan Keuangan')
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
                            ->reactive(),
                        Select::make('selectedYear')
                            ->label('Tahun')
                            ->options(function () {
                                $years = [];
                                $currentYear = now()->year;
                                for ($i = $currentYear - 2; $i <= $currentYear; $i++) {
                                    $years[$i] = $i;
                                }
                                return $years;
                            })
                            ->default(now()->year)
                            ->reactive(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function generateReport(): void
    {
        $this->data = $this->form->getState();

        // Update selectedMonth dan selectedYear dari data form
        $this->selectedMonth = $this->data['selectedMonth'];
        $this->selectedYear = $this->data['selectedYear'];
        
        $startDate = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->endOfMonth();

        // Get income (payments)
        $incomeQuery = Payment::where('status', 'verified')
            ->whereBetween('payment_date', [$startDate, $endDate]);
        
        // Get expenses
        $expenseQuery = Expense::whereBetween('expense_date', [$startDate, $endDate]);
        
        // Calculate totals
        $this->totalIncome = $incomeQuery->sum('amount');
        $this->totalExpenses = $expenseQuery->sum('amount');
        $this->balance = $this->totalIncome - $this->totalExpenses;

        // Combine income and expenses for the table
        $transactions = collect();
        
        // Add income transactions
        $incomeQuery->get()->each(function ($payment) use ($transactions) {
            $userName = $payment->user?->name ?? 'Warga';
            $paymentForMonth = $payment->payment_for_month ? $payment->payment_for_month->translatedFormat('F Y') : 'Bulan tidak diketahui';
            $transactions->push([
                'id' => 'payment_' . $payment->id,
                'date' => $payment->payment_date,
                'description' => 'Pembayaran dari ' . $userName . ' untuk ' . $paymentForMonth,
                'type' => 'Income',
                'amount' => $payment->amount,
                'balance_impact' => $payment->amount,
            ]);
        });
        
        // Add expense transactions
        $expenseQuery->get()->each(function ($expense) use ($transactions) {
            $translations = [
                'operational' => 'Operasional',
                'maintenance' => 'Perbaikan',
                'security' => 'Keamanan',
                'events' => 'Acara',
                'other' => 'Lainnya',
            ];
            
            $categoryKey = strtolower($expense->category);
            $translatedCategory = $translations[$categoryKey] ?? ucfirst($expense->category);
            
            $transactions->push([
                'id' => 'expense_' . $expense->id,
                'date' => $expense->expense_date,
                'description' => $expense->title . ' (' . $translatedCategory . ')',
                'type' => 'Expense',
                'amount' => $expense->amount,
                'balance_impact' => -$expense->amount,
            ]);
        });
        
        // Sort by date
        $this->transactions = $transactions->sortBy('date')->values();
        
        // Refresh the table after updating data
        $this->render();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Use an empty query from the Payment model as a base
                Payment::query()->whereRaw('1=0')
            )
            ->emptyStateHeading('Tidak ada transaksi')
            ->emptyStateDescription('Tidak ada transaksi keuangan untuk periode yang dipilih.')
            ->emptyStateIcon('heroicon-o-document-text')
            ->columns([
                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Keterangan')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Income' => 'success',
                        'Expense' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'Income' => 'Pendapatan',
                        'Expense' => 'Pengeluaran',
                        default => $state,
                    }),
                TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->alignRight(),
                TextColumn::make('balance_impact')
                    ->label('Perubahan Saldo')
                    ->money('IDR')
                    ->alignRight(),
            ])
            ->paginated(false)
            ->headerActions([
                \Filament\Tables\Actions\Action::make('generate')
                    ->label('Buat Laporan')
                    ->action(function () {
                        $this->generateReport();
                    }),
            ])
            ->actions([
                \Filament\Tables\Actions\Action::make('summary')
                    ->label('Ringkasan')
                    ->action(function () {})
                    ->modalContent(function () {
                        return view('filament.pages.financial-summary', [
                            'totalIncome' => $this->totalIncome,
                            'totalExpenses' => $this->totalExpenses,
                            'balance' => $this->balance,
                            'month' => Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->translatedFormat('F Y'),
                        ]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false),
            ]);
    }

    public function getFormStatePath(): string
    {
        return 'data';
    }
}