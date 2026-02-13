<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
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
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8', 'confirmed'],
        ];

        $messages = [
            'email.required' => 'Email is required.',
            'email.unique' => 'This email is already registered.',
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

        $user = new User();
        $user->email = trim($request->email);
        $user->password = Hash::make($request->password);
        $user->role = 'customer';
        $user->is_active = true;
        $user->save();

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

