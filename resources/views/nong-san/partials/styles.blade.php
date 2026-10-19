<style>
/* ── NÔNG SẢN ĐÔNG ANH - LUXURY HERITAGE DESIGN SYSTEM ── */
:root {
    --font-heading: 'Inter', 'Be Vietnam Pro', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    --font-body: 'Be Vietnam Pro', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    --font-sans: 'Be Vietnam Pro', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    --ns-primary: #059669;
    --ns-primary-glow: rgba(16, 185, 129, 0.25);
    --ns-dark: #06281e;
    --ns-dark-surface: #0a3427;
    --ns-gold: #f59e0b;
    --ns-gold-glow: rgba(245, 158, 11, 0.28);
    --ns-gold-border: rgba(245, 158, 11, 0.35);
    --ns-cream: #fcfbf7;
    --ns-surface: #ffffff;
    --ns-text-dark: #0f172a;
    --ns-text-muted: #64748b;
    --ns-border-light: rgba(226, 232, 240, 0.85);
    --ns-card-radius: 24px;
    --ns-shadow-luxury: 0 12px 36px -8px rgba(6, 40, 30, 0.08), 0 4px 12px -2px rgba(6, 40, 30, 0.04);
    --ns-shadow-hover: 0 24px 50px -10px rgba(6, 40, 30, 0.16), 0 0 25px rgba(16, 185, 129, 0.2);
}

[data-theme="dark"] {
    --ns-cream: #051610;
    --ns-surface: #0a251c;
    --ns-text-dark: #f8fafc;
    --ns-text-muted: #94a3b8;
    --ns-border-light: rgba(16, 185, 129, 0.15);
    --ns-shadow-luxury: 0 12px 36px -8px rgba(0, 0, 0, 0.5);
    --ns-shadow-hover: 0 24px 50px -10px rgba(0, 0, 0, 0.7), 0 0 30px rgba(16, 185, 129, 0.3);
}

/* ── CINEMATIC LUXURY HERO ── */
.ns-hero-cinematic {
    position: relative;
    background: radial-gradient(circle at 15% 25%, rgba(16, 185, 129, 0.22) 0%, transparent 45%),
                radial-gradient(circle at 85% 75%, rgba(245, 158, 11, 0.18) 0%, transparent 40%),
                linear-gradient(175deg, #022c22 0%, #064e3b 55%, #052e24 100%);
    padding: 75px 20px 65px;
    overflow: hidden;
    color: #ffffff;
    text-align: center;
    border-bottom: 1px solid rgba(245, 158, 11, 0.2);
}

.ns-hero-ambient-orb-1 {
    position: absolute;
    top: -80px;
    left: -80px;
    width: 320px;
    height: 320px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(16, 185, 129, 0.28) 0%, transparent 70%);
    filter: blur(45px);
    pointer-events: none;
    animation: nsPulse 8s infinite alternate ease-in-out;
}

.ns-hero-ambient-orb-2 {
    position: absolute;
    bottom: -60px;
    right: -60px;
    width: 300px;
    height: 300px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(245, 158, 11, 0.22) 0%, transparent 70%);
    filter: blur(40px);
    pointer-events: none;
    animation: nsPulse 6s infinite alternate-reverse ease-in-out;
}

@keyframes nsPulse {
    0% { transform: scale(1) translate(0, 0); opacity: 0.6; }
    100% { transform: scale(1.15) translate(20px, -15px); opacity: 0.95; }
}

.ns-hero-slogan-pill {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 8px 22px;
    border-radius: 50px;
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    border: 1px solid rgba(245, 158, 11, 0.4);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
    margin-bottom: 22px;
}

.ns-slogan-crown {
    color: #fcd34d;
    font-size: 0.95rem;
}

.ns-slogan-text {
    font-size: 0.82rem;
    font-weight: 800;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: #fef08a;
}

.ns-hero-main-title {
    font-family: var(--font-heading, 'Be Vietnam Pro', sans-serif);
    font-size: clamp(2.2rem, 5vw, 3.8rem);
    font-weight: 900;
    line-height: 1.15;
    margin: 0 0 18px;
    letter-spacing: -0.3px;
}

