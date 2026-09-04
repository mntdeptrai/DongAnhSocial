@extends('layouts.app')

@section('title', 'Livestream Trực Tiếp Đông Anh - Bán Hàng OCOP & Giao Lưu Khám Phá')
@section('meta_description', 'Khám phá các phiên phát trực tiếp (Livestream) sôi động tại Đông Anh: Giới thiệu nông sản OCOP, ẩm thực truyền thống, du lịch trải nghiệm Cổ Loa, Đền Sái.')

@section('content')
<div class="livestream-hub-container">
    <!-- Ambient Glow Background -->
    <div class="glow-orb orb-1"></div>
    <div class="glow-orb orb-2"></div>

    <!-- Hero Banner -->
    <div class="live-hero-card">
        <div class="live-hero-content">
            <div class="live-hero-badge">
                <span class="live-pulse-dot"></span>
                <span>ĐÔNG ANH LIVE STUDIO</span>
            </div>
            <h1 class="live-hero-title">Phát Trực Tiếp Khám Phá & Chợ Số OCOP</h1>
            <p class="live-hero-subtitle">
                Kết nối trực tiếp cùng các tiểu thương, nghệ nhân ẩm thực và người sáng tạo nội dung Đông Anh trong thời gian thực.
            </p>
            <div class="live-hero-actions">
                <a href="{{ route('livestream.create') }}" class="btn-go-live">
                    <span class="btn-icon">📹</span>
                    <span>Bắt đầu Phát Trực Tiếp</span>
                </a>
                <a href="#active-streams" class="btn-explore-live">
                    <span>Khám phá các phòng Live</span>
                    <span>↓</span>
                </a>
            </div>
        </div>
    </div>


    <!-- Category Filters -->
    <div class="live-filter-wrapper">
        <div class="live-filter-scroll">
            <a href="{{ route('livestream.index', ['category' => 'all']) }}" class="filter-chip {{ $category === 'all' || !$category ? 'active' : '' }}">
                🌟 Tất cả phòng
            </a>
            <a href="{{ route('livestream.index', ['category' => 'ocop']) }}" class="filter-chip {{ $category === 'ocop' ? 'active' : '' }}">
                🌾 Nông sản & OCOP
            </a>
            <a href="{{ route('livestream.index', ['category' => 'food']) }}" class="filter-chip {{ $category === 'food' ? 'active' : '' }}">
                🍜 Ẩm thực & Quán ngon
            </a>
            <a href="{{ route('livestream.index', ['category' => 'travel']) }}" class="filter-chip {{ $category === 'travel' ? 'active' : '' }}">
                🗺️ Du lịch & Check-in
            </a>
            <a href="{{ route('livestream.index', ['category' => 'culture']) }}" class="filter-chip {{ $category === 'culture' ? 'active' : '' }}">
                🏺 Văn hóa & Lễ hội
            </a>
        </div>
    </div>

    <!-- Active Live Streams Section -->
    <div id="active-streams" class="live-section">
        <div class="section-header">
            <div class="section-title-box">
                <div class="live-red-indicator">
                    <span class="ping-wave"></span>
                    <span class="solid-dot"></span>
                </div>
                <h2 class="section-title">Đang phát sóng trực tiếp</h2>
                <span class="live-count-badge">{{ $activeStreams->count() }} phòng</span>
            </div>
            <a href="{{ route('livestream.create') }}" class="btn-quick-live-sm">
                + Mở phòng Live
            </a>
        </div>

        @if($activeStreams->count() > 0)
            <div class="live-grid">
                @foreach($activeStreams as $stream)
                    <div class="live-card">
                        <a href="{{ route('livestream.show', $stream->code_or_id) }}" class="live-card-thumb-link">
                            <div class="live-card-thumb">
                                @if($stream->cover_image)
                                    <img src="{{ asset($stream->cover_image) }}" alt="{{ $stream->title }}">
                                @elseif($stream->pinnedProduct && $stream->pinnedProduct->image_url)
                                    <img src="{{ asset($stream->pinnedProduct->image_url) }}" alt="{{ $stream->title }}">
                                @else
                                    <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #64748b; font-size: 2.5rem;">
                                        📹
                                    </div>
                                @endif
                                <div class="thumb-overlay"></div>
                                <div class="badge-live-tag">
                                    <span class="pulse-dot"></span> LIVE
                                </div>
                                <div class="badge-viewer-tag">
                                    👥 {{ number_format($stream->viewer_count) }}
                                </div>
                                @if($stream->pinnedProduct)
                                    <div class="badge-pinned-tag">
                                        🏷️ {{ Str::limit($stream->pinnedProduct->name, 22) }}
                                    </div>
                                @endif
                            </div>
                        </a>
                        <div class="live-card-body">
                            <div class="live-card-avatar">
                                <img src="{{ $stream->user->avatar_url ?: ('https://ui-avatars.com/api/?name=' . urlencode($stream->user->name) . '&background=0ea5e9&color=fff') }}" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($stream->user->name) }}&background=0ea5e9&color=fff';" alt="{{ $stream->user->name }}">
                            </div>
                            <div class="live-card-info">
                                <a href="{{ route('livestream.show', $stream->code_or_id) }}" class="live-card-title">
                                    {{ $stream->title }}
                                </a>
                                <div class="live-card-meta">
                                    <span class="streamer-name">{{ $stream->user->name }}</span>
                                    <span class="meta-dot">•</span>
                                    <span class="stream-likes">❤️ {{ number_format($stream->likes_count) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="live-empty-card">
                <div class="empty-icon">📡</div>
                <h3>Chưa có phiên phát trực tiếp nào đang diễn ra</h3>
                <p>Hãy là người đầu tiên mở phòng phát sóng, giới thiệu đặc sản và giao lưu cùng mọi người ngay hôm nay!</p>
                <a href="{{ route('livestream.create') }}" class="btn-empty-live">
                    🎥 Mở phòng Livestream ngay
                </a>
            </div>
        @endif
    </div>

    <!-- Past Streams / Replays -->
    @if($endedStreams->count() > 0)
        <div class="live-section past-section">
            <div class="section-header">
                <div class="section-title-box">
                    <span class="history-icon">📺</span>
                    <h2 class="section-title">Các phiên phát sóng gần đây</h2>
                </div>
            </div>
            <div class="past-live-grid">
                @foreach($endedStreams as $stream)
                    <div class="past-live-card">
                        <div class="past-thumb">
                            @if($stream->cover_image)
                                <img src="{{ asset($stream->cover_image) }}" alt="{{ $stream->title }}">
                            @elseif($stream->pinnedProduct && $stream->pinnedProduct->image_url)
                                <img src="{{ asset($stream->pinnedProduct->image_url) }}" alt="{{ $stream->title }}">
                            @else
                                <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #64748b; font-size: 1.8rem;">
                                    📺
                                </div>
                            @endif
                            <span class="past-duration-tag">⏱️ {{ $stream->duration }}</span>
                        </div>
                        <div class="past-info">
                            <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 8px;">
                                <h4 class="past-title" style="margin-bottom: 0;">{{ $stream->title }}</h4>
                                @if($currentUser && ($stream->user_id === $currentUser->id || $currentUser->role === 'admin'))
                                    <button type="button" class="btn-delete-past-live" onclick="deletePastLive({{ $stream->id }}, this)" title="Xóa phiên phát sóng này">
                                        🗑️
                                    </button>
                                @endif
                            </div>
                            <div class="past-meta">
                                <span>{{ $stream->user->name }}</span>
                                <span>•</span>
                                <span>{{ $stream->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>

<script>
function deletePastLive(streamId, btn) {
    if (!confirm('Bạn có chắc chắn muốn xóa bản ghi phiên phát trực tiếp này?')) return;
    btn.disabled = true;
    fetch(`/livestream/${streamId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            const card = btn.closest('.past-live-card');
            if (card) card.remove();
            const remaining = document.querySelectorAll('.past-live-card');
            if (remaining.length === 0) {
                const section = document.querySelector('.past-section');
                if (section) section.remove();
            }
        } else {
            alert(data.message || 'Có lỗi xảy ra.');
            btn.disabled = false;
        }
    })
    .catch(err => {
        console.error(err);
        btn.disabled = false;
    });
}
</script>


<style>
.livestream-hub-container {
    max-width: 1200px;
    margin: 24px auto 60px auto;
    padding: 0 16px;
    position: relative;
    font-family: 'Plus Jakarta Sans', 'Be Vietnam Pro', sans-serif;
}

.glow-orb {
    position: fixed;
    width: 500px;
    height: 500px;
    border-radius: 50%;
    filter: blur(140px);
    pointer-events: none;
    z-index: -1;
    opacity: 0.15;
}
.orb-1 { top: 5%; left: -5%; background: #ef4444; }
.orb-2 { top: 30%; right: -5%; background: #0ea5e9; }

/* Hero Banner */
.live-hero-card {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);
    border-radius: 28px;
    padding: 44px 48px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 32px;
    color: #ffffff;
    box-shadow: 0 20px 40px -15px rgba(15, 23, 42, 0.4);
    border: 1px solid rgba(255, 255, 255, 0.1);
    margin-bottom: 32px;
    position: relative;
    overflow: hidden;
}

.live-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(239, 68, 68, 0.18);
    border: 1px solid rgba(239, 68, 68, 0.4);
    color: #fca5a5;
    padding: 6px 14px;
    border-radius: 99px;
    font-size: 0.8rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    margin-bottom: 16px;
}

.live-pulse-dot {
    width: 8px;
    height: 8px;
    background: #ef4444;
    border-radius: 50%;
    box-shadow: 0 0 10px #ef4444;
    animation: live-blink 1.2s infinite ease-in-out;
}

@keyframes live-blink {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.4; transform: scale(1.3); }
}

.live-hero-title {
    font-size: 2.2rem;
    font-weight: 800;
    line-height: 1.25;
    margin-bottom: 12px;
    background: linear-gradient(135deg, #ffffff 0%, #cbd5e1 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.live-hero-subtitle {
    font-size: 1.05rem;
    color: #94a3b8;
    max-width: 540px;
    line-height: 1.6;
    margin-bottom: 28px;
}

.live-hero-actions {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}

.btn-go-live {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: #ffffff;
    padding: 14px 28px;
    border-radius: 16px;
    font-weight: 700;
    font-size: 1.02rem;
    text-decoration: none;
    box-shadow: 0 10px 25px -5px rgba(239, 68, 68, 0.5);
    transition: all 0.25s ease;
}
.btn-go-live:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 30px -5px rgba(239, 68, 68, 0.6);
    color: #fff;
}

.btn-explore-live {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.08);
    color: #e2e8f0;
    padding: 14px 22px;
    border-radius: 16px;
    font-weight: 600;
    text-decoration: none;
    border: 1px solid rgba(255, 255, 255, 0.12);
    transition: all 0.2s;
}
.btn-explore-live:hover {
    background: rgba(255, 255, 255, 0.15);
    color: #ffffff;
}


/* Category Filters */
.live-filter-wrapper {
    margin-bottom: 28px;
}
.live-filter-scroll {
    display: flex;
    align-items: center;
    gap: 10px;
    overflow-x: auto;
    padding-bottom: 6px;
    scrollbar-width: none;
}
.live-filter-scroll::-webkit-scrollbar { display: none; }

.filter-chip {
    padding: 9px 18px;
    border-radius: 99px;
    font-size: 0.92rem;
    font-weight: 600;
    background: #ffffff;
    color: #475569;
    border: 1px solid #e2e8f0;
    text-decoration: none;
    white-space: nowrap;
    transition: all 0.2s;
}
.filter-chip:hover {
    border-color: #cbd5e1;
    color: #0f172a;
    background: #f8fafc;
}
.filter-chip.active {
    background: #0f172a;
    color: #ffffff;
    border-color: #0f172a;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15);
}

/* Live Section */
.live-section {
    margin-bottom: 48px;
}
.section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
}
.section-title-box {
    display: flex;
    align-items: center;
    gap: 12px;
}
.live-red-indicator {
    position: relative;
    width: 14px;
    height: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.ping-wave {
    position: absolute;
    width: 100%;
    height: 100%;
    background: #ef4444;
    border-radius: 50%;
    opacity: 0.75;
    animation: ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;
}
.solid-dot {
    position: relative;
    width: 8px;
    height: 8px;
    background: #ef4444;
    border-radius: 50%;
}
@keyframes ping {
    75%, 100% { transform: scale(2.2); opacity: 0; }
}

.section-title {
    font-size: 1.45rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
}
.live-count-badge {
    background: #fee2e2;
    color: #dc2626;
    font-size: 0.78rem;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 99px;
}
.btn-quick-live-sm {
    font-size: 0.88rem;
    font-weight: 700;
    color: #ef4444;
    background: #fff1f2;
    padding: 8px 16px;
    border-radius: 12px;
    text-decoration: none;
    transition: all 0.2s;
}
.btn-quick-live-sm:hover {
    background: #ffe4e6;
}

/* Live Grid */
.live-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 22px;
}

.live-card {
    background: #ffffff;
    border-radius: 20px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
}
.live-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px -8px rgba(0, 0, 0, 0.1);
    border-color: #cbd5e1;
}

.live-card-thumb {
    position: relative;
    aspect-ratio: 16 / 10;
    background: #0f172a;
    overflow: hidden;
}
.live-card-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}
.live-card:hover .live-card-thumb img {
    transform: scale(1.05);
}
.thumb-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.65) 100%);
}

.badge-live-tag {
    position: absolute;
    top: 12px;
    left: 12px;
    background: #ef4444;
    color: #ffffff;
    font-size: 0.72rem;
    font-weight: 800;
    padding: 4px 10px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    gap: 5px;
    letter-spacing: 0.5px;
}
.pulse-dot {
    width: 6px;
    height: 6px;
    background: #fff;
    border-radius: 50%;
    animation: live-blink 1s infinite;
}
.badge-viewer-tag {
    position: absolute;
    top: 12px;
    right: 12px;
    background: rgba(15, 23, 42, 0.75);
    backdrop-filter: blur(4px);
    color: #ffffff;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 4px 8px;
    border-radius: 6px;
}
.badge-pinned-tag {
    position: absolute;
    bottom: 12px;
    left: 12px;
    right: 12px;
    background: rgba(15, 23, 42, 0.85);
    backdrop-filter: blur(6px);
    color: #fef08a;
    font-size: 0.76rem;
    font-weight: 700;
    padding: 6px 10px;
    border-radius: 8px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    border: 1px solid rgba(254, 240, 138, 0.2);
}

.live-card-body {
    padding: 16px;
    display: flex;
    gap: 12px;
    align-items: flex-start;
}
.live-card-avatar img {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #ef4444;
    padding: 2px;
}
.live-card-info {
    flex: 1;
    min-width: 0;
}
.live-card-title {
    font-size: 0.98rem;
    font-weight: 700;
    color: #0f172a;
    text-decoration: none;
    line-height: 1.35;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin-bottom: 6px;
}
.live-card-title:hover {
    color: #ef4444;
}
.live-card-meta {
    font-size: 0.82rem;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 6px;
}
.streamer-name {
    font-weight: 600;
    color: #334155;
}

/* Empty State */
.live-empty-card {
    background: #ffffff;
    border-radius: 24px;
    padding: 56px 24px;
    text-align: center;
    border: 2px dashed #cbd5e1;
}
.empty-icon {
    font-size: 3.5rem;
    margin-bottom: 16px;
}
.live-empty-card h3 {
    font-size: 1.3rem;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 8px;
}
.live-empty-card p {
    color: #64748b;
    max-width: 460px;
    margin: 0 auto 24px auto;
    line-height: 1.6;
}
.btn-empty-live {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #ef4444;
    color: #ffffff;
    padding: 12px 24px;
    border-radius: 14px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.2s;
}
.btn-empty-live:hover {
    background: #dc2626;
    color: #fff;
}

/* Past Live Section */
.past-live-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 18px;
}
.past-live-card {
    background: #ffffff;
    border-radius: 16px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
}
.past-thumb {
    position: relative;
    aspect-ratio: 16 / 10;
    background: #0f172a;
}
.past-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.past-duration-tag {
    position: absolute;
    bottom: 8px;
    right: 8px;
    background: rgba(0, 0, 0, 0.75);
    color: #fff;
    font-size: 0.72rem;
    font-weight: 600;
    padding: 3px 6px;
    border-radius: 4px;
}
.past-info {
    padding: 12px;
}
.past-title {
    font-size: 0.9rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.past-meta span {
    font-size: 0.78rem;
    color: #94a3b8;
}

.btn-delete-past-live {
    background: transparent;
    border: none;
    cursor: pointer;
    font-size: 0.95rem;
    padding: 2px 6px;
    border-radius: 6px;
    opacity: 0.6;
    transition: all 0.2s;
}
.btn-delete-past-live:hover {
    opacity: 1;
    background: #fee2e2;
    transform: scale(1.1);
}
.past-meta {
    font-size: 0.78rem;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 6px;
}

@media (max-width: 768px) {
    .live-hero-card {
        flex-direction: column;
        padding: 30px 20px;
        text-align: center;
    }
    .live-hero-title {
        font-size: 1.65rem;
    }
    .live-hero-subtitle {
        font-size: 0.95rem;
    }
    .live-hero-actions {
        justify-content: center;
        width: 100%;
    }
    .btn-go-live, .btn-explore-live {
        width: 100%;
        justify-content: center;
    }
    .live-hero-graphic {
        width: 100%;
    }
    .live-graphic-box {
        text-align: left;
    }
    .live-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection
