<!-- ROLE SUB-VIEW: USER / MEMBER -->
<!-- Mini Top Key Stat Cards for User -->
<div class="pro-mini-stats-grid" x-show="activeTab === 'overview'">
    <div class="pro-mini-stat-card">
        <div class="pro-mini-stat-icon">📰</div>
        <div class="pro-mini-stat-val">{{ $posts->count() }} bài</div>
        <div class="pro-mini-stat-lbl">Bài viết cá nhân</div>
    </div>
    <div class="pro-mini-stat-card">
        <div class="pro-mini-stat-icon">🗺️</div>
        <div class="pro-mini-stat-val">{{ $tours->count() }} lịch</div>
        <div class="pro-mini-stat-lbl">Lộ trình Food Tour</div>
    </div>
    <div class="pro-mini-stat-card">
        <div class="pro-mini-stat-icon">👥</div>
        <div class="pro-mini-stat-val">{{ $followersCount }} người</div>
        <div class="pro-mini-stat-lbl">Người theo dõi</div>
    </div>
    <div class="pro-mini-stat-card">
        <div class="pro-mini-stat-icon">🤝</div>
        <div class="pro-mini-stat-val">{{ $followingCount }} kết nối</div>
        <div class="pro-mini-stat-lbl">Đang theo dõi</div>
    </div>
</div>
