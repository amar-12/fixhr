<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\TadaReimburse;
use App\Models\TadaClaim;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TadaReimburseImport implements ToCollection, WithHeadingRow
{
    protected $updatedCount = 0;

    public function collection(Collection $rows)
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;

        DB::beginTransaction();

        try {

            if ($rows->isEmpty()) {
                throw new \Exception('Uploaded file is empty.');
            }

            foreach ($rows as $row) {

                $row = $row->toArray();

                $account_no   = trim($row['account_no'] ?? $row['cheque_no'] ?? '');
                $ifsc         = trim($row['ifsc_code'] ?? '');
                $amount       = isset($row['amount'])
                    ? number_format((float)str_replace(',', '', $row['amount']), 2, '.', '')
                    : null;
                $status       = strtoupper(trim($row['cdflag'] ?? ''));
                $reference_no = trim($row['reference_no'] ?? '');

                if (!$account_no || !$ifsc || !$amount) {
                    continue;
                }

                // Transaction Date Parse
                $transaction_date = null;
                if (!empty($row['transaction_date'])) {
                    $value = $row['transaction_date'];

                    if (is_numeric($value)) {
                        $transaction_date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
                    } else {
                        $transaction_date = \Carbon\Carbon::parse($value)->format('Y-m-d');
                    }
                }

                // Employee Find
                $emp = Employee::whereRaw('TRIM(emp_bank_account_no) = ?', [$account_no])
                    ->where('emp_bank_ifsc_code', $ifsc)
                    ->select('emp_id')
                    ->first();

                if (!$emp) {
                    continue;
                }

                // Claim Find
                $claim = TadaClaim::where('tc_b_id', $businessId)
                    ->where('tc_emp_id', $emp->emp_id)
                    ->where('tc_paid_status', 0)
                    ->whereRaw('ROUND(tc_payed_amount,2) = ?', [$amount])
                    ->orderBy('tc_id', 'asc')
                    ->lockForUpdate()
                    ->first();

                if (!$claim) {
                    continue;
                }

                if ($status === 'D') {

                    if ($claim->tc_paid_status == 1) {
                        continue;
                    }

                    $claim->update([
                        'tc_paid_status'   => 1,
                        'tc_is_payed'      => 1,
                        'tc_status'        => 412,
                        'transaction_date' => $transaction_date,
                        'reference_no'     => $reference_no
                    ]);

                    TadaReimburse::whereJsonContains('tr_claims_id', $claim->tc_id)
                        ->update([
                            'tr_status' => 1,
                            'tr_date'   => now()
                        ]);

                    $this->updatedCount++;
                }
            }

            // 🔥 If nothing updated
            if ($this->updatedCount == 0) {
                throw new \Exception('No matching records found. Please check file data.');
            }

            DB::commit();
        } catch (\Exception $e) {

            DB::rollBack();

            \Log::error('Excel Payment Upload Error: ' . $e->getMessage());

            throw new \Exception($e->getMessage());
        }
    }
}
