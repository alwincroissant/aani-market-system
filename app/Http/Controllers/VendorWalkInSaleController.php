<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\WalkInSale;

class VendorWalkInSaleController extends Controller
{
    /* ──────────────────────────────────────────────
     |  LIST — show all physical / walk-in sales
     |  with date filter (defaults to today)
     * ────────────────────────────────────────────── */
    public function index(Request $request)
    {
        $vendor = Vendor::where('user_id', Auth::id())->firstOrFail();

        $dateFrom = $request->input('date_from', now()->startOfWeek()->toDateString());
        $dateTo   = $request->input('date_to', now()->toDateString());
        $viewMode = $request->input('view_mode', 'active');

        if (!in_array($viewMode, ['active', 'archived', 'all'], true)) {
            $viewMode = 'active';
        }

        $salesQuery = WalkInSale::where('vendor_id', $vendor->id)
            ->whereDate('sale_date', '>=', $dateFrom)
            ->whereDate('sale_date', '<=', $dateTo);

        if ($viewMode === 'archived') {
            $salesQuery->onlyTrashed();
        } elseif ($viewMode === 'all') {
            $salesQuery->withTrashed();
        }

        $sales = (clone $salesQuery)
            ->orderByDesc('sale_date')
            ->orderByDesc('sale_time')
            ->paginate(25)
            ->appends($request->only(['date_from', 'date_to', 'view_mode']));

        // Summary stats for the selected period
        $summary = (clone $salesQuery)
            ->selectRaw('COUNT(*) as total_transactions, SUM(quantity * unit_price) as total_revenue, SUM(quantity) as total_items')
            ->first();

        // Today's physical-sale total
        $todayPhysical = WalkInSale::where('vendor_id', $vendor->id)
            ->whereDate('sale_date', now()->toDateString())
            ->sum(DB::raw('quantity * unit_price'));

        // Today's online-sale total
        $todayOnline = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('order_items.vendor_id', $vendor->id)
            ->whereIn('orders.order_status', ['completed', 'delivered'])
            ->whereDate('orders.created_at', now()->toDateString())
            ->sum(DB::raw('order_items.quantity * order_items.unit_price'));

        return view('vendor.walk-in-sales.index', compact(
            'vendor', 'sales', 'summary', 'dateFrom', 'dateTo',
            'todayPhysical', 'todayOnline', 'viewMode'
        ));
    }

    /* ──────────────────────────────────────────────
     |  CREATE — show the "record a physical sale" form
     * ────────────────────────────────────────────── */
    public function create()
    {
        $vendor   = Vendor::where('user_id', Auth::id())->firstOrFail();
        $products = Product::where('vendor_id', $vendor->id)
            ->where('is_available', true)
            ->orderBy('product_name')
            ->get();

        return view('vendor.walk-in-sales.create', compact('vendor', 'products'));
    }

    /* ──────────────────────────────────────────────
     |  STORE — persist one or more physical sale items
     * ────────────────────────────────────────────── */
    public function store(Request $request)
    {
        $vendor = Vendor::where('user_id', Auth::id())->firstOrFail();

        $request->validate([
            'items'                => 'required|array|min:1',
            'items.*.product_id'   => [
                'nullable',
                Rule::exists('products', 'id')->where(function ($query) use ($vendor) {
                    $query->where('vendor_id', $vendor->id);
                }),
            ],
            'items.*.product_name' => 'required|string|max:255',
            'items.*.quantity'     => 'required|integer|min:1',
            'items.*.unit_price'   => 'required|numeric|min:0',
            'items.*.notes'        => 'nullable|string|max:500',
            'sale_date'            => 'nullable|date|before_or_equal:today',
        ]);

        $saleDate = $request->input('sale_date', now()->toDateString());

        DB::beginTransaction();
        try {
            foreach ($request->input('items') as $item) {
                WalkInSale::create([
                    'vendor_id'    => $vendor->id,
                    'sale_date'    => $saleDate,
                    'sale_time'    => now(),
                    'product_id'   => $item['product_id'] ?: null,
                    'product_name' => $item['product_name'],
                    'quantity'     => (int) $item['quantity'],
                    'unit_price'   => $item['unit_price'],
                    'notes'        => $item['notes'] ?? null,
                ]);

                // Optionally deduct stock when a linked product is selected
                if (!empty($item['product_id'])) {
                    $product = Product::find($item['product_id']);
                    if ($product && $product->track_stock) {
                        $previousStock = $product->stock_quantity;
                        $product->decrement('stock_quantity', (int) $item['quantity']);
                        // Log the stock change
                        DB::table('stock_logs')->insert([
                            'product_id'       => $product->id,
                            'vendor_id'        => $vendor->id,
                            'previous_stock'   => $previousStock,
                            'new_stock'        => $product->fresh()->stock_quantity,
                            'quantity_changed'  => -(int) $item['quantity'],
                            'change_type'      => 'sale',
                            'notes'            => 'Walk-in / physical sale',
                            'created_at'       => now(),
                            'updated_at'       => now(),
                        ]);
                    }
                }
            }
            DB::commit();

            return redirect()
                ->route('vendor.walk-in-sales.index')
                ->with('success', 'Physical sale recorded successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to record sale: ' . $e->getMessage());
        }
    }

