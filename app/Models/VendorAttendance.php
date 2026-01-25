<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorAttendance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vendor_id',
        'market_date',
        'check_in_time',
        'check_out_time',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
