@extends('layouts.seller')

@section('title', 'Kênh Điều Hành Chủ Cơ Sở OCOP — ' . $stallName)

@section('content')

@php
    $totalRevenue = $totalRevenue ?? 0;
    $ordersCount = $ordersCount ?? 0;
    $productsCount = $productsCount ?? 0;
    $pDoc = $primaryProduct ?? null;
@endphp

@if(session('success'))
    <div style="padding: 14px 20px; background: #ecfdf5; border: 1.5px solid #10b981; color: #065f46; border-radius: 12px; font-weight: 800; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
        <span>🎉</span> {{ session('success') }}
    </div>
@endif

<style>
.ocop-hero-header {
    background: linear-gradient(135deg, #064e3b 0%, #047857 50%, #059669 100%);
    border-radius: 22px;
    padding: 28px 32px;
    color: #ffffff;
    box-shadow: 0 18px 36px -10px rgba(5, 150, 105, 0.35);
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}

.ocop-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 14px;
    background: rgba(255, 255, 255, 0.18);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 30px;
    color: #fef08a;
    font-size: 0.78rem;
    font-weight: 800;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    margin-bottom: 12px;
}

.ocop-tab-card {
    background: #ffffff;
    border: 1.5px solid #a7f3d0;
    border-radius: 18px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: 0 10px 25px -5px rgba(5, 150, 105, 0.08);
}

.form-label-ocop {
    font-size: 0.85rem;
    font-weight: 800;
    color: #064e3b;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.form-input-ocop {
    width: 100%;
    padding: 10px 14px;
    border: 1.5px solid #cbd5e1;
    border-radius: 10px;
    font-size: 0.88rem;
    color: #0f172a;
    transition: all 0.2s ease;
    background: #f8fafc;
}

.form-input-ocop:focus {
    outline: none;
    border-color: #059669;
    background: #ffffff;
    box-shadow: 0 0 0 4px rgba(5, 150, 105, 0.12);
}
</style>

<!-- OCOP HERO HEADER -->
<div class="ocop-hero-header">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
        <div>
            <div class="ocop-badge">
                <span>🌾</span> KÊNH CHỦ CƠ SỞ NÔNG SẢN SỐ & ĐẶC SẢN OCOP
            </div>
            <h1 style="font-size: 1.85rem; font-weight: 900; margin: 0 0 8px 0; letter-spacing: -0.02em; color: #ffffff;">
                {{ $stallName }}
            </h1>
            <p style="margin: 0; color: #ecfdf5; font-size: 0.94rem;">
                📍 Địa chỉ: <strong>{{ $market ? $market->address : 'Đông Anh, Hà Nội' }}</strong> | Người đại diện: <strong>{{ $sellerName }}</strong> (📞 {{ $sellerPhone ?: 'Chưa cập nhật' }})
            </p>
        </div>
        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <a href="/dia-diem/{{ $market ? $market->slug : '' }}" target="_blank" class="btn-admin" style="background: #ffffff; color: #064e3b; border: none; padding: 12px 22px; border-radius: 12px; font-weight: 900; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 8px 20px rgba(0,0,0,0.15);">
                👁️ Xem Trang Trực Tuyến Bản Đồ
            </a>
        </div>
    </div>
</div>

