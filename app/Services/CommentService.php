<?php

namespace App\Services;

use App\Domain\Comment\CommentData;
use App\Domain\Comment\Actions\CreateCommentAction;
use App\Models\Comment;
use Illuminate\Support\Facades\DB;

class CommentService
{
    public function __construct(
        protected CreateCommentAction $createAction
    ) {}

    public function createComment(CommentData $data): Comment
    {
        return DB::transaction(function() use ($data) {
            return $this->createAction->execute($data);
        });
    }
}
