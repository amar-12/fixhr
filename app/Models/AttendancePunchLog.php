<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AttendancePunchLog
 *
 * @property int $apl_id
 * @property int|null $apl_atd_id
 * @property int|null $apl_b_id
 * @property int $apl_emp_id
 * @property Carbon|null $apl_date
 * @property Carbon|null $apl_check_in_time
 * @property Carbon|null $apl_check_out_time
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class AttendancePunchLog extends Model
{
	protected $table = 'attendance_punch_log';
	protected $primaryKey = 'apl_id';

	protected $casts = [
		'apl_atd_id' => 'int',
		'apl_b_id' => 'int',
		'apl_emp_id' => 'int',
		'apl_date' => 'datetime',
		'apl_check_in_time' => 'datetime',
		'apl_check_out_time' => 'datetime',
	];

	protected $fillable = [
		'apl_atd_id',
		'apl_b_id',
		'apl_emp_id',
		'apl_device_id',
		'apl_date',
		'apl_pst_id',
		'apl_work_mode_type_id',
		'apl_checkin_method_id',
		'apl_check_in_time',
		'apl_check_out_time',
		'created_at',
		'updated_at',
	];


    public function fh_business()
    {
        return $this->belongsTo(Business::class, 'al_b_id');
    }

    public function fh_employee()
    {
        return $this->belongsTo(AttendanceRecord::class, 'al_emp_id', 'atd_emp_id');
    }
    
    public function fh_employee_data()
    {
        return $this->belongsTo(Employee::class, 'al_updated_by', 'emp_id');
    }

    public function fh_attendance()
    {
        return $this->belongsTo(AttendanceRecord::class, 'al_atd_id');
    }

}
