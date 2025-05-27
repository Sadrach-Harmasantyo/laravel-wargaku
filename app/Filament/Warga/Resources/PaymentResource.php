<?php

namespace App\Filament\Warga\Resources;

use App\Filament\Warga\Resources\PaymentResource\Pages;
use App\Models\BankAccount;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
// Remove this import
// use Filament\Tables\Actions\Action;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static?string $label = 'Pembayaran';

    protected static ?string $navigationLabel = 'Pembayaran';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Bank Pembayaran')
                    ->description('Pilih bank untuk pembayaran')
                    ->schema([
                        Forms\Components\Select::make('bank_account_id')
                            ->label('Akun Bank')
                            ->options(BankAccount::where('is_active', true)->pluck('bank_name', 'id'))
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $bankAccount = BankAccount::find($state);
                                    if ($bankAccount) {
                                        $set('account_number_display', $bankAccount->account_number);
                                        $set('account_holder_display', $bankAccount->account_holder);
                                    }
                                }
                            })
                            ->afterStateHydrated(function ($state, Forms\Set $set) {
                                // Populate account details when viewing existing record
                                if ($state) {
                                    $bankAccount = BankAccount::find($state);
                                    if ($bankAccount) {
                                        $set('account_number_display', $bankAccount->account_number);
                                        $set('account_holder_display', $bankAccount->account_holder);
                                    }
                                }
                            }),
                        
                        Forms\Components\TextInput::make('account_number_display')
                            ->label('No. Rekening')
                            ->helperText('Klik untuk salin')
                            ->readOnly()
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('copy')
                                    ->icon('heroicon-s-clipboard')
                                    ->action(function ($livewire, $state) {
                                        $livewire->dispatch('copy-to-clipboard', text: $state);
                                    })
                            )
                            ->extraAttributes([
                                'x-data' => '{
                                    copyToClipboard(text) {
                                        if (!text) {
                                            text = this.$el.querySelector("input").value;
                                        }
                                        
                                        if (navigator.clipboard && navigator.clipboard.writeText) {
                                            navigator.clipboard.writeText(text).then(() => {
                                                $tooltip("Berhasil disalin", { timeout: 1500 });
                                            }).catch(() => {
                                                $tooltip("Gagal disalin", { timeout: 1500 });
                                            });
                                        } else {
                                            const textArea = document.createElement("textarea");
                                            textArea.value = text;
                                            textArea.style.position = "fixed";
                                            textArea.style.opacity = "0";
                                            document.body.appendChild(textArea);
                                            textArea.select();
                                            try {
                                                document.execCommand("copy");
                                                $tooltip("Berhasil disalin", { timeout: 1500 });
                                            } catch (err) {
                                                $tooltip("Gagal disalin", { timeout: 1500 });
                                            }
                                            document.body.removeChild(textArea);
                                        }
                                    }
                                }',
                                'x-on:copy-to-clipboard.window' => 'copyToClipboard($event.detail.text)',
                                'x-on:click' => 'copyToClipboard()',
                                'class' => 'cursor-pointer',
                            ])
                            ->visible(fn (callable $get) => $get('bank_account_id') !== null)
                            ->dehydrated(false),
                        
                        Forms\Components\TextInput::make('account_holder_display')
                            ->label('Atas Nama')
                            ->readOnly()
                            ->visible(fn (callable $get) => $get('bank_account_id') !== null)
                            ->dehydrated(false),
                    ]),
                
                Forms\Components\Section::make('Informasi Pembayaran')
                    ->description('Isi informasi pembayaran')
                    ->schema([
                        Forms\Components\DatePicker::make('payment_date')
                            ->label('Tanggal Pembayaran')
                            ->required()
                            ->default(now()),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('temp_month')
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
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, $get, Forms\Set $set) {
                                        $year = $get('temp_year');
                                        if ($year) {
                                            $date = Carbon::createFromDate($year, $state, 1)->format('Y-m-d');
                                            $set('payment_for_month', $date);
                                        }
                                    })
                                    ->afterStateHydrated(function ($state, Forms\Set $set, ?Payment $record) {
                                        if ($record && $record->payment_for_month) {
                                            $set('temp_month', Carbon::parse($record->payment_for_month)->month);
                                        }
                                    }),
                                Forms\Components\Select::make('temp_year')
                                    ->label('Tahun')
                                    ->options(function() {
                                        $currentYear = now()->year;
                                        return [
                                            $currentYear => $currentYear,
                                            $currentYear + 1 => $currentYear + 1,
                                        ];
                                    })
                                    ->default(now()->year)
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, $get, Forms\Set $set) {
                                        $month = $get('temp_month');
                                        if ($month) {
                                            $date = Carbon::createFromDate($state, $month, 1)->format('Y-m-d');
                                            $set('payment_for_month', $date);
                                        }
                                    })
                                    ->afterStateHydrated(function ($state, Forms\Set $set, ?Payment $record) {
                                        if ($record && $record->payment_for_month) {
                                            $set('temp_year', Carbon::parse($record->payment_for_month)->year);
                                        }
                                    }),
                            ]),
                        Forms\Components\Hidden::make('payment_for_month')
                            ->default(now()->startOfMonth()->format('Y-m-d'))
                            ->required(),
                        Forms\Components\TextInput::make('amount')
                            ->label('Jumlah Pembayaran')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),
                        Forms\Components\FileUpload::make('proof_of_payment')
                            ->label('Bukti Pembayaran')
                            ->image()
                            ->directory('payment-proofs')
                            ->required(),
                    ]),
                
                Forms\Components\Section::make('Status Informasi Pembayaran')
                    ->schema([
                        Forms\Components\TextInput::make('status')
                            ->readOnly(),
                        Forms\Components\Textarea::make('notes')
                            ->readOnly()
                            ->visible(fn (callable $get) => $get('status') === 'rejected')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (?Payment $record) => $record !== null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('bankAccount.bank_name')
                    ->label('Bank')
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Tanggal Pembayaran')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_for_month')
                    ->label('Target Bulan Pembayaran')
                    ->date('F Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah Pembayaran')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\ImageColumn::make('proof_of_payment')
                    ->label('Bukti Pembayaran')
                    ->disk('public')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'verified' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                    })
                    ->formatStateUsing(function (string $state): string {
                        $translations = [
                            'verified' => 'Terverifikasi',
                            'pending' => 'Tertunda',
                            'rejected' => 'Ditolak',
                        ];
                
                        return $translations[$state] ?? ucfirst($state);
                    }),
                Tables\Columns\TextColumn::make('notes')
                    ->toggleable()
                    ->visible(fn (?Payment $record) => $record?->status === 'rejected'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Tertunda',
                        'verified' => 'Terverifikasi',
                        'rejected' => 'Ditolak',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn (Payment $record) => $record->status === 'pending'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => false),
                ]),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                return $query->where('user_id', Auth::id());
            });
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
            'view' => Pages\ViewPayment::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', Auth::id());
    }
}
