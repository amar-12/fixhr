<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailConfiguration extends Model
{
    /**
     * Table Name
     */
    protected $table = 'email_configurations';

    /**
     * Primary Key
     */
    protected $primaryKey = 'id';

    /**
     * Timestamps Enabled
     */
    public $timestamps = true;

    /**
     * Mass Assignable Fields
     */
    protected $fillable = [
        'b_id',

        // Driver
        'mailer',

        // Common Fields
        'from_address',
        'from_name',

        // SMTP
        'host',
        'port',
        'encryption',
        'username',
        'password',

        // Sendmail
        'sendmail_path',

        // Mailgun
        'mailgun_domain',
        'mailgun_secret',

        // Amazon SES
        'ses_key',
        'ses_secret',
        'ses_region',

        // Postmark
        'postmark_token',

        // Status
        'is_active',

        // Audit
        'created_by',
        'updated_by',
    ];

    /**
     * Hide Sensitive Fields
     */
    protected $hidden = [
        'password',
        'mailgun_secret',
        'ses_secret',
        'postmark_token',
    ];

    /**
     * Cast Attributes
     */
    protected $casts = [
        'id'          => 'integer',
        'b_id'        => 'integer',
        'port'        => 'integer',
        'is_active'   => 'boolean',
        'created_by'  => 'integer',
        'updated_by'  => 'integer',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Active Configuration Scope
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    /**
     * Branch Wise Scope
     */
    public function scopeBranch($query, $bId)
    {
        return $query->where('b_id', $bId);
    }

    /*
    |--------------------------------------------------------------------------
    | Driver Helper Methods
    |--------------------------------------------------------------------------
    */

    public function isSmtp()
    {
        return $this->mailer === 'smtp';
    }

    public function isSendmail()
    {
        return $this->mailer === 'sendmail';
    }

    public function isMailgun()
    {
        return $this->mailer === 'mailgun';
    }

    public function isSes()
    {
        return $this->mailer === 'ses';
    }

    public function isPostmark()
    {
        return $this->mailer === 'postmark';
    }

    public function isLog()
    {
        return $this->mailer === 'log';
    }

    public function isArray()
    {
        return $this->mailer === 'array';
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get Active Email Configuration
     */
    public static function getActiveConfiguration($bId)
    {
        return self::branch($bId)
            ->active()
            ->first();
    }

    /**
     * Set Current Configuration As Active
     */
    public function makeActive()
    {
        self::where('b_id', $this->b_id)
            ->update([
                'is_active' => 0,
            ]);

        $this->update([
            'is_active' => 1,
        ]);

        return true;
    }

    /**
     * Convert Configuration To Laravel Mail Config Array
     */
    public function toMailConfig()
    {
        return [
            'default' => $this->mailer,

            'mailers' => [
                'smtp' => [
                    'transport'  => 'smtp',
                    'host'       => $this->host,
                    'port'       => $this->port,
                    'encryption' => $this->encryption,
                    'username'   => $this->username,
                    'password'   => $this->password,
                ],

                'sendmail' => [
                    'transport' => 'sendmail',
                    'path'      => $this->sendmail_path,
                ],
            ],

            'from' => [
                'address' => $this->from_address,
                'name'    => $this->from_name,
            ],
        ];
    }
}