.ns-title-gold {
    background: linear-gradient(135deg, #fef08a 0%, #f59e0b 50%, #d97706 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    text-shadow: 0 0 35px rgba(245, 158, 11, 0.35);
}

.ns-hero-subtitle {
    max-width: 820px;
    margin: 0 auto 36px;
    font-size: 1.05rem;
    line-height: 1.7;
    color: rgba(255, 255, 255, 0.85);
    font-weight: 400;
}

/* Hero Stats Glass Row */
.ns-hero-stats-glass {
    display: inline-flex;
    align-items: center;
    gap: 20px;
    padding: 14px 32px;
    border-radius: 60px;
    background: rgba(255, 255, 255, 0.07);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.15);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    flex-wrap: wrap;
    justify-content: center;
}

.ns-stat-box {
    display: flex;
    align-items: center;
    gap: 10px;
    text-align: left;
}

.ns-stat-icon-wrap {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: rgba(16, 185, 129, 0.25);
    border: 1px solid rgba(16, 185, 129, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
}

.ns-stat-num {
    font-size: 1.15rem;
    font-weight: 900;
    color: #ffffff;
    line-height: 1.1;
    display: block;
}

.ns-stat-lbl {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.7);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}

.ns-stat-sep {
    width: 1px;
    height: 28px;
    background: rgba(255, 255, 255, 0.15);
}

/* ── SPOTLIGHT HERITAGE BANNER ── */
.ns-spotlight-section {
    max-width: 1220px;
    margin: -35px auto 45px;
    padding: 0 20px;
    position: relative;
    z-index: 10;
}

/* ── MÂM CỖ CỔ LOA XƯA - HERITAGE GRAND BOX ── */
.ns-mamco-card {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.98) 0%, rgba(254, 252, 246, 0.98) 100%);
    border-radius: 30px;
    border: 2px solid rgba(245, 158, 11, 0.45);
    box-shadow: 0 24px 60px rgba(6, 40, 30, 0.14), 0 0 30px rgba(245, 158, 11, 0.12);
    padding: 34px 38px;
    display: grid;
    grid-template-columns: 1.25fr 1fr;
    gap: 36px;
    align-items: center;
    position: relative;
    overflow: hidden;
}

[data-theme="dark"] .ns-mamco-card {
    background: linear-gradient(135deg, rgba(10, 42, 32, 0.96) 0%, rgba(5, 26, 20, 0.98) 100%);
    border-color: rgba(245, 158, 11, 0.45);
}

@media (max-width: 1024px) {
    .ns-mamco-card {
        grid-template-columns: 1fr;
        padding: 24px;
        gap: 28px;
    }
}

.ns-spotlight-title {
    font-family: 'Inter', var(--font-heading, sans-serif);
    font-size: 2.15rem;
    font-weight: 800;
    line-height: 1.25;
    letter-spacing: 0.01em;
    color: var(--ns-dark);
    margin: 0 0 6px;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    text-rendering: optimizeLegibility;
}

[data-theme="dark"] .ns-spotlight-title {
    color: #f8fafc;
}

.ns-mamco-slogan {
    font-style: italic;
    color: #b45309;
    font-size: 0.88rem;
    font-weight: 700;
    margin: -4px 0 14px;
    display: block;
}

[data-theme="dark"] .ns-mamco-slogan {
    color: #fcd34d;
}

/* 4-Bento Categories Grid for Mâm Cỗ */
.ns-mamco-categories-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 22px;
}

@media (max-width: 580px) {
    .ns-mamco-categories-grid {
        grid-template-columns: 1fr;
    }
}

.ns-mamco-cat-box {
    background: var(--ns-cream);
    border: 1px solid rgba(16, 185, 129, 0.2);
    border-radius: 14px;
    padding: 12px 14px;
    transition: all 0.2s ease;
}

[data-theme="dark"] .ns-mamco-cat-box {
    background: rgba(16, 185, 129, 0.08);
    border-color: rgba(16, 185, 129, 0.25);
}

.ns-mamco-cat-box:hover {
    border-color: #f59e0b;
    background: rgba(245, 158, 11, 0.06);
    transform: translateY(-2px);
}

.ns-mamco-cat-head {
    font-size: 0.76rem;
    font-weight: 900;
    text-transform: uppercase;
    color: #047857;
    letter-spacing: 0.5px;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 6px;
}

[data-theme="dark"] .ns-mamco-cat-head {
    color: #6ee7b7;
}

.ns-mamco-cat-items {
    font-size: 0.8rem;
    color: var(--ns-text-dark);
    line-height: 1.5;
    margin: 0;
    font-weight: 500;
}

