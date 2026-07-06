<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KitLog extends Model
{
    use HasFactory;

    protected $table = 'kit_logs';

    protected $fillable = [
        'b_id',
        'kit_id',
        'action_type',
        'reference_id',
        'opening_qty',
        'available_qty',
        'assigned_qty',
        'damaged_qty',
        'qty',
        'lost_qty',
        'replaced_qty',
        'price_per_unit',
        'total_price',
        'note',
        'action_by',
    ];

    // Action types constants
    const ADD      = 'added';
    const UPDATE   = 'updated';
    const DELETE   = 'deleted';
    const ASSIGN   = 'assigned';
    const RETURN   = 'returned';
    const DAMAGE   = 'damaged';
    const LOST     = 'lost';
    const REPLACE  = 'replaced';


    public function kit()
    {
        return $this->belongsTo(Kit::class, 'kit_id');
    }

    public function user()
    {
        return $this->belongsTo(Employee::class, 'action_by', 'emp_id');
    }

    public function assignment()
    {
        return $this->belongsTo(KitAssignment::class, 'reference_id');
    }

    public function damage()
    {
        return $this->belongsTo(KitDamage::class, 'reference_id');
    }

    public function replacement()
    {
        return $this->belongsTo(KitReplacement::class, 'reference_id');
    }


    public static function addLog($data)
    {
        // Auto detect quantity key
        return self::create([
            'b_id'           => $data['b_id'],
            'kit_id'         => $data['kit_id'],
            'action_type'    => $data['action_type'],
            'reference_id'   => $data['reference_id'] ??  0,
            'opening_qty'    => $data['opening_qty'] ??  0,
            'available_qty'  => $data['available_qty'] ??  0,
            'assigned_qty'   => $data['assigned_qty'] ??  0,
            'damaged_qty'    => $data['damaged_qty'] ??  0,
            'lost_qty'       => $data['lost_qty'] ??  0,
            'replaced_qty'   => $data['replaced_qty'] ??  0,
            'price_per_unit' => $data['price_per_unit'] ??  0,
            'total_price'    => $data['total_price'] ??  0,
            'note'           => $data['note'] ??  0,
            'action_by'      => $data['action_by'],
        ]);
    }
}
