<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class GatePassConfirmation extends Model
{

    protected $table = 'gatepass_confirmation';
    protected $primaryKey = 'gcp_id';

    protected $fillable = [
        'gcp_emp_id',
        'gcp_gtp_id',
        'gcp_emp_b_id',
        'gcp_date',
        'gcp_in_time',
        'gcp_out_time',
        'gcp_out_time_confirmation',
        'gcp_in_time_confirmation',
    ];

    protected $casts = [
        'gcp_emp_id' => 'integer',
        'gcp_emp_b_id' => 'integer',
        'gcp_gtp_id' => 'string',
        'gcp_date' => 'date',
        'gcp_out_time_confirmation' => 'boolean',
        'gcp_in_time_confirmation' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'gcp_emp_id', 'emp_id');
    }

    public function business()
    {
        return $this->belongsTo(Business::class, 'gcp_emp_b_id', 'b_id');
    }
}  