/* Interactive Image Switcher on Right */
.ns-mamco-media-card {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.ns-mamco-tabs-bar {
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(16, 185, 129, 0.1);
    padding: 5px;
    border-radius: 50px;
    border: 1px solid rgba(16, 185, 129, 0.25);
}

.ns-mamco-tab-btn {
    flex: 1;
    padding: 8px 14px;
    border-radius: 50px;
    border: none;
    background: transparent;
    color: var(--ns-text-muted);
    font-weight: 800;
    font-size: 0.8rem;
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
    border-radius: 20px;
    overflow: hidden;
    background: #180e07 radial-gradient(circle, #2a150a 0%, #100803 100%);
    border: 2px solid rgba(217, 119, 6, 0.4);
    box-shadow: 0 16px 36px rgba(0, 0, 0, 0.35), 0 0 24px rgba(217, 119, 6, 0.15);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 10px;
}

.ns-mamco-img-frame img {
    max-width: 100%;
    max-height: 100%;
    width: auto;
    height: auto;
    object-fit: contain;
    border-radius: 12px;
    box-shadow: 0 10px 24px rgba(0, 0, 0, 0.5);
    transition: transform 0.4s ease;
}

.ns-mamco-img-frame:hover img {
    transform: scale(1.02);
}

.ns-mamco-img-floating-tag {
    position: absolute;
    bottom: 12px;
    left: 14px;
    right: 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: rgba(6, 40, 30, 0.88);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    padding: 7px 14px;
    border-radius: 50px;
    color: #fef08a;
    font-size: 0.74rem;
    font-weight: 800;
    border: 1px solid rgba(254, 240, 138, 0.25);
}

/* Lightbox Modal */
.ns-lightbox-modal {
    position: fixed;
    inset: 0;
    z-index: 999999;
    background: rgba(0, 0, 0, 0.92);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
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
    border-radius: 16px;
    border: 2px solid rgba(245, 158, 11, 0.5);
    box-shadow: 0 0 50px rgba(0, 0, 0, 0.8);
}

.ns-lightbox-caption {
    margin-top: 12px;
    color: #fef08a;
    font-size: 0.95rem;
    font-weight: 700;
    text-align: center;
    text-shadow: 0 2px 4px rgba(0,0,0,0.8);
}

.ns-spotlight-card:hover .ns-spotlight-right img {
    transform: scale(1.05);
}

/* ── STICKY CONTROL & FILTER BAR ── */
.ns-filter-sticky {
    position: sticky;
    top: 68px;
    z-index: 30;
    background: rgba(252, 251, 247, 0.92);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
    border-top: 1px solid var(--ns-border-light);
    border-bottom: 1px solid var(--ns-border-light);
    padding: 16px 0;
    margin-bottom: 40px;
    transition: all 0.3s;
}

[data-theme="dark"] .ns-filter-sticky {
    background: rgba(5, 22, 16, 0.92);
}

.ns-filter-container {
    max-width: 1220px;
    margin: 0 auto;
    padding: 0 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
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
    border-radius: 50px;
    border: 1.5px solid var(--ns-border-light);
    background: var(--ns-surface);
    color: var(--ns-text-muted);
    font-size: 0.86rem;
    font-weight: 800;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.25s cubic-bezier(0.2, 0.8, 0.2, 1);
    box-shadow: 0 2px 6px rgba(0,0,0,0.03);
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
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
    transform: translateY(-1px);
}

.ns-search-box {
    position: relative;
    min-width: 280px;
}

.ns-search-field {
    width: 100%;
    padding: 11px 18px 11px 42px;
    border-radius: 50px;
    border: 1.5px solid var(--ns-border-light);
    background: var(--ns-surface);
    color: var(--ns-text-dark);
    font-size: 0.88rem;
    font-weight: 600;
    outline: none;
    transition: all 0.25s ease;
    box-shadow: 0 2px 6px rgba(0,0,0,0.03);
}

.ns-search-field:focus {
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
}

/* ── BENTO PRODUCT CARDS GRID ── */
.ns-catalog-container {
    max-width: 1220px;
    margin: 0 auto 70px;
    padding: 0 20px;
}

.ns-cards-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 28px;
}

@media (max-width: 1024px) {
    .ns-cards-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 22px;
    }
}

@media (max-width: 650px) {
    .ns-cards-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
}

/* ── INDIVIDUAL PRODUCT BOX (CARD) ── */
.ns-product-card {
    background: var(--ns-surface);
    border-radius: var(--ns-card-radius);
    border: 1.5px solid var(--ns-border-light);
    box-shadow: var(--ns-shadow-luxury);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    position: relative;
    transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    cursor: pointer;
}

