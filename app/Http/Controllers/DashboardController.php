<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Inbound;
use App\Models\Outbound;
use App\Models\SaleTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        return match($user->role) {
            'owner' => $this->dashboardOwner($request),
            'dapur' => $this->dashboardDapur($request),
            default => $this->dashboardAdmin($request),
        };
    }

    // ================================================================
    // HELPER: Hitung total pendapatan hari ini
    // HANYA dari SaleTransaction (POS) — outbound manual TIDAK dihitung
    // karena outbound manual = pemakaian bahan baku dapur, bukan penjualan
    // ================================================================
    private function getTodayRevenue(): float
    {
        return (float) SaleTransaction::whereDate('date', Carbon::today())
            ->sum('total_amount');
    }

    // ================================================================
    // DASHBOARD ADMIN — data lengkap semua kategori + pendapatan
    // ================================================================
    private function dashboardAdmin(Request $request)
    {
        $period = $request->input('period', 'today');

        $total_items          = Item::count();
        $total_inbound_today  = Inbound::whereDate('date', Carbon::today())->sum('quantity');
        $total_outbound_today = Outbound::whereDate('date', Carbon::today())->sum('quantity');

        // Revenue hanya dari POS (SaleTransaction) — tanpa outbound manual
        $revenue_today = $this->getTodayRevenue();

        [$chart_labels, $inbound_data, $outbound_data, $revenue_data] = $this->buildChartData($period);

        // Aktivitas Terakhir HANYA hari ini — dashboard reset otomatis tiap hari.
        // Riwayat hari-hari sebelumnya dilihat lewat halaman Barang Masuk/Keluar atau Laporan.
        $inbounds = Inbound::with(['item', 'user'])
            ->whereDate('date', Carbon::today())
            ->orderBy('created_at', 'desc')
            ->limit(5)->get()
            ->map(fn($in) => [
                'time'          => $in->created_at?->diffForHumans() ?? '-',
                'item_name'     => $in->item->name ?? 'Produk Dihapus',
                'type'          => 'Inbound',
                'partner'       => $in->supplier,
                'quantity'      => $in->quantity,
                'operator_name' => $in->user->name ?? 'Sistem',
                'raw_time'      => $in->created_at,
            ]);

        $outbounds = Outbound::with(['item', 'user'])
            ->whereDate('date', Carbon::today())
            ->orderBy('created_at', 'desc')
            ->limit(5)->get()
            ->map(fn($out) => [
                'time'          => $out->created_at?->diffForHumans() ?? '-',
                'item_name'     => $out->item->name ?? 'Produk Dihapus',
                'type'          => 'Outbound',
                'partner'       => $out->customer ?? 'Pelanggan',
                'quantity'      => $out->quantity,
                'operator_name' => $out->user->name ?? 'Sistem',
                'raw_time'      => $out->created_at,
            ]);

        $recent_activities = $inbounds->concat($outbounds)->sortByDesc('raw_time')->take(5);
        $low_stock_items   = Item::where('stock', '<=', 5)->orderBy('stock')->limit(5)->get();

        return view('dashboard.admin', compact(
            'total_items', 'total_inbound_today', 'total_outbound_today',
            'revenue_today', 'recent_activities', 'low_stock_items',
            'period', 'chart_labels', 'inbound_data', 'outbound_data', 'revenue_data',
        ));
    }

    // ================================================================
    // DASHBOARD OWNER — analitik aktivitas toko (tanpa nilai inventori)
    // ================================================================
    private function dashboardOwner(Request $request)
    {
        $period = $request->input('period', 'today');

        $total_items          = Item::count();
        $total_inbound_today  = Inbound::whereDate('date', Carbon::today())->sum('quantity');
        $total_outbound_today = Outbound::whereDate('date', Carbon::today())->sum('quantity');

        // Revenue hanya dari POS (SaleTransaction) — tanpa outbound manual
        $revenue_today = $this->getTodayRevenue();

        $low_stock_items = Item::where('stock', '<=', 5)->orderBy('stock')->limit(5)->get();

        // Aktivitas Terakhir HANYA hari ini — dashboard reset otomatis tiap hari.
        // Riwayat hari-hari sebelumnya dilihat lewat halaman Barang Masuk/Keluar atau Laporan.
        $inbounds = Inbound::with(['item', 'user'])
            ->whereDate('date', Carbon::today())
            ->orderBy('created_at', 'desc')->limit(5)->get()
            ->map(fn($in) => [
                'time'          => $in->created_at?->diffForHumans() ?? '-',
                'item_name'     => $in->item->name ?? 'Produk Dihapus',
                'type'          => 'Inbound',
                'partner'       => $in->supplier,
                'quantity'      => $in->quantity,
                'operator_name' => $in->user->name ?? 'Sistem',
                'raw_time'      => $in->created_at,
            ]);

        $outbounds = Outbound::with(['item', 'user'])
            ->whereDate('date', Carbon::today())
            ->orderBy('created_at', 'desc')->limit(5)->get()
            ->map(fn($out) => [
                'time'          => $out->created_at?->diffForHumans() ?? '-',
                'item_name'     => $out->item->name ?? 'Produk Dihapus',
                'type'          => 'Outbound',
                'partner'       => $out->customer ?? 'Pelanggan',
                'quantity'      => $out->quantity,
                'operator_name' => $out->user->name ?? 'Sistem',
                'raw_time'      => $out->created_at,
            ]);

        $recent_activities = $inbounds->concat($outbounds)->sortByDesc('raw_time')->take(5);

        [$chart_labels, $inbound_data, $outbound_data, $revenue_data] = $this->buildChartData($period);

        return view('dashboard.owner', compact(
            'total_items', 'total_inbound_today', 'total_outbound_today',
            'revenue_today', 'low_stock_items',
            'recent_activities', 'period',
            'chart_labels', 'inbound_data', 'outbound_data', 'revenue_data',
        ));
    }

    // ================================================================
    // DASHBOARD DAPUR — hanya bakery, tanpa pendapatan sama sekali
    // ================================================================
    private function dashboardDapur(Request $request)
    {
        $period     = $request->input('period', 'today');
        $bakery     = ['Bakery_Jadi', 'Bakery_Bahan_Baku'];
        $cakePastry = ['Bakery_Jadi'];
        $bahanBaku  = ['Bakery_Bahan_Baku'];

        $total_items = Item::whereIn('category', $bakery)->count();

        $total_inbound_today = Inbound::whereDate('date', Carbon::today())
            ->whereHas('item', fn($q) => $q->whereIn('category', $bakery))
            ->sum('quantity');

        $total_outbound_today = Outbound::whereDate('date', Carbon::today())
            ->whereHas('item', fn($q) => $q->whereIn('category', $bakery))
            ->sum('quantity');

        $low_stock_items = Item::whereIn('category', $bakery)
            ->where('stock', '<=', 10)
            ->orderBy('stock')
            ->get();

        // Penjualan Cake & Pastry terbaru (acuan produksi) — hanya hari ini,
        // riwayat hari sebelumnya dilihat lewat halaman Barang Keluar.
        $recent_sales = Outbound::with(['item', 'user'])
            ->whereDate('date', Carbon::today())
            ->whereHas('item', fn($q) => $q->whereIn('category', $cakePastry))
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get()
            ->map(fn($out) => [
                'time'          => $out->created_at?->diffForHumans() ?? '-',
                'item_name'     => $out->item->name ?? 'Produk Dihapus',
                'quantity'      => $out->quantity,
                'unit'          => $out->item->unit ?? 'pcs',
                'source'        => $out->source,
                'operator_name' => $out->user->name ?? 'Sistem',
                'raw_time'      => $out->created_at,
            ]);

        // Mutasi bahan baku terbaru — hanya hari ini, riwayat hari sebelumnya
        // dilihat lewat halaman Barang Masuk/Keluar.
        $inbounds_bahan = Inbound::with(['item', 'user'])
            ->whereDate('date', Carbon::today())
            ->whereHas('item', fn($q) => $q->whereIn('category', $bahanBaku))
            ->orderBy('created_at', 'desc')->limit(5)->get()
            ->map(fn($in) => [
                'time'          => $in->created_at?->diffForHumans() ?? '-',
                'item_name'     => $in->item->name ?? 'Produk Dihapus',
                'type'          => 'Inbound',
                'quantity'      => $in->quantity,
                'unit'          => $in->item->unit ?? 'pcs',
                'operator_name' => $in->user->name ?? 'Sistem',
                'raw_time'      => $in->created_at,
            ]);

        $outbounds_bahan = Outbound::with(['item', 'user'])
            ->whereDate('date', Carbon::today())
            ->whereHas('item', fn($q) => $q->whereIn('category', $bahanBaku))
            ->orderBy('created_at', 'desc')->limit(5)->get()
            ->map(fn($out) => [
                'time'          => $out->created_at?->diffForHumans() ?? '-',
                'item_name'     => $out->item->name ?? 'Produk Dihapus',
                'type'          => 'Outbound',
                'quantity'      => $out->quantity,
                'unit'          => $out->item->unit ?? 'pcs',
                'operator_name' => $out->user->name ?? 'Sistem',
                'raw_time'      => $out->created_at,
            ]);

        $recent_bahan = $inbounds_bahan->concat($outbounds_bahan)
            ->sortByDesc('raw_time')->take(5);

        [$chart_labels, $inbound_data, $outbound_data] = $this->buildChartDataDapur($period, $bakery);

        return view('dashboard.dapur', compact(
            'total_items', 'total_inbound_today', 'total_outbound_today',
            'low_stock_items', 'recent_sales', 'recent_bahan',
            'period', 'chart_labels', 'inbound_data', 'outbound_data',
        ));
    }

    // ================================================================
    // HELPER: Chart untuk admin & owner (include revenue)
    // Revenue HANYA dari SaleTransaction — outbound manual tidak dihitung
    // ================================================================
    private function buildChartData(string $period): array
    {
        if ($period === 'today') {
            $chart_labels  = collect(range(0, 23))->map(fn($h) => str_pad($h, 2, '0', STR_PAD_LEFT) . ':00')->toArray();
            $inbound_data  = array_fill(0, 24, 0);
            $outbound_data = array_fill(0, 24, 0);
            $revenue_data  = array_fill(0, 24, 0);

            $inbounds = Inbound::whereDate('date', Carbon::today())->get();
            foreach ($inbounds as $row) {
                if ($row->created_at) {
                    $hour = (int) $row->created_at->format('G');
                    $inbound_data[$hour] += $row->quantity;
                }
            }

            $outbounds = Outbound::whereDate('date', Carbon::today())->get();
            foreach ($outbounds as $row) {
                if ($row->created_at) {
                    $hour = (int) $row->created_at->format('G');
                    $outbound_data[$hour] += $row->quantity;
                }
            }

            $sales = SaleTransaction::whereDate('date', Carbon::today())->get();
            foreach ($sales as $row) {
                if ($row->created_at) {
                    $hour = (int) $row->created_at->format('G');
                    $revenue_data[$hour] += (float) $row->total_amount;
                }
            }

        } elseif ($period === 'month') {
            $daysInMonth   = Carbon::now()->daysInMonth;
            $chart_labels  = collect(range(1, $daysInMonth))->map(fn($d) => $d . ' ' . Carbon::now()->format('M'))->toArray();
            $inbound_data  = array_fill(0, $daysInMonth, 0);
            $outbound_data = array_fill(0, $daysInMonth, 0);
            $revenue_data  = array_fill(0, $daysInMonth, 0);

            $inbounds = Inbound::whereYear('date', Carbon::now()->year)
                ->whereMonth('date', Carbon::now()->month)
                ->get();
            foreach ($inbounds as $row) {
                $day = (int) Carbon::parse($row->date)->day;
                if ($day >= 1 && $day <= $daysInMonth) {
                    $inbound_data[$day - 1] += $row->quantity;
                }
            }

            $outbounds = Outbound::whereYear('date', Carbon::now()->year)
                ->whereMonth('date', Carbon::now()->month)
                ->get();
            foreach ($outbounds as $row) {
                $day = (int) Carbon::parse($row->date)->day;
                if ($day >= 1 && $day <= $daysInMonth) {
                    $outbound_data[$day - 1] += $row->quantity;
                }
            }

            $sales = SaleTransaction::whereYear('date', Carbon::now()->year)
                ->whereMonth('date', Carbon::now()->month)
                ->get();
            foreach ($sales as $row) {
                $day = (int) Carbon::parse($row->date)->day;
                if ($day >= 1 && $day <= $daysInMonth) {
                    $revenue_data[$day - 1] += (float) $row->total_amount;
                }
            }

        } else {
            $chart_labels  = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
            $inbound_data  = array_fill(0, 12, 0);
            $outbound_data = array_fill(0, 12, 0);
            $revenue_data  = array_fill(0, 12, 0);

            $inbounds = Inbound::whereYear('date', Carbon::now()->year)->get();
            foreach ($inbounds as $row) {
                $month = (int) Carbon::parse($row->date)->month;
                if ($month >= 1 && $month <= 12) {
                    $inbound_data[$month - 1] += $row->quantity;
                }
            }

            $outbounds = Outbound::whereYear('date', Carbon::now()->year)->get();
            foreach ($outbounds as $row) {
                $month = (int) Carbon::parse($row->date)->month;
                if ($month >= 1 && $month <= 12) {
                    $outbound_data[$month - 1] += $row->quantity;
                }
            }

            $sales = SaleTransaction::whereYear('date', Carbon::now()->year)->get();
            foreach ($sales as $row) {
                $month = (int) Carbon::parse($row->date)->month;
                if ($month >= 1 && $month <= 12) {
                    $revenue_data[$month - 1] += (float) $row->total_amount;
                }
            }
        }

        return [$chart_labels, $inbound_data, $outbound_data, $revenue_data];
    }

    // ================================================================
    // HELPER: Chart khusus dapur — tanpa revenue, filter bakery
    // ================================================================
    private function buildChartDataDapur(string $period, array $bakery): array
    {
        if ($period === 'today') {
            $chart_labels  = collect(range(0, 23))->map(fn($h) => str_pad($h, 2, '0', STR_PAD_LEFT) . ':00')->toArray();
            $inbound_data  = array_fill(0, 24, 0);
            $outbound_data = array_fill(0, 24, 0);

            $inbounds = Inbound::whereDate('date', Carbon::today())
                ->whereHas('item', fn($q) => $q->whereIn('category', $bakery))
                ->get();
            foreach ($inbounds as $row) {
                if ($row->created_at) {
                    $hour = (int) $row->created_at->format('G');
                    $inbound_data[$hour] += $row->quantity;
                }
            }

            $outbounds = Outbound::whereDate('date', Carbon::today())
                ->whereHas('item', fn($q) => $q->whereIn('category', $bakery))
                ->get();
            foreach ($outbounds as $row) {
                if ($row->created_at) {
                    $hour = (int) $row->created_at->format('G');
                    $outbound_data[$hour] += $row->quantity;
                }
            }

        } elseif ($period === 'month') {
            $daysInMonth   = Carbon::now()->daysInMonth;
            $chart_labels  = collect(range(1, $daysInMonth))->map(fn($d) => $d . ' ' . Carbon::now()->format('M'))->toArray();
            $inbound_data  = array_fill(0, $daysInMonth, 0);
            $outbound_data = array_fill(0, $daysInMonth, 0);

            $inbounds = Inbound::whereYear('date', Carbon::now()->year)
                ->whereMonth('date', Carbon::now()->month)
                ->whereHas('item', fn($q) => $q->whereIn('category', $bakery))
                ->get();
            foreach ($inbounds as $row) {
                $day = (int) Carbon::parse($row->date)->day;
                if ($day >= 1 && $day <= $daysInMonth) {
                    $inbound_data[$day - 1] += $row->quantity;
                }
            }

            $outbounds = Outbound::whereYear('date', Carbon::now()->year)
                ->whereMonth('date', Carbon::now()->month)
                ->whereHas('item', fn($q) => $q->whereIn('category', $bakery))
                ->get();
            foreach ($outbounds as $row) {
                $day = (int) Carbon::parse($row->date)->day;
                if ($day >= 1 && $day <= $daysInMonth) {
                    $outbound_data[$day - 1] += $row->quantity;
                }
            }

        } else {
            $chart_labels  = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
            $inbound_data  = array_fill(0, 12, 0);
            $outbound_data = array_fill(0, 12, 0);

            $inbounds = Inbound::whereYear('date', Carbon::now()->year)
                ->whereHas('item', fn($q) => $q->whereIn('category', $bakery))
                ->get();
            foreach ($inbounds as $row) {
                $month = (int) Carbon::parse($row->date)->month;
                if ($month >= 1 && $month <= 12) {
                    $inbound_data[$month - 1] += $row->quantity;
                }
            }

            $outbounds = Outbound::whereYear('date', Carbon::now()->year)
                ->whereHas('item', fn($q) => $q->whereIn('category', $bakery))
                ->get();
            foreach ($outbounds as $row) {
                $month = (int) Carbon::parse($row->date)->month;
                if ($month >= 1 && $month <= 12) {
                    $outbound_data[$month - 1] += $row->quantity;
                }
            }
        }

        return [$chart_labels, $inbound_data, $outbound_data];
    }
}