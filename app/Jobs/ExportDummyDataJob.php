<?php

// app/Jobs/ExportDummyDataJob.php

namespace App\Jobs;

use Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Exports\DummyDataExport;

class ExportDummyDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $fileName;

    // Constructor to pass the necessary data
    public function __construct($fileName)
    {
        $this->fileName = $fileName;
    }

    // Handle method to process the job
    public function handle()
    {
        // Log::info("Starting the dummy data export");

        // Prepare dummy data
        $dummyData = [
            ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'],
            ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com'],
            ['id' => 3, 'name' => 'Bob Brown', 'email' => 'bob@example.com'],
        ];

        // Create export object and export data to Excel
        $export = new DummyDataExport($dummyData);
        Excel::store($export, $this->fileName, 'local');

        // \Log::info("Dummy data exported successfully to: " . $this->fileName);
    }
}
