<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalFlow extends Model
{
    protected $table = 'approval_flows';
    protected $primaryKey = 'afc_id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'afc_b_id',
        'afc_approval_id',
        'afc_approval_status_id',
        'afc_status',
    ];

    protected $casts = [
        'afc_approval_status_id' => 'array', 
    ];

    public function stage()
    {
        return $this->belongsTo(MasterTable::class, 'afc_approval_id', 'm_id');
    }

    public function statuses()
    {
        return MasterTable::whereIn('m_id', $this->afc_approval_status_id ?? [])->get();
    }
}
