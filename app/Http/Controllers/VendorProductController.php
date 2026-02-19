<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use App\Models\StockLog;
use Illuminate\Support\Facades\Auth;

class VendorProductController extends Controller
{
    public function index(Request $request)
    {
        $vendor = DB::table('vendors')
            ->where('user_id', Auth::id())
            ->first();

        if (!$vendor) {
            Log::warning('Vendor not found for user', ['user_id' => Auth::id()]);
            return redirect()->route('home')->with('error', 'Vendor profile not found.');
        }

        $query = DB::table('products as p')
            ->join('product_categories as pc', 'p.category_id', '=', 'pc.id')
            ->where('p.vendor_id', $vendor->id)
            ->select(
                'p.id',
                'p.product_name',
                'p.description',
                'p.price_per_unit',
                'p.unit_type',
                'p.product_image_url',
                'p.is_available',
                'p.category_id',
                'p.stock_quantity',
                'p.minimum_stock',
                'p.track_stock',
                'p.deleted_at',
                'pc.category_name',
                'pc.color_code'
            );

        // Handle active vs deleted products
        if ($request->input('show') === 'deleted') {
            $query->whereNotNull('p.deleted_at');
        } else {
            $query->whereNull('p.deleted_at');
        }

        // Search by product name
        if ($request->filled('search')) {
            $query->where('p.product_name', 'like', '%' . $request->search . '%');
        }

        // Filter by stock status (only for active products)
        if ($request->input('show') !== 'deleted' && $request->filled('status')) {
            switch ($request->status) {
                case 'in_stock':
                    $query->where('p.stock_quantity', '>=', 10);
                    break;
                case 'low_stock':
                    $query->whereBetween('p.stock_quantity', [1, 9]);
                    break;
                case 'out_of_stock':
                    $query->where('p.stock_quantity', 0);
                    break;
            }
        }

        // Filter by price range
        if ($request->filled('price_range')) {
            switch ($request->price_range) {
                case '0-100':
                    $query->whereBetween('p.price_per_unit', [0, 100]);
                    break;
                case '100-500':
                    $query->whereBetween('p.price_per_unit', [100, 500]);
                    break;
                case '500-1000':
                    $query->whereBetween('p.price_per_unit', [500, 1000]);
                    break;
                case '1000+':
                    $query->where('p.price_per_unit', '>=', 1000);
                    break;
            }
        }

        $products = $query->orderBy('p.id', 'DESC')->paginate(10);

        Log::debug('Vendor products loaded', [
            'vendor_id' => $vendor->id,
            'products_count' => $products->count(),
            'total' => $products->total()
        ]);

        return view('product.index', compact('products'));
    }

