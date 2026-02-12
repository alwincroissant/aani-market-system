<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Vendor;

class VendorOrderController extends Controller
{
    public function index(Request $request)
    {
        $vendor = Vendor::where('user_id', Auth::id())->first();
        
        if (!$vendor) {
            return redirect()->route('auth.login')->with('error', 'Vendor profile not found.');
        }

        // Get vendor's orders with their items only
        $query = DB::table('orders as o')
            ->join('customers as c', 'o.customer_id', '=', 'c.id')
            ->join('users as u', 'c.user_id', '=', 'u.id')
            ->join('order_items as oi', 'o.id', '=', 'oi.order_id')
            ->where('oi.vendor_id', $vendor->id)
            ->orderBy('o.order_date', 'desc')
            ->select(
                'o.id',
                'o.order_reference',
                'o.customer_id',
                'o.order_date',
                'o.fulfillment_type',
                'o.order_status',
                'o.delivery_address',
                'o.pickup_code',
                'o.notes',
                'o.created_at',
                'o.updated_at',
                'c.first_name',
                'c.last_name',
                'c.phone',
                'u.email',
                'oi.item_status',
                DB::raw('COUNT(oi.id) as item_count'),
                DB::raw('SUM(oi.quantity * oi.unit_price) as total_amount')
            )
            ->groupBy(
                'o.id',
                'o.order_reference',
                'o.customer_id',
                'o.order_date',
                'o.fulfillment_type',
                'o.order_status',
                'o.delivery_address',
                'o.pickup_code',
                'o.notes',
                'o.created_at',
                'o.updated_at',
                'c.first_name',
                'c.last_name',
                'c.phone',
                'u.email',
                'oi.item_status'
            );

        // Apply filters
        if ($request->filled('status')) {
            $query->where('o.order_status', $request->status);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('o.order_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('order_date', '<=', $request->end_date);
        }

        $orders = $query->get();

        return view('vendor.orders.index', compact('orders'));
    }

    public function show($id)
    {
        $vendor = Vendor::where('user_id', Auth::id())->first();
        
        if (!$vendor) {
            return redirect()->route('auth.login')->with('error', 'Vendor profile not found.');
        }

        // Get specific order with vendor's items only
        $order = DB::table('orders as o')
            ->join('customers as c', 'o.customer_id', '=', 'c.id')
            ->join('users as u', 'c.user_id', '=', 'u.id')
            ->join('order_items as oi', 'o.id', '=', 'oi.order_id')
            ->join('products as p', 'oi.product_id', '=', 'p.id')
            ->where('o.id', $id)
            ->where('oi.vendor_id', $vendor->id)
            ->select(
                'o.*',
                'c.first_name',
                'c.last_name',
                'c.phone',
                'u.email',
                'c.address',
                'oi.id as item_id',
                'oi.quantity',
                'oi.unit_price',
                'oi.item_status',
                'oi.vendor_notes',
                'p.product_name',
                'p.description',
                'p.product_image_url',
                'p.unit_type'
            )
            ->orderBy('oi.id')
            ->get();

        if ($order->isEmpty()) {
            return redirect()->route('vendor.orders.index')->with('error', 'Order not found or not accessible.');
        }

        return view('vendor.orders.show', compact('order'));
    }

    public function updateItemStatus(Request $request, $itemId)
    {
        $vendor = Vendor::where('user_id', Auth::id())->first();
        
        if (!$vendor) {
            return response()->json(['success' => false, 'message' => 'Vendor not found.'], 404);
        }

        $request->validate([
            'item_status' => 'required|in:pending,confirmed,ready,completed,cancelled'
        ]);

        // Verify this order item belongs to the vendor
        $orderItem = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('order_items.id', $itemId)
            ->where('order_items.vendor_id', $vendor->id)
            ->first();

        if (!$orderItem) {
            return response()->json(['success' => false, 'message' => 'Order item not found or not accessible.'], 404);
        }

        try {
            // Update item status
            DB::table('order_items')
                ->where('id', $itemId)
                ->update([
                    'item_status' => $request->item_status,
                    'updated_at' => now()
                ]);

            // Check if all items in this order have the same status
            $allItems = DB::table('order_items')
                ->where('order_id', $orderItem->order_id)
                ->get();

            $allSameStatus = $allItems->every(function ($item) use ($request) {
                    return $item->item_status === $request->item_status;
                });

            // If all items have the same status, update the main order status too
            if ($allSameStatus) {
                DB::table('orders')
                    ->where('id', $orderItem->order_id)
                    ->update([
                        'order_status' => $request->item_status,
                        'updated_at' => now()
                    ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Order item status updated successfully.',
                'item_status' => $request->item_status
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateVendorNotes(Request $request, $itemId)
    {
        $vendor = Vendor::where('user_id', Auth::id())->first();
        
        if (!$vendor) {
            return response()->json(['success' => false, 'message' => 'Vendor not found.'], 404);
        }

        $request->validate([
            'vendor_notes' => 'nullable|string|max:500'
        ]);

        // Verify this order item belongs to the vendor
        $orderItem = DB::table('order_items')
            ->where('id', $itemId)
            ->where('vendor_id', $vendor->id)
            ->first();

        if (!$orderItem) {
            return response()->json(['success' => false, 'message' => 'Order item not found or not accessible.'], 404);
        }

        try {
            DB::table('order_items')
                ->where('id', $itemId)
                ->update([
                    'vendor_notes' => $request->vendor_notes,
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Vendor notes updated successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Failed to update notes: ' . $e->getMessage()
            ], 500);
        }
    }

    public function generatePickupCode($orderId)
    {
        $vendor = Vendor::where('user_id', Auth::id())->first();
        
        if (!$vendor) {
            return response()->json(['success' => false, 'message' => 'Vendor not found.'], 404);
        }

        // Verify this order has vendor's items
        $hasVendorItems = DB::table('order_items')
            ->where('order_id', $orderId)
            ->where('vendor_id', $vendor->id)
            ->exists();

        if (!$hasVendorItems) {
            return response()->json(['success' => false, 'message' => 'No vendor items in this order.'], 404);
        }

        try {
            // Check if pickup code already exists
            $existingCode = DB::table('pickup_codes')
                ->where('order_id', $orderId)
                ->first();

            if (!$existingCode) {
                // Generate pickup code
                $pickupCode = strtoupper(substr(md5(uniqid()), 0, 6));
                
                // Store pickup code
                DB::table('pickup_codes')->insert([
                    'order_id' => $orderId,
                    'code' => $pickupCode,
                    'expires_at' => now()->addDays(7),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // Update order with pickup code
                DB::table('orders')
                    ->where('id', $orderId)
                    ->update(['pickup_code' => $pickupCode]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pickup code generated successfully.',
                'pickup_code' => $existingCode->code ?? $pickupCode
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Failed to generate pickup code: ' . $e->getMessage()
            ], 500);
        }
    }
}
