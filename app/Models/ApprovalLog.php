<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * class ApprovalLog
 *
 * @property int $log_id
 * @property int|null $log_am_id
 * @property int|null $log_request_id
 * @property int|null $log_user_id
 * @property int|null $log_user_role_id
 * @property int|null $log_status
 * @property string|null $log_description
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property ApprovalModule|null $fh_approval_module
 * @property MasterTable|null $fh_master_table
 * @property Employee|null $fh_employee
 * @property Role|null $fh_role
 *
 * @package App\Models
 */
class ApprovalLog extends Model
{
    protected $table = 'approval_logs';
    protected $primaryKey = 'log_id';

    protected $casts = [
        'log_am_id' => 'int',
        'log_request_id' => 'int',
        'log_user_id' => 'int',
        'log_user_role_id' => 'int',
        'log_status' => 'int'
    ];

    protected $fillable = [
        'log_am_id',
        'log_request_id',
        'log_user_id',
        'log_user_role_id',
        'log_status',
        'log_description',
        'log_module_id',
        'log_other',
        
    ];

    public function fh_approval_module()
    {
        return $this->belongsTo(ApprovalModule::class, 'log_am_id');
    }

    public function fh_status()
    {
        return $this->belongsTo(MasterTable::class, 'log_status');
    }

    public function fh_employee()
    {
        return $this->belongsTo(Employee::class, 'log_user_id');
    }

    public function fh_role()
    {
        return $this->belongsTo(Role::class, 'log_user_role_id');
    }

    public function fh_deduction_log()
    {
        return $this->hasMany(DeductionLog::class, 'dlog_user_role_id', 'log_user_role_id')
            ->where('dlog_user_id', '=', $this->log_user_id)
            ->where('dlog_am_id', '=', $this->log_am_id);
    }

    public function fh_deductionLog()
    {
        return $this->hasOne(DeductionLog::class, 'dlog_log_id', 'log_id');
    }

   

}
