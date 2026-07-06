<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class CreateMultipleRequest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:multiple-requests {requests*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create multiple requests at once';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $requests = $this->argument('requests');

        foreach ($requests as $request) {
            $this->info("Creating request: $request");
            Artisan::call("make:request", ['name' => $request]);
        }

        $this->info('All requests created successfully.');
        return 0;
    }
}
