<?php

namespace App\Exports;

use App\Models\Asset;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AssetsAssignedExport implements FromCollection, WithHeadings, WithStyles
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function collection()


    {
        $filters = $this->filters;

        $user = Auth::user();
        $b_id = $user->emp_b_id;
        $query = Asset::with(['employee', 'employee.fh_branch', 'employee.fh_department', 'employee.fh_designation', 'assetType.brand', 'assetType.category', 'assignedby'])
            ->where('status', 'assigned')->where('assets_b_id', $b_id);

        if (!empty($filters['assetType'])) {
            $query->where('asset_type_id', $filters['assetType']);
        }

        if (!empty($filters['category'])) {
            $query->whereHas('assetType', function ($q) use ($filters) {
                $q->where('category_id', $filters['category']);
            });
        }

        if (!empty($filters['assetTag'])) {
            $query->where('id', $filters['assetTag']);
        }

        if (!empty($filters['brand'])) {
            $query->whereHas('assetType', function ($q) use ($filters) {
                $q->where('brand_id', $filters['brand']);
            });
        }

        if (!empty($filters['vendorName'])) {
            $query->where('id', $filters['vendorName']);
        }

        if (!empty($filters['modelNumber'])) {
            $query->where('id', $filters['modelNumber']);
        }

        if (!empty($filters['serialNumber'])) {
            $query->where('id', $filters['serialNumber']);
        }

        if (!empty($filters['employee'])) {
            $query->whereHas('employee', function ($q) use ($filters) {
                $q->where('emp_id', $filters['employee']);
            });
        }

        if (!empty($filters['designation'])) {
            $query->whereHas('employee', function ($q) use ($filters) {
                $q->where('emp_dg_id', $filters['designation']);
            });
        }

        if (!empty($filters['department'])) {
            $query->whereHas('employee', function ($q) use ($filters) {
                $q->where('emp_d_id', $filters['department']);
            });
        }

        if (!empty($filters['branch'])) {
            $query->whereHas('employee', function ($q) use ($filters) {
                $q->where('emp_br_id', $filters['branch']);
            });
        }

        if (!empty($filters['dateRange'])) {
            $dates = explode(' - ', $filters['dateRange']);
            $startDate = Carbon::parse($dates[0])->startOfDay();
            $endDate = Carbon::parse($dates[1])->endOfDay();
            $query->whereBetween('assigned_at', [$startDate, $endDate]);
        }

        $data = $query->get();

        // dd($data);
        $assets = $data->map(function ($asset, $index) {
            return [
                'S No.' => $index + 1,
                'Emp Code' => $asset->employee->emp_code ?? 'N/A',
                'Emp Name' => $asset->employee->emp_full_name ?? 'N/A',
                'Designation' => $asset->employee->fh_designation->dg_name ?? 'N/A',
                'Department' => $asset->employee->fh_department->d_name ?? 'N/A',
                'Branch' => $asset->employee->fh_branch->br_name ?? 'N/A',
                'Asset Tag' => $asset->asset_tag ?? 'N/A',
                'Brand' => $asset->assetType->brand->br_name ?? 'N/A',
                'Specification' => $asset->assetType->description ?? 'N/A',
                'Model No.' => $asset->model_number ?? 'N/A',
                'Serial No.' => $asset->serial_number ?? 'N/A',
                'Asset Type' => $asset->assetType->name ?? 'N/A',
                'Asset Category' => $asset->assetType->category->ac_name ?? 'N/A',
                'Warranty Expiry Date' => $asset->purchase_date && $asset->warranty_months ? Carbon::parse($asset->purchase_date)->addMonths($asset->warranty_months)->format('d-M-y') : 'N/A',
                'AMC Expiry Date' => $asset->amc_expiry_date ? Carbon::parse($asset->amc_expiry_date)->format('d-M-y') : 'N/A',
                'Assigned By' => $asset->assignedby->emp_full_name ?? 'N/A',
                'Assigned Date' => $asset->assigned_at ? Carbon::parse($asset->assigned_at)->format('d-M-y') : 'N/A',
                'Status' => $asset->status ?? 'N/A',
            ];
        })->values()->all();

        return collect($assets);
    }
    public function headings(): array
    {
        return [
            ['Assigned Asset Report'],
            ['S No.', 'Emp Code', 'Emp Name', 'Designation', 'Department', 'Branch', 'Asset Tag', 'Brand', 'Specification', 'Model No.', 'Serial No.', 'Asset Type', 'Asset Category', 'Warranty Till', 'AMC Till', 'Assigned By', 'Assigned Date', 'Status']
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Merge cells for the title row (A1:R1 for 18 columns)
        $sheet->mergeCells('A1:R1');

        return [
            // Style for the title (row 1)
            1 => [
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
            ],
            // Style for headers (row 2)
            2 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F81BD']
                ],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ]
            ],
            // Style for data rows (3 onwards)
            'A3:R' . $sheet->getHighestRow() => [
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ]
            ]
        ];
    }
}
