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
        padding: 22px 26px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.03);
    }
    
    .prod-stats-row {
        display: flex;
        gap: 20px;
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

    /* CONTROL & FILTER TOOLBAR */
    .prod-toolbar {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 14px 18px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
    }

    .toolbar-left-group {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        flex: 1;
        min-width: 280px;
    }

    .prod-search-wrap {
        position: relative;
        flex: 1;
        min-width: 220px;
        max-width: 380px;
    }
    
    .prod-search-wrap i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 0.88rem;
    }
    
    .prod-search-input {
        width: 100%;
        padding: 9px 14px 9px 38px;
        border: 1.5px solid #cbd5e1;
        border-radius: 10px;
        font-size: 0.88rem;
        color: #0f172a;
        background: #fff;
        outline: none;
        transition: border-color 0.2s;
    }
    .prod-search-input:focus { border-color: #059669; }

    .filter-select-input {
        padding: 9px 12px;
        border: 1.5px solid #cbd5e1;
        border-radius: 10px;
        font-size: 0.85rem;
        color: #334155;
        background: #fff;
        cursor: pointer;
        outline: none;
    }

    /* VIEW MODE SWITCHER BUTTONS */
    .view-mode-toggle {
        display: inline-flex;
        background: #f1f5f9;
        padding: 3px;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
    }

    .view-toggle-btn {
        border: none;
        background: transparent;
        padding: 6px 14px;
        font-size: 0.85rem;
        font-weight: 700;
        color: #64748b;
        cursor: pointer;
        border-radius: 8px;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .view-toggle-btn.active {
        background: #ffffff;
        color: #059669;
        box-shadow: 0 2px 6px rgba(0,0,0,0.06);
    }

    /* 1. PRODUCT GRID (CARD VIEW) */
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
        background: rgba(15, 23, 42, 0.75);
        color: #fff;
        font-size: 0.72rem;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 20px;
        backdrop-filter: blur(4px);
    }
    .prod-star-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: #fff;
        font-size: 0.7rem;
        font-weight: 800;
        padding: 3px 8px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
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
    .prod-sku-tag {
        font-size: 0.75rem;
        font-weight: 600;
        color: #0284c7;
        background: #e0f2fe;
        padding: 2px 8px;
        border-radius: 6px;
        display: inline-block;
        margin-bottom: 6px;
        width: fit-content;
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

    /* 2. POS DATA TABLE VIEW (LIKE TENDOO POS) */
    .pos-table-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,0.02);
    }

    .pos-data-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 0.88rem;
    }

    .pos-data-table th {
        background: #f8fafc;
        color: #475569;
        font-weight: 800;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 12px 16px;
        border-bottom: 1.5px solid #e2e8f0;
        white-space: nowrap;
    }

    .pos-data-table td {
        padding: 12px 16px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        color: #1e293b;
    }

    .pos-data-table tr:hover td {
        background: #f0fdf4;
    }

    .pos-thumb-box {
        width: 44px;
        height: 44px;
        border-radius: 8px;
        overflow: hidden;
        background: #f1f5f9;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e2e8f0;
    }

    .pos-thumb-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .pos-prod-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .status-badge-in-stock {
        background: #d1fae5;
        color: #065f46;
        font-size: 0.75rem;
        font-weight: 800;
        padding: 4px 10px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        white-space: nowrap;
    }

    .status-badge-out-stock {
        background: #fee2e2;
        color: #991b1b;
        font-size: 0.75rem;
        font-weight: 800;
        padding: 4px 10px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        white-space: nowrap;
    }
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
                <span>{{ $products->total() }}</span> mặt hàng niêm yết
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

