<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function postSignin(Request $request)
    {
        $rules = [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ];

        $messages = [
            'email.required' => 'Email is required.',
            'email.email' => 'Please enter a valid email address.',
            'password.required' => 'Password is required.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $credentials = $request->only('email', 'password');

        // First check if user exists and is active
        $user = User::where('email', $credentials['email'])->first();
        
        if ($user && !$user->is_active) {
            return redirect()->back()
                ->withErrors(['email' => 'Your account has been deactivated. Please contact the administrator.'])
                ->withInput();
        }

        if (Auth::attempt($credentials, $request->has('remember'))) {
            $request->session()->regenerate();
            
            // Redirect based on user role
            $user = Auth::user();
            if ($user->role === 'vendor') {
                return redirect()->intended(route('vendor.dashboard'))->with('success', 'Login successful.');
            } elseif ($user->role === 'administrator') {
                return redirect()->intended(route('admin.dashboard.index'))->with('success', 'Login successful.');
            } else {
                return redirect()->intended('/')->with('success', 'Login successful.');
            }
        }

        return redirect()->back()
            ->withErrors(['email' => 'Invalid email or password.'])
            ->withInput();
    }

    public function showRegister()
    {
        // Customer registration form only
        return view('auth.register');
    }

    /**
     * Handle customer registration.
     */
    public function register(Request $request)
    {
        $rules = [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'address_line' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z\s\-\.]+$/'],
            'province' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z\s\-\.]+$/'],
            'postal_code' => ['required', 'digits:4', 'regex:/^[0-9]{4}$/'],
            'password' => ['required', 'min:8', 'confirmed'],
        ];

        $messages = [
            'first_name.required' => 'First name is required.',
            'first_name.max' => 'First name may not be greater than 100 characters.',
            'last_name.required' => 'Last name is required.',
            'last_name.max' => 'Last name may not be greater than 100 characters.',
            'email.required' => 'Email is required.',
            'email.unique' => 'This email is already registered.',
            'phone.required' => 'Phone number is required.',
            'phone.max' => 'Phone number may not be greater than 20 characters.',
            'address_line.required' => 'Address line is required.',
            'address_line.max' => 'Address line may not be greater than 255 characters.',
            'city.required' => 'City is required.',
            'city.max' => 'City may not be greater than 100 characters.',
            'city.regex' => 'City may only contain letters, spaces, hyphens, and periods.',
            'province.required' => 'Province is required.',
            'province.max' => 'Province may not be greater than 100 characters.',
            'province.regex' => 'Province may only contain letters, spaces, hyphens, and periods.',
            'postal_code.required' => 'Postal code is required.',
            'postal_code.digits' => 'Postal code must be exactly 4 digits.',
            'postal_code.regex' => 'Postal code must contain only numbers.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = null;
        $customer = null;
        $fullAddress = trim($request->address_line . ', ' . $request->city . ', ' . $request->province . ' ' . $request->postal_code);

        DB::transaction(function () use ($request, &$user, &$customer, $fullAddress) {
            $user = new User();
            $user->email = trim($request->email);
            $user->password = Hash::make($request->password);
            $user->role = 'customer';
            $user->is_active = true;
            $user->save();

            $customer = Customer::create([
                'user_id' => $user->id,
                'first_name' => trim($request->first_name),
                'last_name' => trim($request->last_name),
                'phone' => trim($request->phone),
                'delivery_address' => $fullAddress,
            ]);

            DB::table('customer_addresses')
                ->where('customer_id', $customer->id)
                ->update(['is_default' => false]);

            DB::table('customer_addresses')->insert([
                'customer_id' => $customer->id,
                'address_line' => trim($request->address_line),
                'city' => trim($request->city),
                'province' => trim($request->province),
                'postal_code' => trim($request->postal_code),
                'recipient_name' => trim($request->first_name . ' ' . $request->last_name),
                'recipient_phone' => trim($request->phone),
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        Auth::login($user);

        return redirect()->route('home')->with('success', 'Welcome! Your customer account has been created.');
    }

    /**
     * Show vendor application form.
     */
    public function showVendorRegister()
    {
        return view('auth.vendor-register');
    }

    /**
     * Handle vendor application (requires admin approval).
     */
    public function registerVendor(Request $request)
    {
        $rules = [
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8', 'confirmed'],
            'business_name' => ['required', 'string', 'max:255'],
            'owner_name' => ['required', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
            'business_description' => ['nullable', 'string', 'max:1000'],
        ];

        $messages = [
            'email.required' => 'Email is required.',
            'email.unique' => 'This email is already registered.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            'business_name.required' => 'Business name is required.',
            'owner_name.required' => 'Owner name is required.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create user with vendor role but inactive until approved
        $user = new User();
        $user->email = trim($request->email);
        $user->password = Hash::make($request->password);
        $user->role = 'vendor';
        $user->is_active = false; // will be activated by admin after verification
        $user->save();

        // Create vendor profile in pending status
        \App\Models\Vendor::create([
            'user_id' => $user->id,
            'business_name' => trim($request->business_name),
            'owner_name' => trim($request->owner_name),
            'contact_phone' => $request->contact_phone,
            'business_description' => $request->business_description,
            'weekend_pickup_enabled' => (bool) $request->get('weekend_pickup_enabled', true),
            'weekday_delivery_enabled' => (bool) $request->get('weekday_delivery_enabled', false),
            'weekend_delivery_enabled' => (bool) $request->get('weekend_delivery_enabled', false),
        ]);

        return redirect()->route('auth.login')
            ->with('success', 'Your vendor application has been submitted. An administrator will review and activate your account.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Logged out successfully.');
    }
}

