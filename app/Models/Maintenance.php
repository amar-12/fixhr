<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model
{
    protected $table = 'is_maintinance';

    protected $fillable = [
        'status',
        'end_at',
    ];

    public $timestamps = false;

    protected $casts = [
        'status' => 'boolean',
        'end_at' => 'datetime',
    ];
}
