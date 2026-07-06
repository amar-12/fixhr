<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\TadaReimburse;
use App\Models\TadaClaim;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class TadaSetalmentImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        // dd($rows);
        $user = Auth::user();
        $businessId = $user->emp_b_id;
        foreach ($rows as $index => $row) {
            $row = $row->toArray();
            $claim_id     = $row['claim_id'] ?? null;
            $amount     = $row['amount'] ?? null; 
            $status     = $row['status'] ?? null;

            $claim = TadaClaim::where('tc_b_id', $businessId) 
                ->where('tc_unique_id', $claim_id)
                ->where('tc_payed_amount', $amount)
                ->first();
            if (!$claim) {
                continue;
            }

            $reimburse = TadaReimburse::whereJsonContains('tr_claims_id', $claim->tc_id)->first();
            if ($reimburse && $status === 'Paid') {
                $claim->tc_paid_status = 1;
                $claim->tc_is_payed = 1;
                $claim->tc_status = 412;
                $claim->transaction_date = now();
                $claim->reference_no = "N/A";
                $claim->save();

                $reimburse->tr_status = 1;
                $reimburse->tr_date = Now();
                $reimburse->save();
            }
        }
    }
}
