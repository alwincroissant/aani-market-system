<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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
            'business_name' => 'required|string|max:255',
            'business_description' => 'nullable|string|max:1000',
            'vendor_bio' => 'nullable|string|max:2000',
            'phone' => 'nullable|string|max:20',
            'email' => 'required|email|unique:users,email,' . Auth::id(),
            'logo_url' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'banner_url' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
        ]);

        // Handle logo upload
        $logoPath = null;
        if ($request->hasFile('logo_url')) {
            $file = $request->file('logo_url');
            $filename = time() . '_logo_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/vendor_logos'), $filename);
            $logoPath = 'uploads/vendor_logos/' . $filename;
        }

        // Handle banner upload
        $bannerPath = null;
        if ($request->hasFile('banner_url')) {
            $file = $request->file('banner_url');
            $filename = time() . '_banner_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/vendor_banners'), $filename);
            $bannerPath = 'uploads/vendor_banners/' . $filename;
        }

        // Update users table
        DB::table('users')
            ->where('id', Auth::id())
            ->update([
                'email' => $request->email,
                'updated_at' => now(),
            ]);

        // Update vendor record
        $vendor = DB::table('vendors')
            ->where('user_id', Auth::id())
            ->first();

        if ($vendor) {
            $updateData = [
                'business_name' => $request->business_name,
                'description' => $request->business_description,
                'vendor_bio' => $request->vendor_bio,
                'phone' => $request->phone,
                'updated_at' => now(),
            ];

            if ($logoPath) {
                $updateData['logo_url'] = $logoPath;
            }

            if ($bannerPath) {
                $updateData['banner_url'] = $bannerPath;
            }

            DB::table('vendors')
                ->where('user_id', Auth::id())
                ->update($updateData);
        }

        return redirect()->route('vendor.profile.index')->with('success', 'Vendor profile updated successfully!');
    }
}
