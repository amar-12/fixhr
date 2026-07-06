<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryEmployeeDeductions extends Model
{
    protected $table = 'employee_salaries_deductions';

    protected $primaryKey = 'es_d_id';

    protected $fillable = [
        'es_d_b_id',
        'es_d_emp_id',
        'es_d_type_id',
        'es_d_cal_type_id',
        'es_d_amount',       

      ];

        public function fh_salary_deduction_type()
    {
        return $this->belongsTo(MasterTable::class,'es_d_type_id');
    }
}
