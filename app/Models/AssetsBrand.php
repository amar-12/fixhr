<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class AssetsBrand extends Model
{
    use HasFactory;

    protected $table = 'assets_brands';   

    protected $primaryKey = 'br_id';    

    protected $fillable = [
        'br_name',
        'br_b_id',
        'br_slug',
        'br_description',
    ];

    public function assetTypes()
    {
        return $this->hasMany(AssetType::class, 'brand_id', 'br_id');
    }
}
