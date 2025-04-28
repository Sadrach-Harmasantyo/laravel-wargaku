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
// Remove this import
// use Filament\Tables\Actions\Action;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'My Payments';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Target Bank Information')
                    ->description('Select the bank account for your payment')
                    ->schema([
                        Forms\Components\Select::make('bank_account_id')
                            ->label('Bank Account')
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
                            }),
                        
                        Forms\Components\TextInput::make('account_number_display')
                            ->label('Account Number')
                            ->helperText('Click to copy')
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
                                                $tooltip("Copied to clipboard", { timeout: 1500 });
                                            }).catch(() => {
                                                $tooltip("Failed to copy", { timeout: 1500 });
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
                                                $tooltip("Copied to clipboard", { timeout: 1500 });
                                            } catch (err) {
                                                $tooltip("Failed to copy", { timeout: 1500 });
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
                            ->label('Account Holder')
                            ->readOnly()
                            ->visible(fn (callable $get) => $get('bank_account_id') !== null)
                            ->dehydrated(false),
                    ]),
                
                Forms\Components\Section::make('Payment Information')
                    ->description('Enter your payment details')
                    ->schema([
                        Forms\Components\DatePicker::make('payment_date')
                            ->required()
                            ->default(now()),
                        Forms\Components\DatePicker::make('payment_for_month')
                            ->required()
                            ->displayFormat('F Y')
                            ->default(now()),
                        Forms\Components\TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),
                        Forms\Components\FileUpload::make('proof_of_payment')
                            ->image()
                            ->directory('payment-proofs')
                            ->required(),
                    ]),
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
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_for_month')
                    ->date('F Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\ImageColumn::make('proof_of_payment')
                    ->disk('public')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'verified' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
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
                        'pending' => 'Pending',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
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