<!-- TOOLBAR: SEARCH, FILTERS & VIEW SWITCHER -->
<div class="prod-toolbar">
    <div class="toolbar-left-group">
        <div class="prod-search-wrap">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" class="prod-search-input" id="searchInput" onkeyup="applyProductFilters()" placeholder="Tìm kiếm theo Tên, SKU, Mô tả...">
        </div>

        <select id="stockFilter" class="filter-select-input" onchange="applyProductFilters()">
            <option value="all">Tất cả tồn kho</option>
            <option value="in_stock">🟢 Còn hàng</option>
            <option value="out_of_stock">🔴 Hết hàng</option>
        </select>

        <select id="ocopFilter" class="filter-select-input" onchange="applyProductFilters()">
            <option value="all">Tất cả phân hạng</option>
            <option value="ocop">⭐ Đạt OCOP (3-5 Sao)</option>
            <option value="normal">Sản phẩm thường</option>
        </select>
    </div>

    <div style="display: flex; align-items: center; gap: 14px;">
        <!-- VIEW MODE TOGGLE BUTTONS -->
        <div class="view-mode-toggle">
            <button type="button" class="view-toggle-btn" id="btnViewGrid" onclick="setProductViewMode('grid')">
                <i class="fa-solid fa-border-all"></i>
                <span>Thẻ Grid</span>
            </button>
            <button type="button" class="view-toggle-btn" id="btnViewTable" onclick="setProductViewMode('table')">
                <i class="fa-solid fa-table-list"></i>
                <span>Bảng POS</span>
            </button>
        </div>

        <div style="font-size: 0.82rem; color: #94a3b8; font-weight: 600;" id="filteredCountInfo">
            Hiển thị {{ $products->count() }} / {{ $products->total() }} mặt hàng
        </div>
    </div>
</div>

