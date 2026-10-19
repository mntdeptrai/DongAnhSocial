<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExperienceFeedbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'author_name'   => 'required|string|max:100',
            'activity_name' => 'required|string|max:255',
            'rating'        => 'required|integer|min:1|max:5',
            'comment'       => 'required|string|min:10|max:1500',
            'image_url'     => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'author_name.required'   => 'Vui lòng nhập tên người trải nghiệm.',
            'activity_name.required' => 'Vui lòng chọn hoặc nhập tên hoạt động.',
            'rating.required'        => 'Vui lòng đánh giá số sao (1-5).',
            'comment.required'       => 'Vui lòng chia sẻ cảm nhận của bạn.',
            'comment.min'            => 'Cảm nhận tối thiểu 10 ký tự.',
        ];
    }
}
