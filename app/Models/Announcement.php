<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $table = 'announcements';
    protected $primaryKey = 'ann_id';

    protected $fillable = [
        'ann_b_id',
        'ann_title',
        'ann_is_read',
        'ann_message',
        'ann_user_id',
        'ann_role_id',
        'ann_category',
        'ann_image',
    ];
}
