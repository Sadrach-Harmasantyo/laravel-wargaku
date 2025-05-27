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

    protected static ?string $navigationGroup = 'Keuangan';

    protected static?string $label = 'Pembayaran';

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
                    ->label('Bank')
                    ->options(BankAccount::where('is_active', true)->pluck('bank_name', 'id'))
                    ->searchable()
                    ->required(),
                Forms\Components\DatePicker::make('payment_date')
                    ->label('Tanggal Pembayaran')
                    ->required(),
                Forms\Components\DatePicker::make('payment_for_month')
                    ->label('Target Bulan Pembayaran')
                    ->required()
                    ->displayFormat('F Y'),
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
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                    ])
                    ->required()
                    ->default('pending'),
                Forms\Components\Textarea::make('notes')
                    ->label('Catatan')
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
                    ->label('Tanggal Pembayaran')
                    ->date('d F Y')
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
                    ->toggleable(),
                Tables\Columns\SelectColumn::make('status')
                    ->options([
                        'pending' => 'Tertunda',
                        'verified' => 'Terverifikasi',
                        'rejected' => 'Ditolak',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Pembuatan')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Tanggal Perubahan')
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
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Warga')
                    ->options(User::where('role', 'warga')->pluck('name', 'id'))
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('verify')
                    ->label('Verifikasi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (Payment $record) {
                        $record->update(['status' => 'verified']);
                    })
                    ->visible(fn (Payment $record) => $record->status === 'pending'),
                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
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
                            ->label('Alasan Penolakan')
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
