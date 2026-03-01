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
        // Convert checkboxes to booleans BEFORE validation runs,
        // because browsers send "on" for checked boxes (not true/false)
        $request->merge([
            'is_available' => $request->has('is_available') ? true : false,
        ]);

        $rules = [
            'product_name'  => ['required', 'min:3', 'max:255'],
            'description'   => ['nullable', 'max:1000'],
            'price_per_unit'=> ['required', 'numeric', 'min:0'],
            'unit_type'     => ['required', 'in:kg,g,piece,bundle,pack,dozen,liter'],
            'category_id'   => ['required', 'exists:product_categories,id'],
            'product_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'stock_quantity'=> ['required', 'integer', 'min:0'],
            'is_available'  => ['nullable', 'boolean'],
        ];

        $messages = [
            'product_name.required'  => 'Product name is required.',
            'product_name.min'       => 'Product name must be at least 3 characters.',
            'price_per_unit.required'=> 'Price is required.',
            'price_per_unit.numeric' => 'Price must be a valid number.',
            'unit_type.required'     => 'Unit type is required.',
            'category_id.required'   => 'Please select a category.',
            'stock_quantity.required'=> 'Stock quantity is required.',
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
        
        Log::info('Product create - hasFile: ' . ($request->hasFile('product_image') ? 'YES' : 'NO'));
        
        if ($request->hasFile('product_image') && $request->file('product_image')->isValid()) {
            try {
                $file = $request->file('product_image');
                $name = time() . '_' . $file->getClientOriginalName();
                
                Log::info('Attempting to save file: ' . $name);
                Log::info('File temp path: ' . $file->getRealPath());
                
                // Use move() instead of storeAs()
                $destinationPath = storage_path('app/public/products');
                $fullPath = $destinationPath . DIRECTORY_SEPARATOR . $name;
                
                Log::info('Destination: ' . $fullPath);
                
                $moved = $file->move($destinationPath, $name);
                
                if ($moved) {
                    $imagePath = 'storage/products/' . $name;
                    Log::info('Product image saved successfully using move(): ' . $fullPath);
                } else {
                    Log::error('Failed to move file to: ' . $fullPath);
                }
            } catch (\Exception $e) {
                Log::error('Error saving product image: ' . $e->getMessage());
                Log::error('Stack trace: ' . $e->getTraceAsString());
            }
        } else if ($request->hasFile('product_image')) {
            Log::error('Product image file is not valid');
        }

        $product = new Product();
        $product->vendor_id         = $vendor->id;
        $product->category_id       = $request->category_id;
        $product->product_name      = trim($request->product_name);
        $product->description       = $request->description;
        $product->price_per_unit    = $request->price_per_unit;
        $product->unit_type         = trim($request->unit_type);
        $product->product_image_url = $imagePath;
        $product->is_available      = $request->is_available;
        $product->stock_quantity    = $request->stock_quantity;
        $product->minimum_stock     = 5; // Hardcoded: Alert when stock falls below 5
        $product->track_stock       = true; // Always track stock
        $product->allow_backorder   = false;
        $product->stock_notes       = null;
        
        Log::info('Saving product with image URL: ' . ($imagePath ?? 'NULL'));
        $product->save();
        Log::info('Product saved - ID: ' . $product->id . ', Image: ' . ($product->product_image_url ?? 'NULL'));

        // Log initial stock entry
        if ($request->stock_quantity > 0) {
            StockLog::create([
                'product_id'       => $product->id,
                'vendor_id'        => $vendor->id,
                'previous_stock'   => 0,
                'new_stock'        => $request->stock_quantity,
                'quantity_changed' => $request->stock_quantity,
                'change_type'      => 'restock',
                'notes'            => 'Initial stock entry',
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
            ->get();

        return view('product.edit', compact('product', 'categories'));
    }

    public function update(Request $request, string $id)
    {
        // Convert checkboxes to booleans BEFORE validation runs,
        // because browsers send "on" for checked boxes (not true/false)
        $request->merge([
            'is_available' => $request->has('is_available') ? true : false,
        ]);

        $rules = [
            'product_name'  => ['required', 'min:3', 'max:255'],
            'description'   => ['nullable', 'max:1000'],
            'price_per_unit'=> ['required', 'numeric', 'min:0'],
            'unit_type'     => ['required', 'in:kg,g,piece,bundle,pack,dozen,liter'],
            'category_id'   => ['required', 'exists:product_categories,id'],
            'product_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'stock_quantity'=> ['required', 'integer', 'min:0'],
            'is_available'  => ['nullable', 'boolean'],
        ];

        $messages = [
            'product_name.required'  => 'Product name is required.',
            'product_name.min'       => 'Product name must be at least 3 characters.',
            'price_per_unit.required'=> 'Price is required.',
            'price_per_unit.numeric' => 'Price must be a valid number.',
            'unit_type.required'     => 'Unit type is required.',
            'category_id.required'   => 'Please select a category.',
            'stock_quantity.required'=> 'Stock quantity is required.',
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
        $newStock  = $request->stock_quantity;

        $updateData = [
            'category_id'   => $request->category_id,
            'product_name'  => trim($request->product_name),
            'description'   => $request->description,
            'price_per_unit'=> $request->price_per_unit,
            'unit_type'     => trim($request->unit_type),
            'is_available'  => $request->is_available,
            'stock_quantity'=> $request->stock_quantity,
            'minimum_stock' => 5, // Hardcoded: Alert when stock falls below 5
            'track_stock'   => true, // Always track stock
        ];

        if ($request->hasFile('product_image') && $request->file('product_image')->isValid()) {
            try {
                $file = $request->file('product_image');
                $name = time() . '_' . $file->getClientOriginalName();
                
                Log::info('Attempting to update file: ' . $name);
                Log::info('File temp path: ' . $file->getRealPath());
                
                // Try direct move to ensure it works
                $destinationPath = storage_path('app/public/products');
                $fullPath = $destinationPath . DIRECTORY_SEPARATOR . $name;
                
                Log::info('Destination: ' . $fullPath);
                
                $moved = $file->move($destinationPath, $name);
                
                if ($moved) {
                    $updateData['product_image_url'] = 'storage/products/' . $name;
                    Log::info('Product image saved successfully using move(): ' . $fullPath);
                } else {
                    Log::error('Failed to move file to: ' . $fullPath);
                }
            } catch (\Exception $e) {
                Log::error('Error updating product image: ' . $e->getMessage());
                Log::error('Stack trace: ' . $e->getTraceAsString());
            }
        }

        Log::info('Before update - Product ID: ' . $id . ', Image URL in data: ' . ($updateData['product_image_url'] ?? 'NOT SET'));
        $result = Product::where('id', $id)->update($updateData);
        Log::info('Update result: ' . $result . ' rows updated');
        
        // Verify the update
        $updatedProduct = Product::find($id);
        Log::info('After update - Product Image URL: ' . ($updatedProduct->product_image_url ?? 'NULL'));

        // Log stock change if quantity changed
        if ($oldStock != $newStock) {
            StockLog::create([
                'product_id'       => $id,
                'vendor_id'        => $vendor->id,
                'previous_stock'   => $oldStock,
                'new_stock'        => $newStock,
                'quantity_changed' => $newStock - $oldStock,
                'change_type'      => $newStock > $oldStock ? 'restock' : 'adjustment',
                'notes'            => null,
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
            'action'      => 'required|in:add,subtract,set',
            'amount'      => 'required|integer|min:0',
        ]);

        $productIds = array_filter(explode(',', $request->product_ids));
        $action     = $request->action;
        $amount     = $request->amount;

        // Verify all products belong to vendor
        $productsCount = Product::whereIn('id', $productIds)
            ->where('vendor_id', $vendor->id)
            ->count();

        if ($productsCount !== count($productIds)) {
            return redirect()->back()->with('error', 'One or more products do not belong to your store.');
        }

        foreach ($productIds as $productId) {
            $product  = Product::find($productId);
            $oldStock = $product->stock_quantity;

            $newStock = match ($action) {
                'add'      => $oldStock + $amount,
                'subtract' => max(0, $oldStock - $amount),
                'set'      => $amount,
                default    => $oldStock,
            };

            $product->update(['stock_quantity' => $newStock]);

            StockLog::create([
                'product_id'       => $productId,
                'vendor_id'        => $vendor->id,
                'previous_stock'   => $oldStock,
                'new_stock'        => $newStock,
                'quantity_changed' => $newStock - $oldStock,
                'change_type'      => $newStock > $oldStock ? 'restock' : 'adjustment',
                'notes'            => 'Batch update: ' . $action,
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

        $productsCount = Product::whereIn('id', $productIds)
            ->where('vendor_id', $vendor->id)
            ->count();

        if ($productsCount !== count($productIds)) {
            return redirect()->back()->with('error', 'One or more products do not belong to your store.');
        }

        Product::whereIn('id', $productIds)->delete();

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