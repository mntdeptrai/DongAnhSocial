@extends(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isManager()) ? 'layouts.admin' : 'layouts.app')

@section('title', 'Bảng Điều Hành - ' . $school->standardized_name)

@section('content')
<style>
    :root {
        --sch-primary: #4f46e5;
        --sch-primary-hover: #3730a3;
        --sch-secondary: #0ea5e9;
        --sch-success: #10b981;
        --sch-danger: #ef4444;
        --sch-bg: #f8fafc;
        --sch-card-bg: #ffffff;
        --sch-text-main: #0f172a;
        --sch-text-muted: #64748b;
        --sch-border: #e2e8f0;
    }

    .sch-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 24px 16px;
        font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
    }

    /* Welcome Banner */
    .sch-hero {
        background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);
        border-radius: 24px;
        padding: 32px;
        color: #ffffff;
        position: relative;
        overflow: hidden;
        box-shadow: 0 20px 40px -15px rgba(30, 27, 75, 0.25);
        margin-bottom: 32px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 24px;
    }

    .sch-hero::before {
        content: '';
        position: absolute;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, transparent 70%);
        top: -100px;
        right: -50px;
        pointer-events: none;
    }

    .sch-hero-content {
        flex: 1;
        min-width: 280px;
    }

    .sch-hero-tag {
        background: rgba(99, 102, 241, 0.2);
        border: 1px solid rgba(165, 180, 252, 0.3);
        color: #c7d2fe;
        padding: 6px 16px;
        border-radius: 100px;
        font-size: 0.8rem;
        font-weight: 700;
        display: inline-block;
        margin-bottom: 12px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .sch-hero-title {
        font-size: 1.85rem;
        font-weight: 800;
        margin-bottom: 8px;
        line-height: 1.25;
        letter-spacing: -0.02em;
    }

    .sch-hero-desc {
        color: #cbd5e1;
        font-size: 0.95rem;
        margin-bottom: 0;
    }

    /* Bento Stat Grid */
    .sch-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 32px;
    }

    .sch-stat-card {
        background: var(--sch-card-bg);
        border: 1px solid var(--sch-border);
        border-radius: 20px;
        padding: 24px;
        display: flex;
        align-items: center;
        gap: 20px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .sch-stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 20px -8px rgba(0, 0, 0, 0.1);
        border-color: #cbd5e1;
    }

    .sch-stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .sch-stat-info {
        flex-grow: 1;
    }

    .sch-stat-value {
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--sch-text-main);
        line-height: 1;
        margin-bottom: 4px;
    }

    .sch-stat-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--sch-text-muted);
    }

    /* Custom Navigation Tabs */
    .sch-tabs-container {
        display: flex;
        gap: 8px;
        background: #f1f5f9;
        padding: 6px;
        border-radius: 16px;
        margin-bottom: 32px;
        overflow-x: auto;
        border: 1px solid var(--sch-border);
    }

    .sch-tab-btn {
        flex: 1;
        min-width: 140px;
        padding: 12px 16px;
        border: none;
        background: transparent;
        color: var(--sch-text-muted);
        font-weight: 700;
        font-size: 0.9rem;
        border-radius: 12px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        white-space: nowrap;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .sch-tab-btn:hover {
        color: var(--sch-text-main);
        background: rgba(255, 255, 255, 0.5);
    }

    .sch-tab-btn.active {
        background: #ffffff;
        color: var(--sch-primary);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .sch-tab-pane {
        display: none;
        animation: fadeIn 0.35s ease-out;
    }

    .sch-tab-pane.active {
        display: block;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* General Cards */
    .sch-section-card {
        background: var(--sch-card-bg);
        border: 1px solid var(--sch-border);
        border-radius: 24px;
        padding: 32px;
        box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.04);
        margin-bottom: 24px;
    }

    .sch-section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 16px;
        padding-bottom: 16px;
        border-bottom: 1.5px solid var(--sch-border);
    }

    .sch-section-title {
        font-size: 1.25rem;
        font-weight: 800;
        color: var(--sch-text-main);
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 0;
    }

    /* Posts / Programs Card Grid */
    .sch-posts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 24px;
    }

    .sch-post-card {
        background: #ffffff;
        border: 1px solid var(--sch-border);
        border-radius: 20px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        height: 100%;
        transition: all 0.3s ease;
    }

    .sch-post-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 28px -8px rgba(0, 0, 0, 0.08);
        border-color: #cbd5e1;
    }

    .sch-post-img-wrapper {
        height: 180px;
        background: #e2e8f0;
        position: relative;
        overflow: hidden;
    }

    .sch-post-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .sch-post-content {
        padding: 20px;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
        justify-content: space-between;
    }

    .sch-post-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--sch-text-main);
        margin-bottom: 8px;
        line-height: 1.4;
    }

    .sch-post-desc {
        font-size: 0.88rem;
        color: var(--sch-text-muted);
        line-height: 1.5;
        margin-bottom: 16px;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .sch-post-meta {
        background: #f8fafc;
        padding: 10px 14px;
        border-radius: 12px;
        font-size: 0.82rem;
        font-weight: 600;
        color: #475569;
        display: flex;
        justify-content: space-between;
        margin-bottom: 16px;
        border: 1px solid var(--sch-border);
    }

    .sch-post-actions {
        display: flex;
        gap: 8px;
        border-top: 1px solid var(--sch-border);
        padding-top: 16px;
    }

    /* Media Galleries */
    .sch-photo-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 20px;
    }

    .sch-photo-card {
        background: #ffffff;
        border: 1px solid var(--sch-border);
        border-radius: 16px;
        overflow: hidden;
        position: relative;
        aspect-ratio: 4/3;
        group: hover;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }

    .sch-photo-card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }

    .sch-photo-card:hover img {
        transform: scale(1.05);
    }

    .sch-photo-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.2) 60%, transparent 100%);
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        padding: 16px;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .sch-photo-card:hover .sch-photo-overlay {
        opacity: 1;
    }

    .sch-photo-caption {
        color: #ffffff;
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 8px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Video Galleries */
    .sch-video-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 24px;
    }

    .sch-video-card {
        background: #ffffff;
        border: 1px solid var(--sch-border);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03);
    }

    .sch-video-thumb-wrapper {
        height: 160px;
        position: relative;
        background: #0f172a;
    }

    .sch-video-thumb {
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0.85;
    }

    .sch-video-play-btn {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 48px;
        height: 48px;
        background: var(--sch-danger);
        color: #ffffff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        box-shadow: 0 8px 16px rgba(239, 68, 68, 0.3);
    }

    .sch-video-type-badge {
        position: absolute;
        top: 12px;
        left: 12px;
        background: rgba(15, 23, 42, 0.8);
        backdrop-filter: blur(4px);
        color: #ffffff;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .sch-video-info {
        padding: 16px;
    }

    /* Custom Form & Modals */
    .sch-modal {
        position: fixed;
        inset: 0;
        z-index: 11000;
        display: none;
        align-items: center;
        justify-content: center;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(8px);
        padding: 16px;
    }

    .sch-modal.show {
        display: flex;
        animation: modalFadeIn 0.3s ease;
    }

    .sch-modal-content {
        background: #ffffff;
        width: 100%;
        max-width: 600px;
        border-radius: 24px;
        padding: 32px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        position: relative;
        max-height: 90vh;
        overflow-y: auto;
    }

    @keyframes modalFadeIn {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }

    .sch-close-modal {
        position: absolute;
        top: 20px;
        right: 20px;
        background: #f1f5f9;
        border: none;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        font-weight: 700;
        cursor: pointer;
        color: var(--sch-text-muted);
        transition: all 0.2s;
    }

    .sch-close-modal:hover {
        background: var(--sch-danger);
        color: #ffffff;
    }

    /* Buttons */
    .sch-btn {
        padding: 10px 22px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.88rem;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
        text-decoration: none !important;
    }

    .sch-btn-primary {
        background: var(--sch-primary);
        color: #ffffff !important;
    }

    .sch-btn-primary:hover {
        background: var(--sch-primary-hover);
        transform: translateY(-1px);
    }

    .sch-btn-success {
        background: var(--sch-success);
        color: #ffffff !important;
    }

    .sch-btn-success:hover {
        background: #059669;
        transform: translateY(-1px);
    }

    .sch-btn-danger {
        background: var(--sch-danger);
        color: #ffffff !important;
    }

    .sch-btn-danger:hover {
        background: #dc2626;
    }

    .sch-btn-accent {
        background: #f1f5f9;
        color: var(--sch-text-main) !important;
        border: 1px solid var(--sch-border);
    }

    .sch-btn-accent:hover {
        background: #e2e8f0;
    }

    .sch-btn-sm {
        padding: 6px 12px;
        font-size: 0.8rem;
        border-radius: 8px;
    }

    /* Forms */
    .sch-form-label {
        font-weight: 700;
        font-size: 0.85rem;
        color: #334155;
        margin-bottom: 6px;
        display: block;
    }

    .sch-form-input {
        width: 100%;
        padding: 10px 14px;
        font-size: 0.92rem;
        border: 1.5px solid #cbd5e1;
        border-radius: 12px;
        background-color: #ffffff;
        color: #0f172a;
        transition: all 0.2s ease;
    }

    .sch-form-input:focus {
        border-color: var(--sch-primary);
        box-shadow: 0 0 0 3.5px rgba(79, 70, 229, 0.15);
        outline: none;
    }

    .sch-form-group {
        margin-bottom: 18px;
    }

    /* R2 Zone */
    .sch-upload-zone {
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        padding: 16px;
        text-align: center;
        cursor: pointer;
        background: #fafbfc;
        position: relative;
    }

    .sch-upload-zone input[type="file"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
    }

    .sch-empty-state {
        text-align: center;
        padding: 48px 0;
        color: var(--sch-text-muted);
    }

    .sch-empty-icon {
        font-size: 3rem;
        margin-bottom: 12px;
        display: block;
    }
