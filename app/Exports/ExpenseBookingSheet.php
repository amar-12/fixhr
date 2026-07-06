<?php

namespace App\Exports;

use App\Models\TadaClaim;
use App\Models\TadaRequestDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Style;
use Carbon\Carbon;

class ExpenseBookingSheet implements FromCollection, WithStyles
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
        $exportData[] = [' ', 'Expense Report Sheet'];
        $exportData[] = [' '];
        $exportData[] = [' ','Expense Report Sheet As On Date', date('d-M-Y')];
        $exportData[] = [' ','Extracted By', $this->user->emp_full_name];
        $exportData[] = [' ']; // Empty row for spacing
        $exportData[] = [' ']; // Empty row for spacing

        $tableHeaders[] = [
            'S. No.',
            'Same Voucher Number',
            'Document Date',
            'Document Type',
            'Company Code',
            'Posting Date',
            'Posting Period',
            'Currency',
            'Tax code',
            'Posting Key',
            'Account',
            'Text',
            'Profit Center',
            'Exp Amount',
            'Baseline Date',
            'Reason code',
            'Cost Center',
            'Order',
            'Partner Profit Ctr',
            'Trading Partner',
            'Document Header Text',
            'Assignment',
            'Employee Name',
            'Reference',
            'Reference key 1',
            'Reference key 2',
            'Terms of Payment',
            'Payment Method',
            'Segment'
        ];

        $exportData[] = $tableHeaders;

        $i = 1;

        $additionalExpense = ['Travel Conveyance'=>['expense_code'=>40,'expense_name'=>'TA', 'expense_amount'=>0], 'Daily allowance'=>['expense_code'=>40,'expense_name'=>'DA', 'expense_amount'=>0], 'Advance'=>['expense_name'=>'Advance', 'expense_code'=>31, 'expense_amount'=>0], 'Settlement'=>['expense_name'=>'Settlement', 'expense_code'=>31, 'expense_amount'=>0]];

        foreach ($this->data as $item) {

            //TA
            $travelDetailSumAmt =  $item->fh_policy_tada_travel_type->fh_travel_type->m_id == 124 ?  $item->fh_tada_request_details->sum('trd_net_amount') : TadaRequestDetail::whereHas('fh_policy_tada_travel_vehicle', function($query) {
                $query->where('pttv_claim_type_id', 155);
            })->where('trd_trp_id', $item->trp_id)
            ->sum('trd_net_amount');

            foreach($item->fh_tada_expenses as $expense) {
                if($expense->te_amount){

                    $from_to_date = in_array($expense->te_type_id, [158, 159])  ? "\n" . ' (From:'. $expense->te_from_date .' - To: ' . $expense->te_to_date .')' : '';
                    $rowData = [
                        'S. No.' => $i++,
                        'Same Voucher Number' => $item->trp_id,
                        'Document Date' => Carbon::parse($item->fh_tada_claim->created_at)->format('Y-m-d'),
                        'Document Type' => 'KR',
                        'Company Code' => 8110,
                        'Posting Date' => '27-03-2024',
                        'Posting Period' => '',
                        'Currency' => 'INR',
                        'Tax code' => '',
                        'Posting Key' => 40,
                        'Account' => optional($expense->fh_sub_expense)->tes_code ?? $expense->te_type_id,
                        'Text' => (optional($expense->fh_sub_expense)->tes_head ?? optional($expense->fh_expense_type)->m_name ?? '') . $from_to_date,
                        'Profit Center' => 81101,
                        'Exp Amount' => $expense->te_amount ?  ($expense->te_amount - $expense->te_deviation) : 0,
                        'Baseline Date' => '',
                        'Reason code' => '',
                        'Cost Center' => isset($item->fh_employee) ? $item->fh_employee->emp_account_code : '',
                        'Order' => '',
                        'Partner Profit Ctr' => '',
                        'Trading Partner' => '',
                        'Document Header Text' => '',
                        'Assignment' => isset($item->fh_employee) ? $item->fh_employee->emp_code : '-',
                        'Employee Name' => isset($item->fh_employee) ? $item->fh_employee->emp_full_name : '',
                        'Reference' => $item->trp_unique_id,
                        'Reference key 1' => '',
                        'Reference key 2' => '',
                        'Terms of Payment' => '',
                        'Payment Method' => '',
                        'Segment' => ''
                    ];

                    $item->update(['trp_exp_payment_processed'=>1]);
                    $exportData[] = $rowData;
                }
            }

            // This is added to ensure all calculations match, as TA (Travel details, Tap Location), DA, and Advance are located in separate tables.
            foreach($additionalExpense as $key=>$adExpAmount){

                if($adExpAmount['expense_name'] == 'TA') {
                    $adExpAmount['expense_amount'] = $travelDetailSumAmt ?? 0;
                }
                if($adExpAmount['expense_name'] == 'DA') {
                    $adExpAmount['expense_amount'] = $item->fh_tada_claim->tc_da_amount ?? 0;
                }
                if($adExpAmount['expense_name'] == 'Advance') {
                    $adExpAmount['expense_amount'] = $item->trp_advance_allowance ?? 0;
                }
                if($adExpAmount['expense_name'] == 'Settlement') {
                    $adExpAmount['expense_amount'] = TadaClaim::where('tc_trp_id', $item->trp_id)->value('tc_payed_amount') ?? 0;
                }

                if($adExpAmount['expense_amount'] && $adExpAmount['expense_amount'] > 0) {

                    $account = ($adExpAmount['expense_code'] == 31) ? $item->fh_employee->emp_code : '';
                    $rowData = [
                        'S. No.' => $i++,
                        'Same Voucher Number' => $item->trp_id,
                        'Document Date' => '',  // Leave it empty or add an appropriate value
                        'Document Type' => 'KR',
                        'Company Code' => 8110,
                        'Posting Date' => '27-03-2024',
                        'Posting Period' => '',
                        'Currency' => 'INR',
                        'Tax code' => '',
                        'Posting Key' => $adExpAmount['expense_code'],
                        'Account' => $adExpAmount['expense_name'] == 'DA' ? 66022008 : $account, // You can modify this to fit the required value for travel details
                        'Text' => $key,
                        'Profit Center' => 81101,
                        'Exp Amount' => $adExpAmount['expense_amount'],
                        'Baseline Date' => '',
                        'Reason code' => '',
                        'Cost Center' => isset($item->fh_employee) ? $item->fh_employee->emp_account_code : '',
                        'Order' => '',
                        'Partner Profit Ctr' => '',
                        'Trading Partner' => '',
                        'Document Header Text' => '',
                        'Assignment' => isset($item->fh_employee) ? $item->fh_employee->emp_code : '-',
                        'Employee Name' => isset($item->fh_employee) ? $item->fh_employee->emp_full_name : '',
                        'Reference' => $item->trp_unique_id,
                        'Reference key 1' => '',
                        'Reference key 2' => '',
                        'Terms of Payment' => '',
                        'Payment Method' => '',
                        'Segment' => ''
                    ];
                    $exportData[] = $rowData;
                }

            }

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
