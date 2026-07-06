<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetType extends Model
{
    use HasFactory;

    protected $table = 'asset_types';

    protected $fillable = [
        'name',
        'assets_type_b_id',
        'brand_id',
        'category_id',
        'description',
        'is_active',
    ];

    /**
     * Business Relation
     */
    public function business()
    {
        return $this->belongsTo(Business::class, 'assets_type_b_id', 'id');
    }
    public function brand()
    {
        return $this->belongsTo(AssetsBrand::class, 'brand_id', 'br_id');
    }

    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'category_id', 'ac_id');
    }

    public function assets()
    {
        return $this->hasMany(Asset::class, 'asset_type_id', 'id');
    }
}
