@extends('layouts.hkd')

@section('title', 'Chi Tiết Sản Phẩm — ' . $product->name)
@section('title_header', 'Chi Tiết Hàng Hóa & Dịch Vụ Kinh Doanh')

@section('content')
<style>
    .show-prod-container {
        max-width: 1200px;
        margin: 0 auto;
        padding-bottom: 40px;
    }

    /* Top Navigation Header */
    .show-prod-header {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 20px 24px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
    }

    .back-btn-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #475569;
        font-weight: 700;
        font-size: 0.88rem;
        text-decoration: none;
        background: #f1f5f9;
        padding: 8px 16px;
        border-radius: 30px;
        transition: all 0.2s ease;
    }
    .back-btn-pill:hover {
        background: #e2e8f0;
        color: #0f172a;
    }

    /* Grid 2 Columns */
    .prod-show-grid {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 24px;
    }

    @media (max-width: 960px) {
        .prod-show-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Cards */
    .detail-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
    }

    .detail-field-group {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
    }

    .detail-field-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .detail-field-label {
        font-size: 0.8rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .detail-field-val {
        font-size: 1.05rem;
        font-weight: 800;
        color: #0f172a;
    }

    /* Tabs */
    .prod-tabs-header {
        display: flex;
        gap: 8px;
        border-bottom: 2px solid #f1f5f9;
        padding-bottom: 12px;
        margin-bottom: 20px;
        overflow-x: auto;
    }

    .prod-tab-btn {
        background: transparent;
        border: none;
        padding: 8px 16px;
        border-radius: 10px;
        font-size: 0.88rem;
        font-weight: 700;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s;
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .prod-tab-btn:hover {
        background: #f8fafc;
        color: #0f172a;
    }

    .prod-tab-btn.active {
        background: #ecfdf5;
        color: #059669;
    }

    .tab-content-panel {
        display: none;
    }
    .tab-content-panel.active {
        display: block;
    }

    /* Image Preview Card */
    .prod-img-preview-box {
        width: 100%;
        height: 280px;
        border-radius: 16px;
        background: #f8fafc;
        border: 2px dashed #cbd5e1;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        position: relative;
    }

    .prod-img-preview-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .status-badge-big-in {
        background: #ecfdf5;
        color: #059669;
        border: 1px solid #a7f3d0;
        font-size: 0.82rem;
        font-weight: 800;
        padding: 4px 12px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .status-badge-big-out {
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fca5a5;
        font-size: 0.82rem;
        font-weight: 800;
        padding: 4px 12px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
</style>

<div class="show-prod-container">
    @php
        $sku = $specData['sku'] ?? ('SP-' . str_pad($product->id, 4, '0', STR_PAD_LEFT));
        $stockStat = $specData['stock_status'] ?? 'in_stock';
    @endphp

    <!-- 1. TOP HEADER -->
    <div class="show-prod-header">
        <div style="display: flex; align-items: center; gap: 14px;">
            <a href="{{ route('hkd.products.index') }}" class="back-btn-pill">
                <i class="fa-solid fa-arrow-left"></i> Danh sách sản phẩm
            </a>
            <div>
                <h2 style="font-size: 1.35rem; font-weight: 900; color: #0f172a; margin: 0;">
                    Chi Tiết Sản Phẩm: {{ $product->name }}
                </h2>
                <div style="font-size: 0.82rem; color: #64748b; margin-top: 2px;">
                    Mã hệ thống #{{ $product->id }} · SKU: <strong style="color: #0284c7;">{{ $sku }}</strong>
                </div>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 10px;">
            <a href="{{ route('hkd.products.edit', $product->id) }}" class="hkd-btn-action hkd-btn-emerald" style="text-decoration: none; padding: 10px 18px; font-size: 0.88rem;">
                <i class="fa-solid fa-pen"></i> Chỉnh Sửa
            </a>
            <form action="{{ route('hkd.products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')">
                @csrf @method('DELETE')
                <button type="submit" class="hkd-btn-action hkd-btn-outline-danger" style="padding: 10px 16px; font-size: 0.88rem;">
                    <i class="fa-solid fa-trash"></i> Xóa
                </button>
            </form>
        </div>
    </div>

    <!-- 2. MAIN GRID -->
    <div class="prod-show-grid">
        
        <!-- LEFT COLUMN: PRODUCT ATTRIBUTES & TABS -->
        <div>
            <!-- CARD 1: BASIC IDENTIFICATION -->
            <div class="detail-card">
                <div style="font-size: 1rem; font-weight: 800; color: #0f172a; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-box-archive" style="color: #059669;"></i>
                    <span>Thông Tin Nhận Diện Sản Phẩm</span>
                </div>

                <div class="detail-field-group">
                    <div class="detail-field-item">
                        <span class="detail-field-label">Tên Sản Phẩm</span>
                        <span class="detail-field-val">{{ $product->name }}</span>
                    </div>

                    <div class="detail-field-item">
                        <span class="detail-field-label">Mã Sản Phẩm (SKU)</span>
                        <span class="detail-field-val" style="color: #0284c7; font-family: monospace;">{{ $sku }}</span>
                    </div>

                    <div class="detail-field-item">
                        <span class="detail-field-label">Đơn Vị Tính Cơ Bản</span>
                        <span class="detail-field-val">{{ $product->unit ?: 'Chưa đặt' }}</span>
                    </div>

                    <div class="detail-field-item">
                        <span class="detail-field-label">Chứng Nhận OCOP</span>
                        <span class="detail-field-val">
                            @if(!empty($product->star_rating))
                                <span style="background: #fef3c7; color: #d97706; font-size: 0.85rem; font-weight: 800; padding: 4px 10px; border-radius: 12px; display: inline-block;">
                                    ⭐ {{ $product->star_rating }}
                                </span>
                            @else
                                <span style="color: #64748b; font-weight: 600; font-size: 0.9rem;">Sản phẩm kinh doanh thông thường</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <!-- CARD 2: TABBED DETAILS -->
            <div class="detail-card">
                <div class="prod-tabs-header">
                    <button type="button" class="prod-tab-btn active" onclick="switchTab('tab-pricing', this)">
                        <i class="fa-solid fa-tag"></i> 1. Giá & Giá Sỉ
                    </button>
                    <button type="button" class="prod-tab-btn" onclick="switchTab('tab-inventory', this)">
                        <i class="fa-solid fa-warehouse"></i> 2. Quản Lý Kho
                    </button>
                    <button type="button" class="prod-tab-btn" onclick="switchTab('tab-conversions', this)">
                        <i class="fa-solid fa-right-left"></i> 3. Quy Đổi & Thuộc Tính
                    </button>
                    <button type="button" class="prod-tab-btn" onclick="switchTab('tab-description', this)">
                        <i class="fa-solid fa-file-pen"></i> 4. Mô Tả Chi Tiết
                    </button>
                    <button type="button" class="prod-tab-btn" onclick="switchTab('tab-policies', this)">
                        <i class="fa-solid fa-shield-halved"></i> 5. Cam Kết & Chính Sách
                    </button>
                </div>

                <!-- TAB 1: GIÁ BÁN & GIÁ SỈ -->
                <div id="tab-pricing" class="tab-content-panel active">
                    <div class="detail-field-group">
                        <div class="detail-field-item">
                            <span class="detail-field-label">Giá Bán Lẻ Niêm Yết</span>
                            <span class="detail-field-val" style="color: #059669; font-size: 1.4rem;">
                                {{ number_format($product->price) }}đ
                                <span style="font-size: 0.85rem; color: #64748b; font-weight: 600;">/ {{ $product->unit ?: 'Đơn vị' }}</span>
                            </span>
                        </div>

                        <div class="detail-field-item">
                            <span class="detail-field-label">Giá Bán Sỉ Theo Lô</span>
                            <span class="detail-field-val" style="color: #0284c7; font-size: 1.18rem;">
                                @if(!empty($specData['wholesale_price']))
                                    {{ number_format($specData['wholesale_price']) }}đ
                                @else
                                    <span style="color: #94a3b8; font-weight: 500;">Chưa thiết lập</span>
                                @endif
                            </span>
                        </div>

                        <div class="detail-field-item">
                            <span class="detail-field-label">Số Lượng Tối Thiểu Mua Sỉ</span>
                            <span class="detail-field-val">
                                @if(!empty($specData['wholesale_qty']))
                                    {{ number_format($specData['wholesale_qty']) }} {{ $product->unit ?: 'Đơn vị' }}
                                @else
                                    <span style="color: #94a3b8; font-weight: 500;">Chưa thiết lập</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: QUẢN LÝ KHO -->
                <div id="tab-inventory" class="tab-content-panel">
                    <div class="detail-field-group">
                        <div class="detail-field-item">
                            <span class="detail-field-label">Trạng Thái Tồn Kho</span>
                            <div>
                                @if($stockStat === 'out_of_stock')
                                    <span class="status-badge-big-out">🔴 Hết hàng (Tạm ngưng nhận đơn)</span>
                                @elseif($stockStat === 'pre_order')
                                    <span style="background: #fef3c7; color: #d97706; border: 1px solid #fde68a; font-size: 0.82rem; font-weight: 800; padding: 4px 12px; border-radius: 20px; display: inline-flex; align-items: center; gap: 6px;">🟡 Đặt hàng trước (Pre-order)</span>
                                @else
                                    <span class="status-badge-big-in">🟢 Còn hàng (Sẵn sàng bán)</span>
                                @endif
                            </div>
                        </div>

                        <div class="detail-field-item">
                            <span class="detail-field-label">Số Lượng Trong Kho</span>
                            <span class="detail-field-val">
                                @if(!empty($specData['stock_qty']))
                                    {{ number_format($specData['stock_qty']) }} {{ $product->unit ?: 'Đơn vị' }}
                                @else
                                    <span style="color: #94a3b8; font-weight: 500;">Chưa cập nhật số lượng cụ thể</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                <!-- TAB 3: QUY ĐỔI & THUỘC TÍNH -->
                <div id="tab-conversions" class="tab-content-panel">
                    <div class="detail-field-group">
                        <div class="detail-field-item">
                            <span class="detail-field-label">Quy Đổi Đơn Vị</span>
                            <span class="detail-field-val">
                                {{ $specData['unit_conversion'] ?? 'Chưa khai báo quy đổi' }}
                            </span>
                        </div>

                        <div class="detail-field-item">
                            <span class="detail-field-label">Danh Mục / Loại Hình Kinh Doanh</span>
                            <span class="detail-field-val">
                                {{ $specData['product_type'] ?? 'Hàng hóa kinh doanh' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- TAB 4: MÔ TẢ CHI TIẾT -->
                <div id="tab-description" class="tab-content-panel">
                    <span class="detail-field-label" style="margin-bottom: 8px; display: block;">Mô Tả Sản Phẩm / Dịch Vụ Niêm Yết</span>
                    @if($product->description)
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; font-size: 0.92rem; color: #334155; line-height: 1.6; white-space: pre-line;">
                            {{ $product->description }}
                        </div>
                    @else
                        <div style="color: #cbd5e1; font-style: italic;">Chưa có nội dung mô tả chi tiết</div>
                    @endif
                </div>

                <!-- TAB 5: CAM KẾT & CHÍNH SÁCH -->
                <div id="tab-policies" class="tab-content-panel">
                    <div class="detail-field-group" style="grid-template-columns: 1fr;">
                        <div class="detail-field-item">
                            <span class="detail-field-label">Cam Kết Chất Lượng</span>
                            <span class="detail-field-val" style="font-size: 0.92rem; font-weight: 600; color: #334155;">
                                {{ $specData['commitment_text'] ?? 'Đảm bảo hàng chính hãng & tiêu chuẩn chất lượng đã đăng ký' }}
                            </span>
                        </div>

                        <div class="detail-field-item">
                            <span class="detail-field-label">Chính Sách Giao Hàng & Đặt Hàng</span>
                            <span class="detail-field-val" style="font-size: 0.92rem; font-weight: 600; color: #334155;">
                                {{ $specData['delivery_text'] ?? 'Giao hàng toàn quốc hoặc nhận tại cơ sở kinh doanh' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: IMAGE & QUICK METADATA -->
        <div>
            <div class="detail-card">
                <div style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin-bottom: 14px; border-left: 3px solid #059669; padding-left: 10px;">
                    Ảnh Đại Diện Sản Phẩm
                </div>

                <div class="prod-img-preview-box">
                    @if($product->image_path)
                        <img src="{{ $product->image_path }}" alt="{{ $product->name }}">
                    @else
                        <div style="text-align: center; color: #cbd5e1;">
                            <i class="fa-solid fa-image" style="font-size: 3rem; margin-bottom: 8px;"></i>
                            <div style="font-size: 0.8rem; font-weight: 600;">Chưa cập nhật hình ảnh</div>
                        </div>
                    @endif
                </div>

                <div style="margin-top: 20px; border-top: 1px solid #f1f5f9; padding-top: 16px; display: flex; flex-direction: column; gap: 10px;">
                    <div style="display: flex; justify-content: space-between; font-size: 0.85rem;">
                        <span style="color: #64748b; font-weight: 600;">Cơ sở kinh doanh:</span>
                        <strong style="color: #0f172a;">{{ $eatery->name }}</strong>
                    </div>

                    <div style="display: flex; justify-content: space-between; font-size: 0.85rem;">
                        <span style="color: #64748b; font-weight: 600;">Ngày cập nhật:</span>
                        <strong style="color: #0f172a;">{{ \Carbon\Carbon::parse($product->updated_at ?? now())->format('d/m/Y H:i') }}</strong>
                    </div>
                </div>

                <div style="margin-top: 20px;">
                    <a href="{{ route('ocop.product.show', $product->id) }}" target="_blank" 
                       class="hkd-btn-action hkd-btn-outline" 
                       style="width: 100%; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 8px; font-size: 0.88rem;">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i> Xem Trang Công Khai
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    function switchTab(tabId, btn) {
        document.querySelectorAll('.tab-content-panel').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.prod-tab-btn').forEach(b => b.classList.remove('active'));
        
        const target = document.getElementById(tabId);
        if (target) target.classList.add('active');
        btn.classList.add('active');
    }
</script>
@endsection
