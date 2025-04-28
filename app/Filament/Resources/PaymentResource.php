<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\BankAccount;
use App\Models\Payment;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Finance';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Warga')
                    ->options(User::where('role', 'warga')->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('bank_account_id')
                    ->label('Bank Account')
                    ->options(BankAccount::where('is_active', true)->pluck('bank_name', 'id'))
                    ->searchable()
                    ->required(),
                Forms\Components\DatePicker::make('payment_date')
                    ->required(),
                Forms\Components\DatePicker::make('payment_for_month')
                    ->required()
                    ->displayFormat('F Y'),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->prefix('Rp'),
                Forms\Components\FileUpload::make('proof_of_payment')
                    ->image()
                    ->directory('payment-proofs')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                    ])
                    ->required()
                    ->default('pending'),
                Forms\Components\Textarea::make('notes')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Warga')
                    ->searchable(),
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
                    ->toggleable(),
                Tables\Columns\SelectColumn::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
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
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Warga')
                    ->options(User::where('role', 'warga')->pluck('name', 'id'))
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('verify')
                    ->label('Verify')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (Payment $record) {
                        $record->update(['status' => 'verified']);
                    })
                    ->visible(fn (Payment $record) => $record->status === 'pending'),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->action(function (Payment $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'notes' => $data['notes'],
                        ]);
                    })
                    ->form([
                        Forms\Components\Textarea::make('notes')
                            ->label('Reason for rejection')
                            ->required(),
                    ])
                    ->visible(fn (Payment $record) => $record->status === 'pending'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
        ];
    }
}
