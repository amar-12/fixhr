<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KitDamage extends Model
{
    use HasFactory;

    protected $table = 'kit_damages';

    protected $fillable = [
        'b_id',
        'kit_id',
        'damage_by',
        'damage_qty',
        'damage_date',
        'damage_details',
        'damage_note',
        'damage_cost',
        'status',
    ];


    public function fh_kit()
    {
        return $this->belongsTo(KitStock::class, 'kit_id', 'id');
    }

    public function damagedBy()
    {
        return $this->belongsTo(Employee::class, 'damage_by', 'emp_id');
    }

    public function logs()
    {
        return $this->hasMany(KitLog::class, 'reference_id');
    }
}
