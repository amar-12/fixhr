<?php

namespace App\Http\Requests\Payroll;

use Illuminate\Foundation\Http\FormRequest;

class LoanDetailsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'loan_id' => 'required',
            'loan_name' =>  'required',
            'loan_purpose' => 'required',
            'loan_amount' => 'required',
            'loan_installment_amount' => 'required',
            'loan_total_installmenets' => 'required',
  
        ];
    }
}
