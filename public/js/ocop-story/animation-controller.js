/**
 * OCOP Animation Controller
 * Implements 10-Phase Apple Event / Tesla Presentation / Google Maps Style Cinematic Timeline
 * 
 * Concept: "Chữ LED 'ĐA' chính là nguồn năng lượng khởi phát của toàn bộ hệ sinh thái OCOP Đông Anh."
 * Timeline:
 * Phase 1: LED Glow & Horizontal Light Beam Sweep (600ms)
 * Phase 2: Golden Shockwave Ring (400ms, 0 -> 150px)
 * Phase 3 & 4: 33 Energy Orbs Bezier Curve Flight to Markers (power4.out)
 * Phase 5: Marker Ignition Pulse (1 -> 1.4 -> 1, 250ms)
 * Phase 6: Orb Dissolves into 10-15 Gold Particles (150ms)
 * Phase 7 & 8: Card Opens at Marker Location (Scale 0 -> 1.05 -> 1, Domino Stagger 70ms, Non-Overlapping Fan Offset)
 * Phase 8.5: Golden Constellation Laser Matrix Burst & Imploding Pulse (800ms)
 * Phase 8.6: Heritage Slogan Light Banner Sweep (700ms)
 * Phase 9: Heatmap Fade-In (Opacity 0 -> 100%, 500ms)
 * Phase 10: Grand Finale Stats Card Display
 */
class OcopAnimationController {
    constructor() {
        this.slogans = [
            "OCOP ĐÔNG ANH",
            "Kết nối giá trị",
            "Lan tỏa đặc sản",
            "Nâng tầm nông sản Việt",
            "Tinh hoa địa phương",
            "Mỗi sản phẩm là một câu chuyện",
            "Đông Anh - Vùng đất của giá trị và sáng tạo"
        ];
    }

    /**
     * Cinematic Intro Overlay: Clean Intro Screen
     */
    async playCinematicIntro(totalCount = 33) {
        const introOverlay = document.getElementById('ocopCinematicIntroOverlay');
        const countText = document.getElementById('ocopIntroCountText');
        
        if (countText) countText.innerText = `Khám phá ${totalCount} sản phẩm OCOP tiêu biểu Xã Đông Anh`;

        if (!introOverlay) return;

        introOverlay.style.display = 'flex';
        introOverlay.style.opacity = '1';

        const titleEl = document.getElementById('ocopIntroMainTitle');
        if (titleEl) {
            gsap.fromTo(titleEl, 
                { opacity: 0, y: 30, scale: 0.9 },
                { opacity: 1, y: 0, scale: 1, duration: 0.6, ease: 'power3.out' }
            );
        }

        await new Promise(r => setTimeout(r, 1000));

        return new Promise(resolve => {
            gsap.to(introOverlay, {
                opacity: 0,
                duration: 0.4,
                ease: 'power2.inOut',
                onComplete: () => {
                    introOverlay.style.display = 'none';
                    resolve();
                }
            });
        });
    }

    /**
     * Entry Point for Grand Finale
     */
    async playSloganChainAndFinale(prods = []) {
        await this.playAppleStyle10PhaseFinale(prods);
    }

