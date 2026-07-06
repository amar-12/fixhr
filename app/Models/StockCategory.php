<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockCategory extends Model
{
    protected $table = 'stock_categories';

    protected $primaryKey = 'sc_id';

    protected $fillable = [
        'sc_b_id',
        'sc_name',
        'sc_slug',
        'sc_description',
        'sc_status'
    ];
}
