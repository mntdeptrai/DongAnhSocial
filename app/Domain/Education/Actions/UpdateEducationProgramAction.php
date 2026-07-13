<?php

namespace App\Domain\Education\Actions;

use App\Domain\Education\EducationProgramData;
use App\Models\EducationProgram;

class UpdateEducationProgramAction
{
    public function execute(EducationProgram $program, EducationProgramData $data, ?string $imagePath): EducationProgram
    {
        $program->update([
            'name' => $data->name,
            'description' => $data->description,
            'image_path' => $imagePath,
            'duration' => $data->duration,
            'tuition_fee' => $data->tuition_fee,
        ]);
        return $program;
    }
}
