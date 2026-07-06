<?php

namespace App\Http\Requests\Policy;

use Illuminate\Foundation\Http\FormRequest;

class TravelDailyAllowanceLodgingRequest extends FormRequest
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
            'category_id' => 'required',
            'city_type_id' => 'required',
            'da_eligibility' => 'required',
            'same_day_eligibility' => 'required',
            'remark' => 'nullable',
            'eligibility_bill' => 'required',
            'eligibility_no_bill' => 'required'
        ];
    }
}
