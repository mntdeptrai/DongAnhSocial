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
        this.catchmentPolygon = null;
        this.speechSynth = null;
    }

    initMap() {
        if (this.map) return;

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
        const colors = ['#fef08a', '#fbbf24', '#a5b4fc', '#38bdf8', '#c084fc', '#ffffff'];

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

    async startStory(schoolSlug, redirectUrl) {
        if (this.isBusy) return;
        this.isBusy = true;
        this.isSkipped = false;
        this.targetUrl = redirectUrl || `/dia-diem/${schoolSlug}`;
        this.currentData = window.getSchoolStoryData(schoolSlug);

        // Open modal
        const modal = document.getElementById('storytellingModal');
        if (modal) modal.classList.add('active');

        // Init map & sparkle animation
        this.initMap();
        this.initSparkleCanvas();
        this.resetLayers();
        this.renderTimeline();

        try {
            // Stage 0: Intro
            await this.phase0_Intro();
            if (this.isSkipped) return;

            // Stage 1: Overview
            await this.phase1_Overview();
            if (this.isSkipped) return;

            // Stage 2: Dynamic iteration over all component schools
            for (let i = 0; i < this.currentData.components.length; i++) {
                await this.phase_Component(i);
                if (this.isSkipped) return;
            }

            // Stage 3: Connection & Distance
            await this.phase_Connection();
            if (this.isSkipped) return;

            // Stage 4: Merger Convergence Beam
            await this.phase_Merger();
            if (this.isSkipped) return;

            // Stage 5: New School Presentation
            await this.phase_NewSchool();
            if (this.isSkipped) return;

            // Stage 6: Transition
            await this.phase_Transition();
        } catch (err) {
            console.warn('Storytelling sequence interrupted or completed:', err);
        } finally {
            this.isBusy = false;
        }
    }

    skipStory() {
        this.isSkipped = true;
        if (this.speechSynth) this.speechSynth.cancel();

        const modal = document.getElementById('storytellingModal');
        if (modal) modal.classList.remove('active');

        if (this.targetUrl) {
            window.location.href = this.targetUrl;
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
        return new Promise(resolve => setTimeout(resolve, ms));
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
            if (idx + 1 === stepNum) item.classList.add('active');
            else if (idx + 1 < stepNum) {
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
            steps.push(`${stepIdx++}. Cơ sở ${i + 1}: ${shortName}`);
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
            progressWrap.innerHTML = steps.map((_, i) => `<div class="story-progress-dot ${i === 0 ? 'active' : ''}"></div>`).join('');
        }
    }

    async jumpToStep(stepNum) {
        // Simple step navigation support
        this.setStep(stepNum);
    }

    typeText(elementId, text, speed = 12) {
        return new Promise(resolve => {
            const el = document.getElementById(elementId);
            if (!el) return resolve();

            el.innerHTML = '';
            let i = 0;
            const timer = setInterval(() => {
                if (this.isSkipped) {
                    clearInterval(timer);
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
        this.markers.forEach(m => this.map.removeLayer(m));
        this.markers = [];
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
    }

    // ==========================================
    // STAGE 0: INTRO ANIMATION
    // ==========================================
    async phase0_Intro() {
        this.setStep(1);
        const intro = document.getElementById('storyIntroScreen');
        if (!intro) return;

        intro.classList.remove('hidden');
        document.getElementById('storyIntroTitle').innerText = `HÀNH TRÌNH HÌNH THÀNH\n${this.currentData.mergedSchool.name.toUpperCase()}`;
        document.getElementById('storyIntroSubtitle').innerText = `Tổ chức lại & Sắp xếp các cơ sở giáo dục công lập xã Đông Anh`;

        this.speak(`Hành trình hình thành ${this.currentData.mergedSchool.name}`);
        await this.sleep(1600);

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
        const circleNums = ['①', '②', '③', '④', '⑤', '⑥', '⑦', '⑧'];
        const circleChar = circleNums[index] || (index + 1).toString();
        const stepNum = 2 + index;

        this.setStep(stepNum);
        document.getElementById('storyPhaseLabel').innerText = `GIAI ĐOẠN ${stepNum}: ĐƠN VỊ SÁP NHẬP #${index + 1}`;

        // Hide old card momentarily if not first component
        const card = document.getElementById('storyGlassCard');
        if (card && index > 0) {
            card.classList.remove('show');
            await this.sleep(150);
        }

        // Create pulsing marker
        const iconHtml = `
            <div class="story-marker-icon">
                <div class="story-marker-inner">${circleChar}</div>
                <div class="story-marker-pulse"></div>
            </div>
        `;
        const customIcon = L.divIcon({
            html: iconHtml,
            className: 'custom-story-div-icon',
            iconSize: [44, 44],
            iconAnchor: [22, 22]
        });

        const m = L.marker([comp.lat, comp.lng], { icon: customIcon }).addTo(this.map);
        m.bindTooltip(`<b>${circleChar} ${comp.name}</b><br><span style="color:#a5b4fc;">(Trước sáp nhập)</span>`, {
            permanent: true,
            direction: 'top',
            className: 'story-tooltip-custom',
            offset: [0, -20]
        });
        this.markers.push(m);

        // Fly camera
        this.map.flyTo([comp.lat, comp.lng], 15.5, {
            duration: 1.0
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
            document.getElementById('storyCardPrincipal').innerText = comp.principal || 'Đang cập nhật';
            document.getElementById('storyCardPhone').innerText = comp.phone || 'Đang cập nhật';

            const actionBtn = document.getElementById('storyCardActionBtn');
            if (actionBtn) actionBtn.style.display = 'none';

            card.classList.add('show');
        }

        const msg = `Cơ sở ${index + 1}: ${comp.name} hiện tại có quy mô ${comp.classes} lớp học và ${comp.students} học sinh.`;
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
            padding: [100, 100],
            duration: 1.2
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

        // Hide distance box
        const distBox = document.getElementById('storyDistanceBox');
        if (distBox) distBox.classList.remove('show');

        // Fade out old markers
        this.markers.forEach(m => {
            if (m._icon) {
                m._icon.style.transition = 'all 0.4s ease';
                m._icon.style.opacity = '0';
                m._icon.style.transform = 'scale(0)';
            }
        });

        await this.sleep(300);

        // Add Merged School New Marker
        const mSchool = this.currentData.mergedSchool;
        const iconHtml = `
            <div class="story-marker-icon merged">
                <div class="story-marker-inner">🏫</div>
                <div class="story-marker-pulse"></div>
            </div>
        `;
        const customIcon = L.divIcon({
            html: iconHtml,
            className: 'custom-story-div-icon',
            iconSize: [52, 52],
            iconAnchor: [26, 26]
        });

        const newMarker = L.marker([mSchool.lat, mSchool.lng], { icon: customIcon }).addTo(this.map);
        newMarker.bindTooltip(`<b>✨ ${mSchool.name}</b><br><span style="color:#34d399;">(Trường mới thành lập)</span>`, {
            permanent: true,
            direction: 'top',
            className: 'story-tooltip-custom',
            offset: [0, -26]
        });
        this.markers.push(newMarker);

        // Fly camera to new school
        this.map.flyTo([mSchool.lat, mSchool.lng], 16, {
            duration: 1.0
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

        await this.sleep(1800);
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

        // Show Merged Hero Card
        const mSchool = this.currentData.mergedSchool;
        const card = document.getElementById('storyGlassCard');
        if (card) {
            document.getElementById('storyCardBadge').innerText = 'Trường mới (Sau sáp nhập)';
            document.getElementById('storyCardBadge').classList.add('merged');
            document.getElementById('storyCardImage').src = mSchool.photo;
            document.getElementById('storyCardTitle').innerText = mSchool.name;
            document.getElementById('storyCardAddress').innerHTML = `📍 ${mSchool.address}`;
            document.getElementById('storyStatClasses').innerText = `${mSchool.classes} Lớp`;
            document.getElementById('storyStatStudents').innerText = `${mSchool.students} HS`;
            document.getElementById('storyCardPrincipal').innerText = mSchool.principal;
            document.getElementById('storyCardPhone').innerText = mSchool.phone;

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

        await this.sleep(2200);
    }

    // ==========================================
    // STAGE 6: SEAMLESS TRANSITION TO DETAIL PAGE
    // ==========================================
    async phase_Transition() {
        const compCount = this.currentData.components.length;
        const stepNum = 5 + compCount;
        this.setStep(stepNum);
        document.getElementById('storyPhaseLabel').innerText = `GIAI ĐOẠN ${stepNum}: HOÀN TẤT & CHUYỂN TRANG`;

        const msg = `Đang chuyển tới trang thông tin chi tiết trường...`;
        this.speak(msg);
        await this.typeText('storyNarrativeText', msg, 12);

        // Zoom out and fade map
        this.map.zoomOut(2, { animate: true, duration: 0.8 });

        const modal = document.getElementById('storytellingModal');
        if (modal) {
            modal.style.transition = 'opacity 0.5s ease';
            modal.style.opacity = '0';
        }

        await this.sleep(500);

        if (this.targetUrl) {
            window.location.href = this.targetUrl;
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
