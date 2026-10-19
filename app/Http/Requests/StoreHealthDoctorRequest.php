<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHealthDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'          => 'required|string|max:255',
            'title'         => 'nullable|string|max:255',
            'specialty'     => 'nullable|string|max:255',
            'duty_schedule' => 'nullable|string|max:255',
            'phone'         => 'nullable|string|max:50',
            'avatar'        => 'nullable|string',
        ];
    }
}
