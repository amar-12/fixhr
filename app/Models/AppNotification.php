<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppNotification extends Model
{

    use SoftDeletes;
    protected $casts = [
        'additional_data' => 'array',
    ];
    protected $fillable = [
        'sender_id',
        'user_id',
        'title',
        'body',
        'additional_data',
        'is_read',
    ];
}
