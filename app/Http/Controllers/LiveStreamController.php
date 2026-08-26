<?php

namespace App\Http\Controllers;

use App\Models\LiveStream;
use App\Models\LiveStreamComment;
use App\Models\OcopCertifiedProduct;
use App\Models\OcopProduct;
use App\Models\User;
use App\Events\LiveStreamCommentSent;
use App\Events\LiveStreamReactionSent;
use App\Events\LiveStreamSignal;
use App\Events\LiveStreamProductPinned;
use App\Events\LiveStreamEnded;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use App\Events\LiveStreamProductsUpdated;

class LiveStreamController extends Controller
{
    /**
     * Định dạng dữ liệu sản phẩm OCOP cho Livestream
     */
    protected function formatProduct($product, bool $isPinned = false): array
    {
        return [
            'id'          => $product->id,
            'name'        => $product->name,
            'price'       => $product->price ? number_format($product->price) . 'đ' : 'Liên hệ',
            'raw_price'   => (float)$product->price,
            'image'       => $product->image_path ? (str_starts_with($product->image_path, 'http') ? $product->image_path : asset($product->image_path)) : null,
            'image_url'   => $product->image_path ? (str_starts_with($product->image_path, 'http') ? $product->image_path : asset($product->image_path)) : null,
            'star_rating' => $product->star_rating ?? '4 sao',
            'detail_url'  => route('ocop.product.show', $product->slug ?: $product->id),
            'unit'        => $product->unit ?? 'sản phẩm',
            'is_pinned'   => $isPinned,
        ];
    }


    /**
     * Lấy người dùng hiện tại (Auth session hoặc fallback cho test)
     */
    protected function getCurrentUser()
    {
        return Auth::user() ?? User::first();
    }

    /**
     * Trang chủ / Danh sách Livestream Đông Anh
     */
    public function index(Request $request)
    {
        $category = $request->get('category', 'all');

        $liveQuery = LiveStream::with(['user', 'pinnedProduct', 'products'])
            ->where('status', 'live');

        $endedQuery = LiveStream::with(['user', 'pinnedProduct'])
            ->where('status', 'ended');

        if ($category && $category !== 'all') {
            $liveQuery->where('category', $category);
            $endedQuery->where('category', $category);
        }

        $activeStreams = $liveQuery->orderByDesc('viewer_count')->latest()->get();
        $endedStreams = $endedQuery->latest()->paginate(12);

        $currentUser = $this->getCurrentUser();

        return view('livestream.index', compact('activeStreams', 'endedStreams', 'category', 'currentUser'));
    }

    /**
     * Lấy danh sách sản phẩm OCOP chuẩn Đông Anh từ bảng chuyên biệt ocop_certified_products
     */
    protected function getAvailableOcopProducts()
    {
        return OcopCertifiedProduct::orderBy('name')->get();
    }

