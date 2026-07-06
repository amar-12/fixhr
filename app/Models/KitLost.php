<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KitLost extends Model
{
    use HasFactory;

    protected $table = 'kit_losts';

    protected $fillable = [
        'b_id',
        'kit_id',
        'lost_by',
        'lost_qty',
        'lost_date',
        'lost_details',
        'lost_note',
        'lost_cost',
        'status',
    ];

      public function fh_kit()
    {
        return $this->belongsTo(KitStock::class, 'kit_id', 'id');
    }
    public function lostBy()
    {
        return $this->belongsTo(Employee::class, 'lost_by', 'emp_id');
    }

    public function logs()
    {
        return $this->hasMany(KitLog::class, 'reference_id');
    }
}
