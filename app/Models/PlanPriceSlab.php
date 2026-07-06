<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanPriceSlab extends Model
{
    protected $table = 'plan_price_slabs';
    public $timestamps = true;

    protected $fillable = ['plan_id', 'min_employees', 'max_employees', 'price_per_user'];

    // THIS LINE FIXES EVERYTHING
    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->plan_id) && $model->plan) {
                $model->plan_id = $model->plan->plan_id;
            }
        });
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'plan_id');
    }
}