    /**
     * Apple / Tesla Style 10-Phase Cinematic Grand Finale Timeline
     */
    async playAppleStyle10PhaseFinale(prods = []) {
        const sloganOverlay = document.getElementById('ocopSloganOverlay');
        const sloganTextEl = document.getElementById('ocopSloganText');
        const finaleCard = document.getElementById('ocopFinaleStatsCard');

        if (!sloganOverlay) return;

        // Container setup
        sloganOverlay.style.display = 'flex';
        sloganOverlay.style.opacity = '1';
        sloganOverlay.style.background = 'transparent';
        sloganOverlay.style.backdropFilter = 'none';
        sloganOverlay.style.pointerEvents = 'none';

        if (sloganTextEl) sloganTextEl.style.display = 'none';
        if (finaleCard) finaleCard.style.display = 'none';

        // Prepare Canvas for Bezier Light Trails, Shockwave & Dissolve Particles
        let canvas = document.getElementById('ocopSparkleCanvas');
        if (!canvas) {
            canvas = document.createElement('canvas');
            canvas.id = 'ocopSparkleCanvas';
            document.getElementById('ocopStorytellingModal').appendChild(canvas);
        }

        const modalEl = document.getElementById('ocopStorytellingModal');
        const modalRect = modalEl ? modalEl.getBoundingClientRect() : { width: window.innerWidth, height: window.innerHeight };

        canvas.width = modalRect.width || window.innerWidth;
        canvas.height = modalRect.height || window.innerHeight;
        canvas.style.display = 'block';
        canvas.style.position = 'absolute';
        canvas.style.inset = '0';
        canvas.style.pointerEvents = 'none';
        canvas.style.zIndex = '999995';

        const ctx = canvas.getContext('2d');

        // Center Pixel coordinates (Center of Dong Anh ĐA shape: lat 21.135, lng 105.868)
        let cx = canvas.width / 2;
        let cy = canvas.height / 2;

        const map = (window.ocopStoryController && window.ocopStoryController.map) ? window.ocopStoryController.map : null;
        if (map) {
            const pt = map.latLngToContainerPoint([21.135, 105.868]);
            if (pt && pt.x > 0 && pt.y > 0) {
                cx = pt.x;
                cy = pt.y;
            }
        }

        // =========================================================================
        // PHASE 1: Chữ ĐA phát sáng (Glow Vàng #FFD54F) & Light Beam Sweep (600ms)
        // =========================================================================
        await this.phase1_DaLettersGlowAndSweep();

        // =========================================================================
        // PHASE 2: Golden Shockwave Ring từ center ĐA (0 -> 150px, opacity 1 -> 0, 400ms)
        // =========================================================================
        await this.phase2_GoldenShockwaveRing(ctx, cx, cy);

        // =========================================================================
        // PHASE 3 - 8: 33 Orbs Bezier Flight -> Marker Ignition -> 10-15 Gold Particles -> Card Opens at Marker
        // =========================================================================
        const markerPoints = await this.phase3To8_BezierOrbsToMarkerCardBirth(ctx, prods, cx, cy);

        // =========================================================================
        // PHASE 8.5: Golden Constellation Laser Matrix Burst & Imploding Pulse (800ms)
        // =========================================================================
        await this.phase8_5_ConstellationMatrixBurst(ctx, cx, cy, markerPoints);

        // =========================================================================
        // PHASE 8.6: Heritage Slogan Light Banner Sweep (700ms)
        // =========================================================================
        await this.phase8_6_SloganBannerSweep();

        // =========================================================================
        // PHASE 9: Heatmap Fade-In & Camera Zoom Out in Parallel
        // =========================================================================
        this.phase9_HeatmapFadeIn();

        if (window.ocopStoryController && window.ocopStoryController.cameraCtrl) {
            window.ocopStoryController.cameraCtrl.flyToTarget(21.135, 105.868, 11.2, 0.8);
        }

        // =========================================================================
        // PHASE 10: SHOW FINALE CARD WITH ELEGANT TRANSITION
        // =========================================================================
        sloganOverlay.style.pointerEvents = 'auto';
        gsap.to(sloganOverlay, {
            backgroundColor: 'rgba(15, 94, 74, 0.94)',
            backdropFilter: 'blur(16px)',
            duration: 0.5,
            ease: 'power2.out'
        });

        if (sloganTextEl) sloganTextEl.style.display = 'none';

        if (finaleCard) {
            finaleCard.style.display = 'block';
            gsap.fromTo(finaleCard,
                { opacity: 0, scale: 0.85, y: 35 },
                { opacity: 1, scale: 1, y: 0, duration: 0.6, ease: 'back.out(1.4)' }
            );
        }

        if (canvas) canvas.style.display = 'none';
    }

