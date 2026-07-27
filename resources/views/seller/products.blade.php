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
                            <!-- Nút Xem chi tiết mở modal -->
                            <button type="button"
                                class="btn-admin"
                                style="padding: 6px 11px; font-size: 0.78rem; background: #eff6ff; color: #1d4ed8; border: 1.5px solid rgba(29,78,216,0.3); border-radius: 8px; font-weight: 700; cursor: pointer; transition: all 0.2s;"
                                onmouseover="this.style.background='#dbeafe'"
                                onmouseout="this.style.background='#eff6ff'"
                                onclick="openDetailModal(
                                    {{ json_encode($p->name) }},
                                    {{ intval($p->price) }},
                                    {{ json_encode($p->unit ?? '') }},
                                    {{ json_encode($p->description ?? '') }},
                                    {{ json_encode($p->image_path ?? '') }},
                                    {{ json_encode($p->star_rating ?? '4 sao') }}
                                )">
                                👁️ Xem
                            </button>

                            <!-- Nút Sửa mở modal -->
                            <button type="button"
                                class="btn-admin"
                                style="padding: 6px 11px; font-size: 0.78rem; background: #fef3c7; color: #92400e; border: 1.5px solid rgba(217,119,6,0.3); border-radius: 8px; font-weight: 700; cursor: pointer; transition: all 0.2s;"
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
     MODAL SỬA SẢN PHẨM (Form đồng bộ 100% với Form Thêm)
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
        width: 100%; max-width: 720px;
        margin: 24px;
        overflow: hidden;
        transform: scale(0.92);
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    " id="edit-modal-box" onclick="event.stopPropagation()">

        <!-- Modal Header -->
        <div style="background: linear-gradient(135deg, #1c1007 0%, #2d1a0a 100%); color: #fff; padding: 20px 28px; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="font-size: 1.1rem; font-weight: 800;">✏️ Sửa Thông Tin Sản Phẩm</div>
                <div style="font-size: 0.8rem; color: rgba(255,255,255,0.55); margin-top: 2px;" id="edit-modal-subtitle">Cập nhật tên, giá, nguồn gốc và hình ảnh</div>
            </div>
            <button onclick="closeEditModal()" style="background: rgba(255,255,255,0.12); border: none; color: #fff; width: 36px; height: 36px; border-radius: 50%; font-size: 1.2rem; cursor: pointer; transition: background 0.2s;"
                    onmouseover="this.style.background='rgba(255,255,255,0.22)'"
                    onmouseout="this.style.background='rgba(255,255,255,0.12)'">×</button>
        </div>

        <!-- Current Image Preview -->
        <div style="padding: 16px 28px 0; display: flex; align-items: center; gap: 14px;">
            <img id="edit-current-img" src="" alt="Ảnh hiện tại"
                 style="width: 64px; height: 64px; border-radius: 12px; object-fit: cover; border: 2px solid #fde68a; flex-shrink: 0;">
            <div>
                <div style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #92400e;">Ảnh hiện tại</div>
                <div style="font-size: 0.8rem; color: #78716c; margin-top: 2px;">Tải ảnh mới bên dưới nếu muốn thay đổi</div>
            </div>
        </div>

        <!-- Modal Form -->
        <form id="edit-product-form" method="POST" enctype="multipart/form-data" style="padding: 20px 28px 28px;">
            @csrf
            @method('PUT')
            <input type="hidden" name="price" id="edit-price-raw">

            <!-- Row 1: 3 Cols (Tên, Giá, Đơn vị) -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                <div>
                    <label class="admin-form-label">Tên sản phẩm / Món ăn *</label>
                    <input type="text" name="name" id="edit-name" class="admin-form-input" required placeholder="Ví dụ: Bún riêu cua, Cà chua hữu cơ...">
                </div>

                <div>
                    <label class="admin-form-label">Giá niêm yết (VNĐ) *</label>
                    <div style="position: relative;">
                        <input type="text" id="edit-price-display" class="admin-form-input"
                               autocomplete="off"
                               style="padding-right: 52px;"
                               placeholder="VD: 30.000 VND"
                               oninput="formatPriceInput(this, 'edit-price-raw')"
                               onblur="formatPriceInput(this, 'edit-price-raw')">
                        <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 0.75rem; font-weight: 700; color: var(--slr-primary); pointer-events: none;">VND</span>
                    </div>
                </div>

                <div>
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

            <!-- Row 2: 2 Cols (Nguồn gốc, Ảnh đại diện mới) -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 16px;">
                <div>
                    <label class="admin-form-label">Nguồn gốc hàng hóa / Nông sản</label>
                    <input type="text" name="origin" id="edit-origin" class="admin-form-input" placeholder="Ví dụ: Tự sản xuất, Chợ đầu mối Long Biên...">
                </div>

                <div>
                    <label class="admin-form-label">Ảnh đại diện sản phẩm (Upload)</label>
                    <input type="file" name="image" id="edit-image" class="admin-form-input" accept="image/*">
                </div>
            </div>

            <!-- Row 3: Mô tả -->
            <div style="margin-top: 16px;">
                <label class="admin-form-label">Mô tả sản phẩm</label>
                <textarea name="description" id="edit-description" class="admin-form-input" rows="2"
                          placeholder="Nhập mô tả ngắn về thành phần, nguyên liệu tươi ngon..."></textarea>
            </div>

            <!-- Actions -->
            <div style="display: flex; gap: 12px; justify-content: flex-end; border-top: 1px solid #fde68a; padding-top: 20px; margin-top: 20px;">
                <button type="button" onclick="closeEditModal()" class="btn-admin btn-admin-secondary">
                    Hủy bỏ
                </button>
                <button type="submit" class="btn-admin btn-admin-primary" style="min-width: 140px; font-weight: 700;">
                    💾 Lưu thay đổi
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================
     MODAL XEM CHI TIẾT SẢN PHẨM
     ============================================================ -->
