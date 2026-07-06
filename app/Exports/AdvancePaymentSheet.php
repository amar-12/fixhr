<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Style;

class AdvancePaymentSheet implements FromCollection, WithStyles
{
    protected $data;
    protected $user;

    public function __construct($data, $user)
    {
        $this->data = $data;
        $this->user = $user;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $exportData = [];

        // Add the additional information at the top
        $exportData[] = [' ', $this->user->fh_business->b_name];
        $exportData[] = [' ', 'HQ Address : ' . $this->user->emp_permanent_address];
        $exportData[] = [' ', 'Plant Address : ' . $this->user->emp_temporary_address];
        $exportData[] = [' ', 'Phone No : ' . $this->user->emp_phone,  'Email : ' . $this->user->emp_email];
        $exportData[] = [' '];
        $exportData[] = [' ', 'Advance Payment Sheet'];
        $exportData[] = [' '];
        $exportData[] = [' ','Advance Payment Sheet As On Date', date('d-M-Y')];
        $exportData[] = [' ','Extracted By', $this->user->emp_full_name];
        $exportData[] = [' ']; // Empty row for spacing
        $exportData[] = [' ']; // Empty row for spacing

        $tableHeaders[] = [
            'S. No.',
            'C',
            'Beneficiary Code',
            'Beneficiary Account Number',
            'Instrument Amount',
            'Beneficiary Name',
            'Drawee Location',
            'Print Location',
            'Bene Address 1',
            'Bene Address 2',
            'Bene Address 3',
            'Bene Address 4',
            'Bene Address 5',
            'Instruction Reference Number',
            'Customer Reference Number',
            'Payment details 1',
            'Payment details 2',
            'Payment details 3',
            'Payment details 4',
            'Payment details 5',
            'Payment details 6',
            'Payment details 7',
            'Cheque Number',
            'Chq / Trn Date',
            'MICR Number',
            'IFC Code',
            'Bene Bank Name',
            'Bene Bank Branch Name',
            'Beneficiary email id'
        ];

        $exportData[] = $tableHeaders;

        $i = 1;
        foreach ($this->data as $item) {

            $rowData = [
                'S. No.' => $i++,
                'C' => 'N',
                'Beneficiary Code' => '',
                'Beneficiary Account Number' => isset($item->fh_employee) ? $item->fh_employee->emp_bank_account_no : '',
                'Instrument Amount' => isset($item->trp_advance_allowance) ? $item->trp_advance_allowance : '',
                'Beneficiary Name' => isset($item->fh_employee) ? $item->fh_employee->emp_full_name : '',
                'Drawee Location' => '',
                'Print Location' => '',
                'Bene Address 1' => '',
                'Bene Address 2' => '',
                'Bene Address 3' => '',
                'Bene Address 4' => '',
                'Bene Address 5' => '',
                'Instruction Reference Number' => '',
                'Customer Reference Number' =>  isset($item->fh_employee) ? 'ADV - ' .$item->fh_employee->emp_fname : '',
                'Payment details 1' => '',
                'Payment details 2' => '',
                'Payment details 3' => '',
                'Payment details 4' => '',
                'Payment details 5' => '',
                'Payment details 6' => '',
                'Payment details 7' => '',
                'Cheque Number' => '',
                'Chq / Trn Date' => '27-03-2024',
                'MICR Number' => isset($item->fh_employee) ? $item->fh_employee->emp_bank_micr_code : '',
                'IFC Code' => isset($item->fh_employee) ? $item->fh_employee->emp_bank_ifsc_code : '',
                'Bene Bank Name' => isset($item->fh_employee) ? $item->fh_employee->emp_bank_name : '',
                'Bene Bank Branch Name' => isset($item->fh_employee) ? $item->fh_employee->emp_bank_branch_name : '',
                'Beneficiary email id' => isset($item->fh_employee) ? $item->fh_employee->emp_email : '',
            ];
            $item->update(['trp_adv_payment_processed'=>1]);
            $exportData[] = $rowData;
        }
        return collect($exportData);
    }

    public function backgroundColor()
    {

    }

    public function defaultStyles(Style $sheet)
    {
        $sheet = [
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_TOP,
            ],
            'font' => [
                'bold' => false,
                'size' => 10,
            ],
            'quotePrefix' => true
        ];

        return $sheet;
    }

    public function styles($sheet)
    {
        /********************* Start For Common ***********************************/

        $lastRow = $sheet->getHighestRow();
        $lastColumn = $sheet->getHighestColumn();

        $sheet->freezePane('A13');

        $borderRange = 'A12:' . $lastColumn.$lastRow;
        $borderRange1 = 'A'.($lastRow).':' . $lastColumn.$lastRow;

        $sheet->getStyle($borderRange)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        $sheet->getStyle($borderRange1)->applyFromArray([
            'borders' => [
                'right' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        $sheet->getStyle($borderRange)->getAlignment()->setWrapText(true);

        $sheet->getStyle($borderRange)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);

        /********************* End For Common ***********************************/

        /********************* Start For Top Header ***********************************/

        $sheet->getRowDimension(1)->setRowHeight(30);
        $sheet->getRowDimension(6)->setRowHeight(30);

        $sheet->getColumnDimension('C')->setAutoSize(true);

        $sheet->getStyle('A1:G1')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'font' => [
                'bold' => true,
                'size' => 10,
                // 'italic' => true,
            ],
        ]);

        $sheet->getStyle('B8')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
            ],
        ]);

        $sheet->getStyle('B9')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
            ],
        ]);

        $sheet->getStyle('C8')->applyFromArray([
            'font' => [
                'bold' => false,
                'size' => 11,
            ],
        ]);

        $sheet->getStyle('C9')->applyFromArray([
            'font' => [
                'bold' => false,
                'size' => 11,
            ],
        ]);


        $sheet->mergeCells('B1:H1');
        $sheet->getStyle('B1:H1')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'font' => [
                'bold' => true,
                'size' => 13,
            ],
        ]);

        $sheet->mergeCells('B2:H2');
        $sheet->getStyle('B2:H2')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'font' => [
                // 'bold' => true,
                'size' => 11,
            ],
        ]);

        $sheet->getStyle('B3')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'font' => [
                // 'bold' => true,
                'size' => 11,
            ],
        ]);

        $sheet->getStyle('B4')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'font' => [
                // 'bold' => true,
                'size' => 11,
            ],
        ]);

        $sheet->getStyle('B5')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'font' => [
                'bold' => true,
                'size' => 11,
            ],
        ]);

        $sheet->getStyle('B6')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
        ]);
        $sheet->mergeCells('B3:H3');
        $sheet->mergeCells('B4:H4');
        // $sheet->mergeCells('E5:K5');
        $sheet->mergeCells('B6:H6');

        /********************* End For Top Header ***********************************/

        /********************* Start For Table *************************************/

        $sheet->getRowDimension(12)->setRowHeight(30);

        $sheet->getStyle('A12:'.$lastColumn.'12')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'font' => [
                'bold' => true,
                'size' => 12,
                // 'italic' => true,
            ],
        ]);
        $sheet->getStyle('A12:'.$lastColumn.'12')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('89CFF0');

        $sheet->setAutoFilter('A12:'.$lastColumn . $lastRow);

        return $sheet;
    }
}
