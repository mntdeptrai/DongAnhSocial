<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHealthStationProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'             => 'required|string|max:255',
            'phone'            => 'required|string|max:50',
            'address'          => 'required|string|max:550',
            'opening_hours'    => 'nullable|string|max:255',
            'price_range'      => 'nullable|string|max:255',
            'description'      => 'nullable|string',
            'map_link'         => 'nullable|string',
            'latitude'         => 'nullable|numeric',
            'longitude'        => 'nullable|numeric',
            'image'            => 'nullable|image|max:10240',
            'image_url'        => 'nullable|string',
            'images.*'         => 'nullable|image|max:10240',
            'image_urls'       => 'nullable|array',
            'delete_photo_ids' => 'nullable|array',
            'area'             => 'nullable|string|max:255',
            'staff_count'      => 'nullable|string|max:255',
            'staff_total'      => 'nullable|string|max:255',
            'heritage_year'    => 'nullable|string|max:255',
        ];
    }
}
