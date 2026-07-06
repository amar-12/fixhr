<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdhocComponent extends Model
{
      use HasFactory;

    protected $table = 'adhoc_components';
    protected $primaryKey = 'ac_id';

    protected $fillable = [
        'ac_adhoc_heading_id',
        'ac_adhoc_business_id',
        'ac_adhoc_component_name',
    ];

    // Relationship to the master table (payroll headings)
    public function payrollHeading()
    {
        return $this->hasMany(MasterTable::class, 'm_id', 'ac_adhoc_heading_id');
    }
    // AdhocComponent.php
    // public function payrollHeading()
    // {
    //     return $this->hasMany(AdhocComponent::class, 'ac_adhoc_heading_id', 'ac_id');
    // }

    public function transactionDetails()          // one component → many detail rows
    {
        return $this->hasMany(
            AdhocTransactionDetail::class,
            'component_id',        // FK on detail table
            'ac_id'
        );              // PK on component
    }

}
