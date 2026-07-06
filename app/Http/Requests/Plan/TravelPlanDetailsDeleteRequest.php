<?php

namespace App\Http\Requests\Plan;

use Illuminate\Foundation\Http\FormRequest;

class TravelPlanDetailsDeleteRequest extends FormRequest
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
            'trp_details' => 'required',
            'trp_details.*.trd_name' => 'required',
            'trp_details.*.mode_id' => 'required',
            'trp_details.*.vehicle_id' => 'required',
            'trp_details.*.source' => 'nullable',
            'trp_details.*.destination' => 'nullable',
            'trp_details.*.start_date' => 'nullable',
            'trp_details.*.end_date' => 'nullable',
            'trp_details.*.document' => 'nullable',
            'trp_details.*.segments' => 'nullable',
            'trp_details.*.segments.*.latitude' => 'nullable',
            'trp_details.*.segments.*.longitude' => 'nullable',
            'trp_details.*.segments.*.time' => 'nullable',
            'trp_details.*.start_time' => 'nullable',
            'trp_details.*.end_time' => 'nullable',
            'trp_details.*.total_distance' => 'nullable',
            'trp_details.*.total_tolerance' => 'nullable',
            'trp_details.*.call_id' => 'nullable',
            'trp_details.*.status' => 'nullable',
            'trp_details.*.remarks' => 'nullable',
            'trp_details.*.purpose' => 'nullable',
            'trp_details.*.ticket_type' => 'nullable',
            'trp_details.*.amount' => 'nullable'
        ];

    }
}
