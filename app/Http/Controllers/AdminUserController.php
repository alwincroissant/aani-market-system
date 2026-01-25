<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\Validator;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('users')
            ->select('id', 'email', 'role', 'is_active', 'created_at');

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Search by email (since there's no name column)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('email', 'like', "%{$search}%");
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:administrator,vendor,customer'],
        ];

        $messages = [
            'email.required' => 'Email is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email is already registered.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            'role.required' => 'Role is required.',
            'role.in' => 'Invalid role selected.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Create user
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'is_active' => true,
            ]);

            // If role is vendor, create blank vendor profile
            if ($request->role === 'vendor') {
                Vendor::create([
                    'user_id' => $user->id,
                    'business_name' => '',
                    'owner_name' => '',
                    'weekend_pickup_enabled' => true,
                    'weekday_delivery_enabled' => false,
                    'weekend_delivery_enabled' => false,
                ]);
            }

            return redirect()->route('admin.users.index')
                ->with('success', 'User created successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to create user: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function edit($id)
    {
        $user = DB::table('users')
            ->where('id', $id)
            ->first();

        if (!$user) {
            return redirect()->route('admin.users.index')
                ->with('error', 'User not found.');
        }

        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return redirect()->route('admin.users.index')
                ->with('error', 'User not found.');
        }

        $rules = [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $id],
            'role' => ['required', 'in:administrator,vendor,customer'],
        ];

        $messages = [
            'email.required' => 'Email is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email is already registered.',
            'role.required' => 'Role is required.',
            'role.in' => 'Invalid role selected.',
        ];

        // Add password validation only if password is provided
        if ($request->filled('password')) {
            $rules['password'] = ['string', 'min:8', 'confirmed'];
            $messages['password.min'] = 'Password must be at least 8 characters.';
            $messages['password.confirmed'] = 'Password confirmation does not match.';
        }

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Update user
            $updateData = [
                'email' => $request->email,
                'role' => $request->role,
            ];

            // Update password only if provided
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            // Handle vendor profile creation if role changed to vendor
            if ($request->role === 'vendor') {
                $existingVendor = Vendor::where('user_id', $id)->first();
                if (!$existingVendor) {
                    Vendor::create([
                        'user_id' => $user->id,
                        'business_name' => '',
                        'owner_name' => '',
                        'weekend_pickup_enabled' => true,
                        'weekday_delivery_enabled' => false,
                        'weekend_delivery_enabled' => false,
                    ]);
                }
            }

            return redirect()->route('admin.users.index')
                ->with('success', 'User updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update user: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return redirect()->route('admin.users.index')
                ->with('error', 'User not found.');
        }

        try {
            $user->delete();

            return redirect()->route('admin.users.index')
                ->with('success', 'User deleted successfully.');

        } catch (\Exception $e) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }
}
