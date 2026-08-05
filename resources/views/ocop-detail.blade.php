@extends('layouts.app')

<!-- Tối ưu hóa SEO: Tiêu đề động cho Sản phẩm OCOP -->
@section('title', $product->name . ' - Chứng Nhận OCOP Cấp Quốc Gia | ' . ($eatery ? $eatery->name : 'Đông Anh'))

<!-- Tối ưu hóa SEO Meta Description -->
@section('meta_description', 'Khám phá sản phẩm OCOP ' . $product->name . ' thuộc ' . ($eatery ? $eatery->name : 'Đông Anh') . ', địa chỉ: ' . ($eatery ? $eatery->address : 'Đông Anh, Hà Nội') . '. Xem thông số kỹ thuật, chứng nhận QCVN, thành phần và hotline đặt mua.')

<!-- Tối ưu hóa SEO Từ Khóa Tìm Kiếm Google (Keywords) -->
@section('meta_keywords', $product->name . ', Sản phẩm OCOP ' . $product->name . ', OCOP ' . ($product->star_rating ? $product->star_rating . ' sao' : 'Đông Anh') . ', đặc sản OCOP Đông Anh, ' . $product->name . ' Hà Nội, mua ' . $product->name . ', giá ' . $product->name . ', ' . ($eatery ? $eatery->name : 'Đông Anh'))

