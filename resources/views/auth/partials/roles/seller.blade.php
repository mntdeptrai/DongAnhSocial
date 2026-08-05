<!-- ROLE SUB-VIEW: SELLER -->
<!-- Mini Top Key Stat Cards for Seller -->
<div class="pro-mini-stats-grid" x-show="activeTab === 'overview'">
    <div class="pro-mini-stat-card">
        <div class="pro-mini-stat-icon">🛒</div>
        <div class="pro-mini-stat-val" style="font-size: 0.9rem;">{{ $stall ? Str::limit($stall->stall_name, 12) : 'Gian hàng' }}</div>
        <div class="pro-mini-stat-lbl">Tên gian hàng</div>
    </div>
    <div class="pro-mini-stat-card">
        <div class="pro-mini-stat-icon">📦</div>
        <div class="pro-mini-stat-val">{{ $ocopProductsCount }} SP</div>
        <div class="pro-mini-stat-lbl">Sản phẩm OCOP</div>
    </div>
    <div class="pro-mini-stat-card">
        <div class="pro-mini-stat-icon">👥</div>
        <div class="pro-mini-stat-val">{{ $followersCount }} người</div>
        <div class="pro-mini-stat-lbl">Khách hàng theo dõi</div>
    </div>
    <div class="pro-mini-stat-card">
        <div class="pro-mini-stat-icon">💬</div>
        <div class="pro-mini-stat-val">{{ $totalRev }} nhận xét</div>
        <div class="pro-mini-stat-lbl">Đánh giá gian hàng</div>
    </div>
</div>
