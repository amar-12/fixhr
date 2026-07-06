<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PayrollMasterSetting extends Model
{
    use HasFactory;

    protected $table = 'payroll_master_settings';

    protected $primaryKey = 'pms_id';

    public $timestamps = true;

    protected $fillable = [
        'pms_b_id',
        'pms_payroll_cycle',
        'pms_is_locked',
        'pms_payroll_mode',
        'pms_year_type',
        'pms_include_with_salary',
        'pms_include_tada_with_salary',
        'pms_phone'
    ];

}
