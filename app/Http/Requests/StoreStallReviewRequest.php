<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStallReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rating'    => 'required|integer|min:1|max:5',
            'comment'   => 'required|string|min:5|max:500',
            'user_name' => 'nullable|string|max:50',
            'media.*'   => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,mp4,mov,avi|max:20480',
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required'  => 'Vui lòng đánh giá số sao.',
            'rating.integer'   => 'Số sao phải là số nguyên từ 1 đến 5.',
            'rating.min'       => 'Đánh giá tối thiểu là 1 sao.',
            'rating.max'       => 'Đánh giá tối đa là 5 sao.',
            'comment.required' => 'Vui lòng nhập nội dung nhận xét.',
            'comment.min'      => 'Nội dung nhận xét tối thiểu 5 ký tự.',
            'comment.max'      => 'Nội dung nhận xét tối đa 500 ký tự.',
            'user_name.max'    => 'Tên người dùng không được vượt quá 50 ký tự.',
            'media.*.max'      => 'Kích thước tệp đính kèm không vượt quá 20MB.',
            'media.*.mimes'    => 'Định dạng tệp đính kèm không hợp lệ (hỗ trợ ảnh và video ngắn).',
        ];
    }
}
