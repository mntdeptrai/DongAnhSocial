@extends('layouts.app')

@section('title', 'Bản tin - DongAnh Map Discovery')
@section('meta_description', 'Bản tin cập nhật các bài viết mới nhất từ các Trường học, Profile cá nhân và Gian hàng Đông Anh.')

@section('content')
<!-- Glowing background orbs for cinematic atmosphere -->
<div style="position: fixed; top: 10%; left: -10%; width: 500px; height: 500px; background: radial-gradient(circle, rgba(14, 165, 233, 0.05) 0%, rgba(14, 165, 233, 0) 70%); filter: blur(100px); pointer-events: none; z-index: 1;"></div>
<div style="position: fixed; bottom: 10%; right: -10%; width: 500px; height: 500px; background: radial-gradient(circle, rgba(16, 185, 129, 0.05) 0%, rgba(16, 185, 129, 0) 70%); filter: blur(100px); pointer-events: none; z-index: 1;"></div>

<div class="newsfeed-container" style="max-width: 680px; margin: 24px auto 40px auto; padding: 0 16px; position: relative; z-index: 2; box-sizing: border-box;">
    
    <!-- STREAM OF POSTS CONTAINER -->
    <div style="display: flex; flex-direction: column; gap: 20px; width: 100%;">
        
        <!-- Facebook-Style Post Creator Card (giống trang Profile) -->
        @if(auth()->check())
            @php $authUser = auth()->user(); @endphp
            <div class="pro-creator-card" style="background: #ffffff; border-radius: 20px; padding: 20px; border: 1px solid #e2e8f0; box-shadow: 0 4px 15px rgba(15,23,42,0.03);">
                <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 16px;">
                    @if($authUser->avatar && str_starts_with($authUser->avatar, 'avatars/'))
                        <img src="{{ rtrim(env('R2_PUBLIC_URL'), '/') . '/' . $authUser->avatar }}" style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover;" alt="Avatar">
                    @else
                        <div style="width: 44px; height: 44px; border-radius: 50%; background: linear-gradient(135deg, #0284c7, #0369a1); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem; flex-shrink: 0;">
                            {{ mb_substr($authUser->name, 0, 1, 'UTF-8') }}
                        </div>
                    @endif
                    <button type="button" onclick="openNewsfeedPostModal()" style="flex: 1; background: #f1f5f9; border: none; border-radius: 100px; padding: 12px 20px; color: #64748b; font-size: 0.9rem; font-weight: 500; cursor: pointer; text-align: left; font-family: inherit;">
                        Chia sẻ điều gì đó...
                    </button>
                </div>
                <div style="display: flex; align-items: center; justify-content: space-around; border-top: 1px solid #f1f5f9; padding-top: 14px;">
                    <button type="button" onclick="openNewsfeedPostModal()" style="background: transparent; border: none; color: #475569; font-size: 0.85rem; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; padding: 6px 12px; border-radius: 8px; font-family: inherit;">🖼️ Ảnh & Video</button>
                    <button type="button" onclick="openNewsfeedPostModal()" style="background: transparent; border: none; color: #475569; font-size: 0.85rem; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; padding: 6px 12px; border-radius: 8px; font-family: inherit;">😃 Cảm xúc</button>
                    <button type="button" onclick="openNewsfeedPostModal()" style="background: transparent; border: none; color: #475569; font-size: 0.85rem; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; padding: 6px 12px; border-radius: 8px; font-family: inherit;">🎈 Check-in</button>
                    <button type="button" onclick="openNewsfeedPostModal()" style="background: transparent; border: none; color: #475569; font-size: 0.85rem; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; padding: 6px 12px; border-radius: 8px; font-family: inherit;">📅 Sự kiện</button>
                </div>
            </div>
        @else
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 20px; padding: 18px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 12px;">
                <div style="width: 44px; height: 44px; border-radius: 50%; background: linear-gradient(135deg, #0284c7, #0369a1); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem; flex-shrink: 0;">👤</div>
                <a href="/auth/login" style="flex: 1; background: #f1f5f9; border-radius: 100px; padding: 12px 20px; color: #64748b; font-size: 0.9rem; text-decoration: none;">Đăng nhập để chia sẻ bài viết lên Bản tin...</a>
            </div>
        @endif

        <!-- Flash Success Message -->
        @if(session('success'))
            <div style="background: linear-gradient(135deg, rgba(16,185,129,0.12), rgba(6,182,212,0.08)); border: 1.5px solid rgba(16,185,129,0.35); border-radius: 14px; padding: 14px 20px; display: flex; align-items: center; gap: 12px; font-weight: 600; color: #059669; font-size: 0.92rem;">
                <span style="font-size: 1.3rem;">✅</span>
                {{ session('success') }}
            </div>
        @endif

        <!-- Stream of Posts (Facebook Card Design) -->
        @if(isset($posts) && $posts->isNotEmpty())
            @foreach($posts as $p)
                @php
                    $authorName = 'Thành viên Đông Anh';
                    $profileUrl = '#';
                    $authorAvatar = null;
                    $roleBadge = 'Thành viên';
                    $roleClass = 'role-user';

                    if ($p instanceof \App\Models\EducationProgram) {
                        $authorName = $p->eatery ? ($p->eatery->standardized_name ?: $p->eatery->name) : 'Trường học Đông Anh';
                        $authorSlug = $p->eatery ? $p->eatery->slug : '';
                        $profileUrl = $authorSlug ? "/dia-diem/{$authorSlug}" : '#';
                        $authorAvatar = $p->eatery ? $p->eatery->image_path : null;
                        $roleBadge = '🏫 Trường học';
                        $roleClass = 'role-school';
                    } elseif ($p->user) {
                        $authorName = $p->user->name;
                        $authorSlug = \Illuminate\Support\Str::slug($p->user->name);
                        $profileUrl = "/profile/{$authorSlug}";
                        $authorAvatar = $p->user->avatar ?? null;
                        $roleBadge = $p->user->role === 'admin' ? 'Quản trị viên' : 'Thành viên';
                        $roleClass = $p->user->role === 'admin' ? 'role-admin' : 'role-user';
                    } elseif ($p->eatery) {
                        $authorName = $p->eatery->name;
                        $authorSlug = $p->eatery->slug;
                        $profileUrl = "/dia-diem/{$authorSlug}";
                        $authorAvatar = $p->eatery->image_path;
                        $roleBadge = '🏪 Gian hàng';
                        $roleClass = 'role-store';
                    } elseif (!empty($p->display_name)) {
                        $authorName = $p->display_name;
                    }

                    $imgs = method_exists($p, 'getAllImagesAttribute') ? $p->all_images : ($p->image_path ? [$p->image_path] : []);
                    $imgCount = count($imgs);
                @endphp

                <article class="fb-post-card mb-4" id="post-{{ $p->id }}" style="background: #ffffff; border-radius: 20px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.05); width: 100%; max-width: 100%; box-sizing: border-box; transition: box-shadow 0.4s ease, border-color 0.4s ease;">
                    
                    <!-- Facebook Post Header -->
                    <div class="fb-post-header" style="padding: 16px; box-sizing: border-box; width: 100%;">
                        <div class="fb-post-author-box" style="display: flex; align-items: center; gap: 12px;">
                            <a href="{{ $profileUrl }}" style="text-decoration: none; display: flex; align-items: center; gap: 12px; color: inherit;">
                                @if($authorAvatar)
                                    <img src="{{ $authorAvatar }}" class="fb-user-avatar" alt="{{ $authorName }}" style="width: 42px; height: 42px; border-radius: 50%; object-fit: cover; border: 1px solid #cbd5e1;">
                                @else
                                    <div class="fb-user-avatar" style="width: 42px; height: 42px; border-radius: 50%; background: linear-gradient(135deg, #0284c7, #0369a1); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem; flex-shrink: 0;">
                                        {{ mb_substr($authorName, 0, 1, 'UTF-8') }}
                                    </div>
                                @endif
                                <div>
                                    <h4 class="fb-post-author-name" style="margin: 0; font-size: 0.98rem; font-weight: 800; color: #0f172a; display: inline-flex; align-items: center; gap: 6px;">
                                        {{ $authorName }}
                                        @if($p->user && $p->user->role === 'admin')
                                            <span title="Tài khoản Quản trị viên (Admin)" style="color: #ef4444; font-size: 0.95rem;">⭐</span>
                                        @endif
                                    </h4>
                                    <div class="fb-post-subtext" style="font-size: 0.78rem; color: #64748b; margin-top: 2px; display: flex; align-items: center; gap: 6px;">
                                        <span>{{ $p->created_at ? $p->created_at->diffForHumans() : 'Vừa xong' }}</span>
                                        <span>•</span>
                                        <span>🌐 Công khai</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- Post Content Text -->
                    <div class="fb-post-text" style="padding: 0 16px 14px 16px; word-break: break-word; overflow-wrap: anywhere; max-width: 100%; box-sizing: border-box;">
                        @if($p->name)
                            <strong class="d-block mb-1 text-dark" style="font-size: 1.05rem; word-break: break-word; overflow-wrap: anywhere; line-height: 1.45; color: #0f172a; display: block; margin-bottom: 6px;">🌸 {{ $p->name }}</strong>
                        @endif
                        @if($p->description)
                            <div style="word-break: break-word; overflow-wrap: anywhere; line-height: 1.6; color: #334155;">{!! \App\Helpers\TextHelper::linkify($p->description) !!}</div>
                        @endif
                    </div>

                    <!-- Facebook Multi-Photo Grid System (1, 2, 3, 4, 5+ photos) -->
                    @if($imgCount === 1)
                        <div class="fb-photo-grid fb-grid-1" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 0)">
                            <img src="{{ $imgs[0] }}" alt="{{ $p->name ?? 'Ảnh' }}">
                        </div>
                    @elseif($imgCount === 2)
                        <div class="fb-photo-grid fb-grid-2">
                            <img src="{{ $imgs[0] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 0)" alt="{{ $p->name ?? 'Ảnh' }}">
                            <img src="{{ $imgs[1] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 1)" alt="{{ $p->name ?? 'Ảnh' }}">
                        </div>
                    @elseif($imgCount === 3)
                        <div class="fb-photo-grid fb-grid-3">
                            <img src="{{ $imgs[0] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 0)" alt="{{ $p->name ?? 'Ảnh' }}">
                            <div class="fb-grid-3-col-right">
                                <img src="{{ $imgs[1] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 1)" alt="{{ $p->name ?? 'Ảnh' }}">
                                <img src="{{ $imgs[2] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 2)" alt="{{ $p->name ?? 'Ảnh' }}">
                            </div>
                        </div>
                    @elseif($imgCount === 4)
                        <div class="fb-photo-grid fb-grid-4">
                            <img src="{{ $imgs[0] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 0)" alt="{{ $p->name ?? 'Ảnh' }}">
                            <div class="fb-grid-4-col-right">
                                <img src="{{ $imgs[1] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 1)" alt="{{ $p->name ?? 'Ảnh' }}">
                                <img src="{{ $imgs[2] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 2)" alt="{{ $p->name ?? 'Ảnh' }}">
                                <img src="{{ $imgs[3] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 3)" alt="{{ $p->name ?? 'Ảnh' }}">
                            </div>
                        </div>
                    @elseif($imgCount >= 5)
                        <div class="fb-photo-grid fb-grid-5">
                            <div class="fb-grid-5-row-top">
                                <img src="{{ $imgs[0] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 0)" alt="{{ $p->name ?? 'Ảnh' }}">
                                <img src="{{ $imgs[1] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 1)" alt="{{ $p->name ?? 'Ảnh' }}">
                            </div>
                            <div class="fb-grid-5-row-bottom">
                                <img src="{{ $imgs[2] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 2)" alt="{{ $p->name ?? 'Ảnh' }}">
                                <img src="{{ $imgs[3] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 3)" alt="{{ $p->name ?? 'Ảnh' }}">
                                <div class="fb-photo-thumb-box" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 4)">
                                    <img src="{{ $imgs[4] }}" alt="{{ $p->name ?? 'Ảnh' }}">
                                    @if($imgCount > 5)
                                        <div class="fb-photo-more-overlay">+{{ $imgCount - 5 }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Video Player Section if post contains videos -->
                    @php
                        $vids = method_exists($p, 'getAllVideosAttribute') ? $p->all_videos : ($p->video_path ? [$p->video_path] : []);
                    @endphp
                    @if(!empty($vids))
                        <div class="fb-post-video-container" style="width: 100%; border-radius: 16px; overflow: hidden; margin-top: 10px; background: #000; display: flex; flex-direction: column; gap: 8px;">
                            @foreach($vids as $vidUrl)
                                <video src="{{ $vidUrl }}" controls preload="metadata" style="width: 100%; max-height: 480px; display: block; border-radius: 12px; background: #0f172a;"></video>
                            @endforeach
                        </div>
                    @endif

                    <!-- Facebook Post Stats Bar -->
                    <div class="fb-post-stats" style="padding: 10px 16px; border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; font-size: 0.84rem; color: #64748b;">
                        <div id="post-likes-count-{{ $p->id }}" onclick="showPostLikers({{ $p->id }}, 'post')" style="cursor:pointer;" title="Xem ai đã thích">👍 {{ $p->reaction_total ?? $p->likes_count ?? 0 }} lượt thích</div>
                        <div>💬 {{ $p->comments ? $p->comments->count() : 0 }} bình luận • 0 chia sẻ</div>
                    </div>

                    <!-- Facebook Footer Actions Bar -->
                    <div class="fb-post-actions" style="display: flex; justify-content: space-around; padding: 6px 0; background: #fafafa; border-top: 1px solid #f1f5f9;">
                        <button class="fb-action-btn {{ ($p->is_liked ?? false) ? 'active' : '' }}" 
                                id="post-like-btn-{{ $p->id }}" 
                                onclick="togglePostLike(this, {{ $p->id }})"
                                style="border: none; background: transparent; padding: 8px 16px; font-weight: 700; color: {{ ($p->is_liked ?? false) ? '#2563eb' : '#64748b' }}; cursor: pointer; display: flex; align-items: center; gap: 6px; font-size: 0.88rem;">
                            👍 {{ ($p->is_liked ?? false) ? 'Đã thích' : 'Thích' }}
                        </button>
                        <button class="fb-action-btn" onclick="toggleComments('{{ $p->id }}', this)" style="border: none; background: transparent; padding: 8px 16px; font-weight: 700; color: #64748b; cursor: pointer; display: flex; align-items: center; gap: 6px; font-size: 0.88rem;">
                            💬 Bình luận
                        </button>
                        <button class="fb-action-btn" onclick="shareFbPost({{ $p->id }}, {{ json_encode($p->name ?? '') }}, {{ json_encode($imgs) }})" style="border: none; background: transparent; padding: 8px 16px; font-weight: 700; color: #64748b; cursor: pointer; display: flex; align-items: center; gap: 6px; font-size: 0.88rem;">
                            🔄 Chia sẻ
                        </button>
                    </div>

                    <!-- Expandable Comments Section -->
                    <div class="comments-section" id="comments-section-{{ $p->id }}" style="display: none; padding: 14px 16px; background: #f8fafc; border-top: 1px solid #e2e8f0;">
                        <div class="comments-list" style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 12px;">
                            @if($p->comments && $p->comments->isNotEmpty())
                                @foreach($p->comments as $comment)
                                    <div class="comment-item" style="display: flex; gap: 10px; align-items: flex-start; background: #ffffff; border-radius: 12px; padding: 10px 14px; border: 1px solid #e2e8f0;">
                                        <div class="comment-avatar" style="width: 30px; height: 30px; border-radius: 50%; background: linear-gradient(135deg, #0284c7, #0369a1); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.78rem; font-weight: 800; flex-shrink: 0;">
                                            {{ $comment->user ? mb_substr($comment->user->name, 0, 1, 'UTF-8') : '👤' }}
                                        </div>
                                        <div style="flex: 1; display: flex; flex-direction: column; gap: 3px;">
                                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                                <span style="font-size: 0.84rem; font-weight: 800; color: #0f172a;">
                                                    {{ $comment->display_name }}
                                                    @if($comment->user && $comment->user->role === 'admin')
                                                        <span style="font-size: 0.65rem; font-weight: 700; background: rgba(239, 68, 68, 0.15); color: #ef4444; border-radius: 4px; padding: 1px 5px; margin-left: 4px;">Admin</span>
                                                    @endif
                                                </span>
                                                <span style="font-size: 0.7rem; color: #64748b;">{{ $comment->created_at->diffForHumans() }}</span>
                                            </div>
                                            <p style="margin: 0; font-size: 0.86rem; color: #334155; line-height: 1.45;">{{ $comment->content }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        <!-- Comment Input Form -->
                        <form action="{{ route('comments.store') }}" method="POST" style="display: flex; flex-direction: column; gap: 8px;">
                            @csrf
                            <input type="hidden" name="commentable_id" value="{{ $p->id }}">
                            <input type="hidden" name="commentable_type" value="App\Models\Post">

                            @if(!auth()->check())
                                <input type="text" name="guest_name" placeholder="Tên của bạn (Khách vãng lai)..." 
                                    style="width: 100%; padding: 8px 14px; border-radius: 10px; border: 1px solid #cbd5e1; background: #ffffff; color: #0f172a; font-size: 0.82rem; outline: none; box-sizing: border-box;">
                            @endif

                            <div style="display: flex; gap: 10px; align-items: center;">
                                <input type="text" name="content" placeholder="Viết bình luận của bạn..." required
                                    style="flex: 1; padding: 9px 14px; border-radius: 20px; border: 1px solid #cbd5e1; background: #ffffff; color: #0f172a; font-size: 0.85rem; outline: none;">
                                <button type="submit" style="padding: 9px 18px; border-radius: 20px; background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: #fff; border: none; font-size: 0.84rem; font-weight: 800; cursor: pointer; white-space: nowrap;">
                                    Gửi 💬
                                </button>
                            </div>
                        </form>
                    </div>
                </article>
            @endforeach
        @else
            <div class="glass-panel" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 20px; padding: 40px 20px; text-align: center;">
                <div style="font-size: 3rem; margin-bottom: 12px;">📰</div>
                <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin-bottom: 6px;">Chưa có bài viết nào trên Bản tin</h3>
                <p style="font-size: 0.88rem; color: #64748b; margin-bottom: 20px;">Hãy là người đầu tiên chia sẻ thông tin hoặc bài viết mới nhất lên cộng đồng!</p>
                <a href="/social" style="display: inline-block; background: #0284c7; color: #fff; padding: 10px 20px; border-radius: 12px; font-weight: 700; text-decoration: none;">💬 Kết nối & Đăng bài ngay</a>
            </div>
        @endif
    </div>

</div>

<!-- Newsfeed Post Creation Modal -->
@if(auth()->check())
@php $authUser = auth()->user(); @endphp
<div class="sch-modal" id="addNewsfeedPostModal" onclick="if(event.target === this) closeNewsfeedPostModal()">
    <div class="fb-modal-box">
        <button type="button" onclick="closeNewsfeedPostModal()" class="sch-close-modal" style="top: 16px; right: 16px; width: 36px; height: 36px; border-radius: 50%; background: #f1f5f9; border: none; font-size: 1.1rem; color: #475569; position: absolute; z-index: 10; cursor: pointer;">✕</button>
        <div class="fb-modal-header">
            <h4 class="fb-modal-title">Tạo bài viết</h4>
        </div>
        
        <form action="{{ route('principal.posts.store') }}" method="POST" enctype="multipart/form-data" onsubmit="handleNewsfeedPostSubmit(event, this)">
            @csrf

            <!-- User Header Row -->
            <div class="fb-modal-user-row">
                @if($authUser->avatar && str_starts_with($authUser->avatar, 'avatars/'))
                    <img src="{{ rtrim(env('R2_PUBLIC_URL'), '/') . '/' . $authUser->avatar }}" class="fb-modal-user-avatar">
                @else
                    <div class="fb-modal-user-avatar" style="background: linear-gradient(135deg, #0284c7, #0369a1); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem;">
                        {{ mb_substr($authUser->name, 0, 1, 'UTF-8') }}
                    </div>
                @endif
                <div class="fb-modal-user-info">
                    <h5 class="fb-modal-user-name">{{ $authUser->name }}</h5>
                    <div class="fb-modal-badges">
                        <span class="fb-modal-badge" style="background: #e2e8f0; font-size: 0.78rem; padding: 4px 10px; border-radius: 6px; font-weight: 700; color: #334155;">🌐 Công khai</span>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="fb-modal-body">
                <input type="text" name="name" required class="fb-modal-title-input" placeholder="Tiêu đề bài viết...">
                <textarea name="description" required class="fb-modal-textarea" rows="4" placeholder="{{ $authUser->name }} ơi, bạn đang nghĩ gì thế?"></textarea>
            </div>

            <!-- Multi Image Preview Box -->
            <div id="newsfeed-post-preview" style="display: none; border-radius: 14px; overflow: hidden; margin-bottom: 16px; position: relative;">
                <div id="newsfeed-preview-grid" style="width: 100%;"></div>
            </div>

            <!-- Facebook Bottom Action Bar -->
            <div class="fb-modal-action-bar">
                <span class="fb-modal-action-label">Thêm vào bài viết của bạn</span>
                <div class="fb-modal-action-buttons">
                    <label class="fb-modal-action-btn" title="Thêm ảnh/video">
                        🖼️
                        <input type="file" name="images[]" multiple accept="image/*,video/*,.mp4,.mov,.avi,.mkv,.webm" class="fb-modal-file-input" onchange="previewMultiPostImages(this, 'newsfeed-post-preview', 'newsfeed-preview-grid')">
                    </label>
                </div>
            </div>

            <button type="submit" class="fb-modal-submit-btn">
                Đăng
            </button>
        </form>
    </div>
</div>
@endif

<!-- Facebook Photo Lightbox Modal System -->
<div id="postLightboxModal" class="modal" style="display:none; position:fixed; z-index:99999; left:0; top:0; width:100%; height:100%; overflow:hidden; background-color:rgba(0,0,0,0.92); backdrop-filter:blur(10px); justify-content:center; align-items:center;">
    <span style="position:absolute; top:20px; right:25px; color:#ffffff; font-size:36px; font-weight:bold; cursor:pointer; z-index:100000; text-shadow: 0 2px 8px rgba(0,0,0,0.5);" onclick="closePostLightbox()">&times;</span>
    
    <button type="button" onclick="navigateLightbox(-1)" style="position:absolute; left:20px; top:50%; transform:translateY(-50%); background:rgba(255,255,255,0.15); color:#fff; border:none; border-radius:50%; width:48px; height:48px; font-size:24px; cursor:pointer; display:flex; align-items:center; justify-content:center; z-index:100000; backdrop-filter:blur(5px);">‹</button>
    <button type="button" onclick="navigateLightbox(1)" style="position:absolute; right:20px; top:50%; transform:translateY(-50%); background:rgba(255,255,255,0.15); color:#fff; border:none; border-radius:50%; width:48px; height:48px; font-size:24px; cursor:pointer; display:flex; align-items:center; justify-content:center; z-index:100000; backdrop-filter:blur(5px);">›</button>

    <div style="position:relative; max-width:90vw; max-height:90vh; display:flex; align-items:center; justify-content:center;">
        <img id="lightboxCurrentImg" style="max-width:90vw; max-height:85vh; border-radius:12px; object-fit:contain; box-shadow:0 12px 40px rgba(0,0,0,0.8);">
        <div id="lightboxCounter" style="position:absolute; bottom:-35px; color:rgba(255,255,255,0.85); font-size:0.85rem; font-weight:700; background:rgba(0,0,0,0.5); padding:4px 12px; border-radius:20px;"></div>
    </div>
</div>

<script>
let currentLightboxImages = [];
let currentLightboxIndex = 0;

function openPostLightboxGallery(images, startIndex = 0) {
    if (!images || !images.length) return;
    currentLightboxImages = images;
    currentLightboxIndex = startIndex;
    updateLightboxView();
    document.getElementById('postLightboxModal').style.display = 'flex';
}

function updateLightboxView() {
    if (!currentLightboxImages.length) return;
    const imgEl = document.getElementById('lightboxCurrentImg');
    const counterEl = document.getElementById('lightboxCounter');
    imgEl.src = currentLightboxImages[currentLightboxIndex];
    counterEl.textContent = `${currentLightboxIndex + 1} / ${currentLightboxImages.length}`;
}

function navigateLightbox(dir) {
    if (!currentLightboxImages.length) return;
    currentLightboxIndex = (currentLightboxIndex + dir + currentLightboxImages.length) % currentLightboxImages.length;
    updateLightboxView();
}

function closePostLightbox() {
    document.getElementById('postLightboxModal').style.display = 'none';
}

function toggleComments(postId, btnEl) {
    const sec = document.getElementById('comments-section-' + postId);
    if (sec) {
        sec.style.display = (sec.style.display === 'none' || sec.style.display === '') ? 'block' : 'none';
    }
}

function sendReaction(postId, type, emoji, event) {
    if (event) event.preventDefault();
    fetch('/api/reactions/toggle', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            post_id: postId,
            type: type,
            emoji: emoji
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            window.location.reload();
        }
    })
    .catch(err => console.error('Reaction error:', err));
}

