<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateModerationReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'target_id'      => 'required|string',
            'reason'         => 'required|string|max:255',
            'target_type'    => 'nullable|string|max:50',
            'target_title'   => 'nullable|string|max:255',
            'target_summary' => 'nullable|string|max:500',
            'author_id'      => 'nullable|integer',
            'author_name'    => 'nullable|string|max:100',
            'details'        => 'nullable|string|max:1000',
        ];
    }
}