<div id="detail-product-modal" style="
    display: none;
    position: fixed; inset: 0; z-index: 10000;
    background: rgba(28,16,7,0.65);
    backdrop-filter: blur(8px);
    align-items: center; justify-content: center;
" onclick="closeDetailModal(event)">

    <div style="
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 25px 60px rgba(0,0,0,0.3);
        width: 100%; max-width: 580px;
        margin: 24px;
        overflow: hidden;
        transform: scale(0.92);
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    " id="detail-modal-box" onclick="event.stopPropagation()">

        <!-- Modal Header -->
        <div style="background: linear-gradient(135deg, #1c1007 0%, #2d1a0a 100%); color: #fff; padding: 20px 24px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 1.3rem;">👁️</span>
                <div>
                    <div style="font-size: 1.1rem; font-weight: 800;">Chi Tiết Sản Phẩm</div>
                    <div style="font-size: 0.78rem; color: rgba(255,255,255,0.6); margin-top: 2px;">Gian hàng: {{ $stallName }}</div>
                </div>
            </div>
            <button onclick="closeDetailModal()" style="background: rgba(255,255,255,0.12); border: none; color: #fff; width: 36px; height: 36px; border-radius: 50%; font-size: 1.2rem; cursor: pointer; transition: background 0.2s;"
                    onmouseover="this.style.background='rgba(255,255,255,0.22)'"
                    onmouseout="this.style.background='rgba(255,255,255,0.12)'">×</button>
        </div>

        <div style="padding: 24px;">
            <div style="display: flex; gap: 20px; align-items: flex-start;">
                <!-- Product Image -->
                <img id="detail-img" src="" alt="Ảnh sản phẩm"
                     style="width: 150px; height: 150px; border-radius: 16px; object-fit: cover; border: 2.5px solid #fde68a; box-shadow: 0 4px 12px rgba(0,0,0,0.08); flex-shrink: 0;">

                <!-- Main Meta Info -->
                <div style="flex: 1;">
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 6px;">
                        <span id="detail-star" style="background: #fef3c7; color: #92400e; font-size: 0.72rem; font-weight: 800; padding: 3px 8px; border-radius: 12px; border: 1px solid #fde68a;">⭐ 4 sao</span>
                        <span style="background: #ecfdf5; color: #065f46; font-size: 0.72rem; font-weight: 800; padding: 3px 8px; border-radius: 12px; border: 1px solid #a7f3d0;">Đã niêm yết</span>
                    </div>

                    <h3 id="detail-name" style="font-size: 1.25rem; font-weight: 800; color: #1c1007; margin: 0 0 8px 0; line-height: 1.3;"></h3>

                    <div style="font-size: 1.4rem; font-weight: 900; color: var(--slr-primary); margin-bottom: 12px;">
                        <span id="detail-price"></span> <span id="detail-unit" style="font-size: 0.85rem; font-weight: 600; color: #78716c;"></span>
                    </div>

                    <div style="font-size: 0.82rem; color: #78716c; background: #fffbeb; padding: 10px 14px; border-radius: 10px; border: 1px solid #fde68a;">
                        <div style="font-weight: 700; color: #92400e; margin-bottom: 2px;">📍 Nguồn gốc / Xuất xứ:</div>
                        <div id="detail-origin" style="color: #44403c; font-weight: 600;"></div>
                    </div>
                </div>
            </div>

            <!-- Description Block -->
            <div style="margin-top: 20px; border-top: 1px solid #f3f4f6; padding-top: 16px;">
                <div style="font-size: 0.82rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: #92400e; margin-bottom: 6px;">📝 Mô tả chi tiết</div>
                <div id="detail-description" style="font-size: 0.9rem; color: #44403c; line-height: 1.6; background: #f8fafc; padding: 14px 16px; border-radius: 12px; border: 1px solid #e2e8f0; white-space: pre-line;"></div>
            </div>

            <!-- Footer button -->
            <div style="margin-top: 24px; text-align: right;">
                <button type="button" onclick="closeDetailModal()" class="btn-admin btn-admin-secondary" style="padding: 9px 20px; font-weight: 700;">
                    Đóng cửa sổ
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
/* ==========================================================
   Detail Product Modal
   ========================================================== */