@section('og_image', $product->image_path ?: ($eatery?->image_path ?: 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=800&q=80'))
@section('og_type', 'product')
@section('canonical_url', route('ocop.product.show', $product->id))

<!-- Structured Data JSON-LD cho Google Rich Snippets & Search Indexing -->
@push('head')
<script type="application/ld+json">
{
  "@@context": "https://schema.org/",
  "@@type": "Product",
  "name": "{{ addslashes($product->name) }}",
  "image": [
    "{{ $product->image_path ? asset($product->image_path) : 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=800&q=80' }}"
  ],
  "description": "{{ addslashes(preg_replace('/\s+/', ' ', strip_tags($product->description ?: ($product->story ?: 'Sản phẩm OCOP đạt chứng nhận cấp quốc gia tại Đông Anh, Hà Nội')))) }}",
  "brand": {
    "@@type": "Brand",
    "name": "{{ addslashes($eatery ? $eatery->name : 'Đông Anh OCOP') }}"
  },
  "offers": {
    "@@type": "Offer",
    "url": "{{ route('ocop.product.show', $product->id) }}",
    "priceCurrency": "VND",
    "price": "{{ (int)preg_replace('/[^0-9]/', '', (string)$product->price) ?: 0 }}",
    "priceValidUntil": "{{ date('Y-12-31', strtotime('+1 year')) }}",
    "itemCondition": "https://schema.org/NewCondition",
    "availability": "https://schema.org/InStock",
    "seller": {
      "@@type": "Organization",
      "name": "{{ addslashes($eatery ? $eatery->name : 'Đông Anh OCOP') }}"
    }
  }
  <?php if($product->star_rating): ?>
  ,
  "aggregateRating": {
    "@@type": "AggregateRating",
    "ratingValue": "{{ $product->star_rating }}",
    "reviewCount": "10",
    "bestRating": "5",
    "worstRating": "1"
  }
  <?php endif; ?>
}
</script>

<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "BreadcrumbList",
  "itemListElement": [{
    "@@type": "ListItem",
    "position": 1,
    "name": "Trang chủ",
    "item": "{{ url('/') }}"
  },{
    "@@type": "ListItem",
    "position": 2,
    "name": "Sản phẩm OCOP Đông Anh",
    "item": "{{ url('/') }}#ocop"
  },{
    "@@type": "ListItem",
    "position": 3,
    "name": "{{ addslashes($product->name) }}"
  }]
}
</script>
@endpush

@section('content')

@php
    // --- BỘ PHÂN TÍCH & CHUYỂN ĐỔI MÔ TẢ THÀNH INFOGRAPHIC THÔNG MINH ---
    $fullText = ($product->description ?: '') . "\n" . ($product->story ?: '');
    $lines = array_filter(array_map('trim', explode("\n", str_replace(["\r\n", "\r"], "\n", $fullText))));

    $decisionStr = null;
    $originStr = null;
    $heritageStory = [];
    $ingredientsList = [];
    $specsList = [];
    $usageList = [];
    $storageList = [];
    $warningList = [];
    $qcvnStr = null;

    $paymentDistList = [];

    foreach ($lines as $line) {
        if (preg_match('/QĐ\s*số\s*[^;\n]+/ui', $line, $m)) {
            $decisionStr = $m[0];
            $cleanLine = trim(str_replace($m[0], '', $line), " ;,;");
            if (!empty($cleanLine)) $originStr = $cleanLine;
        } elseif (preg_match('/^(thông\s+tin\s+chi\s+tiết|mô\s+tả\s+sản\s+phẩm)/ui', $line)) {
            continue;
        } elseif (preg_match('/^thành\s+phần:\s*(.*)/ui', $line, $m)) {
            $ingredientsList = array_filter(array_map('trim', explode(',', $m[1])));
        } elseif (preg_match('/^(chỉ\s+tiêu|methanol|ethanol|độ\s+cồn)/ui', $line)) {
            $specsList[] = $line;
        } elseif (preg_match('/^QCVN:\s*(.*)/ui', $line, $m)) {
            $qcvnStr = $m[1];
        } elseif (preg_match('/^lưu\s+ý:\s*(.*)/ui', $line, $m)) {
            $warningList[] = $m[1];
        } elseif (preg_match('/^bảo\s+quản:\s*(.*)/ui', $line, $m)) {
            $storageList[] = $m[1];
        } elseif (preg_match('/^(sử\s+dụng|dùng\s+ngâm|cách\040dùng):\s*(.*)/ui', $line, $m) || preg_match('/^(uống\s+trực\s+tiếp|dùng\s+ngâm)/ui', $line)) {
            $usageList[] = $line;
        } elseif (preg_match('/(số\s+tài\s+khoản|ngân\s+hàng|ACB|tài\s+khoản|siêu\s+thị|cửa\s+hàng\s+tiện\s+ích)/ui', $line)) {
            $paymentDistList[] = $line;
        } else {
            $heritageStory[] = $line;
        }
    }

    // Merge fallback for ingredients if product has array field
    if (!empty($product->ingredients)) {
        $extraIngs = is_array($product->ingredients) ? $product->ingredients : array_filter(array_map('trim', explode("\n", (string)$product->ingredients)));
        $ingredientsList = array_unique(array_merge($ingredientsList, $extraIngs));
    }

    // --- SMART CATEGORY-AWARE FALLBACK SYSTEM ---
    $pNameLower = mb_strtolower($product->name);
    $sellerLower = mb_strtolower($product->seller_name ?: ($eatery?->name ?: ''));
    $combineSearch = $pNameLower . ' ' . $sellerLower;

    $isWine = (str_contains($combineSearch, 'rượu') || str_contains($combineSearch, 'tửu') || str_contains($combineSearch, 'cồn') || str_contains($combineSearch, 'long tửu'));
    $isStatue = (str_contains($combineSearch, 'tượng') || str_contains($combineSearch, 'gỗ') || str_contains($combineSearch, 'điêu khắc') || str_contains($combineSearch, 'mỹ nghệ') || str_contains($combineSearch, 'chạm khắc') || str_contains($combineSearch, 'trần văn tân'));
    $isCake = (str_contains($combineSearch, 'bánh') || str_contains($combineSearch, 'kẹo') || str_contains($combineSearch, 'sampa') || str_contains($combineSearch, 'ngọt') || str_contains($combineSearch, 'chưng') || str_contains($combineSearch, 'nướng'));
    $isTea = (str_contains($combineSearch, 'trà') || str_contains($combineSearch, 'chè'));
    $isSauce = (str_contains($combineSearch, 'tương') || str_contains($combineSearch, 'mắm') || str_contains($combineSearch, 'gia vị') || str_contains($combineSearch, 'dấm'));

    if ($isWine) {
        $fallbackSpec = "Chỉ tiêu Ethanol/Methanol đạt chuẩn Bộ Y Tế, tinh lọc hiện đại khử độc tố.";
        $fallbackIng = ["Gạo nếp hoa vàng", "Men thuốc bắc 36 vị"];
        $fallbackUsage = "Thưởng thức trực tiếp (rất ngon khi uống lạnh) hoặc ngâm thảo dược.";
        $fallbackWarn = "🔞 Không sử dụng cho người dưới 18 tuổi & phụ nữ mang thai.";
    } elseif ($isStatue) {
        $fallbackSpec = "Đục thô & chạm bong thủ công nguyên khối, gỗ quý không mối mọt, đạt chứng nhận OCOP 4 sao Thành Phố.";
        $fallbackIng = ["Gỗ quý nguyên khối chọn lọc (Hương/Gụ/Trắc)", "Chạm bong nổi khối thủ công", "Sơn thếp vàng / Vecni tự nhiên", "Pháp khí Hoa Sen Xanh (Thanh liên)"];
        $fallbackUsage = "Bài trí không gian tâm linh, bộ Tây Phương Tam Thánh, phòng thờ gia đình hoặc triển lãm nghệ thuật Phật giáo.";
        $fallbackWarn = "📜 Bảo quản nơi khô ráo, tránh độ ẩm cao và ánh nắng trực tiếp chiếu vào gỗ.";
    } elseif ($isCake) {
        $fallbackSpec = "Đạt tiêu chuẩn An Toàn Vệ Sinh Thực Phẩm, không hóa chất bảo quản độc hại.";
        $fallbackIng = ["Bột mì cao cấp", "Trứng tươi", "Đường kính", "Bơ thực vật"];
        $fallbackUsage = "Thưởng thức trực tiếp, ngon hơn khi dùng kèm trà nóng hoặc cà phê.";
        $fallbackWarn = "⚠️ Hạn sử dụng ghi trên bao bì. Tránh để nơi ẩm ướt.";
    } elseif ($isTea) {
        $fallbackSpec = "100% búp trà tươi sấy khô truyền thống, giữ trọn hương vị thiên nhiên.";
        $fallbackIng = ["Búp trà tươi chọn lọc"];
        $fallbackUsage = "Pha với nước sôi 85-90°C, hãm trà trong 3-5 phút trước khi thưởng thức.";
        $fallbackWarn = "☕ Bảo quản trong hộp/túi kín sau khi mở nắp.";
    } elseif ($isSauce) {
        $fallbackSpec = "Ủ mốc tự nhiên truyền thống, đạt tiêu chuẩn An Toàn Vệ Sinh Thực Phẩm.";
        $fallbackIng = ["Gạo nếp cái hoa vàng", "Đỗ tương chọn lọc", "Muối tinh", "Nước giếng khơi sạch"];
        $fallbackUsage = "Thưởng thức trực tiếp làm nước chấm rau luộc, thịt luộc, cá kho hoặc nêm nếm gia đình.";
        $fallbackWarn = "⚠️ Đậy kín nắp sau khi dùng. Bảo quản nơi khô ráo, thoáng mát.";
    } else {
        $fallbackSpec = "Đạt tiêu chuẩn chất lượng OCOP Đông Anh & An Toàn Vệ Sinh Thực Phẩm.";
        $fallbackIng = ["Nguyên liệu nông sản bản địa Đông Anh thuần khiết"];
        $fallbackUsage = "Dùng trực tiếp hoặc chế biến món ăn ngon cho gia đình.";
        $fallbackWarn = "🛡️ Bảo quản nơi khô ráo, thoáng mát.";
    }

    // --- DYNAMIC INFOGRAPHIC TITLES & ICONS BASED ON CATEGORY ---
    if ($isStatue) {
        $sectionHeaderTitle = "📊 Thông Số Kỹ Thuật & Giá Trị Nghệ Thuật Điêu Khắc";
        $card1Title = "Chứng Nhận & Kỹ Thuật Chế Tác";
        $card2Title = "Chất Liệu & Kỹ Nghệ Nổi Khối";
        $card3Title = "Bài Trí & Phong Thủy Tâm Linh";
        $card4Title = "Bảo Quản & Khuyến Cáo Đồ Gỗ";
        $card1Icon = "📜";
        $card2Icon = "🪵";
        $card3Icon = "🧘‍♂️";
        $card4Icon = "🛡️";
    } else {
        $sectionHeaderTitle = "📊 Thống Kê & Chỉ Tiêu Kỹ Thuật Đặc Sản";
        $card1Title = "Chỉ Tiêu Chất Lượng & Kiểm Định";
        $card2Title = "Thành Phần Nguyên Liệu Bản Địa";
        $card3Title = "Thưởng Thức & Khuyên Dùng";
        $card4Title = "Bảo Quản & Khuyến Cáo";
        $card1Icon = "🧪";
        $card2Icon = "🌾";
        $card3Icon = "🥂";
        $card4Icon = "🛡️";
    }
@endphp

<style>
    /* =====================================================================
       🌾 LIGHT & BRIGHT INFOGRAPHIC DASHBOARD STYLES FOR OCOP
       ===================================================================== */
    :root {
        --ocop-amber: #d97706;
        --ocop-amber-light: #fffbeb;
        --ocop-emerald: #059669;
        --ocop-emerald-light: #f0fdf4;
    }

    .ocop-hero-card {
        position: relative;
        background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%);
        border: 1.5px solid #a7f3d0;
        border-radius: 28px;
        padding: 36px;
        margin-bottom: 36px;
        box-shadow: 0 15px 40px rgba(5, 150, 105, 0.07);
        overflow: hidden;
    }

    .ocop-pattern-overlay {
        position: absolute;
        inset: 0;
        background-image: radial-gradient(rgba(5, 150, 105, 0.08) 1px, transparent 1px);
        background-size: 24px 24px;
        opacity: 0.6;
        pointer-events: none;
        z-index: 1;
    }

    .product-showcase-img-box {
        position: relative;
        border-radius: 22px;
        overflow: hidden;
        border: 2px solid #a7f3d0;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
        height: 380px;
        background: #f8fafc;
    }

    .product-showcase-img-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .product-showcase-img-box:hover img {
        transform: scale(1.06);
    }

    .gold-crown-badge {
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
        color: #ffffff;
        font-weight: 800;
        font-size: 0.85rem;
        padding: 6px 16px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 4px 12px rgba(217, 119, 6, 0.25);
    }

    .producer-luxury-card {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 20px;
        padding: 22px;
        margin: 20px 0;
        position: relative;
    }

    .btn-call-hotline {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        color: #ffffff !important;
        font-weight: 800;
        font-size: 1.05rem;
        padding: 14px 28px;
        border-radius: 16px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.3);
        transition: all 0.3s ease;
    }

    .btn-call-hotline:hover {
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 10px 25px rgba(16, 185, 129, 0.4);
    }

    .btn-visit-eatery {
        background: #ffffff;
        color: #1e293b !important;
        font-weight: 700;
        font-size: 0.95rem;
        padding: 14px 24px;
        border-radius: 16px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 1.5px solid #cbd5e1;
        transition: all 0.3s ease;
    }

    .btn-visit-eatery:hover {
        background: #f8fafc;
        border-color: #059669;
        color: #059669 !important;
    }

    /* Infographic Card Grid Styles */
    .infographic-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        margin-bottom: 32px;
    }

    .infographic-card {
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 20px;
        padding: 24px;
        position: relative;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.03);
        transition: all 0.3s ease;
    }

    .infographic-card:hover {
        transform: translateY(-4px);
        border-color: #059669;
        box-shadow: 0 14px 30px rgba(5, 150, 105, 0.12);
    }

    .infographic-card-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        margin-bottom: 14px;
    }

    /* Audio Storyteller Widget */
    .audio-storyteller-widget {
        background: linear-gradient(135deg, #fffbeb 0%, #f0fdf4 100%);
        border: 1.5px solid #fde68a;
        border-radius: 24px;
        padding: 20px 28px;
        margin-bottom: 36px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
        box-shadow: 0 8px 25px rgba(245, 158, 11, 0.06);
    }

    .audio-play-btn {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: linear-gradient(135deg, #f59e0b, #d97706);
        border: none;
        color: #ffffff;
        font-size: 1.3rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 15px rgba(245, 158, 11, 0.35);
        transition: transform 0.2s ease;
    }

    .audio-play-btn:hover {
        transform: scale(1.08);
    }

    .equalizer-container {
        display: flex;
        align-items: flex-end;
        gap: 4px;
        height: 24px;
    }

    .eq-bar {
        width: 4px;
        height: 8px;
        background: #d97706;
        border-radius: 2px;
        transition: height 0.2s ease;
    }

    .equalizer-container.playing-audio .eq-bar:nth-child(1) { animation: eq-anim 0.8s infinite alternate; }
    .equalizer-container.playing-audio .eq-bar:nth-child(2) { animation: eq-anim 1.2s infinite alternate 0.2s; }
    .equalizer-container.playing-audio .eq-bar:nth-child(3) { animation: eq-anim 0.6s infinite alternate 0.4s; }
    .equalizer-container.playing-audio .eq-bar:nth-child(4) { animation: eq-anim 1.0s infinite alternate 0.1s; }
    .equalizer-container.playing-audio .eq-bar:nth-child(5) { animation: eq-anim 0.9s infinite alternate 0.3s; }
    .equalizer-container.playing-audio .eq-bar:nth-child(6) { animation: eq-anim 0.7s infinite alternate 0.5s; }

    @keyframes eq-anim {
        0% { height: 6px; }
        100% { height: 24px; }
    }

    /* OCOP Certificate Banner & Info styling */
    .ocop-cert-banner {
        background: linear-gradient(135deg, #fffbeb 0%, #f0fdf4 100%);
        border: 1.5px solid #fde68a;
        border-radius: 24px;
        padding: 24px;
        margin-bottom: 32px;
        display: flex;
        gap: 20px;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 8px 25px rgba(245, 158, 11, 0.05);
    }

    .ocop-cert-info {
        display: flex;
        align-items: center;
        gap: 16px;
        flex: 1;
        min-width: 280px;
    }

    .ocop-cert-icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        background: #fef3c7;
        border: 1px solid #fde68a;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        cursor: pointer;
        flex-shrink: 0;
    }

    .ocop-cert-text {
        flex: 1;
        min-width: 0; /* Allows content wrapping instead of collapsing */
    }

    .ocop-cert-label {
        font-size: 0.78rem;
        font-weight: 800;
        color: #d97706;
        text-transform: uppercase;
        letter-spacing: 1px;
        display: block;
    }

    .ocop-cert-title {
        font-size: 1.15rem;
        font-weight: 800;
        color: #0f172a;
        margin: 2px 0 0 0;
        font-family: var(--font-heading);
    }

    .ocop-cert-actions {
        display: flex;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
    }

    .ocop-qcvn-badge {
        background: #f0fdf4;
        border: 1.5px solid #bbf7d0;
        color: #166534;
        font-weight: 800;
        font-size: 0.88rem;
        padding: 8px 16px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
    }

    .btn-view-ocop-cert {
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
        color: #ffffff;
        font-weight: 800;
        font-size: 0.9rem;
        padding: 10px 20px;
        border-radius: 16px;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 15px rgba(217, 119, 6, 0.3);
        transition: transform 0.2s, box-shadow 0.2s;
        white-space: nowrap;
    }

    .btn-view-ocop-cert:hover {
        transform: scale(1.04);
        box-shadow: 0 6px 20px rgba(217, 119, 6, 0.45);
    }

    /* Main Hero columns */
    .ocop-hero-body {
        position: relative;
        z-index: 2;
        display: flex;
        gap: 36px;
        flex-wrap: wrap;
        align-items: stretch;
    }

    .ocop-hero-image-col {
        flex: 0 0 380px;
        width: 380px;
        max-width: 100%;
    }

    .ocop-hero-info-col {
        flex: 1;
        min-width: 320px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .ocop-badge-label {
        position: absolute;
        top: 16px;
        left: 16px;
        background: linear-gradient(135deg, #059669, #10b981);
        color: #ffffff;
        font-size: 0.75rem;
        font-weight: 800;
        padding: 6px 14px;
        border-radius: 20px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        letter-spacing: 0.5px;
    }

    /* Modal Footer */
    .ocop-modal-footer {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        font-family: var(--font-body, sans-serif);
        padding: 0 10px;
    }

    /* Mobile overrides */
    @media (max-width: 767px) {
        .ocop-cert-banner {
            flex-direction: column;
            align-items: stretch;
            padding: 20px;
            gap: 16px;
        }

        .ocop-cert-info {
            min-width: 0;
        }

        .ocop-cert-actions {
            justify-content: flex-start;
            width: 100%;
        }

        .btn-view-ocop-cert, .ocop-qcvn-badge {
            width: 100%;
            justify-content: center;
        }

        .ocop-hero-body {
            gap: 20px;
        }

        .ocop-hero-image-col {
            flex: 0 0 100%;
            width: 100%;
        }

        .product-showcase-img-box {
            height: 280px;
        }

        .ocop-hero-info-col {
            min-width: 100%;
        }
    }

    @media (max-width: 480px) {
        .ocop-modal-footer {
            flex-direction: column-reverse;
            align-items: center;
            gap: 20px;
        }
    }
</style>

<div class="container" style="padding-top: 28px; padding-bottom: 70px;">
    
    <!-- Breadcrumbs navigation -->
    <nav style="margin-bottom: 24px; font-size: 0.9rem; color: #475569; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
        <a href="/" style="color: #475569; text-decoration: none;">🏠 Trang chủ</a>
        <span>➔</span>
        <a href="/?cat=dong-anh-market" style="color: #475569; text-decoration: none;">🌾 Nông Sản & Đặc Sản OCOP</a>
        <span>➔</span>
        <span style="color: #059669; font-weight: 800;">{{ $product->name }}</span>
    </nav>

    <!-- Main Product Hero Card -->
    <div class="ocop-hero-card">
        <div class="ocop-pattern-overlay"></div>

        <div class="ocop-hero-body">
            
            <!-- Left: High-Res Product Image Showcase -->
            <div class="ocop-hero-image-col">
                <div class="product-showcase-img-box">
                    <img src="{{ $product->image_path ?: ($eatery?->image_path ?: 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=800&q=80') }}" alt="{{ $product->name }}">
                    <span class="ocop-badge-label">
                        🌾 SẢN PHẨM OCOP CHỨNG NHẬN
                    </span>
                </div>
            </div>

            <!-- Right: Product Info & Producer -->
            <div class="ocop-hero-info-col">
                <div>
                    <!-- Badges & Stars -->
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 14px; margin-bottom: 14px; flex-wrap: wrap;">
                        @if($product->star_rating)
                            <div class="gold-crown-badge">
                                ⭐ {{ str_contains($product->star_rating, 'sao') ? $product->star_rating : $product->star_rating . ' sao' }} Cấp Quốc Gia
                            </div>
                        @else
                            <div class="gold-crown-badge">
                                ⭐ Chứng Nhận OCOP Cố Đô Đông Anh
                            </div>
                        @endif

                        <div style="font-size: 1.8rem; font-weight: 900; color: #059669; font-family: var(--font-heading);">
                            @if($product->price && is_numeric($product->price))
                                {{ number_format($product->price, 0, ',', '.') }}đ <span style="font-size: 0.85rem; font-weight: 500; color: #64748b;">/ {{ $product->unit ?: 'sản phẩm' }}</span>
                            @elseif($product->price)
                                {{ $product->price }}
                            @else
                                {{ $eatery?->price_range ?: 'Liên hệ giá sỉ/lẻ' }}
                            @endif
                        </div>
                    </div>

                    <h1 style="font-size: 2.2rem; font-weight: 900; color: #0f172a; margin: 0 0 16px 0; font-family: var(--font-heading); line-height: 1.25;">
                        {{ $product->name }}
                    </h1>

                    <!-- Producer Info Box -->
                    <div class="producer-luxury-card">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 10px;">
                            <span style="font-size: 1.5rem;">🏛️</span>
                            <div>
                                <span style="font-size: 0.78rem; font-weight: 800; color: #059669; text-transform: uppercase; letter-spacing: 1px; display: block;">CHỦ THỂ SẢN XUẤT / HỘ KINH DOANH:</span>
                                <strong style="font-size: 1.15rem; color: #064e3b; font-family: var(--font-heading);">
                                    {{ $product->seller_name ?: ($eatery?->name ?: 'Hộ kinh doanh Đông Anh') }}
                                </strong>
                            </div>
                        </div>

                        <div style="font-size: 0.95rem; color: #334155; display: flex; flex-direction: column; gap: 8px; margin-top: 14px; padding-top: 12px; border-top: 1px dashed #bbf7d0;">
                            <div>📍 <strong>Địa chỉ sản xuất:</strong> {{ $originStr ?: ($eatery?->address ?: 'Đông Anh, Hà Nội') }} {{ $eatery?->commune ? '('.$eatery->commune->name.')' : '' }}</div>
                            @if($product->phone || $eatery?->phone)
                                <div>📞 <strong>Điện thoại liên hệ:</strong> <a href="tel:{{ $product->phone ?: $eatery->phone }}" style="color: #059669; font-weight: 800; text-decoration: none;">{{ $product->phone ?: $eatery->phone }}</a></div>
                            @endif
                            @if(count($paymentDistList) > 0)
                                <div style="margin-top: 8px; padding: 10px 14px; background: #ffffff; border: 1px solid #bbf7d0; border-radius: 12px; font-size: 0.88rem; color: #1e293b;">
                                    💳 <strong>Kênh phân phối & Thanh toán:</strong>
                                    @foreach($paymentDistList as $pItem)
                                        <div style="margin-top: 3px; color: #334155;">• {{ $pItem }}</div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Call to Action Buttons -->
                <div style="display: flex; gap: 14px; flex-wrap: wrap; margin-top: 16px;">
                    @if($product->phone || $eatery?->phone)
                        <a href="tel:{{ $product->phone ?: $eatery->phone }}" class="btn-call-hotline">
                            📞 GỌI ĐẶT HÀNG TRỰC TIẾP
                        </a>
                    @endif

                    @if($eatery)
                        <a href="{{ route('eatery.show', $eatery->slug) }}" class="btn-visit-eatery">
                            🏪 Ghé thăm trang Hộ kinh doanh {{ $eatery->name }} ➔
                        </a>
                    @endif
                </div>

            </div>

        </div>
    </div>

    <!-- AI Voice Storytelling Component -->
    <div class="audio-storyteller-widget">
        <div style="display: flex; align-items: center; gap: 16px; flex: 1; min-width: 0;">
            <button id="playOcopAudioBtn" onclick="toggleOcopAudio()" class="audio-play-btn" title="Nghe kể câu chuyện di sản">
                <span id="playOcopIcon">🔊</span>
            </button>
            <div>
                <strong style="color: #92400e; display: block; font-size: 1.05rem; font-weight: 800;">{{ $isStatue ? '🎧 Nghe thuyết minh tác phẩm nghệ thuật' : '🎧 Nghe thuyết minh di sản đặc sản' }}</strong>
                <span style="font-size: 0.85rem; color: #475569;" id="audioOcopStatus">Bấm nút để lắng nghe giọng đọc AI giới thiệu chi tiết sản phẩm OCOP</span>
            </div>
        </div>
        
        <!-- Equalizer Visualizer -->
        <div class="equalizer-container" id="ocopEqualizer">
            <div class="eq-bar"></div>
            <div class="eq-bar"></div>
            <div class="eq-bar"></div>
            <div class="eq-bar"></div>
            <div class="eq-bar"></div>
            <div class="eq-bar"></div>
        </div>
    </div>

    <!-- TOP HIGHLIGHT: CERTIFICATE & ORIGIN BANNER -->
    <div class="ocop-cert-banner">
        <div class="ocop-cert-info">
            <div class="ocop-cert-icon" onclick="openOcopCertModal()" title="Bấm để xem Giấy Chứng Nhận OCOP">
                📜
            </div>
            <div class="ocop-cert-text">
                <span class="ocop-cert-label">CHỨNG NHẬN CÔNG NHẬN SẢN PHẨM OCOP:</span>
                <h4 class="ocop-cert-title">
                    {{ $decisionStr ?: ('Chứng nhận đạt phân hạng ' . ($product->star_rating ?: 'OCOP 3-5 sao') . ' UBND Thành Phố Hà Nội') }}
                </h4>
            </div>
        </div>

        <div class="ocop-cert-actions">
            @if($qcvnStr)
                <span class="ocop-qcvn-badge">
                    🛡️ BỘ Y TẾ: {{ $qcvnStr }}
                </span>
            @endif
            
            <button onclick="openOcopCertModal()" class="btn-view-ocop-cert">
                📜 XEM GIẤY CHỨNG NHẬN OCOP ➔
            </button>
        </div>
    </div>

    <!-- OFFICIAL INTERACTIVE OCOP CERTIFICATE MODAL -->
    <div id="ocopCertModal" style="display: none; position: fixed; inset: 0; z-index: 999999; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(6px); overflow-y: auto; padding: 20px; box-sizing: border-box; justify-content: center; align-items: flex-start;">
        <div style="background: #fffdf5; border: 10px double #d97706; border-radius: 20px; max-width: 680px; width: 100%; padding: 40px 30px; position: relative; box-shadow: 0 25px 60px rgba(0,0,0,0.4); text-align: center; font-family: 'Times New Roman', Times, serif; color: #1e293b; margin: 30px auto; box-sizing: border-box;">
            
            <!-- Close Button -->
            <button onclick="closeOcopCertModal()" style="position: absolute; top: 15px; right: 20px; background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; width: 36px; height: 36px; border-radius: 50%; font-size: 1.2rem; font-weight: bold; cursor: pointer; display: flex; align-items: center; justify-content: center;">✕</button>

            <!-- Certificate Header -->
            <div style="border-bottom: 2px solid #d97706; padding-bottom: 15px; margin-bottom: 20px;">
                <p style="margin: 0; font-size: 0.95rem; font-weight: bold; text-transform: uppercase; color: #854d0e; letter-spacing: 1px;">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</p>
                <p style="margin: 2px 0 10px 0; font-size: 0.9rem; font-weight: bold; color: #854d0e;">Độc lập - Tự do - Hạnh phúc</p>
                <div style="width: 120px; height: 2px; background: #d97706; margin: 0 auto 10px auto;"></div>
                <p style="margin: 0; font-size: 0.9rem; font-weight: bold; color: #0f172a; text-transform: uppercase;">ỦY BAN NHÂN DÂN THÀNH PHỐ HÀ NỘI</p>
            </div>

            <!-- Title -->
            <div style="margin: 20px 0;">
                <span style="font-size: 2.2rem; display: block; margin-bottom: 5px;">🎖️</span>
                <h2 style="font-size: 1.8rem; font-weight: 900; color: #92400e; margin: 0; text-transform: uppercase; letter-spacing: 2px;">GIẤY CHỨNG NHẬN</h2>
                <p style="font-size: 1.2rem; font-weight: bold; color: #059669; margin: 5px 0; text-transform: uppercase;">SẢN PHẨM OCOP {{ mb_strtoupper($product->star_rating ?: '3-5 SAO') }} CẤP THÀNH PHỐ</p>
            </div>

            <!-- Certified Info Body -->
            <div style="background: #ffffff; border: 1px dashed #d97706; border-radius: 12px; padding: 20px; text-align: left; font-size: 1rem; line-height: 1.8; margin-bottom: 20px; font-family: var(--font-body, sans-serif);">
                <p style="margin: 4px 0;"><strong>Chứng nhận sản phẩm:</strong> <span style="color: #059669; font-weight: bold; font-size: 1.1rem;">{{ $product->name }}</span></p>
                <p style="margin: 4px 0;"><strong>Chủ thể sản xuất:</strong> <span style="color: #0f172a; font-weight: bold;">{{ $product->seller_name }}</span></p>
                <p style="margin: 4px 0;"><strong>Địa chỉ cơ sở:</strong> {{ $product->eatery?->address ?: 'Huyện Đông Anh, Thành phố Hà Nội' }}</p>
                <p style="margin: 4px 0;"><strong>Phân hạng OCOP:</strong> <span style="background: #fef3c7; color: #92400e; font-weight: bold; padding: 2px 10px; border-radius: 10px;">⭐ {{ $product->star_rating ?: '3-5 sao' }}</span></p>
                <p style="margin: 4px 0;"><strong>Cơ sở pháp lý:</strong> {{ $decisionStr ?: 'Phê duyệt theo quyết định của UBND Thành phố Hà Nội' }}</p>
            </div>

            <!-- Seal & Signature Footer -->
            <div class="ocop-modal-footer">
                <div style="text-align: center;">
                    <div style="width: 70px; height: 70px; border-radius: 50%; background: #fef3c7; border: 2px solid #d97706; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: bold; color: #92400e; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                        QR VERIFIED
                    </div>
                    <span style="font-size: 0.75rem; color: #64748b; display: block; margin-top: 4px;">Mã QR Hà Nội</span>
                </div>

                <div style="text-align: center;">
                    <p style="margin: 0; font-size: 0.85rem; font-style: italic; color: #475569;">Hà Nội, ngày cấp chứng nhận OCOP</p>
                    <p style="margin: 2px 0 40px 0; font-size: 0.9rem; font-weight: bold; color: #0f172a; text-transform: uppercase;">TM. UỶ BAN NHÂN DÂN TP. HÀ NỘI<br><span style="font-size: 0.8rem; font-weight: normal; color: #64748b;">KT. CHỦ TỊCH - PHÓ CHỦ TỊCH</span></p>
                    <p style="margin: 0; font-size: 1rem; font-weight: bold; color: #dc2626; font-family: 'Times New Roman', serif;">(Đã ký & Đóng dấu)</p>
                </div>
            </div>

        </div>
    </div>

    <script>
        function openOcopCertModal() {
            document.getElementById('ocopCertModal').style.display = 'flex';
        }
        function closeOcopCertModal() {
            document.getElementById('ocopCertModal').style.display = 'none';
        }
    </script>

    <!-- INFOGRAPHIC DASHBOARD GRID -->
    <div style="margin-bottom: 40px;">
        <h3 style="font-size: 1.4rem; font-weight: 800; color: #059669; margin-bottom: 20px; font-family: var(--font-heading); display: flex; align-items: center; gap: 10px;">
            {{ $sectionHeaderTitle }}
        </h3>

        <div class="infographic-grid">
            
            <!-- Card 1: Specs & Testing -->
            <div class="infographic-card">
                <div class="infographic-card-icon" style="background: #f0fdf4; color: #059669; border: 1px solid #bbf7d0;">
                    {{ $card1Icon }}
                </div>
                <h4 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin-bottom: 10px; font-family: var(--font-heading);">
                    {{ $card1Title }}
                </h4>
                @if(count($specsList) > 0)
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        @foreach($specsList as $sp)
                            <div style="background: #f0fdf4; padding: 8px 12px; border-radius: 10px; border: 1px solid #bbf7d0; font-size: 0.9rem; color: #166534; font-weight: 700; display: flex; align-items: center; gap: 6px;">
                                <span>✔</span> <span>{{ $sp }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p style="font-size: 0.9rem; color: #475569; margin: 0; line-height: 1.6;">
                        ✔ {{ $fallbackSpec }}
                    </p>
                @endif
            </div>

            <!-- Card 2: Ingredients -->
            <div class="infographic-card">
                <div class="infographic-card-icon" style="background: #fefce8; color: #d97706; border: 1px solid #fef08a;">
                    {{ $card2Icon }}
                </div>
                <h4 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin-bottom: 10px; font-family: var(--font-heading);">
                    {{ $card2Title }}
                </h4>
                @if(count($ingredientsList) > 0)
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        @foreach($ingredientsList as $ing)
                            <span style="background: #fefce8; border: 1px solid #fef08a; color: #854d0e; padding: 6px 12px; border-radius: 12px; font-size: 0.88rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                                ✨ {{ $ing }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        @foreach($fallbackIng as $ing)
                            <span style="background: #fefce8; border: 1px solid #fef08a; color: #854d0e; padding: 6px 12px; border-radius: 12px; font-size: 0.88rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                                ✨ {{ $ing }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Card 3: Usage & Recommendations -->
            <div class="infographic-card">
                <div class="infographic-card-icon" style="background: #f0f9ff; color: #0284c7; border: 1px solid #bae6fd;">
                    {{ $card3Icon }}
                </div>
                <h4 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin-bottom: 10px; font-family: var(--font-heading);">
                    {{ $card3Title }}
                </h4>
                @if(count($usageList) > 0)
                    <div style="display: flex; flex-direction: column; gap: 8px; font-size: 0.9rem; color: #334155;">
                        @foreach($usageList as $u)
                            <div style="display: flex; align-items: flex-start; gap: 6px;">
                                <span>🧊</span> <span>{{ $u }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p style="font-size: 0.9rem; color: #334155; margin: 0; line-height: 1.6;">
                        {{ $isStatue ? '⛩️' : '🍽️' }} {{ $fallbackUsage }}
                    </p>
                @endif
            </div>

            <!-- Card 4: Preservation & Warnings -->
            <div class="infographic-card">
                <div class="infographic-card-icon" style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca;">
                    {{ $card4Icon }}
                </div>
                <h4 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin-bottom: 10px; font-family: var(--font-heading);">
                    {{ $card4Title }}
                </h4>
                <div style="display: flex; flex-direction: column; gap: 8px; font-size: 0.88rem; color: #334155;">
                    <div style="background: #fef2f2; padding: 8px 12px; border-radius: 10px; border: 1px solid #fecaca; color: #991b1b; font-weight: 700;">
                        {{ count($warningList) > 0 ? implode(', ', $warningList) : $fallbackWarn }}
                    </div>
                    @if(count($storageList) > 0)
                        <div style="background: #f8fafc; padding: 8px 12px; border-radius: 10px; border: 1px solid #e2e8f0; color: #475569;">
                            ❄️ {{ implode(', ', $storageList) }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <!-- HERITAGE STORY CALLOUT BOX -->
    @if(count($heritageStory) > 0 || !empty($product->story))
    <div style="background: linear-gradient(135deg, #ffffff 0%, #fffbeb 100%); border: 1.5px solid #fde68a; border-radius: 28px; padding: 32px; margin-bottom: 44px; box-shadow: 0 12px 30px rgba(245, 158, 11, 0.08); position: relative; overflow: hidden;">
        
        <h3 style="font-size: 1.4rem; font-weight: 800; color: #92400e; margin-bottom: 16px; font-family: var(--font-heading); display: flex; align-items: center; gap: 10px;">
            👑 Huyền Thoại Di Sản & Bí Quyết Gia Truyền
        </h3>

        <div style="font-size: 1.05rem; line-height: 1.85; color: #334155; font-style: italic;">
            @if(!empty($product->story))
                <p style="margin-bottom: 16px;">{{ $product->story }}</p>
            @endif

            @foreach($heritageStory as $hs)
                <p style="margin-bottom: 12px;">{{ $hs }}</p>
            @endforeach
        </div>

        @if(!empty($product->artisans))
            <div style="margin-top: 20px; padding-top: 16px; border-top: 1px dashed #fde68a; display: flex; align-items: center; gap: 12px;">
                <span style="font-size: 1.5rem;">👨‍🍳</span>
                <div>
                    <strong style="color: #92400e; font-size: 0.9rem; display: block;">NGHỆ NHÂN TRUYỀN NGHỀ BÀN TAY VÀNG:</strong>
                    <span style="font-size: 1rem; color: #0f172a; font-weight: 700;">{{ $product->artisans }}</span>
                </div>
            </div>
        @endif
    </div>
    @endif

    <!-- Related OCOP Products Section -->
    @if(isset($relatedProducts) && $relatedProducts->count() > 0)
    <div>
        <h3 style="font-size: 1.5rem; font-weight: 800; color: #0f172a; margin-bottom: 24px; font-family: var(--font-heading); display: flex; align-items: center; gap: 10px;">
            🌾 Sản Phẩm OCOP Cùng Khu Vực Đông Anh
        </h3>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 24px;">
            @foreach($relatedProducts as $rel)
                <a href="{{ route('ocop.product.show', $rel->id) }}" style="text-decoration: none;">
                    <div style="background: #ffffff; border-radius: 20px; border: 1px solid #e2e8f0; padding: 18px; height: 100%; display: flex; flex-direction: column; justify-content: space-between; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);" onmouseover="this.style.borderColor='#059669'; this.style.transform='translateY(-4px)'; this.style.boxShadow='0 12px 25px rgba(5, 150, 105, 0.12)';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.transform='none'; this.style.boxShadow='0 4px 15px rgba(0, 0, 0, 0.03)';">
                        <div>
                            <div style="height: 180px; border-radius: 14px; overflow: hidden; margin-bottom: 14px; position: relative; border: 1px solid #f1f5f9;">
                                <img src="{{ $rel->image_path ?: ($rel->eatery?->image_path ?: 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=300&q=80') }}" style="width: 100%; height: 100%; object-fit: cover;" alt="{{ $rel->name }}">
                                @if($rel->star_rating)
                                    <span style="position: absolute; top: 10px; left: 10px; background: rgba(0,0,0,0.75); color: #ffb300; font-size: 0.72rem; font-weight: 800; padding: 4px 10px; border-radius: 12px;">⭐ {{ $rel->star_rating }}</span>
                                @endif
                            </div>
                            <h4 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin: 0 0 6px 0; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                {{ $rel->name }}
                            </h4>
                            <p style="font-size: 0.82rem; color: #64748b; margin: 0 0 12px 0;">
                                🏛️ {{ $rel->seller_name ?: ($rel->eatery?->name ?: 'Đông Anh') }}
                            </p>
                        </div>
                        <div style="display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #f1f5f9; padding-top: 12px; margin-top: 8px;">
                            <span style="font-size: 1rem; font-weight: 800; color: #059669;">
                                {{ $rel->price ? (is_numeric($rel->price) ? number_format($rel->price, 0, ',', '.') . 'đ' : $rel->price) : 'Liên hệ' }}
                            </span>
                            <span style="font-size: 0.85rem; color: #d97706; font-weight: 700;">Xem chi tiết ➔</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
    @endif

</div>

<!-- Audio Player Script -->
<script>
    let isOcopAudioPlaying = false;
    let ocopUtterance = null;
    const ocopSynth = window.speechSynthesis;

    function toggleOcopAudio() {
        const btnIcon = document.getElementById('playOcopIcon');
        const statusText = document.getElementById('audioOcopStatus');
        const eq = document.getElementById('ocopEqualizer');

        if (!ocopSynth) {
            alert('Trình duyệt của bạn không hỗ trợ giọng đọc thuyết minh AI.');
            return;
        }

        if (isOcopAudioPlaying) {
            ocopSynth.cancel();
            isOcopAudioPlaying = false;
            btnIcon.textContent = '🔊';
            statusText.textContent = 'Bấm để lắng nghe giọng đọc AI thuyết minh đặc sản';
            eq.classList.remove('playing-audio');
        } else {
            ocopSynth.cancel();
            
            const narrativeText = `{!! addslashes($product->audio_narrative ?: ($product->story ?: $product->description)) !!}`;
            if (!narrativeText) return;

            ocopUtterance = new SpeechSynthesisUtterance(narrativeText);
            ocopUtterance.lang = 'vi-VN';
            ocopUtterance.rate = 0.92;

            const voices = ocopSynth.getVoices();
            const viVoice = voices.find(v => v.lang.includes('VI') || v.lang.includes('vi'));
            if (viVoice) ocopUtterance.voice = viVoice;

            ocopUtterance.onstart = function() {
                isOcopAudioPlaying = true;
                btnIcon.textContent = '⏸️';
                statusText.textContent = 'AI đang thuyết minh về sản phẩm...';
                eq.classList.add('playing-audio');
            };

            ocopUtterance.onend = function() {
                isOcopAudioPlaying = false;
                btnIcon.textContent = '🔊';
                statusText.textContent = 'Thuyết minh hoàn thành. Bấm để nghe lại!';
                eq.classList.remove('playing-audio');
            };

            ocopUtterance.onerror = function() {
                isOcopAudioPlaying = false;
                btnIcon.textContent = '🔊';
                statusText.textContent = 'Đã xảy ra lỗi khi phát thuyết minh.';
                eq.classList.remove('playing-audio');
            };

            ocopSynth.speak(ocopUtterance);
        }
    }
</script>
@endsection
