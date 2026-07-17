<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ItemController extends Controller
{
    private const ALLOWED_CATEGORIES = ['ATK', 'Elektronik', 'Bakery_Jadi', 'Bakery_Bahan_Baku', 'Minuman', 'Snack', 'Kemasan'];
    private const DAPUR_CATEGORIES   = ['Bakery_Jadi', 'Bakery_Bahan_Baku', 'Kemasan'];

    public function index(Request $request)
    {
        $user     = Auth::user();
        $isDapur  = $user->role === 'dapur';
        $isAdmin  = $user->role === 'admin';

        // Tentukan kategori yang boleh diakses berdasarkan role
        if ($isDapur) {
            $allowedCategories = self::DAPUR_CATEGORIES;
        } elseif ($isAdmin) {
            // Admin tidak boleh mengurus bahan baku
            $allowedCategories = ['ATK', 'Elektronik', 'Bakery_Jadi', 'Minuman', 'Snack', 'Kemasan'];
        } else {
            $allowedCategories = self::ALLOWED_CATEGORIES;
        }

        $category = in_array($request->input('category'), $allowedCategories)
                        ? $request->input('category')
                        : 'all';

        $search = mb_substr(trim($request->input('search', '')), 0, 100);

        $query = Item::orderBy('category')->orderBy('name');

        // Batasi kueri item berdasarkan role
        if ($isDapur) {
            $query->whereIn('category', self::DAPUR_CATEGORIES);
        } elseif ($isAdmin) {
            $query->where('category', '!=', 'Bakery_Bahan_Baku');
        }

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($category !== 'all') {
            $query->where('category', $category);
        }

        $items = $query->get();

        return view('items.index', compact('items', 'category', 'search', 'allowedCategories'));
    }

    public function show(Item $item)
    {
        $role = Auth::user()->role;

        // Dapur tidak boleh lihat item di luar Bakery & Kemasan
        if ($role === 'dapur' && !in_array($item->category, self::DAPUR_CATEGORIES)) {
            abort(403);
        }

        // Admin tidak boleh mengakses bahan baku
        if ($role === 'admin' && $item->category === 'Bakery_Bahan_Baku') {
            abort(403);
        }

        return view('items.show', compact('item'));
    }

    public function create()
    {
        Gate::authorize('admin-only');
        return view('items.create');
    }

    public function store(Request $request)
    {
        Gate::authorize('admin-only');

        $user = Auth::user();
        $isAdmin = $user->role === 'admin';

        // Tentukan pilihan kategori yang valid bagi admin saat membuat barang baru
        $allowedCategories = $isAdmin
            ? ['ATK', 'Elektronik', 'Bakery_Jadi', 'Minuman', 'Snack', 'Kemasan']
            : self::ALLOWED_CATEGORIES;

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:' . implode(',', $allowedCategories)],
            'unit'     => ['required', 'string', 'max:30'],
            'price'    => ['required', 'numeric', 'min:0', 'max:999999999'],
            'stock'    => ['required', 'integer', 'min:0', 'max:999999'],
        ]);

        $prefix = match($validated['category']) {
            'ATK'               => 'ATK',
            'Elektronik'        => 'ELK',
            'Bakery_Jadi'       => 'BKJ',
            'Bakery_Bahan_Baku' => 'BBK',
            'Minuman'           => 'MNM',
            'Snack'             => 'SNK',
            'Kemasan'           => 'KMS',
            default             => 'ITM',
        };

        $validated['sku'] = $prefix . '-'
            . str_pad(Item::withTrashed()->count() + 1, 4, '0', STR_PAD_LEFT)
            . '-' . (time() % 10000);

        Item::create($validated);

        return redirect()->route('items.index')
            ->with('success', "Produk '{$validated['name']}' berhasil didaftarkan ke katalog.");
    }

    public function edit(Item $item)
    {
        Gate::authorize('admin-only');

        // Cegah admin mengedit bahan baku via direct link
        if (Auth::user()->role === 'admin' && $item->category === 'Bakery_Bahan_Baku') {
            abort(403);
        }

        return view('items.edit', compact('item'));
    }

    public function update(Request $request, Item $item)
    {
        Gate::authorize('admin-only');

        $user = Auth::user();
        $isAdmin = $user->role === 'admin';

        // Cegah admin meng-update bahan baku via direct link
        if ($isAdmin && $item->category === 'Bakery_Bahan_Baku') {
            abort(403);
        }

        $allowedCategories = $isAdmin
            ? ['ATK', 'Elektronik', 'Bakery_Jadi', 'Minuman', 'Snack', 'Kemasan']
            : self::ALLOWED_CATEGORIES;

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:' . implode(',', $allowedCategories)],
            'unit'     => ['required', 'string', 'max:30'],
            'price'    => ['required', 'numeric', 'min:0', 'max:999999999'],
        ]);

        $item->update($validated);

        return redirect()->route('items.index')
            ->with('success', "Produk '{$item->name}' berhasil diperbarui.");
    }

    public function destroy(Item $item)
    {
        Gate::authorize('admin-only');

        // Cegah admin menghapus bahan baku via direct link
        if (Auth::user()->role === 'admin' && $item->category === 'Bakery_Bahan_Baku') {
            abort(403);
        }

        $name = $item->name;
        $item->delete();

        return redirect()->route('items.index')
            ->with('success', "Produk '{$name}' berhasil dihapus dari katalog.");
    }
}