<!-- ROLE SUB-VIEW: ADMIN -->
<!-- Mini Top Key Stat Cards for Admin -->
<div class="pro-mini-stats-grid" x-show="activeTab === 'overview'">
    <div class="pro-mini-stat-card">
        <div class="pro-mini-stat-icon">📊</div>
        <div class="pro-mini-stat-val">{{ $posts->count() }} bài</div>
        <div class="pro-mini-stat-lbl">Bài đăng Admin</div>
    </div>
    <div class="pro-mini-stat-card">
        <div class="pro-mini-stat-icon">👥</div>
        <div class="pro-mini-stat-val">{{ $followersCount }} người</div>
        <div class="pro-mini-stat-lbl">Người theo dõi</div>
    </div>
    <div class="pro-mini-stat-card">
        <div class="pro-mini-stat-icon">🛡️</div>
        <div class="pro-mini-stat-val">Quản trị viên</div>
        <div class="pro-mini-stat-lbl">Cấp độ hệ thống</div>
    </div>
    <div class="pro-mini-stat-card">
        <div class="pro-mini-stat-icon">⭐</div>
        <div class="pro-mini-stat-val" style="color: #ef4444;">Admin ⭐</div>
        <div class="pro-mini-stat-lbl">Xác thực hệ thống</div>
    </div>
</div>
