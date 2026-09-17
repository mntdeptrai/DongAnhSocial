
<style>
/* Custom styles for the brand-new DongAnh Discovery Banner */
.custom-hero-banner {
    position: relative;
    overflow: hidden !important; /* Prevents 3D clouds, airplane keyframes, and sky elements from expanding screen width on mobile */
    padding: 70px 0 110px;
    background: linear-gradient(180deg, #7dd3fc 0%, #38bdf8 45%, #0ea5e9 85%, #0284c7 100%);
    min-height: 560px;
    contain: layout style; /* CSS Containment for max render performance */
}

@media (max-width: 768px) {
    .custom-hero-banner {
        min-height: auto;
        padding: 40px 0 60px;
    }
    .hero-airplane-container, .jet-contrail-container, .heart-contrail-sky-wrap {
        display: none !important;
    }
}

@media (prefers-reduced-motion: reduce) {
    .custom-hero-banner * {
        animation: none !important;
    }
}

.custom-hero-banner.hero-paused * {
    animation-play-state: paused !important;
}

/* ✈️ REAL PHOTOGRAPHIC PASSENGER AIRLINER & JET CONTRAIL TRAIL */
.hero-airplane-container {
    position: absolute;
    top: 30px;
    left: -320px;
    z-index: 10;
    pointer-events: none;
    will-change: transform;
    animation: flightPath3D 24s cubic-bezier(0.4, 0, 0.2, 1) infinite;
    backface-visibility: hidden;
    -webkit-backface-visibility: hidden;
    transform: translateZ(0);
}

.hero-airplane-photo-wrap {
    position: relative;
    width: 260px;
    height: auto;
    animation: airplaneBank3D 4.5s ease-in-out infinite alternate;
    backface-visibility: hidden;
}

.real-airplane-photo-img {
    width: 100%;
    height: auto;
    display: block;
    filter: drop-shadow(0 22px 30px rgba(15, 23, 42, 0.38));
    user-select: none;
    pointer-events: none;
}

/* Contrail Jet Smoke Trail behind airplane */
.jet-contrail-container {
    position: absolute;
    top: 48px;
    right: 210px;
    display: flex;
    align-items: center;
    pointer-events: none;
}

.jet-contrail-line {
    width: 280px;
    height: 6px;
    background: linear-gradient(90deg, transparent 0%, rgba(255, 255, 255, 0.2) 20%, rgba(255, 255, 255, 0.7) 60%, rgba(255, 255, 255, 0.98) 100%);
    border-radius: 6px;
    filter: blur(1.8px);
    box-shadow: 0 0 8px rgba(255, 255, 255, 0.6);
}

.jet-contrail-puff {
    position: absolute;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.95) 0%, rgba(240, 249, 255, 0.6) 40%, rgba(224, 242, 254, 0.2) 70%, transparent 100%);
    border-radius: 50% !important;
    animation: puffExpand 2.4s ease-out infinite;
    filter: blur(2px);
}

/* 💖 Heart Smoke Contrail Sky Overlay */
.heart-contrail-sky-wrap {
    position: absolute;
    top: 10px;
    left: 50%;
    transform: translateX(-50%);
    width: 92%;
    max-width: 900px;
    height: 320px;
    pointer-events: none;
    z-index: 5;
}

.heart-smoke-path {
    stroke-dasharray: 1200;
    stroke-dashoffset: 1200;
    animation: drawHeartSmoke 22s cubic-bezier(0.4, 0, 0.2, 1) infinite;
}

@keyframes drawHeartSmoke {
    0% { stroke-dashoffset: 1200; opacity: 0; }
    12% { stroke-dashoffset: 1200; opacity: 0.95; }
    70% { stroke-dashoffset: 0; opacity: 0.95; }
    88% { opacity: 0.8; }
    100% { stroke-dashoffset: 0; opacity: 0; }
}

@keyframes flightPath3D {
    0% {
        transform: translate3d(-320px, 110px, 30px) rotateX(6deg) rotateY(10deg) rotateZ(6deg) scale(0.72);
        opacity: 0;
    }
    10% {
        transform: translate3d(12vw, 75px, 70px) rotateX(4deg) rotateY(8deg) rotateZ(5deg) scale(0.85);
        opacity: 1;
    }
    35% {
        transform: translate3d(36vw, 30px, 110px) rotateX(1deg) rotateY(4deg) rotateZ(2deg) scale(1.0);
    }
    60% {
        transform: translate3d(62vw, 45px, 120px) rotateX(-2deg) rotateY(3deg) rotateZ(-2deg) scale(1.06);
    }
    82% {
        transform: translate3d(85vw, 15px, 70px) rotateX(3deg) rotateY(6deg) rotateZ(4deg) scale(0.88);
        opacity: 1;
    }
    100% {
        transform: translate3d(calc(100vw + 320px), -30px, 10px) rotateX(6deg) rotateY(8deg) rotateZ(6deg) scale(0.7);
        opacity: 0;
    }
}

@keyframes airplaneBank3D {
    0% { transform: rotateX(0deg) rotateZ(0deg) translateY(0px); }
    50% { transform: rotateX(3deg) rotateZ(2.5deg) translateY(-6px); }
    100% { transform: rotateX(-2deg) rotateZ(-2deg) translateY(4px); }
}

@keyframes puffExpand {
    0% { transform: scale(0.4); opacity: 0.9; }
    100% { transform: scale(3.5); opacity: 0; }
}

/* ☁️ REAL PHOTOGRAPHIC CLOUDS WITH FLUFFY ATMOSPHERIC DEPTH */
.cloud-3d-wrap {
    position: absolute;
    pointer-events: none;
    will-change: transform;
    transform-style: preserve-3d;
}

.real-cloud-center-img {
    width: 560px;
    max-width: 90vw;
    height: auto;
    display: block;
    filter: drop-shadow(0 15px 35px rgba(2, 132, 199, 0.25));
    opacity: 0.92;
    animation: cloudFloat3D 8s ease-in-out infinite alternate;
    user-select: none;
    pointer-events: none;
}

