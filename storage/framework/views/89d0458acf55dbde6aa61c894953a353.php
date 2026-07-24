<?php $__env->startSection('title', $stallName . ' — ' . $eatery->name . ' | DongAnh Map'); ?>
<?php $__env->startSection('meta_description', 'Xem chi tiết gian hàng ' . $stallName . ' tại ' . $eatery->name . ', Đông Anh — thông tin tiểu thương, sản phẩm niêm yết, bản đồ vị trí và đánh giá từ khách hàng.'); ?>
<?php $__env->startSection('og_image', $eatery->image_path ?: 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=800&q=80'); ?>
<?php $__env->startSection('canonical_url', route('market.stall.show', ['marketSlug' => $marketSlug, 'stallSlug' => $stallSlug])); ?>

<?php $__env->startSection('content'); ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
    /* ====================== STALL DETAIL DYNAMIC DESIGN SYSTEM ====================== */
    :root {
        --stall-primary: var(--primary, #0ea5e9);
        --stall-primary-grad: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%);
        --stall-primary-soft: rgba(14, 165, 233, 0.08);
        --stall-gold: #F59E0B;
        --stall-green: #10B981;
        --stall-green-soft: rgba(16, 185, 129, 0.1);
        --stall-radius: 20px;
        --stall-radius-sm: 12px;
    }

    body {
        font-family: 'Be Vietnam Pro', sans-serif !important;
        background: var(--bg-base) !important;
        color: var(--text-main) !important;
    }

    a { text-decoration: none !important; }

    .stall-page-wrapper {
        max-width: 1300px;
        margin: 0 auto;
        padding: 24px 20px 80px;
        animation: fadeInScale 0.4s ease-out forwards;
    }

    /* ---- Animations ---- */
    @keyframes gradientMove {
        0% { background-position: 0% 50%; }
        100% { background-position: 200% 50%; }
    }
    @keyframes floatAvatar {
        0%, 100% { transform: translateY(0) scale(1); }
        50% { transform: translateY(-5px) scale(1.03); }
    }
    @keyframes pulseGlow {
        0%, 100% { box-shadow: 0 8px 24px rgba(14, 165, 233, 0.25); }
        50% { box-shadow: 0 14px 36px rgba(14, 165, 233, 0.45); }
    }
    @keyframes fadeInScale {
        from { opacity: 0; transform: translateY(12px) scale(0.99); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    /* ---- Breadcrumb ---- */
    .stall-breadcrumb {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.84rem;
        color: var(--text-muted);
        margin-bottom: 24px;
        flex-wrap: wrap;
    }
    .stall-breadcrumb a {
        color: var(--primary);
        font-weight: 600;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .stall-breadcrumb a:hover {
        color: #0284c7;
        transform: translateX(1px);
    }

    /* ---- Hero Header ---- */
    .stall-hero {
        background: linear-gradient(135deg, rgba(14,165,233,0.06) 0%, rgba(6,182,212,0.02) 50%, rgba(16,185,129,0.04) 100%), var(--bg-card);
        border: 1px solid var(--border-glow);
        border-radius: 24px;
        padding: 30px 34px;
        margin-bottom: 28px;
        display: flex;
        gap: 28px;
        align-items: flex-start;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        backdrop-filter: blur(16px);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .stall-hero:hover {
        box-shadow: 0 16px 40px rgba(14, 165, 233, 0.1);
    }
    .stall-hero::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 5px;
        background: linear-gradient(90deg, #0ea5e9, #06b6d4, #10b981, #f59e0b, #0ea5e9);
        background-size: 200% 100%;
        animation: gradientMove 6s linear infinite;
        border-radius: 24px 24px 0 0;
    }
    .stall-avatar {
        width: 88px;
        height: 88px;
        border-radius: 50%;
        background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.2rem;
        flex-shrink: 0;
        animation: pulseGlow 4s ease-in-out infinite, floatAvatar 5s ease-in-out infinite;
        border: 3px solid rgba(255, 255, 255, 0.9);
        box-shadow: 0 10px 30px rgba(14, 165, 233, 0.3);
    }
    .stall-hero-info { flex: 1; }
    .stall-hero-name {
        font-size: 1.75rem;
        font-weight: 900;
        color: var(--text-main);
        margin: 0 0 6px;
        line-height: 1.2;
        letter-spacing: -0.3px;
        background: linear-gradient(135deg, var(--text-main) 30%, var(--primary) 100%);
        -webkit-background-clip: text;
    }
    .stall-hero-market {
        font-size: 0.9rem;
        color: var(--primary);
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 14px;
    }
    .stall-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 10px;
    }
    .stall-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 0.78rem;
        font-weight: 700;
        border: 1px solid;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .stall-badge:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }
    .badge-green { background: var(--stall-green-soft); color: var(--stall-green); border-color: rgba(16,185,129,0.3); }
    .badge-sky { background: rgba(14, 165, 233, 0.08); color: var(--primary); border-color: rgba(14, 165, 233, 0.3); }
    .badge-gold { background: rgba(245,158,11,0.12); color: #B45309; border-color: rgba(245,158,11,0.3); }
    .badge-blue { background: rgba(59,130,246,0.08); color: #2563EB; border-color: rgba(59,130,246,0.25); }

    .stall-avg-rating {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 14px;
    }
    .stall-stars-large {
        color: var(--stall-gold);
        font-size: 1.25rem;
        letter-spacing: 1px;
        filter: drop-shadow(0 2px 4px rgba(245, 158, 11, 0.3));
    }
    .stall-rating-num {
        font-size: 1.45rem;
        font-weight: 900;
        color: var(--text-main);
    }
    .stall-rating-count {
        font-size: 0.82rem;
        color: var(--text-muted);
    }

    /* ---- Main 2-column grid ---- */
    .stall-main-grid {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 24px;
        margin-bottom: 32px;
    }
    @media (max-width: 900px) {
        .stall-main-grid { grid-template-columns: 1fr; }
        .stall-hero { flex-direction: column; padding: 22px; }
        .stall-hero-name { font-size: 1.4rem; }
    }

    /* ---- Panels ---- */
    .stall-panel {
        background: var(--bg-card);
        border: 1px solid var(--border-glow);
        border-radius: var(--stall-radius);
        overflow: hidden;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.03);
        transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.3s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.3s ease;
    }
    .stall-panel:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 36px rgba(14, 165, 233, 0.08), 0 4px 12px rgba(0, 0, 0, 0.03);
        border-color: rgba(14, 165, 233, 0.3);
    }
    .stall-panel-header {
        padding: 18px 22px;
        border-bottom: 1px solid var(--border-glow);
        display: flex;
        align-items: center;
        gap: 12px;
        background: linear-gradient(90deg, rgba(14, 165, 233, 0.03) 0%, transparent 100%);
    }
    .stall-panel-header h3 {
        font-size: 0.98rem;
        font-weight: 800;
        margin: 0;
        color: var(--text-main);
        letter-spacing: -0.2px;
    }
    .panel-icon {
        width: 36px;
        height: 36px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    }
    .panel-body { padding: 22px; }

    /* ---- Info rows ---- */
    .info-row {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 12px 14px;
        margin: 0 -14px;
        border-radius: 12px;
        border-bottom: 1px solid var(--border-glow);
        font-size: 0.88rem;
        transition: background 0.2s ease, transform 0.2s ease;
    }
    .info-row:last-child { border-bottom: none; }
    .info-row:hover {
        background: rgba(14, 165, 233, 0.04);
        transform: translateX(3px);
    }
    .info-icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        background: rgba(14, 165, 233, 0.09);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary);
        font-size: 0.95rem;
        flex-shrink: 0;
    }
    .info-label {
        font-size: 0.72rem;
        font-weight: 800;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: block;
        margin-bottom: 2px;
    }
    .info-value {
        font-size: 0.92rem;
        font-weight: 600;
        color: var(--text-main);
    }

    /* ---- Products List ---- */
    .product-card {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px 18px;
        margin: 0 -16px;
        border-radius: 16px;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        border-bottom: 1px solid var(--border-glow);
        position: relative;
    }
    .product-card:last-child { border-bottom: none; }
    .product-card:hover {
        background: linear-gradient(90deg, rgba(14, 165, 233, 0.06) 0%, rgba(6, 182, 212, 0.02) 100%);
        box-shadow: 0 6px 20px rgba(14, 165, 233, 0.1);
        transform: scale(1.01);
        border-bottom-color: transparent;
    }
    #stallProductsContainer::-webkit-scrollbar {
        width: 6px;
    }
    #stallProductsContainer::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.03);
        border-radius: 10px;
    }
    #stallProductsContainer::-webkit-scrollbar-thumb {
        background: rgba(14, 165, 233, 0.3);
        border-radius: 10px;
    }
    #stallProductsContainer::-webkit-scrollbar-thumb:hover {
        background: var(--primary);
    }

    /* ---- Category Filter Tabs ---- */
    .category-tabs-bar {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        padding: 12px 18px;
        border-bottom: 1px solid var(--border-glow);
        background: rgba(14, 165, 233, 0.02);
        scrollbar-width: none;
    }
    .category-tabs-bar::-webkit-scrollbar { display: none; }
    .cat-tab-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 20px;
        border: 1px solid var(--border-glow);
        background: var(--bg-card);
        color: var(--text-muted);
        font-size: 0.8rem;
        font-weight: 700;
        cursor: pointer;
        white-space: nowrap;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .cat-tab-btn:hover {
        border-color: var(--primary);
        color: var(--primary);
        transform: translateY(-1px);
    }
    .cat-tab-btn.active {
        background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%);
        color: #fff;
        border-color: transparent;
        box-shadow: 0 4px 14px rgba(14, 165, 233, 0.3);
    }
    .cat-count-badge {
        font-size: 0.7rem;
        padding: 2px 7px;
        border-radius: 12px;
        background: rgba(0, 0, 0, 0.06);
    }
    .cat-tab-btn.active .cat-count-badge {
        background: rgba(255, 255, 255, 0.25);
        color: #fff;
    }
    .product-img {
        width: 72px;
        height: 72px;
        border-radius: 16px;
        object-fit: cover;
        flex-shrink: 0;
        box-shadow: 0 4px 14px rgba(0,0,0,0.12);
        transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .product-card:hover .product-img {
        transform: scale(1.08) rotate(1deg);
    }
    .product-img-placeholder {
        width: 72px;
        height: 72px;
        border-radius: 16px;
        background: linear-gradient(135deg, #0ea5e9, #06b6d4);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        flex-shrink: 0;
        box-shadow: 0 6px 16px rgba(14, 165, 233, 0.3);
        transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .product-card:hover .product-img-placeholder {
        transform: scale(1.08);
    }
    .product-name {
        font-weight: 800;
        font-size: 0.96rem;
        color: var(--text-main);
        margin-bottom: 4px;
        line-height: 1.3;
    }
    .product-origin {
        font-size: 0.74rem;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .product-price-chip {
        display: inline-flex;
        align-items: baseline;
        gap: 3px;
        background: linear-gradient(135deg, rgba(14, 165, 233, 0.1) 0%, rgba(16, 185, 129, 0.1) 100%);
        border: 1px solid rgba(14, 165, 233, 0.25);
        border-radius: 10px;
        padding: 4px 12px;
        margin-top: 6px;
    }
    .product-price-num {
        font-weight: 900;
        font-size: 0.98rem;
        color: var(--primary);
    }
    .product-price-unit {
        font-size: 0.7rem;
        color: var(--text-muted);
    }
    .btn-order {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 18px;
        background: linear-gradient(135deg, #0ea5e9, #06b6d4);
        color: #fff;
        border: none;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.82rem;
        cursor: pointer;
        transition: all 0.25s ease;
        white-space: nowrap;
        box-shadow: 0 4px 14px rgba(14, 165, 233, 0.3);
        flex-shrink: 0;
    }
    .btn-order:hover {
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 8px 22px rgba(14, 165, 233, 0.45);
        background: linear-gradient(135deg, #0284c7, #0891b2);
    }
    .btn-order:active { transform: scale(0.95); }
    .btn-order:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

    /* ---- Leaflet Map Panel ---- */
    #stallMap {
        height: 320px;
        width: 100%;
        border-radius: 0 0 var(--stall-radius) var(--stall-radius);
    }
    .map-direction-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 14px 22px;
        background: rgba(14, 165, 233, 0.08);
        color: var(--primary);
        font-weight: 700;
        font-size: 0.88rem;
        transition: all 0.25s ease;
        cursor: pointer;
        border: none;
        width: 100%;
        text-align: left;
    }
    .map-direction-btn:hover {
        background: rgba(14, 165, 233, 0.16);
        color: #0284c7;
    }
    .map-direction-btn i.bi-box-arrow-up-right {
        transition: transform 0.25s ease;
    }
    .map-direction-btn:hover i.bi-box-arrow-up-right {
        transform: translate(3px, -3px);
    }

    /* ---- QR Code Panel ---- */
    .qr-panel-body {
        padding: 22px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 14px;
    }
    .qr-box {
        background: #fff;
        border-radius: 18px;
        padding: 18px;
        border: 2px solid rgba(14, 165, 233, 0.2);
        box-shadow: 0 8px 24px rgba(14, 165, 233, 0.12);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .qr-box:hover {
        transform: scale(1.03);
        box-shadow: 0 12px 32px rgba(14, 165, 233, 0.2);
    }
    .qr-box img { width: 160px; height: 160px; display: block; }

    /* ---- Action Buttons ---- */
    .stall-action-btns {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    .btn-stall-primary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 13px 24px;
        background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%);
        color: #fff;
        border-radius: 14px;
        font-weight: 700;
        font-size: 0.9rem;
        border: none;
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        text-decoration: none !important;
        box-shadow: 0 6px 18px rgba(14, 165, 233, 0.3);
    }
    .btn-stall-primary:hover {
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 10px 25px rgba(14, 165, 233, 0.45);
        color: #fff !important;
    }
    .btn-stall-secondary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 13px 24px;
        background: var(--bg-card);
        color: var(--text-main);
        border-radius: 14px;
        font-weight: 700;
        font-size: 0.9rem;
        border: 1px solid var(--border-glow);
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        text-decoration: none !important;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.03);
    }
    .btn-stall-secondary:hover {
        border-color: var(--primary);
        color: var(--primary) !important;
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 8px 20px rgba(14, 165, 233, 0.15);
    }

    /* ---- Reviews Section ---- */
    .reviews-section {
        background: var(--bg-card);
        border: 1px solid var(--border-glow);
        border-radius: var(--stall-radius);
        overflow: hidden;
        margin-bottom: 28px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.03);
    }
    .review-form-area {
        padding: 28px;
        background: linear-gradient(180deg, rgba(14, 165, 233, 0.05) 0%, transparent 100%);
        border-bottom: 1px solid var(--border-glow);
    }
    .review-form-title {
        font-size: 1rem;
        font-weight: 800;
        color: var(--text-main);
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .star-input-row {
        display: flex;
        gap: 8px;
        margin-bottom: 14px;
    }
    .star-btn {
        font-size: 1.75rem;
        cursor: pointer;
        color: #D1D5DB;
        transition: all 0.15s cubic-bezier(0.34, 1.56, 0.64, 1);
        border: none;
        background: none;
        padding: 0;
        line-height: 1;
    }
    .star-btn.active, .star-btn:hover {
        color: var(--stall-gold);
        transform: scale(1.25);
        filter: drop-shadow(0 0 6px rgba(245,158,11,0.5));
    }
    .review-textarea {
        width: 100%;
        border-radius: 14px;
        border: 1.5px solid var(--border-glow);
        background: var(--bg-base);
        color: var(--text-main);
        padding: 14px 18px;
        font-family: 'Be Vietnam Pro', sans-serif;
        font-size: 0.9rem;
        resize: none;
        outline: none;
        transition: border-color 0.25s ease, box-shadow 0.25s ease;
        box-sizing: border-box;
    }
    .review-textarea:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.15);
    }

    .review-list { padding: 8px 0; }
    .review-item {
        padding: 20px 28px;
        border-bottom: 1px solid var(--border-glow);
        border-left: 3px solid transparent;
        transition: all 0.25s ease;
    }
    .review-item:last-child { border-bottom: none; }
    .review-item:hover {
        background: rgba(14, 165, 233, 0.02);
        border-left-color: var(--primary);
    }
    .review-top {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 8px;
    }
    .reviewer-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #0ea5e9, #06b6d4);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 800;
        font-size: 0.95rem;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(14, 165, 233, 0.25);
    }
    .reviewer-name {
        font-weight: 700;
        font-size: 0.92rem;
        color: var(--text-main);
    }
    .reviewer-time {
        font-size: 0.74rem;
        color: var(--text-muted);
        margin-top: 2px;
    }
    .reviewer-stars {
        margin-left: auto;
        color: var(--stall-gold);
        font-size: 0.9rem;
        letter-spacing: 1px;
    }
    .review-comment {
        font-size: 0.9rem;
        color: var(--text-main);
        line-height: 1.65;
        padding-left: 52px;
    }

    .no-reviews-state {
        text-align: center;
        padding: 44px 24px;
        color: var(--text-muted);
    }
    .no-reviews-state .icon { font-size: 3.2rem; margin-bottom: 12px; }
    .no-reviews-state p { font-size: 0.9rem; font-weight: 600; }

    /* ---- Flash success ---- */
    .flash-success {
        background: var(--stall-green-soft);
        border: 1px solid rgba(16,185,129,0.3);
        color: var(--stall-green);
        border-radius: 14px;
        padding: 14px 20px;
        font-weight: 700;
        font-size: 0.92rem;
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
        transition: opacity 0.5s ease, transform 0.5s ease, max-height 0.5s ease, margin 0.5s ease, padding 0.5s ease;
        max-height: 100px;
        opacity: 1;
        overflow: hidden;
        box-shadow: 0 4px 16px rgba(16, 185, 129, 0.15);
    }
    .flash-success.fade-out {
        opacity: 0;
        transform: translateY(-10px);
        max-height: 0;
        margin-bottom: 0;
        padding-top: 0;
        padding-bottom: 0;
        border-width: 0;
    }
