<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class FhTadaExpenseSetting
 *
 * @property int $tes_id
 * @property int|null $tes_expense_type_id
 * @property int $tes_code
 * @property string|null $tes_head
 * @property Carbon $created_at
 * @property Carbon|null $updated_at
 *
 * @property MasterTable|null $fh_master_table
 *
 * @package App\Models
 */
class TadaExpenseSetting extends Model
{
    protected $table = 'tada_expense_settings';
    protected $primaryKey = 'tes_id';

    protected $casts = [
        'tes_expense_type_id' => 'int',
        'tes_code' => 'int',
        'tc_deduction_amount' => 'float',
        'tes_is_fixed' => 'boolean'
    ];

    protected $fillable = [
        'tes_expense_type_id',
        'tes_b_id',
        'tes_code',
        'tes_head',
        'tes_is_fixed',
        'tes_fixed_amount'
    ];

    public function fh_business()
    {
        return $this->belongsTo(Business::class, 'tes_b_id');
    }

    public function fh_master_table()
    {
        return $this->belongsTo(MasterTable::class, 'tes_expense_type_id');
    }
}
