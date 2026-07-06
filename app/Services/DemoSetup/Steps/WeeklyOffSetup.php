<?php

namespace App\Services\DemoSetup\Steps;

use App\Models\Business;
use App\Models\PolicyWeekOff;
use App\Services\DemoSetup\Contracts\DemoSetupStep;
use App\Services\DemoSetup\DemoSetupService;

class WeeklyOffSetup implements DemoSetupStep
{
    public function key(): string
    {
        return 'weekly_policy';
    }

    public function shouldRun(Business $business): bool
    {
        return !PolicyWeekOff::where('pwo_b_id', $business->b_id)->exists();
    }

    public function run(Business $business, DemoSetupService $service): void
    {
        $input = $service->getInput($this->key()) ?? [];

        // Default Sunday setup
        $sundayId = 327;

        $dayIds = $input['day_ids'] ?? [$sundayId];

        $recurrence = $input['recurrence'] ?? [
            $sundayId => [334, 335, 336, 337, 338], // 1st to 5th week
        ];

        $unpaidDays = $input['unpaid_days'] ?? [];

        PolicyWeekOff::create([
            'pwo_b_id' => $business->b_id,
            'pwo_name' => $input['name'] ?? 'Demo Weekly Policy',
            'pwo_day_ids' => json_encode($dayIds),
            'pwo_recurrence_day_ids' => json_encode($recurrence),
            'pwo_is_unpaid' => json_encode($unpaidDays),
        ]);
    }
}
