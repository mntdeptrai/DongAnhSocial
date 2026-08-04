@extends(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isManager()) ? 'layouts.admin' : 'layouts.app')

@section('title', 'Bảng Điều Hành - ' . $school->standardized_name)

@section('content')
<link rel="stylesheet" href="{{ asset('css/facebook-feed.css') }}?v={{ time() }}">
<script src="{{ asset('js/facebook-feed.js') }}?v={{ time() }}"></script>
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

    /* Photo Gallery Filters & Badges */
    .sch-photo-filters {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 24px;
        padding-bottom: 12px;
        border-bottom: 1px solid #e2e8f0;
    }

    .sch-photo-filter-pill {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #cbd5e1;
        padding: 8px 18px;
        border-radius: 100px;
        font-size: 0.88rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease;
        font-family: 'Be Vietnam Pro', sans-serif;
    }

    .sch-photo-filter-pill:hover {
        background: #e2e8f0;
        color: #0f172a;
    }

    .sch-photo-filter-pill.active {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        color: #ffffff;
        border-color: #2563eb;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
    }

    .sch-photo-group-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 14px;
        padding-bottom: 8px;
        border-bottom: 2px dashed #f1f5f9;
    }

    .sch-photo-group-title {
        font-size: 1.05rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
        font-family: 'Be Vietnam Pro', sans-serif;
    }

    .sch-photo-group-count {
        font-size: 0.8rem;
        font-weight: 700;
        background: #eff6ff;
        color: #2563eb;
        padding: 2px 10px;
        border-radius: 12px;
    }

    .sch-photo-group-badge {
        font-size: 0.78rem;
        font-weight: 600;
        color: #64748b;
        background: #f8fafc;
        padding: 4px 12px;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
    }

    .sch-photo-type-tag {
        position: absolute;
        top: 10px;
        left: 10px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.72rem;
        font-weight: 800;
        color: #ffffff;
        z-index: 2;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        backdrop-filter: blur(4px);
    }

    .sch-photo-type-tag.tag-post {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    }

    .sch-photo-type-tag.tag-cover {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
    }

    .sch-photo-type-tag.tag-avatar {
        background: linear-gradient(135deg, #7c3aed 0%, #8b5cf6 100%);
    }

    .sch-photo-type-tag.tag-upload {
        background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
    }

    .sch-photo-date {
        color: #cbd5e1;
        font-size: 0.75rem;
        display: block;
        margin-top: 2px;
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
        padding: 24px 16px;
        overflow-y: auto !important;
        -webkit-overflow-scrolling: touch;
    }

    .sch-modal.show {
        display: flex !important;
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
        max-height: 85vh !important;
        overflow-y: auto !important;
        margin: auto !important;
    }

    .fb-post-card {
        overflow: visible !important;
    }

    /* Facebook Custom Post Options Dropdown */
    .fb-post-options-container {
        position: relative;
    }

    .fb-post-options-btn {
        background: #f1f5f9;
        border: none;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #475569;
        font-size: 1.1rem;
        font-weight: 800;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .fb-post-options-btn:hover {
        background: #e2e8f0;
        color: #0f172a;
    }

    .fb-post-dropdown-menu {
        position: absolute;
        top: 100%;
        right: 0;
        margin-top: 6px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.15);
        min-width: 200px;
        z-index: 1000;
        display: none;
        overflow: hidden;
        padding: 6px 0;
        list-style: none !important;
        margin-bottom: 0;
    }

    .fb-post-dropdown-menu.show {
        display: block !important;
        animation: dropFadeIn 0.2s ease;
    }

    @keyframes dropFadeIn {
        from { opacity: 0; transform: translateY(-6px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .fb-post-dropdown-item {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        padding: 10px 16px;
        font-size: 0.88rem;
        font-weight: 700;
        color: #334155;
        background: transparent;
        border: none;
        text-align: left;
        cursor: pointer;
        transition: background 0.15s ease;
        text-decoration: none;
    }

    .fb-post-dropdown-item:hover {
        background: #f8fafc;
        color: #0f172a;
    }

    .fb-post-dropdown-item.danger {
        color: #ef4444;
    }

    .fb-post-dropdown-item.danger:hover {
        background: #fef2f2;
        color: #dc2626;
    }

    .fb-modal-header {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding-bottom: 14px !important;
        border-bottom: 1px solid #e2e8f0 !important;
        margin-bottom: 16px !important;
        position: relative !important;
    }

    .fb-modal-title {
        font-size: 1.15rem !important;
        font-weight: 800 !important;
        color: #0f172a !important;
        margin: 0 !important;
        text-align: center !important;
    }

    .fb-modal-user-row {
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
        margin-bottom: 16px !important;
    }

    .fb-modal-user-avatar {
        width: 44px !important;
        height: 44px !important;
        border-radius: 50% !important;
        object-fit: cover !important;
        border: 2px solid #e2e8f0 !important;
        flex-shrink: 0 !important;
    }

    .fb-modal-user-info {
        display: flex !important;
        flex-direction: column !important;
        gap: 4px !important;
    }

    .fb-modal-user-name {
        font-size: 0.96rem !important;
        font-weight: 800 !important;
        color: #0f172a !important;
        margin: 0 !important;
    }

    .fb-modal-badges {
        display: flex !important;
        gap: 6px !important;
    }

    .fb-modal-badge {
        background: #f1f5f9 !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 999px !important;
        padding: 2px 10px !important;
        font-size: 0.75rem !important;
        font-weight: 600 !important;
        color: #334155 !important;
    }

    .fb-modal-body {
        display: flex !important;
        flex-direction: column !important;
        gap: 12px !important;
        margin-bottom: 16px !important;
    }

    .fb-modal-title-input {
        width: 100% !important;
        border: none !important;
        outline: none !important;
        font-size: 1.15rem !important;
        font-weight: 800 !important;
        color: #0f172a !important;
        padding: 4px 0 !important;
        background: transparent !important;
        box-shadow: none !important;
    }

    .fb-modal-textarea {
        width: 100% !important;
        border: none !important;
        outline: none !important;
        font-size: 1.05rem !important;
        color: #1e293b !important;
        padding: 4px 0 !important;
        background: transparent !important;
        resize: none !important;
        min-height: 120px !important;
        box-shadow: none !important;
    }

    .fb-modal-action-bar {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        padding: 12px 16px !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 16px !important;
        background: #ffffff !important;
        box-shadow: 0 4px 14px rgba(0,0,0,0.03) !important;
        margin-bottom: 16px !important;
        box-sizing: border-box !important;
        width: 100% !important;
    }

    .fb-modal-action-label {
        font-size: 0.88rem !important;
        font-weight: 700 !important;
        color: #1e293b !important;
        white-space: nowrap !important;
    }

    .fb-modal-action-buttons {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
    }

    .fb-modal-action-btn {
        width: 38px !important;
        height: 38px !important;
        border-radius: 50% !important;
        background: #f1f5f9 !important;
        border: 1px solid #e2e8f0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        cursor: pointer !important;
        font-size: 1.1rem !important;
        transition: all 0.2s ease !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    .fb-modal-action-btn:hover {
        background: #e2e8f0 !important;
        transform: scale(1.05) !important;
    }

    .fb-modal-file-input {
        display: none !important;
    }

    .fb-modal-submit-btn {
        width: 100% !important;
        padding: 12px !important;
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
        color: #ffffff !important;
        font-weight: 800 !important;
        font-size: 1rem !important;
        border: none !important;
        border-radius: 12px !important;
        cursor: pointer !important;
        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35) !important;
        transition: all 0.2s ease !important;
        display: block !important;
        text-align: center !important;
    }

    .fb-modal-submit-btn:hover {
        background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%) !important;
        transform: translateY(-1px) !important;
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
        z-index: 10;
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
        background: #ffffff;
        border: 1px solid var(--sch-border);
        border-radius: 20px;
        padding: 48px 24px;
        text-align: center;
        color: var(--sch-text-muted);
        box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05);
        margin: 24px auto 48px auto;
        width: 100%;
        max-width: 680px;
        box-sizing: border-box;
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
        <div class="d-flex gap-2 flex-wrap align-items-center">
            @php
                $uId = \Illuminate\Support\Facades\Auth::id() ?? session('user_id');
                $sId = session()->getId();
                $eateryLiked = \App\Models\CheckinReaction::where('reactionable_type', 'eatery')
                    ->where('reactionable_id', $school->id)
                    ->where(function($q) use ($uId, $sId) {
                        if ($uId) { $q->where('user_id', $uId); } else { $q->where('session_id', $sId); }
                    })->exists();

                $eateryLikesCount = \App\Models\CheckinReaction::where('reactionable_type', 'eatery')
                    ->where('reactionable_id', $school->id)
                    ->count();
            @endphp

            <button type="button" class="sch-btn {{ $eateryLiked ? 'sch-btn-primary' : 'sch-btn-accent' }}" 
                    id="eatery-heart-btn-{{ $school->id }}" 
                    onclick="togglePlaceHeart(this, {{ $school->id }})" 
                    style="border-radius: 100px; {{ $eateryLiked ? 'background: #ef4444 !important; color: #ffffff !important;' : '' }}">
                ❤️ <span id="eatery-heart-text-{{ $school->id }}">{{ $eateryLiked ? 'Đã thả tim' : 'Thả tim địa điểm' }} ({{ $eateryLikesCount }})</span>
            </button>

            <a href="/principal/schools" class="sch-btn sch-btn-accent" style="border-radius: 100px;">
                ⬅️ DS Trường học
            </a>
            <a href="{{ route('principal.schools.edit', $school->slug ?: $school->id) }}" class="sch-btn sch-btn-primary" style="border-radius: 100px; background: #f59e0b; color: #ffffff;">
                ✏️ Cập nhật chỉ số & thông tin trường
            </a>
            <a href="{{ route('principal.schools.edit', $school->slug ?: $school->id) }}" class="sch-btn sch-btn-primary" style="border-radius: 100px; background: #6366f1;">
                ⚙️ Điểm trường sáp nhập
            </a>
        </div>
    </header>

    @php
        $storyData = $storyData ?? ($school->storytelling_data ?? []);
        $mergedSchool = $storyData['mergedSchool'] ?? [];
        $mergedComponents = $school->merged_components ?? [];

        $foundedYr = $mergedSchool['founded_year'] ?? ($school->founded_year ?? null);

        $calcStaff = count($mergedComponents) > 0 ? array_sum(array_column($mergedComponents, 'staff')) : null;
        $totalStaff = $mergedSchool['total_staff'] ?? ($school->total_teachers ?? ($calcStaff ?: null));

        $calcStudents = count($mergedComponents) > 0 ? array_sum(array_column($mergedComponents, 'students')) : null;
        $totalStudents = $mergedSchool['total_students'] ?? ($school->total_students ?? ($calcStudents ?: null));

        $awardsCount = $mergedSchool['awards_count'] ?? ($school->awards_count ?? null);

        // Tính tổng số ảnh trong Thư viện (Bài viết + Ảnh bìa/cơ sở + Ảnh đại diện + Ảnh tải lên)
        $allPostPhotosCount = 0;
        foreach ($posts as $p) {
            $pImgs = $p->all_images;
            if (is_array($pImgs)) {
                $allPostPhotosCount += count(array_filter($pImgs));
            }
        }
        $avatarPhotoCount = $school->image_path ? 1 : 0;
        $coverPhotosCount = 0;
        foreach ($mergedComponents as $c) {
            if (!empty($c['photo'])) $coverPhotosCount++;
        }
        if ($coverPhotosCount === 0) {
            $coverPhotosCount = count($storyData['photos'] ?? []);
        }
        $totalGalleryPhotosCount = $allPostPhotosCount + $avatarPhotoCount + $coverPhotosCount + $photos->count();
    @endphp

    <!-- Bento Stats Grid (Unified 7 Cards Grid) -->
    <section class="sch-stats-grid">
        <div class="sch-stat-card">
            <div class="sch-stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #d97706;">
                📅
            </div>
            <div class="sch-stat-info">
                <div class="sch-stat-value">{{ $foundedYr ?: 'Chưa cập nhật' }}</div>
                <div class="sch-stat-label">Năm thành lập</div>
            </div>
        </div>

        <div class="sch-stat-card">
            <div class="sch-stat-icon" style="background: rgba(37, 99, 235, 0.1); color: #2563eb;">
                👩‍🏫
            </div>
            <div class="sch-stat-info">
                <div class="sch-stat-value">{{ $totalStaff !== null ? $totalStaff . ' người' : 'Chưa cập nhật' }}</div>
                <div class="sch-stat-label">Giáo viên & CBGVNV</div>
            </div>
        </div>

        <div class="sch-stat-card">
            <div class="sch-stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                🎒
            </div>
            <div class="sch-stat-info">
                <div class="sch-stat-value">{{ $totalStudents !== null ? $totalStudents . ' bé' : 'Chưa cập nhật' }}</div>
                <div class="sch-stat-label">Học sinh nhà trường</div>
            </div>
        </div>

        <div class="sch-stat-card">
            <div class="sch-stat-icon" style="background: rgba(236, 72, 153, 0.1); color: #ec4899;">
                🏆
            </div>
            <div class="sch-stat-info">
                <div class="sch-stat-value">{{ $awardsCount !== null ? $awardsCount . ' danh hiệu' : 'Chưa cập nhật' }}</div>
                <div class="sch-stat-label">Giải thưởng & Thành tích</div>
            </div>
        </div>

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
                <div class="sch-stat-value">{{ $totalGalleryPhotosCount }}</div>
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
            📰 Bài viết / Hoạt động ({{ $posts->count() }})
        </button>
        <button onclick="switchTab('photos')" id="tab-btn-photos" class="sch-tab-btn">
            🖼️ Thư viện hình ảnh ({{ $totalGalleryPhotosCount }})
        </button>
        <button onclick="switchTab('videos')" id="tab-btn-videos" class="sch-tab-btn">
            📹 Video giới thiệu ({{ $videos->count() }})
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
                <div class="sch-empty-state">
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
                                <div class="fb-post-options-container">
                                    <button class="fb-post-options-btn" type="button" onclick="togglePostOptionsDropdown(event, 'postMenu-{{ $p->id }}')">•••</button>
                                    <div class="fb-post-dropdown-menu" id="postMenu-{{ $p->id }}">
                                        <button type="button" onclick="openEditPostModal({{ json_encode($p) }})" class="fb-post-dropdown-item">
                                            <span>✏️</span> <span>Chỉnh sửa bài viết</span>
                                        </button>
                                        <form action="{{ route('principal.posts.destroy', $p->id) }}" method="POST" onsubmit="return showCustomConfirm(event, this, 'Xóa bài viết', 'Bạn có chắc chắn muốn xóa bài viết này không? Hành động này không thể hoàn tác!', true)">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="fb-post-dropdown-item danger">
                                                <span>🗑️</span> <span>Xóa bài viết</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Post Content Text -->
                            <div class="fb-post-text">
                                <strong class="d-block mb-1 text-dark" style="font-size: 1.05rem;">🌸 {{ $p->name }}</strong>
                                {!! \App\Helpers\TextHelper::linkify($p->description) !!}
                            </div>

                            <!-- Facebook Multi-Photo Grid System (1, 2, 3, 4+ photos with +N overlay) -->
                            @if($imgCount === 1)
                                <div class="fb-photo-grid fb-grid-1" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 0)">
                                    <img src="{{ $imgs[0] }}" alt="{{ $p->name }}">
                                </div>
                            @elseif($imgCount === 2)
                                <div class="fb-photo-grid fb-grid-2">
                                    <img src="{{ $imgs[0] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 0)" alt="{{ $p->name }}">
                                    <img src="{{ $imgs[1] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 1)" alt="{{ $p->name }}">
                                </div>
                            @elseif($imgCount === 3)
                                <div class="fb-photo-grid fb-grid-3">
                                    <img src="{{ $imgs[0] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 0)" alt="{{ $p->name }}">
                                    <div class="fb-grid-3-col-right">
                                        <img src="{{ $imgs[1] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 1)" alt="{{ $p->name }}">
                                        <img src="{{ $imgs[2] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 2)" alt="{{ $p->name }}">
                                    </div>
                                </div>
                            @elseif($imgCount === 4)
                                <div class="fb-photo-grid fb-grid-4">
                                    <img src="{{ $imgs[0] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 0)" alt="{{ $p->name }}">
                                    <div class="fb-grid-4-col-right">
                                        <img src="{{ $imgs[1] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 1)" alt="{{ $p->name }}">
                                        <img src="{{ $imgs[2] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 2)" alt="{{ $p->name }}">
                                        <img src="{{ $imgs[3] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 3)" alt="{{ $p->name }}">
                                    </div>
                                </div>
                            @elseif($imgCount >= 5)
                                <div class="fb-photo-grid fb-grid-5">
                                    <div class="fb-grid-5-row-top">
                                        <img src="{{ $imgs[0] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 0)" alt="{{ $p->name }}">
                                        <img src="{{ $imgs[1] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 1)" alt="{{ $p->name }}">
                                    </div>
                                    <div class="fb-grid-5-row-bottom">
                                        <img src="{{ $imgs[2] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 2)" alt="{{ $p->name }}">
                                        <img src="{{ $imgs[3] }}" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 3)" alt="{{ $p->name }}">
                                        <div class="fb-photo-thumb-box" onclick="openPostLightboxGallery({{ json_encode($imgs) }}, 4)">
                                            <img src="{{ $imgs[4] }}" alt="{{ $p->name }}">
                                            @if($imgCount > 5)
                                                <div class="fb-photo-more-overlay">+{{ $imgCount - 5 }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Facebook Post Stats Bar -->
                            <div class="fb-post-stats">
                                <div id="post-likes-count-{{ $p->id }}">👍 {{ $p->real_likes_count ?? $p->likes_count ?? 0 }} lượt thích</div>
                                <div>💬 {{ $p->real_comments_count ?? 0 }} bình luận • {{ $p->real_shares_count ?? 0 }} chia sẻ</div>
                            </div>

                            <!-- Facebook Footer Actions Bar -->
                            <div class="fb-post-actions">
                                <button class="fb-action-btn {{ ($p->is_liked ?? false) ? 'active' : '' }}" 
                                        id="post-like-btn-{{ $p->id }}" 
                                        onclick="togglePostLike(this, {{ $p->id }})"
                                        style="{{ ($p->is_liked ?? false) ? 'color: #2563eb; font-weight: 700;' : '' }}">
                                    👍 {{ ($p->is_liked ?? false) ? 'Đã thích' : 'Thích' }}
                                </button>
                                <button class="fb-action-btn" onclick="toggleComments({{ $p->id }}, this)">💬 Bình luận</button>
                                <button class="fb-action-btn" onclick="shareFbPost({{ $p->id }}, {{ json_encode($p->name) }}, {{ json_encode($imgs) }})">🔄 Chia sẻ</button>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- ==========================================
         TAB 2: PHOTO GALLERY (CATEGORIZED & AUTO-UPDATED FROM POSTS)
         ========================================== -->
    <div id="pane-photos" class="sch-tab-pane">
        @php
            // 1. Collect post photos dynamically from all posts
            $postPhotos = [];
            foreach ($posts as $p) {
                $pImgs = $p->all_images;
                foreach ($pImgs as $imgUrl) {
                    if (!empty($imgUrl)) {
                        $postPhotos[] = [
                            'url' => $imgUrl,
                            'caption' => 'Bài viết: ' . ($p->name ?: 'Hoạt động trường học'),
                            'date' => $p->created_at ? $p->created_at->diffForHumans() : 'Vừa xong',
                            'post_id' => $p->id,
                        ];
                    }
                }
            }

            // 2. Avatar photo
            $avatarPhoto = $school->image_path ? [
                'url' => $school->image_path,
                'caption' => 'Ảnh đại diện chính - ' . $school->standardized_name
            ] : null;

            // 3. Cover & Campus photos
            $coverPhotos = [];
            $components = $school->merged_components ?? [];
            if (!empty($components) && is_array($components)) {
                foreach ($components as $comp) {
                    if (!empty($comp['photo'])) {
                        $coverPhotos[] = [
                            'url' => $comp['photo'],
                            'caption' => 'Ảnh điểm trường/bìa: ' . ($comp['name'] ?? $school->standardized_name)
                        ];
                    }
                }
            }

            // 4. Manually uploaded photos
            $uploadedPhotos = $photos;

            $totalPhotosCount = count($postPhotos) + count($coverPhotos) + count($uploadedPhotos) + ($avatarPhoto ? 1 : 0);
        @endphp

        <div class="sch-section-card">
            <div class="sch-section-header">
                <div>
                    <h2 class="sch-section-title">
                        <span>🖼️</span> Thư viện Hình ảnh Trường học
                    </h2>
                    <p class="text-secondary mb-0" style="font-size: 0.85rem;">Tự động cập nhật ảnh từ Bài viết, Ảnh bìa, Ảnh đại diện & Ảnh tải lên</p>
                </div>
                <button onclick="openModal('addPhotoModal')" class="sch-btn sch-btn-success" id="add-photo-trigger">
                    + Thêm hình ảnh mới
                </button>
            </div>

            <!-- Group Filter Navigation Pills -->
            <div class="sch-photo-filters">
                <button type="button" onclick="filterPhotoCategory('all', this)" class="sch-photo-filter-pill active">
                    ✨ Tất cả ({{ $totalPhotosCount }})
                </button>
                <button type="button" onclick="filterPhotoCategory('post', this)" class="sch-photo-filter-pill">
                    🖼️ Ảnh bài viết ({{ count($postPhotos) }})
                </button>
                <button type="button" onclick="filterPhotoCategory('cover', this)" class="sch-photo-filter-pill">
                    🏫 Ảnh bìa & Cơ sở ({{ count($coverPhotos) }})
                </button>
                @if($avatarPhoto)
                <button type="button" onclick="filterPhotoCategory('avatar', this)" class="sch-photo-filter-pill">
                    👤 Ảnh đại diện (1)
                </button>
                @endif
                <button type="button" onclick="filterPhotoCategory('upload', this)" class="sch-photo-filter-pill">
                    📷 Ảnh tải lên ({{ $uploadedPhotos->count() }})
                </button>
            </div>

            @if($totalPhotosCount === 0)
                <div class="sch-empty-state">
                    <span class="sch-empty-icon">🖼️</span>
                    <p>Chưa có hình ảnh nào trong thư viện ảnh trường học.</p>
                    <button type="button" onclick="openModal('addPhotoModal')" class="btn btn-primary rounded-pill px-4 py-2 mt-2">
                        + Thêm hình ảnh mới
                    </button>
                </div>
            @else
                <!-- Group 1: Ảnh bài viết (Tự động cập nhật) -->
                @if(!empty($postPhotos))
                <div class="sch-photo-group-section mb-4" data-category="post">
                    <div class="sch-photo-group-header">
                        <h4 class="sch-photo-group-title">
                            <span>🖼️</span> Ảnh bài viết
                            <span class="sch-photo-group-count">{{ count($postPhotos) }} ảnh</span>
                        </h4>
                        <span class="sch-photo-group-badge">⚡ Tự động cập nhật từ các bài viết</span>
                    </div>

                    <div class="sch-photo-grid">
                        @foreach($postPhotos as $pIdx => $item)
                            <div class="sch-photo-card" onclick="openPostLightboxGallery({{ json_encode(array_column($postPhotos, 'url')) }}, {{ $pIdx }})">
                                <img src="{{ $item['url'] }}" alt="{{ $item['caption'] }}" loading="lazy">
                                <span class="sch-photo-type-tag tag-post">🖼️ Bài viết</span>
                                <div class="sch-photo-overlay">
                                    <p class="sch-photo-caption">{{ $item['caption'] }}</p>
                                    @if($item['date'])
                                        <span class="sch-photo-date">🕒 {{ $item['date'] }}</span>
                                    @endif
                                    <div class="mt-2">
                                        <button type="button" class="sch-btn sch-btn-sm sch-btn-accent w-100 justify-content-center">
                                            🔍 Xem ảnh lớn
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Group 2: Ảnh bìa & Cơ sở vật chất -->
                @if(!empty($coverPhotos))
                <div class="sch-photo-group-section mb-4" data-category="cover">
                    <div class="sch-photo-group-header">
                        <h4 class="sch-photo-group-title">
                            <span>🏫</span> Ảnh bìa & Cơ sở vật chất
                            <span class="sch-photo-group-count">{{ count($coverPhotos) }} ảnh</span>
                        </h4>
                        <span class="sch-photo-group-badge">Khuôn viên nhà trường</span>
                    </div>

                    <div class="sch-photo-grid">
                        @foreach($coverPhotos as $cIdx => $cItem)
                            <div class="sch-photo-card" onclick="openPostLightbox('{{ $cItem['url'] }}')">
                                <img src="{{ $cItem['url'] }}" alt="{{ $cItem['caption'] }}" loading="lazy">
                                <span class="sch-photo-type-tag tag-cover">🏫 Ảnh bìa</span>
                                <div class="sch-photo-overlay">
                                    <p class="sch-photo-caption">{{ $cItem['caption'] }}</p>
                                    <div class="mt-2">
                                        <button type="button" class="sch-btn sch-btn-sm sch-btn-accent w-100 justify-content-center">
                                            🔍 Xem ảnh lớn
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Group 3: Ảnh đại diện -->
                @if($avatarPhoto)
                <div class="sch-photo-group-section mb-4" data-category="avatar">
                    <div class="sch-photo-group-header">
                        <h4 class="sch-photo-group-title">
                            <span>👤</span> Ảnh đại diện nhà trường
                            <span class="sch-photo-group-count">1 ảnh</span>
                        </h4>
                        <span class="sch-photo-group-badge">Logo / Avatar chính</span>
                    </div>

                    <div class="sch-photo-grid">
                        <div class="sch-photo-card" onclick="openPostLightbox('{{ $avatarPhoto['url'] }}')">
                            <img src="{{ $avatarPhoto['url'] }}" alt="{{ $avatarPhoto['caption'] }}" loading="lazy">
                            <span class="sch-photo-type-tag tag-avatar">👤 Ảnh đại diện</span>
                            <div class="sch-photo-overlay">
                                <p class="sch-photo-caption">{{ $avatarPhoto['caption'] }}</p>
                                <div class="mt-2">
                                    <button type="button" class="sch-btn sch-btn-sm sch-btn-accent w-100 justify-content-center">
                                        🔍 Xem ảnh lớn
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Group 4: Thư viện ảnh đã tải lên thủ công -->
                @if($uploadedPhotos->isNotEmpty())
                <div class="sch-photo-group-section mb-4" data-category="upload">
                    <div class="sch-photo-group-header">
                        <h4 class="sch-photo-group-title">
                            <span>📷</span> Thư viện ảnh tải lên thủ công
                            <span class="sch-photo-group-count">{{ $uploadedPhotos->count() }} ảnh</span>
                        </h4>
                        <span class="sch-photo-group-badge">Ảnh quản trị viên tải lên</span>
                    </div>

                    <div class="sch-photo-grid">
                        @foreach($uploadedPhotos as $ph)
                            <div class="sch-photo-card">
                                <img src="{{ $ph->image_path }}" alt="{{ $ph->caption }}" loading="lazy">
                                <span class="sch-photo-type-tag tag-upload">📷 Ảnh tải lên</span>
                                <div class="sch-photo-overlay">
                                    <p class="sch-photo-caption">{{ $ph->caption ?: 'Ảnh thư viện nhà trường' }}</p>
                                    <div class="d-flex gap-2 w-100 mt-2">
                                        <button type="button" onclick="openPostLightbox('{{ $ph->image_path }}')" class="sch-btn sch-btn-sm sch-btn-accent flex-grow-1 justify-content-center">
                                            🔍 Phóng to
                                        </button>
                                        <form action="{{ route('principal.photos.destroy', $ph->id) }}" method="POST" onsubmit="return showCustomConfirm(event, this, 'Xóa ảnh thư viện', 'Bạn có chắc chắn muốn xóa ảnh này khỏi thư viện nhà trường?', true)">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="sch-btn sch-btn-danger sch-btn-sm justify-content-center" title="Xóa ảnh">
                                                🗑️
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
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
                                <form action="{{ route('principal.videos.destroy', $vid->id) }}" method="POST" onsubmit="return showCustomConfirm(event, this, 'Xóa video giới thiệu', 'Bạn có chắc chắn muốn xóa video này khỏi danh sách?', true)">
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
<div class="sch-modal" id="addPostModal" onclick="if(event.target === this) closeModal('addPostModal')">
    <div class="fb-modal-box">
        <button type="button" onclick="closeModal('addPostModal')" class="sch-close-modal" style="top: 16px; right: 16px; width: 36px; height: 36px; border-radius: 50%; background: #f1f5f9; border: none; font-size: 1.1rem; color: #475569; position: absolute; z-index: 10; cursor: pointer;">✕</button>
        <div class="fb-modal-header">
            <h4 class="fb-modal-title">Tạo bài viết</h4>
        </div>
        
        <form action="{{ route('principal.posts.store') }}" method="POST" enctype="multipart/form-data" onsubmit="handleRealtimePostSubmit(event, this)">
            @csrf
            <input type="hidden" name="eatery_id" value="{{ $school->id }}">

            <!-- User Header Row -->
            <div class="fb-modal-user-row">
                <img src="{{ $school->image_path ?: 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?auto=format&fit=crop&w=150&q=80' }}" class="fb-modal-user-avatar">
                <div class="fb-modal-user-info">
                    <h5 class="fb-modal-user-name">{{ $school->standardized_name }}</h5>
                    <div class="fb-modal-badges">
                        <span class="fb-modal-badge">🌐 Công khai ▾</span>
                        <span class="fb-modal-badge">⚙️ Thông báo trường ▾</span>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="fb-modal-body">
                <input type="text" name="name" required class="fb-modal-title-input" placeholder="Tiêu đề bài viết...">
                <textarea name="description" required class="fb-modal-textarea" rows="4" placeholder="{{ $school->standardized_name }} ơi, bạn đang nghĩ gì thế?"></textarea>
            </div>

            <!-- Multi Image Preview Box -->
            <div id="add-post-multi-preview" style="display: none; border-radius: 14px; overflow: hidden; margin-bottom: 16px; position: relative;">
                <div id="preview-grid" style="width: 100%;"></div>
            </div>

            <!-- Facebook Bottom Action Bar -->
            <div class="fb-modal-action-bar">
                <span class="fb-modal-action-label">Thêm vào bài viết của bạn</span>
                <div class="fb-modal-action-buttons">
                    <label class="fb-modal-action-btn" title="Thêm ảnh/video">
                        🖼️
                        <input type="file" name="images[]" multiple accept="image/*" class="fb-modal-file-input" onchange="previewMultiPostImages(this, 'add-post-multi-preview', 'preview-grid')">
                    </label>
                    <button type="button" class="fb-modal-action-btn" title="Gắn thẻ">🏷️</button>
                    <button type="button" class="fb-modal-action-btn" title="Cảm xúc">😊</button>
                    <button type="button" class="fb-modal-action-btn" title="Vị trí">📍</button>
                </div>
            </div>

            <button type="submit" class="fb-modal-submit-btn">
                Đăng
            </button>
        </form>
    </div>
</div>

<!-- Modal: Chỉnh sửa bài viết -->
<div class="sch-modal" id="editPostModal" onclick="if(event.target === this) closeModal('editPostModal')">
    <div class="sch-modal-content">
        <button type="button" onclick="closeModal('editPostModal')" class="sch-close-modal">✕</button>
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
<div class="sch-modal" id="addPhotoModal" onclick="if(event.target === this) closeModal('addPhotoModal')">
    <div class="sch-modal-content">
        <button type="button" onclick="closeModal('addPhotoModal')" class="sch-close-modal">✕</button>
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
<div class="sch-modal" id="addVideoModal" onclick="if(event.target === this) closeModal('addVideoModal')">
    <div class="sch-modal-content">
        <button type="button" onclick="closeModal('addVideoModal')" class="sch-close-modal">✕</button>
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
            modal.style.display = 'flex';
        }
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('show');
            modal.style.display = 'none';
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

    function clearMultiPostPreview(containerId, gridId) {
        const container = document.getElementById(containerId);
        const grid = document.getElementById(gridId);
        if (grid) grid.innerHTML = '';
        if (container) container.style.display = 'none';
        const fileInputs = document.querySelectorAll('input[type="file"][name="images[]"]');
        fileInputs.forEach(input => { input.value = ''; });
    }

    // Facebook multi-photo collage previewer for creation modal
    function previewMultiPostImages(input, containerId, gridId) {
        const container = document.getElementById(containerId);
        const grid = document.getElementById(gridId);
        if (!container || !grid) return;

        if (!input.files || input.files.length === 0) {
            container.style.display = 'none';
            grid.innerHTML = '';
            return;
        }

        container.style.display = 'block';
        grid.innerHTML = '';

        const files = Array.from(input.files);
        const total = files.length;
        let loadedCount = 0;
        const imageUrls = new Array(total);

        files.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                imageUrls[index] = e.target.result;
                loadedCount++;
                if (loadedCount === total) {
                    renderFbCollagePreview(grid, imageUrls, containerId);
                }
            };
            reader.readAsDataURL(file);
        });
    }

    function renderFbCollagePreview(grid, imageUrls, containerId) {
        const total = imageUrls.length;
        grid.innerHTML = '';

        const wrapper = document.createElement('div');
        wrapper.style.cssText = 'position: relative; width: 100%; border-radius: 14px; overflow: hidden; background: #0f172a; font-family: "Be Vietnam Pro", sans-serif; border: 1px solid #cbd5e1;';

        const topBar = document.createElement('div');
        topBar.style.cssText = 'position: absolute; top: 12px; left: 12px; right: 12px; display: flex; justify-content: space-between; align-items: center; z-index: 30; pointer-events: none;';
        topBar.innerHTML = `
            <button type="button" style="pointer-events: auto; background: #ffffff; color: #0f172a; border: none; padding: 7px 14px; border-radius: 20px; font-size: 0.8rem; font-weight: 800; display: flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); cursor: pointer;">
                ✏️ Chỉnh sửa tất cả
            </button>
            <button type="button" onclick="clearMultiPostPreview('${containerId}', '${grid.id}')" style="pointer-events: auto; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(4px); color: #ffffff; border: 1px solid rgba(255,255,255,0.2); width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1rem; font-weight: 900; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.3);">
                ✕
            </button>
        `;
        wrapper.appendChild(topBar);

        const flexGrid = document.createElement('div');
        flexGrid.style.cssText = 'display: flex; gap: 2px; width: 100%; height: 380px; background: #0f172a;';

        if (total === 1) {
            flexGrid.style.height = '320px';
            flexGrid.innerHTML = `<div style="width: 100%; height: 100%; overflow: hidden;"><img src="${imageUrls[0]}" style="width: 100%; height: 100%; object-fit: cover;"></div>`;
        } else if (total === 2) {
            flexGrid.style.height = '340px';
            flexGrid.innerHTML = `
                <div style="flex: 1; height: 100%; overflow: hidden;"><img src="${imageUrls[0]}" style="width: 100%; height: 100%; object-fit: cover;"></div>
                <div style="flex: 1; height: 100%; overflow: hidden;"><img src="${imageUrls[1]}" style="width: 100%; height: 100%; object-fit: cover;"></div>
            `;
        } else if (total === 3) {
            flexGrid.style.height = '360px';
            flexGrid.innerHTML = `
                <div style="flex: 1; height: 100%; overflow: hidden;"><img src="${imageUrls[0]}" style="width: 100%; height: 100%; object-fit: cover;"></div>
                <div style="flex: 1; height: 100%; display: flex; flex-direction: column; gap: 2px;">
                    <div style="flex: 1; overflow: hidden;"><img src="${imageUrls[1]}" style="width: 100%; height: 100%; object-fit: cover;"></div>
                    <div style="flex: 1; overflow: hidden;"><img src="${imageUrls[2]}" style="width: 100%; height: 100%; object-fit: cover;"></div>
                </div>
            `;
        } else if (total === 4) {
            flexGrid.style.height = '380px';
            flexGrid.innerHTML = `
                <div style="flex: 1.1; height: 100%; overflow: hidden;"><img src="${imageUrls[0]}" style="width: 100%; height: 100%; object-fit: cover;"></div>
                <div style="flex: 1; height: 100%; display: flex; flex-direction: column; gap: 2px;">
                    <div style="flex: 1; overflow: hidden;"><img src="${imageUrls[1]}" style="width: 100%; height: 100%; object-fit: cover;"></div>
                    <div style="flex: 1; overflow: hidden;"><img src="${imageUrls[2]}" style="width: 100%; height: 100%; object-fit: cover;"></div>
                    <div style="flex: 1; overflow: hidden;"><img src="${imageUrls[3]}" style="width: 100%; height: 100%; object-fit: cover;"></div>
                </div>
            `;
        } else {
            flexGrid.style.height = '390px';
            const extraCount = total - 4;

            flexGrid.innerHTML = `
                <div style="flex: 1; height: 100%; display: flex; flex-direction: column; gap: 2px;">
                    <div style="flex: 1; overflow: hidden;"><img src="${imageUrls[0]}" style="width: 100%; height: 100%; object-fit: cover;"></div>
                    <div style="flex: 1; overflow: hidden;"><img src="${imageUrls[1]}" style="width: 100%; height: 100%; object-fit: cover;"></div>
                </div>
                <div style="flex: 1; height: 100%; display: flex; flex-direction: column; gap: 2px;">
                    <div style="flex: 1; overflow: hidden;"><img src="${imageUrls[2]}" style="width: 100%; height: 100%; object-fit: cover;"></div>
                    <div style="flex: 1; overflow: hidden;"><img src="${imageUrls[3]}" style="width: 100%; height: 100%; object-fit: cover;"></div>
                    <div style="flex: 1; overflow: hidden; position: relative;">
                        <img src="${imageUrls[4]}" style="width: 100%; height: 100%; object-fit: cover;">
                        <div style="position: absolute; inset: 0; background: rgba(0, 0, 0, 0.45); backdrop-filter: blur(1px); display: flex; align-items: center; justify-content: center; color: #ffffff; font-size: 1.8rem; font-weight: 900; font-family: 'Be Vietnam Pro', sans-serif; letter-spacing: -0.5px;">+${extraCount}</div>
                    </div>
                </div>
            `;
        }

        wrapper.appendChild(flexGrid);
        grid.appendChild(wrapper);
    }

    function togglePostLike(btn, postId) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch('/api/reactions/toggle', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '',
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
                    btn.style.color = '';
                    btn.style.fontWeight = '';
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

    function togglePlaceHeart(btn, eateryId) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch('/api/reactions/toggle', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                id: eateryId,
                type: 'eatery',
                emoji: '❤️'
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const textEl = document.getElementById(`eatery-heart-text-${eateryId}`);
                if (data.liked) {
                    btn.style.background = '#ef4444';
                    btn.style.color = '#ffffff';
                    if (textEl) textEl.textContent = `Đã thả tim (${data.likes_count})`;
                } else {
                    btn.style.background = '';
                    btn.style.color = '';
                    if (textEl) textEl.textContent = `Thả tim địa điểm (${data.likes_count})`;
                }
            }
        })
        .catch(err => {
            console.error('Lỗi thả tim địa điểm:', err);
        });
    }

    async function shareFbPost(postId, postTitle, postImages) {
        const shareUrl = window.location.href;
        const titleText = postTitle ? ('Bài viết: ' + postTitle) : 'Chia sẻ bài viết';
        
        if (navigator.share) {
            const shareData = {
                title: titleText,
                text: postTitle ? (postTitle + ' — DongAnh Social') : 'Xem bài viết này trên DongAnh Social',
                url: shareUrl
            };

            let imagesArray = [];
            if (Array.isArray(postImages)) {
                imagesArray = postImages;
            } else if (typeof postImages === 'string' && postImages.startsWith('[')) {
                try { imagesArray = JSON.parse(postImages); } catch(e) {}
            }

            if (imagesArray.length > 0 && imagesArray[0]) {
                try {
                    const firstImgUrl = imagesArray[0];
                    const res = await fetch(firstImgUrl);
                    const blob = await res.blob();
                    const file = new File([blob], 'post-image.jpg', { type: blob.type || 'image/jpeg' });
                    if (navigator.canShare && navigator.canShare({ files: [file] })) {
                        shareData.files = [file];
                    }
                } catch (err) {
                    console.log('Non-critical share image fetch:', err);
                }
            }

            navigator.share(shareData).catch(() => {});
            return;
        }

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(shareUrl).then(() => {
                showToastNotification ? showToastNotification('🔄 Đã sao chép liên kết bài viết vào khay nhớ tạm!') : window.showToast('🔄 Đã sao chép liên kết bài viết!', 'success');
            }).catch(() => {
                fallbackCopyPostUrl(shareUrl);
            });
        } else {
            fallbackCopyPostUrl(shareUrl);
        }
    }

    // Toggle & Fetch Realtime Facebook Post Comments
    function toggleComments(postId, btnEl) {
        let commentBox = document.getElementById(`fb-comments-box-${postId}`);
        
        if (!commentBox) {
            let postCard = btnEl ? btnEl.closest('.fb-post-card') : null;
            if (!postCard) {
                postCard = document.getElementById(`post-like-btn-${postId}`)?.closest('.fb-post-card');
            }
            if (!postCard) return;

            commentBox = document.createElement('div');
            commentBox.id = `fb-comments-box-${postId}`;
            commentBox.className = 'fb-comments-section show';
            
            const currentUserAvatar = document.querySelector('.fb-modal-user-avatar')?.src || 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=100&q=80';

            commentBox.innerHTML = `
                <div class="fb-comments-list" id="fb-comments-list-${postId}">
                    <div style="font-size: 0.85rem; color: #94a3b8; text-align: center; padding: 10px;">⏳ Đang tải bình luận...</div>
                </div>
                <form class="fb-comment-input-box" onsubmit="submitPostComment(event, ${postId})">
                    <img src="${currentUserAvatar}" class="fb-comment-avatar">
                    <input type="text" id="fb-comment-input-${postId}" required class="fb-comment-input" placeholder="Viết bình luận công khai...">
                    <button type="submit" class="fb-comment-send-btn" title="Gửi bình luận">➤</button>
                </form>
            `;

            postCard.appendChild(commentBox);

            fetch(`/api/comments?id=${postId}&type=post`)
                .then(res => res.json())
                .then(data => {
                    const listEl = document.getElementById(`fb-comments-list-${postId}`);
                    if (!listEl) return;
                    
                    if (data.success && data.comments.length > 0) {
                        listEl.innerHTML = '';
                        data.comments.forEach(c => {
                            listEl.appendChild(createCommentItemElement(c));
                        });
                    } else {
                        listEl.innerHTML = `<div class="text-center py-2 text-muted" style="font-size: 0.85rem;" id="no-comments-msg-${postId}">Chưa có bình luận nào. Hãy là người đầu tiên bình luận!</div>`;
                    }
                })
                .catch(err => console.error('Lỗi tải bình luận:', err));
        } else {
            commentBox.classList.toggle('show');
        }
    }

    function submitPostComment(e, postId) {
        e.preventDefault();
        const inputEl = document.getElementById(`fb-comment-input-${postId}`);
        if (!inputEl || !inputEl.value.trim()) return;

        const content = inputEl.value.trim();
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        inputEl.disabled = true;

        fetch('/api/comments', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                id: postId,
                type: 'post',
                content: content
            })
        })
        .then(res => res.json())
        .then(data => {
            inputEl.disabled = false;
            inputEl.value = '';

            if (data.success) {
                const listEl = document.getElementById(`fb-comments-list-${postId}`);
                const noMsg = document.getElementById(`no-comments-msg-${postId}`);
                if (noMsg) noMsg.remove();

                if (listEl && data.comment) {
                    listEl.appendChild(createCommentItemElement(data.comment));
                    listEl.scrollTop = listEl.scrollHeight;
                }

                const postCard = document.getElementById(`post-like-btn-${postId}`)?.closest('.fb-post-card');
                const statsDivs = postCard?.querySelectorAll('.fb-post-stats div');
                if (statsDivs && statsDivs.length >= 2 && data.total_comments !== undefined) {
                    const rightStat = statsDivs[1];
                    const parts = rightStat.textContent.split('•');
                    const sharesPart = parts.length > 1 ? parts[1] : ' 0 chia sẻ';
                    rightStat.innerHTML = `💬 ${data.total_comments} bình luận •${sharesPart}`;
                }
            }
        })
        .catch(err => {
            inputEl.disabled = false;
            console.error('Lỗi gửi bình luận:', err);
        });
    }

    function createCommentItemElement(c) {
        const item = document.createElement('div');
        item.className = 'fb-comment-item animate__animated animate__fadeIn';
        item.innerHTML = `
            <img src="${c.author_avatar}" class="fb-comment-avatar" alt="${c.author_name}">
            <div>
                <div class="fb-comment-bubble">
                    <div class="fb-comment-author">${c.author_name}</div>
                    <div class="fb-comment-text">${escapeHtml(c.content)}</div>
                </div>
                <div class="fb-comment-meta">
                    <span>${c.created_at_human}</span>
                </div>
            </div>
        `;
        return item;
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    let currentGalleryImages = [];
    let currentGalleryIndex = 0;

    function openPostLightboxGallery(images, startIndex = 0) {
        if (!Array.isArray(images) || images.length === 0) return;
        currentGalleryImages = images;
        currentGalleryIndex = startIndex;

        let modal = document.getElementById('postLightboxModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'postLightboxModal';
            modal.style.cssText = 'position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.94); z-index: 10000; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(10px); flex-direction: column;';
            
            modal.innerHTML = `
                <button onclick="closePostLightbox()" style="position: absolute; top: 20px; right: 25px; color: #ffffff; font-size: 2.2rem; cursor: pointer; background: none; border: none; font-weight: bold; z-index: 10001; line-height: 1;">✕</button>
                <div id="postLightboxCounter" style="position: absolute; top: 24px; left: 30px; color: #ffffff; font-size: 0.95rem; font-weight: 700; font-family: 'Be Vietnam Pro', sans-serif; background: rgba(255,255,255,0.18); padding: 6px 16px; border-radius: 20px; backdrop-filter: blur(4px);"></div>
                
                <button id="postLightboxPrevBtn" onclick="navigateLightboxGallery(-1)" style="position: absolute; left: 20px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.2); color: white; border: none; width: 48px; height: 48px; border-radius: 50%; font-size: 1.5rem; cursor: pointer; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(4px); transition: all 0.2s;" onhover="this.style.background='rgba(255,255,255,0.4)'">❮</button>

                <img id="postLightboxImg" src="" style="max-width: 88vw; max-height: 82vh; border-radius: 12px; object-fit: contain; box-shadow: 0 25px 50px rgba(0,0,0,0.6); transition: opacity 0.15s ease-in-out;">

                <button id="postLightboxNextBtn" onclick="navigateLightboxGallery(1)" style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.2); color: white; border: none; width: 48px; height: 48px; border-radius: 50%; font-size: 1.5rem; cursor: pointer; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(4px); transition: all 0.2s;">❯</button>
            `;
            document.body.appendChild(modal);

            document.addEventListener('keydown', function(e) {
                const m = document.getElementById('postLightboxModal');
                if (!m || m.style.display === 'none') return;
                if (e.key === 'ArrowLeft') navigateLightboxGallery(-1);
                else if (e.key === 'ArrowRight') navigateLightboxGallery(1);
                else if (e.key === 'Escape') closePostLightbox();
            });
        }

        updateLightboxView();
        modal.style.display = 'flex';
    }

    function updateLightboxView() {
        const imgEl = document.getElementById('postLightboxImg');
        const counterEl = document.getElementById('postLightboxCounter');
        const prevBtn = document.getElementById('postLightboxPrevBtn');
        const nextBtn = document.getElementById('postLightboxNextBtn');

        if (!imgEl) return;

        imgEl.src = currentGalleryImages[currentGalleryIndex];
        if (counterEl) {
            counterEl.textContent = `Ảnh ${currentGalleryIndex + 1} / ${currentGalleryImages.length}`;
        }

        if (prevBtn) prevBtn.style.display = currentGalleryImages.length > 1 ? 'flex' : 'none';
        if (nextBtn) nextBtn.style.display = currentGalleryImages.length > 1 ? 'flex' : 'none';
    }

    function navigateLightboxGallery(direction) {
        if (!currentGalleryImages.length) return;
        currentGalleryIndex = (currentGalleryIndex + direction + currentGalleryImages.length) % currentGalleryImages.length;
        updateLightboxView();
    }

    function closePostLightbox() {
        const modal = document.getElementById('postLightboxModal');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    function openPostLightbox(src) {
        openPostLightboxGallery([src], 0);
    }

    function togglePostOptionsDropdown(event, menuId) {
        event.stopPropagation();
        const menu = document.getElementById(menuId);
        const isShown = menu ? menu.classList.contains('show') : false;
        
        // Close all open menus first
        document.querySelectorAll('.fb-post-dropdown-menu').forEach(m => m.classList.remove('show'));
        
        if (menu && !isShown) {
            menu.classList.add('show');
        }
    }

    // Close options dropdown when clicking outside
    document.addEventListener('click', function() {
        document.querySelectorAll('.fb-post-dropdown-menu').forEach(m => m.classList.remove('show'));
    });

    // Category filter for Photo Gallery
    function filterPhotoCategory(category, btnEl) {
        document.querySelectorAll('.sch-photo-filter-pill').forEach(b => b.classList.remove('active'));
        if (btnEl) btnEl.classList.add('active');

        document.querySelectorAll('.sch-photo-group-section').forEach(sec => {
            if (category === 'all' || sec.getAttribute('data-category') === category) {
                sec.style.display = 'block';
            } else {
                sec.style.display = 'none';
            }
        });
    }

    // Realtime Post Submission without page refresh
    function handleRealtimePostSubmit(e, form) {
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
                // Close modal
                closeModal('addPostModal');
                
                // Reset form inputs & image preview
                form.reset();
                const previewBox = document.getElementById('add-post-multi-preview');
                const previewGrid = document.getElementById('preview-grid');
                if (previewBox) previewBox.style.display = 'none';
                if (previewGrid) previewGrid.innerHTML = '';

                // Insert new post realtime into feed
                if (data.post) {
                    renderNewPostRealtime(data.post, data.school);
                }

                // Show Toast Alert
                showToastNotification('✅ Đăng bài viết mới thành công!');
            } else {
                alert(data.message || 'Có lỗi xảy ra khi đăng bài viết!');
            }
        })
        .catch(err => {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
            console.error('Lỗi realtime post:', err);
            form.submit();
        });
    }

    function renderNewPostRealtime(post, school) {
        let feedContainer = document.querySelector('.fb-posts-feed');
        const emptyState = document.querySelector('.sch-empty-state');
        
        if (!feedContainer && emptyState) {
            const parent = emptyState.parentElement;
            emptyState.remove();
            feedContainer = document.createElement('div');
            feedContainer.className = 'fb-posts-feed';
            parent.appendChild(feedContainer);
        }

        if (!feedContainer) return;

        const imgs = post.all_images || (post.images ? post.images : (post.image_path ? [post.image_path] : []));
        const imgCount = imgs.length;

        let photoGridHtml = '';
        if (imgCount === 1) {
            photoGridHtml = `<div class="fb-photo-grid fb-grid-1" onclick="openPostLightboxGallery(${JSON.stringify(imgs)}, 0)">
                <img src="${imgs[0]}" alt="${post.name}">
            </div>`;
        } else if (imgCount === 2) {
            photoGridHtml = `<div class="fb-photo-grid fb-grid-2">
                <img src="${imgs[0]}" onclick="openPostLightboxGallery(${JSON.stringify(imgs)}, 0)" alt="${post.name}">
                <img src="${imgs[1]}" onclick="openPostLightboxGallery(${JSON.stringify(imgs)}, 1)" alt="${post.name}">
            </div>`;
        } else if (imgCount === 3) {
            photoGridHtml = `<div class="fb-photo-grid fb-grid-3">
                <img src="${imgs[0]}" onclick="openPostLightboxGallery(${JSON.stringify(imgs)}, 0)" alt="${post.name}">
                <div class="fb-grid-3-col-right">
                    <img src="${imgs[1]}" onclick="openPostLightboxGallery(${JSON.stringify(imgs)}, 1)" alt="${post.name}">
                    <img src="${imgs[2]}" onclick="openPostLightboxGallery(${JSON.stringify(imgs)}, 2)" alt="${post.name}">
                </div>
            </div>`;
        } else if (imgCount === 4) {
            photoGridHtml = `<div class="fb-photo-grid fb-grid-4">
                <img src="${imgs[0]}" onclick="openPostLightboxGallery(${JSON.stringify(imgs)}, 0)" alt="${post.name}">
                <div class="fb-grid-4-col-right">
                    <img src="${imgs[1]}" onclick="openPostLightboxGallery(${JSON.stringify(imgs)}, 1)" alt="${post.name}">
                    <img src="${imgs[2]}" onclick="openPostLightboxGallery(${JSON.stringify(imgs)}, 2)" alt="${post.name}">
                    <img src="${imgs[3]}" onclick="openPostLightboxGallery(${JSON.stringify(imgs)}, 3)" alt="${post.name}">
                </div>
            </div>`;
        } else if (imgCount >= 5) {
            photoGridHtml = `<div class="fb-photo-grid fb-grid-5">
                <div class="fb-grid-5-row-top">
                    <img src="${imgs[0]}" onclick="openPostLightboxGallery(${JSON.stringify(imgs)}, 0)" alt="${post.name}">
                    <img src="${imgs[1]}" onclick="openPostLightboxGallery(${JSON.stringify(imgs)}, 1)" alt="${post.name}">
                </div>
                <div class="fb-grid-5-row-bottom">
                    <img src="${imgs[2]}" onclick="openPostLightboxGallery(${JSON.stringify(imgs)}, 2)" alt="${post.name}">
                    <img src="${imgs[3]}" onclick="openPostLightboxGallery(${JSON.stringify(imgs)}, 3)" alt="${post.name}">
                    <div class="fb-photo-thumb-box" onclick="openPostLightboxGallery(${JSON.stringify(imgs)}, 4)">
                        <img src="${imgs[4]}" alt="${post.name}">
                        ${imgCount > 5 ? `<div class="fb-photo-more-overlay">+${imgCount - 5}</div>` : ''}
                    </div>
                </div>
            </div>`;
        }

        const postCard = document.createElement('article');
        postCard.className = 'fb-post-card animate__animated animate__fadeInDown';
        postCard.style.animationDuration = '0.4s';
        postCard.innerHTML = `
            <div class="fb-post-header">
                <div class="fb-post-author-box">
                    <img src="${school ? school.image_path : ''}" class="fb-user-avatar" alt="${school ? school.name : ''}">
                    <div>
                        <h4 class="fb-post-author-name">${school ? school.name : ''}</h4>
                        <div class="fb-post-subtext">
                            <span>Vừa xong</span>
                            <span>•</span>
                            <span>🌐 Công khai</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="fb-post-text">
                <strong class="d-block mb-1 text-dark" style="font-size: 1.05rem;">🌸 ${post.name}</strong>
                ${post.description ? post.description.replace(/\n/g, '<br>') : ''}
            </div>
            ${photoGridHtml}
            <div class="fb-post-stats">
                <div id="post-likes-count-${post.id}">👍 0 lượt thích</div>
                <div>💬 0 bình luận • 0 chia sẻ</div>
            </div>
            <div class="fb-post-actions">
                <button class="fb-action-btn" id="post-like-btn-${post.id}" onclick="togglePostLike(this, ${post.id})">👍 Thích</button>
                <button class="fb-action-btn" onclick="alert('Tính năng bình luận bài viết đang được kết nối dữ liệu thực!')">💬 Bình luận</button>
                <button class="fb-action-btn" onclick="shareFbPost(${post.id}, ${JSON.stringify(post.name)}, ${JSON.stringify(imgs)})">🔄 Chia sẻ</button>
            </div>
        `;

        feedContainer.prepend(postCard);
        if (typeof initPostTextExpanders === 'function') {
            initPostTextExpanders(postCard);
        }

        if (imgs.length > 0) {
            prependPostPhotosToGallery(imgs, post.name);
        }
    }

    function prependPostPhotosToGallery(images, postTitle) {
        const postPhotoGrid = document.querySelector('.sch-photo-group-section[data-category="post"] .sch-photo-grid');
        if (!postPhotoGrid) return;

        images.forEach((url) => {
            const card = document.createElement('div');
            card.className = 'sch-photo-card animate__animated animate__fadeIn';
            card.onclick = function() { openPostLightbox(url); };
            card.innerHTML = `
                <img src="${url}" alt="${postTitle}" loading="lazy">
                <span class="sch-photo-type-tag tag-post">🖼️ Bài viết</span>
                <div class="sch-photo-overlay">
                    <p class="sch-photo-caption">Bài viết: ${postTitle}</p>
                    <span class="sch-photo-date">🕒 Vừa xong</span>
                    <div class="mt-2">
                        <button type="button" class="sch-btn sch-btn-sm sch-btn-accent w-100 justify-content-center">
                            🔍 Xem ảnh lớn
                        </button>
                    </div>
                </div>
            `;
            postPhotoGrid.prepend(card);
        });
    }

    function showToastNotification(msg) {
        const toast = document.createElement('div');
        toast.style.cssText = 'position: fixed; bottom: 25px; right: 25px; background: #065f46; color: #ffffff; padding: 14px 22px; border-radius: 14px; font-weight: 700; font-size: 0.95rem; font-family: "Be Vietnam Pro", sans-serif; box-shadow: 0 10px 30px rgba(0,0,0,0.2); z-index: 99999; animation: modalFadeIn 0.3s ease; display: flex; align-items: center; gap: 8px;';
        toast.innerHTML = msg;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s';
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    }
</script>
@endsection
