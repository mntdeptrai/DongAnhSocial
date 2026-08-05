<!-- ROLE SUB-VIEW: PRINCIPAL / SCHOOL -->
<!-- 1. Mini Top Key Stat Cards -->
<div class="pro-mini-stats-grid" x-show="activeTab === 'overview'">
    <div class="pro-mini-stat-card">
        <div class="pro-mini-stat-icon">📅</div>
        <div class="pro-mini-stat-val">{{ $foundedYr ?: 'Chưa cập nhật' }}</div>
        <div class="pro-mini-stat-lbl">Thành lập</div>
    </div>
    <div class="pro-mini-stat-card">
        <div class="pro-mini-stat-icon">👩‍🏫</div>
        <div class="pro-mini-stat-val">{{ $staffCount !== null ? $staffCount . ' người' : 'Chưa cập nhật' }}</div>
        <div class="pro-mini-stat-lbl">Giáo viên</div>
    </div>
    <div class="pro-mini-stat-card">
        <div class="pro-mini-stat-icon">🎒</div>
        <div class="pro-mini-stat-val">{{ $studentsCount !== null ? $studentsCount . ' bé' : 'Chưa cập nhật' }}</div>
        <div class="pro-mini-stat-lbl">Học sinh</div>
    </div>
    <div class="pro-mini-stat-card">
        <div class="pro-mini-stat-icon">🏆</div>
        <div class="pro-mini-stat-val">{{ $awardsCount !== null ? $awardsCount . ' danh hiệu' : 'Chưa cập nhật' }}</div>
        <div class="pro-mini-stat-lbl">Giải thưởng</div>
    </div>
</div>