<!-- STATS SUMMARY -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 18px; margin-bottom: 24px;">
    <div style="background: #ffffff; border: 1.5px solid #a7f3d0; border-radius: 16px; padding: 20px; box-shadow: 0 4px 14px rgba(5, 150, 105, 0.06);">
        <div style="font-size: 0.75rem; font-weight: 800; color: #047857; text-transform: uppercase;">SẢN PHẨM OCOP BÀY BÁN</div>
        <div style="font-size: 2rem; font-weight: 900; color: #059669; margin: 4px 0;">{{ $productsCount }} Sản Phẩm</div>
        <div style="font-size: 0.78rem; color: #047857; font-weight: 700;">🌾 Đã số hóa đưa lên bản đồ</div>
    </div>

    <div style="background: #ffffff; border: 1.5px solid #bae6fd; border-radius: 16px; padding: 20px; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.06);">
        <div style="font-size: 0.75rem; font-weight: 800; color: #0369a1; text-transform: uppercase;">PHÂN HẠNG CHỨNG NHẬN</div>
        <div style="font-size: 2rem; font-weight: 900; color: #0284c7; margin: 4px 0;">⭐ {{ $pDoc->star_rating ?? '4 Sao' }}</div>
        <div style="font-size: 0.78rem; color: #0369a1; font-weight: 700;">🏆 OCOP Cấp Quốc Gia / Xã</div>
    </div>

    <div style="background: #ffffff; border: 1.5px solid #fef08a; border-radius: 16px; padding: 20px; box-shadow: 0 4px 14px rgba(217, 119, 6, 0.06);">
        <div style="font-size: 0.75rem; font-weight: 800; color: #b45309; text-transform: uppercase;">HỒ SƠ DI SẢN & THUYẾT MINH</div>
        <div style="font-size: 2rem; font-weight: 900; color: #d97706; margin: 4px 0;">{{ !empty($pDoc->audio_narrative) ? '🎧 Đã có giọng nói' : '📝 Đã tạo câu chuyện' }}</div>
        <div style="font-size: 0.78rem; color: #b45309; font-weight: 700;">🔊 AI Text-to-Speech phát thanh</div>
    </div>

    <div style="background: #ffffff; border: 1.5px solid #ddd6fe; border-radius: 16px; padding: 20px; box-shadow: 0 4px 14px rgba(124, 58, 237, 0.06);">
        <div style="font-size: 0.75rem; font-weight: 800; color: #6d28d9; text-transform: uppercase;">TRUY XUẤT NGUỒN GỐC</div>
        <div style="font-size: 2rem; font-weight: 900; color: #7c3aed; margin: 4px 0;">100% QR Code</div>
        <div style="font-size: 0.78rem; color: #6d28d9; font-weight: 700;">📲 Mã QR quét thông minh</div>
    </div>
</div>

