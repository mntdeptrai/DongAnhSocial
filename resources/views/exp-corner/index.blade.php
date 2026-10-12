@extends('layouts.food-tour')

@section('title', 'Góc trải nghiệm thực tế - Làng nghề & Vui chơi Đông Anh')

@section('styles')
    @include('exp-corner.partials.styles')
@endsection

@section('content')
@php
    // Helper resolve authentic contextual images for activities, strictly eliminating AI & Unsplash photos
    $resolveActivityImage = function($act) {
        $imgPath = $act->image_path ?? '';
        if (!empty($imgPath) && !str_contains($imgPath, 'placeholder') && !str_contains($imgPath, 'unsplash')) {
            return $imgPath;
        }
        $text = mb_strtolower(($act->name ?? '') . ' ' . ($act->description ?? ''));
        if (str_contains($text, 'ăn tối') || str_contains($text, 'nhà dân') || str_contains($text, 'cháo se') || str_contains($text, 'mâm cơm') || str_contains($text, 'đặt lễ')) {
            return 'https://media.xadonganh.com/experience/chao_se_co_loa.jpg';
        }
        if (str_contains($text, 'bún') || str_contains($text, 'mạch tràng')) {
            return 'https://media.xadonganh.com/experience/bun_xao_mach_trang.jpg';
        }
        if (str_contains($text, 'tế lễ') || str_contains($text, 'dâng hương') || str_contains($text, 'nghi lễ')) {
            return 'https://media.xadonganh.com/experience/te_le_den_thuong_co_loa.jpg';
        }
        if (str_contains($text, 'bắn nỏ') || str_contains($text, 'nỏ thần') || str_contains($text, 'âu lạc') || str_contains($text, 'mật mã') || str_contains($text, 'game')) {
            return 'https://media.xadonganh.com/experience/tuong_no_than_au_lac.jpg';
        }
        if (str_contains($text, 'cổng') || str_contains($text, 'quần thể') || str_contains($text, 'vé tham quan')) {
            return 'https://media.xadonganh.com/experience/cong_den_thuong_co_loa.jpg';
        }
        if (str_contains($text, 'trồng rau') || str_contains($text, 'sinh thái') || str_contains($text, 'hào thành')) {
            return 'https://media.xadonganh.com/experience/flycam_toan_canh_den_thuong_co_loa.jpg';
        }
        if (str_contains($text, 'lễ hội') || str_contains($text, 'rước kiệu')) {
            return 'https://media.xadonganh.com/experience/le_hoi_ruoc_kieu_co_loa.jpg';
        }
        return 'https://media.xadonganh.com/experience/gieng_ngoc_co_loa_ho.jpg';
    };

    // Helper resolve audience chip
    $resolveAudienceChip = function($name) {
        $t = mb_strtolower($name);
        if (str_contains($t, 'trồng rau')) {
            return '👶 Trẻ em & Gia đình';
        }
        if (str_contains($t, 'ăn tối')) {
            return '🏡 Gia đình & Bạn bè';
        }
        if (str_contains($t, 'mật mã') || str_contains($t, 'game')) {
            return '🎒 Nhóm bạn & Giới trẻ';
        }
        if (str_contains($t, 'bắn nỏ') || str_contains($t, 'làm bún')) {
            return '👶 Mọi lứa tuổi';
        }
        if (str_contains($t, 'dâng hương') || str_contains($t, 'thuyết minh')) {
            return '🏛️ Đoàn tham quan';
        }
        return '⭐ Mọi lứa tuổi';
    };
@endphp

