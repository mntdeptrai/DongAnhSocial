@extends('layouts.seller')

@section('title', 'Quản Lý Sản Phẩm & Giá Cả — ' . $stallName)

@section('content')

<!-- Workspace Title -->
<div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 24px;">
    <div>
        <h1 style="font-family: var(--slr-font); font-size: 1.6rem; font-weight: 800; margin: 0; color: var(--slr-text-main);">
            📦 Quản Lý Thực Đơn & Sản Phẩm bày bán
        </h1>
        <p style="font-size: 0.9rem; color: var(--slr-text-muted); margin-top: 4px;">
            Thêm món ăn/mặt hàng mới, cập nhật giá niêm yết và tải lên hình ảnh sản phẩm cho gian hàng <strong>{{ $stallName }}</strong>.
        </p>
    </div>
</div>

<!-- Alert notifications -->
@if(session('success'))
    <div class="admin-alert admin-alert-success" style="margin-bottom: 20px;">
        <span>✅</span>
        <div><strong>Thành công!</strong> {{ session('success') }}</div>
    </div>
@endif

<!-- Add New Product Form -->
<div class="admin-card" style="margin-bottom: 30px; border-color: rgba(217,119,6,0.3);">
    <div class="admin-card-header" style="background: rgba(217,119,6,0.04);">
        <h2 class="admin-card-title" style="color: var(--slr-primary);">
            <span>➕</span> Đăng Bày Bán Sản Phẩm / Món Ăn Mới
        </h2>
    </div>

    <form action="{{ route('seller.products.store') }}" method="POST" enctype="multipart/form-data" style="padding: 20px;">
        @csrf
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
            <div>
                <label class="admin-form-label">Tên sản phẩm / Món ăn *</label>
                <input type="text" name="name" class="admin-form-input" required placeholder="Ví dụ: Bún riêu cua, Cà chua hữu cơ...">
            </div>

            <div>
                <label class="admin-form-label">Giá niêm yết (VNĐ) *</label>
                <div style="position: relative;">
                    <input type="text" id="price-display-new" class="admin-form-input"
                        required autocomplete="off"
                        placeholder="VD: 30.000 VND"
                        oninput="formatPriceInput(this, 'price-raw-new')"
                        onblur="formatPriceInput(this, 'price-raw-new')"
                        style="padding-right: 52px;">
                    <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 0.75rem; font-weight: 700; color: var(--slr-primary); pointer-events: none;">VND</span>
                </div>
                <input type="hidden" name="price" id="price-raw-new">
            </div>

            <div>
                <label class="admin-form-label">Đơn vị tính</label>
                <select id="unit-select-new" class="admin-form-input" onchange="handleUnitChange(this, 'unit-custom-new', 'unit-hidden-new')">
                    <option value="bát">Bát / Suất</option>
                    <option value="kg">Kg</option>
                    <option value="hộp">Hộp</option>
                    <option value="gói">Gói</option>
                    <option value="quả">Quả</option>
                    <option value="bó">Bó</option>
                    <option value="con">Con</option>
                    <option value="cái">Cái</option>
                    <option value="__custom__">✏️ Khác (nhập tay)...</option>
                </select>
                <input type="text" id="unit-custom-new" placeholder="Nhập đơn vị, VD: lít, thùng, túi..."
                       class="admin-form-input" style="margin-top: 8px; display: none;"
                       oninput="document.getElementById('unit-hidden-new').value = this.value">
                <input type="hidden" name="unit" id="unit-hidden-new" value="bát">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 16px;">
            <div>
                <label class="admin-form-label">Nguồn gốc hàng hóa / Nông sản</label>
                <input type="text" name="origin" class="admin-form-input" placeholder="Ví dụ: Tự sản xuất, Chợ đầu mối Long Biên...">
            </div>

            <div>
                <label class="admin-form-label">Ảnh đại diện sản phẩm (Upload)</label>
                <input type="file" name="image" class="admin-form-input" accept="image/*">
            </div>
        </div>

        <div style="margin-top: 16px;">
            <label class="admin-form-label">Mô tả sản phẩm</label>
            <textarea name="description" class="admin-form-input" rows="2" placeholder="Nhập mô tả ngắn về thành phần, nguyên liệu tươi ngon..."></textarea>
        </div>

        <div style="margin-top: 20px; text-align: right;">
            <button type="submit" class="btn-admin btn-admin-primary" style="padding: 10px 24px; font-weight: 700;">
                ➕ Thêm Vào Gian Hàng
            </button>
        </div>
    </form>
</div>

