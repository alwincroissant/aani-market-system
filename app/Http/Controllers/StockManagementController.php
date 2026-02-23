<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Vendor;
use App\Models\StockLog;
use Illuminate\Support\Facades\Auth;

class StockManagementController extends Controller
{
    public function __construct()
    {
        // No-op; route middleware controls access
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        if (! in_array($user->role, ['administrator', 'vendor'])) {
            abort(403, 'Unauthorized access');
        }

        $query = Product::with(['vendor', 'category']);

        // Vendors list for admin filter
        $vendors = [];
        if ($user->role === 'administrator') {
            $vendors = Vendor::orderBy('business_name')->get();
        }

        if ($user->role === 'vendor') {
            $vendor = Vendor::where('user_id', $user->id)->first();
            if (! $vendor) {
                return redirect()->route('vendor.dashboard')->with('error', 'Vendor profile not found.');
            }
            $query->where('vendor_id', $vendor->id);
        }

        // Apply vendor filter (admins only)
        if ($user->role === 'administrator' && $request->filled('vendor_id')) {
            $query->where('vendor_id', $request->input('vendor_id'));
        }

        if ($request->filled('search')) {
            $query->where('product_name', 'like', '%' . $request->search . '%');
        }

        $products = $query->orderBy('product_name')->paginate(20)->withQueryString();

        return view('stock.index', compact('products', 'vendors'));
    }

    public function edit(Product $product)
    {
        $user = Auth::user();
        if (! in_array($user->role, ['administrator', 'vendor'])) {
            abort(403, 'Unauthorized access');
        }

        if ($user->role === 'vendor') {
            $vendor = Vendor::where('user_id', $user->id)->first();
            if (! $vendor || $product->vendor_id !== $vendor->id) {
                abort(403);
            }
        }

        return view('stock.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $user = Auth::user();
        if (! in_array($user->role, ['administrator', 'vendor'])) {
            abort(403, 'Unauthorized access');
        }

        if ($user->role === 'vendor') {
            $vendor = Vendor::where('user_id', $user->id)->first();
            if (! $vendor || $product->vendor_id !== $vendor->id) {
                abort(403);
            }
            $vendorId = $vendor->id;
        } else {
            $vendorId = $product->vendor_id;
        }

        $request->validate([
            'action' => 'required|in:add,subtract,set',
            'amount' => 'required|integer|min:0',
            'notes'  => 'nullable|string|max:500',
        ]);

        $action = $request->input('action');
        $amount = (int) $request->input('amount');
        $notes  = $request->input('notes');

        $previous = $product->stock_quantity;
        switch ($action) {
            case 'add':
                $new = $previous + $amount;
                break;
            case 'subtract':
                $new = max(0, $previous - $amount);
                break;
            case 'set':
            default:
                $new = max(0, $amount);
        }

        $product->stock_quantity = $new;
        $product->save();

        StockLog::create([
            'product_id' => $product->id,
            'vendor_id' => $vendorId,
            'previous_stock' => $previous,
            'new_stock' => $new,
            'quantity_changed' => $new - $previous,
            'change_type' => $new > $previous ? 'restock' : 'adjustment',
            'notes' => $notes,
        ]);

        return redirect()->route('stock.index')->with('success', 'Stock updated successfully.');
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|array',
            'action' => 'required|in:add,subtract,set',
            'amount' => 'required|integer|min:0',
        ]);

        $user = Auth::user();
        if (! in_array($user->role, ['administrator', 'vendor'])) {
            abort(403, 'Unauthorized access');
        }

        $productIds = $request->input('product_ids');
        $action = $request->input('action');
        $amount = (int) $request->input('amount');
        $updated = 0;

        foreach ($productIds as $id) {
            $product = Product::find($id);
            if (! $product) continue;
            if ($user->role === 'vendor') {
                $vendor = Vendor::where('user_id', $user->id)->first();
                if (! $vendor || $product->vendor_id !== $vendor->id) continue;
                $vendorId = $vendor->id;
            } else {
                $vendorId = $product->vendor_id;
            }

            $previous = $product->stock_quantity;
            switch ($action) {
                case 'add':
                    $new = $previous + $amount; break;
                case 'subtract':
                    $new = max(0, $previous - $amount); break;
                default:
                    $new = max(0, $amount); break;
            }

            $product->stock_quantity = $new;
            $product->save();

            StockLog::create([
                'product_id' => $product->id,
                'vendor_id' => $vendorId,
                'previous_stock' => $previous,
                'new_stock' => $new,
                'quantity_changed' => $new - $previous,
                'change_type' => $new > $previous ? 'restock' : 'adjustment',
                'notes' => 'Bulk update',
            ]);

            $updated++;
        }

        return redirect()->route('stock.index')->with('success', "Stock updated for {$updated} products.");
    }

    public function recentChanges(Request $request)
    {
        $user = Auth::user();
        if (! in_array($user->role, ['administrator', 'vendor'])) {
            abort(403, 'Unauthorized access');
        }

        $query = StockLog::with(['product', 'vendor']);
        if ($user->role === 'vendor') {
            $vendor = Vendor::where('user_id', $user->id)->first();
            if (! $vendor) {
                return redirect()->route('vendor.dashboard')->with('error', 'Vendor profile not found.');
            }
            $query->where('vendor_id', $vendor->id);
        }

        $stockLogs = $query->orderBy('created_at', 'desc')->paginate(20);
        return view('stock.recent-changes', compact('stockLogs'));
    }
}