    /* ──────────────────────────────────────────────
     |  DESTROY — delete a walk-in sale record
     * ────────────────────────────────────────────── */
    public function destroy($id)
    {
        $vendor = Vendor::where('user_id', Auth::id())->firstOrFail();
        $sale   = WalkInSale::where('vendor_id', $vendor->id)->findOrFail($id);
        $sale->delete();

        return redirect()
            ->route('vendor.walk-in-sales.index')
            ->with('success', 'Sale record archived successfully.');
    }

    public function edit($id)
    {
        $vendor = Vendor::where('user_id', Auth::id())->firstOrFail();
        $sale = WalkInSale::where('vendor_id', $vendor->id)->findOrFail($id);
        $products = Product::where('vendor_id', $vendor->id)
            ->where('is_available', true)
            ->orderBy('product_name')
            ->get();

        return view('vendor.walk-in-sales.edit', compact('vendor', 'sale', 'products'));
    }

    public function update(Request $request, $id)
    {
        $vendor = Vendor::where('user_id', Auth::id())->firstOrFail();
        $sale = WalkInSale::where('vendor_id', $vendor->id)->findOrFail($id);

        $request->validate([
            'sale_date' => 'required|date|before_or_equal:today',
            'product_id' => [
                'nullable',
                Rule::exists('products', 'id')->where(function ($query) use ($vendor) {
                    $query->where('vendor_id', $vendor->id);
                }),
            ],
            'product_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $sale->update([
            'sale_date' => $request->input('sale_date'),
            'product_id' => $request->input('product_id') ?: null,
            'product_name' => $request->input('product_name'),
            'quantity' => (int) $request->input('quantity'),
            'unit_price' => $request->input('unit_price'),
            'notes' => $request->input('notes'),
        ]);

        return redirect()
            ->route('vendor.walk-in-sales.index')
            ->with('success', 'Sale record updated successfully.');
    }

    public function restore($id)
    {
        $vendor = Vendor::where('user_id', Auth::id())->firstOrFail();
        $sale = WalkInSale::onlyTrashed()
            ->where('vendor_id', $vendor->id)
            ->findOrFail($id);

        $sale->restore();

        return redirect()
            ->route('vendor.walk-in-sales.index', ['view_mode' => 'archived'])
            ->with('success', 'Archived sale restored successfully.');
    }

    public function forceDestroy($id)
    {
        $vendor = Vendor::where('user_id', Auth::id())->firstOrFail();
        $sale = WalkInSale::withTrashed()
            ->where('vendor_id', $vendor->id)
            ->findOrFail($id);

        $sale->forceDelete();

        return redirect()
            ->route('vendor.walk-in-sales.index', ['view_mode' => 'archived'])
            ->with('success', 'Sale record permanently deleted.');
    }

    /* ──────────────────────────────────────────────
     |  API: get product info for JS autofill
     * ────────────────────────────────────────────── */
    public function productInfo($productId)
    {
        $vendor  = Vendor::where('user_id', Auth::id())->firstOrFail();
        $product = Product::where('vendor_id', $vendor->id)->findOrFail($productId);

        return response()->json([
            'product_name' => $product->product_name,
            'unit_price'   => $product->price_per_unit,
            'unit_type'    => $product->unit_type,
            'stock'        => $product->stock_quantity,
        ]);
    }
}
