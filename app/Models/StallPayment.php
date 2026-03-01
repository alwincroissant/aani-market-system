<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StallPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'stall_id',
        'amount_due',
        'amount_paid',
        'due_date',
        'paid_at',
        'status',
        'payment_method',
        'payment_reference',
        'billing_period',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function stall()
    {
        return $this->belongsTo(Stall::class);
    }
}
