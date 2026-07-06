<?php

namespace App\Observers;

use App\Helpers\ApprovalHelper;
use App\Models\TadaRequestPlan;
use App\Models\MasterTable;

class TadaRequestPlanObserver
{
    /**
     * Handle the TadaRequestPlan "created" event.
     */
    public function created(TadaRequestPlan $plan): void
    {
        $plan->refresh();//Refresh the model instance to load the default values from the database
        $exeTypeId = MasterTable::where(['m_group'=>'EXECUTION_ON', 'm_id'=> 166])->pluck('m_id')->first(); //166 == 'Create' or 167=='Update'
        ApprovalHelper::checkRuleCriteriaModule(true, $exeTypeId, $plan->fh_policy_tada_travel_type->pttt_type_id, $plan->fh_policy_tada_travel_type->pttt_approval_type_id, $plan->trp_id, $plan->trp_am_id, $plan->trp_request_status, $plan->trp_advance_allowance, $plan);
    }

    /**
     * Handle the TadaRequestPlan "updated" event.
     */
    public function updated(TadaRequestPlan $tadaRequestPlan): void
    {
        //
    }

    /**
     * Handle the TadaRequestPlan "deleted" event.
     */
    public function deleted(TadaRequestPlan $tadaRequestPlan): void
    {
        //
    }

    /**
     * Handle the TadaRequestPlan "restored" event.
     */
    public function restored(TadaRequestPlan $tadaRequestPlan): void
    {
        //
    }

    /**
     * Handle the TadaRequestPlan "force deleted" event.
     */
    public function forceDeleted(TadaRequestPlan $tadaRequestPlan): void
    {
        //
    }
}
