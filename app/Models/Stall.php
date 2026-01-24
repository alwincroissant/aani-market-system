<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stall extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'section_id',
        'stall_number',
        'position_x',
        'position_y',
        'map_coordinates_json',
        'status',
    ];

    protected $casts = [
        'map_coordinates_json' => 'array',
    ];
}

