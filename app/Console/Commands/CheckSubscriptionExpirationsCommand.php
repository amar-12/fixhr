<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\CheckSubscriptionExpirations;

class CheckSubscriptionExpirationsCommand extends Command
{
    protected $signature = 'subscriptions:check-expirations';
    protected $description = 'Check and process subscription expirations';

    public function handle(): void
    {
        $this->info('🚀 Dispatching subscription expiration check job...');
        
        CheckSubscriptionExpirations::dispatch();
        
        $this->info('✅ Job dispatched successfully!');
    }
}