<?php

namespace App\Http\Requests\Plan;

use Illuminate\Foundation\Http\FormRequest;

class TravelPlanDetailsRequest extends FormRequest
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
            'trp_name' =>  'required',
            'trp_purpose' => 'required',
            'trp_advance' => 'nullable',
            'trp_travel_type_id' => 'required',
            'trp_details' => 'nullable'
        ];
    }
}
