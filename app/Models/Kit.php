<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kit extends Model
{
    use HasFactory;

    protected $table = 'kits'; // IMPORTANT: updated table name

    protected $fillable = [
        'b_id',
        'kit_code',
        'kt_category_id',
        'kt_unit_id',
        'name',
        'description',
        'type',
        'size',
        'unit',
        'per_unit_price',
        'total_qty',
        'total_value',
        'status',
        'created_by',
        'updated_by',
    ];

    public function creator()
    {
        return $this->belongsTo(Employee::class, 'created_by', 'emp_id');
    }

    public function updater()
    {
        return $this->belongsTo(Employee::class, 'updated_by', 'emp_id');
    }

    public function assignments()
    {
        return $this->hasMany(KitAssignment::class, 'kit_id');
    }

    public function damages()
    {
        return $this->hasMany(KitDamage::class, 'kit_id');
    }

    public function replacements()
    {
        return $this->hasMany(KitReplacement::class, 'kit_id');
    }

    public function stock()
    {
        return $this->belongsTo(KitStock::class, 'kit_id', 'id');
    }

    public function logs()
    {
        return $this->hasMany(KitLog::class, 'kit_id');
    }
    public function fh_category()
    {
        return $this->belongsTo(StockCategory::class, 'kt_category_id', 'sc_id');
    }

    public function fh_unit()
    {
        return $this->belongsTo(StockUnit::class, 'kt_unit_id', 'su_id');
    }
}
