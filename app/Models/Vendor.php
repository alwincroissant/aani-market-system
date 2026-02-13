<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'user_id',
        'business_name',
        'farm_name',
        'vendor_bio',
        'owner_name',
        'contact_phone',
        'contact_email',
        'regional_sourcing_origin',
        'region',
        'complete_address',
        'farm_size',
        'years_in_operation',
        'business_description',
        'business_hours',
        'logo_url',
        'banner_url',
        'weekend_pickup_enabled',
        'weekday_delivery_enabled',
        'weekend_delivery_enabled',
        'delivery_available',
        'is_live',
    ];

    protected $casts = [
        'weekend_pickup_enabled' => 'boolean',
        'weekday_delivery_enabled' => 'boolean',
        'weekend_delivery_enabled' => 'boolean',
        'delivery_available' => 'boolean',
        'is_live' => 'boolean',
        'farm_size' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}