    /**
     * Phase 1: LED Pins Glow Vàng & Horizontal Beam Sweep (600ms)
     */
    phase1_DaLettersGlowAndSweep() {
        return new Promise(resolve => {
            const pins = document.querySelectorAll('.ocop-da-pin-node');
            if (pins && pins.length > 0) {
                gsap.to(pins, {
                    boxShadow: '0 0 25px #FFD54F, 0 0 45px rgba(255, 213, 79, 0.85)',
                    borderColor: '#FFD54F',
                    duration: 0.6,
                    stagger: { amount: 0.3, from: 'start' },
                    ease: 'power2.out'
                });
            }
            setTimeout(resolve, 600);
        });
    }

    /**
     * Phase 2: Golden Shockwave Ring (0 -> 150px, opacity 100% -> 0%, 400ms)
     */
    phase2_GoldenShockwaveRing(ctx, cx, cy) {
        return new Promise(resolve => {
            const shockwave = { r: 0, alpha: 1.0 };
            gsap.to(shockwave, {
                r: 150,
                alpha: 0,
                duration: 0.4,
                ease: 'power2.out',
                onUpdate: () => {
                    ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
                    ctx.save();
                    ctx.beginPath();
                    ctx.arc(cx, cy, shockwave.r, 0, Math.PI * 2);
                    ctx.strokeStyle = '#FFD54F';
                    ctx.lineWidth = 3.0;
                    ctx.globalAlpha = shockwave.alpha;
                    ctx.shadowBlur = 20;
                    ctx.shadowColor = '#FFD54F';
                    ctx.stroke();
                    ctx.restore();
                },
                onComplete: () => {
                    ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
                    resolve();
                }
            });
        });
    }

