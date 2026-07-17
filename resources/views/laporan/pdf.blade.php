<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { margin: 26px 30px; }
    body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #1e293b; }

    /* ---------- Header ---------- */
    .header { border-bottom: 2px solid #7c3aed; padding-bottom: 10px; margin-bottom: 14px; }
    .header table { width: 100%; }
    .header h1 { font-size: 17px; margin: 0; color: #1e293b; }
    .header .sub { color: #64748b; font-size: 10px; margin-top: 2px; }
    .header .meta { text-align: right; font-size: 9px; color: #94a3b8; vertical-align: bottom; }

    /* ---------- Section title ---------- */
    .section-title {
        font-size: 12px; font-weight: bold; margin: 18px 0 8px; padding: 3px 0 3px 8px;
        border-left: 4px solid #7c3aed; color: #1e293b;
    }
    .section-title .count { font-weight: normal; color: #94a3b8; font-size: 10px; }

    /* ---------- Tables ---------- */
    table.data { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
    table.data th, table.data td { border: 1px solid #e2e8f0; padding: 6px 8px; text-align: left; font-size: 10px; }
    table.data th { background: #f1f5f9; font-weight: bold; color: #475569; text-transform: uppercase; font-size: 9px; }
    table.data tbody tr.alt { background: #f8fafc; }
    table.data tfoot td { background: #f1f5f9; font-weight: bold; border-top: 2px solid #cbd5e1; }

    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .muted { color: #94a3b8; font-style: italic; }

    /* ---------- Summary cards ---------- */
    .cards { width: 100%; margin-bottom: 14px; border-collapse: separate; }
    .cards td { padding: 0 6px 0 0; }
    .cards td:last-child { padding-right: 0; }
    .card-box { border: 1px solid #e2e8f0; border-radius: 6px; padding: 9px 10px; }
    .card-label { font-size: 8.5px; color: #94a3b8; text-transform: uppercase; font-weight: bold; margin-bottom: 3px; }
    .card-value { font-size: 14px; font-weight: bold; }
    .c-emerald { color: #059669; }
    .c-rose    { color: #e11d48; }
    .c-violet  { color: #7c3aed; }
    .c-amber   { color: #d97706; }

    .footer-note {
        margin-top: 16px; font-size: 8.5px; color: #94a3b8; text-align: center;
        border-top: 1px solid #e2e8f0; padding-top: 6px;
    }
</style>
</head>
<body>

    {{-- ============ Header ============ --}}
    <div class="header">
        <table>
            <tr>
                <td>
                    <h1>Laporan Mutasi & Stok — Toko Rajawali</h1>
                    <div class="sub">
                        Periode: {{ $start->format('d M Y') }} – {{ $end->format('d M Y') }}
                        ({{ ucfirst($periodType) }})
                    </div>
                </td>
                <td class="meta">
                    Dicetak: {{ \Carbon\Carbon::now('Asia/Makassar')->format('d M Y H:i') }} WITA
                </td>
            </tr>
        </table>
    </div>

    {{-- ============ Summary Cards (tanpa Total Nilai Stok) ============ --}}
    <table class="cards">
        <tr>
            <td width="33%">
                <div class="card-box">
                    <div class="card-label">Total Barang Masuk</div>
                    <div class="card-value c-emerald">+{{ number_format($totalInboundQty) }} unit</div>
                </div>
            </td>
            <td width="33%">
                <div class="card-box">
                    <div class="card-label">Total Barang Keluar</div>
                    <div class="card-value c-rose">-{{ number_format($totalOutboundQty) }} unit</div>
                </div>
            </td>
            <td width="34%">
                <div class="card-box">
                    <div class="card-label">Nilai Penjualan</div>
                    <div class="card-value c-violet">Rp{{ number_format($totalOutboundValue, 0, ',', '.') }}</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- ============ Detail Penjualan POS ============ --}}
    @if($salesCount > 0)
    <div class="section-title">
        Detail Penjualan POS
        <span class="count">— {{ $periodLabel }} ({{ $salesCount }} transaksi)</span>
    </div>
    <table class="data">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Waktu (WITA)</th>
                <th>No. Transaksi</th>
                <th>Item</th>
                <th>Kasir</th>
                <th class="text-right">Total (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($salesTransactions as $i => $sale)
            <tr class="{{ $i % 2 ? 'alt' : '' }}">
                <td>{{ $sale->wita_date }}</td>
                <td>{{ $sale->wita_time }}</td>
                <td>#{{ $sale->daily_no }}</td>
                <td>{{ $sale->outbounds->map(fn($ob) => ($ob->item->name ?? '-') . ' (' . $ob->quantity . ')')->implode(', ') }}</td>
                <td>{{ $sale->user->name ?? '-' }}</td>
                <td class="text-right">{{ number_format($sale->total_amount, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5">GRAND TOTAL PENJUALAN</td>
                <td class="text-right">{{ number_format($salesTotal, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
    @endif

    {{-- ============ Riwayat Barang Masuk ============ --}}
    @if($inbounds->count())
    <div class="section-title">
        Riwayat Barang Masuk
        <span class="count">({{ $inbounds->count() }} transaksi)</span>
    </div>
    <table class="data">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Nama Barang</th>
                <th>Kategori</th>
                <th>Supplier</th>
                <th class="text-center">Qty</th>
                <th>Operator</th>
            </tr>
        </thead>
        <tbody>
            @foreach($inbounds as $i => $ib)
            <tr class="{{ $i % 2 ? 'alt' : '' }}">
                <td>{{ \Carbon\Carbon::parse($ib->date)->format('d/m/Y') }}</td>
                <td>{{ $ib->item->name ?? '-' }}</td>
                <td>{{ $ib->item->category_label ?? ($ib->item->category ?? '-') }}</td>
                <td>{{ $ib->supplier }}</td>
                <td class="text-center">+{{ $ib->quantity }}</td>
                <td>{{ $ib->user->name ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4">TOTAL</td>
                <td class="text-center">+{{ number_format($totalInboundQty) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
    @endif

    {{-- ============ Riwayat Barang Keluar Manual ============ --}}
    @if($manualOutbounds->count())
    <div class="section-title">
        Riwayat Barang Keluar Manual
        <span class="count">({{ $manualOutbounds->count() }} transaksi)</span>
    </div>
    <table class="data">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Nama Barang</th>
                <th>Kategori</th>
                <th>Penerima</th>
                <th class="text-center">Qty</th>
                <th class="text-right">Nilai (Rp)</th>
                <th>Operator</th>
            </tr>
        </thead>
        <tbody>
            @foreach($manualOutbounds as $i => $ob)
            <tr class="{{ $i % 2 ? 'alt' : '' }}">
                <td>{{ \Carbon\Carbon::parse($ob->date)->format('d/m/Y') }}</td>
                <td>{{ $ob->item->name ?? '-' }}</td>
                <td>{{ $ob->item->category_label ?? ($ob->item->category ?? '-') }}</td>
                <td>{{ $ob->customer ?? '-' }}</td>
                <td class="text-center">-{{ $ob->quantity }}</td>
                <td class="text-right">{{ number_format(($ob->item->price ?? 0) * $ob->quantity, 0, ',', '.') }}</td>
                <td>{{ $ob->user->name ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4">TOTAL</td>
                <td class="text-center">-{{ number_format($manualOutbounds->sum('quantity')) }}</td>
                <td class="text-right">{{ number_format($manualOutbounds->sum(fn($ob) => ($ob->item->price ?? 0) * $ob->quantity), 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
    @endif

    <div class="footer-note">
        Laporan ini dibuat otomatis oleh sistem POS & Inventory Toko Rajawali pada {{ \Carbon\Carbon::now('Asia/Makassar')->format('d M Y H:i') }} WITA.
    </div>

</body>
</html>