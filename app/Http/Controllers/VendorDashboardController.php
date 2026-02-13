<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Vendor;

class VendorDashboardController extends Controller
{
    public function index()
    {
        $vendor = Vendor::where('user_id', Auth::id())->first();
        
        if (!$vendor) {
            return redirect()->route('auth.login')->with('error', 'Vendor profile not found.');
        }

        // Get today's sales
        $todaySales = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('order_items.vendor_id', $vendor->id)
            ->whereDate('orders.created_at', now()->toDateString())
            ->sum(DB::raw('order_items.quantity * order_items.unit_price'));

        // Get pending orders
        $pendingOrders = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('order_items.vendor_id', $vendor->id)
            ->where('orders.order_status', 'pending')
            ->count();

        // Get low stock products (not implemented in current schema)
        $lowStockProducts = 0;

        // Get weekly sales data
        $weeklySales = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $daySales = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('order_items.vendor_id', $vendor->id)
                ->whereDate('orders.created_at', $date->toDateString())
                ->sum(DB::raw('order_items.quantity * order_items.unit_price'));
            
            $weeklySales[] = $daySales;
        }
        $weeklySales = array_reverse($weeklySales);

        // Get vendor's top products
        $topProducts = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('order_items.vendor_id', $vendor->id)
            ->selectRaw('products.product_name, products.category_id, SUM(order_items.quantity) as total_sold, SUM(order_items.quantity * order_items.unit_price) as total_revenue')
            ->groupBy('products.id', 'products.product_name', 'products.category_id')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get();

        return view('vendor.dashboard', compact('vendor', 'todaySales', 'pendingOrders', 'lowStockProducts', 'weeklySales', 'topProducts'));
    }

    public function updateLiveStatus(Request $request)
    {
        $vendor = Vendor::where('user_id', Auth::id())->first();
        
        if (!$vendor) {
            return response()->json(['success' => false, 'message' => 'Vendor not found.'], 404);
        }

        try {
            $vendor->update([
                'is_live' => $request->boolean('is_live')
            ]);

            return response()->json([
                'success' => true, 
                'message' => 'Store status updated successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Failed to update store status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function uploadBanner(Request $request)
    {
        $vendor = Vendor::where('user_id', Auth::id())->first();
        
        if (!$vendor) {
            return response()->json(['success' => false, 'message' => 'Vendor not found.'], 404);
        }

        $request->validate([
            'banner' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            if ($request->hasFile('banner')) {
                // Delete old banner if exists
                if ($vendor->banner_url && Storage::disk('public')->exists($vendor->banner_url)) {
                    Storage::disk('public')->delete($vendor->banner_url);
                }
                
                $banner = $request->file('banner');
                $bannerPath = $banner->store('vendor-banners', 'public');
                
                $vendor->update([
                    'banner_url' => $bannerPath
                ]);
            }

            return response()->json([
                'success' => true, 
                'message' => 'Banner uploaded successfully.',
                'banner_url' => $vendor->banner_url ? asset('storage/' . $vendor->banner_url) : null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Failed to upload banner: ' . $e->getMessage()
            ], 500);
        }
    }

    public function settings()
    {
        $vendor = Vendor::where('user_id', Auth::id())->first();
        
        if (!$vendor) {
            return redirect()->route('auth.login')->with('error', 'Vendor profile not found.');
        }

        return view('vendor.settings', compact('vendor'));
    }

    public function updateSettings(Request $request)
{
    $vendor = Vendor::where('user_id', Auth::id())->first();
    
    if (!$vendor) {
        return response()->json(['success' => false, 'message' => 'Vendor not found.'], 404);
    }

    try {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'business_description' => 'nullable|string|max:1000',
            'business_hours' => 'nullable|string|max:100',
            'delivery_available' => 'boolean',
            'farm_name' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:100',
            'complete_address' => 'nullable|string|max:500',
            'farm_size' => 'nullable|numeric|min:0',
            'years_in_operation' => 'nullable|integer|min:0',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Added logo validation
        ]);

        // Handle banner upload
        if ($request->hasFile('banner')) {
            // Delete old banner if exists
            if ($vendor->banner_url && Storage::disk('public')->exists($vendor->banner_url)) {
                Storage::disk('public')->delete($vendor->banner_url);
            }
            
            $banner = $request->file('banner');
            $bannerPath = $banner->store('vendor-banners', 'public');
            $validated['banner_url'] = $bannerPath;
        }
        
        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($vendor->logo_url && Storage::disk('public')->exists($vendor->logo_url)) {
                Storage::disk('public')->delete($vendor->logo_url);
            }
            
            $logo = $request->file('logo');
            $logoPath = $logo->store('vendor-logos', 'public');
            $validated['logo_url'] = $logoPath;
        }

        // Update vendor with validated data
        $vendor->update($validated);

        return response()->json([
            'success' => true, 
            'message' => 'Store settings updated successfully.',
            'banner_url' => $vendor->banner_url ? asset('storage/' . $vendor->banner_url) : null,
            'logo_url' => $vendor->logo_url ? asset('storage/' . $vendor->logo_url) : null, // Return logo URL too
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false, 
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false, 
            'message' => 'Failed to update settings: ' . $e->getMessage()
        ], 500);
    }
}
}