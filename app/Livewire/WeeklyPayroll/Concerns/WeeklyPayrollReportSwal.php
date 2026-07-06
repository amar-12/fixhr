<?php

namespace App\Livewire\WeeklyPayroll\Concerns;

use Illuminate\Validation\ValidationException;

trait WeeklyPayrollReportSwal
{
    protected function payrollReportSwal(array $options): void
    {
        $json = json_encode($options, JSON_HEX_TAG | JSON_HEX_APOS | JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT | JSON_HEX_AMP);
        $this->js('Swal.fire('.$json.')');
    }

    protected function payrollReportSwalNoData(?string $text = null): void
    {
        $this->payrollReportSwal([
            'title' => 'No data to download',
            'text' => $text ?? 'There are no payroll records for this selection. Process salaries or adjust filters, then try again.',
            'icon' => 'warning',
            'confirmButtonText' => 'OK',
        ]);
    }

    protected function payrollReportSwalError(string $title, string $text): void
    {
        $this->payrollReportSwal([
            'title' => $title,
            'text' => $text,
            'icon' => 'error',
            'confirmButtonText' => 'OK',
        ]);
    }

    protected function payrollReportSwalValidation(ValidationException $e): void
    {
        $lines = $e->validator->errors()->all();
        $html = '<ul style="text-align:left;margin:0.5em 0 0 1.25rem;padding:0;">';
        foreach ($lines as $line) {
            $html .= '<li>'.e($line).'</li>';
        }
        $html .= '</ul>';

        $this->payrollReportSwal([
            'title' => 'Validation',
            'html' => $html,
            'icon' => 'error',
            'confirmButtonText' => 'OK',
        ]);
    }

    /**
     * @return bool false when validation failed (Swal already shown)
     */
    protected function validatePayrollReportRequest(array $rules, array $messages = []): bool
    {
        try {
            $this->validate($rules, $messages);

            return true;
        } catch (ValidationException $e) {
            $this->payrollReportSwalValidation($e);

            return false;
        }
    }
}
