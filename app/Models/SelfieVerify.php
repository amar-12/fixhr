<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SelfieVerify extends Model
{

    protected $table = 'selfie_verify';
    protected $primaryKey = 'sv_id';

    protected $fillable = [
        'sv_emp_code',
        'sv_emp_id',
        'sv_emp_b_id',
        'sv_emp_name',
        'sv_device_id',
        'sv_device_name',
        'sv_device_modal',
        'sv_status',
    ];

    protected $casts = [
        'sv_status' => 'string',
    ];

    // Valid status values
    const STATUS_PENDING = '0';
    const STATUS_VERIFIED = '1';
    const STATUS_REJECTED = '2';

    public static function getValidStatuses()
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_VERIFIED,
            self::STATUS_REJECTED,
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'sv_emp_id', 'emp_id');
    }

}