<!-- Products Table List -->
<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="admin-card-title"><span>📋</span> Các Mặt Hàng Đã Đăng Niêm Yết ({{ $products->count() }})</h2>
    </div>

    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 48px;">STT</th>
                    <th style="width: 80px;">Hình Ảnh</th>
                    <th>Tên Sản Phẩm</th>
                    <th style="width: 130px;">Giá Hiện Tại</th>
                    <th>Mô Tả & Nguồn Gốc</th>
                    <th style="width: 110px; text-align: center;">Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $idx => $p)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>
                        <img src="{{ $p->image_path ?: '/images/stalls/food.png' }}"
                             style="width: 64px; height: 64px; border-radius: 10px; object-fit: cover; border: 1.5px solid #fde68a;">
                    </td>
                    <td style="font-weight: 700; font-size: 0.95rem; color: var(--slr-text-main);">
                        {{ $p->name }}
                        @if(isset($p->unit) && $p->unit)
                            <span style="font-size: 0.72rem; background: #fef3c7; color: #92400e; padding: 2px 7px; border-radius: 8px; font-weight: 600; margin-left: 4px;">{{ $p->unit }}</span>
                        @endif
                    </td>
                    <td>
                        <span style="color: var(--slr-primary); font-weight: 800; font-size: 1.05rem;">
                            {{ number_format($p->price, 0, ',', '.') }}đ
                        </span>
                    </td>
                    <td style="font-size: 0.82rem; color: var(--slr-text-muted); max-width: 240px; line-height: 1.5;">
                        {{ Str::limit($p->description, 80) }}
                    </td>
                    <td style="text-align: center;">
                        <div style="display: flex; gap: 6px; justify-content: center; align-items: center;">
                            <!-- Nút Sửa mở modal -->
                            <button type="button"
                                class="btn-admin"
                                style="padding: 6px 12px; font-size: 0.78rem; background: #fef3c7; color: #92400e; border: 1.5px solid rgba(217,119,6,0.3); border-radius: 8px; font-weight: 700; cursor: pointer; transition: all 0.2s;"
                                onmouseover="this.style.background='#fde68a'"
                                onmouseout="this.style.background='#fef3c7'"
                                onclick="openEditModal(
                                    {{ $p->id }},
                                    {{ json_encode($p->name) }},
                                    {{ intval($p->price) }},
                                    {{ json_encode($p->unit ?? 'kg') }},
                                    {{ json_encode($p->description ?? '') }},
                                    {{ json_encode($p->image_path ?? '') }}
                                )">
                                ✏️ Sửa
                            </button>

                            <!-- Nút Xóa -->
                            <form action="{{ route('seller.products.destroy', $p->id) }}" method="POST"
                                  onsubmit="return confirm('Bạn có chắc muốn xóa mặt hàng này khỏi gian hàng?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-admin btn-admin-danger"
                                        style="padding: 6px 10px; font-size: 0.78rem;">
                                    🗑️ Xóa
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- ============================================================
     MODAL SỬA SẢN PHẨM
     ============================================================ -->
<div id="edit-product-modal" style="
    display: none;
    position: fixed; inset: 0; z-index: 10000;
    background: rgba(28,16,7,0.6);
    backdrop-filter: blur(8px);
    align-items: center; justify-content: center;
