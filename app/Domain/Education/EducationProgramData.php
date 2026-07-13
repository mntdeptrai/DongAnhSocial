<?php

namespace App\Domain\Education;

use Illuminate\Http\Request;

class EducationProgramData
{
    public function __construct(
        public int $eatery_id,
        public string $name,
        public ?string $description,
        public ?string $duration,
        public ?string $tuition_fee,
        public mixed $image = null,
        public ?string $image_url = null
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            eatery_id: (int) $request->input('eatery_id'),
            name: $request->input('name'),
            description: $request->input('description'),
            duration: $request->input('duration'),
            tuition_fee: $request->input('tuition_fee'),
            image: $request->file('image'),
            image_url: $request->input('image_url')
        );
    }
}
