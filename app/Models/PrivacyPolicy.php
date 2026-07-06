<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrivacyPolicy extends Model
{
    protected $table = 'privacy_policy';
    protected $primaryKey = 'pp_id';
    protected $fillable = [
        'pp_title',
        'pp_description',
    ];
}
