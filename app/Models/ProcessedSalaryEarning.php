<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProcessedSalaryEarning extends Model
{
    use HasFactory;

    
    protected $table = 'processed_salary_earnings'; // Replace with actual table name if different
    protected $primaryKey = 'ps_e_id';
    public $timestamps = true; // Enables created_at & updated_at

    
    protected $fillable = ['ps_id', 'ps_earning_type_id','ps_earning_type', 'ps_e_amount'];
    
    public function processedSalary()
    {
        return $this->belongsTo(ProcessedEmployeeSalary::class, 'ps_id');
    }
}



