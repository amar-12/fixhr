<?php

namespace App\Services\DemoSetup\Steps;

use App\Models\Business;
use App\Models\PolicyLeave;
use App\Models\LeaveType;
use App\Services\DemoSetup\Contracts\DemoSetupStep;
use App\Services\DemoSetup\DemoSetupService;

class LeavePolicySetup implements DemoSetupStep
{
    public function key(): string
    {
        return 'leave_policy';
    }

    public function shouldRun(Business $business): bool
    {
        return !PolicyLeave::where('pl_b_id', $business->b_id)->exists();
    }

    public function run(Business $business, DemoSetupService $service): void
    {
        $input = $service->getInput($this->key()) ?? [];

        $policy = PolicyLeave::create([
            'pl_b_id' => $business->b_id,
            'pl_name' => $input['name'] ?? 'Default Leave Policy',
            'pl_effective_date' => null,
            'pl_expire_date' => null,
            'pl_upl_applicable' => $input['upl'] ?? 0,
            'pl_limit_check' => $input['limit'] ?? 0,
            'pl_limit_before' => null,
            'pl_limit_after' => 0,
        ]);

        $categories = $input['categories'] ?? [
            [
                'cat_type_id' => 208, // Sick
                'cycle_id' => 219,
                'days' => 0.5,
                'carry_forward' => 0.5,
            ],
            [
                'cat_type_id' => 207, // Casual
                'cycle_id' => 219,
                'days' => 1,
                'carry_forward' => 1,
            ],
        ];

        foreach ($categories as $category) {
            LeaveType::create([
                'lvt_pl_id' => $policy->pl_id,
                'lvt_cat_type_id' => $category['cat_type_id'],
                'lvt_leave_cycle_id' => $category['cycle_id'],
                'lvt_days_per_year' => $category['days'],
                'lvt_unused_leave_rule_id' => 221, // Carry forward
                'lvt_leave_accrual_rate' => null,
                'lvt_carry_forward' => $category['carry_forward'],
                'lvt_applicable_to_id' => 223, // All
                'lvt_is_sandwich' => 0,
                'lvt_el_per_period' => null,
                'lvt_encashable' => 0,
            ]);
        }
    }
}
