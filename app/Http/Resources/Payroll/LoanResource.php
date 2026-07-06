<?php

namespace App\Http\Resources\Payroll;

use Illuminate\Http\Request;
use App\Helpers\ApprovalHelper;
use App\Models\ProcessApprover;
use App\Http\Resources\MasterTableResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Approval\Travel\ApprovalLogApiResource;
use Illuminate\Support\Carbon;

class LoanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'lnr_id' => $this->lnr_id,
            'lnr_emp_id' => $this->lnr_emp_id,
            'lnr_b_id' => $this->lnr_b_id,
            'company_name' => optional($this->fh_business)->b_name,
            'emp_id' => optional($this->fh_employee)->emp_id,
            'emp_full_name' => optional($this->fh_employee)->emp_full_name,
            'company_address' => optional($this->fh_business)->b_address,
            'lnr_requested_amount' => number_format($this->lnr_requested_amount, 2, '.', ''), // decimal
            'lnr_installment_amount' => number_format($this->lnr_installment_amount, 2, '.', ''), // decimal
            'lnr_installments' => $this->lnr_installments,
            'lnr_advance_type' =>  $this->lnr_advance_type,
            'lnr_start_date' => $this->lnr_start_date ? Carbon::parse($this->lnr_start_date)->format('Y-m-d') : null, // date only
            'lnr_description' => $this->lnr_description,
            'lnr_unique_id' => $this->lnr_unique_id,
            'lnr_request_subject' => $this->lnr_request_subject,
            'lnr_request_status' => $this->lnr_request_status,
            'lnr_am_id' => $this->lnr_am_id,
            'lnr_stage_completed' => $this->lnr_stage_completed,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d') : null, // date only
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d') : null, // date only
            'loan_request_pdf_url' => url('/api/admin/loan/loan_request_pdf/' . md5($this->lnr_id))
        ];
    }






}
