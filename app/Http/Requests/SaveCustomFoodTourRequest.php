<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveCustomFoodTourRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                  => 'required|string|max:255',
            'description'           => 'required|string',
            'duration'              => 'required|string|max:100',
            'distance'              => 'required|string|max:100',
            'budget'                => 'required|string|max:100',
            'difficulty'            => 'nullable|string|max:100',
            'best_time'             => 'required|string|max:100',
            'mood'                  => 'nullable|string|max:100',
            'thumbnail_file'        => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'thumbnail'             => 'nullable|string',
            'story'                 => 'nullable|string',
            'stops'                 => 'required|array|min:1',
            'stops.*.eatery_id'     => 'required|integer',
            'stops.*.stop_story'    => 'nullable|string',
            'stops.*.estimated_time'=> 'nullable|string|max:100',
        ];
    }
}
