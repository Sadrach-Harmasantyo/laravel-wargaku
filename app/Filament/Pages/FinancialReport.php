<?php

// namespace App\Filament\Pages;

// use App\Models\Expense;
// use App\Models\Payment;
// use Carbon\Carbon;
// use Filament\Forms\Components\DatePicker;
// use Filament\Forms\Components\Section;
// use Filament\Forms\Components\Select;
// use Filament\Forms\Concerns\InteractsWithForms;
// use Filament\Forms\Contracts\HasForms;
// use Filament\Forms\Form;
// use Filament\Pages\Page;
// use Filament\Tables\Columns\TextColumn;
// use Filament\Tables\Concerns\InteractsWithTable;
// use Filament\Tables\Contracts\HasTable;
// use Filament\Tables\Table;
// use Illuminate\Support\Facades\DB;

// class FinancialReport extends Page implements HasForms, HasTable
// {
//     use InteractsWithForms;
//     use InteractsWithTable;

//     protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
//     protected static ?string $navigationLabel = 'Financial Reports';
//     protected static ?string $navigationGroup = 'Finance';
//     protected static ?string $title = 'Monthly Financial Report';
//     protected static ?int $navigationSort = 3;

//     protected static string $view = 'filament.pages.financial-report';

//     public ?array $data = [];
//     public $selectedMonth;
//     public $selectedYear;
    
//     // Add a flag to track if table is ready
//     protected $tableInitialized = false;

//     public function mount(): void
//     {
//         $this->selectedMonth = now()->month;
//         $this->selectedYear = now()->year;
//         $this->form->fill();
        
//         // Don't call generateReport() here, we'll let the page load first
//         // The table will be initialized when the page renders
//     }

//     public function form(Form $form): Form
//     {
//         return $form
//             ->schema([
//                 Section::make('Report Period')
//                     ->schema([
//                         Select::make('selectedMonth')
//                             ->label('Month')
//                             ->options([
//                                 1 => 'January',
//                                 2 => 'February',
//                                 3 => 'March',
//                                 4 => 'April',
//                                 5 => 'May',
//                                 6 => 'June',
//                                 7 => 'July',
//                                 8 => 'August',
//                                 9 => 'September',
//                                 10 => 'October',
//                                 11 => 'November',
//                                 12 => 'December',
//                             ])
//                             ->default(now()->month)
//                             ->reactive(),
//                         Select::make('selectedYear')
//                             ->label('Year')
//                             ->options(function () {
//                                 $years = [];
//                                 $currentYear = now()->year;
//                                 for ($i = $currentYear - 2; $i <= $currentYear; $i++) {
//                                     $years[$i] = $i;
//                                 }
//                                 return $years;
//                             })
//                             ->default(now()->year)
//                             ->reactive(),
//                     ])
//                     ->columns(2),
//             ])
//             ->statePath('data');
//     }

//     public function generateReport(): void
//     {
//         $this->data = $this->form->getState();
        
//         // Instead of trying to refresh the table query directly,
//         // we'll just refresh the entire page to rebuild the table
//         $this->dispatch('refresh');
//     }

//     public function table(Table $table): Table
//     {
//         $startDate = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->startOfMonth();
//         $endDate = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->endOfMonth();

//         // Get income (payments)
//         $incomeQuery = Payment::where('status', 'verified')
//             ->whereBetween('payment_date', [$startDate, $endDate]);
        
//         // Get expenses
//         $expenseQuery = Expense::whereBetween('expense_date', [$startDate, $endDate]);
        
//         // Calculate totals
//         $totalIncome = $incomeQuery->sum('amount');
//         $totalExpenses = $expenseQuery->sum('amount');
//         $balance = $totalIncome - $totalExpenses;

//         // Combine income and expenses for the table
//         $transactions = collect();
        
//         // Add income transactions
//         $incomeQuery->get()->each(function ($payment) use ($transactions) {
//             $userName = $payment->user?->name ?? 'Unknown User';
//             $paymentForMonth = $payment->payment_for_month ? $payment->payment_for_month->format('F Y') : 'Unknown Month';
//             $transactions->push([
//                 'date' => $payment->payment_date,
//                 'description' => 'Payment from ' . $userName . ' for ' . $paymentForMonth,
//                 'type' => 'Income',
//                 'amount' => $payment->amount,
//                 'balance_impact' => $payment->amount,
//             ]);
//         });
        
//         // Add expense transactions
//         $expenseQuery->get()->each(function ($expense) use ($transactions) {
//             $transactions->push([
//                 'date' => $expense->expense_date,
//                 'description' => $expense->title . ' (' . $expense->category . ')',
//                 'type' => 'Expense',
//                 'amount' => $expense->amount,
//                 'balance_impact' => -$expense->amount,
//             ]);
//         });
        
//         // Sort by date
//         $sortedTransactions = $transactions->sortBy('date')->values();
        
