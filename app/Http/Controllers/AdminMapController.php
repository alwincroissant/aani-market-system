<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\SystemSetting;
use App\Models\Stall;
use Illuminate\Support\Facades\Validator;

class AdminMapController extends Controller
{
    public function index()
    {
        $mapImage = DB::table('system_settings')
            ->where('setting_key', 'market_map_image')
            ->value('setting_value');

        // Simple check - if we have a path, assume it's valid
        // The asset() helper will handle the rest
        $hasMapImage = !empty($mapImage);
        
        // Debug logging
        \Log::info('Map Image Path: ' . $mapImage);
        \Log::info('Has Map Image: ' . ($hasMapImage ? 'true' : 'false'));

        $stalls = DB::table('stalls as s')
            ->leftJoin('market_sections as ms', 's.section_id', '=', 'ms.id')
            ->leftJoin('stall_assignments as sa', function($join) {
                $join->on('s.id', '=', 'sa.stall_id')
                     ->whereNull('sa.end_date');
            })
            ->leftJoin('vendors as v', 'sa.vendor_id', '=', 'v.id')
            ->whereNull('s.deleted_at')
            ->select(
                's.id',
                's.stall_number',
                's.position_x',
                's.position_y',
                's.x1',
                's.y1', 
                's.x2',
                's.y2',
                's.map_coordinates_json',
                's.status',
                's.section_id',
                'ms.section_name',
                'ms.section_code',
                'v.id as vendor_id',
                'v.business_name'
            )
            ->get();

        $sections = DB::table('market_sections')->get();
        $vendors = DB::table('vendors')
            ->whereNull('deleted_at')
            ->whereNotIn('id', function($query) {
                $query->select('vendor_id')
                    ->from('stall_assignments')
                    ->whereNull('end_date')
                    ->whereIn('stall_id', function($subQuery) {
                        $subQuery->select('id')
                            ->from('stalls')
                            ->whereNull('deleted_at');
                    });
            })
            ->get();

        return view('admin.map.index', compact('mapImage', 'hasMapImage', 'stalls', 'sections', 'vendors'));
    }

    public function uploadBackground(Request $request)
    {
        $rules = [
            'map_image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5120'],
        ];

