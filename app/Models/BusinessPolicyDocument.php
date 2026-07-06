<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessPolicyDocument extends Model
{
    protected $table = 'business_policy_documents';
    protected $primaryKey = 'bpd_id';
    public $timestamps = true;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'bpd_version',
        'bpd_folder_name',
        'bpd_file_name',
        'bpd_with_effect_from',
        'bpd_file_path',
        'bpd_status',
    ];

    protected $casts = [
        'bpd_with_effect_from' => 'date',
        'bpd_status' => 'integer',
    ];



public function folder()
{
    return $this->belongsTo(BusinessPolicyFolder::class, 'bpd_folder_id', 'bpf_id');
}

}