<!-- SECTION 1: CẬP NHẬT HỒ SƠ DI SẢN OCOP -->
<div class="ocop-tab-card">
    <div style="border-bottom: 2px solid #ecfdf5; padding-bottom: 14px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <h2 style="font-size: 1.25rem; font-weight: 900; color: #064e3b; margin: 0; display: flex; align-items: center; gap: 8px;">
            <span>📜</span> Cập Nhật Hồ Sơ Di Sản & Giọng Nói Thuyết Minh (OCOP Dossier)
        </h2>
        <span style="font-size: 0.8rem; background: #ecfdf5; color: #047857; font-weight: 800; padding: 4px 12px; border-radius: 20px; border: 1px solid #a7f3d0;">
            Hiển thị trực tiếp cho du khách trên bản đồ
        </span>
    </div>

    <form action="{{ route('seller.dossier.update') }}" method="POST">
        @csrf
        
        <!-- THÔNG TIN LIÊN HỆ -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 20px;">
            <div>
                <label class="form-label-ocop">📞 Số Điện Thoại Liên Hệ</label>
                <input type="text" name="phone" value="{{ $market->phone ?? '' }}" class="form-input-ocop" placeholder="Ví dụ: 0987654321">
            </div>
            <div>
                <label class="form-label-ocop">⏰ Giờ Mở Cửa / Đón Khách</label>
                <input type="text" name="opening_hours" value="{{ $market->opening_hours ?? '' }}" class="form-input-ocop" placeholder="Ví dụ: 07:30 - 18:00 (Hàng ngày)">
            </div>
            <div>
                <label class="form-label-ocop">💰 Mức Giá Tham Khảo</label>
                <input type="text" name="price_range" value="{{ $market->price_range ?? '' }}" class="form-input-ocop" placeholder="Ví dụ: 30.000đ - 100.000đ/gói">
            </div>
            <div>
                <label class="form-label-ocop">🌾 Năm Công Nhận OCOP / Lịch Sử</label>
                <input type="text" name="heritage_year" value="{{ $pDoc->heritage_year ?? '' }}" class="form-input-ocop" placeholder="Ví dụ: Năm 2022 / Từ thời Hùng Vương">
            </div>
        </div>

        <!-- GIỌNG NÓI THUYẾT MINH -->
        <div style="margin-bottom: 20px; background: #f0fdf4; border: 1.5px solid #bbf7d0; border-radius: 14px; padding: 18px;">
            <label class="form-label-ocop" style="font-size: 0.95rem; color: #047857;">
                <span>🎧</span> Nội Dung Giọng Nói Thuyết Minh (AI Audio Narrative Text-to-Speech)
            </label>
            <p style="font-size: 0.8rem; color: #15803d; margin: 0 0 10px 0; line-height: 1.4;">
                Nhập đoạn văn bản giới thiệu sâu lắng về sản phẩm OCOP & làng nghề. Hệ thống AI sẽ tự động đọc bằng giọng phát thanh truyền cảm khi du khách bấm <strong>"🎧 Nghe kể chuyện di sản"</strong> trên web/app.
            </p>
            <textarea name="audio_narrative" rows="3" class="form-input-ocop" style="resize: vertical; background: #ffffff;" placeholder="Ví dụ: Chào mừng quý khách đến với HTX nông nghiệp dược liệu công nghệ cao KOVI. Nằm tại vùng đất Cổ Loa lịch sử, sản phẩm Đông Trùng Hạ Thảo của chúng tôi được nuôi trồng theo công nghệ sinh học khép kín hoàn toàn tự nhiên...">{{ $pDoc->audio_narrative ?? '' }}</textarea>
        </div>

        <!-- CÁC Ô HỒ SƠ DI SẢN -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label class="form-label-ocop">🏛️ Nguồn Gốc & Câu Chuyện Làng Nghề (Story)</label>
                <textarea name="story" rows="4" class="form-input-ocop" style="resize: vertical;" placeholder="Nhập câu chuyện sản phẩm, ý nghĩa văn hóa, quy trình sản xuất lâu đời...">{{ $pDoc->story ?? '' }}</textarea>
            </div>
            <div>
                <label class="form-label-ocop">👨‍🍳 Nghệ Nhân Truyền Nghề / Người Giữ Lửa (Artisans)</label>
                <textarea name="artisans" rows="4" class="form-input-ocop" style="resize: vertical;" placeholder="Tên nghệ nhân tiêu biểu, số năm kinh nghiệm, tâm huyết làm nghề...">{{ $pDoc->artisans ?? '' }}</textarea>
            </div>
            <div>
                <label class="form-label-ocop">🌾 Bí Quyết & Nguyên Liệu Chế Biến (Ingredients)</label>
                <textarea name="ingredients" rows="4" class="form-input-ocop" style="resize: vertical;" placeholder="Mỗi dòng một mục nguyên liệu. Ví dụ:&#10;Đông trùng hạ thảo nuôi cấy sinh học 100%&#10;Dược liệu sạch không hóa chất...">{{ $pDoc->ingredients ?? '' }}</textarea>
            </div>
            <div>
                <label class="form-label-ocop">📜 Hành Trình Di Sản / Dòng Lịch Sử (Timeline)</label>
                <textarea name="timeline" rows="4" class="form-input-ocop" style="resize: vertical;" placeholder="Định dạng: Mốc năm | Sự kiện. Ví dụ:&#10;Năm 2018 | Thành lập trang trại nghiên cứu dược liệu&#10;Năm 2022 | Đạt chứng nhận OCOP 4 sao Cấp Quốc Gia...">{{ $pDoc->timeline ?? '' }}</textarea>
            </div>
        </div>

        <div style="margin-bottom: 20px;">
            <label class="form-label-ocop">💡 Sự Thật Thú Vị / Bạn Có Biết? (Fun Fact)</label>
            <textarea name="fun_fact" rows="2" class="form-input-ocop" style="resize: vertical;" placeholder="Ví dụ: Quy trình sấy thăng hoa ở nhiệt độ -40 độ C giúp giữ nguyên 99% hàm lượng dưỡng chất quý...">{{ $pDoc->fun_fact ?? '' }}</textarea>
        </div>

        <div style="text-align: right;">
            <button type="submit" class="btn-admin" style="background: linear-gradient(135deg, #059669, #10b981); color: #ffffff; border: none; padding: 14px 28px; border-radius: 12px; font-weight: 900; font-size: 0.95rem; cursor: pointer; box-shadow: 0 8px 20px rgba(5, 150, 105, 0.3);">
                💾 Lưu Cập Nhật Hồ Sơ Di Sản & Giọng Nói Thuyết Minh
            </button>
        </div>
    </form>
