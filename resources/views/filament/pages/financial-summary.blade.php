<div style="padding: 1rem;">
    <h2 style="color: #DC2626; font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem;">Laporan Keuangan {{ $month }}</h2>
    
    <div style="display: flex; flex-direction: column; gap: 1rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background-color: #ECFDF5; border-radius: 0.25rem;">
            <span style="font-weight: 500;">Total Pendapatan:</span>
            <span style="color: #059669; font-weight: 700;">Rp {{ number_format($totalIncome, 0, ',', '.') }}</span>
        </div>
        
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background-color: #FEF2F2; border-radius: 0.25rem;">
            <span style="font-weight: 500;">Total Pengeluaran:</span>
            <span style="color: #DC2626; font-weight: 700;">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</span>
        </div>
        
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background-color: {{ $balance >= 0 ? '#D1FAE5' : '#FEE2E2' }}; border-radius: 0.25rem;">
            <span style="font-weight: 500;">Saldo:</span>
            <span style="color: {{ $balance >= 0 ? '#047857' : '#B91C1C' }}; font-weight: 700;">
                Rp {{ number_format($balance, 0, ',', '.') }}
            </span>
        </div>
    </div>
</div>