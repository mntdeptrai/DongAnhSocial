<?php $__env->startSection('title', 'Bản đồ số Đông Anh - Bản đồ Số hóa Dịch vụ & Địa phương'); ?>

<?php $__env->startSection('content'); ?>
<!-- Motion One Animation Library -->
<script src="https://cdn.jsdelivr.net/npm/motion@11.11.13/dist/motion.js"></script>

<style>
/* Custom styles for the brand-new DongAnh Discovery Banner */
.custom-hero-banner {
    position: relative;
    overflow: visible !important; /* Allow the search suggestions dropdown to float fully outside the banner without being cut off */
    padding: 70px 0 110px;
    background: linear-gradient(180deg, #7dd3fc 0%, #38bdf8 45%, #0ea5e9 85%, #0284c7 100%);
    min-height: 560px;
    z-index: 999; /* Set to 999 to float suggestions above Leaflet map panes (max 700) while keeping it under the header (1000) */
}

/* Clouds floating */
.cloud-bg {
    position: absolute;
    background: rgba(255, 255, 255, 0.45);
    backdrop-filter: blur(4px);
    border-radius: 100px;
    pointer-events: none;
    z-index: 1;
    animation: drift 35s linear infinite;
}
.cloud-bg::before, .cloud-bg::after {
    content: '';
    position: absolute;
    background: rgba(255, 255, 255, 0.45);
    border-radius: 50%;
}
.cloud-1 {
    width: 200px;
    height: 60px;
    top: 15%;
    left: -220px;
    animation-duration: 40s;
}
.cloud-1::before { width: 80px; height: 80px; top: -40px; left: 30px; }
.cloud-1::after { width: 100px; height: 100px; top: -50px; left: 80px; }

.cloud-2 {
    width: 140px;
    height: 45px;
    top: 32%;
    left: -160px;
    animation-duration: 28s;
    animation-delay: 6s;
}
.cloud-2::before { width: 60px; height: 60px; top: -30px; left: 20px; }
.cloud-2::after { width: 70px; height: 70px; top: -35px; left: 60px; }

.cloud-3 {
    width: 185px;
    height: 55px;
    top: 8%;
    left: -200px;
    animation-duration: 50s;
    animation-delay: 15s;
}
.cloud-3::before { width: 70px; height: 70px; top: -35px; left: 25px; }
.cloud-3::after { width: 90px; height: 90px; top: -45px; left: 70px; }

@keyframes drift {
    0% { transform: translateX(0); }
    100% { transform: translateX(calc(100vw + 250px)); }
}

/* Bubbles rising */
.bubble-particle {
    position: absolute;
    background: radial-gradient(circle at 30% 30%, rgba(255, 255, 255, 0.6) 0%, rgba(255, 255, 255, 0.1) 70%);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    pointer-events: none;
    z-index: 1;
    animation: rise 13s linear infinite;
}
@keyframes rise {
    0% {
        transform: translateY(100%) scale(0.8);
        opacity: 0;
    }
    10% {
        opacity: 0.8;
    }
    90% {
        opacity: 0.8;
    }
    100% {
        transform: translateY(-500px) scale(1.2);
        opacity: 0;
    }
}

/* Palm Leaves styling */
.palm-leaf {
    position: absolute;
    pointer-events: none;
    z-index: 3;
    filter: drop-shadow(0 8px 12px rgba(15, 23, 42, 0.18));
}
.palm-leaf-top-right {
    top: -20px;
    right: -20px;
    width: 220px;
    height: 220px;
    transform-origin: top right;
    animation: swayTopRight 6s ease-in-out infinite alternate;
}
.palm-leaf-bottom-left {
    bottom: -20px;
    left: -20px;
    width: 220px;
    height: 220px;
    transform-origin: bottom left;
    animation: swayBottomLeft 6s ease-in-out infinite alternate;
}

@keyframes swayTopRight {
    0% { transform: rotate(0deg) scale(1); }
    100% { transform: rotate(5deg) scale(1.03); }
}
@keyframes swayBottomLeft {
    0% { transform: rotate(0deg) scale(1); }
    100% { transform: rotate(-5deg) scale(1.03); }
}

/* Brand container centered on desktop */
.brand-container {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 30px;
    margin-top: 175px;
    margin-bottom: 25px;
    position: relative;
    z-index: 20;
}

.brand-divider-dot {
    width: 12px;
    height: 12px;
    background: #10b981;
    border: 3px solid #0f172a;
    border-radius: 50%;
    box-shadow: 0 4px 6px rgba(0,0,0,0.15);
}

/* Logo typography & 3D styling with green/cyan gradient */
.logo-title {
    font-family: 'Outfit', sans-serif;
    font-size: 3.8rem;
    font-weight: 900;
    line-height: 0.85;
    letter-spacing: -2px;
    margin: 0;
    background: linear-gradient(135deg, #10b981 0%, #059669 35%, #06b6d4 70%, #0891b2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    filter: drop-shadow(2px 2px 0px #0f172a) 
            drop-shadow(-2px -2px 0px #0f172a) 
            drop-shadow(2px -2px 0px #0f172a) 
            drop-shadow(-2px 2px 0px #0f172a)
            drop-shadow(0px 8px 12px rgba(15, 23, 42, 0.4));
    transform-origin: center;
}

.slogan-group {
    position: relative;
}

.slogan-text {
    font-family: 'Outfit', sans-serif;
    font-size: 1.5rem;
    font-weight: 800;
    line-height: 1.1;
    color: #0f172a;
    margin: 0;
    letter-spacing: -0.5px;
    text-transform: uppercase;
}

.slogan-icon-leaf {
    position: absolute;
    top: -18px;
    right: -20px;
    font-size: 1.1rem;
    transform: rotate(20deg);
}

.slogan-icon-star {
    position: absolute;
    bottom: -12px;
    left: -15px;
    font-size: 1rem;
    color: #ffb300;
}

/* Polaroid Cards Gallery */
.gallery-container {
    position: absolute;
    top: 25px;
    left: 0;
    width: 100%;
    height: 255px;
    z-index: 5;
    pointer-events: auto;
}

.polaroid-card {
    position: absolute;
    background: #ffffff;
    padding: 10px 10px 24px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.18), 0 3px 6px rgba(0,0,0,0.1);
    border-radius: 4px;
    width: 145px;
    cursor: pointer;
    opacity: 0;
    transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.4s ease, z-index 0.1s;
    transform-origin: center bottom;
}
.polaroid-card:hover {
    transform: scale(1.18) rotate(0deg) translateY(-10px) !important;
    box-shadow: 0 20px 40px rgba(0,0,0,0.25), 0 10px 15px rgba(0,0,0,0.15);
    z-index: 100 !important;
}

.polaroid-img {
    width: 100%;
    height: 110px;
    object-fit: cover;
    border: 1px solid rgba(0,0,0,0.06);
    border-radius: 2px;
}

.polaroid-caption {
    font-family: 'Outfit', sans-serif;
    font-size: 0.65rem;
    font-weight: 700;
    color: #475569;
    text-align: center;
    margin-top: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Tent Illustration */
.tent-camp-container {
    position: absolute;
    width: 120px;
    height: 100px;
    z-index: 4;
    opacity: 0;
}

/* Food Round Plate Illustration */
.food-plate-container {
    position: absolute;
    width: 110px;
    height: 110px;
    z-index: 6;
    border-radius: 50%;
    background: #ffffff;
    padding: 6px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    cursor: pointer;
    opacity: 0;
    transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.4s ease;
}
.food-plate-container:hover {
    transform: scale(1.2) rotate(15deg) !important;
    box-shadow: 0 15px 30px rgba(0,0,0,0.22);
    z-index: 100 !important;
}
.food-plate-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
    border: 2px dashed #f97316;
}

/* Stamps/Badges */
.travel-stamp {
    position: absolute;
    pointer-events: none;
    z-index: 8;
    opacity: 0;
    filter: drop-shadow(0 4px 8px rgba(0,0,0,0.12));
}

.stamp-plane {
    background: #e11d48;
    color: #ffffff;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    border: 2px dashed #ffffff;
    box-shadow: inset 0 0 0 2px #e11d48;
}

.stamp-circle-orange, .stamp-circle-green {
    width: 80px;
    height: 80px;
}

/* Multi-Layer Wavy bottom border */
.waves-wrapper {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    line-height: 0;
    z-index: 12;
}
.wave-layer {
    position: absolute;
    bottom: -1px;
    left: 0;
    width: 100%;
}
.wave-layer-1 {
    height: 50px;
    fill: #10b981;
    opacity: 0.35;
    z-index: 1;
}
.wave-layer-2 {
    height: 35px;
    fill: #06b6d4;
    opacity: 0.55;
    z-index: 2;
}
.wave-layer-3 {
    height: 20px;
    fill: var(--bg-base);
    z-index: 3;
}

/* Quick Navigation Capsule bar */
.quick-nav-bar-wrapper {
    position: absolute;
    bottom: 15px;
    left: 50%;
    transform: translateX(-50%);
    width: 100%;
    max-width: 880px;
    padding: 0 20px;
    z-index: 15;
}

.quick-nav-container {
    position: relative;
    background: #0284c7;
    border: 3.5px solid #ffb300;
    border-radius: 28px;
    box-shadow: 0 12px 35px rgba(0, 0, 0, 0.22), inset 0 1px 0 rgba(255,255,255,0.2);
}

.quick-nav-tab {
    position: absolute;
    top: -24px;
    left: 45px;
    background: #ffb300;
    border: 3.5px solid #ffb300;
    border-bottom: none;
    border-radius: 10px 10px 0 0;
    padding: 2px 14px 0;
    display: flex;
    gap: 6px;
    align-items: center;
}
.quick-nav-tab span {
    font-size: 0.8rem;
    filter: drop-shadow(0 2px 3px rgba(0,0,0,0.1));
}

.quick-nav-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 20px;
}

.quick-nav-btn {
    background: transparent;
    border: none;
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    text-align: left;
    padding: 4px 8px;
    border-radius: 12px;
    transition: transform 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275), background 0.25s ease;
}

.quick-nav-btn:hover {
    transform: scale(1.06) translateY(-2px);
    background: rgba(255, 255, 255, 0.08);
}

.quick-icon-wrapper {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #ffffff;
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    border: 1.5px solid rgba(255,255,255,0.8);
}
.quick-icon-wrapper svg {
    filter: drop-shadow(0 2px 3px rgba(0,0,0,0.1));
    transition: transform 0.3s ease;
}
.quick-nav-btn:hover .quick-icon-wrapper svg {
    transform: rotate(15deg) scale(1.15);
}

.circle-red { background: #fee2e2; }
.circle-green { background: #dcfce7; }
.circle-pink { background: #fce7f3; }
.circle-yellow { background: #fef9c3; }

.quick-text-wrapper {
    display: flex;
    flex-direction: column;
    color: #ffffff;
}

.quick-title {
    font-family: 'Outfit', sans-serif;
    font-size: 0.72rem;
    font-weight: 500;
    opacity: 0.85;
    letter-spacing: 0.5px;
    line-height: 1;
}

.quick-subtitle {
    font-family: 'Outfit', sans-serif;
    font-size: 0.82rem;
    font-weight: 800;
    letter-spacing: 0.5px;
    line-height: 1.2;
}

.quick-divider {
    width: 2.5px;
    height: 32px;
    background: rgba(255, 255, 255, 0.22);
}

.quick-map-badge {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: #ffb300;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #ffffff;
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    cursor: pointer;
    transition: transform 0.25s ease, background-color 0.25s ease;
}
.quick-map-badge:hover {
    transform: scale(1.12) rotate(-10deg);
    background: #ffa000;
}

/* Custom Search Container in Banner */
.custom-search-wrapper {
    max-width: 580px;
    margin: 0 auto;
    position: relative;
    z-index: 30;
}
.custom-search-box {
    display: flex;
    align-items: center;
    padding: 5px 6px;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 50px;
    border: 2px solid #ffb300;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.custom-search-box:focus-within {
    box-shadow: 0 15px 40px rgba(2, 132, 199, 0.3), 0 0 0 3px rgba(2, 132, 199, 0.1);
    transform: translateY(-2px);
    border-color: #0284c7;
}

.custom-search-input {
    flex: 1;
    background: transparent;
    border: none;
    color: #1e293b;
    padding: 10px 14px;
    font-size: 0.95rem;
    outline: none;
    font-weight: 600;
    font-family: 'Outfit', sans-serif;
}
.custom-search-input::placeholder {
    color: #64748b;
    font-style: italic;
    font-weight: 400;
}

.custom-search-prefix-icon {
    font-size: 1.15rem;
    margin-left: 14px;
}

.custom-search-submit-btn {
    background: #ffb300;
    color: #0f172a;
    border: none;
    border-radius: 50px;
    padding: 10px 22px;
    font-weight: 800;
    font-family: 'Outfit', sans-serif;
    font-size: 0.88rem;
    cursor: pointer;
    box-shadow: 0 4px 10px rgba(255, 179, 0, 0.25);
    transition: all 0.25s ease;
}
.custom-search-submit-btn:hover {
    background: #ffa000;
    transform: scale(1.02);
}

/* Responsiveness mapping rules */
@media (max-width: 992px) {
    .custom-hero-banner {
        padding: 40px 0 90px;
        min-height: auto;
    }
    .gallery-container {
        position: relative;
        top: 0;
        display: flex;
        overflow-x: auto;
        padding: 10px 20px;
        gap: 15px;
        scroll-snap-type: x mandatory;
        height: auto;
        scrollbar-width: none;
    }
    .gallery-container::-webkit-scrollbar {
        display: none;
    }
    .polaroid-card {
        position: relative !important;
        top: 0 !important;
        left: 0 !important;
        flex: 0 0 140px;
        scroll-snap-align: center;
        transform: rotate(0deg) !important;
        padding: 8px 8px 18px;
        opacity: 1 !important; /* show immediately in scroll */
    }
    .polaroid-img {
        height: 90px;
    }
    .tent-camp-container, .food-plate-container, .travel-stamp {
        display: none !important;
    }
    .brand-container {
        margin-top: 20px;
        flex-direction: column;
        gap: 10px;
        text-align: center;
    }
    .brand-divider-dot {
        display: none;
    }
    .logo-title {
        font-size: 3rem;
    }
    .slogan-text {
        font-size: 1.15rem;
    }
    .slogan-icon-leaf, .slogan-icon-star {
        display: none;
    }
    
    .quick-nav-bar-wrapper {
        position: relative;
        bottom: 0;
        transform: none;
        left: 0;
        margin: 25px auto 0;
        max-width: 100%;
        padding: 0 16px;
    }
    .quick-nav-container {
        border-radius: 20px;
    }
    .quick-nav-tab {
        display: none;
    }
    .quick-nav-content {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        padding: 12px;
    }
    .quick-divider {
        display: none;
    }
    .quick-nav-btn {
        background: rgba(255, 255, 255, 0.05);
        padding: 8px;
        border-radius: 12px;
        width: 100%;
    }
    .quick-icon-wrapper {
        width: 36px;
        height: 36px;
    }
    .quick-icon-wrapper svg {
        width: 18px;
        height: 18px;
    }
    .quick-subtitle {
        font-size: 0.72rem;
    }
    .quick-map-badge {
        grid-column: span 2;
        width: 100%;
        border-radius: 12px;
        height: 38px;
        margin-left: 0;
        margin-top: 5px;
    }
}
</style>

<section class="custom-hero-banner">
    <!-- Drift Clouds background -->
    <div class="cloud-bg cloud-1"></div>
    <div class="cloud-bg cloud-2"></div>
    <div class="cloud-bg cloud-3"></div>

    <!-- Swaying Palm Leaves -->
    <div class="palm-leaf palm-leaf-top-right">
        <svg viewBox="0 0 100 100" style="width: 100%; height: 100%;">
            <path d="M10,90 Q40,40 90,10" fill="none" stroke="#15803d" stroke-width="3" stroke-linecap="round" />
            <path d="M20,75 C25,60 15,50 10,48 C15,55 25,68 30,72 Z" fill="#22c55e" />
            <path d="M30,65 C38,50 30,38 25,35 C32,45 38,58 40,62 Z" fill="#16a34a" />
            <path d="M42,55 C52,38 45,28 40,25 C47,35 50,48 52,52 Z" fill="#22c55e" />
            <path d="M55,45 C65,28 60,18 55,15 C62,25 62,38 64,42 Z" fill="#15803d" />
            <path d="M68,35 C78,20 72,12 68,10 C74,18 73,28 75,32 Z" fill="#16a34a" />
            <path d="M80,25 C88,12 85,8 82,6 C86,12 84,20 85,22 Z" fill="#22c55e" />
        </svg>
    </div>
    <div class="palm-leaf palm-leaf-bottom-left">
        <svg viewBox="0 0 100 100" style="width: 100%; height: 100%; transform: scaleX(-1) rotate(45deg);">
            <path d="M10,90 Q40,40 90,10" fill="none" stroke="#15803d" stroke-width="3" stroke-linecap="round" />
            <path d="M20,75 C25,60 15,50 10,48 C15,55 25,68 30,72 Z" fill="#22c55e" />
            <path d="M30,65 C38,50 30,38 25,35 C32,45 38,58 40,62 Z" fill="#16a34a" />
            <path d="M42,55 C52,38 45,28 40,25 C47,35 50,48 52,52 Z" fill="#22c55e" />
            <path d="M55,45 C65,28 60,18 55,15 C62,25 62,38 64,42 Z" fill="#15803d" />
            <path d="M68,35 C78,20 72,12 68,10 C74,18 73,28 75,32 Z" fill="#16a34a" />
            <path d="M80,25 C88,12 85,8 82,6 C86,12 84,20 85,22 Z" fill="#22c55e" />
        </svg>
    </div>

    <!-- Bubbles particles -->
    <?php for($i = 0; $i < 10; $i++): ?>
        <?php
            $left = rand(5, 95);
            $size = rand(8, 24);
            $delay = rand(0, 100) / 10;
            $duration = rand(10, 16);
        ?>
        <div class="bubble-particle" style="left: <?php echo e($left); ?>%; width: <?php echo e($size); ?>px; height: <?php echo e($size); ?>px; bottom: -30px; animation-delay: <?php echo e($delay); ?>s; animation-duration: <?php echo e($duration); ?>s;"></div>
    <?php endfor; ?>

    <!-- Polaroid Galleries + Decorations -->
    <div class="gallery-container">
        <!-- Stamp: Plane -->
        <div class="travel-stamp stamp-plane" style="left: 7%; top: 135px; transform: rotate(-15deg);">
            ✈️
        </div>

        <!-- Polaroid 1: Scooter -->
        <div class="polaroid-card" style="left: 14%; top: 30px;" data-angle="-7">
            <img src="<?php echo e(asset('images/vivudonganh.jpg')); ?>" alt="Vi vu Đông Anh" class="polaroid-img">
            <div class="polaroid-caption">Vi vu Đông Anh</div>
        </div>

        <!-- Stamp: Orange circle -->
        <div class="travel-stamp stamp-circle-orange" style="left: 23%; top: 140px; transform: rotate(12deg);">
            <svg viewBox="0 0 100 100" style="width: 100%; height: 100%;">
                <path id="circlePath" d="M 50, 50 m -35, 0 a 35,35 0 1,1 70,0 a 35,35 0 1,1 -70,0" fill="none" />
                <text font-family="'Outfit', sans-serif" font-weight="900" font-size="10.5" fill="#f97316">
                    <textPath href="#circlePath" startOffset="0%">DONG ANH • DISCOVERY •</textPath>
                </text>
                <circle cx="50" cy="50" r="19" fill="none" stroke="#f97316" stroke-width="2" />
                <text x="50" y="55" font-family="'Outfit', sans-serif" font-weight="900" font-size="14" fill="#f97316" text-anchor="middle">⛵</text>
            </svg>
        </div>

        <!-- Polaroid 2: Lotus -->
        <div class="polaroid-card" style="left: 28%; top: 15px;" data-angle="4">
            <img src="<?php echo e(asset('images/caydabacho.jpg')); ?>" alt="Cây đa Bác Hồ" class="polaroid-img">
            <div class="polaroid-caption">Cây đa bác hồ</div>
        </div>

        <!-- Tent Camp center illustration -->
        <div class="tent-camp-container" style="left: 45%; top: 60px;">
            <svg viewBox="0 0 100 80" class="tent-camp-svg" style="width: 100%; height: 100%;">
                <!-- Tent -->
                <path d="M10,65 L50,15 L90,65 Z" fill="#0ea5e9" stroke="#0f172a" stroke-width="2.5" />
                <path d="M50,15 L90,65 L70,65 Z" fill="#0284c7" stroke="#0f172a" stroke-width="2.5" />
                <!-- Tent Door -->
                <path d="M50,15 L35,65 L65,65 Z" fill="#ffb300" stroke="#0f172a" stroke-width="2" />
                <path d="M50,15 L50,65" stroke="#0f172a" stroke-width="2" />
                <!-- Campfire logs -->
                <rect x="25" y="70" width="20" height="5" rx="2" fill="#854d0e" stroke="#0f172a" stroke-width="1.5" transform="rotate(15 35 72.5)" />
                <rect x="35" y="70" width="20" height="5" rx="2" fill="#854d0e" stroke="#0f172a" stroke-width="1.5" transform="rotate(-15 45 72.5)" />
                <!-- Campfire flames -->
                <path d="M35,70 C35,60 40,55 45,50 C50,55 55,60 55,70 Z" fill="#ef4444" />
                <path d="M40,70 C40,63 43,60 45,57 C47,60 50,63 50,70 Z" fill="#f97316" />
                <path d="M43,70 C43,66 44,64 45,62 C46,64 47,66 47,70 Z" fill="#eab308" />
            </svg>
        </div>

        <!-- Polaroid 3: Gate -->
        <div class="polaroid-card" style="left: 56%; top: 22px;" data-angle="6">
            <img src="<?php echo e(asset('images/thanhcoloa.webp')); ?>" alt="Thành Cổ Loa" class="polaroid-img">
            <div class="polaroid-caption">Thành Cổ Loa</div>
        </div>

        <!-- Stamp: Green circle -->
        <div class="travel-stamp stamp-circle-green" style="left: 68%; top: 135px; transform: rotate(-10deg);">
            <svg viewBox="0 0 100 100" style="width: 100%; height: 100%;">
                <path id="circlePath2" d="M 50, 50 m -35, 0 a 35,35 0 1,1 70,0 a 35,35 0 1,1 -70,0" fill="none" />
                <text font-family="'Outfit', sans-serif" font-weight="900" font-size="12" fill="#0d9488">
                    <textPath href="#circlePath2" startOffset="0%">DONG ANH • MAP •</textPath>
                </text>
                <circle cx="50" cy="50" r="21" fill="none" stroke="#0d9488" stroke-width="2" stroke-dasharray="3,3" />
                <text x="50" y="55" font-family="'Outfit', sans-serif" font-weight="900" font-size="14" fill="#0d9488" text-anchor="middle">🗺️</text>
            </svg>
        </div>

        <!-- Polaroid 4: Concert -->
        <div class="polaroid-card" style="left: 73%; top: 35px;" data-angle="-5">
            <img src="<?php echo e(asset('images/trungtamvanhoa.webp')); ?>" alt="Trung tâm văn hóa" class="polaroid-img">
            <div class="polaroid-caption">Trung tâm văn hóa</div>
        </div>

        <!-- Food Plate: Bun cha -->
        <div class="food-plate-container" style="left: 85%; top: 110px; transform: rotate(10deg);">
            <img src="https://images.unsplash.com/photo-1596797038530-2c107229654b?auto=format&fit=crop&w=400&q=80" alt="Bún chả Đông Anh" class="food-plate-img">
        </div>
    </div>

    <!-- Main Relative content: brand info, search box, quick filters -->
    <div class="relative z-20 text-center px-4" style="max-width: 960px; margin: 0 auto;">
        <!-- Logo & Slogan -->
        <div class="brand-container">
            <div class="logo-group">
                <h1 class="logo-title">
                    <span>DongAnh</span><br>
                    <span>Discovery</span>
                </h1>
            </div>
            <div class="brand-divider-dot"></div>
            <div class="slogan-group">
                <p class="slogan-text">
                    Đi là mê,<br>chạm là thích
                </p>
                <span class="slogan-icon-leaf">🍃</span>
                <span class="slogan-icon-star">⭐</span>
            </div>
        </div>

        <!-- Search box container -->
        <div class="custom-search-wrapper">
            <form action="/tim-kiem" method="GET" class="search-form" id="searchForm">
                <div class="custom-search-box">
                    <span class="custom-search-prefix-icon">🔍</span>
                    <input type="text" name="q" id="searchInput" class="custom-search-input" placeholder="Tìm kiếm 'Bún chả', 'Bệnh viện Đông Anh', 'Trường THPT Liên Hà', 'Khách sạn'..." autocomplete="off">
                    <button type="submit" class="custom-search-submit-btn">Tìm kiếm</button>
                </div>
            </form>
            <div id="suggestionDropdown" class="autocomplete-suggestions glass-panel"></div>
        </div>
    </div>

    <!-- Bottom waves visual transition -->
    <div class="waves-wrapper">
        <svg viewBox="0 0 1200 60" preserveAspectRatio="none" class="wave-layer wave-layer-1">
            <path d="M0,30 C300,10 600,50 900,20 C1050,5 1150,15 1200,25 L1200,60 L0,60 Z"></path>
        </svg>
        <svg viewBox="0 0 1200 60" preserveAspectRatio="none" class="wave-layer wave-layer-2">
            <path d="M0,40 C400,20 800,60 1200,30 L1200,60 L0,60 Z"></path>
        </svg>
        <svg viewBox="0 0 1200 30" preserveAspectRatio="none" class="wave-layer wave-layer-3">
            <path d="M0,15 C300,5 600,25 900,10 C1050,2 1150,8 1200,12 L1200,30 L0,30 Z"></path>
        </svg>
    </div>

    <!-- Quick Navigation floating folder -->
    <div class="quick-nav-bar-wrapper">
        <div class="quick-nav-container">
            <!-- cute index tab -->
            <div class="quick-nav-tab">
                <span>❤️</span>
                <span>🍳</span>
                <span>🪷</span>
                <span>🎡</span>
            </div>

            <!-- buttons list -->
            <div class="quick-nav-content">
                <button type="button" class="quick-nav-btn btn-y-te" onclick="triggerQuickFilter('wellness-care', '/?cat=wellness-care')">
                    <div class="quick-icon-wrapper circle-red">
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="#ef4444">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                        </svg>
                    </div>
                    <div class="quick-text-wrapper">
                        <span class="quick-title">CHĂM SÓC</span>
                        <span class="quick-subtitle">Y TẾ TẬN TÂM</span>
                    </div>
                </button>

                <div class="quick-divider"></div>

                <button type="button" class="quick-nav-btn btn-am-thuc" onclick="triggerQuickFilter('dong-anh-food-map', '/?cat=dong-anh-food-map')">
                    <div class="quick-icon-wrapper circle-green">
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="#10b981">
                            <path d="M11 9H9V2H7v7H5V2H3v7c0 2.12 1.66 3.84 3.75 3.97V22h2.5v-9.03C11.34 12.84 13 11.12 13 9V2h-2v7zm5-3v8h2.5v8H21V2c-2.76 0-5 2.24-5 4z"/>
                        </svg>
                    </div>
                    <div class="quick-text-wrapper">
                        <span class="quick-title">ẨM THỰC</span>
                        <span class="quick-subtitle">TINH TÚY</span>
                    </div>
                </button>

                <div class="quick-divider"></div>

                <button type="button" class="quick-nav-btn btn-nghi-duong" onclick="triggerQuickFilter('stay-in-dong-anh', '/?cat=stay-in-dong-anh')">
                    <div class="quick-icon-wrapper circle-pink">
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="#ec4899">
                            <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 4.86 6 7.42 6 10.5v5l-2 2v1h16v-1l-2-2z"/>
                        </svg>
                    </div>
                    <div class="quick-text-wrapper">
                        <span class="quick-title">NGHỈ DƯỠNG</span>
                        <span class="quick-subtitle">ĐẲNG CẤP</span>
                    </div>
                </button>

                <div class="quick-divider"></div>

                <button type="button" class="quick-nav-btn btn-vui-choi" onclick="triggerQuickFilter('discover-dong-anh-community-culture-hub', '/?cat=discover-dong-anh-community-culture-hub')">
                    <div class="quick-icon-wrapper circle-yellow">
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="#eab308">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                        </svg>
                    </div>
                    <div class="quick-text-wrapper">
                        <span class="quick-title">VUI CHƠI</span>
                        <span class="quick-subtitle">BẤT TẬN</span>
                    </div>
                </button>

                <!-- Map button -->
                <div class="quick-map-badge" title="Tìm kiếm trên Bản đồ" onclick="window.location.href='/tim-kiem'">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="#ffffff">
                        <path d="M20.5 3l-.16.03L15 5.1 9 3 3.36 4.9c-.21.07-.36.25-.36.48V20.5c0 .28.22.5.5.5l.16-.03L9 18.9l6 2.1 5.64-1.9c.21-.07.36-.25.36-.48V3.5c0-.28-.22-.5-.5-.5zM15 19l-6-2.11V5l6 2.11V19z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Categories Slider -->
<div class="container" style="margin-bottom: 24px; max-width: 1400px;">
    <div class="categories-slider">
        <a href="/" class="category-card glass-panel <?php echo e(!$selectedCatSlug ? 'active' : ''); ?>">
            <span class="cat-icon">🗺️</span>
            <span class="cat-name">
                <span class="cat-title-en">All Places</span>
                <span class="cat-title-vi">Tất cả địa điểm</span>
            </span>
        </a>
        <a href="/checkin" class="category-card glass-panel checkin-highlight-card">
            <span class="cat-icon">📸</span>
            <span class="cat-name">
                <span class="cat-title-en">Check-in Feed</span>
                <span class="cat-title-vi">Cộng đồng Check-in</span>
            </span>
        </a>
        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if($cat->slug === 'checkin-dong-anh'): ?>
                <?php continue; ?>
            <?php endif; ?>
            <?php
                $displayIcon = $cat->icon;
                $displayNameEn = $cat->name;
                $displayNameVi = $cat->name;
                if ($cat->slug === 'dong-anh-food-map') {
                    $displayIcon = '🍜';
                    $displayNameEn = 'DONGANH DISCOVERY';
                    $displayNameVi = 'Bản đồ khám phá đông anh';
                } elseif ($cat->slug === 'stay-in-dong-anh') {
                    $displayIcon = '🛌';
                    $displayNameEn = 'Stay in Đông Anh';
                    $displayNameVi = 'Nhà nghỉ, khách sạn, khu nghỉ dưỡng';
                } elseif ($cat->slug === 'wellness-care') {
                    $displayIcon = '🩺';
                    $displayNameEn = 'Wellness & Care';
                    $displayNameVi = 'Y tế – chăm sóc sức khỏe – spa';
                } elseif ($cat->slug === 'dong-anh-market') {
                    $displayIcon = '🛍️';
                    $displayNameEn = 'Đông Anh Market';
                    $displayNameVi = 'OCOP – quà tặng – đặc sản';
                } elseif ($cat->slug === 'smart-education-map') {
                    $displayIcon = '🎓';
                    $displayNameEn = 'Smart Education Map';
                    $displayNameVi = 'Trường học';
                } elseif ($cat->slug === 'hanh-trinh-di-san') {
                    $displayIcon = '⛩️';
                    $displayNameEn = 'Heritage Journey';
                    $displayNameVi = 'Hành trình di sản';
                } elseif ($cat->slug === 'discover-dong-anh-community-culture-hub') {
                    $displayIcon = '🏛️';
                    $displayNameEn = 'Community & Culture Hub';
                    $displayNameVi = 'Thiết chế văn hóa - thể thao';
                }
            ?>
            <?php if($cat->slug === 'hanh-trinh-di-san'): ?>
                <a href="https://donganh360.vn" target="_blank" rel="noopener noreferrer" class="category-card glass-panel">
                    <img src="<?php echo e(asset('images/den_tho_kinh_duong_vuong_thanh_co_luy_lau_.webp')); ?>" alt="Hành trình di sản" class="cat-icon-img" style="width: 56px; height: 56px; border-radius: 50%; object-fit: cover;">
                    <span class="cat-name">
                        <span class="cat-title-en"><?php echo e($displayNameEn); ?></span>
                        <span class="cat-title-vi"><?php echo e($displayNameVi); ?></span>
                    </span>
                </a>
            <?php elseif($cat->slug === 'discover-dong-anh-community-culture-hub'): ?>
                <a href="/?cat=<?php echo e($cat->slug); ?>" class="category-card glass-panel <?php echo e($selectedCatSlug === $cat->slug ? 'active' : ''); ?>">
                    <img src="<?php echo e(asset('images/nha_van_hoa_dong_anh.png')); ?>" alt="Community & Culture Hub" class="cat-icon-img" style="width: 56px; height: 56px; border-radius: 50%; object-fit: cover;">
                    <span class="cat-name">
                        <span class="cat-title-en"><?php echo e($displayNameEn); ?></span>
                        <span class="cat-title-vi"><?php echo e($displayNameVi); ?></span>
                    </span>
                </a>
            <?php else: ?>
                <a href="/?cat=<?php echo e($cat->slug); ?>" class="category-card glass-panel <?php echo e($selectedCatSlug === $cat->slug ? 'active' : ''); ?> <?php echo e($cat->slug === 'dong-anh-market' ? 'specialty-highlight-card' : ''); ?>">
                    <span class="cat-icon"><?php echo e($displayIcon); ?></span>
                    <span class="cat-name">
                        <span class="cat-title-en"><?php echo e($displayNameEn); ?></span>
                        <span class="cat-title-vi"><?php echo e($displayNameVi); ?></span>
                    </span>
                </a>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>

<!-- Interactive Split Screen Layout -->
<section class="split-screen" style="border-top: 1px solid var(--border-glow);">
    
    <!-- Left column: Scrollable feed of eateries -->
    <div class="split-list">
        <div id="listHeaderContainer">
            <?php if($selectedCatSlug === 'dong-anh-market'): ?>
                <div style="margin-bottom: 20px; border-bottom: 1.5px dashed rgba(212, 175, 55, 0.3); padding-bottom: 16px;">
                    <span class="heritage-badge" style="margin-bottom: 8px; font-size: 0.7rem; font-weight: 800; letter-spacing: 1.5px; border: 1px solid rgba(212, 175, 55, 0.4); background: rgba(212, 175, 55, 0.1); color: #ffb300; padding: 4px 10px; border-radius: 20px; display: inline-block;">🛍️ CHỢ TRUYỀN THỐNG & ĐẶC SẢN OCOP / TRADITIONAL MARKET & OCOP SPECIALTIES</span>
                    <h2 style="font-size: 1.6rem; font-family: var(--font-heading); font-weight: 800; margin: 4px 0 6px 0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                        <span style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Không Gian Chợ & Đặc Sản Đông Anh <span style="font-size: 1.1rem; color: var(--text-muted); font-weight: 600; display: block; margin-top: 4px;">(Đông Anh Market & Local Specialties)</span></span>
                        <span id="resultsCountSpan" style="font-size: 0.85rem; color: var(--text-muted); font-weight: normal;">
                            (<?php echo e($eateries->count()); ?> địa điểm / places)
                        </span>
                    </h2>
                    <p style="font-size: 0.88rem; color: var(--text-muted); line-height: 1.5; margin: 0;">
                        Khám phá các sản phẩm OCOP đặc trưng, quà lưu niệm độc đáo, nông sản sạch cùng hệ thống các siêu thị, chợ truyền thống nhộn nhịp mang đậm hồn quê Đông Anh. <span style="display: block; font-style: italic; margin-top: 4px; font-size: 0.8rem; opacity: 0.8;">Discover signature OCOP products, unique souvenirs, organic agriculture, local supermarkets and lively traditional markets filled with Đông Anh's cultural soul.</span>
                    </p>
                </div>
            <?php else: ?>
                <h2 style="font-size: 1.25rem; margin: 6px 0 0 0; font-family: var(--font-heading); font-weight: 700; line-height: 1.4; color: var(--text-main);">
                    <span style="margin-right: 4px;">📍</span> 
                    <?php if($selectedCatSlug): ?>
                        <?php
                            $selectedCat = $categories->where('slug', $selectedCatSlug)->first();
                            $selEn = $selectedCat->name;
                            $selVi = $selectedCat->name;
                            if ($selectedCatSlug === 'dong-anh-food-map') {
                                $selEn = 'DongAnh Discovery';
                                $selVi = 'Bản đồ khám phá đông anh';
                            } elseif ($selectedCatSlug === 'stay-in-dong-anh') {
                                $selEn = 'Stay in Đông Anh';
                                $selVi = 'Nhà nghỉ, khách sạn, khu nghỉ dưỡng';
                            } elseif ($selectedCatSlug === 'wellness-care') {
                                $selEn = 'Wellness & Care';
                                $selVi = 'Y tế – chăm sóc sức khỏe – spa';
                            } elseif ($selectedCatSlug === 'dong-anh-market') {
                                $selEn = 'Đông Anh Market';
                                $selVi = 'OCOP – quà tặng – đặc sản';
                            } elseif ($selectedCatSlug === 'smart-education-map') {
                                $selEn = 'Smart Education Map';
                                $selVi = 'Trường học';
                            } elseif ($selectedCatSlug === 'hanh-trinh-di-san') {
                                $selEn = 'Heritage Journey';
                                $selVi = 'Hành trình di sản';
                            } elseif ($selectedCatSlug === 'discover-dong-anh-community-culture-hub') {
                                $selEn = 'Discover Dong Anh Community & Culture Hub';
                                $selVi = 'Khám phá thiết chế văn hóa - thể thao Đông Anh';
                            }
                        ?>
                        <?php echo e($selVi); ?> <span style="font-size: 0.9rem; color: var(--text-muted); font-weight: 500; font-style: italic;">(<?php echo e($selEn); ?>)</span>
                    <?php else: ?>
                        Địa điểm nổi bật <span style="font-size: 0.9rem; color: var(--text-muted); font-weight: 500; font-style: italic;">(Featured Places)</span>
                    <?php endif; ?>
                    <span id="resultsCountSpan" style="font-size: 0.8rem; color: var(--text-muted); font-weight: normal; margin-left: 6px; display: inline-block; white-space: nowrap;">
                        (<?php echo e($eateries->count()); ?> địa điểm / places)
                    </span>
                </h2>
            <?php endif; ?>
        </div>
        
        <div id="eateriesListContainer" style="display: flex; flex-direction: column; gap: 24px; width: 100%;">
            <?php if($eateries->count() > 0): ?>
                <?php $__currentLoopData = $eateries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $eat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="eatery-card glass-panel reveal reveal-fade-up hover-lift" 
                         data-slug="<?php echo e($eat->slug); ?>"
                         data-name="<?php echo e($eat->name); ?>"
                         data-address="<?php echo e($eat->address); ?>"
                         data-desc="<?php echo e($eat->description); ?>"
                         data-commune="<?php echo e($eat->commune->name); ?>"
                         data-category="<?php echo e($eat->category->slug); ?>"
                         onclick="focusOnEatery(<?php echo e(number_format($eat->latitude, 6, '.', '')); ?>, <?php echo e(number_format($eat->longitude, 6, '.', '')); ?>, '<?php echo e($eat->slug); ?>')">
                        <div class="eatery-img-wrapper hover-zoom-container">
                            <img src="<?php echo e($eat->image_path ?: 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=300&q=80'); ?>" class="eatery-img hover-zoom-img" alt="<?php echo e($eat->name); ?>">
                            <div style="position: absolute; top: 8px; left: 8px; max-width: calc(100% - 16px); display: flex; align-items: center; gap: 4px; font-size: 0.68rem; font-weight: 700; color: #ffffff; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(4px); padding: 4px 8px; border-radius: 6px; box-shadow: 0 2px 10px rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1);">
                                <span><?php echo e($eat->category->icon); ?></span>
                                <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo e($eat->category->name); ?></span>
                            </div>
                        </div>
                        <div class="eatery-info">
                            <div class="eatery-header">
                                <h3 class="eatery-title"><?php echo e($eat->name); ?></h3>
                                <div class="rating-stars">
                                    <span>⭐</span> <?php echo e($eat->average_rating); ?>

                                </div>
                            </div>
                            <?php if(!empty($eat->description) && $eat->description !== 'null'): ?>
                            <p class="eatery-desc"><?php echo e($eat->description); ?></p>
                            <?php endif; ?>
                            <div class="eatery-footer">
                                <div class="eatery-meta-item">
                                    <span>📍</span> <?php echo e($eat->commune->name); ?>

                                </div>
                                <?php if(!in_array($eat->category->slug, ['smart-education-map', 'hanh-trinh-di-san', 'discover-dong-anh-community-culture-hub'])): ?>
                                <div class="eatery-meta-item" style="color: var(--primary); font-weight: 600;">
                                    <?php echo e($eat->price_range); ?>

                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php else: ?>
                <div class="glass-panel" style="padding: 40px; text-align: center; color: var(--text-muted); width: 100%;">
                    <p style="font-size: 1.2rem; margin-bottom: 8px;">😔 Không tìm thấy địa điểm nào phù hợp</p>
                    <p style="font-size: 0.9rem;">Hãy thử lọc danh mục khác hoặc xóa bộ lọc để khám phá lại toàn bộ Đông Anh!</p>
                    <a href="/" class="btn-primary" style="margin-top: 16px; padding: 8px 16px; text-decoration: none; display: inline-block;">Xem tất cả</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Right column: Premium Leaflet map -->
    <div class="split-map-container" style="position: relative;">
        <div id="map"></div>
        
        <!-- Floating Tóp Tóp Reels FAB removed -->
    </div>
</section>

<!-- Fullscreen Reels Modal -->
<div id="reelsModal" class="reels-overlay" style="display: none;">

    <div class="reels-container glass-panel">
        <div class="reels-video-wrapper">
            <!-- Dynamic video / iframe player container -->
            <div id="reelPlayerWrapper" style="width: 100%; height: 100%; position: absolute; inset: 0; z-index: 1;"></div>
            
            <!-- Instagram-style top progress bars -->
            <div id="reelsProgressBars" style="position: absolute; top: 12px; left: 16px; right: 16px; display: flex; gap: 4px; z-index: 15;"></div>
            
            <!-- Interactive inner next/prev navigation buttons -->
            <button id="modalPrevReelBtn" onclick="searchPrevReel()" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.55); border: 1px solid rgba(255,255,255,0.25); width: 40px; height: 40px; border-radius: 50%; color: #fff; cursor: pointer; display: none; align-items: center; justify-content: center; font-size: 1.1rem; transition: all 0.2s; z-index: 12; backdrop-filter: blur(8px); pointer-events: auto; box-shadow: 0 4px 10px rgba(0,0,0,0.4);" onmouseover="this.style.background='rgba(0,0,0,0.75)'; this.style.transform='translateY(-50%) scale(1.1)';" onmouseout="this.style.background='rgba(0,0,0,0.55)'; this.style.transform='translateY(-50%) scale(1)';">◀</button>
            <button id="modalNextReelBtn" onclick="searchNextReel()" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.55); border: 1px solid rgba(255,255,255,0.25); width: 40px; height: 40px; border-radius: 50%; color: #fff; cursor: pointer; display: none; align-items: center; justify-content: center; font-size: 1.1rem; transition: all 0.2s; z-index: 12; backdrop-filter: blur(8px); pointer-events: auto; box-shadow: 0 4px 10px rgba(0,0,0,0.4);" onmouseover="this.style.background='rgba(0,0,0,0.75)'; this.style.transform='translateY(-50%) scale(1.1)';" onmouseout="this.style.background='rgba(0,0,0,0.55)'; this.style.transform='translateY(-50%) scale(1)';">▶</button>
            
            <div class="reels-header-controls" style="z-index: 10; margin-top: 6px;">
                <span class="reels-badge-live">🎥 REVIEW THỰC TẾ</span>
                <button class="reels-close-btn" style="pointer-events: auto;" onclick="closeReelsModal()">✕</button>
            </div>
            
            <div class="reels-overlay-info" style="z-index: 10; bottom: 20px; left: 16px; right: 80px;">
                <h3 class="reels-eatery-name" id="reelsEateryName" style="font-size: 1.05rem; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.95); margin: 0; font-family: var(--font-heading); color: #ffffff;">Bún Mạch Tràng Cổ Loa</h3>
                <p class="reels-desc" id="reelsVideoDesc" style="display: none !important;"></p>
                <span class="reels-signature-tag" style="display: none !important;"></span>
            </div>
            
            <!-- Double click/Tap overlay to fly hearts -->
            <div id="reelTapOverlay" style="position: absolute; inset: 0; z-index: 5; pointer-events: auto;" onclick="triggerDoubleTapHeart(event)"></div>
        </div>
        
        <!-- Right Action sidebar (Premium Glassmorphic Cinema style) -->
        <div class="reels-side-actions" style="right: 14px; bottom: 50px; gap: 14px;">
            <div style="display: flex; flex-direction: column; align-items: center;">
                <button type="button" class="reels-action-btn" id="reelsLikeBtn" onclick="toggleReelsLike()" style="background: rgba(255, 255, 255, 0.12); width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 4px 15px rgba(0,0,0,0.3);" onmouseover="this.style.transform='scale(1.15) translateY(-2px)'; this.style.backgroundColor='rgba(255, 255, 255, 0.25)';" onmouseout="this.style.transform='scale(1)'; this.style.backgroundColor='rgba(255, 255, 255, 0.12)';">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="#ff3366" stroke="#ff3366" stroke-width="2" style="filter: drop-shadow(0 0 4px rgba(255, 51, 102, 0.6));"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                </button>
                <span class="reels-action-label" id="reelsLikeCount" style="margin-top: 6px; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.8); font-size: 0.72rem;">3.8K</span>
            </div>
            
            <div style="display: flex; flex-direction: column; align-items: center;">
                <button type="button" class="reels-action-btn" onclick="alert('Đã thêm quán ăn này vào Danh sách Yêu thích của bạn!')" style="background: rgba(255, 255, 255, 0.12); width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 4px 15px rgba(0,0,0,0.3);" onmouseover="this.style.transform='scale(1.15) translateY(-2px)'; this.style.backgroundColor='rgba(255, 255, 255, 0.25)';" onmouseout="this.style.transform='scale(1)'; this.style.backgroundColor='rgba(255, 255, 255, 0.12)';">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="#ffb800" stroke="#ffb800" stroke-width="2" style="filter: drop-shadow(0 0 4px rgba(255, 184, 0, 0.6));"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                </button>
                <span class="reels-action-label" style="margin-top: 6px; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.8); font-size: 0.72rem;">4.8</span>
            </div>
            
            <div style="display: flex; flex-direction: column; align-items: center;">
                <button type="button" class="reels-action-btn" onclick="navigator.clipboard.writeText(window.location.href); alert('Đã sao chép liên kết chia sẻ review của quán!');" style="background: rgba(255, 255, 255, 0.12); width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 4px 15px rgba(0,0,0,0.3);" onmouseover="this.style.transform='scale(1.15) translateY(-2px)'; this.style.backgroundColor='rgba(255, 255, 255, 0.25)';" onmouseout="this.style.transform='scale(1)'; this.style.backgroundColor='rgba(255, 255, 255, 0.12)';">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="filter: drop-shadow(0 0 3px rgba(255,255,255,0.4));"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                </button>
                <span class="reels-action-label" style="margin-top: 6px; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.8); font-size: 0.72rem;">Chia sẻ</span>
            </div>
            
            <div class="reels-music-disc" style="border: 2px solid var(--primary); background: radial-gradient(circle, var(--primary) 30%, #000 70%); font-size: 0.95rem; box-shadow: 0 0 10px rgba(var(--primary-rgb), 0.5);">🍜</div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    // 1. Khởi tạo dữ liệu JSON của các quán ăn được truyền từ PHP Controller
    const eateries = <?php echo json_encode($eateries, 15, 512) ?>;
    let map;
    let markers = {};

    // 0. Motion One Animations Initialization
    document.addEventListener("DOMContentLoaded", () => {
        if (window.Motion) {
            const { animate } = window.Motion;

            // Animate each polaroid to its target rotation & load state dynamically
            const cards = document.querySelectorAll(".polaroid-card");
            cards.forEach((card, index) => {
                const angle = parseFloat(card.getAttribute("data-angle") || "0");
                animate(card,
                    { opacity: [0, 1], y: [40, 0], scale: [0.9, 1], rotate: [0, angle] },
                    {
                        delay: 0.15 + (index * 0.12),
                        duration: 0.8,
                        easing: "ease-out"
                    }
                );
            });

            // Animate food plate
            animate(".food-plate-container",
                { opacity: [0, 1], scale: [0.6, 1], rotate: [0, 10] },
                { delay: 0.7, duration: 0.85, easing: "ease-out" }
            );

            // Animate tent decoration
            animate(".tent-camp-container",
                { opacity: [0, 1], scale: [0.8, 1] },
                { delay: 0.55, duration: 0.8, easing: "ease-out" }
            );

            // Animate stamps/badges
            const stamps = document.querySelectorAll(".travel-stamp");
            stamps.forEach((stamp, index) => {
                animate(stamp,
                    { opacity: [0, 1], scale: [0, 1.2, 1] },
                    { delay: 0.95 + (index * 0.15), duration: 0.6, easing: "ease-out" }
                );
            });

            // Infinite subtle bobbing animation for brand logo and slogan
            animate(".logo-title",
                { y: [-5, 5] },
                { duration: 2.5, repeat: Infinity, direction: "alternate", easing: "ease-in-out" }
            );
            animate(".slogan-group",
                { rotate: [-1, 1], y: [1, -1] },
                { duration: 2.0, repeat: Infinity, direction: "alternate", easing: "ease-in-out" }
            );
        }
    });

    window.triggerQuickFilter = function(slug, href) {
        const categoryCard = document.querySelector(`.category-card[href*="cat=${slug}"]`);
        if (categoryCard) {
            document.querySelectorAll('.category-card').forEach(c => c.classList.remove('active'));
            categoryCard.classList.add('active');
            centerActiveCategoryCard(categoryCard);
            if (window.filterCategoryAjax) {
                window.filterCategoryAjax(slug, href);
            }
            setTimeout(() => {
                const splitList = document.querySelector('.split-list');
                if (splitList) {
                    window.scrollTo({
                        top: splitList.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            }, 300);
        } else {
            window.location.href = href;
        }
    };

    // Hàm tự động cuộn thẻ danh mục được chọn vào chính giữa thanh trượt trơn tru (Google Maps/Airbnb Style)
    function centerActiveCategoryCard(cardElement) {
        const slider = document.querySelector('.categories-slider');
        if (!slider || !cardElement) return;

        const sliderWidth = slider.clientWidth;
        const sliderRect = slider.getBoundingClientRect();
        const cardRect = cardElement.getBoundingClientRect();
        
        // Vị trí thực tế của thẻ so với điểm bắt đầu của nội dung trượt (kể cả khi đã cuộn)
        const relativeLeft = cardRect.left - sliderRect.left + slider.scrollLeft;
        const cardWidth = cardElement.clientWidth;

        // Tính toán khoảng cách để đưa thẻ về giữa
        const targetScrollLeft = relativeLeft - (sliderWidth / 2) + (cardWidth / 2);

        slider.scrollTo({
            left: targetScrollLeft,
            behavior: 'smooth'
        });
    }

    document.addEventListener("DOMContentLoaded", function() {
        // 2. Thiết lập bản đồ Leaflet tâm vị trí Đông Anh (huyện lỵ)
        map = L.map('map', {
            zoomControl: false, // Chúng ta sẽ tùy chỉnh vị trí nút zoom
            zoomSnap: 0.5,       // Bước zoom 0.5 giúp phản hồi nhanh nhạy
            zoomDelta: 0.5,      // Độ nhảy zoom mỗi lần cuộn
            wheelPxPerZoomLevel: 60, // Tốc độ zoom tiêu chuẩn nhanh & mượt
            zoomAnimation: true,
            fadeAnimation: true,
            markerZoomAnimation: true
        }).setView([21.1352, 105.8458], 13);
        
        L.control.zoom({ position: 'bottomright' }).addTo(map);

        // 3. Sử dụng Tileset phù hợp chế độ Sáng/Tối (Sử dụng Google Maps chính thức cho bản đồ sáng)
        let currentTheme = localStorage.getItem('theme') || 'light';
        let tileUrl = currentTheme === 'light' 
            ? 'https://mt1.google.com/vt/lyrs=m&hl=vi&x={x}&y={y}&z={z}'
            : 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png';
            
        let activeTileLayer = L.tileLayer(tileUrl, {
            attribution: currentTheme === 'light' ? '&copy; Google Maps' : '&copy; OpenStreetMap &copy; CARTO',
            subdomains: 'abcd',
            maxZoom: 20
        }).addTo(map);

        // Lắng nghe sự kiện đổi chế độ Sáng/Tối để đổi lớp nền bản đồ tức thì
        document.addEventListener('theme-changed', function(e) {
            const nextTheme = e.detail.theme;
            const nextTileUrl = nextTheme === 'light'
                ? 'https://mt1.google.com/vt/lyrs=m&hl=vi&x={x}&y={y}&z={z}'
                : 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png';
            
            map.removeLayer(activeTileLayer);
            activeTileLayer = L.tileLayer(nextTileUrl, {
                attribution: nextTheme === 'light' ? '&copy; Google Maps' : '&copy; OpenStreetMap &copy; CARTO',
                subdomains: 'abcd',
                maxZoom: 20
            }).addTo(map);
        });

        // 4. Định nghĩa hàm vẽ các địa điểm lên Bản đồ (Hỗ trợ gọi lại khi lọc AJAX)
        window.renderEateryMarkers = function(eateriesList) {
            // Xóa toàn bộ markers cũ trên bản đồ
            Object.values(markers).forEach(marker => {
                map.removeLayer(marker);
            });
            markers = {};

            eateriesList.forEach(function(eat) {
                if (eat.latitude && eat.longitude) {
                    // Biểu tượng Marker tùy chỉnh dựa trên danh mục để người dùng phân biệt trực quan
                    let categoryColor = '#0ea5e9'; // Mặc định xanh đại dương
                    const catSlug = eat.category ? (eat.category.slug || eat.category) : '';
                    
                    if (catSlug === 'dong-anh-food-map') categoryColor = '#ff3366';
                    else if (catSlug === 'stay-in-dong-anh') categoryColor = '#9d4edd';
                    else if (catSlug === 'wellness-care') categoryColor = '#20b2aa';
                    else if (catSlug === 'dong-anh-market') categoryColor = '#38b000';
                    else if (catSlug === 'smart-education-map') categoryColor = '#4361ee';
                    else if (catSlug === 'hanh-trinh-di-san') categoryColor = '#e63946';

                    const catIcon = eat.category ? (eat.category.icon || '📍') : '📍';
                    const catName = eat.category ? (eat.category.name || '') : '';
                    const communeName = eat.commune ? (eat.commune.name || eat.commune) : '';
                    const ratingVal = eat.average_rating || (eat.rating ? parseFloat(eat.rating).toFixed(1) : '5.0');

                    const customIcon = L.divIcon({
                        html: `<div style="background-color: ${categoryColor}; width: 28px; height: 28px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 10px rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">${catIcon}</div>`,
                        className: 'custom-leaflet-marker',
                        iconSize: [28, 28],
                        iconAnchor: [14, 14]
                    });

                    const signatureDishName = catSlug === 'dong-anh-food-map' ? 'Món ngon khám phá Đông Anh' : 
                                             (catSlug === 'dong-anh-market' ? 'Đặc sản OCOP vùng cố đô' : 
                                             (catSlug === 'wellness-care' ? 'Dịch vụ chăm sóc sức khỏe' : 
                                             (catSlug === 'smart-education-map' ? 'Học tập & Giáo dục thông minh' : 'Địa điểm nổi bật')));

                    // Nội dung popup hiển thị nhanh
                    const approvedVideos = eat.review_videos || eat.reviewVideos || [];
                    const hasVideo = approvedVideos.length > 0;
                    const videoBtn = hasVideo 
                        ? `<button onclick="openReelsModal('${eat.slug}', '${eat.name.replace(/'/g, "\\'")}', '${signatureDishName}', '${eat.image_path}')" class="btn-secondary" style="padding: 4px 10px; font-size: 0.75rem; border-radius: 6px; font-family: var(--font-heading); background: rgba(var(--primary-rgb), 0.08); border-color: rgba(var(--primary-rgb), 0.25); color: var(--primary); display: inline-flex; align-items: center; gap: 4px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='rgba(var(--primary-rgb), 0.15)'" onmouseout="this.style.background='rgba(var(--primary-rgb), 0.08)'">🎬 Video</button>`
                        : '';

                    const popupContent = `
                        <div class="map-popup-card">
                            <img src="${eat.image_path ? eat.image_path : 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=300&q=80'}" class="map-popup-img">
                            <h4 class="map-popup-title">${eat.name}</h4>
                            <p style="font-size: 0.8rem; color: var(--text-muted); margin: 2px 0;">📍 ${communeName}</p>
                            <div class="map-popup-footer">
                                <span class="rating-stars">⭐ ${ratingVal}</span>
                                <div style="display: flex; gap: 6px; align-items: center;">
                                    ${videoBtn}
                                    <a href="/dia-diem/${eat.slug}" class="btn-primary" style="padding: 4px 10px; font-size: 0.75rem; border-radius: 6px; font-family: var(--font-heading);">Xem chi tiết</a>
                                </div>
                            </div>
                        </div>
                    `;

                    const marker = L.marker([eat.latitude, eat.longitude], { icon: customIcon })
                        .bindPopup(popupContent)
                        .addTo(map);
                        
                    markers[eat.slug] = marker;
                }
            });
        };

        // Vẽ danh sách quán ăn ban đầu lên Bản đồ
        window.renderEateryMarkers(eateries);

        // 5. Logic tìm kiếm gõ real-time cực nhạy + Autocomplete gợi ý
        const searchInput = document.getElementById("searchInput");
        const suggestionDropdown = document.getElementById("suggestionDropdown");
        const cards = document.querySelectorAll('.split-list .eatery-card');
        const countSpan = document.getElementById("resultsCountSpan");
        
        // Hàm chuyển đổi tiếng Việt có dấu thành không dấu để tìm kiếm thông minh hơn
        const removeSign = (str) => {
            return str.normalize("NFD")
                      .replace(/[\u0300-\u036f]/g, "")
                      .replace(/đ/g, "d")
                      .replace(/Đ/g, "D");
        };

        function filterEateries(query) {
            query = query.trim().toLowerCase();
            const queryNoSign = removeSign(query);
            let matchCount = 0;

            cards.forEach(card => {
                const name = card.getAttribute('data-name') || '';
                const address = card.getAttribute('data-address') || '';
                const desc = card.getAttribute('data-desc') || '';
                const slug = card.getAttribute('data-slug') || '';
                
                const textToSearch = `${name} ${address} ${desc}`.toLowerCase();
                const textNoSign = removeSign(textToSearch);
                
                const isMatch = textToSearch.includes(query) || textNoSign.includes(queryNoSign);
                
                if (isMatch || query === '') {
                    card.style.setProperty('display', 'flex', 'important');
                    matchCount++;
                    if (markers[slug]) {
                        markers[slug].addTo(map);
                    }
                } else {
                    card.style.setProperty('display', 'none', 'important');
                    if (markers[slug]) {
                        map.removeLayer(markers[slug]);
                    }
                }
            });

            // Cập nhật số lượng kết quả hiển thị
            if (countSpan) {
                countSpan.innerText = `(${matchCount} kết quả)`;
            }

            // Hiển thị hoặc ẩn phần thông báo không tìm thấy kết quả
            let noResultDiv = document.getElementById('noResultsPlaceholder');
            if (matchCount === 0) {
                if (!noResultDiv) {
                    noResultDiv = document.createElement('div');
                    noResultDiv.id = 'noResultsPlaceholder';
                    noResultDiv.className = 'glass-panel';
                    noResultDiv.style.padding = '40px';
                    noResultDiv.style.textAlign = 'center';
                    noResultDiv.style.color = 'var(--text-muted)';
                    noResultDiv.style.width = '100%';
                    noResultDiv.innerHTML = `
                        <p style="font-size: 1.2rem; margin-bottom: 8px; color: var(--text-main);">😔 Không tìm thấy địa điểm nào phù hợp</p>
                        <p style="font-size: 0.9rem;">Hãy thử từ khóa khác hoặc xóa ô tìm kiếm để hiển thị lại toàn bộ!</p>
                        <button onclick="clearSearch()" class="btn-primary" style="margin-top: 16px; padding: 8px 16px; cursor: pointer;">Xem tất cả</button>
                    `;
                    document.querySelector('.split-list').appendChild(noResultDiv);
                } else {
                    noResultDiv.style.display = 'block';
                }
            } else {
                if (noResultDiv) {
                    noResultDiv.style.display = 'none';
                }
            }
        }

        window.clearSearch = function() {
            searchInput.value = '';
            suggestionDropdown.style.display = 'none';
            filterEateries('');
        };

        // Lọc real-time khi đang gõ
        searchInput.addEventListener("input", function() {
            const query = this.value;
            filterEateries(query);
            
            if (query.trim().length < 2) {
                suggestionDropdown.style.display = "none";
                return;
            }

            fetch(`/tim-kiem?q=${encodeURIComponent(query)}&ajax=suggest`)
                .then(res => res.json())
                .then(data => {
                    suggestionDropdown.innerHTML = "";
                    if (data.length === 0) {
                        suggestionDropdown.style.display = "none";
                        return;
                    }

                    data.forEach(item => {
                        const div = document.createElement("div");
                        div.className = "suggestion-item";
                        div.innerHTML = `
                            <div>
                                <div class="suggestion-title">${item.name}</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">📍 ${item.address}</div>
                            </div>
                            <span class="suggestion-type">Xem chi tiết ➔</span>
                        `;
                        div.onclick = function() {
                            window.location.href = `/dia-diem/${item.slug}`;
                        };
                        suggestionDropdown.appendChild(div);
                    });

                    suggestionDropdown.style.display = "block";
                });
        });

        // Chặn reload trang khi submit Form tìm kiếm trang chủ, thực hiện lọc ngay tại chỗ cực nhạy
        const searchForm = document.getElementById('searchForm');
        if (searchForm) {
            searchForm.addEventListener('submit', function(e) {
                e.preventDefault();
                filterEateries(searchInput.value);
                suggestionDropdown.style.display = "none";
            });
        }

        // Đóng dropdown khi click ngoài
        document.addEventListener("click", function(e) {
            if (e.target !== searchInput && e.target !== suggestionDropdown) {
                suggestionDropdown.style.display = "none";
            }
        });

        // 9. Lắng nghe click danh mục để lọc AJAX (Không reload trang, mượt mà kiểu SPA)
        const catCards = document.querySelectorAll('.category-card');
        catCards.forEach(card => {
            card.addEventListener('click', function(e) {
                if (this.getAttribute('target') === '_blank' || this.getAttribute('href') === '/checkin') {
                    return; // Let the link navigate normally!
                }
                e.preventDefault();
                
                const href = this.getAttribute('href');
                const urlParams = new URLSearchParams(href.split('?')[1] || '');
                const slug = urlParams.get('cat') || '';
                
                // Đánh dấu nút đang chọn
                catCards.forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                
                // Tự động cuộn thẻ được click vào chính giữa thanh trượt ngang
                centerActiveCategoryCard(this);
                
                // Gọi bộ lọc AJAX
                if (window.filterCategoryAjax) {
                    window.filterCategoryAjax(slug, href);
                }
            });
        });

        // Tự động cuộn thẻ danh mục đang active vào chính giữa khi nạp trang lần đầu
        const activeCard = document.querySelector('.category-card.active');
        if (activeCard) {
            setTimeout(() => {
                centerActiveCategoryCard(activeCard);
            }, 300);
        }

        // 7. Tự động cuộn xuống danh sách quán ăn khi người dùng lọc theo Danh mục trên Mobile
        <?php if(request()->has('cat')): ?>
        setTimeout(() => {
            if (window.innerWidth <= 768) {
                const splitList = document.querySelector('.split-list');
                if (splitList) {
                    window.scrollTo({
                        top: splitList.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            }
        }, 500);
        <?php endif; ?>

    });

    // 6. Hàm đồng bộ click card bên trái -> di chuyển camera map qua phải và mở popup marker tương ứng
    function focusOnEatery(lat, lng, slug) {
        if (map && markers[slug]) {
            let targetLat = lat;
            // Trên mobile, dịch chuyển tâm bản đồ lên phía bắc (lat + offset) để đẩy marker xuống phía dưới,
            // giúp phần popup hiển thị trọn vẹn trong khung bản đồ nhỏ (320px) không bị che khuất ở cạnh trên.
            if (window.innerWidth <= 768) {
                targetLat = lat + 0.0018;
            }

            map.flyTo([targetLat, lng], 16, {
                animate: true,
                duration: 1.2
            });
            setTimeout(() => {
                markers[slug].openPopup();
            }, 1000);
            
            // Cuộn màn hình lên vị trí bản đồ trên mobile chuẩn xác ngay dưới thanh Header sticky (64px)
            if (window.innerWidth <= 992) {
                const mapContainer = document.querySelector('.split-map-container');
                if (mapContainer) {
                    window.scrollTo({
                        top: mapContainer.offsetTop - 64,
                        behavior: 'smooth'
                    });
                }
            }
        }
    }



    // 10. Logic lọc danh mục qua AJAX mượt mà (SPA style, không reload trang!)
    window.filterCategoryAjax = function(slug, href) {
        const eateriesContainer = document.getElementById('eateriesListContainer');
        const headerContainer = document.getElementById('listHeaderContainer');
        
        if (!eateriesContainer) return;
        
        // Thêm hiệu ứng mờ mượt khi tải
        eateriesContainer.style.opacity = '0.4';
        eateriesContainer.style.transition = 'opacity 0.2s ease';
        
        // Tạo URL request API
        const ajaxUrl = href + (href.includes('?') ? '&' : '?') + 'ajax=1';
        
        fetch(ajaxUrl)
            .then(res => res.json())
            .then(data => {
                // Cập nhật URL trình duyệt (không reload trang)
                history.pushState(null, '', href);
                
                // Vẽ lại markers trên bản đồ
                if (window.renderEateryMarkers) {
                    window.renderEateryMarkers(data.eateries);
                }
                
                // Cập nhật tiêu đề header của danh sách
                if (headerContainer) {
                    if (slug === 'dong-anh-market') {
                        headerContainer.innerHTML = `
                            <div style="margin-bottom: 20px; border-bottom: 1.5px dashed rgba(212, 175, 55, 0.3); padding-bottom: 16px;">
                                <span class="heritage-badge" style="margin-bottom: 8px; font-size: 0.7rem; font-weight: 800; letter-spacing: 1.5px; border: 1px solid rgba(212, 175, 55, 0.4); background: rgba(212, 175, 55, 0.1); color: #ffb300; padding: 4px 10px; border-radius: 20px; display: inline-block;">🛍️ CHỢ TRUYỀN THỐNG & ĐẶC SẢN OCOP</span>
                                <h2 style="font-size: 1.6rem; font-family: var(--font-heading); font-weight: 800; margin: 4px 0 6px 0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                                    <span style="background: linear-gradient(135deg, #d97706 0%, #b45309 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Không Gian Chợ & Đặc Sản Đông Anh</span>
                                    <span id="resultsCountSpan" style="font-size: 0.85rem; color: var(--text-muted); font-weight: normal;">
                                        (${data.eateries.length} địa điểm)
                                    </span>
                                </h2>
                                <p style="font-size: 0.88rem; color: var(--text-muted); line-height: 1.5; margin: 0;">
                                    Khám phá các sản phẩm OCOP đặc trưng, quà lưu niệm độc đáo, nông sản sạch cùng hệ thống các siêu thị, chợ truyền thống nhộn nhịp mang đậm hồn quê Đông Anh.
                                </p>
                            </div>
                        `;
                    } else {
                        const activeCard = document.querySelector('.category-card.active');
                        let titleText = 'Địa điểm nổi bật <span style="font-size: 0.9rem; color: var(--text-muted); font-weight: 500; font-style: italic;">(Featured Places)</span>';
                        if (slug && activeCard) {
                            const titleEn = activeCard.querySelector('.cat-title-en') ? activeCard.querySelector('.cat-title-en').innerText : '';
                            const titleVi = activeCard.querySelector('.cat-title-vi') ? activeCard.querySelector('.cat-title-vi').innerText : '';
                            titleText = `${titleVi} <span style="font-size: 0.9rem; color: var(--text-muted); font-weight: 500; font-style: italic;">(${titleEn})</span>`;
                        }
                        
                        headerContainer.innerHTML = `
                            <h2 style="font-size: 1.25rem; margin: 6px 0 0 0; font-family: var(--font-heading); font-weight: 700; line-height: 1.4; color: var(--text-main);">
                                <span style="margin-right: 4px;">📍</span> 
                                ${titleText}
                                <span id="resultsCountSpan" style="font-size: 0.8rem; color: var(--text-muted); font-weight: normal; margin-left: 6px; display: inline-block; white-space: nowrap;">
                                    (${data.eateries.length} địa điểm / places)
                                </span>
                            </h2>
                        `;
                    }
                }
                
                // Re-render danh sách quán ăn
                if (data.eateries.length > 0) {
                    let cardsHtml = '';
                    data.eateries.forEach(eat => {
                        const imgUrl = eat.image_path ? eat.image_path : 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=300&q=80';
                        const ratingVal = eat.average_rating || (eat.rating ? parseFloat(eat.rating).toFixed(1) : '5.0');
                        const communeName = eat.commune ? (eat.commune.name || eat.commune) : '';
                        const categoryIcon = eat.category ? (eat.category.icon || '') : '';
                        const categoryName = eat.category ? (eat.category.name || '') : '';
                        const categorySlug = eat.category ? (eat.category.slug || '') : '';

                        cardsHtml += `
                            <div class="eatery-card glass-panel revealed hover-lift" 
                                 data-slug="${eat.slug}"
                                 data-name="${eat.name}"
                                 data-address="${eat.address}"
                                 data-desc="${eat.description && eat.description !== 'null' ? eat.description : ''}"
                                 data-commune="${communeName}"
                                 data-category="${categorySlug}"
                                 style="animation: fadeIn 0.4s ease forwards;"
                                 onclick="focusOnEatery(${eat.latitude}, ${eat.longitude}, '${eat.slug}')">
                                <div class="eatery-img-wrapper hover-zoom-container">
                                    <img src="${imgUrl}" class="eatery-img hover-zoom-img" alt="${eat.name}">
                                    <div style="position: absolute; top: 8px; left: 8px; max-width: calc(100% - 16px); display: flex; align-items: center; gap: 4px; font-size: 0.68rem; font-weight: 700; color: #ffffff; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(4px); padding: 4px 8px; border-radius: 6px; box-shadow: 0 2px 10px rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1);">
                                        <span>${categoryIcon}</span>
                                        <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${categoryName}</span>
                                    </div>
                                </div>
                                <div class="eatery-info">
                                    <div class="eatery-header">
                                        <h3 class="eatery-title">${eat.name}</h3>
                                        <div class="rating-stars">
                                            <span>⭐</span> ${ratingVal}
                                        </div>
                                    </div>
                                    ${eat.description && eat.description !== 'null' ? `<p class="eatery-desc">${eat.description}</p>` : ''}
                                    <div class="eatery-footer">
                                        <div class="eatery-meta-item">
                                            <span>📍</span> ${communeName}
                                        </div>
                                        ${!['smart-education-map', 'hanh-trinh-di-san', 'discover-dong-anh-community-culture-hub'].includes(categorySlug) ? `
                                        <div class="eatery-meta-item" style="color: var(--primary); font-weight: 600;">
                                            ${eat.price_range}
                                        </div>
                                        ` : ''}
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    eateriesContainer.innerHTML = cardsHtml;
                } else {
                    eateriesContainer.innerHTML = `
                        <div class="glass-panel" style="padding: 40px; text-align: center; color: var(--text-muted); width: 100%;">
                            <p style="font-size: 1.2rem; margin-bottom: 8px; color: var(--text-main);">😔 Không tìm thấy địa điểm nào phù hợp</p>
                            <p style="font-size: 0.9rem;">Hãy thử lọc danh mục khác hoặc xóa bộ lọc để khám phá lại toàn bộ Đông Anh!</p>
                            <a href="/" class="btn-primary" style="margin-top: 16px; padding: 8px 16px; text-decoration: none; display: inline-block;">Xem tất cả</a>
                        </div>
                    `;
                    // Tự động gắn sự kiện click cho nút "Xem tất cả" vừa tạo mới qua ajax
                    const viewAllBtn = eateriesContainer.querySelector('a');
                    if (viewAllBtn) {
                        viewAllBtn.addEventListener('click', function(evt) {
                            evt.preventDefault();
                            const allCatCard = document.querySelector('.category-card[href="/"]');
                            if (allCatCard) allCatCard.click();
                        });
                    }
                }
                
                // Mở lại độ mờ
                eateriesContainer.style.opacity = '1';
                
                // Tự động cuộn xuống danh sách quán ăn trên di động
                if (window.innerWidth <= 768) {
                    const splitList = document.querySelector('.split-list');
                    if (splitList) {
                        window.scrollTo({
                            top: splitList.offsetTop - 80,
                            behavior: 'smooth'
                        });
                    }
                }
            })
            .catch(err => {
                console.error("AJAX loading error:", err);
                eateriesContainer.style.opacity = '1';
            });
    };

    // ==========================================================================
    // DYNAMIC TIKTOK REELS-STYLE PLAYER FOR TÓP TÓP FOOD TOUR
    // ==========================================================================
    let reelsList = [];
    // 7. Immersive TikTok Reels Video Player
    let currentLikeCount = 3800;
    let isLiked = false;
    let currentEateryReels = [];
    let currentReelIndex = 0;
    let currentEateryName = '';
    let currentSpecialtyName = '';

    // Helper functions to extract video IDs
    function getTikTokVideoId(url) {
        // Match any sequence of 15 to 22 digits which represents a TikTok video ID
        const matches = url.match(/\d{15,22}/);
        if (matches && matches[0]) return matches[0];
        return null;
    }

    function getYouTubeShortsId(url) {
        const regExp = /^.*(shorts\/|youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
        const match = url.match(regExp);
        return (match && match[1] && match[2] && match[2].length === 11) ? match[2] : (match && match[1] && match[1].length === 11 ? match[1] : null);
    }

    window.openReelsModal = function(eaterySlug, eateryName, specialtyName, imagePath) {
        currentEateryName = eateryName;
        currentSpecialtyName = specialtyName;
        
        // Find eatery and its approved videos
        const eat = eateries.find(e => e.slug === eaterySlug);
        let approvedVideos = [];
        if (eat && eat.review_videos) {
            approvedVideos = eat.review_videos;
        }
        
        if (approvedVideos.length === 0) {
            // Fallback mock video if no specific video is linked
            currentEateryReels = [{
                video_url: 'https://assets.mixkit.co/videos/preview/mixkit-chef-preparing-a-fresh-vegetable-salad-32860-large.mp4',
                video_type: 'local'
            }];
        } else {
            currentEateryReels = approvedVideos;
        }
        
        currentReelIndex = 0;
        document.getElementById('reelsModal').style.display = 'flex';
        
        playReelAtIndex(currentReelIndex);
    };

    function playReelAtIndex(index) {
        if (index < 0 || index >= currentEateryReels.length) return;
        
        const reel = currentEateryReels[index];
        document.getElementById('reelsEateryName').innerText = currentEateryName;
        
        // Dynamic description including video count
        const videoIndicator = currentEateryReels.length > 1 ? `[Video ${index + 1}/${currentEateryReels.length}] ` : '';
        document.getElementById('reelsVideoDesc').innerText = `${videoIndicator}Khám phá món ngon tại "${currentEateryName}". Đặc sản "${currentSpecialtyName}" đang làm nức lòng thực khách gần xa bởi hương vị đậm chất truyền thống Đông Anh!`;
        document.querySelector('.reels-signature-tag').innerText = `🌟 Món đặc trưng: ${currentSpecialtyName}`;
        
        // Update inner navigation arrows visibility (Universal & Mobile helper)
        const mPrevBtn = document.getElementById('modalPrevReelBtn');
        const mNextBtn = document.getElementById('modalNextReelBtn');
        if (mPrevBtn && mNextBtn) {
            mPrevBtn.style.display = index > 0 ? 'flex' : 'none';
            mNextBtn.style.display = index < currentEateryReels.length - 1 ? 'flex' : 'none';
        }

        // Update Instagram-style top progress bars
        const progressContainer = document.getElementById('reelsProgressBars');
        if (progressContainer) {
            progressContainer.innerHTML = '';
            if (currentEateryReels.length > 1) {
                for (let i = 0; i < currentEateryReels.length; i++) {
                    const bar = document.createElement('div');
                    bar.style.flex = '1';
                    bar.style.height = '3px';
                    bar.style.borderRadius = '2px';
                    bar.style.background = i === index ? '#ffffff' : 'rgba(255,255,255,0.35)';
                    bar.style.transition = 'background 0.3s ease';
                    progressContainer.appendChild(bar);
                }
            }
        }
        
        const wrapper = document.getElementById('reelPlayerWrapper');
        wrapper.innerHTML = ''; // Clear previous player
        
        let videoUrl = reel.video_url;
        let videoType = reel.video_type;

        // Dynamically hide/show the right side action panel for YouTube videos
        const sideActions = document.querySelector('.reels-side-actions');
        const tapOverlay = document.getElementById('reelTapOverlay');
        const isIframe = videoType === 'youtube_shorts' || videoType === 'tiktok' || videoUrl.includes('youtube.com') || videoUrl.includes('youtu.be');
        
        if (sideActions) {
            if (isIframe) {
                sideActions.style.display = 'none';
            } else {
                sideActions.style.display = 'flex';
            }
        }

        // Allow touch, click, zoom gestures to go directly to YouTube/TikTok player by setting pointer-events: none
        if (tapOverlay) {
            if (isIframe) {
                tapOverlay.style.pointerEvents = 'none';
            } else {
                tapOverlay.style.pointerEvents = 'auto';
            }
        }

        if (videoType === 'tiktok') {
            const videoId = getTikTokVideoId(videoUrl);
            if (videoId) {
                wrapper.innerHTML = `<iframe src="https://www.tiktok.com/embed/v2/${videoId}" style="width: 100%; height: 100%; border: none; background: #000; pointer-events: auto;" allowfullscreen allow="autoplay; encrypted-media;"></iframe>`;
            } else {
                wrapper.innerHTML = `<iframe src="${videoUrl}" style="width: 100%; height: 100%; border: none; background: #000; pointer-events: auto;" allowfullscreen allow="autoplay;"></iframe>`;
            }
        } else if (videoType === 'youtube_shorts' || videoUrl.includes('youtube.com') || videoUrl.includes('youtu.be')) {
            const shortsId = getYouTubeShortsId(videoUrl);
            if (shortsId) {
                wrapper.innerHTML = `<iframe src="https://www.youtube.com/embed/${shortsId}?autoplay=1" style="width: 100%; height: 100%; border: none; background: #000; pointer-events: auto;" allowfullscreen allow="autoplay; encrypted-media;"></iframe>`;
            } else {
                wrapper.innerHTML = `<iframe src="${videoUrl}" style="width: 100%; height: 100%; border: none; background: #000; pointer-events: auto;" allowfullscreen allow="autoplay;"></iframe>`;
            }
        } else {
            // Local direct mp4 storage file
            wrapper.innerHTML = `<video src="${videoUrl}" autoplay loop muted playsinline controls style="width: 100%; height: 100%; object-fit: cover; background: #000; pointer-events: auto;"></video>`;
        }
    }

    window.searchPrevReel = function() {
        if (currentReelIndex > 0) {
            currentReelIndex--;
            playReelAtIndex(currentReelIndex);
        }
    };

    window.searchNextReel = function() {
        if (currentReelIndex < currentEateryReels.length - 1) {
            currentReelIndex++;
            playReelAtIndex(currentReelIndex);
        }
    };

    window.closeReelsModal = function() {
        document.getElementById('reelPlayerWrapper').innerHTML = ''; // Clear player
        document.getElementById('reelsModal').style.display = 'none';
    };

    window.toggleReelsLike = function() {
        const likeBtn = document.getElementById('reelsLikeBtn');
        const countSpan = document.getElementById('reelsLikeCount');
        
        if (isLiked) {
            isLiked = false;
            currentLikeCount--;
            likeBtn.querySelector('span').style.color = '#fff';
        } else {
            isLiked = true;
            currentLikeCount++;
            likeBtn.querySelector('span').style.color = '#ff3366';
            
            // Tim bay từ tâm
            spawnSingleHeart(window.innerWidth / 2, window.innerHeight / 2);
        }
        countSpan.innerText = (currentLikeCount / 1000).toFixed(1) + 'K';
    };

    window.triggerDoubleTapHeart = function(event) {
        spawnSingleHeart(event.clientX, event.clientY);
        if (!isLiked) {
            toggleReelsLike();
        }
    };

    function spawnSingleHeart(x, y) {
        const container = document.getElementById('reelsModal');
        const heart = document.createElement('div');
        heart.className = 'floating-heart';
        heart.innerHTML = '❤️';
        heart.style.left = x + 'px';
        heart.style.top = y + 'px';
        
        const dx = (Math.random() * 120 - 60) + 'px';
        const rot = (Math.random() * 70 - 35) + 'deg';
        heart.style.setProperty('--dx', dx);
        heart.style.setProperty('--rot', rot);
        
        container.appendChild(heart);
        setTimeout(() => heart.remove(), 1000);
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/mnt/Downloads/FOOD_MAP/resources/views/home.blade.php ENDPATH**/ ?>