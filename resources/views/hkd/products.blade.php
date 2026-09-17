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
        background: rgba(15, 23, 42, 0.65);
        backdrop-filter: blur(6px);
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px 16px;
    }
    .prod-modal-bg.open { display: flex; }
    .prod-modal {
        background: #fff;
        border-radius: 24px;
        width: 95%;
        max-width: 880px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 25px 60px rgba(15, 23, 42, 0.35);
    }
    .prod-modal-head {
        padding: 22px 28px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        position: sticky;
        top: 0;
        background: #fff;
        z-index: 2;
        border-radius: 24px 24px 0 0;
    }
    .prod-modal-head h3 { font-size: 1.22rem; font-weight: 800; color: #0f172a; }
    .prod-modal-head p { font-size: 0.82rem; color: #64748b; margin-top: 3px; }
    .prod-modal-close {
        background: #f1f5f9; border: none;
        width: 34px; height: 34px; border-radius: 50%;
        font-size: 1.2rem; cursor: pointer; color: #64748b;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        transition: background 0.15s;
    }
    .prod-modal-close:hover { background: #e2e8f0; }
    .prod-modal-body { padding: 24px 28px; }
    .prod-modal-foot {
        padding: 16px 28px;
        border-top: 1px solid #f1f5f9;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        background: #f8fafc;
        border-radius: 0 0 24px 24px;
        position: sticky;
        bottom: 0;
    }

    /* CATEGORY QUICK PICKS */
    .cat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(145px, 1fr));
        gap: 10px;
        margin-bottom: 22px;
    }
    .cat-chip {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 4px;
        padding: 10px 8px;
        border: 1.5px solid #e2e8f0;
        border-radius: 14px;
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

    /* FORM LAYOUT & FIELDS */
    .prod-form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    @media (max-width: 768px) {
        .prod-form-grid { grid-template-columns: 1fr; }
    }

    .field-group { margin-bottom: 16px; }
    .field-label {
        font-size: 0.85rem;
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

    <a href="{{ route('hkd.products.create') }}" class="hkd-btn-action hkd-btn-emerald" style="padding: 12px 22px; font-size: 0.92rem; white-space: nowrap; text-decoration: none;">
        <i class="fa-solid fa-plus"></i> Thêm Hàng / Dịch Vụ Mới
    </a>
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
        <a href="{{ route('hkd.products.create') }}" class="hkd-btn-action hkd-btn-emerald" style="text-decoration: none;">
            <i class="fa-solid fa-plus"></i> Thêm Mặt Hàng Đầu Tiên
        </a>
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
                    <div class="prod-desc" style="font-style: italic; color: #cbd5e1;">Chưa có mô tả</div>
                @endif
            </div>

            <div class="prod-actions">
                <a href="{{ route('hkd.products.edit', $p->id) }}" class="prod-btn prod-btn-edit" style="text-decoration: none;">
                    <i class="fa-solid fa-pen"></i> Sửa
                </a>
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

<script>
    function filterProducts() {
        const q = document.getElementById('searchInput').value.toLowerCase().trim();
        document.querySelectorAll('.product-item-card').forEach(card => {
            const match = card.dataset.name.includes(q) || card.dataset.desc.includes(q);
            card.style.display = match ? '' : 'none';
        });
    }
</script>
@endsection
