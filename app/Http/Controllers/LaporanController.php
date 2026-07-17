<?php

namespace App\Http\Controllers;

use App\Models\Inbound;
use App\Models\Item;
use App\Models\Outbound;
use App\Models\SaleTransaction;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;

class LaporanController extends Controller
{
    private const TZ = 'Asia/Makassar';
    private const POS_SOURCES = ['kasir', 'pos'];

    public function index(Request $request)
    {
        Gate::authorize('view-laporan');

        $data      = $this->buildReportData($request);
        $operators = User::orderBy('name')->pluck('name', 'id');
        
        $categoriesQuery = Item::distinct();
        // Sembunyikan kategori bahan baku dari filter dropdown admin
        if (Auth::user()->role === 'admin') {
            $categoriesQuery->where('category', '!=', 'Bakery_Bahan_Baku');
        }
        $categories = $categoriesQuery->pluck('category')->map(fn($c) => [
            'value' => $c,
            'label' => $this->categoryLabel($c),
        ])->values();

        return view('laporan.index', array_merge($data['view'], [
            'operators'  => $operators,
            'categories' => $categories,

            'filterCategory'  => $request->input('filter_category', ''),
            'filterOperator'  => $request->input('filter_operator', ''),
            'filterSource'    => $request->input('filter_source', ''),

            'periodDateValue'  => $request->input('period_date',  Carbon::today(self::TZ)->format('Y-m-d')),
            'periodWeekValue'  => $request->input('period_week',  Carbon::now(self::TZ)->format('o') . '-W' . Carbon::now(self::TZ)->format('W')),
            'periodMonthValue' => $request->input('period_month', Carbon::now(self::TZ)->format('Y-m')),
            'periodYearValue'  => (int) $request->input('period_year', Carbon::now(self::TZ)->year),
        ]));
    }

