<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailTemplate extends Model
{
    protected $table = 'mail_templates';
    protected $primaryKey = 'mt_id';

    protected $fillable = [
        'mt_title',
        'mt_body',
        'mt_mail_type',
        'mt_module_id',
        'mt_b_id',
        'mt_is_enabled',
        'mt_send_to',
        'variables',
    ];

    protected $casts = [
        'mt_b_id'        => 'integer',
        'mt_module_id'   => 'integer',
        'mt_is_enabled'  => 'boolean',
        'variables'      => 'array',
    ];

    public function fh_business()
    {
        return $this->belongsTo(Business::class, 'mt_b_id');
    }

    public function fh_module()
    {
        return $this->belongsTo(MasterTable::class, 'mt_module_id', 'm_id');
    }

    public function fh_master_table()
    {
        return $this->belongsTo(MasterTable::class, 'mt_mail_type', 'm_id');
    }
}
