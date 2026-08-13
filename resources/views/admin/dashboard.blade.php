@extends('layouts.admin')

@section('title', 'Bảng Điều Khiển Quản Trị')

@section('content')

@php
    $categories = $categories ?? \App\Models\Category::all();
    $communes = $communes ?? \App\Models\Commune::all();
@endphp


<!-- SUCCESS ALERT BANNER -->
@if(session('success'))
    <div class="admin-alert admin-alert-success">
        <span>🎉</span>
        <div>
            <strong>Thành công!</strong> {{ session('success') }}
        </div>
    </div>
@endif

@if(session('error'))
    <div class="admin-alert admin-alert-warning" style="background-color: #fee2e2; border-color: #fecaca; color: #b91c1c;">
        <span>⚠️</span>
        <div>
            <strong>Lỗi!</strong> {{ session('error') }}
        </div>
    </div>
@endif

<!-- Welcome Header Card -->
<div class="admin-welcome-banner">
    <div>
        @if(in_array(session('user_role'), ['manager', 'seller']))
            <h1>Chào mừng Ban Quản lý Chợ 🏛️</h1>
            <p>Không gian làm việc điều phối tổng quan thông tin về Chợ, tọa độ bản đồ số, hồ sơ pháp lý cơ sở, chứng chỉ an toàn thực phẩm và các gian hàng.</p>
        @else
            <h1>Chào mừng tới Kênh Quản trị 🏰</h1>
            <p>Tìm kiếm địa điểm cơ sở và click nút "Quản lý" để điều khiển thông tin, tọa độ bản đồ, video review và chứng chỉ an toàn vệ sinh thực phẩm.</p>
        @endif
    </div>
    <div style="font-size: 2.5rem; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.12));">🏛️</div>
</div>

<!-- Stats Grid Widgets -->
<div class="admin-stats-grid">
    <div class="admin-stat-card">
        <div>
            <div class="admin-stat-val">{{ $stats['total_eateries'] }}</div>
            <div class="admin-stat-lbl">TỔNG ĐỊA ĐIỂM</div>
        </div>
        <span class="admin-stat-icon">📍</span>
    </div>

    <div class="admin-stat-card">
        <div>
            <div class="admin-stat-val">{{ $stats['total_categories'] }}</div>
            <div class="admin-stat-lbl">DANH MỤC DỊCH VỤ</div>
        </div>
        <span class="admin-stat-icon">🍔</span>
    </div>

    <div class="admin-stat-card">
        <div>
            <div class="admin-stat-val">{{ $stats['total_communes'] }}</div>
            <div class="admin-stat-lbl">XÃ / THỊ TRẤN SỐ HÓA</div>
        </div>
        <span class="admin-stat-icon">🌾</span>
    </div>

    <div class="admin-stat-card">
        <div>
            <div class="admin-stat-val">{{ $stats['total_reviews'] }}</div>
            <div class="admin-stat-lbl">TỔNG SỐ ĐÁNH GIÁ</div>
        </div>
        <span class="admin-stat-icon">💬</span>
    </div>
</div>