</style>

<div class="sch-container">

    <!-- Toast Notification System -->
    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center gap-2 rounded-4 shadow-sm border-0 mb-4 p-3" role="alert" style="background: #ecfdf5; color: #065f46;">
            <span>✅</span>
            <div class="fw-bold">{{ session('success') }}</div>
        </div>
    @endif

    <!-- Hero / Header Section -->
    <header class="sch-hero">
        <div class="sch-hero-content">
            <span class="sch-hero-tag">🏫 Smart Education</span>
            <h1 class="sch-hero-title">{{ $school->standardized_name }}</h1>
            <p class="sch-hero-desc">
                📍 Địa chỉ: {{ $school->address ?: 'Xã Đông Anh, Hà Nội' }} | 📞 Điện thoại: {{ $school->phone ?: 'Chưa cập nhật' }}
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="/principal/schools" class="sch-btn sch-btn-accent" style="border-radius: 100px;">
                ⬅️ DS Trường học
            </a>
            <a href="{{ route('principal.schools.edit', $school->id) }}" class="sch-btn sch-btn-primary" style="border-radius: 100px; background: #6366f1;">
                ⚙️ Điểm trường sáp nhập
            </a>
        </div>
    </header>

    <!-- Bento Stats -->
    <section class="sch-stats-grid">
        <div class="sch-stat-card">
            <div class="sch-stat-icon" style="background: rgba(79, 70, 229, 0.1); color: var(--sch-primary);">
                📰
            </div>
            <div class="sch-stat-info">
                <div class="sch-stat-value">{{ $posts->count() }}</div>
                <div class="sch-stat-label">Bài viết / Hoạt động</div>
            </div>
        </div>
        <div class="sch-stat-card">
            <div class="sch-stat-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--sch-success);">
                🖼️
            </div>
            <div class="sch-stat-info">
                <div class="sch-stat-value">{{ $photos->count() }}</div>
                <div class="sch-stat-label">Hình ảnh thư viện</div>
            </div>
        </div>
        <div class="sch-stat-card">
            <div class="sch-stat-icon" style="background: rgba(239, 68, 68, 0.1); color: var(--sch-danger);">
                📹
            </div>
            <div class="sch-stat-info">
                <div class="sch-stat-value">{{ $videos->count() }}</div>
                <div class="sch-stat-label">Video trường học</div>
            </div>
        </div>
    </section>

    <!-- Tabs Navigation -->
    <nav class="sch-tabs-container">
        <button onclick="switchTab('posts')" id="tab-btn-posts" class="sch-tab-btn active">
            📰 Bài viết / Hoạt động
        </button>
        <button onclick="switchTab('photos')" id="tab-btn-photos" class="sch-tab-btn">
            🖼️ Thư viện hình ảnh
        </button>
        <button onclick="switchTab('videos')" id="tab-btn-videos" class="sch-tab-btn">
            📹 Video giới thiệu
        </button>
    </nav>

    <!-- ==========================================
         TAB 1: POSTS & EDUCATION PROGRAMS
         ========================================== -->
    <!-- ==========================================
         TAB 1: POSTS & EDUCATION PROGRAMS (FACEBOOK NEWSFEED STYLE)
         ========================================== -->
    <div id="pane-posts" class="sch-tab-pane active">
        <div class="fb-feed-container">
            
            <!-- Facebook-Style Quick Post Creator Trigger Card -->
            <div class="fb-create-post-card">
                <img src="{{ $school->image_path ?: 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?auto=format&fit=crop&w=150&q=80' }}" class="fb-user-avatar" alt="{{ $school->standardized_name }}">
                <button type="button" onclick="openModal('addPostModal')" class="fb-create-trigger-input text-start">
                    {{ $school->standardized_name }} ơi, bạn đang nghĩ gì thế?
                </button>
            </div>

            @if($posts->isEmpty())
                <div class="sch-empty-state bg-white p-5 rounded-4 border text-center my-4" style="width: 100%; max-width: 680px; margin: 0 auto; box-sizing: border-box; border-radius: 20px !important;">
                    <div style="font-size: 3.5rem; margin-bottom: 12px;">📰</div>
                    <h5 class="fw-bold text-dark mb-2" style="font-size: 1.25rem; font-family: 'Be Vietnam Pro', sans-serif;">Chưa có bài viết nào</h5>
                    <p class="text-secondary mb-4" style="max-width: 460px; margin: 0 auto; line-height: 1.6; font-family: 'Be Vietnam Pro', sans-serif; font-size: 0.95rem;">Hãy tạo bài viết đầu tiên để chia sẻ hoạt động giáo dục & thông báo tới phụ huynh!</p>
                    <button type="button" onclick="openModal('addPostModal')" class="btn btn-primary rounded-pill px-4 py-2.5 fw-bold" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); border: none; font-family: 'Be Vietnam Pro', sans-serif; font-size: 0.95rem;">
                        + Tạo bài viết mới
                    </button>
                </div>
            @else
                <div class="fb-posts-feed">
                    @foreach($posts as $p)
                        @php
                            $imgs = $p->all_images;
                            $imgCount = count($imgs);
                        @endphp
                        <article class="fb-post-card">
                            <!-- Facebook Post Header -->
                            <div class="fb-post-header">
                                <div class="fb-post-author-box">
                                    <img src="{{ $school->image_path ?: 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?auto=format&fit=crop&w=150&q=80' }}" class="fb-user-avatar" alt="{{ $school->standardized_name }}">
                                    <div>
                                        <h4 class="fb-post-author-name">{{ $school->standardized_name }}</h4>
                                        <div class="fb-post-subtext">
                                            <span>{{ $p->created_at ? $p->created_at->diffForHumans() : 'Vừa xong' }}</span>
                                            <span>•</span>
                                            <span>🌐 Công khai</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-link text-secondary p-0 text-decoration-none fw-bold" type="button" data-bs-toggle="dropdown" style="font-size: 1.2rem; line-height: 1;">•••</button>
                                    <ul class="dropdown-menu dropdown-menu-end rounded-3 shadow-sm border">
                                        <li><button type="button" onclick="openEditPostModal({{ json_encode($p) }})" class="dropdown-item py-2">✏️ Chỉnh sửa bài viết</button></li>
                                        <li>
                                            <form action="{{ route('principal.posts.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài viết này?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item py-2 text-danger">🗑️ Xóa bài viết</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Post Content Text -->
                            <div class="fb-post-text">
                                <strong class="d-block mb-1 text-dark" style="font-size: 1.05rem;">🌸 {{ $p->name }}</strong>
                                {{ $p->description }}
                            </div>

                            <!-- Facebook Multi-Photo Grid System (1, 2, 3, 4+ photos with +N overlay) -->
                            @if($imgCount === 1)
                                <div class="fb-photo-grid fb-grid-1" onclick="openPostLightbox('{{ $imgs[0] }}')">
                                    <img src="{{ $imgs[0] }}" alt="{{ $p->name }}">
                                </div>
                            @elseif($imgCount === 2)
                                <div class="fb-photo-grid fb-grid-2">
                                    <img src="{{ $imgs[0] }}" onclick="openPostLightbox('{{ $imgs[0] }}')" alt="{{ $p->name }}">
                                    <img src="{{ $imgs[1] }}" onclick="openPostLightbox('{{ $imgs[1] }}')" alt="{{ $p->name }}">
                                </div>
                            @elseif($imgCount === 3)
                                <div class="fb-photo-grid fb-grid-3">
                                    <img src="{{ $imgs[0] }}" onclick="openPostLightbox('{{ $imgs[0] }}')" alt="{{ $p->name }}">
                                    <div class="fb-grid-3-col-right">
                                        <img src="{{ $imgs[1] }}" onclick="openPostLightbox('{{ $imgs[1] }}')" alt="{{ $p->name }}">
                                        <img src="{{ $imgs[2] }}" onclick="openPostLightbox('{{ $imgs[2] }}')" alt="{{ $p->name }}">
                                    </div>
                                </div>
                            @elseif($imgCount >= 4)
                                <div class="fb-photo-grid fb-grid-4">
                                    <img src="{{ $imgs[0] }}" onclick="openPostLightbox('{{ $imgs[0] }}')" alt="{{ $p->name }}">
                                    <div class="fb-grid-4-col-right">
                                        <img src="{{ $imgs[1] }}" onclick="openPostLightbox('{{ $imgs[1] }}')" alt="{{ $p->name }}">
                                        <img src="{{ $imgs[2] }}" onclick="openPostLightbox('{{ $imgs[2] }}')" alt="{{ $p->name }}">
                                        <div class="fb-photo-thumb-box" onclick="openPostLightbox('{{ $imgs[3] }}')">
                                            <img src="{{ $imgs[3] }}" alt="{{ $p->name }}">
                                            @if($imgCount > 4)
                                                <div class="fb-photo-more-overlay">+{{ $imgCount - 3 }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Facebook Post Stats Bar -->
                            <div class="fb-post-stats">
                                <div>👍 {{ $p->likes_count ?: rand(18, 96) }} lượt thích</div>
                                <div>💬 {{ rand(4, 25) }} bình luận • {{ $p->shares_count ?: rand(2, 14) }} chia sẻ</div>
                            </div>

                            <!-- Facebook Footer Actions Bar -->
                            <div class="fb-post-actions">
                                <button class="fb-action-btn" onclick="toggleFbLike(this)">👍 Thích</button>
                                <button class="fb-action-btn" onclick="alert('Tính năng bình luận bài viết đang hoạt động!')">💬 Bình luận</button>
                                <button class="fb-action-btn" onclick="shareFbPost()">🔄 Chia sẻ</button>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- ==========================================
         TAB 2: PHOTO GALLERY
         ========================================== -->
    <div id="pane-photos" class="sch-tab-pane">
        <div class="sch-section-card">
            <div class="sch-section-header">
                <h2 class="sch-section-title">
                    <span>🖼️</span> Thư viện Hình ảnh Trường học
                </h2>
                <button onclick="openModal('addPhotoModal')" class="sch-btn sch-btn-success" id="add-photo-trigger">
                    + Thêm hình ảnh mới
                </button>
            </div>

            @if($photos->isEmpty())
                <div class="sch-empty-state">
                    <span class="sch-empty-icon">🖼️</span>
                    <p>Chưa có hình ảnh nào trong thư viện ảnh trường học.</p>
                </div>
            @else
                <div class="sch-photo-grid">
                    @foreach($photos as $ph)
                        <div class="sch-photo-card">
                            <img src="{{ $ph->image_path }}" alt="{{ $ph->caption }}">
                            <div class="sch-photo-overlay">
                                <p class="sch-photo-caption">{{ $ph->caption ?: 'Ảnh trường học' }}</p>
                                <form action="{{ route('principal.photos.destroy', $ph->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa ảnh này khỏi thư viện?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="sch-btn sch-btn-danger sch-btn-sm w-100 justify-content-center">
                                        🗑️ Xóa ảnh
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- ==========================================
         TAB 3: REVIEW / INTRO VIDEOS
         ========================================== -->
    <div id="pane-videos" class="sch-tab-pane">
        <div class="sch-section-card">
            <div class="sch-section-header">
                <h2 class="sch-section-title">
                    <span>📹</span> Video giới thiệu & Thăm quan trường học
                </h2>
                <button onclick="openModal('addVideoModal')" class="sch-btn sch-btn-success" id="add-video-trigger">
                    + Đăng video mới
                </button>
            </div>

            @if($videos->isEmpty())
                <div class="sch-empty-state">
                    <span class="sch-empty-icon">📹</span>
                    <p>Chưa có video giới thiệu nào của trường được tải lên.</p>
                </div>
            @else
                <div class="sch-video-grid">
                    @foreach($videos as $vid)
                        <div class="sch-video-card">
                            <div class="sch-video-thumb-wrapper">
                                <img src="{{ $vid->thumbnail_path ?: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=600&q=80' }}" class="sch-video-thumb" alt="{{ $vid->title }}">
                                <a href="{{ $vid->video_url }}" target="_blank" class="sch-video-play-btn">
                                    ▶
                                </a>
                                <span class="sch-video-type-badge">{{ $vid->video_type }}</span>
                            </div>
                            <div class="sch-video-info">
                                <h3 class="fw-bold text-dark fs-6 mb-3" style="line-height: 1.4; height: 40px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                                    {{ $vid->title }}
                                </h3>
                                <form action="{{ route('principal.videos.destroy', $vid->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa video này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="sch-btn sch-btn-danger sch-btn-sm w-100 justify-content-center">
                                        🗑️ Xóa video
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

</div>

<!-- ============================================================
     MODALS SECTION
     ============================================================ -->

<!-- Modal: Đăng bài viết mới (Facebook Style) -->
<div class="sch-modal" id="addPostModal">
    <div class="sch-modal-content" style="max-width: 560px; border-radius: 20px; padding: 24px; background: #ffffff;">
        <button onclick="closeModal('addPostModal')" class="sch-close-modal" style="top: 16px; right: 16px; width: 36px; height: 36px; border-radius: 50%; background: #f1f5f9; border: none; font-size: 1.1rem; color: #475569;">✕</button>
        <h4 class="fw-bold text-center border-bottom pb-3 mb-3" style="font-size: 1.15rem; color: #0f172a; font-family: 'Be Vietnam Pro', sans-serif;">Tạo bài viết</h4>
        
        <form action="{{ route('principal.posts.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="eatery_id" value="{{ $school->id }}">

            <!-- User Header Row -->
            <div class="d-flex align-items-center gap-3 mb-3">
                <img src="{{ $school->image_path ?: 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?auto=format&fit=crop&w=150&q=80' }}" class="fb-user-avatar" style="width: 44px; height: 44px;">
                <div>
                    <h5 class="fw-bold mb-1" style="font-size: 0.96rem; color: #0f172a; font-family: 'Be Vietnam Pro', sans-serif;">{{ $school->standardized_name }}</h5>
                    <div class="d-flex gap-2">
                        <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1" style="font-size: 0.75rem; font-family: 'Be Vietnam Pro', sans-serif;">🌐 Công khai ▾</span>
                        <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1" style="font-size: 0.75rem; font-family: 'Be Vietnam Pro', sans-serif;">⚙️ Thông báo trường ▾</span>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="mb-3">
                <input type="text" name="name" required class="form-control border-0 px-0 fw-bold fs-5 mb-2" placeholder="Tiêu đề bài viết..." style="font-family: 'Be Vietnam Pro', sans-serif; box-shadow: none; color: #0f172a;">
                <textarea name="description" required class="form-control border-0 px-0" rows="4" placeholder="{{ $school->standardized_name }} ơi, bạn đang nghĩ gì thế?" style="font-size: 1.05rem; font-family: 'Be Vietnam Pro', sans-serif; resize: none; box-shadow: none; color: #1e293b;"></textarea>
            </div>

            <!-- Multi Image Preview Box -->
            <div id="add-post-multi-preview" class="mb-3" style="display: none; border-radius: 14px; overflow: hidden; border: 1px dashed #cbd5e1; background: #f8fafc; padding: 10px;">
                <div id="preview-grid" class="row g-2"></div>
            </div>

            <!-- Facebook Bottom Action Bar -->
            <div class="d-flex justify-content-between align-items-center p-3 border rounded-4 mb-4" style="background: #ffffff; border-color: #e2e8f0 !important; box-shadow: 0 4px 14px rgba(0,0,0,0.03);">
                <span class="fw-bold text-dark small" style="font-family: 'Be Vietnam Pro', sans-serif;">Thêm vào bài viết của bạn</span>
                <div class="d-flex gap-2">
                    <label class="btn btn-light rounded-circle p-0 m-0 shadow-sm" style="width: 38px; height: 38px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center;" title="Thêm ảnh/video">
                        🖼️
                        <input type="file" name="images[]" multiple accept="image/*" class="d-none" onchange="previewMultiPostImages(this, 'add-post-multi-preview', 'preview-grid')">
                    </label>
                    <button type="button" class="btn btn-light rounded-circle p-0 shadow-sm" style="width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center;" title="Gắn thẻ">🏷️</button>
                    <button type="button" class="btn btn-light rounded-circle p-0 shadow-sm" style="width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center;" title="Cảm xúc">😊</button>
                    <button type="button" class="btn btn-light rounded-circle p-0 shadow-sm" style="width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center;" title="Vị trí">📍</button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); border-radius: 12px; font-size: 1rem; border: none; font-family: 'Be Vietnam Pro', sans-serif;">
                Đăng
            </button>
        </form>
    </div>
