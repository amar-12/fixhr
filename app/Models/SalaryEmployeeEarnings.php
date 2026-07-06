<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryEmployeeEarnings extends Model
{
    
    protected $table = 'employee_salaries_earnings';

    protected $primaryKey = 'es_e_id';

    protected $fillable = [
   
      
        'es_e_b_id',
        'es_e_emp_id',
        'es_e_type_id',
        'es_sa_id',
        'es_e_cal_type_id',
        'es_e_amount'       
     
    
      ];


     public function fh_salary_earning_type()
{
    return $this->belongsTo(SalaryAllowance::class, 'es_sa_id', 'sa_id');
}

      
}
