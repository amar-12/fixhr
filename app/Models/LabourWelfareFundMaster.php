<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabourWelfareFundMaster extends Model
{
    protected $table = 'labour_welfare_fund_master';
    protected $primaryKey = 'lwf_id';
    public $timestamps = false;

    protected $fillable = [
        'state_id',
        'cycle_id',
        'lwf_employee_contri',
        'lwf_employer_contri',
        'total_contribution',
    ];

    // Relationships
    public function state()
    {
        return $this->belongsTo(State::class, 'state_id', 's_id');
    }

    public function cycle()
    {
        return $this->belongsTo(MasterTable::class, 'cycle_id', 'm_id');
    }
}