.ns-product-card:hover {
    transform: translateY(-7px);
    box-shadow: var(--ns-shadow-hover);
    border-color: rgba(16, 185, 129, 0.45);
}

.ns-thumb-container {
    position: relative;
    width: 100%;
    height: 235px;
    background: #0f172a;
    overflow: hidden;
}

.ns-thumb-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.7s cubic-bezier(0.2, 0.8, 0.2, 1);
}

.ns-product-card:hover .ns-thumb-img {
    transform: scale(1.08);
}

.ns-thumb-gradient {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(0,0,0,0.15) 0%, rgba(6, 40, 30, 0.85) 100%);
    pointer-events: none;
}

.ns-badge-floating {
    position: absolute;
    top: 14px;
    left: 14px;
    padding: 6px 14px;
    border-radius: 50px;
    background: rgba(6, 40, 30, 0.82);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    color: #fef08a;
    font-size: 0.75rem;
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
    text-shadow: 0 1px 4px rgba(0,0,0,0.9);
    z-index: 2;
}

.ns-card-content {
    padding: 22px 24px;
    display: flex;
    flex-direction: column;
    flex: 1;
}

.ns-item-name {
    font-family: 'Inter', var(--font-heading, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif);
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--ns-text-dark);
    line-height: 1.35;
    margin: 0 0 10px;
    letter-spacing: 0.01em;
    min-height: 2.8rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    transition: color 0.2s;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    text-rendering: optimizeLegibility;
}

.ns-product-card:hover .ns-item-name {
    color: #059669;
}

.ns-item-summary {
    font-size: 0.87rem;
    color: var(--ns-text-muted);
    line-height: 1.6;
    margin: 0 0 18px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    flex: 1;
}

/* Sensory Tasting / Feature Pill on Card */
.ns-feature-row {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 16px;
    flex-wrap: wrap;
}

.ns-feature-tag {
    font-size: 0.74rem;
    font-weight: 700;
    color: #047857;
    background: rgba(16, 185, 129, 0.08);
    border: 1px solid rgba(16, 185, 129, 0.2);
    padding: 3px 10px;
    border-radius: 8px;
}

[data-theme="dark"] .ns-feature-tag {
    color: #6ee7b7;
    background: rgba(16, 185, 129, 0.16);
}

/* ── LUXURY PRICE BOX (FIX LINE WRAP BUG) ── */
.ns-price-display-box {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.08) 0%, rgba(245, 158, 11, 0.06) 100%);
    border: 1.5px solid rgba(16, 185, 129, 0.22);
    border-radius: 16px;
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
    white-space: nowrap; /* CRITICAL: Prevent "Liên" and "hệ" from breaking */
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: rgba(16, 185, 129, 0.12);
    padding: 3px 12px;
    border-radius: 50px;
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
    gap: 5px;
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
    border-radius: 14px;
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
    border-radius: 14px;
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

/* ── EDITORIAL SHOWCASE MODAL (2-COLUMN SPLIT DESKTOP) ── */
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
    to { opacity: 1; }
}

.ns-modal-sheet {
    background: var(--ns-surface);
    border-radius: 28px;
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
    to { transform: translateY(0) scale(1); opacity: 1; }
}

.ns-close-x-btn {
    position: absolute;
    top: 16px;
    right: 16px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: none;
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
    border: 1px solid rgba(255, 255, 255, 0.25);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.ns-close-x-btn:hover {
    background: #ef4444;
    transform: rotate(90deg) scale(1.05);
}

/* Modal 2-Column Split Grid */
.ns-modal-split-grid {
    display: grid;
    grid-template-columns: 440px 1fr;
    height: 100%;
    max-height: 90vh;
    overflow: hidden;
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
}

/* Left Column: Visual Showcase & Direct Contact */
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
}

.ns-modal-media-wrapper img {
    max-width: 100%;
    max-height: 100%;
    width: auto;
    height: auto;
    object-fit: contain;
    border-radius: 12px;
    box-shadow: 0 10px 28px rgba(0, 0, 0, 0.55);
    transition: transform 0.4s ease;
}

.ns-modal-col-left:hover .ns-modal-media-wrapper img {
    transform: scale(1.02);
}

.ns-modal-media-shade {
    display: none;
}

