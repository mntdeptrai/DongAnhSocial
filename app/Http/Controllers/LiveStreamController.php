<?php

namespace App\Http\Controllers;

use App\Contracts\LiveStreamServiceInterface;
use App\Http\Requests\CreateLiveStreamRequest;
use App\Http\Requests\SendLiveStreamCommentRequest;
use App\Http\Requests\SendLiveStreamSignalRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LiveStreamController extends Controller
{
    public function __construct(
        protected LiveStreamServiceInterface $liveStreamService
    ) {}

    /**
     * Lấy người dùng hiện tại
     */
    protected function getCurrentUser(): ?User
    {
        return Auth::user() ?? (session('user_id') ? User::find(session('user_id')) : null);
    }

    /**
     * Trang chủ / Danh sách Livestream Đông Anh
     * GET /livestream
     */
    public function index(Request $request): View
    {
        $category = $request->get('category', 'all');
        $data = $this->liveStreamService->getIndexStreams($category);
        $currentUser = $this->getCurrentUser();

        return view('livestream.index', [
            'activeStreams' => $data['activeStreams'],
            'endedStreams'  => $data['endedStreams'],
            'category'      => $category,
            'currentUser'   => $currentUser,
        ]);
    }

    /**
     * Giao diện tạo phiên Livestream mới
     * GET /livestream/create
     */
    public function create(): View|RedirectResponse
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để bắt đầu Livestream.');
        }

        $ocopProducts = $this->liveStreamService->getAvailableOcopProducts();

        return view('livestream.create', compact('currentUser', 'ocopProducts'));
    }

    /**
     * Lưu và khởi tạo phiên Livestream
     * POST /livestream
     */
    public function store(CreateLiveStreamRequest $request): RedirectResponse
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để bắt đầu.');
        }

        $liveStream = $this->liveStreamService->createStream(
            $request->validated(),
            $currentUser,
            $request->file('cover_image')
        );

        return redirect()->route('livestream.host', $liveStream->code_or_id);
    }

    /**
     * Studio phát trực tiếp dành cho Host (Streamer)
     * GET /livestream/{id}/host
     */
    public function host(int|string $id): View|RedirectResponse
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập.');
        }

        $data = $this->liveStreamService->getHostViewData($id, $currentUser);

        return view('livestream.host', $data);
    }

    /**
     * Phòng xem phát trực tiếp dành cho Khán giả (Viewer)
     * GET /livestream/{id}
     */
    public function show(int|string $id): View|RedirectResponse
    {
        $currentUser = $this->getCurrentUser();
        $data = $this->liveStreamService->getViewerData($id, $currentUser);

        // Nếu người xem chính là chủ phòng và đang live thì chuyển sang Studio
        if ($currentUser && $data['stream']->user_id === $currentUser->id && $data['stream']->status === 'live') {
            return redirect()->route('livestream.host', $data['stream']->code_or_id);
        }

        return view('livestream.viewer', $data);
    }

    /**
     * Gửi bình luận trong phòng Livestream
     * POST /livestream/{id}/comment
     */
    public function sendComment(SendLiveStreamCommentRequest $request, int|string $id): JsonResponse
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            return response()->json(['status' => 'error', 'message' => 'Vui lòng đăng nhập để bình luận.'], 401);
        }

        $comment = $this->liveStreamService->postComment($id, $request->validated('message'), $currentUser);

        return response()->json([
            'status'  => 'success',
            'comment' => $comment,
        ]);
    }

    /**
     * Gửi cảm xúc (Thả tim, lửa, vỗ tay)
     * POST /livestream/{id}/reaction
     */
    public function sendReaction(Request $request, int|string $id): JsonResponse
    {
        $reactionType = $request->input('type', 'heart');
        $result = $this->liveStreamService->postReaction($id, $reactionType);

        return response()->json([
            'status'      => 'success',
            'total_likes' => $result['total_likes'],
        ]);
    }

    /**
     * Lấy danh sách sản phẩm trong giỏ hàng Livestream
     * GET /livestream/{id}/products
     */
    public function getProducts(int|string $id): JsonResponse
    {
        $data = $this->liveStreamService->getStreamProducts($id);

        return response()->json([
            'status'         => 'success',
            'products'       => $data['products'],
            'pinned_product' => $data['pinned_product'],
        ]);
    }

    /**
     * Thêm sản phẩm mới vào giỏ hàng của Livestream
     * POST /livestream/{id}/products
     */
    public function addProduct(Request $request, int|string $id): JsonResponse
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            return response()->json(['status' => 'error', 'message' => 'Không có quyền thực hiện.'], 403);
        }

        $request->validate(['product_id' => 'required|integer']);

        $data = $this->liveStreamService->addProductToStream($id, (int)$request->input('product_id'), $currentUser);

        return response()->json([
            'status'         => 'success',
            'message'        => 'Đã thêm sản phẩm vào giỏ hàng Live.',
            'products'       => $data['products'],
            'pinned_product' => $data['pinned_product'],
        ]);
    }

    /**
     * Xóa sản phẩm khỏi giỏ hàng của Livestream
     * DELETE /livestream/{id}/products/{productId}
     */
    public function removeProduct(Request $request, int|string $id, int|string $productId): JsonResponse
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            return response()->json(['status' => 'error', 'message' => 'Không có quyền thực hiện.'], 403);
        }

        $data = $this->liveStreamService->removeProductFromStream($id, (int)$productId, $currentUser);

        return response()->json([
            'status'         => 'success',
            'message'        => 'Đã xóa sản phẩm khỏi giỏ hàng Live.',
            'products'       => $data['products'],
            'pinned_product' => $data['pinned_product'],
        ]);
    }

    /**
     * Ghim hoặc đổi sản phẩm OCOP được giới thiệu
     * POST /livestream/{id}/pin-product
     */
    public function pinProduct(Request $request, int|string $id): JsonResponse
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            return response()->json(['status' => 'error', 'message' => 'Không có quyền thực hiện.'], 403);
        }

        $productId = $request->input('product_id') ? (int)$request->input('product_id') : null;
        $data = $this->liveStreamService->pinProduct($id, $productId, $currentUser);

        return response()->json([
            'status'         => 'success',
            'product'        => $data['product'],
            'products'       => $data['products'],
            'pinned_product' => $data['pinned_product'],
        ]);
    }

    /**
     * Trao đổi tín hiệu WebRTC (Signaling) giữa Host & Viewers
     * POST /livestream/{id}/signal
     */
    public function sendSignal(SendLiveStreamSignalRequest $request, int|string $id): JsonResponse
    {
        $this->liveStreamService->handleSignal($id, $request->validated());

        return response()->json(['status' => 'success']);
    }

    /**
     * Lấy danh sách tín hiệu WebRTC qua HTTP Polling
     * GET /livestream/{id}/signals
     */
    public function getSignals(Request $request, int|string $id): JsonResponse
    {
        $mySessionId = $request->query('session_id');
        $isHost = $request->query('is_host') == '1';
        $since = (float)$request->query('since', 0);

        $results = $this->liveStreamService->pollSignals($id, $mySessionId, $isHost, $since);

        return response()->json([
            'status'  => 'success',
            'signals' => $results,
            'now'     => microtime(true),
        ]);
    }

    /**
     * Cập nhật số lượng người xem đang online
     * POST /livestream/{id}/viewers
     */
    public function updateViewerCount(Request $request, int|string $id): JsonResponse
    {
        $count = (int)$request->input('count', 1);
        $result = $this->liveStreamService->updateViewerCount($id, $count);

        return response()->json([
            'status'       => 'success',
            'viewer_count' => $result['viewer_count'],
            'peak_viewers' => $result['peak_viewers'],
        ]);
    }

    /**
     * Cập nhật luồng YouTube Live cho phiên phát trực tiếp
     * POST /livestream/{id}/youtube-live
     */
    public function updateYouTubeLive(Request $request, int|string $id): JsonResponse
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            return response()->json(['status' => 'error', 'message' => 'Không có quyền thực hiện.'], 403);
        }

        $request->validate(['youtube_url' => 'nullable|string|max:500']);

        $result = $this->liveStreamService->updateYouTubeLive($id, $request->input('youtube_url'), $currentUser);

        return response()->json([
            'status'           => 'success',
            'message'          => $result['youtube_video_id'] ? 'Đã kích hoạt nguồn phát YouTube Live thành công!' : 'Đã tắt chế độ YouTube Live.',
            'youtube_video_id' => $result['youtube_video_id'],
            'embed_url'        => $result['embed_url'],
        ]);
    }

    /**
     * Kết thúc phiên phát trực tiếp
     * POST /livestream/{id}/end
     */
    public function endStream(Request $request, int|string $id): JsonResponse
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            return response()->json(['status' => 'error', 'message' => 'Không có quyền thực hiện.'], 403);
        }

        $result = $this->liveStreamService->endStream($id, $currentUser);

        return response()->json([
            'status'       => 'success',
            'duration'     => $result['duration'],
            'peak_viewers' => $result['peak_viewers'],
            'total_likes'  => $result['total_likes'],
        ]);
    }

    /**
     * Tải lên video đã ghi hình của phiên Live và đồng bộ lên Kênh YouTube
     * POST /livestream/{id}/recording
     */
    public function uploadRecording(Request $request, int|string $id): JsonResponse
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            return response()->json(['status' => 'error', 'message' => 'Không có quyền thực hiện.'], 403);
        }

        $request->validate(['recording' => 'required|file|max:512000']);

        $result = $this->liveStreamService->uploadRecording($id, $request->file('recording'), $currentUser);

        return response()->json([
            'status'           => 'success',
            'recording_url'    => $result['recording_url'],
            'youtube_video_id' => $result['youtube_video_id'],
            'is_youtube'       => $result['is_youtube'],
        ]);
    }

    /**
     * Xóa bản ghi phiên Livestream
     * DELETE /livestream/{id}
     */
    public function destroy(int|string $id): JsonResponse
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            return response()->json(['status' => 'error', 'message' => 'Không có quyền thực hiện.'], 403);
        }

        $this->liveStreamService->deleteStream($id, $currentUser);

        return response()->json([
            'status'  => 'success',
            'message' => 'Đã xóa phiên phát sóng thành công.',
        ]);
    }
}