    /**
     * Giao diện tạo phiên Livestream mới
     */
    public function create()
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để bắt đầu Livestream.');
        }

        // Lấy danh sách sản phẩm OCOP chuẩn có thể ghim
        $ocopProducts = $this->getAvailableOcopProducts();

        return view('livestream.create', compact('currentUser', 'ocopProducts'));
    }


    /**
     * Lưu và khởi tạo phiên Livestream
     */
    public function store(Request $request)
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để bắt đầu.');
        }

        $request->validate([
            'title'             => 'required|string|max:255',
            'description'       => 'nullable|string|max:1000',
            'category'          => 'required|string|in:ocop,food,travel,culture,general',
            'pinned_product_id' => 'nullable|integer',
            'product_ids'       => 'nullable|array',
            'product_ids.*'     => 'integer',
            'cover_image'       => 'nullable|image|max:5120',
        ]);


        $coverImagePath = null;
        if ($request->hasFile('cover_image')) {
            $coverImagePath = $request->file('cover_image')->store('livestreams', 'public');
            $coverImagePath = '/storage/' . $coverImagePath;
        }

        $liveStream = LiveStream::create([
            'user_id'           => $currentUser->id,
            'title'             => $request->title,
            'description'       => $request->description,
            'category'          => $request->category,
            'pinned_product_id' => $request->pinned_product_id,
            'cover_image'       => $coverImagePath,
            'status'            => 'live',
            'viewer_count'      => 1,
            'peak_viewers'      => 1,
            'likes_count'       => 0,
            'started_at'        => now(),
        ]);

        // Gắn danh sách nhiều sản phẩm vào giỏ hàng của phiên live
        $productIds = $request->input('product_ids', []);
        if ($request->pinned_product_id && !in_array($request->pinned_product_id, $productIds)) {
            $productIds[] = (int)$request->pinned_product_id;
        }

        if (!empty($productIds)) {
            $attachData = [];
            foreach ($productIds as $idx => $prodId) {
                $attachData[$prodId] = [
                    'is_pinned'  => ((int)$prodId === (int)$request->pinned_product_id),
                    'sort_order' => $idx,
                ];
            }
            $liveStream->products()->sync($attachData);
        }

        return redirect()->route('livestream.host', $liveStream->id);
    }

    /**
     * Studio phát trực tiếp dành cho Host (Streamer)
     */
    public function host($id)
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập.');
        }

        $stream = LiveStream::with(['user', 'pinnedProduct', 'products', 'comments.user'])->findOrFail($id);

        // Kiểm tra quyền (chủ stream hoặc admin)
        if ($stream->user_id !== $currentUser->id && $currentUser->role !== 'admin') {
            return redirect()->route('livestream.show', $id);
        }

        $ocopProducts = $this->getAvailableOcopProducts();
        $streamProducts = $stream->products;

        return view('livestream.host', compact('stream', 'currentUser', 'ocopProducts', 'streamProducts'));

    }

    /**
     * Phòng xem phát trực tiếp dành cho Khán giả (Viewer)
     */
    public function show($id)
    {
        $currentUser = $this->getCurrentUser();
        $stream = LiveStream::with(['user', 'pinnedProduct', 'products', 'comments.user'])->findOrFail($id);

        // Nếu người xem chính là chủ phòng và đang live thì chuyển sang Studio
        if ($currentUser && $stream->user_id === $currentUser->id && $stream->status === 'live') {
            return redirect()->route('livestream.host', $id);
        }

        $relatedStreams = LiveStream::with('user')
            ->where('status', 'live')
            ->where('id', '!=', $id)
            ->limit(4)
            ->get();

        return view('livestream.viewer', compact('stream', 'currentUser', 'relatedStreams'));
    }

    /**
     * Gửi bình luận trong phòng Livestream
     */
    public function sendComment(Request $request, $id)
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            return response()->json(['status' => 'error', 'message' => 'Vui lòng đăng nhập để bình luận.'], 401);
        }

        $request->validate([
            'message' => 'required|string|max:500',
        ]);

        $stream = LiveStream::findOrFail($id);

        $comment = LiveStreamComment::create([
            'live_stream_id' => $stream->id,
            'user_id'        => $currentUser->id,
            'message'        => trim($request->message),
        ]);

        $userAvatar = $currentUser->avatar ? (str_starts_with($currentUser->avatar, 'http') ? $currentUser->avatar : asset($currentUser->avatar)) : 'https://ui-avatars.com/api/?name=' . urlencode($currentUser->name) . '&background=0ea5e9&color=fff';

        try {
            broadcast(new LiveStreamCommentSent(
                liveStreamId: $stream->id,
                commentId: $comment->id,
                userId: $currentUser->id,
                userName: $currentUser->name,
                userAvatar: $userAvatar,
                message: $comment->message,
                createdAt: $comment->created_at->format('H:i')
            ))->toOthers();
        } catch (\Throwable $e) {
            Log::warning('LiveStreamCommentSent broadcast warning: ' . $e->getMessage());
        }

        return response()->json([
            'status'  => 'success',
            'comment' => [
                'id'          => $comment->id,
                'user_name'   => $currentUser->name,
                'user_avatar' => $userAvatar,
                'message'     => $comment->message,
                'created_at'  => $comment->created_at->format('H:i'),
            ]
        ]);
    }

    /**
     * Gửi cảm xúc (Thả tim, lửa, vỗ tay)
     */
    public function sendReaction(Request $request, $id)
    {
        $stream = LiveStream::findOrFail($id);
        $reactionType = $request->input('type', 'heart');

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

        return response()->json([
            'status'      => 'success',
            'total_likes' => $stream->likes_count,
        ]);
    }

    /**
     * Lấy danh sách sản phẩm trong giỏ hàng Livestream
     */
    public function getProducts($id)
    {
        $stream = LiveStream::with('products')->findOrFail($id);
        $pinnedId = $stream->pinned_product_id;

        $products = $stream->products->map(function ($p) use ($pinnedId) {
            return $this->formatProduct($p, (int)$p->id === (int)$pinnedId);
        });

        $pinnedProduct = $stream->pinnedProduct ? $this->formatProduct($stream->pinnedProduct, true) : null;

        return response()->json([
            'status'         => 'success',
            'products'       => $products,
            'pinned_product' => $pinnedProduct,
        ]);
    }

    /**
     * Thêm sản phẩm mới vào giỏ hàng của Livestream
     */
    public function addProduct(Request $request, $id)
    {
        $currentUser = $this->getCurrentUser();
        $stream = LiveStream::findOrFail($id);

        if (!$currentUser || ($stream->user_id !== $currentUser->id && $currentUser->role !== 'admin')) {
            return response()->json(['status' => 'error', 'message' => 'Không có quyền thực hiện.'], 403);
        }

        $request->validate([
            'product_id' => 'required|integer',
        ]);

        $productId = (int)$request->input('product_id');

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

        return response()->json([
            'status'         => 'success',
            'message'        => 'Đã thêm sản phẩm vào giỏ hàng Live.',
            'products'       => $products,
            'pinned_product' => $pinnedProduct,
        ]);
    }

    /**
     * Xóa sản phẩm khỏi giỏ hàng của Livestream
     */
    public function removeProduct(Request $request, $id, $productId)
    {
        $currentUser = $this->getCurrentUser();
        $stream = LiveStream::findOrFail($id);

        if (!$currentUser || ($stream->user_id !== $currentUser->id && $currentUser->role !== 'admin')) {
            return response()->json(['status' => 'error', 'message' => 'Không có quyền thực hiện.'], 403);
        }

        $productId = (int)$productId;
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

        return response()->json([
            'status'         => 'success',
            'message'        => 'Đã xóa sản phẩm khỏi giỏ hàng Live.',
            'products'       => $products,
            'pinned_product' => $pinnedProduct,
        ]);
    }

    /**
     * Ghim hoặc đổi sản phẩm OCOP được giới thiệu
     */
    public function pinProduct(Request $request, $id)
    {
        $currentUser = $this->getCurrentUser();
        $stream = LiveStream::findOrFail($id);

        if (!$currentUser || ($stream->user_id !== $currentUser->id && $currentUser->role !== 'admin')) {
            return response()->json(['status' => 'error', 'message' => 'Không có quyền thực hiện.'], 403);
        }

        $productId = $request->input('product_id') ? (int)$request->input('product_id') : null;
        $product = null;

        if ($productId) {
            $productModel = OcopCertifiedProduct::find($productId) ?? OcopProduct::find($productId);
            if ($productModel) {
                $stream->pinned_product_id = $productModel->id;
                $stream->save();


                // Đảm bảo sản phẩm có trong danh sách giỏ hàng
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

        // Cập nhật lại trạng thái is_pinned trong pivot table
        \Illuminate\Support\Facades\DB::table('live_stream_products')
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

        return response()->json([
            'status'         => 'success',
            'product'        => $product,
            'products'       => $products,
            'pinned_product' => $product,
        ]);
    }


    /**
     * Trao đổi tín hiệu WebRTC (Signaling) giữa Host & Viewers
     */
    public function sendSignal(Request $request, $id)
    {
        $request->validate([
            'sender_session_id' => 'required|string',
            'target_session_id' => 'required|string',
            'signal_type'       => 'required|string|in:viewer_join,host_offer,viewer_answer,ice_candidate,host_ready',
            'signal_data'       => 'nullable|string',
        ]);

        $stream = LiveStream::findOrFail($id);

        try {
            broadcast(new LiveStreamSignal(
                liveStreamId: $stream->id,
                senderSessionId: $request->sender_session_id,
                targetSessionId: $request->target_session_id,
                signalType: $request->signal_type,
                signalData: $request->signal_data
            ))->toOthers();
        } catch (\Throwable $e) {
            Log::warning('LiveStreamSignal broadcast warning: ' . $e->getMessage());
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Cập nhật số lượng người xem đang online
     */
    public function updateViewerCount(Request $request, $id)
    {
        $stream = LiveStream::findOrFail($id);
        $count = max(1, (int)$request->input('count', 1));

        $stream->viewer_count = $count;
        if ($count > $stream->peak_viewers) {
            $stream->peak_viewers = $count;
        }
        $stream->save();

        return response()->json([
            'status'       => 'success',
            'viewer_count' => $stream->viewer_count,
            'peak_viewers' => $stream->peak_viewers,
        ]);
    }

    /**
     * Kết thúc phiên phát trực tiếp
     */
    public function endStream(Request $request, $id)
    {
        $currentUser = $this->getCurrentUser();
        $stream = LiveStream::findOrFail($id);

        if (!$currentUser || ($stream->user_id !== $currentUser->id && $currentUser->role !== 'admin')) {
            return response()->json(['status' => 'error', 'message' => 'Không có quyền thực hiện.'], 403);
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
            ))->toOthers();
        } catch (\Throwable $e) {
            Log::warning('LiveStreamEnded broadcast warning: ' . $e->getMessage());
        }

        return response()->json([
            'status'       => 'success',
            'duration'     => $stream->duration,
            'peak_viewers' => $stream->peak_viewers,
            'total_likes'  => $stream->likes_count,
        ]);
    }

    /**
     * Xóa bản ghi phiên Livestream
     */
    public function destroy($id)
    {
        $currentUser = $this->getCurrentUser();
        $stream = LiveStream::findOrFail($id);

        if (!$currentUser || ($stream->user_id !== $currentUser->id && $currentUser->role !== 'admin')) {
            return response()->json(['status' => 'error', 'message' => 'Không có quyền thực hiện.'], 403);
        }

        $stream->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Đã xóa phiên phát sóng thành công.',
        ]);
    }
}

