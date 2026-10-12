<style>
    /* ========================================================
       CINEMATIC DESIGN SYSTEM & BASE VARIABLES
       ======================================================== */
    :root {
        --exp-emerald: #10b981;
        --exp-emerald-dark: #059669;
        --exp-emerald-light: #34d399;
        --exp-cyan: #06b6d4;
        --exp-sky: #0ea5e9;
        --exp-amber: #f59e0b;
        --exp-purple: #8b5cf6;
    }

    /* Universal Zoom, Viewport & Fluid Layout Safeguards */
    html {
        box-sizing: border-box;
        -webkit-text-size-adjust: 100%;
        text-size-adjust: 100%;
    }
    *, *::before, *::after {
        box-sizing: inherit;
    }
    body {
        overflow-x: hidden !important;
        max-width: 100vw;
        width: 100%;
    }
    img, svg, video, canvas {
        max-width: 100%;
        height: auto;
    }

    /* Cinematic Hero Section */
    .cooking-hero {
        position: relative;
        padding: clamp(48px, 8vw, 85px) 16px clamp(36px, 6vw, 55px);
        background: radial-gradient(circle at 50% 25%, rgba(16, 185, 129, 0.14) 0%, rgba(6, 182, 212, 0.05) 35%, rgba(9, 9, 11, 0) 70%), var(--bg-base);
        overflow: hidden;
        border-bottom: 1px solid rgba(16, 185, 129, 0.12);
        text-align: center;
        box-sizing: border-box;
        width: 100%;
    }
    [data-theme="light"] .cooking-hero {
        background: radial-gradient(circle at 50% 25%, rgba(16, 185, 129, 0.09) 0%, rgba(14, 165, 233, 0.04) 40%, rgba(248, 250, 252, 0) 75%), var(--bg-base);
        border-bottom: 1px solid rgba(16, 185, 129, 0.15);
    }

    /* Ambient Lighting Orbs */
    .cooking-orb-1 {
        position: absolute;
        top: -160px;
        left: 8%;
        width: 520px;
        height: 520px;
        background: radial-gradient(circle, rgba(16, 185, 129, 0.18) 0%, rgba(16, 185, 129, 0) 70%);
        filter: blur(100px);
        pointer-events: none;
        animation: floatOrb1 18s ease-in-out infinite alternate;
        z-index: 0;
    }
    .cooking-orb-2 {
        position: absolute;
        bottom: -140px;
        right: 8%;
        width: 480px;
        height: 480px;
        background: radial-gradient(circle, rgba(6, 182, 212, 0.14) 0%, rgba(6, 182, 212, 0) 70%);
        filter: blur(90px);
        pointer-events: none;
        animation: floatOrb2 22s ease-in-out infinite alternate;
        z-index: 0;
    }

    @keyframes floatOrb1 {
        from { transform: translate(0, 0) scale(1); }
        to { transform: translate(40px, 30px) scale(1.1); }
    }
    @keyframes floatOrb2 {
        from { transform: translate(0, 0) scale(1); }
        to { transform: translate(-35px, -25px) scale(1.08); }
    }

    /* Slogan badge */
    .cooking-slogan {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 22px;
        position: relative;
        z-index: 2;
        max-width: 100%;
        flex-wrap: wrap;
        justify-content: center;
    }
    .cooking-slogan-badge {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(6, 182, 212, 0.1) 100%);
        border: 1.5px solid rgba(16, 185, 129, 0.35);
        color: #10b981;
        padding: 8px 20px;
        border-radius: 50px;
        font-size: 0.8rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        font-family: var(--font-heading);
        box-shadow: 0 4px 20px rgba(16, 185, 129, 0.15);
        backdrop-filter: blur(8px);
    }
    [data-theme="light"] .cooking-slogan-badge {
        background: #ecfdf5;
        border-color: #6ee7b7;
        color: #059669;
    }
    .cooking-slogan-sub {
        color: var(--text-muted);
        font-size: 0.88rem;
        font-weight: 600;
        border-left: 1.5px solid rgba(16, 185, 129, 0.3);
        padding-left: 12px;
        font-family: var(--font-body);
        font-style: italic;
    }
    @media (max-width: 600px) {
        .cooking-slogan {
            flex-direction: column;
            gap: 8px;
            margin-bottom: 16px;
        }
        .cooking-slogan-badge {
            font-size: 0.72rem;
            padding: 6px 14px;
            letter-spacing: 1px;
        }
        .cooking-slogan-sub {
            border-left: none !important;
            padding-left: 0 !important;
            font-size: 0.8rem;
            text-align: center;
        }
    }

    /* Headings */
    .cooking-title {
        font-family: var(--font-heading);
        font-size: clamp(1.65rem, 5vw, 3.5rem);
        font-weight: 900;
        letter-spacing: -0.8px;
        line-height: 1.2;
        position: relative;
        z-index: 2;
        margin: 0 0 18px 0;
        word-break: break-word;
    }
    .cooking-title-line1 {
        display: block;
        background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 40%, #10b981 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-weight: 900;
        filter: drop-shadow(0 2px 10px rgba(14, 165, 233, 0.2));
    }
    .cooking-title-green {
        display: block;
        background: linear-gradient(135deg, #10b981 0%, #059669 50%, #34d399 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-top: 6px;
    }
    [data-theme="light"] .cooking-title-green {
        background: linear-gradient(135deg, #059669 0%, #047857 60%, #10b981 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .cooking-subtitle {
        font-size: clamp(0.88rem, 2vw, 1.05rem);
        font-family: var(--font-body);
        color: var(--text-muted);
        line-height: 1.7;
        max-width: 760px;
        margin: 0 auto 28px;
        position: relative;
        z-index: 2;
        font-weight: 500;
        padding: 0 8px;
    }

    /* Quick Action Buttons in Hero */
    .hero-actions-row {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 36px;
        position: relative;
        z-index: 3;
    }
    .btn-hero-ai {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: linear-gradient(135deg, #10b981 0%, #059669 60%, #047857 100%);
        color: #ffffff !important;
        font-family: var(--font-heading);
        font-weight: 800;
        font-size: 0.95rem;
        padding: 13px 28px;
        border-radius: 50px;
        text-decoration: none;
        box-shadow: 0 8px 25px rgba(16, 185, 129, 0.35);
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    .btn-hero-ai:hover {
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 12px 35px rgba(16, 185, 129, 0.5);
    }
    .btn-hero-secondary {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: rgba(255, 255, 255, 0.08);
        color: var(--text-main) !important;
        font-family: var(--font-heading);
        font-weight: 700;
        font-size: 0.95rem;
        padding: 13px 26px;
        border-radius: 50px;
        text-decoration: none;
        border: 1px solid rgba(16, 185, 129, 0.3);
        backdrop-filter: blur(8px);
        transition: all 0.3s ease;
    }
    [data-theme="light"] .btn-hero-secondary {
        background: #ffffff;
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
    }
    .btn-hero-secondary:hover {
        border-color: #10b981;
        color: #10b981 !important;
        transform: translateY(-2px);
    }

    /* Stats Row */
    .cooking-stats {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 36px;
        flex-wrap: wrap;
        position: relative;
        z-index: 2;
        padding-top: 20px;
        border-top: 1px solid rgba(16, 185, 129, 0.15);
    }
    .cooking-stat-item {
        text-align: center;
    }
    .cooking-stat-value {
        font-family: var(--font-heading);
        font-size: 1.85rem;
        font-weight: 900;
        background: linear-gradient(135deg, #10b981 0%, #06b6d4 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        line-height: 1.1;
        display: block;
    }
    .cooking-stat-label {
        font-size: 0.76rem;
        color: var(--text-muted);
        font-weight: 600;
        margin-top: 4px;
        text-transform: uppercase;
        letter-spacing: 0.6px;
    }
    .cooking-stat-divider {
        width: 1px;
        height: 38px;
        background: rgba(16, 185, 129, 0.2);
    }

    /* ========================================================
       AI MATCHMAKER BENTO WIDGET
       ======================================================== */
    .ai-matchmaker-card {
        background: linear-gradient(145deg, rgba(16, 185, 129, 0.06) 0%, rgba(6, 182, 212, 0.03) 100%), var(--bg-card);
        border: 1.5px solid rgba(16, 185, 129, 0.3);
        border-radius: 28px;
        padding: 32px 36px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.06), 0 0 0 1px rgba(16, 185, 129, 0.1);
        margin-bottom: 50px;
        position: relative;
        overflow: hidden;
        box-sizing: border-box;
        width: 100%;
    }
    [data-theme="light"] .ai-matchmaker-card {
        background: linear-gradient(145deg, #ffffff 0%, #f0fdf4 100%);
        border: 1.5px solid #a7f3d0;
        box-shadow: 0 15px 35px rgba(16, 185, 129, 0.08);
    }
    @media (max-width: 768px) {
        .ai-matchmaker-card {
            padding: 24px 20px;
            border-radius: 22px;
            margin-bottom: 32px;
        }
    }
    @media (max-width: 480px) {
        .ai-matchmaker-card {
            padding: 18px 14px;
            border-radius: 18px;
            margin-bottom: 24px;
        }
    }
    .ai-criteria-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 24px;
    }
    .ai-matchmaker-footer {
        margin-top: 28px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px;
        border-top: 1px solid rgba(16, 185, 129, 0.15);
        padding-top: 20px;
    }
    .ai-footer-actions {
        display: flex;
        gap: 10px;
    }
    @media (max-width: 640px) {
        .ai-criteria-grid {
            grid-template-columns: 1fr;
            gap: 18px;
        }
        .ai-matchmaker-footer {
            flex-direction: column;
            align-items: stretch;
            gap: 14px;
        }
        .ai-footer-actions {
            width: 100%;
        }
        .ai-footer-actions button {
            flex: 1;
            justify-content: center;
        }
    }
    .ai-matchmaker-glow {
        position: absolute;
        top: -60px;
        right: -60px;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(16, 185, 129, 0.2) 0%, rgba(16, 185, 129, 0) 70%);
        filter: blur(40px);
        pointer-events: none;
    }
    .ai-step-title {
        font-size: 0.88rem;
        font-weight: 800;
        color: var(--text-main);
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-family: var(--font-heading);
    }
    .ai-chips-group {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    .ai-chip {
        padding: 8px 16px;
        border-radius: 50px;
        font-size: 0.82rem;
        font-weight: 700;
        cursor: pointer;
        border: 1.5px solid rgba(16, 185, 129, 0.2);
        background: rgba(255, 255, 255, 0.03);
        color: var(--text-muted);
        transition: all 0.25s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        user-select: none;
    }
    [data-theme="light"] .ai-chip {
        background: #ffffff;
        border-color: #e2e8f0;
        color: #475569;
    }
    .ai-chip:hover {
        border-color: #10b981;
        color: #10b981;
        transform: translateY(-2px);
    }
    .ai-chip.active {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border-color: #059669;
        color: #ffffff !important;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
    }

    /* AI Result Box */
    .ai-result-box {
        margin-top: 24px;
        padding: 20px 24px;
        border-radius: 20px;
        background: rgba(16, 185, 129, 0.08);
        border: 1.5px solid rgba(16, 185, 129, 0.35);
        animation: fadeIn 0.4s ease;
    }
    [data-theme="light"] .ai-result-box {
        background: #ecfdf5;
        border-color: #6ee7b7;
    }

    /* ========================================================
       MAIN CATALOG CONTAINER
       ======================================================== */
    .exp-main-container {
        max-width: 1240px;
        width: 100%;
        margin: 0 auto;
        padding: 45px 20px 80px !important;
        box-sizing: border-box;
        position: relative;
        z-index: 2;
    }
    @media (max-width: 768px) {
        .exp-main-container {
            padding: 28px 16px 60px !important;
        }
    }
    @media (max-width: 480px) {
        .exp-main-container {
            padding: 20px 12px 50px !important;
        }
    }

    /* ========================================================
       FILTER TABS & SEARCH BAR
       ======================================================== */
    .filter-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 28px;
    }
    .filter-tabs {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .tab-btn {
        padding: 9px 18px;
        border-radius: 50px;
        font-size: 0.84rem;
        font-weight: 800;
        font-family: var(--font-heading);
        cursor: pointer;
        border: 1.5px solid rgba(16, 185, 129, 0.2);
        background: var(--bg-card);
        color: var(--text-muted);
        transition: all 0.25s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        touch-action: manipulation;
    }
    [data-theme="light"] .tab-btn {
        background: #ffffff;
        border-color: #e2e8f0;
        color: #64748b;
    }
    .tab-btn:hover {
        border-color: #10b981;
        color: #10b981;
    }
    .tab-btn.active {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border-color: #059669;
        color: #ffffff !important;
        box-shadow: 0 4px 18px rgba(16, 185, 129, 0.3);
    }
    .tab-count {
        background: rgba(255, 255, 255, 0.25);
        color: inherit;
        font-size: 0.72rem;
        padding: 2px 7px;
        border-radius: 12px;
        font-weight: 900;
    }
    [data-theme="light"] .tab-btn:not(.active) .tab-count {
        background: #f1f5f9;
        color: #475569;
    }

    .filter-search-box {
        position: relative;
        min-width: 280px;
        flex-shrink: 0;
    }
    .filter-search-input {
        width: 100%;
        padding: 11px 18px 11px 42px;
        border-radius: 50px;
        border: 1.5px solid rgba(16, 185, 129, 0.25);
        background: var(--bg-card);
        color: var(--text-main);
        font-size: 0.88rem;
        outline: none;
        box-sizing: border-box;
        transition: all 0.25s ease;
    }
    [data-theme="light"] .filter-search-input {
        background: #ffffff;
        border-color: #cbd5e1;
    }
    .filter-search-input:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
    }
    .filter-search-icon {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 1rem;
        color: var(--text-muted);
        pointer-events: none;
    }

    /* Mobile & Tablet toolbar styling */
    @media (max-width: 860px) {
        .filter-toolbar {
            flex-direction: column;
            align-items: stretch;
            gap: 14px;
        }
        .filter-search-box {
            width: 100%;
            min-width: 0;
        }
        .filter-tabs {
            flex-wrap: nowrap;
            overflow-x: auto;
            padding-bottom: 6px;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            margin-inline: -12px;
            padding-inline: 12px;
        }
        .filter-tabs::-webkit-scrollbar {
            display: none;
        }
        .tab-btn {
            flex-shrink: 0;
            white-space: nowrap;
            padding: 8px 15px;
            font-size: 0.8rem;
        }
    }

    /* ========================================================
       BENTO CARD MODERN STYLING
       ======================================================== */
    .bento-exp-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 26px;
    }
    @media (max-width: 1100px) {
        .bento-exp-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }
    }
    @media (max-width: 680px) {
        .bento-exp-grid {
            grid-template-columns: 1fr;
            gap: 18px;
        }
    }

    .bento-card {
        border-radius: 24px;
        overflow: hidden;
        border: 1.5px solid rgba(16, 185, 129, 0.2);
        background: var(--bg-card);
        display: flex;
        flex-direction: column;
        transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
    }
    [data-theme="light"] .bento-card {
        background: #ffffff;
        border-color: #e2e8f0;
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.05);
    }
    .bento-card:hover {
        transform: translateY(-8px);
        border-color: rgba(16, 185, 129, 0.45);
        box-shadow: 0 20px 45px rgba(16, 185, 129, 0.18), 0 0 0 1px rgba(16, 185, 129, 0.3);
    }

    .bento-thumb-wrapper {
        position: relative;
        height: 220px;
        overflow: hidden;
        background: #0f172a;
    }
    .bento-thumb {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .bento-card:hover .bento-thumb {
        transform: scale(1.08);
    }
    .bento-thumb-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(0, 0, 0, 0.1) 0%, rgba(0, 0, 0, 0.65) 100%);
        pointer-events: none;
    }

    /* Badges */
    .bento-badge-type {
        position: absolute;
        top: 14px;
        left: 14px;
        padding: 6px 14px;
        border-radius: 50px;
        font-size: 0.74rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        backdrop-filter: blur(8px);
        z-index: 2;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .badge-tour { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
    .badge-experience { background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); }
    .badge-ticket { background: linear-gradient(135deg, #d97706 0%, #b45309 100%); }
    .badge-service { background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%); }
    .badge-craft { background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%); }

    .bento-badge-audience {
        position: absolute;
        top: 14px;
        right: 14px;
        background: rgba(15, 23, 42, 0.75);
        color: #38bdf8;
        border: 1px solid rgba(56, 189, 248, 0.4);
        padding: 5px 12px;
        border-radius: 50px;
        font-size: 0.72rem;
        font-weight: 800;
        backdrop-filter: blur(8px);
        z-index: 2;
    }

    .bento-location-tag {
        position: absolute;
        bottom: 14px;
        left: 14px;
        right: 14px;
        color: #ffffff;
        font-size: 0.82rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 6px;
        z-index: 2;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8);
    }

    .bento-body {
        padding: 24px;
        display: flex;
        flex-direction: column;
        flex: 1;
    }
    .bento-title {
        font-family: var(--font-heading);
        font-size: clamp(1.02rem, 1.8vw, 1.15rem);
        font-weight: 800;
        color: var(--text-main);
        line-height: 1.4;
        margin: 0 0 10px 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 2.8rem;
    }
    @media (max-width: 680px) {
        .bento-title {
            min-height: auto;
        }
    }
    .bento-desc {
        font-size: 0.86rem;
        color: var(--text-muted);
        line-height: 1.6;
        margin: 0 0 18px 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Meta Grid Responsive */
    .bento-meta-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1.25fr;
        gap: 6px 10px;
        background: rgba(16, 185, 129, 0.05);
        border: 1px solid rgba(16, 185, 129, 0.15);
        border-radius: 14px;
        padding: 10px 12px;
        margin-bottom: 20px;
        text-align: center;
        align-items: center;
    }
    [data-theme="light"] .bento-meta-grid {
        background: #f8fafc;
        border-color: #e2e8f0;
    }
    @media (max-width: 440px) {
        .bento-meta-grid {
            grid-template-columns: 1fr 1fr;
        }
        .bento-meta-grid > div:last-child {
            grid-column: span 2;
            border-top: 1px dashed rgba(16, 185, 129, 0.2);
            padding-top: 6px;
            margin-top: 2px;
        }
    }
    .bento-meta-label {
        font-size: 0.68rem;
        color: var(--text-muted);
        font-weight: 600;
        display: block;
        margin-bottom: 2px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .bento-meta-value {
        font-size: 0.82rem;
        font-weight: 800;
        color: var(--text-main);
        line-height: 1.35;
        word-break: break-word;
    }
    .bento-meta-value.price {
        color: #10b981;
    }
    [data-theme="light"] .bento-meta-value.price {
        color: #059669;
    }

    /* Card Action Buttons */
    .bento-actions {
        display: flex;
        gap: 10px;
        margin-top: auto;
    }
    @media (max-width: 440px) {
        .bento-actions {
            flex-direction: column;
            gap: 8px;
        }
        .btn-bento-primary, .btn-bento-secondary {
            width: 100%;
            justify-content: center;
            box-sizing: border-box;
        }
    }
    .btn-bento-primary {
        flex: 1.3;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: #ffffff !important;
        font-weight: 800;
        font-size: 0.88rem;
        padding: 12px;
        border-radius: 14px;
        text-decoration: none;
        border: none;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.25);
        transition: all 0.25s ease;
        cursor: pointer;
    }
    .btn-bento-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
    }
    .btn-bento-secondary {
        flex: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        background: rgba(16, 185, 129, 0.1);
        color: #10b981 !important;
        border: 1px solid rgba(16, 185, 129, 0.35);
        font-weight: 700;
        font-size: 0.84rem;
        padding: 12px;
        border-radius: 14px;
        text-decoration: none;
        transition: all 0.25s ease;
        cursor: pointer;
    }
    [data-theme="light"] .btn-bento-secondary {
        background: #ecfdf5;
        border-color: #a7f3d0;
        color: #059669 !important;
    }
    .btn-bento-secondary:hover {
        background: rgba(16, 185, 129, 0.2);
        transform: translateY(-2px);
    }

    /* ========================================================
       MODAL: ARTISAN CONTACT & QUICK BOOKING
       ======================================================== */
    .artisan-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.65);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        z-index: 10010;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        animation: fadeIn 0.3s ease;
    }
    .artisan-modal-content {
        width: 100%;
        max-width: 560px;
        max-height: 90vh;
        overflow-y: auto;
        background: #ffffff;
        border-radius: 28px;
        padding: 32px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 25px 60px rgba(15, 23, 42, 0.3);
        position: relative;
        color: #0f172a;
    }
    [data-theme="dark"] .artisan-modal-content {
        background: #18181b;
        border-color: rgba(255, 255, 255, 0.12);
        color: #f4f4f5;
    }
    .modal-close-btn {
        position: absolute;
        top: 20px;
        right: 20px;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        color: #64748b;
        font-size: 1.1rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }
    [data-theme="dark"] .modal-close-btn {
        background: #27272a;
        border-color: #3f3f46;
        color: #a1a1aa;
    }
    .modal-close-btn:hover {
        background: #e2e8f0;
        color: #0f172a;
    }

    /* ========================================================
       SCALABLE FEEDBACK SYSTEM: SCORECARD, PHOTOS & LIBRARY
       ======================================================== */
    .section-header-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 26px;
    }
    .section-header-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    @media (max-width: 680px) {
        .section-header-row {
            flex-direction: column;
            align-items: stretch;
            gap: 14px;
        }
        .section-header-actions {
            flex-direction: column;
            width: 100%;
            gap: 10px;
        }
        .section-header-actions button {
            width: 100%;
            justify-content: center;
        }
    }

    .feedback-scorecard,
    .scorecard-container {
        background: linear-gradient(145deg, rgba(16, 185, 129, 0.08) 0%, rgba(6, 182, 212, 0.04) 100%), var(--bg-card);
        border: 1.5px solid rgba(16, 185, 129, 0.25);
        border-radius: 24px;
        padding: 28px 32px;
        display: grid;
        grid-template-columns: 1fr 1.6fr;
        gap: 32px;
        align-items: center;
        margin-bottom: 28px;
    }
    [data-theme="light"] .feedback-scorecard,
    [data-theme="light"] .scorecard-container {
        background: linear-gradient(145deg, #ffffff 0%, #f0fdf4 100%);
        border-color: #a7f3d0;
        box-shadow: 0 10px 30px rgba(16, 185, 129, 0.06);
    }
    @media (max-width: 900px) {
        .feedback-scorecard,
        .scorecard-container {
            grid-template-columns: 1fr;
            gap: 22px;
            padding: 22px 20px;
        }
    }
    @media (max-width: 520px) {
        .feedback-scorecard,
        .scorecard-container {
            padding: 18px 14px;
            border-radius: 18px;
            margin-bottom: 20px;
        }
        .scorecard-big-number {
            font-size: 2.6rem;
        }
    }

    .scorecard-main {
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .scorecard-big-number {
        font-family: var(--font-heading);
        font-size: 3.2rem;
        font-weight: 900;
        line-height: 1;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .scorecard-criteria-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px 24px;
    }
    @media (max-width: 560px) {
        .scorecard-criteria-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }
    }
    .criteria-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .criteria-label-row {
        display: flex;
        justify-content: space-between;
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--text-main);
    }
    .criteria-bar-track {
        height: 6px;
        border-radius: 10px;
        background: rgba(16, 185, 129, 0.15);
        overflow: hidden;
    }
    [data-theme="light"] .criteria-bar-track {
        background: #e2e8f0;
    }
    .criteria-bar-fill {
        height: 100%;
        border-radius: 10px;
        background: linear-gradient(90deg, #10b981 0%, #059669 100%);
    }

    /* Guest Photo Row Responsive */
    .guest-photos-scroll {
        display: flex;
        gap: 12px;
        overflow-x: auto;
        padding-bottom: 12px;
        margin-bottom: 26px;
        -webkit-overflow-scrolling: touch;
        scroll-snap-type: x mandatory;
        scrollbar-width: thin;
        scrollbar-color: rgba(16, 185, 129, 0.4) transparent;
    }
    @media (max-width: 768px) {
        .guest-photos-scroll {
            margin-inline: -12px;
            padding-inline: 12px;
        }
    }
    .guest-photo-item {
        width: 140px;
        height: 105px;
        flex-shrink: 0;
        border-radius: 16px;
        overflow: hidden;
        border: 1.5px solid rgba(16, 185, 129, 0.2);
        cursor: pointer;
        position: relative;
        scroll-snap-align: start;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    @media (max-width: 600px) {
        .guest-photo-item {
            width: 120px;
            height: 90px;
            border-radius: 14px;
        }
    }
    .guest-photo-item:hover {
        transform: translateY(-4px) scale(1.03);
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
    }
    .guest-photo-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .guest-photo-label {
        position: absolute;
        bottom: 0;
        inset-inline: 0;
        padding: 4px 6px;
        background: linear-gradient(180deg, transparent 0%, rgba(0,0,0,0.85) 100%);
        color: #ffffff;
        font-size: 0.65rem;
        font-weight: 700;
        text-overflow: ellipsis;
        white-space: nowrap;
        overflow: hidden;
    }

    /* Feedback Filter Pills */
    .fb-filter-pills {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 24px;
    }
    @media (max-width: 768px) {
        .fb-filter-pills {
            flex-wrap: nowrap;
            overflow-x: auto;
            padding-bottom: 8px;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            margin-inline: -12px;
            padding-inline: 12px;
        }
        .fb-filter-pills::-webkit-scrollbar {
            display: none;
        }
    }
    .fb-pill {
        padding: 7px 15px;
        border-radius: 50px;
        font-size: 0.8rem;
        font-weight: 700;
        cursor: pointer;
        border: 1.5px solid rgba(16, 185, 129, 0.25);
        background: var(--bg-card);
        color: var(--text-muted);
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        flex-shrink: 0;
        white-space: nowrap;
        touch-action: manipulation;
    }
    [data-theme="light"] .fb-pill {
        background: #ffffff;
        border-color: #e2e8f0;
        color: #64748b;
    }
    .fb-pill:hover {
        border-color: #10b981;
        color: #10b981;
    }
    .fb-pill.active {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border-color: #059669;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
    }

    /* Feedback Cards Grid Responsive */
    .feedback-grid-container {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 22px;
    }
    @media (max-width: 1080px) {
        .feedback-grid-container {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }
    }
    @media (max-width: 680px) {
        .feedback-grid-container {
            grid-template-columns: 1fr;
            gap: 16px;
        }
    }

    .feedback-card {
        background: var(--bg-card);
        border: 1.5px solid rgba(16, 185, 129, 0.2);
        border-radius: 20px;
        padding: 22px;
        display: flex;
        flex-direction: column;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        box-shadow: 0 6px 20px rgba(0,0,0,0.03);
        box-sizing: border-box;
        min-width: 0;
    }
    @media (max-width: 480px) {
        .feedback-card {
            padding: 16px 14px;
            border-radius: 18px;
        }
    }

    /* Verified and Host Response */
    .verified-badge {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        font-size: 0.68rem;
        font-weight: 800;
        color: #059669;
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        padding: 2px 7px;
        border-radius: 12px;
    }
    .host-reply-box {
        margin-top: 14px;
        padding: 12px 14px;
        background: rgba(16, 185, 129, 0.05);
        border-left: 3px solid #10b981;
        border-radius: 0 12px 12px 0;
        font-size: 0.8rem;
        color: var(--text-main);
        line-height: 1.5;
    }
    [data-theme="light"] .host-reply-box {
        background: #f8fafc;
        border-color: #10b981;
    }
    .btn-helpful {
        background: transparent;
        border: 1px solid rgba(16, 185, 129, 0.25);
        border-radius: 20px;
        padding: 4px 12px;
        font-size: 0.74rem;
        color: var(--text-muted);
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.2s ease;
    }
    .btn-helpful:hover, .btn-helpful.active {
        border-color: #10b981;
        background: rgba(16, 185, 129, 0.1);
        color: #10b981;
    }

    /* Scaling controls bar */
    .feedback-scaling-bar {
        margin-top: 28px;
        padding: 20px 24px;
        border-radius: 20px;
        background: var(--bg-card);
        border: 1.5px solid rgba(16, 185, 129, 0.2);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
    }
    [data-theme="light"] .feedback-scaling-bar {
        background: #ffffff;
        border-color: #e2e8f0;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    }
    @media (max-width: 680px) {
        .feedback-scaling-bar {
            flex-direction: column;
            align-items: stretch;
            text-align: center;
            padding: 18px 16px;
            gap: 14px;
        }
        .feedback-scaling-bar > div:last-child {
            flex-direction: column;
            width: 100%;
            gap: 10px;
        }
        .feedback-scaling-bar button {
            width: 100%;
            justify-content: center;
        }
    }

    /* Fullscreen Review Library Drawer / Modal */
    .library-drawer-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.75);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        z-index: 10020;
        display: flex;
        justify-content: flex-end;
        animation: fadeIn 0.3s ease;
    }
    .library-drawer-content {
        width: 100%;
        max-width: 980px;
        height: 100vh;
        background: var(--bg-card);
        overflow-y: auto;
        box-shadow: -10px 0 50px rgba(0, 0, 0, 0.3);
        display: flex;
        flex-direction: column;
        color: var(--text-main);
    }
    [data-theme="light"] .library-drawer-content {
        background: #ffffff;
    }
    .library-drawer-header {
        padding: 20px 28px;
        border-bottom: 1px solid rgba(16, 185, 129, 0.2);
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: sticky;
        top: 0;
        background: var(--bg-card);
        z-index: 10;
        backdrop-filter: blur(10px);
    }
    [data-theme="light"] .library-drawer-header {
        background: #ffffff;
        border-color: #e2e8f0;
    }
    .library-drawer-body {
        padding: 28px;
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 32px;
        flex: 1;
    }
    @media (max-width: 820px) {
        .library-drawer-content {
            max-width: 100% !important;
            height: 100vh;
        }
        .library-drawer-body {
            grid-template-columns: 1fr;
            gap: 20px;
            padding: 18px 14px;
        }
        .library-drawer-body aside {
            position: static !important;
        }
        .library-drawer-header {
            padding: 16px 14px;
        }
    }
    @media (max-width: 640px) {
        .library-drawer-header {
            flex-direction: column;
            align-items: stretch;
            gap: 12px;
            padding: 14px 12px;
        }
        .drawer-header-left {
            gap: 10px;
        }
        .drawer-header-actions {
            width: 100%;
        }
        .drawer-header-actions button {
            width: 100%;
            justify-content: center;
        }
        .library-drawer-body {
            padding: 14px 10px;
        }
        .lib-review-item {
            padding: 16px 14px !important;
            border-radius: 16px !important;
        }
    }

    /* Modal Responsive & Touch Friendly Rules */
    .artisan-modal-overlay {
        box-sizing: border-box;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
    }
    @media (max-width: 540px) {
        .artisan-modal-overlay {
            padding: 12px 10px;
        }
        .artisan-modal-content {
            padding: 20px 16px;
            border-radius: 20px;
            max-height: 92vh;
            width: 100%;
            box-sizing: border-box;
        }
        .modal-close-btn {
            top: 12px;
            right: 12px;
            width: 34px;
            height: 34px;
            font-size: 0.95rem;
        }
    }

    /* Activity Detail Modal Deep Responsiveness */
    .modal-detail-content {
        box-sizing: border-box;
    }
    @media (max-width: 600px) {
        .modal-detail-cover {
            height: 200px !important;
        }
        .modal-detail-body {
            padding: 18px 16px !important;
            max-height: calc(88vh - 200px) !important;
        }
        .modal-detail-stats {
            gap: 6px !important;
            padding: 10px !important;
        }
    }
    @media (max-width: 440px) {
        .modal-detail-cover {
            height: 175px !important;
        }
        .modal-detail-body {
            padding: 14px 12px !important;
            max-height: calc(88vh - 175px) !important;
        }
        .modal-detail-stats {
            grid-template-columns: 1fr 1fr !important;
            gap: 8px !important;
        }
        .modal-detail-stats > div:last-child {
            grid-column: span 2;
            border-top: 1px dashed rgba(16, 185, 129, 0.2);
            padding-top: 6px;
        }
        .modal-detail-actions {
            flex-direction: column !important;
            gap: 10px !important;
        }
        .modal-detail-actions button,
        .modal-detail-actions a {
            width: 100% !important;
            flex: none !important;
            justify-content: center !important;
            box-sizing: border-box;
        }
        .modal-contact-row {
            grid-template-columns: 1fr !important;
            gap: 10px !important;
        }
        .modal-form-row {
            grid-template-columns: 1fr !important;
            gap: 10px !important;
        }
        .star-rating-options {
            flex-direction: column !important;
            gap: 8px !important;
        }
        .star-opt-label {
            width: 100% !important;
            box-sizing: border-box;
        }
    }

    /* Mobile iOS input auto-zoom prevention & tap sizes */
    @media (max-width: 768px) {
        input[type="text"],
        input[type="tel"],
        input[type="url"],
        input[type="date"],
        input[type="number"],
        select,
        textarea {
            font-size: 16px !important;
            box-sizing: border-box;
        }
    }

    /* Hero section responsive for mobile */
    @media (max-width: 540px) {
        .hero-actions-row {
            flex-direction: column;
            width: 100%;
            gap: 10px;
        }
        .btn-hero-ai,
        .btn-hero-secondary {
            width: 100%;
            justify-content: center;
            text-align: center;
            padding: 12px 20px;
            box-sizing: border-box;
        }
        .cooking-stats {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 14px !important;
        }
        .cooking-stat-divider {
            display: none !important;
        }
    }
    @media (max-width: 360px) {
        .cooking-stats {
            grid-template-columns: 1fr !important;
            gap: 10px !important;
        }
    }

    /* Footer responsive */
    @media (max-width: 768px) {
        .footer-grid {
            grid-template-columns: 1fr !important;
            gap: 28px !important;
        }
    }
</style>
