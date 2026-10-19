<?php

namespace App\Contracts;

use App\Domain\Comment\CommentData;
use App\Models\Comment;

interface CommentServiceInterface
{
    /**
     * Create a new comment.
     *
     * @param CommentData $data
     * @return Comment
     */
    public function createComment(CommentData $data): Comment;
}
