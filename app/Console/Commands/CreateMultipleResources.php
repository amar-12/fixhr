<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class CreateMultipleResources extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:multiple-resources {resources*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create multiple resources at once';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $resources = $this->argument('resources');

        foreach ($resources as $resource) {
            $this->info("Creating resource: $resource");
            Artisan::call("make:resource", ['name' => $resource]);
        }

        $this->info('All resources created successfully.');
        return 0;
    }
}
