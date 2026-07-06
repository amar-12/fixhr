<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryEmployerDeductions extends Model
{
    protected $table = 'employer_salaries_deductions';

    protected $primaryKey = 'employer_sd_id';

    protected $fillable = [
   
      
        'employer_sd_b_id',
        'employer_sd_emp_id',
        'employer_sd_type_id',
        'employer_sd_cal_type_id',
        'employer_sd_amount',       
     
    
      ];
}
