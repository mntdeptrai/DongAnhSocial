@extends('layouts.hkd')

@section('title', 'Quản Lý Sản Phẩm & Hàng Hóa — ' . $eatery->name)
@section('title_header', 'Danh Mục Sản Phẩm & Hàng Hóa Kinh Doanh')

@section('content')
<style>
    /* HEADER CARD */
    .prod-header-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 24px 28px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.03);
    }
    .prod-stats-row {
        display: flex;
        gap: 24px;
        flex-wrap: wrap;
        align-items: center;
        margin-top: 8px;
    }
    .prod-stat-pill {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.88rem;
        color: #475569;
        font-weight: 600;
    }
    .prod-stat-pill span {
        font-size: 1.1rem;
        font-weight: 800;
        color: #0f172a;
    }

    /* SEARCH BAR */
    .prod-search-wrap {
        position: relative;
        width: 100%;
        max-width: 400px;
    }
    .prod-search-wrap i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 0.9rem;
    }
    .prod-search-input {
        width: 100%;
        padding: 11px 16px 11px 40px;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        font-size: 0.9rem;
        color: #0f172a;
        background: #fff;
        outline: none;
        transition: border-color 0.2s;
    }
    .prod-search-input:focus { border-color: #10b981; }

    /* PRODUCT GRID */
    .prod-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 18px;
    }
    .prod-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: box-shadow 0.2s, transform 0.2s;
    }
    .prod-card:hover {
        box-shadow: 0 8px 24px rgba(16,185,129,0.10);
        transform: translateY(-3px);
        border-color: #10b981;
    }
    .prod-img-box {
        height: 170px;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        position: relative;
    }
    .prod-img-box img { width: 100%; height: 100%; object-fit: cover; }
    .prod-unit-badge {
        position: absolute;
        bottom: 10px;
        left: 10px;
        background: rgba(15, 23, 42, 0.72);
        color: #fff;
        font-size: 0.72rem;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 20px;
        backdrop-filter: blur(4px);
    }
    .prod-body {
        padding: 14px 16px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    .prod-name {
        font-size: 0.98rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 4px;
        line-height: 1.35;
    }
    .prod-price {
        font-size: 1.1rem;
        font-weight: 900;
        color: #059669;
        margin-bottom: 6px;
    }
    .prod-desc {
        font-size: 0.82rem;
        color: #64748b;
        line-height: 1.45;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .prod-actions {
        display: flex;
        gap: 8px;
        padding: 10px 14px;
        border-top: 1px solid #f1f5f9;
        background: #fafafa;
    }
    .prod-btn {
        flex: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 8px 12px;
        border-radius: 10px;
        font-size: 0.82rem;
        font-weight: 700;
        cursor: pointer;
        border: none;
        transition: all 0.15s ease;
    }
    .prod-btn-edit { background: #eff6ff; color: #2563eb; }
    .prod-btn-edit:hover { background: #dbeafe; }
    .prod-btn-del { background: #fef2f2; color: #dc2626; }
    .prod-btn-del:hover { background: #fee2e2; }

    /* MODAL */
    .prod-modal-bg {
        position: fixed; inset: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(5px);
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }
    .prod-modal-bg.open { display: flex; }
    .prod-modal {
        background: #fff;
        border-radius: 20px;
        width: 100%;
        max-width: 580px;
        max-height: 92vh;
        overflow-y: auto;
        box-shadow: 0 24px 48px rgba(0,0,0,0.2);
    }
    .prod-modal-head {
        padding: 20px 24px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        position: sticky;
        top: 0;
        background: #fff;
        z-index: 2;
        border-radius: 20px 20px 0 0;
    }
    .prod-modal-head h3 { font-size: 1.15rem; font-weight: 800; color: #0f172a; }
    .prod-modal-head p { font-size: 0.8rem; color: #64748b; margin-top: 2px; }
    .prod-modal-close {
        background: #f1f5f9; border: none;
        width: 32px; height: 32px; border-radius: 50%;
        font-size: 1.1rem; cursor: pointer; color: #64748b;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        transition: background 0.15s;
    }
    .prod-modal-close:hover { background: #e2e8f0; }
    .prod-modal-body { padding: 20px 24px; }
    .prod-modal-foot {
        padding: 14px 24px;
        border-top: 1px solid #f1f5f9;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        background: #f8fafc;
        border-radius: 0 0 20px 20px;
        position: sticky;
        bottom: 0;
    }

    /* CATEGORY QUICK PICKS */
    .cat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
        gap: 8px;
        margin-bottom: 18px;
    }
    .cat-chip {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 4px;
        padding: 10px 6px;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        background: #f8fafc;
        cursor: pointer;
        font-size: 0.8rem;
        font-weight: 700;
        color: #475569;
        transition: all 0.15s ease;
        text-align: center;
    }
    .cat-chip:hover, .cat-chip.selected {
        border-color: #10b981;
        background: #ecfdf5;
        color: #059669;
    }
    .cat-chip .cat-emoji { font-size: 1.5rem; }

    /* FORM FIELDS */
    .field-group { margin-bottom: 14px; }
    .field-label {
        font-size: 0.84rem;
        font-weight: 700;
        color: #334155;
        margin-bottom: 6px;
        display: block;
    }
    .field-hint { font-size: 0.75rem; color: #94a3b8; font-weight: 500; margin-left: 4px; }
    .field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

    /* IMAGE UPLOAD */
    .img-drop {
        border: 2px dashed #cbd5e1;
        border-radius: 14px;
        background: #f8fafc;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 120px;
        transition: all 0.2s;
        overflow: hidden;
        position: relative;
    }
    .img-drop:hover { border-color: #10b981; background: #f0fdf4; }
    .img-drop img { max-width: 100%; max-height: 150px; object-fit: contain; display: none; }
    .img-drop-label { font-size: 0.84rem; font-weight: 600; color: #64748b; margin-top: 8px; }
    .img-drop-sub { font-size: 0.75rem; color: #94a3b8; }

    /* PRICE PREVIEW */
    .price-preview { font-size: 0.8rem; font-weight: 700; color: #059669; margin-top: 4px; }
</style>

<!-- PAGE HEADER -->
<div class="prod-header-card">
    <div>
        <h2 style="font-size: 1.18rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
            <i class="fa-solid fa-tags" style="color: #059669;"></i>
            Danh Mục Hàng Hóa & Dịch Vụ Kinh Doanh
        </h2>
        <div class="prod-stats-row">
            <div class="prod-stat-pill">
                <i class="fa-solid fa-box-archive" style="color: #059669;"></i>
                <span>{{ $products->total() }}</span> mặt hàng đang niêm yết
            </div>
            @if($products->total() > 0)
            <div class="prod-stat-pill">
                <i class="fa-solid fa-image" style="color: #0284c7;"></i>
                <span>{{ $products->getCollection()->filter(fn($p) => !empty($p->image))->count() }}</span>
                / {{ $products->count() }} có ảnh
            </div>
            @endif
        </div>
    </div>

    <button onclick="openAddModal()" class="hkd-btn-action hkd-btn-emerald" style="padding: 12px 22px; font-size: 0.92rem; white-space: nowrap;">
        <i class="fa-solid fa-plus"></i> Thêm Hàng / Dịch Vụ Mới
    </button>
</div>

<!-- SEARCH + INFO BAR -->
<div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
    <div class="prod-search-wrap">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" class="prod-search-input" id="searchInput" onkeyup="filterProducts()" placeholder="Tìm kiếm sản phẩm, hàng hóa...">
    </div>
    <div style="font-size: 0.84rem; color: #94a3b8; font-weight: 600;">
        Hiển thị {{ $products->count() }} / {{ $products->total() }} mặt hàng
    </div>
</div>

<!-- PRODUCT GRID or EMPTY STATE -->
@if($products->isEmpty())
    <div style="border: 2px dashed #e2e8f0; border-radius: 18px; text-align: center; padding: 60px 24px; background: #fff;">
        <div style="font-size: 3rem; margin-bottom: 12px;">🛒</div>
        <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a;">Gian hàng chưa có sản phẩm nào</h3>
        <p style="font-size: 0.88rem; color: #64748b; margin: 8px auto 20px; max-width: 420px;">
            Hãy thêm các mặt hàng, sản phẩm hoặc dịch vụ mà bạn đang kinh doanh để hiển thị lên trang địa điểm công khai.
        </p>
        <button onclick="openAddModal()" class="hkd-btn-action hkd-btn-emerald">
            <i class="fa-solid fa-plus"></i> Thêm Mặt Hàng Đầu Tiên
        </button>
    </div>
@else
    <div class="prod-grid" id="productGrid">
        @foreach($products as $p)
        <div class="prod-card product-item-card"
             data-name="{{ strtolower($p->name ?? '') }}"
             data-desc="{{ strtolower($p->description ?? '') }}">
            <div class="prod-img-box">
                @if($p->image)
                    <img src="{{ $p->image }}" alt="{{ $p->name }}">
                @else
                    <div style="text-align: center; color: #cbd5e1;">
                        <i class="fa-solid fa-image" style="font-size: 2.5rem;"></i>
                        <div style="font-size: 0.72rem; margin-top: 4px; font-weight: 600;">Chưa có ảnh</div>
                    </div>
                @endif
                <div class="prod-unit-badge">{{ $p->unit ?? 'Cái' }}</div>
            </div>

            <div class="prod-body">
                <div class="prod-name">{{ $p->name }}</div>
                <div class="prod-price">
                    {{ number_format($p->price) }}đ
                    <span style="font-size: 0.78rem; font-weight: 600; color: #94a3b8;">/ {{ $p->unit ?? 'Cái' }}</span>
                </div>
                @if($p->description)
                    <div class="prod-desc">{{ $p->description }}</div>
                @else
                    <div class="prod-desc" style="font-style: italic; color: #e2e8f0;">Chưa có mô tả</div>
                @endif
            </div>

            <div class="prod-actions">
                <button class="prod-btn prod-btn-edit" onclick="openEditModal({{ json_encode($p) }})">
                    <i class="fa-solid fa-pen"></i> Sửa
                </button>
                <form action="{{ route('hkd.products.destroy', $p->id) }}" method="POST"
                      onsubmit="return confirm('Xóa mặt hàng này khỏi danh mục?')" style="flex: 1;">
                    @csrf @method('DELETE')
                    <button type="submit" class="prod-btn prod-btn-del" style="width: 100%;">
                        <i class="fa-solid fa-trash"></i> Xóa
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    <div style="margin-top: 24px;">{{ $products->links() }}</div>
@endif


{{-- ========================= MODAL THÊM ========================= --}}
<div id="addModal" class="prod-modal-bg">
    <div class="prod-modal">
        <div class="prod-modal-head">
            <div>
                <h3>➕ Thêm Hàng Hóa / Dịch Vụ Mới</h3>
                <p>Điền thông tin mặt hàng bạn muốn niêm yết công khai</p>
            </div>
            <button class="prod-modal-close" onclick="closeAddModal()">&times;</button>
        </div>

        <form action="{{ route('hkd.products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="prod-modal-body">

                {{-- STEP 1: Chọn nhóm hàng --}}
                <div class="field-group">
                    <label class="field-label">
                        <i class="fa-solid fa-grip" style="color: #10b981;"></i>
                        Nhóm hàng (chọn để điền nhanh)
                    </label>
                    <div class="cat-grid">
                        <div class="cat-chip" onclick="pickCat('Đồ ăn', 'Suất', 'Món ăn thơm ngon, phục vụ tại chỗ và mang về.')">
                            <div class="cat-emoji">🍲</div><div>Đồ ăn</div>
                        </div>
                        <div class="cat-chip" onclick="pickCat('Đồ uống', 'Ly', 'Thức uống giải khát, trà, cà phê và nước ép.')">
                            <div class="cat-emoji">🧋</div><div>Đồ uống</div>
                        </div>
                        <div class="cat-chip" onclick="pickCat('Quần áo / Thời trang', 'Cái', 'Hàng may mặc, thời trang, phụ kiện thời trang.')">
                            <div class="cat-emoji">👔</div><div>Quần áo</div>
                        </div>
                        <div class="cat-chip" onclick="pickCat('Điện tử / Điện máy', 'Cái', 'Thiết bị điện tử, điện máy gia dụng.')">
                            <div class="cat-emoji">📱</div><div>Điện tử</div>
                        </div>
                        <div class="cat-chip" onclick="pickCat('Nông sản / Thực phẩm', 'Kg', 'Nông sản tươi, thực phẩm đóng gói, hàng khô.')">
                            <div class="cat-emoji">🥬</div><div>Nông sản</div>
                        </div>
                        <div class="cat-chip" onclick="pickCat('Hàng gia dụng', 'Cái', 'Đồ dùng trong nhà, trang trí nội thất, dụng cụ bếp.')">
                            <div class="cat-emoji">🏠</div><div>Gia dụng</div>
                        </div>
                        <div class="cat-chip" onclick="pickCat('Vật liệu xây dựng', 'Bộ', 'Vật liệu xây dựng, sơn nước, vật tư công trình.')">
                            <div class="cat-emoji">🧱</div><div>Xây dựng</div>
                        </div>
                        <div class="cat-chip" onclick="pickCat('Dịch vụ', 'Lần', 'Gói dịch vụ, sửa chữa, bảo dưỡng, tư vấn.')">
                            <div class="cat-emoji">🔧</div><div>Dịch vụ</div>
                        </div>
                        <div class="cat-chip" onclick="pickCat('Mỹ phẩm / Chăm sóc', 'Hộp', 'Mỹ phẩm, chăm sóc cá nhân, sức khỏe.')">
                            <div class="cat-emoji">💄</div><div>Mỹ phẩm</div>
                        </div>
                        <div class="cat-chip" onclick="pickCat('Văn phòng phẩm', 'Cái', 'Văn phòng phẩm, đồ dùng học tập, in ấn.')">
                            <div class="cat-emoji">📎</div><div>Văn phòng</div>
                        </div>
                    </div>
                </div>

                {{-- Tên sản phẩm --}}
                <div class="field-group">
                    <label class="field-label">
                        Tên sản phẩm / hàng hóa <span style="color: #dc2626;">*</span>
                    </label>
                    <input id="add-name" type="text" name="name" class="hkd-form-input" required
                           placeholder="Ví dụ: Áo sơ mi nam dài tay, Gạo tẻ Đông Anh, Sửa máy tính...">
                </div>

                {{-- Giá + Đơn vị --}}
                <div class="field-row">
                    <div class="field-group">
                        <label class="field-label">Giá bán <span style="color: #dc2626;">*</span></label>
                        <input id="add-price" type="number" name="price" class="hkd-form-input" required
                               placeholder="0" oninput="showPrice('addPriceHint', this.value)">
                        <div id="addPriceHint" class="price-preview"></div>
                    </div>
                    <div class="field-group">
                        <label class="field-label">Đơn vị tính</label>
                        <input id="add-unit" type="text" name="unit" list="unitOptions"
                               class="hkd-form-input" placeholder="Ví dụ: Cái, Kg, Lần..."
                               value="Cái" autocomplete="off">
                    </div>
                </div>

                {{-- Mô tả --}}
                <div class="field-group">
                    <label class="field-label">
                        Mô tả ngắn <span class="field-hint">(không bắt buộc)</span>
                    </label>
                    <textarea id="add-desc" name="description" rows="3" class="hkd-form-textarea"
                              placeholder="Xuất xứ, chất liệu, quy cách, hạn sử dụng hoặc thông tin thêm..."></textarea>
                </div>

                {{-- Ảnh sản phẩm --}}
                <div class="field-group">
                    <label class="field-label">Hình ảnh sản phẩm <span class="field-hint">(không bắt buộc)</span></label>
                    <div class="img-drop" onclick="document.getElementById('addImgInput').click()">
                        <input type="file" id="addImgInput" name="image" accept="image/*"
                               style="display:none;" onchange="previewImg(this, 'addImgPrev', 'addImgPlaceholder')">
                        <img id="addImgPrev" style="max-width:100%; max-height:150px; object-fit:contain; display:none; border-radius:10px;">
                        <div id="addImgPlaceholder" style="text-align: center; padding: 16px;">
                            <i class="fa-solid fa-cloud-arrow-up" style="font-size: 1.8rem; color: #10b981;"></i>
                            <div class="img-drop-label">Bấm để chọn ảnh từ máy tính</div>
                            <div class="img-drop-sub">PNG, JPG, WEBP — Tối đa 5MB</div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="prod-modal-foot">
                <button type="button" onclick="closeAddModal()" class="hkd-btn-action hkd-btn-slate">Hủy</button>
                <button type="submit" class="hkd-btn-action hkd-btn-emerald">
                    <i class="fa-solid fa-check"></i> Lưu & Đăng Hàng
                </button>
            </div>
        </form>
    </div>
</div>


{{-- ========================= MODAL SỬA ========================= --}}
<div id="editModal" class="prod-modal-bg">
    <div class="prod-modal">
        <div class="prod-modal-head">
            <div>
                <h3>✏️ Cập Nhật Thông Tin Hàng</h3>
                <p>Chỉnh sửa thông tin mặt hàng đang niêm yết</p>
            </div>
            <button class="prod-modal-close" onclick="closeEditModal()">&times;</button>
        </div>

        <form id="editForm" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="prod-modal-body">

                <div class="field-group">
                    <label class="field-label">Tên sản phẩm / hàng hóa <span style="color: #dc2626;">*</span></label>
                    <input id="edit-name" type="text" name="name" class="hkd-form-input" required>
                </div>

                <div class="field-row">
                    <div class="field-group">
                        <label class="field-label">Giá bán <span style="color: #dc2626;">*</span></label>
                        <input id="edit-price" type="number" name="price" class="hkd-form-input" required
                               oninput="showPrice('editPriceHint', this.value)">
                        <div id="editPriceHint" class="price-preview"></div>
                    </div>
                    <div class="field-group">
                        <label class="field-label">Đơn vị tính</label>
                        <input id="edit-unit" type="text" name="unit" list="unitOptions"
                               class="hkd-form-input" placeholder="Ví dụ: Cái, Kg, Lần..."
                               autocomplete="off">
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label">Mô tả ngắn</label>
                    <textarea id="edit-desc" name="description" rows="3" class="hkd-form-textarea"></textarea>
                </div>

                <div class="field-group">
                    <label class="field-label">Thay ảnh mới <span class="field-hint">(bỏ trống để giữ ảnh cũ)</span></label>
                    <div class="img-drop" onclick="document.getElementById('editImgInput').click()">
                        <input type="file" id="editImgInput" name="image" accept="image/*"
                               style="display:none;" onchange="previewImg(this, 'editImgPrev', 'editImgPlaceholder')">
                        <img id="editImgPrev" style="max-width:100%; max-height:150px; object-fit:contain; display:none; border-radius:10px;">
                        <div id="editImgPlaceholder" style="text-align: center; padding: 16px;">
                            <i class="fa-solid fa-image" style="font-size: 1.8rem; color: #0284c7;"></i>
                            <div class="img-drop-label">Bấm để chọn ảnh thay thế</div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="prod-modal-foot">
                <button type="button" onclick="closeEditModal()" class="hkd-btn-action hkd-btn-slate">Hủy</button>
                <button type="submit" class="hkd-btn-action hkd-btn-sky">
                    <i class="fa-solid fa-floppy-disk"></i> Lưu Thay Đổi
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Shared datalist for unit suggestions --}}
<datalist id="unitOptions">
    <option value="Cái">
    <option value="Chiếc">
    <option value="Suất">
    <option value="Phần">
    <option value="Ly">
    <option value="Cốc">
    <option value="Kg">
    <option value="Gam">
    <option value="Gói">
    <option value="Hộp">
    <option value="Chai">
    <option value="Lon">
    <option value="Bộ">
    <option value="Set">
    <option value="Lần">
    <option value="Buổi">
    <option value="Ngày">
    <option value="Tháng">
    <option value="Mét">
    <option value="Mét vuông">
    <option value="Mét khối">
    <option value="Tấn">
    <option value="Tạ">
    <option value="Yến">
    <option value="Lít">
    <option value="Cuộn">
    <option value="Tờ">
    <option value="Quyển">
    <option value="Viên">
    <option value="Hạt">
    <option value="Cây">
    <option value="Con">
    <option value="Bào">
    <option value="Phần trăm">
</datalist>

<script>
    // Modal open/close
    const addModal = document.getElementById('addModal');
    const editModal = document.getElementById('editModal');
    function openAddModal() { addModal.classList.add('open'); }
    function closeAddModal() { addModal.classList.remove('open'); }
    function openEditModal(prod) {
        editModal.classList.add('open');
        document.getElementById('editForm').action = '/hkd/products/' + prod.id;
        document.getElementById('edit-name').value = prod.name;
        document.getElementById('edit-price').value = prod.price;
        showPrice('editPriceHint', prod.price);
        document.getElementById('edit-unit').value = prod.unit || 'Cái';
        document.getElementById('edit-desc').value = prod.description || '';
        if (prod.image) {
            const img = document.getElementById('editImgPrev');
            img.src = prod.image;
            img.style.display = 'block';
            document.getElementById('editImgPlaceholder').style.display = 'none';
        }
    }
    function closeEditModal() { editModal.classList.remove('open'); }

    // Close on backdrop click
    addModal.addEventListener('click', e => { if (e.target === addModal) closeAddModal(); });
    editModal.addEventListener('click', e => { if (e.target === editModal) closeEditModal(); });

    // ESC to close
    document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeAddModal(); closeEditModal(); } });

    // Quick category preset
    function pickCat(name, unit, desc) {
        document.getElementById('add-name').value = name;
        document.getElementById('add-desc').value = desc;
        document.getElementById('add-unit').value = unit;
        // Highlight selected chip
        document.querySelectorAll('.cat-chip').forEach(c => c.classList.remove('selected'));
        event.currentTarget.classList.add('selected');
        document.getElementById('add-name').focus();
    }

    // Price formatted preview
    function showPrice(elId, val) {
        const n = parseFloat(val) || 0;
        const el = document.getElementById(elId);
        if (!el) return;
        el.textContent = n > 0 ? '= ' + n.toLocaleString('vi-VN') + ' đ' : '';
    }

    // Image preview
    function previewImg(input, previewId, placeholderId) {
        if (!input.files || !input.files[0]) return;
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.getElementById(previewId);
            img.src = e.target.result;
            img.style.display = 'block';
            document.getElementById(placeholderId).style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }

    // Search filter
    function filterProducts() {
        const q = document.getElementById('searchInput').value.toLowerCase().trim();
        document.querySelectorAll('.product-item-card').forEach(card => {
            const match = card.dataset.name.includes(q) || card.dataset.desc.includes(q);
            card.style.display = match ? '' : 'none';
        });
    }
</script>
@endsection
