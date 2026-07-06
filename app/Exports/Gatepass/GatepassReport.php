<?php

namespace App\Exports\Gatepass;

use App\Http\Resources\Approval\Travel\ApprovalLogApiResource;
use App\Models\MasterTable;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class GatepassReport implements FromCollection, WithHeadings, WithColumnWidths, WithEvents
{
    protected $data;
    protected $filters;
    protected $fileName;
    protected $date;

    public function __construct($records, $filters, $fileName, $date)
    {
        $this->data = $records;
        $this->filters = $filters;
        $this->fileName = ucwords(str_replace(['_', '-'], ' ', $fileName)) . ' Report';
        $this->date = $date;
    }

    /* ------------------------------------------------------------------ */
    /*  COLLECTION – Build rows with serial number                        */
    /* ------------------------------------------------------------------ */
    public function collection()
    {
        $serialNumber = 1;
        return collect($this->data)->map(function ($item) use (&$serialNumber) {
            $requestedDate = !empty($item['gtp_date']) ? Carbon::parse($item['gtp_date'])->format('d-M-Y') : '-';
            $appliedDate = !empty($item['created_at']) ? Carbon::parse($item['created_at'])->format('d-M-Y') : '-';
            $appliedTime = !empty($item['created_at']) ? Carbon::parse($item['created_at'])->format('H:i') : '-';
            $inTime = !empty($item['gtp_in_time']) ? Carbon::parse($item['gtp_in_time'])->format('H:i') : '-';
            $outTime = !empty($item['gtp_out_time']) ? Carbon::parse($item['gtp_out_time'])->format('H:i') : '-';
            $approvedDate = !empty($item['fh_gatepass_confirmation']) ? Carbon::parse($item['fh_gatepass_confirmation']->gcp_date)->format('d-M-Y') : '-';
            $approvedOutTime = !empty($item['fh_gatepass_confirmation']) ? Carbon::parse($item['fh_gatepass_confirmation']->gcp_out_time)->format('H:i') : '-';
            $approvedInTime = !empty($item['fh_gatepass_confirmation']) ? Carbon::parse($item['fh_gatepass_confirmation']->gcp_in_time)->format('H:i') : '-';

            $row = [
                'S#' => $serialNumber++,
                'Emp Code' => data_get($item, 'fh_employees_details.emp_code', '-'),
                'Emp Name' => data_get($item, 'fh_employees_details.emp_full_name', '-'),
            ];

            if (!empty($this->filters['branch'])) {
                $row['Branch'] = data_get($item, 'fh_employees_details.fh_branch.br_name', '-');
            }
            if (!empty($this->filters['department'])) {
                $row['Department'] = data_get($item, 'fh_employees_details.fh_department.d_name', '-');
            }
            if (!empty($this->filters['designation'])) {
                $row['Designation'] = data_get($item, 'fh_employees_details.fh_designation.dg_name', '-');
            }
            if (!empty($this->filters['dealership'])) {
                $row['Dealer'] = data_get($item, 'fh_employees_details.fh_dealership.dlr_name', '-');
            }
            if (!empty($this->filters['grade'])) {
                $row['Grade'] = data_get($item, 'fh_employees_details.fh_grade.g_name', '-');
            }
            if (!empty($this->filters['jobStatus'])) {
                $row['Job Status'] = data_get($item, 'fh_employees_details.fh_job_status.m_name', '-');
            }
            if (!empty($this->filters['shift'])) {
                $row['Shift'] = data_get($item, 'fh_employees_details.fh_shift_type.pst_name', '-');
                $row['Shift Start Time'] = data_get($item, 'fh_employees_details.fh_shift_type.pst_start_time')
                    ? Carbon::parse(data_get($item, 'fh_employees_details.fh_shift_type.pst_start_time'))->format('H:i')
                    : '-';
                $row['Shift End Time'] = data_get($item, 'fh_employees_details.fh_shift_type.pst_end_time')
                    ? Carbon::parse(data_get($item, 'fh_employees_details.fh_shift_type.pst_end_time'))->format('H:i')
                    : '-';
            }

            $status = MasterTable::where('m_group', 'APPROVAL_STATUS')
                ->where('m_id', $item['gtp_status'])
                ->value('m_name') ?? '-';

            $approvals = ApprovalLogApiResource::collection($item->fh_plan_approval_log ?? collect())->all();
            $approvalName = $approvals[0]['fh_employee']['emp_full_name'] ?? '-';

            return array_merge($row, [
                'Applied Date' => $appliedDate,
                'Applied Time' => $appliedTime,
                'Requested Date' => $requestedDate,
                'Requested Out Time' => $outTime,
                'Requested In Time' => $inTime,
                'Reason' => $item['gtp_reason'] ?? '-',
                'Destination' => $item['gtp_destination'] ?? '-',
                'Status' => $status,
                'Approved By' => $approvalName,
                'Approved Date' => $approvedDate,
                'Approved In Time' => $approvedInTime,
                'Approved Out Time' => $approvedOutTime,
            ]);
        });
    }

    /* ------------------------------------------------------------------ */
    /*  HEADINGS – Dynamic based on filters                               */
    /* ------------------------------------------------------------------ */
    public function headings(): array
    {
        $headings = ['S#', 'Emp Code', 'Emp Name'];

        if (!empty($this->filters['branch']))       $headings[] = 'Branch';
        if (!empty($this->filters['department']))   $headings[] = 'Department';
        if (!empty($this->filters['designation']))  $headings[] = 'Designation';
        if (!empty($this->filters['dealership']))   $headings[] = 'Dealer';
        if (!empty($this->filters['grade']))        $headings[] = 'Grade';
        if (!empty($this->filters['jobStatus']))    $headings[] = 'Job Status';
        if (!empty($this->filters['shift'])) {
            $headings[] = 'Shift';
            $headings[] = 'Shift Start Time';
            $headings[] = 'Shift End Time';
        }

        return array_merge($headings, [
            'Applied Date', 'Applied Time', 'Requested Date',
            'Requested Out Time', 'Requested In Time', 'Reason',
            'Destination', 'Status', 'Approved By',
            'Approved Date', 'Approved In Time', 'Approved Out Time',
        ]);
    }

    /* ------------------------------------------------------------------ */
    /*  COLUMN WIDTHS – S# = 4, Emp Code = 4, Others = 13              */
    /* ------------------------------------------------------------------ */
    public function columnWidths(): array
    {
        $headings = $this->headings();
        $widths   = [];

        foreach ($headings as $i => $heading) {
            $col = Coordinate::stringFromColumnIndex($i + 1);
            $widths[$col] = match ($heading) {
                'S#'    => 4,
                'Emp Code' => 7,
                default    => 13,
            };
        }

        return $widths;
    }

    /* ------------------------------------------------------------------ */
    /*  AFTER-SHEET – Full styling (same as Absent/Leave reports)        */
    /* ------------------------------------------------------------------ */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet       = $event->sheet->getDelegate();
                $headings    = $this->headings();
                $lastCol     = Coordinate::stringFromColumnIndex(count($headings));
                $headingRow  = 6; // After 5 header rows
                $dataRows    = $this->collection()->count();
                $lastDataRow = $headingRow + $dataRows;

                // Insert 5 rows at top
                $sheet->insertNewRowBefore(1, 5);
                $sheet->setShowGridlines(false);

                // === HEADER CONTENT ===
                $businessName = $this->data->isNotEmpty()
                    ? ($this->data[0]['fh_employees_details']['fh_branch']['br_name'] ?? 'Business')
                    : 'Business';
                $printedOn = Carbon::now()->format('d-M-Y h:i A T');

                $sheet->setCellValue('A1', $businessName);
                $sheet->setCellValue('A2', $this->fileName);
                $sheet->setCellValue('A3', 'Date: ' . $this->date);
                $sheet->setCellValue('A4', "Printed on: {$printedOn}");

                foreach (range(1, 4) as $r) {
                    $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
                    $sheet->getStyle("A{$r}")->getFont()->setSize(11)->setBold(true);
                    $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                // === HEADING ROW ===
                $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")
                    ->getFont()->setSize(10)->setBold(true)->getColor()->setRGB('FFFFFF');
                $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")
                    ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('263871');
                $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);
                $sheet->getRowDimension($headingRow)->setRowHeight(30); // Fit wrapped headings

                if ($dataRows > 0) {
                    // === DATA ROWS – Fixed height 25 ===
                    for ($r = $headingRow + 1; $r <= $lastDataRow; $r++) {
                        $sheet->getRowDimension($r)->setRowHeight(25);
                    }

                    // === BORDERS ===
                    $sheet->getStyle("A{$headingRow}:{$lastCol}{$lastDataRow}")
                        ->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D3D3D3');

                    // === CENTER + WRAP ALL DATA ===
                    $sheet->getStyle("A" . ($headingRow + 1) . ":{$lastCol}{$lastDataRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setWrapText(true);

                    // === FONT SIZE ===
                    $sheet->getStyle("A" . ($headingRow + 1) . ":{$lastCol}{$lastDataRow}")
                        ->getFont()->setSize(9);

                    // === ALTERNATING ROWS ===
                    for ($r = $headingRow + 1; $r <= $lastDataRow; $r++) {
                        $bg = (($r - $headingRow) % 2 == 0) ? 'F5F5F5' : 'FFFFFF';
                        $sheet->getStyle("A{$r}:{$lastCol}{$r}")
                            ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($bg);
                    }
                }

                // === FREEZE PANE ===
                $sheet->freezePane('D7');
            },
        ];
    }
}