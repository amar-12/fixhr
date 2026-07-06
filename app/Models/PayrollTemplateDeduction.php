<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollTemplateDeduction extends Model
{
    protected $table = 'payroll_temp_deductions';

    protected $primaryKey = 'pt_deduc_id';

    protected $fillable = [
   
        'pt_deduc_id',
        'pt_temp_id',
        'pt_deduc_type_id',
        'updated_at',
        'created_at',
    
      ];
}