    public function create()
    {
        $categories = DB::table('product_categories')->get();

        return view('product.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $rules = [
            'product_name' => ['required', 'min:3', 'max:255'],
            'description' => ['nullable', 'max:1000'],
            'price_per_unit' => ['required', 'numeric', 'min:0'],
            'unit_type' => ['required', 'in:kg,g,piece,bundle,pack,dozen,liter'],
            'category_id' => ['required', 'exists:product_categories,id'],
            'product_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'track_stock' => ['boolean'],
        ];

        $messages = [
            'product_name.required' => 'Product name is required.',
            'product_name.min' => 'Product name must be at least 3 characters.',
            'price_per_unit.required' => 'Price is required.',
            'price_per_unit.numeric' => 'Price must be a valid number.',
            'unit_type.required' => 'Unit type is required.',
            'category_id.required' => 'Please select a category.',
            'stock_quantity.required' => 'Stock quantity is required.',
            'minimum_stock.required' => 'Minimum stock level is required.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $vendor = DB::table('vendors')
            ->where('user_id', Auth::id())
            ->first();

        if (!$vendor) {
            return redirect()->back()->with('error', 'Vendor profile not found.');
        }

        $imagePath = null;
        if ($request->hasFile('product_image')) {
            $name = $request->file('product_image')->getClientOriginalName();
            $path = \Illuminate\Support\Facades\Storage::putFileAs(
                'public/products',
                $request->file('product_image'),
                $name
            );
            $imagePath = 'storage/products/' . $name;
        }

        $product = new Product();
        $product->vendor_id = $vendor->id;
        $product->category_id = $request->category_id;
        $product->product_name = trim($request->product_name);
        $product->description = $request->description;
        $product->price_per_unit = $request->price_per_unit;
        $product->unit_type = trim($request->unit_type);
        $product->product_image_url = $imagePath;
        $product->is_available = $request->has('is_available') ? true : false;
        $product->stock_quantity = $request->stock_quantity;
        $product->minimum_stock = $request->minimum_stock;
        $product->track_stock = $request->has('track_stock') ? true : false;
        $product->allow_backorder = false;
        $product->stock_notes = null;
        $product->save();

        // Log initial stock entry
        if ($request->stock_quantity > 0) {
            StockLog::create([
                'product_id' => $product->id,
                'vendor_id' => $vendor->id,
                'previous_stock' => 0,
                'new_stock' => $request->stock_quantity,
                'quantity_changed' => $request->stock_quantity,
                'change_type' => 'restock',
                'notes' => 'Initial stock entry',
            ]);
        }

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function show(string $id)
    {
        $product = DB::table('products as p')
            ->join('product_categories as pc', 'p.category_id', '=', 'pc.id')
            ->join('vendors as v', 'p.vendor_id', '=', 'v.id')
            ->where('p.id', $id)
            ->whereNull('p.deleted_at')
            ->select(
                'p.*',
                'pc.category_name',
                'v.business_name'
            )
            ->first();

        if (!$product) {
            return redirect()->route('products.index')->with('error', 'Product not found.');
        }

        return view('product.show', compact('product'));
    }

    public function edit(string $id)
    {
        $vendor = DB::table('vendors')
            ->where('user_id', Auth::id())
            ->first();

        $product = DB::table('products as p')
            ->join('product_categories as pc', 'p.category_id', '=', 'pc.id')
            ->where('p.id', $id)
            ->where('p.vendor_id', $vendor->id)
            ->whereNull('p.deleted_at')
            ->select('p.*', 'pc.category_name')
            ->first();

        if (!$product) {
            return redirect()->route('products.index')->with('error', 'Product not found.');
        }

        $categories = DB::table('product_categories')
            ->where('id', '<>', $product->category_id)
            ->get();

        return view('product.edit', compact('product', 'categories'));
    }

    public function update(Request $request, string $id)
    {
        $rules = [
            'product_name' => ['required', 'min:3', 'max:255'],
            'description' => ['nullable', 'max:1000'],
            'price_per_unit' => ['required', 'numeric', 'min:0'],
            'unit_type' => ['required', 'in:kg,g,piece,bundle,pack,dozen,liter'],
            'category_id' => ['required', 'exists:product_categories,id'],
            'product_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'track_stock' => ['boolean'],
        ];

        $messages = [
            'product_name.required' => 'Product name is required.',
            'product_name.min' => 'Product name must be at least 3 characters.',
            'price_per_unit.required' => 'Price is required.',
            'price_per_unit.numeric' => 'Price must be a valid number.',
            'unit_type.required' => 'Unit type is required.',
            'category_id.required' => 'Please select a category.',
            'stock_quantity.required' => 'Stock quantity is required.',
            'minimum_stock.required' => 'Minimum stock level is required.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $vendor = DB::table('vendors')
            ->where('user_id', Auth::id())
            ->first();

        $product = Product::where('id', $id)
            ->where('vendor_id', $vendor->id)
            ->first();

        if (!$product) {
            return redirect()->route('products.index')->with('error', 'Product not found.');
        }

        // Check if stock quantity is changing
        $oldStock = $product->stock_quantity;
        $newStock = $request->stock_quantity;

        $updateData = [
            'category_id' => $request->category_id,
            'product_name' => trim($request->product_name),
            'description' => $request->description,
            'price_per_unit' => $request->price_per_unit,
            'unit_type' => trim($request->unit_type),
            'is_available' => $request->has('is_available') ? true : false,
            'stock_quantity' => $request->stock_quantity,
            'minimum_stock' => $request->minimum_stock,
            'track_stock' => $request->has('track_stock') ? true : false,
        ];

        if ($request->hasFile('product_image')) {
            $name = $request->file('product_image')->getClientOriginalName();
            $path = \Illuminate\Support\Facades\Storage::putFileAs(
                'public/products',
                $request->file('product_image'),
                $name
            );
            $updateData['product_image_url'] = 'storage/products/' . $name;
        }

        Product::where('id', $id)->update($updateData);

        // Log stock change if quantity changed
        if ($oldStock != $newStock) {
            $changeType = 'adjustment';
            if ($newStock > $oldStock) {
                $changeType = 'restock';
            }
            
            StockLog::create([
                'product_id' => $id,
                'vendor_id' => $vendor->id,
                'previous_stock' => $oldStock,
                'new_stock' => $newStock,
                'quantity_changed' => $newStock - $oldStock,
                'change_type' => $changeType,
                'notes' => null,
            ]);
        }

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function batchUpdate(Request $request)
    {
        $vendor = DB::table('vendors')
            ->where('user_id', Auth::id())
            ->first();

        if (!$vendor) {
            return redirect()->back()->with('error', 'Vendor profile not found.');
        }

        $request->validate([
            'product_ids' => 'required|string',
            'action' => 'required|in:add,subtract,set',
            'amount' => 'required|integer|min:0',
        ]);

        $productIds = array_filter(explode(',', $request->product_ids));
        $action = $request->action;
        $amount = $request->amount;

        // Verify all products belong to vendor
        $productsCount = Product::whereIn('id', $productIds)
            ->where('vendor_id', $vendor->id)
            ->count();

        if ($productsCount !== count($productIds)) {
            return redirect()->back()->with('error', 'One or more products do not belong to your store.');
        }

        foreach ($productIds as $productId) {
            $product = Product::find($productId);
            $oldStock = $product->stock_quantity;
            $newStock = $oldStock;

            switch ($action) {
                case 'add':
                    $newStock = $oldStock + $amount;
                    break;
                case 'subtract':
                    $newStock = max(0, $oldStock - $amount);
                    break;
                case 'set':
                    $newStock = $amount;
                    break;
            }

            $product->update(['stock_quantity' => $newStock]);

            // Determine change type
            $changeType = 'adjustment';
            if ($newStock > $oldStock) {
                $changeType = 'restock';
            }

            StockLog::create([
                'product_id' => $productId,
                'vendor_id' => $vendor->id,
                'previous_stock' => $oldStock,
                'new_stock' => $newStock,
                'quantity_changed' => $newStock - $oldStock,
                'change_type' => $changeType,
                'notes' => 'Batch update: ' . $action,
            ]);
        }

        return redirect()->route('products.index')->with('success', count($productIds) . ' product(s) stock updated successfully.');
    }

    public function destroy(string $id)
    {
        $vendor = DB::table('vendors')
            ->where('user_id', Auth::id())
            ->first();

        $product = Product::where('id', $id)
            ->where('vendor_id', $vendor->id)
            ->first();

        if (!$product) {
            return redirect()->route('products.index')->with('error', 'Product not found.');
        }

        $product->delete(); // Uses soft delete

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }

    public function batchDelete(Request $request)
    {
        $vendor = DB::table('vendors')
            ->where('user_id', Auth::id())
            ->first();

        if (!$vendor) {
            return redirect()->back()->with('error', 'Vendor profile not found.');
        }

        $request->validate([
            'product_ids' => 'required|string',
        ]);

        $productIds = array_filter(explode(',', $request->product_ids));

        // Verify all products belong to vendor
        $productsCount = Product::whereIn('id', $productIds)
            ->where('vendor_id', $vendor->id)
            ->count();

        if ($productsCount !== count($productIds)) {
            return redirect()->back()->with('error', 'One or more products do not belong to your store.');
        }

        Product::whereIn('id', $productIds)->delete(); // Soft delete

        return redirect()->route('products.index')->with('success', count($productIds) . ' product(s) deleted successfully.');
    }

    public function batchRestore(Request $request)
    {
        $vendor = DB::table('vendors')
            ->where('user_id', Auth::id())
            ->first();

        if (!$vendor) {
            return redirect()->back()->with('error', 'Vendor profile not found.');
        }

        $request->validate([
            'product_ids' => 'required|string',
        ]);

        $productIds = array_filter(explode(',', $request->product_ids));

        // Verify all products belong to vendor
        $productsCount = Product::withTrashed()->whereIn('id', $productIds)
            ->where('vendor_id', $vendor->id)
            ->count();

        if ($productsCount !== count($productIds)) {
            return redirect()->back()->with('error', 'One or more products do not belong to your store.');
        }

        Product::withTrashed()->whereIn('id', $productIds)->restore();

        return redirect()->route('products.index')->with('success', count($productIds) . ' product(s) restored successfully.');
    }

    public function batchForceDelete(Request $request)
    {
        $vendor = DB::table('vendors')
            ->where('user_id', Auth::id())
            ->first();

        if (!$vendor) {
            return redirect()->back()->with('error', 'Vendor profile not found.');
        }

        $request->validate([
            'product_ids' => 'required|string',
        ]);

        $productIds = array_filter(explode(',', $request->product_ids));

        // Verify all products belong to vendor
        $productsCount = Product::withTrashed()->whereIn('id', $productIds)
            ->where('vendor_id', $vendor->id)
            ->count();

        if ($productsCount !== count($productIds)) {
            return redirect()->back()->with('error', 'One or more products do not belong to your store.');
        }

        Product::withTrashed()->whereIn('id', $productIds)->forceDelete();

        return redirect()->route('products.index')->with('success', count($productIds) . ' product(s) permanently deleted.');
    }

    public function restore($id)
    {
        $vendor = DB::table('vendors')
            ->where('user_id', Auth::id())
            ->first();

        $product = Product::withTrashed()->where('id', $id)
            ->where('vendor_id', $vendor->id)
            ->first();

        if (!$product) {
            return redirect()->route('products.index')->with('error', 'Product not found.');
        }

        $product->restore();

        return redirect()->route('products.index')->with('success', 'Product restored successfully.');
    }
}

