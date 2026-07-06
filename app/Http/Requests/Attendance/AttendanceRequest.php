<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequest extends FormRequest
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
            // 'date' => 'required',
            // 'policy' => 'nullable',
            // 'check_in' => 'required',
            // 'check_out' => 'nullable',
            // 'total_working_hour' => 'nullable',
            // 'late' => 'nullable',
            // 'late_duration' => 'nullable',
            // 'absent' => 'nullable',
            // 'overtime' => 'nullable',
            // 'overtime_hours' => 'nullable',
            // 'status' => 'required',
            // 'punchType'=>'required',
            'type_id' => 'required',
            'date'=>'required',
            'in_time'=>'nullable',
            'out_time'=>'nullable',
            'reason'=>'nullable',
            // 'approved_by'=>'required',
        ];
    }
}