    public function exportExcel(Request $request)
    {
        Gate::authorize('view-laporan');

        $data        = $this->buildReportData($request);
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        $this->buildRingkasanSheet($spreadsheet, $data);
        $this->buildPenjualanSheet($spreadsheet, $data['salesTransactions'], $data['periodLabel']);
        $this->buildInboundSheet($spreadsheet, $data['inbounds']);
        $this->buildOutboundSheet($spreadsheet, $data['manualOutbounds']);
        $this->buildStokSheet($spreadsheet, $data);

        $spreadsheet->setActiveSheetIndex(0);
        $filename = 'Laporan-Rajawali_' . $data['start']->format('Ymd') . '-' . $data['end']->format('Ymd') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function exportPdf(Request $request)
    {
        Gate::authorize('view-laporan');

        $data     = $this->buildReportData($request);
        $pdf      = Pdf::loadView('laporan.pdf', $data)->setPaper('a4', 'portrait');
        $filename = 'Laporan-Rajawali_' . $data['start']->format('Ymd') . '-' . $data['end']->format('Ymd') . '.pdf';

        return $pdf->download($filename);
    }

    public function stockByDay(Request $request)
    {
        Gate::authorize('view-laporan');

        $request->validate([
            'date'     => ['nullable', 'date_format:Y-m-d'],
            'category' => ['nullable', 'string', 'in:ATK,Elektronik,Bakery_Jadi,Bakery_Bahan_Baku,Minuman,Snack,Kemasan'],
        ]);

        $category = $request->input('category', '');

        // Proteksi jika admin mencoba query bahan baku secara paksa
        if (Auth::user()->role === 'admin' && $category === 'Bakery_Bahan_Baku') {
            abort(403, 'Akses ditolak.');
        }

        $date = $request->filled('date')
            ? Carbon::parse($request->date, self::TZ)->startOfDay()
            : Carbon::today(self::TZ);

        $start = $date->copy()->startOfDay();
        $end   = $date->copy()->endOfDay();

        $inbBeforeQuery = DB::table('inbounds')
            ->select('item_id', DB::raw('SUM(quantity) as qty'))
            ->whereDate('date', '<', $start)
            ->groupBy('item_id');

        $outBeforeQuery = DB::table('outbounds')
            ->select('item_id', DB::raw('SUM(quantity) as qty'))
            ->whereDate('date', '<', $start)
            ->groupBy('item_id');

        $inbDuringQuery = DB::table('inbounds')
            ->select('item_id', DB::raw('SUM(quantity) as qty'))
            ->whereDate('date', '>=', $start)
            ->whereDate('date', '<=', $end)
            ->groupBy('item_id');

        $outDuringQuery = DB::table('outbounds')
            ->select('item_id', DB::raw('SUM(quantity) as qty'))
            ->whereDate('date', '>=', $start)
            ->whereDate('date', '<=', $end)
            ->groupBy('item_id');

        // Saring transaksi bahan baku dari AJAX laporan harian admin
        if (Auth::user()->role === 'admin') {
            $inbBeforeQuery->whereIn('item_id', function ($q) {
                $q->select('id')->from('items')->where('category', '!=', 'Bakery_Bahan_Baku');
            });
            $outBeforeQuery->whereIn('item_id', function ($q) {
                $q->select('id')->from('items')->where('category', '!=', 'Bakery_Bahan_Baku');
            });
            $inbDuringQuery->whereIn('item_id', function ($q) {
                $q->select('id')->from('items')->where('category', '!=', 'Bakery_Bahan_Baku');
            });
            $outDuringQuery->whereIn('item_id', function ($q) {
                $q->select('id')->from('items')->where('category', '!=', 'Bakery_Bahan_Baku');
            });
        }

        $inbBefore = $inbBeforeQuery->pluck('qty', 'item_id');
        $outBefore = $outBeforeQuery->pluck('qty', 'item_id');
        $inbDuring = $inbDuringQuery->pluck('qty', 'item_id');
        $outDuring = $outDuringQuery->pluck('qty', 'item_id');

        $activeItemIds = $inbDuring->keys()
            ->merge($outDuring->keys())
            ->unique()
            ->values();

        if ($activeItemIds->isEmpty()) {
            $d     = $date->locale('id');
            $bulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
            $hari  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];

            return response()->json([
                'date'           => $hari[$date->dayOfWeek] . ', ' . $date->day . ' ' . $bulan[$date->month - 1] . ' ' . $date->year,
                'date_raw'       => $date->format('Y-m-d'),
                'category'       => $category,
                'category_label' => $this->categoryLabel($category),
                'items'          => [],
                'totals'         => [
                    'stok_awal'     => 0,
                    'mutasi_masuk'  => 0,
                    'mutasi_keluar' => 0,
                    'stok_akhir'    => 0,
                    'ending_value'  => 0,
                ],
            ]);
        }

        $itemsQuery = Item::whereIn('id', $activeItemIds);
        if ($category) {
            $itemsQuery->where('category', $category);
        }
        if (Auth::user()->role === 'admin') {
            $itemsQuery->where('category', '!=', 'Bakery_Bahan_Baku');
        }
        $itemModels = $itemsQuery->get()->keyBy('id');

        $items = collect();

        foreach ($activeItemIds as $itemId) {
            $item = $itemModels->get($itemId);
            if (! $item) continue;

            $qtyInbBefore  = (int) ($inbBefore->get($itemId, 0));
            $qtyOutBefore  = (int) ($outBefore->get($itemId, 0));
            $qtyInbDuring  = (int) ($inbDuring->get($itemId, 0));
            $qtyOutDuring  = (int) ($outDuring->get($itemId, 0));

            $stokAwal  = max(0, $qtyInbBefore - $qtyOutBefore);
            $stokAkhir = max(0, $stokAwal + $qtyInbDuring - $qtyOutDuring);

            $items->push((object) [
                'name'          => $item->name,
                'stok_awal'     => $stokAwal,
                'mutasi_masuk'  => $qtyInbDuring,
                'mutasi_keluar' => $qtyOutDuring,
                'stok_akhir'    => $stokAkhir,
                'price'         => (int) $item->price,
                'ending_value'  => $stokAkhir * (int) $item->price,
            ]);
        }

        $items = $this->mergeDuplicateItemsByName($items);

        $bulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        $hari  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];