</div>

<!-- SECTION 2: DANH SÁCH SẢN PHẨM OCOP & GIÁ BÁN -->
<div class="ocop-tab-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
        <h2 style="font-size: 1.25rem; font-weight: 900; color: #064e3b; margin: 0; display: flex; align-items: center; gap: 8px;">
            <span>📦</span> Danh Sách Sản Phẩm OCOP & Đổi Giá Niêm Yết
        </h2>
        <button type="button" onclick="openAddOcopModal()" class="btn-admin" style="background: #059669; color: #ffffff; border: none; padding: 10px 20px; border-radius: 10px; font-weight: 800; cursor: pointer;">
            + Thêm Sản Phẩm OCOP Mới
        </button>
    </div>

    <div class="admin-table-container">
        <table class="admin-table">
            <thead>
                <tr style="background: #f0fdf4;">
                    <th style="width: 50px;">STT</th>
                    <th>Hình Ảnh</th>
                    <th>Tên Sản Phẩm OCOP</th>
                    <th>Hạng Sao</th>
                    <th>Giá Niêm Yết</th>
                    <th>Mô Tả Sản Phẩm</th>
                    <th style="text-align: center;">Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $idx => $p)
                <tr>
                    <td><strong>#{{ $idx + 1 }}</strong></td>
                    <td>
                        <div style="width: 54px; height: 54px; border-radius: 10px; overflow: hidden; background: #f8fafc; border: 1.5px solid #a7f3d0;">
                            <img src="{{ $p->image_path ?: '/images/stalls/food.png' }}" style="width: 100%; height: 100%; object-fit: cover;" alt="{{ $p->name }}">
                        </div>
                    </td>
                    <td style="font-weight: 800; color: #064e3b; font-size: 0.95rem;">
                        {{ $p->name }}
                    </td>
                    <td>
                        <span style="background: #fef3c7; color: #d97706; font-size: 0.75rem; font-weight: 800; padding: 4px 10px; border-radius: 12px; border: 1px solid #fde68a;">
                            ⭐ {{ $p->star_rating ?: '4 sao' }}
                        </span>
                    </td>
                    <td style="color: #059669; font-weight: 900; font-size: 1rem;">
                        {{ number_format($p->price, 0, ',', '.') }}đ / {{ $p->unit ?? 'gói' }}
                    </td>
                    <td style="font-size: 0.82rem; color: #64748b;">{{ Str::limit($p->description, 60) }}</td>
                    <td style="text-align: center;">
                        <button type="button" onclick="openEditOcopModal({{ json_encode($p) }})" class="btn-admin" style="padding: 6px 14px; font-size: 0.78rem; background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-weight: 800; cursor: pointer; border-radius: 8px;">
                            ✏️ Đổi Giá / Sửa
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: #64748b; padding: 36px;">
                        Chưa có sản phẩm nào. Hãy bấm <strong>+ Thêm Sản Phẩm OCOP Mới</strong> để đăng bài!
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL THÊM SẢN PHẨM OCOP -->
<div id="addOcopModal" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 99999; align-items: center; justify-content: center;">
    <div style="background: #ffffff; width: 90%; max-width: 550px; border-radius: 20px; padding: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.2);">
        <h3 style="margin: 0 0 16px 0; color: #064e3b; font-size: 1.15rem; font-weight: 900;">➕ Thêm Sản Phẩm OCOP Mới</h3>
        <form action="{{ route('seller.products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div style="margin-bottom: 12px;">
                <label class="form-label-ocop">Tên Sản Phẩm OCOP</label>
                <input type="text" name="name" required class="form-input-ocop" placeholder="Ví dụ: Đông trùng hạ thảo ngâm mật tinh nghệ">
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                <div>
                    <label class="form-label-ocop">Giá Niêm Yết (VNĐ)</label>
                    <input type="text" name="price" required class="form-input-ocop" placeholder="30000">
                </div>
                <div>
                    <label class="form-label-ocop">Đơn Vị Tính</label>
                    <input type="text" name="unit" value="gói" class="form-input-ocop" placeholder="hộp / gói">
                </div>
                <div>
                    <label class="form-label-ocop">Hạng OCOP</label>
                    <select name="star_rating" class="form-input-ocop">
                        <option value="4 sao">⭐ 4 sao</option>
                        <option value="5 sao">⭐ 5 sao</option>
                        <option value="3 sao">⭐ 3 sao</option>
                    </select>
                </div>
            </div>
            <div style="margin-bottom: 12px;">
                <label class="form-label-ocop">Ảnh Sản Phẩm (Tải lên)</label>
                <input type="file" name="image" accept="image/*" class="form-input-ocop">
            </div>
            <div style="margin-bottom: 16px;">
                <label class="form-label-ocop">Mô Tả Sản Phẩm & Công Dụng</label>
                <textarea name="description" rows="3" class="form-input-ocop" placeholder="Mô tả công dụng, quy trình đóng gói..."></textarea>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="closeAddOcopModal()" style="padding: 10px 18px; border: 1px solid #cbd5e1; background: #fff; border-radius: 10px; font-weight: 700; cursor: pointer;">Hủy bỏ</button>
                <button type="submit" style="padding: 10px 22px; background: #059669; color: #fff; border: none; border-radius: 10px; font-weight: 900; cursor: pointer;">💾 Thêm Sản Phẩm</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL SỬA SẢN PHẨM OCOP -->
