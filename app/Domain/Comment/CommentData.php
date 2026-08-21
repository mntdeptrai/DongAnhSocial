<?php

namespace App\Domain\Comment;

use Illuminate\Http\Request;

class CommentData
{
    public function __construct(
        public ?int $user_id,
        public ?string $guest_name,
        public string $content,
        public int $commentable_id,
        public string $commentable_type
    ) {}

    public static function fromRequest(Request $request): self
    {
        $userId = auth()->id() ?? session('user_id');
        $user = $userId ? \App\Models\User::find($userId) : null;
        $guestName = $user ? null : ($request->input('guest_name') ?? 'Khách vãng lai');

        return new self(
            user_id: $user ? (int) $user->id : null,
            guest_name: $guestName,
            content: $request->input('content'),
            commentable_id: (int) $request->input('commentable_id'),
            commentable_type: $request->input('commentable_type')
        );
    }
}
