<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Vendor;
use App\Models\WalkInSale;

class VendorDashboardController extends Controller
{
    public function index()
    {
        $vendor = Vendor::where('user_id', Auth::id())->first();
        
        if (!$vendor) {
            return redirect()->route('auth.login')->with('error', 'Vendor profile not found.');
        }

        // Get today's ONLINE sales
        $todayOnlineSales = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('order_items.vendor_id', $vendor->id)
            ->whereDate('orders.created_at', now()->toDateString())
            ->sum(DB::raw('order_items.quantity * order_items.unit_price'));

        // Get today's PHYSICAL / walk-in sales
        $todayPhysicalSales = WalkInSale::where('vendor_id', $vendor->id)
            ->whereDate('sale_date', now()->toDateString())
            ->sum(DB::raw('quantity * unit_price'));

        // Combined total for backward-compatible variable
        $todaySales = $todayOnlineSales + $todayPhysicalSales;

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

        return view('vendor.dashboard', compact(
            'vendor', 'todaySales', 'todayOnlineSales', 'todayPhysicalSales',
            'pendingOrders', 'lowStockProducts', 'weeklySales', 'topProducts'
        ));
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
                if ($vendor->banner_url && Storage::disk('public')->exists($vendor->banner_url)) {
                    Storage::disk('public')->delete($vendor->banner_url);
                }
                
                $bannerPath = $request->file('banner')->store('vendor-banners', 'public');
                
                $vendor->update(['banner_url' => $bannerPath]);
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

        // Convert checkboxes to booleans based on their VALUE (not just presence)
        // JavaScript sends '1' for checked, '0' for unchecked
        $request->merge([
            'weekend_pickup_enabled' => $request->input('weekend_pickup_enabled') === '1',
            'weekday_delivery_enabled' => $request->input('weekday_delivery_enabled') === '1',
            'weekend_delivery_enabled' => $request->input('weekend_delivery_enabled') === '1',
        ]);

        try {
            $validated = $request->validate([
                'business_name'          => 'required|string|max:255',
                'contact_phone'          => 'nullable|string|max:20',
                'business_description'   => 'nullable|string|max:1000',
                'business_hours'         => 'nullable|string|max:100',
                'weekend_pickup_enabled' => 'boolean',
                'weekday_delivery_enabled' => 'boolean',
                'weekend_delivery_enabled' => 'boolean',
                'banner'                 => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'logo'                   => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Handle banner upload
            if ($request->hasFile('banner')) {
                if ($vendor->banner_url && Storage::disk('public')->exists($vendor->banner_url)) {
                    Storage::disk('public')->delete($vendor->banner_url);
                }
                $bannerFile = $request->file('banner');
                $bannerDestination = storage_path('app/public/vendor-banners');
                $bannerName = time() . '_' . uniqid() . '.' . $bannerFile->getClientOriginalExtension();
                $bannerFile->move($bannerDestination, $bannerName);
                $validated['banner_url'] = 'vendor-banners/' . $bannerName;
            }
            
            // Handle logo upload
            if ($request->hasFile('logo')) {
                if ($vendor->logo_url && Storage::disk('public')->exists($vendor->logo_url)) {
                    Storage::disk('public')->delete($vendor->logo_url);
                }
                $logoFile = $request->file('logo');
                $logoDestination = storage_path('app/public/vendor-logos');
                $logoName = time() . '_' . uniqid() . '.' . $logoFile->getClientOriginalExtension();
                $logoFile->move($logoDestination, $logoName);
                $validated['logo_url'] = 'vendor-logos/' . $logoName;
            }

            // Remove 'banner' and 'logo' keys from validated so Eloquent
            // doesn't try to write them as columns (they don't exist in the table)
            unset($validated['banner'], $validated['logo']);

            $vendor->update($validated);

            return response()->json([
                'success'    => true,
                'message'    => 'Store settings updated successfully.',
                'banner_url' => $vendor->banner_url ? asset('storage/' . $vendor->banner_url) : null,
                'logo_url'   => $vendor->logo_url   ? asset('storage/' . $vendor->logo_url)   : null,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the vendor's banner image from storage and null the DB column.
     * Called via AJAX POST from the Store Settings page.
     */
    public function removeBanner()
    {
        try {
            $vendor = Vendor::where('user_id', Auth::id())->first();

            if (!$vendor) {
                return response()->json(['message' => 'Vendor not found.'], 404);
            }

            if ($vendor->banner_url) {
                if (Storage::disk('public')->exists($vendor->banner_url)) {
                    Storage::disk('public')->delete($vendor->banner_url);
                }
                $vendor->update(['banner_url' => null]);
            }

            return response()->json(['message' => 'Banner removed successfully.']);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to remove banner: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the vendor's logo image from storage and null the DB column.
     * Called via AJAX POST from the Store Settings page.
     */
    public function removeLogo()
    {
        try {
            $vendor = Vendor::where('user_id', Auth::id())->first();

            if (!$vendor) {
                return response()->json(['message' => 'Vendor not found.'], 404);
            }

            if ($vendor->logo_url) {
                if (Storage::disk('public')->exists($vendor->logo_url)) {
                    Storage::disk('public')->delete($vendor->logo_url);
                }
                $vendor->update(['logo_url' => null]);
            }

            return response()->json(['message' => 'Logo removed successfully.']);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to remove logo: ' . $e->getMessage()
            ], 500);
        }
    }
}