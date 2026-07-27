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

        // Dark Matter CartoDB / Esri Satellite base tile layer for high-end cinematic display
        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            maxZoom: 19,
            subdomains: 'abcd'
        }).addTo(this.map);
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

        // Init map if needed
        this.initMap();
        this.resetLayers();
        this.renderTimeline();

        try {
            // Stage 0: Intro
            await this.phase0_Intro();
            if (this.isSkipped) return;

            // Stage 1: Overview
            await this.phase1_Overview();
            if (this.isSkipped) return;

            // Stage 2: Component 1
            await this.phase2_Component1();
            if (this.isSkipped) return;

            // Stage 3: Component 2 (and 3 if exists)
            await this.phase3_Component2();
            if (this.isSkipped) return;

            // Stage 4: Connection & Distance
            await this.phase4_Connection();
            if (this.isSkipped) return;

            // Stage 5: Merger Convergence Beam
            await this.phase5_Merger();
            if (this.isSkipped) return;

            // Stage 6: New School Presentation
            await this.phase6_NewSchool();
            if (this.isSkipped) return;

            // Stage 7: Transition
            await this.phase7_Transition();
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

        const compCount = this.currentData.components.length;
        const steps = [
            '1. Khái quát quy hoạch',
            `2. Cơ sở 1: ${this.currentData.components[0].name.replace('Trường ', '')}`,
            compCount > 1 ? `3. Cơ sở 2: ${this.currentData.components[1].name.replace('Trường ', '')}` : '3. Cơ sở bổ sung',
            '4. Tuyến đường kết nối',
            '5. Hợp nhất thương hiệu',
            `6. Trường mới: ${this.currentData.mergedSchool.name}`,
            '7. Hoàn tất & Chuyển trang'
        ];

        list.innerHTML = steps.map((st, i) => `
            <div class="story-timeline-item ${i === 0 ? 'active' : ''}" onclick="window.storyteller.jumpToStep(${i + 1})">
                <div class="story-timeline-num">${i + 1}</div>
                <div class="story-timeline-text">${st}</div>
            </div>
        `).join('');
    }

    async jumpToStep(stepNum) {
        // Simple step navigation support
        this.setStep(stepNum);
    }

    typeText(elementId, text, speed = 25) {
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
        await this.sleep(2600);

        intro.classList.add('hidden');
        await this.sleep(600);
    }

    // ==========================================
    // STAGE 1: ADMINISTRATIVE OVERVIEW
    // ==========================================
    async phase1_Overview() {
        this.setStep(1);
        document.getElementById('storyPhaseLabel').innerText = 'GIAI ĐOẠN 1: KHÁI QUÁT KHU VỰC';
        
        // Fly camera down to Dong Anh
        this.map.flyTo([21.135, 105.865], 12.5, {
            duration: 2.2,
            easeLinearity: 0.25
        });

        const narrativeMsg = `Đang tải ranh giới hành chính xã Đông Anh. Chạm vào các điểm trên bản đồ để khám phá hành trình sáp nhập ${this.currentData.mergedSchool.name}.`;
        this.speak(narrativeMsg);
        await this.typeText('storyNarrativeText', narrativeMsg, 20);

        await this.sleep(2500);
    }

    // ==========================================
    // STAGE 2: COMPONENT SCHOOL 1
    // ==========================================
    async phase2_Component1() {
        this.setStep(2);
        document.getElementById('storyPhaseLabel').innerText = 'GIAI ĐOẠN 2: ĐƠN VỊ SÁP NHẬP #1';

        const comp1 = this.currentData.components[0];
        
        // Create pulsing marker
        const iconHtml = `
            <div class="story-marker-icon">
                <div class="story-marker-inner">①</div>
                <div class="story-marker-pulse"></div>
            </div>
        `;
        const customIcon = L.divIcon({
            html: iconHtml,
            className: 'custom-story-div-icon',
            iconSize: [44, 44],
            iconAnchor: [22, 22]
        });

        const m1 = L.marker([comp1.lat, comp1.lng], { icon: customIcon }).addTo(this.map);
        m1.bindTooltip(`<b>① ${comp1.name}</b><br><span style="color:#a5b4fc;">(Trước sáp nhập)</span>`, {
            permanent: true,
            direction: 'top',
            className: 'story-tooltip-custom',
            offset: [0, -20]
        });
        this.markers.push(m1);

        // Fly camera
        this.map.flyTo([comp1.lat, comp1.lng], 15.5, {
            duration: 1.8
        });

        // Show Glass Card UI
        const card = document.getElementById('storyGlassCard');
        if (card) {
            document.getElementById('storyCardBadge').innerText = 'Đơn vị sáp nhập #1';
            document.getElementById('storyCardBadge').classList.remove('merged');
            document.getElementById('storyCardImage').src = comp1.photo;
            document.getElementById('storyCardTitle').innerText = comp1.name;
            document.getElementById('storyCardAddress').innerHTML = `📍 ${comp1.address}`;
            document.getElementById('storyStatClasses').innerText = `${comp1.classes} Lớp`;
            document.getElementById('storyStatStudents').innerText = `${comp1.students} HS`;
            document.getElementById('storyCardPrincipal').innerText = comp1.principal;
            document.getElementById('storyCardPhone').innerText = comp1.phone;

            card.classList.add('show');
        }

        const msg = `Cơ sở 1: ${comp1.name} hiện tại có quy mô ${comp1.classes} lớp học và ${comp1.students} học sinh.`;
        this.speak(msg);
        await this.typeText('storyNarrativeText', msg, 20);

        await this.sleep(3200);
    }

    // ==========================================
    // STAGE 3: COMPONENT SCHOOL 2
    // ==========================================
    async phase3_Component2() {
        this.setStep(3);
        document.getElementById('storyPhaseLabel').innerText = 'GIAI ĐOẠN 3: ĐƠN VỊ SÁP NHẬP #2';

        if (this.currentData.components.length < 2) {
            const msgSingle = `Đơn vị giữ nguyên vị trí và nâng cấp chuẩn Quốc gia chất lượng cao.`;
            this.speak(msgSingle);
            await this.typeText('storyNarrativeText', msgSingle, 20);
            await this.sleep(2000);
            return;
        }

        const comp2 = this.currentData.components[1];

        // Hide old card momentarily
        const card = document.getElementById('storyGlassCard');
        if (card) card.classList.remove('show');
        await this.sleep(300);

        // Create Marker 2
        const iconHtml = `
            <div class="story-marker-icon">
                <div class="story-marker-inner">②</div>
                <div class="story-marker-pulse"></div>
            </div>
        `;
        const customIcon = L.divIcon({
            html: iconHtml,
            className: 'custom-story-div-icon',
            iconSize: [44, 44],
            iconAnchor: [22, 22]
        });

        const m2 = L.marker([comp2.lat, comp2.lng], { icon: customIcon }).addTo(this.map);
        m2.bindTooltip(`<b>② ${comp2.name}</b><br><span style="color:#a5b4fc;">(Trước sáp nhập)</span>`, {
            permanent: true,
            direction: 'top',
            className: 'story-tooltip-custom',
            offset: [0, -20]
        });
        this.markers.push(m2);

        // Fly camera
        this.map.flyTo([comp2.lat, comp2.lng], 15.5, {
            duration: 1.8
        });

        // Populate Card 2
        if (card) {
            document.getElementById('storyCardBadge').innerText = 'Đơn vị sáp nhập #2';
            document.getElementById('storyCardBadge').classList.remove('merged');
            document.getElementById('storyCardImage').src = comp2.photo;
            document.getElementById('storyCardTitle').innerText = comp2.name;
            document.getElementById('storyCardAddress').innerHTML = `📍 ${comp2.address}`;
            document.getElementById('storyStatClasses').innerText = `${comp2.classes} Lớp`;
            document.getElementById('storyStatStudents').innerText = `${comp2.students} HS`;
            document.getElementById('storyCardPrincipal').innerText = comp2.principal;
            document.getElementById('storyCardPhone').innerText = comp2.phone;

            card.classList.add('show');
        }

        const msg = `Cơ sở 2 được tổ chức lại: ${comp2.name} với quy mô ${comp2.classes} lớp và ${comp2.students} học sinh.`;
        this.speak(msg);
        await this.typeText('storyNarrativeText', msg, 20);

        await this.sleep(3200);
    }

    // ==========================================
    // STAGE 4: CONNECTION & ROUTE DISTANCE
    // ==========================================
    async phase4_Connection() {
        this.setStep(4);
        document.getElementById('storyPhaseLabel').innerText = 'GIAI ĐOẠN 4: TIẾP CẬN & KẾT NỐI HẠ TẦNG';

        // Hide Glass Card
        const card = document.getElementById('storyGlassCard');
        if (card) card.classList.remove('show');

        // Zoom out to fit both markers
        const latLngs = this.markers.map(m => m.getLatLng());
        latLngs.push([this.currentData.mergedSchool.lat, this.currentData.mergedSchool.lng]);
        
        const bounds = L.latLngBounds(latLngs);
        this.map.flyToBounds(bounds, {
            padding: [100, 100],
            duration: 2.0
        });

        await this.sleep(1000);

        // Draw animated Polyline route
        if (this.currentData.components.length >= 2) {
            const p1 = [this.currentData.components[0].lat, this.currentData.components[0].lng];
            const p2 = [this.currentData.components[1].lat, this.currentData.components[1].lng];
            
            this.routePolyline = L.polyline([p1, p2], {
                color: '#38bdf8',
                weight: 4,
                opacity: 0.9,
                dashArray: '10, 10'
            }).addTo(this.map);

            // Draw catchment zone polygon
            this.catchmentPolygon = L.polygon([
                [p1[0] + 0.003, p1[1] - 0.003],
                [p2[0] + 0.003, p2[1] + 0.003],
                [p2[0] - 0.003, p2[1] + 0.003],
                [p1[0] - 0.003, p1[1] - 0.003]
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

        const msg = `Khoảng cách giữa hai cơ sở là ${this.currentData.distanceText}, thời gian di chuyển khoảng ${this.currentData.durationText}. Hạ tầng giao thông kết nối hoàn hảo.`;
        this.speak(msg);
        await this.typeText('storyNarrativeText', msg, 20);

        await this.sleep(3500);
    }

    // ==========================================
    // STAGE 5: MERGER & PARTICLE CONVERGENCE
    // ==========================================
    async phase5_Merger() {
        this.setStep(5);
        document.getElementById('storyPhaseLabel').innerText = 'GIAI ĐOẠN 5: TỔ CHỨC LẠI & SÁP NHẬP';

        // Hide distance box
        const distBox = document.getElementById('storyDistanceBox');
        if (distBox) distBox.classList.remove('show');

        // Fade out old markers
        this.markers.forEach(m => {
            if (m._icon) {
                m._icon.style.transition = 'all 0.8s ease';
                m._icon.style.opacity = '0';
                m._icon.style.transform = 'scale(0)';
            }
        });

        await this.sleep(600);

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
            duration: 1.8
        });

        // Show Flash Announcement Overlay
        const annCard = document.getElementById('storyAnnouncementCard');
        if (annCard) {
            document.getElementById('storyAnnounceTitle').innerText = mSchool.name;
            annCard.classList.add('show');
        }

        const msg = `Hợp nhất các đơn vị thành lập ${mSchool.name}. Tối ưu hóa quy mô và nâng cao chất lượng dạy và học.`;
        this.speak(msg);
        await this.typeText('storyNarrativeText', msg, 20);

        await this.sleep(3500);
    }

    // ==========================================
    // STAGE 6: NEW MERGED SCHOOL HERO PRESENTATION
    // ==========================================
    async phase6_NewSchool() {
        this.setStep(6);
        document.getElementById('storyPhaseLabel').innerText = 'GIAI ĐOẠN 6: TRƯỜNG MỚI HÌNH THÀNH';

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

            card.classList.add('show');
        }

        const msg = `${mSchool.name} chính thức hình thành với tổng quy mô ${mSchool.classes} lớp học, ${mSchool.students} học sinh (${mSchool.ratio}).`;
        this.speak(msg);
        await this.typeText('storyNarrativeText', msg, 20);

        await this.sleep(4000);
    }

    // ==========================================
    // STAGE 7: SEAMLESS TRANSITION TO DETAIL PAGE
    // ==========================================
    async phase7_Transition() {
        this.setStep(7);
        document.getElementById('storyPhaseLabel').innerText = 'GIAI ĐOẠN 7: HOÀN TẤT & CHUYỂN TRANG';

        const msg = `Đang chuyển tới trang thông tin chi tiết trường...`;
        this.speak(msg);
        await this.typeText('storyNarrativeText', msg, 20);

        // Zoom out and fade map
        this.map.zoomOut(2, { animate: true, duration: 1.2 });
        
        const modal = document.getElementById('storytellingModal');
        if (modal) {
            modal.style.transition = 'opacity 1s ease';
            modal.style.opacity = '0';
        }

        await this.sleep(1000);

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
window.openSchoolStoryteller = function(schoolSlug, redirectUrl) {
    if (window.storyteller) {
        window.storyteller.startStory(schoolSlug, redirectUrl);
    } else {
        window.location.href = redirectUrl || `/dia-diem/${schoolSlug}`;
    }
};
