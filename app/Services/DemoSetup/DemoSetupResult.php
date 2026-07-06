<?php

namespace App\Services\DemoSetup;

class DemoSetupResult
{
    public function __construct(
        public array $executedSteps = [],
        public bool $alreadyCompleted = false
    ) {}

    public function hasRun(string $step): bool
    {
        return in_array($step, $this->executedSteps);
    }
}