function togglePostLike(btn, postId) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    fetch('/api/reactions/toggle', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken || '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            id: postId,
            type: 'post',
            emoji: '👍'
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (data.liked) {
                btn.classList.add('active');
                btn.style.color = '#2563eb';
                btn.style.fontWeight = '700';
                btn.innerHTML = '👍 Đã thích';
            } else {
                btn.classList.remove('active');
                btn.style.color = '#64748b';
                btn.style.fontWeight = '700';
                btn.innerHTML = '👍 Thích';
            }
            
            const statsEl = document.getElementById(`post-likes-count-${postId}`);
            if (statsEl) {
                statsEl.innerHTML = `👍 ${data.likes_count} lượt thích`;
            }
        }
    })
    .catch(err => {
        console.error('Lỗi tương tác bài viết:', err);
    });
}

function openNewsfeedPostModal() {
    const m = document.getElementById('addNewsfeedPostModal');
    if (m) {
        m.classList.add('show');
        m.style.display = 'flex';
    }
}

function closeNewsfeedPostModal() {
    const m = document.getElementById('addNewsfeedPostModal');
    if (m) {
        m.classList.remove('show');
        m.style.display = 'none';
    }
}

function handleNewsfeedPostSubmit(e, form) {
    e.preventDefault();
    
    const submitBtn = form.querySelector('.fb-modal-submit-btn');
    const originalBtnText = submitBtn ? submitBtn.innerHTML : 'Đăng';
    
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '🚀 Đang đăng bài...';
    }

    const formData = new FormData(form);
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken || '',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }

        if (data.success) {
            closeNewsfeedPostModal();
            form.reset();
            const previewBox = document.getElementById('newsfeed-post-preview');
            const previewGrid = document.getElementById('newsfeed-preview-grid');
            if (previewBox) previewBox.style.display = 'none';
            if (previewGrid) previewGrid.innerHTML = '';

            if (typeof showToastNotification === 'function') {
                showToastNotification('✅ Đăng bài viết mới thành công!');
            }
            
            setTimeout(() => window.location.reload(), 1200);
        } else {
            alert(data.message || 'Có lỗi xảy ra, vui lòng thử lại.');
        }
    })
    .catch(err => {
        console.error('Post submit error:', err);
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
        alert('Có lỗi xảy ra, vui lòng thử lại.');
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const postId = urlParams.get('post');
    if (postId) {
        const targetPost = document.getElementById('post-' + postId);
        if (targetPost) {
            targetPost.style.borderColor = '#0284c7';
            targetPost.style.boxShadow = '0 0 0 4px rgba(2, 132, 199, 0.25), 0 8px 30px rgba(0,0,0,0.12)';
            setTimeout(() => {
                targetPost.style.borderColor = '#e2e8f0';
                targetPost.style.boxShadow = '0 4px 20px rgba(0,0,0,0.05)';
            }, 3500);
        }
    }
});
</script>
@endsection
