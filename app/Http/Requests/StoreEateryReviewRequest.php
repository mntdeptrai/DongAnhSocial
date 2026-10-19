<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEateryReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_name' => 'required|string|max:50',
            'rating'    => 'nullable|integer|min:1|max:5',
            'comment'   => 'nullable|string|max:500',
            'media.*'   => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:20480',
        ];
    }

    public function messages(): array
    {
        return [
            'user_name.required' => 'Vui lòng nhập họ và tên của bạn.',
            'user_name.max'      => 'Họ và tên không được quá 50 ký tự.',
            'rating.integer'     => 'Số sao đánh giá phải là số nguyên.',
            'rating.min'         => 'Đánh giá tối thiểu là 1 sao.',
            'rating.max'         => 'Đánh giá tối đa là 5 sao.',
            'comment.max'        => 'Nội dung nhận xét tối đa 500 ký tự.',
            'media.*.max'        => 'Kích thước tệp đính kèm không vượt quá 20MB.',
            'media.*.mimes'      => 'Định dạng tệp không được hỗ trợ.',
        ];
    }
}