<div style="background: var(--bg-base); min-height: calc(100vh - 70px); position: relative; overflow-x: hidden;">

    {{-- 1. CINEMATIC HERO SECTION --}}
    @include('exp-corner.partials.hero')

    {{-- MAIN CATALOG CONTAINER --}}
    <div class="container exp-main-container">

        {{-- 2. SMART AI MATCHMAKER BENTO WIDGET --}}
        @include('exp-corner.partials.ai-matchmaker')

        {{-- 3. FILTER TABS & SEARCH TOOLBAR --}}
        @include('exp-corner.partials.toolbar')

        {{-- 4. UNIFIED BENTO EXPERIENCES GRID --}}
        <div class="bento-exp-grid" id="main-exp-grid">

            {{-- ── A. RENDER TOURS ── --}}
            @foreach($tours as $tour)
                <div class="bento-card exp-card-item" 
                     data-kind="tour" 
                     data-tags="tour food craft"
                     data-title="{{ Str::lower($tour->name) }}"
                     data-desc="{{ Str::lower($tour->description) }}">
                    <div class="bento-thumb-wrapper">
                        <img src="{{ $tour->thumbnail ?: asset('images/thanhcoloa.webp') }}" class="bento-thumb" alt="{{ $tour->name }}" onerror="this.src='{{ asset('images/thanhcoloa.webp') }}'">
                        <div class="bento-thumb-overlay"></div>
                        <span class="bento-badge-type badge-tour">
                            <span>🗺️ Lộ trình</span> • {{ $tour->popularity ?: 'Nổi bật' }}
                        </span>
                        <span class="bento-badge-audience">
                            {{ $tour->difficulty ?: '✨ Trải nghiệm tự tay' }}
                        </span>
                        <div class="bento-location-tag">
                            <span>📍</span> Đông Anh, Hà Nội
                        </div>
                    </div>
                    <div class="bento-body">
                        <h3 class="bento-title">{{ $tour->name }}</h3>
                        <p class="bento-desc">{{ $tour->description }}</p>

                        <div class="bento-meta-grid">
                            <div>
                                <span class="bento-meta-label">⏱️ Thời lượng</span>
                                <span class="bento-meta-value">{{ $tour->duration ?: '3 - 4 giờ' }}</span>
                            </div>
                            <div>
                                <span class="bento-meta-label">📏 Cự ly</span>
                                <span class="bento-meta-value">{{ $tour->distance ?: '4.5 km' }}</span>
                            </div>
                            <div>
                                <span class="bento-meta-label">💰 Dự chi</span>
                                <span class="bento-meta-value price">{{ $tour->budget ?: 'Theo nhóm' }}</span>
                            </div>
                        </div>

                        <div class="bento-actions">
                            <a href="/food-tour/{{ $tour->slug }}" class="btn-bento-primary">
                                🚀 Bắt đầu Tour
                            </a>
                            @if($tour->diaries_count > 0)
                            <button type="button" onclick="openDiariesModal('{{ $tour->id }}')" class="btn-bento-secondary">
                                📖 Nhật ký ({{ $tour->diaries_count }})
                            </button>
                            @else
                            <button type="button" onclick="openArtisanModal('{{ addslashes($tour->name) }}', 'Đông Anh, Hà Nội', '0243.965.2973', 'Lộ trình liên hoàn làng nghề')" class="btn-bento-secondary">
                                📞 Đặt trước
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- ── B. RENDER CULTURAL ACTIVITIES & WORKSHOPS ── --}}
            @foreach($activities as $activity)
                @php
                    $actImg = $resolveActivityImage($activity);
                    $audienceChip = $resolveAudienceChip($activity->name);
                    $eateryName = $activity->eatery ? $activity->eatery->name : 'Khu di tích Cổ Loa';
                    $eaterySlug = $activity->eatery ? ($activity->eatery->slug ?? 'khu-di-tich-co-loa') : 'khu-di-tich-co-loa';
                    $eateryPhone = $activity->eatery ? ($activity->eatery->phone ?: '0243.965.2973') : '0243.965.2973';
                    
                    // Categorize kind for tabs filter
                    $kind = 'monument';
                    $badgeClass = 'badge-ticket';
                    $badgeText = '🎫 Vé tham quan';
                    $tags = 'monument';

                    $nameLower = mb_strtolower($activity->name . ' ' . ($activity->description ?? ''));
                    if (str_contains($nameLower, 'ăn tối') || str_contains($nameLower, 'nhà dân')) {
                        $kind = 'homestay';
                        $badgeClass = 'badge-tour';
                        $badgeText = '🏡 Ăn tối nhà dân';
                        $tags .= ' homestay food';
                    } elseif (str_contains($nameLower, 'trồng rau') || str_contains($nameLower, 'nông nghiệp')) {
                        $kind = 'farming';
                        $badgeClass = 'badge-craft';
                        $badgeText = '🌱 Trồng rau hữu cơ';
                        $tags .= ' farming craft';
                    } elseif (str_contains($nameLower, 'mật mã') || str_contains($nameLower, 'game')) {
                        $kind = 'game';
                        $badgeClass = 'badge-experience';
                        $badgeText = '🏹 Game Loa Thành';
                        $tags .= ' game history';
                    } elseif (str_contains($nameLower, 'bắn nỏ') || str_contains($nameLower, 'nỏ thần')) {
                        $kind = 'game';
                        $badgeClass = 'badge-experience';
                        $badgeText = '🎯 Bắn nỏ dân gian';
                        $tags .= ' game history';
                    } elseif (str_contains($nameLower, 'bún') || str_contains($nameLower, 'gốm') || str_contains($nameLower, 'đan lát') || str_contains($nameLower, 'làng nghề') || str_contains($nameLower, 'mộc')) {
                        $kind = 'craft';
                        $badgeClass = 'badge-craft';
                        $badgeText = '🏺 Làng nghề & Workshop';
                        $tags .= ' craft food';
                    } elseif (($activity->type ?? '') === 'service') {
                        $kind = 'monument';
                        $badgeClass = 'badge-service';
                        $badgeText = '🙏 Dịch vụ di tích';
                        $tags .= ' service';
                    }

                    // Prepare rich JSON payload for Activity Detail Modal
                    $actJsonPayload = json_encode([
                        'id'              => $activity->id,
                        'name'            => $activity->name,
                        'type_label'      => $badgeText,
                        'audience'        => $audienceChip,
                        'location'        => $eateryName,
                        'price'           => $activity->price ?? 0,
                        'price_formatted' => ($activity->price ?? 0) > 0 ? number_format($activity->price, 0, ',', '.') . 'đ' : 'Theo yêu cầu',
                        'duration'        => $activity->duration ?? '2 - 3 giờ',
                        'best_time'       => $activity->best_time ?? '08:00 - 17:00 hằng ngày',
                        'description'     => $activity->description ?? '',
                        'image_path'      => $actImg,
                        'highlights'      => $activity->highlights ?? ['Trải nghiệm nhập vai thực tế cùng người bản xứ', 'Không gian văn hóa truyền thống thanh bình', 'Có sản phẩm/thành phẩm mang về làm kỷ niệm'],
                        'timeline'        => $activity->timeline ?? ['Chặng 1: Đón khách và làm quen với nghệ nhân.', 'Chặng 2: Trực tiếp tham gia hoạt động thực hành.', 'Chặng 3: Thưởng thức thành phẩm và chụp ảnh lưu niệm.'],
                        'included'        => $activity->included ?? 'Chi phí trọn gói hoạt động trải nghiệm.',
                        'notice'          => $activity->notice ?? 'Nên liên hệ đặt trước ít nhất 2 giờ để được phục vụ chu đáo nhất.',
                        'eatery'          => [
                            'name'  => $eateryName,
                            'phone' => $eateryPhone
                        ]
                    ], JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT);
                @endphp

                <div class="bento-card exp-card-item" 
                     data-kind="{{ $kind }}"
                     data-tags="{{ $tags }}"
                     data-title="{{ Str::lower($activity->name) }}"
                     data-desc="{{ Str::lower($activity->description ?? '') }}"
                     data-location="{{ Str::lower($eateryName) }}">
                    <div class="bento-thumb-wrapper">
                        <img src="{{ $actImg }}" class="bento-thumb" alt="{{ $activity->name }}" onerror="this.src='{{ asset('images/thanhcoloa.webp') }}'">
                        <div class="bento-thumb-overlay"></div>
                        <span class="bento-badge-type {{ $badgeClass }}">
                            {{ $badgeText }}
                        </span>
                        <span class="bento-badge-audience">
                            {{ $audienceChip }}
                        </span>
                        <div class="bento-location-tag">
                            <span>🏛️</span> {{ $eateryName }}
                        </div>
                    </div>
                    <div class="bento-body">
                        <h3 class="bento-title">{{ $activity->name }}</h3>
                        <p class="bento-desc">
                            {{ $activity->description ?: 'Hoạt động trải nghiệm bản địa đặc sắc được tổ chức trực tiếp tại điểm đến di tích văn hóa Đông Anh.' }}
                        </p>

                        <div class="bento-meta-grid">
                            <div>
                                <span class="bento-meta-label">💰 Mức phí</span>
                                <span class="bento-meta-value price">
                                    {{ ($activity->price ?? 0) > 0 ? number_format($activity->price, 0, ',', '.') . 'đ' : 'Theo yêu cầu' }}
                                </span>
                            </div>
                            <div>
                                <span class="bento-meta-label">📦 Quy cách</span>
                                <span class="bento-meta-value">{{ $activity->unit ?? '1 lượt' }}</span>
                            </div>
                            <div>
                                <span class="bento-meta-label">🏷️ Ưu đãi</span>
                                <span class="bento-meta-value" style="font-size: 0.75rem;" title="{{ $activity->discount_note ?? 'Giá niêm yết' }}">
                                    {{ $activity->discount_note ?? 'Giá niêm yết' }}
                                </span>
                            </div>
                        </div>

                        <div class="bento-actions">
                            <button type="button" 
                                    class="btn-bento-primary" 
                                    onclick='openActivityDetailModal({!! $actJsonPayload !!})'>
                                🔍 Xem Chi Tiết
                            </button>
                            <button type="button" 
                                    onclick="openArtisanModal('{{ addslashes($activity->name) }}', '{{ addslashes($eateryName) }}', '{{ $eateryPhone }}', '{{ $badgeText }}')" 
                                    class="btn-bento-secondary">
                                📞 Đặt lịch
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach

        </div>

        {{-- Khung thông báo không tìm thấy kết quả --}}
        <div id="no-results-box" class="glass-card" style="display: none; text-align: center; padding: 60px 20px; border-radius: 24px; margin-top: 30px;">
            <span style="font-size: 3rem;">🔍</span>
            <h3 style="margin-top: 16px; font-weight: 800; color: var(--text-main);">Không tìm thấy hoạt động phù hợp</h3>
            <p style="color: var(--text-muted); margin-top: 8px;">Thử thay đổi từ khóa hoặc chọn tab bộ lọc khác ở phía trên.</p>
            <button type="button" onclick="resetAllFilters()" style="margin-top: 16px; padding: 10px 24px; border-radius: 50px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border: none; font-weight: 700; cursor: pointer;">
                🔄 Xem lại tất cả
            </button>
        </div>

        {{-- 5. BỨC TƯỜNG NHẬT KÝ & FORM ĐĂNG FEEDBACK CỘNG ĐỒNG --}}
        @include('exp-corner.partials.feedback-section')

    </div>

    {{-- 6. MODAL: XEM CHI TIẾT HOẠT ĐỘNG CHUYÊN SÂU --}}
    @include('exp-corner.partials.activity-detail-modal')

    {{-- 7. MODAL: ĐẶT LỊCH NHANH & KẾT NỐI NGHỆ NHÂN BẢN ĐỊA --}}
    @include('exp-corner.partials.artisan-modal')

    {{-- 7.1 MODAL: THƯ VIỆN TOÀN BỘ CẢM NHẬN & NHẬT KÝ AIRBNB-STYLE --}}
    @include('exp-corner.partials.feedback-library-modal')

    {{-- 8. MODALS NHẬT KÝ FOOD TOUR CŨ (NẾU CÓ) --}}
    @foreach($tours as $tourData)
        @if($tourData->diaries_count > 0)
        <div id="communityDiariesModal-{{ $tourData->id }}" class="artisan-modal-overlay" style="display: none;">
            <div class="artisan-modal-content" style="max-width: 650px; display: flex; flex-direction: column;">
                <button onclick="closeDiariesModal('{{ $tourData->id }}')" class="modal-close-btn">✕</button>
                
                <h3 style="font-weight: 800; color: #0f172a; font-size: 1.35rem; margin: 0 0 20px 0; display: flex; align-items: center; gap: 12px; border-bottom: 1px solid #f1f5f9; padding-bottom: 16px;">
                    📖 Nhật ký Cộng đồng
                    <span style="font-size: 0.8rem; background: #ecfdf5; border: 1px solid #a7f3d0; color: #059669; padding: 4px 12px; border-radius: 20px; font-weight: 800;">
                        {{ $tourData->diaries_count }} đánh giá
                    </span>
                </h3>
                
                <div style="flex: 1; overflow-y: auto; padding-right: 12px; display: flex; flex-direction: column; gap: 20px;">
                    @foreach($tourData->diaries as $diary)
                        <div style="padding: 20px; border-radius: 18px; background: #ffffff; border: 1.5px solid #e2e8f0; border-top: 4px solid #10b981; box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 800;">
                                        {{ substr($diary->user ? $diary->user->name : 'TK', 0, 2) }}
                                    </div>
                                    <div>
                                        <strong style="font-size: 0.9rem; color: #0f172a; display: block;">{{ $diary->user ? $diary->user->name : 'Du khách' }}</strong>
                                        <span style="font-size: 0.72rem; color: #64748b;">{{ $diary->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                </div>
                                <div style="color: #d97706; font-size: 0.75rem; font-weight: 800; background: #fffbeb; padding: 4px 10px; border-radius: 20px; border: 1px solid #fef3c7;">
                                    ⭐ {{ $diary->rating ?: 5 }} / 5 Sao
                                </div>
                            </div>
                            @if($diary->comment)
                            <p style="margin: 0; font-size: 0.86rem; color: #334155; line-height: 1.6; font-style: italic;">
                                "{{ $diary->comment }}"
                            </p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    @endforeach

</div>

{{-- 9. JAVASCRIPT LOGIC --}}
@include('exp-corner.partials.scripts')

@endsection

@section('footer')
<footer style="background: #09090b; border-top: 1px solid rgba(16, 185, 129, 0.15); padding: 50px 0 30px 0; color: var(--text-muted); font-size: 0.88rem; font-family: var(--font-body); margin-top: 60px;">
    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
        <div class="footer-grid" style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 40px; margin-bottom: 30px;">
            <div>
                <h3 class="logo" style="margin-bottom: 16px; font-size: 1.3rem; display: flex; align-items: center; gap: 8px; color: var(--text-main); font-family: var(--font-heading);">
                    <span>📖</span> <span style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">DongAnh Discovery</span>
                </h3>
                <p style="line-height: 1.6; max-width: 480px;">
                    Bản đồ số Đông Anh (DongAnh Discovery) là giải pháp công nghệ số hóa toàn bộ trường học, bệnh viện, cơ sở y tế, khách sạn, nhà nghỉ, nhà hàng, quán cafe và quảng bá các sản phẩm OCOP đặc sản truyền thống của xã Đông Anh, Hà Nội. Hỗ trợ chuyển đổi số và nâng tầm văn hóa du lịch địa phương.
                </p>
            </div>
            <div>
                <h4 style="color: var(--text-main); margin-bottom: 16px; font-size: 1rem; font-weight: 700; font-family: var(--font-heading);">Liên kết nhanh</h4>
                <ul style="list-style: none; display: flex; flex-direction: column; gap: 10px; padding: 0; margin: 0;">
                    <li><a href="/" style="transition: color 0.3s; color: var(--text-muted);" onmouseover="this.style.color='#10b981'" onmouseout="this.style.color='var(--text-muted)'">Trang chủ</a></li>
                    <li><a href="/tim-kiem" style="transition: color 0.3s; color: var(--text-muted);" onmouseover="this.style.color='#10b981'" onmouseout="this.style.color='var(--text-muted)'">Bản đồ số</a></li>
                    <li><a href="/food-tours" style="transition: color 0.3s; color: var(--text-muted);" onmouseover="this.style.color='#10b981'" onmouseout="this.style.color='var(--text-muted)'">Food Tour</a></li>
                    <li><a href="/exp-corner" style="transition: color 0.3s; color: var(--text-muted);" onmouseover="this.style.color='#10b981'" onmouseout="this.style.color='var(--text-muted)'">Góc trải nghiệm thực tế</a></li>
                </ul>
            </div>
            <div>
                <h4 style="color: var(--text-main); margin-bottom: 16px; font-size: 1rem; font-weight: 700; font-family: var(--font-heading);">Thông tin hành chính</h4>
                <p style="line-height: 1.6;">
                    <strong>Cơ quan chủ quản:</strong> Uỷ ban nhân dân xã Đông Anh<br>
                    <strong>Chịu trách nhiệm chính:</strong> Phòng Văn hóa - Xã hội xã Đông Anh<br>
                    📍 Địa chỉ: Số 66 đường Cao Lỗ, xã Đông Anh, thành phố Hà Nội<br>
                    📞 Điện thoại: 0243.965.2973<br>
                    🌐 Website: <a href="https://donganh.hanoi.gov.vn" target="_blank" style="color: inherit; text-decoration: none;">donganh.hanoi.gov.vn</a>
                </p>
            </div>
        </div>
        <div style="border-top: 1px solid rgba(255,255,255,0.05); padding-top: 20px; text-align: center; font-size: 0.8rem; color: rgba(255,255,255,0.3);">
            &copy; 2026 DongAnh Discovery (Bản đồ số Đông Anh). Tất cả quyền được bảo lưu. Phát triển bởi Phòng Văn hóa - Xã hội xã Đông Anh
        </div>
    </div>
</footer>
@endsection
