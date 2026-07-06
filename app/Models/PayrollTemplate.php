<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollTemplate extends Model
{
    protected $table = 'payroll_template';
    protected $primaryKey = 'pt_id';

    protected $fillable = [
   
      'pt_b_id',
      'pt_temp_name',
      'pt_temp_description',
      'pt_an_ctc',
      'pt_m_ctc',
      'pt_component_type',
      'pt_component_list_id',
      'pt_total_earning',
      'pt_gross_salary',
      'pt_total_deduction',
      'pt_net_pay',
      'created_at',
      'updated_at'
    ];

   
}
