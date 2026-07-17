<?php

namespace App\Http\Controllers;

use App\Models\Outbound;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class OutboundController extends Controller
{
    private const ALLOWED_CATEGORIES = ['ATK', 'Elektronik', 'Bakery_Jadi', 'Bakery_Bahan_Baku', 'Minuman', 'Snack', 'Kemasan'];
    private const DAPUR_CATEGORIES   = ['Bakery_Bahan_Baku', 'Kemasan'];
    private const ALLOWED_SOURCES = ['manual', 'kasir'];    

    public function index(Request $request)
    {
        $user    = Auth::user();
        $isDapur = $user->role === 'dapur';
        $isAdmin = $user->role === 'admin';

        // Tentukan opsi filter kategori berdasarkan role
        if ($isDapur) {
            $allowedCategories = self::DAPUR_CATEGORIES;
        } elseif ($isAdmin) {
            $allowedCategories = ['ATK', 'Elektronik', 'Bakery_Jadi', 'Minuman', 'Snack', 'Kemasan'];
        } else {
            $allowedCategories = self::ALLOWED_CATEGORIES;
        }

        $category = in_array($request->input('category'), $allowedCategories)
                        ? $request->input('category')
                        : 'all';
        $source   = in_array($request->input('source'), self::ALLOWED_SOURCES)
                        ? $request->input('source')
                        : 'all';
        $search   = mb_substr(trim($request->input('search', '')), 0, 100);
        $dateFilter = in_array($request->input('date_filter'), ['today', 'yesterday', 'all'])
                        ? $request->input('date_filter')
                        : 'today';

        $query = Outbound::with('item', 'user')->latest('date')->latest();

        // Filter log keluar agar admin tidak bisa melihat mutasi bahan baku
        if ($isDapur) {
            $query->whereHas('item', fn($q) => $q->whereIn('category', self::DAPUR_CATEGORIES));
        } elseif ($isAdmin) {
            $query->whereHas('item', fn($q) => $q->where('category', '!=', 'Bakery_Bahan_Baku'));
        }

        if ($category !== 'all') {
            $query->whereHas('item', fn($q) => $q->where('category', $category));
        }
        if ($source !== 'all') {
            $query->where('source', $source);
        }
        if ($search !== '') {
            $query->whereHas('item', fn($q) => $q->where('name', 'like', "%{$search}%"));
        }
        if ($dateFilter === 'today') {
            $query->whereDate('date', Carbon::today());
        } elseif ($dateFilter === 'yesterday') {
            $query->whereDate('date', Carbon::yesterday());
        }

        // Hitung 3 stat card atas dengan query database-agnostic dan terfilter per role
        $statsBase = Outbound::query();
        if ($isDapur) {
            $statsBase->whereHas('item', fn($q) => $q->whereIn('category', self::DAPUR_CATEGORIES));
        } elseif ($isAdmin) {
            $statsBase->whereHas('item', fn($q) => $q->where('category', '!=', 'Bakery_Bahan_Baku'));
        }

        $today     = Carbon::today()->toDateString();
        $yesterday = Carbon::yesterday()->toDateString();

        $stats = $statsBase
            ->selectRaw("
                SUM(CASE WHEN date = ? THEN quantity ELSE 0 END) as today_qty,
                SUM(CASE WHEN date = ? THEN quantity ELSE 0 END) as yesterday_qty,
                SUM(quantity) as total_qty
            ", [$today, $yesterday])
            ->first();

        $total_outbound_today     = (int) ($stats->today_qty ?? 0);
        $total_outbound_yesterday = (int) ($stats->yesterday_qty ?? 0);
        $total_outbound_all       = (int) ($stats->total_qty ?? 0);

        $outbounds = $query->paginate(15)->withQueryString();

        return view('outbounds.index', compact(
            'outbounds', 'category', 'source', 'search', 'dateFilter', 'allowedCategories',
            'total_outbound_today', 'total_outbound_yesterday', 'total_outbound_all',
        ));
    }

    public function create()
    {
        Gate::authorize('admin-or-dapur');

        $user    = Auth::user();
        $isDapur = $user->role === 'dapur';
        $isAdmin = $user->role === 'admin';

        // Tampilkan item sesuai otorisasi role
        if ($isDapur) {
            $items = Item::whereIn('category', self::DAPUR_CATEGORIES)->where('stock', '>', 0)->orderBy('name')->get();
        } elseif ($isAdmin) {
            // Admin hanya boleh memilih barang keluar di luar bahan baku
            $items = Item::where('category', '!=', 'Bakery_Bahan_Baku')->where('stock', '>', 0)->orderBy('category')->orderBy('name')->get();
        } else {
            $items = Item::where('stock', '>', 0)->orderBy('category')->orderBy('name')->get();
        }

        return view('outbounds.create', compact('items', 'isDapur'));
    }

    public function store(Request $request)
    {
        Gate::authorize('admin-or-dapur');

        $user    = Auth::user();
        $isDapur = $user->role === 'dapur';
        $isAdmin = $user->role === 'admin';

        $validated = $request->validate([
            'item_id'  => ['required', 'integer', 'exists:items,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10000'],
            'customer' => ['nullable', 'string', 'max:255'],
            'date'     => ['required', 'date', 'before_or_equal:today'],
            'notes'    => ['nullable', 'string', 'max:500'],
        ]);

        $item = Item::find($validated['item_id']);

        // Proteksi di sisi backend server
        if ($isDapur) {
            if (!$item || !in_array($item->category, self::DAPUR_CATEGORIES)) {
                return back()->withInput()
                    ->withErrors(['item_id' => 'Role dapur hanya boleh mencatat bahan baku keluar.']);
            }
        } elseif ($isAdmin) {
            if (!$item || $item->category === 'Bakery_Bahan_Baku') {
                return back()->withInput()
                    ->withErrors(['item_id' => 'Role admin tidak boleh mencatat bahan baku keluar.']);
            }
        }

        try {
            DB::transaction(function () use ($validated) {
                $item = Item::lockForUpdate()->findOrFail($validated['item_id']);

                if ($item->stock < $validated['quantity']) {
                    throw new \InvalidArgumentException(
                        "Stok tidak mencukupi. Diminta: {$validated['quantity']} {$item->unit}, " .
                        "Tersedia: {$item->stock} {$item->unit}."
                    );
                }

                $item->decrement('stock', $validated['quantity']);

                Outbound::create([
                    'item_id'  => $validated['item_id'],
                    'user_id'  => Auth::id(),
                    'quantity' => $validated['quantity'],
                    'customer' => $validated['customer'] ?? null,
                    'source'   => 'manual',
                    'date'     => $validated['date'],
                    'notes'    => $validated['notes'] ?? null,
                ]);
            });

            return redirect()->route('outbounds.index')
                ->with('success', 'Bahan keluar berhasil dicatat. Stok otomatis dikurangi.');

        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['quantity' => $e->getMessage()])->withInput();

        } catch (\Exception $e) {
            Log::error('OutboundController@store gagal', [
                'user_id' => Auth::id(),
                'input'   => $validated,
                'error'   => $e->getMessage(),
            ]);

            return back()->withInput()
                ->withErrors(['error' => 'Terjadi kesalahan sistem. Silakan coba lagi atau hubungi administrator.']);
        }
    }
}