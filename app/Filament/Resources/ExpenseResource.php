<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Models\Expense;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Keuangan';

    protected static?string $label = 'Pengeluaran';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Nama Pengeluaran')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('category')
                    ->label('Kategori')
                    ->options([
                        'operational' => 'Operasional',
                        'maintenance' => 'Perbaikan',
                        'security' => 'Keamanan',
                        'events' => 'Acara',
                        'other' => 'Lainnya',
                    ])
                    ->required(),
                Forms\Components\DatePicker::make('expense_date')
                    ->label('Tanggal Pengeluaran')
                    ->required()
                    ->default(now()),
                Forms\Components\TextInput::make('amount')
                    ->label('Jumlah')
                    ->required()
                    ->numeric()
                    ->prefix('Rp'),
                Forms\Components\FileUpload::make('receipt_image')
                    ->label('Bukti Pengeluaran')
                    ->image()
                    ->directory('expense-receipts'),
                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Nama Pengeluaran')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'security' => 'danger',
                        'maintenance' => 'warning',
                        'operational' => 'info',
                        'events' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(function (string $state): string {
                        $translations = [
                            'operational' => 'Operasional',
                            'maintenance' => 'Perbaikan',
                            'security' => 'Keamanan',
                            'events' => 'Acara',
                            'other' => 'Lainnya',
                        ];
                
                        return $translations[$state] ?? ucfirst($state);
                    }),
                Tables\Columns\TextColumn::make('expense_date')
                    ->label('Tanggal Pengeluaran')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\ImageColumn::make('receipt_image')
                    ->label('Bukti Pengeluaran')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Kategori')
                    ->options([
                        'operational' => 'Operasional',
                        'maintenance' => 'Perbaikan',
                        'security' => 'Keamanan',
                        'events' => 'Acara',
                        'other' => 'Lainnya',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
