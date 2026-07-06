<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PreviousOrganization
 *
 * @property int $po_id
 * @property string $po_company_name
 * @property int $po_emp_id
 * @property int $po_dg_id
 * @property Carbon $po_from_date
 * @property Carbon $po_to_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class PreviousOrganization extends Model
{
	protected $table = 'previous_organization';
	protected $primaryKey = 'po_id';

	protected $casts = [
		'po_emp_id' => 'int',
		// 'po_dg_id' => 'int',
		'po_from_date' => 'datetime',
		'po_to_date' => 'datetime'
	];

	  protected $fillable = [
        'po_company_name',
        'po_emp_id',
        'po_designation_name',
        'po_from_date',
        'po_to_date',
        'po_serviceduration',
    ];
}