    /**
     * Phase 3 - 8: Bezier Orbs Flight -> Marker Ignition -> 10-15 Gold Particles -> Card Opens at Marker Location
     */
    async phase3To8_BezierOrbsToMarkerCardBirth(ctx, prods = [], cx, cy) {
        const productList = (prods && prods.length > 0) ? prods : (window.OFFICIAL_OCOP_PRODUCTS || []);
        const total = productList.length;

        // Container for Domino Cards
        let cardsContainer = document.getElementById('ocopDominoCardsContainer');
        if (!cardsContainer) {
            cardsContainer = document.createElement('div');
            cardsContainer.id = 'ocopDominoCardsContainer';
            cardsContainer.className = 'ocop-domino-cards-container';
            document.getElementById('ocopStorytellingModal').appendChild(cardsContainer);
        }
        cardsContainer.innerHTML = '';
        cardsContainer.style.display = 'block';

        const screenW = ctx.canvas.width;
        const screenH = ctx.canvas.height;

        const activeOrbs = [];
        const activeParticles = [];
        const markerPoints = [];
        let isLoopAnimating = true;

        // High-Performance 60 FPS / 120 FPS Render Loop for Canvas Orbs & Particles
        function renderCanvasLoop() {
            if (!ctx || !isLoopAnimating) return;
            ctx.clearRect(0, 0, screenW, screenH);

            // 1. Draw Active Dissolve Gold Dust Particles (Ultra Lightweight 60 FPS)
            for (let pIdx = activeParticles.length - 1; pIdx >= 0; pIdx--) {
                const part = activeParticles[pIdx];
                part.x += part.vx;
                part.y += part.vy;
                part.alpha -= part.decay;

                if (part.alpha <= 0) {
                    activeParticles.splice(pIdx, 1);
                    continue;
                }

                ctx.globalAlpha = part.alpha;
                ctx.fillStyle = '#FFD54F';
                ctx.beginPath();
                ctx.arc(part.x, part.y, part.size, 0, Math.PI * 2);
                ctx.fill();
            }
            ctx.globalAlpha = 1.0;

            // 2. Draw Active Bezier Energy Orbs (~8px glowing head + motion blur tail)
            for (let oIdx = activeOrbs.length - 1; oIdx >= 0; oIdx--) {
                const orb = activeOrbs[oIdx];
                const t = orb.progress;

                const invT = 1 - t;
                const curX = invT * invT * cx + 2 * invT * t * orb.controlX + t * t * orb.targetX;
                const curY = invT * invT * cy + 2 * invT * t * orb.controlY + t * t * orb.targetY;

                if (t > 0.05) {
                    const prevT = Math.max(0, t - 0.12);
                    const invPrevT = 1 - prevT;
                    const prevX = invPrevT * invPrevT * cx + 2 * invPrevT * prevT * orb.controlX + prevT * prevT * orb.targetX;
                    const prevY = invPrevT * invPrevT * cy + 2 * invPrevT * prevT * orb.controlY + prevT * prevT * orb.targetY;

                    const grad = ctx.createLinearGradient(prevX, prevY, curX, curY);
                    grad.addColorStop(0, 'rgba(255, 213, 79, 0)');
                    grad.addColorStop(1, '#FFD54F');
                    ctx.strokeStyle = grad;
                    ctx.lineWidth = 3.0;
                    ctx.beginPath();
                    ctx.moveTo(prevX, prevY);
                    ctx.lineTo(curX, curY);
                    ctx.stroke();
                }

                // Glowing Energy Orb Head (Concentric High-Speed Glow)
                ctx.fillStyle = 'rgba(255, 213, 79, 0.45)';
                ctx.beginPath();
                ctx.arc(curX, curY, 6, 0, Math.PI * 2);
                ctx.fill();

                ctx.fillStyle = '#FFFFFF';
                ctx.beginPath();
                ctx.arc(curX, curY, 3, 0, Math.PI * 2);
                ctx.fill();

                if (t >= 1) {
                    activeOrbs.splice(oIdx, 1);
                }
            }

            if (isLoopAnimating) {
                requestAnimationFrame(renderCanvasLoop);
            }
        }
        renderCanvasLoop();

        // Spawn 10-15 Gold Particles when Orb arrives at Marker (Phase 6, 150ms)
        function spawnDissolveParticles(tx, ty) {
            const count = 12;
            for (let k = 0; k < count; k++) {
                const angle = Math.random() * Math.PI * 2;
                const speed = 1.2 + Math.random() * 2.8;
                activeParticles.push({
                    x: tx,
                    y: ty,
                    vx: Math.cos(angle) * speed,
                    vy: Math.sin(angle) * speed,
                    alpha: 1.0,
                    decay: 0.05,
                    size: 1.5 + Math.random() * 1.8
                });
            }
        }

        const map = (window.ocopStoryController && window.ocopStoryController.map) ? window.ocopStoryController.map : null;

        // Launch 33 Energy Orbs sequentially along Bezier paths (Stagger 70ms)
        for (let i = 0; i < total; i++) {
            const p = productList[i];

            let tx = (0.15 + (i % 6) * 0.14) * screenW;
            let ty = (0.2 + Math.floor(i / 6) * 0.13) * screenH;

            // Prefer Leaflet Container Point projection for 100% accurate coordinates!
            if (map && p.daLat && p.daLng) {
                const pt = map.latLngToContainerPoint([p.daLat, p.daLng]);
                if (pt && pt.x > 0 && pt.y > 0) {
                    tx = pt.x;
                    ty = pt.y;
                }
            } else {
                const pinEl = document.getElementById(`daPin_${i}`);
                if (pinEl) {
                    const rect = pinEl.getBoundingClientRect();
                    const modalEl = document.getElementById('ocopStorytellingModal');
                    const modalRect = modalEl ? modalEl.getBoundingClientRect() : { left: 0, top: 0 };
                    if (rect.width > 0 && rect.height > 0) {
                        tx = rect.left - modalRect.left + rect.width / 2;
                        ty = rect.top - modalRect.top + rect.height / 2;
                    }
                }
            }

            markerPoints.push({ x: tx, y: ty });

            // Calculate Bezier Control Point (Curved Path)
            const midX = (cx + tx) / 2;
            const midY = (cy + ty) / 2;
            const perpX = -(ty - cy) * 0.25;
            const perpY = (tx - cx) * 0.25;
            const controlX = midX + (i % 2 === 0 ? perpX : -perpX);
            const controlY = midY + (i % 2 === 0 ? perpY : -perpY);

            // Create Orb Object
            const orbObj = {
                progress: 0,
                targetX: tx,
                targetY: ty,
                controlX: controlX,
                controlY: controlY
            };
            activeOrbs.push(orbObj);

            // Phase 4: Bezier Flight to Marker with power4.out
            gsap.to(orbObj, {
                progress: 1,
                duration: 0.38,
                ease: 'power4.out',
                onComplete: () => {
                    // PHASE 5: Marker Ignition & Pulse (1 -> 1.4 -> 1, 250ms)
                    const pinEl = document.getElementById(`daPin_${i}`);
                    if (pinEl) {
                        pinEl.classList.add('holo-pulse-active');
                        gsap.fromTo(pinEl,
                            { scale: 1 },
                            { scale: 1.4, duration: 0.12, yoyo: true, repeat: 1, ease: 'power2.out' }
                        );
                    }

                    // PHASE 6: Orb Dissolves into 10-15 Gold Particles (150ms)
                    spawnDissolveParticles(tx, ty);

                    // PHASE 7 & 8: Card Opens at Marker Location with Anti-Overlap Fan Layout
                    setTimeout(() => {
                        this.spawnCardAtMarkerLocation(p, i, tx, ty, cardsContainer, screenW, screenH);
                    }, 50);
                }
            });

            await new Promise(r => setTimeout(r, 70)); // Stagger 70ms
        }

        await new Promise(r => setTimeout(r, 100));
        isLoopAnimating = false;

        return markerPoints;
    }

