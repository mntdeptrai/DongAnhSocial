<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateLiveStreamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'             => 'required|string|max:255',
            'description'       => 'nullable|string|max:1000',
            'category'          => 'required|string|in:ocop,food,travel,culture,general',
            'pinned_product_id' => 'nullable|integer',
            'product_ids'       => 'nullable|array',
            'product_ids.*'     => 'integer',
            'cover_image'       => 'nullable|image|max:5120',
            'youtube_url'       => 'nullable|string|max:500',
            'stream_source'     => 'nullable|string|in:youtube_auto,youtube,webrtc,obs',
        ];
    }
}
