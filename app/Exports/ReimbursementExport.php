<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class ReimbursementExport implements FromCollection, WithHeadings
{
    protected $type;

    public function __construct($type = 'reimbursement')
    {
        $this->type = $type;
    }

    public function collection()
    {
        if ($this->type === 'settlement') {
            return new Collection([
                [
                    's_no'        => 1,
                    'claim_id'    => 'TCAA0016',
                    'emp_code'    => 'TS106',
                    'emp_name'    => 'Nilam',
                    'account_no'  => '63330201004785852',
                    'ifsc_code'   => 'SBIN0055',
                    'amount'      => -1208,
                    'status'      => 'Unpaid',
                ],
                [
                    's_no'        => 2,
                    'claim_id'    => 'TCAA0017',
                    'emp_code'    => 'TS107',
                    'emp_name'    => 'Yash Rastogi',
                    'account_no'  => '63330201004785852',
                    'ifsc_code'   => 'SBIN0055',
                    'amount'      => -8648,
                    'status'      => 'Paid',
                ],
            ]);
        }

        // default reimbursement format
        return new Collection([
            [
                'transaction_date' => '15-Sep-25',
                'claim_id'         => 'TCAA0163',
                'emp_code'         => 'FD001',
                'emp_name'         => 'Paras Patil',
                'account_no'       => '85451254784545',
                'ifsc_code'        => 'INDB0000758',
                'amount'           => 340,
                'cd_flag'          => 'D',
                'reference_no'     => 'HDFCN52025051638029667',
                'branch_name'      => 'NETBANK MUMBAI',
            ],
            [
                'transaction_date' => '16-Sep-25',
                'claim_id'         => 'TCAA0163',
                'emp_code'         => 'FD001',
                'emp_name'         => 'Paras Patil',
                'account_no'       => '85451254784545', // ✅ added missing account_no
                'ifsc_code'        => 'INDB0000758',
                'amount'           => 500,
                'cd_flag'          => 'D',
                'reference_no'     => 'HDFCN52025051638029678',
                'branch_name'      => 'NETBANK MUMBAI',
            ],
        ]);
    }

    public function headings(): array
    {
        if ($this->type === 'settlement') {
            return [
                'S.No',
                'Claim ID',
                'Emp Code',
                'Emp Name',
                'Account No.',
                'IFSC Code',
                'Amount',
                'Status',
            ];
        }

        return [
            'Transaction Date',
            'Claim ID',
            'Emp Code',
            'Emp Name',
            'Account No.',
            'IFSC Code',
            'Amount',
            'C.D.Flag',
            'Reference No',
            'Branch Name',
        ];
    }
}