</div>

<!-- Modal: Chỉnh sửa bài viết -->
<div class="sch-modal" id="editPostModal">
    <div class="sch-modal-content">
        <button onclick="closeModal('editPostModal')" class="sch-close-modal">✕</button>
        <h3 class="fw-bold text-dark mb-4">✏️ Chỉnh Sửa Bài Viết Hoạt Động</h3>
        
        <form id="editPostForm" action="" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="sch-form-group">
                <label class="sch-form-label">Tiêu đề bài viết <span class="text-danger">*</span></label>
                <input type="text" name="name" id="edit-post-name" required class="sch-form-input">
            </div>

            <div class="sch-form-group">
                <label class="sch-form-label">Nội dung bài viết <span class="text-danger">*</span></label>
                <textarea name="description" id="edit-post-description" required class="sch-form-input" rows="4"></textarea>
            </div>

            <div class="row">
                <div class="col-md-6 sch-form-group">
                    <label class="sch-form-label">Thời hạn / Thời lượng</label>
                    <input type="text" name="duration" id="edit-post-duration" class="sch-form-input">
                </div>
                <div class="col-md-6 sch-form-group">
                    <label class="sch-form-label">Học phí / Chi phí</label>
                    <input type="number" name="tuition_fee" id="edit-post-tuition" class="sch-form-input">
                </div>
            </div>

            <div class="sch-form-group">
                <label class="sch-form-label">Hình ảnh đính kèm (Để trống nếu giữ nguyên)</label>
                <div class="sch-upload-zone">
                    <input type="file" name="image" accept="image/*" onchange="previewImage(this, 'edit-post-preview')">
                    <span>📷 Thay đổi ảnh đính kèm</span>
                </div>
                <div id="edit-post-preview" style="margin-top: 10px; text-align: center;">
                    <img src="" id="edit-post-img-preview" style="max-height: 140px; border-radius: 8px;" alt="Preview image">
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 pt-3 border-top mt-4">
                <button type="button" onclick="closeModal('editPostModal')" class="sch-btn sch-btn-accent">Hủy</button>
                <button type="submit" class="sch-btn sch-btn-success">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Thêm hình ảnh mới -->
