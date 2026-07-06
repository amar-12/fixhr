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

class AssetsReplaceExport implements FromCollection, WithHeadings, WithStyles
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

        $query = Asset::with(['assetType.brand', 'assetType.category', 'fh_replacedBy'])->where('status', 'replaced')->where('assets_b_id',$b_id);

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
            $query->whereBetween('replace_date', [$startDate, $endDate]);
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
                'Asset Brand' => $asset->assetType->brand->br_name ?? 'N/A',
                'Replaced By' => $asset->assetType->name ?? 'N/A',
                'Replace With Asset Tag' => $asset->replacedBy->asset_tag ?? 'N/A',
                'Replace Date' => $asset->replace_date ? Carbon::parse($asset->replace_date)->format('d-M-y') : 'N/A',
                'Status' => $asset->status ?? 'N/A',
            ];
        })->values()->all();

        return collect($assets);
    }

    public function headings(): array
    {
        return [
            ['Replacement Report'],
            ['S No.', 'Asset Tag', 'Model No.', 'Serial No.', 'Asset Type', 'Asset Category', 'Asset Brand', 'Replaced By', 'Replace With Asset Tag', 'Replace Date', 'Status']
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Merge cells for the title row (A1:J1 for 10 columns)
        $sheet->mergeCells('A1:J1');

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
            'A3:J' . $sheet->getHighestRow() => [
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
