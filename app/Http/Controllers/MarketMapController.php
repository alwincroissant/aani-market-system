<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarketMapController extends Controller
{
    public function index()
    {
        // Fetch all occupied stalls with vendor information
        $stalls = DB::table('stalls as s')
            ->join('stall_assignments as sa', function($join) {
                $join->on('s.id', '=', 'sa.stall_id')
                     ->whereNull('sa.end_date');
            })
            ->join('vendors as v', 'sa.vendor_id', '=', 'v.id')
            ->leftJoin('market_sections as ms', 's.section_id', '=', 'ms.id')
            ->whereNull('s.deleted_at')
            ->where('s.status', 'occupied')
            ->whereNull('v.deleted_at')
            ->select(
                's.id as stall_id',
                's.stall_number',
                's.x1', 's.y1', 's.x2', 's.y2',
                's.position_x',
                's.position_y',
                's.map_coordinates_json',
                'v.id as vendor_id',
                'v.business_name',
                'v.business_description as vendor_description',
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

        $featuredVendors = DB::table('vendors as v')
            ->whereNull('v.deleted_at')
            ->leftJoin('products as p', function ($join) {
                $join->on('v.id', '=', 'p.vendor_id')
                    ->whereNull('p.deleted_at')
                    ->where('p.is_available', true);
            })
            ->select(
                'v.id',
                'v.business_name',
                'v.business_description',
                'v.logo_url',
                DB::raw('COUNT(p.id) as product_count')
            )
            ->groupBy('v.id', 'v.business_name', 'v.business_description', 'v.logo_url')
            ->orderBy('product_count', 'desc')
            ->limit(6)
            ->get();

        $featuredProducts = DB::table('products as p')
            ->join('vendors as v', 'p.vendor_id', '=', 'v.id')
            ->leftJoin('product_categories as pc', 'p.category_id', '=', 'pc.id')
            ->whereNull('p.deleted_at')
            ->where('p.is_available', true)
            ->whereNull('v.deleted_at')
            ->select(
                'p.id',
                'p.product_name',
                'p.description',
                'p.price_per_unit',
                'p.unit_type',
                'p.product_image_url',
                'p.vendor_id',
                'v.business_name',
                'v.logo_url',
                'pc.category_name'
            )
            ->orderBy('p.created_at', 'desc')
            ->limit(8)
            ->get();

        return view('welcome', compact('stalls', 'mapImage', 'featuredVendors', 'featuredProducts'));
    }
}

