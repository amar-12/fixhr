<?php

namespace App\Services\DemoSetup\Contracts;

use App\Models\Business;
use App\Services\DemoSetup\DemoSetupService;

interface DemoSetupStep
{
    public function key(): string;

    public function shouldRun(Business $business): bool;

    public function run(Business $business, DemoSetupService $service): void;
}
