<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Timezone extends Model
{
    protected $table = 'time_zone';
    protected $primaryKey = 'tz_id';

    protected $fillable = [
        'tz_country_id',
        'zone_name',
        'offset',
    ];


    public function country()
    {
        return $this->belongsTo(Country::class, 'tz_country_id', 'c_id');
    }
}
