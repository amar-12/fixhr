<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\AdhocComponent;
use App\Models\AdhocTransaction;
use App\Models\AdhocTransactionDetail;
use App\Models\PayrollPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;

class AdhocComponentSampleImport implements ToCollection
{
    protected $businessId;

    public function __construct($businessId)
    {
        $this->businessId = $businessId;
    }

    /**
     * Normalize string for header comparison
     */
    private function normalize($string)
    {
        return strtolower(
            preg_replace('/[\s\-_]+/', '-', trim($string))
        );
    }

    /**
     * Process Excel rows
     */
    public function collection(Collection $rows)
    {
        if ($rows->count() < 3) {
            Log::warning("❌ Import failed: Excel file must have at least 3 rows.");
            return;
        }

        // Row 1 - Payroll period info
        $payrollPeriodName = trim($rows[0][1] ?? '');
        if (empty($payrollPeriodName)) {
            Log::warning("❌ Payroll Period not found in B1.");
            return;
        }

        $payrollPeriod = PayrollPeriod::where('pp_name', $payrollPeriodName)
            ->where('pp_b_id', $this->businessId)
            ->first();

        if (!$payrollPeriod) {
            Log::warning("❌ Payroll Period not found in DB: {$payrollPeriodName}");
            return;
        }

        $headers = $rows[1]->map(fn($h) => $this->normalize($h))->toArray();

        // Detect EARNING or DEDUCTION meta type
        $metaType = null;
        if (isset($rows)) {
            $metaRow = $rows->take(3)->flatten();
            foreach ($metaRow as $value) {
                if (stripos($value, 'deduction') !== false) {
                    $metaType = 'DEDUCTION';
                    break;
                }
                if (stripos($value, 'earning') !== false) {
                    $metaType = 'EARNING';
                    break;
                }
            }
        }


        $components = AdhocComponent::where('ac_adhoc_business_id', $this->businessId)
            ->get()
            ->mapWithKeys(function ($comp) use ($metaType) {
                $type = strtoupper(trim($metaType ?? $comp->ac_type ?? ''));
                if (!in_array($type, ['EARNING', 'DEDUCTION'])) {
                    $type = 'EARNING';
                }
                return [
                    $this->normalize($comp->ac_adhoc_component_name) => [
                        'id'   => $comp->ac_id,
                        'type' => $type,
                    ]
                ];
            });

        // Loop data rows (Row 3+)
        for ($i = 2; $i < $rows->count(); $i++) {
            $row = $rows[$i];
            if ($row->filter()->isEmpty()) continue; // skip empty rows

            $employeeCode = trim($row[0] ?? '');
            if (empty($employeeCode)) continue;

            $employee = Employee::where('emp_code', $employeeCode)
                ->where('emp_b_id', $this->businessId)
                ->first();

            if (!$employee) {
                Log::warning("⚠️ Row " . ($i + 1) . ": Employee not found: {$employeeCode}");
                continue;
            }

            // Create or find Adhoc Transaction
            $adhocTransaction = AdhocTransaction::firstOrCreate([
                'at_b_id'    => $this->businessId,
                'at_emp_id'  => $employee->emp_id,
                'at_emp_d_id' => $employee->emp_d_id,
                'at_pp_id'   => $payrollPeriod->pp_id,
            ]);

            $totalEarning   = 0;
            $totalDeduction = 0;
            $remarks        = null;

            // Loop through each column (header)
            foreach ($headers as $columnIndex => $header) {

                if (in_array($header, ['employee-code', 'employee-name', 'payment-in', 'remarks'])) {
                    if ($header === 'remarks') {
                        $remarks = trim($row[$columnIndex] ?? '');
                    }
                    continue;
                }

                $value = $row[$columnIndex] ?? null;
                if (is_null($value) || $value === '') continue;

                $component = $components[$header] ?? null;
                if (!$component) {
                    Log::warning("⚠️ Row " . ($i + 1) . ": Unknown component '{$header}'");
                    continue;
                }
                $amount = floatval($value);
                if ($amount == 0) continue;
                // Handle EARNING or DEDUCTION
                if ($component['type'] === 'EARNING') {
                    $totalEarning += $amount;
                    AdhocTransactionDetail::updateOrCreate(
                        [
                            'adhoc_transaction_id' => $adhocTransaction->at_id,
                            'component_id'         => $component['id'],
                        ],
                        [
                            'earning_amount' => $amount,
                            'remarks'        => $remarks,
                        ]
                    );
                } elseif ($component['type'] === 'DEDUCTION') {
                    $totalDeduction += $amount;
                    AdhocTransactionDetail::updateOrCreate(
                        [
                            'adhoc_transaction_id' => $adhocTransaction->at_id,
                            'component_id'         => $component['id'],
                        ],
                        [
                            'deduction_amount' => $amount,
                            'remarks'          => $remarks,
                        ]
                    );
                }
            }
            if ($component['type'] === 'EARNING') {
                $adhocTransaction->update([
                    'at_e_amount' => $totalEarning,
                ]);
            } elseif ($component['type'] === 'DEDUCTION') {
                $adhocTransaction->update([
                    'at_d_amount' => $totalDeduction,
                ]);
            }
            Log::info("✅ Row " . ($i + 1) . ": Imported successfully for employee {$employeeCode}");
        }
    }
}