<div id="editOcopModal" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 99999; align-items: center; justify-content: center;">
    <div style="background: #ffffff; width: 90%; max-width: 550px; border-radius: 20px; padding: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.2);">
        <h3 style="margin: 0 0 16px 0; color: #064e3b; font-size: 1.15rem; font-weight: 900;">✏️ Cập Nhật Thông Tin & Giá Sản Phẩm OCOP</h3>
        <form id="editOcopForm" action="" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div style="margin-bottom: 12px;">
                <label class="form-label-ocop">Tên Sản Phẩm OCOP</label>
                <input type="text" id="edit_name" name="name" required class="form-input-ocop">
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                <div>
                    <label class="form-label-ocop">Giá Niêm Yết (VNĐ)</label>
                    <input type="text" id="edit_price" name="price" required class="form-input-ocop">
                </div>
                <div>
                    <label class="form-label-ocop">Đơn Vị Tính</label>
                    <input type="text" id="edit_unit" name="unit" class="form-input-ocop">
                </div>
                <div>
                    <label class="form-label-ocop">Hạng OCOP</label>
                    <select id="edit_star_rating" name="star_rating" class="form-input-ocop">
                        <option value="4 sao">⭐ 4 sao</option>
                        <option value="5 sao">⭐ 5 sao</option>
                        <option value="3 sao">⭐ 3 sao</option>
                    </select>
                </div>
            </div>
            <div style="margin-bottom: 12px;">
                <label class="form-label-ocop">Ảnh Sản Phẩm (Thay đổi nếu muốn)</label>
                <input type="file" name="image" accept="image/*" class="form-input-ocop">
            </div>
            <div style="margin-bottom: 16px;">
                <label class="form-label-ocop">Mô Tả Sản Phẩm & Công Dụng</label>
                <textarea id="edit_description" name="description" rows="3" class="form-input-ocop"></textarea>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="closeEditOcopModal()" style="padding: 10px 18px; border: 1px solid #cbd5e1; background: #fff; border-radius: 10px; font-weight: 700; cursor: pointer;">Hủy bỏ</button>
                <button type="submit" style="padding: 10px 22px; background: #059669; color: #fff; border: none; border-radius: 10px; font-weight: 900; cursor: pointer;">💾 Lưu Thay Đổi</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddOcopModal() {
    document.getElementById('addOcopModal').style.display = 'flex';
}
function closeAddOcopModal() {
    document.getElementById('addOcopModal').style.display = 'none';
}
function openEditOcopModal(p) {
    document.getElementById('editOcopForm').action = '/seller/products/' + p.id;
    document.getElementById('edit_name').value = p.name || '';
    document.getElementById('edit_price').value = p.price || 0;
    document.getElementById('edit_unit').value = p.unit || 'gói';
    document.getElementById('edit_star_rating').value = p.star_rating || '4 sao';
    document.getElementById('edit_description').value = p.description || '';
    document.getElementById('editOcopModal').style.display = 'flex';
}
function closeEditOcopModal() {
    document.getElementById('editOcopModal').style.display = 'none';
}
</script>

@endsection
