<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDevice extends Model
{

    protected $table = 'user_devices';
    protected $primaryKey = 'ud_id';

    protected $fillable = [
        'ud_emp_code',
        'ud_emp_id',
        'ud_emp_b_id',
        'ud_emp_name',
        'ud_device_id',
        'ud_status',
    ];

    protected $casts = [
        'ud_status' => 'string',
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
        return $this->belongsTo(Employee::class, 'ud_emp_id', 'emp_id');
    }

}
