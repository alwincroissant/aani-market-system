<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Vendor;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\Auth;

class StockManagementController extends Controller
{
    public function __construct()
    {
        // Middleware is handled in routes
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Check if user has permission to access stock management
        if (!in_array($user->role, ['administrator', 'vendor'])) {
            abort(403, 'Unauthorized access');
        }
        
        $query = Product::with(['vendor', 'category']);

        if ($user->role === 'vendor') {
            // Load vendor relationship
            $vendor = Vendor::where('user_id', $user->id)->first();
            if (!$vendor) {
                return redirect()->route('vendor.dashboard')->with('error', 'Vendor profile not found. Please complete your vendor setup.');
            }
            $query->where('vendor_id', $vendor->id);
        }

        if ($request->filled('search')) {
            $query->where('product_name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'in_stock':
                    $query->where('stock_quantity', '>', 0);
                    break;
                case 'low_stock':
                    $query->whereRaw('stock_quantity > 0 AND stock_quantity <= minimum_stock');
                    break;
                case 'out_of_stock':
                    $query->where('stock_quantity', 0);
                    break;
            }
        }

        $products = $query->orderBy('product_name')->paginate(20);

        return view('stock.index', compact('products'));
    }

    public function edit(Product $product)
    {
        $user = Auth::user();
        
        // Check if user has permission to access stock management
        if (!in_array($user->role, ['administrator', 'vendor'])) {
            abort(403, 'Unauthorized access');
        }
        
        if ($user->role === 'vendor') {
            // Load vendor relationship
            $vendor = Vendor::where('user_id', $user->id)->first();
            if (!$vendor) {
                return redirect()->route('vendor.dashboard')->with('error', 'Vendor profile not found. Please complete your vendor setup.');
            }
            if ($product->vendor_id !== $vendor->id) {
                abort(403);
            }
        }

        return view('stock.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $user = Auth::user();
        
        // Check if user has permission to access stock management
        if (!in_array($user->role, ['administrator', 'vendor'])) {
            abort(403, 'Unauthorized access');
        }
        
        if ($user->role === 'vendor') {
            // Load vendor relationship
            $vendor = Vendor::where('user_id', $user->id)->first();
            if (!$vendor) {
                return redirect()->route('vendor.dashboard')->with('error', 'Vendor profile not found. Please complete your vendor setup.');
            }
            if ($product->vendor_id !== $vendor->id) {
                abort(403);
            }
        }

        $request->validate([
            'stock_quantity' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'track_stock' => 'boolean',
            'allow_backorder' => 'boolean',
            'stock_notes' => 'nullable|string|max:500',
        ]);

        $product->update([
            'stock_quantity' => $request->stock_quantity,
            'minimum_stock' => $request->minimum_stock,
            'track_stock' => $request->boolean('track_stock'),
            'allow_backorder' => $request->boolean('allow_backorder'),
            'stock_notes' => $request->stock_notes,
        ]);

        return redirect()->route('stock.index')
            ->with('success', 'Stock updated successfully.');
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.stock_quantity' => 'required|integer|min:0',
            'action' => 'required|in:set,add,subtract',
        ]);

        $user = Auth::user();
        
        // Check if user has permission to access stock management
        if (!in_array($user->role, ['administrator', 'vendor'])) {
            abort(403, 'Unauthorized access');
        }
        
        // Load vendor relationship if needed
        $vendor = null;
        if ($user->role === 'vendor') {
            $vendor = Vendor::where('user_id', $user->id)->first();
            if (!$vendor) {
                return redirect()->route('vendor.dashboard')->with('error', 'Vendor profile not found. Please complete your vendor setup.');
            }
        }
        
        $action = $request->action;
        $quantity = $request->quantity;
        $updated = 0;

        foreach ($request->products as $productData) {
            $product = Product::find($productData['id']);
            
            if ($user->role === 'vendor' && $product->vendor_id !== $vendor->id) {
                continue;
            }

            $product->updateStock($quantity, $action);
            $updated++;
        }

        return redirect()->route('stock.index')
            ->with('success', "Stock updated for {$updated} products.");
    }

    public function lowStockReport()
    {
        $user = Auth::user();
        
        // Check if user has permission to access stock management
        if (!in_array($user->role, ['administrator', 'vendor'])) {
            abort(403, 'Unauthorized access');
        }
        
        $query = Product::with(['vendor', 'category'])
            ->whereRaw('stock_quantity > 0 AND stock_quantity <= minimum_stock')
            ->where('track_stock', true);

        if ($user->role === 'vendor') {
            // Load vendor relationship
            $vendor = Vendor::where('user_id', $user->id)->first();
            if (!$vendor) {
                return redirect()->route('vendor.dashboard')->with('error', 'Vendor profile not found. Please complete your vendor setup.');
            }
            $query->where('vendor_id', $vendor->id);
        }

        $products = $query->orderBy('stock_quantity')->get();

        return view('stock.low-stock', compact('products'));
    }
}
