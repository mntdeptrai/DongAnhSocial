<?php

namespace App\Domain\Education\Actions;

use App\Domain\Education\EducationProgramData;
use App\Models\EducationProgram;

class CreateEducationProgramAction
{
    public function execute(EducationProgramData $data, ?string $imagePath): EducationProgram
    {
        $program = new EducationProgram();
        $program->fill([
            'eatery_id' => $data->eatery_id,
            'name' => $data->name,
            'description' => $data->description,
            'image_path' => $imagePath,
            'duration' => $data->duration,
            'tuition_fee' => $data->tuition_fee,
        ]);
        $program->save();
        return $program;
    }
}
