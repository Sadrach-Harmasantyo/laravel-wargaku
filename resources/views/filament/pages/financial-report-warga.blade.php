<x-filament-panels::page>
    {{ $this->form }}

    <div style="margin-top: 1.5rem; padding: 1.5rem; background-color: white; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h2 style="font-size: 1.25rem; font-weight: bold; margin-bottom: 1rem; color: black;">
            Laporan Keuangan: {{ $data['month'] }}
        </h2>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
            <div style="padding: 1rem; border-radius: 0.5rem; background-color: #D1FAE5;">
                <h3 style="font-size: 1.125rem; font-weight: 600; color: #4B5563;">Total Pendapatan</h3>
                <p style="font-size: 1.5rem; font-weight: bold; color: #16A34A;">Rp {{ number_format($data['totalIncome'], 0, ',', '.') }}</p>
            </div>

            <div style="padding: 1rem; border-radius: 0.5rem; background-color: #FEE2E2;">
                <h3 style="font-size: 1.125rem; font-weight: 600; color: #4B5563;">Total Pengeluaran</h3>
                <p style="font-size: 1.5rem; font-weight: bold; color: #DC2626;">Rp {{ number_format($data['totalExpenses'], 0, ',', '.') }}</p>
            </div>

            <div style="padding: 1rem; border-radius: 0.5rem; background-color: {{ $data['balance'] >= 0 ? '#D1FAE5' : '#FEE2E2' }};">
                <h3 style="font-size: 1.125rem; font-weight: 600; color: #4B5563;">Saldo</h3>
                <p style="font-size: 1.5rem; font-weight: bold; color: {{ $data['balance'] >= 0 ? '#047857' : '#DC2626' }};">
                    Rp {{ number_format($data['balance'], 0, ',', '.') }}
                </p>
            </div>
        </div>

        @if(count($data['incomeHistory']) > 0)
        <div style="margin-bottom: 1.5rem;">
            <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem; color: black;">Riwayat Pendapatan</h3>
            <div style="overflow-x: auto;">
                <table style="width: 100%; background-color: white; border-collapse: collapse;">
                    <thead style="background-color: #F3F4F6; color: black;">
                        <tr>
                            <th style="padding: 0.5rem 1rem; text-align: left;">Tanggal</th>
                            <th style="padding: 0.5rem 1rem; text-align: left;">Keterangan</th>
                            <th style="padding: 0.5rem 1rem; text-align: right;">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody style="color: black;">
                        @foreach($data['incomeHistory'] as $income)
                        <tr style="border-top: 1px solid #E5E7EB;">
                            <td style="padding: 0.5rem 1rem;">{{ $income['date'] }}</td>
                            <td style="padding: 0.5rem 1rem;">Seorang warga membayar untuk bulan {{ $income['payment_for'] }}</td>
                            <td style="padding: 0.5rem 1rem; text-align: right;">Rp {{ number_format($income['amount'], 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @if(count($data['expenseDetails']) > 0)
        <div style="margin-top: 1.5rem;">
            <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem; color: black;">Detail Pengeluaran</h3>
            <div style="overflow-x: auto;">
                <table style="width: 100%; background-color: white; border-collapse: collapse;">
                    <thead style="background-color: #F3F4F6; color: black;">
                        <tr>
                            <th style="padding: 0.5rem 1rem; text-align: left;">Tanggal</th>
                            <th style="padding: 0.5rem 1rem; text-align: left;">Judul</th>
                            <th style="padding: 0.5rem 1rem; text-align: left;">Kategori</th>
                            <th style="padding: 0.5rem 1rem; text-align: right;">Jumlah</th>
                            <th style="padding: 0.5rem 1rem; text-align: left;">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody style="color: black;">
                        @foreach($data['expenseDetails'] as $expense)
                        <tr style="border-top: 1px solid #E5E7EB;">
                            <td style="padding: 0.5rem 1rem;">{{ $expense['date'] }}</td>
                            <td style="padding: 0.5rem 1rem;">{{ $expense['title'] }}</td>
                            @php
                                $translations = [
                                    'operational' => 'Operasional',
                                    'maintenance' => 'Perbaikan',
                                    'security' => 'Keamanan',
                                    'events' => 'Acara',
                                    'other' => 'Lainnya',
                                    // tambahkan kategori lain sesuai kebutuhan
                                ];

                                $categoryKey = strtolower($expense['category']);
                                $translatedCategory = $translations[$categoryKey] ?? ucfirst($expense['category']);
                            @endphp
                            <td style="padding: 0.5rem 1rem;">{{ $translatedCategory }}</td>
                            <td style="padding: 0.5rem 1rem; text-align: right;">Rp {{ number_format($expense['amount'], 0, ',', '.') }}</td>
                            <td style="padding: 0.5rem 1rem;">{{ $expense['description'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    {{-- <div style="margin-top: 1.5rem;">
        <h2 style="font-size: 1.25rem; font-weight: bold; margin-bottom: 1rem;">Daftar Pembayaran Terverifikasi</h2>
        {{ $this->table }}
    </div> --}}
</x-filament-panels::page>
