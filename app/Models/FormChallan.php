<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormChallan extends Model
{
    protected $table = 'form_challans';
    protected $primaryKey = 'f16ac_id';

    protected $fillable = [
        'f16ac_b_id',
        'f16ac_emp_id',
        'f16ac_fy_id',
        'f16ac_quarter',
        'f16ac_form_key',
        'f16ac_receipt_no',
        'f16ac_bsr_code',
        'f16ac_challan_date',
        'f16ac_challan_serial_no',
        'f16ac_created_by',
    ];

    protected $casts = [
        'f16ac_challan_date' => 'date',
    ];
}

