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
        'owner_name',
        'contact_phone',
        'contact_email',
        'regional_sourcing_origin',
        'business_description',
        'logo_url',
        'banner_url',
        'weekend_pickup_enabled',
        'weekday_delivery_enabled',
        'weekend_delivery_enabled',
    ];
}