</style>

<div class="stall-page-wrapper">

    
    <nav class="stall-breadcrumb">
        <a href="<?php echo e(route('home')); ?>"><i class="bi bi-house-fill"></i> Trang chủ</a>
        <i class="bi bi-chevron-right"></i>
        <a href="<?php echo e(route('eatery.show', $eatery->slug)); ?>"><?php echo e($eatery->name); ?></a>
        <i class="bi bi-chevron-right"></i>
        <span><?php echo e($stallName); ?></span>
    </nav>

    
    <?php if(session('review_success')): ?>
        <div class="flash-success" id="flashSuccessAlert">
            <i class="bi bi-check-circle-fill"></i>
            <?php echo e(session('review_success')); ?>

        </div>
    <?php endif; ?>

    
    <div class="stall-hero">
        <div class="stall-avatar">
            <?php
                $emojiMap = [
                    'Ăn uống' => '🍜', 'Rau củ' => '🥦', 'Thịt tươi' => '🥩',
                    'Thực phẩm khô' => '🌾', 'Khác' => '🏪'
                ];
                $stallEmoji = $emojiMap[$category] ?? '🏪';
            ?>
            <?php echo e($stallEmoji); ?>

        </div>

        <div class="stall-hero-info">
            <h1 class="stall-hero-name"><?php echo e($stallName); ?></h1>

            <div class="stall-hero-market">
                <i class="bi bi-shop"></i>
                <a href="<?php echo e(route('eatery.show', $eatery->slug)); ?>" style="color: var(--primary);">
                    <?php echo e($eatery->name); ?>

                </a>
                <span style="color: var(--text-muted); font-weight: 400;">· Đông Anh, Hà Nội</span>
            </div>

            <div class="stall-badges">
                <span class="stall-badge badge-sky">
                    <i class="bi bi-tag-fill"></i> <?php echo e($category); ?>

                </span>
                <?php if($hasQr): ?>
                    <span class="stall-badge badge-green">
                        <i class="bi bi-qr-code-scan"></i> VietQR
                    </span>
                <?php endif; ?>
                <?php if($hasSmartphone): ?>
                    <span class="stall-badge badge-blue">
                        <i class="bi bi-phone-fill"></i> Smartphone
                    </span>
                <?php endif; ?>
                <?php if($reviews->count() > 0): ?>
                    <span class="stall-badge badge-gold">
                        <i class="bi bi-star-fill"></i> <?php echo e($avgRating); ?>/5 · <?php echo e($reviews->count()); ?> đánh giá
                    </span>
                <?php endif; ?>
            </div>

            <?php if($reviews->count() > 0): ?>
                <div class="stall-avg-rating">
                    <span class="stall-stars-large">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                            <?php echo e($i <= round($avgRating) ? '★' : '☆'); ?>

                        <?php endfor; ?>
                    </span>
                    <span class="stall-rating-num"><?php echo e($avgRating); ?></span>
                    <span class="stall-rating-count">/ 5 · <?php echo e($reviews->count()); ?> lượt đánh giá</span>
                </div>
            <?php endif; ?>
        </div>

        
        <div style="display: flex; flex-direction: column; gap: 10px; flex-shrink: 0;">
            <?php if($sellerPhone): ?>
                <a href="tel:<?php echo e($sellerPhone); ?>" class="btn-stall-primary">
                    <i class="bi bi-telephone-fill"></i> Gọi ngay
                </a>
                <a href="https://zalo.me/<?php echo e($sellerPhone); ?>" target="_blank" class="btn-stall-secondary">
                    <i class="bi bi-chat-dots-fill"></i> Zalo
                </a>
            <?php endif; ?>
            <button onclick="scrollToReviews()" class="btn-stall-secondary" style="border-color: var(--stall-gold); color: #B45309;">
                <i class="bi bi-star-fill"></i> Đánh giá
            </button>
        </div>
    </div>

    
    <div class="stall-main-grid">

        
        <div style="display: flex; flex-direction: column; gap: 20px;">

            
            <div class="stall-panel">
                <div class="stall-panel-header">
                    <div class="panel-icon" style="background: rgba(14, 165, 233, 0.08); color: var(--primary);">
                        <i class="bi bi-person-badge-fill"></i>
                    </div>
                    <h3>Thông tin tiểu thương</h3>
                </div>
                <div class="panel-body">
                    <div class="info-row">
                        <div class="info-icon"><i class="bi bi-person-fill"></i></div>
                        <div>
                            <span class="info-label">Chủ hộ kinh doanh</span>
                            <span class="info-value"><?php echo e($sellerName ?: 'Chưa cập nhật'); ?></span>
                        </div>
                    </div>
                    <?php if($sellerPhone): ?>
                    <div class="info-row">
                        <div class="info-icon"><i class="bi bi-telephone-fill"></i></div>
                        <div>
                            <span class="info-label">Số điện thoại</span>
                            <span class="info-value">
                                <a href="tel:<?php echo e($sellerPhone); ?>" style="color: var(--primary);"><?php echo e($sellerPhone); ?></a>
                            </span>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if($bankInfo): ?>
                    <div class="info-row">
                        <div class="info-icon" style="background: rgba(99,102,241,0.1); color: #6366F1;"><i class="bi bi-bank2"></i></div>
                        <div>
                            <span class="info-label">Số tài khoản (STK)</span>
                            <span class="info-value" style="font-family: monospace; letter-spacing: 0.5px;"><?php echo e($bankInfo); ?></span>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if($originText && $originText !== 'Tự sản xuất'): ?>
                    <div class="info-row">
                        <div class="info-icon"><i class="bi bi-geo-fill"></i></div>
                        <div>
                            <span class="info-label">Nguồn gốc xuất xứ</span>
                            <span class="info-value"><?php echo e($originText); ?></span>
                        </div>
                    </div>
                    <?php endif; ?>

                    
                    <div style="margin-top: 14px; padding-top: 14px; border-top: 1px dashed var(--border-glow);">
                        <div style="font-size: 0.72rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                            <i class="bi bi-share-fill" style="color: var(--primary);"></i> Chia sẻ gian hàng
                        </div>
                        <div style="display: flex; gap: 8px; align-items: stretch; margin-bottom: 8px;">
                            <input type="text" id="shareUrlInput" value="<?php echo e(url()->current()); ?>"
                                   style="flex: 1; padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border-glow); background: var(--bg-base); color: var(--text-main); font-size: 0.8rem; outline: none; min-width: 0;"
                                   readonly>
                            <button onclick="copyShareUrl()" id="copyBtn"
                                    style="padding: 8px 14px; border-radius: 8px; background: rgba(14, 165, 233, 0.08); border: 1px solid rgba(14, 165, 233, 0.25); color: var(--primary); font-weight: 700; font-size: 0.8rem; cursor: pointer; white-space: nowrap; transition: all 0.2s;">
                                <i class="bi bi-clipboard"></i> Sao chép
                            </button>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo e(urlencode(url()->current())); ?>" target="_blank"
                               style="flex:1; text-align:center; padding: 8px; border-radius: 8px; background: rgba(24,119,242,0.08); color: #1877F2; font-weight: 700; font-size: 0.78rem; border: 1px solid rgba(24,119,242,0.15);">
                                <i class="bi bi-facebook"></i> Facebook
                            </a>
                            <a href="https://zalo.me/share/url?url=<?php echo e(urlencode(url()->current())); ?>&title=<?php echo e(urlencode($stallName)); ?>" target="_blank"
                               style="flex:1; text-align:center; padding: 8px; border-radius: 8px; background: rgba(0,107,255,0.08); color: #006BFF; font-weight: 700; font-size: 0.78rem; border: 1px solid rgba(0,107,255,0.15);">
                                <i class="bi bi-chat-fill"></i> Zalo
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            
            <?php
                $categoriesMap = [
                    'food'   => ['name' => '🍜 Món ăn & Đồ uống', 'keywords' => ['bún', 'phở', 'xôi', 'bánh', 'cơm', 'cháo', 'miến', 'nước', 'chè', 'trà', 'sữa', 'cà phê']],
                    'veggie' => ['name' => '🥦 Rau củ & Trái cây', 'keywords' => ['rau', 'củ', 'quả', 'nấm', 'cam', 'táo', 'chuối', 'dưa', 'cà', 'hành', 'tỏi', 'ớt']],
                    'meat'   => ['name' => '🥩 Thịt & Hải sản', 'keywords' => ['thịt', 'giò', 'chả', 'cá', 'tôm', 'mực', 'gà', 'vịt', 'bò', 'heo', 'lợn', 'trứng']],
                    'dry'    => ['name' => '🌾 Món khô & Gia vị', 'keywords' => ['gạo', 'đỗ', 'hạt', 'khô', 'mắm', 'muối', 'đường', 'tiêu', 'dầu', 'mì', 'tương']],
                ];

                $categoryCounts = ['all' => $stallProducts->count()];
                $prodCatAssoc = [];

                foreach ($stallProducts as $prod) {
                    $pNameLower = mb_strtolower($prod->name);
                    $foundCat = 'other';
                    foreach ($categoriesMap as $catKey => $catInfo) {
                        foreach ($catInfo['keywords'] as $kw) {
                            if (str_contains($pNameLower, $kw)) {
                                $foundCat = $catKey;
                                break 2;
                            }
                        }
                    }
                    $prodCatAssoc[$prod->id] = $foundCat;
                    $categoryCounts[$foundCat] = ($categoryCounts[$foundCat] ?? 0) + 1;
                }

                $activeCategoryCount = count(array_filter($categoryCounts, fn($cnt, $k) => $k !== 'all' && $cnt > 0, ARRAY_FILTER_USE_BOTH));
            ?>

            <div class="stall-panel">
                <div class="stall-panel-header" style="flex-wrap: wrap; gap: 10px;">
                    <div class="panel-icon" style="background: rgba(14, 165, 233, 0.08); color: var(--primary);">
                        <i class="bi bi-basket2-fill"></i>
                    </div>
                    <h3>Sản phẩm niêm yết (<span id="productCountBadge"><?php echo e($stallProducts->count()); ?></span>)</h3>
                    
                    <?php if($stallProducts->count() > 3): ?>
                        <div style="margin-left: auto; width: 100%; max-width: 220px; position: relative;">
                            <input type="text" id="productSearchInput" placeholder="Tìm sản phẩm..." onkeyup="filterStallProducts()"
                                   style="width: 100%; padding: 6px 14px 6px 30px; border-radius: 20px; border: 1px solid var(--border-glow); background: var(--bg-base); color: var(--text-main); font-size: 0.8rem; outline: none; box-sizing: border-box;">
                            <i class="bi bi-search" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.75rem;"></i>
                        </div>
                    <?php else: ?>
                        <span style="margin-left: auto; font-size: 0.72rem; color: var(--text-muted); font-weight: 600;">Giá niêm yết</span>
                    <?php endif; ?>
                </div>

                
                <?php if($stallProducts->count() > 0): ?>
                    <div class="category-tabs-bar">
                        <button type="button" class="cat-tab-btn active" data-cat="all" onclick="selectProductCategory('all', this)">
                            <span>Tất cả</span>
                            <span class="cat-count-badge"><?php echo e($stallProducts->count()); ?></span>
                        </button>
                        <?php $__currentLoopData = $categoriesMap; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $catKey => $catInfo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if(!empty($categoryCounts[$catKey])): ?>
                                <button type="button" class="cat-tab-btn" data-cat="<?php echo e($catKey); ?>" onclick="selectProductCategory('<?php echo e($catKey); ?>', this)">
                                    <span><?php echo e($catInfo['name']); ?></span>
                                    <span class="cat-count-badge"><?php echo e($categoryCounts[$catKey]); ?></span>
                                </button>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php if(!empty($categoryCounts['other'])): ?>
                            <button type="button" class="cat-tab-btn" data-cat="other" onclick="selectProductCategory('other', this)">
                                <span>📦 Khác</span>
                                <span class="cat-count-badge"><?php echo e($categoryCounts['other']); ?></span>
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="panel-body" id="stallProductsContainer" style="<?php echo e($stallProducts->count() > 5 ? 'max-height: 520px; overflow-y: auto; padding-right: 14px;' : ''); ?>">
                    <?php $__empty_1 = true; $__currentLoopData = $stallProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $prodOrigin = 'Tự sản xuất';
                            if ($product->description && preg_match('/Nguồn gốc[:\s]+(.*?)[\.\n]/u', $product->description, $pm)) {
                                $prodOrigin = trim($pm[1]);
                            }
                            $prodEmoji = '🛒';
                            if (str_contains($product->name, 'Bún') || str_contains($product->name, 'Phở')) $prodEmoji = '🍜';
                            elseif (str_contains($product->name, 'Rau')) $prodEmoji = '🥦';
                            elseif (str_contains($product->name, 'Thịt') || str_contains($product->name, 'Giò')) $prodEmoji = '🥩';
                            $pCat = $prodCatAssoc[$product->id] ?? 'other';
                        ?>
                        <div class="product-card" data-name="<?php echo e(mb_strtolower($product->name)); ?>" data-category="<?php echo e($pCat); ?>">
                            
                            <?php if($product->image_path): ?>
                                <img src="<?php echo e(asset($product->image_path)); ?>" alt="<?php echo e($product->name); ?>" class="product-img">
                            <?php else: ?>
                                <div class="product-img-placeholder"><?php echo e($prodEmoji); ?></div>
                            <?php endif; ?>

                            
                            <div style="flex: 1; min-width: 0;">
                                <div class="product-name"><?php echo e($product->name); ?></div>
                                <div class="product-origin">
                                    <i class="bi bi-geo-alt-fill" style="color: var(--primary);"></i>
                                    <?php echo e($prodOrigin); ?> &middot; Cam kết tươi sạch
                                </div>
                                <div class="product-price-chip">
                                    <span class="product-price-num"><?php echo e(number_format($product->price, 0, ',', '.')); ?>₫</span>
                                    <?php
                                        $displayUnit = $product->unit ?: 'kg';
                                    ?>
                                    <span class="product-price-unit">&nbsp;/ <?php echo e($displayUnit); ?></span>
                                </div>
                            </div>

                            
                            <button class="btn-order add-to-cart-btn"
                                    data-id="<?php echo e($product->id); ?>"
                                    data-type="ocop_product"
                                    onclick="addToCart(event, this); animateFlyToCart(this);">
                                <i class="bi bi-cart-plus-fill"></i> Đặt hàng
                            </button>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div style="text-align:center; padding: 32px 0; color: var(--text-muted);">
                            <div style="font-size: 2.5rem; margin-bottom: 10px;">📦</div>
                            <p style="font-size: 0.9rem; font-weight: 600;">Chưa có sản phẩm được niêm yết.</p>
                        </div>
                    <?php endif; ?>
                    <div id="noProductMatch" style="display: none; text-align: center; padding: 28px 0; color: var(--text-muted);">
                        <div style="font-size: 2rem; margin-bottom: 6px;">🔍</div>
                        <p style="font-size: 0.85rem; font-weight: 600;">Không tìm thấy sản phẩm phù hợp.</p>
                    </div>
                </div>
            </div>

        </div>

        
        <div style="display: flex; flex-direction: column; gap: 20px;">

            
            <div class="stall-panel">
                <div class="stall-panel-header">
                    <div class="panel-icon" style="background: var(--stall-green-soft); color: var(--stall-green);">
                        <i class="bi bi-map-fill"></i>
                    </div>
                    <h3>Vị trí gian hàng</h3>
                </div>
                <a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo e($lat); ?>,<?php echo e($lng); ?>" target="_blank" class="map-direction-btn">
                    <i class="bi bi-navigation-fill"></i>
                    Chỉ đường tới gian hàng · Google Maps
                    <i class="bi bi-box-arrow-up-right" style="margin-left: auto;"></i>
                </a>
                <div id="stallMap"></div>
            </div>

            
            <?php if($bankName && $bankAcct): ?>
            <div class="stall-panel">
                <div class="stall-panel-header">
                    <div class="panel-icon" style="background: rgba(99,102,241,0.1); color: #6366F1;">
                        <i class="bi bi-qr-code-scan"></i>
                    </div>
                    <h3>Thanh toán VietQR</h3>
                </div>
                <div class="qr-panel-body">
                    <div class="qr-box">
                        <?php
                            $qrDesc = urlencode('Thanh toan cho ' . $stallName);
                            $qrUrl  = "https://img.vietqr.io/image/{$bankName}-{$bankAcct}-compact2.png?accountName=" . urlencode($sellerName) . "&addInfo={$qrDesc}";
                        ?>
                        <img src="<?php echo e($qrUrl); ?>" alt="VietQR <?php echo e($bankName); ?>" style="width:160px;height:auto;display:block;">
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 0.72rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">
                            Số tài khoản
                        </div>
                        <div style="font-family: monospace; font-weight: 900; font-size: 1.05rem; color: var(--text-main); letter-spacing: 1px; margin-bottom: 2px;">
                            <?php echo e($bankAcct); ?>

                        </div>
                        <div style="font-size: 0.78rem; font-weight: 700; color: #6366F1; margin-bottom: 6px;">
                            <?php echo e($bankName); ?> Bank
                        </div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">
                            Quét mã QR để chuyển khoản · 100% bảo mật
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>

    
    <div class="reviews-section" id="reviewsAnchor">

        
        <div class="review-form-area">
            <div class="review-form-title">
                <i class="bi bi-star-fill" style="color: var(--stall-gold);"></i>
                Viết đánh giá cho gian hàng
            </div>
            <form method="POST" action="<?php echo e(route('market.stall.review.store', ['marketSlug' => $marketSlug, 'stallSlug' => $stallSlug])); ?>" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>

                
                <div style="margin-bottom: 12px;">
                    <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 8px;">Đánh giá của bạn</div>
                    <div class="star-input-row" id="starRow">
                        <?php for($s = 1; $s <= 5; $s++): ?>
                            <button type="button" class="star-btn" data-val="<?php echo e($s); ?>" onclick="setStar(<?php echo e($s); ?>)">★</button>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" name="rating" id="ratingInput" value="5">
                </div>

                <?php if(Auth::check()): ?>
                    <div style="margin-bottom: 12px; font-size: 0.85rem; color: var(--text-muted); display: flex; align-items: center; gap: 8px; background: rgba(14, 165, 233, 0.05); padding: 8px 14px; border-radius: 10px; border: 1px solid rgba(14, 165, 233, 0.15);">
                        <i class="bi bi-person-check-fill" style="color: var(--primary); font-size: 1.1rem;"></i>
                        <span>Đang đánh giá với tài khoản: <strong style="color: var(--text-main);"><?php echo e(Auth::user()->name); ?></strong></span>
                    </div>
                <?php else: ?>
                    <div style="margin-bottom: 12px;">
                        <input type="text" name="user_name" placeholder="Tên của bạn (tùy chọn)"
                               style="width: 100%; padding: 10px 14px; border-radius: 10px; border: 1px solid var(--border-glow); background: var(--bg-base); color: var(--text-main); font-family: 'Be Vietnam Pro', sans-serif; font-size: 0.88rem; outline: none; box-sizing: border-box;"
                               maxlength="50">
                    </div>
                <?php endif; ?>

                <textarea name="comment" class="review-textarea" rows="4"
                          placeholder="Chia sẻ trải nghiệm mua sắm của bạn tại gian hàng này..." required minlength="5" maxlength="500"></textarea>

                <?php $__errorArgs = ['comment'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div style="color: #EF4444; font-size: 0.8rem; margin-top: 6px;"><?php echo e($message); ?></div>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                
                <div style="margin-top: 12px;">
                    <label for="stallReviewMedia" style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 14px; border-radius: 10px; border: 1px dashed var(--border-glow); background: var(--bg-base); color: var(--text-muted); font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.2s ease;">
                        <i class="bi bi-camera-fill" style="color: var(--primary); font-size: 1rem;"></i>
                        <span>Thêm ảnh / video (tùy chọn)</span>
                    </label>
                    <input type="file" name="media[]" id="stallReviewMedia" accept="image/*,video/*" multiple style="display: none;" onchange="previewStallReviewMedia(this)">
                    <div id="stallMediaPreview" style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px;"></div>
                </div>

                <div style="display: flex; justify-content: flex-end; margin-top: 14px;">
                    <button type="submit" class="btn-stall-primary">
                        <i class="bi bi-send-fill"></i> Gửi đánh giá
                    </button>
                </div>
            </form>
        </div>

        
        <div style="padding: 18px 24px; border-bottom: 1px solid var(--border-glow); display: flex; align-items: center; gap: 12px;">
            <div style="font-size: 0.95rem; font-weight: 800; color: var(--text-main);">
                <i class="bi bi-chat-square-quote-fill" style="color: var(--primary);"></i>
                Đánh giá từ khách hàng
            </div>
            <?php if($reviews->count() > 0): ?>
                <span style="background: rgba(14, 165, 233, 0.08); color: var(--primary); font-size: 0.78rem; font-weight: 700; padding: 3px 10px; border-radius: 20px; border: 1px solid rgba(14, 165, 233, 0.2);">
                    <?php echo e($reviews->count()); ?> nhận xét
                </span>
                <span style="margin-left: auto; color: var(--stall-gold); font-weight: 800; font-size: 0.95rem;">
                    ★ <?php echo e($avgRating); ?>/5
                </span>
            <?php endif; ?>
        </div>

        
        <div class="review-list">
            <?php $__empty_1 = true; $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="review-item">
                    <div class="review-top">
                        <div class="reviewer-avatar">
                            <?php echo e(mb_strtoupper(mb_substr($rev->user_name, 0, 1))); ?>

                        </div>
                        <div>
                            <div class="reviewer-name"><?php echo e($rev->user_name); ?></div>
                            <div class="reviewer-time">
                                <i class="bi bi-clock"></i>
                                <?php echo e($rev->created_at ? $rev->created_at->diffForHumans() : 'Vừa xong'); ?>

                            </div>
                        </div>
                        <div class="reviewer-stars">
                            <?php echo e(str_repeat('★', $rev->rating)); ?><?php echo e(str_repeat('☆', 5 - $rev->rating)); ?>

                        </div>
                    </div>
                    <div class="review-comment"><?php echo e($rev->comment); ?></div>
                    <?php if($rev->media && $rev->media->count() > 0): ?>
                        <?php
                            $galleryItems = $rev->media->map(function($m) {
                                return [
                                    'url'  => asset($m->file_path),
                                    'type' => $m->file_type ?? 'image'
                                ];
                            })->values()->toArray();
                        ?>
                        <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px;">
                            <?php $__currentLoopData = $rev->media; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mIndex => $mediaItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if($mediaItem->file_type === 'image'): ?>
                                    <div style="position: relative; width: 90px; height: 90px; border-radius: 10px; overflow: hidden; border: 1px solid var(--border-glow); cursor: pointer; transition: transform 0.2s ease;"
                                         onmouseover="this.style.transform='scale(1.05)'"
                                         onmouseout="this.style.transform='none'"
                                         onclick="openReviewGallery(<?php echo e(json_encode($galleryItems)); ?>, <?php echo e($mIndex); ?>)">
                                        <img src="<?php echo e(asset($mediaItem->file_path)); ?>" alt="Review Image" style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                <?php else: ?>
                                    <div style="position: relative; width: 140px; height: 90px; border-radius: 10px; overflow: hidden; border: 1px solid var(--border-glow); cursor: pointer;"
                                         onclick="openReviewGallery(<?php echo e(json_encode($galleryItems)); ?>, <?php echo e($mIndex); ?>)">
                                        <video src="<?php echo e(asset($mediaItem->file_path)); ?>" style="width: 100%; height: 100%; object-fit: cover; pointer-events: none;"></video>
                                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: #fff; font-size: 1.2rem; text-shadow: 0 0 8px #000;">▶</div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="no-reviews-state">
                    <div class="icon">💬</div>
                    <p>Chưa có đánh giá nào. Hãy là người đầu tiên nhận xét!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    
    <div style="text-align: center; padding: 12px 0;">
        <a href="<?php echo e(route('eatery.show', $eatery->slug)); ?>" class="btn-stall-secondary" style="display: inline-flex;">
            <i class="bi bi-arrow-left-circle-fill"></i> Quay lại <?php echo e($eatery->name); ?>

        </a>
    </div>

</div>


<div id="reviewGalleryModal" onclick="closeReviewGallery()" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.93); backdrop-filter: blur(10px); z-index: 999999; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease;">
    <!-- Close button -->
    <button type="button" onclick="closeReviewGallery()" style="position: absolute; top: 20px; right: 24px; background: rgba(255,255,255,0.15); border: none; color: #fff; width: 44px; height: 44px; border-radius: 50%; font-size: 24px; cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 1000001; transition: background 0.2s;" onmouseover="this.style.background='rgba(239,68,68,0.8)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">
        &times;
    </button>

    <!-- Counter badge -->
    <div id="galleryCounter" style="position: absolute; top: 24px; left: 24px; color: #fff; background: rgba(255,255,255,0.15); padding: 6px 16px; border-radius: 20px; font-weight: 700; font-size: 0.88rem; z-index: 1000001; letter-spacing: 0.5px;">
        1 / 1
    </div>

    <!-- Prev button -->
    <button type="button" id="galleryPrevBtn" onclick="event.stopPropagation(); navigateReviewGallery(-1);" style="position: absolute; left: 20px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.15); border: none; color: #fff; width: 50px; height: 50px; border-radius: 50%; font-size: 22px; cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 1000001; transition: background 0.2s;" onmouseover="this.style.background='var(--primary)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">
        <i class="bi bi-chevron-left"></i>
    </button>

    <!-- Media display container -->
    <div style="max-width: 90vw; max-height: 85vh; display: flex; align-items: center; justify-content: center; position: relative;" onclick="event.stopPropagation()">
        <img id="galleryImg" src="" alt="Zoomed Review Image" style="max-width: 90vw; max-height: 85vh; border-radius: 14px; object-fit: contain; box-shadow: 0 10px 40px rgba(0,0,0,0.8); display: none;">
        <video id="galleryVid" src="" controls style="max-width: 90vw; max-height: 85vh; border-radius: 14px; box-shadow: 0 10px 40px rgba(0,0,0,0.8); display: none;"></video>
    </div>

    <!-- Next button -->
    <button type="button" id="galleryNextBtn" onclick="event.stopPropagation(); navigateReviewGallery(1);" style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.15); border: none; color: #fff; width: 50px; height: 50px; border-radius: 50%; font-size: 22px; cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 1000001; transition: background 0.2s;" onmouseover="this.style.background='var(--primary)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">
        <i class="bi bi-chevron-right"></i>
    </button>
