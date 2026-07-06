<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollTemplateEarning extends Model
{
    protected $table = 'payroll_temp_earnings';

    protected $primaryKey = 'pt_earn_id';

    protected $fillable = [
   
        'pt_earn_id',
        'pt_temp_id',
        'pt_earn_type_id',
        'updated_at',
        'created_at',
    
      ];

    
}
