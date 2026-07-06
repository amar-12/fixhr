<?php

namespace App\Exports;

use App\Models\Asset;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AssetsExport implements FromCollection, WithHeadings, WithStyles
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
        $query = Asset::with(['assetType.brand', 'assetType.category'])->where('assets_b_id', $b_id);

        if (!empty($filters['assetType'])) {
            $query->where('asset_type_id', $filters['assetType']);
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

        if (!empty($filters['dateRange'])) {
            $dates = explode(' - ', $filters['dateRange']);
            $startDate = \Carbon\Carbon::parse($dates[0])->startOfDay();
            $endDate   = \Carbon\Carbon::parse($dates[1])->endOfDay();

            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $data = $query->get();




        $assets = $data->groupBy('asset_type_id')
            ->values() // important: re-index groups from 0
            ->map(function ($group, $index) {
                $assetType = $group->first()->assetType;
                return [
                    'S_No' => $index + 1, // now auto increment from 1
                    'Asset_Type' => $assetType->name ?? 'N/A',
                    'Asset_Category' => $assetType->category->ac_name ?? 'N/A',
                    'Brand' => $assetType->brand->br_name ?? 'N/A',
                    'Total_Assets' => $group->whereIn('status', ['stock', 'assigned', 'Return'])->count(),
                    'In_Stock' => $group->where('status', 'stock')->count(),
                    'Assigned' => $group->whereIn('status', ['assigned', 'Return'])->count(),
                    'In_Service' => $group->whereIn('status', ['Service', 'Requested'])->count(),
                    'Scrapped' => $group->where('status', 'scrap')->count(),
                    'Replaced' => $group->where('status', 'replaced')->count(),
                ];
            })
            ->all();




        return collect($assets);
    }



    public function headings(): array
    {
        return [
            ['Asset Summary Report'],
            ['S.No.', 'Asset Type', 'Asset Category', 'Brand', 'Total Assets', 'In Stock', 'Assigned', 'In Service', 'Scrapped', 'Replaced']
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
