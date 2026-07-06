<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Faker\Factory as Faker;

class EmployeeDetailsHeaderExport implements FromArray, WithHeadings
{
    protected $headers;
    protected $type;

    public function __construct(array $headers, $type)
    {
        $this->headers = $headers;
        $this->type = $type;
    }

    public function headings(): array
    {
        return [$this->headers]; // First row = headers
    }

    public function array(): array
    {
        $faker = Faker::create();
        $row = [];

        switch ($this->type) {
            case 'epf_esic':
                $row = [
                    'EMP001',               // Emp Code
                    'Yes',                 // PF Enable
                    'TRUST123',            // PF Trust Code
                    'Yes',                 // Pension Fund Member
                    'PF12345678',          // PF Number
                    'UAN1234567890',       // Universal Account Number
                    '12',                  // VPF(%)
                    "'01/01/2025'",          // PF DOJ
                    "'01/01/2050'",          // PF DOL
                    'Resigned',            // PF Reason
                    'Yes',                 // ESIC Enable
                    'ESIC987654',          // ESIC No
                    'Delhi Center',        // ESIC Dispensary
                    "'01/01/2025'",          // ESIC DOJ
                    "'01/01/2050'",          // ESIC DOL
                    'Transfer',            // ESIC Reason
                    'LIC',                 // Insured By
                    'INS123456789',        // Insurance No
                    "'01/01/2025'",          // Valid From
                    "'01/01/2050'"           // Valid Thru
                ];
                break;

            case 'identity':
                $row = [
                    'EMP001',                         // Emp Code
                    $faker->numerify('##########'),   // Aadhar
                    'DL-' . $faker->bothify('#######'), // DL
                    'EL-' . $faker->bothify('######'),  // Election Card
                    'P' . $faker->bothify('#######'),   // Passport
                    $faker->bothify('?????####?'),      // PAN
                    $faker->bankAccountNumber           // Bank AC
                ];
                break;

            case 'bank_details':
                $row = [
                    'John Doe',                            
                    'EMP001',                              
                    'Bank',                       
                    'ACC123',                              
                    $faker->bothify('ABCD0####'),          
                    'HDFC Bank',                           
                    'Main Branch',                         
                    '110001',                              
                    'BR001',                               
                    $faker->bankAccountNumber,             
                    'Salary Account',                             
                ];
                break;
        }

        return [$row];
    }
}
