<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class VendorProductController extends Controller
{
    public function index()
    {
        $vendor = DB::table('vendors')
            ->where('user_id', Auth::id())
            ->first();

        if (!$vendor) {
            return redirect()->route('home')->with('error', 'Vendor profile not found.');
        }

        $products = DB::table('products as p')
            ->join('product_categories as pc', 'p.category_id', '=', 'pc.id')
            ->where('p.vendor_id', $vendor->id)
            ->whereNull('p.deleted_at')
            ->select(
                'p.id',
                'p.product_name',
                'p.description',
                'p.price_per_unit',
                'p.unit_type',
                'p.product_image_url',
                'p.is_available',
                'pc.category_name',
                'pc.color_code'
            )
            ->orderBy('p.id', 'DESC')
            ->paginate(10);

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
            'unit_type' => ['required', 'max:50'],
            'category_id' => ['required', 'exists:product_categories,id'],
            'product_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];

        $messages = [
            'product_name.required' => 'Product name is required.',
            'product_name.min' => 'Product name must be at least 3 characters.',
            'price_per_unit.required' => 'Price is required.',
            'price_per_unit.numeric' => 'Price must be a valid number.',
            'unit_type.required' => 'Unit type is required.',
            'category_id.required' => 'Please select a category.',
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
        $product->save();

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
            'unit_type' => ['required', 'max:50'],
            'category_id' => ['required', 'exists:product_categories,id'],
            'product_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];

        $messages = [
            'product_name.required' => 'Product name is required.',
            'product_name.min' => 'Product name must be at least 3 characters.',
            'price_per_unit.required' => 'Price is required.',
            'price_per_unit.numeric' => 'Price must be a valid number.',
            'unit_type.required' => 'Unit type is required.',
            'category_id.required' => 'Please select a category.',
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

        $updateData = [
            'category_id' => $request->category_id,
            'product_name' => trim($request->product_name),
            'description' => $request->description,
            'price_per_unit' => $request->price_per_unit,
            'unit_type' => trim($request->unit_type),
            'is_available' => $request->has('is_available') ? true : false,
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

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
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

        Product::destroy($id);

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
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

