<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        $customer = DB::table('customers')
            ->where('user_id', Auth::id())
            ->first();

        $user = DB::table('users')
            ->where('id', Auth::id())
            ->first();

        return view('profile.index', compact('customer', 'user'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'required|email|unique:users,email,' . Auth::id(),
            'bio' => 'nullable|string|max:1000',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle profile picture upload
        $profilePicturePath = null;
        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/profile_pictures'), $filename);
            $profilePicturePath = 'uploads/profile_pictures/' . $filename;
        }

        // Update users table
        DB::table('users')
            ->where('id', Auth::id())
            ->update([
                'email' => $request->email,
                'bio' => $request->bio,
                'updated_at' => now(),
            ]);

        // Update profile picture if uploaded
        if ($profilePicturePath) {
            DB::table('users')
                ->where('id', Auth::id())
                ->update(['profile_picture' => $profilePicturePath]);
        }

        // Update or create customer record
        $customer = DB::table('customers')
            ->where('user_id', Auth::id())
            ->first();

        if ($customer) {
            DB::table('customers')
                ->where('user_id', Auth::id())
                ->update([
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'phone' => $request->phone,
                    'updated_at' => now(),
                ]);
                
            // Update all addresses with new recipient info
            DB::table('customer_addresses')
                ->where('customer_id', $customer->id)
                ->update([
                    'recipient_name' => $request->first_name . ' ' . $request->last_name,
                    'recipient_phone' => $request->phone,
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('customers')->insert([
                'user_id' => Auth::id(),
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->route('profile.index')->with('success', 'Profile updated successfully!');
    }

    public function addresses()
    {
        $addresses = DB::table('customer_addresses')
            ->where('customer_id', DB::raw('(SELECT id FROM customers WHERE user_id = ' . Auth::id() . ')'))
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('profile.addresses', compact('addresses'));
    }

    public function storeAddress(Request $request)
    {
        // Debug: Log the raw request data
        \Log::info('Store address request data:', $request->all());
        
        // Remove boolean validation for now and handle manually
        $request->validate([
            'address_line' => 'required|string|max:255',
            'city' => 'required|string|max:100|regex:/^[a-zA-Z\s\-\.]+$/',
            'province' => 'required|string|max:100|regex:/^[a-zA-Z\s\-\.]+$/',
            'postal_code' => 'nullable|digits:4|regex:/^[0-9]{4}$/',
        ], [
            'address_line.required' => 'Address line is required.',
            'address_line.max' => 'Address line may not be greater than 255 characters.',
            'city.required' => 'City is required.',
            'city.max' => 'City may not be greater than 100 characters.',
            'city.regex' => 'City may only contain letters, spaces, hyphens, and periods.',
            'province.required' => 'Province is required.',
            'province.max' => 'Province may not be greater than 100 characters.',
            'province.regex' => 'Province may only contain letters, spaces, hyphens, and periods.',
            'postal_code.digits' => 'Postal code must be exactly 4 digits.',
            'postal_code.regex' => 'Postal code must contain only numbers.',
        ]);

        $customer = DB::table('customers')
            ->where('user_id', Auth::id())
            ->first();

        if (!$customer) {
            // Create customer record if it doesn't exist
            $customerId = DB::table('customers')->insertGetId([
                'user_id' => Auth::id(),
                'first_name' => auth()->user()->name ?? 'First Name',
                'last_name' => 'Last Name',
                'phone' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $customer = DB::table('customers')->where('id', $customerId)->first();
        }

        // Handle is_default manually - convert checkbox to boolean
        $isDefault = $request->has('is_default') && $request->input('is_default') === '1';
        
        // Debug: Log the boolean value
        \Log::info('Is default value:', ['is_default' => $isDefault, 'raw_input' => $request->input('is_default'), 'has_is_default' => $request->has('is_default')]);

        // If this is set as default, unset other default addresses
        if ($isDefault) {
            DB::table('customer_addresses')
                ->where('customer_id', $customer->id)
                ->update(['is_default' => false]);
        }

        DB::table('customer_addresses')->insert([
            'customer_id' => $customer->id,
            'address_line' => $request->address_line,
            'city' => $request->city,
            'province' => $request->province,
            'postal_code' => $request->postal_code,
            'recipient_name' => $customer->first_name . ' ' . $customer->last_name,
            'recipient_phone' => $customer->phone,
            'is_default' => $isDefault,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('profile.addresses')->with('success', 'Address added successfully!');
    }

    public function updateAddress(Request $request, $id)
    {
        $request->validate([
            'address_line' => 'required|string|max:255',
            'city' => 'required|string|max:100|regex:/^[a-zA-Z\s\-\.]+$/',
            'province' => 'required|string|max:100|regex:/^[a-zA-Z\s\-\.]+$/',
            'postal_code' => 'nullable|digits:4|regex:/^[0-9]{4}$/',
            'is_default' => 'nullable|boolean',
        ], [
            'address_line.required' => 'Address line is required.',
            'address_line.max' => 'Address line may not be greater than 255 characters.',
            'city.required' => 'City is required.',
            'city.max' => 'City may not be greater than 100 characters.',
            'city.regex' => 'City may only contain letters, spaces, hyphens, and periods.',
            'province.required' => 'Province is required.',
            'province.max' => 'Province may not be greater than 100 characters.',
            'province.regex' => 'Province may only contain letters, spaces, hyphens, and periods.',
            'postal_code.digits' => 'Postal code must be exactly 4 digits.',
            'postal_code.regex' => 'Postal code must contain only numbers.',
            'is_default.boolean' => 'Default address field must be true or false.',
        ]);

        $customer = DB::table('customers')
            ->where('user_id', Auth::id())
            ->first();

        if (!$customer) {
            // Create customer record if it doesn't exist
            $customerId = DB::table('customers')->insertGetId([
                'user_id' => Auth::id(),
                'first_name' => auth()->user()->name ?? 'First Name',
                'last_name' => 'Last Name',
                'phone' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $customer = DB::table('customers')->where('id', $customerId)->first();
        }

        $address = DB::table('customer_addresses')
            ->where('id', $id)
            ->where('customer_id', $customer->id)
            ->first();

        if (!$address) {
            return redirect()->route('profile.addresses')->with('error', 'Address not found.');
        }

        // If this is set as default, unset other default addresses
        if ($request->boolean('is_default')) {
            DB::table('customer_addresses')
                ->where('customer_id', $customer->id)
                ->where('id', '!=', $id)
                ->update(['is_default' => false]);
        }

        DB::table('customer_addresses')
            ->where('id', $id)
            ->update([
                'address_line' => $request->address_line,
                'city' => $request->city,
                'province' => $request->province,
                'postal_code' => $request->postal_code,
                'is_default' => $request->boolean('is_default'),
                'updated_at' => now(),
            ]);

        return redirect()->route('profile.addresses')->with('success', 'Address updated successfully!');
    }

    public function deleteAddress($id)
    {
        $customer = DB::table('customers')
            ->where('user_id', Auth::id())
            ->first();

        if (!$customer) {
            // Create customer record if it doesn't exist
            $customerId = DB::table('customers')->insertGetId([
                'user_id' => Auth::id(),
                'first_name' => auth()->user()->name ?? 'First Name',
                'last_name' => 'Last Name',
                'phone' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $customer = DB::table('customers')->where('id', $customerId)->first();
        }

        $address = DB::table('customer_addresses')
            ->where('id', $id)
            ->where('customer_id', $customer->id)
            ->first();

        if (!$address) {
            return redirect()->route('profile.addresses')->with('error', 'Address not found.');
        }

        DB::table('customer_addresses')
            ->where('id', $id)
            ->delete();

        return redirect()->route('profile.addresses')->with('success', 'Address deleted successfully!');
    }

    public function orders()
    {
        return redirect()->route('customer.orders.index');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = DB::table('users')->where('id', Auth::id())->first();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'Current password is incorrect.');
        }

        DB::table('users')
            ->where('id', Auth::id())
            ->update([
                'password' => Hash::make($request->new_password),
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Password changed successfully!');
    }
}
