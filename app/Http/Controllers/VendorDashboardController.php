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
            return redirect()->route('login')->with('error', 'Vendor profile not found.');
        }

        // Get today's sales
        $todaySales = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.vendor_id', $vendor->id)
            ->whereDate('orders.created_at', now()->toDateString())
            ->sum('order_items.quantity * order_items.price');

        // Get pending orders
        $pendingOrders = DB::table('orders')
            ->where('vendor_id', $vendor->id)
            ->where('status', 'pending')
            ->count();

        // Get low stock products
        $lowStockProducts = DB::table('products')
            ->where('vendor_id', $vendor->id)
            ->where('stock_level', '<', 10)
            ->count();

        // Get weekly sales data
        $weeklySales = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $daySales = DB::table('orders')
                ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->where('orders.vendor_id', $vendor->id)
                ->whereDate('orders.created_at', $date->toDateString())
                ->sum('order_items.quantity * order_items.price');
            
            $weeklySales[] = $daySales;
        }
        $weeklySales = array_reverse($weeklySales);

        return view('vendor.dashboard', compact('vendor', 'todaySales', 'pendingOrders', 'lowStockProducts', 'weeklySales'));
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
}
