<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\BusinessManagementServiceInterface;
use Illuminate\Http\Request;

class BusinessManagementController extends Controller
{
    public function __construct(
        protected BusinessManagementServiceInterface $businessService
    ) {}

    public function switchBusiness(Request $request)
    {
        $this->businessService->switchBusiness($request->input('hkd_id'));
        return redirect()->back()->with('success', 'Đã chuyển đổi sang quản lý Hộ kinh doanh được chọn!');
    }

    public function dashboard()
    {
        $data = $this->businessService->getDashboardData();
        return view('hkd.dashboard', $data);
    }

    public function showProfile()
    {
        $data = $this->businessService->getProfileData();
        return view('hkd.profile', $data);
    }

    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'phone'         => 'required|string|max:20',
            'address'       => 'required|string|max:500',
            'description'   => 'nullable|string',
            'opening_hours' => 'nullable|string',
            'price_range'   => 'nullable|string',
            'latitude'      => 'nullable|numeric',
            'longitude'     => 'nullable|numeric',
            'mst'           => 'nullable|string',
            'industry'      => 'nullable|string',
            'owner_name'    => 'nullable|string',
            'story'         => 'nullable|string',
            'bank_account'  => 'nullable|string',
            'bank_name'     => 'nullable|string',
            'bank_holder'   => 'nullable|string',
        ]);

        $this->businessService->updateProfile($validated, $request->file('image'));
        return back()->with('success', 'Đã cập nhật Hồ sơ Cơ sở kinh doanh thành công!');
    }

    public function products()
    {
        $data = $this->businessService->getProductsPaginated(15);
        return view('hkd.products', $data);
    }

    public function createProduct(Request $request)
    {
        $ctx = $this->businessService->getBusinessContext();
        return view('hkd.product-create', ['eatery' => $ctx['eatery']]);
    }

    public function editProduct(Request $request, $id)
    {
        $data = $this->businessService->getProductForEdit($id);
        if (!$data) {
            return redirect()->route('hkd.products.index')->with('error', 'Sản phẩm không tồn tại!');
        }
        return view('hkd.product-edit', $data);
    }

    public function showProduct(Request $request, $id)
    {
        $data = $this->businessService->getProductDetails($id);
        if (!$data) {
            return redirect()->route('hkd.products.index')->with('error', 'Sản phẩm không tồn tại!');
        }
        return view('hkd.product-show', $data);
    }

    public function storeProduct(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'price'            => 'required|numeric|min:0',
            'unit'             => 'nullable|string',
            'star_rating'      => 'nullable|numeric',
            'description'      => 'nullable|string',
            'sku'              => 'nullable|string',
            'stock_status'     => 'nullable|string',
            'stock_qty'        => 'nullable|numeric',
            'unit_conversion'  => 'nullable|string',
            'wholesale_price'  => 'nullable|numeric',
            'wholesale_qty'    => 'nullable|numeric',
            'product_type'     => 'nullable|string',
            'commitment_text'  => 'nullable|string',
            'delivery_text'    => 'nullable|string',
            'certificate_info' => 'nullable|string',
            'order_policy'     => 'nullable|string',
            'payment_policy'   => 'nullable|string',
            'is_signature'     => 'nullable',
        ]);

        $this->businessService->storeProduct($validated, $request->file('image'));
        return redirect()->route('hkd.products.index')->with('success', 'Đã thêm sản phẩm / mặt hàng kinh doanh mới thành công!');
    }

    public function updateProduct(Request $request, $id)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'price'            => 'required|numeric|min:0',
            'unit'             => 'nullable|string',
            'star_rating'      => 'nullable|numeric',
            'description'      => 'nullable|string',
            'sku'              => 'nullable|string',
            'stock_status'     => 'nullable|string',
            'stock_qty'        => 'nullable|numeric',
            'unit_conversion'  => 'nullable|string',
            'wholesale_price'  => 'nullable|numeric',
            'wholesale_qty'    => 'nullable|numeric',
            'product_type'     => 'nullable|string',
            'commitment_text'  => 'nullable|string',
            'delivery_text'    => 'nullable|string',
            'certificate_info' => 'nullable|string',
            'order_policy'     => 'nullable|string',
            'payment_policy'   => 'nullable|string',
            'is_signature'     => 'nullable',
        ]);

        try {
            $this->businessService->updateProduct($id, $validated, $request->file('image'));
            return redirect()->route('hkd.products.index')->with('success', 'Đã cập nhật thông tin sản phẩm thành công!');
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('hkd.products.index')->with('error', $e->getMessage());
        }
    }

    public function destroyProduct($id)
    {
        $this->businessService->deleteProduct($id);
        return back()->with('success', 'Đã xóa sản phẩm khỏi gian hàng!');
    }

    public function orders(Request $request)
    {
        $data = $this->businessService->getOrdersPaginated($request->query('status'), 15);
        return view('hkd.orders', $data);
    }

    public function showOrder($id)
    {
        $data = $this->businessService->getOrderDetails($id);
        if (!$data) {
            return back()->with('error', 'Đơn hàng không tồn tại hoặc chức năng chưa sẵn sàng!');
        }
        return view('hkd.order-detail', $data);
    }

    public function updateOrderStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:pending,processing,completed,cancelled',
        ]);

        $success = $this->businessService->updateOrderStatus($id, $request->input('status'));
        if (!$success) {
            return back()->with('error', 'Đơn hàng không tồn tại hoặc chức năng chưa sẵn sàng!');
        }
        return back()->with('success', 'Đã cập nhật trạng thái đơn hàng!');
    }

    public function reports(Request $request)
    {
        $data = $this->businessService->getReportsData();
        return view('hkd.reports', $data);
    }

    public function qr()
    {
        $data = $this->businessService->getQrData();
        return view('hkd.qr', $data);
    }

    public function chatIndex()
    {
        $data = $this->businessService->getChatContext();
        return view('hkd.chat', $data);
    }
}
