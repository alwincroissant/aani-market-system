<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Vendor;
use App\Models\ProductCategory;

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
        'stock_quantity',
        'minimum_stock',
        'track_stock',
        'allow_backorder',
        'stock_notes',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'price_per_unit' => 'decimal:2',
        'track_stock' => 'boolean',
        'allow_backorder' => 'boolean',
    ];

    // Relationship to vendor
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    // Relationship to category
    public function category()
    {
        return $this->belongsTo(ProductCategory::class);
    }

    // Stock management methods
    public function isInStock()
    {
        if (!$this->track_stock) {
            return true;
        }
        return $this->stock_quantity > 0 || $this->allow_backorder;
    }

    public function isLowStock()
    {
        if (!$this->track_stock) {
            return false;
        }
        return $this->stock_quantity <= $this->minimum_stock;
    }

    public function getStockStatusAttribute()
    {
        if (!$this->track_stock) {
            return 'Not tracked';
        }
        
        if ($this->stock_quantity == 0) {
            return $this->allow_backorder ? 'Backorder' : 'Out of stock';
        }
        
        if ($this->isLowStock()) {
            return 'Low stock';
        }
        
        return 'In stock';
    }

    public function updateStock($quantity, $type = 'add')
    {
        if (!$this->track_stock) {
            return;
        }

        if ($type === 'add') {
            $this->stock_quantity += $quantity;
        } elseif ($type === 'subtract') {
            $this->stock_quantity = max(0, $this->stock_quantity - $quantity);
        } elseif ($type === 'set') {
            $this->stock_quantity = max(0, $quantity);
        }

        $this->save();
    }
}