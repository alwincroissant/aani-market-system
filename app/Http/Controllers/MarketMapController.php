<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarketMapController extends Controller
{
    public function index()
    {
        $stalls = DB::table('stalls as s')
            ->leftJoin('stall_assignments as sa', 's.id', '=', 'sa.stall_id')
            ->leftJoin('vendors as v', 'sa.vendor_id', '=', 'v.id')
            ->leftJoin('market_sections as ms', 's.section_id', '=', 'ms.id')
            ->whereNull('s.deleted_at')
            ->where(function($query) {
                $query->whereNull('sa.end_date')
                      ->orWhere('sa.end_date', '>=', now()->toDateString());
            })
            ->select(
                's.id as stall_id',
                's.stall_number',
                's.position_x',
                's.position_y',
                's.map_coordinates_json',
                's.status',
                'v.id as vendor_id',
                'v.business_name',
                'v.logo_url',
                'v.weekend_pickup_enabled',
                'v.weekday_delivery_enabled',
                'v.weekend_delivery_enabled',
                'ms.section_name',
                'ms.section_code'
            )
            ->get();

        $mapImage = DB::table('system_settings')
            ->where('setting_key', 'market_map_image')
            ->value('setting_value');

        return view('welcome', compact('stalls', 'mapImage'));
    }
}

