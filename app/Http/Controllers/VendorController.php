<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\VendorServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorController extends Controller
{
    public function __construct(
        protected VendorServiceInterface $vendorService
    ) {}

    private function checkAccess(): void
    {
        $role = session('user_role') ?: (Auth::user() ? Auth::user()->role : null);
        $this->vendorService->verifyVendor($role);
    }

    private function getContext(): array
    {
        $user = Auth::user();
        $userId = $user ? $user->id : null;
        $userPhone = $user->phone ?? session('user_phone') ?? '';
        $userName = $user->name ?? session('user_name') ?? '';
        $activeEntityKey = session('active_seller_entity');

        return $this->vendorService->getVendorStallContext($userId, $userPhone, $userName, $activeEntityKey);
    }

    /**
     * Chuyển đổi thực thể kinh doanh đang quản lý (Gian hàng Chợ <-> Cơ sở kinh doanh <-> Tuyến đường)
     */
    public function switchEntity(Request $request)
    {
        $this->checkAccess();
        $entityKey = $request->input('entity_key');
        if ($entityKey) {
            session(['active_seller_entity' => $entityKey]);
        }
        return redirect()->back()->with('success', 'Đã chuyển đổi sang quản lý: ' . ($request->input('entity_name') ?: 'Cơ sở được chọn'));
    }

    /**
     * Xem Thông tin Hồ Sơ Cơ Sở Kinh Doanh / Doanh Nghiệp
     */
    public function showBusinessProfile()
    {
        $this->checkAccess();
        $context = $this->getContext();
        return view('seller.business-profile', $context);
    }

    /**
     * Lưu cập nhật Hồ Sơ Cơ Sở Kinh Doanh
     */
    public function updateBusinessProfile(Request $request)
    {
        $this->checkAccess();
        $context = $this->getContext();

        $request->validate([
            'name'          => 'required|string|max:255',
            'phone'         => 'nullable|string|max:30',
            'address'       => 'nullable|string|max:255',
            'opening_hours' => 'nullable|string|max:100',
            'price_range'   => 'nullable|string|max:100',
            'description'   => 'nullable|string',
            'image'         => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        try {
            $this->vendorService->updateBusinessProfile(
                $context,
                $request->only(['name', 'phone', 'address', 'opening_hours', 'price_range', 'description']),
                $request->file('image'),
                Auth::id()
            );

            return redirect()->back()->with('success', '🎉 Đã cập nhật thông tin Cơ sở kinh doanh thành công! Toàn bộ thông tin đã được đồng bộ lên Bản đồ số toàn huyện.');
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Dashboard Tiểu Thương / Chủ Gian Hàng / Chủ Cơ Sở OCOP
     */
    public function dashboard()
    {
        $this->checkAccess();
        $context = $this->getContext();

        if (isset($context['categorySlug']) && $context['categorySlug'] === 'wellness-care') {
            return redirect()->route('health-station.dashboard');
        }

        $stats = $this->vendorService->getDashboardData($context);
        $viewName = !empty($context['isOcopSeller']) ? 'seller.dashboard-ocop' : 'seller.dashboard';

        return view($viewName, array_merge($context, $stats));
    }

    /**
     * Cập nhật Hồ sơ di sản & Thông tin liên hệ OCOP
     */
    public function updateDossier(Request $request)
    {
        $this->checkAccess();
        $context = $this->getContext();

        $eateryUpdate = $request->only(['phone', 'opening_hours', 'price_range', 'description']);
        $ocopUpdate = $request->only(['audio_narrative', 'story', 'artisans', 'ingredients', 'timeline', 'fun_fact', 'heritage_year', 'star_rating']);

        $this->vendorService->updateDossier($context['eateryId'], $eateryUpdate, $ocopUpdate);

        return redirect()->back()->with('success', '🎉 Đã cập nhật Hồ Sơ Di Sản & Thuyết Minh OCOP thành công! Tất cả thông tin đã được phát hành trực tiếp lên bản đồ.');
    }

    /**
     * Danh sách & Quản lý Sản phẩm / Món ăn của Gian hàng
     */
    public function products()
    {
        $this->checkAccess();
        $context = $this->getContext();
        return view('seller.products', $context);
    }

    /**
     * Thêm sản phẩm mới cho Gian hàng
     */
    public function storeProduct(Request $request)
    {
        $this->checkAccess();
        $context = $this->getContext();

        $request->validate([
            'name'        => 'required|string|max:100',
            'price'       => 'required',
            'unit'        => 'nullable|string|max:20',
            'origin'      => 'nullable|string|max:255',
            'star_rating' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'description' => 'nullable|string',
        ]);

        $this->vendorService->storeProduct(
            $context,
            $request->only(['name', 'price', 'unit', 'origin', 'star_rating', 'description']),
            $request->file('image')
        );

        return redirect()->back()->with('success', 'Đã thêm sản phẩm mới thành công!');
    }

    /**
     * Cập nhật thông tin / giá / ảnh sản phẩm
     */
    public function updateProduct(Request $request, $id)
    {
        $this->checkAccess();

        $request->validate([
            'name'        => 'required|string|max:100',
            'price'       => 'required',
            'unit'        => 'nullable|string|max:20',
            'origin'      => 'nullable|string|max:255',
            'star_rating' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'description' => 'nullable|string',
        ]);

        $updated = $this->vendorService->updateProduct(
            (int) $id,
            $request->only(['name', 'price', 'unit', 'origin', 'star_rating', 'description']),
            $request->file('image')
        );

        if (!$updated) {
            return redirect()->back()->with('error', 'Sản phẩm không tồn tại!');
        }

        return redirect()->back()->with('success', 'Đã cập nhật sản phẩm OCOP thành công!');
    }

    /**
     * Xóa sản phẩm khỏi Gian hàng
     */
    public function destroyProduct($id)
    {
        $this->checkAccess();
        $this->vendorService->destroyProduct((int) $id);

        return redirect()->back()->with('success', 'Đã xóa sản phẩm khỏi gian hàng!');
    }

    /**
     * Quản lý đơn hàng phát sinh của Gian hàng
     */
    public function orders()
    {
        $this->checkAccess();
        $context = $this->getContext();
        $orders = $this->vendorService->getOrders($context, 20);

        return view('seller.orders', array_merge($context, ['orders' => $orders]));
    }

    /**
     * Trang xem chi tiết đơn hàng
     */
    public function showOrder($id)
    {
        $this->checkAccess();
        $context = $this->getContext();
        $role = session('user_role') ?: (Auth::user() ? Auth::user()->role : '');

        $order = $this->vendorService->showOrder((int) $id, $context, $role);
        if (!$order) {
            return redirect()->route('seller.orders.index')->with('error', 'Đơn hàng không tồn tại!');
        }

        return view('seller.order-detail', array_merge($context, ['order' => $order]));
    }

    /**
     * Cập nhật trạng thái đơn hàng
     */
    public function updateOrderStatus(Request $request, $id)
    {
        $this->checkAccess();
        $request->validate([
            'status'        => 'required|string|in:confirmed,ready,preparing,processing,shipping,completed,cancelled',
            'cancel_reason' => 'nullable|string|max:255',
        ], [
            'status.in' => 'Trạng thái đơn hàng không hợp lệ!',
        ]);

        $msg = $this->vendorService->updateOrderStatus(
            (int) $id,
            $request->input('status'),
            $request->input('cancel_reason')
        );

        return redirect()->back()->with('success', $msg);
    }

    /**
     * API JSON: real-time polling đơn hàng Seller
     */
    public function ordersJson()
    {
        $this->checkAccess();
        $context = $this->getContext();
        $data = $this->vendorService->getOrdersJson($context);

        return response()->json($data);
    }

    /**
     * Giao diện Cấu Hình & Cập Nhật Thông Tin Gian Hàng / VietQR
     */
    public function showProfile()
    {
        $this->checkAccess();
        $context = $this->getContext();
        $profileData = $this->vendorService->getProfileData($context, Auth::id());

        return view('seller.profile', array_merge($context, $profileData));
    }

    /**
     * Xử lý Cập Nhật Thông Tin Gian Hàng, SĐT, Ngân Hàng & VietQR
     */
    public function updateProfile(Request $request)
    {
        $this->checkAccess();
        $context = $this->getContext();

        $request->validate([
            'seller_name'  => 'required|string|max:255',
            'seller_phone' => 'required|string|max:50',
            'stall_name'   => 'nullable|string|max:255',
            'address'      => 'nullable|string|max:500',
            'map_link'     => 'nullable|string|max:1000',
            'latitude'     => 'nullable|numeric',
            'longitude'    => 'nullable|numeric',
            'bank_name'    => 'nullable|string|max:100',
            'bank_account' => 'nullable|string|max:100',
            'bank_holder'  => 'nullable|string|max:255',
            'origin'       => 'nullable|string|max:500',
            'attp'         => 'nullable|string|max:255',
            'description'  => 'nullable|string|max:1000',
        ]);

        $this->vendorService->updateProfile(
            $context,
            $request->only([
                'seller_name', 'seller_phone', 'stall_name', 'address', 'map_link',
                'latitude', 'longitude', 'bank_name', 'bank_account', 'bank_holder',
                'origin', 'attp', 'description'
            ]),
            Auth::id(),
            $request->file('stall_image'),
            $request->input('stall_image_url'),
            $request->file('avatar'),
            $request->input('avatar_url')
        );

        $stallName = trim($request->input('stall_name')) ?: $context['stallName'];
        $sellerName = trim($request->input('seller_name'));
        session(['stall_name' => $stallName, 'user_name' => $sellerName]);

        return redirect()->back()->with('success', '🎉 Đã cập nhật thành công Cấu hình Gian hàng & Thanh toán VietQR! Thông tin mới đã được cập nhật trực tiếp trên bản đồ & gian hàng công khai.');
    }

    /**
     * Giao diện Chat Gian Hàng
     */
    public function chatIndex(Request $request)
    {
        $this->checkAccess();
        $context = $this->getContext();

        return view('seller.chat', array_merge($context, [
            'selectedCustomerId' => $request->query('customer_id'),
        ]));
    }

    /**
     * Lấy danh sách cuộc hội thoại
     */
    public function apiChatConversations(Request $request)
    {
        $this->checkAccess();
        $context = $this->getContext();
        $sellerUserId = Auth::id() ?? session('user_id');

        $conversations = $this->vendorService->getChatConversations($context, $sellerUserId);

        return response()->json([
            'success'       => true,
            'stall_name'    => $context['stallName'],
            'conversations' => $conversations,
        ]);
    }

    /**
     * Lấy lịch sử tin nhắn phòng chat
     */
    public function apiChatMessages(Request $request)
    {
        $this->checkAccess();
        $context = $this->getContext();
        $room = $request->input('room', 'private');
        $customerId = $request->input('customer_id') ? (int) $request->input('customer_id') : null;
        $sellerUserId = Auth::id() ?? session('user_id');

        $messages = $this->vendorService->getChatMessages($context, $room, $customerId, $sellerUserId);

        return response()->json([
            'success'  => true,
            'messages' => $messages,
            'count'    => count($messages),
        ]);
    }

    /**
     * Gửi tin nhắn từ gian hàng
     */
    public function apiChatSend(Request $request)
    {
        $this->checkAccess();
        $context = $this->getContext();
        $userId = Auth::id() ?? session('user_id');

        $request->validate([
            'message_text' => 'required_without_all:product_id,image|nullable|string|max:500',
            'product_id'   => 'nullable|integer',
            'image'        => 'nullable|image|max:5120',
            'customer_id'  => 'nullable|integer',
            'room'         => 'nullable|string',
        ]);

        $message = $this->vendorService->sendChatMessage(
            $context,
            $userId,
            $request->only(['message_text', 'product_id', 'customer_id', 'room']),
            $request->file('image')
        );

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    /**
     * Lấy số tin nhắn chưa đọc & tin mới nhất
     */
    public function apiChatUnreadCount(Request $request)
    {
        $this->checkAccess();
        $context = $this->getContext();
        $sellerUserId = Auth::id() ?? session('user_id');

        $result = $this->vendorService->getChatUnreadCount($context, $sellerUserId);

        return response()->json($result);
    }
}
