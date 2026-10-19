<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitNongSanOrderRequest extends FormRequest
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
            'product_name'  => 'required|string|max:255',
            'customer_name' => 'required|string|max:100',
            'phone'         => 'required|string|max:20',
            'quantity'      => 'nullable|string|max:50',
            'note'          => 'nullable|string|max:500',
        ];
    }

    /**
     * Custom validation error messages.
     */
    public function messages(): array
    {
        return [
            'product_name.required'  => 'Vui lòng chọn sản phẩm cần đặt mua.',
            'customer_name.required' => 'Vui lòng nhập họ và tên của bạn.',
            'phone.required'         => 'Vui lòng nhập số điện thoại liên hệ.',
        ];
    }
}
