<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'vendor_id',
        'category_id',
        'product_name',
        'description',
        'price_per_unit',
        'unit_type',
        'product_image_url',
        'is_available',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'price_per_unit' => 'decimal:2',
    ];

    // Relationship to vendor
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    // Relationship to category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}