<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Jobs\CloneDatabaseStructure;

class CreateBusinessDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-business-database {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new business-specific database and dispatch a job to clone table structure.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dbName = $this->argument('name');
        $timestamp = now()->format('Ymd_His');
        $dbNameWithTimestamp = "{$dbName}_{$timestamp}";

        // Create the new database
        DB::statement("CREATE DATABASE `$dbNameWithTimestamp` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        // Dispatch the cloning job
        CloneDatabaseStructure::dispatch($dbNameWithTimestamp);

        $this->info("✅ Database '$dbNameWithTimestamp' created successfully.");
        $this->info("🛠️ Cloning job dispatched to queue.");
    }
}
