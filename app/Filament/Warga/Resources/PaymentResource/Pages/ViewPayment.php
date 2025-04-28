<?php

namespace App\Filament\Warga\Resources\PaymentResource\Pages;

use App\Filament\Warga\Resources\PaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPayment extends ViewRecord
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn ($record) => $record->status === 'pending'),
        ];
    }
}