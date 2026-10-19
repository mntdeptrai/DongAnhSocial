<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendMarketMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message_text'       => 'required_without_all:product_id,image|nullable|string|max:500',
            'product_id'         => 'nullable|integer',
            'image'              => 'nullable|image|max:5120',
            'sender_name'        => 'nullable|string|max:50',
            'private_stall_name' => 'nullable|string|max:100',
            'private_user_id'    => 'nullable|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'message_text.required_without_all' => 'Vui lòng nhập nội dung tin nhắn hoặc chọn hình ảnh/sản phẩm.',
            'message_text.max'                  => 'Nội dung tin nhắn không vượt quá 500 ký tự.',
            'image.image'                       => 'Tệp tải lên phải là hình ảnh.',
            'image.max'                         => 'Hình ảnh không được vượt quá 5MB.',
        ];
    }
}
