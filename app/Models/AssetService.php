<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetService extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'asset_tag',
        'asset_type_id',
        'assets_b_id',
        'service_type',
        'service_location',
        'issue_description',
        'vendor_name',
        'service_status',
        'service_cost',
        'service_start_date',
        'courier_name',
        'docket_no',
        'service_file',
        'employee_id',
        'service_end_date'
    ];

    public function fh_asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }


    public function fh_branch()
    {
        return $this->belongsTo(Branch::class, 'service_location', 'br_id');
    }


    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'emp_id');
    }

    public function user()
    {
        return $this->belongsTo(Employee::class, 'issue_by', 'emp_id');
    }
}
