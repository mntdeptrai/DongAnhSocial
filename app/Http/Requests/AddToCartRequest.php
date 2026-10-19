<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dish_id'         => 'nullable|integer',
            'ocop_product_id' => 'nullable|integer',
            'quantity'        => 'nullable|integer|min:1',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$this->filled('dish_id') && !$this->filled('ocop_product_id')) {
                $validator->errors()->add('item', 'Yêu cầu mã món ăn hoặc sản phẩm OCOP.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'dish_id.integer'         => 'Mã món ăn phải là số nguyên.',
            'ocop_product_id.integer' => 'Mã sản phẩm OCOP phải là số nguyên.',
            'quantity.integer'        => 'Số lượng phải là số nguyên.',
            'quantity.min'            => 'Số lượng tối thiểu là 1.',
        ];
    }
}
