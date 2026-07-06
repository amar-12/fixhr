<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProcessedSalaryDeduction extends Model
{
    use HasFactory;

    
    protected $table = 'processed_salary_deductions'; // Replace with actual table name if different
    protected $primaryKey = 'ps_d_id';
    public $timestamps = true; // Enables created_at & updated_at
    
    protected $fillable = ['ps_id', 'ps_deduction_type_id','ps_deduction_type', 'ps_d_category','ps_d_amount'];
    
    public function processedSalary()
    {
        return $this->belongsTo(ProcessedEmployeeSalary::class, 'ps_id');
    }
}
