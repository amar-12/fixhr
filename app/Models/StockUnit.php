<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockUnit extends Model
{
    protected $table = 'stock_units';

    protected $primaryKey = 'su_id';

    protected $fillable = [
        'su_b_id',
        'su_name',
        'su_short_name',
        'su_description',
        'su_status'
    ];
}
