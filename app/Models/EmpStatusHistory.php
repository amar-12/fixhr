<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmpStatusHistory extends Model
{
    use HasFactory;
    protected $table = 'emp_status_history';
    protected $primaryKey = 'hs_id';

    protected $fillable = [
        'hs_emp_id',
        'hs_b_id',
        'hs_status_id',
    ];


      public function employee()
    {
        return $this->belongsTo(Employee::class, 'hs_emp_id', 'emp_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'hs_b_id', 'b_id');
    }

    public function status()
    {
        return $this->belongsTo(MasterTable::class, 'hs_status_id', 'm_id');
    }

}