<!-- EMPTY STATE -->
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

    <!-- 1. GRID CARD VIEW (CONTAINER 1) -->
    <div class="prod-grid" id="productGridContainer">
        @foreach($products as $p)
            @php
                $specs = [];
                if (!empty($p->ingredients)) {
                    $specs = is_array($p->ingredients) ? $p->ingredients : (json_decode($p->ingredients, true) ?: []);
                }
                $sku = $specs['sku'] ?? ('SP-' . str_pad($p->id, 4, '0', STR_PAD_LEFT));
                $stockStat = $specs['stock_status'] ?? 'in_stock';
                $isOcop = !empty($p->star_rating);
            @endphp
            <div class="prod-card product-item-card"
                 data-name="{{ strtolower($p->name ?? '') }}"
                 data-sku="{{ strtolower($sku) }}"
                 data-desc="{{ strtolower($p->description ?? '') }}"
                 data-stock="{{ $stockStat }}"
                 data-ocop="{{ $isOcop ? 'ocop' : 'normal' }}">
                
                <div class="prod-img-box" style="cursor: pointer;" onclick="window.location.href='{{ route('hkd.products.show', $p->id) }}'">
                    @if($p->image)
                        <img src="{{ $p->image }}" alt="{{ $p->name }}">
                    @else
                        <div style="text-align: center; color: #cbd5e1;">
                            <i class="fa-solid fa-image" style="font-size: 2.5rem;"></i>
                            <div style="font-size: 0.72rem; margin-top: 4px; font-weight: 600;">Chưa có ảnh</div>
                        </div>
                    @endif

                    @if($stockStat === 'out_of_stock')
                        <div style="position: absolute; top: 10px; left: 10px; background: #dc2626; color: #fff; font-size: 0.72rem; font-weight: 800; padding: 3px 10px; border-radius: 12px; z-index: 2; box-shadow: 0 2px 8px rgba(220, 38, 38, 0.4);">
                            🔴 Hết hàng
                        </div>
                    @endif

                    @if($isOcop)
                        <div class="prod-star-badge">⭐ {{ $p->star_rating }}</div>
                    @endif

                    <div class="prod-unit-badge">{{ $p->unit ?? 'Cái' }}</div>
                </div>

                <div class="prod-body">
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 6px; margin-bottom: 6px; flex-wrap: wrap;">
                        <div class="prod-sku-tag">SKU: {{ $sku }}</div>
                        @if($stockStat === 'out_of_stock')
                            <span style="font-size: 0.72rem; font-weight: 800; color: #dc2626; background: #fef2f2; border: 1px solid #fca5a5; padding: 2px 8px; border-radius: 6px;">🔴 Hết hàng</span>
                        @else
                            <span style="font-size: 0.72rem; font-weight: 700; color: #059669; background: #ecfdf5; border: 1px solid #a7f3d0; padding: 2px 8px; border-radius: 6px;">🟢 Còn hàng</span>
                        @endif
                    </div>
                    <div class="prod-name" style="{{ $stockStat === 'out_of_stock' ? 'color: #64748b;' : '' }}">
                        <a href="{{ route('hkd.products.show', $p->id) }}" style="color: inherit; text-decoration: none;">{{ $p->name }}</a>
                    </div>
                    <div class="prod-price">
                        {{ number_format($p->price) }}đ
                        <span style="font-size: 0.78rem; font-weight: 600; color: #94a3b8;">/ {{ $p->unit ?? 'Cái' }}</span>
                    </div>

                    @if(!empty($specs['wholesale_price']))
                        <div style="font-size: 0.76rem; color: #0284c7; font-weight: 700; margin-bottom: 4px;">
                            🏷️ Giá sỉ: {{ number_format($specs['wholesale_price']) }}đ (từ {{ $specs['wholesale_qty'] ?? 10 }} {{ $p->unit ?? 'Cái' }})
                        </div>
                    @endif

                    @if($p->description)
                        <div class="prod-desc">{{ $p->description }}</div>
                    @else
                        <div class="prod-desc" style="font-style: italic; color: #cbd5e1;">Chưa có mô tả</div>
                    @endif
                </div>

                <div class="prod-actions">
                    <a href="{{ route('hkd.products.show', $p->id) }}" class="prod-btn" style="background: #f1f5f9; color: #475569; text-decoration: none;">
                        <i class="fa-solid fa-eye"></i> Xem
                    </a>
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

    <!-- 2. POS DATA TABLE VIEW (CONTAINER 2 - LIKE TENDOO POS) -->
    <div class="pos-table-card" id="productTableContainer" style="display: none;">
        <table class="pos-data-table">
            <thead>
                <tr>
                    <th style="width: 50px; text-align: center;">STT</th>
                    <th style="width: 110px;">Mã SKU</th>
                    <th>Sản Phẩm & Hàng Hóa</th>
                    <th style="width: 90px;">Đơn Vị</th>
                    <th style="width: 110px; text-align: right;">Giá Bán Lẻ</th>
                    <th style="width: 110px; text-align: right;">Giá Sỉ Lô</th>
                    <th style="width: 120px; text-align: center;">Trạng Thái Kho</th>
                    <th style="width: 100px; text-align: center;">Hành Động</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $idx => $p)
                    @php
                        $specs = [];
                        if (!empty($p->ingredients)) {
                            $specs = is_array($p->ingredients) ? $p->ingredients : (json_decode($p->ingredients, true) ?: []);
                        }
                        $sku = $specs['sku'] ?? ('SP-' . str_pad($p->id, 4, '0', STR_PAD_LEFT));
                        $stockStat = $specs['stock_status'] ?? 'in_stock';
                        $isOcop = !empty($p->star_rating);
                    @endphp
                    <tr class="product-item-row"
                        data-name="{{ strtolower($p->name ?? '') }}"
                        data-sku="{{ strtolower($sku) }}"
                        data-desc="{{ strtolower($p->description ?? '') }}"
                        data-stock="{{ $stockStat }}"
                        data-ocop="{{ $isOcop ? 'ocop' : 'normal' }}">
                        <td style="text-align: center; font-weight: 700; color: #64748b;">{{ $idx + 1 }}</td>
                        <td>
                            <span style="font-family: monospace; font-size: 0.82rem; font-weight: 700; color: #0284c7; background: #e0f2fe; padding: 2px 6px; border-radius: 4px;">
                                {{ $sku }}
                            </span>
                        </td>
                        <td>
                            <div class="pos-prod-info">
                                <div class="pos-thumb-box">
                                    @if($p->image)
                                        <img src="{{ $p->image }}" alt="{{ $p->name }}">
                                    @else
                                        <i class="fa-solid fa-box" style="color: #94a3b8;"></i>
                                    @endif
                                </div>
                                <div>
                                    <div style="font-weight: 800; color: #0f172a; font-size: 0.92rem;">
                                        <a href="{{ route('hkd.products.show', $p->id) }}" style="color: inherit; text-decoration: none;">{{ $p->name }}</a>
                                        @if($isOcop)
                                            <span style="font-size: 0.7rem; background: #fef3c7; color: #d97706; font-weight: 800; padding: 2px 6px; border-radius: 10px; margin-left: 4px;">⭐ {{ $p->star_rating }}</span>
                                        @endif
                                    </div>
                                    @if(!empty($specs['product_type']))
                                        <div style="font-size: 0.75rem; color: #64748b;">{{ Str::limit($specs['product_type'], 40) }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <span style="font-weight: 600; color: #475569;">{{ $p->unit ?? 'Cái' }}</span>
                        </td>
                        <td style="text-align: right; font-weight: 800; color: #059669; font-size: 0.95rem;">
                            {{ number_format($p->price) }}đ
                        </td>
                        <td style="text-align: right; font-weight: 700; color: #0284c7; font-size: 0.85rem;">
                            @if(!empty($specs['wholesale_price']))
                                {{ number_format($specs['wholesale_price']) }}đ
                            @else
                                <span style="color: #cbd5e1; font-weight: 400;">—</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            @if($stockStat === 'out_of_stock')
                                <span class="status-badge-out-stock">🔴 Hết hàng</span>
                            @else
                                <span class="status-badge-in-stock">🟢 Còn hàng</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <div style="display: flex; justify-content: center; gap: 6px;">
                                <a href="{{ route('hkd.products.show', $p->id) }}" 
                                   title="Xem chi tiết"
                                   style="width: 32px; height: 32px; background: #f1f5f9; color: #475569; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;">
                                    <i class="fa-solid fa-eye" style="font-size: 0.85rem;"></i>
                                </a>
                                <a href="{{ route('hkd.products.edit', $p->id) }}" 
                                   title="Chỉnh sửa"
                                   style="width: 32px; height: 32px; background: #eff6ff; color: #2563eb; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;">
                                    <i class="fa-solid fa-pen" style="font-size: 0.85rem;"></i>
                                </a>
                                <form action="{{ route('hkd.products.destroy', $p->id) }}" method="POST"
                                      onsubmit="return confirm('Xóa mặt hàng này khỏi danh mục?')" style="display: inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" 
                                            title="Xóa sản phẩm"
                                            style="width: 32px; height: 32px; background: #fef2f2; color: #dc2626; border: none; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer;">
                                        <i class="fa-solid fa-trash" style="font-size: 0.85rem;"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="margin-top: 24px;">{{ $products->links() }}</div>
@endif

<script>
    // VIEW MODE SWITCHER (GRID vs TABLE)
    function setProductViewMode(mode) {
        const gridElem = document.getElementById('productGridContainer');
        const tableElem = document.getElementById('productTableContainer');
        const btnGrid = document.getElementById('btnViewGrid');
        const btnTable = document.getElementById('btnViewTable');

        if (!gridElem || !tableElem) return;

        if (mode === 'table') {
            gridElem.style.display = 'none';
            tableElem.style.display = 'block';
            btnGrid.classList.remove('active');
            btnTable.classList.add('active');
            localStorage.setItem('hkd_prod_view_mode', 'table');
        } else {
            gridElem.style.display = 'grid';
            tableElem.style.display = 'none';
            btnGrid.classList.add('active');
            btnTable.classList.remove('active');
            localStorage.setItem('hkd_prod_view_mode', 'grid');
        }
    }

    // FILTER FUNCTION (REALTIME SEARCH & DROPDOWN FILTERS)
    function applyProductFilters() {
        const q = (document.getElementById('searchInput').value || '').toLowerCase().trim();
        const stock = document.getElementById('stockFilter').value;
        const ocop = document.getElementById('ocopFilter').value;

        let visibleCount = 0;

        // Filter Grid Cards
        document.querySelectorAll('.product-item-card').forEach(card => {
            const name = card.dataset.name || '';
            const sku = card.dataset.sku || '';
            const desc = card.dataset.desc || '';
            const itemStock = card.dataset.stock || 'in_stock';
            const itemOcop = card.dataset.ocop || 'normal';

            const matchSearch = !q || name.includes(q) || sku.includes(q) || desc.includes(q);
            const matchStock = (stock === 'all') || (itemStock === stock);
            const matchOcop = (ocop === 'all') || (itemOcop === ocop);

            if (matchSearch && matchStock && matchOcop) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        // Filter Table Rows
        document.querySelectorAll('.product-item-row').forEach(row => {
            const name = row.dataset.name || '';
            const sku = row.dataset.sku || '';
            const desc = row.dataset.desc || '';
            const itemStock = row.dataset.stock || 'in_stock';
            const itemOcop = row.dataset.ocop || 'normal';

            const matchSearch = !q || name.includes(q) || sku.includes(q) || desc.includes(q);
            const matchStock = (stock === 'all') || (itemStock === stock);
            const matchOcop = (ocop === 'all') || (itemOcop === ocop);

            if (matchSearch && matchStock && matchOcop) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Initialize view mode from localStorage
    document.addEventListener('DOMContentLoaded', () => {
        const savedMode = localStorage.getItem('hkd_prod_view_mode') || 'grid';
        setProductViewMode(savedMode);
    });
</script>
@endsection
