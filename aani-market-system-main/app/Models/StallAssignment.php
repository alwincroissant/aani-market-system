<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StallAssignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'stall_id',
        'vendor_id',
        'assigned_date',
        'end_date',
    ];

    public function stall()
    {
        return $this->belongsTo(Stall::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
