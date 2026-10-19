<style>
/* ══════════════════════════════════════════════════════════════════════════
   NÔNG SẢN ĐÔNG ANH - LUXURY HERITAGE DESIGN SYSTEM
   Impeccable & UI/UX Pro Max Architecture
   Colors: Deep Forest Pine, Antique Royal Gold, Warm Linen Ivory
   ══════════════════════════════════════════════════════════════════════════ */

:root {
    /* Typography */
    --ns-font-heading: 'Be Vietnam Pro', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    --ns-font-body: 'Be Vietnam Pro', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;

    /* Primary Heritage Emerald Palette */
    --ns-emerald-950: #022c22;
    --ns-emerald-900: #064e3b;
    --ns-emerald-800: #065f46;
    --ns-emerald-700: #047857;
    --ns-emerald-600: #059669;
    --ns-emerald-500: #10b981;
    --ns-emerald-400: #34d399;
    --ns-emerald-100: #d1fae5;
    --ns-emerald-50:  #ecfdf5;

    /* Antique Royal Gold & Ochre Palette */
    --ns-gold-900: #78350f;
    --ns-gold-800: #92400e;
    --ns-gold-700: #b45309;
    --ns-gold-600: #d97706;
    --ns-gold-500: #f59e0b;
    --ns-gold-400: #fbbf24;
    --ns-gold-300: #fcd34d;
    --ns-gold-200: #fde68a;
    --ns-gold-100: #fef3c7;
    --ns-gold-50:  #fffbeb;

    /* Terracotta Heritage Accents */
    --ns-terra-800: #9a3412;
    --ns-terra-700: #c2410c;
    --ns-crimson:   #881337;

    /* Surfaces & Neutrals (Light Theme) */
    --ns-cream:        #faf8f5;
    --ns-cream-deep:   #f5f0e8;
    --ns-surface:      #ffffff;
    --ns-surface-card: #ffffff;
    --ns-text-dark:    #0f172a;
    --ns-text-body:    #334155;
    --ns-text-muted:   #64748b;
    --ns-border-light: rgba(10, 52, 39, 0.08);
    --ns-border-subtle: rgba(226, 232, 240, 0.9);
    --ns-border-gold:  rgba(217, 119, 6, 0.35);

    /* Layered Diffuse Shadows */
    --ns-shadow-xs: 0 1px 3px rgba(6, 40, 30, 0.04);
    --ns-shadow-sm: 0 4px 12px -2px rgba(6, 40, 30, 0.05);
    --ns-shadow-md: 0 12px 28px -4px rgba(6, 40, 30, 0.08), 0 4px 10px -2px rgba(6, 40, 30, 0.03);
    --ns-shadow-lg: 0 22px 48px -8px rgba(6, 40, 30, 0.12), 0 6px 16px -4px rgba(6, 40, 30, 0.04);
    --ns-shadow-gold: 0 14px 38px -6px rgba(217, 119, 6, 0.18), 0 0 20px rgba(245, 158, 11, 0.12);
    --ns-shadow-emerald: 0 14px 38px -6px rgba(5, 150, 105, 0.22);

    /* Radii */
    --ns-radius-sm: 10px;
    --ns-radius-md: 16px;
    --ns-radius-lg: 24px;
    --ns-radius-xl: 32px;
    --ns-radius-full: 9999px;
}

[data-theme="dark"] {
    --ns-cream:        #071913;
    --ns-cream-deep:   #0a221a;
    --ns-surface:      #0e2a21;
    --ns-surface-card: #0e2a21;
    --ns-text-dark:    #f8fafc;
    --ns-text-body:    #cbd5e1;
    --ns-text-muted:   #94a3b8;
    --ns-border-light: rgba(255, 255, 255, 0.08);
    --ns-border-subtle: rgba(16, 185, 129, 0.18);
    --ns-border-gold:  rgba(245, 158, 11, 0.4);
    --ns-shadow-sm: 0 4px 14px rgba(0, 0, 0, 0.4);
    --ns-shadow-md: 0 14px 32px rgba(0, 0, 0, 0.5);
    --ns-shadow-lg: 0 24px 54px rgba(0, 0, 0, 0.65);
}

/* Base Page Wrapper */
.ns-page-wrapper {
    background: var(--ns-cream);
    color: var(--ns-text-body);
    font-family: var(--ns-font-body);
    min-height: 100vh;
    overflow-x: hidden;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

/* ══════════════════════════════════════════════════════════════════════════
   1. CINEMATIC LUXURY HERO
   ══════════════════════════════════════════════════════════════════════════ */

.ns-hero-cinematic {
    position: relative;
    background: radial-gradient(circle at 18% 25%, rgba(16, 185, 129, 0.25) 0%, transparent 48%),
                radial-gradient(circle at 82% 70%, rgba(245, 158, 11, 0.2) 0%, transparent 45%),
                linear-gradient(175deg, #02241b 0%, #064e3b 50%, #032d22 100%);
    padding: 85px 20px 80px;
    overflow: hidden;
    color: #ffffff;
    text-align: center;
    border-bottom: 1px solid rgba(245, 158, 11, 0.25);
}

.ns-hero-ambient-orb-1 {
    position: absolute;
    top: -100px;
    left: -100px;
    width: 380px;
    height: 380px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(16, 185, 129, 0.32) 0%, transparent 70%);
    filter: blur(55px);
    pointer-events: none;
    animation: nsOrbFloat1 9s infinite alternate ease-in-out;
}

.ns-hero-ambient-orb-2 {
    position: absolute;
    bottom: -80px;
    right: -80px;
    width: 360px;
    height: 360px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(245, 158, 11, 0.26) 0%, transparent 70%);
    filter: blur(50px);
    pointer-events: none;
    animation: nsOrbFloat2 7s infinite alternate-reverse ease-in-out;
}

@keyframes nsOrbFloat1 {
    0%   { transform: scale(1) translate(0, 0); opacity: 0.6; }
    100% { transform: scale(1.18) translate(25px, -20px); opacity: 0.95; }
}

@keyframes nsOrbFloat2 {
    0%   { transform: scale(1) translate(0, 0); opacity: 0.55; }
    100% { transform: scale(1.15) translate(-20px, 25px); opacity: 0.9; }
}

.ns-hero-pattern-overlay {
    position: absolute;
    inset: 0;
    opacity: 0.04;
    background-image: radial-gradient(#ffffff 1px, transparent 1px);
    background-size: 24px 24px;
    pointer-events: none;
}

.ns-hero-inner {
    max-width: 1100px;
    margin: 0 auto;
    position: relative;
    z-index: 2;
}

/* Slogan Pill with Pulsing Live Beacon */
.ns-hero-slogan-pill {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 7px 22px;
    border-radius: var(--ns-radius-full);
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    border: 1px solid rgba(245, 158, 11, 0.45);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
    margin-bottom: 22px;
    animation: nsFadeInDown 0.6s ease-out;
}

.ns-pulse-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #34d399;
    box-shadow: 0 0 0 0 rgba(52, 211, 153, 0.8);
    animation: nsPulseBeacon 2s infinite;
}

