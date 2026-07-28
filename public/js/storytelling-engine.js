/**
 * STORYTELLING MAP ENGINE - DONG ANH PUBLIC SCHOOL MERGER
 * Leaflet.js | GSAP | Web Speech API TTS | Progressive Timeline Controller
 */

class SchoolStoryteller {
    constructor() {
        this.map = null;
        this.currentData = null;
        this.targetUrl = '';
        this.activeStep = 0;
        this.isVoiceEnabled = false;
        this.isSkipped = false;
        this.isBusy = false;
        this.markers = [];
        this.routePolyline = null;
        this.routePolylines = [];
        this.catchmentPolygon = null;
        this.speechSynth = null;
    }

    initMap() {
        if (!this.map) {
            // Initialize Leaflet map centered on Dong Anh
            this.map = L.map('storyMap', {
                zoomControl: false,
                attributionControl: false,
                fadeAnimation: true,
                zoomAnimation: true
            }).setView([21.135, 105.865], 12);

            // CartoDB Voyager bright light tile layer for crisp, modern, colorful map display
            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                maxZoom: 19,
                subdomains: 'abcd'
            }).addTo(this.map);
        }

        setTimeout(() => {
            if (this.map) {
                this.map.invalidateSize();
            }
        }, 150);
    }

    initSparkleCanvas() {
        const canvas = document.getElementById('storySparkleCanvas');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        let width = canvas.width = window.innerWidth;
        let height = canvas.height = window.innerHeight;

        const onResize = () => {
            width = canvas.width = window.innerWidth;
            height = canvas.height = window.innerHeight;
        };
        window.removeEventListener('resize', onResize);
        window.addEventListener('resize', onResize);

        const particles = [];
        const particleCount = 60;
        const colors = ['#c084fc', '#e879f9', '#818cf8', '#fbbf24', '#f472b6', '#a855f7'];

        for (let i = 0; i < particleCount; i++) {
            particles.push({
                x: Math.random() * width,
                y: Math.random() * height,
                size: Math.random() * 3 + 1,
                color: colors[Math.floor(Math.random() * colors.length)],
                speedY: -(Math.random() * 0.7 + 0.2),
                speedX: (Math.random() - 0.5) * 0.4,
                alpha: Math.random(),
                fade: Math.random() * 0.02 + 0.005
            });
        }

        const animateSparkles = () => {
            const modal = document.getElementById('storytellingModal');
            if (!modal || !modal.classList.contains('active')) return;

            ctx.clearRect(0, 0, width, height);

            particles.forEach(p => {
                p.y += p.speedY;
                p.x += p.speedX;
                p.alpha += p.fade;

                if (p.alpha > 1 || p.alpha < 0.1) {
                    p.fade = -p.fade;
                }

                if (p.y < 0) {
                    p.y = height;
                    p.x = Math.random() * width;
                }

                ctx.save();
                ctx.globalAlpha = Math.max(0, Math.min(1, p.alpha));
                ctx.fillStyle = p.color;
                ctx.shadowBlur = 12;
                ctx.shadowColor = p.color;

                ctx.beginPath();
                ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
                ctx.fill();
                ctx.restore();
            });

            requestAnimationFrame(animateSparkles);
        };

        animateSparkles();
    }

    closeAndResetModal() {
        this.isSkipped = true;
        this.isBusy = false;
        if (this.speechSynth) this.speechSynth.cancel();

        const modal = document.getElementById('storytellingModal');
        if (modal) {
            modal.classList.remove('active');
            modal.style.display = 'none';
            modal.style.opacity = '0';
            modal.style.pointerEvents = 'none';
        }

        const intro = document.getElementById('storyIntroScreen');
        if (intro) intro.classList.add('hidden');

        document.body.style.overflow = 'auto';
    }

    async startStory(schoolSlug, redirectUrl) {
        if (this.isBusy) return;
        this.isBusy = true;
        this.isSkipped = false;
        this.isManualMode = false;
        this.targetUrl = redirectUrl || `/dia-diem/${schoolSlug}`;

        try {
            this.currentData = window.getSchoolStoryData(schoolSlug);
            if (!this.currentData || !this.currentData.components || !this.currentData.components.length) {
                console.warn('Storytelling data missing for:', schoolSlug);
                window.location.href = this.targetUrl;
                return;
            }

            // Open modal
            const modal = document.getElementById('storytellingModal');
            if (modal) {
                modal.style.display = 'block';
                modal.style.opacity = '1';
                modal.style.pointerEvents = 'auto';
                modal.classList.add('active');
            }

            // Init map & sparkle animation
            this.initMap();
            this.initSparkleCanvas();
            this.resetLayers();
            this.renderTimeline();

            // Force Leaflet recalculation after modal displays
            if (this.map) {
                this.map.invalidateSize();
            }

            // Stage 0: Intro
            await this.phase0_Intro();
            if (this.isSkipped || this.isManualMode) return;

            // Stage 1: Overview
            await this.phase1_Overview();
            if (this.isSkipped || this.isManualMode) return;

            // Stage 2: Dynamic iteration over all component schools
            for (let i = 0; i < this.currentData.components.length; i++) {
                await this.phase_Component(i);
                if (this.isSkipped || this.isManualMode) return;
            }

            // Stage 3: Connection & Distance
            await this.phase_Connection();
            if (this.isSkipped || this.isManualMode) return;

            // Stage 4: Merger Convergence Beam
            await this.phase_Merger();
            if (this.isSkipped || this.isManualMode) return;

            // Stage 5: New School Presentation
            await this.phase_NewSchool();
            if (this.isSkipped || this.isManualMode) return;

            // Stage 6: Transition & Redirect
            await this.phase_Transition();
        } catch (err) {
            console.error('Storytelling error, fallback to detail:', err);
            if (this.targetUrl) {
                window.location.href = this.targetUrl;
            }
        } finally {
            this.isBusy = false;
        }
    }

    skipStory() {
        const dest = this.targetUrl;
        this.closeAndResetModal();

        if (dest) {
            window.location.href = dest;
        }
    }

    toggleVoice() {
        this.isVoiceEnabled = !this.isVoiceEnabled;
        const btn = document.getElementById('storyVoiceBtn');
        if (btn) {
            btn.innerHTML = this.isVoiceEnabled
                ? '<span>🔊</span> Trợ lý giọng nói: BẬT'
                : '<span>🔇</span> Trợ lý giọng nói: TẮT';
        }
        if (!this.isVoiceEnabled && this.speechSynth) {
            this.speechSynth.cancel();
        }
    }

    speak(text) {
        // Voice narration disabled as requested
        return;
    }

    async sleep(ms) {
        const startStep = this.activeStep;
        return new Promise(resolve => {
            if (this.isManualMode) return resolve();
            const checkTimer = setInterval(() => {
                if (this.isSkipped || this.isManualMode || this.activeStep !== startStep) {
                    clearInterval(checkTimer);
                    resolve();
                }
            }, 40);
            setTimeout(() => {
                clearInterval(checkTimer);
                resolve();
            }, ms);
        });
    }

    setStep(stepNum) {
        this.activeStep = stepNum;

        // Update progress dots
        const dots = document.querySelectorAll('.story-progress-dot');
        dots.forEach((dot, idx) => {
            if (idx + 1 === stepNum) dot.classList.add('active');
            else dot.classList.remove('active');
        });

        // Update timeline items
        const items = document.querySelectorAll('.story-timeline-item');
        items.forEach((item, idx) => {
            if (idx + 1 === stepNum) {
                item.classList.add('active');
                item.classList.remove('completed');
            } else if (idx + 1 < stepNum) {
                item.classList.remove('active');
                item.classList.add('completed');
            } else {
                item.classList.remove('active', 'completed');
            }
        });
    }

    renderTimeline() {
        const list = document.getElementById('storyTimelineList');
        if (!list) return;

        const components = this.currentData.components || [];
        let stepIdx = 1;
        const steps = [];

        steps.push(`${stepIdx++}. Khái quát quy hoạch`);

        components.forEach((comp, i) => {
            const shortName = comp.name.replace(/^Trường\s+/, '');
            steps.push(`${stepIdx++}. ${shortName}`);
        });

        steps.push(`${stepIdx++}. Tuyến đường kết nối`);
        steps.push(`${stepIdx++}. Hợp nhất thương hiệu`);
        steps.push(`${stepIdx++}. Trường mới: ${this.currentData.mergedSchool.name}`);
        steps.push(`${stepIdx++}. Hoàn tất & Chuyển trang`);

        list.innerHTML = steps.map((st, i) => `
            <div class="story-timeline-item ${i === 0 ? 'active' : ''}" onclick="window.storyteller.jumpToStep(${i + 1})">
                <div class="story-timeline-num">${i + 1}</div>
                <div class="story-timeline-text">${st}</div>
            </div>
        `).join('');

        const progressWrap = document.querySelector('.story-progress-bar-wrap');
        if (progressWrap) {
            progressWrap.innerHTML = steps.map((_, i) => `
                <div class="story-progress-dot ${i === 0 ? 'active' : ''}" onclick="window.storyteller.jumpToStep(${i + 1})" style="cursor: pointer;" title="Chuyển tới bước ${i + 1}"></div>
            `).join('');
        }
    }

    renderComponentMarkers(count = (this.currentData?.components || []).length) {
        this.markers.forEach(m => {
            try {
                if (m.getTooltip()) {
                    m.closeTooltip();
                    m.unbindTooltip();
                }
            } catch (e) { }
            this.map.removeLayer(m);
        });
        this.markers = [];
        document.querySelectorAll('.leaflet-tooltip').forEach(el => el.remove());

        const components = this.currentData?.components || [];
        const max = Math.min(count, components.length);
        for (let i = 0; i < max; i++) {
            const comp = components[i];
            const compNum = i + 1;
            const iconHtml = `
                <div class="story-marker-icon">
                    <div class="story-marker-inner">${compNum}</div>
                    <div class="story-marker-pulse"></div>
                </div>
            `;
            const customIcon = L.divIcon({
                html: iconHtml,
                className: 'custom-story-div-icon',
                iconSize: [48, 48],
                iconAnchor: [24, 24]
            });

            const m = L.marker([comp.lat, comp.lng], { icon: customIcon }).addTo(this.map);
            m.bindTooltip(`<b>📍 ${compNum}. ${comp.name}</b><span class="story-tooltip-sublabel">(Trước sáp nhập)</span>`, {
                permanent: true,
                direction: 'top',
                className: 'story-tooltip-custom story-tooltip-comp',
                offset: [0, -24]
            });
            this.markers.push(m);
        }
    }

    async jumpToStep(stepNum) {
        if (!this.currentData) return;

        const compCount = (this.currentData.components || []).length;
        const totalSteps = 5 + compCount;

        if (stepNum < 1 || stepNum > totalSteps) return;

        // User clicked a step manually: immediately stop auto-play progression!
        this.isManualMode = true;

        // Reset step state and clear layers
        this.setStep(stepNum);
        this.resetLayers();

        if (stepNum === 1) {
            await this.phase1_Overview();
        } else if (stepNum >= 2 && stepNum < 2 + compCount) {
            const compIndex = stepNum - 2;
            this.renderComponentMarkers(compIndex + 1);
            await this.phase_Component(compIndex);
        } else if (stepNum === 2 + compCount) {
            this.renderComponentMarkers(compCount);
            await this.phase_Connection();
        } else if (stepNum === 3 + compCount) {
            this.renderComponentMarkers(compCount);
            await this.phase_Merger();
        } else if (stepNum === 4 + compCount) {
            await this.phase_NewSchool();
        } else if (stepNum === 5 + compCount) {
            await this.phase_Transition();
        }
    }

    typeText(elementId, text, speed = 12) {
        return new Promise(resolve => {
            const el = document.getElementById(elementId);
            if (!el) return resolve();

            if (this.isManualMode) {
                el.innerHTML = text;
                return resolve();
            }

            const startStep = this.activeStep;
            el.innerHTML = '';
            let i = 0;
            const timer = setInterval(() => {
                if (this.isSkipped || this.activeStep !== startStep) {
                    clearInterval(timer);
                    el.innerHTML = text;
                    return resolve();
                }
                if (i < text.length) {
                    el.innerHTML += text.charAt(i);
                    i++;
                } else {
                    clearInterval(timer);
                    resolve();
                }
            }, speed);
        });
    }

    resetLayers() {
        this.markers.forEach(m => {
            try {
                if (m.getTooltip()) m.unbindTooltip();
            } catch (e) { }
            this.map.removeLayer(m);
        });
        this.markers = [];

        if (this.routePolylines) {
            this.routePolylines.forEach(p => this.map.removeLayer(p));
        }
        this.routePolylines = [];

        if (this.routePolyline) {
            this.map.removeLayer(this.routePolyline);
            this.routePolyline = null;
        }
        if (this.catchmentPolygon) {
            this.map.removeLayer(this.catchmentPolygon);
            this.catchmentPolygon = null;
        }

        // Hide cards
        const card = document.getElementById('storyGlassCard');
        if (card) card.classList.remove('show');

        const distBox = document.getElementById('storyDistanceBox');
        if (distBox) distBox.classList.remove('show');

        const annCard = document.getElementById('storyAnnouncementCard');
        if (annCard) annCard.classList.remove('show');

        const celBanner = document.getElementById('storyCelebrationBanner');
        if (celBanner) celBanner.classList.remove('show');

        document.querySelectorAll('.leaflet-tooltip').forEach(el => el.remove());
    }

    launchFireworks(durationMs = 2400) {
        const canvas = document.getElementById('storySparkleCanvas');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        const width = canvas.width = window.innerWidth;
        const height = canvas.height = window.innerHeight;

        const fireworks = [];
        const colors = ['#f59e0b', '#ec4899', '#8b5cf6', '#3b82f6', '#10b981', '#fbbf24', '#a855f7'];

        for (let f = 0; f < 5; f++) {
            const centerX = width * (0.2 + Math.random() * 0.6);
            const centerY = height * (0.2 + Math.random() * 0.35);
            const pCount = 24;

            for (let i = 0; i < pCount; i++) {
                const angle = (Math.PI * 2 / pCount) * i;
                const speed = Math.random() * 7 + 2;
                fireworks.push({
                    x: centerX,
                    y: centerY,
                    vx: Math.cos(angle) * speed,
                    vy: Math.sin(angle) * speed,
                    color: colors[Math.floor(Math.random() * colors.length)],
                    size: Math.random() * 3.5 + 2,
                    alpha: 1,
                    decay: Math.random() * 0.02 + 0.015,
                    gravity: 0.12
                });
            }
        }

        const startTime = Date.now();
        const animateFireworks = () => {
            if (Date.now() - startTime > durationMs) return;

            ctx.clearRect(0, 0, width, height);

            fireworks.forEach(p => {
                p.x += p.vx;
                p.y += p.vy;
                p.vy += p.gravity;
                p.alpha -= p.decay;

                if (p.alpha > 0) {
                    ctx.save();
                    ctx.globalAlpha = Math.max(0, p.alpha);
                    ctx.fillStyle = p.color;
                    ctx.beginPath();
                    ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
                    ctx.fill();
                    ctx.restore();
                }
            });

            requestAnimationFrame(animateFireworks);
        };

        animateFireworks();
    }



    // ==========================================
    // STAGE 0: INTRO ANIMATION
    // ==========================================
    async phase0_Intro() {
        this.setStep(1);
        const intro = document.getElementById('storyIntroScreen');
        if (!intro) return;

        intro.classList.remove('hidden');
        const schoolNameUpper = (this.currentData.mergedSchool.name || '').toUpperCase();
        document.getElementById('storyIntroTitle').innerHTML = `HÀNH TRÌNH HÌNH THÀNH<br><span class="story-intro-highlight">${schoolNameUpper}</span>`;
        document.getElementById('storyIntroSubtitle').innerText = `Tổ chức lại & Sắp xếp các cơ sở giáo dục công lập xã Đông Anh`;

        this.speak(`Hành trình hình thành ${this.currentData.mergedSchool.name}`);
        await this.sleep(2600);

        intro.classList.add('hidden');
        await this.sleep(400);
    }

    // ==========================================
    // STAGE 1: ADMINISTRATIVE OVERVIEW
    // ==========================================
    async phase1_Overview() {
        this.setStep(1);
        document.getElementById('storyPhaseLabel').innerText = 'GIAI ĐOẠN 1: KHÁI QUÁT KHU VỰC';

        // Fly camera down to Dong Anh
        this.map.flyTo([21.135, 105.865], 12.5, {
            duration: 1.2,
            easeLinearity: 0.25
        });

        const narrativeMsg = `Đang tải ranh giới hành chính xã Đông Anh. Chạm vào các điểm trên bản đồ để khám phá hành trình sáp nhập ${this.currentData.mergedSchool.name}.`;
        this.speak(narrativeMsg);
        await this.typeText('storyNarrativeText', narrativeMsg, 12);

        await this.sleep(1200);
    }

    // ==========================================
    // STAGE 2: COMPONENT SCHOOL N (DYNAMIC)
    // ==========================================
    async phase_Component(index) {
        const comp = this.currentData.components[index];
        const compNum = index + 1;
        const stepNum = 2 + index;

        this.setStep(stepNum);
        document.getElementById('storyPhaseLabel').innerText = `GIAI ĐOẠN ${stepNum}: ĐƠN VỊ SÁP NHẬP #${compNum}`;

        // Hide old card momentarily if not first component
        const card = document.getElementById('storyGlassCard');
        if (card && index > 0) {
            card.classList.remove('show');
            await this.sleep(150);
        }

        // Create pulsing marker if not already present
        if (this.markers.length < compNum) {
            const iconHtml = `
                <div class="story-marker-icon">
                    <div class="story-marker-inner">${compNum}</div>
                    <div class="story-marker-pulse"></div>
                </div>
            `;
            const customIcon = L.divIcon({
                html: iconHtml,
                className: 'custom-story-div-icon',
                iconSize: [48, 48],
                iconAnchor: [24, 24]
            });

            const m = L.marker([comp.lat, comp.lng], { icon: customIcon }).addTo(this.map);
            m.bindTooltip(`<b>📍 ${compNum}. ${comp.name}</b><span class="story-tooltip-sublabel">(Trước sáp nhập)</span>`, {
                permanent: true,
                direction: 'top',
                className: 'story-tooltip-custom story-tooltip-comp',
                offset: [0, -24]
            });
            this.markers.push(m);
        }

        // Fly camera centered in open viewport space (clearing 340px left sidebar and 440px right glass card)
        const targetPt = L.latLng(comp.lat, comp.lng);
        const cBounds = L.latLngBounds([targetPt, targetPt]);
        this.map.flyToBounds(cBounds, {
            paddingTopLeft: [360, 120],
            paddingBottomRight: [480, 160],
            maxZoom: 15.5,
            duration: this.isManualMode ? 0.4 : 1.0
        });

        // Show Glass Card UI
        if (card) {
            document.getElementById('storyCardBadge').innerText = `Đơn vị sáp nhập #${index + 1}`;
            document.getElementById('storyCardBadge').classList.remove('merged');
            document.getElementById('storyCardImage').src = comp.photo;
            document.getElementById('storyCardTitle').innerText = comp.name;
            document.getElementById('storyCardAddress').innerHTML = `📍 ${comp.address}`;
            document.getElementById('storyStatClasses').innerText = `${comp.classes} Lớp`;
            document.getElementById('storyStatStudents').innerText = `${comp.students} HS`;
            const boardSec = document.getElementById('storyBoardSection');
            if (boardSec) boardSec.style.display = 'none';

            const actionBtn = document.getElementById('storyCardActionBtn');
            if (actionBtn) actionBtn.style.display = 'none';

            card.classList.add('show');
        }

        const msg = `${comp.name} hiện tại có quy mô ${comp.classes} lớp học và ${comp.students} học sinh.`;
        this.speak(msg);
        await this.typeText('storyNarrativeText', msg, 12);

        await this.sleep(1600);
    }

    // ==========================================
    // STAGE 3: CONNECTION & ROUTE DISTANCE
    // ==========================================
    async phase_Connection() {
        const compCount = this.currentData.components.length;
        const stepNum = 2 + compCount;
        this.setStep(stepNum);
        document.getElementById('storyPhaseLabel').innerText = `GIAI ĐOẠN ${stepNum}: TIẾP CẬN & KẾT NỐI HẠ TẦNG`;

        // Hide Glass Card
        const card = document.getElementById('storyGlassCard');
        if (card) card.classList.remove('show');

        // Zoom out to fit all markers
        const latLngs = this.markers.map(m => m.getLatLng());
        latLngs.push([this.currentData.mergedSchool.lat, this.currentData.mergedSchool.lng]);

        const bounds = L.latLngBounds(latLngs);
        this.map.flyToBounds(bounds, {
            paddingTopLeft: [360, 120],
            paddingBottomRight: [140, 160],
            duration: this.isManualMode ? 0.4 : 1.2
        });

        await this.sleep(500);

        // Draw animated Polyline route connecting all component schools
        if (compCount >= 2) {
            const points = this.currentData.components.map(c => [c.lat, c.lng]);

            this.routePolyline = L.polyline(points, {
                color: '#38bdf8',
                weight: 4,
                opacity: 0.9,
                dashArray: '10, 10'
            }).addTo(this.map);

            // Draw catchment zone polygon
            const lats = points.map(p => p[0]);
            const lngs = points.map(p => p[1]);
            const minLat = Math.min(...lats) - 0.003;
            const maxLat = Math.max(...lats) + 0.003;
            const minLng = Math.min(...lngs) - 0.003;
            const maxLng = Math.max(...lngs) + 0.003;

            this.catchmentPolygon = L.polygon([
                [maxLat, minLng],
                [maxLat, maxLng],
                [minLat, maxLng],
                [minLat, minLng]
            ], {
                color: '#6366f1',
                fillColor: '#818cf8',
                fillOpacity: 0.12,
                weight: 2,
                dashArray: '5, 5'
            }).addTo(this.map);
        }

        // Show floating distance card
        const distBox = document.getElementById('storyDistanceBox');
        if (distBox) {
            document.getElementById('storyDistVal').innerText = this.currentData.distanceText;
            document.getElementById('storyDurationVal').innerText = this.currentData.durationText;
            distBox.classList.add('show');
        }

        const msg = `Khoảng cách kết nối giữa ${compCount} cơ sở là ${this.currentData.distanceText}, thời gian di chuyển khoảng ${this.currentData.durationText}. Hạ tầng giao thông kết nối hoàn hảo.`;
        this.speak(msg);
        await this.typeText('storyNarrativeText', msg, 12);

        await this.sleep(1800);
    }

    // ==========================================
    // STAGE 4: MERGER & CONVERGENCE
    // ==========================================
    async phase_Merger() {
        const compCount = this.currentData.components.length;
        const stepNum = 3 + compCount;
        this.setStep(stepNum);
        document.getElementById('storyPhaseLabel').innerText = `GIAI ĐOẠN ${stepNum}: TỔ CHỨC LẠI & SÁP NHẬP`;

        // Clean up all previous layers (markers, route polylines, catchment polygons, tooltips)
        this.resetLayers();

        // Add Merged School New Marker
        const mSchool = this.currentData.mergedSchool;
        const iconHtml = `
            <div class="story-marker-icon merged">
                <div class="story-marker-inner">🏫</div>
                <div class="story-marker-pulse"></div>
                <div class="story-marker-pulse pulse-ring-2"></div>
            </div>
        `;
        const customIcon = L.divIcon({
            html: iconHtml,
            className: 'custom-story-div-icon',
            iconSize: [76, 76],
            iconAnchor: [38, 38]
        });

        const newMarker = L.marker([mSchool.lat, mSchool.lng], { icon: customIcon }).addTo(this.map);
        this.markers.push(newMarker);

        // Fly camera to new school centered in open viewport space
        const mPt = L.latLng(mSchool.lat, mSchool.lng);
        const mBounds = L.latLngBounds([mPt, mPt]);
        this.map.flyToBounds(mBounds, {
            paddingTopLeft: [360, 120],
            paddingBottomRight: [140, 160],
            maxZoom: 16,
            duration: this.isManualMode ? 0.4 : 1.0
        });

        // Show Flash Announcement Overlay
        const annCard = document.getElementById('storyAnnouncementCard');
        if (annCard) {
            document.getElementById('storyAnnounceTitle').innerText = mSchool.name;
            annCard.classList.add('show');
        }

        const msg = `Hợp nhất các đơn vị thành lập ${mSchool.name}. Tối ưu hóa quy mô và nâng cao chất lượng dạy và học.`;
        this.speak(msg);
        await this.typeText('storyNarrativeText', msg, 12);

        await this.sleep(2400);
    }

    // ==========================================
    // STAGE 5: NEW MERGED SCHOOL HERO PRESENTATION
    // ==========================================
    async phase_NewSchool() {
        const compCount = this.currentData.components.length;
        const stepNum = 4 + compCount;
        this.setStep(stepNum);
        document.getElementById('storyPhaseLabel').innerText = `GIAI ĐOẠN ${stepNum}: TRƯỜNG MỚI HÌNH THÀNH`;

        // Hide announcement card
        const annCard = document.getElementById('storyAnnouncementCard');
        if (annCard) annCard.classList.remove('show');

        // Clear and re-render clean state for new school phase
        this.resetLayers();

        // 1. Add Merged School Marker (Center, Large 76px, Z-Index 1000)
        const mSchool = this.currentData.mergedSchool;
        const iconHtml = `
            <div class="story-marker-icon merged">
                <div class="story-marker-inner">🏫</div>
                <div class="story-marker-pulse"></div>
                <div class="story-marker-pulse pulse-ring-2"></div>
            </div>
        `;
        const customIcon = L.divIcon({
            html: iconHtml,
            className: 'custom-story-div-icon',
            iconSize: [76, 76],
            iconAnchor: [38, 38]
        });

        const mergedMarker = L.marker([mSchool.lat, mSchool.lng], {
            icon: customIcon,
            zIndexOffset: 1000
        }).addTo(this.map);
        mergedMarker.bindTooltip(`<b>✨ ${mSchool.name}</b><span class="story-tooltip-sublabel">🌟 (Trường mới sau sáp nhập)</span>`, {
            permanent: true,
            direction: 'top',
            className: 'story-tooltip-custom story-tooltip-merged',
            offset: [0, -44]
        });
        this.markers.push(mergedMarker);

        // 2. Add All Component School Markers with Opposing Tooltip Directions (Zero Overlap!)
        const components = this.currentData.components || [];
        components.forEach((comp, idx) => {
            const compNum = idx + 1;
            const compIconHtml = `
                <div class="story-marker-icon">
                    <div class="story-marker-inner">${compNum}</div>
                    <div class="story-marker-pulse"></div>
                </div>
            `;
            const compCustomIcon = L.divIcon({
                html: compIconHtml,
                className: 'custom-story-div-icon',
                iconSize: [48, 48],
                iconAnchor: [24, 24]
            });

            const m = L.marker([comp.lat, comp.lng], { icon: compCustomIcon }).addTo(this.map);

            // Component 1 tooltip opens BOTTOM, Component 2 opens TOP to prevent tooltip overlap!
            const tDir = (idx === 0) ? 'bottom' : 'top';
            const tOff = (idx === 0) ? [0, 24] : [0, -24];

            m.bindTooltip(`<b>📍 ${compNum}. ${comp.name}</b><span class="story-tooltip-sublabel">(Trước sáp nhập)</span>`, {
                permanent: true,
                direction: tDir,
                className: 'story-tooltip-custom story-tooltip-comp',
                offset: tOff
            });
            this.markers.push(m);

            if (mSchool.lat && mSchool.lng) {
                const connLine = L.polyline([
                    [comp.lat, comp.lng],
                    [mSchool.lat, mSchool.lng]
                ], {
                    color: '#c084fc',
                    weight: 3.5,
                    opacity: 0.85,
                    dashArray: '8, 8'
                }).addTo(this.map);
                this.routePolylines.push(connLine);
            }
        });

        // Fit map camera bounds to frame all component schools and the new merged school together
        const allPoints = [
            [mSchool.lat, mSchool.lng],
            ...components.map(c => [c.lat, c.lng])
        ];
        this.map.fitBounds(allPoints, {
            paddingTopLeft: [360, 120],
            paddingBottomRight: [480, 160],
            maxZoom: 15.5
        });

        // Show Merged Hero Card
        const card = document.getElementById('storyGlassCard');
        if (card) {
            document.getElementById('storyCardBadge').innerText = 'Trường mới (Sau sáp nhập)';
            document.getElementById('storyCardBadge').classList.add('merged');
            document.getElementById('storyCardImage').src = mSchool.photo;
            document.getElementById('storyCardTitle').innerText = mSchool.name;
            document.getElementById('storyCardAddress').innerHTML = `📍 ${mSchool.address}`;
            document.getElementById('storyStatClasses').innerText = `${mSchool.classes} Lớp`;
            document.getElementById('storyStatStudents').innerText = `${mSchool.students} HS`;
            const boardSec = document.getElementById('storyBoardSection');
            if (boardSec) {
                boardSec.style.display = 'block';
                const gridEl = document.getElementById('storyBoardGrid');
                const boardItems = mSchool.board || [
                    { role: 'Hiệu trưởng:', name: mSchool.principal || 'Đang cập nhật' },
                    { role: 'Phó Hiệu trưởng 1:', name: mSchool.vicePrincipal1 || 'Đang cập nhật' },
                    { role: 'Phó Hiệu trưởng 2:', name: mSchool.vicePrincipal2 || 'Đang cập nhật' }
                ];
                if (gridEl) {
                    gridEl.innerHTML = boardItems.map(item => `
                        <div class="story-board-item">
                            <span class="story-board-role">${item.role}</span>
                            <span class="story-board-name">${item.name}</span>
                        </div>
                    `).join('');
                }
            }

            const actionBtn = document.getElementById('storyCardActionBtn');
            if (actionBtn) {
                actionBtn.style.display = 'flex';
                actionBtn.innerHTML = '<span>🔍 Tra cứu chi tiết trường mới</span> ➔';
            }

            card.classList.add('show');
        }

        const msg = `${mSchool.name} chính thức hình thành với tổng quy mô ${mSchool.classes} lớp học, ${mSchool.students} học sinh (${mSchool.ratio}).`;
        this.speak(msg);
        await this.typeText('storyNarrativeText', msg, 12);

        await this.sleep(3200);
    }

    // ==========================================
    // STAGE 6: SEAMLESS TRANSITION TO DETAIL PAGE
    // ==========================================
    async phase_Transition() {
        const compCount = this.currentData.components.length;
        const stepNum = 5 + compCount;
        this.setStep(stepNum);
        document.getElementById('storyPhaseLabel').innerText = `GIAI ĐOẠN ${stepNum}: HOÀN TẤT & CHUYỂN TRANG`;

        // Launch Fireworks Celebration Explosion!
        this.launchFireworks(3200);

        // Show Celebration Banner
        const celBanner = document.getElementById('storyCelebrationBanner');
        if (celBanner) {
            celBanner.classList.add('show');
        }

        const mSchool = this.currentData.mergedSchool;
        const msg = `🎉 CHÀO MỪNG ${mSchool.name.toUpperCase()} CHÍNH THỨC ĐI VÀO HOẠT ĐỘNG! Chúc mừng đơn vị mới sáp nhập. Đang chuyển tới trang chi tiết...`;
        this.speak(msg);
        await this.typeText('storyNarrativeText', msg, 12);

        await this.sleep(2800);

        // Zoom out and fade map
        this.map.zoomOut(2, { animate: true, duration: 0.8 });

        const modal = document.getElementById('storytellingModal');
        if (modal) {
            modal.style.transition = 'opacity 0.6s ease';
            modal.style.opacity = '0';
        }

        await this.sleep(600);

        let dest = this.targetUrl;
        if (dest) {
            dest += (dest.includes('?') ? '&' : '?') + 'celebrate=1';
        }

        this.closeAndResetModal();

        if (dest) {
            window.location.href = dest;
        }
    }
}

// Global Singleton Instance
window.storyteller = new SchoolStoryteller();

/**
 * Public Trigger Function
 */
window.openSchoolStoryteller = function (schoolSlug, redirectUrl) {
    if (window.storyteller) {
        window.storyteller.startStory(schoolSlug, redirectUrl);
    } else {
        window.location.href = redirectUrl || `/dia-diem/${schoolSlug}`;
    }
};

/**
 * Automatic BFCache & History Back Reset Handlers
 * Solves browser back-button freezing when returning from school detail page
 */
window.addEventListener('pageshow', function (event) {
    if (window.storyteller) {
        window.storyteller.closeAndResetModal();
    }
});

window.addEventListener('popstate', function () {
    if (window.storyteller) {
        window.storyteller.closeAndResetModal();
    }
});
