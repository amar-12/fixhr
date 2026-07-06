<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TadaReimburse extends Model
{
    protected $table = 'tada_reimburses';
    protected $primaryKey = 'tr_id';

    protected $casts = [
        'tr_b_id'       => 'int',
        'tr_claims_id'  => 'json',
        'tr_amount'     => 'float',
    ];

    protected $fillable = [
        'tr_b_id',
        'tr_unique_id',
        'tr_group_id',
        'tr_claims_id',
        'tr_status',
        'tr_date',
        'tr_amount'
    ];


    protected static function incrementLetters($letters)
    {
        $first = $letters[0];
        $second = $letters[1];

        if ($second < 'Z') {
            $second = chr(ord($second) + 1);
        } else {
            $second = 'A';
            $first = chr(ord($first) + 1);
        }

        return $first . $second;
    }


    public function fh_business()
    {
        return $this->belongsTo(Business::class, 'tr_b_id');
    }

    public function getFhClaimsAttribute()
    {
        $ids = $this->tr_claims_id ?? [];

        if (empty($ids)) {
            return collect();
        }

        return TadaClaim::whereIn('tc_id', $ids)->get();
    }



    public function fh_tada_plan_data()
    {
        $claimIds = $this->tr_claims_id;

        if (is_array($claimIds) && !empty($claimIds)) {
            return TadaClaim::with('fh_tada_request_plan:trp_id,trp_unique_id')
                ->whereIn('tc_id', $claimIds)
                ->get();
        }

        return collect();
    }


    public function getFhTadaPlanDataAttribute()
    {
        return $this->fh_tada_plan_data()->pluck('fh_tada_request_plan.trp_unique_id')->toArray();
    }
}