.ns-modal-badge-float {
    position: absolute;
    top: 16px;
    left: 16px;
    padding: 6px 14px;
    border-radius: 50px;
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: #ffffff;
    font-size: 0.76rem;
    font-weight: 800;
    text-transform: uppercase;
    box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4);
    letter-spacing: 0.5px;
    z-index: 2;
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
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.12);
}

.ns-modal-tip-box {
    font-size: 0.82rem;
    line-height: 1.55;
    color: #a7f3d0;
    background: rgba(16, 185, 129, 0.15);
    padding: 10px 14px;
    border-radius: 12px;
    border: 1px solid rgba(16, 185, 129, 0.3);
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
    border-radius: 14px;
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
    border-radius: 14px;
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

/* Right Column: Editorial Details & Callback Form */
.ns-modal-col-right {
    padding: 28px 32px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 20px;
    background: var(--ns-surface);
}

.ns-modal-heading-group {
    border-bottom: 1px solid var(--ns-border-light);
    padding-bottom: 16px;
}

.ns-modal-product-title {
    font-family: 'Inter', var(--font-heading, sans-serif);
    font-size: clamp(1.4rem, 2.5vw, 1.85rem);
    font-weight: 800;
    color: var(--ns-text-dark);
    margin: 0 0 6px;
    line-height: 1.25;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    text-rendering: optimizeLegibility;
}

/* Price Highlight Card Inside Modal */
.ns-price-highlight-banner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 20px;
    border-radius: 18px;
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(245, 158, 11, 0.1) 100%);
    border: 1.5px solid rgba(16, 185, 129, 0.3);
}

.ns-modal-story-title {
    font-size: 0.96rem;
    font-weight: 800;
    color: var(--ns-text-dark);
    margin: 0 0 8px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-family: var(--font-heading, 'Be Vietnam Pro', sans-serif);
}