.real-cloud-backdrop-sub-img {
    position: absolute;
    top: 30px;
    left: 40px;
    width: 460px;
    opacity: 0.7;
    filter: blur(1px);
    animation: cloudFloat3D 10s ease-in-out infinite alternate-reverse;
    user-select: none;
    pointer-events: none;
}

.real-cloud-layer-img {
    display: block;
    height: auto;
    filter: drop-shadow(0 12px 25px rgba(2, 132, 199, 0.22));
    user-select: none;
    pointer-events: none;
}

@keyframes cloudFloat3D {
    0% { transform: translateY(0px) scale(1); }
    100% { transform: translateY(-8px) scale(1.02); }
}

/* Central Realistic Cloud Bank */
.cloud-center-backdrop {
    position: absolute;
    top: 5px;
    left: 50%;
    transform: translateX(-50%);
    width: 560px;
    max-width: 90vw;
    height: auto;
    z-index: 4;
    pointer-events: none;
}

/* Clouds Layer 1 - Foreground Fast & Large */
.cloud-layer-1 {
    top: 5%;
    left: -280px;
    z-index: 8;
    animation: driftCloud1 34s linear infinite;
}

/* Clouds Layer 2 - Midground Soft */
.cloud-layer-2 {
    top: 28%;
    left: -220px;
    z-index: 3;
    animation: driftCloud2 48s linear infinite;
    animation-delay: 9s;
}

/* Clouds Layer 3 - Background Slow Misty */
.cloud-layer-3 {
    top: 2%;
    left: -300px;
    z-index: 1;
    opacity: 0.75;
    animation: driftCloud3 68s linear infinite;
    animation-delay: 19s;
}

/* Cloud Layer 4 - Mid Right Floating */
.cloud-layer-4 {
    top: 18%;
    right: -260px;
    z-index: 4;
    animation: driftCloud4 44s linear infinite;
    animation-delay: 6s;
}

@keyframes driftCloud1 {
    0% { transform: translate3d(0, 0, 50px) scale(1.1); }
    100% { transform: translate3d(calc(100vw + 360px), 0, 50px) scale(1.1); }
}

@keyframes driftCloud2 {
    0% { transform: translate3d(0, 0, 10px) scale(0.92); }
    100% { transform: translate3d(calc(100vw + 300px), 0, 10px) scale(0.92); }
}

@keyframes driftCloud3 {
    0% { transform: translate3d(0, 0, -60px) scale(0.78); }
    100% { transform: translate3d(calc(100vw + 300px), 0, -60px) scale(0.78); }
}

@keyframes driftCloud4 {
    0% { transform: translate3d(0, 0, 20px) scale(0.88); }
    100% { transform: translate3d(calc(-100vw - 360px), 0, 20px) scale(0.88); }
}

@keyframes cloudFloat3D {
    0% { transform: translateY(0px) rotateX(0deg); }
    100% { transform: translateY(-16px) rotateX(4deg); }
}

/* 🎈 3D HOT AIR BALLOON IN BACKGROUND */
.hot-air-balloon-3d {

    position: absolute;
    top: 35px;
    right: 16%;
    z-index: 2;
    pointer-events: none;
    will-change: transform;
    animation: balloonSway3D 9s ease-in-out infinite alternate;
    filter: drop-shadow(0 15px 22px rgba(15, 23, 42, 0.18));
}

@keyframes balloonSway3D {
    0% { transform: translate3d(0, 0, -30px) rotate(-3deg); }
    50% { transform: translate3d(18px, -22px, -20px) rotate(2deg); }
    100% { transform: translate3d(-12px, -38px, -35px) rotate(-2deg); }
}

/* Bubbles rising - 100% Perfect 3D Spheres */
.bubble-particle {
    position: absolute;
    background: radial-gradient(circle at 35% 35%, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.45) 35%, rgba(255, 255, 255, 0.12) 70%);
    border: 1.5px solid rgba(255, 255, 255, 0.7);
    box-shadow: inset -2px -2px 6px rgba(255, 255, 255, 0.6), 0 6px 12px rgba(0, 0, 0, 0.1);
    border-radius: 50% !important;
    aspect-ratio: 1 / 1 !important;
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
    font-family: var(--font-heading);
    font-size: 3.8rem;
    font-weight: 900;
    line-height: 1.0;
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

.logo-title span {
    display: inline-block;
    padding-bottom: 8px;
}

.slogan-group {
    position: relative;
}

.slogan-text {
    font-family: var(--font-heading);
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
    font-family: var(--font-heading);
    font-size: 0.72rem;
    font-weight: 600;
    opacity: 0.9;
    letter-spacing: 0.3px;
    line-height: 1.1;
}

.quick-subtitle {
    font-family: var(--font-heading);
    font-size: 0.82rem;
    font-weight: 800;
    letter-spacing: 0.3px;
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
    font-family: var(--font-body);
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
    font-family: var(--font-heading);
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

/* ==========================================================================
   CHỢ TRUYỀN THỐNG / CHỢ SỐ MASTER CSS OVERRIDES
   ========================================================================== */
.traditional-market-hero-box {
    background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 50%, #f0f9ff 100%) !important;
    border: 1.5px solid #7dd3fc !important;
    border-radius: 22px !important;
    padding: 24px 28px !important;
    margin-bottom: 24px !important;
    box-shadow: 0 12px 30px -8px rgba(14, 165, 233, 0.18) !important;
    position: relative !important;
    overflow: hidden !important;
    transition: all 0.35s ease !important;
}

.traditional-market-hero-box::before {
    content: '';
    position: absolute;
    top: -40%;
    right: -15%;
    width: 220px;
    height: 220px;
    background: radial-gradient(circle, rgba(14, 165, 233, 0.15) 0%, rgba(255, 255, 255, 0) 70%);
    border-radius: 50%;
    pointer-events: none;
}

.market-badge-chip {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    font-size: 0.78rem !important;
    font-weight: 800 !important;
    letter-spacing: 0.5px !important;
    text-transform: uppercase !important;
    color: #0284c7 !important;
    background: #ffffff !important;
    border: 1.5px solid #7dd3fc !important;
    padding: 6px 14px !important;
    border-radius: 30px !important;
    box-shadow: 0 2px 8px rgba(14, 165, 233, 0.1) !important;
}

.market-hero-title {
    font-family: var(--font-heading) !important;
    font-size: 1.8rem !important;
    font-weight: 900 !important;
    line-height: 1.25 !important;
    margin: 12px 0 8px 0 !important;
    color: #0f172a !important;
}

.market-stats-pills {
    display: flex !important;
    flex-wrap: wrap !important;
    gap: 10px !important;
    margin-top: 16px !important;
}

.market-stat-pill {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    padding: 7px 14px !important;
    background: #ffffff !important;
    border: 1.5px solid #7dd3fc !important;
    border-radius: 12px !important;
    font-size: 0.82rem !important;
    font-weight: 700 !important;
    color: #0369a1 !important;
    box-shadow: 0 2px 6px rgba(14, 165, 233, 0.08) !important;
    transition: all 0.2s ease !important;
}

.market-stat-pill:hover {
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 10px rgba(14, 165, 233, 0.15) !important;
}

.market-stat-pill span.icon {
    font-size: 1rem !important;
}

.eatery-card.market-card-highlight {
    border: 1.5px solid #e2e8f0 !important;
    background: #ffffff !important;
    border-radius: 20px !important;
    padding: 18px !important;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04) !important;
    transition: all 0.3s cubic-bezier(0.32, 0.72, 0, 1) !important;
    position: relative !important;
    overflow: hidden !important;
}

