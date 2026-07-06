<?php

namespace App\Livewire\WeeklyPayroll;

use App\Livewire\WeeklyPayroll\Concerns\ResolvesWeeklyPayrollReportPeriod;
use App\Models\PayrollPeriod;
use App\Models\ProcessedEmployeeSalary;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class LetterHead extends Component
{
    use ResolvesWeeklyPayrollReportPeriod;

    public $selectedPayrollPeriodId;

    public $businessId;

    public $showButton = true;

    public $buttonStyle = '';

    /** @var int|null */
    public $selectedWeekId;

    public function mount($payrollId = null, $showButton = true, $buttonStyle = '', $weekId = null)
    {
        $user = Auth::user();
        $this->businessId = $user->emp_b_id;
        $this->selectedPayrollPeriodId = $payrollId;
        $this->showButton = $showButton;
        $this->buttonStyle = $buttonStyle;
        $this->selectedWeekId = $weekId;
    }

    public function generateLetterHead()
    {
        if (! $this->validatePayrollReportRequest([
            'selectedPayrollPeriodId' => 'required|exists:payroll_periods,pp_id',
        ], [
            'selectedPayrollPeriodId.required' => 'Payroll period is required.',
        ])) {
            return;
        }

        $payroll = $this->resolvePayrollForWeeklyReport();
        if (! $payroll) {
            return;
        }

        $processedCount = ProcessedEmployeeSalary::where('ps_payroll_id', $this->selectedPayrollPeriodId)
            ->where('ps_b_id', $this->businessId)
            ->when($this->selectedWeekId, fn ($q) => $q->where('ps_week_id', $this->selectedWeekId))
            ->count();

        if ($processedCount === 0) {
            $this->payrollReportSwalNoData('There is no data to download for the bank letter. No processed salary rows match this selection.');

            return;
        }

        // Get total salary amount
        $totalSalary = ProcessedEmployeeSalary::where('ps_payroll_id', $this->selectedPayrollPeriodId)
            ->when($this->selectedWeekId, fn ($q) => $q->where('ps_week_id', $this->selectedWeekId))
            ->sum('ps_monthly_net_salary');

        // Get business details
        $user = Auth::user();
        $business = $user->fh_business;

        // Get cheque number
        $chequeNumber = $this->getChequeNumber($this->selectedPayrollPeriodId);

        // Format date - 17/11/2025 format
        $today = Carbon::now()->format('d/m/Y');

        // Prepare data for Excel
        $letterData = [
            'bank_name' => $business->b_bank_name ?? 'IndusInd Bank Pvt. Ltd.',
            'date' => $today,
            'total_amount' => $totalSalary,
            'amount_in_words' => $this->numberToWords(round($totalSalary)),
            'cheque_number' => $chequeNumber,
            'company_name' => $business->b_name ?? 'Company Name',
        ];

        $fileName = 'Bank_Letter_'.str_replace(' ', '_', $business->b_name).str_replace(' ', '_', $payroll->pp_name).'.xlsx';

        return Excel::download(new SimpleLetterExport($letterData), $fileName);
    }

    private function getChequeNumber($payrollId)
    {
        // This is a placeholder - implement your own logic
        return '935541';
    }

    /**
     * Convert number to words (Indian numbering system)
     */
    private function numberToWords($number)
    {
        // Remove decimals if any
        $number = (int) $number;

        $ones = [
            0 => 'Zero',
            1 => 'One',
            2 => 'Two',
            3 => 'Three',
            4 => 'Four',
            5 => 'Five',
            6 => 'Six',
            7 => 'Seven',
            8 => 'Eight',
            9 => 'Nine',
            10 => 'Ten',
            11 => 'Eleven',
            12 => 'Twelve',
            13 => 'Thirteen',
            14 => 'Fourteen',
            15 => 'Fifteen',
            16 => 'Sixteen',
            17 => 'Seventeen',
            18 => 'Eighteen',
            19 => 'Nineteen',
        ];

        $tens = [
            2 => 'Twenty',
            3 => 'Thirty',
            4 => 'Forty',
            5 => 'Fifty',
            6 => 'Sixty',
            7 => 'Seventy',
            8 => 'Eighty',
            9 => 'Ninety',
        ];

        if ($number == 0) {
            return $ones[0];
        }

        $words = '';

        // Crore part
        if ($number >= 10000000) {
            $crore = floor($number / 10000000);
            $words .= $this->smallNumberToWords($crore).' Crore ';
            $number %= 10000000;
        }

        // Lakh part
        if ($number >= 100000) {
            $lakh = floor($number / 100000);
            $words .= $this->smallNumberToWords($lakh).' Lac ';
            $number %= 100000;
        }

        // Thousand part
        if ($number >= 1000) {
            $thousand = floor($number / 1000);
            $words .= $this->smallNumberToWords($thousand).' Thousand ';
            $number %= 1000;
        }

        // Hundred part
        if ($number >= 100) {
            $hundred = floor($number / 100);
            $words .= $this->smallNumberToWords($hundred).' Hundred ';
            $number %= 100;
        }

        // Tens and ones
        if ($number > 0) {
            if ($words != '') {
                $words .= '';
            }
            $words .= $this->smallNumberToWords($number);
        }

        return trim($words);
    }

    private function smallNumberToWords($number)
    {
        $ones = [
            0 => '',
            1 => 'One',
            2 => 'Two',
            3 => 'Three',
            4 => 'Four',
            5 => 'Five',
            6 => 'Six',
            7 => 'Seven',
            8 => 'Eight',
            9 => 'Nine',
            10 => 'Ten',
            11 => 'Eleven',
            12 => 'Twelve',
            13 => 'Thirteen',
            14 => 'Fourteen',
            15 => 'Fifteen',
            16 => 'Sixteen',
            17 => 'Seventeen',
            18 => 'Eighteen',
            19 => 'Nineteen',
        ];

        $tens = [
            2 => 'Twenty',
            3 => 'Thirty',
            4 => 'Forty',
            5 => 'Fifty',
            6 => 'Sixty',
            7 => 'Seventy',
            8 => 'Eighty',
            9 => 'Ninety',
        ];

        if ($number < 20) {
            return $ones[$number];
        } else {
            $ten = floor($number / 10);
            $unit = $number % 10;
            $words = $tens[$ten];
            if ($unit > 0) {
                $words .= ' '.$ones[$unit];
            }

            return $words;
        }
    }

    public function render()
    {
        return view('livewire.weekly-payroll.letter-head');
    }
}

