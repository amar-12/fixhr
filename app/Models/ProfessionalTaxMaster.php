<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfessionalTaxMaster extends Model
{
    use HasFactory;

    protected $table = 'professional_tax_master';
    protected $primaryKey = 'ptm_id';
    public $timestamps = false;

    protected $fillable = [
        'ptm_s_id',         // State ID
        'ptm_income_from',  // Minimum income slab
        'ptm_income_to',    // Maximum income slab
        'ptm_tax_amount',   // Professional tax amount
        'ptm_cycle_id',     // Cycle (Monthly, Yearly, etc.)
        'ptm_gender'        // Gender (male, female, other, all)
    ];

    /**
     * Get Professional Tax based on salary, gender, and cycle.
     *
     * @param int $stateId
     * @param float $salary
     * @param string $gender ('male', 'female', 'other', 'all')
     * @param int $cycleId
     * @return ProfessionalTaxMaster|null
     */

    public function state()
    {
        return $this->belongsTo(
            State::class,       // model
            'ptm_s_id',         // FK on THIS table
            's_id'
        );            // PK on states
    }


    public function cycle()
    {
        return $this->belongsTo(
            MasterTable::class, // generic master
            'ptm_cycle_id',     // FK on THIS table
            'm_id'
        );            // PK on master
    }

    /**
     * Gender master record (Male / Female / Other / All)
     */
    public function gender()
    {
        return $this->belongsTo(
            MasterTable::class,
            'ptm_gender_id',
            'm_id'
        );
    }


    public static function getProfessionalTax($stateId, $salary, $gender = 'all', $cycleId = null)
    {
        return self::where('ptm_s_id', $stateId)
            ->where('ptm_income_from', '<=', $salary)
            ->where(function ($query) use ($salary) {
                $query->where('ptm_income_to', '>=', $salary)
                      ->orWhereNull('ptm_income_to'); // For open-ended slabs
            })
            ->where(function ($query) use ($gender) {
                $query->where('ptm_gender', $gender)
                      ->orWhere('ptm_gender', 'all'); // Gender-neutral slabs
            })
            ->when($cycleId, function ($query) use ($cycleId) {
                return $query->where('ptm_cycle_id', $cycleId);
            })
            ->first();
    }
}