<!-- Main Workspace Card: Search & Eateries List -->
<div class="admin-card">
    <div class="admin-card-header" style="flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
        <h2 class="admin-card-title" style="margin-bottom: 0;">
            <span>📋</span> Cơ Sở Dịch Vụ Trên Bản Đồ Số
        </h2>
        @if(session('user_role') === 'admin' || (session('user_role') === 'seller' && $eateries->count() === 0))
        <a href="/admin/eateries/create" class="btn-admin btn-admin-primary" style="font-size: 0.8rem; padding: 8px 16px;">
            <span>➕</span> Đăng ký địa điểm mới
        </a>
        @endif
    </div>

    <!-- Premium CabaFood Style Search & Filters Bar -->
    <form method="GET" action="/admin/dashboard" id="filterForm" onsubmit="event.preventDefault();" style="width: 100%; display: flex; gap: 12px; margin-bottom: 24px; align-items: center; flex-wrap: wrap; background-color: #f8fafc; padding: 14px; border-radius: 12px; border: 1px solid var(--admin-border);">
        <div style="flex: 2; min-width: 260px; position: relative;">
            <input type="text" name="q" id="eaterySearchInput" value="{{ request('q') }}" class="admin-form-input" placeholder="🔍 Tìm theo tên cơ sở, địa chỉ hoặc số điện thoại..." style="padding-left: 14px; width: 100%;">
        </div>
        <div style="flex: 1; min-width: 160px;">
            <select name="category" id="categoryFilter" class="admin-form-input">
                <option value="">Tất cả danh mục</option>
                @foreach($categories as $cat)
                    @php
                        $displayNameEn = $cat->name;
                        $displayNameVi = $cat->name;
                        if ($cat->slug === 'dong-anh-food-map') {
                            $displayNameEn = 'ĐÔNG ANH FOOD MAP';
                            $displayNameVi = 'Ẩm thực Đông Anh';
                        } elseif ($cat->slug === 'stay-in-dong-anh') {
                            $displayNameEn = 'Stay in Đông Anh';
                            $displayNameVi = 'Nhà nghỉ, khách sạn, khu nghỉ dưỡng';
                        } elseif ($cat->slug === 'wellness-care') {
                            $displayNameEn = 'Wellness & Care';
                            $displayNameVi = 'Y tế – chăm sóc sức khỏe – spa';
                        } elseif ($cat->slug === 'dong-anh-market') {
                            $displayNameEn = 'Nông sản số';
                            $displayNameVi = 'OCOP – quà tặng – đặc sản';
                        } elseif ($cat->slug === 'smart-education-map') {
                            $displayNameEn = 'Smart Education Map';
                            $displayNameVi = 'Trường học';
                        } elseif ($cat->slug === 'hanh-trinh-di-san') {
                            $displayNameEn = 'Heritage Journey';
                            $displayNameVi = 'Hành trình di sản';
                        } elseif ($cat->slug === 'discover-dong-anh-community-culture-hub') {
                            $displayNameEn = 'Discover Dong Anh Community & Culture Hub';
                            $displayNameVi = 'Khám phá thiết chế văn hóa - thể thao Đông Anh';
                        } elseif ($cat->slug === 'co-so-kinh-doanh') {
                            $displayNameEn = 'Business & Enterprise';
                            $displayNameVi = 'Cơ sở kinh doanh, Doanh nghiệp';
                        }
                    @endphp
                    <option value="{{ $cat->name }}" {{ request('category') == $cat->name ? 'selected' : '' }}>
                        {{ $displayNameVi }} ({{ $displayNameEn }})
                    </option>
                @endforeach
            </select>
        </div>
        <div style="flex: 1; min-width: 160px;">
            <select name="commune" id="communeFilter" class="admin-form-input">
                <option value="">Tất cả khu vực xã</option>
                @foreach($communes as $com)
                    <option value="{{ $com->name }}" {{ request('commune') == $com->name ? 'selected' : '' }}>{{ $com->name }}</option>
                @endforeach
            </select>
        </div>
        <a href="/admin/dashboard" id="btnResetFilters" class="btn-admin btn-admin-secondary" style="padding: 10px 16px; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; height: 42px; box-sizing: border-box;">
            🔄 Reset
        </a>
    </form>

    <!-- Eateries Table List -->
    @if($eateries->count() > 0)
        <div class="admin-table-container">
            <table class="admin-data-table">
                <thead>
                    <tr>
                        <th style="width: 70px;">Ảnh</th>
                        <th>Tên địa điểm</th>
                        <th>Phân loại</th>
                        <th>Xã / Khu vực</th>
                        <th>Điện thoại</th>
                        <th>Đánh giá</th>
                        <th style="text-align: center; width: 220px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($eateries as $eat)
                        <tr class="eatery-table-row" 
                            data-name="{{ $eat->name }}" 
                            data-address="{{ $eat->address }}" 
                            data-phone="{{ $eat->phone ?: '' }}"
                            data-category="{{ $eat->category->name }}" 
                            data-commune="{{ $eat->commune?->name ?? 'Đông Anh' }}">
                            <td>
                                <img src="{{ $eat->image_path ?: 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=300&q=80' }}" style="width: 48px; height: 48px; object-fit: cover; border-radius: 8px; border: 1px solid var(--admin-border);" loading="lazy">
                            </td>
                            <td>
                                <strong style="color: var(--admin-text-main); font-size: 0.92rem; display: block;">{{ $eat->name }}</strong>
                                <span style="font-size: 0.76rem; color: var(--admin-text-muted); display: block; max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">📍 {{ $eat->address }}</span>
                            </td>
                            <td>
                                <span class="admin-badge admin-badge-primary">{{ $eat->category->icon }} {{ $eat->category->name }}</span>
                            </td>
                            <td>
                                <span style="font-size: 0.88rem; font-weight: 600; color: var(--admin-text-main);">{{ $eat->commune?->name ?? 'Đông Anh' }}</span>
                            </td>
                            <td>
                                <span style="font-size: 0.88rem; font-weight: 700; color: var(--admin-primary);">{{ $eat->phone ?: 'Chưa có' }}</span>
                            </td>
                            <td>
                                <div style="font-size: 0.85rem; display: flex; flex-direction: column;">
                                    <span style="color: var(--admin-warning); font-weight: 800;">★ {{ number_format($eat->average_rating, 1) }}</span>
                                    <span style="font-size: 0.72rem; color: var(--admin-text-muted);">({{ $eat->reviews_count }} reviews)</span>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <div style="display: inline-flex; gap: 6px; align-items: center;">
                                    <a href="/dia-diem/{{ $eat->slug }}" target="_blank" class="btn-admin btn-admin-secondary" style="padding: 6px 12px; font-size: 0.75rem; border-radius: 6px;" title="Xem trang bản đồ khách hàng">
                                        Xem Map
                                    </a>
                                    
                                    <!-- Dynamic Purple CabaFood-style Quản Lý Button -->
                                    <a href="/admin/eateries/{{ $eat->slug }}/edit" class="btn-admin btn-admin-primary" style="padding: 6px 14px; font-size: 0.78rem; border-radius: 6px; background-color: var(--admin-primary);">
                                        ⚙️ Quản lý
                                    </a>
                                    
                                    @if(session('user_role') === 'admin')
                                    <form action="/admin/eateries/{{ $eat->slug }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa địa điểm này khỏi Bản đồ số Đông Anh không?')" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-admin btn-admin-danger" style="padding: 6px 8px; font-size: 0.75rem; border-radius: 6px;">
                                            Xóa
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Custom Premium Pagination Links for Optimized DB Loading --}}
        @if($eateries->hasPages())
            <div class="admin-pagination" style="display: flex; justify-content: center; align-items: center; gap: 8px; margin-top: 24px;">
                {{-- Previous Page Link --}}
                @if($eateries->onFirstPage())
                    <span style="padding: 8px 16px; border-radius: 8px; background: #f1f5f9; color: #94a3b8; font-size: 0.85rem; font-weight: 600; cursor: not-allowed; border: 1px solid var(--admin-border);">Trang trước</span>
                @else
                    <a href="{{ $eateries->previousPageUrl() }}" style="padding: 8px 16px; border-radius: 8px; background: #ffffff; color: var(--admin-primary); border: 1px solid var(--admin-border); font-size: 0.85rem; font-weight: 600; text-decoration: none; transition: all 0.2s;" onmouseover="this.style.backgroundColor='var(--admin-primary-light)'" onmouseout="this.style.backgroundColor='#ffffff'">Trang trước</a>
                @endif

                {{-- Page Numbers Display --}}
                <span style="font-size: 0.88rem; font-weight: bold; color: var(--admin-text-main); background: var(--admin-primary-light); padding: 8px 16px; border-radius: 8px; border: 1px solid rgba(79, 70, 229, 0.15);">
                    Trang {{ $eateries->currentPage() }} / {{ $eateries->lastPage() }}
                </span>

                {{-- Next Page Link --}}
                @if($eateries->hasMorePages())
                    <a href="{{ $eateries->nextPageUrl() }}" style="padding: 8px 16px; border-radius: 8px; background: #ffffff; color: var(--admin-primary); border: 1px solid var(--admin-border); font-size: 0.85rem; font-weight: 600; text-decoration: none; transition: all 0.2s;" onmouseover="this.style.backgroundColor='var(--admin-primary-light)'" onmouseout="this.style.backgroundColor='#ffffff'">Trang sau</a>
                @else
                    <span style="padding: 8px 16px; border-radius: 8px; background: #f1f5f9; color: #94a3b8; font-size: 0.85rem; font-weight: 600; cursor: not-allowed; border: 1px solid var(--admin-border);">Trang sau</span>
                @endif
            </div>
        @endif
    @else
        <div style="text-align: center; padding: 40px 0; color: var(--admin-text-muted);">
            <p style="font-size: 1rem; margin-bottom: 12px;">📭 Chưa có địa điểm nào được ghim lên bản đồ số.</p>
            @if(session('user_role') === 'seller')
            <p style="font-size: 0.88rem; color: var(--admin-text-muted); max-width: 480px; margin: 0 auto 16px; line-height: 1.6;">Tài khoản của bạn chưa liên kết với địa điểm kinh doanh nào. Hãy đăng ký thông tin quán của bạn để bắt đầu quản lý thực đơn và video review!</p>
            <a href="/admin/eateries/create" class="btn-admin btn-admin-primary">➕ Đăng ký quán kinh doanh của tôi</a>
            @endif
        </div>
    @endif
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const searchInput = document.getElementById("eaterySearchInput");
        const categoryFilter = document.getElementById("categoryFilter");
        const communeFilter = document.getElementById("communeFilter");
        const btnResetFilters = document.getElementById("btnResetFilters");

        function performSearch(pageUrl = null) {
            let url;
            if (pageUrl) {
                url = new URL(pageUrl);
            } else {
                url = new URL(window.location.href);
                url.searchParams.set('q', searchInput ? searchInput.value : '');
                url.searchParams.set('category', categoryFilter ? categoryFilter.value : '');
                url.searchParams.set('commune', communeFilter ? communeFilter.value : '');
                url.searchParams.delete('page'); // Reset về trang 1 khi lọc mới
            }

            // Cập nhật mượt mà thanh địa chỉ URL của trình duyệt không tải lại trang
            window.history.pushState({}, '', url);

            // Fetch kết quả phân trang mới bằng AJAX
            fetch(url)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    // Thay thế thân bảng hiển thị dữ liệu quán ăn
                    const newTable = doc.querySelector('.admin-table-container');
                    const currentTable = document.querySelector('.admin-table-container');
                    
                    if (currentTable && newTable) {
                        currentTable.innerHTML = newTable.innerHTML;
                    } else if (newTable) {
                        location.reload();
                        return;
                    } else {
                        if (currentTable) {
                            currentTable.innerHTML = `<div style="text-align: center; padding: 40px 0; color: var(--admin-text-muted);"><p style="font-size: 1rem; margin-bottom: 12px;">📭 Không tìm thấy kết quả phù hợp.</p></div>`;
                        }
                    }

                    // Thay thế thanh liên kết phân trang
                    const newPagination = doc.querySelector('.admin-pagination');
                    const currentPagination = document.querySelector('.admin-pagination');
                    
                    if (currentPagination && newPagination) {
                        currentPagination.outerHTML = newPagination.outerHTML;
                        bindPaginationLinks();
                    } else if (currentPagination) {
                        currentPagination.remove();
                    } else if (newPagination) {
                        const tableContainer = document.querySelector('.admin-table-container');
                        if (tableContainer) {
                            tableContainer.insertAdjacentHTML('afterend', newPagination.outerHTML);
                            bindPaginationLinks();
                        }
                    }
                })
                .catch(err => {
                    console.error("Lỗi AJAX Search: ", err);
                });
        }

        function bindPaginationLinks() {
            const paginationLinks = document.querySelectorAll('.admin-pagination a');
            paginationLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const url = this.getAttribute('href');
                    performSearch(url);
                });
            });
        }

        // Lắng nghe sự kiện gõ tìm kiếm với Debounce 300ms phản hồi cực nhạy
        if (searchInput) {
            let debounceTimeout = null;
            searchInput.addEventListener("input", function() {
                clearTimeout(debounceTimeout);
                debounceTimeout = setTimeout(() => {
                    performSearch();
                }, 300);
            });

            // Ngăn chặn nút Enter gửi form gây reload trang
            searchInput.addEventListener("keydown", function(e) {
                if (e.key === "Enter") {
                    e.preventDefault();
                    performSearch();
                }
            });
        }

        if (categoryFilter) {
            categoryFilter.addEventListener("change", function() {
                performSearch();
            });
        }

        if (communeFilter) {
            communeFilter.addEventListener("change", function() {
                performSearch();
            });
        }

        if (btnResetFilters) {
            btnResetFilters.addEventListener("click", function(e) {
                e.preventDefault();
                if (searchInput) searchInput.value = "";
                if (categoryFilter) categoryFilter.value = "";
                if (communeFilter) communeFilter.value = "";
                performSearch();
            });
        }

        // Khởi động lắng nghe liên kết phân trang lần đầu
        bindPaginationLinks();
    });
</script>
@endsection
