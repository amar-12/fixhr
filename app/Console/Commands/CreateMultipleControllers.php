<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class CreateMultipleControllers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:multiple-controllers {controllers*} {--api} {--resource}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create multiple controllers at once';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $controllers = $this->argument('controllers');
        $api = $this->option('api');
        $resource = $this->option('resource');

        foreach ($controllers as $controller) {
            $this->info("Creating controller: $controller");
            Artisan::call("make:controller", [
                'name' => $controller,
                '--api' => $api,
                '--resource' => $resource,
            ]);
        }

        $this->info('All controllers created successfully.');
        return 0;
    }
}