.eatery-card.market-card-highlight:hover {
    transform: translateY(-4px) !important;
    border-color: #38bdf8 !important;
    box-shadow: 0 12px 28px rgba(14, 165, 233, 0.18) !important;
}

.market-title-badge {
    display: inline-flex !important;
    align-items: center !important;
    gap: 4px !important;
    font-size: 0.62rem !important;
    font-weight: 700 !important;
    letter-spacing: 0.4px !important;
    text-transform: uppercase !important;
    color: #0284c7 !important;
    background: rgba(14, 165, 233, 0.08) !important;
    border: 1px solid rgba(14, 165, 233, 0.25) !important;
    padding: 2px 7px !important;
    border-radius: 10px !important;
    box-shadow: none !important;
}

.eatery-card.market-card-highlight .eatery-title {
    font-size: 1.35rem !important;
    font-weight: 900 !important;
    color: #0f172a !important;
    letter-spacing: -0.3px !important;
}

.market-explore-btn {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 8px !important;
    width: 100% !important;
    margin-top: 14px !important;
    padding: 12px 20px !important;
    background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important;
    color: #ffffff !important;
    font-weight: 800 !important;
    font-size: 0.9rem !important;
    border-radius: 12px !important;
    border: none !important;
    box-shadow: 0 4px 14px rgba(2, 132, 199, 0.3) !important;
    transition: all 0.25s ease !important;
    cursor: pointer !important;
    text-decoration: none !important;
}

.market-explore-btn:hover {
    background: linear-gradient(135deg, #0369a1 0%, #075985 100%) !important;
    box-shadow: 0 6px 20px rgba(2, 132, 199, 0.45) !important;
    transform: translateY(-2px) !important;
}

/* ==========================================================================
   ẨM THỰC ĐÔNG ANH / FOOD & RESTAURANTS MASTER CSS
   ========================================================================== */
.food-hero-box {
    background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 50%, #fef3c7 100%) !important;
    border: 1.5px solid #fdba74 !important;
    border-radius: 22px !important;
    padding: 24px 28px !important;
    margin-bottom: 24px !important;
    box-shadow: 0 12px 30px -8px rgba(249, 115, 22, 0.18) !important;
    position: relative !important;
    overflow: hidden !important;
    transition: all 0.35s ease !important;
}

.food-hero-box::before {
    content: '' !important;
    position: absolute !important;
    top: -40% !important;
    right: -15% !important;
    width: 220px !important;
    height: 220px !important;
    background: radial-gradient(circle, rgba(249, 115, 22, 0.15) 0%, rgba(255, 255, 255, 0) 70%) !important;
    border-radius: 50% !important;
    pointer-events: none !important;
}

.food-badge-chip {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    font-size: 0.78rem !important;
    font-weight: 800 !important;
    letter-spacing: 0.5px !important;
    text-transform: uppercase !important;
    color: #ea580c !important;
    background: #ffffff !important;
    border: 1.5px solid #fdba74 !important;
    padding: 6px 14px !important;
    border-radius: 30px !important;
    box-shadow: 0 2px 8px rgba(249, 115, 22, 0.1) !important;
}

.food-hero-title {
    font-family: var(--font-heading) !important;
    font-size: 1.8rem !important;
    font-weight: 900 !important;
    line-height: 1.25 !important;
    margin: 12px 0 8px 0 !important;
    color: #431407 !important;
}

.food-stats-pills {
    display: flex !important;
    flex-wrap: wrap !important;
    gap: 10px !important;
    margin-top: 16px !important;
}

.food-stat-pill {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    padding: 7px 14px !important;
    background: #ffffff !important;
    border: 1.5px solid #fdba74 !important;
    border-radius: 12px !important;
    font-size: 0.82rem !important;
    font-weight: 700 !important;
    color: #c2410c !important;
    box-shadow: 0 2px 6px rgba(249, 115, 22, 0.08) !important;
    transition: all 0.2s ease !important;
}

.food-stat-pill:hover {
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 10px rgba(249, 115, 22, 0.15) !important;
}

.food-stat-pill span.icon {
    font-size: 1rem !important;
}

.eatery-card.food-card-highlight {
    border: 1.5px solid #fed7aa !important;
    background: #ffffff !important;
    border-radius: 20px !important;
    padding: 18px !important;
    box-shadow: 0 4px 18px rgba(249, 115, 22, 0.06) !important;
    transition: all 0.3s cubic-bezier(0.32, 0.72, 0, 1) !important;
    position: relative !important;
    overflow: hidden !important;
}

.eatery-card.food-card-highlight:hover {
    transform: translateY(-4px) !important;
    border-color: #f97316 !important;
    box-shadow: 0 12px 28px rgba(249, 115, 22, 0.2) !important;
}

.food-title-badge {
    display: inline-flex !important;
    align-items: center !important;
    gap: 4px !important;
    font-size: 0.62rem !important;
    font-weight: 700 !important;
    letter-spacing: 0.4px !important;
    text-transform: uppercase !important;
    color: #ea580c !important;
    background: rgba(249, 115, 22, 0.08) !important;
    border: 1px solid rgba(249, 115, 22, 0.25) !important;
    padding: 2px 7px !important;
    border-radius: 10px !important;
    box-shadow: none !important;
}

.eatery-card.food-card-highlight .eatery-title {
    font-size: 1.35rem !important;
    font-weight: 900 !important;
    color: #0f172a !important;
    letter-spacing: -0.3px !important;
}

.food-explore-btn {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 8px !important;
    width: 100% !important;
    margin-top: 14px !important;
    padding: 12px 20px !important;
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%) !important;
    color: #ffffff !important;
    font-weight: 800 !important;
    font-size: 0.9rem !important;
    border-radius: 12px !important;
    border: none !important;
    box-shadow: 0 4px 14px rgba(249, 115, 22, 0.3) !important;
    transition: all 0.25s ease !important;
    cursor: pointer !important;
    text-decoration: none !important;
}

