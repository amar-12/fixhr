<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessPolicyFolder extends Model
{
    protected $table = 'business_policy_folder';
    protected $primaryKey = 'bpf_id';
    protected $fillable = ['pbf_b_id', 'bpf_name'];
    public $timestamps = true;


    public function documents()
    {
        return $this->hasMany(BusinessPolicyDocument::class, 'bpd_folder_id', 'bpf_id');
    }
}