</div>


<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV/XN/WPaU=" crossorigin=""></script>
<script>
let currentSelectedCategory = 'all';

function selectProductCategory(catKey, btnEl) {
    currentSelectedCategory = catKey;
    document.querySelectorAll('.cat-tab-btn').forEach(btn => btn.classList.remove('active'));
    if (btnEl) btnEl.classList.add('active');
    filterStallProducts();
}

function filterStallProducts() {
    const input = document.getElementById('productSearchInput');
    const filter = input ? input.value.toLowerCase().trim() : '';
    const cards = document.querySelectorAll('#stallProductsContainer .product-card');
    const noMatch = document.getElementById('noProductMatch');
    let visibleCount = 0;

    cards.forEach(card => {
        const name = card.getAttribute('data-name') || '';
        const cat = card.getAttribute('data-category') || 'other';

        const matchSearch = name.includes(filter);
        const matchCategory = (currentSelectedCategory === 'all' || cat === currentSelectedCategory);

        if (matchSearch && matchCategory) {
            card.style.display = 'flex';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    if (noMatch) {
        noMatch.style.display = visibleCount === 0 ? 'block' : 'none';
    }
}
window.selectProductCategory = selectProductCategory;
window.filterStallProducts = filterStallProducts;
let currentGalleryItems = [];
let currentGalleryIndex = 0;

function openReviewGallery(items, index) {
    if (!items || items.length === 0) return;
    currentGalleryItems = items;
    currentGalleryIndex = index;
    
    const modal = document.getElementById('reviewGalleryModal');
    if (!modal) return;
    
    updateGalleryView();
    
    modal.style.display = 'flex';
    setTimeout(() => { modal.style.opacity = '1'; }, 10);
    document.body.style.overflow = 'hidden';
}

function updateGalleryView() {
    const item = currentGalleryItems[currentGalleryIndex];
    const imgEl = document.getElementById('galleryImg');
    const vidEl = document.getElementById('galleryVid');
    const counterEl = document.getElementById('galleryCounter');
    const prevBtn = document.getElementById('galleryPrevBtn');
    const nextBtn = document.getElementById('galleryNextBtn');

    if (!item) return;

    if (item.type === 'video') {
        imgEl.style.display = 'none';
        vidEl.style.display = 'block';
        vidEl.src = item.url;
    } else {
        vidEl.pause();
        vidEl.style.display = 'none';
        imgEl.style.display = 'block';
        imgEl.src = item.url;
    }

    if (counterEl) {
        counterEl.innerText = (currentGalleryIndex + 1) + ' / ' + currentGalleryItems.length;
    }

    if (currentGalleryItems.length <= 1) {
        if (prevBtn) prevBtn.style.display = 'none';
        if (nextBtn) nextBtn.style.display = 'none';
    } else {
        if (prevBtn) prevBtn.style.display = 'flex';
        if (nextBtn) nextBtn.style.display = 'flex';
    }
}

function navigateReviewGallery(direction) {
    if (currentGalleryItems.length <= 1) return;
    currentGalleryIndex = (currentGalleryIndex + direction + currentGalleryItems.length) % currentGalleryItems.length;
    updateGalleryView();
}

function closeReviewGallery() {
    const modal = document.getElementById('reviewGalleryModal');
    const vidEl = document.getElementById('galleryVid');
    if (!modal) return;
    
    if (vidEl) vidEl.pause();
    
    modal.style.opacity = '0';
    setTimeout(() => {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }, 300);
}

document.addEventListener('keydown', function(e) {
    const modal = document.getElementById('reviewGalleryModal');
    if (modal && modal.style.display === 'flex') {
        if (e.key === 'Escape') {
            closeReviewGallery();
        } else if (e.key === 'ArrowLeft') {
            navigateReviewGallery(-1);
        } else if (e.key === 'ArrowRight') {
            navigateReviewGallery(1);
        }
    }
});

window.openReviewGallery = openReviewGallery;
window.closeReviewGallery = closeReviewGallery;
window.navigateReviewGallery = navigateReviewGallery;

let selectedReviewFiles = [];

function previewStallReviewMedia(input) {
    if (input.files && input.files.length > 0) {
        const newFiles = Array.from(input.files);
        selectedReviewFiles = selectedReviewFiles.concat(newFiles);
        updateFileInputAndPreview();
    }
}

function removeStallReviewMedia(index) {
    selectedReviewFiles.splice(index, 1);
    updateFileInputAndPreview();
}

function updateFileInputAndPreview() {
    const input = document.getElementById('stallReviewMedia');
    const preview = document.getElementById('stallMediaPreview');
    if (!preview) return;

    // Sync HTML input files list using DataTransfer
    if (input && window.DataTransfer) {
        const dt = new DataTransfer();
        selectedReviewFiles.forEach(file => dt.items.add(file));
        input.files = dt.files;
    }

    preview.innerHTML = '';
    selectedReviewFiles.forEach((file, index) => {
        const wrapper = document.createElement('div');
        wrapper.style.cssText = 'position: relative; width: 80px; height: 80px; border-radius: 10px; overflow: hidden; border: 1.5px solid var(--border-glow); background: #000; box-shadow: 0 4px 10px rgba(0,0,0,0.15);';
        
        if (file.type.startsWith('image/')) {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.style.cssText = 'width: 100%; height: 100%; object-fit: cover;';
            wrapper.appendChild(img);
        } else if (file.type.startsWith('video/')) {
            const vid = document.createElement('video');
            vid.src = URL.createObjectURL(file);
            vid.style.cssText = 'width: 100%; height: 100%; object-fit: cover;';
            wrapper.appendChild(vid);
            
            const playIcon = document.createElement('div');
            playIcon.innerHTML = '▶';
            playIcon.style.cssText = 'position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: #fff; font-size: 1.1rem; pointer-events: none; text-shadow: 0 0 6px #000;';
            wrapper.appendChild(playIcon);
        }

        // Delete "X" button at top-right
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.innerHTML = '&times;';
        removeBtn.title = 'Xóa tệp này';
        removeBtn.style.cssText = 'position: absolute; top: 3px; right: 3px; width: 22px; height: 22px; border-radius: 50%; background: rgba(239, 68, 68, 0.95); color: #fff; border: 1.5px solid #fff; font-size: 15px; font-weight: bold; line-height: 1; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 6px rgba(0,0,0,0.4); z-index: 10; transition: transform 0.15s ease, background 0.15s ease;';
        removeBtn.onmouseover = function() { this.style.transform = 'scale(1.15)'; this.style.background = '#dc2626'; };
        removeBtn.onmouseout = function() { this.style.transform = 'scale(1)'; this.style.background = 'rgba(239, 68, 68, 0.95)'; };
        removeBtn.onclick = function(e) {
            e.preventDefault();
            e.stopPropagation();
            removeStallReviewMedia(index);
        };
        wrapper.appendChild(removeBtn);

        preview.appendChild(wrapper);
    });
}

window.previewStallReviewMedia = previewStallReviewMedia;
window.removeStallReviewMedia = removeStallReviewMedia;

document.addEventListener('DOMContentLoaded', function() {
    // ---- Auto-hide Flash Message ----
    const flashAlert = document.getElementById('flashSuccessAlert');
    if (flashAlert) {
        setTimeout(function() {
            flashAlert.classList.add('fade-out');
            setTimeout(function() {
                if (flashAlert.parentNode) {
                    flashAlert.parentNode.removeChild(flashAlert);
                }
            }, 500);
        }, 3500);
    }

    // ---- Star picker ----
    const stars  = document.querySelectorAll('.star-btn');
    let selected = 5;

    function setStar(val) {
        selected = val;
        document.getElementById('ratingInput').value = val;
        stars.forEach((s, idx) => {
            s.classList.toggle('active', idx < val);
        });
    }

    window.setStar = setStar;
    setStar(5); // default 5 stars

    stars.forEach((btn, idx) => {
        btn.addEventListener('mouseenter', () => {
            stars.forEach((s, i) => s.classList.toggle('active', i <= idx));
        });
        btn.addEventListener('mouseleave', () => setStar(selected));
    });

    // ---- Leaflet Map ----
    const lat = <?php echo e($lat ?? 21.1571); ?>;
    const lng = <?php echo e($lng ?? 105.8448); ?>;

    const map = L.map('stallMap', { zoomControl: true, scrollWheelZoom: false }).setView([lat, lng], 17);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://openstreetmap.org">OpenStreetMap</a>',
        maxZoom: 19
    }).addTo(map);

    // Custom Cyan / Sky Blue marker pin (matching DongAnh Discovery brand system)
    const cyanIcon = L.divIcon({
        html: `<div style="width:42px;height:42px;background:linear-gradient(135deg,#0ea5e9,#06b6d4);border-radius:50% 50% 50% 0;transform:rotate(-45deg);display:flex;align-items:center;justify-content:center;box-shadow:0 4px 14px rgba(14,165,233,0.45);">
                 <span style="transform:rotate(45deg);font-size:1.2rem;"><?php echo e($stallEmoji); ?></span>
               </div>`,
        className: '',
        iconSize: [42, 42],
        iconAnchor: [21, 42],
        popupAnchor: [0, -44]
    });

    L.marker([lat, lng], { icon: cyanIcon })
     .addTo(map)
     .bindPopup(`
        <div style="font-family:'Be Vietnam Pro',sans-serif;min-width:180px;">
            <strong style="font-size:0.95rem;color:#0ea5e9;"><?php echo e($stallName); ?></strong><br>
            <span style="font-size:0.8rem;color:#666;"><?php echo e($eatery->name); ?></span>
            <?php if($sellerPhone): ?>
            <br><a href="tel:<?php echo e($sellerPhone); ?>" style="font-size:0.8rem;color:#0ea5e9;">📞 <?php echo e($sellerPhone); ?></a>
            <?php endif; ?>
        </div>
     `, { maxWidth: 240 })
     .openPopup();

    // ---- Copy URL ----
    window.copyShareUrl = function() {
        const input = document.getElementById('shareUrlInput');
        navigator.clipboard.writeText(input.value).then(() => {
            const btn = document.getElementById('copyBtn');
            btn.innerHTML = '<i class="bi bi-check-lg"></i> Đã sao chép!';
            btn.style.background = 'rgba(16,185,129,0.1)';
            btn.style.color = '#10B981';
            btn.style.borderColor = 'rgba(16,185,129,0.25)';
            setTimeout(() => {
                btn.innerHTML = '<i class="bi bi-clipboard"></i> Sao chép';
                btn.style.background = '';
                btn.style.color = '';
                btn.style.borderColor = '';
            }, 2500);
        });
    };

    // ---- Scroll to reviews ----
    window.scrollToReviews = function() {
        document.getElementById('reviewsAnchor')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    };
});
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\DA_DISCOVERY\resources\views/stall-detail.blade.php ENDPATH**/ ?>