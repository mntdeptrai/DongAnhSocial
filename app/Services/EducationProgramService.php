<?php

namespace App\Services;

use App\Domain\Education\EducationProgramData;
use App\Helpers\R2Helper;
use App\Models\Eatery;
use App\Models\EducationProgram;

class EducationProgramService
{
    public function create(EducationProgramData|array $data): ?EducationProgram
    {
        if ($data instanceof EducationProgramData) {
            $imagePath = $this->resolveImagePath($data->image, $data->image_url);
            $attributes = [
                'eatery_id' => $data->eatery_id,
                'name' => $data->name,
                'program_type' => $data->program_type,
                'target_age' => $data->target_age,
                'tuition_fee' => $data->tuition_fee,
                'schedule' => $data->schedule,
                'highlights' => $data->highlights,
                'description' => $data->description,
                'image_path' => $imagePath,
            ];
        } else {
            $attributes = $data;
        }

        return $this->storeEducationProgram($attributes);
    }

    public function update($id, EducationProgramData|array $data): ?EducationProgram
    {
        $program = EducationProgram::find($id);
        if (!$program) return null;

        if ($data instanceof EducationProgramData) {
            $imagePath = $this->resolveImagePath($data->image, $data->image_url) ?? $program->image_path;
            $attributes = [
                'eatery_id' => $data->eatery_id,
                'name' => $data->name,
                'program_type' => $data->program_type,
                'target_age' => $data->target_age,
                'tuition_fee' => $data->tuition_fee,
                'schedule' => $data->schedule,
                'highlights' => $data->highlights,
                'description' => $data->description,
                'image_path' => $imagePath,
            ];
        } else {
            $attributes = $data;
        }

        $program->update($attributes);
        return $program;
    }

    public function storeEducationProgram(array $data): ?EducationProgram
    {
        $eatery = Eatery::find($data['eatery_id'] ?? null);
        if (!$eatery) return null;

        return EducationProgram::create($data);
    }

    public function updateEducationProgram($id, array $data): ?EducationProgram
    {
        $program = EducationProgram::find($id);
        if (!$program) return null;

        $program->update($data);
        return $program;
    }

    public function delete($id): bool
    {
        $program = EducationProgram::find($id);
        if (!$program) return false;

        return (bool) $program->delete();
    }

    public function deleteEducationProgram($id): bool
    {
        return $this->delete($id);
    }

    protected function resolveImagePath($imageFile, ?string $imageUrl): ?string
    {
        if ($imageFile) {
            return R2Helper::upload($imageFile, 'education');
        }

        if ($imageUrl) {
            if (preg_match('/(?:drive\.google\.com\/(?:file\/d\/|open\?id=|uc\?id=))([a-zA-Z0-9_-]{25,50})/i', $imageUrl, $matches)) {
                return 'https://drive.google.com/uc?export=download&id=' . $matches[1];
            }
            return $imageUrl;
        }

        return null;
    }
}
