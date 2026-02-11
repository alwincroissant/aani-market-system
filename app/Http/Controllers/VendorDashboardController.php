<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
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
                $banner = $request->file('banner');
                $bannerPath = $banner->store('vendor-banners', 'public');
                
                $vendor->update([
                    'banner_image' => $bannerPath
                ]);
            }

            return response()->json([
                'success' => true, 
                'message' => 'Banner uploaded successfully.',
                'banner_url' => $vendor->banner_image ? asset('storage/' . $vendor->banner_image) : null
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

        $request->validate([
            'store_name' => 'required|string|max:255',
            'contact_number' => 'nullable|string|max:20',
            'store_description' => 'nullable|string|max:1000',
            'business_hours' => 'nullable|string|max:100',
            'delivery_available' => 'boolean',
            'farm_name' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:100',
            'complete_address' => 'nullable|string|max:500',
            'farm_size' => 'nullable|numeric|min:0',
            'years_in_operation' => 'nullable|integer|min:0',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            $updateData = [
                'store_name' => $request->store_name,
                'contact_number' => $request->contact_number,
                'store_description' => $request->store_description,
                'business_hours' => $request->business_hours,
                'delivery_available' => $request->boolean('delivery_available'),
                'farm_name' => $request->farm_name,
                'region' => $request->region,
                'complete_address' => $request->complete_address,
                'farm_size' => $request->farm_size,
                'years_in_operation' => $request->years_in_operation,
            ];

            // Handle banner upload
            if ($request->hasFile('banner')) {
                $banner = $request->file('banner');
                $bannerPath = $banner->store('vendor-banners', 'public');
                $updateData['banner_image'] = $bannerPath;
            }

            $vendor->update($updateData);

            return response()->json([
                'success' => true, 
                'message' => 'Store settings updated successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Failed to update settings: ' . $e->getMessage()
            ], 500);
        }
    }
}
