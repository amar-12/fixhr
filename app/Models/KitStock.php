<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KitStock extends Model
{
    use HasFactory;

    protected $table = 'kit_stock';
    protected $fillable = [
        'b_id',
        'kit_id',
        'opening_qty',
        'available_qty',
        'assigned_qty',
        'damaged_qty',
        'lost_qty',
        'replaced_qty',
        'price_per_unit',
        'total_price',
        'note',
        'is_payable',
        'discount_type',
        'discount_value',
        'created_by',
        'updated_by',
    ];


    public function kit()
    {
        return $this->belongsTo(Kit::class, 'kit_id');
    }


    public function creator()
    {
        return $this->belongsTo(Employee::class, 'created_by', 'emp_id');
    }
    public function updater()
    {
        return $this->belongsTo(Employee::class, 'updated_by', 'emp_id');
    }
    public function logs()
    {
        return $this->hasMany(KitLog::class, 'kit_id');
    }
}