    /**
     * Phase 8.5: Golden Constellation Laser Matrix Burst & Imploding Pulse (800ms)
     */
    phase8_5_ConstellationMatrixBurst(ctx, cx, cy, points = []) {
        return new Promise(resolve => {
            if (!ctx || points.length === 0) return resolve();

            const pins = document.querySelectorAll('.ocop-da-pin-node');
            if (pins && pins.length > 0) {
                gsap.to(pins, {
                    boxShadow: '0 0 50px #FFD54F, 0 0 80px #FFC107',
                    borderColor: '#FFFFFF',
                    scale: 1.2,
                    duration: 0.4,
                    yoyo: true,
                    repeat: 1,
                    ease: 'power2.out'
                });
            }

            // Draw Full Golden Constellation Laser Web on Canvas
            const obj = { alpha: 0.9, ringR: 300 };
            gsap.to(obj, {
                alpha: 0,
                ringR: 0,
                duration: 0.8,
                ease: 'power3.inOut',
                onUpdate: () => {
                    ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);

                    // 1. High-Speed Constellation Web Lines between all markers
                    ctx.strokeStyle = `rgba(255, 213, 79, ${obj.alpha * 0.75})`;
                    ctx.lineWidth = 1.8;

                    for (let i = 0; i < points.length; i++) {
                        const p1 = points[i];
                        const p2 = points[(i + 1) % points.length];
                        const p3 = points[(i + 5) % points.length];

                        ctx.beginPath();
                        ctx.moveTo(p1.x, p1.y);
                        ctx.lineTo(p2.x, p2.y);
                        ctx.stroke();

                        ctx.beginPath();
                        ctx.moveTo(p1.x, p1.y);
                        ctx.lineTo(p3.x, p3.y);
                        ctx.stroke();
                    }

                    // 2. Imploding Ring back to center
                    if (obj.ringR > 0) {
                        ctx.beginPath();
                        ctx.arc(cx, cy, obj.ringR, 0, Math.PI * 2);
                        ctx.strokeStyle = `rgba(255, 213, 79, ${obj.alpha})`;
                        ctx.lineWidth = 3.0;
                        ctx.stroke();
                    }
                },
                onComplete: () => {
                    ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
                    resolve();
                }
            });
        });
    }

    /**
     * Phase 8.6: Heritage Slogan Light Banner Sweep (700ms)
     */
    phase8_6_SloganBannerSweep() {
        return new Promise(resolve => {
            const sloganTextEl = document.getElementById('ocopSloganText');
            if (!sloganTextEl) return resolve();

            sloganTextEl.innerHTML = `
                <div style="font-size: 0.82rem; color: #FFD54F; text-transform: uppercase; letter-spacing: 2px; font-weight: 800; margin-bottom: 4px;">🌾 HÀNH TRÌNH NÔNG SẢN SỐ</div>
                <div style="font-size: 1.45rem; color: #FFFFFF; font-weight: 900; letter-spacing: 0.5px;">ĐÔNG ANH • TINH HOA LAN TỎA</div>
            `;
            sloganTextEl.style.display = 'block';

            gsap.fromTo(sloganTextEl,
                { opacity: 0, scale: 0.85, y: -25 },
                { 
                    opacity: 1, 
                    scale: 1, 
                    y: 0, 
                    duration: 0.5, 
                    ease: 'back.out(1.6)',
                    onComplete: () => {
                        gsap.to(sloganTextEl, {
                            opacity: 0,
                            scale: 0.95,
                            duration: 0.35,
                            delay: 0.8,
                            ease: 'power2.in',
                            onComplete: resolve
                        });
                    }
                }
            );
        });
    }

    /**
     * Phase 7 & 8: Card Opens at Marker Location with Non-Overlapping Spiral Galaxy Fan Layout
     */
    spawnCardAtMarkerLocation(p, index, tx, ty, container, screenW, screenH) {
        const badge = document.createElement('div');
        badge.className = 'ocop-domino-card-badge';
        badge.innerHTML = `
            <div class="domino-thumb">
                <img src="${p.image || ''}" alt="${p.name}">
            </div>
            <div class="domino-info">
                <div class="domino-title">${p.name}</div>
                <div class="domino-star">${p.star_rating || '⭐ OCOP 4 SAO'}</div>
            </div>
        `;

        // Compute Radial Galaxy Spiral Offset to distribute 33 cards without overlap
        const ring = index % 4; // 4 concentric rings (55px, 90px, 125px, 160px)
        const angle = (index * 68.5 * Math.PI) / 180;
        const radius = 55 + ring * 32;

        const offsetX = Math.cos(angle) * radius;
        const offsetY = Math.sin(angle) * radius;

        // Keep cards cleanly within the map viewport (responsive for mobile & desktop)
        const isMobile = screenW <= 768;
        const minX = isMobile ? 12 : 330;
        const maxX = isMobile ? (screenW - 130) : (screenW - 180);
        const minY = isMobile ? 115 : 75;
        const maxY = isMobile ? (screenH - 120) : (screenH - 50);

        const rawX = tx + offsetX - 60;
        const rawY = ty + offsetY - 18;

        const cardX = Math.min(maxX, Math.max(minX, rawX));
        const cardY = Math.min(maxY, Math.max(minY, rawY));

        badge.style.left = `${cardX}px`;
        badge.style.top = `${cardY}px`;

        container.appendChild(badge);

        // GSAP Scale 0 -> 1.05 -> 1, Opacity 0 -> 100% (Duration 240ms)
        gsap.fromTo(badge,
            { scale: 0, opacity: 0 },
            { scale: 1.05, opacity: 1, duration: 0.22, ease: 'back.out(1.4)', onComplete: () => {
                gsap.to(badge, { scale: 1, duration: 0.06, ease: 'power1.out' });
            }}
        );
    }

    /**
     * Phase 9: Heatmap / OCOP Density Fade In (Opacity 0 -> 100% in 500ms)
     */
    phase9_HeatmapFadeIn() {
        return new Promise(resolve => {
            if (window.ocopStoryController && window.ocopStoryController.heatmapLayer) {
                const layer = window.ocopStoryController.heatmapLayer;
                if (layer._container) {
                    layer._container.style.display = 'block';
                    gsap.fromTo(layer._container,
                        { opacity: 0 },
                        { opacity: 1, duration: 0.5, ease: 'power2.out' }
                    );
                }
            }
            setTimeout(resolve, 500);
        });
    }
}

window.OcopAnimationController = OcopAnimationController;