.food-explore-btn:hover {
    background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%) !important;
    box-shadow: 0 6px 20px rgba(249, 115, 22, 0.45) !important;
    transform: translateY(-2px) !important;
}

/* STAY IN ĐÔNG ANH (NHÀ NGHĨ, KHÁCH SẠN) */
.stay-hero-box {
    background: linear-gradient(135deg, #fdf2f8 0%, #fce7f3 50%, #ffe4e6 100%) !important;
    border: 1.5px solid #fbcfe8 !important;
    border-radius: 22px !important;
    padding: 24px 28px !important;
    margin-bottom: 24px !important;
    box-shadow: 0 12px 30px -8px rgba(219, 39, 119, 0.18) !important;
    position: relative !important;
    overflow: hidden !important;
    transition: all 0.35s ease !important;
}
.stay-badge-chip {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    font-size: 0.78rem !important;
    font-weight: 800 !important;
    letter-spacing: 0.5px !important;
    text-transform: uppercase !important;
    color: #be185d !important;
    background: #ffffff !important;
    border: 1.5px solid #fbcfe8 !important;
    padding: 6px 14px !important;
    border-radius: 30px !important;
    box-shadow: 0 2px 8px rgba(219, 39, 119, 0.1) !important;
}
.stay-hero-title {
    font-family: var(--font-heading) !important;
    font-size: 1.8rem !important;
    font-weight: 900 !important;
    line-height: 1.25 !important;
    margin: 12px 0 8px 0 !important;
    color: #831843 !important;
}
.stay-stats-pills {
    display: flex !important;
    flex-wrap: wrap !important;
    gap: 10px !important;
    margin-top: 16px !important;
}
.stay-stat-pill {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    padding: 7px 14px !important;
    background: #ffffff !important;
    border: 1.5px solid #fbcfe8 !important;
    border-radius: 12px !important;
    font-size: 0.82rem !important;
    font-weight: 700 !important;
    color: #be185d !important;
    box-shadow: 0 2px 6px rgba(219, 39, 119, 0.08) !important;
}
.eatery-card.stay-card-highlight {
    border: 1.5px solid #fbcfe8 !important;
    background: #ffffff !important;
    border-radius: 20px !important;
    padding: 18px !important;
    box-shadow: 0 4px 18px rgba(219, 39, 119, 0.06) !important;
}
.eatery-card.stay-card-highlight:hover {
    transform: translateY(-4px) !important;
    border-color: #db2777 !important;
    box-shadow: 0 12px 28px rgba(219, 39, 119, 0.2) !important;
}
.stay-title-badge {
    display: inline-flex !important;
    align-items: center !important;
    gap: 4px !important;
    font-size: 0.62rem !important;
    font-weight: 700 !important;
    letter-spacing: 0.4px !important;
    text-transform: uppercase !important;
    color: #be185d !important;
    background: rgba(219, 39, 119, 0.08) !important;
    border: 1px solid rgba(219, 39, 119, 0.25) !important;
    padding: 2px 7px !important;
    border-radius: 10px !important;
}
.stay-explore-btn {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 8px !important;
    width: 100% !important;
    margin-top: 14px !important;
    padding: 12px 20px !important;
    background: linear-gradient(135deg, #db2777 0%, #be185d 100%) !important;
    color: #ffffff !important;
    font-weight: 800 !important;
    font-size: 0.9rem !important;
    border-radius: 12px !important;
    border: none !important;
    box-shadow: 0 4px 14px rgba(219, 39, 119, 0.3) !important;
    text-decoration: none !important;
}
.stay-explore-btn:hover {
    background: linear-gradient(135deg, #be185d 0%, #9d174d 100%) !important;
    transform: translateY(-2px) !important;
}

/* WELLNESS & CARE (Y TẾ, SPA) */
.wellness-hero-box {
    background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 50%, #ccfbf1 100%) !important;
    border: 1.5px solid #a7f3d0 !important;
    border-radius: 22px !important;
    padding: 24px 28px !important;
    margin-bottom: 24px !important;
    box-shadow: 0 12px 30px -8px rgba(5, 150, 105, 0.18) !important;
    position: relative !important;
    overflow: hidden !important;
}
.wellness-badge-chip {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    font-size: 0.78rem !important;
    font-weight: 800 !important;
    letter-spacing: 0.5px !important;
    text-transform: uppercase !important;
    color: #047857 !important;
    background: #ffffff !important;
    border: 1.5px solid #a7f3d0 !important;
    padding: 6px 14px !important;
    border-radius: 30px !important;
    box-shadow: 0 2px 8px rgba(5, 150, 105, 0.1) !important;
}
.wellness-hero-title {
    font-family: var(--font-heading) !important;
    font-size: 1.8rem !important;
    font-weight: 900 !important;
    line-height: 1.25 !important;
    margin: 12px 0 8px 0 !important;
    color: #064e3b !important;
}
.wellness-stats-pills {
    display: flex !important;
    flex-wrap: wrap !important;
    gap: 10px !important;
    margin-top: 16px !important;
}
.wellness-stat-pill {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    padding: 7px 14px !important;
    background: #ffffff !important;
    border: 1.5px solid #a7f3d0 !important;
    border-radius: 12px !important;
    font-size: 0.82rem !important;
    font-weight: 700 !important;
    color: #047857 !important;
    box-shadow: 0 2px 6px rgba(5, 150, 105, 0.08) !important;
}
.eatery-card.wellness-card-highlight {
    border: 1.5px solid #a7f3d0 !important;
    background: #ffffff !important;
    border-radius: 20px !important;
    padding: 18px !important;
    box-shadow: 0 4px 18px rgba(5, 150, 105, 0.06) !important;
}
.eatery-card.wellness-card-highlight:hover {
    transform: translateY(-4px) !important;
    border-color: #059669 !important;
    box-shadow: 0 12px 28px rgba(5, 150, 105, 0.2) !important;
}
.wellness-title-badge {
    display: inline-flex !important;
    align-items: center !important;
    gap: 4px !important;
    font-size: 0.62rem !important;
    font-weight: 700 !important;
    letter-spacing: 0.4px !important;
    text-transform: uppercase !important;
    color: #047857 !important;
    background: rgba(5, 150, 105, 0.08) !important;
    border: 1px solid rgba(5, 150, 105, 0.25) !important;
    padding: 2px 7px !important;
    border-radius: 10px !important;
}
.wellness-explore-btn {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 8px !important;
    width: 100% !important;
    margin-top: 14px !important;
    padding: 12px 20px !important;
    background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
    color: #ffffff !important;
    font-weight: 800 !important;
    font-size: 0.9rem !important;
    border-radius: 12px !important;
    border: none !important;
    box-shadow: 0 4px 14px rgba(5, 150, 105, 0.3) !important;
    text-decoration: none !important;
}
.wellness-explore-btn:hover {
    background: linear-gradient(135deg, #047857 0%, #065f46 100%) !important;
    transform: translateY(-2px) !important;
}

/* COMMUNITY & CULTURE HUB (THIẾT CHẾ VĂN HÓA) */
.culture-hero-box {
    background: linear-gradient(135deg, #fefce8 0%, #fef9c3 50%, #fef08a 100%) !important;
    border: 1.5px solid #fde047 !important;
    border-radius: 22px !important;
    padding: 24px 28px !important;
    margin-bottom: 24px !important;
    box-shadow: 0 12px 30px -8px rgba(217, 119, 6, 0.18) !important;
    position: relative !important;
    overflow: hidden !important;
}
.culture-badge-chip {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    font-size: 0.78rem !important;
    font-weight: 800 !important;
    letter-spacing: 0.5px !important;
    text-transform: uppercase !important;
    color: #b45309 !important;
    background: #ffffff !important;
    border: 1.5px solid #fde047 !important;
    padding: 6px 14px !important;
    border-radius: 30px !important;
    box-shadow: 0 2px 8px rgba(217, 119, 6, 0.1) !important;
}
.culture-hero-title {
    font-family: var(--font-heading) !important;
    font-size: 1.8rem !important;
    font-weight: 900 !important;
    line-height: 1.25 !important;
    margin: 12px 0 8px 0 !important;
    color: #78350f !important;
}
.culture-stats-pills {
    display: flex !important;
    flex-wrap: wrap !important;
    gap: 10px !important;
    margin-top: 16px !important;
}
.culture-stat-pill {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    padding: 7px 14px !important;
    background: #ffffff !important;
    border: 1.5px solid #fde047 !important;
    border-radius: 12px !important;
    font-size: 0.82rem !important;
    font-weight: 700 !important;
    color: #b45309 !important;
    box-shadow: 0 2px 6px rgba(217, 119, 6, 0.08) !important;
}
.eatery-card.culture-card-highlight {
    border: 1.5px solid #fde047 !important;
    background: #ffffff !important;
    border-radius: 20px !important;
    padding: 18px !important;
    box-shadow: 0 4px 18px rgba(217, 119, 6, 0.06) !important;
}
.eatery-card.culture-card-highlight:hover {
    transform: translateY(-4px) !important;
    border-color: #d97706 !important;
    box-shadow: 0 12px 28px rgba(217, 119, 6, 0.2) !important;
}
.culture-title-badge {
    display: inline-flex !important;
    align-items: center !important;
    gap: 4px !important;
    font-size: 0.62rem !important;
    font-weight: 700 !important;
    letter-spacing: 0.4px !important;
    text-transform: uppercase !important;
    color: #b45309 !important;
    background: rgba(217, 119, 6, 0.08) !important;
    border: 1px solid rgba(217, 119, 6, 0.25) !important;
    padding: 2px 7px !important;
    border-radius: 10px !important;
}
.culture-explore-btn {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 8px !important;
    width: 100% !important;
    margin-top: 14px !important;
    padding: 12px 20px !important;
    background: linear-gradient(135deg, #d97706 0%, #b45309 100%) !important;
    color: #ffffff !important;
    font-weight: 800 !important;
    font-size: 0.9rem !important;
    border-radius: 12px !important;
    border: none !important;
    box-shadow: 0 4px 14px rgba(217, 119, 6, 0.3) !important;
    text-decoration: none !important;
}
.culture-explore-btn:hover {
    background: linear-gradient(135deg, #b45309 0%, #92400e 100%) !important;
    transform: translateY(-2px) !important;
}

/* SMART EDUCATION MAP (TRƯỜNG HỌC) */
.edu-hero-box {
    background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 50%, #c7d2fe 100%) !important;
    border: 1.5px solid #a5b4fc !important;
    border-radius: 22px !important;
    padding: 24px 28px !important;
    margin-bottom: 24px !important;
    box-shadow: 0 12px 30px -8px rgba(79, 70, 229, 0.18) !important;
    position: relative !important;
    overflow: hidden !important;
}
.edu-badge-chip {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    font-size: 0.78rem !important;
    font-weight: 800 !important;
    letter-spacing: 0.5px !important;
    text-transform: uppercase !important;
    color: #4338ca !important;
    background: #ffffff !important;
    border: 1.5px solid #a5b4fc !important;
    padding: 6px 14px !important;
    border-radius: 30px !important;
    box-shadow: 0 2px 8px rgba(79, 70, 229, 0.1) !important;
}
.edu-hero-title {
    font-family: var(--font-heading) !important;
    font-size: 1.8rem !important;
    font-weight: 900 !important;
    line-height: 1.25 !important;
    margin: 12px 0 8px 0 !important;
    color: #1e1b4b !important;
}
.edu-stats-pills {
    display: flex !important;
    flex-wrap: wrap !important;
    gap: 10px !important;
    margin-top: 16px !important;
}
.edu-stat-pill {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    padding: 7px 14px !important;
    background: #ffffff !important;
    border: 1.5px solid #a5b4fc !important;
    border-radius: 12px !important;
    font-size: 0.82rem !important;
    font-weight: 700 !important;
    color: #4338ca !important;
    box-shadow: 0 2px 6px rgba(79, 70, 229, 0.08) !important;
}
.eatery-card.edu-card-highlight {
    border: 1.5px solid #a5b4fc !important;
    background: #ffffff !important;
    border-radius: 20px !important;
    padding: 18px !important;
    box-shadow: 0 4px 18px rgba(79, 70, 229, 0.06) !important;
}
.eatery-card.edu-card-highlight:hover {
    transform: translateY(-4px) !important;
    border-color: #4f46e5 !important;
    box-shadow: 0 12px 28px rgba(79, 70, 229, 0.2) !important;
}
.edu-title-badge {
    display: inline-flex !important;
    align-items: center !important;
    gap: 4px !important;
    font-size: 0.62rem !important;
    font-weight: 700 !important;
    letter-spacing: 0.4px !important;
    text-transform: uppercase !important;
    color: #4338ca !important;
    background: rgba(79, 70, 229, 0.08) !important;
    border: 1px solid rgba(79, 70, 229, 0.25) !important;
    padding: 2px 7px !important;
    border-radius: 10px !important;
}
.edu-card-actions {
    display: flex !important;
    align-items: stretch !important;
    gap: 8px !important;
    margin-top: 14px !important;
    width: 100% !important;
}
.edu-story-btn {
    flex: 1 !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 6px !important;
    padding: 10px 12px !important;
    background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%) !important;
    color: #4338ca !important;
    font-weight: 800 !important;
    font-size: 0.82rem !important;
    border-radius: 12px !important;
    border: 1.5px solid #c7d2fe !important;
    box-shadow: 0 2px 8px rgba(79, 70, 229, 0.1) !important;
    cursor: pointer !important;
    text-decoration: none !important;
    white-space: nowrap !important;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
    box-sizing: border-box !important;
}
.edu-story-btn:hover {
    background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%) !important;
    border-color: #a5b4fc !important;
    color: #312e81 !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2) !important;
}
.edu-explore-btn {
    flex: 1.6 !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 6px !important;
    padding: 10px 12px !important;
    background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%) !important;
    color: #ffffff !important;
    font-weight: 800 !important;
    font-size: 0.82rem !important;
    border-radius: 12px !important;
    border: none !important;
    box-shadow: 0 4px 14px rgba(79, 70, 229, 0.28) !important;
    text-decoration: none !important;
    white-space: nowrap !important;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
    box-sizing: border-box !important;
}
.edu-explore-btn:hover {
    background: linear-gradient(135deg, #4338ca 0%, #312e81 100%) !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 18px rgba(79, 70, 229, 0.38) !important;
}
@media (max-width: 540px) {
    .edu-card-actions {
        flex-direction: column !important;
        gap: 8px !important;
    }
    .edu-card-actions > .edu-story-btn,
    .edu-card-actions > .edu-explore-btn {
        width: 100% !important;
        flex: none !important;
        padding: 11px 14px !important;
        font-size: 0.85rem !important;
    }
}

/* ==================== CƠ SỞ KINH DOANH & DOANH NGHIỆP ==================== */
.business-hero-box {
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 50%, #f8fafc 100%) !important;
    border: 1.5px solid #bae6fd !important;
    border-radius: 24px !important;
    padding: 24px 28px !important;
    margin-bottom: 24px !important;
    position: relative !important;
    overflow: hidden !important;
    box-shadow: 0 10px 30px rgba(2, 132, 199, 0.08) !important;
}

.business-hero-box::before {
    content: '' !important;
    position: absolute !important;
    top: -40% !important;
    right: -15% !important;
    width: 240px !important;
    height: 240px !important;
    background: radial-gradient(circle, rgba(2, 132, 199, 0.15) 0%, rgba(255, 255, 255, 0) 70%) !important;
    border-radius: 50% !important;
    pointer-events: none !important;
}

.business-badge-chip {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    font-size: 0.78rem !important;
    font-weight: 800 !important;
    letter-spacing: 0.5px !important;
    text-transform: uppercase !important;
    color: #0284c7 !important;
    background: #ffffff !important;
    border: 1.5px solid #7dd3fc !important;
    padding: 6px 14px !important;
    border-radius: 30px !important;
    box-shadow: 0 2px 8px rgba(14, 165, 233, 0.1) !important;
}

.business-hero-title {
    font-family: var(--font-heading) !important;
    font-size: 1.8rem !important;
    font-weight: 900 !important;
    line-height: 1.25 !important;
    margin: 12px 0 8px 0 !important;
    color: #0f172a !important;
}

.business-stats-pills {
    display: flex !important;
    flex-wrap: wrap !important;
    gap: 10px !important;
    margin-top: 16px !important;
}

.business-stat-pill {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    padding: 7px 14px !important;
    background: #ffffff !important;
    border: 1.5px solid #bae6fd !important;
    border-radius: 12px !important;
    font-size: 0.82rem !important;
    font-weight: 700 !important;
    color: #0369a1 !important;
    box-shadow: 0 2px 6px rgba(2, 132, 199, 0.08) !important;
    transition: all 0.2s ease !important;
}

.business-stat-pill:hover {
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 10px rgba(2, 132, 199, 0.15) !important;
}

.business-stat-pill span.icon {
    font-size: 1rem !important;
}

.eatery-card.business-card-highlight {
    border: 1.5px solid #e2e8f0 !important;
    background: #ffffff !important;
    border-radius: 20px !important;
    padding: 16px !important;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04) !important;
    transition: all 0.3s cubic-bezier(0.32, 0.72, 0, 1) !important;
    position: relative !important;
    overflow: hidden !important;
}

.eatery-card.business-card-highlight:hover {
    transform: translateY(-4px) !important;
    border-color: #38bdf8 !important;
    box-shadow: 0 12px 28px rgba(2, 132, 199, 0.18) !important;
}

.business-title-badge {
    display: inline-flex !important;
    align-items: center !important;
    gap: 4px !important;
    font-size: 0.64rem !important;
    font-weight: 800 !important;
    letter-spacing: 0.4px !important;
    text-transform: uppercase !important;
    color: #0284c7 !important;
    background: rgba(14, 165, 233, 0.1) !important;
    border: 1px solid rgba(14, 165, 233, 0.28) !important;
    padding: 3px 8px !important;
    border-radius: 8px !important;
}

.business-mst-tag {
    display: inline-flex !important;
    align-items: center !important;
    gap: 4px !important;
    font-size: 0.7rem !important;
    font-weight: 700 !important;
    color: #334155 !important;
    background: #f1f5f9 !important;
    border: 1px solid #cbd5e1 !important;
    padding: 2px 7px !important;
    border-radius: 6px !important;
}

.business-phone-tag {
    display: inline-flex !important;
    align-items: center !important;
    gap: 4px !important;
    font-size: 0.75rem !important;
    font-weight: 800 !important;
    color: #0284c7 !important;
    background: #e0f2fe !important;
    border: 1px solid #bae6fd !important;
    padding: 3px 9px !important;
    border-radius: 8px !important;
    text-decoration: none !important;
    transition: all 0.2s !important;
}

.business-phone-tag:hover {
    background: #0284c7 !important;
    color: #ffffff !important;
    border-color: #0284c7 !important;
}

.business-explore-btn {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 8px !important;
    width: 100% !important;
    margin-top: 12px !important;
    padding: 11px 18px !important;
    background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important;
    color: #ffffff !important;
    font-weight: 800 !important;
    font-size: 0.86rem !important;
    border-radius: 12px !important;
    border: none !important;
    box-shadow: 0 4px 14px rgba(2, 132, 199, 0.25) !important;
    transition: all 0.25s cubic-bezier(0.32, 0.72, 0, 1) !important;
    text-decoration: none !important;
    cursor: pointer !important;
}

.business-explore-btn:hover {
    background: linear-gradient(135deg, #0369a1 0%, #075985 100%) !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 18px rgba(2, 132, 199, 0.38) !important;
    color: #ffffff !important;
}

/* OCOP Product detail popup CSS classes to prevent layout squishing on mobile */
.ocop-popup-hero {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    margin-bottom: 24px;
    border-bottom: 1px solid #e2e8f0;
    padding-bottom: 20px;
}

.ocop-popup-img-col {
    flex: 0 0 220px;
    width: 220px;
    height: 220px;
    border-radius: 14px;
    overflow: hidden;
    border: 1px solid #cbd5e1;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    background: #f8fafc;
}

.ocop-popup-info-col {
    flex: 1;
    min-width: 260px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

/* Mobile responsive adjustments */
@media (max-width: 767px) {
    .ocop-popup-hero {
        gap: 16px;
    }
    
    .ocop-popup-img-col {
        flex: 0 0 100%;
        width: 100%;
        height: 200px;
    }
    
    .ocop-popup-info-col {
        flex: 0 0 100%;
        min-width: 100%;
        gap: 16px;
    }
}

/* Categories Slider Horizontal Scroll Fix for Mobile & Desktop */
.categories-container-wrap {
    width: 100% !important;
    max-width: 1400px !important;
    position: relative !important;
    z-index: 10 !important;
    margin: 20px auto 24px auto !important;
    padding: 0 16px !important;
    box-sizing: border-box !important;
}

.categories-slider {
    display: flex !important;
    flex-wrap: nowrap !important;
    justify-content: flex-start !important;
    align-items: center !important;
    gap: 12px !important;
    padding: 12px 16px 14px !important;
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
    overflow-x: auto !important;
    overflow-y: visible !important;
    scroll-behavior: smooth !important;
    -webkit-overflow-scrolling: touch !important;
    touch-action: pan-x pan-y !important;
    scrollbar-width: thin !important;
    scrollbar-color: #cbd5e1 rgba(0, 0, 0, 0.04) !important;
    cursor: grab;
}

/* Thanh cuộn ngang thanh lịch, mảnh mai & tinh tế */
.categories-slider::-webkit-scrollbar {
    height: 5px !important;
    display: block !important;
}

.categories-slider::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.04) !important;
    border-radius: 10px !important;
    margin: 0 16px !important;
}

.categories-slider::-webkit-scrollbar-thumb {
    background: #cbd5e1 !important;
    border-radius: 10px !important;
    transition: background 0.2s ease !important;
}

.categories-slider::-webkit-scrollbar-thumb:hover {
    background: #94a3b8 !important;
}

.category-card {
    flex: 0 0 auto !important;
    min-width: 148px !important;
    max-width: 172px !important;
    width: 154px !important;
    height: 114px !important;
    padding: 10px 6px 8px !important;
    text-align: center !important;
    cursor: pointer !important;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 4px !important;
    border: 1.5px solid rgba(14, 165, 233, 0.22) !important;
    border-radius: 20px !important;
    background: #ffffff !important;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05) !important;
    text-decoration: none !important;
    box-sizing: border-box !important;
}

.category-card:hover {
    border-color: #0284c7 !important;
    transform: translateY(-4px) !important;
    box-shadow: 0 8px 20px rgba(2, 132, 199, 0.18) !important;
}

.category-card.active {
    background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important;
    border-color: #0284c7 !important;
    color: #ffffff !important;
    box-shadow: 0 8px 22px rgba(2, 132, 199, 0.35) !important;
}

.category-card .cat-icon {
    font-size: 1.5rem !important;
    line-height: 1 !important;
}

.category-card .cat-icon-img {
    width: 38px !important;
    height: 38px !important;
    border-radius: 50% !important;
    object-fit: cover !important;
}

.category-card .cat-name {
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    text-align: center !important;
    gap: 2px !important;
    width: 100% !important;
}

.category-card .cat-title-en {
    color: #0f172a !important;
    font-weight: 800 !important;
    font-size: 0.68rem !important;
    line-height: 1.2 !important;
    text-transform: uppercase !important;
    letter-spacing: 0px !important;
    white-space: nowrap !important;
    text-align: center !important;
    max-width: 100% !important;
    overflow: visible !important;
}

.category-card .cat-title-vi {
    color: #64748b !important;
    font-weight: 600 !important;
    font-size: 0.64rem !important;
    line-height: 1.2 !important;
    white-space: nowrap !important;
    text-align: center !important;
    max-width: 100% !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
}

.category-card.active .cat-title-en,
.category-card.active .cat-title-vi {
    color: #ffffff !important;
}
</style>

<section class="custom-hero-banner">
    <!-- ☁️ GRAND REAL PHOTOGRAPHIC CLOUD BACKDROP -->
    <div class="cloud-center-backdrop">
        <img src="{{ asset('images/cloud_real_c.webp') }}" class="real-cloud-center-img" alt="Bầu trời mây Đông Anh" />
        <img src="{{ asset('images/cloud_real_b.webp') }}" class="real-cloud-backdrop-sub-img" alt="Mây thật" />
    </div>

    <!-- 💖 3D Heart Smoke Contrail Overlay (Delicate Aerobatics Smoke) -->
    <div class="heart-contrail-sky-wrap">
        <svg viewBox="0 0 800 350" preserveAspectRatio="none" style="width: 100%; height: 100%;">
            <defs>
                <filter id="glowSmoke" x="-20%" y="-20%" width="140%" height="140%">
                    <feGaussianBlur stdDeviation="6" result="blur" />
                    <feComposite in="SourceGraphic" in2="blur" operator="over" />
                </filter>
                <linearGradient id="heartSmokeGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="rgba(255, 255, 255, 0.95)"/>
                    <stop offset="35%" stop-color="rgba(254, 205, 211, 0.85)"/>
                    <stop offset="75%" stop-color="rgba(244, 63, 94, 0.7)"/>
                    <stop offset="100%" stop-color="rgba(225, 29, 72, 0.35)"/>
                </linearGradient>
            </defs>
            <path class="heart-smoke-path" d="M 400,65 C 400,65 280,5 280,75 C 280,145 400,240 400,240 C 400,240 520,145 520,75 C 520,5 400,65 400,65 Z" fill="none" stroke="url(#heartSmokeGrad)" stroke-width="7" stroke-linecap="round" stroke-linejoin="round" filter="url(#glowSmoke)"/>
        </svg>
    </div>

    <!-- ✈️ REAL PHOTOGRAPHIC COMMERCIAL PASSENGER AIRLINER -->
    <div class="hero-airplane-container">
        <!-- Dual Engine Jet Contrail Vapors -->
        <div class="jet-contrail-container">
            <div class="jet-contrail-line"></div>
            <div class="jet-contrail-puff" style="width: 14px; height: 14px; right: 10px;"></div>
            <div class="jet-contrail-puff" style="width: 24px; height: 24px; right: 45px; animation-delay: 0.35s;"></div>
            <div class="jet-contrail-puff" style="width: 36px; height: 36px; right: 90px; animation-delay: 0.75s;"></div>
            <div class="jet-contrail-puff" style="width: 52px; height: 52px; right: 150px; animation-delay: 1.15s;"></div>
            <div class="jet-contrail-puff" style="width: 70px; height: 70px; right: 220px; animation-delay: 1.6s;"></div>
        </div>

        <div class="hero-airplane-photo-wrap">
            <img src="{{ asset('images/plane_60.webp') }}" class="real-airplane-photo-img" alt="Máy bay thật Đông Anh Discovery" />
        </div>
    </div>

    <!-- ☁️ REAL PHOTOGRAPHIC PARALLAX CLOUDS -->
    <div class="cloud-3d-wrap cloud-layer-1">
        <img src="{{ asset('images/cloud_real_b.webp') }}" class="real-cloud-layer-img" style="width: 290px;" alt="Mây thật" />
    </div>

    <div class="cloud-3d-wrap cloud-layer-2">
        <img src="{{ asset('images/cloud_real_e.webp') }}" class="real-cloud-layer-img" style="width: 250px;" alt="Mây thật" />
    </div>

    <div class="cloud-3d-wrap cloud-layer-3">
        <img src="{{ asset('images/cloud_real_c.webp') }}" class="real-cloud-layer-img" style="width: 340px;" alt="Mây thật" />
    </div>

    <div class="cloud-3d-wrap cloud-layer-4">
        <img src="{{ asset('images/cloud_real_d.webp') }}" class="real-cloud-layer-img" style="width: 230px;" alt="Mây thật" />
    </div>

    <!-- Polaroid Galleries (Real Photographs) -->
    <div class="gallery-container">
        <!-- Polaroid 1: Vi vu -->
        <div class="polaroid-card" style="left: 14%; top: 30px;" data-angle="-7">
            <img src="{{ asset('images/vivudonganh.jpg') }}" alt="Vi vu Đông Anh" class="polaroid-img">
            <div class="polaroid-caption">Vi vu Đông Anh</div>
        </div>

        <!-- Polaroid 2: Cây đa -->
        <div class="polaroid-card" style="left: 28%; top: 15px;" data-angle="4">
            <img src="{{ asset('images/caydabacho.jpg') }}" alt="Cây đa Bác Hồ" class="polaroid-img">
            <div class="polaroid-caption">Cây đa bác hồ</div>
        </div>

        <!-- Polaroid 3: Gate -->
        <div class="polaroid-card" style="left: 56%; top: 22px;" data-angle="6">
            <img src="{{ asset('images/thanhcoloa.webp') }}" alt="Thành Cổ Loa" class="polaroid-img">
            <div class="polaroid-caption">Thành Cổ Loa</div>
        </div>

        <!-- Polaroid 4: Concert -->
        <div class="polaroid-card" style="left: 73%; top: 35px;" data-angle="-5">
            <img src="{{ asset('images/trungtamvanhoa.webp') }}" alt="Trung tâm văn hóa" class="polaroid-img">
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