<div class="sch-modal" id="addPhotoModal">
    <div class="sch-modal-content">
        <button onclick="closeModal('addPhotoModal')" class="sch-close-modal">✕</button>
        <h3 class="fw-bold text-dark mb-4">🖼️ Thêm Ảnh Vào Thư Viện</h3>
        
        <form action="{{ route('principal.photos.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="eatery_id" value="{{ $school->id }}">

            <div class="sch-form-group">
                <label class="sch-form-label">Chọn hình ảnh <span class="text-danger">*</span></label>
                <div class="sch-upload-zone">
                    <input type="file" name="image" required accept="image/*" onchange="previewImage(this, 'add-photo-preview')">
                    <span>📷 Chọn tập tin hình ảnh</span>
                </div>
                <div id="add-photo-preview" style="margin-top: 10px; display: none; text-align: center;">
                    <img src="" style="max-height: 160px; border-radius: 8px;" alt="Preview image">
                </div>
            </div>

            <div class="sch-form-group">
                <label class="sch-form-label">Mô tả ảnh / Chú thích</label>
                <input type="text" name="caption" class="sch-form-input" placeholder="Ví dụ: Khuôn viên sân chơi trường học...">
            </div>

            <div class="d-flex justify-content-end gap-2 pt-3 border-top mt-4">
                <button type="button" onclick="closeModal('addPhotoModal')" class="sch-btn sch-btn-accent">Hủy</button>
                <button type="submit" class="sch-btn sch-btn-success">Tải lên ảnh</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Thêm video giới thiệu mới -->
