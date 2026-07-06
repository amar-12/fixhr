<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FinancialYear extends Model
{
    use HasFactory;

    protected $table = 'financial_years';
	protected $primaryKey = 'fy_id';


    protected $fillable = [
        'fy_year',
        'fy_b_id',
        'fy_start_date',
        'fy_end_date',
        'fy_is_current',
    ];

    /**
     * Scope to get the current financial year.
     */
    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    
    //payroll period
	public function payrollPeriod()
	{
		return $this->hasMany(PayrollPeriod::class, 'pp_fy_id', 'fy_id');
	}
}
