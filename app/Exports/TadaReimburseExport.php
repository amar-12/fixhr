<?php

namespace App\Exports;

use App\Models\TadaReimburse;
use App\Models\TadaClaim;
use App\Models\Employee;
use App\Models\PaymentMode;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TadaReimburseExport implements FromCollection, WithHeadings, WithStyles
{
    protected int $id;
    protected ?TadaReimburse $reimburse;
    protected ?PaymentMode $paymentMode;

    public function __construct(int $id)
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Unauthorized');
        }

        $this->id = $id;
        $this->reimburse = TadaReimburse::where('tr_id', $id)->where('tr_b_id', $user->emp_b_id)->firstOrFail();
        $this->paymentMode = Auth::user() ? PaymentMode::where('pm_b_id', Auth::user()->emp_b_id)->first() : null;
    }

    public function collection()
    {
        if (!$this->reimburse) {
            return collect([]);
        }

        $claimIds = $this->getClaimIds();
        $claims = $this->fetchClaims($claimIds);
        if ($claims->isEmpty()) {
            return collect([]);
        }

        $empData = $this->fetchEmployeeData($claims);
        return $this->buildRows($claims, $empData);
    }

    protected function getClaimIds(): array
    {
        $claimIds = $this->reimburse->tr_claims_id;
        if (is_string($claimIds)) {
            $claimIds = json_decode($claimIds, true) ?? [];
        }
        return Arr::flatten((array) $claimIds);
    }

    protected function fetchClaims(array $claimIds)
    {
        $businessId = Auth::user()->emp_b_id;

        return TadaClaim::whereIn('tc_id', $claimIds)
            ->where('tc_b_id', $businessId)
            ->select('tc_id', 'tc_emp_id', 'tc_unique_id', 'tc_payed_amount', 'tc_status', 'tc_paid_status')
            ->get();
    }


    protected function fetchEmployeeData($claims)
    {
        $businessId = Auth::user()->emp_b_id;

        return Employee::whereIn('emp_id', $claims->pluck('tc_emp_id'))
            ->where('emp_b_id', $businessId)
            ->select('emp_id', 'emp_code', 'emp_full_name', 'emp_bank_ifsc_code', 'emp_bank_account_no')
            ->get()
            ->keyBy('emp_id');
    }


    protected function buildRows($claims, $empData)
    {
        $rows = [];
        foreach ($claims as $index => $claim) {
            $emp = $empData->get($claim->tc_emp_id) ?? (object) ['emp_code' => '', 'emp_full_name' => ''];
            $row = [
                'S.No' => $index + 1,
                'Claim ID' => $claim->tc_unique_id ?? '',
                'Emp Code' => $emp->emp_code,
                'Emp Name' => $emp->emp_full_name,
            ];

            $this->addPaymentDetails($row, $claim, $emp);
            $rows[] = $row;
        }
        return collect($rows);
    }


    protected $totalAmount = 0;
    protected function addPaymentDetails(array &$row, $claim, $emp)
    {

        // dd($claim);
        $amount = $claim->tc_payed_amount ?? 0;
        $this->totalAmount += $amount; // total collect करना

        if (!$this->paymentMode) {
            $row['Amount'] = $amount;
            return;
        }

        switch ($this->paymentMode->pm_mode) {
            case 'Cheque':
                $row['Payment Mode'] = 'Cheque';
                $row['Cheque No.'] = $this->reimburse->tr_cheque_no ?? '0000001';
                $row['Amount'] = $amount;
                $row['Status'] = $claim->tc_paid_status == 1 ? 'Paid' : 'Unpaid';
                break;

            case 'Cash':
                $row['Payment Mode'] = 'Cash';
                $row['Amount'] = $amount;
                $row['Status'] = $claim->tc_paid_status == 1 ? 'Paid' : 'Unpaid';
                break;

            case 'Bank Transfer':
                // ✅ Force as string so Excel won’t auto-format
                $row['Account No.'] = " " . ($emp->emp_bank_account_no ?? '');
                $row['IFSC Code']  = $emp->emp_bank_ifsc_code ?? '';
                $row['Amount']     = $amount;
                $row['Status'] = $claim->tc_paid_status == 1 ? 'Paid' : 'Unpaid';
                break;
        }
    }


    protected function convertNumberToWords($number)
    {
        $formatter = new \NumberFormatter("en", \NumberFormatter::SPELLOUT);
        return ucfirst($formatter->format($number));
    }


    public function headings(): array
    {
        if (!$this->paymentMode) {
            return ['S.No', 'Emp Code', 'Claim ID', 'Emp Name', 'Amount'];
        }

        return match ($this->paymentMode->pm_mode) {
            'Cheque' => ['S.No',  'Claim ID','Emp Code', 'Emp Name', 'Payment Mode', 'Cheque No.', 'Amount', 'Status'],
            'Cash' => ['S.No', 'Claim ID','Emp Code', 'Emp Name', 'Amount', 'Status'],
            'Bank Transfer' => ['S.No',  'Claim ID','Emp Code', 'Emp Name', 'Account No.', 'IFSC Code', 'Amount', 'Status'],
            default => ['S.No', 'Emp Code', 'Claim ID', 'Emp Name', 'Amount', 'Status'],
        };
    }

    public function styles(Worksheet $sheet)
    {

        $sheet->insertNewRowBefore(1, 8);

        $sheet->mergeCells('A1:G1');

        if ($this->totalAmount < 0) {
            $sheet->setCellValue('A1', 'TADA Settlement Report');
        } else {
            $sheet->setCellValue('A1', 'TADA Reimburse Report');
        }


        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        $sheet->setCellValue('B2', 'Reimburse ID:');
        $sheet->setCellValue('C2', ($this->reimburse->tr_unique_id ?? ''));

        $sheet->setCellValue('F2', 'Status:');
        // $sheet->setCellValue('G2', ($this->reimburse->tr_status ?? ''));

        $status = $this->reimburse->tr_status ?? null;

        if ($status === 0) {
            $sheet->setCellValue('G2', 'Pending');
        } elseif ($status === 1) {
            $sheet->setCellValue('G2', 'Approved');
        } else {
            $sheet->setCellValue('G2', '');
        }


        $sheet->setCellValue('B3', 'Month & Year:');
        $sheet->setCellValue('C3', ($this->reimburse->created_at ?? ''));
        $sheet->setCellValue('C3', $this->reimburse->created_at ? date('d M Y', strtotime($this->reimburse->created_at)) : '');


        $sheet->setCellValue('F3', 'Reimburse Date:');
        $sheet->setCellValue('G3', $this->reimburse->created_at ? date('d M Y', strtotime($this->reimburse->created_at)) : '');

        $sheet->setCellValue('B4', 'Payment Mode:');
        $sheet->setCellValue('C4', ($this->paymentMode->pm_mode ?? ''));


        // $sheet->setCellValue('B5', 'Batch ID:');
        // $sheet->setCellValue('C5', ($this->reimburse->tr_group_id ?? ''));


        if (!empty($this->paymentMode) && strtolower($this->paymentMode->pm_mode) === 'cheque') {
            $sheet->setCellValue('F4', 'Cheque Date:');
            $sheet->setCellValue('G4', $this->cheque_date ?? date('d M Y'));
        } else {
            $sheet->setCellValue('F4', 'Payment Date:');
            $sheet->setCellValue('G4', $this->payment_date ?? date('d M Y'));
        }

        if (!empty($this->paymentMode) && $this->paymentMode->pm_mode === 'Cheque') {
            $sheet->setCellValue('F5', 'Payment Confirmation:');
            $sheet->setCellValue('G5', $this->paymentMode->pm_mode);
        } else {
            $sheet->setCellValue('F5', '');
            $sheet->setCellValue('G5', '');
        }


        $sheet->getStyle('A9:G9')->getFont()->setBold(true);

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $lastRow = $sheet->getHighestRow() + 1;
        $sheet->mergeCells("C" . ($lastRow + 1) . ":D" . ($lastRow + 1));

        $sheet->setCellValue("B" . ($lastRow + 1), 'In words:');
        $sheet->setCellValue("C" . ($lastRow + 1), $this->convertNumberToWords($this->totalAmount));

        if ($this->paymentMode->pm_mode === 'Bank Transfer') {
            $sheet->setCellValue("F" . ($lastRow + 1), 'Total:');
            $sheet->setCellValue("G" . ($lastRow + 1), $this->totalAmount);
        } elseif ($this->paymentMode->pm_mode === 'Cash') {
            $sheet->setCellValue("E" . ($lastRow + 1), 'Total:');
            $sheet->setCellValue("F" . ($lastRow + 1), $this->totalAmount);
        } else {
            $sheet->setCellValue("F" . ($lastRow + 1), 'Total:');
            $sheet->setCellValue("G" . ($lastRow + 1), $this->totalAmount);
        }

        $sheet->getStyle("A{$lastRow}:G{$lastRow}")->getFont()->setBold(true);



        return [9 => ['font' => ['bold' => true]]];
    }
}