@keyframes nsPulseBeacon {
    0%   { box-shadow: 0 0 0 0 rgba(52, 211, 153, 0.8); }
    70%  { box-shadow: 0 0 0 8px rgba(52, 211, 153, 0); }
    100% { box-shadow: 0 0 0 0 rgba(52, 211, 153, 0); }
}

.ns-slogan-gold {
    font-size: 0.78rem;
    font-weight: 800;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: #fef08a;
}

.ns-slogan-dot {
    color: rgba(254, 240, 138, 0.45);
    font-size: 0.8rem;
}

.ns-slogan-sub {
    font-size: 0.78rem;
    font-weight: 700;
    color: #a7f3d0;
    letter-spacing: 0.4px;
}

/* Main Hero Headline */
.ns-hero-main-title {
    font-family: var(--ns-font-heading);
    font-size: clamp(2.3rem, 5.2vw, 4.1rem);
    font-weight: 900;
    line-height: 1.14;
    margin: 0 0 20px;
    letter-spacing: -0.02em;
}

.ns-title-gold {
    background: linear-gradient(135deg, #fffbeb 0%, #fef08a 25%, #f59e0b 65%, #d97706 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    text-shadow: 0 0 40px rgba(245, 158, 11, 0.35);
}

.ns-hero-subtitle {
    max-width: 800px;
    margin: 0 auto 30px;
    font-size: 1.05rem;
    line-height: 1.7;
    color: rgba(255, 255, 255, 0.88);
    font-weight: 400;
}

/* Quick Navigation Anchor Chips */
.ns-hero-anchors-row {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 38px;
}

.ns-hero-anchor-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 16px;
    border-radius: var(--ns-radius-full);
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.16);
    color: #ffffff;
    font-size: 0.82rem;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.25s ease;
    cursor: pointer;
}

.ns-hero-anchor-chip:hover {
    background: rgba(245, 158, 11, 0.22);
    border-color: rgba(245, 158, 11, 0.6);
    color: #fef08a;
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.25);
}

/* Architectural Glass Stats Banner */
.ns-hero-stats-glass {
    display: inline-flex;
    align-items: center;
    gap: 24px;
    padding: 16px 36px;
    border-radius: var(--ns-radius-full);
    background: rgba(255, 255, 255, 0.07);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.18);
    box-shadow: 0 14px 40px rgba(0, 0, 0, 0.32);
    flex-wrap: wrap;
    justify-content: center;
}

.ns-stat-box {
    display: flex;
    align-items: center;
    gap: 12px;
    text-align: left;
}

.ns-stat-icon-wrap {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: rgba(16, 185, 129, 0.22);
    border: 1px solid rgba(16, 185, 129, 0.45);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.ns-stat-text-wrap {
    display: flex;
    flex-direction: column;
}

.ns-stat-num {
    font-size: 1.25rem;
    font-weight: 900;
    color: #ffffff;
    line-height: 1.1;
    letter-spacing: -0.01em;
}

.ns-stat-lbl {
    font-size: 0.74rem;
    color: rgba(255, 255, 255, 0.75);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 2px;
}

.ns-stat-sep {
    width: 1px;
    height: 32px;
    background: rgba(255, 255, 255, 0.16);
}

/* ══════════════════════════════════════════════════════════════════════════
   2. SPOTLIGHT HERITAGE GRAND BOX (MÂM CỖ CỔ LOA XƯA)
   ══════════════════════════════════════════════════════════════════════════ */

.ns-spotlight-section {
    max-width: 1220px;
    margin: -40px auto 55px;
    padding: 0 20px;
    position: relative;
    z-index: 10;
}

.ns-mamco-card {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.98) 0%, rgba(254, 252, 246, 0.98) 100%);
    border-radius: var(--ns-radius-xl);
    border: 2px solid rgba(245, 158, 11, 0.45);
    box-shadow: 0 24px 60px rgba(6, 40, 30, 0.12), 0 0 35px rgba(245, 158, 11, 0.12);
    padding: 38px 42px;
    display: grid;
    grid-template-columns: 1.25fr 1fr;
    gap: 40px;
    align-items: center;
    position: relative;
    overflow: hidden;
}

[data-theme="dark"] .ns-mamco-card {
    background: linear-gradient(135deg, rgba(14, 42, 33, 0.98) 0%, rgba(7, 26, 20, 0.98) 100%);
    border-color: rgba(245, 158, 11, 0.45);
}

.ns-mamco-left {
    display: flex;
    flex-direction: column;
}

.ns-spotlight-badge-wrap {
    margin-bottom: 12px;
}

.ns-spotlight-tag {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 6px 16px;
    border-radius: var(--ns-radius-full);
    background: linear-gradient(135deg, #f59e0b 0%, #b45309 100%);
    color: #ffffff;
    font-size: 0.76rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    box-shadow: 0 4px 14px rgba(245, 158, 11, 0.35);
}

.ns-tag-crown {
    font-size: 0.85rem;
}

.ns-spotlight-title {
    font-family: var(--ns-font-heading);
    font-size: clamp(1.8rem, 3.2vw, 2.4rem);
    font-weight: 900;
    line-height: 1.2;
    color: var(--ns-text-dark);
    margin: 0 0 6px;
    letter-spacing: -0.01em;
}

.ns-mamco-slogan {
    font-style: italic;
    color: #b45309;
    font-size: 0.88rem;
    font-weight: 700;
    margin: 0 0 14px;
    line-height: 1.5;
}

[data-theme="dark"] .ns-mamco-slogan {
    color: #fcd34d;
}

.ns-spotlight-desc {
    font-size: 0.93rem;
    line-height: 1.68;
    color: var(--ns-text-body);
    margin: 0 0 20px;
}

/* 4-Course Royal Bento Grid */
.ns-mamco-categories-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 24px;
}

.ns-mamco-cat-box {
    background: var(--ns-cream);
    border: 1px solid var(--ns-border-subtle);
    border-radius: var(--ns-radius-md);
    padding: 12px 14px;
    transition: all 0.25s ease;
    display: flex;
    flex-direction: column;
}

[data-theme="dark"] .ns-mamco-cat-box {
    background: rgba(16, 185, 129, 0.06);
    border-color: rgba(16, 185, 129, 0.2);
}

.ns-mamco-cat-box:hover {
    border-color: #f59e0b;
    background: rgba(245, 158, 11, 0.05);
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.05);
}

