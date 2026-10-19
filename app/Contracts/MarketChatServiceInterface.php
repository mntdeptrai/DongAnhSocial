<?php

namespace App\Contracts;

use App\Models\MarketMessage;
use App\Models\User;
use Illuminate\Http\UploadedFile;

interface MarketChatServiceInterface
{
    /**
     * Get chat messages and room context for an eatery.
     */
    public function getChatFeed(int|string $eateryId, array $filters, ?User $authUser): array;

    /**
     * Send a market chat message.
     */
    public function sendMessage(int|string $eateryId, array $data, ?User $authUser, ?UploadedFile $imageFile): MarketMessage;
}
