<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KitReplacement extends Model
{
    use HasFactory;

    protected $table = 'kit_replacements'; // Updated table name

    protected $fillable = [
        'b_id',
        'kit_id',
        'replaced_item',
        'replacement_qty',
        'replace_date',
        'replace_note',
        'replaced_by',
        'cost',
    ];

     public function fh_kit()
    {
        return $this->belongsTo(KitStock::class, 'kit_id', 'id');
    }

    public function replacedBy()
    {
        return $this->belongsTo(Employee::class, 'replaced_by', 'emp_id');
    }

    public function logs()
    {
        return $this->hasMany(KitLog::class, 'reference_id');
    }
}
