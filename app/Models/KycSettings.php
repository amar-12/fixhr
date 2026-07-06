<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KycSettings extends Model
{
	protected $table = 'kyc_settings'; // change if your table name differs

	protected $primaryKey = 'id';

	protected $fillable = [
		'business_id',
		'provider_name',
		'api_base_url',
		'access_token',
		'token_expiration',
		'client_id',
		'client_secret',
		'client_version',
		'redirect_uri',
		'auth_url',
		'token_url',
		'user_info_url',
		'mode',
		'is_active',
	];

	protected $casts = [
		'id' => 'int',
		'business_id' => 'int',
		'token_expiration' => 'datetime',
		'is_active' => 'boolean',
		'mode' => 'string', // enum is treated as string in Eloquent
	];

	/**
	 * Relationship: API Provider belongs to a Business
	 */
	public function business()
	{
		return $this->belongsTo(Business::class, 'business_id');
	}

	public function isTokenValid(): bool
	{
		return isset($this->token_expiration) && !$this->token_expiration->isPast();
	}
}