//         // Mark table as initialized
//         $this->tableInitialized = true;

//         // Create a dummy Payment model to use for the query
//         // This ensures we have a valid Eloquent model even when there's no data
//         $dummyPayment = new Payment();
        
//         return $table
//             ->query(
//                 Payment::query()->whereRaw('1=0') // Empty result set using Eloquent Builder
//             )
//             ->modifyQueryUsing(function ($query) use ($sortedTransactions) {
//                 // We'll handle the data display in the view
//                 return $query;
//             })
//             ->emptyStateHeading('No transactions found')
//             ->emptyStateDescription('No financial transactions were found for the selected period.')
//             ->emptyStateIcon('heroicon-o-document-text')
//             ->columns([
//                 TextColumn::make('date')
//                     ->date()
//                     ->sortable(),
//                 TextColumn::make('description')
//                     ->searchable()
//                     ->wrap(),
//                 TextColumn::make('type')
//                     ->badge()
//                     ->color(fn (string $state): string => match ($state) {
//                         'Income' => 'success',
//                         'Expense' => 'danger',
//                         default => 'gray',
//                     }),
//                 TextColumn::make('amount')
//                     ->money('IDR')
//                     ->alignRight(),
//                 TextColumn::make('balance_impact')
//                     ->money('IDR')
//                     ->alignRight(),
//             ])
//             ->paginated(false)
//             ->headerActions([
//                 \Filament\Tables\Actions\Action::make('generate')
//                     ->label('Generate Report')
//                     ->action(function () {
//                         $this->generateReport();
//                     }),
//             ])
//             ->actions([
//                 \Filament\Tables\Actions\Action::make('summary')
//                     ->label('Summary')
//                     ->action(function () {})
//                     ->modalContent(function () use ($totalIncome, $totalExpenses, $balance) {
//                         return view('filament.pages.financial-summary', [
//                             'totalIncome' => $totalIncome,
//                             'totalExpenses' => $totalExpenses,
//                             'balance' => $balance,
//                             'month' => Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->format('F Y'),
//                         ]);
//                     })
//                     ->modalSubmitAction(false)
//                     ->modalCancelAction(false),
//             ]);
//     }

//     public function getFormStatePath(): string
//     {
//         return 'data';
//     }
// }

// <?php

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
    protected static ?string $navigationLabel = 'Financial Reports';
    protected static ?string $navigationGroup = 'Finance';
    protected static ?string $title = 'Monthly Financial Report';
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
                Section::make('Report Period')
                    ->schema([
                        Select::make('selectedMonth')
                            ->label('Month')
                            ->options([
                                1 => 'January',
                                2 => 'February',
                                3 => 'March',
                                4 => 'April',
                                5 => 'May',
                                6 => 'June',
                                7 => 'July',
                                8 => 'August',
                                9 => 'September',
                                10 => 'October',
                                11 => 'November',
                                12 => 'December',
                            ])
                            ->default(now()->month)
                            ->reactive(),
                        Select::make('selectedYear')
                            ->label('Year')
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
            $userName = $payment->user?->name ?? 'Unknown User';
            $paymentForMonth = $payment->payment_for_month ? $payment->payment_for_month->format('F Y') : 'Unknown Month';
            $transactions->push([
                'id' => 'payment_' . $payment->id,
                'date' => $payment->payment_date,
                'description' => 'Payment from ' . $userName . ' for ' . $paymentForMonth,
                'type' => 'Income',
                'amount' => $payment->amount,
                'balance_impact' => $payment->amount,
            ]);
        });
        
        // Add expense transactions
        $expenseQuery->get()->each(function ($expense) use ($transactions) {
            $transactions->push([
                'id' => 'expense_' . $expense->id,
                'date' => $expense->expense_date,
                'description' => $expense->title . ' (' . $expense->category . ')',
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
            ->emptyStateHeading('No transactions found')
            ->emptyStateDescription('No financial transactions were found for the selected period.')
            ->emptyStateIcon('heroicon-o-document-text')
            ->columns([
                TextColumn::make('date')
                    ->date()
                    ->sortable(),
                TextColumn::make('description')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Income' => 'success',
                        'Expense' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('amount')
                    ->money('IDR')
                    ->alignRight(),
                TextColumn::make('balance_impact')
                    ->money('IDR')
                    ->alignRight(),
            ])
            ->paginated(false)
            ->headerActions([
                \Filament\Tables\Actions\Action::make('generate')
                    ->label('Generate Report')
                    ->action(function () {
                        $this->generateReport();
                    }),
            ])
            ->actions([
                \Filament\Tables\Actions\Action::make('summary')
                    ->label('Summary')
                    ->action(function () {})
                    ->modalContent(function () {
                        return view('filament.pages.financial-summary', [
                            'totalIncome' => $this->totalIncome,
                            'totalExpenses' => $this->totalExpenses,
                            'balance' => $this->balance,
                            'month' => Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->format('F Y'),
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