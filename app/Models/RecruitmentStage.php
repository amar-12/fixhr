<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class RecruitmentStage
 *
 * @property int $rsg_id
 * @property bool $rsg_is_active
 * @property string $rsg_stage
 * @property int|null $rsg_sequence
 * @property int|null $rsg_stage_type
 * @property int $rsg_created_by_id
 * @property int $rsg_modified_by_id
 * @property int $rsg_recruitment_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Employee $fh_employee
 * @property Recruitment $fh_recruitment
 * @property MasterTable|null $fh_master_table
 *
 * @package App\Models
 */
class RecruitmentStage extends Model
{
    protected $table = 'recruitment_stage';
    protected $primaryKey = 'rsg_id';

    protected $casts = [
        'rsg_is_active' => 'bool',
        'rsg_sequence' => 'int',
        'rsg_stage_type' => 'int',
        'rsg_created_by_id' => 'int',
        'rsg_modified_by_id' => 'int',
        'rsg_recruitment_id' => 'int',
        // 'rsg_managers' => 'array', // Automatically cast JSON to array

    ];

    protected $fillable = [
        'rsg_b_id',
        'rsg_is_active',
        'rsg_stage',
        'rsg_sequence',
        'rsg_stage_type',
        'rsg_created_by_id',
        'rsg_modified_by_id',
        'rsg_recruitment_id',
        'rsg_managers',
        'created_at',
        'updated_at'
    ];


    public function fh_recruitment()
    {
        return $this->belongsTo(Recruitment::class, 'rsg_recruitment_id');
    }

    public function fh_master_table()
    {
        return $this->belongsTo(MasterTable::class, 'rsg_stage_type');
    }

    public function fh_business()
    {
        return $this->belongsTo(Business::class, 'rsg_b_id');
    }

    public function fh_managers($mangers)
    {
        $mangers= json_decode($mangers);
        return Employee::whereIn('emp_id', $mangers)->pluck('emp_full_name');
    }

    public function fh_employee_modified()
    {
        return $this->belongsTo(Employee::class, 'rsg_modified_by_id');
    }

    public function fh_employee_created()
    {
        return $this->belongsTo(Employee::class, 'rsg_created_by_id');
    }


    public function fh_candidate()
    {
        return $this->hasMany(RecruitmentCandidate::class, 'rc_stage_id', 'rsg_id')
            ->where('rc_recruitment_id', $this->rsg_recruitment_id);
    }

}