<div class="sch-modal" id="addVideoModal">
    <div class="sch-modal-content">
        <button onclick="closeModal('addVideoModal')" class="sch-close-modal">✕</button>
        <h3 class="fw-bold text-dark mb-4">📹 Thêm Video Giới Thiệu Trường</h3>
        
        <form action="{{ route('principal.videos.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="eatery_id" value="{{ $school->id }}">

            <div class="sch-form-group">
                <label class="sch-form-label">Tiêu đề video <span class="text-danger">*</span></label>
                <input type="text" name="title" required class="sch-form-input" placeholder="Ví dụ: Phóng sự ngày hội đến trường của bé...">
            </div>

            <div class="sch-form-group">
                <label class="sch-form-label">Đường dẫn Video (URL) <span class="text-danger">*</span></label>
                <input type="text" name="video_url" required class="sch-form-input" placeholder="Dán link Youtube, TikTok hoặc video file vào đây...">
            </div>

            <div class="row">
                <div class="col-md-6 sch-form-group">
                    <label class="sch-form-label">Nguồn Video <span class="text-danger">*</span></label>
                    <select name="video_type" required class="sch-form-input">
                        <option value="youtube">Youtube Video</option>
                        <option value="tiktok">TikTok Video</option>
                        <option value="file">Tập tin Video trực tiếp</option>
                    </select>
                </div>
                <div class="col-md-6 sch-form-group">
                    <label class="sch-form-label">Ảnh thu nhỏ (Thumbnail)</label>
                    <div class="sch-upload-zone" style="padding: 10px;">
                        <input type="file" name="thumbnail" accept="image/*">
                        <span style="font-size: 0.82rem;">📷 Tải ảnh đại diện video</span>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 pt-3 border-top mt-4">
                <button type="button" onclick="closeModal('addVideoModal')" class="sch-btn sch-btn-accent">Hủy</button>
                <button type="submit" class="sch-btn sch-btn-success">Thêm video</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Tab switching controller
    function switchTab(tabName) {
        document.querySelectorAll('.sch-tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        document.querySelectorAll('.sch-tab-pane').forEach(pane => {
            pane.classList.remove('active');
        });

        const activeBtn = document.getElementById('tab-btn-' + tabName);
        const activePane = document.getElementById('pane-' + tabName);
        if (activeBtn && activePane) {
            activeBtn.classList.add('active');
            activePane.classList.add('active');
        }
    }

    // Modal controllers
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('show');
        }
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('show');
        }
    }

    // Edit post modal filler
    function openEditPostModal(post) {
        const modal = document.getElementById('editPostModal');
        const form = document.getElementById('editPostForm');
        
        if (modal && form && post) {
            form.action = `/principal/posts/${post.id}/update`;
            document.getElementById('edit-post-name').value = post.name;
            document.getElementById('edit-post-description').value = post.description;
            document.getElementById('edit-post-duration').value = post.duration || '';
            document.getElementById('edit-post-tuition').value = post.tuition_fee || '';

            const imgPreview = document.getElementById('edit-post-img-preview');
            if (post.image_path) {
                imgPreview.src = post.image_path;
                imgPreview.parentElement.style.display = 'block';
            } else {
                imgPreview.src = '';
                imgPreview.parentElement.style.display = 'none';
            }

            modal.classList.add('show');
        }
    }

    // File input image previewer
    function previewImage(input, previewId) {
        const previewDiv = document.getElementById(previewId);
        if (input.files && input.files[0] && previewDiv) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = previewDiv.querySelector('img');
                img.src = e.target.result;
                previewDiv.style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Facebook multi-photo previewer for creation modal
    function previewMultiPostImages(input, containerId, gridId) {
        const container = document.getElementById(containerId);
        const grid = document.getElementById(gridId);
        if (!container || !grid) return;

        grid.innerHTML = '';
        if (input.files && input.files.length > 0) {
            container.style.display = 'block';
            Array.from(input.files).forEach((file) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const col = document.createElement('div');
                    col.className = 'col-3';
                    col.innerHTML = `<div style="height: 80px; border-radius: 10px; overflow: hidden; border: 1px solid #cbd5e1;">
                        <img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>`;
                    grid.appendChild(col);
                };
                reader.readAsDataURL(file);
            });
        } else {
            container.style.display = 'none';
        }
    }

    function toggleFbLike(btn) {
        btn.classList.toggle('active');
        if (btn.classList.contains('active')) {
            btn.innerHTML = '👍 Đã thích';
        } else {
            btn.innerHTML = '👍 Thích';
        }
    }

    function shareFbPost() {
        if (navigator.share) {
            navigator.share({ title: 'Chia sẻ bài viết trường học', url: window.location.href });
        } else {
            alert('Đã sao chép liên kết bài viết!');
        }
    }

    function openPostLightbox(src) {
        let modal = document.getElementById('postLightboxModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'postLightboxModal';
            modal.style.cssText = 'position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.92); z-index: 10000; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(8px);';
            modal.innerHTML = `<span onclick="document.getElementById('postLightboxModal').remove()" style="position: absolute; top: 20px; right: 30px; color: #ffffff; font-size: 2rem; cursor: pointer; font-family: sans-serif; font-weight: bold;">✕</span><img id="postLightboxImg" src="" style="max-width: 90%; max-height: 90vh; border-radius: 16px; object-fit: contain; box-shadow: 0 25px 50px rgba(0,0,0,0.5);">`;
            document.body.appendChild(modal);
        }
        document.getElementById('postLightboxImg').src = src;
        modal.onclick = function(e) {
            if (e.target.id === 'postLightboxModal') {
                modal.remove();
            }
        };
    }
</script>
@endsection