.ns-modal-story-p {
    font-size: 0.9rem;
    line-height: 1.68;
    color: var(--ns-text-muted);
    margin: 0 0 16px;
    background: var(--ns-cream);
    padding: 14px 16px;
    border-radius: 14px;
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

/* ── ANTIQUE ROYAL BANQUET PARCHMENT MENU BOARD ── */
.ns-mamco-royal-parchment {
    background: #fdfaf3 linear-gradient(180deg, #fefcf8 0%, #f7f0e1 100%);
    border: 2px solid #b45309;
    border-radius: 18px;
    padding: 20px 22px;
    box-shadow: 0 8px 24px rgba(180, 83, 9, 0.08), inset 0 0 20px rgba(217, 119, 6, 0.04);
    position: relative;
    margin-bottom: 8px;
}

[data-theme="dark"] .ns-mamco-royal-parchment {
    background: #18110b linear-gradient(180deg, #1e150f 0%, #140d07 100%);
    border-color: #d97706;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.5), inset 0 0 20px rgba(217, 119, 6, 0.1);
}

.ns-parchment-inner-border {
    border: 1px dashed rgba(180, 83, 9, 0.45);
    border-radius: 12px;
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

@media (max-width: 600px) {
    .ns-parchment-grid {
        grid-template-columns: 1fr;
    }
}

.ns-parchment-sec-box {
    background: rgba(255, 255, 255, 0.78);
    border: 1px solid rgba(180, 83, 9, 0.25);
    border-radius: 10px;
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

/* Fast Order Card Inside Modal */
.ns-order-glass-box {
    background: rgba(16, 185, 129, 0.05);
    border: 1.5px solid rgba(16, 185, 129, 0.25);
    border-radius: 20px;
    padding: 20px;
}

.ns-form-label {
    font-size: 0.78rem;
    font-weight: 800;
    color: var(--ns-text-dark);
    display: block;
    margin-bottom: 5px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.ns-form-input {
    width: 100%;
    padding: 10px 14px;
    border-radius: 12px;
    border: 1.5px solid var(--ns-border-light);
    background: var(--ns-surface);
    color: var(--ns-text-dark);
    font-size: 0.88rem;
    outline: none;
    transition: all 0.2s;
}

.ns-form-input:focus {
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
}

/* ── TRUST PILLARS STRIP (3 KHÔNG & MINH BẠCH) ── */
.ns-trust-strip {
    max-width: 1220px;
    margin: 0 auto 55px;
    padding: 0 20px;
}

.ns-trust-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
}

@media (max-width: 900px) {
    .ns-trust-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 500px) {
    .ns-trust-grid {
        grid-template-columns: 1fr;
    }
}

.ns-trust-card {
    background: var(--ns-surface);
    border-radius: 20px;
    border: 1.5px solid var(--ns-border-light);
    padding: 20px 18px;
    display: flex;
    align-items: flex-start;
    gap: 14px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
    transition: all 0.25s ease;
}

.ns-trust-card:hover {
    border-color: rgba(16, 185, 129, 0.4);
    transform: translateY(-3px);
    box-shadow: 0 10px 24px rgba(6, 40, 30, 0.08);
}

.ns-trust-icon-box {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    background: rgba(16, 185, 129, 0.12);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    flex-shrink: 0;
}

.ns-trust-title {
    font-size: 0.92rem;
    font-weight: 800;
    color: var(--ns-text-dark);
    margin: 0 0 4px;
}

.ns-trust-desc {
    font-size: 0.78rem;
    color: var(--ns-text-muted);
    line-height: 1.5;
    margin: 0;
}

/* ── PROPOSAL 1: CURATED GIFT COMBOS ── */
.ns-section-header {
    text-align: center;
    max-width: 780px;
    margin: 0 auto 36px;
    padding: 0 20px;
}

.ns-section-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 16px;
    border-radius: 50px;
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
    font-family: var(--font-heading, 'Be Vietnam Pro', sans-serif);
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

.ns-combos-container {
    max-width: 1220px;
    margin: 0 auto 75px;
    padding: 0 20px;
}

.ns-combos-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 26px;
}

@media (max-width: 990px) {
    .ns-combos-grid {
        grid-template-columns: 1fr;
        gap: 22px;
    }
}

.ns-combo-card {
    background: var(--ns-surface);
    border-radius: 24px;
    border: 1.5px solid rgba(245, 158, 11, 0.3);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    position: relative;
    transition: all 0.3s ease;
}

.ns-combo-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 20px 45px rgba(245, 158, 11, 0.18);
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
    border-radius: 50px;
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

.ns-combo-name {
    font-family: var(--font-heading, 'Be Vietnam Pro', sans-serif);
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
    border-radius: 14px;
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

/* ── PROPOSAL 2: SEASONAL HARVEST CALENDAR ── */
.ns-calendar-container {
    max-width: 1220px;
    margin: 0 auto 75px;
    padding: 0 20px;
}

.ns-calendar-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
}

@media (max-width: 1024px) {
    .ns-calendar-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 580px) {
    .ns-calendar-grid {
        grid-template-columns: 1fr;
    }
}

.ns-season-card {
    background: var(--ns-surface);
    border-radius: 22px;
    border: 1.5px solid var(--ns-border-light);
    padding: 24px 20px;
    display: flex;
    flex-direction: column;
    transition: all 0.3s ease;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
    position: relative;
    overflow: hidden;
}

.ns-season-card:hover {
    border-color: #10b981;
    transform: translateY(-5px);
    box-shadow: 0 16px 36px rgba(16, 185, 129, 0.12);
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
    border-radius: 16px;
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
    font-family: var(--font-heading, 'Be Vietnam Pro', sans-serif);
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
    border-top: 1px dashed var(--ns-border-light);
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

/* ── PROPOSAL 3: FARM EXPERIENCE BANNER ── */
.ns-experience-cta-wrap {
    max-width: 1220px;
    margin: 0 auto 80px;
    padding: 0 20px;
}

.ns-experience-cta-box {
    background: radial-gradient(circle at 90% 10%, rgba(245, 158, 11, 0.25) 0%, transparent 50%),
                linear-gradient(135deg, #022c22 0%, #064e3b 100%);
    border-radius: 30px;
    padding: 40px 48px;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 32px;
    border: 1.5px solid rgba(245, 158, 11, 0.35);
    box-shadow: 0 20px 50px rgba(2, 44, 34, 0.3);
    position: relative;
    overflow: hidden;
}

@media (max-width: 860px) {
    .ns-experience-cta-box {
        flex-direction: column;
        padding: 30px 24px;
        text-align: center;
    }
}

.ns-experience-cta-title {
    font-family: var(--font-heading, 'Be Vietnam Pro', sans-serif);
    font-size: clamp(1.4rem, 2.8vw, 2.1rem);
    font-weight: 900;
    margin: 0 0 10px;
    line-height: 1.25;
}

.ns-experience-cta-desc {
    font-size: 0.95rem;
    color: rgba(255, 255, 255, 0.85);
    line-height: 1.6;
    margin: 0;
    max-width: 650px;
}
</style>
