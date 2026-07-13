<?php

namespace App\Domain\Comment\Actions;

use App\Domain\Comment\CommentData;
use App\Models\Comment;

class CreateCommentAction
{
    public function execute(CommentData $data): Comment
    {
        return Comment::create([
            'user_id' => $data->user_id,
            'guest_name' => $data->guest_name,
            'content' => $data->content,
            'commentable_id' => $data->commentable_id,
            'commentable_type' => $data->commentable_type,
        ]);
    }
}
