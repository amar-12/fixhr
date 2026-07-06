<?php

namespace App\Exports;

use App\Models\Asset;
use App\Models\AssetService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class AssetsServiceExport implements FromCollection, WithHeadings, WithStyles
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $user = Auth::user();
        $b_id = $user->emp_b_id;
        $filters = $this->filters;

        // dd($filters);

        $query = AssetService::with(['employee.fh_branch', 'employee.fh_department', 'employee.fh_designation', 'fh_asset.assetType.brand', 'fh_asset.assetType.category', 'fh_branch',])->where('assets_b_id', $b_id)
            ->whereIn('service_status', ['in_service', 'Requested'])->where('assets_b_id',$b_id);

        // -------------------- FILTERS -------------------- //
        if (!empty($filters['assetType'])) {
            $query->where('asset_type_id', $filters['assetType']);
        }

        if (!empty($filters['assetTag'])) {
            $query->where('asset_id', $filters['assetTag']);
        }

        if (!empty($filters['category'])) {
            $query->whereHas('fh_asset.assetType', fn($q) => $q->where('category_id', $filters['category']));
        }

        if (!empty($filters['brand'])) {
            $query->whereHas('fh_asset.assetType', fn($q) => $q->where('brand_id', $filters['brand']));
        }

        if (!empty($filters['vendorName'])) {
            $query->where('vendor_name', $filters['vendorName']);
        }

        if (!empty($filters['modelNumber'])) {
            $query->whereHas('fh_asset', fn($q) => $q->where('id', $filters['modelNumber']));
        }

        if (!empty($filters['serialNumber'])) {
            $query->whereHas('fh_asset', fn($q) => $q->where('id', $filters['serialNumber']));
        }

        if (!empty($filters['employee'])) {
            $query->whereHas('employee', fn($q) => $q->where('emp_id', $filters['employee']));
        }

        if (!empty($filters['designation'])) {
            $query->whereHas('employee', fn($q) => $q->where('emp_dg_id', $filters['designation']));
        }

        if (!empty($filters['department'])) {
            $query->whereHas('employee', fn($q) => $q->where('emp_d_id', $filters['department']));
        }

        if (!empty($filters['branch'])) {
            $query->whereHas('employee', fn($q) => $q->where('emp_br_id', $filters['branch']));
        }

        if (!empty($filters['serviceType'])) {
            $query->where('service_type', $filters['serviceType']);
        }

        if (!empty($filters['serviceLocation'])) {
            $query->where('service_location', $filters['serviceLocation']);
        }

        if (!empty($filters['dateRange'])) {
            $dates = explode(' - ', $filters['dateRange']);
            $startDate = \Carbon\Carbon::parse($dates[0])->startOfDay();
            $endDate   = \Carbon\Carbon::parse($dates[1])->endOfDay();

            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $data = $query->get();

        $assets = $data->map(function ($asset, $index) {
            return [
                'S No.' => $index + 1,
                'Emp Code' => $asset->employee?->emp_code ?? 'N/A',
                'Emp Name' => $asset->employee?->emp_full_name ?? 'N/A',
                'Designation' => $asset->employee?->fh_designation?->dg_name ?? 'N/A',
                'Department' => $asset->employee?->fh_department?->d_name ?? 'N/A',
                'Branch' => $asset->employee?->fh_branch?->br_name ?? 'N/A',
                'Asset Tag' => $asset->fh_asset?->asset_tag ?? 'N/A',
                'Model No.' => $asset->fh_asset?->model_number ?? 'N/A',
                'Serial No.' => $asset->fh_asset?->serial_number ?? 'N/A',
                'Asset Type' => $asset->fh_asset?->assetType?->name ?? 'N/A',
                'Asset Category' => $asset->fh_asset?->assetType?->category?->ac_name ?? 'N/A',
                'Service Type' => $asset->service_type ?? 'N/A',
                'Service Location' => $asset->fh_branch->br_name ?? 'Local',
                'Courier Name' => $asset->courier_name ?? 'N/A',
                'Docket No.' => $asset->docket_no ?? 'N/A',
                'Start Date' => $asset->service_start_date ? \Carbon\Carbon::parse($asset->service_start_date)->format('d-M-y') : 'N/A',
                'Return Date' => $asset->service_end_date ? \Carbon\Carbon::parse($asset->service_end_date)->format('d-M-y') : 'N/A',
                'Service Vendor' => $asset->vendor_name ?? 'N/A',
                'Service Cost' => $asset->service_cost ?? 'N/A',
                'Issue Description' => $asset->issue_description ?? 'N/A',
                'Status' => $asset->service_status ?? 'N/A',
            ];
        })->values()->all();

        return collect($assets);
    }


    public function headings(): array
    {
        return [
            ['Assets in Service / Maintenance Report'],
            ['S No.', 'Emp Code', 'Emp Name', 'Designation', 'Department', 'Branch', 'Asset Tag', 'Model No.', 'Serial No.', 'Asset Type', 'Asset Category', 'Service Type', 'Service Location', 'Courier Name', 'Docket No.', 'Start Date', 'Return Date', 'Service Vendor', 'Service Cost', 'Issue Description', 'Status']
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Merge cells for the title row (A1:U1 for 21 columns)
        $sheet->mergeCells('A1:U1');

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
            'A3:U' . $sheet->getHighestRow() => [
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