" onclick="closeEditModal(event)">

    <div style="
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 25px 60px rgba(0,0,0,0.25);
        width: 100%; max-width: 580px;
        margin: 24px;
        overflow: hidden;
        transform: scale(0.92);
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    " id="edit-modal-box" onclick="event.stopPropagation()">

        <!-- Modal Header -->
        <div style="background: linear-gradient(135deg, #1c1007 0%, #2d1a0a 100%); color: #fff; padding: 20px 28px; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="font-size: 1.1rem; font-weight: 800;">✏️ Sửa Thông Tin Sản Phẩm</div>
                <div style="font-size: 0.8rem; color: rgba(255,255,255,0.55); margin-top: 2px;" id="edit-modal-subtitle">Cập nhật tên, giá, mô tả và hình ảnh</div>
            </div>
            <button onclick="closeEditModal()" style="background: rgba(255,255,255,0.12); border: none; color: #fff; width: 36px; height: 36px; border-radius: 50%; font-size: 1.2rem; cursor: pointer; transition: background 0.2s;"
                    onmouseover="this.style.background='rgba(255,255,255,0.22)'"
                    onmouseout="this.style.background='rgba(255,255,255,0.12)'">×</button>
        </div>

        <!-- Current Image Preview -->
        <div style="padding: 16px 28px 0; display: flex; align-items: center; gap: 14px;">
            <img id="edit-current-img" src="" alt="Ảnh hiện tại"
                 style="width: 72px; height: 72px; border-radius: 12px; object-fit: cover; border: 2px solid #fde68a; flex-shrink: 0;">
            <div>
                <div style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #92400e;">Ảnh hiện tại</div>
                <div style="font-size: 0.8rem; color: #78716c; margin-top: 2px;">Tải ảnh mới bên dưới để thay thế</div>
            </div>
        </div>

        <!-- Modal Form -->
        <form id="edit-product-form" method="POST" enctype="multipart/form-data" style="padding: 20px 28px 28px;">
            @csrf
            @method('PUT')
            <input type="hidden" name="price" id="edit-price-raw">

            <!-- Tên sản phẩm -->
            <div class="admin-form-group">
                <label class="admin-form-label">Tên sản phẩm / Món ăn *</label>
                <input type="text" name="name" id="edit-name" class="admin-form-input" required placeholder="Tên sản phẩm...">
            </div>

            <!-- Giá + Đơn vị -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="admin-form-group" style="margin-bottom: 0;">
                    <label class="admin-form-label">Giá niêm yết (VNĐ) *</label>
                    <div style="position: relative;">
                        <input type="text" id="edit-price-display" class="admin-form-input"
                               autocomplete="off"
                               style="padding-right: 56px;"
                               placeholder="VD: 30.000 VND"
                               oninput="formatPriceInput(this, 'edit-price-raw')"
                               onblur="formatPriceInput(this, 'edit-price-raw')">
                        <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 0.72rem; font-weight: 700; color: var(--slr-primary); pointer-events: none;">VND</span>
                    </div>
                </div>
                <div class="admin-form-group" style="margin-bottom: 0;">
                    <label class="admin-form-label">Đơn vị tính</label>
                    <select id="edit-unit" class="admin-form-input" onchange="handleUnitChange(this, 'edit-unit-custom', 'edit-unit-hidden')">
                        <option value="bát">Bát / Suất</option>
                        <option value="kg">Kg</option>
                        <option value="hộp">Hộp</option>
                        <option value="gói">Gói</option>
                        <option value="quả">Quả</option>
                        <option value="bó">Bó</option>
                        <option value="con">Con</option>
                        <option value="cái">Cái</option>
                        <option value="__custom__">✏️ Khác (nhập tay)...</option>
                    </select>
                    <input type="text" id="edit-unit-custom" placeholder="Nhập đơn vị, VD: lít, thùng, túi..."
                           class="admin-form-input" style="margin-top: 8px; display: none;"
                           oninput="document.getElementById('edit-unit-hidden').value = this.value">
                    <input type="hidden" name="unit" id="edit-unit-hidden" value="kg">
                </div>
            </div>

            <!-- Mô tả -->
            <div class="admin-form-group" style="margin-top: 16px;">
                <label class="admin-form-label">Mô tả sản phẩm</label>
                <textarea name="description" id="edit-description" class="admin-form-input" rows="3"
                          placeholder="Mô tả ngắn: thành phần, nguồn gốc, hỗ trợ thanh toán..."></textarea>
            </div>

            <!-- Ảnh mới -->
            <div class="admin-form-group" style="margin-bottom: 24px;">
                <label class="admin-form-label">Thay Ảnh Sản Phẩm (tuỳ chọn)</label>
                <div style="border: 1.5px dashed #fde68a; border-radius: 10px; padding: 14px 16px; background: #fffbeb; display: flex; align-items: center; gap: 12px;">
                    <span style="font-size: 1.5rem;">🖼️</span>
                    <div style="flex: 1;">
                        <input type="file" name="image" id="edit-image" accept="image/*" style="font-size: 0.82rem; color: #78716c;">
                        <div style="font-size: 0.72rem; color: #a8a29e; margin-top: 4px;">JPG, PNG, WEBP — tối đa 10MB. Để trống nếu không muốn thay ảnh.</div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div style="display: flex; gap: 12px; justify-content: flex-end; border-top: 1px solid #fde68a; padding-top: 20px;">
                <button type="button" onclick="closeEditModal()" class="btn-admin btn-admin-secondary">
                    Hủy bỏ
                </button>
                <button type="submit" class="btn-admin btn-admin-primary" style="min-width: 140px;">
                    💾 Lưu thay đổi
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
/* ==========================================================
   Unit Select — Khác (nhập tay)
   ========================================================== */
