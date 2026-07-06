<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetCategory extends Model
{
    use HasFactory;

    protected $table = 'assets_categories';
    protected $primaryKey = 'ac_id';

    protected $fillable = [
        'ac_code',
        'ac_b_id',
        'ac_name',
        'ac_description',
    ];

     public function assetTypes()
    {
        return $this->hasMany(AssetType::class, 'category_id', 'ac_id');
    }
}
