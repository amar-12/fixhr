<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class ErrorExport implements FromArray
{
    protected $errors;

    public function __construct(array $errors)
    {
        $this->errors = $errors;
    }

    public function array(): array
    {
        // Optionally, you can format the errors to include headers, like 'Row Number', 'Error Description'
        $formattedErrors = [['Row Number', 'Error Description']]; // Add headers

        // Loop through each error and format it into a row
        foreach ($this->errors as $index => $error) {
            $formattedErrors[] = [$index + 1, $error];  // Row number, error message
        }

        return $formattedErrors;
    }
}
