<?php

namespace App\Http\Requests\Approval;

use Illuminate\Foundation\Http\FormRequest;

class RuleCriteriaApiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'b_id' => 'required',
            'am_id' => 'required',
            'approval_rule_id' => 'required',
            'rule_condition_id' => 'required',
            'condition_option_id' => 'required',
            'custom_value' => 'required',
        ];
    }
}
