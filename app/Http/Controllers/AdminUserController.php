<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Stall;
use Illuminate\Support\Facades\Validator;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('users')
            ->select('id', 'email', 'role', 'is_active', 'created_at');

        // Filter by status (default to active users)
        $status = $request->input('status', 'active');
        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        } elseif ($status === 'pending') {
            // Show inactive vendors who don't have stall assignments
            $query->where('users.role', 'vendor')
                  ->where('users.is_active', false)
                  ->leftJoin('vendors', 'users.id', '=', 'vendors.user_id')
                  ->leftJoin('stall_assignments', 'vendors.id', '=', 'stall_assignments.vendor_id')
                  ->whereNull('stall_assignments.vendor_id');
        }

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

        // Get count of pending vendors for notification badge
        $pendingVendorsCount = DB::table('users')
            ->join('vendors', 'users.id', '=', 'vendors.user_id')
            ->leftJoin('stall_assignments', 'vendors.id', '=', 'stall_assignments.vendor_id')
            ->where('users.role', 'vendor')
            ->where('users.is_active', false)
            ->whereNull('stall_assignments.vendor_id')
            ->count();

        // Get map image for stall assignment modal
        $mapImage = DB::table('system_settings')
            ->where('setting_key', 'market_map_image')
            ->value('setting_value');

        return view('admin.users.index', compact('users', 'pendingVendorsCount', 'mapImage'));
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
            'role' => ['required', 'in:administrator,pickup_manager,vendor,customer'],
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
            'role' => ['required', 'in:administrator,pickup_manager,vendor,customer'],
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

    public function activate($id)
    {
        $user = User::find($id);
        if (!$user) {
            return redirect()->route('admin.users.index')
                ->with('error', 'User not found.');
        }

        try {
            $user->update(['is_active' => true]);
            
            return redirect()->route('admin.users.index')
                ->with('success', 'User activated successfully.');
        } catch (\Exception $e) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Failed to activate user: ' . $e->getMessage());
        }
    }

    public function deactivate($id)
    {
        $user = User::find($id);
        if (!$user) {
            return redirect()->route('admin.users.index')
                ->with('error', 'User not found.');
        }

        // Prevent deactivating the currently logged-in admin
        if ($user->id === auth()->user()->id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot deactivate your own account.');
        }

        try {
            $user->update(['is_active' => false]);
            
            return redirect()->route('admin.users.index')
                ->with('success', 'User deactivated successfully.');
        } catch (\Exception $e) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Failed to deactivate user: ' . $e->getMessage());
        }
    }

    public function assignStallAndActivate(Request $request)
    {
        $rules = [
            'user_id' => ['required', 'exists:users,id'],
            'stall_number' => ['required', 'string', 'max:20'],
            'section_id' => ['required', 'exists:market_sections,id'],
            'x1' => ['nullable', 'numeric'],
            'y1' => ['nullable', 'numeric'],
            'x2' => ['nullable', 'numeric'],
            'y2' => ['nullable', 'numeric'],
        ];

        $messages = [
            'user_id.required' => 'User ID is required.',
            'user_id.exists' => 'User not found.',
            'stall_number.required' => 'Stall number is required.',
            'section_id.required' => 'Section is required.',
            'section_id.exists' => 'Selected section is invalid.',
            'x1.numeric' => 'X coordinate must be a number.',
            'y1.numeric' => 'Y coordinate must be a number.',
            'x2.numeric' => 'X coordinate must be a number.',
            'y2.numeric' => 'Y coordinate must be a number.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            // Get user and vendor
            $user = User::find($request->user_id);
            $vendor = Vendor::where('user_id', $request->user_id)->first();

            if (!$user || !$vendor) {
                return response()->json(['success' => false, 'message' => 'User or vendor not found.'], 404);
            }

            // Check if coordinates are provided (new stall) or stall_number only (existing stall)
            if ($request->filled('x1') && $request->filled('y1') && $request->filled('x2') && $request->filled('y2')) {
                // Create new stall
                $centerX = ($request->x1 + $request->x2) / 2;
                $centerY = ($request->y1 + $request->y2) / 2;

                $stall = new \App\Models\Stall();
                $stall->stall_number = trim($request->stall_number);
                $stall->section_id = $request->section_id;
                $stall->position_x = $centerX;
                $stall->position_y = $centerY;
                $stall->x1 = min($request->x1, $request->x2);
                $stall->y1 = min($request->y1, $request->y2);
                $stall->x2 = max($request->x1, $request->x2);
                $stall->y2 = max($request->y1, $request->y2);
                $stall->map_coordinates_json = json_encode([
                    'x1' => $stall->x1, 'y1' => $stall->y1, 
                    'x2' => $stall->x2, 'y2' => $stall->y2
                ]);
                $stall->status = 'occupied';
                $stall->save();

                // Create stall assignment
                DB::table('stall_assignments')->insert([
                    'stall_id' => $stall->id,
                    'vendor_id' => $vendor->id,
                    'assigned_date' => now()->toDateString(),
                    'end_date' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                // Assign to existing stall
                $stall = \App\Models\Stall::where('stall_number', $request->stall_number)
                    ->where('section_id', $request->section_id)
                    ->where('status', 'available')
                    ->first();

                if (!$stall) {
                    return response()->json(['success' => false, 'message' => 'Stall not found or not available.'], 404);
                }

                // Update stall status
                $stall->status = 'occupied';
                $stall->save();

                // Create stall assignment
                DB::table('stall_assignments')->insert([
                    'stall_id' => $stall->id,
                    'vendor_id' => $vendor->id,
                    'assigned_date' => now()->toDateString(),
                    'end_date' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Activate user
            $user->update(['is_active' => true]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Vendor has been assigned a stall and activated successfully.',
                'stall' => [
                    'id' => $stall->id,
                    'stall_number' => $stall->stall_number,
                    'section_id' => $stall->section_id,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false, 
                'message' => 'Failed to assign stall: ' . $e->getMessage()
            ], 500);
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
