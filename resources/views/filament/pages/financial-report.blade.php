<x-filament-panels::page>
    <x-filament::section>
        {{ $this->form }}
        
        <x-filament::button wire:click="generateReport" style="margin-top: 1rem;">
            Buat Laporan
        </x-filament::button>
    </x-filament::section>

    <x-filament::section>
        <div style="padding: 1rem; background-color: white; border-radius: 0.5rem; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);">
            <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                <h2 style="color: #000000; font-size: 1.25rem; font-weight: 700;">Ringkasan Keuangan {{ Carbon\Carbon::createFromDate($selectedYear, $selectedMonth, 1)->translatedFormat('F Y') }}</h2>
                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; justify-content: center;">
                    <span style="color: #059669; font-weight: 600;">Pendapatan: Rp {{ number_format($totalIncome, 0, ',', '.') }}</span>
                    <span style="color: #DC2626; font-weight: 600;">Pengeluaran: Rp {{ number_format($totalExpenses, 0, ',', '.') }}</span>
                    <span style="font-weight: 700; color: {{ $balance >= 0 ? '#059669' : '#DC2626' }};">
                        Saldo: Rp {{ number_format($balance, 0, ',', '.') }}
                    </span>
                </div>
            </div>
            
            <div style="overflow-x: auto;">
                <table style="width: 100%; font-size: 0.875rem; text-align: left; color: #374151;">
                    <thead style="font-size: 0.75rem; text-transform: uppercase; background-color: #F9FAFB;">
                        <tr>
                            <th style="padding: 0.75rem 1.5rem; font-weight: 600;">Tanggal</th>
                            <th style="padding: 0.75rem 1.5rem; font-weight: 600;">Keterangan</th>
                            <th style="padding: 0.75rem 1.5rem; font-weight: 600;">Jenis</th>
                            <th style="padding: 0.75rem 1.5rem; font-weight: 600; text-align: right;">Jumlah</th>
                            <th style="padding: 0.75rem 1.5rem; font-weight: 600; text-align: right;">Perubahan Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($transactions->count() > 0)
                            @foreach($transactions as $transaction)
                                <tr style="background-color: white; border-bottom: 1px solid #E5E7EB;">
                                    <td style="padding: 1rem 1.5rem;">{{ \Carbon\Carbon::parse($transaction['date'])->translatedFormat('d F Y') }}</td>
                                    <td style="padding: 1rem 1.5rem;">{{ $transaction['description'] }}</td>
                                    <td style="padding: 1rem 1.5rem;">
                                        <span style="padding: 0.25rem 0.5rem; font-size: 0.75rem; font-weight: 500; border-radius: 9999px; background-color: {{ $transaction['type'] === 'Income' ? '#DCFCE7' : '#FEE2E2' }}; color: {{ $transaction['type'] === 'Income' ? '#166534' : '#991B1B' }};">
                                            {{ $transaction['type'] === 'Income' ? 'Pendapatan' : 'Pengeluaran' }}
                                        </span>
                                    </td>
                                    <td style="padding: 1rem 1.5rem; text-align: right;">Rp {{ number_format($transaction['amount'], 0, ',', '.') }}</td>
                                    <td style="padding: 1rem 1.5rem; text-align: right; color: {{ $transaction['balance_impact'] >= 0 ? '#059669' : '#DC2626' }};">
                                        Rp {{ number_format($transaction['balance_impact'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr style="background-color: white; border-bottom: 1px solid #E5E7EB;">
                                <td colspan="5" style="padding: 1rem 1.5rem; text-align: center;">Tidak ada transaksi untuk periode ini.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </x-filament::section>
</x-filament-panels::page>