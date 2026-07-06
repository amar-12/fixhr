<?php

namespace App\Http\Requests\Policy;

use Illuminate\Foundation\Http\FormRequest;

class TravelAllowanceRequest extends FormRequest
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
            'allowance_category_id' => 'required',
            'allowance_travelmode_id' => 'required',
            'allowance_traveltype_id' => 'required',
            'eligibility' => 'required',
            'other_eligibility' => 'required',
            'remarks' => 'nullable'
        ];
    }
}
