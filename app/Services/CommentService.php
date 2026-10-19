<?php

namespace App\Services;

use App\Domain\Comment\CommentData;
use App\Domain\Comment\Actions\CreateCommentAction;
use App\Models\Comment;
use App\Contracts\CommentServiceInterface;

class CommentService implements CommentServiceInterface
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
