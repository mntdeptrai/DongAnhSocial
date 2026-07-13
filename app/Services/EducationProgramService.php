<?php

namespace App\Services;

use App\Domain\Education\EducationProgramData;
use App\Domain\Education\Actions\CreateEducationProgramAction;
use App\Domain\Education\Actions\UpdateEducationProgramAction;
use App\Helpers\R2Helper;
use App\Models\EducationProgram;
use App\Services\EateryApiService;

class EducationProgramService
{
    public function __construct(
        protected CreateEducationProgramAction $createAction,
        protected UpdateEducationProgramAction $updateAction
    ) {}

    public function create(EducationProgramData $data, ?string $connName = null): EducationProgram
    {
        $imagePath = $this->resolveImagePath($data->image, $data->image_url);
        
        $action = $this->createAction;
        if ($connName) {
            \App\Models\EducationProgram::setConnectionResolver(app('db'));
        }
        
        $program = $action->execute($data, $imagePath);
        if ($connName) {
            $program->setConnection($connName);
            $program->save();
        }
        return $program;
    }

    public function update($id, EducationProgramData $data, ?string $connName = null): EducationProgram
    {
        $connections = ['mysql', 'mysql_stay', 'mysql_wellness', 'mysql_market', 'mysql_education', 'mysql_culture'];
        $program = null;
        $activeConn = $connName;

        if ($connName) {
            $program = EducationProgram::on($connName)->find($id);
        } else {
            foreach ($connections as $conn) {
                $ep = EducationProgram::on($conn)->find($id);
                if ($ep) {
                    $program = $ep;
                    $activeConn = $conn;
                    break;
                }
            }
        }

        if (!$program) {
            throw new \Exception('Chương trình đào tạo không tồn tại!');
        }

        $imagePath = $program->image_path;
        if ($data->image) {
            $imagePath = R2Helper::upload($data->image, 'education');
        } elseif ($data->image_url) {
            $imagePath = $this->resolveImagePath(null, $data->image_url);
        }

        return $this->updateAction->execute($program, $data, $imagePath);
    }

    public function delete($id): bool
    {
        return EateryApiService::deleteEducationProgram($id);
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