function openDetailModal(name, price, unit, description, imagePath, starRating) {
    document.getElementById('detail-name').textContent = name;
    document.getElementById('detail-unit').textContent = unit ? ('/' + unit) : '';
    document.getElementById('detail-price').textContent = price > 0 ? (parseInt(price).toLocaleString('vi-VN') + 'đ') : 'Liên hệ';
    document.getElementById('detail-img').src = imagePath || '/images/stalls/food.png';
    document.getElementById('detail-star').textContent = '⭐ ' + (starRating || '4 sao');
    
    let originVal = 'Chưa cập nhật';
    let descVal = description || 'Chưa có mô tả.';
    if (descVal.startsWith('Nguồn gốc:')) {
        const parts = descVal.split('.');
        const firstPart = parts.shift();
        originVal = firstPart.replace(/^Nguồn gốc:\s*/, '').trim();
        descVal = parts.join('.').trim() || 'Chưa có mô tả thêm.';
    }
    document.getElementById('detail-origin').textContent = originVal;
    document.getElementById('detail-description').textContent = descVal;

    const modal = document.getElementById('detail-product-modal');
    const box = document.getElementById('detail-modal-box');
    modal.style.display = 'flex';
    setTimeout(function() { box.style.transform = 'scale(1)'; }, 10);
    document.body.style.overflow = 'hidden';
}

function closeDetailModal(e) {
    if (e && e.target !== document.getElementById('detail-product-modal')) return;
    const modal = document.getElementById('detail-product-modal');
    const box = document.getElementById('detail-modal-box');
    box.style.transform = 'scale(0.92)';
    setTimeout(function() {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }, 250);
}

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

    /* Separate Origin & Description */
    let originVal = '';
    let descVal = description || '';
    if (descVal.startsWith('Nguồn gốc:')) {
        const parts = descVal.split('.');
        const firstPart = parts.shift();
        originVal = firstPart.replace(/^Nguồn gốc:\s*/, '').trim();
        descVal = parts.join('.').trim();
    }
    document.getElementById('edit-origin').value = originVal;
    document.getElementById('edit-description').value = descVal;

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
        const modalEdit = document.getElementById('edit-product-modal');
        const modalDetail = document.getElementById('detail-product-modal');
        if (modalEdit && modalEdit.style.display === 'flex') closeEditModal();
        if (modalDetail && modalDetail.style.display === 'flex') closeDetailModal();
    }
});

/* Edit price focus */
document.getElementById('edit-price-display').addEventListener('focus', function() {
    this.value = this.value.replace(/[^\d]/g, '');
});
</script>
@endsection
