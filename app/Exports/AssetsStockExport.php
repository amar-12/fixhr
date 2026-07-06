<?php

namespace App\Exports;

use App\Models\Asset;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AssetsStockExport implements FromCollection, WithHeadings, WithStyles
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


        // dd($filters);

        $query = Asset::with(['assetType.brand', 'assetType.category'])->where('status', 'stock')->where('assets_b_id',$b_id);

        if (!empty($filters['assetType'])) {
            $query->where('asset_type_id', $filters['assetType']);
        }

        if (!empty($filters['assetTag'])) {
            $query->where('id', $filters['assetTag']);
        }

        if (!empty($filters['category'])) {
            $query->whereHas('assetType', function ($q) use ($filters) {
                $q->where('category_id', $filters['category']);
            });
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

        if (!empty($filters['dateRange'])) {
            $dates = explode(' - ', $filters['dateRange']);
            $startDate = Carbon::parse($dates[0])->startOfDay();
            $endDate = Carbon::parse($dates[1])->endOfDay();

            $query->whereBetween('purchase_date', [$startDate, $endDate]);
        }

        $data = $query->get();

        $assets = $data->map(function ($asset, $index) {
            return [
                'S No.' => $index + 1,
                'Asset Tag' => $asset->asset_tag ?? 'N/A',
                'Model No.' => $asset->model_number ?? 'N/A',
                'Serial No.' => $asset->serial_number ?? 'N/A',
                'Asset Type' => $asset->assetType->name ?? 'N/A',
                'Asset Category' => $asset->assetType->category->ac_name ?? 'N/A',
                'Brand' => $asset->assetType->brand->br_name ?? 'N/A',
                'Vendor' => $asset->vendor_name ?? 'N/A',
                'Purchase Date' => $asset->purchase_date ? Carbon::parse($asset->purchase_date)->format('d-M-y') : 'N/A',
                'Warranty Expiry Date' => $asset->purchase_date && $asset->warranty_months ? Carbon::parse($asset->purchase_date)->addMonths($asset->warranty_months)->format('d-M-y') : 'N/A',
                'Amount' => $asset->purchase_value ?? 'N/A',
                'AMC Expiry Date' => $asset->amc_expiry_date ? Carbon::parse($asset->amc_expiry_date)->format('d-M-y') : 'N/A',
                'Status' => ucfirst($asset->status) ?? 'N/A',
            ];
        })->values()->all();

        return collect($assets);
    }



    public function headings(): array
    {
        return [
            ['Asset Stock Report'],
            ['S No.', 'Asset Tag', 'Model No.', 'Serial No.', 'Asset Type', 'Asset Category', 'Asset Brand', 'Vendor', 'Purchase Date', 'Warranty Expiry Date', 'Amount', 'AMC Expiry Date', 'Status']
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Merge cells for the title row (A1:M1 for 13 columns)
        $sheet->mergeCells('A1:M1');

        return [
            // Style for the title (row 1)
            1 => [
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
            ],
            // Style for headers (row 2)
            2 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F81BD']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ]
            ],
            // Style for data rows (3 onwards)
            'A3:M' . $sheet->getHighestRow() => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ]
            ]
        ];
    }
}