// Simple Letter Export Class
class SimpleLetterExport implements FromCollection, WithEvents
{
    protected $letterData;

    public function __construct($letterData)
    {
        $this->letterData = $letterData;
    }

    public function collection()
    {
        return collect([]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Set page orientation to portrait
                $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
                $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);

                // Create simple letter starting from row 9 in column A
                $this->createSimpleLetter($sheet);

                // Show gridlines (Excel sheet format)
                $sheet->setShowGridlines(true);
            },
        ];
    }

    private function createSimpleLetter($sheet)
    {
        $data = $this->letterData;

        // Start from row 9, column A
        $startRow = 9;
        $col = 'A';

        // Format amount with Indian numbering (no decimals)
        $formattedAmount = number_format($data['total_amount'], 0);

        // Row 9: "To,"
        $sheet->setCellValue($col.$startRow, 'To,');
        $sheet->getStyle($col.$startRow)->getFont()->setSize(11);
        $sheet->getRowDimension($startRow)->setRowHeight(18);

        // Row 10: Bank Manager
        $sheet->setCellValue($col.($startRow + 1), 'The Branch Manager,');
        $sheet->getStyle($col.($startRow + 1))->getFont()->setSize(11);

        // Row 11: Bank Name
        $sheet->setCellValue($col.($startRow + 2), $data['bank_name']);
        $sheet->getStyle($col.($startRow + 2))->getFont()->setSize(11);

        // Empty row
        $sheet->getRowDimension($startRow + 3)->setRowHeight(8);

        // Row 13: Date
        $sheet->setCellValue($col.($startRow + 4), 'Date: '.$data['date']);
        $sheet->getStyle($col.($startRow + 4))->getFont()->setSize(11);
        $sheet->getRowDimension($startRow + 4)->setRowHeight(18);

        // Empty row
        $sheet->getRowDimension($startRow + 5)->setRowHeight(8);

        // Row 15: Salutation
        $sheet->setCellValue($col.($startRow + 6), 'Dear Sir/Madam,');
        $sheet->getStyle($col.($startRow + 6))->getFont()->setSize(11);
        $sheet->getRowDimension($startRow + 6)->setRowHeight(18);

        // Empty row
        $sheet->getRowDimension($startRow + 7)->setRowHeight(8);

        // Row 17: Letter Content - Now we'll use Rich Text for partial bold
        $letterContent = 'Kindly process our staff salary whose total amount is ';

        // Set initial text (not bold)
        $sheet->setCellValue($col.($startRow + 8), $letterContent);

        // Create rich text object for partial bold formatting
        $richText = new \PhpOffice\PhpSpreadsheet\RichText\RichText;

        // Add normal text part
        $normalPart = $richText->createTextRun('Kindly process our staff salary whose total amount is ');
        $normalPart->getFont()->setSize(11);

        // Add bold amount part
        $boldAmount = $richText->createTextRun('Rs.'.$formattedAmount.'/- ');
        $boldAmount->getFont()->setSize(11)->setBold(true);

        // Add normal "Inwords:" text
        $normalInwords = $richText->createTextRun('Inwords: ');
        $normalInwords->getFont()->setSize(11);

        // Add bold amount in words
        $boldWords = $richText->createTextRun($data['amount_in_words'].' only  , ');
        $boldWords->getFont()->setSize(11)->setBold(true);

        // Add normal "Cheque No" text
        $normalCheque = $richText->createTextRun('Cheque No ');
        $normalCheque->getFont()->setSize(11);

        // Add bold cheque number
        $boldChequeNo = $richText->createTextRun($data['cheque_number'].'.');
        $boldChequeNo->getFont()->setSize(11)->setBold(true);

        // Add final normal text
        $finalText = $richText->createTextRun(' Please do process it at the earliest.');
        $finalText->getFont()->setSize(11);

        // Set the rich text to cell
        $sheet->setCellValue($col.($startRow + 8), $richText);

        // Merge cells for proper display (A to E columns)
        $sheet->mergeCells($col.($startRow + 8).':E'.($startRow + 8));
        $sheet->getStyle($col.($startRow + 8))->getAlignment()->setWrapText(true);
        $sheet->getRowDimension($startRow + 8)->setRowHeight(40);

        // Empty row
        $sheet->getRowDimension($startRow + 9)->setRowHeight(10);

        // Row 27: Horizontal line (using border)
        $sheet->getStyle($col.($startRow + 10).':E'.($startRow + 10))
            ->getBorders()
            ->getTop()
            ->setBorderStyle(Border::BORDER_THIN);
        $sheet->getRowDimension($startRow + 10)->setRowHeight(5);

        // Row 28: Thanking you
        $sheet->setCellValue($col.($startRow + 11), 'Thanking you,');
        $sheet->getStyle($col.($startRow + 11))->getFont()->setSize(11);
        $sheet->getRowDimension($startRow + 11)->setRowHeight(20);

        // Empty row
        $sheet->getRowDimension($startRow + 12)->setRowHeight(10);

        // Row 30: Company Name (Bold)
        $sheet->setCellValue($col.($startRow + 13), $data['company_name']);
        $sheet->getStyle($col.($startRow + 13))->getFont()->setBold(true)->setSize(11);
        $sheet->getRowDimension($startRow + 13)->setRowHeight(20);

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(40); // Wider for better text wrapping
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);

        // Left align all content in column A
        $sheet->getStyle('A'.$startRow.':A'.($startRow + 13))
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT)
            ->setVertical(Alignment::VERTICAL_TOP);

        // Also align the merged content
        $sheet->getStyle('A'.($startRow + 8))
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT)
            ->setVertical(Alignment::VERTICAL_TOP);

        // Add sheet tabs at bottom
        $this->addSheetTabs($sheet);
    }

    private function addSheetTabs($sheet)
    {
        // Add sheet names at the bottom
        $sheet->setTitle('Bank LH');

        // Mention sheet tabs in row 50
        $sheet->setCellValue('A50', 'attendance   Worksheet   Bank sheet   Hold   Sheet3   SBI   Bank LH');
        $sheet->getStyle('A50')->getFont()->setSize(9)->getColor()->setRGB('666666');

        // Merge cells for sheet tabs
        $sheet->mergeCells('A50:E50');
        $sheet->getStyle('A50')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // Add a subtle background to simulate sheet tabs area
        $sheet->getStyle('A50:E50')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F2F2F2');
    }


}
