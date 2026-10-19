<?php

namespace App\Contracts;

use App\Models\LiveStream;
use App\Models\User;
use Illuminate\Http\UploadedFile;

interface LiveStreamServiceInterface
{
    public function formatProduct($product, bool $isPinned = false): array;

    public function findStream(int|string $identifier, array $with = []): LiveStream;

    public function getIndexStreams(?string $category = 'all'): array;

    public function getAvailableOcopProducts();

    public function createStream(array $data, User $user, ?UploadedFile $coverImage): LiveStream;

    public function getHostViewData(int|string $identifier, User $currentUser): array;

    public function getViewerData(int|string $identifier, ?User $currentUser): array;

    public function postComment(int|string $identifier, string $message, User $user): array;

    public function postReaction(int|string $identifier, string $reactionType): array;

    public function getStreamProducts(int|string $identifier): array;

    public function addProductToStream(int|string $identifier, int $productId, User $user): array;

    public function removeProductFromStream(int|string $identifier, int $productId, User $user): array;

    public function pinProduct(int|string $identifier, ?int $productId, User $user): array;

    public function handleSignal(int|string $identifier, array $data): void;

    public function pollSignals(int|string $identifier, ?string $sessionId, bool $isHost, float $since): array;

    public function updateViewerCount(int|string $identifier, int $count): array;

    public function updateYouTubeLive(int|string $identifier, ?string $youtubeUrl, User $user): array;

    public function endStream(int|string $identifier, User $user): array;

    public function uploadRecording(int|string $identifier, UploadedFile $file, User $user): array;

    public function deleteStream(int|string $identifier, User $user): bool;
}
