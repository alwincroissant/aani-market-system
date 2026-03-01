<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalkInSale extends Model
{
    use HasFactory;

    protected $table = 'walk_in_sales';

    protected $fillable = [
        'vendor_id',
        'sale_date',
        'sale_time',
        'product_id',
        'product_name',
        'quantity',
        'unit_price',
        'notes',
    ];

    protected $casts = [
        'sale_date'  => 'date',
        'sale_time'  => 'datetime',
        'quantity'   => 'decimal:2',
        'unit_price' => 'decimal:2',
    ];

    /* ── Relationships ─────────────────────────────────────── */

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /* ── Computed ───────────────────────────────────────────── */

    public function getTotalAttribute()
    {
        return $this->quantity * $this->unit_price;
    }
}