        return response()->json([
            'date'           => $hari[$date->dayOfWeek] . ', ' . $date->day . ' ' . $bulan[$date->month - 1] . ' ' . $date->year,
            'date_raw'       => $date->format('Y-m-d'),
            'category'       => $category,
            'category_label' => $this->categoryLabel($category),
            'items'          => $items,
            'totals'         => [
                'stok_awal'     => $items->sum('stok_awal'),
                'mutasi_masuk'  => $items->sum('mutasi_masuk'),
                'mutasi_keluar' => $items->sum('mutasi_keluar'),
                'stok_akhir'    => $items->sum('stok_akhir'),
                'ending_value'  => $items->sum('ending_value'),
            ],
        ]);
    }

    private function buildReportData(Request $request): array
    {
        [$start, $end, $periodType] = $this->resolveDateRange($request);

        $filterCategory = $request->input('filter_category', '');
        $filterOperator = $request->input('filter_operator', '');
        $filterSource   = $request->input('filter_source', '');

        // Proteksi jika admin mencoba menyaring bahan baku secara paksa
        if (Auth::user()->role === 'admin' && $filterCategory === 'Bakery_Bahan_Baku') {
            abort(403, 'Akses ditolak.');
        }

        $inboundsQuery = Inbound::with(['item', 'user'])
            ->whereDate('date', '>=', $start)
            ->whereDate('date', '<=', $end)
            ->orderByDesc('date');

        if (Auth::user()->role === 'admin') {
            $inboundsQuery->whereHas('item', fn($q) => $q->where('category', '!=', 'Bakery_Bahan_Baku'));
        }
        if ($filterCategory) {
            $inboundsQuery->whereHas('item', fn($q) => $q->where('category', $filterCategory));
        }
        if ($filterOperator) {
            $inboundsQuery->where('user_id', $filterOperator);
        }

        $inbounds = $inboundsQuery->get();

        $manualOutboundsQuery = Outbound::with(['item', 'user'])
            ->whereDate('date', '>=', $start)
            ->whereDate('date', '<=', $end)
            ->whereNotIn('source', self::POS_SOURCES)
            ->orderByDesc('date');

        if (Auth::user()->role === 'admin') {
            $manualOutboundsQuery->whereHas('item', fn($q) => $q->where('category', '!=', 'Bakery_Bahan_Baku'));
        }
        if ($filterCategory) {
            $manualOutboundsQuery->whereHas('item', fn($q) => $q->where('category', $filterCategory));
        }
        if ($filterOperator) {
            $manualOutboundsQuery->where('user_id', $filterOperator);
        }

        $manualOutbounds = $manualOutboundsQuery->get();

        $allOutboundsQuery = Outbound::with('item')
            ->whereDate('date', '>=', $start)
            ->whereDate('date', '<=', $end);
        if (Auth::user()->role === 'admin') {
            $allOutboundsQuery->whereHas('item', fn($q) => $q->where('category', '!=', 'Bakery_Bahan_Baku'));
        }
        if ($filterCategory) {
            $allOutboundsQuery->whereHas('item', fn($q) => $q->where('category', $filterCategory));
        }
        $allOutbounds = $allOutboundsQuery->get();

        $totalInboundQty  = $inbounds->sum('quantity');
        $totalOutboundQty = $allOutbounds->sum('quantity');

        $salesQuery = SaleTransaction::with(['user', 'outbounds.item'])
            ->whereDate('date', '>=', $start)
            ->whereDate('date', '<=', $end)
            ->orderBy('created_at');

        if ($filterOperator) {
            $salesQuery->where('user_id', $filterOperator);
        }
        if ($filterCategory) {
            $salesQuery->whereHas('outbounds.item', fn($q) => $q->where('category', $filterCategory));
        }

        $salesTransactions = $salesQuery->get()->each(function ($sale) {
            $witaTime = $sale->created_at->setTimezone(self::TZ);
            $sale->wita_date     = $witaTime->format('d/m/Y');
            $sale->wita_time     = $witaTime->format('H:i');
            $sale->wita_date_key = $witaTime->format('Y-m-d');
        })->sortByDesc('wita_date_key')->values();

        $salesCount = $salesTransactions->count();
        $salesTotal = $salesTransactions->sum('total_amount');
        $totalOutboundValue = $salesTotal;

        $stockSummary    = $this->getStockSummary($start, $end, $filterCategory);
        $totalStockValue = $stockSummary->sum('ending_value');
        $periodLabel = $this->periodLabel($periodType, $start, $end);

        $viewData = [
            'periodType'         => $periodType,
            'periodLabel'        => $periodLabel,
            'startDate'          => $start->format('Y-m-d'),
            'endDate'            => $end->format('Y-m-d'),
            'inbounds'           => $inbounds,
            'manualOutbounds'    => $manualOutbounds,
            'totalInboundQty'    => $totalInboundQty,
            'totalOutboundQty'   => $totalOutboundQty,
            'totalOutboundValue' => $totalOutboundValue,
            'stockSummary'       => $stockSummary,
            'totalStockValue'    => $totalStockValue,
            'salesTransactions'  => $salesTransactions,
            'salesCount'         => $salesCount,
            'salesTotal'         => $salesTotal,
        ];

        return array_merge($viewData, [
            'start'             => $start,
            'end'               => $end,
            'view'              => $viewData,
        ]);
    }

    private function getStockSummary(Carbon $start, Carbon $end, string $filterCategory = '')
    {
        $inbBeforeQuery = DB::table('inbounds')
            ->select('item_id', DB::raw('SUM(quantity) as qty'))
            ->whereDate('date', '<', $start)
            ->groupBy('item_id');

        $outBeforeQuery = DB::table('outbounds')
            ->select('item_id', DB::raw('SUM(quantity) as qty'))
            ->whereDate('date', '<', $start)
            ->groupBy('item_id');

        $inbDuringQuery = DB::table('inbounds')
            ->select('item_id', DB::raw('SUM(quantity) as qty'))
            ->whereDate('date', '>=', $start)
            ->whereDate('date', '<=', $end)
            ->groupBy('item_id');

        $outDuringQuery = DB::table('outbounds')
            ->select('item_id', DB::raw('SUM(quantity) as qty'))
            ->whereDate('date', '>=', $start)
            ->whereDate('date', '<=', $end)
            ->groupBy('item_id');

        // Saring transaksi bahan baku agar tidak terhitung di summary stok admin
        if (Auth::user()->role === 'admin') {
            $inbBeforeQuery->whereIn('item_id', function ($q) {
                $q->select('id')->from('items')->where('category', '!=', 'Bakery_Bahan_Baku');
            });
            $outBeforeQuery->whereIn('item_id', function ($q) {
                $q->select('id')->from('items')->where('category', '!=', 'Bakery_Bahan_Baku');
            });
            $inbDuringQuery->whereIn('item_id', function ($q) {
                $q->select('id')->from('items')->where('category', '!=', 'Bakery_Bahan_Baku');
            });
            $outDuringQuery->whereIn('item_id', function ($q) {
                $q->select('id')->from('items')->where('category', '!=', 'Bakery_Bahan_Baku');
            });
        }

        $itemsQuery = Item::query()
            ->leftJoinSub($inbBeforeQuery,  'inb_before',  fn($j) => $j->on('inb_before.item_id',  '=', 'items.id'))
            ->leftJoinSub($outBeforeQuery,  'out_before',  fn($j) => $j->on('out_before.item_id',  '=', 'items.id'))
            ->leftJoinSub($inbDuringQuery,  'inb_during',  fn($j) => $j->on('inb_during.item_id',  '=', 'items.id'))
            ->leftJoinSub($outDuringQuery,  'out_during',  fn($j) => $j->on('out_during.item_id',  '=', 'items.id'))
            ->select(
                'items.*',
                DB::raw('COALESCE(inb_before.qty, 0) as qty_inb_before'),
                DB::raw('COALESCE(out_before.qty, 0) as qty_out_before'),
                DB::raw('COALESCE(inb_during.qty, 0) as qty_inb_during'),
                DB::raw('COALESCE(out_during.qty, 0) as qty_out_during')
            );

        if ($filterCategory) {
            $itemsQuery->where('items.category', $filterCategory);
        } elseif (Auth::user()->role === 'admin') {
            $itemsQuery->where('items.category', '!=', 'Bakery_Bahan_Baku');
        }

        $items = $itemsQuery->get()
            ->filter(fn($item) =>
                $item->qty_inb_before > 0 ||
                $item->qty_inb_during > 0 ||
                $item->qty_out_before > 0 ||
                $item->qty_out_during > 0
            )
            ->map(function ($item) {
                $stokAwal   = max(0, $item->qty_inb_before - $item->qty_out_before);
                $stokAkhir  = max(0, $stokAwal + $item->qty_inb_during - $item->qty_out_during);

                $item->stok_awal         = $stokAwal;
                $item->mutasi_masuk      = (int) $item->qty_inb_during;
                $item->mutasi_keluar     = (int) $item->qty_out_during;
                $item->stok_akhir        = $stokAkhir;
                $item->ending_value      = $stokAkhir * $item->price;
                return $item;
            });

        return $items->groupBy('category')->map(function ($group) {
            $category = $group->first()->category;
            $mergedItems = $this->mergeDuplicateItemsByName($group);

            return (object) [
                'category'        => $category,
                'category_label'  => $this->categoryLabel($category),
                'item_count'      => $mergedItems->count(),
                'stok_awal'       => $group->sum('stok_awal'),
                'mutasi_masuk'    => $group->sum('mutasi_masuk'),
                'mutasi_keluar'   => $group->sum('mutasi_keluar'),
                'stok_akhir'      => $group->sum('stok_akhir'),
                'ending_value'    => $group->sum('ending_value'),
                'items'           => $mergedItems,
            ];
        })->values();
    }

    private function mergeDuplicateItemsByName($items): Collection
    {
        return collect($items)
            ->groupBy(function ($item) {
                $name = str_replace("\xC2\xA0", ' ', (string) $item->name);
                $name = preg_replace('/\s+/u', ' ', $name);
                return mb_strtolower(trim($name));
            })
            ->map(function ($group) {
                $stokAwal     = (int) $group->sum('stok_awal');
                $mutasiMasuk  = (int) $group->sum('mutasi_masuk');
                $mutasiKeluar = (int) $group->sum('mutasi_keluar');
                $stokAkhir    = (int) $group->sum('stok_akhir');
                $endingValue  = (float) $group->sum('ending_value');
                $price        = $group->first()->price;

                return (object) [
                    'name'          => $group->first()->name,
                    'stok_awal'     => $stokAwal,
                    'mutasi_masuk'  => $mutasiMasuk,
                    'mutasi_keluar' => $mutasiKeluar,
                    'stok_akhir'    => $stokAkhir,
                    'ending_value'  => $endingValue,
                    'price'         => $price,
                ];
            })
            ->values();
    }

    private function resolveDateRange(Request $request): array
    {
        $period = $request->input('period_type', 'bulanan');

        switch ($period) {
            case 'mingguan':
                $weekValue = $request->input('period_week', Carbon::now(self::TZ)->format('o') . '-W' . Carbon::now(self::TZ)->format('W'));
                $parts = explode('-W', $weekValue);
                $year = (int)$parts[0];
                $week = (int)$parts[1];

                $start = Carbon::now(self::TZ)->setISODate($year, $week)->startOfWeek();
                $end   = $start->copy()->endOfWeek();
                $periodType = 'mingguan';
                break;

            case 'bulanan':
                $monthValue = $request->input('period_month', Carbon::now(self::TZ)->format('Y-m'));
                $start = Carbon::parse($monthValue . '-01', self::TZ)->startOfMonth();
                $end   = $start->copy()->endOfMonth();
                $periodType = 'bulanan';
                break;

            case 'tahunan':
                $yearVal = (int) $request->input('period_year', Carbon::now(self::TZ)->year);
                $start = Carbon::create($yearVal, 1, 1, 0, 0, 0, self::TZ)->startOfYear();
                $end   = $start->copy()->endOfYear();
                $periodType = 'tahunan';
                break;

            case 'custom':
                $startVal = $request->input('start_date');
                $endVal   = $request->input('end_date');
                $start = $startVal ? Carbon::parse($startVal, self::TZ)->startOfDay() : Carbon::today(self::TZ)->startOfDay();
                $end   = $endVal ? Carbon::parse($endVal, self::TZ)->endOfDay() : Carbon::today(self::TZ)->endOfDay();
                $periodType = 'custom';
                break;

            case 'harian':
            default:
                $dateVal = $request->input('period_date', Carbon::today(self::TZ)->format('Y-m-d'));
                $start = Carbon::parse($dateVal, self::TZ)->startOfDay();
                $end   = $start->copy()->endOfDay();
                $periodType = 'harian';
                break;
        }

        return [$start, $end, $periodType];
    }

    private function periodLabel(string $type, Carbon $start, Carbon $end): string
    {
        $start->locale('id');
        $end->locale('id');

        $bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

        switch ($type) {
            case 'harian':
                return $start->day . ' ' . $bulan[$start->month - 1] . ' ' . $start->year;
            case 'mingguan':
                return 'Minggu ke-' . $start->format('W') . ' (' . $start->day . ' ' . $start->shortMonthName . ' - ' . $end->day . ' ' . $end->shortMonthName . ' ' . $end->year . ')';
            case 'bulanan':
                return $bulan[$start->month - 1] . ' ' . $start->year;
            case 'tahunan':
                return 'Tahun ' . $start->year;
            case 'custom':
            default:
                return $start->day . ' ' . $start->shortMonthName . ' ' . $start->year . ' - ' . $end->day . ' ' . $end->shortMonthName . ' ' . $end->year;
        }
    }

    private function categoryLabel(string $cat): string
    {
        switch ($cat) {
            case 'ATK': return 'ATK';
            case 'Elektronik': return 'Elektronik';
            case 'Bakery_Jadi': return 'Cake & Pastry';
            case 'Bakery_Bahan_Baku': return 'Bahan Baku';
            case 'Snack': return 'Snack';
            case 'Minuman': return 'Minuman';
            case 'Kemasan': return 'Kemasan';
            default: return $cat;
        }
    }

    private function applyTableHeader($sheet, $cells)
    {
        $sheet->getStyle($cells)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E293B'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
    }

    private function applyTotalRow($sheet, $cells)
    {
        $sheet->getStyle($cells)->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F1F5F9'],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
    }

    private function applyTableBorders($sheet, $range)
    {
        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CBD5E1'],
                ],
            ],
        ]);
    }

    private function applyZebraStripes($sheet, $startRow, $endRow, $startCol, $endCol)
    {
        for ($r = $startRow; $r <= $endRow; $r++) {
            if ($r % 2 === 1) {
                $sheet->getStyle("{$startCol}{$r}:{$endCol}{$r}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F8FAFC'],
                    ],
                ]);
            }
        }
    }

    private function buildRingkasanSheet($spreadsheet, $data)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Ringkasan Aktivitas');
        $sheet->setShowGridlines(true);

        $sheet->setCellValue('A2', 'LAPORAN AKTIVITAS & MUTASI BARANG');
        $sheet->getStyle('A2')->getFont()->setSize(16)->setBold(true)->setColor(new Color('1E293B'));
        
        $sheet->setCellValue('A3', 'Toko Rajawali Tondano');
        $sheet->getStyle('A3')->getFont()->setSize(12)->setItalic(true)->setColor(new Color('64748B'));

        $sheet->setCellValue('A5', 'Periode Laporan:');
        $sheet->setCellValue('B5', $data['periodLabel']);
        $sheet->getStyle('A5')->getFont()->setBold(true);

        $sheet->setCellValue('A7', 'METRIK INVENTORI & PENJUALAN');
        $sheet->getStyle('A7')->getFont()->setSize(12)->setBold(true)->setColor(new Color('0F172A'));

        $metrics = [
            ['Total Barang Masuk (Qty)', $data['totalInboundQty'], 'pcs/unit'],
            ['Total Barang Keluar (Qty)', $data['totalOutboundQty'], 'pcs/unit'],
            ['Total Transaksi Kasir (POS)', $data['salesCount'], 'transaksi'],
            ['Total Omset Penjualan (POS)', $data['salesTotal'], 'rupiah'],
            ['Total Nilai Sisa Stok Toko', $data['totalStockValue'], 'rupiah'],
        ];

        $row = 9;
        foreach ($metrics as $m) {
            $sheet->setCellValue("A{$row}", $m[0]);
            $sheet->setCellValue("B{$row}", $m[1]);
            
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            if ($m[2] === 'rupiah') {
                $sheet->getStyle("B{$row}")->getNumberFormat()->setFormatCode('"Rp"#,##0');
            } else {
                $sheet->getStyle("B{$row}")->getNumberFormat()->setFormatCode('#,##0');
            }
            $row++;
        }

        $this->applyTableBorders($sheet, 'A9:B13');
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(25);
    }

    private function buildPenjualanSheet($spreadsheet, $sales, $periodLabel)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Laporan Penjualan (POS)');
        $sheet->setShowGridlines(true);

        $sheet->setCellValue('A2', 'DAFTAR TRANSAKSI PENJUALAN KASIR');
        $sheet->getStyle('A2')->getFont()->setSize(14)->setBold(true);
        $sheet->setCellValue('A3', 'Periode: ' . $periodLabel);

        $headers = ['No', 'ID Transaksi', 'Tanggal', 'Jam', 'Metode Pembayaran', 'Kasir', 'Total Qty', 'Total Belanja'];
        foreach ($headers as $colIdx => $h) {
            $colLetter = chr(65 + $colIdx);
            $sheet->setCellValue("{$colLetter}5", $h);
        }
        $sheet->getRowDimension(5)->setRowHeight(28);
        $this->applyTableHeader($sheet, 'A5:H5');

        $row = 6;
        $idx = 1;
        foreach ($sales as $sale) {
            $sheet->setCellValue("A{$row}", $idx);
            $sheet->setCellValue("B{$row}", 'TRX-' . str_pad($sale->id, 5, '0', STR_PAD_LEFT));
            $sheet->setCellValue("C{$row}", $sale->wita_date);
            $sheet->setCellValue("D{$row}", $sale->wita_time);
            $sheet->setCellValue("E{$row}", strtoupper($sale->payment_method));
            $sheet->setCellValue("F{$row}", $sale->user->name ?? 'Kasir');
            $sheet->setCellValue("G{$row}", $sale->outbounds->sum('quantity'));
            $sheet->setCellValue("H{$row}", $sale->total_amount);

            $sheet->getStyle("A{$row}:E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("H{$row}")->getNumberFormat()->setFormatCode('"Rp"#,##0');
            $row++;
            $idx++;
        }

        $lastRow = $row - 1;
        if ($lastRow >= 6) {
            $this->applyTableBorders($sheet, "A5:H{$lastRow}");
            $this->applyZebraStripes($sheet, 6, $lastRow, 'A', 'H');
            
            $sheet->mergeCells("A{$row}:F{$row}");
            $sheet->setCellValue("A{$row}", 'TOTAL');
            $sheet->setCellValue("G{$row}", "=SUM(G6:G{$lastRow})");
            $sheet->setCellValue("H{$row}", "=SUM(H6:H{$lastRow})");
            
            $sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("H{$row}")->getNumberFormat()->setFormatCode('"Rp"#,##0');
            $this->applyTotalRow($sheet, "A{$row}:H{$row}");
            $this->applyTableBorders($sheet, "A{$row}:H{$row}");
        }

        foreach (['A' => 6, 'B' => 18, 'C' => 15, 'D' => 10, 'E' => 20, 'F' => 20, 'G' => 12, 'H' => 20] as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }
        $sheet->freezePane('A6');
    }

    private function buildInboundSheet($spreadsheet, $inbounds)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Barang Masuk');
        $sheet->setShowGridlines(true);

        $sheet->setCellValue('A2', 'LOG BARANG MASUK (INBOUND)');
        $sheet->getStyle('A2')->getFont()->setSize(14)->setBold(true);

        $headers = ['No', 'Tanggal', 'Nama Barang', 'Kategori', 'Qty Masuk', 'Unit', 'Pemasok (Supplier)', 'Operator'];
        foreach ($headers as $colIdx => $h) {
            $colLetter = chr(65 + $colIdx);
            $sheet->setCellValue("{$colLetter}4", $h);
        }
        $sheet->getRowDimension(4)->setRowHeight(28);
        $this->applyTableHeader($sheet, 'A4:H4');

        $row = 5;
        $idx = 1;
        foreach ($inbounds as $ib) {
            $sheet->setCellValue("A{$row}", $idx);
            $sheet->setCellValue("B{$row}", Carbon::parse($ib->date)->format('d/m/Y'));
            $sheet->setCellValue("C{$row}", $ib->item->name ?? 'Produk Dihapus');
            $sheet->setCellValue("D{$row}", $this->categoryLabel($ib->item->category ?? ''));
            $sheet->setCellValue("E{$row}", $ib->quantity);
            $sheet->setCellValue("F{$row}", $ib->item->unit ?? 'pcs');
            $sheet->setCellValue("G{$row}", $ib->supplier ?? '-');
            $sheet->setCellValue("H{$row}", $ib->user->name ?? 'Sistem');

            $sheet->getStyle("A{$row}:B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $row++;
            $idx++;
        }

        $lastRow = $row - 1;
        if ($lastRow >= 5) {
            $this->applyTableBorders($sheet, "A4:H{$lastRow}");
            $this->applyZebraStripes($sheet, 5, $lastRow, 'A', 'H');

            $sheet->mergeCells("A{$row}:D{$row}");
            $sheet->setCellValue("A{$row}", 'TOTAL BARANG MASUK');
            $sheet->setCellValue("E{$row}", "=SUM(E5:E{$lastRow})");
            $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $this->applyTotalRow($sheet, "A{$row}:H{$row}");
            $this->applyTableBorders($sheet, "A{$row}:H{$row}");
        }

        foreach (['A' => 6, 'B' => 15, 'C' => 30, 'D' => 18, 'E' => 12, 'F' => 10, 'G' => 25, 'H' => 20] as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }
        $sheet->freezePane('A5');
    }

    private function buildOutboundSheet($spreadsheet, $outbounds)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Barang Keluar');
        $sheet->setShowGridlines(true);

        $sheet->setCellValue('A2', 'LOG MUTASI KELUAR MANUAL (OUTBOUND)');
        $sheet->getStyle('A2')->getFont()->setSize(14)->setBold(true);

        $headers = ['No', 'Tanggal', 'Nama Barang', 'Kategori', 'Qty Keluar', 'Unit', 'Tujuan/Pelanggan', 'Operator'];
        foreach ($headers as $colIdx => $h) {
            $colLetter = chr(65 + $colIdx);
            $sheet->setCellValue("{$colLetter}4", $h);
        }
        $sheet->getRowDimension(4)->setRowHeight(28);
        $this->applyTableHeader($sheet, 'A4:H4');

        $row = 5;
        $idx = 1;
        foreach ($outbounds as $ob) {
            $sheet->setCellValue("A{$row}", $idx);
            $sheet->setCellValue("B{$row}", Carbon::parse($ob->date)->format('d/m/Y'));
            $sheet->setCellValue("C{$row}", $ob->item->name ?? 'Produk Dihapus');
            $sheet->setCellValue("D{$row}", $this->categoryLabel($ob->item->category ?? ''));
            $sheet->setCellValue("E{$row}", $ob->quantity);
            $sheet->setCellValue("F{$row}", $ob->item->unit ?? 'pcs');
            $sheet->setCellValue("G{$row}", $ob->customer ?? 'Pelanggan');
            $sheet->setCellValue("H{$row}", $ob->user->name ?? 'Sistem');

            $sheet->getStyle("A{$row}:B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $row++;
            $idx++;
        }

        $lastRow = $row - 1;
        if ($lastRow >= 5) {
            $this->applyTableBorders($sheet, "A4:H{$lastRow}");
            $this->applyZebraStripes($sheet, 5, $lastRow, 'A', 'H');

            $sheet->mergeCells("A{$row}:D{$row}");
            $sheet->setCellValue("A{$row}", 'TOTAL BARANG KELUAR');
            $sheet->setCellValue("E{$row}", "=SUM(E5:E{$lastRow})");
            $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $this->applyTotalRow($sheet, "A{$row}:H{$row}");
            $this->applyTableBorders($sheet, "A{$row}:H{$row}");
        }

        foreach (['A' => 6, 'B' => 15, 'C' => 30, 'D' => 18, 'E' => 12, 'F' => 10, 'G' => 25, 'H' => 20] as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }
        $sheet->freezePane('A5');
    }

    private function buildStokSheet($spreadsheet, $data)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Laporan Mutasi Stok');
        $sheet->setShowGridlines(true);

        $sheet->setCellValue('A2', 'LAPORAN DETAIL MUTASI STOK BARANG');
        $sheet->getStyle('A2')->getFont()->setSize(14)->setBold(true);
        $sheet->setCellValue('A3', 'Periode Laporan: ' . $data['periodLabel']);

        $row = 6;
        $headerRow = 6;
        foreach ($data['stockSummary'] as $cat) {
            $sheet->setCellValue("A{$row}", strtoupper($cat->category_label));
            $sheet->getStyle("A{$row}")->getFont()->setSize(12)->setBold(true)->setColor(new Color('1E3A8A'));
            $row++;

            $headerRow = $row;
            $headers = ['Kategori', 'Nama Barang', 'Harga Satuan', 'Stok Awal', 'Mutasi Masuk', 'Mutasi Keluar', 'Stok Akhir', 'Nilai Stok Akhir'];
            foreach ($headers as $colIdx => $h) {
                $colLetter = chr(65 + $colIdx);
                $sheet->setCellValue("{$colLetter}{$headerRow}", $h);
            }
            $sheet->getRowDimension($headerRow)->setRowHeight(26);
            $this->applyTableHeader($sheet, "A{$headerRow}:H{$headerRow}");
            $row++;

            $firstItemRow = $row;
            foreach ($cat->items as $item) {
                $sheet->setCellValue("A{$row}", $cat->category_label);
                $sheet->setCellValue("B{$row}", $item->name);
                $sheet->setCellValue("C{$row}", $item->price);
                $sheet->setCellValue("D{$row}", $item->stok_awal);
                $sheet->setCellValue("E{$row}", $item->mutasi_masuk);
                $sheet->setCellValue("F{$row}", $item->mutasi_keluar);
                $sheet->setCellValue("G{$row}", $item->stok_akhir);
                $sheet->setCellValue("H{$row}", $item->ending_value);

                if ($item->stok_akhir == 0) {
                    $sheet->getStyle("G{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => 'DC2626']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEE2E2']],
                    ]);
                } elseif ($item->stok_akhir <= 5) {
                    $sheet->getStyle("G{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => 'D97706']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF3C7']],
                    ]);
                }
                $row++;
            }

            $lastItemRow = $row - 1;
            if ($lastItemRow >= $firstItemRow) {
                $this->applyTableBorders($sheet, "A{$firstItemRow}:H{$lastItemRow}");
                $this->applyZebraStripes($sheet, $firstItemRow, $lastItemRow, 'A', 'H');
                $sheet->getStyle("C{$firstItemRow}:C{$lastItemRow}")->getNumberFormat()->setFormatCode('"Rp"#,##0');
                $sheet->getStyle("D{$firstItemRow}:G{$lastItemRow}")->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle("H{$firstItemRow}:H{$lastItemRow}")->getNumberFormat()->setFormatCode('"Rp"#,##0');
            }

            $sheet->mergeCells("A{$row}:B{$row}");
            $sheet->setCellValue("A{$row}", 'Subtotal ' . $cat->category_label);
            $sheet->setCellValue("D{$row}", $cat->stok_awal);
            $sheet->setCellValue("E{$row}", $cat->mutasi_masuk);
            $sheet->setCellValue("F{$row}", $cat->mutasi_keluar);
            $sheet->setCellValue("G{$row}", $cat->stok_akhir);
            $sheet->setCellValue("H{$row}", $cat->ending_value);
            $sheet->getStyle("D{$row}:G{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("H{$row}")->getNumberFormat()->setFormatCode('"Rp"#,##0');
            $this->applyTotalRow($sheet, "A{$row}:H{$row}");
            $this->applyTableBorders($sheet, "A{$row}:H{$row}");
            $row += 2;
        }

        foreach (['A' => 22, 'B' => 32, 'C' => 18, 'D' => 12, 'E' => 12, 'F' => 12, 'G' => 12, 'H' => 20] as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }
        $sheet->freezePane('A' . ($headerRow + 1));
    }
}