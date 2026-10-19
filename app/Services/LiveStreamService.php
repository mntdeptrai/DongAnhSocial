<?php

namespace App\Services;

use App\Contracts\LiveStreamServiceInterface;
use App\Events\LiveStreamCommentSent;
use App\Events\LiveStreamEnded;
use App\Events\LiveStreamProductPinned;
use App\Events\LiveStreamProductsUpdated;
use App\Events\LiveStreamReactionSent;
use App\Events\LiveStreamSignal;
use App\Models\LiveStream;
use App\Models\LiveStreamComment;
use App\Models\OcopCertifiedProduct;
use App\Models\OcopProduct;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LiveStreamService implements LiveStreamServiceInterface
{
    /**
     * Định dạng dữ liệu sản phẩm OCOP cho Livestream
     */
    public function formatProduct($product, bool $isPinned = false): array
    {
        return [
            'id'          => $product->id,
            'name'        => $product->name,
            'price'       => $product->price ? number_format($product->price) . 'đ' : 'Liên hệ',
            'raw_price'   => (float)$product->price,
            'image'       => $product->image_path ? (str_starts_with($product->image_path, 'http') ? $product->image_path : asset($product->image_path)) : '/images/ocop-placeholder.png',
            'image_url'   => $product->image_path ? (str_starts_with($product->image_path, 'http') ? $product->image_path : asset($product->image_path)) : '/images/ocop-placeholder.png',
            'star_rating' => $product->star_rating ?? '4 sao',
            'detail_url'  => route('ocop.product.show', $product->slug ?: $product->id),
            'unit'        => $product->unit ?? 'sản phẩm',
            'is_pinned'   => $isPinned,
            'description' => $product->description ?? 'Sản phẩm OCOP chất lượng cao được tuyển chọn và kiểm định chuẩn Đông Anh.',
            'story'       => $product->story ?? null,
            'artisans'    => $product->artisans ?? null,
        ];
    }

    /**
     * Tìm phiên livestream theo Code định danh (hoặc ID tương thích ngược)
     */
    public function findStream(int|string $identifier, array $with = []): LiveStream
    {
        if (empty($with)) {
            $cached = Cache::get('ls_obj_' . $identifier);
            if ($cached instanceof LiveStream) {
                return $cached;
            }
        }

        $query = LiveStream::query();
        if (!empty($with)) {
            $query->with($with);
        }

        $stream = null;

        try {
            $stream = (clone $query)->where('code', $identifier)->first();
        } catch (\Throwable $e) {}

        if (!$stream && is_numeric($identifier)) {
            $stream = (clone $query)->where('id', $identifier)->first();
        }

        if (!$stream && is_string($identifier) && str_starts_with($identifier, 'live-')) {
            $possibleId = substr($identifier, 5);
            if (is_numeric($possibleId)) {
                $stream = (clone $query)->where('id', $possibleId)->first();
            }
        }

        if (!$stream) {
            $stream = $query->where('id', $identifier)->firstOrFail();
        }

        if (empty($with) && $stream) {
            Cache::put('ls_obj_' . $identifier, $stream, 30);
            Cache::put('ls_obj_' . $stream->id, $stream, 30);
            if (!empty($stream->code)) {
                Cache::put('ls_obj_' . $stream->code, $stream, 30);
            }
        }

        return $stream;
    }

    /**
     * Get live and ended streams for directory page.
     */
    public function getIndexStreams(?string $category = 'all'): array
    {
        $liveQuery = LiveStream::with(['user', 'pinnedProduct', 'products'])
            ->where('status', 'live');

        $endedQuery = LiveStream::with(['user', 'pinnedProduct'])
            ->where('status', 'ended');

        if ($category && $category !== 'all') {
            $liveQuery->where('category', $category);
            $endedQuery->where('category', $category);
        }

        return [
            'activeStreams' => $liveQuery->orderByDesc('viewer_count')->latest()->get(),
            'endedStreams'  => $endedQuery->latest()->paginate(12),
        ];
    }

    /**
     * Lấy danh sách sản phẩm OCOP chuẩn Đông Anh
     */
    public function getAvailableOcopProducts()
    {
        return OcopCertifiedProduct::orderBy('name')->get();
    }

    /**
     * Khởi tạo phiên Livestream
     */
    public function createStream(array $data, User $user, ?UploadedFile $coverImage): LiveStream
    {
        $streamSource = $data['stream_source'] ?? 'youtube_auto';
        $youtubeVideoId = null;

        if ($streamSource === 'youtube_auto') {
            if (\App\Services\YouTubeService::isConfigured()) {
                $createdYtEvent = \App\Services\YouTubeService::createLiveEvent(
                    $data['title'],
                    $data['description'] ?? 'Livestream bán hàng OCOP & Giao lưu cộng đồng Đông Anh',
                    'public'
                );
                if ($createdYtEvent && !empty($createdYtEvent['video_id'])) {
                    $youtubeVideoId = $createdYtEvent['video_id'];
                }
            }
        } elseif (!empty($data['youtube_url'])) {
            $youtubeVideoId = \App\Services\YouTubeService::extractVideoId($data['youtube_url']);
        }

        $coverImagePath = null;
        if ($coverImage) {
            $coverImagePath = '/storage/' . $coverImage->store('livestreams', 'public');
        } elseif ($youtubeVideoId) {
            $coverImagePath = \App\Services\YouTubeService::getThumbnailUrl($youtubeVideoId, 'hqdefault');
        }

        $liveStream = LiveStream::create([
            'user_id'           => $user->id,
            'title'             => $data['title'],
            'description'       => $data['description'] ?? null,
            'category'          => $data['category'],
            'pinned_product_id' => $data['pinned_product_id'] ?? null,
            'cover_image'       => $coverImagePath,
            'youtube_video_id'  => $youtubeVideoId,
            'status'            => 'live',
            'viewer_count'      => 1,
            'peak_viewers'      => 1,
            'likes_count'       => 0,
            'started_at'        => now(),
        ]);

        $productIds = $data['product_ids'] ?? [];
        if (!empty($data['pinned_product_id']) && !in_array((int)$data['pinned_product_id'], $productIds)) {
            $productIds[] = (int)$data['pinned_product_id'];
        }

        if (!empty($productIds)) {
            $attachData = [];
            foreach ($productIds as $idx => $prodId) {
                $attachData[$prodId] = [
                    'is_pinned'  => ((int)$prodId === (int)($data['pinned_product_id'] ?? 0)),
                    'sort_order' => $idx,
                ];
            }
            $liveStream->products()->sync($attachData);
        }

        return $liveStream;
    }

    /**
     * Dữ liệu Studio cho Host
     */
    public function getHostViewData(int|string $identifier, User $currentUser): array
    {
        $stream = $this->findStream($identifier, ['user', 'pinnedProduct', 'products', 'comments.user']);

        if ($stream->user_id !== $currentUser->id && $currentUser->role !== 'admin') {
            abort(403, 'Bạn không phải là chủ phòng này.');
        }

        return [
            'stream'         => $stream,
            'currentUser'    => $currentUser,
            'ocopProducts'   => $this->getAvailableOcopProducts(),
            'streamProducts' => $stream->products,
        ];
    }

    /**
     * Dữ liệu Viewer cho Khán giả
     */
    public function getViewerData(int|string $identifier, ?User $currentUser): array
    {
        $stream = $this->findStream($identifier, ['user', 'pinnedProduct', 'products', 'comments.user']);

        $relatedStreams = LiveStream::with('user')
            ->where('status', 'live')
            ->where('id', '!=', $stream->id)
            ->limit(4)
            ->get();

        return [
            'stream'         => $stream,
            'currentUser'    => $currentUser,
            'relatedStreams' => $relatedStreams,
        ];
    }

    /**
     * Gửi bình luận
     */
    public function postComment(int|string $identifier, string $message, User $user): array
    {
        $stream = $this->findStream($identifier);

        $comment = LiveStreamComment::create([
            'live_stream_id' => $stream->id,
            'user_id'        => $user->id,
            'message'        => trim($message),
        ]);

        $userAvatar = $user->avatar_url ?: ('https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=0ea5e9&color=fff');

        try {
            broadcast(new LiveStreamCommentSent(
                liveStreamId: $stream->id,
                commentId: $comment->id,
                userId: $user->id,
                userName: $user->name,
                userAvatar: $userAvatar,
                message: $comment->message,
                createdAt: $comment->created_at->format('H:i')
            ));
        } catch (\Throwable $e) {
            Log::warning('LiveStreamCommentSent broadcast warning: ' . $e->getMessage());
        }

        return [
            'id'          => $comment->id,
            'user_name'   => $user->name,
            'user_avatar' => $userAvatar,
            'message'     => $comment->message,
            'created_at'  => $comment->created_at->format('H:i'),
        ];
    }

    /**
     * Thả cảm xúc
     */
    public function postReaction(int|string $identifier, string $reactionType): array
    {
        $stream = $this->findStream($identifier);
        $stream->increment('likes_count');
        $stream->refresh();

        try {
            broadcast(new LiveStreamReactionSent(
                liveStreamId: $stream->id,
                reactionType: $reactionType,
                totalLikes: (int)$stream->likes_count
            ))->toOthers();
        } catch (\Throwable $e) {
            Log::warning('LiveStreamReactionSent broadcast warning: ' . $e->getMessage());
        }

        return ['total_likes' => $stream->likes_count];
    }

    /**
     * Lấy danh sách sản phẩm giỏ hàng live
     */
    public function getStreamProducts(int|string $identifier): array
    {
        $stream = $this->findStream($identifier, ['products']);
        $pinnedId = $stream->pinned_product_id;

        $products = $stream->products->map(function ($p) use ($pinnedId) {
            return $this->formatProduct($p, (int)$p->id === (int)$pinnedId);
        });

        $pinnedProduct = $stream->pinnedProduct ? $this->formatProduct($stream->pinnedProduct, true) : null;

        return [
            'products'       => $products,
            'pinned_product' => $pinnedProduct,
        ];
    }

    /**
     * Thêm sản phẩm vào giỏ hàng live
     */
    public function addProductToStream(int|string $identifier, int $productId, User $user): array
    {
        $stream = $this->findStream($identifier);

        if ($stream->user_id !== $user->id && $user->role !== 'admin') {
            abort(403, 'Không có quyền thực hiện.');
        }

        if (!$stream->products()->where('live_stream_products.ocop_product_id', $productId)->exists()) {
            $stream->products()->attach($productId, [
                'is_pinned'  => false,
                'sort_order' => $stream->products()->count(),
            ]);
        }

        $stream->load(['products', 'pinnedProduct']);
        $pinnedId = $stream->pinned_product_id;

        $products = $stream->products->map(function ($p) use ($pinnedId) {
            return $this->formatProduct($p, (int)$p->id === (int)$pinnedId);
        })->toArray();

        $pinnedProduct = $stream->pinnedProduct ? $this->formatProduct($stream->pinnedProduct, true) : null;

        try {
            broadcast(new LiveStreamProductsUpdated(
                liveStreamId: $stream->id,
                products: $products,
                pinnedProduct: $pinnedProduct
            ))->toOthers();
        } catch (\Throwable $e) {
            Log::warning('LiveStreamProductsUpdated warning: ' . $e->getMessage());
        }

        return [
            'products'       => $products,
            'pinned_product' => $pinnedProduct,
        ];
    }

    /**
     * Xóa sản phẩm khỏi giỏ hàng live
     */
    public function removeProductFromStream(int|string $identifier, int $productId, User $user): array
    {
        $stream = $this->findStream($identifier);

        if ($stream->user_id !== $user->id && $user->role !== 'admin') {
            abort(403, 'Không có quyền thực hiện.');
        }

        $stream->products()->detach($productId);

        if ((int)$stream->pinned_product_id === $productId) {
            $stream->pinned_product_id = null;
            $stream->save();
        }

        $stream->load(['products', 'pinnedProduct']);
        $pinnedId = $stream->pinned_product_id;

        $products = $stream->products->map(function ($p) use ($pinnedId) {
            return $this->formatProduct($p, (int)$p->id === (int)$pinnedId);
        })->toArray();

        $pinnedProduct = $stream->pinnedProduct ? $this->formatProduct($stream->pinnedProduct, true) : null;

        try {
            broadcast(new LiveStreamProductsUpdated(
                liveStreamId: $stream->id,
                products: $products,
                pinnedProduct: $pinnedProduct
            ))->toOthers();
        } catch (\Throwable $e) {
            Log::warning('LiveStreamProductsUpdated warning: ' . $e->getMessage());
        }

        return [
            'products'       => $products,
            'pinned_product' => $pinnedProduct,
        ];
    }

    /**
     * Ghim sản phẩm OCOP
     */
    public function pinProduct(int|string $identifier, ?int $productId, User $user): array
    {
        $stream = $this->findStream($identifier);

        if ($stream->user_id !== $user->id && $user->role !== 'admin') {
            abort(403, 'Không có quyền thực hiện.');
        }

        $product = null;

        if ($productId) {
            $productModel = OcopCertifiedProduct::find($productId) ?? OcopProduct::find($productId);
            if ($productModel) {
                $stream->pinned_product_id = $productModel->id;
                $stream->save();

                if (!$stream->products()->where('live_stream_products.ocop_product_id', $productId)->exists()) {
                    $stream->products()->attach($productId, [
                        'is_pinned'  => true,
                        'sort_order' => $stream->products()->count(),
                    ]);
                } else {
                    $stream->products()->updateExistingPivot($productId, ['is_pinned' => true]);
                }

                $product = $this->formatProduct($productModel, true);
            }
        } else {
            $stream->pinned_product_id = null;
            $stream->save();
        }

        DB::table('live_stream_products')
            ->where('live_stream_id', $stream->id)
            ->where('ocop_product_id', '!=', $productId ?? 0)
            ->update(['is_pinned' => false]);

        $stream->load(['products', 'pinnedProduct']);

        $pinnedId = $stream->pinned_product_id;
        $products = $stream->products->map(function ($p) use ($pinnedId) {
            return $this->formatProduct($p, (int)$p->id === (int)$pinnedId);
        })->toArray();

        try {
            broadcast(new LiveStreamProductPinned(
                liveStreamId: $stream->id,
                productData: $product
            ))->toOthers();

            broadcast(new LiveStreamProductsUpdated(
                liveStreamId: $stream->id,
                products: $products,
                pinnedProduct: $product
            ))->toOthers();
        } catch (\Throwable $e) {
            Log::warning('LiveStreamProduct broadcast warning: ' . $e->getMessage());
        }

        return [
            'product'        => $product,
            'products'       => $products,
            'pinned_product' => $product,
        ];
    }

    /**
     * Trao đổi tín hiệu WebRTC (Signaling)
     */
    public function handleSignal(int|string $identifier, array $data): void
    {
        $stream = $this->findStream($identifier);

        $cacheKey = 'live_stream_signals_' . $stream->id;
        $signals = Cache::get($cacheKey, []);
        $signals[] = [
            'live_stream_id'    => $stream->id,
            'sender_session_id' => $data['sender_session_id'],
            'target_session_id' => $data['target_session_id'],
            'signal_type'       => $data['signal_type'],
            'signal_data'       => $data['signal_data'] ?? null,
            'timestamp'         => microtime(true),
        ];
        if (count($signals) > 80) {
            $signals = array_slice($signals, -80);
        }
        Cache::put($cacheKey, $signals, 120);

        try {
            broadcast(new LiveStreamSignal(
                liveStreamId: $stream->id,
                senderSessionId: $data['sender_session_id'],
                targetSessionId: $data['target_session_id'],
                signalType: $data['signal_type'],
                signalData: $data['signal_data'] ?? null
            ));
        } catch (\Throwable $e) {
            Log::warning('LiveStreamSignal broadcast warning: ' . $e->getMessage());
        }
    }

    /**
     * Polling tín hiệu WebRTC qua HTTP
     */
    public function pollSignals(int|string $identifier, ?string $sessionId, bool $isHost, float $since): array
    {
        $stream = $this->findStream($identifier);
        $cacheKey = 'live_stream_signals_' . $stream->id;
        $signals = Cache::get($cacheKey, []);

        $results = [];
        foreach ($signals as $sig) {
            if ($sig['timestamp'] <= $since) {
                continue;
            }
            if ($sig['sender_session_id'] === $sessionId) {
                continue;
            }
            $target = $sig['target_session_id'];
            if ($target === $sessionId || $target === 'all' || ($isHost && $target === 'host')) {
                $results[] = $sig;
            }
        }

        return $results;
    }

    /**
     * Cập nhật số người xem
     */
    public function updateViewerCount(int|string $identifier, int $count): array
    {
        $stream = $this->findStream($identifier);
        $count = max(1, $count);

        $stream->viewer_count = $count;
        if ($count > $stream->peak_viewers) {
            $stream->peak_viewers = $count;
        }
        $stream->save();

        return [
            'viewer_count' => $stream->viewer_count,
            'peak_viewers' => $stream->peak_viewers,
        ];
    }

    /**
     * Cập nhật YouTube Live
     */
    public function updateYouTubeLive(int|string $identifier, ?string $youtubeUrl, User $user): array
    {
        $stream = $this->findStream($identifier);

        if ($stream->user_id !== $user->id && $user->role !== 'admin') {
            abort(403, 'Không có quyền thực hiện.');
        }

        $videoId = !empty($youtubeUrl) ? \App\Services\YouTubeService::extractVideoId($youtubeUrl) : null;

        $stream->youtube_video_id = $videoId;
        if ($videoId && empty($stream->cover_image)) {
            $stream->cover_image = \App\Services\YouTubeService::getThumbnailUrl($videoId, 'hqdefault');
        }
        $stream->save();

        return [
            'youtube_video_id' => $videoId,
            'embed_url'        => $videoId ? \App\Services\YouTubeService::getEmbedUrl($videoId) : null,
        ];
    }

    /**
     * Kết thúc phiên live
     */
    public function endStream(int|string $identifier, User $user): array
    {
        $stream = $this->findStream($identifier);

        if ($stream->user_id !== $user->id && $user->role !== 'admin') {
            abort(403, 'Không có quyền thực hiện.');
        }

        $stream->status = 'ended';
        $stream->ended_at = now();
        $stream->viewer_count = 0;
        $stream->save();

        try {
            broadcast(new LiveStreamEnded(
                liveStreamId: $stream->id,
                duration: $stream->duration,
                peakViewers: (int)$stream->peak_viewers,
                totalLikes: (int)$stream->likes_count
            ));
        } catch (\Throwable $e) {
            Log::warning('LiveStreamEnded broadcast warning: ' . $e->getMessage());
        }

        return [
            'duration'     => $stream->duration,
            'peak_viewers' => $stream->peak_viewers,
            'total_likes'  => $stream->likes_count,
        ];
    }

    /**
     * Tải lên video ghi hình và đồng bộ YouTube
     */
    public function uploadRecording(int|string $identifier, UploadedFile $file, User $user): array
    {
        $stream = $this->findStream($identifier);

        if ($stream->user_id !== $user->id && $user->role !== 'admin') {
            abort(403, 'Không có quyền thực hiện.');
        }

        $storedPath = $file->store('livestream_recordings', 'public');
        $localUrl = '/storage/' . $storedPath;

        $youtubeVideoId = null;
        $youtubeUrl = null;

        if (\App\Services\YouTubeService::isConfigured()) {
            try {
                $ytResult = \App\Services\YouTubeService::uploadVideo(
                    video: $file,
                    title: '🔴 Livestream: ' . $stream->title . ' - Đông Anh Discovery',
                    description: "Phiên phát trực tiếp tại Đông Anh Discovery\n\nChủ phòng: " . ($stream->user ? $stream->user->name : 'Streamer') . "\nChủ đề: " . $stream->category . "\nThời gian: " . ($stream->started_at ? $stream->started_at->format('d/m/Y H:i') : now()->format('d/m/Y H:i')) . "\n\n" . ($stream->description ?: ''),
                    privacy: 'unlisted',
                    tags: ['Đông Anh', 'Livestream', 'OCOP', 'DongAnh Discovery']
                );

                if ($ytResult && !empty($ytResult['id'])) {
                    $youtubeVideoId = $ytResult['id'];
                    $youtubeUrl = $ytResult['url'];
                }
            } catch (\Throwable $e) {
                Log::warning('YouTube auto-upload failed for stream #' . $stream->id . ': ' . $e->getMessage());
            }
        }

        $stream->recording_url = $youtubeUrl ?: $localUrl;
        if ($youtubeVideoId) {
            $stream->youtube_video_id = $youtubeVideoId;
        }
        $stream->save();

        return [
            'recording_url'    => $stream->recording_url,
            'youtube_video_id' => $stream->youtube_video_id,
            'is_youtube'       => !empty($stream->youtube_video_id),
        ];
    }

    /**
     * Xóa phiên live
     */
    public function deleteStream(int|string $identifier, User $user): bool
    {
        $stream = $this->findStream($identifier);

        if ($stream->user_id !== $user->id && $user->role !== 'admin') {
            abort(403, 'Không có quyền thực hiện.');
        }

        return (bool) $stream->delete();
    }
}