        $messages = [
            'map_image.required' => 'Please select an image file.',
            'map_image.image' => 'The file must be an image.',
            'map_image.mimes' => 'The image must be a jpeg, png, jpg, or gif file.',
            'map_image.max' => 'The image size must not exceed 5MB.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Generate unique filename
            $name = time() . '_' . uniqid() . '.' . $request->file('map_image')->getClientOriginalExtension();
            
            // Store file using public disk
            $path = $request->file('map_image')->storeAs('maps', $name, 'public');

            // Verify file was stored
            if (!$path || !Storage::disk('public')->exists('maps/' . $name)) {
                return redirect()->back()
                    ->withErrors(['map_image' => 'Failed to upload image. Please try again.'])
                    ->withInput();
            }

            // Use the correct path format for asset() helper
            $imagePath = 'storage/maps/' . $name;

            // Save to database
            SystemSetting::updateOrCreate(
                ['setting_key' => 'market_map_image'],
                ['setting_value' => $imagePath, 'description' => 'Market floor plan background image']
            );

            return redirect()->route('admin.map.index')->with('success', 'Map image uploaded successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['map_image' => 'Upload failed: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function storeStall(Request $request)
    {
        $rules = [
            'stall_number' => ['required', 'string', 'max:20', 'unique:stalls,stall_number'],
            'section_id' => ['required', 'exists:market_sections,id'],
            'vendor_id' => ['nullable', 'exists:vendors,id', function($attribute, $value, $fail) {
                if ($value) {
                    // Check if vendor is already assigned to another active stall
                    $existingAssignment = DB::table('stall_assignments')
                        ->join('stalls', 'stall_assignments.stall_id', '=', 'stalls.id')
                        ->where('stall_assignments.vendor_id', $value)
                        ->whereNull('stall_assignments.end_date')
                        ->whereNull('stalls.deleted_at')
                        ->first();
                    
                    if ($existingAssignment) {
                        $fail('This vendor is already assigned to another stall.');
                    }
                }
            }],
            'x1' => ['required', 'numeric'],
            'y1' => ['required', 'numeric'],
            'x2' => ['required', 'numeric'],
            'y2' => ['required', 'numeric'],
            'status' => ['required', 'in:available,occupied,maintenance'],
        ];

        $messages = [
            'stall_number.required' => 'Stall number is required.',
            'stall_number.unique' => 'This stall number already exists.',
            'stall_number.max' => 'Stall number cannot exceed 20 characters.',
            'section_id.required' => 'Please select a section.',
            'section_id.exists' => 'Selected section is invalid.',
            'vendor_id.exists' => 'Selected vendor is invalid.',
            'x1.required' => 'First corner X coordinate is required.',
            'x1.numeric' => 'X coordinate must be a number.',
            'y1.required' => 'First corner Y coordinate is required.',
            'y1.numeric' => 'Y coordinate must be a number.',
            'x2.required' => 'Second corner X coordinate is required.',
            'x2.numeric' => 'X coordinate must be a number.',
            'y2.required' => 'Second corner Y coordinate is required.',
            'y2.numeric' => 'Y coordinate must be a number.',
            'status.required' => 'Please select a status.',
            'status.in' => 'Invalid status selected.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Calculate center point for position_x and position_y (for backward compatibility)
        $centerX = ($request->x1 + $request->x2) / 2;
        $centerY = ($request->y1 + $request->y2) / 2;

        $stall = new Stall();
        $stall->stall_number = trim($request->stall_number);
        $stall->section_id = $request->section_id;
        $stall->position_x = $centerX;
        $stall->position_y = $centerY;
        $stall->x1 = min($request->x1, $request->x2); // Ensure x1 is the left coordinate
        $stall->y1 = min($request->y1, $request->y2); // Ensure y1 is the top coordinate
        $stall->x2 = max($request->x1, $request->x2); // Ensure x2 is the right coordinate
        $stall->y2 = max($request->y1, $request->y2); // Ensure y2 is the bottom coordinate
        $stall->map_coordinates_json = json_encode([
            'x1' => $stall->x1, 'y1' => $stall->y1, 
            'x2' => $stall->x2, 'y2' => $stall->y2
        ]);
        $stall->status = $request->vendor_id ? 'occupied' : $request->status;
        $stall->save();

        // Create stall assignment if vendor is assigned
        if ($request->vendor_id) {
            DB::table('stall_assignments')->insert([
                'stall_id' => $stall->id,
                'vendor_id' => $request->vendor_id,
                'assigned_date' => now()->toDateString(),
                'end_date' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Stall created successfully.',
            'stall' => [
                'id' => $stall->id,
                'stall_number' => $stall->stall_number,
                'x1' => $stall->x1,
                'y1' => $stall->y1,
                'x2' => $stall->x2,
                'y2' => $stall->y2,
            ]
        ]);
    }

    public function updateStall(Request $request, $id)
    {
        $rules = [
            'stall_number' => ['required', 'string', 'max:20', 'unique:stalls,stall_number,' . $id],
            'section_id' => ['required', 'exists:market_sections,id'],
            'vendor_id' => ['nullable', 'exists:vendors,id', function($attribute, $value, $fail) use ($id) {
                if ($value) {
                    // Check if vendor is already assigned to another active stall (excluding current stall)
                    $existingAssignment = DB::table('stall_assignments')
                        ->join('stalls', 'stall_assignments.stall_id', '=', 'stalls.id')
                        ->where('stall_assignments.vendor_id', $value)
                        ->whereNull('stall_assignments.end_date')
                        ->whereNull('stalls.deleted_at')
                        ->where('stalls.id', '!=', $id)
                        ->first();
                    
                    if ($existingAssignment) {
                        $fail('This vendor is already assigned to another stall.');
                    }
                }
            }],
            'x1' => ['required', 'numeric'],
            'y1' => ['required', 'numeric'],
            'x2' => ['required', 'numeric'],
            'y2' => ['required', 'numeric'],
            'status' => ['required', 'in:available,occupied,maintenance'],
        ];

        $messages = [
            'stall_number.required' => 'Stall number is required.',
            'stall_number.unique' => 'This stall number already exists.',
            'section_id.required' => 'Please select a section.',
            'section_id.exists' => 'Selected section is invalid.',
            'vendor_id.exists' => 'Selected vendor is invalid.',
            'x1.required' => 'First corner X coordinate is required.',
            'x1.numeric' => 'X coordinate must be a number.',
            'y1.required' => 'First corner Y coordinate is required.',
            'y1.numeric' => 'Y coordinate must be a number.',
            'x2.required' => 'Second corner X coordinate is required.',
            'x2.numeric' => 'X coordinate must be a number.',
            'y2.required' => 'Second corner Y coordinate is required.',
            'y2.numeric' => 'Y coordinate must be a number.',
            'status.required' => 'Please select a status.',
            'status.in' => 'Invalid status selected.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $stall = Stall::find($id);
        if (!$stall) {
            return response()->json(['success' => false, 'message' => 'Stall not found.'], 404);
        }

        // Calculate center point for position_x and position_y (for backward compatibility)
        $centerX = ($request->x1 + $request->x2) / 2;
        $centerY = ($request->y1 + $request->y2) / 2;

        $stall->stall_number = trim($request->stall_number);
        $stall->section_id = $request->section_id;
        $stall->position_x = $centerX;
        $stall->position_y = $centerY;
        $stall->x1 = min($request->x1, $request->x2); // Ensure x1 is the left coordinate
        $stall->y1 = min($request->y1, $request->y2); // Ensure y1 is the top coordinate
        $stall->x2 = max($request->x1, $request->x2); // Ensure x2 is the right coordinate
        $stall->y2 = max($request->y1, $request->y2); // Ensure y2 is the bottom coordinate
        $stall->map_coordinates_json = json_encode([
            'x1' => $stall->x1, 'y1' => $stall->y1, 
            'x2' => $stall->x2, 'y2' => $stall->y2
        ]);
        $stall->status = $request->vendor_id ? 'occupied' : $request->status;
        $stall->save();

        // Update stall assignment
        DB::table('stall_assignments')->where('stall_id', $id)->update(['end_date' => now()->toDateString()]);
        
        if ($request->vendor_id) {
            DB::table('stall_assignments')->insert([
                'stall_id' => $stall->id,
                'vendor_id' => $request->vendor_id,
                'assigned_date' => now()->toDateString(),
                'end_date' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Stall updated successfully.',
            'stall' => [
                'id' => $stall->id,
                'stall_number' => $stall->stall_number,
                'x1' => $stall->x1,
                'y1' => $stall->y1,
                'x2' => $stall->x2,
                'y2' => $stall->y2,
            ]
        ]);
    }

    public function deleteStall($id)
    {
        $stall = Stall::find($id);
        if (!$stall) {
            return response()->json(['success' => false, 'message' => 'Stall not found.'], 404);
        }

        // Clean up stall assignments when deleting a stall
        DB::table('stall_assignments')->where('stall_id', $id)->update(['end_date' => now()->toDateString()]);

        $stall->delete();

        return response()->json(['success' => true, 'message' => 'Stall deleted successfully.']);
    }
}
