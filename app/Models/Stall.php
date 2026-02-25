<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $section_id
 * @property string $stall_number
 * @property float|null $position_x
 * @property float|null $position_y
 * @property float|null $x1
 * @property float|null $y1
 * @property float|null $x2
 * @property float|null $y2
 * @property array|null $map_coordinates_json
 * @property string|null $status
 */
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

