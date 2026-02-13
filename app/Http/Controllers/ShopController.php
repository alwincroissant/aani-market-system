<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        // Get featured vendors (admin-configurable via system_settings)
        $featuredVendorIds = DB::table('system_settings')
            ->where('setting_key', 'featured_vendors')
            ->value('setting_value');
        
        $featuredVendorIds = $featuredVendorIds ? json_decode($featuredVendorIds, true) : [];
        
        $featuredVendorsQuery = DB::table('vendors as v')
            ->whereNull('v.deleted_at')
            ->leftJoin('products as p', function ($join) {
                $join->on('v.id', '=', 'p.vendor_id')
                    ->whereNull('p.deleted_at')
                    ->where('p.is_available', true);
            })
            ->select(
                'v.id',
                'v.business_name',
                'v.business_description',
                'v.logo_url',
                'v.banner_url', // Added banner_url
                'v.weekend_pickup_enabled',
                'v.weekday_delivery_enabled',
                'v.weekend_delivery_enabled',
                DB::raw('COUNT(p.id) as product_count'),
                DB::raw('MIN(p.price_per_unit) as min_price'),
                DB::raw('MAX(p.price_per_unit) as max_price')
            )
            ->groupBy(
                'v.id',
                'v.business_name',
                'v.business_description',
                'v.logo_url',
                'v.banner_url', // Added banner_url
                'v.weekend_pickup_enabled',
                'v.weekday_delivery_enabled',
                'v.weekend_delivery_enabled'
            );

        // Only show featured vendors if configured
        if (!empty($featuredVendorIds)) {
            $featuredVendorsQuery->whereIn('v.id', $featuredVendorIds);
        } else {
            // Fallback: show vendors with most products if no featured vendors configured
            $featuredVendorsQuery->orderBy('product_count', 'desc')->limit(6);
        }

        $featuredVendors = $featuredVendorsQuery->get();

        // Get filtered products
        $productQuery = DB::table('products as p')
            ->join('vendors as v', 'p.vendor_id', '=', 'v.id')
            ->leftJoin('product_categories as pc', 'p.category_id', '=', 'pc.id')
            ->whereNull('p.deleted_at')
            ->where('p.is_available', true)
            ->whereNull('v.deleted_at')
            ->select(
                'p.id',
                'p.product_name',
                'p.description',
                'p.price_per_unit',
                'p.unit_type',
                'p.product_image_url',
                'p.vendor_id',
                'v.business_name',
                'pc.category_name'
            );

        // Apply product filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $productQuery->where(function($q) use ($search) {
                $q->where('p.product_name', 'like', "%{$search}%")
                  ->orWhere('p.description', 'like', "%{$search}%")
                  ->orWhere('v.business_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('service')) {
            $service = $request->input('service');
            if ($service === 'weekend_pickup') {
                $productQuery->where('v.weekend_pickup_enabled', true);
            } elseif ($service === 'weekday_delivery') {
                $productQuery->where('v.weekday_delivery_enabled', true);
            } elseif ($service === 'weekend_delivery') {
                $productQuery->where('v.weekend_delivery_enabled', true);
            }
        }

        if ($request->filled('category')) {
            $productQuery->where('pc.category_name', $request->input('category'));
        }

        if ($request->filled('price_min')) {
            $productQuery->where('p.price_per_unit', '>=', $request->input('price_min'));
        }

        if ($request->filled('price_max')) {
            $productQuery->where('p.price_per_unit', '<=', $request->input('price_max'));
        }

        // Apply product sorting
        $productSortBy = $request->input('product_sort_by', 'created_at');
        $productSortOrder = $request->input('product_sort_order', 'desc');
        
        if ($productSortBy === 'price_low') {
            $productQuery->orderBy('p.price_per_unit', 'asc');
        } elseif ($productSortBy === 'price_high') {
            $productQuery->orderBy('p.price_per_unit', 'desc');
        } elseif ($productSortBy === 'name') {
            $productQuery->orderBy('p.product_name', $productSortOrder);
        } else {
            $productQuery->orderBy('p.created_at', $productSortOrder);
        }

        $products = $productQuery->limit(24)->get();

        // For debugging: capture SQL and bindings when app debug is enabled
        $productQuerySql = null;
        $productQueryBindings = [];
        if (config('app.debug')) {
            try {
                $productQuerySql = $productQuery->toSql();
                $productQueryBindings = $productQuery->getBindings();
            } catch (\Exception $e) {
                // ignore
            }
        }

        // Get available categories for filter dropdown
        $categories = DB::table('product_categories')
            ->whereNull('deleted_at')
            ->orderBy('category_name')
            ->get();

        return view('shop.index', compact('featuredVendors', 'products', 'categories', 'productQuerySql', 'productQueryBindings'));
    }

    public function show($vendor_id)
    {
        // Get vendor information with service flags
        $vendor = DB::table('vendors as v')
            ->where('v.id', $vendor_id)
            ->whereNull('v.deleted_at')
            ->select(
                'v.id',
                'v.business_name',
                'v.business_description',
                'v.logo_url',
                'v.banner_url', // Added banner_url
                'v.region', // Added region
                'v.business_hours', // Added business_hours
                'v.delivery_available', // Added delivery_available
                'v.weekend_pickup_enabled',
                'v.weekday_delivery_enabled',
                'v.weekend_delivery_enabled'
            )
            ->first();

        if (!$vendor) {
            return redirect()->route('home')->with('error', 'Vendor not found.');
        }

        // Get vendor's active products
        $products = DB::table('products as p')
            ->join('product_categories as pc', 'p.category_id', '=', 'pc.id')
            ->where('p.vendor_id', $vendor_id)
            ->where('p.is_available', true)
            ->whereNull('p.deleted_at')
            ->select(
                'p.id',
                'p.product_name',
                'p.description',
                'p.price_per_unit',
                'p.unit_type',
                'p.product_image_url',
                'pc.category_name',
                'pc.color_code'
            )
            ->orderBy('pc.category_name')
            ->orderBy('p.product_name')
            ->get();

        // Group products by category
        $groupedProducts = $products->groupBy('category_name');

        return view('shop.show', compact('vendor', 'groupedProducts'));
    }

    public function product($product_id)
    {
        $product = DB::table('products as p')
            ->join('vendors as v', 'p.vendor_id', '=', 'v.id')
            ->leftJoin('product_categories as pc', 'p.category_id', '=', 'pc.id')
            ->where('p.id', $product_id)
            ->whereNull('p.deleted_at')
            ->where('p.is_available', true)
            ->whereNull('v.deleted_at')
            ->select(
                'p.id',
                'p.product_name',
                'p.description',
                'p.price_per_unit',
                'p.unit_type',
                'p.product_image_url',
                'v.id as vendor_id',
                'v.business_name',
                'v.logo_url',
                'v.banner_url', // Added banner_url
                'pc.category_name'
            )
            ->first();

        if (!$product) {
            return redirect()->route('shop.index')->with('error', 'Product not found.');
        }

        return view('shop.product', compact('product'));
    }
}