function handleUnitChange(selectEl, customInputId, hiddenId) {
    const customInput = document.getElementById(customInputId);
    const hiddenInput = document.getElementById(hiddenId);
    if (selectEl.value === '__custom__') {
        customInput.style.display = 'block';
        customInput.focus();
        customInput.required = true;
        if (hiddenInput) hiddenInput.value = customInput.value || '';
    } else {
        customInput.style.display = 'none';
        customInput.required = false;
        if (hiddenInput) hiddenInput.value = selectEl.value;
    }
}

/* ==========================================================
   Price Auto-Format (VNĐ)
   ========================================================== */
function formatPriceInput(displayEl, rawId) {
    const raw = displayEl.value.replace(/[^\d]/g, '');
    const num = parseInt(raw, 10);
    const rawInput = document.getElementById(rawId);
    if (rawInput) rawInput.value = raw;
    if (raw === '' || isNaN(num)) { displayEl.value = ''; return; }
    displayEl.value = num.toLocaleString('vi-VN') + ' VND';
    const pos = displayEl.value.length - 4;
    try { displayEl.setSelectionRange(pos, pos); } catch(e) {}
}

/* Focus: xóa suffix " VND" để gõ tiếp */
document.addEventListener('DOMContentLoaded', function() {
    [document.getElementById('price-display-new'), document.getElementById('edit-price-display')].forEach(function(el) {
        if (!el) return;
        el.addEventListener('focus', function() {
            this.value = this.value.replace(/[^\d]/g, '');
        });
    });

    /* Validate form thêm mới */
    const displayNew = document.getElementById('price-display-new');
    const rawNew = document.getElementById('price-raw-new');
    if (displayNew) {
        displayNew.closest('form').addEventListener('submit', function(e) {
            if (!rawNew.value || rawNew.value === '0') {
                e.preventDefault();
                displayNew.focus();
                displayNew.style.borderColor = '#ef4444';
                displayNew.placeholder = 'Vui lòng nhập giá!';
            }
        });
    }
});

/* ==========================================================
   Edit Product Modal
   ========================================================== */
function openEditModal(id, name, price, unit, description, imagePath) {
    /* Set form action */
    const form = document.getElementById('edit-product-form');
    form.action = '/seller/products/' + id;

    /* Fill fields */
    document.getElementById('edit-name').value = name;
    
    /* Unit */
    const presetUnits = ['bát', 'kg', 'hộp', 'gói', 'quả', 'bó', 'con', 'cái'];
    const unitSelect = document.getElementById('edit-unit');
    const unitCustom = document.getElementById('edit-unit-custom');
    const unitHidden = document.getElementById('edit-unit-hidden');

    if (presetUnits.includes(unit)) {
        unitSelect.value = unit;
        unitCustom.style.display = 'none';
        unitCustom.required = false;
        unitHidden.value = unit;
    } else if (unit && unit !== '') {
        // Đơn vị tùy chỉnh không có trong danh sách
        unitSelect.value = '__custom__';
        unitCustom.style.display = 'block';
        unitCustom.value = unit;
        unitCustom.required = true;
        unitHidden.value = unit;
    } else {
        unitSelect.value = 'kg';
        unitCustom.style.display = 'none';
        unitHidden.value = 'kg';
    }
    document.getElementById('edit-description').value = description || '';

    /* Price */
    document.getElementById('edit-price-raw').value = price;
    const priceDisplay = document.getElementById('edit-price-display');
    priceDisplay.value = price > 0 ? parseInt(price).toLocaleString('vi-VN') + ' VND' : '';

    /* Image preview */
    const imgEl = document.getElementById('edit-current-img');
    imgEl.src = imagePath || '/images/stalls/food.png';

    /* Update subtitle */
    document.getElementById('edit-modal-subtitle').textContent = 'Đang sửa: ' + name;

    /* Show modal */
    const modal = document.getElementById('edit-product-modal');
    const box = document.getElementById('edit-modal-box');
    modal.style.display = 'flex';
    setTimeout(function() { box.style.transform = 'scale(1)'; }, 10);
    document.body.style.overflow = 'hidden';

    /* Clear file input */
    document.getElementById('edit-image').value = '';
}

function closeEditModal(e) {
    if (e && e.target !== document.getElementById('edit-product-modal')) return;
    const modal = document.getElementById('edit-product-modal');
    const box = document.getElementById('edit-modal-box');
    box.style.transform = 'scale(0.92)';
    setTimeout(function() {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }, 250);
}

/* Close on ESC */
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('edit-product-modal');
        if (modal.style.display === 'flex') closeEditModal();
    }
});

/* Edit price focus */
document.getElementById('edit-price-display').addEventListener('focus', function() {
    this.value = this.value.replace(/[^\d]/g, '');
});
</script>
@endsection