.ns-mamco-cat-head {
    font-size: 0.76rem;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.ns-cat-man .ns-mamco-cat-head  { color: #9a3412; }
.ns-cat-banh .ns-mamco-cat-head { color: #b45309; }
.ns-cat-rau .ns-mamco-cat-head  { color: #047857; }
.ns-cat-tra .ns-mamco-cat-head  { color: #0f766e; }

[data-theme="dark"] .ns-cat-man .ns-mamco-cat-head  { color: #fdba74; }
[data-theme="dark"] .ns-cat-banh .ns-mamco-cat-head { color: #fde68a; }
[data-theme="dark"] .ns-cat-rau .ns-mamco-cat-head  { color: #6ee7b7; }
[data-theme="dark"] .ns-cat-tra .ns-mamco-cat-head  { color: #5eead4; }

.ns-mamco-cat-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
}

.ns-cat-chip {
    font-size: 0.74rem;
    font-weight: 600;
    color: var(--ns-text-body);
    background: rgba(255, 255, 255, 0.85);
    border: 1px solid rgba(0, 0, 0, 0.06);
    padding: 2px 7px;
    border-radius: 6px;
    line-height: 1.4;
}

[data-theme="dark"] .ns-cat-chip {
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(255, 255, 255, 0.1);
    color: #e2e8f0;
}

/* Actions Row */
.ns-mamco-actions-row {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.ns-btn-primary {
    padding: 13px 24px;
    border-radius: var(--ns-radius-md);
    border: none;
    background: linear-gradient(135deg, #059669 0%, #10b981 100%);
    color: #ffffff;
    font-weight: 800;
    font-size: 0.92rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    box-shadow: 0 4px 18px rgba(16, 185, 129, 0.38);
    transition: all 0.25s ease;
    text-decoration: none;
}

.ns-btn-primary:hover {
    filter: brightness(1.08);
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(16, 185, 129, 0.48);
}

.ns-btn-secondary {
    padding: 12px 20px;
    border-radius: var(--ns-radius-md);
    border: 1.5px solid rgba(5, 150, 105, 0.45);
    background: transparent;
    color: #059669;
    font-weight: 800;
    font-size: 0.9rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    transition: all 0.25s ease;
    text-decoration: none;
}

.ns-btn-secondary:hover {
    background: rgba(16, 185, 129, 0.1);
    border-color: #059669;
    transform: translateY(-2px);
}

.ns-hotline-pill {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    color: #059669;
    font-size: 0.88rem;
    text-decoration: none;
    padding: 10px 16px;
    border-radius: var(--ns-radius-md);
    background: rgba(16, 185, 129, 0.08);
    border: 1px solid rgba(16, 185, 129, 0.25);
    font-weight: 700;
    transition: all 0.25s ease;
}

.ns-hotline-pill:hover {
    background: rgba(16, 185, 129, 0.15);
    color: #047857;
    transform: translateY(-1px);
}

/* Right Media Showcase Card */
.ns-mamco-media-card {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.ns-mamco-tabs-bar {
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(16, 185, 129, 0.08);
    padding: 5px;
    border-radius: var(--ns-radius-full);
    border: 1px solid rgba(16, 185, 129, 0.22);
}

.ns-mamco-tab-btn {
    flex: 1;
    padding: 9px 16px;
    border-radius: var(--ns-radius-full);
    border: none;
    background: transparent;
    color: var(--ns-text-muted);
    font-weight: 800;
    font-size: 0.82rem;
    cursor: pointer;
    transition: all 0.25s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    white-space: nowrap;
}

.ns-mamco-tab-btn.active {
    background: linear-gradient(135deg, #059669 0%, #10b981 100%);
    color: #ffffff;
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
}

.ns-mamco-img-frame {
    position: relative;
    width: 100%;
    height: 480px;
    border-radius: var(--ns-radius-lg);
    overflow: hidden;
    background: #180e07 radial-gradient(circle, #2a150a 0%, #100803 100%);
    border: 2px solid rgba(217, 119, 6, 0.45);
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.32), 0 0 24px rgba(217, 119, 6, 0.15);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 12px;
}

.ns-mamco-img-frame img {
    max-width: 100%;
    max-height: 100%;
    width: auto;
    height: auto;
    object-fit: contain;
    border-radius: var(--ns-radius-sm);
    box-shadow: 0 10px 24px rgba(0, 0, 0, 0.5);
    transition: transform 0.4s ease;
}

.ns-mamco-img-frame:hover img {
    transform: scale(1.02);
}

.ns-mamco-img-floating-tag {
    position: absolute;
    bottom: 14px;
    left: 14px;
    right: 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: rgba(6, 40, 30, 0.9);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    padding: 8px 16px;
    border-radius: var(--ns-radius-full);
    color: #fef08a;
    font-size: 0.76rem;
    font-weight: 800;
    border: 1px solid rgba(254, 240, 138, 0.3);
}

.ns-zoom-badge {
    background: rgba(254, 240, 138, 0.18);
    padding: 2px 8px;
    border-radius: 20px;
    font-size: 0.72rem;
}

.ns-mamco-caption-bar {
    display: flex;
    gap: 12px;
    align-items: center;
    justify-content: space-between;
    padding: 0 4px;
}

.ns-caption-hint {
    font-size: 0.78rem;
    color: var(--ns-text-muted);
}

.ns-story-link-btn {
    background: none;
    border: none;
    color: #059669;
    font-size: 0.8rem;
    font-weight: 800;
    cursor: pointer;
    text-decoration: underline;
    white-space: nowrap;
}

/* ══════════════════════════════════════════════════════════════════════════
   2.5. TRUST PILLARS STRIP (3 KHÔNG & MINH BẠCH BẢN ĐỊA)
   ══════════════════════════════════════════════════════════════════════════ */

.ns-trust-strip {
    max-width: 1220px;
    margin: 0 auto 55px;
    padding: 0 20px;
}

.ns-trust-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
}

.ns-trust-card {
    background: var(--ns-surface);
    border-radius: var(--ns-radius-lg);
    border: 1.5px solid var(--ns-border-light);
    padding: 22px 20px;
    display: flex;
    align-items: flex-start;
    gap: 16px;
    box-shadow: var(--ns-shadow-xs);
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.ns-trust-card:hover {
    border-color: rgba(16, 185, 129, 0.45);
    transform: translateY(-4px);
    box-shadow: var(--ns-shadow-md);
}

.ns-trust-icon-box {
    width: 48px;
    height: 48px;
    border-radius: var(--ns-radius-md);
    background: rgba(16, 185, 129, 0.12);
    border: 1px solid rgba(16, 185, 129, 0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.45rem;
    flex-shrink: 0;
}

.ns-trust-content {
    display: flex;
    flex-direction: column;
}

.ns-trust-title {
    font-family: var(--ns-font-heading);
    font-size: 0.95rem;
    font-weight: 800;
    color: var(--ns-text-dark);
    margin: 0 0 4px;
    line-height: 1.3;
}

.ns-trust-desc {
    font-size: 0.8rem;
    color: var(--ns-text-muted);
    line-height: 1.55;
    margin: 0;
}

/* ══════════════════════════════════════════════════════════════════════════
   3. STICKY FILTER & SEARCH BAR
   ══════════════════════════════════════════════════════════════════════════ */

.ns-filter-sticky {
    position: sticky;
    top: 68px;
    z-index: 30;
    background: rgba(250, 248, 245, 0.94);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-top: 1px solid var(--ns-border-subtle);
    border-bottom: 1px solid var(--ns-border-subtle);
    padding: 16px 0;
    margin-bottom: 45px;
    transition: all 0.3s;
}

[data-theme="dark"] .ns-filter-sticky {
    background: rgba(7, 25, 19, 0.94);
}

.ns-filter-container {
    max-width: 1220px;
    margin: 0 auto;
    padding: 0 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap;
}

.ns-pills-row {
    display: flex;
    align-items: center;
    gap: 9px;
    overflow-x: auto;
    padding: 2px 0 6px;
    scrollbar-width: none;
    -webkit-overflow-scrolling: touch;
}
.ns-pills-row::-webkit-scrollbar { display: none; }

.ns-filter-pill {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 10px 20px;
    border-radius: var(--ns-radius-full);
    border: 1.5px solid var(--ns-border-subtle);
    background: var(--ns-surface);
    color: var(--ns-text-muted);
    font-size: 0.86rem;
    font-weight: 800;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.25s cubic-bezier(0.2, 0.8, 0.2, 1);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
}

.ns-filter-pill:hover {
    border-color: #10b981;
    color: #059669;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(16, 185, 129, 0.15);
}

.ns-filter-pill.active {
    background: linear-gradient(135deg, #059669 0%, #10b981 100%);
    color: #ffffff;
    border-color: transparent;
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.38);
    transform: translateY(-1px);
}

.ns-search-box {
    position: relative;
    min-width: 290px;
    display: flex;
    align-items: center;
}

.ns-search-icon {
    position: absolute;
    left: 16px;
    color: var(--ns-text-muted);
    font-size: 0.95rem;
    pointer-events: none;
}

.ns-search-field {
    width: 100%;
    padding: 11px 42px 11px 42px;
    border-radius: var(--ns-radius-full);
    border: 1.5px solid var(--ns-border-subtle);
    background: var(--ns-surface);
    color: var(--ns-text-dark);
    font-size: 0.88rem;
    font-weight: 600;
    outline: none;
    transition: all 0.25s ease;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
}

.ns-search-field:focus {
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
}

.ns-search-clear-btn {
    position: absolute;
    right: 12px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    border: none;
    background: rgba(0, 0, 0, 0.1);
    color: var(--ns-text-muted);
    font-size: 0.72rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.ns-search-clear-btn:hover {
    background: #ef4444;
    color: #ffffff;
}

/* ══════════════════════════════════════════════════════════════════════════
   4. BENTO PRODUCT CARDS GRID
   ══════════════════════════════════════════════════════════════════════════ */

.ns-catalog-container {
    max-width: 1220px;
    margin: 0 auto 75px;
    padding: 0 20px;
}

.ns-cards-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 28px;
}

.ns-product-card {
    background: var(--ns-surface-card);
    border-radius: var(--ns-radius-lg);
    border: 1.5px solid var(--ns-border-light);
    box-shadow: var(--ns-shadow-sm);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    position: relative;
    transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    cursor: pointer;
}

.ns-product-card:hover {
    transform: translateY(-7px);
    box-shadow: var(--ns-shadow-emerald);
    border-color: rgba(16, 185, 129, 0.45);
}

.ns-thumb-container {
    position: relative;
    width: 100%;
    aspect-ratio: 16 / 10;
    background: #0f172a;
    overflow: hidden;
}

.ns-thumb-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.65s cubic-bezier(0.2, 0.8, 0.2, 1);
}

.ns-product-card:hover .ns-thumb-img {
    transform: scale(1.07);
}

.ns-thumb-gradient {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(0,0,0,0.12) 0%, rgba(6, 40, 30, 0.88) 100%);
    pointer-events: none;
}

.ns-badge-floating {
    position: absolute;
    top: 14px;
    left: 14px;
    padding: 6px 14px;
    border-radius: var(--ns-radius-full);
    background: rgba(6, 40, 30, 0.86);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    color: #fef08a;
    font-size: 0.74rem;
    font-weight: 800;
    letter-spacing: 0.4px;
    border: 1px solid rgba(254, 240, 138, 0.35);
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.3);
    z-index: 2;
}

.ns-origin-floating {
    position: absolute;
    bottom: 12px;
    left: 16px;
    right: 16px;
    display: flex;
    align-items: center;
    gap: 6px;
    color: rgba(255, 255, 255, 0.95);
    font-size: 0.8rem;
    font-weight: 700;
    text-shadow: 0 1px 4px rgba(0, 0, 0, 0.9);
    z-index: 2;
}

.ns-card-content {
    padding: 22px 24px;
    display: flex;
    flex-direction: column;
    flex: 1;
}

.ns-item-name {
    font-family: var(--ns-font-heading);
    font-size: 1.15rem;
    font-weight: 800;
    color: var(--ns-text-dark);
    line-height: 1.35;
    margin: 0 0 10px;
    letter-spacing: -0.01em;
    min-height: 2.8rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    transition: color 0.2s;
}

.ns-product-card:hover .ns-item-name {
    color: #059669;
}

/* Feature tags */
.ns-feature-row {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 14px;
    flex-wrap: wrap;
}

.ns-feature-tag {
    font-size: 0.72rem;
    font-weight: 700;
    color: #047857;
    background: rgba(16, 185, 129, 0.08);
    border: 1px solid rgba(16, 185, 129, 0.22);
    padding: 3px 9px;
    border-radius: 8px;
}

[data-theme="dark"] .ns-feature-tag {
    color: #6ee7b7;
    background: rgba(16, 185, 129, 0.16);
}

.ns-item-summary {
    font-size: 0.86rem;
    color: var(--ns-text-muted);
    line-height: 1.6;
    margin: 0 0 16px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    flex: 1;
}

/* Luxury Reference Price Box */
.ns-price-display-box {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.08) 0%, rgba(245, 158, 11, 0.06) 100%);
    border: 1.5px solid rgba(16, 185, 129, 0.22);
    border-radius: var(--ns-radius-md);
    padding: 12px 14px;
    margin-bottom: 18px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    transition: all 0.25s ease;
}

[data-theme="dark"] .ns-price-display-box {
    background: rgba(16, 185, 129, 0.1);
    border-color: rgba(16, 185, 129, 0.3);
}

.ns-price-row-main {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.ns-price-meta-tag {
    font-size: 0.72rem;
    font-weight: 800;
    text-transform: uppercase;
    color: #059669;
    letter-spacing: 0.5px;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 4px;
}

.ns-price-val {
    font-size: 1.15rem;
    font-weight: 900;
    color: #047857;
    letter-spacing: -0.2px;
    white-space: nowrap !important; /* CRITICAL: Never break 'Liên hệ' */
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: rgba(16, 185, 129, 0.12);
    padding: 3px 12px;
    border-radius: var(--ns-radius-full);
    border: 1px solid rgba(16, 185, 129, 0.3);
}

[data-theme="dark"] .ns-price-val {
    color: #34d399;
    background: rgba(16, 185, 129, 0.2);
}

.ns-unit-row {
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--ns-text-muted);
    border-top: 1px dashed rgba(16, 185, 129, 0.2);
    padding-top: 6px;
    display: flex;
    align-items: center;
    gap: 6px;
    line-height: 1.35;
    word-break: break-word;
}

.ns-unit-val {
    font-weight: 700;
    color: var(--ns-text-dark);
}

/* Card Button Group */
.ns-card-btns-row {
    display: flex;
    gap: 10px;
    margin-top: auto;
}

.ns-btn-see-detail {
    flex: 1;
    padding: 12px 14px;
    border-radius: var(--ns-radius-md);
    border: 1.5px solid rgba(5, 150, 105, 0.45);
    background: transparent;
    color: #059669;
    font-weight: 800;
    font-size: 0.86rem;
    cursor: pointer;
    text-align: center;
    transition: all 0.25s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

.ns-btn-see-detail:hover {
    background: rgba(16, 185, 129, 0.12);
    border-color: #059669;
    transform: translateY(-2px);
}

.ns-btn-quick-inquire {
    flex: 1.15;
    padding: 12px 14px;
    border-radius: var(--ns-radius-md);
    border: none;
    background: linear-gradient(135deg, #059669 0%, #10b981 100%);
    color: #ffffff;
    font-weight: 800;
    font-size: 0.86rem;
    cursor: pointer;
    text-align: center;
    transition: all 0.25s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    box-shadow: 0 4px 16px rgba(16, 185, 129, 0.35);
}

.ns-btn-quick-inquire:hover {
    filter: brightness(1.08);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.45);
}

/* Empty State */
.ns-empty-state-box {
    text-align: center;
    padding: 70px 20px;
    background: var(--ns-surface);
    border-radius: var(--ns-radius-xl);
    border: 1.5px dashed var(--ns-border-subtle);
    margin-bottom: 60px;
}

.ns-empty-icon {
    font-size: 3.5rem;
    display: block;
    margin-bottom: 14px;
}

.ns-empty-title {
    font-size: 1.3rem;
    font-weight: 800;
    color: var(--ns-text-dark);
    margin: 0 0 8px;
}

.ns-empty-desc {
    font-size: 0.92rem;
    color: var(--ns-text-muted);
    margin: 0 0 22px;
}

/* ══════════════════════════════════════════════════════════════════════════
   4.2. PROPOSAL 1: CURATED GIFT COMBOS
   ══════════════════════════════════════════════════════════════════════════ */

.ns-combos-container {
    max-width: 1220px;
    margin: 0 auto 80px;
    padding: 0 20px;
}

.ns-section-header {
    text-align: center;
    max-width: 780px;
    margin: 0 auto 38px;
    padding: 0 20px;
}

.ns-section-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 16px;
    border-radius: var(--ns-radius-full);
    background: rgba(245, 158, 11, 0.12);
    border: 1px solid rgba(245, 158, 11, 0.35);
    color: #d97706;
    font-size: 0.78rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    margin-bottom: 10px;
}

[data-theme="dark"] .ns-section-tag {
    color: #fcd34d;
    background: rgba(245, 158, 11, 0.2);
}

.ns-section-title {
    font-family: var(--ns-font-heading);
    font-size: clamp(1.6rem, 3.2vw, 2.3rem);
    font-weight: 900;
    color: var(--ns-text-dark);
    margin: 0 0 10px;
    line-height: 1.25;
}

.ns-section-desc {
    font-size: 0.95rem;
    color: var(--ns-text-muted);
    line-height: 1.65;
    margin: 0;
}

.ns-combos-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 28px;
}

.ns-combo-card {
    background: var(--ns-surface);
    border-radius: var(--ns-radius-lg);
    border: 1.5px solid rgba(245, 158, 11, 0.35);
    box-shadow: var(--ns-shadow-sm);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    position: relative;
    transition: all 0.3s ease;
    cursor: pointer;
}

.ns-combo-card:hover {
    transform: translateY(-6px);
    box-shadow: var(--ns-shadow-gold);
    border-color: #f59e0b;
}

.ns-combo-media {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.ns-combo-media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s ease;
}

.ns-combo-card:hover .ns-combo-media img {
    transform: scale(1.06);
}

.ns-combo-badge-top {
    position: absolute;
    top: 14px;
    left: 14px;
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: #ffffff;
    padding: 5px 12px;
    border-radius: var(--ns-radius-full);
    font-size: 0.72rem;
    font-weight: 800;
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
}

.ns-combo-body {
    padding: 24px;
    display: flex;
    flex-direction: column;
    flex: 1;
}

.ns-combo-tag-lbl {
    font-size: 0.75rem;
    font-weight: 800;
    color: #d97706;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
    display: block;
}

.ns-combo-name {
    font-family: var(--ns-font-heading);
    font-size: 1.18rem;
    font-weight: 800;
    color: var(--ns-text-dark);
    margin: 0 0 8px;
    line-height: 1.35;
}

.ns-combo-desc {
    font-size: 0.85rem;
    color: var(--ns-text-muted);
    line-height: 1.55;
    margin: 0 0 16px;
}

.ns-combo-items-box {
    background: var(--ns-cream);
    border-radius: var(--ns-radius-md);
    padding: 14px;
    margin-bottom: 18px;
    border: 1px dashed rgba(16, 185, 129, 0.3);
}

.ns-combo-items-box-title {
    font-size: 0.75rem;
    font-weight: 800;
    text-transform: uppercase;
    color: #059669;
    letter-spacing: 0.5px;
    margin: 0 0 8px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.ns-combo-items-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.ns-combo-items-list li {
    font-size: 0.8rem;
    color: var(--ns-text-dark);
    line-height: 1.45;
    display: flex;
    align-items: flex-start;
    gap: 6px;
}

.ns-combo-items-list li::before {
    content: "•";
    color: #10b981;
    font-weight: 900;
}

/* ══════════════════════════════════════════════════════════════════════════
   4.3. PROPOSAL 2: SEASONAL HARVEST CALENDAR
   ══════════════════════════════════════════════════════════════════════════ */

.ns-calendar-container {
    max-width: 1220px;
    margin: 0 auto 80px;
    padding: 0 20px;
}

.ns-tag-calendar {
    background: rgba(16, 185, 129, 0.12);
    color: #059669;
    border-color: rgba(16, 185, 129, 0.35);
}

.ns-calendar-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
}

.ns-season-card {
    background: var(--ns-surface);
    border-radius: var(--ns-radius-lg);
    border: 1.5px solid var(--ns-border-subtle);
    padding: 24px 20px;
    display: flex;
    flex-direction: column;
    transition: all 0.3s ease;
    box-shadow: var(--ns-shadow-xs);
    position: relative;
    overflow: hidden;
}

.ns-season-card:hover {
    border-color: #10b981;
    transform: translateY(-5px);
    box-shadow: var(--ns-shadow-emerald);
}

.ns-season-header-box {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 14px;
}

.ns-season-icon {
    width: 48px;
    height: 48px;
    border-radius: var(--ns-radius-md);
    background: rgba(16, 185, 129, 0.12);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
}

.ns-season-lbl {
    font-size: 0.76rem;
    font-weight: 800;
    color: #059669;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: block;
}

.ns-season-title {
    font-family: var(--ns-font-heading);
    font-size: 1.05rem;
    font-weight: 800;
    color: var(--ns-text-dark);
    margin: 2px 0 0;
    line-height: 1.3;
}

.ns-season-desc {
    font-size: 0.83rem;
    color: var(--ns-text-muted);
    line-height: 1.55;
    margin: 0 0 16px;
    flex: 1;
}

.ns-season-bullets {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 6px;
    border-top: 1px dashed var(--ns-border-subtle);
    padding-top: 12px;
}

.ns-season-bullets li {
    font-size: 0.78rem;
    color: var(--ns-text-dark);
    line-height: 1.45;
    display: flex;
    align-items: flex-start;
    gap: 6px;
}

.ns-season-bullets li::before {
    content: "🌾";
    font-size: 0.75rem;
}

/* ══════════════════════════════════════════════════════════════════════════
   4.4. PROPOSAL 3: FARM-TO-TABLE EXPERIENCE CTA
   ══════════════════════════════════════════════════════════════════════════ */

.ns-experience-cta-wrap {
    max-width: 1220px;
    margin: 0 auto 80px;
    padding: 0 20px;
}

.ns-experience-cta-box {
    background: radial-gradient(circle at 90% 10%, rgba(245, 158, 11, 0.25) 0%, transparent 50%),
                linear-gradient(135deg, #022c22 0%, #064e3b 100%);
    border-radius: var(--ns-radius-xl);
    padding: 42px 48px;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 36px;
    border: 1.5px solid rgba(245, 158, 11, 0.35);
    box-shadow: 0 20px 50px rgba(2, 44, 34, 0.3);
    position: relative;
    overflow: hidden;
}

.ns-experience-cta-text {
    flex: 1;
}

.ns-experience-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 16px;
    border-radius: var(--ns-radius-full);
    background: rgba(245, 158, 11, 0.2);
    border: 1px solid rgba(245, 158, 11, 0.4);
    font-size: 0.76rem;
    font-weight: 800;
    text-transform: uppercase;
    color: #fef08a;
    margin-bottom: 12px;
}

.ns-experience-cta-title {
    font-family: var(--ns-font-heading);
    font-size: clamp(1.4rem, 2.8vw, 2.1rem);
    font-weight: 900;
    margin: 0 0 10px;
    line-height: 1.25;
}

.ns-experience-cta-desc {
    font-size: 0.95rem;
    color: rgba(255, 255, 255, 0.88);
    line-height: 1.6;
    margin: 0;
    max-width: 650px;
}

.ns-experience-cta-actions {
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    gap: 12px;
    min-width: 220px;
}

.ns-btn-experience-primary {
    padding: 15px 24px;
    border-radius: var(--ns-radius-md);
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: #ffffff;
    font-weight: 800;
    font-size: 0.95rem;
    text-decoration: none;
    text-align: center;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4);
    transition: all 0.25s ease;
}

.ns-btn-experience-primary:hover {
    filter: brightness(1.08);
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(245, 158, 11, 0.5);
}

.ns-experience-hotline-link {
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    color: #a7f3d0;
    font-weight: 700;
    font-size: 0.88rem;
    padding: 8px;
    transition: color 0.2s;
}

.ns-experience-hotline-link:hover {
    color: #ffffff;
}

/* ══════════════════════════════════════════════════════════════════════════
   5. EDITORIAL SHOWCASE MODAL (2-COLUMN SPLIT DESKTOP)
   ══════════════════════════════════════════════════════════════════════════ */

.ns-modal-backdrop {
    position: fixed;
    inset: 0;
    z-index: 99999;
    background: rgba(2, 44, 34, 0.82);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 18px;
    animation: nsModalFade 0.25s ease-out;
}

@keyframes nsModalFade {
    from { opacity: 0; }
    to   { opacity: 1; }
}

.ns-modal-sheet {
    background: var(--ns-surface);
    border-radius: var(--ns-radius-xl);
    max-width: 1040px;
    width: 100%;
    max-height: 90vh;
    overflow: hidden;
    position: relative;
    box-shadow: 0 35px 80px rgba(0, 0, 0, 0.45);
    border: 1.5px solid rgba(245, 158, 11, 0.45);
    animation: nsModalSlide 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    display: flex;
    flex-direction: column;
}

@keyframes nsModalSlide {
    from { transform: translateY(30px) scale(0.96); opacity: 0; }
    to   { transform: translateY(0) scale(1); opacity: 1; }
}

.ns-close-x-btn {
    position: absolute;
    top: 16px;
    right: 16px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 1px solid rgba(255, 255, 255, 0.25);
    background: rgba(6, 40, 30, 0.85);
    backdrop-filter: blur(8px);
    color: #ffffff;
    font-size: 1.2rem;
    font-weight: 700;
    cursor: pointer;
    z-index: 30;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.25s;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.ns-close-x-btn:hover {
    background: #ef4444;
    transform: rotate(90deg) scale(1.05);
}

.ns-modal-split-grid {
    display: grid;
    grid-template-columns: 440px 1fr;
    height: 100%;
    max-height: 90vh;
    overflow: hidden;
}

/* Modal Left Column: Visual Showcase */
.ns-modal-col-left {
    background: linear-gradient(180deg, #180e07 0%, #0d0703 100%);
    color: #ffffff;
    display: flex;
    flex-direction: column;
    position: relative;
    border-right: 1.5px solid rgba(217, 119, 6, 0.35);
    overflow-y: auto;
}

.ns-modal-media-wrapper {
    position: relative;
    height: 480px;
    width: 100%;
    background: #180e07 radial-gradient(circle, #2a150a 0%, #100803 100%);
    overflow: hidden;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 12px;
    cursor: pointer;
}

.ns-modal-media-wrapper img {
    max-width: 100%;
    max-height: 100%;
    width: auto;
    height: auto;
    object-fit: contain;
    border-radius: var(--ns-radius-sm);
    box-shadow: 0 10px 28px rgba(0, 0, 0, 0.55);
    transition: transform 0.4s ease;
}

.ns-modal-col-left:hover .ns-modal-media-wrapper img {
    transform: scale(1.02);
}

.ns-modal-badge-float {
    position: absolute;
    top: 16px;
    left: 16px;
    padding: 6px 14px;
    border-radius: var(--ns-radius-full);
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: #ffffff;
    font-size: 0.76rem;
    font-weight: 800;
    text-transform: uppercase;
    box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4);
    letter-spacing: 0.5px;
    z-index: 2;
}

.ns-modal-zoom-hint {
    position: absolute;
    bottom: 12px;
    right: 14px;
    background: rgba(6, 40, 30, 0.88);
    backdrop-filter: blur(8px);
    padding: 5px 12px;
    border-radius: var(--ns-radius-full);
    color: #fef08a;
    font-size: 0.74rem;
    font-weight: 800;
    border: 1px solid rgba(254, 240, 138, 0.3);
}

.ns-modal-gallery-strip {
    padding: 12px 18px 0;
    gap: 8px;
    align-items: center;
    display: flex;
}

.ns-modal-left-details {
    padding: 22px 24px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    flex: 1;
}

.ns-modal-origin-box {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.88rem;
    font-weight: 700;
    color: #fef08a;
    background: rgba(255, 255, 255, 0.08);
    padding: 8px 14px;
    border-radius: var(--ns-radius-md);
    border: 1px solid rgba(255, 255, 255, 0.12);
}

.ns-modal-tip-box {
    font-size: 0.82rem;
    line-height: 1.55;
    color: #a7f3d0;
    background: rgba(16, 185, 129, 0.15);
    padding: 10px 14px;
    border-radius: var(--ns-radius-md);
    border: 1px solid rgba(16, 185, 129, 0.3);
}

.ns-modal-guarantee-box {
    font-size: 0.78rem;
    color: rgba(255, 255, 255, 0.78);
    line-height: 1.5;
    background: rgba(0, 0, 0, 0.28);
    padding: 12px 14px;
    border-radius: var(--ns-radius-md);
    border: 1px solid rgba(255, 255, 255, 0.08);
    display: flex;
    gap: 8px;
}

.ns-modal-direct-actions {
    margin-top: auto;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.ns-btn-direct-phone {
    width: 100%;
    padding: 12px;
    border-radius: var(--ns-radius-md);
    border: 1.5px solid #10b981;
    background: rgba(16, 185, 129, 0.15);
    color: #6ee7b7;
    font-weight: 800;
    font-size: 0.9rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.25s;
}

.ns-btn-direct-phone:hover {
    background: #10b981;
    color: #ffffff;
    transform: translateY(-1px);
}

.ns-btn-direct-zalo {
    width: 100%;
    padding: 12px;
    border-radius: var(--ns-radius-md);
    border: none;
    background: linear-gradient(135deg, #0284c7 0%, #0068ff 100%);
    color: #ffffff;
    font-weight: 800;
    font-size: 0.9rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    box-shadow: 0 4px 16px rgba(0, 104, 255, 0.35);
    transition: all 0.25s;
}

.ns-btn-direct-zalo:hover {
    filter: brightness(1.1);
    transform: translateY(-1px);
}

/* Modal Right Column: Editorial Details & Form */
.ns-modal-col-right {
    padding: 28px 32px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 20px;
    background: var(--ns-surface);
}

.ns-modal-heading-group {
    border-bottom: 1px solid var(--ns-border-subtle);
    padding-bottom: 16px;
}

.ns-modal-cat-tag {
    font-size: 0.75rem;
    font-weight: 800;
    color: #059669;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    margin-bottom: 4px;
}

.ns-modal-product-title {
    font-family: var(--ns-font-heading);
    font-size: clamp(1.4rem, 2.5vw, 1.85rem);
    font-weight: 900;
    color: var(--ns-text-dark);
    margin: 0;
    line-height: 1.25;
}

/* Price Highlight Card */
.ns-price-highlight-banner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 20px;
    border-radius: var(--ns-radius-lg);
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(245, 158, 11, 0.1) 100%);
    border: 1.5px solid rgba(16, 185, 129, 0.3);
}

.ns-banner-price-lbl {
    font-size: 0.72rem;
    font-weight: 800;
    color: #059669;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: block;
}

.ns-banner-price-val {
    font-size: 1.45rem;
    color: #047857;
    font-weight: 900;
    letter-spacing: -0.3px;
    white-space: nowrap !important;
}

.ns-banner-price-sub {
    font-size: 0.76rem;
    color: var(--ns-text-muted);
    display: block;
    margin-top: 2px;
}

.ns-unit-meta-col {
    text-align: right;
    max-width: 50%;
}

.ns-banner-unit-lbl {
    font-size: 0.72rem;
    font-weight: 800;
    color: var(--ns-text-muted);
    text-transform: uppercase;
    display: block;
}

.ns-banner-unit-val {
    font-size: 0.92rem;
    font-weight: 800;
    color: var(--ns-text-dark);
    display: block;
    word-break: break-word;
}

.ns-modal-story-title {
    font-size: 0.96rem;
    font-weight: 800;
    color: var(--ns-text-dark);
    margin: 0 0 8px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-family: var(--ns-font-heading);
}

.ns-modal-story-p {
    font-size: 0.9rem;
    line-height: 1.68;
    color: var(--ns-text-muted);
    margin: 0 0 16px;
    background: var(--ns-cream);
    padding: 14px 16px;
    border-radius: var(--ns-radius-md);
    border-left: 3px solid #f59e0b;
}

.ns-modal-points {
    list-style: none;
    padding: 0;
    margin: 0 0 20px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.ns-modal-points li {
    font-size: 0.88rem;
    color: var(--ns-text-dark);
    display: flex;
    align-items: flex-start;
    gap: 10px;
    line-height: 1.5;
}

.ns-modal-points li::before {
    content: "✓";
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: linear-gradient(135deg, #059669 0%, #10b981 100%);
    color: #ffffff;
    font-size: 0.7rem;
    font-weight: 900;
    flex-shrink: 0;
    margin-top: 2px;
    box-shadow: 0 2px 6px rgba(16, 185, 129, 0.4);
}

/* Fast Order Card */
.ns-order-glass-box {
    background: rgba(16, 185, 129, 0.05);
    border: 1.5px solid rgba(16, 185, 129, 0.25);
    border-radius: var(--ns-radius-lg);
    padding: 22px;
}

.ns-order-form-title {
    font-size: 0.95rem;
    font-weight: 900;
    color: #059669;
    margin: 0 0 4px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-family: var(--ns-font-heading);
}

.ns-order-form-sub {
    font-size: 0.82rem;
    color: var(--ns-text-muted);
    margin: 0 0 14px;
    line-height: 1.5;
}

.ns-form-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-bottom: 10px;
}

.ns-form-label {
    font-size: 0.76rem;
    font-weight: 800;
    color: var(--ns-text-dark);
    display: block;
    margin-bottom: 5px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.ns-req {
    color: #ef4444;
}

.ns-form-input {
    width: 100%;
    padding: 10px 14px;
    border-radius: var(--ns-radius-sm);
    border: 1.5px solid var(--ns-border-subtle);
    background: var(--ns-surface);
    color: var(--ns-text-dark);
    font-size: 0.88rem;
    outline: none;
    transition: all 0.2s;
    font-family: var(--ns-font-body);
}

.ns-form-input:focus {
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
}

.ns-btn-submit-order {
    width: 100%;
    padding: 13px;
    border-radius: var(--ns-radius-md);
    border: none;
    background: linear-gradient(135deg, #059669 0%, #10b981 100%);
    color: #ffffff;
    font-weight: 800;
    font-size: 0.92rem;
    cursor: pointer;
    box-shadow: 0 4px 16px rgba(16, 185, 129, 0.35);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.25s;
}

.ns-btn-submit-order:hover {
    filter: brightness(1.08);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.45);
}

/* ══════════════════════════════════════════════════════════════════════════
   6. ANTIQUE ROYAL BANQUET PARCHMENT MENU BOARD
   ══════════════════════════════════════════════════════════════════════════ */

.ns-mamco-royal-parchment {
    background: #fdfaf3 linear-gradient(180deg, #fefcf8 0%, #f7f0e1 100%);
    border: 2px solid #b45309;
    border-radius: var(--ns-radius-lg);
    padding: 20px 22px;
    box-shadow: 0 8px 24px rgba(180, 83, 9, 0.08), inset 0 0 20px rgba(217, 119, 6, 0.04);
    position: relative;
    margin-bottom: 14px;
}

[data-theme="dark"] .ns-mamco-royal-parchment {
    background: #18110b linear-gradient(180deg, #1e150f 0%, #140d07 100%);
    border-color: #d97706;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.5), inset 0 0 20px rgba(217, 119, 6, 0.1);
}

.ns-parchment-inner-border {
    border: 1px dashed rgba(180, 83, 9, 0.45);
    border-radius: var(--ns-radius-md);
    padding: 16px 18px;
}

[data-theme="dark"] .ns-parchment-inner-border {
    border-color: rgba(245, 158, 11, 0.4);
}

.ns-parchment-header {
    text-align: center;
    margin-bottom: 14px;
    position: relative;
}

.ns-parchment-badge {
    display: inline-block;
    font-size: 0.68rem;
    font-weight: 800;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: #9a3412;
    margin-bottom: 3px;
}

[data-theme="dark"] .ns-parchment-badge {
    color: #fcd34d;
}

.ns-parchment-title {
    font-family: 'Cinzel', 'Playfair Display', Georgia, serif;
    font-size: 1.5rem;
    font-weight: 900;
    color: #7f1d1d;
    letter-spacing: 1.2px;
    margin: 0 0 3px;
    text-transform: uppercase;
}

[data-theme="dark"] .ns-parchment-title {
    color: #fca5a5;
    text-shadow: 0 2px 8px rgba(239, 68, 68, 0.2);
}

.ns-parchment-subtitle {
    font-style: italic;
    font-size: 0.88rem;
    color: #9a3412;
    margin: 0 0 8px;
    font-weight: 600;
}

[data-theme="dark"] .ns-parchment-subtitle {
    color: #fde68a;
}

.ns-parchment-divider {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    color: #d97706;
    font-size: 0.72rem;
    margin: 6px auto 12px;
    max-width: 220px;
}

.ns-parchment-divider hr {
    flex: 1;
    border: none;
    height: 1px;
    background: linear-gradient(90deg, transparent, #d97706, transparent);
    margin: 0;
}

.ns-parchment-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-bottom: 12px;
}

.ns-parchment-sec-box {
    background: rgba(255, 255, 255, 0.8);
    border: 1px solid rgba(180, 83, 9, 0.25);
    border-radius: var(--ns-radius-sm);
    padding: 10px 12px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
}

[data-theme="dark"] .ns-parchment-sec-box {
    background: rgba(255, 255, 255, 0.04);
    border-color: rgba(245, 158, 11, 0.25);
}

.ns-parchment-sec-head {
    font-size: 0.78rem;
    font-weight: 900;
    color: #881337;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 6px;
    padding-bottom: 3px;
    border-bottom: 1px dashed rgba(180, 83, 9, 0.2);
    display: flex;
    align-items: center;
    gap: 6px;
}

[data-theme="dark"] .ns-parchment-sec-head {
    color: #fda4af;
    border-bottom-color: rgba(245, 158, 11, 0.2);
}

.ns-parchment-sec-items {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.ns-parchment-sec-items li {
    font-size: 0.8rem;
    color: var(--ns-text-dark);
    display: flex;
    align-items: baseline;
    gap: 6px;
    line-height: 1.45;
}

.ns-parchment-sec-items li::before {
    content: "✦";
    font-size: 0.62rem;
    color: #b45309;
    flex-shrink: 0;
}

[data-theme="dark"] .ns-parchment-sec-items li::before {
    color: #f59e0b;
}

.ns-parchment-footer-quote {
    text-align: center;
    margin-top: 10px;
    padding-top: 8px;
    border-top: 1px dashed rgba(180, 83, 9, 0.25);
}

.ns-parchment-footer-quote p {
    font-style: italic;
    font-size: 0.82rem;
    color: #9a3412;
    margin: 0;
    font-weight: 700;
}

[data-theme="dark"] .ns-parchment-footer-quote p {
    color: #fde68a;
}

/* ══════════════════════════════════════════════════════════════════════════
   7. LIGHTBOX MODAL FOR FULL RESOLUTION MENU IMAGES
   ══════════════════════════════════════════════════════════════════════════ */

.ns-lightbox-modal {
    position: fixed;
    inset: 0;
    z-index: 999999;
    background: rgba(0, 0, 0, 0.94);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    animation: nsModalFade 0.2s ease-out;
}

.ns-lightbox-content {
    position: relative;
    max-width: 94vw;
    max-height: 94vh;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.ns-lightbox-content img {
    max-width: 100%;
    max-height: 85vh;
    object-fit: contain;
    border-radius: var(--ns-radius-lg);
    border: 2px solid rgba(245, 158, 11, 0.5);
    box-shadow: 0 0 60px rgba(0, 0, 0, 0.85);
}

.ns-lightbox-caption {
    margin-top: 14px;
    color: #fef08a;
    font-size: 0.95rem;
    font-weight: 700;
    text-align: center;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8);
}

/* ══════════════════════════════════════════════════════════════════════════
   8. RESPONSIVE BREAKPOINTS (MOBILE, TABLET, DESKTOP)
   ══════════════════════════════════════════════════════════════════════════ */

@media (max-width: 1040px) {
    .ns-mamco-card {
        grid-template-columns: 1fr;
        padding: 28px;
        gap: 32px;
    }
    .ns-cards-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 22px;
    }
    .ns-combos-grid {
        grid-template-columns: 1fr;
        gap: 24px;
    }
    .ns-calendar-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 18px;
    }
}

@media (max-width: 860px) {
    .ns-modal-sheet {
        max-width: 580px;
        max-height: 94vh;
    }
    .ns-modal-split-grid {
        grid-template-columns: 1fr;
        overflow-y: auto;
    }
    .ns-modal-media-wrapper {
        height: 380px !important;
    }
    .ns-trust-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .ns-experience-cta-box {
        flex-direction: column;
        padding: 32px 24px;
        text-align: center;
    }
    .ns-experience-cta-desc {
        max-width: 100%;
    }
    .ns-experience-cta-actions {
        width: 100%;
    }
    .ns-hero-stats-glass {
        padding: 14px 20px;
        gap: 14px;
    }
    .ns-stat-sep {
        display: none;
    }
    .ns-stat-box {
        flex: 1 1 40%;
    }
}

@media (max-width: 640px) {
    .ns-cards-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    .ns-mamco-categories-grid {
        grid-template-columns: 1fr;
    }
    .ns-parchment-grid {
        grid-template-columns: 1fr;
    }
    .ns-calendar-grid {
        grid-template-columns: 1fr;
    }
    .ns-form-grid-2 {
        grid-template-columns: 1fr;
    }
    .ns-hero-cinematic {
        padding: 55px 16px 50px;
    }
    .ns-hero-slogan-pill {
        flex-direction: column;
        gap: 4px;
        border-radius: var(--ns-radius-md);
        padding: 8px 16px;
    }
    .ns-slogan-dot {
        display: none;
    }
    .ns-hero-anchors-row {
        gap: 6px;
    }
    .ns-hero-anchor-chip {
        font-size: 0.76rem;
        padding: 6px 12px;
    }
    .ns-mamco-card {
        padding: 20px 16px;
    }
    .ns-mamco-img-frame {
        height: 340px;
    }
    .ns-trust-grid {
        grid-template-columns: 1fr;
    }
    .ns-modal-col-right {
        padding: 20px 16px;
    }
    .ns-modal-left-details {
        padding: 16px;
    }
    .ns-search-box {
        width: 100%;
        min-width: 0;
    }
}
</style>
