<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Vendor;

class VendorProfileController extends Controller
{
    public function index()
    {
        $vendor = DB::table('vendors')
            ->where('user_id', Auth::id())
            ->first();

        $user = DB::table('users')
            ->where('id', Auth::id())
            ->first();

        return view('vendor.profile.index', compact('vendor', 'user'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'business_name'        => 'required|string|max:255',
            'business_description' => 'nullable|string|max:1000',
            'vendor_bio'           => 'nullable|string|max:2000',
            'phone'                => 'nullable|string|max:20',
            'email'                => 'required|email|unique:users,email,' . Auth::id(),
            'logo_url'             => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'banner_url'           => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            'remove_logo'          => 'nullable|boolean',
            'remove_banner'        => 'nullable|boolean',
        ]);

        $vendor = DB::table('vendors')->where('user_id', Auth::id())->first();
        if (!$vendor) {
            return back()->withErrors(['Vendor not found']);
        }

        DB::table('users')
            ->where('id', Auth::id())
            ->update([
                'email'      => $request->email,
                'updated_at' => now(),
            ]);

        $updateData = [
            'business_name'        => $request->business_name,
            'business_description' => $request->business_description,
            'vendor_bio'           => $request->vendor_bio,
            'contact_phone'        => $request->phone,
            'updated_at'           => now(),
        ];

        // Handle banner removal
        if ($request->remove_banner && $vendor->banner_url) {
            if (Storage::disk('public')->exists($vendor->banner_url)) {
                Storage::disk('public')->delete($vendor->banner_url);
            }
            $updateData['banner_url'] = null;
        }

        // Handle logo removal
        if ($request->remove_logo && $vendor->logo_url) {
            if (Storage::disk('public')->exists($vendor->logo_url)) {
                Storage::disk('public')->delete($vendor->logo_url);
            }
            $updateData['logo_url'] = null;
        }

        // Handle new banner upload (skipped if remove was also requested)
        if ($request->hasFile('banner_url') && !$request->remove_banner) {
            if ($vendor->banner_url && Storage::disk('public')->exists($vendor->banner_url)) {
                Storage::disk('public')->delete($vendor->banner_url);
            }
            $updateData['banner_url'] = $request->file('banner_url')->store('vendor-banners', 'public');
        }

        // Handle new logo upload (skipped if remove was also requested)
        if ($request->hasFile('logo_url') && !$request->remove_logo) {
            if ($vendor->logo_url && Storage::disk('public')->exists($vendor->logo_url)) {
                Storage::disk('public')->delete($vendor->logo_url);
            }
            $updateData['logo_url'] = $request->file('logo_url')->store('vendor-logos', 'public');
        }

        DB::table('vendors')->where('user_id', Auth::id())->update($updateData);

        return redirect()->route('vendor.profile.index')->with('success', 'Vendor profile updated successfully!');
    }

    /**
     * Remove the vendor's banner image from storage and null the DB column.
     * Called via AJAX POST from the Store Settings page.
     */
    public function removeBanner()
    {
        try {
            // Use Eloquent to match how VendorDashboardController handles vendors
            $vendor = Vendor::where('user_id', Auth::id())->first();

            if (!$vendor) {
                return response()->json(['message' => 'Vendor not found'], 404);
            }

            if ($vendor->banner_url) {
                if (Storage::disk('public')->exists($vendor->banner_url)) {
                    Storage::disk('public')->delete($vendor->banner_url);
                }

                $vendor->update(['banner_url' => null]);
            }

            return response()->json(['message' => 'Banner removed successfully']);

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
            // Use Eloquent to match how VendorDashboardController handles vendors
            $vendor = Vendor::where('user_id', Auth::id())->first();

            if (!$vendor) {
                return response()->json(['message' => 'Vendor not found'], 404);
            }

            if ($vendor->logo_url) {
                if (Storage::disk('public')->exists($vendor->logo_url)) {
                    Storage::disk('public')->delete($vendor->logo_url);
                }

                $vendor->update(['logo_url' => null]);
            }

            return response()->json(['message' => 'Logo removed successfully']);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to remove logo: ' . $e->getMessage()
            ], 500);
        }
    }
}